<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inventaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    
    <style>
        /* Container posisi melayang di tengah atas layar */
        .toast-pill-container {
            top: 24px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 1080;
        }

        /* Desain Dasar Kapsul (Pill) */
        .toast-pill {
            border-radius: 50px !important;
            padding: 8px 24px 8px 10px !important;
            display: flex; /* Tanpa !important agar JS bisa menyembunyikannya */
            align-items: center;
            gap: 12px;
            border: none !important;
            width: auto !important;
            max-width: 450px;
            transition: all 0.4s ease-in-out; /* Transisi halus saat hilang */
            animation: slideDownBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }

        /* Variasi Warna Tema */
        .toast-pill.toast-success {
            background-color: #c9f0d9 !important; /* Hijau pastel */
            color: #1a6b3f !important;
            box-shadow: 0 10px 30px rgba(25, 135, 84, 0.25) !important;
        }

        .toast-pill.toast-danger {
            background-color: #f8d7da !important; /* Merah pastel */
            color: #842029 !important;
            box-shadow: 0 10px 30px rgba(220, 53, 69, 0.25) !important;
        }

        .toast-pill.toast-warning {
            background-color: #fff3cd !important; /* Kuning pastel */
            color: #664d03 !important;
            box-shadow: 0 10px 30px rgba(255, 193, 7, 0.25) !important;
        }

        .toast-pill.toast-info {
            background-color: #cff4fc !important; /* Biru pastel */
            color: #055160 !important;
            box-shadow: 0 10px 30px rgba(13, 202, 240, 0.25) !important;
        }

        /* Wrapper Ikon Lingkaran */
        .toast-icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            color: white;
            animation: popIcon 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .toast-success .toast-icon-circle { background-color: #198754; }
        .toast-danger .toast-icon-circle { background-color: #dc3545; }
        .toast-warning .toast-icon-circle { background-color: #ffc107; color: #000; }
        .toast-info .toast-icon-circle { background-color: #0dcaf0; color: #000; }

        /* Class khusus saat notifikasi menutup */
        .toast-pill.toast-hide {
            opacity: 0 !important;
            transform: translateY(-30px) scale(0.9) !important;
        }

        /* Animasi */
        @keyframes popIcon {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        @keyframes slideDownBounce {
            0% { transform: translateY(-50px) scale(0.8); opacity: 0; }
            100% { transform: translateY(0) scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    @include('partials.sidebar')

    <div class="main-panel">
        @include('partials.navbar')

        <main class="content-area">
            @php
                $toastMessage = session('success') ?: session('error') ?: session('warning') ?: session('info');
                $toastType = session('success') ? 'success' : (session('error') ? 'danger' : (session('warning') ? 'warning' : 'info'));
            @endphp

            @if($toastMessage)
                <div class="toast-container position-fixed toast-pill-container">
                    <div id="liveToast" class="toast-pill toast-{{ $toastType }}" role="alert">
                        <div class="toast-icon-circle">
                            @if($toastType === 'success')
                                <i class="bi bi-check-lg"></i>
                            @elseif($toastType === 'danger')
                                <i class="bi bi-x-lg"></i>
                            @elseif($toastType === 'warning')
                                <i class="bi bi-exclamation-lg"></i>
                            @else
                                <i class="bi bi-info-lg"></i>
                            @endif
                        </div>
                        <div class="fw-semibold p-0 m-0" style="font-size: 0.95rem;">
                            {{ $toastMessage }}
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')

            <footer class="app-footer">
                <span>Copyright © 2026 Inventory Management System.</span>
            </footer>
        </main>
    </div>
</div>

<!-- LOGOUT MODAL -->
@include('components.logout-modal')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>

<!-- Script Auto-Close Murni (100% Pasti Hilang Otomatis) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toastEl = document.getElementById('liveToast');
        if (toastEl) {
            // Tampil selama 3 detik (3000ms), lalu jalankan efek hilang
            setTimeout(() => {
                toastEl.classList.add('toast-hide');
                
                // Hapus elemen dari HTML setelah animasi memudar selesai (400ms)
                setTimeout(() => {
                    toastEl.remove();
                }, 400);
            }, 3000);
        }
    });
</script>
</body>
</html>