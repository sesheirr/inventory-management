{{-- Mobile Sidebar Backdrop --}}
<div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden hidden opacity-0 transition-opacity duration-300"></div>

{{-- Sidebar Container --}}
<aside id="sidebarOffcanvas" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-[#0f1b38] border-r border-slate-200/80 dark:border-slate-800 flex flex-col transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:h-screen lg:sticky lg:top-0 shadow-sm">

    {{-- Brand Header --}}
    <div class="h-16 px-5 flex items-center justify-between border-b border-slate-200/70 dark:border-slate-800/80 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 p-1 flex items-center justify-center shadow-sm border border-slate-200 dark:border-slate-700/80 group-hover:scale-105 transition-transform duration-200 shrink-0">
                <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-full h-full object-contain">
            </div>
            <div class="min-w-0">
                <h1 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white leading-tight">Inventaris Diskominfo</h1>
                <p class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 truncate">Diskominfo Garut</p>
            </div>
        </a>

        {{-- Mobile Close Button --}}
        <button type="button" id="closeSidebarBtn" class="lg:hidden text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="bi bi-x-lg text-lg"></i>
        </button>
    </div>

    {{-- Main Navigation Menu --}}
    <div class="flex-1 px-3 py-4 overflow-y-auto space-y-6">
        <div>
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Menu Utama</p>
            <nav class="space-y-1">
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('dashboard')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-grid text-base {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Dashboard</span>
                </a>

                @if(!auth()->user()->isSuperAdmin())
                {{-- Barang --}}
                <a href="{{ route('products.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('products.index') || (request()->routeIs('products.*') && !request()->routeIs('products.scan'))
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-box-seam text-base {{ request()->routeIs('products.index') || (request()->routeIs('products.*') && !request()->routeIs('products.scan')) ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Barang</span>
                </a>

                {{-- Scan Barcode --}}
                <a href="{{ route('products.scan') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('products.scan')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-qr-code-scan text-base {{ request()->routeIs('products.scan') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Scan Barcode</span>
                </a>

                {{-- Kategori --}}
                <a href="{{ route('categories.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('categories.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-tags text-base {{ request()->routeIs('categories.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Kategori</span>
                </a>

                {{-- Ruangan --}}
                <a href="{{ route('rooms.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('rooms.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-building text-base {{ request()->routeIs('rooms.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Ruangan</span>
                </a>

                {{-- Mutasi Barang --}}
                <a href="{{ route('mutations.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                   {{ request()->routeIs('mutations.*')
                      ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                    <i class="bi bi-arrow-left-right text-base {{ request()->routeIs('mutations.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                    <span>Mutasi Barang</span>
                </a>

                                @if(auth()->user()->isAdmin())
                                        {{-- Laporan --}}
                                        <a href="{{ route('reports.index') }}"
                                             class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                                             {{ request()->routeIs('reports.*')
                                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                                                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                                                <i class="bi bi-file-earmark-bar-graph text-base {{ request()->routeIs('reports.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                                                <span>Laporan</span>
                                        </a>
                                @endif
                @endif
            </nav>
        </div>

        {{-- Administrator Section --}}
        @if(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
            <div>
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Administrasi</p>
                <nav class="space-y-1">
                    {{-- Log Aktivitas --}}
                    <a href="{{ route('activity-logs.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                       {{ request()->routeIs('activity-logs.*')
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                        <i class="bi bi-clock-history text-base {{ request()->routeIs('activity-logs.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                        <span>Log Aktivitas</span>
                    </a>

                    {{-- Approval Mutasi --}}
                    <a href="{{ route('mutations.approvals') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                       {{ request()->routeIs('mutations.approvals')
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                        <i class="bi bi-check2-square text-base {{ request()->routeIs('mutations.approvals') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                        <span>Approval Mutasi</span>
                    </a>

                    {{-- Manajemen User (SuperAdmin Only) --}}
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('users.index') }}"
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

        @if(auth()->user()->isSuperAdmin())
            <div>
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Administrasi</p>
                <nav class="space-y-1">
                    <a href="{{ route('users.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                       {{ request()->routeIs('users.*')
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                        <i class="bi bi-people text-base {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                        <span>Manajemen User</span>
                    </a>
                </nav>
            </div>
        @endif
    </div>

    {{-- Bottom Navigation & User Actions --}}
    <div class="p-3 border-t border-slate-200/70 dark:border-slate-800/80 space-y-1 bg-slate-50/50 dark:bg-[#0c152d]">
                @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('settings') }}"
                             class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group
                             {{ request()->routeIs('settings')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25 font-semibold'
                                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/70 hover:text-blue-600 dark:hover:text-blue-400' }}">
                                <i class="bi bi-gear text-base {{ request()->routeIs('settings') ? 'text-white' : 'text-slate-400 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400' }}"></i>
                                <span>Pengaturan</span>
                        </a>
                @endif

        <button type="button" data-modal-target="logoutModal" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all duration-150 group">
            <i class="bi bi-box-arrow-right text-base text-rose-500 group-hover:translate-x-0.5 transition-transform"></i>
            <span>Keluar (Logout)</span>
        </button>

        {{-- Mobile Dark Mode Switch --}}
        <div class="pt-2 mt-1 border-t border-slate-200/50 dark:border-slate-800/60 lg:hidden">
            <button type="button" id="darkModeToggleMobile" class="w-full flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800/60 hover:bg-slate-200/70 dark:hover:bg-slate-800 transition-colors">
                <span class="flex items-center gap-2">
                    <i id="darkModeIconMobile" class="bi bi-moon-stars text-slate-500"></i>
                    <span id="darkModeTextMobile">Mode Gelap</span>
                </span>
                <span class="text-[10px] uppercase font-bold text-blue-600 dark:text-blue-400">Ganti</span>
            </button>
        </div>
    </div>
</aside>