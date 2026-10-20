@extends('layouts.app')

@section('title', 'Manajemen User & Hak Akses — Inventaris Diskominfo Garut')

@section('content')
<div class="space-y-5" data-framer-page>
    {{-- Page Header & Create User Button --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Pengguna</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola akun pegawai & hak akses berjenjang
            </p>
        </div>

        <button type="button"
                data-modal-target="createUserModal"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-600/25 active:scale-95 transition-all cursor-pointer shrink-0">
            <i class="bi bi-person-plus-fill"></i>
            <span>Tambah</span>
        </button>
    </div>

    {{-- KPI Summary Cards --}}
    @if(isset($summary))
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" data-framer-stagger>
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Akun</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="bi bi-people-fill text-sm"></i>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-1 tabular-nums">{{ $summary['total'] ?? 0 }}</div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Superadmin</span>
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i class="bi bi-shield-lock-fill text-sm"></i>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1 tabular-nums">{{ $summary['superadmin'] ?? 0 }}</div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Admin Aset</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="bi bi-person-check-fill text-sm"></i>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1 tabular-nums">{{ $summary['admin'] ?? 0 }}</div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Operator</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i class="bi bi-person-badge text-sm"></i>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">{{ $summary['user'] ?? 0 }}</div>
            </div>
        </div>
    @endif

    {{-- Filter & Search Bar --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-3.5 sm:p-4 shadow-sm" data-framer-card>
        <form action="{{ route('users.index') }}" method="GET" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5 items-center">
            <div class="relative col-span-2 sm:col-span-5">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Cari nama pegawai atau email..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
            </div>

            <div class="col-span-1 sm:col-span-3">
                <select name="role" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                    <option value="all" @selected(($roleFilter ?? 'all') === 'all')>Semua Role</option>
                    <option value="superadmin" @selected(($roleFilter ?? '') === 'superadmin')>Superadmin</option>
                    <option value="admin" @selected(($roleFilter ?? '') === 'admin')>Admin Inventaris</option>
                    <option value="user" @selected(($roleFilter ?? '') === 'user')>User / Operator</option>
                </select>
            </div>

            <div class="col-span-1 sm:col-span-2">
                <select name="sort" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                    <option value="role" @selected(($sort ?? 'role') === 'role')>Urutan Role</option>
                    <option value="name_asc" @selected(($sort ?? '') === 'name_asc')>Nama A-Z</option>
                    <option value="newest" @selected(($sort ?? '') === 'newest')>Terbaru</option>
                </select>
            </div>

            <div class="col-span-2 sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition-colors cursor-pointer">
                    Filter
                </button>
                @if(!empty($search) || ($roleFilter ?? 'all') !== 'all')
                    <a href="{{ route('users.index') }}" class="py-2 px-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Users Table & Mobile Feed Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden" data-framer-card>
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-3.5">Pegawai / Pengguna</th>
                        <th class="px-6 py-3.5">Email & Kontak</th>
                        <th class="px-6 py-3.5">Role Aktif</th>
                        <th class="px-6 py-3.5">Kontribusi Sistem</th>
                        <th class="px-6 py-3.5">Ubah Cepat Role</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @forelse ($users as $managedUser)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if(!empty($managedUser->avatar_url))
                                        <img src="{{ $managedUser->avatar_url }}" alt="{{ $managedUser->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                            {{ strtoupper(substr($managedUser->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <span>{{ $managedUser->name }}</span>
                                            @if (auth()->id() === $managedUser->id)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-500/20">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            Terdaftar {{ $managedUser->created_at ? $managedUser->created_at->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="text-xs font-medium text-slate-600 dark:text-slate-300">{{ $managedUser->email }}</span>
                                    <button type="button"
                                            data-copy="{{ $managedUser->email }}"
                                            data-copy-label="Email Pengguna"
                                            class="w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                            title="Salin Email">
                                        <i class="bi bi-copy text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($managedUser->isSuperAdmin())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20">
                                        <i class="bi bi-shield-lock-fill text-[10px]"></i> Superadmin
                                    </span>
                                @elseif ($managedUser->role === 'admin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-500/20">
                                        <i class="bi bi-shield-check text-[10px]"></i> Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        <i class="bi bi-person text-[10px]"></i> User
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-semibold tabular-nums">
                                        {{ $managedUser->mutations_count ?? 0 }} mutasi
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-semibold tabular-nums">
                                        {{ $managedUser->activity_logs_count ?? 0 }} log
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('users.update-role', $managedUser) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="w-36">
                                        <select name="role"
                                                class="w-full px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200"
                                                @disabled(auth()->id() === $managedUser->id)>
                                            <option value="user" @selected($managedUser->role === 'user')>User</option>
                                            <option value="admin" @selected($managedUser->role === 'admin')>Admin</option>
                                            <option value="superadmin" @selected($managedUser->role === 'superadmin')>Superadmin</option>
                                        </select>
                                    </div>
                                    <button type="submit"
                                            class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-40 transition-all cursor-pointer"
                                            @disabled(auth()->id() === $managedUser->id)>
                                        Simpan
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            data-modal-target="editUserModal{{ $managedUser->id }}"
                                            class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                                            title="Edit Profil & Reset Password">
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </button>

                                    @if (auth()->id() !== $managedUser->id)
                                        <button type="button"
                                                data-modal-target="deleteUserModal{{ $managedUser->id }}"
                                                class="w-8 h-8 rounded-xl flex items-center justify-center text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 transition-colors cursor-pointer"
                                                title="Hapus Pengguna">
                                            <i class="bi bi-trash text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                Tidak ada data pengguna yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards Feed --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800" data-framer-stagger>
            @forelse ($users as $managedUser)
                <div class="p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($managedUser->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">{{ $managedUser->name }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $managedUser->email }}</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 shrink-0">
                            {{ $managedUser->role }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                        <button type="button"
                                data-modal-target="editUserModal{{ $managedUser->id }}"
                                class="flex-1 py-1.5 rounded-xl text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 cursor-pointer">
                            <i class="bi bi-pencil-square me-1"></i> Edit
                        </button>
                        @if (auth()->id() !== $managedUser->id)
                            <button type="button"
                                    data-modal-target="deleteUserModal{{ $managedUser->id }}"
                                    class="px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/30 cursor-pointer">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400">Tidak ada pengguna ditemukan.</div>
            @endforelse
        </div>

        @if ($users->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} akun</div>
                <div>{{ $users->links() }}</div>
            </div>
        @endif
    </div>
</div>

{{-- Create User Modal --}}
<div id="createUserModal" class="modal-container responsive-bottom-sheet fixed inset-0 z-[90] hidden items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm overscroll-contain">
    <div class="sheet-dialog relative w-full max-w-lg max-h-[90dvh] overflow-y-auto overscroll-contain rounded-t-3xl sm:rounded-3xl bg-white dark:bg-[#0f1b38] border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-6 space-y-5 animate-fade-in">
        <div class="sheet-grab-handle w-11 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto mb-2 sm:hidden"></div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Pengguna</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Buat akun akses pegawai Diskominfo</p>
                </div>
            </div>
            <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Nama Lengkap Pegawai</label>
                <input type="text" name="name" required placeholder="Contoh: Hendra Wijaya, S.Kom."
                       class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Alamat Email Resmi</label>
                    <input type="email" name="email" required placeholder="nama@garutkab.go.id"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Level Role & Otorisasi</label>
                    <select name="role" class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <option value="user">User / Operator Inventaris</option>
                        <option value="admin">Admin Inventaris (Approver)</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Password Awal</label>
                    <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" data-modal-close class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit & Delete Modals per User --}}
@foreach ($users as $managedUser)
    {{-- Edit / Reset Password Modal --}}
    <div id="editUserModal{{ $managedUser->id }}" class="modal-container responsive-bottom-sheet fixed inset-0 z-[90] hidden items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm overscroll-contain">
        <div class="sheet-dialog relative w-full max-w-lg max-h-[90dvh] overflow-y-auto overscroll-contain rounded-t-3xl sm:rounded-3xl bg-white dark:bg-[#0f1b38] border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-6 space-y-5 animate-fade-in">
            <div class="sheet-grab-handle w-11 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto mb-2 sm:hidden"></div>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Pengguna</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Perbarui identitas, role, atau kata sandi {{ $managedUser->name }}</p>
                </div>
                <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <form action="{{ route('users.update', $managedUser) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ $managedUser->name }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Alamat Email</label>
                        <input type="email" name="email" value="{{ $managedUser->email }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Role Akses</label>
                        <select name="role" class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="user" @selected($managedUser->role === 'user')>User / Operator</option>
                            <option value="admin" @selected($managedUser->role === 'admin')>Admin Inventaris</option>
                            <option value="superadmin" @selected($managedUser->role === 'superadmin')>Superadmin</option>
                        </select>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800 space-y-3">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300">
                        <i class="bi bi-key-fill text-amber-500 me-1"></i> Reset Password (Opsional)
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="password" name="password" minlength="8" placeholder="Password baru (min. 8 krk)"
                               class="w-full px-3 py-2 rounded-xl text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <input type="password" name="password_confirmation" minlength="8" placeholder="Konfirmasi password baru"
                               class="w-full px-3 py-2 rounded-xl text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" data-modal-close class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm cursor-pointer">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete User Modal --}}
    @if (auth()->id() !== $managedUser->id)
        <div id="deleteUserModal{{ $managedUser->id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-center animate-fade-in">
                <button type="button" data-modal-close class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>

                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="bi bi-person-x"></i>
                </div>

                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Hapus Akun Pengguna?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun <strong class="text-slate-800 dark:text-slate-200">{{ $managedUser->name }}</strong> ({{ $managedUser->email }})? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex items-center gap-3">
                    <button type="button" data-modal-close class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 cursor-pointer">
                        Batal
                    </button>
                    <form action="{{ route('users.destroy', $managedUser) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm cursor-pointer">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
