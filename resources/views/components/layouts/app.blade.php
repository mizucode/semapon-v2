@props([
    'title' => null,
    'header' => null,
    'breadcrumbs' => [],
    'role' => 'owner', // super_admin, owner, admin, technician
])

<x-layouts.base :title="$title ? $title . ' — SEMAPON' : 'SEMAPON — Sistem Manajemen Servis HP'">
    <div
        x-data="{
            mobileMenuOpen: false,
            notificationsOpen: false,
            userDropdownOpen: false
        }"
        class="min-h-full lg:h-screen lg:overflow-hidden flex flex-col lg:flex-row bg-slate-50"
    >
        <!-- Mobile Drawer Backdrop -->
        <div
            x-show="mobileMenuOpen"
            x-cloak
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden"
            @click="mobileMenuOpen = false"
        ></div>

        <!-- Mobile Drawer -->
        <div
            x-show="mobileMenuOpen"
            x-cloak
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl flex flex-col lg:hidden border-r border-slate-200"
        >
            <!-- Drawer Header -->
            <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-primary-600 flex items-center justify-center text-white shadow-xs">
                        <i data-lucide="smartphone" class="w-5 h-5"></i>
                    </div>
                    <span class="font-bold text-lg text-slate-900 tracking-tight">SEMAPON</span>
                </div>
                <button
                    type="button"
                    @click="mobileMenuOpen = false"
                    class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
                >
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Drawer Navigation -->
            <div class="flex-1 overflow-y-auto no-scrollbar px-4 py-4 space-y-1">
                @include('components.layouts.partials.navigation-links', ['isMobile' => true, 'role' => $role])
            </div>

            <!-- Drawer Footer -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()->name ?? 'User SEMAPON' }}</p>
                        <p class="text-2xs text-slate-500 capitalize">{{ $role }}</p>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                    <a href="{{ route('profile') }}" class="text-slate-600 hover:text-primary-600 font-medium flex items-center">
                        <i data-lucide="user" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i>
                        Profil
                    </a>
                    <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}" class="inline">
                        @csrf
                        <button type="submit" class="text-rose-600 hover:text-rose-700 font-medium flex items-center">
                            <i data-lucide="log-out" class="w-3.5 h-3.5 mr-1 text-rose-500"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Desktop Sidebar -->
        <aside class="hidden lg:flex lg:flex-col lg:w-72 lg:shrink-0 bg-white border-r border-slate-200/80 lg:h-screen lg:sticky lg:top-0">
            <!-- Sidebar Header / Brand -->
            <div class="h-16 shrink-0 flex items-center px-6 border-b border-slate-100 space-x-3">
                <div class="w-9 h-9 rounded-xl bg-primary-600 flex items-center justify-center text-white shadow-xs">
                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-lg text-slate-900 tracking-tight">SEMAPON</span>
            </div>

            <!-- Sidebar Navigation Links -->
            <div class="flex-1 overflow-y-auto no-scrollbar px-4 py-5 space-y-1">
                @include('components.layouts.partials.navigation-links', ['isMobile' => false, 'role' => $role])
            </div>

            <!-- Sidebar Footer / Store Switcher & Account -->
            <div class="p-4 shrink-0 border-t border-slate-100">
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/70 flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs shrink-0">
                        <i data-lucide="store" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">{{ $activeStore->name ?? 'Toko Anda' }}</p>
                        <p class="text-2xs text-emerald-600 font-medium">Paket {{ ucfirst($activeStore->plan ?? 'Starter') }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:h-screen lg:overflow-y-auto no-scrollbar pb-16 lg:pb-0">
            <!-- Top Header (Desktop only; hidden in mobile / Android mode) -->
            <header class="hidden lg:flex h-16 shrink-0 bg-white/95 backdrop-blur-xs border-b border-slate-200/80 px-4 sm:px-6 items-center justify-between sticky top-0 z-30">
                <div class="flex items-center space-x-3 min-w-0">
                    <!-- Mobile Hamburger -->
                    <button
                        type="button"
                        @click="mobileMenuOpen = true"
                        class="p-2 -ml-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg lg:hidden transition-colors"
                        aria-label="Buka Menu"
                    >
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <!-- Breadcrumbs -->
                    <nav class="flex items-center text-xs font-medium text-slate-500 truncate" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1.5 truncate">
                            <li class="inline-flex items-center">
                                <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="hover:text-slate-800 flex items-center">
                                    <i data-lucide="home" class="w-3.5 h-3.5 mr-1"></i>
                                    <span>Beranda</span>
                                </a>
                            </li>
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="inline-flex items-center space-x-1.5">
                                    <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                                    @if(!empty($breadcrumb['url']))
                                        <a href="{{ $breadcrumb['url'] }}" class="hover:text-slate-800 truncate">{{ $breadcrumb['label'] }}</a>
                                    @else
                                        <span class="text-slate-800 font-semibold truncate">{{ $breadcrumb['label'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <!-- Notifications Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button
                            type="button"
                            @click="open = !open"
                            @click.outside="open = false"
                            class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors"
                            aria-label="Notifikasi"
                        >
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white"></span>
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
                            class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50"
                        >
                            <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                <h4 class="text-xs font-semibold text-slate-800">Notifikasi & Pengingat</h4>
                                <span class="text-2xs bg-primary-50 text-primary-700 px-2 py-0.5 rounded-full font-medium">3 Baru</span>
                            </div>
                            <div class="py-1 divide-y divide-slate-50 max-h-72 overflow-y-auto">
                                <a href="#" class="block px-4 py-3 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-start space-x-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-slate-800 leading-snug">Servis selesai belum diambil</p>
                                            <p class="text-2xs text-slate-400 mt-0.5">iPhone 11 — Bpk. Anton (3 hari lalu)</p>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="block px-4 py-3 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-start space-x-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="package-x" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-slate-800 leading-snug">Stok LCD menipis</p>
                                            <p class="text-2xs text-slate-400 mt-0.5">LCD Redmi Note 10 sisa 1 unit</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-2 border-t border-slate-100 text-center">
                                <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="text-2xs font-semibold text-primary-600 hover:text-primary-700">
                                    Buka Semua Pengingat & Notifikasi →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button
                            type="button"
                            @click="open = !open"
                            @click.outside="open = false"
                            class="flex items-center space-x-2 p-1.5 hover:bg-slate-100 rounded-xl transition-colors"
                        >
                            <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="hidden md:inline-block text-xs font-medium text-slate-700 max-w-[120px] truncate">
                                {{ auth()->user()->name ?? 'Akun Saya' }}
                            </span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
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
                            class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50"
                        >
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                                <p class="text-2xs text-slate-400 truncate">{{ auth()->user()->email ?? 'user@semapon.com' }}</p>
                            </div>
                            <a href="{{ route('profile') }}" class="flex items-center px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 transition-colors">
                                <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i>
                                Profil & Keamanan
                            </a>
                            <a href="{{ Route::has('settings.store') ? route('settings.store') : '#' }}" class="flex items-center px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 transition-colors">
                                <i data-lucide="settings" class="w-4 h-4 mr-2 text-slate-400"></i>
                                Pengaturan Toko
                            </a>
                            <div class="my-1 border-t border-slate-100"></div>
                            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors">
                                    <i data-lucide="log-out" class="w-4 h-4 mr-2 text-rose-500"></i>
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Title / Subheader (Optional) -->
            @if($header)
                <div class="bg-white border-b border-slate-200/60 px-4 sm:px-6 py-4">
                    {{ $header }}
                </div>
            @endif

            <!-- Main Content View -->
            <main class="flex-1 p-4 sm:p-5 max-w-7xl w-full mx-auto">
                {{ $slot }}
            </main>
        </div>

        <!-- Mobile Bottom Navigation Bar (Sticky) -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-30 px-2 py-1 shadow-lg">
            <div class="grid grid-cols-5 items-center justify-around text-center">
                <!-- 1. Home / Dashboard -->
                <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('dashboard') ? 'text-primary-600 font-semibold' : 'text-slate-600 hover:text-primary-600' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-3xs font-medium mt-1">Beranda</span>
                </a>

                <!-- 2. Orders -->
                <a href="{{ Route::has('orders.index') ? route('orders.index') : '#' }}" class="flex flex-col items-center py-1 {{ request()->routeIs('orders.*') ? 'text-primary-600 font-semibold' : 'text-slate-600 hover:text-primary-600' }}">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                    <span class="text-3xs font-medium mt-1">Order</span>
                </a>

                <!-- 3. Floating Quick Add Order (+) -->
                <div class="flex justify-center -mt-5">
                    <a
                        href="{{ Route::has('orders.create') ? route('orders.create') : '#' }}"
                        class="w-12 h-12 rounded-full bg-primary-600 text-white flex items-center justify-center shadow-lg hover:bg-primary-500 active:scale-95 transition-all"
                        aria-label="Terima Servis Baru"
                    >
                        <i data-lucide="plus" class="w-6 h-6"></i>
                    </a>
                </div>

                <!-- 4. Pelanggan -->
                <a href="{{ Route::has('customers.index') ? route('customers.index') : '#' }}" class="flex flex-col items-center py-1 {{ request()->routeIs('customers.*') ? 'text-primary-600 font-semibold' : 'text-slate-600 hover:text-primary-600' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="text-3xs font-medium mt-1">Pelanggan</span>
                </a>

                <!-- 5. Halaman Menu / Lainnya -->
                <a
                    href="{{ Route::has('menu') ? route('menu') : '#' }}"
                    class="flex flex-col items-center py-1 {{ request()->routeIs('menu') ? 'text-primary-600 font-semibold' : 'text-slate-600 hover:text-primary-600' }}"
                >
                    <i data-lucide="menu" class="w-5 h-5"></i>
                    <span class="text-3xs font-medium mt-1">Lainnya</span>
                </a>
            </div>
        </nav>
    </div>
</x-layouts.base>
