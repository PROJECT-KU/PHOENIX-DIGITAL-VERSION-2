<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Hitung tenggat helpdesk memakai JAM KERJA, bukan jam kalender.
 *
 * Toko buka 08.00-21.00. Kalau batas waktu dihitung lurus, tiket mendesak yang
 * masuk pukul 23.00 sudah "lewat batas" pukul 01.00 dini hari — menuduh
 * keterlambatan yang tidak mungkin dihindari siapa pun, lalu ikut terhitung di
 * kartu ringkasan dan pengingat harian.
 */
class JamKerja
{
    public static function mulai(): int
    {
        return (int) config('helpdesk.jam_kerja.mulai', 8);
    }

    public static function selesai(): int
    {
        return (int) config('helpdesk.jam_kerja.selesai', 21);
    }

    /** Menit kerja dalam sehari. */
    protected static function menitSehari(): int
    {
        return max(1, (self::selesai() - self::mulai()) * 60);
    }

    /** Geser ke awal jam kerja berikutnya bila waktunya di luar jam buka. */
    public static function berikutnya(Carbon $waktu): Carbon
    {
        $w = $waktu->copy();
        $buka = $w->copy()->setTime(self::mulai(), 0);
        $tutup = $w->copy()->setTime(self::selesai(), 0);

        if ($w->lt($buka)) {
            return $buka;
        }

        if ($w->gte($tutup)) {
            return $buka->addDay();
        }

        return $w;
    }

    /** Maju sekian JAM KERJA dari sebuah waktu. */
    public static function maju(Carbon $dari, int $jam): Carbon
    {
        $sisa = max(0, $jam) * 60;
        $kursor = self::berikutnya($dari);

        while ($sisa > 0) {
            $tutup = $kursor->copy()->setTime(self::selesai(), 0);
            $tersedia = $kursor->diffInMinutes($tutup);

            if ($sisa <= $tersedia) {
                return $kursor->addMinutes($sisa);
            }

            $sisa -= $tersedia;
            $kursor = $tutup->copy()->addDay()->setTime(self::mulai(), 0);
        }

        return $kursor;
    }

    /**
     * Mundur sekian JAM KERJA dari sebuah waktu.
     *
     * Dipakai saringan "lewat batas": tiket yang masuk SEBELUM titik ini sudah
     * melewati jatah waktunya.
     */
    public static function mundur(Carbon $dari, int $jam): Carbon
    {
        $sisa = max(0, $jam) * 60;
        $kursor = $dari->copy();

        // Di luar jam kerja, mulai hitung dari jam tutup hari yang berlaku.
        $buka = $kursor->copy()->setTime(self::mulai(), 0);
        $tutup = $kursor->copy()->setTime(self::selesai(), 0);

        if ($kursor->lt($buka)) {
            $kursor = $tutup->copy()->subDay();
        } elseif ($kursor->gt($tutup)) {
            $kursor = $tutup;
        }

        while ($sisa > 0) {
            $buka = $kursor->copy()->setTime(self::mulai(), 0);
            $tersedia = $buka->diffInMinutes($kursor);

            if ($sisa <= $tersedia) {
                return $kursor->subMinutes($sisa);
            }

            $sisa -= $tersedia;
            $kursor = $buka->copy()->subDay()->setTime(self::selesai(), 0);
        }

        return $kursor;
    }

    /** Berapa JAM KERJA antara dua waktu (dibulatkan ke bawah). */
    public static function selisihJam(Carbon $awal, Carbon $akhir): int
    {
        return intdiv(self::selisihMenit($awal, $akhir), 60);
    }

    /** Berapa MENIT KERJA antara dua waktu. */
    public static function selisihMenit(Carbon $awal, Carbon $akhir): int
    {
        if ($akhir->lte($awal)) {
            return 0;
        }

        $menit = 0;
        $kursor = self::berikutnya($awal);

        while ($kursor->lt($akhir)) {
            $tutup = $kursor->copy()->setTime(self::selesai(), 0);
            $batas = $akhir->lt($tutup) ? $akhir : $tutup;

            if ($batas->gt($kursor)) {
                $menit += $kursor->diffInMinutes($batas);
            }

            $kursor = $tutup->copy()->addDay()->setTime(self::mulai(), 0);
        }

        return $menit;
    }
}
