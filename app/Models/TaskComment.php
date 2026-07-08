<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskComment extends Model
{
    use HasUuids;

    protected $fillable = [
        'task_id',
        'group_id',
        'user_id',
        'body',
        'type',
        'file_path',
        'file_name',
        'pinned_at',
        'admin_read_at',
        'karyawan_read_at',
    ];

    protected $casts = [
        'pinned_at' => 'datetime',
        'admin_read_at' => 'datetime',
        'karyawan_read_at' => 'datetime',
    ];

    public function isPinned(): bool
    {
        return $this->pinned_at !== null;
    }

    public function scopePinned($query)
    {
        return $query->whereNotNull('pinned_at');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isImage(): bool
    {
        return $this->file_path
            && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $this->file_path) === 1;
    }
}
