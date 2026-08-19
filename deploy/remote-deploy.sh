#!/bin/bash
# Dijalankan DI SERVER. set -e: satu langkah gagal, sisanya tidak jalan —
# jadi deploy mustahil berjalan tanpa cadangan yang berhasil.
set -e

APP=~/domains/phoenixdigitalwarehouse.com/public_html/phoenixdigital
CAD=~/backup-phoenix
D=$(date +%F-%H%M%S)
mkdir -p "$CAD"
cd "$APP"

baca_env() { grep -m1 "^$1=" .env | cut -d= -f2- | tr -d '"' | tr -d "'"; }
DBN=$(baca_env DB_DATABASE); DBU=$(baca_env DB_USERNAME); DBP=$(baca_env DB_PASSWORD)

echo "===== 1. CADANGKAN DATABASE ====="
mysqldump -u"$DBU" -p"$DBP" "$DBN" | gzip > "$CAD/db-$D.sql.gz"
ls -lh "$CAD/db-$D.sql.gz"
# Cadangan kosong = cadangan palsu. Lebih baik berhenti sekarang.
test "$(stat -c%s "$CAD/db-$D.sql.gz" 2>/dev/null || stat -f%z "$CAD/db-$D.sql.gz")" -gt 10000

echo "===== 2. CADANGKAN BERKAS DI LUAR GIT ====="
# .env dan storage/app tidak ada di repositori, jadi tidak bisa dipulihkan
# dari GitHub. Inilah yang benar-benar tak tergantikan.
tar czf "$CAD/berkas-$D.tar.gz" .env storage/app 2>/dev/null
ls -lh "$CAD/berkas-$D.tar.gz"

echo "===== 3. HITUNG BARIS SEBELUM ====="
Q="SELECT 'orders',COUNT(*) FROM orders UNION ALL SELECT 'order_items',COUNT(*) FROM order_items UNION ALL SELECT 'customers',COUNT(*) FROM customers UNION ALL SELECT 'cash_flows',COUNT(*) FROM cash_flows UNION ALL SELECT 'gaji_karyawans',COUNT(*) FROM gaji_karyawans;"
mysql -u"$DBU" -p"$DBP" "$DBN" -e "$Q" | tee "$CAD/sebelum-$D.txt"

echo "===== 4. VERSI SEBELUM ====="
git log --oneline -1

echo "===== 5. AMBIL KODE ====="
git pull --ff-only origin main

echo "===== 6. MIGRASI ====="
php artisan migrate --force

echo "===== 7. BERSIHKAN CACHE ====="
php artisan optimize:clear

echo "===== 8. HITUNG BARIS SESUDAH ====="
mysql -u"$DBU" -p"$DBP" "$DBN" -e "$Q" | tee "$CAD/sesudah-$D.txt"

echo "===== 9. BANDINGKAN (kosong = tidak ada yang hilang) ====="
diff "$CAD/sebelum-$D.txt" "$CAD/sesudah-$D.txt" && echo "JUMLAH BARIS SAMA PERSIS"

echo "===== 10. VERSI SESUDAH ====="
git log --oneline -1
php artisan migrate:status | tail -6

echo "===== SELESAI-TANDA-AKHIR ====="
