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
        'anonim',
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
        'anonim' => 'boolean',
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
     * Nama untuk TAMPILAN PUBLIK — disamarkan bila pengirim memilih anonim.
     *
     * Kolom `nama` selalu berisi nama ASLI (dipakai admin saat memoderasi);
     * penyamaran terjadi di sini, saat ditampilkan. Semua tampilan publik wajib
     * memakai accessor ini, JANGAN `nama` langsung — kalau tidak, nama asli
     * pengirim anonim bocor ke pengunjung.
     */
    public function getNamaPublikAttribute(): string
    {
        return $this->anonim ? self::samarkanNama((string) $this->nama) : trim((string) $this->nama);
    }

    /** "Berto" → "B•••" : hanya huruf depan yang tampil untuk testimoni anonim. */
    public static function samarkanNama(string $nama): string
    {
        $huruf = mb_substr(trim($nama), 0, 1);

        return $huruf === '' ? 'Anonim' : mb_strtoupper($huruf).'•••';
    }

    /**
     * Nomor dalam bentuk INTI (tanpa awalan 0/62/+62) untuk dipakai sebagai kata
     * pencarian di Data Pelanggan.
     *
     * Nomor yang sama bisa tersimpan beda format di dua tabel ("0895…" di sini,
     * "62895…" di pelanggan). Bentuk inti adalah potongan yang PASTI ada di
     * keempat varian, jadi pencarian LIKE-nya tetap ketemu apa pun formatnya.
     */
    public function getNoHpCariAttribute(): string
    {
        return Customer::normalisasiNoHp($this->no_hp);
    }

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
