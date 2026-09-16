<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderUpload;
use App\Support\BotTurnitin;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Isi panel "Pengecekan Bot Turnitin" dengan data CONTOH, supaya tampilannya
 * bisa dilihat tanpa menunggu pelanggan sungguhan mengunggah dokumen.
 *
 * Dua pengaman, karena data contoh yang menyelinap ke server nyata akan
 * terbaca sebagai pesanan betulan oleh admin:
 *
 *  1. Menolak berjalan di environment production.
 *  2. Semua barisnya diberi penanda tetap (INV-CONTOH-… dan catatan bot yang
 *     menyebut "DATA CONTOH"), sehingga `--hapus` bisa membersihkannya kembali
 *     tanpa menyentuh data lain.
 */
class ContohPengecekanBot extends Command
{
    protected $signature = 'bot:contoh-pengecekan {--hapus : Hapus kembali data contohnya}';

    protected $description = 'Isi panel Bot Turnitin dengan data contoh (hanya di luar production)';

    private const AWALAN = 'INV-CONTOH-';

    private const PENANDA = '[DATA CONTOH]';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Ditolak: perintah ini tidak boleh dijalankan di production.');

            return self::FAILURE;
        }

        if ($this->option('hapus')) {
            return $this->hapus();
        }

        if (! BotTurnitin::skemaSiap()) {
            $this->error('Kolom bot belum ada di database. Jalankan migrasi dulu.');

            return self::FAILURE;
        }

        $contoh = [
            // [nama pembeli, status bot, berapa lama sejak kabar terakhir, kode submitin, catatan]
            ['Rina Wulandari', BotTurnitin::MENUNGGU_HASIL, now()->subMinutes(2), 'SC-688EE1C5C8C7', null],
            ['Budi Santoso', BotTurnitin::DIAMBIL, now()->subMinutes(1), null, null],
            ['Siti Aminah', BotTurnitin::GAGAL, now()->subDays(5), null, 'Kuota paket standard habis saat mengirim.'],
            ['Dedi Kurnia', BotTurnitin::PERLU_DILENGKAPI, now()->subDays(2), 'SC-776D376C9A13', 'Laporan AI belum ada — lengkapi manual.'],
            ['Maya Safitri', BotTurnitin::GAGAL, now()->subHours(2), null, 'Dokumen ditolak submitin: format tidak terbaca.'],
            ['Andi Prasetyo', BotTurnitin::SELESAI, now()->subHours(3), 'SC-91A2F5D0C4B7', null],
            ['Lina Marlina', BotTurnitin::SELESAI, now()->subHours(5), 'SC-33C8B1E77A29', null],
        ];

        $dibuat = 0;

        foreach ($contoh as $i => [$nama, $status, $kabar, $kode, $pesan]) {
            $pelanggan = Customer::create([
                'nama' => $nama,
                'no_hp' => '0812'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
                'email' => Str::slug($nama).'.'.Str::lower(Str::random(4)).'@contoh.test',
            ]);

            $pesanan = Order::create([
                'id' => Str::uuid(),
                'order_number' => self::AWALAN.now()->format('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'customer_id' => $pelanggan->id,
                'subtotal' => 5000, 'total' => 5000, 'unique_code' => 0,
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'qris_dinamis',
                'expired_at' => now()->addDay(),
                'customer_notes' => self::PENANDA,
            ]);

            $selesai = in_array($status, [BotTurnitin::SELESAI, BotTurnitin::PERLU_DILENGKAPI], true);

            OrderUpload::create([
                'order_id' => $pesanan->id,
                'jenis' => 'plagiasi',
                'nama_asli' => 'contoh-naskah-'.($i + 1).'.docx',
                'status' => $status === BotTurnitin::SELESAI ? 'selesai' : 'diproses',
                'dikerjakan_oleh' => 'bot',
                'bot_status' => $status,
                'bot_kode' => $kode,
                'bot_pesan' => $pesan ? self::PENANDA.' '.$pesan : self::PENANDA,
                'bot_diambil_at' => $kabar->copy()->subMinutes(7),
                'bot_diperbarui_at' => $kabar,
                'persentase' => $selesai ? random_int(3, 18) : null,
                'selesai_at' => $status === BotTurnitin::SELESAI ? $kabar : null,
            ]);

            $dibuat++;
        }

        // Satu dokumen yang belum disentuh bot, supaya angka "antre" ikut terisi.
        $pelangganAntre = Customer::create([
            'nama' => 'Fajar Nugroho',
            'no_hp' => '0812'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'email' => 'fajar.'.Str::lower(Str::random(4)).'@contoh.test',
        ]);
        $pesananAntre = Order::create([
            'id' => Str::uuid(),
            'order_number' => self::AWALAN.now()->format('Ymd').'-0008',
            'customer_id' => $pelangganAntre->id,
            'subtotal' => 5000, 'total' => 5000, 'unique_code' => 0,
            'status' => 'paid', 'paid_at' => now(), 'payment_method' => 'qris_dinamis',
            'expired_at' => now()->addDay(), 'customer_notes' => self::PENANDA,
        ]);
        OrderUpload::create([
            'order_id' => $pesananAntre->id,
            'jenis' => 'plagiasi',
            'nama_asli' => 'contoh-naskah-antre.docx',
            'status' => 'menunggu',
            'bot_pesan' => self::PENANDA,
        ]);
        $dibuat++;

        $this->info("{$dibuat} pengecekan contoh dibuat (2 berjalan, 3 perlu admin — 2 di antaranya terbengkalai, 2 selesai, 1 antre).");
        $this->line('Hapus lagi dengan: php artisan bot:contoh-pengecekan --hapus');

        return self::SUCCESS;
    }

    private function hapus(): int
    {
        $pesanan = Order::where('order_number', 'like', self::AWALAN.'%')->get();

        if ($pesanan->isEmpty()) {
            $this->info('Tidak ada data contoh yang tersisa.');

            return self::SUCCESS;
        }

        $pelangganIds = $pesanan->pluck('customer_id')->filter()->unique();

        OrderUpload::whereIn('order_id', $pesanan->pluck('id'))->delete();
        Order::whereIn('id', $pesanan->pluck('id'))->delete();
        Customer::whereIn('id', $pelangganIds)->where('email', 'like', '%@contoh.test')->delete();

        $this->info($pesanan->count().' pesanan contoh beserta unggahannya dihapus.');

        return self::SUCCESS;
    }
}
