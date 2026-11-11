@props([
    'label' => null,
    'description' => null,
    'id' => null,
])

@php
    $id = $id ?? 'toggle-' . md5($attributes->wire('model')->value() ?: Str::random(8));
@endphp

<label for="{{ $id }}" class="inline-flex items-center gap-2.5 cursor-pointer select-none group">
    @if ($label)
        <div class="text-right">
            <span class="block text-xs font-semibold text-slate-700 group-hover:text-slate-900 transition-colors leading-tight">{{ $label }}</span>
            @if ($description)
                <span class="block text-[11px] text-slate-400 leading-tight">{{ $description }}</span>
            @endif
        </div>
    @endif

    <div class="relative shrink-0">
        <input
            type="checkbox"
            id="{{ $id }}"
            {{ $attributes->merge(['class' => 'sr-only peer']) }}
        >
        <!-- Toggle track -->
        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-primary-500/25 rounded-full peer peer-checked:bg-primary-600 transition-colors"></div>
        <!-- Toggle thumb -->
        <div class="absolute top-0.5 start-[2px] bg-white rounded-full h-5 w-5 transition-transform shadow-xs peer-checked:translate-x-5"></div>
    </div>
</label>
