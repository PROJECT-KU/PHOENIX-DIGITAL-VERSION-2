<div class="ck-page">
    <style>
        /* ===== Checkout =====
           Bahasa visual sama dengan Shop, Bundling, Keranjang, dan Wishlist:
           kartu putih bersudut 18px, ubin ikon berwarna di kepala tiap kartu,
           dan warna per bagian (--c) yang diambil dari palet kategori.

           Kelas ck-*: aturan .co-* yang lama ada di public-custom-styles.css,
           dan berkas itu lewat Vite ke public/build yang masuk .gitignore —
           beku di server sampai ada rsync. Ditulis inline supaya tampilan ini
           ikut `git pull`, sama seperti Keranjang dan Wishlist. */
        .ck-page { --ck-ink: #1c1f26; --ck-muted: #6b7280; --ck-line: #eceff3; --ck-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .ck-sec { padding: 22px 0 64px; }

        /* ===== Jalur langkah =====
           Bentuknya diambil dari "Cara Pesan" di beranda: satu JALUR berisi
           beberapa perhentian, bukan kartu setara. Di checkout ia menjawab
           "masih berapa langkah lagi?" — pertanyaan yang paling menentukan
           apakah orang meneruskan atau menutup tab. */
        .ck-jalur { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 18px; }
        .ck-jalur::before {
            content: ""; position: absolute; z-index: 0; top: 17px;
            left: 12.5%; right: 12.5%; border-top: 2px dashed #f8d8bf;
        }
        .ck-henti { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 7px; text-align: center; }
        .ck-henti-bulat {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%; font-size: .92rem;
            /* Latarnya PEKAT, bukan tembus pandang: ia harus menutupi garis
               jalur yang lewat di belakangnya. */
            background: #fff; border: 2px solid var(--ck-line); color: #c3cad3;
        }
        .ck-henti-bulat i.bi, .ck-henti-bulat i.bi::before { display: block; line-height: 1; }
        .ck-henti-teks { font-size: .78rem; font-weight: 700; color: #b4bcc6; }
        /* Yang sudah dilewati memakai hijau: di mana pun hijau berarti beres. */
        .ck-henti.is-lewat .ck-henti-bulat { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
        .ck-henti.is-lewat .ck-henti-teks { color: #16a34a; }
        .ck-henti.is-kini .ck-henti-bulat {
            background: linear-gradient(135deg, #fba919, #f26522); border-color: transparent; color: #fff;
            box-shadow: 0 8px 18px -8px rgba(242, 101, 34, .85);
        }
        .ck-henti.is-kini .ck-henti-teks { color: var(--ck-ink); }

        /* ===== Tata letak dua kolom ===== */
        .ck-tata { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 20px; align-items: start; }
        .ck-kolom { display: flex; flex-direction: column; gap: 16px; min-width: 0; }

        /* ===== Kartu ===== */
        .ck-kartu { background: #fff; border: 1px solid var(--ck-line); border-radius: 18px; overflow: hidden; }
        .ck-kepala {
            display: flex; align-items: center; gap: 10px; padding: 15px 18px;
            border-bottom: 1px solid var(--ck-line);
            font-family: var(--ck-font); font-weight: 800; font-size: 1rem; color: var(--ck-ink); letter-spacing: -.015em;
        }
        /* Ubin ikon berwarna: penanda bagian, bukan hiasan — mata menemukan
           "Kode Promo" tanpa membaca judulnya lebih dulu. */
        .ck-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 34px; height: 34px; border-radius: 11px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .ck-ubin i.bi, .ck-ubin i.bi::before { display: block; line-height: 1; }
        .ck-opsional { font-weight: 600; font-size: .78rem; color: var(--ck-muted); }
        .ck-isi { padding: 18px; }

        /* ===== Isian ===== */
        .ck-baris { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .ck-medan { display: flex; flex-direction: column; gap: 7px; }
        /* Jarak antar medan hanya untuk yang BERTUMPUK. Dibatasi ke anak
           langsung .ck-isi: tanpa itu medan kedua di dalam .ck-baris ikut
           mendapat margin atas, sehingga Nama Lengkap dan Email berdiri di
           ketinggian yang berbeda. */
        .ck-isi > .ck-medan + .ck-medan,
        .ck-isi > .ck-baris + .ck-medan,
        .ck-isi > .ck-medan + .ck-baris { margin-top: 14px; }
        .ck-medan label { font-size: .84rem; font-weight: 700; color: var(--ck-ink); margin: 0; }
        .ck-page .form-control {
            width: 100%; border: 1.5px solid #e7ebf0; border-radius: 12px; padding: 11px 14px;
            font-size: .92rem; color: var(--ck-ink); background: #fff;
            transition: border-color .16s ease, box-shadow .16s ease;
        }
        .ck-page .form-control::placeholder { color: #aeb6c0; }
        .ck-page .form-control:focus {
            outline: 0; border-color: #f26522; box-shadow: 0 0 0 3px rgba(242, 101, 34, .13);
        }
        .ck-page .form-control[readonly], .ck-page .form-control:disabled { background: #f8fafc; color: #64748b; cursor: not-allowed; }
        .ck-page textarea.form-control { resize: vertical; min-height: 88px; }
        .ck-page .form-control.is-invalid { border-color: #f87171; }

        .ck-err { display: flex; align-items: center; gap: 5px; font-size: .78rem; font-weight: 600; color: #dc2626; }
        .ck-err::before { content: "\F33A"; font-family: "bootstrap-icons"; line-height: 1; }
        .ck-temu { display: inline-flex; align-items: center; gap: 6px; font-size: .78rem; font-weight: 700; color: #16a34a; }
        .ck-temu i.bi, .ck-temu i.bi::before { display: block; line-height: 1; }
        .ck-nota { display: flex; align-items: flex-start; gap: 7px; font-size: .78rem; color: var(--ck-muted); line-height: 1.55; margin: 0; }
        .ck-nota i.bi { flex-shrink: 0; margin-top: 2px; font-size: .82rem; color: color-mix(in srgb, var(--c, #f26522) 75%, #0f172a); }
        .ck-nota i.bi::before { display: block; line-height: 1; }
        .ck-nota b { color: var(--ck-ink); font-weight: 700; }

        /* ===== Tombol ===== */
        .ck-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px; width: 100%; height: 46px;
            padding: 0 14px; border: 1.5px solid transparent; border-radius: 12px; cursor: pointer;
            font-family: var(--ck-font); font-weight: 700; font-size: .88rem; white-space: nowrap;
            transition: filter .16s ease, transform .16s ease, background .16s ease, border-color .16s ease, color .16s ease;
        }
        /* Isi tombol dibungkus <span> oleh wire:loading. Span polos bukan flex,
           jadi ikon yang display:block memaksa ganti baris dan ikonnya berdiri
           DI ATAS teksnya — bukan di sampingnya. */
        .ck-btn > span { display: inline-flex; align-items: center; justify-content: center; gap: 7px; }
        .ck-btn i.bi, .ck-btn i.bi::before { display: block; line-height: 1; font-size: .95rem; }
        .ck-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; filter: none; }
        .ck-btn-utama { background: linear-gradient(135deg, #fba919, #f26522); color: #fff; box-shadow: 0 10px 20px -12px rgba(242, 101, 34, .8); }
        .ck-btn-utama:not(:disabled):hover { filter: brightness(1.05); transform: translateY(-1px); }
        .ck-btn-garis { background: #fff; border-color: #e5e0d8; color: var(--ck-ink); }
        .ck-btn-garis:not(:disabled):hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }
        /* Menghapus promo itu tindakan merusak: merahnya terlihat tanpa perlu
           disentuh dulu. */
        .ck-btn-bahaya { background: #fff5f5; border-color: #fecaca; color: #dc2626; }
        .ck-btn-bahaya:not(:disabled):hover { background: #fee2e2; border-color: #f87171; transform: translateY(-1px); }

        .ck-pasangan { display: grid; grid-template-columns: minmax(0, 1fr) 148px; gap: 10px; align-items: start; }

        /* ===== Kabar & promo terpakai ===== */
        .ck-kabar {
            display: flex; align-items: flex-start; gap: 8px; margin-top: 12px;
            padding: 10px 12px; border-radius: 12px; border: 1px solid transparent;
            font-size: .82rem; font-weight: 600; line-height: 1.5;
        }
        .ck-kabar i.bi { flex-shrink: 0; margin-top: 1px; }
        .ck-kabar i.bi::before { display: block; line-height: 1; }
        .ck-kabar.is-ok { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .ck-kabar.is-galat { background: #fff5f5; border-color: #fecaca; color: #b91c1c; }
        .ck-kabar.is-ingat { background: #fffbeb; border-color: #fde68a; color: #b45309; }

        .ck-terpakai {
            display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 10px;
            padding: 11px 13px; border-radius: 13px;
            background: linear-gradient(135deg, #fffaf4, #fff); border: 1px solid #fde3cc;
        }
        .ck-terpakai-kiri { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .ck-terpakai-nama {
            display: inline-flex; align-items: center; gap: 6px;
            font-family: var(--ck-font); font-weight: 700; font-size: .87rem; color: var(--ck-ink);
        }
        .ck-terpakai-nama i.bi, .ck-terpakai-nama i.bi::before { display: block; line-height: 1; color: #f26522; }
        .ck-terpakai-kode {
            display: inline-flex; align-items: center; height: 21px; padding: 0 7px; border-radius: 6px;
            background: #fff1e7; color: #c2410c; font-size: .72rem; font-weight: 700; font-family: ui-monospace, monospace;
        }
        .ck-terpakai-jumlah { font-size: .75rem; color: var(--ck-muted); }
        .ck-terpakai-sub { font-size: .76rem; color: var(--ck-muted); }
        .ck-terpakai-nilai { font-family: var(--ck-font); font-weight: 800; font-size: .95rem; color: #16a34a; white-space: nowrap; }

        /* ===== Ajakan member & poin ===== */
        .ck-member-teks { margin: 0 0 12px; font-size: .87rem; color: #4b5563; line-height: 1.7; }
        .ck-member-teks b { color: var(--ck-ink); font-weight: 700; }
        .ck-member-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 44px;
            border-radius: 12px; text-decoration: none;
            background: color-mix(in srgb, var(--c) 10%, #fff);
            border: 1.5px solid color-mix(in srgb, var(--c) 28%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a);
            font-family: var(--ck-font); font-weight: 700; font-size: .87rem;
            transition: background .16s ease, transform .16s ease;
        }
        .ck-member-btn:hover { background: color-mix(in srgb, var(--c) 16%, #fff); color: color-mix(in srgb, var(--c) 88%, #0f172a); transform: translateY(-1px); }
        .ck-member-btn i.bi, .ck-member-btn i.bi::before { display: block; line-height: 1; }

        .ck-saklar {
            display: flex; align-items: flex-start; gap: 11px; padding: 12px;
            border: 1px solid var(--ck-line); border-radius: 13px; background: #fcfcfd;
        }
        /* Bootstrap menggambar tuas lewat .form-switch, dan .form-switch
           membawa serta display:block + padding-left miliknya sendiri — dua
           hal yang merusak baris flex ini. Tuasnya dipakai, tata letaknya
           diambil alih di sini. Dibiarkan sebagai kotak centang biasa,
           role="switch" pada input jadi janji yang tidak ditepati: pembaca
           layar menyebutnya tuas sementara mata melihat kotak. */
        .ck-saklar.form-switch { display: flex; padding-left: 0; }
        .ck-saklar .form-check-input {
            flex-shrink: 0; margin-top: 1px; margin-left: 0; float: none;
            width: 40px; height: 22px; cursor: pointer;
        }
        .ck-saklar .form-check-input:checked { background-color: #f26522; border-color: #f26522; }
        .ck-saklar .form-check-input:focus { border-color: #f9b98f; box-shadow: 0 0 0 3px rgba(242, 101, 34, .13); }
        .ck-saklar label { margin: 0; }
        .ck-saklar strong { font-family: var(--ck-font); font-weight: 700; font-size: .89rem; color: var(--ck-ink); }
        .ck-saklar-ket { font-size: .8rem; color: var(--ck-muted); margin-top: 2px; }
        .ck-saklar-ket b { color: var(--ck-ink); font-weight: 700; }

        /* ===== Ringkasan ===== */
        .ck-sisi { position: sticky; top: 92px; }
        .ck-ringkas { background: #fff; border: 1px solid var(--ck-line); border-radius: 18px; overflow: hidden; }
        .ck-item {
            display: grid; grid-template-columns: 36px minmax(0, 1fr) auto; align-items: center; gap: 11px;
            padding: 11px 0;
        }
        .ck-item + .ck-item { border-top: 1px dashed var(--ck-line); }
        /* Ubin kategori yang sama dengan baris Keranjang, supaya pembeli
           mengenali barangnya tanpa membaca ulang namanya. */
        .ck-item-ubin {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 11px; font-size: .92rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .ck-item-ubin i.bi, .ck-item-ubin i.bi::before { display: block; line-height: 1; }
        .ck-item-nama { font-family: var(--ck-font); font-weight: 700; font-size: .87rem; color: var(--ck-ink); line-height: 1.35; }
        .ck-item-ket { font-size: .76rem; color: var(--ck-muted); margin-top: 1px; }
        .ck-item-harga { font-family: var(--ck-font); font-weight: 700; font-size: .87rem; color: var(--ck-ink); white-space: nowrap; }

        .ck-garis { height: 1px; margin: 12px 0; background: repeating-linear-gradient(to right, var(--ck-line) 0 6px, transparent 6px 12px); }
        .ck-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 6px 0; font-size: .86rem; color: var(--ck-muted); }
        .ck-row strong { font-family: var(--ck-font); font-weight: 700; color: var(--ck-ink); }
        .ck-row i.bi { font-size: .78rem; color: #b4bcc6; }
        .ck-row.is-potong strong { color: #16a34a; }

        .ck-hemat {
            display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 10px;
            padding: 10px 13px; border-radius: 12px; background: #f0fdf4; border: 1px solid #bbf7d0;
            font-size: .84rem; font-weight: 700; color: #15803d;
        }
        .ck-hemat strong { font-family: var(--ck-font); font-weight: 800; }
        .ck-coret { margin-top: 6px; text-align: right; font-size: .82rem; color: #9aa3af; text-decoration: line-through; }

        .ck-total { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 14px 0 16px; }
        .ck-total span { font-family: var(--ck-font); font-weight: 700; font-size: .95rem; color: var(--ck-ink); }
        .ck-total strong {
            font-family: var(--ck-font); font-weight: 800; font-size: 1.5rem; letter-spacing: -.02em;
            background: linear-gradient(135deg, #fba919, #f26522); -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .ck-bayar {
            display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%;
            height: 54px; padding: 0 18px; border: 0; border-radius: 14px; cursor: pointer;
            background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            font-family: var(--ck-font); font-weight: 800; font-size: .97rem;
            box-shadow: 0 14px 26px -14px rgba(242, 101, 34, .85);
            transition: filter .16s ease, transform .16s ease;
        }
        .ck-bayar:not(:disabled):hover { filter: brightness(1.05); transform: translateY(-1px); }
        .ck-bayar:disabled { opacity: .7; cursor: wait; }
        .ck-bayar span { display: inline-flex; align-items: center; gap: 8px; }
        .ck-bayar i.bi, .ck-bayar i.bi::before { display: block; line-height: 1; font-size: .95rem; }
        .ck-bayar-harga { padding: 3px 10px; border-radius: 8px; background: rgba(255, 255, 255, .22); font-size: .9rem; }

        @media (max-width: 991.98px) {
            .ck-tata { grid-template-columns: minmax(0, 1fr); }
            /* Ringkasan berhenti menempel: di satu kolom ia sudah berada di
               bawah formulirnya, dan yang menempel justru menutupi isinya. */
            .ck-sisi { position: static; }
        }
        @media (max-width: 575.98px) {
            .ck-baris { grid-template-columns: minmax(0, 1fr); }
            .ck-pasangan { grid-template-columns: minmax(0, 1fr); }
            .ck-jalur { gap: 4px; }
            .ck-henti-teks { font-size: .68rem; }
            .ck-jalur::before { left: 12.5%; right: 12.5%; }
            .ck-isi { padding: 15px; }
            .ck-total strong { font-size: 1.35rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ck-btn, .ck-bayar, .ck-member-btn, .ck-page .form-control { transition: none; }
            .ck-btn:hover, .ck-bayar:hover, .ck-member-btn:hover { transform: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-bag-check-fill"></i> Checkout</span>
                <h1>Selesaikan Pesanan</h1>
                <p>Lengkapi data &amp; pilih promo Anda, lalu lanjutkan ke pembayaran.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li><a href="{{ route('cart') }}">Keranjang</a></li>
                    <li class="current">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="ck-sec">
        <div class="container">
            {{-- Empat perhentian; yang ketiga & keempat belum dilewati, jadi
                 dibiarkan kelabu. Menjawab "masih berapa langkah lagi?" di
                 tempat pertanyaan itu paling menentukan. --}}
            <div class="ck-jalur">
                <div class="ck-henti is-lewat">
                    <span class="ck-henti-bulat"><i class="bi bi-check-lg"></i></span>
                    <span class="ck-henti-teks">Keranjang</span>
                </div>
                <div class="ck-henti is-kini">
                    <span class="ck-henti-bulat"><i class="bi bi-person-fill"></i></span>
                    <span class="ck-henti-teks">Data &amp; Promo</span>
                </div>
                <div class="ck-henti">
                    <span class="ck-henti-bulat"><i class="bi bi-credit-card-2-front-fill"></i></span>
                    <span class="ck-henti-teks">Bayar</span>
                </div>
                <div class="ck-henti">
                    <span class="ck-henti-bulat"><i class="bi bi-inbox-fill"></i></span>
                    <span class="ck-henti-teks">Terima</span>
                </div>
            </div>

            <form wire:submit="checkout">
                <div class="ck-tata">
                    {{-- Kolom kiri: data & promo --}}
                    <div class="ck-kolom">
                        {{-- Informasi pelanggan --}}
                        <div class="ck-kartu" style="--c: #2563eb">
                            <div class="ck-kepala">
                                <span class="ck-ubin"><i class="bi bi-person-fill"></i></span> Informasi Pelanggan
                            </div>
                            <div class="ck-isi">
                                <div class="ck-medan">
                                    <label>Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                    <div wire:ignore>
                                        <input type="tel" id="co-phone" class="form-control" autocomplete="tel"
                                            placeholder="812 3456 789" data-init="{{ $no_hp }}">
                                    </div>
                                    {{-- jembatan nilai E.164 ke Livewire (untuk lookup pelanggan) --}}
                                    <input type="hidden" id="co-phone-e164" wire:model="no_hp">
                                    @if ($isLoadingCustomer)
                                        <span class="ck-temu" style="color: #6b7280;"><span class="spinner-border spinner-border-sm"></span> Mencari data...</span>
                                    @endif
                                    @error('no_hp') <span class="ck-err">{{ $message }}</span> @enderror
                                    @if ($customerFound)
                                        <span class="ck-temu"><i class="bi bi-check-circle-fill"></i> Data pelanggan ditemukan</span>
                                    @endif
                                    <p class="ck-nota"><i class="bi bi-globe-americas"></i> <span>Nomor Indonesia cukup ketik <b>08…</b> (otomatis +62). Untuk luar negeri, pilih bendera negaranya.</span></p>
                                </div>

                                <div class="ck-baris">
                                    <div class="ck-medan">
                                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                            wire:model="nama" placeholder="Nama lengkap Anda" {{ $customerFound ? 'readonly' : '' }}>
                                        @error('nama') <span class="ck-err">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="ck-medan">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            wire:model="email" placeholder="email@contoh.com" {{ $customerFound ? 'readonly' : '' }}
                                            x-on:blur="if ($event.target.value.includes('@')) $wire.saveAbandonedCart($event.target.value)">
                                        @error('email') <span class="ck-err">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="ck-medan">
                                    <label>Catatan <span class="ck-opsional">(opsional)</span></label>
                                    <textarea class="form-control" wire:model="customer_notes" rows="3"
                                        placeholder="Catatan tambahan untuk pesanan Anda"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Kode promo --}}
                        <div class="ck-kartu" style="--c: #d97706">
                            <div class="ck-kepala">
                                <span class="ck-ubin"><i class="bi bi-tag-fill"></i></span> Kode Promo
                            </div>
                            <div class="ck-isi">
                                <div class="ck-pasangan">
                                    <div>
                                        <input type="text" class="form-control @error('kodePromo') is-invalid @enderror"
                                            wire:model="kodePromo"
                                            placeholder="{{ $promoBlokirGabung ? 'Tidak bisa digabung' : 'Masukkan kode promo (opsional)' }}"
                                            @if ($promoValid || $promoBlokirGabung) disabled @endif>
                                        @error('kodePromo') <span class="ck-err">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        @if ($promoValid)
                                            <button type="button" class="ck-btn ck-btn-bahaya" wire:click="removePromo">
                                                <i class="bi bi-trash3-fill"></i> Hapus
                                            </button>
                                        @else
                                            <button type="button" class="ck-btn ck-btn-utama" wire:click="checkPromo"
                                                wire:loading.attr="disabled" wire:target="checkPromo"
                                                @if ($promoBlokirGabung) disabled @endif>
                                                <span wire:loading.remove wire:target="checkPromo"><i class="bi bi-check-circle-fill"></i> Pakai</span>
                                                <span wire:loading wire:target="checkPromo"><span class="spinner-border spinner-border-sm"></span></span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if ($promoBlokirGabung)
                                    <div class="ck-kabar is-ingat">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>Promo <b>{{ $promoBlokirGabung }}</b> yang sedang aktif tidak bisa digabung dengan promo lain.</span>
                                    </div>
                                @elseif ($promoMessage)
                                    <div class="ck-kabar {{ $promoValid ? 'is-ok' : 'is-galat' }}">
                                        <i class="bi {{ $promoValid ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                        <span>{{ $promoMessage }}</span>
                                    </div>
                                @endif

                                @if (!empty($appliedPromos))
                                    @php
                                        // Flash Sale/promo otomatis diterapkan per produk, jadi bisa muncul
                                        // berkali-kali. Gabungkan per promo agar rapi: satu kartu, total diskon,
                                        // dan jumlah produk yang terkena.
                                        $groupedPromos = [];
                                        foreach ($appliedPromos as $p) {
                                            $key = $p['promo_id'] ?? ($p['kode_promo'] ?? $p['nama_promo']);
                                            if (!isset($groupedPromos[$key])) {
                                                $groupedPromos[$key] = $p;
                                                $groupedPromos[$key]['items_count'] = 1;
                                            } else {
                                                $groupedPromos[$key]['jumlah_diskon'] += $p['jumlah_diskon'];
                                                $groupedPromos[$key]['items_count']++;
                                            }
                                        }
                                    @endphp
                                    @foreach ($groupedPromos as $promo)
                                        <div class="ck-terpakai">
                                            <div class="ck-terpakai-kiri">
                                                <span class="ck-terpakai-nama">
                                                    @if (($promo['tipe_promo'] ?? '') === 'flash_sale')
                                                        <i class="bi bi-lightning-charge-fill"></i>
                                                    @endif
                                                    {{ $promo['nama_promo'] }}
                                                    @if ($promo['kode_promo'])
                                                        <code class="ck-terpakai-kode">{{ $promo['kode_promo'] }}</code>
                                                    @endif
                                                    @if (($promo['items_count'] ?? 1) > 1)
                                                        <span class="ck-terpakai-jumlah">× {{ $promo['items_count'] }} produk</span>
                                                    @endif
                                                </span>
                                                <span class="ck-terpakai-sub">
                                                    Diskon
                                                    @if ($promo['tipe_diskon'] === 'persen')
                                                        {{ $promo['nilai_diskon'] }}%{{ ($promo['items_count'] ?? 1) > 1 ? ' / produk' : '' }}
                                                    @else
                                                        Rp {{ number_format($promo['nilai_diskon'], 0, ',', '.') }}{{ ($promo['items_count'] ?? 1) > 1 ? ' / produk' : '' }}
                                                    @endif
                                                </span>
                                            </div>
                                            <span class="ck-terpakai-nilai">− Rp {{ number_format($promo['jumlah_diskon'], 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- Kode referral --}}
                        @if ($showReferralInput)
                            <div class="ck-kartu" style="--c: #0d9488">
                                <div class="ck-kepala">
                                    <span class="ck-ubin"><i class="bi bi-people-fill"></i></span> Kode Referral
                                    <span class="ck-opsional">(opsional)</span>
                                </div>
                                <div class="ck-isi">
                                    <div class="ck-pasangan">
                                        <div>
                                            <input type="text" class="form-control" wire:model="referralCode"
                                                placeholder="Kode referral" maxlength="9" style="text-transform: uppercase;"
                                                {{ $referralValid ? 'readonly' : '' }}
                                                @if($promoBlokirReferral) disabled @endif>
                                        </div>
                                        <div>
                                            @if (!$referralValid)
                                                <button class="ck-btn ck-btn-garis" type="button" wire:click="checkReferralCode"
                                                    wire:loading.attr="disabled" wire:target="checkReferralCode">
                                                    <span wire:loading.remove wire:target="checkReferralCode"><i class="bi bi-check-circle"></i> Cek</span>
                                                    <span wire:loading wire:target="checkReferralCode"><span class="spinner-border spinner-border-sm"></span></span>
                                                </button>
                                            @else
                                                <button class="ck-btn ck-btn-utama" type="button" disabled>
                                                    <i class="bi bi-check-circle-fill"></i> Valid
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($referralMessage)
                                        <div class="ck-kabar {{ $referralValid ? 'is-ok' : 'is-galat' }}">
                                            <i class="bi {{ $referralValid ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                            <span>{{ $referralMessage }}</span>
                                        </div>
                                    @endif
                                    @if($promoBlokirReferral)
                                        <div class="ck-kabar is-ingat">
                                            <i class="bi bi-info-circle-fill"></i>
                                            <span>Kode referral tidak bisa dipakai bersama promo <b>{{ $promoBlokirReferral }}</b> yang sedang aktif.</span>
                                        </div>
                                    @else
                                        <p class="ck-nota" style="margin-top: 12px;"><i class="bi bi-info-circle"></i> <span>Punya kode referral dari teman? Masukkan untuk keuntungan bersama!</span></p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Ajakan jadi member — hanya untuk yang BELUM member.
                             Kebalikan dari card Poin di bawah, jadi keduanya tidak
                             pernah muncul bersamaan. Pembeli baru (belum ada datanya)
                             juga belum member, jadi ikut melihat ajakan ini. --}}
                        @if (! $foundCustomer || $foundCustomer->status_member !== 'active')
                            <div class="ck-kartu" style="--c: #7c3aed">
                                <div class="ck-kepala">
                                    <span class="ck-ubin"><i class="bi bi-stars"></i></span> Kamu Belum Jadi Member
                                </div>
                                <div class="ck-isi">
                                    <p class="ck-member-teks">
                                        Sayang banget 😢 — padahal tiap belanja <b>Rp 50.000</b> bisa jadi
                                        <b>1 poin</b>, dan poinnya bikin belanja berikutnya
                                        <b>lebih murah</b>. Gratis, lho!
                                    </p>
                                    <a href="{{ route('member.info') }}" class="ck-member-btn">
                                        <i class="bi bi-gift"></i> Lihat Syarat &amp; Keuntungannya
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- Poin member --}}
                        @if ($showPointsOption)
                            <div class="ck-kartu" style="--c: #db2777">
                                <div class="ck-kepala">
                                    <span class="ck-ubin"><i class="bi bi-star-fill"></i></span> Poin Member
                                </div>
                                <div class="ck-isi">
                                    <div class="ck-saklar form-switch" @if($promoBlokirPoin) style="opacity:.55;" @endif>
                                        <input class="form-check-input" type="checkbox" id="usePoints" role="switch"
                                            wire:model.live="usePoints" @if($promoBlokirPoin) disabled @endif>
                                        <label class="form-check-label" for="usePoints" style="cursor:{{ $promoBlokirPoin ? 'not-allowed' : 'pointer' }};">
                                            <strong>Gunakan Poin Member</strong>
                                            <div class="ck-saklar-ket">
                                                Anda punya <b>{{ number_format($availablePoints, 0, ',', '.') }} poin</b>
                                                (senilai Rp {{ number_format($pointsValue, 0, ',', '.') }})
                                            </div>
                                        </label>
                                    </div>
                                    @if($promoBlokirPoin)
                                        <div class="ck-kabar is-ingat">
                                            <i class="bi bi-info-circle-fill"></i>
                                            <span>Poin tidak bisa dipakai bersama promo <b>{{ $promoBlokirPoin }}</b> yang sedang aktif.</span>
                                        </div>
                                    @endif
                                    @if ($pointsExpireLabel)
                                        <p class="ck-nota" style="margin-top: 12px; color: #b45309;"><i class="bi bi-clock-history" style="color: #b45309;"></i> <span>Poin kadaluarsa pada <b style="color: #b45309;">{{ $pointsExpireLabel }}</b></span></p>
                                    @endif
                                    @if ($usePoints)
                                        <div class="ck-kabar is-ok">
                                            <i class="bi bi-info-circle-fill"></i>
                                            <span>Poin akan digunakan untuk mengurangi total pembayaran.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Kolom kanan: ringkasan --}}
                    <aside class="ck-sisi">
                        <div class="ck-ringkas" style="--c: #f26522">
                            <div class="ck-kepala">
                                <span class="ck-ubin"><i class="bi bi-receipt"></i></span> Ringkasan Pesanan
                            </div>
                            <div class="ck-isi">
                                @foreach ($cart as $item)
                                    @php
                                        // Warna & ikon dari KATEGORI produk — taksonomi yang sama
                                        // dengan Keranjang, Shop, dan beranda.
                                        $isPaket = ($item['type'] ?? '') === 'bundling';
                                        $katCk = $isPaket ? null : \App\Support\KategoriBeranda::untukProduk($item['product_name'] ?? '');
                                        $warnaCk = $isPaket ? '#f26522' : ($katCk['warna'] ?? '#f26522');
                                        $ikonCk = $isPaket ? 'bi-box2-heart-fill' : ($katCk['ikon'] ?? 'bi-box-seam');
                                    @endphp
                                    <div class="ck-item" style="--c: {{ $warnaCk }}">
                                        <span class="ck-item-ubin"><i class="bi {{ $ikonCk }}"></i></span>
                                        <div>
                                            <div class="ck-item-nama">{{ $item['product_name'] }}</div>
                                            <div class="ck-item-ket">
                                                @if ($isPaket)
                                                    Paket Bundling
                                                @else
                                                    {{ $item['duration_value'] }} {{ ucfirst($item['duration_type']) }}
                                                @endif
                                                &times;{{ $item['quantity'] }}
                                            </div>
                                        </div>
                                        <span class="ck-item-harga">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach

                                <div class="ck-garis"></div>

                                <div class="ck-row"><span>Subtotal</span><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div>

                                @if ($uniqueCode > 0)
                                    <div class="ck-row">
                                        <span>Kode Unik <i class="bi bi-info-circle" title="Untuk verifikasi pembayaran"></i></span>
                                        <strong>+ Rp {{ number_format($uniqueCode, 0, ',', '.') }}</strong>
                                    </div>
                                @endif
                                @if ($promoDiscount > 0)
                                    <div class="ck-row is-potong"><span>Diskon Promo</span><strong>− Rp {{ number_format($promoDiscount, 0, ',', '.') }}</strong></div>
                                @endif
                                @if ($referralDiscount > 0)
                                    <div class="ck-row is-potong"><span>Diskon Referral</span><strong>− Rp {{ number_format($referralDiscount, 0, ',', '.') }}</strong></div>
                                @endif
                                @if ($pointsDiscount > 0)
                                    <div class="ck-row is-potong"><span>Diskon Poin</span><strong>− Rp {{ number_format($pointsDiscount, 0, ',', '.') }}</strong></div>
                                @endif

                                @if ($totalDiscount > 0)
                                    <div class="ck-hemat">
                                        <span><i class="bi bi-piggy-bank-fill"></i> Total Hemat</span>
                                        <strong>Rp {{ number_format($totalDiscount, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="ck-coret">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                                @endif

                                <div class="ck-total">
                                    <span>Total Pembayaran</span>
                                    <strong>Rp {{ number_format($finalTotal, 0, ',', '.') }}</strong>
                                </div>

                                <button type="button" wire:click="checkout" class="ck-bayar"
                                    wire:loading.attr="disabled" wire:target="checkout">
                                    <span wire:loading.remove wire:target="checkout">
                                        <i class="bi bi-lock-fill"></i>
                                        {{ $finalTotal > 0 ? 'Bayar Sekarang' : 'Selesaikan Pesanan' }}
                                    </span>
                                    @if ($finalTotal > 0)
                                        <span wire:loading.remove wire:target="checkout" class="ck-bayar-harga">Rp {{ number_format($finalTotal, 0, ',', '.') }}</span>
                                    @endif
                                    <span wire:loading wire:target="checkout"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                                </button>

                                <p class="ck-nota" style="margin-top: 12px;"><i class="bi bi-shield-check" style="color: #16a34a;"></i> <span>Transaksi aman — Transfer Bank &amp; QRIS.</span></p>
                            </div>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </section>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
        <style>
            /* Penyelarasan intl-tel-input dengan isian di halaman ini. Pemilihnya
               memakai .ck-page (dulu .co-card) — kelasnya berganti bersama
               penataan ulang, dan aturan ini harus ikut, kalau tidak kotak
               nomornya kembali ke rupa bawaan pustakanya. */
            .ck-page .iti { width: 100%; display: block; }
            .ck-page #co-phone { border: 1.5px solid #e7ebf0; border-radius: 12px; padding: 11px 14px; font-size: .92rem; width: 100%; }
            .ck-page #co-phone:focus { border-color: #f26522; box-shadow: 0 0 0 3px rgba(242, 101, 34, .13); }
            .ck-page .iti--separate-dial-code .iti__selected-flag { background-color: #fff7f0; border-radius: 11px 0 0 11px; border-right: 1px solid #e7ebf0; padding: 0 10px 0 12px; }
            .ck-page .iti__selected-flag:hover { background-color: #ffe6cf; }
            .ck-page .iti--separate-dial-code .iti__selected-dial-code { color: #1c1f26; font-weight: 700; font-size: .9rem; margin-left: 8px; }
            .ck-page .iti__arrow { border-top-color: #f26522; margin-left: 8px; }
            .ck-page .iti__country-list { border: 1px solid #eceff3; border-radius: 14px; box-shadow: 0 16px 44px rgba(35, 39, 47, .16); font-size: .9rem; padding: 6px; }
            .ck-page .iti__country { padding: 8px 10px; border-radius: 9px; }
            .ck-page .iti__country.iti__highlight { background-color: #fff7f0; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
        <script>
            (function () {
                function syncCoPhone() {
                    var input = document.querySelector('#co-phone');
                    var hidden = document.querySelector('#co-phone-e164');
                    if (!input || !hidden) return;
                    var iti = input._iti;
                    var num = '';
                    if (iti) {
                        try { num = iti.getNumber() || ''; } catch (e) {}
                        if (!num) {
                            try {
                                var dc = (iti.getSelectedCountryData() || {}).dialCode || '';
                                var local = (input.value || '').replace(/\D/g, '').replace(/^0+/, '');
                                num = local ? ('+' + dc + local) : '';
                            } catch (e) {}
                        }
                    } else {
                        num = (input.value || '').trim();
                    }
                    hidden.value = num;
                    hidden.dispatchEvent(new Event('input', { bubbles: true }));
                }

                function initCoPhone() {
                    var input = document.querySelector('#co-phone');
                    if (!input || input.dataset.itiInit || typeof window.intlTelInput === 'undefined') return;
                    input.dataset.itiInit = '1';
                    var iti;
                    try {
                        iti = window.intlTelInput(input, {
                            initialCountry: 'id',
                            preferredCountries: ['id', 'my', 'sg', 'us', 'sa', 'ae', 'gb', 'au'],
                            separateDialCode: true,
                            autoPlaceholder: 'aggressive',
                            utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js'
                        });
                    } catch (e) { return; }
                    input._iti = iti;

                    var initVal = input.getAttribute('data-init');
                    if (initVal) {
                        try { iti.setNumber(initVal); } catch (e) {}
                    } else {
                        fetch('https://ipapi.co/json/').then(function (r) { return r.json(); })
                            .then(function (d) { if (d && d.country_code && !input.value) { try { iti.setCountry(String(d.country_code).toLowerCase()); } catch (e) {} } })
                            .catch(function () {});
                    }

                    // Setiap ketikan: perbarui nilai input tersembunyi (wire:model deferred),
                    // jadi nomor ikut terkirim BERSAMA aksi checkout. Murni lokal (tanpa request).
                    input.addEventListener('input', syncCoPhone);
                    input.addEventListener('countrychange', syncCoPhone);

                    // Saat meninggalkan kolom nomor: sinkron + cari pelanggan lama (auto-isi
                    // nama & email). Aman karena tombol "Bayar" kini pakai wire:target="checkout",
                    // jadi request pencarian ini TIDAK menonaktifkan tombolnya.
                    input.addEventListener('blur', function () {
                        syncCoPhone();
                        var root = input.closest('[wire\\:id]');
                        var hidden = document.querySelector('#co-phone-e164');
                        if (!window.Livewire || !root || !hidden || hidden.value.length < 10) return;
                        var comp = window.Livewire.find(root.getAttribute('wire:id'));
                        if (comp && typeof comp.set === 'function') {
                            comp.set('no_hp', hidden.value); // live → updatedNoHp() → auto-isi
                        }
                    });
                }

                document.addEventListener('livewire:init', initCoPhone);
                document.addEventListener('livewire:navigated', initCoPhone);
                window.addEventListener('load', initCoPhone);
            })();
        </script>
    @endpush
    {{-- Meta Pixel: InitiateCheckout.
         Nilainya memakai subtotal DIKURANGI diskon — angka yang benar-benar akan
         dibayar pembeli, bukan harga sebelum promo. Kalau memakai subtotal mentah,
         nilai konversi di laporan iklan akan selalu lebih besar dari uang yang
         sungguh masuk. --}}
    <script>
        (function () {
            if (typeof fbq !== 'function') return;
            fbq('track', 'InitiateCheckout', {
                content_ids: @json(collect($cart)->pluck('product_id')->map(fn ($v) => (string) $v)->values()),
                content_type: 'product',
                num_items: @json(count($cart)),
                value: @json(max(0, (float) $subtotal - (float) $totalDiscount)),
                currency: 'IDR'
            });
        })();
    </script>
</div>
