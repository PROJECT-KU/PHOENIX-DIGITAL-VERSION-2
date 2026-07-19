<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'order_number',
        'share_token',
        'customer_id',
        'subtotal',
        'total',
        'status',
        'payment_method',
        'payment_gateway',
        'payment_reference',
        'payment_url',
        'bukti_pembayaran',
        'qris_content',
        'qris_trx_id',
        'qris_request_date',
        'paid_at',
        'expired_at',
        'customer_notes',
        'admin_notes',
        'referral_code',
        'referrer_id',
        'applied_promos',
        'promo_discount',
        'referral_discount',
        'total_discount',
        'guest_token',
        'unique_code',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'applied_promos' => 'array',
        'promo_discount' => 'decimal:0',
        'referral_discount' => 'decimal:0',
        'total_discount' => 'decimal:0',
    ];

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->share_token)) {
                do {
                    $token = \Illuminate\Support\Str::random(10);
                } while (self::where('share_token', $token)->exists());
                $order->share_token = $token;
            }
        });
    }

    /**
     * Pesanan ini memuat layanan jasa bertanda $kolom ('pakai_exclude' | 'cek_ai')?
     *
     * Diperiksa pada produknya SENDIRI maupun add-on yang dibeli — cukup salah
     * satu bernilai true. Sifat add-on dibaca dari riwayat pesanan bila ada
     * (paling andal, tak terpengaruh perubahan katalog), lalu jatuh ke katalog
     * lewat id, lalu NAMA — id add-on pesanan lama bisa sudah berubah.
     *
     * Sumber tunggal untuk: panel exclude & syarat bahasa di halaman /cek,
     * serta slot unggah hasil di admin.
     */
    public function punyaLayananJasa(string $kolom): bool
    {
        $this->loadMissing('items.product');

        foreach ($this->items as $item) {
            if (optional($item->product)->butuh_file && $item->product->{$kolom}) {
                return true;
            }

            foreach (($item->addons ?? []) as $addon) {
                if (array_key_exists($kolom, $addon)) {
                    if ($addon[$kolom]) {
                        return true;
                    }

                    continue;
                }

                $katalog = ! empty($addon['id'])
                    ? ProductAddon::find($addon['id'])
                    : null;

                if (! $katalog && ! empty($addon['nama'])) {
                    $katalog = ProductAddon::whereRaw('LOWER(nama) = ?', [mb_strtolower($addon['nama'])])->first();
                }

                if ($katalog && $katalog->{$kolom}) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Pesanan memuat jasa PER HALAMAN (parafrase)? */
    public function adaParafrase(): bool
    {
        $this->loadMissing('items.product');

        return $this->items->contains(fn ($i) => (bool) optional($i->product)->jasaPerHalaman());
    }

    // URL struk publik berbasis token pendek (tanpa expose UUID)
    public function getReceiptUrl(): ?string
    {
        return $this->share_token ? url('/s/'.$this->share_token) : null;
    }

    // relationship
    public function cashFlow(): MorphOne
    {
        return $this->morphOne(CashFlow::class, 'sourceable');
    }

    public function promos(): BelongsToMany
    {
        return $this->belongsToMany(Promo::class, 'order_promo')
            ->withPivot(['kode_promo', 'tipe_diskon', 'nilai_diskon', 'jumlah_diskon'])
            ->withTimestamps();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /** File yang diunggah customer untuk pesanan jasa (mis. dokumen cek plagiasi). */
    public function uploads()
    {
        return $this->hasMany(OrderUpload::class);
    }

    /** Pesanan ini mengandung produk jasa yang butuh unggah file? */
    public function butuhUpload(): bool
    {
        return $this->items->contains(fn ($item) => (bool) optional($item->product)->butuh_file);
    }

    /**
     * Kuota pengecekan = jumlah kredit dari item JASA.
     * Paket "5×" tersimpan sebagai duration_value=5; dikali quantity.
     * Dibaca dari data checkout yang SUDAH ADA (tanpa ubah logic pemesanan).
     */
    public function kuotaPengecekan(): int
    {
        return (int) $this->items
            ->filter(fn ($item) => (bool) optional($item->product)->butuh_file)
            ->sum(fn ($item) => max(1, (int) $item->duration_value) * max(1, (int) $item->quantity));
    }

    /** Pengecekan yang sudah dipakai = baris upload yang TIDAK dibatalkan. */
    public function terpakaiPengecekan(): int
    {
        return (int) $this->uploads
            ->filter(fn ($u) => $u->status !== 'dibatalkan')
            ->count();
    }

    /** Sisa kuota pengecekan (tidak pernah negatif). */
    public function sisaKuota(): int
    {
        return max(0, $this->kuotaPengecekan() - $this->terpakaiPengecekan());
    }

    /** Masih boleh mengunggah pengecekan baru? */
    public function bisaUploadPengecekan(): bool
    {
        return $this->butuhUpload()
            && ! in_array($this->status, ['completed', 'cancelled'])
            && $this->sisaKuota() > 0;
    }

    /**
     * Layanan jasa dianggap tuntas: kuota SUDAH habis DAN semua pengecekan
     * (yang tidak dibatalkan) berstatus 'selesai'. Dipakai untuk menyelesaikan
     * pesanan jasa secara otomatis. Tidak berlaku untuk produk non-jasa.
     */
    public function jasaTuntas(): bool
    {
        if (! $this->butuhUpload() || $this->sisaKuota() > 0) {
            return false;
        }

        $aktif = $this->uploads->where('status', '!=', 'dibatalkan');

        return $aktif->isNotEmpty() && $aktif->every(fn ($u) => $u->status === 'selesai');
    }

    public function hasPromo(): bool
    {
        return $this->promo_discount > 0 || ! empty($this->applied_promos);
    }

    public function getAppliedPromoCodes(): array
    {
        return $this->applied_promos ? array_column($this->applied_promos, 'kode_promo') : [];
    }

    // Scope: order yang punya minimal 1 item habis (status 'habis' ATAU end_date terlewat)
    public function scopeHasExpiredItem($query)
    {
        return $query->whereHas('items', function ($q) {
            $q->where('subscription_status', 'habis')
                ->orWhere(function ($q2) {
                    $q2->whereNotNull('end_date')
                        ->where('end_date', '<', now());
                });
        });
    }

    // Scope untuk filter status
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Check apakah order expired
    public function isExpired()
    {
        return $this->expired_at && now()->greaterThan($this->expired_at);
    }

    // Status badge untuk admin
    public function getStatusBadge()
    {
        return match ($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
            'processing' => '<span class="badge bg-info">Processing</span>',
            'completed' => '<span class="badge bg-primary">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
