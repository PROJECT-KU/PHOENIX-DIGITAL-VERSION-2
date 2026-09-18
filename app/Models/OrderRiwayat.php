<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris jejak pesanan toko. Lihat App\Support\RiwayatPesanan. */
class OrderRiwayat extends Model
{
    protected $table = 'order_riwayat';

    public const UPDATED_AT = null;

    protected $fillable = ['order_id', 'user_id', 'aksi', 'keterangan'];

    protected $casts = ['created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
