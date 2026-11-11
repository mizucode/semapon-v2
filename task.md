# SEMAPON — Development Task List

> Sistem Manajemen Servis HP Multi-Tenant SaaS  
> Stack: Laravel + Blade + Livewire + Alpine.js + Tailwind CSS + MySQL  
> Architecture: Modular Monolith  
> Design: Mobile First  
> Status: Development Planning

---

# DEVELOPMENT RULES

## General Rules

- [ ] Gunakan Laravel sebagai backend utama.
- [ ] Gunakan Blade sebagai template engine.
- [ ] Gunakan Livewire untuk interaksi reaktif.
- [ ] Gunakan Alpine.js untuk interaksi frontend ringan.
- [ ] Gunakan Tailwind CSS untuk seluruh styling.
- [ ] Gunakan MySQL sebagai database utama.
- [ ] Gunakan ULID sebagai primary key.
- [ ] Gunakan modular monolith.
- [ ] Terapkan multi-tenant isolation menggunakan `store_id`.
- [ ] Terapkan Role-Based Access Control.
- [ ] Gunakan Laravel Policies untuk authorization.
- [ ] Terapkan validasi pada backend.
- [ ] Gunakan database transactions untuk transaksi penting.
- [ ] Gunakan eager loading untuk mencegah N+1 queries.
- [ ] Semua fitur wajib mobile-first.
- [ ] Jangan menyimpan data sensitif dalam log.
- [ ] Jangan menghapus histori transaksi secara permanen.
- [ ] Jangan mempercayai data tenant dari frontend.
- [ ] Semua fitur harus memiliki loading, empty, success, dan error state.

## Coding Standards

- Gunakan PSR-12.
- Gunakan nama class dan method yang deskriptif.
- Pisahkan business logic dari Controller.
- Gunakan Form Request untuk validasi kompleks.
- Gunakan Service Class untuk proses bisnis.
- Gunakan Events dan Jobs untuk pekerjaan asynchronous.
- Gunakan Enum atau constant untuk status.
- Hindari duplikasi business logic.
- Gunakan database index sesuai kebutuhan query.
- Setiap migration harus memiliki relasi dan constraint yang benar.

## Definition of Done

Sebuah task dianggap selesai apabila:

1. Implementasi sesuai requirement.
2. Backend validation tersedia.
3. Authorization tersedia.
4. Tenant isolation sudah diuji.
5. UI responsive.
6. Tidak ada error pada browser dan Laravel log.
7. Feature test tersedia untuk proses penting.
8. Tidak merusak modul yang sudah selesai.

---

# PHASE 01 — PROJECT FOUNDATION

## 1. Project Setup

- [x] Install Laravel versi stabil yang kompatibel dengan PHP environment.
- [x] Setup project repository.
- [x] Setup `.env`.
- [x] Setup koneksi MySQL.
- [x] Setup database.
- [x] Setup timezone aplikasi.
- [x] Setup locale `id`.
- [x] Setup konfigurasi currency IDR.
- [x] Setup logging.
- [x] Setup Laravel storage.
- [x] Setup Redis jika infrastruktur mendukung.
- [x] Setup queue connection.
- [x] Setup scheduler.
- [x] Setup testing environment.

## 2. Frontend Setup

- [x] Install Tailwind CSS.
- [x] Setup Blade layouts.
- [x] Setup komponen UI.
- [x] Setup typography.
- [x] Setup color palette SEMAPON.
- [x] Setup spacing system.
- [x] Setup border radius.
- [x] Setup Lucide icons.
- [x] Setup Alpine.js.
- [x] Setup Livewire.
- [x] Setup toast notifications.
- [x] Setup confirmation dialogs.
- [x] Setup loading indicators.
- [x] Setup empty states.
- [x] Setup error states.

## 3. Main Layout

- [x] Buat layout Super Admin.
- [x] Buat layout Owner.
- [x] Buat layout Admin.
- [x] Buat layout Teknisi.
- [x] Buat layout halaman tracking publik.
- [x] Buat responsive sidebar desktop.
- [x] Buat mobile drawer.
- [x] Buat mobile bottom navigation.
- [x] Buat responsive header.
- [x] Buat user profile dropdown.
- [x] Buat notification dropdown.
- [x] Buat breadcrumbs.
- [x] Buat responsive content container.

### Acceptance Criteria

- Aplikasi dapat dijalankan secara lokal.
- Database terhubung.
- Tailwind CSS berjalan.
- Livewire dan Alpine.js berjalan.
- Layout dapat digunakan di smartphone dan desktop.
- Tidak ada horizontal overflow pada halaman utama mobile.
- Semua komponen UI memiliki konsistensi desain.

---

# PHASE 02 — AUTHENTICATION

## 1. User Management

- [x] Buat migration `users`.
- [x] Buat User Model.
- [x] Setup ULID.
- [x] Setup password hashing.
- [x] Buat halaman registrasi.
- [x] Buat halaman login.
- [x] Buat logout.
- [x] Buat forgot password.
- [x] Buat reset password.
- [x] Buat email verification jika diperlukan.
- [x] Buat profil pengguna.
- [x] Buat perubahan password.
- [x] Buat account status.

## 2. Authentication Security

- [x] Implementasikan session regeneration.
- [x] Implementasikan login throttling.
- [x] Implementasikan password validation.
- [x] Implementasikan session expiration.
- [x] Implementasikan secure cookie.
- [x] Implementasikan logout semua perangkat jika diperlukan.

### Acceptance Criteria

- User dapat registrasi.
- User dapat login dan logout.
- Password tidak pernah disimpan dalam plaintext.
- User tidak dapat mengakses halaman tanpa autentikasi.
- Session aman.
- Password reset bekerja.

---

# PHASE 03 — MULTI-TENANT FOUNDATION

## 1. Store Management

- [x] Buat migration `stores`.
- [x] Buat Store Model.
- [x] Buat migration `store_memberships`.
- [x] Buat relasi Owner dan Store.
- [x] Buat Store Registration Service.
- [x] Buat pembuatan toko setelah registrasi.
- [x] Buat pengaturan profil toko.
- [x] Buat upload logo toko.
- [x] Buat store slug.
- [x] Buat store status.

## 2. Tenant Context

- [x] Buat `TenantContext`.
- [x] Buat `ResolveTenant` middleware.
- [x] Buat mekanisme resolusi tenant dari membership.
- [x] Buat active store context.
- [x] Buat tenant-aware base service.
- [x] Buat tenant-aware query scope.
- [x] Implementasikan store ownership validation.
- [x] Implementasikan tenant-aware file storage.
- [x] Implementasikan tenant-aware cache key.
- [x] Implementasikan tenant-aware queue context.

## 3. Tenant Isolation

- [x] Semua model operasional memiliki `store_id`.
- [x] Semua query operasional dibatasi tenant.
- [x] Semua create operation menggunakan tenant aktif.
- [x] Semua update operation memverifikasi kepemilikan data.
- [x] Semua delete operation memverifikasi tenant.
- [x] Semua foreign key tenant diperiksa.
- [x] Buat cross-tenant access tests.
- [x] Buat tenant isolation middleware tests.

### Acceptance Criteria

- Owner dapat membuat toko.
- Store memiliki identitas unik.
- User hanya dapat mengakses toko yang menjadi keanggotaannya.
- Data toko A tidak dapat diakses oleh toko B.
- Tenant tidak dapat dimanipulasi melalui request frontend.
- File toko tidak dapat diakses lintas tenant.

---

# PHASE 04 — ROLE & PERMISSION

## 1. Role Management

- [x] Buat tabel roles.
- [x] Buat tabel permissions.
- [x] Buat tabel role_permissions.
- [x] Hubungkan role dengan store_memberships.
- [x] Buat Owner role.
- [x] Buat Admin role.
- [x] Buat Teknisi role.
- [x] Buat Super Admin role terpisah.
- [x] Buat permission seeder.
- [x] Buat role seeder.

## 2. Authorization

- [x] Setup Laravel Policies.
- [x] Setup Gates.
- [x] Buat permission middleware.
- [x] Buat role middleware jika diperlukan.
- [x] Batasi akses Owner.
- [x] Batasi akses Admin.
- [x] Batasi akses Teknisi.
- [x] Batasi akses Super Admin.
- [x] Buat authorization tests.

### Acceptance Criteria

- Setiap role memiliki hak akses sesuai ketentuan.
- Teknisi tidak dapat membuka laporan keuangan tanpa izin.
- Admin tidak dapat mengubah konfigurasi langganan.
- Owner dapat mengelola karyawan.
- Super Admin menggunakan jalur autentikasi platform tersendiri.

---

# PHASE 05 — CUSTOMER MANAGEMENT

## 1. Database

- [x] Buat migration `customers`.
- [x] Buat migration `customer_devices`.
- [x] Buat relasi Customer.
- [x] Buat relasi CustomerDevice.
- [x] Buat indeks pencarian pelanggan.

## 2. Customer Features

- [x] Buat halaman daftar pelanggan.
- [x] Buat tambah pelanggan.
- [x] Buat edit pelanggan.
- [x] Buat detail pelanggan.
- [x] Buat pencarian pelanggan.
- [x] Buat filter pelanggan.
- [x] Buat pagination.
- [x] Buat profil perangkat pelanggan.
- [x] Buat riwayat servis pelanggan.
- [x] Buat statistik pelanggan.
- [x] Buat validasi nomor WhatsApp.

### Acceptance Criteria

- Owner/Admin dapat mengelola pelanggan.
- Pelanggan dapat memiliki beberapa perangkat.
- Riwayat servis terhubung dengan perangkat.
- Data pelanggan tidak dapat diakses tenant lain.

---

# PHASE 06 — TECHNICIAN MANAGEMENT

## 1. Database

- [x] Buat migration `technician_profiles`.
- [x] Hubungkan technician dengan users.
- [x] Hubungkan technician dengan store_memberships.
- [x] Buat field expertise.
- [x] Buat field commission.
- [x] Buat technician status.

## 2. Technician Features

- [x] Buat daftar teknisi.
- [x] Buat tambah teknisi.
- [x] Buat edit teknisi.
- [x] Buat nonaktifkan teknisi.
- [x] Buat aktivasi teknisi.
- [x] Buat reset password teknisi.
- [x] Buat detail teknisi.
- [x] Buat daftar order teknisi.
- [x] Buat riwayat pengerjaan.
- [x] Buat statistik performa.
- [x] Buat profil keahlian.
- [x] Buat batas teknisi berdasarkan paket.

## 3. Technician Login

- [x] Teknisi dapat login dengan akun sendiri.
- [x] Teknisi diarahkan ke dashboard teknisi.
- [x] Teknisi hanya melihat pekerjaan sesuai permission.
- [x] Teknisi tidak dapat mengakses toko lain.
- [x] Teknisi yang dinonaktifkan tidak dapat login ke tenant tersebut.

### Acceptance Criteria

- Owner dapat menambahkan lebih dari satu teknisi.
- Setiap teknisi memiliki akun sendiri.
- Satu teknisi dapat ditugaskan ke beberapa order.
- Teknisi hanya dapat melihat pekerjaan sesuai hak akses.
- Penghapusan keanggotaan tidak menghapus histori order.

---

# PHASE 07 — SERVICE CATALOG

## 1. Database

- [x] Buat migration `service_categories`.
- [x] Buat migration `services`.
- [x] Setup harga layanan.
- [x] Setup estimasi pengerjaan.
- [x] Setup garansi default.
- [x] Setup status aktif/nonaktif.

## 2. Service Features

- [x] Buat daftar layanan.
- [x] Buat kategori layanan.
- [x] Buat tambah layanan.
- [x] Buat edit layanan.
- [x] Buat hapus/nonaktifkan layanan.
- [x] Buat pencarian layanan.
- [x] Buat filter kategori.
- [x] Buat harga layanan.
- [x] Buat estimasi waktu.
- [x] Buat pengaturan garansi.

### Acceptance Criteria

- Owner dapat mengelola katalog layanan.
- Layanan dapat dipilih saat membuat order.
- Perubahan harga katalog tidak mengubah transaksi lama.
- Layanan nonaktif tidak dapat digunakan pada order baru.

---

# PHASE 08 — SERVICE ORDER MANAGEMENT

## 1. Database

- [x] Buat migration `service_orders`.
- [x] Buat migration `service_order_items`.
- [x] Buat migration `service_order_technicians`.
- [x] Buat migration `service_order_diagnoses`.
- [x] Buat migration `service_order_histories`.
- [x] Buat migration `service_order_media`.
- [x] Buat migration `service_order_parts`.
- [x] Setup foreign keys.
- [x] Setup composite indexes.
- [x] Setup unique order number per tenant.

## 2. Order Creation

- [x] Buat nomor order otomatis.
- [x] Buat form penerimaan HP.
- [x] Pilih pelanggan.
- [x] Tambah pelanggan baru dari form order.
- [x] Pilih perangkat pelanggan.
- [x] Tambah perangkat baru.
- [x] Input merek HP.
- [x] Input model HP.
- [x] Input IMEI.
- [x] Input keluhan.
- [x] Input kondisi fisik.
- [x] Input kelengkapan perangkat.
- [x] Upload foto kondisi awal.
- [x] Input estimasi selesai.
- [x] Simpan order.

## 3. Technician Assignment

- [x] Buat penugasan teknisi.
- [x] Pilih teknisi dari tenant aktif.
- [x] Dukung beberapa teknisi dalam satu order.
- [x] Catat waktu penugasan.
- [x] Catat pengguna yang menugaskan.
- [x] Dukung pergantian teknisi.
- [x] Simpan histori assignment.

## 4. Diagnosis

- [x] Buat form diagnosa.
- [x] Input kerusakan.
- [x] Input hasil pemeriksaan.
- [x] Tambah layanan.
- [x] Tambah estimasi biaya.
- [x] Upload foto diagnosa.
- [x] Simpan catatan teknisi.

## 5. Customer Approval

- [x] Buat status persetujuan.
- [x] Catat estimasi biaya yang diajukan.
- [x] Catat persetujuan pelanggan.
- [x] Catat waktu persetujuan.
- [x] Catat siapa yang merekam persetujuan.
- [x] Dukung penolakan estimasi.
- [x] Blokir pekerjaan yang membutuhkan persetujuan sebelum disetujui.

## 6. Work Progress

- [x] Buat status pekerjaan.
- [x] Buat update status.
- [x] Buat histori status.
- [x] Buat catatan pengerjaan.
- [x] Buat checklist testing.
- [x] Upload foto hasil pengerjaan.
- [x] Buat proses penyelesaian.
- [x] Buat proses penyerahan HP.
- [x] Buat proses pembatalan order.

## 7. Order Status

Gunakan status:

- `received`
- `checking`
- `waiting_approval`
- `waiting_parts`
- `in_progress`
- `testing`
- `completed`
- `ready_for_pickup`
- `picked_up`
- `cancelled`

- [x] Buat status constants/enum.
- [x] Buat validasi transisi status.
- [x] Buat status history.
- [x] Buat status badge.
- [x] Buat filter status.
- [x] Buat daftar order berdasarkan status.
- [x] Buat pencarian nomor order.
- [x] Buat order detail page.

## 8. Print Documents

- [x] Buat nota penerimaan.
- [x] Buat nota pembayaran.
- [x] Buat format thermal 58mm.
- [x] Buat format thermal 80mm.
- [x] Buat format A4.
- [x] Tampilkan identitas toko.
- [x] Tampilkan informasi perangkat.
- [x] Tampilkan keluhan.
- [x] Tampilkan syarat dan ketentuan servis.

### Acceptance Criteria

- [x] Order dapat dibuat.
- [x] Order memiliki nomor unik per toko.
- [x] Order dapat ditugaskan ke banyak teknisi.
- [x] Teknisi dapat memperbarui progres sesuai izin.
- [x] Histori status tidak dapat dihapus melalui operasi normal.
- [x] Estimasi biaya dan persetujuan tercatat.
- [x] Order dapat dicetak.
- [x] Tidak ada relasi lintas tenant.

---

# PHASE 09 — PRIVATE CUSTOMER TRACKING

## 1. Tracking Token

- [x] Buat migration `service_tracking_tokens`.
- [x] Buat TrackingToken Model.
- [x] Buat secure random token generator.
- [x] Gunakan minimal 256-bit entropy.
- [x] Hash token menggunakan SHA-256.
- [x] Simpan hanya hash token.
- [x] Buat token expiration.
- [x] Buat token revocation.
- [x] Buat regenerate token.
- [x] Buat token lookup service.
- [x] Buat rate limiting.
- [x] Hindari token masuk ke log.

## 2. Public Tracking Page

- [x] Buat public tracking route.
- [x] Buat tracking controller/service.
- [x] Validasi token.
- [x] Validasi expiration.
- [x] Validasi revoked status.
- [x] Ambil order terkait.
- [x] Tampilkan nama toko.
- [x] Tampilkan nomor order.
- [x] Tampilkan informasi HP.
- [x] Tampilkan status servis.
- [x] Tampilkan timeline.
- [x] Tampilkan estimasi selesai.
- [x] Tampilkan kontak WhatsApp toko.
- [x] Buat tampilan mobile-first.

## 3. Tracking Security

- [x] Pastikan tracking tidak memerlukan login.
- [x] Jangan tampilkan PIN perangkat.
- [x] Jangan tampilkan catatan internal.
- [x] Jangan tampilkan harga modal.
- [x] Jangan tampilkan data pelanggan lain.
- [x] Terapkan `noindex`.
- [x] Terapkan `Cache-Control: no-store`.
- [x] Gunakan HTTPS.
- [x] Gunakan rate limiting.
- [x] Uji token yang tidak valid.
- [x] Uji token yang kedaluwarsa.
- [x] Uji token yang dicabut.
- [x] Uji token dari tenant berbeda.

## 4. WhatsApp Link

- [x] Buat tombol salin link tracking.
- [x] Buat tombol WhatsApp.
- [x] Buat template pesan.
- [x] Sertakan nama pelanggan.
- [x] Sertakan nomor order.
- [x] Sertakan link tracking.
- [x] Buat status pengiriman manual.

Pengiriman WhatsApp otomatis melalui API merupakan fitur lanjutan yang memerlukan provider dan konfigurasi tersendiri.

### Acceptance Criteria

- [x] Token memiliki entropy minimal 256 bit.
- [x] Database hanya menyimpan hash.
- [x] Pelanggan dapat membuka link tanpa login.
- [x] Token yang dicabut tidak dapat digunakan.
- [x] Token kedaluwarsa tidak dapat digunakan.
- [x] Halaman tidak membocorkan informasi internal.
- [x] Link tracking dapat dikirim melalui WhatsApp manual.
- [x] Halaman tracking berfungsi pada smartphone.

---

# PHASE 10 — INVENTORY MANAGEMENT

## 1. Database

- [x] Buat migration `product_categories`.
- [x] Buat migration `products`.
- [x] Buat migration `stock_movements`.
- [x] Setup stok minimum.
- [x] Setup SKU.
- [x] Setup barcode.
- [x] Setup unit produk.

## 2. Product Features

- [x] Buat daftar produk.
- [x] Buat tambah produk.
- [x] Buat edit produk.
- [x] Buat detail produk.
- [x] Buat pencarian produk.
- [x] Buat kategori.
- [x] Buat harga modal.
- [x] Buat harga jual.
- [x] Buat stok.
- [x] Buat stok minimum.
- [x] Buat riwayat stok.
- [x] Buat penyesuaian stok.
- [x] Buat notifikasi stok menipis.

## 3. Stock Transaction

- [x] Buat stock-in.
- [x] Buat stock-out.
- [x] Buat stock adjustment.
- [x] Buat stock movement history.
- [x] Buat reference transaction.
- [x] Gunakan database transaction.
- [x] Gunakan `lockForUpdate()`.
- [x] Cegah stok negatif kecuali kebijakan toko mengizinkan.
- [x] Buat mekanisme reversal.

## 4. Service Parts Usage

- [x] Pilih sparepart pada order.
- [x] Input jumlah sparepart.
- [x] Ambil snapshot harga modal.
- [x] Ambil snapshot harga jual.
- [x] Kurangi stok melalui transaksi atomik.
- [x] Hubungkan movement dengan order.
- [x] Buat mekanisme pengembalian sparepart.
- [x] Buat laporan penggunaan sparepart.

### Acceptance Criteria

- [x] Stok masuk tercatat.
- [x] Stok keluar tercatat.
- [x] Penggunaan sparepart terhubung ke order.
- [x] Histori stok tidak dihapus.
- [x] Stok tidak dapat dimanipulasi lintas tenant.
- [x] Transaksi stok aman dari race condition.

---

# PHASE 11 — SUPPLIER & PURCHASE

## 1. Database

- [x] Buat migration `suppliers`.
- [x] Buat migration `purchases`.
- [x] Buat migration `purchase_items`.
- [x] Buat migration `supplier_payments`.

## 2. Supplier Features

- [x] Buat daftar supplier.
- [x] Tambah supplier.
- [x] Edit supplier.
- [x] Detail supplier.
- [x] Riwayat pembelian.
- [x] Total pembelian.
- [x] Hutang supplier.

## 3. Purchase Features

- [x] Buat purchase order jika diperlukan.
- [x] Buat transaksi pembelian.
- [x] Tambah item pembelian.
- [x] Input jumlah.
- [x] Input harga modal.
- [x] Hitung subtotal.
- [x] Hitung total.
- [x] Catat pembayaran.
- [x] Catat sisa hutang.
- [x] Terima barang.
- [x] Tambah stok ketika penerimaan dikonfirmasi.
- [x] Buat histori pembelian.
- [x] Buat pembatalan pembelian dengan reversal stok.

### Acceptance Criteria

- [x] Pembelian terhubung ke supplier.
- [x] Stok bertambah saat barang diterima.
- [x] Pembayaran supplier tercatat terpisah.
- [x] Hutang supplier dapat dihitung.
- [x] Pembatalan tidak menghilangkan histori stok.

---

# PHASE 12 — FINANCE & PAYMENT

## 1. Database

- [x] Buat migration `payments`.
- [x] Buat migration `expenses`.
- [x] Buat migration `cash_transactions`.
- [x] Setup payment status.
- [x] Setup payment method.
- [x] Setup payment reference.

## 2. Payment Features

- [x] Buat pembayaran order.
- [x] Dukung pembayaran DP.
- [x] Dukung pembayaran bertahap.
- [x] Dukung pelunasan.
- [x] Dukung tunai.
- [x] Dukung transfer.
- [x] Dukung QRIS sebagai pencatatan manual.
- [x] Dukung debit.
- [x] Buat riwayat pembayaran.
- [x] Buat nomor pembayaran.
- [x] Buat payment receipt.
- [x] Buat void payment.
- [x] Buat refund record.
- [x] Buat idempotency protection.

## 3. Expense Features

- [x] Buat kategori pengeluaran.
- [x] Buat tambah pengeluaran.
- [x] Buat edit pengeluaran.
- [x] Buat detail pengeluaran.
- [x] Buat histori pengeluaran.
- [x] Buat filter tanggal.
- [x] Buat laporan pengeluaran.

## 4. Financial Summary

- [x] Hitung total pemasukan.
- [x] Hitung total pembayaran valid.
- [x] Hitung total pengeluaran.
- [x] Hitung total piutang.
- [x] Hitung hutang supplier.
- [x] Hitung biaya sparepart.
- [x] Hitung laba kotor.
- [x] Hitung estimasi laba bersih.
- [x] Buat ringkasan dashboard.

### Acceptance Criteria

- [x] Pembayaran DP dan pelunasan bekerja.
- [x] Pembayaran tidak dapat terduplikasi.
- [x] Refund memiliki histori.
- [x] Pengeluaran tercatat.
- [x] Saldo dan piutang dapat direkonsiliasi.
- [x] Transaksi keuangan tidak dapat dihapus secara sembarangan.

---

# PHASE 13 — REMINDERS & NOTIFICATIONS

- [x] Buat migration `reminders`.
- [x] Buat sistem notifikasi internal.
- [x] Buat pengingat order terlambat.
- [x] Buat pengingat HP selesai belum diambil.
- [x] Buat pengingat piutang.
- [x] Buat pengingat garansi.
- [x] Buat pengingat stok menipis.
- [x] Buat notifikasi penugasan teknisi.
- [x] Buat notifikasi perubahan order.
- [x] Buat notification preferences.
- [x] Setup Laravel Scheduler.
- [x] Setup queue jobs.

### Acceptance Criteria

- [x] Pengingat muncul sesuai kondisi.
- [x] Notifikasi hanya diterima pengguna yang berhak.
- [x] Tidak ada notifikasi lintas tenant.
- [x] Job gagal dapat dipantau dan diproses ulang.

---

# PHASE 14 — REPORTING & ANALYTICS

## 1. Service Reports

- [x] Laporan jumlah order.
- [x] Laporan status order.
- [x] Laporan servis selesai.
- [x] Laporan servis batal.
- [x] Laporan waktu pengerjaan.
- [x] Laporan layanan terpopuler.

## 2. Technician Reports

- [x] Order per teknisi.
- [x] Produktivitas teknisi.
- [x] Durasi pengerjaan.
- [x] Komisi teknisi.
- [x] Riwayat pekerjaan.

## 3. Inventory Reports

- [x] Stok tersedia.
- [x] Stok menipis.
- [x] Produk paling sering digunakan.
- [x] Nilai persediaan.
- [x] Histori stok.

## 4. Financial Reports

- [x] Pendapatan harian.
- [x] Pendapatan bulanan.
- [x] Pengeluaran.
- [x] Laba kotor.
- [x] Estimasi laba bersih.
- [x] Piutang.
- [x] Hutang supplier.

## 5. Export

- [x] Export PDF.
- [x] Export Excel.
- [x] Filter tanggal.
- [x] Filter teknisi.
- [x] Filter layanan.
- [x] Filter status.
- [x] Pastikan export tenant-scoped.

### Acceptance Criteria

- [x] Angka laporan sesuai transaksi sumber.
- [x] Filter tanggal akurat.
- [x] Laporan tidak mencampur tenant.
- [x] Export sesuai data yang ditampilkan.
- [x] Perhitungan keuangan dapat ditelusuri.

---

# PHASE 15 — SUBSCRIPTION MANAGEMENT

## 1. Database

- [x] Buat migration `plans`.
- [x] Buat migration `subscriptions`.
- [x] Buat migration `subscription_payments`.
- [x] Setup trial.
- [x] Setup subscription status.
- [x] Setup plan features.

## 2. Plan Management

- [x] Buat Starter plan.
- [x] Buat Business plan.
- [x] Buat Pro plan.
- [x] Buat pengaturan harga.
- [x] Buat batas teknisi.
- [x] Buat batas fitur.
- [x] Buat status paket.
- [x] Buat perubahan paket.

## 3. Subscription Features

- [x] Buat halaman langganan Owner.
- [x] Buat informasi paket aktif.
- [x] Buat tanggal kedaluwarsa.
- [x] Buat riwayat pembayaran.
- [x] Buat trial countdown.
- [x] Buat subscription expiration.
- [x] Buat notifikasi masa aktif.
- [x] Buat renewal.
- [x] Buat grace period jika ditetapkan.
- [x] Buat feature limits enforcement.

## 4. Payment Integration

- [x] Buat payment abstraction.
- [x] Buat pencatatan pembayaran manual.
- [x] Buat interface provider.
- [x] Integrasikan payment gateway hanya setelah provider dipilih.
- [x] Validasi webhook signature.
- [x] Buat idempotency.
- [x] Buat reconciliation.
- [x] Buat payment audit.

### Acceptance Criteria

- [x] Paket dapat diatur Super Admin.
- [x] Batas teknisi diterapkan di backend.
- [x] Paket expired mengikuti kebijakan akses.
- [x] Data tenant tidak dihapus saat langganan berakhir.
- [x] Pembayaran langganan tidak dapat dimanipulasi melalui frontend.
- [x] Integrasi gateway tidak dianggap aktif sebelum benar-benar dikonfigurasi dan diuji.

---

# PHASE 16 — SUPER ADMIN DASHBOARD

## 1. Platform Overview

- [x] Buat dashboard Super Admin.
- [x] Total tenant.
- [x] Tenant aktif.
- [x] Tenant trial.
- [x] Tenant expired.
- [x] Total users.
- [x] Total teknisi.
- [x] Total order.
- [x] Total pelanggan.
- [x] Total subscription aktif.

## 2. Tenant Monitoring

- [x] Daftar tenant.
- [x] Detail tenant.
- [x] Owner information.
- [x] Status tenant.
- [x] Plan information.
- [x] Registration date.
- [x] Usage statistics.
- [x] Suspend tenant.
- [x] Reactivate tenant.
- [x] Tenant audit history.

## 3. Subscription Monitoring

- [x] Daftar paket.
- [x] Tambah paket.
- [x] Edit paket.
- [x] Nonaktifkan paket.
- [x] Daftar subscription.
- [x] Riwayat pembayaran.
- [x] Status pembayaran.
- [x] Trial management.

## 4. Platform Analytics

- [x] Daily active users.
- [x] Monthly active users.
- [x] Daily tenant registrations.
- [x] Total request.
- [x] Request per tenant secara agregat.
- [x] Most accessed pages.
- [x] Platform resource usage.
- [x] Error rate.
- [x] Response time.

## 5. Platform Health

- [x] Database health.
- [x] Redis health jika tersedia.
- [x] Queue health.
- [x] Failed jobs.
- [x] Storage usage.
- [x] Disk usage.
- [x] Application uptime.
- [x] Backup monitoring.
- [x] Error monitoring.

## 6. Super Admin Security

- [x] Separate Super Admin authorization.
- [x] Audit semua perubahan platform.
- [x] Audit suspend/reactivate tenant.
- [x] Audit perubahan paket.
- [x] Batasi akses data tenant.
- [x] Implementasikan support access jika dibutuhkan.
- [x] Buat expiration untuk support access.
- [x] Catat alasan support access.

### Acceptance Criteria

- [x] Super Admin dapat memantau platform.
- [x] Super Admin tidak mengelola operasional toko.
- [x] Dashboard menggunakan data agregat.
- [x] Aktivitas sensitif tercatat.
- [x] Monitoring tidak menyebabkan query berat pada database operasional.

---

# PHASE 17 — TESTING & SECURITY AUDIT

## 1. Functional Testing

- [x] Registration tests.
- [x] Login tests.
- [x] Tenant creation tests.
- [x] Membership tests.
- [x] Role tests.
- [x] Customer tests.
- [x] Technician tests.
- [x] Service tests.
- [x] Order tests.
- [x] Tracking tests.
- [x] Inventory tests.
- [x] Purchase tests.
- [x] Payment tests.
- [x] Subscription tests.
- [x] Super Admin tests.

## 2. Tenant Security Testing

- [x] Cross-tenant customer access.
- [x] Cross-tenant order access.
- [x] Cross-tenant technician assignment.
- [x] Cross-tenant product usage.
- [x] Cross-tenant payment access.
- [x] Cross-tenant file access.
- [x] Cross-tenant report access.
- [x] Cross-tenant tracking validation.

## 3. Transaction Testing

- [x] Concurrent stock deduction.
- [x] Duplicate payment prevention.
- [x] Payment rollback.
- [x] Stock movement rollback.
- [x] Purchase reversal.
- [x] Order cancellation.
- [x] Refund process.

## 4. Security Testing

- [x] Authorization bypass.
- [x] IDOR.
- [x] CSRF.
- [x] XSS.
- [x] SQL injection.
- [x] File upload validation.
- [x] Rate limiting.
- [x] Tracking token security.
- [x] Session security.
- [x] Password security.

## 5. Responsive Testing

- [x] Mobile 360px.
- [x] Mobile 390px.
- [x] Mobile 430px.
- [x] Tablet.
- [x] Desktop.
- [x] Touch target.
- [x] Form usability.
- [x] Table responsiveness.
- [x] Modal responsiveness.
- [x] Navigation responsiveness.

### Acceptance Criteria

- [x] Tidak ada akses lintas tenant.
- [x] Semua proses finansial dan stok konsisten.
- [x] Tracking aman.
- [x] UI berjalan pada perangkat mobile.
- [x] Tidak ada critical security issue yang diketahui.

---

# PHASE 18 — PRODUCTION DEPLOYMENT

## 1. Server

- [x] Setup production server.
- [x] Setup PHP.
- [x] Setup Composer.
- [x] Setup MySQL.
- [x] Setup Nginx.
- [x] Setup SSL.
- [x] Setup Redis jika digunakan.
- [x] Setup storage.
- [x] Setup queue worker.
- [x] Setup scheduler.

## 2. Deployment

- [x] Setup production `.env`.
- [x] Setup APP_KEY.
- [x] Setup database credentials.
- [x] Setup storage permissions.
- [x] Setup queue.
- [x] Setup cache.
- [x] Setup optimized autoload.
- [x] Setup database migration.
- [x] Setup backup.
- [x] Setup monitoring.

## 3. Final Validation

- [x] Test registrasi.
- [x] Test login.
- [x] Test pembuatan toko.
- [x] Test penambahan teknisi.
- [x] Test order servis.
- [x] Test tracking pelanggan.
- [x] Test stok.
- [x] Test pembayaran.
- [x] Test laporan.
- [x] Test Super Admin.
- [x] Test tenant isolation.
- [x] Test backup restoration.

### Acceptance Criteria

- [x] Aplikasi berjalan melalui HTTPS.
- [x] Queue dan scheduler berjalan.
- [x] Backup dapat dipulihkan.
- [x] Seluruh modul inti berfungsi.
- [x] Tidak ada error kritis pada production.

---

# FINAL MVP CHECKLIST

MVP pertama dapat diluncurkan untuk uji coba setelah seluruh item berikut selesai:

## Authentication & SaaS
- [x] Registrasi Owner.
- [x] Login.
- [x] Pembuatan toko.
- [x] Multi-tenant isolation.
- [x] Role & permission.

## Service Operations
- [x] Pelanggan.
- [x] Perangkat pelanggan.
- [x] Teknisi.
- [x] Layanan.
- [x] Order servis.
- [x] Penugasan teknisi.
- [x] Diagnosa.
- [x] Status pekerjaan.
- [x] Histori order.

## Customer Experience
- [x] Tracking privat.
- [x] Token SHA-256.
- [x] Link tracking.
- [x] WhatsApp manual.
- [x] Nota penerimaan.

## Basic Business
- [x] Produk.
- [x] Sparepart.
- [x] Supplier.
- [x] Stok.
- [x] Pembelian.
- [x] Pembayaran.
- [x] Pengeluaran.
- [x] Dashboard.

## Platform
- [x] Super Admin.
- [x] Tenant monitoring.
- [x] User monitoring.
- [x] Subscription dasar.
- [x] Platform health.

---

# DEVELOPMENT PRIORITY

Urutan implementasi yang tidak boleh dibalik:

1. Project Foundation.
2. Authentication.
3. Multi-Tenant Foundation.
4. Role & Permission.
5. Customer Management.
6. Technician Management.
7. Service Catalog.
8. Service Order Management.
9. Private Customer Tracking.
10. Inventory Management.
11. Supplier & Purchase.
12. Finance & Payment.
13. Reminders & Notifications.
14. Reporting & Analytics.
15. Subscription Management.
16. Super Admin Dashboard.
17. Testing & Security Audit.
18. Production Deployment.

**Jangan mulai dari dashboard yang penuh grafik. Selesaikan proses bisnis terlebih dahulu.**

---

# PROJECT COMPLETION GOAL

SEMAPON dinyatakan siap untuk MVP ketika pemilik toko dapat:

1. Mendaftar dan membuat toko.
2. Menambahkan banyak teknisi.
3. Menerima HP dari pelanggan.
4. Membuat order servis.
5. Menugaskan teknisi.
6. Memantau pengerjaan.
7. Mengelola sparepart.
8. Mencatat pembayaran.
9. Mengirim link tracking.
10. Melihat status dan laporan usaha.

Pelanggan dapat membuka link tracking tanpa login dan mengetahui progres perbaikan perangkatnya.

Super Admin dapat memantau tenant, pengguna, langganan, trafik, dan kesehatan platform tanpa mencampuri operasional toko.

**END OF TASK LIST**