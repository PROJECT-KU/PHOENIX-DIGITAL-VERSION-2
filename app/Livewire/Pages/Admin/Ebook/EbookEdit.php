<?php

namespace App\Livewire\Pages\Admin\Ebook;

use App\Models\Ebook;
use Livewire\Component;

class EbookEdit extends Component
{
    public Ebook $ebook;

    public function mount(Ebook $ebook)
    {
        $this->ebook = $ebook;
    }

    public function render()
    {
        return view('livewire.pages.admin.ebook.ebook-edit', [
            'ebook' => $this->ebook,
        ])->layout('livewire.layout.templateindex');
    }
}
