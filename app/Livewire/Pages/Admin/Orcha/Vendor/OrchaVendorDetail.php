<?php

namespace App\Livewire\Pages\Admin\Orcha\Vendor;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Vendor\Concerns\MenyuntingVendor;
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
    use MenyuntingVendor;

    public int $vendorId;

    public array $data = [];

    /** Halaman daftar RAB — belanjanya tetap dari seluruhnya. */
    public int $halamanRab = 1;

    public function mount(int $vendor): void
    {
        $this->vendorId = $vendor;
        $this->kosongkanVendor();
        $this->muatRincian();
    }

    private function muatRincian(): void
    {
        try {
            $this->data = $this->orcha()->ambil("/vendor/{$this->vendorId}/rincian", [
                'halaman' => $this->halamanRab,
            ])['data'] ?? [];
            $this->galat = '';
        } catch (OrchaTidakTerjangkau $e) {
            $this->galat = $e->getMessage();
        }
    }

    public function keHalamanRab(int $nomor): void
    {
        $this->halamanRab = max(1, $nomor);
        $this->muatRincian();
    }

    /*
     | Menyuntingnya pindah ke halaman tersendiri.
     |
     | Dulu formulirnya terbuka di tempat, dan alasannya masuk akal: halaman
     | ini satu-satunya yang menyebut sudah dibelanjakan berapa, dan
     | keputusan mengubah kontaknya lahir saat angka itu terlihat. Yang
     | dijaga sebenarnya BUKAN formulirnya ada di sini, melainkan admin
     | kembali ke sini sesudah menyimpan — dan itu dipegang ?dari=detail di
     | tombol Ubah.
     |
     | Trait-nya tetap dipakai untuk nomorDari() dan pilihanJenis(), yang
     | dibaca tampilannya saat menggambar rincian.
     */

    public function matikanIni(): void
    {
        $this->matikan($this->vendorId);
        $this->muatRincian();
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.vendor.detail')
            ->layout('livewire.layout.templateindex');
    }
}
