@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')
@php
    $hasSavedProfile = !empty($savedProfile) && is_array($savedProfile);
@endphp

<div class="auth-page min-h-[100dvh] w-full flex flex-col items-center justify-center px-4 py-12 sm:p-6 relative overflow-hidden bg-slate-100/80 dark:bg-slate-950 transition-colors duration-300">
    {{-- Subtle Ambient Lighting --}}
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-blue-500/10 dark:bg-blue-600/15 blur-[130px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-sky-500/10 dark:bg-sky-600/15 blur-[130px] pointer-events-none"></div>

    <div class="w-full max-w-[420px] relative z-10 space-y-4">
        {{-- Main Adaptive Card --}}
        <div class="rounded-3xl bg-white/95 dark:bg-slate-900/90 border border-slate-200/90 dark:border-slate-800 shadow-xl shadow-slate-900/5 dark:shadow-black/50 backdrop-blur-2xl p-5 sm:p-7 space-y-5 transition-colors duration-300">

            {{-- Brand Header & Logo --}}
            <div class="text-center space-y-2.5">
                <a href="{{ url('/') }}" class="inline-flex p-2.5 rounded-2xl bg-white dark:bg-slate-800 shadow-md shadow-slate-900/5 border border-slate-100 dark:border-slate-700/80 hover:scale-105 transition-transform duration-200" title="Kembali ke Beranda">
                    <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-14 h-14 object-contain">
                </a>
                <div>
                    <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Sistem Inventaris Diskominfo
                    </h1>
                    <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 mt-0.5">
                        Diskominfo Kabupaten Garut
                    </p>
                </div>
            </div>

            {{-- Compact Flash Alert Messages --}}
            @if(session('success'))
                <div class="px-3.5 py-2.5 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/15 border border-emerald-500/25 text-emerald-700 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-emerald-500 text-sm shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('status'))
                <div class="px-3.5 py-2.5 rounded-xl bg-blue-500/10 dark:bg-blue-500/15 border border-blue-500/25 text-blue-700 dark:text-blue-300 text-xs font-semibold flex items-center gap-2">
                    <i class="bi bi-info-circle-fill text-blue-500 text-sm shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="px-3.5 py-2.5 rounded-xl bg-rose-500/10 dark:bg-rose-500/15 border border-rose-500/25 text-rose-700 dark:text-rose-300 text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="bi bi-exclamation-circle-fill text-rose-500 text-xs shrink-0"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- 1-Click Saved Profile Quick Sign-In Section --}}
            @if($hasSavedProfile)
                <div id="quickSignInSection"
                     data-saved-profile-expires="{{ ($savedProfile['expires_at'] ?? 0) * 1000 }}"
                     class="space-y-2.5">
                    <div class="flex items-center justify-between text-[11px] px-0.5">
                        <span class="font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <i class="bi bi-lightning-charge-fill text-amber-500"></i>
                            <span>Masuk Cepat 1-Klik</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/10 text-blue-600 dark:text-blue-300 border border-blue-500/20">
                            Aktif {{ $savedProfile['days_left'] ?? 7 }} hari lagi
                        </span>
                    </div>

                    {{-- 1-Click Profile Card Button --}}
                    <form action="{{ route('login.quick') }}" method="POST" id="quickLoginForm" class="m-0">
                        @csrf
                        <button type="submit"
                                id="quickLoginSubmitBtn"
                                class="w-full p-3 rounded-2xl bg-slate-50/90 hover:bg-blue-50/60 dark:bg-slate-800/70 dark:hover:bg-slate-800 border border-slate-200/90 hover:border-blue-500/60 dark:border-slate-700/80 dark:hover:border-blue-400/60 shadow-xs hover:shadow-md transition-all duration-200 flex items-center gap-3 text-left group active:scale-[0.99] cursor-pointer">
                            {{-- 1:1 Profile Avatar with Unclipped Status Dot --}}
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-extrabold text-sm flex items-center justify-center shadow-xs border border-white/60 dark:border-slate-700 group-hover:scale-105 transition-transform">
                                    @if(!empty($savedProfile['avatar_url']))
                                        <img src="{{ $savedProfile['avatar_url'] }}" alt="{{ $savedProfile['name'] }}" class="w-full h-full object-cover aspect-square">
                                    @else
                                        <span>{{ $savedProfile['initials'] ?? 'U' }}</span>
                                    @endif
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span>
                            </div>

                            {{-- User Name, Role Badge & Email --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-sm font-extrabold text-slate-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $savedProfile['name'] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-500/15 dark:bg-blue-400/15 text-blue-700 dark:text-blue-300 border border-blue-500/25 shrink-0">
                                        <i class="bi bi-patch-check-fill text-[9px]"></i>
                                        <span>{{ $savedProfile['role_label'] }}</span>
                                    </span>
                                </div>
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                    {{ $savedProfile['email'] }}
                                </p>
                            </div>

                            {{-- Right CTA Pill --}}
                            <div class="px-2.5 py-1.5 rounded-xl bg-blue-600 group-hover:bg-blue-700 text-white text-xs font-bold flex items-center gap-1 shrink-0 shadow-xs transition-all">
                                <span class="hidden sm:inline">Masuk</span>
                                <i class="bi bi-arrow-right-short text-base leading-none group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </button>
                    </form>

                    {{-- Quick Profile Segmented Action Bar --}}
                    <div class="grid grid-cols-2 gap-2 pt-0.5">
                        <button type="button"
                                id="switchToManualLoginBtn"
                                class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                            <i class="bi bi-key text-blue-600 dark:text-blue-400"></i>
                            <span>Masuk Manual</span>
                        </button>

                        <form action="{{ route('login.forget-profile') }}" method="POST" id="forgetProfileForm" class="m-0">
                            @csrf
                            <button type="submit"
                                    id="clearSavedProfileBtn"
                                    class="w-full py-2 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/20 text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                                    title="Hapus profil tersimpan dari perangkat ini">
                                <i class="bi bi-trash3"></i>
                                <span>Hapus Profil</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Manual Login Form --}}
            <form action="{{ route('login') }}"
                  method="POST"
                  id="loginForm"
                  class="space-y-4 {{ $hasSavedProfile && !$errors->any() ? 'hidden' : '' }}">
                @csrf

                @if($hasSavedProfile)
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Login Manual</span>
                        <button type="button" id="backToQuickLoginBtn" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                            <i class="bi bi-lightning-charge-fill text-amber-500"></i>
                            <span>Gunakan Profil Tersimpan</span>
                        </button>
                    </div>
                @endif

                {{-- Email Input --}}
                <div class="space-y-1.5 text-left">
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" id="email" name="email" value="{{ old('email', $savedProfile['email'] ?? '') }}" required autofocus
                               placeholder="nama@garutkab.go.id"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-all">
                    </div>
                </div>

                {{-- Password Input --}}
                <div class="space-y-1.5 text-left">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <i class="bi bi-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" id="password" name="password" required
                               placeholder="Masukkan kata sandi"
                               class="w-full pl-10 pr-10 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-all">
                        <button type="button" id="togglePasswordBtn" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors cursor-pointer" aria-label="Tampilkan kata sandi">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Security Verification (Conditional after 5 failed attempts) --}}
                @if (!empty($showCaptcha))
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-2.5 text-left">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                        Verifikasi Keamanan
                    </label>

                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-11 rounded-xl overflow-hidden bg-slate-900 flex items-center justify-center border border-slate-300 dark:border-slate-700">
                            <img src="{{ route('security.image') }}?t={{ time() }}" id="captcha-img" alt="Kode Keamanan" class="h-full object-contain">
                        </div>
                        <button type="button" id="reload-captcha"
                                class="w-11 h-11 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-white flex items-center justify-center border border-slate-200 dark:border-slate-700 transition-all cursor-pointer"
                                title="Muat Ulang Kode">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <input type="text" name="captcha" required
                           placeholder="Ketik kode keamanan di atas"
                           class="w-full px-3.5 py-2 rounded-xl text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40">

                    @error('captcha')
                        <span class="text-rose-600 dark:text-rose-400 text-[11px] block">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                {{-- Remember Me (7-Day Quick Profile) & Forgot Password --}}
                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                        <input type="checkbox" name="remember_me" id="remember_me" value="1" checked
                               class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-blue-600 focus:ring-blue-500/40">
                        <span>Ingat profil 7 hari (1-Klik)</span>
                    </label>

                    <a href="{{ route('password.request') }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                        Lupa Sandi?
                    </a>
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="submitBtn"
                        class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm shadow-lg shadow-blue-600/20 hover:shadow-blue-600/35 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span id="btnText">Masuk ke Dashboard</span>
                    <i class="bi bi-arrow-right" id="btnArrow"></i>
                </button>
            </form>

            {{-- Footer Register Link --}}
            <div class="pt-3 border-t border-slate-200/80 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between gap-2">
                <span>Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 font-bold hover:underline">Daftar Baru</a></span>
                <a href="{{ url('/') }}" class="text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 font-semibold inline-flex items-center gap-1">
                    <i class="bi bi-house-door"></i>
                    <span>Beranda</span>
                </a>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-400 dark:text-slate-500">
            &copy; {{ date('Y') }} Diskominfo Kabupaten Garut. Hak Cipta Dilindungi.
        </p>
    </div>
</div>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Auto-clear saved profile if client clock passes 7-day expiry
        const quickSection = document.getElementById('quickSignInSection');
        const manualForm = document.getElementById('loginForm');
        if (quickSection) {
            const expMs = parseInt(quickSection.getAttribute('data-saved-profile-expires') || '0', 10);
            if (expMs > 0 && Date.now() > expMs) {
                document.getElementById('forgetProfileForm')?.submit();
            }
        }

        // 2. Toggle between 1-Click Quick Sign-In & Manual Login Form
        const switchToManualBtn = document.getElementById('switchToManualLoginBtn');
        const backToQuickBtn = document.getElementById('backToQuickLoginBtn');

        switchToManualBtn?.addEventListener('click', function () {
            if (quickSection && manualForm) {
                quickSection.classList.add('hidden');
                manualForm.classList.remove('hidden');
                document.getElementById('password')?.focus();
            }
        });

        backToQuickBtn?.addEventListener('click', function () {
            if (quickSection && manualForm) {
                manualForm.classList.add('hidden');
                quickSection.classList.remove('hidden');
            }
        });

        // 3. Toggle Password Visibility
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        togglePasswordBtn?.addEventListener('click', function () {
            if (!passwordInput || !toggleIcon) return;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });

        // 4. Reload Security Code Image
        const reloadBtn = document.getElementById('reload-captcha');
        if (reloadBtn) {
            reloadBtn.addEventListener('click', function () {
                const icon = this.querySelector('i');
                icon?.classList.add('animate-spin');

                fetch("{{ route('security.refresh') }}")
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('captcha-img').src = data.image;
                        icon?.classList.remove('animate-spin');
                    })
                    .catch(() => {
                        document.getElementById('captcha-img').src = "{{ route('security.image') }}?t=" + new Date().getTime();
                        icon?.classList.remove('animate-spin');
                    });
            });
        }

        // 5. Submit Loading Feedback
        manualForm?.addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btnText');
            const arrow = document.getElementById('btnArrow');
            if (btn && text) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                text.textContent = 'Memverifikasi...';
                if (arrow) arrow.className = 'bi bi-arrow-repeat animate-spin';
            }
        });

        document.getElementById('quickLoginForm')?.addEventListener('submit', function () {
            const btn = document.getElementById('quickLoginSubmitBtn');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-wait');
            }
        });
    });
</script>
@endsection