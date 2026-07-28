@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="auth-page">

    {{-- Static blur circles --}}
    <div class="blob blob-tl"></div>
    <div class="blob blob-br"></div>

    {{-- LEBAR CARD DIUBAH DARI 430px JADI 380px --}}
    <div class="w-full relative z-10" style="max-width: 380px;">
        <div class="login-card">

           {{-- Logo --}}
            <div class="flex justify-center mb-3">
                <div class="w-16 h-16 rounded-full bg-white/10 border border-white/20 flex items-center justify-center shadow-lg overflow-hidden shrink-0">
                    <img 
                        src="{{ asset('storage/images/logo.png') }}" 
                        alt="Logo" 
                        class="w-full h-full object-cover rounded-full"
                    >
                </div>
            </div>

            <h1 class="form-title">Login</h1>
            <p class="form-subtitle">Masuk ke akun Anda untuk melanjutkan</p>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="error-box">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf

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

                {{-- Remember & Forgot --}}
                <div class="form-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember_me" value="1">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="loginBtn" class="btn-login">
                    {{-- Spinner Loading (Sejajar dengan Teks) --}}
                    <svg id="loginSpinner" class="spinner hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>

                    {{-- Teks Tombol --}}
                    <span id="loginBtnText">Login</span>
                </button>
            </form>

            <p class="footer-text">
                Belum punya akun?
                <a href="{{ route('register') }}">Register</a>
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
        padding: 24px 26px 20px;
    }

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
        margin: 0 0 18px;
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

    .input-group { margin-bottom: 12px; }
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

    .form-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        font-size: 12px;
    }
    .remember-label { display: flex; align-items: center; gap: 6px; color: #D5DBF3; cursor: pointer; }
    .remember-label input { width: 14px; height: 14px; accent-color: #2563EB; cursor: pointer; }
    .forgot-link { color: #9DB2E8; text-decoration: none; }
    .forgot-link:hover { color: #fff; }

    /* Tombol Login Simetris & Rapi */
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
        gap: 8px; /* Jarak antara spinner dan teks */
    }
    .btn-login:hover { background: linear-gradient(180deg, #3B82F6, #2563EB); }
    .btn-login:active { transform: scale(.98); }
    .btn-login:disabled { opacity: .7; cursor: not-allowed; }

    /* Spinner Animasi */
    .spinner { 
        width: 18px; 
        height: 18px; 
        animation: spin 0.7s linear infinite; 
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .hidden { display: none !important; }

    .footer-text { text-align: center; font-size: 12px; color: #D5DBF3; margin-top: 14px; }
    .footer-text a { color: #fff; font-weight: 600; text-decoration: none; }
    .footer-text a:hover { text-decoration: underline; }

    .copyright-text { text-align: center; font-size: 11px; color: rgba(213, 219, 243, .4); margin-top: 12px; }

    @media (max-width: 640px) {
        .login-card { width: 100%; padding: 20px 18px; }
        .form-title { font-size: 20px; }
    }
</style>
@endsection

@section('extra-js')
<script>
    // Toggle password visibility
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePasswordBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const icon = togglePasswordBtn.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });

    // Loading state saat tombol Login diklik
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginBtnText = document.getElementById('loginBtnText');
    const loginSpinner = document.getElementById('loginSpinner');

    loginForm.addEventListener('submit', function () {
        loginBtn.disabled = true;
        loginSpinner.classList.remove('hidden'); // Tampilkan spinner
        loginBtnText.textContent = 'Signing in...'; // Ubah tulisan jadi Signing in...
    });

    // Auto-focus email
    const emailInput = document.getElementById('email');
    if (!emailInput.value) {
        emailInput.focus();
    }
</script>
@endsection