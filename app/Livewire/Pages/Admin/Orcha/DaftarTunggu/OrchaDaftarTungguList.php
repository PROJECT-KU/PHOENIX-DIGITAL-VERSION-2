<?php

namespace App\Livewire\Pages\Admin\Orcha\DaftarTunggu;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Peminat yang menunggu kursi terbuka di trip yang penuh.
 *
 * Sistem sudah mengabari mereka sendiri saat kursi dilepas otomatis. Layar ini
 * untuk dua hal yang tidak bisa dikerjakan sistem:
 *
 *   1. Melihat seberapa besar permintaan yang tertahan pada satu trip — dua
 *      puluh orang mengantre di satu tanggal adalah alasan membuka
 *      keberangkatan tambahan, dan itu keputusan manusia.
 *
 *   2. Menghubungi yang tidak mencantumkan surel. Di formulir kami nomor
 *      WhatsApp yang wajib, surelnya opsional — jadi sebagian antrean memang
 *      tidak bisa dikabari lewat surat, dan hanya bisa dihubungi orang.
 */
class OrchaDaftarTungguList extends Component
{
    use MemanggilOrcha;

    public string $filterPaket = '';

    public function updatedFilterPaket(): void
    {
        $this->halaman = 1;
    }

    /**
     * Mengeluarkan satu orang dari antrean.
     *
     * Dipakai saat orangnya sudah jadi mendaftar, atau menyatakan batal lewat
     * WhatsApp. Tanpa ini antreannya cuma menumpuk, dan kabar kursi terbuka
     * dikirim ke orang yang sudah tidak menunggu.
     */
    public function keluarkan(int $id): void
    {
        try {
            $this->orcha()->hapus("/daftar-tunggu/{$id}");
            $this->dispatch('order-updated', message: 'Dikeluarkan dari daftar tunggu.');
        } catch (OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $parameter = $this->parameterDaftar();

        // Layar ini menyaring per paket, bukan per status.
        unset($parameter['status']);

        if ($this->filterPaket !== '') {
            $parameter['paket_id'] = $this->filterPaket;
        }

        $hasil = $this->muat('/daftar-tunggu', $parameter);

        return view('livewire.pages.admin.orcha.daftar-tunggu.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'paketPilihan' => $hasil['meta']['paket'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
