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
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status_member', $status);
    }

    public function calculateYearlyPoints()
    {
        if ($this->status_member !== 'active') {
            return 0;
        }

        $currentYear = now()->year;

        $memberSince = $this->member_since ?? now();

        $totalPurchases = Order::where('customer_id', $this->id)
            ->whereYear('created_at', $currentYear)
            ->where('created_at', '>=', $memberSince)
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

    public function updatePoints(): void
    {
        $calculation = $this->calculateYearlyPoints();

        if ($calculation['points'] > 0) {
            $this->update([
                'point' => $this->point + $calculation['points'],
                'point_balance' => $calculation['balance'],
            ]);

            Order::where('customer_id', $this->id)
                ->whereYear('created_at', now()->year)
                ->where('created_at', '>=', $this->member_since ?? now())
                ->whereIn('status', ['paid', 'processing', 'completed'])
                ->where('points_calculated', false)
                ->update(['points_calculated' => true]);
        } else {
            $this->update([
                'point_balance' => $calculation['balance'],
            ]);
        }
    }

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

    public function getPointValue(): int
    {
        return $this->point * 500;
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
