<!--================== JS UNTUK SLEECT 2 ==================-->
@assets
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endassets
<!--================== END JS UNTUK SELECT 2 ==================-->

<form wire:submit.prevent="save">
    <div class="d-flex flex-column gap-4">

        <!--================== CSS UNTUK SELECT 2 ==================-->
        <style>
            .select2-container--default .select2-selection--single {
                background-color: #ffffff !important;
                border: 1px solid #dee2e6 !important;
                border-radius: 12px !important;
                /* Lengkungan disamakan dengan input lain */
                min-height: 45px !important;
                /* Tinggi disesuaikan agar tidak kurus */
                padding: 0.375rem 0.75rem !important;
                display: flex;
                align-items: center;
                box-shadow: none !important;
                transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #212529 !important;
                padding-left: 0 !important;
                font-size: 1rem;
                line-height: normal !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__placeholder {
                color: #9ca3af !important;
                /* Warna teks placeholder (abu-abu pudar) */
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 100% !important;
                right: 15px !important;
                top: 0 !important;
                display: flex;
                align-items: center;
            }

            /* Efek Focus saat diklik (meniru form-control bootstrap) */
            .select2-container--default.select2-container--focus .select2-selection--single,
            .select2-container--default.select2-container--open .select2-selection--single {
                border-color: #7c3aed !important;
                box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25) !important;
                /* Shadow ungu/biru */
                outline: 0;
            }

            .select2-dropdown {
                border: 1px solid #dee2e6 !important;
                border-radius: 0.5rem !important;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1) !important;
            }

            .select2-container--default .select2-search--dropdown .select2-search__field {
                border-radius: 0.375rem !important;
                border: 1px solid #ced4da !important;
                padding: 6px 12px;
            }

            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #ffc107 !important;
                /* Warna hover opsi kuning */
                color: #000 !important;
            }
        </style>
        <!--================== CSS UNTUK SELECT 2 ==================-->

        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-primary bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="nama_paket" class="form-label fw-semibold text-muted">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" id="nama_paket" wire:model.defer="nama_paket"
                            class="form-control @error('nama_paket') is-invalid @enderror"
                            placeholder="Masukkan Nama Paket Bundling">
                        @error('nama_paket')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label fw-semibold text-muted">Status <span class="text-danger">*</span></label>
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
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-warning bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-warning fw-bold"><i class="bi bi-box-seam me-2"></i>Daftar Produk Bundling</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">

                    @foreach (['product_1' => 'col-md-4', 'product_2' => 'col-md-4', 'product_3' => 'col-md-4', 'product_4' => 'col-md-6', 'product_5' => 'col-md-6'] as $field => $colClass)
                    <div class="{{ $colClass }}">
                        <label for="{{ $field }}" class="form-label fw-semibold text-muted">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                        <div wire:ignore x-data x-init="
                            let $select = $($el).find('select');
                            $select.select2({
                                placeholder: '-- Pilih Product --',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $($el)
                            });
                            $select.on('change', function () {
                                $wire.set('{{ $field }}', $(this).val(), false);
                            });
                            // Naikkan z-index card saat dropdown dibuka agar tidak
                            // tertimpa card di bawahnya (tiap card punya stacking
                            // context sendiri karena backdrop-filter).
                            $select.on('select2:open', function () {
                                $($el).closest('.card').css('z-index', 1060);
                            });
                            $select.on('select2:close', function () {
                                $($el).closest('.card').css('z-index', '');
                            });
                        ">
                            <select id="{{ $field }}" class="form-select select2-bundling">
                                <option value="">-- Pilih Product --</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected($product->id == $$field)>{{ $product->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
        <div class="card-header bg-success bg-opacity-10 p-3 border-0 rounded-top-4">
            <h5 class="mb-0 text-success fw-bold"><i class="bi bi-tags me-2"></i>Katalog Harga</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="harga_awal" class="form-label fw-semibold text-muted">Harga Awal <span class="text-danger">*</span></label>
                    <input type="text" id="harga_awal" wire:model.defer="harga_awal"
                        class="form-control @error('harga_awal') is-invalid @enderror rupiah"
                        placeholder="Rp 0">
                    @error('harga_awal')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="harga_bundling" class="form-label fw-semibold text-muted">Harga Bundling <span class="text-danger">*</span></label>
                    <input type="text" id="harga_bundling" wire:model.defer="harga_bundling"
                        class="form-control @error('harga_bundling') is-invalid @enderror rupiah"
                        placeholder="Rp 0">
                    @error('harga_bundling')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    @php
    // fallback: $gambar (jika ada), kalau tidak pakai $this->gambar (Livewire), kalau tidak ada jadikan null
    $gambarVar = $gambar ?? $this->gambar ?? null;
    @endphp

    <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
        <div class="card-header bg-info bg-opacity-10 p-3 border-0 rounded-top-4">
            <h5 class="mb-0 text-info fw-bold" style="color: #0dcaf0;"><i class="bi bi-images me-2"></i>Media & Deskripsi</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label fw-bold text-secondary">Gambar Produk Bundling <span class="text-danger">*</span></label>
                    <div class="row g-4 align-items-start">
                        <div class="col-md-6">
                            <div class="upload-container position-relative">
                                <input type="file" id="gambarInput" wire:model="gambar"
                                    class="file-input @error('gambar') is-invalid @enderror"
                                    accept="image/png, image/jpeg, image/jpg">
                                <div class="upload-overlay">
                                    <i class="bi bi-cloud-upload fs-2 text-primary"></i>
                                    <span class="text-muted fw-bold">Klik untuk unggah gambar</span>
                                </div>
                            </div>
                            @error('gambar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            <small class="text-muted mt-2"><i class="bi bi-info-circle me-1"></i> JPG, PNG (Maks 5MB)</small>
                        </div>

                        <div class="col-md-6">
                            <div class="preview-box border p-2 rounded-4 shadow-sm bg-white d-flex align-items-center justify-content-center" style="min-height: 150px;">
                                @if ($gambarVar && is_object($gambarVar) && method_exists($gambarVar, 'temporaryUrl') && !$errors->has('gambar'))
                                <img src="{{ $gambarVar->temporaryUrl() }}"
                                    class="rounded-3 img-fluid"
                                    style="cursor: pointer; max-height: 250px; object-fit: contain;"
                                    onclick="showGlossyPreview('{{ $gambarVar->temporaryUrl() }}')"
                                    title="Klik untuk memperbesar">
                                @elseif (!empty($existingImage))
                                <img src="{{ asset('storage/img/ProductBundlings/' . $existingImage) }}"
                                    class="rounded-3 img-fluid"
                                    style="cursor: pointer; max-height: 250px; object-fit: contain;"
                                    onclick="showGlossyPreview('{{ asset('storage/img/ProductBundlings/' . $existingImage) }}')"
                                    title="Klik untuk memperbesar">
                                @else
                                <div class="text-center text-muted p-3">
                                    <i class="bi bi-image fs-1 opacity-50"></i>
                                    <p class="small mb-0">Preview Gambar</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label for="deskripsi" class="form-label fw-semibold text-muted">Deskripsi</label>
                    <textarea id="deskripsi" wire:model.defer="deskripsi" rows="4"
                        class="form-control @error('deskripsi') is-invalid @enderror"
                        placeholder="Masukkan deskripsi produk..."></textarea>
                    @error('deskripsi')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex mt-2 mb-4">
        <button type="submit"
            class="btn flex-grow-1 d-inline-flex align-items-center justify-content-center text-white rounded-pill shadow-lg"
            style="height: 55px; background: linear-gradient(135deg, #6c63ff, #4e46e5); font-weight: 600; font-size: 1.1rem; border: none; transition: transform 0.2s;">
            <i class="bi bi-check2-circle me-2 fs-4"></i>
            <span>{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
        </button>
    </div>

    </div>
</form>

<!--================== SWEET ALERT IMAGE UPLOAD ==================-->
@push('scripts')
<script>
    // Preview/zoom gambar (sama seperti pada Banners & Product form)
    window.showGlossyPreview = function(imageUrl) {
        Swal.fire({
            imageUrl: imageUrl,
            imageAlt: 'Preview Gambar',
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            padding: '1.25rem',
            background: 'rgba(255, 255, 255, 0.85)',
            backdrop: 'rgba(0, 0, 0, 0.5)',
            customClass: {
                popup: 'rounded-4 shadow-lg border border-white',
                image: 'rounded-3 shadow-sm m-0'
            },
            didOpen: () => {
                const img = Swal.getImage();
                if (img) {
                    img.style.maxHeight = '85vh';
                    img.style.maxWidth = '100%';
                    img.style.objectFit = 'contain';
                }
            }
        });
    };

    // Format Rupiah
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
<!--================== SWEET ALERT IMAGE UPLOAD ==================-->