<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use App\Services\SystemSettings;
use App\Services\EmailOtpService;
use App\Services\TwoFactorRecoveryCodes;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('login');
    }

    public function showRegistration(Request $request, SystemSettings $systemSettings): View
    {
        $captchaEnabled = $systemSettings->enabled('captcha_enabled', true);
        $challenge = $captchaEnabled ? $request->session()->get('registration_challenge') : null;

        if (! $captchaEnabled) {
            $request->session()->forget('registration_challenge');
        } elseif (
            ! is_array($challenge)
            || ! isset($challenge['target'], $challenge['answer'])
            || ! is_int($challenge['target'])
            || ! is_int($challenge['answer'])
            || $challenge['target'] < 15
            || $challenge['target'] > 80
            || $challenge['answer'] !== $challenge['target']
        ) {
            $target = random_int(15, 80);
            $challenge = ['target' => $target, 'answer' => $target];
            $request->session()->put('registration_challenge', $challenge);
        }

        return view('register', ['challenge' => $challenge, 'captchaEnabled' => $captchaEnabled]);
    }

    public function login(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required_without:email', 'nullable', 'string', 'max:255'],
            'email' => ['required_without:identifier', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        $request->session()->forget(['two_factor_pending_user_id', 'two_factor_remember']);

        $identifier = $credentials['identifier'] ?? $credentials['email'];
        $identityField = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $loginCredentials = [$identityField => $identifier, 'password' => $credentials['password']];
        $candidate = User::query()->where($identityField, $identifier)->first();

        if ($candidate?->locked_until?->isFuture()) {
            LoginAttempt::query()->create([
                'user_id' => $candidate->id,
                'identifier' => $identifier,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => false,
                'attempted_at' => now(),
            ]);
            AuditLog::record('error', 'Login blocked for locked account.', $candidate);
            return back()->withErrors(['identifier' => 'These credentials do not match our records.'])->withInput();
        }

        if ($candidate && in_array(strtolower((string) $candidate->status), ['inactive', 'paused'], true)) {
            LoginAttempt::query()->create([
                'user_id' => $candidate->id,
                'identifier' => $identifier,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => false,
                'attempted_at' => now(),
            ]);
            AuditLog::record('error', 'Sign-in blocked for inactive account.', $candidate);

            return back()->withErrors(['identifier' => 'These credentials do not match our records.'])->withInput();
        }

        if (! Auth::attempt($loginCredentials, $request->boolean('remember'))) {
            if ($candidate) {
                $candidate->increment('failed_login_attempts');
                $attempts = $candidate->fresh()->failed_login_attempts;
                if ($attempts >= 5) {
                    $candidate->update([
                        'locked_until' => now()->addMinutes(5),
                        'failed_login_attempts' => 5,
                    ]);
                }
            }
            LoginAttempt::query()->create([
                'user_id' => $candidate?->id,
                'identifier' => $identifier,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => false,
                'attempted_at' => now(),
            ]);
            AuditLog::record('error', 'Failed sign-in attempt.');

            return back()->withErrors(['identifier' => 'These credentials do not match our records.'])->withInput();
        }

        $user = Auth::user();
        $user->update(['failed_login_attempts' => 0, 'locked_until' => null]);
        $request->session()->regenerate();

        if ($user->two_factor_enabled_at) {
            AuditLog::record('access', 'Password credentials accepted; two-factor verification required.', $user);
            $remember = $request->boolean('remember');
            Auth::logout();
            $request->session()->put('two_factor_pending_user_id', $user->id);
            $request->session()->put('two_factor_remember', $remember);
            if ($user->two_factor_method === 'email') {
                $emailOtp->send($user);
            }

            return to_route('two-factor.challenge');
        }

        LoginAttempt::query()->create([
            'user_id' => $user->id,
            'identifier' => $identifier,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => true,
            'attempted_at' => now(),
        ]);
        AuditLog::record('access', 'Sign-in successful.', $user);

        return to_route('dashboard');
    }

    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor_pending_user_id')) {
            return to_route('login');
        }

        $user = User::query()->find($request->session()->get('two_factor_pending_user_id'));
        if (! $user?->two_factor_enabled_at) {
            $request->session()->forget(['two_factor_pending_user_id', 'two_factor_remember']);

            return to_route('login');
        }

        return view('two-factor-challenge', ['method' => $user->two_factor_method ?? 'totp']);
    }

    public function resendTwoFactorCode(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $user = User::query()->find($request->session()->get('two_factor_pending_user_id'));
        if (! $user?->two_factor_enabled_at || $user->two_factor_method !== 'email') {
            return to_route('two-factor.challenge');
        }

        $emailOtp->send($user);

        return back()->with('status', 'A new verification code has been sent.');
    }

    public function verifyTwoFactor(
        Request $request,
        Google2FA $google2fa,
        EmailOtpService $emailOtp,
        TwoFactorRecoveryCodes $recoveryCodes,
    ): RedirectResponse {
        $validated = $request->validate(['code' => ['required', 'string', 'min:6', 'max:30']]);
        $user = User::query()->find($request->session()->get('two_factor_pending_user_id'));

        if (! $user?->two_factor_enabled_at) {
            $request->session()->forget(['two_factor_pending_user_id', 'two_factor_remember']);

            return to_route('login');
        }

        $timestamp = null;
        if ($user->two_factor_method === 'email' && preg_match('/^\d{6}$/', $validated['code']) === 1) {
            $validCode = $emailOtp->verify($user, $validated['code']);
        } elseif (preg_match('/^\d{6}$/', $validated['code']) === 1 && $user->two_factor_secret) {
            $timestamp = $google2fa->verifyKeyNewer(
                $user->two_factor_secret,
                $validated['code'],
                $user->two_factor_last_used_at ?? 0,
                1,
            );
            $validCode = (bool) $timestamp;
        } else {
            $validCode = $recoveryCodes->consume($user, $validated['code']);
        }

        if (! $validCode) {
            LoginAttempt::query()->create([
                'user_id' => $user->id,
                'identifier' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => false,
                'attempted_at' => now(),
            ]);
            AuditLog::record('error', 'Failed authenticator challenge.', $user);

            return back()->withErrors(['code' => 'That verification or recovery code is invalid, expired, or already used.']);
        }

        if ($timestamp) {
            $user->update(['two_factor_last_used_at' => $timestamp]);
        }
        LoginAttempt::query()->create([
            'user_id' => $user->id,
            'identifier' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => true,
            'attempted_at' => now(),
        ]);
        Auth::login($user, (bool) $request->session()->pull('two_factor_remember', false));
        $request->session()->forget('two_factor_pending_user_id');
        $request->session()->regenerate();
        AuditLog::record('access', 'Two-factor sign-in successful.', $user);

        return to_route('dashboard');
    }

    public function register(Request $request, SystemSettings $systemSettings): RedirectResponse
    {
        $rules = [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:30', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
            'terms' => ['accepted'],
        ];
        if ($systemSettings->enabled('captcha_enabled', true)) {
            $rules['captcha_answer'] = [
                'required',
                'integer',
                'between:0,100',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ((int) $value !== (int) $request->session()->get('registration_challenge.answer', -1)) {
                        $fail('Complete the slider puzzle before creating your account.');
                    }
                },
            ];
        }
        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['firstName'].' '.$validated['lastName'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'Guest',
            'status' => 'Active',
        ]);

        $request->session()->forget(['two_factor_pending_user_id', 'two_factor_remember']);
        Auth::login($user);
        $request->session()->forget('registration_challenge');
        $request->session()->regenerate();

        return to_route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLog::record('access', 'Signed out.', $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request, SystemSettings $systemSettings): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        $systemSettings->applyMailConfiguration();
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If an account exists for this email, password reset instructions have been sent.');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'password_changed_at' => now(),
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                ]);
                $user->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'The reset link is invalid or has expired.']);
        }

        Auth::logoutOtherDevices($request->password);
        AuditLog::record('transaction', 'Password reset successfully.');

        return to_route('login')->with('status', 'Your password has been reset.');
    }
}
