{{-- Desktop Sidebar Container (Strictly Desktop lg:flex — Mobile uses Native Bottom Nav & App Sheet) --}}
<aside id="sidebarOffcanvas" class="hidden lg:flex lg:flex-col lg:w-64 lg:shrink-0 lg:sticky lg:top-0 lg:h-screen bg-white dark:bg-[#0f1b38] border-r border-slate-200/80 dark:border-slate-800 z-30 shadow-xs">

    {{-- Brand Header --}}
    <div class="h-16 px-5 flex items-center justify-between border-b border-slate-200/70 dark:border-slate-800/80 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center shadow-sm border border-slate-200 dark:border-slate-700/80 group-hover:scale-105 transition-transform duration-200 shrink-0 overflow-hidden">
                <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-full h-full object-cover">
            </div>
            <div class="min-w-0">
                <h1 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white leading-tight">Inventaris Diskominfo</h1>
                <p class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 truncate">Diskominfo Garut</p>
            </div>
        </a>
    </div>

    {{-- Main Navigation Menu --}}
    <div class="flex-1 px-3 py-4 overflow-y-auto space-y-6">
        <div>
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Menu Utama</p>
            <nav class="space-y-1">
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   data-framer-nav
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('dashboard')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-grid text-base {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Dashboard</span>
                </a>

                {{-- Barang --}}
                <a href="{{ route('products.index') }}"
                   data-framer-nav
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('products.index') || (request()->routeIs('products.*') && !request()->routeIs('products.scan'))
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-box-seam text-base {{ request()->routeIs('products.index') || (request()->routeIs('products.*') && !request()->routeIs('products.scan')) ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Barang</span>
                </a>

                {{-- Ruangan --}}
                <a href="{{ route('rooms.index') }}"
                   data-framer-nav
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('rooms.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-building text-base {{ request()->routeIs('rooms.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Ruangan</span>
                </a>

                {{-- Kategori --}}
                <a href="{{ route('categories.index') }}"
                   data-framer-nav
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('categories.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-tags text-base {{ request()->routeIs('categories.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Kategori</span>
                </a>

                {{-- Scan Barcode --}}
                <a href="{{ route('products.scan') }}"
                   data-framer-nav
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('products.scan')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-qr-code-scan text-base {{ request()->routeIs('products.scan') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Scan Barcode</span>
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
                   class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('mutations.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <span class="flex items-center gap-3">
                        <i class="bi bi-arrow-left-right text-base {{ request()->routeIs('mutations.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                        <span>Mutasi</span>
                    </span>
                    <span id="sidebarPendingMutationBadge" class="{{ $sidebarPendingMutations > 0 ? '' : 'hidden' }} px-2 py-0.5 rounded-full text-[10px] font-bold tabular-nums {{ request()->routeIs('mutations.*') ? 'bg-white text-blue-600' : 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' }}">
                        {{ $sidebarPendingMutations }}
                    </span>
                </a>
            </nav>
        </div>

        @if(auth()->user()->isAdmin())
            <div>
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Administrasi</p>
                <nav class="space-y-1">
                    {{-- Log Aktivitas --}}
                    <a href="{{ route('activity-logs.index') }}"
                       data-framer-nav
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                       {{ request()->routeIs('activity-logs.*')
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                        <i class="bi bi-clock-history text-base {{ request()->routeIs('activity-logs.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                        <span>Log Aktivitas</span>
                    </a>

                    {{-- Observability & Laravel Telescope --}}
                    <a href="{{ route('observability.index') }}"
                       data-framer-nav
                       class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                       {{ request()->routeIs('observability.*')
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-activity text-base {{ request()->routeIs('observability.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                            <span>Observability</span>
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase {{ request()->routeIs('observability.*') ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300' }}">LIVE</span>
                    </a>

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('users.index') }}"
                           data-framer-nav
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                           {{ request()->routeIs('users.*')
                              ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                            <i class="bi bi-people text-base {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                            <span>Manajemen User</span>
                        </a>
                    @endif
                </nav>
            </div>
        @endif
    </div>

    {{-- Bottom Navigation & User Actions --}}
    <div class="p-3 border-t border-slate-200/70 dark:border-slate-800/80 space-y-1 bg-slate-50/50 dark:bg-[#0c152d]">
        <a href="{{ route('settings') }}"
           data-framer-nav
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
           {{ request()->routeIs('settings')
              ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
            <i class="bi bi-gear text-base {{ request()->routeIs('settings') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
            <span>Pengaturan</span>
        </a>

        <button type="button" data-modal-target="logoutModal" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all duration-150 group cursor-pointer">
            <i class="bi bi-box-arrow-right text-base text-rose-500 group-hover:translate-x-0.5 transition-transform"></i>
            <span>Keluar</span>
        </button>
    </div>
</aside>