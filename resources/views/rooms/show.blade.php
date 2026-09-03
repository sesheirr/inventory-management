@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('rooms.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Detail Ruangan</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Informasi ruangan dan penanggung jawab</p>
            </div>
        </div>

        <a href="{{ route('rooms.edit', $room) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all">
            <i class="bi bi-pencil"></i>
            <span>Edit Ruangan</span>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nama Ruangan</span>
            <div class="text-xl font-bold text-slate-900 dark:text-white">{{ $room->name }}</div>
        </div>

        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Penanggung Jawab</span>
            <div class="text-xl font-bold text-slate-900 dark:text-white">{{ $room->person_in_charge ?? 'Belum ditentukan' }}</div>
        </div>
    </div>
</div>
@endsection