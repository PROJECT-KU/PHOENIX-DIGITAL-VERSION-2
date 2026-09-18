
@section('title')
Detail Pesanan || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Isi tiap kartu tetap,
         hanya kulitnya yang diseragamkan (lihat gaya pt-detail di bawah). --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.order.partials.toko-gaya')

    @php
        [$hdStatus, $hdWarna] = $order->labelStatus();
        $hdBayar = $order->labelPembayaran();
        $hdLencana = [
            'success' => 'is-hijau', 'warning' => 'is-kuning', 'info' => 'is-biru', 'primary' => 'is-ungu',
            'danger' => 'is-merah', 'secondary' => 'is-abu', 'dark' => 'is-abu', 'light' => 'is-abu',
        ];
        $hdNamaStatus = [
            'DRAFT' => 'Draft', 'PENDING' => 'Menunggu bayar', 'PAID' => 'Dibayar', 'PROCESSING' => 'Diproses',
            'COMPLETED' => 'Selesai', 'CANCELLED' => 'Dibatalkan', 'SEDANG DIPROSES' => 'Sedang dicek',
        ];
        $hdJumlahItem = $order->items->count();
        $hdSemuaJasa = $hdJumlahItem && $order->items->every(fn ($it) => $it->product && $it->product->butuh_file);
        $hdBisaBatal = $order->status !== 'cancelled';
        $hdBuktiTercatat = (bool) $order->bukti_pembayaran;
        $hdBuktiTersedia = $hdBuktiTercatat && $this->buktiTersedia();
        $hdBolehGanti = $this->bolehGantiBukti();
        $hdBarisBukti = $hdBuktiTercatat || $hdBolehGanti;

        $hdRefBayar = $order->payment_reference;
        $hdIdQris = $order->qris_trx_id;

        // Data pembeli (kartu kanan).
        $pbl = $order->customer;
        $pblMember = $pbl && $pbl->status_member === 'active';
        $pblPesanan = $pbl ? $pbl->orders()->whereIn('status', ['paid', 'processing', 'completed'])->count() : 0;
        $pblWa = $pbl ? preg_replace('/\D/', '', (string) $pbl->no_hp) : '';
        if (str_starts_with($pblWa, '0')) {
            $pblWa = '62'.substr($pblWa, 1);
        }
        $pblAdaWa = strlen($pblWa) >= 9;
        $pblBolehLihat = $pbl && (bool) auth()->user()?->hasPermission('view_customer');
        $hdBolehUbah = (bool) auth()->user()?->hasPermission('edit_pemesanantoko');
        $hdCatatanDitangani = $order->catatan_ditangani_at;
        // Warna Bootstrap (statusWarna/jenisWarna) → lencana dasbor, supaya seragam.
        $lencanaBs = [
            'success' => 'is-hijau', 'warning' => 'is-kuning', 'info' => 'is-biru', 'primary' => 'is-ungu',
            'danger' => 'is-merah', 'secondary' => 'is-abu', 'dark' => 'is-abu', 'light' => 'is-abu',
        ];
    @endphp

    <div class="dsb pt-detail">
    <header class="dsb-hero">
        <div class="dsb-hero-teks">
            <h1 class="dsb-salam">{{ $order->order_number }}</h1>
            <p class="dsb-hero-ket">
                <span class="d-block"><i class="bi bi-calendar3 me-1"></i>Dipesan {{ $order->created_at->locale('id')->translatedFormat('l, d F Y · H:i') }}</span>
                <span class="d-block pt-lencana-kepala">
                    <span class="dsb-lencana {{ $hdLencana[$hdWarna] ?? 'is-abu' }}">{{ $hdNamaStatus[$hdStatus] ?? $hdStatus }}</span>
                    @if ($hdBayar)
                        <span class="dsb-lencana {{ $hdLencana[$hdBayar[2]] ?? 'is-abu' }}"><i class="bi {{ $hdBayar[1] }}"></i>{{ $hdBayar[0] }}</span>
                    @endif
                    @if ($order->butuhUpload())
                        <span class="dsb-lencana is-kuning"><i class="bi bi-shield-check"></i>Pesanan jasa</span>
                    @endif
                </span>
            </p>
        </div>

        <div class="dsb-hero-aksi">
            <a wire:navigate href="{{ route('admin.pesanantoko.index') }}" class="dsb-tombol is-lembut">
                <i class="bi bi-arrow-left"></i><span>Kembali</span>
            </a>
            <button type="button" class="dsb-tombol is-lembut" wire:click="$set('lihatRiwayat', true)">
                <i class="bi bi-clock-history"></i><span>Riwayat</span>
            </button>
            @if ($order->getReceiptUrl())
                <a href="{{ $order->getReceiptUrl() }}" target="_blank" rel="noopener" class="dsb-tombol is-lembut">
                    <i class="bi bi-receipt"></i><span>Lihat Struk</span>
                </a>
            @endif
            @if ($hdBisaBatal)
                {{-- Konfirmasi lewat penangan global .pcek-konfirmasi (skrip di bawah). --}}
                <button type="button" class="dsb-tombol is-bahaya pcek-konfirmasi"
                    data-action="batalkanPesanan"
                    data-title="Batalkan pesanan ini?"
                    data-text="Status menjadi CANCELLED, pembayaran ditandai kedaluwarsa, dan income/modal otomatis dilepas. Akun yang sudah terlanjur dikirim TIDAK ikut tertarik."
                    data-confirm="Ya, batalkan pesanan"
                    data-icon="warning">
                    <i class="bi bi-x-circle"></i><span>Batalkan</span>
                </button>
            @endif
        </div>
    </header>

    {{-- Jendela Riwayat Pesanan (App\Support\RiwayatPesanan) --}}
    @if ($lihatRiwayat)
        <div class="ts-modal-back" wire:click="$set('lihatRiwayat', false)"></div>
        <div class="ts-modal" wire:key="riwayat-pesanan">
            <div class="ts-modal-card dsb is-datar" role="dialog" aria-modal="true" aria-label="Riwayat pesanan" tabindex="-1"
                style="max-width: 560px" x-on:keydown.escape.window="$wire.set('lihatRiwayat', false)">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #4f46e5"><i class="bi bi-clock-history"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">Riwayat Pesanan</h5>
                        <span class="dsb-kartu-sub">{{ $order->order_number }} · terbaru di atas</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="$set('lihatRiwayat', false)" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="dsb-jendela-isi">
                    @include('livewire.pages.admin.order.partials.riwayat-daftar', ['riwayat' => $riwayat])
                </div>
            </div>
        </div>
    @endif

    <style>

    /* Tanpa aturan ini elemen ber-x-cloak sempat terlihat sebelum Alpine siap.
       Layout admin tidak memuat public-custom-styles.css, jadi ditulis di sini
       — sama seperti spending-form. */
    [x-cloak] { display: none !important; }

    /* ===== Kulit dasbor untuk isi lama (pt-detail) =====
       Markup kartu lama dipertahankan — skrip WA, bonus kuota, dan
       pengecekan bergantung padanya — tetapi tampil seperti dsb-kartu. */
    .pt-lencana-kepala { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }

    /* ===== Blok jasa pengecekan ===== */
    .pt-detail .pcek .pcek-head-row { padding-bottom: 16px; margin-bottom: 16px !important; border-bottom: 1px solid #f1f5f9; }
    .pcek-kicker { display: block; color: #f26522; font-size: .66rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .pcek-kuota { margin-bottom: 16px; }
    .pcek-kuota-angka { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 10px; }
    .pcek-kuota-angka > div { padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; }
    .pcek-kuota-angka span { display: block; font-size: .7rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; }
    .pcek-kuota-angka b { display: block; font-size: 1.35rem; line-height: 1.2; color: #1c1f26; }
    .pcek-kuota-angka .is-sisa { background: #f0fdf4; border-color: #bbf7d0; }
    .pcek-kuota-angka .is-sisa b { color: #15803d; }
    .pcek-kuota-angka .is-habis b { color: #94a3b8; }
    .pcek-kuota-garis { height: 8px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
    .pcek-kuota-garis span { display: block; height: 100%; border-radius: inherit; background: #f59e0b; }
    .pcek-kuota-ket { display: inline-flex; align-items: center; gap: 5px; margin-top: 8px; font-size: .76rem; color: #b45309; }
    .pcek-alat { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 16px; }
    .pcek-alat .pcek-link-box { flex: 1 1 320px; min-width: 0; margin: 0 !important; }
    .pcek-link-ic { display: inline-flex; align-items: center; padding: 0 0 0 .75rem; color: #94a3b8; background: #f8fafc; }
    .pcek-alat-tombol { display: flex; flex-wrap: wrap; gap: 8px; }
    .pt-detail .pcek .pcek-btn.wa { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .pt-detail .pcek .pcek-btn.wa:hover { background: #16a34a; color: #fff; border-color: #16a34a; }
    .pcek-daftar-kepala { display: flex; align-items: baseline; gap: 10px; margin: 4px 0 10px; }
    .pcek-daftar-kepala b { font-size: .9rem; color: #1c1f26; }
    .pcek-daftar-kepala span { font-size: .76rem; color: #6b7280; }
    .pcek-daftar { display: grid; gap: 14px; grid-template-columns: minmax(0, 1fr); align-items: start; }
    @media (min-width: 1200px) { .pcek-daftar:not(.is-tunggal) { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .pcek-daftar > .pcek-item { margin: 0 !important; }
    .pcek-daftar > .pcek-kosong { grid-column: 1 / -1; }
    #pengecekan { scroll-margin-top: 90px; }
    /* Jendela unggah hasil */
    .pcek-jendela { max-width: 760px !important; }
    .pcek-jendela .dsb-jendela-teks { min-width: 0; }
    .pcek-jendela-berkas { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pt-detail .pcek.pcek-jendela .pcek-form { margin: 0; border: 0; border-radius: 0; background: #fff; padding: 16px 18px; }
    .pcek-jendela-info {
        display: flex; gap: 8px; align-items: flex-start; margin-bottom: 12px; padding: 10px 12px;
        border-radius: 12px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: .8rem; line-height: 1.5;
    }
    .pcek-jendela-info i.bi { line-height: 1.4; }
    .pt-detail .pcek.pcek-jendela .pcek-aksi { margin-top: 0; }
    .pt-detail .pcek.pcek-jendela .pcek-slot:last-child { margin-bottom: 0; }
    .pcek-kosong { text-align: center; color: #6b7280; padding: 26px 12px; border: 1px dashed #e2e8f0; border-radius: 14px; }
    .pcek-kosong i.bi { display: block; font-size: 1.8rem; opacity: .45; margin-bottom: 6px; }

    /* ===== Form unggah hasil ===== */
    .pt-detail .pcek .pcek-form { border-color: #e9edf3; background: #fcfcfd; padding: 16px; }
    .pt-detail .pcek .pcek-slot { --c: #0284c7; border-color: #eef2f7; border-left: 3px solid var(--c); padding: 14px; }
    .pt-detail .pcek .pcek-slot.is-plagiasi { --c: #0284c7; }
    .pt-detail .pcek .pcek-slot.is-ai { --c: #7c3aed; }
    .pt-detail .pcek .pcek-slot.is-docx { --c: #d97706; }
    .pt-detail .pcek .pcek-slot-no { background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
    .pt-detail .pcek .pcek-slot-lbl { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: .86rem; color: #1c1f26; margin-bottom: 8px; }
    .pt-detail .pcek .pcek-slot-lbl i.bi { color: var(--c); }
    .pt-detail .pcek .pcek-slot-lbl span {
        padding: 1px 8px; border-radius: 999px; background: #f1f5f9; color: #64748b; font-size: .68rem; font-weight: 700;
    }
    /* Zona unggah dan persen berdampingan di layar lebar. */
    .pcek-slot-isi { display: grid; gap: 12px; grid-template-columns: minmax(0, 1fr); align-items: start; }
    @media (min-width: 768px) { .pcek-slot-isi { grid-template-columns: minmax(0, 1fr) 220px; } }
    .pcek-slot-berkas { min-width: 0; }
    .pt-detail .pcek .pcek-slot .pcek-drop {
        border: 1.5px dashed color-mix(in srgb, var(--c) 35%, #fff); background: #fff; padding: 14px 12px; min-height: 70px;
        display: flex; align-items: center; justify-content: center;
    }
    .pt-detail .pcek .pcek-slot .pcek-drop-state { width: 100%; min-width: 0; }
    .pt-detail .pcek .pcek-slot .pcek-drop-state .nm { max-width: calc(100% - 70px); }
    @media (max-width: 575.98px) {
        .pt-detail .pcek .pcek-form { padding: 12px; }
        .pt-detail .pcek .pcek-slot { padding: 12px 10px; gap: 8px; }
        .pt-detail .pcek .pcek-slot-no { display: none; }
        .pt-detail .pcek .pcek-slot .pcek-drop-state .nm { max-width: 100%; }
    }
    .pt-detail .pcek .pcek-slot .pcek-drop:hover { border-color: var(--c); background: color-mix(in srgb, var(--c) 5%, #fff); }
    .pt-detail .pcek .pcek-slot .pcek-drop-state i.bi.up, .pt-detail .pcek .pcek-slot .pcek-drop-state i.bi.ok { color: var(--c); }
    .pt-detail .pcek .pcek-slot .pcek-drop-state .chg { background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
    .pt-detail .pcek .pcek-slot .pcek-persen-wrap { max-width: none; }
    .pt-detail .pcek .pcek-persen-num { height: 42px; border-color: #e9edf3; border-radius: 11px; }
    .pt-detail .pcek .pcek-persen-num::placeholder { font-weight: 500; color: #9aa5b5; font-size: .82rem; }
    .pt-detail .pcek .pcek-persen-num:focus { border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(124, 58, 237, .14); }
    .pt-detail .pcek .pcek-persen-suffix { background: #f1f5f9; color: #475569; }
    .pt-detail .pcek .pcek-aksi { justify-content: flex-end; }
    .pt-detail .pcek .pcek-aksi .pcek-btn { flex: 0 0 auto; min-width: 110px; justify-content: center; }
    .pt-detail .pcek .pcek-aksi .pcek-btn.success { flex: 0 0 auto; min-width: 200px; }
    @media (max-width: 575.98px) {
        .pt-detail .pcek .pcek-aksi .pcek-btn, .pt-detail .pcek .pcek-aksi .pcek-btn.success { flex: 1 1 0; min-width: 0; }
    }

    /* ===== Form bonus kuota ===== */
    .pt-detail .pcek .pcek-bonus { border-color: #fde68a; background: #fffdf5; }
    .pcek-bonus-sisa { margin-left: auto; flex-shrink: 0; padding: 4px 10px; border-radius: 999px; background: #fff; border: 1px solid #fde68a; font-size: .74rem; font-weight: 700; color: #92400e; }
    .pt-detail .pcek .pcek-bonus-f label { text-transform: none; letter-spacing: 0; font-size: .8rem; color: #334155; }
    .pt-detail .pcek .pcek-bonus-f select, .pt-detail .pcek .pcek-bonus-f input { height: 42px; border-color: #e9edf3; border-radius: 11px; }
    .pt-detail .pcek .pcek-bonus-note { padding: 8px 11px; border-radius: 10px; background: #fff; border: 1px solid #fde68a; }
    .pt-detail .pcek .pcek-bonus-btns { justify-content: flex-end; }
    .pt-detail .pcek .pcek-bonus-btns .pcek-btn { min-width: 110px; justify-content: center; }

    /* Kotak bukti pembayaran */
    .pt-bukti {
        display: flex; align-items: center; gap: 12px; margin: 10px 0 4px;
        padding: 10px 12px; border: 1px solid #eef2f7; border-radius: 14px; background: #fcfcfd;
    }
    .pt-bukti-gambar { flex: 0 0 56px; width: 56px; height: 56px; }
    .pt-bukti-thumb {
        display: block; width: 56px; height: 56px; padding: 0; border: 1px solid #e9edf3;
        border-radius: 11px; overflow: hidden; background: #fff; cursor: zoom-in;
    }
    .pt-bukti-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pt-bukti-ikon {
        display: none; width: 56px; height: 56px; border-radius: 11px;
        align-items: center; justify-content: center; font-size: 1.35rem;
    }
    .pt-bukti-ikon i.bi, .pt-bukti-ikon i.bi::before { display: block; line-height: 1; }
    .pt-bukti-ikon-gagal { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .pt-bukti-ikon-kosong { background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e1; }
    .pt-bukti-teks { flex: 1 1 0; min-width: 140px; display: flex; flex-direction: column; gap: 2px; }
    .pt-bukti-judul { font-size: .86rem; font-weight: 800; color: var(--dsb-tinta); }
    .pt-bukti-ket { font-size: .76rem; color: var(--dsb-redup); line-height: 1.4; }
    .pt-bukti-ket-gagal, .pt-bukti-ket-kosong { display: none; }
    .pt-bukti-aksi { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .pt-bukti.is-gagal { border-color: #fecaca; background: #fff7f7; }
    .pt-bukti.is-gagal .pt-bukti-thumb, .pt-bukti.is-gagal .pt-bukti-ket-ada, .pt-bukti.is-gagal .pt-bukti-buka { display: none; }
    .pt-bukti.is-gagal .pt-bukti-ikon-gagal { display: flex; }
    .pt-bukti.is-gagal .pt-bukti-ket-gagal { display: block; color: #b91c1c; }
    .pt-bukti.is-kosong .pt-bukti-ket-ada { display: none; }
    .pt-bukti.is-kosong .pt-bukti-ikon-kosong { display: flex; }
    .pt-bukti.is-kosong .pt-bukti-ket-kosong { display: block; }
    @media (max-width: 420px) {
        .pt-bukti { flex-wrap: wrap; }
        .pt-bukti-aksi { width: 100%; justify-content: flex-end; }
    }
    .pt-kepala-sisip { margin: 0 0 14px !important; }
    .pt-detail > .row.mb-4 { margin-bottom: clamp(20px, 3vw, 32px) !important; }
    /* Kartu setinggi isinya masing-masing: isi kartu pembayaran berbeda
       menurut metode bayar, jadi menyamakan tinggi selalu menyisakan
       kotak kosong di salah satunya. */
    .pt-detail > .row.mb-4 { align-items: flex-start; }
    .pt-detail > .row.mb-4 .detail-info-card { height: auto !important; }
    .pt-kode { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .82rem; overflow-wrap: anywhere; }
    .pt-detail .detail-info-card,
    .pt-detail > .card,
    .pt-detail .pcek.card {
        background: #fff !important; backdrop-filter: none !important;
        border: 1px solid var(--dsb-tepi) !important; border-radius: 18px !important;
        box-shadow: none !important; margin-bottom: clamp(20px, 3vw, 32px) !important;
    }
    .pt-detail .detail-info-card { margin-bottom: 0 !important; display: flex; flex-direction: column; }
    .pt-pembeli-aksi { display: flex; flex-wrap: wrap; gap: 8px; padding-top: 16px; }
    .pt-pembeli-aksi:empty { display: none; }
    .pt-tombol-wa i.bi { color: #16a34a; }
    @media (hover: hover) and (pointer: fine) {
        .pt-detail .detail-info-card:hover, .pt-detail > .card:hover { border-color: #dfe5ee !important; box-shadow: 0 10px 24px rgba(15, 23, 42, .05) !important; }
    }
    .pt-detail .detail-info-card h5, .pt-detail .card h5 { font-size: .98rem; font-weight: 800; color: var(--dsb-tinta); }
    .pt-detail .detail-info-card > .d-flex:first-child { padding-bottom: 14px; margin-bottom: 6px !important; border-bottom: 1px solid #f1f5f9; }
    .pt-detail .detail-info-card .info-icon {
        --c: #7c3aed; width: 42px; height: 42px; border-radius: 12px; box-shadow: none !important;
        background: color-mix(in srgb, var(--c) 12%, #fff) !important;
        border: 1px solid color-mix(in srgb, var(--c) 22%, #fff); color: var(--c) !important;
    }
    .pt-detail .info-icon.bg-grad-green { --c: #16a34a; }
    .pt-detail .detail-info-card .info-label { color: var(--dsb-redup); }
    .pt-detail .method-chip { box-shadow: none !important; padding: .32rem .7rem; font-size: .74rem; }
    .pt-detail .method-flash { background: #ffe4e6; color: #be123c; }
    .pt-detail .method-promo { background: #ede9fe; color: #6d28d9; }
    .pt-detail .method-point { background: #fef3c7; color: #b45309; }
    .pt-detail .method-referral { background: #dcfce7; color: #15803d; }
    .pt-detail .method-none { background: #f1f5f9; color: #64748b; }
    .pt-detail .pcek .pcek-head-row { padding-bottom: 14px; border-bottom: 1px solid #f1f5f9; }
    .pt-detail .items-table thead th {
        background: #f8fafc; color: #6b7280 !important; font-size: .7rem; font-weight: 800;
        letter-spacing: .05em; text-transform: uppercase; border-bottom: 1px solid #eef2f7;
    }
    .pt-detail .items-table thead th:first-child { border-radius: 10px 0 0 10px; }
    /* Baris tabel item: rata, tanpa bayangan hover dari layout, lencana lembut. */
    .pt-detail .items-table { margin-bottom: 0; }
    .pt-detail .items-table thead th { padding: 11px 12px !important; white-space: nowrap; }
    .pt-detail .items-table tbody td {
        padding: 14px 12px !important; border-bottom: 1px solid #f1f5f9 !important;
        color: #334155; font-size: .88rem; vertical-align: middle;
    }
    .pt-detail .items-table tbody tr:last-child td { border-bottom: 0 !important; }
    .pt-detail .items-table tbody tr:hover { box-shadow: none !important; transform: none !important; background: #fafbfd !important; }
    .pt-detail .items-table tbody tr:hover td { background: transparent !important; }
    .pt-detail .items-table .pt-sel-produk { color: var(--dsb-tinta); font-weight: 700 !important; min-width: 220px; }
    /* Angka, durasi, dan tanggal tidak dipatah ("Rp" / "60.000"). */
    .pt-detail .items-table td[data-judul="Durasi"],
    .pt-detail .items-table td[data-judul="Harga Satuan"],
    .pt-detail .items-table td[data-judul="Status"],
    .pt-detail .items-table td[data-judul="Masa Aktif"] { white-space: nowrap; }
    .pt-detail .items-table td[data-judul="Subtotal"] { color: var(--dsb-tinta); }
    /* Harga: nominal di atas, keterangan hitungannya di bawah. */
    .pt-harga { display: block; font-weight: 700; color: var(--dsb-tinta); white-space: nowrap; }
    /* Catatan item di bawah nama produk */
    .pt-catatan {
        display: flex; gap: 6px; align-items: flex-start; margin-top: 6px; padding: 6px 9px; max-width: 420px;
        border-radius: 9px; font-size: .74rem; font-weight: 500; line-height: 1.45; white-space: normal;
    }
    .pt-catatan i.bi { line-height: 1.45; flex-shrink: 0; }
    .pt-catatan b { font-weight: 700; }
    .pt-catatan.is-internal { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .pt-catatan.is-pelanggan { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .pt-catatan > span { flex: 1 1 auto; min-width: 0; }
    .pt-catatan-selesai {
        flex-shrink: 0; margin-left: 4px; padding: 1px 8px; border-radius: 999px; border: 1px solid #fcd34d;
        background: #fff; color: #92400e; font-size: .7rem; font-weight: 700; line-height: 1.6; cursor: pointer;
    }
    .pt-catatan-selesai:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; }
    .pt-catatan-status { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; padding: 8px 0 2px; }
    .pt-catatan-status small { color: var(--dsb-redup); font-size: .74rem; }
    /* Nominal tidak dipatah, tetapi rincian di bawahnya boleh turun baris
       supaya tabel tidak melebar dan menutup kolom lain. */
    .pt-detail .items-table td[data-judul="Subtotal"] { min-width: 132px; max-width: 170px; white-space: normal; }
    .pt-harga-total { font-weight: 800; }
    .pt-harga-ket { display: block; margin-top: 2px; font-size: .72rem !important; color: #6b7280; font-weight: 500; }
    .pt-harga-ket s { color: #94a3b8; }
    .pt-hemat { color: #16a34a; font-weight: 800; white-space: nowrap; }
    .pt-harga-ket s { white-space: nowrap; }
    .pt-detail .items-table td[data-judul="Status"] small,
    .pt-detail .items-table td[data-judul="Masa Aktif"] small { font-size: .72rem !important; }
    .pt-detail .items-table .badge {
        display: inline-flex; align-items: center; gap: 4px; padding: .3em .7em; border-radius: 999px;
        font-size: .7rem; font-weight: 700; border: 1px solid transparent !important; text-decoration: none;
    }
    .pt-detail .items-table .badge.bg-success { background: #dcfce7 !important; color: #15803d !important; border-color: #bbf7d0 !important; }
    .pt-detail .items-table .badge.bg-warning { background: #fef3c7 !important; color: #b45309 !important; border-color: #fde68a !important; }
    .pt-detail .items-table .badge.bg-info { background: #e0f2fe !important; color: #0369a1 !important; border-color: #bae6fd !important; }
    .pt-detail .items-table .badge.bg-primary { background: #ede9fe !important; color: #6d28d9 !important; border-color: #ddd6fe !important; }
    .pt-detail .items-table .badge.bg-danger { background: #fee2e2 !important; color: #b91c1c !important; border-color: #fecaca !important; }
    .pt-detail .items-table .badge.bg-secondary { background: #f1f5f9 !important; color: #475569 !important; border-color: #e2e8f0 !important; }
    .pt-detail .items-table .badge[class*="-subtle"] { border-color: currentColor !important; border-color: color-mix(in srgb, currentColor 30%, #fff) !important; }
    /* Tombol aksi: ubin ikon ringkas seragam */
    .pt-aksi-deret { display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
    .pt-detail .pt-sel-aksi .btn {
        width: 34px; height: 34px; padding: 0 !important; margin: 0 !important;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px; font-size: .9rem; box-shadow: none !important;
    }
    .pt-detail .pt-sel-aksi .btn i.bi, .pt-detail .pt-sel-aksi .btn i.bi::before { display: block; line-height: 1; }
    .pt-detail .pt-sel-aksi .btn-outline-primary { background: #fff; color: #6d28d9; border: 1px solid #ddd6fe; }
    .pt-detail .pt-sel-aksi .btn-primary { background: #7c3aed; color: #fff; border: 1px solid #7c3aed; }
    .pt-detail .pt-sel-aksi .btn-outline-secondary { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
    .pt-detail .pt-sel-aksi .btn-success { background: #16a34a; color: #fff; border: 1px solid #16a34a; }
    @media (hover: hover) and (pointer: fine) {
        .pt-detail .pt-sel-aksi .btn:hover { transform: translateY(-1px); }
        .pt-detail .pt-sel-aksi .btn-outline-primary:hover { background: #f5f3ff; color: #6d28d9; }
        .pt-detail .pt-sel-aksi .btn-outline-secondary:hover { background: #f8fafc; color: #1c1f26; }
    }
    /* HP & tablet: tabel tetap tabel yang digeser (permintaan sebelumnya),
       tetapi kolom Aksi menempel di kanan supaya tombol kirim akun dan
       WhatsApp selalu terlihat tanpa menggeser. */
    @media (max-width: 1199.98px) {
        .pt-detail .items-table .pt-sel-aksi,
        .pt-detail .items-table thead th:last-child {
            position: sticky; right: 0; z-index: 2; background: #fff;
            box-shadow: -10px 0 12px -10px rgba(15, 23, 42, .25);
        }
        .pt-detail .items-table thead th:last-child { background: #f8fafc; }
    }
    .pt-detail .items-table thead th:last-child { border-radius: 0 10px 10px 0; }
    .pt-detail .summary-card { background: #fff !important; border: 1px solid var(--dsb-tepi) !important; box-shadow: none !important; border-radius: 16px !important; }
    .pt-detail .summary-card .summary-total { background: #f0fdf4; border-radius: 12px; padding: 12px 14px !important; margin-top: 8px; border: 1px solid #bbf7d0; }

        .detail-info-card {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 249, 255, 0.9));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
            height: 100%;
        }

        .detail-info-card .info-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #fff;
            flex-shrink: 0;
        }

        /* Pusatkan ikon Bootstrap (bi) yang punya line-height bawaan */
        .detail-info-card .info-icon i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .detail-info-card .info-icon i.bi::before {
            display: block;
            line-height: 1;
        }

        .method-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .85rem;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 700;
            line-height: 1;
            color: #fff;
        }

        .method-chip i {
            font-size: .95rem;
            line-height: 1;
        }

        .method-flash {
            background: linear-gradient(135deg, #f43f5e, #e11d48);
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.30);
        }

        .method-promo {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 4px 12px rgba(78, 70, 229, 0.30);
        }

        .method-point {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.30);
        }

        .method-referral {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.30);
        }

        .method-none {
            background: #e2e8f0;
            color: #64748b;
        }

        .info-icon.bg-grad-purple {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 14px rgba(78, 70, 229, 0.35);
        }

        .info-icon.bg-grad-green {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 6px 14px rgba(16, 185, 129, 0.35);
        }

        .detail-info-card .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: .55rem 0;
            border-bottom: 1px dashed rgba(108, 99, 255, 0.12);
        }

        .detail-info-card .info-row:last-child {
            border-bottom: none;
        }

        .detail-info-card .info-label {
            color: #6b7280;
            font-size: .9rem;
            font-weight: 500;
        }

        .detail-info-card .info-value {
            color: #1e293b;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        .items-table thead th {
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.08));
            color: #4e46e5;
            font-weight: 700;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            border: none;
            white-space: nowrap;
        }

        .items-table tbody td {
            vertical-align: middle;
        }

        .summary-card {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.95));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }

        .summary-card .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .5rem 0;
            font-size: .95rem;
            color: #475569;
        }

        .summary-card .summary-total {
            border-top: 2px dashed rgba(108, 99, 255, 0.20);
            margin-top: .35rem;
            padding-top: .85rem;
        }

        /* ===== Mobile (HP) — rapikan info-card agar tak sempit ===== */
        @media (max-width: 575.98px) {
            .detail-info-card { padding: 1.15rem !important; }

            /* Label di atas, nilai di bawah (rata kiri) — tidak lagi berdesakan
               di satu baris pada layar sempit. */
            .detail-info-card .info-row {
                flex-direction: column;
                align-items: stretch;
                gap: .15rem;
                padding: .5rem 0;
            }
            .detail-info-card .info-label { font-size: .78rem; }
            .detail-info-card .info-value {
                text-align: left;
                width: 100%;
                font-size: .9rem;
                min-width: 0;
            }
            /* Nilai berupa flex (status + tombol batal, chip diskon) → rata kiri
               & boleh membungkus rapi. */
            .detail-info-card .info-value.d-flex {
                justify-content: flex-start !important;
            }

            /* Tombol "Batalkan Pesanan": kotak ringkas sendiri di bawah badge status. */
            .detail-info-card .info-value .pcek-konfirmasi {
                display: flex;
                width: fit-content;
                margin: .55rem 0 0 0 !important;
                padding: 7px 14px !important;
                font-size: .76rem !important;
                border-radius: 10px;
                gap: 6px;
            }

            /* Tombol "Selesaikan Pesanan" jangan menggantung sempit → full-width. */
            .pcek .pcek-finish .pcek-btn { width: 100%; }

            /* Semua kartu bagian lebih rapat & tidak makan tempat. */
            .card > .card-body { padding: 1.15rem !important; }

            /* Item Pesanan: dibiarkan seperti semula (tabel biasa, bisa digeser
               horizontal via .table-responsive) sesuai permintaan. */
            /* Ikon header "Item Pesanan" sejajar vertikal dengan teksnya. */
            .card > .card-body > .d-flex.align-items-center > i.bi {
                line-height: 1;
                display: inline-flex;
                align-items: center;
                align-self: center;
            }
            /* Harga & Subtotal: "Rp" + angka satu baris (sejajar), tidak wrap. */
            .items-table .text-end { white-space: nowrap; }

            /* Ringkasan biaya: TOTAL jadi KOTAK hijau menonjol — label kecil di
               atas, angka besar di bawah. Rapi & estetik, tak menempel. */
            .summary-card .summary-row { font-size: .9rem; gap: 10px; }
            .summary-card .summary-total {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 3px;
                background: #ecfdf5;
                border: 1px solid #d1fae5;
                border-radius: 14px;
                border-top: none !important;
                padding: 14px 16px !important;
                margin-top: 12px;
            }
            .summary-card .summary-total > span:first-child {
                font-size: .72rem !important;
                text-transform: uppercase;
                letter-spacing: .04em;
                font-weight: 700;
                color: #059669 !important;
            }
            .summary-card .summary-total .fs-5 { font-size: 1.55rem !important; }

            /* Header kartu "Pengecekan Plagiasi": rapi — ikon & judul kiri, badge
               "N sisa" di kanan-ATAS sejajar; deskripsi lebih kecil. */
            .pcek-head-row { flex-wrap: nowrap; align-items: flex-start; gap: 12px !important; }
            .pcek .pcek-head-ic { align-self: flex-start; }
            .pcek-head-row .flex-grow-1 { min-width: 0; }
            .pcek-head-row .flex-grow-1 h5 { font-size: 1rem; }
            .pcek-head-row .flex-grow-1 small { font-size: .72rem; line-height: 1.35; }
            .pcek-head-row > .badge {
                margin: 2px 0 0 auto;
                flex-shrink: 0;
                align-self: flex-start;
            }

            /* Header file pengecekan: badge status ("Selesai") di ATAS, SEJAJAR
               ikon (baris 1: ikon kiri, badge kanan); nama file + meta full-width
               di baris 2. */
            .pcek-file-row { flex-wrap: wrap; align-items: center; }
            .pcek-file-row > .pcek-fileic { order: 1; }
            .pcek-file-row > .pcek-status { order: 2; margin: 0 0 0 auto !important; }
            .pcek-file-row > .flex-grow-1 { order: 3; width: 100%; margin-top: .55rem; }
            .pcek-file-row .text-muted.d-inline-flex { font-size: .74rem !important; }

            /* Tombol aksi pengecekan (File Customer / Hasil / Ganti) → full-width. */
            .pcek .pcek-actions { flex-direction: column; align-items: stretch; }
            .pcek .pcek-actions .pcek-btn { width: 100%; }

            /* ============ POLES DESAIN MOBILE (bersih, modern, ala iOS) ============ */
            /* Semua kartu: sudut lebih membulat + bayangan lembut seragam. */
            .card.rounded-4 {
                border-radius: 20px !important;
                box-shadow: 0 6px 20px rgba(15, 23, 42, .06) !important;
            }

            /* Kartu info: latar PUTIH bersih (bukan gradient ungu), border halus,
               tak lagi terlihat "template". */
            .detail-info-card {
                background: #fff !important;
                border: 1px solid #eef0f6 !important;
                border-radius: 20px !important;
                box-shadow: 0 6px 20px rgba(15, 23, 42, .06) !important;
            }
            .detail-info-card .info-icon {
                width: 38px;
                height: 38px;
                border-radius: 11px;
                font-size: 1.02rem;
            }

            /* Baris info gaya iOS-settings: garis TIPIS SOLID (bukan dashed),
               label KECIL-KAPITAL-muted, nilai tegas & jelas. */
            .detail-info-card .info-row {
                border-bottom: 1px solid #f1f5f9;
                padding: .72rem 0;
            }
            .detail-info-card .info-label {
                text-transform: uppercase;
                letter-spacing: .04em;
                font-size: .67rem !important;
                font-weight: 700;
                color: #94a3b8;
                margin-bottom: .1rem;
            }
            .detail-info-card .info-value {
                font-size: .95rem !important;
                font-weight: 600;
                color: #1e293b;
            }

            /* Judul section (h5) seragam & proporsional di HP. */
            .card-body h5.fw-bold { font-size: 1.02rem; }

            /* Ikon kotak header section (Pengecekan/Item) tak terlalu besar. */
            .pcek .pcek-head-ic { width: 40px; height: 40px; border-radius: 12px; font-size: 1.15rem; }

            /* Kartu file pengecekan: sudut & bayangan lembut, seragam iOS. */
            .pcek .pcek-item { border-radius: 16px; box-shadow: 0 2px 12px rgba(15, 23, 42, .05); }
        }
    </style>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="detail-info-card p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="info-icon bg-grad-purple"><i class="bi bi-receipt"></i></span>
                    <h5 class="fw-bold mb-0">Pembayaran &amp; Catatan</h5>
                </div>
                {{-- Nomor, tanggal, status, dan metode bayar ada di kepala halaman;
                     total ada di ringkasan biaya di bawah tabel item. --}}
                <div class="info-row">
                    <span class="info-label">Dibayar</span>
                    <span class="info-value">
                        {{ $order->paid_at ? $order->paid_at->locale('id')->translatedFormat('d M Y, H:i') : 'Belum ada pembayaran tercatat' }}
                    </span>
                </div>
                {{-- Nomor pencocokan dari penyedia QRIS: dipakai saat mencocokkan
                     mutasi. Hanya tampil bila ada (transfer manual tidak punya). --}}
                @if ($hdRefBayar)
                <div class="info-row">
                    <span class="info-label">Referensi Pembayaran</span>
                    <span class="info-value pt-kode">{{ $hdRefBayar }}</span>
                </div>
                @endif
                @if ($hdIdQris)
                <div class="info-row">
                    <span class="info-label">ID Transaksi QRIS</span>
                    <span class="info-value pt-kode">{{ $hdIdQris }}</span>
                </div>
                @endif
                @if ($hdBarisBukti)
                {{-- Bukti pembayaran: kotak sendiri (bukan di sisi kanan baris), supaya
                     thumbnail, keadaan berkas, dan tombolnya punya ruang. --}}
                <div class="pt-bukti {{ $hdBuktiTercatat && ! $hdBuktiTersedia ? 'is-gagal' : '' }} {{ $hdBuktiTercatat ? '' : 'is-kosong' }}">
                    <div class="pt-bukti-gambar">
                        @if ($hdBuktiTersedia)
                            <button type="button" class="bukti-zoom-trigger pt-bukti-thumb"
                                data-bukti-url="{{ route('admin.pesanantoko.bukti', $order) }}" title="Perbesar bukti pembayaran">
                                {{-- onerror: bila berkas gagal dimuat di peramban, tampilkan keadaan gagal. --}}
                                <img src="{{ route('admin.pesanantoko.bukti', $order) }}" alt="Bukti pembayaran" loading="lazy"
                                    onerror="this.closest('.pt-bukti').classList.add('is-gagal')">
                            </button>
                        @endif
                        <span class="pt-bukti-ikon pt-bukti-ikon-gagal"><i class="bi bi-file-earmark-x"></i></span>
                        <span class="pt-bukti-ikon pt-bukti-ikon-kosong"><i class="bi bi-image"></i></span>
                    </div>
                    <div class="pt-bukti-teks">
                        <span class="pt-bukti-judul">Bukti Pembayaran</span>
                        <span class="pt-bukti-ket pt-bukti-ket-ada">Klik gambar untuk memperbesar</span>
                        <span class="pt-bukti-ket pt-bukti-ket-gagal">Berkas tercatat, tetapi tidak ditemukan di server{{ $hdBolehGanti ? ' — unggah ulang' : '' }}</span>
                        <span class="pt-bukti-ket pt-bukti-ket-kosong">Belum ada bukti yang diunggah</span>
                    </div>
                    <div class="pt-bukti-aksi">
                        @if ($hdBuktiTersedia)
                            <a href="{{ route('admin.pesanantoko.bukti', $order) }}" target="_blank" rel="noopener"
                                class="dsb-tabel-btn pt-bukti-buka" title="Buka di tab baru" aria-label="Buka bukti di tab baru">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        @endif
                        @if ($hdBolehGanti)
                            {{-- Halaman unggah bukti yang sama dengan alur draft, supaya
                                 satu pekerjaan tidak punya dua tampilan. --}}
                            <a href="{{ route('admin.pesanantoko.unggah-bukti', $order) }}" class="dsb-tombol is-lembut is-mungil">
                                <i class="bi {{ $hdBuktiTercatat ? 'bi-arrow-repeat' : 'bi-upload' }}"></i>
                                <span>{{ $hdBuktiTercatat ? 'Ganti' : 'Unggah' }}</span>
                            </a>
                        @endif
                    </div>
                </div>
                @endif
                @php
                $promos = collect($order->applied_promos ?? []);
                $usedFlash = $promos->contains(fn($p) => ($p['tipe_promo'] ?? '') === 'flash_sale');
                $usedKodePromo = $promos->contains(fn($p) => ($p['tipe_promo'] ?? '') === 'kode_promo');
                $usedAutoPromo = $promos->contains(fn($p) => ($p['tipe_promo'] ?? '') === 'auto_promo');
                // Ditulis "0 <" (bukan lebih-dari): blok ini tepat sesudah direktif
                // penutup, dan tanda lebih-dari membuat Livewire melewati penandanya.
                $usedPoint = $order->used_points || 0 < $order->points_discount;
                $usedReferral = !empty($order->referral_code) || 0 < $order->referral_discount;
                $adaDiskon = $usedFlash || $usedKodePromo || $usedAutoPromo || $usedPoint || $usedReferral;
                @endphp
                <div class="info-row">
                    <span class="info-label">Diskon Dipakai</span>
                    <span class="info-value d-flex flex-wrap gap-2 justify-content-end">
                        @if ($usedFlash)
                        <span class="method-chip method-flash"><i class="bi bi-lightning-charge-fill"></i>Flash Sale</span>
                        @endif
                        @if ($usedKodePromo)
                        <span class="method-chip method-promo"><i class="bi bi-ticket-perforated-fill"></i>Kode Promo</span>
                        @endif
                        @if ($usedAutoPromo)
                        <span class="method-chip method-promo"><i class="bi bi-tags-fill"></i>Promo</span>
                        @endif
                        @if ($usedPoint)
                        <span class="method-chip method-point"><i class="bi bi-coin"></i>Poin</span>
                        @endif
                        @if ($usedReferral)
                        <span class="method-chip method-referral"><i class="bi bi-people-fill"></i>Referral</span>
                        @endif
                        @unless ($adaDiskon)
                        <span class="method-chip method-none"><i class="bi bi-dash-circle"></i>Tanpa Diskon</span>
                        @endunless
                    </span>
                </div>
                @if ($usedReferral && !empty($order->referral_code))
                <div class="info-row">
                    <span class="info-label">Kode Referral</span>
                    <span class="info-value">{{ $order->referral_code }}</span>
                </div>
                @endif
                @if ($usedKodePromo)
                <div class="info-row">
                    <span class="info-label">Kode Promo</span>
                    <span class="info-value">
                        {{ collect($order->getAppliedPromoCodes())->filter()->implode(', ') ?: '-' }}
                    </span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Catatan Pelanggan</span>
                    <span class="info-value">
                        @if (filled($order->customer_notes))
                        {{ $order->customer_notes }}
                        @else
                        <span class="text-muted fw-normal">- tidak ada -</span>
                        @endif
                    </span>
                </div>
                {{-- Catatan pelanggan tidak dihapus (bagian data pesanan); cukup
                     ditandai ditangani supaya hilang dari tab "Ada Catatan". --}}
                @if (filled($order->customer_notes))
                <div class="pt-catatan-status">
                    @if ($hdCatatanDitangani)
                        <span class="dsb-lencana is-hijau"><i class="bi bi-check2-circle"></i>Sudah ditangani</span>
                        <small>{{ $order->penanganCatatan->name ?? 'Admin' }} · {{ $hdCatatanDitangani->locale('id')->translatedFormat('d M Y, H:i') }}</small>
                    @elseif ($hdBolehUbah)
                        <span class="dsb-lencana is-kuning"><i class="bi bi-exclamation-circle"></i>Belum ditangani</span>
                        <button type="button" class="dsb-tombol is-lembut is-mungil pcek-konfirmasi"
                            data-action="tandaiCatatanDitangani"
                            data-title="Tandai catatan pelanggan sudah ditangani?"
                            data-text="Catatannya tetap tersimpan, hanya tidak lagi muncul di tab Ada Catatan."
                            data-confirm="Ya, sudah ditangani"
                            data-icon="question">
                            <i class="bi bi-check2"></i><span>Tandai ditangani</span>
                        </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="detail-info-card p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="info-icon bg-grad-green"><i class="bi bi-person-circle"></i></span>
                    <h5 class="fw-bold mb-0">Data Pembeli</h5>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">{{ $order->customer->nama ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $order->customer->email ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Telepon</span>
                    <span class="info-value">{{ $order->customer->no_hp ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status Member</span>
                    <span class="info-value">
                        <span class="dsb-lencana {{ $pblMember ? 'is-hijau' : 'is-abu' }}">
                            <i class="bi {{ $pblMember ? 'bi-patch-check-fill' : 'bi-person' }}"></i>{{ $pblMember ? 'Member aktif' : 'Non-member' }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Poin</span>
                    <span class="info-value">{{ number_format((int) ($pbl->point ?? 0), 0, ',', '.') }} poin</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pesanan Dibayar</span>
                    <span class="info-value">{{ $pblPesanan }} pesanan</span>
                </div>

                {{-- Tombol hubungi pembeli. --}}
                <div class="pt-pembeli-aksi">
                    @if ($pblAdaWa)
                        <a href="https://api.whatsapp.com/send?phone={{ $pblWa }}" target="_blank" rel="noopener" class="dsb-tombol is-lembut is-mungil pt-tombol-wa">
                            <i class="bi bi-whatsapp"></i><span>Chat WhatsApp</span>
                        </a>
                    @endif
                    @if ($pblBolehLihat)
                        <a wire:navigate href="{{ route('admin.customer.show', $pbl) }}" class="dsb-tombol is-lembut is-mungil">
                            <i class="bi bi-person-lines-fill"></i><span>Lihat Pelanggan</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Pengecekan plagiasi (pesanan JASA) ===== --}}
    @if ($order->butuhUpload())
    @php
        // Yang dilihat admin adalah PEKERJAAN yang harus diserahkan — dokumen
        // parafrase, hasil plagiasi, dan hasil AI masing-masing satu. Bukan
        // jumlah dokumen yang dikirim pelanggan; itu urusan halaman /cek.
        $jKuota = $order->kuotaPengecekan();
        $jTerpakai = $order->pekerjaanTerserah();
        $jSisa = $order->sisaKuota();
        // Bonus kuota dari admin (kompensasi bila customer terkendala).
        $jBonus = $order->bonusKuotaPerJenis();
        $jBonusTotal = $order->bonusKuota();
        $jJenisBonus = $this->jenisBonusTersedia();
        // Dihitung sekali: dua kondisi "count(...)" dalam satu berkas membuat
        // Livewire salah memasang penanda morph pada yang kedua.
        $jJumlahJenisBonus = count($jJenisBonus);
        // Judul & ikon mengikuti jasa yang dibeli (parafrase / cek plagiasi / cek AI).
        $jJudul = $order->items
            ->filter(fn ($it) => $it->product && $it->product->butuh_file)
            ->map(fn ($it) => $it->product_name ?: $it->product->nama_akun)
            ->filter()->unique()->implode(' · ') ?: 'Pengecekan Dokumen';
        $jIkon = in_array('parafrase', $jJenisBonus, true) ? 'bi-pencil-square'
            : ($jJenisBonus === ['ai'] ? 'bi-robot' : 'bi-shield-check');
        $jPersen = $jKuota ? min(100, (int) round($jTerpakai / $jKuota * 100)) : 0;
        $jAdaSisa = 0 < $jSisa;
        $jRiwayatBonus = $order->riwayatBonusKuota();
        $jTampilBonus = $jBonusTotal || $bonusBuka || ! empty($jRiwayatBonus);
        $jBisaSelesai = $order->status !== 'completed' && $order->uploads->where('status', 'selesai')->isNotEmpty();
        $jJumlahBerkas = $order->uploads->count();
        $jLabelJenis = [
            'ai' => 'Cek AI',
            'plagiasi' => 'Cek Plagiasi',
            'parafrase' => 'Parafrase',
            'pengecekan' => 'Pengecekan',
        ];
    @endphp
    <style>
        /* Ikon sejajar teks di seluruh blok pengecekan */
        .pcek i.bi { line-height: 1; vertical-align: -.075em; }
        .pcek .pcek-head-ic { width: 44px; height: 44px; border-radius: 13px; display: flex; align-items: center; justify-content: center; background: #fff7ed; color: #ea580c; font-size: 1.3rem; flex-shrink: 0; }
        .pcek .pcek-fileic { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; background: #fff7ed; color: #ea580c; font-size: 1.2rem; flex-shrink: 0; }
        /* Ikon di dalam kotak: pusatkan penuh. Glyph bootstrap-icons dirender via
           ::before, jadi jadikan <i> flex + ::before block agar benar-benar center. */
        .pcek .pcek-head-ic i.bi, .pcek .pcek-fileic i.bi { display: flex; align-items: center; justify-content: center; width: 1em; height: 1em; line-height: 1; vertical-align: 0; }
        .pcek .pcek-head-ic i.bi::before, .pcek .pcek-fileic i.bi::before { display: block; line-height: 1; margin: 0; }
        .pcek .pcek-link-box { display: flex; align-items: stretch; border: 1px solid #e2e8f0; border-radius: 11px; overflow: hidden; background: #fff; }
        .pcek .pcek-link-box input { border: 0; background: #f8fafc; font-size: .82rem; color: #475569; padding: .55rem .8rem; flex: 1; min-width: 0; outline: none; }
        .pcek .pcek-link-box button { border: 0; border-left: 1px solid #e2e8f0; background: #fff; color: #ea580c; font-weight: 600; font-size: .82rem; padding: .55rem 1rem; white-space: nowrap; display: inline-flex; align-items: center; gap: .4rem; transition: background .15s; }
        .pcek .pcek-link-box button:hover { background: #fff7ed; }
        .pcek .pcek-item { border: 1px solid #eef0f6; border-radius: 15px; padding: 1rem 1.1rem; background: #fff; transition: box-shadow .2s, border-color .2s; }
        .pcek .pcek-item:hover { box-shadow: 0 6px 18px rgba(15, 23, 42, .06); border-color: #e2e8f0; }
        /* Panel bonus kuota (kompensasi admin) */
        .pcek .pcek-bonus { padding: 13px 15px; margin-bottom: 16px; border: 1px solid #fde68a; border-radius: 13px; background: linear-gradient(180deg, #fffbeb, #fff); }
        .pcek .pcek-bonus-head { display: flex; flex-wrap: wrap; align-items: center; gap: 11px; }
        .pcek .pcek-bonus-ic { width: 36px; height: 36px; flex-shrink: 0; border-radius: 11px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1.02rem; }
        .pcek .pcek-bonus-ic i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; }
        .pcek .pcek-bonus-ic i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-bonus-head b { display: block; font-size: .85rem; color: #92400e; }
        .pcek .pcek-bonus-head small { display: block; font-size: .76rem; color: #64748b; line-height: 1.45; margin-top: 2px; }
        .pcek .pcek-bonus-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 11px; }
        .pcek .pcek-bonus-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 6px 5px 12px; border-radius: 99px; background: #fef3c7; color: #92400e; font-size: .78rem; font-weight: 700; }
        .pcek .pcek-bonus-x { width: 20px; height: 20px; border: 0; border-radius: 50%; background: rgba(180, 83, 9, .12); color: #b45309; display: flex; align-items: center; justify-content: center; font-size: .62rem; cursor: pointer; }
        .pcek .pcek-bonus-x:hover { background: rgba(180, 83, 9, .25); }
        .pcek .pcek-bonus-x i.bi { display: flex; line-height: 1; }
        .pcek .pcek-bonus-form { margin-top: 12px; padding-top: 12px; border-top: 1px dashed #fde68a; }
        .pcek .pcek-bonus-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .pcek .pcek-bonus-f { flex: 1 1 180px; min-width: 0; }
        .pcek .pcek-bonus-f.narrow { flex: 0 0 110px; }
        .pcek .pcek-bonus-f label { display: block; font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; margin-bottom: 5px; }
        .pcek .pcek-bonus-f select, .pcek .pcek-bonus-f input { width: 100%; height: 38px; padding: 0 11px; font-size: .84rem; color: #334155; border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; outline: none; transition: border-color .18s, box-shadow .18s; }
        .pcek .pcek-bonus-f select:focus, .pcek .pcek-bonus-f input:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, .15); }
        .pcek .pcek-bonus-err { display: block; margin-top: 5px; font-size: .74rem; color: #dc2626; }
        .pcek .pcek-bonus-note { display: flex; align-items: flex-start; gap: 7px; margin-top: 10px; font-size: .76rem; color: #b45309; line-height: 1.5; }
        .pcek .pcek-bonus-note i.bi { flex-shrink: 0; margin-top: .12rem; display: flex; line-height: 1; }
        .pcek .pcek-bonus-btns { display: flex; gap: 8px; margin-top: 12px; }
        .pcek .pcek-btn.warn { background: #f59e0b; color: #fff; }
        .pcek .pcek-btn.warn:hover { background: #d97706; color: #fff; }
        .pcek .pcek-bonus-log { margin-top: 11px; padding-top: 10px; border-top: 1px dashed #fde68a; font-size: .75rem; color: #64748b; line-height: 1.6; }
        .pcek .pcek-bonus-log div { display: flex; gap: 6px; }
        .pcek .pcek-bonus-log i.bi { flex-shrink: 0; margin-top: .22rem; display: flex; line-height: 1; color: #cbd5e1; }
        @media (max-width: 479px) {
            .pcek .pcek-bonus-f.narrow { flex: 1 1 100%; }
            .pcek .pcek-bonus-btns .pcek-btn { flex: 1; }
        }
        /* Keterangan saat berkasnya sudah dihapus otomatis */
        .pcek-berkas-hilang {
            display: flex; gap: 9px; align-items: flex-start; margin-top: 10px;
            padding: 9px 12px; border-radius: 12px; font-size: .8rem; line-height: 1.5;
            background: #f8fafc; border: 1px solid #e6eaf0; color: #475569;
        }
        .pcek-berkas-hilang > i { font-size: .95rem; line-height: 1.3; color: #94a3b8; }
        .pcek-berkas-hilang b { color: #334155; }
        .pcek-status { flex-shrink: 0; }
        .pcek-persen-lencana { font-size: .8rem; padding: 5px 11px; }

        /* Siapa yang mengerjakan: bot Turnitin / admin */
        .pcek-pj { display: flex; align-items: center; gap: 12px; margin-top: 12px; padding: 10px 12px; border-radius: 13px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .pcek-pj-ic { flex: 0 0 36px; width: 36px; height: 36px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.05rem; color: #fff; background: linear-gradient(135deg, #94a3b8, #64748b); }
        .pcek-pj-teks { flex: 1 1 auto; min-width: 0; font-size: .8rem; line-height: 1.45; color: #475569; }
        .pcek-pj-teks small { display: block; font-size: .66rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
        .pcek-pj-teks b { display: block; font-size: .86rem; font-weight: 700; color: #1e293b; }
        .pcek-pj-teks span { display: block; }
        .pcek-pj-teks a { font-weight: 700; }
        .pcek-pj-aksi { flex: 0 0 auto; display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }
        .pcek-pj.is-biru { background: #eff6ff; border-color: #bfdbfe; }
        .pcek-pj.is-biru .pcek-pj-ic { background: linear-gradient(135deg, #60a5fa, #2563eb); }
        .pcek-pj.is-biru small { color: #2563eb; }
        .pcek-pj.is-hijau { background: #ecfdf5; border-color: #bbf7d0; }
        .pcek-pj.is-hijau .pcek-pj-ic { background: linear-gradient(135deg, #34d399, #059669); }
        .pcek-pj.is-hijau small { color: #059669; }
        .pcek-pj.is-kuning { background: #fffbeb; border-color: #fde68a; }
        .pcek-pj.is-kuning .pcek-pj-ic { background: linear-gradient(135deg, #fbbf24, #d97706); }
        .pcek-pj.is-kuning small { color: #b45309; }
        .pcek-pj.is-merah { background: #fef2f2; border-color: #fecaca; }
        .pcek-pj.is-merah .pcek-pj-ic { background: linear-gradient(135deg, #f87171, #dc2626); }
        .pcek-pj.is-merah small { color: #dc2626; }
        .pcek-pj.is-abu small { color: #64748b; }
        @media (max-width: 575.98px) {
            .pcek-pj { flex-wrap: wrap; align-items: flex-start; }
            .pcek-pj-teks { flex-basis: calc(100% - 48px); }
            .pcek-pj-aksi { flex-basis: 100%; justify-content: stretch; }
            .pcek-pj-aksi .pcek-btn { flex: 1 1 auto; justify-content: center; }
        }


        /* Setelan exclude + catatan customer */
        .pcek .pcek-set { margin-top: 12px; padding-top: 12px; border-top: 1px dashed #eef0f6; }
        .pcek .pcek-set-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .pcek .pcek-set-lbl { display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0; font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; }
        .pcek .pcek-set-lbl i.bi { display: flex; align-items: center; line-height: 1; font-size: .82rem; }
        .pcek .pcek-set-lbl i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-set-vals { display: flex; flex-wrap: wrap; gap: 6px; }
        .pcek .pcek-c { display: inline-flex; align-items: center; padding: 4px 11px; border-radius: 99px; background: #eef2ff; color: #4338ca; font-size: .76rem; font-weight: 600; white-space: nowrap; }
        .pcek .pcek-c.off { background: #f1f5f9; color: #94a3b8; font-weight: 500; }
        .pcek .pcek-note { display: flex; align-items: flex-start; gap: 8px; margin-top: 10px; padding: 9px 12px; border-left: 3px solid #cbd5e1; border-radius: 0 9px 9px 0; background: #f8fafc; font-size: .8rem; color: #475569; line-height: 1.5; }
        .pcek .pcek-note i.bi { flex-shrink: 0; margin-top: .15rem; color: #94a3b8; display: flex; line-height: 1; }
        .pcek .pcek-note i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-note b { color: #334155; font-weight: 700; }
        /* Baris aksi — satu sistem tombol, tinggi & radius seragam */
        .pcek .pcek-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9; }
        .pcek .pcek-push { margin-left: auto; }
        .pcek .pcek-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 14px; border: 1px solid transparent; border-radius: 9px; font-size: .82rem; font-weight: 600; line-height: 1; white-space: nowrap; cursor: pointer; text-decoration: none; transition: background .16s, border-color .16s, color .16s; }
        .pcek .pcek-btn i.bi { font-size: .92rem; display: flex; align-items: center; line-height: 1; }
        .pcek .pcek-btn i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-btn:disabled { opacity: .6; cursor: not-allowed; }
        .pcek .pcek-btn.ghost { background: #fff; border-color: #e2e8f0; color: #475569; }
        .pcek .pcek-btn.ghost:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        .pcek .pcek-btn.primary { background: #4e46e5; color: #fff; }
        .pcek .pcek-btn.primary:hover { background: #4338ca; color: #fff; }
        .pcek .pcek-btn.success { background: #16a34a; color: #fff; }
        .pcek .pcek-btn.success:hover { background: #15803d; color: #fff; }
        .pcek .pcek-btn.danger { background: #fff; border-color: #fecaca; color: #dc2626; }
        .pcek .pcek-btn.danger:hover { background: #fef2f2; border-color: #fca5a5; }
        /* Form unggah hasil */
        .pcek .pcek-form { margin-top: 14px; padding: 15px; border: 1px solid #d1fae5; border-radius: 14px; background: linear-gradient(180deg, #f6fefa, #fff); }
        /* Dropzone hasil (admin) */
        .pcek .pcek-drop { position: relative; display: block; padding: 16px 14px; border: 2px dashed #bbf7d0; border-radius: 12px; background: #fff; cursor: pointer; text-align: center; transition: border-color .18s, background .18s; }
        .pcek .pcek-drop:hover { border-color: #4ade80; background: #f6fefa; }
        .pcek .pcek-drop-input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .pcek .pcek-drop-state { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px; }
        .pcek .pcek-drop-state i.bi { display: flex; align-items: center; line-height: 1; font-size: 1.15rem; }
        .pcek .pcek-drop-state i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-drop-state i.bi.up { color: #16a34a; }
        .pcek .pcek-drop-state i.bi.ok { color: #16a34a; }
        .pcek .pcek-drop-state .nm { font-size: .84rem; font-weight: 600; color: #334155; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pcek .pcek-drop-state .hint { font-size: .74rem; color: #94a3b8; width: 100%; }
        .pcek .pcek-drop-state .chg { font-size: .72rem; font-weight: 700; color: #15803d; background: #dcfce7; padding: 3px 9px; border-radius: 99px; }
        .pcek .pcek-spin { animation: pcekSpin 1s linear infinite; color: #16a34a; }
        /* Keadaan memuat: TANPA properti display — Livewire yang menampilkannya
           (wire:loading.flex / .inline-flex) hanya selama unggah/simpan berjalan. */
        .pcek .pcek-drop-muat { flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px; width: 100%; }
        .pcek .pcek-drop-muat .nm { font-size: .84rem; font-weight: 600; color: #334155; }
        .pcek .pcek-drop-muat i.bi { display: flex; line-height: 1; font-size: 1.15rem; }
        .pcek .pcek-isi { display: inline-flex; align-items: center; gap: 6px; }
        .pcek .pcek-isi-muat { align-items: center; gap: 8px; }
        .pcek-putar {
            display: inline-block; width: 14px; height: 14px; border-radius: 50%;
            border: 2px solid currentColor; border-right-color: transparent;
            animation: pcekSpin .7s linear infinite; flex-shrink: 0;
        }
        @media (prefers-reduced-motion: reduce) { .pcek-putar { animation-duration: 2s; } }
        @keyframes pcekSpin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .pcek .pcek-spin { animation: none; } }
        /* Baris bawah form: persen di kiri { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; margin-top: 14px; }
        @media (max-width: 479px) {
            .pcek .pcek-persen { width: 100%; }
            .pcek .pcek-persen-wrap { max-width: none; }
        }
        /* Baris aksi form hasil — dua tombol mengisi penuh, tanpa ruang kosong */
        .pcek .pcek-aksi { display: flex; gap: 8px; margin-top: 14px; }
        .pcek .pcek-aksi .pcek-btn { flex: 1; }
        .pcek .pcek-aksi .pcek-btn.success { flex: 2; }

        /* Slot berkas hasil (plagiasi / AI / dokumen) */
        .pcek .pcek-slot { display: flex; gap: 11px; padding: 13px; margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; }
        .pcek .pcek-slot-no { width: 24px; height: 24px; flex-shrink: 0; border-radius: 50%; background: #dcfce7; color: #15803d; font-size: .76rem; font-weight: 800; display: flex; align-items: center; justify-content: center; }
        .pcek .pcek-slot-body { flex: 1; min-width: 0; }
        .pcek .pcek-slot-lbl { display: block; font-size: .82rem; font-weight: 700; color: #334155; margin-bottom: 6px; }
        .pcek .pcek-slot-lbl span { font-weight: 500; color: #94a3b8; font-size: .72rem; }
        .pcek .pcek-slot .pcek-drop { padding: 12px 10px; }
        .pcek .pcek-slot .pcek-persen-wrap { max-width: 150px; }

        /* Persen kemiripan */
        .pcek .pcek-lbl { display: block; font-size: .78rem; font-weight: 700; color: #334155; margin-bottom: 6px; }
        .pcek .pcek-lbl span { font-weight: 500; color: #94a3b8; font-size: .72rem; }
        .pcek .pcek-persen-wrap { position: relative; max-width: 180px; }
        .pcek .pcek-persen-num { width: 100%; height: 40px; padding: 0 46px 0 13px; font-size: .95rem; font-weight: 700; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; outline: none; transition: border-color .18s, box-shadow .18s; }
        .pcek .pcek-persen-num:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, .13); }
        .pcek .pcek-persen-suffix { position: absolute; top: 50%; right: 5px; transform: translateY(-50%); min-width: 34px; text-align: center; padding: 6px 8px; border-radius: 8px; background: #dcfce7; color: #15803d; font-size: .82rem; font-weight: 700; pointer-events: none; }
        .pcek .pcek-auto { display: flex; align-items: flex-start; gap: 7px; margin-top: 8px; font-size: .76rem; color: #15803d; line-height: 1.45; }
        .pcek .pcek-manual { display: flex; align-items: flex-start; gap: 7px; margin-top: 8px; font-size: .76rem; color: #b45309; line-height: 1.45; }
        .pcek .pcek-pilih { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
        .pcek .pcek-pilih-btn { padding: 3px 12px; border-radius: 99px; border: 1px solid #fbbf24;
            background: #fffbeb; color: #b45309; font-size: .78rem; font-weight: 700; cursor: pointer; }
        .pcek .pcek-pilih-btn:hover { background: #fde68a; }
        .pcek .pcek-auto i.bi { flex-shrink: 0; margin-top: .12rem; display: flex; line-height: 1; }
        .pcek .pcek-auto i.bi::before { display: block; line-height: 1; }
    </style>
    <div class="card border-0 shadow-sm rounded-4 mb-4 pcek" id="pengecekan">
        <div class="card-body p-4">
            {{-- Header: nama jasa yang dibeli, bukan judul umum. --}}
            <div class="d-flex align-items-center gap-3 pcek-head-row">
                <div class="pcek-head-ic"><i class="bi {{ $jIkon }}"></i></div>
                <div class="flex-grow-1" style="min-width:0;">
                    <span class="pcek-kicker">Jasa pengecekan</span>
                    <h5 class="fw-bold mb-0">{{ $jJudul }}</h5>
                    <small class="text-muted">Dokumen dari customer, hasil pengecekan, dan kuotanya</small>
                </div>
            </div>

            {{-- Kuota: tiga angka + garis kemajuan --}}
            <div class="pcek-kuota">
                <div class="pcek-kuota-angka">
                    <div><span>Kuota</span><b>{{ $jKuota }}</b></div>
                    <div><span>Terpakai</span><b>{{ $jTerpakai }}</b></div>
                    <div class="{{ $jAdaSisa ? 'is-sisa' : 'is-habis' }}"><span>Sisa</span><b>{{ $jSisa }}</b></div>
                </div>
                <div class="pcek-kuota-garis"><span style="width: {{ $jPersen }}%"></span></div>
                @if ($jBonusTotal)
                <small class="pcek-kuota-ket"><i class="bi bi-gift"></i> Termasuk {{ $jBonusTotal }} bonus dari admin</small>
                @endif
            </div>

            {{-- Satu baris alat: link customer, pengingat, bonus, penyelesaian. --}}
            <div class="pcek-alat">
                <div class="pcek-link-box">
                    <span class="pcek-link-ic" title="Link customer (bila lupa / hilang)"><i class="bi bi-link-45deg"></i></span>
                    <input type="text" id="cust-cek-link" readonly value="{{ url('/cek/'.$order->share_token) }}" aria-label="Link pengecekan customer">
                    <button type="button" onclick="salinLinkCek()"><i class="bi bi-clipboard"></i> Salin</button>
                </div>
                <div class="pcek-alat-tombol">
                    {{-- Ingatkan customer selagi kuotanya masih ada: link /cek mati
                         24 jam setelah hasil terakhir diserahkan, jadi sisa kuota yang
                         terlupa berujung keluhan. Kosong bila kuota habis / tanpa nomor. --}}
                    @if ($this->waKuotaLink)
                    <a href="{{ $this->waKuotaLink }}" target="_blank" rel="noopener" class="pcek-btn wa">
                        <i class="bi bi-whatsapp"></i> Ingatkan sisa {{ $jSisa }}
                    </a>
                    @endif
                    @if (! $bonusBuka)
                    <button type="button" wire:click="bukaBonusKuota" class="pcek-btn ghost">
                        <i class="bi bi-gift"></i> Tambah Bonus
                    </button>
                    @endif
                    {{-- Penyelesaian manual: bila customer tak memakai seluruh kuota,
                         supaya omset tetap masuk cash flow & tak menggantung. --}}
                    @if ($jBisaSelesai)
                    <button type="button" class="pcek-btn primary pcek-konfirmasi"
                        data-action="selesaikanJasa"
                        data-title="Selesaikan pesanan jasa?"
                        data-text="{{ $jAdaSisa ? 'Masih ada '.$jSisa.' kuota tersisa. ' : '' }}Item akan ditandai terkirim dan omset dicatat ke cash flow."
                        data-confirm="Ya, selesaikan"
                        data-icon="question">
                        <i class="bi bi-check2-circle"></i> Selesaikan Pesanan
                    </button>
                    @endif
                </div>
            </div>

            {{-- ===== Bonus kuota (kompensasi bila customer terkendala) =====
                 Aditif: hanya menambah kuota pengecekan, tidak mengubah total
                 harga, status pembayaran, maupun cash flow pesanan. --}}
            @if ($jTampilBonus)
            <div class="pcek-bonus">
                <div class="pcek-bonus-head">
                    <span class="pcek-bonus-ic"><i class="bi bi-gift"></i></span>
                    <div class="flex-grow-1" style="min-width:0;">
                        <b>Bonus Kuota Pengecekan</b>
                        <small>Kuota tambahan gratis bila pengecekan bermasalah. Sisa kuota di link customer langsung bertambah.</small>
                    </div>
                    <span class="pcek-bonus-sisa">Sisa sekarang: {{ $jSisa }}</span>
                </div>

                {{-- Bonus yang sedang berlaku --}}
                @if ($jBonusTotal > 0)
                <div class="pcek-bonus-chips">
                    @foreach ($jBonus as $bJenis => $bJumlah)
                    <span class="pcek-bonus-chip" wire:key="bonus-chip-{{ $bJenis }}">
                        +{{ $bJumlah }} {{ $jLabelJenis[$bJenis] ?? ucfirst($bJenis) }}
                        <button type="button" class="pcek-bonus-x pcek-konfirmasi" title="Batalkan bonus ini"
                            data-action="hapusBonusKuota"
                            data-arg="{{ $bJenis }}"
                            data-title="Batalkan bonus {{ $jLabelJenis[$bJenis] ?? $bJenis }}?"
                            data-text="Kuota bonus yang sudah terlanjur dipakai customer tidak bisa ditarik kembali."
                            data-confirm="Ya, batalkan"
                            data-icon="warning">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </span>
                    @endforeach
                </div>
                @endif

                {{-- Form pemberian bonus --}}
                @if ($bonusBuka)
                <div class="pcek-bonus-form">
                    <div class="pcek-bonus-grid">
                        @if (1 < $jJumlahJenisBonus)
                        <div class="pcek-bonus-f">
                            <label for="bonus-jenis">Jenis pemeriksaan</label>
                            <select id="bonus-jenis" wire:model="bonusJenis">
                                <option value="">— pilih jenis —</option>
                                @foreach ($jJenisBonus as $bj)
                                <option value="{{ $bj }}">{{ $jLabelJenis[$bj] ?? ucfirst($bj) }}</option>
                                @endforeach
                            </select>
                            @error('bonusJenis') <span class="pcek-bonus-err">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="pcek-bonus-f narrow">
                            <label for="bonus-jumlah">Jumlah kuota</label>
                            <input type="number" id="bonus-jumlah" min="1" max="20" wire:model="bonusJumlah" placeholder="1">
                            @error('bonusJumlah') <span class="pcek-bonus-err">{{ $message }}</span> @enderror
                        </div>

                        <div class="pcek-bonus-f">
                            <label for="bonus-alasan">Alasan <span style="text-transform:none; font-weight:500;">(opsional)</span></label>
                            <input type="text" id="bonus-alasan" maxlength="200" placeholder="mis. hasil Turnitin gagal terbaca" wire:model="bonusAlasan">
                            @error('bonusAlasan') <span class="pcek-bonus-err">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if ($jJumlahJenisBonus === 1)
                    <div class="pcek-bonus-note">
                        <i class="bi bi-info-circle"></i>
                        <span>Bonus diberikan untuk <b>{{ $jLabelJenis[$jJenisBonus[0]] ?? ucfirst($jJenisBonus[0]) }}</b> — satu-satunya jenis pemeriksaan pada pesanan ini.</span>
                    </div>
                    @endif

                    @if ($order->status === 'completed')
                    <div class="pcek-bonus-note">
                        <i class="bi bi-unlock"></i>
                        <span>Pesanan ini sudah <b>selesai</b>. Dengan bonus, link customer terbuka kembali agar kuotanya bisa dipakai — status pesanan &amp; omset tidak berubah.</span>
                    </div>
                    @endif

                    <div class="pcek-bonus-btns">
                        <button type="button" wire:click="tutupBonusKuota" class="pcek-btn ghost">
                            Batal
                        </button>
                        <button type="button" wire:click="simpanBonusKuota" wire:loading.attr="disabled" wire:target="simpanBonusKuota" class="pcek-btn warn">
                            <span wire:loading.remove wire:target="simpanBonusKuota" class="pcek-isi"><i class="bi bi-check2"></i> Simpan Bonus</span>
                            <span wire:loading.inline-flex wire:target="simpanBonusKuota" class="pcek-isi-muat"><span class="pcek-putar"></span> Menyimpan…</span>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Jejak pemberian bonus --}}
                @if (! empty($jRiwayatBonus))
                <div class="pcek-bonus-log">
                    @foreach (array_reverse($jRiwayatBonus) as $log)
                    <div>
                        <i class="bi bi-dot"></i>
                        <span>
                            <b>{{ ($log['jumlah'] ?? 0) > 0 ? '+' : '' }}{{ $log['jumlah'] ?? 0 }}</b>
                            {{ $jLabelJenis[$log['jenis'] ?? ''] ?? ($log['jenis'] ?? '-') }}
                            @if (! empty($log['oleh'])) &middot; oleh {{ $log['oleh'] }} @endif
                            @if (! empty($log['at'])) &middot; {{ \Illuminate\Support\Carbon::parse($log['at'])->translatedFormat('d M Y H:i') }} @endif
                            @if (! empty($log['alasan'])) <br><span style="color:#94a3b8;">"{{ $log['alasan'] }}"</span> @endif
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            {{-- Daftar dokumen --}}
            <div class="pcek-daftar-kepala">
                <b>Dokumen customer</b>
                <span>{{ $jJumlahBerkas }} berkas · terbaru di atas</span>
            </div>
            <div class="pcek-daftar {{ $jJumlahBerkas === 1 ? 'is-tunggal' : '' }}">
            @forelse ($order->uploads->sortByDesc('created_at') as $up)
            <div class="pcek-item" wire:key="adm-up-{{ $up->id }}">
                <div class="d-flex align-items-start gap-3 pcek-file-row">
                    <div class="pcek-fileic"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold text-dark text-truncate" title="{{ $up->nama_asli }}">{{ $up->nama_asli }}</div>
                        <div class="text-muted d-inline-flex align-items-center gap-2 flex-wrap" style="font-size:.8rem;">
                            @if ($up->jenisLabel())
                            <span class="dsb-lencana {{ $lencanaBs[$up->jenisWarna()] ?? 'is-abu' }}">{{ $up->jenisLabel() }}</span>
                            @endif
                            <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $up->created_at->locale('id')->translatedFormat('d M Y H:i') }}</span>
                            <span class="text-secondary">&middot;</span>
                            <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-hdd"></i> {{ $up->ukuranLabel() }}</span>
                        </div>
                    </div>
                    <span class="dsb-lencana pcek-status {{ $lencanaBs[$up->statusWarna()] ?? 'is-abu' }}">
                        <i class="bi {{ $up->statusIcon() }}"></i> {{ $up->statusLabel() }}
                    </span>
                </div>

                {{-- Siapa yang mengerjakan: bot Turnitin atau admin (BotTurnitin::pengerja) --}}
                @php
                    $pj = \App\Support\BotTurnitin::skemaSiap() ? \App\Support\BotTurnitin::pengerja($up) : null;
                    $pjAksi = $pj ? $pj['aksi'] : [];
                    $pjBotBekerja = $pj && $pj['bot_bekerja'];
                    $pjAntreBot = $pj && $pj['antre_bot'];
                @endphp
                @if ($pj)
                <div class="pcek-pj is-{{ $pj['nada'] }}">
                    <span class="pcek-pj-ic"><i class="bi {{ $pj['ikon'] }}"></i></span>
                    <div class="pcek-pj-teks">
                        <small>{{ $pj['pelaku'] === 'bot' ? 'Bot Turnitin' : 'Admin' }}</small>
                        <b>{{ $pj['judul'] }}</b>
                        @if ($pj['ket'])
                        <span>{{ $pj['ket'] }}</span>
                        @endif
                        @if ($up->bot_kode)
                        <span>Kode submitin:
                            <a href="https://submitin.id/status?order={{ urlencode($up->bot_kode) }}" target="_blank" rel="noopener">{{ $up->bot_kode }}</a>
                        </span>
                        @endif
                    </div>
                    @if ($pjAksi)
                    <div class="pcek-pj-aksi">
                        @foreach ($pjAksi as $aksi)
                        @if ($aksi === 'coba_lagi')
                        <button type="button" class="pcek-btn ghost pcek-konfirmasi"
                            data-action="cobaLagiBot" data-arg="{{ $up->id }}"
                            data-title="Serahkan lagi ke bot?"
                            data-text="Dokumen ini masuk antrean bot lagi dan dikerjakan otomatis."
                            data-confirm="Ya, serahkan ke bot" data-icon="question">
                            <i class="bi bi-arrow-repeat"></i> Coba Lagi pakai Bot
                        </button>
                        @else
                        <button type="button" class="pcek-btn ghost pcek-konfirmasi"
                            data-action="ambilAlihBot" data-arg="{{ $up->id }}"
                            data-title="Ambil alih dari bot?"
                            data-text="Bot berhenti mengerjakan dokumen ini. Anda yang mengunggah hasilnya."
                            data-confirm="Ya, saya kerjakan" data-icon="warning">
                            <i class="bi bi-person-check"></i> Ambil Alih
                        </button>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif

                {{-- Setelan exclude + catatan dari customer --}}
                <div class="pcek-set">
                    <div class="pcek-set-row">
                        <span class="pcek-set-lbl"><i class="bi bi-sliders"></i> Kecualikan</span>
                        <span class="pcek-set-vals">
                            @forelse ($up->daftarExclude() as $ex)
                            <span class="pcek-c">{{ $ex }}</span>
                            @empty
                            <span class="pcek-c off">Tidak ada</span>
                            @endforelse
                        </span>
                    </div>

                    @if ($up->catatan)
                    <div class="pcek-note">
                        <i class="bi bi-chat-left-quote"></i>
                        <span><b>Catatan customer:</b> {{ $up->catatan }}</span>
                    </div>
                    @endif
                </div>

                {{-- Persen kemiripan bila sudah selesai --}}
                @if ($up->status === 'selesai' && (! is_null($up->persentase) || $up->labelPersenAi()))
                <div class="mt-2 d-flex flex-wrap gap-1">
                    @if (! is_null($up->persentase))
                    <span class="dsb-lencana is-ungu pcek-persen-lencana">
                        <i class="bi bi-graph-up"></i> Plagiasi: {{ $up->persentase }}%
                    </span>
                    @endif
                    @if ($up->labelPersenAi())
                    <span class="dsb-lencana is-biru pcek-persen-lencana">
                        <i class="bi bi-robot"></i> AI: {{ $up->labelPersenAi() }}
                    </span>
                    @endif
                </div>
                @endif

                {{-- Aksi — satu aksi utama per status, sisanya netral; destruktif di kanan --}}
                <div class="pcek-actions">
                    @php
                        // Berkas pelanggan dihapus otomatis 30 hari setelah
                        // pekerjaannya rampung (jasa:hapus-berkas-kadaluarsa).
                        // Tanpa penjagaan ini tombolnya tetap tampil dan
                        // menjanjikan unduhan yang berujung 404.
                        $adaBerkasPelanggan = (bool) $up->path;
                        // Sekali di sini: kondisi in_array(...) yang sama dua kali dalam
                        // satu berkas membuat Livewire salah memasang penanda morph.
                        $upBerjalan = in_array($up->status, ['menunggu', 'diproses'], true);
                        $adaHasil = $up->hasil_path || $up->hasil_ai_path || $up->hasil_docx_path;
                        // Bot sedang bekerja: tombol unggah tetap ada, tapi tidak mencolok.
                        $pjTombolUtama = $up->status === 'diproses' && ! $pjBotBekerja;
                    @endphp
                    @if ($adaBerkasPelanggan)
                    @if ($up->perlu_diringkas)
                    {{-- DOCX melewati batas unggah Groupy: gambar dimampatkan, teks utuh --}}
                    <a href="{{ route('admin.jasa.berkas', $up) }}" class="pcek-btn ghost"
                       title="Gambar dimampatkan agar muat batas Groupy. Teks tidak berubah.">
                        <i class="bi bi-file-earmark-zip"></i> File Customer (ringkas)
                    </a>
                    <a href="{{ route('admin.jasa.berkas', ['upload' => $up, 'asli' => 1]) }}"
                       class="pcek-btn ghost" title="Berkas apa adanya dari customer">
                        <i class="bi bi-download"></i> Asli
                    </a>
                    @else
                    <a href="{{ route('admin.jasa.berkas', $up) }}" class="pcek-btn ghost">
                        <i class="bi bi-download"></i> File Customer
                    </a>
                    @endif
                    @endif
                    @if ($up->pdf_path)
                    {{-- Parafrase: PDF acuan jumlah halaman (file utama = DOCX kerja) --}}
                    <a href="{{ route('admin.jasa.pdf', $up) }}" class="pcek-btn ghost" title="PDF acuan jumlah halaman">
                        <i class="bi bi-filetype-pdf"></i> PDF Acuan
                    </a>
                    @endif

                    @if ($up->status === 'menunggu')
                    <button type="button" wire:click="mulaiProses('{{ $up->id }}')" wire:loading.attr="disabled" wire:target="mulaiProses('{{ $up->id }}')" class="pcek-btn {{ $pjAntreBot ? 'ghost' : 'primary' }}">
                        <span wire:loading.remove wire:target="mulaiProses('{{ $up->id }}')" class="pcek-isi"><i class="bi {{ $pjAntreBot ? 'bi-person-check' : 'bi-play-fill' }}"></i> {{ $pjAntreBot ? 'Kerjakan Sendiri' : 'Mulai Proses' }}</span>
                        <span wire:loading.inline-flex wire:target="mulaiProses('{{ $up->id }}')" class="pcek-isi-muat"><span class="pcek-putar"></span> Memulai…</span>
                    </button>
                    @endif

                    @if ($upBerjalan)
                    <button type="button" wire:click="bukaUploadHasil('{{ $up->id }}')"
                        class="pcek-btn {{ $pjTombolUtama ? 'primary' : 'ghost' }}">
                        <i class="bi bi-cloud-arrow-up"></i> Unggah Hasil
                    </button>
                    @endif

                    @if ($up->status === 'selesai')
                    @if ($up->hasil_path)
                    <a href="{{ route('admin.jasa.hasil', $up) }}" class="pcek-btn primary">
                        <i class="bi bi-file-earmark-check"></i> Hasil Plagiasi
                    </a>
                    @endif
                    @if ($up->hasil_ai_path)
                    <a href="{{ route('admin.jasa.hasil-ai', $up) }}" class="pcek-btn ghost">
                        <i class="bi bi-robot"></i> Hasil AI
                    </a>
                    @endif
                    @if ($up->hasil_docx_path)
                    <a href="{{ route('admin.jasa.hasil-docx', $up) }}" class="pcek-btn ghost">
                        <i class="bi bi-file-earmark-word"></i> Dokumen Hasil
                    </a>
                    @endif
                    <button type="button" wire:click="bukaUploadHasil('{{ $up->id }}')" class="pcek-btn ghost">
                        <i class="bi bi-arrow-repeat"></i> Ganti Hasil
                    </button>
                    @endif

                    {{-- Satu baris dengan aksi lain, di ujung kanan (tidak memakan baris sendiri). --}}
                    @if ($upBerjalan)
                    <button type="button" class="pcek-btn danger pcek-push pcek-konfirmasi" title="Batalkan pengecekan"
                        data-action="batalkanPengecekan"
                        data-arg="{{ $up->id }}"
                        data-title="Batalkan pengecekan ini?"
                        data-text="Kuota customer akan dikembalikan."
                        data-confirm="Ya, batalkan"
                        data-icon="warning">
                        <i class="bi bi-x-lg"></i> Batalkan
                    </button>
                    @endif
                </div>

                {{-- Kenapa tidak ada yang bisa diunduh.

                     Tanpa kalimat ini, layar ini hanya memperlihatkan tombol
                     yang HILANG — dan yang membacanya menyimpulkan halamannya
                     rusak, lalu mencari bug yang tidak ada. --}}
                @php
                    $hasilHilang = ! $adaHasil && $up->status === 'selesai';
                    $naskahHilang = ! $adaBerkasPelanggan;
                @endphp
                @if ($naskahHilang || $hasilHilang)
                <div class="pcek-berkas-hilang">
                    <i class="bi bi-archive"></i>
                    <span>
                        @if ($naskahHilang && $hasilHilang)
                            {{-- Keduanya lenyap: korban aturan lama yang membuang
                                 naskah DAN hasil sekaligus, 7 hari setelah link
                                 /cek mati — dihitung dari tanggal unggah, bukan
                                 dari tanggal hasil diserahkan. --}}
                            <b>Naskah customer dan berkas hasil sudah tidak tersimpan.</b>
                            Keduanya terhapus oleh aturan lama yang membuang semua berkas jasa sekaligus.
                            Sejak 14 Sep 2026 berkas hasil disimpan permanen, dan naskah customer baru
                            dihapus 30 hari setelah pekerjaannya rampung.
                            Kalau salinan hasilnya masih ada, unggah ulang lewat <b>Ganti Hasil</b>;
                            kalau tidak, minta customer mengirim ulang naskahnya lewat link di atas —
                            kuotanya masih tersisa.
                        @elseif ($hasilHilang)
                            <b>Berkas hasil sudah tidak tersimpan.</b>
                            Pesanan lama terkena aturan penghapusan yang dulu ikut membuang berkas hasil.
                            Sejak 14 Sep 2026 berkas hasil disimpan permanen — kalau hasilnya masih ada,
                            unggah ulang lewat <b>Ganti Hasil</b> agar customer bisa mengunduhnya lagi.
                        @else
                            <b>Naskah customer sudah dihapus otomatis</b> (30 hari setelah pekerjaannya rampung).
                            Berkas hasil tidak ikut dihapus.
                        @endif
                    </span>
                </div>
                @endif


                {{-- Form unggah hasil: JENDELA tersendiri, bukan di dalam kartu.
                     Di dalam kartu, status "Selesai" + hasil lama + form baru
                     bercampur jadi satu dan membingungkan. --}}
                @if ($uploadAktifId === $up->id)
                @php $upGanti = $up->status === 'selesai'; @endphp
                <div class="ts-modal-back" wire:click="tutupUploadHasil"></div>
                <div class="ts-modal" wire:key="adm-hasilform-{{ $up->id }}">
                <div class="ts-modal-card dsb is-datar pcek pcek-jendela" role="dialog" aria-modal="true"
                    aria-label="Unggah hasil pengecekan" tabindex="-1"
                    x-on:keydown.escape.window="$wire.tutupUploadHasil()">
                    <div class="dsb-jendela-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: {{ $upGanti ? '#d97706' : '#16a34a' }}"><i class="bi {{ $upGanti ? 'bi-arrow-repeat' : 'bi-cloud-arrow-up' }}"></i></span>
                        <span class="dsb-jendela-teks">
                            <h5 class="dsb-jendela-judul">{{ $upGanti ? 'Ganti Hasil Pengecekan' : 'Unggah Hasil Pengecekan' }}</h5>
                            <span class="dsb-kartu-sub pcek-jendela-berkas" title="{{ $up->nama_asli }}"><i class="bi bi-file-earmark-text"></i> {{ $up->nama_asli }}</span>
                        </span>
                        <button type="button" class="dsb-jendela-tutup" wire:click="tutupUploadHasil" title="Tutup"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <div class="dsb-jendela-isi pcek-form">
                    @if ($upGanti)
                        <div class="pcek-jendela-info">
                            <i class="bi bi-info-circle"></i>
                            <span>Berkas ini sudah punya hasil. Berkas yang Anda unggah di sini <b>menggantikan</b> hasil lama; slot yang tidak diisi tetap memakai berkas lama.</span>
                        </div>
                    @endif

                    {{-- Hasil cek PLAGIASI — hanya bila layanannya memang dibeli --}}
                    @if ($this->slotTampil('plagiasi'))
                    <div class="pcek-slot is-plagiasi">
                        <span class="pcek-slot-no">{{ $this->nomorSlot('plagiasi') }}</span>
                        <div class="pcek-slot-body">
                            <label class="pcek-slot-lbl"><i class="bi bi-shield-check"></i> Hasil Cek Plagiasi (Turnitin) <span>PDF / DOCX</span></label>
                            <div class="pcek-slot-isi">
                            <div class="pcek-slot-berkas">
                            <label class="pcek-drop">
                                <input type="file" wire:model="hasilFile" accept=".pdf,.docx" class="pcek-drop-input">
                                <span wire:loading.flex wire:target="hasilFile" class="pcek-drop-muat">
                                    <i class="bi bi-arrow-repeat pcek-spin"></i>
                                    <span class="nm">Mengunggah &amp; membaca…</span>
                                </span>
                                <span wire:loading.remove wire:target="hasilFile" class="pcek-drop-state">
                                    @if ($hasilFile)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $hasilFile->getClientOriginalName() }}</span>
                                    <span class="chg">Ganti</span>
                                    @elseif ($up->hasil_nama)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $up->hasil_nama }}</span>
                                    <span class="chg">Ganti</span>
                                    @else
                                    <i class="bi bi-cloud-arrow-up up"></i>
                                    <span class="nm">Pilih file atau seret ke sini</span>
                                    <span class="hint">Persen terbaca otomatis dari laporan bila bisa</span>
                                    @endif
                                </span>
                            </label>
                            @error('hasilFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="pcek-persen">
                                <label class="pcek-lbl">Persen plagiasi <span>boleh dikosongkan</span></label>
                                <div class="pcek-persen-wrap">
                                    <input type="number" min="0" max="100" wire:model="persentaseInput" class="pcek-persen-num" placeholder="23">
                                    <span class="pcek-persen-suffix">%</span>
                                </div>
                                @if ($persenTerbacaOtomatis)
                                <div class="pcek-auto"><i class="bi bi-magic"></i><span>Terbaca otomatis — mohon dicek.</span></div>
                                @elseif ($persenGagalBaca)
                                <div class="pcek-manual"><i class="bi bi-pencil-square"></i><span>Persen tak terbaca dari berkas ini — isi manual dari PDF.</span></div>
                                @endif
                                @error('persentaseInput') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            </div>{{-- /.pcek-slot-isi --}}
                        </div>
                    </div>

                    @endif

                    {{-- Hasil cek AI — hanya bila layanannya memang dibeli --}}
                    @if ($this->slotTampil('ai'))
                    <div class="pcek-slot is-ai">
                        <span class="pcek-slot-no">{{ $this->nomorSlot('ai') }}</span>
                        <div class="pcek-slot-body">
                            <label class="pcek-slot-lbl"><i class="bi bi-robot"></i> Hasil Cek AI <span>PDF</span></label>
                            <div class="pcek-slot-isi">
                            <div class="pcek-slot-berkas">
                            <label class="pcek-drop">
                                <input type="file" wire:model="hasilAiFile" accept=".pdf" class="pcek-drop-input">
                                <span wire:loading.flex wire:target="hasilAiFile" class="pcek-drop-muat">
                                    <i class="bi bi-arrow-repeat pcek-spin"></i>
                                    <span class="nm">Mengunggah &amp; membaca…</span>
                                </span>
                                <span wire:loading.remove wire:target="hasilAiFile" class="pcek-drop-state">
                                    @if ($hasilAiFile)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $hasilAiFile->getClientOriginalName() }}</span>
                                    <span class="chg">Ganti</span>
                                    @elseif ($up->hasil_ai_nama)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $up->hasil_ai_nama }}</span>
                                    <span class="chg">Ganti</span>
                                    @else
                                    <i class="bi bi-cloud-arrow-up up"></i>
                                    <span class="nm">Pilih file atau seret ke sini</span>
                                    <span class="hint">Persen AI terbaca otomatis dari laporan bila bisa</span>
                                    @endif
                                </span>
                            </label>
                            @error('hasilAiFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="pcek-persen">
                                <label class="pcek-lbl">Persen AI <span>boleh dikosongkan</span></label>
                                <div class="pcek-persen-wrap">
                                    <input type="number" min="0" max="100" wire:model="persentaseAiInput" class="pcek-persen-num"
                                        placeholder="{{ $aiBawahAmbang ? 'tidak disebut Turnitin' : '8' }}"
                                        @disabled($aiBawahAmbang)>
                                    <span class="pcek-persen-suffix">%</span>
                                </div>
                                @if ($persenAiTerbacaOtomatis)
                                <div class="pcek-auto">
                                    <i class="bi bi-magic"></i>
                                    <span>
                                        Terbaca otomatis dari <b>{{ $sumberAi === 'gptzero' ? 'GPTZero' : 'Turnitin' }}</b>
                                        ({{ $labelAi ?? 'Persen AI' }}) — mohon dicek.
                                        @if ($sumberAi === 'gptzero')
                                        <br><span class="text-muted">Catatan: GPTZero melaporkan <i>probabilitas dokumen dibuat AI</i>, bukan persentase teks AI.</span>
                                        @endif
                                    </span>
                                </div>
                                @elseif (count($pilihanAi) > 1)
                                <div class="pcek-manual">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>
                                        Berkas memuat <b>{{ count($pilihanAi) }} laporan bertumpuk</b>
                                        ({{ implode('%, ', $pilihanAi) }}%). Diisi <b>{{ end($pilihanAi) }}%</b> —
                                        nilai lapisan teratas, yang tampak saat PDF dibuka.
                                        Nilai lain tersembunyi di bawahnya; ganti bila perlu:
                                        <span class="pcek-pilih">
                                            @foreach ($pilihanAi as $nilai)
                                            <button type="button" class="pcek-pilih-btn"
                                                wire:click="$set('persentaseAiInput', {{ $nilai }})">{{ $nilai }}%</button>
                                            @endforeach
                                        </span>
                                    </span>
                                </div>
                                @elseif ($persenAiGagalBaca)
                                <div class="pcek-manual"><i class="bi bi-pencil-square"></i><span>Format laporan tak dikenali — isi persen manual dari PDF.</span></div>
                                @endif
                                @if ($aiBawahAmbang)
                                {{-- Turnitin menolak menyebut angkanya, jadi isian dikunci:
                                     mengetik tebakan di sini akan berbeda dari PDF yang
                                     dibaca pelanggan sendiri. --}}
                                <div class="small mt-1" style="color:#0369a1;">
                                    <i class="bi bi-info-circle"></i>
                                    Turnitin menulis <b>*%</b> — skornya di bawah {{ \App\Models\OrderUpload::AMBANG_AI }}% dan sengaja tidak dirinci.
                                    Pelanggan akan melihat &ldquo;di bawah {{ \App\Models\OrderUpload::AMBANG_AI }}%&rdquo;.
                                </div>
                                @endif
                                @error('persentaseAiInput') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            </div>{{-- /.pcek-slot-isi --}}
                        </div>
                    </div>

                    @endif

                    {{-- Dokumen hasil parafrase — khusus jasa per halaman --}}
                    @if ($this->slotTampil('docx'))
                    <div class="pcek-slot is-docx">
                        <span class="pcek-slot-no">{{ $this->nomorSlot('docx') }}</span>
                        <div class="pcek-slot-body">
                            <label class="pcek-slot-lbl"><i class="bi bi-pencil-square"></i> Dokumen Hasil Parafrase <span>DOCX</span></label>
                            <label class="pcek-drop">
                                <input type="file" wire:model="hasilDocxFile" accept=".docx" class="pcek-drop-input">
                                <span wire:loading.flex wire:target="hasilDocxFile" class="pcek-drop-muat">
                                    <i class="bi bi-arrow-repeat pcek-spin"></i>
                                    <span class="nm">Mengunggah…</span>
                                </span>
                                <span wire:loading.remove wire:target="hasilDocxFile" class="pcek-drop-state">
                                    @if ($hasilDocxFile)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $hasilDocxFile->getClientOriginalName() }}</span>
                                    <span class="chg">Ganti</span>
                                    @elseif ($up->hasil_docx_nama)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $up->hasil_docx_nama }}</span>
                                    <span class="chg">Ganti</span>
                                    @else
                                    <i class="bi bi-cloud-arrow-up up"></i>
                                    <span class="nm">Pilih file atau seret ke sini</span>
                                    @endif
                                </span>
                            </label>
                            @error('hasilDocxFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @endif

                    </div>{{-- /.dsb-jendela-isi --}}

                    <div class="dsb-jendela-kaki">
                        <span class="dsb-jendela-kaki-ket">Hasil langsung bisa diunduh customer setelah disimpan.</span>
                    <div class="pcek-aksi">
                        <button type="button" wire:click="tutupUploadHasil" class="pcek-btn ghost">Batal</button>
                        <button type="button" wire:click="simpanHasil" wire:loading.attr="disabled"
                            wire:target="simpanHasil,hasilFile,hasilAiFile,hasilDocxFile" class="pcek-btn success">
                            {{-- Tanpa kelas display (mis. d-inline-flex yang !important): kelas
                                 seperti itu mengalahkan penyembunyi Livewire, sehingga kedua
                                 keadaan tampil bersamaan. Tampilan diatur .inline-flex. --}}
                            <span wire:loading.remove wire:target="simpanHasil" class="pcek-isi"><i class="bi bi-check-lg"></i> Simpan Hasil</span>
                            <span wire:loading.inline-flex wire:target="simpanHasil" class="pcek-isi-muat"><span class="pcek-putar"></span> Menyimpan…</span>
                        </button>
                    </div>
                    </div>{{-- /.dsb-jendela-kaki --}}
                </div>
                </div>
                @endif
            </div>
            @empty
            <div class="pcek-kosong">
                <i class="bi bi-inbox"></i>
                <div class="fw-semibold text-dark">Belum ada dokumen</div>
                <small>Customer mengunggah lewat link pengecekan di atas.</small>
            </div>
            @endforelse
            </div>{{-- /.pcek-daftar --}}
        </div>
    </div>
    @endif

    <div class="dsb-kepala pt-kepala-sisip" style="--c: #16a34a">
        <span class="dsb-kepala-ikon"><i class="bi bi-box-seam-fill"></i></span>
        <div class="dsb-kepala-teks">
            <span class="dsb-kicker">Item</span>
            <h2 class="dsb-judul">Item Pesanan</h2>
            <div class="dsb-chip-deret">
                <span class="dsb-chip"><i class="bi bi-box-seam"></i>{{ $hdJumlahItem }} produk</span>
                <span class="dsb-chip is-samar">{{ $hdSemuaJasa ? 'Hasil jasa dikirim lewat bagian Pengecekan di atas' : 'Kirim akun & kabari pelanggan dari kolom Aksi' }}</span>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-4 pt-item-kartu">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle items-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Masa Aktif</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                        <tr>
                            <td class="fw-semibold pt-sel-produk">
                                {{-- Salinan nama saat dipesan lebih dipercaya daripada relasi:
                                     item paket bundling menyimpan nama "[Paket] Produk", dan
                                     baris lama tetap benar walau produknya kelak diganti nama. --}}
                                {{ $item->product_name ?: ($item->product->nama_akun ?? '-') }}
                                {{-- Add-on & jumlah halaman (khusus produk jasa) --}}
                                @if (! empty($item->addons) || $item->jumlah_halaman)
                                {{-- Rata kiri, seragam dengan badge ebook/bonus di kolom yang sama --}}
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @if ($item->jumlah_halaman)
                                    <span class="badge bg-info-subtle text-info border border-info rounded-pill" style="font-size:.68rem;">
                                        <i class="bi bi-file-earmark-text"></i>
                                        {{ $item->halaman_dihitung ?? $item->jumlah_halaman }} dari {{ $item->jumlah_halaman }} halaman
                                    </span>
                                    @endif
                                    @if ($item->halaman_dikecualikan)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill" style="font-size:.68rem;"
                                        title="Halaman ini TIDAK perlu dikerjakan">
                                        <i class="bi bi-slash-circle"></i> Lewati hal. {{ $item->halaman_dikecualikan }}
                                    </span>
                                    @endif
                                    @foreach (($item->addons ?? []) as $ad)
                                    <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill" style="font-size:.68rem;"
                                        title="Tambahan Rp {{ number_format($ad['harga'] ?? 0, 0, ',', '.') }}">
                                        <i class="bi bi-plus-circle"></i> {{ $ad['nama'] ?? '-' }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                                @if ($item->ebooks->count() || $item->bonus_description)
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @foreach ($item->ebooks as $eb)
                                    <a href="{{ $eb->getViewUrl() }}" target="_blank"
                                        class="badge bg-success-subtle text-success border border-success text-decoration-none"
                                        title="Baca {{ $eb->judul }} (view-only)">
                                        <i class="bi bi-book"></i> {{ $eb->judul }}
                                    </a>
                                    @endforeach
                                    @if ($item->bonus_description)
                                    <span class="badge bg-warning-subtle text-warning border border-warning">
                                        <i class="bi bi-gift"></i> {{ $item->bonus_description }}
                                    </span>
                                    @endif
                                </div>
                                @endif
                                {{-- Catatan langsung terlihat, tanpa membuka popup catatan satu per satu. --}}
                                @if (filled($item->processing_notes))
                                <div class="pt-catatan is-internal" title="{{ $item->processing_notes }}">
                                    <i class="bi bi-lock-fill"></i>
                                    <span><b>Internal:</b> {{ \Illuminate\Support\Str::limit($item->processing_notes, 160) }}</span>
                                    @if ($hdBolehUbah)
                                    <button type="button" class="pt-catatan-selesai pcek-konfirmasi" title="Sudah dikerjakan — hapus catatan"
                                        data-action="hapusCatatanInternal"
                                        data-arg="{{ $item->id }}"
                                        data-title="Catatan internal sudah dikerjakan?"
                                        data-text="Catatan internal item ini akan dihapus."
                                        data-confirm="Ya, hapus catatan"
                                        data-icon="question">
                                        <i class="bi bi-check2"></i> Selesai
                                    </button>
                                    @endif
                                </div>
                                @endif
                                @if (filled($item->account_notes))
                                <div class="pt-catatan is-pelanggan" title="{{ $item->account_notes }}">
                                    <i class="bi bi-chat-heart"></i>
                                    <span><b>Untuk pelanggan:</b> {{ \Illuminate\Support\Str::limit($item->account_notes, 160) }}</span>
                                </div>
                                @endif
                            </td>
                            <td class="text-center" data-judul="Durasi">
                                {{-- Paket bundling tidak punya durasi tunggal:
                                     durasinya melekat pada tiap produk di dalamnya. --}}
                                @if ($item->duration_type)
                                    {{ $item->duration_value }} {{ $item->duration_type }}
                                @else
                                    <span class="text-muted">Paket</span>
                                @endif
                                @if ($item->hasBonusDuration())
                                <small class="d-block text-success fw-semibold">+ {{ $item->bonus_duration_value }} {{ $item->bonus_duration_type }} bonus</small>
                                @endif
                                {{-- Jumlah hanya ditulis bila lebih dari satu akun; ikut dihitung di Subtotal. --}}
                                @if ($item->quantity != 1)
                                <small class="d-block text-muted">× {{ $item->quantity }} akun</small>
                                @endif
                            </td>
                            @php
                                // Harga ASLI (sebelum diskon) — dihitung dari produk; fallback ke harga tersimpan.
                                $prod = $item->product;
                                $hargaAsli = (int) $item->price;
                                if ($prod) {
                                    $inPkg = $prod->daftarHarga()->contains(fn ($r) => $r['durasi_type'] === $item->duration_type && (int) $r['durasi_value'] === (int) $item->duration_value);
                                    if ($inPkg) {
                                        $hargaAsli = (int) $prod->hargaUntuk((int) $item->duration_value, $item->duration_type);
                                    } else {
                                        $perB = (int) ($prod->harga_perbulan ?? 0);
                                        $hargaAsli = ($item->duration_type === 'bulan' && $perB > 0) ? $perB * (int) $item->duration_value : (int) $item->price;
                                    }
                                    if ($hargaAsli <= 0) {
                                        $hargaAsli = (int) $item->price;
                                    }
                                }

                                // Rincian untuk admin: harga normal per satuan waktu, lalu
                                // total = satuan × durasi — atau harga paket bila lebih murah.
                                $qty = max(1, (int) $item->quantity);
                                $durVal = max(1, (int) $item->duration_value);
                                $durTipe = $item->duration_type;
                                $satuan = 0;
                                if ($prod && $durTipe) {
                                    $satuan = (int) $prod->hargaUntuk(1, $durTipe);
                                }
                                $normal = $satuan * $durVal;
                                $pakaiPaket = $satuan && $durVal !== 1 && $hargaAsli < $normal;
                                $hemat = $pakaiPaket ? $normal - $hargaAsli : 0;
                                $tampilSatuan = $satuan ?: $hargaAsli;
                                $labelSatuan = $satuan ? 'per '.$durTipe : ($durTipe ? 'per '.$durVal.' '.$durTipe : 'per item');
                                $rp = fn ($n) => 'Rp '.number_format((int) $n, 0, ',', '.');

                                // Item JASA (cek plagiasi/AI/parafrase) tidak mengirim akun:
                                // status kirim, masa aktif, dan halaman proses akun tidak berlaku.
                                $itemJasa = (bool) ($prod && $prod->butuh_file);
                                if ($itemJasa) {
                                    $jsKuota = $jKuota ?? $order->kuotaPengecekan();
                                    $jsPakai = $jTerpakai ?? $order->pekerjaanTerserah();
                                    $jsSisa = $jSisa ?? $order->sisaKuota();
                                    $jsSelesai = $order->status === 'completed' || $item->delivery_status === 'delivered';
                                    $jsLencana = $jsSelesai ? ['is-hijau', 'bi-check2-circle', 'Selesai']
                                        : ($jsSisa ? ['is-biru', 'bi-hourglass-split', 'Berjalan'] : ['is-kuning', 'bi-hourglass-bottom', 'Kuota terpakai semua']);
                                }
                                $rumus = $pakaiPaket
                                    ? 'Harga paket '.$durVal.' '.$durTipe
                                    : ($satuan && $durVal !== 1 ? $rp($satuan).' × '.$durVal.' '.$durTipe : '');
                                if (1 < $qty) {
                                    $rumus = trim(($rumus ?: $rp($hargaAsli)).' × '.$qty.' akun');
                                }
                            @endphp
                            <td class="text-end" data-judul="Harga Satuan">
                                <span class="pt-harga">{{ $rp($tampilSatuan) }}</span>
                                <small class="pt-harga-ket">{{ $labelSatuan }}</small>
                            </td>
                            <td class="text-end" data-judul="Subtotal">
                                <span class="pt-harga pt-harga-total">{{ $rp($hargaAsli * $qty) }}</span>
                                <small class="pt-harga-ket">{{ $rumus }}</small>
                                @if ($pakaiPaket)
                                    <small class="pt-harga-ket"><s>{{ $rp($normal * $qty) }}</s> <span class="pt-hemat">hemat {{ $rp($hemat * $qty) }}</span></small>
                                @endif
                            </td>
                            @if ($itemJasa)
                            <td class="text-center" data-judul="Status">
                                <span class="dsb-lencana {{ $jsLencana[0] }}"><i class="bi {{ $jsLencana[1] }}"></i>{{ $jsLencana[2] }}</span>
                                <small class="d-block text-muted mt-1">{{ $jsPakai }} dari {{ $jsKuota }} kuota terpakai</small>
                            </td>
                            <td class="text-center" data-judul="Masa Aktif">
                                <span class="text-muted">—</span>
                                <small class="d-block text-muted">jasa, tanpa masa aktif</small>
                            </td>
                            <td class="text-center text-nowrap pt-sel-aksi" data-judul="Aksi">
                                <span class="pt-aksi-deret">
                                    {{-- Jasa dikerjakan per dokumen di bagian Pengecekan, bukan di
                                         halaman proses akun. --}}
                                    <a href="#pengecekan" class="btn btn-sm btn-primary p-2" title="Buka bagian pengecekan dokumen">
                                        <i class="bi bi-arrow-up-circle"></i>
                                    </a>
                                </span>
                            </td>
                            @else
                            <td class="text-center" data-judul="Status">
                                {!! $item->getDeliveryStatusBadge() !!}
                                @if ($item->processed_by && $item->processed_at)
                                <small class="d-block text-muted mt-1" style="line-height:1.25;">
                                    <i class="bi bi-person-check"></i> {{ $item->processedBy->name ?? 'Admin' }}
                                </small>
                                <small class="d-block text-muted" style="font-size:.72rem;">
                                    {{ \Carbon\Carbon::parse($item->processed_at)->locale('id')->translatedFormat('d M Y, H:i') }}
                                </small>
                                @endif
                            </td>
                            <td class="text-center" data-judul="Masa Aktif">
                                <div>{!! $item->getSubscriptionStatusBadge() !!}</div>
                                @if ($item->pakaiKredit())
                                <small class="d-block text-muted mt-1">Tanpa masa aktif (kredit)</small>
                                @elseif ($item->end_date)
                                <small class="d-block text-muted mt-1">
                                    s/d {{ \Carbon\Carbon::parse($item->end_date)->locale('id')->translatedFormat('d M Y') }}
                                </small>
                                <small class="d-block fw-semibold {{ $item->isHabis() ? 'text-danger' : 'text-success' }}">
                                    {{ $item->getRemainingLabel() }}
                                </small>
                                @if ($item->isHabis())
                                @if ($item->habis_notified_at)
                                <span class="badge bg-success-subtle text-success border border-success mt-1"
                                    title="Diberi tahu {{ $item->habis_notified_at->translatedFormat('d M Y H:i') }}">
                                    <i class="bi bi-check2-circle"></i> Sudah diberi tahu
                                </span>
                                @else
                                <span class="badge bg-warning-subtle text-warning border border-warning mt-1">
                                    <i class="bi bi-exclamation-circle"></i> Belum diberi tahu
                                </span>
                                @endif
                                @endif
                                @endif
                            </td>
                            <td class="text-center text-nowrap pt-sel-aksi" data-judul="Aksi">
                                <span class="pt-aksi-deret">
                                <button type="button" class="btn btn-sm btn-outline-primary p-2 notes-btn"
                                    title="lihat catatan" data-account="{{ $item->account_notes }}"
                                    data-processing="{{ $item->processing_notes }}">
                                    <i class="bi bi-journal-text"></i>
                                </button>
                                <a wire:navigate href="{{ route('admin.pesanantoko.process', $item->id) }}"
                                    class="btn btn-sm btn-primary p-2" title="proses pesanan">
                                    <i class="bi bi-gear"></i></a>
                                @if ($item->delivery_status != 'pending')
                                <button type="button" class="btn btn-sm btn-outline-secondary p-2 change-sub-btn"
                                    title="ubah status langganan" data-id="{{ $item->id }}"
                                    data-current="{{ $item->subscription_status }}">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <button class="btn btn-sm btn-success send-wa-btn p-2" title="kirim akun ke pembeli"
                                    type="button" data-id="{{ $item->id }}"
                                    data-idTransaksi="{{ $order->order_number }}"
                                    data-nama="{{ $order->customer->nama }}"
                                    data-wa="{{ $order->customer->no_hp }}"
                                    {{-- Nama PRODUK yang dibeli, bukan nama slot akun.
                                         Sebelumnya memakai dataakun->nama_akun, yaitu nama baris
                                         stok akun yang lazim bernomor ("DeepL Premium 1",
                                         "DeepL Premium 2") — nomor itu penanda stok internal dan
                                         ikut terkirim ke customer lewat WhatsApp.
                                         product_name adalah salinan nama produk saat dipesan, jadi
                                         tetap benar walau produknya kelak diganti nama atau dihapus;
                                         product->nama_akun hanya cadangan untuk baris lama.
                                         Sama dengan yang dipakai email pesanan. --}}
                                    data-akun="{{ trim($item->product_name ?: ($item->product->nama_akun ?? '-')) }}"
                                    data-tglorder="{{ $order->created_at->translatedFormat('d F Y') }}"
                                    data-total="{{ number_format($order->total, 0, ',', '.') }}"
                                    data-pemesanan="{{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d F Y') : '' }}"
                                    {{-- Dikosongkan bila memang tak ada tanggal akhir (mis. produk
                                         kredit). Carbon::parse(null) memberi HARI INI, dan pesan
                                         WhatsApp-nya lalu mengarang masa aktif yang tidak pernah ada. --}}
                                    data-berakhir="{{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d F Y') : '' }}"
                                    data-kredit="{{ $item->pakaiKredit() ? '1' : '' }}"
                                    data-jumlahkredit="{{ $item->getDurationLabel() }}"
                                    data-username="{{ $item->account_username }}"
                                    data-password="{{ $item->account_password }}"
                                    data-linkakses="{{ $item->account_link }}"
                                    data-catatan="{{ $item->account_notes }}"
                                    data-struk="{{ $order->getReceiptUrl() }}"
                                    data-durasi="{{ $item->getFullDurationLabel() }}"
                                    data-bonus="{{ $item->bonus_description }}"
                                    data-ebooks="{{ $item->ebooks->map(fn ($e) => $e->judul . ' - ' . $e->getViewUrl())->implode('||') }}">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                                @endif
                                </span>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="empty-state-icon-wrapper mb-3">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                        Belum Ada Item Pesanan
                                    </h5>
                                    <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                        Pesanan ini belum memiliki item produk.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($order->items->count())
            <div class="row justify-content-end mt-3">
                <div class="col-lg-5 col-md-7">
                    <div class="summary-card p-4">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span class="fw-semibold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($order->promo_discount > 0)
                        <div class="summary-row text-danger">
                            <span><i class="bi bi-tags-fill me-1"></i>Diskon Promo</span>
                            <span class="fw-semibold">- Rp {{ number_format($order->promo_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->points_discount > 0)
                        <div class="summary-row text-danger">
                            <span><i class="bi bi-coin me-1"></i>Diskon Poin</span>
                            <span class="fw-semibold">- Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->referral_discount > 0)
                        <div class="summary-row text-danger">
                            <span><i class="bi bi-people-fill me-1"></i>Diskon Referral</span>
                            <span class="fw-semibold">- Rp {{ number_format($order->referral_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->total_discount > 0)
                        <div class="summary-row" style="border-top:1px dashed #e5e7eb; padding-top:.5rem;">
                            <span class="fw-semibold">Setelah Diskon</span>
                            <span class="fw-semibold">Rp {{ number_format($order->subtotal - $order->total_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->unique_code > 0)
                        <div class="summary-row">
                            <span>Kode Unik <i class="bi bi-info-circle" title="Untuk verifikasi pembayaran"></i></span>
                            <span class="fw-semibold">+ Rp {{ number_format($order->unique_code, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="summary-row summary-total">
                            <span class="fw-bold text-dark">TOTAL PEMBAYARAN</span>
                            <span class="fw-bolder text-success fs-5">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    </div>{{-- /.dsb --}}

    @include('livewire.layout.sweetalert')
</div>
@push('scripts')
<style>
    .swal-wa-list {
        display: flex;
        flex-direction: column;
        gap: .65rem;
        margin-top: .5rem;
    }

    .swal-wa-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        width: 100%;
        padding: .9rem 1.1rem;
        border-radius: 14px;
        border: 1px solid rgba(108, 99, 255, 0.18);
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #1e293b;
        font-weight: 600;
        font-size: 1rem;
        text-align: left;
        cursor: pointer;
        transition: all .2s ease;
    }

    .swal-wa-item:hover {
        background: linear-gradient(135deg, #6c63ff, #4e46e5);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(78, 70, 229, 0.35);
        border-color: transparent;
    }

    .swal-wa-item .wa-emoji {
        font-size: 1.25rem;
        line-height: 1;
    }

    .swal-wa-item.sub-item-active {
        background: linear-gradient(135deg, #6c63ff, #4e46e5);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 6px 14px rgba(78, 70, 229, 0.30);
    }
</style>
<script>
    const waGlossyConfig = {
        background: 'rgba(255, 255, 255, 0.8)',
        backdrop: 'rgba(139, 92, 246, 0.15)',
        customClass: {
            popup: 'swal-glossy-popup',
            title: 'swal-glossy-title'
        },
        buttonsStyling: false
    };

    let waData = {};

    // Popup glossy untuk memperbesar bukti pembayaran (seragam dgn fitur lain).
    if (!window.__buktiZoomBound) {
        window.__buktiZoomBound = true;
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest && e.target.closest('.bukti-zoom-trigger');
            if (!trigger) return;
            e.preventDefault();
            const url = trigger.getAttribute('data-bukti-url');
            if (!url) return;
            if (typeof Swal === 'undefined') { window.open(url, '_blank'); return; }
            Swal.fire({
                // Gambar dibatasi ke ukuran layar agar muat tanpa perlu scroll.
                html: '<div style="display:flex; align-items:center; justify-content:center; width:100%;"><img src="' + url + '" alt="Bukti pembayaran" style="max-width:88vw; max-height:82vh; width:auto; height:auto; object-fit:contain; border-radius:12px;"></div>',
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' },
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
                padding: '1rem',
            });
        });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('close-wa-modal', () => {
            if (window.Swal) Swal.close();
        });

        Livewire.on('subscription-status-updated', () => {
            Swal.fire({
                title: 'Status Diperbarui',
                text: 'Status langganan akun berhasil diubah.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                ...waGlossyConfig
            });
        });

        Livewire.on('order-updated', (e) => {
            const msg = (e && (e.message ?? (Array.isArray(e) ? e[0]?.message : null))) || 'Berhasil diperbarui.';
            Swal.fire({ title: 'Berhasil', text: msg, icon: 'success', timer: 2200, showConfirmButton: false, ...waGlossyConfig });
        });
    });

    function salinLinkCek() {
        const el = document.getElementById('cust-cek-link');
        const txt = el ? (el.value || el.textContent || '').trim() : '';
        const done = () => {
            if (window.Swal) Swal.fire({
                title: 'Link Disalin',
                text: 'Link pengecekan customer berhasil disalin ke clipboard.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                ...waGlossyConfig
            });
        };
        if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done).catch(done);
        else if (el) { el.select(); try { document.execCommand('copy'); } catch (err) {} done(); }
    }

    // Konfirmasi seragam (SweetAlert glossy) untuk tombol ber-class .pcek-konfirmasi,
    // menggantikan wire:confirm bawaan browser. Aksi & teks diambil dari data-*.
    /* Konfirmasi .pcek-konfirmasi TIDAK dipasang di sini lagi.

       Penangannya pindah ke layout templateindex, satu salinan untuk seluruh
       layar admin. Dua salinan yang sama membuat gayanya berbeda suatu saat —
       dan dialog yang berbeda bentuk untuk tindakan yang sama berbahayanya
       membuat orang berhenti membacanya. */

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }

    document.addEventListener('click', function(e) {
        const notesBtn = e.target.closest('.notes-btn');
        if (notesBtn) {
            const account = notesBtn.dataset.account?.trim();
            const processing = notesBtn.dataset.processing?.trim();

            const block = (icon, title, text, color) =>
                `<div style="text-align:left;border:1px solid rgba(108,99,255,.18);border-radius:14px;padding:.9rem 1.1rem;margin-bottom:.65rem;background:rgba(255,255,255,.55);">
                    <div style="font-weight:700;color:${color};margin-bottom:.35rem;"><i class="bi ${icon}"></i> ${title}</div>
                    <div style="color:#334155;">${text ? escapeHtml(text) : '<span style=\'color:#94a3b8\'>- tidak ada -</span>'}</div>
                 </div>`;

            Swal.fire({
                title: 'Catatan Pesanan',
                html: `<div class="swal-wa-list">
                        ${block('bi-person-heart', 'Catatan untuk Pelanggan', account, '#059669')}
                        ${block('bi-shield-lock', 'Catatan Internal (Admin)', processing, '#4e46e5')}
                    </div>`,
                showConfirmButton: false,
                showCloseButton: true,
                width: 480,
                padding: '1.5em',
                ...waGlossyConfig
            });
            return;
        }

        const subBtn = e.target.closest('.change-sub-btn');
        if (subBtn) {
            const itemId = subBtn.dataset.id;
            const current = subBtn.dataset.current;
            const options = {
                baru: '🆕 Baru',
                perpanjang: '🔄 Perpanjang',
                pengganti: '♻️ Pengganti',
                habis: '⛔ Habis',
            };

            let html = '<div class="swal-wa-list">';
            Object.keys(options).forEach((val) => {
                const activeClass = val === current ? ' sub-item-active' : '';
                html +=
                    `<button type="button" class="swal-wa-item${activeClass}" data-sub-val="${val}">${options[val]}</button>`;
            });
            html += '</div>';

            Swal.fire({
                title: 'Ubah Status Langganan',
                html: html,
                showConfirmButton: false,
                showCloseButton: true,
                width: 460,
                padding: '1.5em',
                ...waGlossyConfig,
                didOpen: () => {
                    Swal.getPopup().querySelectorAll('.swal-wa-item').forEach((item) => {
                        item.addEventListener('click', () => {
                            const val = item.dataset.subVal;
                            const component = subBtn.closest('[wire\\:id]');
                            if (component) {
                                Livewire.find(component.getAttribute('wire:id'))
                                    .call('updateSubscriptionStatus', itemId, val);
                            }
                            Swal.close();
                        });
                    });
                }
            });
            return;
        }

        const button = e.target.closest('.send-wa-btn');
        if (!button) return;

        waData = {
            id: button.dataset.id,
            idtransaksi: button.dataset.idtransaksi,
            noWa: button.dataset.wa,
            nama: button.dataset.nama,
            akun: button.dataset.akun,
            tglorder: button.dataset.tglorder,
            total: button.dataset.total,
            pemesanan: button.dataset.pemesanan,
            berakhir: button.dataset.berakhir,
            kredit: button.dataset.kredit,
            jumlahkredit: button.dataset.jumlahkredit,
            username: button.dataset.username,
            password: button.dataset.password,
            linkakses: button.dataset.linkakses,
            catatan: button.dataset.catatan,
            struk: button.dataset.struk,
            bonus: button.dataset.bonus,
            ebooks: button.dataset.ebooks,
        };

        Swal.fire({
            title: 'Kirim WhatsApp',
            html: `
                <p class="text-muted mb-2">Pilih jenis pesan yang akan dikirim ke pembeli:</p>
                <div class="swal-wa-list">
                    <button type="button" class="swal-wa-item" data-wa-type="pengiriman">
                        <span class="wa-emoji">📦</span> Pengiriman Akun
                    </button>
                    <button type="button" class="swal-wa-item" data-wa-type="pembaharuan">
                        <span class="wa-emoji">♻️</span> Pembaharuan Akun
                    </button>
                    <button type="button" class="swal-wa-item" data-wa-type="habis">
                        <span class="wa-emoji">⛔</span> Akun Habis
                    </button>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: 460,
            padding: '1.5em',
            ...waGlossyConfig,
            didOpen: () => {
                Swal.getPopup().querySelectorAll('.swal-wa-item').forEach((item) => {
                    item.addEventListener('click', () => {
                        kirimWa(item.dataset.waType);
                        Swal.close();
                    });
                });
            }
        });
    });

    function kirimWa(type) {
        const idItem = waData.id;
        const idtransaksi = waData.idtransaksi;
        const nama = waData.nama;
        const noWa = waData.noWa;
        const akun = waData.akun;
        const tglorder = waData.tglorder;
        const total = waData.total;
        const pemesanan = waData.pemesanan;
        const berakhir = waData.berakhir;
        const username = waData.username;
        const password = waData.password;
        const linkakses = waData.linkakses;
        // Emoji dibangun dari code point agar tidak rusak ("?") karena encoding
        const EMO = {
            bullet: String.fromCodePoint(0x2022), // •
            pin: String.fromCodePoint(0x1F4CC), // 📌
            receipt: String.fromCodePoint(0x1F9FE), // 🧾
            gift: String.fromCodePoint(0x1F381), // 🎁
            book: String.fromCodePoint(0x1F4DA), // 📚
        };

        const catatan = (waData.catatan || '').trim();
        const blokCatatan = catatan ? `\n\n${EMO.pin} Catatan: ${catatan}` : '';
        const struk = (waData.struk || '').trim();
        const blokStruk = struk ? `\n\n${EMO.receipt} *Struk pembelian Anda:* ${struk}` : '';
        const bonus = (waData.bonus || '').trim();
        const ebooksRaw = (waData.ebooks || '').trim();
        const ebookList = ebooksRaw ? ebooksRaw.split('||') : [];
        let blokBonus = '';
        if (bonus || ebookList.length) {
            blokBonus = `\n\n${EMO.gift} *BONUS EBOOK/PANDUAN UNTUK ANDA*`;
            ebookList.forEach((e) => {
                const [judul, url] = e.split(' - ');
                blokBonus += `\n${EMO.book} *${judul}:* ${url}`;
            });
            if (bonus) blokBonus += `\n${bonus}`;
        }

        let pesan = '';

        // Produk KREDIT: tidak ada akun baru yang dikirim, dan tidak ada masa
        // aktif. Memakai naskah "pengiriman" biasa berarti mengirim password
        // kosong dan tanggal berakhir yang dikarang.
        if (type === 'pengiriman' && waData.kredit) {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Pesanan *${akun}* Anda pada tanggal *${tglorder}* sudah kami proses.

${EMO.bullet} Kredit ditambahkan: *${waData.jumlahkredit}*
${EMO.bullet} Akun tujuan: ${username || '-'}

Kredit ini *tidak memiliki masa aktif*, jadi bisa Anda pakai kapan saja.
Total pembayaran anda *Rp ${total}*.${blokCatatan}${blokBonus}${blokStruk}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigitalwarehouse.com/`;
        } else if (type === 'pengiriman') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Anda baru saja order akun *${akun}* pada tanggal *${tglorder}*.
Untuk akun *${akun}* yang bisa anda gunakan mulai tanggal *${pemesanan}* dengan masa aktif sampai tanggal *${berakhir}*.
Total pembayaran anda *Rp ${total}*. Untuk Detail akun sebagai berikut:

${EMO.bullet} Username: ${username}
${EMO.bullet} Password: *${password}*
${EMO.bullet} Link Login: ${linkakses}${blokCatatan}${blokBonus}${blokStruk}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigitalwarehouse.com/`;
        } else if (type === 'pembaharuan') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Akun *${akun}* yang anda order pada tanggal *${tglorder}* dengan masa aktif sampai tanggal *${berakhir}* terdapat pembaharuan akun *${akun}*. Untuk detail akunya sebagai berikut:

${EMO.bullet} Username: ${username}
${EMO.bullet} Password: *${password}*
${EMO.bullet} Link Login: ${linkakses}${blokCatatan}${blokBonus}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigitalwarehouse.com/`;
        } else if (type === 'habis') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Akun *${akun}* yang anda order pada tanggal *${tglorder}* dengan masa aktif sampai *${berakhir}* *SUDAH HABIS MASA AKTIFNYA*. Jika Anda ingin memperpanjang akun *${akun}* Anda, silakan hubungi kami.

Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigitalwarehouse.com/`;
        }

        // Bersihkan nomor (hanya digit) & pakai endpoint api.whatsapp.com
        // yang lebih konsisten menampilkan emoji di WhatsApp Web/Desktop.
        const noWaClean = (noWa || '').replace(/\D/g, '');
        const url = `https://api.whatsapp.com/send?phone=${noWaClean}&text=${encodeURIComponent(pesan)}`;
        window.open(url, '_blank');

        if (type === 'habis') {
            // Pemberitahuan akun habis: hanya catat waktu notifikasi,
            // jangan ubah status delivery/order.
            Livewire.dispatch('habis-notified', {
                id: idItem
            });
        } else {
            Livewire.dispatch('sent-on-whatsapp', {
                id: idItem
            });
        }
    }
</script>
@endpush