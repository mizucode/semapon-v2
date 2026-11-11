@props([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'description' => 'Data yang Anda cari atau butuhkan belum tersedia saat ini.',
    'actionLabel' => null,
    'actionUrl' => null,
    'actionClick' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-8 text-center bg-white rounded-2xl border border-dashed border-slate-200 my-4']) }}>
    <div class="flex items-center justify-center w-14 h-14 rounded-full bg-slate-50 text-slate-400 mb-3 border border-slate-100">
        <i data-lucide="{{ $icon }}" class="w-7 h-7"></i>
    </div>
    <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
    <p class="mt-1 text-sm text-slate-500 max-w-sm">{{ $description }}</p>

    @if($actionLabel)
        <div class="mt-5">
            @if($actionUrl)
                <a href="{{ $actionUrl }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-500 rounded-xl shadow-xs transition-colors">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                    {{ $actionLabel }}
                </a>
            @elseif($actionClick)
                <button type="button" @click="{{ $actionClick }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-500 rounded-xl shadow-xs transition-colors">
                    <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
                    {{ $actionLabel }}
                </button>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>

