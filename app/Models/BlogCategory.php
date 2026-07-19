<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Jumlah artikel yang memakai kategori ini (dicocokkan lewat nama).
     */
    public function articlesCount(): int
    {
        return BlogPost::where('category', $this->name)->count();
    }

    /**
     * Buat slug unik dari nama (mengabaikan record $ignoreId saat rename).
     */
    public static function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
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
}
