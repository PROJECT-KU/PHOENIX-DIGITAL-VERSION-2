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
        'points_year',
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
        // Pastikan poin tahun lalu sudah kadaluarsa sebelum menambah poin baru.
        $this->applyYearlyExpiry();

        $calculation = $this->calculateYearlyPoints();

        $this->update([
            'point' => $this->point + $calculation['points'],
            'point_balance' => $calculation['balance'],
            'points_year' => now()->year,
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
     * Tanggal kadaluarsa poin: akhir tahun kalender berjalan (31 Desember).
     * Poin di-reset tiap 1 Januari, jadi poin yang ada sekarang berlaku sampai
     * 31 Desember tahun ini.
     */
    public function pointsExpireAt(): \Carbon\Carbon
    {
        return \Carbon\Carbon::create(now()->year, 12, 31)->endOfDay();
    }

    /**
     * Label tanggal kadaluarsa poin (mis. "31 Desember 2026").
     */
    public function pointsExpireLabel(string $format = 'd F Y'): string
    {
        return $this->pointsExpireAt()->locale('id')->translatedFormat($format);
    }

    /**
     * Terapkan kadaluarsa tahunan secara lazy: bila poin milik tahun sebelumnya,
     * nolkan (sudah kadaluarsa). Dipanggil di titik baca/pakai poin sebagai
     * pengaman bila command terjadwal terlewat. Mengembalikan true bila di-reset.
     */
    public function applyYearlyExpiry(): bool
    {
        $currentYear = now()->year;

        if ((int) $this->points_year === $currentYear) {
            return false;
        }

        // Poin lama (tahun sebelumnya) dianggap kadaluarsa. Untuk data lama yang
        // belum punya points_year (null), adopsi ke tahun berjalan tanpa menolkan.
        if ($this->points_year !== null && (int) $this->points_year < $currentYear) {
            $this->point = 0;
            $this->point_balance = 0;
        }

        $this->points_year = $currentYear;
        $this->save();

        return true;
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
        // Poin referral juga masuk tahun berjalan (kadaluarsa akhir tahun ini).
        $this->applyYearlyExpiry();
        $this->increment('point', $points);
        $this->update(['points_year' => now()->year]);
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
