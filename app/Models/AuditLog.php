<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'description',
        'ip_address',
        'user_agent',
    ];

    public static function record(string $event, string $description, ?User $user = null): void
    {
        try {
            static::query()->create([
                'user_id' => $user?->id ?? auth()->id(),
                'event' => $event,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
