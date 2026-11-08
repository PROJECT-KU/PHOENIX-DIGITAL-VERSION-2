<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    use KontenSitus;

    protected $table = 'asthana_testimoni';

    protected $fillable = ['nama', 'jabatan', 'perusahaan', 'isi', 'foto', 'bintang', 'tampil', 'urutan'];

    protected $casts = ['tampil' => 'boolean', 'urutan' => 'integer', 'bintang' => 'integer'];
}
