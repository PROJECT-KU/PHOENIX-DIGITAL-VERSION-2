<form wire:submit.prevent="save">
    <div class="d-flex flex-column gap-4">

        <!--================== INFORMASI DASAR ==================-->
        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-primary bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-info-circle me-2"></i>Informasi Dasar</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="namaAkun" class="form-label fw-semibold text-muted">Nama Akun <span class="text-danger">*</span></label>
                        <input type="text" id="namaAkun" wire:model.defer="nama_akun"
                            class="form-control @error('nama_akun') is-invalid @enderror"
                            placeholder="Masukkan nama akun">
                        @error('nama_akun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label for="tipeAkun" class="form-label fw-semibold text-muted">Tipe Akun <span class="text-danger">*</span></label>
                        <select id="tipeAkun" wire:model="tipe_akun"
                            class="form-select @error('tipe_akun') is-invalid @enderror">
                            <option value="sharing">Sharing (1 akun banyak orang)</option>
                            <option value="private">Private (1 akun 1 orang)</option>
                        </select>
                        <div class="form-text text-muted" style="font-size:.78rem;">
                            Menentukan cara hitung modal: <b>sharing</b> = total pembelian akun; <b>private</b> = modal satuan &times; jumlah order.
                        </div>
                        @error('tipe_akun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="butuhFile" class="jasa-card {{ $butuh_file ? 'is-on' : '' }}">
                            <span class="jasa-ic"><i class="bi bi-cloud-arrow-up"></i></span>
                            <span class="jasa-body">
                                <span class="jasa-title">
                                    Produk JASA
                                    <span class="jasa-badge">{{ $butuh_file ? 'Aktif' : 'Nonaktif' }}</span>
                                </span>
                                <span class="jasa-desc">
                                    Sekali bayar, tanpa durasi (mis. <b>cek plagiasi</b>). Customer mengunggah
                                    dokumen di halaman "Pesanan Berhasil" atau mengirim via WhatsApp.
                                </span>
                            </span>
                            <span class="jasa-switch">
                                <input class="jasa-input" type="checkbox" role="switch" id="butuhFile"
                                    wire:model.live="butuh_file">
                                <span class="jasa-track"><span class="jasa-thumb"></span></span>
                            </span>
                        </label>
                    </div>
                </div>

                <style>
                    .jasa-card {
                        display: flex;
                        align-items: center;
                        gap: 1rem;
                        width: 100%;
                        margin: .5rem 0 0;
                        padding: 1rem 1.15rem;
                        border-radius: 16px;
                        border: 1.5px solid #e6e8f2;
                        background: #fbfbfe;
                        cursor: pointer;
                        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
                    }

                    .jasa-card:hover { border-color: #fcd34d; }

                    /* ===== Bagian Add-on ===== */
                    .ad-sec { padding: 16px; border: 1px solid #e9ecef; border-radius: 16px; background: #fcfcfd; }
                    .ad-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
                    .ad-head-ic {
                        width: 40px; height: 40px; flex-shrink: 0; border-radius: 11px;
                        background: #ecfdf5; color: #16a34a; font-size: 1.1rem;
                        display: flex; align-items: center; justify-content: center;
                    }
                    .ad-head-ic i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; }
                    .ad-head-ic i.bi::before { display: block; line-height: 1; }
                    .ad-head-txt { flex: 1; min-width: 0; display: flex; flex-direction: column; }
                    .ad-head-txt b { font-size: .92rem; color: #1e293b; }
                    .ad-head-txt small { font-size: .76rem; color: #94a3b8; line-height: 1.35; }
                    .ad-btn-add {
                        flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px;
                        height: 36px; padding: 0 15px; border: 1px solid #86efac; border-radius: 10px;
                        background: #fff; color: #15803d; font-size: .82rem; font-weight: 700; cursor: pointer;
                        transition: background .16s, border-color .16s;
                    }
                    .ad-btn-add:hover { background: #f0fdf4; border-color: #4ade80; }
                    .ad-btn-add i.bi { display: flex; line-height: 1; font-size: .82rem; }
                    .ad-btn-add i.bi::before { display: block; line-height: 1; }

                    /* Mode pemilihan */
                    .ad-mode { margin-bottom: 14px; }
                    .ad-mode-label { display: block; font-size: .74rem; font-weight: 700; letter-spacing: .03em;
                        text-transform: uppercase; color: #94a3b8; margin-bottom: 7px; }
                    .ad-mode-opts { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 8px; }
                    .ad-mode-opt {
                        display: flex; align-items: center; gap: 9px; padding: 10px 12px;
                        border: 1.5px solid #e5e7eb; border-radius: 11px; background: #fff; cursor: pointer;
                        transition: border-color .18s, background .18s, box-shadow .18s;
                    }
                    .ad-mode-opt:hover { border-color: #86efac; background: #f8fefb; }
                    .ad-mode-opt.is-on { border-color: #16a34a; background: #f0fdf4; box-shadow: 0 2px 8px rgba(22,163,74,.10); }
                    .ad-mode-opt input { position: absolute; opacity: 0; width: 0; height: 0; }
                    .ad-mode-opt > i.bi { font-size: 1.05rem; color: #94a3b8; display: flex; line-height: 1; flex-shrink: 0; }
                    .ad-mode-opt > i.bi::before { display: block; line-height: 1; }
                    .ad-mode-opt.is-on > i.bi { color: #16a34a; }
                    .ad-mode-opt span { display: flex; flex-direction: column; min-width: 0; }
                    .ad-mode-opt b { font-size: .83rem; color: #1e293b; }
                    .ad-mode-opt small { font-size: .71rem; color: #94a3b8; }
                    .ad-mode-opt.is-on b { color: #15803d; }

                    /* Kartu add-on */
                    .ad-card { padding: 13px 14px; border: 1px solid #e9ecef; border-radius: 13px;
                        background: #fff; margin-bottom: 10px; transition: box-shadow .2s, border-color .2s; }
                    .ad-card:hover { box-shadow: 0 4px 14px rgba(15,23,42,.05); border-color: #dee2e6; }
                    .ad-card-top { display: flex; align-items: center; gap: 9px; margin-bottom: 11px; }
                    .ad-num {
                        width: 24px; height: 24px; flex-shrink: 0; border-radius: 7px; background: #ecfdf5;
                        color: #15803d; font-size: .76rem; font-weight: 800;
                        display: flex; align-items: center; justify-content: center;
                    }
                    .ad-card-title { flex: 1; min-width: 0; font-size: .86rem; font-weight: 700; color: #334155;
                        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                    .ad-del {
                        flex-shrink: 0; width: 30px; height: 30px; border: 0; border-radius: 8px;
                        background: transparent; color: #cbd5e1; display: flex; align-items: center;
                        justify-content: center; cursor: pointer; transition: background .16s, color .16s;
                    }
                    .ad-del:hover { background: #fef2f2; color: #dc2626; }
                    .ad-del i.bi { display: flex; line-height: 1; font-size: .85rem; }
                    .ad-del i.bi::before { display: block; line-height: 1; }
                    .ad-lbl { display: block; font-size: .76rem; font-weight: 600; color: #64748b; margin-bottom: 4px; }
                    .ad-opt { font-weight: 400; color: #cbd5e1; font-size: .7rem; }

                    /* Empty state */
                    .ad-empty { text-align: center; padding: 26px 16px; border: 1.5px dashed #dee2e6;
                        border-radius: 13px; background: #fff; }
                    .ad-empty > i.bi { font-size: 1.9rem; color: #cbd5e1; display: block; line-height: 1; margin-bottom: 8px; }
                    .ad-empty > i.bi::before { display: block; line-height: 1; }
                    .ad-empty b { display: block; font-size: .88rem; color: #64748b; margin-bottom: 3px; }
                    .ad-empty small { display: block; font-size: .76rem; color: #94a3b8; line-height: 1.5; max-width: 460px; margin: 0 auto; }

                    /* Pratinjau harga per halaman */
                    .hh-preview {
                        display: flex; align-items: center; gap: 8px; margin-top: 11px;
                        padding: 9px 13px; border-radius: 10px; background: #fffbeb;
                        border: 1px solid #fde68a; font-size: .81rem; color: #92400e;
                    }
                    .hh-preview i.bi { display: flex; line-height: 1; flex-shrink: 0; color: #b45309; }
                    .hh-preview i.bi::before { display: block; line-height: 1; }
                    .hh-preview b { color: #b45309; }

                    /* Picker nama add-on — disamakan persis dengan .da-picker-btn (Data Akun) */
                    .ad-picker {
                        cursor: pointer;
                    }

                    .ad-picker::after {
                        content: "\F282";
                        font-family: "bootstrap-icons";
                        float: right;
                        color: #94a3b8;
                        font-size: .8rem;
                    }

                    /* Daftar di popup picker — disamakan dengan .da-pick-* (Data Akun) */
                    .ad-pick-list {
                        max-height: 340px;
                        overflow-y: auto;
                        text-align: left;
                        display: flex;
                        flex-direction: column;
                        gap: .4rem;
                        padding: .2rem;
                    }

                    .ad-pick-item {
                        display: block;
                        width: 100%;
                        text-align: left;
                        border: 1px solid #e6e8f2;
                        background: #fff;
                        border-radius: 12px;
                        padding: .7rem .9rem;
                        font-weight: 600;
                        color: #1e293b;
                        font-size: .92rem;
                        transition: all .15s ease;
                    }

                    .ad-pick-item:hover {
                        border-color: #6c63ff;
                        background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.04));
                        transform: translateY(-1px);
                    }

                    .ad-pick-empty {
                        text-align: center;
                        color: #94a3b8;
                        padding: 1.5rem;
                        font-size: .9rem;
                    }

                    /* ===== Responsif mobile ===== */
                    @media (max-width: 575.98px) {
                        .ad-sec { padding: 13px; border-radius: 14px; }
                        .ad-mode-opts, .jmode-grid { grid-template-columns: 1fr; }
                        .ad-head { flex-wrap: wrap; gap: 10px; }
                        .ad-head-txt { flex-basis: calc(100% - 52px); }
                        .ad-btn-add { width: 100%; justify-content: center; }
                        .ad-card { padding: 12px; }
                        .ad-card-top { margin-bottom: 9px; }
                        .ad-lbl { font-size: .74rem; }
                        .jmode-card { padding: 12px 34px 12px 12px; }
                        .jmode-txt small { font-size: .72rem; }
                        .ad-empty { padding: 20px 12px; }
                        .ad-empty small { font-size: .74rem; }
                    }

                    /* Kartu pilihan mode jasa (paket / per halaman) */
                    .jmode-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 9px; }
                    .jmode-check { position: absolute; top: 10px; right: 11px; font-size: .95rem; color: #f59e0b; opacity: 0; transition: opacity .18s; }
                    .jmode-check::before { display: block; line-height: 1; }
                    .jmode-card.is-on .jmode-check { opacity: 1; }
                    .jmode-card {
                        position: relative;
                        display: flex; align-items: center; gap: 11px; width: 100%; height: 100%;
                        padding: 13px 34px 13px 14px; border: 1.5px solid #e5e7eb; border-radius: 12px;
                        background: #fff; cursor: pointer; transition: border-color .18s, background .18s, box-shadow .18s;
                    }
                    .jmode-card:hover { border-color: #fcd34d; background: #fffdf7; }
                    .jmode-card.is-on { border-color: #f59e0b; background: #fffbeb; box-shadow: 0 2px 8px rgba(245,158,11,.12); }
                    .jmode-radio { position: absolute; opacity: 0; width: 0; height: 0; }
                    .jmode-ic {
                        width: 38px; height: 38px; flex-shrink: 0; border-radius: 10px;
                        background: #fff7ed; color: #ea580c; font-size: 1.05rem;
                        display: flex; align-items: center; justify-content: center;
                    }
                    .jmode-ic i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; }
                    .jmode-ic i.bi::before { display: block; line-height: 1; }
                    .jmode-card.is-on .jmode-ic { background: #f59e0b; color: #fff; }
                    .jmode-txt { display: flex; flex-direction: column; min-width: 0; }
                    .jmode-txt b { font-size: .88rem; color: #1e293b; }
                    .jmode-txt small { font-size: .74rem; color: #94a3b8; line-height: 1.3; }
                    .jmode-card.is-on .jmode-txt b { color: #b45309; }

                    .jasa-card.is-on {
                        border-color: #f59e0b;
                        background: linear-gradient(135deg, #fffbeb, #fff7ed);
                        box-shadow: 0 8px 22px rgba(245, 158, 11, .14);
                    }

                    .jasa-ic {
                        width: 46px;
                        height: 46px;
                        border-radius: 13px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.25rem;
                        color: #fff;
                        flex-shrink: 0;
                        background: #cbd5e1;
                        transition: background .2s ease, box-shadow .2s ease;
                    }

                    .jasa-card.is-on .jasa-ic {
                        background: linear-gradient(135deg, #f59e0b, #d97706);
                        box-shadow: 0 6px 14px rgba(245, 158, 11, .35);
                    }

                    /* Glyph .bi ada di ::before + vertical-align -> perlu dua lapis flex. */
                    .jasa-ic i.bi { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; line-height: 1; }
                    .jasa-ic i.bi::before { display: block; line-height: 1; }

                    .jasa-body { flex: 1; min-width: 0; }

                    .jasa-title {
                        display: flex;
                        align-items: center;
                        gap: .5rem;
                        font-weight: 700;
                        color: #1e293b;
                        font-size: .98rem;
                    }

                    .jasa-badge {
                        font-size: .64rem;
                        font-weight: 800;
                        letter-spacing: .4px;
                        text-transform: uppercase;
                        padding: 2px 8px;
                        border-radius: 999px;
                        color: #64748b;
                        background: #eef0f6;
                    }

                    .jasa-card.is-on .jasa-badge { color: #b45309; background: #fde68a; }

                    .jasa-desc {
                        display: block;
                        margin-top: 3px;
                        font-size: .8rem;
                        line-height: 1.5;
                        color: #94a3b8;
                    }

                    /* Toggle besar & mulus */
                    .jasa-switch { flex-shrink: 0; position: relative; }
                    .jasa-input { position: absolute; opacity: 0; width: 0; height: 0; }
                    .jasa-track {
                        display: block;
                        width: 52px;
                        height: 30px;
                        border-radius: 999px;
                        background: #cbd5e1;
                        transition: background .22s ease;
                        position: relative;
                    }
                    .jasa-thumb {
                        position: absolute;
                        top: 3px;
                        left: 3px;
                        width: 24px;
                        height: 24px;
                        border-radius: 50%;
                        background: #fff;
                        box-shadow: 0 2px 5px rgba(0, 0, 0, .2);
                        transition: transform .22s ease;
                    }
                    .jasa-input:checked + .jasa-track { background: linear-gradient(135deg, #f59e0b, #d97706); }
                    .jasa-input:checked + .jasa-track .jasa-thumb { transform: translateX(22px); }
                    .jasa-input:focus-visible + .jasa-track { outline: 2px solid #6366f1; outline-offset: 2px; }
                </style>
            </div>
        </div>
        <!--================== END INFORMASI DASAR ==================-->

        <!--================== KATALOG HARGA ==================-->
        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-success bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-success fw-bold"><i class="bi bi-tags me-2"></i>Katalog Harga</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">

                    <div class="col-md-12" x-data>
                        <label for="hargaAwal" class="form-label fw-semibold text-muted">Harga Awal</label>
                        <div class="position-relative">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" id="hargaAwal"
                                value="{{ $harga_awal ? number_format($harga_awal, 0, ',', '.') : '' }}"
                                class="form-control @error('harga_awal') is-invalid @enderror"
                                placeholder="0"
                                @input="
                            let number = $el.value.replace(/[^0-9]/g, '');
                            if(number){
                                $el.value = new Intl.NumberFormat('id-ID').format(number);
                            } else {
                                $el.value = '';
                            }
                            @this.set('harga_awal', number)
                        ">
                        </div>
                        @error('harga_awal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <hr class="my-3">

                @if ($butuh_file)
                {{-- Mode penjualan jasa --}}
                <div class="ad-sec mb-3">
                    <div class="ad-head">
                        <span class="ad-head-ic" style="background:#eef2ff; color:#4338ca;"><i class="bi bi-shop"></i></span>
                        <div class="ad-head-txt">
                            <b>Cara Dijual <span class="text-danger">*</span></b>
                            <small>Menentukan bagaimana harga dihitung untuk customer</small>
                        </div>
                    </div>
                    <div class="jmode-grid">
                        <label class="jmode-card {{ $jasa_mode === 'paket' ? 'is-on' : '' }}">
                            <input type="radio" value="paket" wire:model.live="jasa_mode" class="jmode-radio">
                            <span class="jmode-ic"><i class="bi bi-collection"></i></span>
                            <span class="jmode-txt">
                                <b>Paket Pengecekan</b>
                                <small>Customer memilih paket 1×, 5×, 10× — kuota dipakai bertahap</small>
                            </span>
                            <i class="bi bi-check-circle-fill jmode-check"></i>
                        </label>
                        <label class="jmode-card {{ $jasa_mode === 'halaman' ? 'is-on' : '' }}">
                            <input type="radio" value="halaman" wire:model.live="jasa_mode" class="jmode-radio">
                            <span class="jmode-ic"><i class="bi bi-file-earmark-text"></i></span>
                            <span class="jmode-txt">
                                <b>Per Halaman</b>
                                <small>Customer unggah PDF dulu, harga = tarif × jumlah halaman</small>
                            </span>
                            <i class="bi bi-check-circle-fill jmode-check"></i>
                        </label>
                    </div>
                </div>

                @if ($jasa_mode === 'halaman')
                {{-- Jasa PER HALAMAN: satu harga, dikali jumlah halaman dokumen. --}}
                <div class="ad-sec">
                    <div class="ad-head">
                        <span class="ad-head-ic" style="background:#fff7ed; color:#ea580c;"><i class="bi bi-file-earmark-text"></i></span>
                        <div class="ad-head-txt">
                            <b>Harga per Halaman <span class="text-danger">*</span></b>
                            <small>Customer unggah PDF dulu, sistem menghitung halamannya</small>
                        </div>
                    </div>

                    <div class="position-relative" x-data>
                        <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                            style="pointer-events: none; z-index: 5;">
                            Rp
                        </span>
                        <input type="text" style="padding-left: 2.4rem; padding-right: 6rem;"
                            value="{{ $harga_per_halaman !== '' ? number_format((int) $harga_per_halaman, 0, ',', '.') : '' }}"
                            class="form-control @error('harga_per_halaman') is-invalid @enderror" placeholder="0"
                            @input="let n = $el.value.replace(/[^0-9]/g,''); $el.value = n ? new Intl.NumberFormat('id-ID').format(n) : ''; @this.set('harga_per_halaman', n)">
                        <span class="position-absolute top-50 end-0 translate-middle-y text-muted pe-3"
                            style="pointer-events: none; z-index: 5; font-size: .85rem;">
                            / halaman
                        </span>
                    </div>
                    @error('harga_per_halaman') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                    @if ($harga_per_halaman !== '' && (int) $harga_per_halaman > 0)
                    <div class="hh-preview">
                        <i class="bi bi-calculator"></i>
                        <span>Contoh: dokumen <b>10 halaman</b> → <b>Rp {{ number_format((int) $harga_per_halaman * 10, 0, ',', '.') }}</b></span>
                    </div>
                    @endif
                </div>
                @else
                {{-- Produk JASA: paket per JUMLAH PENGECEKAN (mis. 1x, 5x). --}}
                <div class="ad-sec">
                    <div class="ad-head">
                        <span class="ad-head-ic" style="background:#fff7ed; color:#ea580c;"><i class="bi bi-collection"></i></span>
                        <div class="ad-head-txt">
                            <b>Paket Jasa <span class="text-danger">*</span></b>
                            <small>Harga per jumlah pengecekan — mis. 1× Rp10.000, 5× Rp30.000</small>
                        </div>
                        <button type="button" wire:click="addPrice" class="ad-btn-add" style="border-color:#fcd34d; color:#b45309;">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>
                    @error('prices') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                    @foreach ($prices as $i => $row)
                    <div class="ad-card" wire:key="jasa-{{ $i }}">
                        <div class="ad-card-top">
                            <span class="ad-num" style="background:#fff7ed; color:#b45309;">{{ $i + 1 }}</span>
                            <span class="ad-card-title">
                                {{ (int) ($row['durasi_value'] ?? 0) > 0 ? ((int) $row['durasi_value']).'× pengecekan' : 'Paket baru' }}
                            </span>
                            @if (count($prices) > 1)
                            <button type="button" wire:click="removePrice({{ $i }})" class="ad-del" title="Hapus paket">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>

                        <div class="row g-2">
                            <div class="col-12 col-sm-5">
                                <label class="ad-lbl">Jumlah Pengecekan</label>
                                <div class="position-relative">
                                    <input type="number" min="1" wire:model.live.debounce.500ms="prices.{{ $i }}.durasi_value"
                                        class="form-control @error('prices.'.$i.'.durasi_value') is-invalid @enderror" placeholder="1" style="padding-right:2.2rem;">
                                    <span class="position-absolute top-50 end-0 translate-middle-y text-secondary fw-bold pe-3" style="pointer-events:none;">×</span>
                                </div>
                                @error('prices.'.$i.'.durasi_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-sm-7" x-data>
                                <label class="ad-lbl">Harga Paket</label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                        style="pointer-events:none; z-index:5;">Rp</span>
                                    <input type="text" style="padding-left:2.4rem;"
                                        value="{{ ($row['harga'] ?? '') !== '' ? number_format((int) $row['harga'], 0, ',', '.') : '' }}"
                                        class="form-control @error('prices.'.$i.'.harga') is-invalid @enderror" placeholder="0"
                                        @input="let n = $el.value.replace(/[^0-9]/g,''); $el.value = n ? new Intl.NumberFormat('id-ID').format(n) : ''; @this.set('prices.{{ $i }}.harga', n)">
                                </div>
                                @error('prices.'.$i.'.harga') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- ===== Add-on opsional (dinamis) ===== --}}
                <hr class="my-4">
                <div class="ad-sec">
                    <div class="ad-head">
                        <span class="ad-head-ic"><i class="bi bi-plus-circle"></i></span>
                        <div class="ad-head-txt">
                            <b>Add-on Opsional</b>
                            <small>Tambahan berbayar yang bisa dipilih customer saat membeli</small>
                        </div>
                        <button type="button" wire:click="addAddon" class="ad-btn-add">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>

                    @if (count($addons))
                    {{-- Cara memilih --}}
                    <div class="ad-mode">
                        <span class="ad-mode-label">Cara customer memilih</span>
                        <div class="ad-mode-opts">
                            <label class="ad-mode-opt {{ $addon_mode === 'multi' ? 'is-on' : '' }}">
                                <input type="radio" value="multi" wire:model.live="addon_mode">
                                <i class="bi bi-check2-square"></i>
                                <span><b>Boleh beberapa</b><small>Customer bisa centang lebih dari satu tambahan</small></span>
                            </label>
                            <label class="ad-mode-opt {{ $addon_mode === 'tunggal' ? 'is-on' : '' }}">
                                <input type="radio" value="tunggal" wire:model.live="addon_mode">
                                <i class="bi bi-ui-radios"></i>
                                <span><b>Pilih satu</b><small>Bertingkat — memilih yang lain otomatis mengganti</small></span>
                            </label>
                        </div>
                    </div>

                    {{-- Daftar add-on --}}
                    @foreach ($addons as $i => $ad)
                    <div class="ad-card" wire:key="addon-{{ $i }}">
                        <div class="ad-card-top">
                            <span class="ad-num">{{ $i + 1 }}</span>
                            <span class="ad-card-title">{{ trim($ad['nama'] ?? '') !== '' ? $ad['nama'] : 'Add-on baru' }}</span>
                            <button type="button" wire:click="removeAddon({{ $i }})" class="ad-del" title="Hapus add-on">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <div class="row g-2">
                            <div class="col-12 col-md-5">
                                <label class="ad-lbl">Nama <span class="text-danger">*</span></label>
                                <button type="button" onclick="adNamaPicker(this, {{ $i }})"
                                    class="form-select text-start ad-picker shadow-none @error('addons.'.$i.'.nama') is-invalid @enderror">
                                    @if (trim($ad['nama'] ?? '') !== '')
                                    <span class="text-dark">{{ $ad['nama'] }}</span>
                                    @else
                                    <span class="text-muted">-- Pilih Nama Add-on --</span>
                                    @endif
                                </button>
                                @error('addons.'.$i.'.nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="ad-lbl">Keterangan <span class="ad-opt">opsional</span></label>
                                <input type="text" wire:model="addons.{{ $i }}.keterangan"
                                    class="form-control" placeholder="Hasil AI + Turnitin sekaligus">
                            </div>
                            <div class="col-12 col-md-3" x-data>
                                <label class="ad-lbl">Tambahan Harga <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                        style="pointer-events:none; z-index:5;">Rp</span>
                                    <input type="text" style="padding-left:2.4rem;"
                                        value="{{ ($ad['harga'] ?? '') !== '' ? number_format((int) $ad['harga'], 0, ',', '.') : '' }}"
                                        class="form-control @error('addons.'.$i.'.harga') is-invalid @enderror" placeholder="0"
                                        @input="let n = $el.value.replace(/[^0-9]/g,''); $el.value = n ? new Intl.NumberFormat('id-ID').format(n) : ''; @this.set('addons.{{ $i }}.harga', n)">
                                </div>
                                @error('addons.'.$i.'.harga') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    {{-- Empty state --}}
                    <div class="ad-empty">
                        <i class="bi bi-plus-circle"></i>
                        <b>Belum ada add-on</b>
                        <small>Tambahan opsional yang bisa dipilih customer saat membeli.</small>
                    </div>
                    @endif
                </div>
                @else

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label fw-semibold text-muted mb-0">Harga per Durasi <span class="text-danger">*</span></label>
                    <button type="button" wire:click="addPrice"
                        class="btn btn-sm btn-success rounded-3 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Tambah Durasi
                    </button>
                </div>
                @error('prices') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                <style>
                    /* Mobile: beri jarak & pemisah antar baris katalog harga agar tidak mepet. */
                    @media (max-width: 767.98px) {
                        .price-row-sep {
                            margin-top: .85rem !important;
                            padding-top: .85rem;
                            border-top: 1px dashed #e6e8f2;
                        }
                    }
                </style>

                @foreach ($prices as $i => $row)
                <div class="price-row row g-2 align-items-end mb-2 {{ $i > 0 ? 'price-row-sep' : '' }}" wire:key="price-{{ $i }}">
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1 {{ $i > 0 ? 'd-md-none' : '' }}">Durasi</label>
                        <input type="number" min="1" wire:model="prices.{{ $i }}.durasi_value"
                            class="form-control @error('prices.'.$i.'.durasi_value') is-invalid @enderror" placeholder="1">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1 {{ $i > 0 ? 'd-md-none' : '' }}">Satuan</label>
                        <select wire:model="prices.{{ $i }}.durasi_type" class="form-select">
                            <option value="bulan">Bulan</option>
                            <option value="tahun">Tahun</option>
                        </select>
                    </div>
                    <div class="col-9 col-md-5" x-data>
                        <label class="form-label small text-muted mb-1 {{ $i > 0 ? 'd-md-none' : '' }}">Harga</label>
                        <div class="position-relative">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">Rp</span>
                            <input type="text"
                                value="{{ ($row['harga'] ?? '') !== '' ? number_format((int) $row['harga'], 0, ',', '.') : '' }}"
                                class="form-control @error('prices.'.$i.'.harga') is-invalid @enderror" placeholder="0"
                                @input="let n = $el.value.replace(/[^0-9]/g, ''); $el.value = n ? new Intl.NumberFormat('id-ID').format(n) : ''; @this.set('prices.{{ $i }}.harga', n)">
                        </div>
                    </div>
                    <div class="col-3 col-md-1">
                        <label class="form-label small text-muted mb-1 d-block invisible {{ $i > 0 ? 'd-md-none' : '' }}">.</label>
                        <button type="button" wire:click="removePrice({{ $i }})"
                            class="btn btn-outline-danger w-100 d-inline-flex align-items-center justify-content-center"
                            style="height: 38px;" title="Hapus durasi">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
                <div class="form-text text-muted">
                    Tambahkan durasi apa pun (mis. 2, 3, 6 bulan). Untuk akun <b>private</b>, durasi ini juga dipakai mencocokkan modal.
                </div>
                @endif
            </div>
        </div>
        <!--================== END KATALOG HARGA ==================-->

        <!--================== MEDIA & DESKRIPSI ==================-->
        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-info bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-info fw-bold"><i class="bi bi-images me-2"></i>Media & Deskripsi</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label fw-bold text-secondary">Gambar Produk</label>
                        <div class="row g-4 align-items-start">
                            <div class="col-md-6">
                                <div class="upload-container position-relative">
                                    <input type="file" id="gambarInput" wire:model="image"
                                        class="file-input @error('image') is-invalid @enderror"
                                        accept="image/png, image/jpeg, image/jpg">
                                    <div class="upload-overlay">
                                        <i class="bi bi-cloud-upload fs-2 text-primary"></i>
                                        <span class="text-muted fw-bold">Klik untuk unggah gambar</span>
                                    </div>
                                </div>
                                @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted mt-2"><i class="bi bi-info-circle me-1"></i> JPG, PNG (Maks 5MB)</small>
                            </div>

                            <div class="col-md-6">
                                <div class="preview-box border p-2 rounded-4 shadow-sm bg-white d-flex align-items-center justify-content-center" style="min-height: 150px;">
                                    @if ($image && is_object($image) && !$errors->has('image'))
                                    <img src="{{ $image->temporaryUrl() }}"
                                        class="rounded-3 img-fluid"
                                        style="cursor: pointer; max-height: 250px; object-fit: contain;"
                                        onclick="showGlossyPreview('{{ $image->temporaryUrl() }}')"
                                        title="Klik untuk memperbesar">
                                    @elseif ($existingImage)
                                    <img src="{{ asset('storage/img/Product/' . $existingImage) }}"
                                        class="rounded-3 img-fluid"
                                        style="cursor: pointer; max-height: 250px; object-fit: contain;"
                                        onclick="showGlossyPreview('{{ asset('storage/img/Product/' . $existingImage) }}')"
                                        title="Klik untuk memperbesar">
                                    @else
                                    <div class="text-center text-muted p-3">
                                        <i class="bi bi-image fs-1 opacity-50"></i>
                                        <p class="small mb-0">Preview Gambar</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="deskripsi" class="form-label fw-semibold text-muted">Deskripsi</label>
                        <textarea id="deskripsi" wire:model.defer="deskripsi" rows="4"
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            placeholder="Masukkan deskripsi produk..."></textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
        <!--================== END MEDIA & DESKRIPSI ==================-->

        <div class="d-flex mt-2 mb-4">
            <button type="submit"
                class="btn flex-grow-1 d-inline-flex align-items-center justify-content-center text-white rounded-pill shadow-lg"
                style="height: 55px; background: linear-gradient(135deg, #6c63ff, #4e46e5); font-weight: 600; font-size: 1.1rem; border: none; transition: transform 0.2s;">
                <i class="bi bi-check2-circle me-2 fs-4"></i>
                <span>{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
            </button>
        </div>

    </div>
</form>

<!--================== SWEET ALERT IMAGE UPLOAD ==================-->
<script>
    window.showGlossyPreview = function(imageUrl) {
        Swal.fire({
            imageUrl: imageUrl,
            imageAlt: 'Preview Gambar',
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            padding: '1.25rem',
            background: 'rgba(255, 255, 255, 0.85)',
            backdrop: 'rgba(0, 0, 0, 0.5)',
            customClass: {
                popup: 'rounded-4 shadow-lg border border-white',
                image: 'rounded-3 shadow-sm m-0'
            },
            didOpen: () => {
                const img = Swal.getImage();
                if (img) {
                    img.style.maxHeight = '85vh';
                    img.style.maxWidth = '100%';
                    img.style.objectFit = 'contain';
                }
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        const ToastGlossy = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            background: 'rgba(255, 255, 255, 0.85)',
            customClass: {
                popup: 'swal-glossy-toast',
                title: 'swal-toast-title',
                timerProgressBar: 'swal-toast-progress'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // MENGUBAH PENCARIAN ELEMEN MENGGUNAKAN NAMA CLASS (.file-input)
        const gambarInput = document.querySelector('.file-input');

        if (gambarInput) {
            gambarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

                    if (!validImageTypes.includes(file.type)) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.target.value = '';

                        ToastGlossy.fire({
                            icon: 'error',
                            title: 'Format tidak didukung!',
                            text: 'Gunakan file gambar JPG atau PNG.'
                        });
                        return;
                    }

                    const maxSizeInBytes = 5 * 1024 * 1024;
                    if (file.size > maxSizeInBytes) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.target.value = '';

                        ToastGlossy.fire({
                            icon: 'error',
                            title: 'Ukuran Terlalu Besar!',
                            text: 'Maksimal ukuran gambar adalah 5 MB.'
                        });
                        return;
                    }
                }
            }, true);
        }

        // Tampilkan pesan error saat penyimpanan gagal (sebelumnya gagal senyap).
        if (window.Livewire) {
            Livewire.on('product-save-error', (event) => {
                const msg = (event && event.message) ? event.message : 'Terjadi kesalahan saat menyimpan data.';
                ToastGlossy.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: msg
                });
            });
        }

    });
</script>
<!--================== END SWEET ALERT IMAGE UPLOAD ==================-->
<!--================== PICKER NAMA ADD-ON (pola select2 Swal, seragam) ==================-->
<script>
    (function () {
        // Daftar produk diperbarui tiap render (di luar guard).
        window.__adProducts = @json($daftarProduk->map(fn ($p) => ['name' => $p->nama_akun])->values());

        if (window.__adPickerBound) return;
        window.__adPickerBound = true;

        window.adNamaPicker = function (btn, index) {
            if (typeof Swal === 'undefined') return;
            const el = btn.closest('[wire\\:id]'); if (!el) return;
            const cid = el.getAttribute('wire:id');
            const items = window.__adProducts || [];
            const esc = (s) => String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));

            const setNama = (val) => {
                if (window.Livewire) window.Livewire.find(cid).set('addons.' + index + '.nama', val);
            };

            const rows = items.length
                ? items.map(it => '<button type="button" class="ad-pick-item" data-name="' + esc(it.name) + '" data-search="' + esc((it.name || '').toLowerCase()) + '">'
                    + '<i class="bi bi-box-seam me-2" style="color:#d97706;"></i>' + esc(it.name) + '</button>').join('')
                : '<div class="ad-pick-empty">Belum ada produk lain</div>';

            Swal.fire({
                title: 'Pilih Nama Add-on',
                html: '<input id="adPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari produk...">'
                    + '<div id="adPickList" class="ad-pick-list">' + rows + '</div>'
                    + '<button type="button" id="adPickCustom" class="ad-pick-item mt-2" style="border-style:dashed;">'
                    + '<i class="bi bi-pencil me-2" style="color:#64748b;"></i>Tulis sendiri…</button>',
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem',
                didOpen: () => {
                    const search = document.getElementById('adPickSearch');
                    const listEl = document.getElementById('adPickList');
                    if (search) {
                        search.addEventListener('input', () => {
                            const q = search.value.toLowerCase();
                            listEl.querySelectorAll('.ad-pick-item').forEach(b => { b.style.display = b.dataset.search.includes(q) ? '' : 'none'; });
                        });
                        setTimeout(() => search.focus(), 100);
                    }
                    listEl.querySelectorAll('.ad-pick-item').forEach(b => {
                        b.addEventListener('click', () => { setNama(b.dataset.name); Swal.close(); });
                    });
                    // Nama bebas — untuk add-on yang bukan produk (mis. "Target < 20%").
                    const custom = document.getElementById('adPickCustom');
                    if (custom) custom.addEventListener('click', () => {
                        Swal.fire({
                            title: 'Tulis Nama Add-on',
                            input: 'text',
                            inputPlaceholder: 'mis. Target < 20% tanpa maksimal',
                            background: 'rgba(255, 255, 255, 0.92)',
                            backdrop: 'rgba(139, 92, 246, 0.15)',
                            customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold',
                                confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel' },
                            buttonsStyling: false, showCancelButton: true,
                            confirmButtonText: 'Simpan', cancelButtonText: 'Batal',
                        }).then(r => { if (r.isConfirmed && r.value) setNama(r.value); });
                    });
                }
            });
        };
    })();
</script>
<!--================== END PICKER NAMA ADD-ON ==================-->
