@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="auth-page min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-slate-950">
    {{-- Ambient Gradient Glows --}}
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-blue-600/20 blur-[130px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-indigo-600/20 blur-[130px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-blue-500/10 blur-[150px] pointer-events-none"></div>

    <div class="w-full max-w-[420px] relative z-10 space-y-4 animate-fade-in">
        {{-- Main Glass Card --}}
        <div class="rounded-3xl bg-slate-900/80 border border-slate-700/60 shadow-2xl backdrop-blur-2xl p-6 sm:p-8 space-y-6">
            
            {{-- Brand Header & Logo --}}
            <div class="text-center space-y-3">
                <div class="inline-flex p-2.5 rounded-2xl bg-white shadow-lg shadow-blue-500/15 border border-slate-100 transform hover:scale-105 transition-transform duration-200">
                    <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-16 h-16 object-contain">
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">
                        Sistem Inventaris Diskominfo
                    </h1>
                    <p class="text-xs font-medium text-blue-300/80 mt-0.5">
                        Diskominfo Kabupaten Garut
                    </p>
                </div>
            </div>

            {{-- Flash Alert Messages --}}
            @if(session('success'))
                <div class="p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-medium flex items-center gap-2.5">
                    <i class="bi bi-check-circle-fill text-emerald-400 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="bi bi-exclamation-circle-fill text-rose-400 text-xs shrink-0"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Login Form --}}
            <form action="{{ route('login') }}" method="POST" id="loginForm" class="space-y-4">
                @csrf

                {{-- Email Input --}}
                <div class="space-y-1.5 text-left">
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="nama@garutkab.go.id"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                    </div>
                </div>

                {{-- Password Input --}}
                <div class="space-y-1.5 text-left">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <i class="bi bi-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" id="password" name="password" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                        <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white p-1 transition-colors">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember & Forgot Password --}}
                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="flex items-center gap-2 text-slate-300 cursor-pointer select-none">
                        <input type="checkbox" name="remember_me" value="1" class="w-4 h-4 rounded text-blue-600 bg-slate-800 border-slate-700 focus:ring-blue-500">
                        <span>Ingat saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-blue-400 hover:text-blue-300 transition-colors font-medium">
                        Lupa password?
                    </a>
                </div>

                {{-- Security Verification (Captcha) --}}
                <div class="space-y-2 text-left pt-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Verifikasi Keamanan
                        </label>
                        <button type="button" id="refreshSecCodeBtn" class="text-xs text-blue-400 hover:text-blue-300 flex items-center gap-1 transition-colors cursor-pointer">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Ganti Kode</span>
                        </button>
                    </div>

                    <div id="secChallengeBox" class="h-14 w-full bg-white rounded-xl border border-slate-200 overflow-hidden flex items-center justify-center cursor-pointer shadow-inner hover:border-blue-400 transition-colors" title="Klik untuk mengganti kode verifikasi">
                        <div id="secChallengeRender" class="w-full h-full flex items-center justify-center">
                            {!! $captchaSvg ?? \App\Services\CaptchaGenerator::generateSVG($captchaCode ?? session('captcha_code')) !!}
                        </div>
                    </div>

                    <input type="text" name="security_code" id="secChallengeInput" required autocomplete="off"
                           placeholder="Masukkan kode huruf/angka di atas"
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all font-mono tracking-wider text-center">
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="loginBtn" class="w-full py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 shadow-lg shadow-blue-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    <span id="loginBtnText">Masuk ke Sistem</span>
                    <i class="bi bi-arrow-right text-base"></i>
                </button>
            </form>

            {{-- Footer Links --}}
            <div class="pt-2 text-center text-xs text-slate-400 border-t border-slate-800">
                <span>Belum memiliki akun operator?</span>
                <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-semibold ml-1">
                    Daftar Akun Baru
                </a>
            </div>
        </div>

        {{-- Copyright Notice --}}
        <p class="text-center text-[11px] text-slate-500">
            &copy; 2026 Diskominfo Kabupaten Garut. Hak Cipta Dilindungi.
        </p>
    </div>
</div>
@endsection

@section('extra-js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Password visibility toggle
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePasswordBtn?.addEventListener('click', function () {
        const icon = togglePasswordBtn.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });

    // Form submission loading indicator
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginBtnText = document.getElementById('loginBtnText');

    loginForm?.addEventListener('submit', function () {
        loginBtn.disabled = true;
        loginBtn.classList.add('opacity-75', 'cursor-not-allowed');
        loginBtnText.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memverifikasi...</span>';
    });

    // Captcha Refresh
    const secBox = document.getElementById('secChallengeBox');
    const refreshSecBtn = document.getElementById('refreshSecCodeBtn');
    const secRender = document.getElementById('secChallengeRender');
    const secInput = document.getElementById('secChallengeInput');

    function refreshCaptcha() {
        if (!secRender) return;
        secRender.style.opacity = '0.4';
        fetch('{{ route("security.refresh") }}')
            .then(res => res.json())
            .then(data => {
                if (data.svg) {
                    secRender.innerHTML = data.svg;
                } else if (data.code) {
                    secRender.innerHTML = '<div class="text-2xl font-bold font-mono tracking-widest text-slate-900">' + data.code + '</div>';
                }
                if (secInput) {
                    secInput.value = '';
                    secInput.focus();
                }
            })
            .catch(err => console.error('Error refreshing captcha:', err))
            .finally(() => {
                secRender.style.opacity = '1';
            });
    }

    secBox?.addEventListener('click', refreshCaptcha);
    refreshSecBtn?.addEventListener('click', refreshCaptcha);
});
</script>
@endsection