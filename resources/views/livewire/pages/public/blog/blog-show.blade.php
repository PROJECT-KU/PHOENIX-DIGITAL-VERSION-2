@section('title')
{{ $post->meta_title ?: $post->title }} | Blog Phoenix Digital
@endsection

<div class="ph-article">
    <style>
        /* Sembunyikan garis animasi latar khusus di halaman detail blog */
        #ph-page-lines { display: none !important; }
        .ph-article { --o: var(--ph-orange, #f26522); --a: var(--ph-amber, #fba919); --ink: var(--ph-ink, #23272f);
            --muted: var(--ph-muted, #6b7280); --soft: var(--ph-soft, #fff8f1); --line: var(--ph-line, #f1e6d8);
            --grad: var(--ph-grad, linear-gradient(135deg, #fba919 0%, #f26522 100%)); }

        .ph-article .art-shell { max-width: 1120px; }
        .ph-article .art-grid { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 48px; align-items: start; }

        /* Breadcrumb */
        .ph-article .breadcrumbs { margin: 2px 0 20px; }
        .ph-article .breadcrumbs ol { list-style: none; display: flex; flex-wrap: wrap; gap: 8px; padding: 0; margin: 0; font-size: .84rem; color: var(--muted); }
        .ph-article .breadcrumbs li:not(:last-child)::after { content: "/"; margin-left: 8px; color: #cbd0d8; }
        .ph-article .breadcrumbs a { color: var(--o); text-decoration: none; }
        .ph-article .breadcrumbs .current { color: var(--muted); }

        /* Head */
        .ph-article .art-cat {
            display: inline-flex; align-items: center; gap: .4rem; background: var(--soft); color: var(--o);
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .72rem; padding: .34rem .85rem;
            border-radius: 999px; text-transform: uppercase; letter-spacing: .06em;
        }
        .ph-article h1.art-title {
            font-family: 'Poppins', sans-serif; font-weight: 800; font-size: clamp(1.8rem, 3.4vw, 2.5rem);
            color: var(--ink); line-height: 1.2; letter-spacing: -.015em; margin: .9rem 0 .9rem;
        }
        .ph-article .art-meta { display: flex; flex-wrap: wrap; gap: 1.2rem; color: var(--muted); font-size: .88rem; padding-bottom: 1.3rem; border-bottom: 1px solid var(--line); margin-bottom: 1.6rem; }
        .ph-article .art-meta i { color: var(--o); }
        .ph-article .art-cover { border-radius: 18px; overflow: hidden; margin-bottom: 1.8rem; box-shadow: 0 12px 30px rgba(35,39,47,.10); aspect-ratio: 16/9; background: var(--ph-grad-soft, linear-gradient(135deg,#fff5e9,#fff9f3)); }
        .ph-article .art-cover img { width: 100%; height: 100%; object-fit: cover; }
        .ph-article .art-lead { font-family: 'Poppins', sans-serif; font-size: 1.16rem; line-height: 1.65; color: var(--ink); font-weight: 500; margin-bottom: 1.6rem; }

        /* Prose */
        .ph-article .prose { color: #3a3f4a; font-size: 1.06rem; line-height: 1.85; }
        .ph-article .prose > p:first-of-type::first-letter {
            float: left; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 3.2rem; line-height: .82;
            padding: .3rem .55rem 0 0; color: var(--o);
        }
        .ph-article .prose h2 {
            font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--ink);
            line-height: 1.3; margin: 2.1rem 0 .9rem; padding-left: 14px; position: relative;
        }
        .ph-article .prose h2::before { content: ""; position: absolute; left: 0; top: .18em; bottom: .18em; width: 5px; border-radius: 4px; background: var(--grad); }
        .ph-article .prose h3 { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.25rem; color: var(--ink); margin: 1.7rem 0 .7rem; }
        .ph-article .prose p { margin-bottom: 1.2rem; }
        .ph-article .prose ul, .ph-article .prose ol { margin: 0 0 1.2rem 1.3rem; }
        .ph-article .prose li { margin-bottom: .5rem; padding-left: .2rem; }
        .ph-article .prose li::marker { color: var(--o); font-weight: 700; }
        .ph-article .prose a { color: var(--o); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
        .ph-article .prose a:hover { color: var(--ph-orange-2, #f4772b); }
        .ph-article .prose img { max-width: 100%; border-radius: 14px; margin: 1.4rem 0; }
        .ph-article .prose blockquote {
            border: none; position: relative; background: var(--soft); padding: 1.2rem 1.4rem 1.2rem 3rem;
            border-radius: 14px; margin: 1.6rem 0; color: #7c4a24; font-style: italic; line-height: 1.65;
        }
        .ph-article .prose blockquote::before { content: "\201C"; position: absolute; left: .9rem; top: .35rem; font-family: Georgia, serif; font-size: 2.8rem; color: var(--a); line-height: 1; }
        .ph-article .prose strong { color: var(--ink); font-weight: 700; }
        .ph-article .prose hr { border: none; border-top: 1px solid var(--line); margin: 2rem 0; }

        /* Share + back (inline di bawah artikel) */
        .ph-article .art-foot { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: 2.4rem; padding-top: 1.4rem; border-top: 1px solid var(--line); }
        .ph-article .share { display: flex; align-items: center; gap: .55rem; flex-wrap: wrap; }
        .ph-article .share .lbl { font-family: 'Poppins', sans-serif; font-weight: 700; color: var(--ink); font-size: .9rem; margin-right: .2rem; }
        .ph-article .share a, .ph-article .share button { width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 1.02rem; transition: transform .2s ease; border: none; }
        .ph-article .share a:hover, .ph-article .share button:hover { transform: translateY(-3px); }
        .ph-article .share .wa { background: #25d366; }
        .ph-article .share .fb { background: #1877f2; }
        .ph-article .share .tw { background: #111827; }
        .ph-article .share .cp { background: var(--grad); cursor: pointer; }
        .ph-article .back-cta { display: inline-flex; align-items: center; gap: .45rem; color: var(--o); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .9rem; text-decoration: none; }
        .ph-article .back-cta:hover i { transform: translateX(-4px); }
        .ph-article .back-cta i { transition: transform .2s ease; }

        /* Sidebar */
        .ph-article .art-side { position: sticky; top: 92px; display: flex; flex-direction: column; gap: 22px; }
        .ph-article .side-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; padding: 1.3rem 1.3rem 1.4rem; box-shadow: 0 8px 24px rgba(35,39,47,.05); }
        .ph-article .side-title { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.02rem; color: var(--ink); display: flex; align-items: center; gap: .5rem; margin-bottom: 1rem; }
        .ph-article .side-title i { color: var(--o); }
        .ph-article .side-post { display: flex; gap: .8rem; align-items: center; text-decoration: none; padding: .55rem 0; border-top: 1px solid var(--line); }
        .ph-article .side-post:first-of-type { border-top: none; padding-top: 0; }
        .ph-article .side-post .sp-thumb { flex-shrink: 0; width: 74px; aspect-ratio: 16/9; border-radius: 10px; overflow: hidden; background: var(--ph-grad-soft, linear-gradient(135deg,#fff5e9,#fff9f3)); position: relative; }
        .ph-article .side-post .sp-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .ph-article .side-post .sp-thumb .fb { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #f0a35f; }
        .ph-article .side-post .sp-date { font-size: .72rem; color: var(--muted); display: block; margin-bottom: 2px; }
        .ph-article .side-post .sp-name { font-family: 'Poppins', sans-serif; font-weight: 600; font-size: .88rem; color: var(--ink); line-height: 1.35; }
        .ph-article .side-post:hover .sp-name { color: var(--o); }

        .ph-article .cta-card { position: relative; overflow: hidden; border-radius: 18px; padding: 1.5rem 1.4rem; color: #fff; background: var(--grad); box-shadow: 0 12px 30px rgba(242,101,34,.28); }
        .ph-article .cta-card::after { content: ""; position: absolute; right: -40px; top: -40px; width: 140px; height: 140px; border-radius: 50%; background: rgba(255,255,255,.14); }
        .ph-article .cta-card h4 { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.1rem; margin-bottom: .4rem; position: relative; }
        .ph-article .cta-card p { font-size: .86rem; opacity: .95; margin-bottom: 1rem; position: relative; }
        .ph-article .cta-card .cta-btn { position: relative; display: inline-flex; align-items: center; gap: .45rem; background: #fff; color: var(--o); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .88rem; padding: .6rem 1.1rem; border-radius: 999px; text-decoration: none; }
        .ph-article .cta-card .cta-wa { position: relative; display: inline-flex; align-items: center; gap: .4rem; color: #fff; font-size: .82rem; margin-top: .7rem; text-decoration: none; opacity: .95; }

        @media (max-width: 991.98px) {
            .ph-article .art-grid { grid-template-columns: 1fr; gap: 34px; }
            .ph-article .art-side { position: static; }
        }
    </style>

    <section class="pt-4 pt-lg-5 pb-4 pb-lg-5" style="margin-top: 14px;">
        <div class="container art-shell">
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}" wire:navigate>Beranda</a></li>
                    <li><a href="{{ route('blog.index') }}" wire:navigate>Blog</a></li>
                    <li class="current">{{ \Illuminate\Support\Str::limit($post->title, 44) }}</li>
                </ol>
            </nav>

            <div class="art-grid">
                {{-- KOLOM ARTIKEL --}}
                <article class="art-main">
                    @if ($post->category)<span class="art-cat"><i class="bi bi-tag-fill"></i> {{ $post->category }}</span>@endif
                    <h1 class="art-title">{{ $post->title }}</h1>

                    <div class="art-meta">
                        {{-- Penulis sengaja statis "admin", tidak diambil dari kolom
                             author maupun akun yang login, supaya nama karyawan tidak
                             tampil di halaman publik. --}}
                        <span><i class="bi bi-person-circle me-1"></i>admin</span>
                        <span><i class="bi bi-calendar3 me-1"></i>{{ optional($post->published_at ?? $post->created_at)->translatedFormat('d F Y') }}</span>
                        <span><i class="bi bi-clock me-1"></i>{{ $post->readingMinutes() }} menit baca</span>
                        <span><i class="bi bi-eye me-1"></i>{{ number_format($post->views) }}x dilihat</span>
                    </div>

                    @if ($post->cover && \Storage::disk('public')->exists('img/blog/' . $post->cover))
                    <div class="art-cover">
                        <img src="{{ asset('storage/img/blog/' . $post->cover) }}" alt="{{ $post->title }}" decoding="async">
                    </div>
                    @endif

                    @if ($post->excerpt)
                    <p class="art-lead">{{ $post->excerpt }}</p>
                    @endif

                    <div class="prose">
                        {{-- Disaring saat tampil: isi blog berupa HTML dari editor,
                             tanpa ini penulis blog bisa menyisipkan <script> yang
                             berjalan di browser semua pengunjung. --}}
                        {!! \App\Support\HtmlSanitizer::bersihkan($post->body) !!}
                    </div>

                    @php $url = route('blog.show', $post->slug); @endphp
                    <div class="art-foot">
                        <div class="share">
                            <span class="lbl">Bagikan:</span>
                            <a class="wa" target="_blank" rel="noopener" title="WhatsApp"
                                href="https://wa.me/?text={{ urlencode($post->title . ' — ' . $url) }}"><i class="bi bi-whatsapp"></i></a>
                            <a class="fb" target="_blank" rel="noopener" title="Facebook"
                                href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"><i class="bi bi-facebook"></i></a>
                            <a class="tw" target="_blank" rel="noopener" title="X / Twitter"
                                href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode($url) }}"><i class="bi bi-twitter-x"></i></a>
                            <button class="cp share-copy-btn" type="button" title="Salin tautan" data-url="{{ $url }}"><i class="bi bi-link-45deg"></i></button>
                        </div>
                        <a href="{{ route('blog.index') }}" wire:navigate class="back-cta"><i class="bi bi-arrow-left"></i> Kembali ke Blog</a>
                    </div>
                </article>

                {{-- SIDEBAR --}}
                <aside class="art-side">
                    @if ($related->isNotEmpty())
                    <div class="side-card">
                        <div class="side-title"><i class="bi bi-collection"></i> Bacaan Lainnya</div>
                        @foreach ($related as $rel)
                        <a href="{{ route('blog.show', $rel->slug) }}" wire:navigate class="side-post">
                            <span class="sp-thumb">
                                @if ($rel->cover && \Storage::disk('public')->exists('img/blog/' . $rel->cover))
                                    <img src="{{ asset('storage/img/blog/' . $rel->cover) }}" alt="{{ $rel->title }}" loading="lazy" decoding="async">
                                @else
                                    <span class="fb"><i class="bi bi-journal-text"></i></span>
                                @endif
                            </span>
                            <span>
                                <span class="sp-date">{{ optional($rel->published_at ?? $rel->created_at)->translatedFormat('d M Y') }}</span>
                                <span class="sp-name">{{ \Illuminate\Support\Str::limit($rel->title, 58) }}</span>
                            </span>
                        </a>
                        @endforeach
                    </div>
                    @endif

                    <div class="cta-card">
                        <h4>Cari akun premium bergaransi?</h4>
                        <p>Jelajahi katalog akun premium, lisensi &amp; tools AI Phoenix Digital — proses cepat &amp; aman.</p>
                        <a href="{{ route('shop.index') }}" wire:navigate class="cta-btn"><i class="bi bi-bag"></i> Lihat Produk</a>
                        <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya." target="_blank" rel="noopener" class="cta-wa"><i class="bi bi-whatsapp"></i> atau tanya admin</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.share-copy-btn');
        if (!btn) return;
        const url = btn.getAttribute('data-url');
        navigator.clipboard.writeText(url).then(() => {
            if (window.phToast) { window.phToast('Tautan artikel disalin!', 'success'); }
            else { const i = btn.querySelector('i'); if (i) { i.className = 'bi bi-check-lg'; setTimeout(() => i.className = 'bi bi-link-45deg', 1500); } }
        });
    });
</script>
@endpush
