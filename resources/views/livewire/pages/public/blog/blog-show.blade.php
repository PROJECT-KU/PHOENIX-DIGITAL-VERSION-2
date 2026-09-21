@section('title')
{{ $post->meta_title ?: $post->title }} | Blog Phoenix Digital
@endsection

<div class="ph-article" style="--kb: {{ $ragam['warna'] }}">
    {{-- Pita pratinjau: hanya muncul untuk admin yang membuka draf atau
         artikel terjadwal. Pengunjung biasa tidak pernah sampai ke sini
         (BlogShow memanggil abort 404). --}}
    @if ($pratinjau)
        <div class="blgd-pratinjau">
            <i class="bi bi-eye-fill"></i>
            <span>
                <b>Pratinjau</b> — artikel ini belum tayang untuk pengunjung.
                Kunjungan Anda tidak ikut dihitung sebagai pembaca.
            </span>
        </div>
        <style>
            .blgd-pratinjau {
                display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
                margin: 0 0 18px; padding: 12px 16px; border-radius: 14px;
                background: #fef3c7; border: 1px solid #fde68a; color: #92400e;
                font-size: .88rem; line-height: 1.5;
            }
            .blgd-pratinjau i { font-size: 1rem; }
    
        /* ===== Tag artikel ===== */
        .ph-article .blgd-tag {
            display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
            margin: 26px 0 0; padding-top: 22px; border-top: 1px solid var(--line);
        }
        .ph-article .blgd-tag-label {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .84rem; font-weight: 700; color: var(--muted);
        }
        .ph-article .blgd-tag-pil {
            padding: 5px 12px; border-radius: 999px; border: 1px solid var(--line);
            background: var(--soft); color: var(--muted); font-size: .84rem; font-weight: 600;
            text-decoration: none; transition: border-color .15s ease, color .15s ease, background .15s ease;
        }
        .ph-article .blgd-tag-pil:hover { border-color: var(--kb); background: #fff; color: var(--ink); }
    </style>
    @endif

    <style>
        .ph-article { --o: var(--ph-orange, #f26522); --a: var(--ph-amber, #fba919); --ink: var(--ph-ink, #23272f);
            --muted: var(--ph-muted, #6b7280); --soft: var(--ph-soft, #fff8f1); --line: var(--ph-line, #f1e6d8);
            --grad: var(--ph-grad, linear-gradient(135deg, #fba919 0%, #f26522 100%));
            --judul: 'Plus Jakarta Sans', 'Poppins', sans-serif; }

        /* Bilah progres baca — warnanya warna kategori artikel (App\Support\RagamBlog). */
        .ph-article .blgd-progres { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 1100; pointer-events: none; }
        .ph-article .blgd-progres span {
            display: block; height: 100%; width: 100%; transform-origin: 0 50%; transform: scaleX(0);
            background: linear-gradient(90deg, var(--kb), var(--o));
        }

        /* ── Kepala: markup baku layouts/guest, diperkaya baris meta ── */
        .ph-article .ph-page-head.blgd-kepala { min-width: 0; }
        .ph-article .ph-page-head.blgd-kepala h1 { line-height: 1.16; max-width: 34ch; }
        .ph-article .blgd-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .ph-article .blgd-meta span {
            display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px 5px 6px; border-radius: 999px;
            background: #fff; border: 1px solid #eef0f4; color: #4b5260; font-size: .8rem; font-weight: 600;
            box-shadow: 0 4px 12px rgba(120,90,60,.05);
        }
        .ph-article .blgd-meta i {
            display: flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%;
            background: color-mix(in srgb, var(--kb) 12%, #fff); color: var(--kb); font-size: .72rem;
        }
        .ph-article .blgd-meta i::before { display: block; line-height: 1; }

        /* ── Badan ──
           .container polos, TANPA max-width: kartu kepala adalah ::before pada
           .container yang menjorok 12px (= padding .container), jadi badan
           otomatis sejajar dengan kepala di semua breakpoint. */
        .ph-article .blgd-badan { padding: 26px 0 64px; }
        .ph-article .blgd-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 28px; align-items: start; }

        .ph-article .blgd-kartu {
            background: #fff; border: 1px solid var(--line); border-radius: 22px; overflow: hidden;
            box-shadow: 0 14px 40px rgba(35,39,47,.06);
        }

        /* Sampul. Berkas sampul ada di server; bila hilang (atau gagal dimuat)
           bidangnya jatuh ke sapuan warna kategori + ubin ikon, bukan lubang. */
        .ph-article .blgd-sampul { position: relative; overflow: hidden; aspect-ratio: 21 / 9; background: linear-gradient(135deg, #fff5e9, #fff9f3); }
        .ph-article .blgd-sampul img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .ph-article .blgd-sampul.is-kosong { background: linear-gradient(160deg, color-mix(in srgb, var(--kb) 15%, #fff) 0%, #fff 82%); }
        .ph-article .blgd-sampul.is-kosong::before {
            content: ""; position: absolute; top: -30%; right: -8%; width: 42%; aspect-ratio: 1; border-radius: 50%;
            background: color-mix(in srgb, var(--kb) 12%, transparent);
        }
        .ph-article .blgd-sampul.is-kosong::after {
            content: ""; position: absolute; left: 0; bottom: 0; width: 40%; height: 80%;
            background-image: radial-gradient(color-mix(in srgb, var(--kb) 30%, transparent) 1.2px, transparent 1.6px);
            background-size: 16px 16px;
            -webkit-mask-image: linear-gradient(35deg, #000 0%, transparent 70%); mask-image: linear-gradient(35deg, #000 0%, transparent 70%);
        }
        .ph-article .blgd-ubin { display: none; position: absolute; inset: 0; z-index: 1; flex-direction: column; align-items: center; justify-content: center; gap: 14px; }
        .ph-article .blgd-sampul.is-kosong .blgd-ubin { display: flex; }
        .ph-article .blgd-ubin i {
            display: flex; align-items: center; justify-content: center; width: 96px; height: 96px; border-radius: 28px;
            font-size: 2.5rem; color: #fff; background: linear-gradient(140deg, var(--kb), color-mix(in srgb, var(--kb) 58%, #fff));
            box-shadow: 0 18px 34px -14px color-mix(in srgb, var(--kb) 85%, transparent);
            animation: blgdMengambang 4.5s ease-in-out infinite;
        }
        .ph-article .blgd-ubin i::before { display: block; line-height: 1; }
        .ph-article .blgd-ubin b {
            padding: .35rem .85rem; border-radius: 999px; background: rgba(255,255,255,.9); color: var(--kb);
            font-family: var(--judul); font-size: .74rem; letter-spacing: .08em; text-transform: uppercase;
            box-shadow: 0 6px 16px rgba(35,39,47,.06);
        }
        @keyframes blgdMengambang { 0%, 100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-8px) rotate(-3deg); } }

        .ph-article .blgd-isi { padding: 34px 44px 38px; }

        .ph-article .blgd-ringkas {
            position: relative; margin-bottom: 28px; padding: 18px 22px 18px 24px; border-radius: 16px;
            background: color-mix(in srgb, var(--kb) 6%, #fff); border: 1px solid color-mix(in srgb, var(--kb) 16%, #fff);
        }
        .ph-article .blgd-ringkas::before { content: ""; position: absolute; left: 0; top: 16px; bottom: 16px; width: 4px; border-radius: 0 4px 4px 0; background: var(--kb); }
        .ph-article .blgd-ringkas-label {
            display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px; color: var(--kb);
            font-family: var(--judul); font-weight: 800; font-size: .72rem; letter-spacing: .1em; text-transform: uppercase;
        }
        .ph-article .blgd-ringkas p { margin: 0; font-family: var(--judul); font-size: 1.1rem; line-height: 1.62; font-weight: 500; color: var(--ink); }

        /* Daftar isi versi layar sempit — terlipat di atas artikel. */
        .ph-article .blgd-isi-hp { margin-bottom: 26px; border: 1px solid var(--line); border-radius: 16px; background: #fffdfb; }
        .ph-article .blgd-isi-hp summary {
            display: flex; align-items: center; gap: 10px; padding: 12px 14px; cursor: pointer; list-style: none;
            font-family: var(--judul); font-weight: 700; color: var(--ink);
        }
        .ph-article .blgd-isi-hp summary::-webkit-details-marker { display: none; }
        .ph-article .blgd-isi-hp summary small { margin-left: auto; color: var(--muted); font-weight: 600; }
        .ph-article .blgd-isi-hp summary .bi-chevron-down { transition: transform .2s ease; color: var(--muted); }
        .ph-article .blgd-isi-hp[open] summary .bi-chevron-down { transform: rotate(180deg); }
        .ph-article .blgd-isi-hp .blgd-toc-daftar { padding: 0 8px 10px; max-height: 320px; }

        /* ── Isi artikel ── */
        .ph-article .blgd-prosa { color: #3a3f4a; font-size: 1.06rem; line-height: 1.85; }
        .ph-article .blgd-prosa > p:first-of-type::first-letter {
            float: left; font-family: var(--judul); font-weight: 800; font-size: 3.3rem; line-height: .8;
            padding: .32rem .6rem 0 0; color: var(--kb);
        }
        .ph-article .blgd-prosa h2 {
            font-family: var(--judul); font-weight: 800; font-size: 1.5rem; color: var(--ink); line-height: 1.3;
            margin: 2.3rem 0 .9rem; padding-left: 16px; position: relative; scroll-margin-top: 110px;
        }
        .ph-article .blgd-prosa h2::before {
            content: ""; position: absolute; left: 0; top: .15em; bottom: .15em; width: 5px; border-radius: 4px;
            background: linear-gradient(180deg, var(--kb), color-mix(in srgb, var(--kb) 45%, #fff));
        }
        .ph-article .blgd-prosa h3 { font-family: var(--judul); font-weight: 700; font-size: 1.22rem; color: var(--ink); margin: 1.8rem 0 .7rem; scroll-margin-top: 110px; }
        .ph-article .blgd-prosa h4 { font-family: var(--judul); font-weight: 700; font-size: 1.08rem; color: var(--ink); margin: 1.4rem 0 .6rem; }
        .ph-article .blgd-prosa p { margin-bottom: 1.2rem; }
        .ph-article .blgd-prosa ul, .ph-article .blgd-prosa ol { margin: 0 0 1.25rem 1.3rem; padding-left: .4rem; }
        .ph-article .blgd-prosa li { margin-bottom: .5rem; padding-left: .25rem; }
        .ph-article .blgd-prosa li::marker { color: var(--kb); font-weight: 800; }
        .ph-article .blgd-prosa a { color: var(--o); font-weight: 600; text-decoration: underline; text-decoration-color: rgba(242,101,34,.35); text-underline-offset: 3px; }
        .ph-article .blgd-prosa a:hover { text-decoration-color: currentColor; }
        .ph-article .blgd-prosa img { max-width: 100%; height: auto; border-radius: 14px; margin: 1.4rem 0; }
        .ph-article .blgd-prosa blockquote {
            position: relative; margin: 1.8rem 0; padding: 1.2rem 1.4rem 1.2rem 3.2rem; border: 0; border-radius: 16px;
            background: color-mix(in srgb, var(--kb) 6%, #fff); color: var(--ink); font-style: italic; line-height: 1.7;
        }
        .ph-article .blgd-prosa blockquote::before { content: "\201C"; position: absolute; left: 1rem; top: .3rem; font-family: Georgia, serif; font-size: 3rem; line-height: 1; color: var(--kb); }
        .ph-article .blgd-prosa strong, .ph-article .blgd-prosa b { color: var(--ink); font-weight: 700; }
        .ph-article .blgd-prosa hr { border: 0; border-top: 1px dashed var(--line); margin: 2.2rem 0; opacity: 1; }
        .ph-article .blgd-prosa pre { background: #1f2430; color: #e6e8ee; padding: 1rem 1.2rem; border-radius: 14px; overflow-x: auto; font-size: .9rem; }
        .ph-article .blgd-prosa table { display: block; width: 100%; overflow-x: auto; border-collapse: collapse; margin: 1.4rem 0; font-size: .95rem; }
        .ph-article .blgd-prosa th, .ph-article .blgd-prosa td { border: 1px solid var(--line); padding: .55rem .8rem; }
        .ph-article .blgd-prosa th { background: var(--soft); font-family: var(--judul); }

        /* Kaki artikel: bagikan + kembali */
        .ph-article .blgd-kaki {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
            margin-top: 36px; padding: 18px 20px; border-radius: 18px; background: #fffaf5; border: 1px solid var(--line);
        }
        .ph-article .blgd-bagikan { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
        .ph-article .blgd-bagikan-label { font-family: var(--judul); font-weight: 700; color: var(--ink); font-size: .92rem; margin-right: 6px; }
        .ph-article .blgd-bagikan a, .ph-article .blgd-bagikan button {
            display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border: 0; border-radius: 12px;
            color: #fff; font-size: 1rem; cursor: pointer; transition: transform .2s ease, box-shadow .2s ease;
        }
        .ph-article .blgd-bagikan a:hover, .ph-article .blgd-bagikan button:hover { transform: translateY(-3px); box-shadow: 0 10px 18px -8px rgba(35,39,47,.45); }
        .ph-article .blgd-bagikan i::before { display: block; line-height: 1; }
        .ph-article .blgd-bagikan .wa { background: #25d366; }
        .ph-article .blgd-bagikan .fb { background: #1877f2; }
        .ph-article .blgd-bagikan .tw { background: #111827; }
        .ph-article .blgd-bagikan .cp { background: var(--grad); }
        .ph-article .blgd-kembali {
            display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 16px; border-radius: 12px;
            background: #fff; border: 1.5px solid var(--line); color: var(--ink); font-family: var(--judul); font-weight: 700; font-size: .88rem;
            text-decoration: none; transition: border-color .2s ease, color .2s ease;
        }
        .ph-article .blgd-kembali:hover { border-color: var(--o); color: var(--o); }
        .ph-article .blgd-kembali i { transition: transform .2s ease; }
        .ph-article .blgd-kembali:hover i { transform: translateX(-3px); }

        /* ── Samping ── */
        .ph-article .blgd-samping { position: sticky; top: 100px; display: flex; flex-direction: column; gap: 20px; }
        .ph-article .blgd-panel { background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px rgba(35,39,47,.05); }
        .ph-article .blgd-panel-kepala { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .ph-article .blgd-panel-kepala > i {
            display: flex; align-items: center; justify-content: center; flex: 0 0 40px; height: 40px; border-radius: 12px;
            color: #fff; font-size: 1.05rem; background: linear-gradient(140deg, var(--kb), color-mix(in srgb, var(--kb) 58%, #fff));
            box-shadow: 0 10px 20px -10px color-mix(in srgb, var(--kb) 85%, transparent);
        }
        .ph-article .blgd-panel-kepala > i::before { display: block; line-height: 1; }
        .ph-article .blgd-panel-kepala h3 { font-family: var(--judul); font-weight: 800; font-size: 1.02rem; color: var(--ink); margin: 0; }
        .ph-article .blgd-panel-kepala small { color: var(--muted); font-size: .78rem; }

        .ph-article .blgd-toc-daftar {
            list-style: none; margin: 0; padding: 0; max-height: calc(100vh - 430px); min-height: 120px; overflow-y: auto;
            scrollbar-width: thin; scrollbar-color: #eadfd2 transparent;
        }
        .ph-article .blgd-toc-daftar a {
            display: flex; gap: 10px; align-items: flex-start; padding: 8px 10px; border-radius: 10px; text-decoration: none;
            color: #4b5260; font-size: .86rem; line-height: 1.45; transition: background .2s ease, color .2s ease;
        }
        .ph-article .blgd-toc-daftar a .no {
            flex: 0 0 24px; height: 22px; display: flex; align-items: center; justify-content: center; border-radius: 7px;
            background: #f5f1ec; color: var(--muted); font-family: var(--judul); font-weight: 700; font-size: .7rem;
            transition: background .2s ease, color .2s ease;
        }
        .ph-article .blgd-toc-daftar a:hover { background: color-mix(in srgb, var(--kb) 6%, #fff); color: var(--ink); }
        .ph-article .blgd-toc-daftar a.is-aktif { background: color-mix(in srgb, var(--kb) 10%, #fff); color: var(--ink); font-weight: 600; }
        .ph-article .blgd-toc-daftar a.is-aktif .no { background: var(--kb); color: #fff; }

        .ph-article .blgd-ajak {
            position: relative; overflow: hidden; border-radius: 20px; padding: 22px; color: #fff; background: var(--grad);
            box-shadow: 0 16px 34px -14px rgba(242,101,34,.6);
        }
        .ph-article .blgd-ajak::before { content: ""; position: absolute; right: -50px; top: -50px; width: 160px; height: 160px; border-radius: 50%; background: rgba(255,255,255,.14); }
        .ph-article .blgd-ajak::after { content: ""; position: absolute; left: -30px; bottom: -60px; width: 130px; height: 130px; border-radius: 50%; background: rgba(255,255,255,.08); }
        .ph-article .blgd-ajak > * { position: relative; z-index: 1; }
        .ph-article .blgd-ajak-ikon {
            display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 13px; margin-bottom: 12px;
            background: rgba(255,255,255,.22); border: 1px solid rgba(255,255,255,.35); font-size: 1.2rem;
        }
        .ph-article .blgd-ajak-ikon::before { display: block; line-height: 1; }
        .ph-article .blgd-ajak h4 { font-family: var(--judul); font-weight: 800; font-size: 1.12rem; margin: 0 0 6px; color: #fff; }
        .ph-article .blgd-ajak p { font-size: .86rem; line-height: 1.55; margin: 0 0 12px; opacity: .95; }
        .ph-article .blgd-ajak ul { list-style: none; padding: 0; margin: 0 0 16px; display: grid; gap: 5px; font-size: .82rem; }
        .ph-article .blgd-ajak ul i { margin-right: 6px; }
        .ph-article .blgd-ajak-aksi { display: flex; align-items: center; flex-wrap: wrap; gap: 10px 14px; }
        .ph-article .blgd-ajak-btn {
            display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 16px; border-radius: 12px;
            background: #fff; color: var(--o); font-family: var(--judul); font-weight: 700; font-size: .88rem; text-decoration: none;
            box-shadow: 0 8px 18px -8px rgba(120,40,0,.45); transition: transform .2s ease;
        }
        .ph-article .blgd-ajak-btn:hover { color: var(--o); transform: translateY(-2px); }
        .ph-article .blgd-ajak-wa { display: inline-flex; align-items: center; gap: 6px; color: #fff; font-size: .82rem; font-weight: 600; text-decoration: none; }
        .ph-article .blgd-ajak-wa:hover { color: #fff; text-decoration: underline; }

        /* ── Bacaan lainnya ── */
        .ph-article .blgd-lain { margin-top: 56px; }
        .ph-article .blgd-bagian { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        .ph-article .blgd-alis {
            display: inline-flex; align-items: center; gap: 8px; margin-bottom: 8px; color: var(--o);
            font-family: var(--judul); font-weight: 700; font-size: .74rem; letter-spacing: .08em; text-transform: uppercase;
        }
        .ph-article .blgd-alis i { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 8px; background: rgba(242,101,34,.1); font-size: .8rem; }
        .ph-article .blgd-bagian h2 { font-family: var(--judul); font-weight: 800; font-size: clamp(1.3rem, 2vw, 1.65rem); color: var(--ink); margin: 0; letter-spacing: -.01em; }
        .ph-article .blgd-semua { display: inline-flex; align-items: center; gap: 8px; color: var(--o); font-family: var(--judul); font-weight: 700; font-size: .9rem; text-decoration: none; }
        .ph-article .blgd-semua i { transition: transform .2s ease; }
        .ph-article .blgd-semua:hover i { transform: translateX(4px); }

        .ph-article .blgd-kisi { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
        .ph-article .blgd-kartu-lain {
            display: flex; flex-direction: column; height: 100%; background: #fff; border: 1px solid var(--line); border-radius: 18px;
            overflow: hidden; text-decoration: none; box-shadow: 0 10px 30px rgba(35,39,47,.05);
            transition: transform .26s ease, box-shadow .26s ease, border-color .26s ease;
        }
        .ph-article .blgd-kartu-lain:hover {
            transform: translateY(-6px); border-color: color-mix(in srgb, var(--kb) 32%, #fff);
            box-shadow: 0 24px 44px -18px color-mix(in srgb, var(--kb) 42%, transparent);
        }
        .ph-article .blgd-kartu-lain .blgd-sampul { aspect-ratio: 16 / 9; }
        .ph-article .blgd-kartu-lain .blgd-ubin i { width: 60px; height: 60px; border-radius: 18px; font-size: 1.55rem; animation: none; transition: transform .35s ease; }
        .ph-article .blgd-kartu-lain:hover .blgd-ubin i { transform: scale(1.07) rotate(-5deg); }
        .ph-article .blgd-kartu-lain .blgd-sampul img { transition: transform .45s ease; }
        .ph-article .blgd-kartu-lain:hover .blgd-sampul img { transform: scale(1.05); }
        .ph-article .blgd-lencana {
            position: absolute; top: 12px; left: 12px; z-index: 2; padding: .32rem .7rem; border-radius: 999px;
            background: rgba(255,255,255,.95); color: var(--kb); box-shadow: 0 4px 12px rgba(35,39,47,.06);
            font-family: var(--judul); font-weight: 700; font-size: .68rem; letter-spacing: .06em; text-transform: uppercase;
        }
        .ph-article .blgd-kartu-lain-isi { display: flex; flex-direction: column; flex: 1; padding: 16px 18px; }
        .ph-article .blgd-kartu-lain h3 {
            font-family: var(--judul); font-weight: 700; font-size: 1.02rem; line-height: 1.42; color: var(--ink); margin: 0 0 12px;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; transition: color .2s ease;
        }
        .ph-article .blgd-kartu-lain:hover h3 { color: var(--o); }
        .ph-article .blgd-kartu-lain-kaki {
            display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: auto;
            padding-top: 12px; border-top: 1px solid #f5ede3; color: var(--muted); font-size: .78rem;
        }
        .ph-article .blgd-kartu-lain-kaki i.bi-calendar3, .ph-article .blgd-kartu-lain-kaki i.bi-clock { color: var(--o); margin-right: 4px; }
        .ph-article .blgd-panah {
            flex: 0 0 34px; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 11px;
            background: color-mix(in srgb, var(--kb) 10%, #fff); color: var(--kb); transition: background .2s ease, color .2s ease;
        }
        .ph-article .blgd-kartu-lain:hover .blgd-panah { background: var(--kb); color: #fff; }

        @media (prefers-reduced-motion: reduce) {
            .ph-article .blgd-ubin i { animation: none !important; }
            .ph-article .blgd-kartu-lain, .ph-article .blgd-kartu-lain .blgd-sampul img, .ph-article .blgd-kartu-lain .blgd-ubin i { transition: none; }
            .ph-article .blgd-kartu-lain:hover, .ph-article .blgd-kartu-lain:hover .blgd-sampul img,
            .ph-article .blgd-kartu-lain:hover .blgd-ubin i { transform: none; }
        }

        @media (max-width: 1199.98px) {
            .ph-article .blgd-grid { grid-template-columns: minmax(0, 1fr) 300px; }
            .ph-article .blgd-isi { padding: 30px 34px 34px; }
        }
        @media (max-width: 991.98px) {
            .ph-article .blgd-grid { grid-template-columns: minmax(0, 1fr); }
            .ph-article .blgd-samping { position: static; }
            .ph-article .blgd-kisi { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        }
        @media (max-width: 767.98px) {
            .ph-article .blgd-kisi { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .ph-article .blgd-sampul { aspect-ratio: 16 / 10; }
            .ph-article .blgd-ubin i { width: 76px; height: 76px; border-radius: 22px; font-size: 2rem; }
            .ph-article .blgd-isi { padding: 22px 18px 24px; }
            .ph-article .blgd-prosa { font-size: 1rem; line-height: 1.8; }
            .ph-article .blgd-prosa h2 { font-size: 1.28rem; }
            .ph-article .blgd-ringkas p { font-size: 1rem; }
            .ph-article .blgd-kaki { flex-direction: column; align-items: stretch; }
            .ph-article .blgd-kembali { justify-content: center; }
        }
    </style>

    <div class="blgd-progres" aria-hidden="true"><span></span></div>

    <div class="page-title ph-page-title" style="--c: {{ $ragam['warna'] }}">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head blgd-kepala">
                <span class="ph-sec-eyebrow"><i class="bi {{ $ragam['ikon'] }}"></i> {{ $post->category ?: 'Blog' }}</span>
                <h1>{{ $post->title }}</h1>
                <div class="blgd-meta">
                    {{-- Penulis sengaja statis "admin", tidak diambil dari kolom
                         author maupun akun yang login, supaya nama karyawan tidak
                         tampil di halaman publik. --}}
                    <span><i class="bi bi-person-fill"></i>admin</span>
                    <span><i class="bi bi-calendar3"></i>{{ optional($post->published_at ?? $post->created_at)->locale('id')?->translatedFormat('d F Y') }}</span>
                    <span><i class="bi bi-clock"></i>{{ $post->readingMinutes() }} menit baca</span>
                    <span><i class="bi bi-eye"></i>{{ number_format($post->views) }}x dilihat</span>
                </div>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}" wire:navigate>Beranda</a></li>
                    <li><a href="{{ route('blog.index') }}" wire:navigate>Blog</a></li>
                    <li class="current">Artikel</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="blgd-badan">
        <div class="container">
            <div class="blgd-grid">
                {{-- KOLOM ARTIKEL --}}
                <article class="blgd-kartu">
                    @php $adaSampul = $post->cover && \Storage::disk('public')->exists('img/blog/'.$post->cover); @endphp
                    <div class="blgd-sampul {{ $adaSampul ? '' : 'is-kosong' }}">
                        @if ($adaSampul)
                            <img src="{{ asset('storage/img/blog/' . $post->cover) }}" alt="{{ $post->cover_alt ?: $post->title }}" decoding="async"
                                onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                        @endif
                        <div class="blgd-ubin">
                            <i class="bi {{ $ragam['ikon'] }}"></i>
                            @if ($post->category)
                                <b>{{ $post->category }}</b>
                            @endif
                        </div>
                    </div>

                    <div class="blgd-isi">
                        @if ($post->excerpt)
                            <div class="blgd-ringkas">
                                <span class="blgd-ringkas-label"><i class="bi bi-lightbulb-fill"></i> Ringkasan</span>
                                <p>{{ $post->excerpt }}</p>
                            </div>
                        @endif

                        @if (count($daftarIsi) >= 2)
                            <details class="blgd-isi-hp d-lg-none">
                                <summary>
                                    <i class="bi bi-list-ol"></i> Daftar Isi
                                    <small>{{ count($daftarIsi) }} bagian</small>
                                    <i class="bi bi-chevron-down"></i>
                                </summary>
                                <ol class="blgd-toc-daftar">
                                    @foreach ($daftarIsi as $i => $bagian)
                                        <li><a href="#{{ $bagian['id'] }}"><span class="no">{{ $i + 1 }}</span><span>{{ $bagian['teks'] }}</span></a></li>
                                    @endforeach
                                </ol>
                            </details>
                        @endif

                        <div class="blgd-prosa">
                            {{-- Disaring saat tampil (lihat BlogShow::render): isi blog
                                 berupa HTML dari editor, tanpa penyaring penulis blog bisa
                                 menyisipkan <script> yang berjalan di browser semua
                                 pengunjung. Id jangkar daftar isi ditambahkan SESUDAHNYA. --}}
                            {!! $isi !!}
                        </div>

                        @php $tagArtikel = $post->tagDaftar(); @endphp
                        @if ($tagArtikel)
                            <div class="blgd-tag">
                                <span class="blgd-tag-label"><i class="bi bi-hash"></i>Topik</span>
                                @foreach ($tagArtikel as $t)
                                    <a href="{{ route('blog.index', ['tag' => $t]) }}" class="blgd-tag-pil">#{{ $t }}</a>
                                @endforeach
                            </div>
                        @endif

                        @php $url = route('blog.show', $post->slug); @endphp
                        <div class="blgd-kaki">
                            <div class="blgd-bagikan">
                                <span class="blgd-bagikan-label">Bagikan:</span>
                                <a class="wa" target="_blank" rel="noopener" title="WhatsApp" aria-label="Bagikan ke WhatsApp"
                                    href="https://wa.me/?text={{ urlencode($post->title . ' — ' . $url) }}"><i class="bi bi-whatsapp"></i></a>
                                <a class="fb" target="_blank" rel="noopener" title="Facebook" aria-label="Bagikan ke Facebook"
                                    href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"><i class="bi bi-facebook"></i></a>
                                <a class="tw" target="_blank" rel="noopener" title="X / Twitter" aria-label="Bagikan ke X"
                                    href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode($url) }}"><i class="bi bi-twitter-x"></i></a>
                                <button class="cp share-copy-btn" type="button" title="Salin tautan" aria-label="Salin tautan" data-url="{{ $url }}"><i class="bi bi-link-45deg"></i></button>
                            </div>
                            <a href="{{ route('blog.index') }}" wire:navigate class="blgd-kembali"><i class="bi bi-arrow-left"></i> Kembali ke Blog</a>
                        </div>
                    </div>
                </article>

                {{-- SAMPING --}}
                <aside class="blgd-samping">
                    @if (count($daftarIsi) >= 2)
                        <div class="blgd-panel d-none d-lg-block">
                            <div class="blgd-panel-kepala">
                                <i class="bi bi-list-ol"></i>
                                <div>
                                    <h3>Daftar Isi</h3>
                                    <small>{{ count($daftarIsi) }} bagian</small>
                                </div>
                            </div>
                            <ol class="blgd-toc-daftar" id="blgd-toc">
                                @foreach ($daftarIsi as $i => $bagian)
                                    <li><a href="#{{ $bagian['id'] }}" data-bagian="{{ $bagian['id'] }}"><span class="no">{{ $i + 1 }}</span><span>{{ $bagian['teks'] }}</span></a></li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    <div class="blgd-ajak">
                        <i class="bi bi-bag-heart-fill blgd-ajak-ikon"></i>
                        <h4>Cari akun premium bergaransi?</h4>
                        <p>Jelajahi katalog akun premium, lisensi &amp; tools AI Phoenix Digital.</p>
                        <ul>
                            <li><i class="bi bi-lightning-charge-fill"></i>Proses instan</li>
                            <li><i class="bi bi-shield-check"></i>Bergaransi &amp; aman</li>
                        </ul>
                        <div class="blgd-ajak-aksi">
                            <a href="{{ route('shop.index') }}" wire:navigate class="blgd-ajak-btn"><i class="bi bi-bag"></i> Lihat Produk</a>
                            <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya." target="_blank" rel="noopener" class="blgd-ajak-wa"><i class="bi bi-whatsapp"></i> Tanya admin</a>
                        </div>
                    </div>
                </aside>
            </div>

            @if ($related->isNotEmpty())
                <div class="blgd-lain">
                    <div class="blgd-bagian">
                        <div>
                            <span class="blgd-alis"><i class="bi bi-collection"></i> Bacaan Lainnya</span>
                            <h2>Lanjutkan Membaca</h2>
                        </div>
                        <a href="{{ route('blog.index') }}" wire:navigate class="blgd-semua">Semua artikel <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="blgd-kisi">
                        @foreach ($related as $rel)
                            @php
                                $rbL = \App\Support\RagamBlog::untuk($rel->category);
                                $adaSampulL = $rel->cover && \Storage::disk('public')->exists('img/blog/'.$rel->cover);
                            @endphp
                            <a href="{{ route('blog.show', $rel->slug) }}" wire:navigate class="blgd-kartu-lain" style="--kb: {{ $rbL['warna'] }}">
                                <div class="blgd-sampul {{ $adaSampulL ? '' : 'is-kosong' }}">
                                    @if ($adaSampulL)
                                        <img src="{{ asset('storage/img/blog/' . $rel->cover) }}" alt="{{ $rel->cover_alt ?: $rel->title }}" loading="lazy" decoding="async"
                                            onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                                    @endif
                                    <div class="blgd-ubin"><i class="bi {{ $rbL['ikon'] }}"></i></div>
                                    @if ($rel->category)
                                        <span class="blgd-lencana">{{ $rel->category }}</span>
                                    @endif
                                </div>
                                <div class="blgd-kartu-lain-isi">
                                    <h3>{{ $rel->title }}</h3>
                                    <div class="blgd-kartu-lain-kaki">
                                        <span>
                                            <i class="bi bi-calendar3"></i>{{ optional($rel->published_at ?? $rel->created_at)->locale('id')?->translatedFormat('d M Y') }}
                                            &nbsp; <i class="bi bi-clock"></i>{{ $rel->readingMinutes() }} mnt
                                        </span>
                                        <span class="blgd-panah"><i class="bi bi-arrow-right"></i></span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
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

    /*
     | Progres baca + penanda bagian aktif di daftar isi.
     | Pendengarnya dipasang SEKALI untuk seluruh sesi dan mencari elemennya
     | setiap kali dipanggil: halaman blog dibuka lewat wire:navigate, jadi
     | elemen yang ditangkap sekali di awal sudah bukan elemen di layar.
     | Di halaman lain elemennya tidak ada dan fungsinya langsung selesai.
     */
    if (!window.__blgdTerpasang) {
        window.__blgdTerpasang = true;
        let menunggu = false;
        const perbarui = () => {
            menunggu = false;
            const kartu = document.querySelector('.ph-article .blgd-kartu');
            const bilah = document.querySelector('.ph-article .blgd-progres span');
            if (!kartu || !bilah) return;

            const r = kartu.getBoundingClientRect();
            const jarak = r.height - window.innerHeight;
            const nilai = jarak > 0 ? Math.min(1, Math.max(0, -r.top / jarak)) : (r.top < 0 ? 1 : 0);
            bilah.style.transform = 'scaleX(' + nilai + ')';

            const toc = document.getElementById('blgd-toc');
            if (!toc) return;
            // Sepertiga atas layar, bukan angka tetap: header situs sticky
            // (~100px), jadi judul yang baru muncul di bawahnya sudah dibaca.
            const ambang = window.innerHeight * 0.3;
            let aktif = null;
            document.querySelectorAll('.ph-article .blgd-prosa h2[id]').forEach((h) => {
                if (h.getBoundingClientRect().top < ambang) aktif = h.id;
            });
            toc.querySelectorAll('a[data-bagian]').forEach((a) => {
                const kena = a.dataset.bagian === aktif;
                if (kena && !a.classList.contains('is-aktif')) {
                    // Gulir di dalam daftarnya saja, bukan menggulir halaman.
                    const atas = a.offsetTop - toc.offsetTop;
                    if (atas < toc.scrollTop || atas > toc.scrollTop + toc.clientHeight - a.offsetHeight) {
                        toc.scrollTop = atas - toc.clientHeight / 3;
                    }
                }
                a.classList.toggle('is-aktif', kena);
            });
        };
        const minta = () => { if (!menunggu) { menunggu = true; requestAnimationFrame(perbarui); } };
        window.addEventListener('scroll', minta, { passive: true });
        window.addEventListener('resize', minta);
        document.addEventListener('livewire:navigated', minta);
        minta();
    }
</script>
@endpush
