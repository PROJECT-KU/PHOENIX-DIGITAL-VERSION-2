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
        'kuota',
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
        'diskon_member_persen' => 'integer',
        'diskon_member_nominal' => 'integer',
        'diskon_non_member_persen' => 'integer',
        'diskon_non_member_nominal' => 'integer',
        'min_pembelian' => 'integer',
        'kuota' => 'integer',
        'total_diskon_diberikan' => 'integer',
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
    /**
     * Masih ada sisa kuota (atau memang tanpa batas).
     * Versi SQL dari kuotaHabis() — dasarnya sama persis: pesanan non-cancelled.
     */
    public function scopeKuotaTersedia($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('promos.kuota')
                ->orWhereRaw('promos.kuota > (
                    SELECT COUNT(*) FROM order_promo
                    INNER JOIN orders ON orders.id = order_promo.order_id
                    WHERE order_promo.promo_id = promos.id
                      AND orders.status <> ?
                )', ['cancelled']);
        });
    }

    /**
     * Promo yang benar-benar bisa dipakai sekarang.
     *
     * Kuota ikut disaring di sini supaya promo yang kuotanya sudah habis
     * BERHENTI dipajang di publik — kalau tidak, flash sale "20 pembeli
     * pertama" yg habis tgl 15 tetap tampil beserta hitung mundur sampai
     * tgl 20, padahal di kasir sudah ditolak. Itu menipu pembeli.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('mulai_promo', '<=', now())
            ->where('selesai_promo', '>=', now())
            ->kuotaTersedia();
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

        // Kuota habis -> promo tidak bisa dipakai lagi. Dicek di sini (bukan hanya
        // di validateKodePromo) supaya SEMUA jalur ikut terjaga: flash sale,
        // auto promo, promo produk, maupun kode promo.
        if ($this->kuotaHabis()) {
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

    /* ===== Kuota promo ===== */

    /**
     * Jumlah slot kuota yang sudah terpakai.
     *
     * Dihitung dari pesanan NYATA (pivot order_promo), BUKAN dari kolom
     * total_penggunaan — kolom itu terbukti bisa melenceng (pernah tercatat 11
     * padahal pesanan aslinya cuma 9), sehingga tidak layak jadi dasar kuota.
     *
     * Pesanan 'cancelled' tidak dihitung -> slotnya kembali ke kuota.
     * JOIN ke orders sekaligus membuang baris pivot yatim (pesanan terhapus).
     */
    public function kuotaTerpakai(): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('order_promo')
            ->join('orders', 'orders.id', '=', 'order_promo.order_id')
            ->where('order_promo.promo_id', $this->id)
            ->where('orders.status', '!=', 'cancelled')
            ->count();
    }

    /** NULL = promo tanpa batas kuota. */
    public function sisaKuota(): ?int
    {
        return $this->kuota === null ? null : max($this->kuota - $this->kuotaTerpakai(), 0);
    }

    public function kuotaHabis(): bool
    {
        return $this->kuota !== null && $this->kuotaTerpakai() >= $this->kuota;
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
}
