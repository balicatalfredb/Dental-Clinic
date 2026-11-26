<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            ['name'=>'Administrator','display_name'=>'Administrator'],
            ['name'=>'Manager','display_name'=>'Manager'],
            ['name'=>'Staff','display_name'=>'Staff'],
            ['name'=>'Guest','display_name'=>'Guest'],
            ['name'=>'Clinician','display_name'=>'Clinician'],
            ['name'=>'Receptionist','display_name'=>'Receptionist'],
        ] as $role) {
            DB::table('roles')->updateOrInsert(['name'=>$role['name']], $role + ['updated_at'=>now(),'created_at'=>now()]);
        }
        foreach ([
            'manage-users','manage-settings','view-reports','manage-backups','manage-audit','manage-qms'
        ] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name'=>$permission],
                [
                    'display_name' => str_replace('-', ' ', ucfirst($permission)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $permissionId = DB::table('permissions')->where('name',$permission)->value('id');
            $adminRoleId = DB::table('roles')->where('name','Administrator')->value('id');
            if ($adminRoleId) {
                DB::table('permission_role')->updateOrInsert([
                    'permission_id'=>$permissionId,'role_id'=>$adminRoleId,
                ]);
            }
        }
        $users = [
            [
                'name' => 'Daniel Kristeen',
                'email' => 'daniel@dental.test',
                'username' => 'daniel',
            ],
            [
                'name' => 'Emma Smith',
                'email' => 'emma@dental.test',
                'username' => 'emma',
            ],
            [
                'name' => 'Olivia Johnson',
                'email' => 'olivia@dental.test',
                'username' => 'olivia',
            ],
            [
                'name' => 'Isabella Williams',
                'email' => 'isabella@dental.test',
                'username' => 'isabella',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
