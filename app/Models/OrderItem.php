<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
        'data_akun_id',
        'account_username',
        'account_password',
        'account_link',
        'account_notes',
        'start_date',
        'end_date',
        'remaining_days',
        'subscription_status',
        'is_delivered',
        'delivered_at',
        'delivery_status',
        'processed_by',
        'processed_at',
        'processing_notes',
    ];

    protected $casts = [
        'account_password' => 'encrypted',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_delivered' => 'boolean',
        'delivered_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function dataAkun()
    {
        return $this->belongsTo(DataAkun::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('delivery_status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('delivery_status', 'processing');
    }

    public function scopeDelivered($query)
    {
        return $query->where('delivery_status', 'delivered');
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('end_date')
            ->where('end_date', '<', now());
    }

    // Methods
    public function getDurationLabel()
    {
        return "{$this->duration_value} {$this->duration_type}";
    }

    // Hitung end_date berdasarkan start_date dan duration
    public function calculateEndDate()
    {
        if (! $this->start_date) {
            return null;
        }

        $startDate = Carbon::parse($this->start_date);

        if ($this->duration_type === 'tahun') {
            return $startDate->addYears($this->duration_value);
        }

        return $startDate->addMonths($this->duration_value);
    }

    // Update remaining days
    public function updateRemainingDays()
    {
        if (! $this->end_date) {
            $this->remaining_days = null;

            return;
        }

        $endDate = Carbon::parse($this->end_date);
        $today = now();

        if ($today->greaterThan($endDate)) {
            $this->remaining_days = 0;
            $this->subscription_status = 'habis';
        } else {
            $this->remaining_days = $today->diffInDays($endDate);
        }

        $this->saveQuietly();
    }

    // Check apakah subscription aktif
    public function isActive()
    {
        if (! $this->end_date) {
            return false;
        }

        return now()->lessThanOrEqualTo($this->end_date);
    }

    // Check apakah akan expire dalam X hari
    public function isExpiringSoon($days = 7)
    {
        if (! $this->end_date) {
            return false;
        }

        $endDate = Carbon::parse($this->end_date);
        $warningDate = now()->addDays($days);

        return $endDate->between(now(), $warningDate);
    }

    // Get status badge untuk admin
    public function getDeliveryStatusBadge()
    {
        return match ($this->delivery_status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'processing' => '<span class="badge bg-info">Processing</span>',
            'delivered' => '<span class="badge bg-success">Delivered</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getSubscriptionStatusBadge()
    {
        return match ($this->subscription_status) {
            'baru' => '<span class="badge bg-primary">Baru</span>',
            'perpanjang' => '<span class="badge bg-success">Perpanjang</span>',
            'pengganti' => '<span class="badge bg-info">Pengganti</span>',
            'habis' => '<span class="badge bg-danger">Habis</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
