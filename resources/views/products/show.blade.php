@extends('layouts.app')

@section('page-title', 'Detail Barang')
@section('page-subtitle', $product->name)
@section('title', $product->name . ' — Inventaris Diskominfo Garut')
@section('share_code', 'p-' . $product->id)
@section('og_title', $product->name . ' — Inventaris Diskominfo Garut')
@section('og_description', 'Kategori: ' . $product->category_name . ' • Lokasi: ' . ($product->room_name ?: 'Diskominfo Garut') . ' • Stok: ' . ($product->stock ?? 0) . ' unit.')
@section('og_image', $product->image_url ?: asset('images/logo-diskominfo.png'))

@section('content')
<div class="max-w-5xl mx-auto space-y-4 sm:space-y-6" data-framer-page>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
            <a href="{{ route('products.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shrink-0" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-base sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white truncate">Detail Barang</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 truncate">{{ $product->name }}</p>
            </div>
        </div>

        <div class="grid grid-cols-4 sm:flex items-center gap-1.5 sm:gap-2 shrink-0">
            {{-- Share Short Deep Link Button --}}
            <button type="button"
                    data-share-code="p-{{ $product->id }}"
                    data-share-title="{{ $product->name }} — Inventaris Diskominfo Garut"
                    data-share-description="Kategori: {{ $product->category_name }} • Lokasi: {{ $product->room_name ?: 'Diskominfo Garut' }} • Stok: {{ $product->stock ?? 0 }} unit"
                    data-share-image="{{ $product->image_url ?? asset('images/logo-diskominfo.png') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-2xs transition-all cursor-pointer whitespace-nowrap">
                <i class="bi bi-share text-indigo-600 dark:text-indigo-400"></i>
                <span>Bagikan</span>
            </button>

            <button type="button"
                    data-modal-target="quickProductMutationModal"
                    class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 py-2 rounded-xl text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-all cursor-pointer whitespace-nowrap">
                <i class="bi bi-arrow-left-right"></i>
                <span>Mutasi</span>
            </button>

            @if($product->barcode)
                <button type="button"
                        data-barcode-modal="true"
                        data-barcode="{{ $product->barcode }}"
                        data-product-name="{{ $product->name }}"
                        data-product-room="{{ $product->room_name ?: 'Diskominfo Garut' }}"
                        data-product-category="{{ $product->category_name }}"
                        data-print-url="{{ route('products.barcode.print', $product) }}"
                        class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-2xs transition-all cursor-pointer whitespace-nowrap">
                    <i class="bi bi-printer"></i>
                    <span>Stiker</span>
                </button>
            @endif

            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-all whitespace-nowrap">
                <i class="bi bi-pencil"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Left: Product Image & Barcode Card --}}
        <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 sm:p-6 text-center space-y-4">
            <div class="w-full aspect-square max-h-60 sm:max-h-none rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/80 flex items-center justify-center overflow-hidden p-3 skeleton-shimmer">
                @if($product->image_url)
                    <img id="productMainGalleryPreview"
                         src="{{ $product->image_url }}"
                         alt="{{ $product->name }}"
                         onload="this.parentElement.classList.remove('skeleton-shimmer')"
                         class="max-w-full max-h-full object-contain rounded-xl transition-all duration-200"
                         onerror="this.onerror=null; this.src='{{ asset('images/fallback-torn-paper.svg') }}'; this.parentElement.classList.remove('skeleton-shimmer');">
                @else
                    <img src="{{ asset('images/fallback-torn-paper.svg') }}"
                         alt="Gambar tidak tersedia"
                         onload="this.parentElement.classList.remove('skeleton-shimmer')"
                         class="w-36 h-36 object-contain opacity-85">
                @endif
            </div>

            @if(count($product->gallery_urls) > 1)
                <div class="grid grid-cols-4 gap-2 pt-1">
                    @foreach($product->gallery_urls as $idx => $gUrl)
                        <button type="button"
                                onclick="var mainImg = document.getElementById('productMainGalleryPreview'); if (mainImg) mainImg.src = '{{ $gUrl }}';"
                                class="aspect-square rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-700 hover:border-blue-500 focus:border-blue-600 transition-all cursor-pointer bg-slate-50 dark:bg-slate-900">
                            <img src="{{ $gUrl }}" alt="Thumbnail {{ $idx + 1 }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Barcode Visual & Kode Barang Display with 1-Tap Copy --}}
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 text-center space-y-2">
                <div class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider">Barcode & Kode</div>
                @if($product->barcode)
                    <div class="inline-block bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                        <svg id="detail-barcode-svg" class="mx-auto max-w-full h-11"></svg>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-xs sm:text-sm font-mono font-bold text-slate-900 dark:text-white">{{ $product->barcode }}</span>
                        <button type="button"
                                data-copy="{{ $product->barcode }}"
                                data-copy-label="Kode Barcode"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-200/80 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-100 hover:text-blue-700 transition-colors cursor-pointer"
                                title="Salin">
                            <i class="bi bi-copy text-[10px]"></i>
                            <span>Salin</span>
                        </button>
                    </div>
                @endif
                @if($product->kode_barang && $product->kode_barang !== $product->barcode)
                    <div class="flex items-center justify-center gap-1.5 text-xs font-mono text-slate-500 dark:text-slate-400">
                        <span>Kode: {{ $product->kode_barang }}</span>
                        <button type="button"
                                data-copy="{{ $product->kode_barang }}"
                                data-copy-label="Kode Inventaris"
                                class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 hover:text-blue-600 cursor-pointer"
                                title="Salin">
                            <i class="bi bi-copy text-[10px]"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Specifications & Details --}}
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            {{-- Overview Card --}}
            <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-4 sm:p-6 space-y-3.5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-lg sm:text-2xl font-extrabold text-slate-900 dark:text-white leading-snug">{{ $product->name }}</h2>
                        @if($product->subcategory)
                            <p class="text-xs sm:text-sm font-semibold text-blue-600 dark:text-blue-400 mt-0.5">{{ $product->subcategory }}</p>
                        @endif
                    </div>
                    <div class="shrink-0">
                        @if(($product->stock ?? 0) <= 0 || $product->status === 'out_of_stock')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Habis
                            </span>
                        @elseif($product->status === 'inactive')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Nonaktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">Spesifikasi & Catatan</h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900/40 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                        {{ $product->description ?: 'Tidak ada catatan tambahan.' }}
                    </p>
                </div>
            </div>

            {{-- Metric Specs Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4" data-framer-stagger>
                <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kategori</span>
                    <div class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white mt-1 truncate">
                        @if($product->category_id)
                            <a href="{{ route('categories.show', $product->category_id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $product->category_name }}
                            </a>
                        @else
                            {{ $product->category_name }}
                        @endif
                    </div>
                </div>

                <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Ruangan</span>
                    <div class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white mt-1 truncate">
                        @if($product->room_id)
                            <a href="{{ route('rooms.show', $product->room_id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $product->room_name }}
                            </a>
                        @else
                            {{ $product->room_name ?: '-' }}
                        @endif
                    </div>
                </div>

                <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Stok</span>
                    <div class="text-xs sm:text-base font-extrabold text-blue-600 dark:text-blue-400 mt-1 tabular-nums">
                        {{ $product->stock ?? 0 }} Unit
                    </div>
                </div>

                <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Diperbarui</span>
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1">
                        {{ $product->updated_at ? $product->updated_at->format('d M Y') : '-' }}
                    </div>
                </div>
            </div>

            {{-- Product Mutation Audit Log & Quick Action --}}
            <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs overflow-hidden">
                <div class="px-4 sm:px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-clock-history text-blue-600 dark:text-blue-400"></i>
                        <span>Riwayat Mutasi</span>
                    </h3>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button"
                                data-modal-target="quickProductMutationModal"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 transition-colors cursor-pointer">
                            <i class="bi bi-plus-lg text-[10px]"></i>
                            <span>Mutasi</span>
                        </button>
                        <a href="{{ route('mutations.index', ['product_id' => $product->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                            <span>Semua</span>
                            <i class="bi bi-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                @if($product->relationLoaded('mutations') && $product->mutations->isNotEmpty())
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($product->mutations as $mut)
                            <a href="{{ route('mutations.show', $mut) }}" class="px-4 sm:px-5 py-3 flex items-center justify-between gap-3 text-xs hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
                                <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                    <span class="px-2 py-0.5 rounded-md font-bold uppercase text-[10px] shrink-0 mt-0.5
                                        {{ $mut->type === 'masuk' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($mut->type === 'keluar' ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400') }}">
                                        {{ $mut->type === 'pindah_ruang' ? 'Pindah' : ucfirst($mut->type) }}
                                    </span>
                                    <div class="min-w-0 flex-1 space-y-0.5">
                                        <div class="flex items-center flex-wrap gap-1.5 font-bold text-slate-800 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            <span>{{ $mut->quantity }} unit</span>
                                            @if($mut->fromRoom || $mut->toRoom)
                                                <span class="text-slate-400">•</span>
                                                <span class="text-[11px] font-medium text-slate-600 dark:text-slate-300 truncate">
                                                    {{ $mut->fromRoom?->name ?? '-' }} <i class="bi bi-arrow-right mx-0.5 text-[10px]"></i> {{ $mut->toRoom?->name ?? '-' }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400 truncate">
                                            {{ $mut->user?->name ?? 'Sistem' }} • {{ \Carbon\Carbon::parse($mut->mutation_date)->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $mut->status === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($mut->status === 'rejected' ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400') }}">
                                        {{ $mut->status === 'approved' ? 'Disetujui' : ($mut->status === 'rejected' ? 'Ditolak' : 'Pending') }}
                                    </span>
                                    <i class="bi bi-chevron-right text-slate-400 text-[10px]"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-xs text-slate-400 dark:text-slate-500 space-y-2">
                        <p>Belum ada riwayat mutasi.</p>
                        <button type="button" data-modal-target="quickProductMutationModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 transition-colors cursor-pointer">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>Catat Mutasi</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Quick Product Mutation Modal (Desktop Centered / Mobile Bottom Sheet) --}}
<div id="quickProductMutationModal" class="modal-container responsive-bottom-sheet fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="sheet-dialog relative w-full sm:max-w-lg rounded-t-3xl sm:rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-6 max-h-[90vh] overflow-y-auto animate-fade-in">
        <div class="sheet-grab-handle w-11 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto mb-3 sm:hidden"></div>
        <div class="flex items-start justify-between gap-3 pb-3.5 border-b border-slate-100 dark:border-slate-800 mb-4">
            <div>
                <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Catat Mutasi</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $product->name }} • Stok: <strong class="text-blue-600 dark:text-blue-400">{{ $product->stock ?? 0 }} unit</strong>
                </p>
            </div>
            <button type="button" data-modal-close class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>

        <form action="{{ route('mutations.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="redirect_to" value="product">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Jenis</label>
                    <select name="type" id="quickMutType" required
                            class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <option value="pindah_ruang">Pindah Ruang</option>
                        <option value="masuk">Barang Masuk (+Stok)</option>
                        <option value="keluar">Barang Keluar (-Stok)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Jumlah</label>
                    <input type="number" name="quantity" min="1" value="1" required
                           class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Tanggal</label>
                    <input type="date" name="mutation_date" value="{{ now()->toDateString() }}" required
                           class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div id="quickFromRoomWrap" class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Dari Ruangan</label>
                    <select name="from_room_id" id="quickFromRoomSelect"
                            class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <option value="">— Pilih Ruangan Asal —</option>
                        @foreach($rooms ?? [] as $room)
                            <option value="{{ $room->id }}" @selected($product->room_id == $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="quickToRoomWrap" class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Ke Ruangan</label>
                    <select name="to_room_id" id="quickToRoomSelect"
                            class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <option value="">— Pilih Ruangan Tujuan —</option>
                        @foreach($rooms ?? [] as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Catatan</label>
                <textarea name="note" rows="2" placeholder="Alasan mutasi..."
                          class="w-full px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <a href="{{ route('mutations.create', ['product_id' => $product->id]) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    Form Penuh
                </a>
                <div class="flex items-center gap-2">
                    <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs cursor-pointer">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const svg = document.getElementById('detail-barcode-svg');
    if (svg && typeof JsBarcode !== 'undefined') {
        try {
            JsBarcode(svg, "{{ $product->barcode }}", {
                format: "CODE128",
                width: 1.6,
                height: 44,
                displayValue: false,
                margin: 0
            });
        } catch (e) {}
    }

    const quickType = document.getElementById('quickMutType');
    const fromWrap = document.getElementById('quickFromRoomWrap');
    const toWrap = document.getElementById('quickToRoomWrap');
    function syncQuickMutRooms() {
        if (!quickType) return;
        const val = quickType.value;
        if (fromWrap) fromWrap.classList.toggle('hidden', val === 'masuk');
        if (toWrap) toWrap.classList.toggle('hidden', val === 'keluar');
    }
    quickType?.addEventListener('change', syncQuickMutRooms);
    syncQuickMutRooms();
});
</script>
@if($product->barcode)
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
@endif
@endsection