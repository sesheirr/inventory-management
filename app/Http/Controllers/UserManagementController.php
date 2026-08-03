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
            'role' => 'required|in:admin,user',
        ]);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        $user->update(['role' => $request->role]);

        return redirect()->route('users.index')
            ->with('success', "Role {$user->name} berhasil diubah menjadi {$request->role}.");
    }
}
