<?php

namespace App\Livewire\Pages\Admin\Pemasukan;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\Pemasukan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PemasukanList extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $bulan = '';

    public $tahun = '';

    /* ===== Form (CRUD via modal) ===== */
    public bool $showForm = false;

    public ?string $editingId = null;

    public $formTanggal = '';

    public $formNominal = '';

    public $formKategori = '';

    public $formDeskripsi = '';

    /* ===== Bukti (file/gambar/foto langsung) ===== */
    public array $buktiLama = []; // path yang sudah tersimpan (mode edit)

    public array $buktiBaru = []; // file baru yang belum tersimpan

    public $tempUpload = [];       // penampung input file sebelum dipindah

    protected $queryString = [
        'search' => ['except' => ''],
        'bulan' => ['except' => ''],
        'tahun' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBulan(): void
    {
        $this->resetPage();
    }

    public function updatedTahun(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['search']);
        $this->bulan = now()->month;
        $this->tahun = now()->year;
        $this->resetPage();
    }

    private function bolehKelola(): bool
    {
        return auth()->user()?->hasPermission('create_pemasukan') ?? false;
    }

    private function toNumber($value): int
    {
        return (int) preg_replace('/[^0-9]/', '', (string) $value);
    }

    private function syncCashFlow(Pemasukan $p): void
    {
        app(SyncCashFlowAction::class)->execute($p, [
            'amount' => (float) $p->nominal,
            'type' => 'income',
            'date' => $p->tanggal,
            'category' => 'Pemasukan Lainnya',
            'description' => trim(($p->kategori ? $p->kategori.' — ' : '').($p->deskripsi ?: 'Pemasukan lain')),
        ]);
    }

    public function openCreate(): void
    {
        if (! $this->bolehKelola()) {
            $this->dispatch('pemasukan-error', message: 'Anda tidak punya izin menambah pemasukan.');

            return;
        }

        $this->reset(['editingId', 'formNominal', 'formKategori', 'formDeskripsi', 'buktiLama', 'buktiBaru', 'tempUpload']);
        $this->resetErrorBag();
        $this->formTanggal = now()->toDateString();
        $this->showForm = true;
    }

    public function openEdit($id): void
    {
        if (! auth()->user()?->hasPermission('edit_pemasukan')) {
            $this->dispatch('pemasukan-error', message: 'Anda tidak punya izin mengubah pemasukan.');

            return;
        }

        $p = Pemasukan::find($id);
        if (! $p) {
            $this->dispatch('pemasukan-error', message: 'Data pemasukan tidak ditemukan.');

            return;
        }

        $this->reset(['buktiBaru', 'tempUpload']);
        $this->editingId = $p->id;
        $this->formTanggal = $p->tanggal->toDateString();
        $this->formNominal = number_format((int) $p->nominal, 0, ',', '.');
        $this->formKategori = $p->kategori;
        $this->formDeskripsi = $p->deskripsi;
        $this->buktiLama = $p->bukti ?? [];
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetErrorBag();
    }

    /**
     * Setiap file dipilih (file/gambar/kamera) → akumulasi ke $buktiBaru.
     */
    public function updatedTempUpload(): void
    {
        $this->validate(
            ['tempUpload.*' => 'file|max:4096|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx'],
            [],
            ['tempUpload.*' => 'file bukti']
        );

        foreach ($this->tempUpload as $file) {
            $this->buktiBaru[] = $file;
        }

        $this->tempUpload = [];
    }

    public function removeBuktiLama(int $index): void
    {
        unset($this->buktiLama[$index]);
        $this->buktiLama = array_values($this->buktiLama);
    }

    public function removeBuktiBaru(int $index): void
    {
        unset($this->buktiBaru[$index]);
        $this->buktiBaru = array_values($this->buktiBaru);
    }

    public function save(): void
    {
        $izin = $this->editingId ? 'edit_pemasukan' : 'create_pemasukan';
        if (! auth()->user()?->hasPermission($izin)) {
            $this->dispatch('pemasukan-error', message: 'Anda tidak punya izin.');

            return;
        }

        $this->formNominal = (string) $this->toNumber($this->formNominal);

        $this->validate([
            'formTanggal' => ['required', 'date'],
            'formNominal' => ['required', 'numeric', 'min:1'],
            'formKategori' => ['required', 'string', 'max:100'],
            'formDeskripsi' => ['nullable', 'string', 'max:500'],
        ], [], [
            'formTanggal' => 'tanggal',
            'formNominal' => 'nominal',
            'formKategori' => 'kategori/sumber',
            'formDeskripsi' => 'keterangan',
        ]);

        // Validasi file bukti yang masih tertahan di input (jika ada).
        if (! empty($this->tempUpload)) {
            $this->updatedTempUpload();
        }

        // Susun daftar bukti akhir: yang lama dipertahankan + yang baru disimpan.
        $paths = array_values($this->buktiLama);
        foreach ($this->buktiBaru as $file) {
            if ($file && ! is_string($file)) {
                $paths[] = $file->store('pemasukan', 'public');
            }
        }

        // Hapus file lama yang dibuang saat edit.
        if ($this->editingId) {
            $original = Pemasukan::find($this->editingId)?->bukti ?? [];
            foreach (array_diff($original, $paths) as $dibuang) {
                Storage::disk('public')->delete($dibuang);
            }
        }

        $data = [
            'tanggal' => $this->formTanggal,
            'nominal' => $this->toNumber($this->formNominal),
            'kategori' => $this->formKategori,
            'deskripsi' => $this->formDeskripsi,
            'bukti' => $paths ?: null,
        ];

        if ($this->editingId) {
            $p = Pemasukan::find($this->editingId);
            if (! $p) {
                $this->dispatch('pemasukan-error', message: 'Data pemasukan tidak ditemukan.');

                return;
            }
            $p->update($data);
        } else {
            $data['id_transaksi'] = Str::upper(Str::random(5));
            $data['penginput_id'] = auth()->id();
            $p = Pemasukan::create($data);
        }

        $this->syncCashFlow($p);

        $this->showForm = false;
        $this->reset(['editingId', 'formNominal', 'formKategori', 'formDeskripsi', 'buktiLama', 'buktiBaru', 'tempUpload']);
        $this->resetPage();
        $this->dispatch('pemasukan-saved');
    }

    public function deletePemasukan($id): void
    {
        if (! auth()->user()?->hasPermission('delete_pemasukan')) {
            $this->dispatch('pemasukan-deleteError', message: 'Anda tidak punya izin menghapus pemasukan.');

            return;
        }

        $p = Pemasukan::find($id);
        if (! $p) {
            $this->dispatch('pemasukan-deleteError', message: 'Data pemasukan tidak ditemukan.');

            return;
        }

        $p->delete(); // cash flow ikut terhapus (booted deleting)
        $this->dispatch('pemasukan-deleted');
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
        $now = (int) now()->year;

        return range($now, $now - 5);
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $bulan = (int) ($this->bulan ?: now()->month);
        $tahun = (int) ($this->tahun ?: now()->year);

        $query = Pemasukan::query()
            ->with('penginput')
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn ($x) => $x->where('deskripsi', 'like', $term)
                    ->orWhere('kategori', 'like', $term)
                    ->orWhere('nominal', 'like', $term)
                    ->orWhere('id_transaksi', 'like', $term));
            })
            ->when(! $this->search, fn ($q) => $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan));

        $pemasukans = (clone $query)->orderBy('tanggal', 'desc')->paginate(10);
        $totalPemasukan = (float) (clone $query)->sum('nominal');

        return view('livewire.pages.admin.pemasukan.pemasukan-list', [
            'pemasukans' => $pemasukans,
            'totalPemasukan' => $totalPemasukan,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ]);
    }
}
