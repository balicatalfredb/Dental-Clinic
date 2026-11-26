@extends('layout.app')
@section('title', 'Reports')
@section('content')
@include('layout.navigation')
<style>
    .reports-shell { max-width: 1200px; margin: 0 auto; padding: 42px 24px 70px; font-family: Arial, sans-serif; }
    .reports-heading { margin-bottom: 24px; }
    .reports-eyebrow { margin: 0 0 8px; color: #087f73; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
    .reports-heading h1 { margin: 0 0 8px; color: #19324a; font: 400 clamp(30px, 4vw, 42px) Georgia, serif; }
    .reports-heading p:last-child { margin: 0; color: #708296; font-size: 14px; }
    .reports-errors { margin: 0 0 18px; padding: 12px 14px 12px 32px; color: #a74435; background: #fff0ed; border-radius: 6px; font-size: 13px; }
    .reports-filter, .reports-table-panel { background: #fff; border: 1px solid #e3ebf1; border-radius: 10px; box-shadow: 0 8px 26px rgba(25, 50, 74, .045); }
    .reports-filter { margin-bottom: 20px; padding: 20px; }
    .reports-filter h2 { margin: 0 0 15px; color: #19324a; font: 400 20px Georgia, serif; }
    .reports-fields { display: grid; grid-template-columns: minmax(200px, 1.5fr) repeat(5, minmax(120px, 1fr)); gap: 12px; align-items: end; }
    .reports-field { min-width: 0; }
    .reports-field label { display: block; margin-bottom: 6px; color: #344c60; font-size: 12px; font-weight: 700; }
    .reports-field input, .reports-field select { box-sizing: border-box; width: 100%; min-height: 42px; padding: 9px 11px; color: #19324a; background: #fff; border: 1px solid #d5e0e7; border-radius: 6px; font: 13px Arial, sans-serif; }
    .reports-field input:focus, .reports-field select:focus { outline: 0; border-color: #087f73; box-shadow: 0 0 0 4px rgba(8, 127, 115, .12); }
    .reports-filter-actions, .reports-export-actions { display: flex; flex-wrap: wrap; gap: 9px; align-items: center; }
    .reports-filter-actions { margin-top: 15px; }
    .report-button { display: inline-flex; min-height: 40px; box-sizing: border-box; align-items: center; justify-content: center; gap: 7px; padding: 10px 14px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font: 700 12px Arial, sans-serif; text-decoration: none; transition: background .15s ease, border-color .15s ease, transform .15s ease; }
    .report-button:hover { transform: translateY(-1px); }
    .report-button-primary { color: #fff; background: #087f73; }
    .report-button-primary:hover { color: #fff; background: #06685f; }
    .report-button-secondary { color: #087f73; background: #fff; border-color: #cbd9e1; }
    .report-button-secondary:hover { color: #06685f; background: #f5f9fa; }
    .reports-table-heading { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 15px; align-items: center; padding: 18px 20px; border-bottom: 1px solid #e3ebf1; }
    .reports-table-heading h2 { margin: 0; color: #19324a; font: 400 20px Georgia, serif; }
    .reports-count { color: #708296; font-size: 12px; }
    .reports-export-actions { justify-content: flex-end; }
    .reports-table-scroll { overflow-x: auto; }
    .reports-table { width: 100%; min-width: 720px; border-collapse: collapse; font-size: 13px; }
    .reports-table th { padding: 13px 16px; color: #708296; background: #f5f9fa; font-size: 10px; letter-spacing: .7px; text-align: left; text-transform: uppercase; }
    .reports-table td { padding: 14px 16px; color: #19324a; border-top: 1px solid #e8eef2; }
    .reports-empty { padding: 26px 16px !important; color: #708296 !important; text-align: center; }
    .reports-pagination { padding: 8px 18px 16px; }
    html[data-theme="dark"] body .reports-heading h1, html[data-theme="dark"] body .reports-filter h2, html[data-theme="dark"] body .reports-table-heading h2 { color: var(--ink); }
    html[data-theme="dark"] body .reports-heading p:last-child, html[data-theme="dark"] body .reports-count, html[data-theme="dark"] body .reports-empty { color: var(--muted); }
    html[data-theme="dark"] body .reports-filter, html[data-theme="dark"] body .reports-table-panel { background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .reports-field label { color: var(--ink); }
    html[data-theme="dark"] body .reports-field input, html[data-theme="dark"] body .reports-field select { color: var(--ink); background: var(--field); border-color: var(--line); }
    html[data-theme="dark"] body .reports-table th { color: var(--muted); background: var(--field); }
    html[data-theme="dark"] body .reports-table td { color: var(--ink); border-color: var(--line); }
    html[data-theme="dark"] body .reports-table-heading { border-color: var(--line); }
    html[data-theme="dark"] body .reports-errors { color: #ff9a8b; background: #432d31; }
    html[data-theme="dark"] body .report-button-secondary { color: #a4e1d3; background: var(--field); border-color: var(--line); }
    html[data-theme="dark"] body .report-button-secondary:hover { color: #d3f4ea; background: var(--surface-raised); }
    @media (max-width: 850px) { .reports-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } .reports-field:first-child { grid-column: 1 / -1; } }
    @media (max-width: 560px) { .reports-shell { padding: 30px 16px 54px; } .reports-fields { grid-template-columns: 1fr; } .reports-field:first-child { grid-column: auto; } .reports-filter { padding: 16px; } .reports-table-heading { align-items: flex-start; flex-direction: column; } .reports-export-actions { justify-content: flex-start; } }
</style>

<main class="reports-shell">
    <header class="reports-heading">
        <p class="reports-eyebrow">Reporting</p>
        <h1>User reports</h1>
        <p>Search and filter live user records, then export the matching results.</p>
    </header>
    @if ($errors->any())<ul class="reports-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

    <section class="reports-filter" aria-labelledby="reportFilterTitle">
        <h2 id="reportFilterTitle">Filter users</h2>
        <form method="GET" action="{{ route('reports.index') }}">
            <div class="reports-fields">
                <div class="reports-field"><label for="reportSearch">Search</label><input id="reportSearch" name="search" value="{{ request('search') }}" placeholder="Name, email, or username"></div>
                <div class="reports-field"><label for="reportDepartment">Department</label><select id="reportDepartment" name="department"><option value="">All departments</option>@foreach (['General Dentistry','Orthodontics','Dental Hygiene','Administration','Reception'] as $department)<option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>@endforeach</select></div>
                <div class="reports-field"><label for="reportRole">Role</label><select id="reportRole" name="role"><option value="">All roles</option>@foreach ($roles as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>@endforeach</select></div>
                <div class="reports-field"><label for="reportStatus">Status</label><select id="reportStatus" name="status"><option value="">All statuses</option>@foreach (['Active','Invited','Paused'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
                <div class="reports-field"><label for="reportFrom">Created from</label><input id="reportFrom" name="from" type="date" value="{{ request('from') }}"></div>
                <div class="reports-field"><label for="reportTo">Created to</label><input id="reportTo" name="to" type="date" value="{{ request('to') }}"></div>
            </div>
            <div class="reports-filter-actions">
                <button class="report-button report-button-primary" type="submit">Apply filters</button>
                <a class="report-button report-button-secondary" href="{{ route('reports.index') }}">Clear filters</a>
            </div>
        </form>
    </section>

    <section class="reports-table-panel" aria-label="Filtered user report">
        <div class="reports-table-heading">
            <div><h2>Users</h2><span class="reports-count">Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} records</span></div>
            <div class="reports-export-actions">
                <a class="report-button report-button-secondary" href="{{ route('reports.export', ['format' => 'csv'] + request()->query()) }}">Download CSV for Excel</a>
                <a class="report-button report-button-secondary" href="{{ route('reports.export', ['format' => 'xlsx'] + request()->query()) }}">Download Excel workbook</a>
                <a class="report-button report-button-primary" href="{{ route('reports.export', ['format' => 'pdf'] + request()->query()) }}">Download PDF</a>
            </div>
        </div>
        <div class="reports-table-scroll">
            <table class="reports-table">
                <thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->department ?: '—' }}</td><td>{{ $user->role }}</td><td>{{ $user->status }}</td></tr>
                    @empty
                        <tr><td class="reports-empty" colspan="5">No users match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="reports-pagination">{{ $users->links() }}</div>
    </section>
</main>
@endsection
