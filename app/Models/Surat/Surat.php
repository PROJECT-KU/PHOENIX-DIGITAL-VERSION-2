<?php

namespace App\Models\Surat;

use App\Models\User;
use App\Support\Surat\NomorSuratKeluar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Satu surat di buku agenda.
 *
 * Istilahnya sengaja istilah administrasi, bukan istilah program: nomor
 * agenda, perihal, sifat, klasifikasi, disposisi. Admin yang memakai layar
 * ini sudah mengenal kata-kata itu dari buku agenda kertas, dan menamainya
 * ulang ("judul", "kategori", "prioritas") memaksanya menerjemahkan dua arah
 * setiap kali.
 */
class Surat extends Model
{
    use SoftDeletes;

    protected $table = 'surat';

    protected $fillable = [
        'arah', 'nomor_agenda', 'nomor_surat', 'tanggal_surat', 'tanggal_catat',
        'klasifikasi_id', 'sifat', 'perihal', 'dari', 'kepada', 'status',
        'lokasi_fisik', 'ringkasan', 'musnah_pada', 'dicatat_oleh',
        'urut_utama', 'urut_klasifikasi', 'kode_klasifikasi',
        'induk_id', 'tembusan', 'pemusnahan_id', 'penanggung_jawab_id',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_catat' => 'date',
        'musnah_pada' => 'date',
        'klasifikasi_id' => 'integer',
        'dicatat_oleh' => 'integer',
        'induk_id' => 'integer',
        'pemusnahan_id' => 'integer',
        'penanggung_jawab_id' => 'integer',
        'urut_utama' => 'integer',
        'urut_klasifikasi' => 'integer',
    ];

    public const ARAH = [
        'masuk' => 'Surat Masuk',
        'keluar' => 'Surat Keluar',
    ];

    /**
     * Sifat surat, dari yang paling biasa ke yang paling perlu dijaga.
     *
     * 'rahasia' ditaruh terakhir karena ia BUKAN tingkat kecepatan seperti
     * tiga yang lain, melainkan tingkat kerahasiaan. Disatukan dalam satu
     * kolom karena di buku agenda kertas pun satu kotak, dan surat rahasia
     * yang juga segera dicatat sebagai rahasia — yang perlu dijaga lebih
     * dulu daripada yang perlu dikebut.
     */
    public const SIFAT = [
        'biasa' => 'Biasa',
        'segera' => 'Segera',
        'sangat_segera' => 'Sangat Segera',
        'rahasia' => 'Rahasia',
    ];

    public const STATUS = [
        'baru' => 'Belum ditindak',
        'diproses' => 'Sedang diproses',
        'selesai' => 'Selesai',
        'diarsipkan' => 'Diarsipkan',
        /*
         | 'dimusnahkan' hanya bisa dicapai lewat berita acara, tidak lewat
         | tombol status biasa. Ia menyatakan berkas KERTASNYA sudah tidak
         | ada — keadaan yang tidak boleh diberikan atau dicabut sambil lalu.
         */
        'dimusnahkan' => 'Dimusnahkan',
    ];

    /** Status yang boleh dipilih lewat tombol di halaman detail. */
    public const STATUS_MANUAL = ['baru', 'diproses', 'selesai', 'diarsipkan'];

    /**
     * Kolom yang perubahannya dicatat di riwayat, beserta namanya di layar.
     *
     * musnah_pada sengaja TIDAK ada di sini: ia dihitung dari klasifikasi dan
     * tanggal surat, yang dua-duanya sudah dicatat. Kolom turunan yang punya
     * baris riwayat sendiri membuat satu perubahan terbaca seperti dua.
     */
    public const MEDAN_JEJAK = [
        'arah' => 'jenis surat',
        'nomor_surat' => 'nomor surat',
        'tanggal_surat' => 'tanggal surat',
        'tanggal_catat' => 'tanggal diterima/dikirim',
        'klasifikasi_id' => 'klasifikasi',
        'sifat' => 'sifat',
        'perihal' => 'perihal',
        'dari' => 'pengirim',
        'kepada' => 'tujuan',
        'tembusan' => 'tembusan',
        'lokasi_fisik' => 'tempat berkas',
        'ringkasan' => 'ringkasan isi',
        'induk_id' => 'surat yang ditanggapi',
        'penanggung_jawab_id' => 'karyawan yang menangani',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $surat) {
            if (blank($surat->nomor_agenda)) {
                $surat->nomor_agenda = self::nomorAgendaBaru((string) $surat->arah);
            }

            if (blank($surat->dicatat_oleh) && auth()->check()) {
                $surat->dicatat_oleh = auth()->id();
            }
        });

        /*
         | Hitungan nomor surat keluar dibaca ulang dari nomor yang BENAR-BENAR
         | tersimpan, setiap kali disimpan.
         |
         | Bukan dari apa yang diusulkan sistem: nomornya boleh diketik manual,
         | dan kalau hitungannya ikut usulan sementara kertasnya memakai nomor
         | lain, surat berikutnya akan diberi nomor yang sudah beredar. Dengan
         | dibaca dari teksnya, nomor manual pun menyambung rantainya.
         |
         | Surat MASUK dikosongkan: nomornya ditulis oleh instansi lain dan
         | kebetulan berbentuk mirip, sehingga ikut terhitung akan melompatkan
         | nomor surat keluar kita.
         */
        static::saving(function (self $surat) {
            if ($surat->arah !== 'keluar') {
                $surat->urut_utama = null;
                $surat->urut_klasifikasi = null;
                $surat->kode_klasifikasi = null;

                return;
            }

            $urai = NomorSuratKeluar::urai($surat->nomor_surat);

            $surat->urut_utama = $urai['urut_utama'] ?? null;
            $surat->urut_klasifikasi = $urai['urut_klasifikasi'] ?? null;
            $surat->kode_klasifikasi = $urai['kode_klasifikasi'] ?? null;
        });

        /*
         | Jejak perubahan dicatat DI MODEL, bukan di layar.
         |
         | Perubahan bisa datang dari borang, dari halaman detail, dari
         | perintah terjadwal, atau dari perbaikan data lewat tinker.
         | Riwayat yang hanya mencatat sebagian justru lebih menyesatkan
         | daripada tidak ada riwayat sama sekali: ia membuat orang
         | menyimpulkan "tidak ada yang mengubah" dari sesuatu yang cuma
         | tidak terpantau.
         */
        static::created(fn (self $surat) => $surat->catatJejak('dicatat', 'Surat masuk ke buku agenda.'));

        static::updated(function (self $surat) {
            if ($surat->wasChanged('status')) {
                $lama = self::STATUS[$surat->getOriginal('status')] ?? $surat->getOriginal('status');
                $surat->catatJejak('status', 'Status: '.$lama.' → '.$surat->status_label);
            }

            $berubah = collect(array_keys($surat->getChanges()))
                ->intersect(array_keys(self::MEDAN_JEJAK))
                ->map(fn ($medan) => self::MEDAN_JEJAK[$medan])
                ->values();

            if ($berubah->isNotEmpty()) {
                $surat->catatJejak('diubah', 'Yang diubah: '.$berubah->join(', ', ' dan '));
            }
        });

        static::deleted(function (self $surat) {
            // Hanya hapus lembut; pemusnahan punya jejaknya sendiri.
            if (! $surat->isForceDeleting()) {
                $surat->catatJejak('dihapus', 'Surat dipindahkan ke daftar terhapus.');
            }
        });

        static::restored(fn (self $surat) => $surat->catatJejak('dipulihkan', 'Surat dikembalikan ke buku agenda.'));
    }

    /* ----------------------------- RELASI ----------------------------- */

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(SuratKlasifikasi::class, 'klasifikasi_id');
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(SuratLampiran::class, 'surat_id');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(SuratDisposisi::class, 'surat_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /** Surat yang DITANGGAPI oleh surat ini. */
    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    /** Surat-surat yang menanggapi surat ini. */
    public function tanggapan(): HasMany
    {
        return $this->hasMany(self::class, 'induk_id');
    }

    public function jejak(): HasMany
    {
        return $this->hasMany(SuratJejak::class, 'surat_id');
    }

    public function pemusnahan(): BelongsTo
    {
        return $this->belongsTo(SuratPemusnahan::class, 'pemusnahan_id');
    }

    /** Karyawan yang memegang urusan surat ini. */
    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    /* ----------------------------- JEJAK ----------------------------- */

    /**
     * Tulis satu baris riwayat.
     *
     * Galatnya ditelan: riwayat adalah catatan SAMPINGAN, dan kegagalan
     * menulisnya tidak boleh menggagalkan penyimpanan suratnya sendiri —
     * surat yang tidak tersimpan jauh lebih merugikan daripada riwayat yang
     * bolong satu baris.
     */
    public function catatJejak(string $aksi, ?string $keterangan = null): void
    {
        try {
            SuratJejak::create([
                'surat_id' => $this->id,
                'aksi' => $aksi,
                'keterangan' => $keterangan,
                'oleh' => auth()->id(),
            ]);
        } catch (\Throwable) {
            // sengaja dibiarkan
        }
    }

    /* ----------------------------- NOMOR AGENDA ----------------------------- */

    /**
     * Nomor agenda berikutnya: SM-2026-0001 / SK-2026-0001.
     *
     * Berurut per ARAH dan per TAHUN, karena itulah cara buku agenda kertas
     * disusun — surat masuk dan surat keluar punya buku sendiri, dan tiap
     * Januari nomornya mulai dari satu lagi.
     *
     * Dihitung dari nomor TERBESAR yang sudah ada, bukan dari jumlah baris:
     * surat yang dihapus (lembut) tetap memegang nomornya, dan menghitung
     * jumlah baris akan memberikan nomor yang sudah dipakai. Baris terhapus
     * ikut dilihat lewat withTrashed() justru supaya nomornya tidak
     * dipakai dua kali.
     */
    public static function nomorAgendaBaru(string $arah, ?int $tahun = null): string
    {
        $awalan = $arah === 'keluar' ? 'SK' : 'SM';
        $tahun ??= (int) now()->format('Y');
        $pola = "{$awalan}-{$tahun}-";

        $terakhir = static::withTrashed()
            ->where('nomor_agenda', 'like', $pola.'%')
            ->orderByDesc('nomor_agenda')
            ->value('nomor_agenda');

        $urut = $terakhir ? ((int) substr((string) $terakhir, strlen($pola))) + 1 : 1;

        return $pola.str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    /* ----------------------------- SCOPE ----------------------------- */

    public function scopeArah(Builder $q, ?string $arah): Builder
    {
        return $arah ? $q->where('arah', $arah) : $q;
    }

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeSifat(Builder $q, ?string $sifat): Builder
    {
        return $sifat ? $q->where('sifat', $sifat) : $q;
    }

    public function scopeKlasifikasi(Builder $q, $id): Builder
    {
        return $id ? $q->where('klasifikasi_id', $id) : $q;
    }

    public function scopePeriode(Builder $q, ?string $dari, ?string $sampai): Builder
    {
        return $q->when($dari, fn (Builder $b) => $b->whereDate('tanggal_surat', '>=', $dari))
            ->when($sampai, fn (Builder $b) => $b->whereDate('tanggal_surat', '<=', $sampai));
    }

    /**
     * Pencarian satu kotak.
     *
     * Admin yang awam tidak akan memilih "cari berdasarkan" lebih dulu — ia
     * mengetik apa yang diingatnya, dan yang diingat bisa nomor agenda,
     * nomor suratnya, perihalnya, atau nama pengirimnya. Satu kotak yang
     * mencari di semuanya lebih jujur daripada enam kotak yang lima di
     * antaranya selalu kosong.
     */
    public function scopeCari(Builder $q, ?string $kata): Builder
    {
        $kata = trim((string) $kata);

        if ($kata === '') {
            return $q;
        }

        return $q->where(function (Builder $b) use ($kata) {
            foreach (['nomor_agenda', 'nomor_surat', 'perihal', 'dari', 'kepada', 'lokasi_fisik'] as $kolom) {
                $b->orWhere($kolom, 'like', '%'.$kata.'%');
            }
        });
    }

    /**
     * Hanya surat yang boleh dilihat orang ini.
     *
     * Surat bersifat RAHASIA disembunyikan dari siapa pun yang tidak
     * memegang 'view_surat_rahasia'. Arsip surat menyimpan hasil audit,
     * teguran, dan perjanjian yang isinya tidak untuk semua staf — sementara
     * nomor agenda, perihal, dan pindaiannya ada dalam satu daftar yang sama
     * dengan undangan rapat. Tanpa saringan ini, memberi seorang staf akses
     * untuk mencatat surat masuk berarti memberinya seluruh isi lemari.
     *
     * Dipasang di KUERI, bukan di tampilan: menyaring setelah data terambil
     * membuat jumlah di tab, angka dasbor, dan ekspor tetap memuat yang
     * disembunyikan — dan angka yang tidak cocok dengan daftarnya adalah cara
     * paling pelan untuk mengetahui bahwa ada yang bocor.
     */
    public function scopeTerlihatOleh(Builder $q, ?User $pengguna): Builder
    {
        if ($pengguna?->hasPermission('view_surat_rahasia')) {
            return $q;
        }

        return $q->where('sifat', '!=', 'rahasia');
    }

    /** Boleh dibuka orang ini? Dipakai untuk menjaga satu baris, bukan daftar. */
    public function bolehDilihat(?User $pengguna): bool
    {
        return $this->sifat !== 'rahasia' || (bool) $pengguna?->hasPermission('view_surat_rahasia');
    }

    /** Belum tuntas: masih menunggu dikerjakan seseorang. */
    public function scopeBelumSelesai(Builder $q): Builder
    {
        return $q->whereIn('status', ['baru', 'diproses']);
    }

    /* ----------------------------- TAMPILAN ----------------------------- */

    public function getArahLabelAttribute(): string
    {
        return self::ARAH[$this->arah] ?? $this->arah;
    }

    public function getSifatLabelAttribute(): string
    {
        return self::SIFAT[$this->sifat] ?? $this->sifat;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * Nama bulan Indonesia dipaksa lewat locale('id').
     *
     * APP_LOCALE=en di .env, jadi format() bawaan akan menulis "November"
     * sebagai "November" tetapi "Agustus" sebagai "August" — campur dua
     * bahasa dalam satu daftar.
     */
    public function getTanggalSuratTeksAttribute(): string
    {
        return $this->tanggal_surat?->locale('id')->translatedFormat('d F Y') ?? '-';
    }

    public function getTanggalCatatTeksAttribute(): string
    {
        return $this->tanggal_catat?->locale('id')->translatedFormat('d F Y') ?? '-';
    }

    public function getMusnahPadaTeksAttribute(): string
    {
        return $this->musnah_pada?->locale('id')->translatedFormat('d F Y') ?? 'Belum ditentukan';
    }

    /** Lawan bicara surat ini: pengirimnya kalau masuk, tujuannya kalau keluar. */
    public function getPihakAttribute(): string
    {
        $nilai = $this->arah === 'keluar' ? $this->kepada : $this->dari;

        return trim((string) $nilai) ?: 'Tidak dicatat';
    }

    /** Label kolom "pihak" menurut arahnya — dipakai sebagai judul kolom tabel. */
    public function getPihakLabelAttribute(): string
    {
        return $this->arah === 'keluar' ? 'Kepada' : 'Dari';
    }

    /**
     * Sudah lewat tanggal boleh musnah?
     *
     * Bukan perintah memusnahkan — hanya penanda bahwa masa simpannya habis.
     * Keputusan memusnahkan arsip selalu milik manusia.
     */
    public function getSudahBolehMusnahAttribute(): bool
    {
        return $this->musnah_pada !== null && $this->musnah_pada->isPast();
    }

    /**
     * Hitung tanggal boleh musnah dari retensi klasifikasinya.
     *
     * Dasarnya tanggal surat, bukan tanggal dicatat: masa simpan arsip
     * dihitung dari umur dokumennya, dan surat lama yang baru dicatat hari
     * ini tidak jadi muda karenanya.
     *
     * Mengembalikan null bila klasifikasinya belum punya retensi — dan null
     * itu disimpan apa adanya, supaya "belum ditentukan" tidak pernah
     * berubah jadi tanggal karangan.
     */
    public function hitungMusnah(): ?string
    {
        $tahun = $this->klasifikasi?->retensi_tahun;

        if ($tahun === null || ! $this->tanggal_surat) {
            return null;
        }

        return $this->tanggal_surat->copy()->addYears($tahun)->toDateString();
    }
}
