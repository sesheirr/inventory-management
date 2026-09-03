@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<div class="auth-page min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-slate-950">
    {{-- Ambient Gradient Glows --}}
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-blue-600/20 blur-[130px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-indigo-600/20 blur-[130px] pointer-events-none"></div>

    <div class="w-full max-w-[420px] relative z-10 space-y-4 animate-fade-in my-6">
        <div class="rounded-3xl bg-slate-900/80 border border-slate-700/60 shadow-2xl backdrop-blur-2xl p-6 sm:p-8 space-y-5">
            
            {{-- Brand Header & Logo --}}
            <div class="text-center space-y-2">
                <div class="inline-flex p-2.5 rounded-2xl bg-white shadow-lg shadow-blue-500/15 border border-slate-100 transform hover:scale-105 transition-transform duration-200">
                    <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-14 h-14 object-contain">
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">
                        Atur Ulang Password
                    </h1>
                    <p class="text-xs font-medium text-blue-300/80 mt-0.5">
                        Masukkan email dan kata sandi baru Anda
                    </p>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('status'))
                <div class="p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-medium flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-emerald-400 text-sm"></i>
                    <span>{{ session('status') }}</span>
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

            <form action="{{ route('password.direct_reset') }}" method="POST" id="resetDirectForm" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div class="space-y-1.5 text-left">
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Alamat Email Akun
                    </label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="nama@garutkab.go.id"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                    </div>
                </div>

                {{-- Password Baru --}}
                <div class="space-y-1.5 text-left">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Kata Sandi Baru
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

                {{-- Konfirmasi Password --}}
                <div class="space-y-1.5 text-left">
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                        Konfirmasi Kata Sandi Baru
                    </label>
                    <div class="relative">
                        <i class="bi bi-lock-fill absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-2.5 rounded-xl text-sm bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                        <button type="button" id="toggleConfirmPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white p-1 transition-colors">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="resetBtn" class="w-full py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 shadow-lg shadow-blue-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    <span id="btnText">Perbarui Kata Sandi</span>
                    <i class="bi bi-check-lg text-base"></i>
                </button>
            </form>

            {{-- Footer Links --}}
            <div class="pt-2 text-center text-xs text-slate-400 border-t border-slate-800">
                <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-semibold inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali ke Halaman Login</span>
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
    function setupToggle(btnId, inputId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        btn?.addEventListener('click', function () {
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    }
    setupToggle('togglePassword', 'password');
    setupToggle('toggleConfirmPassword', 'password_confirmation');
});
</script>
@endsection