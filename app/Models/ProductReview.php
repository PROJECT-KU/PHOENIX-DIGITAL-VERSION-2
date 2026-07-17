<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'nama',
        'rating',
        'ulasan',
        'status',
    ];

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
