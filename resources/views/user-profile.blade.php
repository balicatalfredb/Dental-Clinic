@extends('layout.app')

@section('title', 'User Management - Dental')

<style>
    :root { --navy: #123752; --teal: #087f73; --mint: #dff4ed; --coral: #ef806d; --ink: #19324a; --muted: #708296; --line: #e3ebf1; --paper: #f5f9fa; }
    body { margin: 0; color: var(--ink); background: var(--paper); font-family: Georgia, 'Times New Roman', serif; }
    .dental-nav { display: flex; align-items: center; gap: 42px; min-height: 74px; padding: 0 5vw; color: white; background: var(--navy); }
    .brand { color: white; font-size: 25px; font-weight: 700; text-decoration: none; }
    .brand-mark { display: inline-grid; width: 34px; height: 34px; margin-right: 9px; color: var(--navy); background: var(--mint); border-radius: 50%; place-items: center; font: 700 19px Arial, sans-serif; }
    .nav-links { display: flex; align-items: center; gap: 26px; height: 74px; margin: 0; padding: 0; list-style: none; font-family: Arial, sans-serif; }
    .nav-links a { position: relative; display: flex; align-items: center; color: #b9cbd5; font-size: 14px; text-decoration: none; transition: color .2s ease; }
    .nav-links a.active, .nav-links a:hover { color: white; }
    .nav-links a::after { position: absolute; right: 0; bottom: 0; left: 0; height: 3px; content: ''; background: var(--coral); transform: scaleX(0); transform-origin: center; transition: transform .2s ease; }
    .nav-links a.active::after, .nav-links a:hover::after { transform: scaleX(1); }
    .nav-spacer { flex: 1; }
    .logout-btn { padding: 10px 16px; color: white; background: transparent; border: 1px solid #7893a2; border-radius: 5px; cursor: pointer; font: 13px Arial, sans-serif; }
    .page-shell { max-width: 1220px; margin: 0 auto; padding: 50px 5vw 70px; }
    .heading-row { display: flex; align-items: end; justify-content: space-between; margin-bottom: 30px; }
    .eyebrow { margin: 0 0 9px; color: var(--teal); font: 700 11px Arial, sans-serif; letter-spacing: 1.8px; text-transform: uppercase; }
    h1 { margin: 0; font-size: clamp(34px, 4vw, 52px); font-weight: 400; letter-spacing: -1.5px; }
    .heading-copy { color: var(--muted); font: 14px Arial, sans-serif; }
    .add-btn { padding: 12px 17px; color: white; background: var(--teal); border: 0; border-radius: 4px; cursor: pointer; font: 700 12px Arial, sans-serif; }
    .table-panel { overflow: hidden; background: white; border: 1px solid var(--line); }
    .table-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 20px; padding: 20px 24px; border-bottom: 1px solid var(--line); }
    .table-title { margin: 0; font-size: 21px; font-weight: 400; }
    .search { width: 220px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 3px; font: 13px Arial, sans-serif; }
    .table-scroll { overflow-x: auto; }
    table { width: 100%; min-width: 760px; border-collapse: collapse; font-family: Arial, sans-serif; }
    th { padding: 14px 24px; color: var(--muted); background: #f8fbfb; font-size: 10px; letter-spacing: 1px; text-align: left; text-transform: uppercase; }
    td { padding: 18px 24px; border-top: 1px solid var(--line); font-size: 13px; }
    .user-cell { display: flex; align-items: center; gap: 12px; }
    .avatar { display: grid; width: 36px; height: 36px; color: var(--teal); background: var(--mint); border-radius: 50%; place-items: center; font-weight: 700; }
    .user-name { display: block; margin-bottom: 3px; font-family: Georgia, 'Times New Roman', serif; font-size: 16px; }
    .subtext { color: var(--muted); font-size: 11px; }
    .role { display: inline-block; padding: 6px 9px; color: var(--teal); background: var(--mint); border-radius: 3px; font-size: 11px; }
    .status { color: var(--teal); font-size: 12px; }
    .actions { padding: 0; color: var(--teal); background: transparent; border: 0; cursor: pointer; font: 12px Arial, sans-serif; text-decoration: underline; }
    .delete-action { margin-left: 10px; color: var(--coral); }
    #notice { position: fixed; right: 24px; bottom: 24px; z-index: 2; padding: 13px 17px; color: white; background: var(--navy); border-radius: 4px; font: 13px Arial, sans-serif; opacity: 0; transition: opacity .2s; }
    #notice.notice-visible { opacity: 1; }
    .edit-modal { position: fixed; inset: 0; z-index: 3; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(18, 55, 82, .56); backdrop-filter: blur(3px); }
    .edit-modal.open { display: flex; }
    .edit-panel { box-sizing: border-box; width: min(760px, 100%); max-height: calc(100vh - 32px); overflow-y: auto; background: white; border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 22px 65px rgba(18, 55, 82, .25); }
    .edit-header { position: sticky; top: 0; z-index: 1; display: flex; align-items: start; justify-content: space-between; padding: 22px 26px 18px; background: white; border-bottom: 1px solid var(--line); }
    .edit-header h2 { margin: 0 0 5px; font-size: 25px; font-weight: 400; }
    .close-edit { width: 34px; height: 34px; padding: 0; color: var(--muted); background: #f5f9fa; border: 1px solid var(--line); border-radius: 50%; cursor: pointer; font: 22px Arial, sans-serif; }
    .edit-body { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; padding: 22px 26px; }
    .edit-field { min-width: 0; margin: 0; }
    .edit-field label { display: block; margin-bottom: 7px; font: 700 12px Arial, sans-serif; }
    .edit-field input, .edit-field select { box-sizing: border-box; width: 100%; min-height: 43px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px; font: 13px Arial, sans-serif; }
    .edit-field input:focus, .edit-field select:focus { outline: 0; border-color: var(--teal); box-shadow: 0 0 0 4px rgba(8, 127, 115, .12); }
    .edit-footer { position: sticky; bottom: 0; display: flex; justify-content: end; gap: 10px; padding: 16px 26px; background: white; border-top: 1px solid var(--line); }
    .cancel-btn, .save-btn { min-height: 40px; padding: 10px 16px; border-radius: 6px; cursor: pointer; font: 700 12px Arial, sans-serif; transition: background .15s ease, transform .15s ease; }
    .cancel-btn { color: var(--teal); background: white; border: 1px solid var(--line); }
    .save-btn { color: white; background: var(--teal); border: 1px solid var(--teal); }
    .cancel-btn:hover, .save-btn:hover, .add-btn:hover { transform: translateY(-1px); }
    .save-btn:hover, .add-btn:hover { background: #06685f; }
    .edit-field-wide { grid-column: 1 / -1; }
    .edit-section-label { grid-column: 1 / -1; margin: 2px 0 -7px; color: var(--teal); font: 700 10px Arial, sans-serif; letter-spacing: 1px; text-transform: uppercase; }
    html[data-theme="dark"] body .edit-header, html[data-theme="dark"] body .edit-footer { background: #183448; }
    html[data-theme="dark"] body .close-edit { color: #b0c1cc; background: #102938; }
    html[data-theme="dark"] body .edit-section-label { color: #73d5c1; }
    .add-modal { z-index: 4; }
    @media (max-width: 800px) { .dental-nav { gap: 18px; padding: 0 20px; } .nav-links { gap: 14px; } .page-shell { padding: 35px 20px 50px; } .heading-row { align-items: start; flex-direction: column; gap: 18px; } }
    @media (max-width: 520px) { .dental-nav { flex-wrap: wrap; height: auto; padding: 14px 20px; } .nav-links { order: 3; width: 100%; height: 40px; } .nav-links a::after { display: none; } .logout-btn { margin-left: auto; } .table-toolbar { align-items: stretch; flex-direction: column; } .search { width: auto; } .edit-body { grid-template-columns: 1fr; padding: 18px; } .edit-field-wide, .edit-section-label { grid-column: auto; } .edit-header { padding: 18px; } .edit-footer { padding: 14px 18px; } }
</style>

@include('layout.navigation')

<main class="page-shell">
    <div class="heading-row">
        <div><p class="eyebrow">Practice administration</p><h1>User management</h1><p class="heading-copy">Manage your dental team's access and responsibilities.</p></div>
        <button class="add-btn" type="button" onclick="openAddMember()">+ Add team member</button>
    </div>
    <section class="table-panel">
        <div class="table-toolbar"><h2 class="table-title">Team members <span class="subtext"><span id="activeCount">{{ $users->where('status', 'Active')->count() }}</span> active users</span></h2><input class="search" type="search" placeholder="Search team members" aria-label="Search team members" oninput="filterUsers(this.value)"></div>
        <div class="table-scroll"><table><thead><tr><th>Team member</th><th>Role</th><th>Contact</th><th>Joined</th><th>Status</th><th>Action</th></tr></thead><tbody id="userRows">
            @forelse ($users as $user)
                @include('users.row', ['user' => $user])
            @empty
                <tr><td colspan="6" class="subtext">No team members found.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</main>
<div id="notice" role="status" aria-live="polite"></div>

<div id="editModal" class="edit-modal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
    <form id="editForm" class="edit-panel" method="POST">
        @csrf
        @method('PUT')
        <div class="edit-header"><div><h2 id="editTitle">Edit team member</h2><span class="subtext">Update this team member's details.</span></div><button class="close-edit" type="button" aria-label="Close edit panel" onclick="closeEdit()">&times;</button></div>
        <div class="edit-body">
            <p class="edit-section-label">Profile</p>
            <div class="edit-field"><label for="editName">Full name</label><input id="editName" name="name" autocomplete="name" required></div>
            <div class="edit-field"><label for="editDepartment">Department</label><select id="editDepartment" name="department" required><option>General Dentistry</option><option>Orthodontics</option><option>Dental Hygiene</option><option>Administration</option><option>Reception</option></select></div>
            <div class="edit-field"><label for="editEmail">Email address</label><input id="editEmail" name="email" type="email" autocomplete="email" required></div>
            <div class="edit-field"><label for="editPhone">Phone number</label><input id="editPhone" name="phone" type="tel" autocomplete="tel" required></div>
            <p class="edit-section-label">Access</p>
            <div class="edit-field"><label for="editRole">Role</label><select id="editRole" name="role" required>@foreach ($roles as $role)<option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>@endforeach</select></div>
            <div class="edit-field"><label for="editStatus">Account status</label><select id="editStatus" name="status" required><option>Active</option><option>Invited</option><option>Paused</option></select></div>
        </div>
        <div class="edit-footer"><button class="cancel-btn" type="button" onclick="closeEdit()">Cancel</button><button class="save-btn" type="submit">Save changes</button></div>
    </form>
</div>

<div id="addModal" class="edit-modal add-modal" role="dialog" aria-modal="true" aria-labelledby="addMemberTitle">
    <form id="addForm" class="edit-panel" action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="edit-header"><div><h2 id="addMemberTitle">Add team member</h2><span class="subtext">Create a new practice team profile.</span></div><button class="close-edit" type="button" aria-label="Close add panel" onclick="closeAddMember()">&times;</button></div>
        <div class="edit-body">
            <p class="edit-section-label">Profile</p>
            <div class="edit-field"><label for="addName">Full name</label><input id="addName" name="name" autocomplete="name" required></div>
            <div class="edit-field"><label for="addDepartment">Department</label><select id="addDepartment" name="department" required><option value="">Select department</option><option>General Dentistry</option><option>Orthodontics</option><option>Dental Hygiene</option><option>Administration</option><option>Reception</option></select></div>
            <div class="edit-field"><label for="addEmail">Email address</label><input id="addEmail" name="email" type="email" autocomplete="email" required></div>
            <div class="edit-field"><label for="addPhone">Phone number</label><input id="addPhone" name="phone" type="tel" placeholder="+1 999 444 555" autocomplete="tel" required></div>
            <p class="edit-section-label">Access</p>
            <div class="edit-field"><label for="addRole">Role</label><select id="addRole" name="role" required>@foreach ($roles as $role)<option value="{{ $role }}" @selected($role === 'Staff')>{{ $role }}</option>@endforeach</select></div>
            <div class="edit-field"><label for="addStatus">Account status</label><select id="addStatus" name="status" required><option>Invited</option><option>Active</option></select></div>
            <div class="edit-field"><label for="addPassword">Initial password</label><div class="password-field"><input id="addPassword" name="password" type="password" minlength="8" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle="addPassword" aria-label="Show initial password" aria-pressed="false">Show</button></div></div>
            <div class="edit-field"><label for="addPasswordConfirmation">Confirm password</label><div class="password-field"><input id="addPasswordConfirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle="addPasswordConfirmation" aria-label="Show confirm password" aria-pressed="false">Show</button></div></div>
        </div>
        <div class="edit-footer"><button class="cancel-btn" type="button" onclick="closeAddMember()">Cancel</button><button class="save-btn" type="submit">Add member</button></div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    function logoutUser() {
        sessionStorage.clear();
        localStorage.clear();
        window.location.href = '/';
    }

    function showNotice(message) {
        const notice = document.getElementById('notice');
        notice.textContent = message;
        notice.className = 'notice-visible';
        window.setTimeout(() => notice.className = '', 2500);
    }

    function filterUsers(query) {
        const search = query.toLowerCase();
        document.querySelectorAll('#userRows tr').forEach((row) => {
            row.hidden = !row.textContent.toLowerCase().includes(search);
        });
    }

    function openEdit(button) {
        document.getElementById('editForm').action = button.dataset.url;
        document.getElementById('editName').value = button.dataset.name;
        document.getElementById('editDepartment').value = button.dataset.department;
        document.getElementById('editEmail').value = button.dataset.email;
        document.getElementById('editPhone').value = button.dataset.phone || '';
        document.getElementById('editRole').value = button.dataset.role;
        document.getElementById('editStatus').value = button.dataset.status;
        document.getElementById('editModal').classList.add('open');
        document.getElementById('editName').focus();
    }

    function closeEdit() {
        document.getElementById('editModal').classList.remove('open');
    }

    function openAddMember() {
        document.getElementById('addModal').classList.add('open');
        document.getElementById('addName').focus();
    }

    function closeAddMember() {
        const form = document.getElementById('addForm');
        form.reset();
        form.querySelectorAll('[data-password-toggle]').forEach((button) => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (input) input.type = 'password';
            button.textContent = 'Show';
            button.setAttribute('aria-pressed', 'false');
        });
        document.getElementById('addModal').classList.remove('open');
    }

    function refreshActiveCount() {
        document.getElementById('activeCount').textContent = document.querySelectorAll('#userRows .status[data-status="Active"]').length;
    }

    function showRequestError(xhr) {
        const errors = xhr.responseJSON?.errors;
        const message = errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'The request could not be completed.');
        showNotice(message);
    }

    $(document).on('click', '.js-edit-user', function () {
        openEdit(this);
    });

    $('#addForm, #editForm').on('submit', function (event) {
        event.preventDefault();
        const form = this;
        const submitButton = form.querySelector('[type="submit"]');
        submitButton.disabled = true;

        $.ajax({
            url: form.action,
            method: 'POST',
            data: $(form).serialize(),
            headers: { Accept: 'application/json' },
        }).done((result) => {
            const userId = $(result.row).attr('data-user-row');
            const existing = document.querySelector(`[data-user-row="${userId}"]`);
            if (existing) {
                existing.outerHTML = result.row;
            } else {
                $('#userRows tr:not([data-user-row])').remove();
                $('#userRows').append(result.row);
                form.reset();
            }
            refreshActiveCount();
            closeEdit();
            closeAddMember();
            showNotice(result.message);
        }).fail(showRequestError).always(() => {
            submitButton.disabled = false;
        });
    });

    $(document).on('submit', '.js-delete-user', function (event) {
        event.preventDefault();
        const form = this;
        if (!window.confirm('Remove this team member?')) return;

        $.ajax({
            url: form.action,
            method: 'POST',
            data: $(form).serialize(),
            headers: { Accept: 'application/json' },
        }).done((result) => {
            form.closest('[data-user-row]').remove();
            refreshActiveCount();
            showNotice(result.message);
        }).fail(showRequestError);
    });

</script>
