<?php

namespace App\Livewire\Pages\Admin\Promo;

use App\Models\Promo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class PromoList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $searchDataPromo = '';

    protected $listeners = ['promoDeleted' => '$refresh'];

    public function updatingSearchDataPromo()
    {
        $this->resetPage();
    }

    #[On('delete-promo-data')]
    public function delete($id)
    {
        try {
            $promo = Promo::findOrFail($id);
            $promo->delete();

            session()->flash('success', 'Promo berhasil dihapus');
            $this->dispatch('promoDeleted');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus promo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $now = now();

        // Nonaktifkan promo yang selesai
        Promo::where('is_active', true)
            ->where('selesai_promo', '<', $now)
            ->update(['is_active' => false]);

        // Aktifkan promo yang sudah memasuki masa mulai
        Promo::where('is_active', false)
            ->where('mulai_promo', '<=', $now)
            ->where('selesai_promo', '>=', $now) // Pastikan belum selesai
            ->update(['is_active' => true]);

        $promos = Promo::query()
            ->when($this->searchDataPromo, function ($query) {
                $query->where('nama_promo', 'like', '%' . $this->searchDataPromo . '%')
                    ->orWhere('kode_promo', 'like', '%' . $this->searchDataPromo . '%')
                    ->orWhere('tipe_promo', 'like', '%' . $this->searchDataPromo . '%')
                    ->orWhere('kode_promo', 'like', '%' . $this->searchDataPromo . '%')
                    ->orWhere('diskon_member_persen', 'like', '%' . $this->searchDataPromo . '%')
                    ->orWhere('diskon_member_nominal', 'like', '%' . $this->searchDataPromo . '%')
                    ->orWhere('diskon_non_member_persen', 'like', '%' . $this->searchDataPromo . '%')
                    ->orWhere('diskon_non_member_nominal', 'like', '%' . $this->searchDataPromo . '%')
                    ->orWhereRaw("CASE WHEN is_active = 1 THEN 'aktif' ELSE 'nonaktif' END LIKE ?", ['%' . strtolower($this->searchDataPromo) . '%'])
                    ->orWhereRaw("CASE WHEN show_on_homepage = 1 THEN 'homepage' ELSE 'biasa' END LIKE ?", ['%' . strtolower($this->searchDataPromo) . '%']);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.promo.promo-list', ['promos' => $promos])
            ->layout('livewire.layout.templateindex');
    }
}
