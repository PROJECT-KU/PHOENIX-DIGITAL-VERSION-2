<div>
    <form wire:submit.prevent="save">
        <div class="row g-4">

            <div class="col-md-6">
                <label for="namaAkun" class="form-label text-secondary fw-bold">Nama Akun <span class="text-danger">*</span></label>
                <input type="text" id="namaAkun" wire:model.defer="nama_akun"
                    class="form-control shadow-none @error('nama_akun') is-invalid @enderror"
                    placeholder="Masukkan nama akun">
                @error('nama_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="username" class="form-label text-secondary fw-bold">Username Akun <span class="text-danger">*</span></label>
                <input type="text" id="username" wire:model.defer="username_akun"
                    class="form-control shadow-none @error('username_akun') is-invalid @enderror"
                    placeholder="Masukkan username">
                @error('username_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label text-secondary fw-bold">Password <span class="text-danger">*</span></label>
                <input type="text" id="password" wire:model.defer="password_akun"
                    class="form-control shadow-none @error('password_akun') is-invalid @enderror"
                    placeholder="Masukkan password">
                @error('password_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="linkLogin" class="form-label text-secondary fw-bold">Link Login Akun <span class="text-danger">*</span></label>
                <input type="url" id="linkLogin" wire:model.defer="link_login_akun"
                    class="form-control shadow-none @error('link_login_akun') is-invalid @enderror"
                    placeholder="https://example.com/login">
                @error('link_login_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 py-1">
                <hr class="text-secondary opacity-25 m-0">
            </div>

            <div class="col-md-4">
                <label for="harga_satuan" class="form-label text-secondary fw-bold">Harga Satuan <span class="text-danger">*</span></label>
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                        style="pointer-events: none; z-index: 5;">
                        Rp
                    </span>
                    <input type="text"
                        inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                        wire:model.defer="harga_satuan"
                        class="form-control shadow-none @error('harga_satuan') is-invalid @enderror"
                        placeholder="0"
                        style="background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 8px; padding-left: 45px; height: 100%;">
                    @error('harga_satuan')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-4">
                <label for="pjAkun" class="form-label text-secondary fw-bold">PJ Akun <span class="text-danger">*</span></label>
                <select id="pjAkun" wire:model.defer="pj_akun"
                    class="form-select shadow-none @error('pj_akun') is-invalid @enderror">
                    <option value="">-- Pilih Penanggung Jawab --</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('pj_akun')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label text-secondary fw-bold">Status <span class="text-danger">*</span></label>
                <select id="status" wire:model.defer="status"
                    class="form-select shadow-none @error('status') is-invalid @enderror">
                    <option value="">-- Pilih Status --</option>
                    <option value="active">Active</option>
                    <option value="non-active">Non-Active</option>
                </select>
                @error('status')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="deskripsi" class="form-label text-secondary fw-bold">Deskripsi</label>
                <textarea id="deskripsi" wire:model.defer="deskripsi" rows="3"
                    class="form-control shadow-none @error('deskripsi') is-invalid @enderror"
                    placeholder="Masukkan deskripsi produk"></textarea>
                @error('deskripsi')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-5 pt-4 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center shadow-sm"
                style="height: 52px; border-radius: 8px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span class="fw-semibold">{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
            </button>
        </div>
    </form>
</div>