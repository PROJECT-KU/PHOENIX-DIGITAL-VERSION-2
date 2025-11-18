<div>
    <div class="card">
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
                        <label for="user_id" class="form-label">
                            Nama Peminjam <span class="text-danger">*</span>
                        </label>
                        <select 
                            id="user_id" 
                            name="user_id" 
                            wire:model="user_id" 
                            class="form-control @error('user_id') is-invalid @enderror"
                        >
                            <option value="">-- Pilih Peminjam --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')
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

                    <!-- NOMINAL (formatted input + hidden wire model) -->
                    <div class="col-md-6 mb-3"
                        x-data="{
                            formatRupiah(v) {
                                if (!v) return '';
                                let number_string = v.toString().replace(/[^,\d]/g, '');
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
                            },
                            parseRaw(v) {
                                return (v || '').toString().replace(/[^0-9]/g, '');
                            }
                        }"
                        x-init="$nextTick(() => { 
                            // inisialisasi tampilan dari nilai Livewire (hidden.value)
                            $refs.display.value = formatRupiah($refs.hidden.value ?? '');
                        })"
                    >
                        <label for="nominal" class="form-label">Nominal Pinjaman <span class="text-danger">*</span></label>

                        <!-- tampil untuk user (format Rupiah) -->
                        <input type="text"
                            x-ref="display"
                            x-on:input="
                                let raw = parseRaw($event.target.value);
                                // update display (tampil Rp)
                                $event.target.value = formatRupiah(raw);
                                // update Livewire property langsung supaya submit valid
                                $wire.set('nominal', raw);
                                // juga sinkron ke hidden input value agar x-init baca benar
                                $refs.hidden.value = raw;
                            "
                            class="form-control @error('nominal') is-invalid @enderror"
                            placeholder="Rp 0">
                        <!-- hidden untuk Livewire binding (nilai angka murni) -->
                        <input type="hidden" x-ref="hidden" wire:model.defer="nominal">
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
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>
                        {{ $this->mode === 'create' ? 'Tambah peminjaman' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>