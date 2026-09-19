<?php

namespace App\Livewire\Pages\Admin\Banners;

use App\Models\Banners;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BannersForm extends Component
{
    use WithFileUploads;

    public ?Banners $banners = null;

    public $judul = '';

    public $gambar;

    public $existingImage = null; // nama file lama di DB

    public $deskripsi = '';

    public $status = '';

    /**
     * Jadwal tayang (opsional). Kosong = tanpa batas waktu, sehingga banner
     * tanpa jadwal berperilaku persis seperti sebelum fitur ini ada.
     */
    public $mulai_tayang = '';

    public $selesai_tayang = '';

    public $mode = 'create';

    /**
     * Tujuan klik banner di beranda. '' = halaman Belanja (bawaan lama),
     * member | bundling | produk | lain. Disimpan sebagai path relatif supaya
     * tidak bergantung pada domain.
     */
    public string $tautanJenis = '';

    public string $tautanProduk = '';

    public string $tautanLain = '';

    public const TAUTAN_TETAP = [
        'member' => 'member.info',
        'bundling' => 'bundling.index',
    ];

    public function mount()
    {
        if ($this->banners) {
            $this->judul = $this->banners->judul;
            $this->existingImage = $this->banners->gambar;
            $this->deskripsi = $this->banners->deskripsi;
            $this->status = $this->banners->status;
            // format datetime-local: Y-m-d\TH:i
            $this->mulai_tayang = $this->banners->mulai_tayang?->format('Y-m-d\TH:i') ?? '';
            $this->selesai_tayang = $this->banners->selesai_tayang?->format('Y-m-d\TH:i') ?? '';
            $this->mode = 'edit';
            $this->bacaTautan((string) $this->banners->tautan);
        } else {
            // Banner baru biasanya langsung ingin tayang.
            $this->status = 'active';
        }
    }

    /** Pecah tautan tersimpan kembali ke pilihan form. */
    protected function bacaTautan(string $tautan): void
    {
        if ($tautan === '') {
            return;
        }
        foreach (self::TAUTAN_TETAP as $jenis => $rute) {
            if ($tautan === route($rute, absolute: false)) {
                $this->tautanJenis = $jenis;

                return;
            }
        }
        $awalan = rtrim(route('shop.detail-product', 'X', absolute: false), 'X');
        if (str_starts_with($tautan, $awalan)) {
            $this->tautanJenis = 'produk';
            $this->tautanProduk = substr($tautan, strlen($awalan));

            return;
        }
        $this->tautanJenis = 'lain';
        $this->tautanLain = $tautan;
    }

    /** Tautan yang akan disimpan (null = halaman Belanja). */
    protected function tautanSimpan(): ?string
    {
        return match ($this->tautanJenis) {
            'member', 'bundling' => route(self::TAUTAN_TETAP[$this->tautanJenis], absolute: false),
            'produk' => $this->tautanProduk ? route('shop.detail-product', $this->tautanProduk, absolute: false) : null,
            'lain' => trim($this->tautanLain) ?: null,
            default => null,
        };
    }

    /** Isian cepat jadwal tayang. */
    public function aturJadwal(string $pilihan): void
    {
        $f = fn ($t) => $t->format('Y-m-d\TH:i');
        $mulai = $this->mulai_tayang ? \Illuminate\Support\Carbon::parse($this->mulai_tayang) : now();

        match ($pilihan) {
            'sekarang' => $this->mulai_tayang = $f(now()),
            '7hari' => [$this->mulai_tayang = $f($mulai), $this->selesai_tayang = $f($mulai->copy()->addDays(7)->setTime(23, 59))],
            '30hari' => [$this->mulai_tayang = $f($mulai), $this->selesai_tayang = $f($mulai->copy()->addDays(30)->setTime(23, 59))],
            'akhirbulan' => [$this->mulai_tayang = $f($mulai), $this->selesai_tayang = $f($mulai->copy()->endOfMonth()->setTime(23, 59))],
            'kosong' => [$this->mulai_tayang = '', $this->selesai_tayang = ''],
            default => null,
        };
        $this->resetErrorBag(['mulai_tayang', 'selesai_tayang']);
    }

    public function save()
    {
        // Rute sudah dijaga izin, tetapi komponen ini juga bisa dipanggil dari
        // halaman lain — izin diperiksa di sini juga.
        abort_unless(auth()->user()?->hasPermission($this->mode === 'create' ? 'create_banners' : 'edit_banners'), 403);

        $rules = [
            'judul' => 'required|min:3',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:active,non-active',
            'mulai_tayang' => 'nullable|date',
            // after_or_equal hanya diperiksa bila mulai_tayang diisi; kalau
            // kosong, selesai_tayang berdiri sendiri sbg "tayang sampai".
            'selesai_tayang' => 'nullable|date'.($this->mulai_tayang ? '|after_or_equal:mulai_tayang' : ''),
            'tautanJenis' => 'in:,member,bundling,produk,lain',
            'tautanProduk' => $this->tautanJenis === 'produk' ? 'required|exists:products,id' : 'nullable',
            // Hanya path di situs ini atau https — bukan javascript: dan sejenisnya.
            'tautanLain' => $this->tautanJenis === 'lain' ? ['required', 'max:500', 'regex:#^(/[^/]|/$|https://)#'] : 'nullable',
        ];

        if ($this->mode === 'create') {
            $rules['gambar'] = 'required|image|mimes:png,jpg,jpeg|max:5120';
        } else {
            $rules['gambar'] = 'nullable|image|mimes:png,jpg,jpeg|max:5120';
        }

        $this->validate($rules, [
            'tautanProduk.required' => 'Pilih produknya.',
            'tautanLain.required' => 'Isi tautan tujuannya.',
            'tautanLain.regex' => 'Tautan harus diawali "/" (halaman di situs ini) atau "https://".',
        ]);

        if ($this->mode === 'create') {
            $this->createBanners();
        } else {
            $this->updateBanners();
        }
    }

    private function createBanners()
    {
        try {
            // generate nama unik dengan angka random
            $random = rand(10000, 99999);
            // Disimpan sebagai WebP (lihat App\Support\GambarWebp). Banner adalah
            // gambar TERBERAT di situs — tiga berkas PNG sempat memakan 5,7 MB
            // atau 78% bobot beranda.
            $filename = \App\Support\GambarWebp::simpan($this->gambar, 'img/banners', 'Banners_'.$random);

            // simpan hanya nama file ke DB
            Banners::create([
                'judul' => $this->judul,
                'gambar' => $filename, // cuma nama file
                'deskripsi' => $this->deskripsi,
                'status' => $this->status,
                'tautan' => $this->tautanSimpan(),
                // Banner baru masuk di slide terakhir; urutan diatur dari daftar.
                'urutan' => (int) Banners::max('urutan') + 1,
                'mulai_tayang' => $this->mulai_tayang ?: null,
                'selesai_tayang' => $this->selesai_tayang ?: null,
            ]);

            session()->flash('successCreated', 'Data Banner berhasil ditambahkan!');
            $this->dispatch('Banners-created');
            $this->resetForm();

            return redirect()->route('admin.Banners.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menambahkan Data Banners: '.$e->getMessage());
        }
    }

    private function updateBanners()
    {
        try {
            $data = [
                'judul' => $this->judul,
                'deskripsi' => $this->deskripsi,
                'status' => $this->status,
                'tautan' => $this->tautanSimpan(),
                'mulai_tayang' => $this->mulai_tayang ?: null,
                'selesai_tayang' => $this->selesai_tayang ?: null,
            ];

            if ($this->gambar && is_object($this->gambar)) {
                // hapus file lama kalau ada
                if ($this->existingImage && Storage::disk('public')->exists('img/banners/'.$this->existingImage)) {
                    Storage::disk('public')->delete('img/banners/'.$this->existingImage);
                }

                // upload baru → replace
                $random = rand(10000, 99999);
                $filename = \App\Support\GambarWebp::simpan($this->gambar, 'img/banners', 'Banners_'.$random);
                $data['gambar'] = $filename;
            } else {
                $data['gambar'] = $this->existingImage; // pakai gambar lama
            }

            $this->banners->update($data);

            session()->flash('successUpdated', 'Perubahan Data Banners berhasil disimpan!');
            $this->dispatch('Banners-updated');
            $this->resetForm();

            return redirect()->route('admin.Banners.index');
        } catch (\Exception $e) {
            session()->flash('errorUpdated', 'Gagal mengupdate Data Banners: '.$e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->judul = '';
        $this->gambar = '';
        $this->deskripsi = '';
        $this->status = '';
        $this->mulai_tayang = '';
        $this->selesai_tayang = '';
    }

    public function render()
    {
        return view('livewire.pages.admin.Banners.Banners-form', [
            'daftarProduk' => $this->tautanJenis === 'produk'
                ? \App\Models\Product::orderBy('nama_akun')->get(['id', 'nama_akun'])
                : collect(),
        ]);
    }
}
