<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Deretan merek pada pita "Dipercaya oleh ribuan pelanggan".
 *
 * Diambil dari katalog yang benar-benar dijual, bukan daftar logo yang
 * ditempel di Blade. Pita semacam ini gampang berubah jadi kebohongan kecil:
 * logo yang tetap terpampang berbulan-bulan setelah produknya berhenti dijual.
 * Dengan membacanya dari katalog, produk yang dijeda atau dihapus hilang
 * sendiri dari pita — tidak ada yang perlu ingat menyuntingnya.
 *
 * Nama yang ditampilkan dipendekkan jadi nama merek saja: pengunjung mengenali
 * "ChatGPT", bukan "Chat Gpt Plus Sharing". Varian sharing dan private dari
 * merek yang sama pun menyatu jadi satu logo, karena dua logo identik
 * bersebelahan terbaca sebagai kesalahan, bukan sebagai dua pilihan.
 */
class MerekDipercaya
{
    /** Kata yang bukan bagian dari nama merek. */
    public const IMBUHAN = [
        'premium', 'private', 'sharing', 'plus', 'pro', 'advance', 'advanced',
        'lisensi', 'akun', 'legal', 'garansi', 'murah',
    ];

    /**
     * Ejaan resmi sebagian merek. Katalog menulisnya dengan kapitalisasi yang
     * berbeda-beda ("Chat Gpt", "Scite_"), dan merek yang salah tulis di pita
     * kepercayaan justru mengurangi kepercayaan.
     */
    public const EJAAN = [
        'chat gpt' => 'ChatGPT',
        'chatgpt' => 'ChatGPT',
        'quillbot' => 'QuillBot',
        'deepl' => 'DeepL',
        'scite_' => 'Scite',
        'scite' => 'Scite',
        'scopus' => 'Scopus',
        'gamma ai' => 'Gamma AI',
        'super ai' => 'Super AI',
        'microsoft office 365' => 'Microsoft 365',
        'research rabbit' => 'ResearchRabbit',
        'story tribe' => 'StoryTribe',
    ];

    public const SIMPAN_MENIT = 30;

    /**
     * @return array<int, array{nama: string, gambar: ?string}>
     */
    public static function ambil(int $jumlah = 8): array
    {
        return Cache::remember('beranda.merek.'.$jumlah, now()->addMinutes(self::SIMPAN_MENIT), function () use ($jumlah) {
            // Urutannya mengikuti apa yang PALING SERING DIBELI, bukan abjad.
            // Pita kepercayaan bekerja lewat pengenalan: merek yang dikenal
            // pengunjung harus jatuh di sebelah kiri, tempat mata mendarat
            // pertama. Abjad menaruh merek paling asing di depan hanya karena
            // huruf awalnya.
            $produk = ProdukTerlaris::ambil(60, null)
                ->filter(fn ($p) => filled($p->image))
                ->concat(
                    Product::query()
                        ->whereNotNull('image')
                        ->where('image', '!=', '')
                        ->where(fn ($q) => $q->whereNull('dijeda')->orWhere('dijeda', false))
                        ->orderBy('nama_akun')
                        ->get(['id', 'nama_akun', 'image'])
                );

            $merek = [];

            foreach ($produk as $p) {
                $nama = self::merek($p->nama_akun);

                if ($nama === '' || isset($merek[Str::lower($nama)])) {
                    continue;
                }

                // Berkas gambarnya harus benar-benar ada. Sebagian baris produk
                // menyimpan nama berkas yang sudah tidak ada di penyimpanan, dan
                // ikon gambar rusak di pita kepercayaan justru menghasilkan
                // kesan sebaliknya — lebih baik satu merek berkurang.
                if (! is_file(public_path('storage/img/Product/'.$p->image))) {
                    continue;
                }

                $merek[Str::lower($nama)] = ['nama' => $nama, 'gambar' => $p->image];
            }

            return array_slice(array_values($merek), 0, $jumlah);
        });
    }

    /** Nama merek dari nama produk lengkap. */
    public static function merek(string $namaProduk): string
    {
        // "Scopus Lisensi + Scopus AI Private" → ambil bagian sebelum tanda +:
        // yang di belakang hanya menerangkan isi paket, bukan merek lain.
        $nama = Str::before($namaProduk, '+');

        $kata = preg_split('/\s+/', trim($nama)) ?: [];

        $sisa = array_values(array_filter(
            $kata,
            fn ($k) => ! in_array(Str::lower(trim($k, '_-')), self::IMBUHAN, true)
        ));

        $bersih = trim(implode(' ', $sisa));

        // Semua katanya imbuhan (misal produk bernama "Akun Premium") — tidak
        // ada merek yang bisa ditunjukkan, jadi jangan tampilkan apa pun.
        if ($bersih === '') {
            return '';
        }

        return self::EJAAN[Str::lower($bersih)] ?? $bersih;
    }
}
