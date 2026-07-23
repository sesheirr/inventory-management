@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="auth-page">

    {{-- Static blur circles --}}
    <div class="blob blob-tl"></div>
    <div class="blob blob-br"></div>

    {{-- LEBAR CARD DIUBAH DARI 430px JADI 380px --}}
    <div class="w-full relative z-10" style="max-width: 380px;">
        <div class="login-card">

            {{-- Static avatar icon --}}
            <div class="flex justify-center mb-3">
                <div class="avatar-icon">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>

            <h1 class="form-title">Create Account</h1>
            <p class="form-subtitle">Silakan lengkapi data untuk membuat akun</p>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="error-box">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" id="registerForm">
                @csrf

                {{-- Nama Lengkap --}}
                <div class="input-group">
                    <label for="name">Nama Lengkap</label>
                    <div class="input-wrap">
                        <i class="bi bi-person icon-left"></i>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Nama lengkap Anda"
                            required
                        >
                    </div>
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope icon-left"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            required
                        >
                    </div>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock icon-left"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" id="togglePassword" class="toggle-eye">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="input-group">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill icon-left"></i>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" id="toggleConfirmPassword" class="toggle-eye">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" id="registerBtn" class="btn-login" style="margin-top: 14px;">
                    <span id="btnText">Register</span>
                    <span id="btnSpinner" class="hidden">
                        <svg class="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Membuat akun...
                    </span>
                </button>
            </form>

            <p class="footer-text">
                Sudah punya akun?
                <a href="{{ route('login') }}">Login</a>
            </p>
        </div>

        <p class="copyright-text">&copy; 2026 Inventory Management. All rights reserved.</p>
    </div>
</div>
@endsection

@section('extra-css')
<style>
    .auth-page {
        position: relative;
        min-height: 100vh;
        width: 100%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: linear-gradient(135deg, #081120, #10264d, #183b72, #10264d);
    }

    /* Static blur circles */
    .blob {
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        filter: blur(140px);
        opacity: .35;
        pointer-events: none;
    }
    .blob-tl { top: -80px; left: -80px; background: #2563EB; }
    .blob-br { bottom: -80px; right: -80px; background: #3357d8; }

    /* Padding kartu dikecilkan pas untuk 4 field input */
    .login-card {
        position: relative;
        z-index: 10;
        width: 100%;
        background: rgba(255, 255, 255, .08);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, .15);
        box-shadow: 0 15px 45px rgba(0, 0, 0, .35);
        padding: 22px 26px 18px;
    }

    /* Avatar dikecilkan ke 60px */
    .avatar-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(180deg, #3357d8, #2748bb);
        box-shadow: 0 10px 20px rgba(0, 0, 0, .25);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-icon i { font-size: 28px; color: #fff; }

    /* Judul & Subtitle disesuaikan */
    .form-title {
        text-align: center;
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 4px;
        line-height: 1.2;
    }
    .form-subtitle {
        text-align: center;
        font-size: 13px;
        color: #D5DBF3;
        margin: 0 0 16px;
    }

    .error-box {
        background: rgba(239, 68, 68, .1);
        border: 1px solid rgba(248, 113, 113, .3);
        border-radius: 10px;
        padding: 8px 12px;
        margin-bottom: 12px;
    }
    .error-box p { color: #FCA5A5; font-size: 12px; margin: 0; }
    .error-box p + p { margin-top: 2px; }

    .input-group { margin-bottom: 10px; }
    .input-group label {
        display: block;
        font-size: 12px;
        color: #D5DBF3;
        margin-bottom: 4px;
        font-weight: 500;
    }
    .field-error { color: #FCA5A5; font-size: 11px; margin: 3px 0 0; }

    .input-wrap { position: relative; }
    .input-wrap .icon-left {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #AEB8D8;
        font-size: 15px;
    }
    
    /* Input Field dikecilkan ke 42px */
    .input-wrap input {
        width: 100%;
        height: 42px;
        border-radius: 10px;
        background: rgba(255, 255, 255, .07);
        border: 1px solid rgba(255, 255, 255, .15);
        color: #fff;
        padding: 0 38px;
        font-size: 13px;
        transition: background .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .input-wrap input::placeholder { color: #AEB8D8; }
    .input-wrap input:hover { background: rgba(255, 255, 255, .11); }
    .input-wrap input:focus {
        outline: none;
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
        background: rgba(255, 255, 255, .1);
    }
    .toggle-eye {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #AEB8D8;
        cursor: pointer;
        font-size: 15px;
        padding: 2px;
        line-height: 1;
    }
    .toggle-eye:hover { color: #fff; }

    /* Tombol Register dikecilkan ke 42px */
    .btn-login {
        width: 100%;
        height: 42px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(180deg, #2563EB, #1D4ED8);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: .3px;
        cursor: pointer;
        transition: background .2s ease, transform .2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-login:hover { background: linear-gradient(180deg, #3B82F6, #2563EB); }
    .btn-login:active { transform: scale(.98); }
    .btn-login:disabled { opacity: .7; cursor: not-allowed; }

    .spinner { width: 16px; height: 16px; animation: spin 0.7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .hidden { display: none !important; }

    .footer-text { text-align: center; font-size: 12px; color: #D5DBF3; margin-top: 12px; }
    .footer-text a { color: #fff; font-weight: 600; text-decoration: none; }
    .footer-text a:hover { text-decoration: underline; }

    .copyright-text { text-align: center; font-size: 11px; color: rgba(213, 219, 243, .4); margin-top: 12px; }

    @media (max-width: 640px) {
        .login-card { width: 100%; padding: 18px 16px; }
        .avatar-icon { width: 52px; height: 52px; }
        .avatar-icon i { font-size: 24px; }
        .form-title { font-size: 20px; }
    }
</style>
@endsection

@section('extra-js')
<script>
    function setupToggle(buttonId, inputId) {
        const btn = document.getElementById(buttonId);
        const input = document.getElementById(inputId);
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    }
    setupToggle('togglePassword', 'password');
    setupToggle('toggleConfirmPassword', 'password_confirmation');

    // Loading state on submit
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    registerForm.addEventListener('submit', function () {
        registerBtn.disabled = true;
        btnText.classList.add('hidden');
        btnSpinner.classList.remove('hidden');
    });
</script>
@endsection