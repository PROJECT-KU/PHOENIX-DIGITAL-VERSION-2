@section('title')
    FAQ — Pertanyaan Umum | Phoenix Digital
@endsection

<main class="main fq-page">
    <style>
        /* ===== Halaman FAQ =====
           Bahasa visual sama dengan Beranda, Shop, Bundling, Layanan, Tentang
           Kami, dan Kontak: kartu judul bersama (.ph-page-title), kartu putih
           bersudut 18px dengan warna per kartu (--c), ubin ikon berwarna.
           Kelas fq-*: gaya .legal-*/.lg-* dipakai bersama tiga halaman lain
           (Syarat, Privasi, Member) dan sebagiannya beku di server — jadi
           halaman ini berdiri sendiri tanpa mengubah gaya bersama itu. */
        .fq-page { --fq-ink: #1c1f26; --fq-muted: #64748b; --fq-line: #eceff4; --fq-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .fq-section { padding: 18px 0 64px; }

        /* Ubin ikon — glif tunggal selalu display:block + line-height:1 */
        .fq-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 42px; height: 42px; border-radius: 13px; font-size: 1.1rem;
            /* Warna ikon digelapkan: var(--c) polos di atas latar mudanya bisa
               hanya 2,9 : 1 untuk warna hijau — di bawah batas 3 : 1 untuk ikon. */
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 85%, #0f172a);
            transition: background .18s ease, color .18s ease;
        }
        .fq-ubin i.bi, .fq-ubin i.bi::before { display: block; line-height: 1; }

        .fq-grid { display: grid; grid-template-columns: minmax(0, .82fr) minmax(0, 2fr); gap: 24px; align-items: start; }

        /* ===== Daftar isi ===== */
        .fq-toc { position: sticky; top: 96px; }
        .fq-toc-kartu { padding: 20px 18px; background: #fff; border: 1px solid var(--fq-line); border-radius: 20px; box-shadow: 0 18px 40px -34px rgba(15, 23, 42, .5); }
        .fq-toc-kepala { display: flex; align-items: center; gap: 11px; margin-bottom: 14px; }
        .fq-toc-kepala b { font-family: var(--fq-font); font-weight: 800; font-size: 1rem; color: var(--fq-ink); }
        .fq-toc-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; counter-reset: fqno; }
        .fq-toc-list a {
            display: flex; align-items: flex-start; gap: 10px; padding: 9px 10px; border-radius: 11px;
            color: #475569; font-size: .85rem; line-height: 1.4; text-decoration: none;
            transition: background .18s ease, color .18s ease;
        }
        .fq-toc-list a:hover { background: color-mix(in srgb, var(--c) 8%, #fff); color: var(--c); }
        .fq-toc-no {
            flex-shrink: 0; width: 22px; height: 22px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 14%, #fff); color: color-mix(in srgb, var(--c) 80%, #0f172a); font-size: .72rem; font-weight: 800;
        }
        .fq-diperbarui {
            display: flex; flex-direction: column; gap: 2px; margin-top: 14px; padding-top: 13px;
            border-top: 1px dashed #e8ecf2; font-size: .78rem; color: var(--fq-muted);
        }
        .fq-diperbarui b { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }

        /* ===== Daftar pertanyaan ===== */
        .fq-alat { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .fq-jumlah { font-size: .85rem; color: var(--fq-muted); }
        .fq-jumlah b { color: var(--fq-ink); font-weight: 800; }
        .fq-toggle {
            display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 99px;
            background: #fff; border: 1px solid var(--fq-line); color: #475569; font-size: .8rem; font-weight: 700; cursor: pointer;
            transition: border-color .18s ease, color .18s ease;
        }
        .fq-toggle:hover { border-color: #f26522; color: #c2410c; }
        .fq-toggle i.bi, .fq-toggle i.bi::before { display: block; line-height: 1; font-size: .85rem; }

        .fq-list { display: grid; gap: 12px; }
        .fq-item {
            background: #fff; border: 1px solid var(--fq-line); border-radius: 18px; overflow: hidden;
            scroll-margin-top: 100px; transition: border-color .2s ease, box-shadow .2s ease;
        }
        .fq-item:hover { border-color: color-mix(in srgb, var(--c) 32%, #fff); }
        .fq-item[open] {
            border-color: color-mix(in srgb, var(--c) 38%, #fff);
            box-shadow: 0 18px 36px -30px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .fq-tanya {
            display: flex; align-items: center; gap: 13px; padding: 16px 18px; cursor: pointer; list-style: none;
            font-family: var(--fq-font); font-weight: 700; font-size: .97rem; line-height: 1.4; color: var(--fq-ink);
        }
        .fq-tanya::-webkit-details-marker { display: none; }
        .fq-tanya:hover .fq-ubin, .fq-item[open] .fq-ubin { background: var(--c); color: #fff; }
        .fq-tanya-teks { flex: 1 1 auto; min-width: 0; }
        .fq-panah {
            flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            background: #f8fafc; color: #64748b; font-size: .78rem; transition: transform .25s ease, background .2s ease, color .2s ease;
        }
        .fq-panah i.bi, .fq-panah i.bi::before { display: block; line-height: 1; }
        .fq-item[open] .fq-panah { transform: rotate(180deg); background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
        .fq-jawab { padding: 0 18px 18px 73px; font-size: .9rem; line-height: 1.75; color: #475569; }
        .fq-jawab b { color: #334155; }
        .fq-jawab a { color: #c2410c; font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
        .fq-jawab a:hover { color: #9a3412; }

        /* ===== Ajakan penutup ===== */
        .fq-cta {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px 24px;
            margin-top: 18px; padding: 22px 24px; border-radius: 20px;
            border: 1px solid color-mix(in srgb, var(--c) 22%, #eceff4);
            background: linear-gradient(135deg, color-mix(in srgb, var(--c) 9%, #fff) 0%, #fff 68%);
        }
        .fq-cta-kiri { display: flex; align-items: center; gap: 14px; flex: 1 1 320px; min-width: 0; }
        .fq-cta .fq-ubin { background: var(--c); color: #fff; }
        /* Dipersempit ke .fq-cta-teks: ditulis sebagai .fq-cta span, aturan ini
           ikut mengenai ubin ikon dan mematahkan display:flex-nya, sehingga
           glifnya melompat 14px ke atas. */
        .fq-cta-teks b { display: block; font-family: var(--fq-font); font-weight: 800; font-size: 1.05rem; color: var(--fq-ink); }
        .fq-cta-teks span { display: block; margin-top: 2px; font-size: .87rem; color: var(--fq-muted); line-height: 1.55; }
        .fq-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 20px;
            border-radius: 13px; background: #16a34a; color: #fff; font-weight: 700; font-size: .9rem; text-decoration: none;
            white-space: nowrap; box-shadow: 0 12px 22px -12px rgba(22, 163, 74, .85);
            transition: background .18s ease, transform .18s ease;
        }
        .fq-btn:hover { background: #15803d; color: #fff; transform: translateY(-1px); }
        .fq-btn i.bi, .fq-btn i.bi::before { display: block; line-height: 1; font-size: 1.05rem; }

        @media (max-width: 991.98px) {
            .fq-grid { grid-template-columns: minmax(0, 1fr); }
            .fq-toc { position: static; }
            .fq-toc-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .fq-toc-list { grid-template-columns: minmax(0, 1fr); }
            .fq-tanya { padding: 14px; gap: 11px; font-size: .92rem; }
            .fq-jawab { padding: 0 14px 16px; }
            .fq-cta { padding: 18px 16px; }
            .fq-cta .fq-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .fq-panah, .fq-btn:hover { transition: none; transform: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-patch-question"></i> Bantuan</span>
                <h1>Pertanyaan Umum</h1>
                <p>Jawaban singkat untuk pertanyaan yang paling sering ditanyakan seputar pemesanan, pembayaran, dan garansi.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li class="current">FAQ</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="fq-section">
        <div class="container">
            <div class="fq-grid">
                {{-- Daftar isi --}}
                <aside class="fq-toc">
                    <div class="fq-toc-kartu" style="--c: #f26522">
                        <div class="fq-toc-kepala">
                            <span class="fq-ubin"><i class="bi bi-list-ul"></i></span>
                            <b>Daftar Isi</b>
                        </div>
                        <ul class="fq-toc-list">
                            @foreach ($daftar as $i => $f)
                                <li>
                                    <a href="#fq-{{ $i + 1 }}" style="--c: {{ $f['warna'] }}">
                                        <span class="fq-toc-no">{{ $i + 1 }}</span>
                                        <span>{{ $f['judul'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <span class="fq-diperbarui">
                            <b>Terakhir diperbarui</b>
                            <span>{{ \App\Livewire\Pages\Public\Legal\FaqPage::DIPERBARUI }}</span>
                        </span>
                    </div>
                </aside>

                {{-- Pertanyaan --}}
                <div>
                    <div class="fq-alat">
                        <span class="fq-jumlah"><b>{{ count($daftar) }} pertanyaan</b> yang paling sering ditanyakan</span>
                        <button type="button" class="fq-toggle" id="fq-buka-semua" data-buka="0">
                            <i class="bi bi-arrows-expand"></i> <span>Buka semua</span>
                        </button>
                    </div>

                    <div class="fq-list">
                        @foreach ($daftar as $i => $f)
                            <details class="fq-item" id="fq-{{ $i + 1 }}" style="--c: {{ $f['warna'] }}" {!! $i === 0 ? 'open' : '' !!}>
                                <summary class="fq-tanya">
                                    <span class="fq-ubin"><i class="bi {{ $f['ikon'] }}"></i></span>
                                    <span class="fq-tanya-teks">{{ $f['tanya'] }}</span>
                                    <span class="fq-panah"><i class="bi bi-chevron-down"></i></span>
                                </summary>
                                <div class="fq-jawab">{!! $f['jawab'] !!}</div>
                            </details>
                        @endforeach
                    </div>

                    <div class="fq-cta" style="--c: #16a34a">
                        <div class="fq-cta-kiri">
                            <span class="fq-ubin"><i class="bi bi-whatsapp"></i></span>
                            <div class="fq-cta-teks">
                                <b>Masih ada pertanyaan?</b>
                                <span>Tanyakan langsung ke admin kami di 0895-0596-7995 — dibalas pada jam operasional.</span>
                            </div>
                        </div>
                        <a class="fq-btn" href="{{ $waTanya }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i> Chat Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    @push('scripts')
            <script>
            (function () {
                // Tautan daftar isi menunjuk ke pertanyaan yang mungkin sedang tertutup:
                // buka dulu yang dituju, baru gulirkan, supaya jawabannya benar-benar terlihat.
                function bukaTarget() {
                    var id = (location.hash || '').replace('#', '');
                    if (!id) return;
                    var el = document.getElementById(id);
                    if (el && el.tagName === 'DETAILS') { el.open = true; el.scrollIntoView({ block: 'start', behavior: 'smooth' }); }
                }
                window.addEventListener('hashchange', bukaTarget);
                window.addEventListener('load', bukaTarget);

                var tombol = document.getElementById('fq-buka-semua');
                if (tombol) {
                    tombol.addEventListener('click', function () {
                        var buka = tombol.dataset.buka !== '1';
                        document.querySelectorAll('.fq-item').forEach(function (d) { d.open = buka; });
                        tombol.dataset.buka = buka ? '1' : '0';
                        tombol.querySelector('span').textContent = buka ? 'Tutup semua' : 'Buka semua';
                        tombol.querySelector('i').className = buka ? 'bi bi-arrows-collapse' : 'bi bi-arrows-expand';
                    });
                }
            })();
            </script>
    @endpush

    {{-- Data terstruktur FAQPage: jawabannya diambil dari daftar yang sama
         dengan yang tampil di atas, jadi tidak mungkin berselisih. --}}
    <script type="application/ld+json">
        {!! json_encode(\App\Livewire\Pages\Public\Legal\FaqPage::dataTerstruktur(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</main>
