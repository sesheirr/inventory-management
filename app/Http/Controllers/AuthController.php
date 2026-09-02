<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CaptchaGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan login form
     */
    public function showLoginForm()
    {
        // Panggil generateSVG() TANPA melempar $captchaCode.
        // Fungsi generateSVG() akan membuat & menyimpan session secara konsisten 1x saja.
        $captchaSvg = CaptchaGenerator::generateSVG();
        
        return view('auth.login', [
            'captchaSvg' => $captchaSvg
        ]);
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Support both security_code (adblock-resistant) and captcha
        $secCode = $request->input('security_code', $request->input('captcha'));
        $request->merge(['security_code' => $secCode, 'captcha' => $secCode]);

        // Validasi input
        $request->validate([
            'email' => 'required|email|string',
            'password' => 'required|string|min:6',
            'security_code' => app()->runningUnitTests() ? 'nullable|string' : 'required|string',
        ], [
            'security_code.required' => 'Kode verifikasi keamanan wajib diisi.',
        ]);

        // Verify captcha / security code
        if (!app()->runningUnitTests() && !CaptchaGenerator::verify($secCode)) {
            return back()->withErrors([
                'security_code' => 'Kode verifikasi salah. Silakan coba lagi.',
                'captcha' => 'Kode verifikasi salah. Silakan coba lagi.',
            ])->withInput($request->only('email'));
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        // Attempt login
        if (Auth::attempt($credentials, $request->remember_me)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', 'Login berhasil!');
        }

        // Jika login gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    /**
     * Tampilkan register form
     */
    public function showRegister()
    {
        $captchaSvg = CaptchaGenerator::generateSVG();

        return view('auth.register', [
            'captchaSvg' => $captchaSvg
        ]);
    }

    /**
     * Handle register request
     */
    public function register(Request $request)
    {
        $secCode = $request->input('security_code', $request->input('captcha'));
        $request->merge(['security_code' => $secCode, 'captcha' => $secCode]);

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'security_code' => app()->runningUnitTests() ? 'nullable|string' : 'required|string',
        ], [
            'security_code.required' => 'Kode verifikasi keamanan wajib diisi.',
        ]);

        // Verify captcha / security code
        if (!app()->runningUnitTests() && !CaptchaGenerator::verify($secCode)) {
            return back()->withErrors([
                'security_code' => 'Kode verifikasi salah. Silakan coba lagi.',
                'captcha' => 'Kode verifikasi salah. Silakan coba lagi.',
            ])->withInput();
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat, silakan login.');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }

    /**
     * Get captcha image via HTTP Request (Wajib sertakan Header SVG & anti-cache)
     */
    public function getCaptchaImage()
    {
        // Selalu buat SVG baru untuk route gambar agar session selalu fresh
        $svg = CaptchaGenerator::generateSVG();

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Refresh Captcha via AJAX
     */
    public function refreshCaptcha()
    {
        $svg = CaptchaGenerator::generateSVG();

        return response()->json([
            'svg' => $svg
        ]);
    }
}