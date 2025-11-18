<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Url;
use Livewire\Component;

class GlobalSearch extends Component
{
    #[Url(as: 'search', keep: false)]
    public $searchQuery = '';

    public function search()
    {
        if (empty(trim($this->searchQuery))) {
            return;
        }

        if (request()->routeIs('shop')) {
            $this->dispatch('search-updated', search: $this->searchQuery);
        } else {
            return $this->redirect('/shop?search=' . urlencode($this->searchQuery));
        }
    }

    public function mount()
    {
        $this->searchQuery = request('search', '');
    }
    public function render()
    {
        return view('livewire.components.global-search');
    }
}
