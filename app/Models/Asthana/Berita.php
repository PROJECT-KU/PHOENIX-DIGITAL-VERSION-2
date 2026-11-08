<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    use KontenSitus;

    protected $table = 'asthana_berita';

    protected $fillable = ['judul', 'slug', 'kategori', 'ringkasan', 'isi', 'sampul', 'terbit_at'];

    protected $casts = ['terbit_at' => 'datetime'];

    /** Sudah boleh dibaca publik: punya tanggal terbit yang tidak di masa depan. */
    public function scopeTerbit(Builder $q): Builder
    {
        return $q->whereNotNull('terbit_at')->where('terbit_at', '<=', now());
    }

    /** 'draf' | 'terjadwal' | 'terbit' */
    public function keadaan(): string
    {
        if (! $this->terbit_at) {
            return 'draf';
        }

        return $this->terbit_at->isFuture() ? 'terjadwal' : 'terbit';
    }

    public function keKartu(): array
    {
        return [
            'slug' => $this->slug,
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'ringkasan' => $this->ringkasan,
            'sampul_url' => self::urlGambar($this->sampul),
            'terbit_at' => $this->terbit_at?->toIso8601String(),
        ];
    }

    public function keDetail(): array
    {
        return $this->keKartu() + ['isi' => $this->isi];
    }
}
