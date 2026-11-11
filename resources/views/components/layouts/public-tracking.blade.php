@props([
    'title' => 'Status Servis HP',
    'storeName' => 'SEMAPON Servis',
    'storePhone' => null,
])

<x-layouts.base :title="$title . ' — ' . $storeName">
    @push('meta')
        <meta name="robots" content="noindex, nofollow">
    @endpush
    <div class="min-h-screen bg-slate-50 flex flex-col justify-between">
        <!-- Public Tracking Top Bar -->
        <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 shadow-2xs">
            <div class="max-w-2xl mx-auto px-4 py-3.5 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-primary-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                        <i data-lucide="smartphone" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold text-slate-900 tracking-tight leading-none">{{ $storeName }}</h1>
                        <span class="text-2xs text-slate-400">Tracking Servis Real-Time</span>
                    </div>
                </div>

                @if($storePhone)
                    <a
                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storePhone) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl border border-emerald-200/80 transition-colors"
                    >
                        <i data-lucide="message-circle" class="w-3.5 h-3.5 mr-1 text-emerald-600"></i>
                        <span>Hubungi Toko</span>
                    </a>
                @endif
            </div>
        </header>

        <!-- Main Tracking Content -->
        <main class="flex-1 max-w-2xl w-full mx-auto p-4 sm:p-6">
            {{ $slot }}
        </main>

        <!-- Public Footer -->
        <footer class="py-6 text-center text-xs text-slate-400 border-t border-slate-200/60 bg-white">
            <p>Didukung oleh <span class="font-semibold text-slate-600">SEMAPON</span> — Sistem Servis HP</p>
        </footer>
    </div>
</x-layouts.base>
