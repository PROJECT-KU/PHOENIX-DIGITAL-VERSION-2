<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'cover',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'views',
        'author',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    /**
     * Route model binding memakai slug (untuk URL publik yang ramah SEO).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Hanya artikel yang sudah dipublikasikan & waktunya sudah tiba.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * [label, kelas lencana, warna, ikon] keadaan artikel.
     *
     * "Terbit" dan "Terjadwal" sama-sama berstatus published di basis data —
     * yang membedakan hanya waktunya sudah tiba atau belum. Dipisahkan di sini
     * supaya seluruh layar memakai istilah yang sama.
     */
    public function keadaan(): array
    {
        if ($this->status !== 'published') {
            return ['Draf', 'is-kuning', '#d97706', 'bi-pencil-square'];
        }

        return $this->published_at && $this->published_at->isFuture()
            ? ['Terjadwal', 'is-biru', '#2563eb', 'bi-clock-history']
            : ['Terbit', 'is-hijau', '#16a34a', 'bi-globe2'];
    }

    /** Perkiraan lama baca (menit) — nama Indonesia untuk readingMinutes(). */
    public function lamaBaca(): int
    {
        return $this->readingMinutes();
    }

    /** Alamat gambar sampul, atau null bila berkasnya memang tidak ada. */
    public function sampulUrl(): ?string
    {
        if (! $this->cover) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->exists('img/blog/'.$this->cover)
            ? asset('storage/img/blog/'.$this->cover)
            : null;
    }

    /**
     * Buat slug unik dari sebuah judul (mengabaikan record $ignoreId saat edit).
     */
    public static function makeSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * Estimasi waktu baca (menit) dari isi artikel.
     */
    public function readingMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->body));

        return max(1, (int) ceil($words / 200));
    }
}
