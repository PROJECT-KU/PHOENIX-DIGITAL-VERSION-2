<?php

namespace App\Livewire\Pages\Admin\Orcha\Vendor;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use App\Livewire\Pages\Admin\Orcha\Vendor\Concerns\MenyuntingVendor;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Halaman tersendiri untuk membuat dan menyunting vendor.
 *
 * Sebelumnya formulirnya terbuka DI DALAM daftar: kartu yang menyelip di
 * antara saringan dan tabel, mendorong daftarnya keluar pandangan. Dua
 * akibatnya nyata. Menyunting vendor di halaman kesembilan mengembalikan
 * admin ke halaman satu begitu tersimpan, dan alamatnya tidak pernah
 * menunjuk apa yang sedang dikerjakan — tidak bisa dibagikan, tidak bisa
 * dibuka di tab baru, dan menekan Kembali justru meninggalkan daftarnya.
 *
 * Isiannya sendiri tidak disalin: ia hidup di satu partial yang dipakai
 * halaman ini maupun layar rincian vendor. Yang pindah cuma tempatnya.
 */
class OrchaVendorForm extends Component
{
    use MemanggilOrcha;
    use MenyuntingVendor;

    public ?int $vendorId = null;

    public bool $ubah = false;

    /** Nama yang sudah tersimpan — dipakai di judul halaman dan remah. */
    public string $namaTersimpan = '';

    /**
     * Dari mana admin datang, supaya Batal dan Simpan mengembalikannya ke
     * sana — bukan selalu ke daftar.
     */
    #[Url(as: 'dari')]
    public string $dari = '';

    public function mount(?int $vendor = null): void
    {
        $this->kosongkanVendor();

        if (! $vendor) {
            $this->tambah = true;

            return;
        }

        $this->vendorId = $vendor;
        $this->sunting = $vendor;
        $this->ubah = true;

        /*
         | Lewat /rincian, karena Orcha tidak punya GET /vendor/{id} satuan —
         | yang ada hanya daftar dan rincian. Rinciannya memang membawa jejak
         | belanja yang tidak dipakai borang ini, tetapi menambah jalur baru
         | di Orcha demi satu objek yang sudah ada di sana lebih mahal
         | daripada membaca satu kunci lebih dalam.
         */
        try {
            $data = $this->orcha()->ambil("/vendor/{$vendor}/rincian")['data']['vendor'] ?? [];
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        if ($data === [] || ! isset($data['nama'])) {
            return;
        }

        $this->namaTersimpan = (string) ($data['nama'] ?? '');
        $this->isiDari($data);
    }

    public function tujuanKembali(): string
    {
        return $this->dari === 'detail' && $this->vendorId
            ? route('admin.orcha.vendor.detail', $this->vendorId)
            : route('admin.orcha.vendor');
    }

    public function simpan(): void
    {
        $data = $this->periksaVendor();

        try {
            $balasan = $this->sunting
                ? $this->orcha()->ubah("/vendor/{$this->sunting}", $data)
                : $this->orcha()->kirim('/vendor', $data);
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());

            return;
        }

        /*
         | Daftar vendor ikut disimpan di katalog RAB. Tanpa dilupakan, vendor
         | yang baru dibuat tidak muncul di kotak pilih master harga dan
         | tampak seperti gagal tersimpan.
         */
        cache()->forget('orcha.rab.katalog');

        $this->dispatch('orcha-sukses-pindah',
            message: $balasan['pesan'] ?? 'Vendor disimpan.',
            url: $this->tujuanKembali());
    }

    /**
     * Provinsi yang sudah dipakai vendor lain, untuk saran isian.
     *
     * Disarankan, bukan dikunci: vendor nyata muncul di provinsi yang belum
     * pernah ada di daftar, dan menolaknya hanya memaksa admin mengetik di
     * kolom lain. Gagal memuatnya pun tidak boleh mematikan borang —
     * kehilangan saran jauh lebih murah daripada kehilangan halamannya.
     *
     * @return array<int, string>
     */
    private function provinsiAda(): array
    {
        try {
            return $this->orcha()->ambil('/vendor', ['per_halaman' => 1])['meta']['ringkas']['provinsi'] ?? [];
        } catch (OrchaTidakTerjangkau) {
            return [];
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.vendor.borang', [
            'pilihanJenis' => self::JENIS,
            'provinsiAda' => $this->provinsiAda(),
        ])->layout('livewire.layout.templateindex');
    }
}
