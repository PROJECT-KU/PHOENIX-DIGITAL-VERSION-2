<?php

namespace App\Console\Commands;

use App\Models\Banners;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Models\Testimoni;
use App\Support\GambarWebp;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Ubah gambar LAMA menjadi WebP, sekali jalan.
 *
 * Unggahan baru sudah otomatis WebP lewat App\Support\GambarWebp. Perintah ini
 * mengurus yang telanjur tersimpan sebelum itu — 43 berkas, 16 MB, dengan tiga
 * banner PNG saja memakan 5,7 MB.
 *
 * Yang membuat ini berisiko: nama berkas tersimpan di DATABASE. Mengubah
 * ekstensi tanpa memperbarui kolomnya akan membuat SEMUA gambar hilang dari
 * situs sekaligus. Karena itu urutannya dijaga ketat:
 *
 *   1. tulis berkas .webp BARU di sebelah yang lama
 *   2. baru perbarui kolom di database
 *   3. berkas lama TIDAK dihapus (kecuali diminta --hapus-lama)
 *
 * Kalau langkah 2 gagal, berkas lama masih di tempatnya dan situs tetap utuh.
 */
class KonversiGambarWebp extends Command
{
    protected $signature = 'gambar:webp
        {--terapkan : Benar-benar tulis perubahan. Tanpa ini hanya melaporkan rencana.}
        {--hapus-lama : Hapus berkas asli SETELAH kolom DB berhasil diperbarui.}';

    protected $description = 'Ubah gambar lama (PNG/JPG) menjadi WebP dan perbarui nama berkasnya di database';

    /**
     * Peta: model => [folder penyimpanan, kolom nama berkas].
     *
     * Blog sengaja TIDAK ada di sini — BlogImageService sudah menyimpan cover
     * sebagai WebP sejak awal.
     */
    private const SASARAN = [
        Banners::class => ['img/banners', 'gambar'],
        Product::class => ['img/Product', 'image'],
        ProductBundlings::class => ['img/ProductBundlings', 'gambar'],
        Testimoni::class => ['img/testimoni', 'foto'],
    ];

    public function handle(): int
    {
        $terapkan = (bool) $this->option('terapkan');

        if (! $terapkan) {
            $this->warn('MODE COBA — tidak ada yang diubah. Tambahkan --terapkan untuk menjalankan.');
        }

        $disk = Storage::disk('public');
        $totalHemat = 0;
        $diubah = 0;
        $dilewati = 0;
        $gagal = 0;

        foreach (self::SASARAN as $model => [$folder, $kolom]) {
            $this->line('');
            $this->info(class_basename($model).'  ('.$folder.')');

            foreach ($model::query()->whereNotNull($kolom)->get() as $baris) {
                $nama = (string) $baris->{$kolom};

                if ($nama === '' || str_ends_with(strtolower($nama), '.webp')) {
                    $dilewati++;

                    continue;
                }

                $jalurLama = $folder.'/'.basename($nama);

                if (! $disk->exists($jalurLama)) {
                    $this->line('  ? '.$nama.' — berkas tidak ada, dilewati');
                    $dilewati++;

                    continue;
                }

                $ukuranLama = $disk->size($jalurLama);
                $webp = GambarWebp::ubah($disk->path($jalurLama));

                if ($webp === null) {
                    $this->line('  ! '.$nama.' — tidak bisa dikonversi, dibiarkan apa adanya');
                    $gagal++;

                    continue;
                }

                $namaBaru = pathinfo($nama, PATHINFO_FILENAME).'.webp';
                $hemat = $ukuranLama - strlen($webp);
                $totalHemat += $hemat;
                $diubah++;

                $this->line(sprintf(
                    '  %s %s  %s KB -> %s KB  (hemat %d%%)',
                    $terapkan ? 'v' : '-',
                    $nama,
                    number_format($ukuranLama / 1024, 0),
                    number_format(strlen($webp) / 1024, 0),
                    $ukuranLama > 0 ? round($hemat / $ukuranLama * 100) : 0,
                ));

                if (! $terapkan) {
                    continue;
                }

                // Berkas dulu, DATABASE belakangan. Bila urutannya dibalik dan
                // penulisan berkas gagal, kolom sudah menunjuk berkas yang
                // tidak pernah ada.
                $disk->put($folder.'/'.$namaBaru, $webp);

                $baris->{$kolom} = $namaBaru;
                $baris->save();

                if ($this->option('hapus-lama')) {
                    $disk->delete($jalurLama);
                }
            }
        }

        $this->line('');
        $this->info(sprintf(
            '%s: %d diubah, %d dilewati, %d gagal. Hemat %s MB.',
            $terapkan ? 'SELESAI' : 'RENCANA',
            $diubah,
            $dilewati,
            $gagal,
            number_format($totalHemat / 1024 / 1024, 1),
        ));

        if ($terapkan && ! $this->option('hapus-lama')) {
            $this->comment('Berkas asli SENGAJA dibiarkan. Setelah situs terbukti menampilkan semua gambar, jalankan ulang dengan --hapus-lama.');
        }

        return self::SUCCESS;
    }
}
