# Business Monitoring System

Aplikasi web untuk memantau stok gudang, penjualan dari marketplace (Shopee, TikTok Shop, dan penjualan manual), pembelian ke supplier, serta keuangan toko dalam satu dashboard.

Dibuat untuk toko online skala kecil-menengah yang masih mencatat stok dan omzet secara terpisah di spreadsheet. Semua transaksi penjualan dan pembelian otomatis mengubah stok dan tercatat di buku kas, jadi laporan laba rugi, cashflow, dan neraca bisa langsung dilihat tanpa input ulang.

![Dashboard](docs/screenshots/dashboard.png)

## Fitur

**Produk**
- Kelola produk, kategori, brand, dan supplier
- Kode produk otomatis per kategori (contoh: `SKN-000001`, `HRB-000002`)
- Cetak label barcode untuk banyak produk sekaligus (ukuran label bisa diatur)
- Filter produk dengan stok rendah (5 unit ke bawah)

**Stok**
- Stock in, stock out, penyesuaian, dan mutasi antar lokasi rak
- Riwayat pergerakan stok lengkap dengan stok sebelum/sesudah
- Lokasi penyimpanan berupa rak dan sub rak
- Stock opname dengan pencarian lewat scan barcode
- Analitik: prediksi kapan stok habis, rekomendasi restock berdasarkan lead time supplier, dan daftar produk slow moving
- Scan produk lewat kamera HP/laptop atau scanner barcode USB

**Pembelian**
- Purchase order ke supplier (draft, dipesan, diterima, dibatalkan)
- Pembayaran bertahap, sisa hutang terlihat per PO
- Upload foto nota dan retur barang ke supplier
- Stok bertambah otomatis saat PO ditandai diterima

**Penjualan & Marketplace**
- Catat penjualan manual
- Import pesanan Shopee/TikTok dari file Excel/CSV (template tersedia)
- Mapping SKU marketplace ke produk internal
- Stok berkurang otomatis, pembatalan pesanan mengembalikan stok

**Keuangan**
- Pencatatan pemasukan dan pengeluaran; transaksi dari penjualan, biaya admin marketplace, dan pembayaran PO tercatat otomatis
- Laporan laba rugi, cashflow bulanan, dan neraca sederhana

**Laporan & Pengaturan**
- Export laporan ke PDF dan Excel. Laporan besar (penjualan, pembelian, pergerakan stok) diproses di background lewat queue, lalu muncul notifikasi saat siap diunduh
- Notifikasi stok menipis dan batch produk yang mendekati kedaluwarsa
- Backup database dari halaman pengaturan, plus backup otomatis harian
- Log aktivitas untuk setiap perubahan data
- Mode gelap, tampilan responsif, dan bisa dipasang sebagai PWA

## Tech Stack

| Bagian | Yang dipakai |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Database | MySQL / MariaDB |
| Frontend | Blade, Bootstrap 5, SCSS, jQuery, Vite |
| Tabel & grafik | DataTables (server-side), Chart.js |
| Package | Sanctum, Spatie Permission, Spatie Activitylog, Spatie Backup, Laravel Excel, DomPDF, Milon Barcode, html5-qrcode |

## Screenshot

| | |
|---|---|
| ![Login](docs/screenshots/login.png) Login | ![Dashboard dark mode](docs/screenshots/dashboard-dark.png) Dashboard (mode gelap) |
| ![Produk](docs/screenshots/produk.png) Daftar produk | ![Riwayat stok](docs/screenshots/stok-riwayat.png) Riwayat pergerakan stok |
| ![Rak](docs/screenshots/stok-rak.png) Lokasi rak & sub rak | ![Scan](docs/screenshots/scan.png) Scan barcode |
| ![Pembelian](docs/screenshots/pembelian.png) Purchase order | ![Penjualan](docs/screenshots/penjualan.png) Daftar penjualan |
| ![Form penjualan](docs/screenshots/form-penjualan.png) Input penjualan manual | ![Marketplace](docs/screenshots/marketplace.png) Import pesanan marketplace |
| ![Keuangan](docs/screenshots/keuangan.png) Transaksi keuangan | ![Laba rugi](docs/screenshots/laba-rugi.png) Laporan laba rugi |
| ![Cashflow](docs/screenshots/cashflow.png) Cashflow bulanan | ![Neraca](docs/screenshots/neraca.png) Neraca |
| ![Laporan](docs/screenshots/laporan.png) Export laporan | ![Log aktivitas](docs/screenshots/log-aktivitas.png) Log aktivitas |

Tampilan mobile:

<p>
  <img src="docs/screenshots/mobile-dashboard.png" width="260" alt="Dashboard mobile">
  &nbsp;
  <img src="docs/screenshots/mobile-penjualan.png" width="260" alt="Penjualan mobile">
</p>

> Data pada screenshot adalah data dummy dari `DemoSeeder`.

## Instalasi

Kebutuhan: PHP 8.2+, Composer, Node.js 18+, dan MySQL/MariaDB.

```bash
git clone https://github.com/ucuf-cyber/warehouse.git
cd warehouse

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=business_monitoring
DB_USERNAME=root
DB_PASSWORD=
```

Akun login dibuat dari `.env` (aplikasi ini tidak punya halaman registrasi). Tambahkan:

```env
OWNER_NAME="Owner"
OWNER_EMAIL=owner@businessmonitoring.local
OWNER_PASSWORD=password
```

Lalu jalankan migrasi dan seeder. `DemoSeeder` akan mengisi produk, pembelian, penjualan, dan transaksi keuangan contoh.

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Buka `http://localhost:8000` dan login dengan email serta password di atas.

### Queue dan scheduler

Export laporan async dan notifikasi memakai queue database. Saat development jalankan:

```bash
php artisan queue:work
```

Untuk pengecekan stok menipis, batch kedaluwarsa, dan backup harian, jalankan scheduler:

```bash
php artisan schedule:work
```

Di server, cukup pasang satu cron: `* * * * * php /path/ke/project/artisan schedule:run`.

| Command | Jadwal | Fungsi |
|---|---|---|
| `bms:check-low-stock` | 07:00 | Notifikasi produk aktif dengan stok 5 atau kurang |
| `bms:check-expired-batches` | 07:05 | Notifikasi batch yang kedaluwarsa dalam 7 hari |
| `backup:clean` / `backup:run` | 01:00 / 01:30 | Bersihkan backup lama lalu buat backup baru |

### Konfigurasi tambahan (opsional)

```env
# Ukuran label barcode (mm)
BARCODE_PRINTER_WIDTH_MM=40
BARCODE_PRINTER_HEIGHT_MM=25

# Lama cache data dashboard (menit)
DASHBOARD_CACHE_TTL=5

# Lokasi mysqldump jika tidak ada di PATH (dibutuhkan untuk backup)
DB_DUMP_BINARY_PATH="C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/"
```

Integrasi API Shopee dan TikTok Shop sudah disiapkan (`MARKETPLACE_SHOPEE_ENABLED`, `MARKETPLACE_TIKTOK_ENABLED`, dan kredensialnya di `config/bms.php`), tapi untuk saat ini pesanan masuk lewat import file.

## Struktur Singkat

```
app/
├── Console/Commands/   # command terjadwal (stok menipis, batch kedaluwarsa)
├── Exports/ Imports/   # export Excel & import pesanan marketplace
├── Http/Controllers/
│   ├── Admin/          # halaman dashboard per modul
│   └── Api/            # endpoint JSON untuk dashboard & scan
├── Jobs/               # export laporan di background
├── Models/
├── Notifications/
└── Services/           # logika stok, keuangan, analitik, import marketplace
resources/views/admin/  # tampilan per modul
resources/js/           # script per halaman
docs/                   # ERD dan catatan deploy
```

Diagram relasi tabel ada di [docs/erd.md](docs/erd.md). Catatan deploy ke Vercel ada di [docs/vercel-deployment.md](docs/vercel-deployment.md).

## Testing

```bash
php artisan test
```

## Lisensi

MIT
