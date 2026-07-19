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

    // Bonus penyelesaian task diatur di halaman "Penyelesaian Task" (pool bersama).
    // Di form ini nilainya read-only (carried), tetap masuk perhitungan total.
    public $bonus_penyelesaian_task = 0;

    public $jam_lembur;

    public $tarif_lembur;

    public $uang_lembur;

    // Presensi (ditarik otomatis dari fitur presensi berdasarkan karyawan + periode)
    public $jumlah_hadir_offline = 0;

    public $tarif_hadir_offline;

    public $uang_hadir_offline;

    public $jumlah_hadir_online = 0;

    public $tarif_hadir_online;

    public $uang_hadir_online;

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
            $this->bonus_penyelesaian_task = (int) $this->gajikaryawan->bonus_penyelesaian_task;
            $this->jam_lembur = $this->gajikaryawan->jam_lembur ?: null;
            // Tarif/jam diturunkan dari data historis bila ada, agar nilai lama tetap akurat
            $this->tarif_lembur = ($this->gajikaryawan->jam_lembur > 0)
                ? $this->formatAngka((int) round($this->gajikaryawan->uang_lembur / $this->gajikaryawan->jam_lembur))
                : $this->formatAngka(Setting::get('tarif_lembur_per_jam', 15000));
            $this->uang_lembur = $this->formatAngka($this->gajikaryawan->uang_lembur);

            // Presensi: tampilkan nilai historis tersimpan (tarif diturunkan dari uang/jumlah)
            $this->jumlah_hadir_offline = (int) ($this->gajikaryawan->jumlah_hadir_offline ?? 0);
            $this->tarif_hadir_offline = ($this->gajikaryawan->jumlah_hadir_offline > 0)
                ? $this->formatAngka((int) round($this->gajikaryawan->uang_hadir_offline / $this->gajikaryawan->jumlah_hadir_offline))
                : $this->formatAngka(Setting::get('tarif_presensi_offline', 0));
            $this->uang_hadir_offline = $this->formatAngka($this->gajikaryawan->uang_hadir_offline);
            $this->jumlah_hadir_online = (int) ($this->gajikaryawan->jumlah_hadir_online ?? 0);
            $this->tarif_hadir_online = ($this->gajikaryawan->jumlah_hadir_online > 0)
                ? $this->formatAngka((int) round($this->gajikaryawan->uang_hadir_online / $this->gajikaryawan->jumlah_hadir_online))
                : $this->formatAngka(Setting::get('tarif_presensi_online', 0));
            $this->uang_hadir_online = $this->formatAngka($this->gajikaryawan->uang_hadir_online);

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

            // Gaji yang masih DRAFT (belum dibayar) disegarkan dari sumbernya:
            // - tarif lembur & tarif hadir  -> data Karyawan (EmployeeDetail) terkini
            // - jam lembur & jumlah hadir   -> fitur Presensi sesuai PERIODE GAJI (21 s/d 20)
            // Ini penting untuk draft hasil "Generate Gaji" yang dibuat sebelum presensi lengkap.
            // Gaji COMPLETED sengaja TIDAK diubah (nilai historis terkunci).
            if ($this->status !== 'completed') {
                $this->muatTarifKaryawan();
                $this->muatDataPresensi();
            }

            $this->hitungSisaPinjaman();
        } else {
            $this->mode = 'create';
            $this->periode_bulan = now()->month;
            $this->periode_tahun = now()->year;
            // Default tanggal bayar = tanggal gajian (cutoff, default tgl 20) periode berjalan.
            $this->tanggal_transaksi = \App\Support\PeriodeGaji::tanggalBayar(
                (int) $this->periode_bulan,
                (int) $this->periode_tahun
            )->format('Y-m-d');
            $this->bonus_penyelesaian_task = 0;
            // Tarif diisi dari data karyawan saat karyawan dipilih; default 0.
            $this->jam_lembur = 0;
            $this->tarif_lembur = $this->formatAngka(0);
            $this->uang_lembur = $this->formatAngka(0);

            $this->tarif_hadir_offline = $this->formatAngka(0);
            $this->tarif_hadir_online = $this->formatAngka(0);
            $this->uang_hadir_offline = $this->formatAngka(0);
            $this->uang_hadir_online = $this->formatAngka(0);
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
        $this->muatTarifKaryawan();
        $this->muatDataPresensi();
    }

    /**
     * Ambil tarif bonus milik karyawan terpilih (offline/online/lembur).
     * Tidak diisi -> 0 (tanpa bonus).
     */
    private function muatTarifKaryawan(): void
    {
        if (! $this->nama_karyawan) {
            return;
        }

        $detail = \App\Models\EmployeeDetail::where('user_id', $this->nama_karyawan)->first();

        $this->tarif_hadir_offline = $this->formatAngka((int) ($detail->tarif_presensi_offline ?? 0));
        $this->tarif_hadir_online = $this->formatAngka((int) ($detail->tarif_presensi_online ?? 0));
        $this->tarif_lembur = $this->formatAngka((int) ($detail->tarif_lembur_per_jam ?? 0));
    }

    /**
     * Tarik jumlah kehadiran (offline/online) & jam lembur dari fitur presensi
     * untuk karyawan + periode terpilih. Hanya sesi yang sudah absen pulang.
     */
    private function muatDataPresensi(): void
    {
        if (! $this->nama_karyawan || ! $this->periode_bulan || ! $this->periode_tahun) {
            return;
        }

        // Ikuti PERIODE GAJI (21 bln sebelumnya s/d 20 bln ini), bukan bulan kalender.
        $rekap = \App\Models\Presensi::rekapPeriodeGaji(
            $this->nama_karyawan,
            (int) $this->periode_bulan,
            (int) $this->periode_tahun
        );

        $this->jumlah_hadir_offline = $rekap['hari_offline'];
        $this->jumlah_hadir_online = $rekap['hari_online'];
        $this->jam_lembur = (int) round($rekap['jam_lembur']);

        $this->hitungUangPresensi();
        $this->hitungUangLembur();
        $this->calculateTotal();
    }

    /**
     * Uang presensi = jumlah kehadiran x tarif per kehadiran (offline & online terpisah).
     */
    private function hitungUangPresensi(): void
    {
        $tarifOff = (int) $this->toNumber($this->tarif_hadir_offline);
        $tarifOn = (int) $this->toNumber($this->tarif_hadir_online);

        $this->uang_hadir_offline = $this->formatAngka((int) $this->jumlah_hadir_offline * $tarifOff);
        $this->uang_hadir_online = $this->formatAngka((int) $this->jumlah_hadir_online * $tarifOn);
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
        // Periode berubah -> tarik ulang data presensi karyawan
        if (in_array($propertyName, ['periode_bulan', 'periode_tahun'])) {
            $this->muatDataPresensi();
        }

        // Jam lembur / tarif berubah -> hitung ulang uang lembur
        if (in_array($propertyName, ['jam_lembur', 'tarif_lembur'])) {
            $this->hitungUangLembur();
        }

        // Tarif presensi berubah -> hitung ulang uang presensi
        if (in_array($propertyName, ['tarif_hadir_offline', 'tarif_hadir_online'])) {
            $this->hitungUangPresensi();
        }

        if (in_array($propertyName, [
            'gaji_pokok', 'bonus_kinerja', 'bonus_lainnya', 'uang_lembur',
            'jam_lembur', 'tarif_lembur',
            'tarif_hadir_offline', 'tarif_hadir_online',
            'uang_hadir_offline', 'uang_hadir_online',
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
            (int) $this->bonus_penyelesaian_task +
            $this->toNumber($this->uang_lembur) +
            $this->toNumber($this->uang_hadir_offline) +
            $this->toNumber($this->uang_hadir_online) +
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
        // Nilai 0 / kosong ditampilkan sebagai input kosong (placeholder "0"),
        // bukan angka "0" — supaya jelas field belum diisi.
        if ((int) $angka === 0) {
            return '';
        }

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
        // Pastikan uang lembur & presensi & total dihitung otomatis
        $this->hitungUangLembur();
        $this->hitungUangPresensi();
        $this->calculateTotal();

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
            'bonus_penyelesaian_task' => (int) $this->bonus_penyelesaian_task,
            'uang_lembur' => $this->toNumber($this->uang_lembur),
            'jam_lembur' => (int) $this->toNumber($this->jam_lembur),
            'jumlah_hadir_offline' => (int) $this->jumlah_hadir_offline,
            'uang_hadir_offline' => $this->toNumber($this->uang_hadir_offline),
            'jumlah_hadir_online' => (int) $this->jumlah_hadir_online,
            'uang_hadir_online' => $this->toNumber($this->uang_hadir_online),
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
        $baru = null;

        try {
            DB::transaction(function () use ($action, &$baru) {
                $gaji = $baru = GajiKaryawans::create(array_merge(
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

            // Gaji bisa langsung dibuat completed -> 'pending' sbg status "sebelum".
            $this->beritahuKaryawanGajiLunas($baru, 'pending');

            session()->flash('success', 'Data Gaji Karyawan berhasil ditambahkan!');

            return redirect()->route('admin.gajikaryawan.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menambahkan data: '.$e->getMessage());
        }
    }

    private function updategajikaryawan(SyncCashFlowAction $action)
    {
        // Ditangkap SEBELUM update — sesudahnya nilai lama sudah hilang, dan
        // tanpa ini notifikasi akan terkirim ulang tiap kali gaji completed diedit.
        $statusSebelum = $this->gajikaryawan->status;

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

            // Sesudah transaksi commit — kalau simpanan gagal & rollback, karyawan
            // tidak boleh terlanjur dapat kabar gajinya cair.
            $this->beritahuKaryawanGajiLunas($this->gajikaryawan, $statusSebelum);

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
    /**
     * Kirim notifikasi lonceng ke karyawan HANYA saat gajinya berpindah ke LUNAS.
     * Dipakai bersama oleh jalur create & update supaya aturannya cuma di satu tempat.
     *
     * Sengaja tidak dikirim bila: gaji sudah completed sejak awal (mis. admin cuma
     * mengedit catatan), atau statusnya masih pending.
     */
    private function beritahuKaryawanGajiLunas(?GajiKaryawans $gaji, ?string $statusSebelum): void
    {
        if (! $gaji || $gaji->status !== 'completed' || $statusSebelum === 'completed') {
            return;
        }

        $gaji->karyawan?->notify(new \App\Notifications\GajiCompleted($gaji));
    }

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
