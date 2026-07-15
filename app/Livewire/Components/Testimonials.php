<?php

namespace App\Livewire\Components;

use App\Models\Customer;
use App\Models\Testimoni;
use Livewire\Component;

class Testimonials extends Component
{
    public bool $submitted = false;

    public string $nama = '';

    public string $peran = '';

    /** Dipakai HANYA utk mencocokkan pesanan — tidak pernah ditampilkan publik. */
    public string $no_hp = '';

    public int $rating = 5;

    public string $pesan = '';

    /** Diisi setelah kirim: pembeli terverifikasi & jadi calon member? */
    public bool $terverifikasi = false;

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|min:2|max:60',
            'peran' => 'nullable|string|max:100',
            'no_hp' => 'required|string|min:8|max:20',
            'rating' => 'required|integer|min:1|max:5',
            'pesan' => 'required|string|min:10|max:500',
        ];
    }

    protected $messages = [
        'nama.required' => 'Nama wajib diisi.',
        'no_hp.required' => 'Nomor WhatsApp wajib diisi untuk mencocokkan pesananmu.',
        'no_hp.min' => 'Nomor WhatsApp sepertinya kurang lengkap.',
        'pesan.required' => 'Pesan testimoni wajib diisi.',
        'pesan.min' => 'Ceritakan sedikit lebih detail (min. 10 karakter).',
    ];

    public function submit(): void
    {
        // Batasi agar tidak bisa di-spam (walau sudah dimoderasi admin).
        $rlKey = 'testimoni-submit:'.request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rlKey, 3)) {
            $this->addError('pesan', 'Terlalu banyak kiriman. Coba lagi nanti.');

            return;
        }

        $this->validate();
        \Illuminate\Support\Facades\RateLimiter::hit($rlKey, 3600);

        // Cocokkan nomor -> pelanggan. Hanya yang punya pesanan SELESAI yang
        // ditautkan; 'paid'/'pending'/'cancelled' belum berhak. Kalau tidak
        // cocok, testimoninya TETAP masuk — cuma tanpa label & tidak jadi member.
        $pelanggan = Customer::cariDariNoHp($this->no_hp);
        $berhak = $pelanggan && $pelanggan->jumlahBelanjaSelesai() > 0;

        Testimoni::create([
            'customer_id' => $berhak ? $pelanggan->id : null,
            'nama' => trim($this->nama),
            'peran' => $this->peran ? trim($this->peran) : null,
            'no_hp' => trim($this->no_hp),
            'pesan' => trim($this->pesan),
            'rating' => $this->rating,
            'status' => 'non-active', // menunggu persetujuan admin
            'source' => 'customer',   // dikirim langsung oleh pelanggan
        ]);

        $this->terverifikasi = $berhak;
        $this->reset(['nama', 'peran', 'no_hp', 'pesan']);
        $this->rating = 5;
        $this->submitted = true;

        // Slider tidak berubah (testimoni baru non-active), tapi pastikan Swiper tetap sehat
        $this->dispatch('tm-reinit');
    }

    public function render()
    {
        // withCount di relasi customer.orders: label "Sudah belanja N×" dihitung
        // dalam 1 query utk semua kartu — bukan 9 query terpisah (N+1).
        $testimonials = Testimoni::where('status', 'active')
            ->with(['customer' => fn ($q) => $q->withCount([
                'orders as belanja_selesai_count' => fn ($o) => $o->where('status', 'completed'),
            ])])
            ->latest()
            ->take(9)
            ->get();

        return view('livewire.components.testimonials', [
            'testimonials' => $testimonials,
        ]);
    }
}
