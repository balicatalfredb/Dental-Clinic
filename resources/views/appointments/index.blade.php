@extends('layout.app')

@section('title', 'Appointments - '.($systemBrand['name'] ?? 'Dental'))

@section('content')
@include('layout.navigation')
<style>
  .appointments-shell { max-width: 1180px; margin: 0 auto; padding: 44px 5vw 70px; color: #19324a; font-family: Arial, sans-serif; }
  .appointments-heading { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 18px; margin-bottom: 27px; }
  .appointments-heading p { margin: 0 0 8px; color: #087f73; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
  .appointments-heading h1 { margin: 0; font: 400 clamp(32px, 4vw, 46px) Georgia, serif; }
  .appointment-button { display: inline-block; padding: 12px 17px; color: #fff !important; background: #087f73; border-radius: 5px; font-size: 13px; font-weight: 700; text-decoration: none; }
  .appointments-flash, .appointments-errors { margin-bottom: 18px; padding: 12px 15px; border-radius: 5px; font-size: 13px; }
  .appointments-flash { color: #126653; background: #e4f5ef; }
  .appointments-errors { color: #a74435; background: #fff0ed; }
  .appointments-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 13px; margin-bottom: 25px; }
  .summary-card, .appointment-card { background: #fff; border: 1px solid #e3ebf1; }
  .summary-card { padding: 18px 20px; }
  .summary-card span { display: block; color: #708296; font-size: 12px; }
  .summary-card strong { display: block; margin-top: 6px; font: 400 28px Georgia, serif; }
  .appointment-card { margin-bottom: 13px; padding: 20px 22px; }
  .appointment-top { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; }
  .appointment-top h2 { margin: 0 0 7px; font: 400 21px Georgia, serif; }
  .appointment-meta { color: #708296; font-size: 13px; line-height: 1.7; }
  .appointment-detail { margin-top: 14px; padding-top: 13px; border-top: 1px solid #e3ebf1; font-size: 13px; line-height: 1.6; }
  .appointment-detail strong { color: #19324a; }
  .appointment-status { align-self: flex-start; padding: 6px 9px; color: #087f73; background: #dff4ed; border-radius: 4px; font-size: 11px; font-weight: 700; }
  .appointment-status.status-cancelled, .appointment-status.status-no-show { color: #a74435; background: #fff0ed; }
  .appointment-status.status-completed { color: #315d8a; background: #e8f0f8; }
  .appointment-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px; }
  .appointment-actions button, .consultation-form button { padding: 9px 12px; color: #fff; background: #087f73; border: 0; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 700; }
  .appointment-actions .danger { color: #a74435; background: #fff0ed; }
  .appointment-actions input, .consultation-form input, .consultation-form textarea { box-sizing: border-box; width: 100%; padding: 9px; border: 1px solid #d5e0e7; border-radius: 4px; font: 13px Arial, sans-serif; }
  .consultation-form { display: grid; gap: 10px; margin-top: 14px; padding: 14px; background: #f5f9fa; }
  .consultation-form label { display: grid; gap: 6px; color: #708296; font-size: 12px; font-weight: 700; }
  .consultation-form .two-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .appointments-empty { padding: 38px 20px; color: #708296; background: #fff; border: 1px solid #e3ebf1; text-align: center; }
  html[data-theme="dark"] body .appointments-shell { color: var(--ink); }
  html[data-theme="dark"] body .summary-card, html[data-theme="dark"] body .appointment-card, html[data-theme="dark"] body .appointments-empty { background: var(--surface); border-color: var(--line); }
  html[data-theme="dark"] body .appointment-detail { border-color: var(--line); }
  html[data-theme="dark"] body .appointment-detail strong { color: var(--ink); }
  html[data-theme="dark"] body .consultation-form { background: var(--field); }
  @media (max-width: 620px) { .appointments-shell { padding: 30px 18px 55px; } .appointments-summary { grid-template-columns: 1fr; } .appointment-card { padding: 16px; } .consultation-form .two-fields { grid-template-columns: 1fr; } }
</style>

<main class="appointments-shell">
  <header class="appointments-heading">
    <div><p>{{ now()->format('l, F j, Y') }}</p><h1>{{ $isPatient ? 'Your appointments' : 'Appointment dashboard' }}</h1></div>
    @if ($canBook)<a class="appointment-button" href="{{ route('appointments.create') }}">Book an appointment</a>@endif
  </header>

  @if (session('success'))<div class="appointments-flash" role="status">{{ session('success') }}</div>@endif
  @if ($errors->any())<ul class="appointments-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

  <section class="appointments-summary" aria-label="Appointment summary">
    <article class="summary-card"><span>Showing</span><strong>{{ $appointments->total() }}</strong></article>
    <article class="summary-card"><span>Upcoming on this page</span><strong>{{ $appointments->getCollection()->filter(fn ($appointment) => $appointment->starts_at?->isFuture() && ! in_array($appointment->status, ['Cancelled', 'No-show'], true))->count() }}</strong></article>
    <article class="summary-card"><span>Completed on this page</span><strong>{{ $appointments->getCollection()->where('status', 'Completed')->count() }}</strong></article>
  </section>

  @forelse ($appointments as $appointment)
    <article class="appointment-card">
      <div class="appointment-top">
        <div>
          <h2>{{ $appointment->starts_at?->format('D, M j, Y · g:i A') }} <span class="appointment-meta">({{ $appointment->appointment_type }})</span></h2>
          <div class="appointment-meta">
            @if ($isPatient)
              With {{ $appointment->clinician?->name ?? 'Former clinician' }}
            @else
              Patient: {{ $appointment->patient?->name ?? 'Former patient' }}
              @if ($canManage) · {{ $appointment->patient?->phone ?? $appointment->patient?->email }} @endif
              · Clinician: {{ $appointment->clinician?->name ?? 'Former clinician' }}
            @endif
          </div>
        </div>
        <span class="appointment-status status-{{ \Illuminate\Support\Str::slug($appointment->status) }}">{{ $appointment->status }}</span>
      </div>
      <div class="appointment-detail"><strong>Reason:</strong> {{ $appointment->reason }}</div>
      @if ($appointment->status === 'Cancelled' && $appointment->cancellation_reason)
        <div class="appointment-detail"><strong>Cancellation reason:</strong> {{ $appointment->cancellation_reason }}</div>
      @endif
      @if ($appointment->status === 'Completed' && ($isPatient || $canConsult))
        <div class="appointment-detail">
          <strong>Consultation history</strong>
          @if ($appointment->diagnosis)<br><strong>Diagnosis:</strong> {{ $appointment->diagnosis }}@endif
          @if ($appointment->consultation_notes)<br>{{ $appointment->consultation_notes }}@endif
          @if ($appointment->treatment_plan)<br><strong>Treatment plan:</strong> {{ $appointment->treatment_plan }}@endif
          @if ($appointment->follow_up_date)<br><strong>Follow-up:</strong> {{ $appointment->follow_up_date->format('M j, Y') }}@endif
          @if (! $appointment->diagnosis && ! $appointment->consultation_notes && ! $appointment->treatment_plan)
            <br>Consultation record is not available yet.
          @endif
        </div>
      @endif

      @if (($canManage && ! in_array($appointment->status, ['Completed', 'Cancelled', 'No-show'], true))
        || ($canConsult && (int) $appointment->clinician_id === (int) auth()->id() && ! in_array($appointment->status, ['Cancelled', 'No-show'], true)))
        <div class="appointment-actions">
          @if ($canManage)
            @if ($appointment->status === 'Pending')
              <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="Confirmed"><button type="submit">Confirm</button></form>
            @endif
            @if (in_array($appointment->status, ['Pending', 'Confirmed'], true))
              <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="Checked in"><button type="submit">Check in</button></form>
              <form method="POST" action="{{ route('appointments.status', $appointment) }}" class="cancel-appointment-form">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="Cancelled">
                <input type="text" name="cancellation_reason" aria-label="Cancellation reason" placeholder="Reason for cancellation" maxlength="1000" required>
                <button class="danger" type="submit">Cancel</button>
              </form>
            @endif
            <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="No-show"><button class="danger" type="submit">Mark no-show</button></form>
          @elseif ($canConsult)
            @if (in_array($appointment->status, ['Pending', 'Confirmed', 'Checked in'], true))
              <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="In consultation"><button type="submit">Start consultation</button></form>
            @endif
            @if ($appointment->status === 'In consultation')
              <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="Completed"><button type="submit">Complete visit</button></form>
            @endif
            <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="No-show"><button class="danger" type="submit">Mark no-show</button></form>
          @endif
        </div>
      @endif

      @if ($canConsult && (int) $appointment->clinician_id === (int) auth()->id() && ! in_array($appointment->status, ['Cancelled', 'No-show'], true))
        <details>
          <summary class="appointment-detail">Add or update consultation record</summary>
          <form class="consultation-form" method="POST" action="{{ route('appointments.consultation', $appointment) }}">
            @csrf @method('PATCH')
            <label>Clinical notes<textarea name="consultation_notes" rows="3" required minlength="5" maxlength="12000">{{ old('consultation_notes', $appointment->consultation_notes) }}</textarea></label>
            <div class="two-fields">
              <label>Diagnosis<textarea name="diagnosis" rows="2" maxlength="4000">{{ old('diagnosis', $appointment->diagnosis) }}</textarea></label>
              <label>Treatment plan<textarea name="treatment_plan" rows="2" maxlength="4000">{{ old('treatment_plan', $appointment->treatment_plan) }}</textarea></label>
            </div>
            <label>Follow-up date<input type="date" name="follow_up_date" min="{{ now()->toDateString() }}" value="{{ old('follow_up_date', $appointment->follow_up_date?->toDateString()) }}"></label>
            <button type="submit">Save consultation record</button>
          </form>
        </details>
      @endif
      @if ($isPatient && in_array($appointment->status, ['Pending', 'Confirmed'], true))
        <form class="appointment-actions" method="POST" action="{{ route('appointments.cancel', $appointment) }}" onsubmit="return confirm('Cancel this appointment?')">
          @csrf @method('PATCH')
          <input type="hidden" name="cancellation_reason" value="Cancelled by patient">
          <button class="danger" type="submit">Cancel appointment</button>
        </form>
      @endif
    </article>
  @empty
    <div class="appointments-empty">
      <strong>No appointments yet.</strong>
      <p>{{ $isPatient ? 'Book an appointment online and your confirmed visits and consultation history will appear here.' : 'New patient appointments will appear here.' }}</p>
      @if ($canBook)<a class="appointment-button" href="{{ route('appointments.create') }}">Book an appointment</a>@endif
    </div>
  @endforelse
  <div>{{ $appointments->links() }}</div>
</main>
@endsection
