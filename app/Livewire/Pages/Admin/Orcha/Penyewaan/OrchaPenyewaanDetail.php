<?php

namespace App\Livewire\Pages\Admin\Orcha\Penyewaan;

use App\Livewire\Pages\Admin\Orcha\Concerns\BagianUnit;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Component;

/**
 * Satu penyewaan, selengkapnya.
 *
 * Daftar sewa menjawab "siapa yang menyewa apa". Halaman ini menjawab yang
 * ditanyakan sesudahnya, dan biasanya saat unitnya sedang berdiri di depan
 * loket: jam berapa seharusnya kembali, sudah telat berapa lama, bagian mana
 * yang rusak dibanding saat diserahkan, dan berapa yang harus ditagih.
 */
class OrchaPenyewaanDetail extends Component
{
    use BagianUnit;
    use MemanggilOrcha;

    public int $penyewaanId;

    public array $data = [];

    public function mount(int $penyewaan): void
    {
        $this->penyewaanId = $penyewaan;
    }

    public function ubahStatus(string $status): void
    {
        $this->kirimPerubahan(
            "/penyewaan/{$this->penyewaanId}/status",
            ['status' => $status],
            'Status pemesanan sewa diperbarui di Orcha.'
        );
    }

    /**
     * Pesan siap kirim untuk penyewa, disusun menurut keadaan pesanannya.
     *
     * Disusun di sini, bukan di blade: nominal, tenggat, dan aturan dendanya
     * berasal dari rujukan Orcha yang sama dengan yang dipakai kwitansi — jadi
     * angka di percakapan tidak pernah berbeda dari angka di berkas.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pilihanPesan(): array
    {
        $sewa = $this->data;

        if ($sewa === []) {
            return [];
        }

        $rp = fn ($angka) => 'Rp '.number_format((int) $angka, 0, ',', '.');
        $nama = $sewa['nama'] ?? 'Kak';
        $kode = $sewa['kode'] ?? '';
        $unit = data_get($sewa, 'kendaraan.sebutan') ?: data_get($sewa, 'kendaraan.nama', 'unit');

        /*
         | Emoji ditulis sebagai TITIK KODE, bukan huruf emojinya langsung.
         |
         | Pola yang sama dengan halaman pendaftaran, dengan alasan yang sudah
         | diuji di sana: emoji rusak jadi tanda tanya dalam perjalanan lewat
         | respons server, sementara tiap pemeriksaan di sisi kami tampak bersih.
         | Penandanya dirakit jadi emoji di peramban dengan String.fromCodePoint
         | — lihat partial salin-wa.
         |
         | Bila skripnya tidak sempat jalan, penandanya dibuang begitu saja dan
         | kalimatnya tetap utuh. Kalimat lengkap tanpa emoji jauh lebih baik
         | daripada kalimat penuh tanda tanya.
         */
        $pakaiEmoji = (bool) config('orcha.emoji_wa', true);
        $e = fn (string $titikKode) => $pakaiEmoji ? "[[E:{$titikKode}]] " : '';

        $salam = 'Halo Kak '.$nama.($pakaiEmoji ? ' [[E:1F44B]]' : '')
            ."\n\nTerima kasih sudah memesan sewa *{$unit}* di Orcha Journey."
            ."\nKode pesanan Anda: *{$kode}*";
        $penutup = "\n\n".$e('1F64F')."Terima kasih\n_Orcha Journey_";

        $mulai = ($sewa['tanggal_mulai'] ?? null)
            ? \Carbon\Carbon::parse($sewa['tanggal_mulai'])->locale('id')->translatedFormat('l, j F Y')
                .' pukul '.($sewa['jam_mulai'] ?? '')
            : null;

        $tenggat = ($sewa['jadwal_selesai'] ?? null)
            ? \Carbon\Carbon::parse($sewa['jadwal_selesai'])->locale('id')
                ->translatedFormat('l, j F Y').' pukul '.\Carbon\Carbon::parse($sewa['jadwal_selesai'])->format('H:i')
            : null;

        $tagihan = $sewa['tagihan'] ?? [];
        $konfirmasi = $sewa['konfirmasi_pembayaran_tautan'] ?? null;
        $pilihan = [];

        // 1. Ringkasan pesanan. Selalu berguna: inilah yang dicocokkan penyewa
        //    dengan ingatannya sebelum hari H.
        $pilihan[] = [
            'kunci' => 'rincian',
            'judul' => 'Kirim rincian pesanan',
            'ringkas' => 'Unit, jadwal, titik jemput, dan tenggat kembali',
            'ikon' => 'bi-truck-front',
            'rupa' => 'orcha-ikon-netral',
            'pesan' => $salam
                ."\n\n".$e('1F697').'*Unit:* '.$unit
                .($mulai ? "\n".$e('1F4C5').'*Mulai:* '.$mulai : '')
                .($tenggat ? "\n".$e('23F0').'*Ditunggu kembali:* '.$tenggat : '')
                .($sewa['durasi_label'] ?? null ? "\n".$e('1F4CF').'*Durasi:* '.$sewa['durasi_label'] : '')
                .($sewa['lokasi_antar'] ?? null
                    ? "\n".$e('1F4CD').'*'.(($sewa['dengan_sopir'] ?? false) ? 'Titik penjemputan' : 'Lokasi pengantaran').":* {$sewa['lokasi_antar']}"
                    : '')
                .(($sewa['dengan_sopir'] ?? false) && ($sewa['tujuan'] ?? null)
                    ? "\n".$e('1F6E3').'*Tujuan:* '.$sewa['tujuan'] : '')
                ."\n\n".$e('1F4B0').'*Biaya sewa:* '.$rp($sewa['estimasi_biaya'] ?? 0)
                .$this->kalimatTermasuk()
                .$penutup,
        ];

        // 2. Tagihan. Hanya bila masih ada yang harus dibayar — menagih orang
        //    yang sudah lunas adalah cara tercepat kehilangan kepercayaannya.
        if ($tagihan !== [] && ! ($tagihan['lunas'] ?? false)) {
            $sisa = (int) ($tagihan['sisa'] ?? 0);
            $sudah = (int) ($tagihan['sudah'] ?? 0);

            $pilihan[] = [
                'kunci' => 'tagihan',
                'judul' => $sudah > 0 ? 'Tagih sisa pembayaran' : 'Tagih pembayaran',
                'ringkas' => $sudah > 0
                    ? 'Sisa '.$rp($sisa).' · membawa tautan kirim bukti'
                    : $rp($sisa).' · membawa tautan kirim bukti',
                'ikon' => 'bi-cash-coin',
                'rupa' => 'orcha-ikon-awas',
                'pesan' => $salam
                    ."\n\n".$e('1F4B3').'*Total sewa:* '.$rp($tagihan['total'] ?? 0)
                    .($sudah > 0 ? "\n".$e('2705').'*Sudah diterima:* '.$rp($sudah) : '')
                    ."\n".$e('1F534').'*Yang perlu ditransfer:* '.$rp($sisa)
                    ."\n\nPembayaran hanya sah ke rekening atas nama *PT ASTHANA CIPTA MANDIRI*. "
                    .'Nomor rekeningnya kami kirim lewat percakapan ini.'
                    .($konfirmasi
                        ? "\n\n".$e('1F4E4')."Setelah transfer, mohon kirim buktinya lewat tautan berikut:\n{$konfirmasi}"
                        : '')
                    .$penutup,
            ];
        }

        // 3. Pengingat pengembalian. Hanya selama unit masih di tangan penyewa;
        //    sesudah kembali, pengingat ini hanya membingungkan.
        if (($sewa['diserahkan_pada'] ?? null) && ! ($sewa['dikembalikan_pada'] ?? null) && $tenggat) {
            $aturan = $this->rujukan('denda_sewa');
            $tenggang = (int) ($aturan['tenggang_menit'] ?? 0);
            $persen = (int) ($aturan['persen_tarif_harian_per_jam'] ?? 0);

            $pilihan[] = [
                'kunci' => 'pengingat',
                'judul' => 'Ingatkan jadwal pengembalian',
                'ringkas' => 'Jam kembali dan aturan keterlambatan',
                'ikon' => 'bi-alarm',
                'rupa' => 'orcha-ikon-awas',
                'pesan' => $salam
                    ."\n\n".$e('23F0').'Unit ditunggu kembali *'.$tenggat.'*'
                    .(($sewa['lokasi_kembali'] ?? null) ? ' di '.$sewa['lokasi_kembali'] : '').'.'
                    .($tenggang > 0 && $persen > 0
                        ? "\n\n".$e('26A0')."Ada tenggang *{$tenggang} menit*. Lewat dari itu dikenakan denda "
                            ."keterlambatan *{$persen}% tarif harian per jam*."
                        : '')
                    ."\n\nBila perlu perpanjangan, mohon kabari kami sebelum jam tersebut supaya bisa kami atur."
                    .$penutup,
            ];
        }

        // 4. Tagihan denda. Hanya sesudah unit diperiksa DAN dendanya ditetapkan
        //    — usulan yang belum disimpan bukan tagihan, dan mengirimkannya
        //    berarti menagih angka yang belum tentu jadi.
        if (($sewa['dikembalikan_pada'] ?? null) && (int) ($sewa['total_denda'] ?? 0) > 0) {
            $baris = '';

            foreach ([
                ['Keterlambatan', $sewa['denda_keterlambatan'] ?? 0],
                ['Kerusakan', $sewa['denda_kerusakan'] ?? 0],
                ['Lain-lain', $sewa['denda_lain'] ?? 0],
            ] as [$label, $nilai]) {
                if ((int) $nilai > 0) {
                    $baris .= "\n· {$label}: ".$rp($nilai);
                }
            }

            $pilihan[] = [
                'kunci' => 'denda',
                'judul' => 'Kirim tagihan denda',
                'ringkas' => 'Rincian per jenis, '.$rp($sewa['total_denda']),
                'ikon' => 'bi-exclamation-triangle',
                'rupa' => 'orcha-ikon-bahaya',
                'pesan' => $salam
                    ."\n\nUnit sudah kembali dan selesai kami periksa. Ada denda yang perlu kami sampaikan:"
                    .$baris
                    ."\n\n".$e('1F4B0').'*Total denda:* '.$rp($sewa['total_denda'])
                    .(($sewa['catatan_denda'] ?? null) ? "\n\n".$e('1F4DD').'*Catatan:* '.$sewa['catatan_denda'] : '')
                    ."\n\nBila menurut Anda ada yang tidak sesuai, mohon hubungi kami sebelum membayar — "
                    .'hasil pemeriksaan saat unit diserahkan kami simpan dan bisa dibandingkan.'
                    .$penutup,
            ];
        }

        // 5. Terima kasih. Untuk unit yang kembali bersih; pesan yang paling
        //    jarang dikirim padahal paling murah dampaknya.
        if (($sewa['dikembalikan_pada'] ?? null) && (int) ($sewa['total_denda'] ?? 0) === 0) {
            $pilihan[] = [
                'kunci' => 'selesai',
                'judul' => 'Ucapkan terima kasih',
                'ringkas' => 'Unit kembali tanpa denda',
                'ikon' => 'bi-emoji-smile',
                'rupa' => 'orcha-ikon-aman',
                'pesan' => $salam
                    ."\n\n".$e('2705').'Unit sudah kembali dan diperiksa — tidak ada denda. '
                    .'Terima kasih sudah menjaga kendaraannya.'
                    ."\n\nSemoga perjalanannya menyenangkan, dan kami tunggu pesanan berikutnya."
                    .$penutup,
            ];
        }

        /*
         | Tiap pilihan membawa dua bentuk pesan.
         |
         | 'pesan' berpenanda, dipakai skrip untuk merakit emoji dan menyalin ke
         | papan tempel. 'polos' tanpa penanda, dipakai href sebagai cadangan
         | bila skripnya tidak sempat jalan.
         */
        return array_map(function ($satu) {
            $satu['polos'] = trim(preg_replace('/[ \t]+$/m', '',
                preg_replace('/\[\[E:[0-9A-F]+\]\] ?/', '', $satu['pesan'])));

            return $satu;
        }, $pilihan);
    }

    /**
     * Satu kalimat tentang pos biaya yang ditanggung penyewa.
     *
     * Yang tidak tertulis di mana pun paling mudah dipersoalkan saat menagih,
     * dan BBM adalah pos yang paling sering diperselisihkan.
     */
    private function kalimatTermasuk(): string
    {
        $daftar = collect(data_get($this->data, 'kendaraan.termasuk') ?: []);

        if ($daftar->isEmpty()) {
            return '';
        }

        $ditanggung = $daftar->reject(fn ($pos) => $pos['termasuk'])->pluck('label');

        return $ditanggung->isEmpty()
            ? "\n_Sudah termasuk ".$daftar->pluck('label')->implode(', ').'._'
            : "\n_Sudah termasuk ".$daftar->filter(fn ($pos) => $pos['termasuk'])->pluck('label')->implode(', ')
                .'. '.$ditanggung->implode(' dan ').' ditanggung penyewa._';
    }

    /** Tautan WhatsApp lewat api.whatsapp.com, bukan wa.me. */
    public function tautanWa(string $pesan): string
    {
        return \App\Support\TautanWa::kirim($this->data['whatsapp'] ?? null, $pesan);
    }

    public function render()
    {
        $hasil = $this->muat("/penyewaan/{$this->penyewaanId}");
        $this->data = $hasil['data'] ?? [];

        return view('livewire.pages.admin.orcha.penyewaan.detail', [
            'sewa' => $this->data,
            'pilihanStatus' => $this->rujukan('status_penyewaan'),
            // Bagian yang berlaku untuk unit ini, bukan seluruh daftar:
            // sewa bus yang menampilkan dua belas baris "—" untuk bagian mobil
            // membuat yang benar-benar diperiksa tenggelam di antaranya.
            'bagianPeriksa' => $this->bagianUntukUnit(
                data_get($this->data, 'kendaraan.jenis'),
                (array) ($this->data['kondisi_awal'] ?? []),
                (array) ($this->data['kondisi_akhir'] ?? []),
            ),
            'pilihanKondisi' => $this->rujukan('kondisi_pemeriksaan'),
        ])->layout('livewire.layout.templateindex');
    }
}
