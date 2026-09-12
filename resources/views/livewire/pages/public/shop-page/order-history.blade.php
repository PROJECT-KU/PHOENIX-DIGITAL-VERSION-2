<div>
    <style>
        /* ===== Riwayat Pesanan =====
           Bahasa visual sama dengan Beranda, Shop, Bundling, dan halaman lain
           yang sudah ditata: kartu putih bersudut 18px dengan warna per kartu
           (--c), ubin ikon berwarna, dan sapuan warna tipis di pojok.
           Kelas rw-*: aturan .oh-* ada di public-custom-styles.css yang beku
           di server, jadi tampilan ini tidak bergantung padanya. */
        .rw-page { --rw-ink: #1c1f26; --rw-muted: #64748b; --rw-line: #eceff3; --rw-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .rw-sec { padding: 22px 0 64px; }

        /* Ubin ikon — glif tunggal selalu display:block + line-height:1 */
        .rw-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 42px; height: 42px; border-radius: 13px; font-size: 1.1rem;
            background: color-mix(in srgb, var(--c) 13%, #fff); color: color-mix(in srgb, var(--c) 85%, #0f172a);
        }
        .rw-ubin.is-kecil { width: 32px; height: 32px; border-radius: 10px; font-size: .9rem; }
        .rw-ubin i.bi, .rw-ubin i.bi::before { display: block; line-height: 1; }

        /* ===== Bilah atas: catatan + tombol pulihkan ===== */
        .rw-bar {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px 20px;
            padding: 16px 18px; margin-bottom: 18px; border-radius: 18px;
            background: linear-gradient(135deg, color-mix(in srgb, var(--c) 8%, #fff) 0%, #fff 70%);
            border: 1px solid color-mix(in srgb, var(--c) 20%, #eceff4);
        }
        .rw-bar-kiri { display: flex; align-items: center; gap: 13px; flex: 1 1 420px; min-width: 0; }
        .rw-bar-teks b { display: block; font-family: var(--rw-font); font-weight: 800; font-size: .95rem; color: var(--rw-ink); }
        .rw-bar-teks span { display: block; margin-top: 2px; font-size: .84rem; line-height: 1.55; color: var(--rw-muted); }
        .rw-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 44px; padding: 0 18px;
            border-radius: 13px; border: 1.5px solid #e5e0d8; background: #fff; color: var(--rw-ink);
            font-weight: 700; font-size: .87rem; text-decoration: none; white-space: nowrap; cursor: pointer;
            transition: border-color .18s ease, color .18s ease, transform .18s ease, background .18s ease;
        }
        .rw-btn:hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }
        .rw-btn.is-utama {
            border-color: transparent; background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            box-shadow: 0 12px 22px -12px rgba(242, 101, 34, .8);
        }
        .rw-btn.is-utama:hover { color: #fff; filter: brightness(1.05); }
        .rw-btn i.bi, .rw-btn i.bi::before { display: block; line-height: 1; font-size: 1rem; }

        .rw-jumlah { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; font-size: .85rem; color: var(--rw-muted); }
        .rw-jumlah b { color: var(--rw-ink); font-weight: 800; }
        .rw-jumlah i.bi, .rw-jumlah i.bi::before { display: block; line-height: 1; }

        /* ===== Kartu pesanan (akordeon) ===== */
        .rw-list { display: grid; gap: 12px; }
        .rw-order {
            position: relative; overflow: hidden; background: #fff; border: 1px solid var(--rw-line); border-radius: 18px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .rw-order:hover { border-color: color-mix(in srgb, var(--c) 32%, #fff); }
        .rw-order[open] {
            border-color: color-mix(in srgb, var(--c) 38%, #fff);
            box-shadow: 0 18px 36px -30px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .rw-kepala {
            display: flex; align-items: center; gap: 14px; padding: 15px 18px; cursor: pointer; list-style: none;
        }
        .rw-kepala::-webkit-details-marker { display: none; }
        .rw-kepala:hover .rw-ubin, .rw-order[open] .rw-ubin { background: var(--c); color: #fff; }
        .rw-kepala-isi { display: flex; flex-direction: column; gap: 3px; min-width: 0; flex: 1 1 auto; }
        .rw-nomor { font-family: var(--rw-font); font-weight: 800; font-size: .95rem; color: var(--rw-ink); line-height: 1.3; }
        .rw-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 4px 10px; font-size: .78rem; color: var(--rw-muted); }
        .rw-meta i.bi, .rw-meta i.bi::before { display: inline-block; line-height: 1; }
        .rw-kanan { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
        .rw-status {
            display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 11px; border-radius: 99px;
            background: color-mix(in srgb, var(--c) 13%, #fff); color: color-mix(in srgb, var(--c) 62%, #0f172a);
            font-size: .73rem; font-weight: 800; white-space: nowrap;
        }
        .rw-status i.bi, .rw-status i.bi::before { display: block; line-height: 1; font-size: .75rem; }
        .rw-total { font-family: var(--rw-font); font-weight: 800; font-size: 1rem; color: var(--rw-ink); white-space: nowrap; }
        .rw-panah {
            flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px;
            border-radius: 50%; background: #f8fafc; color: #64748b; font-size: .78rem;
            transition: transform .25s ease, background .2s ease, color .2s ease;
        }
        .rw-panah i.bi, .rw-panah i.bi::before { display: block; line-height: 1; }
        .rw-order[open] .rw-panah { transform: rotate(180deg); background: color-mix(in srgb, var(--c) 13%, #fff); color: color-mix(in srgb, var(--c) 70%, #0f172a); }

        /* Lencana peringatan masa aktif */
        .rw-tanda {
            display: inline-flex; align-items: center; gap: 6px; height: 24px; padding: 0 10px; border-radius: 99px;
            font-size: .72rem; font-weight: 800; white-space: nowrap;
        }
        .rw-tanda i.bi, .rw-tanda i.bi::before { display: block; line-height: 1; font-size: .72rem; }
        .rw-tanda.is-habis { background: #fef2f2; color: #b91c1c; }
        .rw-tanda.is-campur { background: #fff7ed; color: #b45309; }
        .rw-tanda.is-segera { background: #fffbeb; color: #a16207; }
        .rw-tanda.is-aktif { background: #f0fdf4; color: #15803d; }
        .rw-tanda.is-tunggu { background: #f8fafc; color: #475569; }

        /* ===== Isi pesanan ===== */
        .rw-isi { padding: 0 18px 18px; }
        .rw-baris {
            display: flex; align-items: center; gap: 13px; padding: 13px 0;
            border-top: 1px dashed #e8ecf2;
        }
        .rw-baris-isi { display: flex; flex-direction: column; gap: 5px; min-width: 0; flex: 1 1 auto; }
        .rw-produk { font-family: var(--rw-font); font-weight: 700; font-size: .9rem; color: var(--rw-ink); line-height: 1.35; }
        .rw-baris-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 6px 10px; font-size: .77rem; color: var(--rw-muted); }
        .rw-baris-meta i.bi, .rw-baris-meta i.bi::before { display: inline-block; line-height: 1; }
        .rw-durasi {
            display: inline-flex; align-items: center; height: 22px; padding: 0 9px; border-radius: 99px;
            background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 700; white-space: nowrap;
        }
        .rw-harga { flex-shrink: 0; font-family: var(--rw-font); font-weight: 800; font-size: .9rem; color: var(--rw-ink); white-space: nowrap; }

        /* Ringkasan biaya */
        .rw-struk { margin-top: 14px; padding: 14px 16px; border-radius: 14px; background: #fcfcfd; border: 1px solid var(--rw-line); }
        .rw-struk-baris { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: .85rem; color: #475569; padding: 4px 0; }
        .rw-struk-baris.is-hijau { color: #15803d; }
        .rw-struk-baris.is-akhir {
            margin-top: 8px; padding-top: 10px; border-top: 1px dashed #e2e8f0;
            font-family: var(--rw-font); font-weight: 800; font-size: .98rem; color: var(--rw-ink);
        }
        .rw-struk-baris.is-akhir b { color: #ea580c; font-size: 1.08rem; }

        /* Promo */
        .rw-promo { margin-top: 14px; padding: 14px 16px; border-radius: 14px; background: #fffdf7; border: 1px solid #fde68a; }
        .rw-promo-judul { display: flex; align-items: center; gap: 9px; margin-bottom: 10px; font-family: var(--rw-font); font-weight: 800; font-size: .87rem; color: #92400e; }
        .rw-promo-judul i.bi, .rw-promo-judul i.bi::before { display: block; line-height: 1; }
        .rw-promo-item { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 10px; padding: 7px 0; font-size: .83rem; color: #475569; }
        .rw-promo-item + .rw-promo-item { border-top: 1px dashed #fde68a; }
        .rw-promo-tag {
            display: inline-flex; align-items: center; gap: 6px; height: 24px; padding: 0 10px; border-radius: 99px;
            background: color-mix(in srgb, var(--p) 14%, #fff); color: color-mix(in srgb, var(--p) 60%, #0f172a);
            font-size: .71rem; font-weight: 800; white-space: nowrap;
        }
        .rw-promo-tag i.bi, .rw-promo-tag i.bi::before { display: block; line-height: 1; font-size: .72rem; }
        .rw-promo-nama { flex: 1 1 auto; min-width: 0; }
        .rw-kode {
            display: inline-block; padding: 1px 7px; border-radius: 6px; background: #fff7ed; border: 1px dashed #fcd34d;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .76rem; color: #92400e;
        }
        .rw-promo-amt { flex-shrink: 0; font-weight: 800; color: #15803d; white-space: nowrap; }

        /* ===== Jendela pemulihan ===== */
        .rw-modal .modal-content { border: 0; border-radius: 24px; overflow: hidden; box-shadow: 0 40px 80px -40px rgba(15, 23, 42, .6); }
        .rw-modal-kepala {
            position: relative; display: flex; align-items: flex-start; gap: 14px; padding: 22px 24px 20px;
            background:
                radial-gradient(70% 130% at 100% 0%, rgba(251, 169, 25, .18), transparent 62%),
                linear-gradient(150deg, #fff7ef 0%, #fff 72%);
            border-bottom: 1px solid #f7e7d7;
        }
        .rw-modal-teks { min-width: 0; flex: 1 1 auto; padding-right: 30px; }
        .rw-modal-label {
            display: inline-flex; align-items: center; height: 22px; padding: 0 10px; border-radius: 99px;
            background: #fff1e4; color: #c2410c; font-size: .68rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
        }
        .rw-modal-kepala h5 { margin: 8px 0 4px; font-family: var(--rw-font); font-weight: 800; font-size: 1.15rem; color: var(--rw-ink); }
        .rw-modal-kepala p { margin: 0; font-size: .85rem; line-height: 1.6; color: var(--rw-muted); }
        .rw-modal-tutup {
            position: absolute; top: 16px; right: 16px; display: flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border: 0; border-radius: 50%; background: rgba(255, 255, 255, .8); color: #64748b;
            cursor: pointer; transition: background .18s ease, color .18s ease;
        }
        .rw-modal-tutup:hover { background: #fee2e2; color: #dc2626; }
        .rw-modal-tutup i.bi, .rw-modal-tutup i.bi::before { display: block; line-height: 1; font-size: .85rem; }
        .rw-modal-isi { padding: 20px 24px 24px; }
        .rw-medan + .rw-medan { margin-top: 14px; }
        .rw-label { display: flex; align-items: center; gap: 7px; margin-bottom: 7px; font-size: .83rem; font-weight: 700; color: #334155; }
        .rw-wajib, .rw-opsional {
            display: inline-flex; align-items: center; height: 19px; padding: 0 7px; border-radius: 99px;
            font-size: .66rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
        }
        .rw-wajib { background: #fef2f2; color: #b91c1c; }
        .rw-opsional { background: #f1f5f9; color: #475569; }
        .rw-kolom { position: relative; display: flex; align-items: center; }
        .rw-kolom > i.bi {
            position: absolute; left: 14px; display: block; line-height: 1; font-size: .95rem; color: #94a3b8; pointer-events: none;
        }
        .rw-kolom .form-control {
            width: 100%; height: 50px; padding: 0 14px 0 40px; border: 1.5px solid #e8ecf2; border-radius: 13px;
            background: #fff; color: #0f172a; font-size: .92rem; box-shadow: none;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .rw-kolom .form-control::placeholder { color: #94a3b8; }
        .rw-kolom .form-control:focus { border-color: #f26522; box-shadow: 0 0 0 4px rgba(242, 101, 34, .14); }
        .rw-kolom .form-control.is-invalid { border-color: #dc2626; background-image: none; }
        .rw-galat { display: block; margin-top: 6px; font-size: .78rem; font-weight: 600; color: #dc2626; }
        .rw-catatan {
            display: flex; align-items: flex-start; gap: 11px; margin-top: 16px; padding: 12px 14px; border-radius: 14px;
            background: color-mix(in srgb, var(--c) 7%, #fff); border: 1px solid color-mix(in srgb, var(--c) 20%, #eceff4);
            font-size: .82rem; line-height: 1.6; color: #475569;
        }
        .rw-catatan b { color: #334155; }
        .rw-kirim {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 50px;
            margin-top: 18px; border: 0; border-radius: 14px; cursor: pointer;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .95rem; box-shadow: 0 14px 26px -14px rgba(242, 101, 34, .85);
            transition: filter .16s ease, transform .16s ease;
        }
        .rw-kirim:hover:not(:disabled) { filter: brightness(1.05); transform: translateY(-1px); }
        .rw-kirim:disabled { opacity: .7; cursor: progress; }
        .rw-kirim i.bi, .rw-kirim i.bi::before { display: block; line-height: 1; }
        .rw-privasi {
            display: flex; align-items: center; justify-content: center; gap: 7px; margin: 12px 0 0;
            /* #94a3b8 hanya 2,6 : 1 — terlalu pucat untuk teks sekecil ini. */
            font-size: .76rem; color: #64748b; text-align: center;
        }
        .rw-privasi i.bi, .rw-privasi i.bi::before { display: block; line-height: 1; font-size: .8rem; }

        @media (max-width: 767.98px) {
            .rw-kepala { flex-wrap: wrap; gap: 10px; padding: 14px; }
            .rw-kepala-isi { flex: 1 1 60%; }
            .rw-kanan { width: 100%; justify-content: space-between; }
            .rw-isi { padding: 0 14px 14px; }
            .rw-baris { align-items: flex-start; }
            .rw-bar { padding: 14px; }
            .rw-bar .rw-btn { width: 100%; }
            .rw-modal-kepala { padding: 18px 18px 16px; }
            .rw-modal-isi { padding: 16px 18px 20px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .rw-btn:hover, .rw-panah { transition: none; transform: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-clock-history"></i> Riwayat</span>
                <h1>Riwayat Pesanan</h1>
                <p>Semua pesanan Anda tampil di sini. Ganti perangkat? Pulihkan lewat Nomor HP.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Toko</a></li>
                    <li class="current">Riwayat Pesanan</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="rw-sec rw-page">
        <div class="container">
            <div class="rw-bar" style="--c: #2563eb">
                <div class="rw-bar-kiri">
                    <span class="rw-ubin"><i class="bi bi-info-circle-fill"></i></span>
                    <div class="rw-bar-teks">
                        <b>Riwayat tersimpan di perangkat ini</b>
                        <span>Pindah perangkat, ganti browser, atau hapus cookie? Pulihkan lewat <b>Nomor HP</b>.</span>
                    </div>
                </div>
                <button type="button" class="rw-btn" data-bs-toggle="modal" data-bs-target="#restoreModal">
                    <i class="bi bi-arrow-repeat"></i> Pulihkan Riwayat
                </button>
            </div>

            @if($this->myOrders->total() > 0)
            <p class="rw-jumlah"><i class="bi bi-receipt"></i> <b>{{ $this->myOrders->total() }} pesanan</b> ditemukan</p>

            <div class="rw-list">
                @foreach($this->myOrders as $order)
                @php
                    // Warna & label status pesanan — satu tempat, dipakai ubin
                    // ikon, lencana, dan sapuan kartunya.
                    [$warnaOrder, $ikonOrder, $labelOrder] = match ($order->status) {
                        'paid' => ['#16a34a', 'bi-check-circle-fill', 'Lunas'],
                        'completed' => ['#0d9488', 'bi-patch-check-fill', 'Selesai'],
                        'pending' => ['#d97706', 'bi-hourglass-split', 'Menunggu Pembayaran'],
                        'cancelled' => ['#e11d48', 'bi-x-circle-fill', 'Dibatalkan'],
                        default => ['#64748b', 'bi-receipt', ucfirst((string) $order->status)],
                    };
                    $totalAcc = $order->items->count();
                    $habisCount = $order->items->filter(fn ($i) => $i->isHabis())->count();
                    $soonCount = $order->items->filter(fn ($i) => ! $i->isHabis() && $i->end_date && $i->isExpiringSoon())->count();
                    // Ditulis tanpa tanda lebih-besar: tanda itu sesudah direktif blok
                    // membuat Livewire melewati penanda morph-nya.
                    $semuaHabis = $habisCount !== 0 && $habisCount === $totalAcc;
                    $sebagianHabis = $habisCount !== 0 && ! $semuaHabis;
                    $adaSegera = $soonCount !== 0;
                    $adaDiskon = 0.0 !== (float) $order->total_discount;
                    $adaKodeUnik = 0 !== (int) $order->unique_code;
                @endphp
                <details class="rw-order" style="--c: {{ $warnaOrder }}">
                    <summary class="rw-kepala">
                        <span class="rw-ubin"><i class="bi {{ $ikonOrder }}"></i></span>
                        <span class="rw-kepala-isi">
                            <span class="rw-nomor">{{ $order->order_number }}</span>
                            <span class="rw-meta">
                                <span><i class="bi bi-calendar-event"></i> {{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                                <span><i class="bi bi-box-seam"></i> {{ $totalAcc }} produk</span>
                            </span>
                            @if ($semuaHabis)
                                <span class="rw-tanda is-habis"><i class="bi bi-x-circle-fill"></i> {{ $totalAcc > 1 ? 'Semua Akun Habis' : 'Akun Habis' }}</span>
                            @elseif ($sebagianHabis)
                                <span class="rw-tanda is-campur"><i class="bi bi-exclamation-triangle-fill"></i> {{ $habisCount }}/{{ $totalAcc }} Akun Habis</span>
                            @elseif ($adaSegera)
                                <span class="rw-tanda is-segera"><i class="bi bi-clock-fill"></i> Segera Berakhir</span>
                            @endif
                        </span>
                        <span class="rw-kanan">
                            <span class="rw-status"><i class="bi {{ $ikonOrder }}"></i> {{ $labelOrder }}</span>
                            <span class="rw-total">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                            <span class="rw-panah"><i class="bi bi-chevron-down"></i></span>
                        </span>
                    </summary>

                    <div class="rw-isi">
                        @foreach($order->items as $item)
                        @php
                            $habisItem = $item->isHabis();
                            $segeraItem = ! $habisItem && $item->end_date && $item->isExpiringSoon();
                            [$warnaItem, $ikonItem, $tandaItem, $kelasItem] = $habisItem
                                ? ['#e11d48', 'bi-x-circle-fill', 'Habis', 'is-habis']
                                : ($segeraItem
                                    ? ['#d97706', 'bi-clock-fill', $item->getRemainingLabel(), 'is-segera']
                                    : ($item->end_date
                                        ? ['#16a34a', 'bi-check-circle-fill', 'Aktif · '.$item->getRemainingLabel(), 'is-aktif']
                                        : ['#64748b', 'bi-hourglass-split', 'Menunggu aktivasi', 'is-tunggu']));
                        @endphp
                        <div class="rw-baris" style="--c: {{ $warnaItem }}">
                            <span class="rw-ubin is-kecil"><i class="bi {{ $ikonItem }}"></i></span>
                            <span class="rw-baris-isi">
                                <span class="rw-produk">{{ $item->product_name }}</span>
                                <span class="rw-baris-meta">
                                    <span class="rw-tanda {{ $kelasItem }}"><i class="bi {{ $ikonItem }}"></i> {{ $tandaItem }}</span>
                                    <span class="rw-durasi">{{ $item->duration_value }} {{ ucfirst($item->duration_type) }}</span>
                                    @if ($item->end_date)
                                        <span><i class="bi bi-calendar-event"></i>
                                            {{ $habisItem ? 'Berakhir' : 'Berlaku s.d.' }}
                                            {{ \Illuminate\Support\Carbon::parse($item->end_date)->translatedFormat('d M Y') }}</span>
                                    @endif
                                </span>
                            </span>
                            <span class="rw-harga">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @endforeach

                        <div class="rw-struk">
                            <div class="rw-struk-baris"><span>Subtotal</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                            @if ($adaDiskon)
                                <div class="rw-struk-baris is-hijau"><span>Diskon</span><span>− Rp {{ number_format($order->total_discount, 0, ',', '.') }}</span></div>
                            @endif
                            @if ($adaKodeUnik)
                                <div class="rw-struk-baris"><span>Kode Unik</span><span>+ Rp {{ number_format($order->unique_code, 0, ',', '.') }}</span></div>
                            @endif
                            <div class="rw-struk-baris is-akhir"><span>Total Bayar</span><b>Rp {{ number_format($order->total, 0, ',', '.') }}</b></div>
                        </div>

                        @php
                            $promos = collect($order->applied_promos ?? []);
                            $hasReferral = ! empty($order->referral_code) || (float) $order->referral_discount > 0;
                            $hasPoints = (int) ($order->used_points ?? 0) > 0 || (float) ($order->points_discount ?? 0) > 0;
                            $hasAnyPromo = $promos->isNotEmpty() || $hasReferral || $hasPoints;
                        @endphp

                        @if ($hasAnyPromo)
                        <div class="rw-promo">
                            <div class="rw-promo-judul"><i class="bi bi-ticket-perforated-fill"></i> Promo Digunakan</div>
                            @foreach ($promos as $p)
                            @php
                                [$labelPromo, $warnaPromo, $ikonPromo] = match ($p['tipe_promo'] ?? '') {
                                    'flash_sale' => ['Flash Sale', '#e11d48', 'bi-lightning-charge-fill'],
                                    'kode_promo' => ['Kode Promo', '#2563eb', 'bi-tag-fill'],
                                    'auto_promo' => ['Promo Otomatis', '#7c3aed', 'bi-magic'],
                                    default => ['Promo', '#d97706', 'bi-gift-fill'],
                                };
                            @endphp
                            <div class="rw-promo-item">
                                <span class="rw-promo-tag" style="--p: {{ $warnaPromo }}"><i class="bi {{ $ikonPromo }}"></i> {{ $labelPromo }}</span>
                                <span class="rw-promo-nama">
                                    {{ $p['nama_promo'] ?? '-' }}
                                    @if (! empty($p['kode_promo']))
                                        <code class="rw-kode">{{ $p['kode_promo'] }}</code>
                                    @endif
                                </span>
                                @if (! empty($p['jumlah_diskon']))
                                    <span class="rw-promo-amt">− Rp {{ number_format($p['jumlah_diskon'], 0, ',', '.') }}</span>
                                @endif
                            </div>
                            @endforeach

                            @if ($hasReferral)
                            <div class="rw-promo-item">
                                <span class="rw-promo-tag" style="--p: #0d9488"><i class="bi bi-people-fill"></i> Referral</span>
                                <span class="rw-promo-nama">
                                    @if ($order->referral_code)
                                        <code class="rw-kode">{{ $order->referral_code }}</code>
                                    @endif
                                </span>
                                @if ((float) $order->referral_discount > 0)
                                    <span class="rw-promo-amt">− Rp {{ number_format($order->referral_discount, 0, ',', '.') }}</span>
                                @endif
                            </div>
                            @endif

                            @if ($hasPoints)
                            <div class="rw-promo-item">
                                <span class="rw-promo-tag" style="--p: #d97706"><i class="bi bi-star-fill"></i> Poin</span>
                                <span class="rw-promo-nama">{{ number_format((int) $order->used_points, 0, ',', '.') }} poin</span>
                                @if ((float) ($order->points_discount ?? 0) > 0)
                                    <span class="rw-promo-amt">− Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </details>
                @endforeach
            </div>

            @if($this->myOrders->hasPages())
            <div class="mt-4 ph-pagination">
                {{ $this->myOrders->links('pagination.ph') }}
            </div>
            @endif
            @else
        <div class="ph-empty py-4">
            <div class="ph-empty-art">
                <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                    aria-label="Belum ada riwayat pesanan">
                    <defs>
                        <radialGradient id="peGlowH" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                            <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                        </radialGradient>
                        <linearGradient id="peDoc" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#fbc25a" />
                            <stop offset="100%" stop-color="#f26522" />
                        </linearGradient>
                    </defs>
                    <ellipse class="pe-glow" cx="120" cy="108" rx="78" ry="78" fill="url(#peGlowH)" />
                    <ellipse class="pe-shadow" cx="120" cy="184" rx="54" ry="8" fill="#e15a18" />

                    <g transform="translate(46,66)"><path class="pe-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                    <g transform="translate(198,80)"><path class="pe-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                    <g transform="translate(56,150)"><path class="pe-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                    <g class="pe-float">
                        <path d="M80 50 Q80 44 86 44 H154 Q160 44 160 50 V148 L150 142 L140 148 L130 142 L120 148 L110 142 L100 148 L90 142 L80 148 Z"
                            fill="#ffffff" stroke="url(#peDoc)" stroke-width="2.5" stroke-linejoin="round" />
                        <rect x="96" y="58" width="48" height="9" rx="4" fill="url(#peDoc)" />
                        <rect x="96" y="79" width="48" height="5" rx="2.5" fill="#f1e6d8" />
                        <rect x="96" y="90" width="34" height="5" rx="2.5" fill="#f1e6d8" />
                        <rect x="96" y="105" width="48" height="5" rx="2.5" fill="#f1e6d8" />
                        <rect x="96" y="116" width="28" height="5" rx="2.5" fill="#f1e6d8" />
                    </g>

                    <g class="pe-float-2">
                        <circle cx="164" cy="126" r="21" fill="#ffffff" stroke="url(#peDoc)" stroke-width="3" />
                        <path d="M164 126 V114 M164 126 L173 130" stroke="#f26522" stroke-width="3"
                            stroke-linecap="round" />
                        <circle cx="164" cy="126" r="2.6" fill="#f26522" />
                    </g>
                </svg>
            </div>
            <h3 class="ph-empty-title">Belum ada riwayat pesanan</h3>
            <p class="ph-empty-sub">Pesanan Anda akan muncul di sini secara otomatis. Memesan lewat perangkat lain?
                Gunakan <strong>Pulihkan Riwayat</strong>.</p>
            <div class="ph-empty-actions">
                <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Mulai Belanja</a>
                <button type="button" class="ph-empty-btn ghost" data-bs-toggle="modal" data-bs-target="#restoreModal">
                    <i class="bi bi-arrow-repeat"></i> Pulihkan Riwayat
                </button>
            </div>
        </div>
            @endif

        {{-- Jendela pemulihan. Kelas rw-modal-*: gaya .restore-modal ada di
             public-custom-styles.css yang beku di server. Penanda yang dipakai
             skrip DIPERTAHANKAN: id restoreModal, wire:submit restoreSession,
             serta wire:model phoneNumber & invoiceCode. --}}
        <div wire:ignore.self class="modal fade rw-modal" id="restoreModal" tabindex="-1" aria-labelledby="rwModalJudul">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="rw-modal-kepala">
                        <span class="rw-ubin is-padat" style="--c: #f26522"><i class="bi bi-arrow-repeat"></i></span>
                        <div class="rw-modal-teks">
                            <span class="rw-modal-label">Pulihkan</span>
                            <h5 id="rwModalJudul">Pulihkan Riwayat Pesanan</h5>
                            <p>Tampilkan pesanan Anda di perangkat ini memakai Nomor HP yang dipakai saat memesan.</p>
                        </div>
                        <button type="button" class="rw-modal-tutup" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="rw-modal-isi">
                        <form wire:submit.prevent="restoreSession">
                            <div class="rw-medan">
                                <label class="rw-label" for="rw-hp">Nomor WhatsApp <span class="rw-wajib">wajib</span></label>
                                <div class="rw-kolom">
                                    <i class="bi bi-whatsapp"></i>
                                    <input type="number" id="rw-hp" wire:model="phoneNumber"
                                        class="form-control {{ $errors->has('phoneNumber') ? 'is-invalid' : '' }}"
                                        placeholder="0821*********" inputmode="numeric">
                                </div>
                                @error('phoneNumber') <span class="rw-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="rw-medan">
                                <label class="rw-label" for="rw-kode">Kode Pesanan <span class="rw-opsional">opsional</span></label>
                                <div class="rw-kolom">
                                    <i class="bi bi-receipt"></i>
                                    <input type="text" id="rw-kode" wire:model="invoiceCode"
                                        class="form-control {{ $errors->has('invoiceCode') ? 'is-invalid' : '' }}"
                                        placeholder="Kosongkan untuk melihat semua pesanan">
                                </div>
                                @error('invoiceCode') <span class="rw-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="rw-catatan" style="--c: #2563eb">
                                <span class="rw-ubin is-kecil"><i class="bi bi-info-circle-fill"></i></span>
                                <span>Isi <b>Nomor HP saja</b> untuk melihat <b>semua riwayat</b> pesanan Anda.
                                    Tambahkan <b>Kode Pesanan</b> bila ingin menampilkan <b>satu pesanan</b> tertentu.</span>
                            </div>

                            <button type="submit" class="rw-kirim" wire:loading.attr="disabled" wire:target="restoreSession">
                                <span wire:loading.remove wire:target="restoreSession"><i class="bi bi-arrow-repeat"></i> Tampilkan Riwayat</span>
                                <span wire:loading wire:target="restoreSession"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                            </button>

                            <p class="rw-privasi"><i class="bi bi-shield-lock-fill"></i> Nomor Anda hanya dipakai untuk mencocokkan pesanan di perangkat ini.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
</div>

@script
<script>
    const restoreModalEl = document.getElementById('restoreModal');
    const restoreModal = new bootstrap.Modal(restoreModalEl);

    $wire.on('restore-success', (data) => {
        const modalInstance = bootstrap.Modal.getInstance(restoreModalEl);
        if (modalInstance) modalInstance.hide();

        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true,
            html:
                '<div class="ph-toast">' +
                  '<span class="ph-toast-ic"><i class="bi bi-clock-history"></i></span>' +
                  '<div class="ph-toast-txt">' +
                    '<strong>Berhasil</strong>' +
                    '<span>' + (data[0].message || 'Riwayat pesanan ditampilkan.') + '</span>' +
                  '</div>' +
                '</div>',
            customClass: { popup: 'ph-toast-popup' },
            didClose: () => { window.location.reload(); }
        });
    });
</script>

@endscript
