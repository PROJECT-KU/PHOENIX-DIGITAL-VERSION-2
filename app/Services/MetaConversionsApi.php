<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pengiriman peristiwa PEMBELIAN ke Meta lewat Conversions API (server-ke-server).
 *
 * Kenapa dari server, bukan dari pixel di browser seperti tiga peristiwa lain:
 * pesanan di sini baru sah lunas setelah Midtrans/poller QRIS mengonfirmasinya.
 * Saat itu tiba, browser pembeli sering sudah ditutup — pixel tidak akan pernah
 * terpicu, dan penjualannya hilang dari laporan iklan. Peristiwa yang dikirim
 * dari server tidak bergantung pada browser sama sekali.
 *
 * Pembagiannya sengaja tidak tumpang tindih, supaya tidak perlu pencocokan ganda:
 *
 *   Browser : ViewContent, AddToCart, InitiateCheckout
 *   Server  : Purchase
 *
 * Data pribadi TIDAK pernah dikirim apa adanya. Email, telepon, dan nama diacak
 * jadi sidik jari SHA-256 lebih dulu (syarat Meta), sehingga yang menyeberang
 * hanya deretan huruf yang cuma berguna untuk mencocokkan, bukan untuk dibaca.
 */
class MetaConversionsApi
{
    /** Umur maksimum peristiwa yang masih mau diterima Meta, dalam hari. */
    private const BATAS_HARI = 7;

    /**
     * Kirim Pembelian untuk satu pesanan. Aman dipanggil berkali-kali.
     *
     * Selalu gagal-diam: laporan iklan tidak boleh bisa menggagalkan pembayaran.
     * Callback Midtrans yang error akan diulang gateway, dan pengulangan itu
     * berisiko menggandakan sisi keuangan — jauh lebih mahal daripada satu
     * peristiwa iklan yang tidak tercatat.
     */
    public function kirimPembelian(Order $order): void
    {
        try {
            if (! $this->aktif() || $order->meta_purchase_sent_at !== null) {
                return;
            }

            // Meta menolak peristiwa yang lebih tua dari 7 hari (error 2804003).
            // Ini bukan kasus langka: admin kadang menyelesaikan pesanan lama
            // secara massal, dan tanpa penjagaan ini setiap pesanan begitu
            // menembak API lalu gagal — memperlambat penyimpanan dan
            // membanjiri log dengan peringatan yang tak bisa ditindaklanjuti.
            if ($this->waktuPeristiwa($order)->lt(now()->subDays(self::BATAS_HARI))) {
                Order::whereKey($order->getKey())->update(['meta_purchase_sent_at' => now()]);

                return;
            }

            $balasan = Http::connectTimeout(5)
                ->timeout(10)
                ->asJson()
                ->post($this->endpoint(), $this->muatan($order));

            if ($balasan->failed()) {
                Log::warning('Meta CAPI Purchase ditolak', [
                    'order' => $order->order_number,
                    'status' => $balasan->status(),
                    'body' => $balasan->json(),
                ]);

                return;
            }

            // Ditulis lewat query builder, BUKAN $order->update(): menyimpan
            // model akan memicu ulang observer Order yang justru memanggil
            // method ini.
            Order::whereKey($order->getKey())->update(['meta_purchase_sent_at' => now()]);

            $order->setAttribute('meta_purchase_sent_at', now());
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI Purchase gagal: '.$e->getMessage(), [
                'order' => $order->order_number ?? null,
            ]);
        }
    }

    /** Kapan pembelian itu terjadi — waktu lunas, bukan waktu kita mengirimnya. */
    private function waktuPeristiwa(Order $order): \Illuminate\Support\Carbon
    {
        return $order->paid_at ?? $order->updated_at ?? now();
    }

    /** Pengiriman hanya hidup bila token sudah dipasang di .env server. */
    public function aktif(): bool
    {
        return filled(config('services.meta.capi_token'))
            && filled(config('services.meta.pixel_id'));
    }

    private function endpoint(): string
    {
        return sprintf(
            'https://graph.facebook.com/%s/%s/events?access_token=%s',
            config('services.meta.api_version'),
            config('services.meta.pixel_id'),
            urlencode((string) config('services.meta.capi_token')),
        );
    }

    /**
     * Badan permintaan lengkap untuk satu pesanan.
     *
     * @return array<string,mixed>
     */
    public function muatan(Order $order): array
    {
        $data = [
            'event_name' => 'Purchase',
            'event_time' => $this->waktuPeristiwa($order)->getTimestamp(),

            // Penanda anti-dobel. Nomor pesanan dipakai apa adanya karena sudah
            // unik dan tetap sama andai Midtrans mengirim notifikasi dua kali.
            'event_id' => (string) $order->order_number,

            'action_source' => 'website',

            // Sengaja halaman checkout, BUKAN URL sukses pesanan: URL sukses
            // memuat token rahasia pelanggan (lihat App\Support\JalurAnalitik),
            // dan mengirimnya berarti menitipkan kunci pesanan itu ke Facebook.
            'event_source_url' => route('checkout'),

            'user_data' => $this->dataPengguna($order),
            'custom_data' => $this->dataPesanan($order),
        ];

        $muatan = ['data' => [$data]];

        if (filled($kode = config('services.meta.test_event_code'))) {
            $muatan['test_event_code'] = $kode;
        }

        return $muatan;
    }

    /**
     * Identitas pembeli — seluruhnya sudah diacak kecuali yang dilarang Meta.
     *
     * @return array<string,mixed>
     */
    private function dataPengguna(Order $order): array
    {
        $pelanggan = $order->customer;

        $data = array_filter([
            'em' => $this->acak($pelanggan?->email),
            'ph' => $this->acak($this->nomorInternasional($pelanggan?->no_hp)),
            'fn' => $this->acak($this->namaDepan($pelanggan?->nama)),
            'ln' => $this->acak($this->namaBelakang($pelanggan?->nama)),
            'external_id' => $this->acak($pelanggan?->getKey()),
        ]);

        // Nilainya harus berupa daftar; Meta menolak string telanjang.
        $data = array_map(fn ($nilai) => [$nilai], $data);

        // fbp/fbc adalah pengenal Meta sendiri — justru TIDAK boleh diacak.
        // Kosong bila pembeli datang bukan dari iklan, dan itu wajar.
        if (filled($order->fbp)) {
            $data['fbp'] = $order->fbp;
        }

        if (filled($order->fbc)) {
            $data['fbc'] = $order->fbc;
        }

        return $data;
    }

    /**
     * Nilai transaksi dan isi keranjangnya.
     *
     * @return array<string,mixed>
     */
    private function dataPesanan(Order $order): array
    {
        $isi = $order->items->map(fn ($item) => [
            'id' => (string) $item->product_id,
            'quantity' => (int) $item->quantity,
            'item_price' => (float) $item->price,
        ])->all();

        return [
            // total, bukan subtotal: inilah uang yang benar-benar diterima —
            // sudah dipotong promo/poin dan sudah termasuk kode unik.
            'value' => (float) $order->total,
            'currency' => 'IDR',
            'order_id' => (string) $order->order_number,
            'content_type' => 'product',
            'contents' => $isi,
            'content_ids' => array_column($isi, 'id'),
            'num_items' => (int) $order->items->sum('quantity'),
        ];
    }

    /**
     * Aturan Meta: rapikan dulu (huruf kecil, tanpa spasi tepi), baru SHA-256.
     * Tanpa perapian, "Budi@Mail.com" dan "budi@mail.com" jadi dua orang berbeda.
     */
    private function acak(?string $nilai): ?string
    {
        $bersih = trim(mb_strtolower((string) $nilai));

        return $bersih === '' ? null : hash('sha256', $bersih);
    }

    /**
     * Nomor HP ke bentuk internasional tanpa tanda baca: "081339139595" -> "6281339139595".
     * Meta mencocokkan nomor apa adanya, jadi format harus seragam dulu.
     */
    private function nomorInternasional(?string $nomor): ?string
    {
        $inti = Customer::normalisasiNoHp($nomor);

        return $inti === '' ? null : '62'.$inti;
    }

    private function namaDepan(?string $nama): ?string
    {
        $bagian = preg_split('/\s+/', trim((string) $nama), -1, PREG_SPLIT_NO_EMPTY);

        return $bagian[0] ?? null;
    }

    /** Null bila pelanggan hanya menulis satu kata — jangan kirim nama depan dua kali. */
    private function namaBelakang(?string $nama): ?string
    {
        $bagian = preg_split('/\s+/', trim((string) $nama), -1, PREG_SPLIT_NO_EMPTY);

        return count($bagian) > 1 ? end($bagian) : null;
    }
}
