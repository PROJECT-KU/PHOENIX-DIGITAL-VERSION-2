<div class="mb-3">
    <!--================== ACTION BAR: SEARCH + EXPORT + CREATE ==================-->
    <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-2 mb-3 header-action">
        <!-- Search -->
        <div class="form-group position-relative flex-grow-1 mb-0">
            <div class="form-control-icon">
                <i class="bi bi-search"></i>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                placeholder="Cari penginput, deskripsi, PIC, atau tanggal (mis. Juni 2026)...">
            @if ($search)
            <span wire:click="$set('search', '')"
                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
            </span>
            @endif
        </div>

        <!-- Export + Create -->
        <div class="d-flex gap-2">
            <button wire:click="downloadPdf" wire:loading.attr="disabled" wire:target="downloadPdf"
                class="btn btn-danger rounded-pill d-flex align-items-center justify-content-center gap-2 px-3">
                <span wire:loading.remove wire:target="downloadPdf" class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <span class="d-none d-md-inline">Export PDF</span>
                </span>
            </button>
            <a class="btn btn-primary rounded-pill d-flex align-items-center justify-content-center gap-2 px-3"
                href="{{ route('admin.spending.create') }}" wire:navigate>
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-lg-inline">Tambah Data</span>
            </a>
        </div>
    </div>

    <!--================== FILTER PERIODE ==================-->
    <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden">
        <div class="card-body p-3 px-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 40px; height: 40px; font-size: 1.1rem; border-radius: 12px;">
                        <i class="bi bi-funnel"></i>
                    </span>
                    <span>Filter Periode</span>
                </div>

                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                    <select wire:model.live="bulan" class="form-select rounded-3" style="min-width: 160px;">
                        <option value="">Semua Bulan</option>
                        @foreach ($daftarBulan as $num => $nama)
                        <option value="{{ $num }}">{{ $nama }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="tahun" class="form-select rounded-3" style="min-width: 130px;">
                        <option value="">Semua Tahun</option>
                        @foreach ($daftarTahun as $th)
                        <option value="{{ $th }}">{{ $th }}</option>
                        @endforeach
                    </select>

                    @if ($bulan || $tahun)
                    <button wire:click="resetFilter" type="button" class="btn btn-danger rounded-3"
                        title="Reset filter">
                        <i class="bi bi-x-circle"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>