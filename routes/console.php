<?php

use App\Models\Promo;
use App\Models\Task;
use App\Notifications\TaskDeadlineSoon;
use App\Notifications\TaskOverdue;
use App\Support\CronHeartbeat;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $now = now();

    // Nonaktifkan yang selesai
    Promo::where('is_active', true)->where('selesai_promo', '<', $now)->update(['is_active' => false]);

    // Aktifkan yang mulai
    Promo::where('is_active', false)
        ->where('mulai_promo', '<=', $now)
        ->where('selesai_promo', '>=', $now)
        ->update(['is_active' => true]);
})->everyMinute(); // Jalankan setiap menit

/**
 * Batalkan otomatis order pending yang sudah lewat batas waktu pembayaran
 * (QRIS/order kedaluwarsa) — supaya tidak terus menggantung "pending"
 * meski tidak ada yang membuka halaman pembayaran.
 */
// Cek pembayaran QRIS di sisi server DULU (tandai lunas) sebelum cancel-expired
// membatalkan order — supaya order yang sudah dibayar tidak ikut dibatalkan.
// withoutOverlapping(10): kunci auto-lepas dalam 10 menit. Bila suatu run
// terputus/ke-kill di tengah, kunci default bertahan 24 JAM dan memblokir
// deteksi QRIS + auto-cancel selama itu (kejadian 23 Jul 2026). 10 menit >
// durasi wajar command, jadi tak ada false-skip, tapi sembuh cepat bila macet.
Schedule::command('qris:cek-pembayaran')->everyMinute()->withoutOverlapping(10);
Schedule::command('orders:cancel-expired')->everyMinute()->withoutOverlapping(10);

/**
 * Kirim email pengingat ~10 menit sebelum batas waktu pembayaran habis.
 */
Schedule::command('payment:remind')->everyMinute()->withoutOverlapping();

/**
 * Ingatkan keranjang yang ditinggalkan (belum checkout) ~1 jam kemudian.
 */
Schedule::command('cart:remind-abandoned')->everyThirtyMinutes()->withoutOverlapping();

/**
 * Notifikasi task: deadline mendekat (besok) & terlambat (lewat, belum selesai).
 * Status/lock task diturunkan real-time di model; command ini hanya mengirim notifikasi
 * sekali per task (dedup via kolom *_notified_at).
 */
Artisan::command('tasks:notify-deadlines', function () {
    $today = today();
    $besok = today()->addDay();

    Task::whereDate('deadline_selesai', $besok)
        ->where('progress', '!=', 'selesai')
        ->whereNull('deadline_notified_at')
        ->with('karyawan')
        ->get()
        ->each(function (Task $t) {
            $t->karyawan?->notify(new TaskDeadlineSoon($t));
            $t->update(['deadline_notified_at' => now()]);
        });

    Task::whereDate('deadline_selesai', '<', $today)
        ->where('progress', '!=', 'selesai')
        ->whereNull('overdue_notified_at')
        ->with('karyawan')
        ->get()
        ->each(function (Task $t) {
            $t->karyawan?->notify(new TaskOverdue($t));
            $t->update(['overdue_notified_at' => now()]);
        });

    $this->info('Notifikasi task selesai diproses.');
})->purpose('Kirim notifikasi deadline & keterlambatan task');

Schedule::command('tasks:notify-deadlines')->dailyAt('07:00');

/**
 * Hapus notifikasi bulan-bulan lama (sebelum awal bulan berjalan) agar DB tak menumpuk.
 * Bell hanya menampilkan bulan berjalan, jadi yang lebih lama aman dihapus permanen.
 */
Schedule::command('notifications:prune')->dailyAt('00:05');

/**
 * Bersihkan Log Aktivitas agar tabel tidak membengkak: kunjungan biasa
 * (type 'visit', tidak lambat) dibuang setelah 7 hari; error/auth/kunjungan
 * lambat disimpan 30 hari.
 */
Schedule::command('activity-logs:prune')->dailyAt('00:15');

/**
 * Hapus komentar task (chat + file/gambar) dari tahun-tahun sebelumnya agar DB & storage
 * tidak menumpuk. Idempoten: hanya menghapus yang dibuat sebelum awal tahun berjalan.
 */
Schedule::command('comments:prune')->dailyAt('00:10');

/**
 * Reset poin member setiap awal tahun (poin kadaluarsa akhir tahun kalender).
 * Berjalan tiap 1 Januari 00:15. Model juga punya pengaman lazy (applyYearlyExpiry)
 * bila scheduler terlewat.
 */
Schedule::command('points:reset-yearly')->yearlyOn(1, 1, '00:15');

/**
 * Hapus draft unggahan jasa yang tak pernah jadi pesanan (customer batal
 * checkout). Isinya dokumen pribadi customer, jadi tak boleh tersimpan
 * selamanya. Draft yang berhasil jadi pesanan sudah dihapus saat checkout.
 */
Schedule::command('jasa:bersihkan-draft --hari=7')->dailyAt('00:20');

/**
 * Hapus BERKAS jasa pengecekan (unggahan customer + hasil admin) 7 hari setelah
 * link /cek kedaluwarsa (kuota habis → +24 jam link mati → +7 hari berkas dihapus).
 * Hemat storage; baris pengecekan (persentase) tetap disimpan. Per-jam agar
 * penghapusan dekat dengan waktu tepatnya per pesanan (bukan sekali sehari).
 */
Schedule::command('jasa:hapus-berkas-kadaluarsa')->hourly()->withoutOverlapping();

/**
 * Denyut nadi scheduler — supaya "cron mati" bisa dibuktikan, bukan ditebak.
 * Dipakai bot Telegram (/cron) & App\Support\CronHeartbeat.
 *
 * Sumber dibedakan dari konteks proses: 'cli' = cron asli server (yang harus
 * menyala tiap menit), 'web' = jaring pengaman lewat trafik pengunjung
 * (KickScheduler) atau route /cron/run/{token}.
 *
 * Aditif & tanpa efek samping: hanya menulis dua kunci cache.
 */
Schedule::call(function () {
    CronHeartbeat::catat(app()->runningInConsole() ? 'cli' : 'web');
})->everyMinute();

/**
 * Pastikan webhook bot Telegram terdaftar — supaya setelah deploy bot langsung
 * hidup begitu .env diisi, tanpa perlu SSH (crontab CLI diblokir di hosting ini),
 * dan sembuh sendiri bila webhook lepas.
 *
 * Idempoten: hanya memanggil Telegram bila URL belum cocok. Bila
 * TELEGRAM_* belum diisi, langsung berhenti tanpa panggilan keluar apa pun.
 */
Schedule::command('telegram:webhook pastikan')->hourly()->withoutOverlapping();
