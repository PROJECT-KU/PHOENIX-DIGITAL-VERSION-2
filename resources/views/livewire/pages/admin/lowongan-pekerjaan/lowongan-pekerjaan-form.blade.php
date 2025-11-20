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
        <div class="form-group">
            <label for="descriptions" class="form-label">Deskripsi Lowongan</label>
            <textarea class="form-control" name="descriptions" wire:model='descriptions' id="descriptions" cols="30"
                rows="5"></textarea>
        </div>
        <div class="form-group">
            <label for="requirements" class="form-label">Requirement Lowongan</label>
            <textarea class="form-control" name="requirements" wire:model='requirements' id="requirements" cols="30"
                rows="5"></textarea>
        </div>
        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>
                {{ $this->mode === 'create' ? 'Tambah Lowongan' : 'Simpan Perubahan' }}
            </button>
        </div>
    </form>
</div>
{{-- @push('scripts-head')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush --}}
