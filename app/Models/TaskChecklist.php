<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu langkah di dalam sebuah task.
 *
 * Sengaja TIDAK ikut menentukan bobot maupun poin: bobot adalah penilaian
 * pemberi task atas beratnya pekerjaan, sedangkan checklist adalah cara
 * penerimanya memecah pekerjaan itu. Kalau centang ikut menambah poin,
 * siapa pun bisa menaikkan bonusnya sendiri dengan memecah langkahnya
 * lebih halus.
 */
class TaskChecklist extends Model
{
    use HasUuids;

    protected $fillable = ['task_id', 'teks', 'selesai', 'urutan'];

    protected $casts = ['selesai' => 'boolean'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
