<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GajiKaryawans extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id_transaksi',
        'nama_karyawan', // menyimpan user id
        'bank',
        'no_rek',
        'tanggal_transaksi',
        'gaji_pokok',
        'bonus_kinerja',
        'bonus_lainnya',
        'tunjangan_kesehatan',
        'tunjangan_thr',
        'tunjangan_ketenagakerjaan',
        'tunjangan_lainnya',
        'potongan',
        'pph21',
        'total',
        'deskripsi',
        'status'
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
    ];

    /**
     * Relasi ke tabel users.
     * Nama method dibuat 'nama_karyawan' supaya kompatibel dengan kode
     * yang memanggil ->nama_karyawan->name
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nama_karyawan');
    }

    // Helper / accessor untuk menampilkan nama karyawan
    public function getNamaKaryawanTextAttribute(): string
    {
        return $this->nama_karyawan?->name ?? '-tidak ada-';
    }

    public function getTotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total ?? 0, 0, ',', '.');
    }

    public function getGajiPokokFormattedAttribute()
    {
        return 'Rp ' . number_format($this->gaji_pokok, 0, ',', '.');
    }

    public function getTanggalTransaksiFormattedAttribute(): string
    {
        return Carbon::parse($this->tanggal_transaksi)->translatedFormat('d F Y');
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->translatedFormat('d F Y H:i');
    }

    // Scope filter status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope filter tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
    }

    // Scope filter karyawan
    public function scopeByPenginput($query, $karyawanId)
    {
        return $query->where('nama_karyawan', $karyawanId);
    }

    // Scope filter id transaksi
    public function scopeByIDTransaksi($query, $idtransaksi)
    {
        return $query->where('id_transaksi', $idtransaksi);
    }

    // Scope filter nomor rekening
    public function scopeByNorek($query, $norek)
    {
        return $query->where('no_rek', $norek);
    }
}
