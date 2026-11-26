@extends('layout.app')

@section('title', 'User Profile - Dental')

@section('content')
<style>
    :root { --ink: #19324a; --muted: #708296; --line: #e3ebf1; --teal: #087f73; --navy: #123752; --paper: #f5f9fa; }
    body { margin: 0; color: var(--ink); background: var(--paper); font-family: Georgia, 'Times New Roman', serif; }
    .dental-nav { display: flex; align-items: center; gap: 30px; min-height: 74px; padding: 0 5vw; color: white; background: var(--navy); font-family: Arial, sans-serif; }
    .brand, .nav-links a { color: white; text-decoration: none; }
    .brand { font: 700 24px Georgia, serif; }
    .nav-links { display: flex; gap: 22px; margin: 0; padding: 0; list-style: none; }
    .nav-links a { color: #b9cbd5; font-size: 13px; }
    .nav-links a.active { color: white; }
    .nav-spacer { flex: 1; }
    .logout-btn { padding: 9px 14px; color: white; background: transparent; border: 1px solid #7893a2; border-radius: 4px; cursor: pointer; }
    .profile-shell { max-width: 760px; margin: 0 auto; padding: 54px 24px 70px; }
    .eyebrow { margin: 0 0 9px; color: var(--teal); font: 700 11px Arial, sans-serif; letter-spacing: 1px; text-transform: uppercase; }
    h1 { margin: 0 0 8px; font-size: 42px; font-weight: 400; }
    .lead { margin: 0 0 28px; color: var(--muted); font: 14px Arial, sans-serif; }
    .profile-panel { padding: 28px; background: white; border: 1px solid var(--line); }
    .profile-picture-section { display: flex; align-items: center; gap: 18px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--line); }
    .profile-picture { width: 92px; height: 92px; object-fit: cover; border: 3px solid white; border-radius: 50%; box-shadow: 0 5px 18px rgba(18,55,82,.16); }
    .profile-picture-fallback { display: grid; width: 92px; height: 92px; color: var(--teal); background: #dff4ed; border-radius: 50%; place-items: center; font: 700 28px Arial, sans-serif; }
    .picture-controls { font: 13px Arial, sans-serif; }
    .picture-controls input { max-width: 280px; margin-top: 8px; }
    .picture-help { display: block; margin-top: 7px; color: var(--muted); font-size: 11px; }
    .fields { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .field label { display: block; margin-bottom: 7px; font: 700 12px Arial, sans-serif; }
    .field input { box-sizing: border-box; width: 100%; padding: 12px; border: 1px solid var(--line); border-radius: 3px; font: 14px Arial, sans-serif; }
    .field input:focus { outline: 2px solid #dff4ed; border-color: var(--teal); }
    .error-list, .success { margin: 0 0 18px; color: #a74435; font: 13px Arial, sans-serif; }
    .success { color: var(--teal); }
    .actions { display: flex; justify-content: end; margin-top: 24px; }
    .save-btn { padding: 12px 17px; color: white; background: var(--teal); border: 0; border-radius: 3px; cursor: pointer; font: 700 12px Arial, sans-serif; }
    .security-section { margin-top: 30px; padding-top: 24px; border-top: 1px solid var(--line); }
    .security-section h2 { margin: 0 0 8px; font-size: 22px; font-weight: 400; }
    .security-section p { color: var(--muted); font: 13px/1.5 Arial, sans-serif; }
    .security-link { display: inline-block; margin-top: 5px; padding: 11px 15px; color: white; background: var(--teal); border-radius: 3px; font: 700 12px Arial, sans-serif; text-decoration: none; }
    .security-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; max-width: 500px; }
    @media (max-width: 560px) { .dental-nav { flex-wrap: wrap; gap: 14px; padding: 14px 20px; } .nav-links { order: 3; width: 100%; justify-content: space-between; } .logout-btn { margin-left: auto; } .fields { grid-template-columns: 1fr; } .profile-panel { padding: 22px 18px; } h1 { font-size: 34px; } }
</style>

@include('layout.navigation')

<main class="profile-shell">
    <p class="eyebrow">Account settings</p>
    <h1>User profile</h1>
    <p class="lead">Manage the details associated with your account.</p>
    <section class="profile-panel">
        @if (session('success'))<p class="success" role="status">{{ session('success') }}</p>@endif
        @if ($errors->any())<ul class="error-list">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @php
                $initials = collect(explode(' ', $user->name))->map(fn ($part) => substr($part, 0, 1))->join('');
            @endphp
            <div class="profile-picture-section">
                @if ($user->profile_picture)
                    <img class="profile-picture" id="profilePreview" src="{{ Storage::url($user->profile_picture) }}" alt="{{ $user->name }}">
                @else
                    <span class="profile-picture-fallback" id="profilePreview">{{ strtoupper(substr($initials, 0, 2)) }}</span>
                @endif
                <div class="picture-controls">
                    <label for="profile_picture">Profile picture</label>
                    <input id="profile_picture" name="profile_picture" type="file" accept="image/jpeg,image/png,image/webp">
                    <span class="picture-help">JPG, PNG or WebP. Maximum 2 MB.</span>
                </div>
            </div>
            <div class="fields">
                <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
                <div class="field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username', $user->username) }}" minlength="3" maxlength="30" pattern="[A-Za-z0-9_\-]+" required></div>
                <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required></div>
                <div class="field"><label for="department">Department</label><input id="department" name="department" value="{{ old('department', $user->department) }}"></div>
                <div class="field"><label for="phone">Phone number</label><input id="phone" name="phone" value="{{ old('phone', $user->phone) }}"></div>
                <div class="field"><label for="current_password">Current password</label><div class="password-field"><input id="current_password" name="current_password" type="password" autocomplete="current-password"><button class="password-toggle" type="button" data-password-toggle="current_password" aria-label="Show current password" aria-pressed="false">Show</button></div></div>
                <div class="field"><label for="password">New password</label><div class="password-field"><input id="password" name="password" type="password" minlength="8" autocomplete="new-password"><button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show new password" aria-pressed="false">Show</button></div></div>
                <div class="field"><label for="password_confirmation">Confirm new password</label><div class="password-field"><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password"><button class="password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password confirmation" aria-pressed="false">Show</button></div></div>
            </div>
            <div class="actions"><button class="save-btn" type="submit">Save profile</button></div>
        </form>
        <div class="security-section">
            <h2>Two-factor authentication</h2>
            @if ($user->two_factor_enabled_at)
                <p>Enabled with {{ $user->two_factor_method === 'email' ? 'email verification codes' : 'an authenticator app' }}.</p>
                @if ($user->two_factor_method === 'email')
                    <form action="{{ route('two-factor.email.resend') }}" method="POST" style="margin-bottom:14px">
                        @csrf
                        <button class="save-btn" type="submit">Send a code to disable email verification</button>
                    </form>
                @endif
                <form action="{{ route('two-factor.disable') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="security-fields">
                        <div class="field"><label for="disablePassword">Current password</label><div class="password-field"><input id="disablePassword" name="password" type="password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle="disablePassword" aria-label="Show current password" aria-pressed="false">Show</button></div></div>
                        <div class="field"><label for="disableCode">Verification or recovery code</label><input id="disableCode" name="code" type="text" inputmode="text" autocomplete="one-time-code" required></div>
                    </div>
                    <div class="actions"><button class="save-btn" type="submit">Disable 2FA</button></div>
                </form>
            @else
                <p>Not enabled. Choose an authenticator app or email codes to protect account sign-in.</p>
                <a class="security-link" href="{{ route('two-factor.setup') }}">Set up Google Authenticator</a>
                <a class="security-link" href="{{ route('two-factor.email.setup') }}">Use email verification</a>
            @endif
            @if (session('recovery_codes'))
                <div class="security-section" role="status">
                    <h2>Save your recovery codes</h2>
                    <p>Each code works once. Store them somewhere safe; they will not be shown again.</p>
                    <ul>@foreach (session('recovery_codes') as $recoveryCode)<li><code>{{ $recoveryCode }}</code></li>@endforeach</ul>
                </div>
            @endif
        </div>
    </section>
</main>
<script>
    document.getElementById('profile_picture')?.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) return;

        const preview = document.getElementById('profilePreview');
        if (preview.tagName === 'IMG') {
            preview.src = URL.createObjectURL(file);
        } else {
            const image = document.createElement('img');
            image.id = 'profilePreview';
            image.className = 'profile-picture';
            image.alt = 'Profile picture preview';
            image.src = URL.createObjectURL(file);
            preview.replaceWith(image);
        }
    });
</script>
@endsection