<?php

namespace App\Livewire\Pages\Admin\Banners;

use App\Models\Banners;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BannersList extends Component
{
    use WithPagination;

    #[Url(as: 'cari', except: '')]
    public $searchBanners = '';

    /** '' | tayang | terjadwal | berakhir | nonaktif — keadaan NYATA di publik. */
    #[Url(as: 'keadaan', except: '')]
    public string $keadaan = '';

    /** Banner yang sedang dibuka di jendela detail. */
    public ?string $lihatId = null;

    public const KEADAAN = ['tayang', 'terjadwal', 'berakhir', 'nonaktif'];

    /**
     * Rute admin.Banners.show (/admin/DataBanners/{id}) membuka halaman ini
     * dengan jendela detail. Dulu rute itu memuat HALAMAN UBAH hanya dengan
     * izin melihat — pengguna tanpa izin ubah bisa menyunting banner.
     */
    public function mount($Banners = null): void
    {
        if ($Banners) {
            $this->lihatId = Banners::whereKey($Banners)->value('id');
        }
    }

    // Reset page ketika search berubah
    public function updatedSearchBanners()
    {
        $this->resetPage();
    }

    public function updatedKeadaan()
    {
        if (! in_array($this->keadaan, self::KEADAAN, true)) {
            $this->keadaan = '';
        }
        $this->resetPage();
    }

    public function lihat(string $id): void
    {
        $this->lihatId = Banners::whereKey($id)->value('id');
    }

    public function tutupLihat(): void
    {
        $this->lihatId = null;
    }

    /** Saklar cepat Aktif ⇄ Non-Aktif dari kartu, tanpa membuka form. */
    public function alihStatus(string $id): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_banners'), 403);

        $banner = Banners::findOrFail($id);
        $banner->update(['status' => $banner->status === 'active' ? 'non-active' : 'active']);

        $this->dispatch('swal-success', message: $banner->status === 'active'
            ? 'Banner diaktifkan'.($banner->sedangTayang() ? ' dan kini tayang.' : ' — tayang sesuai jadwal.')
            : 'Banner disembunyikan dari beranda.');
    }

    /**
     * Geser banner satu posisi (naik = tampil lebih dulu di beranda).
     * Urutan dinormalkan dulu jadi 1..n supaya nilai kembar/berlubang dari
     * data lama tidak membuat tombol tampak tidak bekerja.
     */
    public function geser(string $id, string $arah): void
    {
        abort_unless(auth()->user()?->hasPermission('edit_banners'), 403);

        $ids = Banners::orderBy('urutan')->orderBy('created_at')->pluck('id')->values();
        $i = $ids->search($id);
        $j = $arah === 'naik' ? $i - 1 : $i + 1;
        if ($i === false || $j < 0 || $j >= $ids->count()) {
            return;
        }

        $baru = $ids->all();
        [$baru[$i], $baru[$j]] = [$baru[$j], $baru[$i]];
        foreach ($baru as $posisi => $bid) {
            Banners::whereKey($bid)->update(['urutan' => $posisi + 1]);
        }
    }

    // Hapus Banners
    public function deleteBanners($id)
    {
        if (! auth()->user()->hasPermission('delete_banners')) {
            $this->dispatch('Banners-deleteError', message: 'Anda tidak memiliki izin menghapus banner.');

            return;
        }

        $Banners = Banners::find($id);

        if (! $Banners) {
            $this->dispatch('Banners-deleteError', message: 'Data Banners tidak ditemukan!');

            return;
        }

        // Hapus file fisik jika ada
        if ($Banners->gambar) {
            $filePath = storage_path('app/public/img/banners/'.$Banners->gambar);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Hapus record dari DB
        $Banners->delete();
        $this->lihatId = null;

        $this->dispatch('Banners-deleted', id: $id);
    }

    /** Terapkan saringan keadaan (sama dengan Banners::keadaanTayang()). */
    protected function saringKeadaan($q, string $keadaan)
    {
        $now = now();

        return match ($keadaan) {
            'tayang' => $q->tayang(),
            'terjadwal' => $q->where('status', 'active')->where('mulai_tayang', '>', $now),
            'berakhir' => $q->where('status', 'active')->where('selesai_tayang', '<', $now),
            'nonaktif' => $q->where('status', '!=', 'active'),
            default => $q,
        };
    }

    public function render()
    {
        $Banners = Banners::query()
            ->when($this->searchBanners, fn ($q) => $q->where(fn ($s) => $s
                ->where('judul', 'like', "%{$this->searchBanners}%")
                ->orWhere('deskripsi', 'like', "%{$this->searchBanners}%")))
            ->when($this->keadaan, fn ($q) => $this->saringKeadaan($q, $this->keadaan))
            // Sama dengan urutan slide di beranda (kecil = lebih dulu).
            ->orderBy('urutan')->orderBy('created_at')
            ->paginate(12);

        $hitung = ['semua' => Banners::count()];
        foreach (self::KEADAAN as $k) {
            $hitung[$k] = $this->saringKeadaan(Banners::query(), $k)->count();
        }

        return view('livewire.pages.admin.Banners.Banners-list', [
            'Banners' => $Banners,
            'hitung' => $hitung,
            'detail' => $this->lihatId ? Banners::find($this->lihatId) : null,
            // Posisi slide yang sedang tayang (1 = slide pertama di beranda).
            'nomorSlide' => Banners::tayang()->pluck('id')->flip()->map(fn ($i) => $i + 1),
            'idPertama' => Banners::orderBy('urutan')->orderBy('created_at')->value('id'),
            'idTerakhir' => Banners::orderByDesc('urutan')->orderByDesc('created_at')->value('id'),
        ])
            ->layout('livewire.layout.templateindex');
    }
}
