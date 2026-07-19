<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class GajiKaryawans extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id_transaksi',
        'nama_karyawan', // menyimpan user id
        'bank',
        'no_rek',
        'tanggal_transaksi',
        'periode_bulan',
        'periode_tahun',
        'gaji_pokok',
        'bonus_kinerja',
        'bonus_lainnya',
        'task_budget',
        'bonus_penyelesaian_task',
        'tasks',
        'uang_lembur',
        'jam_lembur',
        'jumlah_hadir_offline',
        'uang_hadir_offline',
        'jumlah_hadir_online',
        'uang_hadir_online',
        'tunjangan_kesehatan',
        'tunjangan_thr',
        'tunjangan_ketenagakerjaan',
        'tunjangan_lainnya',
        'tunjangan_transport',
        'tunjangan_makan',
        'potongan',
        'potongan_bpjs_kesehatan',
        'potongan_bpjs_ketenagakerjaan',
        'potongan_pinjaman',
        'pph21',
        'total',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'tasks' => 'array',
    ];

    protected static function booted(): void
    {
        // Saat data gaji dihapus, bersihkan cash flow & pengembalian potongan pinjaman terkait
        static::deleting(function (self $model) {
            \App\Models\Pengembalian::where('source_gaji_id', $model->id)->delete();
            $model->cashFlow()->delete();
        });
    }

    // relationship
    public function cashFlow(): MorphOne
    {
        return $this->morphOne(CashFlow::class, 'sourceable');
    }

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

    /**
     * Hitung ulang total gaji dari nilai kolom tersimpan (Σ pendapatan − Σ potongan).
     * Rumus harus konsisten dengan GajiKaryawansForm::calculateTotal().
     * Dipakai saat bonus penyelesaian task diperbarui dari luar form (halaman pool).
     */
    public function hitungTotalDariKolom(): int
    {
        $pendapatan = (int) $this->gaji_pokok
            + (int) $this->bonus_kinerja
            + (int) $this->bonus_lainnya
            + (int) $this->bonus_penyelesaian_task
            + (int) $this->uang_lembur
            + (int) $this->uang_hadir_offline
            + (int) $this->uang_hadir_online
            + (int) $this->tunjangan_kesehatan
            + (int) $this->tunjangan_thr
            + (int) $this->tunjangan_ketenagakerjaan
            + (int) $this->tunjangan_lainnya
            + (int) $this->tunjangan_transport
            + (int) $this->tunjangan_makan;

        $potongan = (int) $this->potongan
            + (int) $this->potongan_bpjs_kesehatan
            + (int) $this->potongan_bpjs_ketenagakerjaan
            + (int) $this->potongan_pinjaman
            + (int) $this->pph21;

        return $pendapatan - $potongan;
    }

    public function getTotalFormattedAttribute(): string
    {
        return 'Rp '.number_format($this->total ?? 0, 0, ',', '.');
    }

    public function getGajiPokokFormattedAttribute()
    {
        return 'Rp '.number_format($this->gaji_pokok, 0, ',', '.');
    }

    public function getTanggalTransaksiFormattedAttribute(): string
    {
        return Carbon::parse($this->tanggal_transaksi)->locale('id')->translatedFormat('d F Y');
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return Carbon::parse($this->created_at)->locale('id')->translatedFormat('d F Y H:i');
    }

    public function getPeriodeLabelAttribute(): string
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($this->periode_bulan && $this->periode_tahun) {
            return ($namaBulan[(int) $this->periode_bulan] ?? '').' '.$this->periode_tahun;
        }

        return '-';
    }

    // Pengembalian pinjaman yang berasal dari potongan gaji ini
    public function pengembalianPinjaman()
    {
        return $this->hasOne(Pengembalian::class, 'source_gaji_id');
    }

    /**
     * Scope kepemilikan data (row-level security).
     * Dipakai oleh SEMUA jalur baca (tabel, pencarian, total, export PDF, slip)
     * agar data gaji rahasia tidak bocor ke karyawan lain.
     *
     * - user dengan permission "view_all_gajikaryawan" (admin/finance) -> semua data
     * - selain itu -> hanya gaji miliknya sendiri (nama_karyawan = id user) yang
     *   sudah "completed"; gaji "pending" (masih draft/diproses) TIDAK ditampilkan
     *   ke karyawan (dashboard maupun list) sampai difinalkan admin
     * - tidak login -> tidak ada data
     */
    public function scopeVisibleTo($query, ?User $user = null)
    {
        $user ??= auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canViewAll('gajikaryawan')) {
            return $query;
        }

        return $query->where('nama_karyawan', $user->id)
            ->where('status', 'completed');
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
