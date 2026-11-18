---
name: ui-loading-icon
description: >
  Standar wajib loading icon SEMAPON. Wajib gunakan icon Lucide 'loader-circle'
  (https://lucide.dev/icons/loader-circle) untuk seluruh indikator loading, spinner, dan proses async.
---

# Standar Icon Loading SEMAPON

Seluruh indikator proses asynchronous, spinner, tombol loading, dan status `wire:loading` pada aplikasi SEMAPON **WAJIB** menggunakan icon Lucide **`loader-circle`** (referensi: [Lucide loader-circle](https://lucide.dev/icons/loader-circle)) dengan class animasi Tailwind `animate-spin`.

## Format Penggunaan Standar

### 1. Komponen Blade Standar (Direkomendasikan)
Gunakan komponen yang telah terintegrasi dengan icon `loader-circle`:

```blade
{{-- Komponen Loading Section / Container --}}
<x-loading target="saveOrder" text="Menyimpan order..." size="md" />

{{-- Komponen Button dengan Loading Target --}}
<x-button type="submit" variant="primary" loading="saveOrder">
    Simpan Order
</x-button>
```

### 2. Menggunakan Tag Lucide Icon (HTML / Blade)
```blade
<i data-lucide="loader-circle" class="w-4 h-4 text-primary-600 animate-spin"></i>
```

### 3. Menggunakan Inline SVG Lucide `loader-circle`
Gunakan format ini saat dibutuhkan untuk menghindari jeda render DOM pada `wire:loading`:

```blade
<svg class="animate-spin w-4 h-4 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
</svg>
```

### 4. Bersama Livewire & Alpine.js
```blade
<div wire:loading wire:target="submitForm" class="flex items-center gap-2 text-xs text-primary-600">
    <svg class="animate-spin w-3.5 h-3.5 text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
    </svg>
    <span>Memproses data...</span>
</div>
```

## Larangan
- **DILARANG** menggunakan spinner generic seperti lingkaran dot/arc non-Lucide (`<circle>` + `<path d="M4 12a8 8 0 018-8v8H4z">`).
- **DILARANG** menggunakan spinner murni CSS border bulat (`border-t-transparent rounded-full animate-spin`) tanpa bentuk icon `loader-circle`.
- **DILARANG** menggunakan icon lain seperti `loader`, `rotate-cw`, atau `refresh-cw` sebagai pengganti `loader-circle`.

