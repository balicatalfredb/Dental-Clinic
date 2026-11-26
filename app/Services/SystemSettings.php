<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SystemSettings
{
    private const CACHE_KEY = 'system_settings.runtime';

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), function (): array {
            return DB::table('system_settings')->pluck('value', 'key')->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function enabled(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function applyMailConfiguration(): void
    {
        $host = $this->get('smtp_host');
        $port = $this->get('smtp_port');

        if (blank($host) || blank($port)) {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', (int) $port);
        Config::set('mail.mailers.smtp.username', $this->get('smtp_username'));

        $encryptedPassword = $this->get('smtp_password');
        if (filled($encryptedPassword)) {
            Config::set('mail.mailers.smtp.password', Crypt::decryptString($encryptedPassword));
        }

        Mail::purge('smtp');
    }
}
