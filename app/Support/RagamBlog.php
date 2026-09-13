<?php

namespace App\Support;

/**
 * Warna & ikon untuk kategori artikel blog.
 *
 * Kategori blog (BlogCategory) hanya menyimpan nama dan slug — tidak ada kolom
 * warna maupun ikon. Tanpa keduanya, kartu artikel yang tidak punya sampul
 * semuanya tampil sebagai kotak persik pucat berikon sama; delapan kartu di
 * /blog jadi tidak bisa dibedakan satu sama lain.
 *
 * Warnanya TIDAK dipilih sendiri di sini, melainkan dipinjam dari kategori
 * produk padanannya di KategoriBeranda — pembaca yang datang dari Shop sudah
 * mengenali biru sebagai urusan plagiasi dan ungu sebagai urusan AI, dan artikel
 * tentang hal yang sama sebaiknya memakai warna yang sama.
 *
 * Dicocokkan lewat KATA KUNCI di awal kata, bukan nama persis: nama kategori blog boleh
 * diubah admin ("Turnitin" jadi "Cek Turnitin") tanpa membuat warnanya hilang.
 */
class RagamBlog
{
    /** Kata kunci nama kategori blog => label kategori di KategoriBeranda. */
    private const PETA = [
        'turnitin' => 'Cek Plagiasi',
        'plagia' => 'Cek Plagiasi',
        'ai' => 'AI Tools',
        'riset' => 'Jurnal & Riset',
        'publikasi' => 'Jurnal & Riset',
        'jurnal' => 'Jurnal & Riset',
        'desain' => 'Desain & Kreatif',
        'parafrase' => 'Jasa Parafrase',
    ];

    /** Dipakai bila kategorinya kosong atau tidak dikenali. */
    public const BAWAAN = ['warna' => '#f26522', 'ikon' => 'bi-journal-text'];

    /**
     * @return array{warna: string, ikon: string}
     */
    public static function untuk(?string $kategori): array
    {
        $nama = mb_strtolower(trim((string) $kategori));

        if ($nama === '') {
            return self::BAWAAN;
        }

        foreach (self::PETA as $kata => $label) {
            // Awal kata harus cocok; "ai" juga harus berakhir sebagai kata utuh.
            // Tanpa batas kata, "pemakaian" dan "rangkaian" ikut terbaca AI.
            $pola = '/(?<!\p{L})'.preg_quote($kata, '/').($kata === 'ai' ? '(?!\p{L})' : '').'/u';

            if (! preg_match($pola, $nama)) {
                continue;
            }

            $kat = collect(KategoriBeranda::PETA)->firstWhere('label', $label);

            if ($kat) {
                return ['warna' => $kat['warna'], 'ikon' => $kat['ikon']];
            }
        }

        return self::BAWAAN;
    }
}
