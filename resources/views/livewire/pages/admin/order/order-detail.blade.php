<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Detail Pesanan {{ $order->order_number }}</h3>
        @php
            $breadcrumbs = [
                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                ['name' => 'Data Pesanan Toko', 'url' => route('admin.pesanantoko.index')],
                ['name' => 'Detail Pesanan'],
            ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="mb-0 card">
            <div class="card-body">
                <a wire:navigate href="{{ route('admin.pesanantoko.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left-circle me-1"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <h5 class="card-title">Data Pesanan</h5>
                        <p class="mb-1"><strong>No. Order:</strong> {{ $order->order_number }}</p>
                        <p class="mb-1"><strong>Tanggal:</strong> {{ $order->created_at->format('d-m-Y H:i') }}</p>
                        <p class="mb-0"><strong>Status:</strong> {{ $order->status }}</p>
                    </div>
                    <div class="col-lg-6">
                        <h5 class="card-title">Data Pembeli</h5>
                        <p class="mb-1"><strong>Nama:</strong> {{ $order->customer->nama ?? '-' }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $order->customer->email ?? '-' }}</p>
                        <p class="mb-0"><strong>Telepon:</strong> {{ $order->customer->no_hp ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Item Pesanan</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-sm">
                        <thead class="table-secondary">
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Durasi</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->nama_akun ?? '-' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->duration_value }} {{ $item->duration_type }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                    <td>{!! $item->getDeliveryStatusBadge() !!}</td>
                                    <td>
                                        <a wire:navigate href="{{ route('admin.pesanantoko.process', $item->id) }}"
                                            class="btn btn-primary" title="proses pesanan">
                                            <i class="bi bi-gear"></i></a>
                                        @if ($item->delivery_status != 'pending')
                                            <button class="btn btn-success send-wa-btn" title="kirim akun ke pembeli"
                                                type="button" data-id="{{ $item->id }}"
                                                data-idTransaksi="{{ $order->order_number }}"
                                                data-nama="{{ $order->customer->nama }}"
                                                data-wa="{{ $order->customer->no_hp }}"
                                                data-akun="{{ $item->dataakun?->nama_akun ?? '-' }}"
                                                data-pemesanan="{{ \Carbon\Carbon::parse($item->start_date)->format('d F Y') }}"
                                                data-berakhir="{{ \Carbon\Carbon::parse($item->end_date)->format('d F Y') }}"
                                                data-username="{{ $item->account_username }}"
                                                data-password="{{ $item->account_password }}"
                                                data-linkakses="{{ $item->account_link }}">
                                                <i class="bi bi-whatsapp"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Belum ada item pesanan</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($order->items->count())
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="5" class="text-end">Total</th>
                                    <th colspan="2">Rp
                                        {{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                {{-- modal --}}
                <div class="modal fade" id="modalWaOptions" tabindex="-1" aria-labelledby="modalWaLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Kirim Whatsapp</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Pilih jenis pesan yang akan dikirim ke pembeli:</p>
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
                                    <button class="list-group-item list-group-item-action"
                                        onclick="kirimWa('pengiriman')">📦 Pengiriman Akun</button>
                                    <button class="list-group-item list-group-item-action"
                                        onclick="kirimWa('pembaharuan')">♻️ Pembaharuan Akun</button>
                                    <button class="list-group-item list-group-item-action" onclick="kirimWa('habis')">⛔
                                        Akun Habis</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('close-wa-modal', () => {
                const modalEl = document.getElementById('modalWaOptions');
                const modalInstance = bootstrap.Modal.getInstance(modalEl) ?? new bootstrap.Modal(modalEl);
                modalInstance.hide();
            });
        });

        document.addEventListener('click', function(e) {
            const button = e.target.closest('.send-wa-btn');
            if (!button) return;

            document.getElementById('waId').value = button.dataset.id;
            document.getElementById('waIdTransaksi').value = button.dataset.idtransaksi;
            document.getElementById('waNumber').value = button.dataset.wa;
            document.getElementById('waNama').value = button.dataset.nama;
            document.getElementById('waAkun').value = button.dataset.akun;
            document.getElementById('waPemesanan').value = button.dataset.pemesanan;
            document.getElementById('waBerakhir').value = button.dataset.berakhir;
            document.getElementById('waUsername').value = button.dataset.username;
            document.getElementById('waPassword').value = button.dataset.password;
            document.getElementById('waLinkAkses').value = button.dataset.linkakses;

            const modalEl = document.getElementById('modalWaOptions');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });

        function kirimWa(type) {
            const idItem = document.getElementById('waId').value;
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
            Livewire.dispatch('sent-on-whatsapp', {
                id: idItem
            });
        }
    </script>
@endpush
