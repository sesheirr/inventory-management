<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CaptchaGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public const QUICK_PROFILE_COOKIE = 'inv_quick_profile';

    public const QUICK_PROFILE_TTL_DAYS = 7;

    /**
     * Resolve and validate 7-day saved quick login profile from cookie
     */
    private function resolveSavedProfile(Request $request): ?array
    {
        $rawCookie = $request->cookie(self::QUICK_PROFILE_COOKIE);
        if (empty($rawCookie) || ! is_string($rawCookie)) {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($rawCookie), true);
            if (! is_array($payload)) {
                return null;
            }

            $expiresAt = (int) ($payload['expires_at'] ?? 0);
            $savedAt = (int) ($payload['saved_at'] ?? 0);
            $now = now()->timestamp;

            // Auto-clear after 1 week (7 days)
            if ($expiresAt <= $now || ($savedAt > 0 && ($now - $savedAt) > (self::QUICK_PROFILE_TTL_DAYS * 86400))) {
                Cookie::queue(Cookie::forget(self::QUICK_PROFILE_COOKIE));

                return null;
            }

            $userId = (int) ($payload['user_id'] ?? 0);
            $user = User::find($userId);
            if (! $user) {
                Cookie::queue(Cookie::forget(self::QUICK_PROFILE_COOKIE));

                return null;
            }

            $expectedHashPrefix = substr((string) $user->password, 0, 16);
            if (($payload['password_hash_prefix'] ?? '') !== $expectedHashPrefix) {
                Cookie::queue(Cookie::forget(self::QUICK_PROFILE_COOKIE));

                return null;
            }

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_label' => $user->roleLabel(),
                'avatar_url' => $user->avatar_url,
                'initials' => strtoupper(substr($user->name ?? 'U', 0, 2)),
                'saved_at' => $savedAt,
                'expires_at' => $expiresAt,
                'days_left' => max(1, (int) ceil(($expiresAt - $now) / 86400)),
            ];
        } catch (\Throwable $e) {
            Cookie::queue(Cookie::forget(self::QUICK_PROFILE_COOKIE));

            return null;
        }
    }

    /**
     * Build encrypted 7-day quick profile cookie for user
     */
    private function makeSavedProfileCookie(User $user)
    {
        $now = now();
        $expires = $now->copy()->addDays(self::QUICK_PROFILE_TTL_DAYS);

        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role_label' => $user->roleLabel(),
            'avatar_url' => $user->avatar_url,
            'saved_at' => $now->timestamp,
            'expires_at' => $expires->timestamp,
            'password_hash_prefix' => substr((string) $user->password, 0, 16),
        ];

        $encrypted = Crypt::encryptString(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return cookie(
            self::QUICK_PROFILE_COOKIE,
            $encrypted,
            60 * 24 * self::QUICK_PROFILE_TTL_DAYS,
            '/',
            null,
            false,
            true,
            false,
            'Lax'
        );
    }

    /**
     * Tampilkan login form
     */
    public function showLoginForm(Request $request)
    {
        // Panggil generateSVG() TANPA melempar $captchaCode.
        // Fungsi generateSVG() akan membuat & menyimpan session secara konsisten 1x saja.
        $captchaSvg = CaptchaGenerator::generateSVG();
        $savedProfile = $this->resolveSavedProfile($request);

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Auth/Login', [
                'securityChallengeSvg' => $captchaSvg,
                'savedQuickProfile' => $savedProfile,
                'csrfToken' => csrf_token(),
            ]);
        }

        return view('auth.login', [
            'captchaSvg' => $captchaSvg,
            'savedProfile' => $savedProfile,
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
        if (! app()->runningUnitTests() && ! CaptchaGenerator::verify($secCode)) {
            return back()->withErrors([
                'security_code' => 'Kode verifikasi salah. Silakan coba lagi.',
                'captcha' => 'Kode verifikasi salah. Silakan coba lagi.',
            ])->withInput($request->only('email'));
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $remember = $request->boolean('remember_me');

        // Attempt login
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            $redirect = redirect()->route('dashboard')->with('success', 'Login berhasil!');

            if ($remember && $user) {
                $redirect->withCookie($this->makeSavedProfileCookie($user));
            }

            return $redirect;
        }

        // Jika login gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    /**
     * 1-Click Quick Sign-In using valid 7-day saved profile token
     */
    public function quickLogin(Request $request)
    {
        $savedProfile = $this->resolveSavedProfile($request);
        if (! $savedProfile) {
            return redirect()->route('login')
                ->withCookie(Cookie::forget(self::QUICK_PROFILE_COOKIE))
                ->withErrors(['email' => 'Sesi profil tersimpan telah kedaluwarsa (lebih dari 7 hari) atau telah dihapus. Silakan masuk secara manual.']);
        }

        $user = User::find($savedProfile['user_id']);
        if (! $user) {
            return redirect()->route('login')
                ->withCookie(Cookie::forget(self::QUICK_PROFILE_COOKIE))
                ->withErrors(['email' => 'Akun tidak ditemukan. Silakan masuk secara manual.']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang kembali, '.$user->name.'!');
    }

    /**
     * Clear saved quick sign-in profile
     */
    public function forgetSavedProfile(Request $request)
    {
        Cookie::queue(Cookie::forget(self::QUICK_PROFILE_COOKIE));

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('login')
            ->withCookie(Cookie::forget(self::QUICK_PROFILE_COOKIE))
            ->with('success', 'Profil login cepat berhasil dihapus. Silakan masuk secara manual.');
    }

    /**
     * Tampilkan register form
     */
    public function showRegister(Request $request)
    {
        $captchaSvg = CaptchaGenerator::generateSVG();

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Auth/Register', [
                'securityChallengeSvg' => $captchaSvg,
                'csrfToken' => csrf_token(),
            ]);
        }

        return view('auth.register', [
            'captchaSvg' => $captchaSvg,
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'security_code' => app()->runningUnitTests() ? 'nullable|string' : 'required|string',
        ], [
            'security_code.required' => 'Kode verifikasi keamanan wajib diisi.',
        ]);

        // Verify captcha / security code
        if (! app()->runningUnitTests() && ! CaptchaGenerator::verify($secCode)) {
            return back()->withErrors([
                'security_code' => 'Kode verifikasi salah. Silakan coba lagi.',
                'captcha' => 'Kode verifikasi salah. Silakan coba lagi.',
            ])->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
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
            'svg' => $svg,
        ]);
    }
}
