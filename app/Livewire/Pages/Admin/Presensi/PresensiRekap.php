<?php

namespace App\Livewire\Pages\Admin\Presensi;

use App\Models\Presensi;
use App\Models\User;
use Illuminate\Support\Carbon;
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

    /* ===== Presensi manual (input oleh admin) ===== */
    public bool $showManual = false;

    public string $manualUserId = '';

    public string $manualTanggal = '';

    public string $manualTipe = 'hadir_offline';

    public string $manualJamMasuk = '';

    public string $manualJamPulang = '';

    public string $manualCatatan = '';

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

    private function bolehManual(): bool
    {
        return auth()->user()?->hasPermission('create_presensi_manual') ?? false;
    }

    public function openManual(): void
    {
        if (! $this->bolehManual()) {
            $this->dispatch('presensi-manualError', message: 'Anda tidak punya izin membuat presensi manual.');

            return;
        }

        $this->reset(['manualUserId', 'manualJamMasuk', 'manualJamPulang', 'manualCatatan']);
        $this->resetErrorBag();
        $this->manualTanggal = now()->toDateString();
        $this->manualTipe = 'hadir_offline';
        $this->showManual = true;
    }

    public function closeManual(): void
    {
        $this->showManual = false;
        $this->resetErrorBag();
    }

    public function saveManual(): void
    {
        if (! $this->bolehManual()) {
            $this->dispatch('presensi-manualError', message: 'Anda tidak punya izin membuat presensi manual.');

            return;
        }

        $this->validate([
            'manualUserId' => ['required', 'exists:users,id'],
            'manualTanggal' => ['required', 'date'],
            'manualTipe' => ['required', 'in:hadir_offline,hadir_online,lembur'],
            'manualJamMasuk' => ['required', 'date_format:H:i'],
            'manualJamPulang' => ['nullable', 'date_format:H:i'],
            'manualCatatan' => ['required', 'string', 'min:3', 'max:500'],
        ], [], [
            'manualUserId' => 'karyawan',
            'manualTanggal' => 'tanggal',
            'manualTipe' => 'jenis',
            'manualJamMasuk' => 'jam masuk',
            'manualJamPulang' => 'jam pulang',
            'manualCatatan' => 'alasan',
        ]);

        $masuk = Carbon::parse($this->manualTanggal.' '.$this->manualJamMasuk);
        $pulang = $this->manualJamPulang
            ? Carbon::parse($this->manualTanggal.' '.$this->manualJamPulang)
            : null;

        if ($pulang && $pulang->lessThanOrEqualTo($masuk)) {
            $this->addError('manualJamPulang', 'Jam pulang harus setelah jam masuk.');

            return;
        }

        // Presensi manual: tanpa batas jarak & durasi, tapi wajib beri jejak audit.
        Presensi::create([
            'user_id' => $this->manualUserId,
            'tanggal' => $this->manualTanggal,
            'tipe' => $this->manualTipe,
            'waktu_masuk' => $masuk,
            'waktu_pulang' => $pulang,
            'durasi_menit' => $pulang ? $masuk->diffInMinutes($pulang) : null,
            'status' => $pulang ? 'selesai' : 'aktif',
            'catatan' => $this->manualCatatan,
            'is_manual' => true,
            'dibuat_oleh' => auth()->id(),
        ]);

        // Lebarkan filter agar entri yang baru dibuat langsung terlihat di tabel.
        if ($this->manualTanggal < $this->tanggalDari) {
            $this->tanggalDari = $this->manualTanggal;
        }
        if ($this->manualTanggal > $this->tanggalSampai) {
            $this->tanggalSampai = $this->manualTanggal;
        }

        $this->showManual = false;
        $this->reset(['manualUserId', 'manualJamMasuk', 'manualJamPulang', 'manualCatatan']);
        $this->resetPage();
        $this->dispatch('presensi-manualSaved');
    }

    protected function baseQuery()
    {
        return Presensi::query()
            ->visibleTo()
            ->with(['user', 'dibuatOleh'])
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

        $karyawanList = $this->bolehManual()
            ? User::whereHas('role', fn ($r) => $r->where('name', '!=', 'customer'))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('livewire.pages.admin.presensi.presensi-rekap', [
            'presensis' => $presensis,
            'stats' => $stats,
            'karyawanList' => $karyawanList,
        ]);
    }
}
