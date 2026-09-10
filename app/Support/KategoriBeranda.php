<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductBundlings;
use Illuminate\Support\Facades\Cache;

/**
 * Kategori yang tampil di beranda.
 *
 * Katalog ini tidak punya tabel kategori — `tipe_akun` hanya membedakan sharing
 * dan private, bukan jenis kebutuhan. Membuat tabel kategori berarti mengubah
 * alur produk yang sudah berjalan, dan itu pantangan di repo ini. Maka kategori
 * disusun di sini sebagai daftar kata kunci yang dicocokkan ke nama produk.
 *
 * Aturan yang dipegang: SEBUAH KATEGORI HANYA TAMPIL BILA BENAR-BENAR ADA
 * ISINYA. Chip cantik yang membawa pengunjung ke halaman kosong lebih buruk
 * daripada tidak ada chip sama sekali — itu janji yang tidak ditepati, dan
 * pengunjung yang sekali tertipu berhenti mengklik apa pun.
 *
 * Karena itu pula jumlahnya ikut dihitung: begitu katalog berubah, kategori
 * yang kehabisan produk hilang sendiri tanpa ada yang perlu menyuntingnya.
 */
class KategoriBeranda
{
    /**
     * Kunci → label, ikon, dan kata kunci pencocok.
     *
     * Kata kuncinya sengaja nama merek, bukan istilah umum: "AI" cocok dengan
     * hampir semua deskripsi dan akan membuat setiap kategori berisi segalanya.
     *
     * Tiap kategori juga punya WARNANYA sendiri. Sebelumnya kedelapan kartu
     * memakai ikon berlatar persik yang sama persis, dan delapan kartu identik
     * berjajar terbaca sebagai satu tekstur — bukan sebagai delapan pilihan
     * yang berbeda. Warna di sini bukan hiasan: ia yang membuat mata bisa
     * kembali ke kategori yang sama tanpa membaca ulang labelnya.
     *
     * @var array<string, array{label: string, ikon: string, warna: string, kata: array<int, string>}>
     */
    public const PETA = [
        'ai-tools' => [
            'label' => 'AI Tools',
            'ikon' => 'bi-robot',
            'warna' => '#7c3aed',
            'kata' => ['chat gpt', 'chatgpt', 'gemini', 'gamma', 'humata', 'super ai', 'jenni'],
        ],
        'cek-plagiasi' => [
            'label' => 'Cek Plagiasi',
            'ikon' => 'bi-search',
            'warna' => '#2563eb',
            'kata' => ['plagiasi', 'plagiarism', 'turnitin'],
        ],
        'jurnal-riset' => [
            'label' => 'Jurnal & Riset',
            'ikon' => 'bi-journal-text',
            'warna' => '#0d9488',
            'kata' => ['scopus', 'consensus', 'research rabbit', 'scite', 'paperpal'],
        ],
        'desain-kreatif' => [
            'label' => 'Desain & Kreatif',
            'ikon' => 'bi-palette',
            'warna' => '#db2777',
            'kata' => ['canva', 'story tribe'],
        ],
        'parafrase' => [
            'label' => 'Jasa Parafrase',
            'ikon' => 'bi-chat-quote',
            'warna' => '#d97706',
            'kata' => ['quillbot', 'parafrase', 'paraphrase'],
        ],
        'edukasi' => [
            'label' => 'Edukasi',
            'ikon' => 'bi-mortarboard',
            'warna' => '#4f46e5',
            'kata' => ['kahoot', 'edukasi'],
        ],
        'produktivitas' => [
            'label' => 'Produktivitas',
            'ikon' => 'bi-gear',
            'warna' => '#475569',
            'kata' => ['office', 'grammarly', 'deepl', 'notion'],
        ],
    ];

    /** Berapa lama hitungan kategori boleh dipercayai tanpa dihitung ulang. */
    public const SIMPAN_MENIT = 30;

    /**
     * Kategori yang benar-benar ada isinya, siap dirender.
     *
     * "Paket Bundling" disisipkan sebagai kategori tersendiri karena ia memang
     * bagian katalog yang berdiri sendiri — bukan hasil pencocokan nama.
     *
     * ALAMATNYA DIRAKIT SAAT DIRENDER, bukan ikut disimpan di tembolok.
     *
     * route() menghasilkan alamat lengkap berikut host dan portnya. Disimpan
     * di tembolok, alamat itu ikut membeku: tembolok yang terisi saat aplikasi
     * berjalan di localhost:8000 akan terus mengirim pengunjung ke sana meski
     * aplikasinya sudah pindah — dan di sinilah "AI Tools" berujung 404.
     *
     * Bahayanya sama di server: tembolok yang dihangatkan lewat perintah CLI
     * atau lewat permintaan dengan host berbeda akan mengunci seluruh tautan
     * kategori ke host yang salah, dan tidak ada yang menyadarinya sampai ada
     * yang mengklik. Yang disimpan cukup KUNCI-nya; alamat dirakit tiap kali.
     *
     * @return array<int, array{kunci: string, label: string, ikon: string, warna: string, jumlah: int, url: string}>
     */
    public static function tersedia(): array
    {
        $daftar = Cache::remember('beranda.kategori', now()->addMinutes(self::SIMPAN_MENIT), function () {
            $hasil = [];

            foreach (self::PETA as $kunci => $k) {
                $jumlah = self::hitung($k['kata']);

                if ($jumlah === 0) {
                    continue;
                }

                $hasil[] = [
                    'kunci' => $kunci,
                    'label' => $k['label'],
                    'ikon' => $k['ikon'],
                    'warna' => $k['warna'],
                    'jumlah' => $jumlah,
                ];
            }

            $bundling = ProductBundlings::tayang()->count();

            if ($bundling > 0) {
                $hasil[] = [
                    'kunci' => 'bundling',
                    'label' => 'Paket Bundling',
                    'ikon' => 'bi-box-seam',
                    'warna' => '#f26522',
                    'jumlah' => $bundling,
                ];
            }

            return $hasil;
        });

        return array_map(fn ($k) => $k + [
            'url' => $k['kunci'] === 'bundling'
                ? route('bundling.product-bundlings')
                : route('shop.index', ['kategori' => $k['kunci']]),
        ], $daftar);
    }

    /** Produk yang cocok dengan salah satu kata kunci, tanpa yang dijeda. */
    public static function hitung(array $kata): int
    {
        return self::saring(Product::query(), $kata)
            ->where(fn ($q) => $q->whereNull('dijeda')->orWhere('dijeda', false))
            ->count();
    }

    /**
     * Tempelkan penyaring kategori ke sebuah query produk.
     *
     * Dipakai bersama oleh penghitung di sini dan halaman /shop, supaya angka
     * yang dijanjikan chip dan isi halaman tujuannya tidak mungkin berbeda.
     */
    public static function saring($query, array $kata)
    {
        return $query->where(function ($q) use ($kata) {
            foreach ($kata as $k) {
                $q->orWhere('nama_akun', 'like', '%'.$k.'%');
            }
        });
    }

    /** Kata kunci sebuah kategori, atau null bila kuncinya tidak dikenal. */
    public static function kata(?string $kunci): ?array
    {
        return $kunci && isset(self::PETA[$kunci]) ? self::PETA[$kunci]['kata'] : null;
    }

    /** Label sebuah kategori, untuk ditampilkan di halaman tujuan. */
    public static function label(?string $kunci): ?string
    {
        return $kunci && isset(self::PETA[$kunci]) ? self::PETA[$kunci]['label'] : null;
    }
}
