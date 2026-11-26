<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function profile(Request $request): View
    {
        return view('profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:30', 'unique:users,username,'.$user->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'current_password' => $request->filled('password')
                ? ['required', 'current_password']
                : ['nullable'],
            'password' => ['nullable', 'confirmed', 'different:current_password', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        unset($validated['profile_picture']);
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $validated['profile_picture'] = $request->file('profile_picture')
                ->store('profile-pictures', 'public');
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->string('password')->toString());
            $validated['password_changed_at'] = now();
        } else {
            unset($validated['password']);
        }
        unset($validated['current_password'], $validated['password_confirmation']);

        $user->update($validated);
        if ($request->filled('password')) {
            Auth::logoutOtherDevices($request->string('password')->toString());
        }
        AuditLog::record('transaction', 'Profile updated.', $user);

        return to_route('profile')->with('success', 'Profile updated.');
    }

    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate((int) $request->integer('per_page', 20))
            ->withQueryString();

        $roles = DB::table('roles')->orderBy('display_name')->pluck('name');

        return view('user-profile', compact('users', 'roles'));
    }

    public function create(): View
    {
        return view('users.create', ['roles' => DB::table('roles')->orderBy('display_name')->pluck('name')]);
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => DB::table('roles')->orderBy('display_name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'in:General Dentistry,Orthodontics,Dental Hygiene,Administration,Reception'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'string', 'in:Active,Invited'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::create($validated);
        AuditLog::record('transaction', 'Created team member '.$user->email.'.', $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Team member added.',
                'row' => view('users.row', compact('user'))->render(),
            ], 201);
        }

        return to_route($request->is('user-profile') ? 'users.legacy.index' : 'users.index')
            ->with('success', 'Team member added.');
    }

    public function update(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'in:General Dentistry,Orthodontics,Dental Hygiene,Administration,Reception'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'max:50'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'string', 'in:Active,Invited,Paused'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['password_changed_at'] = now();
        }

        $user->update($validated);
        AuditLog::record('transaction', 'Updated team member '.$user->email.'.', $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Team member updated.',
                'row' => view('users.row', compact('user'))->render(),
            ]);
        }

        return to_route('users.index')->with('success', 'Team member updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($request->user()->is($user)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You cannot remove your own active account.'], 422);
            }

            return back()->withErrors(['user' => 'You cannot remove your own active account.']);
        }

        $email = $user->email;
        $user->delete();
        AuditLog::record('transaction', 'Deleted team member '.$email.'.', $request->user());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Team member removed.']);
        }

        return to_route('users.index')->with('success', 'Team member removed.');
    }
}
