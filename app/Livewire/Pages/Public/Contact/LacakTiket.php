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

    public function mount(Request $request, ?string $ticket = null)
    {
        $this->ticket = (string) ($ticket ?? $request->query('ticket', ''));

        // Tautan dari surel sudah bertanda tangan, jadi tidak perlu surel lagi.
        if ($this->ticket !== '' && $request->hasValidSignature()) {
            $this->pesan = CustomerMessage::where('ticket', $this->ticket)->first();
            $this->dicari = true;
        }
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

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.contact.lacak-tiket');
    }
}
