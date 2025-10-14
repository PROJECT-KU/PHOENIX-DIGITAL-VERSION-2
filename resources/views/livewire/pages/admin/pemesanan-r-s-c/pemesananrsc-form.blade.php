<div>
    <form wire:submit="save" x-cloak>

        <!--================== Data kategori ==================-->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-4 fw-bold">
                    <i class="bi bi-folder2-open me-2 text-primary"></i> Data Kategori
                </h5>

                <div class="row">
                    <!-- Nama Camp -->
                    <div class="col-md-6 mb-3">
                        <label for="nama_camp" class="form-label">
                            Nama Kategori <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="nama_camp"
                            class="form-control @error('nama_camp') is-invalid @enderror" id="nama_camp"
                            placeholder="contoh: Scopus Camp Yogyakarta">
                        @error('nama_camp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Batch -->
                    <div class="col-md-6 mb-3">
                        <label for="batch_camp" class="form-label">
                            Batch <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">#</span>
                            <input type="number" wire:model="batch_camp"
                                class="form-control @error('batch_camp') is-invalid @enderror" id="batch_camp"
                                placeholder="contoh: 3">
                            @error('batch_camp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai_camp" class="form-label">
                            Tanggal Mulai <span class="text-danger">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_mulai_camp"
                            class="form-control @error('tanggal_mulai_camp') is-invalid @enderror"
                            id="tanggal_mulai_camp">
                        @error('tanggal_mulai_camp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Berakhir -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_akhir_camp" class="form-label">
                            Tanggal Berakhir <span class="text-danger">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_akhir_camp"
                            class="form-control @error('tanggal_akhir_camp') is-invalid @enderror"
                            id="tanggal_akhir_camp">
                        @error('tanggal_akhir_camp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <!--================== End Data kategori ==================-->

        <!--================== Data akun ==================-->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-4 fw-bold">
                    <i class="bi bi-person-badge me-2 text-success"></i> Data Akun
                </h5>

                <div class="row">
                    <!-- Nama akun -->
                    <div class="mb-3">
                        <label for="akun" class="form-label">Pilih Akun <span class="text-danger">*</span></label>
                        <select wire:model.live="akun" id="akun" class="form-control">
                            <option value="">-- Pilih Akun --</option>
                            @foreach ($akuns as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- username akun -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" wire:model="username" class="form-control" readonly>
                    </div>

                    <!-- password akun -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" wire:model="password" class="form-control" readonly>
                    </div>

                    <!-- link akses -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Link Akses</label>
                        <input type="text" wire:model="link_akses" class="form-control" readonly>
                    </div>

                    <!-- harga satuan -->
                    <div class="col-md-6 mb-3">
                        <label for="harga_satuan" class="form-label">Harga Satuan</label>
                        <input type="text" wire:model="harga_satuan" x-nominal x-currency id="harga_satuan"
                            class="form-control" readonly>
                    </div>

                </div>
            </div>
        </div>
        <!--================== end Data akun ==================-->

        <!--================== Data pembeli ==================-->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-4 fw-bold">
                    <i class="bi bi-person-vcard me-2 text-success"></i> Data Pembeli
                </h5>

                <div class="row">
                    <!-- Nama Pembeli -->
                    <div class="col-md-6 mb-3">
                        <label for="nama_pembeli" class="form-label">
                            Nama Pembeli <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="nama_pembeli"
                            class="form-control @error('nama_pembeli') is-invalid @enderror" id="nama_pembeli"
                            placeholder="contoh: Budi Santoso">
                        @error('nama_pembeli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nomor Telepon -->
                    <div class="col-md-6 mb-3">
                        <label for="telp_pembeli" class="form-label">
                            Nomor Telepon <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="telp_pembeli"
                            class="form-control @error('telp_pembeli') is-invalid @enderror" id="telp_pembeli"
                            placeholder="awali dengan +62">
                        @error('telp_pembeli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jumlah Pesanan -->
                    <div class="col-md-4 mb-3">
                        <label for="jumlah_pemesanan" class="form-label">
                            Jumlah Pesanan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="jumlah_pemesanan" wire:model.live="jumlah_pemesanan"
                                class="form-control @error('jumlah_pemesanan') is-invalid @enderror"
                                placeholder="Masukkan jumlah dalam bulan">
                            <span class="input-group-text">Bulan</span>
                            @error('jumlah_pemesanan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tanggal Pemesanan -->
                    <div class="col-md-4 mb-3">
                        <label for="tanggal_pemesanan" class="form-label">
                            Tanggal Pemesanan <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="tanggal_pemesanan" wire:model.live="tanggal_pemesanan"
                            class="form-control @error('tanggal_pemesanan') is-invalid @enderror">
                        @error('tanggal_pemesanan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Berakhir (Auto calculated) -->
                    <div class="col-md-4 mb-3">
                        <label for="tanggal_berakhir" class="form-label">
                            Tanggal Berakhir
                        </label>
                        <input type="date" id="tanggal_berakhir" wire:model="tanggal_berakhir"
                            class="form-control bg-light" readonly>
                    </div>

                </div>
            </div>
        </div>
        <!--================== end Data pembeli ==================-->

        <!--================== Data lainnya ==================-->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-4 fw-bold">
                    <i class="bi bi-person-vcard me-2 text-success"></i> Data Lainnya
                </h5>

                <div class="row">
                    <!-- Total -->
                    <div class="col-md-12 mb-3">
                        <label for="total" class="form-label">Total</label>
                        {{-- <input type="text" id="total" wire:model="total" x-nominal x-currency
                            class="form-control" readonly disabled> --}}
                        <div class="form-control bg-light">
                            Rp {{ number_format($this->total(), 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- PIC -->
                    <div class="col-md-6 mb-3">
                        <label for="pic" class="form-label">Pilih PIC</label>
                        <select wire:model="pic" id="pic" class="form-control">
                            <option value="">-- Pilih PIC --</option>
                            @foreach ($users as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Pilih Status</label>
                        <select wire:model="status" id="status" class="form-control">
                            <option value="">-- Pilih Status --</option>
                            <option value="habis">Habis</option>
                            <option value="pengganti">Pengganti</option>
                            <option value="perpanjang">Perpanjang</option>
                            <option value="baru">Baru</option>
                        </select>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                            class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Masukkan deskripsi produk"></textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
        <!--================== end Data lainnya ==================-->

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-send me-1"></i>
                {{ $this->mode === 'create' ? 'Tambah Data' : 'Simpan Perubahan' }}
            </button>
        </div>
    </form>
</div>
