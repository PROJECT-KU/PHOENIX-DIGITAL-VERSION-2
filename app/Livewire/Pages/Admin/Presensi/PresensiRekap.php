<?php

namespace App\Livewire\Pages\Admin\Presensi;

use App\Models\Presensi;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PresensiRekap extends Component
{
    use WithPagination;

    public string $search = '';

    public string $tanggalDari = '';

    public string $tanggalSampai = '';

    public string $filterTipe = '';

    public function mount(): void
    {
        $this->tanggalDari = now()->startOfMonth()->toDateString();
        $this->tanggalSampai = now()->toDateString();
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['search', 'tanggalDari', 'tanggalSampai', 'filterTipe'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'filterTipe']);
        $this->tanggalDari = now()->startOfMonth()->toDateString();
        $this->tanggalSampai = now()->toDateString();
        $this->resetPage();
    }

    public function deletePresensi($id): void
    {
        if (! auth()->user()->hasPermission('view_all_presensi')) {
            $this->dispatch('presensi-deleteError', message: 'Anda tidak punya izin menghapus presensi.');

            return;
        }

        $presensi = Presensi::visibleTo()->find($id);
        if (! $presensi) {
            $this->dispatch('presensi-deleteError', message: 'Data presensi tidak ditemukan.');

            return;
        }

        $presensi->delete();
        $this->dispatch('presensi-deleted');
    }

    protected function baseQuery()
    {
        return Presensi::query()
            ->visibleTo()
            ->with('user')
            ->when($this->tanggalDari, fn ($q) => $q->whereDate('tanggal', '>=', $this->tanggalDari))
            ->when($this->tanggalSampai, fn ($q) => $q->whereDate('tanggal', '<=', $this->tanggalSampai))
            ->when($this->filterTipe, fn ($q) => $q->where('tipe', $this->filterTipe))
            ->when($this->search, function ($q) {
                $term = $this->search;
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"));
            });
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $presensis = $this->baseQuery()->latest('waktu_masuk')->paginate(15);

        $statBase = $this->baseQuery();
        $stats = [
            'total' => (clone $statBase)->count(),
            'hadir' => (clone $statBase)->whereIn('tipe', ['hadir_offline', 'hadir_online'])->count(),
            'lembur' => (clone $statBase)->where('tipe', 'lembur')->count(),
            'menit' => (int) (clone $statBase)->whereNotNull('durasi_menit')->sum('durasi_menit'),
        ];

        return view('livewire.pages.admin.presensi.presensi-rekap', [
            'presensis' => $presensis,
            'stats' => $stats,
        ]);
    }
}
