<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan perubahan sebuah task: siapa mengubah apa, kapan.
 *
 * Ditulis hanya-tambah (append-only) dan tidak pernah disunting: gunanya
 * justru sebagai rujukan saat ada perselisihan soal bonus, dan catatan yang
 * bisa diubah belakangan tidak menyelesaikan perselisihan apa pun.
 */
class TaskRiwayat extends Model
{
    use HasUuids;

    protected $table = 'task_riwayats';

    protected $fillable = ['task_id', 'user_id', 'aksi', 'dari', 'ke', 'catatan'];

    /** Kalimat siap tampil untuk satu baris riwayat. */
    public function kalimat(): string
    {
        return match ($this->aksi) {
            'dibuat' => 'Task dibuat',
            'status' => 'Status diubah '.($this->dari ?: '—').' → '.($this->ke ?: '—'),
            'tenggat' => 'Tenggat digeser '.($this->dari ?: '—').' → '.($this->ke ?: '—'),
            'dibuka-kembali' => 'Dibuka kembali untuk revisi',
            'berulang' => 'Disalin otomatis dari task berulang',
            default => ucfirst(str_replace('-', ' ', (string) $this->aksi)),
        };
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
