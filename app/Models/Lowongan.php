<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lowongan extends Model
{
    use HasFactory;

    /** Perusahaan tempat posisi ini berada. */
    public const UNIT = [
        'asthana' => 'PT. Asthana Cipta Mandiri',
        'reka' => 'Reka Technology',
        'orcha' => 'Orcha Journey',
    ];

    public const JENIS = [
        'penuh_waktu' => 'Penuh waktu',
        'kontrak' => 'Kontrak',
        'magang' => 'Magang',
        'freelance' => 'Freelance',
    ];

    public const POLA_KERJA = [
        'kantor' => 'Di kantor',
        'hybrid' => 'Hybrid',
        'remote' => 'Remote',
    ];

    protected $fillable = [
        'title',
        'slug',
        'is_active',
        'requirements',
        'descriptions',
        'unit',
        'jenis',
        'lokasi',
        'pola_kerja',
        'batas_lamaran',
        'ringkasan',
        'gaji',
        'jumlah_posisi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'batas_lamaran' => 'date',
        'jumlah_posisi' => 'integer',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 'active');
    }

    /**
     * Yang boleh tampil & dilamar di situs: aktif DAN batas lamarannya belum
     * lewat. Batas lamaran berlaku sampai akhir harinya (hari itu masih
     * boleh melamar).
     */
    public function scopeTerbuka(Builder $query): Builder
    {
        return $query->active()->where(function (Builder $q) {
            $q->whereNull('batas_lamaran')->orWhereDate('batas_lamaran', '>=', now()->toDateString());
        });
    }

    public function getUnitLabelAttribute(): string
    {
        return self::UNIT[$this->unit] ?? self::UNIT['asthana'];
    }

    /** Masih 'active' tapi batas lamarannya sudah lewat: tidak tampil di situs. */
    public function getLewatBatasAttribute(): bool
    {
        return $this->is_active === 'active'
            && $this->batas_lamaran !== null
            && $this->batas_lamaran->lt(now()->startOfDay());
    }

    public function getJenisLabelAttribute(): ?string
    {
        return self::JENIS[$this->jenis] ?? null;
    }

    public function getPolaKerjaLabelAttribute(): ?string
    {
        return self::POLA_KERJA[$this->pola_kerja] ?? null;
    }

    /** Data publik untuk situs (daftar & detail memakai bentuk yang sama). */
    public function untukSitus(bool $lengkap = false): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'unit' => $this->unit ?: 'asthana',
            'unit_label' => $this->unit_label,
            'jenis' => $this->jenis,
            'jenis_label' => $this->jenis_label,
            'lokasi' => $this->lokasi,
            'pola_kerja' => $this->pola_kerja,
            'pola_kerja_label' => $this->pola_kerja_label,
            'batas_lamaran' => $this->batas_lamaran?->format('Y-m-d'),
            'ringkasan' => $this->ringkasan,
            'gaji' => $this->gaji,
            'jumlah_posisi' => $this->jumlah_posisi,
            'created_at' => $this->created_at?->toISOString(),
            'posted_at' => $this->created_at?->format('Y-m-d'),
        ];

        if ($lengkap) {
            $data['requirements'] = $this->requirements;
            $data['descriptions'] = $this->descriptions;
            $data['is_active'] = $this->is_active;
        }

        return $data;
    }
}
