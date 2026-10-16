@extends('layouts.app')

@section('title', 'Scan Barcode Aset — Inventaris Diskominfo Garut')
@section('share_code', 'scan')

@section('content')
<div class="max-w-3xl mx-auto space-y-5" data-framer-page>
    {{-- Page Header --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-qr-code-scan text-blue-600 dark:text-blue-400"></i>
                <span>Scan Barcode</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Pindai stiker barcode via kamera belakang HP atau scanner USB/Wireless
            </p>
        </div>

        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-[#0f1b38] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shrink-0">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    {{-- Smart Device & Camera Mode Banner --}}
    <div id="desktopScannerNotice" class="hidden rounded-2xl bg-amber-50/90 dark:bg-amber-950/35 border border-amber-200 dark:border-amber-800/60 p-4 sm:p-5 shadow-xs">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0 mt-0.5">
                <i class="bi bi-usb-symbol"></i>
            </div>
            <div class="space-y-1.5 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-amber-200/70 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300">
                        Mode Desktop / Kamera Depan Terdeteksi
                    </span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Siap Menerima Input Alat Scanner USB / Wireless
                    </span>
                </div>
                <h2 id="desktopNoticeTitle" class="text-sm font-bold text-slate-900 dark:text-white">
                    Pemindaian Kamera Hanya Aktif untuk Pengguna Mobile (Kamera Belakang)
                </h2>
                <p id="desktopNoticeDescription" class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Kamera depan laptop/komputer tidak dapat digunakan untuk memindai barcode secara akurat. Bagi pengguna <strong>Desktop / Laptop</strong>, silakan gunakan <strong>Alat Scanner Barcode Tambahan (USB / Wireless HID)</strong>—cukup tembakkan laser scanner langsung pada stiker barang, atau ketik kode pada kolom pencarian di bawah.
                </p>
            </div>
        </div>
    </div>

    {{-- Main Scanner & Hardware Input Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden" data-framer-card>
        {{-- Mobile Camera Viewport Section --}}
        <div id="mobileCameraSection" class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span id="cameraStatusDot" class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                    <span id="cameraStatusLabel" class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                        Mendeteksi Perangkat & Kamera...
                    </span>
                </div>
                <button type="button"
                        id="retryCameraDetectBtn"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Ulangi</span>
                </button>
            </div>

            {{-- Camera Viewport Box (Only shown on eligible Mobile/Tablet with rear camera) --}}
            <div id="readerWrapper" class="relative w-full max-w-md mx-auto rounded-2xl overflow-hidden bg-slate-900 border-2 border-slate-200 dark:border-slate-700 aspect-square sm:aspect-video flex items-center justify-center">
                <div id="reader" class="w-full h-full"></div>

                {{-- Blocked / Front-Camera / Desktop Overlay inside viewport --}}
                <div id="cameraBlockedOverlay" class="hidden absolute inset-0 bg-slate-900/95 backdrop-blur-md p-6 flex flex-col items-center justify-center text-center text-white space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center text-2xl">
                        <i class="bi bi-camera-video-off"></i>
                    </div>
                    <h3 id="blockedOverlayTitle" class="text-sm sm:text-base font-bold">
                        Kamera Depan Laptop / Desktop Dinonaktifkan
                    </h3>
                    <p id="blockedOverlayDesc" class="text-xs text-slate-300 max-w-xs leading-relaxed">
                        Pemindai kamera web hanya diizinkan pada perangkat Mobile/Tablet yang memiliki kamera belakang. Gunakan alat scanner fisik (USB/Wireless) atau input kode di bawah.
                    </p>
                </div>
            </div>
        </div>

        {{-- Manual & Hardware USB/Wireless Scanner Input Section --}}
        <div class="p-4 sm:p-6 bg-slate-50/60 dark:bg-slate-900/30 space-y-3.5">
            <div class="flex items-center justify-between gap-2">
                <label for="manualBarcodeInput" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                    Input Scanner Fisik / Ketik Kode
                </label>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/40 shrink-0">
                    <i class="bi bi-keyboard"></i> Auto-Listen
                </span>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <i class="bi bi-upc-scan absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text"
                           id="manualBarcodeInput"
                           placeholder="Contoh: BRG-000001"
                           autofocus
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm font-mono font-bold uppercase bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                </div>
                <button type="button"
                        id="manualSearchBtn"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/20 active:scale-[0.98] transition-all cursor-pointer shrink-0">
                    <i class="bi bi-search"></i>
                    <span>Cari</span>
                </button>
            </div>

            {{-- Dynamic Feedback Message --}}
            <div id="scanMessage" class="hidden p-3.5 rounded-xl text-xs font-semibold"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const messageEl = document.getElementById('scanMessage');
    const manualInput = document.getElementById('manualBarcodeInput');
    const manualBtn = document.getElementById('manualSearchBtn');
    const desktopNotice = document.getElementById('desktopScannerNotice');
    const desktopNoticeTitle = document.getElementById('desktopNoticeTitle');
    const desktopNoticeDesc = document.getElementById('desktopNoticeDescription');
    const blockedOverlay = document.getElementById('cameraBlockedOverlay');
    const blockedOverlayTitle = document.getElementById('blockedOverlayTitle');
    const blockedOverlayDesc = document.getElementById('blockedOverlayDesc');
    const cameraStatusDot = document.getElementById('cameraStatusDot');
    const cameraStatusLabel = document.getElementById('cameraStatusLabel');
    const retryBtn = document.getElementById('retryCameraDetectBtn');

    let isProcessing = false;
    let html5QrCode = null;

    function showMessage(text, type = 'info') {
        if (!messageEl) return;
        messageEl.classList.remove(
            'hidden',
            'bg-blue-50', 'text-blue-700', 'border-blue-200', 'dark:bg-blue-950/40', 'dark:text-blue-300',
            'bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 'dark:bg-emerald-950/40', 'dark:text-emerald-300',
            'bg-rose-50', 'text-rose-700', 'border-rose-200', 'dark:bg-rose-950/40', 'dark:text-rose-300',
            'bg-amber-50', 'text-amber-700', 'border-amber-200', 'dark:bg-amber-950/40', 'dark:text-amber-300'
        );
        messageEl.classList.add('border');

        if (type === 'success') {
            messageEl.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 'dark:bg-emerald-950/40', 'dark:text-emerald-300');
        } else if (type === 'danger') {
            messageEl.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200', 'dark:bg-rose-950/40', 'dark:text-rose-300');
        } else if (type === 'warning') {
            messageEl.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200', 'dark:bg-amber-950/40', 'dark:text-amber-300');
        } else {
            messageEl.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200', 'dark:bg-blue-950/40', 'dark:text-blue-300');
        }

        messageEl.textContent = text;
    }

    async function lookupBarcode(rawCode) {
        const code = (rawCode || '').trim().toUpperCase();
        if (!code || isProcessing) return;

        isProcessing = true;
        showMessage('Mencari barang dengan kode: ' + code + ' ...', 'info');

        try {
            const response = await fetch("{{ route('products.scan.search') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ barcode: code })
            });

            const data = await response.json();

            if (!response.ok || !data.found) {
                showMessage(data.message || 'Barang dengan barcode tersebut tidak ditemukan.', 'danger');
                window.toast?.error('Barcode Tidak Ditemukan', { description: `Kode ${code} belum terdaftar.` });
                isProcessing = false;
                return;
            }

            showMessage('Barang ditemukan! Mengalihkan ke halaman detail...', 'success');
            window.toast?.success('Barang Ditemukan!', { description: `Membuka data ${code}...` });
            window.location.href = data.redirect_url;
        } catch (error) {
            showMessage('Terjadi kesalahan saat menghubungi server.', 'danger');
            isProcessing = false;
        }
    }

    manualBtn?.addEventListener('click', function () {
        lookupBarcode(manualInput.value);
    });

    manualInput?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            lookupBarcode(manualInput.value);
        }
    });

    // Global Hardware Barcode Scanner (USB / Wireless HID Wedge) Rapid-Keystroke Listener
    let hidBuffer = '';
    let lastKeyTime = 0;
    document.addEventListener('keydown', function (e) {
        if (document.activeElement === manualInput) return;
        if (e.ctrlKey || e.altKey || e.metaKey) return;

        const now = performance.now();
        if (now - lastKeyTime > 85) {
            hidBuffer = '';
        }
        lastKeyTime = now;

        if (e.key === 'Enter' && hidBuffer.length >= 4) {
            e.preventDefault();
            const scannedCode = hidBuffer.trim().toUpperCase();
            hidBuffer = '';
            if (manualInput) manualInput.value = scannedCode;
            lookupBarcode(scannedCode);
        } else if (e.key.length === 1 && /[A-Za-z0-9\-]/.test(e.key)) {
            hidBuffer += e.key;
        }
    });

    // Smart Device & Front-Camera Detector
    function isMobileOrTabletUA() {
        const ua = navigator.userAgent || '';
        const isMobileRegex = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|Tablet/i.test(ua);
        const isIPadOS = (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        return isMobileRegex || isIPadOS;
    }

    function isFrontOrLaptopWebcamLabel(label = '') {
        const l = label.toLowerCase();
        if (!l) return false;
        const frontKeywords = ['front', 'depan', 'user', 'facetime', 'integrated', 'webcam', 'built-in', 'hd camera', 'pc camera', 'usb video'];
        const rearKeywords = ['back', 'rear', 'belakang', 'environment', 'world', '0, facing back'];
        if (rearKeywords.some(k => l.includes(k))) return false;
        return frontKeywords.some(k => l.includes(k));
    }

    function activateDesktopOrFrontCamLock(reasonTitle, reasonDesc) {
        desktopNotice?.classList.remove('hidden');
        blockedOverlay?.classList.remove('hidden');
        if (desktopNoticeTitle && reasonTitle) desktopNoticeTitle.textContent = reasonTitle;
        if (desktopNoticeDesc && reasonDesc) desktopNoticeDesc.innerHTML = reasonDesc;
        if (blockedOverlayTitle && reasonTitle) blockedOverlayTitle.textContent = reasonTitle;
        if (blockedOverlayDesc && reasonDesc) blockedOverlayDesc.textContent = reasonDesc.replace(/<[^>]*>?/gm, '');

        if (cameraStatusDot) {
            cameraStatusDot.className = 'w-2.5 h-2.5 rounded-full bg-amber-500';
        }
        if (cameraStatusLabel) {
            cameraStatusLabel.textContent = 'Kamera Web Dinonaktifkan — Gunakan Alat Scanner Tambahan';
        }
        manualInput?.focus();
    }

    async function initSmartScanner() {
        const isMobileDevice = isMobileOrTabletUA();

        // 1. If opened on Desktop/Laptop, immediately inform user to use external scanner
        if (!isMobileDevice) {
            activateDesktopOrFrontCamLock(
                'Perangkat Desktop / Laptop Terdeteksi — Gunakan Alat Scanner Tambahan',
                'Fitur scan kamera hanya diperuntukkan bagi pengguna <strong>Mobile / Smartphone</strong>. Untuk pengguna Desktop/Laptop, harap gunakan <strong>Alat Scanner Barcode Tambahan (USB / Wireless)</strong> atau ketik kode barang pada kolom di bawah.'
            );
            return;
        }

        if (typeof Html5Qrcode === 'undefined') {
            showMessage('Pustaka pemindai kamera gagal dimuat. Gunakan input manual di bawah.', 'warning');
            return;
        }

        try {
            const cameras = await Html5Qrcode.getCameras();
            if (!cameras || cameras.length === 0) {
                activateDesktopOrFrontCamLock(
                    'Kamera Tidak Terdeteksi pada Perangkat Ini',
                    'Tidak ditemukan modul kamera belakang. Silakan gunakan alat scanner barcode tambahan (USB/Wireless) atau masukkan kode barang secara manual.'
                );
                return;
            }

            // Smart check: if only 1 camera is present and its label indicates a front/laptop camera
            const rearCameras = cameras.filter(cam => {
                const l = (cam.label || '').toLowerCase();
                return l.includes('back') || l.includes('rear') || l.includes('belakang') || l.includes('environment');
            });

            const allAreFrontCameras = cameras.every(cam => isFrontOrLaptopWebcamLabel(cam.label || ''));

            if (rearCameras.length === 0 && allAreFrontCameras) {
                activateDesktopOrFrontCamLock(
                    'Hanya Kamera Depan yang Terdeteksi — Pemindaian Kamera Tidak Didukung',
                    'Sistem mendeteksi perangkat ini hanya memiliki <strong>Kamera Depan / Webcam</strong>. Pemindaian stiker barcode membutuhkan kamera belakang dengan autofokus atau <strong>Alat Scanner Barcode Tambahan</strong>.'
                );
                return;
            }

            desktopNotice?.classList.add('hidden');
            blockedOverlay?.classList.add('hidden');
            if (cameraStatusDot) cameraStatusDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse';
            if (cameraStatusLabel) cameraStatusLabel.textContent = 'Kamera Belakang Mobile Aktif';

            html5QrCode = new Html5Qrcode("reader");
            const selectedCamera = rearCameras.length > 0 ? rearCameras[0].id : { facingMode: "environment" };

            await html5QrCode.start(
                selectedCamera,
                { fps: 12, qrbox: { width: 250, height: 140 } },
                (decodedText) => {
                    lookupBarcode(decodedText);
                },
                () => {}
            );
        } catch (err) {
            activateDesktopOrFrontCamLock(
                'Kamera Belakang Tidak Tersedia / Izin Ditolak',
                'Pastikan Anda membuka halaman ini melalui perangkat Mobile dan mengizinkan akses kamera belakang, atau gunakan alat scanner barcode tambahan pada komputer desktop.'
            );
        }
    }

    retryBtn?.addEventListener('click', function () {
        if (html5QrCode) {
            html5QrCode.stop().catch(() => {}).finally(() => initSmartScanner());
        } else {
            initSmartScanner();
        }
    });

    initSmartScanner();
});
</script>
@endsection