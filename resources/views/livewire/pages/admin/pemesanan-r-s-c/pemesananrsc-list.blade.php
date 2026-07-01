<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Data Pemesanan RSC</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pemesanan RSC']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('livewire.pages.admin.pemesanan-r-s-c.partials.filter')
                    <!-- Table -->
                    <div class="table-responsive">
                        <table id="productTable" class="table align-middle table-striped nowrap" style="width:100%">
                            <thead class="text-center table-light">
                                <tr style="text-align: center;">
                                    <th>Kategori</th>
                                    <th>Batch</th>
                                    <th>Akun</th>
                                    <th>Jumlah Peserta</th>
                                    <th>Status</th>
                                    <th>Total Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($pemesananrsc as $item)
                                <tr style="text-align: center;">
                                    <td>{{ $item->nama_camp }}</td>
                                    <td>#{{ $item->batch_camp }}</td>
                                    <td>{{ $item->dataakun?->nama_akun ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $item->total_peserta }} Peserta</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'baru' ? 'success' : ($item->status === 'habis' ? 'danger' : ($item->status === 'perpanjang' ? 'info' : 'warning')) }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                    <td>
                                        <div>
                                            {{-- Edit menuju ke batch group --}}
                                            @if (auth()->user()->hasPermission('edit_pesananrsc'))
                                            <a href="{{ route('admin.pesananrsc.edit', ['nama_camp' => $item->nama_camp, 'batch_camp' => $item->batch_camp]) }}"
                                                wire:navigate
                                                class="btn btn-sm btn-warning me-1"
                                                title="Edit Batch">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @endif

                                            {{-- Delete batch --}}
                                            @if (auth()->user()->hasPermission('delete_pesananrsc'))
                                            <button type="button"
                                                class="btn btn-danger btn-sm delete-batch-btn"
                                                wire:click="confirmDeleteBatch('{{ $item->nama_camp }}', '{{ $item->batch_camp }}', {{ $item->total_peserta }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif

                                            {{-- Detail peserta --}}
                                            <a wire:navigate href="{{ route('admin.pesananrsc.detail', ['nama_camp' => urlencode($item->nama_camp), 'batch_camp' => urlencode($item->batch_camp)]) }}"
                                                class="btn btn-primary btn-sm"
                                                title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="py-4 text-center">
                                        <div class="text-muted">
                                            <i class="mb-2 bi bi-inbox fs-1"></i>
                                            <p>Tidak ada data pemesanan yang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="modal fade" id="modalWaOptions" tabindex="-1" aria-labelledby="modalWaLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Kirim WhatsApp</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <p>Pilih jenis pesan yang ingin dikirim ke pelanggan:</p>
                                    <input type="hidden" id="waId">
                                    <input type="hidden" id="waIdTransaksi">
                                    <input type="hidden" id="waNumber">
                                    <input type="hidden" id="waNama">
                                    <input type="hidden" id="waAkun">
                                    <input type="hidden" id="waPemesanan">
                                    <input type="hidden" id="waBerakhir">
                                    <input type="hidden" id="waUsername">
                                    <input type="hidden" id="waPassword">
                                    <input type="hidden" id="waLinkAkses">
                                    <div class="list-group">
                                        <button class="list-group-item list-group-item-action" onclick="kirimWa('pengiriman')">📦 Pengiriman Akun</button>
                                        <button class="list-group-item list-group-item-action" onclick="kirimWa('pembaharuan')">♻️ Pembaharuan Akun</button>
                                        <button class="list-group-item list-group-item-action" onclick="kirimWa('habis')">⛔ Akun Habis</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $pemesananrsc->links('vendor.pagination') }}
                    </div>
                </div>
            </div>

            <!-- modal select batch -->
            @if($showExportModal)
            <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Export Data Peserta ke Excel</h5>
                            <button type="button" class="btn-close" wire:click="closeExportModal"></button>
                        </div>
                        <div class="modal-body">

                            {{-- Search Filter di dalam Modal --}}
                            <div class="mb-3">
                                <input type="text"
                                    class="form-control"
                                    placeholder="Cari Nama Camp atau Batch..."
                                    wire:model.live.debounce.300ms="searchBatchExport">
                            </div>

                            {{-- List Batch (Scrollable) --}}
                            <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                @if($this->availableBatchesForExport->isEmpty())
                                <div class="text-center text-muted py-3">Batch tidak ditemukan.</div>
                                @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Nama Camp</th>
                                                <th>Batch</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($this->availableBatchesForExport as $batch)
                                            <tr>
                                                <td>
                                                    <input type="checkbox"
                                                        class="form-check-input"
                                                        value="{{ $batch->key }}"
                                                        wire:model.live="selectedBatches">
                                                </td>
                                                <td>{{ $batch->nama_camp }}</td>
                                                <td>Batch {{ $batch->batch_camp }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>

                            {{-- Counter Seleksi --}}
                            <div class="mt-2 text-end">
                                <small class="text-muted">{{ count($selectedBatches) }} Batch dipilih</small>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-secondary" wire:click="closeExportModal">Batal</button>

                            <div class="d-flex gap-2">
                                <!-- preview pdf -->
                                @if(!empty($selectedBatches))
                                <a href="{{ route('admin.preview.invoice', ['batches' => $selectedBatches]) }}"
                                    target="_blank"
                                    class="btn btn-info text-white">
                                    <i class="bi bi-eye"></i> Preview PDF
                                </a>
                                @endif
                                {{-- Tombol PDF Invoice --}}
                                <button type="button" class="btn btn-danger" wire:click="exportInvoice" wire:loading.attr="disabled">
                                    <i class="bi bi-file-earmark-pdf"></i> Download Invoice
                                    <span wire:loading wire:target="exportInvoice" class="spinner-border spinner-border-sm ms-1"></span>
                                </button>

                                {{-- Tombol Excel Data --}}
                                <button type="button" class="btn btn-success" wire:click="exportExcel" wire:loading.attr="disabled">
                                    <i class="bi bi-file-earmark-excel"></i> Download Data Excel
                                    <span wire:loading wire:target="exportExcel" class="spinner-border spinner-border-sm ms-1"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!--================== SWEET ALERT DELETE ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.delete-pemesananrsc-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const BannersId = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus Data ini?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
                        Livewire.find(livewireComponentId).call('deletepemesananrsc', BannersId);
                    }
                });
            });
        });

        window.addEventListener('pemesananrsc-deleted', () => {
            Swal.fire({
                title: 'Terhapus!',
                text: 'Data berhasil dihapus.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        });

        window.addEventListener('delete-error', (e) => {
            Swal.fire({
                title: 'Gagal!',
                text: e.detail.message,
                icon: 'error'
            });
        });

    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->

<!--================== MODAL PENGIRIMAN AKUN ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.send-wa-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('waId').value = this.dataset.id;
                document.getElementById('waIdTransaksi').value = this.dataset.idtransaksi;
                document.getElementById('waNumber').value = this.dataset.wa;
                document.getElementById('waNama').value = this.dataset.nama;
                document.getElementById('waAkun').value = this.dataset.akun;
                document.getElementById('waPemesanan').value = this.dataset.pemesanan;
                document.getElementById('waBerakhir').value = this.dataset.berakhir;
                document.getElementById('waUsername').value = this.dataset.username;
                document.getElementById('waPassword').value = this.dataset.password;
                document.getElementById('waLinkAkses').value = this.dataset.linkakses;
                var modal = new bootstrap.Modal(document.getElementById('modalWaOptions'));
                modal.show();
            });
        });
    });

    function kirimWa(type) {
        const idtransaksi = document.getElementById('waIdTransaksi').value;
        const nama = document.getElementById('waNama').value;
        const noWa = document.getElementById('waNumber').value;
        const akun = document.getElementById('waAkun').value;
        const pemesanan = document.getElementById('waPemesanan').value;
        const berakhir = document.getElementById('waBerakhir').value;
        const username = document.getElementById('waUsername').value;
        const password = document.getElementById('waPassword').value;
        const linkakses = document.getElementById('waLinkAkses').value;

        let pesan = '';

        if (type === 'pengiriman') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Kami dari Phoenix Digital Warehouse bermaksud mengirimkan akun ${akun} yang bisa Anda gunakan mulai tanggal ${pemesanan} dengan masa aktif sampai tanggal ${berakhir}.

Berikut detail akun Anda:

• Username: ${username}
• Password: ${password}
• Link Login: ${linkakses}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        } else if (type === 'pembaharuan') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Akun ${akun} yang anda order pada tanggal ${pemesanan} dengan masa aktif sampai tanggal ${berakhir}, terdapat pembaharuan akun ${akun}.

Berikut detail akun Anda:

• Username: ${username}
• Password: ${password}
• Link Login: ${linkakses}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        } else if (type === 'habis') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Akun ${akun} yang anda order pada tanggal ${berakhir} sudah habis. Jika Anda ingin memperpanjang akun ${akun} Anda, silakan hubungi kami.

Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        }

        const url = `https://wa.me/${noWa}?text=${encodeURIComponent(pesan)}`;
        window.open(url, '_blank');
    }
</script>
<!--================== END ==================-->