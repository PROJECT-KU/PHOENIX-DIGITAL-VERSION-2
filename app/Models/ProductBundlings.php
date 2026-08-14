<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundlings extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'product_bundlings';

    protected $fillable = [
        'nama_paket',
        'product_1',
        'product_2',
        'product_3',
        'product_4',
        'product_5',
        'durations',
        'harga_awal',
        'harga_bundling',
        'gambar',
        'deskripsi',
        'status',
        'mulai_tayang',
        'selesai_tayang',
    ];

    protected $casts = [
        'durations' => 'array',
        'mulai_tayang' => 'datetime',
        'selesai_tayang' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Hanya paket yang BOLEH dilihat pembeli sekarang.
     *
     * Tanggal kosong = tanpa batas, jadi paket lama yang tidak pernah dijadwalkan
     * tetap tampil persis seperti sebelumnya. Itu yang membuat penambahan ini
     * aman untuk data yang sudah ada.
     */
    public function scopeTayang($query)
    {
        $now = now();

        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('mulai_tayang')->orWhere('mulai_tayang', '<=', $now))
            ->where(fn ($q) => $q->whereNull('selesai_tayang')->orWhere('selesai_tayang', '>=', $now));
    }

    /** Versi per-baris dari scopeTayang(), untuk memeriksa satu paket. */
    public function sedangTayang(): bool
    {
        return $this->status === 'active'
            && (is_null($this->mulai_tayang) || $this->mulai_tayang <= now())
            && (is_null($this->selesai_tayang) || $this->selesai_tayang >= now());
    }

    /** Keterangan jadwal untuk admin, mis. "Berakhir 20 Sep 2026, 23:59". */
    public function jadwalLabel(): ?string
    {
        if ($this->mulai_tayang && $this->mulai_tayang > now()) {
            return 'Mulai '.$this->mulai_tayang->translatedFormat('d M Y, H:i');
        }

        if ($this->selesai_tayang) {
            return ($this->selesai_tayang < now() ? 'Berakhir ' : 'Sampai ')
                .$this->selesai_tayang->translatedFormat('d M Y, H:i');
        }

        return null;
    }

    /**
     * Promo yang MENAMPILKAN paket ini di etalasenya (mis. bagian flash sale).
     *
     * Sekadar penempatan tampilan — bukan pemberian diskon. Harga paket sudah
     * harga promo lewat `harga_bundling`.
     */
    public function promos(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Promo::class, 'promo_product_bundling', 'product_bundling_id', 'promo_id')
            ->withTimestamps();
    }

    // Daftar produk paket + durasinya (default 1 bulan jika belum diset)
    public function bundleProducts(): array
    {
        $out = [];
        foreach (['product_1', 'product_2', 'product_3', 'product_4', 'product_5'] as $col) {
            if (! $this->$col) {
                continue;
            }
            $dur = $this->durations[$col] ?? [];
            $out[] = [
                'product_id' => $this->$col,
                'duration_value' => (int) ($dur['value'] ?? 1),
                'duration_type' => $dur['type'] ?? 'bulan',
            ];
        }

        return $out;
    }

    public function product1()
    {
        return $this->belongsTo(Product::class, 'product_1');
    }

    public function product2()
    {
        return $this->belongsTo(Product::class, 'product_2');
    }

    public function product3()
    {
        return $this->belongsTo(Product::class, 'product_3');
    }

    public function product4()
    {
        return $this->belongsTo(Product::class, 'product_4');
    }

    public function product5()
    {
        return $this->belongsTo(Product::class, 'product_5');
    }
}
