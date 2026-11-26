<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('clinician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('appointment_type', 80);
            $table->text('reason');
            $table->string('status', 30)->default('Pending');
            $table->text('consultation_notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['clinician_id', 'starts_at', 'status']);
            $table->index(['patient_id', 'starts_at', 'status']);
        });

        $roleNames = ['Guest', 'Receptionist', 'Clinician'];
        foreach ($roleNames as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleName],
                [
                    'display_name' => $roleName,
                    'description' => $roleName.' access role',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $permissions = [
            'book-appointments' => 'Book and view own appointments',
            'manage-appointments' => 'Manage patient appointments',
            'view-clinician-appointments' => 'View assigned clinical appointments',
            'manage-consultations' => 'Record consultation outcomes',
        ];
        foreach ($permissions as $name => $displayName) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['display_name' => $displayName, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $assignments = [
            'Guest' => ['book-appointments'],
            'Receptionist' => ['manage-appointments'],
            'Clinician' => ['view-clinician-appointments', 'manage-consultations'],
        ];
        foreach ($assignments as $roleName => $permissionNames) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            foreach ($permissionNames as $permissionName) {
                $permissionId = DB::table('permissions')->where('name', $permissionName)->value('id');
                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        DB::table('permission_role')->whereIn(
            'permission_id',
            DB::table('permissions')->whereIn('name', [
                'book-appointments',
                'manage-appointments',
                'view-clinician-appointments',
                'manage-consultations',
            ])->select('id')
        )->delete();
        DB::table('permissions')->whereIn('name', [
            'book-appointments',
            'manage-appointments',
            'view-clinician-appointments',
            'manage-consultations',
        ])->delete();
        DB::table('roles')->where('name', 'Receptionist')->delete();
    }
};
