@props([
    'status' => null,
    'variant' => null,
    'size' => 'md', // sm, md
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

    // Variants (matching design guide: Primary, Success, Error, Warning, Info, Gray)
    'primary' => 'bg-blue-600 text-white shadow-xs',
    'success' => 'bg-emerald-500 text-white shadow-xs',
    'error' => 'bg-rose-500 text-white shadow-xs',
    'danger' => 'bg-rose-500 text-white shadow-xs',
    'warning' => 'bg-amber-500 text-white shadow-xs',
    'info' => 'bg-sky-500 text-white shadow-xs',
    'gray' => 'bg-slate-700 text-white shadow-xs',
    'slate' => 'bg-slate-700 text-white shadow-xs',
];

$labelMap = [
    // Service order database statuses
    'received' => 'Diterima',
    'diagnosing' => 'Diagnosa',
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

$key = $variant ?? $status;
$colorClasses = $colorMap[$key] ?? 'bg-slate-700 text-white shadow-xs';
$sizeClasses = $size === 'sm' ? 'px-2.5 py-0.5 text-2xs' : 'px-3 py-1 text-xs';
$displayText = $slot->isNotEmpty() ? $slot : ($labelMap[$status] ?? ucfirst(str_replace('_', ' ', $status ?? '')));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-semibold rounded-full {$sizeClasses} {$colorClasses} whitespace-nowrap transition-colors"]) }}>
    {{ $displayText }}
</span>

