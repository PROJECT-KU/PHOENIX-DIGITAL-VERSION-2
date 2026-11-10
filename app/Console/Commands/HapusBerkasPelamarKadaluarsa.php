<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Hapus CV, surat lamaran, dan berkas pendukung milik pelamar yang urusannya
 * sudah selesai — catatan lamarannya sendiri TIDAK dihapus.
 *
 * Kenapa perlu:
 *
 *  - CV dan surat lamaran adalah dokumen pribadi orang lain: alamat rumah,
 *    nomor telepon, riwayat kerja, kadang tanggal lahir dan foto. Sebelum ini
 *    tidak ada satu pun yang menghapusnya — berkas pelamar tahun 2025 yang
 *    tidak diterima masih utuh di cakram sampai hari ini.
 *  - Yang dihapus HANYA berkasnya. Nama, email, nomor lamaran, status, dan
 *    catatan internal tetap tinggal, jadi pertanyaan "dulu si A pernah
 *    melamar belum?" tetap bisa dijawab bertahun-tahun kemudian.
 *
 * Dua aturan, dan keduanya perlu:
 *
 *  1. Lamaran yang sudah DIPUTUSKAN (diterima / ditolak) — dihitung dari
 *     tanggal keputusannya, bukan tanggal melamar. Dihitung dari tanggal
 *     melamar, berkas pelamar yang seleksinya memang lama akan lenyap di
 *     tengah proses.
 *  2. Lamaran yang DIDIAMKAN berapa pun statusnya — satu tahun tanpa
 *     disentuh berarti rekrutmennya memang sudah lewat. Tanpa aturan kedua,
 *     lamaran yang statusnya tidak pernah diubah dari "baru" akan menyimpan
 *     CV-nya selamanya, dan justru itulah tumpukan terbesarnya.
 *
 * Kolom jalurnya ikut dikosongkan, bukan cuma berkasnya dihapus: tombol unduh
 * yang masih tergambar lalu menyerahkan galat, dan admin mengira sistemnya
 * rusak padahal berkasnya memang sudah dihapus sesuai aturan.
 */
class HapusBerkasPelamarKadaluarsa extends Command
{
    protected $signature = 'pelamar:hapus-berkas-kadaluarsa
                            {--hari=90 : Umur sesudah lamaran diputuskan (diterima/ditolak)}
                            {--hari-diam=365 : Umur lamaran yang statusnya tidak pernah selesai}
                            {--dry-run : Tampilkan saja, jangan hapus}';

    protected $description = 'Hapus CV & surat lamaran pelamar yang sudah kedaluwarsa (catatan lamarannya tetap disimpan)';

    /** Berkas milik pelamar. HANYA kolom-kolom ini yang boleh dihapus. */
    private const BERKAS_PELAMAR = ['cv_path', 'cover_letter_path', 'lampiran_path'];

    /** Lamaran yang prosesnya sudah tuntas. */
    private const STATUS_SELESAI = ['diterima', 'ditolak'];

    public function handle(): int
    {
        $simulasi = (bool) $this->option('dry-run');
        $hari = max(1, (int) $this->option('hari'));
        $hariDiam = max($hari, (int) $this->option('hari-diam'));

        $batasPutusan = now()->subDays($hari);
        $batasDiam = now()->subDays($hariDiam);

        $disk = Storage::disk('local');
        $berkasTerhapus = 0;
        $lamaranTerproses = 0;

        JobApplication::query()
            ->where(function ($q) use ($batasPutusan, $batasDiam) {
                /*
                 | Sudah diputuskan, dihitung dari tanggal keputusannya.
                 |
                 | status_diubah_at bisa kosong pada baris lama yang statusnya
                 | diubah sebelum kolom itu ada; untuk baris itu dipakai
                 | tanggal melamar, karena menganggapnya "baru diputuskan hari
                 | ini" berarti berkasnya tidak pernah tersentuh sama sekali.
                 */
                $q->where(function ($w) use ($batasPutusan) {
                    $w->whereIn('status', self::STATUS_SELESAI)
                        ->where(function ($x) use ($batasPutusan) {
                            $x->where('status_diubah_at', '<', $batasPutusan)
                                ->orWhere(fn ($y) => $y->whereNull('status_diubah_at')
                                    ->where('created_at', '<', $batasPutusan));
                        });
                })
                    // Didiamkan terlalu lama, berapa pun statusnya.
                    ->orWhere('created_at', '<', $batasDiam);
            })
            ->where(function ($q) {
                foreach (self::BERKAS_PELAMAR as $kolom) {
                    $q->orWhereNotNull($kolom);
                }
            })
            ->chunkById(200, function ($daftar) use ($disk, $simulasi, &$berkasTerhapus, &$lamaranTerproses) {
                foreach ($daftar as $lamaran) {
                    $ubah = [];

                    foreach (self::BERKAS_PELAMAR as $kolom) {
                        $jalur = $lamaran->{$kolom};

                        if (! $jalur) {
                            continue;
                        }

                        if (! $simulasi) {
                            // Berkas yang sudah hilang duluan tidak membatalkan
                            // pengosongan kolomnya: yang keliru justru kolom
                            // yang menunjuk berkas tidak ada.
                            $disk->delete($jalur);
                        }

                        $ubah[$kolom] = null;
                        $berkasTerhapus++;
                    }

                    if ($ubah === []) {
                        continue;
                    }

                    $lamaranTerproses++;

                    if (! $simulasi) {
                        $lamaran->forceFill($ubah)->saveQuietly();
                    }
                }
            });

        $awalan = $simulasi ? '[simulasi] ' : '';
        $this->info("{$awalan}{$berkasTerhapus} berkas dari {$lamaranTerproses} lamaran dibersihkan.");

        return self::SUCCESS;
    }
}
