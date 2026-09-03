@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Akun Pengguna</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola hak akses dan peran (role) operator sistem inventaris</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-xs font-semibold text-rose-800 dark:text-rose-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-3.5">Nama Lengkap</th>
                        <th class="px-6 py-3.5">Alamat Email</th>
                        <th class="px-6 py-3.5">Peran (Role)</th>
                        <th class="px-6 py-3.5 text-right">Pengaturan Role & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-3.5 font-semibold text-slate-900 dark:text-white">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($user->isSuperAdmin())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                                        Super Admin
                                    </span>
                                @elseif($user->isAdmin())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40">
                                        Administrator
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        Operator (User)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                @if($user->id === auth()->id())
                                    <span class="text-xs text-slate-400 italic">Akun Anda Sendiri</span>
                                @elseif($user->isSuperAdmin())
                                    <span class="text-xs text-slate-400 italic">Super Admin Terkunci</span>
                                @else
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('users.update-role', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" class="px-2.5 py-1 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                                                <option value="user" @selected($user->role === 'user')>User</option>
                                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                                <option value="superadmin" @selected($user->role === 'superadmin')>Superadmin</option>
                                            </select>
                                            <button type="submit" class="px-3 py-1 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors"
                                                    onclick="return confirm('Ubah role {{ $user->name }}?')">
                                                Simpan
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Yakin ingin menghapus akun {{ $user->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors" title="Hapus Akun">
                                                <i class="bi bi-trash text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($users as $user)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $user->name }}</div>
                            <div class="text-xs text-slate-400">{{ $user->email }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $user->isSuperAdmin() ? 'bg-rose-50 text-rose-700' : ($user->isAdmin() ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>

                    @if($user->id !== auth()->id() && !$user->isSuperAdmin())
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                            <form method="POST" action="{{ route('users.update-role', $user) }}" class="flex items-center gap-2 flex-1">
                                @csrf
                                @method('PUT')
                                <select name="role" class="px-2.5 py-1 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white flex-1">
                                    <option value="user" @selected($user->role === 'user')>User</option>
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    <option value="superadmin" @selected($user->role === 'superadmin')>Superadmin</option>
                                </select>
                                <button type="submit" class="px-3 py-1 rounded-xl text-xs font-semibold text-white bg-blue-600">Simpan</button>
                            </form>

                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus akun?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-rose-600"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
