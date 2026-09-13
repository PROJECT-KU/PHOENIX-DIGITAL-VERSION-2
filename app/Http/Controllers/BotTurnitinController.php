<?php

namespace App\Http\Controllers;

use App\Models\OrderUpload;
use App\Support\BotTurnitin;
use App\Support\BotTurnitinDitolak;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * API untuk skrip bot Turnitin (public/bot/phoenix-turnitin.user.js).
 * Seluruh aturan tinggal di App\Support\BotTurnitin; di sini hanya HTTP-nya.
 */
class BotTurnitinController extends Controller
{
    /** Tanda hidup + laporan masalah di sisi submitin (belum login, kuota habis). */
    public function detak(Request $request): JsonResponse
    {
        $data = $request->validate([
            'masalah' => ['nullable', 'string', 'max:300'],
            'kuota_habis' => ['nullable', 'boolean'],
            'tugas' => ['nullable', 'string', 'max:40'],
        ]);

        BotTurnitin::catatDetak($data['masalah'] ?? null);

        if (! empty($data['kuota_habis'])) {
            BotTurnitin::tandaiKuotaHabis($data['masalah'] ?? 'Kuota paket Standard di submitin.id habis.');
        }

        if (! empty($data['tugas']) && $up = OrderUpload::find($data['tugas'])) {
            BotTurnitin::masihMenunggu($up);
        }

        return response()->json([
            'ok' => true,
            'dijeda' => BotTurnitin::dijeda(),
            'kuota_habis' => (bool) BotTurnitin::kuotaHabis(),
        ]);
    }

    public function tugas(): JsonResponse
    {
        BotTurnitin::catatDetak();

        $up = BotTurnitin::ambilTugas();

        return response()->json([
            'ok' => true,
            'dijeda' => BotTurnitin::dijeda(),
            'kuota_habis' => (bool) BotTurnitin::kuotaHabis(),
            'tugas' => $up ? BotTurnitin::dataTugas($up->load('order')) : null,
        ]);
    }

    /** Berkas customer — hanya untuk unggahan yang sedang dipegang bot. */
    public function berkas(OrderUpload $upload)
    {
        abort_unless($upload->bot_status === BotTurnitin::DIAMBIL, 409, 'Unggahan ini tidak sedang dipegang bot.');
        abort_unless($upload->path && Storage::disk('local')->exists($upload->path), 404);

        return Storage::disk('local')->download($upload->path, BotTurnitin::namaBerkas($upload->load('order')));
    }

    public function terkirim(Request $request, OrderUpload $upload): JsonResponse
    {
        $data = $request->validate(['kode' => ['required', 'string', 'max:40']]);

        return $this->jalankan(fn () => BotTurnitin::tandaiTerkirim($upload, $data['kode']));
    }

    public function hasil(Request $request, OrderUpload $upload): JsonResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:40'],
            'persen' => ['nullable', 'integer', 'min:0', 'max:100'],
            'berkas' => ['required', 'file', 'max:102400'],
        ]);

        return $this->jalankan(fn () => [
            'status' => BotTurnitin::simpanHasil(
                $upload,
                $data['kode'],
                $request->file('berkas'),
                isset($data['persen']) ? (int) $data['persen'] : null
            ),
        ]);
    }

    public function gagal(Request $request, OrderUpload $upload): JsonResponse
    {
        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:500'],
            'kuota_habis' => ['nullable', 'boolean'],
        ]);

        return $this->jalankan(fn () => BotTurnitin::gagal($upload, $data['pesan'], (bool) ($data['kuota_habis'] ?? false)));
    }

    private function jalankan(callable $aksi): JsonResponse
    {
        try {
            $hasil = $aksi();

            return response()->json(array_merge(['ok' => true], is_array($hasil) ? $hasil : []));
        } catch (BotTurnitinDitolak $e) {
            return response()->json(['ok' => false, 'pesan' => $e->getMessage()], 409);
        }
    }
}
