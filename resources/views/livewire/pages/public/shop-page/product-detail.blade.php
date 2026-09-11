<main class="main">
    @include('partials.media-produk-style')
    <style>
    /* Pemberitahuan layanan dijeda — memakai warna peringatan, bukan aksen toko,
       supaya terbaca sebagai keadaan sementara dan bukan bagian dari promosi.
       Jarak atas 22px menyamai .pd-buy di public-custom-styles.css, sehingga
       kotak ini mengikuti ritme halaman dan tidak menempel ke kartu paket di
       atasnya; jarak bawahnya diserahkan ke margin .pd-buy itu sendiri. */
    .pd-jeda { margin:22px 0 0; border:1px solid #fde68a; border-radius:18px; overflow:hidden;
        background:linear-gradient(135deg, #fffbeb 0%, #fff 72%);
        box-shadow:0 16px 34px -26px rgba(217,119,6,.55); }
    .pd-jeda-isi { display:flex; align-items:flex-start; gap:14px; padding:16px 18px; }
    /* Ubin ikon: glif tunggal di tengah — display:block + line-height:1 */
    .pd-jeda-ic { width:46px; height:46px; flex-shrink:0; border-radius:15px; display:flex; align-items:center; justify-content:center;
        background:linear-gradient(140deg, #f59e0b, #d97706); color:#fff; font-size:1.3rem;
        box-shadow:0 0 0 5px #fef3c7, 0 12px 22px -12px rgba(217,119,6,.9); }
    .pd-jeda-ic i.bi, .pd-jeda-ic i.bi::before { display:block; line-height:1; }
    .pd-jeda-txt { flex:1; min-width:0; display:flex; flex-direction:column; align-items:flex-start; gap:4px; }
    .pd-jeda-lencana { display:inline-flex; align-items:center; gap:6px; height:22px; padding:0 9px; border-radius:99px;
        background:#fef3c7; color:#92400e; font-size:.66rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .pd-jeda-titik { width:7px; height:7px; border-radius:50%; background:#d97706; animation:pdJedaDenyut 1.8s ease-out infinite; }
    @keyframes pdJedaDenyut { 0% { box-shadow:0 0 0 0 rgba(217,119,6,.5); } 70%, 100% { box-shadow:0 0 0 7px rgba(217,119,6,0); } }
    .pd-jeda-judul { font-family:'Plus Jakarta Sans','Poppins',sans-serif; font-size:.98rem; font-weight:800; color:#78350f; line-height:1.35; }
    .pd-jeda-pesan { font-size:.85rem; color:#92400e; line-height:1.6; }
    .pd-jeda-aksi { display:flex; flex-wrap:wrap; align-items:center; gap:8px 10px; padding:12px 18px;
        border-top:1px dashed #fcd34d; background:rgba(254,243,199,.35); }
    .pd-jeda-btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; height:38px; padding:0 15px;
        border-radius:11px; font-size:.8rem; font-weight:700; text-decoration:none; transition:background .18s, border-color .18s, color .18s; }
    .pd-jeda-btn i.bi, .pd-jeda-btn i.bi::before { display:block; line-height:1; font-size:.95rem; }
    .pd-jeda-btn.is-wa { background:#16a34a; color:#fff; box-shadow:0 8px 16px -10px rgba(22,163,74,.9); }
    .pd-jeda-btn.is-wa:hover { background:#15803d; color:#fff; }
    .pd-jeda-btn.is-lain { background:#fff; color:#92400e; border:1px solid #fcd34d; }
    .pd-jeda-btn.is-lain:hover { background:#fffbeb; border-color:#f59e0b; color:#78350f; }
    /* Tombol beli saat dijeda: netral & jelas nonaktif — dulu jingga pudar
       (opacity+grayscale) sehingga terlihat seperti tombol yang rusak. */
    .pd-buy .pd-add.is-jeda, .pd-buy .pd-add.is-jeda:hover {
        background:#f1f5f9 !important; color:#64748b !important; border:1.5px dashed #cbd5e1 !important;
        box-shadow:none !important; transform:none !important; filter:none !important; text-shadow:none !important;
        opacity:1 !important; cursor:not-allowed; }
    .pd-add:disabled:not(.is-jeda) { opacity:.55; cursor:not-allowed; filter:grayscale(.35); }
    @media (max-width:575.98px) {
        .pd-jeda-isi { padding:14px; gap:12px; }
        .pd-jeda-aksi { padding:12px 14px; }
        .pd-jeda-btn { flex:1 1 auto; }
    }
    @media (prefers-reduced-motion: reduce) { .pd-jeda-titik { animation:none; } }

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
            .pd-col-media .pd-media { flex:1; min-height:0; display:flex; align-items:center; justify-content:center; }
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

        /* ===== Pengaturan dokumen, add-on & rincian biaya (jasa) =====
           Tiap blok = kartu putih dengan kepala berikon warna (--k), pola yang
           sama dengan langkah "Cara Pesan". Ikon tunggal di dalam ubin selalu
           display:block + line-height:1 supaya benar-benar di tengah. */
        .jd-kartu { --k:#d97706; margin-top:14px; padding:16px 18px 18px; border-radius:18px;
            border:1px solid color-mix(in srgb, var(--k) 16%, #eceff4);
            background:linear-gradient(180deg, color-mix(in srgb, var(--k) 5%, #fff) 0, #fff 76px); }
        .jd-kepala { display:flex; align-items:flex-start; gap:12px; margin-bottom:14px; }
        .jd-kepala-ic { width:40px; height:40px; flex-shrink:0; border-radius:13px; display:flex; align-items:center; justify-content:center;
            background:color-mix(in srgb, var(--k) 13%, #fff); color:var(--k); font-size:1.05rem;
            box-shadow:inset 0 0 0 1px color-mix(in srgb, var(--k) 18%, transparent); }
        .jd-kepala-ic i.bi, .jd-kepala-ic i.bi::before { display:block; line-height:1; }
        .jd-kepala-txt { display:flex; flex-direction:column; gap:3px; min-width:0; padding-top:1px; }
        .jd-kepala-txt > b { font-family:'Plus Jakarta Sans','Poppins',sans-serif; font-size:.94rem; font-weight:800; color:#0f172a; line-height:1.3; }
        .jd-kepala-txt > small { font-size:.78rem; color:#64748b; line-height:1.5; }
        .jd-kepala-txt > small b { color:#334155; }

        /* Bagian dokumen yang dilewati: tiga ubin pilihan, warna per bagian (--o) */
        .jd-bagian { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:10px; }
        .jd-bagian-chip { --o:#2563eb; position:relative; display:flex; flex-direction:column; align-items:center; gap:7px;
            padding:15px 8px 12px; border-radius:15px; border:1.5px solid #e8ecf2; background:#fff; text-align:center;
            cursor:pointer; user-select:none; transition:border-color .18s, background .18s, box-shadow .18s, transform .18s; }
        .jd-bagian-chip:hover { border-color:color-mix(in srgb, var(--o) 40%, #fff); transform:translateY(-1px); }
        .jd-bagian-chip.is-on { border-color:var(--o); background:color-mix(in srgb, var(--o) 6%, #fff);
            box-shadow:0 8px 18px -12px color-mix(in srgb, var(--o) 70%, transparent); }
        .jd-bagian-chip input { position:absolute; opacity:0; width:0; height:0; pointer-events:none; }
        .jd-bagian-chip:has(input:focus-visible) { outline:2px solid var(--o); outline-offset:2px; }
        .jd-bagian-ic { width:40px; height:40px; border-radius:13px; display:flex; align-items:center; justify-content:center;
            background:color-mix(in srgb, var(--o) 12%, #fff); color:var(--o); font-size:1.08rem; transition:background .18s, color .18s; }
        .jd-bagian-chip.is-on .jd-bagian-ic { background:var(--o); color:#fff; }
        .jd-bagian-ic i.bi, .jd-bagian-ic i.bi::before { display:block; line-height:1; }
        .jd-bagian-nama { font-size:.82rem; font-weight:800; color:#0f172a; line-height:1.25; }
        .jd-bagian-status { font-size:.7rem; font-weight:700; color:#94a3b8; }
        .jd-bagian-chip.is-on .jd-bagian-status { color:color-mix(in srgb, var(--o) 78%, #0f172a); }
        .jd-bagian-box { position:absolute; top:8px; right:8px; width:18px; height:18px; border-radius:50%;
            border:1.5px solid #cbd5e1; background:#fff; display:flex; align-items:center; justify-content:center;
            transition:background .18s, border-color .18s; }
        .jd-bagian-box i.bi { font-size:.58rem; color:#fff; opacity:0; display:block; line-height:1; }
        .jd-bagian-box i.bi::before { display:block; line-height:1; }
        .jd-bagian-chip.is-on .jd-bagian-box { background:var(--o); border-color:var(--o); }
        .jd-bagian-chip.is-on .jd-bagian-box i.bi { opacity:1; }

        /* Halaman yang dikecualikan (tidak ditagih) */
        .jd-exc-field { position:relative; display:block; margin:0; }
        .jd-exc-field > i.bi { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--k);
            font-size:.95rem; pointer-events:none; }
        .jd-exc-field > i.bi, .jd-exc-field > i.bi::before { display:block; line-height:1; }
        .jd-exc-input { width:100%; font-size:.9rem; padding:12px 14px 12px 40px; border:1.5px solid #e8ecf2;
            border-radius:12px; background:#fff; color:#0f172a; outline:none; transition:border-color .18s, box-shadow .18s; }
        .jd-exc-input::placeholder { color:#94a3b8; }
        .jd-exc-input:focus { border-color:var(--k); box-shadow:0 0 0 4px color-mix(in srgb, var(--k) 14%, transparent); }
        .jd-exc-quick { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
        .jd-exc-btn { display:inline-flex; align-items:center; gap:6px; height:32px; padding:0 13px; border-radius:99px;
            border:1px solid color-mix(in srgb, var(--k) 22%, #e8ecf2); background:color-mix(in srgb, var(--k) 5%, #fff);
            color:color-mix(in srgb, var(--k) 78%, #0f172a); font-size:.77rem; font-weight:700;
            cursor:pointer; transition:border-color .18s, background .18s; }
        .jd-exc-btn:hover { border-color:color-mix(in srgb, var(--k) 45%, #fff); background:color-mix(in srgb, var(--k) 12%, #fff); }
        .jd-exc-btn.is-clear { border-color:#fecaca; background:#fff5f5; color:#dc2626; }
        .jd-exc-btn.is-clear:hover { border-color:#fca5a5; background:#fee2e2; }
        .jd-exc-btn i.bi, .jd-exc-btn i.bi::before { display:block; line-height:1; font-size:.72rem; }
        .jd-exc-info { display:flex; align-items:center; gap:10px; margin-top:12px; padding:10px 12px; border-radius:12px;
            border:1px solid #bbf7d0; background:#f0fdf4; font-size:.8rem; color:#166534; line-height:1.5; }
        .jd-exc-info-ic { width:26px; height:26px; flex-shrink:0; border-radius:8px; background:#16a34a; color:#fff;
            display:flex; align-items:center; justify-content:center; font-size:.8rem; }
        .jd-exc-info-ic i.bi, .jd-exc-info-ic i.bi::before { display:block; line-height:1; }

        /* Add-on — dipisah jelas dari blok paket di atasnya. Warna kartu (--a)
           dari App\Support\IkonAddon. */
        .jd-addon-sec { margin-top:26px; padding-top:22px; border-top:1px solid var(--ph-line); }
        .jd-addon-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px 12px; margin-bottom:6px; }
        .jd-addon-head .pd-sub { margin-bottom:0; }
        .jd-addon-mode { display:inline-flex; align-items:center; gap:6px; height:26px; padding:0 11px; border-radius:99px;
            background:#f1f5f9; color:#475569; font-size:.72rem; font-weight:700; }
        .jd-addon-mode i.bi, .jd-addon-mode i.bi::before { display:block; line-height:1; font-size:.72rem; }
        /* min(250px,100%) & min-width:0: kolom tidak melebar mengikuti isinya —
           dulu di HP kartunya luber ±30px ke kanan. */
        .jd-addons { display:grid; grid-template-columns:repeat(auto-fit, minmax(min(250px, 100%), 1fr)); gap:12px; }
        .jd-addon { --a:#d97706; position:relative; display:flex; flex-direction:column; gap:12px; width:100%; min-width:0;
            text-align:left; padding:15px 16px 13px; border:1.5px solid #e8ecf2; border-radius:16px; background:#fff;
            cursor:pointer; transition:border-color .18s, background .18s, box-shadow .18s, transform .18s; }
        .jd-addon:hover { border-color:color-mix(in srgb, var(--a) 40%, #fff); transform:translateY(-1px);
            box-shadow:0 12px 24px -18px color-mix(in srgb, var(--a) 70%, transparent); }
        .jd-addon.is-on { border-color:var(--a); background:linear-gradient(180deg, color-mix(in srgb, var(--a) 7%, #fff), #fff 70%);
            box-shadow:0 12px 26px -16px color-mix(in srgb, var(--a) 70%, transparent); }
        .jd-addon-atas { display:flex; align-items:flex-start; gap:12px; padding-right:28px; }
        .jd-addon-ic { width:40px; height:40px; flex-shrink:0; border-radius:13px; display:flex; align-items:center; justify-content:center;
            background:color-mix(in srgb, var(--a) 12%, #fff); color:var(--a); font-size:1.1rem; transition:background .18s, color .18s; }
        .jd-addon.is-on .jd-addon-ic { background:var(--a); color:#fff; }
        .jd-addon-ic i.bi, .jd-addon-ic i.bi::before { display:block; line-height:1; }
        .jd-addon-txt { flex:1; min-width:0; display:flex; flex-direction:column; gap:3px; }
        .jd-addon-txt b { font-size:.88rem; font-weight:800; color:#0f172a; line-height:1.35;
            display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .jd-addon-txt small { font-size:.76rem; color:#64748b; line-height:1.45; }
        .jd-addon-box { position:absolute; top:14px; right:14px; width:22px; height:22px; border-radius:50%;
            border:1.5px solid #cbd5e1; background:#fff; display:flex; align-items:center; justify-content:center;
            transition:background .18s, border-color .18s; }
        .jd-addon-box i.bi { font-size:.66rem; color:#fff; opacity:0; display:block; line-height:1; }
        .jd-addon-box i.bi::before { display:block; line-height:1; }
        .jd-addon.is-on .jd-addon-box { background:var(--a); border-color:var(--a); }
        .jd-addon.is-on .jd-addon-box i.bi { opacity:1; }
        .jd-addon-bawah { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:auto;
            padding-top:11px; border-top:1px dashed #e8ecf2; }
        .jd-addon-harga { font-size:.9rem; font-weight:800; color:#0f172a; white-space:nowrap; }
        .jd-addon-harga.is-gratis { color:#15803d; }
        .jd-addon-aksi { display:inline-flex; align-items:center; gap:5px; font-size:.74rem; font-weight:700;
            color:color-mix(in srgb, var(--a) 80%, #0f172a); white-space:nowrap; }
        .jd-addon-aksi i.bi, .jd-addon-aksi i.bi::before { display:block; line-height:1; font-size:.72rem; }

        /* Rincian biaya: struk kecil — tiap baris berikon, total menonjol */
        .jd-rincian { margin:18px 0 4px; border-radius:18px; overflow:hidden; background:#fff;
            border:1px solid color-mix(in srgb, var(--c) 20%, #eceff4);
            box-shadow:0 16px 34px -26px color-mix(in srgb, var(--c) 70%, transparent); }
        .jd-rincian-head { --k:var(--c); display:flex; align-items:center; gap:12px; padding:14px 18px;
            background:linear-gradient(90deg, color-mix(in srgb, var(--c) 9%, #fff), #fff);
            border-bottom:1px solid color-mix(in srgb, var(--c) 12%, #eceff4); }
        .jd-rincian-list { list-style:none; margin:0; padding:4px 18px; }
        .jd-rincian-row { --r:#64748b; display:flex; align-items:center; gap:11px; padding:10px 0; font-size:.85rem; color:#334155; }
        .jd-rincian-row + .jd-rincian-row { border-top:1px solid #f1f5f9; }
        .jd-rincian-ic { width:30px; height:30px; flex-shrink:0; border-radius:10px; display:flex; align-items:center; justify-content:center;
            background:color-mix(in srgb, var(--r) 12%, #fff); color:var(--r); font-size:.85rem; }
        .jd-rincian-ic i.bi, .jd-rincian-ic i.bi::before { display:block; line-height:1; }
        .jd-rincian-lbl { flex:1; min-width:0; display:flex; flex-direction:column; gap:1px; font-weight:600; color:#1e293b; line-height:1.35; }
        .jd-rincian-lbl small { font-size:.73rem; font-weight:500; color:#94a3b8; }
        .jd-rincian-nilai { font-weight:800; color:#0f172a; white-space:nowrap; }
        .jd-rincian-nilai.is-hijau { color:#15803d; }
        .jd-rincian-total { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 18px;
            background:color-mix(in srgb, var(--c) 6%, #fff); border-top:1px dashed color-mix(in srgb, var(--c) 35%, #fff); }
        .jd-rincian-total > span { display:flex; flex-direction:column; font-size:.88rem; font-weight:800; color:#0f172a; }
        .jd-rincian-total > span small { font-size:.72rem; font-weight:500; color:#64748b; }
        .jd-rincian-total > b { font-family:'Plus Jakarta Sans','Poppins',sans-serif; font-size:1.35rem; font-weight:800;
            color:#ea580c; letter-spacing:-.01em; white-space:nowrap; }

        @media (max-width:575.98px) {
            .jd-kartu { padding:14px 14px 16px; }
            .jd-bagian { gap:8px; }
            .jd-bagian-chip { padding:13px 6px 11px; }
            .jd-bagian-ic { width:36px; height:36px; border-radius:12px; font-size:1rem; }
            .jd-addon-sec { margin-top:22px; padding-top:18px; }
            .jd-addons { grid-template-columns:minmax(0, 1fr); }
            .jd-rincian-list { padding:4px 14px; }
            .jd-rincian-head, .jd-rincian-total { padding:13px 14px; }
            .jd-rincian-total > b { font-size:1.2rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .jd-bagian-chip, .jd-addon { transition:none; }
            .jd-bagian-chip:hover, .jd-addon:hover { transform:none; }
        }

        /* ===================================================================
           TAMPILAN BARU HALAMAN DETAIL PRODUK

           Warna halaman mengikuti KATEGORI produknya (--c, dipasang di kartu
           judul dan .pd-section). Unsur identitas — gambar, label kategori,
           paket terpilih, ikon subjudul — memakai warna itu. Harga dan tombol
           beli tetap jingga merek: di seluruh toko, jingga berarti "bayar di
           sini", dan tombol beli yang berganti warna tiap produk membuat orang
           harus mencarinya lagi di tiap halaman.
           =================================================================== */

        /* Gutter vertikal Bootstrap .g-lg-5 memberi .pd-row margin atas -48px,
           lebih besar dari jarak atas .pd-section (40px, CSS publik beku):
           isinya naik 4px MENIMPA tepi bawah kartu judul. Dinolkan, lalu jarak
           atas section diatur ulang supaya ada celah rapi ±22px. */
        @media (min-width: 992px) {
            .pd-section { padding-top: 18px; }
            .pd-section .pd-row { margin-top: 0; }
        }

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

        /* Tanpa gambar (atau berkasnya hilang): ubin besar ikon kategori,
           bukan teks alt mentah di pojok kotak kosong. Sama dengan detail paket. */
        .pd-ubin { display: none; }
        .pd-media.is-kosong { display: flex; align-items: center; justify-content: center; min-height: 300px; }
        /* position+z-index: .pd-media::after (kilau putih, absolut) menutupi
           ubin dan membuatnya pucat bila ubin tidak diangkat di atasnya. */
        .pd-media.is-kosong .pd-ubin {
            position: relative; z-index: 1;
            display: flex; align-items: center; justify-content: center;
            width: 112px; height: 112px; border-radius: 34px;
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff));
            color: #fff; font-size: 2.8rem;
            box-shadow: 0 0 0 6px #fff, 0 24px 44px -18px color-mix(in srgb, var(--c) 85%, transparent);
            transform: rotate(-6deg); transition: transform .35s ease;
        }
        .pd-media.is-kosong:hover .pd-ubin { transform: rotate(0deg) translateY(-4px); }
        .pd-ubin i.bi, .pd-ubin i.bi::before { display: block; line-height: 1; }
        @media (max-width: 575.98px) {
            .pd-media.is-kosong { min-height: 240px; }
            .pd-media.is-kosong .pd-ubin { width: 88px; height: 88px; border-radius: 28px; font-size: 2.2rem; }
        }
        @media (prefers-reduced-motion: reduce) { .pd-ubin { transition: none; } }

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
        $ikonKat = $kat['ikon'] ?? 'bi-box-seam';

        // Gambar hanya dipakai bila berkasnya benar-benar ada; selain itu
        // kotak gambar menampilkan ubin ikon kategori.
        $gambarProduk = $product->image && is_file(public_path('storage/img/Product/' . $product->image))
            ? asset('storage/img/Product/' . $product->image)
            : null;
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

    <section class="pd-section" data-pd-beku style="--c: {{ $warnaKat }}">
        <div class="container">
            <div class="row g-4 g-lg-5 pd-row">
                {{-- Media --}}
                <div class="col-lg-6 pd-col-media">
                    <div class="pd-media {{ $gambarProduk ? '' : 'is-kosong' }}">
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
                        @if ($gambarProduk)
                            <img src="{{ $gambarProduk }}" alt="{{ $product->nama_akun }}" onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                        @endif
                        <span class="pd-ubin" aria-hidden="true"><i class="bi {{ $ikonKat }}"></i></span>
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
                            <input type="file" wire:model="dokumenJasa" accept=".pdf" class="jd-drop-input" title="">
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
                            <input type="file" wire:model="dokumenKerja" accept=".docx" class="jd-drop-input" title="">
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
                        @php
                            $bagianDok = [
                                ['model' => 'excludeCover', 'on' => $excludeCover, 'label' => 'Cover', 'ikon' => 'bi-journal-bookmark', 'warna' => '#2563eb'],
                                ['model' => 'excludeDaftarIsi', 'on' => $excludeDaftarIsi, 'label' => 'Daftar Isi', 'ikon' => 'bi-list-ol', 'warna' => '#7c3aed'],
                                ['model' => 'excludeDaftarPustaka', 'on' => $excludeDaftarPustaka, 'label' => 'Daftar Pustaka', 'ikon' => 'bi-book', 'warna' => '#0d9488'],
                            ];
                        @endphp
                        <div class="jd-kartu" style="--k: #d97706; margin-top:16px;">
                            <div class="jd-kepala">
                                <span class="jd-kepala-ic"><i class="bi bi-bookmark-x"></i></span>
                                <span class="jd-kepala-txt">
                                    <b>Bagian yang tidak perlu diparafrase</b>
                                    <small>Bagian yang dicentang dibiarkan apa adanya. Hilangkan centang bila Anda ingin bagian itu tetap dikerjakan.</small>
                                </span>
                            </div>
                            <div class="jd-bagian">
                                @foreach ($bagianDok as $bg)
                                <label class="jd-bagian-chip {{ $bg['on'] ? 'is-on' : '' }}" style="--o: {{ $bg['warna'] }}">
                                    <input type="checkbox" wire:model.live="{{ $bg['model'] }}">
                                    <span class="jd-bagian-box"><i class="bi bi-check-lg"></i></span>
                                    <span class="jd-bagian-ic"><i class="bi {{ $bg['ikon'] }}"></i></span>
                                    <span class="jd-bagian-nama">{{ $bg['label'] }}</span>
                                    <small class="jd-bagian-status">{{ $bg['on'] ? 'Dilewati' : 'Diparafrase' }}</small>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Halaman yang tak perlu dikerjakan (tidak ditagih) --}}
                        <div class="jd-kartu" style="--k: #2563eb">
                            <div class="jd-kepala">
                                <span class="jd-kepala-ic"><i class="bi bi-file-earmark-minus"></i></span>
                                <span class="jd-kepala-txt">
                                    <b>Ada halaman yang tidak perlu diparafrase?</b>
                                    <small>Mis. cover, daftar isi, atau daftar pustaka. Halaman ini <b>tidak dihitung</b> dalam harga.</small>
                                </span>
                            </div>

                            <label class="jd-exc-field">
                                <i class="bi bi-hash"></i>
                                <input type="text" class="jd-exc-input"
                                    wire:model.live.debounce.500ms="halamanDikecualikan"
                                    aria-label="Nomor halaman yang tidak perlu diparafrase"
                                    placeholder="Contoh: 1,2,12  atau  1-3,12">
                            </label>

                            <div class="jd-exc-quick">
                                <button type="button" wire:click="tandaiHalamanPertama" class="jd-exc-btn">
                                    <i class="bi bi-skip-start"></i> Halaman pertama
                                </button>
                                <button type="button" wire:click="tandaiHalamanTerakhir" class="jd-exc-btn">
                                    <i class="bi bi-skip-end"></i> Halaman terakhir
                                </button>
                                @if (count($this->halamanExclude))
                                <button type="button" wire:click="hapusHalamanExclude" class="jd-exc-btn is-clear">
                                    <i class="bi bi-x-lg"></i> Kosongkan
                                </button>
                                @endif
                            </div>

                            @if (count($this->halamanExclude))
                            <div class="jd-exc-info">
                                <span class="jd-exc-info-ic"><i class="bi bi-check-lg"></i></span>
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
                        <div class="jd-addon-head">
                            <h4 class="pd-sub"><i class="bi bi-plus-circle"></i> Tambahan Opsional</h4>
                            <span class="jd-addon-mode">
                                <i class="bi {{ $product->addonPilihSatu() ? 'bi-record-circle' : 'bi-ui-checks-grid' }}"></i>
                                {{ $product->addonPilihSatu() ? 'Pilih salah satu' : 'Boleh lebih dari satu' }}
                            </span>
                        </div>
                        <p class="jd-hint">Tidak wajib — lewati saja bila tidak diperlukan.</p>
                        <div class="jd-addons">
                            @foreach ($product->addonAktif() as $ad)
                            @php
                                $aktif = in_array($ad->id, $selectedAddons, true);
                                $ia = \App\Support\IkonAddon::untuk($ad->nama, (bool) $ad->cek_ai);
                            @endphp
                            <button type="button" wire:click="toggleAddon('{{ $ad->id }}')"
                                class="jd-addon {{ $aktif ? 'is-on' : '' }}" style="--a: {{ $ia['warna'] }}"
                                aria-pressed="{{ $aktif ? 'true' : 'false' }}">
                                <span class="jd-addon-atas">
                                    <span class="jd-addon-ic"><i class="bi {{ $ia['ikon'] }}"></i></span>
                                    <span class="jd-addon-txt">
                                        <b>{{ $ad->nama }}</b>
                                        @if ($ad->keterangan)<small>{{ $ad->keterangan }}</small>@endif
                                    </span>
                                </span>
                                <span class="jd-addon-box"><i class="bi bi-check-lg"></i></span>
                                <span class="jd-addon-bawah">
                                    <span class="jd-addon-harga {{ $ad->harga ? '' : 'is-gratis' }}">{{ $ad->harga ? '+Rp '.number_format($ad->harga, 0, ',', '.') : 'Gratis' }}</span>
                                    <span class="jd-addon-aksi"><i class="bi {{ $aktif ? 'bi-check2-circle' : 'bi-plus-lg' }}"></i> {{ $aktif ? 'Ditambahkan' : 'Tambahkan' }}</span>
                                </span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Rincian biaya jasa per halaman: tiap add-on terpilih dirinci,
                         bukan digabung jadi satu baris "Tambahan". --}}
                    @if ($product->jasaPerHalaman() && $jumlahHalaman)
                    @php
                        $addonDipilih = $product->addonAktif()->whereIn('id', $selectedAddons);
                        $jmlKecuali = count($this->halamanExclude);
                    @endphp
                    <div class="jd-rincian">
                        <div class="jd-rincian-head">
                            <span class="jd-kepala-ic"><i class="bi bi-receipt"></i></span>
                            <span class="jd-kepala-txt">
                                <b>Rincian Biaya</b>
                                <small>Dari {{ $jumlahHalaman }} halaman yang terbaca di PDF Anda.</small>
                            </span>
                        </div>
                        <ul class="jd-rincian-list">
                            <li class="jd-rincian-row" style="--r: #2563eb">
                                <span class="jd-rincian-ic"><i class="bi bi-file-earmark-text"></i></span>
                                <span class="jd-rincian-lbl">
                                    Parafrase {{ $this->halamanDihitung }} halaman
                                    <small>Rp {{ number_format($product->hargaPerHalaman(), 0, ',', '.') }} / halaman</small>
                                </span>
                                <b class="jd-rincian-nilai">Rp {{ number_format($this->hargaPerHalamanTotal, 0, ',', '.') }}</b>
                            </li>
                            @if ($jmlKecuali)
                            <li class="jd-rincian-row" style="--r: #16a34a">
                                <span class="jd-rincian-ic"><i class="bi bi-dash-circle"></i></span>
                                <span class="jd-rincian-lbl">
                                    {{ $jmlKecuali }} halaman dikecualikan
                                    <small>Nomor {{ implode(', ', $this->halamanExclude) }}</small>
                                </span>
                                <b class="jd-rincian-nilai is-hijau">Tidak ditagih</b>
                            </li>
                            @endif
                            @foreach ($addonDipilih as $ad)
                            @php $ia = \App\Support\IkonAddon::untuk($ad->nama, (bool) $ad->cek_ai); @endphp
                            <li class="jd-rincian-row" style="--r: {{ $ia['warna'] }}">
                                <span class="jd-rincian-ic"><i class="bi {{ $ia['ikon'] }}"></i></span>
                                <span class="jd-rincian-lbl">
                                    {{ $ad->nama }}
                                    <small>Tambahan</small>
                                </span>
                                <b class="jd-rincian-nilai {{ $ad->harga ? '' : 'is-hijau' }}">{{ $ad->harga ? '+Rp '.number_format($ad->harga, 0, ',', '.') : 'Gratis' }}</b>
                            </li>
                            @endforeach
                        </ul>
                        <div class="jd-rincian-total">
                            <span>
                                Total bayar
                                <small>{{ $addonDipilih->count() ? 'Sudah termasuk tambahan' : 'Belum ada tambahan' }}</small>
                            </span>
                            <b>Rp {{ number_format($this->hargaPerHalamanTotal + $this->addonsTotal, 0, ',', '.') }}</b>
                        </div>
                    </div>
                    @endif

                    @php $dijeda = \App\Support\JedaLayanan::produkDijeda($product); @endphp

                    @if ($dijeda)
                    {{-- Layanan dijeda: halaman tetap utuh supaya calon pembeli tahu
                         layanan ini ada, hanya pintu belinya yang ditutup. --}}
                    @php
                        $waJeda = 'https://wa.me/6289505967995?text='.rawurlencode(
                            'Halo Phoenix Digital, saya ingin bertanya tentang '.trim((string) $product->nama_akun).' yang sedang ditutup sementara.'
                        );
                    @endphp
                    <div class="pd-jeda" role="status">
                        <div class="pd-jeda-isi">
                            <span class="pd-jeda-ic"><i class="bi bi-pause-fill"></i></span>
                            <div class="pd-jeda-txt">
                                <span class="pd-jeda-lencana"><span class="pd-jeda-titik"></span> Ditutup sementara</span>
                                <b class="pd-jeda-judul">Sedang tidak menerima pesanan baru</b>
                                <span class="pd-jeda-pesan">{{ \App\Support\JedaLayanan::pesanProduk($product) }}</span>
                            </div>
                        </div>
                        <div class="pd-jeda-aksi">
                            <a class="pd-jeda-btn is-wa" href="{{ $waJeda }}" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp"></i> Tanya via WhatsApp
                            </a>
                            <a class="pd-jeda-btn is-lain" href="{{ route('shop.index') }}">
                                <i class="bi bi-grid"></i> Lihat layanan lain
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Beli --}}
                    <div class="pd-buy">
                        <button type="button" class="pd-add {{ $dijeda ? 'is-jeda' : '' }}" wire:click="addToCart"
                            wire:loading.attr="disabled" wire:target="addToCart"
                            @disabled($dijeda)>
                            @if ($dijeda)
                            <span><i class="bi bi-lock"></i> Pesanan Ditutup Sementara</span>
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
    {{-- Kotak gambar (≥992px) setinggi kolom kanan SAAT DIMUAT, lalu tingginya
         dibekukan. Tanpa ini, di produk jasa kotak gambar ikut memanjang ratusan
         piksel begitu file diunggah (kolom kanan bertambah langkah DOCX, pilihan
         bagian, dan ringkasan harga).

         Gaya ditaruh di <head>, bukan atribut style: morph Livewire akan
         menghapus atribut yang tidak ada di HTML server. Diukur ulang hanya saat
         lebar layar berubah DAN belum ada file terunggah (.jd-file). Pemilihnya
         dibatasi [data-pd-beku] supaya tidak terbawa ke halaman lain lewat
         wire:navigate. --}}
    <script>
        (function () {
            var gaya = document.getElementById('pd-beku-media');
            if (!gaya) {
                gaya = document.createElement('style');
                gaya.id = 'pd-beku-media';
                document.head.appendChild(gaya);
            }
            gaya.textContent = '';
            var lebarTerukur = 0;

            function ukur(paksa) {
                var media = document.querySelector('[data-pd-beku] .pd-col-media .pd-media');
                if (!media) return;
                if (window.innerWidth < 992) { gaya.textContent = ''; lebarTerukur = 0; return; }
                if (!paksa && window.innerWidth === lebarTerukur) return;
                if (lebarTerukur && document.querySelector('[data-pd-beku] .jd-file')) return;

                gaya.textContent = '';
                var tinggi = Math.round(media.getBoundingClientRect().height);
                lebarTerukur = window.innerWidth;
                var s = '[data-pd-beku] ';
                gaya.textContent = [
                    '@media (min-width: 992px) {',
                    s + '.pd-row { grid-template-rows: auto auto 1fr; }',
                    s + '.pd-row > .pd-col-media, ' + s + '.pd-row > .pd-col-trust { align-self: start; }',
                    s + '.pd-col-media .pd-media { flex: none; width: 100%; height: ' + tinggi + 'px; }',
                    '}'
                ].join('\n');
            }

            window.__pdBekuUkur = ukur;
            ukur(true);
            if (document.readyState !== 'complete') window.addEventListener('load', function () { ukur(true); }, { once: true });
            if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { ukur(true); });
            if (!window.__pdBekuResize) {
                var jeda;
                window.__pdBekuResize = true;
                window.addEventListener('resize', function () {
                    clearTimeout(jeda);
                    jeda = setTimeout(function () { window.__pdBekuUkur && window.__pdBekuUkur(false); }, 150);
                });
            }
        })();
    </script>

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
