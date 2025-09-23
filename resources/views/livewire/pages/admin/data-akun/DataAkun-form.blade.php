<div>
    <form wire:submit.prevent="save" class="p-3">
        <div class="row g-3">
            <!-- Nama Akun -->
            <div class="col-md-6">
                <label for="namaAkun" class="form-label">Nama Akun</label>
                <input type="text" id="namaAkun" wire:model.defer="nama_akun"
                    class="form-control @error('nama_akun') is-invalid @enderror"
                    placeholder="Masukkan nama akun">
                @error('nama_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Username -->
            <div class="col-md-6">
                <label for="username" class="form-label">Username Akun</label>
                <input type="text" id="username" wire:model.defer="username_akun"
                    class="form-control @error('username_akun') is-invalid @enderror"
                    placeholder="Masukkan username">
                @error('username_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <input type="text" id="password" wire:model.defer="password_akun"
                    class="form-control @error('password_akun') is-invalid @enderror"
                    placeholder="Masukkan password">
                @error('password_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Link Login -->
            <div class="col-md-6">
                <label for="linkLogin" class="form-label">Link Login Akun</label>
                <input type="url" id="linkLogin" wire:model.defer="link_login_akun"
                    class="form-control @error('link_login_akun') is-invalid @enderror"
                    placeholder="https://example.com/login">
                @error('link_login_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- PJ Akun -->
            <div class="col-md-6">
                <label for="pjAkun" class="form-label">PJ Akun</label>
                <select id="pjAkun"
                    wire:model.defer="pj_akun"
                    class="form-select @error('pj_akun') is-invalid @enderror">
                    <option value="">-- Pilih Penanggung Jawab --</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                @error('pj_akun')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>


            <!-- Status -->
            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select id="status" wire:model.defer="status"
                    class="form-select @error('status') is-invalid @enderror">
                    <option value="">-- Pilih Status --</option>
                    <option value="active">Active</option>
                    <option value="nonactive">Nonactive</option>
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
                    placeholder="Masukkan deskripsi produk"></textarea>
                @error('deskripsi')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Tombol -->
        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>
                {{ $this->mode === 'create' ? 'Tambah Data' : 'Simpan Perubahan' }}
            </button>
        </div>
    </form>

</div>