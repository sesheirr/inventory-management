@extends('layouts.app')

@section('title', 'Profil Pengguna — Inventaris Diskominfo Garut')
@section('page-title', 'Profil Pengguna')
@section('page-subtitle', 'Kelola identitas akun, nomor telepon terverifikasi & foto profil 1:1')

@section('content')
@php
    $phoneMeta = $user->phone_meta;
    $birthDateValue = '';
    $birthDateDisplay = 'Belum diisi';
    if (!empty($user->birth_date)) {
        try {
            $parsedBirth = \Carbon\Carbon::parse($user->birth_date);
            $birthDateValue = $parsedBirth->format('Y-m-d');
            $birthDateDisplay = $parsedBirth->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            $birthDateDisplay = (string) $user->birth_date;
        }
    }
@endphp

<div class="max-w-4xl mx-auto space-y-6" data-framer-page>
    {{-- Header --}}
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Profil Pengguna</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola informasi data pribadi dan akun operator inventaris Anda</p>
        </div>

        <button type="button"
                data-modal-target="editProfileModal"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-95 transition-all cursor-pointer">
            <i class="bi bi-pencil-square"></i>
            <span>Ubah</span>
        </button>
    </div>

    {{-- Hero Profile Card --}}
    <div class="rounded-2xl bg-gradient-to-tr from-blue-600 via-blue-700 to-indigo-700 p-6 sm:p-8 text-white shadow-lg relative overflow-hidden" data-framer-card>
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            {{-- 1:1 Avatar with Click-to-Preview Lightbox --}}
            <div class="relative group shrink-0">
                <button type="button"
                        id="heroAvatarPreviewTrigger"
                        data-avatar-src="{{ $user->avatar_url }}"
                        data-avatar-name="{{ $user->name }}"
                        class="w-24 h-24 sm:w-28 sm:h-28 aspect-square rounded-2xl bg-white/20 border-2 border-white/40 shadow-md overflow-hidden flex items-center justify-center relative cursor-pointer group-hover:scale-[1.02] active:scale-95 transition-all"
                        title="{{ !empty($user->avatar_url) ? 'Klik untuk melihat foto profil' : 'Klik untuk mengubah foto profil' }}">
                    <img id="heroAvatarImg"
                         src="{{ $user->avatar_url }}"
                         alt="{{ $user->name }}"
                         class="{{ !empty($user->avatar_url) ? '' : 'hidden' }} w-full h-full object-cover aspect-square">
                    <span id="heroAvatarInitials" class="{{ !empty($user->avatar_url) ? 'hidden' : '' }} text-3xl font-extrabold">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                    </span>
                    <span class="absolute inset-0 bg-slate-950/45 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-1 text-[10px] font-bold text-white">
                        <i class="bi bi-zoom-in text-base"></i>
                        <span>Lihat 1:1</span>
                    </span>
                </button>
                <button type="button"
                        data-modal-target="editProfileModal"
                        class="-bottom-1.5 -right-1.5 absolute w-8 h-8 rounded-xl bg-white text-blue-600 shadow-md border border-blue-100 flex items-center justify-center hover:bg-blue-50 active:scale-90 transition-all cursor-pointer"
                        title="Ubah Foto Profil">
                    <i class="bi bi-camera-fill text-xs"></i>
                </button>
            </div>

            <div class="space-y-2 min-w-0 flex-1">
                {{-- Name & Role Badge Stacked (Badge Below Name per User Spec) --}}
                <div class="flex flex-col items-center sm:items-start gap-1.5">
                    <h2 class="text-2xl font-extrabold tracking-tight leading-tight">{{ $user->name }}</h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/15 border border-white/30 backdrop-blur-md shadow-2xs">
                        <i class="bi {{ $user->isSuperAdmin() ? 'bi-shield-lock-fill text-amber-300' : ($user->isAdmin() ? 'bi-patch-check-fill text-sky-200' : 'bi-person-badge text-blue-100') }}"></i>
                        <span>{{ $user->roleLabel() }}</span>
                    </span>
                </div>

                <p class="text-sm text-blue-100 flex items-center justify-center sm:justify-start gap-1.5 pt-0.5">
                    <i class="bi bi-envelope"></i>
                    <span>{{ $user->email }}</span>
                </p>

                <div class="pt-1.5 flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-blue-100/90">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10">
                        <i class="bi bi-calendar-check"></i>
                        <span>Bergabung: {{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</span>
                    </span>
                    @if(!empty($phoneMeta['formatted']))
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10">
                            <i class="bi bi-whatsapp text-emerald-300"></i>
                            <span>{{ $phoneMeta['formatted'] }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Personal Information Cards --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4" data-framer-card>
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Informasi Pribadi & Kontak</h3>
            <span class="text-[11px] text-slate-400 dark:text-slate-500">Terakhir diperbarui {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'baru saja' }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Telepon / WhatsApp</span>
                @if(!empty($phoneMeta['formatted']))
                    <div class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">{{ $phoneMeta['formatted'] }}</div>
                    <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-300 border border-blue-500/20">
                            <i class="bi bi-globe2"></i>
                            <span>{{ $phoneMeta['name'] }} ({{ $phoneMeta['dial'] }})</span>
                        </span>
                        @if(!empty($phoneMeta['provider']))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 border border-emerald-500/20">
                                {{ $phoneMeta['provider'] }}
                            </span>
                        @endif
                    </div>
                @else
                    <div class="text-sm font-semibold text-slate-400 dark:text-slate-500 mt-1">Belum diisi</div>
                @endif
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jenis Kelamin</span>
                <div class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $user->gender ?: 'Belum diisi' }}</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tanggal Lahir</span>
                <div class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $birthDateDisplay }}</div>
            </div>

            <div class="sm:col-span-2 lg:col-span-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat Tempat Tinggal</span>
                <div class="text-sm text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">{{ $user->address ?: 'Belum ada data alamat yang dicantumkan.' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Full 1:1 Avatar Lightbox Modal --}}
<div id="avatarLightboxModal" class="modal-container fixed inset-0 z-[80] hidden items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md">
    <div class="relative w-full max-w-sm rounded-3xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800 shadow-2xl p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pratinjau Foto Profil (1:1)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $user->name }} &bull; {{ $user->roleLabel() }}</p>
            </div>
            <button type="button" data-modal-close class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>
        <div class="aspect-square w-full rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex items-center justify-center">
            <img id="avatarLightboxImg" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover aspect-square">
        </div>
        <div class="flex items-center justify-end gap-2">
            <button type="button" data-modal-close class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 cursor-pointer">
                Tutup
            </button>
            <button type="button" id="lightboxOpenEditBtn" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 cursor-pointer inline-flex items-center gap-1.5">
                <i class="bi bi-crop"></i>
                <span>Ubah / Crop Foto</span>
            </button>
        </div>
    </div>
</div>

{{-- Edit Profile Modal --}}
<div id="editProfileModal" class="modal-container fixed inset-0 z-[75] hidden items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="relative w-full sm:max-w-xl rounded-t-3xl sm:rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-7 max-h-[92vh] overflow-y-auto overflow-x-hidden">
        <div class="sheet-grab-handle sm:hidden mb-2"></div>

        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Ubah Informasi Profil</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Mendukung crop foto rasio 1:1 (maks 8MB) & deteksi nomor pintar</p>
            </div>
            <button type="button" data-modal-close class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="profileUpdateForm">
            @csrf
            <input type="hidden" name="cropped_avatar" id="croppedAvatarInput" value="">

            {{-- 1:1 Avatar Selector, Live Thumbnail Preview & Interactive Cropper --}}
            <div class="p-3.5 rounded-2xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-3">
                <div class="flex items-center gap-3.5 sm:gap-4">
                    {{-- Live 1:1 Thumbnail Box --}}
                    <button type="button"
                            id="avatarFormPreviewTrigger"
                            class="relative w-16 h-16 sm:w-20 sm:h-20 aspect-square rounded-2xl overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-extrabold text-xl flex items-center justify-center border-2 border-white dark:border-slate-700 shadow-sm shrink-0 group cursor-pointer"
                            title="Klik untuk pratinjau / sesuaikan crop 1:1">
                        <img id="avatarFormPreviewImg"
                             src="{{ $user->avatar_url }}"
                             alt="{{ $user->name }}"
                             class="{{ !empty($user->avatar_url) ? '' : 'hidden' }} w-full h-full object-cover aspect-square">
                        <span id="avatarFormPreviewInitials" class="{{ !empty($user->avatar_url) ? 'hidden' : '' }}">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                        </span>
                        <span class="absolute inset-0 bg-slate-950/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs">
                            <i class="bi bi-crop"></i>
                        </span>
                    </button>

                    <div class="min-w-0 flex-1 space-y-1.5">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <label for="avatarFileInput" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Foto Profil (Rasio 1:1 &bull; Maks 8 MB)
                            </label>
                            <span id="avatarSizeBadge" class="hidden px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 border border-emerald-500/25"></span>
                        </div>
                        <input type="file"
                               name="avatar"
                               id="avatarFileInput"
                               accept="image/jpeg,image/png,image/webp"
                               class="w-full max-w-full px-3 py-1.5 rounded-xl text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-950/60 file:text-blue-700 dark:file:text-blue-300 cursor-pointer">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                            <span>Format JPG, PNG, WEBP hingga 8 MB.</span>
                            <button type="button"
                                    id="toggleAvatarCropStudioBtn"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-300 border border-blue-500/20 font-bold transition-colors cursor-pointer">
                                <i class="bi bi-crop"></i>
                                <span id="toggleAvatarCropStudioLabel">Sesuaikan Crop 1:1</span>
                            </button>
                        </div>
                        <p id="avatarErrorMsg" class="hidden text-xs font-semibold text-rose-600 dark:text-rose-400"></p>
                    </div>
                </div>

                {{-- Interactive 1:1 Square Crop Studio --}}
                <div id="avatarCropperPanel" class="hidden pt-3 border-t border-slate-200/80 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                            <i class="bi bi-aspect-ratio text-blue-600 dark:text-blue-400"></i>
                            <span>Editor Crop Persegi 1:1 (Geser foto & atur zoom)</span>
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" id="avatarCropRotateLeftBtn" class="px-2 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer" title="Putar Kiri 90 Derajat">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button type="button" id="avatarCropRotateBtn" class="px-2 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer" title="Putar Kanan 90 Derajat">
                                <i class="bi bi-arrow-clockwise"></i> Putar
                            </button>
                            <button type="button" id="avatarCropResetBtn" class="px-2 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">
                                Reset
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="relative w-48 h-48 sm:w-52 sm:h-52 aspect-square rounded-2xl overflow-hidden bg-slate-900 border-2 border-blue-500/60 shadow-inner cursor-move touch-none select-none shrink-0">
                            <canvas id="avatarCropCanvas" width="480" height="480" class="w-full h-full block"></canvas>
                            {{-- 1:1 Rule-of-Thirds Grid Overlay --}}
                            <div class="absolute inset-0 pointer-events-none grid grid-cols-3 grid-rows-3">
                                <div class="border-r border-b border-white/20"></div>
                                <div class="border-r border-b border-white/20"></div>
                                <div class="border-b border-white/20"></div>
                                <div class="border-r border-b border-white/20"></div>
                                <div class="border-r border-b border-white/20"></div>
                                <div class="border-b border-white/20"></div>
                                <div class="border-r border-white/20"></div>
                                <div class="border-r border-white/20"></div>
                                <div></div>
                            </div>
                        </div>

                        <div class="w-full flex-1 space-y-3">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-600 dark:text-slate-300">Perbesar (Zoom)</span>
                                    <span id="avatarZoomValueLabel" class="font-bold text-blue-600 dark:text-blue-400 tabular-nums">1.0x</span>
                                </div>
                                <input type="range" id="avatarCropZoomRange" min="1" max="3" step="0.05" value="1" class="w-full accent-blue-600 cursor-pointer">
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                Geser foto di dalam kotak untuk menentukan titik fokus. Thumbnail di atas otomatis diperbarui secara langsung dalam rasio 1:1 (512&times;512 px).
                            </p>
                            <div class="flex items-center gap-2">
                                <button type="button" id="applyAvatarCropBtn" class="flex-1 py-2 px-3 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition-all active:scale-97 cursor-pointer inline-flex items-center justify-center gap-1.5">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Terapkan Crop 1:1</span>
                                </button>
                                <button type="button" id="cancelAvatarCropBtn" class="py-2 px-3 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-200/80 dark:bg-slate-800 hover:bg-slate-300/80 cursor-pointer">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alamat Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500">
                </div>

                {{-- Smart Indonesian & International Phone Input (Zero Overflow Unified Group) --}}
                <div class="sm:col-span-2 space-y-1.5 min-w-0" data-smart-phone-wrapper>
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <label for="smartPhoneInput" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Nomor Telepon / WhatsApp
                        </label>
                        <span id="smartPhoneDetectedBadge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-300 border border-blue-500/20">
                            <i class="bi bi-magic"></i>
                            <span id="smartPhoneDetectedText">{{ !empty($phoneMeta['name']) ? $phoneMeta['name'].' ('.$phoneMeta['dial'].')' : 'Indonesia (+62)' }}</span>
                        </span>
                    </div>

                    <div class="flex items-stretch w-full max-w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-blue-500/25 focus-within:border-blue-500 overflow-hidden transition-all">
                        @php
                            $dialOptions = [
                                ['dial' => '62', 'label' => 'ID (+62)'],
                                ['dial' => '60', 'label' => 'MY (+60)'],
                                ['dial' => '65', 'label' => 'SG (+65)'],
                                ['dial' => '1', 'label' => 'US (+1)'],
                                ['dial' => '44', 'label' => 'GB (+44)'],
                                ['dial' => '61', 'label' => 'AU (+61)'],
                                ['dial' => '81', 'label' => 'JP (+81)'],
                                ['dial' => '82', 'label' => 'KR (+82)'],
                                ['dial' => '966', 'label' => 'SA (+966)'],
                                ['dial' => '971', 'label' => 'AE (+971)'],
                                ['dial' => '86', 'label' => 'CN (+86)'],
                                ['dial' => '91', 'label' => 'IN (+91)'],
                                ['dial' => '49', 'label' => 'DE (+49)'],
                                ['dial' => '31', 'label' => 'NL (+31)'],
                                ['dial' => '33', 'label' => 'FR (+33)'],
                            ];
                            $selectedDial = ltrim((string) ($phoneMeta['dial'] ?? '+62'), '+');
                        @endphp
                        <select name="phone_country_code"
                                id="smartPhoneCountrySelect"
                                data-native-select="true"
                                data-no-custom-select="true"
                                aria-label="Kode Negara Telepon"
                                class="w-[104px] sm:w-[118px] shrink-0 px-2.5 py-2.5 text-xs font-bold bg-slate-100/90 dark:bg-slate-800/90 border-r border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white focus:outline-none cursor-pointer">
                            @foreach($dialOptions as $opt)
                                <option value="{{ $opt['dial'] }}" @selected($selectedDial === $opt['dial'])>{{ $opt['label'] }}</option>
                            @endforeach
                        </select>

                        <div class="relative flex-1 min-w-0 flex items-center">
                            <input type="tel"
                                   name="phone"
                                   id="smartPhoneInput"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="Contoh: 0812-3456-7890 atau +6281234567890"
                                   autocomplete="tel"
                                   class="w-full min-w-0 pl-3 pr-9 py-2.5 text-xs font-semibold bg-transparent border-0 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-0">
                            <span id="smartPhoneStatusIcon" class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-500 pointer-events-none">
                                <i class="bi bi-check-circle-fill text-xs"></i>
                            </span>
                        </div>
                    </div>
                    <p id="smartPhoneHelper" class="text-[11px] text-slate-500 dark:text-slate-400">
                        Ketik langsung <code class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800">08xx</code>, <code class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800">628xx</code>, atau <code class="px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800">+KodeNegara</code> — otomatis mendeteksi negara & menyimpan format standar E.164.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Jenis Kelamin</label>
                    <select name="gender" class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-laki" @selected(old('gender', $user->gender) === 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected(old('gender', $user->gender) === 'Perempuan')>Perempuan</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Tanggal Lahir</label>
                    <input type="date"
                           name="birth_date"
                           value="{{ old('birth_date', $birthDateValue) }}"
                           data-datepicker-mode="birthdate"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alamat</label>
                <textarea name="address" rows="2"
                          class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/25 focus:border-blue-500">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm active:scale-95 transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
