<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use App\Models\Customer;
use App\Models\Testimoni;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TestimoniForm extends Component
{
    use WithFileUploads;

    public ?Testimoni $testimoni = null;

    /** Pelanggan yang ditautkan — sumber label "Pembeli Asli" di homepage. */
    public $customer_id = '';

    public $nama = '';

    public $peran = '';

    /** Terisi otomatis dari data pelanggan; admin tidak perlu mengetik. */
    public $no_hp = '';

    public $pesan = '';

    public $rating = 5;

    public $foto;

    public $existingImage = null; // nama file lama di DB

    public $status = '';

    public $mode = 'create';

    public function mount()
    {
        if ($this->testimoni) {
            $this->customer_id = $this->testimoni->customer_id ?? '';
            $this->nama = $this->testimoni->nama;
            $this->peran = $this->testimoni->peran;
            $this->no_hp = $this->testimoni->no_hp ?? '';
            $this->pesan = $this->testimoni->pesan;
            $this->rating = $this->testimoni->rating;
            $this->existingImage = $this->testimoni->foto;
            $this->status = $this->testimoni->status;
            $this->mode = 'edit';
        }
    }

    /**
     * Pelanggan dipilih -> nama & nomor terisi sendiri.
     *
     * Admin tidak perlu mengetik nomor: salah satu digit saja, tautannya meleset
     * dan label "Pembeli Asli" tidak muncul tanpa penjelasan apa pun.
     */
    public function updatedCustomerId($value): void
    {
        if (! $value) {
            $this->no_hp = '';

            return;
        }

        $pelanggan = Customer::find($value);
        if (! $pelanggan) {
            $this->customer_id = '';
            $this->no_hp = '';

            return;
        }

        $this->no_hp = $pelanggan->no_hp;

        // Nama hanya diisikan bila admin belum mengetik apa pun — jangan menimpa
        // nama panggilan yang sengaja dia tulis (mis. "Pak Berto").
        if (trim((string) $this->nama) === '') {
            $this->nama = $pelanggan->nama;
        }
    }

    /** Lepas tautan pelanggan — testimoni kembali jadi testimoni biasa. */
    public function lepasPelanggan(): void
    {
        $this->customer_id = '';
        $this->no_hp = '';
    }

    /**
     * Testimoni tersimpan dgn status Active & tertaut pelanggan -> pelanggannya
     * jadi member.
     *
     * Dipanggil dari jalur SIMPAN, bukan cuma dari tombol status di daftar:
     * admin yang menginputkan testimoni dari WhatsApp langsung memilih status
     * "Active" di form ini, dan tanpa ini pelanggannya tidak pernah jadi member —
     * diam-diam, tanpa pesan apa pun.
     *
     * aktifkanMember() memulangkan false bila sudah member, jadi aman dipanggil
     * berulang kali (mis. tiap kali testimoni diedit).
     */
    private function aktifkanMemberBilaPerlu(): void
    {
        if ($this->status !== 'active' || ! $this->customer_id) {
            return;
        }

        $pelanggan = Customer::find($this->customer_id);
        if ($pelanggan && $pelanggan->aktifkanMember()) {
            session()->flash('successMember', $pelanggan->nama.' otomatis jadi Member 🎉');
        }
    }

    public function save()
    {
        $rules = [
            'customer_id' => 'nullable|exists:customers,id',
            'nama' => 'required|min:3',
            'peran' => 'nullable|string|max:100',
            'pesan' => 'required|string|min:5',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:active,non-active',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
        ];

        $this->validate($rules);

        if ($this->mode === 'create') {
            $this->createTestimoni();
        } else {
            $this->updateTestimoni();
        }
    }

    private function createTestimoni()
    {
        try {
            $filename = null;
            if ($this->foto && is_object($this->foto)) {
                $random = rand(10000, 99999);
                $filename = 'Testimoni_' . $random . '.' . $this->foto->getClientOriginalExtension();
                $this->foto->storeAs('img/testimoni', $filename, 'public');
            }

            Testimoni::create([
                'customer_id' => $this->customer_id ?: null,
                'nama' => $this->nama,
                'peran' => $this->peran,
                'no_hp' => $this->no_hp ?: null,
                'pesan' => $this->pesan,
                'rating' => $this->rating,
                'foto' => $filename,
                'status' => $this->status,
            ]);

            $this->aktifkanMemberBilaPerlu();

            session()->flash('successCreated', 'Data Testimoni berhasil ditambahkan!');
            $this->dispatch('testimoni-created');
            $this->resetForm();

            return redirect()->route('admin.testimoni.index');
        } catch (\Exception $e) {
            session()->flash('errorCreated', 'Gagal menambahkan Data Testimoni: ' . $e->getMessage());
        }
    }

    private function updateTestimoni()
    {
        try {
            $data = [
                'customer_id' => $this->customer_id ?: null,
                'nama' => $this->nama,
                'peran' => $this->peran,
                'no_hp' => $this->no_hp ?: null,
                'pesan' => $this->pesan,
                'rating' => $this->rating,
                'status' => $this->status,
            ];

            if ($this->foto && is_object($this->foto)) {
                if ($this->existingImage && Storage::disk('public')->exists('img/testimoni/' . $this->existingImage)) {
                    Storage::disk('public')->delete('img/testimoni/' . $this->existingImage);
                }

                $random = rand(10000, 99999);
                $filename = 'Testimoni_' . $random . '.' . $this->foto->getClientOriginalExtension();
                $this->foto->storeAs('img/testimoni', $filename, 'public');
                $data['foto'] = $filename;
            } else {
                $data['foto'] = $this->existingImage;
            }

            $this->testimoni->update($data);

            $this->aktifkanMemberBilaPerlu();

            session()->flash('successUpdated', 'Perubahan Data Testimoni berhasil disimpan!');
            $this->dispatch('testimoni-updated');
            $this->resetForm();

            return redirect()->route('admin.testimoni.index');
        } catch (\Exception $e) {
            session()->flash('errorUpdated', 'Gagal mengupdate Data Testimoni: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->customer_id = '';
        $this->nama = '';
        $this->peran = '';
        $this->no_hp = '';
        $this->pesan = '';
        $this->rating = 5;
        $this->foto = '';
        $this->status = '';
    }

    public function render()
    {
        // Hanya pelanggan yang PUNYA pesanan selesai — merekalah yang berhak
        // label "Pembeli Asli". Menawarkan yang lain cuma menjebak admin:
        // tertaut tapi labelnya tidak pernah muncul.
        $pelanggan = Customer::withCount([
            'orders as belanja_selesai_count' => fn ($q) => $q->where('status', 'completed'),
        ])
            ->having('belanja_selesai_count', '>', 0)
            ->orderBy('nama')
            ->get();

        // Pelanggan terpilih + hitungan belanjanya, utk panel info di form.
        $terpilih = $this->customer_id
            ? $pelanggan->firstWhere('id', $this->customer_id)
            : null;

        return view('livewire.pages.admin.testimoni.testimoni-form', [
            'daftarPelanggan' => $pelanggan,
            'pelangganTerpilih' => $terpilih,
        ]);
    }
}
