<?php

namespace App\Livewire\Pages\Admin\Orcha\Pelanggan;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Orang, bukan pesanan.
 *
 * Seluruh layar Orcha lain menyusun data menurut PESANAN — satu baris satu
 * pendaftaran, satu baris satu penyewaan. Yang tidak terjawab di mana pun:
 * siapa saja yang pernah memesan, dan siapa yang sudah memesan berkali-kali.
 *
 * Pertanyaan itu muncul justru saat paling berharga: ketika ada trip baru yang
 * perlu ditawarkan, dan ketika kode rujukan perlu diberikan kepada orang yang
 * tidak mencantumkan surel — yang di formulir kami bukan minoritas, sebab
 * nomor WhatsApp yang wajib dan surelnya opsional.
 *
 * Tombol WhatsApp-nya sengaja TIDAK langsung membuka WhatsApp. Ia membuka
 * jendela dulu, dan di jendela itu kode rujukannya disiapkan beserta pesan
 * yang tinggal dikirim. Langsung membuka WhatsApp berarti admin mengetik
 * sendiri kodenya dari ingatan — dan kode yang salah satu huruf adalah komisi
 * yang tidak pernah sampai ke pemiliknya.
 */
class OrchaPelangganList extends Component
{
    use MemanggilOrcha;

    /** Nomor pelanggan yang jendelanya sedang terbuka; kosong berarti tertutup. */
    public string $buka = '';

    public array $terpilih = [];

    /** Kode yang baru saja dibuatkan, supaya jendelanya bisa langsung memakainya. */
    public string $kodeBaru = '';

    public function bukaPesan(array $baris): void
    {
        $this->terpilih = $baris;
        $this->buka = (string) $baris['whatsapp_angka'];
        $this->kodeBaru = (string) ($baris['kode_rujukan'] ?? '');
    }

    public function tutupPesan(): void
    {
        $this->buka = '';
        $this->terpilih = [];
        $this->kodeBaru = '';
    }

    /**
     * Membuatkan kode rujukan untuk orang yang sedang dibuka.
     *
     * Tombol tersendiri, bukan efek samping membuka jendelanya. Membuka layar
     * tidak boleh membuat apa pun — kalau tidak, setiap orang yang pernah
     * memesan sekali mendapat kode, termasuk yang pesanannya batal dan
     * termasuk saat admin cuma sedang mencari nomor teleponnya.
     */
    public function buatkanKode(): void
    {
        if ($this->terpilih === []) {
            return;
        }

        try {
            $hasil = $this->orcha()->kirim('/pelanggan/kode-rujukan', [
                'nama' => $this->terpilih['nama'] ?? '',
                'whatsapp' => $this->terpilih['whatsapp_angka'] ?? '',
                'email' => $this->terpilih['email'] ?? null,
            ]);

            $this->kodeBaru = (string) ($hasil['data']['kode'] ?? '');
            $this->terpilih['kode_rujukan'] = $this->kodeBaru;

            $this->dispatch('order-updated', message: 'Kode rujukan '.$this->kodeBaru.' dibuat.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $parameter = $this->parameterDaftar();

        // Layar ini menyaring per kata, bukan per status pesanan.
        unset($parameter['status']);

        $hasil = $this->muat('/pelanggan', $parameter);

        return view('livewire.pages.admin.orcha.pelanggan.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
