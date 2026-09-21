<?php

namespace App\Livewire\Pages\Public\Blog;

use App\Models\BlogPost;
use App\Support\DaftarIsiArtikel;
use App\Support\HtmlSanitizer;
use App\Support\RagamBlog;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;

class BlogShow extends Component
{
    public BlogPost $post;

    /** Benar bila yang membuka adalah admin yang sedang melihat draf. */
    public bool $pratinjau = false;

    public function mount(BlogPost $post)
    {
        // Alamat lama tetap hidup: dialihkan permanen ke alamat yang baru,
        // supaya tautan yang sudah beredar tidak mati dan peringkatnya pindah.
        if ($post->slugDiminta && $post->slugDiminta !== $post->slug) {
            // Dilempar sebagai respons, bukan "return redirect()": Livewire
            // membungkus redirect dari mount menjadi 302, padahal pemindahan
            // alamat permanen harus 301 supaya peringkatnya ikut pindah.
            // RedirectResponse langsung, bukan helper redirect(): di dalam
            // komponen Livewire helper itu mengembalikan Redirector milik
            // Livewire yang selalu berakhir 302.
            throw new HttpResponseException(
                new RedirectResponse(route('blog.show', $post->slug), 301)
            );
        }

        $belumTayang = $post->status !== 'published'
            || ($post->published_at && $post->published_at->isFuture())
            || ($post->unpublish_at && $post->unpublish_at->isPast());

        if ($belumTayang) {
            // Draf & artikel terjadwal tetap tertutup untuk publik, tetapi
            // boleh dibuka oleh admin sebagai PRATINJAU — tanpa itu satu-
            // satunya cara melihat hasilnya adalah menerbitkannya dulu.
            abort_unless(auth()->user()?->hasPermission('view_blog'), 404);

            $this->pratinjau = true;
        }

        $this->post = $post;

        if (! $this->pratinjau && $this->layakDihitung($post)) {
            $post->catatBaca();
        }

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
            // Tidak lagi memakai $post->author supaya nama karyawan tidak bocor ke
            // hasil pencarian. Dipakai nama organisasi, bukan "admin", karena
            // @type-nya Organization dan Google membacanya sebagai nama penerbit.
            'author' => ['@type' => 'Organization', 'name' => 'Phoenix Digital'],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Phoenix Digital',
                'logo' => ['@type' => 'ImageObject', 'url' => asset(config('seo.image'))],
            ],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('blog.show', $post->slug)],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Apakah kunjungan ini pantas menambah hitungan baca.
     *
     * Sebelumnya setiap pemuatan halaman dihitung — termasuk muat ulang
     * berkali-kali, kunjungan admin sendiri, dan perayap mesin pencari —
     * sehingga "paling dibaca" tidak bisa dipercaya.
     *
     * Penandanya disimpan di sesi peramban, bukan di basis data: tidak ada
     * jejak siapa membaca apa yang ikut tersimpan.
     */
    private function layakDihitung(BlogPost $post): bool
    {
        if (auth()->hasUser()) {
            return false;
        }

        $agen = strtolower((string) request()->userAgent());
        foreach (['bot', 'crawl', 'spider', 'slurp', 'preview', 'facebookexternalhit', 'headless'] as $tanda) {
            if (str_contains($agen, $tanda)) {
                return false;
            }
        }

        $kunci = 'blog_dibaca';
        $sudah = (array) session()->get($kunci, []);
        $batas = now()->getTimestamp() - 6 * 3600;

        // Buang catatan lama supaya sesi tidak menggemuk tanpa batas.
        $sudah = array_filter($sudah, fn ($waktu) => $waktu > $batas);

        if (isset($sudah[$post->id])) {
            session()->put($kunci, $sudah);

            return false;
        }

        $sudah[$post->id] = now()->getTimestamp();
        session()->put($kunci, $sudah);

        return true;
    }

    public function render()
    {
        // Artikel terkait disusun berlapis: yang berbagi TAG lebih dulu
        // (penanda topik paling tajam), lalu sekategori, lalu yang terbaru.
        $tag = $this->post->tagDaftar();
        $related = collect();

        if ($tag) {
            $related = BlogPost::published()
                ->where('id', '!=', $this->post->id)
                ->where(function ($q) use ($tag) {
                    foreach ($tag as $t) {
                        $q->orWhereJsonContains('tags', $t);
                    }
                })
                ->orderByDesc('published_at')
                ->take(3)
                ->get();
        }

        if ($related->count() < 3 && $this->post->category) {
            $related = $related->concat(
                BlogPost::published()
                    ->where('id', '!=', $this->post->id)
                    ->where('category', $this->post->category)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->orderByDesc('published_at')
                    ->take(3 - $related->count())
                    ->get()
            );
        }

        // Kalau masih kurang, lengkapi dengan artikel terbaru lainnya.
        if ($related->count() < 3) {
            $related = $related->concat(
                BlogPost::published()
                    ->where('id', '!=', $this->post->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->orderByDesc('published_at')
                    ->take(3 - $related->count())
                    ->get()
            );
        }

        // Isi disaring dulu, BARU diberi id jangkar untuk daftar isi — penyaring
        // membuang atribut id, jadi urutan sebaliknya menghapus jangkarnya lagi.
        $susunan = DaftarIsiArtikel::susun(HtmlSanitizer::bersihkan($this->post->body));

        return view('livewire.pages.public.blog.blog-show', [
            'related' => $related,
            'isi' => $susunan['html'],
            'daftarIsi' => $susunan['daftar'],
            'ragam' => RagamBlog::untuk($this->post->category),
        ])->layout('layouts.guest');
    }
}
