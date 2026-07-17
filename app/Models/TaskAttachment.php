<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'task_id',
        'uploaded_by',
        'path',
        'name',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function isImage(): bool
    {
        return $this->path
            && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $this->path) === 1;
    }
}
