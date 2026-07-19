<div>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .perm-input {
            border-radius: 12px;
            border: 1.5px solid #e7e9f2;
            padding: 11px 14px;
            transition: 0.18s;
        }

        .perm-input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.18rem rgba(124, 58, 237, 0.12);
        }

        .perm-input[readonly] {
            background: #f8fafc;
            color: #475569;
        }

        .perm-hint {
            font-size: 0.78rem;
            color: #94a3b8;
        }

        .quick-card {
            background: linear-gradient(135deg, #faf5ff, #eff6ff);
            border: 1px solid #ede9fe;
            border-radius: 16px;
        }

        .tips-card {
            background: #f8fafc;
            border: 1px dashed #e2e8f0;
            border-radius: 16px;
        }

        .tips-card code {
            background: #eef2ff;
            color: #4f46e5;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 0.78rem;
        }

        /* Select2 disamakan dengan tema glossy */
        .select2-container--default .select2-selection--single {
            height: 48px;
            border: 1.5px solid #e7e9f2;
            border-radius: 12px;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.4;
            padding-left: 6px;
            color: #334155;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.18rem rgba(124, 58, 237, 0.12);
        }

        .select2-dropdown {
            border: 1.5px solid #e7e9f2;
            border-radius: 12px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: #7c3aed;
        }
    </style>

    <form wire:submit.prevent="save">
        @unless ($isEdit)
        <!--================== MODE CEPAT (IKUT SIDEBAR) ==================-->
        <div class="quick-card p-4 mb-4">
            <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-magic" style="color:#7c3aed;"></i> Mode Cepat — Ikuti Sidebar
            </h6>
            <p class="perm-hint mb-3">Pilih modul & jenis aksi, lalu Key, Group, dan Nama Tampilan terisi otomatis.</p>

            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label fw-semibold text-dark">Modul (sesuai sidebar)</label>
                    <div wire:ignore x-data x-init="
                        let $select = $($el).find('select');
                        $select.select2({
                            placeholder: '-- Pilih Modul --',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $($el)
                        });
                        $select.on('change', function () {
                            $wire.set('selectedModule', $(this).val());
                        });
                        $select.on('select2:open', function () {
                            $($el).closest('.card').css('z-index', 1060);
                        });
                        $select.on('select2:close', function () {
                            $($el).closest('.card').css('z-index', '');
                        });
                    ">
                        <select class="form-select">
                            <option value="">-- Pilih Modul --</option>
                            @foreach ($sidebarModules as $key => $label)
                            <option value="{{ $key }}" @selected($selectedModule===$key)>{{ $label }} ({{ $key }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-semibold text-dark">Jenis Aksi</label>
                    <select wire:model.live="selectedAction" class="form-select perm-input">
                        <option value="">-- Pilih Aksi --</option>
                        @foreach ($aksiOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        @endunless

        <div class="row g-3">
            <!-- Name (key) -->
            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold text-dark">
                    Nama Permission (Key) <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control perm-input @error('name') is-invalid @enderror"
                    id="name" wire:model="name" placeholder="otomatis dari pilihan di atas"
                    {{ $isEdit ? 'readonly' : '' }}>
                <small class="perm-hint d-block mt-1">
                    <i class="bi bi-info-circle"></i>
                    @if ($isEdit)
                    Key tidak dapat diubah saat edit.
                    @else
                    Terisi otomatis (format <span class="fw-semibold">action_module</span>), masih bisa diubah manual.
                    @endif
                </small>
                @error('name')
                <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <!-- Display Name -->
            <div class="col-md-6">
                <label for="display_name" class="form-label fw-semibold text-dark">
                    Nama Tampilan <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control perm-input @error('display_name') is-invalid @enderror"
                    id="display_name" wire:model="display_name" placeholder="contoh: Lihat Pemesanan Toko">
                <small class="perm-hint d-block mt-1">
                    <i class="bi bi-info-circle"></i>
                    Otomatis terisi, tetap bisa diubah manual
                </small>
                @error('display_name')
                <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <!-- Group -->
            <div class="col-md-6">
                <label for="group" class="form-label fw-semibold text-dark">Group/Kategori</label>
                <input type="text" class="form-control perm-input @error('group') is-invalid @enderror"
                    id="group" wire:model="group" placeholder="contoh: pemesanantoko, users, roles">
                <small class="perm-hint d-block mt-1">
                    <i class="bi bi-info-circle"></i>
                    @if ($isEdit)
                    Untuk mengelompokkan permission per modul/fitur
                    @else
                    Terisi otomatis mengikuti modul, bisa diubah manual
                    @endif
                </small>
                @error('group')
                <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            <!-- Description -->
            <div class="col-md-6">
                <label for="description" class="form-label fw-semibold text-dark">Deskripsi</label>
                <textarea class="form-control perm-input @error('description') is-invalid @enderror"
                    id="description" wire:model="description" rows="3"
                    placeholder="Deskripsi singkat tentang permission ini"></textarea>
                <small class="perm-hint d-block mt-1">
                    <i class="bi bi-info-circle"></i>
                    Penjelasan fungsi permission untuk memudahkan admin
                </small>
                @error('description')
                <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Submit -->
        <div class="d-flex mt-4">
            <button type="submit"
                class="btn flex-grow-1 d-inline-flex align-items-center justify-content-center text-white rounded-pill shadow-lg"
                style="height: 55px; background: linear-gradient(135deg, #6c63ff, #4e46e5); font-weight: 600; font-size: 1.1rem; border: none;"
                wire:loading.attr="disabled">
                <span wire:loading.remove class="d-inline-flex align-items-center">
                    <i class="bi bi-check2-circle me-2 fs-4"></i>
                    <span>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data' }}</span>
                </span>
            </button>
        </div>
    </form>

    <!-- Tips Penamaan -->
    <div class="tips-card p-4 mt-4">
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-lightbulb-fill text-warning"></i> Tips Penamaan Permission
        </h6>
        <div class="table-responsive">
            <table class="table table-sm table-borderless mb-0 align-middle">
                <thead>
                    <tr class="text-muted" style="font-size: 0.75rem;">
                        <th class="text-uppercase">Format</th>
                        <th class="text-uppercase">Contoh</th>
                        <th class="text-uppercase">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <tr>
                        <td><code>view_[module]</code></td>
                        <td><code>view_pemesanantoko</code></td>
                        <td class="text-muted">Melihat/mengakses halaman</td>
                    </tr>
                    <tr>
                        <td><code>view_all_[module]</code></td>
                        <td><code>view_all_gajikaryawan</code></td>
                        <td class="text-muted">Lihat SEMUA data (admin/finance)</td>
                    </tr>
                    <tr>
                        <td><code>create_[module]</code></td>
                        <td><code>create_users</code></td>
                        <td class="text-muted">Membuat data baru</td>
                    </tr>
                    <tr>
                        <td><code>edit_[module]</code></td>
                        <td><code>edit_pemesanantoko</code></td>
                        <td class="text-muted">Mengedit data</td>
                    </tr>
                    <tr>
                        <td><code>delete_[module]</code></td>
                        <td><code>delete_users</code></td>
                        <td class="text-muted">Menghapus data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>