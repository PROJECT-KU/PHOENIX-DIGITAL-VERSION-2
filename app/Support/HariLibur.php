<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Hari libur nasional & hari peringatan, untuk ditandai di kalender kegiatan.
 *
 * Dibagi dua karena sifatnya benar-benar berbeda:
 *
 *  - TANGGAL TETAP jatuh pada tanggal yang sama setiap tahun. Ini pasti, dan
 *    aman ditulis di kode.
 *  - TANGGAL BERGERAK (Idul Fitri, Nyepi, Waisak, Imlek, dan seterusnya)
 *    ditetapkan pemerintah lewat SKB tiga menteri, berbeda tiap tahun, dan
 *    kadang bergeser sehari dari hasil hitungan kalender mana pun.
 *
 * Yang bergerak SENGAJA tidak dihitung sendiri oleh kode ini. Menghitungnya
 * lewat konversi kalender Hijriah akan menghasilkan tanggal yang kelihatan
 * meyakinkan tapi bisa meleset sehari — dan tanggal libur yang salah di
 * kalender perusahaan bukan kesalahan kecil: orang mengatur cuti, rapat, dan
 * tenggat berdasarkan itu. Lebih baik tidak menampilkan apa-apa daripada
 * menampilkan tanggal karangan.
 *
 * Isi BERGERAK dari SKB begitu terbit. Selama belum diisi, kalender hanya
 * menandai tanggal tetap, dan sisanya bisa ditambahkan sebagai kegiatan
 * berjenis "Libur" seperti biasa.
 */
class HariLibur
{
    /**
     * Tanggal yang sama setiap tahun. Kunci: 'mm-dd'.
     *
     * 'libur' true = tanggal merah (hari libur nasional). false = hari
     * peringatan yang TETAP HARI KERJA — dibedakan tegas supaya tidak ada yang
     * mengira Hari Pelanggan Nasional itu hari libur lalu tidak masuk.
     *
     * @var array<string, array{nama: string, libur: bool}>
     */
    public const TETAP = [
        '01-01' => ['nama' => 'Tahun Baru Masehi', 'libur' => true],
        '05-01' => ['nama' => 'Hari Buruh Internasional', 'libur' => true],
        '06-01' => ['nama' => 'Hari Lahir Pancasila', 'libur' => true],
        '08-17' => ['nama' => 'HUT Kemerdekaan RI', 'libur' => true],
        '12-25' => ['nama' => 'Hari Raya Natal', 'libur' => true],

        // Hari peringatan — bukan hari libur.
        '02-09' => ['nama' => 'Hari Pers Nasional', 'libur' => false],
        '04-21' => ['nama' => 'Hari Kartini', 'libur' => false],
        '05-02' => ['nama' => 'Hari Pendidikan Nasional', 'libur' => false],
        '05-20' => ['nama' => 'Hari Kebangkitan Nasional', 'libur' => false],
        '07-22' => ['nama' => 'Hari Kejaksaan', 'libur' => false],
        '08-14' => ['nama' => 'Hari Pramuka', 'libur' => false],
        '09-04' => ['nama' => 'Hari Pelanggan Nasional', 'libur' => false],
        '10-01' => ['nama' => 'Hari Kesaktian Pancasila', 'libur' => false],
        '10-28' => ['nama' => 'Hari Sumpah Pemuda', 'libur' => false],
        '11-10' => ['nama' => 'Hari Pahlawan', 'libur' => false],
        '12-22' => ['nama' => 'Hari Ibu', 'libur' => false],
    ];

    /**
     * Tanggal yang berpindah tiap tahun, menurut SKB. Kunci: tahun, lalu
     * 'yyyy-mm-dd'.
     *
     * KOSONG SAMPAI DIISI DARI SKB. Jangan menebak: lihat catatan di kepala
     * kelas ini.
     *
     * @var array<int, array<string, array{nama: string, libur: bool}>>
     */
    public const BERGERAK = [];

    /**
     * Semua penanda pada satu rentang tanggal.
     *
     * @return array<string, array{nama: string, libur: bool}> kunci 'yyyy-mm-dd'
     */
    public static function untukRentang(Carbon $awal, Carbon $akhir): array
    {
        $out = [];
        $hari = $awal->copy()->startOfDay();
        $batas = $akhir->copy()->endOfDay();

        while ($hari->lessThanOrEqualTo($batas)) {
            $tanggal = $hari->toDateString();

            if ($t = self::TETAP[$hari->format('m-d')] ?? null) {
                $out[$tanggal] = $t;
            }

            // Yang bergerak menimpa yang tetap bila jatuh di hari yang sama:
            // pada tanggal itu keduanya benar, tapi yang berpindah biasanya
            // hari libur, dan hari libur yang lebih penting diketahui.
            if ($b = self::BERGERAK[(int) $hari->year][$tanggal] ?? null) {
                $out[$tanggal] = $b;
            }

            $hari->addDay();
        }

        return $out;
    }

    /** Ada tanggal bergerak yang sudah diisi untuk tahun ini? */
    public static function bergerakTerisi(int $tahun): bool
    {
        return ! empty(self::BERGERAK[$tahun] ?? []);
    }
}
