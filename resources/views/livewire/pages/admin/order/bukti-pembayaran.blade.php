@section('title')
Unggah Bukti Pembayaran || lemon
@stop

<div>
    <style>
        .bp-card {
            border: 1px solid rgba(108, 99, 255, 0.14);
            border-radius: 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.97), rgba(248, 249, 255, 0.97));
            box-shadow: 0 12px 32px rgba(108, 99, 255, 0.12);
        }

        .bp-drop {
            border: 1.5px dashed #d6d9e6;
            border-radius: 14px;
            padding: 26px 20px;
            text-align: center;
            position: relative;
            background: #fbfcff;
            transition: border-color .15s ease, background .15s ease;
        }

        .bp-drop:hover {
            border-color: #7c3aed;
            background: #f7f5ff;
        }

        .bp-drop input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .bp-total {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -.5px;
        }

        .bp-baris {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: .35rem 0;
            font-size: .9rem;
        }
    </style>

    <div class="card bp-card mb-3">
        <div class="card-body p-4">
            <h4 class="gradient-text fw-bold mb-1">Unggah Bukti Pembayaran</h4>
            <x-breadcrumb :items="[
                ['label' => 'Pesanan Toko', 'url' => route('admin.pesanantoko.index')],
                ['label' => 'Unggah Bukti'],
            ]" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card bp-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-receipt-cutoff text-primary"></i>
                        <span class="fw-bold">Ringkasan Pesanan</span>
                    </div>

                    <div class="bp-baris">
                        <span class="text-muted">Nomor</span>
                        <span class="fw-semibold">{{ $order->order_number }}</span>
                    </div>
                    <div class="bp-baris">
                        <span class="text-muted">Pelanggan</span>
                        <span class="fw-semibold">{{ $order->customer->nama ?? '-' }}</span>
                    </div>
                    <div class="bp-baris">
                        <span class="text-muted">Metode</span>
                        <span class="fw-semibold">
                            {{ $order->payment_method === 'transfer' ? 'Transfer Bank' : 'QRIS Statis' }}
                        </span>
                    </div>
                    <div class="bp-baris">
                        <span class="text-muted">Dibuat</span>
                        <span class="fw-semibold">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</span>
                    </div>

                    <hr class="my-3">

                    <div class="text-muted" style="font-size:.85rem;">Total yang harus dibayar</div>
                    <div class="bp-total gradient-text">Rp {{ number_format((int) $order->total, 0, ',', '.') }}</div>

                    @if ($order->items->count())
                        <hr class="my-3">
                        <div class="text-muted mb-2" style="font-size:.85rem;">Item</div>
                        @foreach ($order->items as $item)
                            <div class="bp-baris">
                                <span>{{ $item->product_name }}
                                    <span class="text-muted">×{{ $item->quantity }}</span></span>
                                <span class="fw-semibold">Rp
                                    {{ number_format((int) $item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card bp-card h-100">
                <div class="card-body p-4">
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
                        <i class="bi bi-info-circle mt-1"></i>
                        <div style="font-size:.88rem;">
                            Pesanan ini masih <b>draft</b> dan belum masuk daftar pesanan aktif.
                            Setelah bukti diunggah, statusnya berubah menjadi <b>pending</b> dan
                            pesanan langsung bisa diproses dari halaman detail.
                        </div>
                    </div>

                    <div class="bp-drop" wire:loading.class="opacity-50" wire:target="bukti">
                        <input type="file" wire:model="bukti" accept="image/*">
                        <i class="bi bi-cloud-arrow-up" style="font-size:2rem; color:#7c3aed;"></i>
                        <div class="fw-semibold text-dark mt-1">Klik untuk pilih gambar bukti</div>
                        <div class="text-muted" style="font-size:.78rem;">JPG/PNG · maks 4 MB</div>
                        <div wire:loading wire:target="bukti" class="text-primary small mt-2">
                            <span class="spinner-border spinner-border-sm me-1"></span>Mengunggah...
                        </div>
                    </div>

                    @error('bukti')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror

                    @if ($bukti && ! is_string($bukti))
                        <div class="mt-3 text-center">
                            <img src="{{ $bukti->temporaryUrl() }}" alt="Pratinjau bukti pembayaran"
                                style="max-height:260px; max-width:100%; border-radius:12px; box-shadow:0 6px 18px rgba(15,23,42,.12);">
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.pesanantoko.index', ['activeTab' => 'draft']) }}"
                            class="btn btn-secondary rounded-pill px-4 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-arrow-left"></i> <span>Kembali</span>
                        </a>
                        <button type="button" wire:click="simpan" wire:loading.attr="disabled"
                            wire:target="simpan,bukti"
                            class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-1">
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
