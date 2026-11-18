@props([
    'options' => [],
    'header' => null,
    'placeholder' => 'Pilih...',
])

@php
$wireModel = $attributes->wire('model')->value();
$currentValue = $wireModel ? data_get($this, $wireModel) : null;
$labelsJson = json_encode($options);
@endphp

<div
    x-data="{
        open: false,
        selected: $wire.entangle('{{ $wireModel }}'),
        labels: {{ $labelsJson }},
        get label() {
            return this.labels[this.selected] ?? '{{ $placeholder }}';
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative"
>
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-hidden transition-all cursor-pointer shadow-2xs hover:border-slate-300"
    >
        <span class="truncate" x-text="label">{{ $options[$currentValue] ?? $placeholder }}</span>
        <svg
            class="w-4 h-4 text-slate-400 shrink-0 ml-2 transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="m6 9 6 6 6-6"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute left-0 right-0 mt-1.5 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-40 max-h-60 overflow-y-auto divide-y divide-slate-50"
    >
        @if($header)
            <div class="px-3.5 py-1.5 text-3xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                {{ $header }}
            </div>
        @endif

        <div class="py-0.5">
            @foreach($options as $val => $text)
                <button
                    type="button"
                    @click="selected = '{{ $val }}'; open = false"
                    class="w-full flex items-center justify-between px-3.5 py-2 text-xs text-left transition-colors cursor-pointer"
                    :class="selected == '{{ $val }}' ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                >
                    <span>{{ $text }}</span>
                    <svg
                        x-show="selected == '{{ $val }}'"
                        class="w-4 h-4 text-primary-600 shrink-0 ml-2"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </button>
            @endforeach
        </div>
    </div>
</div>

