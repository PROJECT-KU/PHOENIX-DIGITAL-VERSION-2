<div class="sks-page">
    <style>
        /* ===== Pesanan berhasil =====
           Penutup rangkaian Keranjang → Checkout → Bayar → Terima, dan memakai
           bahasa visual yang sama: kartu putih bersudut 18px, ubin ikon
           berwarna di kepala, warna per bagian lewat --c.

           Kelas sks-*: aturan .pay-*, .cart-*, dan .ph-empty* ada di
           public-custom-styles.css yang lewat Vite ke public/build, dan folder
           itu masuk .gitignore — beku di server sampai ada rsync. Ditulis
           inline supaya tampilan ini ikut `git pull`.

           Yang dipertahankan apa adanya: seluruh animasi su-* beserta
           ilustrasi centangnya, kartu poin/member su-pm-*, dan id su-cek-link
           yang dibaca suSalinCek(). */
        .sks-page { --sks-ink: #1c1f26; --sks-muted: #6b7280; --sks-line: #eceff3; --sks-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .sks-sec { padding: 22px 0 64px; }

        /* ===== Jalur langkah — perhentian terakhir ===== */
        .sks-jalur { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 18px; }
        .sks-jalur::before { content: ""; position: absolute; z-index: 0; top: 17px; left: 12.5%; right: 12.5%; border-top: 2px dashed #f8d8bf; }
        .sks-henti { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 7px; text-align: center; }
        .sks-henti-bulat {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%; font-size: .92rem;
            background: #fff; border: 2px solid var(--sks-line); color: #c3cad3;
        }
        .sks-henti-bulat i.bi, .sks-henti-bulat i.bi::before { display: block; line-height: 1; }
        .sks-henti-teks { font-size: .78rem; font-weight: 700; color: #b4bcc6; }
        .sks-henti.is-lewat .sks-henti-bulat { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
        .sks-henti.is-lewat .sks-henti-teks { color: #16a34a; }
        /* border:0 — cincin border transparan di atas latar gradasi
           meninggalkan jahitan siku di dalam lingkaran. */
        .sks-henti.is-kini .sks-henti-bulat {
            background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff;
            box-shadow: 0 8px 18px -8px rgba(242, 101, 34, .85);
        }
        .sks-henti.is-kini .sks-henti-teks { color: var(--sks-ink); }
        .sks-henti.is-tuntas .sks-henti-bulat {
            background: linear-gradient(135deg, #4ade80, #16a34a); border: 0; color: #fff;
            box-shadow: 0 8px 18px -8px rgba(22, 163, 74, .85);
        }
        .sks-henti.is-tuntas .sks-henti-teks { color: #15803d; }

        /* ===== Tata letak & kartu ===== */
        .sks-tata { display: grid; grid-template-columns: minmax(0, 1fr) 400px; gap: 20px; align-items: start; }
        .sks-kolom { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
        .sks-kartu { background: #fff; border: 1px solid var(--sks-line); border-radius: 18px; overflow: hidden; }
        .sks-kepala {
            display: flex; align-items: center; gap: 10px; padding: 15px 18px; border-bottom: 1px solid var(--sks-line);
            font-family: var(--sks-font); font-weight: 800; font-size: 1rem; color: var(--sks-ink); letter-spacing: -.015em;
        }
        .sks-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 34px; height: 34px; border-radius: 11px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .sks-ubin i.bi, .sks-ubin i.bi::before { display: block; line-height: 1; }
        .sks-isi { padding: 18px; }

        /* ===== Perayaan ===== */
        .sks-rayakan { text-align: center; }
        .sks-gambar { max-width: 210px; margin: 0 auto 2px; }
        .sks-gambar svg { display: block; width: 100%; height: auto; }
        .sks-lencana {
            display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;
            height: 28px; padding: 0 13px; border-radius: 99px;
            background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d;
            font-size: .74rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        }
        .sks-lencana i.bi, .sks-lencana i.bi::before { display: block; line-height: 1; font-size: .8rem; }
        .sks-judul { font-family: var(--sks-font); font-weight: 800; font-size: 1.45rem; color: var(--sks-ink); margin: 0 0 10px; letter-spacing: -.02em; }
        .sks-ket { font-size: .9rem; color: var(--sks-muted); line-height: 1.75; margin: 0 auto; max-width: 440px; }
        .sks-nomor {
            display: inline-block; font-family: ui-monospace, 'Courier New', monospace; font-weight: 700;
            color: #c2410c; background: #fff4ec; border: 1px solid #fbd3b4; border-radius: 8px; padding: 2px 10px;
        }

        /* ===== Pengiriman akun ===== */
        .sks-kirim {
            display: flex; align-items: flex-start; gap: 11px; padding: 14px 16px; border-radius: 16px;
            background: linear-gradient(135deg, #f0fdfa, #fff); border: 1px solid #99f6e4;
        }
        .sks-kirim i.bi { flex-shrink: 0; margin-top: 2px; font-size: 1.2rem; color: #0d9488; }
        .sks-kirim i.bi::before { display: block; line-height: 1; }
        .sks-kirim span { display: block; font-size: .76rem; color: #0f766e; font-weight: 600; }
        .sks-kirim b { font-family: var(--sks-font); font-weight: 800; font-size: .9rem; color: #134e4a; word-break: break-word; }
        .sks-kirim small { display: block; margin-top: 4px; font-size: .8rem; color: var(--sks-muted); line-height: 1.55; }

        /* ===== Blok jasa ===== */
        .sks-jasa-teks { font-size: .87rem; color: var(--sks-muted); line-height: 1.7; margin: 0 0 12px; }
        .sks-jasa-teks b { color: var(--sks-ink); font-weight: 700; }
        .sks-tautan {
            display: flex; align-items: center; gap: 8px; margin-bottom: 12px; padding: 9px 10px;
            border-radius: 11px; background: #f8fafc; border: 1px dashed var(--sks-line);
        }
        .sks-tautan code { flex: 1; min-width: 0; font-size: .78rem; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sks-salin {
            display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
            border: 0; border-radius: 9px; padding: 7px 13px; cursor: pointer;
            background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            font-family: var(--sks-font); font-weight: 700; font-size: .78rem; white-space: nowrap;
            transition: filter .16s ease;
        }
        .sks-salin:hover { filter: brightness(1.05); }
        .sks-salin i.bi, .sks-salin i.bi::before { display: block; line-height: 1; }

        /* ===== Daftar item ===== */
        .sks-item { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; align-items: center; gap: 11px; padding: 11px 0; }
        .sks-item + .sks-item { border-top: 1px dashed var(--sks-line); }
        /* Logo produk bila berkasnya ada, ikon kategori bila tidak — sama
           dengan Keranjang, Checkout, Pembayaran, dan halaman kedaluwarsa. */
        .sks-item-ubin {
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            width: 40px; height: 40px; border-radius: 12px; font-size: .92rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .sks-item-ubin i.bi, .sks-item-ubin i.bi::before { display: block; line-height: 1; }
        .sks-item-ubin img { max-width: 76%; max-height: 76%; object-fit: contain; mix-blend-mode: multiply; }
        .sks-item-nama { font-family: var(--sks-font); font-weight: 700; font-size: .86rem; color: var(--sks-ink); line-height: 1.35; }
        .sks-item-ket { font-size: .76rem; color: var(--sks-muted); margin-top: 1px; }
        .sks-item-harga { font-family: var(--sks-font); font-weight: 700; font-size: .86rem; color: var(--sks-ink); white-space: nowrap; }

        .sks-garis { height: 1px; margin: 12px 0; background: repeating-linear-gradient(to right, var(--sks-line) 0 6px, transparent 6px 12px); }
        .sks-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: .86rem; color: var(--sks-muted); }
        .sks-row strong { font-family: var(--sks-font); font-weight: 700; color: var(--sks-ink); }
        .sks-row.is-potong strong { color: #16a34a; }
        .sks-total { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 12px 0 2px; }
        .sks-total span { font-family: var(--sks-font); font-weight: 700; font-size: .95rem; color: var(--sks-ink); }
        /* Total di sini HIJAU, bukan jingga: uangnya sudah benar-benar masuk —
           ini kabar selesai, bukan ajakan membayar seperti di halaman lain. */
        .sks-total strong {
            font-family: var(--sks-font); font-weight: 800; font-size: 1.45rem; letter-spacing: -.02em;
            background: linear-gradient(135deg, #4ade80, #16a34a); -webkit-background-clip: text; background-clip: text; color: transparent;
        }

        /* ===== Tombol ===== */
        .sks-aksi { display: flex; flex-wrap: wrap; gap: 10px; }
        .sks-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; flex: 1 1 160px;
            height: 46px; padding: 0 18px; border: 1.5px solid transparent; border-radius: 13px; text-decoration: none;
            font-family: var(--sks-font); font-weight: 700; font-size: .88rem; white-space: nowrap;
            transition: filter .16s ease, transform .16s ease, border-color .16s ease, color .16s ease;
        }
        .sks-btn i.bi, .sks-btn i.bi::before { display: block; line-height: 1; font-size: .95rem; }
        /* border:0 — gradasi harus mengisi sampai tepi. */
        .sks-btn-utama { background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff; box-shadow: 0 12px 24px -14px rgba(242, 101, 34, .85); }
        .sks-btn-utama:hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .sks-btn-garis { background: #fff; border-color: #e5e0d8; color: var(--sks-ink); }
        .sks-btn-garis:hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }

        .sks-nota { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 4px 0 0; font-size: .78rem; color: var(--sks-muted); }
        .sks-nota i.bi { font-size: .85rem; color: #64748b; }
        .sks-nota i.bi::before { display: block; line-height: 1; }

        @media (max-width: 991.98px) {
            .sks-tata { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .sks-isi { padding: 15px; }
            .sks-jalur { gap: 4px; }
            .sks-henti-teks { font-size: .68rem; }
            .sks-gambar { max-width: 170px; }
            .sks-judul { font-size: 1.22rem; }
            .sks-btn { flex: 1 1 100%; }
            .sks-total strong { font-size: 1.3rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .sks-btn, .sks-btn:hover, .sks-salin { transition: none; transform: none; }
        }

        /* ===== Animasi centang (sudah inline sejak semula) ===== */
        .su-ring { transform-box: fill-box; transform-origin: center; animation: suRing 2.4s ease-out infinite; }
        .su-ring.r2 { animation-delay: 1.2s; }
        .su-check { stroke-dasharray: 90; stroke-dashoffset: 90; animation: suCheck .55s ease forwards .25s; }
        .su-pop { transform-box: fill-box; transform-origin: center; animation: suPop .5s cubic-bezier(.2,1.4,.4,1) forwards; }
        .su-spark { transform-box: fill-box; transform-origin: center; animation: suTwinkle 2s ease-in-out infinite; }
        .su-spark.s2 { animation-delay: .5s; }
        .su-spark.s3 { animation-delay: 1s; }
        .su-spark.s4 { animation-delay: 1.5s; }
        @keyframes suRing { 0% { transform: scale(.7); opacity: .55; } 100% { transform: scale(1.7); opacity: 0; } }
        @keyframes suCheck { to { stroke-dashoffset: 0; } }
        @keyframes suPop { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        @keyframes suTwinkle { 0%,100% { opacity: .25; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }
        @media (prefers-reduced-motion: reduce) {
            .su-ring, .su-check, .su-pop, .su-spark { animation: none !important; stroke-dashoffset: 0 !important; }
        }

        /* ===== Kartu poin / ajakan member (tidak diubah) ===== */
        .su-pm { --su-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; background: #fff; border: 1px solid #eceff3; border-radius: 18px; overflow: hidden; text-align: left; }
        .su-pm-kepala {
            display: flex; align-items: center; gap: 10px; padding: 15px 18px; border-bottom: 1px solid #eceff3;
            font-family: var(--su-font); font-weight: 800; font-size: 1rem; color: #1c1f26; letter-spacing: -.015em;
        }
        .su-pm-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 34px; height: 34px; border-radius: 11px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .su-pm-ubin i.bi, .su-pm-ubin i.bi::before { display: block; line-height: 1; }
        .su-pm-isi { padding: 18px; }
        .su-pm-angka { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .su-pm-poin { font-family: var(--su-font); font-weight: 800; font-size: 1.9rem; line-height: 1; color: color-mix(in srgb, var(--c) 80%, #0f172a); letter-spacing: -.02em; }
        .su-pm-poin small { font-size: .95rem; font-weight: 700; margin-left: 4px; }
        .su-pm-nilai { font-size: .86rem; color: #6b7280; }
        .su-pm-nilai b { font-family: var(--su-font); font-weight: 800; color: #1c1f26; }
        .su-pm-batang { height: 8px; margin: 14px 0 8px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
        .su-pm-batang span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, color-mix(in srgb, var(--c) 55%, #fff), var(--c)); }
        .su-pm-ket { font-size: .82rem; color: #6b7280; line-height: 1.6; margin: 0; }
        .su-pm-ket b { color: #1c1f26; font-weight: 700; }
        .su-pm-teks { margin: 0 0 14px; font-size: .88rem; color: #4b5563; line-height: 1.7; }
        .su-pm-teks b { color: #1c1f26; font-weight: 700; }
        .su-pm-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 44px;
            border-radius: 12px; text-decoration: none;
            background: color-mix(in srgb, var(--c) 10%, #fff);
            border: 1.5px solid color-mix(in srgb, var(--c) 28%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a);
            font-family: var(--su-font); font-weight: 700; font-size: .87rem;
            transition: background .16s ease, transform .16s ease;
        }
        .su-pm-btn:hover { background: color-mix(in srgb, var(--c) 16%, #fff); color: color-mix(in srgb, var(--c) 88%, #0f172a); transform: translateY(-1px); }
        .su-pm-btn i.bi, .su-pm-btn i.bi::before { display: block; line-height: 1; }
        @media (prefers-reduced-motion: reduce) { .su-pm-btn, .su-pm-btn:hover { transition: none; transform: none; } }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-patch-check-fill"></i> Berhasil</span>
                <h1>Pembayaran Berhasil</h1>
                <p>Pesanan <b>#{{ $order->order_number }}</b> sudah kami terima dan sedang diproses.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">Berhasil</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="sks-sec">
        <div class="container">
            {{-- Perhentian terakhir. Selama pesanannya belum 'completed', akun
                 memang belum di tangan pembeli — jadi "Terima" masih berjalan,
                 bukan sudah beres. Menandainya hijau lebih cepat dari kenyataan
                 membuat pembeli mengira akunnya sudah dikirim. --}}
            @php $sudahTuntas = $order->status === 'completed'; @endphp
            <div class="sks-jalur">
                <div class="sks-henti is-lewat">
                    <span class="sks-henti-bulat"><i class="bi bi-check-lg"></i></span>
                    <span class="sks-henti-teks">Keranjang</span>
                </div>
                <div class="sks-henti is-lewat">
                    <span class="sks-henti-bulat"><i class="bi bi-check-lg"></i></span>
                    <span class="sks-henti-teks">Data &amp; Promo</span>
                </div>
                <div class="sks-henti is-lewat">
                    <span class="sks-henti-bulat"><i class="bi bi-check-lg"></i></span>
                    <span class="sks-henti-teks">Bayar</span>
                </div>
                <div class="sks-henti {{ $sudahTuntas ? 'is-tuntas' : 'is-kini' }}">
                    <span class="sks-henti-bulat"><i class="bi {{ $sudahTuntas ? 'bi-check-lg' : 'bi-inbox-fill' }}"></i></span>
                    <span class="sks-henti-teks">Terima</span>
                </div>
            </div>

            <div class="sks-tata">
                {{-- Kolom kiri --}}
                <div class="sks-kolom">
                    <div class="sks-kartu" style="--c: #16a34a">
                        <div class="sks-kepala">
                            <span class="sks-ubin"><i class="bi bi-patch-check-fill"></i></span> Pembayaran Diterima
                        </div>
                        <div class="sks-isi sks-rayakan">
                            <div class="sks-gambar">
                                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Pembayaran berhasil">
                                    <defs>
                                        <linearGradient id="suG" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#fbc25a" />
                                            <stop offset="1" stop-color="#f26522" />
                                        </linearGradient>
                                    </defs>
                                    <circle class="su-ring" cx="100" cy="100" r="46" fill="none" stroke="url(#suG)" stroke-width="3" />
                                    <circle class="su-ring r2" cx="100" cy="100" r="46" fill="none" stroke="url(#suG)" stroke-width="3" />

                                    <g transform="translate(40,52)"><path class="su-spark s1" d="M0,-8 L2,-2 8,0 2,2 0,8 -2,2 -8,0 -2,-2Z" fill="#fba919" /></g>
                                    <g transform="translate(162,60)"><path class="su-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                                    <circle class="su-spark s3" cx="164" cy="146" r="4.5" fill="#fbaf45" />
                                    <circle class="su-spark s4" cx="40" cy="150" r="4" fill="#f4772b" />

                                    <g class="su-pop">
                                        <circle cx="100" cy="100" r="46" fill="url(#suG)" />
                                        <path class="su-check" d="M78 101 L94 117 L124 83" fill="none" stroke="#fff" stroke-width="9" stroke-linecap="round" stroke-linejoin="round" />
                                    </g>
                                </svg>
                            </div>

                            <span class="sks-lencana"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                            <h3 class="sks-judul">Pembayaran Berhasil! 🎉</h3>
                            <p class="sks-ket">
                                Terima kasih! Pesanan <span class="sks-nomor">{{ $order->order_number }}</span>
                                telah kami terima dan sedang diproses.
                            </p>
                        </div>
                    </div>

                    {{-- Info pengiriman akun — hanya bila ada item AKUN (produk jasa tak dikirim akun) --}}
                    @if ($order->items->contains(fn ($i) => ! optional($i->product)->butuh_file))
                        <div class="sks-kirim">
                            <i class="bi bi-envelope-check"></i>
                            <div>
                                <span>Pengiriman Akun</span>
                                <b>Detail akun dikirim ke {{ $order->customer->email }}</b>
                                <small>via Email / WhatsApp, maksimal 1×24 jam pada jam operasional.</small>
                            </div>
                        </div>
                    @endif

                    {{-- ===== Pesanan JASA: arahkan ke halaman pengecekan (link permanen) ===== --}}
                    @if ($order->butuhUpload())
                        <div class="sks-kartu" style="--c: #d97706">
                            <div class="sks-kepala">
                                <span class="sks-ubin"><i class="bi bi-shield-check"></i></span> Halaman Cek Plagiasi
                            </div>
                            <div class="sks-isi">
                                <p class="sks-jasa-teks">
                                    Pesanan ini termasuk <b>jasa cek plagiasi</b>. Unggah file &amp; unduh hasil lewat halaman
                                    khusus di bawah. <b>Simpan linknya</b> agar bisa dibuka kapan saja — tanpa perlu login.
                                </p>

                                {{-- id su-cek-link WAJIB tetap: dibaca suSalinCek(). --}}
                                <div class="sks-tautan">
                                    <code id="su-cek-link">{{ url('/cek/'.$order->share_token) }}</code>
                                    <button type="button" class="sks-salin" onclick="suSalinCek()"><i class="bi bi-clipboard"></i> Salin</button>
                                </div>

                                <a href="{{ route('jasa.cek', $order->share_token) }}" class="sks-btn sks-btn-utama" style="width: 100%;">
                                    <i class="bi bi-box-arrow-up-right"></i> Buka Halaman Pengecekan
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="sks-aksi">
                        <a href="{{ route('order.history') }}" class="sks-btn sks-btn-utama"><i class="bi bi-clock-history"></i> Lihat Riwayat</a>
                        <a href="{{ route('shop.index') }}" class="sks-btn sks-btn-garis"><i class="bi bi-bag"></i> Belanja Lagi</a>
                    </div>

                    <p class="sks-nota"><i class="bi bi-shield-lock"></i> Halaman ini hanya bisa diakses dari perangkat pemesan.</p>
                </div>

                {{-- Kolom kanan --}}
                <div class="sks-kolom">
                    <div class="sks-kartu" style="--c: #2563eb">
                        <div class="sks-kepala">
                            <span class="sks-ubin"><i class="bi bi-receipt"></i></span> Ringkasan Pesanan
                        </div>
                        <div class="sks-isi">
                            @foreach ($order->items as $item)
                                @php
                                    // Warna & ikon dari KATEGORI produk — taksonomi yang sama
                                    // dengan Keranjang, Checkout, Pembayaran, dan kedaluwarsa.
                                    $katSks = \App\Support\KategoriBeranda::untukProduk($item->product_name ?? '');
                                    $warnaSks = $katSks['warna'] ?? '#f26522';
                                    $ikonSks = $katSks['ikon'] ?? 'bi-box-seam';

                                    // Logo dipakai HANYA bila berkasnya ada; memasangnya tanpa
                                    // syarat membuat teks alt tampil sebagai gambar rusak.
                                    $berkasSks = $item->product_image ? basename($item->product_image) : null;
                                    $logoSks = null;
                                    if ($berkasSks) {
                                        foreach (['Product', 'ProductBundlings'] as $folderSks) {
                                            if (is_file(public_path('storage/img/'.$folderSks.'/'.$berkasSks))) {
                                                $logoSks = asset('storage/img/'.$folderSks.'/'.$berkasSks);
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <div class="sks-item" style="--c: {{ $warnaSks }}">
                                    <span class="sks-item-ubin">
                                        @if ($logoSks)
                                            <img src="{{ $logoSks }}" alt="{{ $item->product_name }}" loading="lazy"
                                                onerror="this.remove();">
                                        @else
                                            <i class="bi {{ $ikonSks }}"></i>
                                        @endif
                                    </span>
                                    <div>
                                        <div class="sks-item-nama">{{ $item->product_name }} @if ($item->delivery_status === 'cancelled') <span style="color:#dc2626;font-weight:700;">· Dibatalkan</span> @endif</div>
                                        <div class="sks-item-ket">{{ $item->getDurationLabel() }} &times;{{ $item->quantity }}</div>
                                    </div>
                                    <span class="sks-item-harga">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach

                            <div class="sks-garis"></div>

                            <div class="sks-row"><span>Subtotal</span><strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong></div>
                            @if ($order->total_discount > 0)
                                <div class="sks-row is-potong"><span>Diskon</span><strong>− Rp {{ number_format($order->total_discount, 0, ',', '.') }}</strong></div>
                            @endif
                            @if ((int) $order->unique_code > 0)
                                <div class="sks-row"><span>Kode Unik</span><strong>+ Rp {{ number_format($order->unique_code, 0, ',', '.') }}</strong></div>
                            @endif

                            <div class="sks-total">
                                <span>Total Dibayar</span>
                                <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Poin member / ajakan jadi member =====
                         Di sinilah tempat ajakannya, bukan di checkout: pembeli
                         sudah membayar, jadi tidak ada corong yang bisa bocor —
                         dan angkanya bisa disebut apa adanya dari pesanan yang
                         baru saja terjadi. --}}
                    @php
                        $perPoin = \App\Models\Customer::RUPIAH_PER_POIN;
                        $nilaiPoin = \App\Models\Customer::NILAI_PER_POIN;
                    @endphp

                    @if ($memberAktif)
                        @php
                            $sisaMenuju = max(0, $perPoin - $sisaPoin);
                            $persen = $perPoin > 0 ? min(100, round($sisaPoin / $perPoin * 100)) : 0;
                        @endphp
                        <div class="su-pm" style="--c: #db2777">
                            <div class="su-pm-kepala">
                                <span class="su-pm-ubin"><i class="bi bi-star-fill"></i></span> Poin Member
                            </div>
                            <div class="su-pm-isi">
                                <div class="su-pm-angka">
                                    <span class="su-pm-poin">{{ number_format($poin, 0, ',', '.') }}<small>poin</small></span>
                                    <span class="su-pm-nilai">senilai <b>Rp {{ number_format($poin * $nilaiPoin, 0, ',', '.') }}</b></span>
                                </div>

                                <div class="su-pm-batang"><span style="width: {{ $persen }}%"></span></div>

                                @if ($sisaPoin > 0)
                                    <p class="su-pm-ket">
                                        <b>Rp {{ number_format($sisaMenuju, 0, ',', '.') }}</b> lagi menuju poin berikutnya.
                                        Poin bisa dipakai untuk memotong total belanja di checkout.
                                    </p>
                                @else
                                    <p class="su-pm-ket">
                                        Tiap belanja terkumpul <b>Rp {{ number_format($perPoin, 0, ',', '.') }}</b> jadi
                                        <b>1 poin</b>, dan poin memotong total belanja berikutnya di checkout.
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        @php
                            // Dihitung dengan pembagi yang sama persis dengan
                            // Customer::calculateYearlyPoints(), supaya layar tidak
                            // menjanjikan poin yang tak pernah datang.
                            $poinDariBelanjaIni = intdiv((int) $order->total, $perPoin);
                        @endphp
                        <div class="su-pm" style="--c: #7c3aed">
                            <div class="su-pm-kepala">
                                <span class="su-pm-ubin"><i class="bi bi-stars"></i></span> Kamu Belum Jadi Member
                            </div>
                            <div class="su-pm-isi">
                                @if ($poinDariBelanjaIni >= 1)
                                    {{-- Angka nyata dari pesanan ini. Jauh lebih
                                         meyakinkan daripada janji umum. --}}
                                    <p class="su-pm-teks">
                                        Belanja <b>Rp {{ number_format($order->total, 0, ',', '.') }}</b> ini sebenarnya bisa jadi
                                        <b>{{ $poinDariBelanjaIni }} poin</b> — senilai
                                        <b>Rp {{ number_format($poinDariBelanjaIni * $nilaiPoin, 0, ',', '.') }}</b>
                                        untuk memotong belanja berikutnya. Jadi member itu gratis.
                                    </p>
                                @else
                                    {{-- Pesanan ini belum cukup untuk 1 poin: jangan
                                         sebut "bisa jadi 0 poin", itu malah mematahkan
                                         ajakannya sendiri. --}}
                                    <p class="su-pm-teks">
                                        Tiap belanja terkumpul <b>Rp {{ number_format($perPoin, 0, ',', '.') }}</b> jadi
                                        <b>1 poin</b>, dan 1 poin memotong <b>Rp {{ number_format($nilaiPoin, 0, ',', '.') }}</b>
                                        dari belanja berikutnya. Belanja ini pun ikut terkumpul begitu kamu jadi member — gratis.
                                    </p>
                                @endif
                                <a href="{{ route('member.info') }}" class="su-pm-btn">
                                    <i class="bi bi-gift"></i> Lihat Syarat &amp; Keuntungannya
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($order->butuhUpload())
    <script>
        function suSalinCek() {
            var el = document.getElementById('su-cek-link');
            var txt = el ? el.textContent.trim() : '';
            var done = function () {
                if (typeof window.phToast === 'function') window.phToast('Simpan link ini untuk unggah file & unduh hasil.', 'Link disalin', 'bi-clipboard-check');
                else if (typeof Swal !== 'undefined') Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:2400, title:'Link disalin' });
            };
            if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done).catch(done);
            else { var r=document.createRange(); r.selectNode(el); window.getSelection().removeAllRanges(); window.getSelection().addRange(r); try{document.execCommand('copy');}catch(e){} done(); }
        }
    </script>
    @endif
</div>
