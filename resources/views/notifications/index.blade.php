@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Pemberitahuan & Notifikasi</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Riwayat pemberitahuan aktivitas mutasi, stok menipis, dan verifikasi</p>
        </div>

        <div class="flex items-center gap-2">
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800/40 transition-colors cursor-pointer">
                        <i class="bi bi-check2-all"></i>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('notifications.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-colors {{ !request('filter') ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-[#0f1b38] text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800/80 hover:bg-slate-50' }}">
            Semua Notifikasi
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-colors {{ request('filter') === 'unread' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-[#0f1b38] text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800/80 hover:bg-slate-50' }}">
            Belum Dibaca ({{ auth()->user()->unreadNotifications->count() }})
        </a>
    </div>

    {{-- Notification List --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($notifications as $notification)
            <div class="p-4 sm:p-5 flex items-start justify-between gap-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors {{ is_null($notification->read_at) ? 'bg-blue-50/30 dark:bg-blue-950/10' : '' }}">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base shrink-0 {{ is_null($notification->read_at) ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                        <i class="bi bi-bell"></i>
                    </div>

                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                            {{ $notification->data['title'] ?? 'Pemberitahuan Sistem' }}
                        </h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            {{ $notification->data['message'] ?? ($notification->data['description'] ?? '-') }}
                        </p>
                        <span class="text-[10px] text-slate-400 block pt-0.5">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    @if(is_null($notification->read_at))
                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Tandai telah dibaca">
                                <i class="bi bi-check2"></i>
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors" title="Hapus Notifikasi">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-slate-400 dark:text-slate-500">
                <i class="bi bi-bell-slash text-4xl mb-3 block text-slate-300 dark:text-slate-600"></i>
                <h3 class="text-base font-bold text-slate-700 dark:text-slate-300">Tidak Ada Notifikasi</h3>
                <p class="text-xs mt-1">Anda sudah melihat seluruh pemberitahuan terbaru.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $notifications->links() }}
    </div>
</div>
@endsection
