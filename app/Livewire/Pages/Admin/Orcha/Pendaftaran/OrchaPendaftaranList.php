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
     * Saringan "perlu ditagih": sudah bayar DP, tanggalnya sudah dekat.
     *
     * Ini daftar yang perlu dikejar orang. Pengingat otomatis sudah mengurus
     * yang bergerak setelah membaca suratnya; yang tersisa di sini justru yang
     * TIDAK bergerak — dan itu cuma bisa diselesaikan lewat telepon.
     *
     * Sebelum ada saringan ini, satu-satunya cara menemukannya adalah membuka
     * pendaftaran satu per satu dan menghitung tanggalnya di kepala.
     * Pekerjaannya cukup melelahkan sehingga tidak pernah benar-benar
     * dikerjakan, dan uangnya menguap tanpa ada yang menyadarinya.
     */
    public bool $perluDitagih = false;

    public function updatedPerluDitagih(): void
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
        $this->reset(['cari', 'filterStatus', 'filterPaket', 'perluDitagih']);
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterStatus !== '' || $this->filterPaket !== ''
            || $this->perluDitagih;
    }

    /** Saringan yang sedang berlaku — dipakai daftar maupun tautan manifes. */
    public function saringanTampil(): array
    {
        return array_filter([
            'cari' => $this->cari,
            'status' => $this->filterStatus,
            'paket_id' => $this->filterPaket,
            // Dikirim hanya saat menyala. Mengirim 0 membuat Orcha membaca
            // parameternya ada, dan daftar biasa ikut tersaring.
            'perlu_ditagih' => $this->perluDitagih ? 1 : '',
        ], fn ($nilai) => $nilai !== '' && $nilai !== null);
    }

    public function ubahStatus(int $id, string $status): void
    {
        $this->kirimPerubahan("/pendaftaran/{$id}/status", ['status' => $status], 'Status pendaftaran diperbarui di Orcha.');
    }

    public function render()
    {
        $hasil = $this->muat('/pendaftaran', $this->parameterDaftar(array_filter([
            'paket_id' => $this->filterPaket,
            'perlu_ditagih' => $this->perluDitagih ? 1 : null,
        ], fn ($nilai) => $nilai !== null && $nilai !== '')));

        return view('livewire.pages.admin.orcha.pendaftaran.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanStatus' => $this->rujukan('status_pendaftaran'),
            'pilihanPaket' => $this->rujukan('paket_wisata'),
        ])->layout('livewire.layout.templateindex');
    }
}
