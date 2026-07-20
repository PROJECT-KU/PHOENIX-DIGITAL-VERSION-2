@section('title')
Blog — Tips, Panduan & Info Akun Premium | Phoenix Digital
@endsection

<div class="ph-blog">
    <style>
        /* Sembunyikan garis animasi latar khusus di halaman blog */
        #ph-page-lines { display: none !important; }
        .ph-blog { --o: var(--ph-orange, #f26522); --a: var(--ph-amber, #fba919); --ink: var(--ph-ink, #23272f);
            --muted: var(--ph-muted, #6b7280); --soft: var(--ph-soft, #fff8f1); --line: var(--ph-line, #f1e6d8);
            --grad: var(--ph-grad, linear-gradient(135deg, #fba919 0%, #f26522 100%)); }

        /* Toolbar */
        .ph-blog .blog-toolbar { display: flex; flex-direction: column; align-items: center; gap: 18px; margin: 6px 0 40px; }
        .ph-blog .blog-search { position: relative; width: 100%; max-width: 480px; }
        .ph-blog .blog-search input {
            width: 100%; border: 1.5px solid var(--line); border-radius: 999px; padding: .85rem 2.8rem;
            font-family: 'Poppins', sans-serif; font-size: .96rem; color: var(--ink); background: #fff;
            transition: border-color .2s ease, box-shadow .2s ease; outline: none;
        }
        .ph-blog .blog-search input:focus { border-color: var(--o); box-shadow: 0 0 0 4px rgba(242,101,34,.10); }
        .ph-blog .blog-search .ico { position: absolute; left: 1.05rem; top: 50%; transform: translateY(-50%); color: var(--o); font-size: .95rem; }
        .ph-blog .blog-search .clr { position: absolute; right: 1.05rem; top: 50%; transform: translateY(-50%); color: #b6bcc6; cursor: pointer; }
        .ph-blog .blog-search .clr:hover { color: var(--o); }

        .ph-blog .cat-chips { display: flex; flex-wrap: wrap; gap: .5rem; justify-content: center; }
        .ph-blog .cat-chip {
            border: 1.5px solid var(--line); background: #fff; color: var(--muted); font-family: 'Poppins', sans-serif;
            font-weight: 600; font-size: .85rem; padding: .42rem 1.05rem; border-radius: 999px; cursor: pointer;
            transition: all .2s ease; letter-spacing: .01em;
        }
        .ph-blog .cat-chip:hover { border-color: var(--o); color: var(--o); background: var(--soft); }
        .ph-blog .cat-chip.active { background: var(--grad); border-color: transparent; color: #fff; box-shadow: 0 8px 18px rgba(242,101,34,.28); }

        /* Featured */
        .ph-blog .feat {
            display: grid; grid-template-columns: 1.05fr .95fr; gap: 0; align-items: center;
            background: linear-gradient(135deg, #fffaf4 0%, #fff4e8 100%); border-radius: 22px;
            overflow: hidden; border: 1px solid var(--line); box-shadow: 0 14px 40px rgba(35,39,47,.06);
            margin-bottom: 46px; text-decoration: none; transition: transform .28s ease, box-shadow .28s ease;
        }
        .ph-blog .feat:hover { transform: translateY(-4px); box-shadow: 0 24px 54px rgba(242,101,34,.15); }
        .ph-blog .feat .thumb { position: relative; aspect-ratio: 16/9; overflow: hidden; background: var(--ph-grad-soft, linear-gradient(135deg,#fff5e9,#fff9f3)); }
        .ph-blog .feat .thumb img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .ph-blog .feat .thumb .fb { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #f0a35f; font-size: 3.2rem; }
        .ph-blog .feat .body {
            position: relative; padding: 2.6rem 2.3rem; display: flex; flex-direction: column; justify-content: center;
            overflow: hidden;
        }
        .ph-blog .feat .body::before { content: ""; position: absolute; right: -70px; bottom: -70px; width: 230px; height: 230px; border-radius: 50%; background: radial-gradient(circle, rgba(251,169,25,.18), transparent 68%); pointer-events: none; }
        .ph-blog .feat .body::after { content: ""; position: absolute; left: -40px; top: -40px; width: 130px; height: 130px; border-radius: 50%; background: radial-gradient(circle, rgba(242,101,34,.08), transparent 70%); pointer-events: none; }
        .ph-blog .feat .body > * { position: relative; z-index: 1; }
        .ph-blog .feat .tag {
            display: inline-flex; align-items: center; gap: .4rem; align-self: flex-start; background: var(--soft);
            color: var(--o); font-weight: 700; font-size: .72rem; padding: .34rem .85rem; border-radius: 999px;
            margin-bottom: 1.1rem; text-transform: uppercase; letter-spacing: .06em;
        }
        .ph-blog .feat .body h2 {
            font-family: 'Poppins', sans-serif; font-weight: 800; font-size: clamp(1.5rem, 2.6vw, 2.15rem);
            color: var(--ink); line-height: 1.22; letter-spacing: -.01em; margin-bottom: .85rem;
        }
        .ph-blog .feat .body p { color: var(--muted); font-size: 1rem; line-height: 1.7; margin-bottom: 1.5rem; }
        .ph-blog .feat .meta { display: flex; gap: 1.1rem; flex-wrap: wrap; font-size: .82rem; color: var(--muted); margin-bottom: 1.1rem; }
        .ph-blog .feat .meta i { color: var(--o); }
        .ph-blog .feat .read { align-self: flex-start; color: var(--o); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .95rem; display: inline-flex; align-items: center; gap: .5rem; }
        .ph-blog .feat:hover .read i { transform: translateX(4px); }
        .ph-blog .feat .read i { transition: transform .2s ease; }

        /* Cards */
        .ph-blog .card-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }
        .ph-blog .bcard {
            display: flex; flex-direction: column; height: 100%; background: #fff; border-radius: 18px; overflow: hidden;
            border: 1px solid var(--line); box-shadow: 0 10px 30px rgba(35,39,47,.05); text-decoration: none;
            transition: transform .26s ease, box-shadow .26s ease, border-color .26s ease;
        }
        .ph-blog .bcard:hover { transform: translateY(-6px); box-shadow: 0 22px 44px rgba(242,101,34,.14); border-color: rgba(242,101,34,.28); }
        .ph-blog .bcard .thumb { position: relative; aspect-ratio: 16/9; overflow: hidden; background: var(--ph-grad-soft, linear-gradient(135deg,#fff5e9,#fff9f3)); }
        .ph-blog .bcard .thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
        .ph-blog .bcard:hover .thumb img { transform: scale(1.05); }
        .ph-blog .bcard .thumb .fb { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #f0a35f; font-size: 2.2rem; }
        .ph-blog .bcard .cat-badge {
            position: absolute; top: .8rem; left: .8rem; background: rgba(255,255,255,.94); color: var(--o);
            font-weight: 700; font-size: .68rem; padding: .3rem .7rem; border-radius: 999px; text-transform: uppercase;
            letter-spacing: .05em;
        }
        .ph-blog .bcard .body { padding: 1.3rem 1.35rem 1.4rem; display: flex; flex-direction: column; flex-grow: 1; }
        .ph-blog .bcard .meta { font-size: .78rem; color: var(--muted); margin-bottom: .6rem; display: flex; gap: .9rem; flex-wrap: wrap; }
        .ph-blog .bcard .meta i { color: var(--o); }
        .ph-blog .bcard h3 { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.12rem; color: var(--ink); line-height: 1.4; letter-spacing: -.005em; margin-bottom: .55rem; }
        .ph-blog .bcard p { color: var(--muted); font-size: .9rem; line-height: 1.65; margin-bottom: 1.05rem; flex-grow: 1; }
        .ph-blog .bcard .more { color: var(--o); font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .86rem; display: inline-flex; align-items: center; gap: .4rem; }
        .ph-blog .bcard:hover .more i { transform: translateX(4px); }
        .ph-blog .bcard .more i { transition: transform .2s ease; }

        /* Empty state — pola sama dengan halaman Bundling (.bdl-empty / .be-*),
           prefix ble- dipakai agar tidak bentrok dengan utility .bg-* Bootstrap. */
        .blg-empty { text-align: center; padding: 30px 16px 20px; max-width: 480px; margin: 0 auto; }
        .blg-empty-art { margin-bottom: 6px; }
        .blg-empty-art svg { width: 260px; max-width: 82%; height: auto; overflow: visible; }
        .blg-empty-title { font-family: 'Poppins', sans-serif; font-weight: 800; color: var(--ink); font-size: 1.35rem; margin: 4px 0 6px; }
        .blg-empty-sub { color: var(--muted); font-size: .95rem; line-height: 1.6; margin: 0 auto 18px; max-width: 400px; }
        .blg-empty-btn { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #fba919, #f26522); color: #fff; font-weight: 700; padding: .7rem 1.4rem; border-radius: 12px; box-shadow: 0 8px 20px rgba(242, 101, 34, .28); text-decoration: none; border: 0; cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; }
        .blg-empty-btn:hover { color: #fff; transform: translateY(-2px); filter: brightness(1.04); box-shadow: 0 10px 24px rgba(242, 101, 34, .36); }

        .ble-book { animation: ble-bob 3.4s ease-in-out infinite; }
        .ble-pen { animation: ble-penfloat 3.4s ease-in-out infinite; }
        .ble-glow, .ble-shadow, .ble-spark, .ble-book, .ble-pen { transform-box: fill-box; transform-origin: center; }
        .ble-glow { animation: ble-glowpulse 3.4s ease-in-out infinite; }
        .ble-shadow { animation: ble-shadowpulse 3.4s ease-in-out infinite; }
        .ble-spark { animation: ble-twinkle 2s ease-in-out infinite; }
        .ble-spark.s2 { animation-delay: .5s; }
        .ble-spark.s3 { animation-delay: 1s; }
        .ble-spark.s4 { animation-delay: 1.4s; }
        @keyframes ble-bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @keyframes ble-penfloat { 0%, 100% { transform: translateY(-4px) rotate(0deg); } 50% { transform: translateY(-12px) rotate(-8deg); } }
        @keyframes ble-glowpulse { 0%, 100% { opacity: .45; transform: scale(1); } 50% { opacity: .75; transform: scale(1.08); } }
        @keyframes ble-shadowpulse { 0%, 100% { opacity: .16; transform: scaleX(1); } 50% { opacity: .09; transform: scaleX(.82); } }
        @keyframes ble-twinkle { 0%, 100% { opacity: .25; transform: scale(.7); } 50% { opacity: 1; transform: scale(1.15); } }
        @media (prefers-reduced-motion: reduce) {
            .ble-book, .ble-pen, .ble-glow, .ble-shadow, .ble-spark { animation: none !important; }
        }

        @media (max-width: 991.98px) { .ph-blog .card-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 767.98px) {
            .ph-blog .feat { grid-template-columns: 1fr; }
            .ph-blog .feat .body { padding: 1.8rem 1.5rem; }
            .ph-blog .card-grid { grid-template-columns: 1fr; }
        }
    </style>

    {{-- Header (seragam dengan halaman About) --}}
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-journal-richtext"></i> Blog</span>
                <h1>Wawasan &amp; Panduan Digital</h1>
                <p>Tips, panduan, dan info terbaru seputar akun premium, tools AI, &amp; keamanan digital.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li class="current">Blog</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="py-4 py-lg-5">
        <div class="container" style="max-width: 1140px;">
            {{-- Toolbar --}}
            <div class="blog-toolbar">
                <div class="blog-search">
                    <i class="bi bi-search ico"></i>
                    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari artikel...">
                    @if ($search)
                    <i class="bi bi-x-circle-fill clr" wire:click="$set('search', '')"></i>
                    @endif
                </div>
                @if ($categories->isNotEmpty())
                <div class="cat-chips">
                    <span class="cat-chip {{ $category === '' ? 'active' : '' }}" wire:click="$set('category', '')">Semua</span>
                    @foreach ($categories as $cat)
                    <span class="cat-chip {{ $category === $cat ? 'active' : '' }}" wire:click="filterCategory(@js($cat))">{{ $cat }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Featured --}}
            @if ($featured)
            <a href="{{ route('blog.show', $featured->slug) }}" wire:navigate class="feat">
                <div class="thumb">
                    @if ($featured->cover && \Storage::disk('public')->exists('img/blog/' . $featured->cover))
                        <img src="{{ asset('storage/img/blog/' . $featured->cover) }}" alt="{{ $featured->title }}" loading="lazy" decoding="async">
                    @else
                        <div class="fb"><i class="bi bi-journal-text"></i></div>
                    @endif
                </div>
                <div class="body">
                    <span class="tag"><i class="bi bi-star-fill"></i> Artikel Terbaru</span>
                    <h2>{{ $featured->title }}</h2>
                    <div class="meta">
                        @if ($featured->category)<span><i class="bi bi-tag me-1"></i>{{ $featured->category }}</span>@endif
                        <span><i class="bi bi-calendar3 me-1"></i>{{ optional($featured->published_at ?? $featured->created_at)->translatedFormat('d M Y') }}</span>
                        <span><i class="bi bi-clock me-1"></i>{{ $featured->readingMinutes() }} mnt baca</span>
                    </div>
                    <p>{{ $featured->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($featured->body), 170) }}</p>
                    <span class="read">Baca Selengkapnya <i class="bi bi-arrow-right"></i></span>
                </div>
            </a>
            @endif

            {{-- Grid --}}
            @if ($posts->isEmpty())
                {{-- Empty state bervektor & beranimasi, seragam dengan halaman
                     Bundling — menggantikan kotak ikon datar. --}}
                <div class="blg-empty">
                    <div class="blg-empty-art">
                        <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                            aria-label="Ilustrasi buku dan pena">
                            <defs>
                                <radialGradient id="bleGlow" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                    <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                </radialGradient>
                                <linearGradient id="bleCover" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#fbc25a" />
                                    <stop offset="100%" stop-color="#f26522" />
                                </linearGradient>
                                <linearGradient id="bleSpine" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#f7a23e" />
                                    <stop offset="100%" stop-color="#e15a18" />
                                </linearGradient>
                            </defs>

                            <ellipse class="ble-glow" cx="120" cy="106" rx="80" ry="80" fill="url(#bleGlow)" />
                            <ellipse class="ble-shadow" cx="120" cy="180" rx="58" ry="8" fill="#e15a18" />

                            <g transform="translate(48,66)"><path class="ble-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                            <g transform="translate(196,86)"><path class="ble-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                            <g transform="translate(190,144)"><path class="ble-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                            <g transform="translate(54,148)"><path class="ble-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                            {{-- Buku terbuka --}}
                            <g class="ble-book">
                                <path d="M120,74 C104,64 84,62 66,66 L66,150 C84,146 104,148 120,158 Z" fill="#ffe9d0" />
                                <path d="M120,74 C136,64 156,62 174,66 L174,150 C156,146 136,148 120,158 Z" fill="#fff5e8" />
                                <path d="M66,66 C84,62 104,64 120,74 L120,158 C104,148 84,146 66,150 Z" fill="none"
                                    stroke="url(#bleSpine)" stroke-width="4" stroke-linejoin="round" />
                                <path d="M174,66 C156,62 136,64 120,74 L120,158 C136,148 156,146 174,150 Z" fill="none"
                                    stroke="url(#bleCover)" stroke-width="4" stroke-linejoin="round" />
                                <path d="M120,74 L120,158" stroke="#e15a18" stroke-width="3" stroke-linecap="round" />
                                <path d="M80,88 H108 M80,102 H104 M80,116 H110" stroke="#f7a23e" stroke-opacity=".55"
                                    stroke-width="3.5" stroke-linecap="round" />
                                <path d="M132,88 H160 M136,102 H160 M132,116 H158" stroke="#f4772b" stroke-opacity=".5"
                                    stroke-width="3.5" stroke-linecap="round" />
                            </g>

                            {{-- Pena melayang --}}
                            <g class="ble-pen">
                                <path d="M176,38 L192,54 L162,70 L156,58 Z" fill="url(#bleCover)" />
                                <path d="M156,58 L162,70 L150,74 Z" fill="#ffe9d0" />
                            </g>
                        </svg>
                    </div>

                    @if ($search || $category)
                        <h3 class="blg-empty-title">Artikel tidak ditemukan</h3>
                        <p class="blg-empty-sub">Tidak ada artikel yang cocok dengan pencarian atau kategori itu.
                            Coba kata kunci lain, ya.</p>
                        <button type="button" class="blg-empty-btn" wire:click="resetFilter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Pencarian
                        </button>
                    @else
                        <h3 class="blg-empty-title">Belum ada artikel</h3>
                        <p class="blg-empty-sub">Tulisan menarik dari kami sedang disiapkan. Sementara itu,
                            lihat-lihat produk kami dulu, yuk!</p>
                        <a href="{{ url('/shop') }}" class="blg-empty-btn">
                            <i class="bi bi-bag"></i> Lihat Produk
                        </a>
                    @endif
                </div>
            @else
                <div class="card-grid">
                    @foreach ($posts as $post)
                    @if (! ($featured && $post->id === $featured->id))
                    <a href="{{ route('blog.show', $post->slug) }}" wire:navigate class="bcard">
                        <div class="thumb">
                            @if ($post->cover && \Storage::disk('public')->exists('img/blog/' . $post->cover))
                                <img src="{{ asset('storage/img/blog/' . $post->cover) }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
                            @else
                                <div class="fb"><i class="bi bi-journal-text"></i></div>
                            @endif
                            @if ($post->category)<span class="cat-badge">{{ $post->category }}</span>@endif
                        </div>
                        <div class="body">
                            <div class="meta">
                                <span><i class="bi bi-calendar3 me-1"></i>{{ optional($post->published_at ?? $post->created_at)->translatedFormat('d M Y') }}</span>
                                <span><i class="bi bi-clock me-1"></i>{{ $post->readingMinutes() }} mnt</span>
                            </div>
                            <h3>{{ $post->title }}</h3>
                            <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 115) }}</p>
                            <span class="more">Baca artikel <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                    @endif
                    @endforeach
                </div>

                <div class="mt-5 d-flex justify-content-center">
                    {{ $posts->links('vendor.pagination') }}
                </div>
            @endif
        </div>
    </section>
</div>
