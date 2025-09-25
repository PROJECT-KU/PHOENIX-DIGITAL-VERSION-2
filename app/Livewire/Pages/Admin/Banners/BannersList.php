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
            $this->dispatchBrowserEvent('delete-error', ['message' => 'Data Banners tidak ditemukan!']);
            return;
        }

        $Banners->delete();

        $this->dispatchBrowserEvent('Banners-deleted', ['id' => $id]);
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
