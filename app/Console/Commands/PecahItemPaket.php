<?php

namespace App\Console\Commands;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBundlings;
use App\Support\ItemPaket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Memperbaiki pesanan paket yang terlanjur tersimpan sebagai SATU baris.
 *
 * Checkout publik dahulu menyimpan paket bundling sebagai satu order item
 * dengan product_id menunjuk ke id PAKET, bukan produk. Akibatnya baris itu
 * tak punya nama produk, tak punya durasi, dan cuma menyediakan satu slot
 * kredensial untuk paket yang isinya beberapa akun — akunnya jadi tidak bisa
 * dikirim (kejadian INV-20260820-0008).
 *
 * Perintah ini memecah baris seperti itu menjadi satu baris per produk,
 * memakai pembagian harga yang sama dengan checkout dan form admin.
 * Tanpa --terapkan, perintah hanya melaporkan rencananya.
 */
class PecahItemPaket extends Command
{
    protected $signature = 'paket:pecah-item
                            {--order= : Batasi ke satu nomor pesanan, mis. INV-20260820-0008}
                            {--terapkan : Benar-benar tulis perubahan (tanpa ini hanya laporan)}';

    protected $description = 'Pecah item pesanan paket bundling menjadi satu item per produk';

    public function handle(): int
    {
        $terapkan = (bool) $this->option('terapkan');

        $idPaket = ProductBundlings::pluck('id')->all();

        if (empty($idPaket)) {
            $this->warn('Tidak ada paket bundling di database.');

            return self::SUCCESS;
        }

        $q = OrderItem::query()
            ->whereIn('product_id', $idPaket)
            ->with('order');

        if ($nomor = $this->option('order')) {
            $q->whereHas('order', fn ($o) => $o->where('order_number', $nomor));
        }

        $items = $q->get();

        if ($items->isEmpty()) {
            $this->info('Tidak ada item paket yang perlu dipecah.');

            return self::SUCCESS;
        }

        $this->line($terapkan ? 'MENERAPKAN perubahan:' : 'MODE LAPORAN (tambahkan --terapkan untuk menulis):');

        $berhasil = 0;
        $dilewati = 0;

        foreach ($items as $item) {
            $nomor = $item->order->order_number ?? '?';
            $paket = ProductBundlings::find($item->product_id);

            if (! $paket) {
                $this->warn("  [$nomor] paket sudah dihapus — dilewati.");
                $dilewati++;

                continue;
            }

            // Sudah dikerjakan admin: memecahnya akan membuang kredensial yang
            // sudah diisi. Biarkan, biar admin yang memutuskan.
            if ($item->data_akun_id || $item->account_username) {
                $this->warn("  [$nomor] {$paket->nama_paket} sudah berisi akun — dilewati.");
                $dilewati++;

                continue;
            }

            $baris = ItemPaket::pecah($paket, (int) $item->subtotal);

            if (empty($baris)) {
                $this->warn("  [$nomor] {$paket->nama_paket} tidak punya produk penyusun — dilewati.");
                $dilewati++;

                continue;
            }

            $this->line("  [$nomor] {$paket->nama_paket} (Rp ".number_format((int) $item->subtotal, 0, ',', '.').') →');

            foreach ($baris as $sub) {
                $this->line('      • '.ItemPaket::namaItem($paket->nama_paket, $sub['product_name'])
                    ." — {$sub['duration_value']} {$sub['duration_type']} — Rp "
                    .number_format($sub['distributed'], 0, ',', '.'));
            }

            if (! $terapkan) {
                $berhasil++;

                continue;
            }

            DB::transaction(function () use ($item, $paket, $baris) {
                foreach ($baris as $sub) {
                    $produk = Product::find($sub['product_id']);

                    OrderItem::create([
                        'id' => Str::uuid(),
                        'order_id' => $item->order_id,
                        'product_id' => $sub['product_id'],
                        'product_name' => ItemPaket::namaItem($paket->nama_paket, $sub['product_name']),
                        'product_description' => $produk->deskripsi ?? null,
                        'product_image' => $produk->image ?? null,
                        'duration_type' => $sub['duration_type'],
                        'duration_value' => $sub['duration_value'],
                        'price' => $sub['distributed'],
                        'quantity' => 1,
                        'subtotal' => $sub['distributed'],
                    ]);
                }

                $item->delete();
            });

            $berhasil++;
        }

        $this->newLine();
        $this->info(($terapkan ? 'Dipecah: ' : 'Akan dipecah: ')."$berhasil item, dilewati: $dilewati.");

        return self::SUCCESS;
    }
}
