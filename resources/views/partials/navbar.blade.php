@php
    $showBackButton = false;

    if (Route::is('dashboard')) {
        $showBackButton = false;
    } elseif (Route::is('reports.index') || Route::is('reports.*')) {
        $showBackButton = true;
    } elseif (Route::is('products.index')) {
        $showBackButton = false;
    } elseif (Route::is('products.*')) {
        $showBackButton = true;
    } elseif (Route::is('categories.index')) {
        $showBackButton = false;
    } elseif (Route::is('categories.*')) {
        $showBackButton = true;
    } elseif (Route::is('rooms.index')) {
        $showBackButton = false;
    } elseif (Route::is('rooms.*')) {
        $showBackButton = true;
    } elseif (Route::is('mutations.index') || Route::is('mutations.*')) {
        $showBackButton = true;
    } elseif (Route::is('users.index') || Route::is('users.*')) {
        $showBackButton = true;
    } elseif (Route::is('activity-logs.index') || Route::is('activity-logs.*')) {
        $showBackButton = true;
    } elseif (Route::is('profile') || Route::is('profile.*')) {
        $showBackButton = true;
    } elseif (Route::is('settings')) {
        $showBackButton = true;
    }

    $backUrl = url()->previous();
    if (!$backUrl || $backUrl === url()->current()) {
        $backUrl = route('dashboard');
    }

    $currentUser = auth()->user();
@endphp

<header class="glass-nav sticky top-0 z-30 h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between transition-colors duration-200">
    {{-- Left Side: Hamburger & Back Navigation --}}
    <div class="flex items-center gap-3">
        {{-- Mobile Hamburger Menu --}}
        <button type="button" id="openSidebarBtn" class="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/40">
            <i class="bi bi-list text-2xl"></i>
        </button>

        {{-- Back Button --}}
        @if($showBackButton)
            <a href="{{ $backUrl }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400 shadow-sm transition-all duration-150 group" title="Kembali ke halaman sebelumnya">
                <i class="bi bi-arrow-left text-base group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
        @endif

        {{-- System Title & Breadcrumb on Desktop --}}
        <div class="hidden sm:block">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                @if(Route::is('dashboard')) Dashboard Inventaris
                @elseif(Route::is('products.*')) Manajemen Data Barang
                @elseif(Route::is('categories.*')) Kategori Barang
                @elseif(Route::is('rooms.*')) Manajemen Ruangan
                @elseif(Route::is('mutations.*')) Mutasi & Distribusi Barang
                @elseif(Route::is('reports.*')) Laporan Inventaris
                @elseif(Route::is('activity-logs.*')) Log Aktivitas Sistem
                @elseif(Route::is('users.*')) Manajemen Akun Pengguna
                @elseif(Route::is('profile.*')) Profil Pengguna
                @elseif(Route::is('settings')) Pengaturan Sistem
                @else Sistem Informasi Inventaris @endif
            </h2>
        </div>
    </div>

    {{-- Right Side: Theme Switcher & User Profile Chip --}}
    <div class="flex items-center gap-3">
        {{-- Theme Switcher Button (Desktop) --}}
        <button type="button" id="darkModeToggle" class="hidden sm:flex w-9 h-9 rounded-xl items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:hover:bg-slate-700/80 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/40" title="Ganti Mode Tampilan (Gelap / Terang)">
            <i id="themeIcon" class="bi bi-moon-stars text-base"></i>
        </button>

        {{-- User Profile Pill Link --}}
        <a href="{{ route('profile') }}" class="flex items-center gap-2.5 p-1.5 pr-3 rounded-full bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-500/50 shadow-sm transition-all duration-150 group">
            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shadow-sm overflow-hidden shrink-0">
                @if(!empty($currentUser?->avatar))
                    @php
                        $avatarUrl = \Illuminate\Support\Str::startsWith($currentUser->avatar, ['http://', 'https://'])
                            ? $currentUser->avatar
                            : asset('storage/' . $currentUser->avatar);
                    @endphp
                    <img src="{{ $avatarUrl }}" alt="{{ $currentUser->name }}" class="w-full h-full object-cover">
                @else
                    <span>{{ strtoupper(substr($currentUser->name ?? 'U', 0, 2)) }}</span>
                @endif
            </div>
            
            <div class="hidden sm:flex flex-col text-left">
                <span class="text-xs font-semibold text-slate-800 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-none truncate max-w-[130px]">
                    {{ $currentUser->name ?? 'Pengguna' }}
                </span>
                <span class="text-[10px] font-medium text-slate-400 dark:text-slate-400 mt-0.5 leading-none">
                    @if($currentUser?->isSuperAdmin()) Super Administrator
                    @elseif($currentUser?->isAdmin()) Administrator
                    @else Operator Inventaris @endif
                </span>
            </div>
        </a>
    </div>
</header>