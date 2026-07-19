<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * File yang diunggah customer SEBELUM membayar — dipakai jasa per halaman
 * (parafrase) untuk menghitung jumlah halaman sehingga harganya bisa
 * ditentukan di muka. Saat checkout, file ini dipindahkan menjadi OrderUpload.
 */
class JasaDraftUpload extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'path',
        'nama_asli',
        'ukuran',
        'mime',
        'jumlah_halaman',
        'kerja_path',
        'kerja_nama',
        'kerja_ukuran',
        'kerja_mime',
        'session_token',
    ];

    protected $casts = [
        'ukuran' => 'integer',
        'jumlah_halaman' => 'integer',
        'kerja_ukuran' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
