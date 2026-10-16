@extends('layouts.app')

@section('title', 'Pusat Notifikasi & Pengingat — Inventaris Diskominfo Garut')

@section('content')
<div class="max-w-4xl mx-auto space-y-5" data-framer-page>
    {{-- Page Header --}}
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-slate-900 dark:text-white">Pusat Notifikasi</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Pengingat stok, persetujuan mutasi & pembaruan sistem
            </p>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
            <form action="{{ route('notifications.checkReminders') }}" method="POST" class="inline-flex m-0 shrink-0">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 border border-amber-200 dark:border-amber-800/50 transition-all cursor-pointer whitespace-nowrap shrink-0">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Sinkronkan</span>
                </button>
            </form>

            @if(($counts['unread'] ?? auth()->user()->unreadNotifications->count()) > 0)
                <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="inline-flex m-0 shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800/50 transition-all cursor-pointer whitespace-nowrap shrink-0">
                        <i class="bi bi-check2-all"></i>
                        <span>Baca Semua</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Category Filter Pills --}}
    @php
        $activeFilter = $filter ?? request('filter', 'all');
        $tabs = [
            'all' => ['label' => 'Semua (' . ($counts['all'] ?? 0) . ')', 'icon' => 'bi-collection'],
            'unread' => ['label' => 'Belum Dibaca (' . ($counts['unread'] ?? 0) . ')', 'icon' => 'bi-envelope-exclamation'],
            'approval' => ['label' => 'Persetujuan', 'icon' => 'bi-shield-exclamation'],
            'low_stock' => ['label' => 'Stok Menipis', 'icon' => 'bi-exclamation-triangle'],
            'out_of_stock' => ['label' => 'Stok Habis', 'icon' => 'bi-exclamation-octagon'],
        ];
    @endphp
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar" data-framer-stagger>
        @foreach($tabs as $key => $tab)
            <a href="{{ route('notifications.index', ['filter' => $key]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap shrink-0 transition-all {{ $activeFilter === $key ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/25' : 'bg-white dark:bg-[#0f1b38] text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800 hover:border-blue-400' }}">
                <i class="bi {{ $tab['icon'] }}"></i>
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Mobile Gesture Hint --}}
    @if($notifications->isNotEmpty())
        <div class="sm:hidden flex items-center justify-between gap-2 px-3.5 py-2 rounded-xl bg-blue-500/10 dark:bg-blue-400/10 border border-blue-500/20 text-[11px] font-semibold text-blue-700 dark:text-blue-300">
            <span class="flex items-center gap-1.5">
                <i class="bi bi-hand-index-thumb"></i>
                <span>Geser ke kiri untuk hapus, atau ke kanan untuk tandai dibaca</span>
            </span>
        </div>
    @endif

    {{-- Notifications Feed --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden" data-framer-card>
        <div class="divide-y divide-slate-100 dark:divide-slate-800" data-framer-stagger id="notificationsFeedList">
            @forelse($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $data = (array) $notification->data;
                    $category = $data['category'] ?? 'info';
                    $title = $data['title'] ?? 'Pemberitahuan Sistem';
                    $message = $data['message'] ?? 'Anda memiliki pembaruan aktivitas baru.';
                    $icon = $data['icon'] ?? 'bi-bell-fill';

                    $badgeColors = [
                        'approval' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
                        'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
                        'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
                        'out_of_stock' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
                        'low_stock' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
                        'info' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
                    ];
                    $badgeStyle = $badgeColors[$category] ?? $badgeColors['info'];
                @endphp
                <div class="notif-swipe-row relative overflow-hidden select-none"
                     data-swipe-notification="{{ $notification->id }}"
                     data-is-unread="{{ $isUnread ? '1' : '0' }}"
                     data-mark-read-url="{{ route('notifications.markAsRead', $notification->id, false) }}"
                     data-delete-url="{{ route('notifications.destroy', $notification->id, false) }}">
                    {{-- Mobile Swipe Background Actions --}}
                    <div class="notif-swipe-bg-left absolute inset-y-0 left-0 w-1/2 bg-emerald-500/90 text-white flex items-center justify-start pl-5 gap-2 text-xs font-bold opacity-0 pointer-events-none transition-opacity">
                        <i class="bi bi-check2-all text-base"></i>
                        <span>Tandai Dibaca</span>
                    </div>
                    <div class="notif-swipe-bg-right absolute inset-y-0 right-0 w-1/2 bg-rose-600/90 text-white flex items-center justify-end pr-5 gap-2 text-xs font-bold opacity-0 pointer-events-none transition-opacity">
                        <span>Hapus</span>
                        <i class="bi bi-trash3-fill text-sm"></i>
                    </div>

                    <div class="notif-swipe-card relative z-10 p-4 flex items-center justify-between gap-3 bg-white dark:bg-[#0f1b38] transition-colors {{ $isUnread ? 'bg-blue-50/35 dark:bg-blue-950/20' : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/40' }}">
                        {{-- Clickable Notification Object (Routes Directly to Target Content) --}}
                        <a href="{{ route('notifications.go', $notification->id, false) }}" class="flex items-start gap-3.5 min-w-0 flex-1 group">
                            <div class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 {{ $badgeStyle }} group-hover:scale-105 group-active:scale-95 transition-transform">
                                <i class="bi {{ $icon }} text-sm"></i>
                            </div>
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $title }}
                                    </span>
                                    @if($isUnread)
                                        <span data-notif-unread-pill class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 dark:bg-blue-400/15 text-blue-600 dark:text-blue-300 border border-blue-500/25 dark:border-blue-400/25 backdrop-blur-xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 dark:bg-blue-400"></span>
                                            Baru
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-2">
                                    {{ $message }}
                                </p>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500 pt-0.5">
                                    <span><i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}</span>
                                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                                    <span class="text-blue-600/80 dark:text-blue-400/80 font-semibold group-hover:translate-x-0.5 transition-transform inline-flex items-center gap-1">
                                        Lihat detail <i class="bi bi-arrow-right text-[10px]"></i>
                                    </span>
                                </div>
                            </div>
                        </a>

                        {{-- Subtle Secondary Icon Actions (Mark Read / Delete) --}}
                        <div class="flex items-center gap-1.5 shrink-0 flex-nowrap">
                            @if($isUnread)
                                <form action="{{ route('notifications.markAsRead', $notification->id, false) }}" method="POST" class="inline-flex m-0 shrink-0" data-notif-read-form>
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-600 dark:hover:text-blue-400 transition-all active:scale-90 cursor-pointer shrink-0" title="Tandai Dibaca" aria-label="Tandai Dibaca">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('notifications.destroy', $notification->id, false) }}" method="POST" class="inline-flex m-0 shrink-0" data-notif-delete-form>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all active:scale-90 cursor-pointer shrink-0" title="Hapus Notifikasi" aria-label="Hapus Notifikasi">
                                    <i class="bi bi-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-slate-400 dark:text-slate-500">
                    <i class="bi bi-bell-slash text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                    <p class="font-semibold text-slate-600 dark:text-slate-300 text-sm">Belum ada notifikasi</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
