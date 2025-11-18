<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Models\PemesananRsc;
use App\Models\User;
use App\Models\DataAkun;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class PemesananrscForm extends Component
{
    use WithPagination;

    public ?PemesananRsc $pemesananrsc = null;
    public $id_transaksi;
    public $nama_camp;
    public $batch_camp;
    public $tanggal_mulai_camp;
    public $tanggal_akhir_camp;
    public $nama_pembeli;
    public $telp_pembeli;
    public $jumlah_pemesanan;
    public $tanggal_pemesanan;
    public $tanggal_berakhir;
    public $harga_satuan;
    public $total;
    public $akun = null;
    public $username = null;
    public $password = null;
    public $link_akses = null;
    public $pic;
    public $deskripsi;
    public $status = 'baru';

    public $users;
    public $mode = 'create';

    public function mount()
    {
        $this->users = User::select('id', 'name')->orderBy('name')->get();

        if ($this->pemesananrsc) {
            $this->pemesananrsc         = $this->pemesananrsc;
            $this->id_transaksi         = $this->pemesananrsc->id_transaksi;
            $this->nama_camp            = $this->pemesananrsc->nama_camp;
            $this->batch_camp           = $this->pemesananrsc->batch_camp;
            $this->tanggal_mulai_camp = $this->pemesananrsc->tanggal_mulai_camp
                ? Carbon::parse($this->pemesananrsc->tanggal_mulai_camp)->format('Y-m-d')
                : null;

            $this->tanggal_akhir_camp = $this->pemesananrsc->tanggal_akhir_camp
                ? Carbon::parse($this->pemesananrsc->tanggal_akhir_camp)->format('Y-m-d')
                : null;
            $this->nama_pembeli         = $this->pemesananrsc->getAttribute('nama_pembeli');
            $this->telp_pembeli         = $this->pemesananrsc->telp_pembeli;
            $this->jumlah_pemesanan     = $this->pemesananrsc->jumlah_pemesanan;
            $this->tanggal_pemesanan = $this->pemesananrsc->tanggal_pemesanan
                ? Carbon::parse($this->pemesananrsc->tanggal_pemesanan)->format('Y-m-d')
                : null;

            $this->tanggal_berakhir = $this->pemesananrsc->tanggal_berakhir
                ? Carbon::parse($this->pemesananrsc->tanggal_berakhir)->format('Y-m-d')
                : null;
            $this->harga_satuan         = $this->formatRupiah($this->pemesananrsc->harga_satuan);
            $this->total                = $this->formatRupiah($this->pemesananrsc->total);
            $this->akun                 = $this->pemesananrsc->akun;
            $this->username             = $this->pemesananrsc->username;
            $this->password             = $this->pemesananrsc->password;
            $this->link_akses           = $this->pemesananrsc->link_akses;
            $this->pic                  = $this->pemesananrsc->pic;
            $this->deskripsi            = $this->pemesananrsc->deskripsi;
            $this->status               = $this->pemesananrsc->status;
            $this->mode = 'edit';
        } else {
            $this->mode = 'create';
            $this->tanggal_pemesanan = now()->format('Y-m-d');
        }
        $this->hitungTanggalBerakhir();
    }

    public function hitungTanggalBerakhir()
    {
        if ($this->jumlah_pemesanan && $this->tanggal_pemesanan) {
            try {
                $tanggal = Carbon::parse($this->tanggal_pemesanan);
                $this->tanggal_berakhir = $tanggal
                    ->addMonths((int) $this->jumlah_pemesanan)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                $this->tanggal_berakhir = null;
            }
        } else {
            $this->tanggal_berakhir = null;
        }
    }

    #[Computed]
    public function total()
    {
        $jumlah = (int) $this->jumlah_pemesanan;
        $harga = $this->toNumber($this->harga_satuan);

        return $jumlah * $harga;
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
        $this->validate([
            'nama_camp'            => 'required',
            'batch_camp'           => 'required|numeric',
            'tanggal_mulai_camp'   => 'required|date',
            'tanggal_akhir_camp'   => 'required|date|after_or_equal:tanggal_mulai_camp',
            'nama_pembeli'         => 'required',
            'telp_pembeli'         => 'required',
            'tanggal_pemesanan'    => 'required|date',
            'jumlah_pemesanan'     => 'required|numeric|min:0',
            'akun'                 => 'required',
            'pic'                  => 'required',
            'status'               => 'required|in:habis,pengganti,perpanjang,baru',
        ], $this->messages());

        $this->hitungTanggalBerakhir();

        if ($this->mode === 'create') {
            $this->createpemesananrsc();
        } else {
            $this->updatepemesananrsc();
        }
    }

    protected function messages()
    {
        return [
            'nama_camp.required'            => 'Nama camp harus diisi.',
            'batch_camp.required'           => 'Batch camp harus diisi.',
            'batch_camp.numeric'            => 'Batch camp harus berupa angka.',

            'tanggal_mulai_camp.required'   => 'Tanggal mulai camp harus diisi.',
            'tanggal_mulai_camp.date'       => 'Tanggal mulai camp harus berupa tanggal yang valid.',

            'tanggal_akhir_camp.required'   => 'Tanggal akhir camp harus diisi.',
            'tanggal_akhir_camp.date'       => 'Tanggal akhir camp harus berupa tanggal yang valid.',
            'tanggal_akhir_camp.after_or_equal' => 'Tanggal akhir camp tidak boleh lebih awal dari tanggal mulai.',

            'nama_pembeli.required'         => 'Nama pembeli harus diisi.',
            'telp_pembeli.required'         => 'Nomor telepon pembeli harus diisi.',

            'tanggal_pemesanan.required'   => 'Tanggal akhir camp harus diisi.',
            'tanggal_pemesanan.date'       => 'Tanggal akhir camp harus berupa tanggal yang valid.',

            'jumlah_pemesanan.required'     => 'Jumlah pemesanan harus diisi.',
            'jumlah_pemesanan.numeric'      => 'Jumlah pemesanan harus berupa angka.',
            'jumlah_pemesanan.min'          => 'Jumlah pemesanan minimal 1.',

            'harga_satuan.required'         => 'Harga satuan harus diisi.',
            'harga_satuan.numeric'          => 'Harga satuan harus berupa angka.',
            'harga_satuan.min'              => 'Harga satuan tidak boleh kurang dari 0.',

            'akun.required'                 => 'Akun harus dipilih.',
            'pic.required'                  => 'PIC harus diisi.',

            'status.required'               => 'Status harus dipilih.',
            'status.in'                     => 'Status hanya boleh: habis, pengganti, perpanjang, atau baru.',
        ];
    }

    private function createpemesananrsc()
    {
        try {
            PemesananRsc::create([
                'id_transaksi'          => Str::upper(Str::random(5)),
                'nama_camp'             => $this->nama_camp,
                'batch_camp'            => $this->batch_camp,
                'tanggal_mulai_camp'    => $this->tanggal_mulai_camp,
                'tanggal_akhir_camp'    => $this->tanggal_akhir_camp,
                'nama_pembeli'          => $this->nama_pembeli,
                'telp_pembeli'          => $this->telp_pembeli,
                'jumlah_pemesanan'      => $this->jumlah_pemesanan,
                'tanggal_pemesanan'     => $this->tanggal_pemesanan,
                'tanggal_berakhir'      => $this->tanggal_berakhir,
                'harga_satuan'          => $this->toNumber($this->harga_satuan),
                'total'                 => $this->total(),
                'akun'                  => $this->akun,
                'username'              => $this->username,
                'password'              => $this->password,
                'link_akses'            => $this->link_akses,
                'pic'                   => $this->pic,
                'deskripsi'             => $this->deskripsi,
                'status'                => $this->status,
            ]);


            session()->flash('success', 'Data Pemesanan berhasil ditambahkan!');
            return redirect()->route('admin.pesananrsc.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan Pemesanan: ' . $e->getMessage());
        }
    }

    private function updatepemesananrsc()
    {
        try {
            $this->pemesananrsc->update([
                'nama_camp'             => $this->nama_camp,
                'batch_camp'            => $this->batch_camp,
                'tanggal_mulai_camp'    => $this->tanggal_mulai_camp,
                'tanggal_akhir_camp'    => $this->tanggal_akhir_camp,
                'nama_pembeli'          => $this->nama_pembeli,
                'telp_pembeli'          => $this->telp_pembeli,
                'jumlah_pemesanan'      => $this->jumlah_pemesanan,
                'tanggal_pemesanan'     => $this->tanggal_pemesanan,
                'tanggal_berakhir'      => $this->tanggal_berakhir,
                'harga_satuan'          => $this->toNumber($this->harga_satuan),
                'total'                 => $this->total(),
                'akun'                  => $this->akun,
                'username'              => $this->username,
                'password'              => $this->password,
                'link_akses'            => $this->link_akses,
                'pic'                   => $this->pic,
                'deskripsi'             => $this->deskripsi,
                'status'                => $this->status,
            ]);

            session()->flash('success', 'Perubahan Data Pemesanan berhasil disimpan!');
            return redirect()->route('admin.pesananrsc.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate Data Pemesanan: ' . $e->getMessage());
        }
    }

    public function updated($propertyName, $value)
    {
        if ($propertyName === 'akun') {
            $akun = \App\Models\DataAkun::find($value);

            if ($akun) {
                $this->username   = $akun->username_akun ?: 'Tidak ada';
                $this->password   = $akun->password_akun ?: 'Tidak ada';
                $this->link_akses = $akun->link_login_akun ?: 'Tidak ada';
                $this->harga_satuan = $akun->harga_satuan ?? 0;
            } else {
                $this->username   = '';
                $this->password   = '';
                $this->link_akses = '';
                $this->harga_satuan = '';
            }
        }
        if (in_array($propertyName, ['jumlah_pemesanan', 'tanggal_pemesanan'])) {
            $this->hitungTanggalBerakhir();
        }
    }

    public function render()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        $akuns = DataAkun::all();

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-form', [
            'pemesananrsc' => $this->pemesananrsc,
            'users' => $users,
            'akuns' => $akuns,
        ]);
    }
}
