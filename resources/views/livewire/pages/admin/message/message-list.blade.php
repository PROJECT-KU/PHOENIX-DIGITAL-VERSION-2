<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Pesan Masuk</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pesan Masuk']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <!-- Filter Section -->
            <div class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter Bulan</label>
                        <select wire:model.live="filterMonth" class="form-select">
                            <option value="">Semua Bulan</option>
                            @foreach ($months as $month)
                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Pesan</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="unread">Belum Dibaca</option>
                            <option value="read">Sudah Dibaca</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button wire:click="resetFilters" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table text-center align-middle" style="width:100%">
                    <thead class="align-middle table-light">
                        <tr>
                            <th style="width: 50px;">Status</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $item)
                        <tr class="{{ is_null($item->read_at) ? 'table-warning' : '' }}">
                            <td>
                                @if (is_null($item->read_at))
                                <span class="badge bg-warning">Baru</span>
                                @else
                                <i class="text-success bi bi-check-circle-fill"></i>
                                @endif
                            </td>
                            <td class="fw-semibold text-capitalize">
                                {{ $item->name }}
                                @if (is_null($item->read_at))
                                <i class="text-primary bi bi-dot"></i>
                                @endif
                            </td>
                            <td>
                                {{ $item->email }}
                            </td>
                            <td class="text-muted small">
                                {{ $item->created_at->diffForHumans() }}
                            </td>
                            <td>
                                <div class="gap-2 d-flex justify-content-center">
                                    <a wire:navigate href="{{ route('admin.message.detail', $item) }}"
                                        class="text-black btn btn-sm btn-warning" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" wire:click="$dispatch('will-delete-message-data', {{$item}})"
                                        class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-3 text-center text-muted">
                                Belum ada pesan masuk
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $messages->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>