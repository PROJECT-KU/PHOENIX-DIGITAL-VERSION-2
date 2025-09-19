<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <form wire:submit.prevent="save">
        <div class="form-group">
            <label for="name" class="form-label">Nama Pelanggan</label>
            <input class="form-control" type="text" wire:model="name" placeholder="nama pelanggan">
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email Pelangan</label>
            <input class="form-control" type="email" wire:model="email" placeholder="email@example.com">
        </div>
        <div class="form-group">
            <label for="phone" class="form-label">Nomor Handphone</label>
            <input class="form-control" type="text" wire:model="phone" placeholder="082*********">
        </div>
        <fieldset class="form-group">
            <label for="statusMember" class="form-label">Status Member</label>
            <select wire:model="statusMember" class="form-select" id="basicSelect">
                <option value="non-active">Non Active</option>
                <option value="active">Active</option>
            </select>
        </fieldset>
        <div class="form-group mt-4">
            <button type="submit"
                class="btn btn-primary">{{ $this->mode === 'create' ? 'Tambah Data' : 'Simpan Perubahan' }}</button>
        </div>
    </form>
</div>
