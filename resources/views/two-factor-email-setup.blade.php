@extends('layout.app')
@section('title', 'Set up email verification')
@section('content')
<style>
    .email-2fa-shell { display: grid; min-height: 100vh; box-sizing: border-box; padding: 24px; place-items: center; background: #f5f9fa; font-family: Arial, sans-serif; }
    .email-2fa-card { width: min(440px, 100%); box-sizing: border-box; padding: 32px; background: #fff; border: 1px solid #e3ebf1; border-radius: 10px; }
    .email-2fa-card h1 { margin: 0 0 10px; color: #19324a; font: 400 30px Georgia, serif; }
    .email-2fa-card p { color: #708296; font-size: 14px; line-height: 1.55; }
    .email-2fa-card label { display: block; margin: 18px 0 7px; color: #19324a; font-size: 12px; font-weight: 700; }
    .email-2fa-card input { box-sizing: border-box; width: 100%; padding: 13px; border: 1px solid #d5e0e7; border-radius: 5px; font-size: 18px; letter-spacing: 4px; text-align: center; }
    .email-2fa-card button { width: 100%; margin-top: 16px; padding: 13px; color: white; background: #087f73; border: 0; border-radius: 5px; cursor: pointer; font-weight: 700; }
    .email-2fa-card a { display: block; margin-top: 16px; color: #087f73; font-size: 13px; text-align: center; }
    .email-2fa-errors { color: #a74435; }
    html[data-theme="dark"] body .email-2fa-shell { background: #0f2535; }
    html[data-theme="dark"] body .email-2fa-card { background: #183448; border-color: #3a5263; }
    html[data-theme="dark"] body .email-2fa-card h1, html[data-theme="dark"] body .email-2fa-card label { color: #e4edf3; }
    html[data-theme="dark"] body .email-2fa-card p { color: #b0c1cc; }
    html[data-theme="dark"] body .email-2fa-card input { color: #e4edf3; background: #102938; border-color: #3a5263; }
    html[data-theme="dark"] body .email-2fa-card .email-2fa-errors { color: #ffb4a9 !important; background: #432d31; }
    html[data-theme="dark"] body .email-2fa-card a { color: #73d5c1; }
    @media (max-width: 520px) { .email-2fa-card { padding: 24px 18px; } }
</style>
<main class="email-2fa-shell">
    <section class="email-2fa-card">
        @if ($challenge ?? false)
            <h1>Verify your email</h1>
            <p>Enter the six-digit code sent to your account email. The code expires shortly.</p>
            @if (session('status'))<p role="status">{{ session('status') }}</p>@endif
            @if ($errors->any())<p class="email-2fa-errors" role="alert">{{ $errors->first() }}</p>@endif
            <form method="POST" action="{{ route('two-factor.email.verify') }}">
                @csrf
                <label for="emailSetupCode">Verification code</label>
                <input id="emailSetupCode" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
                <button type="submit">Enable email verification</button>
            </form>
        @else
            <h1>Use email verification</h1>
            <p>We will send a one-time sign-in code to {{ auth()->user()->email }}. Email delivery must be configured by the system administrator.</p>
            @if ($errors->any())<p class="email-2fa-errors" role="alert">{{ $errors->first() }}</p>@endif
            <form method="POST" action="{{ route('two-factor.email.send') }}">
                @csrf
                <button type="submit">Send verification code</button>
            </form>
        @endif
        <a href="{{ route('profile') }}">Return to profile</a>
    </section>
</main>
@endsection
