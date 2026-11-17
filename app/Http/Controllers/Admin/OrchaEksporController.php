<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Exports\OrchaPendaftaranExport;
use App\Exports\OrchaPendaftaranRekapExport;
use App\Exports\OrchaTemplatPesertaExport;
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

    /**
     * Medan saringan yang diteruskan ke Orcha — SATU daftar untuk semua ekspor.
     *
     * Dulu tiap ekspor menyusun daftarnya sendiri, dan manifes rombongan
     * tertinggal: ia membaca cari, status, dan paket_id saja. Sejak satu paket
     * boleh dijual untuk beberapa tanggal, layar menyaring per ROMBONGAN —
     * saringan yang tidak dibaca di sini. Tour leader yang menyaring "4–6 Des"
     * lalu menekan Unduh mendapat ketiga rombongan sekaligus, tanpa satu pun
     * tanda bahwa saringannya tidak ikut: lembarnya terbit, isinya banyak, dan
     * kelebihannya baru ketahuan di parkiran.
     *
     * Medan baru ditambahkan DI SINI, sekali. Lihat
     * OrchaPendaftaranList::saringanTampil() di sisi yang mengirimnya.
     *
     * @return array<string, mixed>
     */
    private function saringanDari(Request $request): array
    {
        return array_filter([
            'cari' => $request->string('cari')->toString(),
            'status' => $request->string('status')->toString(),
            'paket_id' => $request->string('paket_id')->toString(),
            'keberangkatan_id' => $request->string('keberangkatan_id')->toString(),
            // Dikirim hanya bila menyala: mengirim 0 membuat Orcha membaca
            // parameternya ada, dan daftar biasa ikut tersaring.
            'perlu_ditagih' => $request->boolean('perlu_ditagih') ? 1 : '',
            'urut' => $request->string('urut')->toString(),
        ], fn ($nilai) => $nilai !== '' && $nilai !== null);
    }

    /**
     * Rekap daftar pendaftaran, mengikuti saringan yang sedang tampil.
     *
     * Berbeda pembaca dari dua berkas di atas. Yang ini untuk REKAP KANTOR —
     * satu baris per pendaftaran, berisi angka uang dan keadaannya. Sebelumnya
     * satu-satunya ekspor berbentuk daftar adalah manifes tour leader, yang
     * isinya nama peserta dan titik jemput; rekap bulanan karenanya disalin
     * tangan dari layar, satu baris demi satu baris.
     *
     * TIDAK memuat data kesehatan, jadi tidak menuntut izin itu: isinya nama
     * pemesan, nomor, paket, tanggal, dan uang.
     */
    public function pendaftaranRekap(Request $request)
    {
        $saringan = array_merge($this->saringanDari($request), ['per_halaman' => 100]);

        try {
            /*
             | Halamannya dirangkai sampai habis, dengan batas atas.
             |
             | Rekap yang berhenti di 100 baris pertama tanpa memberi tahu
             | adalah rekap yang angkanya salah — dan salahnya tidak kelihatan,
             | karena lembarnya terbit penuh. Batas 20 halaman (2.000 baris)
             | menjaga satu klik tidak berubah jadi dua puluh ribu panggilan;
             | kalau sampai mentok, jumlahnya disebut di lembarnya.
             */
            $baris = [];
            $halaman = 1;
            $total = null;

            do {
                $hasil = $this->orcha->ambil('/pendaftaran', $saringan + ['page' => $halaman]);
                $baris = array_merge($baris, $hasil['data'] ?? []);
                $total ??= (int) ($hasil['meta']['total'] ?? 0);
                $terakhir = (int) ($hasil['meta']['halaman_terakhir'] ?? 1);
                $halaman++;
            } while ($halaman <= $terakhir && $halaman <= 20);
        } catch (OrchaTidakTerjangkau $e) {
            abort(503, $e->getMessage());
        }

        abort_if($baris === [], 404, 'Tidak ada pendaftaran yang cocok dengan saringan ini.');

        return Excel::download(
            new OrchaPendaftaranRekapExport($baris, $total ?? count($baris), $saringan),
            'REKAP-PENDAFTARAN-'.now()->format('Ymd-Hi').'.xlsx'
        );
    }

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
    /**
     * Templat kosong untuk panitia mengisi daftar peserta.
     *
     * Tidak menuntut izin data kesehatan: isinya cuma nama kolom dan pilihan
     * titik jemput — tidak ada satu pun data peserta di dalamnya.
     */
    public function templatPeserta(Request $request, int $pendaftaran)
    {
        try {
            $data = $this->orcha->ambil("/pendaftaran/{$pendaftaran}")['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            abort(503, $e->getMessage());
        }

        abort_if($data === [], 404, 'Pendaftaran tidak ditemukan.');

        $kode = $data['kode'] ?? 'PESERTA';

        return Excel::download(
            new OrchaTemplatPesertaExport(
                $kode,
                (int) ($data['jumlah_peserta'] ?? 1),
                array_values(array_filter((array) data_get($data, 'paket.titik_jemput', []))),
            ),
            'TEMPLAT-PESERTA-'.$kode.'.xlsx',
        );
    }

    public function manifesDaftar(Request $request)
    {
        abort_unless($request->user()?->hasPermission('view_orcha_kesehatan'), 403);

        $saringan = array_merge($this->saringanDari($request), ['per_halaman' => 100]);

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
    /**
     * Surat pernyataan penggantian peserta.
     *
     * SATU surat untuk seluruh pendaftaran, memuat semua penggantiannya.
     *
     * Berbentuk PDF, sebentuk dengan kwitansi yang sudah dipegang pemesan.
     * Sempat DOCX supaya bisa disunting, tetapi surat yang bisa diubah
     * penerimanya bukan bukti yang baik atas apa yang disepakati — dan sejak
     * penggantian dicatat lewat tombol di admin, ejaannya memang sudah dikunci
     * sistem.
     */
    public function suratPenggantian(int $pendaftaran)
    {
        // Tanpa parameter: isinya dibaca Orcha dari riwayat pendaftaran itu
        // sendiri. Yang tercetak di surat bermaterai harus persis yang tercatat
        // sistem, bukan yang bisa disetir lewat URL.
        return $this->teruskanBerkas("/pendaftaran/{$pendaftaran}/surat-penggantian");
    }

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

    /**
     * PDF RAB: penawaran untuk pelanggan, atau internal berisi modal & margin.
     * Jenis lain dianggap penawaran — salah ketik tidak boleh jatuh ke berkas
     * yang memuat modal.
     */
    /**
     * Daftar vendor sebagai PDF, mengikuti saringan yang sedang dipakai.
     *
     * Berkasnya dirakit Orcha — logo, alamat kantor, dan sosial di kakinya
     * hidup di sana, dan menyalinnya ke sini berarti dua tempat yang harus
     * diubah tiap kali alamat kantor berganti.
     */
    public function vendor(Request $request)
    {
        return $this->teruskanBerkas('/vendor/pdf', $request->only(['cari', 'jenis', 'status', 'provinsi']));
    }

    /** Daftar master harga sebagai PDF, mengikuti saringan yang sedang dipakai. */
    public function masterHarga(Request $request)
    {
        return $this->teruskanBerkas('/master-harga/pdf', $request->only([
            'cari', 'kategori', 'provinsi', 'vendor', 'dipakai',
        ]));
    }

    public function rab(Request $request, int $rab)
    {
        $jenis = $request->query('jenis') === 'internal' ? 'internal' : 'penawaran';

        /*
         | Dilihat dulu, bukan langsung diunduh.
         |
         | Memeriksa hasilnya berarti unduh → buka → tutup → ulang, berkali
         | kali dalam satu penawaran. Berkasnya sama persis; yang berbeda
         | cuma apakah peramban menampilkannya atau menyimpannya.
         */
        return $this->teruskanBerkas(
            "/rab/{$rab}/pdf",
            ['jenis' => $jenis],
            inline: $request->boolean('pratinjau'),
        );
    }

    /** Berkas dibuat di Orcha lalu diteruskan apa adanya, tanpa digambar ulang. */
    /**
     * @param  array<string, mixed>  $parameter
     * @param  string  $jenis  jenis MIME berkasnya; semua berkas Orcha kini PDF
     */
    private function teruskanBerkas(
        string $jalur,
        array $parameter = [],
        string $jenis = 'application/pdf',
        bool $inline = false,
    ) {
        try {
            $berkas = $this->orcha->berkas($jalur, $parameter);
        } catch (OrchaTidakTerjangkau $e) {
            abort(503, $e->getMessage());
        }

        // "inline" menampilkan di peramban, "attachment" menyimpan. Nama
        // berkasnya tetap dibawa supaya tombol simpan di penampil PDF
        // memberi nama yang sama dengan unduhan biasa.
        return response($berkas['isi'], 200, [
            'Content-Type' => $jenis,
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.$berkas['nama'].'"',
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
