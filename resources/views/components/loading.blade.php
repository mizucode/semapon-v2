@props([
    'target' => null,
    'size' => 'md', // sm, md, lg
    'text' => 'Memuat data...',
])

@php
$sizeClasses = [
    'sm' => 'w-4 h-4 border-2',
    'md' => 'w-6 h-6 border-2',
    'lg' => 'w-10 h-10 border-3',
][$size] ?? 'w-6 h-6 border-2';
@endphp

<div
    {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-6 text-slate-500']) }}
    @if($target) wire:loading wire:target="{{ $target }}" @endif
>
    <div class="{{ $sizeClasses }} border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    @if($text)
        <span class="mt-2 text-xs font-medium text-slate-500">{{ $text }}</span>
    @endif
</div>

