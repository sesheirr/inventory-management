@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Sistem</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola preferensi antarmuka, profil akun, dan informasi aplikasi</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        {{-- Appearance / Theme Settings --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-palette"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Tema & Tampilan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pilih mode tampilan terang (Light Mode) atau gelap (Dark Mode)</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" data-theme="light" class="segment-button p-4 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center gap-2 text-xs font-semibold hover:border-blue-500 transition-all cursor-pointer">
                    <i class="bi bi-sun text-base text-amber-500"></i>
                    <span>Mode Terang</span>
                </button>
                <button type="button" data-theme="dark" class="segment-button p-4 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center gap-2 text-xs font-semibold hover:border-blue-500 transition-all cursor-pointer">
                    <i class="bi bi-moon-stars text-base text-blue-400"></i>
                    <span>Mode Gelap</span>
                </button>
            </div>
        </div>

        {{-- Profile Edit Card --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Informasi Profil Akun</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ubah nama, email, nomor HP, atau foto profil Anda</p>
                </div>
            </div>

            <button type="button" data-modal-target="editProfileModal" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-[0.98] transition-all cursor-pointer">
                <span>Ubah Profil</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>

        {{-- About Application Card --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Tentang Aplikasi</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Informasi lisensi dan instansi pengelola sistem inventaris</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs pt-1">
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Nama Aplikasi</span>
                    <span class="font-semibold text-slate-900 dark:text-white">Sistem Informasi Inventarisasi Barang dan Aset</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Instansi</span>
                    <span class="font-semibold text-slate-900 dark:text-white">Diskominfo Kab. Garut</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Versi Frontend</span>
                    <span class="inline-flex items-center gap-1.5 font-mono font-bold text-blue-600 dark:text-blue-400">
                        Tailwind CSS v4 Modern Blue
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Profile Modal --}}
<div id="editProfileModal" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 sm:p-8 animate-fade-in max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Edit Data Profil</h3>
            <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Foto Profil</label>
                <input type="file" id="avatarInput" name="avatar" accept="image/*" 
                       class="w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                           class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                           class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Jenis Kelamin</label>
                    <select name="gender" class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" @selected($user->gender === 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected($user->gender === 'Perempuan')>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alamat</label>
                <textarea name="address" rows="2" 
                          class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">{{ old('address', $user->address ?? '') }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
