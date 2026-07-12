<?php

namespace App\Livewire\Pages\Admin\Testimoni;

use Livewire\Component;

class TestimoniCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.testimoni.testimoni-create')
            ->layout('livewire.layout.templateindex');
    }
}
