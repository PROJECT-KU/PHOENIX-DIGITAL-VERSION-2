<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Model;

/** Kutipan karyawan untuk halaman Karir situs Asthana. */
class SuaraKaryawan extends Model
{
    use KontenSitus;

    protected $table = 'asthana_suara_karyawan';

    protected $fillable = ['nama', 'jabatan', 'perusahaan', 'isi', 'foto', 'tampil', 'urutan'];

    protected $casts = ['tampil' => 'boolean', 'urutan' => 'integer'];
}
