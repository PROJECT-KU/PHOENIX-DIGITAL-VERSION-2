<?php

namespace App\Livewire\Pages\Admin\Asthana;

use App\Models\Asthana\Berita;
use App\Models\Asthana\Portofolio;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar portofolio / berita situs Asthana.
 */
#[Layout('livewire.layout.templateindex')]
class AsthanaTulisanList extends Component
{
    use WithPagination;

    /** judul, ikon, warna, keterangan */
    public const JENIS = [
        'portofolio' => ['Portofolio', 'bi-grid-1x2', '#2455e6', 'Proyek yang pernah dikerjakan — bukti terkuat bagi calon klien.'],
        'berita' => ['Berita', 'bi-newspaper', '#16a34a', 'Kabar perusahaan, kegiatan, dan artikel.'],
    ];

    public string $jenis = 'portofolio';

    #[Url(as: 'cari', except: '')]
    public string $cari = '';

    /** semua | terbit | draf (berita juga: terjadwal) */
    #[Url(as: 'keadaan', except: 'semua')]
    public string $keadaan = 'semua';

    public function mount(string $jenis = 'portofolio'): void
    {
        abort_unless(isset(self::JENIS[$jenis]), 404);
        $this->jenis = $jenis;
    }

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function setKeadaan(string $k): void
    {
        $this->keadaan = in_array($k, ['semua', 'terbit', 'draf', 'terjadwal'], true) ? $k : 'semua';
        $this->resetPage();
    }

    private function kueri()
    {
        $isBerita = $this->jenis === 'berita';
        $q = $isBerita ? Berita::query() : Portofolio::query();

        if ($this->cari !== '') {
            $kata = '%'.$this->cari.'%';
            $q->where(fn ($w) => $w->where('judul', 'like', $kata)
                ->orWhere('ringkasan', 'like', $kata)
                ->when(! $isBerita, fn ($w2) => $w2->orWhere('klien', 'like', $kata))
                ->when($isBerita, fn ($w2) => $w2->orWhere('kategori', 'like', $kata)));
        }

        return $q;
    }

    private function saringKeadaan($q, string $k)
    {
        if ($this->jenis === 'berita') {
            return match ($k) {
                'terbit' => $q->terbit(),
                'draf' => $q->whereNull('terbit_at'),
                'terjadwal' => $q->where('terbit_at', '>', now()),
                default => $q,
            };
        }

        return match ($k) {
            'terbit' => $q->where('terbit', true),
            'draf' => $q->where('terbit', false),
            default => $q,
        };
    }

    /** Portofolio: terbit ↔ draf tanpa membuka formulirnya. */
    public function alihTerbit(int $id): void
    {
        $p = Portofolio::findOrFail($id);
        $p->update(['terbit' => ! $p->terbit]);
        $this->dispatch('swal-success', message: $p->terbit ? 'Diterbitkan di situs.' : 'Dijadikan draf — tidak tampil di situs.');
    }

    public function hapus(int $id): void
    {
        $model = $this->jenis === 'berita' ? Berita::findOrFail($id) : Portofolio::findOrFail($id);
        $gambar = array_merge([$model->sampul], $this->jenis === 'portofolio' ? ($model->galeri ?? []) : []);
        $model->delete();
        foreach ($gambar as $jalur) {
            $model::hapusGambar($jalur);
        }

        $this->dispatch('swal-success', message: 'Dihapus dari situs.');
    }

    public function render()
    {
        $daftarKeadaan = $this->jenis === 'berita'
            ? ['semua' => 'Semua', 'terbit' => 'Terbit', 'terjadwal' => 'Terjadwal', 'draf' => 'Draf']
            : ['semua' => 'Semua', 'terbit' => 'Terbit', 'draf' => 'Draf'];

        $jumlah = collect($daftarKeadaan)->map(fn ($_, $k) => $this->saringKeadaan($this->kueri(), $k)->count());

        $urut = $this->saringKeadaan($this->kueri(), $this->keadaan);
        $urut = $this->jenis === 'berita'
            ? $urut->orderByRaw('terbit_at is null desc')->orderByDesc('terbit_at')->orderByDesc('id')
            : $urut->orderBy('urutan')->orderByDesc('tahun')->orderByDesc('id');

        return view('livewire.pages.admin.asthana.tulisan-list', [
            'daftar' => $urut->paginate(10),
            'info' => self::JENIS[$this->jenis],
            'daftarKeadaan' => $daftarKeadaan,
            'jumlah' => $jumlah,
        ]);
    }
}
