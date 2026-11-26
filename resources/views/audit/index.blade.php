@extends('layout.app')
@section('title', 'Audit Trail - Dental')
@section('content')
@include('layout.navigation')
<style>
    .audit-shell { max-width: 1280px; margin: 0 auto; padding: 42px 24px 64px; font-family: Arial, sans-serif; }
    .audit-heading { margin-bottom: 22px; }
    .audit-eyebrow { margin: 0 0 8px; color: #087f73; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
    .audit-heading h1 { margin: 0 0 8px; color: #19324a; font: 400 clamp(30px, 4vw, 42px) Georgia, serif; }
    .audit-heading p:last-child { margin: 0; color: #708296; font-size: 14px; }
    .audit-filter, .audit-table-panel { background: #fff; border: 1px solid #e3ebf1; border-radius: 10px; box-shadow: 0 8px 26px rgba(25, 50, 74, .045); }
    .audit-filter { margin-bottom: 20px; padding: 20px; }
    .audit-filter h2 { margin: 0 0 14px; color: #19324a; font: 400 20px Georgia, serif; }
    .audit-filter-fields { display: grid; grid-template-columns: minmax(210px, 1.5fr) repeat(3, minmax(130px, 1fr)); gap: 12px; align-items: end; }
    .audit-field { min-width: 0; }
    .audit-field label { display: block; margin-bottom: 6px; color: #344c60; font-size: 12px; font-weight: 700; }
    .audit-field input, .audit-field select { box-sizing: border-box; width: 100%; min-height: 42px; padding: 9px 11px; color: #19324a; background: #fff; border: 1px solid #d5e0e7; border-radius: 6px; font: 13px Arial, sans-serif; }
    .audit-field input:focus, .audit-field select:focus { outline: 0; border-color: #087f73; box-shadow: 0 0 0 4px rgba(8, 127, 115, .12); }
    .audit-filter-actions { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 15px; }
    .audit-button { display: inline-flex; min-height: 40px; box-sizing: border-box; align-items: center; justify-content: center; padding: 10px 15px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font: 700 12px Arial, sans-serif; text-decoration: none; transition: background .15s ease, transform .15s ease; }
    .audit-button:hover { transform: translateY(-1px); }
    .audit-button-primary { color: #fff; background: #087f73; }
    .audit-button-primary:hover { color: #fff; background: #06685f; }
    .audit-button-secondary { color: #087f73; background: #fff; border-color: #cbd9e1; }
    .audit-button-secondary:hover { color: #06685f; background: #f5f9fa; }
    .audit-errors { margin: 0 0 18px; padding: 12px 14px 12px 32px; color: #a74435; background: #fff0ed; border-radius: 6px; font-size: 13px; }
    .audit-table-heading { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; align-items: center; padding: 17px 20px; border-bottom: 1px solid #e3ebf1; }
    .audit-table-heading h2 { margin: 0; color: #19324a; font: 400 20px Georgia, serif; }
    .audit-count { color: #708296; font-size: 12px; }
    .audit-table-wrap { overflow-x: auto; }
    .audit-table { width: 100%; min-width: 780px; border-collapse: collapse; font-size: 13px; }
    .audit-table th { padding: 13px 16px; color: #708296; background: #f5f9fa; font-size: 10px; letter-spacing: .7px; text-align: left; text-transform: uppercase; }
    .audit-table td { padding: 14px 16px; color: #19324a; border-top: 1px solid #e8eef2; vertical-align: top; }
    .audit-event { display: inline-block; padding: 5px 8px; color: #087f73; background: #e4f5ef; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .audit-muted { color: #708296 !important; }
    .audit-empty { padding: 28px 16px !important; color: #708296 !important; text-align: center; }
    .audit-pagination { padding: 8px 18px 16px; }
    html[data-theme="dark"] body .audit-heading h1, html[data-theme="dark"] body .audit-filter h2, html[data-theme="dark"] body .audit-table-heading h2 { color: var(--ink); }
    html[data-theme="dark"] body .audit-heading p:last-child, html[data-theme="dark"] body .audit-count, html[data-theme="dark"] body .audit-muted, html[data-theme="dark"] body .audit-empty { color: var(--muted) !important; }
    html[data-theme="dark"] body .audit-filter, html[data-theme="dark"] body .audit-table-panel { background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .audit-field label { color: var(--ink); }
    html[data-theme="dark"] body .audit-field input, html[data-theme="dark"] body .audit-field select { color: var(--ink); background: var(--field); border-color: var(--line); }
    html[data-theme="dark"] body .audit-table th { color: var(--muted); background: var(--field); }
    html[data-theme="dark"] body .audit-table td { color: var(--ink); border-color: var(--line); }
    html[data-theme="dark"] body .audit-table-heading { border-color: var(--line); }
    html[data-theme="dark"] body .audit-event { color: #a4e1d3; background: #21463f; }
    html[data-theme="dark"] body .audit-button-secondary { color: #a4e1d3; background: var(--field); border-color: var(--line); }
    html[data-theme="dark"] body .audit-button-secondary:hover { color: #d3f4ea; background: var(--surface-raised); }
    html[data-theme="dark"] body .audit-errors { color: #ff9a8b; background: #432d31; }
    @media (max-width: 900px) { .audit-filter-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } .audit-field:first-child { grid-column: 1 / -1; } }
    @media (max-width: 560px) { .audit-shell { padding: 30px 16px 52px; } .audit-filter { padding: 16px; } .audit-filter-fields { grid-template-columns: 1fr; } .audit-field:first-child { grid-column: auto; } }
</style>

<main class="audit-shell">
    <header class="audit-heading">
        <p class="audit-eyebrow">Security</p>
        <h1>Audit trail</h1>
        <p>Review and filter recorded access, transactions, and errors.</p>
    </header>

    @if ($errors->any())<ul class="audit-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

    <section class="audit-filter" aria-labelledby="auditFilterTitle">
        <h2 id="auditFilterTitle">Filter activity</h2>
        <form method="GET" action="{{ route('audit.index') }}">
            <div class="audit-filter-fields">
                <div class="audit-field"><label for="auditSearch">Search</label><input id="auditSearch" name="search" value="{{ request('search') }}" placeholder="Description, user, or IP"></div>
                <div class="audit-field"><label for="auditEvent">Activity type</label><select id="auditEvent" name="event"><option value="">All activity</option><option value="access" @selected(request('event') === 'access')>Access</option><option value="transaction" @selected(request('event') === 'transaction')>Transaction</option><option value="error" @selected(request('event') === 'error')>Error</option></select></div>
                <div class="audit-field"><label for="auditFrom">From</label><input id="auditFrom" name="from" type="date" value="{{ request('from') }}"></div>
                <div class="audit-field"><label for="auditTo">To</label><input id="auditTo" name="to" type="date" value="{{ request('to') }}"></div>
            </div>
            <div class="audit-filter-actions">
                <button class="audit-button audit-button-primary" type="submit">Apply filters</button>
                <a class="audit-button audit-button-secondary" href="{{ route('audit.index') }}">Clear filters</a>
            </div>
        </form>
    </section>

    <section class="audit-table-panel" aria-label="Audit activity">
        <div class="audit-table-heading">
            <h2>Activity records</h2>
            <div>
                <span class="audit-count">Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} records</span>
                <a class="audit-button audit-button-secondary" href="{{ route('audit.export', request()->query()) }}">Export filtered CSV</a>
            </div>
        </div>
        <div class="audit-table-wrap">
            <table class="audit-table">
                <thead><tr><th>Time</th><th>Account</th><th>Event</th><th>Details</th><th>IP address</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr><td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td><td>{{ $log->user?->email ?? 'Guest' }}</td><td><span class="audit-event">{{ ucfirst($log->event) }}</span></td><td>{{ $log->description }}</td><td class="audit-muted">{{ $log->ip_address ?: '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="audit-empty">No activity matches these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="audit-pagination">{{ $logs->links() }}</div>
    </section>
</main>
@endsection
