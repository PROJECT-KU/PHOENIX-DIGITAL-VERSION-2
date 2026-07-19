<?php

namespace App\Console\Commands;

use App\Models\JasaDraftUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Hapus draft unggahan jasa yang tak pernah jadi pesanan.
 *
 * Customer parafrase mengunggah PDF + DOCX SEBELUM membayar agar halamannya
 * bisa dihitung. Bila ia batal checkout, berkasnya tertinggal selamanya —
 * padahal isinya dokumen pribadi (skripsi/naskah) yang tak pernah diminta
 * disimpan. Draft yang berhasil jadi pesanan sudah dihapus saat checkout,
 * jadi yang tersisa di sini pasti telantar.
 */
class BersihkanDraftJasa extends Command
{
    protected $signature = 'jasa:bersihkan-draft
                            {--hari=7 : Umur minimal draft yang dihapus}
                            {--dry-run : Tampilkan saja, jangan hapus}';

    protected $description = 'Hapus draft unggahan jasa telantar beserta berkasnya';

    public function handle(): int
    {
        $hari = max(1, (int) $this->option('hari'));
        $batas = now()->subDays($hari);
        $simulasi = (bool) $this->option('dry-run');

        $draft = JasaDraftUpload::where('created_at', '<', $batas)->get();

        if ($draft->isEmpty()) {
            $this->info("Tidak ada draft telantar lebih tua dari {$hari} hari.");

            return self::SUCCESS;
        }

        $disk = Storage::disk('local');
        $berkasTerhapus = 0;

        foreach ($draft as $d) {
            foreach ([$d->path, $d->kerja_path] as $path) {
                if (! $path || ! $disk->exists($path)) {
                    continue;
                }

                if ($simulasi) {
                    $berkasTerhapus++;

                    continue;
                }

                if ($disk->delete($path)) {
                    $berkasTerhapus++;
                }
            }

            if (! $simulasi) {
                $d->delete();
            }
        }

        $kata = $simulasi ? 'AKAN dihapus' : 'dihapus';
        $this->info("{$draft->count()} draft {$kata} ({$berkasTerhapus} berkas), lebih tua dari {$hari} hari.");

        return self::SUCCESS;
    }
}
