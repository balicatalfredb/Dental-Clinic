<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LoginAttempt;
use App\Notifications\EmailOtpNotification;
use App\Services\SystemSettings;
use App\Services\TwoFactorRecoveryCodes;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_user_management_reads_users_from_the_database(): void
    {
        $admin = User::factory()->create(['name' => 'Database Patient', 'role' => 'Administrator']);

        $response = $this->actingAs($admin)->get('/user-profile');

        $response->assertOk()
            ->assertSee('Database Patient')
            ->assertSee('addEmail')
            ->assertSee('addPassword')
            ->assertDontSee('switchAddTab')
            ->assertDontSee('switchEditTab');
    }

    public function test_team_member_can_be_created_in_the_database(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $response = $this->actingAs($admin)->post('/user-profile', [
            'name' => 'New Team Member',
            'department' => 'Dental Hygiene',
            'email' => 'new.member@example.com',
            'phone' => '+1 999 444 559',
            'role' => 'Staff',
            'status' => 'Invited',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ]);

        $response->assertRedirect('/user-profile');
        $this->assertDatabaseHas('users', [
            'email' => 'new.member@example.com',
            'name' => 'New Team Member',
        ]);
    }

    public function test_registration_creates_and_logs_in_a_user(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee('captchaSlider')
            ->assertSee('Slide the puzzle piece into the outlined space')
            ->assertSee('data-password-toggle="registerPassword"', false);

        $response = $this->withSession(['registration_challenge' => ['target' => 50, 'answer' => 50]])
            ->post('/register', [
                'firstName' => 'New',
                'lastName' => 'Doctor',
                'username' => 'newdoctor',
                'email' => 'doctor@example.com',
                'password' => 'StrongPass1!',
                'password_confirmation' => 'StrongPass1!',
                'terms' => 'on',
                'captcha_answer' => 50,
            ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'doctor@example.com']);
    }

    public function test_existing_user_can_log_in(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/');
    }

    public function test_registration_rejects_an_incorrect_challenge_answer(): void
    {
        $this->withSession(['registration_challenge' => ['target' => 50, 'answer' => 50]])
            ->post('/register', [
                'firstName' => 'New',
                'lastName' => 'Doctor',
                'username' => 'wronganswer',
                'email' => 'wrong.answer@example.com',
                'password' => 'StrongPass1!',
                'password_confirmation' => 'StrongPass1!',
                'terms' => 'on',
                'captcha_answer' => 51,
            ])
            ->assertSessionHasErrors('captcha_answer');
    }

    public function test_registration_replaces_a_legacy_arithmetic_challenge_with_a_slider_challenge(): void
    {
        $this->withSession(['registration_challenge' => ['first' => 5, 'second' => 2, 'answer' => 7]])
            ->get(route('register'))
            ->assertOk()
            ->assertSee('id="captchaBoard" data-target="', false)
            ->assertViewHas('challenge', function (array $challenge): bool {
                return isset($challenge['target'], $challenge['answer'])
                    && is_int($challenge['target'])
                    && $challenge['target'] >= 15
                    && $challenge['target'] <= 80
                    && $challenge['answer'] === $challenge['target'];
            });
    }

    public function test_team_members_support_json_create_update_and_delete(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        $this->actingAs($admin);

        $this->postJson(route('users.store'), [
            'name' => 'Async Team Member',
            'department' => 'Dental Hygiene',
            'email' => 'async.member@example.com',
            'phone' => '+1 999 444 559',
            'role' => 'Staff',
            'status' => 'Active',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ])->assertCreated()->assertJsonPath('message', 'Team member added.');

        $user = User::where('email', 'async.member@example.com')->firstOrFail();

        $this->putJson(route('users.update', $user), [
            'name' => 'Updated Team Member',
            'department' => 'Orthodontics',
            'email' => $user->email,
            'phone' => '+1 999 444 558',
            'role' => 'Clinician',
            'status' => 'Active',
        ])->assertOk()->assertJsonPath('message', 'Team member updated.');

        $this->deleteJson(route('users.destroy', $user))
            ->assertOk()
            ->assertJsonPath('message', 'Team member removed.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_authenticated_user_can_update_their_profile(): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Profile Name',
            'username' => 'updated-profile',
            'email' => $user->email,
            'department' => 'Administration',
            'phone' => '+1 999 444 557',
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('profile'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Profile Name',
            'department' => 'Administration',
            'password' => $originalPassword,
        ]);
    }

    public function test_password_change_requires_current_password_and_invalidates_other_sessions(): void
    {
        $user = User::factory()->create(['password' => 'OldStrongPass1!']);
        $profile = [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'password' => 'NewStrongPass1!',
            'password_confirmation' => 'NewStrongPass1!',
        ];

        $this->actingAs($user)->put(route('profile.update'), $profile)
            ->assertSessionHasErrors('current_password');

        $samePasswordProfile = array_merge($profile, [
            'password' => 'OldStrongPass1!',
            'password_confirmation' => 'OldStrongPass1!',
        ]);
        $this->put(route('profile.update'), $samePasswordProfile + ['current_password' => 'OldStrongPass1!'])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('OldStrongPass1!', $user->fresh()->password));

        $this->put(route('profile.update'), $profile + ['current_password' => 'OldStrongPass1!'])
            ->assertRedirect(route('profile'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('NewStrongPass1!', $user->fresh()->password));
    }

    public function test_dashboard_displays_real_account_summary(): void
    {
        $user = User::factory()->create(['name' => 'Dashboard User', 'role' => 'Administrator']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard User')
            ->assertSee('Hello, Dashboard')
            ->assertSee('Team members')
            ->assertSee('Account growth');
    }

    public function test_staff_only_sees_the_limited_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'Staff User', 'role' => 'Staff']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Staff workspace')
            ->assertSee('Manage your profile')
            ->assertDontSee('Team members')
            ->assertDontSee('Audit records')
            ->assertDontSee('roleChart');
    }

    public function test_manager_sees_the_management_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'Manager User', 'role' => 'Manager']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Management overview')
            ->assertSee('Audit records')
            ->assertSee('View reports')
            ->assertDontSee('Recently added')
            ->assertDontSee('userGrowth');
    }

    public function test_appointment_roles_can_open_their_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'Guest']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Patient dashboard')
            ->assertSee('View appointments')
            ->assertSee(route('dashboard'));
    }

    public function test_navigation_displays_live_philippine_date_and_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-26 10:00:00', 'UTC'));

        try {
            $user = User::factory()->create(['name' => 'Philippine Time User']);

            $this->actingAs($user)->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Thu, Nov 26, 2026 - 6:00:00 PM PHT')
                ->assertSee("timeZone: 'Asia/Manila'", false)
                ->assertSee('window.setInterval(updateDateTime, 1000)', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_user_can_enable_authenticator_with_a_qr_code(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee('data:image/svg+xml;base64', false);

        $secret = Crypt::decryptString(session()->get('two_factor_setup_secret'));
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->post(route('two-factor.enable'), ['code' => $code])
            ->assertRedirect(route('profile'));

        $this->assertNotNull($user->fresh()->two_factor_enabled_at);
        $this->assertNotNull($user->fresh()->two_factor_secret);
    }

    public function test_two_factor_user_must_complete_authenticator_challenge_to_sign_in(): void
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        $user = User::factory()->create([
            'password' => 'password123',
            'two_factor_secret' => $secret,
            'two_factor_enabled_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $this->post(route('two-factor.verify'), [
            'code' => $google2fa->getCurrentOtp($secret),
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->two_factor_last_used_at);
    }

    public function test_existing_user_can_sign_in_with_username(): void
    {
        $user = User::factory()->create([
            'email' => 'doctor.login@example.com',
            'username' => null,
            'password' => 'password123',
        ]);

        $this->assertSame('doctorlogin', $user->username);

        $this->post(route('login.store'), [
            'identifier' => $user->username,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_new_accounts_get_a_username_from_their_email_when_omitted(): void
    {
        $user = User::factory()->create([
            'email' => 'sample.person@example.com',
            'username' => null,
        ]);

        $this->assertSame('sampleperson', $user->username);
    }

    public function test_non_administrator_cannot_manage_users(): void
    {
        $user = User::factory()->create(['role' => 'Staff']);

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
    }

    public function test_authenticated_access_is_recorded_in_the_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'access',
            'description' => 'dashboard',
        ]);
    }

    public function test_admin_can_view_audit_and_backup_pages(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $this->actingAs($admin)->get(route('audit.index'))->assertOk()->assertSee('Audit trail');
        $this->get(route('backups.index'))->assertOk()->assertSee('Backups');
    }

    public function test_backup_requires_an_archive_encryption_secret(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        config(['backup.backup.password' => null]);

        $this->actingAs($admin)->post(route('backups.store'), ['scope' => 'database'])
            ->assertSessionHasErrors('backup');
    }

    public function test_admin_database_backup_runs_in_a_cli_process(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        config(['backup.backup.password' => 'test-only-password']);
        Process::fake();

        $this->actingAs($admin)
            ->post(route('backups.store'), ['scope' => 'database'])
            ->assertRedirect(route('backups.index'));

        Process::assertRan(function ($process): bool {
            $this->assertSame([
                PHP_BINARY,
                base_path('artisan'),
                'backup:run',
                '--only-to-disk=backups',
                '--disable-notifications',
                '--only-db',
            ], $process->command);
            $this->assertSame(base_path(), $process->path);
            $this->assertNull($process->timeout);
            $this->assertSame(config('backup.backup.temporary_directory'), $process->environment['TMP']);
            $this->assertSame(config('backup.backup.temporary_directory'), $process->environment['TEMP']);
            $this->assertSame(config('backup.backup.temporary_directory'), $process->environment['TMPDIR']);
            if (PHP_OS_FAMILY === 'Windows') {
                $this->assertSame(getenv('SystemRoot') ?: 'C:\Windows', $process->environment['SystemRoot']);
                $this->assertNotEmpty($process->environment['COMSPEC']);
            }

            return true;
        });
    }

    public function test_existing_account_can_be_promoted_to_administrator(): void
    {
        $user = User::factory()->create(['role' => 'Staff']);

        Artisan::call('app:promote-admin', ['email' => $user->email]);

        $this->assertSame('Administrator', $user->fresh()->role);
    }

    public function test_admin_can_download_a_nested_backup_file(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        Storage::fake('backups');
        Storage::disk('backups')->put('Dental/nested-backup.zip', 'private backup');
        $token = rtrim(strtr(base64_encode('Dental/nested-backup.zip'), '+/', '-_'), '=');

        $response = $this->actingAs($admin)->get(route('backups.download', $token))->assertOk();
        $cacheControl = $response->headers->get('cache-control');

        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
    }

    public function test_administrator_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $this->actingAs($admin)->deleteJson(route('users.destroy', $admin))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'You cannot remove your own active account.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_qms_forms_store_records_using_their_table_fields(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        $this->actingAs($admin)->post(route('qms.store', 'documents'), [
            'reference_number' => 'QMS-001',
            'title' => 'Quality policy',
            'category' => 'Policy',
            'version' => '1.0',
            'approval_status' => 'Approved',
            'status' => 'Active',
            'review_date' => '2027-01-01',
        ])->assertRedirect();
        $this->post(route('qms.store', 'processes'), [
            'name' => 'Document control', 'status' => 'Active',
        ])->assertRedirect();
        $this->post(route('qms.store', 'risks'), [
            'title' => 'Service interruption', 'description' => 'Risk detail',
            'level' => 'Medium', 'status' => 'Open',
        ])->assertRedirect();
        $this->post(route('qms.store', 'actions'), [
            'reference_number' => 'CA-001', 'problem' => 'Incorrect record',
            'status' => 'Open', 'target_date' => '2027-02-01',
        ])->assertRedirect();
        $this->post(route('qms.store', 'audits'), [
            'title' => 'Annual audit', 'scope' => 'Quality system',
            'status' => 'Scheduled', 'scheduled_date' => '2027-03-01',
        ])->assertRedirect();
        $this->post(route('qms.store', 'improvements'), [
            'title' => 'Improve review', 'proposal' => 'Review process',
            'status' => 'Proposed', 'target_date' => '2027-04-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('quality_documents', [
            'reference_number' => 'QMS-001',
            'title' => 'Quality policy',
            'review_date' => '2027-01-01',
        ]);
        $this->assertDatabaseHas('quality_processes', ['name' => 'Document control']);
        $this->assertDatabaseHas('risk_register', ['title' => 'Service interruption']);
        $this->assertDatabaseHas('corrective_actions', ['reference_number' => 'CA-001', 'target_date' => '2027-02-01']);
        $this->assertDatabaseHas('internal_audits', ['title' => 'Annual audit', 'scheduled_date' => '2027-03-01']);
        $this->assertDatabaseHas('improvement_records', ['title' => 'Improve review', 'target_date' => '2027-04-01']);
    }

    public function test_settings_encrypts_smtp_secret_and_persists_unchecked_toggles(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $this->actingAs($admin)->put(route('settings.update'), [
            'system_name' => 'Clinic Portal',
            'smtp_host' => 'smtp.example.test',
            'smtp_port' => 587,
            'smtp_username' => 'smtp-user',
            'smtp_password' => 'smtp-secret-value',
            'captcha_enabled' => '1',
        ])->assertRedirect();

        $storedSecret = DB::table('system_settings')->where('key', 'smtp_password')->value('value');
        $this->assertNotSame('smtp-secret-value', $storedSecret);
        $this->assertSame('smtp-secret-value', Crypt::decryptString($storedSecret));
        $this->assertDatabaseHas('system_settings', ['key' => 'captcha_enabled', 'value' => '1']);
        $this->assertDatabaseHas('system_settings', ['key' => 'mfa_enabled', 'value' => '0']);
        app(SystemSettings::class)->applyMailConfiguration();
        $this->assertSame('smtp.example.test', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->get(route('settings.index'))->assertOk()->assertSee('Clinic Portal')->assertDontSee('smtp-secret-value');
    }

    public function test_audit_pagination_uses_bootstrap_sized_controls(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);

        $this->actingAs($admin)->get(route('audit.index'))
            ->assertOk()
            ->assertSee('page-link')
            ->assertDontSee('<svg', false);
    }

    public function test_login_links_to_a_working_forgot_password_flow(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'))
            ->assertSee('data-password-toggle="password"', false)
            ->assertDontSee('Password recovery will be available soon.');

        $this->get(route('password.request'))->assertOk()->assertSee('Send reset link');
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_link_changes_the_account_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-password-toggle="password_confirmation"', false);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrongPass1!',
            'password_confirmation' => 'NewStrongPass1!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewStrongPass1!', $user->fresh()->password));
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $this->withSession(['registration_challenge' => ['target' => 50, 'answer' => 50]])
            ->post(route('register.store'), [
                'firstName' => 'New',
                'lastName' => 'Doctor',
                'username' => 'mismatchuser',
                'email' => 'mismatch@example.com',
                'password' => 'StrongPass1!',
                'password_confirmation' => 'DifferentPass1!',
                'terms' => 'on',
                'captcha_answer' => 50,
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_report_filters_and_csv_export_use_the_selected_search(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        User::factory()->create(['name' => 'Filtered Staff', 'email' => 'filtered@example.com']);
        User::factory()->create(['name' => 'Other Staff', 'email' => 'other@example.com']);
        $this->actingAs($admin);

        $this->get(route('reports.index', ['search' => 'Filtered']))
            ->assertOk()
            ->assertSee('Filtered Staff')
            ->assertDontSee('Other Staff');

        $this->get(route('reports.export', ['format' => 'csv', 'search' => 'Filtered']))
            ->assertOk()
            ->assertDownload('user-report.csv');
    }

    public function test_audit_search_filters_activity_and_backup_actions_are_available(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        \App\Models\AuditLog::query()->create([
            'user_id' => $admin->id,
            'event' => 'transaction',
            'description' => 'Filtered test activity',
            'ip_address' => '127.0.0.1',
        ]);
        \App\Models\AuditLog::query()->create([
            'user_id' => $admin->id,
            'event' => 'error',
            'description' => 'Other test activity',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)->get(route('audit.index', ['search' => 'Filtered']))
            ->assertOk()
            ->assertSee('Filtered test activity')
            ->assertDontSee('Other test activity');

        $this->get(route('backups.index'))
            ->assertOk()
            ->assertSee('Back up database')
            ->assertSee('Back up system files')
            ->assertSee('data-backup-form', false);
    }

    public function test_role_permissions_are_enforced_and_can_be_changed(): void
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        $this->actingAs($manager)->get(route('reports.index'))->assertOk();
        $this->get(route('audit.index'))->assertOk();
        $this->get(route('backups.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'Administrator']);
        $managerRoleId = DB::table('roles')->where('name', 'Manager')->value('id');
        $reportPermissionId = DB::table('permissions')->where('name', 'view-reports')->value('id');
        $this->actingAs($admin)->put(route('roles.update', $managerRoleId), [
            'description' => 'Reporting only',
            'permissions' => [$reportPermissionId],
        ])->assertRedirect(route('roles.index'));

        $this->actingAs($manager)->get(route('reports.index'))->assertOk();
        $this->get(route('audit.index'))->assertForbidden();
    }

    public function test_failed_login_attempts_lock_an_account_and_are_logged(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])->assertRedirect();
        }

        $this->assertSame(5, $user->fresh()->failed_login_attempts);
        $this->assertNotNull($user->fresh()->locked_until);
        $this->assertSame(5, LoginAttempt::query()->where('user_id', $user->id)->where('successful', false)->count());

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('identifier');
    }

    public function test_expired_login_lockout_is_cleared_after_a_successful_sign_in(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
            'failed_login_attempts' => 5,
            'locked_until' => now()->subMinute(),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, $user->fresh()->failed_login_attempts);
        $this->assertNull($user->fresh()->locked_until);
    }

    public function test_email_two_factor_sign_in_uses_an_expiring_one_time_code(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'password' => 'password123',
            'two_factor_method' => 'email',
            'two_factor_enabled_at' => now(),
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $code = null;
        Notification::assertSentTo($user, EmailOtpNotification::class, function (EmailOtpNotification $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });
        $this->assertNotNull($code);
        $this->post(route('two-factor.verify'), ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('email_otp_tokens', ['user_id' => $user->id]);
        $this->assertDatabaseHas('login_attempts', ['user_id' => $user->id, 'successful' => 1]);
    }

    public function test_user_can_enable_email_two_factor_and_receive_recovery_codes(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'Staff']);

        $this->actingAs($user)->post(route('two-factor.email.send'))
            ->assertRedirect(route('two-factor.email.challenge'));
        $this->get(route('two-factor.email.challenge'))->assertOk()->assertSee('Verify your email');

        $code = null;
        Notification::assertSentTo($user, EmailOtpNotification::class, function (EmailOtpNotification $notification) use (&$code): bool {
            $code = $notification->code;

            return true;
        });
        $this->post(route('two-factor.email.verify'), ['code' => $code])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('recovery_codes');

        $this->assertSame('email', $user->fresh()->two_factor_method);
        $this->assertNotNull($user->fresh()->two_factor_enabled_at);
        $this->assertSame(8, DB::table('user_recovery_codes')->where('user_id', $user->id)->count());
    }

    public function test_recovery_code_can_be_used_once_for_two_factor_sign_in(): void
    {
        $user = User::factory()->create([
            'two_factor_method' => 'email',
            'two_factor_enabled_at' => now(),
        ]);
        $code = app(TwoFactorRecoveryCodes::class)->generate($user)[0];

        $this->withSession(['two_factor_pending_user_id' => $user->id, 'two_factor_remember' => false])
            ->post(route('two-factor.verify'), ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(DB::table('user_recovery_codes')
            ->where('user_id', $user->id)
            ->whereNotNull('used_at')
            ->exists());
    }

    public function test_system_captcha_setting_controls_registration_challenge(): void
    {
        DB::table('system_settings')->insert([
            'key' => 'captcha_enabled',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        app(SystemSettings::class)->forgetCache();

        $this->get(route('register'))->assertOk()->assertDontSee('id="captchaSlider"', false);
        $this->post(route('register.store'), [
            'firstName' => 'Challenge',
            'lastName' => 'Disabled',
            'username' => 'challenge-disabled',
            'email' => 'challenge.disabled@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'terms' => 'on',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_enabling_required_mfa_redirects_unconfigured_accounts_to_setup(): void
    {
        DB::table('system_settings')->insert([
            'key' => 'mfa_enabled',
            'value' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        app(SystemSettings::class)->forgetCache();
        $user = User::factory()->create(['role' => 'Staff']);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('two-factor.setup'));
        $this->get(route('two-factor.setup'))->assertOk()->assertSee('Scan this QR');
    }

    public function test_reports_generate_real_xlsx_and_pdf_downloads(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        User::factory()->create(['name' => '=HYPERLINK("https://example.test")', 'email' => 'report@example.com']);
        $this->actingAs($admin);

        $xlsxResponse = $this->get(route('reports.export', ['format' => 'xlsx']))
            ->assertOk()
            ->assertDownload('user-report.xlsx');
        $xlsxPath = $xlsxResponse->baseResponse->getFile()->getPathname();
        $archive = new \ZipArchive;
        $this->assertSame(true, $archive->open($xlsxPath));
        $worksheet = $archive->getFromName('xl/worksheets/sheet1.xml');
        $this->assertIsString($worksheet);
        $this->assertStringContainsString('=HYPERLINK', $worksheet);
        $archive->close();
        @unlink($xlsxPath);

        $this->get(route('reports.export', ['format' => 'pdf']))
            ->assertOk()
            ->assertDownload('user-report.pdf');
    }

    public function test_dashboard_filters_login_metrics_and_plots_daily_registration_counts(): void
    {
        $user = User::factory()->create(['role' => 'Administrator']);
        LoginAttempt::query()->create([
            'user_id' => $user->id,
            'identifier' => $user->email,
            'ip_address' => '127.0.0.1',
            'successful' => true,
            'attempted_at' => now(),
        ]);
        LoginAttempt::query()->create([
            'user_id' => $user->id,
            'identifier' => $user->email,
            'ip_address' => '127.0.0.1',
            'successful' => false,
            'attempted_at' => now(),
        ]);

        $this->actingAs($user)->get(route('dashboard', [
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]))->assertOk()
            ->assertViewHas('successfulLogins', 1)
            ->assertViewHas('failedLogins', 1)
            ->assertViewHas('dailyUsers', fn ($days): bool => $days->sum('total') === 1);
    }

    public function test_qms_records_support_objectives_feedback_reviews_and_status_history(): void
    {
        $admin = User::factory()->create(['role' => 'Administrator']);
        $this->actingAs($admin)->post(route('qms.store', 'documents'), [
            'reference_number' => 'QMS-REV',
            'title' => 'Approval procedure',
            'category' => 'Procedure',
            'version' => '1.0',
            'approval_status' => 'Approved',
            'status' => 'Active',
        ])->assertRedirect();
        $this->post(route('qms.store', 'objectives'), [
            'title' => 'Reduce response time',
            'target_value' => 5,
            'current_value' => 8,
            'unit' => 'days',
            'status' => 'Active',
        ])->assertRedirect();
        $this->post(route('qms.store', 'feedback'), [
            'reference_number' => 'FB-001',
            'feedback' => 'Service follow-up requested',
            'status' => 'Open',
        ])->assertRedirect();
        $this->post(route('qms.store', 'reviews'), [
            'review_date' => '2027-01-05',
            'recommendations' => 'Review staffing level',
        ])->assertRedirect();
        $documentId = DB::table('quality_documents')->where('reference_number', 'QMS-REV')->value('id');
        $this->post(route('qms.document.revision.store', $documentId), [
            'version' => '1.1',
            'change_note' => 'Clarified approval responsibilities',
        ])->assertRedirect();

        $actionId = DB::table('corrective_actions')->insertGetId([
            'reference_number' => 'CA-HISTORY',
            'problem' => 'Initial state',
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->patch(route('qms.update', ['section' => 'actions', 'id' => $actionId]), [
            'status' => 'Completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('quality_objectives', ['title' => 'Reduce response time']);
        $this->assertDatabaseHas('customer_feedback', ['reference_number' => 'FB-001']);
        $this->assertDatabaseHas('management_reviews', ['review_date' => '2027-01-05']);
        $this->assertDatabaseHas('quality_documents', [
            'id' => $documentId,
            'version' => '1.1',
            'approval_status' => 'Draft',
        ]);
        $this->assertDatabaseHas('quality_document_revisions', [
            'quality_document_id' => $documentId,
            'version' => '1.1',
            'change_note' => 'Clarified approval responsibilities',
        ]);
        $this->assertDatabaseHas('corrective_actions', ['id' => $actionId, 'status' => 'Completed']);
        $this->get(route('qms.history', ['section' => 'actions', 'id' => $actionId]))
            ->assertOk()->assertSee('Updated')->assertSee('Initial state');
        $this->get(route('qms.index'))->assertOk()->assertSee('Reduce response time')->assertSee('Review · 2027-01-05');
    }
}
