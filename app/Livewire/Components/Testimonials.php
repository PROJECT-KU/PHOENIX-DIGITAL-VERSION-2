<?php

namespace App\Livewire\Components;

use App\Models\Customer;
use App\Models\Testimoni;
use Livewire\Component;
use Livewire\WithFileUploads;

class Testimonials extends Component
{
    use WithFileUploads;

    public bool $submitted = false;

    public string $nama = '';

    public string $peran = '';

    /** Dipakai HANYA utk mencocokkan pesanan — tidak pernah ditampilkan publik. */
    public string $no_hp = '';

    public int $rating = 5;

    public string $pesan = '';

    /** Dicentang pelanggan → di testimoni hanya huruf depan nama yang tampil. */
    public bool $anonim = false;

    /** Foto pengirim (opsional). Tanpa ini kolom foto hanya bisa diisi admin. */
    public $foto = null;

    /**
     * Perangkap bot (honeypot). Medannya disembunyikan dari manusia, jadi kalau
     * terisi hampir pasti bot — kirimannya dibuang diam-diam supaya bot tidak
     * belajar dari pesan galat. Batas 3 kiriman/jam saja tidak cukup: bot bisa
     * terus mengisi kuota itu sepanjang hari.
     */
    public string $situs = '';

    /** Dipakai halaman /testimoni: cuma formulirnya, tanpa slider beranda. */
    public bool $tampilkanDaftar = true;

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
            'foto' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ];
    }

    protected $messages = [
        'nama.required' => 'Nama wajib diisi.',
        'no_hp.required' => 'Nomor WhatsApp wajib diisi untuk mencocokkan pesananmu.',
        'no_hp.min' => 'Nomor WhatsApp sepertinya kurang lengkap.',
        'pesan.required' => 'Pesan testimoni wajib diisi.',
        'pesan.min' => 'Ceritakan sedikit lebih detail (min. 10 karakter).',
        'foto.image' => 'Berkasnya harus berupa gambar (JPG atau PNG).',
        'foto.max' => 'Ukuran foto maksimal 5 MB.',
    ];

    public function submit(): void
    {
        if (filled($this->situs)) {
            // Berpura-pura berhasil: bot tidak perlu tahu perangkapnya kena.
            $this->reset(['nama', 'peran', 'no_hp', 'pesan', 'anonim', 'nomorDikenali', 'foto', 'situs']);
            $this->submitted = true;

            return;
        }

        // Batasi agar tidak bisa di-spam (walau sudah dimoderasi admin).
        $rlKey = 'testimoni-submit:'.request()->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rlKey, 3)) {
            $this->addError('pesan', 'Terlalu banyak kiriman. Coba lagi nanti.');

            return;
        }

        $this->validate();

        // Batas KEDUA, per nomor: batas per IP saja mudah dilewati dengan
        // ganti jaringan, sekaligus menghukum satu kantor/warnet yang
        // pemakainya berbagi IP.
        $inti = Customer::normalisasiNoHp($this->no_hp);
        $kunciNomor = 'testimoni-nomor:'.$inti;
        if ($inti !== '' && \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($kunciNomor, 3)) {
            $this->addError('pesan', 'Nomor ini sudah beberapa kali mengirim testimoni hari ini. Coba lagi besok ya.');

            return;
        }

        // Kiriman ganda (tombol tertekan dua kali / dikirim ulang): isinya
        // sama persis dari nomor yang sama dalam sehari terakhir.
        if ($inti !== '' && Testimoni::where('pesan', trim($this->pesan))
            ->where('no_hp', 'like', '%'.$inti.'%')
            ->where('created_at', '>=', now()->subDay())
            ->exists()) {
            $this->addError('pesan', 'Testimoni dengan isi yang sama sudah kami terima. Tidak perlu dikirim ulang — testimoninya sedang ditinjau admin.');

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit($rlKey, 3600);
        if ($inti !== '') {
            \Illuminate\Support\Facades\RateLimiter::hit($kunciNomor, 86400);
        }

        // Cocokkan nomor -> pelanggan. Hanya yang punya pesanan SELESAI yang
        // ditautkan; 'paid'/'pending'/'cancelled' belum berhak. Kalau tidak
        // cocok, testimoninya TETAP masuk — cuma tanpa label & tidak jadi member.
        $pelanggan = Customer::cariDariNoHp($this->no_hp);
        $berhak = $pelanggan && $pelanggan->jumlahBelanjaSelesai() > 0;

        // Anonim: nama ASLI tetap disimpan utuh, penyamaran dilakukan saat
        // DITAMPILKAN (Testimoni::getNamaPublikAttribute). Peran tetap tampil.
        //
        // Dulu yang disimpan sudah tersamar ("B•••") sehingga nama asli hilang
        // permanen — admin pun ikut melihat "B•••" saat memoderasi, dan untuk
        // kiriman tamu tidak ada cara memulihkannya.
        // Foto disimpan sebagai WebP 400 px — tampilnya kecil & bulat saja.
        // Disimpan SETELAH validasi supaya kiriman gagal tidak meninggalkan berkas.
        $namaFoto = null;
        if ($this->foto && is_object($this->foto)) {
            $namaFoto = rescue(fn () => \App\Support\GambarWebp::simpan($this->foto, 'img/testimoni', 'Testimoni_'.rand(10000, 99999), 400), null, report: false);
        }

        Testimoni::create([
            'customer_id' => $berhak ? $pelanggan->id : null,
            'nama' => trim($this->nama),
            'anonim' => $this->anonim,
            'peran' => $this->peran ? trim($this->peran) : null,
            'no_hp' => trim($this->no_hp),
            'pesan' => trim($this->pesan),
            'rating' => $this->rating,
            'foto' => $namaFoto,
            'status' => 'pending',  // masuk antrian moderasi admin
            'source' => 'customer', // dikirim langsung oleh pelanggan
        ]);

        $this->terverifikasi = $berhak;
        $this->reset(['nama', 'peran', 'no_hp', 'pesan', 'anonim', 'nomorDikenali', 'foto']);
        $this->rating = 5;
        $this->submitted = true;
        $this->dispatch('testi-terkirim');

        // Slider tidak berubah (testimoni baru non-active), tapi pastikan Swiper tetap sehat
        $this->dispatch('tm-reinit');
    }

    public function render()
    {
        // withCount di relasi customer.orders: label "Sudah belanja N×" dihitung
        // dalam 1 query utk semua kartu — bukan 9 query terpisah (N+1).
        // tampilPublik + urutTampil: admin yang menentukan mana yang naik
        // (sorot/urutan) — dulu selalu 9 terbaru, jadi testimoni bagus yang
        // lama tenggelam dan admin tidak punya cara menaikkannya kembali.
        $testimonials = Testimoni::tampilPublik()
            ->with(['customer' => fn ($q) => $q->withCount([
                'orders as belanja_selesai_count' => fn ($o) => $o->where('status', 'completed'),
            ])])
            ->urutTampil()
            ->take(Testimoni::jumlahBeranda())
            ->get();

        return view('livewire.components.testimonials', [
            'testimonials' => $testimonials,
        ]);
    }
}
