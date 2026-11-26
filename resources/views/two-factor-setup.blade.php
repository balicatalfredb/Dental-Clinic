@extends('layout.app')

@section('title', 'Set Up Authenticator - Dental')

@section('content')
<style>
    body { margin: 0; color: #19324a; background: #f5f9fa; font-family: Georgia, 'Times New Roman', serif; }
    .setup-shell { max-width: 760px; margin: 0 auto; padding: 50px 24px 70px; }
    .setup-panel { display: grid; grid-template-columns: 280px 1fr; gap: 30px; align-items: center; padding: 30px; background: white; border: 1px solid #e3ebf1; }
    .qr-code { display: block; width: 100%; height: auto; border: 1px solid #e3ebf1; }
    .eyebrow { margin: 0 0 9px; color: #087f73; font: 700 11px Arial, sans-serif; letter-spacing: 1px; text-transform: uppercase; }
    h1 { margin: 0 0 10px; font-size: 34px; font-weight: 400; }
    .lead { margin: 0 0 22px; color: #708296; font: 14px/1.6 Arial, sans-serif; }
    label { display: block; margin-bottom: 7px; font: 700 12px Arial, sans-serif; }
    input { box-sizing: border-box; width: 100%; padding: 12px; border: 1px solid #e3ebf1; border-radius: 3px; font: 18px Arial, sans-serif; letter-spacing: 3px; text-align: center; }
    .error { margin: 10px 0 0; color: #a74435; font: 13px Arial, sans-serif; }
    .actions { display: flex; gap: 12px; align-items: center; margin-top: 18px; }
    .actions a { color: #087f73; font: 13px Arial, sans-serif; }
    button { padding: 12px 16px; color: white; background: #087f73; border: 0; border-radius: 3px; cursor: pointer; font: 700 12px Arial, sans-serif; }
    html[data-theme="dark"] body .setup-panel { color: #e4edf3; background: #183448; border-color: #3a5263; }
    html[data-theme="dark"] body .setup-panel h1,
    html[data-theme="dark"] body .setup-panel label { color: #e4edf3; }
    html[data-theme="dark"] body .setup-panel .lead { color: #b0c1cc; }
    html[data-theme="dark"] body .setup-panel input { color: #e4edf3; background: #102938; border-color: #3a5263; }
    html[data-theme="dark"] body .setup-panel .error { color: #ffb4a9; }
    html[data-theme="dark"] body .setup-panel .actions a { color: #73d5c1; }
    html[data-theme="dark"] body .qr-code { border-color: #3a5263; }
    @media (max-width: 650px) { .setup-panel { grid-template-columns: 1fr; padding: 22px; } .qr-code { width: min(280px, 100%); margin: 0 auto; } }
</style>
<main class="setup-shell">
    <section class="setup-panel">
        <img class="qr-code" src="data:image/svg+xml;base64,{{ $qrCode }}" alt="Authenticator setup QR code">
        <div>
            <p class="eyebrow">Account security</p>
            <h1>Set up an authenticator</h1>
            <p class="lead">Scan this QR code with Google Authenticator or another TOTP app, then enter its six-digit code to bind the app to your account.</p>
            <form action="{{ route('two-factor.enable') }}" method="POST">
                @csrf
                <label for="code">Authenticator code</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                @error('code')<p class="error">{{ $message }}</p>@enderror
                <div class="actions"><button type="submit">Enable 2FA</button><a href="{{ route('profile') }}">Cancel</a></div>
            </form>
        </div>
    </section>
</main>
@endsection