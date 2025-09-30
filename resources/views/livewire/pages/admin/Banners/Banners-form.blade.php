<div>
    <form wire:submit.prevent="save" class="p-3">
        <div class="row g-3">
            <!-- Judul Banner -->
            <div class="col-md-6">
                <label for="judul" class="form-label">Judul Banner <span class="text-danger">*</span></label>
                <input type="text" id="judul" wire:model.defer="judul"
                    class="form-control @error('judul') is-invalid @enderror"
                    placeholder="Masukkan Judul Banner">
                @error('judul')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>


            <!-- Gambar Banner -->
            <div class="col-md-12">
                <div class="row">
                    <!-- Input -->
                    <div class="col-md-6">
                        <label for="gambar" class="form-label">Gambar Banner <span class="text-danger">*</span></label>
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

                        @if ($gambar && is_object($gambar))
                        <img src="{{ $gambar->temporaryUrl() }}" alt="Preview Banner"
                            class="img-thumbnail" style="max-height: 200px;">
                        @elseif ($existingImage)
                        <img src="{{ asset('storage/img/banners/' . $existingImage) }}" alt="Banner Lama"
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