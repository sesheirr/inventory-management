@extends('layouts.app')

@section('page-title', 'Edit Kategori')
@section('page-subtitle', 'Perbarui informasi kategori')

@section('content')
<div class="max-w-xl mx-auto space-y-5" data-framer-page>
    <div class="flex items-center gap-3 pb-3.5 border-b border-slate-200/80 dark:border-slate-800/80">
        <a href="{{ route('categories.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Edit Kategori</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $category->name }}</p>
        </div>
    </div>

    <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs p-5 sm:p-7">
        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Nama Kategori <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required 
                       class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">
                @error('name')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Deskripsi
                </label>
                <textarea name="description" rows="4" 
                          class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors">{{ old('description', $category->description) }}</textarea>
                @error('description')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <a href="{{ route('categories.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-all cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection