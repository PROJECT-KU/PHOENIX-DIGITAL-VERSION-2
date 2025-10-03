<div>
    <form wire:submit.prevent="save" class="p-3">
        <div class="row">

            <!-- Nama Karyawan -->
            <div class="col-md-6">
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

            <!-- Tanggal Transaksi -->
            <div class="col-md-6 mb-3">
                <label for="tanggal_transaksi" class="form-label">
                    Tanggal Transaksi <span class="text-danger">*</span>
                </label>
                <input type="date" wire:model="tanggal_transaksi"
                    class="form-control @error('tanggal_transaksi') is-invalid @enderror" id="tanggal_transaksi">
                @error('tanggal_transaksi')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Gaji Pokok -->
            <div class="col-md-12 mb-3">
                <label for="gaji_pokok" class="form-label">
                    Gaji Pokok <span class="text-danger">*</span>
                </label>
                <input type="text" wire:model="gaji_pokok"
                    class="form-control @error('gaji_pokok') is-invalid @enderror rupiah" id="gaji_pokok" placeholder="Masukkan nominal">
                @error('gaji_pokok')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Bonus -->
            <div class="col-md-4 mb-3">
                <label for="bonus" class="form-label">
                    Bonus
                </label>
                <input type="text" wire:model="bonus"
                    class="form-control @error('bonus') is-invalid @enderror rupiah" id="bonus" placeholder="Masukkan nominal">
                @error('bonus')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tunjangan -->
            <div class="col-md-4 mb-3">
                <label for="tunjangan" class="form-label">
                    Tunjangan
                </label>
                <input type="text" wire:model="tunjangan"
                    class="form-control @error('tunjangan') is-invalid @enderror rupiah" id="tunjangan" placeholder="Masukkan nominal">
                @error('tunjangan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Bonus Lainnya -->
            <div class="col-md-4 mb-3">
                <label for="bonus_lainnya" class="form-label">
                    Bonus Lainnya
                </label>
                <input type="text" wire:model="bonus_lainnya"
                    class="form-control @error('bonus_lainnya') is-invalid @enderror rupiah" id="bonus_lainnya" placeholder="Masukkan nominal">
                @error('bonus_lainnya')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Potongan -->
            <div class="col-md-6 mb-3">
                <label for="potongan" class="form-label">
                    Potongan
                </label>
                <input type="text" wire:model="potongan"
                    class="form-control @error('potongan') is-invalid @enderror rupiah" id="potongan" rupiah>
                @error('potongan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Total -->
            <div class="col-md-6 mb-3">
                <label for="total" class="form-label">
                    Total
                </label>
                <input type="text"
                    id="total"
                    class="form-control rupiah"
                    value="{{ $total ? 'Rp ' . number_format((int)$total, 0, ',', '.') : '' }}"
                    readonly>
                @error('total')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>


            <!-- status -->
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
                    placeholder="Masukkan deskripsi produk"></textarea>
                @error('deskripsi')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tombol -->
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
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
        let bonus = parseInt(document.getElementById('bonus').value.replace(/[^,\d]/g, "")) || 0;
        let tunjangan = parseInt(document.getElementById('tunjangan').value.replace(/[^,\d]/g, "")) || 0;
        let bonus_lainnya = parseInt(document.getElementById('bonus_lainnya').value.replace(/[^,\d]/g, "")) || 0;
        let potongan = parseInt(document.getElementById('potongan').value.replace(/[^,\d]/g, "")) || 0;

        let total = gaji_pokok + bonus + tunjangan + bonus_lainnya - potongan;

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