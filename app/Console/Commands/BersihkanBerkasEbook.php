<?php

namespace App\Console\Commands;

use App\Models\Ebook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Hapus PDF di storage/app/ebooks yang tidak dipakai ebook mana pun.
 *
 * Berkas yatim muncul dari masa sebelum penggantian berkas menghapus berkas
 * lamanya, atau dari unggahan yang gagal disimpan. Aman:
 *  - ebook di tempat sampah (soft delete) tetap dianggap memakai berkasnya;
 *  - berkas yang lebih muda dari --umur menit tidak disentuh (unggahan yang
 *    sedang berjalan menyimpan berkas SEBELUM baris ebook-nya dibuat);
 *  - --kering hanya menampilkan daftar tanpa menghapus.
 */
class BersihkanBerkasEbook extends Command
{
    protected $signature = 'ebook:bersihkan-berkas
        {--kering : Tampilkan daftar saja, jangan hapus}
        {--umur=60 : Abaikan berkas yang lebih muda dari sekian menit}';

    protected $description = 'Hapus berkas PDF ebook yang tidak dipakai ebook mana pun';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $dipakai = Ebook::withTrashed()->whereNotNull('file')->pluck('file')->flip();
        $batas = now()->subMinutes(max(0, (int) $this->option('umur')))->getTimestamp();

        $yatim = collect($disk->files('ebooks'))
            ->reject(fn ($p) => str_starts_with(basename($p), '.'))
            ->reject(fn ($p) => $dipakai->has(basename($p)))
            ->filter(fn ($p) => $disk->lastModified($p) < $batas)
            ->values();

        if ($yatim->isEmpty()) {
            $this->info('Tidak ada berkas yatim.');

            return self::SUCCESS;
        }

        $total = $yatim->sum(fn ($p) => $disk->size($p));
        $this->table(['Berkas', 'Ukuran'], $yatim->map(fn ($p) => [basename($p), number_format($disk->size($p) / 1024, 0, ',', '.').' KB']));

        if ($this->option('kering')) {
            $this->warn($yatim->count().' berkas ('.number_format($total / 1048576, 1, ',', '.').' MB) AKAN dihapus. Jalankan tanpa --kering untuk menghapus.');

            return self::SUCCESS;
        }

        $disk->delete($yatim->all());
        $this->info($yatim->count().' berkas yatim dihapus ('.number_format($total / 1048576, 1, ',', '.').' MB).');

        return self::SUCCESS;
    }
}
