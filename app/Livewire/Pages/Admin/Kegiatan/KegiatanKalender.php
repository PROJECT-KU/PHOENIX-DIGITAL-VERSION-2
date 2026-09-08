<?php

namespace App\Livewire\Pages\Admin\Kegiatan;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Kalender kegiatan satu bulan penuh.
 *
 * Sengaja satu komponen, bukan trio List/Create/Edit: pada kalender, menambah
 * kegiatan hampir selalu berawal dari "klik tanggal ini". Melempar orang ke
 * halaman lain memutus konteks tanggal yang baru saja ia pilih, jadi formnya
 * tinggal di modal pada halaman yang sama.
 */
class KegiatanKalender extends Component
{
    /** Bulan & tahun yang sedang ditampilkan. */
    public int $bulan;

    public int $tahun;

    /** Saring menurut jenis kegiatan; kosong = semua. */
    public string $saringJenis = '';

    /** Tampilkan hanya kegiatan yang saya ikuti / saya buat. */
    public bool $hanyaSaya = false;

    /** Tanggal yang sedang dibuka daftarnya (Y-m-d), kosong = belum ada. */
    public string $tanggalTerpilih = '';

    // ===== Form =====
    public bool $formTampil = false;

    public ?string $formId = null;

    public string $judul = '';

    public string $deskripsi = '';

    public string $jenis = 'rapat';

    public string $lokasi = '';

    public string $tanggalMulai = '';

    public string $jamMulai = '09:00';

    public string $tanggalSelesai = '';

    public string $jamSelesai = '';

    public bool $seharian = false;

    /** @var array<int> */
    public array $peserta = [];

    protected $queryString = [
        'bulan' => ['except' => ''],
        'tahun' => ['except' => ''],
        'saringJenis' => ['except' => ''],
        'hanyaSaya' => ['except' => false],
        // Ikut di URL supaya satu hari tertentu bisa dikirim sebagai tautan.
        'tanggalTerpilih' => ['except' => ''],
    ];

    public function mount(): void
    {
        $kini = now();
        $this->bulan = (int) $kini->month;
        $this->tahun = (int) $kini->year;
    }

    // ===== Navigasi bulan =====

    private function acuan(): Carbon
    {
        return Carbon::create($this->tahun, $this->bulan, 1)->startOfDay();
    }

    public function bulanSebelumnya(): void
    {
        $t = $this->acuan()->subMonthNoOverflow();
        $this->bulan = (int) $t->month;
        $this->tahun = (int) $t->year;
        $this->tanggalTerpilih = '';
    }

    public function bulanBerikutnya(): void
    {
        $t = $this->acuan()->addMonthNoOverflow();
        $this->bulan = (int) $t->month;
        $this->tahun = (int) $t->year;
        $this->tanggalTerpilih = '';
    }

    public function keHariIni(): void
    {
        $kini = now();
        $this->bulan = (int) $kini->month;
        $this->tahun = (int) $kini->year;
        $this->tanggalTerpilih = $kini->toDateString();
    }

    public function pilihTanggal(string $tanggal): void
    {
        $this->tanggalTerpilih = $this->tanggalTerpilih === $tanggal ? '' : $tanggal;
    }

    // ===== Izin =====

    private function boleh(string $izin): bool
    {
        return (bool) auth()->user()?->hasPermission($izin);
    }

    public function getBolehTambahProperty(): bool
    {
        return $this->boleh('create_kegiatan');
    }

    public function getBolehUbahProperty(): bool
    {
        return $this->boleh('edit_kegiatan');
    }

    public function getBolehHapusProperty(): bool
    {
        return $this->boleh('delete_kegiatan');
    }

    // ===== Form =====

    public function buatBaru(?string $tanggal = null): void
    {
        if (! $this->bolehTambah) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menambah kegiatan.');

            return;
        }

        $this->bersihkanForm();

        // Tanggal awal mengikuti tanggal yang diklik; kalau tidak ada, hari ini
        // bila bulannya sedang tampil, selain itu tanggal 1 bulan tersebut —
        // supaya tanggal di form tidak pernah melompat keluar dari yang dilihat.
        $this->tanggalMulai = $tanggal
            ?: ($this->tanggalTerpilih ?: $this->tanggalBawaan());

        $this->formTampil = true;
    }

    private function tanggalBawaan(): string
    {
        $kini = now();

        return ((int) $kini->month === $this->bulan && (int) $kini->year === $this->tahun)
            ? $kini->toDateString()
            : $this->acuan()->toDateString();
    }

    public function sunting(string $id): void
    {
        if (! $this->bolehUbah) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah kegiatan.');

            return;
        }

        $k = Kegiatan::with('peserta')->findOrFail($id);

        $this->formId = $k->id;
        $this->judul = $k->judul;
        $this->deskripsi = (string) $k->deskripsi;
        $this->jenis = $k->jenis;
        $this->lokasi = (string) $k->lokasi;
        $this->seharian = (bool) $k->seharian;
        $this->tanggalMulai = $k->mulai->toDateString();
        $this->jamMulai = $k->mulai->format('H:i');
        $this->tanggalSelesai = $k->selesai?->toDateString() ?? '';
        $this->jamSelesai = $k->selesai?->format('H:i') ?? '';
        $this->peserta = $k->peserta->pluck('id')->all();

        $this->resetValidation();
        $this->formTampil = true;
    }

    public function tutupForm(): void
    {
        $this->formTampil = false;
        $this->bersihkanForm();
    }

    private function bersihkanForm(): void
    {
        $this->formId = null;
        $this->judul = '';
        $this->deskripsi = '';
        $this->jenis = 'rapat';
        $this->lokasi = '';
        $this->tanggalMulai = '';
        $this->jamMulai = '09:00';
        $this->tanggalSelesai = '';
        $this->jamSelesai = '';
        $this->seharian = false;
        $this->peserta = [];
        $this->resetValidation();
    }

    protected function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'jenis' => ['required', 'in:'.implode(',', array_keys(Kegiatan::JENIS))],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'tanggalMulai' => ['required', 'date'],
            'jamMulai' => [$this->seharian ? 'nullable' : 'required', 'date_format:H:i'],
            'tanggalSelesai' => ['nullable', 'date'],
            'jamSelesai' => ['nullable', 'date_format:H:i'],
            'peserta' => ['array'],
            'peserta.*' => ['integer', 'exists:users,id'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'judul' => 'judul kegiatan',
            'tanggalMulai' => 'tanggal mulai',
            'jamMulai' => 'jam mulai',
            'tanggalSelesai' => 'tanggal selesai',
            'jamSelesai' => 'jam selesai',
        ];
    }

    public function simpan(): void
    {
        $ubah = (bool) $this->formId;
        $izin = $ubah ? 'edit_kegiatan' : 'create_kegiatan';

        if (! $this->boleh($izin)) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menyimpan kegiatan.');

            return;
        }

        $this->validate();

        $mulai = $this->seharian
            ? Carbon::parse($this->tanggalMulai)->startOfDay()
            : Carbon::parse($this->tanggalMulai.' '.$this->jamMulai);

        $selesai = $this->hitungSelesai($mulai);

        if ($selesai && $selesai->lessThanOrEqualTo($mulai)) {
            $this->addError('jamSelesai', 'Waktu selesai harus setelah waktu mulai.');

            return;
        }

        $data = [
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi ?: null,
            'jenis' => $this->jenis,
            'lokasi' => $this->lokasi ?: null,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'seharian' => $this->seharian,
        ];

        if ($this->formId) {
            $k = Kegiatan::findOrFail($this->formId);
            $k->update($data);
        } else {
            $k = Kegiatan::create($data + ['dibuat_oleh' => auth()->id()]);
        }

        $k->peserta()->sync($this->peserta);

        // Kalender melompat ke bulan kegiatannya. Tanpa ini, menyimpan kegiatan
        // bulan depan terlihat seperti gagal menyimpan: tidak muncul di mana pun.
        $this->bulan = (int) $mulai->month;
        $this->tahun = (int) $mulai->year;
        $this->tanggalTerpilih = $mulai->toDateString();

        $this->formTampil = false;
        $this->bersihkanForm();

        $this->dispatch('swal-success', message: $ubah
            ? 'Kegiatan berhasil diperbarui.'
            : 'Kegiatan berhasil ditambahkan.');
    }

    private function hitungSelesai(Carbon $mulai): ?Carbon
    {
        if ($this->seharian) {
            return $this->tanggalSelesai
                ? Carbon::parse($this->tanggalSelesai)->endOfDay()
                : null;
        }

        if (! $this->jamSelesai) {
            return null;
        }

        // Tanggal selesai boleh kosong untuk kegiatan yang berakhir di hari yang
        // sama — kasus paling sering, dan mengisinya dua kali hanya menambah kerja.
        $tanggal = $this->tanggalSelesai ?: $this->tanggalMulai;

        return Carbon::parse($tanggal.' '.$this->jamSelesai);
    }

    public function hapus(string $id): void
    {
        if (! $this->bolehHapus) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus kegiatan.');

            return;
        }

        Kegiatan::findOrFail($id)->delete();

        $this->dispatch('swal-success', message: 'Kegiatan berhasil dihapus.');
    }

    // ===== Tampilan =====

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $awalBulan = $this->acuan();
        $akhirBulan = $awalBulan->copy()->endOfMonth();

        // Grid selalu mulai Senin dan berakhir Minggu supaya kolomnya rata.
        $awalGrid = $awalBulan->copy()->startOfWeek(Carbon::MONDAY);
        $akhirGrid = $akhirBulan->copy()->endOfWeek(Carbon::SUNDAY);

        $idSaya = auth()->id();

        $kegiatan = Kegiatan::with('peserta:id,name')
            ->dalamRentang($awalGrid, $akhirGrid)
            ->when($this->saringJenis, fn ($q) => $q->where('jenis', $this->saringJenis))
            ->when($this->hanyaSaya, fn ($q) => $q->where(function ($w) use ($idSaya) {
                $w->where('dibuat_oleh', $idSaya)
                    ->orWhereHas('peserta', fn ($p) => $p->where('users.id', $idSaya));
            }))
            ->get()
            ->groupBy(fn (Kegiatan $k) => $k->mulai->toDateString());

        $minggu = [];
        $hari = $awalGrid->copy();

        while ($hari->lessThanOrEqualTo($akhirGrid)) {
            $baris = [];

            for ($i = 0; $i < 7; $i++) {
                $kunci = $hari->toDateString();
                $baris[] = [
                    'tanggal' => $kunci,
                    'angka' => (int) $hari->day,
                    'bulanIni' => (int) $hari->month === $this->bulan,
                    'hariIni' => $hari->isToday(),
                    'akhirPekan' => $hari->isWeekend(),
                    'kegiatan' => $kegiatan->get($kunci, collect()),
                ];
                $hari->addDay();
            }

            $minggu[] = $baris;
        }

        return view('livewire.pages.admin.kegiatan.kegiatan-kalender', [
            'minggu' => $minggu,
            'namaBulan' => $awalBulan->locale('id')->translatedFormat('F Y'),
            'jumlahBulanIni' => $kegiatan->flatten()->filter(
                fn (Kegiatan $k) => (int) $k->mulai->month === $this->bulan && (int) $k->mulai->year === $this->tahun
            )->count(),
            'berikutnya' => Kegiatan::with('peserta:id,name')
                ->where('mulai', '>=', now())
                ->orderBy('mulai')
                ->limit(5)
                ->get(),
            'daftarKegiatanTerpilih' => $this->tanggalTerpilih
                ? $kegiatan->get($this->tanggalTerpilih, collect())
                : collect(),
            'semuaKaryawan' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
