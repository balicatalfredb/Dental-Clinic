<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentScheduling
{
    public const SLOT_MINUTES = 30;

    public function slotsFor(int $clinicianId, Carbon $date): Collection
    {
        if ($date->isWeekend() || $date->isPast() && ! $date->isToday()) {
            return collect();
        }

        $dayStart = $date->copy()->setTime(9, 0);
        $dayEnd = $date->copy()->setTime(17, 0);
        $booked = Appointment::query()
            ->where('clinician_id', $clinicianId)
            ->whereIn('status', Appointment::BLOCKING_STATUSES)
            ->where('starts_at', '<', $dayEnd)
            ->where('ends_at', '>', $dayStart)
            ->get(['starts_at', 'ends_at'])
            ->all();

        $slots = collect();
        for ($slot = $dayStart->copy(); $slot->lt($dayEnd); $slot->addMinutes(self::SLOT_MINUTES)) {
            $slotEnd = $slot->copy()->addMinutes(self::SLOT_MINUTES);
            $overlapsBooking = collect($booked)->contains(
                fn (Appointment $appointment): bool => Carbon::parse($appointment->starts_at)->lt($slotEnd)
                    && Carbon::parse($appointment->ends_at)->gt($slot)
            );
            if ($slot->gt(now()) && ! $overlapsBooking) {
                $slots->push($slot->toIso8601String());
            }
        }

        return $slots;
    }

    public function isBookableTime(Carbon $startsAt): bool
    {
        return ! $startsAt->isWeekend()
            && $startsAt->isFuture()
            && $startsAt->minute % self::SLOT_MINUTES === 0
            && $startsAt->second === 0
            && $startsAt->hour >= 9
            && $startsAt->hour < 17;
    }
}
