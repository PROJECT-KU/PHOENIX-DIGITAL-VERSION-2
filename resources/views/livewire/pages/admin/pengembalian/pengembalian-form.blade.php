<div>
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
            <div class="col-md-12 mb-3"
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
                                return rupiah;
                            },
                            parseRaw(v) {
                                return (v || '').toString().replace(/[^0-9]/g, '');
                            }
                        }"
                x-init="$nextTick(() => {
                            // Inisialisasi tampilan dari nilai Livewire
                            $refs.display.value = formatRupiah($wire.nominal);
                        })">
                <label for="nominal_display" class="form-label">
                    Nominal Pengembalian <span class="text-danger">*</span>
                </label>

                <!-- Input tampil ke user (format Rupiah) -->
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                        style="pointer-events: none; z-index: 5;">
                        Rp
                    </span>
                    <input type="text"
                        id="nominal_display"
                        x-ref="display"
                        x-on:focus="$event.target.select()"
                        x-on:input="
                                let raw = parseRaw($event.target.value);
                                $event.target.value = formatRupiah(raw);
                                $wire.set('nominal', raw);
                        "
                        class="form-control @error('nominal') is-invalid @enderror"
                        style="padding-left: 45px;"
                        placeholder="0">
                </div>
                @error('nominal')
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
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span>{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
            </button>
        </div>
    </form>
</div>