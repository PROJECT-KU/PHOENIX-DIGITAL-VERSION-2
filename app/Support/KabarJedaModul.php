<?php

namespace App\Support;

use App\Mail\ModulAdminDijedaMail;
use App\Models\User;

/**
 * Mengabari karyawan saat satu modul admin ditutup atau dibuka kembali.
 *
 * Penerimanya punya DUA sumber yang saling meniadakan:
 *
 *  - Selama JEDA_EMAIL_UJI terisi, seluruh kabar dikirim ke alamat itu saja
 *    dan tidak ke seorang karyawan pun. Dipakai untuk uji coba supaya
 *    percobaan tidak mengganggu tim.
 *  - Bila dikosongkan (keadaan yang dituju di server), penerimanya diambil
 *    dari data karyawan: akun aktif yang punya data kepegawaian dan surel.
 *
 * Pengirimannya sinkron, sama seperti NikReminderMail — antrean di server ini
 * tidak bisa diandalkan karena proc_open dimatikan hosting.
 */
class KabarJedaModul
{
    /**
     * Alamat yang akan dikirimi kabar.
     *
     * Yang menekan tombol tidak ikut dikirimi: ia baru saja melakukannya dan
     * sudah melihat konfirmasinya di layar.
     *
     * @return array<int, string>
     */
    public static function penerima(?User $pelaku = null): array
    {
        $uji = trim((string) config('jeda.email_uji'));

        if ($uji !== '') {
            return [$uji];
        }

        return User::query()
            ->where('status', 'active')
            ->whereHas('detail')
            ->whereNotNull('email')
            ->when($pelaku, fn ($q) => $q->whereKeyNot($pelaku->getKey()))
            ->pluck('email')
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /** Sedang memakai alamat uji coba, bukan data karyawan? */
    public static function modeUji(): bool
    {
        return trim((string) config('jeda.email_uji')) !== '';
    }

    /**
     * Kirim kabarnya.
     *
     * Kegagalan kirim tidak boleh membatalkan penutupan modulnya: modulnya
     * ditutup karena ada yang perlu diperbaiki, dan surel yang gagal adalah
     * urusan yang jauh lebih ringan. Karena itu dibungkus dan hanya dicatat.
     *
     * @return int jumlah alamat yang dikirimi
     */
    public static function kirim(
        string $namaModul,
        bool $ditutup,
        string $pesan,
        ?User $pelaku = null,
        ?\Illuminate\Support\Carbon $mulai = null,
        ?\Illuminate\Support\Carbon $sampai = null,
    ): int {
        $penerima = self::penerima($pelaku);

        if (empty($penerima)) {
            return 0;
        }

        $oleh = $pelaku?->name ?: ($pelaku?->email ?: 'Admin');

        // Lewat BCC: tanpa itu setiap karyawan melihat surel semua rekannya.
        return KirimMassal::bcc(
            $penerima,
            fn () => new ModulAdminDijedaMail($namaModul, $ditutup, $pesan, $oleh, $mulai, $sampai)
        );
    }
}
