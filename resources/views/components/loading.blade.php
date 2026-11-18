@props([
    'target' => null,
    'size' => 'md', // sm, md, lg
    'text' => 'Memuat data...',
])

@php
$sizeClasses = [
    'sm' => 'w-4 h-4',
    'md' => 'w-6 h-6',
    'lg' => 'w-10 h-10',
][$size] ?? 'w-6 h-6';
@endphp

<div
    {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-6 text-slate-500']) }}
    @if($target) wire:loading wire:target="{{ $target }}" @endif
>
    <svg class="{{ $sizeClasses }} text-primary-600 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
    </svg>
    @if($text)
        <span class="mt-2 text-xs font-medium text-slate-500">{{ $text }}</span>
    @endif
</div>

