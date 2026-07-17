<div class="container-fluid">
    <style>
        /* Diselaraskan dgn form Gaji: card putih bersih, yang berwarna hanya
           chip ikonnya — bukan seluruh pita header (dulu 6 warna berbeda). */
        .form-section {
            background: rgba(255, 255, 255, 0.9);
        }

        /* Presisi ikon: .stat-icon-wrapper global tdk punya line-height:1,
           sehingga glyph turun sedikit dari tengah lingkaran. */
        .stat-icon-wrapper {
            line-height: 1 !important;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .stat-icon-wrapper i::before {
            display: block;
            line-height: 1;
        }
    </style>

    <form wire:submit.prevent="save">

        <!--================== CARD 1 & 2 ==================-->
        <div class="row">

            <!--================== INFORMASI DASAR ==================-->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4 form-section h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                                style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                                <i class="bi bi-info-circle"></i>
                            </span>
                            <h5 class="fw-bold mb-0">Informasi Dasar</h5>
                        </div>
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
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4 form-section h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="stat-icon-wrapper bg-gradient-green flex-shrink-0"
                                style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                                <i class="bi bi-tag-fill"></i>
                            </span>
                            <h5 class="fw-bold mb-0">Pengaturan Diskon</h5>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Tipe Diskon <span class="text-danger">*</span></label>
                            <select class="form-control form-select" wire:model.live="tipe_diskon" required>
                                <option value="persen">Persentase (%)</option>
                                <option value="nominal">Nominal (Rp)</option>
                            </select>
                        </div>

                        <div class="row">
                            @if ($tipe_diskon === 'persen')
                            <div class="col-6 mb-3 promo-diskon-col">
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

                            <div class="col-6 mb-3 promo-diskon-col">
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
                            <div class="col-6 mb-3 promo-diskon-col">
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

                            <div class="col-6 mb-3 promo-diskon-col">
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
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4 form-section h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="stat-icon-wrapper bg-gradient-blue flex-shrink-0"
                                style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                                <i class="bi bi-shield-check"></i>
                            </span>
                            <h5 class="fw-bold mb-0">Syarat & Ketentuan</h5>
                        </div>
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

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Kuota Promo</label>
                            <div class="position-relative">
                                <span class="position-absolute top-50 start-0 translate-middle-y text-secondary ps-3"
                                    style="pointer-events: none; z-index: 5;">
                                    <i class="bi bi-people-fill" style="vertical-align: -0.125em;"></i>
                                </span>
                                <input type="text"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                    class="form-control form-control-lg shadow-none"
                                    wire:model="kuota"
                                    placeholder="Tanpa batas"
                                    style="background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 12px; padding-left: 45px; height: 100%;">
                            </div>
                            @if($kuotaTerpakai !== null)
                            <small class="d-block mt-1 {{ $kuotaSisa === 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                                <i class="bi bi-bag-check me-1" style="vertical-align: -0.125em;"></i>Sudah terpakai <b>{{ $kuotaTerpakai }}</b>{{ $kuotaSisa !== null ? ' — sisa '.$kuotaSisa : '' }}
                            </small>
                            @endif
                            <small class="text-muted d-block"><i class="bi bi-info-circle me-1" style="vertical-align: -0.125em;"></i>Kosongkan = tanpa batas. Nyalakan juga "Pembeli Pertama" untuk promo seperti <b>20 pembeli pertama</b>. Pesanan yang dibatalkan mengembalikan kuota.</small>
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
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4 form-section h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="stat-icon-wrapper bg-gradient-red flex-shrink-0"
                                style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                                <i class="bi bi-calendar-range"></i>
                            </span>
                            <h5 class="fw-bold mb-0">Periode & Status</h5>
                        </div>
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
            <div class="{{ $tipe_promo === 'flash_sale' ? 'col-md-6' : 'col-md-12' }} mb-4">
                <div class="card border-0 shadow-sm rounded-4 form-section h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                                style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                                <i class="bi bi-layers-fill"></i>
                            </span>
                            <h5 class="fw-bold mb-0">Pengaturan Stacking Promo</h5>
                        </div>
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

                /* Mobile: label diskon dijaga 1 baris agar input Member & Non-Member sejajar. */
                @media (max-width: 575.98px) {
                    .promo-diskon-col .form-label {
                        font-size: .78rem;
                        white-space: nowrap;
                        margin-bottom: .3rem;
                    }
                }
            </style>

            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4 form-section h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="stat-icon-wrapper bg-gradient-blue flex-shrink-0"
                                style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                                <i class="bi bi-patch-check-fill"></i>
                            </span>
                            <h5 class="fw-bold mb-0">Tampilan Badge</h5>
                        </div>

                        {{-- Pemilih warna & preview dihilangkan: badge-nya toh sudah
                             langsung terlihat di halaman publik. Warna badge kini
                             mengikuti bawaan (#FF6B6B). Warna promo LAMA tidak
                             diubah — nilainya tetap tersimpan & tetap dipakai. --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Teks Badge</label>
                                <input type="text" class="form-control"
                                    wire:model="badge_text" placeholder="Contoh: SALE 50%">
                                <small class="text-muted d-block"><i class="bi bi-info-circle me-1"></i>Tampil pada produk</small>
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
        {{-- Tanpa h-100: card ini anak langsung <form>, bukan kolom dlm row —
             height:100% di sini justru bikin tumpang tindih dgn card di atasnya. --}}
        <div class="card border-0 shadow-sm rounded-4 form-section mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-wrapper bg-gradient-green flex-shrink-0"
                            style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                            <i class="bi bi-box-seam-fill"></i>
                        </span>
                        <h5 class="fw-bold mb-0 d-inline-flex align-items-center gap-2" style="line-height:1;">
                            Pilih Produk
                            <span class="badge bg-primary rounded-pill" style="font-size:.7rem;">{{ count($selectedProducts) }}/{{ count($allProducts) }}</span>
                        </h5>
                    </div>
                    {{-- Dua tombol terpisah: label tidak bisa basi/terbalik walau saklar
                         diubah manual (wire:model produk sengaja deferred). --}}
                    <div class="d-flex gap-2">
                        <button type="button" wire:click="pilihSemuaProduk"
                            class="btn btn-sm btn-primary rounded-pill px-3 flex-fill d-inline-flex align-items-center justify-content-center gap-2"
                            style="line-height:1;">
                            <i class="bi bi-check2-square"></i>Pilih Semua
                        </button>
                        <button type="button" wire:click="hapusSemuaProduk"
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 flex-fill d-inline-flex align-items-center justify-content-center gap-2"
                            style="line-height:1;">
                            <i class="bi bi-x-square"></i>Hapus Semua
                        </button>
                    </div>
                </div>

                <p class="text-muted mb-3" style="font-size:.88rem;">
                    <i class="bi bi-info-circle me-1" style="vertical-align:-0.125em;"></i>Pilih produk yang akan mendapatkan promo ini. Kosongkan jika berlaku untuk semua produk.
                </p>

                {{-- Daftar dibatasi tingginya & bisa digulir sendiri: 100 produk
                     sebelumnya jadi dinding panjang yang mendorong tombol Simpan
                     jauh ke bawah. --}}
                <div class="rounded-4 border p-2" style="max-height: 380px; overflow-y: auto; background: #fbfcff;">
                    <div class="row g-2">
                        @foreach ($allProducts as $product)
                        <div class="col-md-6 col-lg-4">
                            <div class="px-2 py-1 rounded-3 h-100">
                                <div class="form-check form-switch mb-0 d-flex align-items-center w-100">
                                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox" wire:model="selectedProducts"
                                        value="{{ $product->id }}" id="product_{{ $product->id }}" style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                    <label class="form-check-label fw-semibold ms-3 w-100 text-truncate" for="product_{{ $product->id }}"
                                        style="cursor: pointer; font-size: .88rem; line-height: 1.3;" title="{{ $product->nama_akun }}">
                                        {{ $product->nama_akun }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
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