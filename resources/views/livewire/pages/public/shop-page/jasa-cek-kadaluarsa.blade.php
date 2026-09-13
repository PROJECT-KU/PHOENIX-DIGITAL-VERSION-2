<div class="cke-page">
    <style>
        /* ===== Halaman jasa yang masa aksesnya berakhir =====
           Bahasa visual sama dengan /cek dan halaman lain di alur belanja.

           Warna halaman mengikuti JENIS JASA. Sebelumnya seluruh halaman ini
           dipatok kuning/jingga (#b45309, #fde68a, #f26522) padahal ia menerima
           $ragam dan hanya memakai kosakatanya. Akibatnya pelanggan Deteksi AI —
           yang warnanya indigo — menutup pesanannya di halaman berwarna layanan
           pengecekan, dan pelanggan parafrase begitu pula.

           Kelas cke-*: .cart-section yang lama ada di public-custom-styles.css
           yang lewat Vite ke public/build, dan folder itu masuk .gitignore —
           beku di server sampai ada rsync. */
        .cke-page {
            --kek-warna: {{ $ragam['warna'] }};
            --kek-lembut: {{ $ragam['lembut'] }};
            --kek-tepi: {{ $ragam['tepi'] }};
            --ph-orange: #f26522; --ph-ink: #23272f; --ph-muted: #6b7280;
            --ph-soft: #fff8f1; --ph-line: #f1e6d8;
            --cke-font: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        }
        .cke-sec { padding: 22px 0 64px; }

        /* Satu kabar penutup, jadi kartunya sengaja TIDAK selebar kartu kepala:
           satu paragraf yang direntangkan 1296px justru lebih sulit dibaca.
           Yang diseragamkan bahasanya — kartu putih, sudut 18px, ubin ikon
           berwarna — bukan lebarnya. */
        .cke-kartu {
            max-width: 620px; margin: 0 auto; text-align: center;
            background: #fff; border: 1px solid #eceff3; border-radius: 18px;
            padding: 34px 28px 30px;
        }

        /* Ikon besar di tengah, berwarna jenis jasanya. */
        .cke-ic {
            width: 84px; height: 84px; border-radius: 50%; margin: 0 auto 18px;
            display: grid; place-items: center; font-size: 2.2rem;
            color: var(--kek-warna);
            background: linear-gradient(135deg, color-mix(in srgb, var(--kek-lembut) 70%, #fff), var(--kek-lembut));
            border: 1px solid var(--kek-tepi);
        }
        .cke-ic i.bi, .cke-ic i.bi::before { display: block; line-height: 1; }

        .cke-lencana {
            display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;
            height: 28px; padding: 0 13px; border-radius: 99px;
            background: var(--kek-lembut); border: 1px solid var(--kek-tepi);
            color: color-mix(in srgb, var(--kek-warna) 85%, #0f172a);
            font-size: .73rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
        }
        .cke-lencana i.bi, .cke-lencana i.bi::before { display: block; line-height: 1; font-size: .8rem; }

        .cke-title { font-family: var(--cke-font); font-weight: 800; color: var(--ph-ink);
            font-size: 1.45rem; margin: 0 0 8px; letter-spacing: -.02em; }
        .cke-sub { color: var(--ph-muted); font-size: .95rem; line-height: 1.7; margin: 0 auto; max-width: 460px; }
        .cke-order { font-family: ui-monospace, 'Courier New', monospace; font-weight: 700;
            color: color-mix(in srgb, var(--kek-warna) 85%, #0f172a);
            background: var(--kek-lembut); border: 1px solid var(--kek-tepi);
            border-radius: 7px; padding: 1px 8px; }

        .cke-info {
            background: var(--kek-lembut); border: 1px solid var(--kek-tepi); border-radius: 14px;
            padding: 14px 16px; margin: 20px auto 0; max-width: 480px; text-align: left;
            font-size: .86rem; color: #4b5563; line-height: 1.7;
        }
        .cke-info-judul {
            display: flex; align-items: center; gap: 7px; margin-bottom: 6px;
            font-family: var(--cke-font); font-weight: 800; font-size: .87rem;
            color: color-mix(in srgb, var(--kek-warna) 85%, #0f172a);
        }
        .cke-info-judul i.bi, .cke-info-judul i.bi::before { display: block; line-height: 1; }
        .cke-info b { color: color-mix(in srgb, var(--kek-warna) 88%, #0f172a); font-weight: 700; }

        .cke-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 22px; }
        .cke-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 46px; padding: 0 20px; border-radius: 13px; text-decoration: none;
            border: 1.5px solid transparent; cursor: pointer;
            font-family: var(--cke-font); font-weight: 700; font-size: .88rem; white-space: nowrap;
            transition: filter .18s ease, transform .18s ease, border-color .18s ease, color .18s ease;
        }
        .cke-btn i.bi, .cke-btn i.bi::before { display: block; line-height: 1; font-size: .95rem; }
        /* border:0 — cincin border transparan di atas latar gradasi
           meninggalkan garis pucat di tepi tombol. Jingganya tetap: itu warna
           tindakan rumah di seluruh toko, bukan penanda jenis layanan. */
        .cke-btn.primary {
            background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff;
            box-shadow: 0 12px 24px -14px rgba(242, 101, 34, .85);
        }
        .cke-btn.primary:hover { color: #fff; transform: translateY(-1px); filter: brightness(1.05); }
        .cke-btn.ghost { background: #fff; border-color: #e5e0d8; color: var(--ph-ink); }
        .cke-btn.ghost:hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }

        @media (max-width: 575.98px) {
            .cke-kartu { padding: 26px 18px 24px; }
            .cke-ic { width: 70px; height: 70px; font-size: 1.85rem; }
            .cke-title { font-size: 1.22rem; }
            .cke-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .cke-btn, .cke-btn:hover { transition: none; transform: none; }
        }
    </style>

    <!-- Page Title -->
    {{-- --c menyetel aksen kartu kepala mengikuti JENIS JASA, sama seperti di
         halaman /cek yang aktif. --}}
    <div class="page-title ph-page-title" style="--c: {{ $ragam['warna'] }}">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-{{ $ragam['ikon'] }}"></i> {{ $ragam['nama'] }}</span>
                <h1>Masa Akses Berakhir</h1>
                <p>Halaman pesanan <b>#{{ $order->order_number }}</b> sudah ditutup demi menjaga kerahasiaan dokumen Anda.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">{{ $ragam['nama'] }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="cke-sec">
        <div class="container">
            <div class="cke-kartu">
                <div class="cke-ic"><i class="bi bi-clock-history"></i></div>

                <span class="cke-lencana"><i class="bi bi-lock-fill"></i> Ditutup</span>

                {{-- Kata "pengecekan" diganti kosakata jasanya: pelanggan parafrase
                     tidak pernah membeli pengecekan, dan membacanya di halaman
                     penutup membuatnya mengira salah membuka pesanan orang lain. --}}
                <h3 class="cke-title">Halaman {{ $ragam['nama'] }} Sudah Berakhir</h3>
                <p class="cke-sub">
                    Seluruh {{ $ragam['satuan'] }} untuk pesanan
                    <span class="cke-order">{{ $order->order_number }}</span> sudah selesai,
                    dan masa akses halaman ini telah berakhir.
                </p>

                <div class="cke-info">
                    <div class="cke-info-judul"><i class="bi bi-info-circle-fill"></i> Kenapa link ini tidak bisa dibuka lagi?</div>
                    Setiap halaman hanya aktif <b>24 jam setelah hasil terakhir diunggah</b>, demi
                    menjaga keamanan &amp; kerahasiaan dokumen Anda.
                    @isset($kadaluarsaAt)
                        Masa akses berakhir pada
                        <b>{{ $kadaluarsaAt->translatedFormat('l, d F Y • H:i') }} WIB</b>.
                    @endisset
                    Jika Anda masih membutuhkan hasilnya atau ingin memesan lagi, silakan hubungi kami.
                </div>

                <div class="cke-actions">
                    <a href="{{ url('/') }}" class="cke-btn primary"><i class="bi bi-house-door"></i> Kembali ke Beranda</a>
                    <a href="{{ url('/shop') }}" class="cke-btn ghost"><i class="bi bi-bag"></i> Pesan Lagi</a>
                </div>
            </div>
        </div>
    </section>
</div>
