<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Model;

class Angka extends Model
{
    use KontenSitus;

    protected $table = 'asthana_angka';

    protected $fillable = ['label', 'nilai', 'akhiran', 'ikon', 'tampil', 'urutan'];

    protected $casts = ['tampil' => 'boolean', 'urutan' => 'integer', 'nilai' => 'integer'];
}
