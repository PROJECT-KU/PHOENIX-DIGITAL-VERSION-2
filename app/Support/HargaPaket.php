<?php

namespace App\Support;

use App\Models\ProductBundlings;
use App\Models\Promo;

/**
 * Harga tayang sebuah paket bundling: sesudah promo bila ada, apa adanya bila tidak.
 *
 * Dikumpulkan di satu tempat karena harga paket muncul di banyak halaman —
 * beranda, daftar bundling, jendela detail, etalase flash sale. Bila tiap
 * halaman menghitung sendiri, cepat atau lambat salah satunya menampilkan angka
 * yang berbeda dari yang ditagih di keranjang, dan pembeli membacanya sebagai
 * harga yang berubah diam-diam saat checkout.
 *
 * Aturan potongannya SAMA PERSIS dengan PromoService: dihitung dari `harga_awal`
 * (harga coret), lalu dikurangkan dari `harga_bundling` yang dibayar, dan
 * dibatasi setinggi harga paket supaya tidak pernah minus.
 */
class HargaPaket
{
    /**
     * Promo aktif yang dilampirkan ke paket-paket ini, dimuat sekali saja.
     *
     * @var \Illuminate\Support\Collection<string,\Illuminate\Support\Collection>|null
     */
    private static $petaPromo = null;

    /**
     * @return array{bayar:int, coret:int, potongan:int, promo:?Promo, butuh_kode:bool}
     */
    public static function untuk(ProductBundlings $paket): array
    {
        $awal = self::angka($paket->harga_awal);
        $bundling = self::angka($paket->harga_bundling);

        $polos = [
            'bayar' => $bundling,
            'coret' => $awal > $bundling ? $awal : 0,
            'potongan' => 0,
            'promo' => null,
            'butuh_kode' => false,
        ];

        if ($bundling <= 0) {
            return $polos;
        }

        $promo = self::promoTerbaik($paket, $bundling, $awal);

        if (! $promo) {
            return $polos;
        }

        $potongan = self::potongan($promo, $bundling, $awal);

        if ($potongan <= 0) {
            return $polos;
        }

        // Begitu paket kena promo, dasar hitungnya adalah HARGA AWAL, bukan
        // harga paket: harga paket diabaikan dan potongan promo dikurangkan
        // dari harga awal. Contoh: awal 125.000, paket 99.000, promo 10% ->
        // potongan 12.500, dibayar 112.500.
        //
        // Konsekuensi yang disadari pemilik: bila persentase promo lebih kecil
        // daripada potongan paket itu sendiri, harga promo bisa lebih mahal
        // daripada harga paket biasa. Tidak ada pengaman otomatis; pemilihan
        // persentase ada di tangan admin.
        $dasar = $awal > 0 ? $awal : $bundling;
        $bayar = max(0, $dasar - $potongan);

        return [
            'bayar' => $bayar,
            // Yang dicoret adalah harga awal, yaitu harga bila produknya
            // dibeli satuan.
            'coret' => $dasar > $bayar ? $dasar : 0,
            'potongan' => $potongan,
            'promo' => $promo,
            'butuh_kode' => $promo->tipe_promo === 'kode_promo',
        ];
    }

    /** Promo aktif yang memberi potongan TERBESAR untuk paket ini. */
    private static function promoTerbaik(ProductBundlings $paket, int $bundling, int $awal): ?Promo
    {
        $kandidat = self::peta()->get((string) $paket->id);

        if (! $kandidat || $kandidat->isEmpty()) {
            return null;
        }

        return $kandidat
            ->sortByDesc(fn (Promo $p) => self::potongan($p, $bundling, $awal))
            ->first();
    }

    /**
     * Peta paket -> promo aktif yang melampirkannya.
     *
     * Dimuat SEKALI per permintaan. Tanpa ini, halaman berisi sembilan paket
     * menembak database sembilan kali hanya untuk memasang label harga.
     */
    private static function peta()
    {
        if (self::$petaPromo !== null) {
            return self::$petaPromo;
        }

        $promos = Promo::active()->with('bundlings:id')->get();

        $peta = collect();

        foreach ($promos as $promo) {
            foreach ($promo->bundlings as $b) {
                $peta->put(
                    (string) $b->id,
                    ($peta->get((string) $b->id) ?? collect())->push($promo)
                );
            }
        }

        return self::$petaPromo = $peta;
    }

    /**
     * Besar potongan untuk satu promo — cerminan PromoService.
     *
     * Basisnya `harga_awal` bila diisi; bila tidak, jatuh ke harga paket supaya
     * perilakunya sama seperti produk biasa, bukan nol.
     */
    private static function potongan(Promo $promo, int $bundling, int $awal): int
    {
        $basis = $awal > 0 ? $awal : $bundling;

        $potongan = $promo->tipe_diskon === 'persen'
            ? (int) floor($basis * (float) $promo->getDiskonValue(false, 'persen') / 100)
            : (int) $promo->getDiskonValue(false, 'nominal');

        // Batasnya dasar hitung itu sendiri, bukan harga paket: yang
        // dikurangkan memang harga awal (lihat untuk()).
        return max(0, min($potongan, $basis));
    }

    private static function angka($nilai): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $nilai);
    }

    /**
     * Harga dasar paket di keranjang, SEBELUM potongan promo dikurangkan.
     *
     * Saat paket kena promo, dasarnya harga awal (karena potongan dikurangkan
     * dari harga awal); bila tidak kena promo, dasarnya harga paket. Dengan
     * begitu PromoService cukup mengurangkan potongan seperti biasa dan yang
     * ditagih selalu sama dengan yang tertulis di layar.
     */
    public static function dasarKeranjang(ProductBundlings $paket): int
    {
        $h = self::untuk($paket);

        return $h['bayar'] + $h['potongan'];
    }

    /**
     * Menyetel ulang baris paket di keranjang mengikuti keadaan promo saat ini.
     *
     * Promo bisa mulai atau berakhir setelah paket masuk keranjang, jadi harga
     * dasarnya dihitung ulang tiap kali keranjang dipakai, bukan dibekukan.
     */
    public static function selaraskanKeranjang(array $cart): array
    {
        foreach ($cart as $kunci => $baris) {
            if (($baris['type'] ?? null) !== 'bundling') {
                continue;
            }

            $paket = ProductBundlings::find($baris['product_id'] ?? null);

            if (! $paket) {
                continue;
            }

            $dasar = self::dasarKeranjang($paket);
            $jumlah = max(1, (int) ($baris['quantity'] ?? 1));

            $cart[$kunci]['price'] = $dasar;
            $cart[$kunci]['subtotal'] = $dasar * $jumlah;
        }

        return $cart;
    }

    /** Dipakai pengujian agar peta tidak terbawa antar-skenario. */
    public static function lupakan(): void
    {
        self::$petaPromo = null;
    }
}
