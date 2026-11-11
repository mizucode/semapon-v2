<div class="max-w-xl mx-auto space-y-4 pb-6">
    <!-- User Profile & Store Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <div class="flex items-center space-x-3.5">
            <div class="w-13 h-13 rounded-2xl bg-primary-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shrink-0">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2">
                    <h2 class="text-base font-bold text-slate-900 truncate">{{ $user->name ?? 'Pengguna SEMAPON' }}</h2>
                    <span class="px-2 py-0.5 text-3xs font-semibold rounded-full bg-primary-50 text-primary-700 capitalize">
                        {{ $role }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $user->email ?? '-' }}</p>
                @if($store)
                    <div class="flex items-center space-x-1.5 mt-1 text-2xs text-slate-600">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-primary-600 shrink-0"></i>
                        <span class="font-medium truncate">{{ $store->name }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-emerald-600 font-semibold">Paket {{ ucfirst($store->plan ?? 'Starter') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('profile') }}" class="text-xs font-medium text-primary-600 hover:text-primary-700 flex items-center">
                <i data-lucide="user-circle" class="w-4 h-4 mr-1.5"></i>
                Kelola Profil & Kata Sandi
            </a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </div>
    </div>

    <!-- Section 1: Operasional Servis -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Operasional Servis</span>
            <span class="text-3xs text-slate-400 font-medium">Layanan Utama</span>
        </div>
        <div class="divide-y divide-slate-100">
            <!-- Order Servis -->
            <a href="{{ route('orders.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">Order Servis</p>
                        <p class="text-2xs text-slate-500">Antrean masuk, proses, & siap ambil</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($activeOrdersCount > 0)
                        <span class="px-2 py-0.5 text-2xs font-bold rounded-full bg-blue-100 text-blue-700">
                            {{ $activeOrdersCount }} Aktif
                        </span>
                    @endif
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                </div>
            </a>

            <!-- Katalog Layanan -->
            <a href="{{ route('services.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">Katalog Layanan</p>
                        <p class="text-2xs text-slate-500">Tarif jasa perbaikan & estimasi durasi</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </a>

            <!-- Pelanggan -->
            <a href="{{ route('customers.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">Pelanggan</p>
                        <p class="text-2xs text-slate-500">Database pelanggan & riwayat servis</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </a>

            @if($role === 'owner')
                <!-- Teknisi & Komisi -->
                <a href="{{ route('technicians.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <i data-lucide="user-cog" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">Teknisi & Komisi</p>
                            <p class="text-2xs text-slate-500">Performa kerja & hitung bagi hasil</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Section 2: Inventaris & Pengadaan -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Inventaris & Sparepart</span>
            <span class="text-3xs text-slate-400 font-medium">Stok & Barang</span>
        </div>
        <div class="divide-y divide-slate-100">
            <!-- Produk & Sparepart -->
            <a href="{{ route('inventory.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="cpu" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">Produk & Sparepart</p>
                        <p class="text-2xs text-slate-500">LCD, baterai, IC, aksesoris, & barcode</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($lowStockCount > 0)
                        <span class="px-2 py-0.5 text-2xs font-bold rounded-full bg-rose-100 text-rose-700">
                            {{ $lowStockCount }} Menipis
                        </span>
                    @endif
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                </div>
            </a>

            <!-- Supplier & Pembelian -->
            <a href="{{ route('suppliers.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                        <i data-lucide="truck" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">Supplier & Pembelian</p>
                        <p class="text-2xs text-slate-500">Daftar pemasok & riwayat restock</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </a>
        </div>
    </div>

    @if($role === 'owner')
        <!-- Section 3: Keuangan & Rekap (Owner Only) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Keuangan & Rekap</span>
                <span class="text-3xs text-slate-400 font-medium">Khusus Owner</span>
            </div>
            <div class="divide-y divide-slate-100">
                <!-- Kas & Keuangan -->
                <a href="{{ route('finances.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i data-lucide="wallet" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">Kas & Keuangan</p>
                            <p class="text-2xs text-slate-500">Buku kas harian, pemasukan & pengeluaran</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                </a>

                <!-- Laporan & Analisis -->
                <a href="{{ route('reports.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">Laporan & Analisis</p>
                            <p class="text-2xs text-slate-500">Grafik omzet, margin laba, & tren servis</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                </a>
            </div>
        </div>
    @endif

    <!-- Section 4: Pengaturan & Sistem -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Pengaturan & Bantuan</span>
            <span class="text-3xs text-slate-400 font-medium">Sistem</span>
        </div>
        <div class="divide-y divide-slate-100">
            <!-- Pengingat & Notifikasi -->
            <a href="{{ route('notifications.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">Notifikasi & Pengingat</p>
                        <p class="text-2xs text-slate-500">Unit belum diambil & sparepart kritis</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </a>

            @if($role === 'owner')
                <!-- Pengaturan Toko -->
                <a href="{{ route('settings.store') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">Pengaturan Toko</p>
                            <p class="text-2xs text-slate-500">Alamat nota, printer thermal, & jam kerja</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                </a>

                <!-- Paket Langganan -->
                <a href="{{ route('subscriptions.index') }}" class="flex items-center justify-between p-3.5 hover:bg-slate-50 active:bg-slate-100 transition-colors">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center shrink-0">
                            <i data-lucide="sparkle" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">Paket Langganan</p>
                            <p class="text-2xs text-slate-500">Status kuota transaksi & upgrade paket</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Tombol Keluar Aplikasi (Logout) -->
    <div class="pt-2">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center justify-center space-x-2 py-3 px-4 rounded-xl bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 active:bg-rose-100 font-semibold text-sm shadow-xs transition-colors"
            >
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Keluar dari Aplikasi</span>
            </button>
        </form>
        <p class="text-center text-3xs text-slate-400 mt-3 font-medium">
            SEMAPON Mobile v1.0.0 • Sistem Manajemen Servis HP
        </p>
    </div>
</div>

