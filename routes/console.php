<?php

use App\Models\Promo;
use App\Models\Task;
use App\Notifications\TaskDeadlineSoon;
use App\Notifications\TaskOverdue;
use App\Support\CronHeartbeat;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Perintah contoh bawaan Laravel. Jadwal ->hourly() SENGAJA dilepas: ia
// dijalankan lewat proses terpisah (butuh proc_open, yang dimatikan hosting ini)
// dan keluarannya toh dibuang ke /dev/null, jadi satu-satunya hasilnya adalah 24
// baris galat per hari di Log Aktivitas. Perintahnya sendiri tetap ada dan bisa
// dipanggil manual.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Jadwalkan perintah artisan TANPA membuka proses baru.
 *
 * Kenapa ada: `Schedule::command()` menjalankan tiap perintah lewat proses
 * terpisah, dan itu membutuhkan `proc_open`. Hosting ini mematikannya —
 *
 *   disable_functions: system, exec, shell_exec, ..., proc_open
 *
 * sehingga SETIAP perintah terjadwal gagal tiap menit tanpa pernah berjalan
 * sekali pun. Kerusakannya senyap: tak ada halaman error, tak ada keluhan
 * pelanggan, hanya galat menumpuk di Log Aktivitas — 198 ribu baris (2,1 GB)
 * selama 23 hari sebelum ketahuan pada 12 Agustus 2026. Selama itu deteksi
 * pembayaran QRIS, pembatalan order kedaluwarsa, dan pengingat bayar semuanya
 * mati.
 *
 * `Artisan::call()` menjalankan perintah di dalam proses yang SEDANG berjalan,
 * jadi `proc_open` tidak pernah disentuh. Ini juga membuat penjadwal KEBAL bila
 * hosting mengubah pengaturannya lagi — sengaja tidak memilih jalan "minta
 * hosting menyalakan proc_open", karena itu menaruh seluruh operasi terjadwal
 * pada saklar milik pihak lain. Pola yang sama sudah terbukti bekerja di
 * App\Http\Controllers\CronController.
 *
 * `->name()` WAJIB dipanggil di sini: `withoutOverlapping()` pada event closure
 * melempar LogicException bila event-nya belum bernama.
 *
 * Konsekuensi yang disadari: semua perintah kini berbagi satu proses PHP dan
 * satu batas waktu, sehingga tidak lagi saling terisolasi. Ditukar dengan
 * perintah yang benar-benar BERJALAN — perdagangan yang jelas menguntungkan.
 *
 * Karena berbagi proses itu pula, SETIAP withoutOverlapping() di berkas ini
 * diberi batas menit yang eksplisit. Satu run yang mati di tengah (mis. kena
 * max_execution_time saat dipicu KickScheduler dari request pengunjung)
 * meninggalkan kuncinya tetap terpegang; dengan default Laravel, kunci itu
 * bertahan 24 JAM dan tugasnya di-skip DIAM-DIAM selama itu — tanpa error,
 * tanpa gejala. Persis yang terjadi pada 23 Juli 2026. Batasnya disamakan
 * dengan irama tugas masing-masing supaya cepat sembuh sendiri.
 */
$jadwalkan = fn (string $perintah) => Schedule::call(function () use ($perintah) {
    Artisan::call($perintah);
})->name($perintah);

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
$jadwalkan('qris:cek-pembayaran')->everyMinute()->withoutOverlapping(10);
$jadwalkan('orders:cancel-expired')->everyMinute()->withoutOverlapping(10);

/**
 * Kirim email pengingat ~10 menit sebelum batas waktu pembayaran habis.
 */
$jadwalkan('payment:remind')->everyMinute()->withoutOverlapping(10);

/**
 * Ingatkan keranjang yang ditinggalkan (belum checkout) ~1 jam kemudian.
 */
$jadwalkan('cart:remind-abandoned')->everyThirtyMinutes()->withoutOverlapping(30);

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

$jadwalkan('tasks:notify-deadlines')->dailyAt('07:00');

/**
 * Hapus notifikasi bulan-bulan lama (sebelum awal bulan berjalan) agar DB tak menumpuk.
 * Bell hanya menampilkan bulan berjalan, jadi yang lebih lama aman dihapus permanen.
 */
$jadwalkan('notifications:prune')->dailyAt('00:05');

/**
 * Bersihkan Log Aktivitas agar tabel tidak membengkak: kunjungan biasa
 * (type 'visit', tidak lambat) dibuang setelah 7 hari; error/auth/kunjungan
 * lambat disimpan 30 hari.
 */
$jadwalkan('activity-logs:prune')->dailyAt('00:15');

/**
 * Hapus komentar task (chat + file/gambar) dari tahun-tahun sebelumnya agar DB & storage
 * tidak menumpuk. Idempoten: hanya menghapus yang dibuat sebelum awal tahun berjalan.
 */
$jadwalkan('comments:prune')->dailyAt('00:10');

/**
 * Reset poin member setiap awal tahun (poin kadaluarsa akhir tahun kalender).
 * Berjalan tiap 1 Januari 00:15. Model juga punya pengaman lazy (applyYearlyExpiry)
 * bila scheduler terlewat.
 */
$jadwalkan('points:reset-yearly')->yearlyOn(1, 1, '00:15');

/**
 * Hapus draft unggahan jasa yang tak pernah jadi pesanan (customer batal
 * checkout). Isinya dokumen pribadi customer, jadi tak boleh tersimpan
 * selamanya. Draft yang berhasil jadi pesanan sudah dihapus saat checkout.
 */
$jadwalkan('jasa:bersihkan-draft --hari=7')->dailyAt('00:20');

/**
 * Hapus BERKAS jasa pengecekan (unggahan customer + hasil admin) 7 hari setelah
 * link /cek kedaluwarsa (kuota habis → +24 jam link mati → +7 hari berkas dihapus).
 * Hemat storage; baris pengecekan (persentase) tetap disimpan. Per-jam agar
 * penghapusan dekat dengan waktu tepatnya per pesanan (bukan sekali sehari).
 */
$jadwalkan('jasa:hapus-berkas-kadaluarsa')->hourly()->withoutOverlapping(60);

/**
 * Denyut nadi cron hPanel — supaya "cron mati" bisa dibuktikan, bukan ditebak.
 * Dipakai bot Telegram (/cron) & App\Support\CronHeartbeat.
 *
 * HANYA mencatat saat dijalankan dari konsol, yaitu cron asli server. Dua
 * pemicu lain menandai dirinya sendiri di tempat masing-masing:
 *   - CronController  -> 'http'   (cron-job.org)
 *   - KickScheduler   -> 'trafik' (menumpang request pengunjung)
 *
 * Aditif & tanpa efek samping: hanya menulis kunci cache.
 */
Schedule::call(function () {
    if (app()->runningInConsole()) {
        CronHeartbeat::catat('cli');
    }
})->everyMinute();

/**
 * Pastikan webhook bot Telegram terdaftar — supaya setelah deploy bot langsung
 * hidup begitu .env diisi, tanpa perlu SSH (crontab CLI diblokir di hosting ini),
 * dan sembuh sendiri bila webhook lepas.
 *
 * Idempoten: hanya memanggil Telegram bila URL belum cocok. Bila
 * TELEGRAM_* belum diisi, langsung berhenti tanpa panggilan keluar apa pun.
 */
$jadwalkan('telegram:webhook pastikan')->hourly()->withoutOverlapping(60);

/**
 * Peringatkan lewat Telegram saat keandalan pemicu terjadwal BERUBAH
 * (mis. cron hPanel mati, atau pulih lagi). Hanya mengirim saat berubah.
 *
 * Diam total bila bot belum dikonfigurasi.
 */
$jadwalkan('cron:pantau')->everyFiveMinutes()->withoutOverlapping(10);
