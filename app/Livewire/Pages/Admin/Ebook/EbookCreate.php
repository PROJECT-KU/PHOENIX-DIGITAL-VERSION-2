<?php

namespace App\Livewire\Pages\Admin\Ebook;

use Livewire\Component;

class EbookCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.ebook.ebook-create')
            ->layout('livewire.layout.templateindex');
    }
}
