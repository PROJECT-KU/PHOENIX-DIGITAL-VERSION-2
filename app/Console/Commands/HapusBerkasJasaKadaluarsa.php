<?php

namespace App\Console\Commands;

use App\Models\OrderUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Hapus NASKAH ASLI milik pelanggan 30 hari setelah dikirim. Berkas HASIL
 * (laporan plagiasi, laporan AI, dokumen parafrase) TIDAK PERNAH dihapus.
 *
 * Kenapa dipisah begitu:
 *
 *  - Naskah pelanggan (skripsi, jurnal, tugas) adalah dokumen pribadi orang
 *    lain. Tidak ada alasan menyimpannya setelah pekerjaannya rampung, dan
 *    itulah bagian yang benar-benar memakan ruang.
 *  - Berkas hasil adalah barang yang KAMI serahkan. Pelanggan yang kehilangan
 *    laporannya, atau komplain sebulan kemudian, hanya bisa ditolong bila
 *    admin masih memegangnya.
 *
 * Aturan lama menghapus KEDUANYA 7 hari setelah link /cek mati, dan pada kode
 * yang lebih tua lagi hitungannya dimulai dari tanggal pelanggan mengunggah —
 * bukan dari tanggal hasil diserahkan. Akibatnya hasil pesanan INV-20260828-0006
 * lenyap hanya tiga hari setelah diserahkan (8 Sep 2026), dan admin tidak punya
 * apa pun untuk dikirim ulang. 153 pengecekan kehilangan berkas hasilnya
 * dengan cara yang sama.
 *
 * Berkas hanya dihapus bila pekerjaannya SUDAH RAMPUNG (selesai / dibatalkan).
 * Pengecekan yang masih menunggu atau sedang diproses tidak pernah disentuh,
 * berapa pun umurnya — admin masih membutuhkan naskahnya.
 */
class HapusBerkasJasaKadaluarsa extends Command
{
    protected $signature = 'jasa:hapus-berkas-kadaluarsa
                            {--dry-run : Tampilkan saja, jangan hapus}';

    protected $description = 'Hapus naskah asli pelanggan 30 hari setelah pekerjaannya rampung (berkas hasil tidak ikut dihapus)';

    /** Naskah dari pelanggan. HANYA kolom-kolom ini yang boleh dihapus. */
    private const BERKAS_PELANGGAN = ['path', 'pdf_path'];

    /** Umur naskah sebelum boleh dihapus. */
    public const HARI_SIMPAN = 30;

    /** Pekerjaan yang sudah rampung — naskahnya tidak diperlukan lagi. */
    private const STATUS_RAMPUNG = ['selesai', 'dibatalkan'];

    public function handle(): int
    {
        $simulasi = (bool) $this->option('dry-run');
        $disk = Storage::disk('local');
        $batas = now()->subDays(self::HARI_SIMPAN);

        $berkasTerhapus = 0;
        $unggahanTerproses = 0;

        OrderUpload::query()
            ->whereIn('status', self::STATUS_RAMPUNG)
            ->where('created_at', '<', $batas)
            ->where(function ($q) {
                foreach (self::BERKAS_PELANGGAN as $kolom) {
                    $q->orWhereNotNull($kolom);
                }
            })
            ->chunkById(200, function ($unggahan) use ($disk, $simulasi, &$berkasTerhapus, &$unggahanTerproses) {
                foreach ($unggahan as $up) {
                    $ubah = [];

                    foreach (self::BERKAS_PELANGGAN as $kolom) {
                        $path = $up->{$kolom};
                        if (! $path) {
                            continue;
                        }

                        $berkasTerhapus++;

                        if ($simulasi) {
                            continue;
                        }

                        if ($disk->exists($path)) {
                            $disk->delete($path);
                        }
                        $ubah[$kolom] = null;
                    }

                    if (! $simulasi && $ubah) {
                        // forceFill: kolom path tidak ikut $fillable demi keamanan.
                        $up->forceFill($ubah)->save();
                    }

                    $unggahanTerproses++;
                }
            });

        $kata = $simulasi ? 'AKAN dihapus' : 'dihapus';
        $this->info("{$unggahanTerproses} unggahan diproses, {$berkasTerhapus} naskah pelanggan {$kata} "
            .'(lebih dari '.self::HARI_SIMPAN.' hari & pekerjaannya rampung). Berkas hasil tidak disentuh.');

        return self::SUCCESS;
    }
}
