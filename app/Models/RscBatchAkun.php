<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RscBatchAkun extends Model
{
    protected $fillable = [
        'nama_camp',
        'batch_camp',
        'akun_id',
        'nama_akun',
        'username',
        'password',
        'link_akses',
    ];

    public function dataakun(): BelongsTo
    {
        return $this->belongsTo(DataAkun::class, 'akun_id');
    }
}
