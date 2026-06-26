<div>
    <!--================== GLOSSY STYLE ==================-->
    <style>
        .stat-icon-wrapper {
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .stat-icon-wrapper i,
        .perm-ico i,
        .gt-ico i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .stat-icon-wrapper i::before,
        .perm-ico i::before,
        .gt-ico i::before {
            display: block;
            line-height: 1;
        }

        .category-progress {
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: #eef0f6;
            overflow: hidden;
        }

        .category-progress-bar {
            height: 100%;
            border-radius: 999px;
            transition: width 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .perm-card {
            background: rgba(255, 255, 255, 0.95);
            transition: 0.2s;
        }

        .perm-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(100, 116, 139, 0.12) !important;
        }

        .grand-total-card {
            background: linear-gradient(135deg, #4f46e5, #0ea5e9);
            border-radius: 20px;
            box-shadow: 0 12px 28px rgba(56, 70, 220, 0.25);
        }

        .grand-total-card .gt-ico {
            width: 46px;
            height: 46px;
            font-size: 1.35rem;
            border-radius: 14px;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
        }

        /* ===== Chip permission (warna mengikuti jenis aksi) ===== */
        .perm-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border: 1.5px solid #e7e9f2;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.18s;
            background: #fff;
            height: 100%;
            margin-bottom: 0;
        }

        .perm-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.12);
        }

        .perm-ico {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .perm-label {
            font-weight: 600;
            font-size: 0.83rem;
            color: #475569;
            line-height: 1.2;
        }

        .perm-check {
            margin-left: auto;
            color: #cbd5e1;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
        }

        /* warna dasar per aksi */
        .act-view .perm-ico {
            background: #eff6ff;
            color: #2563eb;
        }

        .act-create .perm-ico {
            background: #ecfdf5;
            color: #059669;
        }

        .act-edit .perm-ico {
            background: #fffbeb;
            color: #d97706;
        }

        .act-delete .perm-ico {
            background: #fef2f2;
            color: #e11d48;
        }

        /* state aktif per aksi */
        .perm-chip.active.act-view {
            border-color: #2563eb;
            background: #f5f9ff;
        }

        .perm-chip.active.act-create {
            border-color: #059669;
            background: #f3fdf8;
        }

        .perm-chip.active.act-edit {
            border-color: #d97706;
            background: #fffdf5;
        }

        .perm-chip.active.act-delete {
            border-color: #e11d48;
            background: #fff5f6;
        }

        .perm-chip.active.act-view .perm-ico {
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            color: #fff;
        }

        .perm-chip.active.act-create .perm-ico {
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff;
        }

        .perm-chip.active.act-edit .perm-ico {
            background: linear-gradient(135deg, #d97706, #f59e0b);
            color: #fff;
        }

        .perm-chip.active.act-delete .perm-ico {
            background: linear-gradient(135deg, #e11d48, #f43f5e);
            color: #fff;
        }

        .perm-chip.active.act-view .perm-label {
            color: #1d4ed8;
        }

        .perm-chip.active.act-create .perm-label {
            color: #047857;
        }

        .perm-chip.active.act-edit .perm-label {
            color: #b45309;
        }

        .perm-chip.active.act-delete .perm-label {
            color: #be123c;
        }

        .perm-chip.active.act-view .perm-check {
            color: #2563eb;
        }

        .perm-chip.active.act-create .perm-check {
            color: #059669;
        }

        .perm-chip.active.act-edit .perm-check {
            color: #d97706;
        }

        .perm-chip.active.act-delete .perm-check {
            color: #e11d48;
        }

        .form-switch .form-check-input {
            cursor: pointer;
            width: 2.4em;
            height: 1.3em;
        }

        .form-switch .form-check-input:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
    </style>

    @php
    $totalModul = count($groupedPermissions);
    $totalPermission = collect($groupedPermissions)->sum(fn ($g) => count($g));
    $dipilih = count($selectedPermissions);
    $persenTotal = $totalPermission > 0 ? round(($dipilih / $totalPermission) * 100) : 0;

    $ikonAksi = ['view' => 'bi-eye-fill', 'create' => 'bi-plus-lg', 'edit' => 'bi-pencil-fill', 'delete' => 'bi-trash-fill'];

    // Helper label modul (pakai moduleMeta, fallback nama group).
    $labelModul = fn ($g) => $moduleMeta[$g][0] ?? ucwords(str_replace('_', ' ', $g));

    // Modul hasil filter pencarian (cocok di label maupun key group).
    $cari = trim(strtolower($searchModul));
    $modulTampil = collect($groupedPermissions)->filter(function ($p, $g) use ($cari, $labelModul) {
    if ($cari === '') return true;
    return str_contains(strtolower($labelModul($g)), $cari) || str_contains(strtolower($g), $cari);
    });
    @endphp

    <div class="container-fluid">
        <!--================== HEADER ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start">
                        <h3 class="gradient-text fw-bold mb-1">Kelola Permission Role</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Manajemen Role', 'url' => route('admin.account.role')],
                            ['name' => 'Kelola Permission'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== RINGKASAN STAT ==================-->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Role</p>
                            <h5 class="fw-bold mb-0 text-dark text-capitalize">{{ $role->name }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff;">
                            <i class="bi bi-collection-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Modul</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $totalModul }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#059669,#10b981); color:#fff;">
                            <i class="bi bi-key-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Permission</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $totalPermission }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#e11d48,#f43f5e); color:#fff;">
                            <i class="bi bi-check2-square"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Dipilih</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $dipilih }} <small class="text-muted fw-normal" style="font-size: 0.85rem;">/ {{ $totalPermission }}</small></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== SEARCH MODUL ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted"
                        style="left: 16px; font-size: 0.95rem;"></i>
                    <input type="text" wire:model.live.debounce.300ms="searchModul"
                        class="form-control rounded-pill ps-5 pe-5"
                        style="height: 50px;"
                        placeholder="Cari modul... (mis. produk, gaji, pesanan)">
                    @if ($searchModul)
                    <span wire:click="$set('searchModul', '')" role="button"
                        class="position-absolute top-50 translate-middle-y text-muted"
                        style="right: 16px; cursor: pointer;">
                        <i class="bi bi-x-circle-fill"></i>
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save">
            @if ($totalModul > 0)
            <div class="row g-3 align-items-stretch">
                @forelse ($modulTampil as $groupName => $permissions)
                @php
                $groupIds = collect($permissions)->pluck('id');
                $groupSelected = $groupIds->filter(fn ($id) => in_array($id, $selectedPermissions))->count();
                $groupTotal = $groupIds->count();
                $persen = $groupTotal > 0 ? round(($groupSelected / $groupTotal) * 100) : 0;
                $meta = $moduleMeta[$groupName] ?? [ucwords(str_replace('_', ' ', $groupName)), 'bi-grid-1x2-fill', '#64748b,#94a3b8'];
                $namaModul = $meta[0];
                $ikonModul = $meta[1];
                $gradModul = $meta[2];
                $warnaModul = explode(',', $gradModul)[0];
                @endphp
                <div class="col-12 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 perm-card overflow-hidden">
                        <div class="card-body p-4">
                            <!-- Header grup -->
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="stat-icon-wrapper flex-shrink-0"
                                    style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px; background: linear-gradient(135deg,{{ $gradModul }}); color:#fff;">
                                    <i class="bi {{ $ikonModul }}"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $namaModul }}</h6>
                                    <small class="text-muted">{{ $groupSelected }} dari {{ $groupTotal }} aktif</small>
                                </div>
                                <div class="form-check form-switch m-0" title="Pilih semua">
                                    <input class="form-check-input" type="checkbox"
                                        id="group_{{ $groupName }}"
                                        wire:click="toggleGroup('{{ $groupName }}')"
                                        {{ $this->isGroupFullySelected($groupName) ? 'checked' : '' }}>
                                </div>
                            </div>

                            <!-- Progress -->
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted" style="font-size: 0.74rem;">Akses Diberikan</span>
                                <span class="fw-bold" style="font-size: 0.74rem; color: {{ $warnaModul }};">{{ $persen }}%</span>
                            </div>
                            <div class="category-progress mb-3">
                                <div class="category-progress-bar" style="width: {{ $persen }}%; background: linear-gradient(135deg,{{ $gradModul }});"></div>
                            </div>

                            <!-- Chip permission -->
                            <div class="row g-2">
                                @foreach ($permissions as $permission)
                                @php
                                $aktif = in_array($permission->id, $selectedPermissions);
                                $aksi = \Illuminate\Support\Str::before($permission->name, '_');
                                $aksiKelas = in_array($aksi, ['view', 'create', 'edit', 'delete']) ? $aksi : 'view';
                                $ico = $ikonAksi[$aksi] ?? 'bi-dot';
                                @endphp
                                <div class="col-12 col-sm-6">
                                    <label for="permission_{{ $permission->id }}" class="perm-chip act-{{ $aksiKelas }} {{ $aktif ? 'active' : '' }}">
                                        <input type="checkbox" class="d-none" id="permission_{{ $permission->id }}"
                                            wire:click="togglePermission({{ $permission->id }})"
                                            {{ $aktif ? 'checked' : '' }}>
                                        <span class="perm-ico"><i class="bi {{ $ico }}"></i></span>
                                        <span class="perm-label">
                                            {{ $permission->display_name }}
                                            @if ($permission->description)
                                            <i class="bi bi-info-circle text-muted ms-1" data-bs-toggle="tooltip"
                                                title="{{ $permission->description }}"></i>
                                            @endif
                                        </span>
                                        <i class="perm-check bi {{ $aktif ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-5 text-center">
                            <div class="empty-state-icon-wrapper mb-3"><i class="bi bi-search"></i></div>
                            <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Modul Tidak Ditemukan</h5>
                            <p class="text-muted mb-0">Tidak ada modul yang cocok dengan "<span class="fw-semibold">{{ $searchModul }}</span>".</p>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            <!--================== GRAND TOTAL / SIMPAN ==================-->
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="stat-icon-wrapper flex-shrink-0"
                            style="width: 46px; height: 46px; font-size: 1.35rem; border-radius: 14px; background: linear-gradient(135deg, #6c63ff, #4e46e5); color: #fff;">
                            <i class="bi bi-check2-circle"></i>
                        </span>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $dipilih }} dari {{ $totalPermission }} permission dipilih</div>
                            <small class="text-muted">Untuk role <span class="text-capitalize fw-semibold">{{ $role->name }}</span> ({{ $persenTotal }}% total akses)</small>
                        </div>
                    </div>
                    <div class="d-flex mt-2">
                        <button type="submit"
                            class="btn flex-grow-1 d-inline-flex align-items-center justify-content-center text-white rounded-pill shadow-lg"
                            style="height: 55px; background: linear-gradient(135deg, #6c63ff, #4e46e5); font-weight: 600; font-size: 1.1rem; border: none; transition: transform 0.2s;"
                            wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save" class="d-inline-flex align-items-center">
                                <i class="bi bi-check2-circle me-2 fs-4"></i>
                                <span>Simpan Permission</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            @else
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5 text-center">
                    <div class="empty-state-icon-wrapper mb-3"><i class="bi bi-exclamation-triangle"></i></div>
                    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Permission</h5>
                    <p class="text-muted mb-0">Silakan buat permission terlebih dahulu di menu Permission.</p>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initTooltips = () => {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                if (!el._tip) el._tip = new bootstrap.Tooltip(el);
            });
        };
        initTooltips();
        if (window.Livewire) {
            Livewire.hook('morph.updated', initTooltips);
        }
    });
</script>
@endpush