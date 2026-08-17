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
     * percakapan.
     */
    public function pesanWa(): string
    {
        $nama = $this->data['nama'] ?? 'Kak';
        $keperluan = $this->data['keperluan_label'] ?? null;
        $isi = trim((string) ($this->data['pesan'] ?? ''));
        $kutipan = mb_strlen($isi) > 120 ? mb_substr($isi, 0, 117).'…' : $isi;

        return "Halo {$nama} [[E:1F44B]]\n\n"
            .'Terima kasih sudah menghubungi *Orcha Journey*'
            .($keperluan ? " soal {$keperluan}" : '').".\n\n"
            .($kutipan ? "Menanggapi pesan Anda:\n_\"{$kutipan}\"_\n\n" : '')
            .'Ada yang bisa kami bantu jelaskan lebih dulu?';
    }

    public function tautanWa(): ?string
    {
        $tautan = \App\Support\TautanWa::kirim(
            $this->data['whatsapp'] ?? null,
            preg_replace('/\[\[E:[0-9A-F]+\]\] ?/', '', $this->pesanWa()),
        );

        return $tautan ?: null;
    }

    public function render()
    {
        $this->data = $this->muat("/pesan/{$this->pesanId}")['data'] ?? [];

        return view('livewire.pages.admin.orcha.pesan.detail', [
            'pesan' => $this->data,
        ])->layout('livewire.layout.templateindex');
    }
}
