<header class="sticky top-0 z-40 h-14 sm:h-16 px-3.5 sm:px-6 lg:px-8 flex items-center justify-between gap-3 relative">
    {{-- Frosted Glass Background Layer (separated from <header> so backdrop-filter does not trap position:fixed descendants on mobile) --}}
    <div class="absolute inset-0 bg-white/90 dark:bg-[#0f1b38]/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 pointer-events-none -z-10"></div>

    {{-- Left Side: Mobile Native App Header Brand & Desktop Page Title --}}
    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
        {{-- Mobile Native App Emblem (Replaces Left Drawer Hamburger) --}}
        <a href="{{ route('dashboard') }}" class="lg:hidden flex items-center gap-2.5 min-w-0 group">
            <div class="w-8 h-8 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 p-1 shadow-2xs flex items-center justify-center shrink-0 group-active:scale-95 transition-transform">
                <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Diskominfo Garut" class="w-full h-full object-contain">
            </div>
            <div class="min-w-0">
                <h2 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight truncate leading-tight">
                    @yield('page-title', 'Inventaris')
                </h2>
                <p class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 truncate leading-none mt-0.5">
                    Diskominfo Garut
                </p>
            </div>
        </a>

        {{-- Desktop Title & Subtitle --}}
        <div class="hidden lg:block min-w-0">
            <h2 class="text-base sm:text-lg font-bold text-slate-800 dark:text-white truncate leading-tight">
                @yield('page-title', 'Dashboard Inventaris')
            </h2>
            @hasSection('page-subtitle')
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                    @yield('page-subtitle')
                </p>
            @endif
        </div>
    </div>

    {{-- Right Side: Quick Global Search (Desktop), Quick Share, Notifications, Dark Mode & Profile --}}
    <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
        {{-- Quick Global Search Input (Desktop/Tablet) --}}
        <form action="{{ route('products.index') }}" method="GET" class="hidden md:flex items-center relative">
            <i class="bi bi-search absolute left-3.5 text-slate-400 text-xs pointer-events-none"></i>
            <input type="search"
                   name="search"
                   value="{{ request()->routeIs('products.index') ? request('search') : '' }}"
                   placeholder="Cari barang / kode..."
                   class="w-48 lg:w-64 pl-9 pr-3.5 py-1.5 text-xs bg-slate-100/90 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 rounded-xl text-slate-700 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all">
        </form>

        {{-- Quick Deep-Link Share Trigger --}}
        <button type="button"
                id="navbarQuickShareBtn"
                data-share-trigger="true"
                data-share-code="{{ $shareShortCode ?? 'dash' }}"
                data-share-title="{{ trim($__env->yieldContent('page-title', 'Inventaris Diskominfo Garut')) }}"
                data-share-description="{{ trim($__env->yieldContent('page-subtitle', 'Sistem Informasi Inventarisasi Barang & Aset Diskominfo Kabupaten Garut.')) }}"
                class="w-9 h-9 sm:w-9.5 sm:h-9.5 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 transition-all active:scale-95 cursor-pointer"
                title="Bagikan">
            <i class="bi bi-share text-xs sm:text-sm"></i>
        </button>

        {{-- Live Notification Bell Dropdown / Mobile Sheet --}}
        @php
            $unreadNotificationsCount = 0;
            $recentNavbarNotifications = collect();
            if (auth()->check()) {
                try {
                    $unreadNotificationsCount = auth()->user()->unreadNotifications()->count();
                    $recentNavbarNotifications = auth()->user()->notifications()->latest()->take(6)->get();
                } catch (\Throwable $e) {
                    $unreadNotificationsCount = 0;
                    $recentNavbarNotifications = collect();
                }
            }
        @endphp

        <div class="relative" id="notificationDropdownRoot">
            <button type="button"
                    id="notificationBellBtn"
                    aria-expanded="false"
                    aria-controls="notificationDropdownPanel"
                    class="relative w-9 h-9 sm:w-9.5 sm:h-9.5 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 transition-all active:scale-95 cursor-pointer"
                    title="Notifikasi">
                <i class="bi bi-bell text-sm sm:text-base transition-transform duration-200"></i>
                <span id="navbarUnreadBadge"
                      class="{{ $unreadNotificationsCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 rounded-full bg-rose-600 text-white text-[9px] font-extrabold flex items-center justify-center shadow-xs tabular-nums">
                    {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                </span>
            </button>

            {{-- Mobile Backdrop for Notification Sheet --}}
            <div id="notificationSheetBackdrop"
                 class="hidden sm:hidden fixed inset-0 bg-slate-900/45 backdrop-blur-xs z-[65] transition-opacity duration-200 opacity-0"></div>

            {{-- Notification Panel: Mobile Floating Bottom Sheet / Desktop Animated Dropdown --}}
            <div id="notificationDropdownPanel"
                 data-animated-sheet="true"
                 class="hidden fixed inset-x-3 bottom-[4.75rem] sm:bottom-auto sm:inset-x-auto sm:absolute sm:right-0 sm:top-full sm:mt-2 sm:w-96 max-h-[75vh] sm:max-h-[32rem] flex flex-col rounded-3xl sm:rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/90 dark:border-slate-800 shadow-2xl z-[70] overflow-hidden">
                {{-- Mobile Grab Handle --}}
                <div class="sheet-grab-handle sm:hidden shrink-0"></div>

                {{-- Header --}}
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 shrink-0">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <i class="bi bi-bell-fill text-xs"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold text-slate-900 dark:text-white leading-tight">Notifikasi</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">Klik item untuk menuju detail</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <form action="{{ route('notifications.checkReminders') }}" method="POST" class="inline-flex m-0 shrink-0">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 transition-colors cursor-pointer whitespace-nowrap"
                                        title="Sinkronkan Stok">
                                    <i class="bi bi-arrow-repeat text-xs"></i>
                                    <span>Sinkronkan</span>
                                </button>
                            </form>
                            @if($unreadNotificationsCount > 0)
                                <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="inline-flex m-0 shrink-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/15 hover:bg-blue-100/80 dark:hover:bg-blue-500/25 transition-colors cursor-pointer whitespace-nowrap">
                                        <i class="bi bi-check2-all text-xs"></i>
                                        <span>Baca Semua</span>
                                    </button>
                                </form>
                            @endif
                            <button type="button"
                                    id="closeNotificationSheetBtn"
                                    class="sm:hidden w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                                <i class="bi bi-x-lg text-xs"></i>
                            </button>
                        </div>
                    </div>
                    @if($recentNavbarNotifications->isNotEmpty())
                        <p class="sm:hidden text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 flex items-center gap-1">
                            <i class="bi bi-hand-index-thumb text-blue-500"></i>
                            <span>Geser item ke kiri untuk hapus, kanan untuk tandai dibaca</span>
                        </p>
                    @endif
                </div>

                {{-- Notification List --}}
                <div id="navbarNotificationList" class="overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/80 flex-1 overscroll-contain">
                    @forelse($recentNavbarNotifications as $notif)
                        @php
                            $notifData = (array) $notif->data;
                            $notifCat = $notifData['category'] ?? 'info';
                            $notifTitle = $notifData['title'] ?? 'Pemberitahuan Sistem';
                            $notifMsg = $notifData['message'] ?? '';
                            $notifIcon = $notifData['icon'] ?? 'bi-bell-fill';
                            $isNotifUnread = is_null($notif->read_at);
                            $lvlColor = match($notifCat) {
                                'approved' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/15 dark:text-emerald-400',
                                'approval', 'low_stock' => 'text-amber-600 bg-amber-50 dark:bg-amber-500/15 dark:text-amber-400',
                                'rejected', 'out_of_stock' => 'text-rose-600 bg-rose-50 dark:bg-rose-500/15 dark:text-rose-400',
                                default => 'text-blue-600 bg-blue-50 dark:bg-blue-500/15 dark:text-blue-400',
                            };
                        @endphp
                        <div class="notif-swipe-row relative overflow-hidden select-none"
                             data-swipe-notification="{{ $notif->id }}"
                             data-is-unread="{{ $isNotifUnread ? '1' : '0' }}"
                             data-mark-read-url="{{ route('notifications.markAsRead', $notif->id, false) }}"
                             data-delete-url="{{ route('notifications.destroy', $notif->id, false) }}">
                            {{-- Swipe Background Actions --}}
                            <div class="notif-swipe-bg-left absolute inset-y-0 left-0 w-1/2 bg-emerald-500/90 text-white flex items-center justify-start pl-4 gap-1.5 text-[11px] font-bold opacity-0 pointer-events-none transition-opacity">
                                <i class="bi bi-check2-all text-sm"></i>
                                <span>Dibaca</span>
                            </div>
                            <div class="notif-swipe-bg-right absolute inset-y-0 right-0 w-1/2 bg-rose-600/90 text-white flex items-center justify-end pr-4 gap-1.5 text-[11px] font-bold opacity-0 pointer-events-none transition-opacity">
                                <span>Hapus</span>
                                <i class="bi bi-trash3-fill text-xs"></i>
                            </div>

                            <a href="{{ route('notifications.go', $notif->id, false) }}"
                               class="notif-swipe-card relative z-10 flex items-start gap-3 px-4 py-3 bg-white dark:bg-[#0f1b38] hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors group {{ $isNotifUnread ? 'bg-blue-50/40 dark:bg-blue-950/25' : '' }}">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 {{ $lvlColor }} group-hover:scale-105 transition-transform">
                                    <i class="bi {{ $notifIcon }} text-xs"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $notifTitle }}</p>
                                        @if($isNotifUnread)
                                            <span data-notif-unread-pill class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-blue-500/15 dark:bg-blue-400/15 text-blue-600 dark:text-blue-300 border border-blue-500/25 dark:border-blue-400/25 backdrop-blur-xs shrink-0">
                                                Baru
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5 leading-snug">{{ $notifMsg }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 mt-1 flex items-center gap-1.5">
                                        <i class="bi bi-clock text-[9px]"></i>
                                        <span>{{ $notif->created_at->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center">
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                <i class="bi bi-bell-slash text-base"></i>
                            </div>
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Belum ada notifikasi</p>
                        </div>
                    @endforelse
                </div>

                {{-- Footer --}}
                <div class="p-2.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 flex items-center justify-between gap-2 shrink-0">
                    <button type="button" id="enableWebPushBtn" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-broadcast text-blue-600 dark:text-blue-400"></i>
                        <span>Notif Perangkat</span>
                    </button>
                    <a href="{{ route('notifications.index') }}" class="px-3 py-1.5 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors flex items-center gap-1">
                        <span>Semua</span>
                        <i class="bi bi-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Dark Mode Toggle (Desktop + Mobile Top Bar) --}}
        <button type="button"
                id="darkModeToggle"
                class="w-9 h-9 sm:w-9.5 sm:h-9.5 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 transition-all active:scale-95 cursor-pointer"
                title="Ganti Tema">
            <i id="darkModeIcon" class="bi bi-moon-stars text-sm sm:text-base"></i>
        </button>

        {{-- User Profile Dropdown --}}
        <div class="relative" id="userDropdownContainer">
            <button type="button"
                    id="userMenuButton"
                    aria-expanded="false"
                    aria-controls="userMenuDropdown"
                    class="flex items-center gap-2 pl-1.5 pr-2 sm:pr-3 py-1 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent hover:border-slate-200/80 dark:hover:border-slate-700/80 transition-all active:scale-97 cursor-pointer">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-8 h-8 rounded-lg object-cover border border-slate-200/80 dark:border-slate-700 shadow-xs shrink-0">
                @else
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <div class="hidden sm:block text-left">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-tight max-w-[120px] truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 leading-tight">
                        {{ auth()->user()->roleLabel() }}
                    </p>
                </div>
                <i class="bi bi-chevron-down text-[10px] text-slate-400 hidden sm:inline-block transition-transform duration-200"></i>
            </button>

            {{-- Dropdown Menu --}}
            <div id="userMenuDropdown"
                 data-animated-dropdown="true"
                 class="hidden fixed right-3 left-3 top-[3.75rem] sm:left-auto sm:top-full sm:absolute sm:right-0 sm:mt-2 sm:w-60 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800 shadow-xl py-2 z-[70]">
                <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}"
                             alt="{{ auth()->user()->name }}"
                             class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-bold text-sm flex items-center justify-center shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-500/15 dark:bg-blue-400/15 text-blue-600 dark:text-blue-300 border border-blue-500/25 dark:border-blue-400/25">
                            {{ auth()->user()->roleLabel() }}
                        </span>
                    </div>
                </div>

                <div class="py-1">
                    <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <i class="bi bi-person-circle text-sm"></i>
                        <span>Profil</span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="flex items-center justify-between px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <span class="flex items-center gap-2.5">
                            <i class="bi bi-bell text-sm"></i>
                            <span>Notifikasi</span>
                        </span>
                        @if($unreadNotificationsCount > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-600 dark:text-rose-300 border border-rose-500/25">{{ $unreadNotificationsCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('settings') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        <i class="bi bi-sliders text-sm"></i>
                        <span>Pengaturan</span>
                    </a>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-1 mt-1">
                    <button type="button"
                            data-modal-target="logoutModal"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer">
                        <i class="bi bi-box-arrow-right text-sm"></i>
                        <span>Keluar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>