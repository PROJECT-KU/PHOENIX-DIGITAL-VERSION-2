<?php

namespace App\Livewire\Pages\Admin\Orcha\Vendor;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Satu vendor beserta jejaknya.
 *
 * Daftar vendor menjawab "siapa saja"; halaman ini menjawab dua pertanyaan
 * yang datanya sudah ada sejak awal tetapi tidak pernah dijumlahkan: harga
 * apa saja yang dibeli dari sini, RAB mana saja yang memakainya, dan sudah
 * berapa yang dibelanjakan ke sana.
 *
 * Angka belanjanya dihitung Orcha dengan rumus yang SAMA dengan rincian biaya
 * RAB — satu bus 1,1 juta yang jadi 2 unit selama 3 hari bernilai 6,6 juta,
 * dan angka itulah yang dipakai menawar harga tahun depan.
 */
class OrchaVendorDetail extends Component
{
    use MemanggilOrcha;

    public int $vendorId;

    public array $data = [];

    public function mount(int $vendor): void
    {
        $this->vendorId = $vendor;

        try {
            $this->data = $this->orcha()->ambil("/vendor/{$vendor}/rincian")['data'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->galat = $e->getMessage();
        }
    }

    /** Nomor yang bisa ditelepon — aturan yang sama dengan daftar vendor. */
    public function nomorDari(?string $kontak): ?string
    {
        if (! preg_match('/\d[\d\s().+-]{6,}/', (string) $kontak, $cocok)) {
            return null;
        }

        $angka = preg_replace('/\D/', '', $cocok[0]);

        return strlen($angka) >= 7 ? $angka : null;
    }

    public function nomorWa(?string $kontak): ?string
    {
        $angka = $this->nomorDari($kontak);

        if ($angka === null) {
            return null;
        }

        return str_starts_with($angka, '0') ? '62'.substr($angka, 1) : $angka;
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.vendor.detail')
            ->layout('livewire.layout.templateindex');
    }
}
