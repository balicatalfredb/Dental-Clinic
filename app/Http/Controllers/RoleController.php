<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('roles.index', [
            'roles' => DB::table('roles')->orderBy('display_name')->get(),
            'permissions' => DB::table('permissions')->orderBy('display_name')->get(),
            'assignments' => DB::table('permission_role')->get()->groupBy('role_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z][A-Za-z0-9 _-]*$/', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ]);

        DB::transaction(function () use ($validated): void {
            $roleId = DB::table('roles')->insertGetId([
                'name' => $validated['name'],
                'display_name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->syncPermissions($roleId, $validated['permissions'] ?? []);
        });

        AuditLog::record('transaction', 'Created a role and assigned permissions.', $request->user());

        return to_route('roles.index')->with('success', 'Role created.');
    }

    public function update(Request $request, int $role): RedirectResponse
    {
        $roleRecord = DB::table('roles')->where('id', $role)->first();
        abort_if($roleRecord === null, 404);

        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ]);

        DB::transaction(function () use ($roleRecord, $validated): void {
            DB::table('roles')->where('id', $roleRecord->id)->update([
                'description' => $validated['description'] ?? null,
                'updated_at' => now(),
            ]);
            $this->syncPermissions($roleRecord->id, $validated['permissions'] ?? []);
        });

        AuditLog::record('transaction', 'Updated permissions for role '.$roleRecord->name.'.', $request->user());

        return to_route('roles.index')->with('success', 'Role permissions updated.');
    }

    private function syncPermissions(int $roleId, array $permissionIds): void
    {
        DB::table('permission_role')->where('role_id', $roleId)->delete();

        if ($permissionIds === []) {
            return;
        }

        DB::table('permission_role')->insert(
            collect($permissionIds)->map(fn (int $permissionId): array => [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ])->all()
        );
    }
}
