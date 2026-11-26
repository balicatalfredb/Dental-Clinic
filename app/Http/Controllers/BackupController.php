<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupController extends Controller
{
    public function index(): View
    {
        $disk = Storage::disk('backups');
        $backups = collect($disk->allFiles())
            ->filter(fn (string $file): bool => str_ends_with($file, '.zip'))
            ->map(fn (string $file): array => [
                'name' => basename($file),
                'token' => rtrim(strtr(base64_encode($file), '+/', '-_'), '='),
                'size' => $disk->size($file),
                'modified' => $disk->lastModified($file),
            ])
            ->sortByDesc('modified')
            ->values();

        return view('backups.index', compact('backups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['scope' => ['required', 'in:database,system']]);

        if (blank(config('backup.backup.password'))) {
            return back()->withErrors(['backup' => 'Set BACKUP_ARCHIVE_PASSWORD before creating encrypted backups.']);
        }

        $command = [
            PHP_BINARY,
            base_path('artisan'),
            'backup:run',
            '--only-to-disk=backups',
            '--disable-notifications',
            $validated['scope'] === 'database' ? '--only-db' : '--only-files',
        ];
        $temporaryDirectory = config('backup.backup.temporary_directory');
        $processEnvironment = [
            'TMP' => $temporaryDirectory,
            'TEMP' => $temporaryDirectory,
            'TMPDIR' => $temporaryDirectory,
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            $systemRoot = getenv('SystemRoot') ?: 'C:\Windows';
            $processEnvironment += [
                'SystemRoot' => $systemRoot,
                'WINDIR' => getenv('WINDIR') ?: $systemRoot,
                'COMSPEC' => getenv('COMSPEC') ?: $systemRoot.'\System32\cmd.exe',
            ];
        }

        File::ensureDirectoryExists($temporaryDirectory);

        try {
            $result = Process::path(base_path())
                ->env($processEnvironment)
                ->forever()
                ->run($command);
        } catch (Throwable $exception) {
            Log::error('Backup process failed to start.', ['exception' => $exception]);
            AuditLog::record('error', 'Backup failed ('.class_basename($exception).').', $request->user());

            return back()->withErrors(['backup' => 'Backup could not be completed. Check the application logs.']);
        }

        if ($result->failed()) {
            $details = Str::limit(trim($result->errorOutput() ?: $result->output()), 500);
            Log::error('Backup command failed.', [
                'scope' => $validated['scope'],
                'exit_code' => $result->exitCode(),
                'output' => $details,
            ]);
            AuditLog::record('error', 'Backup command returned exit code '.$result->exitCode().'.', $request->user());

            return back()->withErrors(['backup' => 'Backup could not be completed. Check the application logs.']);
        }

        $label = $validated['scope'] === 'database' ? 'Database' : 'System files';
        AuditLog::record('transaction', $label.' backup created.', $request->user());

        return to_route('backups.index')->with('success', $label.' backup created.');
    }

    public function download(string $token): BinaryFileResponse
    {
        if (! preg_match('/^[A-Za-z0-9_-]+$/', $token)) {
            abort(404);
        }

        $encodedPath = strtr($token, '-_', '+/');
        $encodedPath = str_pad($encodedPath, (int) (ceil(strlen($encodedPath) / 4) * 4), '=', STR_PAD_RIGHT);
        $relativePath = base64_decode($encodedPath, true);

        if (! is_string($relativePath) || str_contains($relativePath, '..') || ! preg_match('#^[A-Za-z0-9._/-]+\.zip$#', $relativePath)) {
            abort(404);
        }

        $disk = Storage::disk('backups');

        abort_unless($disk->exists($relativePath), 404);

        $response = response()->download($disk->path($relativePath), basename($relativePath));
        $response->headers->set('Cache-Control', 'private, no-store');
        AuditLog::record('transaction', 'Downloaded backup '.basename($relativePath).'.');

        return $response;
    }

    public function destroy(Request $request, string $token): RedirectResponse
    {
        $relativePath = base64_decode(str_pad(strtr($token, '-_', '+/'), (int) (ceil(strlen($token) / 4) * 4), '=', STR_PAD_RIGHT), true);
        abort_unless(is_string($relativePath) && str_ends_with($relativePath, '.zip'), 404);
        $disk = Storage::disk('backups');
        abort_unless($disk->exists($relativePath), 404);
        $disk->delete($relativePath);
        AuditLog::record('transaction', 'Deleted backup '.basename($relativePath).'.', $request->user());
        return back()->with('success', 'Backup deleted.');
    }
}
