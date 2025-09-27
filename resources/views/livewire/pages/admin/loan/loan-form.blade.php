<div>
    <div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            {{ $isEdit ? 'Edit' : 'Tambah' }} Peminjaman
        </h5>
    </div>

    <div class="card-body">
        <!-- Flash Messages -->
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form wire:submit="save">
            <div class="row">
                <!-- Nama Peminjam -->
                <div class="col-md-6 mb-3">
                    <label for="nama_peminjam" class="form-label">
                        Nama Peminjam <span class="text-danger">*</span>
                    </label>
                    <input type="text" wire:model="nama_peminjam"
                        class="form-control @error('nama_peminjam') is-invalid @enderror"
                        id="nama_peminjam" placeholder="Masukkan nama peminjam">
                    @error('nama_peminjam')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Tanggal Peminjaman -->
                <div class="col-md-6 mb-3">
                    <label for="tanggal_peminjam" class="form-label">
                        Tanggal Peminjaman <span class="text-danger">*</span>
                    </label>
                    <input type="date" wire:model="tanggal_peminjam"
                        class="form-control @error('tanggal_peminjam') is-invalid @enderror"
                        id="tanggal_peminjam">
                    @error('tanggal_peminjam')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nominal dengan format Rupiah -->
                <div class="col-md-6 mb-3"
                    x-data="{
                        displayValue: @entangle('nominal').defer,
                        formatRupiah(value) {
                            if (!value) return '';
                            let number_string = value.toString().replace(/[^,\d]/g, '');
                            let split = number_string.split(',');
                            let sisa = split[0].length % 3;
                            let rupiah = split[0].substr(0, sisa);
                            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                            if (ribuan) {
                                let separator = sisa ? '.' : '';
                                rupiah += separator + ribuan.join('.');
                            }
                            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                            return rupiah ? 'Rp ' + rupiah : '';
                        }
                    }"
                    x-init="$watch('displayValue', value => {
                        $refs.input.value = formatRupiah(value);
                    })"
                >
                    <label for="nominal" class="form-label">
                        Nominal Pinjaman <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                        x-ref="input"
                        wire:model.defer="nominal"
                        x-on:input="
                            let raw = $event.target.value.replace(/[^0-9]/g, '');
                            displayValue = raw; // simpan angka murni ke Livewire
                            $event.target.value = formatRupiah(raw); // tampilkan format Rp
                        "
                        class="form-control @error('nominal') is-invalid @enderror"
                        id="nominal"
                        placeholder="Rp 0">
                    @error('nominal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select wire:model="status"
                        class="form-select @error('status') is-invalid @enderror" id="status">
                        <option value="">Pilih Status</option>
                        <option value="pending">Pending</option>
                        <option value="berjalan">Berjalan</option>
                        <option value="lunas">Lunas</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="mb-3">
                <label for="deskripsi" class="form-label">
                    Deskripsi
                </label>
                <textarea wire:model="deskripsi"
                    class="form-control @error('deskripsi') is-invalid @enderror"
                    id="deskripsi" rows="4"
                    placeholder="Masukkan deskripsi pinjaman..."></textarea>
                @error('deskripsi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.loan.index') }}" class="btn btn-secondary" wire:navigate>
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        {{ $isEdit ? 'Perbarui' : 'Simpan' }}
                    </span>
                    <span wire:loading>
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

</div>