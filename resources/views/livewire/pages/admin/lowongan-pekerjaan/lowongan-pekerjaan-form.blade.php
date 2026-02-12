<div>
    <form wire:submit.prevent="save">
        <div class="row">
            <div class="form-group col-6">
                <label for="title" class="form-label">Nama Lowongan Pekerjaan</label>
                <input class="form-control" type="text" wire:model="title" placeholder="nama lowongan">
            </div>
            <div class="form-group col-6">
                <label for="isActive" class="form-label">Status Lowongan</label>
                <select class="form-select" wire:model.defer='isActive'>
                    <option value="">Pilih Status</option>
                    <option value="active">Aktif</option>
                    <option value="non-active">Tidak Aktif</option>
                </select>
            </div>
        </div>
        <!-- descriptions form -->
        <div class="form-group" wire:ignore>
            <label class="form-label fw-semibold">Deskripsi Lowongan</label>
            <div class="border rounded quill-container" style="height: 150px; overflow: auto;">
                <div id="editor-descriptions"></div>
            </div>
            <input type="hidden" wire:model="descriptions" id="descriptions">
            @error('descriptions')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <!-- requirements form -->
        <div class="form-group" wire:ignore>
            <label class="form-label fw-semibold">Persyaratan Lowongan</label>
            <div class="border rounded quill-container" style="height: 150px; overflow: auto;">
                <div id="editor-requirements"></div>
            </div>
            <input type="hidden" wire:model="requirements" id="requirements">
            @error('requirements')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="mt-4 text-end">
            <button type="submit" class="btn w-100 btn-primary">
                <i class="bi bi-send me-1"></i>
                {{ $this->mode === 'create' ? 'Tambah Lowongan' : 'Simpan Perubahan' }}
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