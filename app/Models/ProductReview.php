<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ulasan pembeli — untuk produk satuan ATAU paket bundling.
 *
 * `product_id` menyimpan id targetnya; `jenis` membedakan tabelnya
 * ('produk' → products, 'paket' → product_bundlings). Ulasan lama semuanya
 * berjenis 'produk' lewat nilai bawaan kolom.
 */
class ProductReview extends Model
{
    public const JENIS_PRODUK = 'produk';

    public const JENIS_PAKET = 'paket';

    protected $fillable = [
        'product_id',
        'jenis',
        'nama',
        'rating',
        'ulasan',
        'status',
    ];

    /** Nilai bawaan di sisi model juga, supaya instans baru sudah tahu jenisnya. */
    protected $attributes = [
        'jenis' => self::JENIS_PRODUK,
    ];

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /** Ulasan untuk satu target: produk atau paket tertentu. */
    public function scopeUntuk($query, string $jenis, $id)
    {
        return $query->where('jenis', $jenis)->where('product_id', $id);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Paket bundling yang diulas (hanya bermakna bila jenisnya 'paket'). */
    public function paket()
    {
        return $this->belongsTo(ProductBundlings::class, 'product_id');
    }

    /** Nama produk atau paket yang diulas, untuk halaman moderasi admin. */
    public function namaTarget(): string
    {
        return $this->jenis === self::JENIS_PAKET
            ? ($this->paket->nama_paket ?? '—')
            : ($this->product->nama_akun ?? '—');
    }

    /** Target ulasannya sudah terhapus — ulasannya jadi menggantung. */
    public function targetHilang(): bool
    {
        return $this->jenis === self::JENIS_PAKET
            ? $this->paket === null
            : $this->product === null;
    }

    /**
     * Alamat gambar produk/paket yang diulas, atau null bila tidak ada.
     * Kolomnya berbeda: products.image vs product_bundlings.gambar.
     */
    public function gambarTarget(): ?string
    {
        $berkas = $this->jenis === self::JENIS_PAKET
            ? ($this->paket->gambar ?? null)
            : ($this->product->image ?? null);

        if (! $berkas) {
            return null;
        }

        $folder = $this->jenis === self::JENIS_PAKET ? 'img/bundling' : 'img/Product';

        return \Illuminate\Support\Facades\Storage::disk('public')->exists($folder.'/'.basename($berkas))
            ? asset('storage/'.$folder.'/'.basename($berkas))
            : null;
    }

    /** Halaman publik target ulasan — untuk memeriksa ulasan dalam konteksnya. */
    public function tautanPublik(): ?string
    {
        if ($this->targetHilang()) {
            return null;
        }

        return $this->jenis === self::JENIS_PAKET
            ? route('bundling.detail', $this->product_id)
            : route('shop.detail-product', $this->product_id);
    }

    /** [label, kelas lencana, warna] untuk tiap status. */
    public function tampilanStatus(): array
    {
        return match ($this->status) {
            'approved' => ['Disetujui', 'is-hijau', '#16a34a'],
            'hidden' => ['Disembunyikan', 'is-abu', '#64748b'],
            default => ['Menunggu', 'is-kuning', '#d97706'],
        };
    }

    /** Ulasan yang patut diperiksa lebih dulu — petunjuk, bukan penolakan. */
    public function kecurigaan(): array
    {
        $alasan = [];
        $teks = (string) $this->ulasan;

        if (preg_match('~(https?://|www\.|\b[a-z0-9-]+\.(com|net|id|co|xyz|shop|link|me)\b)~i', $teks)) {
            $alasan[] = 'Mengandung tautan';
        }

        if (mb_strlen(trim($teks)) < 12) {
            $alasan[] = 'Terlalu pendek';
        }

        if ($this->targetHilang()) {
            $alasan[] = 'Produk sudah terhapus';
        }

        return $alasan;
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'pending');
    }
}
