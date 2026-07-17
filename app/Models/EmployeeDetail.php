<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EmployeeDetail extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Atasan langsung karyawan ini. */
    public function atasan()
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    /**
     * Buat Nomor Induk Karyawan otomatis: "ACM-XXXXXX" (6 karakter acak,
     * tanpa O/0/I/1 agar tidak ambigu). Dijamin unik.
     */
    public static function generateNik(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $s = '';
            for ($i = 0; $i < 6; $i++) {
                $s .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $nik = 'ACM-'.$s;
        } while (self::where('nik', $nik)->exists());

        return $nik;
    }

    /** Tanggal awal karyawan dihitung bekerja (bergabung, atau fallback akun dibuat). */
    public function tanggalMulaiKerja(): ?Carbon
    {
        if ($this->tanggal_bergabung) {
            return Carbon::parse($this->tanggal_bergabung);
        }

        return $this->user?->created_at ? Carbon::parse($this->user->created_at) : null;
    }

    /** Masa kerja terbaca manusia, mis. "2 tahun 3 bulan". Null bila tak diketahui. */
    public function masaKerja(): ?string
    {
        $mulai = $this->tanggalMulaiKerja();
        if (! $mulai) {
            return null;
        }
        if ($mulai->isFuture()) {
            return 'Belum mulai';
        }

        $diff = $mulai->diff(now());
        $bagian = [];
        if ($diff->y) {
            $bagian[] = $diff->y.' tahun';
        }
        if ($diff->m) {
            $bagian[] = $diff->m.' bulan';
        }
        if (! $bagian) {
            $bagian[] = max(0, $diff->days).' hari';
        }

        return implode(' ', $bagian);
    }
}
