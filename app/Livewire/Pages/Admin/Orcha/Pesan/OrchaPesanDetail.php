<?php

namespace App\Livewire\Pages\Admin\Orcha\Pesan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Satu pesan kontak, lengkap dengan riwayat pengirimnya.
 *
 * Pertanyaan pertama admin saat membuka pesan bukan "apa isinya" — itu sudah
 * terbaca di daftar — melainkan "orang ini siapa": calon pelanggan yang baru
 * bertanya, atau pemesan yang sedang menanyakan pesanannya sendiri. Jawabannya
 * menentukan seluruh nada balasan, dan selama ini dicari sendiri dengan
 * menyalin nomornya ke kolom pencarian di halaman lain.
 */
class OrchaPesanDetail extends Component
{
    use MemanggilOrcha;

    public int $pesanId;

    public array $data = [];

    public function mount(int $id): void
    {
        $this->pesanId = $id;
    }

    /**
     * Membuka pesan berarti membacanya.
     *
     * Ditandai sendiri, bukan lewat tombol terpisah: kotak masuk yang menuntut
     * dua tindakan untuk satu perbuatan akan meninggalkan pesan "belum dibaca"
     * yang sebenarnya sudah dibaca — dan angka di dashboard ikut berbohong.
     */
    public function tandaiDibaca(): void
    {
        if ($this->data['sudah_dibaca'] ?? false) {
            return;
        }

        $this->kirimPerubahan("/pesan/{$this->pesanId}/dibaca", [], 'Pesan ditandai sudah dibaca.');
    }

    /**
     * Balasan WhatsApp yang sudah menyebut pertanyaannya.
     *
     * Pelanggan menulis berhari-hari lalu; balasan yang dibuka dengan "Halo,
     * ada yang bisa dibantu?" memaksanya mengingat sendiri apa yang ia
     * tanyakan. Kutipan singkat pertanyaannya menghemat satu putaran
     * percakapan — dan daftar isian yang mengikuti keperluannya menghemat satu
     * putaran lagi.
     *
     * Kalimatnya disusun di App\Support\BalasanPesanKontak supaya tombol WA
     * di daftar dan di halaman ini benar-benar mengirim pesan yang sama.
     */
    public function pesanWa(): string
    {
        return \App\Support\BalasanPesanKontak::untuk($this->data);
    }

    public function tautanWa(): ?string
    {
        return \App\Support\BalasanPesanKontak::tautan($this->data) ?: null;
    }

    public function render()
    {
        $this->data = $this->muat("/pesan/{$this->pesanId}")['data'] ?? [];

        return view('livewire.pages.admin.orcha.pesan.detail', [
            'pesan' => $this->data,
        ])->layout('livewire.layout.templateindex');
    }
}
