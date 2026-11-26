<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'job_title') && ! Schema::hasColumn('users', 'department')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->renameColumn('job_title', 'department');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'department') && ! Schema::hasColumn('users', 'job_title')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->renameColumn('department', 'job_title');
            });
        }
    }
};