<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\SystemSettings;
use App\Services\EmailOtpService;
use App\Services\TwoFactorRecoveryCodes;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function setup(Request $request, Google2FA $google2fa): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->two_factor_enabled_at) {
            return to_route('profile');
        }

        $secret = $this->pendingSecret($request);

        if ($secret === null) {
            $secret = $google2fa->generateSecretKey();
            $request->session()->put('two_factor_setup_secret', Crypt::encryptString($secret));
        }

        $otpUrl = $google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);
        $renderer = new ImageRenderer(new RendererStyle(280, 2), new SvgImageBackEnd);
        $qrCode = base64_encode((new Writer($renderer))->writeString($otpUrl));

        return view('two-factor-setup', compact('qrCode'));
    }

    public function enable(Request $request, Google2FA $google2fa, TwoFactorRecoveryCodes $recoveryCodes): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $secret = $this->pendingSecret($request);

        if ($secret === null) {
            return to_route('two-factor.setup')->withErrors(['code' => 'Start setup again to generate a new QR code.']);
        }

        $timestamp = $google2fa->verifyKey($secret, $validated['code'], 1, null, 0);

        if (! $timestamp) {
            return back()->withErrors(['code' => 'The code did not match. Scan the QR code and try again.']);
        }

        $request->user()->update([
            'two_factor_secret' => $secret,
            'two_factor_method' => 'totp',
            'two_factor_enabled_at' => now(),
            'two_factor_last_used_at' => $timestamp,
        ]);
        $request->session()->forget('two_factor_setup_secret');
        $codes = $recoveryCodes->generate($request->user());

        return to_route('profile')->with('success', 'Two-factor authentication enabled. Save your recovery codes now.')
            ->with('recovery_codes', $codes);
    }

    public function showEmailSetup(Request $request): View|RedirectResponse
    {
        if ($request->user()->two_factor_enabled_at) {
            return to_route('profile');
        }

        return view('two-factor-email-setup');
    }

    public function showEmailChallenge(Request $request): View|RedirectResponse
    {
        if ((int) $request->session()->get('two_factor_email_setup_user_id') !== (int) $request->user()->id) {
            return to_route('profile');
        }

        return view('two-factor-email-setup', ['challenge' => true]);
    }

    public function sendEmailSetup(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        if ($request->user()->two_factor_enabled_at) {
            return to_route('profile');
        }

        $emailOtp->send($request->user());
        $request->session()->put('two_factor_email_setup_user_id', $request->user()->id);

        return to_route('two-factor.email.challenge')->with('status', 'A verification code has been sent to your account email.');
    }

    public function verifyEmailSetup(Request $request, EmailOtpService $emailOtp, TwoFactorRecoveryCodes $recoveryCodes): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = User::query()->find($request->session()->get('two_factor_email_setup_user_id'));

        if (! $user || $user->two_factor_enabled_at) {
            $request->session()->forget('two_factor_email_setup_user_id');

            return to_route('profile');
        }

        if (! $emailOtp->verify($user, $validated['code'])) {
            return back()->withErrors(['code' => 'That verification code is invalid or expired.']);
        }

        $user->update([
            'two_factor_method' => 'email',
            'two_factor_secret' => null,
            'two_factor_enabled_at' => now(),
            'two_factor_last_used_at' => null,
        ]);
        $request->session()->forget('two_factor_email_setup_user_id');
        $codes = $recoveryCodes->generate($user);

        return to_route('profile')->with('success', 'Email verification enabled. Save your recovery codes now.')
            ->with('recovery_codes', $codes);
    }

    public function disable(
        Request $request,
        Google2FA $google2fa,
        SystemSettings $systemSettings,
        EmailOtpService $emailOtp,
        TwoFactorRecoveryCodes $recoveryCodes,
    ): RedirectResponse
    {
        if ($systemSettings->enabled('mfa_enabled', false)) {
            return back()->withErrors(['code' => 'Multi-factor authentication is required by the system administrator.']);
        }

        $validated = $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'min:6', 'max:30'],
        ]);
        $user = $request->user();

        if (! Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        $validCode = false;
        $timestamp = null;
        if (preg_match('/^\d{6}$/', $validated['code']) === 1 && $user->two_factor_method === 'email') {
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
            return back()->withErrors(['code' => 'The authenticator code is invalid or has already been used.']);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_method' => null,
            'two_factor_enabled_at' => null,
            'two_factor_last_used_at' => $timestamp,
        ]);
        DB::table('user_recovery_codes')->where('user_id', $user->id)->delete();
        DB::table('email_otp_tokens')->where('user_id', $user->id)->delete();

        return to_route('profile')->with('success', 'Two-factor authentication disabled.');
    }

    public function sendDisableEmailCode(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $user = $request->user();
        if (! $user->two_factor_enabled_at || $user->two_factor_method !== 'email') {
            return to_route('profile')->withErrors(['code' => 'Email verification is not enabled for this account.']);
        }

        $emailOtp->send($user);

        return to_route('profile')->with('success', 'A verification code has been sent to your account email.');
    }

    private function pendingSecret(Request $request): ?string
    {
        $storedSecret = $request->session()->get('two_factor_setup_secret');

        if (! is_string($storedSecret)) {
            return null;
        }

        try {
            return Crypt::decryptString($storedSecret);
        } catch (DecryptException) {
            $request->session()->forget('two_factor_setup_secret');

            return null;
        }
    }
}
