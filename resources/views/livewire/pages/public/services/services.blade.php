@section('title')
    Layanan Teknologi | Phoenix Digital
@endsection

<main class="main ly-page">
    <style>
        /* ===== Halaman Layanan =====
           Bahasa visual sama dengan Shop, Bundling, dan detail produk: kartu
           judul bersama (.ph-page-title), kartu putih bersudut 18px dengan
           warna per kartu (--c), ubin ikon berwarna, dan sapuan pojok seperti
           Cara Pesan. Kelas ly-*: aturan .svc-* di public-custom-styles.css
           server beku, jadi desain ini tidak bergantung padanya. */
        .ly-page { --ly-ink: #1c1f26; --ly-muted: #64748b; --ly-line: #eceff4; --ly-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .ly-section { padding: 18px 0 64px; }
        .ly-blok { margin-top: 60px; scroll-margin-top: 110px; }

        /* Ubin ikon bersama — glif tunggal selalu display:block + line-height:1
           supaya benar-benar di tengah ubinnya. */
        .ly-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 14px; font-size: 1.15rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c);
        }
        .ly-ubin.is-padat {
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff)); color: #fff;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .ly-ubin.is-besar { width: 64px; height: 64px; border-radius: 20px; font-size: 1.65rem; }
        .ly-ubin i.bi, .ly-ubin i.bi::before { display: block; line-height: 1; }

        /* Kepala tiap bagian */
        .ly-kepala { margin-bottom: 22px; }
        .ly-kepala h2 {
            margin: 10px 0 6px; font-family: var(--ly-font); font-weight: 800; letter-spacing: -.02em;
            font-size: clamp(1.35rem, 1.05rem + 1.1vw, 1.8rem); color: var(--ly-ink);
        }
        .ly-kepala p { margin: 0; max-width: 640px; color: var(--ly-muted); font-size: .95rem; line-height: 1.65; }
        .ly-kepala p b { color: #334155; }

        /* Tombol */
        .ly-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 46px; padding: 0 20px; border-radius: 13px; border: 1.5px solid transparent;
            font-weight: 700; font-size: .9rem; text-decoration: none; white-space: nowrap;
            transition: background .18s, border-color .18s, color .18s, transform .18s, filter .18s;
        }
        .ly-btn i.bi, .ly-btn i.bi::before { display: block; line-height: 1; font-size: 1.05rem; }
        .ly-btn:hover { transform: translateY(-1px); }
        .ly-btn.is-wa { background: #16a34a; color: #fff; box-shadow: 0 12px 22px -12px rgba(22, 163, 74, .85); }
        .ly-btn.is-wa:hover { background: #15803d; color: #fff; }
        .ly-btn.is-utama {
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            box-shadow: 0 12px 22px -12px rgba(242, 101, 34, .8);
        }
        .ly-btn.is-utama:hover { color: #fff; filter: brightness(1.05); }
        .ly-btn.is-garis { background: #fff; color: var(--ly-ink); border-color: #e5e0d8; }
        .ly-btn.is-garis:hover { border-color: #f26522; color: #c2410c; }
        .ly-btn.is-garis .bi-whatsapp { color: #16a34a; }

        /* ===== Kartu pembuka ===== */
        .ly-intro {
            display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 28px; align-items: center;
            padding: 30px 32px; border: 1px solid #f7dcc6; border-radius: 22px; overflow: hidden;
            background:
                radial-gradient(60% 90% at 100% 0%, rgba(251, 169, 25, .14), transparent 60%),
                linear-gradient(160deg, #fff7ef 0%, #fff 72%);
        }
        .ly-intro h2 {
            margin: 0 0 10px; font-family: var(--ly-font); font-weight: 800; letter-spacing: -.02em;
            font-size: clamp(1.4rem, 1.1rem + 1.2vw, 1.9rem); line-height: 1.25; color: var(--ly-ink);
        }
        .ly-intro p { margin: 0; color: #4b5563; font-size: .95rem; line-height: 1.7; }
        .ly-intro p b { color: #c2410c; }
        .ly-intro-aksi { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
        .ly-sorot { display: grid; gap: 10px; }
        .ly-sorot-item {
            display: flex; align-items: center; gap: 13px; padding: 12px 14px;
            background: #fff; border: 1px solid var(--ly-line); border-radius: 16px;
            box-shadow: 0 12px 26px -22px rgba(15, 23, 42, .4);
        }
        .ly-sorot-item b { display: block; font-family: var(--ly-font); font-weight: 800; font-size: .95rem; color: var(--ly-ink); line-height: 1.3; }
        .ly-sorot-item small { display: block; margin-top: 2px; font-size: .79rem; color: var(--ly-muted); }

        /* ===== Kartu layanan — sama dengan kartu produk di Shop ===== */
        .ly-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
        .ly-kartu {
            position: relative; display: flex; flex-direction: column; min-width: 0;
            background: #fff; border: 1px solid var(--ly-line); border-radius: 18px; overflow: hidden;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .ly-kartu:hover {
            transform: translateY(-4px); border-color: color-mix(in srgb, var(--c) 35%, #fff);
            box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--c) 75%, transparent);
        }
        .ly-media {
            position: relative; display: flex; align-items: center; justify-content: center; height: 156px; overflow: hidden;
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 11%, #fff) 0%, #fff 78%);
        }
        .ly-media::before {
            content: ""; position: absolute; top: -44px; right: -44px; width: 132px; height: 132px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent); transition: transform .35s ease;
        }
        .ly-kartu:hover .ly-media::before { transform: scale(1.3); }
        .ly-media .ly-ubin { position: relative; transition: transform .35s ease; }
        .ly-kartu:hover .ly-media .ly-ubin { transform: scale(1.06) rotate(-4deg); }
        .ly-bonus {
            position: absolute; left: 12px; bottom: 12px; z-index: 1;
            display: inline-flex; align-items: center; gap: 6px; max-width: calc(100% - 24px);
            height: 28px; padding: 0 11px 0 9px; border-radius: 99px;
            background: rgba(255, 255, 255, .94); border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a); font-size: .72rem; font-weight: 700; white-space: nowrap;
        }
        .ly-bonus span { overflow: hidden; text-overflow: ellipsis; }
        .ly-bonus i.bi, .ly-bonus i.bi::before { display: block; line-height: 1; }
        .ly-isi { display: flex; flex-direction: column; flex: 1 1 auto; padding: 16px 18px 18px; }
        .ly-judul { margin: 0; font-family: var(--ly-font); font-weight: 800; font-size: 1.08rem; line-height: 1.35; letter-spacing: -.01em; color: var(--ly-ink); }
        .ly-desk { margin: 6px 0 0; color: var(--ly-muted); font-size: .87rem; line-height: 1.6; }
        .ly-fitur { list-style: none; margin: 14px 0 0; padding: 0; display: grid; gap: 8px; }
        .ly-fitur li { display: flex; align-items: flex-start; gap: 9px; font-size: .85rem; line-height: 1.45; color: #334155; }
        .ly-cek {
            width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px; border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 13%, #fff); color: var(--c); font-size: .72rem;
        }
        .ly-cek i.bi, .ly-cek i.bi::before { display: block; line-height: 1; }
        .ly-kaki {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            margin-top: auto; padding-top: 14px; border-top: 1px dashed #e8ecf2;
        }
        .ly-kartu .ly-fitur { margin-bottom: 16px; }
        .ly-harga small { display: block; font-size: .66rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #64748b; }
        .ly-harga b { font-family: var(--ly-font); font-weight: 800; font-size: 1.14rem; color: var(--ly-ink); white-space: nowrap; }
        .ly-pesan {
            display: inline-flex; align-items: center; gap: 7px; height: 42px; padding: 0 16px; border-radius: 12px;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .86rem; text-decoration: none; white-space: nowrap;
            box-shadow: 0 10px 20px -12px rgba(242, 101, 34, .7); transition: filter .16s ease, transform .16s ease;
        }
        .ly-pesan:hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .ly-pesan i.bi, .ly-pesan i.bi::before { display: block; line-height: 1; }

        /* ===== Sudah termasuk ===== */
        .ly-termasuk { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .ly-poin, .ly-lk {
            position: relative; display: flex; flex-direction: column; gap: 12px; padding: 18px;
            background: #fff; border: 1px solid var(--ly-line); border-radius: 18px; overflow: hidden;
            transition: border-color .22s ease, box-shadow .22s ease;
        }
        .ly-poin::before, .ly-lk::before {
            content: ""; position: absolute; top: -34px; right: -34px; width: 96px; height: 96px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 10%, transparent); transition: transform .35s ease;
        }
        .ly-poin:hover, .ly-lk:hover { border-color: color-mix(in srgb, var(--c) 32%, #fff); box-shadow: 0 16px 30px -24px color-mix(in srgb, var(--c) 80%, transparent); }
        .ly-poin:hover::before, .ly-lk:hover::before { transform: scale(1.25); }
        .ly-poin > *, .ly-lk > * { position: relative; }
        .ly-poin b, .ly-lk b { display: block; font-family: var(--ly-font); font-weight: 800; font-size: .94rem; line-height: 1.35; color: var(--ly-ink); }
        .ly-poin small { display: block; margin-top: 3px; font-size: .8rem; line-height: 1.5; color: var(--ly-muted); }

        /* ===== Paket website ===== */
        .ly-paket { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; align-items: start; padding-top: 12px; }
        .ly-pkt {
            position: relative; display: flex; flex-direction: column; padding: 24px 22px 22px;
            background: #fff; border: 1px solid var(--ly-line); border-radius: 20px;
            transition: border-color .22s ease, box-shadow .22s ease;
        }
        .ly-pkt:hover { border-color: color-mix(in srgb, var(--c) 35%, #fff); box-shadow: 0 20px 40px -30px color-mix(in srgb, var(--c) 80%, transparent); }
        .ly-pkt.is-populer {
            border: 2px solid #f26522; background: linear-gradient(180deg, #fff7ef 0%, #fff 34%);
            box-shadow: 0 26px 50px -30px rgba(242, 101, 34, .75);
        }
        .ly-pkt-lencana {
            position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
            display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 13px; border-radius: 99px;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-size: .72rem; font-weight: 800; letter-spacing: .04em; white-space: nowrap;
            box-shadow: 0 8px 16px -8px rgba(242, 101, 34, .85);
        }
        .ly-pkt-lencana i.bi, .ly-pkt-lencana i.bi::before { display: block; line-height: 1; }
        .ly-pkt-atas { display: flex; align-items: center; gap: 12px; }
        .ly-pkt-nama { margin: 0; font-family: var(--ly-font); font-weight: 800; font-size: 1.08rem; line-height: 1.3; color: var(--ly-ink); }
        .ly-pkt-untuk { display: block; margin-top: 2px; font-size: .78rem; color: var(--ly-muted); }
        .ly-pkt-harga { display: flex; align-items: center; flex-wrap: wrap; gap: 6px 10px; margin-top: 18px; }
        .ly-pkt-harga s { color: #64748b; font-size: .85rem; }
        .ly-pkt-hemat {
            display: inline-flex; align-items: center; height: 22px; padding: 0 8px; border-radius: 99px;
            background: #dcfce7; color: #15803d; font-size: .7rem; font-weight: 800;
        }
        .ly-pkt-now { display: flex; align-items: baseline; gap: 6px; margin-top: 4px; }
        .ly-pkt-now small { font-size: .7rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #64748b; }
        .ly-pkt-now b { font-family: var(--ly-font); font-weight: 800; font-size: 1.75rem; letter-spacing: -.02em; color: var(--ly-ink); }
        .ly-pkt.is-populer .ly-pkt-now b { color: #ea580c; }
        .ly-pkt .ly-fitur { margin-top: 18px; padding-top: 18px; border-top: 1px dashed #e8ecf2; }
        .ly-lebih summary {
            list-style: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin-top: 12px;
            font-size: .8rem; font-weight: 700; color: color-mix(in srgb, var(--c) 80%, #0f172a);
        }
        .ly-lebih summary::-webkit-details-marker { display: none; }
        .ly-lebih summary i.bi, .ly-lebih summary i.bi::before { display: block; line-height: 1; transition: transform .2s ease; }
        .ly-lebih[open] summary i.bi { transform: rotate(180deg); }
        .ly-lebih .ly-fitur { margin-top: 10px; padding-top: 0; border-top: 0; }
        .ly-pkt-cocok {
            display: flex; align-items: center; gap: 8px; margin-top: 18px; padding: 10px 12px; border-radius: 12px;
            background: color-mix(in srgb, var(--c) 7%, #fff); color: #334155; font-size: .8rem; line-height: 1.4;
        }
        .ly-pkt-cocok i.bi, .ly-pkt-cocok i.bi::before { display: block; line-height: 1; color: var(--c); }
        .ly-pkt .ly-btn { width: 100%; margin-top: 14px; }

        /* ===== Alur kerja (bergaya Cara Pesan) ===== */
        .ly-langkah { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .ly-lk-atas { display: flex; align-items: center; justify-content: space-between; }
        /* Nomor besar memakai warna penuh kartunya: teks besar butuh kontras
           ≥ 3 : 1, dan versi pucatnya dulu hanya 1,4 : 1. */
        .ly-lk-no { font-family: var(--ly-font); font-weight: 800; font-size: 1.7rem; line-height: 1; color: var(--c); }
        .ly-lk p { margin: -4px 0 0; font-size: .84rem; line-height: 1.6; color: var(--ly-muted); }

        /* ===== Perbandingan ===== */
        .ly-banding {
            background: #fff; border: 1px solid var(--ly-line); border-radius: 20px; overflow: hidden;
            box-shadow: 0 18px 40px -34px rgba(15, 23, 42, .45);
        }
        .ly-bd-baris {
            display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr) minmax(0, 1fr);
            align-items: center; gap: 16px; padding: 13px 22px; font-size: .87rem;
        }
        .ly-bd-baris + .ly-bd-baris { border-top: 1px solid #f1f5f9; }
        .ly-bd-baris:nth-child(even):not(.ly-bd-kepala) { background: #fcfcfd; }
        .ly-bd-kepala { padding-block: 14px; background: #f8fafc; font-size: .7rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: #64748b; }
        .ly-bd-kepala .ly-bd-kami { color: #c2410c; }
        .ly-bd-fitur { font-weight: 700; color: var(--ly-ink); }
        .ly-bd-kami, .ly-bd-mereka { display: flex; align-items: center; gap: 10px; line-height: 1.4; min-width: 0; }
        .ly-bd-kami { color: #166534; font-weight: 600; }
        /* Kolom pembanding sengaja lebih redup dari kolom kami, tetapi tetap
           terbaca (kontras ≥ 4,5 : 1) — redupnya dibawa ikon, bukan teksnya. */
        .ly-bd-mereka { color: #64748b; }
        .ly-tanda { width: 22px; height: 22px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .7rem; }
        .ly-tanda.is-ya { background: #16a34a; color: #fff; }
        .ly-tanda.is-tidak { background: #f1f5f9; color: #94a3b8; }
        .ly-tanda i.bi, .ly-tanda i.bi::before { display: block; line-height: 1; }
        .ly-bd-label { display: none; }

        /* ===== Ajakan penutup ===== */
        .ly-cta {
            position: relative; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px 28px;
            padding: 28px 32px; border-radius: 22px; overflow: hidden; color: #fff;
            background:
                radial-gradient(70% 130% at 100% 0%, rgba(251, 169, 25, .35), transparent 60%),
                linear-gradient(135deg, #23272f 0%, #3a2a20 100%);
        }
        .ly-cta-kiri { display: flex; align-items: center; gap: 16px; flex: 1 1 380px; min-width: 0; }
        .ly-cta .ly-ubin { background: rgba(251, 169, 25, .16); color: #fbbf24; box-shadow: inset 0 0 0 1px rgba(251, 191, 36, .25); }
        .ly-cta h3 { margin: 0 0 4px; font-family: var(--ly-font); font-weight: 800; font-size: 1.3rem; color: #fff; }
        .ly-cta p { margin: 0; color: rgba(255, 255, 255, .78); font-size: .92rem; line-height: 1.6; }
        .ly-catatan {
            display: flex; align-items: center; gap: 12px; margin: 16px 0 0; padding: 12px 14px; border-radius: 14px;
            background: #f8fafc; border: 1px solid var(--ly-line); color: #475569; font-size: .84rem; line-height: 1.6;
        }
        .ly-catatan .ly-ubin { width: 30px; height: 30px; border-radius: 10px; font-size: .9rem; }

        @media (max-width: 991.98px) {
            .ly-intro { grid-template-columns: minmax(0, 1fr); }
            .ly-grid, .ly-termasuk, .ly-langkah { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ly-paket { grid-template-columns: minmax(0, 1fr); max-width: 560px; margin-inline: auto; gap: 26px; }
        }
        @media (min-width: 992px) {
            .ly-pkt.is-populer { transform: translateY(-10px); }
        }
        @media (max-width: 767.98px) {
            /* Tabel jadi tumpukan kartu: tiap baris = fitur, lalu dua jawaban berlabel. */
            .ly-bd-kepala { display: none; }
            .ly-bd-baris { grid-template-columns: minmax(0, 1fr); gap: 8px; padding: 14px 16px; }
            .ly-bd-label { display: block; font-size: .66rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
            .ly-bd-kami .ly-bd-label { color: #c2410c; }
            .ly-bd-kami > span:last-child, .ly-bd-mereka > span:last-child { display: flex; flex-direction: column; }
        }
        @media (max-width: 575.98px) {
            .ly-blok { margin-top: 44px; }
            .ly-intro { padding: 22px 18px; gap: 20px; }
            .ly-intro-aksi .ly-btn { flex: 1 1 auto; }
            .ly-grid { grid-template-columns: minmax(0, 1fr); gap: 14px; }
            .ly-termasuk { gap: 10px; }
            .ly-poin { padding: 14px; }
            .ly-poin b { font-size: .86rem; }
            .ly-langkah { grid-template-columns: minmax(0, 1fr); }
            .ly-cta { padding: 22px 18px; }
            .ly-cta .ly-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ly-kartu, .ly-kartu:hover, .ly-btn:hover, .ly-pesan:hover { transform: none; }
            .ly-media::before, .ly-media .ly-ubin, .ly-poin::before, .ly-lk::before { transition: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Layanan Teknologi</span>
                <h1>Layanan</h1>
                <p>Website, aplikasi, konten, dan sistem digital untuk bisnis &amp; instansi Anda.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Beranda</a></li>
                    <li class="current">Layanan</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="ly-section">
        <div class="container">

            {{-- Kartu pembuka --}}
            <div class="ly-intro">
                <div>
                    <h2>Solusi digital untuk bisnis &amp; instansi Anda</h2>
                    <p>Selain menjual akun premium &amp; tools AI, Phoenix Digital juga mengerjakan pengembangan
                        teknologi — dari website hingga aplikasi. Harga <b>mulai Rp 500.000-an</b>, menyesuaikan kebutuhan.</p>
                    <div class="ly-intro-aksi">
                        <a class="ly-btn is-wa" href="{{ $waKonsultasi }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i> Konsultasi Gratis
                        </a>
                        <a class="ly-btn is-garis" href="#paket-website">
                            <i class="bi bi-window-stack"></i> Lihat Paket Website
                        </a>
                    </div>
                </div>
                <div class="ly-sorot">
                    <div class="ly-sorot-item" style="--c: #16a34a">
                        <span class="ly-ubin"><i class="bi bi-cash-coin"></i></span>
                        <span><b>Mulai Rp 500.000-an</b><small>Harga menyesuaikan kebutuhan</small></span>
                    </div>
                    <div class="ly-sorot-item" style="--c: #2563eb">
                        <span class="ly-ubin"><i class="bi bi-globe2"></i></span>
                        <span><b>Domain &amp; hosting gratis</b><small>1 tahun untuk website &amp; aplikasi</small></span>
                    </div>
                    <div class="ly-sorot-item" style="--c: #7c3aed">
                        <span class="ly-ubin"><i class="bi bi-file-earmark-code"></i></span>
                        <span><b>Source code milik Anda</b><small>Tanpa terkunci ke vendor</small></span>
                    </div>
                </div>
            </div>

            {{-- Layanan --}}
            <div class="ly-blok" id="pilih-layanan">
                <div class="ly-kepala">
                    <span class="ph-sec-eyebrow"><i class="bi bi-grid-1x2-fill"></i> Pilih Layanan</span>
                    <h2>Apa yang bisa kami kerjakan</h2>
                    <p>Pilih layanan yang Anda butuhkan — pemesanan dan konsultasi langsung lewat WhatsApp.</p>
                </div>
                <div class="ly-grid">
                    @foreach ($layanan as $l)
                        <article class="ly-kartu" style="--c: {{ $l['warna'] }}">
                            <div class="ly-media">
                                <span class="ly-ubin is-padat is-besar"><i class="bi {{ $l['ikon'] }}"></i></span>
                                <span class="ly-bonus"><i class="bi bi-gift-fill"></i><span>{{ $l['bonus'] }}</span></span>
                            </div>
                            <div class="ly-isi">
                                <h3 class="ly-judul">{{ $l['judul'] }}</h3>
                                <p class="ly-desk">{{ $l['desk'] }}</p>
                                <ul class="ly-fitur">
                                    @foreach ($l['fitur'] as $f)
                                        <li><span class="ly-cek"><i class="bi bi-check-lg"></i></span> {{ $f }}</li>
                                    @endforeach
                                </ul>
                                <div class="ly-kaki">
                                    <span class="ly-harga"><small>Mulai</small><b>{{ $l['harga'] }}</b></span>
                                    <a class="ly-pesan" href="{{ $l['wa'] }}" target="_blank" rel="noopener">
                                        <i class="bi bi-whatsapp"></i> Pesan
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            {{-- Sudah termasuk --}}
            <div class="ly-blok">
                <div class="ly-kepala">
                    <span class="ph-sec-eyebrow"><i class="bi bi-patch-check-fill"></i> Sudah Termasuk</span>
                    <h2>Setiap proyek sudah termasuk</h2>
                    <p>Nilai lebih yang Anda dapatkan <b>tanpa biaya tambahan</b>.</p>
                </div>
                <div class="ly-termasuk">
                    @foreach ($termasuk as $t)
                        <div class="ly-poin" style="--c: {{ $t['warna'] }}">
                            <span class="ly-ubin"><i class="bi {{ $t['ikon'] }}"></i></span>
                            <span><b>{{ $t['judul'] }}</b><small>{{ $t['ket'] }}</small></span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Paket berjenjang khusus Pengembangan Website --}}
            <div class="ly-blok" id="paket-website">
                <div class="ly-kepala">
                    <span class="ph-sec-eyebrow"><i class="bi bi-window-stack"></i> Paket Website</span>
                    <h2>Paket Pengembangan Website</h2>
                    <p>Pilih paket yang paling sesuai — semua sudah termasuk <b>domain &amp; hosting gratis 1 tahun</b>.</p>
                </div>
                <div class="ly-paket">
                    @foreach ($paketWeb as $p)
                        <div class="ly-pkt {{ $p['populer'] ? 'is-populer' : '' }}" style="--c: {{ $p['warna'] }}">
                            @if ($p['populer'])
                                <span class="ly-pkt-lencana"><i class="bi bi-star-fill"></i> Terpopuler</span>
                            @endif
                            <div class="ly-pkt-atas">
                                <span class="ly-ubin is-padat"><i class="bi {{ $p['ikon'] }}"></i></span>
                                <div>
                                    <h3 class="ly-pkt-nama">{{ $p['nama'] }}</h3>
                                    <span class="ly-pkt-untuk">{{ $p['untuk'] }}</span>
                                </div>
                            </div>
                            <div class="ly-pkt-harga">
                                <s>{{ $p['lama'] }}</s>
                                @if ($p['hemat'])
                                    <span class="ly-pkt-hemat">Hemat {{ $p['hemat'] }}%</span>
                                @endif
                            </div>
                            <div class="ly-pkt-now"><small>Mulai</small><b>{{ $p['harga'] }}</b></div>
                            <ul class="ly-fitur">
                                @foreach ($p['fiturUtama'] as $f)
                                    <li><span class="ly-cek"><i class="bi bi-check-lg"></i></span> {{ $f }}</li>
                                @endforeach
                            </ul>
                            @if ($p['fiturLain'])
                                <details class="ly-lebih">
                                    <summary>Lihat {{ count($p['fiturLain']) }} fitur lainnya <i class="bi bi-chevron-down"></i></summary>
                                    <ul class="ly-fitur">
                                        @foreach ($p['fiturLain'] as $f)
                                            <li><span class="ly-cek"><i class="bi bi-check-lg"></i></span> {{ $f }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                            <div class="ly-pkt-cocok"><i class="bi bi-bullseye"></i> <span>Cocok untuk: <b>{{ $p['untuk'] }}</b></span></div>
                            <a class="ly-btn {{ $p['populer'] ? 'is-utama' : 'is-garis' }}" href="{{ $p['wa'] }}" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp"></i> Pilih Paket
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Alur kerja --}}
            <div class="ly-blok">
                <div class="ly-kepala">
                    <span class="ph-sec-eyebrow"><i class="bi bi-signpost-split-fill"></i> Alur Kerja</span>
                    <h2>Dari konsultasi sampai serah terima</h2>
                    <p>Empat langkah sederhana — Anda selalu tahu proyeknya sudah sampai mana.</p>
                </div>
                <div class="ly-langkah">
                    @foreach ($langkah as $i => $s)
                        <div class="ly-lk" style="--c: {{ $s['warna'] }}">
                            <div class="ly-lk-atas">
                                <span class="ly-ubin is-padat"><i class="bi {{ $s['ikon'] }}"></i></span>
                                <span class="ly-lk-no">{{ sprintf('%02d', $i + 1) }}</span>
                            </div>
                            <b>{{ $s['judul'] }}</b>
                            <p>{{ $s['ket'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pembeda dari vendor lain --}}
            <div class="ly-blok">
                <div class="ly-kepala">
                    <span class="ph-sec-eyebrow"><i class="bi bi-award-fill"></i> Keunggulan Kami</span>
                    <h2>Kenapa Phoenix Digital berbeda?</h2>
                    <p>Semua keunggulan ini <b>sudah termasuk dalam harga</b> — tanpa biaya tersembunyi, tanpa tambahan mendadak.</p>
                </div>
                <div class="ly-banding" role="table" aria-label="Perbandingan Phoenix Digital dengan vendor umumnya">
                    <div class="ly-bd-baris ly-bd-kepala" role="row">
                        <span class="ly-bd-fitur" role="columnheader">Yang Anda dapatkan</span>
                        <span class="ly-bd-kami" role="columnheader">Phoenix Digital</span>
                        <span class="ly-bd-mereka" role="columnheader">Vendor umumnya</span>
                    </div>
                    @foreach ($perbandingan as $d)
                        <div class="ly-bd-baris" role="row">
                            <span class="ly-bd-fitur" role="cell">{{ $d[0] }}</span>
                            <span class="ly-bd-kami" role="cell">
                                <span class="ly-tanda is-ya"><i class="bi bi-check-lg"></i></span>
                                <span><em class="ly-bd-label">Phoenix Digital</em>{{ $d[1] }}</span>
                            </span>
                            <span class="ly-bd-mereka" role="cell">
                                <span class="ly-tanda is-tidak"><i class="bi bi-x-lg"></i></span>
                                <span><em class="ly-bd-label">Vendor umumnya</em>{{ $d[2] }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Ajakan penutup --}}
            <div class="ly-blok">
                <div class="ly-cta">
                    <div class="ly-cta-kiri">
                        <span class="ly-ubin is-besar"><i class="bi bi-lightbulb"></i></span>
                        <div>
                            <h3>Punya kebutuhan khusus?</h3>
                            <p>Ceritakan proyek Anda — kami bantu carikan solusi &amp; estimasi harga terbaik.</p>
                        </div>
                    </div>
                    <a class="ly-btn is-wa" href="{{ $waProyek }}" target="_blank" rel="noopener">
                        <i class="bi bi-whatsapp"></i> Hubungi Admin
                    </a>
                </div>
                <p class="ly-catatan" style="--c: #64748b">
                    <span class="ly-ubin"><i class="bi bi-info-lg"></i></span>
                    <span>Harga di atas adalah harga <b>mulai</b>; biaya akhir menyesuaikan cakupan, fitur, dan tingkat kerumitan proyek.</span>
                </p>
            </div>
        </div>
    </section>
</main>
