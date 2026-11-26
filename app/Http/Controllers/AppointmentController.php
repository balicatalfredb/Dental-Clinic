<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AppointmentScheduling;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless(
            $user->hasPermission('book-appointments')
                || $user->hasPermission('manage-appointments')
                || $user->hasPermission('view-clinician-appointments'),
            403
        );

        $appointments = Appointment::query()
            ->visibleToUser($user)
            ->with(['patient', 'clinician'])
            ->orderByRaw("CASE WHEN starts_at >= ? AND status NOT IN ('Completed', 'Cancelled', 'No-show') THEN 0 ELSE 1 END", [now()])
            ->orderByRaw('CASE WHEN starts_at >= ? THEN starts_at END ASC', [now()])
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->withQueryString();

        $isPatient = $user->hasPermission('book-appointments')
            && ! $user->hasPermission('manage-appointments')
            && ! $user->hasPermission('view-clinician-appointments');

        return view('appointments.index', [
            'appointments' => $appointments,
            'isPatient' => $isPatient,
            'canManage' => $user->hasPermission('manage-appointments'),
            'canConsult' => $user->hasPermission('manage-consultations'),
            'canBook' => $user->hasPermission('book-appointments') || $user->hasPermission('manage-appointments'),
        ]);
    }

    public function create(Request $request, AppointmentScheduling $scheduling): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('book-appointments') || $user->hasPermission('manage-appointments'), 403);

        $validated = $request->validate([
            'clinician_id' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addDays(90)->toDateString()],
        ]);
        $clinicians = User::query()
            ->whereRaw('LOWER(role) = ?', ['clinician'])
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();
        $selectedDate = isset($validated['date']) ? Carbon::parse($validated['date']) : null;
        $selectedClinicianId = isset($validated['clinician_id']) ? (int) $validated['clinician_id'] : null;
        $slots = $selectedDate && $selectedClinicianId
            ? $scheduling->slotsFor($selectedClinicianId, $selectedDate)
            : collect();

        return view('appointments.create', [
            'clinicians' => $clinicians,
            'patients' => $user->hasPermission('manage-appointments')
                ? User::query()->whereRaw('LOWER(role) = ?', ['guest'])->where('status', 'Active')->orderBy('name')->get()
                : collect(),
            'isReceptionist' => $user->hasPermission('manage-appointments'),
            'selectedDate' => $selectedDate,
            'selectedClinicianId' => $selectedClinicianId,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request, AppointmentScheduling $scheduling): RedirectResponse
    {
        $user = $request->user();
        $canManage = $user->hasPermission('manage-appointments');
        abort_unless($user->hasPermission('book-appointments') || $canManage, 403);
        $validated = $request->validate([
            'patient_id' => [$canManage ? 'required' : 'nullable', 'integer', 'exists:users,id'],
            'clinician_id' => ['required', 'integer', 'exists:users,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'appointment_type' => ['required', Rule::in(['General consultation', 'Dental check-up', 'Follow-up', 'Other'])],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $startsAt = Carbon::parse($validated['starts_at']);
        if (! $scheduling->isBookableTime($startsAt) || $startsAt->gt(now()->addDays(90))) {
            return back()->withErrors(['starts_at' => 'Choose an available weekday slot between 9:00 AM and 5:00 PM within the next 90 days.'])->withInput();
        }

        $clinician = User::query()
            ->whereKey($validated['clinician_id'])
            ->whereRaw('LOWER(role) = ?', ['clinician'])
            ->where('status', 'Active')
            ->firstOrFail();
        $patientId = $canManage ? (int) $validated['patient_id'] : $user->id;
        if ($canManage) {
            abort_unless(
                User::query()->whereKey($patientId)->whereRaw('LOWER(role) = ?', ['guest'])->where('status', 'Active')->exists(),
                422
            );
        }

        $appointment = DB::transaction(function () use ($validated, $clinician, $patientId, $startsAt, $user): ?Appointment {
            User::query()->whereKey($clinician->id)->lockForUpdate()->firstOrFail();
            $endsAt = $startsAt->copy()->addMinutes(AppointmentScheduling::SLOT_MINUTES);
            $overlaps = Appointment::query()
                ->where('clinician_id', $clinician->id)
                ->whereIn('status', Appointment::BLOCKING_STATUSES)
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->exists();
            if ($overlaps) {
                return null;
            }

            return Appointment::query()->create([
                'patient_id' => $patientId,
                'clinician_id' => $clinician->id,
                'created_by_id' => $user->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'appointment_type' => $validated['appointment_type'],
                'reason' => $validated['reason'],
                'status' => 'Pending',
            ]);
        });
        if (! $appointment) {
            return back()->withErrors(['starts_at' => 'That slot was just booked. Please choose another available time.'])->withInput();
        }

        AuditLog::record('transaction', 'Booked appointment #'.$appointment->id.'.', $user);

        return to_route('appointments.index')->with('success', 'Appointment requested. The clinic will confirm your booking.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        $isClinician = $user->hasPermission('manage-consultations')
            && (int) $appointment->clinician_id === (int) $user->id;
        abort_unless($user->hasPermission('manage-appointments') || $isClinician, 403);
        $allowedStatuses = $user->hasPermission('manage-appointments')
            ? match ($appointment->status) {
                'Pending' => ['Confirmed', 'Checked in', 'Cancelled', 'No-show'],
                'Confirmed' => ['Checked in', 'Cancelled', 'No-show'],
                'Checked in' => ['No-show'],
                default => [],
            }
            : match ($appointment->status) {
                'Pending', 'Confirmed', 'Checked in' => ['In consultation', 'No-show'],
                'In consultation' => ['Completed', 'No-show'],
                default => [],
            };
        $validated = $request->validate([
            'status' => ['required', Rule::in($allowedStatuses)],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($validated['status'] === 'Cancelled' && blank($validated['cancellation_reason'] ?? null)) {
            return back()->withErrors(['cancellation_reason' => 'Please provide a reason for cancelling this appointment.']);
        }
        if ($validated['status'] === 'Completed' && blank($appointment->consultation_notes)) {
            return back()->withErrors(['consultation_notes' => 'Save the consultation notes before completing this visit.']);
        }

        $appointment->update([
            'status' => $validated['status'],
            'cancellation_reason' => $validated['status'] === 'Cancelled' ? $validated['cancellation_reason'] : null,
            'completed_at' => $validated['status'] === 'Completed' ? now() : $appointment->completed_at,
        ]);
        AuditLog::record('transaction', 'Changed appointment #'.$appointment->id.' to '.$validated['status'].'.', $user);

        return back()->with('success', 'Appointment status updated.');
    }

    public function updateConsultation(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        abort_unless(
            $user->hasPermission('manage-consultations')
                && (int) $appointment->clinician_id === (int) $user->id,
            403
        );
        abort_if(in_array($appointment->status, ['Cancelled', 'No-show'], true), 422);
        $validated = $request->validate([
            'consultation_notes' => ['required', 'string', 'min:5', 'max:12000'],
            'diagnosis' => ['nullable', 'string', 'max:4000'],
            'treatment_plan' => ['nullable', 'string', 'max:4000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $appointment->update($validated);
        AuditLog::record('transaction', 'Updated consultation record for appointment #'.$appointment->id.'.', $user);

        return back()->with('success', 'Consultation record saved.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        abort_unless(
            $user->hasPermission('manage-appointments')
                || ($user->hasPermission('book-appointments') && (int) $appointment->patient_id === (int) $user->id),
            403
        );
        abort_unless(in_array($appointment->status, ['Pending', 'Confirmed'], true), 422);
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
        $appointment->update([
            'status' => 'Cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);
        AuditLog::record('transaction', 'Cancelled appointment #'.$appointment->id.'.', $user);

        return back()->with('success', 'Appointment cancelled.');
    }
}
