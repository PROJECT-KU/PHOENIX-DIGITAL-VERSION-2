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

    /** Dicentang pelanggan → di testimoni hanya huruf depan nama yang tampil. */
    public bool $anonim = false;

    /** True saat nomor cocok dgn pelanggan terdaftar (utk feedback + auto-isi nama). */
    public bool $nomorDikenali = false;

    /** Diisi setelah kirim: pembeli terverifikasi & jadi calon member? */
    public bool $terverifikasi = false;

    /**
     * Saat nomor WhatsApp diisi, cari pelanggan terdaftar. Bila cocok, nama
     * diisi otomatis — pelanggan cukup ketik nomor. Tidak menimpa nama yang
     * sudah diketik manual; hanya membersihkan yang tadinya terisi otomatis.
     */
    public function updatedNoHp($value): void
    {
        $pelanggan = Customer::cariDariNoHp($value);

        if ($pelanggan && filled($pelanggan->nama)) {
            if (blank($this->nama) || $this->nomorDikenali) {
                $this->nama = $pelanggan->nama;
            }
            $this->nomorDikenali = true;
        } else {
            if ($this->nomorDikenali) {
                $this->nama = '';
            }
            $this->nomorDikenali = false;
        }
    }

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

        // Anonim: HANYA nama yang disamarkan jadi huruf depan (jadi otomatis
        // begitu pula yang tampil di mana-mana). Peran tetap tampil. Nama asli
        // pembeli terverifikasi tetap bisa ditelusuri admin lewat relasi
        // customer (customer_id).
        $namaTampil = $this->anonim ? $this->samarkanNama($this->nama) : trim($this->nama);

        Testimoni::create([
            'customer_id' => $berhak ? $pelanggan->id : null,
            'nama' => $namaTampil,
            'peran' => $this->peran ? trim($this->peran) : null,
            'no_hp' => trim($this->no_hp),
            'pesan' => trim($this->pesan),
            'rating' => $this->rating,
            'status' => 'pending',  // masuk antrian moderasi admin
            'source' => 'customer', // dikirim langsung oleh pelanggan
        ]);

        $this->terverifikasi = $berhak;
        $this->reset(['nama', 'peran', 'no_hp', 'pesan', 'anonim', 'nomorDikenali']);
        $this->rating = 5;
        $this->submitted = true;

        // Slider tidak berubah (testimoni baru non-active), tapi pastikan Swiper tetap sehat
        $this->dispatch('tm-reinit');
    }

    /** "Berto" → "B•••" : hanya huruf depan yang tampil untuk testimoni anonim. */
    private function samarkanNama(string $nama): string
    {
        $huruf = mb_substr(trim($nama), 0, 1);

        return $huruf === '' ? 'Anonim' : mb_strtoupper($huruf).'•••';
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
