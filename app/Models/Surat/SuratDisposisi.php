<?php

namespace App\Models\Surat;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Disposisi: perintah tindak lanjut atas sebuah surat.
 *
 * Inilah bagian yang membuat arsip ini bukan cuma lemari. Surat masuk yang
 * hanya dicatat tidak pernah selesai sendiri — yang membuatnya selesai adalah
 * seseorang diberi tugas, dengan tenggat, dan tugas itu ditutup.
 */
class SuratDisposisi extends Model
{
    protected $table = 'surat_disposisi';

    protected $fillable = [
        'surat_id', 'kepada_user_id', 'kepada_nama', 'instruksi',
        'batas', 'status', 'selesai_pada', 'dibuat_oleh',
        'diberitahu_pada', 'telat_diberitahu_pada',
    ];

    protected $casts = [
        'batas' => 'date',
        'selesai_pada' => 'datetime',
        'surat_id' => 'integer',
        'kepada_user_id' => 'integer',
        'diberitahu_pada' => 'datetime',
        'telat_diberitahu_pada' => 'datetime',
    ];

    public const STATUS = [
        'menunggu' => 'Menunggu',
        'dikerjakan' => 'Dikerjakan',
        'selesai' => 'Selesai',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $d) {
            if (blank($d->dibuat_oleh) && auth()->check()) {
                $d->dibuat_oleh = auth()->id();
            }
        });

        /*
         | Disposisi menulis riwayat SURATNYA, bukan riwayat sendiri.
         |
         | Yang dibaca orang adalah "apa yang terjadi pada surat ini", dan
         | perintah tindak lanjut adalah bagian terpenting dari cerita itu.
         */
        static::created(fn (self $d) => $d->surat?->catatJejak(
            'disposisi', 'Disposisi ke '.$d->kepada_teks.': '.Str::limit((string) $d->instruksi, 120)));

        static::updated(function (self $d) {
            if ($d->wasChanged('status')) {
                $d->surat?->catatJejak('disposisi',
                    'Disposisi ke '.$d->kepada_teks.' jadi "'.(self::STATUS[$d->status] ?? $d->status).'".');
            }
        });

        static::deleted(fn (self $d) => $d->surat?->catatJejak(
            'disposisi', 'Disposisi ke '.$d->kepada_teks.' dihapus.'));
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class, 'surat_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepada_user_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function scopeBelumSelesai(Builder $q): Builder
    {
        return $q->whereIn('status', ['menunggu', 'dikerjakan']);
    }

    /**
     * Nama penerima: akun lemon kalau ada, kalau tidak nama yang ditulis
     * tangan. Tidak semua yang didisposisi punya akun, dan menolak nama tanpa
     * akun berarti disposisinya tidak tercatat sama sekali.
     */
    public function getKepadaTeksAttribute(): string
    {
        return $this->penerima?->name
            ?: (trim((string) $this->kepada_nama) ?: 'Tidak dicatat');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getBatasTeksAttribute(): string
    {
        return $this->batas?->locale('id')->translatedFormat('d F Y') ?? 'Tanpa tenggat';
    }

    /**
     * Lewat tenggat dan belum selesai.
     *
     * Dua syarat, bukan satu: disposisi yang sudah selesai bulan lalu tetap
     * punya tenggat di masa lalu, dan menandainya merah membuat seluruh
     * riwayat terlihat seperti kegagalan.
     */
    public function getTelatAttribute(): bool
    {
        return $this->batas !== null
            && $this->status !== 'selesai'
            && $this->batas->isPast();
    }
}
