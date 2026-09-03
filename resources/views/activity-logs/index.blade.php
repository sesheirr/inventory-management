@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Log Aktivitas Sistem</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Audit trail seluruh operasi dan interaksi data oleh pengguna di sistem</p>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-3.5">Pengguna</th>
                        <th class="px-6 py-3.5">Aksi</th>
                        <th class="px-6 py-3.5">Deskripsi Aktivitas</th>
                        <th class="px-6 py-3.5 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @forelse($activityLogs as $log)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if(!empty($log->user?->avatar))
                                        @php
                                            $avatarUrl = \Illuminate\Support\Str::startsWith($log->user->avatar, ['http://', 'https://'])
                                                ? $log->user->avatar
                                                : asset('storage/' . $log->user->avatar);
                                        @endphp
                                        <img src="{{ $avatarUrl }}" alt="{{ $log->user->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                                            {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $log->user?->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($log->action === 'created')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">Dibuat</span>
                                @elseif($log->action === 'updated')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400">Diperbarui</span>
                                @elseif($log->action === 'deleted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">Dihapus</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ ucfirst($log->action) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-700 dark:text-slate-300 font-medium max-w-md">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-500 dark:text-slate-400 text-right whitespace-nowrap">
                                {{ $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="bi bi-clock-history text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Tidak ada log aktivitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards Feed --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($activityLogs as $log)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if(!empty($log->user?->avatar))
                                @php
                                    $avatarUrl = \Illuminate\Support\Str::startsWith($log->user->avatar, ['http://', 'https://'])
                                        ? $log->user->avatar
                                        : asset('storage/' . $log->user->avatar);
                                @endphp
                                <img src="{{ $avatarUrl }}" alt="{{ $log->user->name }}" class="w-6 h-6 rounded-full object-cover">
                            @else
                                <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 text-xs font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $log->user?->name ?? 'Sistem' }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300">{{ $log->description }}</p>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400">Tidak ada log aktivitas.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection