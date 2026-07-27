@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="auth-page">

    <div class="blob blob-tl"></div>
    <div class="blob blob-br"></div>

    <div class="w-full relative z-10" style="max-width: 380px;">
        <div class="login-card">

            <div class="flex justify-center mb-3">
                <div class="avatar-icon">
                    <i class="bi bi-key-fill"></i>
                </div>
            </div>

            <h1 class="form-title">Reset Password</h1>
            <p class="form-subtitle">Masukkan email dan password baru Anda</p>

            {{-- Alert Sukses --}}
            @if (session('status'))
                <div class="success-box">
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            {{-- Alert Error --}}
            @if ($errors->any())
                <div class="error-box">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.direct_reset') }}" method="POST" id="resetDirectForm">
                @csrf

                {{-- Email --}}
                <div class="input-group">
                    <label for="email">Email Akun</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope icon-left"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            required
                            autofocus
                        >
                    </div>
                </div>

                {{-- Password Baru --}}
                <div class="input-group">
                    <label for="password">Password Baru</label>
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
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div class="input-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
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

                {{-- Submit Button --}}
                <button type="submit" id="resetBtn" class="btn-login" style="margin-top: 14px;">
                    <span id="btnText">Simpan Password Baru</span>
                    <span id="btnSpinner" class="hidden">
                        <svg class="spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </form>

            <p class="footer-text">
                Ingat password Anda?
                <a href="{{ route('login') }}">Kembali ke Login</a>
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

    .success-box {
        background: rgba(34, 197, 94, .15);
        border: 1px solid rgba(74, 222, 128, .3);
        border-radius: 10px;
        padding: 8px 12px;
        margin-bottom: 12px;
    }
    .success-box p { color: #86EFAC; font-size: 12px; margin: 0; text-align: center; }

    .error-box {
        background: rgba(239, 68, 68, .1);
        border: 1px solid rgba(248, 113, 113, .3);
        border-radius: 10px;
        padding: 8px 12px;
        margin-bottom: 12px;
    }
    .error-box p { color: #FCA5A5; font-size: 12px; margin: 0; }

    .input-group { margin-bottom: 12px; }
    .input-group label {
        display: block;
        font-size: 12px;
        color: #D5DBF3;
        margin-bottom: 4px;
        font-weight: 500;
    }

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
    }
    .input-wrap input:focus {
        outline: none;
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
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
    }

    .btn-login {
        width: 100%;
        height: 42px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(180deg, #2563EB, #1D4ED8);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .spinner { width: 16px; height: 16px; animation: spin 0.7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .hidden { display: none !important; }

    .footer-text { text-align: center; font-size: 12px; color: #D5DBF3; margin-top: 14px; }
    .footer-text a { color: #fff; font-weight: 600; text-decoration: none; }

    .copyright-text { text-align: center; font-size: 11px; color: rgba(213, 219, 243, .4); margin-top: 12px; }
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

    const form = document.getElementById('resetDirectForm');
    const btn = document.getElementById('resetBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    form.addEventListener('submit', function () {
        btn.disabled = true;
        btnText.classList.add('hidden');
        btnSpinner.classList.remove('hidden');
    });
</script>
@endsection