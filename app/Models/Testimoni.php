<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'testimonis';

    protected $fillable = [
        'customer_id',
        'nama',
        'peran',
        'no_hp',
        'pesan',
        'rating',
        'foto',
        'status',
        'source',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * no_hp disembunyikan dari serialisasi — dipakai admin saat memoderasi,
     * tapi tidak boleh ikut bocor ke keluaran publik.
     */
    protected $hidden = [
        'no_hp',
    ];

    /**
     * Pemilik testimoni. NULL = testimoni tamu (nomornya tidak cocok dgn
     * pelanggan mana pun, atau pelanggannya belum punya pesanan 'completed').
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** Testimoni dari pembeli sungguhan yang pesanannya sudah selesai. */
    public function terverifikasi(): bool
    {
        return $this->customer_id !== null;
    }

    /** Dikirim langsung oleh pelanggan (bukan diinput admin). */
    public function dariPelanggan(): bool
    {
        return $this->source === 'customer';
    }

    /** Menunggu moderasi admin — sumber angka badge sidebar. */
    public function scopeMenunggu($query)
    {
        return $query->where('status', 'pending');
    }

    /** Sudah disetujui admin (tampil di publik). */
    public function scopeDisetujui($query)
    {
        return $query->where('status', 'active');
    }

    /** Ditolak/disembunyikan admin. */
    public function scopeDitolak($query)
    {
        return $query->where('status', 'non-active');
    }
}
