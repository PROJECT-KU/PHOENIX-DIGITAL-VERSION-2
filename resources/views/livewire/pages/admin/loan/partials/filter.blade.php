<div x-data="{ showFilter: $persist(false) }">
    <!-- Search + Filter Toggle -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex gap-2">
            <div class="form-group mb-0 position-relative has-icon-left">
                <input wire:model.live.debounce.300ms="search" type="text"
                    class="form-control border-secondary"
                    placeholder="Cari nama peminjam, penginput, atau deskripsi...">
                <div class="form-control-icon">
                    <i class="bi bi-search"></i>
                </div>
            </div>
            <button type="button" @click="showFilter = !showFilter"
                class="btn btn-outline-secondary d-flex gap-1">
                <i class="bi bi-filter"></i>
                <span>Filter</span>
            </button>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button wire:click="exportExcel" class="btn btn-success rounded-pill">
                <i class="bi bi-file-earmark-excel"></i> 
                <span class="d-none d-md-inline"> Export Excel
            </button>
            <a class="btn btn-primary rounded-pill" href="{{ route('admin.loan.create') }}" wire:navigate>
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-lg-inline">Tambah Data Peminjaman</span>
            </a>
        </div>
        
    </div>

    <!-- Filter Card -->
    <div class="card border pt-3 pb-0 px-4"
        x-show="showFilter" x-transition x-cloak>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="fw-semibold">Filter Tabel</p>
            <button wire:click="clearFilters" class="btn btn-sm btn-secondary">
                <i class="bi bi-x-circle me-1"></i> Reset
            </button>
        </div>
        <div class="row mb-3">
            <div class="col-lg-2 mb-2">
                <label class="fw-semibold mb-1">Status</label>
                <select wire:model.live="statusFilter" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($statusOptions as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-2">
                <label class="fw-semibold mb-1">Penginput</label>
                <select wire:model.live="penginputFilter" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 mb-2">
                <label class="fw-semibold mb-1">Jarak Tanggal</label>
                <div class="d-flex gap-1">
                    <input type="date" wire:model.live="startDate" class="form-control form-control-sm">
                    <input type="date" wire:model.live="endDate" class="form-control form-control-sm">
                </div>
            </div>
            <div class="col-lg-2 mb-2">
                <label class="fw-semibold mb-1">Per Page</label>
                <select wire:model.live="perPage" class="form-select form-select-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>
</div>