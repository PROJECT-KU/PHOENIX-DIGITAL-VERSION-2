<?php

namespace App\Livewire\Pages\Public\ShopPage;

use App\Models\Order;
use App\Models\OrderUpload;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Halaman hub JASA (link permanen ber-token) — satu link untuk: unggah dokumen,
 * lihat progress pengecekan, dan unduh hasil. Tanpa login (audiens orang tua),
 * akses cukup lewat share_token pesanan yang acak & tak bisa ditebak.
 */
class JasaCekPage extends Component
{
    use WithFileUploads;

    public Order $order;

    /** Dokumen yang akan diunggah customer. */
    public $dokumen;

    // Setelan exclude Turnitin (default aman).
    public bool $exclude_bibliografi = true;

    public bool $exclude_kutipan = true;

    public bool $exclude_sumber_kecil = false;

    // Ambang "exclude source": angka + satuan (persen / kata), dipilih customer.
    public $ambang_nilai = '';

    public string $ambang_satuan = 'persen';

    public string $catatan = '';

    public function mount(string $token)
    {
        $order = Order::where('share_token', $token)
            ->with(['items.product', 'uploads'])
            ->firstOrFail();

        // Hanya pesanan JASA yang benar-benar sudah dibayar. Pesanan
        // pending/dibatalkan tak boleh membuka halaman ini walau token benar.
        abort_unless($order->butuhUpload(), 404);
        abort_unless(in_array($order->status, ['paid', 'processing', 'completed']), 404);

        $this->order = $order;
    }

    /** Muat ulang status (dipanggil polling agar progress "hidup"). */
    public function refreshStatus(): void
    {
        $this->order->load('uploads');
    }

    /**
     * Saat "Exclude Source" dicentang, langsung isi pilihan yang disarankan.
     * Orang awam jadi tak perlu memutuskan apa pun — tinggal biarkan.
     */
    public function updatedExcludeSumberKecil($value): void
    {
        if ($value && $this->ambang_nilai === '') {
            $this->ambang_nilai = 5;
            $this->ambang_satuan = 'persen';
        }
    }

    /**
     * Validasi SEKETIKA saat file dipilih. Tanpa ini, file bertipe apa pun
     * terlanjur terunggah ke temp dan tampak "diterima" (nama file muncul),
     * padahal baru ditolak saat tombol Kirim ditekan.
     */
    public function updatedDokumen()
    {
        try {
            $this->validateOnly('dokumen', [
                'dokumen' => ['required', 'file', 'mimes:pdf,docx', 'max:20480'],
            ], [
                'dokumen.mimes' => 'Format file harus PDF atau DOCX.',
                'dokumen.max' => 'Ukuran file maksimal 20 MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Buang file yang tak sah + bersihkan nama file di tampilan.
            $this->reset('dokumen');
            $this->dispatch('cek-file-ditolak');

            throw $e; // biarkan Livewire menampilkan pesan errornya
        }
    }

    public function uploadDokumen()
    {
        // Hitung ulang sisa TEPAT sebelum simpan (cegah dobel-upload lintas tab).
        $this->order->load('uploads');

        if (! $this->order->bisaUploadPengecekan()) {
            $this->dispatch('cek-error', message: 'Kuota pengecekan sudah habis atau pesanan tidak aktif.');

            return;
        }

        $rules = [
            'dokumen' => ['required', 'file', 'mimes:pdf,docx', 'max:20480'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];

        // Ambang hanya wajib bila "exclude source" dipilih. Batas atas ikut satuan:
        // persen maksimal 100, kata boleh lebih besar.
        if ($this->exclude_sumber_kecil) {
            $rules['ambang_satuan'] = ['required', 'in:persen,kata'];
            $rules['ambang_nilai'] = ['required', 'integer', 'min:1', $this->ambang_satuan === 'persen' ? 'max:100' : 'max:9999'];
        }

        $this->validate($rules, [
            'dokumen.required' => 'Pilih file dokumen dulu.',
            'dokumen.mimes' => 'Format file harus PDF atau DOCX.',
            'dokumen.max' => 'Ukuran file maksimal 20 MB.',
            'ambang_nilai.required' => 'Isi dulu angka ambangnya.',
            'ambang_nilai.integer' => 'Ambang harus berupa angka.',
            'ambang_nilai.min' => 'Ambang minimal 1.',
            'ambang_nilai.max' => $this->ambang_satuan === 'persen' ? 'Ambang persen maksimal 100.' : 'Ambang kata maksimal 9999.',
        ]);

        $file = $this->dokumen;
        // Disk PRIVAT — file tak boleh diakses lewat URL publik (privasi + audit).
        $path = $file->store('order-uploads/'.$this->order->id.'/masuk', 'local');

        OrderUpload::create([
            'order_id' => $this->order->id,
            'path' => $path,
            'nama_asli' => $file->getClientOriginalName(),
            'ukuran' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'status' => 'menunggu',
            'exclude_bibliografi' => $this->exclude_bibliografi,
            'exclude_kutipan' => $this->exclude_kutipan,
            'exclude_sumber_kecil' => $this->exclude_sumber_kecil,
            // Simpan sebagai teks siap tampil, mis. "5%" atau "10 kata".
            'ambang_sumber_kecil' => $this->exclude_sumber_kecil
                ? (int) $this->ambang_nilai.($this->ambang_satuan === 'persen' ? '%' : ' kata')
                : null,
            'catatan' => $this->catatan ?: null,
        ]);

        $this->reset('dokumen', 'catatan');
        $this->exclude_bibliografi = true;
        $this->exclude_kutipan = true;
        $this->exclude_sumber_kecil = false;
        $this->ambang_nilai = '';
        $this->ambang_satuan = 'persen';

        $this->order->load('uploads');
        $this->dispatch('cek-success', message: 'Dokumen berhasil diunggah. Silakan tunggu, hasil akan muncul di halaman ini.');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        // Selalu ambil data terbaru saat render (termasuk saat polling).
        $this->order->load('uploads');

        return view('livewire.pages.public.shop-page.jasa-cek-page', [
            'order' => $this->order,
            'kuota' => $this->order->kuotaPengecekan(),
            'terpakai' => $this->order->terpakaiPengecekan(),
            'sisa' => $this->order->sisaKuota(),
            'pengecekan' => $this->order->uploads->sortByDesc('created_at')->values(),
        ]);
    }
}
