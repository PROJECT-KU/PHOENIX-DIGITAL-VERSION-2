<?php

namespace App\Support\Surat;

use App\Models\Surat\Surat;
use App\Models\Surat\SuratKlasifikasi;
use Illuminate\Support\Carbon;

/**
 * Nomor surat keluar: 016/03.D/ACM/VI/2026
 *
 * Lima bagian, dan tiap bagian punya aturan sendiri:
 *
 *   016   nomor urut SELURUH surat keluar tahun ini. Naik satu setiap kali
 *         ada surat keluar, klasifikasinya apa pun.
 *   03.D  nomor urut surat keluar berklasifikasi D SAJA, lalu kode
 *         klasifikasinya. Surat keluar berklasifikasi lain tidak
 *         menggesernya — kalau D terakhir 03, surat D berikutnya 04 meski di
 *         antaranya sudah terbit lima surat B.
 *   ACM   tetap, tidak pernah berubah.
 *   VI    bulan TERBITNYA surat dalam angka Romawi — diambil dari tanggal
 *         surat, bukan dari tanggal pencatatan. Surat Juni yang baru dicatat
 *         bulan Agustus tetap bernomor VI.
 *   2026  tahunnya, juga dari tanggal surat.
 *
 * SETENGAH OTOMATIS
 *
 * Kelas ini hanya MENGUSULKAN. Nomor yang tersimpan selalu yang tertulis di
 * borang, karena ada surat yang nomornya sudah terlanjur dikeluarkan di
 * kertas dan arsip harus mengikuti kertasnya, bukan sebaliknya. Supaya
 * usulan berikutnya tetap benar, hitungannya dibaca kembali dari nomor yang
 * tersimpan lewat urai() — nomor yang diketik manual pun menyambung rantai.
 */
class NomorSuratKeluar
{
    /** Singkatan instansi; bagian yang sengaja tidak pernah berubah. */
    public const INSTANSI = 'ACM';

    public const ROMAWI = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    /**
     * Usulkan nomor berikutnya.
     *
     * @param  int|string|null  $klasifikasiId
     * @return array{nomor: string, urut_utama: int, urut_klasifikasi: int|null, kode_klasifikasi: string|null}
     */
    public static function usulkan(?string $tanggal, $klasifikasiId = null, ?int $abaikanId = null): array
    {
        $saat = static::tanggal($tanggal);
        $tahun = (int) $saat->format('Y');
        $kode = static::kodeKlasifikasi($klasifikasiId);

        $utama = static::urutUtamaTerakhir($tahun, $abaikanId) + 1;
        $perKode = $kode === null ? null : static::urutKodeTerakhir($tahun, $kode, $abaikanId) + 1;

        /*
         | Kalau nomor usulan ternyata sudah dipakai, naikkan sampai bebas.
         |
         | Terjadi saat nomor diketik manual mendahului hitungannya, atau dua
         | admin membuka borang pada detik yang sama. Dua-duanya naik bersama:
         | surat yang memakai nomor itu sudah memakan jatah keduanya.
         */
        $nomor = static::rakit($utama, $perKode, $kode, $saat);

        for ($putaran = 0; $putaran < 50 && static::sudahDipakai($nomor, $abaikanId); $putaran++) {
            $utama++;
            $perKode = $perKode === null ? null : $perKode + 1;
            $nomor = static::rakit($utama, $perKode, $kode, $saat);
        }

        return [
            'nomor' => $nomor,
            'urut_utama' => $utama,
            'urut_klasifikasi' => $perKode,
            'kode_klasifikasi' => $kode,
        ];
    }

    /**
     * Rakit teks nomornya.
     *
     * Tanpa klasifikasi, bagian "03.D" DIHILANGKAN — bukan diisi tanda tanya
     * atau nol. Nomor surat beredar di luar kantor; lebih baik ia pendek
     * daripada memuat bagian yang jelas belum diisi.
     */
    public static function rakit(int $utama, ?int $perKode, ?string $kode, Carbon $saat): string
    {
        $bagian = [str_pad((string) $utama, 3, '0', STR_PAD_LEFT)];

        if ($kode !== null && $perKode !== null) {
            $bagian[] = str_pad((string) $perKode, 2, '0', STR_PAD_LEFT).'.'.$kode;
        }

        $bagian[] = static::INSTANSI;
        $bagian[] = static::ROMAWI[(int) $saat->format('n')] ?? '';
        $bagian[] = $saat->format('Y');

        return implode('/', $bagian);
    }

    /**
     * Baca kembali hitungan dari sebuah nomor surat.
     *
     * Dibuat longgar dengan sengaja: "16/3.D/ACM/VI/2026" tanpa angka nol di
     * depan tetap terbaca, karena orang mengetik begitu dan nomor yang tidak
     * terbaca akan menghilang dari hitungan tanpa pesan galat apa pun.
     *
     * @return array{urut_utama: int, urut_klasifikasi: int|null, kode_klasifikasi: string|null}|null
     */
    public static function urai(?string $nomor): ?array
    {
        $nomor = trim((string) $nomor);

        if ($nomor === '') {
            return null;
        }

        if (preg_match('#^\s*(\d{1,6})\s*/\s*(\d{1,6})\s*\.\s*([^/\s]{1,20})\s*/#', $nomor, $cocok) === 1) {
            return [
                'urut_utama' => (int) $cocok[1],
                'urut_klasifikasi' => (int) $cocok[2],
                'kode_klasifikasi' => $cocok[3],
            ];
        }

        if (preg_match('#^\s*(\d{1,6})\s*/#', $nomor, $cocok) === 1) {
            return [
                'urut_utama' => (int) $cocok[1],
                'urut_klasifikasi' => null,
                'kode_klasifikasi' => null,
            ];
        }

        return null;
    }

    /** Potongan-potongan nomor beserta artinya, untuk keterangan di borang. */
    public static function penjelasan(string $nomor): array
    {
        $bagian = explode('/', $nomor);
        $arti = count($bagian) === 5
            ? ['Urut surat keluar tahun ini', 'Urut klasifikasinya', 'Singkatan ACM', 'Bulan terbit', 'Tahun terbit']
            : ['Urut surat keluar tahun ini', 'Singkatan ACM', 'Bulan terbit', 'Tahun terbit'];

        $hasil = [];

        foreach ($bagian as $i => $isi) {
            $hasil[] = ['isi' => $isi, 'arti' => $arti[$i] ?? ''];
        }

        return $hasil;
    }

    /* ----------------------------- HITUNGAN ----------------------------- */

    /**
     * Angka tertinggi yang sudah dipakai, BUKAN jumlah suratnya.
     *
     * Surat yang dihapus tetap memegang nomornya — nomor itu sudah beredar di
     * luar. Karena itu baris terhapus ikut dilihat lewat withTrashed(), justru
     * supaya nomornya tidak diberikan dua kali.
     */
    private static function urutUtamaTerakhir(int $tahun, ?int $abaikanId): int
    {
        return (int) static::dasar($tahun, $abaikanId)->max('urut_utama');
    }

    private static function urutKodeTerakhir(int $tahun, string $kode, ?int $abaikanId): int
    {
        return (int) static::dasar($tahun, $abaikanId)
            ->where('kode_klasifikasi', $kode)
            ->max('urut_klasifikasi');
    }

    private static function dasar(int $tahun, ?int $abaikanId)
    {
        return Surat::withTrashed()
            ->where('arah', 'keluar')
            ->whereYear('tanggal_surat', $tahun)
            ->when($abaikanId, fn ($q) => $q->whereKeyNot($abaikanId));
    }

    public static function sudahDipakai(string $nomor, ?int $abaikanId = null): bool
    {
        return Surat::withTrashed()
            ->where('arah', 'keluar')
            ->where('nomor_surat', $nomor)
            ->when($abaikanId, fn ($q) => $q->whereKeyNot($abaikanId))
            ->exists();
    }

    /* ----------------------------- BANTU ----------------------------- */

    private static function tanggal(?string $tanggal): Carbon
    {
        $tanggal = trim((string) $tanggal);

        if ($tanggal === '') {
            return Carbon::now();
        }

        try {
            return Carbon::parse($tanggal);
        } catch (\Throwable) {
            // Tanggal setengah diketik ("2026-1") tidak boleh menjatuhkan
            // layar; borangnya memang masih dalam proses diisi.
            return Carbon::now();
        }
    }

    private static function kodeKlasifikasi($klasifikasiId): ?string
    {
        if (blank($klasifikasiId)) {
            return null;
        }

        $kode = SuratKlasifikasi::whereKey($klasifikasiId)->value('kode');

        return blank($kode) ? null : trim((string) $kode);
    }
}
