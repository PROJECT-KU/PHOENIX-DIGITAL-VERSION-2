<div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

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
                            return rupiah;
                        },
                        parseRaw(v) {
                            return (v || '').toString().replace(/[^0-9]/g, '');
                        }
                    }"
                x-init="$nextTick(() => {
                        // Inisialisasi tampilan jika Livewire sudah punya nilai awal
                        $refs.display.value = formatRupiah($wire.nominal);
                    })">
                <label for="nominal_display" class="form-label">
                    Nominal <span class="text-danger">*</span>
                </label>

                <!-- Input tampil ke user -->
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

                <!-- Pesan error dari Livewire -->
                @error('nominal')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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

        <!-- PIC: hanya untuk pembelian akun -->
        <div class="row" x-show="jenis_pengeluaran === 'pembelian_akun'" x-transition x-cloak>
            <!-- PIC Penginput (selalu dari akun yang login) -->
            <div class="col-md-6 mb-3">
                <label class="form-label">
                    PIC Penginput
                </label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                <div class="form-text text-muted">Otomatis diambil dari akun yang sedang login.</div>
            </div>

            <!-- PIC Pembeli -->
            <div class="col-md-6 mb-3">
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
                Deskripsi
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
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span>{{ $isEdit ? 'Update Data' : 'Simpan Data' }}</span>
            </button>
        </div>
    </form>
</div>