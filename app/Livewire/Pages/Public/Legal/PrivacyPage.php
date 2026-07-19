<?php

namespace App\Livewire\Pages\Public\Legal;

use Livewire\Attributes\Layout;
use Livewire\Component;

class PrivacyPage extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.legal.privacy');
    }
}
