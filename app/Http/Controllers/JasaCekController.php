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
        $upload->loadMissing('order.items.product', 'order.uploads');

        abort_unless($upload->order && $upload->order->share_token === $token, 404);
        // Link kedaluwarsa 24 jam setelah kuota habis → hasil pun tak bisa diunduh.
        abort_if($upload->order->cekLinkKadaluarsa(), 410, 'Masa akses link pengecekan sudah berakhir.');
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

    /** Customer unduh hasil cek AI — token cocok & pengecekan selesai. */
    public function unduhHasilAiPublik(string $token, OrderUpload $upload)
    {
        $upload->loadMissing('order.items.product', 'order.uploads');

        abort_unless($upload->order && $upload->order->share_token === $token, 404);
        // Link kedaluwarsa 24 jam setelah kuota habis → hasil pun tak bisa diunduh.
        abort_if($upload->order->cekLinkKadaluarsa(), 410, 'Masa akses link pengecekan sudah berakhir.');
        abort_unless($upload->status === 'selesai' && $upload->hasil_ai_path, 404);
        abort_unless(Storage::disk('local')->exists($upload->hasil_ai_path), 404);

        return Storage::disk('local')->download($upload->hasil_ai_path, $upload->hasil_ai_nama ?: 'hasil-cek-ai.pdf');
    }

    /** Customer unduh dokumen hasil parafrase (DOCX). */
    public function unduhHasilDocxPublik(string $token, OrderUpload $upload)
    {
        $upload->loadMissing('order.items.product', 'order.uploads');

        abort_unless($upload->order && $upload->order->share_token === $token, 404);
        // Link kedaluwarsa 24 jam setelah kuota habis → hasil pun tak bisa diunduh.
        abort_if($upload->order->cekLinkKadaluarsa(), 410, 'Masa akses link pengecekan sudah berakhir.');
        abort_unless($upload->status === 'selesai' && $upload->hasil_docx_path, 404);
        abort_unless(Storage::disk('local')->exists($upload->hasil_docx_path), 404);

        return Storage::disk('local')->download($upload->hasil_docx_path, $upload->hasil_docx_nama ?: 'hasil-parafrase.docx');
    }

    /** Admin unduh hasil cek AI. */
    public function unduhHasilAiAdmin(OrderUpload $upload)
    {
        abort_unless($upload->hasil_ai_path && Storage::disk('local')->exists($upload->hasil_ai_path), 404);

        return Storage::disk('local')->download($upload->hasil_ai_path, $upload->hasil_ai_nama ?: 'hasil-cek-ai.pdf');
    }

    /** Admin unduh dokumen hasil parafrase (DOCX). */
    public function unduhHasilDocxAdmin(OrderUpload $upload)
    {
        abort_unless($upload->hasil_docx_path && Storage::disk('local')->exists($upload->hasil_docx_path), 404);

        return Storage::disk('local')->download($upload->hasil_docx_path, $upload->hasil_docx_nama ?: 'hasil-parafrase.docx');
    }

    /** Admin unduh PDF acuan halaman (parafrase: pendamping file kerja DOCX). */
    public function unduhPdfAdmin(OrderUpload $upload)
    {
        abort_unless($upload->pdf_path && Storage::disk('local')->exists($upload->pdf_path), 404);

        return Storage::disk('local')->download($upload->pdf_path, $upload->pdf_nama ?: 'acuan-halaman.pdf');
    }

    /** Admin unduh file HASIL (yang ia unggah). */
    public function unduhHasilAdmin(OrderUpload $upload)
    {
        abort_unless($upload->hasil_path && Storage::disk('local')->exists($upload->hasil_path), 404);

        return Storage::disk('local')->download($upload->hasil_path, $upload->hasil_nama ?: 'hasil-pengecekan.pdf');
    }
}
