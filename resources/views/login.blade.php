@extends('layout.app')

@section('title', 'Sign in - '.($systemBrand['name'] ?? 'Dental'))

@section('content')

<style>
    :root {
        --navy: #123752;
        --teal: #087f73;
        --mint: #dff4ed;
        --coral: #ef806d;
        --ink: #19324a;
        --muted: #708296;
        --line: #e3ebf1;
    }

    .auth-page {
        min-height: 100vh;
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(380px, .95fr);
        background: #f5f9fa;
        color: var(--ink);
        font-family: Georgia, 'Times New Roman', serif;
    }

    .auth-intro {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 46px 7vw 42px;
        color: white;
        background: var(--navy);
    }

    .auth-brand {
        display: inline-flex;
        align-items: center;
        color: white;
        font-size: 28px;
        font-weight: 700;
        text-decoration: none;
    }

    .auth-mark {
        display: inline-grid;
        width: 38px;
        height: 38px;
        margin-right: 10px;
        color: var(--navy);
        background: var(--mint);
        border-radius: 50%;
        place-items: center;
        font: 700 21px Arial, sans-serif;
    }
    .auth-logo { width: 38px; height: 38px; margin-right: 10px; padding: 3px; background: white; border-radius: 50%; object-fit: contain; }

    .intro-copy {
        max-width: 470px;
    }

    .intro-copy p {
        color: #b9cbd5;
        font: 15px/1.7 Arial, sans-serif;
    }

    .intro-copy h1 {
        margin: 0;
        font-size: clamp(42px, 5vw, 68px);
        font-weight: 400;
        line-height: 1.03;
        letter-spacing: -2px;
        color: white;
    }

    .intro-rule {
        width: 52px;
        height: 4px;
        margin: 24px 0;
        background: var(--coral);
    }

    .auth-quote {
        color: #b9cbd5;
        font: italic 14px/1.6 Arial, sans-serif;
    }

    .auth-form-wrap {
        display: flex;
        align-items: center;
        padding: 50px clamp(28px, 7vw, 100px);
        background: white;
    }

    .auth-form {
        width: 100%;
        max-width: 410px;
    }

    .eyebrow {
        margin: 0 0 9px;
        color: var(--teal);
        font: 700 11px Arial, sans-serif;
        letter-spacing: 1.8px;
        text-transform: uppercase;
    }

    .auth-form h2 {
        margin: 0 0 10px;
        font-size: 34px;
        font-weight: 400;
        color: var(--ink);
    }

    .form-lead {
        margin: 0 0 30px;
        color: var(--muted);
        font: 14px Arial, sans-serif;
    }

    .field {
        margin-bottom: 18px;
    }

    .field label {
        display: block;
        margin-bottom: 7px;
        font: 700 12px Arial, sans-serif;
        color: var(--ink);
    }

    .field input {
        box-sizing: border-box;
        width: 100%;
        padding: 14px 15px;
        color: var(--ink);
        background: #fbfdfd;
        border: 1px solid var(--line);
        border-radius: 4px;
        font: 15px Arial, sans-serif;
    }

    .field input:focus {
        outline: 2px solid var(--mint);
        border-color: var(--teal);
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 4px 0 25px;
        color: var(--muted);
        font: 12px Arial, sans-serif;
    }

    .remember-label {
        display: flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
    }

    .remember-label input {
        accent-color: var(--teal);
        cursor: pointer;
    }

    .form-options a,
    .auth-switch a {
        color: var(--teal);
        font-weight: 700;
        text-decoration: none;
    }

    .form-options a:hover,
    .auth-switch a:hover {
        text-decoration: underline;
    }

    .primary-btn {
        width: 100%;
        padding: 14px;
        color: white;
        background: var(--teal);
        border: 0;
        border-radius: 4px;
        cursor: pointer;
        font: 700 13px Arial, sans-serif;
        transition: background 0.2s ease;
    }

    .primary-btn:hover {
        background: #06675e;
    }

    .primary-btn:focus {
        outline: 2px solid var(--mint);
        outline-offset: 2px;
    }

    .auth-switch {
        margin: 24px 0 0;
        color: var(--muted);
        text-align: center;
        font: 13px Arial, sans-serif;
    }

    .auth-notice {
        min-height: 18px;
        margin-top: 16px;
        color: var(--teal);
        text-align: center;
        font: 12px Arial, sans-serif;
    }

    @media (max-width: 760px) {
        .auth-page {
            grid-template-columns: 1fr;
        }

        .auth-intro {
            min-height: 360px;
            padding: 30px 24px;
        }

        .auth-quote {
            display: none;
        }

        .auth-form-wrap {
            padding: 44px 24px 60px;
        }

        .auth-form h2 {
            font-size: 30px;
        }
    }

    html[data-theme="dark"] body .auth-page {
        color: #e4edf3;
        background: #0f2535;
    }

    html[data-theme="dark"] body .auth-form-wrap {
        background: #183448;
    }

    html[data-theme="dark"] body .auth-form h2,
    html[data-theme="dark"] body .field label,
    html[data-theme="dark"] body .remember-label {
        color: #e4edf3;
    }

    html[data-theme="dark"] body .eyebrow {
        color: #73d5c1;
    }

    html[data-theme="dark"] body .form-lead,
    html[data-theme="dark"] body .form-options,
    html[data-theme="dark"] body .auth-switch {
        color: #b0c1cc;
    }

    html[data-theme="dark"] body .form-options a,
    html[data-theme="dark"] body .auth-switch a {
        color: #73d5c1;
    }

    html[data-theme="dark"] body .field input {
        color: #e4edf3;
        background: #102938;
        border-color: #3a5263;
    }

    html[data-theme="dark"] body .field input::placeholder {
        color: #9aafbd;
        opacity: 1;
    }

    html[data-theme="dark"] body .field input:focus {
        outline-color: rgba(115, 213, 193, .3);
        border-color: #73d5c1;
    }

    html[data-theme="dark"] body .auth-notice {
        color: #73d5c1;
    }

    html[data-theme="dark"] body .auth-intro .intro-copy p,
    html[data-theme="dark"] body .auth-intro .auth-quote {
        color: #b9cbd5;
    }

    html[data-theme="dark"] body .auth-intro .intro-copy h1 {
        color: #fff;
    }

    html[data-theme="dark"] body .auth-intro a.auth-brand {
        color: #fff !important;
    }

    .auth-form-wrap {
        background: #f5f9fa;
    }

    .auth-form {
        box-sizing: border-box;
        max-width: 430px;
        padding: clamp(24px, 4vw, 42px);
        background: #fff;
        border: 1px solid rgba(25, 50, 74, .07);
        border-radius: 12px;
        box-shadow: 0 18px 48px rgba(18, 55, 82, .09);
    }

    .field input {
        min-height: 48px;
        border-radius: 7px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .field input:focus {
        outline: 0;
        box-shadow: 0 0 0 4px rgba(8, 127, 115, .12);
    }

    .primary-btn {
        min-height: 48px;
        border-radius: 7px;
        box-shadow: 0 5px 12px rgba(8, 127, 115, .18);
    }

    .remember-label input {
        width: 16px;
        height: 16px;
    }

    html[data-theme="dark"] body .auth-form-wrap {
        background: #0f2535;
    }

    html[data-theme="dark"] body .auth-form {
        background: #183448;
        border-color: #3a5263;
        box-shadow: 0 18px 48px rgba(0, 0, 0, .2);
    }

    @media (max-width: 760px) {
        .auth-intro { min-height: 300px; }
        .auth-intro .intro-copy { margin: 46px 0 18px; }
        .auth-intro .intro-copy h1 { font-size: clamp(36px, 9vw, 52px); }
        .auth-form-wrap { padding: 28px 18px 48px; }
        .auth-form { max-width: 500px; }
    }
</style>

<main class="auth-page">

    <section class="auth-intro">

        <a class="auth-brand" href="/">
            @if ($systemBrand['logo'])
                <img class="auth-logo" src="{{ Storage::url($systemBrand['logo']) }}" alt="">
            @else
                <span class="auth-mark">+</span>
            @endif
            {{ $systemBrand['name'] }}
        </a>

        <div class="intro-copy">
            <p class="eyebrow">Care, clearly managed</p>

            <h1>Make every appointment count.</h1>

            <div class="intro-rule"></div>

            <p>
                One calm workspace for your patients, team, and practice.
                Welcome back to your clinic dashboard.
            </p>
        </div>

        <p class="auth-quote">
            "Healthy smiles start with thoughtful care."
        </p>

    </section>

    <section class="auth-form-wrap">

        <form class="auth-form" action="{{ route('login.store') }}" method="POST">
            @csrf

            <p class="eyebrow">Welcome back</p>

            <h2>Sign in to {{ $systemBrand['name'] }}</h2>

            <p class="form-lead">
                Enter your details to continue to your practice.
            </p>

            <div class="field">
                <label for="identifier">
                    Username or email
                </label>

                <input
                    type="text"
                    id="identifier"
                    name="identifier"
                    value="{{ old('identifier', old('email')) }}"
                    placeholder="Username or email"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="field">
                <label for="password">
                    Password
                </label>

                <div class="password-field">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">Show</button>
                </div>
            </div>

            <div class="form-options">

                <label class="remember-label">
                    <input
                        type="checkbox"
                        name="remember"
                    >
                    <span>Remember me</span>
                </label>

                <a
                    href="{{ route('password.request') }}"
                >
                    Forgot password?
                </a>

            </div>

            <button
                class="primary-btn"
                type="submit"
            >
                Sign in
            </button>

            <p class="auth-switch">
                New to {{ $systemBrand['name'] }}?
                <a href="/register">
                    Create an account
                </a>
            </p>

            <p
                id="loginNotice"
                class="auth-notice"
                role="status"
                aria-live="polite"
            ></p>

            @error('identifier')
                <p class="auth-notice">{{ $message }}</p>
            @enderror
            @error('password')
                <p class="auth-notice">{{ $message }}</p>
            @enderror

        </form>

    </section>

</main>

@endsection
