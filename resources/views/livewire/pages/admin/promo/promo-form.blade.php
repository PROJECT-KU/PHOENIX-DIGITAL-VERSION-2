<div>
    <form wire:submit.prevent="save">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light mb-3">
                        <h5 class="mb-0">Informasi Dasar</h5>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <div class="mb-3">
                                <label class="form-label">Nama Promo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_promo') is-invalid @enderror"
                                    wire:model="nama_promo" placeholder="Contoh: Flash Sale Akhir Tahun">
                                @error('nama_promo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tipe Promo <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipe_promo') is-invalid @enderror"
                                    wire:model.live="tipe_promo">
                                    <option value="flash_sale">Flash Sale</option>
                                    <option value="kode_promo">Kode Promo</option>
                                    <option value="referral_bonus">Referral Bonus</option>
                                </select>
                                @error('tipe_promo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($tipe_promo === 'kode_promo')
                                <div class="mb-3">
                                    <label class="form-label">Kode Promo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode_promo') is-invalid @enderror"
                                        wire:model="kode_promo" placeholder="Contoh: PROMO2024"
                                        style="text-transform: uppercase;">
                                    @error('kode_promo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kode akan otomatis diubah menjadi huruf kapital</small>
                                </div>
                            @endif

                            <div class="col-12 mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" wire:model="deskripsi" rows="3" placeholder="Deskripsi singkat tentang promo"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light mb-3">
                        <h5 class="mb-0"></i> Pengaturan Diskon</h5>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <div class="mb-3">
                                <label class="form-label">Tipe Diskon <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipe_diskon') is-invalid @enderror"
                                    wire:model.live="tipe_diskon">
                                    <option value="persen">Persentase (%)</option>
                                    <option value="nominal">Nominal (Rp)</option>
                                </select>
                                @error('tipe_diskon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="">
                                    @if ($tipe_diskon === 'persen')
                                        <div class="mb-3">
                                            <label class="form-label">Diskon Member (%)</label>
                                            <input type="number" class="form-control" wire:model="diskon_member_persen"
                                                min="0" max="100" step="0.01" placeholder="0">
                                            <small class="text-muted">Contoh: 20 untuk diskon 20%</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Diskon Non-Member (%)</label>
                                            <input type="number" class="form-control"
                                                wire:model="diskon_non_member_persen" min="0" max="100"
                                                step="0.01" placeholder="0">
                                            <small class="text-muted">Contoh: 15 untuk diskon 15%</small>
                                        </div>
                                    @else
                                        <div class="mb-3">
                                            <label class="form-label">Diskon Member (Rp)</label>
                                            <input type="number" class="form-control"
                                                wire:model="diskon_member_nominal" min="0" step="1000"
                                                placeholder="0">
                                            <small class="text-muted">Contoh: 50000 untuk diskon Rp 50.000</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Diskon Non-Member (Rp)</label>
                                            <input type="number" class="form-control"
                                                wire:model="diskon_non_member_nominal" min="0" step="1000"
                                                placeholder="0">
                                            <small class="text-muted">Contoh: 30000 untuk diskon Rp 30.000</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <!-- Syarat & Ketentuan -->
                <div class="card mb-3">
                    <div class="card-header bg-light mb-3">
                        <h5 class="mb-0">Syarat & Ketentuan</h5>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <div class="mb-3">
                                <label class="form-label">Untuk Member <span class="text-danger">*</span></label>
                                <select class="form-select @error('untuk_member') is-invalid @enderror"
                                    wire:model="untuk_member">
                                    <option value="semua">Semua (Member & Non-Member)</option>
                                    <option value="member_only">Member Saja</option>
                                    <option value="non_member_only">Non-Member Saja</option>
                                </select>
                                @error('untuk_member')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Minimum Pembelian (Rp)</label>
                                <input type="number" class="form-control" wire:model="min_pembelian" min="0"
                                    step="1000" placeholder="0">
                                <small class="text-muted">Kosongkan jika tidak ada minimum</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        wire:model="untuk_pembeli_pertama" id="untuk_pembeli_pertama">
                                    <label class="form-check-label" for="untuk_pembeli_pertama">
                                        Untuk Pembeli Pertama Saja
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Periode & Status -->
                <div class="card mb-3">
                    <div class="card-header bg-light mb-3">
                        <h5 class="mb-0">Periode & Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mulai Promo <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('mulai_promo') is-invalid @enderror"
                                        wire:model="mulai_promo">
                                    @error('mulai_promo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Selesai Promo <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('selesai_promo') is-invalid @enderror"
                                        wire:model="selesai_promo">
                                    @error('selesai_promo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Prioritas</label>
                                <input type="number" class="form-control" wire:model="prioritas" min="1"
                                    max="100" placeholder="50">
                                <small class="text-muted">Semakin tinggi angka, semakin prioritas (1-100)</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="is_active"
                                        id="is_active">
                                    <label class="form-check-label" for="is_active">
                                        Status Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Pengaturan Stacking -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light mb-3">
                        <h5 class="mb-0">Pengaturan Stacking Promo</h5>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="can_stack_with_other"
                                        id="can_stack_with_other">
                                    <label class="form-check-label" for="can_stack_with_other">
                                        Dapat Digabung dengan Promo Lain
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        wire:model="can_stack_with_referral" id="can_stack_with_referral">
                                    <label class="form-check-label" for="can_stack_with_referral">
                                        Dapat Digabung dengan Referral
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        wire:model="can_stack_with_points" id="can_stack_with_points">
                                    <label class="form-check-label" for="can_stack_with_points">
                                        Dapat Digabung dengan Poin
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light mb-3">
                        <h5 class="mb-0">Tampilan Badge</h5>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <div class="mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" wire:model="show_on_homepage"
                                        id="show_on_homepage">
                                    <label class="form-check-label" for="show_on_homepage">
                                        Tampilkan di Homepage
                                    </label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Teks Badge</label>
                                    <input type="text" class="form-control" wire:model="badge_text"
                                        placeholder="Contoh: SALE 50%">
                                    <small class="text-muted">Teks yang tampil pada badge produk</small>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Warna Badge</label>
                                    <input type="color" class="form-control form-control-color"
                                        wire:model="badge_color">
                                </div>

                                @if ($badge_text)
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Preview Badge:</label>
                                        <div>
                                            <span class="badge px-3 py-2"
                                                style="background-color: {{ $badge_color }}; color: white;">
                                                {{ $badge_text }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tampilan Badge -->

        <!-- Pilih Produk -->
        <div class="card mb-3">
            <div class="card-header bg-light mb-3">
                <h5 class="mb-0">Pilih Produk</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Pilih produk yang akan mendapatkan promo ini. Kosongkan jika berlaku untuk
                    semua
                    produk.</p>

                <div class="row">
                    @foreach ($allProducts as $product)
                        <div class="col-md-6 col-lg-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model="selectedProducts"
                                    value="{{ $product->id }}" id="product_{{ $product->id }}">
                                <label class="form-check-label" for="product_{{ $product->id }}">
                                    {{ $product->nama_akun }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (count($selectedProducts) > 0)
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i>
                        {{ count($selectedProducts) }} produk dipilih
                    </div>
                @endif
            </div>
        </div>
        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>
                {{ $this->mode === 'create' ? 'Tambah Lowongan' : 'Simpan Perubahan' }}
            </button>
        </div>

    </form>
</div>
