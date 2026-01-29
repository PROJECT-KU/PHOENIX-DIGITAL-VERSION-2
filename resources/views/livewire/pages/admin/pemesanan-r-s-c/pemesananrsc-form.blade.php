<div>
    <form wire:submit="save" x-cloak>
        <!--================== Data kategori ==================-->
        <div class="card mb-4">
            <div class="card mb-4">
                @if($mode === 'create')
                <div class="card-body border-top bg-light">
                    <label class="fw-bold mb-2"><i class="bi bi-file-earmark-spreadsheet text-success"></i> Import Data Peserta (Excel)</label>
                    <div class="input-group">
                        <input type="file" wire:model="file_excel" class="form-control">
                        <div wire:loading wire:target="file_excel" class="input-group-text">
                            <span class="spinner-border spinner-border-sm" role="status"></span> Loading...
                        </div>
                    </div>
                    <small class="text-muted">Format: Kolom A (Nama), Kolom B (No Telp). Row 1 dianggap header.</small>
                </div>
                @endif
            </div>
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light fw-semibold">
                    <i class="bi bi-folder2-open me-2 text-primary"></i> Data Kategori
                </div>
                <div class="card-body">
                    <div class="row">

                        <!-- Nama Camp -->
                        <div class="col-md-6 mb-3 mt-3">
                            <label for="nama_camp" class="form-label">
                                Nama Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text" wire:model="nama_camp"
                                class="form-control @error('nama_camp') is-invalid @enderror" id="nama_camp"
                                placeholder="contoh: Scopus Camp Yogyakarta">
                            @error('nama_camp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Batch -->
                        <div class="col-md-6 mb-3 mt-3">
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

            <!--================== Data akun ==================-->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light fw-semibold">
                    <i class="bi bi-person-badge me-2 text-success"></i> Data Akun
                </div>
                <div class="card-body">
                    <div class="row">

                        <!-- Nama akun -->
                        <div class="mb-3 mt-3">
                            <label for="akun" class="form-label">Pilih Akun <span class="text-danger">*</span></label>
                            <select wire:model.live="akun" id="akun" class="form-control">
                                <option value="">-- Pilih Akun --</option>
                                @foreach ($akuns as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- username akun -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" wire:model="username" class="form-control" readonly>
                        </div>

                        <!-- password akun -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="text" wire:model="password" class="form-control" readonly>
                        </div>

                        <!-- link akses -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Link Akses</label>
                            <input type="text" wire:model="link_akses" class="form-control" readonly>
                        </div>

                        <!-- harga satuan -->
                        <div class="col-md-6 mb-3">
                            <label for="harga_satuan" class="form-label">Harga Satuan</label>
                            <input type="text" wire:model="harga_satuan" x-nominal x-currency id="harga_satuan"
                                class="form-control bg-light" readonly>
                        </div>

                    </div>
                </div>
            </div>
            <!--================== end Data akun ==================-->

            <!--================== Data pembeli ==================-->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light fw-semibold d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-start">
                        <i class="bi bi-person-vcard me-2 text-success"></i>
                        <span>Data Pembeli</span>
                    </span>
                    @if($mode === 'create')
                    <button type="button" wire:click="addPeserta" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Peserta
                    </button>
                    @endif
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive" wire:loading.class="opacity-50" wire:target="removePeserta">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width=" 5%">No</th>
                                    <th width="45%">Nama Pembeli <span class="text-danger">*</span></th>
                                    <th width="40%">No. Telepon <span class="text-danger">*</span></th>
                                    @if($mode === 'create')
                                    <th width="10%">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peserta as $tmpId => $p)
                                <tr wire:key="row-{{$tmpId}}">
                                    <td class="align-middle text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <input type="text"
                                            wire:model.defer="peserta.{{ $tmpId }}.nama_pembeli"
                                            class="form-control @error('peserta.'.$tmpId.'.nama_pembeli') is-invalid @enderror"
                                            placeholder="Nama Peserta">
                                        @error('peserta.'.$tmpId.'.nama_pembeli')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="text"
                                            wire:model.defer="peserta.{{ $tmpId }}.telp_pembeli"
                                            class="form-control @error('peserta.'.$tmpId.'.telp_pembeli') is-invalid @enderror"
                                            placeholder="0812..."
                                            onkeypress="filterPhoneNumberInput(event)"> {{-- Gunakan fungsi JS Anda --}}
                                    </td>
                                    @if($mode === 'create')
                                    <td class="align-middle text-center">
                                        @if(count($peserta) > 1)
                                        <button type="button" wire:click="removePeserta('{{ $tmpId }}')" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary Peserta --}}
                    <div class="p-3 bg-light text-end border-top">
                        <strong>Total Peserta: {{ count($peserta) }} orang</strong>
                    </div>

                    <div class="row mt-3">
                        <!-- Jumlah Pesanan -->
                        <div class="col-md-4 mb-3">
                            <label for="jumlah_pemesanan" class="form-label">
                                Jumlah Pesanan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" id="jumlah_pemesanan" wire:model.live="jumlah_pemesanan"
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
                            <input type="date" id="tanggal_pemesanan" wire:model.live="tanggal_pemesanan"
                                class="form-control @error('tanggal_pemesanan') is-invalid @enderror">
                            @error('tanggal_pemesanan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Berakhir (Auto calculated) -->
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_berakhir" class="form-label">
                                Tanggal Berakhir
                            </label>
                            <input type="date" id="tanggal_berakhir" wire:model="tanggal_berakhir"
                                class="form-control bg-light" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <!--================== end Data pembeli ==================-->

            <!--================== Data lainnya ==================-->
            <div class="card shadow-sm border mb-4">
                <div class="card-header bg-light fw-semibold">
                    <i class="bi bi-person-vcard me-2 text-success"></i> Data Lainnya
                </div>

                <div class="card-body">
                    <div class="row">

                        <!-- Total -->
                        <div class="col-md-12 mb-3 mt-3">
                            <label class="mb-2">Estimasi Total Pembayaran untuk <strong>{{ count($peserta) }} Peserta</label>
                            <div class="form-control bg-success text-white fw-bold">
                                Rp {{ number_format($this->grand_total, 0, ',', '.') }}
                            </div>
                        </div>

                        <!-- PIC -->
                        <div class="col-md-6 mb-3">
                            <label for="pic" class="form-label">Pilih PIC <span class="text-danger">*</span></label>
                            <select wire:model="pic" id="pic" class="form-control">
                                <option value="">-- Pilih PIC --</option>
                                @foreach ($users as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Pilih Status <span class="text-danger">*</span></label>
                            <select wire:model="status" id="status" class="form-control">
                                <option value="">-- Pilih Status --</option>
                                <option value="habis">Habis</option>
                                <option value="pengganti">Pengganti</option>
                                <option value="perpanjang">Perpanjang</option>
                                <option value="baru">Baru</option>
                            </select>
                        </div>

                        <!-- Deskripsi -->
                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                                class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Masukkan deskripsi produk"></textarea>
                            @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
            <!--================== end Data lainnya ==================-->

            <div class="col-12">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-send me-1"></i>
                    {{ $this->mode === 'create' ? 'Tambah Data' : 'Simpan Perubahan' }}
                </button>
            </div>
    </form>
</div>

<!--================== FORMAT TELP ==================-->
<script>
    function formatPhoneNumber(input) {
        // Jika user ketik 0 di awal, ubah otomatis menjadi +62
        if (input.value.startsWith('0')) {
            input.value = '+62' + input.value.substring(1);
        }
    }

    function validatePhoneNumber(input) {
        const regex = /^\+62[0-9]{8,13}$/;
        const errorDiv = document.getElementById('telp_error');

        if (!regex.test(input.value)) {
            input.classList.add('is-invalid');
            errorDiv.textContent = 'Nomor telepon harus diawali +62 dan berisi 9–14 digit angka.';
        } else {
            input.classList.remove('is-invalid');
            errorDiv.textContent = '';
        }
    }

    // ✅ Batasi input agar hanya angka + simbol "+" (hanya di awal)
    function filterPhoneNumberInput(event) {
        const char = String.fromCharCode(event.which);

        // Izinkan backspace, delete, arrow keys
        if ([8, 37, 39, 46].includes(event.keyCode)) {
            return;
        }

        // Jika karakter pertama, boleh "+"
        if (event.target.value.length === 0 && char === '+') {
            return;
        }

        // Setelah karakter pertama, hanya boleh angka
        if (!/[0-9]/.test(char)) {
            event.preventDefault();
        }
    }
</script>
<!--================== END ==================-->