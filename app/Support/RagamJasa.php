<?php

namespace App\Support;

use App\Models\Order;

/**
 * Kosakata & warna untuk tiap jenis jasa di halaman /cek.
 *
 * Dulu seluruh halaman berbicara sebagai "pengecekan": judulnya "Halaman
 * Pengecekan Anda", jatahnya "Sisa Pengecekan", tombolnya "Kirim untuk
 * Diperiksa". Untuk pesanan PARAFRASE itu keliru — tidak ada yang diperiksa di
 * sana, naskahnya ditulis ulang. Pelanggan parafrase membaca halaman yang
 * seolah milik layanan lain, dan kata "kuota pengecekan" membuatnya mengira
 * ia membeli jatah pemeriksaan yang tidak pernah ia beli.
 *
 * Semua perbedaan kata dan warna dikumpulkan di SATU tempat ini. Kalau tersebar
 * sebagai `@if ($jenis === 'parafrase')` di sepanjang Blade, menambah satu jenis
 * jasa berarti menyisir ulang seluruh halaman dan pasti ada yang terlewat.
 */
class RagamJasa
{
    public const BAWAAN = 'pengecekan';

    /**
     * Satu baris per jenis layanan.
     *
     * 'satuan' dipakai untuk kalimat jatah, dan sengaja berbeda: pelanggan
     * plagiasi memang membeli sejumlah PENGECEKAN, sedangkan pelanggan
     * parafrase membeli pengerjaan atas satu NASKAH.
     */
    public const DAFTAR = [
        'plagiasi' => [
            'nama' => 'Cek Plagiasi',
            'judul' => 'Halaman Pengecekan Anda',
            'ajakan' => 'Simpan halaman ini untuk mengunggah file & mengunduh hasil.',
            'ikon' => 'shield-check',
            'jatahLabel' => 'Sisa Pengecekan',
            'jatahIkon' => 'collection',
            'satuan' => 'pengecekan',
            'unggahJudul' => 'Unggah File untuk Diperiksa',
            'unggahIkon' => 'cloud-arrow-up',
            'tombol' => 'Kirim untuk Diperiksa',
            'riwayat' => 'Riwayat Pengecekan',
            'selesaiSemua' => 'Pengecekan Anda sudah selesai seluruhnya',
            'habis' => 'Jumlah pengecekan Anda sudah maksimal',
            'kosong' => 'Belum ada file yang diunggah. Silakan unggah file pertama Anda di atas.',
            'warna' => '#ea580c',
            'lembut' => '#fff7ed',
            'tepi' => '#fed7aa',
            'jaminanJudul' => 'Jaminan Privasi & Keamanan',
            'jaminan' => [
                ['b' => 'No Repository', 't' => 'file Anda <b>tidak disimpan</b> ke database Turnitin. Jadi dokumen Anda tidak akan terdeteksi sebagai kemiripan pada pengecekan berikutnya.'],
                ['b' => '100% Turnitin', 't' => 'pengecekan dilakukan memakai Turnitin asli, bukan alat lain.'],
                ['b' => 'Aman &amp; rahasia', 't' => 'dokumen bersifat pribadi dan <b>tidak disebarluaskan</b> ke pihak mana pun.'],
            ],
        ],

        'ai' => [
            'nama' => 'Deteksi AI',
            'judul' => 'Halaman Deteksi AI Anda',
            'ajakan' => 'Simpan halaman ini untuk mengunggah file & mengunduh laporan deteksi.',
            'ikon' => 'robot',
            'jatahLabel' => 'Sisa Deteksi',
            'jatahIkon' => 'cpu',
            'satuan' => 'deteksi',
            'unggahJudul' => 'Unggah File untuk Dideteksi',
            'unggahIkon' => 'cloud-arrow-up',
            'tombol' => 'Kirim untuk Dideteksi',
            'riwayat' => 'Riwayat Deteksi AI',
            'selesaiSemua' => 'Deteksi AI Anda sudah selesai seluruhnya',
            'habis' => 'Jumlah deteksi Anda sudah maksimal',
            'kosong' => 'Belum ada file yang diunggah. Silakan unggah file pertama Anda di atas.',
            'warna' => '#4f46e5',
            'lembut' => '#eef0ff',
            'tepi' => '#c7d2fe',
            'jaminanJudul' => 'Tentang Deteksi AI Ini',
            'jaminan' => [
                ['b' => 'Turnitin AI Detection', 't' => 'memakai alat resmi Turnitin, bukan pendeteksi gratisan yang hasilnya berubah-ubah.'],
                ['b' => 'Wajib bahasa Inggris', 't' => 'deteksi AI Turnitin hanya bekerja pada naskah berbahasa Inggris. Naskah berbahasa Indonesia tidak dapat dideteksi.'],
                ['b' => 'Tidak disimpan', 't' => 'file Anda <b>tidak dimasukkan</b> ke database Turnitin dan <b>tidak disebarluaskan</b> ke pihak mana pun.'],
            ],
        ],

        'parafrase' => [
            'nama' => 'Jasa Parafrase',
            'judul' => 'Halaman Pengerjaan Anda',
            'ajakan' => 'Simpan halaman ini untuk mengirim naskah & mengunduh hasilnya.',
            'ikon' => 'pencil-square',
            'jatahLabel' => 'Naskah Dikirim',
            'jatahIkon' => 'file-earmark-text',
            'satuan' => 'naskah',
            'unggahJudul' => 'Kirim Naskah untuk Diparafrase',
            'unggahIkon' => 'send',
            'tombol' => 'Kirim untuk Dikerjakan',
            'riwayat' => 'Riwayat Pengerjaan',
            'selesaiSemua' => 'Pengerjaan Anda sudah selesai seluruhnya',
            'habis' => 'Seluruh naskah Anda sudah kami terima',
            'kosong' => 'Belum ada naskah yang dikirim. Silakan kirim naskah Anda di atas.',
            'warna' => '#7c3aed',
            'lembut' => '#f5f3ff',
            'tepi' => '#ddd6fe',
            'jaminanJudul' => 'Cara Kami Mengerjakannya',
            'jaminan' => [
                ['b' => 'Ditulis ulang manusia', 't' => 'naskah diparafrase <b>manual oleh editor</b>, bukan diputar lewat alat otomatis.'],
                ['b' => 'Makna tetap utuh', 't' => 'susunan kalimat berubah, tetapi maksud dan data di dalamnya tidak.'],
                ['b' => 'Aman &amp; rahasia', 't' => 'naskah bersifat pribadi dan <b>tidak disebarluaskan</b> ke pihak mana pun.'],
            ],
        ],

        // Produk jasa yang tidak mengaku sebagai salah satu di atas.
        'pengecekan' => [
            'nama' => 'Layanan Anda',
            'judul' => 'Halaman Layanan Anda',
            'ajakan' => 'Simpan halaman ini untuk mengunggah file & mengunduh hasil.',
            'ikon' => 'file-earmark-check',
            'jatahLabel' => 'Sisa Pengiriman',
            'jatahIkon' => 'collection',
            'satuan' => 'pengiriman',
            'unggahJudul' => 'Unggah File',
            'unggahIkon' => 'cloud-arrow-up',
            'tombol' => 'Kirim File',
            'riwayat' => 'Riwayat Pengerjaan',
            'selesaiSemua' => 'Pesanan Anda sudah selesai seluruhnya',
            'habis' => 'Seluruh file Anda sudah kami terima',
            'kosong' => 'Belum ada file yang diunggah. Silakan unggah file pertama Anda di atas.',
            'warna' => '#0f766e',
            'lembut' => '#f0fdfa',
            'tepi' => '#99f6e4',
            'jaminanJudul' => 'Jaminan Privasi & Keamanan',
            'jaminan' => [
                ['b' => 'Aman &amp; rahasia', 't' => 'dokumen bersifat pribadi dan <b>tidak disebarluaskan</b> ke pihak mana pun.'],
                ['b' => 'Dikerjakan manusia', 't' => 'setiap pesanan ditangani tim kami, bukan diproses otomatis tanpa pemeriksaan.'],
            ],
        ],
    ];

    /**
     * Jenis yang mewakili sebuah pesanan.
     *
     * Diambil dari PRODUKNYA, bukan dari add-on. Pesanan parafrase yang
     * membeli tambahan hasil plagiasi & AI tetap sebuah pesanan parafrase —
     * add-on adalah berkas balasan, bukan layanan yang dipesan. Kalau tidak,
     * halaman pelanggan parafrase akan berganti judul jadi "Pengecekan" hanya
     * karena ia menambah satu laporan.
     *
     * Bila satu pesanan memuat lebih dari satu jenis produk jasa, dipakai
     * kosakata netral: menonjolkan salah satunya berarti menyembunyikan yang lain.
     */
    public static function jenisPesanan(Order $order): string
    {
        $jenis = $order->items
            ->map(fn ($i) => optional($i->product)?->jenisLayanan())
            ->filter()
            ->unique()
            ->values();

        if ($jenis->count() !== 1) {
            return self::BAWAAN;
        }

        return isset(self::DAFTAR[$jenis->first()]) ? $jenis->first() : self::BAWAAN;
    }

    /** @return array<string, mixed> */
    public static function untuk(Order $order): array
    {
        return self::DAFTAR[self::jenisPesanan($order)];
    }

    /** @return array<string, mixed> */
    public static function dariJenis(?string $jenis): array
    {
        return self::DAFTAR[$jenis] ?? self::DAFTAR[self::BAWAAN];
    }
}
