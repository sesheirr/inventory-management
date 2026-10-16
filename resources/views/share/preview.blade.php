<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] }}</title>
    <meta name="description" content="{{ $meta['description'] }}">

    {{-- OpenGraph / WhatsApp / Telegram / Social Rich Preview --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Inventaris Diskominfo Kabupaten Garut">
    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:image" content="{{ $meta['image'] }}">
    <meta property="og:url" content="{{ $shortUrl }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $meta['title'] }}">
    <meta name="twitter:description" content="{{ $meta['description'] }}">
    <meta name="twitter:image" content="{{ $meta['image'] }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo-diskominfo.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 text-white flex items-center justify-center p-4 selection:bg-blue-600 selection:text-white">
    <div class="max-w-md w-full rounded-3xl bg-slate-900/90 border border-slate-800 p-6 sm:p-8 shadow-2xl text-center space-y-5">
        <div class="relative mx-auto w-24 h-24 rounded-2xl bg-white p-2.5 shadow-lg border border-slate-200 overflow-hidden flex items-center justify-center">
            <img src="{{ $meta['image'] }}"
                 alt="{{ $meta['title'] }}"
                 class="w-full h-full object-contain rounded-xl"
                 onerror="this.onerror=null;this.src='{{ asset('images/fallback-torn-paper.svg') }}';">
        </div>

        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-blue-500/15 text-blue-400 border border-blue-500/30">
                <span>{{ $meta['type'] }}</span>
                <span>•</span>
                <span>{{ $meta['badge'] }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-extrabold text-white leading-snug">
                {{ $meta['title'] }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                {{ $meta['description'] }}
            </p>
        </div>

        <div class="pt-2 space-y-3">
            <a id="deepLinkContinueBtn"
               href="{{ $meta['target_url'] }}"
               class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-600/25 transition-all cursor-pointer">
                <span>Buka Data Tujuan Sekarang</span>
                <i class="bi bi-arrow-right"></i>
            </a>
            <p class="text-[11px] text-slate-500">
                Mengalihkan otomatis ke halaman tujuan Inventaris Diskominfo Garut...
            </p>
        </div>
    </div>

    @if(!request()->boolean('preview'))
    <script>
        setTimeout(function () {
            var btn = document.getElementById('deepLinkContinueBtn');
            if (btn && btn.href) {
                window.location.replace(btn.href);
            }
        }, 120);
    </script>
    @endif
</body>
</html>
