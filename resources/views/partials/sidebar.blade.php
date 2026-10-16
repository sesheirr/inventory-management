{{-- Desktop Sidebar Container (Strictly Desktop lg:flex — Collapsible to Compact Icon-Only Mode) --}}
<aside id="sidebarOffcanvas"
       data-collapsible-sidebar="true"
       class="{{ request()->routeIs('products.scan') ? 'hidden' : 'hidden lg:flex lg:flex-col lg:w-64 lg:shrink-0 lg:sticky lg:top-0 lg:h-screen' }} bg-white dark:bg-[#1c1917] border-r border-slate-200/80 dark:border-stone-800/80 z-30 shadow-xs transition-[width] duration-200 ease-out">

    {{-- Brand Header + Desktop Compact Collapse Toggle --}}
    <div class="h-16 px-3.5 flex items-center justify-between gap-2 border-b border-slate-200/70 dark:border-stone-800/80 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group min-w-0" title="Inventaris Diskominfo Garut">
            <div class="w-9 h-9 rounded-full bg-white dark:bg-stone-800 flex items-center justify-center shadow-sm border border-slate-200 dark:border-stone-700/80 group-hover:scale-105 transition-transform duration-200 shrink-0 overflow-hidden">
                <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-full h-full object-cover">
            </div>
            <div class="sidebar-brand-text min-w-0">
                <h1 class="text-sm font-bold tracking-tight text-slate-900 dark:text-stone-100 leading-tight truncate">Inventaris Diskominfo</h1>
                <p class="text-[11px] font-semibold text-blue-600 dark:text-amber-400/90 truncate">Diskominfo Garut</p>
            </div>
        </a>

        <button type="button"
                id="desktopSidebarCollapseBtn"
                data-sidebar-collapse-toggle="true"
                class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-500 hover:text-slate-800 dark:text-stone-400 dark:hover:text-stone-100 hover:bg-slate-100 dark:hover:bg-stone-800 transition-colors shrink-0 cursor-pointer"
                title="Ringkas / Perluas Sidebar (Icon Only)"
                aria-label="Ringkas atau perluas sidebar">
            <i id="desktopSidebarCollapseIcon" class="bi bi-layout-sidebar-inset text-sm transition-transform duration-200"></i>
        </button>
    </div>

    {{-- Main Navigation Menu --}}
    <div class="flex-1 px-3 py-4 overflow-y-auto space-y-6">
        <div>
            <p class="sidebar-section-title px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-stone-500 mb-2">Menu Utama</p>
            <nav class="space-y-1">
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   data-framer-nav
                   data-sidebar-item
                   title="Dashboard"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('dashboard')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                    <i class="bi bi-grid text-base shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                    <span class="sidebar-item-label truncate">Dashboard</span>
                </a>

                {{-- Barang --}}
                <a href="{{ route('products.index') }}"
                   data-framer-nav
                   data-sidebar-item
                   title="Barang Inventaris"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('products.index') || (request()->routeIs('products.*') && !request()->routeIs('products.scan'))
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                    <i class="bi bi-box-seam text-base shrink-0 {{ request()->routeIs('products.index') || (request()->routeIs('products.*') && !request()->routeIs('products.scan')) ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                    <span class="sidebar-item-label truncate">Barang</span>
                </a>

                {{-- Ruangan --}}
                <a href="{{ route('rooms.index') }}"
                   data-framer-nav
                   data-sidebar-item
                   title="Ruangan"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('rooms.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                    <i class="bi bi-building text-base shrink-0 {{ request()->routeIs('rooms.*') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                    <span class="sidebar-item-label truncate">Ruangan</span>
                </a>

                {{-- Kategori --}}
                <a href="{{ route('categories.index') }}"
                   data-framer-nav
                   data-sidebar-item
                   title="Kategori"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('categories.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                    <i class="bi bi-tags text-base shrink-0 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                    <span class="sidebar-item-label truncate">Kategori</span>
                </a>

                {{-- Scan Barcode (1D Linear Barcode Sticker Icon) --}}
                <a href="{{ route('products.scan') }}"
                   data-framer-nav
                   data-sidebar-item
                   title="Scan Barcode"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('products.scan')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                    <i class="bi bi-upc-scan text-base shrink-0 {{ request()->routeIs('products.scan') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                    <span class="sidebar-item-label truncate">Scan Barcode</span>
                </a>

                {{-- Mutasi Barang --}}
                @php
                    $sidebarPendingMutations = 0;
                    if (auth()->check() && auth()->user()->isAdmin()) {
                        try {
                            $sidebarPendingMutations = \App\Models\Mutation::where('status', 'pending')->count();
                        } catch (\Throwable $e) {
                            $sidebarPendingMutations = 0;
                        }
                    }
                @endphp
                <a href="{{ route('mutations.index') }}"
                   data-framer-nav
                   data-sidebar-item
                   title="Mutasi Barang"
                   class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('mutations.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                    <span class="flex items-center gap-3 min-w-0">
                        <i class="bi bi-arrow-left-right text-base shrink-0 {{ request()->routeIs('mutations.*') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                        <span class="sidebar-item-label truncate">Mutasi</span>
                    </span>
                    <span id="sidebarPendingMutationBadge" class="sidebar-item-badge {{ $sidebarPendingMutations > 0 ? '' : 'hidden' }} px-2 py-0.5 rounded-full text-[10px] font-bold tabular-nums {{ request()->routeIs('mutations.*') ? 'bg-white text-blue-600' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' }}">
                        {{ $sidebarPendingMutations }}
                    </span>
                </a>
            </nav>
        </div>

        @if(auth()->user()->isAdmin())
            <div>
                <p class="sidebar-section-title px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-stone-500 mb-2">Administrasi</p>
                <nav class="space-y-1">
                    {{-- Log Aktivitas --}}
                    <a href="{{ route('activity-logs.index') }}"
                       data-framer-nav
                       data-sidebar-item
                       title="Log Aktivitas"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                       {{ request()->routeIs('activity-logs.*')
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                          : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                        <i class="bi bi-clock-history text-base shrink-0 {{ request()->routeIs('activity-logs.*') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                        <span class="sidebar-item-label truncate">Log Aktivitas</span>
                    </a>

                    {{-- Observability & Laravel Telescope --}}
                    <a href="{{ route('observability.index') }}"
                       data-framer-nav
                       data-sidebar-item
                       title="Observability"
                       class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                       {{ request()->routeIs('observability.*')
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                          : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                        <span class="flex items-center gap-3 min-w-0">
                            <i class="bi bi-activity text-base shrink-0 {{ request()->routeIs('observability.*') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                            <span class="sidebar-item-label truncate">Observability</span>
                        </span>
                        <span class="sidebar-item-badge px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase {{ request()->routeIs('observability.*') ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300' }}">LIVE</span>
                    </a>

                    {{-- Admin Webhook Studio (Strictly Admin & Super Admin) --}}
                    <a href="{{ route('webhooks.index') }}"
                       data-framer-nav
                       data-sidebar-item
                       title="Webhook Studio"
                       class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                       {{ request()->routeIs('webhooks.*')
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                          : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                        <span class="flex items-center gap-3 min-w-0">
                            <i class="bi bi-broadcast-pin text-base shrink-0 {{ request()->routeIs('webhooks.*') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                            <span class="sidebar-item-label truncate">Webhook Studio</span>
                        </span>
                        <span class="sidebar-item-badge px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase {{ request()->routeIs('webhooks.*') ? 'bg-white/20 text-white' : 'bg-blue-100 dark:bg-stone-800 text-blue-700 dark:text-amber-300' }}">HMAC</span>
                    </a>

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('users.index') }}"
                           data-framer-nav
                           data-sidebar-item
                           title="Manajemen User"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                           {{ request()->routeIs('users.*')
                              ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                              : 'text-slate-600 dark:text-stone-300 hover:bg-slate-100 dark:hover:bg-stone-800/75 hover:text-blue-600 dark:hover:text-stone-100' }}">
                            <i class="bi bi-people text-base shrink-0 {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 dark:text-stone-400 group-hover:text-blue-600 dark:group-hover:text-stone-200' }}"></i>
                            <span class="sidebar-item-label truncate">Manajemen User</span>
                        </a>
                    @endif
                </nav>
            </div>
        @endif
    </div>

    {{-- Bottom Navigation & User Actions (Warm Dark Surface — Never Navy Blue in Dark Mode) --}}
    <div id="sidebarBottomFooter" class="p-3 border-t border-slate-200/70 dark:border-stone-800/80 space-y-1 bg-slate-50/60 dark:bg-[#161412]">
        <a href="{{ route('settings') }}"
           data-framer-nav
           data-sidebar-item
           title="Pengaturan"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
           {{ request()->routeIs('settings')
              ? 'bg-slate-900 dark:bg-stone-800 text-white dark:text-stone-100 shadow-xs font-semibold'
              : 'text-slate-600 dark:text-stone-300 hover:bg-slate-200/70 dark:hover:bg-stone-800/80 hover:text-slate-900 dark:hover:text-stone-100' }}">
            <i class="bi bi-gear text-base shrink-0 {{ request()->routeIs('settings') ? 'text-white dark:text-amber-400' : 'text-slate-400 dark:text-stone-400 group-hover:text-slate-800 dark:group-hover:text-stone-200' }}"></i>
            <span class="sidebar-item-label truncate">Pengaturan</span>
        </a>

        <button type="button"
                data-modal-target="logoutModal"
                data-sidebar-item
                title="Keluar"
                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-rose-600 dark:text-rose-400 bg-transparent dark:bg-transparent hover:bg-rose-50 dark:hover:bg-stone-800/80 transition-all duration-150 group cursor-pointer">
            <i class="bi bi-box-arrow-right text-base shrink-0 text-rose-500 dark:text-rose-400 group-hover:translate-x-0.5 transition-transform"></i>
            <span class="sidebar-item-label truncate">Keluar</span>
        </button>
    </div>
</aside>