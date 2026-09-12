@section('title')
    Kebijakan Privasi | Phoenix Digital
@endsection

<main class="main prv-page">
    <style>
        /* ===== Halaman Kebijakan Privasi =====
           Bahasa visual sama dengan Beranda, Shop, Bundling, Layanan, Tentang,
           Kontak, FAQ, Member, dan Syarat & Ketentuan: kartu judul bersama (.ph-page-title), kartu
           putih bersudut 18px dengan warna per kartu (--c), ubin ikon berwarna.
           Kelas prv-*: gaya .legal-*/.lg-* dipakai bersama halaman lain dan
           sebagiannya beku di server, jadi halaman ini berdiri sendiri. */
        .prv-page { --prv-ink: #1c1f26; --prv-muted: #6b7280; --prv-line: #eceff3; --prv-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .prv-sec { padding: 22px 0 64px; }

        /* Ubin ikon — glif tunggal selalu display:block + line-height:1 */
        .prv-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 14px; font-size: 1.15rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 85%, #0f172a);
            transition: background .2s ease, color .2s ease;
        }
        .prv-ubin.is-padat {
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff)); color: #fff;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .prv-ubin.is-kecil { width: 34px; height: 34px; border-radius: 11px; font-size: .95rem; }
        .prv-ubin i.bi, .prv-ubin i.bi::before { display: block; line-height: 1; }

        /* ===== Ringkasan tiga poin terpenting ===== */
        .prv-ringkas { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 26px; }
        .prv-ringkas-item {
            position: relative; display: flex; align-items: center; gap: 13px; padding: 16px 18px; overflow: hidden;
            background: #fff; border: 1px solid var(--prv-line); border-radius: 16px;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .prv-ringkas-item::before {
            content: ""; position: absolute; top: -34px; right: -34px; width: 96px; height: 96px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent); transition: transform .3s ease;
        }
        .prv-ringkas-item:hover { transform: translateY(-3px); border-color: color-mix(in srgb, var(--c) 35%, #fff); box-shadow: 0 12px 28px color-mix(in srgb, var(--c) 18%, transparent); }
        .prv-ringkas-item:hover::before { transform: scale(1.35); }
        .prv-ringkas-item > * { position: relative; }
        .prv-ringkas-item b { display: block; font-family: var(--prv-font); font-weight: 800; font-size: .96rem; color: var(--prv-ink); line-height: 1.3; }
        .prv-ringkas-item small { display: block; margin-top: 3px; font-size: .8rem; color: var(--prv-muted); line-height: 1.5; }

        .prv-grid { display: grid; grid-template-columns: minmax(0, .8fr) minmax(0, 2fr); gap: 24px; align-items: start; }

        /* ===== Daftar isi ===== */
        .prv-toc { position: sticky; top: 96px; }
        .prv-toc-kartu { padding: 20px 18px; background: #fff; border: 1px solid var(--prv-line); border-radius: 20px; box-shadow: 0 18px 40px -34px rgba(15, 23, 42, .5); }
        .prv-toc-kepala { display: flex; align-items: center; gap: 11px; margin-bottom: 14px; }
        .prv-toc-kepala b { font-family: var(--prv-font); font-weight: 800; font-size: 1rem; color: var(--prv-ink); }
        .prv-toc-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 3px; }
        .prv-toc-list a {
            display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 11px;
            color: #475569; font-size: .84rem; line-height: 1.4; text-decoration: none;
            transition: background .18s ease, color .18s ease;
        }
        .prv-toc-list a:hover { background: color-mix(in srgb, var(--c) 8%, #fff); color: color-mix(in srgb, var(--c) 80%, #0f172a); }
        .prv-toc-no {
            flex-shrink: 0; width: 24px; height: 24px; border-radius: 8px; display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 13%, #fff); color: color-mix(in srgb, var(--c) 70%, #0f172a);
            font-size: .73rem; font-weight: 800; font-variant-numeric: tabular-nums;
        }
        .prv-diperbarui {
            display: flex; flex-direction: column; gap: 2px; margin-top: 14px; padding-top: 13px;
            border-top: 1px dashed #e8ecf2; font-size: .78rem; color: var(--prv-muted);
        }
        .prv-diperbarui b { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }

        /* ===== Kartu pasal ===== */
        .prv-list { display: grid; gap: 14px; }
        .prv-pasal {
            position: relative; overflow: hidden; padding: 22px 24px;
            background: #fff; border: 1px solid var(--prv-line); border-radius: 18px; scroll-margin-top: 100px;
            transition: border-color .22s ease, box-shadow .22s ease;
        }
        .prv-pasal::before {
            content: ""; position: absolute; top: -38px; right: -38px; width: 108px; height: 108px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 9%, transparent); transition: transform .3s ease;
        }
        .prv-pasal:hover { border-color: color-mix(in srgb, var(--c) 32%, #fff); box-shadow: 0 16px 32px -26px color-mix(in srgb, var(--c) 85%, transparent); }
        .prv-pasal:hover::before { transform: scale(1.3); }
        /* Pasal yang paling sering jadi sumber salah paham (batas perangkat &
           refund) diberi latar berwarna — sama seperti .legal-highlight dulu,
           tetapi mengikuti warna pasalnya sendiri. */
        .prv-pasal.is-sorot {
            border-color: color-mix(in srgb, var(--c) 30%, #fff);
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 8%, #fff) 0%, #fff 70%);
        }
        .prv-pasal > * { position: relative; }
        .prv-kepala { display: flex; align-items: center; gap: 13px; margin-bottom: 12px; }
        .prv-no {
            flex-shrink: 0; display: flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 10%, #fff); border: 2px solid color-mix(in srgb, var(--c) 30%, #fff);
            color: color-mix(in srgb, var(--c) 62%, #0f172a);
            font-family: var(--prv-font); font-weight: 800; font-size: .88rem; line-height: 1; font-variant-numeric: tabular-nums;
        }
        .prv-judul { margin: 0; font-family: var(--prv-font); font-weight: 800; font-size: 1.05rem; line-height: 1.3; letter-spacing: -.015em; color: var(--prv-ink); }
        .prv-pasal p { margin: 0; font-size: .9rem; line-height: 1.75; color: #475569; }
        .prv-pasal p + p { margin-top: 10px; }
        .prv-pasal p b { color: #334155; }
        .prv-pasal a { color: #c2410c; font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
        .prv-pasal a:hover { color: #9a3412; }
        .prv-lencana {
            display: inline-flex; align-items: center; gap: 6px; height: 24px; padding: 0 10px; border-radius: 99px;
            background: color-mix(in srgb, var(--c) 14%, #fff); color: color-mix(in srgb, var(--c) 60%, #0f172a);
            font-size: .7rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap;
        }
        .prv-lencana i.bi, .prv-lencana i.bi::before { display: block; line-height: 1; font-size: .72rem; }

        /* ===== Ajakan penutup ===== */
        .prv-cta {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px 24px;
            margin-top: 16px; padding: 22px 24px; border-radius: 20px;
            border: 1px solid color-mix(in srgb, var(--c) 22%, #eceff4);
            background: linear-gradient(135deg, color-mix(in srgb, var(--c) 9%, #fff) 0%, #fff 68%);
        }
        .prv-cta-kiri { display: flex; align-items: center; gap: 14px; flex: 1 1 320px; min-width: 0; }
        .prv-cta-teks b { display: block; font-family: var(--prv-font); font-weight: 800; font-size: 1.05rem; color: var(--prv-ink); }
        .prv-cta-teks span { display: block; margin-top: 2px; font-size: .87rem; color: var(--prv-muted); line-height: 1.55; }
        .prv-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 20px;
            border-radius: 13px; background: #16a34a; color: #fff; font-weight: 700; font-size: .9rem; text-decoration: none;
            white-space: nowrap; box-shadow: 0 12px 22px -12px rgba(22, 163, 74, .85);
            transition: background .18s ease, transform .18s ease;
        }
        .prv-btn:hover { background: #15803d; color: #fff; transform: translateY(-1px); }
        .prv-btn i.bi, .prv-btn i.bi::before { display: block; line-height: 1; font-size: 1.05rem; }

        @media (max-width: 991.98px) {
            .prv-grid { grid-template-columns: minmax(0, 1fr); }
            .prv-toc { position: static; }
            .prv-toc-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .prv-ringkas { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .prv-toc-list { grid-template-columns: minmax(0, 1fr); }
            .prv-pasal { padding: 18px 16px; }
            .prv-cta { padding: 18px 16px; }
            .prv-cta .prv-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .prv-ringkas-item, .prv-ringkas-item:hover, .prv-btn:hover { transform: none; transition: none; }
            .prv-pasal::before, .prv-ringkas-item::before { transition: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-shield-lock"></i> Legal</span>
                <h1>Kebijakan Privasi</h1>
                <p>Kami menghargai privasi Anda. Berikut cara Phoenix Digital mengumpulkan, memakai, dan menjaga data Anda.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li class="current">Kebijakan Privasi</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="prv-sec">
        <div class="container">
            {{-- Tiga janji utama, ditaruh di atas supaya terbaca lebih dulu --}}
            <div class="prv-ringkas">
                @foreach ($ringkas as $r)
                    <div class="prv-ringkas-item" style="--c: {{ $r['warna'] }}">
                        <span class="prv-ubin"><i class="bi {{ $r['ikon'] }}"></i></span>
                        <span><b>{{ $r['judul'] }}</b><small>{{ $r['ket'] }}</small></span>
                    </div>
                @endforeach
            </div>

            <div class="prv-grid">
                {{-- Daftar isi --}}
                <aside class="prv-toc">
                    <div class="prv-toc-kartu" style="--c: #f26522">
                        <div class="prv-toc-kepala">
                            <span class="prv-ubin is-kecil"><i class="bi bi-list-ul"></i></span>
                            <b>Daftar Isi</b>
                        </div>
                        <ul class="prv-toc-list">
                            @foreach ($pasal as $i => $p)
                                <li>
                                    <a href="#pv-{{ $i + 1 }}" style="--c: {{ $p['warna'] }}">
                                        <span class="prv-toc-no">{{ $i + 1 }}</span>
                                        <span>{{ $p['judul'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        {{-- Tanggal perubahan ISI terakhir menurut riwayat git, bukan tanggal
                             penyuntingan tata letak. Halaman hukum yang mengaku "diperbarui"
                             padahal hanya gayanya yang berubah menyesatkan pembacanya. --}}
                        <span class="prv-diperbarui">
                            <b>Terakhir diperbarui</b>
                            <span>{{ \App\Livewire\Pages\Public\Legal\PrivacyPage::DIPERBARUI }}</span>
                        </span>
                    </div>
                </aside>

                {{-- Pasal --}}
                <div class="prv-list">
                    @foreach ($pasal as $i => $p)
                        <article class="prv-pasal {{ $p['sorot'] ? 'is-sorot' : '' }}" id="pv-{{ $i + 1 }}" style="--c: {{ $p['warna'] }}">
                            <div class="prv-kepala">
                                <span class="prv-no">{{ $i + 1 }}</span>
                                <span class="prv-ubin is-kecil"><i class="bi {{ $p['ikon'] }}"></i></span>
                                <h2 class="prv-judul">{{ $p['judul'] }}</h2>
                                @if ($p['sorot'])
                                    <span class="prv-lencana"><i class="bi bi-exclamation-triangle-fill"></i> Penting</span>
                                @endif
                            </div>
                            @foreach ($p['isi'] as $paragraf)
                                <p>{!! $paragraf !!}</p>
                            @endforeach
                        </article>
                    @endforeach

                    <div class="prv-cta" style="--c: #16a34a">
                        <div class="prv-cta-kiri">
                            <span class="prv-ubin is-padat"><i class="bi bi-whatsapp"></i></span>
                            <div class="prv-cta-teks">
                                <b>Ada pertanyaan soal data Anda?</b>
                                <span>Ingin mengubah atau menghapus data Anda? Tulis ke admin kami, akan kami bantu.</span>
                            </div>
                        </div>
                        <a class="prv-btn" href="{{ $waBantuan }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i> 0895-0596-7995
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            (function () {
                // Lompat ke pasal lewat scrollIntoView, bukan mengandalkan tanda
                // pagar: di halaman ini navigasi tanda pagar tidak menggulir sama
                // sekali — membuka /privacy#pv-5 langsung pun berhenti di puncak
                // halaman. scrollIntoView terbukti jalan, dan scroll-margin-top
                // pada kartunya menjaga jarak dari kepala halaman yang menempel.
                // Tautan dalam dari luar (mis. /privacy#pv-5) juga berhenti di
                // puncak halaman, jadi posisinya diperbaiki setelah halaman siap.
                function keTujuanAwal() {
                    var t0 = (location.hash || '').slice(1);
                    var el0 = t0 && document.getElementById(t0);
                    if (el0) el0.scrollIntoView({ block: 'start' });
                }
                if (document.readyState === 'complete') keTujuanAwal();
                else window.addEventListener('load', keTujuanAwal, { once: true });

                document.querySelectorAll('.prv-toc-list a').forEach(function (a) {
                    a.addEventListener('click', function (e) {
                        var tujuan = (a.getAttribute('href') || '').slice(1);
                        var el = tujuan && document.getElementById(tujuan);
                        if (!el) return;
                        e.preventDefault();
                        el.scrollIntoView({ block: 'start', behavior: 'smooth' });
                        if (history.replaceState) history.replaceState(null, '', '#' + tujuan);
                    });
                });
            })();
        </script>
    @endpush
</main>
