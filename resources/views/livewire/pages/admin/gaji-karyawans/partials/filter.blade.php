<div x-data="{ showFilter: $persist(false) }">
    <!-- Search Bar -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="form-group mb-0 position-relative has-icon-left">
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-secondary"
                    placeholder="Cari berdasarkan nama penginput, deskripsi, atau nama PIC pembeli...">
                <div class="form-control-icon">
                    <i class="bi bi-search" style="font-size: 14px;"></i>
                </div>
            </div>
            <button type="button" @click="showFilter = !showFilter" class="btn btn-outline-secondary d-flex gap-1">
                <i class="bi bi-filter"></i>
                <span>filter</span>
            </button>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button wire:click="exportExcel" class="btn btn-success rounded-pill">
                <i class="bi bi-file-earmark-excel"></i> 
                <span class="d-none d-md-inline"> Export Excel
            </button>
            <a class="btn btn-primary rounded-pill" href="{{ route('admin.gajikaryawan.create') }}" wire:navigate>
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-lg-inline">Tambah Data Gaji Karyawan</span>
            </a>
        </div>
    </div>
    <div class="card border pt-3 pb-0 px-4" x-show="showFilter" x-transition x-transition:enter.duration.500ms
        x-transition:leave.duration.400ms x-cloak>
        <div class="d-flex align-items-center justify-content-between">
            <p class="fw-semibold text-dark">filter tabel</p>
            <div class="">
                <button wire:click="clearFilters" class="btn btn-sm btn-secondary w-100">
                    <i class="bi bi-x-circle me-2"></i>
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-lg-2 mb-2">
                <label for="idtransaksiFilter" style="font-size: 14px;" class="fw-semibold mb-1">ID Transaksi</label>
                <select wire:model.live="idtransaksiFilter" id="idtransaksiFilter" class="form-select form-select-sm">
                    <option value="">Semua ID Transaksi</option>
                    @foreach ($idTransaksiOptions as $idtransaksi)
                    <option value="{{ $idtransaksi }}">{{ $idtransaksi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label for="norekFilter" style="font-size: 14px;" class="fw-semibold mb-1">Nomor Rekening</label>
                <select wire:model.live="norekFilter" id="norekFilter" class="form-select form-select-sm">
                    <option value="">Semua Nomor Rekening</option>
                    @foreach ($norekOptions as $norek)
                    <option value="{{ $norek }}">{{ $norek }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label for="statusFilter" style="font-size: 14px;" class="fw-semibold mb-1">status</label>
                <select wire:model.live="statusFilter" id="statusFilter" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label for="karyawanFilter" style="font-size: 14px;" class="fw-semibold mb-1">Nama Karyawan</label>
                <select wire:model.live="karyawanFilter" class="form-select form-select-sm" id="karyawanFilter">
                    <option value="">Semua Karyawan</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-2">
                <label for="startDate" style="font-size: 14px;" class="fw-semibold mb-1">Jarak Tanggal Transaksi</label>
                <div class="d-flex gap-1">
                    <input type="date" wire:model.live="startDate" class="form-control form-control-sm"
                        id="startDate">
                    <input type="date" wire:model.live="endDate" class="form-control form-control-sm">
                </div>
            </div>
            <div class="col-lg-1 mb-2">
                <label for="perPage" style="font-size: 14px;" class="fw-semibold mb-1">per page</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" id="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>
</div>