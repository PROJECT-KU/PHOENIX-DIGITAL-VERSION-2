<?php

namespace App\Support;

use App\Mail\UndanganKegiatanMail;
use App\Models\Kegiatan;
use App\Models\User;

/**
 * Mengabari peserta sebuah kegiatan lewat surel.
 *
 * Kapan surel dikirim adalah keputusan yang lebih penting daripada isinya.
 * Mengirim setiap kali tombol Simpan ditekan akan melatih orang mengabaikan
 * kabar dari lemon, dan begitu itu terjadi, undangan yang benar-benar penting
 * pun ikut tidak dibaca. Karena itu:
 *
 *  - peserta BARU dapat undangan;
 *  - peserta LAMA hanya dikabari bila yang berubah menyangkut kehadirannya —
 *    judul, jenis, waktu, atau lokasi. Membetulkan salah ketik di catatan
 *    tidak mengirimi siapa pun;
 *  - peserta yang DIKELUARKAN diberi tahu, karena diam-diam menghilangkan
 *    kegiatan dari dasbornya membuat orang datang ke rapat yang bukan lagi
 *    urusannya — atau justru tidak datang tanpa tahu sebabnya;
 *  - kegiatan yang DIHAPUS mengabari semua pesertanya.
 *
 * Yang menekan tombol tidak ikut dikirimi: ia baru saja melakukannya dan sudah
 * melihat konfirmasinya di layar.
 *
 * Pengirimannya sinkron, sama seperti KabarJedaModul — antrean di server ini
 * tidak bisa diandalkan karena proc_open dimatikan hosting.
 */
class KabarKegiatan
{
    /** Rincian yang bila berubah, wajib dikabarkan ke peserta lama. */
    public const RINCIAN_PENTING = ['judul', 'jenis', 'mulai', 'selesai', 'seharian', 'lokasi'];

    /** Sedang memakai alamat uji coba, bukan surel peserta sungguhan? */
    public static function modeUji(): bool
    {
        return trim((string) config('kegiatan.email_uji')) !== '';
    }

    /**
     * Alamat surel dari sekumpulan id pengguna.
     *
     * @param  iterable<int>  $idPengguna
     * @return array<int, string>
     */
    public static function alamat(iterable $idPengguna, ?User $pelaku = null): array
    {
        $id = array_values(array_unique(array_filter((array) $idPengguna)));

        if (empty($id)) {
            return [];
        }

        $uji = trim((string) config('kegiatan.email_uji'));

        if ($uji !== '') {
            return [$uji];
        }

        return User::query()
            ->whereIn('id', $id)
            ->where('status', 'active')
            ->whereNotNull('email')
            ->when($pelaku, fn ($q) => $q->whereKeyNot($pelaku->getKey()))
            ->pluck('email')
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Kirim satu rupa kabar ke sekumpulan peserta.
     *
     * Kegagalan kirim tidak boleh membatalkan penyimpanan kegiatannya: jadwal
     * yang tersimpan tanpa surel jauh lebih baik daripada surel terkirim untuk
     * jadwal yang gagal tersimpan. KirimMassal sudah menelan galatnya sendiri.
     *
     * @param  iterable<int>  $idPeserta
     * @return int jumlah alamat yang dikirimi
     */
    public static function kirim(
        Kegiatan $kegiatan,
        iterable $idPeserta,
        string $rupa,
        ?User $pelaku = null,
    ): int {
        $penerima = self::alamat($idPeserta, $pelaku);

        if (empty($penerima)) {
            return 0;
        }

        $oleh = $pelaku?->name ?: ($pelaku?->email ?: 'Admin');

        // Lewat BCC: tanpa itu setiap peserta melihat surel semua rekannya.
        return KirimMassal::bcc(
            $penerima,
            fn () => new UndanganKegiatanMail($kegiatan, $rupa, $oleh)
        );
    }

    /**
     * Apakah perubahan ini menyangkut kehadiran peserta?
     *
     * @param  array<string, mixed>  $sebelum  nilai rincian sebelum disimpan
     */
    public static function perluDikabarkan(Kegiatan $kegiatan, array $sebelum): bool
    {
        foreach (self::RINCIAN_PENTING as $kolom) {
            $lama = $sebelum[$kolom] ?? null;
            $baru = $kegiatan->getAttribute($kolom);

            // Waktu dibandingkan sebagai teks: dua Carbon yang sama tetap dua
            // objek berbeda, dan !== akan selalu bilang "berubah".
            $lama = $lama instanceof \DateTimeInterface ? $lama->format('Y-m-d H:i:s') : $lama;
            $baru = $baru instanceof \DateTimeInterface ? $baru->format('Y-m-d H:i:s') : $baru;

            if ((string) $lama !== (string) $baru) {
                return true;
            }
        }

        return false;
    }
}
