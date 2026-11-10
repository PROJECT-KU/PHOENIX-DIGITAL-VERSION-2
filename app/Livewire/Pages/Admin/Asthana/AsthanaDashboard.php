<?php

namespace App\Livewire\Pages\Admin\Asthana;

use App\Models\Asthana\Berita;
use App\Models\Asthana\Portofolio;
use App\Models\JobApplication;
use App\Models\Lowongan;
use App\Models\Message;
use App\Models\User;
use App\Support\Asthana\ModeAsthana;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Dasbor mode Asthana — padanan Dashboard Orcha: apa yang menunggu
 * dikerjakan (pelamar baru, pesan belum dibaca, lowongan lewat batas), ringkasan
 * angka, dan lamaran/pesan terbaru. Setiap bagian hanya tampil bagi pemegang
 * izin layarnya.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Dashboard Asthana')]
class AsthanaDashboard extends Component
{
    public function render()
    {
        $u = auth()->user();
        $boleh = fn (string $izin) => $u->hasPermission($izin);

        return view('livewire.pages.admin.asthana.dashboard', [
            'perlu' => ModeAsthana::perlu($u),
            'boleh' => [
                'lowongan' => $boleh('view_lowongan'),
                'pelamar' => $boleh('view_pelamar'),
                'pesan' => $boleh('view_message'),
                'karyawan' => $boleh('view_karyawan'),
                'situs' => $boleh('kelola_situs_asthana'),
            ],
            'angka' => [
                'lowongan_terbuka' => $boleh('view_lowongan') ? Lowongan::terbuka()->count() : null,
                'pelamar_bulan_ini' => $boleh('view_pelamar')
                    ? JobApplication::where('created_at', '>=', now()->startOfMonth())->count() : null,
                'karyawan' => $boleh('view_karyawan') ? User::count() : null,
                'isi_situs' => $boleh('kelola_situs_asthana')
                    ? Portofolio::terbit()->count() + Berita::terbit()->count() : null,
            ],
            'pelamarTerbaru' => $boleh('view_pelamar')
                ? JobApplication::with('job')->latest()->limit(5)->get() : collect(),
            'pesanTerbaru' => $boleh('view_message')
                ? Message::latest()->limit(5)->get() : collect(),
        ]);
    }
}
