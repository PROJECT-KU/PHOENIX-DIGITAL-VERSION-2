<?php

namespace App\Models\Surat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hasil pindai surat. Berkasnya di disk PRIVAT, disajikan lewat
 * BerkasPrivatController — pindaian surat bisa memuat nama, alamat, dan nomor
 * telepon orang.
 */
class SuratLampiran extends Model
{
    protected static function booted(): void
    {
        static::created(fn (self $l) => $l->surat?->catatJejak('lampiran', 'Pindaian ditambahkan: '.$l->nama));
        static::deleted(fn (self $l) => $l->surat?->catatJejak('lampiran', 'Pindaian dihapus: '.$l->nama));
    }

    protected $table = 'surat_lampiran';

    protected $fillable = ['surat_id', 'nama', 'jalur', 'ukuran', 'jenis', 'diunggah_oleh'];

    protected $casts = ['ukuran' => 'integer'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class, 'surat_id');
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    /** "1,4 MB" — ukuran yang bisa dibaca tanpa menghitung. */
    public function getUkuranTeksAttribute(): string
    {
        return static::ukuranTeks($this->ukuran);
    }

    /**
     * Bentuk statis dari accessor di atas.
     *
     * Dipakai juga untuk berkas yang BELUM tersimpan — antrean pindaian di
     * borang masih berupa berkas sementara, belum punya baris di tabel ini,
     * dan daftarnya harus menyebut ukuran dengan satuan yang sama persis
     * supaya "1,4 MB" di antrean dan "1,4 MB" di daftar lampiran tidak
     * terbaca sebagai dua angka yang berbeda.
     */
    public static function ukuranTeks($bita): string
    {
        $b = (int) $bita;

        if ($b <= 0) {
            return '-';
        }

        if ($b < 1024) {
            return $b.' B';
        }

        if ($b < 1048576) {
            return number_format($b / 1024, 0, ',', '.').' KB';
        }

        return number_format($b / 1048576, 1, ',', '.').' MB';
    }

    /**
     * Berkas yang bisa DITAMPILKAN langsung di peramban.
     *
     * Peramban hanya tahu menggambar PDF dan gambar. Word, Excel, dan kawannya
     * harus diunduh dulu — dan di hosting ini tidak ada jalan mengubahnya jadi
     * PDF lebih dulu (proc_open dimatikan, jadi LibreOffice tidak bisa
     * dipanggil). Lebih baik dikatakan apa adanya daripada memberi tombol
     * pratinjau yang berujung layar kosong.
     */
    public function getBisaPratinjauAttribute(): bool
    {
        return $this->jenis_ringkas !== 'lain';
    }

    /** 'pdf' | 'gambar' | 'lain' — dipakai jendela pratinjau untuk memilih bentuk. */
    public function getJenisRingkasAttribute(): string
    {
        $jenis = strtolower((string) $this->jenis);

        // Baris lama bisa saja tidak menyimpan jenisnya; namanya masih menyimpan
        // ekstensi, dan itu cukup untuk memutuskan.
        $ekstensi = strtolower(pathinfo((string) $this->nama, PATHINFO_EXTENSION));

        if (str_contains($jenis, 'pdf') || $ekstensi === 'pdf') {
            return 'pdf';
        }

        if (str_starts_with($jenis, 'image/') || in_array($ekstensi, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif'], true)) {
            return 'gambar';
        }

        return 'lain';
    }

    /** Ikon menurut jenis berkasnya, supaya daftar lampiran bisa dipindai mata. */
    public function getIkonAttribute(): string
    {
        $jenis = (string) $this->jenis;

        return match (true) {
            str_contains($jenis, 'pdf') => 'bi-file-earmark-pdf',
            str_starts_with($jenis, 'image/') => 'bi-file-earmark-image',
            str_contains($jenis, 'word'), str_contains($jenis, 'document') => 'bi-file-earmark-word',
            default => 'bi-file-earmark',
        };
    }
}
