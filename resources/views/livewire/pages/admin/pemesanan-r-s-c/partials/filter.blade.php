<div x-data="{ showFilter: $persist(false) }">
    <!-- Search Bar -->
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <div class="gap-2 d-flex align-items-center">
            <div class="mb-0 form-group position-relative has-icon-left">
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-secondary"
                    placeholder="Cari berdasarkan nama penginput, deskripsi, atau nama PIC pembeli...">
                <div class="form-control-icon">
                    <i class="bi bi-search" style="font-size: 14px;"></i>
                </div>
            </div>
            <button type="button" @click="showFilter = !showFilter" class="gap-1 btn btn-outline-secondary d-flex">
                <i class="bi bi-filter"></i>
                <span>filter</span>
            </button>
        </div>
        <a class="btn btn-primary rounded-pill" href="{{ route('admin.pesananrsc.create') }}" wire:navigate>
            <i class="bi bi-plus-lg"></i>
            <span class="d-none d-lg-inline">Tambah Data Pemesanan</span>
        </a>
    </div>
    <div class="px-4 pt-3 pb-0 border card" x-show="showFilter" x-transition x-transition:enter.duration.500ms
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
        <div class="mb-3 row">
            <div class="mb-2 col-lg-2">
                <label for="statusFilter" style="font-size: 14px;" class="mb-1 fw-semibold">status</label>
                <select wire:model.live="statusFilter" id="statusFilter" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2 col-lg-2">
                <label for="jenisPengeluaran" style="font-size: 14px;" class="mb-1 fw-semibold">Jenis
                    Pengeluaran</label>
                <select wire:model.live="jenisPengeluaran" class="form-select form-select-sm" id="jenisPengeluaran">
                    <option value="">Semua Jenis</option>
                    @foreach ($jenisPengeluaranOptions as $jenis)
                    <option value="{{ $jenis }}">
                        {{ $jenis === 'pembelian_akun' ? 'Penjualan Akun' : 'Lainnya' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2 col-lg-2">
                <label for="penginputFilter" style="font-size: 14px;" class="mb-1 fw-semibold">penginput data</label>
                <select wire:model.live="penginputFilter" class="form-select form-select-sm" id="penginputFilter">
                    <option value="">Semua Penginput</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2 col-lg-2">
                <label for="picPembeliFilter" style="font-size: 14px;" class="mb-1 fw-semibold">PIC pembeli akun</label>
                <select wire:model.live="picPembeliFilter" class="form-select form-select-sm" id="picPembeliFilter">
                    <option value="">Semua PIC</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2 col-lg-3">
                <label for="startDate" style="font-size: 14px;" class="mb-1 fw-semibold">jarak tanggal</label>
                <div class="gap-1 d-flex">
                    <input type="date" wire:model.live="startDate" class="form-control form-control-sm"
                        id="startDate">
                    <input type="date" wire:model.live="endDate" class="form-control form-control-sm">
                </div>
            </div>
            <div class="mb-2 col-lg-1">
                <label for="perPage" style="font-size: 14px;" class="mb-1 fw-semibold">per page</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" id="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>
</div>