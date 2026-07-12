<form wire:submit.prevent="save">
    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label fw-bold text-secondary">Nama <span class="text-danger">*</span></label>
            <input type="text" wire:model.defer="nama" class="form-control @error('nama') is-invalid @enderror"
                placeholder="Contoh: Budi Santoso">
            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold text-secondary">Peran / Jabatan</label>
            <input type="text" wire:model.defer="peran" class="form-control @error('peran') is-invalid @enderror"
                placeholder="Contoh: Mahasiswa UNAIR / Peneliti">
            @error('peran') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold text-secondary">Rating <span class="text-danger">*</span></label>
            <select wire:model.defer="rating" class="form-control form-select @error('rating') is-invalid @enderror">
                <option value="5">★★★★★ (5)</option>
                <option value="4">★★★★☆ (4)</option>
                <option value="3">★★★☆☆ (3)</option>
                <option value="2">★★☆☆☆ (2)</option>
                <option value="1">★☆☆☆☆ (1)</option>
            </select>
            @error('rating') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold text-secondary">Status <span class="text-danger">*</span></label>
            <select wire:model.defer="status" class="form-control form-select @error('status') is-invalid @enderror">
                <option value="">-- Pilih Status --</option>
                <option value="active">Active</option>
                <option value="non-active">Non-Active</option>
            </select>
            @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-secondary">Foto (opsional)</label>

            <div class="row g-4 align-items-start">
                <div class="col-md-6">
                    <div class="upload-container position-relative">
                        <input type="file" id="fotoInput" wire:model="foto"
                            class="file-input @error('foto') is-invalid @enderror"
                            accept="image/png, image/jpeg, image/jpg">

                        <div class="upload-overlay">
                            <i class="bi bi-cloud-upload fs-2 text-primary"></i>
                            <span class="text-muted fw-bold">Klik untuk unggah foto</span>
                        </div>
                    </div>
                    @error('foto') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    <small class="text-muted mt-2"><i class="bi bi-info-circle me-1"></i> JPG, PNG (Maks 5MB)</small>
                </div>

                <div class="col-md-6">
                    <div class="preview-box border p-2 rounded-4 shadow-sm bg-white d-flex align-items-center justify-content-center"
                        style="min-height: 150px;">
                        @if ($foto && is_object($foto) && !$errors->has('foto'))
                            <img src="{{ $foto->temporaryUrl() }}" class="rounded-3 img-fluid"
                                style="cursor: pointer; max-height: 250px; object-fit: contain;"
                                onclick="showGlossyPreview('{{ $foto->temporaryUrl() }}')" title="Klik untuk memperbesar">
                        @elseif ($existingImage)
                            <img src="{{ asset('storage/img/testimoni/' . $existingImage) }}" class="rounded-3 img-fluid"
                                style="cursor: pointer; max-height: 250px; object-fit: contain;"
                                onclick="showGlossyPreview('{{ asset('storage/img/testimoni/' . $existingImage) }}')"
                                title="Klik untuk memperbesar">
                        @else
                            <div class="text-center text-muted p-3">
                                <i class="bi bi-person-circle fs-1 opacity-50"></i>
                                <p class="small mb-0">Preview Foto</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-secondary mb-0">Pesan Testimoni <span
                    class="text-danger">*</span></label>
            <div class="textarea-wrapper">
                <textarea wire:model.defer="pesan" rows="4"
                    class="form-control description-input @error('pesan') is-invalid @enderror"
                    placeholder="Tuliskan testimoni pelanggan di sini..."></textarea>
            </div>
            @error('pesan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mt-4 pt-3 border-top d-flex gap-2">
        <button type="submit"
            class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
            style="height: 52px;">
            <i class="bi bi-check2-circle me-2 fs-5"></i>
            <span>{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
        </button>
    </div>
</form>

<!--================== SWEET ALERT IMAGE UPLOAD ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ToastGlossy = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: 'rgba(255, 255, 255, 0.85)',
            customClass: {
                popup: 'swal-glossy-toast',
                title: 'swal-toast-title',
                timerProgressBar: 'swal-toast-progress'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        const fotoInput = document.getElementById('fotoInput');

        if (fotoInput) {
            fotoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

                    if (!validImageTypes.includes(file.type)) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.target.value = '';
                        ToastGlossy.fire({
                            icon: 'error',
                            title: 'Format tidak didukung!',
                            text: 'Gunakan file gambar JPG atau PNG.'
                        });
                        return;
                    }

                    const maxSizeInBytes = 5 * 1024 * 1024;
                    if (file.size > maxSizeInBytes) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.target.value = '';
                        ToastGlossy.fire({
                            icon: 'error',
                            title: 'Ukuran Terlalu Besar!',
                            text: 'Maksimal ukuran gambar adalah 5 MB.'
                        });
                        return;
                    }
                }
            }, true);
        }
    });
</script>
<!--================== END SWEET ALERT IMAGE UPLOAD ==================-->
