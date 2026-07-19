<?php

namespace App\Livewire\Pages\Public\Blog;

use App\Models\BlogPost;
use Livewire\Attributes\Layout;
use Livewire\Component;

class BlogShow extends Component
{
    public BlogPost $post;

    public function mount(BlogPost $post)
    {
        // Hanya artikel terbit yang boleh diakses publik.
        if ($post->status !== 'published'
            || ($post->published_at && $post->published_at->isFuture())) {
            abort(404);
        }

        $this->post = $post;

        // Hitung tampilan (tanpa mengganggu updated_at).
        BlogPost::whereKey($post->id)->update(['views' => $post->views + 1]);

        // SEO dinamis — dibaca partials/seo.blade.php.
        $title = $post->meta_title ?: $post->title;
        $desc = $post->meta_description
            ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 155));

        view()->share('seoTitle', $title.' | Blog Phoenix Digital');
        view()->share('seoDescription', $desc);
        view()->share('seoCrumbName', $post->title);
        if ($post->cover) {
            view()->share('seoImage', 'storage/img/blog/'.$post->cover);
        }

        view()->share('seoJsonLd', json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $desc,
            'image' => $post->cover ? asset('storage/img/blog/'.$post->cover) : asset(config('seo.image')),
            'datePublished' => optional($post->published_at ?? $post->created_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'author' => ['@type' => 'Organization', 'name' => $post->author ?: 'Phoenix Digital'],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Phoenix Digital',
                'logo' => ['@type' => 'ImageObject', 'url' => asset(config('seo.image'))],
            ],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('blog.show', $post->slug)],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function render()
    {
        $related = BlogPost::published()
            ->where('id', '!=', $this->post->id)
            ->when($this->post->category, fn ($q) => $q->where('category', $this->post->category))
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        // Kalau kategori sama tak cukup, lengkapi dengan artikel terbaru lainnya.
        if ($related->count() < 3) {
            $extra = BlogPost::published()
                ->where('id', '!=', $this->post->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->orderByDesc('published_at')
                ->take(3 - $related->count())
                ->get();
            $related = $related->concat($extra);
        }

        return view('livewire.pages.public.blog.blog-show', [
            'related' => $related,
        ])->layout('layouts.guest');
    }
}
