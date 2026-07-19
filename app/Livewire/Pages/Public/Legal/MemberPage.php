<?php

namespace App\Livewire\Pages\Public\Legal;

use Livewire\Attributes\Layout;
use Livewire\Component;

class MemberPage extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        // Angka diambil dari SUMBERNYA, bukan diketik ulang di view — kalau rumus
        // poin di Customer berubah, halaman ini ikut benar dgn sendirinya.
        //   Customer::calculateYearlyPoints() -> floor($total / 50000)
        //   Customer::getPointValue()         -> $point * 500
        $perPoin = 50000;
        $nilaiPoin = 500;

        return view('livewire.pages.public.legal.member', [
            'perPoin' => $perPoin,
            'nilaiPoin' => $nilaiPoin,
            'persenBalik' => round($nilaiPoin / $perPoin * 100, 1),
            'contohBelanja' => 170000,
            'contohPoin' => intdiv(170000, $perPoin),
            'contohSisa' => 170000 % $perPoin,
            'contohNilai' => intdiv(170000, $perPoin) * $nilaiPoin,
        ]);
    }
}
