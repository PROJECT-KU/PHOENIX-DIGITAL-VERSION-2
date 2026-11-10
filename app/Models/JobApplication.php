<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobApplication extends Model
{
    use HasFactory;

    protected $table = 'tbl_job_applications';

    protected $fillable = ['job_id', 'name', 'email', 'phone', 'cv_path', 'cover_letter_path', 'lampiran_path', 'portofolio_url', 'sumber_info', 'ip_address',
        'nomor_lamaran', 'status', 'status_diubah_at', 'catatan_internal',
        'berkas_dibuka_at', 'berkas_dibuka_oleh'];

    protected $casts = [
        'status_diubah_at' => 'datetime',
        'berkas_dibuka_at' => 'datetime',
    ];

    /** Kode nomor lamaran per perusahaan; lamaran umum memakai kode grup (AST). */
    public const KODE_UNIT = ['asthana' => 'AST', 'reka' => 'RKA', 'orcha' => 'ORC'];

    /**
     * Tahap seleksi: label, warna lencana admin, dan keterangan yang dibaca
     * PELAMAR di halaman cek status situs (jadi ditulis untuk mereka).
     */
    public const STATUS = [
        'baru' => ['Diterima sistem', 'secondary', 'Lamaran Anda sudah kami terima dan menunggu ditinjau tim kami.'],
        'diseleksi' => ['Seleksi berkas', 'primary', 'Tim kami sedang meninjau CV dan berkas Anda.'],
        'wawancara' => ['Wawancara', 'info', 'Anda lolos seleksi berkas. Tim kami menghubungi Anda untuk jadwal wawancara.'],
        'diterima' => ['Diterima', 'success', 'Selamat! Anda diterima. Tim kami menghubungi Anda untuk langkah selanjutnya.'],
        'ditolak' => ['Belum sesuai', 'danger', 'Terima kasih atas minat Anda. Untuk saat ini profil Anda belum sesuai dengan kebutuhan posisi ini.'],
    ];

    /** Huruf & angka tanpa yang mudah tertukar (0/O, 1/I/L). */
    private const ABJAD_NOMOR = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /**
     * Nomor lamaran: KODE-ddmmyy-ACAK, mis. RKA-101126-7KQ2X.
     * Bagian acak 5 karakter (31^5 ≈ 28 juta kemungkinan per hari) dan
     * diperiksa unik — nomor ini sekaligus "kunci" cek status bersama email.
     */
    public static function buatNomor(?string $unit, ?Carbon $waktu = null): string
    {
        $kode = self::KODE_UNIT[$unit ?? 'asthana'] ?? 'AST';
        $tanggal = ($waktu ?? now())->format('dmy');

        do {
            $acak = '';
            for ($i = 0; $i < 5; $i++) {
                $acak .= self::ABJAD_NOMOR[random_int(0, strlen(self::ABJAD_NOMOR) - 1)];
            }
            $nomor = "{$kode}-{$tanggal}-{$acak}";
        } while (DB::table('tbl_job_applications')->where('nomor_lamaran', $nomor)->exists());

        return $nomor;
    }

    protected static function booted(): void
    {
        static::creating(function (JobApplication $lamaran) {
            $lamaran->status ??= 'baru';
            if (! $lamaran->nomor_lamaran) {
                $unit = $lamaran->job_id ? Lowongan::whereKey($lamaran->job_id)->value('unit') : null;
                $lamaran->nomor_lamaran = self::buatNomor($unit, $lamaran->created_at ?? now());
            }
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status][0] ?? ucfirst((string) $this->status);
    }

    public function getStatusWarnaAttribute(): string
    {
        return self::STATUS[$this->status][1] ?? 'secondary';
    }

    /** Dari mana pelamar tahu lowongan ini (isian opsional formulir situs). */
    public const SUMBER_INFO = [
        'situs' => 'Situs web Asthana',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'linkedin' => 'LinkedIn',
        'jobstreet' => 'Portal lowongan (JobStreet, Glints, dll.)',
        'kampus' => 'Kampus / sekolah',
        'teman' => 'Teman / kenalan',
        'lainnya' => 'Lainnya',
    ];

    /** Lamaran umum (talent pool) tidak terikat ke satu lowongan. */
    public function getPosisiLabelAttribute(): string
    {
        return $this->job?->title ?? 'Lamaran umum';
    }

    public function getSumberInfoLabelAttribute(): ?string
    {
        return self::SUMBER_INFO[$this->sumber_info] ?? null;
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class, 'job_id');
    }

    /** Riwayat: perubahan tahap, catatan tim, hasil kirim surel. */
    public function jejak(): HasMany
    {
        /*
         | Diurut created_at LALU id.
         |
         | Beberapa baris bisa lahir dalam detik yang sama (ubah tahap →
         | hasil surel), dan urutan yang cuma bersandar pada created_at
         | menampilkannya terbalik sesekali saja — tampak seperti data yang
         | kacau, bukan seperti bug yang bisa dicari.
         */
        return $this->hasMany(JejakLamaran::class, 'job_application_id')
            ->orderByDesc('created_at')->orderByDesc('id');
    }

    /** Apakah berkasnya masih ada (belum dihapus mengikuti masa simpan). */
    public function getAdaBerkasAttribute(): bool
    {
        return (bool) ($this->cv_path || $this->cover_letter_path || $this->lampiran_path);
    }

    /**
     * Kapan berkas lamaran ini dijadwalkan dihapus.
     *
     * Aturannya menyalin HapusBerkasPelamarKadaluarsa: 90 hari sesudah
     * diputuskan, atau 365 hari kalau tidak pernah diputuskan. Ditulis di
     * sini supaya admin bisa melihat tenggatnya SEBELUM berkasnya hilang —
     * selama ini satu-satunya tanda adalah tombol unduh yang tiba-tiba
     * lenyap.
     *
     * Dua tempat memang menyimpan angka yang sama, dan itu disengaja:
     * perintahnya menerima --hari lewat baris perintah, jadi angkanya tidak
     * bisa dibaca dari sana tanpa menjalankannya. Dijaga MasaSimpanBerkasTest.
     */
    public const HARI_SIMPAN_SESUDAH_PUTUSAN = 90;

    public const HARI_SIMPAN_DIDIAMKAN = 365;

    public function getBerkasHangusPadaAttribute(): ?Carbon
    {
        if (! $this->ada_berkas) {
            return null;
        }

        if (in_array($this->status, ['diterima', 'ditolak'], true)) {
            $dasar = $this->status_diubah_at ?? $this->created_at;

            return $dasar?->copy()->addDays(self::HARI_SIMPAN_SESUDAH_PUTUSAN);
        }

        return $this->created_at?->copy()->addDays(self::HARI_SIMPAN_DIDIAMKAN);
    }

    /**
     * Lamaran LAIN dari orang yang sama.
     *
     * Dicocokkan lewat email, bukan nama: nama ditulis ulang tiap melamar
     * (kapital, gelar, singkatan) sementara email hampir selalu sama. Nomor
     * telepon tidak dipakai sebagai pencocok karena satu nomor keluarga bisa
     * dipinjam dua orang.
     */
    public function lamaranLain()
    {
        return self::with('job')
            ->where('email', $this->email)
            ->where('id', '<>', $this->id)
            ->latest();
    }

    public function getCvUrlAttribute()
    {
        return Storage::disk('public')->url($this->cv_path);
    }

    public function getCoverLetterUrlAttribute()
    {
        return Storage::disk('public')->url($this->cover_letter_path);
    }
}
