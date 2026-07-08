<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Atasan langsung karyawan ini. */
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }
}
