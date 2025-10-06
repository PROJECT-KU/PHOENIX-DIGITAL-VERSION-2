<div>
    <form wire:submit="save" x-cloak>

        <!--================== Data kategori ==================-->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-4 fw-bold">
                    <i class="bi bi-folder2-open me-2 text-primary"></i> Data Kategori
                </h5>

                <div class="row">
                    <!-- Nama Camp -->
                    <div class="col-md-6 mb-3">
                        <label for="nama_camp" class="form-label">
                            Nama Kategori <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="nama_camp"
                            class="form-control @error('nama_camp') is-invalid @enderror" id="nama_camp"
                            placeholder="contoh: Scopus Camp">
                        @error('nama_camp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Batch -->
                    <div class="col-md-6 mb-3">
                        <label for="batch_camp" class="form-label">
                            Batch <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">#</span>
                            <input type="number" wire:model="batch_camp"
                                class="form-control @error('batch_camp') is-invalid @enderror" id="batch_camp"
                                placeholder="contoh: 3">
                            @error('batch_camp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai_camp" class="form-label">
                            Tanggal Mulai <span class="text-danger">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_mulai_camp"
                            class="form-control @error('tanggal_mulai_camp') is-invalid @enderror"
                            id="tanggal_mulai_camp">
                        @error('tanggal_mulai_camp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Berakhir -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_akhir_camp" class="form-label">
                            Tanggal Berakhir <span class="text-danger">*</span>
                        </label>
                        <input type="date" wire:model="tanggal_akhir_camp"
                            class="form-control @error('tanggal_akhir_camp') is-invalid @enderror"
                            id="tanggal_akhir_camp">
                        @error('tanggal_akhir_camp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <!--================== End Data kategori ==================-->

        <!--================== Data pembeli ==================-->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-4 fw-bold">
                    <i class="bi bi-person-vcard me-2 text-success"></i> Data Pembeli
                </h5>

                <div class="row">
                    <!-- Nama Pembeli -->
                    <div class="col-md-6 mb-3">
                        <label for="nama_pembeli" class="form-label">
                            Nama Pembeli <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="nama_pembeli"
                            class="form-control @error('nama_pembeli') is-invalid @enderror" id="nama_pembeli"
                            placeholder="contoh: Budi Santoso">
                        @error('nama_pembeli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nomor Telepon -->
                    <div class="col-md-6 mb-3">
                        <label for="telp_pembeli" class="form-label">
                            Nomor Telepon <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model="telp_pembeli"
                            class="form-control @error('telp_pembeli') is-invalid @enderror" id="telp_pembeli"
                            placeholder="awali dengan +62">
                        @error('telp_pembeli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jumlah Pesanan -->
                    <div class="col-md-4 mb-3">
                        <label for="jumlah_pemesanan" class="form-label">
                            Jumlah Pesanan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="jumlah_pemesanan" name="jumlah_pemesanan"
                                class="form-control @error('jumlah_pemesanan') is-invalid @enderror"
                                placeholder="Masukkan jumlah dalam bulan">
                            <span class="input-group-text">Bulan</span>
                            @error('jumlah_pemesanan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tanggal Pemesanan -->
                    <div class="col-md-4 mb-3">
                        <label for="tanggal_pemesanan" class="form-label">
                            Tanggal Pemesanan <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="tanggal_pemesanan" name="tanggal_pemesanan"
                            class="form-control @error('tanggal_pemesanan') is-invalid @enderror">
                        @error('tanggal_pemesanan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Berakhir -->
                    <div class="col-md-4 mb-3">
                        <label for="tanggal_berakhir" class="form-label">
                            Tanggal Berakhir
                        </label>
                        <input type="date" id="tanggal_berakhir" name="tanggal_berakhir"
                            class="form-control @error('tanggal_berakhir') is-invalid @enderror" readonly>
                        @error('tanggal_berakhir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>
        <!--================== end Data pembeli ==================-->

        <!--================== Data akun ==================-->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-4 fw-bold">
                    <i class="bi bi-person-badge me-2 text-success"></i> Data Akun
                </h5>

                <div class="alert alert-info mt-3">
                    <strong>Debug:</strong><br>
                    Akun dipilih: {{ $akun ?? 'kosong' }}<br>
                    Username: {{ $username ?? 'kosong' }}<br>
                    Password: {{ $password ?? 'kosong' }}<br>
                    Link: {{ $link_akses ?? 'kosong' }}
                </div>


                <div class="row">
                    <!-- Nama akun -->
                    <div class="mb-3">
                        <label for="akun" class="form-label">Pilih Akun</label>
                        <select wire:model.live="akun" id="akun" class="form-control">
                            <option value="">-- Pilih Akun --</option>
                            @foreach ($akuns as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- username akun -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" wire:model="username" class="form-control" readonly>
                    </div>

                    <!-- password akun -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" wire:model="password" class="form-control" readonly>
                    </div>

                    <!-- link akses -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Link Akses</label>
                        <input type="text" wire:model="link_akses" class="form-control" readonly>
                    </div>

                    <!-- harga satuan -->
                    <div class="col-md-6 mb-3">
                        <label for="harga_satuan" class="form-label">Harga Satuan</label>
                        <input type="text" id="harga_satuan" class="form-control" readonly>
                    </div>

                    <!-- total -->
                    <div class="col-md-6 mb-3">
                        <label for="total" class="form-label">Total</label>
                        <input type="text" id="total" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>
        <!--================== end Data akun ==================-->

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-send me-1"></i>
                {{ $this->mode === 'create' ? 'Tambah Data' : 'Simpan Perubahan' }}
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jumlahInput = document.getElementById('jumlah_pemesanan');
        const tanggalMulaiInput = document.getElementById('tanggal_pemesanan');
        const tanggalBerakhirInput = document.getElementById('tanggal_berakhir');

        // Fungsi untuk menghitung tanggal berakhir
        function hitungTanggalBerakhir() {
            const jumlah = parseInt(jumlahInput.value);
            const tanggalMulai = tanggalMulaiInput.value;

            if (!isNaN(jumlah) && tanggalMulai) {
                let startDate = new Date(tanggalMulai);
                startDate.setMonth(startDate.getMonth() + jumlah);

                // Format ke yyyy-mm-dd
                const year = startDate.getFullYear();
                const month = String(startDate.getMonth() + 1).padStart(2, '0');
                const day = String(startDate.getDate()).padStart(2, '0');
                const formatted = `${year}-${month}-${day}`;

                tanggalBerakhirInput.value = formatted;
            } else {
                tanggalBerakhirInput.value = '';
            }
        }

        // Jalankan otomatis setiap kali input berubah
        jumlahInput.addEventListener('input', hitungTanggalBerakhir);
        tanggalMulaiInput.addEventListener('change', hitungTanggalBerakhir);

        // Set default tanggal_pemesanan ke hari ini
        const today = new Date().toISOString().split('T')[0];
        tanggalMulaiInput.value = today;
        hitungTanggalBerakhir(); // langsung hitung saat load awal
    });
</script>
