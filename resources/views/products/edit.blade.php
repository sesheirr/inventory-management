@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-5" data-framer-page>
    {{-- Header --}}
    <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-lg sm:text-xl font-bold tracking-tight text-slate-900 dark:text-white">Edit Barang</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui rincian informasi & status barang</p>
            </div>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-5 sm:p-8" data-framer-card>
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Nama Barang --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Nama Barang <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    @error('name')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Kategori --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Kategori <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" id="toggleNewCategoryBtn" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                            + Kategori Baru
                        </button>
                    </div>
                    <select name="category_id" id="categorySelect" @if($categories->isNotEmpty() && !old('category')) required @endif
                            class="{{ ($categories->isEmpty() || old('category')) ? 'hidden' : '' }} w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="category" id="newCategoryInput" value="{{ old('category') }}"
                           placeholder="Ketik nama kategori baru"
                           class="{{ ($categories->isEmpty() || old('category')) ? '' : 'hidden' }} w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400">
                    @error('category_id')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                    @error('category')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Kapasitas / Subkategori --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Kapasitas / Spesifikasi Singkat
                    </label>
                    <input type="text" name="subcategory" value="{{ old('subcategory', $product->subcategory) }}" 
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    @error('subcategory')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Ruangan --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Lokasi Ruangan <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" id="toggleNewRoomBtn" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                            + Ruangan Baru
                        </button>
                    </div>
                    <select name="room_id" id="roomSelect" @if($rooms->isNotEmpty() && !old('room')) required @endif
                            class="{{ ($rooms->isEmpty() || old('room')) ? 'hidden' : '' }} w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="" disabled @selected(!old('room_id', $product->room_id))>Pilih Ruangan</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('room_id', $product->room_id) == $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="room" id="newRoomInput" value="{{ old('room') }}"
                           placeholder="Ketik nama ruangan baru"
                           class="{{ ($rooms->isEmpty() || old('room')) ? '' : 'hidden' }} w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400">
                    @error('room_id')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                    @error('room')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Jumlah Stok --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Jumlah Stok Unit <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    @error('stock')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Status --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Status Barang <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required 
                            class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="active" @selected(old('status', $product->status) === 'active')>Aktif (Tersedia / Digunakan)</option>
                        <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Tidak Aktif (Rusak / Afkir)</option>
                        <option value="out_of_stock" @selected(old('status', $product->status) === 'out_of_stock')>Stok Habis (0 Unit)</option>
                    </select>
                    @error('status')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Deskripsi --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Deskripsi / Catatan Barang
                </label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">{{ old('description', $product->description) }}</textarea>
                @error('description')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            {{-- Foto Barang (Reusable Component with Instant Thumbnail Grid Preview) --}}
            <x-media-uploader
                name="image"
                label="Foto & Galeri Barang"
                hint="(Multi-Upload JPG, PNG, WEBP — Maks. 8MB/file)"
                :max-files="8"
                :initial-gallery="$product->gallery_urls ?? []"
                :initial-image-url="$product->image_url"
                :allow-remove-existing="true"
            />

            {{-- Barcode Section --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                <div class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200">
                    <i class="bi bi-upc-scan text-blue-600 dark:text-blue-400"></i>
                    <span>Kode Barcode</span>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i class="bi bi-upc absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text"
                                   name="barcode"
                                   id="barcode-input"
                                   value="{{ old('barcode', $product->barcode) }}"
                                   placeholder="Scan atau kosongkan (otomatis)"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm font-mono uppercase bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors"
                                   autocomplete="off"
                                   oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9\-]/g,'')">
                        </div>

                        <button type="button" id="btn-scan-camera" class="px-3.5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-sm shadow-blue-500/20 transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                            <i class="bi bi-qr-code-scan"></i>
                            <span>Scan Fullscreen</span>
                        </button>

                        <button type="button" id="preview-barcode-btn" class="px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer shrink-0">
                            <i class="bi bi-eye"></i>
                            <span class="hidden sm:inline">Preview</span>
                        </button>
                    </div>

                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        <i class="bi bi-info-circle me-1 text-blue-500"></i>
                        Kosongkan jika ingin sistem men-generate kode otomatis.
                    </p>
                    @error('barcode')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror

                    {{-- Live Barcode Preview Container --}}
                    <div id="barcode-preview-container" class="hidden p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center">
                        <div class="inline-block bg-white p-3 rounded-lg shadow-sm">
                            <svg id="barcode-preview"></svg>
                        </div>
                        <div class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 mt-2" id="barcode-preview-text"></div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <a href="{{ route('products.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-[0.98] transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Fullscreen Banking-Style Inventory Barcode Scanner HUD Modal --}}
<div id="scannerModal" class="fixed inset-0 z-[120] hidden flex-col justify-between bg-black/95 backdrop-blur-md text-white select-none">
    {{-- Top Banking Scanner HUD Bar --}}
    <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-white/10 bg-black/50">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-blue-600/25 border border-blue-400/40 text-sky-400 flex items-center justify-center">
                <i class="bi bi-qr-code-scan text-base"></i>
            </span>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-extrabold tracking-wide uppercase">Inventory Scanner HUD</h3>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>LIVE OPTIC</span>
                    </span>
                </div>
                <p class="text-[11px] text-white/60">Diskominfo Kab. Garut • Pindai kode128 / QR aset</p>
            </div>
        </div>
        <button type="button" id="btn-close-scanner" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 flex items-center justify-center text-white transition-colors">
            <i class="bi bi-x-lg text-sm"></i>
        </button>
    </div>

    {{-- Center Banking Reticle Viewport --}}
    <div class="relative flex-1 flex flex-col items-center justify-center p-4 overflow-hidden">
        <div class="relative w-full max-w-md aspect-[4/3] rounded-3xl overflow-hidden border border-white/20 bg-black shadow-[0_0_60px_rgba(37,99,235,0.3)]">
            <div id="reader" class="w-full h-full object-cover"></div>
            {{-- Corner Targeting Brackets --}}
            <div class="pointer-events-none absolute inset-6">
                <span class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-sky-400 rounded-tl-xl"></span>
                <span class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-sky-400 rounded-tr-xl"></span>
                <span class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-sky-400 rounded-bl-xl"></span>
                <span class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-sky-400 rounded-br-xl"></span>
                <div class="scanner-laser-line"></div>
            </div>
        </div>
        <p class="text-xs font-semibold text-sky-300 mt-4 flex items-center gap-2 bg-sky-500/10 border border-sky-400/25 px-4 py-2 rounded-full">
            <i class="bi bi-bullseye"></i>
            <span>Posisikan stiker barcode aset di dalam bingkai pemindai</span>
        </p>
    </div>

    {{-- Bottom Banking HUD Controls --}}
    <div class="px-4 sm:px-6 py-4 border-t border-white/10 bg-black/60 flex items-center justify-between gap-3">
        <span class="text-[11px] text-white/60 font-medium">Mendukung Kamera Optik & USB/Bluetooth HID</span>
        <button type="button" id="btn-stop-scanner" class="px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/30 transition-all">
            Tutup Fullscreen
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
function renderBarcodePreview(value) {
    const container = document.getElementById('barcode-preview-container');
    const svg = document.getElementById('barcode-preview');
    const text = document.getElementById('barcode-preview-text');
    if (value && value.length >= 4) {
        try {
            JsBarcode(svg, value, { format: 'CODE128', width: 2, height: 60, displayValue: false });
            text.textContent = value;
            container.classList.remove('hidden');
        } catch(e) { container.classList.add('hidden'); }
    } else {
        container.classList.add('hidden');
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

let html5QrCode = null;
const scannerModal = document.getElementById('scannerModal');

function closeScannerModal() {
    if (scannerModal) {
        scannerModal.classList.add('hidden');
        scannerModal.classList.remove('flex');
    }
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().catch(err => console.log(err));
    }
}

document.getElementById('btn-scan-camera')?.addEventListener('click', function() {
    if (scannerModal) {
        scannerModal.classList.remove('hidden');
        scannerModal.classList.remove('flex');
        scannerModal.classList.add('flex');
    }

    setTimeout(() => {
        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" }, 
            { fps: 10, qrbox: { width: 250, height: 100 } },
            (decodedText) => {
                document.getElementById('barcode-input').value = decodedText.toUpperCase();
                renderBarcodePreview(decodedText.toUpperCase());
                closeScannerModal();
            },
            () => {}
        ).catch(err => {
            alert("Gagal mengakses kamera: " + err);
            closeScannerModal();
        });
    }, 400);
});

document.getElementById('btn-close-scanner')?.addEventListener('click', closeScannerModal);
document.getElementById('btn-stop-scanner')?.addEventListener('click', closeScannerModal);

const toggleNewCategoryBtn = document.getElementById('toggleNewCategoryBtn');
const categorySelect = document.getElementById('categorySelect');
const newCategoryInput = document.getElementById('newCategoryInput');

toggleNewCategoryBtn?.addEventListener('click', function () {
    const isInputVisible = !newCategoryInput.classList.contains('hidden');
    if (isInputVisible && categorySelect.options.length > 1) {
        newCategoryInput.classList.add('hidden');
        newCategoryInput.value = '';
        newCategoryInput.required = false;
        categorySelect.classList.remove('hidden');
        categorySelect.required = true;
        this.textContent = '+ Kategori Baru';
    } else {
        categorySelect.classList.add('hidden');
        categorySelect.required = false;
        newCategoryInput.classList.remove('hidden');
        newCategoryInput.required = true;
        newCategoryInput.focus();
        this.textContent = 'Pilih dari Daftar';
    }
});

const toggleNewRoomBtn = document.getElementById('toggleNewRoomBtn');
const roomSelect = document.getElementById('roomSelect');
const newRoomInput = document.getElementById('newRoomInput');

toggleNewRoomBtn?.addEventListener('click', function () {
    const isInputVisible = !newRoomInput.classList.contains('hidden');
    if (isInputVisible && roomSelect.options.length > 1) {
        newRoomInput.classList.add('hidden');
        newRoomInput.value = '';
        newRoomInput.required = false;
        roomSelect.classList.remove('hidden');
        roomSelect.required = true;
        this.textContent = '+ Ruangan Baru';
    } else {
        roomSelect.classList.add('hidden');
        roomSelect.required = false;
        newRoomInput.classList.remove('hidden');
        newRoomInput.required = true;
        newRoomInput.focus();
        this.textContent = 'Pilih dari Daftar';
    }
});
</script>
@endsection