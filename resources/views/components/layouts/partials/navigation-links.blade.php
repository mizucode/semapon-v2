@props([
    'isMobile' => false,
    'role' => 'owner',
])

@php
$navItemClass = 'flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition-colors group ';
$activeClass = 'bg-primary-600 text-white font-semibold shadow-xs';
$inactiveClass = 'text-slate-600 hover:text-slate-900 hover:bg-slate-50';
$iconClass = 'w-5 h-5 mr-3 shrink-0 text-slate-400 group-hover:text-slate-600 transition-colors';
$activeIconClass = 'w-5 h-5 mr-3 shrink-0 text-white';
@endphp

@if($role === 'super_admin')
    <div class="space-y-1">
        <div class="px-3 py-1.5 text-xs font-semibold text-slate-400">Super Admin</div>
        <a href="{{ Route::has('superadmin.dashboard') ? route('superadmin.dashboard') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('superadmin.dashboard') ? $activeClass : $inactiveClass }}">
            <i data-lucide="layout-dashboard" class="{{ request()->routeIs('superadmin.dashboard') ? $activeIconClass : $iconClass }}"></i>
            <span>Overview Platform</span>
        </a>
        <a href="#" class="{{ $navItemClass }} {{ request()->routeIs('superadmin.tenants*') ? $activeClass : $inactiveClass }}">
            <i data-lucide="building-2" class="{{ request()->routeIs('superadmin.tenants*') ? $activeIconClass : $iconClass }}"></i>
            <span>Kelola Tenant</span>
        </a>
        <a href="#" class="{{ $navItemClass }} {{ request()->routeIs('superadmin.subscriptions*') ? $activeClass : $inactiveClass }}">
            <i data-lucide="credit-card" class="{{ request()->routeIs('superadmin.subscriptions*') ? $activeIconClass : $iconClass }}"></i>
            <span>Paket & Langganan</span>
        </a>
        <a href="#" class="{{ $navItemClass }} {{ request()->routeIs('superadmin.analytics*') ? $activeClass : $inactiveClass }}">
            <i data-lucide="line-chart" class="{{ request()->routeIs('superadmin.analytics*') ? $activeIconClass : $iconClass }}"></i>
            <span>Analisis Platform</span>
        </a>
    </div>
@elseif($role === 'technician')
    <div class="space-y-1">
        <div class="px-3 py-1.5 text-xs font-semibold text-slate-400">Teknisi Area</div>
        <a href="#" class="{{ $navItemClass }} {{ request()->routeIs('tech.dashboard') ? $activeClass : $inactiveClass }}">
            <i data-lucide="wrench" class="{{ request()->routeIs('tech.dashboard') ? $activeIconClass : $iconClass }}"></i>
            <span>Order Saya</span>
        </a>
        <a href="#" class="{{ $navItemClass }} {{ request()->routeIs('tech.history') ? $activeClass : $inactiveClass }}">
            <i data-lucide="check-circle-2" class="{{ request()->routeIs('tech.history') ? $activeIconClass : $iconClass }}"></i>
            <span>Riwayat Servis</span>
        </a>
        <a href="#" class="{{ $navItemClass }} {{ request()->routeIs('tech.parts') ? $activeClass : $inactiveClass }}">
            <i data-lucide="cpu" class="{{ request()->routeIs('tech.parts') ? $activeIconClass : $iconClass }}"></i>
            <span>Permintaan Sparepart</span>
        </a>
    </div>
@else
    {{-- Owner & Admin Default Navigation --}}
    <div class="space-y-1">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="{{ $navItemClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
            <i data-lucide="layout-dashboard" class="{{ request()->routeIs('dashboard') ? $activeIconClass : $iconClass }}"></i>
            <span>Dashboard</span>
        </a>

        <div class="pt-3.5 pb-1 px-3 text-xs font-semibold text-slate-400">Operasional Servis</div>

        <a href="{{ Route::has('customers.index') ? route('customers.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('customers.*') ? $activeClass : $inactiveClass }}">
            <i data-lucide="users" class="{{ request()->routeIs('customers.*') ? $activeIconClass : $iconClass }}"></i>
            <span>Pelanggan</span>
        </a>

        <a href="{{ Route::has('orders.index') ? route('orders.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('orders.*') ? $activeClass : $inactiveClass }}">
            <i data-lucide="clipboard-list" class="{{ request()->routeIs('orders.*') ? $activeIconClass : $iconClass }}"></i>
            <span>Order Servis</span>
        </a>

        <a href="{{ Route::has('services.index') ? route('services.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('services.*') ? $activeClass : $inactiveClass }}">
            <i data-lucide="sparkles" class="{{ request()->routeIs('services.*') ? $activeIconClass : $iconClass }}"></i>
            <span>Katalog Layanan</span>
        </a>

        @if($role === 'owner')
            <a href="{{ Route::has('technicians.index') ? route('technicians.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('technicians.*') ? $activeClass : $inactiveClass }}">
                <i data-lucide="user-cog" class="{{ request()->routeIs('technicians.*') ? $activeIconClass : $iconClass }}"></i>
                <span>Teknisi & Komisi</span>
            </a>
        @endif

        <div class="pt-3.5 pb-1 px-3 text-xs font-semibold text-slate-400">Inventaris & Barang</div>

        <a href="{{ Route::has('inventory.index') ? route('inventory.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('inventory.*') ? $activeClass : $inactiveClass }}">
            <i data-lucide="cpu" class="{{ request()->routeIs('inventory.*') ? $activeIconClass : $iconClass }}"></i>
            <span>Produk & Sparepart</span>
        </a>
        <a href="{{ Route::has('suppliers.index') ? route('suppliers.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('suppliers.*') || request()->routeIs('purchases.*') ? $activeClass : $inactiveClass }}">
            <i data-lucide="truck" class="{{ request()->routeIs('suppliers.*') || request()->routeIs('purchases.*') ? $activeIconClass : $iconClass }}"></i>
            <span>Supplier & Pembelian</span>
        </a>

        @if($role === 'owner')
            <div class="pt-3.5 pb-1 px-3 text-xs font-semibold text-slate-400">Keuangan & Rekap</div>

            <a href="{{ Route::has('finances.index') ? route('finances.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('finances.*') ? $activeClass : $inactiveClass }}">
                <i data-lucide="wallet" class="{{ request()->routeIs('finances.*') ? $activeIconClass : $iconClass }}"></i>
                <span>Kas & Keuangan</span>
            </a>
            <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('reports.*') ? $activeClass : $inactiveClass }}">
                <i data-lucide="bar-chart-3" class="{{ request()->routeIs('reports.*') ? $activeIconClass : $iconClass }}"></i>
                <span>Laporan & Analisis</span>
            </a>

            <div class="pt-3.5 pb-1 px-3 text-xs font-semibold text-slate-400">Pengaturan</div>

            <a href="{{ Route::has('settings.store') ? route('settings.store') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('settings.*') ? $activeClass : $inactiveClass }}">
                <i data-lucide="settings" class="{{ request()->routeIs('settings.*') ? $activeIconClass : $iconClass }}"></i>
                <span>Pengaturan Toko</span>
            </a>
            <a href="{{ Route::has('subscriptions.index') ? route('subscriptions.index') : '#' }}" class="{{ $navItemClass }} {{ request()->routeIs('subscriptions.*') ? $activeClass : $inactiveClass }}">
                <i data-lucide="sparkle" class="{{ request()->routeIs('subscriptions.*') ? $activeIconClass : $iconClass }}"></i>
                <span>Paket Langganan</span>
            </a>
        @endif
    </div>
@endif
