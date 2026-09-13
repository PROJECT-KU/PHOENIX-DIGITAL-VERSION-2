<div class="byr-page" @if ($needsQris) wire:init="prepareQris" @endif>
    <style>
        /* ===== Pembayaran =====
           Bahasa visual sama dengan Keranjang & Checkout: kartu putih bersudut
           18px, ubin ikon berwarna di kepala tiap bagian, warna per bagian
           lewat --c.

           Kelas byr-*: aturan .pay-* dan .ct-* yang lama ada di
           public-custom-styles.css, dan berkas itu lewat Vite ke public/build
           yang masuk .gitignore — beku di server sampai ada rsync. Ditulis
           inline supaya tampilan ini ikut `git pull`.

           Yang TIDAK boleh berubah dan karena itu dipertahankan apa adanya:
           #countdown (digerakkan public/niceshop/assets/js/custom.js lewat
           data-expired, dan saat habis ia menulis "Kadaluarsa" ke dalamnya),
           #ph-qris-img (dibaca phDownloadQris untuk menggambar kartu unduhan),
           wire:init, wire:poll, dan seluruh wire:click. */
        .byr-page { --byr-ink: #1c1f26; --byr-muted: #6b7280; --byr-line: #eceff3; --byr-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .byr-sec { padding: 22px 0 64px; }

        /* ===== Jalur langkah — sama dengan /checkout, perhentiannya maju satu ===== */
        .byr-jalur { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 18px; }
        .byr-jalur::before { content: ""; position: absolute; z-index: 0; top: 17px; left: 12.5%; right: 12.5%; border-top: 2px dashed #f8d8bf; }
        .byr-henti { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 7px; text-align: center; }
        .byr-henti-bulat {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%; font-size: .92rem;
            background: #fff; border: 2px solid var(--byr-line); color: #c3cad3;
        }
        .byr-henti-bulat i.bi, .byr-henti-bulat i.bi::before { display: block; line-height: 1; }
        .byr-henti-teks { font-size: .78rem; font-weight: 700; color: #b4bcc6; }
        .byr-henti.is-lewat .byr-henti-bulat { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
        .byr-henti.is-lewat .byr-henti-teks { color: #16a34a; }
        /* border:0, bukan border-color:transparent — cincin border transparan di
           atas latar gradasi meninggalkan jahitan siku di dalam lingkaran. */
        .byr-henti.is-kini .byr-henti-bulat {
            background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff;
            box-shadow: 0 8px 18px -8px rgba(242, 101, 34, .85);
        }
        .byr-henti.is-kini .byr-henti-teks { color: var(--byr-ink); }

        /* ===== Tata letak ===== */
        .byr-tata { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 20px; align-items: start; }
        .byr-kolom { display: flex; flex-direction: column; gap: 16px; min-width: 0; }

        .byr-kartu { background: #fff; border: 1px solid var(--byr-line); border-radius: 18px; overflow: hidden; }
        .byr-kepala {
            display: flex; align-items: center; gap: 10px; padding: 15px 18px; border-bottom: 1px solid var(--byr-line);
            font-family: var(--byr-font); font-weight: 800; font-size: 1rem; color: var(--byr-ink); letter-spacing: -.015em;
        }
        .byr-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 34px; height: 34px; border-radius: 11px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .byr-ubin i.bi, .byr-ubin i.bi::before { display: block; line-height: 1; }
        .byr-isi { padding: 18px; }

        /* ===== Kartu QRIS ===== */
        .byr-qris-judul { text-align: center; margin-bottom: 14px; }
        .byr-qris-judul h3 { font-family: var(--byr-font); font-weight: 800; font-size: 1.25rem; color: var(--byr-ink); margin: 0 0 4px; letter-spacing: -.02em; }
        .byr-qris-judul p { font-size: .86rem; color: var(--byr-muted); margin: 0; }
        .byr-qris-judul p b { color: var(--byr-ink); font-weight: 700; }

        .byr-nominal { text-align: center; margin-bottom: 14px; }
        .byr-nominal small { display: block; font-size: .7rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #9aa3af; margin-bottom: 3px; }
        .byr-nominal strong {
            font-family: var(--byr-font); font-weight: 800; font-size: 2.1rem; letter-spacing: -.025em;
            background: linear-gradient(135deg, #fba919, #f26522); -webkit-background-clip: text; background-clip: text; color: transparent;
        }

        /* Hitung mundur ditaruh TEPAT di bawah nominal: dua angka yang
           menentukan keputusan pembeli, jangan dipisah oleh apa pun. */
        .byr-mundur {
            display: flex; align-items: center; justify-content: center; gap: 9px; margin: 0 auto 16px;
            width: fit-content; max-width: 100%; padding: 9px 16px; border-radius: 99px;
            background: #fffbeb; border: 1px solid #fde68a; color: #b45309;
            font-size: .84rem; font-weight: 700;
        }
        .byr-mundur i.bi, .byr-mundur i.bi::before { display: block; line-height: 1; font-size: .9rem; }
        /* #countdown bisa berisi "12:34" maupun kata "Kadaluarsa" saat waktunya
           habis — lebarnya karena itu tidak dipatok. */
        .byr-mundur b { font-family: var(--byr-font); font-weight: 800; font-size: .98rem; color: #92400e; font-variant-numeric: tabular-nums; }

        .byr-qr {
            display: flex; align-items: center; justify-content: center;
            padding: 16px; margin-bottom: 14px; border-radius: 16px;
            background: #fff; border: 1.5px solid #eadfd2;
            box-shadow: 0 10px 26px -20px rgba(35, 39, 47, .5);
        }
        .byr-qr img { display: block; width: 100%; max-width: 300px; height: auto; }
        .byr-qr.is-memuat { min-height: 240px; }

        .byr-meta { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-bottom: 14px; }
        .byr-meta span {
            display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 99px;
            background: #f8fafc; border: 1px solid #e8edf3; color: #64748b;
            font-size: .74rem; font-weight: 600; font-family: ui-monospace, monospace;
        }

        /* Peringatan penipuan: satu-satunya blok merah di halaman ini, supaya
           ia tidak bersaing dengan apa pun. */
        .byr-awas {
            display: flex; align-items: flex-start; gap: 10px; margin-bottom: 14px;
            padding: 12px 14px; border-radius: 13px;
            background: #fff5f5; border: 1px solid #fecaca; border-left: 4px solid #dc2626;
            font-size: .83rem; line-height: 1.6; color: #7f1d1d;
        }
        .byr-awas i.bi { flex-shrink: 0; margin-top: 2px; font-size: 1rem; color: #dc2626; }
        .byr-awas i.bi::before { display: block; line-height: 1; }
        .byr-awas b, .byr-awas strong { font-weight: 800; color: #b91c1c; }

        /* ===== Cara pembayaran: satu jalur berisi lima perhentian =====
           Bentuk yang sama dengan "Cara Pesan" di beranda. Nomornya cukup satu
           warna di sini — ini urutan langkah, bukan kategori yang berbeda. */
        .byr-cara { padding: 15px 16px; margin-bottom: 14px; border-radius: 14px; background: #fcfcfd; border: 1px solid var(--byr-line); }
        .byr-cara-judul {
            display: flex; align-items: center; gap: 8px; margin-bottom: 13px;
            font-family: var(--byr-font); font-weight: 800; font-size: .88rem; color: var(--byr-ink);
        }
        .byr-cara-judul i.bi, .byr-cara-judul i.bi::before { display: block; line-height: 1; color: #f26522; }
        .byr-deret { position: relative; display: flex; flex-direction: column; gap: 11px; }
        .byr-deret::before { content: ""; position: absolute; z-index: 0; left: 13px; top: 26px; bottom: 14px; border-left: 2px dashed #f8d8bf; }
        .byr-langkah { position: relative; z-index: 1; display: flex; align-items: center; gap: 11px; }
        .byr-nomor {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 28px; height: 28px; border-radius: 50%;
            background: #fff4ec; border: 1.5px solid #fbd3b4; color: #c2410c;
            font-family: var(--byr-font); font-weight: 800; font-size: .8rem; line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .byr-langkah span { font-size: .85rem; color: #4b5563; line-height: 1.5; }
        .byr-langkah span b { color: var(--byr-ink); font-weight: 700; }

        /* ===== Tombol ===== */
        .byr-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 48px;
            padding: 0 16px; border: 1.5px solid transparent; border-radius: 13px; cursor: pointer;
            font-family: var(--byr-font); font-weight: 700; font-size: .9rem;
            transition: filter .16s ease, transform .16s ease, background .16s ease, border-color .16s ease, color .16s ease;
        }
        .byr-btn > span { display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .byr-btn i.bi, .byr-btn i.bi::before { display: block; line-height: 1; font-size: .95rem; }
        .byr-btn:disabled { opacity: .6; cursor: wait; transform: none; filter: none; }
        /* border:0 — gradasi harus mengisi sampai tepi, bukan berhenti di cincin
           border transparan dan menyisakan garis pucat. */
        .byr-btn-utama { background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff; box-shadow: 0 12px 24px -14px rgba(242, 101, 34, .85); }
        .byr-btn-utama:not(:disabled):hover { filter: brightness(1.05); transform: translateY(-1px); }
        .byr-btn-garis { background: #fff; border-color: #e5e0d8; color: var(--byr-ink); }
        .byr-btn-garis:not(:disabled):hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }
        .byr-btn + .byr-btn { margin-top: 10px; }

        .byr-auto { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 12px 0 0; font-size: .78rem; color: var(--byr-muted); }
        .byr-auto i.bi, .byr-auto i.bi::before { display: block; line-height: 1; font-size: .82rem; color: #16a34a; }

        /* ===== Kabar ===== */
        .byr-kabar {
            display: flex; align-items: flex-start; gap: 9px; margin-bottom: 14px;
            padding: 11px 13px; border-radius: 13px; border: 1px solid transparent;
            font-size: .84rem; font-weight: 600; line-height: 1.55;
        }
        .byr-kabar i.bi { flex-shrink: 0; margin-top: 1px; }
        .byr-kabar i.bi::before { display: block; line-height: 1; }
        .byr-kabar.is-galat { background: #fff5f5; border-color: #fecaca; color: #b91c1c; }

        /* ===== Kolom kanan ===== */
        .byr-item { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; align-items: center; gap: 11px; padding: 11px 0; }
        .byr-item + .byr-item { border-top: 1px dashed var(--byr-line); }
        /* Logo produk bila berkasnya ada, ikon kategori bila tidak — sama
           dengan ringkasan di /checkout. */
        .byr-item-ubin {
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            width: 40px; height: 40px; border-radius: 12px; font-size: .92rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .byr-item-ubin i.bi, .byr-item-ubin i.bi::before { display: block; line-height: 1; }
        .byr-item-ubin img { max-width: 76%; max-height: 76%; object-fit: contain; mix-blend-mode: multiply; }
        .byr-item-nama { font-family: var(--byr-font); font-weight: 700; font-size: .86rem; color: var(--byr-ink); line-height: 1.35; }
        .byr-item-ket { font-size: .76rem; color: var(--byr-muted); margin-top: 1px; }
        .byr-item-harga { font-family: var(--byr-font); font-weight: 700; font-size: .86rem; color: var(--byr-ink); white-space: nowrap; }

        .byr-garis { height: 1px; margin: 12px 0; background: repeating-linear-gradient(to right, var(--byr-line) 0 6px, transparent 6px 12px); }
        .byr-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: .86rem; color: var(--byr-muted); }
        .byr-row strong { font-family: var(--byr-font); font-weight: 700; color: var(--byr-ink); }
        .byr-row.is-potong strong { color: #16a34a; }
        .byr-row b { font-family: var(--byr-font); font-weight: 700; color: var(--byr-ink); text-align: right; word-break: break-word; }

        .byr-total { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 12px 0 2px; }
        .byr-total span { font-family: var(--byr-font); font-weight: 700; font-size: .95rem; color: var(--byr-ink); }
        .byr-total strong {
            font-family: var(--byr-font); font-weight: 800; font-size: 1.45rem; letter-spacing: -.02em;
            background: linear-gradient(135deg, #fba919, #f26522); -webkit-background-clip: text; background-clip: text; color: transparent;
        }

        .byr-tenggat {
            display: flex; align-items: center; gap: 11px; padding: 14px 16px; border-radius: 16px;
            background: linear-gradient(135deg, #fffbeb, #fff); border: 1px solid #fde68a;
        }
        .byr-tenggat i.bi { flex-shrink: 0; font-size: 1.2rem; color: #d97706; }
        .byr-tenggat i.bi::before { display: block; line-height: 1; }
        .byr-tenggat span { display: block; font-size: .76rem; color: #b45309; font-weight: 600; }
        .byr-tenggat b { font-family: var(--byr-font); font-weight: 800; font-size: .92rem; color: #92400e; }

        .byr-nota { display: flex; align-items: flex-start; gap: 9px; padding: 13px 15px; border-radius: 14px; background: #f8fafc; border: 1px solid #e8edf3; font-size: .8rem; color: var(--byr-muted); line-height: 1.65; }
        .byr-nota i.bi { flex-shrink: 0; margin-top: 2px; font-size: .9rem; color: #64748b; }
        .byr-nota i.bi::before { display: block; line-height: 1; }

        @media (max-width: 991.98px) {
            .byr-tata { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .byr-isi { padding: 15px; }
            .byr-jalur { gap: 4px; }
            .byr-henti-teks { font-size: .68rem; }
            .byr-nominal strong { font-size: 1.75rem; }
            .byr-total strong { font-size: 1.3rem; }
            .byr-qr { padding: 12px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .byr-btn, .byr-btn:hover { transition: none; transform: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-qr-code"></i> Pembayaran</span>
                <h1>Selesaikan Pembayaran</h1>
                <p>Order <b>#{{ $order->order_number }}</b> — scan QRIS di bawah untuk membayar.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">Pembayaran</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="byr-sec">
        <div class="container">
            @if (session()->has('error'))
                <div class="byr-kabar is-galat">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Perhentian ketiga yang menyala; dua yang pertama sudah dilewati. --}}
            <div class="byr-jalur">
                <div class="byr-henti is-lewat">
                    <span class="byr-henti-bulat"><i class="bi bi-check-lg"></i></span>
                    <span class="byr-henti-teks">Keranjang</span>
                </div>
                <div class="byr-henti is-lewat">
                    <span class="byr-henti-bulat"><i class="bi bi-check-lg"></i></span>
                    <span class="byr-henti-teks">Data &amp; Promo</span>
                </div>
                <div class="byr-henti is-kini">
                    <span class="byr-henti-bulat"><i class="bi bi-credit-card-2-front-fill"></i></span>
                    <span class="byr-henti-teks">Bayar</span>
                </div>
                <div class="byr-henti">
                    <span class="byr-henti-bulat"><i class="bi bi-inbox-fill"></i></span>
                    <span class="byr-henti-teks">Terima</span>
                </div>
            </div>

            <div class="byr-tata">
                {{-- Kolom kiri: QRIS --}}
                <div class="byr-kolom">
                    @if ($qrCodeImage && $payment)
                        <div wire:poll.15s="checkPaymentStatus">
                            <div class="byr-kartu" style="--c: #f26522">
                                <div class="byr-kepala">
                                    <span class="byr-ubin"><i class="bi bi-qr-code-scan"></i></span> QRIS
                                </div>
                                <div class="byr-isi">
                                    <div class="byr-qris-judul">
                                        <h3>Scan untuk Membayar</h3>
                                        <p>Pembayaran ke <b>Phoenix Digital Warehouse</b></p>
                                    </div>

                                    <div class="byr-nominal">
                                        <small>Nominal</small>
                                        <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                                    </div>

                                    @if (!$payment->isExpired())
                                        {{-- #countdown & data-expired WAJIB tetap: digerakkan
                                             custom.js, dan saat habis ia memanggil
                                             checkPaymentStatus lewat komponen terdekat. --}}
                                        <div class="byr-mundur">
                                            <i class="bi bi-clock-history"></i>
                                            <span>Sisa waktu</span>
                                            <b id="countdown" wire:ignore data-expired="{{ $payment->expired_at->toIso8601String() }}">…</b>
                                        </div>
                                    @else
                                        <div class="byr-kabar is-galat" style="justify-content: center;">
                                            <i class="bi bi-x-octagon-fill"></i>
                                            <span>QRIS sudah kadaluarsa.</span>
                                        </div>
                                    @endif

                                    <div class="byr-qr">
                                        <img id="ph-qris-img" src="data:image/png;base64,{{ $qrCodeImage }}" alt="QRIS">
                                    </div>

                                    @if ($qrisNmid || $qrisInvoiceId)
                                        <div class="byr-meta">
                                            @if ($qrisNmid)
                                                <span>NMID: {{ $qrisNmid }}</span>
                                            @endif
                                            @if ($qrisInvoiceId)
                                                <span>Invoice: {{ $qrisInvoiceId }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="byr-awas">
                                        <i class="bi bi-shield-exclamation"></i>
                                        <div>
                                            <b>Hati-hati penipuan!</b>
                                            Pastikan pembayaran tertuju atas nama
                                            <strong>Phoenix Digital Warehouse</strong>.
                                            Selain nama itu, <u>dipastikan penipuan</u> — jangan lanjutkan.
                                        </div>
                                    </div>

                                    <div class="byr-cara">
                                        <div class="byr-cara-judul"><i class="bi bi-list-check"></i> Cara Pembayaran</div>
                                        <div class="byr-deret">
                                            @foreach ([
                                                'Buka aplikasi Mobile Banking / E-Wallet.',
                                                'Pilih menu <b>Scan QRIS</b>.',
                                                'Scan QR Code di atas.',
                                                'Pastikan nominal <b>sama persis</b>.',
                                                'Selesaikan pembayaran.',
                                            ] as $i => $langkah)
                                                <div class="byr-langkah">
                                                    <span class="byr-nomor">{{ $i + 1 }}</span>
                                                    <span>{!! $langkah !!}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <button type="button" class="byr-btn byr-btn-garis" onclick="phDownloadQris(this)"
                                        data-order="{{ $order->order_number }}"
                                        data-nominal="Rp {{ number_format($order->total, 0, ',', '.') }}"
                                        data-nmid="{{ $qrisNmid }}"
                                        data-invoice="{{ $qrisInvoiceId }}"
                                        data-expired="{{ $payment->expired_at->translatedFormat('d M Y, H:i') }}">
                                        <span><i class="bi bi-download"></i> Simpan / Unduh QRIS</span>
                                    </button>

                                    @if (!$payment->isExpired())
                                        <button wire:click="checkPaymentStatus" class="byr-btn byr-btn-utama"
                                            wire:loading.attr="disabled" wire:target="checkPaymentStatus">
                                            <span wire:loading.remove wire:target="checkPaymentStatus"><i class="bi bi-check-circle-fill"></i> Saya Sudah Bayar</span>
                                            <span wire:loading wire:target="checkPaymentStatus"><span class="spinner-border spinner-border-sm"></span> Mengecek...</span>
                                        </button>
                                        <p class="byr-auto"><i class="bi bi-arrow-repeat"></i> Status pembayaran diperiksa otomatis setiap 15 detik.</p>
                                    @else
                                        <button wire:click="generateNewQris" class="byr-btn byr-btn-utama">
                                            <span><i class="bi bi-arrow-clockwise"></i> Buat QRIS Baru</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif ($qrisError)
                        <div class="byr-kartu" style="--c: #dc2626">
                            <div class="byr-kepala">
                                <span class="byr-ubin"><i class="bi bi-qr-code-scan"></i></span> Gagal Membuat QRIS
                            </div>
                            <div class="byr-isi">
                                <div class="byr-kabar is-galat">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>{{ $qrisError }}</span>
                                </div>
                                <button wire:click="retryQris" class="byr-btn byr-btn-utama"
                                    wire:loading.attr="disabled" wire:target="retryQris">
                                    <span wire:loading.remove wire:target="retryQris"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</span>
                                    <span wire:loading wire:target="retryQris"><span class="spinner-border spinner-border-sm"></span> Membuat...</span>
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- QRIS sedang dibuat (dipicu wire:init) — halaman sudah tampil instan --}}
                        <div class="byr-kartu" style="--c: #f26522">
                            <div class="byr-kepala">
                                <span class="byr-ubin"><i class="bi bi-qr-code-scan"></i></span> QRIS
                            </div>
                            <div class="byr-isi">
                                <div class="byr-qris-judul">
                                    <h3>Menyiapkan QRIS…</h3>
                                    <p>Sebentar ya, kami sedang membuat kode pembayaran Anda.</p>
                                </div>
                                <div class="byr-nominal">
                                    <small>Nominal</small>
                                    <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                                </div>
                                <div class="byr-qr is-memuat">
                                    <span class="spinner-border" style="width:2.4rem;height:2.4rem;color:#f26522;" role="status" aria-label="Membuat QRIS"></span>
                                </div>
                                <p class="byr-auto"><i class="bi bi-shield-lock" style="color:#64748b;"></i> Kode QRIS aman &amp; unik untuk pesanan ini.</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Kolom kanan: ringkasan & info --}}
                <div class="byr-kolom">
                    <div class="byr-kartu" style="--c: #2563eb">
                        <div class="byr-kepala">
                            <span class="byr-ubin"><i class="bi bi-receipt"></i></span> Detail Pesanan
                        </div>
                        <div class="byr-isi">
                            @foreach ($order->items as $item)
                                @php
                                    // Warna & ikon dari KATEGORI produk — taksonomi yang sama
                                    // dengan Keranjang, Checkout, Shop, dan beranda.
                                    $katByr = \App\Support\KategoriBeranda::untukProduk($item->product_name ?? '');
                                    $warnaByr = $katByr['warna'] ?? '#f26522';
                                    $ikonByr = $katByr['ikon'] ?? 'bi-box-seam';

                                    // Logo dipakai HANYA bila berkasnya ada; memasangnya tanpa
                                    // syarat membuat teks alt tampil sebagai gambar rusak.
                                    // Item paket sudah dipecah jadi baris produk, tapi baris
                                    // paket lama masih mungkin ada — karena itu dua folder dicoba.
                                    $berkasByr = $item->product_image ? basename($item->product_image) : null;
                                    $logoByr = null;
                                    if ($berkasByr) {
                                        foreach (['Product', 'ProductBundlings'] as $folderByr) {
                                            if (is_file(public_path('storage/img/'.$folderByr.'/'.$berkasByr))) {
                                                $logoByr = asset('storage/img/'.$folderByr.'/'.$berkasByr);
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <div class="byr-item" style="--c: {{ $warnaByr }}">
                                    <span class="byr-item-ubin">
                                        @if ($logoByr)
                                            <img src="{{ $logoByr }}" alt="{{ $item->product_name }}" loading="lazy"
                                                onerror="this.remove();">
                                        @else
                                            <i class="bi {{ $ikonByr }}"></i>
                                        @endif
                                    </span>
                                    <div>
                                        <div class="byr-item-nama">{{ $item->product_name }}</div>
                                        <div class="byr-item-ket">{{ $item->getDurationLabel() }} &times;{{ $item->quantity }}</div>
                                    </div>
                                    <span class="byr-item-harga">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach

                            <div class="byr-garis"></div>

                            <div class="byr-row"><span>Subtotal</span><strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong></div>
                            @if ($order->total_discount > 0)
                                <div class="byr-row is-potong"><span>Diskon</span><strong>− Rp {{ number_format($order->total_discount, 0, ',', '.') }}</strong></div>
                            @endif
                            @if ((int) $order->unique_code > 0)
                                <div class="byr-row"><span>Kode Unik</span><strong>+ Rp {{ number_format($order->unique_code, 0, ',', '.') }}</strong></div>
                            @endif

                            <div class="byr-total">
                                <span>Total Pembayaran</span>
                                <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="byr-tenggat">
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            <span>Batas Waktu Pembayaran</span>
                            <b>{{ $payment && $payment->expired_at ? $payment->expired_at->format('d M Y, H:i').' WIB' : '—' }}</b>
                        </div>
                    </div>

                    <div class="byr-kartu" style="--c: #7c3aed">
                        <div class="byr-kepala">
                            <span class="byr-ubin"><i class="bi bi-person-fill"></i></span> Informasi Pelanggan
                        </div>
                        <div class="byr-isi">
                            <div class="byr-row"><span>Nama</span><b>{{ $order->customer->nama }}</b></div>
                            <div class="byr-row"><span>Email</span><b>{{ $order->customer->email }}</b></div>
                            <div class="byr-row"><span>No HP</span><b>{{ $order->customer->no_hp }}</b></div>
                            <div class="byr-row"><span>Status</span><span>{!! $order->getStatusBadge() !!}</span></div>
                            <div class="byr-row"><span>Dibuat</span><b>{{ $order->created_at->format('d M Y, H:i') }}</b></div>
                        </div>
                    </div>

                    <div class="byr-nota">
                        <i class="bi bi-info-circle"></i>
                        <span>Akun premium dikirim via email/WhatsApp maks. 1×24 jam setelah pembayaran terverifikasi. Jika tidak dibayar hingga batas waktu, pesanan otomatis dibatalkan.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            window.phDownloadQris = function (btn) {
                var imgEl = document.getElementById('ph-qris-img');
                if (!imgEl) return;
                var d = btn.dataset;

                var im = new Image();
                im.onload = function () {
                    var W = 760, H = 1120, F = "'Poppins','Segoe UI',Arial,sans-serif";
                    var c = document.createElement('canvas');
                    c.width = W; c.height = H;
                    var x = c.getContext('2d');

                    function rr(px, py, w, h, r) {
                        x.beginPath();
                        x.moveTo(px + r, py);
                        x.arcTo(px + w, py, px + w, py + h, r);
                        x.arcTo(px + w, py + h, px, py + h, r);
                        x.arcTo(px, py + h, px, py, r);
                        x.arcTo(px, py, px + w, py, r);
                        x.closePath();
                    }

                    // Latar + kartu
                    x.fillStyle = '#fff6ee'; x.fillRect(0, 0, W, H);
                    rr(28, 28, W - 56, H - 56, 30); x.fillStyle = '#fff'; x.fill();
                    x.lineWidth = 2; x.strokeStyle = '#f3dccb'; x.stroke();

                    // Header oranye (sudut atas membulat via clip)
                    x.save();
                    rr(28, 28, W - 56, H - 56, 30); x.clip();
                    var g = x.createLinearGradient(28, 28, W - 28, 190);
                    g.addColorStop(0, '#fba919'); g.addColorStop(1, '#f0531e');
                    x.fillStyle = g; x.fillRect(28, 28, W - 56, 162);
                    x.restore();

                    x.textAlign = 'center';
                    x.fillStyle = '#fff';
                    x.font = '800 40px ' + F;
                    x.fillText('Phoenix Digital', W / 2, 100);
                    x.font = '600 19px ' + F;
                    x.fillText('W A R E H O U S E', W / 2, 140);

                    x.fillStyle = '#8a6a4e'; x.font = '600 20px ' + F;
                    x.fillText('Scan QRIS untuk membayar', W / 2, 232);

                    // Kotak QR
                    var qs = 430, qx = (W - qs) / 2, qy = 260;
                    rr(qx - 22, qy - 22, qs + 44, qs + 44, 22);
                    x.fillStyle = '#fff'; x.fill(); x.lineWidth = 2; x.strokeStyle = '#eadfd2'; x.stroke();
                    x.drawImage(im, qx, qy, qs, qs);

                    var metaY = qy + qs + 52;
                    var meta = [];
                    if (d.nmid) meta.push('NMID: ' + d.nmid);
                    if (d.invoice) meta.push('Invoice: ' + d.invoice);
                    if (meta.length) { x.fillStyle = '#9a8a79'; x.font = '400 17px ' + F; x.fillText(meta.join('     •     '), W / 2, metaY); }

                    // Nominal
                    x.fillStyle = '#9a8a79'; x.font = '600 16px ' + F;
                    x.fillText('N O M I N A L', W / 2, metaY + 46);
                    x.fillStyle = '#f0531e'; x.font = '800 46px ' + F;
                    x.fillText(d.nominal || '', W / 2, metaY + 96);

                    // Peringatan penipuan
                    var wy = metaY + 132, wh = 150, wx = 60, ww = W - 120;
                    rr(wx, wy, ww, wh, 16); x.fillStyle = '#fff5f0'; x.fill();
                    x.lineWidth = 1.5; x.strokeStyle = '#f6c6ad'; x.stroke();
                    x.fillStyle = '#f0531e'; x.fillRect(wx, wy + 14, 5, wh - 28);

                    x.fillStyle = '#b3401a'; x.font = '800 23px ' + F;
                    x.fillText('Hati-hati Penipuan!', W / 2, wy + 44);
                    var lines = ['Pastikan pembayaran atas nama', 'PHOENIX DIGITAL WAREHOUSE.', 'Selain nama itu, dipastikan penipuan.'];
                    for (var i = 0; i < lines.length; i++) {
                        x.font = (i === 1 ? '800 20px ' : '400 20px ') + F;
                        x.fillStyle = (i === 1 ? '#b3401a' : '#7a3d1a');
                        x.fillText(lines[i], W / 2, wy + 80 + i * 28);
                    }

                    // Footer
                    x.fillStyle = '#9a8a79'; x.font = '400 16px ' + F;
                    var foot = 'Order ' + (d.order || '') + (d.expired ? '     •     Berlaku s.d. ' + d.expired : '');
                    x.fillText(foot, W / 2, H - 54);

                    var a = document.createElement('a');
                    a.href = c.toDataURL('image/png');
                    a.download = 'QRIS-' + (d.order || 'phoenix-digital') + '.png';
                    document.body.appendChild(a); a.click(); a.remove();
                };
                im.src = imgEl.src;
            };
        </script>
    @endpush
</div>
