@section('title')
Blog Orcha || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Blog Orcha',
            'keterangan' => 'Panduan perjalanan dan cerita destinasi yang tampil di orchajourney.com/blog.',
        ])

        {{-- ============ SARINGAN ============ --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-5">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari judul artikel...'])
                    </div>

                    <div class="col-6 col-lg-2">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua status</option>
                            <option value="draf">📝 Draf</option>
                            <option value="tayang">🌐 Tayang</option>
                        </select>
                    </div>

                    <div class="col-6 col-lg-3">
                        <select wire:model.live="filterKategori" class="form-select">
                            <option value="">Semua kategori</option>
                            @foreach ($daftarKategori as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-2 d-flex gap-2 justify-content-lg-end">
                        @if ($this->adaSaringan())
                            <button type="button" wire:click="bersihkanSaringan"
                                class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 flex-shrink-0">
                                <i class="bi bi-x-circle"></i> <span>Bersihkan</span>
                            </button>
                        @endif
                        <a href="{{ route('admin.orcha.blog.create') }}" wire:navigate
                            class="btn btn-primary d-inline-flex align-items-center gap-1 flex-shrink-0">
                            <i class="bi bi-plus-lg"></i> <span>Tulis</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ DAFTAR ============ --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">

                @if (empty($artikel))
                    <div class="text-center py-5">
                        <div class="empty-state-icon-wrapper mx-auto mb-3">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        @if ($this->adaSaringan())
                            <h6 class="fw-bold mb-1">Tidak ada artikel yang cocok</h6>
                            <p class="text-muted small mb-3">Coba kata lain atau bersihkan saringannya.</p>
                            <button type="button" wire:click="bersihkanSaringan" class="btn btn-outline-secondary">
                                Tampilkan semua
                            </button>
                        @else
                            <h6 class="fw-bold mb-1">Belum ada artikel</h6>
                            <p class="text-muted small mb-3">Tulisan pertama akan tampil di halaman blog Orcha.</p>
                            <a href="{{ route('admin.orcha.blog.create') }}" wire:navigate class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> Tulis artikel pertama
                            </a>
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th style="width:88px;">Sampul</th>
                                    <th>Judul</th>
                                    <th class="d-none d-lg-table-cell">Kategori</th>
                                    <th class="d-none d-md-table-cell">Terbit</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($artikel as $a)
                                    <tr wire:key="artikel-{{ $a['id'] }}">
                                        <td>
                                            <img src="{{ $a['sampul_tampil'] }}" alt=""
                                                class="rounded-3"
                                                style="width:76px;height:48px;object-fit:cover;">
                                        </td>

                                        <td>
                                            <div class="fw-bold">{{ $a['judul'] }}</div>
                                            <div class="text-muted small">
                                                <i class="bi bi-clock me-1"></i>{{ $a['lama_baca'] }} menit
                                                <span class="mx-1">·</span>
                                                <i class="bi bi-eye me-1"></i>{{ $a['dilihat'] }}
                                            </div>
                                        </td>

                                        <td class="d-none d-lg-table-cell">
                                            <span class="text-muted small">{{ $a['kategori_label'] ?? '—' }}</span>
                                        </td>

                                        <td class="d-none d-md-table-cell">
                                            <span class="text-muted small">{{ $a['tanggal_terbit'] ?? '—' }}</span>
                                        </td>

                                        <td>
                                            {{-- Tiga keadaan, bukan dua.

                                                 "Tayang" yang tanggalnya masih di depan BELUM terbaca
                                                 siapa pun. Menampilkannya sama dengan yang sudah terbit
                                                 membuat admin mengira tulisannya sudah hidup. --}}
                                            @if ($a['status'] === 'draf')
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis">Draf</span>
                                            @elseif ($a['sedang_tayang'])
                                                <span class="badge bg-success-subtle text-success-emphasis">Tayang</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis">Terjadwal</span>
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                @if ($a['sedang_tayang'])
                                                    <a href="{{ $a['tautan'] }}" target="_blank" rel="noopener"
                                                        class="btn btn-sm btn-outline-secondary" title="Lihat di situs">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                @endif

                                                <a href="{{ route('admin.orcha.blog.edit', $a['id']) }}" wire:navigate
                                                    class="btn btn-sm btn-outline-primary" title="Sunting">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                {{-- Konfirmasi seragam lemon (pcek-konfirmasi), bukan
                                                     wire:confirm bawaan. --}}
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger pcek-konfirmasi"
                                                    data-action="hapus"
                                                    data-arg="{{ $a['id'] }}"
                                                    data-title="Hapus artikel?"
                                                    data-text="{{ $a['judul'] }} akan hilang dari blog Orcha dan tidak bisa dikembalikan."
                                                    data-confirm="Ya, hapus"
                                                    data-icon="warning"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @include('livewire.pages.admin.orcha.partials.paginasi')
                @endif
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
