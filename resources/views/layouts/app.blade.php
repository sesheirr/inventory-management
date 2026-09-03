<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Inventaris Diskominfo Garut' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="h-full bg-slate-50 dark:bg-[#0b1329] text-slate-800 dark:text-slate-100 transition-colors duration-200 antialiased selection:bg-blue-500 selection:text-white">
    <div class="min-h-screen flex flex-col lg:flex-row">
        {{-- Sidebar Component --}}
        @include('partials.sidebar')

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            {{-- Top Navbar --}}
            @include('partials.navbar')

            {{-- Floating Toast Notifications --}}
            @php
                $toastMessage = session('success') ?: session('error') ?: session('warning') ?: session('info');
                $toastType = session('success') ? 'success' : (session('error') ? 'danger' : (session('warning') ? 'warning' : 'info'));
            @endphp

            @if($toastMessage)
                <div id="liveToast" class="fixed top-20 left-1/2 -translate-x-1/2 z-50 toast-pill-enter">
                    <div class="flex items-center gap-3 px-4 py-2.5 rounded-full shadow-lg border backdrop-blur-md transition-all duration-300
                        @if($toastType === 'success') bg-emerald-500/90 text-white border-emerald-400/30 shadow-emerald-500/20
                        @elseif($toastType === 'danger') bg-rose-500/90 text-white border-rose-400/30 shadow-rose-500/20
                        @elseif($toastType === 'warning') bg-amber-500/90 text-white border-amber-400/30 shadow-amber-500/20
                        @else bg-blue-500/90 text-white border-blue-400/30 shadow-blue-500/20 @endif">
                        
                        <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold shrink-0">
                            @if($toastType === 'success') <i class="bi bi-check-lg"></i>
                            @elseif($toastType === 'danger') <i class="bi bi-x-lg"></i>
                            @elseif($toastType === 'warning') <i class="bi bi-exclamation-lg"></i>
                            @else <i class="bi bi-info-lg"></i> @endif
                        </div>
                        <span class="text-xs md:text-sm font-medium pr-1">{{ $toastMessage }}</span>
                        <button type="button" class="toast-close-btn text-white/70 hover:text-white text-base leading-none pl-1 transition-colors">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Main Content --}}
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 max-w-[1600px] w-full mx-auto animate-fade-in">
                @yield('content')
            </main>

            {{-- Footer --}}
            @include('partials.footer')
        </div>
    </div>

    {{-- Logout Modal --}}
    @include('components.logout-modal')

    @stack('scripts')
</body>

</html>
