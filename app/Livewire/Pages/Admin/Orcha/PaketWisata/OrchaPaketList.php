<?php

namespace App\Livewire\Pages\Admin\Orcha\PaketWisata;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Daftar paket wisata Orcha.
 *
 * Datanya dari API; penambahan dan pengubahannya lewat OrchaPaketForm.
 */
class OrchaPaketList extends Component
{
    use MemanggilOrcha;

    /**
     * Saringan keadaan penayangan: tayang, terjadwal, berakhir, draf, arsip.
     *
     * Terpisah dari $filterStatus milik trait, yang di layar ini dipakai
     * untuk KATEGORI. Dua saringan berbeda yang menumpang satu properti akan
     * saling menghapus, dan yang kalah hilang tanpa galat.
     */
    #[Url(as: 'tampil', except: '')]
    public string $filterTampil = '';

    #[Url(as: 'urut', except: '')]
    public string $urut = '';

    /** Pilihan pengurutan; kuncinya dikirim apa adanya ke Orcha. */
    public const URUT = [
        '' => 'Terbaru dulu',
        'nama' => 'Nama A–Z',
        'berangkat' => 'Tanggal berangkat terdekat',
        'harga-termurah' => 'Harga termurah',
        'harga-termahal' => 'Harga termahal',
    ];

    public function updatedFilterTampil(): void
    {
        $this->halaman = 1;
    }

    public function updatedUrut(): void
    {
        $this->halaman = 1;
    }

    public function bersihkanSaringan(): void
    {
        $this->filterTampil = '';
        $this->urut = '';

        // Trait yang mengosongkan cari, kategori, dan nomor halaman.
        $this->cari = '';
        $this->filterStatus = '';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterStatus !== '' || $this->filterTampil !== '';
    }

    /** Menyaring menurut keadaan penayangan lewat cip di kepala halaman. */
    public function saringTampil(string $nilai): void
    {
        $this->filterTampil = $this->filterTampil === $nilai ? '' : $nilai;
        $this->halaman = 1;
    }

    /**
     * Orcha menolak menghapus paket yang sudah punya pendaftar. Alasannya
     * diteruskan apa adanya ke admin lewat toast.
     */
    public function hapus(int $id): void
    {
        $this->hapusData("/paket-wisata/{$id}", 'Paket wisata dihapus.');
    }

    /**
     * Menduplikat seluruh paket sebagai modal paket berikutnya.
     *
     * Salinannya lahir sebagai DRAF di Orcha — lihat PaketWisataController.
     * Yang dikerjakan di sini cuma memanggilnya dan menggambar ulang
     * daftarnya; pesannya datang dari Orcha supaya nama salinan yang
     * sebenarnya ikut tersebut.
     */
    public function salin(int $id): void
    {
        $this->kirimData(
            "/paket-wisata/{$id}/salin", [],
            'Paket disalin jadi draf baru. Ganti tanggal & harganya, lalu terbitkan.'
        );
    }

    public function render()
    {
        $parameter = $this->parameterDaftar();
        unset($parameter['status']);

        if ($this->filterStatus !== '') {
            $parameter['kategori'] = $this->filterStatus;
        }

        if ($this->filterTampil !== '') {
            $parameter['tampil'] = $this->filterTampil;
        }

        if ($this->urut !== '') {
            $parameter['urut'] = $this->urut;
        }

        $hasil = $this->muat('/paket-wisata', $parameter);

        return view('livewire.pages.admin.orcha.paket-wisata.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihan' => $this->rujukan('kategori_paket'),
            'pilihanTampil' => $this->rujukan('status_tayang'),
            'pilihanUrut' => self::URUT,
        ])->layout('livewire.layout.templateindex');
    }
}
