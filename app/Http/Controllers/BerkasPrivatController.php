<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Order;
use App\Models\Spending;
use Illuminate\Support\Facades\Storage;

/**
 * Penyaji berkas SENSITIF dari disk privat.
 *
 * Sebelumnya CV pelamar, bukti pembayaran, dan lampiran pengeluaran disimpan
 * di disk 'public' — bisa diunduh siapa pun yang tahu URL-nya, selamanya,
 * tanpa login. Nama filenya memang acak, tapi sekali URL bocor (riwayat
 * browser, tautan disalin, header referrer) tak ada lagi yang menahannya.
 *
 * Sekarang berkasnya di disk privat dan hanya bisa diambil lewat route
 * ber-izin di bawah ini.
 */
class BerkasPrivatController extends Controller
{
    /** CV pelamar kerja. */
    public function cvPelamar(JobApplication $pelamar)
    {
        return $this->sajikan($pelamar->cv_path, 'cv-'.$pelamar->name.'.pdf');
    }

    /** Surat lamaran pelamar kerja. */
    public function suratPelamar(JobApplication $pelamar)
    {
        return $this->sajikan($pelamar->cover_letter_path, 'surat-lamaran-'.$pelamar->name.'.pdf');
    }

    /** Bukti pembayaran pesanan. */
    public function buktiPembayaran(Order $order)
    {
        return $this->sajikan($order->bukti_pembayaran, 'bukti-'.$order->order_number.'.jpg');
    }

    /** Lampiran pengeluaran (nota/faktur) — bisa lebih dari satu. */
    public function lampiranSpending(Spending $spending, int $index = 0)
    {
        $daftar = $spending->gambar_list;

        if (is_string($daftar)) {
            $daftar = json_decode($daftar, true) ?: [];
        }

        $daftar = is_array($daftar) ? array_values($daftar) : [];

        // Kolom lama 'gambar' dipakai bila daftar barunya kosong.
        $path = $daftar[$index] ?? ($index === 0 ? $spending->gambar : null);

        return $this->sajikan($path, 'lampiran-'.$spending->id_transaksi.'.jpg');
    }

    /**
     * Tampilkan berkas apa adanya (inline) agar bisa dipratinjau di admin.
     *
     * Berkas lama masih ada di disk 'public'; keduanya dicoba supaya data
     * yang belum sempat dipindah tetap terbuka bagi admin.
     */
    private function sajikan(?string $path, string $namaUnduh)
    {
        abort_if(! $path, 404);

        /*
         * Path dari database, tapi tetap diperiksa: kalau suatu saat ada jalur
         * yang bisa menyisipkan '../', Flysystem melempar PathTraversalDetected
         * yang tanpa penanganan muncul sebagai galat 500 beserta jejak kode.
         * Dijadikan 404 biasa supaya tidak membocorkan apa pun.
         */
        try {
            foreach (['local', 'public'] as $disk) {
                if (Storage::disk($disk)->exists($path)) {
                    return Storage::disk($disk)->response($path, $namaUnduh);
                }
            }
        } catch (\Throwable $e) {
            abort(404);
        }

        abort(404);
    }
}
