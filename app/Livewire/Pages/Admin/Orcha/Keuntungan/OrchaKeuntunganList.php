<?php

namespace App\Livewire\Pages\Admin\Orcha\Keuntungan;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Keuntungan paket wisata Orcha: selisih harga jual dan modal, per peserta.
 *
 * Halaman tersendiri, bukan satu kotak tambahan di dashboard. Pertanyaan yang
 * dijawab di sini berbeda dari pertanyaan dashboard: bukan "apa yang harus
 * saya kerjakan sekarang", melainkan "trip mana yang benar-benar menghasilkan"
 * — dan jawabannya perlu rentang tanggal, rekap per paket, serta rincian yang
 * bisa ditelusuri sampai ke satu pendaftaran.
 *
 * Seluruh hitungannya di sisi Orcha. Lemon tidak pernah menghitung ulang
 * marginnya sendiri: dua tempat menghitung hal yang sama adalah dua angka yang
 * cepat atau lambat berbeda, dan yang berbeda soal uang tidak akan dipercaya
 * lagi keduanya.
 */
class OrchaKeuntunganList extends Component
{
    use MemanggilOrcha;

    public string $dari = '';

    public string $sampai = '';

    public string $kategori = '';

    public string $paketId = '';

    /** Tanggal mana yang dipakai menyaring: 'daftar' atau 'berangkat'. */
    public string $dasar = 'daftar';

    /**
     * Rincian bawaannya menampilkan yang belum lunas juga.
     *
     * Angka besar di atas hanya menghitung yang lunas, dan kalau daftarnya ikut
     * disaring begitu, pesanan yang menggantung jadi tidak terlihat di mana
     * pun — padahal justru itu yang perlu ditindak supaya jadi keuntungan.
     */
    public bool $hanyaLunas = false;

    /** Rentang siap pakai, karena mengetik dua tanggal untuk "bulan ini" itu kerja sia-sia. */
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

    public function updatedDari(): void
    {
        $this->halaman = 1;
    }

    public function updatedSampai(): void
    {
        $this->halaman = 1;
    }

    public function updatedKategori(): void
    {
        $this->halaman = 1;
    }

    public function updatedPaketId(): void
    {
        $this->halaman = 1;
    }

    public function updatedDasar(): void
    {
        $this->halaman = 1;
    }

    public function updatedHanyaLunas(): void
    {
        $this->halaman = 1;
    }

    /** Rentang yang sedang dipakai, untuk menandai tombol yang aktif. */
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

    /**
     * Persentase bulat yang jumlahnya PASTI 100.
     *
     * Membulatkan tiap porsi sendiri-sendiri tampak wajar sampai angkanya
     * dijumlahkan: 62% + 24% + 15% = 101%, tepat di bawah kalimat yang
     * menjanjikan seratus. Bagi admin yang tidak menghitung ulang apa pun,
     * selisih satu persen itu bukan hal sepele — ia satu-satunya petunjuk yang
     * ia punya bahwa angka di halaman ini boleh dipercaya.
     *
     * Cara membaginya: semua dibulatkan ke bawah dulu, lalu sisa persennya
     * diberikan satu per satu kepada yang pecahannya paling besar. Yang
     * dikorbankan hanya ketelitian di bawah satu persen — dan itu memang sudah
     * hilang begitu angkanya dibulatkan.
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @return array<array-key, int> kunci baris => persen
     */
    public function porsiBulat(array $baris): array
    {
        $nilai = [];

        foreach ($baris as $satu) {
            // Yang rugi tidak menyumbang porsi apa pun; porsi negatif tidak
            // punya arti dalam pembagian seratus persen.
            $nilai[$satu['kunci']] = max(0, (int) $satu['keuntungan']);
        }

        $total = array_sum($nilai);

        if ($total <= 0) {
            return array_map(fn () => 0, $nilai);
        }

        $persen = [];
        $pecahan = [];

        foreach ($nilai as $kunci => $angka) {
            $tepat = $angka / $total * 100;
            $persen[$kunci] = (int) floor($tepat);
            $pecahan[$kunci] = $tepat - floor($tepat);
        }

        $sisa = 100 - array_sum($persen);

        arsort($pecahan);

        foreach (array_keys($pecahan) as $kunci) {
            if ($sisa <= 0) {
                break;
            }

            // Baris yang porsinya benar-benar nol tidak ikut kebagian sisa:
            // "0%" yang tiba-tiba jadi "1%" pada baris yang rugi lebih
            // membingungkan daripada jumlah yang meleset.
            if ($nilai[$kunci] <= 0) {
                continue;
            }

            $persen[$kunci]++;
            $sisa--;
        }

        return $persen;
    }

    private function saringan(): array
    {
        return array_filter([
            'dari' => $this->dari,
            'sampai' => $this->sampai,
            'kategori' => $this->kategori,
            'paket_id' => $this->paketId,
            'dasar' => $this->dasar,
        ], fn ($nilai) => $nilai !== '' && $nilai !== null);
    }

    public function render()
    {
        $laporan = $this->muat('/keuntungan', $this->saringan())['data'] ?? [];

        // Rincian hanya diminta bila laporannya sendiri berhasil. Orcha yang
        // sedang mati akan gagal dua kali dan menimpa pesan galat yang pertama
        // dengan pesan kedua yang bunyinya sama.
        $rincian = $this->galat === ''
            ? $this->muat('/keuntungan/rincian', $this->saringan() + [
                'hanya_lunas' => $this->hanyaLunas ? 1 : 0,
                'page' => $this->halaman,
                'per_halaman' => $this->perHalaman(),
            ])
            : ['data' => [], 'meta' => []];

        return view('livewire.pages.admin.orcha.keuntungan.index', [
            'ringkasan' => $laporan['ringkasan'] ?? [],
            'perPaket' => $laporan['per_paket'] ?? [],
            'perKategori' => $laporan['per_kategori'] ?? [],
            'perBulan' => $laporan['per_bulan'] ?? [],
            'daftarPaket' => $laporan['paket'] ?? [],
            'pilihanDasar' => $laporan['dasar_tanggal'] ?? [],
            'pilihanKategori' => $this->rujukan('kategori_paket'),
            'rincian' => $rincian['data'] ?? [],
            'meta' => $rincian['meta'] ?? [],
        ])->layout('livewire.layout.templateindex');
    }
}
