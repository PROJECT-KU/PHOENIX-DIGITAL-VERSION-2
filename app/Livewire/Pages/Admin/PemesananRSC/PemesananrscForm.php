<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\DataAkun;
use App\Models\PemesananRsc;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PemesananrscForm extends Component
{
    use WithFileUploads, WithPagination;

    public ?PemesananRsc $pemesananrsc = null;

    public $file_excel;

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

    public $peserta = [];

    public $pemesananBatch = [];

    public function mount()
    {
        $this->users = User::select('id', 'name')->orderBy('name')->get();

        if ($this->pemesananrsc && ! empty($this->pemesananBatch)) {
            $first = $this->pemesananrsc;

            $this->nama_camp = $first->nama_camp;
            $this->batch_camp = $first->batch_camp;
            $this->tanggal_mulai_camp = $first->tanggal_mulai_camp
                ? Carbon::parse($first->tanggal_mulai_camp)->format('Y-m-d')
                : null;
            $this->tanggal_akhir_camp = $first->tanggal_akhir_camp
                ? Carbon::parse($first->tanggal_akhir_camp)->format('Y-m-d')
                : null;
            $this->jumlah_pemesanan = count($this->pemesananBatch);
            $this->tanggal_pemesanan = $first->tanggal_pemesanan
                ? Carbon::parse($first->tanggal_pemesanan)->format('Y-m-d')
                : null;
            $this->tanggal_berakhir = $first->tanggal_berakhir
                ? Carbon::parse($first->tanggal_berakhir)->format('Y-m-d')
                : null;
            $this->harga_satuan = $this->formatRupiah($first->harga_satuan);
            $this->akun = $first->akun;
            $this->username = $first->username;
            $this->password = $first->password;
            $this->link_akses = $first->link_akses;
            $this->pic = $first->pic;
            $this->deskripsi = $first->deskripsi;
            $this->status = $first->status;

            // Load semua peserta ke array
            $this->peserta = [];
            foreach ($this->pemesananBatch as $p) {
                $this->peserta[$p->id] = [
                    'tmp_id' => $p->id, // Gunakan ID asli sebagai tmp_id
                    'nama_pembeli' => $p->nama_pembeli,
                    'telp_pembeli' => $p->telp_pembeli,
                ];
            }
            $this->mode = 'edit';
        } else {
            $this->mode = 'create';
            $this->tanggal_pemesanan = now()->format('Y-m-d');
            $tmpId = (string) Str::uuid();
            $this->peserta[$tmpId] = [
                'tmp_id' => $tmpId,
                'nama_pembeli' => '',
                'telp_pembeli' => '',
            ];
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
        if ($angka === null) {
            return null;
        }

        return 'Rp '.number_format($angka, 0, ',', '.');
    }

    private function toNumber($value)
    {
        if (! $value) {
            return 0;
        }

        return (int) preg_replace('/[^0-9]/', '', $value);
    }

    public function save(SyncCashFlowAction $syncCashFlow)
    {
        $this->validate([
            'nama_camp' => 'required',
            'batch_camp' => 'required|numeric',
            'tanggal_mulai_camp' => 'required|date',
            'tanggal_akhir_camp' => 'required|date|after_or_equal:tanggal_mulai_camp',
            'tanggal_pemesanan' => 'required|date',
            'jumlah_pemesanan' => 'required|numeric|min:0',
            'akun' => 'required',
            'pic' => 'required',
            'status' => 'required|in:habis,pengganti,perpanjang,baru',
            'peserta.*.nama_pembeli' => 'required',
            'peserta.*.telp_pembeli' => 'required',
        ], $this->messages());

        $this->hitungTanggalBerakhir();

        if ($this->mode === 'create') {
            $this->createpemesananrsc($syncCashFlow);
        } else {
            $this->updatepemesananrsc($syncCashFlow);
        }
    }

    protected function messages()
    {
        return [
            'nama_camp.required' => 'Nama camp harus diisi.',
            'batch_camp.required' => 'Batch camp harus diisi.',
            'batch_camp.numeric' => 'Batch camp harus berupa angka.',

            'tanggal_mulai_camp.required' => 'Tanggal mulai camp harus diisi.',
            'tanggal_mulai_camp.date' => 'Tanggal mulai camp harus berupa tanggal yang valid.',

            'tanggal_akhir_camp.required' => 'Tanggal akhir camp harus diisi.',
            'tanggal_akhir_camp.date' => 'Tanggal akhir camp harus berupa tanggal yang valid.',
            'tanggal_akhir_camp.after_or_equal' => 'Tanggal akhir camp tidak boleh lebih awal dari tanggal mulai.',

            'nama_pembeli.required' => 'Nama pembeli harus diisi.',
            'telp_pembeli.required' => 'Nomor telepon pembeli harus diisi.',

            'tanggal_pemesanan.required' => 'Tanggal akhir camp harus diisi.',
            'tanggal_pemesanan.date' => 'Tanggal akhir camp harus berupa tanggal yang valid.',

            'jumlah_pemesanan.required' => 'Jumlah pemesanan harus diisi.',
            'jumlah_pemesanan.numeric' => 'Jumlah pemesanan harus berupa angka.',
            'jumlah_pemesanan.min' => 'Jumlah pemesanan minimal 1.',

            'harga_satuan.required' => 'Harga satuan harus diisi.',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka.',
            'harga_satuan.min' => 'Harga satuan tidak boleh kurang dari 0.',

            'akun.required' => 'Akun harus dipilih.',
            'pic.required' => 'PIC harus diisi.',

            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status hanya boleh: habis, pengganti, perpanjang, atau baru.',
        ];
    }

    public function addPeserta()
    {
        $tmpId = (string) Str::uuid();
        $this->peserta[$tmpId] = ['tmp_id' => $tmpId, 'nama_pembeli' => '', 'telp_pembeli' => ''];
    }

    public function removePeserta($tmpId)
    {
        unset($this->peserta[$tmpId]);
    }

    #[Computed()]
    public function total_per_peserta()
    {
        $jumlah = (int) $this->jumlah_pemesanan;
        $harga = $this->toNumber($this->harga_satuan);

        return $jumlah * $harga;
    }

    #[Computed()]
    public function grand_total()
    {
        return $this->total_per_peserta() * count($this->peserta);
    }

    private function createpemesananrsc(SyncCashFlowAction $action)
    {
        DB::beginTransaction();
        try {
            foreach ($this->peserta as $p) {

                $pemesanan = PemesananRsc::create([
                    'id_transaksi' => Str::upper(Str::random(5)),
                    'nama_camp' => $this->nama_camp,
                    'batch_camp' => $this->batch_camp,
                    'tanggal_mulai_camp' => $this->tanggal_mulai_camp,
                    'tanggal_akhir_camp' => $this->tanggal_akhir_camp,
                    'jumlah_pemesanan' => $this->jumlah_pemesanan,
                    'tanggal_pemesanan' => $this->tanggal_pemesanan,
                    'tanggal_berakhir' => $this->tanggal_berakhir,
                    'harga_satuan' => $this->toNumber($this->harga_satuan),
                    'total' => $this->total_per_peserta(),
                    'akun' => $this->akun,
                    'username' => $this->username,
                    'password' => $this->password,
                    'link_akses' => $this->link_akses,
                    'pic' => $this->pic,
                    'deskripsi' => $this->deskripsi,
                    'status' => $this->status,

                    // data peserta
                    'nama_pembeli' => $p['nama_pembeli'],
                    'telp_pembeli' => $p['telp_pembeli'],
                ]);

                $action->execute($pemesanan, [
                    'amount' => $pemesanan->total,
                    'type' => 'income',
                    'date' => $pemesanan->tanggal_pemesanan,
                    'category' => 'PemesananRSC',
                    'description' => $pemesanan->deskripsi ?? 'Pemesanan dari Rumah Scopus',
                ]);
            }
            DB::commit();

            session()->flash('success', 'Data Pemesanan berhasil ditambahkan!');

            return redirect()->route('admin.pesananrsc.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan Pemesanan: '.$e->getMessage());
        }
    }

    private function updatepemesananrsc(SyncCashFlowAction $action)
    {
        DB::beginTransaction();

        try {
            $existingIds = collect($this->peserta)
                ->filter(fn ($p) => ! Str::isUuid($p['tmp_id']) || PemesananRsc::where('id', $p['tmp_id'])->exists())
                ->pluck('tmp_id')
                ->toArray();

            PemesananRsc::where('nama_camp', $this->nama_camp)
                ->where('batch_camp', $this->batch_camp)
                ->whereNotIn('id', $existingIds)
                ->get()
                ->each(function ($item) use ($action) {
                    $action->delete($item);
                    $item->delete();
                });

            // update atau create peserta
            foreach ($this->peserta as $tmpId => $p) {
                $data = [
                    'nama_camp' => $this->nama_camp,
                    'batch_camp' => $this->batch_camp,
                    'tanggal_mulai_camp' => $this->tanggal_mulai_camp,
                    'tanggal_akhir_camp' => $this->tanggal_akhir_camp,
                    'jumlah_pemesanan' => count($this->peserta),
                    'tanggal_pemesanan' => $this->tanggal_pemesanan,
                    'tanggal_berakhir' => $this->tanggal_berakhir,
                    'harga_satuan' => $this->toNumber($this->harga_satuan),
                    'total' => $this->total_per_peserta(),
                    'akun' => $this->akun,
                    'username' => $this->username,
                    'password' => $this->password,
                    'link_akses' => $this->link_akses,
                    'pic' => $this->pic,
                    'deskripsi' => $this->deskripsi,
                    'status' => $this->status,
                    'nama_pembeli' => $p['nama_pembeli'],
                    'telp_pembeli' => $p['telp_pembeli'],
                ];

                if (! Str::isUuid($tmpId) || PemesananRsc::where('id', $tmpId)->exists()) {
                    $pemesanan = PemesananRsc::find($tmpId);
                    if ($pemesanan) {
                        $pemesanan->update($data);
                    }

                    $action->execute($pemesanan, [
                        'amount' => $pemesanan->total,
                        'type' => 'income',
                        'date' => $pemesanan->tanggal_pemesanan,
                        'category' => 'PemesananRSC',
                        'description' => $pemesanan->deskripsi ?? 'Pemesanan dari Rumah Scopus',

                    ]);
                } else {
                    $data['id_transaksi'] = Str::upper(Str::random(5));
                    $pemesanan = PemesananRsc::create($data);

                    $action->execute($pemesanan, [
                        'amount' => $pemesanan->total,
                        'type' => 'income',
                        'date' => $pemesanan->tanggal_pemesanan,
                        'category' => 'PemesananRSC',
                        'description' => $pemesanan->deskripsi ?? 'Pemesanan dari Rumah Scopus',
                    ]);
                }
            }

            DB::commit();
            session()->flash('success', 'Berhasil Update data!');

            return redirect()->route('admin.pesananrsc.index');
        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update data: '.$e->getMessage());
        }
    }

    public function updated($propertyName, $value)
    {
        if ($propertyName === 'akun') {
            $akun = \App\Models\DataAkun::find($value);

            if ($akun) {
                $this->username = $akun->username_akun ?: 'Tidak ada';
                $this->password = $akun->password_akun ?: 'Tidak ada';
                $this->link_akses = $akun->link_login_akun ?: 'Tidak ada';
                $this->harga_satuan = $akun->harga_satuan ?? 0;
            } else {
                $this->username = '';
                $this->password = '';
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
