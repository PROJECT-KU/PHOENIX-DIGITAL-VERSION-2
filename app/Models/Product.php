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
        'harga_perbulan',
        'harga_5_perbulan',
        'harga_10_perbulan',
        'harga_pertahun',
        'deskripsi',
    ];
}
