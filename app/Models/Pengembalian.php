<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Pengembalian extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_pengembalian',
        'tanggal_pengembalian',
        'nominal',
        'deskripsi',
        'status',
        'user_id',
    ];

    protected $casts = [
        'tanggal_pengembalian' => 'date',
        'nominal' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING   = 'pending';
    const STATUS_BERJALAN  = 'berjalan';
    const STATUS_LUNAS     = 'lunas';

    // Relationship ke User (siapa yang input)
    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // === Scopes ===
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_pengembalian', [$startDate, $endDate]);
    }

    public function scopeByPenginput($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // === Accessors (formatted attributes) ===
    public function getNominalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    public function getNamaPenginputAttribute(): string
    {
        return $this->penginput->name ?? '-';
    }

    public function getTanggalPengembalianFormattedAttribute(): string
    {
        return $this->tanggal_pengembalian
            ? Carbon::parse($this->tanggal_pengembalian)->translatedFormat('d F Y')
            : '-';
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at
            ? Carbon::parse($this->created_at)->translatedFormat('d F Y H:i')
            : '-';
    }
}
