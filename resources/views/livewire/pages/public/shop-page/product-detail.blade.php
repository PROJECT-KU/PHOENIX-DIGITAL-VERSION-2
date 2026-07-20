<main class="main">
    @include('partials.media-produk-style')
    <style>
        /* ===== Daftar fitur produk (pecahan dari deskripsi ber-"✅") =====
           Sengaja inline di blade, bukan di public-custom-styles.css: berkas di
           public/build/ tidak ikut git pull sehingga gaya bisa tertinggal di
           server. Lihat catatan aset di README. */
        .pd-feat { list-style:none; margin:0 0 22px; padding:0;
            display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:9px 18px; }
        .pd-feat li { display:flex; align-items:flex-start; gap:9px;
            font-size:.92rem; line-height:1.55; color:var(--ph-ink); }
        .pd-feat li i { color:#16a34a; font-size:1rem; line-height:1.45; flex:0 0 auto; }
        .pd-feat li span { min-width:0; }
        @media (max-width: 767.98px) { .pd-feat { grid-template-columns:1fr; gap:8px; } }

        /* ===== Denyut pada ikon centang =====
           Berdenyut terus-menerus supaya daftar terasa hidup. Tidak ada animasi
           masuk: teksnya langsung terbaca begitu halaman tampil.

           Jeda tiap ikon dihitung dari --i yang ditulis blade, jadi denyutnya
           bergelombang (tidak serempak) dan otomatis menyesuaikan berapa pun
           jumlah poinnya. */
        .pd-feat li i {
            transform-origin: center;
            animation: pdCheckPulse 2.4s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * 200ms);
            will-change: transform;
        }
        @keyframes pdCheckPulse {
            0%, 100% { transform:scale(1);    filter:drop-shadow(0 0 0 rgba(22, 163, 74, 0)); }
            50%      { transform:scale(1.16); filter:drop-shadow(0 0 5px rgba(22, 163, 74, .45)); }
        }

        /* Hormati pengguna yang meminta animasi dikurangi: diamkan denyutnya. */
        @media (prefers-reduced-motion: reduce) {
            .pd-feat li i { animation:none; transform:none; filter:none; }
        }

        /* ===== Kartu deskripsi ===== */
        .pd-desc-card { border:1px solid var(--ph-line); border-radius:18px; padding:20px 22px;
            background:linear-gradient(180deg, #fffdfa 0%, #fff 60%); }
        .pd-desc-head { display:flex; align-items:center; gap:9px;
            font-family:'Poppins', sans-serif; font-weight:800; font-size:1rem;
            color:var(--ph-ink); margin:0 0 12px; }
        .pd-desc-head i { color:var(--ph-orange); font-size:1.05rem; }
        .pd-desc-card .pd-desc { margin-bottom:12px; }
        .pd-desc-card .pd-desc.is-lead { color:var(--ph-ink); font-weight:600; }
        .pd-desc-card .pd-desc:last-child { margin-bottom:0; }
        .pd-desc-card .pd-feat { margin-bottom:0; padding-top:4px; }
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
    </style>
    @php
        $best = $this->bestDiscount;
        $isFlash = $best && ($best['promo']->tipe_promo ?? null) === 'flash_sale';
        $selOrig = $this->selectedHarga();
        $selDisc = $this->applyDiscount($selOrig);
        $selSave = max(0, $selOrig - $selDisc);
    @endphp

    <!-- Page Title -->
    <div class="page-title ph-page-title">
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

    <section class="pd-section">
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
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-shield-check"></i></span>
                            <span class="pd-feature-txt">
                                <b>Bergaransi</b>
                                <small>Selama masa aktif paket</small>
                            </span>
                        </div>
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-whatsapp"></i></span>
                            <span class="pd-feature-txt">
                                <b>Dukungan Cepat</b>
                                <small>Bantuan &amp; respons via WhatsApp</small>
                            </span>
                        </div>
                        <div class="pd-feature">
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
                @php $desk = \App\Support\DeskripsiProduk::pisah($product->deskripsi); @endphp
                @if ($desk['paragraf'] || $desk['poin'])
                    <div class="col-lg-6 pd-col-desc">
                        <div class="pd-desc-card">
                            <h3 class="pd-desc-head"><i class="bi bi-card-text"></i> Deskripsi Produk</h3>

                            {{-- Teks biasa: TANPA centang. --}}
                            @foreach ($desk['paragraf'] as $i => $par)
                                <p class="pd-desc {{ $i === 0 ? 'is-lead' : '' }}">{{ $par }}</p>
                            @endforeach

                            {{-- Hanya bagian yang ditandai admin (✅ dsb) yang bercentang. --}}
                            @if ($desk['poin'])
                                {{-- --i dipakai CSS untuk menjeda animasi tiap poin
                                     secara bertingkat, berapa pun jumlah poinnya. --}}
                                <ul class="pd-feat">
                                    @foreach ($desk['poin'] as $poin)
                                        <li style="--i: {{ $loop->index }}">
                                            <i class="bi bi-check-circle-fill"></i><span>{{ $poin }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Info --}}
                <div class="col-lg-6 pd-col-info">
                    <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Akun Premium</span>
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
                            @foreach ($product->daftarHarga() as $pkg)
                                @php
                                    $isActive = ($durationType === $pkg['durasi_type'] && (int) $durationValue === (int) $pkg['durasi_value']);
                                    $pOrig = (int) $pkg['harga'];
                                    $pDisc = $this->applyDiscount($pOrig);
                                @endphp
                                <button type="button"
                                    class="pd-pkg {{ $isActive ? 'is-active' : '' }}"
                                    wire:click="selectPackage('{{ $pkg['durasi_type'] }}', {{ $pkg['durasi_value'] }})">
                                    <span class="pd-pkg-dur">{{ $pkg['durasi_value'] }} {{ ucfirst($pkg['durasi_type']) }}</span>
                                    <span class="pd-pkg-price">
                                        @if ($pDisc < $pOrig)
                                            <span class="pd-pkg-old">Rp {{ number_format($pOrig, 0, ',', '.') }}</span>
                                        @endif
                                        <span class="pd-pkg-now">Rp {{ number_format($pDisc, 0, ',', '.') }}</span>
                                    </span>
                                    @if ($pOrig - $pDisc > 0)
                                        <span class="pd-pkg-save">Hemat Rp {{ number_format($pOrig - $pDisc, 0, ',', '.') }}</span>
                                    @endif
                                    <i class="bi bi-check-circle-fill pd-pkg-check"></i>
                                </button>
                            @endforeach

                            {{-- Durasi custom (seperti flash sale) --}}
                            @if ((int) ($product->harga_perbulan ?? 0) > 0)
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

                    {{-- Beli --}}
                    <div class="pd-buy">
                        <button type="button" class="pd-add" wire:click="addToCart"
                            wire:loading.attr="disabled" wire:target="addToCart">
                            <span wire:loading.remove wire:target="addToCart"><i class="bi bi-cart-plus"></i> Tambah ke Keranjang</span>
                            <span wire:loading wire:target="addToCart"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
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
    <section class="rev-section">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto;">
                @livewire(\App\Livewire\Components\ProductReviews::class, ['productId' => $product->id], key('rev-'.$product->id))
            </div>
        </div>
    </section>

    {{-- Produk terkait / rekomendasi --}}
    @if (count($this->relatedProducts))
        <section class="rel-section">
            <div class="container">
                <div class="ph-sec-head" style="text-align:center;">
                    <span class="ph-sec-eyebrow"><i class="bi bi-grid-3x3-gap"></i> Produk Lainnya</span>
                    <h2 class="ph-sec-title">Mungkin Anda juga suka</h2>
                </div>
                <div class="rel-grid rel-scroll">
                    @foreach ($this->relatedProducts as $rp)
                        <a href="{{ route('shop.detail-product', $rp->id) }}" class="rel-card">
                            <div class="rel-thumb">
                                @if ($rp->image)
                                    <img src="{{ asset('storage/img/Product/'.basename($rp->image)) }}" alt="{{ $rp->nama_akun }}" loading="lazy">
                                @else
                                    <span class="rel-noimg"><i class="bi bi-box-seam"></i></span>
                                @endif
                            </div>
                            <div class="rel-body">
                                <h3 class="rel-name">{{ $rp->nama_akun }}</h3>
                                @if ($rp->harga_perbulan)
                                    <div class="rel-price"><small>Mulai</small> Rp {{ number_format($rp->harga_perbulan, 0, ',', '.') }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
