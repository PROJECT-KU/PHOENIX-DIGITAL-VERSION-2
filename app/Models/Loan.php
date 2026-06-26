<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Loan extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nama_peminjam',
        'tanggal_peminjam',
        'nominal',
        'deskripsi',
        'status',
        'user_id',
        'id_transaksi',
    ];

    protected $casts = [
        'tanggal_peminjam' => 'date',
        'nominal' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';

    const STATUS_BERJALAN = 'berjalan';

    const STATUS_LUNAS = 'lunas';

    // Relationship
    public function cashFlow(): MorphOne
    {
        return $this->morphOne(CashFlow::class, 'sourceable');
    }

    public function penginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope kepemilikan data (row-level security) untuk peminjaman.
     * Dipakai SEMUA jalur baca (tabel, pencarian, total, export PDF) agar
     * data pinjaman tidak bocor antar karyawan.
     *
     * Catatan: kolom `user_id` di tabel ini = PENGINPUT (admin/finance), bukan
     * peminjam. Peminjam diidentifikasi lewat `nama_peminjam` (disalin dari
     * nama user terpilih saat input), sehingga scope karyawan dicocokkan ke nama.
     *
     * - punya "view_all_loan" (admin/finance) -> semua data
     * - selain itu -> hanya pinjaman atas namanya sendiri
     * - tidak login -> tidak ada data
     */
    public function scopeVisibleTo($query, ?User $user = null)
    {
        $user ??= auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->canViewAll('loan')) {
            return $query;
        }

        return $query->where('nama_peminjam', $user->name);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_peminjam', [$startDate, $endDate]);
    }

    public function scopeByPenginput($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByIdTransaksi($query, $transaksiId)
    {
        return $query->where('id_transaksi', $transaksiId);
    }

    // Accessors (formatted attributes)
    public function getNominalFormattedAttribute(): string
    {
        return 'Rp '.number_format($this->nominal, 0, ',', '.');
    }

    public function getNamaPenginputAttribute(): string
    {
        return $this->penginput->name ?? '-';
        // return auth()->check()
        // ? auth()->user()->name
        // : '-tidak ada-';
    }

    public function getTanggalPeminjamFormattedAttribute(): string
    {
        return $this->tanggal_peminjam
            ? Carbon::parse($this->tanggal_peminjam)->translatedFormat('d F Y')
            : '-';
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at
            ? Carbon::parse($this->created_at)->translatedFormat('d F Y H:i')
            : '-';
    }

    public function getTotalBorrowerLoanFormattedAttribute()
    {
        return 'Rp '.number_format($this->total_borrower_loan, 0, ',', '.');
    }

    /**
     * Tentukan status peminjaman OTOMATIS dari total pinjaman vs total pengembalian.
     * - belum ada pengembalian       -> pending
     * - sudah mencicil sebagian       -> berjalan
     * - pengembalian >= total pinjaman -> lunas
     */
    public static function statusDari($totalPinjaman, $totalPengembalian): string
    {
        $totalPinjaman = (float) $totalPinjaman;
        $totalPengembalian = (float) $totalPengembalian;

        if ($totalPinjaman <= 0 || $totalPengembalian <= 0) {
            return self::STATUS_PENDING;
        }

        if ($totalPengembalian < $totalPinjaman) {
            return self::STATUS_BERJALAN;
        }

        return self::STATUS_LUNAS;
    }

    /**
     * Peta status otomatis per nama peminjam (lintas waktu / seluruh riwayat).
     * Mengembalikan array ['Nama Peminjam' => 'pending|berjalan|lunas'].
     * Dipakai bersama oleh daftar peminjaman, pengembalian, dan export.
     */
    public static function statusMap(): array
    {
        $pinjaman = static::query()
            ->select('nama_peminjam', DB::raw('SUM(nominal) as total'))
            ->groupBy('nama_peminjam')
            ->pluck('total', 'nama_peminjam');

        $pengembalian = DB::table('pengembalians')
            ->select('nama_pengembalian', DB::raw('SUM(nominal) as total'))
            ->groupBy('nama_pengembalian')
            ->pluck('total', 'nama_pengembalian');

        $map = [];
        foreach ($pinjaman as $nama => $total) {
            $map[$nama] = static::statusDari($total, $pengembalian[$nama] ?? 0);
        }

        return $map;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // UUID untuk primary key
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Auto generate ID transaksi (misal TRX-20251009-001)
            if (empty($model->id_transaksi)) {
                $prefix = 'PMJ-'.now()->format('Ymd');
                $last = static::whereDate('created_at', now()->toDateString())
                    ->orderBy('created_at', 'desc')
                    ->first();

                $nextNumber = 1;
                if ($last && preg_match('/-(\d+)$/', $last->id_transaksi, $matches)) {
                    $nextNumber = (int) $matches[1] + 1;
                }

                $model->id_transaksi = $prefix.'-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }

            // Pastikan user_id otomatis dari auth()
            if (auth()->check()) {
                $model->user_id = auth()->id();
            }
        });

        // Saat peminjaman dihapus, hapus juga catatan cash flow terkait
        static::deleting(function ($model) {
            $model->cashFlow()->delete();
        });
    }
}
