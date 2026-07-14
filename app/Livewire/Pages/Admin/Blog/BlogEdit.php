<?php

namespace App\Livewire\Pages\Admin\Blog;

use App\Models\BlogPost;
use Livewire\Component;

class BlogEdit extends Component
{
    public BlogPost $post;

    public function mount(BlogPost $post)
    {
        $this->post = $post;
    }

    public function render()
    {
        return view('livewire.pages.admin.blog.blog-edit', [
            'post' => $this->post,
        ])->layout('livewire.layout.templateindex');
    }
}
