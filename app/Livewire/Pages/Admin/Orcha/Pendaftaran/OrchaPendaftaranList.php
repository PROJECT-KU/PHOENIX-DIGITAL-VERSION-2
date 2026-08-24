<?php

namespace App\Livewire\Pages\Admin\Orcha\Pendaftaran;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaPendaftaranList extends Component
{
    use MemanggilOrcha;

    /**
     * Saringan paket.
     *
     * Ada karena manifes tour leader dibentuk dari daftar yang sedang tampil,
     * dan satu keberangkatan hampir selalu berarti satu paket. Sebelum ini
     * admin harus mengetik nama paketnya di kotak cari lalu berharap tidak ada
     * paket lain yang namanya mirip — dan manifes yang kelebihan satu rombongan
     * baru ketahuan di lapangan.
     */
    public string $filterPaket = '';

    public function updatedFilterPaket(): void
    {
        $this->halaman = 1;
    }

    /**
     * Saringan paket ikut dikosongkan.
     *
     * Menimpa milik trait: halaman ini punya saringan ketiga, dan tombol yang
     * menyisakan satu saringan hidup justru membingungkan — daftarnya tetap
     * terpotong padahal admin merasa sudah membersihkan semuanya.
     */
    public function bersihkanSaringan(): void
    {
        $this->reset(['cari', 'filterStatus', 'filterPaket']);
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterStatus !== '' || $this->filterPaket !== '';
    }

    /** Saringan yang sedang berlaku — dipakai daftar maupun tautan manifes. */
    public function saringanTampil(): array
    {
        return array_filter([
            'cari' => $this->cari,
            'status' => $this->filterStatus,
            'paket_id' => $this->filterPaket,
        ], fn ($nilai) => $nilai !== '' && $nilai !== null);
    }

    public function ubahStatus(int $id, string $status): void
    {
        $this->kirimPerubahan("/pendaftaran/{$id}/status", ['status' => $status], 'Status pendaftaran diperbarui di Orcha.');
    }

    public function render()
    {
        $hasil = $this->muat('/pendaftaran', $this->parameterDaftar(['paket_id' => $this->filterPaket]));

        return view('livewire.pages.admin.orcha.pendaftaran.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanStatus' => $this->rujukan('status_pendaftaran'),
            'pilihanPaket' => $this->rujukan('paket_wisata'),
        ])->layout('livewire.layout.templateindex');
    }
}
