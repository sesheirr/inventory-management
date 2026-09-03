@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <a href="{{ route('mutations.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Tambah Mutasi Barang</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Ajukan pencatatan barang masuk, keluar, atau pemindahan lokasi ruangan</p>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('mutations.store') }}" method="POST" class="space-y-5" id="mutationForm">
            @csrf

            {{-- Barang Selector --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Pilih Barang <span class="text-rose-500">*</span>
                </label>
                <select name="product_id" required 
                        class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    <option value="" disabled selected>Pilih Barang yang Dimutasi</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" @selected(old('product_id') == $item->id)>
                            {{ $item->name }} 
                            @if(!empty($item->barcode))
                                [Barcode: {{ $item->barcode }}]
                            @elseif(!empty($item->kode_barang))
                                [Kode: {{ $item->kode_barang }}]
                            @endif
                            — Stok: {{ $item->stock }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Jenis Mutasi --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Jenis Mutasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="type" id="mutationType" required 
                            class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="masuk" @selected(old('type') === 'masuk')>Masuk (Penambahan Stok)</option>
                        <option value="keluar" @selected(old('type') === 'keluar')>Keluar (Pengurangan Stok / Dipinjam)</option>
                        <option value="pindah_ruang" @selected(old('type') === 'pindah_ruang')>Pindah Ruang (Distribusi Antar Lokasi)</option>
                    </select>
                    @error('type')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Jumlah Unit --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Jumlah Unit <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    @error('quantity')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Ruangan Tujuan --}}
                <div class="space-y-1.5" id="toRoomField">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Ruangan Tujuan
                    </label>
                    <select name="to_room_id" id="to_room_id" 
                            class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                        <option value="" selected disabled>Pilih Ruangan Tujuan</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('to_room_id') == $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                    @error('to_room_id')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>

                {{-- Tanggal Mutasi --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Tanggal Mutasi <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="mutation_date" value="{{ old('mutation_date', date('Y-m-d')) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                    @error('mutation_date')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Catatan --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Catatan / Keterangan Mutasi
                </label>
                <textarea name="note" rows="3" 
                          class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                          placeholder="Alasan mutasi, nomor surat permohonan, atau kondisi barang saat dipindahkan...">{{ old('note') }}</textarea>
                @error('note')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            {{-- Form Actions --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('mutations.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-[0.98] transition-all cursor-pointer">
                    Simpan Mutasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mutationType = document.getElementById('mutationType');
    const toRoomField = document.getElementById('toRoomField');
    function updateToRoomVisibility() {
        if (!toRoomField || !mutationType) return;
        if (mutationType.value === 'keluar') {
            toRoomField.style.display = 'none';
        } else {
            toRoomField.style.display = 'block';
        }
    }
    mutationType?.addEventListener('change', updateToRoomVisibility);
    updateToRoomVisibility();
});
</script>
@endsection