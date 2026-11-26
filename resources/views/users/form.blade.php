@extends('layout.app')

@section('title', $title . ' - Dental')

@section('content')
<style>
    body { margin: 0; color: #19324a; background: #f5f9fa; font-family: Georgia, 'Times New Roman', serif; }
    .form-shell { max-width: 760px; margin: 0 auto; padding: 50px 24px 70px; }
    .form-nav { display: flex; justify-content: space-between; align-items: center; padding: 22px 5vw; color: white; background: #123752; font-family: Arial, sans-serif; }
    .form-nav a { color: white; text-decoration: none; }
    .form-panel { padding: 30px; background: white; border: 1px solid #e3ebf1; }
    .eyebrow { margin: 0 0 9px; color: #087f73; font: 700 11px Arial, sans-serif; letter-spacing: 1.8px; text-transform: uppercase; }
    h1 { margin: 0 0 10px; font-size: 38px; font-weight: 400; }
    .lead { margin: 0 0 28px; color: #708296; font: 14px Arial, sans-serif; }
    .fields { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 16px; }
    .field label { display: block; margin-bottom: 7px; font: 700 12px Arial, sans-serif; }
    .field input, .field select { box-sizing: border-box; width: 100%; padding: 12px; border: 1px solid #e3ebf1; border-radius: 3px; font: 14px Arial, sans-serif; }
    .errors { margin-bottom: 20px; color: #b34c3c; font: 13px Arial, sans-serif; }
    .actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 26px; }
    .button { display: inline-block; padding: 12px 17px; border: 0; border-radius: 4px; cursor: pointer; font: 700 12px Arial, sans-serif; text-decoration: none; }
    .button-secondary { color: #087f73; background: white; border: 1px solid #e3ebf1; }
    .button-primary { color: white; background: #087f73; }
    @media (max-width: 560px) { .fields { grid-template-columns: 1fr; } .form-panel { padding: 22px 18px; } }
</style>

@include('layout.navigation')

<main class="form-shell">
    <section class="form-panel">
        <p class="eyebrow">Practice administration</p>
        <h1>{{ $title }}</h1>
        <p class="lead">{{ $description }}</p>

        @if ($errors->any())
            <div class="errors"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ $action }}" method="POST">
            @csrf
            @if ($method !== 'POST') @method($method) @endif
            <div class="fields">
                <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required></div>
                <div class="field"><label for="department">Department</label><select id="department" name="department" required><option value="">Select department</option>@foreach (['General Dentistry', 'Orthodontics', 'Dental Hygiene', 'Administration', 'Reception'] as $department)<option value="{{ $department }}" @selected(old('department', $user->department ?? '') === $department)>{{ $department }}</option>@endforeach</select></div>
                <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required></div>
                <div class="field"><label for="phone">Phone number</label><input id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" required></div>
                <div class="field"><label for="role">Role</label><select id="role" name="role">@foreach ($roles as $role)<option value="{{ $role }}" @selected(old('role', $user->role ?? 'Staff') === $role)>{{ $role }}</option>@endforeach</select></div>
                <div class="field"><label for="status">Account status</label><select id="status" name="status"><option @selected(old('status', $user->status ?? 'Active') === 'Active')>Active</option><option @selected(old('status', $user->status ?? '') === 'Invited')>Invited</option><option @selected(old('status', $user->status ?? '') === 'Paused')>Paused</option></select></div>
                <div class="field"><label for="password">{{ $method === 'POST' ? 'Password' : 'New password (optional)' }}</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" {{ $method === 'POST' ? 'required' : '' }}></div>
                <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" {{ $method === 'POST' ? 'required' : '' }}></div>
            </div>
            <div class="actions"><a class="button button-secondary" href="{{ route('users.index') }}">Cancel</a><button class="button button-primary" type="submit">{{ $submitLabel }}</button></div>
        </form>
    </section>
</main>
@endsection