<?php

namespace App\Livewire\Pages\Admin\Ebook;

use App\Models\Ebook;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EbookForm extends Component
{
    use WithFileUploads;

    public ?Ebook $ebook = null;

    public $judul = '';

    public $deskripsi = '';

    public $file;            // file upload baru

    public $existingFile = null;

    public $status = 'active';

    public $mode = 'create';

    /** Batas ukuran PDF (KB). Dulu 2 MB; batas unggah server sudah jauh di atas ini. */
    public const MAKS_KB = 10240;

    /** Produk yang memakai ebook ini sebagai bawaan (tercentang otomatis saat diproses). */
    public array $produkBawaan = [];

    public function mount()
    {
        if ($this->ebook) {
            $this->judul = $this->ebook->judul;
            $this->deskripsi = $this->ebook->deskripsi;
            $this->existingFile = $this->ebook->file;
            $this->status = $this->ebook->status;
            $this->mode = 'edit';
            $this->produkBawaan = $this->ebook->produkBawaan()->pluck('id')->map(fn ($id) => (string) $id)->all();
        }
    }

    public function tambahProdukBawaan(string $id): void
    {
        if (! in_array($id, $this->produkBawaan, true)) {
            $this->produkBawaan[] = $id;
        }
    }

    /**
     * Produk yang paling sering menerima ebook ini (riwayat pesanan) tetapi
     * belum dijadikan bawaan — saran sekali klik di form ubah.
     */
    protected function saranProduk()
    {
        if (! $this->ebook) {
            return collect();
        }

        return DB::table('order_item_ebook')
            ->join('order_items', 'order_items.id', '=', 'order_item_ebook.order_item_id')
            ->where('order_item_ebook.ebook_id', $this->ebook->id)
            ->whereNotIn('order_items.product_id', $this->produkBawaan ?: ['-'])
            ->selectRaw('order_items.product_id, count(*) as n')
            ->groupBy('order_items.product_id')
            ->orderByDesc('n')
            ->limit(4)
            ->get()
            ->map(fn ($r) => ['produk' => Product::find($r->product_id), 'n' => (int) $r->n])
            ->filter(fn ($r) => $r['produk'] && ! $r['produk']->butuh_file)
            ->values();
    }

    public function save()
    {
        $rules = [
            'judul' => 'required|min:3|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:active,non-active',
            // Hanya PDF (untuk proteksi viewer view-only), maks 2MB
            'file' => ($this->mode === 'create' ? 'required' : 'nullable').'|file|mimes:pdf|max:'.self::MAKS_KB,
            'produkBawaan' => 'array',
            'produkBawaan.*' => 'exists:products,id',
        ];

        $this->validate($rules, [
            'file.mimes' => 'File ebook harus berformat PDF.',
            'file.max' => 'Ukuran PDF maksimal 10 MB.',
        ]);

        try {
            $filename = $this->existingFile;

            if ($this->file && is_object($this->file)) {
                // Simpan di disk PRIVAT (storage/app/ebooks) — tidak bisa diakses langsung via URL
                if ($this->existingFile && Storage::disk('local')->exists('ebooks/'.$this->existingFile)) {
                    Storage::disk('local')->delete('ebooks/'.$this->existingFile);
                }
                $filename = 'ebook_'.rand(10000, 99999).'_'.time().'.pdf';
                $this->file->storeAs('ebooks', $filename, 'local');
            }

            if ($this->mode === 'create') {
                $baru = Ebook::create([
                    'judul' => $this->judul,
                    'deskripsi' => $this->deskripsi,
                    'file' => $filename,
                    'status' => $this->status,
                ]);
                $this->simpanProdukBawaan($baru);
                session()->flash('successCreated', 'Data Ebook berhasil ditambahkan!');
            } else {
                $this->ebook->update([
                    'judul' => $this->judul,
                    'deskripsi' => $this->deskripsi,
                    'file' => $filename,
                    'status' => $this->status,
                ]);
                $this->simpanProdukBawaan($this->ebook);
                session()->flash('successUpdated', 'Perubahan Data Ebook berhasil disimpan!');
            }

            return redirect()->route('admin.ebook.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menyimpan ebook: '.$e->getMessage());
        }
    }

    /**
     * Satu produk hanya punya satu ebook bawaan: produk yang dicentang di sini
     * berpindah ke ebook ini; yang dilepas dikosongkan.
     */
    protected function simpanProdukBawaan(Ebook $ebook): void
    {
        Product::where('ebook_bawaan_id', $ebook->id)
            ->whereNotIn('id', $this->produkBawaan ?: ['-'])
            ->update(['ebook_bawaan_id' => null]);

        if ($this->produkBawaan) {
            Product::whereIn('id', $this->produkBawaan)->update(['ebook_bawaan_id' => $ebook->id]);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.ebook.ebook-form', [
            'daftarProduk' => Product::where(fn ($q) => $q->where('butuh_file', false)->orWhereNull('butuh_file'))
                ->with('ebookBawaan:id,judul')->orderBy('nama_akun')->get(['id', 'nama_akun', 'ebook_bawaan_id']),
            'saranProduk' => $this->saranProduk(),
        ]);
    }
}
