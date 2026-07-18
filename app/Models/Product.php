<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Product extends Model
{
    use HasFactory, HasUuids;

    /** Harga per durasi (fleksibel). */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Modal satuan (harga beli 1 akun) untuk durasi tertentu — dari katalog
     * pengeluaran "pembelian_akun" produk ini.
     * Harga BERLAKU PER TANGGAL: bila $asOf diisi, ambil entri terbaru dengan
     * tanggal <= $asOf (harga yang berlaku saat itu, tidak berubah retroaktif).
     * Bila kosong, ambil entri terbaru global.
     */
    public function modalSatuan(int $durasiValue, string $durasiType, $asOf = null): int
    {
        $row = $this->modalPrices()
            ->where('durasi_value', $durasiValue)
            ->where('durasi_type', $durasiType)
            ->when($asOf, fn ($q) => $q->where('berlaku_mulai', '<=', $asOf))
            ->orderBy('berlaku_mulai', 'desc')
            ->orderBy('created_at', 'desc')
            ->value('harga');

        return (int) ($row ?? 0);
    }

    /** Katalog harga modal akun private (ber-tanggal-berlaku). */
    public function modalPrices(): HasMany
    {
        return $this->hasMany(ProductModalPrice::class);
    }

    /**
     * Modal untuk SATU baris order (per 1 quantity), sesuai jenis produk:
     *  - JASA (butuh_file): modal per 1× pengecekan × jumlah pengecekan (durasi_value).
     *    Cukup 1 entri modal "1 kali" di katalog untuk semua paket (1x/5x/10x).
     *  - Non-jasa: modal satuan tepat pada (durasi_value, durasi_type) — perilaku lama.
     * Caller mengalikan hasil ini dengan quantity item.
     */
    public function modalItem(int $durasiValue, string $durasiType, $asOf = null): int
    {
        if ($this->butuh_file) {
            return $this->modalSatuan(1, 'kali', $asOf) * max(1, $durasiValue);
        }

        return $this->modalSatuan($durasiValue, $durasiType, $asOf);
    }

    /**
     * Harga untuk durasi tertentu. Ambil dari tabel harga fleksibel;
     * fallback ke kolom lama bila belum ada barisnya.
     */
    public function hargaUntuk(int $durasiValue, string $durasiType): int
    {
        $row = $this->relationLoaded('prices')
            ? $this->prices->first(fn ($p) => (int) $p->durasi_value === $durasiValue && $p->durasi_type === $durasiType)
            : $this->prices()->where('durasi_value', $durasiValue)->where('durasi_type', $durasiType)->first();

        if ($row) {
            return (int) $row->harga;
        }

        if ($durasiType === 'tahun') {
            return (int) ($this->harga_pertahun ?? 0);
        }

        return match ($durasiValue) {
            1 => (int) ($this->harga_perbulan ?? 0),
            5 => (int) ($this->harga_5_perbulan ?? 0),
            10 => (int) ($this->harga_10_perbulan ?? 0),
            default => 0,
        };
    }

    /**
     * Daftar harga per durasi (terurut). Dari tabel; fallback kolom lama bila kosong.
     *
     * @return Collection<int, array{durasi_value:int, durasi_type:string, harga:int, label:string}>
     */
    public function daftarHarga(): Collection
    {
        $rows = $this->relationLoaded('prices')
            ? $this->prices->sortBy([['durasi_type', 'asc'], ['durasi_value', 'asc']])->values()
            : $this->prices()->orderBy('durasi_type')->orderBy('durasi_value')->get();

        if ($rows->isNotEmpty()) {
            return $rows->map(fn ($p) => [
                'durasi_value' => (int) $p->durasi_value,
                'durasi_type' => $p->durasi_type,
                'harga' => (int) $p->harga,
                'label' => $p->durasi_value.' '.$p->durasi_type,
            ])->values();
        }

        $out = collect();
        foreach ([[1, 'bulan', $this->harga_perbulan], [5, 'bulan', $this->harga_5_perbulan], [10, 'bulan', $this->harga_10_perbulan], [1, 'tahun', $this->harga_pertahun]] as [$v, $t, $h]) {
            if ((int) $h > 0) {
                $out->push(['durasi_value' => $v, 'durasi_type' => $t, 'harga' => (int) $h, 'label' => $v.' '.$t]);
            }
        }

        return $out;
    }

    protected $fillable = [
        'nama_akun',
        'tipe_akun',
        'butuh_file',
        'jasa_mode',
        'addon_mode',
        'image',
        'harga_awal',
        'harga_perbulan',
        'harga_5_perbulan',
        'harga_10_perbulan',
        'harga_pertahun',
        'deskripsi',
    ];

    protected $casts = [
        'butuh_file' => 'boolean',
    ];

    /** Add-on opsional produk jasa (dinamis, diatur admin). */
    public function addons(): HasMany
    {
        return $this->hasMany(ProductAddon::class);
    }

    /** Add-on aktif, terurut — untuk ditampilkan ke customer. */
    public function addonAktif()
    {
        return $this->addons
            ->where('aktif', true)
            ->sortBy('urutan')
            ->values();
    }

    /** Produk jasa yang dijual PER HALAMAN (mis. parafrase). */
    public function jasaPerHalaman(): bool
    {
        return (bool) $this->butuh_file && $this->jasa_mode === 'halaman';
    }

    /** Add-on hanya boleh pilih salah satu? (mis. tingkat target parafrase) */
    public function addonPilihSatu(): bool
    {
        return $this->addon_mode === 'tunggal';
    }

    /** Harga per halaman (produk jasa mode 'halaman'). */
    public function hargaPerHalaman(): int
    {
        $row = $this->prices->firstWhere('durasi_type', 'halaman');

        return $row ? (int) $row->harga : 0;
    }

    /** Paket JASA (produk butuh_file) — per jumlah pengecekan (durasi_type 'kali'). */
    public function paketJasa()
    {
        return $this->prices
            ->whereIn('durasi_type', ['kali', 'sekali'])
            ->sortBy('durasi_value')
            ->values();
    }

    /** Harga paket jasa terkecil (untuk ringkasan). Null bila bukan jasa/tak ada. */
    public function hargaSekali(): ?int
    {
        $row = $this->paketJasa()->first();

        return $row ? (int) $row->harga : null;
    }

    // Helper format rupiah
    public function numberFormatted($value)
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    // Fungsi dinamis untuk semua harga
    public function formatted($field)
    {
        if (! isset($this->{$field})) {
            return 'Rp 0';
        }

        return $this->numberFormatted($this->{$field});
    }

    public function scopeLatestLimit($query, $limit = 4)
    {
        return $query->latest()->take($limit);
    }

    // Relationship
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Get available packages
    public function getAvailablePackages()
    {
        $packages = [];

        if ($this->harga_perbulan) {
            $packages[] = [
                'duration_type' => 'bulan',
                'duration_value' => 1,
                'price' => $this->harga_perbulan,
                'label' => 'Paket 1 Bulan',
            ];
        }

        if ($this->harga_5_perbulan) {
            $savings = ($this->harga_perbulan * 5) - $this->harga_5_perbulan;
            $packages[] = [
                'duration_type' => 'bulan',
                'duration_value' => 5,
                'price' => $this->harga_5_perbulan,
                'label' => 'Paket 5 Bulan',
                'savings' => $savings,
            ];
        }

        if ($this->harga_10_perbulan) {
            $savings = ($this->harga_perbulan * 10) - $this->harga_10_perbulan;
            $packages[] = [
                'duration_type' => 'bulan',
                'duration_value' => 10,
                'price' => $this->harga_10_perbulan,
                'label' => 'Paket 10 Bulan',
                'savings' => $savings,
            ];
        }

        if ($this->harga_pertahun) {
            $savings = ($this->harga_perbulan * 12) - $this->harga_pertahun;
            $packages[] = [
                'duration_type' => 'tahun',
                'duration_value' => 1,
                'price' => $this->harga_pertahun,
                'label' => 'Paket 1 Tahun',
                'savings' => $savings,
            ];
        }

        return $packages;
    }

    // Get lowest price
    public function getLowestPrice()
    {
        return min(array_filter([
            $this->harga_perbulan,
            $this->harga_5_perbulan,
            $this->harga_10_perbulan,
            $this->harga_pertahun,
        ]));
    }
}
