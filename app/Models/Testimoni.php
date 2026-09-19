<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimoni extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'testimonis';

    protected $fillable = [
        'customer_id',
        'nama',
        'anonim',
        'peran',
        'no_hp',
        'pesan',
        'rating',
        'foto',
        'status',
        'source',
        'sorot',
        'urutan',
        'ditinjau_at',
        'ditinjau_oleh',
        'alasan_tolak',
    ];

    protected $casts = [
        'rating' => 'integer',
        'anonim' => 'boolean',
        'sorot' => 'boolean',
        'urutan' => 'integer',
        'ditinjau_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * no_hp disembunyikan dari serialisasi — dipakai admin saat memoderasi,
     * tapi tidak boleh ikut bocor ke keluaran publik.
     */
    protected $hidden = [
        'no_hp',
    ];

    /**
     * Nama untuk TAMPILAN PUBLIK — disamarkan bila pengirim memilih anonim.
     *
     * Kolom `nama` selalu berisi nama ASLI (dipakai admin saat memoderasi);
     * penyamaran terjadi di sini, saat ditampilkan. Semua tampilan publik wajib
     * memakai accessor ini, JANGAN `nama` langsung — kalau tidak, nama asli
     * pengirim anonim bocor ke pengunjung.
     */
    public function getNamaPublikAttribute(): string
    {
        return $this->anonim ? self::samarkanNama((string) $this->nama) : trim((string) $this->nama);
    }

    /** "Berto" → "B•••" : hanya huruf depan yang tampil untuk testimoni anonim. */
    public static function samarkanNama(string $nama): string
    {
        $huruf = mb_substr(trim($nama), 0, 1);

        return $huruf === '' ? 'Anonim' : mb_strtoupper($huruf).'•••';
    }

    /**
     * Nomor dalam bentuk INTI (tanpa awalan 0/62/+62) untuk dipakai sebagai kata
     * pencarian di Data Pelanggan.
     *
     * Nomor yang sama bisa tersimpan beda format di dua tabel ("0895…" di sini,
     * "62895…" di pelanggan). Bentuk inti adalah potongan yang PASTI ada di
     * keempat varian, jadi pencarian LIKE-nya tetap ketemu apa pun formatnya.
     */
    public function getNoHpCariAttribute(): string
    {
        return Customer::normalisasiNoHp($this->no_hp);
    }

    /**
     * Pemilik testimoni. NULL = testimoni tamu (nomornya tidak cocok dgn
     * pelanggan mana pun, atau pelanggannya belum punya pesanan 'completed').
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** Testimoni dari pembeli sungguhan yang pesanannya sudah selesai. */
    public function terverifikasi(): bool
    {
        return $this->customer_id !== null;
    }

    /** Dikirim langsung oleh pelanggan (bukan diinput admin). */
    public function dariPelanggan(): bool
    {
        return $this->source === 'customer';
    }

    /** Menunggu moderasi admin — sumber angka badge sidebar. */
    public function scopeMenunggu($query)
    {
        return $query->where('status', 'pending');
    }

    /** Sudah disetujui admin (tampil di publik). */
    public function scopeDisetujui($query)
    {
        return $query->where('status', 'active');
    }

    /** Ditolak/disembunyikan admin. */
    public function scopeDitolak($query)
    {
        return $query->where('status', 'non-active');
    }

    /** Jejak moderasi. Lihat App\Support\RiwayatTestimoni. */
    public function riwayat()
    {
        return $this->hasMany(TestimoniRiwayat::class)->latest('created_at');
    }

    /** Admin yang terakhir meninjau (menyetujui/menolak) testimoni ini. */
    public function peninjau()
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }

    /** Rating minimum yang boleh tampil di beranda tanpa disorot manual. */
    public const RATING_MIN_TAMPIL = 4;

    /** Bawaan banyak testimoni yang muat di slider beranda. */
    public const BERANDA_MAKS = 9;

    public const SETELAN_BERANDA = 'testimoni_beranda_maks';

    /**
     * Banyak kartu testimoni di beranda — bisa diubah admin dari layar Data
     * Testimoni. Dibatasi 3..24 supaya slidernya tetap masuk akal.
     */
    public static function jumlahBeranda(): int
    {
        return max(3, min(24, (int) (Setting::get(self::SETELAN_BERANDA) ?: self::BERANDA_MAKS)));
    }

    /**
     * Testimoni yang BENAR-BENAR tampil di beranda.
     *
     * Disetujui saja tidak cukup: bintang rendah tidak ikut tampil, kecuali
     * admin sengaja menyorotnya. Dulu semua yang disetujui langsung terpampang,
     * jadi satu klik keliru pada bintang 1 langsung terlihat pengunjung.
     */
    public function scopeTampilPublik($query)
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->where('rating', '>=', self::RATING_MIN_TAMPIL)->orWhere('sorot', true));
    }

    /** Urutan beranda: yang disorot dulu, lalu urutan manual, lalu terbaru. */
    public function scopeUrutTampil($query)
    {
        return $query->orderByDesc('sorot')->orderBy('urutan')->orderByDesc('created_at');
    }

    /** Sudah disetujui tapi tetap tidak tampil karena ratingnya rendah. */
    public function tersembunyiKarenaRating(): bool
    {
        return $this->status === 'active' && ! $this->sorot && (int) $this->rating < self::RATING_MIN_TAMPIL;
    }

    /** Berapa hari kiriman ini menunggu ditinjau (0 = hari ini). */
    public function menungguHari(): int
    {
        return $this->status === 'pending' ? (int) $this->created_at?->startOfDay()->diffInDays(now()->startOfDay()) : 0;
    }

    /**
     * Penanda kiriman yang patut dicurigai — hanya PETUNJUK untuk admin,
     * tidak pernah menolak sendiri. Keputusan tetap di tangan manusia.
     *
     * @return array<int, string> alasan yang terbaca manusia
     */
    public function kecurigaan(): array
    {
        $alasan = [];
        $pesan = (string) $this->pesan;

        if (preg_match('~(https?://|www\.|\b[a-z0-9-]+\.(com|net|id|co|xyz|shop|link|me)\b)~i', $pesan)) {
            $alasan[] = 'Mengandung tautan';
        }

        if (mb_strlen(trim($pesan)) < 15) {
            $alasan[] = 'Terlalu pendek';
        }

        // Nomor yang sama mengirim berkali-kali, atau isi pesan persis kembar.
        if (filled($this->no_hp) && static::where('no_hp', $this->no_hp)->whereKeyNot($this->getKey())->exists()) {
            $alasan[] = 'Nomor pernah mengirim';
        }

        if (static::where('pesan', $pesan)->whereKeyNot($this->getKey())->exists()) {
            $alasan[] = 'Isi kembar';
        }

        return $alasan;
    }

    /** Tautan WhatsApp untuk membalas pengirim (kosong bila nomornya tidak ada). */
    public function tautanWa(?string $pesan = null): ?string
    {
        $nomor = preg_replace('/\D+/', '', (string) $this->no_hp);
        if ($nomor === '' || $nomor === null) {
            return null;
        }

        $nomor = str_starts_with($nomor, '0') ? '62'.substr($nomor, 1) : $nomor;

        return 'https://wa.me/'.$nomor.($pesan ? '?text='.rawurlencode($pesan) : '');
    }
}
