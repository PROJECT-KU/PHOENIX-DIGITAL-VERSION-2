<?php

namespace App\Livewire\Pages\Admin\Presensi;

use App\Models\Presensi;
use App\Models\User;
use App\Support\PeriodeGaji;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PresensiRekap extends Component
{
    use WithPagination;

    public string $search = '';

    // Filter periode SERAGAM dengan Cashflow: pilih bulan & tahun, dengan mode
    // 'kalender' (1 s/d akhir bulan) atau 'siklus20' (siklus gaji 21-20).
    public $bulan;

    public $tahun;

    public string $modePeriode = 'kalender';

    public string $filterTipe = '';

    /* ===== Presensi manual (input oleh admin) ===== */
    public bool $showManual = false;

    public string $manualUserId = '';

    public string $manualTanggal = '';

    public string $manualTipe = 'hadir_offline';

    public string $manualJamMasuk = '';

    public string $manualJamPulang = '';

    public string $manualCatatan = '';

    /* ===== Koreksi presensi "lupa pulang" (tutup entri yang masih Berjalan) ===== */
    public bool $showKoreksi = false;

    public string $koreksiId = '';

    public string $koreksiJamPulang = '';

    public string $koreksiCatatan = '';

    /** Info baris yang sedang dikoreksi (nama, tanggal, jam masuk) — tampil saja. */
    public ?array $koreksiInfo = null;

    public function mount(): void
    {
        // Default ke bulan & tahun berjalan (seragam dgn Cashflow).
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['search', 'bulan', 'tahun', 'modePeriode', 'filterTipe'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'filterTipe']);
        $this->bulan = '';
        $this->tahun = '';
        $this->modePeriode = 'kalender';
        $this->resetPage();
    }

    /**
     * Rentang tanggal [mulai, akhirInklusif] dari bulan/tahun/mode terpilih.
     * Bulan kosong = tanpa batas (semua data). Siklus mengikuti setelan gaji
     * (payroll_cutoff_day) — SATU sumber dengan fitur Gaji & Cashflow.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function periodeRange(): array
    {
        if (empty($this->bulan)) {
            return [null, null];
        }

        $bulan = (int) $this->bulan;
        $tahun = (int) ($this->tahun ?: now()->year);

        if ($this->modePeriode === 'siklus20') {
            return [PeriodeGaji::mulai($bulan, $tahun), PeriodeGaji::akhir($bulan, $tahun)];
        }

        $mulai = Carbon::create($tahun, $bulan, 1)->startOfMonth();

        return [$mulai, (clone $mulai)->endOfMonth()];
    }

    protected function daftarBulan(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    protected function daftarTahun(): array
    {
        $tahunSekarang = (int) now()->year;

        return range($tahunSekarang, $tahunSekarang - 5);
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

        // Arahkan filter ke periode entri yang baru dibuat agar langsung terlihat.
        $mt = Carbon::parse($this->manualTanggal);
        $this->bulan = $mt->month;
        $this->tahun = $mt->year;

        $this->showManual = false;
        $this->reset(['manualUserId', 'manualJamMasuk', 'manualJamPulang', 'manualCatatan']);
        $this->resetPage();
        $this->dispatch('presensi-manualSaved');
    }

    /**
     * Buka modal koreksi untuk presensi yang MASIH BERJALAN (lupa klik pulang).
     */
    public function bukaKoreksi($id): void
    {
        if (! $this->bolehManual()) {
            $this->dispatch('presensi-manualError', message: 'Anda tidak punya izin mengoreksi presensi.');

            return;
        }

        $p = Presensi::visibleTo()->with('user')->find($id);
        if (! $p || $p->waktu_pulang !== null) {
            $this->dispatch('presensi-manualError', message: 'Presensi tidak ditemukan atau sudah selesai.');

            return;
        }

        $this->koreksiId = (string) $p->id;
        $this->koreksiJamPulang = now()->format('H:i');
        $this->koreksiCatatan = '';
        $this->koreksiInfo = [
            'nama' => $p->user->name ?? '—',
            'tanggal' => $p->waktu_masuk->translatedFormat('d M Y'),
            'jam_masuk' => $p->waktu_masuk->format('H:i'),
        ];
        $this->showKoreksi = true;
    }

    public function tutupKoreksi(): void
    {
        $this->showKoreksi = false;
        $this->reset(['koreksiId', 'koreksiJamPulang', 'koreksiCatatan', 'koreksiInfo']);
    }

    /**
     * Isi jam pulang pada record yang menggantung -> durasi dihitung ulang,
     * status jadi selesai, dan MASUK KEMBALI ke perhitungan gaji. Bukan record
     * baru (tidak duplikat). Jejak audit ditambahkan ke catatan.
     */
    public function simpanKoreksi(): void
    {
        if (! $this->bolehManual()) {
            $this->dispatch('presensi-manualError', message: 'Anda tidak punya izin mengoreksi presensi.');

            return;
        }

        $this->validate([
            'koreksiJamPulang' => ['required', 'date_format:H:i'],
            'koreksiCatatan' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'koreksiJamPulang.required' => 'Jam pulang wajib diisi.',
            'koreksiJamPulang.date_format' => 'Format jam pulang tidak valid.',
            'koreksiCatatan.required' => 'Alasan koreksi wajib diisi.',
            'koreksiCatatan.min' => 'Alasan minimal :min karakter.',
        ]);

        $p = Presensi::visibleTo()->find($this->koreksiId);
        if (! $p || $p->waktu_pulang !== null) {
            $this->dispatch('presensi-manualError', message: 'Presensi tidak ditemukan atau sudah selesai.');

            return;
        }

        $masuk = $p->waktu_masuk;
        // Tanggal pulang = tanggal masuk (kasus lupa pulang di hari yang sama).
        $pulang = Carbon::parse($masuk->toDateString().' '.$this->koreksiJamPulang);

        if ($pulang->lessThanOrEqualTo($masuk)) {
            $this->addError('koreksiJamPulang', 'Jam pulang harus setelah jam masuk ('.$masuk->format('H:i').').');

            return;
        }

        $jejak = '[Koreksi lupa pulang oleh '.(auth()->user()->name ?? 'admin').' pada '.now()->format('d M Y H:i').'] '.trim($this->koreksiCatatan);

        $p->update([
            'waktu_pulang' => $pulang,
            'durasi_menit' => $masuk->diffInMinutes($pulang),
            'status' => 'selesai',
            'catatan' => trim(($p->catatan ? $p->catatan."\n" : '').$jejak),
        ]);

        $this->tutupKoreksi();
        $this->dispatch('presensi-koreksiSaved');
    }

    protected function baseQuery()
    {
        [$mulai, $akhir] = $this->periodeRange();

        return Presensi::query()
            ->visibleTo()
            ->with(['user', 'dibuatOleh'])
            ->when($mulai, fn ($q) => $q->whereDate('tanggal', '>=', $mulai->toDateString()))
            ->when($akhir, fn ($q) => $q->whereDate('tanggal', '<=', $akhir->toDateString()))
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

        [$periodeMulai, $periodeAkhir] = $this->periodeRange();

        return view('livewire.pages.admin.presensi.presensi-rekap', [
            'presensis' => $presensis,
            'stats' => $stats,
            'karyawanList' => $karyawanList,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
            'periodeMulai' => $periodeMulai,
            'periodeAkhir' => $periodeAkhir,
            'cutoffDay' => PeriodeGaji::cutoffDay(),
        ]);
    }
}
