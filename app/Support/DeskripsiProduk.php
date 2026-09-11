<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Merapikan deskripsi produk & paket secara OTOMATIS, apa pun cara admin
 * menulisnya: diketik dengan pola toko ("Nama – slogan", paragraf, poin ✅),
 * atau ditempel mentah dari ChatGPT lengkap dengan **tebal**, "## Judul",
 * "- poin", dan "1. langkah".
 *
 * Data yang tersimpan TIDAK diubah. Semua perapian terjadi saat ditampilkan,
 * jadi admin tidak perlu belajar format apa pun, dan deskripsi lama yang sudah
 * rapi tetap tampil persis seperti sebelumnya.
 *
 * Hasil blok() adalah daftar blok BERURUTAN:
 *
 *   judul     baris pertama yang pendek ("Canva Premium – Akun Siap Pakai"),
 *             dipecah jadi nama & slogan
 *   paragraf  teks biasa
 *   subjudul  "## Fitur Utama", "**Fitur Utama**", atau "Cocok untuk:"
 *   poin      ✅ / - / * / • — dikelompokkan jadi satu daftar
 *   langkah   "1." / "2)" — dikelompokkan jadi daftar bernomor
 *   catatan   📌 🎯 ⚡ 💡 … — baris tersendiri dengan ikonnya
 *
 * Urutan dipertahankan karena teks dari AI biasanya punya beberapa bagian
 * ("Fitur", "Cocok untuk", "Cara pakai"); menumpuk semua poin jadi satu daftar
 * akan mencampur fitur dengan langkah pemesanan.
 */
class DeskripsiProduk
{
    /** Penanda poin FITUR → dirender sebagai centang. */
    private const PENANDA_POIN = ['✅', '✔️', '✔', '☑️', '✓', '•', '●', '▪'];

    /** Penanda CATATAN → baris tersendiri, ikonnya dipertahankan. */
    private const PENANDA_CATATAN = ['📌', '🎯', '⚡', '🎉', '🔥', '💡', '⭐', '👉'];

    /**
     * Baris pertama sepanjang ini atau kurang boleh dianggap judul.
     *
     * Diukur dari seluruh deskripsi di toko: baris judul 31–78 karakter,
     * paragraf pembuka 146–329 karakter. Batas 90 memisahkan keduanya tanpa
     * satu pun salah tebak.
     */
    public const JUDUL_MAKS = 90;

    /** Baris biasa di bawah label bertitik dua sepanjang ini masih dianggap butir daftar. */
    public const BUTIR_MAKS = 120;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function blok(?string $teks): array
    {
        $teks = self::bersihkan($teks);

        if ($teks === '') {
            return [];
        }

        $pola = self::polaPenanda();
        $blok = [];
        $sesudahKosong = true;

        // Sesudah label bertitik dua ("Yang kamu dapat:"), baris-baris biasa
        // di bawahnya adalah DAFTAR meski tanpa penanda — begitulah orang
        // menulis rincian isi paket. Tanpa ini, rinciannya tampil sebagai
        // paragraf abu yang tercerai dari labelnya.
        $daftar = false;

        foreach (explode("\n", $teks) as $mentah) {
            $baris = trim($mentah);

            // Baris kosong dan garis pemisah (---, ***, ___) hanya jeda.
            if ($baris === '' || preg_match('/^([-_*=]\s*){3,}$/u', $baris)) {
                $sesudahKosong = true;

                continue;
            }

            // Subjudul: "## Judul", baris yang seluruhnya tebal, atau label
            // pendek yang diakhiri titik dua ("Cocok untuk:").
            if (preg_match('/^#{1,6}\s+(.+)$/u', $baris, $m)) {
                $blok[] = ['jenis' => 'subjudul', 'teks' => self::rapikanLabel($m[1]), 'asal' => 'pagar'];
                $sesudahKosong = false;
                $daftar = false;

                continue;
            }

            if (preg_match('/^(?:\*\*|__)([^*_]+?)(?:\*\*|__)\s*(:?)\s*$/u', $baris, $m)) {
                $blok[] = ['jenis' => 'subjudul', 'teks' => self::rapikanLabel($m[1]), 'asal' => 'tebal'];
                $sesudahKosong = false;
                $daftar = $m[2] === ':' || str_ends_with(trim($m[1]), ':');

                continue;
            }

            if (preg_match('/^([^:.!?]{2,48}):$/u', $baris, $m) && ! preg_match($pola, $baris)) {
                $blok[] = ['jenis' => 'subjudul', 'teks' => self::rapikanLabel($m[1]), 'asal' => 'label'];
                $sesudahKosong = false;
                $daftar = true;

                continue;
            }

            // Langkah bernomor: "1. Bayar", "2) Terima akun".
            if (preg_match('/^\d{1,2}[.)]\s+(.+)$/u', $baris, $m)) {
                self::tambahButir($blok, 'langkah', $m[1]);
                $sesudahKosong = false;

                continue;
            }

            // Poin gaya markdown: "- ", "* ", "+ ", "• ".
            if (preg_match('/^[-*+•●▪]\s+(.+)$/u', $baris, $m)) {
                self::tambahButir($blok, 'poin', $m[1]);
                $sesudahKosong = false;

                continue;
            }

            // Baris biasa — mungkin memuat penanda ✅/📌 di awal atau di
            // tengahnya ("✅ A ✅ B" dalam satu baris), perilaku lama.
            $bagian = preg_split($pola, $baris, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$baris];
            $awal = trim((string) array_shift($bagian));

            if ($awal !== '' && $daftar && ! $bagian && mb_strlen($awal) <= self::BUTIR_MAKS) {
                self::tambahButir($blok, 'poin', $awal);
                $sesudahKosong = false;

                continue;
            }

            if ($awal !== '') {
                // Kalimat panjang sesudah label adalah paragraf penjelas, dan
                // daftarnya dianggap selesai di situ.
                $daftar = false;

                // Baris tanpa penanda TEPAT sesudah sebuah poin (tanpa baris
                // kosong) adalah lanjutan poin itu — AI kerap membungkus poin
                // panjang jadi dua baris.
                if (! $sesudahKosong && self::butirTerakhir($blok)) {
                    self::sambung($blok, $awal);
                } else {
                    $blok[] = ['jenis' => 'paragraf', 'teks' => $awal];
                }
            }

            for ($i = 0; $i + 1 < count($bagian); $i += 2) {
                $penanda = $bagian[$i];
                $isi = trim((string) preg_replace('/\s+/u', ' ', $bagian[$i + 1]));

                if ($isi === '') {
                    continue;
                }

                $terakhir = $i + 3 >= count($bagian);

                if (in_array($penanda, self::PENANDA_CATATAN, true) && $terakhir && str_ends_with($isi, ':')) {
                    // "📌 Yang kamu dapat:" bukan catatan, melainkan kepala
                    // daftar yang ikonnya dipertahankan.
                    $blok[] = ['jenis' => 'subjudul', 'teks' => self::rapikanLabel($isi), 'asal' => 'label', 'ikon' => $penanda];
                    $daftar = true;
                } elseif (in_array($penanda, self::PENANDA_CATATAN, true)) {
                    $blok[] = ['jenis' => 'catatan', 'ikon' => $penanda, 'teks' => $isi];
                    $daftar = false;
                } else {
                    self::tambahButir($blok, 'poin', $isi);
                }
            }

            $sesudahKosong = false;
        }

        return self::tandaiJudul($blok);
    }

    /**
     * Bentuk lama (paragraf / poin / ekstra) untuk tampilan yang belum memakai
     * blok(). Ditambah kunci judul & tagline; baris judul tidak lagi ikut
     * dicetak sebagai paragraf pertama.
     *
     * @return array{judul: ?string, tagline: ?string, paragraf: array<int, string>, poin: array<int, string>, ekstra: array<int, array{ikon: string, teks: string}>}
     */
    public static function pisah(?string $teks): array
    {
        $hasil = ['judul' => null, 'tagline' => null, 'paragraf' => [], 'poin' => [], 'ekstra' => []];

        foreach (self::blok($teks) as $b) {
            switch ($b['jenis']) {
                case 'judul':
                    $hasil['judul'] = $b['teks'];
                    $hasil['tagline'] = $b['tagline'];
                    break;
                case 'paragraf':
                case 'subjudul':
                    $hasil['paragraf'][] = $b['teks'];
                    break;
                case 'poin':
                case 'langkah':
                    array_push($hasil['poin'], ...$b['butir']);
                    break;
                case 'catatan':
                    $hasil['ekstra'][] = ['ikon' => $b['ikon'], 'teks' => $b['teks']];
                    break;
            }
        }

        return $hasil;
    }

    /**
     * Teks satu baris siap cetak: DI-ESCAPE lebih dulu, baru **tebal** dan
     * __tebal__ diubah jadi <strong>. Kode `sebaris` dan tautan [teks](url)
     * dari AI dilepas jadi teks biasa — tautan yang ikut tertempel tanpa
     * diperiksa tidak boleh bisa diklik di halaman toko.
     */
    public static function inline(?string $teks): HtmlString
    {
        $aman = e((string) $teks);
        $aman = preg_replace('/\*\*([^*]+?)\*\*/u', '<strong>$1</strong>', $aman) ?? $aman;
        $aman = preg_replace('/__([^_]+?)__/u', '<strong>$1</strong>', $aman) ?? $aman;
        $aman = preg_replace('/`([^`]+?)`/u', '$1', $aman) ?? $aman;
        $aman = preg_replace('/\[([^\]]+)\]\(https?:\/\/[^)\s]+\)/u', '$1', $aman) ?? $aman;

        return new HtmlString($aman);
    }

    /**
     * Blok pertama dijadikan judul bila ia paragraf pendek tanpa tanda akhir
     * kalimat, atau judul "# …" / baris tebal ber-slogan. Label "Cocok untuk:"
     * atau "**Kenapa pilih kami?**" di baris pertama TIDAK dijadikan judul —
     * itu kepala bagian, bukan nama produk.
     */
    private static function tandaiJudul(array $blok): array
    {
        if (count($blok) < 2) {
            return $blok;
        }

        $pertama = $blok[0];

        $paragrafPendek = $pertama['jenis'] === 'paragraf'
            && mb_strlen($pertama['teks']) <= self::JUDUL_MAKS
            && ! preg_match('/[.!?:…]$/u', $pertama['teks']);

        $judulMarkdown = $pertama['jenis'] === 'subjudul'
            && ($pertama['asal'] === 'pagar'
                || ($pertama['asal'] === 'tebal' && preg_match('/\s[–—-]\s/u', $pertama['teks'])));

        if ($paragrafPendek || $judulMarkdown) {
            $judul = self::rapikanLabel($pertama['teks']);
            [$nama, $tagline] = self::pisahTagline($judul);
            $blok[0] = ['jenis' => 'judul', 'teks' => $judul, 'nama' => $nama, 'tagline' => $tagline];
        }

        return $blok;
    }

    /** "Canva Premium – Akun Siap Pakai" → ["Canva Premium", "Akun Siap Pakai"]. */
    private static function pisahTagline(string $judul): array
    {
        // Tanda hubung HARUS diapit spasi: "Sat-Set" adalah satu kata.
        $bagian = preg_split('/\s[–—-]\s/u', $judul, 2);

        return count($bagian) === 2
            ? [trim($bagian[0]), trim($bagian[1])]
            : [$judul, null];
    }

    private static function tambahButir(array &$blok, string $jenis, string $teks): void
    {
        $teks = trim($teks);

        if ($teks === '') {
            return;
        }

        $n = count($blok);

        if ($n > 0 && $blok[$n - 1]['jenis'] === $jenis) {
            $blok[$n - 1]['butir'][] = $teks;

            return;
        }

        $blok[] = ['jenis' => $jenis, 'butir' => [$teks]];
    }

    private static function butirTerakhir(array $blok): bool
    {
        $akhir = end($blok);

        return $akhir !== false && in_array($akhir['jenis'], ['poin', 'langkah', 'catatan'], true);
    }

    private static function sambung(array &$blok, string $teks): void
    {
        $i = count($blok) - 1;

        if ($blok[$i]['jenis'] === 'catatan') {
            $blok[$i]['teks'] .= ' '.$teks;

            return;
        }

        $j = count($blok[$i]['butir']) - 1;
        $blok[$i]['butir'][$j] .= ' '.$teks;
    }

    /** Lepas **, __, # di depan, dan titik dua di belakang. */
    private static function rapikanLabel(string $teks): string
    {
        $teks = preg_replace('/^#+\s*/u', '', trim($teks)) ?? $teks;
        $teks = str_replace(['**', '__'], '', $teks);

        return trim(rtrim(trim($teks), ':'));
    }

    /**
     * Teks tempelan membawa sampah yang tidak terlihat: akhir baris Windows,
     * pemisah baris Unicode, spasi-tak-putus, dan tanda BOM / spasi-nol di
     * awal. ZWJ sengaja DIBIARKAN — tanpanya emoji gabungan seperti 👨‍💻
     * pecah jadi dua.
     */
    private static function bersihkan(?string $teks): string
    {
        $teks = str_replace(["\r\n", "\r", "\u{2028}", "\u{2029}"], "\n", (string) $teks);
        $teks = preg_replace('/[\x{200B}\x{FEFF}]/u', '', $teks) ?? $teks;
        $teks = str_replace("\u{00A0}", ' ', $teks);

        return trim($teks);
    }

    private static function polaPenanda(): string
    {
        $semua = array_merge(self::PENANDA_POIN, self::PENANDA_CATATAN);

        return '/('.implode('|', array_map(fn ($m) => preg_quote($m, '/'), $semua)).')/u';
    }
}
