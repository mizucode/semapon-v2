@props([
    'variant' => 'primary', // primary, secondary, danger, outline, ghost
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconRight' => null,
    'type' => 'button',
    'loading' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-150 focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none active:scale-[0.98] cursor-pointer';

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-xs gap-1.5 min-h-[34px]',
    'md' => 'px-4 py-2.5 text-sm gap-2 min-h-[42px]',
    'lg' => 'px-5 py-3 text-base gap-2.5 min-h-[48px]',
][$size] ?? 'px-4 py-2.5 text-sm gap-2 min-h-[42px]';

$variantClasses = [
    'primary' => 'bg-primary-600 hover:bg-primary-700 text-white shadow-xs focus:ring-2 focus:ring-primary-500/30',
    'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 focus:ring-2 focus:ring-slate-300',
    'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs focus:ring-2 focus:ring-rose-500/30',
    'outline' => 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-2xs focus:ring-2 focus:ring-slate-200',
    'ghost' => 'hover:bg-slate-100 text-slate-600 hover:text-slate-900',
][$variant] ?? 'bg-primary-600 hover:bg-primary-700 text-white shadow-xs';
@endphp

<button
    type="{{ $type }}"
    @if($loading) wire:loading.attr="disabled" wire:target="{{ $loading }}" @endif
    {{ $attributes->merge(['class' => "{$baseClasses} {$sizeClasses} {$variantClasses}"]) }}
>
    @if($loading)
        <span wire:loading wire:target="{{ $loading }}" class="shrink-0">
            <svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
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

