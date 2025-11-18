<?php

namespace App\Models;

use App\Models\User;
use App\Models\DataAkun;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemesananRsc extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pemesanan_rsc';

    protected $fillable = [
        'id_transaksi',
        'nama_camp',
        'batch_camp',
        'tanggal_mulai_camp',
        'tanggal_akhir_camp',
        'nama_pembeli',
        'telp_pembeli',
        'jumlah_pemesanan',
        'tanggal_pemesanan',
        'tanggal_berakhir',
        'harga_satuan',
        'total',
        'akun',
        'username',
        'password',
        'link_akses',
        'pic',
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
    public function users()
    {
        return $this->belongsTo(User::class, 'pic', 'id');
    }
    public function dataakun()
    {
        return $this->belongsTo(DataAkun::class, 'akun', 'id');
    }

    // Helper / accessor untuk menampilkan nama karyawan
    public function getNamaTextAttribute(): string
    {
        return $this->nama?->name ?? '-tidak ada-';
    }

    public function getTotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total ?? 0, 0, ',', '.');
    }

    public function getTanggalPemesananFormattedAttribute(): string
    {
        return Carbon::parse($this->tanggal_pemesanan)->translatedFormat('d F Y');
    }

    public function getTanggalBerakhirFormattedAttribute(): string
    {
        return Carbon::parse($this->tanggal_berakhir)->translatedFormat('d F Y');
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
        return $query->whereBetween('tanggal_pemesanan', [$startDate, $endDate]);
    }

    // Scope filter karyawan
    public function scopeByPembeli($query, $pembeli)
    {
        return $query->where('nama', $pembeli);
    }

    // Scope filter id transaksi
    public function scopeByIDTransaksi($query, $idtransaksi)
    {
        return $query->where('id_transaksi', $idtransaksi);
    }
}
