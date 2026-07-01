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
        }
    </style>

    <form wire:submit="{{ $this->isEdit ? 'update' : 'store' }}">
        <div class="row g-4">
            <!--================== DATA AKUN ==================-->
            <div class="col-lg-6">
                <div class="kry-section-title">
                    <span class="kry-section-ico" style="background: linear-gradient(135deg,#7c3aed,#6d28d9);">
                        <i class="bi bi-person-fill"></i>
                    </span>
                    <span>Data Akun</span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" placeholder="Nama karyawan" wire:model="name"
                        class="form-control kry-input @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Email <span class="text-danger">*</span></label>
                    <input type="email" placeholder="sample@email.com" wire:model="email"
                        class="form-control kry-input @error('email') is-invalid @enderror">
                    @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Role <span class="text-danger">*</span></label>
                    <select wire:model="role_id" class="form-select kry-input text-capitalize @error('role_id') is-invalid @enderror">
                        <option value="">Pilih Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">
                        Password {{ $isEdit ? '(kosongkan jika tidak diganti)' : '' }}
                        @unless($isEdit) <span class="text-danger">*</span> @endunless
                    </label>
                    <div class="position-relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" placeholder="********" wire:model="password"
                            class="form-control kry-input pe-5 @error('password') is-invalid @enderror">
                        <span @click="show = !show"
                            class="position-absolute end-0 top-50 translate-middle-y pe-3" style="cursor: pointer; z-index: 5;">
                            <i class="bi text-secondary" :class="show ? 'bi-eye' : 'bi-eye-slash'"></i>
                        </span>
                    </div>
                    @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>

            <!--================== DATA KARYAWAN & BANK ==================-->
            <div class="col-lg-6">
                <div class="kry-section-title">
                    <span class="kry-section-ico" style="background: linear-gradient(135deg,#059669,#10b981);">
                        <i class="bi bi-briefcase-fill"></i>
                    </span>
                    <span>Data Karyawan & Bank</span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Jabatan <span class="text-danger">*</span></label>
                    <input type="text" placeholder="Jabatan karyawan" wire:model="jabatan"
                        class="form-control kry-input @error('jabatan') is-invalid @enderror">
                    @error('jabatan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6 mb-1">
                        <label class="form-label fw-semibold text-dark">Nama Bank</label>
                        <input type="text" wire:model="nama_bank" class="form-control kry-input" placeholder="Contoh: BCA">
                    </div>
                    <div class="col-md-6 mb-1">
                        <label class="form-label fw-semibold text-dark">No. Rekening</label>
                        <input type="text" placeholder="0000-0000-0000-0000" wire:model="nomor_rekening"
                            class="form-control kry-input @error('nomor_rekening') is-invalid @enderror" maxlength="19"
                            x-data @input="
                            let val = $el.value.replace(/\D/g, '');
                            if (val.length > 0) { val = val.match(/.{1,4}/g).join('-'); }
                            $el.value = val;">
                        @error('nomor_rekening') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label fw-semibold text-dark">No. Telepon</label>
                    <input type="text" placeholder="082134******" wire:model="phone" class="form-control kry-input">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Alamat</label>
                    <textarea wire:model="alamat" placeholder="Alamat karyawan" class="form-control kry-input" rows="2"></textarea>
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