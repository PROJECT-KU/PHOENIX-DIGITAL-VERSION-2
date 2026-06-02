<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Models\PemesananRsc;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PemesananrscDetail extends Component
{
    public $nama_camp;

    public $batch_camp;

    public $batchData;

    public $pesertaList;

    public function mount($nama_camp, $batch_camp)
    {
        $this->nama_camp = urldecode($nama_camp);
        $this->batch_camp = urldecode($batch_camp);

        $this->loadBatchData();
    }

    public function loadBatchData()
    {
        $this->batchData = PemesananRsc::query()
            ->select([
                'nama_camp',
                'batch_camp',
                'tanggal_mulai_camp',
                'tanggal_akhir_camp',
                'tanggal_pemesanan',
                'tanggal_berakhir',
                'akun',
                'pic',
                'status',
                'username',
                'password',
                'link_akses',
                'deskripsi',
                DB::raw('COUNT(*) as total_peserta'),
                DB::raw('SUM(CAST(total as DECIMAL(15,2))) as total_harga'),
                DB::raw('AVG(CAST(harga_satuan as DECIMAL(15,2))) as harga_satuan'),
            ])
            ->with(['dataakun', 'users'])
            ->where('nama_camp', $this->nama_camp)
            ->where('batch_camp', $this->batch_camp)
            ->groupBy([
                'nama_camp',
                'batch_camp',
                'tanggal_mulai_camp',
                'tanggal_akhir_camp',
                'tanggal_pemesanan',
                'tanggal_berakhir',
                'akun',
                'pic',
                'status',
                'username',
                'password',
                'link_akses',
                'deskripsi',
            ])
            ->first();

        // Ambil semua peserta dalam batch ini
        $this->pesertaList = PemesananRsc::query()
            ->with(['dataakun', 'users'])
            ->where('nama_camp', $this->nama_camp)
            ->where('batch_camp', $this->batch_camp)
            ->orderBy('nama_pembeli')
            ->get();

        // Redirect jika data tidak ditemukan
        if (! $this->batchData) {
            session()->flash('error', 'Data batch tidak ditemukan!');

            return redirect()->route('admin.pesananrsc.index');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-detail');
    }
}
