<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            {{ $isEdit ? 'Edit' : 'Tambah' }} Pengeluaran
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

        <form wire:submit="save" x-data="{ jenis_pengeluaran: @entangle('jenis_pengeluaran') }" x-cloak>
            <div class="row">
                <!-- Tanggal Transaksi -->
                <div class="col-md-6 mb-3">
                    <label for="tanggal_transaksi" class="form-label">
                        Tanggal Transaksi <span class="text-danger">*</span>
                    </label>
                    <input type="date" wire:model="tanggal_transaksi"
                        class="form-control @error('tanggal_transaksi') is-invalid @enderror" id="tanggal_transaksi">
                    @error('tanggal_transaksi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nominal -->
                <div class="col-md-6 mb-3">
                    <label for="nominal" class="form-label">
                        Nominal <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" wire:model="nominal"
                            class="form-control @error('nominal') is-invalid @enderror" id="nominal" step="0.01"
                            min="0" placeholder="0">
                        @error('nominal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror"
                        id="status">
                        <option value="">Pilih Status</option>
                        @foreach ($statusOptions as $option)
                            <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Jenis Pengeluaran -->
                <div class="col-md-6 mb-3">
                    <label for="jenis_pengeluaran" class="form-label">
                        Jenis Pengeluaran <span class="text-danger">*</span>
                    </label>
                    <select wire:model="jenis_pengeluaran"
                        class="form-select @error('jenis_pengeluaran') is-invalid @enderror" id="jenis_pengeluaran">
                        <option value="">Pilih Jenis Pengeluaran</option>
                        @foreach ($jenisPengeluaran as $jenis)
                            <option value="{{ $jenis }}">
                                {{ $jenis === 'pembelian_akun' ? 'Pembelian Akun' : 'Lainnya' }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_pengeluaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- PIC Pembeli -->
                <div class="col-md-6 mb-3" x-show="jenis_pengeluaran === 'pembelian_akun'" x-transition>
                    <label for="pic_pembeli_id" class="form-label">
                        PIC Pembeli <span class="text-danger">*</span>
                    </label>
                    <select wire:model="pic_pembeli_id"
                        class="form-select @error('pic_pembeli_id') is-invalid @enderror" id="pic_pembeli_id">
                        <option value="">Pilih PIC Pembeli</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('pic_pembeli_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="mb-3">
                <label for="deskripsi" class="form-label">
                    Deskripsi <span class="text-danger">*</span>
                </label>
                <textarea wire:model="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi"
                    rows="4" placeholder="Masukkan deskripsi pengeluaran..."></textarea>
                @error('deskripsi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    <span class="text-muted">Maksimal 1000 karakter</span>
                    <span class="float-end text-muted">
                        {{ strlen($deskripsi ?? '') }}/1000
                    </span>
                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.spending.index') }}"class="btn btn-secondary" wire:navigate>
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
