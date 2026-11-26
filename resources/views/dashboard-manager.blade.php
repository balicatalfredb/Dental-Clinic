@extends('layout.app')

@section('title', 'Management Dashboard')

@section('content')
<style>
    body { margin: 0; color: #19324a; background: #f5f9fa; font-family: Georgia, 'Times New Roman', serif; }
    .manager-dashboard { max-width: 1220px; margin: 0 auto; padding: 50px 5vw 70px; }
    .manager-heading { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 30px; }
    .manager-eyebrow { margin: 0 0 9px; color: #087f73; font: 700 11px Arial, sans-serif; letter-spacing: 1.8px; text-transform: uppercase; }
    .manager-heading h1 { margin: 0; font-size: clamp(34px, 4vw, 52px); font-weight: 400; }
    .manager-period { color: #708296; font: 14px Arial, sans-serif; }
    .manager-filter { display: flex; flex-wrap: wrap; align-items: end; gap: 10px; margin-bottom: 28px; padding: 14px; background: white; border: 1px solid #e3ebf1; font: 12px Arial, sans-serif; }
    .manager-filter label { display: grid; gap: 5px; color: #708296; font-weight: 700; }
    .manager-filter input { min-height: 36px; box-sizing: border-box; padding: 7px 9px; border: 1px solid #e3ebf1; border-radius: 4px; }
    .manager-filter button, .manager-link { min-height: 36px; padding: 10px 13px; color: white; background: #087f73; border: 0; border-radius: 4px; cursor: pointer; font: 700 12px Arial, sans-serif; text-decoration: none; }
    .manager-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 28px; }
    .manager-stat, .manager-panel { padding: 22px; background: white; border: 1px solid #e3ebf1; }
    .manager-stat { border-top: 4px solid #087f73; }
    .manager-stat:nth-child(2) { border-top-color: #ef806d; }
    .manager-stat:nth-child(3) { border-top-color: #d5a83b; }
    .manager-stat:nth-child(4) { border-top-color: #6286a0; }
    .manager-stat span { display: block; color: #708296; font: 12px Arial, sans-serif; }
    .manager-stat strong { display: block; margin-top: 9px; font-size: 31px; font-weight: 400; }
    .manager-panel h2 { margin: 0 0 12px; font-size: 23px; font-weight: 400; }
    .manager-panel p { margin: 0 0 18px; color: #708296; font: 14px/1.5 Arial, sans-serif; }
    .manager-links { display: flex; flex-wrap: wrap; gap: 10px; }
    .manager-errors { margin-bottom: 20px; color: #a74435; font: 13px Arial, sans-serif; }
    html[data-theme="dark"] body .manager-stat, html[data-theme="dark"] body .manager-panel, html[data-theme="dark"] body .manager-filter { color: var(--ink); background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .manager-period, html[data-theme="dark"] body .manager-stat span, html[data-theme="dark"] body .manager-panel p { color: var(--muted); }
    @media (max-width: 760px) {
        .manager-dashboard { padding: 35px 20px 50px; }
        .manager-heading { align-items: start; flex-direction: column; }
        .manager-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 460px) { .manager-stats { grid-template-columns: 1fr; } }
</style>

@include('layout.navigation')

<main class="manager-dashboard">
    <header class="manager-heading">
        <div>
            <p class="manager-eyebrow">Management overview</p>
            <h1>Welcome, {{ Str::before($user->name, ' ') }}.</h1>
        </div>
        <span class="manager-period">{{ $periodStart->format('M j, Y') }} - {{ $periodEnd->format('M j, Y') }}</span>
    </header>

    <form class="manager-filter" method="GET" action="{{ route('dashboard') }}">
        <label for="managerFrom">From <input id="managerFrom" name="from" type="date" value="{{ request('from', $periodStart->toDateString()) }}"></label>
        <label for="managerTo">To <input id="managerTo" name="to" type="date" value="{{ request('to', $periodEnd->toDateString()) }}"></label>
        <button type="submit">Apply date range</button>
    </form>

    @if ($errors->any())
        <ul class="manager-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    @endif

    @if ($canViewAudit)
        <section class="manager-stats" aria-label="Audit activity summary">
            <article class="manager-stat"><span>Transactions</span><strong>{{ $totalTransactions }}</strong></article>
            <article class="manager-stat"><span>Audit records</span><strong>{{ $auditRecords }}</strong></article>
            <article class="manager-stat"><span>Successful logins</span><strong>{{ $successfulLogins }}</strong></article>
            <article class="manager-stat"><span>Failed logins</span><strong>{{ $failedLogins }}</strong></article>
        </section>
    @endif

    <section class="manager-panel">
        <h2>Management tools</h2>
        <p>Open the information available to your role.</p>
        <div class="manager-links">
            @if ($canManageUsers)
                <a class="manager-link" href="{{ route('users.index') }}">Manage users</a>
            @endif
            @if ($canViewReports)
                <a class="manager-link" href="{{ route('reports.index') }}">View reports</a>
            @endif
            @if ($canViewAudit)
                <a class="manager-link" href="{{ route('audit.index') }}">View audit trail</a>
            @endif
        </div>
    </section>
</main>
@endsection
