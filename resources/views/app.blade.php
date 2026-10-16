<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="author" content="fwzann-abd (https://github.com/fwzann-abd)">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Inventaris Diskominfo Garut') }}</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#0b132b] text-slate-800 dark:text-slate-100 antialiased">
    @inertia
</body>
</html>
