<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\SystemSettings;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:promote-admin {email}', function (string $email): int {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error('No account exists with that email address.');

        return 1;
    }

    $user->update(['role' => 'Administrator']);
    $this->info('Administrator access granted to '.$user->email.'.');

    return 0;
})->purpose('Grant administrator access to an existing account');

if (filled(config('backup.backup.password'))) {
    Schedule::command('backup:run')->dailyAt('02:00')->withoutOverlapping();
    Schedule::call(function (): void {
        $retentionDays = (int) app(SystemSettings::class)->get('backup_retention', 30);
        if ($retentionDays < 1 || $retentionDays > 365) {
            throw new \UnexpectedValueException('Backup retention must be between 1 and 365 days.');
        }
        config(['backup.cleanup.default_strategy.keep_all_backups_for_days' => $retentionDays]);
        Artisan::call('backup:clean');
    })->dailyAt('03:00')->name('backup-cleanup')->withoutOverlapping();
}
