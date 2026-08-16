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
     * Manifes gabungan untuk satu keberangkatan.
     *
     * Manifes per pendaftaran berguna saat satu rombongan datang bersama, tapi
     * open trip justru dibentuk dari banyak pendaftaran terpisah yang berangkat
     * di hari yang sama. Tour leader tidak membawa dua belas lembar; ia membawa
     * satu, dengan seluruh peserta dikelompokkan per titik jemput.
     *
     * Yang diekspor mengikuti saringan yang sedang dilihat admin di layar,
     * supaya tidak ada dua pengertian tentang "daftar mana".
     */
    public function manifesDaftar(Request $request)
    {
        abort_unless($request->user()?->hasPermission('view_orcha_kesehatan'), 403);

        $saringan = array_filter([
            'cari' => $request->string('cari')->toString(),
            'status' => $request->string('status')->toString(),
            'per_halaman' => 100,
        ], fn ($nilai) => $nilai !== '' && $nilai !== null);

        try {
            $daftar = $this->orcha->ambil('/pendaftaran', $saringan)['data'] ?? [];

            // Riwayat kesehatan diminta per pendaftaran lewat jalurnya sendiri
            // supaya setiap pembukaannya tetap tercatat di Orcha. Dibatasi
            // supaya satu klik tidak berubah jadi ratusan panggilan.
            $kesehatan = [];

            foreach (array_slice($daftar, 0, 40) as $satu) {
                $kesehatan[$satu['id']] = $this->orcha
                    ->ambil("/pendaftaran/{$satu['id']}/riwayat-kesehatan")['data'] ?? [];
            }
        } catch (OrchaTidakTerjangkau $e) {
            abort(503, $e->getMessage());
        }

        abort_if($daftar === [], 404, 'Tidak ada pendaftaran yang cocok dengan saringan ini.');

        return Pdf::loadView('exports.orcha-manifest-daftar-pdf', [
            'daftar' => $daftar,
            'kesehatan' => $kesehatan,
            'saringan' => $saringan,
        ])->setPaper('a4')->download('MANIFES-ROMBONGAN-'.now()->format('Ymd-Hi').'.pdf');
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
        return $this->teruskanBerkas("/pendaftaran/{$pendaftaran}/kwitansi");
    }

    /**
     * Kwitansi sewa kendaraan — sebelum unit kembali berisi estimasi sewa,
     * sesudahnya menjadi nota akhir lengkap dengan rincian dendanya.
     */
    public function kwitansiSewa(int $penyewaan)
    {
        return $this->teruskanBerkas("/penyewaan/{$penyewaan}/kwitansi");
    }

    /** Berkas dibuat di Orcha lalu diteruskan apa adanya, tanpa digambar ulang. */
    private function teruskanBerkas(string $jalur)
    {
        try {
            $berkas = $this->orcha->berkas($jalur);
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
