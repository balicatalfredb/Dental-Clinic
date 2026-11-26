<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('job_title')->default('Dental professional');
            $table->string('phone', 50)->nullable();
            $table->string('role')->default('Staff');
            $table->string('status')->default('Active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['job_title', 'phone', 'role', 'status']);
        });
    }
};

