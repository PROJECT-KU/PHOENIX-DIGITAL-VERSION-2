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

        // Saat pesanan BARU menjadi 'paid' (dari mana pun deteksinya: poller QRIS,
        // halaman share customer, admin, atau penyelamatan cancel-expired), kabari
        // admin. Murni efek samping (kirim notifikasi) — tidak mengubah alur/logic
        // pesanan. Gagal-aman: PesananBaru::kirim membungkus dengan try/catch.
        static::updated(function (Order $order) {
            if ($order->wasChanged('status')
                && $order->status === 'paid'
                && $order->getOriginal('status') !== 'paid') {
                \App\Notifications\PesananBaru::kirim($order);
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
     * Kuota pengecekan PER JENIS ('ai' | 'plagiasi' | 'parafrase' |
     * 'pengecekan'). Produk menyumbang jenisnya sendiri (paket "5×" = 5),
     * tiap add-on pemeriksaan menyumbang +1 jenisnya. Add-on non-pemeriksaan
     * (mis. target parafrase) tidak menambah kuota.
     *
     * @return array<string,int>  mis. ['ai' => 1, 'plagiasi' => 1]
     */
    public function kuotaPerJenis(): array
    {
        $out = [];

        foreach ($this->items as $item) {
            $product = $item->product;
            if (! $product || ! $product->butuh_file) {
                continue;
            }

            $qty = max(1, (int) $item->quantity);

            $jenisProduk = $product->jenisLayanan();
            if ($jenisProduk) {
                $out[$jenisProduk] = ($out[$jenisProduk] ?? 0)
                    + max(1, (int) $item->duration_value) * $qty;
            }

            // Add-on pemeriksaan: jenisnya dibaca dari riwayat pesanan (cek_ai /
            // pakai_exclude yang ikut tersimpan). Pesanan lama belum menyimpan
            // penanda itu, jadi jatuh ke katalog lewat id lalu NAMA.
            foreach (($item->addons ?? []) as $addon) {
                $jenisAddon = $this->jenisAddon($addon);

                if ($jenisAddon) {
                    $out[$jenisAddon] = ($out[$jenisAddon] ?? 0) + $qty;
                }
            }
        }

        return $out;
    }

    /**
     * Jenis pemeriksaan sebuah add-on dari data riwayatnya, dengan cadangan
     * ke katalog (id → nama) untuk pesanan lama yang belum menyimpan penanda.
     */
    protected function jenisAddon(array $addon): ?string
    {
        if (array_key_exists('cek_ai', $addon) || array_key_exists('pakai_exclude', $addon)) {
            return match (true) {
                ! empty($addon['cek_ai']) => 'ai',
                ! empty($addon['pakai_exclude']) => 'plagiasi',
                default => null,
            };
        }

        $katalog = ! empty($addon['id']) ? ProductAddon::find($addon['id']) : null;

        if (! $katalog && ! empty($addon['nama'])) {
            $katalog = ProductAddon::whereRaw('LOWER(nama) = ?', [mb_strtolower($addon['nama'])])->first();
        }

        return $katalog?->jenisLayanan();
    }

    /**
     * Kuota pengecekan TOTAL = jumlah semua jenis.
     * Tetap kompatibel: pesanan satu-jenis menghasilkan angka yang sama
     * seperti sebelumnya.
     */
    public function kuotaPengecekan(): int
    {
        return (int) array_sum($this->kuotaPerJenis());
    }

    /** Unggahan aktif (tidak dibatalkan) untuk satu jenis. */
    protected function uploadAktifJenis(string $jenis)
    {
        return $this->uploads
            ->filter(fn ($u) => $u->status !== 'dibatalkan')
            ->filter(function ($u) use ($jenis) {
                // Unggahan lama tanpa jenis dianggap milik jenis TUNGGAL pesanan.
                if ($u->jenis === null) {
                    $semua = array_keys($this->kuotaPerJenis());

                    return count($semua) === 1 && $semua[0] === $jenis;
                }

                return $u->jenis === $jenis;
            });
    }

    /** Sudah dipakai untuk satu jenis. */
    public function terpakaiPerJenis(string $jenis): int
    {
        return $this->uploadAktifJenis($jenis)->count();
    }

    /** Sisa kuota untuk satu jenis (tidak pernah negatif). */
    public function sisaKuotaJenis(string $jenis): int
    {
        return max(0, ($this->kuotaPerJenis()[$jenis] ?? 0) - $this->terpakaiPerJenis($jenis));
    }

    /** Total pengecekan yang sudah dipakai = baris upload yang TIDAK dibatalkan. */
    public function terpakaiPengecekan(): int
    {
        return (int) $this->uploads
            ->filter(fn ($u) => $u->status !== 'dibatalkan')
            ->count();
    }

    /** Sisa kuota TOTAL (semua jenis, tidak pernah negatif). */
    public function sisaKuota(): int
    {
        return max(0, $this->kuotaPengecekan() - $this->terpakaiPengecekan());
    }

    /** Jenis pemeriksaan yang MASIH punya sisa kuota (untuk pemilih di /cek). */
    public function jenisTersisa(): array
    {
        $out = [];
        foreach (array_keys($this->kuotaPerJenis()) as $jenis) {
            if ($this->sisaKuotaJenis($jenis) > 0) {
                $out[] = $jenis;
            }
        }

        return $out;
    }

    /** Masih boleh mengunggah pengecekan baru (jenis apa pun)? */
    public function bisaUploadPengecekan(): bool
    {
        return $this->butuhUpload()
            && ! in_array($this->status, ['completed', 'cancelled'])
            && $this->sisaKuota() > 0;
    }

    /** Masih boleh mengunggah untuk SATU jenis pemeriksaan tertentu? */
    public function bisaUploadJenis(string $jenis): bool
    {
        return $this->butuhUpload()
            && ! in_array($this->status, ['completed', 'cancelled'])
            && $this->sisaKuotaJenis($jenis) > 0;
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
