# Deployment — Scheduler (cPanel Shared Hosting)

Semua tugas terjadwal aplikasi didefinisikan **di dalam repo** (`routes/console.php`), jadi
otomatis ikut ter-deploy. Jadwal yang aktif:

| Jadwal | Waktu | Fungsi |
|---|---|---|
| Promo aktif/nonaktif | tiap menit | update status promo |
| `tasks:notify-deadlines` | 07:00 | notifikasi deadline & keterlambatan task |
| `notifications:prune` | 00:05 | hapus notifikasi bulan lama (anti-bloat DB) |
| `comments:prune` | 00:10 | hapus komentar task (chat + file/gambar) tahun lalu (anti-bloat DB & storage) |

Agar semua itu berjalan, Laravel butuh **satu cron** yang memanggil `schedule:run` tiap menit.
Di cPanel, cron **tidak** bisa ikut git — jadi dipasang **SEKALI** lewat panel. Setelah itu,
setiap deploy berikutnya cukup upload kode; scheduler tetap hidup tanpa perubahan apa pun.

## Langkah SEKALI di cPanel (setelah deploy pertama)

1. Login cPanel → **Cron Jobs**.
2. Common Settings: pilih **Once Per Minute (`* * * * *`)**.
3. Command (sesuaikan path PHP & lokasi `artisan` — lihat catatan di bawah):

   ```
   * * * * * /usr/local/bin/php /home/USERNAME/APP_PATH/artisan schedule:run >> /dev/null 2>&1
   ```

4. Add New Cron Job. **Selesai** — tidak perlu diulang di deploy berikutnya.

### Menemukan path yang benar
- **PHP binary**: cek di cPanel → *Select PHP Version* / *MultiPHP Manager*. Umumnya
  `/usr/local/bin/php`, atau versi spesifik seperti `/opt/cpanel/ea-php82/root/usr/bin/php`.
- **Lokasi `artisan`**: folder root Laravel (bukan `public_html`). Contoh:
  `/home/USERNAME/laravel-app/artisan`. Jalankan `pwd` via Terminal cPanel di folder proyek.

## Opsi: deploy via cPanel Git Version Control (`.cpanel.yml`)

Jika Anda memakai **cPanel → Git Version Control**, file `.cpanel.yml` di repo akan
dijalankan tiap "Deploy HEAD Commit". Isinya: salin kode ke folder aplikasi, jalankan
`migrate --force` + clear cache, dan **memasang cron `schedule:run` otomatis** (idempoten,
tidak dobel). Dengan begitu deploy pertama sekaligus memasang scheduler.

Sebelum dipakai, **edit placeholder** di `.cpanel.yml`:
- `USERNAME/APP_DIR` → path root Laravel (folder berisi `artisan`).
- `PHP_BIN` → path PHP dari MultiPHP Manager.

Catatan: sebagian shared host **memblokir perintah `crontab` dari proses deploy**. Bila cron
tidak ikut terpasang (cek dengan `crontab -l`), pasang sekali lewat panel seperti langkah di
atas — sekali itu saja.

Jika deploy via **FTP/upload manual**, `.cpanel.yml` diabaikan; cukup pakai cron panel (1×).

## Verifikasi
Lewat Terminal cPanel (atau SSH) di folder proyek:

```bash
php artisan schedule:list      # lihat daftar jadwal
php artisan notifications:prune # tes hapus notifikasi lama (manual)
php artisan schedule:run        # jalankan scheduler sekali secara manual
```

## Catatan
- Cron ini **cukup sekali**. Deploy berikutnya = upload kode saja.
- Timezone pergantian bulan mengikuti `config/app.php` (`timezone`). Pastikan sesuai
  (mis. `Asia/Jakarta`).
- Jika host membatasi cron per menit, minimal set setiap 1–5 menit; jadwal harian tetap jalan.

---

## Lokal (macOS, dev) — sudah terpasang
Cron dev terpasang lewat `crontab`:
```
* * * * * cd "/path/PHOENIX-DIGITAL-VERSION-2" && /opt/homebrew/bin/php artisan schedule:run >> /dev/null 2>&1
```
Catatan macOS: beri **Full Disk Access** ke `/usr/sbin/cron` bila proyek ada di `~/Documents`.
Hanya berjalan saat Mac menyala.
