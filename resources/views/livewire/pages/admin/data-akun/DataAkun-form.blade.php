<div>
    <form wire:submit.prevent="save" class="p-3">
        <div class="row g-3">
            <!-- Nama Akun -->
            <div class="col-md-6">
                <label for="namaAkun" class="form-label">Nama Akun <span class="text-danger">*</span></label>
                <input type="text" id="namaAkun" wire:model.defer="nama_akun"
                    class="form-control @error('nama_akun') is-invalid @enderror"
                    placeholder="Masukkan nama akun">
                @error('nama_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Username -->
            <div class="col-md-6">
                <label for="username" class="form-label">Username Akun <span class="text-danger">*</span></label>
                <input type="text" id="username" wire:model.defer="username_akun"
                    class="form-control @error('username_akun') is-invalid @enderror"
                    placeholder="Masukkan username">
                @error('username_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="col-md-6">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="text" id="password" wire:model.defer="password_akun"
                    class="form-control @error('password_akun') is-invalid @enderror"
                    placeholder="Masukkan password">
                @error('password_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Link Login -->
            <div class="col-md-6">
                <label for="linkLogin" class="form-label">Link Login Akun <span class="text-danger">*</span></label>
                <input type="url" id="linkLogin" wire:model.defer="link_login_akun"
                    class="form-control @error('link_login_akun') is-invalid @enderror"
                    placeholder="https://example.com/login">
                @error('link_login_akun')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- harga satuan -->
            <div class="col-md-4">
                <label for="harga_satuan" class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                <input type="text" id="harga_satuan" wire:model.defer="harga_satuan"
                    class="form-control @error('harga_satuan') is-invalid @enderror rupiah"
                    placeholder="Masukkan username">
                @error('harga_satuan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- PJ Akun -->
            <div class="col-md-4">
                <label for="pjAkun" class="form-label">PJ Akun <span class="text-danger">*</span></label>
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
            <div class="col-md-4">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select id="status" wire:model.defer="status"
                    class="form-select @error('status') is-invalid @enderror">
                    <option value="">-- Pilih Status --</option>
                    <option value="active">Active</option>
                    <option value="non-active">Non-Active</option>
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

<!--================== FORMAT RUPIAH ==================-->
@push('scripts')
<script>
    document.querySelectorAll('.rupiah').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^,\d]/g, "");
            let numberString = value.toString();
            let sisa = numberString.length % 3;
            let rupiah = numberString.substr(0, sisa);
            let ribuan = numberString.substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            e.target.value = rupiah ? 'Rp ' + rupiah : '';
        });
    });
</script>
@endpush
<!--================== END ==================-->