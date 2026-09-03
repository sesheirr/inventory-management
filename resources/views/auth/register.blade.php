@extends('layouts.auth')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="auth-page min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-slate-950">
    {{-- Ambient Gradient Glows --}}
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-blue-600/20 blur-[130px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-indigo-600/20 blur-[130px] pointer-events-none"></div>

    <div class="w-full max-w-[440px] relative z-10 space-y-4 animate-fade-in my-6">
        <div class="rounded-3xl bg-slate-900/80 border border-slate-700/60 shadow-2xl backdrop-blur-2xl p-6 sm:p-8 space-y-5">
            
            {{-- Brand Header & Logo --}}
            <div class="text-center space-y-2">
                <div class="inline-flex p-2.5 rounded-2xl bg-white shadow-lg shadow-blue-500/15 border border-slate-100 transform hover:scale-105 transition-transform duration-200">
                    <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-14 h-14 object-contain">
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">
                        Daftar Akun Operator
                    </h1>
                    <p class="text-xs font-medium text-blue-300/80 mt-0.5">
                        Sistem Informasi Inventarisasi Barang & Aset
                    </p>
                </div>
            </div>

            {{-- Error Messages --}}
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

            {{-- Register Form --}}
            <form action="{{ route('register') }}" method="POST" id="registerForm" class="space-y-3.5">
                @csrf

                {{-- Nama Lengkap --}}
                <div class="space-y-1 text-left">
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <i class="bi bi-person absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                               placeholder="Nama lengkap Anda"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                    </div>
                </div>

                {{-- Email --}}
                <div class="space-y-1 text-left">
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               placeholder="nama@garutkab.go.id"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                    </div>
                </div>

                {{-- Password & Confirm Password --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                    <div class="space-y-1">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                   placeholder="••••••••"
                                   class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Konfirmasi
                        </label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                   placeholder="••••••••"
                                   class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                        </div>
                    </div>
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
                           placeholder="Masukkan kode di atas"
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all font-mono tracking-wider text-center">
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="registerBtn" class="w-full py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 shadow-lg shadow-blue-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    <span id="registerBtnText">Daftar Akun</span>
                    <i class="bi bi-arrow-right text-base"></i>
                </button>
            </form>

            {{-- Footer Links --}}
            <div class="pt-2 text-center text-xs text-slate-400 border-t border-slate-800">
                <span>Sudah memiliki akun?</span>
                <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-semibold ml-1">
                    Masuk Sekarang
                </a>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-500">
            &copy; 2026 Diskominfo Kabupaten Garut. Hak Cipta Dilindungi.
        </p>
    </div>
</div>
@endsection

@section('extra-js')
<script>
document.addEventListener('DOMContentLoaded', function () {
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