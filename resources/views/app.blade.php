<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="author" content="fwzann-abd (https://github.com/fwzann-abd)">
    <meta name="co-collaborator" content="msayib (https://github.com/msayib) — Ideation, System Design, Architecture, Performance, Refactor, Tune-Up, UI/UX & Mobile-First High-Level Engineering">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Inventaris Diskominfo Garut') }}</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="min-h-screen bg-[#F8FAFC] dark:bg-[#0b132b] text-slate-800 dark:text-slate-100 antialiased relative">
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute inset-0 opacity-[0.032] dark:opacity-[0.05]" style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="absolute -top-48 right-1/4 w-[36rem] h-[36rem] rounded-full bg-blue-600/6 dark:bg-blue-500/8 blur-3xl"></div>
        <div class="absolute bottom-0 left-10 w-[28rem] h-[28rem] rounded-full bg-cyan-500/5 dark:bg-sky-500/7 blur-3xl"></div>
    </div>
    <div class="relative z-10">
        @inertia
    </div>
</body>
</html>
