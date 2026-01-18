<?php

namespace App\Livewire\Pages\Public\About;

use Livewire\Attributes\Layout;
use Livewire\Component;

class AboutPage extends Component
{
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.about.about-page');
    }
}
