<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-users');

        $search = trim((string) $request->input('search', ''));
        $roleFilter = (string) $request->input('role', 'all');
        $sort = (string) $request->input('sort', 'role');

        $usersQuery = User::query()
            ->withCount(['mutations', 'activityLogs']);

        if ($search !== '') {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($roleFilter, [User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_USER], true)) {
            $usersQuery->where('role', $roleFilter);
        }

        if ($sort === 'newest') {
            $usersQuery->latest();
        } elseif ($sort === 'name_asc') {
            $usersQuery->orderBy('name', 'asc');
        } else {
            $usersQuery
                ->orderByRaw("CASE role WHEN 'superadmin' THEN 1 WHEN 'admin' THEN 2 ELSE 3 END")
                ->orderBy('name');
        }

        $users = $usersQuery->paginate(12)->withQueryString();

        $summary = [
            'total' => User::count(),
            'superadmin' => User::where('role', User::ROLE_SUPERADMIN)->count(),
            'admin' => User::where('role', User::ROLE_ADMIN)->count(),
            'user' => User::where('role', User::ROLE_USER)->count(),
        ];

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Admin/Users/Index', [
                'users' => $users->items(),
                'summary' => $summary,
            ]);
        }

        return view('users.index', compact('users', 'search', 'roleFilter', 'sort', 'summary'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'role' => ['required', 'string', Rule::in([User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_SUPERADMIN])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $newUser = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::record(
            'created',
            "Menambahkan akun pengguna baru {$newUser->name} ({$newUser->email}) dengan role {$newUser->role}",
            $newUser,
            $newUser->id,
            $request->user()->id
        );

        return redirect()
            ->route('users.index')
            ->with('success', "Akun pengguna {$newUser->name} berhasil ditambahkan.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in([User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_SUPERADMIN])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $newRole = $validated['role'];

        if ($request->user()->is($user) && $newRole !== User::ROLE_SUPERADMIN) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        if ($user->isSuperAdmin() && $newRole !== User::ROLE_SUPERADMIN) {
            $superadminCount = User::where('role', User::ROLE_SUPERADMIN)->count();
            if ($superadminCount <= 1) {
                return redirect()
                    ->route('users.index')
                    ->with('error', 'Sistem harus memiliki minimal satu superadmin.');
            }
        }

        $user->name = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));
        $user->role = $newRole;

        $passwordChanged = false;
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $passwordChanged = true;
        }

        $user->save();

        ActivityLog::record(
            'updated',
            "Memperbarui profil/akses pengguna {$user->name} ({$user->role})".($passwordChanged ? ' beserta reset password' : ''),
            $user,
            $user->id,
            $request->user()->id
        );

        return redirect()
            ->route('users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update-user-role');

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in([User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_SUPERADMIN])],
        ]);

        $newRole = $validated['role'];

        if ($request->user()->is($user) && $newRole !== User::ROLE_SUPERADMIN) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        if ($user->isSuperAdmin() && $newRole !== User::ROLE_SUPERADMIN) {
            $superadminCount = User::where('role', User::ROLE_SUPERADMIN)->count();

            if ($superadminCount <= 1) {
                return redirect()
                    ->route('users.index')
                    ->with('error', 'Sistem harus memiliki minimal satu superadmin.');
            }
        }

        $oldRole = $user->role;
        $user->update(['role' => $newRole]);

        ActivityLog::record(
            'updated',
            "Mengubah role pengguna {$user->name} dari {$oldRole} menjadi {$newRole}",
            $user,
            $user->id,
            $request->user()->id
        );

        return redirect()
            ->route('users.index')
            ->with('success', "Role untuk {$user->name} berhasil diperbarui menjadi {$newRole}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-users');

        if ($request->user()->is($user)) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri saat sedang login.');
        }

        if ($user->isSuperAdmin()) {
            $superadminCount = User::where('role', User::ROLE_SUPERADMIN)->count();

            if ($superadminCount <= 1) {
                return redirect()
                    ->route('users.index')
                    ->with('error', 'Tidak dapat menghapus superadmin terakhir di sistem.');
            }
        }

        $deletedId = $user->id;
        $deletedName = $user->name;
        $deletedEmail = $user->email;
        $user->delete();

        ActivityLog::record(
            'deleted',
            "Menghapus akun pengguna {$deletedName} ({$deletedEmail})",
            User::class,
            $deletedId,
            $request->user()->id
        );

        return redirect()
            ->route('users.index')
            ->with('success', "Akun {$deletedName} berhasil dihapus.");
    }
}
