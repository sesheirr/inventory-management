@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Profil Pengguna</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola informasi data pribadi dan akun operator inventaris Anda</p>
        </div>

        <button type="button" data-modal-target="editProfileModal" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-[0.98] transition-all cursor-pointer">
            <i class="bi bi-pencil"></i>
            <span>Ubah Profil</span>
        </button>
    </div>

    {{-- Hero Profile Card --}}
    <div class="rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-700 p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white/20 border-2 border-white/40 shadow-md overflow-hidden flex items-center justify-center shrink-0">
                @if(!empty($user->avatar))
                    @php
                        $avatarUrl = \Illuminate\Support\Str::startsWith($user->avatar, ['http://', 'https://'])
                            ? $user->avatar
                            : asset('storage/' . $user->avatar);
                    @endphp
                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-3xl font-extrabold">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                @endif
            </div>

            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <h2 class="text-2xl font-bold tracking-tight">{{ $user->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 border border-white/30 backdrop-blur-sm">
                        {{ ucfirst($user->role ?? 'Operator') }}
                    </span>
                </div>
                <p class="text-sm text-blue-100 flex items-center justify-center sm:justify-start gap-1.5">
                    <i class="bi bi-envelope"></i>
                    <span>{{ $user->email }}</span>
                </p>
                <div class="pt-2 flex items-center justify-center sm:justify-start gap-4 text-xs text-blue-200">
                    <span><i class="bi bi-calendar-check me-1"></i>Bergabung: {{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Personal Information Cards --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Informasi Pribadi & Kontak</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Telepon / WhatsApp</span>
                <div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">{{ $user->phone ?: 'Belum diisi' }}</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jenis Kelamin</span>
                <div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">{{ $user->gender ?: 'Belum diisi' }}</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Lahir</span>
                <div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">
                    @php
                        $birthDateDisplay = 'Belum diisi';
                        if (!empty($user->birth_date)) {
                            try { $birthDateDisplay = \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y'); }
                            catch (\Throwable $e) { $birthDateDisplay = $user->birth_date; }
                        }
                    @endphp
                    {{ $birthDateDisplay }}
                </div>
            </div>

            <div class="sm:col-span-2 lg:col-span-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat Tempat Tinggal</span>
                <div class="text-sm text-slate-700 dark:text-slate-300 mt-1">{{ $user->address ?: 'Belum ada data alamat yang dicantumkan.' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Profile Modal --}}
<div id="editProfileModal" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 sm:p-8 animate-fade-in max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Ubah Informasi Profil</h3>
            <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Foto Profil (Avatar)</label>
                <input type="file" name="avatar" accept="image/*" 
                       class="w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                           class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alamat Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                           class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                           class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Jenis Kelamin</label>
                    <select name="gender" class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-laki" @selected(old('gender', $user->gender) === 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected(old('gender', $user->gender) === 'Perempuan')>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Tanggal Lahir</label>
                <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}" 
                       class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alamat</label>
                <textarea name="address" rows="2" 
                          class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
