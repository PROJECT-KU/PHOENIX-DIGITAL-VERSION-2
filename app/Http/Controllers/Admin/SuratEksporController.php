<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SuratExport;
use App\Http\Controllers\Controller;
use App\Models\Surat\Surat;
use App\Models\Surat\SuratPemusnahan;
use App\Support\Surat\SaringanSurat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Tiga berkas, tiga pembaca.
 *
 * Excel dipakai di kantor: semua kolom, untuk disaring dan dihitung sendiri.
 * PDF buku agenda dipakai saat pemeriksaan: ringkas, muat di A4 melintang,
 * dan punya ruang tanda tangan.
 * Lembar disposisi dicetak lalu DITEMPEL di surat fisiknya — surat yang
 * berpindah meja harus membawa perintahnya ikut berpindah.
 *
 * Ketiganya memakai SaringanSurat yang sama dengan layarnya, termasuk
 * penyaring kerahasiaan: izin yang dijaga di layar tetapi bocor di berkas
 * unduhan sama saja dengan tidak dijaga.
 */
class SuratEksporController extends Controller
{
    public function excel(Request $request)
    {
        $saringan = SaringanSurat::dariPermintaan($request);

        return Excel::download(
            new SuratExport($request->user(), $saringan),
            'buku-agenda-surat-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    public function pdf(Request $request)
    {
        $saringan = SaringanSurat::dariPermintaan($request);

        $surat = SaringanSurat::urut(
            SaringanSurat::kueri($request->user(), $saringan)->with('klasifikasi'),
            $saringan['urut'] ?? 'terbaru',
        )->get();

        $pdf = Pdf::loadView('livewire.pages.admin.surat.cetak.buku-agenda-pdf', [
            'surat' => $surat,
            'ringkasan' => SaringanSurat::ringkasan($saringan),
            'pencetak' => $request->user()?->name,
        ])->setPaper('a4', 'landscape');

        /*
         | Dilihat dulu, bukan langsung diunduh.
         |
         | Memeriksa hasilnya berarti unduh → buka → tutup → ulang, berkali
         | kali dalam satu sesi penyaringan. Berkasnya sama persis; yang
         | berbeda cuma apakah peramban menampilkannya atau menyimpannya.
         */
        return $pdf->stream('buku-agenda-surat-'.now()->format('Ymd-His').'.pdf');
    }

    public function lembarDisposisi(Request $request, Surat $surat)
    {
        abort_unless($surat->bolehDilihat($request->user()), 403,
            'Surat ini bersifat rahasia. Mintalah izin "Lihat Surat Rahasia" kepada admin.');

        $surat->load(['klasifikasi', 'penanggungJawab']);

        $pdf = Pdf::loadView('livewire.pages.admin.surat.cetak.lembar-disposisi-pdf', [
            'surat' => $surat,
            'disposisi' => $surat->disposisi()->with('penerima')->orderBy('id')->get(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('lembar-disposisi-'.$surat->nomor_agenda.'.pdf');
    }

    /**
     * Berita acara pemusnahan.
     *
     * Dibuka oleh pemegang 'view_surat' saja — ia berisi daftar perihal
     * surat, dan perihal surat rahasia pun ikut di dalamnya kalau memang
     * dimusnahkan bersama. Karena itu surat rahasia disaring keluar dari
     * daftarnya untuk yang tidak berizin, sementara jumlah totalnya tetap
     * ditulis apa adanya: berita acara yang jumlahnya berubah-ubah menurut
     * siapa yang mencetaknya bukan lagi dokumen.
     */
    public function beritaAcara(Request $request, SuratPemusnahan $pemusnahan)
    {
        $surat = $pemusnahan->surat()
            ->terlihatOleh($request->user())
            ->with('klasifikasi')
            ->orderBy('nomor_agenda')
            ->get();

        abort_if(
            $surat->count() !== $pemusnahan->surat()->count(),
            403,
            'Berita acara ini memuat surat rahasia. Mintalah izin "Lihat Surat Rahasia" kepada admin.',
        );

        $pdf = Pdf::loadView('livewire.pages.admin.surat.cetak.berita-acara-pdf', [
            'ba' => $pemusnahan->load('pembuat'),
            'surat' => $surat,
            'terbilang' => self::terbilang($surat->count()),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('berita-acara-'.$pemusnahan->nomor.'.pdf');
    }

    /**
     * Angka jadi kata, sampai 999.
     *
     * Berita acara resmi menulis jumlah dua kali — angka dan huruf — supaya
     * angkanya tidak bisa diubah sepihak setelah ditandatangani.
     */
    private static function terbilang(int $n): string
    {
        $satuan = ['nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($n < 12) {
            return $satuan[$n];
        }

        if ($n < 20) {
            return $satuan[$n - 10].' belas';
        }

        if ($n < 100) {
            $sisa = $n % 10;

            return $satuan[intdiv($n, 10)].' puluh'.($sisa ? ' '.$satuan[$sisa] : '');
        }

        if ($n < 1000) {
            $ratus = intdiv($n, 100);
            $sisa = $n % 100;

            return ($ratus === 1 ? 'seratus' : $satuan[$ratus].' ratus').($sisa ? ' '.self::terbilang($sisa) : '');
        }

        return (string) $n;
    }
}
