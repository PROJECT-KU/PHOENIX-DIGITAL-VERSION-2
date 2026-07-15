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

    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_promo')) {
            $this->dispatch('promoDeleteError', message: 'Anda tidak memiliki izin menghapus promo.');

            return;
        }

        try {
            $promo = Promo::findOrFail($id);

            // Pengecekan status is_active
            if ($promo->is_active) {
                $this->dispatch('promoDeleteError', message: 'Promo masih aktif! Nonaktifkan promo terlebih dahulu sebelum menghapus.');
                return;
            }

            // Jika tidak aktif, jalankan proses delete
            $promo->delete();

            session()->flash('success', 'Promo berhasil dihapus');
            $this->dispatch('promoDeleted');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus promo: ' . $e->getMessage());
            $this->dispatch('promoDeleteError', message: 'Terjadi kesalahan sistem saat menghapus data.');
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

        // Kuota terpakai untuk SEMUA promo di halaman ini dalam 1 query — kalau
        // memanggil $promo->kuotaTerpakai() per baris, jadi 10 query tambahan (N+1).
        // Dasarnya sama persis dgn Promo::kuotaTerpakai(): pesanan non-cancelled.
        $terpakai = \Illuminate\Support\Facades\DB::table('order_promo')
            ->join('orders', 'orders.id', '=', 'order_promo.order_id')
            ->whereIn('order_promo.promo_id', $promos->pluck('id'))
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_promo.promo_id, COUNT(*) as jml')
            ->groupBy('order_promo.promo_id')
            ->pluck('jml', 'order_promo.promo_id');

        return view('livewire.pages.admin.promo.promo-list', [
            'promos' => $promos,
            'kuotaTerpakai' => $terpakai,
        ])->layout('livewire.layout.templateindex');
    }
}
