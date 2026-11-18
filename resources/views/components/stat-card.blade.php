@props([
    'title' => null,
    'label' => null,
    'value' => null,
    'icon' => null,
    'badge' => null,
    'description' => null,
])

@php
$displayTitle = $title ?? $label;
@endphp

<div {{ $attributes->merge(['class' => 'bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between']) }}>
    @if($displayTitle || $icon)
        <div class="flex items-center justify-between gap-2">
            @if($displayTitle)
                <span class="text-sm font-medium text-slate-500">{{ $displayTitle }}</span>
            @endif
            @if($icon)
                <div class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $displayTitle ? 'mt-3 sm:mt-4' : '' }} flex items-baseline justify-between gap-2">
        <span class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
            {{ $value ?? $slot }}
        </span>

        @if($badge)
            <div class="shrink-0">
                {{ $badge }}
            </div>
        @endif
    </div>

    @if($description)
        <p class="text-xs text-slate-400 mt-2">{{ $description }}</p>
    @endif
</div>

