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
        // Sebelumnya TIDAK ada di sini, padahal CustomerForm & aktifkanMember()
        // sama-sama mengisinya saat member diaktifkan — Eloquent membuangnya
        // diam-diam, sehingga member_since SELALU NULL (lihat PDW_0001 & PDW_0002).
        'member_since',
        'point',
        'point_balance',
        'points_year',
    ];

    protected $casts = [
        'member_since' => 'datetime',
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
    /**
     * Tukarkan poin sebesar nilai diskon yang BENAR-BENAR dipakai.
     *
     * Sebelumnya seluruh poin dinolkan berapa pun yang terpakai, sehingga
     * customer berpoin Rp100.000 yang belanja Rp30.000 kehilangan sisa
     * Rp70.000-nya. Kini hanya poin senilai diskonnya yang dipotong.
     *
     * point_balance sengaja TIDAK disentuh — itu sisa rupiah menuju poin
     * berikutnya (hasil belanja), bukan saldo yang ditukarkan.
     *
     * @param  int  $nilaiDipakai  Rupiah diskon yang dipakai; 0 = pakai semua
     *                             (perilaku lama, untuk pemanggil yang belum
     *                             mengirim nilainya).
     */
    public function usePoints(int $nilaiDipakai = 0): bool
    {
        if ($this->point <= 0) {
            return false;
        }

        $poinTerpakai = $nilaiDipakai > 0
            ? min($this->point, (int) ceil($nilaiDipakai / self::NILAI_PER_POIN))
            : $this->point;

        $this->update(['point' => max(0, $this->point - $poinTerpakai)]);

        return true;
    }

    /**
     * Get nilai poin dalam rupiah
     */
    public function getPointValue(): int
    {
        return $this->point * self::NILAI_PER_POIN;
    }

    /** Nilai tukar 1 poin dalam rupiah. */
    public const NILAI_PER_POIN = 500;

    /* ===== Pencocokan nomor WhatsApp (dipakai verifikasi ulasan) ===== */

    /**
     * Samakan nomor ke bentuk inti: buang non-digit, lalu awalan 62 / 0.
     * "+62 895-421-735441", "0895421735441", "62895421735441" -> "895421735441".
     * Logika yang sama dgn TrackOrder::localPhone().
     */
    public static function normalisasiNoHp($nomor): string
    {
        $d = preg_replace('/\D/', '', (string) $nomor);
        $d = preg_replace('/^62/', '', $d);

        return preg_replace('/^0/', '', $d);
    }

    /**
     * Cari pelanggan dari nomor apa pun formatnya.
     *
     * Sengaja memakai whereIn dgn daftar varian — BUKAN memuat semua pelanggan
     * lalu menyaring di PHP. Toko ini punya 10rb+ pelanggan; menyaring di PHP
     * berarti menarik semuanya ke memori tiap kali ada yang menulis ulasan.
     */
    public static function cariDariNoHp($nomor): ?self
    {
        $inti = self::normalisasiNoHp($nomor);

        if ($inti === '') {
            return null;
        }

        return self::whereIn('no_hp', [
            $inti,
            '0'.$inti,
            '62'.$inti,
            '+62'.$inti,
        ])->first();
    }

    /**
     * Jumlah pesanan yang benar-benar SELESAI (bukan paid/pending/cancel).
     * Inilah palang jadi member & angka pada label "Sudah belanja N×".
     */
    public function jumlahBelanjaSelesai(): int
    {
        return $this->orders()->where('status', 'completed')->count();
    }

    /**
     * Aktifkan keanggotaan — langkahnya sama persis dgn form Pelanggan di admin:
     * status active, terbitkan kode referral & member_since bila belum punya,
     * lalu hitung poin dari transaksi tahun ini.
     *
     * Idempotent: yang sudah member dilewati, jadi aman dipanggil berulang
     * (mis. admin menyetujui ulang ulasan yang sama).
     *
     * @return bool true bila BARU diaktifkan sekarang
     */
    public function aktifkanMember(): bool
    {
        if ($this->status_member === self::STATUS_MEMBER_ACTIVE) {
            return false;
        }

        $data = ['status_member' => self::STATUS_MEMBER_ACTIVE];

        if (empty($this->kode_ref)) {
            $data['kode_ref'] = self::generateReferralCode();
            $data['member_since'] = now();
        }

        $this->update($data);
        $this->updatePoints();

        return true;
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
