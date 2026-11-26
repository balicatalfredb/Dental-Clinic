@extends('layout.app')
<style>
  :root {
    --ink: #19324a;
    --muted: #708296;
    --line: #e3ebf1;
    --mint: #dff4ed;
    --teal: #087f73;
    --navy: #123752;
    --coral: #ef806d;
    --paper: #f5f9fa;
  }

  body {
    margin: 0;
    color: var(--ink);
    background: var(--paper);
    font-family: Georgia, 'Times New Roman', serif;
  }

  .dental-nav {
    display: flex;
    align-items: center;
    gap: 42px;
    min-height: 74px;
    padding: 0 5vw;
    color: white;
    background: var(--navy);
  }

  .brand {
    color: white;
    font-size: 25px;
    font-weight: 700;
    letter-spacing: -.5px;
    text-decoration: none;
  }

  .brand-mark {
    display: inline-grid;
    width: 34px;
    height: 34px;
    margin-right: 9px;
    color: var(--navy);
    background: var(--mint);
    border-radius: 50%;
    place-items: center;
    font-family: Arial, sans-serif;
    font-size: 19px;
    font-weight: 700;
  }

  .nav-links {
    display: flex;
    align-items: center;
    gap: 26px;
    height: 74px;
    margin: 0;
    padding: 0;
    list-style: none;
    font-family: Arial, sans-serif;
  }

  .nav-links a {
    position: relative;
    display: flex;
    align-items: center;
    color: #b9cbd5;
    font-size: 14px;
    text-decoration: none;
    transition: color .2s ease;
  }

  .nav-links a.active,
  .nav-links a:hover { color: white; }

  .nav-links a::after { position: absolute; right: 0; bottom: 0; left: 0; height: 3px; content: ''; background: var(--coral); transform: scaleX(0); transform-origin: center; transition: transform .2s ease; }
  .nav-links a.active::after, .nav-links a:hover::after { transform: scaleX(1); }

  .nav-spacer { flex: 1; }

  .clinic-status {
    color: #c8e8df;
    font: 12px Arial, sans-serif;
  }

  .logout-btn {
    padding: 10px 16px;
    color: white;
    background: transparent;
    border: 1px solid #7893a2;
    border-radius: 5px;
    cursor: pointer;
    font: 13px Arial, sans-serif;
  }

  .logout-btn:hover { background: #204963; }

  .dashboard-shell {
    max-width: 1220px;
    margin: 0 auto;
    padding: 50px 5vw 70px;
  }

  .eyebrow {
    margin: 0 0 10px;
    color: var(--teal);
    font: 700 11px Arial, sans-serif;
    letter-spacing: 1.8px;
    text-transform: uppercase;
  }

  .welcome-row {
    display: flex;
    align-items: end;
    justify-content: space-between;
    margin-bottom: 32px;
  }

  h1 {
    margin: 0;
    font-size: clamp(34px, 4vw, 52px);
    font-weight: 400;
    letter-spacing: -1.5px;
  }

  .date-label { color: var(--muted); font: 14px Arial, sans-serif; }
  .date-filter { display: flex; flex-wrap: wrap; gap: 10px; align-items: end; margin: -12px 0 28px; padding: 14px; background: white; border: 1px solid var(--line); font: 12px Arial, sans-serif; }
  .date-filter label { display: grid; gap: 5px; color: var(--muted); font-weight: 700; }
  .date-filter input { min-height: 36px; box-sizing: border-box; padding: 7px 9px; color: var(--ink); background: white; border: 1px solid var(--line); border-radius: 4px; }
  .date-filter button, .date-filter a { min-height: 36px; box-sizing: border-box; padding: 10px 13px; color: white; background: var(--teal); border: 0; border-radius: 4px; text-decoration: none; cursor: pointer; font: 700 12px Arial, sans-serif; }
  .date-filter a { color: var(--teal); background: transparent; border: 1px solid var(--line); }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 34px;
  }

  .stat-card {
    padding: 22px;
    background: white;
    border: 1px solid var(--line);
    border-top: 4px solid var(--teal);
    box-shadow: 0 8px 24px rgba(25, 50, 74, .04);
  }

  .stat-card:nth-child(2) { border-top-color: var(--coral); }
  .stat-card:nth-child(3) { border-top-color: #d5a83b; }
  .stat-card:nth-child(4) { border-top-color: #6286a0; }
  .stat-label { color: var(--muted); font: 12px Arial, sans-serif; }
  .stat-number { display: block; margin: 9px 0 4px; font-size: 31px; }
  .stat-note { color: var(--teal); font: 12px Arial, sans-serif; }

  .content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(290px, .85fr);
    gap: 22px;
  }

  .panel {
    padding: 26px;
    background: white;
    border: 1px solid var(--line);
  }

  .panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
  }

  h2 { margin: 0; font-size: 23px; font-weight: 400; }
  .panel-link { padding: 0; color: var(--teal); background: transparent; border: 0; cursor: pointer; font: 12px Arial, sans-serif; text-decoration: none; }
  .appointment { display: grid; grid-template-columns: 72px 1fr auto; gap: 16px; align-items: center; padding: 16px 0; border-top: 1px solid var(--line); }
  .appointment-time { color: var(--teal); font: 700 13px Arial, sans-serif; }
  .patient-name { margin: 0 0 5px; font-size: 17px; }
  .patient-detail { color: var(--muted); font: 12px Arial, sans-serif; }
  .appointment-type { padding: 6px 9px; color: var(--teal); background: var(--mint); border-radius: 3px; font: 11px Arial, sans-serif; }
  .task { display: flex; gap: 12px; padding: 15px 0; border-top: 1px solid var(--line); font: 14px Arial, sans-serif; }
  .task-marker { width: 9px; height: 9px; margin-top: 4px; border: 2px solid var(--coral); border-radius: 50%; }
  .task small { display: block; margin-top: 4px; color: var(--muted); font-size: 11px; }
  #notice { position: fixed; right: 24px; bottom: 24px; z-index: 2; padding: 13px 17px; color: white; background: var(--navy); border-radius: 4px; font: 13px Arial, sans-serif; opacity: 0; transition: opacity .2s; }
  #notice.notice-visible { opacity: 1; }

  @media (max-width: 800px) {
    .dental-nav { gap: 18px; padding: 0 20px; }
    .nav-links { gap: 14px; }
    .clinic-status { display: none; }
    .dashboard-shell { padding: 35px 20px 50px; }
    .welcome-row { align-items: start; flex-direction: column; gap: 12px; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .content-grid { grid-template-columns: 1fr; }
  }

  @media (max-width: 520px) {
    .dental-nav { flex-wrap: wrap; height: auto; padding: 14px 20px; }
    .nav-links { order: 3; width: 100%; height: 40px; }
    .nav-links a::after { display: none; }
    .logout-btn { margin-left: auto; }
    .stats-grid { grid-template-columns: 1fr; }
    .panel { padding: 20px 16px; }
    .appointment { grid-template-columns: 58px 1fr; }
    .appointment-type { grid-column: 2; justify-self: start; }
  }
  html[data-theme="dark"] body .date-filter { background: var(--surface); border-color: var(--line); }
  html[data-theme="dark"] body .date-filter label { color: var(--muted); }
  html[data-theme="dark"] body .date-filter input { color: var(--ink); background: var(--field); border-color: var(--line); }
  html[data-theme="dark"] body .date-filter a { color: #a4e1d3; border-color: var(--line); }
 </style>

@include('layout.navigation')

<main class="dashboard-shell">
  <div class="welcome-row">
    <div>
      <p class="eyebrow">{{ now()->format('l, F j, Y') }}</p>
      <h1>Welcome, {{ Str::before($user->name, ' ') }}.</h1>
    </div>
    <span class="date-label">Activity from {{ $periodStart->format('M j, Y') }} to {{ $periodEnd->format('M j, Y') }}</span>
  </div>

  <form class="date-filter" method="GET" action="{{ route('dashboard') }}">
    <label for="dashboardFrom">From <input id="dashboardFrom" name="from" type="date" value="{{ request('from', $periodStart->toDateString()) }}"></label>
    <label for="dashboardTo">To <input id="dashboardTo" name="to" type="date" value="{{ request('to', $periodEnd->toDateString()) }}"></label>
    <button type="submit">Apply date range</button>
    <a href="{{ route('dashboard') }}">Last 30 days</a>
  </form>
  @if ($errors->any())
    <ul class="form-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  @endif

  <section class="stats-grid" aria-label="Practice overview">
    <article class="stat-card"><span class="stat-label">Team members</span><strong class="stat-number">{{ $totalUsers }}</strong><span class="stat-note">All registered accounts</span></article>
    <article class="stat-card"><span class="stat-label">Active accounts</span><strong class="stat-number">{{ $activeUsers }}</strong><span class="stat-note">Currently enabled</span></article>
    <article class="stat-card"><span class="stat-label">Invitations</span><strong class="stat-number">{{ $invitedUsers }}</strong><span class="stat-note">Awaiting activation</span></article>
    <article class="stat-card"><span class="stat-label">Added this month</span><strong class="stat-number">{{ $newUsersThisMonth }}</strong><span class="stat-note">New team accounts</span></article>
  </section>

  <section class="content-grid">
    <article class="panel">
      <div class="panel-heading"><h2>Account growth</h2><span class="patient-detail">New accounts in selected period</span></div>
      <div style="position:relative;height:280px"><canvas id="userGrowth" aria-label="New user accounts by month" role="img"></canvas></div>
    </article>
    <article class="panel">
      <div class="panel-heading"><h2>Recently added</h2>@can('manage-users')<a class="panel-link" href="{{ route('users.index') }}">Manage users &gt;</a>@endcan</div>
      @forelse ($recentUsers as $recentUser)
        <div class="task"><span class="task-marker"></span><div>{{ $recentUser->name }}<small>{{ $recentUser->email }} · {{ $recentUser->created_at->diffForHumans() }}</small></div></div>
      @empty
        <p class="patient-detail">No accounts have been created yet.</p>
      @endforelse
    </article>
  </section>

  <section class="stats-grid" aria-label="Audit and backup overview">
    <article class="stat-card"><span class="stat-label">Transactions</span><strong class="stat-number">{{ $totalTransactions }}</strong></article>
    <article class="stat-card"><span class="stat-label">Audit records</span><strong class="stat-number">{{ $auditRecords }}</strong></article>
    <article class="stat-card"><span class="stat-label">Successful logins</span><strong class="stat-number">{{ $successfulLogins }}</strong></article>
    <article class="stat-card"><span class="stat-label">Failed logins</span><strong class="stat-number">{{ $failedLogins }}</strong></article>
    <article class="stat-card"><span class="stat-label">Database backups</span><strong class="stat-number">{{ $backupCount }}</strong></article>
  </section>

  <section class="content-grid">
    <article class="panel"><div class="panel-heading"><h2>Role distribution</h2></div><div style="height:260px"><canvas id="roleChart"></canvas></div></article>
    <article class="panel"><div class="panel-heading"><h2>Account status</h2></div><div style="height:260px"><canvas id="statusChart"></canvas></div></article>
  </section>
</main>
<div id="notice" role="status" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
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

  new Chart(document.getElementById('userGrowth'), {
    type: 'line',
    data: {
      labels: @json($dailyUsers->pluck('label')->all()),
      datasets: [{ label: 'New accounts', data: @json($dailyUsers->pluck('total')->all()), borderColor: '#087f73', backgroundColor: 'rgba(8,127,115,.14)', fill: true, tension: .25 }],
    },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
  });

  new Chart(document.getElementById('roleChart'), {
    type: 'pie',
    data: {
      labels: @json($roleDistribution->pluck('role')->all()),
      datasets: [{ data: @json($roleDistribution->pluck('total')->all()), backgroundColor: ['#087f73','#ef806d','#d5a83b','#6286a0'] }],
    },
  });

  new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
      labels: ['Active','Invited','Inactive'],
      datasets: [{ data: [@json($activeUsers), @json($invitedUsers), @json($inactiveUsers)], backgroundColor: ['#087f73','#d5a83b','#ef806d'] }],
    },
  });
</script>
