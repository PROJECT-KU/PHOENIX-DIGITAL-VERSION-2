<div>
    <style>
        .of-section {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.95));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }
        .of-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff; flex-shrink: 0;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 14px rgba(78, 70, 229, 0.35);
        }
        .of-icon.green { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 14px rgba(16, 185, 129, 0.35); }
        .of-icon.amber { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 6px 14px rgba(217, 119, 6, 0.35); }
        .of-icon.rose { background: linear-gradient(135deg, #f43f5e, #e11d48); box-shadow: 0 6px 14px rgba(225, 29, 72, 0.35); }
        .of-icon i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; width: 100%; height: 100%; }
        .of-form-label { font-weight: 600; color: #475569; font-size: .85rem; margin-bottom: .35rem; }

        /* ===== Field read-only (otomatis dari akun) ===== */
        .rsc-ro-field {
            display: flex; align-items: center; gap: 9px; height: 42px; padding: 0 13px;
            background: linear-gradient(135deg, #f8f9ff, #f4f6fb);
            border: 1px solid #e9ebf5; border-radius: .7rem;
        }
        .rsc-ro-field .rsc-ro-ico { color: #94a3b8; font-size: 1rem; line-height: 1; flex-shrink: 0; display: inline-flex; }
        .rsc-ro-field .rsc-ro-input { border: 0; outline: 0; background: transparent; width: 100%; color: #475569; font-weight: 500; font-size: .9rem; padding: 0; }
        .rsc-ro-field .rsc-ro-input::placeholder { color: #b6bcc6; }
        .rsc-ro-field .rsc-ro-lock { color: #cbd5e1; font-size: .78rem; line-height: 1; flex-shrink: 0; display: inline-flex; }
        .rsc-ro-field .rsc-ro-input[type="date"]::-webkit-calendar-picker-indicator { opacity: .35; }
        .of-section .form-control, .of-section .form-select { border-radius: .7rem; }

        /* ===== Ikon di dalam input (sejajar teks) ===== */
        .rsc-ico-wrap { position: relative; }
        .rsc-ico-left {
            position: absolute; top: 50%; left: 13px; transform: translateY(-50%);
            color: #94a3b8; font-size: 1rem; line-height: 1; pointer-events: none; z-index: 4; display: inline-flex;
        }
        .rsc-ico-left.top { top: 13px; transform: none; }
        .rsc-has-ico { padding-left: 38px !important; }
        .of-peserta-table thead th { font-size: .8rem; color: #64748b; font-weight: 600; background: transparent; border-bottom: 1.5px solid #eef0f7; }
        .rsc-del-btn {
            width: 36px; height: 36px; border-radius: 10px; padding: 0;
            border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444;
            display: inline-flex; align-items: center; justify-content: center; transition: all .15s ease;
        }
        .rsc-del-btn:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: translateY(-1px); }
        .rsc-del-btn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .of-total-box {
            border-radius: .8rem; padding: 14px 18px; color: #fff; font-weight: 800; font-size: 1.15rem;
            background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 8px 18px rgba(16, 185, 129, .28);
        }

        /* ===== Tabs metode harga ===== */
        .rsc-price-tabs { display: flex; gap: .5rem; }
        .rsc-price-tab {
            flex: 1 1 0; text-align: center; border: 1.5px solid #e6e8f2; background: #fff; border-radius: .8rem;
            padding: 10px 12px; font-weight: 700; color: #64748b; transition: all .15s; cursor: pointer; line-height: 1.25;
        }
        .rsc-price-tab small { font-weight: 500; font-size: .72rem; color: #94a3b8; }
        .rsc-price-tab:hover { border-color: #c7d2fe; }
        .rsc-price-tab.active { border-color: #7c3aed; background: linear-gradient(135deg, rgba(124,58,237,.08), rgba(78,70,229,.04)); color: #6d28d9; }
        .rsc-price-tab.active small { color: #7c3aed; }

        /* ===== Drop zone import Excel ===== */
        .rsc-drop {
            display: block; position: relative; cursor: pointer; text-align: center;
            border: 2px dashed #d9dcea; border-radius: 16px; padding: 26px 18px;
            background: linear-gradient(135deg, #fffdf7, #fff9ee);
            transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .rsc-drop:hover { border-color: #f59e0b; background: linear-gradient(135deg, #fff8e6, #fff3d6); box-shadow: 0 8px 20px rgba(245, 158, 11, .12); }
        .rsc-drop.is-loading { opacity: .7; pointer-events: none; }
        .rsc-drop-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .rsc-drop-ico {
            width: 54px; height: 54px; margin: 0 auto 10px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #fff;
            background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 8px 16px rgba(217, 119, 6, .32);
        }
        .rsc-drop-ico i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
        .rsc-drop-file {
            display: inline-flex; align-items: center; gap: 4px; font-size: .82rem; font-weight: 600;
            color: #059669; background: rgba(16, 185, 129, .12); padding: 4px 12px; border-radius: 999px;
        }
        .rsc-fmt { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .rsc-fmt-title { font-size: .8rem; font-weight: 700; color: #64748b; }
        .rsc-fmt-chip {
            display: inline-flex; align-items: center; gap: 6px; font-size: .78rem; color: #475569;
            background: #f4f6fb; border: 1px solid #e6e8f2; border-radius: 999px; padding: 4px 11px;
        }
        .rsc-fmt-chip b {
            display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px;
            border-radius: 6px; background: #eef0f7; color: #6d28d9; font-size: .72rem;
        }
        .rsc-fmt-note { font-size: .75rem; color: #94a3b8; }
        .rsc-btn-template {
            display: inline-flex; align-items: center; font-size: .84rem; font-weight: 700; color: #b45309;
            background: linear-gradient(135deg, #fff8e6, #ffefc7); border: 1px solid #fcd34d; border-radius: 12px;
            padding: 8px 16px; transition: all .18s ease; white-space: nowrap;
        }
        .rsc-btn-template:hover { color: #92400e; box-shadow: 0 6px 16px rgba(245, 158, 11, .22); transform: translateY(-1px); }
        .rsc-btn-template:disabled { opacity: .65; }
        .rsc-btn-template span { display: inline-flex; align-items: center; line-height: 1; }
        .rsc-btn-template i.bi { display: inline-flex; align-items: center; line-height: 1; }

        /* ===== Popup download template ===== */
        .rsc-tpl { text-align: left; }
        .rsc-tpl-hero {
            display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; margin-bottom: 18px;
        }
        .rsc-tpl-badge {
            width: 68px; height: 68px; border-radius: 20px; display: inline-flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #fff; background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 12px 26px rgba(245, 158, 11, .38); position: relative;
        }
        .rsc-tpl-badge::after {
            content: ""; position: absolute; inset: 3px 3px 55% 3px; border-radius: 17px 17px 40px 40px;
            background: linear-gradient(180deg, rgba(255,255,255,.45), rgba(255,255,255,0)); pointer-events: none;
        }
        .rsc-tpl-badge i.bi {
            position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center;
            width: 100%; height: 100%; line-height: 1;
        }
        .rsc-tpl-hero h3 { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
        .rsc-tpl-hero p { font-size: .86rem; color: #64748b; margin: 0; max-width: 320px; }
        .rsc-tpl-label { font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; margin-bottom: 8px; }
        .rsc-tpl-cols { display: flex; flex-direction: column; gap: 8px; }
        .rsc-tpl-col {
            display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 14px;
            background: linear-gradient(135deg, #ffffff, #f8fafc); border: 1px solid #eef0f7;
            box-shadow: 0 2px 6px rgba(15, 23, 42, .04);
        }
        .rsc-tpl-col-letter {
            width: 30px; height: 30px; flex: 0 0 30px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: .82rem; color: #b45309; background: linear-gradient(135deg, #fff3d6, #ffe4a3); border: 1px solid #fcd34d;
        }
        .rsc-tpl-col-name { font-weight: 700; color: #334155; font-size: .9rem; }
        .rsc-tpl-col-hint { margin-left: auto; font-size: .75rem; color: #94a3b8; }
        .rsc-tpl-note {
            display: flex; align-items: center; gap: 8px; margin-top: 16px; padding: 10px 13px; border-radius: 12px;
            background: rgba(59, 130, 246, .08); border: 1px solid rgba(59, 130, 246, .18); color: #1d4ed8; font-size: .8rem; font-weight: 600;
        }
        .swal-tpl-download {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #fff !important; border: 0 !important;
            font-weight: 700 !important; padding: 11px 26px !important; border-radius: 13px !important;
            box-shadow: 0 10px 22px rgba(245, 158, 11, .35) !important; transition: all .18s ease !important;
        }
        .swal-tpl-download:hover { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(245, 158, 11, .45) !important; }
        .swal-tpl-cancel {
            background: #f1f5f9 !important; color: #475569 !important; border: 0 !important;
            font-weight: 700 !important; padding: 11px 22px !important; border-radius: 13px !important;
        }
        .swal-tpl-cancel:hover { background: #e2e8f0 !important; }

        /* ===== Picker akun (popup select searchable, seperti toko) ===== */
        .of-picker-btn { cursor: pointer; }
        .of-picker-btn::after { content: "\F282"; font-family: "bootstrap-icons"; float: right; color: #94a3b8; font-size: .8rem; }
        .of-pick-list { max-height: 320px; overflow-y: auto; text-align: left; display: flex; flex-direction: column; gap: .4rem; padding: .2rem; }
        .of-pick-item { display: block; width: 100%; text-align: left; border: 1px solid #e6e8f2; background: #fff; border-radius: 12px; padding: .7rem .9rem; font-weight: 600; color: #1e293b; font-size: .92rem; transition: all .15s ease; }
        .of-pick-item:hover { border-color: #6c63ff; background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.04)); transform: translateY(-1px); }
        .of-pick-empty { text-align: center; color: #94a3b8; padding: 1.5rem; font-size: .9rem; }

        /* Mobile: tombol/badge tertentu memenuhi lebar & isi (ikon+teks) di tengah.
           Desktop tetap seperti semula. */
        @media (max-width: 575.98px) {
            .rsc-m-full {
                width: 100% !important;
                display: flex !important;
                align-items: center;
                justify-content: center !important;
                text-align: center;
            }

            /* Tombol hapus akun tambahan: pindah ke pojok kanan atas card, bulat & menarik.
               Beri ruang di atas (padding-top) agar tak menimpa select Akun. */
            .rsc-akun-card { position: relative; padding-top: 46px !important; }
            .rsc-akun-card .rsc-del-btn {
                position: absolute;
                top: 8px;
                right: 8px;
                z-index: 3;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                box-shadow: 0 4px 12px rgba(239, 68, 68, .20);
            }

            /* Box Total: ikon & teks "Rp" sejajar & berada di tengah (agar kanan tak kosong). */
            .of-total-box { display: flex; align-items: center; justify-content: center; text-align: center; }
            .of-total-box i.bi { display: inline-flex; align-items: center; line-height: 1; }

            /* Rincian harga per akun: harga "Rp" tetap utuh & rata kanan, nama menyusut. */
            .rsc-akun-row { gap: 10px; }
            .rsc-akun-row > span:first-child { min-width: 0; }
            .rsc-akun-row > span:last-child { white-space: nowrap; flex-shrink: 0; text-align: right; }
        }
    </style>

    <form wire:submit="save" x-cloak>
        {{-- ================== Import Excel (create & edit) ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                <span class="of-icon amber"><i class="bi bi-file-earmark-spreadsheet-fill"></i></span>
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-0">{{ $mode === 'edit' ? 'Tambah Peserta dari Excel' : 'Import Data Peserta' }}</h5>
                    <small class="text-muted">
                        @if($mode === 'edit')
                            Peserta dari file akan <b>ditambahkan</b> — data peserta yang sudah ada tetap aman
                        @else
                            Opsional — unduh template, isi, lalu unggah kembali
                        @endif
                    </small>
                </div>
                <button type="button" onclick="rscTemplatePopup(this)" class="btn rsc-btn-template rsc-m-full">
                    <span><i class="bi bi-download me-1"></i>Download Template</span>
                </button>
            </div>

            <label class="rsc-drop" wire:loading.class="is-loading" wire:target="file_excel">
                <input type="file" wire:model="file_excel" class="rsc-drop-input" accept=".xlsx,.xls,.csv">
                <span class="rsc-drop-ico"><i class="bi bi-cloud-arrow-up"></i></span>
                <div class="fw-semibold text-dark">{{ $mode === 'edit' ? 'Klik untuk menambah peserta dari file Excel' : 'Klik untuk memilih file Excel' }}</div>
                <div class="text-muted small">Format didukung: .xlsx · .xls · .csv (maks 2 MB)</div>
                <div wire:loading wire:target="file_excel" class="text-warning fw-semibold small mt-2">
                    <span class="spinner-border spinner-border-sm me-1"></span> Memuat &amp; memproses...
                </div>
                @if($file_excel && is_object($file_excel))
                <div class="rsc-drop-file mt-2" wire:loading.remove wire:target="file_excel">
                    <i class="bi bi-file-earmark-check-fill"></i>{{ $file_excel->getClientOriginalName() }}
                </div>
                @endif
            </label>
            @error('file_excel')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

            <div class="rsc-fmt mt-3">
                <span class="rsc-fmt-title">Format kolom:</span>
                <span class="rsc-fmt-chip"><b>A</b> Nama Camp</span>
                <span class="rsc-fmt-chip"><b>B</b> Batch Camp</span>
                <span class="rsc-fmt-chip"><b>C</b> Nama Pembeli</span>
                <span class="rsc-fmt-chip"><b>D</b> No Telp</span>
                <span class="rsc-fmt-note"><i class="bi bi-info-circle me-1"></i>
                    @if($mode === 'edit')
                        Baris 1 = header (diabaikan) · hanya kolom <b>C &amp; D</b> yang dipakai
                    @else
                        Baris 1 = header (diabaikan)
                    @endif
                </span>
            </div>
        </div>

        {{-- ================== Data Kategori ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="of-icon"><i class="bi bi-folder2-open"></i></span>
                <h5 class="fw-bold mb-0">Data Kategori</h5>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nama_camp" class="of-form-label d-block">Nama Kategori <span class="text-danger">*</span></label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left"><i class="bi bi-tag"></i></span>
                        <input type="text" wire:model="nama_camp" id="nama_camp"
                            class="form-control rsc-has-ico @error('nama_camp') is-invalid @enderror" placeholder="contoh: Scopus Camp Yogyakarta">
                    </div>
                    @error('nama_camp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="batch_camp" class="of-form-label d-block">Batch <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary fw-semibold"
                            style="pointer-events:none; z-index:5;">#</span>
                        <input type="number" wire:model="batch_camp" id="batch_camp" style="padding-left: 30px;"
                            class="form-control @error('batch_camp') is-invalid @enderror" placeholder="contoh: 3">
                    </div>
                    @error('batch_camp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="tanggal_mulai_camp" class="of-form-label d-block">Tanggal Mulai <span class="text-danger">*</span></label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" wire:model="tanggal_mulai_camp" id="tanggal_mulai_camp"
                            class="form-control rsc-has-ico @error('tanggal_mulai_camp') is-invalid @enderror">
                    </div>
                    @error('tanggal_mulai_camp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="tanggal_akhir_camp" class="of-form-label d-block">Tanggal Berakhir <span class="text-danger">*</span></label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left"><i class="bi bi-calendar-check"></i></span>
                        <input type="date" wire:model="tanggal_akhir_camp" id="tanggal_akhir_camp"
                            class="form-control rsc-has-ico @error('tanggal_akhir_camp') is-invalid @enderror">
                    </div>
                    @error('tanggal_akhir_camp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- ================== Data Akun ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="of-icon green"><i class="bi bi-person-badge-fill"></i></span>
                <div>
                    <h5 class="fw-bold mb-0">Data Akun Utama</h5>
                    <small class="text-muted">Harga satuan &amp; perhitungan diambil dari akun ini</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="of-form-label d-block">Pilih Akun <span class="text-danger">*</span></label>
                    @php $selAkun = $akuns->firstWhere('id', (int) $akun); @endphp
                    <button type="button" onclick="rscAkunPicker(this)"
                        class="form-select text-start of-picker-btn @error('akun') is-invalid @enderror">
                        @if($selAkun)
                        <span class="text-dark">{{ $selAkun->nama_akun }}</span>
                        @else
                        <span class="text-muted">-- Pilih Akun --</span>
                        @endif
                    </button>
                    @error('akun')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="of-form-label d-block">Username</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-person rsc-ro-ico"></i>
                        <input type="text" wire:model="username" class="rsc-ro-input" placeholder="—" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Otomatis dari akun"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="of-form-label d-block">Password</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-key rsc-ro-ico"></i>
                        <input type="text" wire:model="password" class="rsc-ro-input" placeholder="—" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Otomatis dari akun"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="of-form-label d-block">Link Akses</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-link-45deg rsc-ro-ico"></i>
                        <input type="text" wire:model="link_akses" class="rsc-ro-input" placeholder="—" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Otomatis dari akun"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="harga_satuan" class="of-form-label d-block">Harga Satuan</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-cash-coin rsc-ro-ico"></i>
                        <input type="text" wire:model="harga_satuan" x-nominal x-currency id="harga_satuan"
                            class="rsc-ro-input" placeholder="—" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Otomatis dari akun"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== Akun Tambahan (kredensial saja) ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <span class="of-icon"><i class="bi bi-collection-fill"></i></span>
                    <div>
                        <h5 class="fw-bold mb-0">Akun Tambahan</h5>
                        <small class="text-muted">Kredensial saja (mis. Grammarly, DeepL) — tidak memengaruhi harga</small>
                    </div>
                </div>
                <button type="button" wire:click="addAkunTambahan"
                    class="btn btn-primary btn-sm rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-1 rsc-m-full">
                    <i class="bi bi-plus-circle"></i> Tambah Akun
                </button>
            </div>

            @forelse($akunTambahan as $tmpId => $a)
            <div class="border rounded-3 p-3 mb-2 rsc-akun-card" wire:key="akt-{{ $tmpId }}" style="background:#fff;">
                <div class="row g-3 align-items-start">
                    <div class="col-md-3">
                        <label class="of-form-label d-block">Akun</label>
                        <button type="button" onclick="rscAkunTambahanPicker(this, '{{ $tmpId }}')"
                            class="form-select text-start of-picker-btn">
                            @if($a['akun_id'])<span class="text-dark">{{ $a['nama_akun'] }}</span>
                            @else<span class="text-muted">-- Pilih Akun --</span>@endif
                        </button>
                        @if($metode_harga==='per_akun' && $a['akun_id'])
                        <span class="badge bg-success-subtle text-success border border-success rounded-pill mt-1" style="font-size:.68rem;">
                            <i class="bi bi-tag me-1"></i>Rp {{ number_format($a['harga'] ?? 0, 0, ',', '.') }}
                        </span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="of-form-label d-block">Username</label>
                        <div class="rsc-ro-field">
                            <i class="bi bi-person rsc-ro-ico"></i>
                            <input type="text" class="rsc-ro-input" value="{{ $a['username'] }}" placeholder="—" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="of-form-label d-block">Password</label>
                        <div class="rsc-ro-field">
                            <i class="bi bi-key rsc-ro-ico"></i>
                            <input type="text" class="rsc-ro-input" value="{{ $a['password'] }}" placeholder="—" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="of-form-label d-block">Link Akses</label>
                        <div class="d-flex gap-2">
                            <div class="rsc-ro-field flex-grow-1">
                                <i class="bi bi-link-45deg rsc-ro-ico"></i>
                                <input type="text" class="rsc-ro-input" value="{{ $a['link_akses'] }}" placeholder="—" readonly>
                            </div>
                            <button type="button" class="rsc-del-btn flex-shrink-0" title="Hapus akun"
                                wire:click="removeAkunTambahan('{{ $tmpId }}')"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-muted small mb-0">
                <i class="bi bi-info-circle me-1"></i>Belum ada akun tambahan. Klik <b>Tambah Akun</b> bila batch ini memakai lebih dari satu akun.
            </p>
            @endforelse
        </div>

        {{-- ================== Data Pembeli ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <span class="of-icon rose"><i class="bi bi-person-vcard-fill"></i></span>
                    <h5 class="fw-bold mb-0">Data Pembeli</h5>
                </div>
                <button type="button" wire:click="addPeserta"
                    class="btn btn-primary btn-sm rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-1 rsc-m-full">
                    <i class="bi bi-plus-circle"></i> Tambah Peserta
                </button>
            </div>

            <div class="table-responsive" wire:loading.class="opacity-50" wire:target="removePeserta">
                <table class="table align-middle of-peserta-table mb-0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="45%">Nama Pembeli <span class="text-danger">*</span></th>
                            <th width="40%">No. Telepon <span class="text-danger">*</span></th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($peserta as $tmpId => $p)
                        <tr wire:key="row-{{ $tmpId }}">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="rsc-ico-wrap">
                                    <span class="rsc-ico-left"><i class="bi bi-person"></i></span>
                                    <input type="text" wire:model.defer="peserta.{{ $tmpId }}.nama_pembeli"
                                        class="form-control rsc-has-ico @error('peserta.'.$tmpId.'.nama_pembeli') is-invalid @enderror"
                                        placeholder="Nama Peserta">
                                </div>
                                @error('peserta.'.$tmpId.'.nama_pembeli')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </td>
                            <td>
                                <div class="rsc-ico-wrap">
                                    <span class="rsc-ico-left"><i class="bi bi-telephone"></i></span>
                                    <input type="text" wire:model.defer="peserta.{{ $tmpId }}.telp_pembeli"
                                        class="form-control rsc-has-ico @error('peserta.'.$tmpId.'.telp_pembeli') is-invalid @enderror"
                                        placeholder="0812..." onkeypress="filterPhoneNumberInput(event)">
                                </div>
                            </td>
                            <td class="text-center">
                                @if(count($peserta) > 1)
                                <button type="button" title="Hapus peserta" class="rsc-del-btn"
                                    x-on:click="let y = window.scrollY; $wire.removePeserta('{{ $tmpId }}').then(() => requestAnimationFrame(() => window.scrollTo(0, y)))">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <span class="badge bg-primary-subtle text-primary border border-primary rounded-pill px-3 py-2 rsc-m-full">
                    <i class="bi bi-people-fill me-1"></i> Total Peserta: {{ count($peserta) }} orang
                </span>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label for="jumlah_pemesanan" class="of-form-label d-block">Jumlah Pesanan <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="number" id="jumlah_pemesanan" wire:model.live="jumlah_pemesanan" style="padding-right: 58px;"
                            class="form-control @error('jumlah_pemesanan') is-invalid @enderror" placeholder="Durasi">
                        <span class="position-absolute top-50 end-0 translate-middle-y pe-3 text-secondary fw-semibold"
                            style="pointer-events:none; z-index:5;">Bulan</span>
                    </div>
                    @error('jumlah_pemesanan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="tanggal_pemesanan" class="of-form-label d-block">Tanggal Pemesanan <span class="text-danger">*</span></label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left"><i class="bi bi-calendar-date"></i></span>
                        <input type="date" id="tanggal_pemesanan" wire:model.live="tanggal_pemesanan"
                            class="form-control rsc-has-ico @error('tanggal_pemesanan') is-invalid @enderror">
                    </div>
                    @error('tanggal_pemesanan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="tanggal_berakhir" class="of-form-label d-block">Tanggal Berakhir</label>
                    <div class="rsc-ro-field">
                        <i class="bi bi-calendar-check rsc-ro-ico"></i>
                        <input type="date" id="tanggal_berakhir" wire:model="tanggal_berakhir" class="rsc-ro-input" readonly>
                        <i class="bi bi-lock-fill rsc-ro-lock" title="Dihitung otomatis"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== Data Lainnya ================== --}}
        <div class="of-section p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="of-icon amber"><i class="bi bi-sliders"></i></span>
                <h5 class="fw-bold mb-0">Data Lainnya</h5>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="of-form-label d-block">Metode Perhitungan Harga</label>
                    <div class="rsc-price-tabs mb-2">
                        <button type="button" wire:click="$set('metode_harga','per_peserta')"
                            class="rsc-price-tab @if($metode_harga==='per_peserta') active @endif">
                            <i class="bi bi-people-fill me-1"></i> Per Peserta
                            <small class="d-block">harga × jumlah peserta</small>
                        </button>
                        <button type="button" wire:click="$set('metode_harga','per_akun')"
                            class="rsc-price-tab @if($metode_harga==='per_akun') active @endif">
                            <i class="bi bi-collection-fill me-1"></i> Per Akun
                            <small class="d-block">harga × jumlah akun</small>
                        </button>
                    </div>

                    @if($metode_harga==='per_akun')
                    {{-- Rincian harga tiap akun (utama + tambahan) --}}
                    <div class="border rounded-3 p-3 mb-2" style="background:#fff;">
                        <div class="fw-semibold small text-dark mb-2"><i class="bi bi-list-ul me-1"></i>Rincian harga per akun</div>
                        @php $selUtama = $akuns->firstWhere('id', (int) $akun); @endphp
                        @if($akun)
                        <div class="rsc-akun-row d-flex justify-content-between align-items-center small py-1 border-bottom">
                            <span><i class="bi bi-star-fill text-warning me-1"></i>{{ $selUtama->nama_akun ?? 'Akun Utama' }}
                                <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill ms-1" style="font-size:.6rem;">UTAMA</span></span>
                            <span class="fw-semibold">Rp {{ number_format($this->hargaUtama(), 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @foreach($akunTambahan as $a)
                        @if(!empty($a['akun_id']))
                        <div class="rsc-akun-row d-flex justify-content-between align-items-center small py-1 border-bottom">
                            <span><i class="bi bi-collection me-1 text-primary"></i>{{ $a['nama_akun'] }}</span>
                            <span class="fw-semibold">Rp {{ number_format($a['harga'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @endforeach
                        <div class="rsc-akun-row d-flex justify-content-between align-items-center pt-2 fw-bold text-success">
                            <span>Jumlah harga {{ $this->jumlahAkun() }} akun</span>
                            <span>Rp {{ number_format($this->sumHargaAkun(), 0, ',', '.') }}</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.75rem;">× {{ (int)($jumlah_pemesanan ?: 0) }} bulan</div>
                    </div>
                    @else
                    <div class="text-muted small mb-2">
                        <i class="bi bi-calculator me-1"></i>{{ (int)($jumlah_pemesanan ?: 0) }} bulan × {{ $harga_satuan ?: 'Rp 0' }} × {{ count($peserta) }} peserta
                    </div>
                    @endif

                    <div class="of-total-box"><i class="bi bi-cash-stack me-2"></i>Rp {{ number_format($this->grand_total, 0, ',', '.') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="of-form-label d-block">Pilih PIC <span class="text-danger">*</span></label>
                    @php $selPic = $users->firstWhere('id', (int) $pic); @endphp
                    <button type="button" onclick="rscPicPicker(this)"
                        class="form-select text-start of-picker-btn @error('pic') is-invalid @enderror">
                        @if($selPic)
                        <span class="text-dark">{{ $selPic->name }}</span>
                        @else
                        <span class="text-muted">-- Pilih PIC --</span>
                        @endif
                    </button>
                    @error('pic')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="of-form-label d-block">Pilih Status <span class="text-danger">*</span></label>
                    <select wire:model="status" id="status" class="form-select">
                        <option value="">-- Pilih Status --</option>
                        <option value="habis">Habis</option>
                        <option value="pengganti">Pengganti</option>
                        <option value="perpanjang">Perpanjang</option>
                        <option value="baru">Baru</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="deskripsi" class="of-form-label d-block">Deskripsi</label>
                    <div class="rsc-ico-wrap">
                        <span class="rsc-ico-left top"><i class="bi bi-card-text"></i></span>
                        <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                            class="form-control rsc-has-ico @error('deskripsi') is-invalid @enderror" placeholder="Masukkan deskripsi (opsional)"></textarea>
                    </div>
                    @error('deskripsi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span>{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
            </button>
        </div>
    </form>
</div>

<!--================== FORMAT TELP ==================-->
<script>
    function formatPhoneNumber(input) {
        if (input.value.startsWith('0')) {
            input.value = '+62' + input.value.substring(1);
        }
    }

    function validatePhoneNumber(input) {
        const regex = /^\+62[0-9]{8,13}$/;
        const errorDiv = document.getElementById('telp_error');
        if (!regex.test(input.value)) {
            input.classList.add('is-invalid');
            if (errorDiv) errorDiv.textContent = 'Nomor telepon harus diawali +62 dan berisi 9–14 digit angka.';
        } else {
            input.classList.remove('is-invalid');
            if (errorDiv) errorDiv.textContent = '';
        }
    }

    // Batasi input agar hanya angka + simbol "+" (hanya di awal)
    function filterPhoneNumberInput(event) {
        const char = String.fromCharCode(event.which);
        if ([8, 37, 39, 46].includes(event.keyCode)) {
            return;
        }
        if (event.target.value.length === 0 && char === '+') {
            return;
        }
        if (!/[0-9]/.test(char)) {
            event.preventDefault();
        }
    }
</script>
<!--================== END ==================-->

<!--================== PICKER AKUN & PIC (popup searchable) ==================-->
<script>
    {{-- Hanya akun status active yang boleh dipilih (utama & tambahan). --}}
    window.__rscAkuns = {!! json_encode($akunsAktif->map(fn ($a) => ['id' => (string) $a->id, 'name' => $a->nama_akun])->values()) !!};
    window.__rscUsers = {!! json_encode($users->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])->values()) !!};

    if (!window.__rscPickerBound) {
        window.__rscPickerBound = true;

        const rscGlossy = {
            background: 'rgba(255, 255, 255, 0.92)',
            backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
            buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem',
        };

        // Picker generik (dipakai akun & PIC)
        window.__rscPicker = function (title, placeholder, items, emptyText, onPick) {
            if (typeof Swal === 'undefined') return;
            const rows = items.length
                ? items.map(it => `<button type="button" class="of-pick-item" data-id="${it.id}" data-search="${it.name.toLowerCase()}">${it.name}</button>`).join('')
                : `<div class="of-pick-empty">${emptyText}</div>`;

            Swal.fire({
                title: title,
                html: `<input id="rscPickSearch" class="form-control mb-2" placeholder="${placeholder}">
                       <div id="rscPickList" class="of-pick-list">${rows}</div>`,
                ...rscGlossy,
                didOpen: () => {
                    const search = document.getElementById('rscPickSearch');
                    const listEl = document.getElementById('rscPickList');
                    if (search) {
                        search.addEventListener('input', () => {
                            const q = search.value.toLowerCase();
                            listEl.querySelectorAll('.of-pick-item').forEach(b => { b.style.display = b.dataset.search.includes(q) ? '' : 'none'; });
                        });
                        setTimeout(() => search.focus(), 100);
                    }
                    listEl.querySelectorAll('.of-pick-item').forEach(b => {
                        b.addEventListener('click', () => { onPick(b.dataset.id); Swal.close(); });
                    });
                }
            });
        };

        window.rscAkunPicker = function (btn) {
            const comp = btn.closest('[wire\\:id]'); if (!comp) return;
            const cid = comp.getAttribute('wire:id');
            window.__rscPicker('Pilih Akun', 'Ketik untuk mencari akun...', window.__rscAkuns || [], 'Tidak ada akun',
                (id) => Livewire.find(cid).set('akun', id));
        };

        window.rscPicPicker = function (btn) {
            const comp = btn.closest('[wire\\:id]'); if (!comp) return;
            const cid = comp.getAttribute('wire:id');
            window.__rscPicker('Pilih PIC', 'Ketik untuk mencari PIC...', window.__rscUsers || [], 'Tidak ada data PIC',
                (id) => Livewire.find(cid).set('pic', id));
        };

        // Picker untuk baris akun tambahan (kredensial saja)
        window.rscAkunTambahanPicker = function (btn, tmpId) {
            const comp = btn.closest('[wire\\:id]'); if (!comp) return;
            const cid = comp.getAttribute('wire:id');
            window.__rscPicker('Pilih Akun', 'Ketik untuk mencari akun...', window.__rscAkuns || [], 'Tidak ada akun',
                (id) => Livewire.find(cid).call('setAkunTambahan', tmpId, id));
        };

        // Popup glossy untuk download template Excel
        window.rscTemplatePopup = function (btn) {
            if (typeof Swal === 'undefined') return;
            const comp = btn.closest('[wire\\:id]'); if (!comp) return;
            const cid = comp.getAttribute('wire:id');

            const cols = [
                { l: 'A', name: 'Nama Camp', hint: 'wajib' },
                { l: 'B', name: 'Batch Camp', hint: 'angka' },
                { l: 'C', name: 'Nama Pembeli', hint: 'wajib' },
                { l: 'D', name: 'No Telp', hint: 'wajib' },
            ];
            const colsHtml = cols.map(c => `
                <div class="rsc-tpl-col">
                    <span class="rsc-tpl-col-letter">${c.l}</span>
                    <span class="rsc-tpl-col-name">${c.name}</span>
                    <span class="rsc-tpl-col-hint">${c.hint}</span>
                </div>`).join('');

            Swal.fire({
                html: `
                    <div class="rsc-tpl">
                        <div class="rsc-tpl-hero">
                            <span class="rsc-tpl-badge"><i class="bi bi-file-earmark-arrow-down"></i></span>
                            <h3>Download Template Excel</h3>
                            <p>Unduh template, isi data peserta, lalu unggah kembali pada kotak import di bawah.</p>
                        </div>
                        <div class="rsc-tpl-label">Struktur Kolom</div>
                        <div class="rsc-tpl-cols">${colsHtml}</div>
                        <div class="rsc-tpl-note">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>Baris pertama adalah header — jangan dihapus atau diubah.</span>
                        </div>
                    </div>`,
                background: 'rgba(255, 255, 255, 0.94)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                width: 460,
                padding: '1.6rem 1.4rem 1.4rem',
                buttonsStyling: false,
                showCancelButton: true,
                showCloseButton: true,
                reverseButtons: true,
                confirmButtonText: '<i class="bi bi-download me-1"></i> Download Sekarang',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0',
                    confirmButton: 'swal-tpl-download',
                    cancelButton: 'swal-tpl-cancel',
                    actions: 'gap-2 mt-3',
                },
            }).then((res) => {
                if (res.isConfirmed) {
                    Livewire.find(cid).call('downloadTemplate');
                    if (typeof window.fireGlossySwal === 'function') {
                        window.fireGlossySwal('Sedang Diunduh', 'Template Excel sedang disiapkan…', 'success');
                    }
                }
            });
        };
    }
</script>
<!--================== END PICKER AKUN & PIC ==================-->
