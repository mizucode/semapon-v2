<x-layouts.base title="SEMAPON — Sistem Manajemen Servis HP SaaS Terpadu">
    <div class="min-h-screen bg-slate-50 flex flex-col selection:bg-primary-100 selection:text-primary-800">
        <!-- Top Navbar -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-20">
                    <!-- Brand -->
                    <a href="{{ route('home') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-600 flex items-center justify-center text-white shadow-xs">
                            <i data-lucide="smartphone" class="w-6 h-6"></i>
                        </div>
                        <span class="font-bold text-xl text-slate-900 tracking-tight block">SEMAPON</span>
                    </a>

                    <!-- Nav Links -->
                    <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-600">
                        <a href="#fitur" class="hover:text-primary-600 transition-colors">Fitur Unggulan</a>
                        <a href="#lacak" class="hover:text-primary-600 transition-colors">Cek Status Servis</a>
                        <a href="#harga" class="hover:text-primary-600 transition-colors">Paket & Harga</a>
                    </nav>

                    <!-- Auth Action Buttons -->
                    <div class="flex items-center space-x-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 mr-1.5"></i>
                                Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2.5 text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                                Coba Gratis 14 Hari
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Hero Section -->
            <section class="relative pt-12 pb-20 sm:pt-20 sm:pb-28 overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                    <!-- Badge -->
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200/60 mb-6">
                        <span class="flex h-2 w-2 rounded-full bg-primary-600 mr-2"></span>
                        Sistem Manajemen Servis Smartphone Multi-Tenant #1 Indonesia
                    </div>

                    <!-- Headline -->
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight max-w-4xl mx-auto leading-tight sm:leading-tight">
                        Kelola Usaha Servis HP Lebih <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-indigo-600">Cepat, Rapi, & Transparan</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="mt-6 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                        Aplikasi cloud terpadu untuk pencatatan penerimaan unit, diagnosa teknisi, kontrol sparepart otomatis, cetak nota thermal kasir, hingga link tracking perbaikan real-time ke WhatsApp pelanggan.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-primary-500/20 transition-all hover:-translate-y-0.5">
                            <i data-lucide="store" class="w-4 h-4 mr-2"></i>
                            Daftarkan Toko Anda Gratis
                        </a>
                        <a href="#lacak" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-sm font-semibold rounded-xl shadow-2xs transition-colors">
                            <i data-lucide="search" class="w-4 h-4 mr-2 text-slate-400"></i>
                            Cek Status Servis Pelanggan
                        </a>
                    </div>
                </div>
            </section>

            <!-- Public Tracking Lookup Widget -->
            <section id="lacak" class="py-12 bg-white border-y border-slate-200/80">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div
                        x-data="{
                            token: '',
                            track() {
                                if (!this.token.trim()) return;
                                window.location.href = '/track/' + encodeURIComponent(this.token.trim());
                            }
                        }"
                        class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 sm:p-10 shadow-xl text-white relative overflow-hidden"
                    >
                        <!-- Background Glow Accent -->
                        <div class="absolute -top-24 -right-24 w-72 h-72 bg-primary-500/20 rounded-full blur-3xl pointer-events-none"></div>

                        <div class="relative z-10 text-center sm:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-white/10 text-primary-300 text-xs font-semibold mb-3">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                Portal Mandiri Pelanggan
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Lacak Status Servis Smartphone Anda</h2>
                            <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                                Pantau progres perbaikan unit HP Anda secara transparan tanpa perlu login. Cukup masukkan token tracking yang tercantum pada nota servis Anda.
                            </p>

                            <!-- Form -->
                            <form @submit.prevent="track" class="mt-6 flex flex-col sm:flex-row gap-3">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="key" class="w-5 h-5"></i>
                                    </div>
                                    <input
                                        type="text"
                                        x-model="token"
                                        placeholder="Tempel atau ketik kode token (Contoh: e7a9f...)"
                                        class="w-full pl-11 pr-4 py-3 bg-white/10 border border-white/20 text-white placeholder-slate-400 text-xs sm:text-sm rounded-xl focus:outline-hidden focus:ring-2 focus:ring-primary-400 focus:border-transparent transition-all"
                                        required
                                    >
                                </div>
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition-colors shrink-0"
                                >
                                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                                    Lacak Sekarang
                                </button>
                            </form>
                            <p class="text-2xs text-slate-400 mt-2.5">
                                *Token tracking dienkripsi aman 256-bit dan tercetak otomatis pada nota servis atau pesan WhatsApp toko.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="fitur" class="py-16 sm:py-24 bg-slate-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <h2 class="text-xs sm:text-sm font-bold text-primary-600 uppercase tracking-wider">Fitur Komprehensif</h2>
                        <p class="mt-2 text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Semua Kebutuhan Toko Servis HP dalam Satu Tempat</p>
                        <p class="mt-3 text-sm text-slate-500">Dirancang khusus untuk alur kerja teknisi dan pemilik konter servis smartphone di Indonesia.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                        <!-- Feature 1 -->
                        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs hover:border-primary-200 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center mb-5">
                                <i data-lucide="clipboard-check" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Penerimaan & Order Servis Terkini</h3>
                            <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                                Catat keluhan, kondisi fisik, passcode, dan kelengkapan unit secara detail. Cetak nota thermal kasir 58mm/80mm atau format dokumen A4.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs hover:border-primary-200 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5">
                                <i data-lucide="globe-2" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Tracking Mandiri Pelanggan</h3>
                            <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                                Pelanggan dapat membuka status pengerjaan secara privat tanpa login, menyetujui estimasi biaya, dan terhubung langsung ke WhatsApp toko.
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs hover:border-primary-200 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-5">
                                <i data-lucide="cpu" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Kontrol Sparepart Otomatis</h3>
                            <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                                Stok LCD, baterai, IC, dan fleksibel berkurang otomatis saat dipasang ke unit pelanggan. Tersedia alert jika stok di bawah batas minimal.
                            </p>
                        </div>

                        <!-- Feature 4 -->
                        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs hover:border-primary-200 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5">
                                <i data-lucide="user-cog" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Manajemen Teknisi & Komisi</h3>
                            <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                                Penugasan reparasi ke teknisi tertentu dengan pembagian komisi otomatis sesuai jenis layanan atau kesepakatan persentase.
                            </p>
                        </div>

                        <!-- Feature 5 -->
                        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs hover:border-primary-200 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-5">
                                <i data-lucide="wallet" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Buku Kas & Keuangan Lengkap</h3>
                            <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                                Pencatatan DP, pelunasan kasir, pengeluaran operasional toko, pencatatan hutang supplier, serta rekonsiliasi saldo kas real-time.
                            </p>
                        </div>

                        <!-- Feature 6 -->
                        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs hover:border-primary-200 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-5">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Multi-Tenant Terisolasi Aman</h3>
                            <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                                Data pelanggan, keuangan, dan daftar stok toko Anda terisolasi 100% dan tidak dapat diakses atau diintip oleh toko servis lainnya.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pricing Section -->
            <section id="harga" class="py-16 sm:py-24 bg-white border-t border-slate-200/80">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <h2 class="text-xs sm:text-sm font-bold text-primary-600 uppercase tracking-wider">Paket Langganan</h2>
                        <p class="mt-2 text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Investasi Terjangkau untuk Pertumbuhan Usaha</p>
                        <p class="mt-3 text-sm text-slate-500">Mulai dari masa percobaan gratis 14 hari tanpa kartu kredit. Upgrade kapan saja.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto items-stretch">
                        <!-- Starter Plan -->
                        <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200 flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Starter</h3>
                                <p class="text-xs text-slate-500 mt-1">Cocok untuk toko mandiri dengan 1 teknisi.</p>
                                <div class="mt-4 flex items-baseline gap-1">
                                    <span class="text-3xl font-extrabold text-slate-900">Rp 29.000</span>
                                    <span class="text-xs text-slate-400">/ bulan</span>
                                </div>
                                <ul class="mt-6 space-y-3 text-xs text-slate-600">
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Batas 1 Teknisi Aktif</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Maksimal 50 Order Servis / bulan</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Cetak Nota Kasir Thermal & A4</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Link Tracking Publik Pelanggan</span>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{ route('register') }}" class="mt-8 block w-full py-2.5 px-4 text-center text-xs font-semibold rounded-xl bg-white text-slate-800 border border-slate-300 hover:bg-slate-100 transition-colors">
                                Mulai Coba Gratis
                            </a>
                        </div>

                        <!-- Business Plan (Recommended) -->
                        <div class="bg-white rounded-2xl p-6 sm:p-8 border-2 border-primary-600 shadow-xl flex flex-col justify-between relative">
                            <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 bg-primary-600 text-white text-3xs font-bold uppercase tracking-wider rounded-full shadow-xs">
                                Paling Populer
                            </span>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Business</h3>
                                <p class="text-xs text-slate-500 mt-1">Solusi ideal untuk konter servis berkembang.</p>
                                <div class="mt-4 flex items-baseline gap-1">
                                    <span class="text-3xl font-extrabold text-slate-900">Rp 59.000</span>
                                    <span class="text-xs text-slate-400">/ bulan</span>
                                </div>
                                <ul class="mt-6 space-y-3 text-xs text-slate-600">
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span class="font-semibold text-slate-800">Hingga 3 Teknisi Aktif</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span class="font-semibold text-slate-800">Unlimited Order Servis</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Perhitungan Komisi Teknisi</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Ekspor Laporan & Buku Kas</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Pengendalian Stok Sparepart</span>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{ route('register') }}" class="mt-8 block w-full py-2.5 px-4 text-center text-xs font-semibold rounded-xl bg-primary-600 hover:bg-primary-700 text-white shadow-xs transition-colors">
                                Pilih Paket Business
                            </a>
                        </div>

                        <!-- Pro Plan -->
                        <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200 flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Pro</h3>
                                <p class="text-xs text-slate-500 mt-1">Kapasitas maksimal untuk pusat servis besar.</p>
                                <div class="mt-4 flex items-baseline gap-1">
                                    <span class="text-3xl font-extrabold text-slate-900">Rp 99.000</span>
                                    <span class="text-xs text-slate-400">/ bulan</span>
                                </div>
                                <ul class="mt-6 space-y-3 text-xs text-slate-600">
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span class="font-semibold text-slate-800">Hingga 10 Teknisi Aktif</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Unlimited Order Servis</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Prioritas Notifikasi & Reminder</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>Fitur Lengkap Seluruh Modul</span>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{ route('register') }}" class="mt-8 block w-full py-2.5 px-4 text-center text-xs font-semibold rounded-xl bg-white text-slate-800 border border-slate-300 hover:bg-slate-100 transition-colors">
                                Mulai Coba Gratis
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200/80 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-lg bg-primary-600 flex items-center justify-center text-white">
                        <i data-lucide="smartphone" class="w-4 h-4"></i>
                    </div>
                    <span class="font-bold text-sm text-slate-900">SEMAPON</span>
                    <span class="text-2xs text-slate-400">© {{ date('Y') }} SEMAPON SaaS Indonesia. All rights reserved.</span>
                </div>
                <div class="flex items-center space-x-6 text-xs text-slate-500">
                    <a href="{{ route('login') }}" class="hover:text-primary-600">Masuk Akun</a>
                    <a href="{{ route('register') }}" class="hover:text-primary-600">Daftar Toko</a>
                    <a href="#lacak" class="hover:text-primary-600">Lacak Servis</a>
                </div>
            </div>
        </footer>
    </div>
</x-layouts.base>
