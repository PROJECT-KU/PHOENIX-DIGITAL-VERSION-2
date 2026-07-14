<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Pemasukan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id_transaksi',
        'tanggal',
        'nominal',
        'kategori',
        'deskripsi',
        'bukti',
        'penginput_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:0',
        'bukti' => 'array',
    ];

    protected static function booted(): void
    {
        // Saat pemasukan dihapus, bersihkan cash flow & file bukti terkait.
        static::deleting(function (self $model) {
            $model->cashFlow()->delete();

            foreach ((array) $model->bukti as $path) {
                if ($path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
            }
        });
    }

    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penginput_id');
    }

    public function cashFlow(): MorphOne
    {
        return $this->morphOne(CashFlow::class, 'sourceable');
    }
}
