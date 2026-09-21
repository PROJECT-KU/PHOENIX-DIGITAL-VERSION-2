<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alamat lama sebuah artikel.
 *
 * Dibuat setiap kali slug berubah, supaya tautan yang sudah beredar di luar
 * (pesan WhatsApp, hasil pencarian, catatan orang) tidak mati begitu saja.
 */
class BlogPostRedirect extends Model
{
    protected $table = 'blog_post_redirects';

    protected $fillable = ['slug_lama', 'blog_post_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
