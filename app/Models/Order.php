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
     * Bonus kuota dari admin (bila ada) ikut ditambahkan di sini — lihat
     * bonusKuotaPerJenis().
     *
     * @return array<string,int> mis. ['ai' => 1, 'plagiasi' => 1]
     */
    public function kuotaPerJenis(): array
    {
        $out = $this->kuotaDasarPerJenis();

        /*
         * Bonus admin hanya MENAMBAH jenis yang memang dibeli — tidak pernah
         * memunculkan jenis pemeriksaan baru. Dengan begitu jumlah jenis pada
         * pesanan tetap, sehingga penentuan pemilik unggahan lama (yang kolom
         * `jenis`-nya null, lihat uploadAktifJenis()) tidak ikut berubah.
         */
        foreach ($this->bonusKuotaPerJenis() as $jenis => $jumlah) {
            if ($jumlah > 0 && isset($out[$jenis])) {
                $out[$jenis] += $jumlah;
            }
        }

        return $out;
    }

    /**
     * Kuota yang benar-benar DIBELI customer (tanpa bonus admin) — dasar
     * perhitungan kuotaPerJenis() sekaligus daftar jenis yang boleh diberi
     * bonus.
     *
     * @return array<string,int>
     */
    public function kuotaDasarPerJenis(): array
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

    /*
     |--------------------------------------------------------------------------
     | Bonus kuota pengecekan (kompensasi admin)
     |--------------------------------------------------------------------------
     | Bila pengecekan bermasalah (hasil keliru, file gagal, dsb), admin bisa
     | memberi kuota tambahan gratis dari halaman detail pesanan. Nilainya
     | disimpan sebagai SATU baris JSON di tabel `settings` (satu key per
     | pesanan) — bukan kolom baru, jadi skema database sama sekali tidak
     | berubah. Bentuk nilainya:
     |
     |   {"kuota":{"ai":2},"riwayat":[{"jenis":"ai","jumlah":2,"alasan":"...",
     |    "oleh":"Budi","at":"2026-07-31T10:00:00+07:00"}]}
     |
     | Pesanan tanpa bonus (yakni semua pesanan lama) tidak punya baris ini dan
     | berperilaku persis seperti sebelumnya.
     */

    /** Cache per-instance — kuotaPerJenis() dipanggil berkali-kali per request. */
    protected ?array $bonusKuotaCache = null;

    /** Key penyimpanan bonus pesanan ini di tabel `settings`. */
    public function bonusKuotaKey(): string
    {
        return 'bonus_kuota_order:'.$this->getKey();
    }

    /** Isi lengkap bonus: ['kuota' => [...], 'riwayat' => [...]]. */
    private function bonusKuotaData(): array
    {
        if ($this->bonusKuotaCache !== null) {
            return $this->bonusKuotaCache;
        }

        $data = json_decode((string) Setting::get($this->bonusKuotaKey(), ''), true);
        $data = is_array($data) ? $data : [];

        $kuota = [];
        foreach (is_array($data['kuota'] ?? null) ? $data['kuota'] : [] as $jenis => $jumlah) {
            if (is_string($jenis) && (int) $jumlah > 0) {
                $kuota[$jenis] = (int) $jumlah;
            }
        }

        return $this->bonusKuotaCache = [
            'kuota' => $kuota,
            'riwayat' => is_array($data['riwayat'] ?? null) ? $data['riwayat'] : [],
        ];
    }

    /**
     * Bonus kuota per jenis pemeriksaan. Array kosong = tak pernah diberi bonus.
     *
     * @return array<string,int>
     */
    public function bonusKuotaPerJenis(): array
    {
        return $this->bonusKuotaData()['kuota'];
    }

    /** Total bonus kuota yang berlaku pada pesanan ini. */
    public function bonusKuota(): int
    {
        return (int) array_sum($this->bonusKuotaPerJenis());
    }

    /** Jejak pemberian bonus (untuk ditampilkan di halaman admin). */
    public function riwayatBonusKuota(): array
    {
        return $this->bonusKuotaData()['riwayat'];
    }

    /**
     * Tambah bonus kuota untuk satu jenis. Mengembalikan total bonus jenis itu
     * setelah penambahan.
     */
    public function tambahBonusKuota(string $jenis, int $jumlah, ?string $alasan = null, ?string $oleh = null): int
    {
        $jumlah = max(1, $jumlah);
        $data = $this->bonusKuotaData();

        $data['kuota'][$jenis] = ($data['kuota'][$jenis] ?? 0) + $jumlah;
        $data['riwayat'][] = [
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'alasan' => $alasan,
            'oleh' => $oleh,
            'at' => now()->toIso8601String(),
        ];

        $total = $data['kuota'][$jenis];
        $this->simpanBonusKuotaData($data);

        return $total;
    }

    /**
     * Hapus SELURUH bonus satu jenis (mis. admin salah ketik jumlah). Kuota yang
     * terlanjur dipakai customer tidak bisa ditarik kembali — sisa kuota memang
     * dijaga tidak pernah negatif oleh sisaKuotaJenis().
     */
    public function hapusBonusKuota(string $jenis, ?string $oleh = null): void
    {
        $data = $this->bonusKuotaData();

        if (! isset($data['kuota'][$jenis])) {
            return;
        }

        $dihapus = $data['kuota'][$jenis];
        unset($data['kuota'][$jenis]);

        $data['riwayat'][] = [
            'jenis' => $jenis,
            'jumlah' => -$dihapus,
            'alasan' => 'Bonus dibatalkan admin',
            'oleh' => $oleh,
            'at' => now()->toIso8601String(),
        ];

        $this->simpanBonusKuotaData($data);
    }

    private function simpanBonusKuotaData(array $data): void
    {
        $data['kuota'] = array_filter($data['kuota'], fn ($v) => (int) $v > 0);
        // Riwayat dibatasi agar muat di kolom `settings.value` (TEXT).
        $data['riwayat'] = array_slice($data['riwayat'], -30);

        if (empty($data['kuota']) && empty($data['riwayat'])) {
            Setting::where('key', $this->bonusKuotaKey())->delete();
        } else {
            Setting::set($this->bonusKuotaKey(), json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        $this->bonusKuotaCache = null;
    }

    /**
     * Waktu kuota pengecekan HABIS = unggahan (non-batal) TERAKHIR yang mengisi
     * slot terakhir. Null bila pesanan tak berkuota atau masih ada sisa. Dipakai
     * untuk menghitung masa berlaku link /cek — tanpa kolom DB baru.
     */
    public function kuotaHabisAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->kuotaPengecekan() <= 0 || $this->sisaKuota() > 0) {
            return null;
        }

        $terakhir = $this->uploads
            ->filter(fn ($u) => $u->status !== 'dibatalkan')
            ->max('created_at');

        return $terakhir ? \Illuminate\Support\Carbon::parse($terakhir) : null;
    }

    /** Batas akhir link /cek bisa diakses = 24 jam setelah kuota habis (null bila belum habis). */
    public function cekLinkKadaluarsaAt(): ?\Illuminate\Support\Carbon
    {
        return $this->kuotaHabisAt()?->copy()->addHours(24);
    }

    /** Link /cek sudah tak bisa diakses? (kuota habis DAN sudah lewat 24 jam). */
    public function cekLinkKadaluarsa(): bool
    {
        $batas = $this->cekLinkKadaluarsaAt();

        return $batas !== null && now()->greaterThan($batas);
    }

    /**
     * Waktu berkas (unggahan customer + hasil admin) dihapus otomatis = 7 hari
     * SETELAH link /cek kedaluwarsa (yakni kuota habis + 24 jam + 7 hari).
     * Null bila kuota belum habis. Menghemat storage — dokumen pribadi tak
     * disimpan lebih lama dari yang perlu.
     */
    public function berkasHapusAt(): ?\Illuminate\Support\Carbon
    {
        return $this->cekLinkKadaluarsaAt()?->copy()->addDays(7);
    }

    /** Berkas jasa sudah waktunya dihapus? (link kedaluwarsa DAN sudah lewat 7 hari). */
    public function berkasHarusDihapus(): bool
    {
        $batas = $this->berkasHapusAt();

        return $batas !== null && now()->greaterThan($batas);
    }

    /** Masih boleh mengunggah pengecekan baru (jenis apa pun)? */
    public function bisaUploadPengecekan(): bool
    {
        return $this->butuhUpload()
            && $this->statusBolehUpload()
            && $this->sisaKuota() > 0;
    }

    /** Masih boleh mengunggah untuk SATU jenis pemeriksaan tertentu? */
    public function bisaUploadJenis(string $jenis): bool
    {
        return $this->butuhUpload()
            && $this->statusBolehUpload()
            && $this->sisaKuotaJenis($jenis) > 0;
    }

    /**
     * Status pesanan mengizinkan unggahan baru?
     *
     * Sama dengan aturan lama (selain 'completed'/'cancelled' boleh) dengan
     * SATU pengecualian: pesanan 'completed' yang diberi BONUS kuota oleh admin.
     * Pesanan jasa otomatis jadi 'completed' begitu kuota habis & semua hasil
     * terunggah — padahal justru di titik itulah kendala biasanya ketahuan.
     * Tanpa pengecualian ini, bonusnya tak akan pernah bisa dipakai customer.
     * Pesanan 'cancelled' tetap terkunci mutlak.
     */
    protected function statusBolehUpload(): bool
    {
        return match ($this->status) {
            'cancelled' => false,
            'completed' => $this->bonusKuota() > 0,
            default => true,
        };
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

    /**
     * Label status untuk DITAMPILKAN di badge (daftar & detail). TIDAK mengubah
     * kolom `status` di DB — hanya menerjemahkannya agar tidak menyesatkan.
     *
     * Untuk order JASA pengecekan yang sudah dibayar tapi BELUM tuntas, "PAID"
     * membingungkan admin awam (dikira selesai). Diganti label kerja yang jelas:
     * - masih ada pengecekan menunggu/diproses → "PERLU DIPROSES" (giliran admin)
     * - belum ada, tapi kuota masih tersisa     → "MENUNGGU CUSTOMER" (giliran customer)
     * Order non-jasa & status lain tetap seperti semula.
     *
     * @return array{0:string,1:string} [teks, warna bootstrap]
     */
    public function labelStatus(): array
    {
        $map = [
            'draft' => ['DRAFT', 'secondary'],
            'pending' => ['PENDING', 'warning'],
            'paid' => ['PAID', 'success'],
            'processing' => ['PROCESSING', 'info'],
            'completed' => ['COMPLETED', 'primary'],
            'cancelled' => ['CANCELLED', 'danger'],
        ];
        $default = $map[$this->status] ?? [strtoupper((string) $this->status), 'secondary'];

        // Order jasa yang admin SUDAH mengunggah minimal 1 hasil (upload
        // 'selesai') tapi belum tuntas → "SEDANG DIPROSES". Sebelum hasil
        // pertama diunggah, biarkan tetap "PAID" agar admin tahu ini pesanan
        // BARU. Konsisten dengan tab "Pengecekan Berjalan".
        if ($this->butuhUpload()
            && in_array($this->status, ['paid', 'processing'], true)
            && $this->uploads->contains(fn ($u) => $u->status === 'selesai')) {
            return ['SEDANG DIPROSES', 'info'];
        }

        return $default;
    }

    /**
     * Order JASA pengecekan yang SEDANG dikerjakan: sudah dibayar & admin telah
     * mengunggah ≥1 hasil (upload 'selesai'), tapi belum tuntas. Dipakai tab
     * "Pengecekan Berjalan" & labelStatus(). Sebelum hasil pertama diunggah,
     * order tetap berada di "Pesanan Baru" (PAID) agar dikenali sbg pesanan baru.
     */
    public function scopePengecekanBerjalan($query)
    {
        return $query->whereIn('status', ['paid', 'processing'])
            ->whereHas('items.product', fn ($p) => $p->where('butuh_file', 1))
            ->whereHas('uploads', fn ($u) => $u->where('status', 'selesai'));
    }
}
