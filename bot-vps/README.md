# Bot Turnitin — versi VPS

Pengganti skrip Tampermonkey (`public/bot/phoenix-turnitin.user.js`) supaya
pengecekan plagiasi tidak lagi bergantung pada browser di laptop admin.

Terpasang di **103.247.11.17** (`server1.phoenixdigitalwarehouse.com`,
Ubuntu 24.04, 1 vCPU / 2 GB). Akses: `ssh vpsbot` dari laptop admin.

## Isi server

| Jalur | Isi |
|---|---|
| `/opt/botturnitin/bot.mjs` | bot (berkas ini, disalin dari repo) |
| `/opt/botturnitin/.env` | **kredensial** — hak akses 600, milik `bot`. TIDAK PERNAH masuk repo |
| `/opt/botturnitin/profil/` | profil Chromium; sesi submitin bertahan di sini |
| `/opt/botturnitin/potret/` | tangkapan layar mode aman |
| `/var/log/botturnitin/bot.log` | catatan jalannya bot (dirotasi mingguan) |
| `/etc/systemd/system/phoenix-bot.service` | service; hidup lagi setelah reboot |

## Dua mode

`MODE` di `.env` menentukan perilakunya:

- **`aman`** (bawaan) — service **tidak menyentuh antrean sama sekali**, hanya
  berdetak ke Phoenix. Pesanan pelanggan sungguhan tidak akan pernah diambil
  lalu ditandai gagal. Pengujian satu tugas dilakukan manual:
  `sudo -u bot node /opt/botturnitin/bot.mjs --sekali`
- **`penuh`** — bekerja sungguhan: ambil antrean → isi form → "Gunakan Paket" →
  tunggu laporan → kirim hasil ke Phoenix.

## Menyalakan mode penuh

1. Pastikan akun submitin punya **paket Standard** yang masih bersisa.
2. `sudo sed -i 's/^MODE=.*/MODE=penuh/' /opt/botturnitin/.env`
3. `sudo systemctl restart phoenix-bot`
4. Pantau: `sudo tail -f /var/log/botturnitin/bot.log`

Mengembalikan ke aman: ubah `MODE=aman`, lalu restart.

## Pengaman yang TIDAK boleh dilepas

- Bot hanya membayar dengan **paket Standard**. QRIS dan Saldo tidak pernah
  dipilih; kalau tombolnya bukan "Gunakan Paket", pengiriman dibatalkan.
- Angka "Total Bayar" **bukan** pengaman: submitin tetap menampilkan harga per
  dokumen (Rp 5.000) walau dibayar dengan paket.
- Nama berkas & judul memakai penanda `PD-xxxxxxxx-INV-…`. Nama, nomor HP, dan
  surel pelanggan tidak pernah dikirim ke submitin; nomor WhatsApp yang dipakai
  adalah milik akun submitin sendiri.
- Hasil hanya diambil bila halaman status memuat penanda yang cocok, supaya
  laporan tidak tertukar antar pesanan.

## Memperbarui bot

Dari laptop, di dalam repo:

```
rsync -az bot-vps/bot.mjs vpsbot:/opt/botturnitin/bot.mjs
ssh vpsbot 'chown bot:bot /opt/botturnitin/bot.mjs && systemctl restart phoenix-bot'
```

## Catatan pemasangan (sudah dikerjakan)

- Login SSH dengan password **dimatikan**; hanya kunci `~/.ssh/phoenix_vps_bot`.
- `ufw` aktif (hanya SSH), `fail2ban` jalan.
- Swap 2 GB (bawaan VPS cuma 256 MB — terlalu kecil untuk Chromium).
- Node 22 + Playwright + Chromium, berjalan sebagai user `bot` (bukan root).
