<?php

namespace App\Livewire\Pages\Admin\Pengembalian;

use App\Livewire\Concerns\MergesPinjamanData;
use App\Support\PeriodeGaji;
use Livewire\Component;
use Livewire\WithPagination;

class PengembalianList extends Component
{
    use MergesPinjamanData, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $bulan = '';

    public $tahun = '';

    // Mode periode: 'siklus20' = siklus gaji (21 s/d 20) atau 'kalender'.
    // Dibaca oleh MergesPinjamanData (dipakai bersama tab Peminjaman).
    public $modePeriode = 'siklus20';

    protected $queryString = [
        'search' => ['except' => ''],
        'bulan' => ['except' => ''],
        'tahun' => ['except' => ''],
        'modePeriode' => ['except' => 'siklus20'],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        // Default: siklus gaji 21-20 periode BERJALAN — seragam dengan Cash Flow,
        // Pengeluaran, Modal, Pemasukan, & Gaji.
        $p = PeriodeGaji::dariTanggal(now());
        $this->bulan = $p['bulan'];
        $this->tahun = $p['tahun'];
        $this->modePeriode = 'siklus20';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingBulan()
    {
        $this->resetPage();
    }

    public function updatingTahun()
    {
        $this->resetPage();
    }

    public function updatingModePeriode()
    {
        $this->resetPage();
    }

    public function resetFilter()
    {
        // Kembali ke default: siklus 21-20 periode berjalan, bukan dikosongkan.
        $p = PeriodeGaji::dariTanggal(now());
        $this->bulan = $p['bulan'];
        $this->tahun = $p['tahun'];
        $this->modePeriode = 'siklus20';
        $this->resetPage();
    }

    public function render()
    {
        // Rentang dihitung di komponen, bukan di view — supaya tanggal yang tampil
        // di chip mustahil beda dengan rentang yang benar-benar difilter.
        [$siklusMulai, $siklusAkhir] = $this->siklusTampil();

        return view('livewire.pages.admin.pengembalian.pengembalian-list', [
            'rows' => $this->buildMergedRows('pengembalian'),
            'totalLoans' => $this->buildTotalLoans(),
            'siklusMulai' => $siklusMulai,
            'siklusAkhir' => $siklusAkhir,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => $this->daftarTahun(),
        ])->layout('livewire.layout.templateindex');
    }

    public function downloadPdf()
    {
        return $this->generatePdf('pengembalian');
    }
}
