<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataAkun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_akun',
        'username_akun',
        'password_akun',
        'link_login_akun',
        'pj_akun',
        'harga_satuan',
        'deskripsi',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $hidden = [
        'password_akun', // Sembunyikan password dari serialization
    ];

    public function pj()
    {
        return $this->belongsTo(User::class, 'pj_akun');
    }
}
