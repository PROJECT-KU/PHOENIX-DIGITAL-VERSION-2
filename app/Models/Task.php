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
        'group_id',
        'user_id',
        'assigned_by',
        'created_by',
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

    /** Pemberi task. NULL = dibuat admin dari Penyelesaian Task. */
    public function pemberi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Pembuat task — dipakai HANYA untuk menampilkan nama pemberi ketika task
     * dibuat admin (assigned_by NULL). Tidak memengaruhi rantai kelola bawahan
     * yang tetap berpatokan pada assigned_by.
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    /**
     * Komentar di level GROUP (dibagikan semua sub-task dalam grup ini).
     * Task multi-penerima berbagi satu diskusi via group_id.
     */
    public function groupComments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'group_id', 'group_id')->oldest();
    }

    /** Semua sub-task dalam grup yang sama (termasuk dirinya). */
    public function groupSiblings()
    {
        return static::where('group_id', $this->group_id);
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

        // Karyawan melihat:
        //  - task miliknya sendiri (user_id), DAN
        //  - task yang diberikan oleh dirinya ATAU oleh siapa pun di bawahannya
        //    (downline), sehingga atasan memantau seluruh rantai di bawahnya, DAN
        //  - SEMUA sub-task dalam grup di mana ia menjadi anggota, sehingga sesama
        //    penerima 1 task (folder) bisa saling melihat progres.
        // Task dari admin (Penyelesaian Task) ber-assigned_by NULL, jadi TIDAK
        // ikut ter-scope ke atasan — hanya anggota grupnya yang melihatnya.
        $giverIds = array_merge([$user->id], $user->bawahanIds());

        return $query->where(function ($q) use ($user, $giverIds) {
            $q->where('user_id', $user->id)
                ->orWhereIn('assigned_by', $giverIds)
                ->orWhereIn('group_id', function ($sub) use ($user) {
                    $sub->select('group_id')->from('tasks')->where('user_id', $user->id);
                });
        });
    }

    /**
     * Status bonus DITURUNKAN otomatis dari waktu penyelesaian vs deadline.
     * - selesai <= deadline  -> tepat_waktu (100%)
     * - selesai >  deadline  -> terlambat (alokasi x bobot/4 x faktor telat)
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
     * Berapa HARI selesai melewati deadline. 0 = tepat waktu / belum selesai.
     * Dipakai BonusTaskPeriodeAction untuk penalti bertingkat (ada masa tenggang).
     */
    public function hariTerlambat(): int
    {
        if ($this->progress !== 'selesai' || ! $this->completed_at || ! $this->deadline_selesai) {
            return 0;
        }

        $batas = $this->deadline_selesai->copy()->startOfDay();
        $selesai = $this->completed_at->copy()->startOfDay();

        if ($selesai->lte($batas)) {
            return 0;
        }

        // round(), BUKAN (int): Carbon 3 mengembalikan float. Kalau timezone
        // sewaktu-waktu diganti ke zona ber-DST, 6 hari bisa jadi 5.958 dan
        // (int) memotongnya ke 5 -> lolos dari penalti. Ini soal uang.
        return (int) round($batas->diffInDays($selesai));
    }

    /**
     * Periode gaji yang menanggung bonus task ini.
     * Dasarnya tanggal SELESAI — bonus ikut gaji periode saat task benar-benar
     * rampung (mis. deadline 10 Jun tapi selesai 30 Jun -> periode Juli, sebab
     * gaji Juni sudah dibayar 20 Jun). Belum selesai -> pakai deadline sbg perkiraan.
     *
     * @return array{bulan: int, tahun: int}
     */
    public function periodeGaji(): array
    {
        return \App\Support\PeriodeGaji::dariTanggal(
            $this->completed_at ?? $this->deadline_selesai ?? now()
        );
    }

    /** Sudah melewati deadline & belum selesai. */
    public function isLewatDeadline(): bool
    {
        if ($this->progress === 'selesai') {
            return false;
        }
        $batas = $this->deadline_selesai?->endOfDay();

        return (bool) ($batas && now()->gt($batas));
    }

    /**
     * Terkunci bila sudah selesai, ATAU sudah melewati deadline dan BELUM
     * dikerjakan (tidak selesai — hangus). Jika sudah "dikerjakan" saat lewat
     * deadline, TIDAK terkunci: karyawan masih boleh "Tandai Selesai Melebihi
     * Deadline" (bonus dikurangi otomatis via bonusStatus 'terlambat').
     */
    public function isLocked(): bool
    {
        if ($this->progress === 'selesai') {
            return true;
        }

        if ($this->isLewatDeadline()) {
            return $this->progress !== 'dikerjakan';
        }

        return false;
    }

    public function bobotPoin(): int
    {
        return self::BOBOT_POIN[$this->bobot] ?? 1;
    }
}
