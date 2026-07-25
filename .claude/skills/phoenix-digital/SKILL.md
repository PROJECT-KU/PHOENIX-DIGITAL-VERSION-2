---
name: phoenix-digital
description: Struktur, aturan wajib, konvensi desain, dan alur kerja (git/deploy/cron/notifikasi) proyek PHOENIX-DIGITAL-VERSION-2 — e-commerce Laravel 11 + Livewire 3, admin "lemon by acm". Baca SEBELUM mengerjakan fitur/perbaikan di repo ini agar tidak melanggar batasan yang menyebabkan regresi.
---

# Phoenix Digital (PHOENIX-DIGITAL-VERSION-2)

E-commerce Phoenix Digital: menjual akun premium, lisensi, tools AI, dan **JASA pengecekan plagiasi/AI (Turnitin)**. Stack: **Laravel 11**, **Livewire 3 + Volt**, admin panel **"lemon by acm"**. Audiens customer lansia → **TANPA login customer**; pesanan diidentifikasi via token/cookie (`share_token`, `guest_token`).

## ATURAN WAJIB (melanggar = regresi / kebocoran data)

- **JANGAN ubah logic/alur produk NON-JASA.** User tak mau testing ulang. Semua perubahan harus **ADITIF** (tambah, bukan ubah alur lama). Kalau ragu, tanya dulu.
- **Data customer (nama, no_hp, email, isi pesan, username/password akun) TIDAK BOLEH keluar dari server.** Jangan kirim ke layanan eksternal, jangan log, jangan commit.
- **Jangan buat data test di DB.** Untuk verifikasi pakai `DB::transaction()` + `rollBack()`. (Pengecualian: hanya bila user eksplisit minta data dummy LOKAL — jangan pernah di production.)
- **`phpunit.xml`: baris SQLite in-memory harus tetap aktif.** Jangan `migrate:fresh`/`RefreshDatabase` di DB nyata (pernah menghapus DB lokal).
- **Aset build tidak ikut deploy.** `public/build` di-gitignore → CSS/JS dari Vite TIDAK sampai server → **tulis CSS/JS INLINE di blade**.
- **Diagnostics editor sering FALSE POSITIVE** untuk blade, `@php`, `?->`, named-arg (`message:`), namespace `Public\`, Livewire `->layout()`. **Verifikasi nyata dengan `php -l` dan `Blade::compileString`**, jangan percaya panel Problems.

## Alur Git & Deploy

- **Branch flow: `need → dev → main`** (`git merge --no-edit`, umumnya fast-forward). Commit di `need`, lalu merge maju.
- Branch lokal `need` **sering tertinggal** dari `origin/main`. Sebelum commit: `git stash` → `git merge --ff-only origin/main` → `git stash pop` → baru commit. Selalu cek `git diff --stat` bersih (hanya file yang dimaksud).
- **Deploy server** (Hostinger shared, SSH port 65002): path Laravel = `~/domains/phoenixdigital.id/public_html/phoenixdigital`. Deploy = `git checkout main && git pull origin main && php artisan optimize:clear`. Migrasi: `php artisan migrate --force`.
- **Kredensial (SSH password, CRON_TOKEN) dibagikan terpisah — JANGAN commit ke repo.** Rotasi password SSH setelah tiap deploy.
- **crontab CLI diblokir**; tinker REPL butuh proc_open. Untuk skrip kompleks di server pakai `php artisan tinker --execute="eval(base64_decode('<b64>'))"`. SSH otomatis via `expect` (hapus helper berisi password setelah dipakai).
- **Env server:** `proc_open` AKTIF di CLI **dan** SAPI web (schedule:run via HTTP berhasil). `ini_set('memory_limit')` bisa dinaikkan (dipakai 3072M utk parse PDF). Upload limit dinaikkan ke 100MB.

## Cron / Scheduler

- **Cron utama = hPanel GUI** (Advanced → Cron Jobs), bukan SSH. Entri: `* * * * *` command `domains/.../phoenixdigital/artisan schedule:run` (hPanel auto-prefix `/usr/bin/php /home/u463281286/`). Output ditulis ke `~/.logs/cronjob_*` (cek mtime & isi untuk membuktikan cron menembak; atau tombol "Lihat Output").
- Scheduled (`routes/console.php`): promo toggle, `qris:cek-pembayaran`, `orders:cancel-expired`, `payment:remind` (semua per menit, `withoutOverlapping(10)`), plus tugas harian (prune, dll).
- **Cron hPanel pernah mati diam-diam** → gejala: order QRIS nyangkut `pending`, tidak auto-cancel. Diagnosa via `~/.logs/cronjob_*` / "Lihat Output".
- **Cron cadangan HTTP**: route `GET /cron/run/{token}` (`CronController`, token dari `config/cron.php` ← `env('CRON_TOKEN')`, kosong = 404). Untuk dipicu layanan eksternal (cron-job.org) tiap menit sebagai jaring pengaman bila cron hPanel mati. Menjalankan `schedule:run`; fallback in-process (`qris:cek-pembayaran`/`orders:cancel-expired`/`payment:remind`) bila proc_open diblokir. **Bersifat pasif — hanya "jalan" bila ada pemanggil eksternal terdaftar.**

## Model Order & alur pesanan

- **Status:** `pending, draft, paid, processing, completed, cancelled`. **Income dihitung LANGSUNG dari status** (paid/processing/completed) — bukan dari tabel `cash_flows`. Jadi meng-cancel order otomatis melepas income.
- **Cancel order yang BENAR** (lewat Eloquent agar observer jalan): payment `pending`→`expire`, status→`cancelled`, `paid_at`→`null`. Tombol **"Batalkan Pesanan"** di `OrderDetail::batalkanPesanan()`. `OrderObserver` → `SyncOrderPrivateCostAction` otomatis **menghapus entri modal** saat status bukan paid/processing/completed. JANGAN cancel via raw SQL (melewatkan observer → modal/income tak sinkron).
- `OrderObserver` (updated): `SyncOrderPrivateCostAction` (catat/hapus modal per item), `updatePoints` (customer member aktif), poin referral (completed + referrer).
- **Pengaman ProcessOrder** ("belum lunas"): sebelum kirim akun, bila `qris_dinamis` **tanpa** payment `settlement` → banner + konfirmasi SweetAlert (`belumLunas` + `lunasDikonfirmasi` + event `konfirmasi-belum-lunas`). Additif, tidak memblokir (transfer manual tetap bisa lanjut). Order yang sudah lunas / non-QRIS tidak terpengaruh.
- QRIS dinamis = **polling** (tak ada callback); status `paid` hanya terdeteksi saat `qris:cek-pembayaran` jalan (butuh cron). `orders:cancel-expired` membatalkan order kedaluwarsa (setelah QRIS terakhir expire), dengan pengaman `checkStatus` supaya order yang ternyata sudah dibayar tidak ikut dibatalkan.

## Jasa pengecekan plagiasi/AI (Turnitin)

- `OrderUpload` (jenis `plagiasi`/`ai`, status `menunggu/diproses/selesai/dibatalkan`) + setelan exclude (kutipan/bibliografi/sumber kecil/ambang). `PlagiarismReader` membaca % dari PDF (cap 40MB, memory 3072M).
- **Groupy (bot Discord) = MANUAL, tidak bisa diotomatiskan dengan aman.** Tak ada API, tak ada submit/hasil via email (submit & hasil keduanya di Discord). Otomatisasi = self-bot = **risiko akun Groupy di-banned**. Kartu panduan Groupy di detail order **sudah dihapus** (jangan bangun ulang tanpa diminta). Otomatisasi penuh hanya mungkin bila pindah ke penyedia cek yang punya email/API.
- `/cek/{token}`: halaman customer upload; link mati 24 jam setelah kuota pengecekan habis.

## Konvensi Desain / UI

- **Konfirmasi seragam (SweetAlert glossy):** tombol `class="... pcek-konfirmasi"` + `data-action="namaMetodeLivewire"` (+ `data-arg`, `data-title`, `data-text`, `data-confirm`, `data-icon`). Handler global di blade menampilkan Swal (`btn-glossy-confirm`/`btn-glossy-cancel`) lalu `Livewire.find(...).call(method[, arg])`. Pakai ini untuk aksi destruktif, bukan `wire:confirm` bawaan.
- **Toast/sukses:** `session()->flash('success', ...)` atau `dispatch('order-updated', message: ...)` → satu SweetAlert. Hindari toast + swal dobel.
- **Tombol dengan ikon+teks:** `display:inline-flex; align-items:center; gap:4px; white-space:nowrap` agar ikon sejajar & tak wrap.
- **Cash flow & dashboard:** periode **21–20** (`App\Support\PeriodeGaji`). "Pendapatan Hari Ini" = `Order` status paid/processing/completed hari ini (bukan cash_flows). Layout kartu: 2 atas + 3 bawah.
- **Kompres PDF di server TIDAK feasible** (Imagick malah membengkakkan Turnitin PDF; proc_open utk tools eksternal diblokir di sebagian konteks) → solusi = naikkan batas upload, bukan kompres.

## Notifikasi & Suara

- **Foreground** (tab/PWA terbuka): `App\Livewire\Layout\NotifPoller` (`<livewire:layout.notif-poller/>` di `layouts/app` dalam `@auth`) memegang `wire:poll.5s.keep-alive` + `<span id="ttl-badge">`. **SENGAJA terpisah dari sidebar** (poll sidebar menghilangkan `.active/.open` karena `request()->routeIs()`). Memicu badge judul tab "(N) lemon" + popup OS + suara **hanya saat ada HAL BARU** (order paid / testimoni / ulasan / helpdesk bertambah). Popup memakai `serviceWorker.showNotification` (PWA Android melarang `new Notification()`), fallback ke konstruktor.
- **Background** (situs tertutup): Web Push (`minishlink/web-push`, VAPID, tabel `push_subscriptions`, `public/sw.js` handler `push`, listener `SendWebPushNotification` on `NotificationSent`). Aktifkan via lonceng → **"🔔 Aktifkan notifikasi perangkat"** (`registerSub`, dedup per host + `device_id`). Suara kustom saat BACKGROUND tidak mungkin di web/PWA (OS pakai suara bawaan).
- **Suara:** `window.lemonChime` (Web Audio, jingle C–E–G–C, debounce 3s) + `ucapLemon` (TTS "lemon", voice perempuan). Toggle **"🔊 Suara notifikasi"** (`localStorage['lemon-sound']`). Butuh gestur user untuk unlock AudioContext.
- `App\Notifications\PesananBaru::kirim($order)` dipicu di `Order::booted()` `static::updated` saat status→`paid` (guard `wasChanged` + original≠paid), ke admin ber-permission `view_pemesanantoko`.
- **App Badge (macOS) finicky**: butuh System Settings → Notifications → Chrome ON; badge di ikon Dock app terinstall (konteks terpisah dari tab). Android andal.

## Data scoping (keamanan baris)

- Konvensi own-vs-all: `view_all_X` + `scopeVisibleTo` agar data rahasia tak bocor saat karyawan mengakses. "Task Saya" sengaja menampilkan semua task untuk admin (`view_all_task`); karyawan hanya miliknya.

## Cara aman bekerja di repo ini

1. Ubahan **aditif**; jangan sentuh alur non-jasa.
2. Verifikasi tiap blade/PHP dengan `php -l` + `Blade::compileString` (abaikan false-positive editor).
3. Push lewat `need → dev → main`; deploy hanya bila diminta; jangan commit rahasia.
4. Untuk operasi data production, pakai Eloquent (agar observer jalan), bukan raw SQL.
