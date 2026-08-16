<?php

namespace App\Livewire\Pages\Admin\Orcha\Penyewaan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrchaPenyewaanList extends Component
{
    use MemanggilOrcha;
    use WithFileUploads;

    /** Foto berkas jaminan penyewa (KTP/SIM) yang baru dipilih admin. */
    public $berkasJaminan;

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

    /**
     * Biaya tiap bagian yang rusak, bisa disunting satu per satu.
     *
     * Daftar tarif hanya perkiraan; harga bengkel yang sebenarnya berbeda tiap
     * kejadian. Kalau admin hanya bisa mengubah totalnya, rincian yang
     * ditunjukkan ke penyewa jadi tidak cocok dengan angka yang ditagih — dan
     * rincian yang tidak cocok lebih buruk daripada tidak ada rincian.
     *
     * @var array<string, string> bagian => biaya
     */
    public array $biayaKerusakan = [];

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
        // Kolom "saat diserahkan" diisikan dari kondisi terakhir unit yang
        // tercatat. Tanpa ini admin mengetik ulang daftar lecet lama setiap
        // kali unit disewakan — dan yang lupa diketik akan tertagih ke penyewa
        // berikutnya sebagai kerusakan baru.
        $this->kondisiAwal = $baris['kondisi_awal'] ?: ($baris['kondisi_unit_terkini'] ?? []);
        $this->kondisiAkhir = $baris['kondisi_akhir'] ?: [];

        // Keduanya terisi dengan usulan sistem supaya admin melanjutkan, bukan
        // menaksir dari nol. Angka yang sudah pernah disimpan tidak ditimpa.
        $this->dendaKeterlambatan = $baris['denda_keterlambatan'] ?: ($baris['denda_keterlambatan_usulan'] ?? 0);
        // Tiap bagian yang rusak punya barisnya sendiri, terisi tarif usulan
        // dan bisa disunting. Totalnya mengikuti jumlah baris-baris itu.
        // Dikunci dengan kunci bagian (bodi_kiri), bukan namanya ("Bodi
        // samping kiri"). Nama bagian mengandung spasi dan "&", dan Livewire
        // tidak bisa mengikat isian ke kunci seperti itu — ketikan admin hilang
        // begitu isiannya ditinggalkan, persis seperti yang terjadi kemarin.
        $this->biayaKerusakan = collect($baris['rincian_denda_kerusakan'] ?? [])
            ->mapWithKeys(fn ($satu) => [
                $satu['kunci'] ?? \Illuminate\Support\Str::slug($satu['bagian'], '_') => number_format((int) $satu['biaya'], 0, ',', '.'),
            ])
            ->all();

        $this->dendaKerusakan = $baris['denda_kerusakan'] ?: ($baris['denda_kerusakan_usulan'] ?? 0);
        $this->dendaLain = $baris['denda_lain'] ?? 0;
        $this->catatanDenda = $baris['catatan_denda'] ?: $this->rangkumKerusakan($baris);

        $this->rapikanRupiah();
    }

    /**
     * Menandai unit kembali sekarang juga.
     *
     * Serah terima dicatat saat unitnya benar-benar ada di depan admin, jadi
     * "sekarang" adalah jawaban yang benar hampir setiap kali. Mengetik tanggal
     * dan jam sendiri hanya menambah peluang salah ketik — dan salah ketik di
     * sini berarti denda keterlambatan yang salah.
     */
    public function kembaliSekarang(): void
    {
        $this->dikembalikanPada = now()->format('Y-m-d\TH:i');
    }

    /**
     * Menyimpan foto berkas jaminan penyewa.
     *
     * Dikirim terpisah dari lembar serah terima, jadi admin bisa memotret KTP
     * saat unit diserahkan tanpa harus menunggu seluruh lembarnya lengkap.
     * Isian bertipe berkas juga tidak bisa ikut di badan JSON yang sama.
     */
    public function simpanJaminan(): void
    {
        if (! $this->serahTerimaUntuk || ! $this->berkasJaminan) {
            return;
        }

        $this->validate([
            'berkasJaminan' => 'image|max:8192',
        ], [], ['berkasJaminan' => 'foto berkas jaminan']);

        $berhasil = $this->kirimData(
            "/penyewaan/{$this->serahTerimaUntuk}/berkas-jaminan",
            [],
            'Foto berkas jaminan tersimpan di Orcha.',
            $this->berkasJaminan,
        );

        if ($berhasil) {
            $this->reset('berkasJaminan');
        }
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
            'denda_keterlambatan' => $this->angka($this->dendaKeterlambatan),
            'denda_kerusakan' => $this->angka($this->dendaKerusakan),
            'denda_lain' => $this->angka($this->dendaLain),
            'catatan_denda' => $this->catatanDenda ?: null,
        ], 'Catatan serah terima kendaraan tersimpan di Orcha.');

        $this->tutup();
    }

    /** Bentuk yang diterima isian datetime-local di peramban. */
    private function waktuIsian(?string $waktu): string
    {
        return $waktu ? \Carbon\Carbon::parse($waktu)->format('Y-m-d\TH:i') : '';
    }

    /**
     * Catatan denda yang sudah setengah jadi.
     *
     * Isinya bagian mana yang memburuk berikut biayanya — kalimat itu yang
     * dibacakan ke penyewa saat menagih, dan mengetiknya ulang dari ceklis
     * yang baru saja diisi hanya membuang waktu.
     */
    private function rangkumKerusakan(array $baris): string
    {
        $rincian = $baris['rincian_denda_kerusakan'] ?? [];

        if ($rincian === []) {
            return '';
        }

        return collect($rincian)
            ->map(fn ($satu) => $satu['bagian'].' ('.strtolower($satu['dari']).' → '
                .strtolower($satu['jadi']).') Rp '.number_format($satu['biaya'], 0, ',', '.'))
            ->implode("\n");
    }

    /**
     * Rupiah dibaca dan ditulis bertitik.
     *
     * Angka denda di sini bisa berjuta-juta; "1500000" tanpa pemisah gampang
     * dibaca keliru satu digit, dan salah satu digit di kolom denda berarti
     * salah tagih sepuluh kali lipat.
     */
    public function updatedDendaKeterlambatan(): void
    {
        $this->rapikanRupiah();
    }

    public function updatedDendaKerusakan(): void
    {
        $this->rapikanRupiah();
    }

    /**
     * Mengubah biaya satu bagian berarti totalnya ikut berubah.
     *
     * Yang disunting admin adalah barisnya; totalnya sekadar hasil penjumlahan
     * — tidak ada tempat untuk mengetik total yang berbeda dari rinciannya.
     */
    public function updatedBiayaKerusakan(): void
    {
        $jumlah = 0;

        foreach ($this->biayaKerusakan as $bagian => $biaya) {
            $angka = $this->angka($biaya);
            $this->biayaKerusakan[$bagian] = number_format($angka, 0, ',', '.');
            $jumlah += $angka;
        }

        $this->dendaKerusakan = number_format($jumlah, 0, ',', '.');
        $this->catatanDenda = $this->rangkumDariIsian();
    }

    /** Catatan denda ditulis ulang dari baris yang sedang tampil. */
    private function rangkumDariIsian(): string
    {
        $rincian = $this->sewa['rincian_denda_kerusakan'] ?? [];

        if ($rincian === []) {
            return $this->catatanDenda;
        }

        return collect($rincian)
            ->map(function ($satu) {
                $kunci = $satu['kunci'] ?? \Illuminate\Support\Str::slug($satu['bagian'], '_');

                return $satu['bagian'].' ('.strtolower($satu['dari']).' → '.strtolower($satu['jadi']).') Rp '
                    .($this->biayaKerusakan[$kunci] ?? number_format((int) $satu['biaya'], 0, ',', '.'));
            })
            ->implode("\n");
    }

    public function updatedDendaLain(): void
    {
        $this->rapikanRupiah();
    }

    private function rapikanRupiah(): void
    {
        foreach (['dendaKeterlambatan', 'dendaKerusakan', 'dendaLain'] as $isian) {
            $angka = (int) preg_replace('/\D/', '', (string) $this->{$isian});
            $this->{$isian} = number_format($angka, 0, ',', '.');
        }
    }

    /** Angka polos untuk dikirim ke Orcha, apa pun cara admin menuliskannya. */
    private function angka($nilai): int
    {
        return (int) preg_replace('/\D/', '', (string) $nilai);
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
