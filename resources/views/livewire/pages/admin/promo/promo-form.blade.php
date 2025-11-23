<div>
    <form wire:submit.prevent="save" class="p-3">
        <div class="row g-3">
            <div class="mb-3">
                <label class="form-label">Nama Promo</label>
                <input type="text" wire:model="nama_promo" class="form-control"
                    placeholder="Contoh: Promo Akhir Tahun">
                @error('nama_promo') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="diskon_rupiah" class="form-label">Harga Awal</label>
                    <input type="text" id="diskon_rupiah"
                        value="{{ $diskon_rupiah ? 'Rp ' . number_format($diskon_rupiah, 0, ',', '.') : '' }}"
                        class="form-control @error('diskon_rupiah') is-invalid @enderror"
                        placeholder="Rp 0"
                        @input="
                            let number = $el.value.replace(/[^0-9]/g, '');
                            if(number){
                                $el.value = new Intl.NumberFormat('id-ID', {
                                    style: 'currency', currency: 'IDR',
                                    minimumFractionDigits: 0, maximumFractionDigits: 0
                                }).format(number);
                            } else {
                                $el.value = '';
                            }
                            @this.set('diskon_rupiah', number)
                        ">
                    @error('diskon_rupiah')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Diskon Persen (%)</label>
                    <input type="text"
                        id="diskon_persen"
                        class="form-control @error('diskon_persen') is-invalid @enderror"
                        placeholder="0%"
                        x-data
                        x-init="
                            let val = $wire.get('diskon_persen');
                            if (val) {
                                val = parseFloat(val);   // ubah 20.00 → 20
                                $el.value = val + '%';
                            }
                        "
                        x-on:input="
                            let number = $el.value.replace(/[^0-9]/g, '');
                            if(number){
                                $el.value = number + '%';
                            } else {
                                $el.value = '';
                            }
                            $wire.set('diskon_persen', number);
                        "
                    >
                    @error('diskon_persen')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

        <!-- Tombol -->
        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>
                {{ $this->mode === 'create' ? 'Tambah Promo' : 'Simpan Perubahan' }}
            </button>
        </div>
    </form>
</div>