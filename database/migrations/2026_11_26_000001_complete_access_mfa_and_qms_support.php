<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'two_factor_method')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('two_factor_method', 20)->nullable()->after('two_factor_secret');
            });
        }
        Schema::table('quality_documents', function (Blueprint $table): void {
            if (! Schema::hasColumn('quality_documents', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('owner_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('quality_documents', 'file_path')) {
                $table->string('file_path')->nullable();
            }
        });

        if (! Schema::hasTable('user_recovery_codes')) {
            Schema::create('user_recovery_codes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('code_hash');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'used_at']);
            });
        }

        if (! Schema::hasTable('email_otp_tokens')) {
            Schema::create('email_otp_tokens', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('code_hash');
                $table->dateTime('expires_at');
                $table->dateTime('last_sent_at');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamps();
                $table->index('expires_at');
            });
        }

        Schema::create('quality_document_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quality_document_id')->constrained()->cascadeOnDelete();
            $table->string('version', 50);
            $table->text('change_note')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['quality_document_id', 'version']);
        });

        Schema::create('quality_objectives', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('target_value', 12, 3)->nullable();
            $table->decimal('current_value', 12, 3)->nullable();
            $table->string('unit', 50)->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('Active');
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });

        Schema::create('customer_feedback', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('source', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->text('feedback');
            $table->date('received_date')->nullable();
            $table->text('response')->nullable();
            $table->string('status', 30)->default('Open');
            $table->timestamps();
            $table->index(['status', 'received_date']);
        });

        Schema::create('management_reviews', function (Blueprint $table): void {
            $table->id();
            $table->date('review_date');
            $table->text('attendees')->nullable();
            $table->text('quality_objectives')->nullable();
            $table->text('audit_results')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->text('process_performance')->nullable();
            $table->text('nonconformities')->nullable();
            $table->text('risks_opportunities')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('review_date');
        });

        Schema::create('qms_record_history', function (Blueprint $table): void {
            $table->id();
            $table->string('record_type', 80);
            $table->unsignedBigInteger('record_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30);
            $table->json('snapshot')->nullable();
            $table->timestamps();
            $table->index(['record_type', 'record_id']);
        });

        $roleNames = ['Administrator', 'Manager', 'Staff', 'Guest', 'Clinician'];
        foreach ($roleNames as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleName],
                ['display_name' => $roleName, 'description' => $roleName.' access role', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $permissionNames = [
            'manage-users' => 'Manage users and access',
            'manage-settings' => 'Manage system settings',
            'view-reports' => 'View and export reports',
            'manage-backups' => 'Manage backups',
            'view-audit' => 'View audit trail',
            'manage-qms' => 'Manage quality records',
        ];
        foreach ($permissionNames as $name => $displayName) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['display_name' => $displayName, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $administratorId = DB::table('roles')->where('name', 'Administrator')->value('id');
        $managerId = DB::table('roles')->where('name', 'Manager')->value('id');
        foreach (array_keys($permissionNames) as $name) {
            $permissionId = DB::table('permissions')->where('name', $name)->value('id');
            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $administratorId, 'permission_id' => $permissionId]
            );
        }
        foreach (['view-reports', 'view-audit'] as $name) {
            $permissionId = DB::table('permissions')->where('name', $name)->value('id');
            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $managerId, 'permission_id' => $permissionId]
            );
        }
    }

    public function down(): void
    {
        foreach ([
            'qms_record_history', 'management_reviews', 'customer_feedback', 'quality_objectives',
            'quality_document_revisions', 'email_otp_tokens', 'user_recovery_codes',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        DB::table('roles')->whereIn('name', ['Administrator', 'Manager', 'Staff', 'Guest', 'Clinician'])->delete();
        DB::table('permissions')->whereIn('name', [
            'manage-users', 'manage-settings', 'view-reports', 'manage-backups', 'view-audit', 'manage-qms',
        ])->delete();

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('two_factor_method');
        });
        Schema::table('quality_documents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('file_path');
        });
    }
};
