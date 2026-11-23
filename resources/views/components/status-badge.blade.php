@props([
    'status' => null,
    'variant' => null,
    'size' => 'md', // sm, md, lg
    'dot' => false,
    'tinted' => false,
])

@php
$colorMap = [
    // Service order database statuses
    'received' => 'bg-blue-600 text-white shadow-xs',
    'diagnosing' => 'bg-purple-600 text-white shadow-xs',
    'waiting_approval' => 'bg-amber-500 text-white shadow-xs',
    'in_progress' => 'bg-indigo-600 text-white shadow-xs',
    'waiting_parts' => 'bg-orange-500 text-white shadow-xs',
    'testing' => 'bg-violet-600 text-white shadow-xs',
    'ready_for_pickup' => 'bg-sky-500 text-white shadow-xs',
    'completed' => 'bg-emerald-500 text-white shadow-xs',
    'picked_up' => 'bg-slate-700 text-white shadow-xs',
    'cancelled' => 'bg-rose-500 text-white shadow-xs',
    'unrepairable' => 'bg-rose-600 text-white shadow-xs',
    'pending' => 'bg-amber-500 text-white shadow-xs',

    // Indonesian aliases
    'masuk' => 'bg-blue-600 text-white shadow-xs',
    'antrean' => 'bg-amber-500 text-white shadow-xs',
    'diagnosa' => 'bg-purple-600 text-white shadow-xs',
    'konfirmasi_biaya' => 'bg-amber-500 text-white shadow-xs',
    'proses_servis' => 'bg-indigo-600 text-white shadow-xs',
    'selesai' => 'bg-emerald-500 text-white shadow-xs',
    'diambil' => 'bg-slate-700 text-white shadow-xs',
    'dibatalkan' => 'bg-rose-500 text-white shadow-xs',
    'garansi' => 'bg-amber-500 text-white shadow-xs',

    // Payment statuses
    'unpaid' => 'bg-rose-500 text-white shadow-xs',
    'partially_paid' => 'bg-amber-500 text-white shadow-xs',
    'paid' => 'bg-emerald-500 text-white shadow-xs',

    // Store & User statuses
    'active' => 'bg-emerald-500 text-white shadow-xs',
    'inactive' => 'bg-slate-500 text-white shadow-xs',
    'suspended' => 'bg-rose-600 text-white shadow-xs',
    'closed' => 'bg-slate-600 text-white shadow-xs',
    'expired' => 'bg-amber-600 text-white shadow-xs',
    'trial' => 'bg-blue-500 text-white shadow-xs',

    // Variants (matching design guide: Primary, Success, Error, Warning, Info, Gray)
    'primary' => 'bg-blue-600 text-white shadow-xs',
    'success' => 'bg-emerald-500 text-white shadow-xs',
    'error' => 'bg-rose-500 text-white shadow-xs',
    'danger' => 'bg-rose-600 text-white shadow-xs',
    'warning' => 'bg-amber-500 text-white shadow-xs',
    'info' => 'bg-sky-500 text-white shadow-xs',
    'gray' => 'bg-slate-700 text-white shadow-xs',
    'slate' => 'bg-slate-700 text-white shadow-xs',
    'purple' => 'bg-purple-600 text-white shadow-xs',
    'indigo' => 'bg-indigo-600 text-white shadow-xs',
];

$labelMap = [
    // Service order database statuses
    'received' => 'Menunggu Diagnosa',
    'diagnosing' => 'Sedang Diagnosa',
    'waiting_approval' => 'Tunggu Konfirmasi',
    'in_progress' => 'Dalam Pengerjaan',
    'waiting_parts' => 'Menunggu Sparepart',
    'testing' => 'Pengujian (QC)',
    'ready_for_pickup' => 'Siap Diambil',
    'completed' => 'Selesai',
    'picked_up' => 'Sudah Diambil',
    'cancelled' => 'Dibatalkan',
    'unrepairable' => 'Tidak Bisa Diperbaiki',
    'pending' => 'Menunggu',

    // Indonesian aliases
    'masuk' => 'Unit Masuk',
    'antrean' => 'Antrean',
    'diagnosa' => 'Diagnosa',
    'konfirmasi_biaya' => 'Konfirmasi Biaya',
    'proses_servis' => 'Dalam Pengerjaan',
    'selesai' => 'Selesai',
    'diambil' => 'Sudah Diambil',
    'dibatalkan' => 'Dibatalkan',
    'garansi' => 'Klaim Garansi',

    // Payment statuses
    'unpaid' => 'Belum Lunas',
    'partially_paid' => 'Sebagian (DP)',
    'paid' => 'Lunas',

    // Store & User statuses
    'active' => 'Aktif',
    'inactive' => 'Nonaktif',
    'suspended' => 'Ditangguhkan',
    'closed' => 'Tutup',
    'expired' => 'Kedaluwarsa',
    'trial' => 'Masa Percobaan',
];

$tintedColorMap = [
    // Service order database statuses
    'received' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'diagnosing' => 'bg-purple-50 text-purple-700 border border-purple-200',
    'waiting_approval' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'in_progress' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
    'waiting_parts' => 'bg-orange-50 text-orange-700 border border-orange-200',
    'testing' => 'bg-violet-50 text-violet-700 border border-violet-200',
    'ready_for_pickup' => 'bg-sky-50 text-sky-700 border border-sky-200',
    'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'picked_up' => 'bg-slate-100 text-slate-700 border border-slate-200',
    'cancelled' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'unrepairable' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'pending' => 'bg-amber-50 text-amber-700 border border-amber-200',

    // Indonesian aliases
    'masuk' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'antrean' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'diagnosa' => 'bg-purple-50 text-purple-700 border border-purple-200',
    'konfirmasi_biaya' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'proses_servis' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
    'selesai' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'diambil' => 'bg-slate-100 text-slate-700 border border-slate-200',
    'dibatalkan' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'garansi' => 'bg-amber-50 text-amber-700 border border-amber-200',

    // Payment statuses
    'unpaid' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'partially_paid' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'paid' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',

    // Store & User statuses
    'active' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'inactive' => 'bg-slate-100 text-slate-600 border border-slate-200',
    'suspended' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'closed' => 'bg-slate-100 text-slate-600 border border-slate-200',
    'expired' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'trial' => 'bg-blue-50 text-blue-700 border border-blue-200',

    // Variants
    'primary' => 'bg-blue-50 text-blue-700 border border-blue-200',
    'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'error' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'danger' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'warning' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'info' => 'bg-sky-50 text-sky-700 border border-sky-200',
    'gray' => 'bg-slate-100 text-slate-700 border border-slate-200',
    'slate' => 'bg-slate-100 text-slate-700 border border-slate-200',
    'purple' => 'bg-purple-50 text-purple-700 border border-purple-200',
    'indigo' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
];

$key = $variant ?? $status;
$colorClasses = $tinted 
    ? ($tintedColorMap[$key] ?? 'bg-slate-100 text-slate-700 border border-slate-200')
    : ($colorMap[$key] ?? 'bg-slate-700 text-white shadow-xs');

$sizeClasses = match($size) {
    'sm' => 'px-2.5 py-0.5 text-[11px] gap-1',
    'lg' => 'px-3.5 py-1.5 text-sm gap-1.5',
    default => 'px-3 py-1 text-xs gap-1.5',
};

$displayText = $slot->isNotEmpty() ? $slot : ($labelMap[$status] ?? ucfirst(str_replace('_', ' ', $status ?? '')));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center justify-center font-semibold rounded-full {$sizeClasses} {$colorClasses} whitespace-nowrap transition-colors select-none"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $tinted ? 'bg-current opacity-70' : 'bg-white/80' }} shrink-0"></span>
    @endif
    <span>{{ $displayText }}</span>
</span>

