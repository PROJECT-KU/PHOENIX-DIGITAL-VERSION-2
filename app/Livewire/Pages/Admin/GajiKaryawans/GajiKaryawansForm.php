<?php

namespace App\Livewire\Pages\Admin\GajiKaryawans;

use App\Actions\Finance\SyncCashFlowAction;
use App\Models\GajiKaryawans;
use App\Models\Loan;
use App\Models\Pengembalian;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class GajiKaryawansForm extends Component
{
    public ?GajiKaryawans $gajikaryawan = null;

    public $id_transaksi;

    public $nama_karyawan;

    public $bank;

    public $no_rek;

    public $tanggal_transaksi;

    public $periode_bulan;

    public $periode_tahun;

    // Pendapatan
    public $gaji_pokok;

    public $bonus_kinerja;

    public $bonus_lainnya;

    public $jam_lembur;

    public $tarif_lembur;

    public $uang_lembur;

    public $tunjangan_kesehatan;

    public $tunjangan_thr;

    public $tunjangan_ketenagakerjaan;

    public $tunjangan_lainnya;

    public $tunjangan_transport;

    public $tunjangan_makan;

    // Potongan
    public $potongan;

    public $potongan_bpjs_kesehatan;

    public $potongan_bpjs_ketenagakerjaan;

    public $potongan_pinjaman;

    public $pph21;

    public $total;

    public $deskripsi;

    public $status = 'pending';

    public $users;

    // Info bantu: sisa pinjaman karyawan terpilih
    public $sisaPinjaman = 0;

    public $mode = 'create';

    public function mount()
    {
        $this->users = User::select('id', 'name')->orderBy('name')->get();

        if ($this->gajikaryawan) {
            $this->id_transaksi = $this->gajikaryawan->id_transaksi;
            $this->nama_karyawan = $this->gajikaryawan->nama_karyawan;

            $karyawanTerkait = User::with('detail')->find($this->nama_karyawan);
            if ($karyawanTerkait && $karyawanTerkait->detail) {
                $this->bank = $karyawanTerkait->detail->nama_bank;
                $this->no_rek = $karyawanTerkait->detail->nomor_rekening;
            }

            $this->tanggal_transaksi = $this->gajikaryawan->tanggal_transaksi?->format('Y-m-d');
            $this->periode_bulan = $this->gajikaryawan->periode_bulan;
            $this->periode_tahun = $this->gajikaryawan->periode_tahun;

            $this->gaji_pokok = $this->formatRupiah($this->gajikaryawan->gaji_pokok);
            $this->bonus_kinerja = $this->formatRupiah($this->gajikaryawan->bonus_kinerja);
            $this->bonus_lainnya = $this->formatRupiah($this->gajikaryawan->bonus_lainnya);
            $this->jam_lembur = $this->gajikaryawan->jam_lembur ?: null;
            // Tarif/jam diturunkan dari data historis bila ada, agar nilai lama tetap akurat
            $this->tarif_lembur = ($this->gajikaryawan->jam_lembur > 0)
                ? $this->formatAngka((int) round($this->gajikaryawan->uang_lembur / $this->gajikaryawan->jam_lembur))
                : $this->formatAngka(Setting::get('tarif_lembur_per_jam', 15000));
            $this->uang_lembur = $this->formatAngka($this->gajikaryawan->uang_lembur);
            $this->tunjangan_kesehatan = $this->formatRupiah($this->gajikaryawan->tunjangan_kesehatan);
            $this->tunjangan_thr = $this->formatRupiah($this->gajikaryawan->tunjangan_thr);
            $this->tunjangan_ketenagakerjaan = $this->formatRupiah($this->gajikaryawan->tunjangan_ketenagakerjaan);
            $this->tunjangan_lainnya = $this->formatRupiah($this->gajikaryawan->tunjangan_lainnya);
            $this->tunjangan_transport = $this->formatRupiah($this->gajikaryawan->tunjangan_transport);
            $this->tunjangan_makan = $this->formatRupiah($this->gajikaryawan->tunjangan_makan);
            $this->potongan = $this->formatRupiah($this->gajikaryawan->potongan);
            $this->potongan_bpjs_kesehatan = $this->formatRupiah($this->gajikaryawan->potongan_bpjs_kesehatan);
            $this->potongan_bpjs_ketenagakerjaan = $this->formatRupiah($this->gajikaryawan->potongan_bpjs_ketenagakerjaan);
            $this->potongan_pinjaman = $this->formatRupiah($this->gajikaryawan->potongan_pinjaman);
            $this->pph21 = $this->formatRupiah($this->gajikaryawan->pph21);
            $this->total = $this->gajikaryawan->total;
            $this->deskripsi = $this->gajikaryawan->deskripsi;
            $this->status = $this->gajikaryawan->status;
            $this->mode = 'edit';

            $this->hitungSisaPinjaman();
        } else {
            $this->mode = 'create';
            $this->tanggal_transaksi = now()->format('Y-m-d');
            $this->periode_bulan = now()->month;
            $this->periode_tahun = now()->year;
            $this->jam_lembur = null;
            $this->tarif_lembur = $this->formatAngka(Setting::get('tarif_lembur_per_jam', 15000));
            $this->uang_lembur = $this->formatAngka(0);
        }

        $this->calculateTotal();
    }

    public function updatedNamaKaryawan($userId)
    {
        if (! $userId) {
            $this->resetBankData();
            $this->sisaPinjaman = 0;

            return;
        }

        $user = User::with('detail')->find($userId);

        if ($user && $user->detail) {
            $this->bank = $user->detail->nama_bank ?? '-';
            $this->no_rek = $user->detail->nomor_rekening ?? '-';
        } else {
            $this->resetBankData();
        }

        $this->hitungSisaPinjaman();
    }

    private function resetBankData()
    {
        $this->bank = '';
        $this->no_rek = '';
    }

    /**
     * Hitung sisa pinjaman karyawan terpilih (total pinjaman - total pengembalian).
     */
    private function hitungSisaPinjaman(): void
    {
        $nama = User::find($this->nama_karyawan)?->name;

        if (! $nama) {
            $this->sisaPinjaman = 0;

            return;
        }

        $totalPinjaman = (float) Loan::where('nama_peminjam', $nama)->sum('nominal');
        $totalPengembalian = (float) Pengembalian::where('nama_pengembalian', $nama)->sum('nominal');

        $this->sisaPinjaman = max($totalPinjaman - $totalPengembalian, 0);
    }

    /**
     * Maksimal potongan pinjaman yang boleh dipotong dari gaji ini =
     * total pinjaman - total pengembalian LAIN (di luar pengembalian milik gaji ini).
     * Saat edit, pengembalian milik gaji ini dikecualikan agar nominalnya bisa diubah.
     */
    private function maksPotonganPinjaman(): float
    {
        $nama = User::find($this->nama_karyawan)?->name;

        if (! $nama) {
            return 0;
        }

        $totalPinjaman = (float) Loan::where('nama_peminjam', $nama)->sum('nominal');

        $pengembalianLain = Pengembalian::where('nama_pengembalian', $nama);
        if ($this->mode === 'edit' && $this->gajikaryawan) {
            $pengembalianLain->where('source_gaji_id', '!=', $this->gajikaryawan->id);
        }

        return max($totalPinjaman - (float) $pengembalianLain->sum('nominal'), 0);
    }

    public function updated($propertyName)
    {
        // Jam lembur / tarif berubah -> hitung ulang uang lembur
        if (in_array($propertyName, ['jam_lembur', 'tarif_lembur'])) {
            $this->hitungUangLembur();
        }

        if (in_array($propertyName, [
            'gaji_pokok', 'bonus_kinerja', 'bonus_lainnya', 'uang_lembur',
            'jam_lembur', 'tarif_lembur',
            'tunjangan_kesehatan', 'tunjangan_thr', 'tunjangan_ketenagakerjaan',
            'tunjangan_lainnya', 'tunjangan_transport', 'tunjangan_makan',
            'potongan', 'potongan_bpjs_kesehatan', 'potongan_bpjs_ketenagakerjaan',
            'potongan_pinjaman', 'pph21',
        ])) {
            $this->calculateTotal();
        }
    }

    /**
     * Uang lembur = jumlah jam lembur x tarif per jam.
     */
    private function hitungUangLembur(): void
    {
        $jam = (int) $this->toNumber($this->jam_lembur);
        $tarif = (int) $this->toNumber($this->tarif_lembur);

        $this->uang_lembur = $this->formatAngka($jam * $tarif);
    }

    private function calculateTotal()
    {
        $pendapatan =
            $this->toNumber($this->gaji_pokok) +
            $this->toNumber($this->bonus_kinerja) +
            $this->toNumber($this->bonus_lainnya) +
            $this->toNumber($this->uang_lembur) +
            $this->toNumber($this->tunjangan_kesehatan) +
            $this->toNumber($this->tunjangan_thr) +
            $this->toNumber($this->tunjangan_ketenagakerjaan) +
            $this->toNumber($this->tunjangan_lainnya) +
            $this->toNumber($this->tunjangan_transport) +
            $this->toNumber($this->tunjangan_makan);

        $potongan =
            $this->toNumber($this->potongan) +
            $this->toNumber($this->potongan_bpjs_kesehatan) +
            $this->toNumber($this->potongan_bpjs_ketenagakerjaan) +
            $this->toNumber($this->potongan_pinjaman) +
            $this->toNumber($this->pph21);

        $this->total = $pendapatan - $potongan;
    }

    private function totalPotongan(): int
    {
        return $this->toNumber($this->potongan)
            + $this->toNumber($this->potongan_bpjs_kesehatan)
            + $this->toNumber($this->potongan_bpjs_ketenagakerjaan)
            + $this->toNumber($this->potongan_pinjaman)
            + $this->toNumber($this->pph21);
    }

    // Format angka dengan pemisah ribuan TANPA prefix "Rp" (Rp ditaruh terpisah di UI).
    // Dipakai seragam oleh seluruh input nominal di form gaji.
    private function formatRupiah($angka)
    {
        return $this->formatAngka($angka);
    }

    private function formatAngka($angka): string
    {
        if ($angka === null || $angka === '') {
            return '';
        }

        return number_format((int) $angka, 0, ',', '.');
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
        // Pastikan uang lembur & total dihitung otomatis (jam x tarif)
        $this->hitungUangLembur();
        $this->calculateTotal();

        // Simpan tarif lembur sebagai default global yang baru (dinamis).
        // Hanya saat create agar membuka/menyimpan data lama tidak mengubah default.
        $tarif = (int) $this->toNumber($this->tarif_lembur);
        if ($this->mode === 'create' && $tarif > 0) {
            Setting::set('tarif_lembur_per_jam', $tarif);
        }

        $this->validate([
            'nama_karyawan' => 'required',
            'tanggal_transaksi' => 'required|date',
            'periode_bulan' => 'required',
            'periode_tahun' => 'required',
            'gaji_pokok' => 'required',
            'status' => 'required|in:pending,completed',
        ]);

        // Pengaman 1: potongan pinjaman tidak boleh melebihi sisa pinjaman karyawan.
        // Bila karyawan tidak punya pinjaman aktif, potongan pinjaman wajib 0.
        $potonganPinjaman = $this->toNumber($this->potongan_pinjaman);
        if ($potonganPinjaman > 0) {
            $maks = $this->maksPotonganPinjaman();

            if ($potonganPinjaman > $maks) {
                $this->addError('potongan_pinjaman', $maks <= 0
                    ? 'Karyawan ini tidak memiliki pinjaman aktif, jadi Potongan Pinjaman harus kosong/0.'
                    : 'Potongan Pinjaman melebihi sisa pinjaman karyawan (maksimal Rp '.number_format($maks, 0, ',', '.').').');

                return;
            }
        }

        // Pengaman 2: total gaji bersih tidak boleh minus.
        // (Mis. gaji 1jt tapi potongan pinjaman 5jt -> tidak mungkin.)
        if ($this->total < 0) {
            $pendapatan = $this->total + $this->totalPotongan();
            $this->addError('potongan_pinjaman',
                'Total potongan melebihi pendapatan — gaji bersih tidak boleh minus. '.
                'Maksimal potongan dari gaji ini Rp '.number_format($pendapatan, 0, ',', '.').'.');

            return;
        }

        if ($this->mode === 'create') {
            $this->creategajikaryawan($syncCashFlow);
        } else {
            $this->updategajikaryawan($syncCashFlow);
        }
    }

    protected function messages()
    {
        return [
            'nama_karyawan.required' => 'Nama karyawan harus diisi.',
            'tanggal_transaksi.required' => 'Tanggal transaksi harus diisi.',
            'tanggal_transaksi.date' => 'Tanggal transaksi harus berupa format tanggal yang valid.',
            'periode_bulan.required' => 'Periode bulan harus dipilih.',
            'periode_tahun.required' => 'Periode tahun harus dipilih.',
            'gaji_pokok.required' => 'Gaji pokok harus diisi.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status hanya boleh berisi pending atau completed.',
        ];
    }

    /**
     * Data kolom yang dipakai bersama oleh create & update.
     */
    private function payload(): array
    {
        return [
            'nama_karyawan' => $this->nama_karyawan,
            'bank' => $this->bank,
            'no_rek' => $this->no_rek,
            'tanggal_transaksi' => $this->tanggal_transaksi,
            'periode_bulan' => $this->periode_bulan,
            'periode_tahun' => $this->periode_tahun,
            'gaji_pokok' => $this->toNumber($this->gaji_pokok),
            'bonus_kinerja' => $this->toNumber($this->bonus_kinerja),
            'bonus_lainnya' => $this->toNumber($this->bonus_lainnya),
            'uang_lembur' => $this->toNumber($this->uang_lembur),
            'jam_lembur' => (int) $this->toNumber($this->jam_lembur),
            'tunjangan_kesehatan' => $this->toNumber($this->tunjangan_kesehatan),
            'tunjangan_thr' => $this->toNumber($this->tunjangan_thr),
            'tunjangan_ketenagakerjaan' => $this->toNumber($this->tunjangan_ketenagakerjaan),
            'tunjangan_lainnya' => $this->toNumber($this->tunjangan_lainnya),
            'tunjangan_transport' => $this->toNumber($this->tunjangan_transport),
            'tunjangan_makan' => $this->toNumber($this->tunjangan_makan),
            'potongan' => $this->toNumber($this->potongan),
            'potongan_bpjs_kesehatan' => $this->toNumber($this->potongan_bpjs_kesehatan),
            'potongan_bpjs_ketenagakerjaan' => $this->toNumber($this->potongan_bpjs_ketenagakerjaan),
            'potongan_pinjaman' => $this->toNumber($this->potongan_pinjaman),
            'pph21' => $this->toNumber($this->pph21),
            'total' => $this->total,
            'deskripsi' => $this->deskripsi ?? '',
            'status' => $this->status,
        ];
    }

    private function creategajikaryawan(SyncCashFlowAction $action)
    {
        try {
            DB::transaction(function () use ($action) {
                $gaji = GajiKaryawans::create(array_merge(
                    ['id_transaksi' => Str::upper(Str::random(5))],
                    $this->payload()
                ));

                // Beban gaji = take-home + potongan pinjaman, sehingga pengembalian
                // pinjaman (income) tidak menyebabkan dobel hitung di cash flow.
                $action->execute($gaji, [
                    'amount' => (float) $gaji->total + (float) $gaji->potongan_pinjaman,
                    'type' => 'expense',
                    'date' => $gaji->tanggal_transaksi,
                    'category' => 'Gaji Karyawan',
                    'description' => $gaji->deskripsi ?: 'Pembayaran gaji karyawan',
                ]);

                // Potongan pinjaman -> catat sebagai pengembalian pinjaman karyawan (income)
                $this->syncPengembalianPinjaman($gaji, $action);
            });

            session()->flash('success', 'Data Gaji Karyawan berhasil ditambahkan!');

            return redirect()->route('admin.gajikaryawan.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan data: '.$e->getMessage());
        }
    }

    private function updategajikaryawan(SyncCashFlowAction $action)
    {
        try {
            DB::transaction(function () use ($action) {
                $this->gajikaryawan->update($this->payload());
                $this->gajikaryawan->refresh();

                $action->execute($this->gajikaryawan, [
                    'amount' => (float) $this->gajikaryawan->total + (float) $this->gajikaryawan->potongan_pinjaman,
                    'type' => 'expense',
                    'date' => $this->gajikaryawan->tanggal_transaksi,
                    'category' => 'Gaji Karyawan',
                    'description' => $this->gajikaryawan->deskripsi ?: 'Pembayaran gaji karyawan',
                ]);

                $this->syncPengembalianPinjaman($this->gajikaryawan, $action);
            });

            session()->flash('success', 'Perubahan Data Gaji Karyawan berhasil disimpan!');

            return redirect()->route('admin.gajikaryawan.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengupdate data: '.$e->getMessage());
        }
    }

    /**
     * Sinkronkan potongan pinjaman ke fitur Pengembalian.
     * Nominal potongan pinjaman dicatat sebagai pengembalian milik karyawan
     * (mengurangi sisa pinjaman) DAN tercatat sebagai income di cash flow.
     * Agar tidak dobel hitung, beban gaji dinaikkan sebesar potongan ini
     * (lihat creategajikaryawan/updategajikaryawan), sehingga net kas tetap benar.
     */
    private function syncPengembalianPinjaman(GajiKaryawans $gaji, SyncCashFlowAction $action): void
    {
        $nominal = $this->toNumber($this->potongan_pinjaman);
        $namaKaryawan = User::find($gaji->nama_karyawan)?->name;

        $existing = Pengembalian::where('source_gaji_id', $gaji->id)->first();

        // Tidak ada potongan pinjaman -> hapus pengembalian terkait (cash flow ikut terhapus)
        if ($nominal <= 0) {
            $existing?->delete();

            return;
        }

        $data = [
            'nama_pengembalian' => $namaKaryawan,
            'tanggal_pengembalian' => $gaji->tanggal_transaksi,
            'nominal' => $nominal,
            'deskripsi' => 'Potongan pinjaman dari gaji ('.$gaji->id_transaksi.')',
            'status' => 'lunas',
            'source_gaji_id' => $gaji->id,
        ];

        if ($existing) {
            $existing->update($data);
            $pengembalian = $existing;
        } else {
            $pengembalian = Pengembalian::create($data);
        }

        // Catat / perbarui cash flow income untuk pengembalian ini
        $pengembalian->refresh();
        $action->execute($pengembalian, [
            'amount' => $nominal,
            'type' => 'income',
            'date' => $pengembalian->tanggal_pengembalian,
            'category' => 'Pinjaman',
            'description' => 'Pengembalian pinjaman (potongan gaji '.$gaji->id_transaksi.')',
        ]);
    }

    public function render()
    {
        $daftarBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $tahunSekarang = (int) now()->year;
        $daftarTahun = range($tahunSekarang, $tahunSekarang - 5);

        return view('livewire.pages.admin.gaji-karyawans.gaji-karyawans-form', [
            'users' => $this->users,
            'daftarBulan' => $daftarBulan,
            'daftarTahun' => $daftarTahun,
        ]);
    }
}
