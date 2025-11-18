<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Support\Str;


class Loan extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_peminjam',
        'tanggal_peminjam',
        'nominal',
        'deskripsi',
        'status',
        'user_id',
        'id_transaksi',
    ];

    protected $casts = [
        'tanggal_peminjam' => 'date',
        'nominal' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING   = 'pending';
    const STATUS_BERJALAN  = 'berjalan';
    const STATUS_LUNAS     = 'lunas';

    // Relationship
    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_peminjam', [$startDate, $endDate]);
    }

    public function scopeByPenginput($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByIdTransaksi($query, $transaksiId)
    {
        return $query->where('id_transaksi', $transaksiId);
    }

    // Accessors (formatted attributes)
    public function getNominalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    public function getNamaPenginputAttribute(): string
    {
        return $this->penginput->name ?? '-';
        // return auth()->check() 
        // ? auth()->user()->name 
        // : '-tidak ada-';
    }

    public function getTanggalPeminjamFormattedAttribute(): string
    {
        return $this->tanggal_peminjam
            ? Carbon::parse($this->tanggal_peminjam)->translatedFormat('d F Y')
            : '-';
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at
            ? Carbon::parse($this->created_at)->translatedFormat('d F Y H:i')
            : '-';
    }

    public function getTotalBorrowerLoanFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total_borrower_loan, 0, ',', '.');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // UUID untuk primary key
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Auto generate ID transaksi (misal TRX-20251009-001)
            if (empty($model->id_transaksi)) {
                $prefix = 'PMJ-' . now()->format('Ymd');
                $last = static::whereDate('created_at', now()->toDateString())
                    ->orderBy('created_at', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($last && preg_match('/-(\d+)$/', $last->id_transaksi, $matches)) {
                    $nextNumber = (int) $matches[1] + 1;
                }

                $model->id_transaksi = $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }

            // Pastikan user_id otomatis dari auth()
            if (auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }
}
