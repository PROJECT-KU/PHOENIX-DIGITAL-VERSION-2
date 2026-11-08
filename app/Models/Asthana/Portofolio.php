<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Portofolio extends Model
{
    use KontenSitus;

    protected $table = 'asthana_portofolio';

    public const KATEGORI = [
        'teknologi' => 'Teknologi',
        'pariwisata' => 'Pariwisata',
    ];

    protected $fillable = [
        'judul', 'slug', 'kategori', 'klien', 'tahun', 'tautan', 'ringkasan',
        'isi', 'sampul', 'galeri', 'unggulan', 'terbit', 'urutan',
    ];

    protected $casts = [
        'galeri' => 'array',
        'unggulan' => 'boolean',
        'terbit' => 'boolean',
        'tahun' => 'integer',
        'urutan' => 'integer',
    ];

    public function scopeTerbit(Builder $q): Builder
    {
        return $q->where('terbit', true);
    }

    /** Urutan tampil di situs: pilihan admin dulu, lalu yang terbaru. */
    public function scopeUrutSitus(Builder $q): Builder
    {
        return $q->orderBy('urutan')->orderByDesc('tahun')->orderByDesc('id');
    }

    public function keKartu(): array
    {
        return [
            'slug' => $this->slug,
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'kategori_label' => self::KATEGORI[$this->kategori] ?? $this->kategori,
            'klien' => $this->klien,
            'tahun' => $this->tahun,
            'ringkasan' => $this->ringkasan,
            'sampul_url' => self::urlGambar($this->sampul),
        ];
    }

    public function keDetail(): array
    {
        return $this->keKartu() + [
            'tautan' => $this->tautan,
            'isi' => $this->isi,
            'galeri' => collect($this->galeri ?? [])->map(fn ($j) => self::urlGambar($j))->filter()->values()->all(),
        ];
    }
}
