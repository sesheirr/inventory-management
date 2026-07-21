@extends('layouts.app')

@section('content')
<div class="settings-page">
    <div class="settings-hero">
        <div class="settings-hero-inner">
            <div>
                <p class="settings-breadcrumb">Settings</p>
                <h1 class="settings-hero-title">Pengaturan</h1>
                <p class="settings-hero-subtitle">Kelola preferensi aplikasi dan akun Anda.</p>
            </div>
        </div>
    </div>

    <div class="settings-stack">
        <section class="settings-card">
            <div class="settings-card-inner">
                <div class="settings-card-meta">
                    <div class="settings-card-icon settings-card-icon-violet">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="14" rx="2" ry="2"></rect>
                            <path d="M8 20h8"></path>
                            <path d="M12 16v4"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="settings-card-title">Tampilan</h2>
                        <p class="settings-card-text">Atur tampilan aplikasi sesuai preferensi Anda.</p>
                    </div>
                </div>

                <div class="settings-card-action settings-card-action-right">
                    <div>
                        <p class="settings-action-label">Mode</p>
                        <div class="segment-control">
                            <button type="button" class="segment-button" data-theme="light">
                                Light
                            </button>
                            <button type="button" class="segment-button" data-theme="dark">
                                Dark
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-card">
            <div class="settings-card-inner">
                <div class="settings-card-meta">
                    <div class="settings-card-icon settings-card-icon-green">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="11" width="12" height="9" rx="2"></rect>
                            <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="settings-card-title">Keamanan</h2>
                        <p class="settings-card-text">Ubah password akun Anda secara berkala untuk keamanan.</p>
                    </div>
                </div>

                <div class="settings-card-action">
                    <button type="button" class="btn-ghost">
                        Ubah Password
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
                            <path d="M5 12h14"></path>
                            <path d="m13 6 6 6-6 6"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <section class="settings-card">
            <div class="settings-card-inner">
                <div class="settings-card-meta">
                    <div class="settings-card-icon settings-card-icon-yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 8h.01"></path>
                            <path d="M11 12h1v4h1"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="settings-card-title">Tentang Aplikasi</h2>
                        <p class="settings-card-text">Informasi versi dan detail aplikasi inventaris.</p>
                    </div>
                </div>

                <div class="settings-card-action settings-card-info">
                    <div class="info-row">
                        <span class="info-label">Nama Aplikasi</span>
                        <span class="info-value">Inventory Management</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Versi</span>
                        <span class="info-value">v1.0.0</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Developer</span>
                        <span class="info-value">Tim Inventaris</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
