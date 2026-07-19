<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Modal extends Model
{
    use HasFactory, HasUuids;

    protected static function booted(): void
    {
        // Saat setoran modal dihapus, bersihkan cash flow terkait.
        static::deleting(function (self $model) {
            $model->cashFlow()->delete();
        });
    }

    protected $fillable = [
        'tanggal',
        'nominal',
        'jenis',
        'deskripsi',
        'gambar',
        'gambar_list',
        'penginput_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:0',
        'gambar_list' => 'array',
    ];

    /**
     * Semua gambar/bukti top-up sebagai array path. Memakai gambar_list bila ada;
     * jika belum (data lama), jatuh ke kolom tunggal "gambar".
     *
     * @return array<int, string>
     */
    public function getImagesAttribute(): array
    {
        if (! empty($this->gambar_list) && is_array($this->gambar_list)) {
            return array_values($this->gambar_list);
        }

        return $this->gambar ? [$this->gambar] : [];
    }

    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penginput_id');
    }

    public function cashFlow(): MorphOne
    {
        return $this->morphOne(CashFlow::class, 'sourceable');
    }

    public function scopeOperasional($query)
    {
        return $query->where('jenis', 'operasional');
    }

    /**
     * Total modal pembelian akun pada suatu periode (bulan/tahun) —
     * dihitung otomatis dari pengeluaran berjenis 'pembelian_akun'.
     */
    public static function totalPembelianAkun(?int $bulan, ?int $tahun): float
    {
        return (float) Spending::where('jenis_pengeluaran', 'pembelian_akun')
            // Hanya pembelian yang SELESAI = biaya nyata. Sama dengan aturan di
            // fitur Modal & Omset Bersih, supaya angkanya tak pernah berbeda.
            ->where('status', 'completed')
            ->when($tahun, fn ($q) => $q->whereYear('tanggal_transaksi', $tahun))
            ->when($bulan, fn ($q) => $q->whereMonth('tanggal_transaksi', $bulan))
            ->sum('nominal');
    }

    /**
     * Total modal operasional (manual) pada suatu periode.
     */
    public static function totalOperasional(?int $bulan, ?int $tahun): float
    {
        return (float) static::operasional()
            ->when($tahun, fn ($q) => $q->whereYear('tanggal', $tahun))
            ->when($bulan, fn ($q) => $q->whereMonth('tanggal', $bulan))
            ->sum('nominal');
    }
}
