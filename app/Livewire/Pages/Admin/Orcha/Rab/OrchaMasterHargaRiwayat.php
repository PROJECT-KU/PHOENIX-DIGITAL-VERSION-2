<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Riwayat perubahan harga — satu baris, atau seluruhnya.
 *
 * Dulu dua panel yang terbuka di dalam daftar. Riwayat adalah hal yang DIBACA
 * lama: dibandingkan dengan penawaran lama, dibuka di tab kedua, dikirim ke
 * rekan yang menanyakan "kapan tiket ini naik?". Panel yang hidup di dalam
 * daftar tidak bisa satu pun dari itu — alamatnya tetap alamat daftar, dan
 * menyegarkan halaman menutupnya.
 *
 * Satu komponen untuk dua jalur, karena pertanyaannya memang sama dengan
 * saringan yang berbeda: tanpa {harga} ia menjawab "apa saja yang berubah
 * belakangan ini", dengan {harga} ia menjawab "harga ini pernah berapa".
 */
class OrchaMasterHargaRiwayat extends Component
{
    use MemanggilOrcha;

    public ?int $hargaId = null;

    public array $baris = [];

    public array $meta = [];

    public string $galat = '';

    /**
     * Rentang hari untuk riwayat seluruhnya.
     *
     * Tidak berlaku pada riwayat satu baris: di sana yang dicari justru
     * perubahan yang paling lama, dan memotongnya di 30 hari menyembunyikan
     * justru yang dicari.
     */
    #[Url(as: 'hari')]
    public int $hari = 30;

    /** Saringan daftar yang sedang aktif, supaya Kembali pulang ke sana. */
    #[Url(as: 'kembali')]
    public string $kembali = '';

    public function mount(?int $harga = null): void
    {
        $this->hargaId = $harga;
        $this->muat();
    }

    public function updatedHari(): void
    {
        // Hanya berarti pada riwayat seluruhnya.
        if (! $this->hargaId) {
            $this->muat();
        }
    }

    private function muat(): void
    {
        try {
            $balasan = $this->hargaId
                ? $this->orcha()->ambil("/master-harga/{$this->hargaId}/riwayat")
                : $this->orcha()->ambil('/master-harga/riwayat', array_filter([
                    'hari' => $this->hari ?: null,
                    'batas' => 100,
                ]));

            $this->baris = $balasan['data'] ?? [];
            $this->meta = $balasan['meta'] ?? [];
            $this->galat = '';
        } catch (OrchaTidakTerjangkau $e) {
            $this->galat = $e->getMessage();
        }
    }

    public function tujuanKembali(): string
    {
        $dasar = route('admin.orcha.master-harga');

        return $this->kembali !== '' ? $dasar.'?'.$this->kembali : $dasar;
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.rab.master-harga-riwayat')
            ->layout('livewire.layout.templateindex');
    }
}
