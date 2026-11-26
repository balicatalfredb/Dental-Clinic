@extends('layout.app')

@section('title', 'Book an appointment - '.($systemBrand['name'] ?? 'Dental'))

@section('content')
@include('layout.navigation')
<style>
  .booking-shell { max-width: 900px; margin: 0 auto; padding: 42px 5vw 70px; color: #19324a; font-family: Arial, sans-serif; }
  .booking-heading { margin-bottom: 25px; }
  .booking-heading p { margin: 0 0 8px; color: #087f73; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
  .booking-heading h1 { margin: 0; font: 400 clamp(30px, 4vw, 44px) Georgia, serif; }
  .booking-card { padding: clamp(20px, 4vw, 34px); background: #fff; border: 1px solid #e3ebf1; }
  .booking-intro { color: #708296; font-size: 14px; line-height: 1.6; }
  .booking-form { display: grid; gap: 17px; margin-top: 23px; }
  .booking-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .booking-field { display: grid; gap: 7px; }
  .booking-field label { color: #19324a; font-size: 12px; font-weight: 700; }
  .booking-field :is(input, select, textarea) { box-sizing: border-box; width: 100%; min-height: 44px; padding: 10px 12px; color: #19324a; background: #fbfdfd; border: 1px solid #d5e0e7; border-radius: 5px; font: 14px Arial, sans-serif; }
  .booking-field textarea { min-height: 105px; resize: vertical; }
  .booking-hint { color: #708296; font-size: 12px; line-height: 1.5; }
  .booking-errors { padding: 12px 14px 12px 32px; color: #a74435; background: #fff0ed; border-radius: 5px; font-size: 13px; }
  .booking-submit { justify-self: start; padding: 12px 17px; color: #fff; background: #087f73; border: 0; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 700; }
  .booking-back { color: #087f73; font-size: 13px; text-decoration: none; }
  .no-slots { padding: 12px; color: #708296; background: #f5f9fa; font-size: 13px; }
  html[data-theme="dark"] body .booking-shell { color: var(--ink); }
  html[data-theme="dark"] body .booking-card { background: var(--surface); border-color: var(--line); }
  html[data-theme="dark"] body .booking-field label { color: var(--ink); }
  html[data-theme="dark"] body .no-slots { background: var(--field); }
  @media (max-width: 620px) { .booking-shell { padding: 30px 18px 55px; } .booking-grid { grid-template-columns: 1fr; } }
</style>

<main class="booking-shell">
  <header class="booking-heading"><p>Online scheduling</p><h1>Book an appointment</h1></header>
  <section class="booking-card">
    <p class="booking-intro">Choose a clinician and an open weekday time. Visits are 30 minutes and available Monday through Friday, 9:00 AM–5:00 PM. Your request will appear as pending until the clinic confirms it.</p>
    @if ($errors->any())<ul class="booking-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

    @if ($clinicians->isEmpty())
      <p class="no-slots">Online booking is not available yet because no active clinician accounts have been set up. Please contact the clinic.</p>
    @else
      <form class="booking-form" method="GET" action="{{ route('appointments.create') }}">
        <div class="booking-grid">
          <div class="booking-field"><label for="clinician_id">Clinician</label>
            <select id="clinician_id" name="clinician_id" required>
              <option value="">Choose a clinician</option>
              @foreach ($clinicians as $clinician)
                <option value="{{ $clinician->id }}" @selected($selectedClinicianId === $clinician->id)>{{ $clinician->name }}{{ $clinician->department ? ' · '.$clinician->department : '' }}</option>
              @endforeach
            </select>
          </div>
          <div class="booking-field"><label for="date">Appointment date</label>
            <input id="date" name="date" type="date" min="{{ now()->toDateString() }}" max="{{ now()->addDays(90)->toDateString() }}" value="{{ $selectedDate?->toDateString() }}" required>
          </div>
        </div>
        <button class="booking-submit" type="submit">Show available times</button>
      </form>

      @if ($selectedDate && $selectedClinicianId)
        <form class="booking-form" method="POST" action="{{ route('appointments.store') }}">
          @csrf
          @if ($isReceptionist)
            <div class="booking-field"><label for="patient_id">Patient account</label>
              <select id="patient_id" name="patient_id" required>
                <option value="">Select a patient</option>
                @foreach ($patients as $patient)
                  <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>{{ $patient->name }} · {{ $patient->email }}</option>
                @endforeach
              </select>
            </div>
          @endif
          <input type="hidden" name="clinician_id" value="{{ $selectedClinicianId }}">
          <div class="booking-field"><label for="starts_at">Available time</label>
            @if ($slots->isEmpty())
              <p class="no-slots">No times are available for this clinician on the selected date. Choose another date or clinician.</p>
            @else
              <select id="starts_at" name="starts_at" required>
                <option value="">Choose a time</option>
                @foreach ($slots as $slot)
                  @php($slotTime = \Carbon\Carbon::parse($slot))
                  <option value="{{ $slotTime->toIso8601String() }}" @selected(old('starts_at') === $slotTime->toIso8601String())>{{ $slotTime->format('g:i A') }}</option>
                @endforeach
              </select>
            @endif
          </div>
          <div class="booking-field"><label for="appointment_type">Appointment type</label>
            <select id="appointment_type" name="appointment_type" required>
              @foreach (['General consultation', 'Dental check-up', 'Follow-up', 'Other'] as $type)
                <option value="{{ $type }}" @selected(old('appointment_type') === $type)>{{ $type }}</option>
              @endforeach
            </select>
          </div>
          <div class="booking-field"><label for="reason">What would you like help with?</label>
            <textarea id="reason" name="reason" minlength="5" maxlength="2000" required>{{ old('reason') }}</textarea>
            <span class="booking-hint">Share a brief reason for your visit. Please do not include emergency symptoms; for urgent care, contact the clinic directly.</span>
          </div>
          @if ($slots->isNotEmpty())<button class="booking-submit" type="submit">Request appointment</button>@endif
        </form>
      @endif
    @endif
    <p><a class="booking-back" href="{{ route('appointments.index') }}">← Back to appointments</a></p>
  </section>
</main>
@endsection
