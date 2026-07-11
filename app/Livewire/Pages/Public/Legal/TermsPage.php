<?php

namespace App\Livewire\Pages\Public\Legal;

use Livewire\Attributes\Layout;
use Livewire\Component;

class TermsPage extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.legal.terms');
    }
}
