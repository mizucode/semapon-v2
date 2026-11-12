@props([
    'status' => null,
    'variant' => null,
    'size' => 'md', // sm, md
])

@php
$colorMap = [
    // Service order database statuses
    'received' => 'bg-blue-50 text-blue-700 border-blue-200',
    'diagnosing' => 'bg-purple-50 text-purple-700 border-purple-200',
    'waiting_approval' => 'bg-amber-50 text-amber-700 border-amber-200',
    'in_progress' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
    'waiting_parts' => 'bg-orange-50 text-orange-700 border-orange-200',
    'testing' => 'bg-purple-50 text-purple-700 border-purple-200',
    'ready_for_pickup' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'picked_up' => 'bg-slate-100 text-slate-700 border-slate-200',
    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
    'unrepairable' => 'bg-rose-50 text-rose-700 border-rose-200',
    'pending' => 'bg-amber-50 text-amber-800 border-amber-200',

    // Indonesian aliases
    'masuk' => 'bg-blue-50 text-blue-700 border-blue-200',
    'antrean' => 'bg-amber-50 text-amber-700 border-amber-200',
    'diagnosa' => 'bg-purple-50 text-purple-700 border-purple-200',
    'konfirmasi_biaya' => 'bg-amber-50 text-amber-700 border-amber-200',
    'proses_servis' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
    'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'diambil' => 'bg-slate-100 text-slate-700 border-slate-200',
    'dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
    'garansi' => 'bg-yellow-50 text-yellow-800 border-yellow-200',

    // Payment statuses
    'unpaid' => 'bg-rose-50 text-rose-700 border-rose-200',
    'partially_paid' => 'bg-amber-50 text-amber-700 border-amber-200',
    'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',

    // Variants
    'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
    'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
    'info' => 'bg-blue-50 text-blue-700 border-blue-200',
    'slate' => 'bg-slate-100 text-slate-700 border-slate-200',
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
$colorClasses = $colorMap[$key] ?? 'bg-slate-100 text-slate-700 border-slate-200';
$sizeClasses = $size === 'sm' ? 'px-2 py-0.5 text-2xs' : 'px-2.5 py-1 text-xs';
$displayText = $slot->isNotEmpty() ? $slot : ($labelMap[$status] ?? ucfirst(str_replace('_', ' ', $status ?? '')));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full border {$sizeClasses} {$colorClasses} whitespace-nowrap transition-colors"]) }}>
    {{ $displayText }}
</span>

