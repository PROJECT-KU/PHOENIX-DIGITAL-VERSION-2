<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CustomerMessage extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($message) {
            // Format TKT-XXXX-XXXX, diulang sampai benar-benar belum terpakai.
            // Kolomnya unique(): tanpa pemeriksaan ini, satu tabrakan acak
            // (sekecil apa pun peluangnya) muncul sebagai galat 500 di
            // formulir kontak — ditanggung pelanggan yang sedang mengeluh.
            do {
                $nomor = 'TKT-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
            } while (static::withTrashed()->where('ticket', $nomor)->exists());

            $message->ticket = $nomor;
        });
    }

    protected $fillable = [
        'ticket',
        'status',
        'priority',
        'name',
        'email',
        'no_telp',
        'message',
        'ip_address',
        'user_agent',
        'read_at',
        'kategori',
        'assigned_to',
        'replied_at',
        'replied_by',
        'is_spam',
        'merged_into',
        'tunda_sampai',
        'kepuasan',
        'kepuasan_at',
        'kepuasan_komentar',
        'minta_nilai_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'is_spam' => 'boolean',
        'tunda_sampai' => 'datetime',
        'kepuasan_at' => 'datetime',
        'minta_nilai_at' => 'datetime',
    ];

    // ===== Relasi =====

    /** Petugas yang memegang tiket ini. */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Yang pertama kali mencatat balasan. */
    public function pembalas()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /** Linimasa tiket: dibaca, status, tugas, balasan, catatan internal. */
    public function logs()
    {
        return $this->hasMany(CustomerMessageLog::class)->orderBy('created_at');
    }

    public function lampiran()
    {
        return $this->hasMany(CustomerMessageAttachment::class)->orderBy('id');
    }

    /** Tiket induk bila tiket ini digabungkan ke tiket lain. */
    public function induk()
    {
        return $this->belongsTo(CustomerMessage::class, 'merged_into');
    }

    /** Tiket lain yang digabungkan KE tiket ini. */
    public function gabungan()
    {
        return $this->hasMany(CustomerMessage::class, 'merged_into')->withTrashed();
    }

    /**
     * Kirim ajakan menilai — sekali saja per tiket, dan hanya kalau ada surelnya.
     *
     * Dikirim SESUDAH respons: hosting ini tidak menjalankan queue:work sebagai
     * proses tetap, dan admin tidak boleh menunggui SMTP saat menutup tiket.
     */
    public function mintaPenilaian(): void
    {
        if ($this->minta_nilai_at || $this->kepuasan || blank($this->email) || ! $this->selesai()) {
            return;
        }

        $this->forceFill(['minta_nilai_at' => now()])->saveQuietly();
        $pesan = $this;

        app()->terminating(function () use ($pesan) {
            try {
                \Illuminate\Support\Facades\Mail::to($pesan->email)->send(new \App\Mail\MintaPenilaianMail($pesan));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim ajakan menilai '.$pesan->ticket.': '.$e->getMessage());
            }
        });
    }

    /** [label, ikon, warna] penilaian pelanggan. */
    public function tampilanKepuasan(): ?array
    {
        return match ($this->kepuasan) {
            3 => ['Puas', 'bi-emoji-smile-fill', '#16a34a'],
            2 => ['Biasa saja', 'bi-emoji-neutral-fill', '#d97706'],
            1 => ['Kecewa', 'bi-emoji-frown-fill', '#dc2626'],
            default => null,
        };
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /** Tiket yang masih perlu ditangani (bukan selesai/ditutup). */
    public function scopeBerjalan($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }

    public function belumDibaca(): bool
    {
        return $this->read_at === null;
    }

    public function selesai(): bool
    {
        return in_array($this->status, ['resolved', 'closed'], true);
    }

    /** [label, kelas lencana, warna] untuk tiap status. */
    public function tampilanStatus(): array
    {
        return match ($this->status) {
            'open' => ['Terbuka', 'is-kuning', '#d97706'],
            'pending' => ['Tertunda', 'is-abu', '#64748b'],
            'in_progress' => ['Diproses', 'is-biru', '#2563eb'],
            'resolved' => ['Selesai', 'is-hijau', '#16a34a'],
            'closed' => ['Ditutup', 'is-abu', '#64748b'],
            default => [ucfirst((string) $this->status), 'is-abu', '#64748b'],
        };
    }

    /** [label, kelas, warna] untuk tiap prioritas. */
    public function tampilanPrioritas(): array
    {
        return match ($this->priority) {
            'urgent' => ['Mendesak', 'is-mendesak', '#dc2626'],
            'high' => ['Tinggi', 'is-tinggi', '#ea580c'],
            'medium' => ['Sedang', 'is-sedang', '#d97706'],
            default => ['Rendah', 'is-rendah', '#64748b'],
        };
    }

    /**
     * Berapa lama tiket ini menunggu dijawab (jam).
     *
     * Dihitung sampai DIBACA; sesudah dibaca, umurnya berhenti — angka yang
     * terus tumbuh untuk tiket yang sudah ditangani cuma jadi kecemasan palsu.
     */
    public function menungguJam(): int
    {
        $sampai = $this->read_at ?: now();

        return $this->created_at
            ? \App\Support\JamKerja::selisihJam($this->created_at, $sampai)
            : 0;
    }

    /**
     * Belum dibaca dan sudah menunggu cukup lama untuk pantas ditandai.
     *
     * Perbandingannya sengaja di sini, bukan di dalam @if di Blade: teks
     * ">=" sesudah direktif membuat Livewire melewatkan penanda morph-nya,
     * dan penanda yang timpang merusak pembaruan Livewire berikutnya.
     */
    public function menungguLama(int $jam = 3): bool
    {
        return $this->belumDibaca() && $this->menungguJam() >= $jam;
    }

    /** Tautan WhatsApp untuk membalas pengirim (kosong bila nomornya tidak ada). */
    public function tautanWa(?string $pesan = null): ?string
    {
        $nomor = preg_replace('/\D+/', '', (string) $this->no_telp);
        if ($nomor === '' || $nomor === null) {
            return null;
        }

        $nomor = str_starts_with($nomor, '0') ? '62'.substr($nomor, 1) : $nomor;

        return 'https://wa.me/'.$nomor.($pesan ? '?text='.rawurlencode($pesan) : '');
    }

    /** Tautan surel balasan, lengkap dengan nomor tiket di perihalnya. */
    public function tautanEmail(): ?string
    {
        if (blank($this->email)) {
            return null;
        }

        return 'mailto:'.$this->email.'?subject='.rawurlencode('Balasan '.$this->ticket.' — Phoenix Digital');
    }

    // ===== Tindak lanjut =====

    public function sudahDibalas(): bool
    {
        return $this->replied_at !== null;
    }

    /** Target membalas (jam) menurut prioritasnya. */
    public function batasJam(): int
    {
        return (int) (config('helpdesk.batas_jam')[$this->priority] ?? 24);
    }

    /**
     * Kapan tiket ini seharusnya sudah dibalas.
     *
     * Dihitung dengan JAM KERJA (lihat App\Support\JamKerja): tiket yang masuk
     * pukul 23.00 tidak dianggap terlambat pukul 01.00.
     */
    public function tenggat(): ?\Illuminate\Support\Carbon
    {
        return $this->created_at
            ? \App\Support\JamKerja::maju($this->created_at, $this->batasJam())
            : null;
    }

    public function ditunda(): bool
    {
        return $this->tunda_sampai && $this->tunda_sampai->isFuture();
    }

    /**
     * Lewat batas = belum dibalas DAN sudah melewati tenggatnya.
     *
     * Tiket yang sudah dibalas tidak pernah "lewat batas" walau jawabannya
     * telat — yang perlu ditandai adalah yang masih menggantung.
     */
    public function lewatBatas(): bool
    {
        if ($this->sudahDibalas() || $this->selesai() || $this->ditunda()) {
            return false;
        }

        return $this->tenggat()?->isPast() ?? false;
    }

    public function labelKategori(): ?string
    {
        return $this->kategori ? (config('helpdesk.kategori')[$this->kategori] ?? $this->kategori) : null;
    }

    /**
     * Lama menunggu dalam kalimat: menit untuk tiket yang baru masuk, jam,
     * lalu hari. "0 jam" untuk pesan lima menit lalu cuma membingungkan.
     */
    public function menungguTeks(): string
    {
        $sampai = $this->read_at ?: now();
        // Ikut JAM KERJA, sama seperti menungguJam() — kalau tidak, dua angka
        // di kartu yang sama bisa bercerita beda.
        $menit = $this->created_at
            ? \App\Support\JamKerja::selisihMenit($this->created_at, $sampai)
            : 0;

        if ($menit < 60) {
            return max(1, $menit).' menit';
        }

        $jam = intdiv($menit, 60);

        return $jam < 24 ? $jam.' jam' : intdiv($jam, 24).' hari';
    }

    /** Tulis satu baris linimasa. Nama pelaku ikut dibekukan di barisnya. */
    public function catat(string $jenis, ?string $isi = null, ?string $kanal = null): CustomerMessageLog
    {
        return $this->logs()->create([
            'user_id' => auth()->id(),
            'nama_pelaku' => auth()->user()?->name,
            'jenis' => $jenis,
            'kanal' => $kanal,
            'isi' => $isi,
        ]);
    }

    // ===== Kaitan dengan data pelanggan =====

    /** Pelanggan terdaftar dengan surel/nomor yang sama (kalau ada). */
    public function pelangganTerdaftar(): ?Customer
    {
        return once(function () {
            if (filled($this->email) && $c = Customer::where('email', $this->email)->first()) {
                return $c;
            }

            return filled($this->no_telp) ? Customer::cariDariNoHp($this->no_telp) : null;
        });
    }

    /** Pesan lain dari orang yang sama — surel ATAU nomor yang sama. */
    public function pesanLain(int $batas = 5)
    {
        $inti = Customer::normalisasiNoHp($this->no_telp);

        return static::query()
            ->whereKeyNot($this->getKey())
            ->where(function ($q) use ($inti) {
                if (filled($this->email)) {
                    $q->orWhere('email', $this->email);
                }
                if ($inti !== '') {
                    $q->orWhere('no_telp', 'like', '%'.$inti.'%');
                }
                // Tanpa kontak apa pun, jangan sampai kueri ini cocok ke semua baris.
                $q->orWhereRaw('1 = 0');
            })
            ->latest()
            ->limit($batas)
            ->get();
    }

    /** Petugas yang boleh dititipi tiket: akun aktif yang boleh melihat helpdesk. */
    public static function petugasTersedia()
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('role.permissions', fn ($q) => $q->where('name', 'view_customer_message'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Pernah mengirim spam sebelumnya (surel atau nomor yang sama).
     *
     * Dipakai sebagai PENANDA saja, bukan penyaring otomatis: tiket tetap
     * masuk antrean, cuma diberi tahu supaya admin membacanya dengan curiga.
     */
    public function pernahSpam(): bool
    {
        return once(function () {
            $inti = Customer::normalisasiNoHp($this->no_telp);

            return static::withTrashed()
                ->where('is_spam', true)
                ->whereKeyNot($this->getKey())
                ->where(function ($q) use ($inti) {
                    if (filled($this->email)) {
                        $q->orWhere('email', $this->email);
                    }
                    if ($inti !== '') {
                        $q->orWhere('no_telp', 'like', '%'.$inti.'%');
                    }
                    $q->orWhereRaw('1 = 0');
                })
                ->exists();
        });
    }

    /**
     * Gabungkan tiket $lain ke tiket ini.
     *
     * Isi dan linimasa tiket lama TIDAK dipindah — ia ditutup, diarsipkan, dan
     * ditandai induknya, lalu isinya disalin sebagai satu baris linimasa di
     * sini. Dengan begitu kedua tiket tetap bisa ditelusuri apa adanya.
     */
    public function gabungkan(self $lain): bool
    {
        if ($lain->is($this) || $lain->merged_into) {
            return false;
        }

        $this->catat('gabung', 'Dari tiket '.$lain->ticket.' ('.$lain->created_at?->format('d/m/Y H:i').'):'."\n".$lain->message);

        $lain->catat('gabung', 'Digabungkan ke tiket '.$this->ticket.'.');
        $lain->update(['status' => 'closed', 'merged_into' => $this->getKey()]);
        $lain->delete();

        return true;
    }

    // ===== Scope tambahan =====

    public function scopeBukanSpam($query)
    {
        return $query->where('is_spam', false);
    }

    public function scopeMilik($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Batasi ke tiket yang boleh dilihat pengguna ini.
     *
     * Mengikuti konvensi view_all_* di modul lain: tanpa izin itu, petugas
     * hanya melihat tiket miliknya sendiri plus yang belum dipegang siapa pun
     * (supaya triase tetap bisa jalan).
     */
    public function scopeTerlihatOleh($query, $user)
    {
        if (! $user || $user->hasPermission('view_all_customer_message')) {
            return $query;
        }

        return $query->where(fn ($q) => $q->where('assigned_to', $user->id)->orWhereNull('assigned_to'));
    }

    public function scopeBelumDibalas($query)
    {
        return $query->whereNull('replied_at');
    }

    /**
     * Tiket yang sudah lewat batas waktu membalas.
     *
     * Batasnya beda-beda per prioritas, jadi disusun sebagai gabungan OR di
     * PHP — bukan DATE_ADD di SQL, yang tulisannya beda antara MySQL (server)
     * dan SQLite (uji).
     */
    public function scopeLewatBatas($query)
    {
        return $query->belumDibalas()->berjalan()
            ->where(fn ($q) => $q->whereNull('tunda_sampai')->orWhere('tunda_sampai', '<=', now()))
            ->where(function ($q) {
                foreach (config('helpdesk.batas_jam') as $prioritas => $jam) {
                    // Titik potongnya dimundurkan lewat JAM KERJA, bukan jam
                    // kalender — lihat alasannya di App\Support\JamKerja.
                    $batas = \App\Support\JamKerja::mundur(now(), (int) $jam);

                    $q->orWhere(fn ($sub) => $sub->where('priority', $prioritas)
                        ->where('created_at', '<', $batas));
                }
            });
    }

    public function scopeDitunda($query)
    {
        return $query->whereNotNull('tunda_sampai')->where('tunda_sampai', '>', now());
    }
}
