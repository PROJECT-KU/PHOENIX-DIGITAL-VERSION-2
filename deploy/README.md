# Deploy Phoenix

## Lewat SSH (utama)

```bash
./deploy/deploy-ssh.sh
```

Urutannya: **cadangkan → hitung baris → ambil kode → migrasi → bersihkan
cache → hitung ulang → bandingkan**. Memakai `set -e`, jadi satu langkah
gagal membuat sisanya tidak berjalan — deploy mustahil jalan tanpa
cadangan yang berhasil.

Cadangan disimpan di server pada `~/backup-phoenix/`:

- `db-<waktu>.sql.gz` — seluruh database
- `berkas-<waktu>.tar.gz` — `.env` dan `storage/app` (tidak ada di git,
  jadi tidak bisa dipulihkan dari GitHub)

Butuh pintasan `phoenix` di `~/.ssh/config` dan kunci `phoenix_ed25519`
terdaftar di hPanel → SSH Access → SSH Keys.

## Bila SSH tidak bisa dipakai

1. hPanel → GIT → Deploy (branch `main`, path `phoenixdigital`).
   Pesan "Deployment failed" di langkah composer itu NORMAL: `proc_open`
   dimatikan hosting. Pengambilan kodenya tetap berhasil.
2. Migrasi lewat Cron Job. Batas perintah **255 karakter SETELAH escape**,
   jadi `>` `&` `;` `~` membengkak — buat perintahnya sependek mungkin.
3. Kosongkan `bootstrap/cache/*.php` dan `storage/framework/views/*.php`.

## Catatan server

- Jalur aplikasi: `~/domains/phoenixdigitalwarehouse.com/public_html/phoenixdigital`
  — BUKAN `~/public_html`.
- Perintah SSH dibungkus `bash --noprofile --norc` untuk melewati
  `.bashrc`; tanpa itu perintah pernah menggantung (13 Agu 2026).
- phpMyAdmin menjalankan query dengan konteks `information_schema`, jadi
  `DATABASE()` menyesatkan. Tulis nama database eksplisit.
