# Deploy ke Vercel

Proyek ini berjalan melalui PHP community runtime `vercel-php@0.9.0`. File statis dari `public/` dilayani Vercel terlebih dahulu, lalu semua route aplikasi diteruskan ke `api/index.php`.

## Yang perlu disiapkan

Vercel tidak menyediakan filesystem yang persisten. Aplikasi ini **tidak boleh** memakai database SQLite atau MySQL Laragon lokal di production. Siapkan database MySQL/MariaDB yang dapat diakses dari internet, lalu jalankan migrasi dari mesin yang memiliki akses ke database tersebut.

Upload foto produk, nota pembelian, logo, hasil export, dan backup saat ini memakai disk lokal Laravel. Data itu tidak akan persisten di Vercel. Sebelum fitur-fitur tersebut dipakai di production, pindahkan disk `public`/backup ke object storage yang kompatibel S3.

Queue berbasis database juga membutuhkan worker terpisah; Vercel Function tidak menjalankan `queue:work` secara terus-menerus. Jalankan worker pada layanan terpisah atau ubah pekerjaan yang relevan ke proses sinkron.

## Environment Variables

Tambahkan di Vercel Project Settings → Environment Variables untuk **Production**:

```text
APP_KEY=<salin nilai APP_KEY dari .env lokal>
APP_URL=https://<nama-proyek>.vercel.app
DB_CONNECTION=mysql
DB_HOST=<host database production>
DB_PORT=3306
DB_DATABASE=<nama database>
DB_USERNAME=<user database>
DB_PASSWORD=<password database>
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Tambahkan juga seluruh variabel layanan yang memang dipakai, seperti `MAIL_*`, kredensial Shopee/TikTok, dan kredensial object storage. Jangan mengunggah `.env`.

## Migrasi database

Sesudah database tersedia, dari mesin lokal jalankan:

```powershell
$env:DB_CONNECTION='mysql'
$env:DB_HOST='<host database production>'
$env:DB_PORT='3306'
$env:DB_DATABASE='<nama database>'
$env:DB_USERNAME='<user database>'
$env:DB_PASSWORD='<password database>'
php artisan migrate --force
```

## Deploy

Login dan deploy dari root proyek:

```powershell
npx vercel login
npx vercel --prod
```

Setelah URL production diterima, masukkan URL tersebut sebagai `APP_URL`, lalu deploy ulang. Vercel menyediakan PHP sebagai community runtime; filesystem function hanya read-only dengan `/tmp` sebagai scratch space.
