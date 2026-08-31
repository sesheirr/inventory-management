@extends('layouts.app')

@section('content')
<div class="card dashboard-card mobile-borderless-card">
    <style>
        .back-btn-circle { width: 40px; height: 40px; padding: 0; font-size: 1.1rem; flex-shrink: 0; }
        #scan-reader { width: 100%; max-width: 480px; margin: 0 auto; border-radius: 12px; overflow: hidden; }
        .scan-status { font-size: 0.95rem; padding: 10px 16px; border-radius: 10px; margin: 16px auto; max-width: 480px; text-align: center; }
        .scan-status.idle { background: #f1f3f5; color: #495057; }
        .scan-status.active { background: #e7f5ff; color: #1971c2; }
        .scan-status.detected { background: #fff9db; color: #997404; }
        .scan-status.found { background: #ebfbee; color: #2b8a3e; }
        .scan-status.notfound { background: #fff5f5; color: #c92a2a; }
        @media (max-width: 576px) {
            .mobile-borderless-card { border: none !important; border-radius: 0 !important; padding-left: 0 !important; padding-right: 0 !important; box-shadow: none !important; background-color: transparent !important; }
        }
    </style>

    <div class="d-flex align-items-center gap-3 mb-4 px-3 px-md-0">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-circle d-inline-flex d-md-none align-items-center justify-content-center back-btn-circle" title="Kembali">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-semibold mb-1">Scan Barcode</h4>
            <p class="text-muted mb-0">Scan barcode barang untuk langsung membuka detailnya.</p>
        </div>
    </div>

    <div class="px-3 px-md-0">
        <div class="text-center mb-3">
            <button type="button" class="btn btn-primary rounded-pill px-4" id="btn-start-camera">
                <i class="bi bi-camera me-1"></i> Aktifkan Kamera
            </button>
        </div>

        <div id="scan-reader"></div>

        <div id="scan-status" class="scan-status idle">Kamera belum aktif.</div>

        <hr class="my-4">

        <div class="mx-auto" style="max-width: 480px;">
            <label class="form-label">Atau masukkan barcode manual</label>
            <div class="input-group">
                <input type="text" id="manual-barcode" class="form-control" placeholder="Contoh: BRG-000025" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
                <button type="button" class="btn btn-outline-primary" id="btn-manual-search">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
            </div>
            <div class="form-text text-muted">Gunakan ini jika kamera tidak dapat diakses.</div>
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
        statusEl.className = 'scan-status ' + type;
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function searchBarcode(code) {
        if (isProcessing) return;
        isProcessing = true;
        setStatus('Barcode terdeteksi: ' + code + ' — mencari barang...', 'detected');

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
                setStatus('Barang ditemukan! Membuka detail...', 'found');
                window.location.href = data.redirect_url;
                return;
            }

            setStatus(data.message || 'Barang dengan barcode tersebut tidak ditemukan.', 'notfound');
        } catch (e) {
            setStatus('Terjadi kesalahan saat mencari barang. Coba lagi.', 'notfound');
        } finally {
            isProcessing = false;
            if (html5QrCode && html5QrCode.isScanning) {
                // kamera tetap aktif, siap scan barang berikutnya
            }
        }
    }

    btnStart.addEventListener('click', function () {
        if (html5QrCode && html5QrCode.isScanning) return;

        setStatus('Meminta izin kamera...', 'active');
        html5QrCode = new Html5Qrcode("scan-reader");

        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 100 } },
            function (decodedText) {
                if (isProcessing) return;
                searchBarcode(decodedText.trim().toUpperCase());
            },
            function () { /* frame tanpa barcode, abaikan */ }
        ).then(function () {
            setStatus('Kamera aktif — arahkan ke barcode barang.', 'active');
        }).catch(function (err) {
            setStatus('Kamera tidak dapat diakses. Gunakan input manual di bawah.', 'notfound');
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