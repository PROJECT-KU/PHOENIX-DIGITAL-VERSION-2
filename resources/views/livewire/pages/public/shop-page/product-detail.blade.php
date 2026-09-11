<main class="main">
    @include('partials.media-produk-style')
    <style>
    /* Pemberitahuan layanan dijeda — memakai warna peringatan, bukan aksen toko,
       supaya terbaca sebagai keadaan sementara dan bukan bagian dari promosi.
       Jarak atas 22px menyamai .pd-buy di public-custom-styles.css, sehingga
       kotak ini mengikuti ritme halaman dan tidak menempel ke kartu paket di
       atasnya; jarak bawahnya diserahkan ke margin .pd-buy itu sendiri. */
    .pd-jeda{display:flex;gap:11px;align-items:flex-start;padding:13px 15px;margin:22px 0 0;
        border:1px solid #f0c36d;background:#fdf6e3;border-radius:12px;color:#7a5a12}
    .pd-jeda > i{font-size:1.15rem;line-height:1.35;flex-shrink:0}
    .pd-jeda b{display:block;font-size:.92rem}
    .pd-jeda span{display:block;font-size:.85rem;opacity:.9;margin-top:1px}
    .pd-add:disabled{opacity:.55;cursor:not-allowed;filter:grayscale(.35)}

        /* ===== Kartu deskripsi ===== */
        .pd-desc-card { border:1px solid var(--ph-line); border-radius:18px; padding:20px 22px;
            background:linear-gradient(180deg, #fffdfa 0%, #fff 60%); }
        .pd-desc-head { display:flex; align-items:center; gap:9px;
            font-family:'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight:800; font-size:1rem;
            color:var(--ph-ink); margin:0 0 12px; }
        .pd-desc-head i { color:var(--ph-orange); font-size:1.05rem; }
        @media (max-width: 575.98px) { .pd-desc-card { padding:16px 16px; border-radius:15px; } }

        /* ===== Tata letak kolom kiri: gambar di ATAS, lalu deskripsi, lalu
           kartu jaminan. Kolom kanan (harga s.d. Wishlist) mengisi kolom kedua
           penuh, sehingga tombol Wishlist sejajar dengan bagian bawah kiri.

           Hanya ≥992px. Bootstrap .row dijadikan grid, jadi gutter bawaannya
           (margin negatif + padding kolom) dinetralkan dan diganti gap grid. */
        @media (min-width: 992px) {
            .pd-row {
                display:grid;
                grid-template-columns:1fr 1fr;
                grid-template-areas:"media info" "desc info" "trust info";
                /* Baris gambar memakai SISA ruang: deskripsi & kartu jaminan
                   mengambil tinggi sesuai isinya, gambar menyesuaikan sisanya.
                   Efeknya tinggi kolom kiri mengikuti kolom kanan, sehingga
                   kartu jaminan sejajar dengan tombol Wishlist. minmax menjaga
                   gambar tidak menyusut lebih kecil dari 260px. */
                grid-template-rows:minmax(260px, 1fr) auto auto;
                align-items:stretch;
                column-gap:3rem; row-gap:22px;
                margin-left:0; margin-right:0;
            }
            .pd-row > .pd-col-media { grid-area:media; display:flex; min-height:0; }
            .pd-row > .pd-col-desc  { grid-area:desc; }
            .pd-row > .pd-col-trust { grid-area:trust; align-self:end; }
            .pd-row > .pd-col-info  { grid-area:info; }
            /* Netralkan gutter Bootstrap agar jaraknya tidak dobel */
            .pd-row > [class*="col-"] { padding-left:0; padding-right:0; width:auto; max-width:none; margin-top:0; }

            /* Gambar ikut tinggi kotaknya. object-fit:contain dipakai (bukan
               cover) supaya logo produk tidak terpotong saat kotaknya memendek. */
            .pd-col-media .pd-media { flex:1; min-height:0; display:flex; }
            .pd-col-media .pd-media img { width:100%; height:100%; aspect-ratio:auto; object-fit:contain; }
        }

        /* Di bawah 992px kolom menumpuk: gambar, kartu jaminan, info beli, lalu
           deskripsi. Deskripsi sengaja terakhir supaya tidak mendahului nama
           produk dan harga. */
        @media (max-width: 991.98px) {
            .pd-row > .pd-col-media { order:1; }
            .pd-row > .pd-col-trust { order:2; }
            .pd-row > .pd-col-info  { order:3; }
            .pd-row > .pd-col-desc  { order:4; }
        }

        /* ===== Blok JASA di halaman produk (upload per halaman & add-on) ===== */
        .jd-hint { font-size:.83rem; color:var(--ph-muted); line-height:1.55; margin:0 0 10px; }

        /* Syarat bahasa untuk layanan deteksi AI — ditegaskan sebelum bayar */
        .jd-lang { display:flex; align-items:flex-start; gap:11px; margin-top:22px;
            padding:13px 15px; border:1px solid #bfdbfe; border-radius:13px; background:#eff6ff; }
        .jd-lang > i.bi { flex-shrink:0; margin-top:1px; font-size:1rem; color:#2563eb;
            display:flex; align-items:center; line-height:1; }
        .jd-lang > i.bi::before { display:block; line-height:1; }
        .jd-lang span { display:flex; flex-direction:column; gap:3px; min-width:0;
            font-size:.8rem; color:#1e40af; line-height:1.55; }
        .jd-lang b { font-size:.86rem; color:#1d4ed8; }
        .jd-drop { position:relative; display:block; padding:20px 16px; border:2px dashed #fcd9a8; border-radius:14px; background:#fffdf8; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; }
        .jd-drop:hover { border-color:#f59e0b; background:#fff7ed; }
        .jd-drop-input { position:absolute; inset:0; width:100%; height:100%; opacity:0; cursor:pointer; }
        .jd-drop-state { display:flex; flex-direction:column; align-items:center; gap:3px; }
        .jd-drop-ic { font-size:1.8rem; color:#f59e0b; display:flex; line-height:1; margin-bottom:4px; }
        .jd-drop-ic::before { display:block; line-height:1; }
        .jd-drop-title { font-weight:700; color:#b45309; font-size:.9rem; }
        .jd-drop-hint { font-size:.75rem; color:var(--ph-muted); }
        .jd-spin { display:inline-block; animation:jdSpin 1s linear infinite; color:#f59e0b; font-size:1.2rem; }
        @keyframes jdSpin { to { transform:rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .jd-spin { animation:none; } }
        .jd-err { color:#dc2626; font-size:.8rem; margin-top:8px; }
        .jd-file { display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid #bbf7d0; border-radius:12px; background:#f0fdf4; }
        .jd-file > i.bi { color:#16a34a; font-size:1.3rem; display:flex; line-height:1; flex-shrink:0; }
        .jd-file > i.bi::before { display:block; line-height:1; }
        .jd-file-txt { flex:1; min-width:0; display:flex; flex-direction:column; }
        .jd-file-txt b { font-size:.88rem; color:#15803d; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .jd-file-txt small { font-size:.76rem; color:#4d7c58; }
        .jd-file-x { flex-shrink:0; width:30px; height:30px; border:0; border-radius:8px; background:#fff; color:#94a3b8; display:flex; align-items:center; justify-content:center; cursor:pointer; }
        .jd-file-x:hover { background:#fee2e2; color:#dc2626; }
        .jd-file-x i.bi { display:flex; line-height:1; font-size:.8rem; }
        /* Penanda langkah unggah (PDF lalu DOCX) */
        .jd-step { display:flex; align-items:center; gap:10px; margin-bottom:9px; }
        .jd-step-no { width:24px; height:24px; flex-shrink:0; border-radius:50%; background:#f59e0b; color:#fff;
            font-size:.76rem; font-weight:800; display:flex; align-items:center; justify-content:center; }
        .jd-step-txt { display:flex; flex-direction:column; min-width:0; }
        .jd-step-txt b { font-size:.87rem; color:#1e293b; }
        .jd-step-txt small { font-size:.75rem; color:var(--ph-muted); line-height:1.35; }

        /* Chip bagian dokumen yang dikecualikan (cover / daftar isi / daftar pustaka) */
        .jd-bagian { display:flex; flex-wrap:wrap; gap:8px; }
        .jd-bagian-chip { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:99px;
            border:1.5px solid var(--ph-line); background:#fff; color:#64748b; font-size:.82rem; font-weight:600;
            cursor:pointer; user-select:none; transition:border-color .18s, background .18s, color .18s; }
        .jd-bagian-chip:hover { border-color:#fcd9a8; color:#b45309; }
        .jd-bagian-chip.is-on { border-color:#f59e0b; background:#fffbeb; color:#b45309; }
        .jd-bagian-chip input { position:absolute; opacity:0; width:0; height:0; pointer-events:none; }
        .jd-bagian-box { width:17px; height:17px; flex-shrink:0; border:1.5px solid #cbd5e1; border-radius:5px;
            background:#fff; display:flex; align-items:center; justify-content:center; transition:background .18s, border-color .18s; }
        .jd-bagian-box i.bi { font-size:.62rem; color:#fff; opacity:0; display:flex; line-height:1; }
        .jd-bagian-box i.bi::before { display:block; line-height:1; }
        .jd-bagian-chip.is-on .jd-bagian-box { background:#f59e0b; border-color:#f59e0b; }
        .jd-bagian-chip.is-on .jd-bagian-box i.bi { opacity:1; }

        /* Halaman yang dikecualikan (tidak ditagih) */
        .jd-exc { margin-top:12px; padding:14px 15px; border:1px solid var(--ph-line); border-radius:14px; background:#fcfcfd; }
        .jd-exc-head { display:flex; flex-direction:column; gap:2px; margin-bottom:10px; }
        .jd-exc-head b { font-size:.86rem; color:#1e293b; }
        .jd-exc-head small { font-size:.77rem; color:var(--ph-muted); line-height:1.45; }
        .jd-exc-input { width:100%; font-size:.88rem; padding:10px 12px; border:1px solid var(--ph-line);
            border-radius:10px; background:#fff; color:#334155; outline:none; transition:border-color .18s, box-shadow .18s; }
        .jd-exc-input::placeholder { color:#cbd5e1; }
        .jd-exc-input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.13); }
        .jd-exc-quick { display:flex; flex-wrap:wrap; gap:7px; margin-top:9px; }
        .jd-exc-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:99px;
            border:1px solid var(--ph-line); background:#fff; color:#64748b; font-size:.76rem; font-weight:600;
            cursor:pointer; transition:border-color .18s, background .18s, color .18s; }
        .jd-exc-btn:hover { border-color:#fcd9a8; background:#fffdf8; color:#b45309; }
        .jd-exc-btn.is-clear:hover { border-color:#fecaca; background:#fef2f2; color:#dc2626; }
        .jd-exc-btn i.bi { display:flex; align-items:center; line-height:1; font-size:.62rem; }
        .jd-exc-btn i.bi::before { display:block; line-height:1; }
        .jd-exc-info { display:flex; align-items:flex-start; gap:7px; margin-top:10px; font-size:.79rem;
            color:#15803d; line-height:1.5; }
        .jd-exc-info i.bi { flex-shrink:0; margin-top:.15rem; display:flex; line-height:1; }
        .jd-exc-info i.bi::before { display:block; line-height:1; }

        /* Add-on — dipisah jelas dari blok paket di atasnya */
        .jd-addon-sec { margin-top:26px; padding-top:22px; border-top:1px solid var(--ph-line); }
        .jd-addons { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:10px; }
        .jd-addon {
            display:flex; align-items:center; gap:12px; width:100%; text-align:left;
            padding:14px 16px; border:1.5px solid var(--ph-line); border-radius:14px; background:#fff;
            cursor:pointer; transition:border-color .18s, background .18s, box-shadow .18s;
        }
        .jd-addon:hover { border-color:#fcd9a8; background:#fffdf8; }
        .jd-addon.is-on { border-color:#f59e0b; background:#fffbeb; box-shadow:0 3px 12px rgba(245,158,11,.13); }
        .jd-addon-box {
            width:21px; height:21px; flex-shrink:0; border:1.5px solid #cbd5e1;
            border-radius:7px; background:#fff; display:flex; align-items:center; justify-content:center;
            transition:background .18s, border-color .18s;
        }
        .jd-addon-box i.bi { font-size:.68rem; color:#fff; opacity:0; display:flex; line-height:1; }
        .jd-addon-box i.bi::before { display:block; line-height:1; }
        .jd-addon.is-on .jd-addon-box { background:#f59e0b; border-color:#f59e0b; }
        .jd-addon.is-on .jd-addon-box i.bi { opacity:1; }
        .jd-addon-txt { flex:1; min-width:0; display:flex; flex-direction:column; gap:2px; }
        .jd-addon-txt b {
            font-size:.89rem; font-weight:700; color:#1e293b; line-height:1.35;
            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
        }
        .jd-addon.is-on .jd-addon-txt b { color:#92400e; }
        .jd-addon-txt small { font-size:.76rem; color:var(--ph-muted); line-height:1.4; }
        .jd-addon-harga {
            flex-shrink:0; padding:6px 13px; border-radius:99px;
            background:#f1f5f9; color:#64748b; font-size:.81rem; font-weight:800;
            white-space:nowrap; transition:background .18s, color .18s;
        }
        .jd-addon.is-on .jd-addon-harga { background:#f59e0b; color:#fff; }
        @media (max-width:575.98px) {
            .jd-addon-sec { margin-top:22px; padding-top:18px; }
            .jd-addons { grid-template-columns:1fr; }
            .jd-addon { padding:13px 14px; gap:10px; }
            .jd-addon-harga { padding:5px 11px; font-size:.78rem; }
        }
        /* Ringkasan total */
        .jd-total { margin:14px 0 4px; padding:13px 15px; border:1px solid #fde68a; border-radius:14px; background:linear-gradient(180deg,#fffbeb,#fff); }
        .jd-total-row { display:flex; align-items:center; justify-content:space-between; gap:10px; font-size:.85rem; color:#78350f; padding:3px 0; }
        .jd-total-row.is-final { border-top:1px dashed #fcd34d; margin-top:6px; padding-top:9px; font-size:.95rem; }
        .jd-total-row.is-final b { color:#b45309; font-size:1.05rem; }

        /* ===================================================================
           TAMPILAN BARU HALAMAN DETAIL PRODUK

           Warna halaman mengikuti KATEGORI produknya (--c, dipasang di kartu
           judul dan .pd-section). Unsur identitas — gambar, label kategori,
           paket terpilih, ikon subjudul — memakai warna itu. Harga dan tombol
           beli tetap jingga merek: di seluruh toko, jingga berarti "bayar di
           sini", dan tombol beli yang berganti warna tiap produk membuat orang
           harus mencarinya lagi di tiap halaman.
           =================================================================== */

        /* --- Gambar produk: latar diwarnai kategorinya --- */
        .pd-section .pd-media {
            background:
                radial-gradient(70% 90% at 100% 0%, color-mix(in srgb, var(--c) 16%, transparent) 0%, transparent 62%),
                radial-gradient(60% 80% at 0% 100%, color-mix(in srgb, var(--c) 9%, transparent) 0%, transparent 60%),
                #fff;
            border-color: color-mix(in srgb, var(--c) 18%, #eceff4);
            box-shadow: 0 20px 48px color-mix(in srgb, var(--c) 12%, transparent);
        }
        .pd-section .pd-media img { mix-blend-mode: multiply; }

        /* Lencana diskon DITENANGKAN — sama dengan lencana kartu di beranda
           (partials/media-produk-style): tanpa miring, tanpa denyut, jingga
           padat.

           Animasi fsBadgePop memutarnya -6 derajat terus-menerus. Selain
           terbaca sebagai stiker cetakan, kemiringan itu yang membuat ikonnya
           tampak turun 5 piksel: ikon duduk di ujung kiri lencana, dan ujung
           kiri itulah yang terangkat-turun oleh putarannya. Diukur datar,
           ikonnya tepat di tengah. Animasi mengalahkan deklarasi biasa di
           dalam cascade, jadi `animation: none` wajib ditulis — `transform:
           none` saja tidak pernah menang. */
        .pd-section .pd-badge,
        .pd-section .pd-badge.is-flash {
            animation: none; transform: none;
            background: #f26522;
            box-shadow: 0 6px 16px rgba(242, 101, 34, .28);
        }
        .pd-badge i.bi { display: block; line-height: 1; }
        .pd-badge i.bi::before { display: block; line-height: 1; }

        /* --- Label kategori di atas nama produk --- */
        .pd-kat {
            display: inline-flex; align-items: center; gap: 8px;
            background: color-mix(in srgb, var(--c) 11%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 24%, #fff);
            color: var(--c); text-decoration: none;
            border-radius: 999px; padding: 6px 14px 6px 8px;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: .8rem; line-height: 1;
            transition: background .2s ease, border-color .2s ease;
        }
        a.pd-kat:hover { color: var(--c); background: color-mix(in srgb, var(--c) 18%, #fff); }
        .pd-kat i.bi {
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: var(--c); color: #fff; font-size: .72rem; line-height: 1;
        }
        .pd-kat i.bi::before { display: block; line-height: 1; }

        /* --- Nama produk tidak dicetak dua kali ---
           Di layar lebar nama produk sudah tampil besar di kartu judul, tepat
           100px di atasnya; mengulangnya di kolom beli membuat dua judul
           identik bertumpuk. Di layar sempit keduanya terpisah ~670px, dan
           di sanalah pengulangan justru berguna: pembeli yang menggulir sampai
           tombol beli masih melihat produk apa yang ia pilih. */
        .pd-title {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif !important;
            letter-spacing: -.025em;
        }
        @media (min-width: 992px) {
            .pd-col-info .pd-title { display: none; }
            .pd-col-info .pd-kat { margin-bottom: 14px; }
        }
        @media (max-width: 991.98px) {
            .pd-col-info .pd-title { margin-top: 12px; }
        }

        .pd-price-now, .pd-sub { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif !important; }
        .pd-price-now { letter-spacing: -.03em; font-variant-numeric: lining-nums tabular-nums; }
        .pd-sub i.bi { color: var(--c); display: block; line-height: 1; }
        .pd-sub i.bi::before { display: block; line-height: 1; }

        /* --- Paket: yang terpilih berwarna kategori --- */
        .pd-pkg:hover { border-color: color-mix(in srgb, var(--c) 45%, #fff) !important; }
        .pd-pkg.is-active {
            border-color: var(--c) !important;
            background: color-mix(in srgb, var(--c) 7%, #fff) !important;
            box-shadow: 0 10px 24px color-mix(in srgb, var(--c) 18%, transparent) !important;
        }
        .pd-pkg-check { color: var(--c) !important; }

        /* "Paling hemat" + harga setara per bulan di kartu paket — aturan yang
           sama dengan jendela durasi di /shop (App\Support\PaketHemat). Pil memakai
           warna kategori halaman ini; ruang kanan label dipesan untuk ikon
           centang kartu terpilih di pojok kanan atas. */
        .pd-pkg-dur { display: inline-flex; align-items: center; flex-wrap: wrap; gap: 5px 8px; padding-right: 22px; }
        .pd-pkg-hemat {
            display: inline-flex; align-items: center; height: 20px; padding: 0 8px; border-radius: 99px;
            background: var(--c, #f26522); color: #fff; line-height: 1; white-space: nowrap;
            font-size: .6rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase;
        }
        .pd-pkg-per { font-size: .74rem; font-weight: 500; color: #6b7280; }
        .pd-stepper button:hover:not(:disabled) {
            background: color-mix(in srgb, var(--c) 9%, #fff) !important; color: var(--c) !important;
        }

        /* --- Kartu jaminan: warna per butir seperti Cara Pesan ---
           Sebelumnya ketiganya ubin gradien jingga identik; tiga kotak sama
           persis berjajar terbaca sebagai satu hiasan yang diulang, bukan tiga
           jaminan berbeda. */
        .pd-feature {
            position: relative; overflow: hidden;
            border-color: #eceff4 !important;
        }
        .pd-feature::before {
            content: ""; position: absolute; top: -34px; right: -34px;
            width: 92px; height: 92px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent);
            transition: transform .3s ease; pointer-events: none;
        }
        .pd-feature:hover::before { transform: scale(1.35); }
        .pd-feature:hover {
            border-color: color-mix(in srgb, var(--c) 35%, #fff) !important;
            box-shadow: 0 12px 26px color-mix(in srgb, var(--c) 16%, transparent) !important;
        }
        .pd-feature > * { position: relative; z-index: 1; }
        .pd-feature-ic {
            background: color-mix(in srgb, var(--c) 12%, #fff) !important;
            color: var(--c) !important; box-shadow: none !important;
        }
        .pd-feature-ic i.bi { display: block; line-height: 1; }
        .pd-feature-ic i.bi::before { display: block; line-height: 1; }

        /* ===== Produk lainnya =====
           Kelas rk-*, bukan rel-* lama: aturan .rel-* di public-custom-styles.css
           di server sudah beku (kartu 150px, gambar dipotong cover) dan akan
           terus menempel ke kelas lama. Warna tiap kartu = warna kategorinya,
           sama dengan kartu di /shop dan kategori di beranda. */
        .rk-deret { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 18px; }
        .rk-kartu {
            position: relative; display: flex; flex-direction: column; min-width: 0;
            background: #fff; border: 1px solid #eceff4; border-radius: 18px; overflow: hidden;
            text-decoration: none; color: inherit;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .rk-kartu:hover {
            transform: translateY(-4px); color: inherit;
            border-color: color-mix(in srgb, var(--c) 35%, #fff);
            box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--c) 75%, transparent);
        }

        /* Area gambar bernada kategori dengan sapuan pojok seperti Cara Pesan.
           Gambar produk kebanyakan logo berlatar putih: multiply melarutkan
           putihnya ke latar, bukan kotak putih yang menempel. */
        .rk-media {
            position: relative; display: flex; align-items: center; justify-content: center;
            aspect-ratio: 16 / 10; overflow: hidden;
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 11%, #fff) 0%, #fff 78%);
        }
        .rk-media::before {
            content: ""; position: absolute; top: -42px; right: -42px;
            width: 124px; height: 124px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent);
            transition: transform .35s ease;
        }
        .rk-kartu:hover .rk-media::before { transform: scale(1.3); }
        .rk-media img {
            position: relative; max-width: 64%; max-height: 72%; object-fit: contain;
            mix-blend-mode: multiply; transition: transform .35s ease;
        }
        .rk-kartu:hover .rk-media img { transform: scale(1.06); }

        /* Cadangan saat gambar tidak ada / gagal dimuat: ubin penuh warna
           kategori — bukan ikon gambar rusak dengan teks alt mentah. */
        .rk-cadangan {
            display: none; position: relative; align-items: center; justify-content: center;
            width: 62px; height: 62px; border-radius: 19px;
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff));
            color: #fff; font-size: 1.6rem;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
            transition: transform .35s ease;
        }
        .rk-media.is-kosong .rk-cadangan { display: flex; }
        .rk-kartu:hover .rk-cadangan { transform: scale(1.06) rotate(-4deg); }

        /* Kategori di kanan atas, diskon di kiri atas — konvensi yang sama
           dengan kartu /shop, jadi keduanya berseberangan dan tak bertabrakan. */
        .rk-kat {
            position: absolute; top: 10px; right: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 5px;
            max-width: calc(100% - 20px); height: 26px; padding: 0 10px 0 8px; border-radius: 99px;
            background: rgba(255, 255, 255, .92); border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: var(--c); font-size: .7rem; font-weight: 700; white-space: nowrap;
        }
        .rk-kat span { overflow: hidden; text-overflow: ellipsis; }
        .rk-media:has(.rk-diskon) .rk-kat { max-width: calc(100% - 92px); }
        /* Pil putih bertinta merah muda, bukan blok merah pekat: saat promo
           berlaku untuk semua produk, sepuluh lencana merah berjajar menjadi
           dinding merah yang menenggelamkan nama dan harga. */
        .rk-diskon {
            position: absolute; top: 10px; left: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 4px; height: 26px; padding: 0 9px;
            border-radius: 99px; background: rgba(255, 255, 255, .95); border: 1px solid #fecdd3;
            color: #e11d48; font-size: .72rem; font-weight: 800;
        }
        .rk-diskon.is-flash { background: #fff1f2; }

        .rk-isi { display: flex; flex-direction: column; flex: 1 1 auto; gap: 4px; padding: 14px 16px 16px; }
        .rk-jenis {
            font-size: .66rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; color: var(--c);
        }
        /* Dua baris dipesan untuk setiap nama, supaya harga semua kartu dalam
           satu baris sejajar meski panjang namanya berbeda. */
        .rk-nama {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
            min-height: 2.7em;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700;
            font-size: .98rem; line-height: 1.35; letter-spacing: -.01em; color: #1c1f26;
        }
        .rk-kaki {
            display: flex; align-items: flex-end; justify-content: space-between; gap: 10px;
            margin-top: auto; padding-top: 12px;
        }
        .rk-harga { display: flex; flex-direction: column; min-width: 0; line-height: 1.25; }
        .rk-harga s { font-size: .74rem; color: #9aa2ae; }
        .rk-harga b {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
            font-size: 1.02rem; color: #1c1f26; white-space: nowrap;
        }
        .rk-harga small { margin-left: 2px; font-size: .72rem; font-weight: 600; color: #9aa2ae; }
        .rk-harga .rk-mulai { margin: 0 3px 0 0; }
        .rk-panah {
            flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); font-size: .9rem;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }
        .rk-kartu:hover .rk-panah { background: var(--c); color: #fff; transform: rotate(45deg); }
        .rk-kat i.bi, .rk-diskon i.bi, .rk-panah i.bi, .rk-cadangan i.bi { line-height: 1; }
        .rk-kat i.bi::before, .rk-diskon i.bi::before, .rk-panah i.bi::before, .rk-cadangan i.bi::before { display: block; line-height: 1; }

        /* Jumlah kartu dipotong mengikuti kolom, supaya baris terakhir penuh:
           5 kolom × 2 = 10, 4 × 2 = 8, 3 × 3 = 9. */
        @media (max-width: 1199.98px) {
            .rk-deret { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .rk-kartu:nth-child(n+9) { display: none; }
        }
        @media (max-width: 991.98px) {
            .rk-deret { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
            .rk-kartu:nth-child(n+9) { display: flex; }
            .rk-kartu:nth-child(n+10) { display: none; }
        }
        /* HP: digeser mendatar, kartu berikutnya mengintip sebagai petunjuk
           bahwa deretan ini bisa digeser. */
        @media (max-width: 767.98px) {
            .rk-deret {
                grid-template-columns: none; grid-auto-flow: column; grid-auto-columns: 72%;
                overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none;
                gap: 14px; padding-bottom: 4px;
            }
            .rk-deret::-webkit-scrollbar { display: none; }
            .rk-kartu, .rk-kartu:nth-child(n+9), .rk-kartu:nth-child(n+10) { display: flex; scroll-snap-align: start; }
            .rk-kartu:hover { transform: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .pd-feature::before, .pd-kat { transition: none; }
            .rk-kartu, .rk-media::before, .rk-media img, .rk-cadangan, .rk-panah { transition: none; }
        }
    </style>
    @php
        $best = $this->bestDiscount;
        $isFlash = $best && ($best['promo']->tipe_promo ?? null) === 'flash_sale';
        $selOrig = $this->selectedHarga();
        $selDisc = $this->applyDiscount($selOrig);
        $selSave = max(0, $selOrig - $selDisc);

        // Kategori produk menentukan WARNA halaman ini — memakai taksonomi yang
        // sama dengan kartu kategori di beranda dan kartu di /shop, jadi produk
        // yang sama berwarna sama di mana pun ia muncul. Produk tanpa kategori
        // memakai jingga merek.
        $kat = \App\Support\KategoriBeranda::untukProduk($product->nama_akun);
        $warnaKat = $kat['warna'] ?? '#f26522';
    @endphp

    <!-- Page Title -->
    <div class="page-title ph-page-title" style="--c: {{ $warnaKat }}">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-box-seam"></i> Detail Produk</span>
                <h1>{{ $product->nama_akun }}</h1>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">Detail</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="pd-section" style="--c: {{ $warnaKat }}">
        <div class="container">
            <div class="row g-4 g-lg-5 pd-row">
                {{-- Media --}}
                <div class="col-lg-6 pd-col-media">
                    <div class="pd-media">
                        @if ($best)
                            <span class="pd-badge {{ $isFlash ? 'is-flash' : '' }}">
                                @if ($isFlash)<i class="bi bi-lightning-charge-fill"></i> @endif
                                @if ($best['type'] === 'persen')
                                    {{ $isFlash ? 'Diskon s.d.' : 'Diskon' }} {{ number_format($best['value'], 0) }}%
                                @else
                                    {{ $isFlash ? 'Diskon s.d.' : 'Diskon' }} Rp{{ number_format($best['value'], 0, ',', '.') }}
                                @endif
                            </span>
                        @endif
                        @if ($product->image)
                            <img src="{{ asset('storage/img/Product/' . $product->image) }}" alt="{{ $product->nama_akun }}">
                        @else
                            <img src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                alt="{{ $product->nama_akun }}">
                        @endif
                    </div>

                </div>

                {{-- Kartu jaminan: dikeluarkan dari kolom media agar bisa
                     ditempatkan SETELAH deskripsi pada layar lebar. --}}
                <div class="col-lg-6 pd-col-trust">
                    <div class="pd-features">
                        <div class="pd-feature" style="--c: #2563eb">
                            <span class="pd-feature-ic"><i class="bi bi-shield-check"></i></span>
                            <span class="pd-feature-txt">
                                <b>Bergaransi</b>
                                <small>Selama masa aktif paket</small>
                            </span>
                        </div>
                        <div class="pd-feature" style="--c: #16a34a">
                            <span class="pd-feature-ic"><i class="bi bi-whatsapp"></i></span>
                            <span class="pd-feature-txt">
                                <b>Dukungan Cepat</b>
                                <small>Bantuan &amp; respons via WhatsApp</small>
                            </span>
                        </div>
                        <div class="pd-feature" style="--c: #7c3aed">
                            <span class="pd-feature-ic"><i class="bi bi-shield-lock-fill"></i></span>
                            <span class="pd-feature-txt">
                                <b>Pembayaran Aman</b>
                                <small>Transfer Bank &amp; QRIS</small>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi: anak langsung .pd-row supaya bisa ditempatkan di
                     kolom kiri ATAS gambar pada layar lebar (lihat .pd-row di
                     <style> atas). Dikeluarkan dari kolom info agar kolom kanan
                     memendek dan tombol Wishlist sejajar dengan kartu jaminan. --}}
                {{-- Deskripsi dirapikan OTOMATIS dari teks mentahnya — admin cukup
                     mengetik atau menempel dari ChatGPT. Lihat App\Support\DeskripsiProduk
                     dan partials/deskripsi-rapi (dipakai juga oleh pratinjau admin). --}}
                @php $desk = \App\Support\DeskripsiProduk::blok($product->deskripsi); @endphp
                @if ($desk)
                    <div class="col-lg-6 pd-col-desc">
                        <div class="pd-desc-card">
                            <h3 class="pd-desc-head"><i class="bi bi-card-text"></i> Deskripsi Produk</h3>
                            @include('partials.deskripsi-rapi', ['blok' => $desk])
                        </div>
                    </div>
                @endif

                {{-- Info --}}
                <div class="col-lg-6 pd-col-info">
                    {{-- Label di atas nama produk sebelumnya selalu "Akun
                         Premium" — untuk SEMUA produk, termasuk layanan cek
                         plagiasi yang sama sekali bukan akun. Kini diambil dari
                         kategorinya, sehingga ia benar-benar memberi tahu
                         sesuatu. --}}
                    @if ($kat)
                        <a class="pd-kat" href="{{ route('shop.index', ['kategori' => $kat['kunci']]) }}">
                            <i class="bi {{ $kat['ikon'] }}"></i> {{ $kat['label'] }}
                        </a>
                    @else
                        <span class="pd-kat pd-kat-polos">
                            <i class="bi {{ $product->butuh_file ? 'bi-file-earmark-check' : 'bi-stars' }}"></i>
                            {{ $product->butuh_file ? 'Layanan' : 'Akun Premium' }}
                        </span>
                    @endif
                    <h2 class="pd-title">{{ $product->nama_akun }}</h2>

                    <div class="pd-price">
                        <span class="pd-price-now">Rp {{ number_format($selDisc, 0, ',', '.') }}</span>
                        @if ($selDisc < $selOrig)
                            <span class="pd-price-old">Rp {{ number_format($selOrig, 0, ',', '.') }}</span>
                        @endif
                        <span class="pd-price-unit">/ {{ $durationValue }} {{ ucfirst($durationType) }}</span>
                        @if ($selSave > 0)
                            <span class="pd-price-save">Hemat Rp {{ number_format($selSave, 0, ',', '.') }}</span>
                        @endif
                    </div>

                    {{-- ===== JASA PER HALAMAN: unggah dokumen dulu (harga = per halaman) ===== --}}
                    @if ($product->jasaPerHalaman())
                    <div class="pd-packages">
                        <h4 class="pd-sub"><i class="bi bi-file-earmark-arrow-up"></i> Unggah Dokumen</h4>
                        <p class="jd-hint">
                            Harga dihitung dari <b>jumlah halaman</b> dokumen Anda
                            (<b>Rp {{ number_format($product->hargaPerHalaman(), 0, ',', '.') }}</b> / halaman).
                            Siapkan <b>2 file dari dokumen yang sama</b>: PDF untuk menghitung halaman,
                            dan DOCX yang akan dikerjakan tim kami.
                        </p>

                        <div class="jd-step">
                            <span class="jd-step-no">1</span>
                            <div class="jd-step-txt">
                                <b>File PDF</b>
                                <small>Untuk menghitung halaman &amp; menentukan harga</small>
                            </div>
                        </div>

                        @if (! $draftUploadId)
                        <label class="jd-drop">
                            <input type="file" wire:model="dokumenJasa" accept=".pdf" class="jd-drop-input">
                            <span wire:loading wire:target="dokumenJasa" class="jd-drop-state">
                                <i class="bi bi-arrow-repeat jd-spin"></i> Menghitung halaman…
                            </span>
                            <span wire:loading.remove wire:target="dokumenJasa" class="jd-drop-state">
                                <i class="bi bi-cloud-arrow-up jd-drop-ic"></i>
                                <span class="jd-drop-title">Pilih file PDF atau seret ke sini</span>
                                <span class="jd-drop-hint">Hanya PDF · maksimal 20 MB</span>
                            </span>
                        </label>
                        @error('dokumenJasa') <div class="jd-err">{{ $message }}</div> @enderror
                        @else
                        <div class="jd-file">
                            <i class="bi bi-file-earmark-check"></i>
                            <div class="jd-file-txt">
                                <b>{{ $draftNamaFile }}</b>
                                <small>{{ $jumlahHalaman }} halaman terbaca</small>
                            </div>
                            <button type="button" wire:click="hapusDraft" class="jd-file-x" title="Ganti file">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        {{-- Langkah 2: file kerja DOCX (yang benar-benar diparafrase) --}}
                        <div class="jd-step" style="margin-top:16px;">
                            <span class="jd-step-no">2</span>
                            <div class="jd-step-txt">
                                <b>File DOCX</b>
                                <small>Dokumen Word yang akan dikerjakan tim — formatnya tetap utuh</small>
                            </div>
                        </div>

                        @if (! $draftNamaKerja)
                        <label class="jd-drop">
                            <input type="file" wire:model="dokumenKerja" accept=".docx" class="jd-drop-input">
                            <span wire:loading wire:target="dokumenKerja" class="jd-drop-state">
                                <i class="bi bi-arrow-repeat jd-spin"></i> Mengunggah…
                            </span>
                            <span wire:loading.remove wire:target="dokumenKerja" class="jd-drop-state">
                                <i class="bi bi-file-earmark-word jd-drop-ic"></i>
                                <span class="jd-drop-title">Pilih file DOCX atau seret ke sini</span>
                                <span class="jd-drop-hint">Hanya DOCX &middot; maksimal 20 MB</span>
                            </span>
                        </label>
                        @error('dokumenKerja') <div class="jd-err">{{ $message }}</div> @enderror
                        @else
                        <div class="jd-file">
                            <i class="bi bi-file-earmark-check"></i>
                            <div class="jd-file-txt">
                                <b>{{ $draftNamaKerja }}</b>
                                <small>File kerja siap</small>
                            </div>
                            <button type="button" wire:click="hapusDraftKerja" class="jd-file-x" title="Ganti file">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        @endif

                        {{-- Bagian dokumen yang tak perlu diparafrase --}}
                        <div class="jd-exc" style="margin-top:16px;">
                            <div class="jd-exc-head">
                                <b>Bagian yang tidak perlu diparafrase</b>
                                <small>Biasanya bagian ini dibiarkan apa adanya. Hilangkan centang bila Anda ingin bagian itu tetap dikerjakan.</small>
                            </div>
                            <div class="jd-bagian">
                                <label class="jd-bagian-chip {{ $excludeCover ? 'is-on' : '' }}">
                                    <input type="checkbox" wire:model.live="excludeCover">
                                    <span class="jd-bagian-box"><i class="bi bi-check-lg"></i></span>
                                    <span>Cover</span>
                                </label>
                                <label class="jd-bagian-chip {{ $excludeDaftarIsi ? 'is-on' : '' }}">
                                    <input type="checkbox" wire:model.live="excludeDaftarIsi">
                                    <span class="jd-bagian-box"><i class="bi bi-check-lg"></i></span>
                                    <span>Daftar Isi</span>
                                </label>
                                <label class="jd-bagian-chip {{ $excludeDaftarPustaka ? 'is-on' : '' }}">
                                    <input type="checkbox" wire:model.live="excludeDaftarPustaka">
                                    <span class="jd-bagian-box"><i class="bi bi-check-lg"></i></span>
                                    <span>Daftar Pustaka</span>
                                </label>
                            </div>
                        </div>

                        {{-- Halaman yang tak perlu dikerjakan (tidak ditagih) --}}
                        <div class="jd-exc">
                            <div class="jd-exc-head">
                                <b>Ada halaman yang tidak perlu diparafrase?</b>
                                <small>Mis. cover, daftar isi, atau daftar pustaka. Halaman ini <b>tidak dihitung</b> dalam harga.</small>
                            </div>

                            <input type="text" class="jd-exc-input"
                                wire:model.live.debounce.500ms="halamanDikecualikan"
                                placeholder="Contoh: 1,2,12  atau  1-3,12">

                            <div class="jd-exc-quick">
                                <button type="button" wire:click="tandaiHalamanPertama" class="jd-exc-btn">
                                    <i class="bi bi-plus-lg"></i> Halaman pertama
                                </button>
                                <button type="button" wire:click="tandaiHalamanTerakhir" class="jd-exc-btn">
                                    <i class="bi bi-plus-lg"></i> Halaman terakhir
                                </button>
                                @if (count($this->halamanExclude))
                                <button type="button" wire:click="hapusHalamanExclude" class="jd-exc-btn is-clear">
                                    <i class="bi bi-x-lg"></i> Kosongkan
                                </button>
                                @endif
                            </div>

                            @if (count($this->halamanExclude))
                            <div class="jd-exc-info">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>
                                    Dikecualikan <b>{{ count($this->halamanExclude) }} halaman</b>
                                    (nomor {{ implode(', ', $this->halamanExclude) }}) —
                                    dibayar <b>{{ $this->halamanDihitung }} dari {{ $jumlahHalaman }} halaman</b>.
                                </span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Paket (disembunyikan untuk jasa per halaman) --}}
                    @if (! $product->jasaPerHalaman())
                    <div class="pd-packages">
                        <h4 class="pd-sub"><i class="bi bi-calendar2-week"></i> Pilih Paket</h4>
                        <div class="pd-pkg-grid">
                            {{-- Data kartu dari ProductDetail::paketTampil(); "Paling hemat" memakai
                                 aturan yang sama dengan jendela durasi di /shop. --}}
                            @foreach ($this->paketTampil as $pkg)
                                <button type="button"
                                    class="pd-pkg {{ $pkg['aktif'] ? 'is-active' : '' }}"
                                    wire:click="selectPackage('{{ $pkg['tipe'] }}', {{ $pkg['nilai'] }})">
                                    <span class="pd-pkg-dur">{{ $pkg['label'] }}@if ($pkg['terhemat'])<span class="pd-pkg-hemat">Paling hemat</span>@endif</span>
                                    <span class="pd-pkg-price">
                                        @if ($pkg['asli'])
                                            <span class="pd-pkg-old">{{ $pkg['asli'] }}</span>
                                        @endif
                                        <span class="pd-pkg-now">{{ $pkg['akhir'] }}</span>
                                    </span>
                                    @if ($pkg['hemat'])
                                        <span class="pd-pkg-save">{{ $pkg['hemat'] }}</span>
                                    @endif
                                    @if ($pkg['perBulan'])
                                        <span class="pd-pkg-per">{{ $pkg['perBulan'] }}</span>
                                    @endif
                                    <i class="bi bi-check-circle-fill pd-pkg-check"></i>
                                </button>
                            @endforeach

                            {{-- Durasi custom (seperti flash sale) --}}
                            {{-- Tanpa tanda lebih-besar di ekspresi ini: tanda itu sesudah direktif
                                 blok membuat Livewire melewati penanda morph-nya (juga bila ia ada
                                 di dalam komentar Blade). Harga per bulan tidak pernah negatif. --}}
                            @if ((int) ($product->harga_perbulan ?? 0))
                                @php $cp = $this->customPricing(); @endphp
                                <div class="pd-pkg pd-pkg-custom {{ $isCustom ? 'is-active' : '' }}">
                                    <button type="button" class="pd-pkg-custom-head" wire:click="chooseCustom">
                                        <span class="pd-pkg-dur">Durasi lain</span>
                                        <span class="pd-pkg-sub">
                                            @if ($cp['matched'])
                                                Sesuai paket {{ $pickCustomMonths }} bulan
                                            @else
                                                Rp {{ number_format($product->harga_perbulan, 0, ',', '.') }}/bulan
                                            @endif
                                        </span>
                                    </button>
                                    <div class="pd-stepper">
                                        <button type="button" wire:click="decCustom" @disabled($pickCustomMonths <= 1)>−</button>
                                        <span class="pd-stepper-val">{{ $pickCustomMonths }} bln</span>
                                        <button type="button" wire:click="incCustom" @disabled($pickCustomMonths >= 60)>+</button>
                                    </div>
                                    @if ($isCustom)
                                        <div class="pd-pkg-custom-total">
                                            <span class="pd-pkg-custom-label">Total {{ $pickCustomMonths }} bulan</span>
                                            @if ($cp['discounted'] < $cp['base'])
                                                <span class="pd-pkg-old">Rp {{ number_format($cp['base'], 0, ',', '.') }}</span>
                                            @endif
                                            <span class="pd-pkg-now">Rp {{ number_format($cp['discounted'], 0, ',', '.') }}</span>
                                            @if ($cp['savings'] > 0)
                                                <span class="pd-pkg-save">Hemat Rp {{ number_format($cp['savings'], 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <i class="bi bi-check-circle-fill pd-pkg-check"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Syarat bahasa diberitahukan SEBELUM bayar, bukan saat unggah.
                         Deteksi AI hanya akurat untuk teks Inggris, jadi dokumen
                         berbahasa lain akan ditolak sistem di halaman unggah. --}}
                    @if ($this->wajibInggris())
                    <div class="jd-lang">
                        <i class="bi bi-translate"></i>
                        <span>
                            <b>Dokumen wajib berbahasa Inggris</b>
                            Layanan deteksi AI hanya akurat untuk teks berbahasa Inggris. Dokumen
                            berbahasa lain akan ditolak saat diunggah, jadi pastikan Anda sudah
                            punya versi bahasa Inggrisnya sebelum memesan.
                        </span>
                    </div>
                    @endif

                    {{-- ===== Add-on opsional (produk jasa) ===== --}}
                    @if ($product->butuh_file && $product->addonAktif()->count())
                    <div class="pd-packages jd-addon-sec">
                        <h4 class="pd-sub"><i class="bi bi-plus-circle"></i> Tambahan Opsional</h4>
                        <p class="jd-hint">
                            {{ $product->addonPilihSatu() ? 'Pilih salah satu, atau lewati saja.' : 'Boleh pilih lebih dari satu, atau lewati saja.' }}
                        </p>
                        <div class="jd-addons">
                            @foreach ($product->addonAktif() as $ad)
                            @php $aktif = in_array($ad->id, $selectedAddons, true); @endphp
                            <button type="button" wire:click="toggleAddon('{{ $ad->id }}')"
                                class="jd-addon {{ $aktif ? 'is-on' : '' }}">
                                <span class="jd-addon-box"><i class="bi bi-check-lg"></i></span>
                                <span class="jd-addon-txt">
                                    <b>{{ $ad->nama }}</b>
                                    @if ($ad->keterangan)<small>{{ $ad->keterangan }}</small>@endif
                                </span>
                                <span class="jd-addon-harga">+Rp&nbsp;{{ number_format($ad->harga, 0, ',', '.') }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Ringkasan harga jasa per halaman --}}
                    @if ($product->jasaPerHalaman() && $jumlahHalaman > 0)
                    <div class="jd-total">
                        <div class="jd-total-row">
                            <span>{{ $this->halamanDihitung }} halaman × Rp {{ number_format($product->hargaPerHalaman(), 0, ',', '.') }}</span>
                            <b>Rp {{ number_format($this->hargaPerHalamanTotal, 0, ',', '.') }}</b>
                        </div>
                        @if (count($this->halamanExclude))
                        <div class="jd-total-row" style="color:#15803d;">
                            <span>{{ count($this->halamanExclude) }} halaman dikecualikan</span>
                            <b>tidak ditagih</b>
                        </div>
                        @endif
                        @if ($this->addonsTotal > 0)
                        <div class="jd-total-row">
                            <span>Tambahan</span>
                            <b>+Rp {{ number_format($this->addonsTotal, 0, ',', '.') }}</b>
                        </div>
                        @endif
                        <div class="jd-total-row is-final">
                            <span>Total</span>
                            <b>Rp {{ number_format($this->hargaPerHalamanTotal + $this->addonsTotal, 0, ',', '.') }}</b>
                        </div>
                    </div>
                    @endif

                    @php $dijeda = \App\Support\JedaLayanan::produkDijeda($product); @endphp

                    @if ($dijeda)
                    {{-- Layanan dijeda: halaman tetap utuh supaya calon pembeli tahu
                         layanan ini ada, hanya pintu belinya yang ditutup. --}}
                    <div class="pd-jeda">
                        <i class="bi bi-pause-circle"></i>
                        <div>
                            <b>Sedang tidak menerima pesanan baru</b>
                            <span>{{ \App\Support\JedaLayanan::pesanProduk($product) }}</span>
                        </div>
                    </div>
                    @endif

                    {{-- Beli --}}
                    <div class="pd-buy">
                        <button type="button" class="pd-add" wire:click="addToCart"
                            wire:loading.attr="disabled" wire:target="addToCart"
                            @disabled($dijeda)>
                            @if ($dijeda)
                            <span><i class="bi bi-pause-circle"></i> Pesanan Ditutup Sementara</span>
                            @else
                            <span wire:loading.remove wire:target="addToCart"><i class="bi bi-cart-plus"></i> Tambah ke Keranjang</span>
                            <span wire:loading wire:target="addToCart"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                            @endif
                        </button>

                        <button type="button" class="pd-wish"
                            x-data="{ saved: false }"
                            x-init="saved = (JSON.parse(localStorage.getItem('ph_wishlist')||'[]')).includes('{{ $product->id }}')"
                            @click="
                                let w = JSON.parse(localStorage.getItem('ph_wishlist')||'[]');
                                if (w.includes('{{ $product->id }}')) { w = w.filter(i => i !== '{{ $product->id }}'); saved = false; }
                                else { w.push('{{ $product->id }}'); saved = true; }
                                localStorage.setItem('ph_wishlist', JSON.stringify(w));
                                window.dispatchEvent(new Event('ph-wishlist-changed'));
                                if (window.phToast) phToast(saved ? 'Ditambahkan ke wishlist' : 'Dihapus dari wishlist', 'Wishlist', saved ? 'bi-heart-fill' : 'bi-heart');
                            ">
                            <i class="bi" :class="saved ? 'bi-heart-fill' : 'bi-heart'"></i>
                            <span x-text="saved ? 'Tersimpan di Wishlist' : 'Simpan ke Wishlist'"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- Ulasan & rating produk --}}
    {{-- Selebar kontainer, bukan dikurung 800px di tengah: kepala bagiannya
         kini sama dengan "Produk Terkait" di bawahnya, dan dua kepala bagian
         yang tepi kirinya berbeda terbaca sebagai dua halaman. Warna kategori
         diteruskan lewat --c supaya aksen ulasan sewarna dengan produknya. --}}
    <section class="rev-section" style="--c: {{ $warnaKat ?? '#f26522' }}">
        <div class="container">
            @livewire(\App\Livewire\Components\ProductReviews::class, ['productId' => $product->id], key('rev-'.$product->id))
        </div>
    </section>

    {{-- Produk lainnya / rekomendasi. Data kartu dirakit di
         ProductDetail::kartuTerkait() — harga, promo, kategori, dan gambar yang
         benar-benar ada — supaya Blade di sini tidak menghitung apa pun. --}}
    @if (count($this->kartuTerkait))
        <section class="rel-section">
            <div class="container">
                {{-- Kepala bagian yang sama dengan seluruh bagian beranda,
                     bukan kepala tengah bergaya lama — satu bagian yang
                     judulnya tersusun berbeda terbaca sebagai halaman lain. --}}
                <x-kepala-bagian
                    ikon="bi-grid-3x3-gap-fill"
                    kicker="Produk Lainnya"
                    judul="Mungkin Anda juga suka"
                    :tautan-url="route('shop.index')"
                    tautan-teks="Lihat Semua Produk" />
                <div class="rk-deret">
                    @foreach ($this->kartuTerkait as $k)
                        <a href="{{ $k['url'] }}" class="rk-kartu" style="--c: {{ $k['warna'] }}" wire:key="rk-{{ $k['id'] }}">
                            <span class="rk-media {{ $k['gambar'] ? '' : 'is-kosong' }}">
                                @if ($k['gambar'])
                                    {{-- alt kosong: namanya tercetak tepat di bawah. Gagal dimuat
                                         di peramban pun, gambar diganti ubin ikon kategori. --}}
                                    <img src="{{ $k['gambar'] }}" alt="" loading="lazy"
                                        onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                                @endif
                                <span class="rk-cadangan" aria-hidden="true"><i class="bi {{ $k['ikon'] }}"></i></span>
                                @if ($k['kategori'])
                                    <span class="rk-kat"><i class="bi {{ $k['ikon'] }}"></i><span>{{ $k['kategori'] }}</span></span>
                                @endif
                                @if ($k['diskon'])
                                    <span class="rk-diskon {{ $k['flash'] ? 'is-flash' : '' }}"><i class="bi {{ $k['flash'] ? 'bi-lightning-charge-fill' : 'bi-tag-fill' }}"></i>{{ $k['diskon'] }}</span>
                                @endif
                            </span>
                            <span class="rk-isi">
                                <span class="rk-jenis">{{ $k['jenis'] }}</span>
                                <span class="rk-nama">{{ $k['nama'] }}</span>
                                <span class="rk-kaki">
                                    <span class="rk-harga">
                                        @if ($k['hargaAsli'])
                                            <s>Rp{{ number_format($k['hargaAsli'], 0, ',', '.') }}</s>
                                        @endif
                                        @if ($k['harga'])
                                            <b>@if ($k['mulai'])<small class="rk-mulai">Mulai</small>@endif Rp{{ number_format($k['harga'], 0, ',', '.') }}<small>{{ $k['satuan'] }}</small></b>
                                        @else
                                            <b>Lihat detail</b>
                                        @endif
                                    </span>
                                    <span class="rk-panah" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    {{-- Meta Pixel: ViewContent.
         Dipasang sebagai skrip halaman (bukan lewat dispatch Livewire) karena ini
         peristiwa saat halaman DIMUAT. Saat pengunjung berpindah produk lewat
         wire:navigate, skrip ini jalan lagi — dan memang itu yang benar: produk
         berbeda = ViewContent baru.

         Nilai (value) sengaja tidak dikirim: harga di halaman ini berubah mengikuti
         paket durasi yang dipilih pengunjung, jadi angka saat halaman dimuat belum
         tentu yang dimaksud. Untuk retargeting, id & nama produk sudah cukup. --}}
    <script>
        (function () {
            if (typeof fbq !== 'function') return;
            fbq('track', 'ViewContent', {
                content_ids: [@json((string) $product->id)],
                content_name: @json((string) $product->nama_akun),
                content_type: 'product',
                currency: 'IDR'
            });
        })();
    </script>
</main>
