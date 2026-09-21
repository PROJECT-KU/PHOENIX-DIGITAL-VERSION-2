<?php

namespace App\Models;

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
