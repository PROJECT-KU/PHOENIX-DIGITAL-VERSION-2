<?php

namespace App\Support;

use App\Mail\FiturPublikDitutupMail;
use App\Models\Customer;

/**
 * Mengabari PEMBELI saat satu halaman toko ditutup atau dibuka kembali.
 *
 * Penerimanya punya dua sumber yang saling meniadakan, sama seperti kabar ke
 * karyawan:
 *
 *  - Selama JEDA_EMAIL_UJI terisi, seluruh kabar dikirim ke alamat itu saja.
 *  - Dikosongkan (keadaan yang dituju di server), penerimanya diambil dari
 *    data pelanggan.
 *
 * Satu sakelar untuk keduanya, supaya tidak ada yang lupa dikosongkan saat
 * deploy dan pelanggan sungguhan tiba-tiba kebanjiran surel percobaan.
 */
class KabarFiturPublik
{
    /**
     * Alamat pelanggan yang akan dikabari.
     *
     * @return array<int, string>
     */
    public static function penerima(): array
    {
        $uji = trim((string) config('jeda.email_uji'));

        if ($uji !== '') {
            return [$uji];
        }

        return Customer::query()
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->pluck('email')
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /** Sedang memakai alamat uji coba, bukan data pelanggan? */
    public static function modeUji(): bool
    {
        return trim((string) config('jeda.email_uji')) !== '';
    }

    /** Berapa pelanggan yang akan dikabari bila tombolnya ditekan sekarang. */
    public static function jumlahPenerima(): int
    {
        return count(self::penerima());
    }

    /**
     * Kirim kabarnya lewat BCC.
     *
     * Kegagalan kirim tidak boleh membatalkan penutupan halamannya: halaman
     * ditutup karena ada yang perlu diperbaiki, dan surel yang gagal adalah
     * urusan yang jauh lebih ringan.
     *
     * @return int jumlah alamat yang dikirimi
     */
    public static function kirim(
        string $namaHalaman,
        bool $ditutup,
        string $pesan,
        ?\Illuminate\Support\Carbon $mulai = null,
        ?\Illuminate\Support\Carbon $sampai = null,
    ): int {
        return KirimMassal::bcc(
            self::penerima(),
            fn () => new FiturPublikDitutupMail($namaHalaman, $ditutup, $pesan, $mulai, $sampai),
            'phoenix',
        );
    }
}
