<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Model;

class Klien extends Model
{
    use KontenSitus;

    protected $table = 'asthana_klien';

    protected $fillable = ['nama', 'logo', 'tautan', 'tampil', 'urutan'];

    protected $casts = ['tampil' => 'boolean', 'urutan' => 'integer'];
}
