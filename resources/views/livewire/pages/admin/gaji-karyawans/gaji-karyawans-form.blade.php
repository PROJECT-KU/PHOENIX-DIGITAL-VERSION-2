<div>
    <style>
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

        .form-section {
            background: rgba(255, 255, 255, 0.9);
        }

        /* Suffix unit di dalam input (mis. "jam") */
        .input-suffix-wrap {
            position: relative;
        }

        .input-suffix-wrap .input-suffix {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.85rem;
            pointer-events: none;
        }

        /* Hilangkan tombol panah bawaan input number agar rapi */
        .no-spinner::-webkit-outer-spin-button,
        .no-spinner::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .no-spinner {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        /* Prefix "Rp" di dalam input (gaya seperti peminjaman) */
        .rp-wrap {
            position: relative;
        }

        .rp-wrap .form-control {
            padding-left: 45px;
        }

        /* Field readonly: lembut, bersih, menarik */
        .readonly-pretty {
            background-color: #f6f5ff !important;
            border-color: #e4e0ff !important;
            color: #4f46e5 !important;
            font-weight: 600;
        }

        .readonly-pretty:focus {
            box-shadow: none;
            border-color: #c7d2fe !important;
        }

        .readonly-total {
            background-color: #ecfdf5 !important;
            border-color: #bbf7d0 !important;
            color: #059669 !important;
            font-weight: 700;
        }

        .readonly-total:focus {
            box-shadow: none;
        }
    </style>

    <form wire:submit.prevent="save">

        <!--================== DATA KARYAWAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                        <i class="bi bi-person-vcard"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Data Karyawan</h5>
                </div>

                <div class="row">
                    <!-- Nama Karyawan -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Nama Karyawan <span class="text-danger">*</span></label>
                        @if(isset($users) && $users->count())
                        <select wire:model.live="nama_karyawan" class="form-select @error('nama_karyawan') is-invalid @enderror">
                            <option value="">-- Pilih Nama Karyawan --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @else
                        <select class="form-select" disabled>
                            <option>Tidak ada karyawan</option>
                        </select>
                        @endif
                        @error('nama_karyawan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="bank" class="form-label">Nama Bank</label>
                        <input type="text" wire:model="bank"
                            class="form-control readonly-pretty @error('bank') is-invalid @enderror" id="bank" readonly>
                        @error('bank')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="no_rek" class="form-label">No Rekening</label>
                        <input type="text" wire:model="no_rek"
                            class="form-control readonly-pretty @error('no_rek') is-invalid @enderror" id="no_rek" readonly>
                        @error('no_rek')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Transaksi (tanggal pembayaran) -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_transaksi" class="form-label">
                            Tanggal Pembayaran <span class="text-danger">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_transaksi"
                            class="form-control @error('tanggal_transaksi') is-invalid @enderror" id="tanggal_transaksi">
                        @error('tanggal_transaksi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Periode Gaji -->
                    <div class="col-md-3 mb-3">
                        <label for="periode_bulan" class="form-label">
                            Periode Bulan <span class="text-danger">*</span>
                        </label>
                        <select wire:model="periode_bulan" id="periode_bulan"
                            class="form-select @error('periode_bulan') is-invalid @enderror">
                            <option value="">-- Bulan --</option>
                            @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('periode_bulan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="periode_tahun" class="form-label">
                            Periode Tahun <span class="text-danger">*</span>
                        </label>
                        <select wire:model="periode_tahun" id="periode_tahun"
                            class="form-select @error('periode_tahun') is-invalid @enderror">
                            <option value="">-- Tahun --</option>
                            @foreach ($daftarTahun as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                            @endforeach
                        </select>
                        @error('periode_tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!--================== DATA GAJI ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-green flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                        <i class="bi bi-cash-coin"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Data Gaji & Tunjangan</h5>
                </div>

                <div class="row">
                    <!-- Gaji Pokok -->
                    <div class="col-md-12 mb-3">
                        <label for="gaji_pokok" class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="gaji_pokok"
                                class="form-control @error('gaji_pokok') is-invalid @enderror rupiah" id="gaji_pokok" placeholder="0">
                        </div>
                        @error('gaji_pokok')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="bonus_kinerja" class="form-label">Bonus Kinerja</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="bonus_kinerja"
                                class="form-control @error('bonus_kinerja') is-invalid @enderror rupiah" id="bonus_kinerja" placeholder="0">
                        </div>
                        @error('bonus_kinerja')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="bonus_lainnya" class="form-label">Bonus Lainnya</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="bonus_lainnya"
                                class="form-control @error('bonus_lainnya') is-invalid @enderror rupiah" id="bonus_lainnya" placeholder="0">
                        </div>
                        @error('bonus_lainnya')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_kesehatan" class="form-label">Tunjangan Kesehatan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_kesehatan"
                                class="form-control @error('tunjangan_kesehatan') is-invalid @enderror rupiah" id="tunjangan_kesehatan" placeholder="0">
                        </div>
                        @error('tunjangan_kesehatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_thr" class="form-label">Tunjangan THR</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_thr"
                                class="form-control @error('tunjangan_thr') is-invalid @enderror rupiah" id="tunjangan_thr" placeholder="0">
                        </div>
                        @error('tunjangan_thr')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_ketenagakerjaan" class="form-label">Tunjangan Ketenagakerjaan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_ketenagakerjaan"
                                class="form-control @error('tunjangan_ketenagakerjaan') is-invalid @enderror rupiah" id="tunjangan_ketenagakerjaan" placeholder="0">
                        </div>
                        @error('tunjangan_ketenagakerjaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_lainnya" class="form-label">Tunjangan Lainnya</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_lainnya"
                                class="form-control @error('tunjangan_lainnya') is-invalid @enderror rupiah" id="tunjangan_lainnya" placeholder="0">
                        </div>
                        @error('tunjangan_lainnya')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Lembur: jam x tarif/jam = total otomatis -->
                    <div class="col-md-4 mb-3">
                        <label for="jam_lembur" class="form-label">Jam Lembur</label>
                        <div class="input-suffix-wrap">
                            <input type="number" min="0" step="1" wire:model="jam_lembur"
                                class="form-control no-spinner pe-5 @error('jam_lembur') is-invalid @enderror" id="jam_lembur" placeholder="0">
                            <span class="input-suffix">jam</span>
                        </div>
                        @error('jam_lembur')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="tarif_lembur" class="form-label">
                            Tarif / Jam
                            <i class="bi bi-pencil-square text-primary" title="Bisa diubah; tersimpan sebagai default baru"></i>
                        </label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tarif_lembur"
                                class="form-control @error('tarif_lembur') is-invalid @enderror" id="tarif_lembur" placeholder="15.000">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Default Rp 15.000, bisa diedit.</div>
                        @error('tarif_lembur')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="uang_lembur" class="form-label">Total Uang Lembur</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" id="uang_lembur" class="form-control readonly-pretty"
                                value="{{ $uang_lembur }}" placeholder="0" readonly>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.78rem;">Otomatis = jam &times; tarif.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_transport" class="form-label">Tunjangan Transport</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_transport"
                                class="form-control @error('tunjangan_transport') is-invalid @enderror rupiah" id="tunjangan_transport" placeholder="0">
                        </div>
                        @error('tunjangan_transport')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tunjangan_makan" class="form-label">Tunjangan Makan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="tunjangan_makan"
                                class="form-control @error('tunjangan_makan') is-invalid @enderror rupiah" id="tunjangan_makan" placeholder="0">
                        </div>
                        @error('tunjangan_makan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!--================== DATA POTONGAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-red flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                        <i class="bi bi-dash-circle"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Potongan & Total</h5>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="potongan" class="form-label">Potongan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="potongan"
                                class="form-control @error('potongan') is-invalid @enderror rupiah" id="potongan" placeholder="0">
                        </div>
                        @error('potongan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pph21" class="form-label">PPH 21</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="pph21"
                                class="form-control @error('pph21') is-invalid @enderror rupiah" id="pph21" placeholder="0">
                        </div>
                        @error('pph21')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="potongan_bpjs_kesehatan" class="form-label">Potongan BPJS Kesehatan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="potongan_bpjs_kesehatan"
                                class="form-control @error('potongan_bpjs_kesehatan') is-invalid @enderror rupiah" id="potongan_bpjs_kesehatan" placeholder="0">
                        </div>
                        @error('potongan_bpjs_kesehatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="potongan_bpjs_ketenagakerjaan" class="form-label">Potongan BPJS Ketenagakerjaan</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="potongan_bpjs_ketenagakerjaan"
                                class="form-control @error('potongan_bpjs_ketenagakerjaan') is-invalid @enderror rupiah" id="potongan_bpjs_ketenagakerjaan" placeholder="0">
                        </div>
                        @error('potongan_bpjs_ketenagakerjaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Potongan Pinjaman (terhubung ke fitur Pengembalian) -->
                    <div class="col-md-12 mb-3">
                        <label for="potongan_pinjaman" class="form-label">
                            Potongan Pinjaman / Kasbon
                            <i class="bi bi-link-45deg text-primary" title="Terhubung ke fitur Pengembalian"></i>
                        </label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" wire:model="potongan_pinjaman"
                                class="form-control @error('potongan_pinjaman') is-invalid @enderror rupiah" id="potongan_pinjaman" placeholder="0">
                        </div>
                        <div class="form-text d-flex align-items-center gap-1 mt-1">
                            <i class="bi bi-info-circle text-muted"></i>
                            <span class="text-muted">
                                Sisa pinjaman karyawan ini:
                                <strong class="{{ $sisaPinjaman > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($sisaPinjaman, 0, ',', '.') }}
                                </strong>.
                                Nominal potongan otomatis tercatat sebagai <em>pengembalian pinjaman</em> &amp; mengurangi sisa.
                            </span>
                        </div>
                        @error('potongan_pinjaman')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Total -->
                    <div class="col-md-12 mb-1">
                        <label for="total" class="form-label fw-semibold">Total Gaji Diterima</label>
                        <div class="rp-wrap">
                            <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                                style="pointer-events: none; z-index: 5;">
                                Rp
                            </span>
                            <input type="text" id="total"
                                class="form-control form-control-lg readonly-total"
                                value="{{ $total ? number_format((int)$total, 0, ',', '.') : '' }}" readonly>
                        </div>
                        @error('total')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!--================== DATA LAINNYA ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4 form-section">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-blue flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                        <i class="bi bi-info-circle"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Informasi Lainnya</h5>
                </div>

                <div class="row">
                    <!-- Status -->
                    <div class="col-md-12 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" wire:model.defer="status"
                            class="form-select @error('status') is-invalid @enderror">
                            <option value="">-- Pilih Status --</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea id="deskripsi" wire:model.defer="deskripsi" rows="3"
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            placeholder="Masukkan catatan / deskripsi (opsional)"></textarea>
                        @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!--================== TOMBOL ==================-->
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span>{{ $this->mode === 'create' ? 'Tambah Data' : 'Simpan Perubahan' }}</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // ================= FORMAT RUPIAH =================
    function formatRupiah(angka) {
        let numberString = angka.toString().replace(/[^,\d]/g, "");
        let sisa = numberString.length % 3;
        let rupiah = numberString.substr(0, sisa);
        let ribuan = numberString.substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return rupiah ? 'Rp ' + rupiah : '';
    }

    // ================= AUTO FORMAT INPUT RUPIAH (tanpa "Rp", prefix terpisah) =================
    document.querySelectorAll('.rupiah').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^,\d]/g, "");
            e.target.value = formatNumber(value);

            hitungTotal(); // setiap kali input berubah, total dihitung ulang
        });
    });

    // ================= HITUNG TOTAL =================
    function hitungTotal() {
        const ambil = (id) => parseInt((document.getElementById(id)?.value || '').replace(/[^,\d]/g, "")) || 0;

        // Pendapatan
        let pendapatan =
            ambil('gaji_pokok') +
            ambil('bonus_kinerja') +
            ambil('bonus_lainnya') +
            ambil('uang_lembur') +
            ambil('tunjangan_kesehatan') +
            ambil('tunjangan_thr') +
            ambil('tunjangan_ketenagakerjaan') +
            ambil('tunjangan_lainnya') +
            ambil('tunjangan_transport') +
            ambil('tunjangan_makan');

        // Potongan
        let potongan =
            ambil('potongan') +
            ambil('potongan_bpjs_kesehatan') +
            ambil('potongan_bpjs_ketenagakerjaan') +
            ambil('potongan_pinjaman') +
            ambil('pph21');

        let total = pendapatan - potongan;

        // tampilkan di input total (angka saja, "Rp" sudah jadi prefix)
        document.getElementById('total').value = formatNumber(total);

        // update ke Livewire (biar tersimpan juga di backend)
        @this.set('total', total);
    }

    // Format angka dengan pemisah ribuan TANPA "Rp" (Rp ada di prefix terpisah)
    function formatNumber(angka) {
        let n = angka.toString().replace(/[^,\d]/g, "");
        let sisa = n.length % 3;
        let hasil = n.substr(0, sisa);
        let ribuan = n.substr(sisa).match(/\d{3}/gi);
        if (ribuan) {
            let sep = sisa ? '.' : '';
            hasil += sep + ribuan.join('.');
        }
        return hasil;
    }

    // ================= HITUNG UANG LEMBUR (jam x tarif) =================
    function hitungLembur() {
        const jamEl = document.getElementById('jam_lembur');
        const tarifEl = document.getElementById('tarif_lembur');
        const uangEl = document.getElementById('uang_lembur');
        if (!uangEl) return;

        let jam = parseInt((jamEl?.value || '').replace(/[^0-9]/g, "")) || 0;
        let tarif = parseInt((tarifEl?.value || '').replace(/[^,\d]/g, "")) || 0;

        // rapikan tampilan tarif (tanpa "Rp", karena Rp sudah jadi prefix)
        if (tarifEl && document.activeElement === tarifEl) {
            tarifEl.value = formatNumber(tarif);
        }

        uangEl.value = formatNumber(jam * tarif);
        hitungTotal();
    }

    document.getElementById('jam_lembur')?.addEventListener('input', hitungLembur);
    document.getElementById('tarif_lembur')?.addEventListener('input', hitungLembur);

    // Panggil saat halaman pertama kali load (jaga2 kalau edit data lama)
    document.addEventListener('DOMContentLoaded', function() {
        hitungLembur();
        hitungTotal();
    });
</script>
@endpush
