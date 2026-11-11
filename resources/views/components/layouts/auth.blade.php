@props([
    'title' => 'Masuk ke SEMAPON',
    'subtitle' => null,
])

<x-layouts.base :title="$title . ' — SEMAPON'">
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-primary-600 text-white shadow-md mb-3">
                <i data-lucide="smartphone" class="w-6 h-6"></i>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">SEMAPON</h2>
            <p class="text-xs text-slate-500 font-medium tracking-wide uppercase mt-0.5">Sistem Manajemen Servis Smartphone</p>
            @if($subtitle)
                <p class="mt-2 text-xs text-slate-600">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 sm:px-8 shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-100">
                {{ $slot }}
            </div>

            <div class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} SEMAPON SaaS. Hak cipta dilindungi.
            </div>
        </div>
    </div>
</x-layouts.base>

