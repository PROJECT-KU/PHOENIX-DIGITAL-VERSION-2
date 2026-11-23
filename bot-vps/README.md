# Bot Turnitin — versi VPS

Pengganti skrip Tampermonkey (`public/bot/phoenix-turnitin.user.js`) supaya
pengecekan plagiasi tidak lagi bergantung pada browser di laptop admin.

Terpasang di **103.247.11.17** (`server1.phoenixdigitalwarehouse.com`,
Ubuntu 24.04, 1 vCPU / 2 GB). Akses: `ssh vpsbot` dari laptop admin.

## Isi server

| Jalur | Isi |
|---|---|
| `/opt/botturnitin/bot.mjs` | bot (disalin dari repo) |
| `/opt/botturnitin/baca-bayar.mjs` | pembaca metode bayar, dijalankan di dalam halaman submitin |
| `/opt/botturnitin/cekplagiat.mjs` | penyedia cadangan |
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
- **`penuh`** — bekerja sungguhan: ambil antrean → isi form → tekan tombol
  bayarnya ("Gunakan Paket" / "Bayar dengan Saldo") → tunggu laporan → kirim
  hasil ke Phoenix.

## Menyalakan mode penuh

1. Pastikan akun submitin punya **paket yang masih bersisa**, atau saldo yang
   cukup untuk setidaknya satu dokumen.
2. `sudo sed -i 's/^MODE=.*/MODE=penuh/' /opt/botturnitin/.env`
3. `sudo systemctl restart phoenix-bot`
4. Pantau: `sudo tail -f /var/log/botturnitin/bot.log`

Mengembalikan ke aman: ubah `MODE=aman`, lalu restart.

## Metode bayar: paket dulu, saldo belakangan

Urutannya (keputusan 23 Nov 2026) — **paket** lebih dulu, karena sudah dibayar
di muka sehingga memakainya tidak menambah ongkos; **saldo** hanya dipakai saat
paketnya habis/kedaluwarsa, karena ia terpotong per dokumen. **QRIS tidak
pernah dipilih**: ia menunggu pembayaran manual, jadi pesanan akan menggantung.

Bentuk nyata barisnya (dipetakan 23 Nov 2026 di halaman sungguhan):

```html
<label class="pay-method-label" data-pm="package" data-pkg-id="6933">
  <span class="pm-text">Max</span>
  <span class="pm-badge">25x tersisa - s/d 07/12/2026</span>
<label class="pay-method-label pm-disabled" data-pm="wallet" data-balance="2100">
```

Baris yang tidak bisa dipakai diberi kelas `pm-disabled` **dan** radionya
`disabled`; keduanya diperiksa. Paket juga ditolak bila labelnya berbunyi
"0x tersisa" atau tanggal "s/d" sudah lewat. Sesudah kartunya ditekan, bot
memeriksa `#payMethodType`, `#payPackageId` (harus sama dengan `data-pkg-id`,
bukan `0`), dan bunyi tombol `#payBtn` — tombolnya berubah sendiri jadi "Bayar
dengan QRIS" kalau metodenya salah, dan itu pengaman terakhirnya.

"kuota" yang dilaporkan ke Telegram = sisa paket, atau berapa dokumen lagi yang
sanggup dibayar saldonya kalau paketnya sudah habis.

### Gladi bersih tanpa memakai kuota

```bash
sudo -u bot cp -a /opt/botturnitin/profil /tmp/uji-profil
sudo -u bot rm -f /tmp/uji-profil/Singleton*
sudo -u bot env PROFIL=/tmp/uji-profil node /opt/botturnitin/uji-bayar.mjs
```

Membaca metode bayar dengan kode yang sama persis dengan bot, menekan kartunya,
lalu mencetak hasil pemeriksaan dan memotret formnya — **tombol bayar tidak
pernah ditekan** dan Phoenix tidak dihubungi. Salinan profil dipakai supaya
tidak bentrok dengan Chromium milik service yang sedang hidup.

## Penyedia cadangan: cekplagiat.id

Dipakai **otomatis** hanya saat submitin.id tidak bisa dipakai (layanannya 503,
form tidak muncul, atau paketnya habis). Submitin tetap yang utama karena jauh
lebih murah. Matikan dengan `CEKPLAGIAT=0` di `.env`.

Yang perlu diketahui sebelum mengandalkannya:

| Hal | submitin.id | cekplagiat.id |
|---|---|---|
| Biaya | paket (sudah dibayar di muka) atau saldo (Rp12.900/berkas) | **18–22 koin ≈ Rp18.000–22.000/berkas** |
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

- Bot hanya membayar dengan **paket** atau **saldo**. QRIS tidak pernah
  dipilih; kalau bunyi tombolnya tidak cocok dengan metode yang dipilih,
  pengiriman dibatalkan.
- Angka "Total Bayar" **bukan** pengaman kecukupan: submitin tetap menampilkan
  harga per dokumen (Rp 12.900) walau yang dipotong kuota paket. Untuk saldo
  angka itu dibandingkan dengan saldonya; untuk paket sengaja tidak.
- Versi Turnitin tetap **V1 dulu, V2 hanya bila V1 dicabut** submitin.
- Nama berkas & judul memakai penanda `PD-xxxxxxxx-INV-…`. Nama, nomor HP, dan
  surel pelanggan tidak pernah dikirim ke submitin; nomor WhatsApp yang dipakai
  adalah milik akun submitin sendiri.
- Hasil hanya diambil bila halaman status memuat penanda yang cocok, supaya
  laporan tidak tertukar antar pesanan.

## Memperbarui bot

Dari laptop, di dalam repo:

```
rsync -az bot-vps/bot.mjs bot-vps/baca-bayar.mjs bot-vps/cekplagiat.mjs \
          bot-vps/uji-bayar.mjs bot-vps/uji-cekplagiat.mjs vpsbot:/opt/botturnitin/
ssh vpsbot 'chown bot:bot /opt/botturnitin/*.mjs && systemctl restart phoenix-bot'
```

`bot.mjs` mengimpor `baca-bayar.mjs` dan `cekplagiat.mjs` — menyalin `bot.mjs`
sendirian bisa membuat service gagal start.

## Catatan pemasangan (sudah dikerjakan)

- Login SSH dengan password **dimatikan**; hanya kunci `~/.ssh/phoenix_vps_bot`.
- `ufw` aktif (hanya SSH), `fail2ban` jalan.
- Swap 2 GB (bawaan VPS cuma 256 MB — terlalu kecil untuk Chromium).
- Node 22 + Playwright + Chromium, berjalan sebagai user `bot` (bukan root).
