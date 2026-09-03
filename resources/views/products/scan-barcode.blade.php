@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Scan Barcode Aset</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pindai stiker barcode barang untuk langsung membuka data detail</p>
            </div>
        </div>
    </div>

    {{-- Scanner Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 sm:p-8 space-y-6 text-center">
        <div>
            <button type="button" id="btn-start-camera" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/25 active:scale-[0.98] transition-all cursor-pointer">
                <i class="bi bi-camera text-base"></i>
                <span>Aktifkan Kamera Pemindai</span>
            </button>
        </div>

        {{-- Camera Viewport --}}
        <div id="scan-reader" class="w-full max-w-md mx-auto rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800"></div>

        {{-- Status Notification Pill --}}
        <div id="scan-status" class="inline-block px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 transition-all">
            Kamera belum aktif. Klik tombol di atas untuk mulai memindai.
        </div>

        {{-- Manual Barcode Input Fallback --}}
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 max-w-md mx-auto text-left space-y-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Atau Masukkan Barcode Secara Manual
            </label>
            <div class="flex items-center gap-2">
                <input type="text" id="manual-barcode" 
                       placeholder="Contoh: BRG-000025" 
                       class="flex-1 px-4 py-2.5 rounded-xl text-sm font-mono uppercase bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors"
                       oninput="this.value=this.value.toUpperCase()">
                <button type="button" id="btn-manual-search" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors cursor-pointer">
                    Cari
                </button>
            </div>
            <p class="text-[11px] text-slate-400">Tekan Enter atau klik Cari untuk memeriksa data barang di sistem.</p>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
(function () {
    const statusEl = document.getElementById('scan-status');
    const readerEl = document.getElementById('scan-reader');
    const btnStart = document.getElementById('btn-start-camera');
    const manualInput = document.getElementById('manual-barcode');
    const btnManual = document.getElementById('btn-manual-search');

    let html5QrCode = null;
    let isProcessing = false;

    function setStatus(text, type) {
        statusEl.textContent = text;
        statusEl.className = 'inline-block px-4 py-2 rounded-xl text-xs font-semibold transition-all ';
        if (type === 'active') {
            statusEl.className += 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50';
        } else if (type === 'detected') {
            statusEl.className += 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50';
        } else if (type === 'found') {
            statusEl.className += 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50';
        } else if (type === 'notfound') {
            statusEl.className += 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50';
        } else {
            statusEl.className += 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300';
        }
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function searchBarcode(code) {
        if (isProcessing) return;
        isProcessing = true;
        setStatus('Barcode terdeteksi: ' + code + ' — mencari barang di database...', 'detected');

        try {
            const res = await fetch("{{ route('products.scan.search') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ barcode: code }),
            });

            const data = await res.json();

            if (res.ok && data.found) {
                setStatus('Barang ditemukan! Membuka halaman detail...', 'found');
                window.location.href = data.redirect_url;
                return;
            }

            setStatus(data.message || 'Barang dengan barcode tersebut tidak ditemukan.', 'notfound');
        } catch (e) {
            setStatus('Terjadi kendala saat mencari barcode. Silakan coba kembali.', 'notfound');
        } finally {
            isProcessing = false;
        }
    }

    btnStart.addEventListener('click', function () {
        if (html5QrCode && html5QrCode.isScanning) return;

        setStatus('Meminta izin akses kamera perangkat...', 'active');
        html5QrCode = new Html5Qrcode("scan-reader");

        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 100 } },
            function (decodedText) {
                if (isProcessing) return;
                searchBarcode(decodedText.trim().toUpperCase());
            },
            function () { }
        ).then(function () {
            setStatus('Kamera aktif — arahkan tepat ke stiker barcode barang.', 'active');
        }).catch(function (err) {
            setStatus('Kamera tidak dapat diakses. Gunakan kotak input manual di bawah.', 'notfound');
        });
    });

    btnManual.addEventListener('click', function () {
        const code = manualInput.value.trim().toUpperCase();
        if (!code) {
            setStatus('Masukkan kode barcode terlebih dahulu.', 'notfound');
            return;
        }
        searchBarcode(code);
    });

    manualInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnManual.click();
        }
    });

    window.addEventListener('beforeunload', function () {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(() => {});
        }
    });
})();
</script>
@endsection