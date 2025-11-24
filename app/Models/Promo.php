<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_promo',
        'diskon_rupiah',
        'diskon_persen'
    ];

    public function numberFormatted($value)
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    public function formatted($field)
    {
        if (! isset($this->{$field})) {
            return '-';
        }

        return $this->numberFormatted($this->{$field});
    }

    public function percentFormatted($value)
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return '-';
        }

        $value = (float) $value;
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%';
    }
}
