<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Satu kegiatan di kalender: rapat, tenggat, acara, libur.
 */
class Kegiatan extends Model
{
    use HasFactory, HasUuids;

    /**
     * Jenis kegiatan beserta warnanya.
     *
     * Tiap jenis punya DUA warna: 'warna' untuk keadaan terpilih dan pita di
     * kalender, 'lembut' untuk latar saat tidak terpilih. Warna lembutnya
     * ditulis tetap, bukan dihitung dari yang pekat, supaya hasilnya bisa
     * dilihat langsung di sini dan tidak berubah diam-diam.
     *
     * Nadanya sengaja disamakan dengan tombol bawaan lemon (ungu, mawar,
     * kuning) agar kalender tidak terasa seperti halaman dari aplikasi lain.
     */
    public const JENIS = [
        'rapat' => ['label' => 'Rapat', 'warna' => '#6366f1', 'lembut' => '#eef0ff', 'ikon' => 'people-fill'],
        'tenggat' => ['label' => 'Tenggat', 'warna' => '#f43f5e', 'lembut' => '#ffeef1', 'ikon' => 'flag-fill'],
        'acara' => ['label' => 'Acara', 'warna' => '#0ea5e9', 'lembut' => '#e8f6fe', 'ikon' => 'stars'],
        'libur' => ['label' => 'Libur', 'warna' => '#10b981', 'lembut' => '#e7f8f2', 'ikon' => 'sun-fill'],
        'lainnya' => ['label' => 'Lainnya', 'warna' => '#94a3b8', 'lembut' => '#f1f5f9', 'ikon' => 'three-dots'],
    ];

    protected $fillable = [
        'judul',
        'deskripsi',
        'jenis',
        'lokasi',
        'mulai',
        'selesai',
        'seharian',
        'dibuat_oleh',
    ];

    protected $casts = [
        'mulai' => 'datetime',
        'selesai' => 'datetime',
        'seharian' => 'boolean',
    ];

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function peserta(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kegiatan_peserta')->withTimestamps();
    }

    /**
     * Kegiatan yang MENYENTUH rentang tanggal ini, terurut waktu mulai.
     *
     * Bukan yang mulainya di dalam rentang: kegiatan 30 Agustus–2 September
     * harus ikut terlihat saat yang dibuka September, dan sebaliknya. Karena
     * itu yang diuji adalah tumpang tindihnya, bukan tanggal mulainya.
     */
    public function scopeDalamRentang($query, Carbon $awal, Carbon $akhir)
    {
        $mulaiRentang = $awal->copy()->startOfDay();
        $akhirRentang = $akhir->copy()->endOfDay();

        return $query
            ->where('mulai', '<=', $akhirRentang)
            ->where(function ($w) use ($mulaiRentang) {
                // selesai kosong berarti kegiatannya hanya sepanjang hari mulainya.
                $w->where('selesai', '>=', $mulaiRentang)
                    ->orWhere(fn ($x) => $x->whereNull('selesai')->where('mulai', '>=', $mulaiRentang));
            })
            ->orderBy('mulai');
    }

    /**
     * Kapan kegiatan ini benar-benar berakhir.
     *
     * Tanpa waktu selesai, sebuah kegiatan berakhir di penghujung hari
     * mulainya — bukan di detik mulainya. Kalau tidak, rapat pukul 09.00 yang
     * belum diisi jam selesainya akan dihitung berdurasi nol dan hilang dari
     * kisi begitu jam 09.00 lewat.
     */
    public function akhirEfektif(): Carbon
    {
        return $this->selesai ?: $this->mulai->copy()->endOfDay();
    }

    /** Membentang lebih dari satu hari? */
    public function beberapaHari(): bool
    {
        return ! $this->akhirEfektif()->isSameDay($this->mulai);
    }

    /**
     * Kegiatan yang menyangkut satu orang: ia pesertanya, atau ia yang membuatnya.
     *
     * Pembuat ikut disertakan karena orang yang menjadwalkan rapat jelas perlu
     * melihatnya di agendanya sendiri, meski lupa mencentang namanya.
     */
    public function scopeMilik($query, $idPengguna)
    {
        return $query->where(function ($w) use ($idPengguna) {
            $w->where('dibuat_oleh', $idPengguna)
                ->orWhereHas('peserta', fn ($p) => $p->where('users.id', $idPengguna));
        });
    }

    /** Agenda mendatang milik satu orang, terurut paling dekat lebih dulu. */
    public function scopeAgenda($query, $idPengguna)
    {
        return $query->milik($idPengguna)
            ->where(fn ($w) => $w->where('mulai', '>=', now()->startOfDay()))
            ->orderBy('mulai');
    }

    public function label(): string
    {
        return self::JENIS[$this->jenis]['label'] ?? 'Lainnya';
    }

    public function warna(): string
    {
        return self::JENIS[$this->jenis]['warna'] ?? '#94a3b8';
    }

    public function lembut(): string
    {
        return self::JENIS[$this->jenis]['lembut'] ?? '#f1f5f9';
    }

    /**
     * Nada warna untuk membedakan dua kegiatan berjenis SAMA yang bersebelahan.
     *
     * Dua acara yang tanggalnya bertumpuk sama-sama biru, dan sebagai dua
     * batang panjang yang berdempetan keduanya terbaca seperti satu urusan.
     * Memberi tiap kegiatan warnanya sendiri akan menyelesaikan itu, tapi
     * merusak legenda di batang saring — warna tidak lagi berarti jenis.
     *
     * Jalan tengahnya: nada yang berbeda dalam KELUARGA warna yang sama. Biru
     * tetap biru, tapi dua batang biru bisa dibedakan.
     *
     * Diambil dari id, bukan diacak: satu kegiatan harus berwarna sama setiap
     * kali halamannya dimuat.
     */
    /**
     * Tiga nada: [pergeseran rona, pergeseran kecerahan].
     *
     * Rona ikut digeser, bukan kecerahan saja. Dengan kecerahan saja, warna
     * yang sudah gelap (hijau Libur) mentok di pagar bawah dan dua nadanya
     * berakhir nyaris kembar.
     *
     * Ronanya hanya 8 derajat, tidak lebih. Pada 14 derajat, nada terang Libur
     * (hijau menuju pirus) dan nada gelap Acara (biru menuju pirus) bertemu di
     * tengah dan justru saling tertukar — dua jenis berbeda terlihat sewarna,
     * kesalahan yang jauh lebih buruk daripada dua kegiatan sejenis yang mirip.
     * Selisihnya ditambal lewat kecerahan, yang tidak pernah menyeberang jenis.
     *
     * Nada pertama sengaja nol: sepertiga kegiatan lalu berwarna PERSIS seperti
     * chip legendanya, jadi hubungan warna-dengan-jenis tidak pernah kabur.
     */
    private const NADA = [[0, 0], [-8, -12], [8, 8]];

    public function nada(): int
    {
        return abs(crc32((string) $this->id)) % count(self::NADA);
    }

    /** Warna pekat untuk balok di kisi, sudah bernada. */
    public function warnaBalok(): string
    {
        [$rona, $terang] = self::NADA[$this->nada()];

        return self::geser($this->warna(), $rona, $terang);
    }

    /** Latar balok: ronanya ikut, kecerahannya nyaris tidak — supaya tetap pucat. */
    public function lembutBalok(): string
    {
        [$rona, $terang] = self::NADA[$this->nada()];

        return self::geser($this->lembut(), $rona, $terang * 0.15);
    }

    /**
     * Menggeser rona & kecerahan sebuah warna heks.
     *
     * Tanpa pergeseran sama sekali, warnanya dikembalikan apa adanya — bolak-balik
     * lewat HSL selalu meleset sedikit karena pembulatan, dan meleset sedikit saja
     * sudah cukup membuat balok tidak lagi sewarna dengan chip legendanya.
     */
    private static function geser(string $heks, float $rona, float $terang): string
    {
        if ($rona == 0.0 && $terang == 0.0) {
            return $heks;
        }

        [$r, $g, $b] = sscanf(ltrim($heks, '#'), '%2x%2x%2x');
        [$h, $s, $l] = self::keHsl($r, $g, $b);

        // Pagar 30%-62% hanya untuk warna pekat; latar lembut memang harus pucat.
        $lBaru = $l < 70 ? max(30, min(62, $l + $terang)) : max(0, min(100, $l + $terang));

        return self::keHeks(fmod($h + $rona + 360, 360), $s, $lBaru);
    }

    /** @return array{0: float, 1: float, 2: float} hue 0-360, saturation & lightness 0-100 */
    private static function keHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $maks = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($maks + $min) / 2;
        $d = $maks - $min;

        if ($d == 0.0) {
            return [0.0, 0.0, $l * 100];
        }

        $s = $l > 0.5 ? $d / (2 - $maks - $min) : $d / ($maks + $min);

        $h = match ($maks) {
            $r => (($g - $b) / $d) + ($g < $b ? 6 : 0),
            $g => (($b - $r) / $d) + 2,
            default => (($r - $g) / $d) + 4,
        };

        return [$h * 60, $s * 100, $l * 100];
    }

    private static function keHeks(float $h, float $s, float $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return sprintf('#%02x%02x%02x',
            (int) round(($r + $m) * 255),
            (int) round(($g + $m) * 255),
            (int) round(($b + $m) * 255),
        );
    }

    public function ikon(): string
    {
        return self::JENIS[$this->jenis]['ikon'] ?? 'three-dots';
    }

    /**
     * Rentang waktu siap tampil.
     *
     * Kegiatan seharian tidak menyebut jam sama sekali — menuliskan "00:00"
     * membuat orang mengira acaranya dimulai tengah malam.
     */
    public function rentangWaktu(): string
    {
        if ($this->seharian) {
            return 'Seharian';
        }

        $mulai = $this->mulai->translatedFormat('H:i');

        if (! $this->selesai) {
            return $mulai;
        }

        return $this->selesai->isSameDay($this->mulai)
            ? $mulai.' – '.$this->selesai->translatedFormat('H:i')
            : $mulai.' – '.$this->selesai->translatedFormat('d M, H:i');
    }

    /** Sudah lewat? Dipakai untuk meredupkan kegiatan lampau. */
    public function sudahLewat(): bool
    {
        return ($this->selesai ?: $this->mulai)->isPast();
    }
}
