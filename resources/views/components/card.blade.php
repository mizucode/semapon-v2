@props([
    'title' => null,
    'subtitle' => null,
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden']) }}>
    @if($title || $action)
        <div class="px-4 sm:px-5 py-3.5 sm:py-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <div>
                @if($title)
                    <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($action)
                <div class="shrink-0 ml-auto">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>
</div>

