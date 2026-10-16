@extends('layouts.app')

@section('page-title', 'Catat Mutasi')
@section('page-subtitle', 'Transaksi barang masuk, keluar & pindah ruangan')
@section('title', 'Catat Mutasi Barang — Inventaris Diskominfo Garut')

@section('content')
@php
    $productOptions = $items ?? $products ?? collect();
    $currentSelectedId = old('product_id', $selectedProductId ?? null);
    $preselectedProduct = $currentSelectedId ? $productOptions->firstWhere('id', (int) $currentSelectedId) : null;
@endphp

<div class="max-w-2xl mx-auto space-y-5" data-framer-page>
    <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
            <a href="{{ $preselectedProduct ? route('products.show', $preselectedProduct) : route('mutations.index') }}"
               class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all shrink-0"
               title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-base sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white truncate">Catat Mutasi</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 truncate">Masuk, keluar, atau pindah ruangan</p>
            </div>
        </div>

        <a href="{{ route('mutations.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 whitespace-nowrap shrink-0 transition-all">
            <i class="bi bi-clock-history text-blue-600 dark:text-blue-400"></i>
            <span>Riwayat</span>
        </a>
    </div>

    <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-5 sm:p-7">
        <form action="{{ route('mutations.store') }}" method="POST" class="space-y-5" id="mutationForm">
            @csrf
            <input type="hidden" name="from_room_id" id="fromRoomHiddenInput" value="{{ old('from_room_id', $preselectedProduct?->room_id) }}">

            {{-- Barang Selector --}}
            <div class="space-y-2">
                <label for="mutationProductSelect" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Pilih Barang <span class="text-rose-500">*</span>
                </label>
                <select name="product_id" id="mutationProductSelect" required
                        class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    <option value="" disabled @selected(!$currentSelectedId)>Pilih Barang yang Dimutasi</option>
                    @foreach($productOptions as $item)
                        <option value="{{ $item->id }}"
                                data-room-id="{{ $item->room_id ?? '' }}"
                                data-room-name="{{ $item->room?->name ?? 'Belum ditentukan' }}"
                                data-stock="{{ $item->stock ?? 0 }}"
                                data-code="{{ $item->barcode ?: ($item->kode_barang ?: '-') }}"
                                @selected((int) $currentSelectedId === (int) $item->id)>
                            {{ $item->name }}
                            @if(!empty($item->barcode))
                                [{{ $item->barcode }}]
                            @elseif(!empty($item->kode_barang))
                                [{{ $item->kode_barang }}]
                            @endif
                            — Ruangan: {{ $item->room?->name ?? 'Belum ditentukan' }} (Stok: {{ $item->stock ?? 0 }} unit)
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror

                {{-- Selected Product Live Context Banner --}}
                <div id="selectedProductInfoCard"
                     class="{{ $preselectedProduct ? 'flex' : 'hidden' }} items-center justify-between gap-3 p-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/70 dark:border-blue-800/50 text-xs transition-all">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="min-w-0">
                            <div id="selectedProductNameLabel" class="font-bold text-slate-900 dark:text-white truncate">
                                {{ $preselectedProduct?->name ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-600 dark:text-slate-300 flex flex-wrap items-center gap-x-2.5 gap-y-0.5 mt-0.5">
                                <span>Kode: <strong id="selectedProductCodeLabel" class="font-mono">{{ $preselectedProduct?->barcode ?: ($preselectedProduct?->kode_barang ?: '-') }}</strong></span>
                                <span>Ruang: <strong id="selectedProductRoomLabel">{{ $preselectedProduct?->room?->name ?? 'Belum ditentukan' }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Stok</span>
                        <span id="selectedProductStockLabel" class="text-sm font-extrabold text-blue-600 dark:text-blue-400 tabular-nums">
                            {{ $preselectedProduct?->stock ?? 0 }} Unit
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Jenis Mutasi --}}
                <div class="space-y-1.5">
                    <label for="mutationType" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Jenis Mutasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="type" id="mutationType" required
                            class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="pindah_ruang" @selected(old('type', $preselectedProduct ? 'pindah_ruang' : 'masuk') === 'pindah_ruang')>Pindah Ruang</option>
                        <option value="masuk" @selected(old('type', $preselectedProduct ? 'pindah_ruang' : 'masuk') === 'masuk')>Masuk (+Stok)</option>
                        <option value="keluar" @selected(old('type') === 'keluar')>Keluar (-Stok)</option>
                    </select>
                    @error('type')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Jumlah Unit --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Jumlah Unit <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" min="1" name="quantity" id="mutationQuantityInput" value="{{ old('quantity', 1) }}" required
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    @error('quantity')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Ruangan Tujuan --}}
                <div class="space-y-1.5" id="toRoomField">
                    <label for="to_room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Ruangan Tujuan <span id="toRoomRequiredMark" class="text-rose-500">*</span>
                    </label>
                    <select name="to_room_id" id="to_room_id"
                            class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="" selected disabled>Pilih Ruangan Tujuan</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('to_room_id') == $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                    @error('to_room_id')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                    @error('from_room_id')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Tanggal Mutasi --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Tanggal <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="mutation_date" value="{{ old('mutation_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    @error('mutation_date')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Live Route & Stock Impact Simulator (Logistics Preview) --}}
            <div id="mutationRoutePreviewCard" class="p-4 rounded-2xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-3">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                        <i class="bi bi-signpost-split text-blue-600 dark:text-blue-400"></i>
                        <span>Simulasi Rute & Dampak Stok</span>
                    </span>
                    <span id="simTypeBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 text-blue-600 dark:text-blue-300 border border-blue-500/25">
                        Pindah Ruangan
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-7 gap-2.5 items-center text-xs">
                    <div class="sm:col-span-3 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Titik Asal</span>
                        <span id="simOriginText" class="font-bold text-slate-800 dark:text-slate-100 truncate block mt-0.5">
                            {{ $preselectedProduct?->room?->name ?? 'Pilih barang terlebih dahulu' }}
                        </span>
                    </div>
                    <div class="sm:col-span-1 flex items-center justify-center text-blue-600 dark:text-blue-400 font-extrabold">
                        <i class="bi bi-arrow-right-circle-fill text-base"></i>
                    </div>
                    <div class="sm:col-span-3 p-2.5 rounded-xl bg-blue-50/50 dark:bg-blue-950/30 border border-blue-200/70 dark:border-blue-800/60">
                        <span class="text-[10px] font-bold uppercase text-blue-600 dark:text-blue-400 block">Titik Tujuan</span>
                        <span id="simDestText" class="font-bold text-slate-800 dark:text-slate-100 truncate block mt-0.5">
                            Pilih ruangan tujuan
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-800 text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Proyeksi Stok Barang:</span>
                    <div class="flex items-center gap-2 font-extrabold tabular-nums">
                        <span id="simStockBefore" class="text-slate-600 dark:text-slate-300">{{ $preselectedProduct?->stock ?? 0 }} unit</span>
                        <i class="bi bi-arrow-right text-[10px] text-slate-400"></i>
                        <span id="simStockAfter" class="px-2 py-0.5 rounded-lg bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25">
                            {{ $preselectedProduct?->stock ?? 0 }} unit
                        </span>
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Catatan
                </label>
                <textarea name="note" rows="3"
                          class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                          placeholder="Alasan mutasi atau keterangan tambahan...">{{ old('note') }}</textarea>
                @error('note')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            {{-- Form Actions --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <a href="{{ $preselectedProduct ? route('products.show', $preselectedProduct) : route('mutations.index') }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all whitespace-nowrap">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs active:scale-95 transition-all cursor-pointer whitespace-nowrap">
                    <i class="bi bi-check2-circle"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productSelect = document.getElementById('mutationProductSelect');
    const mutationType = document.getElementById('mutationType');
    const quantityInput = document.getElementById('mutationQuantityInput');
    const toRoomField = document.getElementById('toRoomField');
    const toRoomSelect = document.getElementById('to_room_id');
    const toRoomRequiredMark = document.getElementById('toRoomRequiredMark');
    const fromRoomHidden = document.getElementById('fromRoomHiddenInput');

    const infoCard = document.getElementById('selectedProductInfoCard');
    const nameLabel = document.getElementById('selectedProductNameLabel');
    const codeLabel = document.getElementById('selectedProductCodeLabel');
    const roomLabel = document.getElementById('selectedProductRoomLabel');
    const stockLabel = document.getElementById('selectedProductStockLabel');

    const simTypeBadge = document.getElementById('simTypeBadge');
    const simOriginText = document.getElementById('simOriginText');
    const simDestText = document.getElementById('simDestText');
    const simStockBefore = document.getElementById('simStockBefore');
    const simStockAfter = document.getElementById('simStockAfter');

    function updateSimulator() {
        const opt = productSelect?.options[productSelect.selectedIndex];
        const hasProduct = opt && opt.value;
        const roomName = hasProduct ? (opt.getAttribute('data-room-name') || 'Gudang Inventaris') : 'Pilih barang terlebih dahulu';
        const baseStock = hasProduct ? parseInt(opt.getAttribute('data-stock') || '0', 10) : 0;
        const qty = Math.max(1, parseInt(quantityInput?.value || '1', 10) || 1);
        const typeVal = mutationType?.value || 'pindah_ruang';
        const destOpt = toRoomSelect?.options[toRoomSelect.selectedIndex];
        const destRoomName = (destOpt && destOpt.value) ? destOpt.textContent.trim() : '';

        let afterStock = baseStock;
        if (typeVal === 'masuk') {
            afterStock = baseStock + qty;
            if (simTypeBadge) simTypeBadge.textContent = 'Barang Masuk (+' + qty + ')';
            if (simOriginText) simOriginText.textContent = 'Pengadaan / Gudang Utama';
            if (simDestText) simDestText.textContent = destRoomName || roomName;
        } else if (typeVal === 'keluar') {
            afterStock = Math.max(0, baseStock - qty);
            if (simTypeBadge) simTypeBadge.textContent = 'Barang Keluar (-' + qty + ')';
            if (simOriginText) simOriginText.textContent = roomName;
            if (simDestText) simDestText.textContent = 'Distribusi / Pemakaian Keluar';
        } else {
            afterStock = baseStock;
            if (simTypeBadge) simTypeBadge.textContent = 'Pindah Ruangan (' + qty + ' unit)';
            if (simOriginText) simOriginText.textContent = roomName;
            if (simDestText) simDestText.textContent = destRoomName || 'Pilih ruangan tujuan';
        }

        if (simStockBefore) simStockBefore.textContent = baseStock + ' unit';
        if (simStockAfter) {
            simStockAfter.textContent = afterStock + ' unit';
            const insufficient = (typeVal === 'keluar' || typeVal === 'pindah_ruang') && hasProduct && qty > baseStock;
            simStockAfter.className = insufficient
                ? 'px-2 py-0.5 rounded-lg bg-rose-500/15 text-rose-600 dark:text-rose-300 border border-rose-500/25'
                : 'px-2 py-0.5 rounded-lg bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25';
        }
    }

    function updateProductContext() {
        if (!productSelect) return;
        const opt = productSelect.options[productSelect.selectedIndex];
        if (!opt || !opt.value) {
            infoCard?.classList.add('hidden');
            infoCard?.classList.remove('flex');
            updateSimulator();
            return;
        }
        const roomId = opt.getAttribute('data-room-id') || '';
        const roomName = opt.getAttribute('data-room-name') || 'Belum ditentukan';
        const stock = opt.getAttribute('data-stock') || '0';
        const code = opt.getAttribute('data-code') || '-';
        const cleanName = (opt.textContent || '').split('[')[0].split('—')[0].trim();

        if (fromRoomHidden) fromRoomHidden.value = roomId;
        if (nameLabel) nameLabel.textContent = cleanName;
        if (codeLabel) codeLabel.textContent = code;
        if (roomLabel) roomLabel.textContent = roomName;
        if (stockLabel) stockLabel.textContent = stock + ' Unit';

        infoCard?.classList.remove('hidden');
        infoCard?.classList.add('flex');
        updateSimulator();
    }

    function updateToRoomVisibility() {
        if (!toRoomField || !mutationType) return;
        const typeVal = mutationType.value;
        if (typeVal === 'keluar') {
            toRoomField.style.display = 'none';
            if (toRoomSelect) toRoomSelect.required = false;
        } else {
            toRoomField.style.display = 'block';
            if (toRoomSelect) toRoomSelect.required = (typeVal === 'pindah_ruang');
            if (toRoomRequiredMark) {
                toRoomRequiredMark.style.display = (typeVal === 'pindah_ruang') ? 'inline' : 'none';
            }
        }
        updateSimulator();
    }

    productSelect?.addEventListener('change', updateProductContext);
    mutationType?.addEventListener('change', updateToRoomVisibility);
    quantityInput?.addEventListener('input', updateSimulator);
    toRoomSelect?.addEventListener('change', updateSimulator);
    updateProductContext();
    updateToRoomVisibility();
});
</script>
@endsection