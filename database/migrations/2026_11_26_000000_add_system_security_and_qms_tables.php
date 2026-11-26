<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedTinyInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('password_changed_at')->nullable();
        });

        Schema::create('login_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('identifier', 255)->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->boolean('successful')->default(false);
            $table->timestamp('attempted_at');
            $table->timestamps();
            $table->index(['identifier', 'attempted_at']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->index('created_at');
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->timestamps();
        });

        Schema::create('quality_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('title');
            $table->string('category');
            $table->string('version')->default('1.0');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status')->default('Draft');
            $table->string('status')->default('Active');
            $table->timestamp('approved_at')->nullable();
            $table->date('review_date')->nullable();
            $table->timestamps();
            $table->index(['category', 'status']);
        });

        Schema::create('quality_processes', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->text('inputs')->nullable();
            $table->text('outputs')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('risk_register', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('level')->default('Medium');
            $table->text('impact')->nullable();
            $table->text('mitigation')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('Open');
            $table->timestamps();
            $table->index(['level', 'status']);
        });

        Schema::create('corrective_actions', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_number')->unique();
            $table->text('problem');
            $table->text('root_cause')->nullable();
            $table->text('action')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_date')->nullable();
            $table->string('status')->default('Open');
            $table->text('verification')->nullable();
            $table->timestamps();
        });

        Schema::create('internal_audits', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('scope')->nullable();
            $table->text('criteria')->nullable();
            $table->foreignId('auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scheduled_date')->nullable();
            $table->string('status')->default('Scheduled');
            $table->text('findings')->nullable();
            $table->timestamps();
        });

        Schema::create('improvement_records', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('proposal');
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_date')->nullable();
            $table->string('status')->default('Proposed');
            $table->text('effectiveness')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'improvement_records', 'internal_audits', 'corrective_actions', 'risk_register',
            'quality_processes', 'quality_documents', 'system_settings', 'permission_role',
            'permissions', 'roles', 'password_reset_tokens', 'login_attempts',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['failed_login_attempts', 'locked_until', 'password_changed_at']);
        });
    }
};
