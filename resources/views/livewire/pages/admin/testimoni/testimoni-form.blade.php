<form wire:submit.prevent="save">
    <style>
        .tf-pick-btn { cursor: pointer; text-align: left; }
        .tf-pick-btn::after {
            content: "\F282"; font-family: "bootstrap-icons"; float: right; color: #94a3b8; font-size: .8rem;
        }
        .tf-pick-list { max-height: 320px; overflow-y: auto; text-align: left; }
        .tf-pick-item {
            display: block; width: 100%; text-align: left; border: 1px solid #eef0f7; background: #fff;
            border-radius: 12px; padding: 10px 12px; margin-bottom: 6px; cursor: pointer; transition: .12s;
        }
        .tf-pick-item:hover { border-color: #c7d2fe; background: #f7f8ff; }
        .tf-pick-name { font-weight: 700; color: #1e293b; font-size: .92rem; }
        .tf-pick-sub { color: #64748b; font-size: .76rem; }
        .tf-pick-empty { color: #94a3b8; font-size: .88rem; padding: 18px; text-align: center; }
    </style>

    <div class="row g-4">
        {{-- Tautan ke pelanggan: sumber label "Pembeli Asli" di homepage.
             Nomor diambil dari data pelanggan, TIDAK diketik admin — salah satu
             digit saja, tautannya meleset & labelnya diam-diam tidak muncul. --}}
        <div class="col-12">
            <label class="form-label fw-bold text-secondary">
                Tautkan ke Pelanggan <span class="text-muted fw-normal" style="font-size:.8rem;">(opsional)</span>
            </label>

            <button type="button" onclick="tfPelangganPicker(this)"
                class="form-control form-select tf-pick-btn @error('customer_id') is-invalid @enderror">
                {{ $pelangganTerpilih?->nama ?? 'Pilih pelanggan — atau biarkan kosong' }}
            </button>
            @error('customer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

            @if ($pelangganTerpilih)
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                    <span class="badge bg-success-subtle text-success border border-success rounded-pill d-inline-flex align-items-center gap-1"
                        style="font-size:.72rem; line-height:1;">
                        <i class="bi bi-bag-check-fill"></i>Sudah belanja {{ $pelangganTerpilih->belanja_selesai_count }} kali
                    </span>
                    @if ($pelangganTerpilih->status_member === 'active')
                        <span class="badge bg-primary-subtle text-primary border border-primary rounded-pill d-inline-flex align-items-center gap-1"
                            style="font-size:.72rem; line-height:1;">
                            <i class="bi bi-star-fill"></i>Sudah member
                        </span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill d-inline-flex align-items-center gap-1"
                            style="font-size:.72rem; line-height:1;"
                            title="Otomatis jadi member begitu testimoni ini berstatus Aktif">
                            <i class="bi bi-hourglass-split"></i>Belum member
                        </span>
                    @endif
                    <span class="text-muted" style="font-size:.76rem;">
                        <i class="bi bi-whatsapp me-1" style="vertical-align:-0.125em;"></i>{{ $no_hp }}
                    </span>
                    <button type="button" wire:click="lepasPelanggan"
                        class="btn btn-sm btn-light-danger rounded-pill px-3 d-inline-flex align-items-center gap-1"
                        style="line-height:1; font-size:.74rem;">
                        <i class="bi bi-x-lg"></i>Lepas
                    </button>
                </div>
                <small class="text-muted d-block mt-1">
                    <i class="bi bi-info-circle me-1" style="vertical-align:-0.125em;"></i>Testimoni ini akan tampil dengan label
                    <b>Pembeli Asli</b> di halaman depan.
                </small>
            @else
                <small class="text-muted d-block mt-1">
                    <i class="bi bi-info-circle me-1" style="vertical-align:-0.125em;"></i>Kosongkan bila testimoni ini bukan dari
                    pelanggan terdaftar. Hanya pelanggan dengan pesanan <b>Selesai</b> yang bisa dipilih.
                </small>
            @endif
        </div>

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

<!--================== PICKER PELANGGAN (Swal glossy, seragam dgn Pengeluaran) ==================-->
@php
    // Disiapkan di sini, bukan di dalam @json(...): direktif Blade memotong
    // argumennya dgn mencocokkan kurung, jadi array multi-baris bikin parse error.
    $tfPelangganJs = $daftarPelanggan->map(fn ($c) => [
        'id' => $c->id,
        'nama' => $c->nama,
        'no_hp' => $c->no_hp,
        'belanja' => $c->belanja_selesai_count,
        'member' => $c->status_member === 'active',
    ])->values();
@endphp
<script>
    // Daftar pelanggan yg berhak label "Pembeli Asli" — hanya yg punya pesanan Selesai.
    window.__tfPelanggan = @json($tfPelangganJs);

    window.tfPelangganPicker = function (btn) {
        if (typeof Swal === 'undefined') return;
        const el = btn.closest('[wire\\:id]');
        if (!el) return;
        const cid = el.getAttribute('wire:id');
        const items = window.__tfPelanggan || [];

        const rows = items.length
            ? items.map(function (it) {
                const lencana = it.member
                    ? '<span class="badge bg-primary-subtle text-primary border border-primary rounded-pill" style="font-size:.64rem;">member</span>'
                    : '';
                return '<button type="button" class="tf-pick-item" data-id="' + it.id + '" ' +
                    'data-search="' + ((it.nama || '') + ' ' + (it.no_hp || '')).toLowerCase() + '">' +
                    '<span class="tf-pick-name">' + it.nama + ' ' + lencana + '</span>' +
                    '<span class="tf-pick-sub">' + it.no_hp + ' &middot; sudah belanja ' + it.belanja + ' kali</span>' +
                    '</button>';
            }).join('')
            : '<div class="tf-pick-empty">Belum ada pelanggan dengan pesanan Selesai.</div>';

        Swal.fire({
            title: 'Pilih Pelanggan',
            html: '<input id="tfPickSearch" class="form-control mb-2" placeholder="Ketik nama atau nomor...">' +
                '<div id="tfPickList" class="tf-pick-list">' + rows + '</div>',
            background: 'rgba(255, 255, 255, 0.92)',
            backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
            buttonsStyling: false,
            showConfirmButton: false,
            showCloseButton: true,
            width: 480,
            padding: '1.25rem',
            didOpen: function () {
                const search = document.getElementById('tfPickSearch');
                const listEl = document.getElementById('tfPickList');
                if (search) {
                    search.addEventListener('input', function () {
                        const q = search.value.toLowerCase();
                        listEl.querySelectorAll('.tf-pick-item').forEach(function (b) {
                            b.style.display = b.dataset.search.includes(q) ? '' : 'none';
                        });
                    });
                    setTimeout(function () { search.focus(); }, 100);
                }
                listEl.querySelectorAll('.tf-pick-item').forEach(function (b) {
                    b.addEventListener('click', function () {
                        // set() memicu updatedCustomerId() -> nama & no_hp terisi sendiri
                        if (window.Livewire) window.Livewire.find(cid).set('customer_id', b.dataset.id);
                        Swal.close();
                    });
                });
            }
        });
    };
</script>
<!--================== END PICKER PELANGGAN ==================-->
