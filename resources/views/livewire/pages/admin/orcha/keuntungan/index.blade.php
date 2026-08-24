@section('title')
Keuntungan Paket || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Keuntungan Paket Wisata',
            'keterangan' => 'Selisih harga jual dan modal, dikalikan jumlah peserta. Yang dihitung hanya pendaftaran yang sudah lunas.',
        ])

        {{-- ================= SARINGAN ================= --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                @php
                    $aktif = $this->rentangAktif();
                    $tombol = [
                        'bulan-ini' => 'Bulan ini',
                        'bulan-lalu' => 'Bulan lalu',
                        'tahun-ini' => 'Tahun ini',
                        'semua' => 'Semua waktu',
                    ];
                @endphp

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div class="orcha-rentang">
                        @foreach ($tombol as $kunci => $label)
                            <button type="button" wire:click="pilihRentang('{{ $kunci }}')"
                                class="{{ $aktif === $kunci ? 'aktif' : '' }}">{{ $label }}</button>
                        @endforeach
                    </div>

                    <span class="text-muted small orcha-ikon-teks">
                        <i class="bi bi-info-circle"></i>
                        Keuntungan dihitung dari pendaftaran <strong class="mx-1">lunas</strong> saja
                    </span>
                </div>

                <div class="row g-2 align-items-end">
                    <div class="col-6 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Dari</label>
                        <input type="date" class="form-control" wire:model.live="dari">
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Sampai</label>
                        <input type="date" class="form-control" wire:model.live="sampai">
                    </div>
                    <div class="col-12 col-lg-3">
                        <label class="form-label small fw-semibold mb-1">Dihitung menurut</label>
                        <select class="form-select" wire:model.live="dasar">
                            @foreach ($pilihanDasar as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Kategori</label>
                        <select class="form-select" wire:model.live="kategori">
                            <option value="">Semua kategori</option>
                            @foreach ($pilihanKategori as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="form-label small fw-semibold mb-1">Paket</label>
                        <select class="form-select" wire:model.live="paketId">
                            <option value="">Semua paket</option>
                            @foreach ($daftarPaket as $paket)
                                <option value="{{ $paket['id'] }}">
                                    {{ $paket['nama'] }}{{ $paket['modal_terisi'] ? '' : ' (modal belum diisi)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= ANGKA UTAMA ================= --}}
        @php
            $untung = (int) ($ringkasan['keuntungan'] ?? 0);
            $omzet = (int) ($ringkasan['omzet'] ?? 0);
            $modal = (int) ($ringkasan['modal'] ?? 0);
            $belumLengkap = (int) ($ringkasan['belum_lengkap'] ?? 0);
            $adaData = (int) ($ringkasan['pendaftaran'] ?? 0) > 0;

            // Porsi batang susun. Modal dan untung dihitung terhadap omzet yang
            // MODALNYA DIKETAHUI (modal + untung), bukan terhadap omzet
            // keseluruhan — kalau tidak, paket yang modalnya belum diisi
            // membuat batangnya menyisakan celah tanpa keterangan.
            $dasarBatang = max(1, $modal + max(0, $untung));
            $porsiModal = $modal / $dasarBatang * 100;
            $porsiUntung = max(0, $untung) / $dasarBatang * 100;

            $rupaHero = ! $adaData ? 'kosong' : ($untung < 0 ? 'rugi' : '');
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-5">
                <div class="orcha-untung-hero {{ $rupaHero }} d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="orcha-hero-ikon">
                            <i class="bi {{ $untung < 0 ? 'bi-graph-down-arrow' : 'bi-graph-up-arrow' }}"></i>
                        </span>
                        <div class="flex-grow-1">
                            <div class="orcha-hero-label">
                                {{ $untung < 0 ? 'Rugi dari pendaftaran lunas' : 'Keuntungan dari pendaftaran lunas' }}
                            </div>
                            <div class="orcha-hero-angka">{{ $ringkasan['keuntungan_teks'] ?? 'Rp 0' }}</div>
                            <div class="orcha-hero-sub mt-1">
                                {{ $ringkasan['pendaftaran'] ?? 0 }} pendaftaran ·
                                {{ $ringkasan['peserta'] ?? 0 }} peserta ·
                                rata-rata {{ $ringkasan['margin_rata_per_orang_teks'] ?? 'Rp 0' }}/orang
                            </div>
                        </div>
                    </div>

                    @if ($adaData)
                        {{-- Omzet dipecah jadi modal dan untung dalam satu garis. Dua angka
                             rupiah berjajar tidak memberi tahu perbandingannya; panjang
                             potongannya memberi tahu — dan margin tipis terlihat setipis
                             kenyataannya. --}}
                        <div class="orcha-pecah mt-auto mb-2">
                            <span class="bagian-modal" style="width: {{ $porsiModal }}%"></span>
                            <span class="bagian-untung" style="width: {{ $porsiUntung }}%"></span>
                        </div>
                        <div class="orcha-legenda">
                            <span class="orcha-ikon-teks">
                                <span class="titik titik-modal"></span>
                                Modal {{ $ringkasan['modal_teks'] ?? 'Rp 0' }}
                                @if ($untung >= 0)({{ round($porsiModal) }}%)@endif
                            </span>
                            <span class="orcha-ikon-teks">
                                <span class="titik titik-untung"></span>
                                {{-- Persennya hanya berarti bila ada potongan untung yang bisa
                                     ditunjuk di batang. Pada keadaan rugi, "(0%)" di sebelah
                                     angka minus terbaca seperti untungnya nihil, padahal yang
                                     terjadi modalnya terlampaui. --}}
                                @if ($untung < 0)
                                    Rugi Rp {{ number_format(abs($untung), 0, ',', '.') }} di atas modal
                                @else
                                    Untung {{ $ringkasan['keuntungan_teks'] ?? 'Rp 0' }} ({{ round($porsiUntung) }}%)
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="orcha-hero-sub mt-auto">
                            Belum ada pendaftaran lunas pada rentang ini. Coba lebarkan rentang tanggalnya.
                        </div>
                    @endif
                </div>
            </div>

            @php
                $kartu = [
                    ['Omzet', $ringkasan['omzet_teks'] ?? 'Rp 0', 'Uang masuk dari pendaftaran lunas',
                        'bi-cash-stack', 'orcha-ikon-omzet'],
                    ['Modal', $ringkasan['modal_teks'] ?? 'Rp 0', 'Biaya internal yang sudah tercatat',
                        'bi-box-seam', 'orcha-ikon-modal'],
                    ['Potensi', $ringkasan['potensi_keuntungan_teks'] ?? 'Rp 0',
                        ($ringkasan['potensi_pendaftaran'] ?? 0).' pendaftaran belum lunas · '
                            .($ringkasan['potensi_peserta'] ?? 0).' peserta',
                        'bi-hourglass-split', 'orcha-ikon-potensi'],
                ];
            @endphp
            <div class="col-12 col-xl-7">
                <div class="row g-3 h-100">
                    @foreach ($kartu as [$label, $nilai, $keterangan, $ikon, $rupa])
                        <div class="col-12 col-md-4">
                            <div class="card orcha-kartu orcha-untung-kartu h-100">
                                <div class="card-body p-3 p-lg-4">
                                    <span class="orcha-ikon {{ $rupa }} mb-3"><i class="bi {{ $ikon }}"></i></span>
                                    <div class="text-muted small">{{ $label }}</div>
                                    <div class="nilai">{{ $nilai }}</div>
                                    <div class="keterangan text-muted mt-1">{{ $keterangan }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Laporan yang belum utuh harus mengakui dirinya belum utuh. --}}
        @if ($belumLengkap > 0)
            <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-start gap-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <div>
                    <strong class="d-block">{{ $belumLengkap }} pendaftaran belum bisa dihitung untungnya</strong>
                    <span class="small">
                        Modal per orang belum diisi pada paket:
                        {{ implode(', ', $ringkasan['paket_belum_lengkap'] ?? []) }}.
                        Omzetnya tetap terhitung, keuntungannya tidak dikarang.
                        <a href="{{ route('admin.orcha.paket') }}" wire:navigate class="fw-semibold">Isi modalnya</a>.
                    </span>
                </div>
            </div>
        @endif

        {{-- ================= REKAP PER PAKET ================= --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <h6 class="fw-bold mb-0 orcha-ikon-teks">
                        <i class="bi bi-map text-primary"></i> Keuntungan per paket
                    </h6>
                    <span class="text-muted small">Urut dari yang paling menghasilkan</span>
                </div>
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel mb-0">
                        <thead>
                            <tr>
                                <th style="width:2.5rem"></th>
                                <th>Paket</th>
                                <th class="text-end">Jual/orang</th>
                                <th class="text-end">Modal/orang</th>
                                <th class="text-center">Margin/orang</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-end">Omzet</th>
                                <th class="text-end">Keuntungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perPaket as $urutan => $baris)
                                @php
                                    $nilai = (int) $baris['keuntungan'];
                                    $margin = $baris['margin_per_orang'];
                                @endphp
                                <tr wire:key="paket-untung-{{ $baris['kunci'] }}">
                                    <td>
                                        <span class="orcha-urutan {{ $urutan === 0 && $nilai > 0 ? 'puncak' : '' }}">
                                            {{ $urutan + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $baris['kategori_label'] }} · {{ $baris['pendaftaran'] }} pendaftaran
                                            @if ($baris['belum_lengkap'] > 0)
                                                · <span class="text-warning-emphasis fw-semibold">{{ $baris['belum_lengkap'] }} tanpa modal</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap">{{ $baris['harga_jual_teks'] }}</td>
                                    <td class="text-end text-nowrap">{{ $baris['harga_modal_teks'] }}</td>
                                    <td class="text-center">
                                        <span class="orcha-chip-margin {{ $margin === null ? 'kosong' : ((int) $margin < 0 ? 'rugi' : '') }}">
                                            {{ $baris['margin_per_orang_teks'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $baris['peserta'] }}</td>
                                    <td class="text-end text-nowrap">{{ $baris['omzet_teks'] }}</td>
                                    <td class="text-end">
                                        {{-- Nominalnya saja. Baris yang rugi dikenali dari angka
                                             merah bertanda minus, bukan dari penanda tambahan. --}}
                                        <span class="orcha-untung-nilai {{ $nilai < 0 ? 'rugi' : '' }} text-nowrap">
                                            {{ $baris['keuntungan_teks'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-graph-up-arrow"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Belum ada pendaftaran lunas pada rentang ini.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            {{-- ================= PER KATEGORI ================= --}}
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-1 orcha-ikon-teks">
                            <i class="bi bi-pie-chart text-primary"></i> Per jenis layanan
                        </h6>
                        <p class="text-muted small mb-3">
                            Tiap batang memecah omzet layanan itu — bagian abu modalnya,
                            bagian <span class="text-success fw-semibold">hijau</span> untungnya.
                            Makin tebal hijaunya, makin besar untung yang tersisa.
                            <span class="text-danger fw-semibold">Merah</span> berarti modalnya
                            tidak tertutup pemasukan.
                        </p>

                        @php
                            // Porsi tiap layanan terhadap seluruh keuntungan, ditulis sebagai
                            // kalimat di baris keterangan — bukan sebagai panjang batang, karena
                            // batangnya sekarang memecah uang layanan itu sendiri.
                            $persenKategori = $this->porsiBulat($perKategori);
                        @endphp

                        @forelse ($perKategori as $baris)
                            @php
                                $nilaiKategori = (int) $baris['keuntungan'];
                                $modalKategori = (int) $baris['modal'];
                                $omzetKategori = (int) $baris['omzet'];

                                /*
                                 | Batangnya memecah uang layanan itu, sebentuk dengan batang di
                                 | kartu utama. Pembaginya yang lebih besar antara omzet dan
                                 | modal, supaya satu rumus melayani dua keadaan:
                                 |
                                 | - Untung: pembaginya omzet. Abu = modal, hijau = untung,
                                 |   keduanya genap 100% — dan tipisnya hijau itulah gambaran
                                 |   marginnya.
                                 | - Rugi: pembaginya modal, karena modalnya melebihi pemasukan.
                                 |   Abu = bagian modal yang tertutup omzet, merah = sisanya yang
                                 |   tidak tertutup. Genap 100% juga, karena omzet + rugi = modal.
                                 */
                                $dasarBaris = max(1, $omzetKategori, $modalKategori);
                                $bagianModal = min($omzetKategori, $modalKategori) / $dasarBaris * 100;
                                $bagianHasil = abs($nilaiKategori) / $dasarBaris * 100;
                            @endphp
                            <div class="orcha-rekap-baris">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                    <span class="small fw-semibold">{{ $baris['label'] }}</span>
                                    <span class="orcha-untung-nilai {{ $nilaiKategori < 0 ? 'rugi' : '' }} text-nowrap">
                                        {{ $baris['keuntungan_teks'] }}
                                    </span>
                                </div>
                                <div class="orcha-pecah orcha-pecah-terang">
                                    <span class="bagian-modal" style="width: {{ $bagianModal }}%"></span>
                                    <span class="{{ $nilaiKategori < 0 ? 'bagian-rugi' : 'bagian-untung' }}"
                                        style="width: {{ $bagianHasil }}%"></span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:.74rem">
                                    {{ $baris['peserta'] }} peserta · omzet {{ $baris['omzet_teks'] }} ·
                                    modal {{ $baris['modal_teks'] }}
                                    @if ($nilaiKategori > 0)
                                        · {{ $persenKategori[$baris['kunci']] ?? 0 }}% dari untung
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada data pada rentang ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ================= PER BULAN ================= --}}
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-1 orcha-ikon-teks">
                            <i class="bi bi-calendar3 text-primary"></i> Per bulan
                        </h6>
                        <p class="text-muted small mb-3">
                            Sama seperti kartu di sebelah: bagian abu modalnya, bagian
                            <span class="text-success fw-semibold">hijau</span> untungnya.
                            Panah di kanan membandingkan dengan bulan sebelumnya.
                        </p>

                        {{-- Daftarnya dibatasi tingginya dan digulung ke dalam.

                             Bulan terus bertambah — setahun 12 baris, tiga tahun 36 — sedangkan
                             kartu di sebelahnya selalu tiga baris saja. Tanpa batas ini, sepasang
                             kartu itu makin lama makin timpang: yang satu memanjang ke bawah,
                             yang satu menyisakan ruang kosong sepanjang itu juga.

                             Digulung, bukan dipotong: bulan lama tetap bisa dilihat dengan
                             menggulir, tidak ada data yang hilang dari layar. --}}
                        <div class="orcha-gulung-tegak">
                        @php
                            // Bulan terbaru di atas: itu yang dicari admin lebih dulu, dan pada
                            // daftar yang digulung, yang paling dicari tidak boleh berada di
                            // ujung yang harus digulir dulu untuk sampai ke sana.
                            //
                            // Kuncinya sengaja dipertahankan (array_reverse dengan preserve_keys)
                            // supaya perbandingan tetap menunjuk bulan sebelumnya menurut waktu,
                            // bukan menurut urutan tampil.
                            $bulanTampil = array_reverse($perBulan, true);
                        @endphp
                        @forelse ($bulanTampil as $urutanBulan => $baris)
                            @php
                                $sebelumnya = $perBulan[$urutanBulan - 1] ?? null;
                                $ubah = $sebelumnya && (int) $sebelumnya['keuntungan'] > 0
                                    ? ((int) $baris['keuntungan'] - (int) $sebelumnya['keuntungan'])
                                        / (int) $sebelumnya['keuntungan'] * 100
                                    : null;

                                $nilaiBulan = (int) $baris['keuntungan'];
                                $dasarBulan = max(1, (int) $baris['omzet'], (int) $baris['modal']);
                                $modalBulan = min((int) $baris['omzet'], (int) $baris['modal']) / $dasarBulan * 100;
                                $hasilBulan = abs($nilaiBulan) / $dasarBulan * 100;
                            @endphp
                            <div class="orcha-rekap-baris">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                    <span class="small fw-semibold">{{ $baris['bulan_label'] }}</span>
                                    <span class="d-flex align-items-center gap-2">
                                        @if ($ubah !== null && round(abs($ubah)) > 0)
                                            <span class="orcha-ubah {{ $ubah < 0 ? 'turun' : '' }}"
                                                title="{{ $ubah < 0 ? 'Turun' : 'Naik' }} {{ round(abs($ubah)) }}% dibanding {{ $sebelumnya['bulan_label'] }}">
                                                <i class="bi bi-arrow-{{ $ubah < 0 ? 'down' : 'up' }}"></i>
                                                {{ round(abs($ubah)) }}%
                                            </span>
                                        @endif
                                        <span class="orcha-untung-nilai {{ $nilaiBulan < 0 ? 'rugi' : '' }} text-nowrap">
                                            {{ $baris['keuntungan_teks'] }}
                                        </span>
                                    </span>
                                </div>
                                <div class="orcha-pecah orcha-pecah-terang">
                                    <span class="bagian-modal" style="width: {{ $modalBulan }}%"></span>
                                    <span class="{{ $nilaiBulan < 0 ? 'bagian-rugi' : 'bagian-untung' }}"
                                        style="width: {{ $hasilBulan }}%"></span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:.74rem">
                                    {{-- Bulan pembandingnya tidak ditulis di sini: dengan urutan
                                         terbaru di atas, ia baris tepat di bawahnya — dan
                                         mengulangnya membuat tiap baris jadi dua baris,
                                         sehingga bulan yang muat di layar tinggal separuhnya. --}}
                                    {{ $baris['pendaftaran'] }} pendaftaran · {{ $baris['peserta'] }} peserta ·
                                    omzet {{ $baris['omzet_teks'] }} · modal {{ $baris['modal_teks'] }}
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada data pada rentang ini.</p>
                        @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= RINCIAN ================= --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1 orcha-ikon-teks">
                            <i class="bi bi-list-check text-primary"></i> Rincian per pendaftaran
                        </h6>
                        <p class="text-muted small mb-0">
                            Baris-baris yang membentuk angka di atas. Yang belum lunas ikut tampil supaya
                            terlihat mana yang masih perlu ditagih.
                        </p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="untung-hanya-lunas"
                            wire:model.live="hanyaLunas">
                        <label class="form-check-label small" for="untung-hanya-lunas">Hanya yang lunas</label>
                    </div>
                </div>

                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel orcha-tabel-judul-tengah orcha-tabel-rincian mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Pemesan</th>
                                <th>Paket</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-end">Omzet</th>
                                <th class="text-end">Modal</th>
                                <th class="text-end">Keuntungan</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rincian as $baris)
                                <tr wire:key="rincian-{{ $baris['id'] }}">
                                    <td class="orcha-kode">{{ $baris['kode'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.orcha.pendaftaran.detail', $baris['id']) }}"
                                            wire:navigate class="text-decoration-none fw-semibold">
                                            {{ $baris['nama'] }}
                                        </a>
                                        <div class="text-muted" style="font-size:.76rem">
                                            daftar
                                            {{ $baris['tanggal_daftar'] ? \Carbon\Carbon::parse($baris['tanggal_daftar'])->locale('id')->translatedFormat('d M Y') : '—' }}
                                        </div>
                                    </td>
                                    <td class="small">
                                        {{ $baris['paket'] }}
                                        <div class="text-muted" style="font-size:.76rem">
                                            berangkat
                                            {{ $baris['tanggal_berangkat'] ? \Carbon\Carbon::parse($baris['tanggal_berangkat'])->locale('id')->translatedFormat('d M Y') : '—' }}
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $baris['peserta'] }}</td>
                                    <td class="text-end text-nowrap">{{ $baris['omzet_teks'] }}</td>
                                    <td class="text-end text-nowrap">{{ $baris['modal_teks'] }}</td>
                                    <td class="text-end text-nowrap">
                                        <span class="orcha-untung-nilai {{ $baris['keuntungan'] === null ? 'kosong' : ((int) $baris['keuntungan'] < 0 ? 'rugi' : '') }}">
                                            {{ $baris['keuntungan_teks'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{-- Lencana status memakai tiga warna yang sudah dipakai bukti
                                             bayar: hijau untuk yang uangnya utuh, kuning untuk yang
                                             masih menggantung, merah untuk yang batal. --}}
                                        @php
                                            $warna = match ($baris['status']) {
                                                'lunas' => 'orcha-lencana-bayar-diterima',
                                                'batal' => 'orcha-lencana-bayar-ditolak',
                                                default => 'orcha-lencana-bayar-menunggu',
                                            };
                                        @endphp
                                        <span class="badge {{ $warna }}">{{ $baris['status_label'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <p class="text-muted mb-0">Tidak ada pendaftaran pada rentang ini.</p>
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
