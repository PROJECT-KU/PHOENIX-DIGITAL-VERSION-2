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
        'jenis_pengeluaran'
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'nominal' => 'decimal:2',
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
}
