# Phoenix Digital v2

Sistem e-commerce + back office untuk **Phoenix Digital** — penjualan akun premium, lisensi software, tools AI, serta jasa pengecekan plagiarisme/AI. Satu aplikasi Laravel yang melayani dua sisi: etalase publik untuk pelanggan, dan panel admin untuk operasional, HR, dan keuangan perusahaan.

Bahasa kode dan antarmuka adalah **Bahasa Indonesia** — nama kolom, route, dan teks UI. Ikuti itu saat menambah kode baru.

> **Dokumen pendamping**
> - [`CLAUDE.md`](CLAUDE.md) — detail arsitektur per lapisan (layout admin, konvensi komponen, pola model)
> - [`DEPLOYMENT.md`](DEPLOYMENT.md) — scheduler & cron di shared hosting

---

## ⚠️ Baca ini sebelum menjalankan apa pun

**`php artisan test` bisa menghapus seluruh database Anda kalau `phpunit.xml` salah konfigurasi.**

`tests/Pest.php` memakai `RefreshDatabase`, yang menjalankan `migrate:fresh`. Bila dua baris berikut di `phpunit.xml` dinonaktifkan, test memakai koneksi dari `.env` — yaitu database kerja Anda — dan mengosongkannya:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Kedua baris itu **wajib aktif**. Ini pernah benar-benar terjadi (20 Juli 2026) dan menghapus database pengembangan. Sebelum percaya, pastikan keluaran test menyebut `Connection: sqlite`, bukan `mysql`.

Aturan turunannya, berlaku untuk siapa pun yang melanjutkan:

- **Jangan membuat data uji di database kerja.** Bungkus dengan `DB::beginTransaction()` + `rollBack()`, atau pakai `Mail::fake()` / `Storage::fake()`.
- Backup database sebelum menjalankan migrasi yang belum pernah dicoba.

---

## Tumpukan teknologi

| Komponen | Versi / Catatan |
|---|---|
| PHP | 8.2+ (produksi 8.2.30, lokal 8.4) |
| Laravel | 11.x |
| Livewire | 3.6 + Volt 1.7 |
| Database | MySQL 8 — hampir semua model memakai UUID sebagai primary key |
| Frontend | Blade + Bootstrap 5, dibangun dengan Vite |
| Pembayaran | Midtrans Snap + QRIS |
| PDF / Excel | `barryvdh/laravel-dompdf`, `maatwebsite/excel` |
| Baca PDF | `smalot/pdfparser` — untuk membaca laporan Turnitin |

Tailwind terpasang tapi **tidak dipakai di admin**; panel admin memakai Bootstrap 5 dengan sistem desain sendiri.

`composer.json` mengunci `config.platform.php` ke versi PHP produksi. Jangan dihapus — tanpa itu `composer install` di server bisa menarik paket yang menuntut PHP lebih baru lalu gagal.

---

## Menjalankan secara lokal

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate

# Sesuaikan DB_* di .env, lalu:
php artisan migrate
php artisan storage:link

# Dua terminal terpisah:
npm run dev            # Vite (HMR)
php artisan serve      # http://localhost:8000
```

Perintah harian lain:

```bash
npm run build              # build aset produksi
vendor/bin/pint            # format kode — jalankan sebelum commit
php artisan test           # pastikan phpunit.xml memakai sqlite (lihat peringatan di atas)
php artisan queue:work     # queue, cache, dan session semuanya memakai driver database
```

### Kunci `.env` yang belum ada di `.env.example`

`config/mail.php` mendefinisikan mailer kedua bernama `phoenix` untuk email ke pelanggan (`halo@phoenixdigital.id`); dashboard tetap memakai mailer `smtp` bawaan. Mailer itu membaca lima kunci:

| Kunci | Nilai bawaan | Wajib diisi? |
|---|---|---|
| `PHOENIX_MAIL_HOST` | `smtp.hostinger.com` | tidak |
| `PHOENIX_MAIL_PORT` | `465` | tidak |
| `PHOENIX_MAIL_ENCRYPTION` | `ssl` | tidak |
| `PHOENIX_MAIL_USERNAME` | `halo@phoenixdigital.id` | tidak |
| `PHOENIX_MAIL_PASSWORD` | — | **ya** |

Jadi yang benar-benar wajib ada di `.env` produksi hanya `PHOENIX_MAIL_PASSWORD`; sisanya sudah punya nilai bawaan yang tepat.

Catatan: `.env` juga memuat `PHOENIX_MAIL_FROM_ADDRESS`, tetapi kunci itu **tidak dibaca kode mana pun** — sisa lama yang aman dihapus.

Sisanya (`MIDTRANS_*`, `QRIS_*`, `VAPID_*` untuk push notification) sudah tercantum di `.env.example`.

---

## Peta kode

Route memetakan langsung ke **komponen Livewire**, bukan controller. `routes/web.php` adalah sumber kebenaran untuk URL mana dilayani komponen mana.

```
app/
├── Livewire/Pages/
│   ├── Admin/<Fitur>/       # pola FeatureList / FeatureCreate / FeatureEdit
│   └── Public/              # Homepage, ShopPage, Bundling, Blog, About, Services, Legal
├── Models/                  # perilaku domain tinggal di sini (booted, scope, accessor)
├── Actions/                 # Finance, Gaji, Task — operasi lintas model
├── Services/                # PaymentService, QrisService, WebPushService, PromoService
├── Support/                 # kelas bantu tanpa state (tabel di bawah)
├── Console/Commands/        # 9 perintah terjadwal
└── Http/Controllers/        # hanya endpoint non-Livewire: callback Midtrans, unduhan berkas
```

### Isi `app/Support/`

| Kelas | Tugas |
|---|---|
| `AtribusiAddonJasa` | Memindahkan omset & modal add-on ke produk add-on itu sendiri |
| `CashFlowInsight` | Panel insight arus kas — **murni PHP, tanpa AI atau API eksternal** |
| `PlagiarismReader` | Membaca persentase dari laporan PDF Turnitin/GPTZero |
| `PdfPageCounter` | Menghitung halaman PDF untuk penetapan harga jasa |
| `LanguageDetector` | Mendeteksi dokumen berbahasa Inggris (syarat cek AI) |
| `HtmlSanitizer` | Membersihkan HTML isi blog dengan whitelist tag/atribut |
| `JalurAnalitik` | Menyamarkan URL bertoken sebelum dikirim ke GA/Pixel |
| `PeriodeGaji` | Perhitungan periode penggajian |
| `ActivityLogger` | Pencatatan aktivitas pengguna |

---

## Konsep domain yang wajib dipahami

Bagian inilah yang paling sering disalahpahami. Baca sebelum menyentuh fitur terkait.

### 1. Buku besar CashFlow bersifat polimorfik

`CashFlow` adalah **satu** buku besar yang menampung pergerakan uang dari banyak model: `Order`, `Spending`, `Loan`, `Pengembalian`, `GajiKaryawans`, `PemesananRsc`. Masing-masing punya relasi `cashFlow(): MorphOne`.

Satu-satunya pintu masuk:

```php
App\Actions\Finance\SyncCashFlowAction::execute($model, $data);
```

Method privat `shouldRecord()` di dalamnya memuat aturan kapan uang dianggap nyata — misalnya `Spending` baru dicatat saat `status !== 'pending'`, dan `Order` saat `paid`/`completed`. Bila `false`, baris buku besar yang sudah ada akan dihapus.

**Menambah fitur yang menyentuh uang? Wajib lewat action ini dan perluas `shouldRecord()`.** Jangan menulis ke tabel `cash_flows` secara langsung.

### 2. Modal, omset bersih, dan add-on harus selalu cocok

Tiga tempat menghitung angka yang sama dan **harus konsisten**:

1. Fitur **Modal** (`ModalList`)
2. Kartu **omset bersih** di Cash Flow (`CashFlowList::hitungOmsetBersih()`)
3. Rincian **riwayat transaksi**

Jebakan terbesar: **`order_items.subtotal` SUDAH termasuk harga add-on.** Karena itu omset add-on harus **dipindahkan** dari produk induk ke produk add-on, bukan ditambahkan — kalau ditambahkan, omset terhitung dua kali. Inilah tugas `AtribusiAddonJasa`. Modal justru sebaliknya: **ditambahkan**, karena modal induk belum menghitung pemeriksaan add-on.

Semua perhitungan omset wajib menyaring `status = 'completed'`; pesanan `pending` bukan uang nyata.

Dua invarian yang harus selalu benar:

- penjualan jasa **=** `SUM(order_items.subtotal)`
- modal di kartu omset bersih **=** total di fitur Modal

### 3. Pelanggan tidak punya akun — token adalah kuncinya

Audiens sasaran adalah kalangan lanjut usia, jadi **tidak ada login pelanggan**. Pesanan diidentifikasi lewat token/cookie:

- `/cek/{token}` — halaman hasil pengecekan
- `/s/{token}` — struk
- `/e/{token}` — pembaca ebook

Token itu setara kata sandi. Konsekuensinya:

- URL bertoken **tidak boleh** bocor ke pihak ketiga. `JalurAnalitik` menyamarkannya sebelum dikirim ke Google Analytics, Meta Pixel dimatikan di halaman tersebut, dan `<meta name="referrer" content="origin">` mencegah kebocoran lewat header referrer. **Menambah route bertoken baru? Daftarkan di konstanta `PETA` milik `JalurAnalitik`.**
- Berkas unggahan pelanggan disimpan di disk privat, bukan di `public/`.
- Data pelanggan (nama, no HP, email, isi pesan, kredensial akun) tidak boleh keluar dari server.

### 4. Alur jasa pengecekan

Pelanggan membeli produk jasa → mengunggah dokumen → admin memproses → hasil tampil di `/cek/{token}`.

- Kuota dan dokumen dipisah **per jenis pemeriksaan** (plagiasi vs AI). Lihat `Order::kuotaPerJenis()`, `sisaKuotaJenis()`, `bisaUploadJenis()`.
- Cek AI **mewajibkan dokumen berbahasa Inggris** (`LanguageDetector`). Penandanya adalah kolom centang pada produk — sistem tidak menebak sendiri.
- Panel exclude hanya muncul untuk pengecekan kemiripan, tidak untuk cek AI.
- Jasa parafrase dihitung **per halaman**: modal = modal satuan × jumlah halaman yang dikerjakan.
- Laporan Turnitin kadang memuat dua hasil yang saling menimpa pada koordinat sama; `PlagiarismReader` mengambil yang terakhir muncul karena itulah lapisan yang terlihat.

### 5. RBAC ditulis sendiri, bukan paket

`User` punya relasi `Role` dan `Permission` dengan `hasAnyRole()`. Middleware terdaftar di `bootstrap/app.php`:

- `checkrole:admin,admin-mimin` — menjaga grup route admin
- `permission:` — menjaga berdasarkan permission
- `IdleTimeout`, `LastUserActivity`, `EnsureGuestToken`

**Konvensi lingkup data:** fitur yang menampilkan data milik banyak orang harus memakai pola `view_all_X` + `scopeVisibleTo`. Tanpa itu, karyawan bisa melihat data rahasia rekan kerjanya. Pengecualian yang disengaja: **Task Saya** menampilkan semua task bagi pemegang `view_all_task` — itu perilaku yang diinginkan, jangan diubah.

---

## Aset frontend: jebakan yang mahal

`public/build/` masuk `.gitignore`. Artinya **CSS dari `resources/css/` tidak ikut `git pull`** dan harus dikirim terpisah lewat rsync.

Ini pernah merusak halaman shop di produksi: markup baru sampai ke server, aturan `width` untuk ilustrasi SVG tidak — sehingga gambar memenuhi seluruh layar.

Dua pendekatan yang dipakai di repo ini:

| Pendekatan | Kapan dipakai |
|---|---|
| `<style>` inline di blade | Gaya khusus satu halaman (bundling, blog, empty state shop) — **selalu ikut `git pull`** |
| `resources/css/public-custom-styles.css` | Gaya yang benar-benar dipakai bersama banyak halaman — **butuh rsync `public/build/`** |

Kalau ragu, taruh inline di blade. Lebih aman daripada aset yang tidak sinkron.

---

## Tugas terjadwal

Sembilan perintah berjalan lewat scheduler (`routes/console.php`). Cron **wajib dipasang** di server; tanpa itu semuanya mati.

| Perintah | Jadwal | Fungsi |
|---|---|---|
| `orders:cancel-expired` | tiap menit | Batalkan pesanan kedaluwarsa |
| `payment:remind` | tiap menit | Pengingat pembayaran |
| `cart:remind-abandoned` | tiap 30 menit | Pengingat keranjang terbengkalai |
| `tasks:notify-deadlines` | 07:00 | Notifikasi tenggat task |
| `notifications:prune` | 00:05 | Bersihkan notifikasi lama |
| `comments:prune` | 00:10 | Bersihkan komentar lama |
| `activity-logs:prune` | 00:15 | Bersihkan log aktivitas |
| `jasa:bersihkan-draft` | 00:20 | Hapus draft unggahan lebih dari 7 hari |
| `points:reset-yearly` | 1 Januari | Reset poin tahunan |

Notifikasi lonceng, badge PWA, dan promo semuanya bergantung pada scheduler ini — bukan pada worker terpisah.

---

## Deployment

Detail lengkap ada di [`DEPLOYMENT.md`](DEPLOYMENT.md). Ringkasnya, produksi berjalan di shared hosting Hostinger dengan batasan berikut:

- `exec()` dan `proc_open()` **dinonaktifkan** → `composer install` harus memakai `--no-scripts`, lalu `php artisan package:discover` dijalankan manual
- Tidak ada `npm` di server → aset dibangun lokal lalu di-rsync
- `php artisan storage:link` gagal → pakai `ln -s` manual
- Branch: pengembangan di **`need`**, produksi menarik dari **`main`**

Setelah deploy:

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## SEO & analitik

Sudah terpasang, tidak perlu disetel ulang:

- `partials/seo.blade.php` — title, description, Open Graph, canonical, JSON-LD; dikonfigurasi lewat `config/seo.php` per nama route
- `/sitemap.xml` — dibuat dinamis oleh `SitemapController`, otomatis menyertakan produk dan artikel yang sudah terbit
- `robots.txt` — memblokir `/checkout`, `/cart`, `/payment/`, `/order/`, `/admin`, `/s/`, `/e/`
- Google Analytics 4 dan Meta Pixel di layout publik, dengan penyamaran URL bertoken (lihat konsep domain no. 3)

---

## Konvensi

- **Jalankan `vendor/bin/pint` sebelum commit.**
- Ikuti struktur fitur yang sudah ada: trio `List`/`Create`/`Edit`, komponen `Form` bersama, `partials/filter.blade.php`, dan kelas layout admin yang tersedia. Jangan membuat sistem desain baru.
- Penamaan domain dalam Bahasa Indonesia.
- Tanggal ditampilkan dengan `->locale('id')->translatedFormat(...)` karena `APP_LOCALE=en` — pertahankan pola ini.
- Sebagian besar model memakai `HasUuids`, jadi route-model binding memakai UUID.
- Perilaku domain tinggal di model (`booted()`, `scope*`, accessor), bukan di komponen Livewire.
