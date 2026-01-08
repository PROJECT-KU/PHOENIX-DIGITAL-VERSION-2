<form wire:submit="{{$this->isEdit ? 'update' : 'store'}}" class="row">
    <div class="col-md-6">
        <h5 class="mb-3 text-primary">Data Akun</h5>

        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" placeholder="nama karyawan" wire:model="name" class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" placeholder="sample@email.com" wire:model="email" class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select wire:model="role_id" class="form-select @error('role_id') is-invalid @enderror">
                <option value="">Pilih Role</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <label class="form-label">Password {{ $isEdit ? '(Kosongkan jika tidak diganti)' : '' }}</label>
        <div class="form-group position-relative has-icon-right" x-data="{ show: false }">
            <input
                :type="show ? 'text' : 'password'"
                placeholder="********"
                wire:model="password"
                class="form-control @error('password') is-invalid @enderror">

            <div class="form-control-icon" @click="show = !show" style="cursor: pointer">
                <i class="bi" :class="show ? 'bi-eye' : 'bi-eye-slash'"></i>
            </div>

            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <h5 class="mb-3 text-primary">Data Karyawan & Bank</h5>

        <div class="mb-3">
            <label class="form-label">Jabatan</label>
            <input type="text" placeholder="jabatan karyawan" wire:model="jabatan" class="form-control @error('jabatan') is-invalid @enderror">
            @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Bank</label>
                <input type="text" wire:model="nama_bank" class="form-control" placeholder="Contoh: BCA">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">No. Rekening</label>
                <input
                    type="text"
                    placeholder="0000-0000-0000-0000"
                    wire:model="nomor_rekening"
                    class="form-control"
                    maxlength="19"
                    x-data
                    @input="
                    let val = $el.value.replace(/\D/g, '');
                    if (val.length > 0) {
                        val = val.match(/.{1,4}/g).join('-');
                    }
                    $el.value = val;
        ">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">No. Telepon</label>
            <input type="text" placeholder="082134******" wire:model="phone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea wire:model="alamat" placeholder="alamat karyawan" class="form-control" rows="2"></textarea>
        </div>
    </div>

    <!-- Tombol -->
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-send me-1"></i>
            {{ $this->isEdit ? 'Simpan Perubahan' : 'Tambah Data' }}
        </button>
    </div>
</form>