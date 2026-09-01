<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserManagementController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-users');

        $users = User::orderBy('name')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('update-user-role');

        if (! auth()->user() || ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah role user.');
        }

        $request->validate([
            'role' => 'required|in:superadmin,admin,user',
        ]);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        if ($user->isSuperAdmin() && $request->role !== 'superadmin') {
            $superAdminCount = User::where('role', 'superadmin')->count();
            if ($superAdminCount <= 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Tidak dapat menurunkan role Super Admin karena ini adalah Super Admin terakhir di sistem.');
            }
        }

        $user->update(['role' => $request->role]);

        return redirect()->route('users.index')
            ->with('success', "Role {$user->name} berhasil diubah menjadi {$request->role}.");
    }

    public function destroy(User $user)
    {
        Gate::authorize('manage-users');

        if (! auth()->user() || ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus user.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Akun Super Admin tidak bisa dihapus.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Akun {$userName} berhasil dihapus.");
    }
}
