<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use App\Models\Testimoni;
use Livewire\Component;

class TestimoniEdit extends Component
{
    public Testimoni $testimoni;

    public function mount(Testimoni $testimoni)
    {
        $this->testimoni = $testimoni;
    }

    public function render()
    {
        return view('livewire.pages.admin.testimoni.testimoni-edit', [
            'testimoni' => $this->testimoni,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
