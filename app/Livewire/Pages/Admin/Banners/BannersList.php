<?php

namespace App\Livewire\Pages\Admin\Banners;

use App\Models\Banners;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class BannersList extends Component
{
    use WithPagination;

    public $searchBanners = '';

    // Reset page ketika search berubah
    public function updatedSearchBanners()
    {
        $this->resetPage();
    }

    // Hapus Banners
    public function deleteBanners($id)
    {
        $Banners = Banners::find($id);

        if (!$Banners) {
            $this->dispatch('delete-error', message: 'Data Banners tidak ditemukan!');
            return;
        }

        // Hapus file fisik jika ada
        if ($Banners->gambar) {
            $filePath = storage_path('app/public/img/banners/' . $Banners->gambar);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Hapus record dari DB
        $Banners->delete();

        $this->dispatch('Banners-deleted', id: $id);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        $Banners = Banners::query()
            ->where('judul', 'like', "%{$this->searchBanners}%")
            ->orWhere('deskripsi', 'like', "%{$this->searchBanners}%")
            ->orWhere('status', 'like', "%{$this->searchBanners}%")
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.Banners.Banners-list', [
            'Banners' => $Banners,
        ]);
    }
}
