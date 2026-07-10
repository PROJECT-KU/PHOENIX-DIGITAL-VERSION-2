
@section('title')
Data Pengeluaran || lemon
@stop
<div>
    <!--================== GLOSSY TABS STYLE ==================-->
    <style>
        /* Tab glossy — seragam dengan tab di fitur Order (Pemesanan Toko). */
        .spending-tabs {
            display: flex;
            width: 100%;
            gap: .5rem;
            padding: .5rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.12);
            overflow-x: auto;
        }

        .spending-tab {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            border: none;
            background: transparent;
            color: #6b7280;
            font-weight: 600;
            font-size: 1.05rem;
            line-height: 1;
            padding: .95rem 1.5rem;
            border-radius: 999px;
            cursor: pointer;
            transition: all .25s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .spending-tab i {
            font-size: 1.25rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }

        .spending-tab:hover:not(.active) {
            color: #4e46e5;
            background: rgba(108, 99, 255, 0.10);
        }

        .spending-tab.active {
            color: #fff;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 16px rgba(78, 70, 229, 0.45);
            transform: translateY(-1px);
        }

        @media (max-width: 575.98px) {
            .spending-tab {
                flex: 0 0 auto;
                justify-content: center;
                padding: .6rem .9rem;
                font-size: .9rem;
            }
        }

        /* Presisi ikon di dalam wrapper & tab */
        .stat-icon-wrapper {
            line-height: 1 !important;
        }

        .stat-icon-wrapper i,
        .spending-tab i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .stat-icon-wrapper i::before,
        .spending-tab i::before {
            display: block;
            line-height: 1;
        }

        /* Kategori Card */
        .category-card {
            background: rgba(255, 255, 255, 0.9) !important;
        }

        .category-progress {
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: rgba(139, 92, 246, 0.1);
            overflow: hidden;
        }

        .category-progress-bar {
            height: 100%;
            border-radius: 999px;
            transition: width 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        /* Grand Total */
        .grand-total-card {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            border-radius: 20px;
            box-shadow: 0 12px 28px rgba(124, 58, 237, 0.28);
        }

        .grand-total-card .stat-icon-wrapper {
            width: 46px;
            height: 46px;
            font-size: 1.3rem;
            border-radius: 14px;
            color: #fff;
        }

        /* Rapikan Total Keseluruhan Pengeluaran di layar mobile. */
        @media (max-width: 575.98px) {
            .grand-total-card {
                text-align: center;
                padding: 1.25rem !important;
                gap: .35rem !important;
            }

            .grand-total-card>div {
                justify-content: center;
                flex-wrap: wrap;
                row-gap: .35rem;
            }

            .grand-total-card>div span.fw-semibold {
                font-size: .95rem !important;
            }

            .grand-total-card h3 {
                font-size: 1.5rem;
                width: 100%;
            }
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Pengeluaran</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pengeluaran']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABS ==================-->
        <div class="spending-tabs mb-4">
            <a href="{{ route('admin.spending.index', ['jenisPengeluaran' => 'lainnya']) }}"
                class="spending-tab {{ $jenisPengeluaran !== 'pembelian_akun' ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Pengeluaran Lainnya</span>
            </a>
            <a href="{{ route('admin.spending.index', ['jenisPengeluaran' => 'pembelian_akun']) }}"
                class="spending-tab {{ $jenisPengeluaran === 'pembelian_akun' ? 'active' : '' }}">
                <i class="bi bi-bag-check"></i>
                <span>Pengeluaran Pembelian Akun</span>
            </a>
        </div>

        <!--================== TABEL DATA PENGELUARAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                @include('livewire.pages.admin.spending.partials.filter')

                @php
                $isPembelianAkun = $jenisPengeluaran === 'pembelian_akun';
                // Saat mencari, data dua kategori tergabung -> selalu tampilkan kolom PIC Pembeli
                $showPicColumn = $isPembelianAkun || filled($search);
                @endphp

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>ID Transaksi</th>
                                <th>Waktu Transaksi</th>
                                <th>Nominal</th>
                                <th>{{ $isPembelianAkun ? 'Akun' : 'Deskripsi' }}</th>
                                <th class="text-center">Status</th>
                                <th>Penginput</th>
                                @if ($showPicColumn)
                                <th>PIC Pembeli</th>
                                @endif
                                <th>Waktu Data Dibuat</th>
                                @if (auth()->user()->hasAnyPermission(['edit_spending', 'delete_spending']))
                                <th class="text-center" width="120">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($spendings as $spending)
                            <tr style="text-align: center;">
                                <td class="fw-bold">{{ $spending->id_transaksi }}</td>
                                <td>{{ $spending->tanggal_transaksi_formatted }}</td>
                                <td>{{ $spending->nominal_formatted }}</td>
                                <td class="text-truncate" style="max-width: 200px;">
                                    @if ($spending->jenis_pengeluaran === 'pembelian_akun')
                                    <span class="badge bg-primary-subtle text-primary border border-primary">
                                        <i class="bi bi-box-seam me-1"></i>{{ $spending->product->nama_akun ?? '—' }}
                                    </span>
                                    @else
                                    {{ Str::limit($spending->deskripsi, 50) }}
                                    @endif
                                    @php $fotoBukti = $spending->images; @endphp
                                    @if (count($fotoBukti))
                                    <a href="javascript:void(0)" role="button" class="sp-bukti-trigger d-inline-block ms-1 align-middle position-relative" title="Lihat gambar/bukti"
                                        data-bukti='@json(collect($fotoBukti)->map(fn ($p) => Storage::url($p))->values())'>
                                        <img src="{{ Storage::url($fotoBukti[0]) }}" alt="bukti" style="width:26px; height:26px; object-fit:cover; border-radius:6px; border:1px solid #e6e8f2; cursor:zoom-in;">
                                        @if (count($fotoBukti) > 1)
                                        <span class="badge bg-primary position-absolute top-0 start-100 translate-middle" style="font-size:.5rem;">+{{ count($fotoBukti) - 1 }}</span>
                                        @endif
                                    </a>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-{{ $spending->status === 'completed' ? 'success' : ($spending->status === 'rejected' ? 'danger' : ($spending->status === 'approved' ? 'info' : 'warning')) }}">
                                        {{ ucfirst($spending->status) }}
                                    </span>
                                </td>
                                <td>{{ $spending->namaPenginput }}</td>
                                @if ($showPicColumn)
                                <td>{{ $spending->jenis_pengeluaran === 'pembelian_akun' ? ($spending->namaPicPembeli ?: '-') : '-' }}</td>
                                @endif
                                <td>{{ $spending->created_at_formatted }}</td>
                                @if (auth()->user()->hasAnyPermission(['edit_spending', 'delete_spending']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_spending'))
                                    <a href="{{ route('admin.spending.edit', $spending->id) }}" wire:navigate
                                        class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_spending'))
                                    <button type="button" class="btn btn-sm btn-danger p-2 delete-spending-btn"
                                        data-id="{{ $spending->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $showPicColumn ? 9 : 8 }}" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Pengeluaran
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Tidak ada data pengeluaran yang ditemukan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $spendings->links('vendor.pagination') }}
                </div>
            </div>
        </div>

        <!--================== TOTAL PER KATEGORI ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.2rem; border-radius: 13px;">
                        <i class="bi bi-pie-chart-fill"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Total Pengeluaran Per Kategori</h5>
                </div>

                @if($totalSpendings->isNotEmpty())
                @php $grandTotal = $totalSpendings->sum('total_pengeluaran'); @endphp

                <div class="row g-3 align-items-stretch">
                    @foreach($totalSpendings as $item)
                    @php
                    $isAkun = $item->jenisPengeluaran === 'pembelian_akun';
                    $label = $isAkun ? 'Pembelian Akun' : ($item->jenisPengeluaran === 'lainnya' ? 'Pengeluaran Lainnya' : 'Tidak Diketahui');
                    $icon = $isAkun ? 'bi-bag-check-fill' : 'bi-receipt';
                    $gradient = $isAkun ? 'bg-gradient-blue' : 'bg-gradient-purple';
                    $persen = $grandTotal > 0 ? round(($item->total_pengeluaran / $grandTotal) * 100) : 0;
                    @endphp
                    <div class="col-12 col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 stat-card overflow-hidden category-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="stat-icon-wrapper {{ $gradient }} flex-shrink-0">
                                        <i class="bi {{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">{{ $label }}</p>
                                        <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted" style="font-size: 0.78rem;">Kontribusi</span>
                                    <span class="fw-bold" style="font-size: 0.78rem; color: {{ $isAkun ? '#2563eb' : '#7c3aed' }};">{{ $persen }}%</span>
                                </div>
                                <div class="category-progress">
                                    <div class="category-progress-bar {{ $gradient }}" style="width: {{ $persen }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Grand Total -->
                <div class="grand-total-card mt-3 p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0"
                            style="background: rgba(255,255,255,0.2); box-shadow: none;">
                            <i class="bi bi-wallet2"></i>
                        </span>
                        <span class="fw-semibold text-white" style="font-size: 1.05rem;">Total Keseluruhan Pengeluaran</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h3>
                </div>

                @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="empty-state-icon-wrapper mb-3">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Data</h5>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">Tidak ada data pengeluaran.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->

    @push('scripts')
    {{-- Popup lihat bukti pengeluaran (SweetAlert glossy). >1 gambar = slider manual (tanpa auto-slide). --}}
    <script>
        window.spShowBukti = function (images) {
            if (!images || !images.length) return;
            if (typeof Swal === 'undefined') { window.open(images[0], '_blank'); return; }

            const glossy = {
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' },
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
                padding: '1rem',
            };

            // Satu gambar: tampilkan langsung (dibatasi ke ukuran layar agar tanpa scroll).
            if (images.length === 1) {
                Swal.fire(Object.assign({
                    html: '<div style="display:flex; align-items:center; justify-content:center; width:100%;"><img src="' + images[0] + '" alt="Bukti pengeluaran" style="max-width:88vw; max-height:82vh; width:auto; height:auto; object-fit:contain; border-radius:12px;"></div>',
                }, glossy));
                return;
            }

            // Banyak gambar: slider manual (prev/next + panah keyboard), TIDAK auto-slide.
            let idx = 0;
            const html =
                '<div style="position:relative; max-width:80vw;">' +
                '  <img id="spBuktiImg" src="' + images[0] + '" style="max-width:100%; max-height:70vh; border-radius:12px; object-fit:contain;">' +
                '  <button type="button" id="spBuktiPrev" class="btn btn-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="position:absolute; top:50%; left:8px; transform:translateY(-50%); width:40px; height:40px;"><i class="bi bi-chevron-left"></i></button>' +
                '  <button type="button" id="spBuktiNext" class="btn btn-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="position:absolute; top:50%; right:8px; transform:translateY(-50%); width:40px; height:40px;"><i class="bi bi-chevron-right"></i></button>' +
                '  <div id="spBuktiCounter" class="mt-2 fw-semibold text-muted">1 / ' + images.length + '</div>' +
                '</div>';

            Swal.fire(Object.assign({
                html: html,
                didOpen: function () {
                    const img = document.getElementById('spBuktiImg');
                    const counter = document.getElementById('spBuktiCounter');
                    const show = function (i) {
                        idx = (i + images.length) % images.length;
                        img.src = images[idx];
                        counter.textContent = (idx + 1) + ' / ' + images.length;
                    };
                    document.getElementById('spBuktiPrev').addEventListener('click', function () { show(idx - 1); });
                    document.getElementById('spBuktiNext').addEventListener('click', function () { show(idx + 1); });
                    const onKey = function (e) {
                        if (!document.getElementById('spBuktiImg')) { document.removeEventListener('keydown', onKey); return; }
                        if (e.key === 'ArrowLeft') show(idx - 1);
                        if (e.key === 'ArrowRight') show(idx + 1);
                    };
                    document.addEventListener('keydown', onKey);
                },
            }, glossy));
        };

        // Delegasi klik thumbnail bukti (di-bind sekali).
        if (!window.__spBuktiBound) {
            window.__spBuktiBound = true;
            document.addEventListener('click', function (e) {
                const trigger = e.target.closest && e.target.closest('.sp-bukti-trigger');
                if (!trigger) return;
                e.preventDefault();
                let images = [];
                try { images = JSON.parse(trigger.getAttribute('data-bukti') || '[]'); } catch (_) { images = []; }
                window.spShowBukti(images);
            });
        }
    </script>
    @endpush
</div>
