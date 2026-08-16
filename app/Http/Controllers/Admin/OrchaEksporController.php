<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Exports\OrchaPendaftaranExport;
use App\Http\Controllers\Controller;
use App\Services\OrchaClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Dua berkas untuk dua pembaca yang berbeda.
 *
 * Excel dipakai di kantor: semua kolom, termasuk riwayat kesehatan lengkap,
 * untuk disaring dan dihitung sendiri.
 *
 * PDF dipakai tour leader di lapangan, sering di layar ponsel sambil berdiri
 * di titik jemput. Isinya hanya yang dipakai saat itu: siapa dijemput di mana,
 * siapa yang perlu diperhatikan, dan nomor siapa yang ditelepon kalau terjadi
 * sesuatu. Rincian medis yang panjang justru mengubur ketiganya.
 */
class OrchaEksporController extends Controller
{
    public function __construct(private OrchaClient $orcha) {}

    public function excel(Request $request, int $pendaftaran)
    {
        [$data, $riwayat] = $this->ambil($request, $pendaftaran);

        return Excel::download(
            new OrchaPendaftaranExport($data, $riwayat),
            $this->namaBerkas('data-lengkap', $data)
        );
    }

    public function pdf(Request $request, int $pendaftaran)
    {
        [$data, $riwayat] = $this->ambil($request, $pendaftaran);

        return Pdf::loadView('exports.orcha-manifest-pdf', [
            'pendaftaran' => $data,
            'riwayat' => $riwayat,
        ])->setPaper('a4')->download($this->namaBerkas('manifes-tour-leader', $data));
    }

    /**
     * Kwitansi pendaftaran — jaring pengaman saat surat tidak sampai.
     *
     * Berkasnya dibuat di Orcha, sama persis dengan yang dikirim ke pelanggan,
     * lalu diteruskan apa adanya. Kalau lemon menggambarnya sendiri, cepat atau
     * lambat yang dipegang admin berbeda isi dengan yang dipegang pelanggan.
     *
     * Tidak menuntut izin data kesehatan: isinya biaya dan peserta, bukan data
     * medis — dan justru inilah yang perlu cepat dikirim ulang lewat WhatsApp
     * saat pelanggan mengeluh suratnya tidak masuk.
     */
    public function kwitansi(int $pendaftaran)
    {
        try {
            $berkas = $this->orcha->berkas("/pendaftaran/{$pendaftaran}/kwitansi");
        } catch (OrchaTidakTerjangkau $e) {
            abort(503, $e->getMessage());
        }

        return response($berkas['isi'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$berkas['nama'].'"',
        ]);
    }

    /**
     * Mengambil data pendaftaran beserta riwayat kesehatannya.
     *
     * Riwayat kesehatan diminta lewat jalurnya sendiri, jadi pengunduhannya
     * ikut tercatat di Orcha beserta akun yang mengunduh — sama seperti saat
     * dibuka di layar.
     *
     * @return array{0: array, 1: array}
     */
    private function ambil(Request $request, int $pendaftaran): array
    {
        // Penjagaan sebenarnya di sini, bukan pada tombol yang disembunyikan.
        abort_unless($request->user()?->hasPermission('view_orcha_kesehatan'), 403);

        try {
            $data = $this->orcha->ambil("/pendaftaran/{$pendaftaran}")['data'] ?? [];
            $riwayat = $this->orcha->ambil("/pendaftaran/{$pendaftaran}/riwayat-kesehatan")['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            abort(503, $e->getMessage());
        }

        abort_if($data === [], 404, 'Data pendaftaran tidak ditemukan di Orcha.');

        return [$data, $riwayat];
    }

    private function namaBerkas(string $awalan, array $data): string
    {
        $kode = $data['kode'] ?? 'tanpa-kode';

        return str($awalan.'-'.$kode)->slug()->upper()->toString()
            .($awalan === 'data-lengkap' ? '.xlsx' : '.pdf');
    }
}
