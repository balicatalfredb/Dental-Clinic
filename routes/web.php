<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\QmsController;
use App\Http\Controllers\RoleController;
use App\Http\Middleware\AuditTrail;
use App\Http\Middleware\RequireTwoFactorSetup;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');

Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
Route::get('/two-factor/challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
Route::post('/two-factor/challenge', [AuthController::class, 'verifyTwoFactor'])->middleware('throttle:6,1')->name('two-factor.verify');
Route::post('/two-factor/challenge/resend', [AuthController::class, 'resendTwoFactorCode'])->middleware('throttle:3,1')->name('two-factor.resend');

Route::middleware(['auth', AuditTrail::class, RequireTwoFactorSetup::class])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::patch('/appointments/{appointment}/consultation', [AppointmentController::class, 'updateConsultation'])->name('appointments.consultation');
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/two-factor', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/profile/two-factor', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::delete('/profile/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::get('/profile/two-factor/email', [TwoFactorController::class, 'showEmailSetup'])->name('two-factor.email.setup');
    Route::post('/profile/two-factor/email', [TwoFactorController::class, 'sendEmailSetup'])->middleware('throttle:3,1')->name('two-factor.email.send');
    Route::get('/profile/two-factor/email/challenge', [TwoFactorController::class, 'showEmailChallenge'])->name('two-factor.email.challenge');
    Route::post('/profile/two-factor/email/challenge', [TwoFactorController::class, 'verifyEmailSetup'])->middleware('throttle:6,1')->name('two-factor.email.verify');
    Route::post('/profile/two-factor/email/resend', [TwoFactorController::class, 'sendDisableEmailCode'])->middleware('throttle:3,1')->name('two-factor.email.resend');

    Route::middleware('can:manage-users')->group(function (): void {
        Route::get('/user-profile', [UserController::class, 'index'])->name('users.legacy.index');
        Route::post('/user-profile', [UserController::class, 'store'])->name('users.legacy.store');
        Route::prefix('admin')->group(function (): void {
            Route::get('/user-profile', [UserController::class, 'index'])->name('users.index');
            Route::get('/user-profile/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/user-profile', [UserController::class, 'store'])->name('users.store');
            Route::get('/user-profile/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/user-profile/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/user-profile/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });
    });

    Route::middleware('can:view-audit')->group(function (): void {
        Route::get('/admin/audit-trail', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/admin/audit-trail/export', [AuditLogController::class, 'export'])->name('audit.export');
    });
    Route::middleware('can:manage-backups')->group(function (): void {
        Route::get('/admin/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/admin/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('/admin/backups/{filename}', [BackupController::class, 'download'])
            ->where('filename', '[A-Za-z0-9._-]+')
            ->name('backups.download');
        Route::delete('/admin/backups/{token}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
    Route::middleware('can:view-reports')->group(function (): void {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/{format}', [ReportController::class, 'export'])->name('reports.export');
    });
    Route::middleware('can:manage-settings')->group(function (): void {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
    Route::middleware('can:manage-qms')->group(function (): void {
        Route::get('/qms', [QmsController::class, 'index'])->name('qms.index');
        Route::post('/qms/{section}', [QmsController::class, 'store'])->name('qms.store');
        Route::get('/qms/documents/{document}/download', [QmsController::class, 'document'])->name('qms.document.download');
        Route::get('/qms/documents/{document}/revisions/{revision}/download', [QmsController::class, 'documentRevision'])->name('qms.document.revision.download');
        Route::post('/qms/documents/{document}/revisions', [QmsController::class, 'addDocumentRevision'])->name('qms.document.revision.store');
        Route::get('/qms/{section}/{id}/history', [QmsController::class, 'history'])->name('qms.history');
        Route::patch('/qms/{section}/{id}', [QmsController::class, 'update'])->name('qms.update');
        Route::delete('/qms/{section}/{id}', [QmsController::class, 'destroy'])->name('qms.destroy');
    });
    Route::middleware('can:manage-users')->group(function (): void {
        Route::get('/admin/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/admin/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/admin/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
});
