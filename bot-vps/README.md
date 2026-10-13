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

## Penyedia cadangan: cekplagiat.id

Dipakai **otomatis** hanya saat submitin.id tidak bisa dipakai (layanannya 503,
form tidak muncul, atau paketnya habis). Submitin tetap yang utama karena jauh
lebih murah. Matikan dengan `CEKPLAGIAT=0` di `.env`.

Yang perlu diketahui sebelum mengandalkannya:

| Hal | submitin.id | cekplagiat.id |
|---|---|---|
| Biaya | paket (±Rp5.000/berkas) | **18–22 koin ≈ Rp18.000–22.000/berkas** |
| Batas berkas | 50 MB | **10 MB** (Phoenix menerima 20 MB → PDF dikecilkan dulu) |
| Laporan | Turnitin V1 lengkap | **Basic Report** (skor + PDF, tanpa rincian sumber; Advanced "Dalam Perbaikan") |
| Saringan | kutipan, pustaka, angka sumber/kata | hanya kutipan & pustaka — **tugas dengan batas angka ditolak, dikerjakan admin** |
| Login | email + sandi (otomatis) | **Google saja** → sesi dititipkan manual, lihat di bawah |

### Menitipkan sesi (sekali, dan diulang kalau sesinya habis)

Bot tidak bisa login Google sendiri. Caranya: admin login di browser biasa,
lalu cookie `cekplagiat.id`-nya disalin ke VPS sebagai
`/opt/botturnitin/cekplagiat-cookie.json` (hak akses **600**, milik `bot`).
Berkas itu setara kunci masuk akun, jadi **tidak pernah masuk repo**
(sudah di `.gitignore`).

Kalau sesinya habis, bot berhenti sopan dengan pesan "Sesi cekplagiat.id sudah
habis" dan menunggu cookie baru — tidak pernah menebak sandi.

### Gladi bersih tanpa memakai koin

```bash
sudo -u bot node /opt/botturnitin/uji-cekplagiat.mjs
```

Mengisi form dengan dokumen contoh, mencetak sisa koin dan biayanya, memotret
jendelanya, lalu berhenti **sebelum** "Proses dan Unggah". Koin tidak terpakai
dan antrean Phoenix tidak disentuh.

### Kalau koinnya kurang

Bot melaporkan kegagalan sekali, lalu **diam 30 menit** sebelum mencoba lagi.
Tanda "kuota habis" milik Phoenix sengaja TIDAK dipakai untuk ini: tanda itu
lengket sampai admin membersihkannya, jadi bot akan tetap diam walaupun
submitin sudah pulih. Isi koin di <https://www.cekplagiat.id/u>.

## Berkas kebesaran

Submitin menolak berkas **di atas 50 MB**. Bot mengecilkan PDF yang melewati
`BATAS_MB` (bawaan 48) memakai Ghostscript — bertahap dari `/printer` ke
`/ebook` lalu `/screen`, berhenti begitu muat.

**Yang dikecilkan hanya gambarnya.** Sesudah dikecilkan, jumlah kata pada
lapisan teks dibandingkan dengan berkas asli; kalau berkurang, hasilnya dibuang
dan bot memilih gagal terang-terangan daripada mengirim naskah yang isinya
sudah berkurang. Nama berkas dipertahankan karena memuat penanda `PD-…`.

Terbukti 20 Sep 2026: 92,80 MB → 15,18 MB dalam 18,8 detik, teks 7.560 kata
**identik kata per kata**.

Berkas non-PDF (DOCX/DOC/TXT) yang kebesaran tidak dikecilkan — bot melapor
gagal dengan pesan jelas supaya dikerjakan manual.

Butuh `ghostscript` + `poppler-utils` di server (sudah terpasang).

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
