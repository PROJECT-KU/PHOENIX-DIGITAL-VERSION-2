<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Umpan produk untuk Katalog Meta (Commerce Manager).
 *
 * Meta menarik sendiri alamat ini secara terjadwal, lalu isinya dipakai untuk
 * katalog WhatsApp/Instagram dan iklan dinamis. Karena ditarik — bukan dikirim —
 * produk yang ditambah atau diubah di admin ikut memperbarui katalog tanpa ada
 * yang perlu mengunggah apa pun.
 *
 * Formatnya RSS 2.0 dengan namespace g:, standar yang sama dipakai Google
 * Merchant. Meta menerimanya apa adanya.
 */
class KatalogMetaController extends Controller
{
    /**
     * Berapa lama isi umpan disimpan sebelum dibangun ulang.
     *
     * Meta menarik paling sering sekali per jam, jadi membangun ulang tiap
     * permintaan hanya membebani database tanpa mempercepat apa pun.
     */
    private const SIMPAN_MENIT = 60;

    /** Meta menolak seluruh item bila deskripsinya melebihi batas ini. */
    private const MAKS_DESKRIPSI = 5000;

    public function __invoke(): Response
    {
        $xml = Cache::remember('katalog-meta-xml', now()->addMinutes(self::SIMPAN_MENIT), fn () => $this->bangun());

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function bangun(): string
    {
        $item = Product::with('prices')
            ->orderBy('nama_akun')
            ->get()
            ->map(fn (Product $p) => $this->baris($p))
            ->filter()
            ->implode("\n");

        $judul = $this->aman(config('app.name', 'Phoenix Digital Warehouse'));

        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
        <channel>
        <title>{$judul}</title>
        <link>{$this->aman(rtrim(config('app.url'), '/'))}</link>
        <description>Katalog produk {$judul}</description>
        {$item}
        </channel>
        </rss>
        XML;
    }

    /**
     * Satu produk menjadi satu item, atau null bila belum layak dikirim.
     *
     * Sengaja MELEWATI produk tanpa harga atau tanpa gambar. Meta menolak item
     * seperti itu satu per satu dan menandai umpannya bermasalah — lebih baik
     * tidak dikirim sama sekali daripada katalog penuh peringatan yang menutupi
     * kesalahan sungguhan.
     */
    private function baris(Product $p): ?string
    {
        $harga = $this->hargaTerendah($p);

        if ($harga <= 0 || blank($p->image)) {
            return null;
        }

        $tautan = route('shop.detail-product', $p->id);
        $gambar = asset('storage/img/Product/'.basename((string) $p->image));

        // Deskripsi wajib ada. Bila kosong, pakai namanya — item tanpa
        // deskripsi ditolak Meta.
        $deskripsi = trim(strip_tags((string) $p->deskripsi));
        $deskripsi = $deskripsi !== '' ? $deskripsi : (string) $p->nama_akun;
        $deskripsi = mb_substr($deskripsi, 0, self::MAKS_DESKRIPSI);

        return implode("\n", [
            '<item>',
            '<g:id>'.$this->aman($p->id).'</g:id>',
            '<g:title>'.$this->aman(mb_substr((string) $p->nama_akun, 0, 150)).'</g:title>',
            '<g:description>'.$this->aman($deskripsi).'</g:description>',
            '<g:link>'.$this->aman($tautan).'</g:link>',
            '<g:image_link>'.$this->aman($gambar).'</g:image_link>',
            '<g:availability>in stock</g:availability>',
            '<g:condition>new</g:condition>',
            // Meta mensyaratkan mata uang menyertai angkanya, dipisah spasi.
            '<g:price>'.$harga.' IDR</g:price>',
            '<g:brand>'.$this->aman(config('app.name', 'Phoenix Digital Warehouse')).'</g:brand>',
            '<g:product_type>'.$this->aman($this->jenis($p)).'</g:product_type>',
            '</item>',
        ]);
    }

    /**
     * Harga termurah di antara semua durasi.
     *
     * Katalog Meta hanya menampung SATU harga per item, sedangkan di sini satu
     * produk punya beberapa durasi. Yang termurah dipakai supaya angka di
     * katalog tidak pernah lebih tinggi daripada yang sebenarnya bisa dibayar
     * pembeli — harga yang kelewat tinggi mengusir orang sebelum mereka membuka
     * halaman produknya.
     */
    private function hargaTerendah(Product $p): int
    {
        $daftar = $p->daftarHarga()->pluck('harga')->filter(fn ($h) => (int) $h > 0);

        return (int) ($daftar->min() ?? 0);
    }

    private function jenis(Product $p): string
    {
        if ($p->butuh_file) {
            return 'Jasa';
        }

        return filled($p->tipe_akun) ? (string) $p->tipe_akun : 'Akun Premium';
    }

    /** Tanpa ini satu tanda & pada nama produk merusak seluruh umpan. */
    private function aman(?string $teks): string
    {
        return htmlspecialchars((string) $teks, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
