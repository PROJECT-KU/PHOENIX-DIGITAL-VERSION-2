<?php

namespace App\Livewire\Pages\Admin\Orcha\Pendaftaran;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Satu pelanggan, selengkapnya.
 *
 * Daftar pendaftaran menjawab "siapa saja yang mendaftar"; halaman ini
 * menjawab pertanyaan yang muncul setelahnya, dan hampir selalu datang
 * bersamaan lewat WhatsApp: sudah bayar berapa, siapa saja yang ikut dan
 * dijemput di mana, riwayat kesehatannya sudah lengkap belum, serta apakah
 * ada pengajuan pembatalan. Sebelumnya jawabannya tersebar di empat menu.
 */
class OrchaPendaftaranDetail extends Component
{
    use MemanggilOrcha;

    public int $pendaftaranId;

    public array $data = [];

    /** Riwayat kesehatan hanya dimuat saat dibuka, dan pembukaannya dicatat. */
    public bool $riwayatTerbuka = false;

    public array $riwayat = [];

    public function mount(int $pendaftaran): void
    {
        $this->pendaftaranId = $pendaftaran;
    }

    public function ubahStatus(string $status): void
    {
        $this->kirimPerubahan(
            "/pendaftaran/{$this->pendaftaranId}/status",
            ['status' => $status],
            'Status pendaftaran diperbarui di Orcha.'
        );
    }

    public function bukaRiwayat(): void
    {
        // Penjagaan sebenarnya ada di sini, bukan di tombol yang disembunyikan.
        abort_unless(auth()->user()->hasPermission('view_orcha_kesehatan'), 403);

        $this->riwayat = [];

        try {
            $this->riwayat = $this->orcha()->ambil("/pendaftaran/{$this->pendaftaranId}/riwayat-kesehatan")['data'] ?? [];
            $this->riwayatTerbuka = true;
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tutupRiwayat(): void
    {
        $this->riwayatTerbuka = false;
        $this->riwayat = [];
    }

    public function render()
    {
        $hasil = $this->muat("/pendaftaran/{$this->pendaftaranId}");
        $this->data = $hasil['data'] ?? [];

        return view('livewire.pages.admin.orcha.pendaftaran.detail', [
            'pendaftaran' => $this->data,
            'pilihanStatus' => $this->rujukan('status_pendaftaran'),
        ])->layout('livewire.layout.templateindex');
    }
}
