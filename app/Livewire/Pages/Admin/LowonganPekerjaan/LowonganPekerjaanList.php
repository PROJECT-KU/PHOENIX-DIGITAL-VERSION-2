<?php

namespace App\Livewire\Pages\Admin\LowonganPekerjaan;

use App\Models\Lowongan;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LowonganPekerjaanList extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    /** Unit usaha yang sedang disaring; kosong = semua. */
    public string $filterUnit = '';

    public string $tabAktif = 'semua';

    /**
     * Tab: label, ikon, warna.
     *
     * "Lewat batas" bukan sekadar cara lain melihat daftar — ia pekerjaan
     * yang menunggu. Lowongan yang masih bertanda aktif tetapi batas
     * lamarannya sudah lewat BERHENTI tampil di situs tanpa satu pun tanda
     * di layar ini, dan yang mengiranya masih menarik pelamar menunggu
     * lamaran yang tidak akan datang.
     */
    public const TAB = [
        'semua' => ['Semua lowongan', 'bi-collection', '#7c3aed'],
        'terbuka' => ['Masih dibuka', 'bi-door-open', '#16a34a'],
        'nonaktif' => ['Sudah ditutup', 'bi-slash-circle', '#64748b'],
        'lewat_batas' => ['Lewat batas lamaran', 'bi-hourglass-bottom', '#d97706'],
    ];

    /** Dua kelompok: cara melihat daftar, dan daftar pekerjaan. */
    public const GRUP_TAB = [
        ['Lihat daftar', 'Semua lowongan yang pernah dibuat', ['semua', 'terbuka', 'nonaktif']],
        ['Butuh diperiksa', 'Tidak tampil di situs', ['lewat_batas']],
    ];

    public function setTab(string $tab): void
    {
        $this->tabAktif = array_key_exists($tab, self::TAB) ? $tab : 'semua';
        $this->resetPage();
    }

    public function bersihkanSaringan(): void
    {
        $this->reset(['search', 'filterUnit', 'tabAktif']);
        $this->resetPage();
    }

    public function adaSaringan(): bool
    {
        return trim($this->search) !== '' || $this->filterUnit !== '' || $this->tabAktif !== 'semua';
    }

    /**
     * Saringan dasar: pencarian & unit, TANPA tab.
     *
     * Dipakai bersama oleh daftar dan penghitung tab. Dua kueri yang
     * ditulis terpisah akan berselisih begitu saringannya bertambah, dan
     * angka di tab lalu menjanjikan isi yang tidak ada saat ditekan.
     */
    private function dasar(): Builder
    {
        return Lowongan::query()
            ->when(trim($this->search) !== '', function (Builder $q) {
                $kata = '%'.trim($this->search).'%';
                $q->where(fn (Builder $w) => $w->where('title', 'like', $kata)
                    ->orWhere('lokasi', 'like', $kata)
                    ->orWhere('ringkasan', 'like', $kata));
            })
            ->when($this->filterUnit !== '', fn (Builder $q) => $q->where('unit', $this->filterUnit));
    }

    /** Batas tiap tab, satu tempat — dipakai daftar maupun penghitungnya. */
    private function batasiTab(Builder $q, string $tab): Builder
    {
        $hariIni = now()->toDateString();

        return match ($tab) {
            'terbuka' => $q->terbuka(),
            'nonaktif' => $q->where('is_active', '!=', 'active'),
            // Masih 'active', tetapi tanggalnya sudah lewat.
            'lewat_batas' => $q->where('is_active', 'active')
                ->whereNotNull('batas_lamaran')
                ->whereDate('batas_lamaran', '<', $hariIni),
            default => $q,
        };
    }

    public function jumlahTab(): array
    {
        $jumlah = [];

        foreach (array_keys(self::TAB) as $tab) {
            $jumlah[$tab] = $this->batasiTab($this->dasar(), $tab)->count();
        }

        return $jumlah;
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $dataLowongan = $this->batasiTab($this->dasar(), $this->tabAktif)
            // Berapa orang yang sudah melamar: pertanyaan pertama yang
            // dibawa orang saat membuka layar ini, dan dulu tidak terjawab
            // di sini sama sekali.
            ->withCount('applications')
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-list', [
            'dataLowongan' => $dataLowongan,
            'tabs' => self::TAB,
            'grupTab' => self::GRUP_TAB,
            'jumlahTab' => $this->jumlahTab(),
            'pilihanUnit' => Lowongan::UNIT,
        ]);
    }

    #[On('delete-lowongan-data')]
    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_lowongan')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus data lowongan.');

            return;
        }

        try {
            Lowongan::findOrFail($id)->delete();
            $this->dispatch('swal-success', message: 'Data lowongan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('swal-error', message: 'Gagal menghapus data lowongan.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterUnit()
    {
        $this->resetPage();
    }
}
