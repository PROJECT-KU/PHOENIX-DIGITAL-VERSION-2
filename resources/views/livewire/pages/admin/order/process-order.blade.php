<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Proses Pesanan</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pesanan', 'url' => route('admin.pesanantoko.index')],
        ['name' => 'Detail Pesanan', 'url' => route('admin.pesanantoko.detail', $order)],
        ['name' => 'Proses Pesanan'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="mb-0 card">
            <div class="card-body">
                <a wire:navigate href="{{ route('admin.pesanantoko.detail', $order) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if (session()->has('info'))
            <div class="alert alert-info alert-dismissible fade show">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif


            <div class="mb-4 card">
                <div class="card-body bg-body rounded-4">
                    <div>
                        <h4>{{ $order->order_number }}</h4>
                        <div class="gap-5 mt-4 d-flex align-items-center">
                            <div class="mb-2">
                                <strong>Nama Produk:</strong><br>
                                {{ $orderItem->product_name }}
                            </div>
                            <div class="mb-2">
                                <strong>Durasi:</strong><br>
                                {{ $orderItem->getDurationLabel() }}
                            </div>
                            <div class="mb-2">
                                <strong>Harga:</strong><br>
                                Rp {{ number_format($orderItem->price, 0, ',', '.') }}
                            </div>
                            <div class="mb-2">
                                <strong>Jumlah:</strong><br>
                                {{ $orderItem->quantity }}
                            </div>
                        </div>
                    </div>
                </div>
                <form wire:submit="processOrder">
                    <div class="col-md-12">
                        <!-- Pilih Data Akun -->
                        <div class="mb-3 card">
                            <div class="card-body">
                                <h6 class="mb-3 card-title">Pilih Akun Premium</h6>

                                <div class="mb-3">
                                    <label class="form-label">Data Akun Tersedia</label>
                                    <select class="form-select @error('selectedDataAkunId') is-invalid @enderror"
                                        wire:model.live="selectedDataAkunId">
                                        <option value="">-- Pilih atau isi manual di bawah --</option>
                                        @foreach ($availableAccounts as $akun)
                                        <option value="{{ $akun->id }}">
                                            {{ $akun->nama_akun }} - {{ $akun->username }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('selectedDataAkunId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Pilih dari akun yang tersedia atau isi manual di form bawah
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username / Email Akun *</label>
                                    <input type="text"
                                        class="form-control @error('accountUsername') is-invalid @enderror"
                                        wire:model="accountUsername" placeholder="username@example.com">
                                    @error('accountUsername')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password Akun *</label>
                                    <input type="text"
                                        class="form-control @error('accountPassword') is-invalid @enderror"
                                        wire:model="accountPassword" placeholder="Password akun premium">
                                    @error('accountPassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Link Akses</label>
                                    <input type="url"
                                        class="form-control @error('accountLink') is-invalid @enderror"
                                        wire:model="accountLink" placeholder="https://example.com/login">
                                    @error('accountLink')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Catatan untuk Pelanggan</label>
                                    <textarea class="form-control @error('accountNotes') is-invalid @enderror" wire:model="accountNotes" rows="3"
                                        placeholder="Catatan tambahan untuk pelanggan (opsional)"></textarea>
                                    @error('accountNotes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Periode Berlangganan -->
                        <div class="mb-3 card">
                            <div class="card-body">
                                <h6 class="mb-3 card-title">Periode Berlangganan</h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Mulai *</label>
                                            <input type="date"
                                                class="form-control @error('startDate') is-invalid @enderror"
                                                wire:model.live="startDate">
                                            @error('startDate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Akhir</label>
                                            <input type="date" class="form-control" wire:model="endDate" readonly>
                                            <small class="text-muted">
                                                Otomatis dihitung dari tanggal mulai +
                                                {{ $orderItem->getDurationLabel() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status Pembelian *</label>
                                    <select class="form-select @error('subscriptionStatus') is-invalid @enderror"
                                        wire:model="subscriptionStatus">
                                        <option value="baru">Baru (Pembelian pertama kali)</option>
                                        <option value="perpanjang">Perpanjang (Perpanjangan akun lama)</option>
                                        <option value="pengganti">Pengganti (Ganti akun bermasalah)</option>
                                    </select>
                                    @error('subscriptionStatus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Admin -->
                        <div class="mb-3 card">
                            <div class="card-body">
                                <h6 class="mb-3 card-title">Catatan Internal (Admin)</h6>

                                <div class="mb-3">
                                    <label class="form-label">Catatan Proses</label>
                                    <textarea class="form-control @error('processingNotes') is-invalid @enderror" wire:model="processingNotes"
                                        rows="3" placeholder="Catatan internal untuk admin (tidak dilihat customer)"></textarea>
                                    @error('processingNotes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0 alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Admin yang memproses:</strong> {{ auth()->user()->name }}<br>
                                    <strong>Waktu proses:</strong> {{ now()->format('d F Y, H:i') }} WIB
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="gap-2 d-flex justify-content-end">
                            <button type="button" wire:click="cancelProcessing" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle"></i> Proses & Lanjut ke Pengiriman
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm"></span>
                                    Memproses...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>