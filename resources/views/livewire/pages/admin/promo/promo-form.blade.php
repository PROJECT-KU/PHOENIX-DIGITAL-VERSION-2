<div class="container-fluid">
    <form wire:submit.prevent="save">

        <!--================== CARD 1 & 2 ==================-->
        <div class="row">

            <!--================== INFORMASI DASAR ==================-->
            <div class="col-md-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-primary bg-opacity-10 p-3 border-0">
                        <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Nama Promo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_promo') is-invalid @enderror"
                                wire:model="nama_promo" placeholder="Contoh: Flash Sale Akhir Tahun" required>
                            @error('nama_promo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Tipe Promo <span class="text-danger">*</span></label>
                            <select class="form-control form-select @error('tipe_promo') is-invalid @enderror"
                                wire:model.live="tipe_promo" required>
                                <option value="auto_promo">Auto Promo</option>
                                <option value="flash_sale">Flash Sale</option>
                                <option value="kode_promo">Kode Promo</option>
                                <option value="referral_bonus">Referral Bonus</option>
                            </select>
                        </div>

                        @if ($tipe_promo === 'kode_promo')
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Kode Promo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase"
                                wire:model="kode_promo" placeholder="CONTOH2024">
                        </div>
                        @endif

                        <div class="mb-0">
                            <label class="form-label fw-bold text-secondary">Deskripsi</label>
                            <textarea class="form-control" style="height: auto;" wire:model="deskripsi" rows="4" placeholder="Deskripsi singkat..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <!--================== END INFORMASI DASAR ==================-->

            <!--================== PENGATURAN DISKON ==================-->
            <div class="col-md-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-success bg-opacity-10 p-3 border-0">
                        <h5 class="mb-0 text-success fw-bold"><i class="bi bi-tag-fill me-2"></i>Pengaturan Diskon</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Tipe Diskon <span class="text-danger">*</span></label>
                            <select class="form-control form-select" wire:model.live="tipe_diskon" required>
                                <option value="persen">Persentase (%)</option>
                                <option value="nominal">Nominal (Rp)</option>
                            </select>
                        </div>

                        <div class="row">
                            @if ($tipe_diskon === 'persen')
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold text-secondary">Diskon Member</label>
                                <div class="position-relative">
                                    <input type="text"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3)"
                                        class="form-control shadow-none"
                                        wire:model="diskon_member_persen"
                                        placeholder="0"
                                        style="background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 12px; padding-right: 35px; height: 100%;">

                                    <span class="position-absolute top-50 end-0 translate-middle-y text-secondary fw-bold pe-3"
                                        style="pointer-events: none; z-index: 5;">
                                        %
                                    </span>
                                </div>
                            </div>

                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold text-secondary">Diskon Non-Member</label>
                                <div class="position-relative">
                                    <input type="text"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        class="form-control shadow-none"
                                        wire:model="diskon_non_member_persen"
                                        placeholder="0"
                                        style="background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 12px; padding-right: 35px; height: 100%;">

                                    <span class="position-absolute top-50 end-0 translate-middle-y text-secondary fw-bold pe-3"
                                        style="pointer-events: none; z-index: 5;">
                                        %
                                    </span>
                                </div>
                            </div>
                            @else
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold text-secondary">Diskon Member</label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                        style="pointer-events: none; z-index: 5;">
                                        Rp
                                    </span>

                                    <input type="text"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                        class="form-control shadow-none"
                                        wire:model="diskon_member_nominal"
                                        placeholder="0"
                                        style="background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 12px; padding-left: 45px; height: 100%;">
                                </div>
                            </div>

                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold text-secondary">Diskon Non-Member</label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                        style="pointer-events: none; z-index: 5;">
                                        Rp
                                    </span>

                                    <input type="text"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                        class="form-control shadow-none"
                                        wire:model="diskon_non_member_nominal"
                                        placeholder="0"
                                        style="background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 12px; padding-left: 45px; height: 100%;">
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info mt-3">
                            <small><i class="bi bi-info-circle me-1"></i> Pastikan nominal diskon sesuai dengan kebijakan harga toko.</small>
                        </div>
                    </div>
                </div>
            </div>
            <!--================== END PENGATURAN DISKON ==================-->
        </div>
        <!--================== END CARD 1 & 2 ==================-->


        <!--================== CARD 3 & 4 ==================-->
        <div class="row">
            <!--================== SYARAT & KETENTUAN ==================-->
            <div class="col-md-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-warning bg-opacity-10 p-3 border-0">
                        <h5 class="mb-0 text-warning fw-bold"><i class="bi bi-shield-check me-2"></i>Syarat & Ketentuan</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Untuk Member <span class="text-danger">*</span></label>
                            <select class="form-control form-select @error('untuk_member') is-invalid @enderror"
                                wire:model="untuk_member">
                                <option value="semua">Semua (Member & Non-Member)</option>
                                <option value="member_only">Member Saja</option>
                                <option value="non_member_only">Non-Member Saja</option>
                            </select>
                            @error('untuk_member')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Minimum Pembelian</label>
                            <div class="position-relative">
                                <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                    style="pointer-events: none; z-index: 5;">
                                    Rp
                                </span>
                                <input type="text"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                    class="form-control form-control-lg shadow-none"
                                    wire:model="min_pembelian"
                                    placeholder="0"
                                    style="background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 12px; padding-left: 45px; height: 100%;">
                            </div>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Kosongkan jika tidak ada minimum</small>
                        </div>

                        <div class="mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="untuk_pembeli_pertama" id="untuk_pembeli_pertama" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-semibold ms-2" for="untuk_pembeli_pertama" style="cursor: pointer; padding-top: 2px;">
                                    Untuk Pembeli Pertama Saja
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--================== END SYARAT & KETENTUAN ==================-->

            <!--================== PERIODE & STATUS ==================-->
            <div class="col-md-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-danger bg-opacity-10 p-3 border-0">
                        <h5 class="mb-0 text-danger fw-bold"><i class="bi bi-calendar-range me-2"></i>Periode & Status</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Mulai Promo <span class="text-danger">*</span></label>
                                <input type="datetime-local"
                                    class="form-control @error('mulai_promo') is-invalid @enderror"
                                    wire:model="mulai_promo">
                                @error('mulai_promo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Selesai Promo <span class="text-danger">*</span></label>
                                <input type="datetime-local"
                                    class="form-control @error('selesai_promo') is-invalid @enderror"
                                    wire:model="selesai_promo">
                                @error('selesai_promo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Prioritas</label>
                            <input type="text"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="form-control"
                                wire:model="prioritas"
                                placeholder="50">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Semakin tinggi angka, semakin prioritas (1-100)</small>
                        </div>

                        <div class="mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-semibold ms-2" for="is_active" style="cursor: pointer; padding-top: 2px;">
                                    Status Aktif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--================== END PERIODE & STATUS ==================-->
        </div>
        <!--================== END CARD 3 & 4 ==================-->


        <!--================== CARD 5 & 6 ==================-->
        <div class="row">
            <!--================== PENGATURAN STACKING PROMO ==================-->
            <div class="{{ $tipe_promo === 'flash_sale' ? 'col-md-6' : 'col-md-12' }}">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-info bg-opacity-10 p-3 border-0">
                        <h5 class="mb-0 text-info fw-bold"><i class="bi bi-layers-fill me-2"></i>Pengaturan Stacking Promo</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="can_stack_with_other"
                                    id="can_stack_with_other" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-semibold ms-2" for="can_stack_with_other" style="cursor: pointer; padding-top: 2px;">
                                    Dapat Digabung dengan Promo Lain
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="can_stack_with_referral"
                                    id="can_stack_with_referral" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-semibold ms-2" for="can_stack_with_referral" style="cursor: pointer; padding-top: 2px;">
                                    Dapat Digabung dengan Referral
                                </label>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="can_stack_with_points"
                                    id="can_stack_with_points" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-semibold ms-2" for="can_stack_with_points" style="cursor: pointer; padding-top: 2px;">
                                    Dapat Digabung dengan Poin
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--================== END PENGATURAN STACKING PROMO ==================-->

            <!--================== TAMPILAN BADGE ==================-->
            @if ($tipe_promo === 'flash_sale')
            <style>
                /* Reset total tampilan bawaan Mac/Windows */
                .input-color-solid {
                    -webkit-appearance: none !important;
                    -moz-appearance: none !important;
                    appearance: none !important;
                    background-color: transparent !important;
                    padding: 0 !important;
                    border: none !important;
                }

                /* Menghilangkan padding dalam dari browser */
                .input-color-solid::-webkit-color-swatch-wrapper {
                    padding: 0 !important;
                }

                /* Memaksa warna merentang penuh dan melengkung mengikuti Bootstrap */
                .input-color-solid::-webkit-color-swatch {
                    border: none !important;
                    border-radius: 0.5rem !important;
                }

                .input-color-solid::-moz-color-swatch {
                    border: none !important;
                    border-radius: 0.5rem !important;
                }
            </style>

            <div class="col-md-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-secondary bg-opacity-10 p-3 border-0">
                        <h5 class="mb-0 text-secondary fw-bold"><i class="bi bi-patch-check-fill me-2"></i>Tampilan Badge</h5>
                    </div>
                    <div class="card-body p-4">

                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Teks Badge</label>
                                <input type="text" class="form-control"
                                    wire:model="badge_text" placeholder="Contoh: SALE 50%">
                                <small class="text-muted d-block"><i class="bi bi-info-circle me-1"></i>Tampil pada produk</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Warna</label>
                                <input type="color" class="form-control form-control-lg w-100 shadow-sm input-color-solid"
                                    wire:model="badge_color" style="height: 48px; cursor: pointer;">

                                @if ($badge_text)
                                <div class="mt-3 text-center">
                                    <label class="form-label fw-semibold d-block mb-2 text-muted" style="font-size: 0.8rem;">Preview:</label>
                                    <span class="badge rounded-pill shadow-sm w-100 d-inline-block text-truncate"
                                        style="background-color: {{ $badge_color }}; color: white; padding: 0.7em 1em; font-size: 0.9em;">
                                        {{ $badge_text }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="show_on_homepage"
                                    id="show_on_homepage" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-semibold ms-2" for="show_on_homepage" style="cursor: pointer; padding-top: 2px;">
                                    Tampilkan di Homepage
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            @endif
            <!--================== END TAMPILAN BADGE ==================-->
        </div>
        <!--================== END CARD 5 & 6 ==================-->


        <!--================== TAMPILAN PRODUK ==================-->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-primary bg-opacity-10 p-3 border-0">
                <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-box-seam-fill me-2"></i>Pilih Produk</h5>
            </div>

            <div class="card-body p-4">
                <p class="text-muted mb-4">
                    <i class="bi bi-info-circle me-1"></i>Pilih produk yang akan mendapatkan promo ini. Kosongkan jika berlaku untuk semua produk.
                </p>

                <div class="row g-3">
                    @foreach ($allProducts as $product)
                    <div class="col-md-6 col-lg-4">
                        <div class="p-2 rounded-3 border-0 d-flex align-items-center" style="transition: all 0.2s;">
                            <div class="form-check form-switch mb-0 d-flex align-items-center w-100">
                                <input class="form-check-input m-0 flex-shrink-0" type="checkbox" wire:model="selectedProducts"
                                    value="{{ $product->id }}" id="product_{{ $product->id }}" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-semibold ms-3 w-100 text-truncate" for="product_{{ $product->id }}" style="cursor: pointer; padding-top: 2px;">
                                    {{ $product->nama_akun }}
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if (count($selectedProducts) > 0)
                <div class="p-3 bg-primary bg-opacity-10 border-0 rounded-4 mt-4 mb-0 d-flex align-items-center shadow-sm">

                    <i class="bi bi-check-circle-fill text-primary fs-4 mb-3 me-3 lh-1"></i>

                    <p class="text-dark mb-0 m-0" style="font-size: 0.95rem;">
                        <span class="fw-bold text-primary">{{ count($selectedProducts) }} produk</span> telah dipilih untuk mendapatkan promo ini.
                    </p>

                </div>
                @endif
            </div>
        </div>
        <!--================== END TAMPILAN PRODUK ==================-->

        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                {{ $this->mode === 'create' ? 'Tambah Promo' : 'Simpan Perubahan' }}
            </button>
        </div>

    </form>
</div>