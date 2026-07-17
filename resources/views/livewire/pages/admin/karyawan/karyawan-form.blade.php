<div>
    <style>
        .kry-input {
            border-radius: 12px;
            border: 1.5px solid #e7e9f2;
            padding: 11px 14px;
            transition: 0.18s;
        }

        .kry-input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.18rem rgba(124, 58, 237, 0.12);
        }

        /* ===== Panel & field dengan ikon ===== */
        .kry-panel {
            border-radius: 18px;
            padding: 20px;
            background: linear-gradient(135deg, #ffffff, #fbfbff);
            border: 1px solid #eef0f7;
            box-shadow: 0 6px 18px rgba(108, 99, 255, .05);
        }

        .kry-field {
            position: relative;
        }

        .kry-field-ico {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #a3a9bd;
            pointer-events: none;
            z-index: 3;
            display: flex;
            align-items: center;
            line-height: 1;
        }

        .kry-field-ico i.bi {
            display: flex;
            align-items: center;
            line-height: 1;
        }

        .kry-field-ico i.bi::before {
            display: block;
            line-height: 1;
        }

        .kry-input.has-ico {
            padding-left: 42px;
        }

        /* Textarea: ikon disejajarkan ke BARIS PERTAMA, bukan tengah kotak. */
        .kry-field-area .kry-field-ico {
            top: 13px;
            transform: none;
        }

        /* ===== Kartu info read-only (NIK, masa kerja, tanggal dibuat) ===== */
        .kry-info-card {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .9rem 1rem;
            border-radius: 14px;
            border: 1px solid #eef0f6;
            background: #fff;
            height: 100%;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .kry-info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, .07);
        }

        .kry-info-ico {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        /* Glyph .bi digambar di ::before & punya vertical-align — perlu dua lapis
           flex + line-height:1 agar benar-benar di tengah kotaknya. */
        .kry-info-ico i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
            vertical-align: 0;
        }

        .kry-info-ico i.bi::before {
            display: block;
            line-height: 1;
        }

        .kry-info-label {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .kry-info-value {
            font-weight: 700;
            color: #1e293b;
            font-size: .98rem;
            line-height: 1.25;
        }

        .kry-info-nik {
            font-family: 'Courier New', monospace;
            letter-spacing: 1.2px;
            color: #4e46e5;
        }

        .kry-info-sub {
            font-size: .72rem;
            color: #94a3b8;
        }

        .kry-note {
            border-radius: 12px;
            padding: 11px 14px;
            font-size: .82rem;
            color: #4338ca;
            background: linear-gradient(135deg, rgba(124, 58, 237, .06), rgba(108, 99, 255, .06));
            border: 1px dashed rgba(124, 58, 237, .3);
        }

        .kry-note i.bi {
            color: #7c3aed;
            font-size: 1.05rem;
        }

        .kry-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .kry-section-ico {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .kry-section-ico i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }

        .kry-section-ico i.bi::before {
            display: block;
            line-height: 1;
        }

        /* ===== Kartu tarif bonus (glossy) ===== */
        .kry-tarif-wrap {
            border-radius: 18px;
            padding: 18px;
            background: linear-gradient(135deg, rgba(124, 58, 237, .05), rgba(108, 99, 255, .05));
            border: 1px solid rgba(124, 58, 237, .14);
        }

        .kry-tarif-card {
            border: 1.5px solid #eef0f7;
            border-radius: 16px;
            padding: 14px;
            background: linear-gradient(135deg, #ffffff, #f8f9ff);
            box-shadow: 0 6px 18px rgba(108, 99, 255, .06);
            transition: 0.18s;
            height: 100%;
        }

        .kry-tarif-card:focus-within {
            border-color: #7c3aed;
            box-shadow: 0 10px 24px rgba(124, 58, 237, .16);
            transform: translateY(-2px);
        }

        .kry-tarif-ico {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .kry-tarif-ico i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }

        .kry-tarif-ico i.bi::before {
            display: block;
            line-height: 1;
        }

        .kry-tarif-label {
            font-weight: 700;
            color: #1e293b;
            font-size: .9rem;
            line-height: 1.15;
        }

        .kry-tarif-unit {
            font-size: .72rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .kry-rp-field {
            position: relative;
        }

        .kry-rp-prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #a3a9bd;
            font-weight: 600;
            font-size: .9rem;
            pointer-events: none;
            z-index: 2;
        }

        .kry-rp-input {
            width: 100%;
            border: 1.5px solid #e7e9f2;
            border-radius: 12px;
            background: #fff;
            padding: 12px 14px 12px 40px;
            font-weight: 700;
            font-size: 1.15rem;
            color: #1e293b;
            transition: 0.18s;
        }

        .kry-rp-input::placeholder {
            color: #cbd0de;
            font-weight: 600;
        }

        .kry-rp-input:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.18rem rgba(124, 58, 237, 0.12);
        }

        /* hilangkan spinner number */
        .kry-rp-input::-webkit-outer-spin-button,
        .kry-rp-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .kry-rp-input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

    <form wire:submit="{{ $this->isEdit ? 'update' : 'store' }}">
        <!--================== DATA AKUN & KARYAWAN ==================-->
        <div class="kry-panel">
            <div class="kry-section-title mb-3">
                <span class="kry-section-ico" style="background: linear-gradient(135deg,#7c3aed,#6d28d9);">
                    <i class="bi bi-person-badge-fill"></i>
                </span>
                <div class="d-flex flex-column">
                    <span>Data Akun &amp; Karyawan</span>
                    <span class="text-muted fw-normal" style="font-size:.8rem;">Informasi login dan jabatan karyawan.</span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-person"></i></span>
                        <input type="text" placeholder="Nama karyawan" wire:model="name"
                            class="form-control kry-input has-ico @error('name') is-invalid @enderror">
                    </div>
                    @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Email <span class="text-danger">*</span></label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-envelope"></i></span>
                        <input type="email" placeholder="sample@email.com" wire:model="email"
                            class="form-control kry-input has-ico @error('email') is-invalid @enderror">
                    </div>
                    @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Role <span class="text-danger">*</span></label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-shield-check"></i></span>
                        <select wire:model="role_id" class="form-select kry-input has-ico text-capitalize @error('role_id') is-invalid @enderror">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('role_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                @if($isEdit)
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Status Akun <span class="text-danger">*</span></label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-shield-lock"></i></span>
                        <select wire:model="status" class="form-select kry-input has-ico @error('status') is-invalid @enderror">
                            <option value="active">Active</option>
                            <option value="blokir">Blokir</option>
                        </select>
                    </div>
                    @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    <small class="text-muted" style="font-size:.78rem;">Pilih <b>Active</b> untuk membuka blokir akun (mis. terkena blokir 3x gagal login).</small>
                </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Jabatan <span class="text-danger">*</span></label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-briefcase"></i></span>
                        <input type="text" placeholder="Jabatan karyawan" wire:model="jabatan"
                            class="form-control kry-input has-ico @error('jabatan') is-invalid @enderror">
                    </div>
                    @error('jabatan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        Atasan Langsung
                        <span class="text-muted fw-normal" style="font-size:.8rem;">— opsional, untuk pemberian task</span>
                    </label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-diagram-3"></i></span>
                        <select wire:model="atasan_id"
                            class="form-select kry-input has-ico text-capitalize @error('atasan_id') is-invalid @enderror">
                            <option value="">— Tidak ada —</option>
                            @foreach($atasanOptions as $opt)
                            <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('atasan_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold text-dark">
                        Password {{ $isEdit ? '(kosongkan jika tidak diganti)' : '' }}
                        @unless($isEdit) <span class="text-danger">*</span> @endunless
                    </label>
                    <div class="kry-field" x-data="{ show: false }">
                        <span class="kry-field-ico"><i class="bi bi-lock"></i></span>
                        <input :type="show ? 'text' : 'password'" placeholder="********" wire:model="password"
                            class="form-control kry-input has-ico pe-5 @error('password') is-invalid @enderror">
                        <span @click="show = !show"
                            class="position-absolute end-0 top-50 translate-middle-y pe-3" style="cursor: pointer; z-index: 5;">
                            <i class="bi text-secondary" :class="show ? 'bi-eye' : 'bi-eye-slash'"></i>
                        </span>
                    </div>
                    @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- ===== Data Pribadi & Kepegawaian (paritas dengan halaman Profil) ===== --}}
        <div class="kry-tarif-wrap mt-4">
            <div class="kry-section-title mb-3">
                <span class="kry-section-ico" style="background: linear-gradient(135deg,#0ea5e9,#2563eb);">
                    <i class="bi bi-person-vcard"></i>
                </span>
                <div class="d-flex flex-column">
                    <span>Data Pribadi &amp; Kepegawaian</span>
                    <span class="text-muted fw-normal" style="font-size:.8rem;">
                        Sama dengan yang ada di halaman Profil karyawan. Boleh dikosongkan.
                    </span>
                </div>
            </div>

            {{-- Info read-only: dikelola sistem, bukan diketik admin. --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="kry-info-card">
                        <span class="kry-info-ico" style="background:linear-gradient(135deg,#6c63ff,#4e46e5); box-shadow:0 6px 14px rgba(108,99,255,.35);">
                            <i class="bi bi-person-vcard-fill"></i>
                        </span>
                        <div>
                            <div class="kry-info-label">Nomor Induk Karyawan</div>
                            <div class="kry-info-value kry-info-nik">{{ $nik ?: '—' }}</div>
                            <div class="kry-info-sub">{{ $isEdit ? 'Otomatis, tidak bisa diubah' : 'Dibuat otomatis saat disimpan' }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="kry-info-card">
                        <span class="kry-info-ico" style="background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 6px 14px rgba(16,185,129,.35);">
                            <i class="bi bi-briefcase-fill"></i>
                        </span>
                        <div>
                            <div class="kry-info-label">Masa Kerja</div>
                            <div class="kry-info-value">{{ $isEdit ? ($userModel->detail?->masaKerja() ?? '—') : '—' }}</div>
                            <div class="kry-info-sub">{{ $isEdit ? 'Dihitung dari tanggal bergabung' : 'Tersedia setelah disimpan' }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="kry-info-card">
                        <span class="kry-info-ico" style="background:linear-gradient(135deg,#f59e0b,#d97706); box-shadow:0 6px 14px rgba(245,158,11,.35);">
                            <i class="bi bi-calendar-plus-fill"></i>
                        </span>
                        <div>
                            <div class="kry-info-label">Tanggal Dibuat</div>
                            <div class="kry-info-value">
                                {{ $isEdit && $userModel->created_at ? $userModel->created_at->translatedFormat('d M Y') : '—' }}
                            </div>
                            <div class="kry-info-sub">
                                {{ $isEdit && $userModel->created_at ? 'Pukul '.$userModel->created_at->format('H:i') : 'Terisi saat disimpan' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Field yang bisa diisi admin. --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Tanggal Bergabung</label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-calendar-check"></i></span>
                        <input type="date" wire:model="tanggal_bergabung"
                            class="form-control kry-input has-ico @error('tanggal_bergabung') is-invalid @enderror">
                    </div>
                    <small class="text-muted">Dasar masa kerja. Kosong = pakai tanggal dibuat.</small>
                    @error('tanggal_bergabung') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Tanggal Lahir</label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-cake2"></i></span>
                        <input type="date" wire:model="tanggal_lahir"
                            class="form-control kry-input has-ico @error('tanggal_lahir') is-invalid @enderror">
                    </div>
                    @error('tanggal_lahir') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">No. HP</label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-telephone"></i></span>
                        <input type="text" placeholder="08xxxxxxxxxx" wire:model="phone"
                            class="form-control kry-input has-ico @error('phone') is-invalid @enderror">
                    </div>
                    @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Nama Bank</label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-bank"></i></span>
                        <input type="text" placeholder="mis. Bank Mandiri" wire:model="nama_bank"
                            class="form-control kry-input has-ico @error('nama_bank') is-invalid @enderror">
                    </div>
                    @error('nama_bank') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">No. Rekening</label>
                    <div class="kry-field">
                        <span class="kry-field-ico"><i class="bi bi-credit-card"></i></span>
                        <input type="text" placeholder="Nomor rekening" wire:model="nomor_rekening"
                            class="form-control kry-input has-ico @error('nomor_rekening') is-invalid @enderror">
                    </div>
                    @error('nomor_rekening') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">Alamat</label>
                    <div class="kry-field kry-field-area">
                        <span class="kry-field-ico"><i class="bi bi-geo-alt"></i></span>
                        <textarea wire:model="alamat" rows="1" placeholder="Alamat lengkap"
                            class="form-control kry-input has-ico @error('alamat') is-invalid @enderror"></textarea>
                    </div>
                    @error('alamat') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- ===== Tarif bonus per karyawan (full width, di bawah kedua kolom) ===== --}}
        <div class="kry-tarif-wrap mt-4">
            <div class="kry-section-title mb-2">
                <span class="kry-section-ico" style="background: linear-gradient(135deg,#10b981,#059669);">
                    <i class="bi bi-cash-coin"></i>
                </span>
                <div class="d-flex flex-column">
                    <span>Tarif Bonus Kehadiran &amp; Lembur</span>
                    <span class="text-muted fw-normal" style="font-size:.8rem;">
                        Dipakai otomatis saat menghitung gaji dari data presensi. Kosongkan/0 bila tidak ada bonus.
                    </span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="kry-tarif-card">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="kry-tarif-ico" style="background: linear-gradient(135deg,#6c63ff,#4e46e5);">
                                <i class="bi bi-building-check"></i>
                            </span>
                            <div>
                                <div class="kry-tarif-label">Hadir Offline</div>
                                <div class="kry-tarif-unit">per hari kehadiran</div>
                            </div>
                        </div>
                        <div class="kry-rp-field">
                            <span class="kry-rp-prefix">Rp</span>
                            <input type="number" min="0" step="1000" wire:model="tarif_presensi_offline"
                                class="kry-rp-input @error('tarif_presensi_offline') is-invalid @enderror" placeholder="0">
                        </div>
                        @error('tarif_presensi_offline') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="kry-tarif-card">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="kry-tarif-ico" style="background: linear-gradient(135deg,#0ea5e9,#2563eb);">
                                <i class="bi bi-globe2"></i>
                            </span>
                            <div>
                                <div class="kry-tarif-label">Hadir Online</div>
                                <div class="kry-tarif-unit">per hari kehadiran</div>
                            </div>
                        </div>
                        <div class="kry-rp-field">
                            <span class="kry-rp-prefix">Rp</span>
                            <input type="number" min="0" step="1000" wire:model="tarif_presensi_online"
                                class="kry-rp-input @error('tarif_presensi_online') is-invalid @enderror" placeholder="0">
                        </div>
                        @error('tarif_presensi_online') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="kry-tarif-card">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="kry-tarif-ico" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
                                <i class="bi bi-moon-stars-fill"></i>
                            </span>
                            <div>
                                <div class="kry-tarif-label">Lembur</div>
                                <div class="kry-tarif-unit">per jam lembur</div>
                            </div>
                        </div>
                        <div class="kry-rp-field">
                            <span class="kry-rp-prefix">Rp</span>
                            <input type="number" min="0" step="1000" wire:model="tarif_lembur_per_jam"
                                class="kry-rp-input @error('tarif_lembur_per_jam') is-invalid @enderror" placeholder="0">
                        </div>
                        @error('tarif_lembur_per_jam') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol -->
        <div class="d-flex mt-4">
            <button type="submit"
                class="btn flex-grow-1 d-inline-flex align-items-center justify-content-center text-white rounded-pill shadow-lg"
                style="height: 55px; background: linear-gradient(135deg, #6c63ff, #4e46e5); font-weight: 600; font-size: 1.1rem; border: none;"
                wire:loading.attr="disabled" wire:target="store,update">
                <span wire:loading.remove wire:target="store,update" class="d-inline-flex align-items-center">
                    <i class="bi bi-check2-circle me-2 fs-4"></i>
                    <span>{{ $this->isEdit ? 'Simpan Perubahan' : 'Tambah Data' }}</span>
                </span>
            </button>
        </div>
    </form>
</div>