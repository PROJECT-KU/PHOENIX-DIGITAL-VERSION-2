<div>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .sp-picker-btn {
            cursor: pointer;
        }

        .sp-picker-btn::after {
            content: "\F282";
            font-family: "bootstrap-icons";
            float: right;
            color: #94a3b8;
            font-size: .8rem;
        }

        .sp-pick-list {
            max-height: 320px;
            overflow-y: auto;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            padding: .2rem;
        }

        .sp-pick-item {
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

        .sp-pick-item:hover {
            border-color: #6c63ff;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.04));
            transform: translateY(-1px);
        }

        .sp-pick-empty {
            text-align: center;
            color: #94a3b8;
            padding: 1.5rem;
            font-size: .9rem;
        }
    </style>

    <!-- Flash Messages -->
    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form wire:submit="save" x-data="{ jenis_pengeluaran: @entangle('jenis_pengeluaran') }" x-cloak>
        <div class="row">
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

            <!-- Nominal -->
            <div class="col-md-6 mb-3"
                x-data="{
                        formatRupiah(v) {
                            if (!v) return '';
                            let number_string = v.toString().replace(/[^,\d]/g, '');
                            let split = number_string.split(',');
                            let sisa = split[0].length % 3;
                            let rupiah = split[0].substr(0, sisa);
                            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                            if (ribuan) {
                                let separator = sisa ? '.' : '';
                                rupiah += separator + ribuan.join('.');
                            }
                            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                            return rupiah;
                        },
                        parseRaw(v) {
                            return (v || '').toString().replace(/[^0-9]/g, '');
                        }
                    }"
                x-init="$nextTick(() => {
                        // Inisialisasi tampilan jika Livewire sudah punya nilai awal
                        $refs.display.value = formatRupiah($wire.nominal);
                    })">
                <label for="nominal_display" class="form-label">
                    Nominal <span class="text-danger">*</span>
                </label>

                <!-- Input tampil ke user -->
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y text-secondary fw-bold ps-3"
                        style="pointer-events: none; z-index: 5;">
                        Rp
                    </span>
                    <input type="text"
                        id="nominal_display"
                        x-ref="display"
                        x-on:focus="$event.target.select()"
                        x-on:input="
                                let raw = parseRaw($event.target.value);
                                $event.target.value = formatRupiah(raw);
                                $wire.set('nominal', raw);
                        "
                        class="form-control @error('nominal') is-invalid @enderror"
                        style="padding-left: 45px;"
                        placeholder="0">
                </div>

                <!-- Pesan error dari Livewire -->
                @error('nominal')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>


            <!-- Status -->
            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">
                    Status <span class="text-danger">*</span>
                </label>
                <select wire:model="status" class="form-select @error('status') is-invalid @enderror"
                    id="status">
                    <option value="">Pilih Status</option>
                    @foreach ($statusOptions as $option)
                    <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Jenis Pengeluaran -->
            <div class="col-md-6 mb-3">
                <label for="jenis_pengeluaran" class="form-label">
                    Jenis Pengeluaran <span class="text-danger">*</span>
                </label>
                <select wire:model="jenis_pengeluaran"
                    class="form-select @error('jenis_pengeluaran') is-invalid @enderror" id="jenis_pengeluaran">
                    <option value="">Pilih Jenis Pengeluaran</option>
                    @foreach ($jenisPengeluaran as $jenis)
                    <option value="{{ $jenis }}">
                        {{ $jenis === 'pembelian_akun' ? 'Pembelian Akun' : 'Lainnya' }}
                    </option>
                    @endforeach
                </select>
                @error('jenis_pengeluaran')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- PIC: hanya untuk pembelian akun -->
        <div class="row" x-show="jenis_pengeluaran === 'pembelian_akun'" x-transition x-cloak>
            <!-- PIC Penginput (selalu dari akun yang login) -->
            <div class="col-md-6 mb-3">
                <label class="form-label">
                    PIC Penginput
                </label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                <div class="form-text text-muted">Otomatis diambil dari akun yang sedang login.</div>
            </div>

            <!-- PIC Pembeli -->
            <div class="col-md-6 mb-3">
                <label for="pic_pembeli_id" class="form-label">
                    PIC Pembeli <span class="text-danger">*</span>
                </label>
                <select wire:model="pic_pembeli_id"
                    class="form-select @error('pic_pembeli_id') is-invalid @enderror" id="pic_pembeli_id">
                    <option value="">Pilih PIC Pembeli</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('pic_pembeli_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Produk yang dibeli (dasar modal per produk) -->
            <div class="col-12 mb-3">
                <label for="product_id" class="form-label">
                    Produk (Akun) <span class="text-danger">*</span>
                </label>
                @php $selProduk = $products->firstWhere('id', $product_id); @endphp
                <button type="button" onclick="spProductPicker(this)"
                    class="form-select text-start sp-picker-btn @error('product_id') is-invalid @enderror" id="product_id">
                    @if ($selProduk)
                    <span class="text-dark">{{ $selProduk->nama_akun }}</span>
                    <span class="badge {{ $selProduk->tipe_akun === 'private' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-info-subtle text-info border border-info' }} ms-1">{{ ucfirst($selProduk->tipe_akun) }}</span>
                    @else
                    <span class="text-muted">-- Pilih Produk / Akun --</span>
                    @endif
                </button>
                <div class="form-text text-muted">Tipe akun mengikuti produk (sharing / private).</div>
                @error('product_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Durasi: hanya untuk produk PRIVATE (modal satuan per durasi) --}}
            @if ($selectedProductTipe === 'private')
            <div class="col-md-6 mb-3">
                <label class="form-label">Durasi <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" min="1" wire:model="durasi_value"
                        class="form-control @error('durasi_value') is-invalid @enderror" placeholder="mis. 1">
                    <select wire:model="durasi_type" class="form-select" style="max-width: 130px;">
                        <option value="bulan">Bulan</option>
                        <option value="tahun">Tahun</option>
                    </select>
                </div>
                <div class="form-text text-muted">
                    Nominal di atas = <b>modal 1 akun</b> untuk durasi ini. Otomatis dikali jumlah order pada periode.
                </div>
                @error('durasi_value') <div class="text-danger small">{{ $message }}</div> @enderror
                @error('durasi_type') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            @elseif ($selectedProductTipe === 'sharing')
            <div class="col-12 mb-2">
                <div class="alert alert-info py-2 mb-0" style="font-size: .82rem;">
                    <i class="bi bi-info-circle me-1"></i> Produk <b>sharing</b>: nominal dianggap <b>total modal</b> (bukan per akun), tanpa durasi.
                </div>
            </div>
            @endif
        </div>

        <!-- Deskripsi -->
        <div class="mb-3">
            <label for="deskripsi" class="form-label">
                Deskripsi
            </label>
            <textarea wire:model="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi"
                rows="4" placeholder="Masukkan deskripsi pengeluaran..."></textarea>
            @error('deskripsi')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                <span class="text-muted">Maksimal 1000 karakter</span>
                <span class="float-end text-muted">
                    {{ strlen($deskripsi ?? '') }}/1000
                </span>
            </div>
        </div>

        <!-- Buttons -->
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <button type="submit"
                class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                style="height: 52px;">
                <i class="bi bi-check2-circle me-2 fs-5"></i>
                <span>{{ $isEdit ? 'Update Data' : 'Simpan Data' }}</span>
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        (function () {
            // Data produk untuk picker (diperbarui tiap render)
            window.__spProducts = @json($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->nama_akun])->values());

            if (window.__spPickerBound) return;
            window.__spPickerBound = true;

            const spGlossy = {
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false,
                showConfirmButton: false,
                showCloseButton: true,
                width: 480,
                padding: '1.25rem',
            };

            window.spProductPicker = function (btn) {
                if (typeof Swal === 'undefined') return;
                const el = btn.closest('[wire\\:id]');
                if (!el) return;
                const cid = el.getAttribute('wire:id');
                const items = window.__spProducts || [];

                const rows = items.length
                    ? items.map(function (it) {
                        return '<button type="button" class="sp-pick-item" data-id="' + it.id + '" data-search="' + (it.name || '').toLowerCase() + '">' + it.name + '</button>';
                    }).join('')
                    : '<div class="sp-pick-empty">Tidak ada produk</div>';

                Swal.fire(Object.assign({
                    title: 'Pilih Produk / Akun',
                    html: '<input id="spPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari...">' +
                        '<div id="spPickList" class="sp-pick-list">' + rows + '</div>',
                    didOpen: function () {
                        const search = document.getElementById('spPickSearch');
                        const listEl = document.getElementById('spPickList');
                        if (search) {
                            search.addEventListener('input', function () {
                                const q = search.value.toLowerCase();
                                listEl.querySelectorAll('.sp-pick-item').forEach(function (b) {
                                    b.style.display = b.dataset.search.includes(q) ? '' : 'none';
                                });
                            });
                            setTimeout(function () { search.focus(); }, 100);
                        }
                        listEl.querySelectorAll('.sp-pick-item').forEach(function (b) {
                            b.addEventListener('click', function () {
                                if (window.Livewire) window.Livewire.find(cid).set('product_id', b.dataset.id);
                                Swal.close();
                            });
                        });
                    }
                }, spGlossy));
            };
        })();
    </script>
    @endpush
</div>