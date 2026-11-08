<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Model;

class Tim extends Model
{
    use KontenSitus;

    protected $table = 'asthana_tim';

    protected $fillable = ['nama', 'jabatan', 'foto', 'linkedin', 'tampil', 'urutan'];

    protected $casts = ['tampil' => 'boolean', 'urutan' => 'integer'];
}
