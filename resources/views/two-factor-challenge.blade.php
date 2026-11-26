@extends('layout.app')

@section('title', 'Authenticator Check - Dental')

@section('content')
<style>
    body { margin: 0; color: #19324a; background: #f5f9fa; font-family: Georgia, 'Times New Roman', serif; }
    .challenge-shell { display: grid; min-height: 100vh; place-items: center; padding: 24px; }
    .challenge-panel { width: min(420px, 100%); padding: 34px; background: white; border: 1px solid #e3ebf1; }
    .eyebrow { margin: 0 0 9px; color: #087f73; font: 700 11px Arial, sans-serif; letter-spacing: 1px; text-transform: uppercase; }
    h1 { margin: 0 0 10px; font-size: 32px; font-weight: 400; }
    .lead { margin: 0 0 25px; color: #708296; font: 14px/1.6 Arial, sans-serif; }
    label { display: block; margin-bottom: 7px; font: 700 12px Arial, sans-serif; }
    input { box-sizing: border-box; width: 100%; padding: 13px; border: 1px solid #e3ebf1; border-radius: 3px; font: 20px Arial, sans-serif; letter-spacing: 4px; text-align: center; }
    .error { margin: 12px 0 0; color: #a74435; font: 13px Arial, sans-serif; }
    button { width: 100%; margin-top: 20px; padding: 13px; color: white; background: #087f73; border: 0; border-radius: 3px; cursor: pointer; font: 700 13px Arial, sans-serif; }
    .back { display: block; margin-top: 18px; color: #087f73; text-align: center; font: 13px Arial, sans-serif; }
    html[data-theme="dark"] body .challenge-panel { color: #e4edf3; background: #183448; border-color: #3a5263; }
    html[data-theme="dark"] body .challenge-panel h1,
    html[data-theme="dark"] body .challenge-panel label { color: #e4edf3; }
    html[data-theme="dark"] body .challenge-panel .lead { color: #b0c1cc; }
    html[data-theme="dark"] body .challenge-panel input { color: #e4edf3; background: #102938; border-color: #3a5263; }
    html[data-theme="dark"] body .challenge-panel .error { color: #ffb4a9; }
    html[data-theme="dark"] body .challenge-panel .back { color: #73d5c1; }
</style>
<main class="challenge-shell">
    <section class="challenge-panel">
        <p class="eyebrow">Two-factor authentication</p>
        <h1>Enter your code</h1>
        <p class="lead">
            @if ($method === 'email')
                Enter the six-digit sign-in code sent to your account email.
            @else
                Enter the current six-digit code from your authenticator app.
            @endif
            You may also use one of your saved recovery codes.
        </p>
        @if (session('status'))<p class="lead" role="status">{{ session('status') }}</p>@endif
        <form action="{{ route('two-factor.verify') }}" method="POST">
            @csrf
            <label for="code">Verification or recovery code</label>
            <input id="code" name="code" type="text" inputmode="text" autocomplete="one-time-code" maxlength="30" required autofocus>
            @error('code')<p class="error">{{ $message }}</p>@enderror
            <button type="submit">Verify and sign in</button>
        </form>
        @if ($method === 'email')
            <form action="{{ route('two-factor.resend') }}" method="POST">@csrf<button type="submit">Send a new email code</button></form>
        @endif
        <a class="back" href="{{ route('login') }}">Return to sign in</a>
    </section>
</main>
@endsection