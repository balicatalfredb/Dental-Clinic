<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate($this->filterRules($request));
        $logs = $this->filteredLogs($request)
            ->paginate(40)
            ->withQueryString();

        return view('audit.index', ['logs' => $logs]);
    }

    public function export(Request $request): StreamedResponse
    {
        $request->validate($this->filterRules($request));
        $logs = $this->filteredLogs($request)->get();
        AuditLog::record('transaction', 'Exported filtered audit records as CSV.', $request->user());

        return response()->streamDownload(function () use ($logs): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Time', 'Account', 'Event', 'Details', 'IP address']);
            foreach ($logs as $log) {
                fputcsv($handle, array_map($this->safeCsvCell(...), [
                    $log->created_at?->format('Y-m-d H:i:s') ?? '',
                    $log->user?->email ?? 'Guest',
                    $log->event,
                    $log->description,
                    $log->ip_address,
                ]));
            }
            fclose($handle);
        }, 'audit-trail.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filteredLogs(Request $request)
    {
        return AuditLog::query()
            ->with('user')
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')))
            ->latest();
    }

    private function filterRules(Request $request): array
    {
        $rules = [
            'search' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'string', 'in:access,transaction,error'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ];
        if ($request->filled('from') && $request->filled('to')) {
            $rules['to'][] = 'after_or_equal:from';
        }

        return $rules;
    }

    private function safeCsvCell(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^\s*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }
}
