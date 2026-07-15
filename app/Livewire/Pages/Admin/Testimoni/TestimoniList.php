<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use App\Models\Testimoni;
use Livewire\Component;
use Livewire\WithPagination;

class TestimoniList extends Component
{
    use WithPagination;

    public $searchTestimoni = '';

    public function updatedSearchTestimoni()
    {
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        if (! auth()->user()->hasPermission('edit_testimoni')) {
            $this->dispatch('testimoni-deleteError', message: 'Anda tidak memiliki izin mengubah status.');

            return;
        }

        $testimoni = Testimoni::find($id);
        if (! $testimoni) {
            $this->dispatch('testimoni-deleteError', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        $testimoni->status = $testimoni->status === 'active' ? 'non-active' : 'active';
        $testimoni->save();

        // Testimoni DISETUJUI (dinyalakan) -> pengirimnya jadi member otomatis.
        // customer hanya terisi bila nomornya cocok DAN punya pesanan selesai,
        // jadi menyetujui testimoni tamu/buatan admin tidak mengaktifkan siapa pun.
        // Dimatikan lagi TIDAK mencabut keanggotaan — poin & kode referral sudah
        // terlanjur jadi haknya.
        if ($testimoni->status === 'active' && $testimoni->customer && $testimoni->customer->aktifkanMember()) {
            $this->dispatch('swal-success', message: $testimoni->customer->nama.' otomatis jadi Member 🎉');
        }

        $this->dispatch('testimoni-status', active: $testimoni->status === 'active');
    }

    public function deleteTestimoni($id)
    {
        if (! auth()->user()->hasPermission('delete_testimoni')) {
            $this->dispatch('testimoni-deleteError', message: 'Anda tidak memiliki izin menghapus testimoni.');

            return;
        }

        $testimoni = Testimoni::find($id);

        if (! $testimoni) {
            $this->dispatch('testimoni-deleteError', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        if ($testimoni->foto) {
            $filePath = storage_path('app/public/img/testimoni/' . $testimoni->foto);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $testimoni->delete();

        $this->dispatch('testimoni-deleted', id: $id);
    }

    public function render()
    {
        // customer + hitungan pesanan selesai dimuat sekalian (1 query) supaya
        // admin bisa menilai keaslian testimoni tanpa membuka halaman lain.
        $Testimoni = Testimoni::query()
            ->with(['customer' => fn ($q) => $q->withCount([
                'orders as belanja_selesai_count' => fn ($o) => $o->where('status', 'completed'),
            ])])
            ->where(function ($q) {
                $q->where('nama', 'like', "%{$this->searchTestimoni}%")
                    ->orWhere('peran', 'like', "%{$this->searchTestimoni}%")
                    ->orWhere('pesan', 'like', "%{$this->searchTestimoni}%")
                    ->orWhere('status', 'like', "%{$this->searchTestimoni}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.pages.admin.testimoni.testimoni-list', [
            'Testimoni' => $Testimoni,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
