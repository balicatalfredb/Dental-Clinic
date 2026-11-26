@extends('layout.app')
@section('title', 'Roles and permissions')
@section('content')
@include('layout.navigation')
<style>
    .roles-shell { max-width: 1080px; margin: 0 auto; padding: 40px 24px 70px; font-family: Arial, sans-serif; color: #19324a; }
    .roles-shell h1 { margin: 0 0 8px; font: 400 38px Georgia, serif; }
    .roles-shell .lead { margin: 0 0 24px; color: #708296; font-size: 14px; }
    .roles-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(310px, 1fr)); gap: 16px; }
    .role-card { padding: 20px; background: #fff; border: 1px solid #e3ebf1; border-radius: 9px; }
    .role-card h2 { margin: 0 0 5px; font: 400 22px Georgia, serif; }
    .role-card p { color: #708296; font-size: 13px; }
    .permission-list { display: grid; gap: 9px; margin: 17px 0; }
    .permission-list label { display: flex; gap: 9px; align-items: flex-start; font-size: 13px; }
    .permission-list input { margin-top: 2px; accent-color: #087f73; }
    .role-description, .role-name { box-sizing: border-box; width: 100%; margin-top: 8px; padding: 10px; border: 1px solid #d5e0e7; border-radius: 5px; font: 13px Arial, sans-serif; }
    .role-save { padding: 10px 14px; color: white; background: #087f73; border: 0; border-radius: 5px; font-weight: 700; cursor: pointer; }
    .role-new { margin-bottom: 20px; padding: 20px; background: #fff; border: 1px solid #e3ebf1; border-radius: 9px; }
    .role-new h2 { margin: 0 0 8px; font: 400 22px Georgia, serif; }
    .role-errors { margin-bottom: 16px; color: #a74435; }
    html[data-theme="dark"] body .roles-shell { color: var(--ink); }
    html[data-theme="dark"] body .roles-shell .lead, html[data-theme="dark"] body .role-card p { color: var(--muted); }
    html[data-theme="dark"] body .role-card, html[data-theme="dark"] body .role-new { background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .role-description, html[data-theme="dark"] body .role-name { color: var(--ink); background: var(--field); border-color: var(--line); }
</style>
<main class="roles-shell">
    <h1>Roles &amp; permissions</h1>
    <p class="lead">Grant only the access each role needs. Changes are enforced on server routes as well as navigation links.</p>
    @if (session('success'))<p role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())<ul class="role-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

    <section class="role-new">
        <h2>Create a role</h2>
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <label for="new-role-name">Role name</label>
            <input class="role-name" id="new-role-name" name="name" value="{{ old('name') }}" maxlength="100" required>
            <label for="new-role-description">Description</label>
            <input class="role-description" id="new-role-description" name="description" value="{{ old('description') }}" maxlength="1000">
            <div class="permission-list">
                @foreach ($permissions as $permission)
                    <label><input type="checkbox" name="permissions[]" value="{{ $permission->id }}"><span>{{ $permission->display_name }}</span></label>
                @endforeach
            </div>
            <button class="role-save" type="submit">Create role</button>
        </form>
    </section>

    <div class="roles-grid">
        @foreach ($roles as $role)
            @php($assignedPermissionIds = $assignments->get($role->id, collect())->pluck('permission_id')->map(fn ($id) => (int) $id)->all())
            <section class="role-card">
                <h2>{{ $role->display_name }}</h2>
                <p>Users with this role keep their existing assignments. Update the description and permissions below.</p>
                <form method="POST" action="{{ route('roles.update', $role->id) }}">
                    @csrf
                    @method('PUT')
                    <label for="role-description-{{ $role->id }}">Description</label>
                    <input class="role-description" id="role-description-{{ $role->id }}" name="description" value="{{ $role->description }}" maxlength="1000">
                    <div class="permission-list">
                        @foreach ($permissions as $permission)
                            <label>
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array((int) $permission->id, $assignedPermissionIds, true))>
                                <span>{{ $permission->display_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <button class="role-save" type="submit">Save permissions</button>
                </form>
            </section>
        @endforeach
    </div>
</main>
@endsection
