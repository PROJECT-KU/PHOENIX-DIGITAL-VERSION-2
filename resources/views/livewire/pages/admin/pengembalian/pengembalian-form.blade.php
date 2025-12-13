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
                    <!-- Nama Pengembalian (ambil dari user) -->
                    <div class="col-md-6 mb-3">
                        <label for="user_id" class="form-label">
                            Nama Pengembalian <span class="text-danger">*</span>
                        </label>
                        <select id="user_id"
                                wire:model="user_id"
                                class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">-- Pilih User --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        {{-- <label for="user_id" class="form-label">
                            Nama Pengembalian <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            id="user_id"
                            class="form-control"
                            value="{{ auth()->user()->name }}"
                            readonly> --}}
                    </div>

                    <!-- Tanggal Pengembalian -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_pengembalian" class="form-label">
                            Tanggal Pengembalian <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                                id="tanggal_pengembalian"
                                wire:model="tanggal_pengembalian"
                                class="form-control @error('tanggal_pengembalian') is-invalid @enderror">
                        @error('tanggal_pengembalian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nominal Pengembalian -->
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
                            $refs.display.value = formatRupiah($refs.hidden.value ?? '');
                        })"
                    >
                        <label for="nominal_display" class="form-label">
                            Nominal Pengembalian <span class="text-danger">*</span>
                        </label>

                        <!-- tampil untuk user -->
                        <input type="text"
                            id="nominal_display"
                            x-ref="display"
                            x-on:input="
                                    let raw = parseRaw($event.target.value);
                                    $event.target.value = formatRupiah(raw);
                                    $wire.set('nominal', raw);
                                    $refs.hidden.value = raw;
                            "
                            class="form-control @error('nominal') is-invalid @enderror"
                            placeholder="Rp 0">
                        <!-- hidden untuk Livewire -->
                        <input type="hidden" id="nominal" x-ref="hidden" wire:model.defer="nominal">

                        @error('nominal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select id="status"
                                wire:model="status"
                                class="form-select @error('status') is-invalid @enderror">
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
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea id="deskripsi"
                            wire:model="deskripsi"
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            rows="4"
                            placeholder="Masukkan deskripsi pengembalian..."></textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>
                        {{ $this->mode === 'create' ? 'Tambah Pengembalian' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
