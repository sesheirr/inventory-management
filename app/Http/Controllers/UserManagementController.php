<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);

        return view('users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:superadmin,admin,user',
        ]);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Role akun Super Admin tidak bisa diubah oleh Super Admin lain.');
        }

        $user->update(['role' => $request->role]);

        return redirect()->route('users.index')
            ->with('success', "Role {$user->name} berhasil diubah menjadi {$request->role}.");
    }

    public function destroy(User $user)
    {
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
