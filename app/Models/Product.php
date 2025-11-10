<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_akun',
        'image',
        'harga_awal',
        'harga_perbulan',
        'harga_5_perbulan',
        'harga_10_perbulan',
        'harga_pertahun',
        'deskripsi',
    ];

    // Helper format rupiah
    public function numberFormatted($value)
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    // Fungsi dinamis untuk semua harga
    public function formatted($field)
    {
        if (! isset($this->{$field})) {
            return 'Rp 0';
        }

        return $this->numberFormatted($this->{$field});
    }

    public function scopeLatestLimit($query, $limit = 4)
    {
        return $query->latest()->take($limit);
    }
}
