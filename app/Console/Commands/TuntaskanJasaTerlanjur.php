<?php

namespace App\Console\Commands;

use App\Actions\Jasa\SelesaikanPesananJasa;
use App\Models\Order;
use Illuminate\Console\Command;

/**
 * Menuntaskan pesanan jasa yang seluruh pekerjaannya SUDAH diserahkan tetapi
 * statusnya masih menggantung.
 *
 * Penuntasan biasanya terjadi otomatis saat admin menekan Simpan Hasil. Yang
 * lolos adalah pesanan yang menjadi tuntas BELAKANGAN — mis. setelah hitungan
 * kuotanya diperbaiki — karena tak ada lagi yang menilainya ulang.
 *
 * Tanpa --terapkan, perintah hanya melaporkan rencananya.
 */
class TuntaskanJasaTerlanjur extends Command
{
    protected $signature = 'jasa:tuntaskan
                            {--order= : Batasi ke satu nomor pesanan}
                            {--terapkan : Benar-benar tuntaskan (tanpa ini hanya laporan)}';

    protected $description = 'Tuntaskan pesanan jasa yang pekerjaannya sudah selesai tapi statusnya menggantung';

    public function handle(SelesaikanPesananJasa $selesaikan): int
    {
        $terapkan = (bool) $this->option('terapkan');

        $q = Order::query()
            ->whereIn('status', ['paid', 'processing'])
            ->with(['items.product', 'uploads']);

        if ($nomor = $this->option('order')) {
            $q->where('order_number', $nomor);
        }

        $calon = $q->get()->filter(fn (Order $o) => $o->butuhUpload() && $o->jasaTuntas());

        if ($calon->isEmpty()) {
            $this->info('Tidak ada pesanan jasa yang menggantung.');

            return self::SUCCESS;
        }

        $this->line($terapkan ? 'MENUNTASKAN:' : 'MODE LAPORAN (tambahkan --terapkan untuk menulis):');

        $selesai = 0;
        $tertahan = 0;

        foreach ($calon as $order) {
            $this->line(sprintf(
                '  %s — pekerjaan %d/%d, status %s',
                $order->order_number,
                $order->pekerjaanTerserah(),
                $order->kuotaPengecekan(),
                $order->status,
            ));

            if (! $terapkan) {
                $selesai++;

                continue;
            }

            if ($selesaikan->execute($order)) {
                $selesai++;

                continue;
            }

            // Pesanan campuran: bagian jasanya beres, item akunnya belum dikirim.
            $this->warn('      masih menunggu item non-jasa dikirim — belum completed.');
            $tertahan++;
        }

        $this->newLine();
        $this->info(($terapkan ? 'Dituntaskan: ' : 'Akan dituntaskan: ').$selesai
            .($tertahan ? ", tertahan: {$tertahan}" : '').'.');

        return self::SUCCESS;
    }
}
