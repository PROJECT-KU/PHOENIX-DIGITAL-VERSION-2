@section('title')
Unggah Bukti Pembayaran || lemon
@stop

<div class="container-fluid">
    <style>
        /* ===== Perataan ikon =====
           Ikon Bootstrap (bi) memakai font-icon, jadi glyph-nya duduk mengikuti
           baseline huruf dan terlihat naik-turun terhadap teks di sebelahnya.
           Dinolkan line-height-nya, lalu ::before dijadikan block agar tingginya
           persis sama dengan kotak ikonnya. Pola ini sama dengan yang dipakai
           dashboard karyawan. */
        .bp-wrap i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .bp-wrap i.bi::before {
            display: block;
            line-height: 1;
        }

        .bp-card {
            border: 1px solid rgba(108, 99, 255, .14);
            border-radius: 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, .97), rgba(248, 249, 255, .97));
            box-shadow: 0 12px 32px rgba(108, 99, 255, .12);
        }

        /* Kepala tiap kartu: keping ikon + judul, sejajar rapi. */
        .bp-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .bp-chip {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
            flex: 0 0 auto;
        }

        .bp-head-title {
            font-weight: 700;
            font-size: .98rem;
            line-height: 1.2;
            margin: 0;
        }

        .bp-head-sub {
            font-size: .78rem;
            color: #8a90a2;
            line-height: 1.3;
        }

        /* Baris ringkasan: label kiri, nilai kanan, pemisah tipis. */
        .bp-baris {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 14px;
            padding: .55rem 0;
            font-size: .88rem;
            border-bottom: 1px dashed rgba(108, 99, 255, .12);
        }

        .bp-baris:last-child { border-bottom: 0; }
        .bp-baris .bp-label { color: #8a90a2; flex: 0 0 auto; }
        .bp-baris .bp-nilai { font-weight: 600; text-align: right; min-width: 0; word-break: break-word; }

        .bp-total-kotak {
            border-radius: 14px;
            padding: 14px 16px;
            background: linear-gradient(135deg, rgba(124, 58, 237, .08), rgba(79, 70, 229, .06));
            border: 1px solid rgba(124, 58, 237, .16);
        }

        .bp-total {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.5px;
            line-height: 1.15;
        }

        .bp-drop {
            border: 1.5px dashed #d6d9e6;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            position: relative;
            background: #fbfcff;
            transition: border-color .15s ease, background .15s ease;
        }

        .bp-drop:hover { border-color: #7c3aed; background: #f7f5ff; }

        .bp-drop input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .bp-drop-ic {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            margin: 0 auto 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
        }

        /* Catatan draft: ikon sejajar dengan baris pertama teks. */
        .bp-nota {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 15px;
            border-radius: 13px;
            border: 1px solid #fde68a;
            background: #fffbeb;
            font-size: .85rem;
            line-height: 1.6;
            color: #854d0e;
        }

        .bp-nota > i.bi {
            flex: 0 0 auto;
            margin-top: .18rem;
            font-size: 1rem;
            color: #d97706;
        }

        .bp-pratinjau {
            max-height: 260px;
            max-width: 100%;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .12);
        }

        @media (max-width: 575.98px) {
            .bp-baris { font-size: .84rem; }
            .bp-total { font-size: 1.35rem; }
        }
    </style>

    <div class="bp-wrap">
        {{-- Kepala halaman: pola yang sama dengan Tambah Pesanan. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 fixed-header-card">
            <div class="card-body p-4">
                <div class="title-wrapper text-center text-md-start w-100">
                    <h3 class="gradient-text fw-bold mb-1">Unggah Bukti Pembayaran</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        {{-- Kuncinya 'name', bukan 'label' — itu yang dibaca
                             resources/views/components/breadcrumb.blade.php. --}}
                        @php
                            $breadcrumbs = [
                                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                                ['name' => 'Data Pemesanan', 'url' => route('admin.pesanantoko.index')],
                                ['name' => 'Unggah Bukti'],
                            ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Ringkasan --}}
            <div class="col-lg-5">
                <div class="card bp-card h-100">
                    <div class="card-body p-4">
                        <div class="bp-head">
                            <span class="bp-chip bg-gradient-blue"><i class="bi bi-receipt-cutoff"></i></span>
                            <div>
                                <p class="bp-head-title">Ringkasan Pesanan</p>
                                <div class="bp-head-sub">Data pesanan yang akan diaktifkan</div>
                            </div>
                        </div>

                        <div class="bp-baris">
                            <span class="bp-label">Nomor</span>
                            <span class="bp-nilai">{{ $order->order_number }}</span>
                        </div>
                        <div class="bp-baris">
                            <span class="bp-label">Pelanggan</span>
                            <span class="bp-nilai">{{ $order->customer->nama ?? '-' }}</span>
                        </div>
                        <div class="bp-baris">
                            <span class="bp-label">Metode</span>
                            <span class="bp-nilai">
                                {{ $order->payment_method === 'transfer' ? 'Transfer Bank' : 'QRIS Statis' }}
                            </span>
                        </div>
                        <div class="bp-baris">
                            <span class="bp-label">Dibuat</span>
                            <span class="bp-nilai">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</span>
                        </div>

                        <div class="bp-total-kotak mt-3">
                            <div class="bp-head-sub mb-1">Total yang harus dibayar</div>
                            <div class="bp-total gradient-text">Rp {{ number_format((int) $order->total, 0, ',', '.') }}</div>
                        </div>

                        @if ($order->items->count())
                            <div class="bp-head mt-4 mb-2">
                                <span class="bp-chip bg-gradient-purple" style="width:32px;height:32px;font-size:.95rem;border-radius:10px;">
                                    <i class="bi bi-box-seam"></i>
                                </span>
                                <p class="bp-head-title">Item Pesanan</p>
                            </div>
                            @foreach ($order->items as $item)
                                <div class="bp-baris">
                                    <span class="bp-label">
                                        {{ $item->product_name }}
                                        <span class="text-muted">×{{ $item->quantity }}</span>
                                    </span>
                                    <span class="bp-nilai">Rp {{ number_format((int) $item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- Unggah --}}
            <div class="col-lg-7">
                <div class="card bp-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="bp-head">
                            <span class="bp-chip bg-gradient-purple"><i class="bi bi-cloud-arrow-up"></i></span>
                            <div>
                                <p class="bp-head-title">Bukti Pembayaran</p>
                                <div class="bp-head-sub">Unggah gambar bukti transfer atau pembayaran QRIS</div>
                            </div>
                        </div>

                        <div class="bp-nota mb-3">
                            <i class="bi bi-info-circle"></i>
                            <div>
                                Pesanan ini masih <b>draft</b> dan belum masuk daftar pesanan aktif.
                                Setelah bukti diunggah, statusnya berubah menjadi <b>pending</b> dan
                                pesanan langsung bisa diproses dari halaman detail.
                            </div>
                        </div>

                        <div class="bp-drop" wire:loading.class="opacity-50" wire:target="bukti">
                            <input type="file" wire:model="bukti" accept="image/*">
                            <span class="bp-drop-ic bg-gradient-purple"><i class="bi bi-cloud-arrow-up"></i></span>
                            <div class="fw-semibold text-dark">Klik untuk pilih gambar bukti</div>
                            <div class="bp-head-sub mt-1">JPG/PNG · maks 4 MB</div>
                            <div wire:loading wire:target="bukti" class="text-primary small mt-2 d-inline-flex align-items-center gap-1">
                                <span class="spinner-border spinner-border-sm"></span> Mengunggah...
                            </div>
                        </div>

                        @error('bukti')
                            <div class="text-danger small mt-2 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> <span>{{ $message }}</span>
                            </div>
                        @enderror

                        @if ($bukti && ! is_string($bukti) && $bukti->isPreviewable())
                            <div class="mt-3 text-center">
                                <img src="{{ $bukti->temporaryUrl() }}" alt="Pratinjau bukti pembayaran" class="bp-pratinjau">
                            </div>
                        @endif

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-auto pt-4">
                            <a href="{{ route('admin.pesanantoko.index', ['activeTab' => 'draft']) }}"
                                class="btn btn-secondary rounded-pill px-4 d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-arrow-left"></i> <span>Kembali</span>
                            </a>
                            <button type="button" wire:click="simpan" wire:loading.attr="disabled"
                                wire:target="simpan,bukti"
                                class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check2-circle"></i>
                                <span wire:loading.remove wire:target="simpan">Simpan &amp; Aktifkan Pesanan</span>
                                <span wire:loading wire:target="simpan">Memproses...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
