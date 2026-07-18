<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrderUpload extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'path',
        'nama_asli',
        'ukuran',
        'mime',
        'status',
        'hasil_path',
        'hasil_nama',
        'hasil_ukuran',
        'hasil_mime',
        'persentase',
        'exclude_bibliografi',
        'exclude_kutipan',
        'exclude_sumber_kecil',
        'ambang_sumber_kecil',
        'catatan',
        'diproses_at',
        'selesai_at',
    ];

    protected $casts = [
        'ukuran' => 'integer',
        'hasil_ukuran' => 'integer',
        'persentase' => 'integer',
        'exclude_bibliografi' => 'boolean',
        'exclude_kutipan' => 'boolean',
        'exclude_sumber_kecil' => 'boolean',
        'diproses_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** Ukuran file MASUK (dari customer) ramah dibaca (KB/MB). */
    public function ukuranLabel(): string
    {
        return $this->formatBytes((int) $this->ukuran);
    }

    /** Ukuran file HASIL (dari admin) ramah dibaca. */
    public function hasilUkuranLabel(): string
    {
        return $this->formatBytes((int) $this->hasil_ukuran);
    }

    private function formatBytes(int $b): string
    {
        if ($b >= 1048576) {
            return round($b / 1048576, 1).' MB';
        }
        if ($b >= 1024) {
            return round($b / 1024).' KB';
        }

        return $b.' B';
    }

    public function sudahSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    public function dibatalkan(): bool
    {
        return $this->status === 'dibatalkan';
    }

    /** Label + gaya badge status (untuk tampilan customer/admin). */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'menunggu' => 'Menunggu diperiksa',
            'diproses' => 'Sedang diperiksa',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusIcon(): string
    {
        return match ($this->status) {
            'menunggu' => 'bi-hourglass-split',
            'diproses' => 'bi-search',
            'selesai' => 'bi-check-circle-fill',
            'dibatalkan' => 'bi-x-circle',
            default => 'bi-question-circle',
        };
    }

    /** Kelas warna Bootstrap (text/bg) sesuai status. */
    public function statusWarna(): string
    {
        return match ($this->status) {
            'menunggu' => 'warning',
            'diproses' => 'info',
            'selesai' => 'success',
            'dibatalkan' => 'secondary',
            default => 'secondary',
        };
    }

    /** Ringkasan setelan exclude (untuk ditampilkan ke admin/customer). */
    public function ringkasanExclude(): string
    {
        $out = $this->daftarExclude();

        return $out ? implode(', ', $out) : 'Tidak ada';
    }

    /**
     * Setelan exclude sebagai array — dipakai tampilan agar bisa dirender
     * satu per satu (chip), bukan sebagai satu baris teks panjang.
     *
     * @return array<int, string>
     */
    public function daftarExclude(): array
    {
        $out = [];
        if ($this->exclude_bibliografi) {
            $out[] = 'Daftar Pustaka';
        }
        if ($this->exclude_kutipan) {
            $out[] = 'Kutipan';
        }
        if ($this->exclude_sumber_kecil) {
            $out[] = 'Source'.($this->ambang_sumber_kecil ? ' < '.$this->ambang_sumber_kecil : '');
        }

        return $out;
    }
}
