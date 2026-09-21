<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hitungan baca satu artikel pada satu hari.
 *
 * Disimpan per hari, bukan per kunjungan: satu baris per artikel per tanggal
 * sudah cukup untuk menjawab "sedang ramai atau tidak" tanpa menyimpan jejak
 * siapa pun yang membacanya.
 */
class BlogPostRead extends Model
{
    protected $table = 'blog_post_reads';

    protected $fillable = ['blog_post_id', 'tanggal', 'jumlah'];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
