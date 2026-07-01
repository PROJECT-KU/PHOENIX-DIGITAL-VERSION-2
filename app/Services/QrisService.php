<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Integrasi QRIS Dinamis qris.online / OkeConnect (QRIS Interactive).
 *
 * - createInvoice(): generate QR dinamis (EMV payload) untuk nominal order.
 * - checkStatus():   cek apakah invoice sudah dibayar.
 *
 * Field response provider diparse secara toleran (nama field bisa berbeda
 * antar versi API), dan setiap response mentah dicatat ke log untuk debugging.
 */
class QrisService
{
    protected string $baseUrl;

    protected ?string $mid;

    protected ?string $apikey;

    protected int $expiryMinutes;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.qris.base_url'), '/');
        $this->mid = config('services.qris.mid');
        $this->apikey = config('services.qris.apikey');
        $this->expiryMinutes = (int) config('services.qris.expiry_minutes', 30);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->mid) && ! empty($this->apikey);
    }

    /**
     * HTTP client. Verifikasi SSL dimatikan di lingkungan non-produksi
     * (XAMPP/Laragon lokal sering tidak punya CA bundle → cURL error 60),
     * sehingga panggilan yang sukses di Postman juga sukses dari Laravel.
     */
    protected function http()
    {
        return Http::timeout(25)
            ->acceptJson()
            ->withOptions(['verify' => app()->isProduction()]);
    }

    /**
     * Generate QR dinamis baru untuk order & simpan payload-nya ke order.
     *
     * @return array{success: bool, message?: string}
     */
    public function createInvoice(Order $order): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Kredensial QRIS belum dikonfigurasi.'];
        }

        // Nomor transaksi unik tiap generate (regenerate = nomor baru)
        $trx = $order->order_number.'-'.strtoupper(Str::random(4));
        $amount = (int) round((float) $order->total);

        try {
            $response = $this->http()
                ->get($this->baseUrl.'/show_qris.php', [
                    'do' => 'create-invoice',
                    'apikey' => $this->apikey,
                    'mID' => $this->mid,
                    'cliTrxNumber' => $trx,
                    'cliTrxAmount' => $amount,
                    'useTip' => 'no',
                ]);

            $json = $response->json();
            Log::info('QRIS create-invoice', ['order' => $order->order_number, 'trx' => $trx, 'resp' => $json]);

            if (! $response->ok() || ! is_array($json)) {
                return ['success' => false, 'message' => 'Tidak dapat menghubungi server QRIS.'];
            }

            $status = strtolower((string) ($json['status'] ?? ''));
            // Saat gagal, "data" bisa berupa string (mis. "failed, apikey not registered")
            $data = is_array($json['data'] ?? null) ? $json['data'] : [];

            $content = $data['qris_content'] ?? null;
            $invoiceId = $data['qris_invoiceid'] ?? null;

            if ($status !== 'success' || empty($content) || empty($invoiceId)) {
                $msg = $data['qris_status']
                    ?? (is_string($json['data'] ?? null) ? $json['data'] : null)
                    ?? $json['message']
                    ?? 'Gagal membuat QRIS.';

                return ['success' => false, 'message' => is_string($msg) ? $msg : 'Gagal membuat QRIS.'];
            }

            // Tanggal generate (dipakai sebagai trxdate saat cek status)
            $requestDate = ! empty($data['qris_request_date'])
                ? \Illuminate\Support\Carbon::parse($data['qris_request_date'])->toDateString()
                : now()->toDateString();

            $order->update([
                'payment_method' => 'qris_dinamis',
                'payment_gateway' => 'qris.interactive',
                'payment_reference' => $trx,
                'qris_content' => $content,
                'qris_trx_id' => $invoiceId,
                'qris_request_date' => $requestDate,
                'expired_at' => now()->addMinutes($this->expiryMinutes),
            ]);

            return ['success' => true];
        } catch (\Throwable $e) {
            Log::error('QRIS create-invoice error: '.$e->getMessage());

            // Tampilkan pesan asli di lingkungan non-produksi agar mudah didiagnosa
            $msg = app()->isProduction()
                ? 'Terjadi kesalahan saat membuat QRIS.'
                : 'QRIS error: '.$e->getMessage();

            return ['success' => false, 'message' => $msg];
        }
    }

    /**
     * Cek status pembayaran invoice.
     *
     * @return string 'paid' | 'unpaid' | 'error'
     */
    public function checkStatus(Order $order): string
    {
        if (! $this->isConfigured() || empty($order->qris_trx_id)) {
            return 'error';
        }

        try {
            $trxDate = $order->qris_request_date
                ? \Illuminate\Support\Carbon::parse($order->qris_request_date)->toDateString()
                : now()->toDateString();

            $response = $this->http()
                ->get($this->baseUrl.'/checkpaid_qris.php', [
                    'do' => 'checkStatus',
                    'apikey' => $this->apikey,
                    'mID' => $this->mid,
                    'invid' => $order->qris_trx_id,
                    'trxvalue' => (int) round((float) $order->total),
                    'trxdate' => $trxDate,
                ]);

            $json = $response->json();

            if (! $response->ok() || ! is_array($json)) {
                return 'error';
            }

            $data = is_array($json['data'] ?? null) ? $json['data'] : [];
            $qrisStatus = strtolower((string) ($data['qris_status'] ?? ''));

            return $qrisStatus === 'paid' ? 'paid' : 'unpaid';
        } catch (\Throwable $e) {
            Log::error('QRIS checkStatus error: '.$e->getMessage());

            return 'error';
        }
    }
}
