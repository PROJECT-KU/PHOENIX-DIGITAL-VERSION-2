<?php

namespace App\Livewire\Pages\Admin\PemesananRSC;

use App\Models\PemesananRsc;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PemesananrscEdit extends Component
{
    public PemesananRsc $pemesananrsc;

    public $pemesananBatch;

    public $nama_camp;

    public $batch_camp;

    public function mount($batch_camp, $nama_camp)
    {
        if ($batch_camp && $nama_camp) {
            $this->nama_camp = $nama_camp;
            $this->batch_camp = $batch_camp;

            $this->pemesananBatch = PemesananRsc::where('nama_camp', $nama_camp)
                ->where('batch_camp', $batch_camp)
                ->get();

            $this->pemesananrsc = $this->pemesananBatch->first();
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.pemesanan-r-s-c.pemesananrsc-edit', [
            'pemesananrsc' => $this->pemesananrsc,
            'pemesananBatch' => $this->pemesananBatch,
        ]);
    }
}
