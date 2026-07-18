<?php

namespace App\Http\Controllers;

use App\Models\OrderUpload;
use Illuminate\Support\Facades\Storage;

/**
 * Unduh berkas pengecekan plagiasi. File disimpan di disk PRIVAT ('local'),
 * jadi tak bisa diakses lewat URL publik — hanya lewat route ber-token
 * (customer) atau route terproteksi auth (admin).
 */
class JasaCekController extends Controller
{
    /** Customer unduh HASIL — hanya bila token cocok & status selesai. */
    public function unduhHasilPublik(string $token, OrderUpload $upload)
    {
        $upload->loadMissing('order');

        abort_unless($upload->order && $upload->order->share_token === $token, 404);
        abort_unless($upload->status === 'selesai' && $upload->hasil_path, 404);
        abort_unless(Storage::disk('local')->exists($upload->hasil_path), 404);

        return Storage::disk('local')->download($upload->hasil_path, $upload->hasil_nama ?: 'hasil-pengecekan.pdf');
    }

    /** Admin unduh file MASUK dari customer. */
    public function unduhBerkasAdmin(OrderUpload $upload)
    {
        abort_unless($upload->path && Storage::disk('local')->exists($upload->path), 404);

        return Storage::disk('local')->download($upload->path, $upload->nama_asli ?: 'dokumen.pdf');
    }

    /** Admin unduh file HASIL (yang ia unggah). */
    public function unduhHasilAdmin(OrderUpload $upload)
    {
        abort_unless($upload->hasil_path && Storage::disk('local')->exists($upload->hasil_path), 404);

        return Storage::disk('local')->download($upload->hasil_path, $upload->hasil_nama ?: 'hasil-pengecekan.pdf');
    }
}
