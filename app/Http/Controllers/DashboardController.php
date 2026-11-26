<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\Appointment;
use App\Models\LoginAttempt;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $role = strtolower(trim((string) $user->role));
        $isAdministrator = in_array($role, ['administrator', 'admin'], true);

        if (! $isAdministrator) {
            $canManageAppointments = $user->hasPermission('manage-appointments');
            $canViewClinicianAppointments = $user->hasPermission('view-clinician-appointments');
            $canBookAppointments = $user->hasPermission('book-appointments');

            if ($canManageAppointments || $canViewClinicianAppointments || $canBookAppointments) {
                $isClinician = ! $canManageAppointments && $canViewClinicianAppointments;
                $isPatient = ! $canManageAppointments && ! $canViewClinicianAppointments && $canBookAppointments;
                $dashboardRole = $canManageAppointments
                    ? 'Receptionist'
                    : ($isClinician ? 'Clinician' : 'Patient');
                $asOf = now();
                $upcomingQuery = Appointment::query()
                    ->visibleToUser($user)
                    ->whereIn('status', Appointment::BLOCKING_STATUSES)
                    ->where('starts_at', '>=', $asOf);

                return view('dashboard-appointments', [
                    'user' => $user,
                    'dashboardRole' => $dashboardRole,
                    'isClinician' => $isClinician,
                    'isPatient' => $isPatient,
                    'canBookAppointments' => $canBookAppointments || $canManageAppointments,
                    'upcomingCount' => (clone $upcomingQuery)->count(),
                    'upcomingAppointments' => (clone $upcomingQuery)
                        ->with(['patient', 'clinician'])
                        ->orderBy('starts_at')
                        ->limit(5)
                        ->get(),
                ]);
            }
        }

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => [
                'nullable',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($request->filled('from') && filled($value)) {
                        $start = Carbon::parse($request->input('from'));
                        $end = Carbon::parse($value);
                        if ($end->lt($start)) {
                            $fail('The end date must be on or after the start date.');
                        } elseif ($start->diffInDays($end) > 365) {
                            $fail('The selected dashboard period cannot exceed 366 days.');
                        }
                    }
                },
            ],
        ]);
        $periodStart = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(29)->startOfDay();
        $periodEnd = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        if (! $isAdministrator) {
            $canManageUsers = $user->hasPermission('manage-users');
            $canViewReports = $user->hasPermission('view-reports');
            $canViewAudit = $user->hasPermission('view-audit');

            if ($canManageUsers || $canViewReports || $canViewAudit) {
                $managerDashboard = [
                    'user' => $user,
                    'periodStart' => $periodStart,
                    'periodEnd' => $periodEnd,
                    'canManageUsers' => $canManageUsers,
                    'canViewReports' => $canViewReports,
                    'canViewAudit' => $canViewAudit,
                ];

                if ($canViewAudit) {
                    $managerDashboard += [
                        'totalTransactions' => AuditLog::query()
                            ->where('event', 'transaction')
                            ->whereBetween('created_at', [$periodStart, $periodEnd])
                            ->count(),
                        'auditRecords' => AuditLog::query()
                            ->whereBetween('created_at', [$periodStart, $periodEnd])
                            ->count(),
                        'successfulLogins' => LoginAttempt::query()
                            ->where('successful', true)
                            ->whereBetween('attempted_at', [$periodStart, $periodEnd])
                            ->count(),
                        'failedLogins' => LoginAttempt::query()
                            ->where('successful', false)
                            ->whereBetween('attempted_at', [$periodStart, $periodEnd])
                            ->count(),
                    ];
                }

                return view('dashboard-manager', $managerDashboard);
            }

            return view('dashboard-limited', ['user' => $user]);
        }

        $dailyTotals = User::query()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');
        $dailyUsers = collect(CarbonPeriod::create($periodStart->copy()->startOfDay(), $periodEnd->copy()->startOfDay()))
            ->map(fn (Carbon $day): array => [
                'label' => $day->format('M j'),
                'total' => (int) $dailyTotals->get($day->toDateString(), 0),
            ]);

        return view('dashboard', [
            'user' => $request->user(),
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('status', 'Active')->count(),
            'invitedUsers' => User::query()->where('status', 'Invited')->count(),
            'newUsersThisMonth' => User::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'dailyUsers' => $dailyUsers,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'recentUsers' => User::query()->latest()->limit(5)->get(),
            'roleDistribution' => User::query()->selectRaw('role, count(*) as total')->groupBy('role')->get(),
            'inactiveUsers' => User::query()->where(function ($query): void {
                $query->where('status', 'Paused')->orWhere('status', 'Inactive');
            })->count(),
            'totalTransactions' => AuditLog::query()->where('event', 'transaction')->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
            'auditRecords' => AuditLog::query()->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
            'successfulLogins' => LoginAttempt::query()->where('successful', true)->whereBetween('attempted_at', [$periodStart, $periodEnd])->count(),
            'failedLogins' => LoginAttempt::query()->where('successful', false)->whereBetween('attempted_at', [$periodStart, $periodEnd])->count(),
            'backupCount' => collect(Storage::disk('backups')->allFiles())->filter(fn (string $file): bool => str_ends_with($file, '.zip'))->count(),
        ]);
    }
}
