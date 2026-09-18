@section('title')
RAB & Itinerary || lemon
@stop

@php
    $jumlahData = (int) ($meta['total'] ?? count($daftar));
    $bisaBersih = $this->adaSaringan();
    $warnaStatus = [
        'draf' => 'bg-secondary-subtle text-secondary-emphasis',
        'dikirim' => 'bg-primary-subtle text-primary-emphasis',
        'disetujui' => 'bg-success-subtle text-success-emphasis',
        'batal' => 'bg-danger-subtle text-danger-emphasis',
    ];
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'RAB & Itinerary',
            'keterangan' => 'Susun itinerary private trip, hitung anggarannya, lalu kirim penawaran PDF ke pelanggan.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-6">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari kode, pelanggan, judul, atau WhatsApp...'])
                    </div>
                    <div class="col-12 col-lg-3">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua status</option>
                            @foreach ($status as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-3 d-grid">
                        <button type="button" wire:click="bukaTambah" class="orcha-btn orcha-btn-utama justify-content-center">
                            <i class="bi bi-plus-lg"></i>
                            <span>RAB Baru</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($tambah)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <h6 class="fw-bold mb-1 orcha-judul-ikon">
                        <i class="bi bi-calculator text-primary"></i> RAB Baru
                    </h6>
                    <p class="text-muted small mb-3">
                        Cukup yang dibutuhkan untuk mulai menghitung. Biaya wajib provinsinya langsung terisi;
                        destinasi dipilih di langkah berikutnya.
                    </p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Judul perjalanan <span class="text-danger">*</span></label>
                            <input type="text" wire:model="isian.judul" maxlength="150"
                                class="form-control @error('isian.judul') is-invalid @enderror" placeholder="Jogja Heritage 3H2M">
                            @error('isian.judul')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold">Nama pelanggan <span class="text-danger">*</span></label>
                            <input type="text" wire:model="isian.nama_pelanggan" maxlength="120"
                                class="form-control @error('isian.nama_pelanggan') is-invalid @enderror" placeholder="Keluarga Bpk. Hendra">
                            @error('isian.nama_pelanggan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold">WhatsApp</label>
                            <input type="text" wire:model="isian.whatsapp" maxlength="32" inputmode="tel"
                                class="form-control" placeholder="0812…">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Provinsi tujuan <span class="text-danger">*</span></label>
                            <select wire:model="isian.provinsi" class="form-select @error('isian.provinsi') is-invalid @enderror">
                                <option value="">Pilih provinsi</option>
                                @foreach ($provinsi as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                            @error('isian.provinsi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Kota / daerah</label>
                            <input type="text" wire:model="isian.daerah" maxlength="80" class="form-control" placeholder="Yogyakarta">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Tanggal berangkat</label>
                            <input type="date" wire:model="isian.tanggal_mulai" class="form-control">
                            <div class="form-text">Boleh dikosongkan bila belum pasti.</div>
                        </div>

                        <div class="col-4">
                            <label class="form-label small fw-semibold">Hari <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="30" wire:model.live.debounce.400ms="isian.jumlah_hari"
                                class="form-control @error('isian.jumlah_hari') is-invalid @enderror">
                            @error('isian.jumlah_hari')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Malam <span class="text-danger">*</span></label>
                            <input type="number" min="0" max="30" wire:model="isian.jumlah_malam"
                                class="form-control @error('isian.jumlah_malam') is-invalid @enderror">
                            @error('isian.jumlah_malam')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Peserta <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="500" wire:model="isian.jumlah_peserta"
                                class="form-control @error('isian.jumlah_peserta') is-invalid @enderror">
                            @error('isian.jumlah_peserta')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end pt-3 mt-4 border-top">
                        <button type="button" wire:click="tutup" class="orcha-btn orcha-btn-lembut">Batal</button>
                        <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                            class="orcha-btn orcha-btn-utama">
                            <span wire:loading.remove wire:target="simpan"><i class="bi bi-arrow-right-circle"></i> Buat & Susun</span>
                            <span wire:loading wire:target="simpan">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Membuat…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="text-muted small mb-3">{{ $jumlahData }} RAB.</div>
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel mb-0">
                        <thead>
                            <tr>
                                <th>KODE</th>
                                <th>PERJALANAN</th>
                                <th>TUJUAN</th>
                                <th class="text-end">HARGA</th>
                                <th class="text-end">UNTUNG</th>
                                <th>STATUS</th>
                                <th class="text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                @php
                                    $bisaDihapus = empty($baris['kode_pendaftaran']);
                                    $kelasStatus = $warnaStatus[$baris['status']] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                    $tanggal = $baris['tanggal_mulai']
                                        ? \Illuminate\Support\Carbon::parse($baris['tanggal_mulai'])->locale('id')->translatedFormat('j M Y')
                                        : 'Tanggal belum pasti';
                                @endphp
                                <tr wire:key="rab-{{ $baris['id'] }}">
                                    <td><span class="orcha-kode">{{ $baris['kode'] }}</span></td>
                                    <td>
                                        <a href="{{ route('admin.orcha.rab.susun', $baris['id']) }}" class="fw-bold text-decoration-none" wire:navigate>
                                            {{ $baris['judul'] }}
                                        </a>
                                        <div class="text-muted small">{{ $baris['nama_pelanggan'] }} · {{ $baris['jumlah_peserta'] }} orang</div>
                                    </td>
                                    <td class="small">
                                        {{ $baris['daerah'] ?: $baris['provinsi'] }}
                                        <div class="text-muted">{{ $tanggal }} · {{ $baris['jumlah_hari'] }}H{{ $baris['jumlah_malam'] }}M</div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="fw-bold">{{ $baris['harga_total_teks'] }}</div>
                                        <div class="text-muted small">{{ $baris['harga_per_orang_teks'] }}/org</div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="fw-bold text-success">{{ $baris['untung_teks'] }}</div>
                                        @isset($baris['persen_untung'])
                                            <div class="text-muted small">{{ str_replace('.', ',', $baris['persen_untung']) }}%</div>
                                        @endisset
                                    </td>
                                    <td>
                                        <span class="badge {{ $kelasStatus }}">{{ $baris['status_label'] }}</span>
                                        @if ($baris['kedaluwarsa'])
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Lewat masa berlaku</span>
                                        @endif
                                        @if ($baris['kode_pendaftaran'])
                                            <div class="small text-muted mt-1"><i class="bi bi-link-45deg"></i> {{ $baris['kode_pendaftaran'] }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('admin.orcha.rab.susun', $baris['id']) }}" wire:navigate
                                                class="btn btn-sm orcha-aksi orcha-aksi-ubah" title="Buka penyusun">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $baris['id'], 'jenis' => 'penawaran']) }}"
                                                class="btn btn-sm orcha-aksi orcha-aksi-lihat" title="Unduh PDF penawaran">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            @if ($bisaDihapus)
                                                <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                                    data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                    data-title="Hapus RAB {{ $baris['kode'] }}?"
                                                    data-text="Itinerary dan seluruh rincian biayanya ikut terhapus."
                                                    data-confirm="Ya, hapus" data-icon="warning"
                                                    title="Hapus RAB ini">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-3"><i class="bi bi-calculator"></i></div>
                                        <p class="text-muted mb-0">
                                            {{ $bisaBersih ? 'Tidak ada RAB yang cocok dengan saringan.' : 'Belum ada RAB. Tekan RAB Baru saat ada pelanggan meminta private trip.' }}
                                        </p>
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
