<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductModalPrice extends Model
{
    use HasUuids;

    protected static function booted(): void
    {
        // Saat harga modal produk JASA diubah/dihapus, expense "Modal Akun
        // Private" pesanan lama ikut disegarkan. Tanpa ini angkanya membeku
        // pada nilai lama (mis. parafrase yang dulu tersimpan per 1x cek).
        // Sengaja HANYA untuk produk jasa — produk non-jasa tak disentuh
        // agar alur & angka yang sudah teruji tetap sama.
        static::saved(fn (self $m) => $m->segarkanExpenseJasa());
        static::deleted(fn (self $m) => $m->segarkanExpenseJasa());
    }

    /** Hitung ulang expense modal untuk pesanan berbayar produk jasa ini. */
    protected function segarkanExpenseJasa(): void
    {
        $product = $this->product;
        if (! $product || ! $product->butuh_file) {
            return;
        }

        Order::query()
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->whereHas('items', fn ($q) => $q->where('product_id', $this->product_id))
            ->with('items.product.modalPrices')
            ->chunkById(50, function ($orders) {
                $sync = app(\App\Actions\Finance\SyncOrderPrivateCostAction::class);
                foreach ($orders as $order) {
                    $sync->execute($order);
                }
            });
    }

    protected $fillable = [
        'product_id',
        'durasi_value',
        'durasi_type',
        'harga',
        'berlaku_mulai',
    ];

    protected $casts = [
        'durasi_value' => 'integer',
        'harga' => 'integer',
        'berlaku_mulai' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->durasi_value.' '.$this->durasi_type;
    }
}
