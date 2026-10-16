@props([
    'name' => 'image',
    'label' => 'Foto & Galeri Barang',
    'hint' => '(Multi-Upload JPG, PNG, WEBP — Maks. 8MB/file)',
    'maxFiles' => 8,
    'initialGallery' => [],
    'initialImageUrl' => null,
    'allowRemoveExisting' => false,
])

@php
    $resolvedGallery = is_array($initialGallery) ? $initialGallery : [];
    if (empty($resolvedGallery) && !empty($initialImageUrl)) {
        $resolvedGallery = [
            [
                'id' => 'primary-existing',
                'name' => 'Foto Barang Saat Ini',
                'url' => $initialImageUrl,
                'thumb_url' => $initialImageUrl,
            ],
        ];
    }
@endphp

<div class="space-y-3"
     data-reusable-media-uploader="true"
     data-v2-multi-uploader="true"
     data-upload-endpoint="{{ route('uploads.tmp.store') }}"
     data-delete-endpoint="{{ route('uploads.tmp.destroy') }}"
     data-max-files="{{ $maxFiles }}"
     data-initial-gallery='@json($resolvedGallery)'>

    <div class="flex flex-wrap items-center justify-between gap-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            {{ $label }}
            <span class="text-[11px] font-normal normal-case text-slate-400 dark:text-slate-500">{{ $hint }}</span>
        </label>
        <span data-uploader-counter
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
            <i class="bi bi-images text-[10px]"></i>
            <span data-uploader-count-text>{{ count($resolvedGallery) }} / {{ $maxFiles }} Foto</span>
        </span>
    </div>

    {{-- Interactive Dropzone + Native File Input --}}
    <div data-uploader-dropzone
         class="relative rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/80 dark:bg-[#151311]/90 hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50/30 dark:hover:bg-blue-950/15 transition-all p-4 sm:p-5 text-center group">
        <input type="file"
               name="{{ $name }}"
               data-uploader-file-input
               accept="image/jpeg,image/png,image/webp"
               multiple
               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
               aria-label="Pilih atau seret file gambar">

        <div class="pointer-events-none flex flex-col sm:flex-row items-center justify-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-blue-600/10 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 border border-blue-500/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i class="bi bi-cloud-arrow-up-fill text-xl"></i>
            </div>
            <div class="text-center sm:text-left">
                <p class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100">
                    Klik <span class="text-blue-600 dark:text-blue-400 underline decoration-dotted">Pilih File</span> atau seret gambar ke area ini
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    Thumbnail pratinjau muncul otomatis • Mendukung beberapa foto sekaligus (JPG, PNG, WEBP)
                </p>
            </div>
            <span class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-slate-200 dark:border-slate-700 shadow-2xs shrink-0">
                <i class="bi bi-folder2-open mr-1"></i> Pilih Gambar
            </span>
        </div>
    </div>

    {{-- Live Selected Thumbnail Grid (Populated Immediately When User Chooses Files) --}}
    <div data-uploader-preview-wrapper class="{{ count($resolvedGallery) > 0 ? '' : 'hidden' }} space-y-2.5">
        <div class="flex items-center justify-between gap-2 px-1">
            <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                <i class="bi bi-eye-fill text-blue-600 dark:text-blue-400"></i>
                <span>Pratinjau Thumbnail Gambar</span>
            </p>
            <button type="button"
                    data-uploader-clear-btn
                    class="hidden text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:underline inline-flex items-center gap-1">
                <i class="bi bi-trash3 text-[10px]"></i>
                <span>Reset Pilihan Baru</span>
            </button>
        </div>

        <div data-uploader-preview-grid
             class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($resolvedGallery as $idx => $mediaItem)
                @php
                    $thumbUrl = $mediaItem['thumb_url'] ?? $mediaItem['url'] ?? '';
                    $mediaName = $mediaItem['name'] ?? ('Foto #' . ($idx + 1));
                @endphp
                <div data-existing-media-card
                     class="relative group rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xs">
                    <div class="aspect-square w-full bg-slate-100 dark:bg-slate-800 overflow-hidden relative">
                        <img src="{{ $thumbUrl }}"
                             alt="{{ $mediaName }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}';">
                        @if($idx === 0)
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-blue-600/90 text-white shadow-xs backdrop-blur-xs">
                                Saat Ini
                            </span>
                        @endif
                    </div>
                    <div class="p-2 flex items-center justify-between gap-1.5 bg-white dark:bg-slate-900">
                        <p class="text-[11px] font-bold text-slate-700 dark:text-slate-200 truncate">{{ $mediaName }}</p>
                        <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shrink-0">Tersimpan</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Hidden Staging Inputs Container for Multi-Upload Tokens --}}
    <div data-uploader-hidden-tokens class="hidden"></div>

    @if($allowRemoveExisting && !empty($initialImageUrl))
        <div class="flex items-center justify-between gap-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ $initialImageUrl }}"
                     alt="Gambar saat ini"
                     class="w-11 h-11 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0"
                     onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}';">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">Foto Utama Tersimpan</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Centang opsi di kanan jika ingin menghapus foto tanpa mengganti</p>
                </div>
            </div>
            <label class="flex items-center gap-2 text-xs font-bold text-rose-600 dark:text-rose-400 cursor-pointer shrink-0">
                <input type="checkbox" name="remove_image" value="1" class="w-4 h-4 rounded text-rose-600 border-slate-300 focus:ring-rose-500">
                <span>Hapus foto saat ini</span>
            </label>
        </div>
    @endif

    @error($name)
        <p class="text-xs font-semibold text-rose-500">{{ $message }}</p>
    @enderror
</div>
