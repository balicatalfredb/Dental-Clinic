@extends('layout.app')

@section('title', $dashboardRole.' Dashboard')

@section('content')
<style>
    body { margin: 0; color: #19324a; background: #f5f9fa; font-family: Georgia, 'Times New Roman', serif; }
    .appointments-dashboard { max-width: 1220px; margin: 0 auto; padding: 50px 5vw 70px; }
    .appointments-heading { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 30px; }
    .appointments-eyebrow { margin: 0 0 9px; color: #087f73; font: 700 11px Arial, sans-serif; letter-spacing: 1.8px; text-transform: uppercase; }
    .appointments-heading h1 { margin: 0; font-size: clamp(34px, 4vw, 52px); font-weight: 400; }
    .appointments-intro { margin: 11px 0 0; color: #708296; font: 14px/1.5 Arial, sans-serif; }
    .appointments-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .appointments-action { display: inline-block; padding: 11px 15px; color: white; background: #087f73; border-radius: 4px; font: 700 12px Arial, sans-serif; text-decoration: none; white-space: nowrap; }
    .appointments-action-secondary { color: #087f73; background: transparent; border: 1px solid #d5e0e7; }
    .appointments-summary, .appointments-panel { margin-bottom: 22px; background: white; border: 1px solid #e3ebf1; }
    .appointments-summary { display: flex; align-items: center; gap: 18px; padding: 22px 26px; border-left: 4px solid #087f73; }
    .appointments-summary strong { font-size: 32px; font-weight: 400; }
    .appointments-summary span { color: #708296; font: 13px Arial, sans-serif; }
    .appointments-panel { padding: 24px 26px; }
    .appointments-panel h2 { margin: 0 0 16px; font-size: 23px; font-weight: 400; }
    .appointment-row { display: grid; grid-template-columns: 145px minmax(0, 1fr) auto; gap: 18px; align-items: center; padding: 17px 0; border-top: 1px solid #e3ebf1; }
    .appointment-time { color: #087f73; font: 700 13px/1.5 Arial, sans-serif; }
    .appointment-time span, .appointment-time strong { display: block; }
    .appointment-time strong { font-size: 12px; }
    .appointment-details strong { display: block; margin-bottom: 5px; font-size: 16px; font-weight: 400; }
    .appointment-details span { color: #708296; font: 12px Arial, sans-serif; }
    .appointment-status { padding: 6px 9px; color: #087f73; background: #dff4ed; border-radius: 3px; font: 11px Arial, sans-serif; white-space: nowrap; }
    .appointments-empty { margin: 0; color: #708296; font: 14px/1.5 Arial, sans-serif; }
    html[data-theme="dark"] body .appointments-summary, html[data-theme="dark"] body .appointments-panel { color: var(--ink); background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .appointments-intro, html[data-theme="dark"] body .appointments-summary span, html[data-theme="dark"] body .appointment-details span, html[data-theme="dark"] body .appointments-empty { color: var(--muted); }
    html[data-theme="dark"] body .appointment-row { border-color: var(--line); }
    html[data-theme="dark"] body .appointment-status { color: #a4e1d3; background: #21463f; }
    @media (max-width: 760px) {
        .appointments-dashboard { padding: 35px 20px 50px; }
        .appointments-heading { align-items: start; flex-direction: column; }
    }
    @media (max-width: 520px) {
        .appointment-row { grid-template-columns: 1fr auto; gap: 9px 14px; }
        .appointment-time { grid-column: 1 / -1; }
        .appointments-panel { padding: 20px 16px; }
    }
</style>

@include('layout.navigation')

<main class="appointments-dashboard">
    <header class="appointments-heading">
        <div>
            <p class="appointments-eyebrow">{{ $dashboardRole }} dashboard</p>
            <h1>Welcome, {{ Str::before($user->name, ' ') }}.</h1>
            <p class="appointments-intro">
                @if ($isPatient)
                    Keep track of your upcoming visits and book your next appointment.
                @elseif ($isClinician)
                    Review the appointments assigned to you.
                @else
                    Review and manage the clinic's upcoming appointments.
                @endif
            </p>
        </div>
        <div class="appointments-actions">
            <a class="appointments-action appointments-action-secondary" href="{{ route('appointments.index') }}">View appointments</a>
            @if ($canBookAppointments)
                <a class="appointments-action" href="{{ route('appointments.create') }}">Book appointment</a>
            @endif
        </div>
    </header>

    <section class="appointments-summary" aria-label="Upcoming appointment count">
        <strong>{{ $upcomingCount }}</strong>
        <span>upcoming {{ \Illuminate\Support\Str::plural('appointment', $upcomingCount) }}</span>
    </section>

    <section class="appointments-panel">
        <h2>Coming up</h2>
        @forelse ($upcomingAppointments as $appointment)
            @php($startsAt = $appointment->starts_at->copy()->timezone('Asia/Manila'))
            <article class="appointment-row">
                <div class="appointment-time">
                    <span>{{ $startsAt->format('D, M j, Y') }}</span>
                    <strong>{{ $startsAt->format('g:i A') }} PHT</strong>
                </div>
                <div class="appointment-details">
                    @if ($isPatient)
                        <strong>With {{ $appointment->clinician?->name ?? 'Former clinician' }}</strong>
                    @elseif ($isClinician)
                        <strong>{{ $appointment->patient?->name ?? 'Former patient' }}</strong>
                    @else
                        <strong>{{ $appointment->patient?->name ?? 'Former patient' }}</strong>
                        <span>Clinician: {{ $appointment->clinician?->name ?? 'Former clinician' }}</span>
                    @endif
                    @if ($isPatient || $isClinician)
                        <span>{{ $appointment->appointment_type }}</span>
                    @endif
                </div>
                <span class="appointment-status">{{ $appointment->status }}</span>
            </article>
        @empty
            <p class="appointments-empty">
                @if ($isPatient)
                    You don't have any upcoming appointments yet. Book an appointment when you're ready.
                @elseif ($isClinician)
                    You don't have any upcoming appointments assigned to you.
                @else
                    There are no upcoming clinic appointments.
                @endif
            </p>
        @endforelse
    </section>
</main>
@endsection
