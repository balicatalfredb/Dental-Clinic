<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactorRecoveryCodes
{
    public function generate(User $user): array
    {
        $codes = collect(range(1, 8))
            ->map(fn (): string => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
        $now = now();

        DB::transaction(function () use ($user, $codes, $now): void {
            DB::table('user_recovery_codes')->where('user_id', $user->id)->delete();
            DB::table('user_recovery_codes')->insert(
                collect($codes)->map(fn (string $code): array => [
                    'user_id' => $user->id,
                    'code_hash' => Hash::make($code),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        });

        return $codes;
    }

    public function consume(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code): bool {
            $codes = DB::table('user_recovery_codes')
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->lockForUpdate()
                ->get();

            foreach ($codes as $storedCode) {
                if (Hash::check(strtoupper(trim($code)), $storedCode->code_hash)) {
                    DB::table('user_recovery_codes')->where('id', $storedCode->id)->update([
                        'used_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return true;
                }
            }

            return false;
        });
    }
}
