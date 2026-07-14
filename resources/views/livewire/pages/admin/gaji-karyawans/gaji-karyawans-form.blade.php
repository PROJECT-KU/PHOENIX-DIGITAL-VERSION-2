<div>
    <style>
        .stat-icon-wrapper {
            line-height: 1 !important;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .stat-icon-wrapper i::before {
            display: block;
            line-height: 1;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.9);
        }

        /* Suffix unit di dalam input (mis. "jam") */
        .input-suffix-wrap {
            position: relative;
        }

        .input-suffix-wrap .input-suffix {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.85rem;
            pointer-events: none;
        }

        /* Hilangkan tombol panah bawaan input number agar rapi */
        .no-spinner::-webkit-outer-spin-button,
        .no-spinner::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .no-spinner {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        /* Prefix "Rp" di dalam input (gaya seperti peminjaman) */
        .rp-wrap {
            position: relative;
        }

        .rp-wrap .form-control {
            padding-left: 45px;
        }

        /* Field readonly: lembut, bersih, menarik */
        .readonly-pretty {
            background-color: #f6f5ff !important;
            border-color: #e4e0ff !important;
            color: #4f46e5 !important;
            font-weight: 600;
        }

        .readonly-pretty:focus {
            box-shadow: none;
            border-color: #c7d2fe !important;
        }

        .readonly-total {
            background-color: #ecfdf5 !important;
            border-color: #bbf7d0 !important;
            color: #059669 !important;
            font-weight: 700;
        }

        .readonly-total:focus {
            box-shadow: none;
        }

        /* ===== Panel Penyelesaian Task ===== */
        .task-panel {
            border: 1px solid #eef0f7;
            border-radius: 16px;
            padding: 18px;
            background: linear-gradient(135deg, rgba(124, 58, 237, .04), rgba(37, 99, 235, .03));
        }

        .task-ico {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7c3aed, #4e46e5);
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .task-ico i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }

        .task-table th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .task-total {
            background: #fff;
            border: 1px solid #eef0f7;
            border-radius: 999px;
            padding: 8px 18px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, .08);
        }
    </style>

    <form wire:submit.prevent="save">

        <!--================== DATA KARYAWAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                        <i class="bi bi-person-vcard"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Data Karyawan</h5>
                </div>

                <div class="row">
                    <!-- Nama Karyawan -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Nama Karyawan <span class="text-danger">*</span></label>
                        @if(isset($users) && $users->count())
                        @php $gajiSelUser = $users->firstWhere('id', $nama_karyawan); @endphp
                        <button type="button" onclick="gajiKaryawanPicker(this)" id="nama_karyawan"
                            class="form-select text-start gaji-picker-btn @error('nama_karyawan') is-invalid @enderror">
                            @if ($gajiSelUser)
                                <span class="text-dark d-inline-flex align-items-center gap-1"><i class="bi bi-person-fill" style="color:#7c3aed; line-height:1;"></i>{{ $gajiSelUser->name }}</span>
                            @else
                                <span class="text-muted">-- Pilih Nama Karyawan --</span>
                            @endif
                        </button>
                        @else
                        <select class="form-select" disabled>
                            <option>Tidak ada karyawan</option>
                        </select>
                        @endif
                        @error('nama_karyawan')
                        <div class="text-danger small mt-1 d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="bank" class="form-label">Nama Bank</label>
                        <input type="text" wire:model="bank"
                            class="form-control readonly-pretty @error('bank') is-invalid @enderror" id="bank" readonly>
                        @error('bank')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="no_rek" class="form-label">No Rekening</label>
                        <input type="text" wire:model="no_rek"
                            class="form-control readonly-pretty @error('no_rek') is-invalid @enderror" id="no_rek" readonly>
                        @error('no_rek')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Transaksi (tanggal pembayaran) -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_transaksi" class="form-label">
                            Tanggal Pembayaran <span class="text-danger">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_transaksi"
                            class="form-control @error('tanggal_transaksi') is-invalid @enderror" id="tanggal_transaksi">
                        @error('tanggal_transaksi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Periode Gaji -->
                    <div class="col-md-3 mb-3">
                        <label for="periode_bulan" class="form-label">
                            Periode Bulan <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="periode_bulan" id="periode_bulan"
                            class="form-select @error('periode_bulan') is-invalid @enderror">
                            <option value="">-- Bulan --</option>
                            @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('periode_bulan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="periode_tahun" class="form-label">
                            Periode Tahun <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="periode_tahun" id="periode_tahun"
                            class="form-select @error('periode_tahun') is-invalid @enderror">
                            <option value="">-- Tahun --</option>
                            @foreach ($daftarTahun as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                            @endforeach
                        </select>
                        @error('periode_tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!--================== DATA GAJI ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-green flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                        <i class="bi bi-cash-coin"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Data Gaji & Tunjangan</h5>
                </div>

                <div class="row">
                    <!-- Gaji Pokok -->
                    <div class="col-md-12 mb-3">
                        <label for="gaji_pokok" class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="gaji_pokok"
                                class="form-control @error('gaji_pokok') is-invalid @enderror rupiah" id="gaji_pokok" placeholder="0">
                        </div>
                        @error('gaji_pokok')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="bonus_kinerja" class="form-label">Bonus Kinerja</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="bonus_kinerja"
                                class="form-control @error('bonus_kinerja') is-invalid @enderror rupiah" id="bonus_kinerja" placeholder="0">
                        </div>
                        @error('bonus_kinerja')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="bonus_lainnya" class="form-label">Bonus Lainnya</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="bonus_lainnya"
                                class="form-control @error('bonus_lainnya') is-invalid @enderror rupiah" id="bonus_lainnya" placeholder="0">
                        </div>
                        @error('bonus_lainnya')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ================== BONUS PENYELESAIAN TASK (read-only) ================== --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-inline-flex align-items-center gap-1">
                            <span>Bonus Penyelesaian Task</span>
                            <i class="bi bi-info-circle text-muted" style="line-height:1;" title="Diatur di halaman Penyelesaian Task (pool bersama)"></i>
                        </label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">Rp</span>
                            <input type="text" id="bonus_penyelesaian_task_disp" readonly
                                class="form-control readonly-pretty"
                                value="{{ number_format((int) $bonus_penyelesaian_task, 0, ',', '.') }}">
                        </div>
                        <small class="text-muted"><i class="bi bi-list-check me-1"></i>Diatur di halaman <b>Penyelesaian Task</b>.</small>
                        {{-- Hidden field agar total (JS) ikut menghitung bonus task --}}
                        <input type="hidden" id="bonus_penyelesaian_task" value="{{ (int) $bonus_penyelesaian_task }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_kesehatan" class="form-label">Tunjangan Kesehatan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_kesehatan"
                                class="form-control @error('tunjangan_kesehatan') is-invalid @enderror rupiah" id="tunjangan_kesehatan" placeholder="0">
                        </div>
                        @error('tunjangan_kesehatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_thr" class="form-label">Tunjangan THR</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_thr"
                                class="form-control @error('tunjangan_thr') is-invalid @enderror rupiah" id="tunjangan_thr" placeholder="0">
                        </div>
                        @error('tunjangan_thr')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_ketenagakerjaan" class="form-label">Tunjangan Ketenagakerjaan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_ketenagakerjaan"
                                class="form-control @error('tunjangan_ketenagakerjaan') is-invalid @enderror rupiah" id="tunjangan_ketenagakerjaan" placeholder="0">
                        </div>
                        @error('tunjangan_ketenagakerjaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_lainnya" class="form-label">Tunjangan Lainnya</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_lainnya"
                                class="form-control @error('tunjangan_lainnya') is-invalid @enderror rupiah" id="tunjangan_lainnya" placeholder="0">
                        </div>
                        @error('tunjangan_lainnya')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_transport" class="form-label">Tunjangan Transport</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_transport"
                                class="form-control @error('tunjangan_transport') is-invalid @enderror rupiah" id="tunjangan_transport" placeholder="0">
                        </div>
                        @error('tunjangan_transport')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="tunjangan_makan" class="form-label">Tunjangan Makan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_makan"
                                class="form-control @error('tunjangan_makan') is-invalid @enderror rupiah" id="tunjangan_makan" placeholder="0">
                        </div>
                        @error('tunjangan_makan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!--================== LEMBUR & KEHADIRAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px; background: linear-gradient(135deg,#0ea5e9,#2563eb); color:#fff;">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Lembur &amp; Kehadiran</h5>
                </div>

                <div class="row">
                    <!-- Lembur: jam (auto dari presensi) x tarif/jam = total otomatis -->
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-2 mb-2 mt-1">
                            <i class="bi bi-alarm text-primary d-inline-flex align-items-center" style="line-height: 1;"></i>
                            <span class="fw-semibold d-inline-flex align-items-center">Lembur (dari data presensi)</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary fw-normal d-inline-flex align-items-center">otomatis per periode</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="jam_lembur" class="form-label">Jam Lembur</label>
                        <div class="input-suffix-wrap">
                            <input type="text" id="jam_lembur" class="form-control no-spinner pe-5 readonly-pretty"
                                value="{{ $jam_lembur }}" placeholder="0" readonly>
                            <span class="input-suffix">jam</span>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Otomatis dari presensi lembur (yang sudah absen pulang).</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="tarif_lembur" class="form-label">
                            Tarif / Jam
                            <i class="bi bi-lock-fill text-muted" title="Diatur di data karyawan"></i>
                        </label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" id="tarif_lembur" class="form-control readonly-pretty"
                                value="{{ $tarif_lembur }}" placeholder="0" readonly>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Dari data karyawan.</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="uang_lembur" class="form-label">Total Uang Lembur</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" id="uang_lembur" class="form-control readonly-pretty"
                                value="{{ $uang_lembur }}" placeholder="0" readonly>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Otomatis = jam &times; tarif.</div>
                    </div>

                    <!-- ===== Presensi Offline: jumlah (auto dari presensi) x tarif = total ===== -->
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-2 mb-2 mt-2">
                            <i class="bi bi-building-check text-primary d-inline-flex align-items-center" style="line-height: 1;"></i>
                            <span class="fw-semibold d-inline-flex align-items-center">Uang Kehadiran (dari data presensi)</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary fw-normal d-inline-flex align-items-center">
                                otomatis per periode
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="jumlah_hadir_offline" class="form-label">Hadir Offline</label>
                        <div class="input-suffix-wrap">
                            <input type="number" id="jumlah_hadir_offline" class="form-control no-spinner pe-5 readonly-pretty"
                                value="{{ $jumlah_hadir_offline }}" readonly>
                            <span class="input-suffix">hari</span>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Otomatis dari presensi (yang sudah absen pulang).</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="tarif_hadir_offline" class="form-label">
                            Tarif / Hadir Offline
                            <i class="bi bi-lock-fill text-muted" title="Diatur di data karyawan"></i>
                        </label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">Rp</span>
                            <input type="text" id="tarif_hadir_offline" class="form-control readonly-pretty"
                                value="{{ $tarif_hadir_offline }}" placeholder="0" readonly>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Dari data karyawan.</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="uang_hadir_offline" class="form-label">Total Uang Offline</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">Rp</span>
                            <input type="text" id="uang_hadir_offline" class="form-control readonly-pretty"
                                value="{{ $uang_hadir_offline }}" placeholder="0" readonly>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Otomatis = hari &times; tarif.</div>
                    </div>

                    <!-- ===== Presensi Online: jumlah (auto dari presensi) x tarif = total ===== -->
                    <div class="col-md-4 mb-3">
                        <label for="jumlah_hadir_online" class="form-label">Hadir Online</label>
                        <div class="input-suffix-wrap">
                            <input type="number" id="jumlah_hadir_online" class="form-control no-spinner pe-5 readonly-pretty"
                                value="{{ $jumlah_hadir_online }}" readonly>
                            <span class="input-suffix">hari</span>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Otomatis dari presensi (yang sudah absen pulang).</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="tarif_hadir_online" class="form-label">
                            Tarif / Hadir Online
                            <i class="bi bi-lock-fill text-muted" title="Diatur di data karyawan"></i>
                        </label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">Rp</span>
                            <input type="text" id="tarif_hadir_online" class="form-control readonly-pretty"
                                value="{{ $tarif_hadir_online }}" placeholder="0" readonly>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Dari data karyawan.</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="uang_hadir_online" class="form-label">Total Uang Online</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">Rp</span>
                            <input type="text" id="uang_hadir_online" class="form-control readonly-pretty"
                                value="{{ $uang_hadir_online }}" placeholder="0" readonly>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Otomatis = hari &times; tarif.</div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== DATA POTONGAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-red flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                        <i class="bi bi-dash-circle"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Potongan & Total</h5>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="potongan" class="form-label">Potongan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="potongan"
                                class="form-control @error('potongan') is-invalid @enderror rupiah" id="potongan" placeholder="0">
                        </div>
                        @error('potongan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pph21" class="form-label">PPH 21</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="pph21"
                                class="form-control @error('pph21') is-invalid @enderror rupiah" id="pph21" placeholder="0">
                        </div>
                        @error('pph21')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="potongan_bpjs_kesehatan" class="form-label">Potongan BPJS Kesehatan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="potongan_bpjs_kesehatan"
                                class="form-control @error('potongan_bpjs_kesehatan') is-invalid @enderror rupiah" id="potongan_bpjs_kesehatan" placeholder="0">
                        </div>
                        @error('potongan_bpjs_kesehatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="potongan_bpjs_ketenagakerjaan" class="form-label">Potongan BPJS Ketenagakerjaan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="potongan_bpjs_ketenagakerjaan"
                                class="form-control @error('potongan_bpjs_ketenagakerjaan') is-invalid @enderror rupiah" id="potongan_bpjs_ketenagakerjaan" placeholder="0">
                        </div>
                        @error('potongan_bpjs_ketenagakerjaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Potongan Pinjaman (terhubung ke fitur Pengembalian) -->
                    <div class="col-md-12 mb-3">
                        <label for="potongan_pinjaman" class="form-label">
                            Potongan Pinjaman / Kasbon
                            <i class="bi bi-link-45deg text-primary" title="Terhubung ke fitur Pengembalian"></i>
                        </label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="potongan_pinjaman"
                                class="form-control @error('potongan_pinjaman') is-invalid @enderror rupiah" id="potongan_pinjaman" placeholder="0">
                        </div>
                        <div class="form-text d-flex align-items-center gap-1 mt-1">
                            <i class="bi bi-info-circle text-muted"></i>
                            <span class="text-muted">
                                Sisa pinjaman karyawan ini:
                                <strong class="{{ $sisaPinjaman > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($sisaPinjaman, 0, ',', '.') }}
                                </strong>.
                                Nominal potongan otomatis tercatat sebagai <em>pengembalian pinjaman</em> &amp; mengurangi sisa.
                            </span>
                        </div>
                        @error('potongan_pinjaman')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Total -->
                    <div class="col-md-12 mb-1">
                        <label for="total" class="form-label fw-semibold">Total Gaji Diterima</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" id="total"
                                class="form-control form-control-lg readonly-total"
                                value="{{ $total ? number_format((int)$total, 0, ',', '.') : '' }}" readonly>
                        </div>
                        @error('total')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!--================== DATA LAINNYA ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-blue flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                        <i class="bi bi-info-circle"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Informasi Lainnya</h5>
                </div>

                <div class="row">
                    <!-- Status -->
                    <div class="col-md-12 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" wire:model.defer="status"
                            class="form-select @error('status') is-invalid @enderror">
                            <option value="">-- Pilih Status --</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea id="deskripsi" wire:model.defer="deskripsi" rows="3"
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            placeholder="Masukkan catatan / deskripsi (opsional)"></textarea>
                        @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!--================== TOMBOL ==================-->
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span>{{ $this->mode === 'create' ? 'Tambah Data' : 'Simpan Perubahan' }}</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // ================= FORMAT RUPIAH =================
    function formatRupiah(angka) {
        let numberString = angka.toString().replace(/[^,\d]/g, "");
        let sisa = numberString.length % 3;
        let rupiah = numberString.substr(0, sisa);
        let ribuan = numberString.substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return rupiah ? 'Rp ' + rupiah : '';
    }

    // ================= AUTO FORMAT INPUT RUPIAH (tanpa "Rp", prefix terpisah) =================
    document.querySelectorAll('.rupiah').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^,\d]/g, "");
            e.target.value = formatNumber(value);

            hitungTotal(); // setiap kali input berubah, total dihitung ulang
        });
    });

    // ================= HITUNG TOTAL =================
    function hitungTotal() {
        const ambil = (id) => parseInt((document.getElementById(id)?.value || '').replace(/[^,\d]/g, "")) || 0;

        // Pendapatan
        let pendapatan =
            ambil('gaji_pokok') +
            ambil('bonus_kinerja') +
            ambil('bonus_lainnya') +
            ambil('bonus_penyelesaian_task') +
            ambil('uang_lembur') +
            ambil('uang_hadir_offline') +
            ambil('uang_hadir_online') +
            ambil('tunjangan_kesehatan') +
            ambil('tunjangan_thr') +
            ambil('tunjangan_ketenagakerjaan') +
            ambil('tunjangan_lainnya') +
            ambil('tunjangan_transport') +
            ambil('tunjangan_makan');

        // Potongan
        let potongan =
            ambil('potongan') +
            ambil('potongan_bpjs_kesehatan') +
            ambil('potongan_bpjs_ketenagakerjaan') +
            ambil('potongan_pinjaman') +
            ambil('pph21');

        let total = pendapatan - potongan;

        // tampilkan di input total (angka saja, "Rp" sudah jadi prefix)
        document.getElementById('total').value = formatNumber(total);

        // update ke Livewire (biar tersimpan juga di backend)
        @this.set('total', total);
    }

    // Format angka dengan pemisah ribuan TANPA "Rp" (Rp ada di prefix terpisah)
    function formatNumber(angka) {
        let n = angka.toString().replace(/[^,\d]/g, "");
        let sisa = n.length % 3;
        let hasil = n.substr(0, sisa);
        let ribuan = n.substr(sisa).match(/\d{3}/gi);
        if (ribuan) {
            let sep = sisa ? '.' : '';
            hasil += sep + ribuan.join('.');
        }
        return hasil;
    }

    // ================= HITUNG UANG LEMBUR (jam x tarif) =================
    function hitungLembur() {
        const jamEl = document.getElementById('jam_lembur');
        const tarifEl = document.getElementById('tarif_lembur');
        const uangEl = document.getElementById('uang_lembur');
        if (!uangEl) return;

        let jam = parseInt((jamEl?.value || '').replace(/[^0-9]/g, "")) || 0;
        let tarif = parseInt((tarifEl?.value || '').replace(/[^,\d]/g, "")) || 0;

        // rapikan tampilan tarif (tanpa "Rp", karena Rp sudah jadi prefix)
        if (tarifEl && document.activeElement === tarifEl) {
            tarifEl.value = formatNumber(tarif);
        }

        uangEl.value = formatNumber(jam * tarif);
        hitungTotal();
    }

    document.getElementById('jam_lembur')?.addEventListener('input', hitungLembur);
    document.getElementById('tarif_lembur')?.addEventListener('input', hitungLembur);

    // ================= HITUNG UANG PRESENSI (hari x tarif) =================
    function hitungPresensiSatu(jumlahId, tarifId, uangId) {
        const jumlahEl = document.getElementById(jumlahId);
        const tarifEl = document.getElementById(tarifId);
        const uangEl = document.getElementById(uangId);
        if (!uangEl) return;

        let jumlah = parseInt((jumlahEl?.value || '').replace(/[^0-9]/g, "")) || 0;
        let tarif = parseInt((tarifEl?.value || '').replace(/[^,\d]/g, "")) || 0;

        if (tarifEl && document.activeElement === tarifEl) {
            tarifEl.value = formatNumber(tarif);
        }

        uangEl.value = formatNumber(jumlah * tarif);
    }

    function hitungPresensi() {
        hitungPresensiSatu('jumlah_hadir_offline', 'tarif_hadir_offline', 'uang_hadir_offline');
        hitungPresensiSatu('jumlah_hadir_online', 'tarif_hadir_online', 'uang_hadir_online');
        hitungTotal();
    }

    document.getElementById('tarif_hadir_offline')?.addEventListener('input', hitungPresensi);
    document.getElementById('tarif_hadir_online')?.addEventListener('input', hitungPresensi);

    // Panggil saat halaman pertama kali load (jaga2 kalau edit data lama)
    document.addEventListener('DOMContentLoaded', function() {
        hitungLembur();
        hitungPresensi();
        hitungTotal();
    });
</script>
@endpush

@push('styles')
<style>
    .gaji-picker-btn { cursor:pointer; }
    .gaji-picker-btn::after { content:"\F282"; font-family:"bootstrap-icons"; float:right; color:#94a3b8; font-size:.8rem; }
    .gaji-pick-list { max-height:320px; overflow-y:auto; text-align:left; display:flex; flex-direction:column; gap:.4rem; padding:.2rem; }
    .gaji-pick-item { display:block; width:100%; text-align:left; border:1px solid #e6e8f2; background:#fff; border-radius:12px; padding:.7rem .9rem; font-weight:600; color:#1e293b; font-size:.92rem; transition:all .15s ease; }
    .gaji-pick-item:hover { border-color:#7c3aed; background:linear-gradient(135deg,rgba(124,58,237,.10),rgba(78,70,229,.04)); transform:translateY(-1px); }
    .gaji-pick-empty { text-align:center; color:#94a3b8; padding:1.5rem; font-size:.9rem; }
</style>
@endpush

@push('scripts')
<script>
    window.__gajiUsers = @json($users->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])->values());

    if (!window.__gajiUserPickerBound) {
        window.__gajiUserPickerBound = true;
        window.gajiKaryawanPicker = function (btn) {
            if (typeof Swal === 'undefined') return;
            const el = btn.closest('[wire\\:id]'); if (!el) return;
            const cid = el.getAttribute('wire:id');
            const items = window.__gajiUsers || [];
            const esc = (s) => String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
            const rows = items.length
                ? items.map(it => '<button type="button" class="gaji-pick-item" data-id="' + esc(it.id) + '" data-search="' + esc((it.name || '').toLowerCase()) + '">' + esc(it.name) + '</button>').join('')
                : '<div class="gaji-pick-empty">Tidak ada karyawan</div>';
            Swal.fire({
                title: 'Pilih Nama Karyawan',
                html: '<input id="gajiPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari...">' +
                      '<div id="gajiPickList" class="gaji-pick-list">' + rows + '</div>',
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem',
                didOpen: () => {
                    const search = document.getElementById('gajiPickSearch');
                    const listEl = document.getElementById('gajiPickList');
                    if (search) {
                        search.addEventListener('input', () => {
                            const q = search.value.toLowerCase();
                            listEl.querySelectorAll('.gaji-pick-item').forEach(b => { b.style.display = b.dataset.search.includes(q) ? '' : 'none'; });
                        });
                        setTimeout(() => search.focus(), 100);
                    }
                    listEl.querySelectorAll('.gaji-pick-item').forEach(b => {
                        b.addEventListener('click', () => {
                            if (window.Livewire) window.Livewire.find(cid).set('nama_karyawan', b.dataset.id);
                            Swal.close();
                        });
                    });
                }
            });
        };
    }

    // Setiap update Livewire (mis. pilih karyawan / ganti periode) → hitung ulang total
    // di klien, karena set() dari picker tidak memicu event input pada field readonly.
    document.addEventListener('livewire:init', function () {
        if (window.__gajiRecalcHook || typeof Livewire === 'undefined') return;
        window.__gajiRecalcHook = true;
        Livewire.hook('commit', function ({ succeed }) {
            succeed(function () {
                queueMicrotask(function () {
                    if (typeof window.hitungLembur === 'function') window.hitungLembur();
                    if (typeof window.hitungPresensi === 'function') window.hitungPresensi();
                    if (typeof window.hitungTotal === 'function') window.hitungTotal();
                });
            });
        });
    });
</script>
@endpush
