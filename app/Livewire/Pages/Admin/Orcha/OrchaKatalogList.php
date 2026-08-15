<?php

namespace App\Livewire\Pages\Admin\Orcha;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Etalase Orcha — paket wisata dan armada.
 *
 * Baca saja. Pengubahannya masih di admin Orcha karena melibatkan unggah
 * gambar; di sini gunanya untuk melihat data sambil melayani pelanggan tanpa
 * berpindah aplikasi.
 */
class OrchaKatalogList extends Component
{
    use MemanggilOrcha;

    /** 'paket' atau 'armada' — ditentukan rute. */
    public string $jenis = 'paket';

    public function mount(string $jenis = 'paket'): void
    {
        $this->jenis = in_array($jenis, ['paket', 'armada'], true) ? $jenis : 'paket';
    }

    /**
     * Orcha menolak penghapusan yang bisa merugikan — paket yang sudah punya
     * pendaftar, atau unit yang sudah pernah disewa. Alasannya diteruskan apa
     * adanya ke admin lewat toast.
     */
    public function hapus(int $id): void
    {
        $this->hapusData(
            $this->jenis === 'paket' ? "/paket-wisata/{$id}" : "/kendaraan/{$id}",
            $this->jenis === 'paket' ? 'Paket wisata dihapus.' : 'Kendaraan dihapus.'
        );
    }

    public function render()
    {
        $isPaket = $this->jenis === 'paket';

        $parameter = $this->parameterDaftar();
        unset($parameter['status']);

        if ($this->filterStatus !== '') {
            $parameter[$isPaket ? 'kategori' : 'jenis'] = $this->filterStatus;
        }

        $hasil = $this->muat($isPaket ? '/paket-wisata' : '/kendaraan', $parameter);

        return view('livewire.pages.admin.orcha.orcha-katalog-list', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihan' => $this->rujukan($isPaket ? 'kategori_paket' : 'jenis_kendaraan'),
            'isPaket' => $isPaket,
        ])->layout('livewire.layout.templateindex');
    }
}
