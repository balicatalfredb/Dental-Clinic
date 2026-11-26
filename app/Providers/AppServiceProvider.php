<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Services\SystemSettings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        foreach ([
            'manage-users',
            'manage-settings',
            'view-reports',
            'manage-backups',
            'view-audit',
            'manage-qms',
            'book-appointments',
            'manage-appointments',
            'view-clinician-appointments',
            'manage-consultations',
        ] as $permission) {
            Gate::define($permission, fn (User $user): bool => $user->hasPermission($permission));
        }

        View::composer([
            'layout.navigation',
            'login',
            'register',
            'auth.forgot-password',
            'auth.reset-password',
        ], function ($view): void {
            $settings = app(SystemSettings::class)->all();
            $view->with('systemBrand', [
                'name' => $settings['system_name'] ?? config('app.name', 'Dental'),
                'logo' => $settings['logo_path'] ?? null,
            ]);
        });
    }
}
