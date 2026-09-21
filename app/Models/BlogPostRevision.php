<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu versi isi artikel, disimpan SEBELUM perubahan ditulis.
 *
 * Yang disimpan hanya bagian yang mahal ditulis ulang (judul, ringkasan,
 * isi). Sampul, status, dan jadwal tidak ikut: memulihkan versi lama tidak
 * boleh diam-diam menerbitkan atau menurunkan artikel.
 */
class BlogPostRevision extends Model
{
    protected $table = 'blog_post_revisions';

    protected $fillable = ['blog_post_id', 'user_id', 'title', 'excerpt', 'body'];

    /** Riwayat yang disimpan per artikel; sisanya dibuang saat menyimpan. */
    public const BATAS = 20;

    /** Penanda di awal kolom body untuk isi yang dimampatkan. */
    private const TANDA_MAMPAT = 'gz:';

    /**
     * Isi revisi disimpan TERMAMPAT.
     *
     * Dua puluh salinan naskah utuh per artikel tumbuh cepat: artikel 50 KB
     * dikali 20 versi dikali seratus artikel sudah 100 MB di basis data.
     * gzip pada HTML biasanya menyisakan sekitar sepertiganya.
     *
     * Baris lama yang belum bertanda tetap terbaca apa adanya, jadi
     * perubahan ini tidak perlu memigrasikan data yang sudah ada.
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn ($nilai) => self::lepasMampat($nilai),
            set: fn ($nilai) => self::mampatkan($nilai),
        );
    }

    private static function mampatkan(?string $nilai): ?string
    {
        if ($nilai === null || $nilai === '' || ! function_exists('gzencode')) {
            return $nilai;
        }

        $mampat = @gzencode($nilai, 6);

        if ($mampat === false) {
            return $nilai;
        }

        $terkode = self::TANDA_MAMPAT.base64_encode($mampat);

        // Naskah sangat pendek justru membengkak setelah dimampatkan dan
        // di-base64; simpan apa adanya kalau tidak ada untungnya.
        return strlen($terkode) < strlen($nilai) ? $terkode : $nilai;
    }

    private static function lepasMampat(?string $nilai): ?string
    {
        if ($nilai === null || ! str_starts_with($nilai, self::TANDA_MAMPAT)) {
            return $nilai;
        }

        $mentah = base64_decode(substr($nilai, strlen(self::TANDA_MAMPAT)), true);

        if ($mentah === false) {
            return $nilai;
        }

        $asli = @gzdecode($mentah);

        return $asli === false ? $nilai : $asli;
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function penyunting(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Perkiraan jumlah kata versi ini — untuk membandingkan sekilas. */
    public function jumlahKata(): int
    {
        $teks = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->body)));

        return $teks === '' ? 0 : count(preg_split('/\s+/u', $teks, -1, PREG_SPLIT_NO_EMPTY));
    }
}
