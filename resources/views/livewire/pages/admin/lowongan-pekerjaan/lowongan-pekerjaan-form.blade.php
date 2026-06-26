<div>
    <style>
        .lwg-input {
            border-radius: 12px;
            border: 1.5px solid #e7e9f2;
            padding: 11px 14px;
            transition: 0.18s;
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
                    <option value="">Pilih Status</option>
                    <option value="active">Aktif</option>
                    <option value="non-active">Tidak Aktif</option>
                </select>
                @error('isActive') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
                <span wire:loading.remove wire:target="save" class="d-inline-flex align-items-center">
                    <i class="bi bi-check2-circle me-2 fs-4"></i>
                    <span>{{ $this->mode === 'create' ? 'Tambah Lowongan' : 'Simpan Perubahan' }}</span>
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