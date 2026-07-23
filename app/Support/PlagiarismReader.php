<?php

namespace App\Support;

use Smalot\PdfParser\Parser;

/**
 * Pembaca persen kemiripan dari PDF hasil Turnitin. Bersifat "best effort":
 * kalau gagal/format tak dikenal, kembalikan null — admin isi manual.
 * Angka final SELALU dikonfirmasi admin, jadi heuristik ini cukup untuk pra-isi.
 */
class PlagiarismReader
{
    /** Batas ukuran PDF yang di-parse. Di atas ini → dilewati (admin isi manual). */
    private const MAKS_BYTE = 40 * 1024 * 1024; // 40 MB

    /**
     * Longgarkan batas memori & waktu SEBELUM parsing. PDF Turnitin 27MB memakai
     * ~1GB memori; default 1536M kadang mepet saat digabung memori request lain,
     * dan kehabisan memori = FATAL (tak tertangkap try/catch) → upload mati.
     * Server mengizinkan ini_set memori dinaikkan (diuji: 1536M → 3072M).
     */
    private static function siapkanParse(): void
    {
        @ini_set('memory_limit', '3072M');
        @set_time_limit(120);
    }

    public static function persenDariPdf(string $absolutePath): ?int
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        // PDF terlalu besar dilewati (admin isi manual). Batas dinaikkan ke 40MB
        // karena laporan Turnitin kini sering 20-30MB — dulu 8MB membuat persen
        // sering "tak terbaca". Parsing dibatasi memori + waktu di siapkanParse().
        if (filesize($absolutePath) > self::MAKS_BYTE) {
            return null;
        }

        self::siapkanParse();

        try {
            $text = (new Parser)->parseFile($absolutePath)->getText();
        } catch (\Throwable $e) {
            return null;
        }

        if (! $text) {
            return null;
        }

        // Normalkan spasi agar regex stabil.
        $norm = preg_replace('/\s+/', ' ', $text);

        /*
         * Laporan Turnitin memuat BANYAK angka %: satu angka utama
         * ("14% Overall Similarity") plus rincian per sumber ("12% Internet
         * database", "3% Publications database", dst). Karena itu angka hanya
         * diambil bila menempel langsung pada frasa penanda angka UTAMA —
         * jarak antar keduanya dibatasi ketat (maks 15 karakter non-angka).
         * Urutan pola: dari paling spesifik ke paling longgar.
         */
        $pola = [
            '/(\d{1,3})\s*%\s*overall\s+similarity/i',              // "14% Overall Similarity"
            '/overall\s+similarity[^0-9%]{0,15}(\d{1,3})\s*%/i',    // "Overall Similarity: 14%"
            '/(\d{1,3})\s*%\s*similarity\s+index/i',                // "14% Similarity Index"
            '/similarity\s+index[^0-9%]{0,15}(\d{1,3})\s*%/i',      // "Similarity Index 14%"
            '/(\d{1,3})\s*%\s*kemiripan/i',
            '/kemiripan[^0-9%]{0,15}(\d{1,3})\s*%/i',
        ];

        foreach ($pola as $p) {
            if (preg_match($p, $norm, $m)) {
                return self::batasi((int) $m[1]);
            }
        }

        /*
         * Sengaja TIDAK ada fallback "ambil persen pertama". Menebak berisiko
         * menyambar angka rincian sumber dan menampilkan persen plagiasi yang
         * SALAH ke customer — jauh lebih berbahaya daripada kolom kosong.
         * Bila ragu, kembalikan null dan biarkan admin mengisinya manual.
         */
        return null;
    }

    /**
     * Persen teks terdeteksi AI dari PDF laporan "AI Writing" Turnitin.
     * Sama seperti persen plagiasi: hanya diambil bila menempel pada frasa
     * penandanya — bila ragu kembalikan null agar admin mengisi manual.
     */
    public static function persenAiDariPdf(string $absolutePath): ?int
    {
        return self::bacaAi($absolutePath)['persen'] ?? null;
    }

    /**
     * Baca laporan AI beserta SUMBERNYA. Sumber penting karena artinya berbeda:
     *  - Turnitin  : persentase teks yang terdeteksi AI.
     *  - GPTZero   : PROBABILITAS dokumen dibuat AI — laporannya sendiri menegaskan
     *                ini "not a percentage of AI text in the document".
     * Keduanya angka 0-100, tapi label ke admin/customer harus dibedakan supaya
     * tidak menyesatkan.
     *
     * @return array{persen:int, sumber:string, label:string}|null
     */
    public static function bacaAi(string $absolutePath): ?array
    {
        $norm = self::teksPdf($absolutePath);
        if ($norm === null) {
            return null;
        }

        // Turnitin — persentase teks AI.
        $polaTurnitin = [
            '/(\d{1,3})\s*%\s*detected\s+as\s+ai/i',              // "14% detected as AI"
            '/detected\s+as\s+ai[^0-9%]{0,15}(\d{1,3})\s*%/i',
            '/(\d{1,3})\s*%\s*ai[\s-]*generated/i',               // "14% AI-generated"
            '/ai[\s-]*generated[^0-9%]{0,15}(\d{1,3})\s*%/i',
            '/ai\s+writing[^0-9%]{0,20}(\d{1,3})\s*%/i',          // "AI Writing ... 14%"
            '/(\d{1,3})\s*%\s*ai\s+writing/i',
        ];

        // GPTZero — probabilitas dokumen AI.
        $polaGptZero = [
            '/ai\s*probability[^0-9%]{0,15}(\d{1,3})\s*%/i',      // "AI Probability 16%"
            '/(\d{1,3})\s*%\s*ai\s*probability/i',
            '/probability[^0-9%]{0,15}(\d{1,3})\s*%[^0-9%]{0,40}ai\s+generated/i',
        ];

        foreach ([
            ['turnitin', 'Persen teks AI', $polaTurnitin],
            ['gptzero', 'Probabilitas AI', $polaGptZero],
        ] as [$sumber, $label, $polaSumber]) {
            $nilai = self::semuaNilai($norm, $polaSumber);

            if (count($nilai) === 1) {
                return ['persen' => $nilai[0], 'sumber' => $sumber, 'label' => $label, 'ambigu' => false];
            }

            /*
             * Satu berkas bisa memuat LEBIH DARI SATU laporan — mis. dua submission
             * Turnitin yang saling TERTIMPA di koordinat sama. Yang digambar paling
             * akhir berada di lapisan atas, dan itulah yang dilihat admin saat PDF
             * dibuka; nilai sebelumnya tertutup di bawahnya.
             *
             * Jadi yang dipra-isi adalah nilai TERAKHIR (yang tampak), bukan yang
             * pertama ketemu. Tetap ditandai ambigu supaya admin sadar ada nilai
             * lain di berkas itu dan bisa menggantinya bila perlu.
             */
            if (count($nilai) > 1) {
                return [
                    'persen' => end($nilai),
                    'sumber' => $sumber,
                    'label' => $label,
                    'ambigu' => true,
                    'nilai' => $nilai,
                ];
            }
        }

        return null;
    }

    /**
     * Semua persen unik yang cocok dengan sekumpulan pola, URUT SESUAI POSISI
     * KEMUNCULAN di dalam PDF — bukan urut angka. Urutan ini penting: pada PDF
     * yang laporannya saling tertimpa, yang muncul belakangan digambar di
     * lapisan atas dan itulah yang terlihat.
     */
    private static function semuaNilai(string $norm, array $pola): array
    {
        $temuan = []; // offset => persen

        foreach ($pola as $p) {
            if (preg_match_all($p, $norm, $mm, PREG_OFFSET_CAPTURE)) {
                foreach ($mm[1] as $cocok) {
                    if (! is_null($n = self::batasi((int) $cocok[0]))) {
                        $temuan[(int) $cocok[1]] = $n;
                    }
                }
            }
        }

        ksort($temuan); // urut posisi di dokumen

        // Buang duplikat tapi PERTAHANKAN kemunculan TERAKHIR tiap angka,
        // agar nilai lapisan teratas tetap berada di akhir daftar.
        $nilai = [];
        foreach ($temuan as $n) {
            if (($k = array_search($n, $nilai, true)) !== false) {
                unset($nilai[$k]);
            }
            $nilai[] = $n;
        }

        return array_values($nilai);
    }

    /** Ambil teks PDF yang sudah dinormalkan spasinya; null bila gagal. */
    private static function teksPdf(string $absolutePath): ?string
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        // Lihat catatan di persenDariPdf(): batas 40MB + memori dinaikkan.
        if (filesize($absolutePath) > self::MAKS_BYTE) {
            return null;
        }

        self::siapkanParse();

        try {
            $text = (new Parser)->parseFile($absolutePath)->getText();
        } catch (\Throwable $e) {
            return null;
        }

        return $text ? preg_replace('/\s+/', ' ', $text) : null;
    }

    private static function batasi(int $n): ?int
    {
        if ($n < 0 || $n > 100) {
            return null;
        }

        return $n;
    }
}
