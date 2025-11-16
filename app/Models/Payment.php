<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'payment_gateway',
        'transaction_id',
        'payment_method',
        'payment_channel',
        'amount',
        'status',
        'gateway_response',
        'payment_url',
        'qr_url',
        'va_number',
        'expired_at',
        'paid_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Check if payment expired
    public function isExpired()
    {
        return $this->expired_at && now()->greaterThan($this->expired_at);
    }

    // Get status badge
    public function getStatusBadge()
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'settlement' => '<span class="badge bg-success">Paid</span>',
            'expire' => '<span class="badge bg-secondary">Expired</span>',
            'cancel' => '<span class="badge bg-danger">Cancelled</span>',
            'deny' => '<span class="badge bg-danger">Denied</span>',
            'refund' => '<span class="badge bg-info">Refunded</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
