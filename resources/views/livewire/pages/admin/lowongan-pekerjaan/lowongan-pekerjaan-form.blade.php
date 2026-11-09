<div>
    <style>
        .lwg-input {
            border-radius: 12px;
            border: 1.5px solid #e7e9f2;
            padding: 11px 14px;
            transition: 0.18s;
        }

        .lwg-rincian {
            padding: 18px;
            border-radius: 16px;
            background: #f8f9fd;
            border: 1px solid #eceef6;
        }

        .lwg-rincian-judul {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 14px;
        }

        .lwg-rincian-judul i {
            font-size: 1.4rem;
            color: #4e46e5;
        }

        .lwg-rincian-judul strong {
            display: block;
            color: #1f2340;
        }

        .lwg-rincian-judul small {
            color: #6b7193;
        }

        .lwg-input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.18rem rgba(124, 58, 237, 0.12);
        }
    </style>

    <form wire:submit.prevent="save">
        <div class="row g-3">
            <div class="form-group col-md-6">
                <label for="title" class="form-label fw-semibold text-dark">Nama Lowongan Pekerjaan <span class="text-danger">*</span></label>
                <input class="form-control lwg-input @error('title') is-invalid @enderror" type="text" wire:model.live="title" placeholder="Nama lowongan">
                @error('title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="isActive" class="form-label fw-semibold text-dark">Status Lowongan <span class="text-danger">*</span></label>
                <select class="form-select lwg-input @error('isActive') is-invalid @enderror" wire:model.defer='isActive'>
                    <option value="" @selected((string) $this->isActive === '')>Pilih Status</option>
                    <option value="active" @selected((string) $this->isActive === 'active')>Aktif</option>
                    <option value="non-active" @selected((string) $this->isActive === 'non-active')>Tidak Aktif</option>
                </select>
                @error('isActive') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Rincian yang tampil di kartu & halaman detail situs Asthana --}}
        <div class="lwg-rincian mt-4">
            <div class="lwg-rincian-judul">
                <i class="bi bi-card-checklist"></i>
                <div>
                    <strong>Rincian untuk situs</strong>
                    <small>Tampil di kartu lowongan & halaman detail. Yang kosong tidak ditampilkan.</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="form-group col-md-4">
                    <label class="form-label fw-semibold text-dark">Perusahaan <span class="text-danger">*</span></label>
                    <select class="form-select lwg-input @error('unit') is-invalid @enderror" wire:model="unit">
                        @foreach (\App\Models\Lowongan::UNIT as $kunci => $label)
                            <option value="{{ $kunci }}" @selected((string) $this->unit === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('unit') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label fw-semibold text-dark">Jenis Pekerjaan</label>
                    <select class="form-select lwg-input @error('jenis') is-invalid @enderror" wire:model="jenis">
                        <option value="" @selected((string) $this->jenis === '')>— Tidak disebut —</option>
                        @foreach (\App\Models\Lowongan::JENIS as $kunci => $label)
                            <option value="{{ $kunci }}" @selected((string) $this->jenis === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('jenis') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label fw-semibold text-dark">Pola Kerja</label>
                    <select class="form-select lwg-input @error('polaKerja') is-invalid @enderror" wire:model="polaKerja">
                        <option value="" @selected((string) $this->polaKerja === '')>— Tidak disebut —</option>
                        @foreach (\App\Models\Lowongan::POLA_KERJA as $kunci => $label)
                            <option value="{{ $kunci }}" @selected((string) $this->polaKerja === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('polaKerja') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label fw-semibold text-dark">Lokasi</label>
                    <input class="form-control lwg-input @error('lokasi') is-invalid @enderror" type="text" wire:model="lokasi" maxlength="100" placeholder="mis. Yogyakarta">
                    @error('lokasi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label fw-semibold text-dark">Batas Lamaran</label>
                    <input class="form-control lwg-input @error('batasLamaran') is-invalid @enderror" type="date" wire:model="batasLamaran">
                    <small class="text-muted">Lewat dari tanggal ini, lowongan otomatis hilang dari situs.</small>
                    @error('batasLamaran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label fw-semibold text-dark">Jumlah Posisi</label>
                    <input class="form-control lwg-input @error('jumlahPosisi') is-invalid @enderror" type="number" min="1" max="999" wire:model="jumlahPosisi" placeholder="mis. 2">
                    @error('jumlahPosisi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-4">
                    <label class="form-label fw-semibold text-dark">Kisaran Gaji</label>
                    <input class="form-control lwg-input @error('gaji') is-invalid @enderror" type="text" wire:model="gaji" maxlength="100" placeholder="mis. Rp 4–6 juta / bulan">
                    <small class="text-muted">Kosongkan bila tidak ingin ditampilkan.</small>
                    @error('gaji') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-8">
                    <label class="form-label fw-semibold text-dark">Ringkasan Singkat</label>
                    <textarea class="form-control lwg-input @error('ringkasan') is-invalid @enderror" rows="2" wire:model="ringkasan" maxlength="255" placeholder="1–2 kalimat yang tampil di kartu lowongan">{{ $ringkasan }}</textarea>
                    @error('ringkasan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- descriptions form -->
        <div class="form-group mt-3" wire:ignore>
            <label class="form-label fw-semibold text-dark">Deskripsi Lowongan</label>
            <div class="border rounded-3 quill-container" style="height: 150px; overflow: auto;">
                <div id="editor-descriptions"></div>
            </div>
            <input type="hidden" wire:model="descriptions" id="descriptions">
            @error('descriptions')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- requirements form -->
        <div class="form-group mt-3" wire:ignore>
            <label class="form-label fw-semibold text-dark">Persyaratan Lowongan</label>
            <div class="border rounded-3 quill-container" style="height: 150px; overflow: auto;">
                <div id="editor-requirements"></div>
            </div>
            <input type="hidden" wire:model="requirements" id="requirements">
            @error('requirements')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex mt-4">
            <button type="submit"
                class="btn flex-grow-1 d-inline-flex align-items-center justify-content-center text-white rounded-pill shadow-lg"
                style="height: 55px; background: linear-gradient(135deg, #6c63ff, #4e46e5); font-weight: 600; font-size: 1.1rem; border: none;"
                wire:loading.attr="disabled" wire:target="save">
                {{-- Kelas display di dalam, bukan pada elemen wire:loading: d-* !important
                     mengalahkan cara Livewire menyembunyikannya. --}}
                <span wire:loading.remove wire:target="save">
                    <span class="d-inline-flex align-items-center">
                        <i class="bi bi-check2-circle me-2 fs-4"></i>
                        <span>{{ $this->mode === 'create' ? 'Tambah Lowongan' : 'Simpan Perubahan' }}</span>
                    </span>
                </span>
                <span wire:loading wire:target="save">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan…
                </span>
            </button>
        </div>
    </form>
</div>

@push('scripts-head')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush
@script
<script>
    const toolbarOptions = [
        ['bold', 'italic', 'underline'],
        [{
            'header': 1
        }, {
            'header': 2
        }],
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }],
        ['align', {
            'align': 'center'
        }]
    ];

    function initQuillEditor(editorId, inputId) {
        const quill = new Quill(`#${editorId}`, {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            }
        });

        const hiddenInput = document.querySelector(`#${inputId}`);

        quill.on('text-change', function() {
            const html = quill.root.innerHTML;
            hiddenInput.value = html;
            hiddenInput.dispatchEvent(new Event('input'));
        });

        return quill;
    }

    const quilldescriptions = initQuillEditor('editor-descriptions', 'descriptions');
    const quillrequirements = initQuillEditor('editor-requirements', 'requirements');

    Livewire.on('load-quill-content', (data) => {
        if (data[0].descriptions) {
            quilldescriptions.root.innerHTML = data[0].descriptions;
        }
        if (data[0].requirements) {
            quillrequirements.root.innerHTML = data[0].requirements;
        }
    });
</script>
@endscript

@push('styles')
<style>
    .quill-container {
        position: relative;
        height: 250px;
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .quill-container .ql-toolbar {
        position: sticky;
        top: 0;
        z-index: 10;
        background: white;
        border-bottom: 1px solid #ccc;
    }

    .quill-container .ql-container {
        height: calc(100% - 42px);
        /* 42px adalah tinggi toolbar */
        overflow-y: auto;
    }
</style>
@endpush