@extends('layouts.app')

@section('content')
<div class="card dashboard-card mobile-borderless-card">
    <style>
        .back-btn-circle { width: 40px; height: 40px; padding: 0; font-size: 1.1rem; flex-shrink: 0; position: relative; z-index: 1050 !important; pointer-events: auto; }

        /* Mencegah kamera laptop/webcam tampil mirror (terbalik) */
        #scanner-container video {
            transform: scaleX(-1);
            -webkit-transform: scaleX(-1);
        }

        @media (max-width: 576px) {
            .mobile-borderless-card {
                border: none !important;
                border-radius: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                box-shadow: none !important;
                background-color: transparent !important;
            }
        }
    </style>

    <div class="d-flex align-items-center gap-3 mb-4 px-3 px-md-0">
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-circle d-inline-flex d-md-none align-items-center justify-content-center back-btn-circle" title="Kembali">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-semibold mb-1">Edit Barang</h4>
            <p class="text-muted mb-0">Perbarui detail barang ini.</p>
        </div>
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="row g-4 px-2 px-md-0">
        @csrf
        @method('PUT')

        <div class="col-md-6">
            <label class="form-label">Nama Barang</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select" required>
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Kapasitas</label>
            <input type="text" name="subcategory" class="form-control" value="{{ old('subcategory', $product->subcategory) }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Ruangan</label>
            <select name="room_id" class="form-select" required>
                <option value="" selected disabled>Pilih ruangan</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" @selected(old('room_id', $product->room_id) == $room->id)>{{ $room->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Jumlah</label>
            <input type="number" min="0" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="active" @selected(old('status', $product->status) === 'active')>Aktif</option>
                <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Tidak Aktif</option>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Gambar</label>
            <input type="file" name="image" class="form-control">
            @if($product->hasMedia('images'))
                <div class="mt-2">
                    <img src="{{ $product->getFirstMediaUrl('images') }}" alt="Gambar saat ini" class="img-thumbnail" style="max-height:120px;" onerror="this.onerror=null; this.src='https://placehold.co/120x120?text=No+Image';">
                    <label class="form-check-label ms-2">Hapus gambar</label>
                    <input type="checkbox" name="remove_image" value="1" class="form-check-input ms-2">
                </div>
            @endif
        </div>

        {{-- BARCODE FEATURE: Section Barcode + Tombol Scan Kamera --}}
        <div class="col-12">
            <hr class="my-2">
            <h6 class="fw-semibold mb-3 text-muted"><i class="bi bi-upc-scan me-2"></i>Informasi Barcode Aset</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Kode Barcode</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-upc"></i></span>
                        <input type="text"
                            name="barcode"
                            id="barcode-input"
                            class="form-control @error('barcode') is-invalid @enderror"
                            value="{{ old('barcode', $product->barcode) }}"
                            placeholder="Scan stiker barcode atau kosongkan untuk generate otomatis"
                            autocomplete="off"
                            style="text-transform:uppercase;"
                            oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9\-]/g,'')">
                        
                        {{-- Tombol Scan Kamera --}}
                        <button type="button" class="btn btn-outline-primary" id="btn-scan-camera" title="Scan dengan Kamera">
                            <i class="bi bi-camera me-1"></i>Scan
                        </button>

                        {{-- Tombol Preview --}}
                        <button type="button" class="btn btn-outline-secondary" id="preview-barcode-btn">
                            <i class="bi bi-eye me-1"></i>Preview
                        </button>
                    </div>
                    <div class="form-text text-muted mt-1">
                        <i class="bi bi-info-circle me-1"></i>
                        Kosongkan jika barang belum punya stiker — sistem akan generate kode otomatis saat disimpan.
                        Jika barang sudah ada stiker, scan atau ketik kode yang tertera.
                    </div>
                    @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12" id="barcode-preview-container" style="display:none;">
                    <div class="p-3 border rounded-3 text-center bg-white">
                        <svg id="barcode-preview"></svg>
                        <div class="text-muted small mt-1" id="barcode-preview-text"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 form-actions mt-4">
            <button type="submit" class="btn btn-primary rounded-pill px-4 w-100 w-md-auto">Perbarui Barang</button>
        </div>
    </form>
</div>

{{-- Modal Scanner Kamera --}}
<div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-camera me-2"></i>Scan Barcode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="scanner-container" style="width:100%;"></div>
                <div id="scanner-status" class="text-center text-muted small mt-2">Arahkan kamera ke barcode...</div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Library Script: JsBarcode & Html5-Qrcode --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
// BARCODE FEATURE: Preview barcode live
function renderBarcodePreview(value) {
    const container = document.getElementById('barcode-preview-container');
    const svg = document.getElementById('barcode-preview');
    const text = document.getElementById('barcode-preview-text');
    if (value && value.length >= 4) {
        try {
            JsBarcode(svg, value, { format: 'CODE128', width: 2, height: 60, displayValue: false });
            text.textContent = value;
            container.style.display = 'block';
        } catch(e) { container.style.display = 'none'; }
    } else {
        container.style.display = 'none';
    }
}

document.getElementById('barcode-input')?.addEventListener('input', function() {
    renderBarcodePreview(this.value);
});

document.getElementById('preview-barcode-btn')?.addEventListener('click', function() {
    const val = document.getElementById('barcode-input').value;
    renderBarcodePreview(val);
});

document.addEventListener('DOMContentLoaded', function() {
    const existing = document.getElementById('barcode-input')?.value;
    if (existing) renderBarcodePreview(existing);
});

// BARCODE FEATURE: Scan kamera
let html5QrCode = null;

function startScanner() {
    const status = document.getElementById('scanner-status');
    html5QrCode = new Html5Qrcode("scanner-container");

    Html5Qrcode.getCameras().then(cameras => {
        if (!cameras || cameras.length === 0) {
            status.textContent = 'Kamera tidak ditemukan.';
            return;
        }

        const camera = cameras.find(c => /back|rear|environment/i.test(c.label)) || cameras[cameras.length - 1];

        html5QrCode.start(
            camera.id,
            { fps: 10, qrbox: { width: 280, height: 120 } },
            (decodedText) => {
                const input = document.getElementById('barcode-input');
                const cleaned = decodedText.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
                input.value = cleaned;
                renderBarcodePreview(cleaned);

                stopScanner();
                const modal = bootstrap.Modal.getInstance(document.getElementById('scannerModal'));
                modal?.hide();

                status.textContent = 'Barcode berhasil dibaca!';
            },
            (errorMsg) => {}
        ).catch(err => {
            status.textContent = 'Gagal akses kamera: ' + err;
        });

    }).catch(err => {
        status.textContent = 'Error: ' + err;
    });
}

function stopScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
            html5QrCode = null;
        }).catch(() => {});
    }
}

document.getElementById('btn-scan-camera')?.addEventListener('click', function () {
    const modal = new bootstrap.Modal(document.getElementById('scannerModal'));
    modal.show();
    setTimeout(startScanner, 400);
});

document.getElementById('scannerModal')?.addEventListener('hidden.bs.modal', function () {
    stopScanner();
});
</script>
@endsection