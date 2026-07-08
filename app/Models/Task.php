<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasUuids;

    public const BOBOT_POIN = ['ringan' => 1, 'sedang' => 2, 'berat' => 3];

    protected $fillable = [
        'user_id',
        'periode_bulan',
        'periode_tahun',
        'nama',
        'deskripsi',
        'task_category_id',
        'task_category_label_id',
        'bobot',
        'deadline_mulai',
        'deadline_selesai',
        'progress',
        'completed_at',
        'assigned_notified_at',
        'deadline_notified_at',
        'overdue_notified_at',
    ];

    protected $casts = [
        'deadline_mulai' => 'date',
        'deadline_selesai' => 'date',
        'completed_at' => 'datetime',
        'assigned_notified_at' => 'datetime',
        'deadline_notified_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(TaskCategoryLabel::class, 'task_category_label_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->oldest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->latest();
    }

    /**
     * Scoping baris: karyawan hanya melihat task miliknya sendiri, kecuali
     * punya izin view_all_task. Meniru pola Presensi::scopeVisibleTo.
     */
    public function scopeVisibleTo($query, ?User $user = null)
    {
        $user ??= auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canViewAll('task')) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    /**
     * Status bonus DITURUNKAN otomatis dari waktu penyelesaian vs deadline.
     * - selesai <= deadline  -> tepat_waktu (100%)
     * - selesai >  deadline  -> terlambat (60%)
     * - belum selesai & lewat deadline -> tidak_selesai (0%)
     * - selain itu (masih berjalan) -> tidak_ada_info (dikecualikan dari pool)
     */
    public function bonusStatus(): string
    {
        $batas = $this->deadline_selesai?->endOfDay();

        if ($this->progress === 'selesai') {
            if ($this->completed_at && $batas && $this->completed_at->lte($batas)) {
                return 'tepat_waktu';
            }

            return 'terlambat';
        }

        if ($batas && now()->gt($batas)) {
            return 'tidak_selesai';
        }

        return 'tidak_ada_info';
    }

    /**
     * Terkunci bila sudah selesai ATAU sudah melewati deadline (tidak selesai).
     * Karyawan tak bisa lagi mengubah status pada kondisi ini.
     */
    public function isLocked(): bool
    {
        if ($this->progress === 'selesai') {
            return true;
        }

        $batas = $this->deadline_selesai?->endOfDay();

        return $batas && now()->gt($batas);
    }

    public function bobotPoin(): int
    {
        return self::BOBOT_POIN[$this->bobot] ?? 1;
    }
}
