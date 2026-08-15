<?php

namespace App\Livewire\Pages\Admin\Orcha\Pembatalan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaPembatalanList extends Component
{
    use MemanggilOrcha;

    public ?int $sedangDiubah = null;

    public string $statusBaru = '';

    public string $catatanAdmin = '';

    public function buka(int $id, string $status, ?string $catatan = null): void
    {
        $this->sedangDiubah = $id;
        $this->statusBaru = $status;
        $this->catatanAdmin = (string) $catatan;
    }

    public function tutup(): void
    {
        $this->sedangDiubah = null;
        $this->statusBaru = '';
        $this->catatanAdmin = '';
    }

    public function simpan(): void
    {
        if (! $this->sedangDiubah) {
            return;
        }

        $this->kirimPerubahan(
            "/pembatalan/{$this->sedangDiubah}/status",
            ['status' => $this->statusBaru, 'catatan_admin' => $this->catatanAdmin],
            'Status pembatalan diperbarui di Orcha.'
        );

        $this->tutup();
    }

    public function render()
    {
        $hasil = $this->muat('/pembatalan', $this->parameterDaftar());

        return view('livewire.pages.admin.orcha.pembatalan.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanStatus' => $this->rujukan('status_pembatalan'),
        ])->layout('livewire.layout.templateindex');
    }
}
