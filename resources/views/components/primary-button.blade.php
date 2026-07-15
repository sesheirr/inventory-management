@props(['href' => null, 'type' => 'button', 'variant' => 'primary'])

@php
    $classes = 'btn rounded-pill px-4 py-2 ' . ($variant === 'outline' ? 'btn-outline-secondary' : 'btn-primary');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
