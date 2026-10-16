@extends('layouts.app')

@section('title', 'Scan Barcode Aset — Inventaris Diskominfo Garut')
@section('page-title', 'Scan Barcode Fullscreen')
@section('share_code', 'scan')

@section('content')
<div id="bankingScannerFullscreenContainer"
     data-scanner-fullscreen="true"
     class="fixed inset-0 z-[120] bg-[#0b0a09] text-white flex flex-col justify-between overflow-y-auto select-none transition-all duration-300"
     data-framer-page>

    {{-- Ambient Radial Grid & Banking HUD Glow --}}
    <div class="pointer-events-none fixed inset-0 opacity-20"
         style="background-image: radial-gradient(circle at 50% 35%, rgba(56, 189, 248, 0.22), transparent 65%), linear-gradient(to right, rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.04) 1px, transparent 1px); background-size: 100% 100%, 36px 36px, 36px 36px;"></div>

    {{-- Top Banking-Style Scanner HUD Header --}}
    <div class="relative z-20 flex items-center justify-between gap-3 px-4 sm:px-6 py-3.5 border-b border-white/10 bg-black/55 backdrop-blur-xl shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('products.index') }}"
               class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/15 flex items-center justify-center text-white transition-all shrink-0"
               title="Kembali ke Inventaris">
                <i class="bi bi-arrow-left text-sm"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-extrabold tracking-wide uppercase truncate">
                        Inventory Barcode Scanner
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-sky-500/20 text-sky-300 border border-sky-400/30 shrink-0">
                        <span id="cameraStatusDot" class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                        <span id="cameraStatusLabel">Mendeteksi Kamera...</span>
                    </span>
                </div>
                <p class="text-[11px] text-white/60 truncate">
                    Sistem Inventaris Diskominfo Kab. Garut • Fullscreen Optic &amp; USB/Wireless HID
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button"
                    id="retryCameraDetectBtn"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-sky-300 bg-sky-500/15 hover:bg-sky-500/25 border border-sky-400/30 transition-all cursor-pointer"
                    title="Muat Ulang Kamera">
                <i class="bi bi-arrow-clockwise"></i>
                <span class="hidden sm:inline">Ulangi Kamera</span>
            </button>

            <button type="button"
                    id="toggleNativeFullscreenBtn"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/15 transition-all cursor-pointer"
                    title="Layar Penuh Penuh (Fullscreen)">
                <i id="nativeFullscreenIcon" class="bi bi-arrows-fullscreen"></i>
                <span class="hidden sm:inline">Layar Penuh</span>
            </button>

            <button type="button"
                    id="toggleDockedModeBtn"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-amber-300 bg-amber-500/15 hover:bg-amber-500/25 border border-amber-400/30 transition-all cursor-pointer"
                    title="Beralih Mode Tampilan">
                <i class="bi bi-layout-sidebar-inset"></i>
                <span id="dockedModeBtnText" class="hidden sm:inline">Mode Ringkas</span>
            </button>
        </div>
    </div>

    {{-- Center Banking Scanner Viewport & Target Reticle --}}
    <div id="mobileCameraSection" class="relative z-10 flex-1 flex flex-col items-center justify-center px-4 py-4 sm:py-6 overflow-hidden">
        {{-- Smart Device & Camera Mode Banner (Collapsible on Desktop) --}}
        <div id="desktopScannerNotice" class="hidden w-full max-w-xl mb-4 rounded-2xl bg-amber-500/15 border border-amber-400/30 p-3.5 backdrop-blur-md">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center text-base shrink-0 mt-0.5">
                    <i class="bi bi-usb-symbol"></i>
                </div>
                <div class="space-y-1 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-amber-400/20 text-amber-200">
                            Mode Desktop / Kamera Depan Terdeteksi
                        </span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            USB / Wireless HID Aktif
                        </span>
                    </div>
                    <h2 id="desktopNoticeTitle" class="text-xs sm:text-sm font-bold text-white">
                        Pemindaian Kamera Dioptimalkan untuk Kamera Belakang Mobile
                    </h2>
                    <p id="desktopNoticeDescription" class="text-[11px] text-white/75 leading-relaxed">
                        Gunakan <strong>Alat Scanner Barcode USB / Wireless</strong> atau ketik kode aset pada panel bawah.
                    </p>
                </div>
            </div>
        </div>

        {{-- Banking-Style Optical Scanner Frame --}}
        <div id="readerWrapper"
             class="relative w-full max-w-md aspect-square sm:aspect-[4/3] rounded-[2rem] overflow-hidden bg-black/90 border-2 border-white/15 shadow-[0_0_70px_rgba(37,99,235,0.28)] flex items-center justify-center">
            <div id="reader" class="w-full h-full object-cover"></div>

            {{-- Banking Corner Targeting Brackets + Animated Laser Line --}}
            <div class="pointer-events-none absolute inset-6 sm:inset-8 z-10">
                <span class="absolute top-0 left-0 w-9 h-9 border-t-4 border-l-4 border-sky-400 rounded-tl-2xl shadow-[-4px_-4px_12px_rgba(56,189,248,0.4)]"></span>
                <span class="absolute top-0 right-0 w-9 h-9 border-t-4 border-r-4 border-sky-400 rounded-tr-2xl shadow-[4px_-4px_12px_rgba(56,189,248,0.4)]"></span>
                <span class="absolute bottom-0 left-0 w-9 h-9 border-b-4 border-l-4 border-sky-400 rounded-bl-2xl shadow-[-4px_4px_12px_rgba(56,189,248,0.4)]"></span>
                <span class="absolute bottom-0 right-0 w-9 h-9 border-b-4 border-r-4 border-sky-400 rounded-br-2xl shadow-[4px_4px_12px_rgba(56,189,248,0.4)]"></span>
                <div class="scanner-laser-line"></div>
            </div>

            {{-- Blocked / Front-Camera / Desktop Overlay inside viewport --}}
            <div id="cameraBlockedOverlay" class="hidden absolute inset-0 z-20 bg-black/90 backdrop-blur-md p-6 flex flex-col items-center justify-center text-center text-white space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 text-amber-300 border border-amber-400/30 flex items-center justify-center text-2xl">
                    <i class="bi bi-upc-scan"></i>
                </div>
                <h3 id="blockedOverlayTitle" class="text-sm sm:text-base font-extrabold">
                    Siap Menerima Tembakan Laser Scanner USB / Input Kode
                </h3>
                <p id="blockedOverlayDesc" class="text-xs text-white/70 max-w-xs leading-relaxed">
                    Kamera belakang mobile tidak terdeteksi pada perangkat ini. Tembakkan scanner fisik langsung atau ketik kode barang pada kolom bawah.
                </p>
            </div>
        </div>

        <div class="mt-3.5 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-[11px] font-semibold text-sky-300">
            <i class="bi bi-bullseye"></i>
            <span>Arahkan stiker barcode tepat di tengah kotak bidik otomatis</span>
        </div>
    </div>

    {{-- Bottom Banking Glass Dock: Hardware HID + Manual Barcode Search --}}
    <div class="relative z-20 px-4 sm:px-6 py-4 border-t border-white/10 bg-[#141210]/95 backdrop-blur-2xl shrink-0">
        <div class="max-w-xl mx-auto space-y-3">
            <div class="flex items-center justify-between gap-2">
                <label for="manualBarcodeInput" class="block text-[11px] font-extrabold uppercase tracking-wider text-white/80">
                    Input Scanner Fisik (USB/HID) atau Ketik Kode Barang
                </label>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-400/30 shrink-0">
                    <i class="bi bi-keyboard"></i>
                    <span>Auto-Listen Aktif</span>
                </span>
            </div>

            <div class="flex items-center gap-2.5">
                <div class="relative flex-1">
                    <i class="bi bi-upc-scan absolute left-3.5 top-1/2 -translate-y-1/2 text-sky-400"></i>
                    <input type="text"
                           id="manualBarcodeInput"
                           placeholder="Contoh: BRG-00001"
                           autofocus
                           class="w-full pl-10 pr-4 py-3 rounded-2xl text-sm font-mono font-extrabold uppercase bg-white/10 border border-white/20 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-sky-400/50 focus:border-sky-400 transition-all">
                </div>
                <button type="button"
                        id="manualSearchBtn"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-extrabold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 shadow-lg shadow-blue-600/30 active:scale-95 transition-all cursor-pointer shrink-0">
                    <i class="bi bi-search"></i>
                    <span>Cari Aset</span>
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
    const fullscreenContainer = document.getElementById('bankingScannerFullscreenContainer');
    const toggleDockedBtn = document.getElementById('toggleDockedModeBtn');
    const dockedModeBtnText = document.getElementById('dockedModeBtnText');
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
    let isFullscreenHud = true;

    // Toggle between Fullscreen Banking HUD overlay and Compact Docked Card mode
    toggleDockedBtn?.addEventListener('click', function () {
        if (!fullscreenContainer) return;
        isFullscreenHud = !isFullscreenHud;
        if (isFullscreenHud) {
            fullscreenContainer.className = 'fixed inset-0 z-[120] bg-[#0b0a09] text-white flex flex-col justify-between overflow-y-auto select-none transition-all duration-300';
            if (dockedModeBtnText) dockedModeBtnText.textContent = 'Mode Ringkas';
        } else {
            fullscreenContainer.className = 'relative z-10 max-w-3xl mx-auto rounded-3xl overflow-hidden border border-slate-800 bg-[#0b0a09] text-white flex flex-col justify-between shadow-2xl transition-all duration-300';
            if (dockedModeBtnText) dockedModeBtnText.textContent = 'Mode Fullscreen';
        }
    });

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
            'bg-blue-500/20', 'text-sky-200', 'border-sky-400/40',
            'bg-emerald-500/20', 'text-emerald-200', 'border-emerald-400/40',
            'bg-rose-500/20', 'text-rose-200', 'border-rose-400/40',
            'bg-amber-500/20', 'text-amber-200', 'border-amber-400/40'
        );
        messageEl.classList.add('border');

        if (type === 'success') {
            messageEl.classList.add('bg-emerald-500/20', 'text-emerald-200', 'border-emerald-400/40');
        } else if (type === 'danger') {
            messageEl.classList.add('bg-rose-500/20', 'text-rose-200', 'border-rose-400/40');
        } else if (type === 'warning') {
            messageEl.classList.add('bg-amber-500/20', 'text-amber-200', 'border-amber-400/40');
        } else {
            messageEl.classList.add('bg-blue-500/20', 'text-sky-200', 'border-sky-400/40');
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
                { fps: 15, qrbox: { width: 260, height: 150 } },
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