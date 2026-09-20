<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Support\PeriodeGaji;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyalin task yang ditandai BERULANG ke periode berikutnya.
 *
 * Laporan mingguan dan bulanan sebelumnya dibuat ulang dengan tangan tiap
 * periode — dan yang terlupa dibuat ulang tidak pernah terlihat hilang,
 * karena tidak ada apa pun di layar yang menunjukkan bahwa ia seharusnya ada.
 *
 * Yang disalin adalah PERINTAHNYA: nama, uraian, kategori, bobot, penerima,
 * langkah, dan lampiran — semua yang menjelaskan CARA mengerjakan.
 *
 * Yang TIDAK ikut hanya hasil kerja periode lalu: progres, centang langkah,
 * komentar, dan riwayat. Salinan itu pekerjaan baru, bukan lanjutan pekerjaan
 * lama.
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
            $acuan = $t->ulang_terakhir_at ?: $t->deadline_selesai;

            // Salinan dibuat begitu PERIODE BERJALAN berakhir.
            //
            // Dulu syaratnya "tenggat salinan berikutnya sudah lewat", jadi
            // task periode 21 Sep–19 Okt baru muncul pada 19 Okt — tepat di
            // hari jatuh temponya sendiri, saat tak ada lagi waktu
            // mengerjakannya. Dilaporkan nyata: task bulanan 21 Agu–19 Sep
            // tidak memunculkan periode berikutnya.
            if ($acuan->gt($sekarang)) {
                continue;
            }

            // Berapa periode harus dimajukan supaya tenggatnya jatuh di depan.
            // Task yang lama terbengkalai menghasilkan SATU salinan untuk
            // periode berjalan, bukan tumpukan salinan yang sudah telat.
            $lompatan = 1;
            while ($lompatan < 60 && $this->majuPeriode($acuan, $t->ulang, $lompatan)->lte($sekarang)) {
                $lompatan++;
            }

            $berikutnya = $this->majuPeriode($acuan, $t->ulang, $lompatan);

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

            $this->salin($t, $berikutnya, $lompatan);

            // TONGKAT ESTAFETNYA PINDAH ke salinan. Induknya berhenti berulang.
            //
            // Tanpa ini, induk dan salinannya SAMA-SAMA berulang, dan tiap
            // salinan ikut beranak: sekali jalan jadi 2, besoknya 4, lalu 8,
            // 16, 32 — diuji persis begitu. Dengan tongkat yang pindah, pada
            // satu saat hanya ADA SATU task di rantai itu yang berulang, dan
            // ia selalu yang terbaru — yang juga task yang dilihat orang kalau
            // ingin menghentikan rantainya.
            $t->forceFill(['ulang' => 'tidak', 'ulang_terakhir_at' => $berikutnya])->save();
            $t->catat('berulang', null, null, 'Salinan berikutnya dibuat; task ini berhenti berulang');
            $dibuat++;
        }

        $this->info($dibuat.' task berulang '.($kering ? 'akan disalin' : 'disalin').'.');

        return self::SUCCESS;
    }

    /**
     * Majukan satu tanggal sebanyak $kali periode.
     *
     * addMonthsNoOverflow: tenggat 31 Januari tidak boleh melompat ke
     * 3 Maret hanya karena Februari lebih pendek.
     */
    protected function majuPeriode(\Illuminate\Support\Carbon $tanggal, string $ulang, int $kali): \Illuminate\Support\Carbon
    {
        return $ulang === 'mingguan'
            ? $tanggal->copy()->addWeeks($kali)
            : $tanggal->copy()->addMonthsNoOverflow($kali);
    }

    protected function salin(Task $t, \Illuminate\Support\Carbon $tenggat, int $lompatan): void
    {
        // Tanggal MULAI ikut digeser satu periode, bukan dihitung mundur dari
        // panjang harinya: 21 Agu–19 Sep harus jadi 21 Sep–19 Okt, sedangkan
        // hitung-mundur 29 hari memberi 20 Sep karena Agustus lebih panjang.
        $mulaiBaru = $t->deadline_mulai
            ? $this->majuPeriode($t->deadline_mulai, $t->ulang, $lompatan)
            : null;

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
            'deadline_mulai' => $mulaiBaru,
            'deadline_selesai' => $tenggat,
            'progress' => 'belum',
            // Salinannya ikut berulang, sehingga rantainya berlanjut sendiri.
            'ulang' => $t->ulang,
        ]);

        $this->salinLangkah($t, $baru);
        $this->salinLampiran($t, $baru);

        $baru->catat('berulang', null, null, 'Disalin dari task '.$t->nama);
    }

    /**
     * Langkah ikut tersalin dalam keadaan BELUM tercentang.
     *
     * Langkah adalah cara mengerjakan, bukan hasil kerjanya — menyuruh orang
     * mengetik ulang daftar yang sama tiap periode adalah pekerjaan sia-sia.
     */
    protected function salinLangkah(Task $t, Task $baru): void
    {
        foreach ($t->checklists()->get() as $langkah) {
            TaskChecklist::create([
                'task_id' => $baru->id,
                'teks' => $langkah->teks,
                'selesai' => false,
                'urutan' => $langkah->urutan,
            ]);
        }
    }

    /**
     * Lampiran ikut tersalin, BERIKUT berkas fisiknya.
     *
     * Berbagi satu berkas antar task berbahaya: menghapus lampiran menghapus
     * berkasnya di disk DAN seluruh baris dengan path yang sama (lihat
     * TaskSayaList::removeAttachment). Kalau salinannya ikut menunjuk berkas
     * yang sama, menghapus lampiran periode lalu akan mematikan lampiran
     * periode berjalan.
     *
     * Gagal-aman: lampiran yang berkasnya sudah hilang dilewati, tidak
     * menggagalkan penyalinan tasknya.
     */
    protected function salinLampiran(Task $t, Task $baru): void
    {
        foreach ($t->attachments()->get() as $lampiran) {
            if (! $lampiran->path || ! Storage::disk('public')->exists($lampiran->path)) {
                continue;
            }

            $folder = trim(dirname($lampiran->path), '.') ?: 'task_files';
            $ekstensi = pathinfo($lampiran->path, PATHINFO_EXTENSION);
            $tujuan = $folder.'/'.Str::uuid().($ekstensi ? '.'.$ekstensi : '');

            $tersalin = rescue(fn () => Storage::disk('public')->copy($lampiran->path, $tujuan), false, report: false);

            if (! $tersalin) {
                continue;
            }

            TaskAttachment::create([
                'task_id' => $baru->id,
                'uploaded_by' => $lampiran->uploaded_by,
                'path' => $tujuan,
                'name' => $lampiran->name,
                'jenis' => $lampiran->jenis,
            ]);
        }
    }
}
