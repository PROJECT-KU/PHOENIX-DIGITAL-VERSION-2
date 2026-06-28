<div>
    <style>
        .ebook-preview {
            min-height: 158px;
            height: 100%;
            border-radius: 16px;
            border: 1.5px dashed #d7dbf5;
            background: linear-gradient(135deg, #fbfbff, #f5f6ff);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: .35rem;
            padding: 1.1rem;
            transition: all .25s ease;
        }

        .ebook-preview.is-ready {
            border-style: solid;
            border-color: rgba(16, 185, 129, 0.35);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.04));
        }

        .ebook-preview.is-saved {
            border-style: solid;
            border-color: rgba(108, 99, 255, 0.30);
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.07), rgba(78, 70, 229, 0.04));
        }

        .ebook-preview-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            background: #fff;
            box-shadow: 0 6px 16px rgba(78, 70, 229, 0.12);
            color: #6c63ff;
            margin: 0 auto;
        }

        .ebook-preview-icon i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            width: 100%;
            height: 100%;
        }

        .ebook-preview-icon i.bi::before {
            display: block;
            line-height: 1;
        }

        .ebook-preview.is-ready .ebook-preview-icon {
            color: #059669;
        }

        .ebook-preview.is-empty .ebook-preview-icon {
            color: #b6bcd4;
            box-shadow: none;
            background: #eef0f7;
        }

        .ebook-preview-name {
            font-weight: 700;
            font-size: .9rem;
            color: #1e293b;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ebook-preview-badge {
            font-size: .8rem;
            font-weight: 600;
        }
    </style>

    <form wire:submit.prevent="save">
        <div class="row g-4">
            <div class="col-md-8">
                <label class="form-label fw-bold text-secondary">Judul Ebook <span class="text-danger">*</span></label>
                <input type="text" wire:model.defer="judul"
                    class="form-control @error('judul') is-invalid @enderror"
                    placeholder="Contoh: Ebook Panduan AI, Ebook Panduan Scopus">
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-secondary">Status <span class="text-danger">*</span></label>
                <select wire:model.defer="status" class="form-control form-select @error('status') is-invalid @enderror">
                    <option value="active">Active</option>
                    <option value="non-active">Non-Active</option>
                </select>
                @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-bold text-secondary">
                    File Ebook @if ($mode === 'create') <span class="text-danger">*</span> @endif
                </label>

                <div class="row g-4 align-items-start">
                    <div class="{{ $mode === 'create' ? 'col-12' : 'col-md-6' }}">
                        <div class="upload-container position-relative">
                            <input type="file" id="ebookFileInput" wire:model.live="file"
                                class="file-input @error('file') is-invalid @enderror"
                                accept="application/pdf,.pdf">

                            <div class="upload-overlay">
                                @if ($file && is_object($file) && !$errors->has('file'))
                                <i class="bi bi-file-earmark-check-fill fs-2 text-success mb-2"
                                    id="ebookOverlayIcon"></i>
                                <span class="fw-bold text-success" id="ebookOverlayText"
                                    style="max-width: 90%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $file->getClientOriginalName() }}
                                </span>
                                @else
                                <i class="bi bi-cloud-upload fs-2 text-primary mb-2" id="ebookOverlayIcon"></i>
                                <span class="text-muted fw-bold" id="ebookOverlayText">Klik untuk unggah ebook (PDF)</span>
                                @endif
                            </div>
                        </div>
                        <div wire:loading wire:target="file" class="text-primary mt-2" style="font-size:.85rem;">
                            <span class="spinner-border spinner-border-sm"></span> Mengunggah...
                        </div>
                        @error('file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle me-1"></i> Hanya PDF (Maks
                            2MB) — akan dibuka view-only untuk pelanggan</small>
                    </div>

                    @if ($mode !== 'create')
                    <div class="col-md-6">
                        @php
                        $pExt = null;
                        if ($file && is_object($file) && !$errors->has('file')) {
                        $pExt = strtolower($file->getClientOriginalExtension());
                        } elseif ($existingFile) {
                        $pExt = strtolower(pathinfo($existingFile, PATHINFO_EXTENSION));
                        }
                        $iconMap = [
                        'pdf' => ['bi-file-earmark-pdf-fill', '#dc2626'],
                        'doc' => ['bi-file-earmark-word-fill', '#2563eb'],
                        'docx' => ['bi-file-earmark-word-fill', '#2563eb'],
                        'zip' => ['bi-file-earmark-zip-fill', '#d97706'],
                        'epub' => ['bi-file-earmark-richtext-fill', '#7c3aed'],
                        'png' => ['bi-file-earmark-image-fill', '#059669'],
                        'jpg' => ['bi-file-earmark-image-fill', '#059669'],
                        'jpeg' => ['bi-file-earmark-image-fill', '#059669'],
                        ];
                        $pIcon = $iconMap[$pExt][0] ?? 'bi-file-earmark-text-fill';
                        $pColor = $iconMap[$pExt][1] ?? '#6c63ff';
                        @endphp

                        @if ($file && is_object($file) && !$errors->has('file'))
                        <div class="ebook-preview is-ready text-center">
                            <div class="ebook-preview-icon d-flex justify-content-center align-items-center"><i class="bi {{ $pIcon }}" style="color: {{ $pColor }};"></i></div>
                            <div class="ebook-preview-name">{{ $file->getClientOriginalName() }}</div>
                            <span class="ebook-preview-badge text-success"><i class="bi bi-check-circle-fill"></i> Siap diunggah</span>
                        </div>
                        @elseif ($existingFile)
                        <div class="ebook-preview is-saved text-center">
                            <div class="ebook-preview-icon d-flex justify-content-center align-items-center"><i class="bi {{ $pIcon }}" style="color: {{ $pColor }};"></i></div>
                            <div class="ebook-preview-name">{{ strtoupper($pExt) }} tersimpan</div>
                            <a href="{{ route('admin.ebook.download', $ebook) }}" target="_blank"
                                class="btn btn-sm btn-outline-success rounded-pill px-3 mt-2 d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-download me-2"></i> <span>Unduh file saat ini</span>
                            </a>
                        </div>
                        @else
                        <div class="ebook-preview is-empty text-center">
                            <div class="ebook-preview-icon d-flex justify-content-center align-items-center"><i class="bi bi-file-earmark"></i></div>
                            <div class="ebook-preview-name text-muted">Preview Ebook</div>
                            <small class="text-muted">Belum ada file dipilih</small>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold text-secondary">Deskripsi</label>
                <textarea wire:model.defer="deskripsi" rows="4"
                    class="form-control @error('deskripsi') is-invalid @enderror"
                    placeholder="Keterangan singkat tentang ebook ini (opsional)"></textarea>
                @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;" wire:loading.attr="disabled" wire:target="save,file">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span>{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
            </button>
        </div>
    </form>

    <!--================== SWEET ALERT EBOOK UPLOAD ==================-->
    <script>
        // Pasang sekali saja di level document agar tetap jalan walau halaman dibuka via wire:navigate (SPA).
        if (!window.__ebookUploadBound) {
            window.__ebookUploadBound = true;

            // Popup glossy tengah, seragam dengan SweetAlert lain di aplikasi
            const ebookGlossyError = (title, text) => {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    title: title,
                    html: text,
                    icon: 'error',
                    background: 'rgba(255, 255, 255, 0.9)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    confirmButtonText: 'Mengerti',
                    customClass: {
                        popup: 'swal-glossy-popup rounded-4 shadow-lg border-0',
                        confirmButton: 'btn-glossy-confirm',
                        title: 'fw-bold'
                    },
                    buttonsStyling: false
                });
            };

            const allowedExt = ['pdf'];
            const maxSize = 2 * 1024 * 1024; // 2MB (mengikuti upload_max_filesize PHP)

            // Tampilkan nama file langsung di area upload agar admin tahu file sudah dipilih
            const showSelectedName = (name) => {
                const txt = document.getElementById('ebookOverlayText');
                const ico = document.getElementById('ebookOverlayIcon');
                if (txt) txt.innerHTML = name ?
                    '<span class="text-success">' + name + '</span>' :
                    'Klik untuk unggah ebook';
                if (ico) ico.className = name ?
                    'bi bi-file-earmark-check-fill fs-2 text-success' :
                    'bi bi-cloud-upload fs-2 text-primary';
            };

            // Validasi sebelum upload — delegasi di document (capture) khusus input ebook
            document.addEventListener('change', function(e) {
                if (!e.target || e.target.id !== 'ebookFileInput') return;

                const file = e.target.files[0];
                if (!file) {
                    showSelectedName(null);
                    return;
                }

                const ext = file.name.split('.').pop().toLowerCase();

                if (!allowedExt.includes(ext)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.target.value = '';
                    showSelectedName(null);
                    ebookGlossyError('Harus PDF',
                        'File <b>.' + ext + '</b> tidak bisa diunggah.<br>Ebook <b>hanya boleh format PDF</b> agar bisa dibuka view-only.');
                    return;
                }

                if (file.size > maxSize) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.target.value = '';
                    showSelectedName(null);
                    const mb = (file.size / 1024 / 1024).toFixed(2);
                    ebookGlossyError('Ukuran File Terlalu Besar',
                        'File Anda berukuran <b>' + mb + ' MB</b>, melebihi batas maksimal <b>2 MB</b>.<br>' +
                        'Silakan kompres dulu file-nya, lalu unggah ulang.');
                    return;
                }

                // Valid → langsung tampilkan nama file (sebelum proses upload selesai)
                showSelectedName(file.name);
            }, true);

            // Kegagalan upload dari server (mis. melebihi batas PHP / koneksi putus)
            document.addEventListener('livewire-upload-error', function(e) {
                if (!e.target || e.target.id !== 'ebookFileInput') return;
                ebookGlossyError('Gagal Mengunggah File',
                    'File gagal diunggah karena <b>ukuran melebihi batas server (maks 2 MB)</b> atau koneksi terputus.<br>' +
                    'Kompres file lalu coba lagi.');
            }, true);
        }
    </script>
    <!--================== END SWEET ALERT EBOOK UPLOAD ==================-->
</div>