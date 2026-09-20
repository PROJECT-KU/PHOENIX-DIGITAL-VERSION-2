<?php

namespace App\Livewire\Pages\Public\Contact;

use App\Models\CustomerMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman publik untuk melihat status satu tiket helpdesk.
 *
 * Dua jalan masuk:
 * 1. Tautan bertanda tangan dari surel tanda terima — langsung terbuka.
 * 2. Isi nomor tiket + surel, untuk yang surelnya sudah telanjur hilang.
 *
 * Yang ditampilkan HANYA milik pelanggan itu sendiri: status, pesannya, dan
 * balasan yang memang dikirim kepadanya. Catatan internal, penugasan, dan
 * keterangan teknis TIDAK pernah ikut.
 */
class LacakTiket extends Component
{
    public string $ticket = '';

    public string $email = '';

    public ?CustomerMessage $pesan = null;

    public bool $dicari = false;

    /** Keterangan tambahan dari pelanggan untuk tiket yang sama. */
    public string $tambahan = '';

    /** Penilaian kepuasan: 1 kecewa, 2 biasa saja, 3 puas. */
    public ?int $nilai = null;

    public string $nilaiKomentar = '';

    /** Tanda tangan tautan sudah kedaluwarsa (bukan sekadar tidak ada). */
    public bool $tautanKedaluwarsa = false;

    public function mount(Request $request, ?string $ticket = null)
    {
        $this->ticket = (string) ($ticket ?? $request->query('ticket', ''));

        // Tautan dari surel sudah bertanda tangan, jadi tidak perlu surel lagi.
        if ($this->ticket !== '' && $request->hasValidSignature()) {
            $this->pesan = CustomerMessage::where('ticket', $this->ticket)->first();
            $this->dicari = true;

            return;
        }

        // Tanda tangan ADA tapi tidak sah lagi: kemungkinan besar kedaluwarsa,
        // jadi jangan biarkan pelanggan menebak-nebak kenapa halamannya kosong.
        if ($this->ticket !== '' && $request->hasAny(['signature', 'expires'])) {
            $this->tautanKedaluwarsa = true;
        }
    }

    /**
     * Pelanggan menambahkan keterangan ke tiket yang SAMA.
     *
     * Ini pengganti jujur dari "balas surel ini": kotak masuk surel tidak ada
     * yang membaca otomatis, jadi jalur balik yang benar-benar sampai ke tiket
     * disediakan di sini.
     */
    public function tambahKeterangan(Request $request): void
    {
        if (! $this->pesan) {
            return;
        }

        $kunci = 'tambah-tiket:'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            $this->addError('tambahan', 'Terlalu banyak kiriman. Coba lagi beberapa menit lagi.');

            return;
        }

        $this->validate([
            'tambahan' => ['required', 'string', 'min:3', 'max:2000'],
        ], [], ['tambahan' => 'keterangan']);

        RateLimiter::hit($kunci, 300);

        $this->pesan->logs()->create([
            'jenis' => 'pelanggan',
            'nama_pelaku' => $this->pesan->name,
            'isi' => $this->tambahan,
        ]);

        // Kembalikan ke antrean: tiket yang sudah ditutup pun perlu dilihat lagi
        // kalau pelanggannya menambahkan sesuatu.
        $this->pesan->update([
            'read_at' => null,
            'tunda_sampai' => null,
            'status' => $this->pesan->selesai() ? 'open' : $this->pesan->status,
        ]);

        $this->tambahan = '';
        $this->pesan->refresh();
        session()->flash('tiket-sukses', 'Keterangan Anda sudah masuk ke tiket ini. Kami akan membacanya.');
    }

    /** Penilaian kepuasan, hanya untuk tiket yang sudah selesai. */
    public function nilai(int $angka): void
    {
        if (! $this->pesan || ! $this->pesan->selesai() || ! in_array($angka, [1, 2, 3], true)) {
            return;
        }

        $this->nilai = $angka;
        $this->pesan->update(['kepuasan' => $angka, 'kepuasan_at' => now()]);
        $this->pesan->refresh();
    }

    public function simpanKomentarNilai(): void
    {
        if (! $this->pesan?->kepuasan) {
            return;
        }

        $this->validate(['nilaiKomentar' => ['nullable', 'string', 'max:1000']]);

        $this->pesan->update(['kepuasan_komentar' => $this->nilaiKomentar ?: null]);
        $this->pesan->refresh();
        session()->flash('tiket-sukses', 'Terima kasih atas masukannya.');
    }

    public function cari(Request $request): void
    {
        $kunci = 'lacak-tiket:'.$request->ip();

        // Tanpa rem ini, nomor tiket + surel bisa ditebak beruntun.
        if (RateLimiter::tooManyAttempts($kunci, 6)) {
            $this->addError('ticket', 'Terlalu banyak percobaan. Coba lagi beberapa menit lagi.');

            return;
        }

        RateLimiter::hit($kunci, 300);

        $this->validate([
            'ticket' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255'],
        ], [], ['ticket' => 'nomor tiket', 'email' => 'alamat surel']);

        $this->pesan = CustomerMessage::where('ticket', trim($this->ticket))
            ->where('email', trim($this->email))
            ->first();

        $this->dicari = true;
    }

    /** Balasan yang boleh dilihat pelanggan — catatan internal tidak termasuk. */
    public function getBalasanProperty()
    {
        return $this->pesan
            ? $this->pesan->logs()->where('jenis', 'balasan')->get()
            : collect();
    }

    /** Keterangan tambahan yang dikirim pelanggan sendiri. */
    public function getKeteranganProperty()
    {
        return $this->pesan
            ? $this->pesan->logs()->where('jenis', 'pelanggan')->get()
            : collect();
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.contact.lacak-tiket', [
            // Halaman ini berisi isi pesan pelanggan — jangan sampai terindeks.
            'seoTitle' => 'Status Pesan Anda — Phoenix Digital',
            'seoDescription' => 'Lihat status pesan yang Anda kirim ke Phoenix Digital dengan nomor tiket.',
            'seoNoindex' => true,
        ]);
    }
}
