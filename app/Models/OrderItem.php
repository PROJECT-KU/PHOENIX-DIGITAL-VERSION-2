<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

class OrderItem extends Model
{
    use HasUuids;

    protected static function booted(): void
    {
        // Sinkron biaya modal akun private saat item dibuat/diubah.
        static::saved(function (self $item) {
            if ($item->order) {
                app(\App\Actions\Finance\SyncOrderPrivateCostAction::class)->execute($item->order);
            }
        });

        // Hapus cash flow biaya modal (akun private) saat item dihapus.
        static::deleting(function (self $item) {
            $item->cashFlow()->delete();
        });
    }

    /** Cash flow expense = modal akun private (per order item). */
    public function cashFlow(): MorphOne
    {
        return $this->morphOne(CashFlow::class, 'sourceable');
    }

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_description',
        'product_image',
        'duration_type',
        'duration_value',
        'addons',
        'addons_total',
        'jumlah_halaman',
        'halaman_dikecualikan',
        'halaman_dihitung',
        'bonus_duration_value',
        'bonus_duration_type',
        'price',
        'quantity',
        'subtotal',
        'data_akun_id',
        'account_username',
        'account_password',
        'account_link',
        'account_notes',
        'bonus_description',
        'bonus_file',
        'start_date',
        'end_date',
        'remaining_days',
        'subscription_status',
        'is_delivered',
        'delivered_at',
        'delivery_status',
        'habis_notified_at',
        'processed_by',
        'processed_at',
        'processing_notes',
    ];

    protected $casts = [
        'addons' => 'array',
        'addons_total' => 'integer',
        'jumlah_halaman' => 'integer',
        'halaman_dihitung' => 'integer',
        'account_password' => 'encrypted',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_delivered' => 'boolean',
        'delivered_at' => 'datetime',
        'processed_at' => 'datetime',
        'habis_notified_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function dataAkun()
    {
        return $this->belongsTo(DataAkun::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function ebooks()
    {
        return $this->belongsToMany(Ebook::class, 'order_item_ebook')->withTimestamps();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('delivery_status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('delivery_status', 'processing');
    }

    public function scopeDelivered($query)
    {
        return $query->where('delivery_status', 'delivered');
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('end_date')
            ->where('end_date', '<', now());
    }

    /**
     * Daftar halaman yang dilewati dalam bentuk ringkas & mudah dibaca:
     * "1,2,28,29,30,31,32,33" → "1, 2, 28–33". Nomor berurutan digabung
     * jadi rentang agar customer tak perlu memindai deretan angka panjang.
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

    // Methods
    public function getDurationLabel()
    {
        return "{$this->duration_value} {$this->duration_type}";
    }

    // Apakah item ini punya bonus durasi tambahan
    public function hasBonusDuration(): bool
    {
        return $this->bonus_duration_value > 0 && ! empty($this->bonus_duration_type);
    }

    // Label durasi lengkap termasuk bonus, mis. "1 bulan + bonus 2 bulan (total 3 bulan)"
    public function getFullDurationLabel(): string
    {
        $label = "{$this->duration_value} {$this->duration_type}";

        if ($this->hasBonusDuration()) {
            $label .= " + bonus {$this->bonus_duration_value} {$this->bonus_duration_type}";

            // Total hanya ditampilkan jika satuan sama
            if ($this->duration_type === $this->bonus_duration_type) {
                $total = $this->duration_value + $this->bonus_duration_value;
                $label .= " (total {$total} {$this->duration_type})";
            }
        }

        return $label;
    }

    // URL unduh file bonus (ebook) yang diupload admin
    public function getBonusFileUrl(): ?string
    {
        if (! $this->bonus_file) {
            return null;
        }

        return asset('storage/order-bonus/' . $this->bonus_file);
    }

    // Hitung end_date berdasarkan start_date + durasi beli + bonus durasi
    public function calculateEndDate()
    {
        if (! $this->start_date) {
            return null;
        }

        $endDate = Carbon::parse($this->start_date);

        $endDate = $this->duration_type === 'tahun'
            ? $endDate->addYears($this->duration_value)
            : $endDate->addMonths($this->duration_value);

        if ($this->hasBonusDuration()) {
            $endDate = $this->bonus_duration_type === 'tahun'
                ? $endDate->addYears($this->bonus_duration_value)
                : $endDate->addMonths($this->bonus_duration_value);
        }

        return $endDate;
    }

    // Update remaining days
    public function updateRemainingDays()
    {
        if (! $this->end_date) {
            $this->remaining_days = null;

            return;
        }

        $endDate = Carbon::parse($this->end_date);
        $today = now();

        if ($today->greaterThan($endDate)) {
            $this->remaining_days = 0;
            $this->subscription_status = 'habis';
        } else {
            $this->remaining_days = $today->diffInDays($endDate);
        }

        $this->saveQuietly();
    }

    // Check apakah subscription aktif
    public function isActive()
    {
        if (! $this->end_date) {
            return false;
        }

        return now()->lessThanOrEqualTo($this->end_date);
    }

    // Check apakah akun sudah habis (manual status 'habis' ATAU end_date terlewat)
    public function isHabis(): bool
    {
        if ($this->subscription_status === 'habis') {
            return true;
        }

        return $this->end_date && now()->greaterThan($this->end_date);
    }

    // Label sisa masa aktif untuk tampilan admin
    public function getRemainingLabel(): string
    {
        if (! $this->end_date) {
            return '-';
        }

        if ($this->isHabis()) {
            return 'Habis';
        }

        $sisa = (int) ceil(now()->floatDiffInDays($this->end_date, false));

        return $sisa . ' hari lagi';
    }

    // Check apakah akan expire dalam X hari
    public function isExpiringSoon($days = 7)
    {
        if (! $this->end_date) {
            return false;
        }

        $endDate = Carbon::parse($this->end_date);
        $warningDate = now()->addDays($days);

        return $endDate->between(now(), $warningDate);
    }

    // Get status badge untuk admin
    public function getDeliveryStatusBadge()
    {
        return match ($this->delivery_status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'processing' => '<span class="badge bg-info">Processing</span>',
            'delivered' => '<span class="badge bg-success">Delivered</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getSubscriptionStatusBadge()
    {
        return match ($this->subscription_status) {
            'baru' => '<span class="badge bg-primary">Baru</span>',
            'perpanjang' => '<span class="badge bg-success">Perpanjang</span>',
            'pengganti' => '<span class="badge bg-info">Pengganti</span>',
            'habis' => '<span class="badge bg-danger">Habis</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
