<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\QrisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired {--tua=0 : Umur JAM. >0 = sapu juga order QRIS yang biasanya dilewati pengaman, TAPI hanya bila penyedia QRIS memastikan belum dibayar.}';

    protected $description = 'Batalkan order kedaluwarsa: pending (di daftar) 30 menit, draft 24 jam. --tua=24 menyapu order tua yang tersangkut pengaman.';

    /** Draft yang dishare admin diberi tenggang lebih panjang sebelum dibatalkan. */
    private const DRAFT_JAM = 24;

    public function handle(QrisService $qris): int
    {
        $now = now();
        $batasDraft = $now->copy()->subHours(self::DRAFT_JAM);
        $count = 0;
        $diselamatkan = 0;

        // Dilewati demi keamanan (QR pernah dibuat, status lunas tak terkonfirmasi).
        // Dihitung supaya keluarannya JUJUR: sebelumnya command ini melaporkan
        // "dibatalkan: 0" tanpa menjelaskan bahwa ada order yang sengaja tidak
        // disentuh, sehingga terlihat seolah perintahnya tidak bekerja.
        $dilewatiAman = 0;
        $takBisaDicek = 0;

        // Sapuan order TUA (opsional, default mati supaya cron tetap konservatif).
        $tuaJam = (int) $this->option('tua');
        $batasTua = $tuaJam > 0 ? $now->copy()->subHours($tuaJam) : null;

        // Pending: order di daftar, kedaluwarsa mengikuti QRIS (±30 menit).
        // Draft: admin "Simpan ke Draft" setelah share QR — sengaja diberi 24 jam
        //        supaya customer punya waktu bayar, tidak ikut aturan 30 menit.
        Order::whereIn('status', ['pending', 'draft'])
            // Jangan sentuh order yang sudah punya pembayaran lunas.
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'settlement'))
            ->with(['payments' => fn ($q) => $q->latest()])
            ->chunkById(200, function ($orders) use (&$count, &$diselamatkan, &$dilewatiAman, &$takBisaDicek, $now, $batasDraft, $batasTua, $tuaJam, $qris) {
                foreach ($orders as $order) {
                    $expired = false;

                    if ($order->status === 'draft') {
                        // Draft: hanya kedaluwarsa setelah 24 jam sejak dibuat.
                        // Kedaluwarsa QR 30 menit sengaja DIABAIKAN untuk draft.
                        $expired = $order->created_at
                            && $order->created_at->lessThanOrEqualTo($batasDraft);
                    } else {
                        $latest = $order->payments->first();

                        // 1) QRIS terakhir sudah kedaluwarsa (batas waktu pembayaran lewat).
                        if ($latest && ($latest->status === 'expire'
                            || ($latest->expired_at && $latest->expired_at->lessThanOrEqualTo($now)))) {
                            $expired = true;
                        }

                        // 2) Masa berlaku order-nya sendiri sudah habis (pengaman).
                        if ($order->expired_at && $order->expired_at->lessThanOrEqualTo($now)) {
                            $expired = true;
                        }
                    }

                    if (! $expired) {
                        continue;
                    }

                    if ($order->payment_method === 'qris_dinamis') {
                        // checkStatus() punya TIGA jawaban dan bedanya penting:
                        //   'paid'   = penyedia memastikan sudah dibayar
                        //   'unpaid' = penyedia memastikan BELUM dibayar
                        //   'error'  = tidak bisa diperiksa (tak ada trx id, HTTP
                        //              gagal, konfigurasi kosong) — BUKAN bukti
                        //              apa pun, jadi tidak boleh dianggap belum bayar.
                        $statusQris = $order->qris_trx_id ? $qris->checkStatus($order) : 'error';

                        if ($statusQris === 'paid') {
                            $order->update(['status' => 'paid', 'paid_at' => now()]);
                            $order->payments()->where('status', 'pending')->update(['status' => 'settlement']);
                            $diselamatkan++;
                            Log::info('QRIS: order '.$order->order_number.' hampir dibatalkan tapi ternyata sudah dibayar → paid.');

                            continue;
                        }

                        // KRITIS: QR sudah PERNAH dibuat (ada qris_trx_id ATAU ada
                        // payment record) → mungkin SUDAH DIBAYAR tapi deteksi gagal.
                        // JANGAN batalkan otomatis — dulu ini membatalkan order yang
                        // uangnya sudah masuk.
                        if ($order->qris_trx_id || $order->payments->isNotEmpty()) {
                            // Sapuan order TUA (--tua=JAM): order yang sudah lama
                            // sekali menggantung boleh dibatalkan, TAPI hanya kalau
                            // penyedia QRIS secara TEGAS bilang belum dibayar.
                            // Jawaban 'error' tidak pernah cukup — lebih baik order
                            // tertinggal menggantung daripada membatalkan yang
                            // uangnya sudah masuk.
                            $cukupTua = $batasTua !== null
                                && $order->created_at
                                && $order->created_at->lessThanOrEqualTo($batasTua);

                            if (! ($cukupTua && $statusQris === 'unpaid')) {
                                if ($cukupTua && $statusQris === 'error') {
                                    $takBisaDicek++;
                                    Log::warning('QRIS: order '.$order->order_number.' cukup tua TAPI status ke penyedia tak bisa diperiksa → TIDAK dibatalkan.');
                                } else {
                                    $dilewatiAman++;
                                    Log::warning('QRIS: order '.$order->order_number.' kedaluwarsa & tak terkonfirmasi lunas TAPI QR sudah pernah dibuat → TIDAK dibatalkan otomatis (cegah cancel order terbayar).');
                                }

                                continue;
                            }

                            Log::info('QRIS: order '.$order->order_number.' dibatalkan lewat sapuan tua (umur ≥ '.$tuaJam.' jam & penyedia QRIS memastikan belum dibayar).');
                        }
                        // Sampai sini: QRIS tanpa QR sama sekali (checkout
                        // ditinggalkan sebelum QR dibuat = pasti belum bayar),
                        // atau order tua yang sudah dipastikan belum dibayar.
                    }

                    // Order non-QRIS, atau QRIS yang QR-nya tak pernah dibuat → aman dibatalkan.
                    $order->payments()->where('status', 'pending')->update(['status' => 'expire']);
                    $order->update(['status' => 'cancelled']);
                    $count++;
                }
            });

        // Keluaran dibuat JUJUR & lengkap. Versi lama hanya menyebut jumlah yang
        // dibatalkan, sehingga saat semua order tersangkut pengaman hasilnya
        // "dibatalkan: 0" tanpa alasan — terbaca seolah perintahnya rusak.
        $this->info("Dibatalkan: {$count}. Diselamatkan (ternyata sudah dibayar): {$diselamatkan}.");

        if ($dilewatiAman > 0) {
            $this->warn("Dilewati demi aman: {$dilewatiAman} (QR pernah dibuat & belum terbukti belum dibayar)."
                .($tuaJam > 0 ? '' : ' Pakai --tua=24 untuk menyapu yang sudah lebih dari 24 jam.'));
        }

        if ($takBisaDicek > 0) {
            $this->warn("Cukup tua tapi status tak bisa diperiksa ke penyedia QRIS: {$takBisaDicek}. Perlu dibatalkan manual dari halaman admin.");
        }

        return self::SUCCESS;
    }
}
