@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header & Search Bar --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Kategori Barang</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelompokkan dan kelola klasifikasi aset inventaris</p>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('categories.index') }}" method="GET" class="relative w-full sm:w-72">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="search" name="search" value="{{ $query ?? '' }}" placeholder="Cari kategori..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors" autocomplete="off">
                </form>

                <button type="button" data-modal-target="addCategoryModal" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Kategori</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Categories Grid --}}
    @if($categories->isEmpty())
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-12 text-center text-slate-400 dark:text-slate-500 shadow-sm">
            <i class="bi bi-tags text-4xl mb-3 block text-slate-300 dark:text-slate-600"></i>
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-300">Kategori Tidak Ditemukan</h3>
            <p class="text-xs mt-1">Coba gunakan kata kunci lain atau tambahkan kategori baru.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($categories as $category)
                <div class="group rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm hover:shadow-md hover:border-blue-500/40 transition-all duration-200 p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg font-bold shrink-0">
                                    <i class="bi bi-tag"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">{{ $category->name }}</h3>
                                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">{{ $category->products_count }} total barang</span>
                                </div>
                            </div>

                            @if(auth()->user()->isAdmin())
                                <div class="relative">
                                    <button type="button" data-dropdown-toggle="cat-dropdown-{{ $category->id }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        <i class="bi bi-three-dots-vertical text-sm"></i>
                                    </button>
                                    <div id="cat-dropdown-{{ $category->id }}" data-dropdown-menu class="hidden absolute right-0 mt-1 w-36 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-1 z-20 text-left">
                                        <a href="{{ route('categories.edit', $category) }}" class="flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                            <i class="bi bi-pencil text-slate-400"></i> Edit Kategori
                                        </a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
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

                        {{-- Product Preview Items --}}
                        @if($category->products->isEmpty())
                            <p class="text-xs text-slate-400 dark:text-slate-500 italic py-2">Belum ada barang di kategori ini.</p>
                        @else
                            <div class="space-y-1.5 my-3 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                @foreach($category->products->take(3) as $product)
                                    <div class="flex items-center justify-between text-xs py-1">
                                        <span class="text-slate-700 dark:text-slate-300 truncate max-w-[180px]">{{ $product->name }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            {{ $product->stock }} unit
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 mt-2">
                        <a href="{{ route('products.index', ['search' => $category->name, 'from' => 'categories']) }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50/80 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                            <span>Lihat Semua Barang</span>
                            <i class="bi bi-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Category Modal --}}
<div id="addCategoryModal" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 animate-fade-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Kategori Baru</h3>
            <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label for="category-name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nama Kategori <span class="text-rose-500">*</span></label>
                <input id="category-name" type="text" name="name" value="{{ old('name') }}" required 
                       class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                       placeholder="Contoh: Elektronik & Jaringan">
                @error('name')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Deskripsi (Opsional)</label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors placeholder-slate-400"
                          placeholder="Penjelasan ringkas kategori barang ini...">{{ old('description') }}</textarea>
                @error('description')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all cursor-pointer">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>
@endsection