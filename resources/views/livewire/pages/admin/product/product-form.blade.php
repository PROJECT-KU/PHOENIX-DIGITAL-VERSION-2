<form wire:submit.prevent="save">
    <div class="d-flex flex-column gap-4">

        <!--================== INFORMASI DASAR ==================-->
        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-primary bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="namaAkun" class="form-label fw-semibold text-muted">Nama Akun <span class="text-danger">*</span></label>
                        <input type="text" id="namaAkun" wire:model.defer="nama_akun"
                            class="form-control @error('nama_akun') is-invalid @enderror"
                            placeholder="Masukkan nama akun">
                        @error('nama_akun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="tipeAkun" class="form-label fw-semibold text-muted">Tipe Akun <span class="text-danger">*</span></label>
                        <select id="tipeAkun" wire:model="tipe_akun"
                            class="form-select @error('tipe_akun') is-invalid @enderror">
                            <option value="sharing">Sharing (1 akun banyak orang)</option>
                            <option value="private">Private (1 akun 1 orang)</option>
                        </select>
                        <div class="form-text text-muted" style="font-size:.78rem;">
                            Menentukan cara hitung modal: <b>sharing</b> = total pembelian akun; <b>private</b> = modal satuan &times; jumlah order.
                        </div>
                        @error('tipe_akun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <!--================== END INFORMASI DASAR ==================-->

        <!--================== KATALOG HARGA ==================-->
        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-success bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-success fw-bold"><i class="bi bi-tags me-2"></i>Katalog Harga</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">

                    <div class="col-md-12" x-data>
                        <label for="hargaAwal" class="form-label fw-semibold text-muted">Harga Awal</label>
                        <div class="position-relative">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" id="hargaAwal"
                                value="{{ $harga_awal ? number_format($harga_awal, 0, ',', '.') : '' }}"
                                class="form-control @error('harga_awal') is-invalid @enderror"
                                placeholder="0"
                                @input="
                            let number = $el.value.replace(/[^0-9]/g, '');
                            if(number){
                                $el.value = new Intl.NumberFormat('id-ID').format(number);
                            } else {
                                $el.value = '';
                            }
                            @this.set('harga_awal', number)
                        ">
                        </div>
                        @error('harga_awal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <hr class="my-3">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label fw-semibold text-muted mb-0">Harga per Durasi <span class="text-danger">*</span></label>
                    <button type="button" wire:click="addPrice"
                        class="btn btn-sm btn-success rounded-3 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Tambah Durasi
                    </button>
                </div>
                @error('prices') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                @foreach ($prices as $i => $row)
                <div class="row g-2 align-items-end mb-2" wire:key="price-{{ $i }}">
                    <div class="col-4 col-md-3">
                        @if ($i === 0)<label class="form-label small text-muted mb-1">Durasi</label>@endif
                        <input type="number" min="1" wire:model="prices.{{ $i }}.durasi_value"
                            class="form-control @error('prices.'.$i.'.durasi_value') is-invalid @enderror" placeholder="1">
                    </div>
                    <div class="col-4 col-md-3">
                        @if ($i === 0)<label class="form-label small text-muted mb-1">Satuan</label>@endif
                        <select wire:model="prices.{{ $i }}.durasi_type" class="form-select">
                            <option value="bulan">Bulan</option>
                            <option value="tahun">Tahun</option>
                        </select>
                    </div>
                    <div class="col-md-5" x-data>
                        @if ($i === 0)<label class="form-label small text-muted mb-1">Harga</label>@endif
                        <div class="position-relative">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">Rp</span>
                            <input type="text"
                                value="{{ ($row['harga'] ?? '') !== '' ? number_format((int) $row['harga'], 0, ',', '.') : '' }}"
                                class="form-control @error('prices.'.$i.'.harga') is-invalid @enderror" placeholder="0"
                                @input="let n = $el.value.replace(/[^0-9]/g, ''); $el.value = n ? new Intl.NumberFormat('id-ID').format(n) : ''; @this.set('prices.{{ $i }}.harga', n)">
                        </div>
                    </div>
                    <div class="col-4 col-md-1">
                        @if ($i === 0)<label class="form-label small text-muted mb-1 d-block invisible">.</label>@endif
                        <button type="button" wire:click="removePrice({{ $i }})"
                            class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center"
                            style="height: 38px;" title="Hapus durasi">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
                <div class="form-text text-muted">
                    Tambahkan durasi apa pun (mis. 2, 3, 6 bulan). Untuk akun <b>private</b>, durasi ini juga dipakai mencocokkan modal.
                </div>
            </div>
        </div>
        <!--================== END KATALOG HARGA ==================-->

        <!--================== MEDIA & DESKRIPSI ==================-->
        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-info bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-info fw-bold"><i class="bi bi-images me-2"></i>Media & Deskripsi</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label fw-bold text-secondary">Gambar Produk</label>
                        <div class="row g-4 align-items-start">
                            <div class="col-md-6">
                                <div class="upload-container position-relative">
                                    <input type="file" id="gambarInput" wire:model="image"
                                        class="file-input @error('image') is-invalid @enderror"
                                        accept="image/png, image/jpeg, image/jpg">
                                    <div class="upload-overlay">
                                        <i class="bi bi-cloud-upload fs-2 text-primary"></i>
                                        <span class="text-muted fw-bold">Klik untuk unggah gambar</span>
                                    </div>
                                </div>
                                @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted mt-2"><i class="bi bi-info-circle me-1"></i> JPG, PNG (Maks 5MB)</small>
                            </div>

                            <div class="col-md-6">
                                <div class="preview-box border p-2 rounded-4 shadow-sm bg-white d-flex align-items-center justify-content-center" style="min-height: 150px;">
                                    @if ($image && is_object($image) && !$errors->has('image'))
                                    <img src="{{ $image->temporaryUrl() }}"
                                        class="rounded-3 img-fluid"
                                        style="cursor: pointer; max-height: 250px; object-fit: contain;"
                                        onclick="showGlossyPreview('{{ $image->temporaryUrl() }}')"
                                        title="Klik untuk memperbesar">
                                    @elseif ($existingImage)
                                    <img src="{{ asset('storage/img/Product/' . $existingImage) }}"
                                        class="rounded-3 img-fluid"
                                        style="cursor: pointer; max-height: 250px; object-fit: contain;"
                                        onclick="showGlossyPreview('{{ asset('storage/img/Product/' . $existingImage) }}')"
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
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
        <!--================== END MEDIA & DESKRIPSI ==================-->

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
<script>
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

        // MENGUBAH PENCARIAN ELEMEN MENGGUNAKAN NAMA CLASS (.file-input)
        const gambarInput = document.querySelector('.file-input');

        if (gambarInput) {
            gambarInput.addEventListener('change', function(e) {
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