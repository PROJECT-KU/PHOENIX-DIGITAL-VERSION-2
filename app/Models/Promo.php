<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'kode_promo',
        'nama_promo',
        'deskripsi',
        'tipe_promo',
        'tipe_diskon',
        'diskon_member_persen',
        'diskon_member_nominal',
        'diskon_non_member_persen',
        'diskon_non_member_nominal',
        'untuk_member',
        'untuk_pembeli_pertama',
        'min_pembelian',
        'mulai_promo',
        'selesai_promo',
        'is_active',
        'prioritas',
        'can_stack_with_other',
        'can_stack_with_referral',
        'can_stack_with_points',
        'show_on_homepage',
        'banner_image',
        'badge_text',
        'badge_color',
        'total_penggunaan',
        'total_diskon_diberikan',
    ];

    protected $casts = [
        'mulai_promo' => 'datetime',
        'selesai_promo' => 'datetime',
        'is_active' => 'boolean',
        'untuk_pembeli_pertama' => 'boolean',
        'can_stack_with_other' => 'boolean',
        'can_stack_with_referral' => 'boolean',
        'can_stack_with_points' => 'boolean',
        'show_on_homepage' => 'boolean',
        'diskon_member_persen' => 'decimal:2',
        'diskon_member_nominal' => 'decimal:0',
        'diskon_non_member_persen' => 'decimal:2',
        'diskon_non_member_nominal' => 'decimal:0',
        'min_pembelian' => 'decimal:0',
        'total_diskon_diberikan' => 'decimal:0',
    ];

    // Relationships
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promo_product')
            ->withTimestamps();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_promo')
            ->withPivot(['kode_promo', 'tipe_diskon', 'nilai_diskon', 'jumlah_diskon'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('mulai_promo', '<=', now())
            ->where('selesai_promo', '>=', now());
    }

    public function scopeFlashSale($query)
    {
        return $query->where('tipe_promo', 'flash_sale');
    }

    public function scopeKodePromo($query)
    {
        return $query->where('tipe_promo', 'kode_promo');
    }

    public function scopeHomepage($query)
    {
        return $query->where('show_on_homepage', true);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->whereHas('products', function ($q) use ($productId) {
            $q->where('product_id', $productId);
        });
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->is_active
            && $this->mulai_promo <= now()
            && $this->selesai_promo >= now();
    }

    public function isExpired(): bool
    {
        return $this->selesai_promo < now();
    }

    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->selesai_promo);
    }

    public function hoursRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInHours($this->selesai_promo);
    }

    public function getTimeRemaining(): array
    {
        if ($this->isExpired()) {
            return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0];
        }

        $diff = now()->diff($this->selesai_promo);

        return [
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
        ];
    }

    public function canBeUsedBy(?Customer $customer): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        // Check member eligibility
        if ($this->untuk_member === 'member_only' && (! $customer || $customer->status_member !== 'active')) {
            return false;
        }

        if ($this->untuk_member === 'non_member_only' && $customer && $customer->status_member === 'active') {
            return false;
        }

        // Check first purchase requirement
        if ($this->untuk_pembeli_pertama && $customer && $customer->hasTransactions()) {
            return false;
        }

        return true;
    }

    public function getDiskonValue(bool $isMember, ?string $type = null): float
    {
        $type = $type ?? $this->tipe_diskon;

        if ($type === 'persen') {
            return $isMember ? (float) $this->diskon_member_persen : (float) $this->diskon_non_member_persen;
        }

        return $isMember ? (float) $this->diskon_member_nominal : (float) $this->diskon_non_member_nominal;
    }

    public function incrementUsage(float $discountAmount): void
    {
        $this->increment('total_penggunaan');
        $this->increment('total_diskon_diberikan', $discountAmount);
    }

    public function scopeAutoPromo($query)
    {
        return $query->where('tipe_promo', 'auto_promo');
    }

    // Scope untuk semua promo otomatis (flash_sale + auto_promo)
    public function scopeAutomaticPromos($query)
    {
        return $query->whereIn('tipe_promo', ['flash_sale', 'auto_promo']);
    }

    // Helper method - cek apakah promo butuh kode
    public function requiresCode(): bool
    {
        return $this->tipe_promo === 'kode_promo';
    }

    // Helper method - cek apakah promo otomatis
    public function isAutomatic(): bool
    {
        return in_array($this->tipe_promo, ['flash_sale', 'auto_promo']);
    }

    // Helper method - untuk display badge text
    public function getDisplayBadge(): string
    {
        if ($this->badge_text) {
            return $this->badge_text;
        }

        return match ($this->tipe_promo) {
            'flash_sale' => 'FLASH SALE',
            'auto_promo' => 'PROMO',
            'kode_promo' => 'KODE PROMO',
            default => 'DISKON'
        };
    }
}
