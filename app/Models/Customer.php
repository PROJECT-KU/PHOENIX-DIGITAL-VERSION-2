<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public const STATUS_MEMBER_ACTIVE = 'active';

    public const STATUS_MEMBER_NONACTIVE = 'non-active';

    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'kode_ref',
        'status_member',
        'point',
        'point_balance',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status_member', $status);
    }

    public function calculateYearlyPoints(): array
    {
        if ($this->status_member !== 'active') {
            return [
                'points' => 0,
                'balance' => 0,
                'total_amount' => 0,
            ];
        }

        $currentYear = now()->year;

        $totalPurchases = Order::where('customer_id', $this->id)
            ->whereYear('created_at', $currentYear)
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->where('points_calculated', false)
            ->where('used_points', false)
            ->sum('total');

        $totalAmount = $totalPurchases + $this->point_balance;

        $newPoints = floor($totalAmount / 50000);

        $newBalance = $totalAmount % 50000;

        return [
            'points' => $newPoints,
            'balance' => $newBalance,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Update poin customer
     */
    public function updatePoints(): void
    {
        $calculation = $this->calculateYearlyPoints();

        $this->update([
            'point' => $this->point + $calculation['points'],
            'point_balance' => $calculation['balance'],
        ]);

        if ($calculation['total_amount'] > 0) {
            Order::where('customer_id', $this->id)
                ->whereYear('created_at', now()->year)
                ->whereIn('status', ['paid', 'processing', 'completed'])
                ->where('points_calculated', false)
                ->where('used_points', false)
                ->update(['points_calculated' => true]);
        }
    }

    /**
     * Gunakan poin (reset ke 0)
     */
    public function usePoints(): bool
    {
        if ($this->point <= 0) {
            return false;
        }

        $this->update([
            'point' => 0,
            'point_balance' => 0,
        ]);

        return true;
    }

    /**
     * Get nilai poin dalam rupiah
     */
    public function getPointValue(): int
    {
        return $this->point * 500;
    }

    /**
     * Generate unique referral code
     */
    public static function generateReferralCode(): string
    {
        $prefix = 'PDW_';

        $lastCustomer = self::whereNotNull('kode_ref')
            ->orderBy('kode_ref', 'desc')
            ->first();

        if (! $lastCustomer || ! $lastCustomer->kode_ref) {
            $nextNumber = 1;
        } else {
            $lastNumber = (int) substr($lastCustomer->kode_ref, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tambah poin referral (2 poin untuk setiap referral berhasil)
     */
    public function addReferralPoints(int $points = 2): void
    {
        $this->increment('point', $points);
    }

    /**
     * Check apakah customer sudah pernah bertransaksi
     */
    public function hasTransactions(): bool
    {
        return $this->orders()->exists();
    }

    /**
     * Check apakah customer adalah member active atau tidak aktif (non member)
     */
    public function isMember(): bool
    {
        return $this->status_member === self::STATUS_MEMBER_ACTIVE;
    }

    public function isNonMember(): bool
    {
        return $this->status_member === self::STATUS_MEMBER_NONACTIVE;
    }
}
