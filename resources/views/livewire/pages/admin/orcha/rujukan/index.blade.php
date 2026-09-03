@section('title')
Kode Rujukan || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Kode Rujukan',
            'keterangan' => 'Alumni dan mitra yang membawa pendaftaran baru.',
        ])

        {{-- Keterangan pembuka, bukan hiasan.

             Yang membuka layar ini pertama kali akan menyamakannya dengan promo
             rombongan — keduanya potongan, keduanya diatur admin. Bedanya justru
             yang menentukan kapan masing-masing dipakai, dan kalau tidak
             dijelaskan di sini, ia dijelaskan berulang-ulang lewat WhatsApp. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-bagian-kepala mb-0">
                    <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <div class="orcha-bagian-judul">Bedanya dengan promo rombongan</div>
                        <div class="orcha-bagian-sub">
                            Promo rombongan berlaku dalam <strong>satu pendaftaran</strong> — ramai
                            orang berangkat bersama di tanggal yang sama.
                            <br>
                            Kode rujukan berlaku <strong>lintas pendaftaran</strong> — orang yang sudah
                            pulang mengajak temannya ikut trip berikutnya, di tanggal yang berbeda.
                            Keduanya bisa berlaku bersamaan.
                            <br>
                            Alumni mendapat kodenya sendiri lewat surat ajakan testimoni. Yang
                            diketik di sini biasanya mitra atau kenalan yang belum pernah ikut trip.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Angka yang berlaku disebut apa adanya.

             Admin yang menjelaskan program ini lewat telepon perlu membaca
             angkanya tanpa membuka berkas config — dan angka yang diingat dari
             percakapan bulan lalu adalah angka yang salah. --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-lg-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-gradient-green"><i class="bi bi-tag"></i></div>
                        <div>
                            <div class="text-muted small">Potongan untuk yang memakai</div>
                            <div class="fw-bold fs-5">
                                Rp {{ number_format($meta['potongan'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-lg-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-gradient-purple"><i class="bi bi-gift"></i></div>
                        <div>
                            <div class="text-muted small">Imbalan untuk pemilik kode</div>
                            <div class="fw-bold fs-5">
                                Rp {{ number_format($meta['imbalan'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-lg-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper {{ ($meta['aktif'] ?? true) ? 'bg-gradient-blue' : 'bg-gradient-red' }}">
                            <i class="bi {{ ($meta['aktif'] ?? true) ? 'bi-broadcast' : 'bi-pause-circle' }}"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Program rujukan</div>
                            <div class="fw-bold fs-5">
                                {{ ($meta['aktif'] ?? true) ? 'Sedang berjalan' : 'Sedang dimatikan' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($tambah || $sunting)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-person-plus"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">
                                {{ $sunting ? 'Ubah kode rujukan' : 'Kode rujukan baru' }}
                            </div>
                            <div class="orcha-bagian-sub">
                                {{ $sunting
                                    ? 'Kodenya sendiri tidak bisa diubah — ia sudah tersebar dan sudah menempel pada pendaftaran yang lalu.'
                                    : 'Kodenya dibuat sendiri dari nama depan pemiliknya, misalnya BUDI-K7QM.' }}
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Nama pemilik <span class="text-danger">*</span></label>
                            <input type="text" wire:model="isian.nama" maxlength="120"
                                class="form-control @error('isian.nama') is-invalid @enderror"
                                placeholder="Budi Santoso">
                            @error('isian.nama')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Nomor WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" wire:model="isian.whatsapp" maxlength="32"
                                class="form-control @error('isian.whatsapp') is-invalid @enderror"
                                placeholder="0812-3456-7890">
                            {{-- Nomornya bukan sekadar cara menghubungi.

                                 Ia juga yang menahan seseorang memakai kodenya
                                 sendiri, dan yang menjaga satu orang tidak punya
                                 dua kode. Disebutkan supaya admin tidak
                                 mengetikkan nomor kantor demi kepraktisan. --}}
                            <div class="form-text">
                                Dipakai juga untuk menahan kode dipakai pemiliknya sendiri.
                            </div>
                            @error('isian.whatsapp')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Email <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="email" wire:model="isian.email" maxlength="150"
                                class="form-control @error('isian.email') is-invalid @enderror"
                                placeholder="nama@email.com">
                            @error('isian.email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="text" wire:model="isian.catatan" maxlength="200"
                                class="form-control" placeholder="Mitra travel Surabaya">
                        </div>
                    </div>

                    <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }} mt-3">
                        <span class="rupa">
                            <i class="bi {{ $aktif ? 'bi-broadcast' : 'bi-pause-circle' }}"></i>
                        </span>
                        <span class="isi">
                            <span class="judul">{{ $aktif ? 'Kode berlaku' : 'Kode dimatikan' }}</span>
                            <span class="ket">
                                {{ $aktif
                                    ? 'Bisa dipakai mendaftar, dan pemakaiannya menambah komisi pemiliknya.'
                                    : 'Ditolak saat dipakai mendaftar. Komisi yang sudah tercatat tidak hilang.' }}
                            </span>
                        </span>
                        <span class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                        </span>
                    </label>

                    <div class="d-flex gap-2 justify-content-end pt-3 mt-4 border-top">
                        <button type="button" wire:click="tutup" class="orcha-btn orcha-btn-lembut">
                            Batal
                        </button>

                        <button type="button" wire:click="simpan" wire:loading.attr="disabled"
                            wire:target="simpan" class="orcha-btn orcha-btn-utama">
                            <span wire:loading.remove wire:target="simpan">
                                <i class="bi bi-check2-circle"></i>
                                Simpan
                            </span>
                            <span wire:loading wire:target="simpan">
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>Menyimpan…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-8">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari nama, kode, atau nomor WhatsApp...'])
                    </div>

                    <div class="col-12 col-lg-4 d-grid">
                        <button type="button" wire:click="bukaTambah" class="orcha-btn orcha-btn-utama justify-content-center">
                            <i class="bi bi-plus-lg"></i>
                            <span>Kode baru</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($lihatPemakaian)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="orcha-bagian-kepala mb-0">
                            <div class="orcha-bagian-nomor"><i class="bi bi-list-check"></i></div>
                            <div>
                                <div class="orcha-bagian-judul">Pendaftaran yang memakai kode ini</div>
                                <div class="orcha-bagian-sub">
                                    Saat komisi dibayarkan, pertanyaannya bukan "berapa" melainkan
                                    "untuk pendaftaran yang mana".
                                </div>
                            </div>
                        </div>

                        <button type="button" wire:click="tutupPemakaian" class="btn btn-sm orcha-aksi orcha-aksi-mati"
                            title="Tutup rincian">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">PENDAFTARAN</th>
                                    <th>TRIP</th>
                                    <th>IMBALAN</th>
                                    <th class="text-end pe-4">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pemakaian as $satu)
                                    <tr wire:key="pakai-{{ $satu['id'] }}">
                                        <td class="ps-4">
                                            <span class="orcha-kode">{{ $satu['kode'] }}</span>
                                            <div class="fw-semibold">{{ $satu['nama'] }}</div>
                                        </td>

                                        <td class="text-muted small">
                                            {{ $satu['nama_paket'] ?? '—' }}
                                            @if ($satu['tanggal_berangkat'])
                                                <div>
                                                    {{ \Carbon\Carbon::parse($satu['tanggal_berangkat'])->locale('id')->translatedFormat('d M Y') }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="fw-semibold">
                                            Rp {{ number_format($satu['imbalan'], 0, ',', '.') }}
                                        </td>

                                        <td class="text-end pe-4">
                                            @if ($satu['dibayar_pada'])
                                                <span class="badge bg-success-subtle text-success-emphasis">
                                                    Dibayar
                                                    {{ \Carbon\Carbon::parse($satu['dibayar_pada'])->locale('id')->translatedFormat('d M Y') }}
                                                </span>
                                            @else
                                                {{-- Menandai, bukan membayar: uangnya berpindah
                                                     lewat transfer di luar sistem. Yang dicatat di
                                                     sini pengakuan bahwa itu sudah terjadi. --}}
                                                <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                                    wire:click="bayar({{ $satu['id'] }})"
                                                    wire:confirm="Tandai imbalan Rp {{ number_format($satu['imbalan'], 0, ',', '.') }} untuk {{ $satu['kode'] }} sudah dibayar? Penandaan ini tidak bisa dibatalkan.">
                                                    <i class="bi bi-cash-coin"></i> Tandai dibayar
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            Kode ini belum pernah dipakai siapa pun.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">KODE</th>
                                <th>PEMILIK</th>
                                <th>DIPAKAI</th>
                                <th>KOMISI BELUM DIBAYAR</th>
                                <th>STATUS</th>
                                <th class="text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="rujukan-{{ $baris['id'] }}">
                                    <td class="ps-4">
                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                        @if ($baris['kode_pendaftaran_asal'])
                                            <div class="text-muted" style="font-size:.72rem">
                                                alumni {{ $baris['kode_pendaftaran_asal'] }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="fw-semibold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted small">{{ $baris['whatsapp'] }}</div>
                                    </td>

                                    <td>
                                        <span class="orcha-cip-peserta">
                                            <i class="bi bi-people-fill"></i>
                                            {{ $baris['jumlah_dipakai'] }}&times;
                                        </span>
                                    </td>

                                    <td>
                                        {{-- Yang belum dibayar ditonjolkan, bukan totalnya.

                                             Total komisi sepanjang masa enak dibaca tetapi tidak
                                             menuntut perbuatan apa pun. Yang menuntut perbuatan
                                             utangnya — dan itu yang harus terlihat lebih dulu. --}}
                                        @if ($baris['imbalan_belum_dibayar'] > 0)
                                            <span class="fw-bold text-danger-emphasis">
                                                Rp {{ number_format($baris['imbalan_belum_dibayar'], 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif

                                        @if ($baris['imbalan_total'] > 0)
                                            <div class="text-muted" style="font-size:.72rem">
                                                total Rp {{ number_format($baris['imbalan_total'], 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($baris['aktif'])
                                            <span class="badge bg-success-subtle text-success-emphasis">Berlaku</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">Dimatikan</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-lihat"
                                                wire:click="bukaPemakaian({{ $baris['id'] }})"
                                                title="Lihat pendaftaran yang memakai kode ini">
                                                <i class="bi bi-list-check"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                                wire:click="bukaSunting({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                                title="Ubah kode ini">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-3">
                                            <i class="bi bi-share"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Belum ada kode rujukan. Alumni mendapatkannya sendiri lewat
                                            surat ajakan testimoni dua hari setelah pulang.
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
