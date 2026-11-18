<?php

namespace App\Livewire\Pages\Admin\Orcha\KeuntunganSewa;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Keuntungan sewa kendaraan: selisih harga sewa dan modal ke vendor.
 *
 * Layar kembaran dari Keuntungan Paket, dan sengaja TIDAK digabung ke sana.
 * Keduanya menjawab pertanyaan yang sama bunyinya tetapi beda isinya: paket
 * dihitung per peserta dengan kategori dan paket sebagai pemilah, sewa
 * dihitung per pesanan dengan unit dan vendor sebagai pemilah. Satu layar
 * untuk keduanya berarti separuh saringannya selalu tidak berlaku.
 *
 * Yang paling penting di layar ini bukan angka untungnya, melainkan angka
 * "belum dicatat modalnya": selama ia belum nol, untung di atasnya masih
 * kurang dari yang sebenarnya — dan itu harus kelihatan, bukan disembunyikan
 * demi rekap yang terlihat rapi.
 */
class OrchaKeuntunganSewaList extends Component
{
    use MemanggilOrcha;

    public string $dari = '';

    public string $sampai = '';

    public string $vendorId = '';

    /** 'mulai' = sewa yang JALAN di rentang ini; 'dibuat' = yang DIPESAN. */
    public string $dasar = 'mulai';

    /** Rentang siap pakai — mengetik dua tanggal untuk "bulan ini" itu kerja sia-sia. */
    public function pilihRentang(string $nama): void
    {
        [$this->dari, $this->sampai] = match ($nama) {
            'bulan-ini' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'bulan-lalu' => [
                now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                now()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
            'tahun-ini' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            default => ['', ''],
        };

        $this->halaman = 1;
    }

    public function rentangAktif(): string
    {
        return match ([$this->dari, $this->sampai]) {
            [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()] => 'bulan-ini',
            [
                now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
                now()->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ] => 'bulan-lalu',
            [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()] => 'tahun-ini',
            ['', ''] => 'semua',
            default => '',
        };
    }

    public function updatedDari(): void
    {
        $this->halaman = 1;
    }

    public function updatedSampai(): void
    {
        $this->halaman = 1;
    }

    public function updatedVendorId(): void
    {
        $this->halaman = 1;
    }

    public function updatedDasar(): void
    {
        $this->halaman = 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function saringan(): array
    {
        return array_filter([
            'dari' => $this->dari,
            'sampai' => $this->sampai,
            'vendor_id' => $this->vendorId,
            'dasar' => $this->dasar,
        ], fn ($nilai) => $nilai !== '' && $nilai !== null);
    }

    public function render()
    {
        $laporan = $this->muat('/keuntungan-sewa', $this->saringan())['data'] ?? [];

        // Rincian hanya diminta bila laporannya sendiri berhasil: Orcha yang
        // sedang mati akan gagal dua kali dan menimpa pesan galat pertama
        // dengan pesan kedua yang bunyinya sama.
        $rincian = $this->galat === ''
            ? $this->muat('/keuntungan-sewa/rincian', $this->saringan() + [
                'page' => $this->halaman,
                'per_halaman' => $this->perHalaman(),
            ])
            : ['data' => [], 'meta' => []];

        return view('livewire.pages.admin.orcha.keuntungan-sewa.index', [
            'ringkas' => $laporan['ringkas'] ?? [],
            'perUnit' => $laporan['per_unit'] ?? [],
            'perVendor' => $laporan['per_vendor'] ?? [],
            'daftarVendor' => $laporan['vendor'] ?? [],
            'pilihanDasar' => $laporan['dasar_tanggal'] ?? [],
            'rincian' => $rincian['data'] ?? [],
            'meta' => $rincian['meta'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
