<div>
    <form wire:submit.prevent="save" class="p-3">

        <!--================== data karyawan ==================-->
        <div class="card mb-4">
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light fw-semibold">
                    <i class="bi bi-person me-2 text-primary"></i> Data Karyawan
                </div>
                <div class="card-body">
                    <div class="row">

                        <!-- Nama Karyawan -->
                        <div class="col-md-12 mt-3 mb-3">
                            <label class="form-label">Nama Karyawan <span class="text-danger">*</span></label>

                            @if(isset($users) && $users->count())
                            <select wire:model.defer="nama_karyawan" class="form-select @error('nama_karyawan') is-invalid @enderror">
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
                            <label for="tunjangan" class="form-label">
                                Nama Bank
                            </label>
                            <input type="text" wire:model="tunjangan"
                                class="form-control @error('tunjangan') is-invalid @enderror rupiah bg-light" id="tunjangan" readonly>
                            @error('tunjangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tunjangan" class="form-label">
                                No Rekening
                            </label>
                            <input type="text" wire:model="tunjangan"
                                class="form-control @error('tunjangan') is-invalid @enderror rupiah bg-light" id="tunjangan" readonly>
                            @error('tunjangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Transaksi -->
                        <div class="col-md-12 mb-3">
                            <label for="tanggal_transaksi" class="form-label">
                                Tanggal Transaksi <span class="text-danger">*</span>
                            </label>
                            <input type="date" wire:model="tanggal_transaksi"
                                class="form-control @error('tanggal_transaksi') is-invalid @enderror" id="tanggal_transaksi">
                            @error('tanggal_transaksi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!--================== END ==================-->

        <!--================== data gaji ==================-->
        <div class="card mb-4">
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light fw-semibold">
                    <i class="bi bi-currency-exchange me-2 text-primary"></i> Data Gaji
                </div>
                <div class="card-body">
                    <div class="row">

                        <!-- Gaji Pokok -->
                        <div class="col-md-12 mb-3 mt-3">
                            <label for="gaji_pokok" class="form-label">
                                Gaji Pokok <span class="text-danger">*</span>
                            </label>
                            <input type="text" wire:model="gaji_pokok"
                                class="form-control @error('gaji_pokok') is-invalid @enderror rupiah" id="gaji_pokok" placeholder="Masukkan nominal">
                            @error('gaji_pokok')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bonus kinerja -->
                        <div class="col-md-6 mb-3">
                            <label for="bonus_kinerja" class="form-label">
                                Bonus Kinerja
                            </label>
                            <input type="text" wire:model="bonus_kinerja"
                                class="form-control @error('bonus_kinerja') is-invalid @enderror rupiah" id="bonus_kinerja" placeholder="Masukkan nominal">
                            @error('bonus_kinerja')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bonus Lainnya -->
                        <div class="col-md-6 mb-3">
                            <label for="bonus_lainnya" class="form-label">
                                Bonus Lainnya
                            </label>
                            <input type="text" wire:model="bonus_lainnya"
                                class="form-control @error('bonus_lainnya') is-invalid @enderror rupiah" id="bonus_lainnya" placeholder="Masukkan nominal">
                            @error('bonus_lainnya')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- tunjangan kesehatan -->
                        <div class="col-md-6 mb-3">
                            <label for="tunjangan_kesehatan" class="form-label">
                                Tunjangan Kesehatan
                            </label>
                            <input type="text" wire:model="tunjangan_kesehatan"
                                class="form-control @error('tunjangan_kesehatan') is-invalid @enderror rupiah" id="tunjangan_kesehatan" placeholder="Masukkan nominal">
                            @error('tunjangan_kesehatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tunjangan THR -->
                        <div class="col-md-6 mb-3">
                            <label for="tunjangan_thr" class="form-label">
                                Tunjangan THR
                            </label>
                            <input type="text" wire:model="tunjangan_thr"
                                class="form-control @error('tunjangan_thr') is-invalid @enderror rupiah" id="tunjangan_thr" placeholder="Masukkan nominal">
                            @error('tunjangan_thr')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tunjangan ketenagakerjaan -->
                        <div class="col-md-6 mb-3">
                            <label for="tunjangan_ketenagakerjaan" class="form-label">
                                Tunjangan Ketenagakerjaan
                            </label>
                            <input type="text" wire:model="tunjangan_ketenagakerjaan"
                                class="form-control @error('tunjangan_ketenagakerjaan') is-invalid @enderror rupiah" id="tunjangan_ketenagakerjaan" placeholder="Masukkan nominal">
                            @error('tunjangan_ketenagakerjaan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tunjangan lainnya -->
                        <div class="col-md-6 mb-3">
                            <label for="tunjangan_lainnya" class="form-label">
                                Tunjangan Lainnya
                            </label>
                            <input type="text" wire:model="tunjangan_lainnya"
                                class="form-control @error('tunjangan_lainnya') is-invalid @enderror rupiah" id="tunjangan_lainnya" placeholder="Masukkan nominal">
                            @error('tunjangan_lainnya')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!--================== END ==================-->

        <!--================== data potongan ==================-->
        <div class="card mb-4">
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light fw-semibold">
                    <i class="bi bi-currency-exchange me-2 text-primary"></i> Data Potongan
                </div>
                <div class="card-body">
                    <div class="row">

                        <!-- Potongan -->
                        <div class="col-md-6 mb-3 mt-3">
                            <label for="potongan" class="form-label">
                                Potongan
                            </label>
                            <input type="text" wire:model="potongan"
                                class="form-control @error('potongan') is-invalid @enderror rupiah" id="potongan" placeholder="Masukkan nominal">
                            @error('potongan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- pph21 -->
                        <div class="col-md-6 mb-3 mt-3">
                            <label for="pph21" class="form-label">
                                PPH 21
                            </label>
                            <input type="text" wire:model="pph21"
                                class="form-control @error('pph21') is-invalid @enderror rupiah" id="pph21" placeholder="Masukkan nominal">
                            @error('pph21')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Total -->
                        <div class="col-md-12 mb-3">
                            <label for="total" class="form-label">
                                Total
                            </label>
                            <input type="text"
                                id="total"
                                class="form-control rupiah bg-light"
                                value="{{ $total ? 'Rp ' . number_format((int)$total, 0, ',', '.') : '' }}"
                                readonly>
                            @error('total')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!--================== END ==================-->

        <!--================== data lainnya ==================-->
        <div class="card mb-4">
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light fw-semibold">
                    <i class="bi bi-currency-exchange me-2 text-primary"></i> Data Lainnya
                </div>
                <div class="card-body">
                    <div class="row">

                        <!-- status -->
                        <div class="col-md-12 mb-3 mt-3">
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
                                placeholder="Masukkan deskripsi produk"></textarea>
                            @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!--================== END ==================-->
        <!-- Tombol -->
        <div class="col-12">
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-send me-1"></i>
                {{ $this->mode === 'create' ? 'Tambah Data' : 'Simpan Perubahan' }}
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

    // ================= AUTO FORMAT INPUT RUPIAH =================
    document.querySelectorAll('.rupiah').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^,\d]/g, "");
            e.target.value = formatRupiah(value);

            hitungTotal(); // setiap kali input berubah, total dihitung ulang
        });
    });

    // ================= HITUNG TOTAL =================
    function hitungTotal() {
        // ambil nilai tiap field, kalau kosong dianggap 0
        let gaji_pokok = parseInt(document.getElementById('gaji_pokok').value.replace(/[^,\d]/g, "")) || 0;
        let bonus_kinerja = parseInt(document.getElementById('bonus_kinerja').value.replace(/[^,\d]/g, "")) || 0;
        let bonus_lainnya = parseInt(document.getElementById('bonus_lainnya').value.replace(/[^,\d]/g, "")) || 0;
        let tunjangan_kesehatan = parseInt(document.getElementById('tunjangan_kesehatan').value.replace(/[^,\d]/g, "")) || 0;
        let tunjangan_thr = parseInt(document.getElementById('tunjangan_thr').value.replace(/[^,\d]/g, "")) || 0;
        let tunjangan_ketenagakerjaan = parseInt(document.getElementById('tunjangan_ketenagakerjaan').value.replace(/[^,\d]/g, "")) || 0;
        let tunjangan_lainnya = parseInt(document.getElementById('tunjangan_lainnya').value.replace(/[^,\d]/g, "")) || 0;
        let potongan = parseInt(document.getElementById('potongan').value.replace(/[^,\d]/g, "")) || 0;
        let pph21 = parseInt(document.getElementById('pph21').value.replace(/[^,\d]/g, "")) || 0;

        let total = gaji_pokok + bonus_kinerja + bonus_lainnya + tunjangan_kesehatan + tunjangan_thr + tunjangan_ketenagakerjaan + tunjangan_lainnya - potongan - pph21;

        // tampilkan di input total
        document.getElementById('total').value = formatRupiah(total);

        // update ke Livewire (biar tersimpan juga di backend)
        @this.set('total', total);
    }

    // Panggil saat halaman pertama kali load (jaga2 kalau edit data lama)
    document.addEventListener('DOMContentLoaded', function() {
        hitungTotal();
    });
</script>
@endpush