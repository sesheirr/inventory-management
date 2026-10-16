@extends('layouts.app')

@section('page-title', 'Kategori')
@section('page-subtitle', 'Klasifikasi & pengelompokan barang')

@section('content')
<div class="space-y-4 sm:space-y-6" data-framer-page>
    {{-- Header & Search Bar --}}
    <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-xs space-y-3.5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">Kategori</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">Klasifikasi aset inventaris</p>
            </div>

            <button type="button" data-modal-target="addCategoryModal" 
                    class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/20 transition-all shrink-0 cursor-pointer">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah</span>
            </button>
        </div>

        <form action="{{ route('categories.index') }}" method="GET" class="relative w-full">
            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            <input type="search" name="search" value="{{ $query ?? '' }}" placeholder="Cari nama atau deskripsi kategori..." 
                   class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors" autocomplete="off">
        </form>
    </div>

    {{-- Categories Grid --}}
    @if($categories->isEmpty())
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-10 text-center text-slate-400 dark:text-slate-500 shadow-xs">
            <i class="bi bi-tags text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Kategori Tidak Ditemukan</h3>
            <p class="text-xs mt-1">Gunakan kata kunci lain atau tambahkan kategori baru.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-5" data-framer-stagger>
            @foreach($categories as $category)
                <div class="group rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md hover:border-blue-500/40 transition-all duration-200 p-4 sm:p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-2.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base font-bold shrink-0">
                                    <i class="bi bi-tag"></i>
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('categories.show', $category) }}" class="block text-sm sm:text-base font-extrabold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 leading-tight transition-colors truncate">
                                        {{ $category->name }}
                                    </a>
                                    <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 mt-0.5">{{ $category->products_count }} barang</div>
                                </div>
                            </div>

                            @if(auth()->user()->isAdmin())
                                <div class="relative shrink-0">
                                    <button type="button" data-dropdown-toggle="cat-dropdown-{{ $category->id }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        <i class="bi bi-three-dots-vertical text-sm"></i>
                                    </button>
                                    <div id="cat-dropdown-{{ $category->id }}" data-dropdown-menu class="hidden absolute right-0 mt-1 w-32 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-1 z-20 text-left">
                                        <a href="{{ route('categories.edit', $category) }}" class="flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                            <i class="bi bi-pencil text-slate-400"></i> Edit
                                        </a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($category->description)
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-2">{{ $category->description }}</p>
                        @endif

                        {{-- Product Preview Items --}}
                        @if($category->products->isEmpty())
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 italic py-1.5">Belum ada barang.</p>
                        @else
                            <div class="space-y-1 my-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                @foreach($category->products->take(3) as $product)
                                    <a href="{{ route('products.show', $product) }}" class="flex items-center justify-between text-xs py-1 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        <span class="text-slate-700 dark:text-slate-300 truncate pr-2 hover:underline">{{ $product->name }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0 tabular-nums">
                                            {{ $product->stock }} unit
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 mt-2">
                        <a href="{{ route('products.index', ['category_id' => $category->id, 'from' => 'categories']) }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50/80 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                            <span>Lihat Barang</span>
                            <i class="bi bi-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Category Modal (Desktop Centered / Mobile Bottom Sheet) --}}
<div id="addCategoryModal" class="modal-container responsive-bottom-sheet fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="sheet-dialog relative w-full sm:max-w-md rounded-t-3xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-6 animate-fade-in">
        <div class="sheet-grab-handle w-11 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto mb-3 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Tambah Kategori</h3>
            <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i class="bi bi-x-lg text-xs"></i>
            </button>
        </div>

        <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label for="category-name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nama Kategori <span class="text-rose-500">*</span></label>
                <input id="category-name" type="text" name="name" value="{{ old('name') }}" required 
                       class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                       placeholder="Contoh: Elektronik & Jaringan">
                @error('name')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Deskripsi</label>
                <textarea name="description" rows="3" 
                          class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                          placeholder="Penjelasan ringkas kategori...">{{ old('description') }}</textarea>
                @error('description')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection