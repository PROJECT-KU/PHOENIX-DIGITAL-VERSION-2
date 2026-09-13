<div class="kdl-page">
    <style>
        /* ===== Pesanan kedaluwarsa =====
           Bahasa visual sama dengan Keranjang, Checkout, dan Pembayaran: kartu
           putih bersudut 18px, ubin ikon berwarna di kepala, warna per bagian
           lewat --c.

           Kelas kdl-*: .cart-section dan .ph-empty* ada di
           public-custom-styles.css yang lewat Vite ke public/build, dan folder
           itu masuk .gitignore — beku di server sampai ada rsync. Ditulis
           inline supaya tampilan ini ikut `git pull`. Kelas animasi exp-*
           memang sudah inline sejak semula dan dipertahankan apa adanya. */
        .kdl-page { --kdl-ink: #1c1f26; --kdl-muted: #6b7280; --kdl-line: #eceff3; --kdl-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .kdl-sec { padding: 22px 0 64px; }

        /* ===== Jalur langkah =====
           Bentuk yang sama dengan /checkout dan /payment, tapi perhentian
           ketiganya MERAH: di sinilah pesanannya berhenti. Menggambarkannya
           kelabu seperti langkah yang belum dijalani akan menyembunyikan
           satu-satunya hal yang perlu diketahui pembeli. */
        .kdl-jalur { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 18px; }
        .kdl-jalur::before { content: ""; position: absolute; z-index: 0; top: 17px; left: 12.5%; right: 12.5%; border-top: 2px dashed #f8d8bf; }
        .kdl-henti { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 7px; text-align: center; }
        .kdl-henti-bulat {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%; font-size: .92rem;
            background: #fff; border: 2px solid var(--kdl-line); color: #c3cad3;
        }
        .kdl-henti-bulat i.bi, .kdl-henti-bulat i.bi::before { display: block; line-height: 1; }
        .kdl-henti-teks { font-size: .78rem; font-weight: 700; color: #b4bcc6; }
        .kdl-henti.is-lewat .kdl-henti-bulat { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
        .kdl-henti.is-lewat .kdl-henti-teks { color: #16a34a; }
        /* border:0 — cincin border transparan di atas latar gradasi
           meninggalkan jahitan siku di dalam lingkaran. */
        .kdl-henti.is-gagal .kdl-henti-bulat {
            background: linear-gradient(135deg, #f87171, #dc2626); border: 0; color: #fff;
            box-shadow: 0 8px 18px -8px rgba(220, 38, 38, .8);
        }
        .kdl-henti.is-gagal .kdl-henti-teks { color: #b91c1c; }

        /* ===== Kartu ===== */
        .kdl-tata { display: grid; grid-template-columns: minmax(0, 1fr) 400px; gap: 20px; align-items: start; }
        .kdl-kartu { background: #fff; border: 1px solid var(--kdl-line); border-radius: 18px; overflow: hidden; }
        .kdl-kepala {
            display: flex; align-items: center; gap: 10px; padding: 15px 18px; border-bottom: 1px solid var(--kdl-line);
            font-family: var(--kdl-font); font-weight: 800; font-size: 1rem; color: var(--kdl-ink); letter-spacing: -.015em;
        }
        .kdl-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 34px; height: 34px; border-radius: 11px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .kdl-ubin i.bi, .kdl-ubin i.bi::before { display: block; line-height: 1; }
        .kdl-isi { padding: 18px; }

        /* ===== Kabar utama ===== */
        .kdl-utama { text-align: center; }
        .kdl-gambar { max-width: 260px; margin: 0 auto 4px; }
        .kdl-gambar svg { display: block; width: 100%; height: auto; }
        .kdl-lencana {
            display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;
            height: 28px; padding: 0 13px; border-radius: 99px;
            background: #fff5f5; border: 1px solid #fecaca; color: #b91c1c;
            font-size: .74rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        }
        .kdl-lencana i.bi, .kdl-lencana i.bi::before { display: block; line-height: 1; font-size: .8rem; }
        .kdl-judul { font-family: var(--kdl-font); font-weight: 800; font-size: 1.4rem; color: var(--kdl-ink); margin: 0 0 10px; letter-spacing: -.02em; }
        .kdl-ket { font-size: .9rem; color: var(--kdl-muted); line-height: 1.75; margin: 0 auto 18px; max-width: 460px; }
        .kdl-ket b { color: var(--kdl-ink); font-weight: 700; }
        .kdl-nomor {
            display: inline-block; font-family: ui-monospace, 'Courier New', monospace; font-weight: 700;
            color: #c2410c; background: #fff4ec; border: 1px solid #fbd3b4; border-radius: 8px; padding: 2px 10px;
        }

        .kdl-aksi { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
        .kdl-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 46px; padding: 0 20px;
            border: 1.5px solid transparent; border-radius: 13px; text-decoration: none;
            font-family: var(--kdl-font); font-weight: 700; font-size: .88rem; white-space: nowrap;
            transition: filter .16s ease, transform .16s ease, border-color .16s ease, color .16s ease;
        }
        .kdl-btn i.bi, .kdl-btn i.bi::before { display: block; line-height: 1; font-size: .95rem; }
        /* border:0 — gradasi harus mengisi sampai tepi. */
        .kdl-btn-utama { background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff; box-shadow: 0 12px 24px -14px rgba(242, 101, 34, .85); }
        .kdl-btn-utama:hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .kdl-btn-garis { background: #fff; border-color: #e5e0d8; color: var(--kdl-ink); }
        .kdl-btn-garis:hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }

        /* ===== Isi pesanan yang batal =====
           Pembeli yang kembali harus mengingat sendiri apa yang tadi ia pilih.
           Daftarnya ditampilkan supaya ia tidak perlu mengingat. */
        .kdl-item { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; align-items: center; gap: 11px; padding: 11px 0; }
        .kdl-item + .kdl-item { border-top: 1px dashed var(--kdl-line); }
        .kdl-item-ubin {
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            width: 40px; height: 40px; border-radius: 12px; font-size: .92rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .kdl-item-ubin i.bi, .kdl-item-ubin i.bi::before { display: block; line-height: 1; }
        .kdl-item-ubin img { max-width: 76%; max-height: 76%; object-fit: contain; mix-blend-mode: multiply; }
        .kdl-item-nama { font-family: var(--kdl-font); font-weight: 700; font-size: .86rem; color: var(--kdl-ink); line-height: 1.35; }
        .kdl-item-ket { font-size: .76rem; color: var(--kdl-muted); margin-top: 1px; }
        .kdl-item-harga { font-family: var(--kdl-font); font-weight: 700; font-size: .86rem; color: var(--kdl-muted); white-space: nowrap; }

        .kdl-garis { height: 1px; margin: 12px 0; background: repeating-linear-gradient(to right, var(--kdl-line) 0 6px, transparent 6px 12px); }
        .kdl-total { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
        .kdl-total span { font-family: var(--kdl-font); font-weight: 700; font-size: .9rem; color: var(--kdl-ink); }
        /* Totalnya kelabu, bukan bergradasi jingga seperti di halaman lain:
           uang ini tidak jadi berpindah, jadi jangan dirayakan. */
        .kdl-total strong { font-family: var(--kdl-font); font-weight: 800; font-size: 1.2rem; color: #9aa3af; text-decoration: line-through; }

        .kdl-nota { display: flex; align-items: flex-start; gap: 9px; margin-top: 16px; padding: 13px 15px; border-radius: 14px; background: #f8fafc; border: 1px solid #e8edf3; font-size: .8rem; color: var(--kdl-muted); line-height: 1.65; }
        .kdl-nota i.bi { flex-shrink: 0; margin-top: 2px; font-size: .9rem; color: #64748b; }
        .kdl-nota i.bi::before { display: block; line-height: 1; }

        @media (max-width: 991.98px) {
            .kdl-tata { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .kdl-isi { padding: 15px; }
            .kdl-jalur { gap: 4px; }
            .kdl-henti-teks { font-size: .68rem; }
            .kdl-gambar { max-width: 200px; }
            .kdl-judul { font-size: 1.2rem; }
            .kdl-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .kdl-btn, .kdl-btn:hover { transition: none; transform: none; }
        }

        /* ===== Animasi jam pasir (sudah inline sejak semula) ===== */
        .exp-hg { animation: expFloat 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .exp-glow { animation: expGlow 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .exp-shadow { animation: expShadow 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .exp-stream { stroke-dasharray: 2 5; animation: expSand .55s linear infinite; }
        .exp-spark { transform-box: fill-box; transform-origin: center; animation: expTwinkle 2.4s ease-in-out infinite; }
        .exp-spark.s2 { animation-delay: .6s; }
        .exp-spark.s3 { animation-delay: 1.2s; }
        .exp-spark.s4 { animation-delay: 1.8s; }
        @keyframes expFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes expGlow { 0%,100% { opacity: .5; transform: scale(1); } 50% { opacity: .85; transform: scale(1.07); } }
        @keyframes expShadow { 0%,100% { transform: scaleX(1); opacity: .16; } 50% { transform: scaleX(.8); opacity: .09; } }
        @keyframes expSand { to { stroke-dashoffset: -21; } }
        @keyframes expTwinkle { 0%,100% { opacity: .25; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }
        @media (prefers-reduced-motion: reduce) {
            .exp-hg, .exp-glow, .exp-shadow, .exp-stream, .exp-spark { animation: none !important; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-hourglass-bottom"></i> Kedaluwarsa</span>
                <h1>Waktu Pembayaran Habis</h1>
                <p>Pesanan <b>#{{ $order->order_number }}</b> dibatalkan karena melewati batas waktu.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">Kedaluwarsa</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="kdl-sec">
        <div class="container">
            {{-- Perhentian ketiga MERAH: di sinilah pesanannya berhenti. --}}
            <div class="kdl-jalur">
                <div class="kdl-henti is-lewat">
                    <span class="kdl-henti-bulat"><i class="bi bi-check-lg"></i></span>
                    <span class="kdl-henti-teks">Keranjang</span>
                </div>
                <div class="kdl-henti is-lewat">
                    <span class="kdl-henti-bulat"><i class="bi bi-check-lg"></i></span>
                    <span class="kdl-henti-teks">Data &amp; Promo</span>
                </div>
                <div class="kdl-henti is-gagal">
                    <span class="kdl-henti-bulat"><i class="bi bi-x-lg"></i></span>
                    <span class="kdl-henti-teks">Bayar</span>
                </div>
                <div class="kdl-henti">
                    <span class="kdl-henti-bulat"><i class="bi bi-inbox-fill"></i></span>
                    <span class="kdl-henti-teks">Terima</span>
                </div>
            </div>

            <div class="kdl-tata">
                {{-- Kabar utama --}}
                <div class="kdl-kartu" style="--c: #d97706">
                    <div class="kdl-kepala">
                        <span class="kdl-ubin"><i class="bi bi-hourglass-bottom"></i></span> Pesanan Dibatalkan
                    </div>
                    <div class="kdl-isi kdl-utama">
                        <div class="kdl-gambar">
                            <svg viewBox="0 0 240 240" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Jam pasir kedaluwarsa">
                                <defs>
                                    <linearGradient id="ehgO" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0" stop-color="#fbc25a" />
                                        <stop offset="1" stop-color="#f26522" />
                                    </linearGradient>
                                    <linearGradient id="ehgV" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0" stop-color="#fba919" />
                                        <stop offset="1" stop-color="#f26522" />
                                    </linearGradient>
                                    <radialGradient id="ehgGlow" cx="50%" cy="50%" r="50%">
                                        <stop offset="0" stop-color="#fba919" stop-opacity=".55" />
                                        <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                    </radialGradient>
                                </defs>

                                <circle class="exp-glow" cx="120" cy="118" r="82" fill="url(#ehgGlow)" />
                                <ellipse class="exp-shadow" cx="120" cy="214" rx="56" ry="11" fill="#e15a18" />

                                <g transform="translate(46,72)"><path class="exp-spark s1" d="M0,-8 L2,-2 8,0 2,2 0,8 -2,2 -8,0 -2,-2Z" fill="#fba919" /></g>
                                <g transform="translate(198,92)"><path class="exp-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                                <circle class="exp-spark s3" cx="196" cy="158" r="5" fill="#fbaf45" />
                                <circle class="exp-spark s4" cx="48" cy="156" r="4" fill="#f4772b" />

                                <g class="exp-hg">
                                    <rect x="66" y="44" width="108" height="12" rx="6" fill="url(#ehgV)" />
                                    <rect x="66" y="184" width="108" height="12" rx="6" fill="url(#ehgV)" />
                                    <rect x="70" y="53" width="6" height="132" rx="3" fill="url(#ehgV)" opacity=".45" />
                                    <rect x="164" y="53" width="6" height="132" rx="3" fill="url(#ehgV)" opacity=".45" />
                                    <path d="M80 56 L160 56 L120 120 Z" fill="rgba(251,169,25,.13)" stroke="url(#ehgO)" stroke-width="4" stroke-linejoin="round" />
                                    <path d="M80 184 L160 184 L120 120 Z" fill="rgba(251,169,25,.13)" stroke="url(#ehgO)" stroke-width="4" stroke-linejoin="round" />
                                    <path d="M101 104 L139 104 L120 120 Z" fill="url(#ehgV)" />
                                    <line class="exp-stream" x1="120" y1="120" x2="120" y2="150" stroke="url(#ehgV)" stroke-width="3.5" stroke-linecap="round" />
                                    <path d="M84 184 L156 184 L120 150 Z" fill="url(#ehgV)" />
                                    <path d="M92 64 L108 64" stroke="#ffffff" stroke-opacity=".5" stroke-width="3" stroke-linecap="round" />
                                </g>
                            </svg>
                        </div>

                        <span class="kdl-lencana"><i class="bi bi-x-octagon-fill"></i> Dibatalkan</span>
                        <h3 class="kdl-judul">Waktu Pembayaran Habis</h3>
                        <p class="kdl-ket">
                            Pesanan <span class="kdl-nomor">{{ $order->order_number }}</span> telah <b>dibatalkan</b>
                            karena melewati batas waktu pembayaran. Tidak ada uang yang terpotong.
                            Tidak masalah — Anda bisa memesan kembali kapan saja.
                        </p>

                        <div class="kdl-aksi">
                            <a href="{{ route('shop.index') }}" class="kdl-btn kdl-btn-utama"><i class="bi bi-bag"></i> Belanja Lagi</a>
                            <a href="{{ route('order.history') }}" class="kdl-btn kdl-btn-garis"><i class="bi bi-clock-history"></i> Lihat Riwayat</a>
                            <a href="{{ route('homepage') }}" class="kdl-btn kdl-btn-garis"><i class="bi bi-house"></i> Ke Beranda</a>
                        </div>

                        <div class="kdl-nota">
                            <i class="bi bi-info-circle"></i>
                            <span>Pesanan dibatalkan otomatis bila belum dibayar sampai batas waktu, supaya stok akun tidak tertahan. Anda tidak perlu melakukan apa pun.</span>
                        </div>
                    </div>
                </div>

                {{-- Isi pesanan yang batal — supaya pembeli tidak perlu mengingat
                     sendiri apa yang tadi ia pilih saat hendak memesan ulang. --}}
                @if ($order->items->isNotEmpty())
                    <div class="kdl-kartu" style="--c: #2563eb">
                        <div class="kdl-kepala">
                            <span class="kdl-ubin"><i class="bi bi-receipt"></i></span> Isi Pesanan Ini
                        </div>
                        <div class="kdl-isi">
                            @foreach ($order->items as $item)
                                @php
                                    // Warna & ikon dari KATEGORI produk — taksonomi yang sama
                                    // dengan Keranjang, Checkout, dan Pembayaran.
                                    $katKdl = \App\Support\KategoriBeranda::untukProduk($item->product_name ?? '');
                                    $warnaKdl = $katKdl['warna'] ?? '#f26522';
                                    $ikonKdl = $katKdl['ikon'] ?? 'bi-box-seam';

                                    // Logo dipakai HANYA bila berkasnya ada; memasangnya tanpa
                                    // syarat membuat teks alt tampil sebagai gambar rusak.
                                    $berkasKdl = $item->product_image ? basename($item->product_image) : null;
                                    $logoKdl = null;
                                    if ($berkasKdl) {
                                        foreach (['Product', 'ProductBundlings'] as $folderKdl) {
                                            if (is_file(public_path('storage/img/'.$folderKdl.'/'.$berkasKdl))) {
                                                $logoKdl = asset('storage/img/'.$folderKdl.'/'.$berkasKdl);
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <div class="kdl-item" style="--c: {{ $warnaKdl }}">
                                    <span class="kdl-item-ubin">
                                        @if ($logoKdl)
                                            <img src="{{ $logoKdl }}" alt="{{ $item->product_name }}" loading="lazy"
                                                onerror="this.remove();">
                                        @else
                                            <i class="bi {{ $ikonKdl }}"></i>
                                        @endif
                                    </span>
                                    <div>
                                        <div class="kdl-item-nama">{{ $item->product_name }}</div>
                                        <div class="kdl-item-ket">{{ $item->getDurationLabel() }} &times;{{ $item->quantity }}</div>
                                    </div>
                                    <span class="kdl-item-harga">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach

                            <div class="kdl-garis"></div>

                            <div class="kdl-total">
                                <span>Total yang batal</span>
                                <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
