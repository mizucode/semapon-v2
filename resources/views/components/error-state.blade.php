@props([
    'title' => 'Terjadi kesalahan',
    'message' => 'Gagal memuat data atau memproses permintaan Anda.',
    'retryAction' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-6 text-center bg-rose-50/50 rounded-2xl border border-rose-100 my-4']) }}>
    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-rose-100 text-rose-600 mb-3">
        <i data-lucide="alert-circle" class="w-6 h-6"></i>
    </div>
    <h3 class="text-sm font-semibold text-rose-900">{{ $title }}</h3>
    <p class="mt-1 text-xs text-rose-700 max-w-sm">{{ $message }}</p>

    @if($retryAction)
        <div class="mt-4">
            <button
                type="button"
                wire:click="{{ $retryAction }}"
                class="inline-flex items-center px-3.5 py-1.5 text-xs font-semibold text-rose-800 bg-white border border-rose-200 hover:bg-rose-50 rounded-xl shadow-2xs transition-colors"
            >
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 mr-1.5"></i>
                Coba Lagi
            </button>
        </div>
    @endif
</div>

