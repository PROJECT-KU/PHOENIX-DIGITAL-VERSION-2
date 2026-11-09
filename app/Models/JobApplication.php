<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JobApplication extends Model
{
    use HasFactory;

    protected $table = 'tbl_job_applications';

    protected $fillable = ['job_id', 'name', 'email', 'phone', 'cv_path', 'cover_letter_path', 'portofolio_url', 'sumber_info', 'ip_address'];

    /** Dari mana pelamar tahu lowongan ini (isian opsional formulir situs). */
    public const SUMBER_INFO = [
        'situs' => 'Situs web Asthana',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'linkedin' => 'LinkedIn',
        'jobstreet' => 'Portal lowongan (JobStreet, Glints, dll.)',
        'kampus' => 'Kampus / sekolah',
        'teman' => 'Teman / kenalan',
        'lainnya' => 'Lainnya',
    ];

    /** Lamaran umum (talent pool) tidak terikat ke satu lowongan. */
    public function getPosisiLabelAttribute(): string
    {
        return $this->job?->title ?? 'Lamaran umum';
    }

    public function getSumberInfoLabelAttribute(): ?string
    {
        return self::SUMBER_INFO[$this->sumber_info] ?? null;
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class, 'job_id');
    }

    public function getCvUrlAttribute()
    {
        return Storage::disk('public')->url($this->cv_path);
    }

    public function getCoverLetterUrlAttribute()
    {
        return Storage::disk('public')->url($this->cover_letter_path);
    }
}
