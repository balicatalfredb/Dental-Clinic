<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use App\Services\SystemSettings;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = DB::table('system_settings')->get()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request, SystemSettings $systemSettings): RedirectResponse
    {
        $validated = $request->validate([
            'system_name' => ['required', 'string', 'max:255'],
            'system_description' => ['nullable', 'string', 'max:2000'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'captcha_enabled' => ['sometimes', 'boolean'],
            'captcha_provider' => ['nullable', 'string', 'max:100'],
            'mfa_enabled' => ['sometimes', 'boolean'],
            'otp_expiration' => ['nullable', 'integer', 'between:1,60'],
            'backup_retention' => ['nullable', 'integer', 'between:1,365'],
            'smtp_host' => ['nullable', 'required_with:smtp_port', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'required_with:smtp_host', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $validated['captcha_enabled'] = $request->boolean('captcha_enabled');
        $validated['mfa_enabled'] = $request->boolean('mfa_enabled');
        unset($validated['logo']);
        $previousLogo = DB::table('system_settings')->where('key', 'logo_path')->value('value');
        $newLogo = $request->hasFile('logo')
            ? $request->file('logo')->store('system/logos', 'public')
            : null;

        DB::transaction(function () use ($validated, $newLogo): void {
            foreach ($validated as $key => $value) {
                if ($key === 'smtp_password' && blank($value)) {
                    continue;
                }
                if ($key === 'smtp_password') {
                    $value = Crypt::encryptString($value);
                }
                DB::table('system_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                        'is_secret' => str_contains($key, 'password') || str_contains($key, 'secret'),
                        'updated_at' => now(),
                    ]
                );
            }
            if ($newLogo !== null) {
                DB::table('system_settings')->updateOrInsert(
                    ['key' => 'logo_path'],
                    ['value' => $newLogo, 'is_secret' => false, 'updated_at' => now()]
                );
            }
        });

        $systemSettings->forgetCache();
        if ($newLogo !== null && filled($previousLogo)) {
            Storage::disk('public')->delete($previousLogo);
        }
        AuditLog::record('transaction', 'System settings updated.', $request->user());
        return back()->with('success', 'System settings updated.');
    }
}
