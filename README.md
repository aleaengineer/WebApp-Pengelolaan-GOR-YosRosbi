# GOR Yos Rosbi - Web App Pengelolaan

> Pengelolaan booking lapangan GOR Yos Rosbi dengan sistem member, kupon, dan PWA.

---

## Daftar Isi

- [Fitur](#fitur)
- [Tech Stack](#tech-stack)
- [Instalasi](#instalasi)
- [Struktur Workflow](#struktur-workflow)
- [Peran Pengguna](#peran-pengguna)
- [Database](#database)
- [Kontributor](#kontributor)

---

## Fitur

### Customer
- Landing page dengan info GOR, harga, timeline, promo
- Jadwal ketersediaan lapangan realtime
- Booking per jam / harian dengan cek ketersediaan & jeda 30 menit otomatis
- 3 metode pembayaran: transfer (unggah bukti), Midtrans (simulasi), cash
- Kuota member gratis (jika paket aktif)
- Kupon percent (hanya per jam, max 1x/user)
- Dashboard: statistik booking, paket member, riwayat, promo
- PWA installable

### Admin / Operator
- Kelola booking (lihat detail, update status)
- Kelola blokir jadwal manual
- Kelola paket member (harga, kuota, durasi)
- Kelola kupon (buat, edit, aktif/nonaktifkan)
- Laporan booking per tanggal & jenis kegiatan
- Pengaturan umum (jam operasional, buffer, harga sewa, rekening, kontak)

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Framework | Laravel 12.69 |
| Auth | Laravel Breeze (Blade) |
| Frontend | Blade + Tailwind CSS 3 |
| Fonts | Poppins, Inter |
| PWA | vite-plugin-pwa (Cache-First/Network-First) |
| Database | MySQL/MariaDB (produksi) / SQLite (dev) |
| PHP | 8.2 |
| Node | 25+ |

---

## Instalasi

### Persyaratan
- PHP 8.2+ dengan extension: openssl, pdo, mbstring, tokenizer, xml, ctype, json, bcmath, fileinfo
- Composer 2+
- Node 18+ & npm 10+
- MySQL 5.7+ / MariaDB 10.4+ **atau** SQLite

### Setup Lokal

```bash
# 1. Clone repo
git clone https://github.com/aleaengineer/WebApp-Pengelolaan-GOR-YosRosbi.git
cd WebApp-Pengelolaan-GOR-YosRosbi

# 2. Install PHP dependencies
composer install

# 3. Install npm dependencies & build assets
npm install && npm run build

# 4. Copy env
cp .env.example .env

# 5. Configure database di .env
DB_CONNECTION=mysql
DB_DATABASE=goryosrosbi
DB_USERNAME=root
DB_PASSWORD=

# 6. Generate app key
php artisan key:generate

# 7. Jalankan migrasi + seeder
php artisan migrate:fresh --seed

# 8. Jalankan server
php artisan serve
```

Buka: `http://127.0.0.1:8000`

### Setup MySQL (opsional, jika tidak pakai SQLite)

```sql
CREATE DATABASE goryosrosbi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Struktur Workflow

### 1. Landing Page (`/`)
- Hero dengan nama GOR, deskripsi, CTA "Booking Sekarang"
- Strip info: jam operasional, alamat, kontak
- Harga per jam & harian
- Timeline pengalaman GOR
- Promo aktif (banner merah)
- Tombol PWA install

### 2. Cek Ketersediaan (`/jadwal`)
- Slot tetap: 08:00 - 00:00 (90 menit siklus = 60 menit sewa + 30 menit jeda)
- Cek realtime berdasarkan booking aktif + blokir jadwal + buffer pembersihan
- Jika ada bentrok → saran slot tersedia berikutnya

### 3. Booking (`/booking/create`)
**Form:**
- Tanggal, jam mulai, jam selesai, jenis kegiatan (badminton/volley/basket/event)
- Tipe sewa: per jam / harian
- Metode: transfer / midtrans / cash
- Kode kupon (opsional)

**Proses submit:**
```
[1] Validasi input
[2] Cek ketersediaan (buffer jeda 30 menit antar booking)
[3] Hitung harga dasar (HargaSewa per_jam/harian)
[4] Cek kuota member → jika cukup, total = Rp0
[5] Validasi kupon (percent, per_jam, tidak stack dengan gratis member, 1x/user)
[6] Hitung diskon kupon
[7] Simpan booking + payment dalam DB transaction
[8] Redirect ke halaman detail booking
```

### 4. Detail Booking (`/booking/{id}`)
- Info lengkap: tanggal, jam, jenis, tipe, metode
- Status: pending_verifikasi / paid / confirmed / completed / cancelled
- Jika transfer → upload bukti pembayaran
- Tombol cancel (kembalikan kuota kupon jika ada)

### 5. Dashboard Customer (`/dashboard`)
- Greeting + status member
- Statistik: total booking, jam terpakai, total bayar, total diskon
- Jadwal terdekat (countdown)
- Quick actions: booking baru, riwayat, jadwal
- Promo aktif
- Riwayat booking terbaru

### 6. Admin Panel (`/admin/*`)
- `/admin/bookings` → Lihat semua booking, detail per booking
- `/admin/blokir` → Tambah/hapus blokir jadwal manual
- `/admin/coupons` → CRUD kupon (code, percent, max discount, quota, per_user_limit, min_amount, aktif/nonaktif)
- `/admin/laporan` → Filter booking per tanggal & jenis, jumlah total
- `/admin/settings` → Pengaturan umum + harga paket member

---

## Peran Pengguna

| Peran | Akses |
|-------|-------|
| **Customer** | Booking, dashboard, riwayat, dashboard customer |
| **Admin** | Semua fitur admin + dashboard admin |
| **Operator** | Akses admin (booking, blokir, settings) |

---

## Database

### Tabel Utama

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Pengguna (role: customer/admin/operator, member_package_id, member_expired_at) |
| `lapangans` | Data lapangan |
| `paket_members` | Paket member (nama, kuota_jam, durasi_hari, harga) |
| `harga_sewas` | Harga sewa per lapangan (tipe: per_jam/harian, harga, harga_member) |
| `bookings` | Booking (tanggal, jam, jenis, tipe_sewa, total_harga, discount, status) |
| `payments` | Pembayaran (metode, amount, status, bukti_transfer) |
| `blokir_jadwals` | Blokir jadwal manual |
| `settings` | Pengaturan umum (key-value) |
| `coupons` | Kupon (code, percent, max_discount, quota, per_user_limit, min_amount) |
| `coupon_usages` | Riwayat penggunaan kupon per user |

### Relasi Workflow di Database

```
User ──(1:N)── Booking ──(1:1)── Payment
User ──(1:N)── Booking ──(N:1)── Coupon ──(1:N)── CouponUsage
User ──(N:1)── PaketMember (via member_package_id)
Lapangan ──(1:N)── Booking
Lapangan ──(1:N)── HargaSewa
Lapangan ──(1:N)── BlokirJadwal
Booking ──(N:1)── PaketMember (via paket_member_id, jika pakai kuota gratis)
```

---

## Akun Default (Hasil Seed)

| Email | Password | Peran |
|-------|----------|-------|
| `admin@goryosrosbi.test` | `password` | Admin |
| `operator@goryosrosbi.test` | `password` | Operator |
| `customer@test.com` | `password` | Customer (Paket Hemat 10x aktif) |

---

## Kupon Demo

| Kode | Diskon | Max | Min Belanja | Quota |
|------|--------|-----|-------------|-------|
| `YOS10` | 10% | Rp15.000 | - | 100 |
| `HEMAT20` | 20% | Rp20.000 | Rp50.000 | 50 |

---

## PWA

Aplikasi support install sebagai PWA:
- Manifest: `public/manifest.json` (theme color `#DC2626`)
- Service Worker: `public/sw.js` (Cache-First untuk assets, Network-First untuk API)
- Halaman offline: `public/offline.html`
- Ikon: `public/icons/` (72x72 sampai 512x512)

---

## Kreditor

Project ini dikembangkan oleh **Farhan Ale** dengan kontribusi tim GOR Yos Rosbi.
