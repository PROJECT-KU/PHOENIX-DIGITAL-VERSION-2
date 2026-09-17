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

    /** "nama|batch" batch sumber saat menyalin batch (mode create). */
    public ?string $salinDari = null;

    /**
     * Kunci batch saat halaman edit dibuka. Dipakai untuk menolak ganti
     * nama/nomor ke batch yang sudah ada, dan membersihkan akun tambahan
     * yang tertinggal di kunci lama.
     */
    public ?string $kunciAsal = null;

    /** Pesan untuk layar ini, tampil di atas form (bukan flash session:
     *  flash baru terbaca di halaman berikutnya, padahal di sini tidak ada
     *  perpindahan halaman). */
    public ?string $pesanGalat = null;

    public ?string $pesanSukses = null;

    public function mount()
    {
        $this->users = User::select('id', 'name')->orderBy('name')->get();

        if ($this->pemesananrsc && ! empty($this->pemesananBatch)) {
            $this->muatDariBatch(collect($this->pemesananBatch), false);
            $this->kunciAsal = $this->nama_camp.'|'.$this->batch_camp;
            $this->mode = 'edit';
        } else {
            $this->mode = 'create';
            $sumber = $this->salinDari ? $this->barisBatch($this->salinDari) : collect();

            if ($sumber->isNotEmpty()) {
                $this->muatDariBatch($sumber, true);
            } else {
                $this->salinDari = null;
                $this->tanggal_pemesanan = now()->format('Y-m-d');
                $tmpId = (string) Str::uuid();
                $this->peserta[$tmpId] = [
                    'tmp_id' => $tmpId,
                    'nama_pembeli' => '',
                    'telp_pembeli' => '',
                ];
            }
        }
        $this->hitungTanggalBerakhir();
    }

    private function barisBatch(string $kunci)
    {
        [$nama, $batch] = array_pad(explode('|', $kunci, 2), 2, null);

        return PemesananRsc::where('nama_camp', $nama)->where('batch_camp', $batch)
            ->orderBy('created_at')->orderBy('id')->get();
    }

    /**
     * Isi form dari baris-baris satu batch.
     *
     * $salin = true: dipakai "Salin Batch". Peserta & akun tambahan diberi
     * kunci sementara baru (disimpan sebagai data BARU), jadwal camp
     * dikosongkan, tanggal pesan = hari ini, dan nomor batch diusulkan
     * nomor berikutnya supaya tidak bertabrakan dengan batch sumber.
     */
    private function muatDariBatch($rows, bool $salin): void
    {
        $first = $rows->first();
        $tgl = fn ($d) => $d ? Carbon::parse($d)->format('Y-m-d') : null;

        $this->nama_camp = $first->nama_camp;
        $this->batch_camp = $salin ? $this->nomorBatchBerikutnya($first->nama_camp) : $first->batch_camp;
        $this->tanggal_mulai_camp = $salin ? null : $tgl($first->tanggal_mulai_camp);
        $this->tanggal_akhir_camp = $salin ? null : $tgl($first->tanggal_akhir_camp);
        $this->jumlah_pemesanan = $first->jumlah_pemesanan;
        $this->metode_harga = $first->metode_harga ?? 'per_peserta';
        $this->tanggal_pemesanan = $salin ? now()->format('Y-m-d') : $tgl($first->tanggal_pemesanan);
        $this->tanggal_berakhir = $salin ? null : $tgl($first->tanggal_berakhir);
        $this->harga_satuan = $this->formatRupiah($first->harga_satuan);
        $this->akun = $first->akun;
        $this->username = $first->username;
        $this->password = $first->password;
        $this->link_akses = $first->link_akses;
        $this->pic = $first->pic;
        $this->deskripsi = $first->deskripsi;
        $this->status = $salin ? 'baru' : $first->status;

        // Akun utama disalin dengan data TERKINI dari Data Akun (harga &
        // kredensial bisa sudah berubah sejak batch sumber dibuat).
        if ($salin && ($akun = DataAkun::find($first->akun))) {
            $this->username = $akun->username_akun ?: 'Tidak ada';
            $this->password = $akun->password_akun ?: 'Tidak ada';
            $this->link_akses = $akun->link_login_akun ?: 'Tidak ada';
            $this->harga_satuan = $akun->harga_satuan ?? 0;
        }

        $this->peserta = [];
        foreach ($rows as $p) {
            $key = $salin ? (string) Str::uuid() : $p->id;
            $this->peserta[$key] = [
                'tmp_id' => $key,
                'nama_pembeli' => $p->nama_pembeli,
                'telp_pembeli' => $p->telp_pembeli,
            ];
        }

        $this->akunTambahan = [];
        $extra = \App\Models\RscBatchAkun::where('nama_camp', $first->nama_camp)
            ->where('batch_camp', $first->batch_camp)
            ->orderBy('id')
            ->get();
        foreach ($extra as $e) {
            $key = $salin ? (string) Str::uuid() : 'db-'.$e->id;
            $sumberAkun = $e->akun_id ? DataAkun::find($e->akun_id) : null;
            $this->akunTambahan[$key] = [
                'tmp_id' => $key,
                'akun_id' => $e->akun_id,
                'nama_akun' => $e->nama_akun,
                'username' => $salin && $sumberAkun ? ($sumberAkun->username_akun ?: 'Tidak ada') : $e->username,
                'password' => $salin && $sumberAkun ? ($sumberAkun->password_akun ?: 'Tidak ada') : $e->password,
                'link_akses' => $salin && $sumberAkun ? ($sumberAkun->link_login_akun ?: 'Tidak ada') : $e->link_akses,
                'harga' => $sumberAkun ? $this->toNumber($sumberAkun->harga_satuan) : 0,
            ];
        }
    }

    /** Nomor batch berikutnya untuk satu kategori (angka terbesar + 1). */
    private function nomorBatchBerikutnya(string $namaCamp): string
    {
        $terbesar = PemesananRsc::where('nama_camp', $namaCamp)->pluck('batch_camp')
            ->map(fn ($b) => (int) preg_replace('/\D/', '', (string) $b))
            ->max();

        return (string) (((int) $terbesar) + 1);
    }

    /** Apakah batch nama+nomor ini sudah dipakai (selain batch yang sedang diedit)? */
    private function batchSudahAda(): bool
    {
        $kunci = trim((string) $this->nama_camp).'|'.trim((string) $this->batch_camp);
        if ($this->mode === 'edit' && $kunci === $this->kunciAsal) {
            return false;
        }

        return PemesananRsc::where('nama_camp', trim((string) $this->nama_camp))
            ->where('batch_camp', trim((string) $this->batch_camp))
            ->exists();
    }

    /**
     * Nomor telepon yang sama dipakai lebih dari satu peserta.
     *
     * Hanya PERINGATAN (tidak menahan simpan): satu nomor bisa sah dipakai
     * bersama, mis. pendaftaran lewat satu admin kampus. Baris yang nama
     * DAN nomornya sama persis ditolak saat simpan.
     *
     * @return array<int, array{telp: string, nomor: array<int, int>}>
     */
    public function telpGanda(): array
    {
        $grup = [];
        $no = 0;
        foreach ($this->peserta as $p) {
            $no++;
            $digit = preg_replace('/\D/', '', (string) ($p['telp_pembeli'] ?? ''));
            if (strlen($digit) < 6) {
                continue;
            }
            $normal = $this->formatPhoneNumber($digit);
            $grup[$normal][] = $no;
        }

        return collect($grup)->filter(fn ($n) => count($n) > 1)
            ->map(fn ($n, $t) => ['telp' => $t, 'nomor' => $n])->values()->all();
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
        $this->pesanGalat = null;
        $this->pesanSukses = null;

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
                    $this->pesanGalat = 'Nama Camp dan Batch Camp tidak ditemukan di baris pertama.';

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
                $this->pesanGalat = null;
                $this->pesanSukses = count($this->peserta).' peserta dimuat dari file. Periksa datanya, lalu simpan.';
            } else {
                $this->pesanGalat = 'File Excel kosong atau tidak sesuai format.';
            }
        } catch (\Exception $e) {
            $this->pesanGalat = 'Gagal import file: '.$e->getMessage();
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
                $this->pesanGalat = 'File Excel kosong atau tidak sesuai format.';

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
                $this->pesanGalat = null;
                $this->pesanSukses = $ditambah.' peserta ditambahkan dari file. Klik "Simpan Perubahan" untuk menyimpan.';
            } else {
                $this->pesanGalat = 'Tidak ada nama peserta pada kolom C yang bisa ditambahkan.';
            }
        } catch (\Exception $e) {
            $this->pesanGalat = 'Gagal import file: '.$e->getMessage();
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
        $this->pesanGalat = null;
        $this->pesanSukses = null;

        try {
            $this->validasiSimpan();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Form ini panjang: beri tahu halaman supaya menggulung ke isian
            // salah yang pertama, bukan membiarkan admin mencarinya sendiri.
            $this->dispatch('rsc-form-galat');

            throw $e;
        }

        $this->hitungTanggalBerakhir();

        if ($this->mode === 'create') {
            return $this->createpemesananrsc($syncCashFlow);
        }

        return $this->updatepemesananrsc($syncCashFlow);
    }

    private function validasiSimpan(): void
    {
        $this->nama_camp = trim((string) $this->nama_camp);
        $this->batch_camp = trim((string) $this->batch_camp);

        $galat = [];

        // Satu nama+nomor batch = satu batch. Tanpa penjagaan ini, batch
        // "baru" dengan nama & nomor yang sama diam-diam TERGABUNG ke batch
        // lama — peserta, total, dan Cash Flow keduanya ikut bercampur.
        if ($this->batchSudahAda()) {
            $galat['batch_camp'] = 'Batch '.$this->nama_camp.' #'.$this->batch_camp.' sudah ada. Buka batch itu lewat Edit untuk menambah peserta, atau pakai nomor batch lain.';
        }

        // Baris yang nama DAN nomornya sama persis: hampir pasti dobel
        // (sering terjadi setelah impor Excel dua kali).
        $sudah = [];
        $urutan = 0;
        foreach ($this->peserta as $tmpId => $p) {
            $urutan++;
            $nama = mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $p['nama_pembeli'])));
            if ($nama === '') {
                continue; // baris kosong ditangani aturan "wajib diisi"
            }
            $kunci = $nama.'|'.$this->formatPhoneNumber((string) $p['telp_pembeli']);
            if (isset($sudah[$kunci])) {
                $galat['peserta.'.$tmpId.'.nama_pembeli'] = 'Peserta nomor '.$urutan.' sama persis dengan peserta nomor '.$sudah[$kunci].'.';
            } else {
                $sudah[$kunci] = $urutan;
            }
        }

        // Aturan dasar dan pemeriksaan di atas dilaporkan BERSAMA: admin
        // melihat semua yang perlu dibetulkan sekaligus, bukan satu per satu.
        try {
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
                'peserta.*.telp_pembeli' => ['required', function ($attr, $value, $fail) {
                    // Setelah dirapikan ke +62…, sisa digitnya 8–13 (nomor HP Indonesia).
                    $digit = preg_replace('/\D/', '', (string) $value);
                    $sisa = strlen(preg_replace('/^(0|62)/', '', $digit));
                    if ($sisa < 8 || $sisa > 13) {
                        $fail('Nomor telepon tidak valid (contoh: 0812 3456 7890).');
                    }
                }],
            ], $this->messages());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $galat = array_merge($galat, array_map(fn ($m) => $m[0], $e->errors()));
        }

        if ($galat) {
            throw \Illuminate\Validation\ValidationException::withMessages($galat);
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
            'peserta.*.nama_pembeli.required' => 'Nama peserta harus diisi.',
            'peserta.*.telp_pembeli.required' => 'Nomor telepon peserta harus diisi.',

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

    /**
     * Catat cash flow untuk seluruh batch sebagai SATU entri (total batch), bukan
     * per peserta. Entri dilampirkan ke satu baris representatif (tertua); cash flow
     * baris lain dalam batch dihapus agar tidak terpecah-pecah di laporan cash flow.
     */
    private function syncRscBatchCashFlow(SyncCashFlowAction $action): void
    {
        $rows = PemesananRsc::where('nama_camp', $this->nama_camp)
            ->where('batch_camp', $this->batch_camp)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $representatif = $rows->first();
        $totalBatch = (int) $rows->sum('total');

        // Sisakan hanya cash flow milik baris representatif — pemasukan DAN modal.
        // Modal hanya boleh menempel di representatif; bersihkan sisanya supaya
        // tidak pernah terhitung dobel walau representatif berganti.
        $modalAction = app(\App\Actions\Finance\SyncRscPrivateCostAction::class);
        foreach ($rows as $row) {
            if ($row->id !== $representatif->id) {
                $action->delete($row);
                $modalAction->delete($row);
            }
        }

        // Satu entri untuk seluruh batch. execute() self-guard lewat shouldRecord()
        // (status 'baru'): bila tak layak dicatat, cash flow representatif dihapus.
        $action->execute($representatif, [
            'amount' => $totalBatch,
            'type' => 'income',
            'date' => $representatif->tanggal_pemesanan,
            'category' => 'PemesananRSC',
            'description' => 'Pesanan Rumah Scopus - '.$this->nama_camp.' Batch '.$this->batch_camp,
        ]);

        // Modal akun PRIVATE (bila akunnya private) — baris terpisah, idempoten.
        // Non-private/tak layak → self-delete di dalam action, jadi aman dipanggil selalu.
        app(\App\Actions\Finance\SyncRscPrivateCostAction::class)->execute($representatif);
    }

    private function createpemesananrsc(SyncCashFlowAction $action)
    {
        DB::beginTransaction();
        try {
            $rowTotals = $this->rowTotals();
            foreach ($this->peserta as $tmpId => $p) {

                $formattedTelp = $this->formatPhoneNumber($p['telp_pembeli']);

                PemesananRsc::create([
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
            }

            // Akun tambahan (kredensial saja) — WAJIB disimpan SEBELUM sync cash
            // flow: modal akun private dihitung dari akun tambahan di database.
            // Kalau sync jalan duluan, modalnya memakai daftar akun yang LAMA
            // (akun yang baru dihapus masih terhitung, yang baru ditambah terlewat).
            $this->simpanAkunTambahan();

            // Cash flow dicatat sekali per batch (total batch), bukan per peserta.
            $this->syncRscBatchCashFlow($action);

            DB::commit();

            session()->flash('success', 'Data Pemesanan berhasil ditambahkan!');

            return redirect()->route('admin.pesananrsc.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->pesanGalat = 'Gagal menyimpan batch: '.$e->getMessage();
            $this->dispatch('rsc-form-galat');
        }
    }

    private function updatepemesananrsc(SyncCashFlowAction $action)
    {
        DB::beginTransaction();

        try {
            // Nama/nomor batch diganti: akun tambahan di kunci lama ikut
            // dipindah (simpanAkunTambahan() hanya membersihkan kunci baru).
            $kunciBaru = $this->nama_camp.'|'.$this->batch_camp;
            if ($this->kunciAsal && $this->kunciAsal !== $kunciBaru) {
                [$namaLama, $batchLama] = explode('|', $this->kunciAsal, 2);
                \App\Models\RscBatchAkun::where('nama_camp', $namaLama)->where('batch_camp', $batchLama)->delete();
            }

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
                    // Bersihkan juga baris modal (bila item ini pemegangnya) supaya
                    // tidak jadi cash flow yatim. syncRscBatchCashFlow() setelah ini
                    // akan mencatat ulang modal pada representatif baru.
                    app(\App\Actions\Finance\SyncRscPrivateCostAction::class)->delete($item);
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
                } else {
                    $data['id_transaksi'] = Str::upper(Str::random(5));
                    PemesananRsc::create($data);
                }
            }

            // Akun tambahan (kredensial saja) — WAJIB disimpan SEBELUM sync cash
            // flow: modal akun private dihitung dari akun tambahan di database.
            // Kalau sync jalan duluan, modalnya memakai daftar akun yang LAMA
            // (akun yang baru dihapus masih terhitung, yang baru ditambah terlewat).
            $this->simpanAkunTambahan();

            // Cash flow dicatat sekali per batch (total batch), bukan per peserta.
            $this->syncRscBatchCashFlow($action);

            DB::commit();
            session()->flash('success', 'Berhasil Update data!');

            return redirect()->route('admin.pesananrsc.index');
        } catch (Exception $e) {
            DB::rollBack();
            $this->pesanGalat = 'Gagal menyimpan perubahan: '.$e->getMessage();
            $this->dispatch('rsc-form-galat');
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

        // $akuns (semua) hanya untuk MENAMPILKAN nama akun yang sudah terpilih —
        // termasuk bila akun itu kini non-active (mis. saat mengedit batch lama),
        // supaya pilihannya tidak terlihat hilang.
        $akuns = DataAkun::all();

        // $akunsAktif = daftar yang boleh DIPILIH di picker (utama & tambahan):
        // hanya status active. Akun non-active tak muncul saat memilih.
        $akunsAktif = $akuns->where('status', 'active')->values();

        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-form', [
            'pemesananrsc' => $this->pemesananrsc,
            'users' => $users,
            'akuns' => $akuns,
            'akunsAktif' => $akunsAktif,
        ]);
    }
}
