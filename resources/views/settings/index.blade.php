@extends('layout.app')
@section('title', 'System settings')
@section('content')
@include('layout.navigation')
<style>
    .settings-shell { max-width: 1000px; margin: 0 auto; padding: 42px 24px 72px; font-family: Arial, sans-serif; }
    .settings-heading { margin-bottom: 26px; }
    .settings-eyebrow { margin: 0 0 8px; color: #087f73; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
    .settings-heading h1 { margin: 0 0 8px; color: #19324a; font: 400 clamp(30px, 4vw, 42px) Georgia, serif; }
    .settings-heading p:last-child { margin: 0; color: #708296; font-size: 14px; }
    .settings-alert { margin: 0 0 20px; padding: 12px 16px; border-radius: 6px; background: #e4f5ef; color: #126653; font-size: 14px; }
    .settings-alert-error { background: #fff0ed; color: #a74435; }
    .settings-form { display: grid; gap: 18px; }
    .settings-card { padding: 22px 24px; background: #fff; border: 1px solid #e3ebf1; border-radius: 10px; box-shadow: 0 8px 26px rgba(25, 50, 74, .045); }
    .settings-card h2 { margin: 0 0 5px; color: #19324a; font: 400 21px Georgia, serif; }
    .settings-card > p { margin: 0 0 18px; color: #708296; font-size: 13px; }
    .settings-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px 18px; }
    .settings-field { min-width: 0; }
    .settings-field-wide { grid-column: 1 / -1; }
    .settings-field label { display: block; margin-bottom: 6px; color: #344c60; font-size: 12px; font-weight: 700; }
    .settings-field input, .settings-field textarea, .settings-field select { box-sizing: border-box; width: 100%; min-height: 42px; padding: 10px 12px; color: #19324a; background: #fff; border: 1px solid #d5e0e7; border-radius: 5px; font: 14px Arial, sans-serif; }
    .settings-field textarea { min-height: 86px; resize: vertical; }
    .settings-field input:focus, .settings-field textarea:focus, .settings-field select:focus { outline: 3px solid rgba(8, 127, 115, .16); border-color: #087f73; }
    .settings-hint { display: block; margin-top: 6px; color: #708296; font-size: 12px; line-height: 1.45; }
    .settings-toggles { display: flex; flex-wrap: wrap; gap: 12px; }
    .settings-toggle { display: flex; align-items: center; gap: 10px; padding: 12px 14px; background: #f5f9fa; border: 1px solid #e3ebf1; border-radius: 6px; color: #344c60; font-size: 13px; font-weight: 600; cursor: pointer; }
    .settings-toggle input { width: 17px; height: 17px; accent-color: #087f73; }
    .settings-actions { display: flex; justify-content: flex-end; }
    .settings-save { padding: 12px 19px; color: #fff; background: #087f73; border: 0; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 700; }
    .settings-save:hover { background: #06685f; }
    html[data-theme="dark"] body .settings-heading h1, html[data-theme="dark"] body .settings-card h2 { color: var(--ink); }
    html[data-theme="dark"] body .settings-heading p:last-child, html[data-theme="dark"] body .settings-card > p, html[data-theme="dark"] body .settings-hint { color: var(--muted); }
    html[data-theme="dark"] body .settings-card { background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .settings-field label { color: var(--ink); }
    html[data-theme="dark"] body .settings-toggle { color: var(--ink); background: var(--field); border-color: var(--line); }
    @media (max-width: 620px) { .settings-shell { padding: 30px 18px 56px; } .settings-card { padding: 19px 16px; } .settings-fields { grid-template-columns: 1fr; } .settings-field-wide { grid-column: auto; } }
</style>
<main class="settings-shell">
    <header class="settings-heading">
        <p class="settings-eyebrow">System administration</p>
        <h1>System settings</h1>
        <p>Manage your organization details and integrations. Secret values are stored encrypted and are never displayed here.</p>
    </header>

    @if (session('success'))<p class="settings-alert" role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())
        <div class="settings-alert settings-alert-error" role="alert">
            <strong>Settings were not saved:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form class="settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <input type="hidden" name="settings_submitted" value="1">
        <section class="settings-card">
            <h2>System identity</h2><p>Set the system name and organization shown in this workspace.</p>
            <div class="settings-fields">
                <div class="settings-field"><label for="system_name">System name</label><input id="system_name" name="system_name" value="{{ old('system_name', $settings['system_name']->value ?? 'Dental') }}" maxlength="255" required></div>
                <div class="settings-field"><label for="organization_name">Organization</label><input id="organization_name" name="organization_name" value="{{ old('organization_name', $settings['organization_name']->value ?? '') }}" maxlength="255"></div>
                <div class="settings-field settings-field-wide"><label for="system_description">System description</label><textarea id="system_description" name="system_description" maxlength="2000">{{ old('system_description', $settings['system_description']->value ?? '') }}</textarea></div>
                <div class="settings-field settings-field-wide">
                    <label for="logo">System logo</label>
                    @if (!empty($settings['logo_path']->value ?? null))
                        <img src="{{ Storage::url($settings['logo_path']->value) }}" alt="Current system logo" style="display:block;width:auto;height:auto;max-width:180px;max-height:70px;margin:0 0 10px">
                    @endif
                    <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp">
                    <span class="settings-hint">Optional. JPG, PNG or WebP, up to 2 MB. Replaces the current system logo.</span>
                </div>
            </div>
        </section>

        <section class="settings-card">
            <h2>Security</h2><p>Configure registration challenge and multi-factor authentication options.</p>
            <div class="settings-fields">
                <div class="settings-field"><label for="captcha_provider">CAPTCHA provider</label><input id="captcha_provider" name="captcha_provider" value="{{ old('captcha_provider', $settings['captcha_provider']->value ?? 'Built-in slider puzzle') }}" maxlength="100"></div>
                <div class="settings-field"><label for="otp_expiration">OTP expiration (minutes)</label><input id="otp_expiration" name="otp_expiration" type="number" min="1" max="60" value="{{ old('otp_expiration', $settings['otp_expiration']->value ?? 5) }}"></div>
                <div class="settings-field settings-field-wide">
                    <div class="settings-toggles">
                        <label class="settings-toggle"><input type="checkbox" name="captcha_enabled" value="1" @checked(old('settings_submitted') ? old('captcha_enabled') == '1' : (($settings['captcha_enabled']->value ?? '1') == '1'))> Enable CAPTCHA</label>
                        <label class="settings-toggle"><input type="checkbox" name="mfa_enabled" value="1" @checked(old('settings_submitted') ? old('mfa_enabled') == '1' : (($settings['mfa_enabled']->value ?? '0') == '1'))> Enable MFA</label>
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-card">
            <h2>Email delivery</h2><p>Set SMTP connection details used for password recovery and account notifications.</p>
            <div class="settings-fields">
                <div class="settings-field"><label for="smtp_host">SMTP host</label><input id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host']->value ?? '') }}" maxlength="255" autocomplete="off"></div>
                <div class="settings-field"><label for="smtp_port">SMTP port</label><input id="smtp_port" name="smtp_port" type="number" min="1" max="65535" value="{{ old('smtp_port', $settings['smtp_port']->value ?? '') }}"></div>
                <div class="settings-field"><label for="smtp_username">SMTP username</label><input id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username']->value ?? '') }}" maxlength="255" autocomplete="off"></div>
                <div class="settings-field"><label for="smtp_password">SMTP password</label><input id="smtp_password" name="smtp_password" type="password" value="" maxlength="255" autocomplete="new-password" placeholder="Leave blank to keep current"></div>
                <div class="settings-field settings-field-wide"><span class="settings-hint">Configure sender email and SMTP encryption in the deployment environment. This page does not expose a stored SMTP password.</span></div>
            </div>
        </section>

        <section class="settings-card">
            <h2>Backup retention</h2><p>Set how long backup files should be retained by your operational policy.</p>
            <div class="settings-fields">
                <div class="settings-field"><label for="backup_retention">Backup retention (days)</label><input id="backup_retention" name="backup_retention" type="number" min="1" max="365" value="{{ old('backup_retention', $settings['backup_retention']->value ?? 30) }}"></div>
            </div>
        </section>
        <div class="settings-actions"><button class="settings-save" type="submit">Save settings</button></div>
    </form>
</main>
@endsection
