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
    /**
     * Menandai bahwa orangnya sudah dihubungi lewat WhatsApp.
     *
     * Dipanggil dari tombol WhatsApp itu sendiri, bukan tombol tersendiri:
     * langkah tambahan yang harus diingat adalah langkah yang akhirnya
     * terlewat — dan penanda yang tidak pernah dipasang membuat antrean tampak
     * belum diurus padahal seluruhnya sudah ditelepon.
     *
     * Orangnya TIDAK dikeluarkan dari antrean: ia bisa saja menjawab "nanti
     * saya kabari lagi", dan mengeluarkannya berarti kehilangan jejaknya.
     *
     * Gagalnya DIAM. WhatsApp-nya sudah telanjur terbuka di tab sebelah oleh
     * peramban, dan pesan merah yang muncul sesudahnya tidak bisa
     * membatalkannya — yang didapat admin cuma kebingungan tentang sesuatu
     * yang sudah terjadi. Yang hilang hanya penandanya, dan itu bisa dipasang
     * lagi dengan menekan tombol yang sama.
     */
    /*
     |--------------------------------------------------------------------------
     | Menawarkan kursi yang baru kosong
     |--------------------------------------------------------------------------
     |
     | Datang dari layar Pendaftaran lewat ?tawarkan=<rentang tanggal>. Saat
     | yang orang teringat daftar tunggu adalah saat ia baru membatalkan satu
     | pendaftaran dan satu kursi menganggur — dan pada saat itu yang ia
     | butuhkan bukan daftar antrean, melainkan kalimat yang siap dikirim
     | lengkap dengan TANGGALNYA.
     |
     | Pesan tanpa tanggal ("ada kabar soal trip yang Anda tunggu") menghasilkan
     | percakapan bolak-balik yang seluruhnya tentang satu keterangan yang
     | sudah ada di tangan admin sejak awal.
     */
    #[\Livewire\Attributes\Url(as: 'tawarkan', except: '')]
    public string $tawarkan = '';

    /**
     * Pesan tawaran yang sudah terisi tanggalnya.
     *
     * Kosong bila tidak sedang menawarkan apa pun — layar daftar tunggu juga
     * dipakai untuk pekerjaan lain, dan kalimat tawaran yang selalu terpasang
     * akan terkirim pada hari tidak ada kursi yang kosong.
     */
    public function pesanTawaran(array $baris): string
    {
        $paket = trim((string) ($baris['paket'] ?? ''));

        if ($this->tawarkan === '') {
            return 'Halo '.$baris['nama'].', ada kabar soal '
                .($paket !== '' ? $paket : 'trip yang Anda tunggu').'.';
        }

        return 'Halo '.$baris['nama'].', ada kursi yang baru kosong untuk '
            .($paket !== '' ? $paket.' ' : '')
            .'keberangkatan '.$this->tawarkan.'. '
            .'Untuk '.(int) ($baris['jumlah_peserta'] ?? 1).' orang seperti yang Bapak/Ibu tunggu. '
            .'Masih berminat? Kursinya kami tahan dulu sampai nanti sore.';
    }

    /**
     * Menahan kursi satu rombongan untuk orang ini.
     *
     * Dicatat sesaat SEBELUM admin membuka WhatsApp, bukan sesudah balasannya
     * datang: yang menahan admin kedua menawarkan kursi yang sama adalah
     * catatan niatnya, dan menunggu balasan justru membuat jendela bentroknya
     * selebar waktu menunggu.
     */
    public function tahanKursi(int $id, int $keberangkatan): void
    {
        $this->kirimLewatPos("/daftar-tunggu/{$id}/tawarkan", [
            'keberangkatan_id' => $keberangkatan,
            'tahan_jam' => 6,
        ]);
    }

    public function lepasTahanan(int $id): void
    {
        if ($this->hapusData("/daftar-tunggu/{$id}/tawarkan",
            'Tawaran dilepas. Kursinya boleh ditawarkan ke yang lain.')) {
            // Tidak ada yang perlu dikerjakan: daftar digambar ulang sendiri.
        }
    }

    /**
     * Rombongan yang masih bisa ditawarkan untuk paket orang ini.
     *
     * Hanya yang BELUM berangkat dan masih dibuka — menawarkan kursi
     * rombongan yang sudah jalan bukan kekeliruan kecil, dan daftar pilihan
     * yang memuatnya membuat kekeliruan itu tinggal satu ketukan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rombonganPaket(int $paketId): array
    {
        $paket = collect($this->rujukan('paket_wisata'))->firstWhere('id', $paketId);

        return collect($paket['keberangkatan'] ?? [])
            ->filter(fn ($kb) => ($kb['aktif'] ?? false)
                && ! empty($kb['tanggal_berangkat'])
                && $kb['tanggal_berangkat'] >= now()->toDateString())
            ->values()
            ->all();
    }

    /** Pengirim POST sederhana; MemanggilOrcha hanya punya PATCH & POST berkas. */
    private function kirimLewatPos(string $jalur, array $data): void
    {
        try {
            $balasan = $this->orcha()->kirim($jalur, $data);

            cache()->forget('orcha.perlu-ditindak');
            \App\Support\HitunganOrcha::lupakanSemua();

            $this->dispatch('order-updated', message: $balasan['pesan'] ?? 'Tersimpan.');
        } catch (\App\Exceptions\OrchaTidakTerjangkau $e) {
            $this->dispatch('toast-error', message: $e->getMessage());
        }
    }

    public function tandaiDihubungi(int $id): void
    {
        try {
            $this->orcha()->kirim("/daftar-tunggu/{$id}/dihubungi", []);
            \App\Support\HitunganOrcha::lupakanSemua();
        } catch (OrchaTidakTerjangkau) {
            // Sengaja diam — lihat alasannya di atas.
        }
    }

    public function keluarkan(int $id): void
    {
        try {
            $this->orcha()->hapus("/daftar-tunggu/{$id}");

            /*
             | Penanda di bilah samping ikut dilupakan.
             |
             | Angkanya disimpan semenit supaya tiap perpindahan halaman tidak
             | menembak Orcha lagi — tetapi begitu admin sendiri yang
             | mengubahnya, simpanan itu berubah dari penghemat jadi pembohong:
             | orang yang baru saja dikeluarkan tetap terhitung, dan admin
             | mengira tekanannya tidak tersimpan lalu mengulanginya.
             */
            \App\Support\HitunganOrcha::lupakanSemua();

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
