<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Template balasan cepat helpdesk. */
class CustomerMessageTemplate extends Model
{
    protected $fillable = ['nama', 'isi', 'urutan'];

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    /** Isi template dengan data tiket: {nama} dan {tiket}. */
    public function untuk(CustomerMessage $pesan): string
    {
        return strtr($this->isi, [
            '{nama}' => $pesan->name,
            '{tiket}' => $pesan->ticket,
        ]);
    }
}
