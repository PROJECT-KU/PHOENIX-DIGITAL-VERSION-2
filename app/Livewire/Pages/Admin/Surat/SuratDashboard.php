<?php

namespace App\Livewire\Pages\Admin\Surat;

use App\Models\Surat\Surat;
use App\Models\Surat\SuratDisposisi;
use App\Models\Surat\SuratKlasifikasi;
use App\Support\Surat\ModeSurat;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Dasbor mode Arsip Surat — padanan Dashboard Orcha & Dashboard Asthana.
 *
 * Susunannya menjawab tiga pertanyaan, dalam urutan yang ditanyakan staf
 * administrasi tiap pagi: apa yang menunggu saya, berapa banyak surat yang
 * masuk dan keluar, dan surat apa yang terakhir dicatat.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Dashboard Arsip Surat')]
class SuratDashboard extends Component
{
    public function render()
    {
        $u = auth()->user();
        $bolehKlasifikasi = (bool) $u?->hasPermission('kelola_klasifikasi_surat');

        $bulan = now()->startOfMonth();

        return view('livewire.pages.admin.surat.dashboard', [
            'perlu' => ModeSurat::perlu($u),
            'bolehCatat' => (bool) $u?->hasPermission('create_surat'),
            'bolehKlasifikasi' => $bolehKlasifikasi,
            'angka' => [
                'masuk_bulan' => Surat::where('arah', 'masuk')->where('tanggal_surat', '>=', $bulan)->count(),
                'keluar_bulan' => Surat::where('arah', 'keluar')->where('tanggal_surat', '>=', $bulan)->count(),
                'belum_selesai' => Surat::belumSelesai()->count(),
                'seluruh' => Surat::count(),
            ],
            'klasifikasiAktif' => $bolehKlasifikasi ? SuratKlasifikasi::aktif()->count() : null,
            'suratTerbaru' => Surat::with('klasifikasi')->latest('id')->limit(6)->get(),
            /*
             | Disposisi yang masih menggantung, yang paling mendesak di atas.
             |
             | Diurutkan menurut batas dengan yang TANPA batas di belakang:
             | di MySQL, ORDER BY pada kolom nullable menaruh NULL paling
             | depan, sehingga tugas tanpa tenggat akan menyingkirkan tugas
             | yang hari ini jatuh tempo dari daftar enam baris ini.
             */
            'disposisiMenggantung' => SuratDisposisi::with(['surat', 'penerima'])
                ->belumSelesai()
                ->orderByRaw('batas IS NULL')
                ->orderBy('batas')
                ->limit(6)
                ->get(),
        ]);
    }
}
