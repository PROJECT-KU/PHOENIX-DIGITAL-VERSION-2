<?php

namespace App\Livewire\Pages\Admin\GajiKaryawans;

use App\Models\GajiKaryawans;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;

class GajiKaryawansForm extends Component
{
    public ?GajiKaryawans $gajikaryawan = null;
    public $id_transaksi;
    public $nama_karyawan;
    public $tanggal_transaksi;
    public $gaji_pokok;
    public $bonus;
    public $tunjangan;
    public $bonus_lainnya;
    public $potongan;
    public $total;
    public $deskripsi;
    public $status = 'pending';

    public $users;
    public $mode = 'create';

    public function mount()
    {
        $this->users = User::select('id', 'name')->orderBy('name')->get();

        if ($this->gajikaryawan) {
            $this->gajikaryawan = $this->gajikaryawan;
            $this->id_transaksi = $this->gajikaryawan->id_transaksi;
            $this->nama_karyawan = $this->gajikaryawan->getAttribute('nama_karyawan');
            $this->tanggal_transaksi = $this->gajikaryawan->tanggal_transaksi?->format('Y-m-d');
            $this->gaji_pokok        = $this->formatRupiah($this->gajikaryawan->gaji_pokok);
            $this->bonus             = $this->formatRupiah($this->gajikaryawan->bonus);
            $this->tunjangan         = $this->formatRupiah($this->gajikaryawan->tunjangan);
            $this->bonus_lainnya     = $this->formatRupiah($this->gajikaryawan->bonus_lainnya);
            $this->potongan          = $this->formatRupiah($this->gajikaryawan->potongan);
            $this->total = $this->gajikaryawan->total;
            $this->deskripsi = $this->gajikaryawan->deskripsi;
            $this->status = $this->gajikaryawan->status;
            $this->mode = 'edit';
        } else {
            $this->mode = 'create';
            $this->tanggal_transaksi = now()->format('Y-m-d');
        }

        $this->calculateTotal();
    }

    public function updated($propertyName)
    {
        // setiap kali field berubah, hitung ulang total
        if (in_array($propertyName, ['gaji_pokok', 'bonus', 'tunjangan', 'bonus_lainnya', 'potongan'])) {
            $this->calculateTotal();
        }
    }

    private function calculateTotal()
    {
        $this->total =
            (int) $this->toNumber($this->gaji_pokok) +
            (int) $this->toNumber($this->bonus) +
            (int) $this->toNumber($this->tunjangan) +
            (int) $this->toNumber($this->bonus_lainnya) -
            (int) $this->toNumber($this->potongan);
    }

    private function formatRupiah($angka)
    {
        if ($angka === null) return null;
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }

    private function toNumber($value)
    {
        if (!$value) return 0;
        // hilangkan format Rp / titik sebelum disimpan sebagai angka
        return (int) preg_replace('/[^0-9]/', '', $value);
    }

    public function save()
    {
        $this->calculateTotal();

        $this->validate([
            'nama_karyawan' => 'required',
            'tanggal_transaksi' => 'required|date',
            'gaji_pokok' => 'required',
            'status' => 'required|in:pending,completed',
        ]);

        if ($this->mode === 'create') {
            $this->creategajikaryawan();
        } else {
            $this->updategajikaryawan();
        }
    }

    protected function messages()
    {
        return [
            'nama_karyawan.required'     => 'Nama karyawan harus diisi.',
            'tanggal_transaksi.required' => 'Tanggal transaksi harus diisi.',
            'tanggal_transaksi.date'     => 'Tanggal transaksi harus berupa format tanggal yang valid.',
            'gaji_pokok.required'        => 'Gaji pokok harus diisi.',
            'status.required'            => 'Status harus dipilih.',
            'status.in'                  => 'Status hanya boleh berisi pending atau completed.',
        ];
    }

    private function creategajikaryawan()
    {
        try {
            GajiKaryawans::create([
                'id_transaksi'      => Str::upper(Str::random(5)),
                'nama_karyawan'     => $this->nama_karyawan,
                'tanggal_transaksi' => $this->tanggal_transaksi,
                'gaji_pokok'        => $this->toNumber($this->gaji_pokok),
                'bonus'             => $this->toNumber($this->bonus),
                'tunjangan'         => $this->toNumber($this->tunjangan),
                'bonus_lainnya'     => $this->toNumber($this->bonus_lainnya),
                'potongan'          => $this->toNumber($this->potongan),
                'total'             => $this->total,
                'deskripsi'         => $this->deskripsi,
                'status'            => $this->status,
            ]);

            session()->flash('success', 'Data Gaji Karyawan berhasil ditambahkan!');
            return redirect()->route('admin.gajikaryawan.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan Data Akun: ' . $e->getMessage());
        }
    }

    private function updategajikaryawan()
    {
        try {
            $this->gajikaryawan->update([
                'id_transaksi'      => $this->id_transaksi,
                'nama_karyawan'     => $this->nama_karyawan,
                'tanggal_transaksi' => $this->tanggal_transaksi,
                'gaji_pokok'        => $this->toNumber($this->gaji_pokok),
                'bonus'             => $this->toNumber($this->bonus),
                'tunjangan'         => $this->toNumber($this->tunjangan),
                'bonus_lainnya'     => $this->toNumber($this->bonus_lainnya),
                'potongan'          => $this->toNumber($this->potongan),
                'total'             => $this->total,
                'deskripsi'         => $this->deskripsi,
                'status'            => $this->status,
            ]);

            session()->flash('success', 'Perubahan Data Gaji Karyawan berhasil disimpan!');
            return redirect()->route('admin.gajikaryawan.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate Data Akun: ' . $e->getMessage());
        }
    }

    public function render()
    {

        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('livewire.pages.admin.gaji-karyawans.gaji-karyawans-form', [
            'gajikaryawan' => $this->gajikaryawan,
            'users'    => $users,

        ]);
    }
}
