@props([
    'status' => null,
    'variant' => null,
    'size' => 'md', // sm, md
])

@php
$colorMap = [
    // Service order statuses
    'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
    'masuk' => 'bg-blue-50 text-blue-700 border-blue-200',
    'antrean' => 'bg-amber-50 text-amber-700 border-amber-200',
    'diagnosa' => 'bg-purple-50 text-purple-700 border-purple-200',
    'konfirmasi_biaya' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
    'proses_servis' => 'bg-sky-50 text-sky-700 border-sky-200',
    'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'diambil' => 'bg-slate-100 text-slate-600 border-slate-200',
    'dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
    'garansi' => 'bg-yellow-50 text-yellow-800 border-yellow-200',

    // Variants
    'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
    'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
    'info' => 'bg-blue-50 text-blue-700 border-blue-200',
    'slate' => 'bg-slate-100 text-slate-700 border-slate-200',
];

$labelMap = [
    'draft' => 'Draft',
    'masuk' => 'Unit Masuk',
    'antrean' => 'Antrean',
    'diagnosa' => 'Diagnosa',
    'konfirmasi_biaya' => 'Konfirmasi Biaya',
    'proses_servis' => 'Dalam Pengerjaan',
    'selesai' => 'Selesai (Siap Diambil)',
    'diambil' => 'Sudah Diambil',
    'dibatalkan' => 'Dibatalkan',
    'garansi' => 'Klaim Garansi',
];

$key = $variant ?? $status;
$colorClasses = $colorMap[$key] ?? 'bg-slate-100 text-slate-700 border-slate-200';
$sizeClasses = $size === 'sm' ? 'px-2 py-0.5 text-2xs' : 'px-2.5 py-1 text-xs';
$displayText = $slot->isNotEmpty() ? $slot : ($labelMap[$status] ?? ucfirst(str_replace('_', ' ', $status ?? '')));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full border {$sizeClasses} {$colorClasses} whitespace-nowrap transition-colors"]) }}>
    {{ $displayText }}
</span>

