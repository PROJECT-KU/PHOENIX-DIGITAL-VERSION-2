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
        'pdf_path',
        'pdf_nama',
        'status',
        'hasil_path',
        'hasil_nama',
        'hasil_ukuran',
        'hasil_mime',
        'persentase',
        'hasil_ai_path',
        'hasil_ai_nama',
        'hasil_ai_ukuran',
        'hasil_ai_mime',
        'persentase_ai',
        'hasil_docx_path',
        'hasil_docx_nama',
        'hasil_docx_ukuran',
        'hasil_docx_mime',
        'exclude_bibliografi',
        'exclude_kutipan',
        'exclude_cover',
        'exclude_daftar_isi',
        'halaman_dikecualikan',
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
        'hasil_ai_ukuran' => 'integer',
        'persentase_ai' => 'integer',
        'hasil_docx_ukuran' => 'integer',
        'exclude_bibliografi' => 'boolean',
        'exclude_kutipan' => 'boolean',
        'exclude_cover' => 'boolean',
        'exclude_daftar_isi' => 'boolean',
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
        // Bagian dokumen (parafrase)
        if ($this->exclude_cover) {
            $out[] = 'Cover';
        }
        if ($this->exclude_daftar_isi) {
            $out[] = 'Daftar Isi';
        }
        // Dipakai kedua jenis jasa
        if ($this->exclude_bibliografi) {
            $out[] = 'Daftar Pustaka';
        }
        // Khusus pengecekan plagiasi
        if ($this->exclude_kutipan) {
            $out[] = 'Kutipan';
        }
        if ($this->exclude_sumber_kecil) {
            $out[] = 'Source'.($this->ambang_sumber_kecil ? ' < '.$this->ambang_sumber_kecil : '');
        }
        // Nomor halaman yang diminta customer untuk dilewati
        if ($this->halaman_dikecualikan) {
            $out[] = 'Halaman '.$this->halamanDikecualikanRingkas();
        }

        return $out;
    }

    /**
     * Ringkas deret halaman jadi rentang: "1,2,28,29,30,31,32,33" → "1–2, 28–33".
     * Mudah dibaca customer & tim, tanpa deretan angka panjang.
     */
    public function halamanDikecualikanRingkas(): ?string
    {
        if (! $this->halaman_dikecualikan) {
            return null;
        }

        $nomor = collect(preg_split('/\D+/', $this->halaman_dikecualikan, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($n) => (int) $n)
            ->filter(fn ($n) => $n > 0)
            ->unique()->sort()->values()->all();

        if (empty($nomor)) {
            return null;
        }

        $bagian = [];
        $awal = $akhir = $nomor[0];

        foreach (array_slice($nomor, 1) as $n) {
            if ($n === $akhir + 1) {
                $akhir = $n;

                continue;
            }
            $bagian[] = $awal === $akhir ? (string) $awal : $awal.'–'.$akhir;
            $awal = $akhir = $n;
        }
        $bagian[] = $awal === $akhir ? (string) $awal : $awal.'–'.$akhir;

        return implode(', ', $bagian);
    }
}
