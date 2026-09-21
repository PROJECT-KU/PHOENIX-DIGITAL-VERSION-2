<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use SoftDeletes;

    /** Artikel dianggap mandek bila sudah selama ini terbit tanpa pembaca baru. */
    public const HARI_MANDEK = 90;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'cover',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'views',
        'author',
        'tags',
        'cover_alt',
        'is_featured',
        'updated_by',
        'unpublish_at',
        'focus_keyword',
        'disimpan_manual',
        'dibuka_oleh',
        'dibuka_pada',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'unpublish_at' => 'datetime',
        'dibuka_pada' => 'datetime',
        'views' => 'integer',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'disimpan_manual' => 'boolean',
    ];

    /** Berapa lama penanda "sedang dibuka" dianggap masih berlaku (menit). */
    public const MENIT_KUNCI = 3;

    public function bacaHarian(): HasMany
    {
        return $this->hasMany(BlogPostRead::class, 'blog_post_id');
    }

    public function revisi(): HasMany
    {
        return $this->hasMany(BlogPostRevision::class, 'blog_post_id')->latest('id');
    }

    public function pengalihan(): HasMany
    {
        return $this->hasMany(BlogPostRedirect::class, 'blog_post_id');
    }

    public function penyunting(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pembuka(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }

    protected static function booted(): void
    {
        static::updating(function (self $artikel) {
            // Hanya untuk jejak internal. Kolom 'author' sengaja tidak ikut
            // berubah supaya halaman publik tetap menulis "admin".
            if (auth()->hasUser()) {
                $artikel->updated_by = auth()->id();
            }
        });
    }

    /**
     * Route model binding memakai slug (untuk URL publik yang ramah SEO).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Slug yang diminta pengunjung — bisa berbeda dari slug artikel bila
     * alamatnya sudah pernah diubah. Dibaca BlogShow untuk mengalihkan.
     */
    public ?string $slugDiminta = null;

    /**
     * Cari artikel menurut slug; kalau tidak ketemu, telusuri alamat lamanya.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $artikel = $this->where($field ?? $this->getRouteKeyName(), $value)->first();

        if (! $artikel) {
            $alih = BlogPostRedirect::where('slug_lama', $value)->first();
            $artikel = $alih?->post;
        }

        if ($artikel) {
            $artikel->slugDiminta = (string) $value;
        }

        return $artikel;
    }

    /**
     * Simpan keadaan isi SEKARANG sebagai satu versi riwayat.
     *
     * Dipanggil sebelum perubahan ditulis, jadi yang tersimpan adalah versi
     * yang akan tergantikan — itulah yang dicari orang saat ingin kembali.
     */
    public function catatRevisi(): void
    {
        $this->revisi()->create([
            'user_id' => auth()->id(),
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
        ]);

        // Buang yang paling lama supaya tabelnya tidak tumbuh tanpa batas.
        $sisa = $this->revisi()->pluck('id')->slice(BlogPostRevision::BATAS);
        if ($sisa->isNotEmpty()) {
            BlogPostRevision::whereIn('id', $sisa)->delete();
        }
    }

    /** Catat bahwa alamat lama ini masih mengarah ke artikel ini. */
    public function catatAlamatLama(string $slugLama): void
    {
        if ($slugLama === '' || $slugLama === $this->slug) {
            return;
        }

        // Alamat yang kini dipakai artikel ini tidak boleh jadi pengalihan
        // ke dirinya sendiri (mis. slug ditukar bolak-balik).
        BlogPostRedirect::where('slug_lama', $this->slug)->delete();

        BlogPostRedirect::updateOrCreate(
            ['slug_lama' => $slugLama],
            ['blog_post_id' => $this->getKey()]
        );
    }

    /** Tandai artikel sedang dibuka oleh pengguna yang sedang login. */
    public function tandaiDibuka(): void
    {
        if (! auth()->hasUser()) {
            return;
        }

        static::whereKey($this->getKey())->update([
            'dibuka_oleh' => auth()->id(),
            'dibuka_pada' => now(),
        ]);
    }

    public function lepasTandaDibuka(): void
    {
        if ($this->dibuka_oleh && auth()->id() === $this->dibuka_oleh) {
            static::whereKey($this->getKey())->update(['dibuka_oleh' => null, 'dibuka_pada' => null]);
        }
    }

    /**
     * Sedang dibuka orang lain yang masih aktif?
     *
     * Penandanya sengaja kedaluwarsa sendiri: tab yang ditutup paksa tidak
     * boleh mengunci artikel selamanya.
     */
    public function dipegangOrangLain(): bool
    {
        return $this->dibuka_oleh
            && $this->dibuka_oleh !== auth()->id()
            && $this->dibuka_pada
            && $this->dibuka_pada->gt(now()->subMinutes(self::MENIT_KUNCI));
    }

    /**
     * Hanya artikel yang sudah dipublikasikan & waktunya sudah tiba.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            // Artikel yang sudah lewat waktu berhenti tayang diperlakukan
            // seperti belum terbit, tanpa mengubah statusnya di basis data.
            ->where(function ($q) {
                $q->whereNull('unpublish_at')
                    ->orWhere('unpublish_at', '>', now());
            });
    }

    /** Artikel yang disematkan di atas daftar blog publik. */
    public function scopeUnggulan(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * [label, kelas lencana, warna, ikon] keadaan artikel.
     *
     * "Terbit" dan "Terjadwal" sama-sama berstatus published di basis data —
     * yang membedakan hanya waktunya sudah tiba atau belum. Dipisahkan di sini
     * supaya seluruh layar memakai istilah yang sama.
     */
    public function keadaan(): array
    {
        if ($this->status !== 'published') {
            return ['Draf', 'is-kuning', '#d97706', 'bi-pencil-square'];
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return ['Terjadwal', 'is-biru', '#2563eb', 'bi-clock-history'];
        }

        // Statusnya masih published, tapi waktunya sudah lewat — pengunjung
        // tidak lagi melihatnya, jadi layar admin pun tidak boleh bilang
        // "Terbit".
        if ($this->unpublish_at && $this->unpublish_at->isPast()) {
            return ['Berakhir', 'is-abu', '#64748b', 'bi-slash-circle'];
        }

        return ['Terbit', 'is-hijau', '#16a34a', 'bi-globe2'];
    }

    /** Perkiraan lama baca (menit) — nama Indonesia untuk readingMinutes(). */
    public function lamaBaca(): int
    {
        return $this->readingMinutes();
    }

    /** Alamat gambar sampul, atau null bila berkasnya memang tidak ada. */
    public function sampulUrl(): ?string
    {
        if (! $this->cover) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->exists('img/blog/'.$this->cover)
            ? asset('storage/img/blog/'.$this->cover)
            : null;
    }

    /**
     * Buat slug unik dari sebuah judul (mengabaikan record $ignoreId saat edit).
     */
    public static function makeSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * Catat satu kali baca.
     *
     * Kenaikannya lewat SQL (views + 1 di sisi basis data), bukan baca-lalu-
     * tulis di PHP: dua pengunjung yang membuka artikel yang sama pada saat
     * bersamaan sama-sama menulis angka lama + 1, dan satu hitungan hilang.
     */
    public function catatBaca(): void
    {
        static::whereKey($this->getKey())->update(['views' => DB::raw('views + 1')]);

        $tanggal = now()->toDateString();

        try {
            $baris = BlogPostRead::firstOrCreate(
                ['blog_post_id' => $this->getKey(), 'tanggal' => $tanggal],
                ['jumlah' => 0]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Dua permintaan membuat baris hari yang sama secara bersamaan;
            // yang kalah cukup memakai baris yang sudah jadi.
            $baris = BlogPostRead::where('blog_post_id', $this->getKey())->where('tanggal', $tanggal)->first();

            if (! $baris) {
                throw $e;
            }
        }

        BlogPostRead::whereKey($baris->getKey())->update(['jumlah' => DB::raw('jumlah + 1')]);
    }

    /** Jumlah baca dalam $hari terakhir (termasuk hari ini). */
    public function dibacaPeriode(int $hari = 30): int
    {
        return (int) $this->bacaHarian()
            ->where('tanggal', '>=', now()->subDays(max(1, $hari) - 1)->toDateString())
            ->sum('jumlah');
    }

    /**
     * Baca 30 hari terakhir. Memakai hasil withSum() bila daftarnya sudah
     * memuatnya, supaya halaman daftar tidak menembak satu kueri per baris.
     */
    public function baca30(): int
    {
        return (int) ($this->baca_30 ?? $this->dibacaPeriode(30));
    }

    /**
     * Sudah lama terbit tapi tidak ada yang membaca sebulan terakhir.
     *
     * Ini bukan vonis "artikelnya jelek" — hanya penanda supaya tulisan yang
     * diam tidak hilang begitu saja dari perhatian.
     */
    public function mandek(): bool
    {
        if ($this->keadaan()[0] !== 'Terbit') {
            return false;
        }

        $terbit = $this->published_at ?? $this->created_at;

        return $terbit
            && $terbit->lt(now()->subDays(self::HARI_MANDEK))
            && $this->baca30() === 0;
    }

    /** "3 hari lagi" / "5 jam lagi" untuk artikel terjadwal; null bila bukan. */
    public function hitungMundur(): ?string
    {
        if ($this->keadaan()[0] !== 'Terjadwal') {
            return null;
        }

        // ->locale('id') WAJIB: APP_LOCALE=en, tanpa ini keluar "2 days".
        return $this->published_at->locale('id')->diffForHumans(now(), [
            'syntax' => Carbon::DIFF_ABSOLUTE,
            'parts' => 1,
        ]).' lagi';
    }

    /** Daftar tag yang bersih (tanpa nilai kosong). */
    public function tagDaftar(): array
    {
        return array_values(array_filter(array_map('trim', (array) ($this->tags ?? [])), 'strlen'));
    }

    /**
     * Salin artikel sebagai draf baru.
     *
     * Berkas sampulnya ikut DISALIN, bukan dipakai bersama: kalau salah satu
     * artikel dihapus nanti, yang lain tidak ikut kehilangan gambarnya.
     */
    public function duplikat(): self
    {
        $judul = Str::limit('Salinan — '.$this->title, 180, '');

        $sampul = null;
        if ($this->cover && Storage::disk('public')->exists('img/blog/'.$this->cover)) {
            $sampul = 'blog_'.time().'_'.mt_rand(10000, 99999).'.'.pathinfo($this->cover, PATHINFO_EXTENSION);
            Storage::disk('public')->copy('img/blog/'.$this->cover, 'img/blog/'.$sampul);
        }

        return static::create([
            'title' => $judul,
            'slug' => static::makeSlug($judul),
            'category' => $this->category,
            'tags' => $this->tags,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'cover' => $sampul,
            'cover_alt' => $this->cover_alt,
            // Salinan SELALU mulai sebagai draf: menyalin artikel terbit lalu
            // ikut terbit berarti dua tulisan kembar tayang tanpa diminta.
            'status' => 'draft',
            'published_at' => null,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'focus_keyword' => $this->focus_keyword,
            'views' => 0,
            'is_featured' => false,
            'disimpan_manual' => true,
            'author' => 'admin',
        ]);
    }

    /**
     * Estimasi waktu baca (menit) dari isi artikel.
     */
    public function readingMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->body));

        return max(1, (int) ceil($words / 200));
    }
}
