<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ebook extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'share_token',
        'judul',
        'deskripsi',
        'file',
        'status',
        'dibuka_count',
        'terakhir_dibuka_at',
    ];

    protected $casts = [
        'terakhir_dibuka_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($ebook) {
            if (empty($ebook->share_token)) {
                $ebook->share_token = self::generateToken();
            }
        });
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::random(10);
        } while (self::where('share_token', $token)->exists());

        return $token;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Produk yang memakai ebook ini sebagai ebook bawaan (tercentang otomatis saat diproses). */
    public function produkBawaan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class, 'ebook_bawaan_id');
    }

    /** Catat satu kali halaman baca dibuka pelanggan (tanpa menyentuh updated_at). */
    public function catatDibuka(): void
    {
        static::withoutTimestamps(fn () => $this->newQuery()->whereKey($this->id)->update([
            'dibuka_count' => \Illuminate\Support\Facades\DB::raw('dibuka_count + 1'),
            'terakhir_dibuka_at' => now(),
        ]));
    }

    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_ebook')->withTimestamps();
    }

    // URL viewer view-only (link pendek, tanpa expose file/uuid)
    public function getViewUrl(): ?string
    {
        return $this->share_token ? url('/e/'.$this->share_token) : null;
    }

    /** Ukuran berkas PDF dalam byte, atau null bila berkasnya tidak ada. */
    public function ukuranFile(): ?int
    {
        $path = $this->file ? storage_path('app/ebooks/'.$this->file) : null;

        return $path && is_file($path) ? (int) filesize($path) : null;
    }

    /** Ukuran berkas yang enak dibaca: "842 KB", "1,4 MB". */
    public function ukuranFileLabel(): ?string
    {
        $b = $this->ukuranFile();
        if ($b === null) {
            return null;
        }

        return $b < 1048576
            ? max(1, (int) round($b / 1024)).' KB'
            : number_format($b / 1048576, 1, ',', '.').' MB';
    }

    // URL unduh untuk ADMIN (terproteksi auth), bukan untuk pelanggan
    public function getAdminDownloadUrl(): ?string
    {
        return route('admin.ebook.download', $this);
    }
}
