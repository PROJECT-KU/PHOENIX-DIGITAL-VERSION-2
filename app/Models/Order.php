<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'subtotal',
        'total',
        'status',
        'payment_method',
        'payment_gateway',
        'payment_reference',
        'payment_url',
        'paid_at',
        'expired_at',
        'customer_notes',
        'admin_notes',
        'referral_code',
        'referrer_id',
        'applied_promos',
        'promo_discount',
        'referral_discount',
        'total_discount',
        'guest_token',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'applied_promos' => 'array',
        'promo_discount' => 'decimal:0',
        'referral_discount' => 'decimal:0',
        'total_discount' => 'decimal:0',
    ];

    // relationship
    public function cashFlow(): MorphOne
    {
        return $this->morphOne(CashFlow::class, 'sourceable');
    }

    public function promos(): BelongsToMany
    {
        return $this->belongsToMany(Promo::class, 'order_promo')
            ->withPivot(['kode_promo', 'tipe_diskon', 'nilai_diskon', 'jumlah_diskon'])
            ->withTimestamps();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function hasPromo(): bool
    {
        return $this->promo_discount > 0 || ! empty($this->applied_promos);
    }

    public function getAppliedPromoCodes(): array
    {
        return $this->applied_promos ? array_column($this->applied_promos, 'kode_promo') : [];
    }

    // Scope untuk filter status
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Check apakah order expired
    public function isExpired()
    {
        return $this->expired_at && now()->greaterThan($this->expired_at);
    }

    // Status badge untuk admin
    public function getStatusBadge()
    {
        return match ($this->status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
            'processing' => '<span class="badge bg-info">Processing</span>',
            'completed' => '<span class="badge bg-primary">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
