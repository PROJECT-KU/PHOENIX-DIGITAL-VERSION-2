<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use App\Models\Testimoni;
use Livewire\Component;
use Livewire\WithPagination;

class TestimoniList extends Component
{
    use WithPagination;

    public $searchTestimoni = '';

    // Tab moderasi aktif: pending (menunggu) | active (disetujui) | non-active (ditolak) | all
    public string $filter = 'pending';

    public function updatedSearchTestimoni()
    {
        $this->resetPage();
    }

    public function setFilter(string $f): void
    {
        $this->filter = $f;
        $this->resetPage();
    }

    /**
     * Setujui testimoni -> tampil di publik (status active).
     * Logika auto-member dipertahankan persis seperti sebelumnya.
     */
    public function approve($id)
    {
        if (! auth()->user()->hasPermission('edit_testimoni')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin memoderasi testimoni.');

            return;
        }

        $testimoni = Testimoni::find($id);
        if (! $testimoni) {
            $this->dispatch('swal-error', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        $testimoni->update(['status' => 'active']);
        $this->dispatch('sidebar-badge-updated');

        // Pengirim jadi member otomatis bila nomornya cocok pelanggan yang
        // punya pesanan selesai. Menyetujui testimoni tamu/admin tidak
        // mengaktifkan siapa pun (customer null).
        if ($testimoni->customer && $testimoni->customer->aktifkanMember()) {
            $this->dispatch('swal-success', message: $testimoni->customer->nama.' disetujui & otomatis jadi Member 🎉');

            return;
        }

        $this->dispatch('swal-success', message: 'Testimoni disetujui & kini tampil di publik.');
    }

    /** Tolak testimoni -> disembunyikan dari publik (status non-active). */
    public function reject($id)
    {
        if (! auth()->user()->hasPermission('edit_testimoni')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin memoderasi testimoni.');

            return;
        }

        $testimoni = Testimoni::find($id);
        if (! $testimoni) {
            $this->dispatch('swal-error', message: 'Data Testimoni tidak ditemukan!');

            return;
        }

        // Menolak TIDAK mencabut keanggotaan yang sudah terlanjur diberikan —
        // poin & kode referral tetap hak pelanggan.
        $testimoni->update(['status' => 'non-active']);
        $this->dispatch('sidebar-badge-updated');
        $this->dispatch('swal-success', message: 'Testimoni ditolak & disembunyikan dari publik.');
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
        // Menghapus kiriman pelanggan yang belum ditinjau juga mengurangi badge.
        $this->dispatch('sidebar-badge-updated');
    }

    public function render()
    {
        // customer + hitungan pesanan selesai dimuat sekalian (1 query) supaya
        // admin bisa menilai keaslian testimoni tanpa membuka halaman lain.
        $Testimoni = Testimoni::query()
            ->with(['customer' => fn ($q) => $q->withCount([
                'orders as belanja_selesai_count' => fn ($o) => $o->where('status', 'completed'),
            ])])
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->when($this->searchTestimoni !== '', function ($q) {
                $term = "%{$this->searchTestimoni}%";
                $q->where(function ($sub) use ($term) {
                    $sub->where('nama', 'like', $term)
                        ->orWhere('peran', 'like', $term)
                        ->orWhere('pesan', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);

        $tabCounts = [
            'all' => Testimoni::count(),
            'pending' => Testimoni::where('status', 'pending')->count(),
            'active' => Testimoni::where('status', 'active')->count(),
            'non-active' => Testimoni::where('status', 'non-active')->count(),
        ];

        return view('livewire.pages.admin.testimoni.testimoni-list', [
            'Testimoni' => $Testimoni,
            'tabCounts' => $tabCounts,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
