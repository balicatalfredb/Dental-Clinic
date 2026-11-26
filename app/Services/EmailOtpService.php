<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\EmailOtpNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmailOtpService
{
    public function __construct(private readonly SystemSettings $settings) {}

    public function send(User $user): void
    {
        $minutes = (int) $this->settings->get('otp_expiration', 5);
        if ($minutes < 1 || $minutes > 60) {
            throw new \UnexpectedValueException('The configured email verification-code expiry must be between 1 and 60 minutes.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $now = now();

        DB::table('email_otp_tokens')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => $now->copy()->addMinutes($minutes),
                'last_sent_at' => $now,
                'attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->settings->applyMailConfiguration();
        $user->notify(new EmailOtpNotification($code, $minutes));
    }

    public function verify(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code): bool {
            $token = DB::table('email_otp_tokens')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($token === null) {
                return false;
            }

            if (now()->greaterThanOrEqualTo($token->expires_at) || $token->attempts >= 5) {
                DB::table('email_otp_tokens')->where('user_id', $user->id)->delete();

                return false;
            }

            if (! Hash::check($code, $token->code_hash)) {
                DB::table('email_otp_tokens')->where('user_id', $user->id)->update([
                    'attempts' => $token->attempts + 1,
                    'updated_at' => now(),
                ]);

                return false;
            }

            DB::table('email_otp_tokens')->where('user_id', $user->id)->delete();

            return true;
        });
    }
}
