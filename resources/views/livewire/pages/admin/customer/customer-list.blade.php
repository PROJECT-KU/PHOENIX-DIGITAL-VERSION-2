<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Manajemen Data Pelanggan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pelanggan']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="searchCustomer" type="text" class="form-control"
                                placeholder="ketik nama, no hp atau email pelanggan">

                            @if ($searchCustomer)
                            <span wire:click="$set('searchCustomer', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .customer-glossy-tabs {
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
            }

            .customer-glossy-tab {
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
                text-transform: capitalize;
                white-space: nowrap;
            }

            .customer-glossy-tab i {
                font-size: 1.25rem;
                line-height: 1;
                display: inline-flex;
                align-items: center;
            }

            .customer-glossy-tab:hover:not(.active) {
                color: #4e46e5;
                background: rgba(108, 99, 255, 0.10);
            }

            .customer-glossy-tab.active {
                color: #fff;
                background: linear-gradient(135deg, #6c63ff, #4e46e5);
                box-shadow: 0 6px 16px rgba(78, 70, 229, 0.45);
                transform: translateY(-1px);
            }

            .customer-glossy-tab .tab-count {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 1.75rem;
                height: 1.75rem;
                padding: 0 .55rem;
                font-size: .82rem;
                font-weight: 800;
                line-height: 1;
                border-radius: 999px;
                color: #fff;
                background: linear-gradient(135deg, #7c73ff, #4e46e5);
                border: 1px solid rgba(255, 255, 255, 0.45);
                box-shadow: 0 4px 10px rgba(78, 70, 229, 0.40), inset 0 1px 1px rgba(255, 255, 255, 0.45);
                transition: all .25s ease;
            }

            .customer-glossy-tab:hover:not(.active) .tab-count {
                transform: scale(1.08);
            }

            .customer-glossy-tab.active .tab-count {
                color: #4e46e5;
                background: linear-gradient(135deg, #ffffff, #eef0ff);
                border-color: rgba(255, 255, 255, 0.9);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18), inset 0 1px 1px rgba(255, 255, 255, 0.9);
            }

            @media (max-width: 575.98px) {
                .customer-glossy-tabs {
                    display: flex;
                    width: 100%;
                }

                .customer-glossy-tab {
                    flex: 1;
                    justify-content: center;
                    padding: .6rem .6rem;
                }
            }
        </style>

        @php
        $allCustomerCount = \App\Models\Customer::count();
        $memberCustomerCount = \App\Models\Customer::where('status_member', 'active')->count();
        @endphp

        <div class="mt-3 mb-3">
            <div class="customer-glossy-tabs">
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'all') active @endif"
                    wire:click="setTab('all')">
                    <i class="bi bi-people-fill"></i>
                    <span>Data Pelanggan</span>
                    <span class="tab-count">{{ $allCustomerCount }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'member') active @endif"
                    wire:click="setTab('member')">
                    <i class="bi bi-patch-check-fill"></i>
                    <span>Data Member</span>
                    <span class="tab-count">{{ $memberCustomerCount }}</span>
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Nama Pelanggan</th>
                                <th>Email Pelanggan</th>
                                <th>Nomor Handphone</th>
                                <th>Status Member</th>
                                <th>Jumlah Poin</th>
                                <th>Kode Referral</th>
                                @if (auth()->user()->hasAnyPermission(['edit_customer', 'delete_customer']))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $customer)
                            <tr class="text-center">
                                <td>{{ $customer->nama }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->no_hp }}</td>
                                <td>
                                    <span
                                        class="badge {{ $customer->status_member === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($customer->status_member) }}
                                    </span>
                                </td>
                                <td>{{ $customer->point }}</td>
                                <td>{{ $customer->kode_ref }}</td>
                                @if (auth()->user()->hasAnyPermission(['edit_customer', 'delete_customer']))
                                <td>
                                    @if (auth()->user()->hasPermission('edit_customer'))
                                    <a wire:navigate href="{{ route('admin.customer.edit', $customer) }}"
                                        class="btn btn-warning btn-sm me-1">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_customer'))
                                    <button type="button"
                                        wire:click="$dispatch('will-delete-customer-data', {{ $customer }})"
                                        class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Pelanggan
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Data pelanggan belum tersedia untuk ditampilkan saat ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $customers->links('vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>