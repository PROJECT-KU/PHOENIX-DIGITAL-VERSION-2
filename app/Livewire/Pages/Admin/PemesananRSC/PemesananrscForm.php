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
use Maatwebsite\Excel\Facades\Excel;

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

    // Metode harga: 'per_peserta' (harga x jumlah peserta) atau 'per_akun' (harga x jumlah akun).
    public $metode_harga = 'per_peserta';

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

    // Akun tambahan (kredensial saja: username/password/link) — TIDAK memengaruhi harga/hitungan.
    public $akunTambahan = [];

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
            $this->jumlah_pemesanan = $first->jumlah_pemesanan;
            $this->metode_harga = $first->metode_harga ?? 'per_peserta';
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
            // Muat akun tambahan batch (kredensial saja).
            $this->akunTambahan = [];
            $extra = \App\Models\RscBatchAkun::where('nama_camp', $this->nama_camp)
                ->where('batch_camp', $this->batch_camp)
                ->orderBy('id')
                ->get();
            foreach ($extra as $e) {
                $key = 'db-'.$e->id;
                $hargaAkun = $e->akun_id ? $this->toNumber(optional(\App\Models\DataAkun::find($e->akun_id))->harga_satuan) : 0;
                $this->akunTambahan[$key] = [
                    'tmp_id' => $key,
                    'akun_id' => $e->akun_id,
                    'nama_akun' => $e->nama_akun,
                    'username' => $e->username,
                    'password' => $e->password,
                    'link_akses' => $e->link_akses,
                    'harga' => $hargaAkun,
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

    // Download template Excel untuk import peserta.
    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\RscTemplateImportExport,
            'template-import-peserta-rsc.xlsx'
        );
    }

    // import excel file
    public function updatedFileExcel()
    {

        $this->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        // Mode edit: TAMBAHKAN peserta dari file (data lama tidak dihapus).
        if ($this->mode === 'edit') {
            $this->importPesertaAppend();

            return;
        }

        try {
            $data = Excel::toCollection(new class implements \Maatwebsite\Excel\Concerns\ToCollection
            {
                public function collection(\Illuminate\Support\Collection $rows)
                {
                    return $rows;
                }
            }, $this->file_excel)->first();

            if ($data->count() > 1) {
                $this->peserta = [];

                // Ambil nama_camp dan batch_camp dari baris pertama data (row 2 di Excel)
                $firstDataRow = $data->skip(1)->first();

                if (! empty($firstDataRow[0]) && ! empty($firstDataRow[1])) {
                    $this->nama_camp = $firstDataRow[0];
                    $this->batch_camp = $firstDataRow[1];
                } else {
                    session()->flash('error', 'Nama Camp dan Batch Camp tidak ditemukan di baris pertama.');

                    return;
                }

                // Loop semua data untuk ambil peserta (mulai dari row 2)
                foreach ($data->skip(1) as $row) {
                    // Cek kolom nama pembeli (C atau index 2)
                    if (! empty($row[2])) {
                        $tmpId = (string) Str::uuid();
                        $this->peserta[$tmpId] = [
                            'tmp_id' => $tmpId,
                            'nama_pembeli' => $row[2],
                            'telp_pembeli' => $this->formatPhoneNumber($row[3]) ?? '',
                        ];
                    }
                }
                $this->dispatch('success-upload-excel');
            } else {
                session()->flash('error', 'File Excel kosong atau tidak sesuai format.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal import file: '.$e->getMessage());
        }
    }

    /**
     * Tambahkan peserta dari Excel ke daftar yang sudah ada (mode edit).
     * Hanya membaca kolom C (nama) & D (no telp); camp/batch tidak diubah.
     * Peserta baru diberi tmp_id UUID baru agar dibuat sebagai record baru saat simpan.
     */
    private function importPesertaAppend()
    {
        try {
            $data = Excel::toCollection(new class implements \Maatwebsite\Excel\Concerns\ToCollection
            {
                public function collection(\Illuminate\Support\Collection $rows)
                {
                    return $rows;
                }
            }, $this->file_excel)->first();

            if (! $data || $data->count() <= 1) {
                session()->flash('error', 'File Excel kosong atau tidak sesuai format.');

                return;
            }

            $ditambah = 0;
            foreach ($data->skip(1) as $row) {
                // Kolom C (index 2) = nama peserta; wajib ada.
                if (empty($row[2])) {
                    continue;
                }
                $tmpId = (string) Str::uuid();
                $this->peserta[$tmpId] = [
                    'tmp_id' => $tmpId,
                    'nama_pembeli' => $row[2],
                    'telp_pembeli' => ! empty($row[3]) ? $this->formatPhoneNumber($row[3]) : '',
                ];
                $ditambah++;
            }

            // Reset input file agar bisa upload file lain lagi.
            $this->reset('file_excel');

            if ($ditambah > 0) {
                $this->dispatch('success-upload-excel', message: $ditambah.' peserta berhasil ditambahkan dari file. Klik "Update" untuk menyimpan.');
            } else {
                session()->flash('error', 'Tidak ada nama peserta pada kolom C yang bisa ditambahkan.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal import file: '.$e->getMessage());
        }
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

    // ===== Akun tambahan (kredensial saja) =====
    public function addAkunTambahan()
    {
        $id = (string) Str::uuid();
        $this->akunTambahan[$id] = [
            'tmp_id' => $id, 'akun_id' => '', 'nama_akun' => '',
            'username' => '', 'password' => '', 'link_akses' => '', 'harga' => 0,
        ];
    }

    public function removeAkunTambahan($tmpId)
    {
        unset($this->akunTambahan[$tmpId]);
    }

    // Dipanggil dari picker; isi kredensial dari DataAkun (tanpa harga).
    public function setAkunTambahan($tmpId, $akunId)
    {
        if (! isset($this->akunTambahan[$tmpId])) {
            return;
        }

        $akun = \App\Models\DataAkun::find($akunId);
        if (! $akun) {
            return;
        }

        $this->akunTambahan[$tmpId] = array_merge($this->akunTambahan[$tmpId], [
            'akun_id' => $akun->id,
            'nama_akun' => $akun->nama_akun,
            'username' => $akun->username_akun ?: 'Tidak ada',
            'password' => $akun->password_akun ?: 'Tidak ada',
            'link_akses' => $akun->link_login_akun ?: 'Tidak ada',
            'harga' => $this->toNumber($akun->harga_satuan),
        ]);
    }

    // Simpan/ganti akun tambahan untuk batch (kredensial saja).
    private function simpanAkunTambahan()
    {
        \App\Models\RscBatchAkun::where('nama_camp', $this->nama_camp)
            ->where('batch_camp', $this->batch_camp)
            ->delete();

        foreach ($this->akunTambahan as $a) {
            if (empty($a['akun_id'])) {
                continue;
            }
            \App\Models\RscBatchAkun::create([
                'nama_camp' => $this->nama_camp,
                'batch_camp' => $this->batch_camp,
                'akun_id' => $a['akun_id'],
                'nama_akun' => $a['nama_akun'],
                'username' => $a['username'],
                'password' => $a['password'],
                'link_akses' => $a['link_akses'],
            ]);
        }
    }

    #[Computed()]
    public function total_per_peserta()
    {
        $jumlah = (int) $this->jumlah_pemesanan;
        $harga = $this->toNumber($this->harga_satuan);

        return $jumlah * $harga;
    }

    // Harga akun utama (numerik) — dipakai untuk rincian mode 'per_akun'.
    public function hargaUtama()
    {
        return $this->toNumber($this->harga_satuan);
    }

    // Jumlah harga semua akun (utama + tambahan) — dipakai mode 'per_akun'.
    public function sumHargaAkun()
    {
        $sum = $this->toNumber($this->harga_satuan); // akun utama
        foreach ($this->akunTambahan as $a) {
            if (! empty($a['akun_id'])) {
                $sum += (int) ($a['harga'] ?? 0);
            }
        }

        return $sum;
    }

    // Jumlah akun terisi (utama + tambahan).
    public function jumlahAkun()
    {
        $n = $this->akun ? 1 : 0;
        foreach ($this->akunTambahan as $a) {
            if (! empty($a['akun_id'])) {
                $n++;
            }
        }

        return $n;
    }

    #[Computed()]
    public function grand_total()
    {
        // Mode per akun: bulan x jumlah harga semua akun (peserta tidak berpengaruh).
        if ($this->metode_harga === 'per_akun') {
            return (int) $this->jumlah_pemesanan * $this->sumHargaAkun();
        }

        // Mode per peserta (default, TIDAK diubah): harga per peserta x jumlah peserta.
        return $this->total_per_peserta() * count($this->peserta);
    }

    /**
     * Total yang disimpan per baris peserta, agar SUM(total) = grand_total.
     * - per_peserta: tiap baris = total_per_peserta() (persis seperti sekarang).
     * - per_akun: grand_total dibagi rata ke peserta (sisa pembulatan ke baris pertama).
     *
     * @return array<string,int> keyed by tmp_id peserta
     */
    private function rowTotals()
    {
        $keys = array_keys($this->peserta);
        $n = count($keys);

        if ($this->metode_harga === 'per_akun') {
            if ($n === 0) {
                return [];
            }
            $grand = $this->grand_total();
            $base = intdiv($grand, $n);
            $totals = array_fill_keys($keys, $base);
            $totals[$keys[0]] = $base + ($grand - $base * $n); // sisa ke baris pertama

            return $totals;
        }

        // per_peserta: tetap seperti sekarang
        $per = $this->total_per_peserta();

        return array_fill_keys($keys, $per);
    }

    private function formatPhoneNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (substr($number, 0, 1) === '0') {
            return '+62'.substr($number, 1);
        } elseif (substr($number, 0, 2) === '62') {
            return '+'.$number;
        }

        return '+62'.$number;
    }

    private function createpemesananrsc(SyncCashFlowAction $action)
    {
        DB::beginTransaction();
        try {
            $rowTotals = $this->rowTotals();
            foreach ($this->peserta as $tmpId => $p) {

                $formattedTelp = $this->formatPhoneNumber($p['telp_pembeli']);

                $pemesanan = PemesananRsc::create([
                    'id_transaksi' => Str::upper(Str::random(5)),
                    'nama_camp' => $this->nama_camp,
                    'batch_camp' => $this->batch_camp,
                    'tanggal_mulai_camp' => $this->tanggal_mulai_camp,
                    'tanggal_akhir_camp' => $this->tanggal_akhir_camp,
                    'jumlah_pemesanan' => $this->jumlah_pemesanan,
                    'metode_harga' => $this->metode_harga,
                    'tanggal_pemesanan' => $this->tanggal_pemesanan,
                    'tanggal_berakhir' => $this->tanggal_berakhir,
                    'harga_satuan' => $this->toNumber($this->harga_satuan),
                    'total' => $rowTotals[$tmpId] ?? $this->total_per_peserta(),
                    'akun' => $this->akun,
                    'username' => $this->username,
                    'password' => $this->password,
                    'link_akses' => $this->link_akses,
                    'pic' => $this->pic,
                    'deskripsi' => $this->deskripsi,
                    'status' => $this->status,

                    // data peserta
                    'nama_pembeli' => $p['nama_pembeli'],
                    'telp_pembeli' => $formattedTelp,
                ]);

                $action->execute($pemesanan, [
                    'amount' => $pemesanan->total,
                    'type' => 'income',
                    'date' => $pemesanan->tanggal_pemesanan,
                    'category' => 'PemesananRSC',
                    'description' => $pemesanan->deskripsi ?? 'Pemesanan dari Rumah Scopus',
                ]);
            }

            // Akun tambahan (kredensial saja).
            $this->simpanAkunTambahan();

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
            $rowTotals = $this->rowTotals();
            foreach ($this->peserta as $tmpId => $p) {
                $formattedTelp = $this->formatPhoneNumber($p['telp_pembeli']);
                $data = [
                    'nama_camp' => $this->nama_camp,
                    'batch_camp' => $this->batch_camp,
                    'tanggal_mulai_camp' => $this->tanggal_mulai_camp,
                    'tanggal_akhir_camp' => $this->tanggal_akhir_camp,
                    'jumlah_pemesanan' => $this->jumlah_pemesanan,
                    'metode_harga' => $this->metode_harga,
                    'tanggal_pemesanan' => $this->tanggal_pemesanan,
                    'tanggal_berakhir' => $this->tanggal_berakhir,
                    'harga_satuan' => $this->toNumber($this->harga_satuan),
                    'total' => $rowTotals[$tmpId] ?? $this->total_per_peserta(),
                    'akun' => $this->akun,
                    'username' => $this->username,
                    'password' => $this->password,
                    'link_akses' => $this->link_akses,
                    'pic' => $this->pic,
                    'deskripsi' => $this->deskripsi,
                    'status' => $this->status,
                    'nama_pembeli' => $p['nama_pembeli'],
                    'telp_pembeli' => $formattedTelp,
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

            // Akun tambahan (kredensial saja).
            $this->simpanAkunTambahan();

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
