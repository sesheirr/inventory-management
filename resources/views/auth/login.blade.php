@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex">
    <!-- LEFT SECTION - GRADIENT & FEATURES -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-600 to-indigo-700 relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-20 left-10 w-72 h-72 bg-white rounded-full mix-blend-multiply filter blur-xl"></div>
            <div class="absolute top-40 right-10 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-indigo-300 rounded-full mix-blend-multiply filter blur-xl"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col justify-between w-full px-12 py-12">
            <!-- Top section -->
            <div>
                <!-- Icon -->
                <div class="mb-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-10 rounded-2xl backdrop-blur-xl">
                        <i class="bi bi-box-seam text-4xl text-white"></i>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-6 leading-tight">
                    Inventory<br>Management<br>System
                </h1>

                <!-- Description -->
                <p class="text-blue-100 text-lg mb-12 max-w-md leading-relaxed">
                    Manage products, categories, rooms, and reports efficiently in one powerful platform.
                </p>

                <!-- Features List -->
                <div class="space-y-4">
                    <div class="flex items-start gap-4 group">
                        <div class="flex-shrink-0 flex items-center justify-center h-6 w-6 rounded-lg bg-white bg-opacity-20 group-hover:bg-opacity-30 mt-1">
                            <i class="bi bi-check2 text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white font-semibold">Product Management</p>
                            <p class="text-blue-100 text-sm">Organize and track all your products</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="flex-shrink-0 flex items-center justify-center h-6 w-6 rounded-lg bg-white bg-opacity-20 group-hover:bg-opacity-30 mt-1">
                            <i class="bi bi-check2 text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white font-semibold">Category Management</p>
                            <p class="text-blue-100 text-sm">Organize products by categories</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="flex-shrink-0 flex items-center justify-center h-6 w-6 rounded-lg bg-white bg-opacity-20 group-hover:bg-opacity-30 mt-1">
                            <i class="bi bi-check2 text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white font-semibold">Room Management</p>
                            <p class="text-blue-100 text-sm">Manage inventory across locations</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="flex-shrink-0 flex items-center justify-center h-6 w-6 rounded-lg bg-white bg-opacity-20 group-hover:bg-opacity-30 mt-1">
                            <i class="bi bi-check2 text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-white font-semibold">Reports & Analytics</p>
                            <p class="text-blue-100 text-sm">Get insights with detailed reports</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-blue-100 text-sm">
                <p>© 2026 Inventory Management. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- RIGHT SECTION - LOGIN FORM -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12 sm:px-12 lg:px-12 bg-gradient-to-b from-slate-50 to-white">
        <div class="w-full max-w-sm">
            <!-- Logo Section -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl mb-6">
                    <i class="bi bi-box-seam text-2xl text-white"></i>
                </div>
                <h2 class="text-3xl font-bold text-slate-900">Welcome Back</h2>
                <p class="text-slate-600 text-sm mt-2">Sign in to access your inventory dashboard</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex gap-3">
                        <i class="bi bi-exclamation-circle-fill text-red-600 text-lg mt-0.5 flex-shrink-0"></i>
                        <div class="flex-1">
                            @foreach ($errors->all() as $error)
                                <p class="text-red-700 text-sm font-medium">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-4" id="loginForm">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="bi bi-envelope text-lg"></i>
                        </div>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            required
                            class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent hover:border-slate-300 @error('email') border-red-300 focus:ring-red-500 @enderror"
                        >
                    </div>
                    @error('email')
                        <p class="text-red-600 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="bi bi-lock text-lg"></i>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            class="w-full pl-12 pr-12 py-3 bg-white border border-slate-200 rounded-lg text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent hover:border-slate-300 @error('password') border-red-300 focus:ring-red-500 @enderror"
                        >
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none"
                        >
                            <i class="bi bi-eye text-lg"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-600 text-xs mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="checkbox"
                            name="remember_me"
                            value="1"
                            class="w-4 h-4 border border-slate-300 rounded bg-white text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-0 cursor-pointer"
                        >
                        <span class="text-sm text-slate-600 group-hover:text-slate-900">Remember me</span>
                    </label>
                    <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700">Forgot password?</a>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="loginBtn"
                    class="w-full mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-4 rounded-lg focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
                >
                    <span class="inline-flex items-center gap-2" id="btnText">
                        <span>Sign In</span>
                    </span>
                    <span id="btnSpinner" class="hidden inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Signing in...</span>
                    </span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gradient-to-b from-slate-50 to-white text-slate-500">Powered by Inventory Management System</span>
                </div>
            </div>

            <!-- Footer Text -->
            <p class="text-center text-xs text-slate-500">
                © 2026 Inventory Management. All rights reserved.
            </p>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script>
    // Toggle Password Visibility
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePasswordBtn.addEventListener('click', function(e) {
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

    // Add loading state on form submit
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    loginForm.addEventListener('submit', function() {
        loginBtn.disabled = true;
        btnText.classList.add('hidden');
        btnSpinner.classList.remove('hidden');
    });

    // Auto-focus email if empty
    const emailInput = document.getElementById('email');
    if (!emailInput.value) {
        emailInput.focus();
    }
</script>
@endsection
