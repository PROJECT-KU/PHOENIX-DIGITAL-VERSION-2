<?php

namespace App\Livewire\Pages\Admin\Orcha\Penyewaan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaPenyewaanList extends Component
{
    use MemanggilOrcha;

    /** Penyewaan yang sedang dibuka lembar serah terimanya. */
    public ?int $serahTerimaUntuk = null;

    public array $sewa = [];

    public string $diserahkanPada = '';

    public string $dikembalikanPada = '';

    public $kilometerAwal = '';

    public $kilometerAkhir = '';

    public string $bahanBakarAwal = '';

    public string $bahanBakarAkhir = '';

    public string $jaminan = '';

    /** @var array<string, string> bagian => kondisi */
    public array $kondisiAwal = [];

    public array $kondisiAkhir = [];

    public $dendaKeterlambatan = 0;

    public $dendaKerusakan = 0;

    public $dendaLain = 0;

    public string $catatanDenda = '';

    public function ubahStatus(int $id, string $status): void
    {
        $this->kirimPerubahan("/penyewaan/{$id}/status", ['status' => $status], 'Status pemesanan sewa diperbarui di Orcha.');
    }

    /**
     * Membuka lembar serah terima satu unit.
     *
     * Isinya diambil dari data yang sudah ada supaya admin melanjutkan, bukan
     * mengetik ulang — termasuk denda keterlambatan yang DIUSULKAN sistem.
     * Usulan itu boleh diubah: alasan telat kadang memang di luar kuasa
     * penyewa, dan yang memutuskan tetap manusia.
     */
    public function buka(array $baris): void
    {
        $this->serahTerimaUntuk = $baris['id'];
        $this->sewa = $baris;

        $this->diserahkanPada = $this->waktuIsian($baris['diserahkan_pada'] ?? null);
        $this->dikembalikanPada = $this->waktuIsian($baris['dikembalikan_pada'] ?? null);
        $this->kilometerAwal = $baris['kilometer_awal'] ?? '';
        $this->kilometerAkhir = $baris['kilometer_akhir'] ?? '';
        $this->bahanBakarAwal = $baris['bahan_bakar_awal'] ?? '';
        $this->bahanBakarAkhir = $baris['bahan_bakar_akhir'] ?? '';
        $this->jaminan = $baris['jaminan'] ?? '';
        $this->kondisiAwal = $baris['kondisi_awal'] ?: [];
        $this->kondisiAkhir = $baris['kondisi_akhir'] ?: [];

        $this->dendaKeterlambatan = $baris['denda_keterlambatan'] ?: ($baris['denda_keterlambatan_usulan'] ?? 0);
        $this->dendaKerusakan = $baris['denda_kerusakan'] ?? 0;
        $this->dendaLain = $baris['denda_lain'] ?? 0;
        $this->catatanDenda = $baris['catatan_denda'] ?? '';
    }

    public function tutup(): void
    {
        $this->reset([
            'serahTerimaUntuk', 'sewa', 'diserahkanPada', 'dikembalikanPada',
            'kilometerAwal', 'kilometerAkhir', 'bahanBakarAwal', 'bahanBakarAkhir',
            'jaminan', 'kondisiAwal', 'kondisiAkhir',
            'dendaKeterlambatan', 'dendaKerusakan', 'dendaLain', 'catatanDenda',
        ]);
    }

    public function simpanSerahTerima(): void
    {
        if (! $this->serahTerimaUntuk) {
            return;
        }

        $this->kirimPerubahan("/penyewaan/{$this->serahTerimaUntuk}/serah-terima", [
            'diserahkan_pada' => $this->diserahkanPada ?: null,
            'dikembalikan_pada' => $this->dikembalikanPada ?: null,
            'kilometer_awal' => $this->kilometerAwal !== '' ? (int) $this->kilometerAwal : null,
            'kilometer_akhir' => $this->kilometerAkhir !== '' ? (int) $this->kilometerAkhir : null,
            'bahan_bakar_awal' => $this->bahanBakarAwal ?: null,
            'bahan_bakar_akhir' => $this->bahanBakarAkhir ?: null,
            'jaminan' => $this->jaminan ?: null,
            'kondisi_awal' => $this->kondisiAwal ?: null,
            'kondisi_akhir' => $this->kondisiAkhir ?: null,
            'denda_keterlambatan' => (int) $this->dendaKeterlambatan,
            'denda_kerusakan' => (int) $this->dendaKerusakan,
            'denda_lain' => (int) $this->dendaLain,
            'catatan_denda' => $this->catatanDenda ?: null,
        ], 'Catatan serah terima kendaraan tersimpan di Orcha.');

        $this->tutup();
    }

    /** Bentuk yang diterima isian datetime-local di peramban. */
    private function waktuIsian(?string $waktu): string
    {
        return $waktu ? \Carbon\Carbon::parse($waktu)->format('Y-m-d\TH:i') : '';
    }

    public function render()
    {
        $hasil = $this->muat('/penyewaan', $this->parameterDaftar());

        return view('livewire.pages.admin.orcha.penyewaan.index', [
            'daftar' => $hasil['data'] ?? [],
            'meta' => $hasil['meta'] ?? [],
            'pilihanStatus' => $this->rujukan('status_penyewaan'),
            'bagianPeriksa' => $this->rujukan('pemeriksaan_kendaraan'),
            'pilihanKondisi' => $this->rujukan('kondisi_pemeriksaan'),
        ])->layout('livewire.layout.templateindex');
    }
}
