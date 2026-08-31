@section('title')
Jejak Audit Orcha || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Jejak Audit',
            'keterangan' => 'Siapa mengubah apa, kapan. Hanya bisa dibaca — tidak ada yang bisa menghapusnya dari sini.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        @include('livewire.pages.admin.orcha.partials.cari', [
                            'petunjuk' => 'Cari kode pesanan, nama admin, atau isi perubahan...',
                        ])
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label small fw-semibold">Admin</label>
                        <select wire:model.live="filterAdmin" class="form-select">
                            <option value="">Semua admin</option>
                            @foreach ($pilihanAdmin as $nama)
                                <option value="{{ $nama }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Rentang tanggal, bukan satu tanggal.

                         Pertanyaan audit hampir selalu berbentuk rentang — "pekan lalu",
                         "sejak pengembaliannya diajukan" — dan satu tanggal memaksa
                         menelusuri hari demi hari. --}}
                    <div class="col-6 col-lg-2">
                        <label class="form-label small fw-semibold">Dari</label>
                        <input type="date" wire:model.live="dari" class="form-control">
                    </div>

                    <div class="col-6 col-lg-2">
                        <label class="form-label small fw-semibold">Sampai</label>
                        <input type="date" wire:model.live="sampai" class="form-control">
                    </div>

                    <div class="col-12 col-lg-1">
                        <button type="button" wire:click="bersihkanSaringan"
                            class="btn btn-outline-secondary w-100" title="Bersihkan saringan">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Admin</th>
                                <th>Perubahan</th>
                                <th>Pesanan</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="jejak-{{ $baris['id'] }}">
                                    <td class="text-nowrap">
                                        <div class="fw-semibold">{{ $baris['waktu_teks'] }}</div>
                                    </td>
                                    <td>
                                        <span class="orcha-lencana-catat">
                                            <i class="bi bi-person-badge"></i> {{ $baris['admin'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $baris['aksi'] }}</div>

                                        {{-- Perpindahan status ditampilkan sebagai "dari → ke",
                                             bukan hanya nilai akhirnya. Nilai akhir saja tidak
                                             menjawab pertanyaan yang sebenarnya diajukan:
                                             berubah DARI apa. --}}
                                        @if ($baris['sebelum'] !== null || $baris['sesudah'] !== null)
                                            <div class="text-muted" style="font-size:.78rem">
                                                {{ $baris['sebelum'] ?: '—' }}
                                                <i class="bi bi-arrow-right mx-1"></i>
                                                <strong>{{ $baris['sesudah'] ?: '—' }}</strong>
                                            </div>
                                        @endif

                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $baris['ringkasan'] }}
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($baris['kode'])
                                            <span class="font-monospace" style="font-size:.8rem">{{ $baris['kode'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size:.78rem">{{ $baris['ip'] ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada perubahan yang tercatat untuk saringan ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('livewire.pages.admin.orcha.partials.paginasi')
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
