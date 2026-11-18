@props([
    'variant' => 'primary', // primary, secondary, danger, outline, ghost
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconRight' => null,
    'type' => 'button',
    'loading' => null,
    'href' => null,
])

@php
$baseClasses = 'inline-flex shrink-0 items-center justify-center font-semibold rounded-lg transition-all active:translate-y-px cursor-pointer focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none';

$sizeClasses = [
    'sm' => 'h-8 px-3.5 py-1.5 text-xs gap-1.5',
    'md' => 'h-10 px-5 py-2.5 text-sm gap-2',
    'lg' => 'h-11 px-6 py-3 text-base gap-2.5',
][$size] ?? 'h-10 px-5 py-2.5 text-sm gap-2';

$variantClasses = [
    'primary' => 'bg-primary-600 hover:bg-primary-700 text-white shadow-xs focus:ring-2 focus:ring-primary-500/30',
    'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 focus:ring-2 focus:ring-slate-300',
    'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs focus:ring-2 focus:ring-rose-500/30',
    'outline' => 'bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 hover:border-slate-300 shadow-2xs focus:ring-2 focus:ring-slate-200',
    'ghost' => 'hover:bg-slate-100 text-slate-600 hover:text-slate-900',
][$variant] ?? 'bg-primary-600 hover:bg-primary-700 text-white shadow-xs';

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
$targetHref = $href ?? $attributes->get('href');
@endphp

@if($targetHref)
    <a
        href="{{ $targetHref }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($icon)
            @if($icon === 'check-circle')
                <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            @else
                <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
            @endif
        @endif

        <span>{{ $slot }}</span>

        @if($iconRight)
            <i data-lucide="{{ $iconRight }}" class="w-4 h-4 shrink-0"></i>
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @if($loading) wire:loading.attr="disabled" wire:target="{{ $loading }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <span wire:loading wire:target="{{ $loading }}" class="shrink-0">
                <svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
            </span>
        @endif

        @if($icon)
            @if($icon === 'check-circle')
                <svg @if($loading) wire:loading.remove wire:target="{{ $loading }}" @endif class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            @else
                <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0" @if($loading) wire:loading.remove wire:target="{{ $loading }}" @endif></i>
            @endif
        @endif

        <span>{{ $slot }}</span>

        @if($iconRight)
            <i data-lucide="{{ $iconRight }}" class="w-4 h-4 shrink-0"></i>
        @endif
    </button>
@endif
