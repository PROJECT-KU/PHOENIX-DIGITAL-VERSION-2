<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Support\PeriodeGaji;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Menyalin task yang ditandai BERULANG ke periode berikutnya.
 *
 * Laporan mingguan dan bulanan sebelumnya dibuat ulang dengan tangan tiap
 * periode — dan yang terlupa dibuat ulang tidak pernah terlihat hilang,
 * karena tidak ada apa pun di layar yang menunjukkan bahwa ia seharusnya ada.
 *
 * Yang disalin hanya PERINTAHNYA: nama, uraian, kategori, bobot, penerima.
 * Progres, komentar, lampiran, dan riwayat tidak ikut — salinan itu pekerjaan
 * baru, bukan lanjutan pekerjaan lama.
 */
class SalinTaskBerulang extends Command
{
    protected $signature = 'tasks:salin-berulang {--kering : Tampilkan saja, jangan menyalin}';

    protected $description = 'Salin task berulang (mingguan/bulanan) ke periode berikutnya';

    public function handle(): int
    {
        $kering = (bool) $this->option('kering');
        $sekarang = now();
        $dibuat = 0;

        $induk = Task::where('ulang', '!=', 'tidak')->whereNotNull('deadline_selesai')->get();

        foreach ($induk as $t) {
            $berikutnya = $this->tenggatBerikutnya($t);

            // Belum waktunya: tenggat salinan berikutnya masih di depan.
            if ($berikutnya->gt($sekarang)) {
                continue;
            }

            // Penanda anti-ganda. Penjadwal berjalan tiap hari, dan tanpa ini
            // satu task berulang akan disalin berkali-kali dalam sehari.
            if ($t->ulang_terakhir_at && $t->ulang_terakhir_at->gte($berikutnya)) {
                continue;
            }

            $this->line(($kering ? '[kering] ' : '').'Menyalin "'.$t->nama.'" → tenggat '.$berikutnya->toDateString());

            if ($kering) {
                $dibuat++;

                continue;
            }

            $this->salin($t, $berikutnya);
            $t->forceFill(['ulang_terakhir_at' => $berikutnya])->save();
            $dibuat++;
        }

        $this->info($dibuat.' task berulang '.($kering ? 'akan disalin' : 'disalin').'.');

        return self::SUCCESS;
    }

    /** Tenggat salinan berikutnya, dihitung dari tenggat induknya. */
    protected function tenggatBerikutnya(Task $t): \Illuminate\Support\Carbon
    {
        $acuan = $t->ulang_terakhir_at ?: $t->deadline_selesai;

        // addMonthNoOverflow: tenggat 31 Januari tidak boleh melompat ke
        // 3 Maret hanya karena Februari lebih pendek.
        return $t->ulang === 'mingguan'
            ? $acuan->copy()->addWeek()
            : $acuan->copy()->addMonthNoOverflow();
    }

    protected function salin(Task $t, \Illuminate\Support\Carbon $tenggat): void
    {
        $panjang = $t->deadline_mulai && $t->deadline_selesai
            ? (int) $t->deadline_mulai->startOfDay()->diffInDays($t->deadline_selesai->startOfDay())
            : 0;

        $periode = PeriodeGaji::dariTanggal($tenggat);

        $baru = Task::create([
            'id' => Str::uuid(),
            // group_id BARU: salinan ini pekerjaan tersendiri, dan kalau ikut
            // grup induknya, komentar periode lalu akan muncul di task baru.
            'group_id' => Str::uuid(),
            'user_id' => $t->user_id,
            'assigned_by' => $t->assigned_by,
            'created_by' => $t->created_by,
            'periode_bulan' => $periode['bulan'],
            'periode_tahun' => $periode['tahun'],
            'nama' => $t->nama,
            'deskripsi' => $t->deskripsi,
            'task_category_id' => $t->task_category_id,
            'task_category_label_id' => $t->task_category_label_id,
            'bobot' => $t->bobot,
            'deadline_mulai' => $tenggat->copy()->subDays($panjang),
            'deadline_selesai' => $tenggat,
            'progress' => 'belum',
            // Salinannya ikut berulang, sehingga rantainya berlanjut sendiri.
            'ulang' => $t->ulang,
        ]);

        $baru->catat('berulang', null, null, 'Disalin dari task '.$t->nama);
    }
}
