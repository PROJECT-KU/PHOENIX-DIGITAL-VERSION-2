<?php

namespace App\Livewire\Pages\Admin\Surat;

use App\Models\Surat\SuratKlasifikasi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Master klasifikasi (kualifikasi) surat.
 *
 * Satu layar, tanpa halaman tambah/ubah terpisah: daftarnya pendek dan jarang
 * disentuh — biasanya diisi sekali lalu dibiarkan bertahun-tahun. Memecahnya
 * jadi tiga halaman hanya menambah klik untuk pekerjaan yang terjadi dua kali
 * setahun.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Klasifikasi Surat')]
class KlasifikasiList extends Component
{
    /** Id yang sedang disunting; null berarti borangnya untuk baris BARU. */
    public ?int $sunting = null;

    public string $kode = '';

    public string $nama = '';

    public string $keterangan = '';

    public string $retensi = '';

    public bool $aktif = true;

    public string $urutan = '';

    protected function rules(): array
    {
        return [
            'kode' => 'required|string|max:40|unique:surat_klasifikasi,kode'.($this->sunting ? ','.$this->sunting : ''),
            'nama' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
            /*
             | Retensi 0 sampai 100 tahun, dan BOLEH kosong.
             |
             | Kosong berarti "belum ditentukan", bukan nol: nol tahun berarti
             | boleh dimusnahkan hari ini, dan menyamakan keduanya akan
             | menandai seluruh arsip yang belum diputuskan sebagai siap
             | musnah.
             */
            'retensi' => 'nullable|integer|min:0|max:100',
            'urutan' => 'nullable|integer|min:0|max:9999',
        ];
    }

    protected function messages(): array
    {
        return [
            'kode.required' => 'Kode klasifikasi wajib diisi.',
            'kode.unique' => 'Kode ini sudah dipakai klasifikasi lain.',
            'nama.required' => 'Nama klasifikasi wajib diisi.',
            'retensi.integer' => 'Masa simpan harus berupa angka tahun, atau dibiarkan kosong.',
        ];
    }

    protected function validationAttributes(): array
    {
        return ['kode' => 'kode', 'nama' => 'nama', 'retensi' => 'masa simpan', 'urutan' => 'urutan'];
    }

    public function mulaiSunting(int $id): void
    {
        $k = SuratKlasifikasi::findOrFail($id);

        $this->sunting = $k->id;
        $this->kode = $k->kode;
        $this->nama = $k->nama;
        $this->keterangan = (string) $k->keterangan;
        $this->retensi = $k->retensi_tahun === null ? '' : (string) $k->retensi_tahun;
        $this->aktif = (bool) $k->aktif;
        $this->urutan = (string) $k->urutan;
        $this->resetValidation();
    }

    public function batal(): void
    {
        $this->reset(['sunting', 'kode', 'nama', 'keterangan', 'retensi', 'urutan']);
        $this->aktif = true;
        $this->resetValidation();
    }

    public function simpan(): void
    {
        abort_unless(auth()->user()?->hasPermission('kelola_klasifikasi_surat'), 403);

        $this->validate();

        $data = [
            'kode' => trim($this->kode),
            'nama' => trim($this->nama),
            'keterangan' => trim($this->keterangan) ?: null,
            // '' → null, dan '0' → 0. Perbedaan itu dijaga di sini, bukan
            // diserahkan ke pengecoran tipe yang akan menyamakan keduanya.
            'retensi_tahun' => $this->retensi === '' ? null : (int) $this->retensi,
            'aktif' => $this->aktif,
            'urutan' => $this->urutan === '' ? 0 : (int) $this->urutan,
        ];

        if ($this->sunting) {
            SuratKlasifikasi::findOrFail($this->sunting)->update($data);
            $this->dispatch('swal-success', message: 'Klasifikasi '.$data['kode'].' diperbarui.');
        } else {
            SuratKlasifikasi::create($data);
            $this->dispatch('swal-success', message: 'Klasifikasi '.$data['kode'].' ditambahkan.');
        }

        $this->batal();
    }

    public function hapus(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('kelola_klasifikasi_surat'), 403);

        $k = SuratKlasifikasi::findOrFail($id);
        $dipakai = $k->surat()->count();

        /*
         | Klasifikasi yang masih dipakai TIDAK dihapus, hanya dinonaktifkan.
         |
         | Menghapusnya akan melepas klasifikasi dari surat-surat lama
         | (nullOnDelete), dan arsip yang kehilangan kodenya kehilangan pula
         | dasar masa simpannya — tanpa satu pun tanda bahwa itu pernah
         | terjadi. Dinonaktifkan sudah cukup: ia hilang dari kotak pilih
         | surat baru, tetapi surat lama tetap menunjuk kodenya.
         */
        if ($dipakai !== 0) {
            $k->update(['aktif' => false]);

            $this->dispatch('swal-success', message: 'Klasifikasi '.$k->kode.' masih dipakai '.$dipakai
                .' surat, jadi dinonaktifkan saja — bukan dihapus. Surat lama tetap menunjuk kode ini.');

            return;
        }

        $k->delete();
        $this->dispatch('swal-success', message: 'Klasifikasi '.$k->kode.' dihapus.');

        if ($this->sunting === $id) {
            $this->batal();
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.surat.klasifikasi', [
            'daftar' => SuratKlasifikasi::withCount('surat')->tersusun()->get(),
            'bolehKelola' => (bool) auth()->user()?->hasPermission('kelola_klasifikasi_surat'),
        ]);
    }
}
