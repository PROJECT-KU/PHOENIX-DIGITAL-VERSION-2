<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OrderItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_description',
        'product_image',
        'duration_type',
        'duration_value',
        'price',
        'quantity',
        'subtotal',
        'is_delivered',
        'delivered_at',
        'credentials',
    ];

    protected $casts = [
        'is_delivered' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Get formatted duration
    public function getDurationLabel()
    {
        return "{$this->duration_value} {$this->duration_type}";
    }

    // Encrypt credentials before save
    public function setCredentialsAttribute($value)
    {
        $this->attributes['credentials'] = $value ? encrypt($value) : null;
    }

    // Decrypt credentials
    public function getCredentialsAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }
}
