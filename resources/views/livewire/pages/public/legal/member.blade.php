@section('title')
    Keuntungan & Syarat Member | Phoenix Digital
@endsection

<main class="main mbr-page">
    <style>
        /* ===== Halaman Member =====
           Bahasa visualnya mengikuti BERANDA, bukan halaman hukum: kepala
           bagian rata tengah (.ph-sec-head), jalur langkah putus-putus dengan
           bulatan bernomor seperti "Cara Pesan", kartu bersapuan warna di
           pojok, dan latar seksi berselang-seling supaya berirama.

           Kelas mbr-*: gaya .legal-*/.lg-* dipakai bersama Syarat, Privasi,
           dan FAQ (sebagian beku di server), jadi halaman ini berdiri sendiri. */
        .mbr-page { --mbr-ink: #1c1f26; --mbr-muted: #6b7280; --mbr-line: #eceff3; --mbr-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .mbr-sec { padding: 46px 0; }
        .mbr-sec.is-pita { background: linear-gradient(180deg, #fff8f1 0%, #fffdfb 100%); border-block: 1px solid #f7e7d7; }
        /* Pita teratas: kartu judul di atasnya berlatar PUTIH, jadi pita krem
           inilah yang memisahkan keduanya — tanpa itu blok putih penuh-lebar
           menyambung tepat di bawah kartu dan keduanya terbaca menyatu. */
        .mbr-sec.is-atas.is-pita { background: linear-gradient(180deg, #fff4e9 0%, #fffaf4 100%); }
        /* Strip sorotan diberi jarak lebih lega dari kartu judul: keduanya
           sama-sama kartu putih, jadi dengan jarak sempit mereka terbaca
           sebagai satu blok yang menempel. */
        .mbr-sec.is-atas { padding-top: 40px; }
        .mbr-sec .ph-sec-head { margin-bottom: 28px; }

        /* Ubin ikon — glif tunggal selalu display:block + line-height:1 */
        .mbr-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 14px; font-size: 1.15rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 85%, #0f172a);
            transition: background .22s ease, color .22s ease;
        }
        .mbr-ubin.is-padat {
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff)); color: #fff;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .mbr-ubin.is-kecil { width: 34px; height: 34px; border-radius: 11px; font-size: .95rem; }
        .mbr-ubin i.bi, .mbr-ubin i.bi::before { display: block; line-height: 1; }

        /* ===== Panel pembuka: jangkar visual halaman =====
           Halaman ini seluruhnya teks, sementara Beranda/Shop/Bundling punya
           gambar produk sebagai jangkar mata. Panel bergradasi dengan tiruan
           KARTU MEMBER memberi halaman ini jangkarnya sendiri. Dasarnya jingga
           TUA: putih di atas jingga terang hanya 3,1 : 1. */
        .mbr-panel {
            position: relative; display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr);
            gap: 30px; align-items: center; padding: 32px 34px; border-radius: 24px; overflow: hidden; color: #fff;
            background:
                radial-gradient(80% 120% at 100% 0%, rgba(255, 255, 255, .16), transparent 58%),
                linear-gradient(135deg, rgba(255, 255, 255, .1) 0%, rgba(255, 255, 255, 0) 55%),
                #c2410c;
            box-shadow: 0 26px 50px -30px rgba(194, 65, 12, .95);
        }
        /* Titik-titik samar, bahasa yang sama dengan kartu judul bersama */
        .mbr-panel::after {
            content: ""; position: absolute; inset: 0; z-index: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255, 255, 255, .16) 1px, transparent 1px);
            background-size: 18px 18px;
            -webkit-mask-image: linear-gradient(250deg, #000 0%, transparent 62%);
            mask-image: linear-gradient(250deg, #000 0%, transparent 62%);
        }
        .mbr-panel > * { position: relative; z-index: 1; }
        .mbr-panel-label {
            display: inline-flex; align-items: center; gap: 8px; height: 28px; padding: 0 12px 0 6px; border-radius: 99px;
            background: rgba(255, 255, 255, .16); color: #fff; font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        }
        .mbr-panel-label .mbr-ubin { width: 20px; height: 20px; border-radius: 7px; background: #fff; color: #c2410c; font-size: .64rem; }
        .mbr-panel h2 { margin: 12px 0 8px; font-family: var(--mbr-font); font-weight: 800; letter-spacing: -.02em; line-height: 1.22; color: #fff; font-size: clamp(1.5rem, 1.1rem + 1.5vw, 2.1rem); }
        .mbr-panel p { margin: 0; font-size: .95rem; line-height: 1.7; color: rgba(255, 255, 255, .92); }
        .mbr-panel-aksi { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }

        /* Tiruan kartu member */
        .mbr-kartu-member {
            position: relative; overflow: hidden; border-radius: 20px; padding: 22px 22px 20px;
            background: linear-gradient(140deg, #2b2118 0%, #3d2a1c 55%, #23272f 100%);
            box-shadow: 0 24px 44px -24px rgba(0, 0, 0, .7); transform: rotate(-1.4deg);
            transition: transform .35s ease;
        }
        .mbr-kartu-member:hover { transform: rotate(0deg) translateY(-4px); }
        .mbr-kartu-member::before {
            content: ""; position: absolute; top: -70px; right: -50px; width: 190px; height: 190px; border-radius: 50%;
            background: radial-gradient(circle, rgba(251, 169, 25, .42) 0%, transparent 68%);
        }
        .mbr-km-atas { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .mbr-km-merek { display: flex; align-items: center; gap: 9px; }
        .mbr-km-merek img { width: 30px; height: auto; display: block; }
        .mbr-km-merek span { font-family: var(--mbr-font); font-weight: 800; font-size: .82rem; letter-spacing: .12em; text-transform: uppercase; color: #fff; }
        .mbr-km-pita {
            display: inline-flex; align-items: center; height: 24px; padding: 0 10px; border-radius: 99px;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-size: .68rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
        }
        .mbr-km-isi { position: relative; margin-top: 26px; display: grid; gap: 4px; }
        .mbr-km-label { font-size: .66rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: rgba(255, 255, 255, .62); }
        .mbr-km-nama { font-family: var(--mbr-font); font-weight: 800; font-size: 1.08rem; color: #fff; }
        .mbr-km-baris { position: relative; display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; margin-top: 18px; }
        .mbr-km-poin { font-family: var(--mbr-font); font-weight: 800; font-size: 1.35rem; line-height: 1; color: #fbbf24; }
        .mbr-km-kode {
            display: inline-flex; align-items: center; gap: 7px; height: 30px; padding: 0 12px; border-radius: 10px;
            background: rgba(255, 255, 255, .1); border: 1px dashed rgba(255, 255, 255, .34);
            font-size: .78rem; font-weight: 700; letter-spacing: .08em; color: rgba(255, 255, 255, .92);
        }
        .mbr-km-kode i.bi, .mbr-km-kode i.bi::before { display: block; line-height: 1; font-size: .82rem; }
        .mbr-km-catatan { position: relative; margin: 12px 0 0; font-size: .72rem; color: rgba(255, 255, 255, .6); text-align: center; }

        /* ===== Penghitung poin ===== */
        .mbr-kalk {
            display: grid; gap: 12px; margin-bottom: 22px; padding: 20px 22px; border-radius: 18px;
            background: #fff; border: 1px solid var(--mbr-line); box-shadow: 0 16px 34px -30px rgba(15, 23, 42, .55);
        }
        .mbr-kalk-label { font-size: .82rem; font-weight: 700; color: #334155; }
        .mbr-kalk-kolom { position: relative; display: flex; align-items: center; }
        .mbr-kalk-rp { position: absolute; left: 16px; font-weight: 800; font-size: .95rem; color: #64748b; pointer-events: none; }
        .mbr-kalk-kolom input {
            width: 100%; height: 52px; padding: 0 16px 0 46px; border: 1.5px solid #e8ecf2; border-radius: 14px;
            background: #fff; color: #0f172a; font-family: var(--mbr-font); font-weight: 800; font-size: 1.15rem;
            outline: none; transition: border-color .18s ease, box-shadow .18s ease;
        }
        .mbr-kalk-kolom input:focus { border-color: #f26522; box-shadow: 0 0 0 4px rgba(242, 101, 34, .14); }
        .mbr-kalk-cepat { display: flex; flex-wrap: wrap; gap: 8px; }
        .mbr-kalk-cepat button {
            height: 34px; padding: 0 13px; border-radius: 99px; border: 1px solid var(--mbr-line); background: #fff;
            color: #475569; font-size: .79rem; font-weight: 700; cursor: pointer;
            transition: border-color .18s ease, color .18s ease, background .18s ease;
        }
        .mbr-kalk-cepat button:hover { border-color: #f26522; color: #c2410c; background: #fff7ef; }

        /* ===== Strip sorotan ===== */
        .mbr-sorot { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .mbr-sorot-item {
            position: relative; display: flex; align-items: center; gap: 13px; padding: 16px 18px; overflow: hidden;
            background: #fff; border: 1px solid var(--mbr-line); border-radius: 16px;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .mbr-sorot-item::before {
            content: ""; position: absolute; top: -34px; right: -34px; width: 96px; height: 96px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent); transition: transform .3s ease;
        }
        .mbr-sorot-item:hover { transform: translateY(-3px); border-color: color-mix(in srgb, var(--c) 35%, #fff); box-shadow: 0 12px 28px color-mix(in srgb, var(--c) 18%, transparent); }
        .mbr-sorot-item:hover::before { transform: scale(1.35); }
        .mbr-sorot-item > * { position: relative; }
        .mbr-sorot-item b { display: block; font-family: var(--mbr-font); font-weight: 800; font-size: 1rem; color: var(--mbr-ink); line-height: 1.3; }
        .mbr-sorot-item small { display: block; margin-top: 2px; font-size: .8rem; color: var(--mbr-muted); }

        /* Pita lompat bagian — menggantikan daftar isi ala halaman hukum */
        .mbr-lompat { display: flex; flex-wrap: wrap; gap: 9px; justify-content: center; margin-top: 26px; }
        .mbr-lompat a {
            display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 15px 0 7px; border-radius: 99px;
            background: #fff; border: 1px solid var(--mbr-line); color: #475569;
            font-size: .85rem; font-weight: 700; text-decoration: none;
            transition: border-color .2s ease, color .2s ease, transform .2s ease;
        }
        .mbr-lompat a:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--c) 45%, #fff); color: color-mix(in srgb, var(--c) 80%, #0f172a); }
        .mbr-lompat .mbr-ubin { width: 28px; height: 28px; border-radius: 9px; font-size: .82rem; }

        /* ===== Jalur langkah (pola "Cara Pesan" di beranda) ===== */
        .mbr-deret { position: relative; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        /* Garis jalur: digambar di lapisan belakang, hanya terlihat di CELAH
           antar kartu — persis seperti .cp-deret::before di beranda. */
        .mbr-deret::before {
            content: ""; position: absolute; z-index: 0; top: 48px; left: 22%; right: 22%;
            border-top: 2px dashed #f8d8bf;
        }
        .mbr-langkah {
            position: relative; z-index: 1; overflow: hidden;
            background: #fff; border: 1px solid var(--mbr-line); border-radius: 16px; padding: 26px 22px 24px;
            transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
        }
        .mbr-langkah::before {
            content: ""; position: absolute; top: -34px; right: -34px; width: 96px; height: 96px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent); transition: transform .3s ease;
        }
        .mbr-langkah:hover { transform: translateY(-3px); border-color: color-mix(in srgb, var(--c) 35%, #fff); box-shadow: 0 12px 28px color-mix(in srgb, var(--c) 18%, transparent); }
        .mbr-langkah:hover::before { transform: scale(1.35); }
        /* Bulatan bernomor: perhentian pada jalur. Latarnya PEKAT supaya
           menutupi garis yang lewat di belakangnya. */
        .mbr-nomor {
            position: relative; z-index: 1; display: flex; align-items: center; justify-content: center;
            width: 44px; height: 44px; border-radius: 50%; margin-bottom: 16px;
            background: color-mix(in srgb, var(--c) 10%, #fff);
            border: 2px solid color-mix(in srgb, var(--c) 32%, #fff);
            /* Angkanya digelapkan & sedikit dibesarkan: var(--c) polos di atas
               latar mudanya hanya 3,6 : 1, di bawah 4,5 : 1 untuk ukuran ini. */
            color: color-mix(in srgb, var(--c) 62%, #0f172a);
            font-family: var(--mbr-font); font-weight: 800; font-size: 1.18rem; line-height: 1;
            font-variant-numeric: tabular-nums;
            transition: background .22s ease, color .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .mbr-langkah:hover .mbr-nomor { background: var(--c); color: #fff; border-color: transparent; box-shadow: 0 8px 18px color-mix(in srgb, var(--c) 32%, transparent); }
        /* Ikon menemani angka, ditaruh di pojok berhadapan supaya tidak berebut tempat */
        .mbr-ikon-pojok {
            position: absolute; top: 26px; right: 22px; z-index: 1;
            display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 10px;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 85%, #0f172a); font-size: 1rem;
        }
        .mbr-ikon-pojok i.bi, .mbr-ikon-pojok i.bi::before { display: block; line-height: 1; }
        .mbr-judul { margin: 0 0 8px; font-family: var(--mbr-font); font-weight: 700; font-size: 1.05rem; line-height: 1.3; letter-spacing: -.015em; color: var(--mbr-ink); }
        .mbr-ket { margin: 0; font-size: .89rem; line-height: 1.65; color: var(--mbr-muted); }
        .mbr-ket b { color: #334155; }

        .mbr-aktif {
            display: flex; align-items: center; gap: 14px; margin-top: 18px; padding: 18px 20px; border-radius: 18px;
            border: 1px solid color-mix(in srgb, var(--c) 22%, #eceff4);
            background: linear-gradient(135deg, color-mix(in srgb, var(--c) 10%, #fff) 0%, #fff 68%);
        }
        .mbr-aktif-teks b { display: block; font-family: var(--mbr-font); font-weight: 800; font-size: 1rem; color: var(--mbr-ink); }
        .mbr-aktif-teks span { display: block; margin-top: 3px; font-size: .87rem; line-height: 1.6; color: #475569; }

        /* ===== Keuntungan ===== */
        .mbr-untung { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
        .mbr-kartu {
            position: relative; overflow: hidden; padding: 26px 22px 24px;
            background: #fff; border: 1px solid var(--mbr-line); border-radius: 16px;
            transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
        }
        .mbr-kartu::before {
            content: ""; position: absolute; top: -34px; right: -34px; width: 96px; height: 96px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent); transition: transform .3s ease;
        }
        .mbr-kartu:hover { transform: translateY(-3px); border-color: color-mix(in srgb, var(--c) 35%, #fff); box-shadow: 0 12px 28px color-mix(in srgb, var(--c) 18%, transparent); }
        .mbr-kartu:hover::before { transform: scale(1.35); }
        .mbr-kartu:hover .mbr-ubin.is-padat { transform: scale(1.06) rotate(-4deg); }
        .mbr-kartu > * { position: relative; }
        .mbr-kartu .mbr-ubin.is-padat { transition: transform .3s ease; }
        .mbr-chip {
            display: inline-flex; align-items: center; height: 26px; padding: 0 11px; margin-top: 14px; border-radius: 99px;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 55%, #0f172a);
            font-size: .74rem; font-weight: 800;
        }

        /* ===== Alur hitungan poin ===== */
        .mbr-alur { position: relative; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
        .mbr-alur::before {
            content: ""; position: absolute; z-index: 0; top: 52px; left: 15%; right: 15%;
            border-top: 2px dashed #f8d8bf;
        }
        .mbr-alur-item {
            position: relative; z-index: 1; overflow: hidden; display: flex; flex-direction: column; align-items: center;
            gap: 7px; padding: 24px 18px 22px; text-align: center;
            background: #fff; border: 1px solid var(--mbr-line); border-radius: 16px;
            transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
        }
        .mbr-alur-item:hover { transform: translateY(-3px); border-color: color-mix(in srgb, var(--c) 35%, #fff); box-shadow: 0 12px 28px color-mix(in srgb, var(--c) 18%, transparent); }
        .mbr-alur-item.is-hasil { background: linear-gradient(160deg, color-mix(in srgb, var(--c) 10%, #fff) 0%, #fff 72%); border-color: color-mix(in srgb, var(--c) 30%, #fff); }
        .mbr-alur-item .mbr-ubin { margin-bottom: 4px; }
        .mbr-alur-label { font-size: .72rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: var(--mbr-muted); }
        .mbr-alur-nilai { font-family: var(--mbr-font); font-weight: 800; font-size: 1.35rem; line-height: 1.2; letter-spacing: -.02em; color: color-mix(in srgb, var(--c) 70%, #0f172a); }
        .mbr-alur-ket { font-size: .79rem; color: var(--mbr-muted); line-height: 1.5; }
        .mbr-sisa {
            display: flex; align-items: center; gap: 14px; margin-top: 18px; padding: 16px 18px; border-radius: 16px;
            border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; font-size: .89rem; line-height: 1.6;
        }
        .mbr-sisa b { color: #14532d; }

        /* ===== Syarat ===== */
        .mbr-syarat { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .mbr-syarat-item {
            display: flex; align-items: flex-start; gap: 13px; padding: 16px 18px;
            background: #fff; border: 1px solid var(--mbr-line); border-radius: 16px;
            font-size: .87rem; line-height: 1.65; color: #475569;
            transition: border-color .22s ease, box-shadow .22s ease;
        }
        .mbr-syarat-item:hover { border-color: color-mix(in srgb, var(--c) 32%, #fff); box-shadow: 0 12px 26px -22px color-mix(in srgb, var(--c) 85%, transparent); }
        .mbr-syarat-item b { color: #334155; }

        /* ===== Ajakan penutup ===== */
        .mbr-cta {
            position: relative; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
            gap: 18px 26px; padding: 28px 30px; border-radius: 22px; overflow: hidden; color: #fff;
            background:
                radial-gradient(70% 130% at 100% 0%, rgba(251, 169, 25, .32), transparent 60%),
                linear-gradient(135deg, #23272f 0%, #3a2a20 100%);
        }
        .mbr-cta-kiri { display: flex; align-items: center; gap: 16px; flex: 1 1 340px; min-width: 0; }
        .mbr-cta .mbr-ubin { background: rgba(251, 169, 25, .18); color: #fbbf24; }
        .mbr-cta-teks b { display: block; font-family: var(--mbr-font); font-weight: 800; font-size: 1.15rem; color: #fff; }
        .mbr-cta-teks span { display: block; margin-top: 3px; font-size: .89rem; color: rgba(255, 255, 255, .82); line-height: 1.55; }
        .mbr-cta-aksi { display: flex; flex-wrap: wrap; gap: 10px; }
        .mbr-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 20px;
            border-radius: 13px; border: 1.5px solid transparent; font-weight: 700; font-size: .9rem;
            text-decoration: none; white-space: nowrap; transition: filter .16s ease, transform .16s ease, background .16s ease, border-color .16s ease;
        }
        .mbr-btn.is-utama { background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff; box-shadow: 0 12px 22px -12px rgba(242, 101, 34, .85); }
        .mbr-btn.is-utama:hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .mbr-btn.is-tembus { background: rgba(255, 255, 255, .1); color: #fff; border-color: rgba(255, 255, 255, .3); }
        .mbr-btn.is-tembus:hover { background: rgba(255, 255, 255, .18); color: #fff; border-color: rgba(255, 255, 255, .5); transform: translateY(-1px); }
        .mbr-btn i.bi, .mbr-btn i.bi::before { display: block; line-height: 1; font-size: 1.02rem; }

        @media (max-width: 991.98px) {
            .mbr-panel { grid-template-columns: minmax(0, 1fr); gap: 24px; }
            .mbr-kartu-member { max-width: 420px; margin-inline: auto; }
            .mbr-sorot { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
            .mbr-untung { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            /* Dua baris: garis lurus tidak lagi menggambarkan jalurnya dengan benar. */
            .mbr-alur { grid-template-columns: minmax(0, 1fr); }
            .mbr-alur::before { display: none; }
        }
        @media (max-width: 767.98px) {
            .mbr-sorot { grid-template-columns: minmax(0, 1fr); }
            .mbr-syarat { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .mbr-sec { padding: 34px 0; }
            .mbr-panel { padding: 22px 18px; }
            .mbr-panel-aksi .mbr-btn { flex: 1 1 auto; }
            .mbr-kalk { padding: 16px 14px; }
            .mbr-sec.is-atas { padding-top: 32px; }
            .mbr-deret { grid-template-columns: minmax(0, 1fr); gap: 12px; }
            .mbr-deret::before { display: none; }
            .mbr-untung { grid-template-columns: minmax(0, 1fr); }
            .mbr-langkah, .mbr-kartu { padding: 20px 18px 18px; }
            .mbr-nomor { width: 38px; height: 38px; font-size: 1rem; margin-bottom: 12px; }
            .mbr-ikon-pojok { top: 20px; right: 18px; width: 30px; height: 30px; font-size: .9rem; }
            .mbr-cta { padding: 22px 18px; }
            .mbr-cta-aksi .mbr-btn { flex: 1 1 auto; }
        }
        @media (prefers-reduced-motion: reduce) {
            .mbr-kartu-member, .mbr-kartu-member:hover { transform: none; transition: none; }
            .mbr-langkah, .mbr-kartu, .mbr-sorot-item, .mbr-alur-item, .mbr-btn { transition: none; }
            .mbr-langkah:hover, .mbr-kartu:hover, .mbr-sorot-item:hover, .mbr-alur-item:hover, .mbr-btn:hover { transform: none; }
            .mbr-langkah::before, .mbr-kartu::before, .mbr-sorot-item::before { transition: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Gratis, selamanya</span>
                <h1>Jadi Member Phoenix</h1>
                <p>Tanpa biaya, tanpa ribet. Kumpulkan poin dari setiap belanja dan tukar jadi potongan di
                    pembelian berikutnya.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li class="current">Member</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    {{-- Sorotan + pita lompat bagian --}}
    <section class="mbr-sec is-atas is-pita">
        <div class="container">
            {{-- Panel pembuka: jangkar visual halaman, sekaligus tiruan kartu
                 member supaya keuntungannya terlihat, bukan hanya terbaca. --}}
            <div class="mbr-panel">
                <div>
                    <span class="mbr-panel-label">
                        <span class="mbr-ubin"><i class="bi bi-stars"></i></span> Program Member
                    </span>
                    <h2>Belanja, tulis testimoni, poinmu mulai terkumpul</h2>
                    <p>Gratis selamanya. Tiap Rp {{ number_format($perPoin, 0, ',', '.') }} belanja jadi 1 poin,
                        dan poinnya bisa dipakai memotong tagihan kapan saja.</p>
                    <div class="mbr-panel-aksi">
                        <a class="mbr-btn is-utama" href="{{ route('shop.index') }}"><i class="bi bi-bag"></i> Mulai Belanja</a>
                        <a class="mbr-btn is-tembus" href="#mb-1"><i class="bi bi-signpost-split"></i> Lihat Caranya</a>
                    </div>
                </div>

                <div>
                    <div class="mbr-kartu-member" aria-hidden="true">
                        <div class="mbr-km-atas">
                            <span class="mbr-km-merek">
                                <img src="{{ asset('storage/img/phoenix-mark.png') }}" alt="">
                                <span>Phoenix</span>
                            </span>
                            <span class="mbr-km-pita">Member</span>
                        </div>
                        <div class="mbr-km-isi">
                            <span class="mbr-km-label">Nama kamu</span>
                            <span class="mbr-km-nama">Calon Member</span>
                        </div>
                        <div class="mbr-km-baris">
                            <span>
                                <span class="mbr-km-label">Poin</span>
                                <span class="mbr-km-poin">{{ $contohPoin }} poin</span>
                            </span>
                            <span class="mbr-km-kode"><i class="bi bi-gift"></i> KODE REFERRAL</span>
                        </div>
                    </div>
                    <p class="mbr-km-catatan">Contoh tampilan kartu member</p>
                </div>
            </div>

            <div class="mbr-sorot" style="margin-top:22px;">
                <div class="mbr-sorot-item" style="--c: #16a34a">
                    <span class="mbr-ubin"><i class="bi bi-cash-coin"></i></span>
                    <span><b>Gratis selamanya</b><small>Tanpa biaya daftar & iuran</small></span>
                </div>
                <div class="mbr-sorot-item" style="--c: #d97706">
                    <span class="mbr-ubin"><i class="bi bi-coin"></i></span>
                    <span><b>≈ {{ rtrim(rtrim(number_format($persenBalik, 1, ',', '.'), '0'), ',') }}% belanja kembali</b><small>Rp {{ number_format($perPoin, 0, ',', '.') }} = 1 poin = Rp {{ number_format($nilaiPoin, 0, ',', '.') }}</small></span>
                </div>
                <div class="mbr-sorot-item" style="--c: #2563eb">
                    <span class="mbr-ubin"><i class="bi bi-piggy-bank-fill"></i></span>
                    <span><b>Sisa belanja tidak hangus</b><small>Ditumpuk ke belanja berikutnya</small></span>
                </div>
            </div>

            <nav class="mbr-lompat" aria-label="Lompat ke bagian">
                @foreach ($bagian as $i => $b)
                    <a href="#mb-{{ $i + 1 }}" style="--c: {{ $b['warna'] }}">
                        <span class="mbr-ubin"><i class="bi {{ $b['ikon'] }}"></i></span> {{ $b['judul'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </section>

    {{-- 1. Caranya cuma 2 langkah --}}
    <section class="mbr-sec" id="mb-1">
        <div class="container">
            <div class="ph-sec-head">
                <span class="ph-sec-eyebrow"><i class="bi {{ $bagian[0]['ikon'] }}"></i> Cara jadi member</span>
                <h2 class="ph-sec-title">{{ $bagian[0]['judul'] }}</h2>
                <p class="ph-sec-sub">Tanpa formulir pendaftaran — cukup belanja, lalu ceritakan pengalamanmu.</p>
            </div>

            <div class="mbr-deret">
                @foreach ($langkah as $i => $l)
                    <div class="mbr-langkah" style="--c: {{ $l['warna'] }}">
                        <span class="mbr-nomor">{{ $i + 1 }}</span>
                        <span class="mbr-ikon-pojok"><i class="bi {{ $l['ikon'] }}"></i></span>
                        <h3 class="mbr-judul">{{ $l['judul'] }}</h3>
                        <p class="mbr-ket">{!! $l['teks'] !!}</p>
                    </div>
                @endforeach
            </div>

            <div class="mbr-aktif" style="--c: #16a34a">
                <span class="mbr-ubin is-padat"><i class="bi bi-patch-check-fill"></i></span>
                <div class="mbr-aktif-teks">
                    <b>Status member langsung aktif</b>
                    <span>Begitu testimonimu disetujui admin, status Member menyala otomatis — tanpa perlu
                        menghubungi siapa pun. Kode referral pun langsung kamu terima.</span>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Keuntungan --}}
    <section class="mbr-sec is-pita" id="mb-2">
        <div class="container">
            <div class="ph-sec-head">
                <span class="ph-sec-eyebrow"><i class="bi {{ $bagian[1]['ikon'] }}"></i> Keuntungan member</span>
                <h2 class="ph-sec-title">{{ $bagian[1]['judul'] }}</h2>
                <p class="ph-sec-sub">Tiga keuntungan yang berlaku selama kamu jadi member.</p>
            </div>

            <div class="mbr-untung">
                @foreach ($keuntungan as $k)
                    <div class="mbr-kartu" style="--c: {{ $k['warna'] }}">
                        <span class="mbr-ubin is-padat"><i class="bi {{ $k['ikon'] }}"></i></span>
                        <h3 class="mbr-judul" style="margin-top:14px;">{{ $k['judul'] }}</h3>
                        <p class="mbr-ket">{!! $k['teks'] !!}</p>
                        <span class="mbr-chip">{{ $k['chip'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. Contoh hitungan --}}
    <section class="mbr-sec" id="mb-3">
        <div class="container">
            <div class="ph-sec-head">
                <span class="ph-sec-eyebrow"><i class="bi {{ $bagian[2]['ikon'] }}"></i> Hitungan poin</span>
                <h2 class="ph-sec-title">{{ $bagian[2]['judul'] }}</h2>
                <p class="ph-sec-sub">Misal kamu belanja Rp {{ number_format($contohBelanja, 0, ',', '.') }} — begini poinnya terkumpul.</p>
            </div>

            {{-- Penghitung: angka di jalur bawah ikut berubah saat nilainya
                 diketik. Nilai bawaannya sudah tercetak dari server, jadi
                 halaman ini tetap utuh bila JavaScript mati. --}}
            <div class="mbr-kalk">
                <label class="mbr-kalk-label" for="mbr-belanja">Coba hitung: berapa belanjamu?</label>
                <div class="mbr-kalk-kolom">
                    <span class="mbr-kalk-rp">Rp</span>
                    <input type="text" id="mbr-belanja" inputmode="numeric" autocomplete="off"
                        value="{{ number_format($contohBelanja, 0, ',', '.') }}" aria-label="Nilai belanja">
                </div>
                <div class="mbr-kalk-cepat">
                    <button type="button" data-nilai="100000">Rp 100.000</button>
                    <button type="button" data-nilai="250000">Rp 250.000</button>
                    <button type="button" data-nilai="500000">Rp 500.000</button>
                    <button type="button" data-nilai="1000000">Rp 1.000.000</button>
                </div>
            </div>

            <div class="mbr-alur">
                <div class="mbr-alur-item" style="--c: #2563eb">
                    <span class="mbr-ubin is-padat"><i class="bi bi-bag"></i></span>
                    <span class="mbr-alur-label">Belanja</span>
                    <span class="mbr-alur-nilai" id="mbr-nilai-belanja">Rp {{ number_format($contohBelanja, 0, ',', '.') }}</span>
                    <span class="mbr-alur-ket">Dibagi Rp {{ number_format($perPoin, 0, ',', '.') }} per poin</span>
                </div>
                <div class="mbr-alur-item" style="--c: #d97706">
                    <span class="mbr-ubin is-padat"><i class="bi bi-coin"></i></span>
                    <span class="mbr-alur-label">Poin didapat</span>
                    <span class="mbr-alur-nilai" id="mbr-nilai-poin">{{ $contohPoin }} poin</span>
                    <span class="mbr-alur-ket">1 poin = Rp {{ number_format($nilaiPoin, 0, ',', '.') }}</span>
                </div>
                <div class="mbr-alur-item is-hasil" style="--c: #16a34a">
                    <span class="mbr-ubin is-padat"><i class="bi bi-ticket-perforated"></i></span>
                    <span class="mbr-alur-label">Jadi potongan</span>
                    <span class="mbr-alur-nilai" id="mbr-nilai-potongan">Rp {{ number_format($contohNilai, 0, ',', '.') }}</span>
                    <span class="mbr-alur-ket">Dipakai kapan saja</span>
                </div>
            </div>

            <div class="mbr-sisa">
                <span class="mbr-ubin is-kecil" style="--c: #16a34a"><i class="bi bi-piggy-bank-fill"></i></span>
                <span>Sisa <b id="mbr-nilai-sisa">Rp {{ number_format($contohSisa, 0, ',', '.') }}</b> <b>tidak hangus</b> —
                    disimpan dan dijumlahkan ke belanja berikutnya. Jadi belanja kecil pun tidak sia-sia.</span>
            </div>
        </div>
    </section>

    {{-- 4. Syarat & ketentuan --}}
    <section class="mbr-sec is-pita" id="mb-4">
        <div class="container">
            <div class="ph-sec-head">
                <span class="ph-sec-eyebrow"><i class="bi {{ $bagian[3]['ikon'] }}"></i> Aturan main</span>
                <h2 class="ph-sec-title">{{ $bagian[3]['judul'] }}</h2>
                <p class="ph-sec-sub">Ringkas dan tanpa huruf kecil tersembunyi.</p>
            </div>

            <div class="mbr-syarat">
                @foreach ($syarat as $s)
                    <div class="mbr-syarat-item" style="--c: {{ $s['warna'] }}">
                        <span class="mbr-ubin is-kecil"><i class="bi {{ $s['ikon'] }}"></i></span>
                        <span>{!! $s['teks'] !!}</span>
                    </div>
                @endforeach
            </div>

            {{-- Rute checkout dipakai langsung, BUKAN url()->previous() — kalau halaman
                 ini dibuka dari tempat lain, previous() melempar ke sana padahal
                 tombolnya jelas-jelas bertuliskan "Kembali ke Checkout". --}}
            <div class="mbr-cta" style="margin-top:26px;">
                <div class="mbr-cta-kiri">
                    <span class="mbr-ubin is-padat" style="--c: #fba919"><i class="bi bi-bag-check-fill"></i></span>
                    <div class="mbr-cta-teks">
                        <b>Siap lanjut belanja?</b>
                        <span>Selesaikan pesananmu — poinnya mulai terkumpul begitu pesanan dibayar.</span>
                    </div>
                </div>
                <div class="mbr-cta-aksi">
                    <a class="mbr-btn is-utama" href="{{ route('checkout') }}">
                        <i class="bi bi-arrow-left"></i> Kembali ke Checkout
                    </a>
                    <a class="mbr-btn is-tembus" href="{{ route('shop.index') }}">
                        <i class="bi bi-bag"></i> Lihat Produk
                    </a>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            (function () {
                var perPoin = {{ (int) $perPoin }}, nilaiPoin = {{ (int) $nilaiPoin }};
                var kolom = document.getElementById('mbr-belanja');
                if (!kolom) return;
                var el = {
                    belanja: document.getElementById('mbr-nilai-belanja'),
                    poin: document.getElementById('mbr-nilai-poin'),
                    potongan: document.getElementById('mbr-nilai-potongan'),
                    sisa: document.getElementById('mbr-nilai-sisa')
                };
                var rupiah = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };

                function hitung() {
                    var angka = parseInt((kolom.value || '').replace(/\D/g, ''), 10) || 0;
                    // Dibatasi agar angka konyol tidak merusak tata letak kartunya.
                    if (angka > 999999999) { angka = 999999999; }
                    kolom.value = angka ? angka.toLocaleString('id-ID') : '';
                    var poin = Math.floor(angka / perPoin);
                    if (el.belanja) el.belanja.textContent = rupiah(angka);
                    if (el.poin) el.poin.textContent = poin.toLocaleString('id-ID') + ' poin';
                    if (el.potongan) el.potongan.textContent = rupiah(poin * nilaiPoin);
                    if (el.sisa) el.sisa.textContent = rupiah(angka % perPoin);
                }

                kolom.addEventListener('input', hitung);
                document.querySelectorAll('.mbr-kalk-cepat button').forEach(function (b) {
                    b.addEventListener('click', function () {
                        kolom.value = parseInt(b.dataset.nilai, 10).toLocaleString('id-ID');
                        hitung();
                        kolom.focus();
                    });
                });
            })();
        </script>
    @endpush
</main>
