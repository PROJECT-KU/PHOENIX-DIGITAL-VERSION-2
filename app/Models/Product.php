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

    // Relationship
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Get available packages
    public function getAvailablePackages()
    {
        $packages = [];

        if ($this->harga_perbulan) {
            $packages[] = [
                'duration_type' => 'bulan',
                'duration_value' => 1,
                'price' => $this->harga_perbulan,
                'label' => 'Paket 1 Bulan',
            ];
        }

        if ($this->harga_5_perbulan) {
            $savings = ($this->harga_perbulan * 5) - $this->harga_5_perbulan;
            $packages[] = [
                'duration_type' => 'bulan',
                'duration_value' => 5,
                'price' => $this->harga_5_perbulan,
                'label' => 'Paket 5 Bulan',
                'savings' => $savings,
            ];
        }

        if ($this->harga_10_perbulan) {
            $savings = ($this->harga_perbulan * 10) - $this->harga_10_perbulan;
            $packages[] = [
                'duration_type' => 'bulan',
                'duration_value' => 10,
                'price' => $this->harga_10_perbulan,
                'label' => 'Paket 10 Bulan',
                'savings' => $savings,
            ];
        }

        if ($this->harga_pertahun) {
            $savings = ($this->harga_perbulan * 12) - $this->harga_pertahun;
            $packages[] = [
                'duration_type' => 'tahun',
                'duration_value' => 1,
                'price' => $this->harga_pertahun,
                'label' => 'Paket 1 Tahun',
                'savings' => $savings,
            ];
        }

        return $packages;
    }

    // Get lowest price
    public function getLowestPrice()
    {
        return min(array_filter([
            $this->harga_perbulan,
            $this->harga_5_perbulan,
            $this->harga_10_perbulan,
            $this->harga_pertahun,
        ]));
    }
}
