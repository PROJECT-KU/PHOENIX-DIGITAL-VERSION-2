
@section('title')
Data Karyawan || lemon
@stop
<div>
    <!--================== GLOSSY STYLE ==================-->
    <style>
        .stat-icon-wrapper {
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .kry-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        /* Nomor Induk Karyawan — monospace agar mudah dibaca & dibedakan. */
        .kry-nik {
            display: inline-block;
            font-family: 'Courier New', monospace;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .5px;
            color: #4e46e5;
            background: #eef2ff;
            border-radius: 6px;
            padding: 1px 7px;
            margin-top: 2px;
        }

        /* Alamat dipangkas 2 baris; teks penuh muncul saat hover (title). */
        .kry-alamat {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 260px;
        }

        /* Bootstrap versi ini tak punya utility .min-w-0. Tanpa min-width:0,
           item flex tak bisa menyusut di bawah lebar kontennya — email panjang
           mendorong badge Role keluar dari kartu. Definisikan di sini. */
        .min-w-0 {
            min-width: 0 !important;
        }

        /* Select filter role: melebar penuh di mobile, ringkas di desktop. */
        .kry-role-select {
            min-width: 0;
        }

        @media (min-width: 576px) {
            .kry-role-select {
                min-width: 160px;
                max-width: 190px;
            }
        }

        /* ===== Kartu karyawan (tampilan mobile) ===== */
        .kry-mcard {
            border: 1px solid #eef0f6;
            border-radius: 16px;
            padding: 1rem;
            background: #fff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .04);
        }

        .kry-mcard+.kry-mcard {
            margin-top: .8rem;
        }

        .kry-mrow {
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            font-size: .85rem;
            padding: .5rem 0;
            border-top: 1px dashed #eef0f6;
        }

        .kry-mrow i.bi {
            color: #94a3b8;
            margin-top: 2px;
        }

        .kry-mrow-lbl {
            color: #94a3b8;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER + TOOLBAR (SATU CARD) ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Data Karyawan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Karyawan'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 header-action" style="flex: 1 1 auto; max-width: 620px;">
                        <!-- Search -->
                        <div class="form-group position-relative mb-0 flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari nama atau email...">
                            @if ($search)
                            <span wire:click="$set('search', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>

                        <!-- Filter Role + Reset (satu baris, rapi di mobile) -->
                        <div class="d-flex gap-2">
                            <select wire:model.live="filterRole"
                                class="form-select text-capitalize kry-role-select flex-grow-1 flex-sm-grow-0">
                                <option value="">Semua Role</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>

                            @if ($search || $filterRole)
                            <button wire:click="resetFilters" type="button"
                                class="btn btn-light border d-inline-flex align-items-center justify-content-center px-3 flex-shrink-0"
                                title="Reset filter">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            @endif
                        </div>

                        @if (auth()->user()->hasPermission('create_karyawan'))
                        <a href="{{ route('admin.karyawan.create') }}" wire:navigate
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4 flex-shrink-0">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!--================== RINGKASAN STAT ==================-->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff;">
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Karyawan</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $totalKaryawan }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Role</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $roles->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABEL ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive d-none d-lg-block">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Karyawan</th>
                                <th>Jabatan</th>
                                <th>Role</th>
                                <th class="text-start">Kontak &amp; Alamat</th>
                                <th>Info Bank</th>
                                @if (auth()->user()->hasAnyPermission(['edit_karyawan', 'delete_karyawan']))
                                <th width="120">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $avatarGrad = ['#7c3aed,#6d28d9', '#2563eb,#0ea5e9', '#059669,#10b981', '#e11d48,#f43f5e', '#d97706,#f59e0b'];
                            @endphp
                            @forelse($users as $user)
                            <tr style="text-align: center;">
                                <td class="text-start">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="kry-avatar" style="background: linear-gradient(135deg,{{ $avatarGrad[$loop->index % count($avatarGrad)] }});">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                        {{-- text-start: baris <tr> di-center, tanpa ini nama/email/NIK
                                             ikut ter-center di lebar yang berbeda-beda tiap baris. --}}
                                        <div class="text-start">
                                            <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                                                {{ $user->name }}
                                                @if(($user->status ?? 'active') === 'blokir')
                                                <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill" style="font-size:.66rem;"><i class="bi bi-lock-fill me-1"></i>Blokir</span>
                                                @endif
                                            </div>
                                            <small class="d-block text-muted">{{ $user->email }}</small>
                                            @if($user->detail?->nik)
                                            <span class="kry-nik">{{ $user->detail->nik }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    <div>{{ $user->detail->jabatan ?? '-' }}</div>
                                    @if($user->detail?->masaKerja())
                                    <small class="text-success fw-semibold"><i class="bi bi-briefcase me-1"></i>{{ $user->detail->masaKerja() }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-gradient-blue rounded-pill px-3 py-2 text-capitalize">
                                        {{ $user->role->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-start" style="font-size:.85rem;">
                                    @if($user->detail?->phone)
                                    <div class="text-dark"><i class="bi bi-telephone text-muted me-1"></i>{{ $user->detail->phone }}</div>
                                    @endif
                                    @if($user->detail?->alamat)
                                    <div class="kry-alamat text-muted" title="{{ $user->detail->alamat }}">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $user->detail->alamat }}
                                    </div>
                                    @endif
                                    @unless($user->detail?->phone || $user->detail?->alamat)
                                    <span class="text-muted">-</span>
                                    @endunless
                                </td>
                                <td>
                                    @if($user->detail && $user->detail->nama_bank)
                                    <span class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $user->detail->nama_bank }}</span>
                                    <small class="d-block text-muted">{{ $user->detail->nomor_rekening }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_karyawan', 'delete_karyawan']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_karyawan'))
                                    <a href="{{ route('admin.karyawan.edit', $user) }}" wire:navigate
                                        class="btn btn-warning btn-sm text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_karyawan'))
                                    <button type="button" class="btn btn-danger btn-sm p-2 delete-karyawan-btn"
                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                        title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Karyawan</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Tambahkan karyawan baru atau ubah filter pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== Kartu (tampilan mobile) ===== --}}
                <div class="d-lg-none">
                    @php
                    $avatarGradM = ['#7c3aed,#6d28d9', '#2563eb,#0ea5e9', '#059669,#10b981', '#e11d48,#f43f5e', '#d97706,#f59e0b'];
                    @endphp
                    @forelse($users as $user)
                    <div class="kry-mcard">
                        {{-- Header: avatar + nama + email + NIK --}}
                        <div class="d-flex align-items-center gap-3">
                            <span class="kry-avatar" style="background: linear-gradient(135deg,{{ $avatarGradM[$loop->index % count($avatarGradM)] }});">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-dark d-flex align-items-center gap-2 flex-wrap">
                                    {{ $user->name }}
                                    @if(($user->status ?? 'active') === 'blokir')
                                    <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill" style="font-size:.62rem;"><i class="bi bi-lock-fill me-1"></i>Blokir</span>
                                    @endif
                                </div>
                                <small class="d-block text-muted text-truncate">{{ $user->email }}</small>
                                @if($user->detail?->nik)
                                <span class="kry-nik">{{ $user->detail->nik }}</span>
                                @endif
                            </div>
                            <span class="badge bg-gradient-blue rounded-pill px-3 py-2 text-capitalize flex-shrink-0" style="font-size:.7rem;">
                                {{ $user->role->name ?? '-' }}
                            </span>
                        </div>

                        {{-- Jabatan & masa kerja --}}
                        <div class="kry-mrow">
                            <i class="bi bi-briefcase"></i>
                            <div>
                                <div class="kry-mrow-lbl">Jabatan</div>
                                <div class="text-dark">{{ $user->detail->jabatan ?? '-' }}
                                    @if($user->detail?->masaKerja())
                                    <span class="text-success fw-semibold">· {{ $user->detail->masaKerja() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Kontak & alamat --}}
                        @if($user->detail?->phone || $user->detail?->alamat)
                        <div class="kry-mrow">
                            <i class="bi bi-geo-alt"></i>
                            <div class="min-w-0">
                                <div class="kry-mrow-lbl">Kontak &amp; Alamat</div>
                                @if($user->detail?->phone)<div class="text-dark"><i class="bi bi-telephone me-1"></i>{{ $user->detail->phone }}</div>@endif
                                @if($user->detail?->alamat)<div class="text-muted">{{ $user->detail->alamat }}</div>@endif
                            </div>
                        </div>
                        @endif

                        {{-- Info bank --}}
                        @if($user->detail && $user->detail->nama_bank)
                        <div class="kry-mrow">
                            <i class="bi bi-bank"></i>
                            <div>
                                <div class="kry-mrow-lbl">Info Bank</div>
                                <div class="text-dark">{{ $user->detail->nama_bank }} <span class="text-muted">· {{ $user->detail->nomor_rekening }}</span></div>
                            </div>
                        </div>
                        @endif

                        {{-- Aksi --}}
                        @if (auth()->user()->hasAnyPermission(['edit_karyawan', 'delete_karyawan']))
                        <div class="d-flex gap-2 mt-3">
                            @if (auth()->user()->hasPermission('edit_karyawan'))
                            <a href="{{ route('admin.karyawan.edit', $user) }}" wire:navigate
                                class="btn btn-warning btn-sm text-white flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            @endif
                            @if (auth()->user()->hasPermission('delete_karyawan'))
                            <button type="button" class="btn btn-danger btn-sm flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1 delete-karyawan-btn"
                                data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                            @endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <div class="empty-state-icon-wrapper mb-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Karyawan</h5>
                            <p class="text-muted mb-0" style="font-size: 0.95rem;">Tambahkan karyawan baru atau ubah filter pencarian.</p>
                        </div>
                    </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $users->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE (GLOSSY, SEPERTI BANNERS) ==================-->
<script>
    const glossyConfigKaryawan = {
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

    document.addEventListener('livewire:navigated', function() {
        document.body.addEventListener('click', function(event) {
            const button = event.target.closest('.delete-karyawan-btn');

            if (button) {
                event.preventDefault();
                const karyawanId = button.getAttribute('data-id');
                const karyawanName = button.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: 'Data karyawan ' + (karyawanName || '') + ' akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigKaryawan
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            const livewireComponentId = component.getAttribute('wire:id');
                            Livewire.find(livewireComponentId).call('delete', karyawanId);
                        }
                    }
                });
            }
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->