<?php

namespace App\Livewire\Pages\Admin\Blog;

use Livewire\Component;

class BlogCreate extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.blog.blog-create')
            ->layout('livewire.layout.templateindex');
    }
}
