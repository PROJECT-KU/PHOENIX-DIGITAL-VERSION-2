{{-- <div>
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

            <!-- Upload Gambar -->
            <div class="col-md-6">
                <label for="image" class="form-label">Gambar Produk</label>
                <input type="file" id="image" wire:model="image"
                    class="form-control @error('image') is-invalid @enderror">
                @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if ($image && !$errors->has('image'))
                    <div class="mt-2">
                        <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="img-thumbnail" width="120">
                    </div>
                @endif
            </div>

            <!-- Harga Perbulan -->
            <div class="col-md-6">
                <label for="hargaPerbulan" class="form-label">Harga / Bulan</label>
                <input type="text" id="hargaPerbulan" wire:model.defer="harga_perbulan"
                    class="form-control rupiah @error('harga_perbulan') is-invalid @enderror"
                    placeholder="Rp 0">
                @error('harga_perbulan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Harga 5 Bulan -->
            <div class="col-md-6">
                <label for="harga5Bulan" class="form-label">Harga / 5 Bulan</label>
                <input type="text" id="harga5Bulan" wire:model.defer="harga_5_perbulan"
                    class="form-control rupiah @error('harga_5_perbulan') is-invalid @enderror"
                    placeholder="Rp 0">
                @error('harga_5_perbulan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Harga 10 Bulan -->
            <div class="col-md-6">
                <label for="harga10Bulan" class="form-label">Harga / 10 Bulan</label>
                <input type="text" id="harga10Bulan" wire:model.defer="harga_10_perbulan"
                    class="form-control rupiah @error('harga_10_perbulan') is-invalid @enderror"
                    placeholder="Rp 0">
                @error('harga_10_perbulan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Harga Pertahun -->
            <div class="col-md-6">
                <label for="hargaPertahun" class="form-label">Harga / Tahun</label>
                <input type="text" id="hargaPertahun" wire:model.defer="harga_pertahun"
                    class="form-control rupiah @error('harga_pertahun') is-invalid @enderror"
                    placeholder="Rp 0">
                @error('harga_pertahun')
                <div class="invalid-feedback">{{ $message }}</div>
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
                {{ $this->mode === 'create' ? 'Tambah Produk' : 'Simpan Perubahan' }}
            </button>
        </div>
    </form>

</div> --}}

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

            <!-- Upload Gambar -->
            <div class="col-md-6">
                <label for="image" class="form-label">Gambar Produk</label>
                <input type="file" id="image" wire:model="image"
                    class="form-control @error('image') is-invalid @enderror">
                @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if ($image && !$errors->has('image'))
                    <div class="mt-2">
                        @if (is_string($image))
                            {{-- Jika edit & image dari DB --}}
                            <img src="{{ asset('storage/' . $image) }}" alt="Preview" class="img-thumbnail" width="120">
                        @else
                            {{-- Jika upload baru --}}
                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="img-thumbnail" width="120">
                        @endif
                    </div>
                @endif
            </div>

            <!-- Harga Perbulan -->
            <div class="col-md-6" x-data>
                <label for="hargaPerbulan" class="form-label">Harga / Bulan</label>
                <input type="text" id="hargaPerbulan"
                    value="{{ $harga_perbulan ? 'Rp ' . number_format($harga_perbulan, 0, ',', '.') : '' }}"
                    class="form-control @error('harga_perbulan') is-invalid @enderror"
                    placeholder="Rp 0"
                    @input="
                        let number = $el.value.replace(/[^0-9]/g, '');
                        if(number){
                            $el.value = new Intl.NumberFormat('id-ID', {
                                style: 'currency', currency: 'IDR',
                                minimumFractionDigits: 0, maximumFractionDigits: 0
                            }).format(number);
                        } else {
                            $el.value = '';
                        }
                        @this.set('harga_perbulan', number)
                    ">
                @error('harga_perbulan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Harga 5 Bulan -->
            <div class="col-md-6" x-data>
                <label for="harga5Bulan" class="form-label">Harga / 5 Bulan</label>
                <input type="text" id="harga5Bulan"
                    value="{{ $harga_5_perbulan ? 'Rp ' . number_format($harga_5_perbulan, 0, ',', '.') : '' }}"
                    class="form-control @error('harga_5_perbulan') is-invalid @enderror"
                    placeholder="Rp 0"
                    @input="
                        let number = $el.value.replace(/[^0-9]/g, '');
                        if(number){
                            $el.value = new Intl.NumberFormat('id-ID', {
                                style: 'currency', currency: 'IDR',
                                minimumFractionDigits: 0, maximumFractionDigits: 0
                            }).format(number);
                        } else {
                            $el.value = '';
                        }
                        @this.set('harga_5_perbulan', number)
                    ">
                @error('harga_5_perbulan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Harga 10 Bulan -->
            <div class="col-md-6" x-data>
                <label for="harga10Bulan" class="form-label">Harga / 10 Bulan</label>
                <input type="text" id="harga10Bulan"
                    value="{{ $harga_10_perbulan ? 'Rp ' . number_format($harga_10_perbulan, 0, ',', '.') : '' }}"
                    class="form-control @error('harga_10_perbulan') is-invalid @enderror"
                    placeholder="Rp 0"
                    @input="
                        let number = $el.value.replace(/[^0-9]/g, '');
                        if(number){
                            $el.value = new Intl.NumberFormat('id-ID', {
                                style: 'currency', currency: 'IDR',
                                minimumFractionDigits: 0, maximumFractionDigits: 0
                            }).format(number);
                        } else {
                            $el.value = '';
                        }
                        @this.set('harga_10_perbulan', number)
                    ">
                @error('harga_10_perbulan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Harga Pertahun -->
            <div class="col-md-6" x-data>
                <label for="hargaPertahun" class="form-label">Harga / Tahun</label>
                <input type="text" id="hargaPertahun"
                    value="{{ $harga_pertahun ? 'Rp ' . number_format($harga_pertahun, 0, ',', '.') : '' }}"
                    class="form-control @error('harga_pertahun') is-invalid @enderror"
                    placeholder="Rp 0"
                    @input="
                        let number = $el.value.replace(/[^0-9]/g, '');
                        if(number){
                            $el.value = new Intl.NumberFormat('id-ID', {
                                style: 'currency', currency: 'IDR',
                                minimumFractionDigits: 0, maximumFractionDigits: 0
                            }).format(number);
                        } else {
                            $el.value = '';
                        }
                        @this.set('harga_pertahun', number)
                    ">
                @error('harga_pertahun')
                <div class="invalid-feedback">{{ $message }}</div>
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
                {{ $this->mode === 'create' ? 'Tambah Produk' : 'Simpan Perubahan' }}
            </button>
        </div>
    </form>
</div>

