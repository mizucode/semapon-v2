# PRODUCT REQUIREMENTS DOCUMENT (PRD)
# SEMAPON — Sistem Manajemen Servis HP Multi-Tenant SaaS

**Versi:** 1.0  
**Status:** Draft — Product Foundation  
**Jenis Produk:** Multi-Tenant SaaS  
**Target Pasar:** Pemilik konter HP dan usaha reparasi smartphone di Indonesia  
**Platform:** Web Application (Mobile-First Responsive)  
**Domain Produk:** SEMAPON  
**Bahasa Utama:** Bahasa Indonesia  

---

# 1. PRODUCT OVERVIEW

## 1.1 Deskripsi Produk

SEMAPON adalah aplikasi berbasis SaaS (Software as a Service) untuk membantu pemilik usaha servis HP mengelola seluruh kegiatan operasional toko dalam satu platform.

Aplikasi dirancang dengan konsep menyerupai BengkelApp, tetapi dikhususkan untuk kebutuhan bisnis reparasi smartphone.

SEMAPON memungkinkan pemilik toko untuk:
- Mengelola pelanggan dan riwayat servis.
- Mengelola banyak teknisi dalam satu toko.
- Membuat dan membagikan order servis kepada teknisi.
- Memantau status pengerjaan servis.
- Mengelola stok sparepart dan produk.
- Mengelola supplier dan pembelian.
- Mengelola pemasukan, pengeluaran, dan piutang.
- Mengirimkan link tracking servis kepada pelanggan.
- Memantau performa teknisi dan keuntungan usaha.
- Mengelola operasional toko melalui smartphone maupun desktop.

SEMAPON menggunakan arsitektur Multi-Tenant sehingga banyak toko dapat menggunakan satu aplikasi tanpa saling mengakses data milik toko lainnya.

## 1.2 Visi Produk

Menjadi platform manajemen usaha servis HP yang membantu pemilik konter mengelola bisnis secara profesional, efisien, dan terukur.

## 1.3 Misi Produk

1. Menyederhanakan proses penerimaan dan pengerjaan servis HP.
2. Memudahkan pemilik toko mengelola banyak teknisi.
3. Memberikan transparansi progres servis kepada pelanggan.
4. Membantu pengelolaan sparepart dan keuangan.
5. Menyediakan laporan bisnis yang mudah dipahami.
6. Menghadirkan software servis HP yang terjangkau untuk UMKM Indonesia.

## 1.4 Target Pengguna

| Pengguna | Kebutuhan |
|---|---|
| Pemilik konter HP | Mengelola seluruh operasional toko |
| Pemilik usaha servis HP | Mengontrol order dan keuntungan |
| Admin / Frontdesk | Menerima HP dan membuat order |
| Teknisi HP | Mengerjakan servis dan memperbarui progres |
| Pelanggan | Memantau status servis secara online |
| Super Admin SEMAPON | Monitoring keseluruhan platform |

---

# 2. BUSINESS MODEL

## 2.1 Model SaaS

SEMAPON menggunakan model langganan bulanan dan tahunan.

Setiap toko yang mendaftar menjadi satu tenant dengan data operasional terpisah.

Ketentuan:
- Satu akun pemilik dapat membuat dan memiliki satu toko.
- Satu toko dapat memiliki banyak akun karyawan.
- Pemilik dapat menambahkan teknisi sesuai batas paket.
- Setiap toko memiliki konfigurasi dan identitas sendiri.
- Setiap toko dapat berlangganan paket yang tersedia.
- Super Admin mengelola paket dan status langganan.

## 2.2 Rencana Paket Langganan

Harga berikut merupakan rancangan awal untuk validasi pasar.

| Fitur | Starter | Business | Pro |
|---|---:|---:|---:|
| Harga/bulan | Rp29.000 | Rp59.000 | Rp99.000 |
| Jumlah teknisi | 1 | 3 | 10 |
| Order servis | Terbatas | Unlimited | Unlimited |
| Pelanggan | Ya | Ya | Ya |
| Tracking servis | Ya | Ya | Ya |
| Produk & sparepart | Ya | Ya | Ya |
| Supplier | Ya | Ya | Ya |
| Laporan | Dasar | Lengkap | Lengkap |
| Keuangan | Dasar | Lengkap | Lengkap |
| Komisi teknisi | - | Ya | Ya |
| Export laporan | - | Ya | Ya |

Catatan:
- Batas paket harus dapat dikonfigurasi oleh Super Admin.
- Jumlah teknisi adalah contoh batas akun teknisi aktif.
- Paket dapat diubah tanpa mengubah data operasional toko.
- Sistem harus menyediakan periode trial yang dapat dikonfigurasi.

---

# 3. USER ROLE & PERMISSION

SEMAPON memiliki empat jenis akses utama.

## 3.1 Super Admin

Super Admin adalah pengelola utama platform SEMAPON.

Tugas:
- Monitoring jumlah tenant.
- Monitoring jumlah pengguna.
- Monitoring trafik aplikasi.
- Monitoring aktivitas platform.
- Mengelola paket langganan.
- Mengelola harga langganan.
- Memantau status pembayaran langganan.
- Mengelola pengguna dan status akun.
- Melihat statistik penggunaan sistem.
- Monitoring error dan performa aplikasi.
- Mengelola pengumuman platform.
- Mengelola konfigurasi global.

Batasan:
- Tidak ikut mengelola order servis toko.
- Tidak mengubah transaksi toko.
- Tidak mengelola teknisi toko.
- Tidak memiliki akses otomatis ke data pelanggan toko.
- Tidak dapat melihat isi operasional toko tanpa mekanisme akses bantuan yang sah dan tercatat.

Jika diperlukan akses bantuan teknis ke tenant, gunakan mekanisme Support Access yang memerlukan otorisasi, memiliki batas waktu, dan mencatat seluruh aktivitas.

## 3.2 Owner (Pemilik Toko)

Owner memiliki akses penuh terhadap operasional tokonya.

Hak akses:
- Dashboard.
- Pelanggan.
- Produk dan sparepart.
- Supplier.
- Pembelian.
- Layanan servis.
- Order servis.
- Teknisi.
- Keuangan.
- Laporan.
- Pengingat.
- Pengaturan toko.
- Manajemen karyawan.
- Pengaturan langganan.

Owner dapat menambahkan beberapa akun teknisi dan karyawan sesuai paket.

## 3.3 Admin / Frontdesk

Admin membantu pemilik dalam operasional sehari-hari.

Hak akses:
- Mengelola pelanggan.
- Membuat order servis.
- Memperbarui informasi order.
- Mengelola penerimaan HP.
- Mengelola pembayaran sesuai izin.
- Melihat stok sparepart.
- Mengirim link tracking kepada pelanggan.
- Melihat daftar teknisi.

Admin tidak dapat:
- Mengubah paket langganan.
- Menghapus toko.
- Mengelola Super Admin.
- Mengakses pengaturan sensitif pemilik.

## 3.4 Teknisi

Teknisi merupakan karyawan yang melakukan pekerjaan reparasi HP.

Hak akses:
- Melihat order yang ditugaskan kepadanya.
- Melihat detail perangkat.
- Melihat keluhan pelanggan.
- Mengisi hasil diagnosa.
- Mengisi catatan pengerjaan.
- Memperbarui status pekerjaan sesuai izin.
- Meminta sparepart.
- Mengunggah foto sebelum dan sesudah servis.
- Mengisi hasil testing.
- Menandai pekerjaan selesai untuk pemeriksaan akhir.

Teknisi tidak dapat:
- Melihat seluruh laporan keuangan.
- Mengubah harga layanan.
- Mengelola supplier.
- Mengelola pengguna.
- Menghapus transaksi.
- Melihat order teknisi lain kecuali diberikan izin.

---

# 4. MULTI-TENANT ARCHITECTURE

## 4.1 Konsep Tenant

Setiap toko yang mendaftar akan memiliki identitas tenant tersendiri.

Contoh:

- Toko A: Konter Jaya
- Toko B: Servis HP Mandiri
- Toko C: Maju Cell

Ketiganya menggunakan aplikasi SEMAPON yang sama.

Namun, data setiap toko wajib terisolasi.

## 4.2 Data Isolation

Seluruh data operasional harus memiliki relasi tenant/store.

Contoh:
- Pelanggan.
- Order servis.
- Teknisi.
- Produk.
- Supplier.
- Pembelian.
- Pengeluaran.
- Transaksi.
- Pengingat.

Aturan:
1. Pengguna hanya dapat mengakses tenant tempat mereka menjadi anggota.
2. Setiap query data operasional wajib dibatasi dengan tenant_id/store_id.
3. Validasi kepemilikan data dilakukan pada backend.
4. Tidak boleh mengandalkan penyembunyian menu frontend sebagai mekanisme keamanan.
5. Akses lintas tenant harus ditolak.
6. File upload setiap tenant harus memiliki pemisahan direktori.
7. Queue job dan scheduled task harus mempertahankan konteks tenant.
8. Cache harus menggunakan namespace tenant.

**Rekomendasi arsitektur:** gunakan shared database dengan shared schema dan kolom `store_id` sebagai tenant identifier pada tabel operasional.

Pendekatan ini lebih sederhana dan ekonomis untuk tahap awal SaaS UMKM dibanding database terpisah untuk setiap toko.

---

# 5. CORE MODULES

# 5.1 Dashboard

Dashboard merupakan halaman utama Owner setelah login.

Informasi yang ditampilkan:

### Ringkasan Pendapatan
- Pendapatan hari ini.
- Pendapatan bulan ini.
- Total pembayaran diterima.
- Total piutang.

### Ringkasan Order
- Total order.
- Order pending.
- Order sedang dikerjakan.
- Order menunggu sparepart.
- Order selesai.
- Order sudah diambil.

### Ringkasan Inventaris
- Total produk.
- Stok menipis.
- Stok habis.
- Nilai persediaan.

### Ringkasan Teknisi
- Total teknisi aktif.
- Teknisi sedang mengerjakan servis.
- Jumlah order per teknisi.
- Performa penyelesaian pekerjaan.

### Informasi Tambahan
- Order terbaru.
- Aktivitas terbaru.
- Pengingat.
- Grafik pendapatan.
- Grafik jumlah order.

Dashboard harus responsif dan memprioritaskan informasi penting pada tampilan smartphone.

# 5.2 Pelanggan

Digunakan untuk menyimpan data pelanggan.

Data:
- Nama pelanggan.
- Nomor WhatsApp.
- Nomor telepon alternatif.
- Alamat.
- Email (opsional).
- Catatan.
- Tanggal registrasi.

Fitur:
- Tambah pelanggan.
- Edit pelanggan.
- Hapus pelanggan sesuai aturan relasi transaksi.
- Pencarian pelanggan.
- Riwayat servis.
- Total transaksi.
- Total pengeluaran pelanggan.
- Daftar perangkat yang pernah diservis.

Nomor WhatsApp dapat digunakan sebagai identitas pencarian, tetapi tidak boleh menjadi unique identifier global karena pelanggan yang sama bisa tercatat pada toko berbeda.

# 5.3 Produk & Sparepart

Digunakan untuk mengelola komponen yang digunakan dalam proses servis.

Kategori produk:
- LCD.
- Baterai.
- Flexible.
- IC.
- Konektor charger.
- Kamera.
- Speaker.
- Mikrofon.
- Komponen motherboard.
- Aksesori HP.
- Lain-lain.

Data produk:
- Nama produk.
- SKU.
- Barcode.
- Kategori.
- Merek.
- Model perangkat yang kompatibel.
- Harga modal.
- Harga jual.
- Stok.
- Stok minimum.
- Satuan.
- Lokasi penyimpanan.
- Keterangan.

Fitur:
- Tambah/edit produk.
- Pencarian produk.
- Barcode.
- Stok masuk.
- Stok keluar.
- Penyesuaian stok.
- Riwayat pergerakan stok.
- Notifikasi stok menipis.
- Laporan persediaan.

Pengurangan stok akibat servis harus tercatat melalui penggunaan sparepart pada order, bukan melalui perubahan stok langsung tanpa histori.

# 5.4 Supplier

Digunakan untuk mengelola pemasok sparepart.

Data:
- Nama supplier.
- Nama kontak.
- Nomor telepon.
- WhatsApp.
- Alamat.
- Email.
- Catatan.

Fitur:
- Tambah supplier.
- Edit supplier.
- Riwayat pembelian.
- Total pembelian.
- Hutang supplier.
- Riwayat pembayaran.

# 5.5 Layanan Servis

Modul untuk mengelola jenis layanan dan tarif jasa.

Contoh layanan:
- Ganti LCD.
- Ganti baterai.
- Ganti konektor charger.
- Flashing software.
- Instal ulang.
- Perbaikan motherboard.
- Perbaikan sinyal.
- Perbaikan mati total.
- Perbaikan bootloop.
- Perbaikan kamera.

Data:
- Nama layanan.
- Kategori.
- Deskripsi.
- Estimasi pengerjaan.
- Harga jasa.
- Garansi default.
- Status aktif/nonaktif.

Layanan dapat memiliki harga berbeda sesuai jenis perangkat atau kondisi kerusakan.

# 5.6 Teknisi

Modul khusus untuk mengelola karyawan teknisi.

Owner dapat menambahkan banyak teknisi dalam satu toko.

Data teknisi:
- Nama lengkap.
- Foto profil.
- Nomor telepon.
- Email.
- Username.
- Password.
- Tanggal bergabung.
- Keahlian.
- Status aktif/nonaktif.
- Komisi (opsional).

Fitur:
- Tambah teknisi.
- Edit teknisi.
- Nonaktifkan teknisi.
- Reset password.
- Lihat daftar pekerjaan.
- Lihat riwayat servis.
- Lihat performa teknisi.
- Lihat komisi.

Setiap teknisi harus memiliki akun login sendiri.

Teknisi tidak perlu mendaftar melalui halaman registrasi publik. Akun dibuat oleh Owner atau Admin yang memiliki izin.

# 5.7 Order Servis

Ini merupakan modul paling penting dalam SEMAPON.

Setiap perangkat yang masuk untuk diperbaiki harus memiliki order servis.

### A. Penerimaan Perangkat

Data order:

**Informasi order**
- Nomor order otomatis.
- Tanggal masuk.
- Estimasi selesai.
- Status order.

**Informasi pelanggan**
- Pelanggan.
- Nomor WhatsApp.

**Informasi perangkat**
- Merek.
- Model.
- Warna.
- IMEI 1 (opsional).
- IMEI 2 (opsional).
- Nomor seri (opsional).
- Password/PIN perangkat (opsional dan sensitif).
- Kelengkapan perangkat.

**Informasi kerusakan**
- Keluhan pelanggan.
- Kondisi fisik.
- Diagnosa awal.
- Foto perangkat.
- Catatan penerimaan.

Password atau PIN perangkat tidak boleh ditampilkan dalam halaman tracking publik. Jika benar-benar dibutuhkan, simpan menggunakan perlindungan enkripsi dan batasi aksesnya.

### B. Penugasan Teknisi

Owner/Admin dapat:
- Menentukan teknisi.
- Mengganti teknisi.
- Menugaskan beberapa teknisi jika dibutuhkan.
- Menentukan prioritas pengerjaan.
- Menentukan estimasi pengerjaan.

Sistem menyimpan histori penugasan.

### C. Diagnosa

Teknisi dapat:
- Mengisi hasil pengecekan.
- Mengidentifikasi kerusakan.
- Menambahkan layanan.
- Menambahkan kebutuhan sparepart.
- Mengajukan estimasi biaya.
- Mengunggah foto hasil pemeriksaan.

### D. Persetujuan Pelanggan

Pelanggan perlu menyetujui estimasi biaya sebelum pekerjaan yang memerlukan persetujuan dilakukan.

Status persetujuan:
- Menunggu persetujuan.
- Disetujui.
- Ditolak.

Persetujuan dapat dicatat oleh Admin melalui komunikasi dengan pelanggan. Persetujuan digital melalui halaman pelanggan dapat dikembangkan sebagai fitur lanjutan.

### E. Pengerjaan

Teknisi dapat:
- Memulai pekerjaan.
- Menambahkan catatan.
- Menggunakan sparepart.
- Mengunggah foto.
- Memperbarui progres.
- Mencatat kendala.

### F. Testing

Sebelum servis dinyatakan selesai:
- Teknisi melakukan pengujian.
- Mengisi checklist fungsi.
- Mengunggah foto hasil akhir.
- Mengisi catatan testing.

### G. Penyelesaian

Order dapat ditandai selesai setelah proses pengerjaan dan pemeriksaan akhir.

Pembayaran dicatat.
Pelanggan diberitahu.
Garansi mulai dihitung sesuai aturan toko dan ketentuan transaksi.

### Status Order

Gunakan status berikut:

1. `received` — HP diterima.
2. `checking` — Sedang diperiksa.
3. `waiting_approval` — Menunggu persetujuan pelanggan.
4. `waiting_parts` — Menunggu sparepart.
5. `in_progress` — Sedang dikerjakan.
6. `testing` — Pengujian perangkat.
7. `completed` — Servis selesai.
8. `ready_for_pickup` — Siap diambil.
9. `picked_up` — Sudah diambil.
10. `cancelled` — Dibatalkan.

Tidak semua status wajib dilewati. Sistem harus mendukung transisi status sesuai kondisi pekerjaan.

Setiap perubahan status harus memiliki histori:
- Status sebelumnya.
- Status baru.
- Pengguna yang mengubah.
- Waktu perubahan.
- Catatan perubahan.

---

# 6. CUSTOMER SERVICE TRACKING

## 6.1 Tujuan

Pelanggan dapat mengetahui status perbaikan HP tanpa perlu login atau membuat akun SEMAPON.

Konsepnya seperti tracking nomor resi ekspedisi.

## 6.2 Mekanisme Tracking

Ketika order dibuat, sistem menghasilkan token acak yang aman.

Contoh link:

`https://sempanon.id/track/AbC9xY7mKp2...`

Ketentuan:
- Token dibuat menggunakan cryptographically secure random generator.
- Token memiliki entropi minimal 256 bit.
- Database hanya menyimpan SHA-256 hash dari token.
- Token asli hanya tersedia untuk kebutuhan pengiriman link.
- Token tidak boleh berasal dari ID order, nomor telepon, atau nomor invoice.
- Token dapat dicabut dan dibuat ulang.
- Token tidak boleh ditebak melalui nomor order.

Saat pelanggan membuka link:
1. Sistem menerima token.
2. Sistem melakukan hashing terhadap token.
3. Sistem mencari hash yang cocok.
4. Sistem memverifikasi token masih aktif dan belum kedaluwarsa.
5. Sistem mengambil data order terkait.
6. Sistem menampilkan halaman tracking.

## 6.3 Halaman Tracking Pelanggan

Informasi yang ditampilkan:

- Nama toko.
- Logo toko.
- Nomor order.
- Tanggal penerimaan.
- Merek dan model HP.
- Keluhan servis yang aman untuk ditampilkan.
- Status pengerjaan.
- Estimasi selesai (jika tersedia).
- Timeline perubahan status.
- Informasi bahwa HP siap diambil.
- Kontak WhatsApp toko.

Informasi yang tidak boleh ditampilkan:
- Catatan internal teknisi.
- Harga modal sparepart.
- Keuntungan toko.
- Password/PIN perangkat.
- Data pelanggan lain.
- Catatan internal toko.
- Informasi akun teknisi yang tidak diperlukan.

## 6.4 Contoh Timeline Tracking

- 09.00 — Perangkat diterima.
- 10.15 — Sedang dilakukan pengecekan.
- 11.30 — Diagnosa selesai.
- 13.00 — Menunggu persetujuan pelanggan.
- 14.00 — Pengerjaan dimulai.
- 16.00 — Servis selesai.
- 16.15 — Siap diambil.

Timeline tersebut merupakan ilustrasi. Data sebenarnya berasal dari histori order.

## 6.5 Pengiriman Link

Setelah order dibuat, Admin dapat menekan tombol:

**Kirim Tracking WhatsApp**

Pesan contoh:

Halo Kak, perangkat HP kamu sudah kami terima untuk proses servis.

Kamu dapat memantau perkembangan pengerjaan melalui link berikut:

[LINK TRACKING]

Terima kasih telah mempercayakan servis HP kepada toko kami.

Pengiriman dapat menggunakan:
- WhatsApp manual melalui link WhatsApp.
- WhatsApp API sebagai fitur lanjutan.

## 6.6 Keamanan Tracking

- Gunakan token acak, bukan hash ID order.
- Hash token menggunakan SHA-256.
- Gunakan HTTPS.
- Batasi request tracking untuk mencegah penyalahgunaan.
- Jangan menampilkan informasi sensitif.
- Sediakan pencabutan token.
- Gunakan masa berlaku token yang dapat dikonfigurasi.
- Terapkan `noindex` agar halaman tracking tidak diindeks mesin pencari.
- Hindari menyimpan token mentah pada log aplikasi dan analytics.
- Terapkan kebijakan cache yang tidak membagikan informasi antar pelanggan.

Link tracking merupakan akses privat. Siapa pun yang memiliki link dapat melihat informasi yang diizinkan, sehingga pelanggan harus diberi tahu agar tidak membagikannya sembarangan.

---

# 7. PENGINGAT

Modul pengingat membantu toko mengelola aktivitas.

Jenis pengingat:
- Order belum dikerjakan.
- Order terlambat.
- HP sudah selesai tetapi belum diambil.
- Piutang pelanggan.
- Garansi servis hampir habis.
- Stok sparepart menipis.
- Jadwal pekerjaan.
- Pengingat pembayaran supplier.

Pengingat dapat ditampilkan melalui dashboard dan notifikasi internal.

---

# 8. KEUANGAN

## 8.1 Pemasukan

Sumber pemasukan:
- Pembayaran servis.
- Pembayaran sparepart.
- Biaya pemeriksaan (jika diterapkan).
- Pendapatan lainnya.

## 8.2 Pengeluaran

Kategori:
- Pembelian sparepart.
- Biaya operasional.
- Listrik.
- Sewa tempat.
- Gaji.
- Komisi teknisi.
- Transportasi.
- Pengeluaran lainnya.

## 8.3 Pembayaran Order

Mendukung:
- Tunai.
- Transfer bank.
- QRIS.
- Debit.
- Pembayaran campuran jika diperlukan.

Fitur:
- Pembayaran penuh.
- Pembayaran DP.
- Pembayaran bertahap.
- Sisa tagihan.
- Riwayat pembayaran.
- Pembatalan pembayaran melalui mekanisme koreksi yang tercatat.

## 8.4 Laporan Keuangan

- Total pemasukan.
- Total pengeluaran.
- Laba kotor.
- Estimasi laba bersih.
- Total piutang.
- Hutang supplier.
- Arus kas.

Perhitungan keuntungan harus memperhitungkan biaya jasa, biaya sparepart, pengeluaran terkait, dan pembayaran aktual secara terpisah.

---

# 9. LAPORAN

Jenis laporan:

### Laporan Servis
- Total order.
- Order selesai.
- Order dibatalkan.
- Order dalam proses.
- Rata-rata waktu pengerjaan.
- Servis paling banyak.

### Laporan Teknisi
- Jumlah order per teknisi.
- Order selesai per teknisi.
- Rata-rata durasi pengerjaan.
- Komisi teknisi.
- Produktivitas teknisi.

### Laporan Produk
- Sparepart paling sering digunakan.
- Stok tersedia.
- Stok menipis.
- Nilai persediaan.
- Riwayat stok masuk dan keluar.

### Laporan Keuangan
- Pendapatan harian.
- Pendapatan bulanan.
- Pengeluaran.
- Keuntungan.
- Piutang.
- Pembayaran supplier.

Filter laporan:
- Hari ini.
- Kemarin.
- Minggu ini.
- Bulan ini.
- Tahun ini.
- Rentang tanggal khusus.

Export:
- PDF.
- Excel.

---

# 10. SUPER ADMIN DASHBOARD

Super Admin memiliki dashboard terpisah dari dashboard toko.

Fokus utama adalah monitoring platform SaaS.

## 10.1 Overview

Informasi:
- Total tenant.
- Tenant aktif.
- Tenant trial.
- Tenant expired.
- Total pengguna.
- Total teknisi terdaftar.
- Total order servis platform.
- Total pelanggan platform.
- Total langganan aktif.

## 10.2 Traffic Monitoring

Informasi:
- Total request.
- Pengunjung harian.
- Pengunjung bulanan.
- Pengguna aktif.
- Halaman paling banyak diakses.
- Penggunaan resource aplikasi.
- Statistik endpoint.
- Status layanan.

Untuk analytics, gunakan agregasi yang menjaga privasi tenant. Jangan menyimpan informasi sensitif tracking atau kredensial pengguna dalam analytics.

## 10.3 Tenant Management

Super Admin dapat:
- Melihat daftar tenant.
- Melihat nama toko.
- Melihat pemilik.
- Melihat tanggal registrasi.
- Melihat paket langganan.
- Melihat status tenant.
- Mengaktifkan atau menonaktifkan tenant.
- Melihat statistik penggunaan.
- Melihat riwayat langganan.

## 10.4 Subscription Management

- Membuat paket.
- Mengubah harga paket.
- Mengatur batas teknisi.
- Mengatur batas fitur.
- Melihat pembayaran.
- Memperpanjang langganan.
- Mengelola masa trial.

## 10.5 Platform Monitoring

- Application health.
- Database status.
- Queue status.
- Failed jobs.
- Error log.
- Storage usage.
- Response time.
- Status backup.

Super Admin tidak boleh memiliki akses otomatis ke detail transaksi dan informasi pribadi pelanggan tenant.

---

# 11. USER INTERFACE & DESIGN SYSTEM

## 11.1 Prinsip Desain

SEMAPON menggunakan pendekatan Mobile-First Design.

Prioritas:
1. Smartphone.
2. Tablet.
3. Desktop.

Alasan:
Pemilik konter dan teknisi lebih sering menggunakan smartphone dalam aktivitas operasional sehari-hari.

## 11.2 Teknologi UI

- Tailwind CSS.
- Blade Templates.
- Alpine.js atau Livewire untuk interaksi.
- Lucide Icons.
- Chart.js untuk grafik.

## 11.3 Design Guidelines

- Tampilan modern dan minimalis.
- Navigasi mudah dipahami.
- Sidebar desktop.
- Bottom navigation atau drawer untuk mobile.
- Formulir nyaman digunakan melalui layar sentuh.
- Tombol memiliki ukuran sentuh yang memadai.
- Tabel desktop berubah menjadi card/list pada mobile.
- Status order menggunakan badge.
- Warna konsisten untuk setiap status.
- Modal dan drawer responsif.
- Loading state.
- Empty state.
- Error state.
- Toast notification.

## 11.4 Navigasi Mobile

Menu utama yang direkomendasikan:
- Dashboard.
- Order.
- Tambah Order.
- Pelanggan.
- Lainnya.

Tombol tambah order harus mudah ditemukan karena penerimaan HP merupakan salah satu aktivitas terpenting.

---

# 12. TECHNOLOGY STACK

## 12.1 Backend

| Teknologi | Penggunaan |
|---|---|
| PHP | Bahasa pemrograman |
| Laravel | Backend framework |
| Laravel Eloquent | ORM |
| Laravel Validation | Validasi input |
| Laravel Policies | Authorization |
| Laravel Queues | Background jobs |
| Laravel Scheduler | Scheduled tasks |

## 12.2 Frontend

| Teknologi | Penggunaan |
|---|---|
| Blade | Template engine |
| Tailwind CSS | Styling |
| Alpine.js | Interaksi ringan |
| Livewire | Reactive components |
| Lucide Icons | Icon |
| Chart.js | Dashboard charts |

Rekomendasi: Laravel + Blade + Livewire + Alpine.js + Tailwind CSS.

Tidak perlu memulai dengan frontend SPA terpisah. Pendekatan ini lebih sederhana untuk MVP dan tetap dapat menghasilkan UI modern.

## 12.3 Database

Rekomendasi:
- MySQL atau PostgreSQL.
- Gunakan foreign key.
- Gunakan indexing.
- Gunakan transaksi database.
- Gunakan soft delete pada data yang relevan.
- Gunakan UUID/ULID untuk identifier yang perlu diekspos secara eksternal.

## 12.4 Infrastructure

- Redis untuk cache dan queue.
- Object storage untuk foto perangkat.
- HTTPS.
- Linux VPS.
- Nginx.
- Automated database backup.
- Monitoring dan logging.

## 12.5 Architecture

Gunakan modular monolith.

Modul:
- Authentication.
- Tenant Management.
- User & Roles.
- Customers.
- Technicians.
- Products.
- Suppliers.
- Services.
- Service Orders.
- Tracking.
- Inventory.
- Finance.
- Reports.
- Subscriptions.
- Super Admin.

Pisahkan tanggung jawab menggunakan Controllers, Services, Models, Policies, Jobs, dan Events sesuai kebutuhan.

---

# 13. DATABASE DESIGN (INITIAL ERD)

Database berikut merupakan rancangan awal entitas utama.

## 13.1 Authentication & Tenant

### users
- id
- name
- email
- password
- phone
- status
- email_verified_at
- created_at
- updated_at

### stores
- id
- owner_id
- name
- slug
- logo
- phone
- whatsapp
- email
- address
- timezone
- status
- created_at
- updated_at

### store_users
- id
- store_id
- user_id
- role
- status
- created_at
- updated_at

Tabel ini memungkinkan seorang user memiliki keanggotaan pada toko sesuai kebijakan produk.

### roles / permissions
Gunakan sistem role dan permission untuk mengatur akses secara granular.

## 13.2 Subscription

### plans
- id
- name
- price_monthly
- price_yearly
- max_technicians
- features
- status

### subscriptions
- id
- store_id
- plan_id
- starts_at
- ends_at
- status
- trial_ends_at

### subscription_payments
- id
- subscription_id
- amount
- payment_method
- payment_reference
- status
- paid_at

## 13.3 Customers & Technicians

### customers
- id
- store_id
- name
- phone
- email
- address
- notes

### technician_profiles
- id
- store_id
- user_id
- expertise
- commission_type
- commission_value
- status

## 13.4 Products & Suppliers

### product_categories
- id
- store_id
- name

### products
- id
- store_id
- category_id
- sku
- name
- brand
- cost_price
- selling_price
- stock
- minimum_stock
- unit
- status

### suppliers
- id
- store_id
- name
- phone
- address
- notes

### purchases
- id
- store_id
- supplier_id
- invoice_number
- total_amount
- paid_amount
- status
- purchased_at

### purchase_items
- id
- purchase_id
- product_id
- quantity
- unit_cost
- subtotal

### stock_movements
- id
- store_id
- product_id
- reference_type
- reference_id
- movement_type
- quantity
- notes
- created_by
- created_at

## 13.5 Services & Orders

### service_categories
- id
- store_id
- name

### services
- id
- store_id
- category_id
- name
- description
- price
- estimated_duration
- warranty_days
- status

### service_orders
- id
- store_id
- customer_id
- order_number
- device_brand
- device_model
- device_color
- imei_1
- imei_2
- serial_number
- customer_complaint
- initial_condition
- initial_diagnosis
- estimated_cost
- final_cost
- status
- received_at
- estimated_completion_at
- completed_at
- picked_up_at
- created_by

### service_order_items
- id
- store_id
- service_order_id
- service_id
- description
- quantity
- unit_price
- subtotal

### service_order_technicians
- id
- store_id
- service_order_id
- technician_id
- assigned_by
- assigned_at
- completed_at

### service_order_parts
- id
- store_id
- service_order_id
- product_id
- quantity
- unit_cost
- selling_price
- subtotal

### service_order_histories
- id
- store_id
- service_order_id
- old_status
- new_status
- description
- changed_by
- created_at

### service_order_diagnostics
- id
- store_id
- service_order_id
- technician_id
- diagnosis
- estimated_cost
- notes
- created_at

### service_order_media
- id
- store_id
- service_order_id
- uploaded_by
- file_path
- media_type
- visibility
- created_at

## 13.6 Tracking

### service_tracking_tokens
- id
- store_id
- service_order_id
- token_hash
- expires_at
- revoked_at
- last_accessed_at
- created_at

Jangan menyimpan token asli dalam tabel ini.

## 13.7 Finance

### transactions
- id
- store_id
- service_order_id
- type
- category
- description
- amount
- payment_method
- created_by
- transaction_date

### payments
- id
- store_id
- service_order_id
- amount
- payment_method
- payment_reference
- paid_at
- created_by

### expenses
- id
- store_id
- category
- description
- amount
- expense_date
- created_by

## 13.8 Notifications & Logs

### reminders
- id
- store_id
- title
- description
- reminder_date
- status

### activity_logs
- id
- store_id
- user_id
- action
- subject_type
- subject_id
- metadata
- created_at

### platform_metrics
- id
- metric_name
- metric_value
- recorded_at

Catatan: ERD ini merupakan fondasi awal. Relasi, unique constraint, indeks, audit trail, dan kebutuhan normalisasi harus difinalisasi sebelum implementasi migration.

---

# 14. SECURITY REQUIREMENTS

Keamanan merupakan persyaratan utama karena SEMAPON menyimpan data dari banyak toko.

1. Password wajib di-hash menggunakan mekanisme Laravel.
2. Semua halaman operasional memerlukan autentikasi.
3. Setiap tenant wajib memiliki isolasi data.
4. Gunakan middleware untuk resolusi tenant.
5. Gunakan Policies/Gates untuk otorisasi.
6. Validasi semua request pada backend.
7. Terapkan CSRF protection.
8. Terapkan rate limiting.
9. Gunakan HTTPS.
10. Lindungi file upload.
11. Batasi akses Super Admin.
12. Simpan audit log untuk tindakan penting.
13. Gunakan token tracking yang aman.
14. Gunakan database transaction untuk operasi finansial dan stok.
15. Jangan menghapus histori pembayaran dan stok secara permanen melalui operasi biasa.

Informasi perangkat yang sensitif harus mendapatkan perlindungan tambahan.

---

# 15. NON-FUNCTIONAL REQUIREMENTS

## Performance
- Halaman operasional dioptimalkan untuk perangkat mobile.
- Gunakan pagination.
- Gunakan eager loading.
- Hindari N+1 queries.
- Optimalkan query laporan.
- Gunakan cache untuk data yang sesuai.

## Reliability
- Database backup otomatis.
- Penanganan error.
- Queue retry.
- Logging.
- Monitoring.

## Scalability
- Modular monolith.
- Tenant isolation.
- Redis cache.
- Background jobs.
- Storage terpisah.
- Database indexing.

## Usability
- Bahasa Indonesia.
- Responsive.
- Form sederhana.
- Navigasi mudah.
- Minim langkah untuk membuat order servis.

---

# 16. DEVELOPMENT ROADMAP

Pengembangan dilakukan secara bertahap agar fitur utama benar-benar stabil.

## PHASE 1 — Foundation & Authentication

- Setup Laravel.
- Setup Tailwind CSS.
- Setup database.
- Authentication.
- Registrasi Owner.
- Login dan logout.
- Tenant creation.
- Tenant middleware.
- Role & permission.
- Dashboard layout.
- Mobile navigation.
- Super Admin authentication.

**Output:** pengguna dapat mendaftar, membuat toko, dan login ke dashboard masing-masing.

## PHASE 2 — Core Service Management

- CRUD pelanggan.
- CRUD layanan.
- CRUD teknisi.
- CRUD order servis.
- Penugasan teknisi.
- Diagnosa.
- Status pekerjaan.
- Histori order.
- Upload foto.
- Cetak nota penerimaan.

**Output:** toko dapat menerima perangkat dan mengelola proses servis.

## PHASE 3 — Customer Tracking

- Generate token tracking.
- Hash token.
- Public tracking page.
- Timeline servis.
- Tracking link.
- Tombol WhatsApp.
- Revoke dan regenerate token.
- Security dan rate limiting.

**Output:** pelanggan dapat mengecek status servis melalui link pribadi.

## PHASE 4 — Inventory & Supplier

- Kategori produk.
- Produk dan sparepart.
- Supplier.
- Pembelian.
- Stock movements.
- Penggunaan sparepart pada order.
- Notifikasi stok menipis.

**Output:** stok dan pembelian dapat dikelola dengan baik.

## PHASE 5 — Finance & Reporting

- Pembayaran.
- DP dan pelunasan.
- Pengeluaran.
- Piutang.
- Hutang supplier.
- Dashboard keuangan.
- Laporan servis.
- Laporan teknisi.
- Export PDF/Excel.

**Output:** pemilik dapat mengetahui kondisi keuangan dan performa usaha.

## PHASE 6 — SaaS & Super Admin

- Subscription.
- Paket langganan.
- Trial.
- Payment verification.
- Tenant management.
- Platform dashboard.
- Traffic monitoring.
- Usage analytics.
- Platform health monitoring.

**Output:** SEMAPON siap dioperasikan sebagai SaaS komersial.

## PHASE 7 — Advanced Features

- WhatsApp API.
- Notifikasi otomatis.
- Komisi teknisi.
- Garansi servis.
- Customer approval online.
- Tracking analytics.
- PWA.
- Fitur lanjutan sesuai kebutuhan pasar.

---

# 17. MVP SCOPE

Agar tidak terlalu besar, MVP awal harus fokus pada proses servis dari awal sampai selesai.

### Fitur wajib MVP

- [ ] Registrasi dan login Owner.
- [ ] Multi-tenant isolation.
- [ ] Dashboard toko.
- [ ] Manajemen pelanggan.
- [ ] Manajemen teknisi.
- [ ] Manajemen layanan.
- [ ] Order servis.
- [ ] Penugasan teknisi.
- [ ] Diagnosa kerusakan.
- [ ] Status pengerjaan.
- [ ] Histori status.
- [ ] Upload foto perangkat.
- [ ] Public tracking link.
- [ ] Hash token tracking.
- [ ] Tombol WhatsApp.
- [ ] Nota penerimaan.
- [ ] Pembayaran dasar.
- [ ] Role dan permission.
- [ ] Super Admin monitoring dasar.

### Fitur yang bisa menyusul

- [ ] Inventory lengkap.
- [ ] Supplier dan pembelian.
- [ ] Laporan keuntungan.
- [ ] Komisi teknisi.
- [ ] WhatsApp API.
- [ ] Subscription payment gateway.
- [ ] Export laporan.
- [ ] PWA.

---

# 18. ACCEPTANCE CRITERIA

MVP dianggap berhasil apabila:

1. Owner dapat membuat akun dan mendaftarkan toko.
2. Setiap toko memiliki data yang terisolasi.
3. Owner dapat menambahkan beberapa teknisi sesuai batas paket.
4. Setiap teknisi dapat login menggunakan akunnya sendiri.
5. Owner/Admin dapat membuat order servis.
6. Order dapat ditugaskan kepada teknisi.
7. Teknisi dapat memperbarui progres pekerjaan.
8. Setiap perubahan status tercatat.
9. Pelanggan menerima link tracking.
10. Pelanggan dapat membuka tracking tanpa login.
11. Token tracking tidak dapat ditebak melalui ID order.
12. Link tracking yang dicabut tidak dapat digunakan lagi.
13. Pelanggan tidak dapat melihat informasi internal.
14. Pembayaran tercatat pada toko yang benar.
15. Super Admin dapat memantau jumlah tenant dan penggunaan platform.
16. Tenant lain tidak dapat mengakses data toko berbeda.
17. Seluruh fitur utama dapat digunakan melalui smartphone.

---

# 19. PRODUCT PRINCIPLES

Prinsip yang harus selalu dipertahankan selama pengembangan SEMAPON:

**1. Mobile First**

Setiap fitur harus dirancang untuk penggunaan smartphone terlebih dahulu.

**2. Simple Workflow**

Proses penerimaan HP, penugasan teknisi, dan pembaruan status harus sesederhana mungkin.

**3. Tenant Isolation**

Privasi dan keamanan data setiap toko adalah prioritas.

**4. Owner Control**

Pemilik toko tetap memegang kendali atas teknisi, order, keuangan, dan pengaturan toko.

**5. Transparent Tracking**

Pelanggan dapat mengetahui progres servis tanpa harus menghubungi toko berkali-kali.

**6. Modular Development**

Fitur harus dapat dikembangkan secara bertahap tanpa merusak modul lain.

**7. SaaS Ready**

Arsitektur harus mendukung pertumbuhan jumlah tenant, pengguna, dan order.

---

# 20. FINAL PRODUCT DIRECTION

SEMAPON bukan aplikasi POS biasa dan bukan aplikasi servis yang hanya digunakan satu teknisi.

SEMAPON adalah platform manajemen bisnis servis HP yang memungkinkan satu pemilik toko mengelola banyak teknisi, pelanggan, perangkat, order servis, stok sparepart, dan keuangan melalui satu sistem.

Setiap toko memiliki ruang kerja sendiri di dalam platform SaaS.

Pelanggan tidak perlu memiliki akun. Mereka cukup menerima link tracking privat untuk memantau proses perbaikan HP seperti melacak paket ekspedisi.

Super Admin bertugas menjaga dan memantau kesehatan platform secara keseluruhan, bukan menjalankan operasional toko.

**Positioning:**

*SEMAPON — Smart Management System for Mobile Phone Repair Businesses.*

**Fokus utama:**

Mengubah pengelolaan servis HP yang sebelumnya manual menjadi lebih terstruktur, transparan, dan profesional.