@section('title')
Data Pesanan RSC || PT. Asthana Cipta Mandiri
@stop
<div>
    <style>
        /* Pusatkan ikon Bootstrap (bi) di stat-icon-wrapper (kartu Filter Periode) */
        .stat-icon-wrapper i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; }
        .stat-icon-wrapper i.bi::before { display: block; line-height: 1; }

        /* ===== Modal Export (glossy) ===== */
        .rsc-export-backdrop {
            position: fixed; inset: 0; z-index: 1055; display: flex; align-items: center; justify-content: center;
            padding: 1rem; background: rgba(30, 27, 75, .38); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
            animation: rscFade .18s ease;
        }
        @keyframes rscFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes rscPop { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: none; } }
        .rsc-export-card {
            width: 100%; max-width: 620px; background: rgba(255, 255, 255, .96); border-radius: 22px; overflow: hidden;
            box-shadow: 0 30px 70px rgba(15, 23, 42, .32); border: 1px solid rgba(255, 255, 255, .6); animation: rscPop .22s ease;
            display: flex; flex-direction: column; max-height: 92vh;
        }
        .rsc-export-head {
            display: flex; align-items: center; gap: 14px; padding: 20px 24px;
            background: linear-gradient(135deg, #16a34a, #059669); color: #fff; position: relative;
        }
        .rsc-export-head-ico {
            width: 46px; height: 46px; flex: 0 0 46px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.4rem; background: rgba(255, 255, 255, .2); box-shadow: inset 0 1px 0 rgba(255,255,255,.4);
        }
        .rsc-export-head-ico i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .rsc-export-head h5 { margin: 0; font-weight: 800; font-size: 1.08rem; line-height: 1.2; }
        .rsc-export-head p { margin: 2px 0 0; font-size: .8rem; opacity: .9; }
        .rsc-export-close {
            margin-left: auto; width: 34px; height: 34px; border-radius: 10px; border: 0; color: #fff; font-size: 1.05rem;
            background: rgba(255, 255, 255, .16); display: inline-flex; align-items: center; justify-content: center; transition: background .15s;
        }
        .rsc-export-close:hover { background: rgba(255, 255, 255, .3); }
        .rsc-export-close i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .rsc-export-close i.bi::before { display: block; line-height: 1; }
        .rsc-export-body { padding: 20px 24px; overflow-y: auto; }
        .rsc-export-search { position: relative; margin-bottom: 14px; }
        .rsc-export-search .bi {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8;
            display: inline-flex; align-items: center; justify-content: center; line-height: 1; pointer-events: none;
        }
        .rsc-export-search .bi::before { display: block; line-height: 1; }
        .rsc-export-search input {
            width: 100%; border: 1px solid #e6e8f2; border-radius: 13px; padding: 11px 14px 11px 40px; font-size: .92rem; transition: all .15s;
        }
        .rsc-export-search input:focus { outline: none; border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, .12); }
        .rsc-export-list {
            border: 1px solid #eef0f7; border-radius: 15px; max-height: 300px; overflow-y: auto; background: #fbfcfe;
        }
        .rsc-export-row {
            display: flex; align-items: center; gap: 12px; padding: 11px 16px; cursor: pointer; border-bottom: 1px solid #f1f3f9; transition: background .12s;
        }
        .rsc-export-row:last-child { border-bottom: 0; }
        .rsc-export-row:hover { background: #f4fbf6; }
        .rsc-export-row.is-checked { background: linear-gradient(135deg, rgba(22,163,74,.10), rgba(5,150,105,.05)); }
        .rsc-export-row .form-check-input { margin: 0; cursor: pointer; }
        .rsc-export-row .form-check-input:checked { background-color: #16a34a; border-color: #16a34a; }
        .rsc-export-row-name { font-weight: 700; color: #1e293b; font-size: .92rem; }
        .rsc-export-row-batch {
            margin-left: auto; font-size: .74rem; font-weight: 700; color: #0f766e;
            background: #d1fae5; border-radius: 999px; padding: 3px 11px;
        }
        .rsc-export-empty { text-align: center; color: #94a3b8; padding: 2rem 1rem; font-size: .9rem; }
        .rsc-export-counter {
            display: inline-flex; align-items: center; gap: 6px; margin-top: 12px; font-size: .8rem; font-weight: 700; color: #475569;
        }
        .rsc-export-counter b { color: #16a34a; }
        .rsc-export-foot {
            display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
            padding: 16px 24px; background: #f8fafc; border-top: 1px solid #eef0f7;
        }
        .rsc-x-btn {
            display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: .86rem; border: 0; border-radius: 12px;
            padding: 10px 18px; transition: all .16s ease; white-space: nowrap; line-height: 1;
        }
        .rsc-x-btn i.bi { display: inline-flex; align-items: center; line-height: 1; }
        .rsc-x-btn:disabled { opacity: .55; cursor: not-allowed; }
        .rsc-x-cancel { background: #eef1f6; color: #475569; }
        .rsc-x-cancel:hover { background: #e2e6ee; }
        .rsc-x-preview { background: #e0f2fe; color: #0369a1; }
        .rsc-x-preview:hover { background: #bae6fd; color: #075985; }
        .rsc-x-pdf { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 8px 18px rgba(239,68,68,.30); }
        .rsc-x-pdf:hover { transform: translateY(-1px); box-shadow: 0 12px 22px rgba(239,68,68,.4); }
        .rsc-x-excel { background: linear-gradient(135deg, #16a34a, #059669); color: #fff; box-shadow: 0 8px 18px rgba(22,163,74,.30); }
        .rsc-x-excel:hover { transform: translateY(-1px); box-shadow: 0 12px 22px rgba(22,163,74,.4); }
    </style>

    {{-- Header (seragam dengan Pesanan Toko) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="title-wrapper text-center text-md-start w-100">
                    <h3 class="gradient-text fw-bold mb-1">Data Pemesanan RSC</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pemesanan RSC']];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                    <div class="form-group position-relative flex-grow-1 mb-0">
                        <div class="form-control-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                            placeholder="Cari apa saja: camp, batch, peserta, no. telp, akun, PIC, status, tanggal...">
                        @if ($search)
                        <span wire:click="$set('search', '')"
                            class="position-absolute end-0 top-50 translate-middle-y pe-3"
                            style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                            <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                        </span>
                        @endif
                    </div>
                    <button wire:click="openExportModal" type="button"
                        class="btn btn-success d-flex align-items-center justify-content-center px-4">
                        <i class="bi bi-download"></i>
                        <span class="ms-2 text-nowrap">Download</span>
                    </button>
                    <a wire:navigate href="{{ route('admin.pesananrsc.create') }}"
                        class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                        <i class="bi bi-plus-lg"></i>
                        <span class="ms-2 text-nowrap">Tambah Data</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Periode (seragam dengan Pesanan Toko) --}}
    <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden mb-4">
        <div class="card-body p-3 px-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 40px; height: 40px; font-size: 1.1rem; border-radius: 12px;">
                        <i class="bi bi-funnel"></i>
                    </span>
                    <span>Filter Periode</span>
                </div>

                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                    <select wire:model.live="filterMonth" class="form-select rounded-3" style="min-width: 180px;">
                        <option value="">Semua Bulan</option>
                        @foreach ($months as $month)
                        <option value="{{ $month['value'] }}">{{ ucfirst($month['label']) }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="filterYear" class="form-select rounded-3" style="min-width: 160px;">
                        <option value="">Semua Tahun</option>
                        @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                    @if ($search || $filterMonth || $filterYear)
                    <button wire:click="resetFilters" type="button" class="btn btn-danger rounded-3" title="Reset filter">
                        <i class="bi bi-x-circle"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr style="text-align: center;">
                                    <th>Kategori</th>
                                    <th>Batch</th>
                                    <th>Akun</th>
                                    <th class="text-center">Jumlah Peserta</th>
                                    <th class="text-center">Status</th>
                                    <th>Total Harga</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($pemesananrsc as $item)
                                <tr style="text-align: center;">
                                    <td>{{ $item->nama_camp }}</td>
                                    <td>#{{ $item->batch_camp }}</td>
                                    <td>{{ $item->dataakun?->nama_akun ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary rounded-pill px-3 py-2">{{ $item->total_peserta }} Peserta</span>
                                    </td>
                                    <td class="text-center">
                                        @php $sc = $item->status === 'baru' ? 'success' : ($item->status === 'habis' ? 'danger' : ($item->status === 'perpanjang' ? 'info' : 'warning')); @endphp
                                        <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} border border-{{ $sc }} rounded-pill px-3 py-2 text-capitalize">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                    <td>
                                        <div>
                                            {{-- Edit menuju ke batch group --}}
                                            @if (auth()->user()->hasPermission('edit_pesananrsc'))
                                            <a href="{{ route('admin.pesananrsc.edit', ['nama_camp' => $item->nama_camp, 'batch_camp' => $item->batch_camp]) }}"
                                                wire:navigate
                                                class="btn btn-sm btn-warning text-white me-1"
                                                title="Edit Batch">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @endif

                                            {{-- Delete batch --}}
                                            @if (auth()->user()->hasPermission('delete_pesananrsc'))
                                            <button type="button" title="Hapus Batch"
                                                class="btn btn-danger btn-sm rsc-delete-batch"
                                                data-nama="{{ $item->nama_camp }}" data-batch="{{ $item->batch_camp }}"
                                                data-total="{{ $item->total_peserta }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif

                                            {{-- Detail peserta --}}
                                            <a wire:navigate href="{{ route('admin.pesananrsc.detail', ['nama_camp' => urlencode($item->nama_camp), 'batch_camp' => urlencode($item->batch_camp)]) }}"
                                                class="btn btn-primary btn-sm"
                                                title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div class="empty-state-icon-wrapper mb-3">
                                                <i class="bi bi-inbox"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Data</h5>
                                            <p class="text-muted mb-0" style="font-size: 0.95rem;">Tidak ada data pemesanan RSC yang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($pemesananrsc->hasPages())
                    <div class="mt-4">
                        {{ $pemesananrsc->links('vendor.pagination') }}
                    </div>
                    @endif

                    <div class="modal fade" id="modalWaOptions" tabindex="-1" aria-labelledby="modalWaLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Kirim WhatsApp</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <p>Pilih jenis pesan yang ingin dikirim ke pelanggan:</p>
                                    <input type="hidden" id="waId">
                                    <input type="hidden" id="waIdTransaksi">
                                    <input type="hidden" id="waNumber">
                                    <input type="hidden" id="waNama">
                                    <input type="hidden" id="waAkun">
                                    <input type="hidden" id="waPemesanan">
                                    <input type="hidden" id="waBerakhir">
                                    <input type="hidden" id="waUsername">
                                    <input type="hidden" id="waPassword">
                                    <input type="hidden" id="waLinkAkses">
                                    <div class="list-group">
                                        <button class="list-group-item list-group-item-action" onclick="kirimWa('pengiriman')">📦 Pengiriman Akun</button>
                                        <button class="list-group-item list-group-item-action" onclick="kirimWa('pembaharuan')">♻️ Pembaharuan Akun</button>
                                        <button class="list-group-item list-group-item-action" onclick="kirimWa('habis')">⛔ Akun Habis</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $pemesananrsc->links('vendor.pagination') }}
                    </div>
                </div>
            </div>

            <!-- modal select batch (glossy) -->
            @if($showExportModal)
            <div class="rsc-export-backdrop" wire:click.self="closeExportModal">
                <div class="rsc-export-card">
                    {{-- Header --}}
                    <div class="rsc-export-head">
                        <span class="rsc-export-head-ico"><i class="bi bi-cloud-arrow-down-fill"></i></span>
                        <div>
                            <h5>Export Data Peserta</h5>
                            <p>Pilih batch, lalu unduh sebagai Excel atau Invoice PDF</p>
                        </div>
                        <button type="button" class="rsc-export-close" wire:click="closeExportModal"><i class="bi bi-x-lg"></i></button>
                    </div>

                    {{-- Body --}}
                    <div class="rsc-export-body">
                        <div class="rsc-export-search">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari Nama Camp atau Batch..."
                                wire:model.live.debounce.300ms="searchBatchExport">
                        </div>

                        <div class="rsc-export-list">
                            @if($this->availableBatchesForExport->isEmpty())
                            <div class="rsc-export-empty">
                                <i class="bi bi-inbox d-block mb-2" style="font-size:1.6rem;"></i>
                                Batch tidak ditemukan.
                            </div>
                            @else
                            @foreach($this->availableBatchesForExport as $batch)
                            <label class="rsc-export-row {{ in_array($batch->key, $selectedBatches) ? 'is-checked' : '' }}">
                                <input type="checkbox" class="form-check-input"
                                    value="{{ $batch->key }}" wire:model.live="selectedBatches">
                                <span class="rsc-export-row-name">{{ $batch->nama_camp }}</span>
                                <span class="rsc-export-row-batch">Batch {{ $batch->batch_camp }}</span>
                            </label>
                            @endforeach
                            @endif
                        </div>

                        <div class="rsc-export-counter">
                            <i class="bi bi-check2-circle"></i> <b>{{ count($selectedBatches) }}</b> batch dipilih
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="rsc-export-foot">
                        <button type="button" class="rsc-x-btn rsc-x-cancel" wire:click="closeExportModal">
                            <i class="bi bi-x-lg"></i> Batal
                        </button>

                        <div class="d-flex gap-2 flex-wrap">
                            @if(!empty($selectedBatches))
                            <a href="{{ route('admin.preview.invoice', ['batches' => $selectedBatches]) }}"
                                target="_blank" class="rsc-x-btn rsc-x-preview">
                                <i class="bi bi-eye"></i> Preview PDF
                            </a>
                            @endif

                            <button type="button" class="rsc-x-btn rsc-x-pdf" wire:click="exportInvoice"
                                wire:loading.attr="disabled" @disabled(empty($selectedBatches))>
                                <span wire:loading.remove wire:target="exportInvoice"><i class="bi bi-file-earmark-pdf"></i></span>
                                <span wire:loading wire:target="exportInvoice" class="spinner-border spinner-border-sm"></span>
                                Invoice
                            </button>

                            <button type="button" class="rsc-x-btn rsc-x-excel" wire:click="exportExcel"
                                wire:loading.attr="disabled" @disabled(empty($selectedBatches))>
                                <span wire:loading.remove wire:target="exportExcel"><i class="bi bi-file-earmark-excel"></i></span>
                                <span wire:loading wire:target="exportExcel" class="spinner-border spinner-border-sm"></span>
                                Data Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE (glossy, seragam banners) ==================-->
<script>
    if (!window.__rscDeleteBound) {
        window.__rscDeleteBound = true;

        const rscGlossyConfig = {
            background: 'rgba(255, 255, 255, 0.8)',
            backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: {
                popup: 'swal-glossy-popup',
                confirmButton: 'btn-glossy-confirm',
                cancelButton: 'btn-glossy-cancel',
                title: 'swal-glossy-title'
            },
            buttonsStyling: false
        };

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.rsc-delete-batch');
            if (!button) return;
            event.preventDefault();

            const nama = button.dataset.nama;
            const batch = button.dataset.batch;
            const total = button.dataset.total;

            Swal.fire({
                title: 'Yakin hapus batch ini?',
                html: `Batch <b>#${batch}</b> — <b>${nama}</b><br><span class="text-danger">${total} peserta &amp; data cashflow terkait akan dihapus permanen!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                ...rscGlossyConfig
            }).then((result) => {
                if (result.isConfirmed) {
                    const comp = button.closest('[wire\\:id]');
                    if (comp) Livewire.find(comp.getAttribute('wire:id')).call('deleteBatch', nama, batch);
                }
            });
        });

        window.addEventListener('batch-deleted', (e) => {
            Swal.fire({
                title: 'Terhapus!',
                text: (e.detail && e.detail.message) || 'Batch berhasil dihapus.',
                icon: 'success',
                timer: 2500,
                showConfirmButton: false,
                ...rscGlossyConfig
            });
        });

        window.addEventListener('batch-delete-error', (e) => {
            Swal.fire({
                title: 'Gagal!',
                text: (e.detail && e.detail.message) || 'Gagal menghapus batch.',
                icon: 'error',
                timer: 3000,
                showConfirmButton: false,
                ...rscGlossyConfig
            });
        });
    }
</script>
<!--================== END SWEET ALERT DELETE ==================-->

<!--================== MODAL PENGIRIMAN AKUN ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.send-wa-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('waId').value = this.dataset.id;
                document.getElementById('waIdTransaksi').value = this.dataset.idtransaksi;
                document.getElementById('waNumber').value = this.dataset.wa;
                document.getElementById('waNama').value = this.dataset.nama;
                document.getElementById('waAkun').value = this.dataset.akun;
                document.getElementById('waPemesanan').value = this.dataset.pemesanan;
                document.getElementById('waBerakhir').value = this.dataset.berakhir;
                document.getElementById('waUsername').value = this.dataset.username;
                document.getElementById('waPassword').value = this.dataset.password;
                document.getElementById('waLinkAkses').value = this.dataset.linkakses;
                var modal = new bootstrap.Modal(document.getElementById('modalWaOptions'));
                modal.show();
            });
        });
    });

    function kirimWa(type) {
        const idtransaksi = document.getElementById('waIdTransaksi').value;
        const nama = document.getElementById('waNama').value;
        const noWa = document.getElementById('waNumber').value;
        const akun = document.getElementById('waAkun').value;
        const pemesanan = document.getElementById('waPemesanan').value;
        const berakhir = document.getElementById('waBerakhir').value;
        const username = document.getElementById('waUsername').value;
        const password = document.getElementById('waPassword').value;
        const linkakses = document.getElementById('waLinkAkses').value;

        let pesan = '';

        if (type === 'pengiriman') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Kami dari Phoenix Digital Warehouse bermaksud mengirimkan akun ${akun} yang bisa Anda gunakan mulai tanggal ${pemesanan} dengan masa aktif sampai tanggal ${berakhir}.

Berikut detail akun Anda:

• Username: ${username}
• Password: ${password}
• Link Login: ${linkakses}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        } else if (type === 'pembaharuan') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Akun ${akun} yang anda order pada tanggal ${pemesanan} dengan masa aktif sampai tanggal ${berakhir}, terdapat pembaharuan akun ${akun}.

Berikut detail akun Anda:

• Username: ${username}
• Password: ${password}
• Link Login: ${linkakses}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        } else if (type === 'habis') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Akun ${akun} yang anda order pada tanggal ${berakhir} sudah habis. Jika Anda ingin memperpanjang akun ${akun} Anda, silakan hubungi kami.

Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        }

        const url = `https://wa.me/${noWa}?text=${encodeURIComponent(pesan)}`;
        window.open(url, '_blank');
    }
</script>
<!--================== END ==================-->