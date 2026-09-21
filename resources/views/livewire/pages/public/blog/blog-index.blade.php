@push('styles')
    {{-- Penemuan otomatis umpan: pembaca RSS menemukannya tanpa perlu
         ditunjukkan alamatnya. --}}
    <link rel="alternate" type="application/rss+xml" title="Blog Phoenix Digital" href="{{ route('blog.feed') }}">

    {{-- Penanda halaman berurutan: tanpa ini tiap halaman daftar terbaca
         sebagai halaman terpisah yang isinya mirip. --}}
    @if ($posts->previousPageUrl())
        <link rel="prev" href="{{ $posts->previousPageUrl() }}">
    @endif
    @if ($posts->nextPageUrl())
        <link rel="next" href="{{ $posts->nextPageUrl() }}">
    @endif
@endpush

@section('title')
Blog — Tips, Panduan & Info Akun Premium | Phoenix Digital
@endsection

<div class="ph-blog">
    <style>
        .ph-blog { --o: var(--ph-orange, #f26522); --a: var(--ph-amber, #fba919); --ink: var(--ph-ink, #23272f);
            --muted: var(--ph-muted, #6b7280); --soft: var(--ph-soft, #fff8f1); --line: var(--ph-line, #f1e6d8);
            --grad: var(--ph-grad, linear-gradient(135deg, #fba919 0%, #f26522 100%));
            --judul: 'Plus Jakarta Sans', 'Poppins', sans-serif; }

        /* Lebar badan TIDAK dipatok angka. Dulu .container di bawah kepala diberi
           max-width 1140px, sehingga badan lebih sempit 90px di kiri-kanan dari
           kartu kepala. Kartu kepala adalah ::before pada .container yang
           menjorok 12px — sama dengan padding .container — jadi dengan .container
           polos keduanya sejajar di SEMUA ukuran layar. */

        /* ── Bilah alat: cari + topik dalam satu kartu ── */
        .ph-blog .blg-alat {
            display: flex; align-items: center; gap: 14px; padding: 10px; margin-bottom: 30px;
            background: #fff; border: 1px solid var(--line); border-radius: 20px;
            box-shadow: 0 10px 30px rgba(35,39,47,.05);
        }
        .ph-blog .blg-cari { position: relative; flex: 0 0 340px; }
        .ph-blog .blg-cari input {
            width: 100%; height: 46px; padding: 0 2.6rem; border: 1.5px solid transparent; border-radius: 14px;
            background: #faf6f1; color: var(--ink); font-family: var(--judul); font-size: .93rem; outline: none;
            transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        }
        .ph-blog .blg-cari input::placeholder { color: #a3a8b1; }
        .ph-blog .blg-cari input:focus { background: #fff; border-color: var(--o); box-shadow: 0 0 0 4px rgba(242,101,34,.10); }
        .ph-blog .blg-cari .ico { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--o); }
        .ph-blog .blg-cari .clr {
            position: absolute; right: .7rem; top: 50%; transform: translateY(-50%); border: 0; background: none;
            color: #b6bcc6; padding: .25rem; line-height: 1; cursor: pointer;
        }
        .ph-blog .blg-cari .clr:hover { color: var(--o); }
        .ph-blog .blg-pemisah { width: 1px; align-self: stretch; margin: 6px 0; background: var(--line); }
        .ph-blog .blg-topik { display: flex; flex-wrap: wrap; gap: 8px; flex: 1; min-width: 0; }
        .ph-blog .blg-chip {
            display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px 0 6px;
            border: 1.5px solid var(--line); border-radius: 12px; background: #fff; color: var(--ink);
            font-family: var(--judul); font-weight: 600; font-size: .86rem; white-space: nowrap; cursor: pointer;
            transition: border-color .2s ease, background .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .ph-blog .blg-chip i {
            display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 9px;
            font-size: .85rem; color: var(--kb); background: color-mix(in srgb, var(--kb) 12%, #fff);
            transition: background .2s ease, color .2s ease;
        }
        .ph-blog .blg-chip:hover { border-color: color-mix(in srgb, var(--kb) 45%, #fff); background: color-mix(in srgb, var(--kb) 5%, #fff); }
        .ph-blog .blg-chip.is-aktif {
            border-color: transparent; color: #fff;
            background: linear-gradient(135deg, color-mix(in srgb, var(--kb) 70%, #fff) -40%, var(--kb) 100%);
            box-shadow: 0 10px 20px -10px color-mix(in srgb, var(--kb) 90%, transparent);
        }
        .ph-blog .blg-chip.is-aktif i { background: rgba(255,255,255,.22); color: #fff; }

        /* ── Sampul: dipakai kartu utama, baris "Baru Terbit", dan kartu grid ──
           Tidak satu pun artikel punya sampul, dan dulu semuanya jadi kotak persik
           berikon sama. Kini bidangnya bersapuan warna KATEGORI (App\Support\
           RagamBlog, dipinjam dari kategori produk padanannya di Shop) dengan
           ubin ikon di tengah — perlakuan yang sama dengan kartu /shop & /bundling. */
        .ph-blog .blg-sampul { position: relative; overflow: hidden; background: linear-gradient(135deg, #fff5e9, #fff9f3); }
        .ph-blog .blg-sampul img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
        .ph-blog .blg-sampul.is-kosong { background: linear-gradient(160deg, color-mix(in srgb, var(--kb) 14%, #fff) 0%, #fff 80%); }
        .ph-blog .blg-sampul.is-kosong::before {
            content: ""; position: absolute; top: -28%; right: -14%; width: 52%; aspect-ratio: 1; border-radius: 50%;
            background: color-mix(in srgb, var(--kb) 12%, transparent); transition: transform .4s ease;
        }
        /* Titik memudar — perbendaharaan yang sama dengan kartu kepala. */
        .ph-blog .blg-sampul.is-kosong::after {
            content: ""; position: absolute; left: 0; bottom: 0; width: 45%; height: 70%;
            background-image: radial-gradient(color-mix(in srgb, var(--kb) 30%, transparent) 1.2px, transparent 1.6px);
            background-size: 14px 14px;
            -webkit-mask-image: linear-gradient(35deg, #000 0%, transparent 70%); mask-image: linear-gradient(35deg, #000 0%, transparent 70%);
        }
        .ph-blog .blg-ubin { display: none; position: absolute; inset: 0; z-index: 1; align-items: center; justify-content: center; }
        .ph-blog .blg-sampul.is-kosong .blg-ubin { display: flex; }
        .ph-blog .blg-ubin i {
            --u: 60px;
            display: flex; align-items: center; justify-content: center; width: var(--u); height: var(--u);
            border-radius: calc(var(--u) * .3); font-size: calc(var(--u) * .42); color: #fff;
            background: linear-gradient(140deg, var(--kb), color-mix(in srgb, var(--kb) 58%, #fff));
            box-shadow: 0 14px 26px -12px color-mix(in srgb, var(--kb) 85%, transparent);
            transition: transform .35s ease;
        }
        .ph-blog .blg-ubin i::before { display: block; line-height: 1; }
        a:hover .blg-sampul img { transform: scale(1.05); }
        a:hover .blg-sampul.is-kosong::before { transform: scale(1.25); }
        a:hover .blg-ubin i { transform: scale(1.07) rotate(-5deg); }

        .ph-blog .blg-label {
            display: inline-flex; align-items: center; gap: 6px; font-family: var(--judul); font-weight: 700;
            font-size: .7rem; letter-spacing: .06em; text-transform: uppercase; color: var(--kb);
        }
        .ph-blog .blg-label::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--kb); }
        .ph-blog .blg-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 14px; font-size: .8rem; color: var(--muted); }
        .ph-blog .blg-meta i { color: var(--o); margin-right: 4px; }

        /* ── Sorotan: artikel utama + panel "Baru Terbit" ── */
        .ph-blog .blg-sorot { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr); gap: 24px; margin-bottom: 60px; }
        .ph-blog .blg-sorot.is-tunggal { grid-template-columns: minmax(0, 1fr); }

        .ph-blog .blg-utama {
            display: flex; flex-direction: column; background: #fff; border: 1px solid var(--line); border-radius: 22px;
            overflow: hidden; text-decoration: none; box-shadow: 0 14px 40px rgba(35,39,47,.06);
            transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
        }
        .ph-blog .blg-utama:hover {
            transform: translateY(-4px); border-color: color-mix(in srgb, var(--kb) 30%, #fff);
            box-shadow: 0 26px 50px -18px color-mix(in srgb, var(--kb) 40%, transparent);
        }
        .ph-blog .blg-utama .blg-sampul { aspect-ratio: 16 / 7; }
        .ph-blog .blg-utama .blg-ubin i { --u: 92px; }
        .ph-blog .blg-pita {
            position: absolute; top: 16px; left: 16px; z-index: 2; display: inline-flex; align-items: center; gap: 6px;
            padding: .4rem .8rem; border-radius: 999px; background: rgba(255,255,255,.95); color: var(--o);
            font-family: var(--judul); font-weight: 700; font-size: .72rem; letter-spacing: .05em; text-transform: uppercase;
            box-shadow: 0 6px 16px rgba(35,39,47,.08);
        }
        .ph-blog .blg-utama-isi { display: flex; flex-direction: column; flex: 1; padding: 24px 28px 26px; }
        .ph-blog .blg-utama h2 {
            font-family: var(--judul); font-weight: 800; font-size: clamp(1.35rem, 2.1vw, 1.8rem); line-height: 1.25;
            letter-spacing: -.015em; color: var(--ink); margin: 10px 0 10px; transition: color .2s ease;
        }
        .ph-blog .blg-utama:hover h2 { color: var(--o); }
        .ph-blog .blg-utama p {
            color: var(--muted); font-size: .97rem; line-height: 1.7; margin-bottom: 20px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .ph-blog .blg-utama-kaki {
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
            margin-top: auto; padding-top: 18px; border-top: 1px dashed var(--line);
        }
        .ph-blog .blg-tombol {
            display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border-radius: 12px;
            background: var(--grad); color: #fff; font-family: var(--judul); font-weight: 700; font-size: .88rem;
            box-shadow: 0 10px 20px -10px rgba(242,101,34,.8); white-space: nowrap;
        }
        .ph-blog .blg-tombol i { transition: transform .2s ease; }
        .ph-blog .blg-utama:hover .blg-tombol i { transform: translateX(4px); }

        .ph-blog .blg-baru {
            display: flex; flex-direction: column; background: #fff; border: 1px solid var(--line); border-radius: 22px;
            padding: 22px 22px 8px; box-shadow: 0 14px 40px rgba(35,39,47,.06); position: relative; overflow: hidden;
        }
        .ph-blog .blg-baru::before {
            content: ""; position: absolute; top: -70px; right: -70px; width: 180px; height: 180px; border-radius: 50%;
            background: radial-gradient(circle, rgba(251,169,25,.16), transparent 68%); pointer-events: none;
        }
        .ph-blog .blg-baru-kepala { position: relative; display: flex; align-items: center; gap: 12px; padding-bottom: 14px; }
        .ph-blog .blg-baru-kepala > i {
            display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 13px;
            background: var(--grad); color: #fff; font-size: 1.1rem; box-shadow: 0 10px 20px -10px rgba(242,101,34,.8);
        }
        .ph-blog .blg-baru-kepala h3 { font-family: var(--judul); font-weight: 800; font-size: 1.1rem; color: var(--ink); margin: 0; }
        .ph-blog .blg-baru-kepala p { font-size: .8rem; color: var(--muted); margin: 0; }
        .ph-blog .blg-daftar { list-style: none; margin: 0; padding: 0; }
        .ph-blog .blg-daftar li { display: flex; border-top: 1px dashed var(--line); }
        .ph-blog .blg-baris { display: flex; align-items: center; gap: 14px; width: 100%; padding: 14px 0; text-decoration: none; }
        .ph-blog .blg-baris .blg-sampul { flex: 0 0 88px; height: 88px; border-radius: 16px; }
        .ph-blog .blg-baris .blg-sampul.is-kosong::before, .ph-blog .blg-baris .blg-sampul.is-kosong::after { display: none; }
        .ph-blog .blg-baris .blg-ubin i { --u: 40px; }
        .ph-blog .blg-baris-isi { min-width: 0; }
        .ph-blog .blg-baris h4 {
            font-family: var(--judul); font-weight: 700; font-size: .96rem; line-height: 1.4; color: var(--ink); margin: 4px 0 6px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color .2s ease;
        }
        .ph-blog .blg-baris:hover h4 { color: var(--o); }
        .ph-blog .blg-baris .blg-meta { font-size: .76rem; }
        /* Ajakan di dasar panel: panel ini lebih pendek dari kartu utama, dan
           sisa tingginya dipakai untuk jalan ke toko alih-alih dibiarkan kosong. */
        .ph-blog .blg-ajak {
            position: relative; overflow: hidden; display: flex; align-items: center; gap: 12px; margin: auto 0 14px;
            padding: 14px 14px 14px 16px; border-radius: 16px; text-decoration: none;
            background: linear-gradient(135deg, #fff4e6 0%, #ffe7d1 100%); border: 1px solid #fbd9b8;
        }
        .ph-blog .blg-ajak::after {
            content: ""; position: absolute; right: -30px; bottom: -40px; width: 110px; height: 110px; border-radius: 50%;
            background: radial-gradient(circle, rgba(242,101,34,.18), transparent 70%); pointer-events: none;
        }
        .ph-blog .blg-ajak-teks { flex: 1; min-width: 0; }
        .ph-blog .blg-ajak b { display: block; font-family: var(--judul); font-size: .92rem; color: var(--ink); }
        .ph-blog .blg-ajak small { display: block; color: var(--muted); font-size: .78rem; line-height: 1.45; }
        .ph-blog .blg-ajak .blg-tombol { position: relative; z-index: 1; height: 38px; padding: 0 14px; font-size: .82rem; border-radius: 11px; }
        .ph-blog .blg-ajak:hover .blg-tombol i { transform: translateX(3px); }
        .ph-blog .blg-daftar + .blg-ajak { margin-top: auto; }

        /* ── Kepala bagian grid ── */
        .ph-blog .blg-bagian { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        .ph-blog .blg-alis {
            display: inline-flex; align-items: center; gap: 8px; margin-bottom: 8px; color: var(--o);
            font-family: var(--judul); font-weight: 700; font-size: .74rem; letter-spacing: .08em; text-transform: uppercase;
        }
        .ph-blog .blg-alis i {
            display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 8px;
            background: rgba(242,101,34,.1); font-size: .8rem;
        }
        .ph-blog .blg-bagian h2 { font-family: var(--judul); font-weight: 800; font-size: clamp(1.3rem, 2vw, 1.65rem); color: var(--ink); letter-spacing: -.01em; margin: 0; }
        .ph-blog .blg-jumlah {
            display: inline-flex; align-items: center; gap: 6px; padding: .45rem .9rem; border-radius: 999px;
            background: var(--soft); border: 1px solid var(--line); color: var(--muted); font-size: .82rem; font-weight: 600;
        }
        .ph-blog .blg-jumlah b { color: var(--ink); }

        /* ── Grid kartu ── */
        .ph-blog .blg-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px; }
        .ph-blog .blg-kartu {
            display: flex; flex-direction: column; height: 100%; background: #fff; border: 1px solid var(--line); border-radius: 18px;
            overflow: hidden; text-decoration: none; box-shadow: 0 10px 30px rgba(35,39,47,.05);
            transition: transform .26s ease, box-shadow .26s ease, border-color .26s ease;
        }
        .ph-blog .blg-kartu:hover {
            transform: translateY(-6px); border-color: color-mix(in srgb, var(--kb) 32%, #fff);
            box-shadow: 0 24px 44px -18px color-mix(in srgb, var(--kb) 42%, transparent);
        }
        .ph-blog .blg-kartu .blg-sampul { aspect-ratio: 16 / 10; }
        .ph-blog .blg-lencana {
            position: absolute; top: 12px; left: 12px; z-index: 2; padding: .32rem .7rem; border-radius: 999px;
            background: rgba(255,255,255,.95); box-shadow: 0 4px 12px rgba(35,39,47,.06);
        }
        .ph-blog .blg-lencana.blg-label::before { display: none; }
        .ph-blog .blg-kartu-isi { display: flex; flex-direction: column; flex: 1; padding: 16px 18px 16px; }
        .ph-blog .blg-kartu h3 {
            font-family: var(--judul); font-weight: 700; font-size: 1.02rem; line-height: 1.42; color: var(--ink); margin: 0 0 8px;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; transition: color .2s ease;
        }
        .ph-blog .blg-kartu:hover h3 { color: var(--o); }
        .ph-blog .blg-kartu p {
            color: var(--muted); font-size: .87rem; line-height: 1.62; margin: 0 0 14px;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        }
        .ph-blog .blg-kartu-kaki {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            margin-top: auto; padding-top: 12px; border-top: 1px solid #f5ede3;
        }
        .ph-blog .blg-kartu-kaki .blg-meta { font-size: .76rem; gap: 4px 10px; }
        .ph-blog .blg-panah {
            flex: 0 0 34px; display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 11px;
            background: color-mix(in srgb, var(--kb) 10%, #fff); color: var(--kb); transition: background .2s ease, color .2s ease, transform .2s ease;
        }
        .ph-blog .blg-kartu:hover .blg-panah { background: var(--kb); color: #fff; transform: translateX(2px); }

        /* Livewire sedang mengambil hasil pencarian/topik. */
        .ph-blog .blg-hasil { transition: opacity .2s ease; }
        .ph-blog .blg-hasil.is-memuat { opacity: .5; pointer-events: none; }

        @media (prefers-reduced-motion: reduce) {
            .ph-blog .blg-utama, .ph-blog .blg-kartu, .ph-blog .blg-sampul img, .ph-blog .blg-ubin i,
            .ph-blog .blg-sampul.is-kosong::before, .ph-blog .blg-panah { transition: none; }
            .ph-blog .blg-utama:hover, .ph-blog .blg-kartu:hover, a:hover .blg-sampul img,
            a:hover .blg-sampul.is-kosong::before, a:hover .blg-ubin i { transform: none; }
        }

        /* Empty state — pola sama dengan halaman Bundling (.bdl-empty / .be-*),
           prefix ble- dipakai agar tidak bentrok dengan utility .bg-* Bootstrap. */
        .blg-empty { text-align: center; padding: 30px 16px 20px; max-width: 480px; margin: 0 auto; }
        .blg-empty-art { margin-bottom: 6px; }
        .blg-empty-art svg { width: 260px; max-width: 82%; height: auto; overflow: visible; }
        .blg-empty-title { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800; color: var(--ink); font-size: 1.35rem; margin: 4px 0 6px; }
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

        @media (max-width: 1199.98px) {
            .ph-blog .blg-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ph-blog .blg-cari { flex-basis: 280px; }
        }
        @media (max-width: 991.98px) {
            .ph-blog .blg-sorot { grid-template-columns: minmax(0, 1fr); }
            .ph-blog .blg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ph-blog .blg-alat { flex-direction: column; align-items: stretch; }
            .ph-blog .blg-cari { flex-basis: auto; }
            .ph-blog .blg-pemisah { display: none; }
            /* Topik digeser ke samping, bukan ditumpuk jadi beberapa baris. */
            .ph-blog .blg-topik { flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; padding-bottom: 2px; }
            .ph-blog .blg-topik::-webkit-scrollbar { display: none; }
        }
        @media (max-width: 575.98px) {
            .ph-blog .blg-grid { grid-template-columns: minmax(0, 1fr); }
            .ph-blog .blg-utama .blg-sampul { aspect-ratio: 16 / 10; }
            .ph-blog .blg-utama .blg-ubin i { --u: 72px; }
            .ph-blog .blg-utama-isi { padding: 18px 18px 20px; }
            .ph-blog .blg-baru { padding: 18px 16px 4px; }
            .ph-blog .blg-baris .blg-sampul { flex-basis: 72px; height: 72px; border-radius: 14px; }
            .ph-blog .blg-baris .blg-ubin i { --u: 34px; }
            .ph-blog .blg-sorot { margin-bottom: 44px; }
        }

        .ph-blog .blg-rss {
            display: inline-flex; align-items: center; gap: 6px; flex: 0 0 auto;
            padding: 8px 13px; border-radius: 999px; border: 1px solid var(--ph-line, #f1e6d8);
            background: #fff; color: var(--ph-muted, #6b7280); font-size: .84rem; font-weight: 700;
            text-decoration: none;
        }
        .ph-blog .blg-rss:hover { border-color: #f59e0b; color: #b45309; }
        .ph-blog .blg-rss i { color: #f59e0b; }

        /* ===== Bilah tag (topik) ===== */
        .ph-blog .blg-tagbar {
            display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
            margin: -6px 0 18px; padding: 0 2px;
        }
        .ph-blog .blg-tagbar-label {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .82rem; font-weight: 700; color: var(--ph-muted, #6b7280);
        }
        .ph-blog .blg-tag {
            padding: 5px 12px; border-radius: 999px; border: 1px solid var(--ph-line, #f1e6d8);
            background: #fff; color: var(--ph-muted, #6b7280); font-size: .82rem; font-weight: 600;
            cursor: pointer; transition: border-color .15s ease, color .15s ease, background .15s ease;
        }
        .ph-blog .blg-tag:hover { border-color: var(--ph-orange, #f26522); color: var(--ph-ink, #23272f); }
        .ph-blog .blg-tag.is-aktif {
            border-color: transparent; color: #fff;
            background: var(--ph-grad, linear-gradient(135deg, #fba919 0%, #f26522 100%));
        }
        .ph-blog .blg-tag.is-lepas { border-style: dashed; }
        .ph-blog .blg-ket-kategori {
            display: flex; align-items: flex-start; gap: 8px; margin: -6px 0 18px;
            padding: 11px 15px; border-radius: 14px;
            background: var(--ph-soft, #fff8f1); border: 1px solid var(--ph-line, #f1e6d8);
            color: var(--ph-muted, #6b7280); font-size: .88rem; line-height: 1.55;
        }
        .ph-blog .blg-ket-kategori i { color: var(--ph-orange, #f26522); margin-top: 2px; }
        @media (max-width: 575.98px) {
            .ph-blog .blg-tagbar { gap: 6px; }
            .ph-blog .blg-tag { font-size: .78rem; padding: 4px 10px; }
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
        <div class="container">
            {{-- Bilah alat: cari + topik --}}
            <div class="blg-alat">
                <div class="blg-cari">
                    <i class="bi bi-search ico"></i>
                    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari artikel..." aria-label="Cari artikel">
                    @if ($search)
                        <button type="button" class="clr" wire:click="$set('search', '')" aria-label="Hapus pencarian"><i class="bi bi-x-circle-fill"></i></button>
                    @endif
                </div>
                <a class="blg-rss" href="{{ route('blog.feed') }}" target="_blank" rel="noopener"
                    title="Langganan lewat pembaca RSS">
                    <i class="bi bi-rss-fill"></i><span>RSS</span>
                </a>
                @if ($categories->isNotEmpty())
                    <span class="blg-pemisah"></span>
                    <div class="blg-topik">
                        <button type="button" class="blg-chip {{ $category === '' ? 'is-aktif' : '' }}" style="--kb: #f26522" wire:click="$set('category', '')">
                            <i class="bi bi-grid"></i> Semua
                        </button>
                        @foreach ($categories as $cat)
                            @php $rbC = \App\Support\RagamBlog::untuk($cat); @endphp
                            <button type="button" class="blg-chip {{ $category === $cat ? 'is-aktif' : '' }}" style="--kb: {{ $rbC['warna'] }}" wire:click="filterCategory(@js($cat))">
                                <i class="bi {{ $rbC['ikon'] }}"></i> {{ $cat }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Bilah tag: topik yang lebih tajam daripada kategori. Hanya tag
                 yang benar-benar dipakai artikel terbit yang muncul. --}}
            @if ($tagDipakai)
                <div class="blg-tagbar">
                    <span class="blg-tagbar-label"><i class="bi bi-hash"></i>Topik</span>
                    @foreach ($tagDipakai as $t)
                        <button type="button" class="blg-tag {{ $tag === $t ? 'is-aktif' : '' }}" wire:click="pilihTag(@js($t))">#{{ $t }}</button>
                    @endforeach
                    @if ($tag)
                        <button type="button" class="blg-tag is-lepas" wire:click="$set('tag', '')"><i class="bi bi-x-lg"></i> Lepas</button>
                    @endif
                </div>
            @endif

            @if ($ketKategori)
                <p class="blg-ket-kategori"><i class="bi bi-info-circle"></i> {{ $ketKategori }}</p>
            @endif

            <div class="blg-hasil" wire:loading.class="is-memuat" wire:target="search,category,tag,pilihTag,filterCategory,resetFilter,gotoPage,nextPage,previousPage">
            {{-- Sorotan: artikel utama + "Baru Terbit" --}}
            @if ($featured)
                @php
                    $rbF = \App\Support\RagamBlog::untuk($featured->category);
                    $adaSampulF = $featured->cover && \Storage::disk('public')->exists('img/blog/'.$featured->cover);
                @endphp
                <div class="blg-sorot {{ $sorotan->isEmpty() ? 'is-tunggal' : '' }}">
                    <a href="{{ route('blog.show', $featured->slug) }}" wire:navigate class="blg-utama" style="--kb: {{ $rbF['warna'] }}">
                        <div class="blg-sampul {{ $adaSampulF ? '' : 'is-kosong' }}">
                            @if ($adaSampulF)
                                <img src="{{ asset('storage/img/blog/' . $featured->cover) }}" alt="{{ $featured->cover_alt ?: $featured->title }}" decoding="async"
                                    onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                            @endif
                            <div class="blg-ubin"><i class="bi {{ $rbF['ikon'] }}"></i></div>
                            <span class="blg-pita"><i class="bi bi-star-fill"></i> Artikel Terbaru</span>
                        </div>
                        <div class="blg-utama-isi">
                            @if ($featured->category)
                                <span class="blg-label">{{ $featured->category }}</span>
                            @endif
                            <h2>{{ $featured->title }}</h2>
                            <p>{{ $featured->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($featured->body), 170) }}</p>
                            <div class="blg-utama-kaki">
                                <div class="blg-meta">
                                    <span><i class="bi bi-calendar3"></i>{{ optional($featured->published_at ?? $featured->created_at)->translatedFormat('d M Y') }}</span>
                                    <span><i class="bi bi-clock"></i>{{ $featured->readingMinutes() }} mnt baca</span>
                                </div>
                                <span class="blg-tombol">Baca Selengkapnya <i class="bi bi-arrow-right"></i></span>
                            </div>
                        </div>
                    </a>

                    @if ($sorotan->isNotEmpty())
                        <aside class="blg-baru">
                            <div class="blg-baru-kepala">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <div>
                                    <h3>Baru Terbit</h3>
                                    <p>Artikel terbaru setelah sorotan</p>
                                </div>
                            </div>
                            <ol class="blg-daftar">
                                @foreach ($sorotan as $post)
                                    @php
                                        $rbS = \App\Support\RagamBlog::untuk($post->category);
                                        $adaSampulS = $post->cover && \Storage::disk('public')->exists('img/blog/'.$post->cover);
                                    @endphp
                                    <li>
                                        <a href="{{ route('blog.show', $post->slug) }}" wire:navigate class="blg-baris" style="--kb: {{ $rbS['warna'] }}">
                                            <div class="blg-sampul {{ $adaSampulS ? '' : 'is-kosong' }}">
                                                @if ($adaSampulS)
                                                    <img src="{{ asset('storage/img/blog/' . $post->cover) }}" alt="{{ $post->cover_alt ?: $post->title }}" loading="lazy" decoding="async"
                                                        onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                                                @endif
                                                <div class="blg-ubin"><i class="bi {{ $rbS['ikon'] }}"></i></div>
                                            </div>
                                            <div class="blg-baris-isi">
                                                @if ($post->category)
                                                    <span class="blg-label">{{ $post->category }}</span>
                                                @endif
                                                <h4>{{ $post->title }}</h4>
                                                <div class="blg-meta">
                                                    <span><i class="bi bi-calendar3"></i>{{ optional($post->published_at ?? $post->created_at)->translatedFormat('d M Y') }}</span>
                                                    <span><i class="bi bi-clock"></i>{{ $post->readingMinutes() }} mnt</span>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                            <a href="{{ url('/shop') }}" wire:navigate class="blg-ajak">
                                <span class="blg-ajak-teks">
                                    <b>Siap dipraktikkan?</b>
                                    <small>Akun premium, tools AI &amp; cek Turnitin ada di toko.</small>
                                </span>
                                <span class="blg-tombol">Ke Shop <i class="bi bi-arrow-right"></i></span>
                            </a>
                        </aside>
                    @endif
                </div>
            @endif

            {{-- Grid --}}
            @if ($posts->isEmpty())
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
            @elseif ($lain->isNotEmpty())
                <div class="blg-bagian">
                    <div>
                        @if ($search !== '')
                            <span class="blg-alis"><i class="bi bi-search"></i> Hasil Pencarian</span>
                            <h2>“{{ $search }}”</h2>
                        @elseif ($category !== '')
                            <span class="blg-alis"><i class="bi bi-tag"></i> Topik</span>
                            <h2>{{ $category }}</h2>
                        @elseif ($featured)
                            <span class="blg-alis"><i class="bi bi-journal-richtext"></i> Artikel Lainnya</span>
                            <h2>Lanjutkan Membaca</h2>
                        @else
                            <span class="blg-alis"><i class="bi bi-journal-richtext"></i> Semua Artikel</span>
                            <h2>Halaman {{ $posts->currentPage() }}</h2>
                        @endif
                    </div>
                    <span class="blg-jumlah"><i class="bi bi-collection"></i><b>{{ $posts->total() }}</b> artikel</span>
                </div>

                <div class="blg-grid">
                    @foreach ($lain as $post)
                        @php
                            $rb = \App\Support\RagamBlog::untuk($post->category);
                            $adaSampul = $post->cover && \Storage::disk('public')->exists('img/blog/'.$post->cover);
                        @endphp
                        <a href="{{ route('blog.show', $post->slug) }}" wire:navigate class="blg-kartu" style="--kb: {{ $rb['warna'] }}">
                            <div class="blg-sampul {{ $adaSampul ? '' : 'is-kosong' }}">
                                @if ($adaSampul)
                                    <img src="{{ asset('storage/img/blog/' . $post->cover) }}" alt="{{ $post->cover_alt ?: $post->title }}" loading="lazy" decoding="async"
                                        onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                                @endif
                                <div class="blg-ubin"><i class="bi {{ $rb['ikon'] }}"></i></div>
                                @if ($post->category)
                                    <span class="blg-lencana blg-label">{{ $post->category }}</span>
                                @endif
                            </div>
                            <div class="blg-kartu-isi">
                                <h3>{{ $post->title }}</h3>
                                <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 115) }}</p>
                                <div class="blg-kartu-kaki">
                                    <div class="blg-meta">
                                        <span><i class="bi bi-calendar3"></i>{{ optional($post->published_at ?? $post->created_at)->translatedFormat('d M Y') }}</span>
                                        <span><i class="bi bi-clock"></i>{{ $post->readingMinutes() }} mnt</span>
                                    </div>
                                    <span class="blg-panah"><i class="bi bi-arrow-right"></i></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($posts->hasPages())
                <div class="mt-5 d-flex justify-content-center">
                    {{ $posts->links('vendor.pagination') }}
                </div>
            @endif
            </div>
        </div>
    </section>
</div>
