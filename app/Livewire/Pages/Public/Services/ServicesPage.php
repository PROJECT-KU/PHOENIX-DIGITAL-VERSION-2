<?php

namespace App\Livewire\Pages\Public\Services;

use Livewire\Attributes\Layout;
use Livewire\Component;

class ServicesPage extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.services.services');
    }
}
