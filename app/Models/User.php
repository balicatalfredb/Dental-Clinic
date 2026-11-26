<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'department',
        'phone',
        'role',
        'status',
        'profile_picture',
        'password',
        'two_factor_secret',
        'two_factor_method',
        'two_factor_enabled_at',
        'two_factor_last_used_at',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_enabled_at' => 'datetime',
            'two_factor_last_used_at' => 'integer',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        $role = strtolower(trim((string) $this->role));
        if (in_array($role, ['administrator', 'admin'], true)) {
            return true;
        }

        $canonicalRole = match ($role) {
            'manager' => 'Manager',
            'staff' => 'Staff',
            'guest' => 'Guest',
            'clinician' => 'Clinician',
            default => $this->role,
        };
        $roleId = DB::table('roles')->where('name', $canonicalRole)->value('id');

        if (! $roleId) {
            return false;
        }

        return DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $roleId)
            ->where('permissions.name', $permission)
            ->exists();
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (blank($user->username) && filled($user->email)) {
                $base = Str::slug(Str::before($user->email, '@'), '');
                $base = $base !== '' ? substr($base, 0, 24) : 'user';
                $username = $base;
                $suffix = 1;

                while (static::query()->where('username', $username)->exists()) {
                    $username = substr($base, 0, 24).$suffix++;
                }

                $user->username = $username;
            }
        });
    }
}
