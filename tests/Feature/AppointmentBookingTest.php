<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 26)->setTime(8, 0));
    }

    public function test_patient_can_view_available_slots_and_book_a_visit(): void
    {
        $patient = $this->createUser('Guest', 'pat@example.test');
        $clinician = $this->createUser('Clinician', 'clinician@example.test');

        $this->actingAs($patient)
            ->get(route('appointments.create', ['clinician_id' => $clinician->id, 'date' => '2026-11-27']))
            ->assertOk()
            ->assertSee('9:00 AM');

        $this->actingAs($patient)
            ->post(route('appointments.store'), [
                'clinician_id' => $clinician->id,
                'starts_at' => '2026-11-27T09:00:00+08:00',
                'appointment_type' => 'Dental check-up',
                'reason' => 'Routine dental examination',
            ])
            ->assertRedirect(route('appointments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'clinician_id' => $clinician->id,
            'status' => 'Pending',
            'appointment_type' => 'Dental check-up',
        ]);
    }

    public function test_double_booking_a_clinician_slot_is_rejected(): void
    {
        $firstPatient = $this->createUser('Guest', 'first@example.test');
        $secondPatient = $this->createUser('Guest', 'second@example.test');
        $clinician = $this->createUser('Clinician', 'clinician@example.test');
        $booking = [
            'clinician_id' => $clinician->id,
            'starts_at' => '2026-11-27T09:00:00+08:00',
            'appointment_type' => 'General consultation',
            'reason' => 'Initial dental consultation',
        ];

        $this->actingAs($firstPatient)->post(route('appointments.store'), $booking)->assertRedirect();
        $this->actingAs($secondPatient)
            ->from(route('appointments.create'))
            ->post(route('appointments.store'), $booking)
            ->assertRedirect(route('appointments.create'))
            ->assertSessionHasErrors('starts_at');

        $this->assertSame(1, Appointment::query()->count());
    }

    public function test_patient_cannot_view_another_patients_consultation_history(): void
    {
        $patient = $this->createUser('Guest', 'pat@example.test');
        $otherPatient = $this->createUser('Guest', 'other@example.test');
        $clinician = $this->createUser('Clinician', 'clinician@example.test');
        Appointment::query()->create([
            'patient_id' => $patient->id,
            'clinician_id' => $clinician->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subDay()->addMinutes(30),
            'appointment_type' => 'Follow-up',
            'reason' => 'Private patient visit',
            'status' => 'Completed',
            'consultation_notes' => 'Confidential consultation notes',
        ]);
        Appointment::query()->create([
            'patient_id' => $otherPatient->id,
            'clinician_id' => $clinician->id,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDays(2)->addMinutes(30),
            'appointment_type' => 'Follow-up',
            'reason' => 'Another private visit',
            'status' => 'Completed',
            'consultation_notes' => 'Do not disclose this record',
        ]);

        $this->actingAs($patient)
            ->get(route('appointments.index'))
            ->assertOk()
            ->assertSee('Confidential consultation notes')
            ->assertDontSee('Do not disclose this record');
    }

    public function test_patient_dashboard_only_shows_their_upcoming_appointments(): void
    {
        $patient = $this->createUser('Guest', 'pat@example.test');
        $otherPatient = $this->createUser('Guest', 'other@example.test');
        $clinician = $this->createUser('Clinician', 'clinician@example.test');

        foreach ([
            [$patient, 'My dental check-up'],
            [$otherPatient, 'Another patient visit'],
        ] as [$appointmentPatient, $appointmentType]) {
            Appointment::query()->create([
                'patient_id' => $appointmentPatient->id,
                'clinician_id' => $clinician->id,
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDay()->addMinutes(30),
                'appointment_type' => $appointmentType,
                'reason' => 'Routine appointment',
                'status' => 'Confirmed',
            ]);
        }

        $this->actingAs($patient)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Patient dashboard')
            ->assertSee('My dental check-up')
            ->assertDontSee('Another patient visit');
    }

    public function test_clinician_dashboard_only_shows_assigned_appointments(): void
    {
        $patient = $this->createUser('Guest', 'pat@example.test');
        $clinician = $this->createUser('Clinician', 'clinician@example.test');
        $otherClinician = $this->createUser('Clinician', 'other-clinician@example.test');

        foreach ([
            [$clinician, 'Assigned appointment'],
            [$otherClinician, 'Unassigned appointment'],
        ] as [$appointmentClinician, $appointmentType]) {
            Appointment::query()->create([
                'patient_id' => $patient->id,
                'clinician_id' => $appointmentClinician->id,
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDay()->addMinutes(30),
                'appointment_type' => $appointmentType,
                'reason' => 'Routine appointment',
                'status' => 'Confirmed',
            ]);
        }

        $this->actingAs($clinician)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Clinician dashboard')
            ->assertSee('Assigned appointment')
            ->assertDontSee('Unassigned appointment');
    }

    public function test_receptionist_dashboard_shows_all_upcoming_appointments(): void
    {
        $receptionist = $this->createUser('Receptionist', 'receptionist@example.test');
        $patient = $this->createUser('Guest', 'pat@example.test');
        $otherPatient = $this->createUser('Guest', 'other@example.test');
        $clinician = $this->createUser('Clinician', 'clinician@example.test');

        foreach ([
            [$patient, 'First appointment'],
            [$otherPatient, 'Second appointment'],
        ] as [$appointmentPatient, $appointmentType]) {
            Appointment::query()->create([
                'patient_id' => $appointmentPatient->id,
                'clinician_id' => $clinician->id,
                'starts_at' => now()->addDay(),
                'ends_at' => now()->addDay()->addMinutes(30),
                'appointment_type' => $appointmentType,
                'reason' => 'Routine appointment',
                'status' => 'Confirmed',
            ]);
        }

        $this->actingAs($receptionist)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Receptionist dashboard')
            ->assertSee('Pat')
            ->assertSee('Other');
    }

    public function test_receptionist_can_book_and_confirm_and_clinician_can_complete_a_consultation(): void
    {
        $receptionist = $this->createUser('Receptionist', 'receptionist@example.test');
        $patient = $this->createUser('Guest', 'pat@example.test');
        $clinician = $this->createUser('Clinician', 'clinician@example.test');
        $appointmentData = [
            'patient_id' => $patient->id,
            'clinician_id' => $clinician->id,
            'starts_at' => '2026-11-27T09:00:00+08:00',
            'appointment_type' => 'General consultation',
            'reason' => 'Patient requested a dental consultation',
        ];

        $this->actingAs($receptionist)
            ->post(route('appointments.store'), $appointmentData)
            ->assertRedirect(route('appointments.index'));
        $appointment = Appointment::query()->sole();
        $this->assertSame($receptionist->id, $appointment->created_by_id);

        $this->actingAs($receptionist)
            ->patch(route('appointments.status', $appointment), ['status' => 'Confirmed'])
            ->assertRedirect();
        $this->assertSame('Confirmed', $appointment->fresh()->status);

        $this->actingAs($clinician)
            ->patch(route('appointments.status', $appointment), ['status' => 'Completed'])
            ->assertSessionHasErrors('status');
        $this->actingAs($clinician)
            ->patch(route('appointments.status', $appointment), ['status' => 'In consultation'])
            ->assertRedirect();
        $this->actingAs($clinician)
            ->patch(route('appointments.status', $appointment), ['status' => 'Completed'])
            ->assertSessionHasErrors('consultation_notes');
        $this->actingAs($clinician)
            ->patch(route('appointments.consultation', $appointment), [
                'consultation_notes' => 'Completed examination and discussed care options.',
                'diagnosis' => 'No urgent concerns',
                'treatment_plan' => 'Continue routine care',
                'follow_up_date' => '2027-01-15',
            ])
            ->assertRedirect();
        $this->actingAs($clinician)
            ->patch(route('appointments.status', $appointment), ['status' => 'Completed'])
            ->assertRedirect();

        $this->assertSame('Completed', $appointment->fresh()->status);
        $this->assertSame('Completed examination and discussed care options.', $appointment->fresh()->consultation_notes);

        $this->actingAs($patient)
            ->get(route('appointments.index'))
            ->assertSee('Completed examination and discussed care options.');
        $this->actingAs($receptionist)
            ->get(route('appointments.index'))
            ->assertDontSee('Completed examination and discussed care options.');
    }

    private function createUser(string $role, string $email): User
    {
        return User::factory()->create([
            'name' => ucfirst(strstr($email, '@', true)),
            'email' => $email,
            'role' => $role,
            'status' => 'Active',
        ]);
    }
}
