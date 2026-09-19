
@section('title')
Proses Pesanan || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @php
        // Dibaca sekali: dua kondisi "session()->has(...)" dalam satu berkas
        // membuat Livewire salah memasang penanda morph pada yang kedua.
        $pesanGalat = session('error');
        $pesanInfo = session('info');
    @endphp
    <div class="dsb pt-proses">
    <style>
        /* Tombol pemicu picker (mirip form-select) */
        .pa-picker-btn {
            cursor: pointer;
            position: relative;
        }

        .pa-picker-btn::after {
            content: "\F282";
            font-family: "bootstrap-icons";
            position: absolute;
            right: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .9rem;
        }

        /* Daftar pilihan di dalam SweetAlert */
        .pa-pick-list {
            max-height: 320px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: .35rem;
            text-align: left;
        }

        .pa-pick-item {
            width: 100%;
            border: 1px solid #eef0f6;
            background: linear-gradient(135deg, #ffffff, #f8f9ff);
            border-radius: 12px;
            padding: .7rem .9rem;
            font-size: .92rem;
            color: #1e293b;
            font-weight: 600;
            text-align: left;
            transition: all .15s ease;
        }

        .pa-pick-item:hover {
            border-color: #c7c3ff;
            background: linear-gradient(135deg, #f1f0ff, #e9e7ff);
            transform: translateY(-1px);
        }

        .pa-pick-item .pa-sub {
            display: block;
            font-size: .78rem;
            font-weight: 500;
            color: #94a3b8;
            margin-top: 2px;
        }

        .pa-pick-empty {
            padding: 1.25rem;
            text-align: center;
            color: #94a3b8;
        }
    </style>
    <header class="dsb-hero">
        <div class="dsb-hero-teks">
            <h1 class="dsb-salam">Proses Pesanan</h1>
            <p class="dsb-hero-ket">
                <span class="d-block"><i class="bi bi-receipt me-1"></i>{{ $order->order_number }} · {{ $order->customer->nama ?? 'Pelanggan' }}</span>
                <span class="d-block">Pilih akun, atur masa berlangganan, lalu kirim ke pelanggan.</span>
            </p>
        </div>
        <div class="dsb-hero-aksi">
            <a wire:navigate href="{{ route('admin.pesanantoko.detail', $order) }}" class="dsb-tombol is-lembut">
                <i class="bi bi-arrow-left"></i><span>Detail Pesanan</span>
            </a>
        </div>
    </header>

    <style>
        .proc-section {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.95));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }

        .proc-section-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 14px rgba(78, 70, 229, 0.35);
        }

        /* Pusatkan ikon Bootstrap (bi) yang punya line-height bawaan */
        .proc-section-icon i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .proc-section-icon i.bi::before {
            display: block;
            line-height: 1;
        }

        .proc-section-icon.icon-green {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 6px 14px rgba(16, 185, 129, 0.35);
        }

        .proc-section-icon.icon-amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 6px 14px rgba(217, 119, 6, 0.35);
        }

        .order-summary-card {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.95));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }

        .order-summary-card .summary-stat {
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.06), rgba(78, 70, 229, 0.04));
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: .85rem;
            padding: .85rem 1rem;
            height: 100%;
        }

        .order-summary-card .summary-stat .label {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6b7280;
        }

        .order-summary-card .summary-stat .value {
            font-size: 1.02rem;
            font-weight: 700;
            margin-top: .15rem;
            color: #1e293b;
        }

        .proc-section .form-label {
            font-weight: 600;
            color: #475569;
            font-size: .9rem;
        }

        /* Kotak Total Masa Aktif */
        .total-aktif {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: .6rem .9rem;
            background: #f8fafc;
            min-height: 58px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .total-aktif.is-active {
            border-style: solid;
            border-color: rgba(16, 185, 129, 0.35);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.10), rgba(5, 150, 105, 0.06));
        }

        .total-aktif .ta-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            margin-bottom: .25rem;
        }

        .total-aktif.is-active .ta-label {
            color: #059669;
        }

        .total-aktif .ta-value {
            display: flex;
            align-items: center;
            gap: .4rem;
            flex-wrap: wrap;
            font-weight: 700;
            color: #0f172a;
            font-size: .95rem;
        }

        .total-aktif .ta-plus {
            color: #94a3b8;
            font-weight: 700;
        }

        .total-aktif .ta-bonus {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            padding: .12rem .5rem;
            border-radius: 999px;
            font-size: .82rem;
        }

        .total-aktif .ta-empty {
            font-size: .82rem;
            color: #94a3b8;
        }

        /* Kartu pilih Ebook Bonus (glossy) */
        .ebook-pick {
            position: relative;
            display: flex;
            align-items: center;
            gap: .85rem;
            height: 100%;
            padding: .85rem 1rem;
            padding-right: 2.2rem;
            border-radius: 14px;
            border: 1.5px solid #e6e8f2;
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            cursor: pointer;
            transition: all .2s ease;
        }

        .ebook-pick input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .ebook-pick:hover {
            border-color: rgba(108, 99, 255, 0.40);
            background: rgba(108, 99, 255, 0.05);
            transform: translateY(-1px);
        }

        .ebook-pick:has(input:checked) {
            border-color: #6c63ff;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.12), rgba(78, 70, 229, 0.05));
            box-shadow: 0 8px 18px rgba(78, 70, 229, 0.16);
        }

        .ebook-pick .ep-icon {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            background: #eef0ff;
            color: #4e46e5;
            transition: all .2s ease;
        }

        /* Ikon buku terlihat turun & agak ke kiri di dalam kotaknya.
           Penyebabnya bukan .ep-icon (itu sudah flex + center), melainkan
           Bootstrap Icons sendiri: .bi::before memakai `vertical-align: -.125em`
           yang menggeser glif ke bawah.
           Perbaikannya MENYALIN pola yang sudah dipakai .proc-section-icon di
           berkas ini juga (ikon kepala seksi) — kotak ebook cuma terlewat. */
        .ebook-pick .ep-icon i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }

        .ebook-pick .ep-icon i.bi::before {
            display: block;
            line-height: 1;
            vertical-align: 0;
        }

        .ebook-pick:has(input:checked) .ep-icon {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            color: #fff;
            box-shadow: 0 6px 14px rgba(78, 70, 229, 0.35);
        }

        .ebook-pick .ep-body {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .ebook-pick .ep-title {
            font-weight: 700;
            font-size: .92rem;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ebook-pick .ep-desc {
            font-size: .78rem;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ebook-pick .ep-check {
            position: absolute;
            top: .7rem;
            right: .75rem;
            font-size: 1.1rem;
            color: #d3d8e4;
            transition: all .2s ease;
        }

        .ebook-pick:has(input:checked) .ep-check {
            color: #4e46e5;
            transform: scale(1.1);
        }

        .ebook-empty {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: 1rem 1.1rem;
            border-radius: 14px;
            border: 1.5px dashed #cbd5e1;
            background: #f8fafc;
            color: #64748b;
        }

        .ebook-empty i {
            font-size: 1.6rem;
            color: #f59e0b;
        }

        /* ============ Penyesuaian MOBILE (≤576px) ============ */
        @media (max-width: 575.98px) {
            /* Judul & breadcrumb ringkas — breadcrumb PAKSA 1 baris (geser). */
            h3.gradient-text { font-size: 1.2rem !important; line-height: 1.25 !important; }
            .breadcrumb-custom { overflow: hidden; max-width: 100%; }
            .breadcrumb-custom .breadcrumb {
                flex-wrap: nowrap;
                overflow-x: auto;
                white-space: nowrap;
                font-size: .72rem;
                margin-bottom: 0;
                scrollbar-width: none;
                -webkit-overflow-scrolling: touch;
            }
            .breadcrumb-custom .breadcrumb::-webkit-scrollbar { display: none; }
            .breadcrumb-custom .breadcrumb-item { white-space: nowrap; }

            /* Card INV & header section: ikon SEJAJAR teks; nomor INV/judul muat. */
            .order-summary-card > .d-flex.align-items-center,
            .proc-section > .d-flex.align-items-center { align-items: center; }
            .proc-section-icon { align-self: center; }
            .order-summary-card > .d-flex.align-items-center h4 { font-size: 1.05rem; min-width: 0; word-break: break-word; line-height: 1.2; }
            .proc-section > .d-flex.align-items-center h5 { font-size: 1rem; min-width: 0; }

            /* Kartu section lebih ringkas di HP. */
            .order-summary-card, .proc-section { padding: 1.15rem !important; }

            /* Selector akun terpilih: teks 1 baris + ellipsis — cegah nama akun
               tumpang tindih dengan username saat sudah dipilih. */
            .pa-picker-btn { display: flex; align-items: center; height: auto; min-height: 46px; }
            #paAccountLabel {
                flex: 1;
                min-width: 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        /* ===== Kulit dasbor ===== */
        /* Layout admin (templateindex) memaksa .form-control berpadding kiri
           45px, tinggi 48px, dan bingkai 2px — semuanya !important — sehingga
           isian tanpa ikon menjorok dan textarea terjepit. Ditimpa di sini
           dengan pemilih yang lebih kuat. */
        .pt-proses .form-control, .pt-proses .form-select {
            padding: 9px 13px !important; height: auto !important; min-height: 42px;
            border: 1px solid #e9edf3 !important; border-radius: 11px !important;
        }
        .pt-proses .form-select { padding-right: 32px !important; }
        .pt-proses textarea.form-control { min-height: 84px; }
        /* Pilihan ebook bonus: kartu bersih seragam dasbor. */
        .pp-ebook-deret { display: grid; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); }
        .pt-proses .ebook-pick {
            display: flex; align-items: center; gap: 12px; height: 100%; padding: 12px 14px; margin: 0;
            border-radius: 14px !important; border: 1.5px solid #e9edf3 !important; background: #fff !important;
            box-shadow: none !important; transform: none !important; cursor: pointer;
        }
        .pt-proses .ebook-pick:hover { border-color: #c4b5fd !important; }
        .pt-proses .ebook-pick:has(input:checked) { border-color: #7c3aed !important; background: #f5f3ff !important; }
        .pt-proses .ebook-pick.is-bawaan { border-style: dashed !important; }
        .pt-proses .ebook-pick.is-bawaan:has(input:checked) { border-style: solid !important; }
        .pt-proses .ebook-pick .ep-icon { width: 38px !important; height: 38px !important; flex: 0 0 38px; border-radius: 11px !important; background: #ede9fe !important; color: #7c3aed !important; box-shadow: none !important; }
        .pt-proses .ebook-pick:has(input:checked) .ep-icon { background: #7c3aed !important; color: #fff !important; }
        .pt-proses .ebook-pick .ep-body { flex: 1 1 auto; min-width: 0; }
        .pt-proses .ebook-pick .ep-title { display: block; font-size: .86rem; font-weight: 700; color: #1c1f26; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pt-proses .ebook-pick .ep-desc { display: block; font-size: .74rem; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pt-proses .ebook-pick .ep-bawaan { display: inline-flex; align-items: center; gap: 4px; margin-top: 2px; font-size: .7rem; font-weight: 800; color: #c2410c; }
        .pp-ebook-info { display: flex; gap: 8px; align-items: flex-start; margin-bottom: 10px; padding: 9px 12px; border-radius: 12px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; font-size: .8rem; line-height: 1.5; }
        .pp-templat { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 2px 0 10px; }
        .pp-templat-judul { font-size: .74rem; font-weight: 700; color: #94a3b8; display: inline-flex; align-items: center; gap: 4px; }
        .pp-templat-judul i { color: #f59e0b; }
        .pp-templat-btn {
            display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px;
            border: 1px solid #ddd6fe; background: #f5f3ff; color: #6d28d9; font-size: .78rem; font-weight: 700;
            cursor: pointer; transition: background .15s ease, color .15s ease;
        }
        .pp-templat-btn:hover:not(:disabled) { background: #7c3aed; color: #fff; }
        .pp-templat-btn.is-dipakai { border-color: #bbf7d0; background: #f0fdf4; color: #15803d; cursor: default; }
        .pt-proses form .btn-danger, .pt-proses .pp-tombol .btn-danger {
            background: #fff !important; color: #dc2626 !important; border: 1px solid #fecaca !important;
        }
        .pt-proses form .btn-danger:hover, .pt-proses .pp-tombol .btn-danger:hover { background: #dc2626 !important; color: #fff !important; }

        .pt-proses-nilai { font-size: clamp(1.05rem, 2vw, 1.3rem) !important; }
        .pt-proses .proc-section {
            background: #fff !important; border: 1px solid #e9edf3 !important; border-radius: 18px !important; box-shadow: none !important;
        }
        .pt-proses .proc-section > .d-flex:first-child { padding-bottom: 14px; margin-bottom: 18px !important; border-bottom: 1px solid #f1f5f9; }
        .pt-proses .proc-section h5 { font-size: .98rem; font-weight: 800; color: #1c1f26; }
        .pt-proses .proc-section-icon {
            --c: #7c3aed; width: 42px; height: 42px; box-shadow: none !important; color: var(--c) !important;
            background: color-mix(in srgb, var(--c) 12%, #fff) !important;
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
        }
        .pt-proses .proc-section-icon.icon-green { --c: #16a34a; }
        .pt-proses .proc-section-icon.icon-amber { --c: #d97706; }
        .pt-proses .proc-section-icon.icon-rose, .pt-proses .proc-section-icon.icon-red { --c: #e11d48; }
        .pt-proses .proc-section-icon.icon-blue { --c: #0284c7; }
        .pt-proses .form-control, .pt-proses .form-select {
            min-height: 42px; border: 1px solid #e9edf3; border-radius: 11px; font-size: .88rem; box-shadow: none;
        }
        .pt-proses .form-control:focus, .pt-proses .form-select:focus { border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(124, 58, 237, .14); }
        .pt-proses .proc-section .alert-info { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .pt-proses form > .border-top { border-color: #eef2f7 !important; }
        .pt-proses form .btn-primary { background: #7c3aed; border: 0; border-radius: 14px; box-shadow: 0 10px 22px rgba(124, 58, 237, .28); font-weight: 800; }
        .pt-proses form .btn-primary:hover { background: #6d28d9; }
        .pt-proses form .btn-danger { background: #fff; color: #dc2626; border: 1px solid #fecaca; border-radius: 14px; font-weight: 700; }
        .pt-proses form .btn-danger:hover { background: #dc2626; color: #fff; }
        .pt-proses .pa-picker-btn::after { content: none; }
        /* ===== Tata letak proses: form + panel ringkasan ===== */
        .pp-tata { display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr); align-items: start; }
        @media (min-width: 1200px) {
            .pp-tata { grid-template-columns: minmax(0, 1fr) 340px; }
            .pp-samping { position: sticky; top: 84px; }
        }
        .pp-utama > .proc-section, .pp-utama > .alert { margin-bottom: 16px !important; }
        .pp-kepala-teks { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .pp-kepala-teks small { color: #6b7280; font-size: .78rem; line-height: 1.45; }
        .pp-langkah { color: #f26522; font-size: .66rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .pp-pemisah { height: 1px; background: #f1f5f9; margin: 18px 0; }
        .pp-ring-kepala { display: flex; align-items: center; gap: 12px; padding-bottom: 14px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
        .pp-ring { margin: 0; display: flex; flex-direction: column; }
        .pp-ring > div { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px dashed #eef2f7; font-size: .84rem; }
        .pp-ring dt { color: #6b7280; font-weight: 600; }
        .pp-ring dd { margin: 0; color: #1c1f26; font-weight: 700; text-align: right; overflow-wrap: anywhere; }
        .pp-plus { color: #16a34a; font-weight: 800; }
        .pp-ring-sorot { display: flex; flex-direction: column; gap: 10px; margin: 14px 0; padding: 12px; border-radius: 14px; background: #f8fafc; border: 1px solid #eef2f7; }
        .pp-ring-baris { display: flex; gap: 10px; align-items: flex-start; }
        .pp-ring-baris i.bi { color: #cbd5e1; font-size: 1rem; line-height: 1.3; }
        .pp-ring-baris i.bi.is-ok { color: #16a34a; }
        .pp-ring-baris b { display: block; font-size: .82rem; color: #1c1f26; }
        .pp-ring-baris small { display: block; font-size: .76rem; color: #6b7280; overflow-wrap: anywhere; }
        .pp-tombol { display: flex; flex-direction: column; gap: 8px; }
        .pt-proses .pp-tombol .btn-primary, .pt-proses .pp-tombol .btn-danger { border-radius: 14px; }
        .pp-isi-tombol { display: inline-flex; align-items: center; justify-content: center; }
        .pp-isi-muat { align-items: center; gap: 8px; }
        .pp-putar {
            display: inline-block; width: 16px; height: 16px; border-radius: 50%;
            border: 2px solid currentColor; border-right-color: transparent; animation: ppPutar .7s linear infinite;
        }
        @keyframes ppPutar { to { transform: rotate(360deg); } }
        .pp-admin { margin: 12px 0 0; font-size: .74rem; color: #6b7280; line-height: 1.5; text-align: center; }
        .ebook-pick { height: 100%; }
        @media (max-width: 575.98px) {
            .pt-proses form > .border-top { flex-direction: column-reverse; }
            .pt-proses form > .border-top .btn { width: 100%; }
        }
    </style>

    @if ($pesanGalat)
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $pesanGalat }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if ($pesanInfo)
    <div class="alert alert-info alert-dismissible fade show rounded-4 border-0 shadow-sm">
        <i class="bi bi-info-circle-fill me-1"></i>{{ $pesanInfo }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @php
        $pkKredit = $this->pakaiKredit();
        $ppPembeli = $order->customer->nama ?? 'Pelanggan';
        $ppTgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d M Y') : null;
        $ppMulai = $ppTgl($startDate);
        $ppAkhir = $ppTgl($endDate);
        $ppAdaBonus = 0 < (int) $bonusDurationValue;
        $ppAkunTerisi = filled($accountUsername);
        $ppLabelStatus = ['baru' => 'Baru', 'perpanjang' => 'Perpanjang', 'pengganti' => 'Pengganti'][$subscriptionStatus] ?? ucfirst((string) $subscriptionStatus);
    @endphp

    <form wire:submit="processOrder">
    <div class="pp-tata">
    <div class="pp-utama">
    {{-- Pesanan KREDIT: tidak ada akun baru yang dikirim. Admin menambah kredit
         ke akun milik pembeli, alamatnya ditulis pembeli di Catatan. --}}
    @if ($pkKredit)
        <div class="proc-section p-4 mb-4" style="border-left: 4px solid #f59e0b;">
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="proc-section-icon" style="background: linear-gradient(135deg,#f59e0b,#d97706);"><i class="bi bi-coin"></i></span>
                <h5 class="fw-bold mb-0">Pesanan Kredit — isi ke akun pelanggan</h5>
            </div>
            <p class="mb-2 text-muted">
                Tambahkan <strong>{{ $orderItem->getDurationLabel() }}</strong> ke akun pelanggan.
                Kredit tidak punya masa aktif, jadi tanggal akhir dikosongkan.
            </p>
            <div class="mb-0">
                <span class="fw-semibold">Akun tujuan (catatan pelanggan):</span>
                @if (filled($order->customer_notes))
                    <span class="d-inline-block">{{ $order->customer_notes }}</span>
                @else
                    <span class="text-danger">- pelanggan tidak menulis akun tujuan, hubungi dulu -</span>
                @endif
            </div>
        </div>
    @endif

        @if ($this->belumLunas)
        <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-3 d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>
                <strong>Pembayaran QRIS belum masuk (masih pending).</strong><br>
                <span class="small">Pastikan pembayaran benar-benar sudah diterima sebelum mengirim akun. Kamu akan diminta konfirmasi saat menekan "Proses".</span>
            </div>
        </div>
        @endif

        <!-- Pilih Data Akun -->
        <div class="proc-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="proc-section-icon"><i class="bi bi-key-fill"></i></span>
                <div class="pp-kepala-teks">
                    <span class="pp-langkah">Langkah 1</span>
                    <h5 class="fw-bold mb-0">{{ $pkKredit ? 'Akun Tujuan Kredit' : 'Akun yang Dikirim' }}</h5>
                    <small>{{ $pkKredit ? 'Isi email akun milik pelanggan yang ditambah kreditnya.' : 'Pilih dari stok akun, atau isi manual bila akunnya belum ada di daftar.' }}</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Data Akun Tersedia <span class="text-danger">*</span></label>
                <button type="button" onclick="paOpenPicker(this)"
                    class="form-select text-start pa-picker-btn @error('selectedDataAkunId') is-invalid @enderror">
                    <span id="paAccountLabel" wire:ignore class="text-muted">-- Pilih atau isi manual di bawah --</span>
                </button>

                {{-- Sumber data akun AKTIF untuk picker (dibaca oleh JS) --}}
                <div id="paAccountsSource" class="d-none">
                    @foreach ($availableAccounts as $akun)
                    <span data-id="{{ $akun->id }}" data-name="{{ $akun->nama_akun }}"
                        data-sub="{{ $akun->username_akun }}"
                        @if ($akun->id == $selectedDataAkunId) data-selected="1" @endif></span>
                    @endforeach
                </div>
                @error('selectedDataAkunId')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">
                    Memilih akun mengisi username, password, dan link di bawah secara otomatis.
                </small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ $pkKredit ? 'Email akun pelanggan yang diisi' : 'Username / Email Akun' }}</label>
                    <input type="text" class="form-control @error('accountUsername') is-invalid @enderror"
                        wire:model="accountUsername" placeholder="username@example.com">
                    @error('accountUsername')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Password Akun @if ($pkKredit)<span class="text-muted">(tidak perlu untuk kredit)</span>@endif</label>
                    <input type="text" class="form-control @error('accountPassword') is-invalid @enderror"
                        wire:model="accountPassword" placeholder="Password akun premium">
                    @error('accountPassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Link Akses</label>
                <input type="url" class="form-control @error('accountLink') is-invalid @enderror"
                    wire:model="accountLink" placeholder="https://example.com/login">
                @error('accountLink')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0">
                <label class="form-label">Catatan untuk Pelanggan</label>
                {{-- Templat sekali klik; teksnya ikut terkirim di pesan WhatsApp akun. --}}
                <div class="pp-templat">
                    <span class="pp-templat-judul"><i class="bi bi-lightning-charge-fill"></i> Templat:</span>
                    @foreach ($this::TEMPLAT_CATATAN as $kunci => $tpl)
                        @php $tplDipakai = str_contains((string) $accountNotes, $tpl['teks']); @endphp
                        <button type="button" class="pp-templat-btn {{ $tplDipakai ? 'is-dipakai' : '' }}"
                            wire:click="pakaiTemplatCatatan('{{ $kunci }}')" @disabled($tplDipakai)
                            title="{{ $tpl['teks'] }}">
                            <i class="bi {{ $tplDipakai ? 'bi-check2' : $tpl['ikon'] }}"></i>
                            <span>{{ $tpl['label'] }}{{ $tplDipakai ? ' · sudah ditambahkan' : '' }}</span>
                        </button>
                    @endforeach
                </div>
                <textarea class="form-control @error('accountNotes') is-invalid @enderror" wire:model="accountNotes" rows="3"
                    placeholder="Catatan tambahan untuk pelanggan (opsional)"></textarea>
                @error('accountNotes')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Periode Berlangganan -->
        <div class="proc-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="proc-section-icon icon-green"><i class="bi bi-calendar-range-fill"></i></span>
                <div class="pp-kepala-teks">
                    <span class="pp-langkah">Langkah 2</span>
                    <h5 class="fw-bold mb-0">Masa Aktif</h5>
                    <small>Tanggal akhir dihitung otomatis dari tanggal mulai, durasi, dan bonus.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control @error('startDate') is-invalid @enderror"
                            wire:model.live="startDate">
                        @error('startDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" wire:model="endDate" readonly @disabled($pkKredit)>
                        <small class="text-muted">
                            @if ($pkKredit)
                                Kredit tidak kedaluwarsa — tanpa tanggal akhir.
                            @else
                                Otomatis: mulai + {{ $orderItem->getDurationLabel() }}
                                @if ($bonusDurationValue)
                                + bonus {{ $bonusDurationValue }} {{ $bonusDurationType }}
                                @endif
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <div class="row align-items-end">
                <div class="col-md-4">
                    <div class="mb-3 mb-md-0">
                        <label class="form-label">Bonus Durasi <span class="text-muted">(opsional)</span></label>
                        <input type="number" min="1" class="form-control @error('bonusDurationValue') is-invalid @enderror"
                            wire:model.live="bonusDurationValue" placeholder="mis. 2">
                        @error('bonusDurationValue')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3 mb-md-0">
                        <label class="form-label">Satuan Bonus</label>
                        <select class="form-select" wire:model.live="bonusDurationType">
                            <option value="bulan">Bulan</option>
                            <option value="tahun">Tahun</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="total-aktif {{ (int) $bonusDurationValue > 0 ? 'is-active' : '' }}">
                        <div class="ta-label"><i class="bi bi-gift-fill"></i> Total Masa Aktif</div>
                        @if ((int) $bonusDurationValue > 0)
                        <div class="ta-value">
                            <span class="ta-base">{{ $orderItem->duration_type ? $orderItem->duration_value.' '.$orderItem->duration_type : 'Paket' }}</span>
                            <span class="ta-plus">+</span>
                            <span class="ta-bonus">{{ $bonusDurationValue }} {{ $bonusDurationType }}</span>
                        </div>
                        @else
                        <div class="ta-empty">Belum ada bonus durasi</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="pp-pemisah"></div>

            <div class="mb-0">
                <label class="form-label">Status Pembelian *</label>
                <select class="form-select @error('subscriptionStatus') is-invalid @enderror"
                    wire:model="subscriptionStatus">
                    <option value="baru">Baru (Pembelian pertama kali)</option>
                    <option value="perpanjang">Perpanjang (Perpanjangan akun lama)</option>
                    <option value="pengganti">Pengganti (Ganti akun bermasalah)</option>
                </select>
                @error('subscriptionStatus')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Bonus untuk Pelanggan -->
        <div class="proc-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="proc-section-icon icon-amber"><i class="bi bi-gift-fill"></i></span>
                <div class="pp-kepala-teks">
                    <span class="pp-langkah">Langkah 3 · opsional</span>
                    <h5 class="fw-bold mb-0">Bonus untuk Pelanggan</h5>
                    <small>Ebook yang dicentang ikut terkirim sebagai tautan di pesan WhatsApp.</small>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label mb-0">Pilih Ebook Bonus dari Pustaka</label>
                    <a href="{{ route('admin.ebook.index') }}" target="_blank"
                        class="small text-decoration-none d-inline-flex align-items-center gap-1">
                        <i class="bi bi-gear"></i> Kelola Pustaka
                    </a>
                </div>

                @if (count($availableEbooks) > 0)
                @php
                    // Ebook bawaan produk ditaruh paling depan.
                    $ebookUrut = collect($availableEbooks)->sortBy(fn ($eb) => (string) $eb->id === $ebookBawaanId ? 0 : 1)->values();
                @endphp
                @if ($ebookBawaanOtomatis)
                    <div class="pp-ebook-info">
                        <i class="bi bi-magic"></i>
                        <span>Ebook <b>bawaan produk</b> ini sudah tercentang otomatis. Hapus centangnya bila pelanggan ini tidak perlu ebook.</span>
                    </div>
                @endif
                <div class="pp-ebook-deret">
                    @foreach ($ebookUrut as $eb)
                        @php $ebBawaan = (string) $eb->id === $ebookBawaanId; @endphp
                        <label class="ebook-pick {{ $ebBawaan ? 'is-bawaan' : '' }}" wire:key="ep-{{ $eb->id }}">
                            <input type="checkbox" value="{{ $eb->id }}" wire:model="selectedEbooks">
                            <span class="ep-icon"><i class="bi bi-journal-bookmark-fill"></i></span>
                            <span class="ep-body">
                                <span class="ep-title">{{ $eb->judul }}</span>
                                @if ($ebBawaan)
                                    <small class="ep-bawaan"><i class="bi bi-magic"></i> Bawaan produk</small>
                                @else
                                    <small class="ep-desc">{{ $eb->deskripsi ?: 'Ebook bonus' }}</small>
                                @endif
                            </span>
                            <i class="bi bi-check-circle-fill ep-check"></i>
                        </label>
                    @endforeach
                </div>
                @error('selectedEbooks.*') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                @else
                <div class="ebook-empty">
                    <i class="bi bi-journal-x"></i>
                    <div>
                        <div class="fw-semibold">Belum ada ebook di pustaka</div>
                        <a href="{{ route('admin.ebook.create') }}" target="_blank" class="small">Tambah ebook dulu →</a>
                    </div>
                </div>
                @endif
            </div>

            <div class="mb-0">
                <label class="form-label">Catatan Bonus Lain <span class="text-muted">(opsional)</span></label>
                <input type="text" class="form-control @error('bonusDescription') is-invalid @enderror"
                    wire:model="bonusDescription" placeholder="mis. Bonus konsultasi 30 menit (selain ebook)">
                @error('bonusDescription')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Catatan Admin -->
        <div class="proc-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="proc-section-icon icon-blue"><i class="bi bi-journal-text"></i></span>
                <div class="pp-kepala-teks">
                    <span class="pp-langkah">Langkah 4 · opsional</span>
                    <h5 class="fw-bold mb-0">Catatan Internal</h5>
                    <small>Hanya terlihat oleh admin, tidak dikirim ke pelanggan.</small>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label">Catatan Proses</label>
                <textarea class="form-control @error('processingNotes') is-invalid @enderror" wire:model="processingNotes"
                    rows="3" placeholder="Catatan internal untuk admin (tidak dilihat customer)"></textarea>
                @error('processingNotes')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>

    </div>{{-- /.pp-utama --}}

    {{-- Panel ringkasan: apa yang diproses dan apa yang akan tercatat,
         bersama tombol simpan — tidak perlu menggulir ke ujung form. --}}
    <aside class="pp-samping">
        <div class="proc-section p-4">
            <div class="pp-ring-kepala">
                <span class="proc-section-icon"><i class="bi bi-bag-check-fill"></i></span>
                <div class="pp-kepala-teks">
                    <span class="pp-langkah">Ringkasan</span>
                    <h5 class="fw-bold mb-0">{{ $orderItem->product_name }}</h5>
                </div>
            </div>

            <dl class="pp-ring">
                <div><dt>Pembeli</dt><dd>{{ $ppPembeli }}</dd></div>
                <div><dt>Durasi</dt><dd>{{ $orderItem->getDurationLabel() }}@if ($ppAdaBonus) <span class="pp-plus">+ {{ $bonusDurationValue }} {{ $bonusDurationType }}</span>@endif</dd></div>
                <div><dt>Harga</dt><dd>Rp {{ number_format($orderItem->price, 0, ',', '.') }} × {{ $orderItem->quantity }}</dd></div>
                <div><dt>Status</dt><dd>{{ $ppLabelStatus }}</dd></div>
            </dl>

            <div class="pp-ring-sorot">
                <div class="pp-ring-baris">
                    <i class="bi {{ $ppAkunTerisi ? 'bi-check-circle-fill is-ok' : 'bi-circle' }}"></i>
                    <span>
                        <b>Akun</b>
                        <small>{{ $ppAkunTerisi ? $accountUsername : 'Belum diisi' }}</small>
                    </span>
                </div>
                <div class="pp-ring-baris">
                    <i class="bi {{ $ppMulai ? 'bi-check-circle-fill is-ok' : 'bi-circle' }}"></i>
                    <span>
                        <b>Masa aktif</b>
                        <small>
                            @if ($pkKredit)
                                Kredit — tanpa tanggal akhir
                            @elseif ($ppMulai)
                                {{ $ppMulai }} – {{ $ppAkhir ?? '…' }}
                            @else
                                Tanggal mulai belum diisi
                            @endif
                        </small>
                    </span>
                </div>
            </div>

            <div class="pp-tombol">
                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center"
                    style="height: 50px;" wire:loading.attr="disabled" wire:target="processOrder">
                    {{-- Tanpa d-inline-flex (!important): kelas itu mengalahkan
                         penyembunyi Livewire sehingga dua keadaan tampil bersamaan. --}}
                    <span wire:loading.remove wire:target="processOrder" class="pp-isi-tombol">
                        <i class="bi bi-check2-circle me-2 fs-5"></i>
                        <span>Proses &amp; Kirim</span>
                    </span>
                    <span wire:loading.inline-flex wire:target="processOrder" class="pp-isi-muat">
                        <span class="pp-putar"></span> Menyimpan…
                    </span>
                </button>
                <button type="button" wire:click="cancelProcessing"
                    class="btn btn-danger w-100 d-inline-flex align-items-center justify-content-center" style="height: 44px;">
                    <i class="bi bi-x-circle me-2"></i><span>Batal, kembali ke daftar</span>
                </button>
            </div>

            <p class="pp-admin">
                <i class="bi bi-person-check"></i>
                Diproses oleh <b>{{ auth()->user()->name }}</b> · {{ now()->locale('id')->translatedFormat('d M Y, H:i') }} WIB
            </p>
        </div>
    </aside>
    </div>{{-- /.pp-tata --}}
    </form>
    </div>{{-- /.dsb --}}

    @push('scripts')
    <script>
        (function () {
            // Popup pemilih akun (SweetAlert) — seragam dengan create order
            window.paPicker = function (title, items, onPick) {
                if (typeof Swal === 'undefined') return;
                var wrap = document.createElement('div');
                var search = document.createElement('input');
                search.className = 'form-control mb-2';
                search.placeholder = 'Ketik untuk mencari akun...';
                var list = document.createElement('div');
                list.className = 'pa-pick-list';

                if (!items.length) {
                    var empty = document.createElement('div');
                    empty.className = 'pa-pick-empty';
                    empty.textContent = 'Tidak ada akun aktif';
                    list.appendChild(empty);
                } else {
                    items.forEach(function (it) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'pa-pick-item';
                        btn.dataset.search = ((it.name || '') + ' ' + (it.sub || '')).toLowerCase();
                        btn.textContent = it.name;
                        if (it.sub) {
                            var s = document.createElement('span');
                            s.className = 'pa-sub';
                            s.textContent = it.sub;
                            btn.appendChild(s);
                        }
                        btn.addEventListener('click', function () {
                            onPick(it.id, it.name, it.sub);
                            Swal.close();
                        });
                        list.appendChild(btn);
                    });
                }
                wrap.appendChild(search);
                wrap.appendChild(list);

                Swal.fire({
                    title: title,
                    html: wrap,
                    background: 'rgba(255,255,255,0.92)',
                    backdrop: 'rgba(139,92,246,0.15)',
                    customClass: { popup: 'swal-glossy-popup', title: 'swal-glossy-title' },
                    buttonsStyling: false,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: 480,
                    padding: '1.25rem',
                    didOpen: function () {
                        search.addEventListener('input', function () {
                            var q = search.value.toLowerCase();
                            list.querySelectorAll('.pa-pick-item').forEach(function (b) {
                                b.style.display = b.dataset.search.indexOf(q) !== -1 ? '' : 'none';
                            });
                        });
                        setTimeout(function () { search.focus(); }, 100);
                    }
                });
            };

            window.paOpenPicker = function (btn) {
                var src = document.getElementById('paAccountsSource');
                if (!src) return;
                var items = Array.prototype.map.call(src.querySelectorAll('span'), function (s) {
                    return { id: s.dataset.id, name: s.dataset.name, sub: s.dataset.sub || '' };
                });
                var cidEl = btn.closest('[wire\\:id]');
                var cid = cidEl ? cidEl.getAttribute('wire:id') : null;
                window.paPicker('Pilih Akun Premium (aktif)', items, function (id, name, sub) {
                    var label = document.getElementById('paAccountLabel');
                    if (label) {
                        label.textContent = name + (sub ? ' — ' + sub : '');
                        label.classList.remove('text-muted');
                    }
                    if (cid && window.Livewire) window.Livewire.find(cid).call('pickAccount', id);
                });
            };

            // Set label awal bila sudah ada akun terpilih
            function paInitLabel() {
                var src = document.getElementById('paAccountsSource');
                var label = document.getElementById('paAccountLabel');
                if (!src || !label) return;
                var sel = src.querySelector('[data-selected]');
                if (sel) {
                    label.textContent = sel.dataset.name + (sel.dataset.sub ? ' — ' + sel.dataset.sub : '');
                    label.classList.remove('text-muted');
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', paInitLabel);
            } else {
                paInitLabel();
            }
            document.addEventListener('livewire:navigated', paInitLabel);
        })();

        // Peringatan bila memproses order QRIS yang pembayarannya belum masuk.
        // processOrder() akan menahan diri & memancarkan event ini; setelah admin
        // menyetujui, set flag lalu panggil ulang processOrder.
        document.addEventListener('livewire:init', function () {
            Livewire.on('konfirmasi-belum-lunas', function () {
                if (typeof Swal === 'undefined') { return; }
                Swal.fire({
                    title: 'Pembayaran belum masuk',
                    html: 'Pembayaran QRIS pesanan ini <b>belum terkonfirmasi (masih pending)</b>.<br>Yakin tetap kirim akun ke customer?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, tetap proses',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel' },
                }).then(function (r) {
                    if (!r.isConfirmed) { return; }
                    // Ambil komponen yang MEMILIKI form processOrder — BUKAN
                    // komponen pertama di halaman (dulu keliru mengambil NotifPoller
                    // di layout, sehingga "Ya, tetap proses" tak berfungsi).
                    var form = document.querySelector('form[wire\\:submit="processOrder"]');
                    var el = form ? form.closest('[wire\\:id]') : document.querySelector('[wire\\:id]');
                    var lw = (el && window.Livewire) ? window.Livewire.find(el.getAttribute('wire:id')) : null;
                    if (!lw) { return; }
                    Promise.resolve(lw.set('lunasDikonfirmasi', true)).then(function () {
                        lw.call('processOrder');
                    });
                });
            });
        });
    </script>
    @endpush
</div>