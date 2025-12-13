<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Promo</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Promo']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="searchDataPromo" type="text" class="form-control"
                        placeholder="Ketik nama promo">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.promo.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Promo</span>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle text-center" style="width:100%">
                    <thead class="table-light align-middle">
                        <tr>
                            <th style="width: 200px;">Nama Promo</th>
                            <th style="width: 120px;">Kode Promo</th>
                            <th style="width: 100px;">Tipe Promo</th>
                            <th style="width: 120px;">Diskon Member</th>
                            <th style="width: 140px;">Diskon Non-Member</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;">Periode</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($promos as $item)
                            <tr style="text-align: center;">
                                <!-- Nama Promo -->
                                <td class="fw-semibold text-start">
                                    {{ $item->nama_promo }}
                                    @if ($item->show_on_homepage)
                                        <span class="badge bg-info ms-1">Homepage</span>
                                    @endif
                                </td>

                                <!-- Kode Promo -->
                                <td class="fw-semibold">
                                    @if ($item->kode_promo)
                                        <span class="badge bg-secondary">{{ $item->kode_promo }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Tipe Promo -->
                                <td>
                                    @php
                                        $tipeClass = match ($item->tipe_promo) {
                                            'flash_sale' => 'bg-danger',
                                            'kode_promo' => 'bg-primary',
                                            'referral_bonus' => 'bg-success',
                                            default => 'bg-secondary',
                                        };
                                        $tipeText = match ($item->tipe_promo) {
                                            'flash_sale' => 'Flash Sale',
                                            'kode_promo' => 'Kode Promo',
                                            'referral_bonus' => 'Referral',
                                            default => $item->tipe_promo,
                                        };
                                    @endphp
                                    <span class="badge {{ $tipeClass }}">{{ $tipeText }}</span>
                                </td>

                                <!-- Diskon Member -->
                                <td class="fw-semibold">
                                    @if ($item->tipe_diskon === 'persen')
                                        {{ $item->diskon_member_persen }}%
                                    @else
                                        Rp {{ number_format($item->diskon_member_nominal, 0, ',', '.') }}
                                    @endif
                                </td>

                                <!-- Diskon Non-Member -->
                                <td class="fw-semibold">
                                    @if ($item->tipe_diskon === 'persen')
                                        {{ $item->diskon_non_member_persen }}%
                                    @else
                                        Rp {{ number_format($item->diskon_non_member_nominal, 0, ',', '.') }}
                                    @endif
                                </td>

                                <!-- Status -->
                                <td>
                                    @if ($item->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>

                                <!-- Periode -->
                                <td class="text-start small">
                                    <div>{{ $item->mulai_promo->format('d/m/Y H:i') }}</div>
                                    <div class="text-muted">s/d</div>
                                    <div>{{ $item->selesai_promo->format('d/m/Y H:i') }}</div>
                                </td>

                                <!-- Action -->
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a wire:navigate href="{{ route('admin.promo.edit', $item->id) }}"
                                            class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" wire:click="$dispatch('will-delete-promo-data', {{$item}})"
                                            class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">
                                    Belum ada data promo
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $promos->links() }}
            </div>
        </div>
    </div>
</div>
