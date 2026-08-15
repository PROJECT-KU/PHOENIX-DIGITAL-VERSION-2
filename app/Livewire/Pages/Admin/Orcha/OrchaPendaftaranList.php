<?php

namespace App\Livewire\Pages\Admin\Orcha;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaPendaftaranList extends Component
{
    use MemanggilOrcha;

    /** Pendaftaran yang sedang dibuka riwayat kesehatannya. */
    public ?int $riwayatUntuk = null;

    public string $riwayatNama = '';

    public array $riwayat = [];

    public function ubahStatus(int $id, string $status): void
    {
        $this->kirimPerubahan("/pendaftaran/{$id}/status", ['status' => $status], 'Status pendaftaran diperbarui di Orcha.');
    }

    /**
     * Riwayat kesehatan sengaja diminta terpisah — datanya sensitif, jadi tidak
     * ikut terbawa daftar dan setiap pembukaannya tercatat di sisi Orcha.
     */
    public function bukaRiwayat(int $id, string $nama): void
    {
        // Penjagaan sebenarnya ada di sini, bukan di tombol yang disembunyikan.
        abort_unless(auth()->user()->hasPermission('view_orcha_kesehatan'), 403);

        $this->riwayatUntuk = $id;
        $this->riwayatNama = $nama;
        $this->riwayat = [];

        try {
            $this->riwayat = $this->orcha()->ambil("/pendaftaran/{$id}/riwayat-kesehatan")['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
            $this->riwayatUntuk = null;
        }
    }

    public function tutupRiwayat(): void
    {
        $this->riwayatUntuk = null;
        $this->riwayat = [];
        $this->riwayatNama = '';
    }

    public function render()
    {
        $hasil = $this->muat('/pendaftaran', $this->parameterDaftar());

        return view('livewire.pages.admin.orcha.orcha-pendaftaran-list', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanStatus' => $this->rujukan('status_pendaftaran'),
        ])->layout('livewire.layout.templateindex');
    }
}
