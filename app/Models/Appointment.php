<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public const BLOCKING_STATUSES = ['Pending', 'Confirmed', 'Checked in', 'In consultation'];

    protected $fillable = [
        'patient_id',
        'clinician_id',
        'created_by_id',
        'starts_at',
        'ends_at',
        'appointment_type',
        'reason',
        'status',
        'consultation_notes',
        'diagnosis',
        'treatment_plan',
        'follow_up_date',
        'cancellation_reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'follow_up_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->hasPermission('manage-appointments')) {
            return $query;
        }

        if ($user->hasPermission('view-clinician-appointments')) {
            return $query->where('clinician_id', $user->getKey());
        }

        return $query->where('patient_id', $user->getKey());
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function clinician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clinician_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
