# Agen Telegram — Phoenix Digital

Bot Telegram untuk memantau dan memperbaiki masalah operasional dari HP: cron mati,
order QRIS nyangkut, antrian macet, error menumpuk.

---

## Batas kewenangan (baca dulu)

Bot ini **tidak bisa menjalankan perintah bebas**. Satu-satunya pintu eksekusi adalah
daftar putih di [`app/Services/Telegram/AksiAgen.php`](app/Services/Telegram/AksiAgen.php).
Tidak ada shell, tidak ada `artisan` sembarang, tidak ada `eval`. Menambah kemampuan bot
berarti menambah entri di file itu secara sadar.

Konsekuensinya: kalau token bot bocor, kerusakan maksimal terbatas pada daftar itu —
penyerang bisa membersihkan cache dan membatalkan order kedaluwarsa, tapi tidak bisa
membaca database, mengubah kode, atau mengambil alih server.

**Data customer tidak pernah dikirim ke Telegram.** Balasan bot hanya memuat angka
agregat, status, nomor order, dan pesan error teknis — bukan nama, no_hp, email, isi
pesan, atau kredensial akun. Ini mengikuti aturan wajib proyek (lihat `CLAUDE.md`).

---

## Perintah

| Perintah | Fungsi | Mengubah sistem? |
|---|---|---|
| `/status` | Ringkasan: cron, order nyangkut, antrian, database, disk, error | tidak |
| `/cron` | Cron server masih menyala? Kapan terakhir jalan? | tidak |
| `/cron_jalankan` | Paksa jalankan tugas terjadwal sekarang | **ya** |
| `/qris` | Tanya penyedia QRIS, tandai order yang sudah dibayar | **ya** |
| `/order_nyangkut` | Daftar order pending lewat batas bayar | tidak |
| `/order_batalkan` | Batalkan order kedaluwarsa | **ya** |
| `/trafik` | Kunjungan & pengunjung unik, banding kemarin, halaman terpopuler | tidak |
| `/antrian` | Jumlah job menunggu & gagal | tidak |
| `/log` | Error terbanyak 24 jam terakhir | tidak |
| `/cache_bersih` | `optimize:clear` | **ya** |
| `/bantuan` | Daftar perintah | tidak |

Daftar ini juga terdaftar sebagai **menu Telegram** — ketik `/` di chat dan
semuanya muncul lengkap dengan keterangan. Menu disinkronkan otomatis oleh
`telegram:webhook pastikan` (tiap jam) dan hanya ditulis ulang bila isinya
berubah. Menambah aksi baru di `AksiAgen::daftar()` otomatis ikut ke menu —
tidak perlu mengatur apa pun di @BotFather.

---

## Pemasangan

### 1. Isi `.env`

```env
TELEGRAM_BOT_TOKEN=<token dari @BotFather>
TELEGRAM_WEBHOOK_SECRET=<acak, lihat di bawah>
TELEGRAM_ALLOWED_CHAT_IDS=
```

Buat secret acak:

```bash
php -r "echo bin2hex(random_bytes(24)).PHP_EOL;"
```

`TELEGRAM_BOT_TOKEN` kosong → webhook membalas 404, bot mati total. Itu cara
mematikannya kalau suatu saat perlu.

### 2. Daftarkan webhook

**Biasanya tidak perlu dikerjakan manual.** Scheduler menjalankan
`telegram:webhook pastikan` tiap jam: begitu `.env` terisi, webhook didaftarkan
sendiri — tanpa SSH. Ini penting karena `crontab` CLI diblokir di hosting ini.
Perintah itu juga menyembuhkan diri kalau webhook lepas.

Kalau tidak mau menunggu satu jam:

```bash
php artisan config:clear
php artisan telegram:webhook pasang
```

Telegram **hanya menerima HTTPS**, jadi `APP_URL` di `.env` harus `https://...`.
Untuk memakai URL lain: `php artisan telegram:webhook pasang --url=https://domain.anda/telegram/webhook`

Selama `TELEGRAM_BOT_TOKEN` atau `TELEGRAM_WEBHOOK_SECRET` masih kosong,
`pastikan` berhenti seketika tanpa memanggil Telegram sama sekali — jadi aman
ikut ter-deploy dalam keadaan belum dikonfigurasi.

### 3. Daftarkan chat ID Anda

Selama `TELEGRAM_ALLOWED_CHAT_IDS` masih kosong, bot ada di **mode pendaftaran**: ia
hanya membalas chat ID Anda dan **tidak menjalankan perintah apa pun**.

Kirim pesan apa pun ke bot → ia membalas dengan chat ID Anda → masukkan ke `.env`:

```env
TELEGRAM_ALLOWED_CHAT_IDS=123456789
```

Lalu:

```bash
php artisan config:clear
php artisan telegram:webhook tes
```

Beberapa admin: pisahkan dengan koma (`123456789,987654321`).

### 4. Periksa

```bash
php artisan telegram:webhook info
```

---

## Deteksi cron mati

Scheduler menulis "denyut nadi" tiap menit ke cache, dan **membedakan sumbernya**:

- **`cli`** — cron asli server (hPanel). Inilah yang harus menyala tiap menit.
- **`web`** — jaring pengaman: `KickScheduler` (dipicu trafik pengunjung) atau
  `/cron/run/{token}` (dipicu cron-job.org).

Jadi `/cron` bisa membedakan dua keadaan yang selama ini tampak sama:

- *Cron hPanel mati, tapi tugas tetap jalan lewat trafik* → bot memberi tahu bahwa
  jaring pengaman sedang menutupi, dan cron tetap perlu diperbaiki (jaring pengaman
  berhenti kalau situs sepi).
- *Semuanya mati* → order QRIS akan nyangkut. Tindakan: `/cron_jalankan` sebagai
  pertolongan pertama, lalu perbaiki cron di hPanel.

Ambang basi 5 menit (`CronHeartbeat::AMBANG_DETIK`).

### Peringatan otomatis

`cron:pantau` berjalan tiap 5 menit dan mengirim pesan Telegram **hanya saat
keadaan berubah** — mis. cron hPanel mati, atau pulih lagi. Tidak membanjiri chat.

**Bot tidak bisa menyalakan kembali cron yang mati.** Cron hPanel diatur di
panel Hostinger dan `crontab` CLI diblokir di hosting ini; cron-job.org adalah
layanan luar dengan akun sendiri. Yang tersedia adalah `/cron_jalankan` —
menjalankan tugasnya sekarang juga sebagai pertolongan pertama — plus peringatan
di atas supaya Anda tahu segera, bukan setelah order menumpuk.

**Batas yang perlu disadari:** pemantau ini sendiri berjalan lewat scheduler.
Kalau SEMUA pemicu mati berbarengan, tidak ada yang menjalankannya, jadi tidak
ada peringatan yang terkirim. Ia menutup kasus yang paling sering terjadi —
satu pemicu mati sementara yang lain hidup. Untuk mati total, nyalakan
notifikasi kegagalan bawaan cron-job.org sebagai lapis terakhir.

---

## Lapisan AI (opsional, mati secara bawaan)

Tanpa ini bot sudah berfungsi penuh lewat perintah slash — gratis, tidak memanggil
layanan luar apa pun.

Kalau diaktifkan, Anda bisa mengetik bahasa bebas ("cron mati ya? tolong cek") dan model
memetakannya ke satu perintah di atas.

**AI tidak pernah mengeksekusi apa pun.** Keluarannya cuma sebuah string kunci, lalu
`AksiAgen::jalankan()` memvalidasi ulang kunci itu terhadap daftar putih. Model yang
berhalusinasi — atau balasan yang disusupi — tetap tidak bisa menambah kemampuan bot.

Yang dikirim keluar hanya teks perintah Anda + nama & keterangan aksi. Tidak ada data
customer, kredensial, atau hasil eksekusi.

### Endpoint OpenAI-compatible (9Router, OpenRouter, dll.)

```env
TELEGRAM_AI_DRIVER=openai
TELEGRAM_AI_BASE_URL=http://127.0.0.1:20128/v1
TELEGRAM_AI_API_KEY=<kunci>
TELEGRAM_AI_MODEL=<nama model>
```

Catatan untuk 9Router: ia berjalan sebagai proses yang menyala terus, jadi **tidak bisa
di hosting shared** — perlu VPS atau mesin lain yang bisa dijangkau server ini. Kalau
9Router ada di laptop Anda, server produksi tidak akan bisa memanggilnya.

### Anthropic langsung

```env
TELEGRAM_AI_DRIVER=anthropic
TELEGRAM_AI_BASE_URL=https://api.anthropic.com/v1
TELEGRAM_AI_API_KEY=<kunci dari console.anthropic.com>
TELEGRAM_AI_MODEL=claude-opus-5
```

Langganan claude.ai (Pro/Max) **tidak** memberi kredit API — tagihannya terpisah dan
diisi di Console.

Matikan lapisan AI kapan saja dengan mengosongkan `TELEGRAM_AI_API_KEY`.

---

## Berkas terkait

| Berkas | Isi |
|---|---|
| `config/telegram.php` | Konfigurasi |
| `app/Services/Telegram/AksiAgen.php` | **Daftar putih aksi** — satu-satunya pintu eksekusi |
| `app/Services/Telegram/TelegramClient.php` | Pembungkus Bot API |
| `app/Services/Telegram/AgenAi.php` | Lapisan AI opsional |
| `app/Http/Controllers/TelegramWebhookController.php` | Webhook + lapisan keamanan |
| `app/Console/Commands/TelegramWebhook.php` | `telegram:webhook` |
| `app/Support/CronHeartbeat.php` | Denyut nadi scheduler |

Semua memakai `Http` facade bawaan Laravel, **tanpa dependensi composer baru** — sebab
`/vendor` di-gitignore dan deploy tidak menjalankan `composer install`, jadi paket baru
tidak akan sampai ke server.

---

## Masalah umum

**Bot diam saja.** `php artisan telegram:webhook info` — cek `last_error_message`, dan
pastikan chat ID Anda ada di daftar izin. Setelah mengubah `.env`, **wajib**
`php artisan config:clear`.

**"Telegram hanya menerima webhook HTTPS".** `APP_URL` masih `http://`. Perbaiki, atau
pakai `--url=`.

**Webhook 404 padahal sudah dipasang.** `TELEGRAM_BOT_TOKEN` atau
`TELEGRAM_WEBHOOK_SECRET` kosong di server (keduanya kosong = 404 by design).

**`/cron` bilang "belum pernah".** Wajar tepat setelah dipasang — tunggu scheduler jalan
sekali. Kalau tetap kosong setelah beberapa menit, cron memang mati.
