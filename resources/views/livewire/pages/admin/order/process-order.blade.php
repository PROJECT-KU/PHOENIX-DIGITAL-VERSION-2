<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4 mb-4 fixed-header-card">
        <div class="card-body p-4 d-flex align-items-center">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 header-action w-100">
                <div class="title-wrapper text-center text-md-start w-100">
                    <h3 class="gradient-text fw-bold mb-1">Proses Pesanan</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [
                        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                        ['name' => 'Data Pesanan', 'url' => route('admin.pesanantoko.index')],
                        ['name' => 'Detail Pesanan', 'url' => route('admin.pesanantoko.detail', $order)],
                        ['name' => 'Proses Pesanan'],
                        ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        .ebook-pick .ep-icon i.bi {
            line-height: 1;
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
    </style>

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session()->has('info'))
    <div class="alert alert-info alert-dismissible fade show rounded-4 border-0 shadow-sm">
        <i class="bi bi-info-circle-fill me-1"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Ringkasan Pesanan -->
    <div class="order-summary-card p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="proc-section-icon"><i class="bi bi-bag-check-fill"></i></span>
            <h4 class="fw-bold mb-0">{{ $order->order_number }}</h4>
        </div>
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="summary-stat">
                    <div class="label">Nama Produk</div>
                    <div class="value">{{ $orderItem->product_name }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="summary-stat">
                    <div class="label">Durasi</div>
                    <div class="value">{{ $orderItem->getDurationLabel() }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="summary-stat">
                    <div class="label">Harga</div>
                    <div class="value">Rp {{ number_format($orderItem->price, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="summary-stat">
                    <div class="label">Jumlah</div>
                    <div class="value">{{ $orderItem->quantity }}</div>
                </div>
            </div>
        </div>
    </div>

    <form wire:submit="processOrder">
        <!-- Pilih Data Akun -->
        <div class="proc-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="proc-section-icon"><i class="bi bi-key-fill"></i></span>
                <h5 class="fw-bold mb-0">Pilih Akun Premium</h5>
            </div>

            <div class="mb-3">
                <label class="form-label">Data Akun Tersedia <span class="text-danger">*</span></label>
                <select class="form-select @error('selectedDataAkunId') is-invalid @enderror"
                    wire:model.live="selectedDataAkunId">
                    <option value="">-- Pilih atau isi manual di bawah --</option>
                    @foreach ($availableAccounts as $akun)
                    <option value="{{ $akun->id }}">
                        {{ $akun->nama_akun }} - {{ $akun->username }}
                    </option>
                    @endforeach
                </select>
                @error('selectedDataAkunId')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">
                    Pilih dari akun yang tersedia atau isi manual di form bawah
                </small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username / Email Akun</label>
                    <input type="text" class="form-control @error('accountUsername') is-invalid @enderror"
                        wire:model="accountUsername" placeholder="username@example.com">
                    @error('accountUsername')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Password Akun</label>
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
                <h5 class="fw-bold mb-0">Periode Berlangganan</h5>
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
                        <input type="date" class="form-control" wire:model="endDate" readonly>
                        <small class="text-muted">
                            Otomatis: mulai + {{ $orderItem->getDurationLabel() }}
                            @if ((int) $bonusDurationValue > 0)
                            + bonus {{ $bonusDurationValue }} {{ $bonusDurationType }}
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
                            <span class="ta-base">{{ $orderItem->duration_value }} {{ $orderItem->duration_type }}</span>
                            <span class="ta-plus">+</span>
                            <span class="ta-bonus">{{ $bonusDurationValue }} {{ $bonusDurationType }}</span>
                        </div>
                        @else
                        <div class="ta-empty">Belum ada bonus durasi</div>
                        @endif
                    </div>
                </div>
            </div>

            <hr class="my-3">

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
                <h5 class="fw-bold mb-0">Bonus untuk Pelanggan <span class="text-muted fs-6">(opsional)</span></h5>
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
                <div class="row g-3">
                    @foreach ($availableEbooks as $eb)
                    <div class="col-md-6">
                        <label class="ebook-pick">
                            <input type="checkbox" value="{{ $eb->id }}" wire:model="selectedEbooks">
                            <span class="ep-icon"><i class="bi bi-journal-bookmark-fill"></i></span>
                            <span class="ep-body">
                                <span class="ep-title">{{ $eb->judul }}</span>
                                <small class="ep-desc">{{ $eb->deskripsi ?: 'Ebook bonus' }}</small>
                            </span>
                            <i class="bi bi-check-circle-fill ep-check"></i>
                        </label>
                    </div>
                    @endforeach
                </div>
                @error('selectedEbooks.*') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-info-circle me-1"></i>Centang ebook yang ingin diberikan — tautan unduhnya otomatis
                    ikut di pesan WhatsApp.
                </small>
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
                <span class="proc-section-icon icon-amber"><i class="bi bi-journal-text"></i></span>
                <h5 class="fw-bold mb-0">Catatan Internal (Admin)</h5>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan Proses</label>
                <textarea class="form-control @error('processingNotes') is-invalid @enderror" wire:model="processingNotes"
                    rows="3" placeholder="Catatan internal untuk admin (tidak dilihat customer)"></textarea>
                @error('processingNotes')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-0 alert alert-info rounded-4 mb-0">
                <i class="bi bi-info-circle"></i>
                <strong>Admin yang memproses:</strong> {{ auth()->user()->name }}<br>
                <strong>Waktu proses:</strong> {{ now()->format('d F Y, H:i') }} WIB
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="button" wire:click="cancelProcessing"
                class="btn btn-danger px-5 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-x-circle me-2 fs-5"></i>
                <span>Batal</span>
            </button>
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;" wire:loading.attr="disabled">
                <span wire:loading.remove class="d-inline-flex align-items-center justify-content-center">
                    <i class="bi bi-check2-circle me-2 fs-5"></i>
                    <span>Proses &amp; Lanjut ke Pengiriman</span>
                </span>
            </button>
        </div>
    </form>
</div>