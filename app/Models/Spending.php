<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spending extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'tanggal_transaksi',
        'nominal',
        'deskripsi',
        'status',
        'penginput_id',
        'pic_pembeli_id',
        'jenis_pengeluaran',
        'id_transaksi',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'nominal' => 'decimal:0',
    ];
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    // relationship
    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penginput_id');
    }
    public function picPembeli(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_pembeli_id');
    }

    // scope
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
    }
    public function scopebyJenisPengeluaran($query, $jenis)
    {
        return $query->where('jenis_pengeluaran', $jenis);
    }
    public function scopeByPenginput($query, $userId)
    {
        return $query->where('penginput_id', $userId);
    }
    public function scopeByPicPembeli($query, $userId)
    {
        return $query->where('pic_pembeli_id', $userId);
    }
    public function scopeByIdTransaksi($query, $transaksiId)
    {
        return $query->where('id_transaksi', $transaksiId);
    }

    //format data
    public function getNominalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }
    public function getNamaPenginputAttribute(): string
    {
        return $this->penginput->name ?? '-tidak ada-';
    }
    public function getNamaPicPembeliAttribute(): string
    {
        return $this->picPembeli->name ?? '- tidak ada -';
    }
    public function getTanggalTransaksiFormattedAttribute(): string
    {
        return Carbon::parse($this->tanggal_transaksi)
            ->translatedFormat('d F Y');
    }

    // akses created_at dengan format: 26 September 2025 14:35
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at
            ->translatedFormat('d F Y H:i');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            // Auto generate ID transaksi jika belum ada
            if (empty($model->id_transaksi)) {
                $prefix = $model->jenis_pengeluaran === 'pembelian_akun' ? 'PPA' : 'PLL';
                $model->id_transaksi = self::generateTransactionId($prefix);
            }

            // Pastikan penginput otomatis user login
            if (auth()->check() && empty($model->penginput_id)) {
                $model->penginput_id = auth()->id();
            }
        });

        // Saat data di-update
        static::updating(function ($model) {
            // Jika jenis_pengeluaran berubah, ubah id_transaksi menyesuaikan
            if ($model->isDirty('jenis_pengeluaran')) {
                $newJenis = $model->jenis_pengeluaran;
                $prefix = $newJenis === 'pembelian_akun' ? 'PPA' : 'PLL';
                $model->id_transaksi = self::generateTransactionId($prefix);
            }
        });
    }

    private static function generateTransactionId(string $prefix): string
    {
        $tanggal = now()->format('Ymd'); // contoh: 20251009
        $count = self::whereDate('created_at', today())->count() + 1; // urutan harian
        $nomorUrut = str_pad($count, 3, '0', STR_PAD_LEFT); // 001, 002, dst
        return "{$prefix}-{$tanggal}-{$nomorUrut}";
    }
}
