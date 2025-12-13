<div>
    <form wire:submit.prevent="save" class="p-3">
        <div class="row g-3">

            <!-- Nama Paket Bundling dengan Button + -->
            <div class="col-md-6">
                <div class="d-flex align-items-end">
                    <div class="flex-grow-1 me-2">
                        <label for="nama_paket" class="form-label">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" id="nama_paket" wire:model.defer="nama_paket"
                            class="form-control @error('nama_paket') is-invalid @enderror"
                            placeholder="Masukkan Nama Paket Bundling">
                        @error('nama_paket')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Status -->
            <div class="col-md-6">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select id="status" wire:model.defer="status"
                    class="form-select @error('status') is-invalid @enderror">
                    <option value="">-- Pilih Status --</option>
                    <option value="active">Active</option>
                    <option value="non-active">Non-Active</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Grup Product 1, 2 & 3 -->
            <div class="col-md-4">
                <label for="product_1" class="form-label">Product 1</label>
                <select id="product_1" wire:model="product_1" class="form-select">
                    <option value="">-- Pilih Product --</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->nama_akun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="product_2" class="form-label">Product 2</label>
                <select id="product_2" wire:model="product_2" class="form-select">
                    <option value="">-- Pilih Product --</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->nama_akun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="product_3" class="form-label">Product 3</label>
                <select id="product_3" wire:model="product_3" class="form-select me-2">
                    <option value="">-- Pilih Product --</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->nama_akun }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grup Product 4 & 5 -->
            <div class="col-md-6">
                <label for="product_4" class="form-label">Product 4</label>
                <select id="product_4" wire:model="product_4" class="form-select">
                    <option value="">-- Pilih Product --</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->nama_akun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 ">
                <label for="product_5" class="form-label">Product 5</label>
                <select id="product_5" wire:model="product_5" class="form-select me-2"">
                            <option value="">-- Pilih Product --</option>
                            @foreach($products as $product)
                            <option value=" {{ $product->id }}">{{ $product->nama_akun }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Harga Awal -->
            <div class="col-md-6">
                <label for="harga_awal" class="form-label">Harga Awal <span class="text-danger">*</span></label>
                <input type="text" id="harga_awal" wire:model.defer="harga_awal"
                    class="form-control @error('harga_awal') is-invalid @enderror rupiah"
                    placeholder="Masukkan Harga Awal">
                @error('harga_awal')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="harga_bundling" class="form-label">Harga Bundling <span class="text-danger">*</span></label>
                <input type="text" id="harga_bundling" wire:model.defer="harga_bundling"
                    class="form-control @error('harga_bundling') is-invalid @enderror rupiah"
                    placeholder="Masukkan Harga Bundling">
                @error('harga_bundling')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @php
            // fallback: $gambar (jika ada), kalau tidak pakai $this->gambar (Livewire), kalau tidak ada jadikan null
            $gambarVar = $gambar ?? $this->gambar ?? null;
            @endphp

            <!-- Gambar -->
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <label for="gambar" class="form-label">Gambar Produk <span class="text-danger">*</span></label>
                        <input type="file" id="gambar" wire:model="gambar"
                            class="form-control @error('gambar') is-invalid @enderror"
                            accept="image/png,image/jpg,image/jpeg">
                        @error('gambar')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preview -->
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label">Preview</label>
                        </div>

                        @if ($gambarVar && is_object($gambarVar) && method_exists($gambarVar, 'temporaryUrl'))
                        <!-- Preview sementara dari upload Livewire -->
                        <img src="{{ $gambarVar->temporaryUrl() }}" alt="Preview Banner"
                            class="img-thumbnail" style="max-height: 200px;">
                        @elseif (!empty($existingImage))
                        <!-- Gambar lama dari storage -->
                        <img src="{{ asset('storage/img/ProductBundlings/' . $existingImage) }}" alt="Banner Lama"
                            class="img-thumbnail" style="max-height: 200px;">
                        @else
                        <!-- Placeholder ketika belum ada gambar -->
                        <img src="https://via.placeholder.com/200x150?text=Preview+Banner"
                            alt="Preview Banner" class="img-thumbnail" style="max-height: 200px;">
                        @endif
                    </div>
                </div>
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