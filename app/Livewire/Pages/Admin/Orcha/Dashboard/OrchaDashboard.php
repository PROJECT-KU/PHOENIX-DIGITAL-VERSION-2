<?php

namespace App\Livewire\Pages\Admin\Orcha\Dashboard;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

class OrchaDashboard extends Component
{
    use MemanggilOrcha;

    public function segarkan(): void
    {
        cache()->forget('orcha.perlu-ditindak');
        cache()->forget('orcha.rujukan');
    }

    /**
     * Enam bulan terakhir, bulan tanpa transaksi diisi nol.
     *
     * /keuntungan hanya mengirim bulan yang ADA isinya. Pada armada yang baru
     * jalan itu berarti satu titik — dan grafik satu titik bukan grafik, ia
     * cuma noktah yang tidak memberi tahu naik atau turun. Lebih buruk lagi,
     * penjagaan "gambar bila lebih dari satu bulan" membuatnya tidak tergambar
     * sama sekali, dan admin bertanya di mana grafiknya.
     *
     * Jendelanya disamakan dengan grafik tren di atasnya supaya keduanya bisa
     * dibaca berpasangan: bulan yang sama, di sumbu yang sama.
     *
     * @param  array<int|string, array<string, mixed>>  $perBulan
     * @return array<int, array<string, mixed>>
     */
    private function enamBulan(array $perBulan): array
    {
        /*
         | Laporan yang TIDAK terkirim berbeda dari laporan yang isinya nol.
         |
         | Tanpa penjagaan ini, Orcha yang sedang tidak bisa dihubungi
         | menghasilkan grafik enam bulan rata di angka nol — dan itu berbohong:
         | ia menyatakan omzetnya memang nol, padahal yang terjadi datanya tidak
         | sampai. Yang benar: tidak menggambar apa pun.
         */
        if ($perBulan === []) {
            return [];
        }

        $isi = collect($perBulan)->keyBy('bulan');
        $rupiah = fn (int $n) => 'Rp '.number_format($n, 0, ',', '.');
        $hasil = [];

        foreach (range(5, 0) as $mundur) {
            $saat = now()->startOfMonth()->subMonths($mundur);
            $kunci = $saat->format('Y-m');
            $ada = $isi->get($kunci);

            $hasil[] = $ada ?: [
                'bulan' => $kunci,
                'bulan_label' => $saat->locale('id')->translatedFormat('M Y'),
                'omzet' => 0,
                'modal' => 0,
                'keuntungan' => 0,
                'omzet_teks' => $rupiah(0),
                'keuntungan_teks' => $rupiah(0),
            ];
        }

        return $hasil;
    }

    public function render()
    {
        $isi = $this->muat('/dashboard')['data'] ?? [];

        /*
         | Uang diambil dari /keuntungan, BUKAN dihitung ulang di sini.
         |
         | Laporan itulah yang dibaca admin di halaman Keuntungan Paket, dan
         | dua tempat yang menghitung omzet sendiri-sendiri akan berbeda
         | angkanya suatu saat — biasanya tepat ketika ada yang menanyakannya.
         |
         | Gagalnya diam: dashboard tanpa grafik uang masih berguna, dashboard
         | yang roboh tidak. Orcha yang belum mengirimnya menghasilkan kartu
         | uang yang tidak digambar, bukan halaman galat.
         */
        $untung = [];

        try {
            $untung = $this->orcha()->ambil('/keuntungan')['data'] ?? [];
        } catch (\Throwable) {
            $untung = [];
        }

        /*
         | Yang dikirim ke tampilan hanya yang benar-benar digambar.
         |
         | Dashboard sempat memuat delapan blok sekaligus — dua grafik enam
         | bulan, rincian paket per kategori, rincian armada per jenis, dan
         | bilah isi etalase — sehingga membacanya menuntut memutuskan sendiri
         | mana yang penting. Yang tersisa menjawab tiga pertanyaan saja: apa
         | yang harus dikerjakan sekarang, bagaimana uangnya, dan apa yang baru
         | masuk.
         |
         | 'kartu', 'paket_per_kategori', 'kendaraan_per_jenis', dan
         | 'tren_bulanan' sengaja tidak diteruskan lagi. Isinya tetap dikirim
         | Orcha di jawaban /dashboard yang sama, jadi menghidupkannya kembali
         | cukup menambahkan barisnya di sini.
         */
        return view('livewire.pages.admin.orcha.dashboard.index', [
            'perluDitindak' => $isi['perlu_ditindak'] ?? [],
            'pendaftaranTerbaru' => $isi['pendaftaran_terbaru'] ?? [],
            'penyewaanTerbaru' => $isi['penyewaan_terbaru'] ?? [],
            'uang' => $untung['ringkasan'] ?? [],
            'uangPerBulan' => $this->enamBulan($untung['per_bulan'] ?? []),
        ])->layout('livewire.layout.templateindex');
    }
}
