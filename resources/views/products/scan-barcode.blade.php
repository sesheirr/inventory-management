@extends('layouts.app')

@section('title', 'Scan Barcode Aset — Inventaris Diskominfo Garut')
@section('page-title', 'Scan Barcode Fullscreen')
@section('share_code', 'scan')

@section('content')
<div id="bankingScannerFullscreenContainer"
     data-scanner-fullscreen="true"
     data-barcode-mode="linear-1d"
     class="fixed inset-0 z-[120] bg-[#F8FAFC] dark:bg-[#12100e] text-slate-900 dark:text-[#f5f5f4] flex flex-col justify-between overflow-y-auto select-none transition-colors duration-300"
     data-framer-page>

    {{-- Ambient Technical Grid & Theme-Adaptive Glow --}}
    <div class="pointer-events-none fixed inset-0 opacity-60 dark:opacity-30"
         style="background-image: radial-gradient(circle at 50% 35%, rgba(44, 74, 118, 0.14), transparent 65%), linear-gradient(to right, rgba(148,163,184,0.12) 1px, transparent 1px), linear-gradient(to bottom, rgba(148,163,184,0.12) 1px, transparent 1px); background-size: 100% 100%, 36px 36px, 36px 36px;"></div>

    {{-- Full-Only Scanner Mode Header (No App Topbar / No Mobile Bottom Dock until Exit) --}}
    <div class="relative z-20 flex items-center justify-between gap-3 px-4 sm:px-6 py-3.5 border-b border-slate-200/80 dark:border-stone-800 bg-white/90 dark:bg-[#1c1917]/90 backdrop-blur-xl shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('products.index') }}"
               id="exitScannerModeBtn"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200/80 dark:bg-stone-800 dark:hover:bg-stone-700 border border-slate-200/80 dark:border-stone-700 text-slate-800 dark:text-stone-100 text-xs font-extrabold transition-all shrink-0 cursor-pointer"
               title="Keluar dari Mode Scan Barcode">
                <i class="bi bi-arrow-left text-sm"></i>
                <span>Keluar Mode Scan</span>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-extrabold tracking-wide uppercase truncate text-slate-900 dark:text-stone-100">
                        1D Barcode Sticker Scanner
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-blue-500/10 dark:bg-amber-500/15 text-blue-700 dark:text-amber-300 border border-blue-500/20 dark:border-amber-500/30 shrink-0">
                        <span id="cameraStatusDot" class="w-2 h-2 rounded-full bg-blue-500 dark:bg-amber-400 animate-pulse"></span>
                        <span id="cameraStatusLabel">Mendeteksi Kamera...</span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-stone-400 truncate">
                    Pemindai Stiker Barcode Code-128 Barang Inventaris • Fullscreen Only Mode
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button"
                    id="scannerThemeToggleBtn"
                    onclick="window.applyTheme && window.applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark', { animate: true, notify: true })"
                    class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-amber-400 bg-slate-100 dark:bg-stone-800 hover:bg-slate-200/70 dark:hover:bg-stone-700 border border-slate-200/80 dark:border-stone-700 transition-all cursor-pointer"
                    title="Ganti Tema Terang / Gelap">
                <i class="bi bi-circle-half text-xs"></i>
            </button>

            <button type="button"
                    id="retryCameraDetectBtn"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-blue-700 dark:text-amber-300 bg-blue-50 dark:bg-stone-800 hover:bg-blue-100 dark:hover:bg-stone-700 border border-blue-200/80 dark:border-stone-700 transition-all cursor-pointer"
                    title="Muat Ulang Kamera">
                <i class="bi bi-arrow-clockwise"></i>
                <span class="hidden sm:inline">Ulangi Kamera</span>
            </button>

            <button type="button"
                    id="toggleNativeFullscreenBtn"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-stone-200 bg-slate-100 dark:bg-stone-800 hover:bg-slate-200/80 dark:hover:bg-stone-700 border border-slate-200/80 dark:border-stone-700 transition-all cursor-pointer"
                    title="Layar Penuh Browser">
                <i id="nativeFullscreenIcon" class="bi bi-arrows-fullscreen"></i>
                <span class="hidden sm:inline">Layar Penuh</span>
            </button>
        </div>
    </div>

    {{-- Center 1D Linear Barcode Sticker Viewport & Target Reticle --}}
    <div id="mobileCameraSection" class="relative z-10 flex-1 flex flex-col items-center justify-center px-4 py-4 sm:py-6 overflow-hidden">
        {{-- Smart Device & Camera Mode Banner (Collapsible on Desktop) --}}
        <div id="desktopScannerNotice" class="hidden w-full max-w-xl mb-4 rounded-2xl bg-amber-50 dark:bg-amber-950/35 border border-amber-300/80 dark:border-amber-700/50 p-3.5 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-700 dark:text-amber-300 flex items-center justify-center text-base shrink-0 mt-0.5">
                    <i class="bi bi-usb-symbol"></i>
                </div>
                <div class="space-y-1 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-800 dark:text-amber-200">
                            Mode Desktop / Scanner Fisik USB HID
                        </span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Laser Scanner Aktif
                        </span>
                    </div>
                    <h2 id="desktopNoticeTitle" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-stone-100">
                        Pemindaian Kamera Dioptimalkan untuk Stiker Barcode Barang
                    </h2>
                    <p id="desktopNoticeDescription" class="text-[11px] text-slate-600 dark:text-stone-300 leading-relaxed">
                        Gunakan <strong>Alat Scanner Barcode USB / Wireless</strong> untuk menembak stiker barcode barang, atau ketik kode aset pada panel bawah.
                    </p>
                </div>
            </div>
        </div>

        {{-- Wide 1D Linear Barcode Sticker Optical Frame (Matches Product Barcode Sticker Aspect Ratio) --}}
        <div id="readerWrapper"
             class="relative w-full max-w-xl aspect-[16/9] sm:aspect-[16/7] rounded-[2rem] overflow-hidden bg-white dark:bg-[#1c1917] border-2 border-slate-300 dark:border-stone-700 shadow-2xl flex items-center justify-center">
            <div id="reader" class="w-full h-full object-cover"></div>

            {{-- 1D Linear Barcode Sticker Targeting Brackets + Red Laser Sweep Line --}}
            <div class="pointer-events-none absolute inset-5 sm:inset-7 z-10 flex flex-col items-center justify-between">
                <span class="absolute top-0 left-0 w-9 h-7 border-t-4 border-l-4 border-blue-600 dark:border-amber-400 rounded-tl-xl"></span>
                <span class="absolute top-0 right-0 w-9 h-7 border-t-4 border-r-4 border-blue-600 dark:border-amber-400 rounded-tr-xl"></span>
                <span class="absolute bottom-0 left-0 w-9 h-7 border-b-4 border-l-4 border-blue-600 dark:border-amber-400 rounded-bl-xl"></span>
                <span class="absolute bottom-0 right-0 w-9 h-7 border-b-4 border-r-4 border-blue-600 dark:border-amber-400 rounded-br-xl"></span>

                {{-- Translucent 1D Code-128 Sticker Guide Overlay --}}
                <div class="my-auto flex flex-col items-center justify-center px-4 py-2 rounded-xl border border-dashed border-slate-400/50 dark:border-stone-600/70 bg-white/40 dark:bg-stone-900/40 backdrop-blur-[1px]">
                    <span class="text-[9px] font-extrabold uppercase tracking-widest text-slate-600 dark:text-stone-300 mb-1">
                        STIKER BARCODE INVENTARIS (CODE-128)
                    </span>
                    <svg class="w-48 sm:w-60 h-10 text-slate-800/35 dark:text-stone-200/35" viewBox="0 0 240 42" fill="currentColor" aria-hidden="true">
                        <rect x="4" y="2" width="4" height="38" />
                        <rect x="11" y="2" width="2" height="38" />
                        <rect x="16" y="2" width="6" height="38" />
                        <rect x="26" y="2" width="2" height="38" />
                        <rect x="31" y="2" width="4" height="38" />
                        <rect x="39" y="2" width="8" height="38" />
                        <rect x="51" y="2" width="2" height="38" />
                        <rect x="56" y="2" width="4" height="38" />
                        <rect x="64" y="2" width="6" height="38" />
                        <rect x="74" y="2" width="2" height="38" />
                        <rect x="80" y="2" width="4" height="38" />
                        <rect x="88" y="2" width="2" height="38" />
                        <rect x="94" y="2" width="8" height="38" />
                        <rect x="106" y="2" width="2" height="38" />
                        <rect x="112" y="2" width="4" height="38" />
                        <rect x="120" y="2" width="6" height="38" />
                        <rect x="130" y="2" width="2" height="38" />
                        <rect x="136" y="2" width="4" height="38" />
                        <rect x="144" y="2" width="8" height="38" />
                        <rect x="156" y="2" width="2" height="38" />
                        <rect x="162" y="2" width="4" height="38" />
                        <rect x="170" y="2" width="6" height="38" />
                        <rect x="180" y="2" width="2" height="38" />
                        <rect x="186" y="2" width="4" height="38" />
                        <rect x="194" y="2" width="2" height="38" />
                        <rect x="200" y="2" width="6" height="38" />
                        <rect x="210" y="2" width="4" height="38" />
                        <rect x="218" y="2" width="2" height="38" />
                        <rect x="224" y="2" width="6" height="38" />
                        <rect x="233" y="2" width="3" height="38" />
                    </svg>
                    <span class="font-mono text-[10px] font-black tracking-widest text-slate-600 dark:text-stone-300 mt-0.5">
                        BRG-00001
                    </span>
                </div>

                <div class="scanner-laser-line"></div>
            </div>

            {{-- Blocked / Front-Camera / Desktop Overlay inside viewport --}}
            <div id="cameraBlockedOverlay" class="hidden absolute inset-0 z-20 bg-white/95 dark:bg-[#1c1917]/95 backdrop-blur-md p-6 flex flex-col items-center justify-center text-center text-slate-900 dark:text-stone-100 space-y-2.5">
                <div class="w-13 h-13 rounded-2xl bg-blue-500/10 dark:bg-amber-500/15 text-blue-600 dark:text-amber-300 border border-blue-500/20 dark:border-amber-400/30 flex items-center justify-center text-2xl">
                    <i class="bi bi-upc-scan"></i>
                </div>
                <h3 id="blockedOverlayTitle" class="text-sm sm:text-base font-extrabold">
                    Siap Menerima Tembakan Laser Scanner Barcode (Code-128)
                </h3>
                <p id="blockedOverlayDesc" class="text-xs text-slate-500 dark:text-stone-400 max-w-sm leading-relaxed">
                    Arahkan scanner fisik USB/Wireless ke stiker garis barcode barang (contoh: <span class="font-mono font-bold">BRG-00001</span>) atau ketik pada kolom di bawah.
                </p>
            </div>
        </div>

        <div class="mt-3.5 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white dark:bg-[#1c1917] border border-slate-200/80 dark:border-stone-800 text-[11px] font-bold text-slate-700 dark:text-stone-300 shadow-2xs">
            <i class="bi bi-upc-scan text-blue-600 dark:text-amber-400"></i>
            <span>Sejajarkan garis horizontal stiker barcode barang dengan laser pemindai</span>
        </div>
    </div>

    {{-- Bottom Scanner Dock: Hardware HID + Manual Barcode Search --}}
    <div class="relative z-20 px-4 sm:px-6 py-4 border-t border-slate-200/80 dark:border-stone-800 bg-white/95 dark:bg-[#1c1917]/95 backdrop-blur-2xl shrink-0">
        <div class="max-w-xl mx-auto space-y-3">
            <div class="flex items-center justify-between gap-2">
                <label for="manualBarcodeInput" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-600 dark:text-stone-300">
                    Input Stiker Barcode (USB/HID) atau Ketik Kode Barang
                </label>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25 shrink-0">
                    <i class="bi bi-upc"></i>
                    <span>Code-128 Auto-Listen</span>
                </span>
            </div>

            <div class="flex items-center gap-2.5">
                <div class="relative flex-1">
                    <i class="bi bi-upc-scan absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-600 dark:text-amber-400"></i>
                    <input type="text"
                           id="manualBarcodeInput"
                           placeholder="Contoh: BRG-00001"
                           autofocus
                           class="w-full pl-10 pr-4 py-3 rounded-2xl text-sm font-mono font-extrabold uppercase bg-slate-50 dark:bg-[#12100e] border border-slate-300 dark:border-stone-700 text-slate-900 dark:text-stone-100 placeholder-slate-400 dark:placeholder-stone-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all">
                </div>
                <button type="button"
                        id="manualSearchBtn"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 active:scale-95 transition-all cursor-pointer shrink-0">
                    <i class="bi bi-search"></i>
                    <span>Cari Barang</span>
                </button>
            </div>

            {{-- Dynamic Feedback Message --}}
            <div id="scanMessage" class="hidden p-3 rounded-xl text-xs font-bold"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('scanner-fullscreen-active');
    const fullscreenContainer = document.getElementById('bankingScannerFullscreenContainer');
    const toggleNativeFullscreenBtn = document.getElementById('toggleNativeFullscreenBtn');
    const nativeFullscreenIcon = document.getElementById('nativeFullscreenIcon');
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

    // Native Browser Fullscreen API Toggle
    toggleNativeFullscreenBtn?.addEventListener('click', function () {
        if (!document.fullscreenElement) {
            (fullscreenContainer || document.documentElement).requestFullscreen?.().catch(() => {});
            if (nativeFullscreenIcon) nativeFullscreenIcon.className = 'bi bi-fullscreen-exit';
        } else {
            document.exitFullscreen?.().catch(() => {});
            if (nativeFullscreenIcon) nativeFullscreenIcon.className = 'bi bi-arrows-fullscreen';
        }
    });

    function showMessage(text, type = 'info') {
        if (!messageEl) return;
        messageEl.classList.remove(
            'hidden',
            'bg-blue-500/15', 'text-blue-700', 'dark:text-blue-200', 'border-blue-400/40',
            'bg-emerald-500/15', 'text-emerald-700', 'dark:text-emerald-200', 'border-emerald-400/40',
            'bg-rose-500/15', 'text-rose-700', 'dark:text-rose-200', 'border-rose-400/40',
            'bg-amber-500/15', 'text-amber-700', 'dark:text-amber-200', 'border-amber-400/40'
        );
        messageEl.classList.add('border');

        if (type === 'success') {
            messageEl.classList.add('bg-emerald-500/15', 'text-emerald-700', 'dark:text-emerald-200', 'border-emerald-400/40');
        } else if (type === 'danger') {
            messageEl.classList.add('bg-rose-500/15', 'text-rose-700', 'dark:text-rose-200', 'border-rose-400/40');
        } else if (type === 'warning') {
            messageEl.classList.add('bg-amber-500/15', 'text-amber-700', 'dark:text-amber-200', 'border-amber-400/40');
        } else {
            messageEl.classList.add('bg-blue-500/15', 'text-blue-700', 'dark:text-blue-200', 'border-blue-400/40');
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
            cameraStatusDot.className = 'w-2 h-2 rounded-full bg-amber-400';
        }
        if (cameraStatusLabel) {
            cameraStatusLabel.textContent = 'Mode Scanner USB / Input Kode';
        }
        manualInput?.focus();
    }

    async function initSmartScanner() {
        const isMobileDevice = isMobileOrTabletUA();

        if (!isMobileDevice) {
            activateDesktopOrFrontCamLock(
                'Mode Fullscreen Scanner — Gunakan Scanner Fisik atau Input Kode',
                'Kamera optik otomatis aktif pada smartphone/tablet. Pada Desktop/Laptop, silakan tembakkan <strong>Scanner Barcode USB/Wireless</strong> atau ketik kode aset di bawah.'
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

            const rearCameras = cameras.filter(cam => {
                const l = (cam.label || '').toLowerCase();
                return l.includes('back') || l.includes('rear') || l.includes('belakang') || l.includes('environment');
            });

            const allAreFrontCameras = cameras.every(cam => isFrontOrLaptopWebcamLabel(cam.label || ''));

            if (rearCameras.length === 0 && allAreFrontCameras) {
                activateDesktopOrFrontCamLock(
                    'Hanya Kamera Depan yang Terdeteksi',
                    'Pemindaian stiker barcode membutuhkan kamera belakang dengan autofokus atau <strong>Alat Scanner Barcode Tambahan</strong>.'
                );
                return;
            }

            desktopNotice?.classList.add('hidden');
            blockedOverlay?.classList.add('hidden');
            if (cameraStatusDot) cameraStatusDot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-pulse';
            if (cameraStatusLabel) cameraStatusLabel.textContent = 'Kamera Optik Fullscreen Aktif';

            html5QrCode = new Html5Qrcode("reader");
            const selectedCamera = rearCameras.length > 0 ? rearCameras[0].id : { facingMode: "environment" };

            await html5QrCode.start(
                selectedCamera,
                { fps: 15, qrbox: { width: 300, height: 110 } },
                (decodedText) => {
                    lookupBarcode(decodedText);
                },
                () => {}
            );
        } catch (err) {
            activateDesktopOrFrontCamLock(
                'Kamera Belakang Tidak Tersedia / Izin Ditolak',
                'Pastikan izin kamera diberikan pada perangkat Mobile, atau gunakan scanner USB/Wireless dan input manual di bawah.'
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