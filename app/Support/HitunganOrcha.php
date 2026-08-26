<?php

namespace App\Support;

use App\Services\OrchaClient;
use Illuminate\Support\Facades\Cache;

/**
 * Dasar bagi hitungan kecil yang diambil dari Orcha untuk penanda di lemon.
 *
 * Dua sifat yang wajib, karena turunannya dipanggil di SETIAP halaman admin:
 *
 * 1. Disimpan sebentar. Tanpa itu, tiap perpindahan halaman menembak Orcha
 *    sekali lagi hanya untuk beberapa bilangan yang jarang berubah.
 * 2. Tidak pernah melempar. Orcha mati tidak boleh merobohkan seluruh admin
 *    lemon — yang hilang cukup penandanya.
 *
 * Dikumpulkan di satu tempat supaya kedua aturan itu tidak perlu ditulis ulang
 * tiap kali ada penanda baru, dan supaya tidak ada turunan yang diam-diam lupa
 * salah satunya.
 */
abstract class HitunganOrcha
{
    /** Detik. Cukup lama untuk menghemat, cukup pendek untuk terasa hidup. */
    protected const SIMPAN_DETIK = 60;

    /** Kunci simpanan; wajib berbeda tiap turunan. */
    abstract protected static function kunci(): string;

    /** Jalur API Orcha yang ditanya. */
    abstract protected static function jalur(): string;

    /**
     * Bentuk jawaban saat Orcha tidak bisa dihubungi. Bentuknya harus sama
     * dengan jawaban sungguhan supaya pemakainya tidak perlu memeriksa dua
     * kemungkinan.
     *
     * @return array<string, int>
     */
    abstract protected static function bawaan(): array;

    /** @return array<string, int> */
    public static function ambil(): array
    {
        return Cache::remember(static::kunci(), static::SIMPAN_DETIK, function () {
            try {
                $data = app(OrchaClient::class)->ambil(static::jalur())['data'] ?? [];
            } catch (\Throwable $e) {
                // Sengaja diam: penanda ini tergambar di tiap halaman, dan
                // gangguan sambungan tidak pantas jadi halaman galat.
                return static::bawaan();
            }

            return collect(static::bawaan())
                ->map(fn ($bawaan, $nama) => (int) ($data[$nama] ?? $bawaan))
                ->all();
        });
    }

    /**
     * Melupakan simpanan.
     *
     * Dipanggil sesudah admin mengerjakan salah satunya: penandanya harus ikut
     * turun saat itu juga, bukan semenit kemudian — kalau tidak, admin mengira
     * pekerjaannya tidak tersimpan lalu mengulanginya.
     */
    public static function lupakan(): void
    {
        Cache::forget(static::kunci());
    }

    /**
     * Seluruh penanda yang ada, didaftar di satu tempat.
     *
     * Dipakai untuk melupakan semuanya sekaligus sesudah admin mengubah apa pun
     * di Orcha. Satu perubahan status bisa menggeser lebih dari satu hitungan —
     * menyetujui pembatalan, misalnya, ikut membatalkan pesanannya — dan
     * menebak mana saja yang terpengaruh di tiap tempat pemanggilan adalah cara
     * paling pasti untuk melewatkan salah satunya.
     *
     * @return array<int, class-string<self>>
     */
    public static function semua(): array
    {
        return [
            OrchaMenungguDicek::class,
            OrchaSewaPerhatian::class,
            OrchaPembatalanPerhatian::class,
            OrchaPesanPerhatian::class,
            OrchaPendaftaranPerhatian::class,
        ];
    }

    /** Melupakan simpanan SEMUA penanda. */
    public static function lupakanSemua(): void
    {
        foreach (self::semua() as $penanda) {
            $penanda::lupakan();
        }
    }
}
