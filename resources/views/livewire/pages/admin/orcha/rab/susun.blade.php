@section('title')
Susun RAB || lemon
@stop

{{-- Penyusun RAB.

     Semua perbandingan dirakit jadi boolean di blok php di bawah ini, bukan
     ditulis di kondisi direktif: tanda "lebih besar" di kondisi direktif blok
     membuat Livewire melewatkan penanda morph-nya (PenandaMorphAdminTest). --}}
@php
    $ada = $rab !== [];
    $r = $rab['ringkasan'] ?? [];
    $jumlahHari = max(1, (int) ($rab['jumlah_hari'] ?? 1));
    $sudahPendaftaran = ! empty($rab['kode_pendaftaran']);
    $statusKini = $rab['status'] ?? 'draf';
    $kosakata = $katalog['kosakata'] ?? [];

    $kegiatanPerHari = [];
    foreach ($itinerary as $i => $k) {
        $kegiatanPerHari[$k['hari_ke']][$i] = $k;
    }
    $kegiatanAktif = $kegiatanPerHari[$hariAktif] ?? [];
    $hariAktifKosong = $kegiatanAktif === [];
    $idxPertama = array_key_first($kegiatanAktif);
    $idxTerakhir = array_key_last($kegiatanAktif);

    $destinasiDipakai = collect($itinerary)->pluck('destinasi')->filter()->flip()->all();
    $kata = mb_strtolower(trim($cariDestinasi));
    $destinasi = collect($katalog['destinasi'] ?? [])
        ->filter(fn ($d) => $kata === '' || str_contains(mb_strtolower($d['nama'].' '.($d['daerah'] ?? '')), $kata))
        ->values()->all();
    $tanpaDestinasi = $destinasi === [];

    $masterPerKategori = collect($katalog['master'] ?? [])->groupBy('kategori_label')->all();
    // Urutan kategorinya sudah dari Orcha (tiket, transportasi, akomodasi, …).
    $barisPerKategori = collect($r['baris'] ?? [])->groupBy('kategori_label')->all();
    $tanpaBiaya = $barisPerKategori === [];
    $adaTanpaTiket = $tanpaTiket !== [];
    $hargaBasi = (bool) ($r['ada_harga_basi'] ?? false);
    $manualBerkapasitas = in_array($manual['satuan'] ?? '', ['unit_hari', 'kamar_malam'], true);
    $marginPersen = $marginJenis === 'persen';
    $marginBelumTersimpan = $marginJenis !== ($rab['margin_jenis'] ?? $marginJenis);

    $tanggalTeks = ! empty($rab['tanggal_mulai'])
        ? \Illuminate\Support\Carbon::parse($rab['tanggal_mulai'])->locale('id')->translatedFormat('j M Y')
        : 'Tanggal belum pasti';
    $warnaStatus = [
        'draf' => 'bg-secondary-subtle text-secondary-emphasis',
        'dikirim' => 'bg-primary-subtle text-primary-emphasis',
        'disetujui' => 'bg-success-subtle text-success-emphasis',
        'batal' => 'bg-danger-subtle text-danger-emphasis',
    ];
    $kelasStatus = $warnaStatus[$statusKini] ?? $warnaStatus['draf'];
    $bolehJadiPendaftaran = ! $sudahPendaftaran && $statusKini !== 'batal' && ! $tanpaBiaya;
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <style>
        .rab-fakta { display: flex; flex-wrap: wrap; gap: .5rem; }
        .rab-fakta span { background: var(--orc-panel-latar); border: var(--orc-panel-garis); border-radius: 999px; padding: .3rem .8rem; font-size: .82rem; color: var(--orc-tinta); }
        .rab-hari-tab { display: flex; flex-wrap: wrap; gap: .5rem; }
        .rab-hari-tab button { border: 2px solid #e2e8f0; background: #fff; border-radius: 12px; padding: .45rem .9rem; font-weight: 600; font-size: .85rem; color: var(--orc-tinta); }
        .rab-hari-tab button.aktif { border-color: var(--orc-primer); background: var(--orc-gradien); color: #fff; }
        .rab-hari-tab small { opacity: .75; font-weight: 500; margin-left: .25rem; }
        .rab-kegiatan { display: grid; grid-template-columns: 124px 1fr auto; grid-template-areas: "jam nama aksi" ". ket ket"; gap: .5rem .6rem; align-items: center; padding: .75rem; border: var(--orc-panel-garis); border-radius: 14px; background: #fff; margin-bottom: .5rem; }
        .rab-kegiatan .rab-jam { grid-area: jam; }
        .rab-kegiatan .rab-nama { grid-area: nama; }
        .rab-kegiatan .rab-ket { grid-area: ket; }
        .rab-kegiatan .rab-kegiatan-aksi { grid-area: aksi; }
        .rab-kegiatan .form-control, .rab-kegiatan .form-select { height: 38px !important; min-height: 38px; font-size: .88rem; padding-left: .75rem !important; }
        .rab-kegiatan-aksi { display: flex; gap: .3rem; align-items: center; }
        .rab-kegiatan-aksi .btn { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
        .rab-kegiatan-aksi .form-select { width: 74px; padding-right: 1.6rem; }
        .rab-destinasi { display: flex; flex-wrap: wrap; gap: .45rem; max-height: 260px; overflow-y: auto; padding: .25rem; }
        .rab-destinasi button { border: 1px solid #dbe3ec; background: #fff; border-radius: 999px; padding: .4rem .8rem; font-size: .83rem; color: var(--orc-tinta); display: inline-flex; gap: .35rem; align-items: center; }
        .rab-destinasi button:hover { border-color: var(--orc-primer); color: var(--orc-primer); }
        .rab-destinasi button.dipakai { background: #eafaf1; border-color: #9fd6b4; color: #14683f; font-weight: 600; }
        .rab-destinasi .tiket { color: #b7791f; }
        .rab-lengket { position: sticky; top: 90px; }
        .rab-harga-besar { font-size: 1.9rem; font-weight: 800; color: var(--orc-primer); line-height: 1.1; }
        .rab-baris-angka { display: flex; justify-content: space-between; font-size: .88rem; padding: .3rem 0; color: var(--orc-tinta); }
        .rab-baris-angka.garis { border-top: 1px dashed #e2e8f0; margin-top: .3rem; padding-top: .55rem; }
        .rab-pilih-margin { display: grid; grid-template-columns: repeat(3, 1fr); gap: .4rem; }
        .rab-pilih-margin label { border: 2px solid #e2e8f0; border-radius: 10px; padding: .45rem .3rem; text-align: center; font-size: .78rem; font-weight: 600; cursor: pointer; color: var(--orc-tinta); }
        .rab-pilih-margin label.aktif { border-color: var(--orc-primer); color: var(--orc-primer); background: #f5f0ff; }
        .rab-pilih-margin input { display: none; }
        .rab-tabel-biaya .form-control { height: 36px !important; min-height: 36px; font-size: .85rem; padding-left: .6rem !important; text-align: right; }
        .rab-kat td { background: var(--orc-panel-latar) !important; font-size: .75rem; letter-spacing: .08em; text-transform: uppercase; font-weight: 700; color: var(--orc-primer); }
        .rab-basi { font-size: .75rem; color: #b42318; }
        @media (max-width: 575.98px) {
            .rab-kegiatan { grid-template-columns: 1fr; grid-template-areas: "jam" "nama" "ket" "aksi"; }
            .rab-kegiatan-aksi { justify-content: flex-end; }
        }
    </style>

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Susun RAB',
            'keterangan' => 'Klik destinasi untuk menyusun itinerary; harga dan PDF-nya mengikuti.',
        ])

        @if ($ada)
            {{-- ========================= KEPALA RAB ========================= --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="orcha-kode">{{ $rab['kode'] }}</span>
                                <span class="badge {{ $kelasStatus }}">{{ $rab['status_label'] }}</span>
                                @if ($rab['kedaluwarsa'])
                                    <span class="badge bg-warning-subtle text-warning-emphasis">Lewat masa berlaku</span>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-1">{{ $rab['judul'] }}</h5>
                            <div class="text-muted small mb-2">
                                {{ $rab['nama_pelanggan'] }}
                                @if ($rab['whatsapp']) · {{ $rab['whatsapp'] }} @endif
                            </div>
                            <div class="rab-fakta">
                                <span><i class="bi bi-geo-alt"></i> {{ $rab['daerah'] ? $rab['daerah'].', ' : '' }}{{ $rab['provinsi'] }}</span>
                                <span><i class="bi bi-calendar3"></i> {{ $tanggalTeks }}</span>
                                <span><i class="bi bi-moon-stars"></i> {{ $rab['jumlah_hari'] }} hari {{ $rab['jumlah_malam'] }} malam</span>
                                <span><i class="bi bi-people"></i> {{ $rab['jumlah_peserta'] }} peserta</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-start justify-content-lg-end">
                            <select class="form-select form-select-sm w-auto" wire:change="ubahStatus($event.target.value)" title="Status RAB">
                                @foreach ($kosakata['status'] ?? [] as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected($kunci === $statusKini)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="$toggle('bukaKepala')" class="orcha-btn orcha-btn-lembut">
                                <i class="bi bi-sliders"></i> Data perjalanan
                            </button>
                            <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $rabId, 'jenis' => 'penawaran']) }}" class="orcha-btn orcha-btn-utama">
                                <i class="bi bi-file-earmark-pdf"></i> PDF Penawaran
                            </a>
                            <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $rabId, 'jenis' => 'internal']) }}" class="orcha-btn orcha-btn-lembut"
                                title="Berisi modal & margin — jangan dikirim ke pelanggan">
                                <i class="bi bi-lock"></i> PDF Internal
                            </a>
                        </div>
                    </div>

                    @if ($bukaKepala)
                        <div class="border-top mt-3 pt-3">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Judul <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="kepala.judul" maxlength="150" class="form-control @error('kepala.judul') is-invalid @enderror">
                                    @error('kepala.judul')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Nama pelanggan <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="kepala.nama_pelanggan" maxlength="120" class="form-control @error('kepala.nama_pelanggan') is-invalid @enderror">
                                    @error('kepala.nama_pelanggan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">WhatsApp</label>
                                    <input type="text" wire:model="kepala.whatsapp" maxlength="32" class="form-control">
                                    <div class="form-text">Wajib sebelum dijadikan pendaftaran.</div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Email</label>
                                    <input type="email" wire:model="kepala.email" maxlength="150" class="form-control @error('kepala.email') is-invalid @enderror">
                                    @error('kepala.email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Kota / daerah</label>
                                    <input type="text" wire:model="kepala.daerah" maxlength="80" class="form-control">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Tanggal berangkat</label>
                                    <input type="date" wire:model="kepala.tanggal_mulai" class="form-control">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Penawaran berlaku sampai</label>
                                    <input type="date" wire:model="kepala.berlaku_sampai" class="form-control">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label small fw-semibold">Hari</label>
                                    <input type="number" min="1" max="30" wire:model="kepala.jumlah_hari" class="form-control @error('kepala.jumlah_hari') is-invalid @enderror">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label small fw-semibold">Malam</label>
                                    <input type="number" min="0" max="30" wire:model="kepala.jumlah_malam" class="form-control @error('kepala.jumlah_malam') is-invalid @enderror">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label small fw-semibold">Peserta</label>
                                    <input type="number" min="1" max="500" wire:model="kepala.jumlah_peserta" class="form-control @error('kepala.jumlah_peserta') is-invalid @enderror">
                                </div>
                                @error('kepala.jumlah_hari')<div class="col-12 text-danger small mt-1">{{ $message }}</div>@enderror
                                @error('kepala.jumlah_malam')<div class="col-12 text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Catatan di penawaran <span class="text-muted fw-normal">(dibaca pelanggan)</span></label>
                                    <textarea wire:model="kepala.catatan_penawaran" rows="3" maxlength="3000" class="form-control"
                                        placeholder="Harga belum termasuk tiket pesawat PP."></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Catatan internal <span class="text-muted fw-normal">(hanya di PDF internal)</span></label>
                                    <textarea wire:model="kepala.catatan" rows="3" maxlength="3000" class="form-control"
                                        placeholder="Vendor, hasil negosiasi, hal yang perlu dicek."></textarea>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end pt-3">
                                <button type="button" wire:click="$set('bukaKepala', false)" class="orcha-btn orcha-btn-lembut">Batal</button>
                                <button type="button" wire:click="simpanKepala" wire:loading.attr="disabled" wire:target="simpanKepala" class="orcha-btn orcha-btn-utama">
                                    <i class="bi bi-check2-circle"></i> Simpan
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    {{-- ========================= ITINERARY ========================= --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="orcha-bagian-kepala">
                                <div class="orcha-bagian-nomor">1</div>
                                <div>
                                    <div class="orcha-bagian-judul">Itinerary</div>
                                    <div class="orcha-bagian-sub">Pilih harinya, lalu klik destinasi di bawah. Jam dan keterangan boleh diisi belakangan.</div>
                                </div>
                            </div>

                            <div class="rab-hari-tab mb-3">
                                @for ($h = 1; $h <= $jumlahHari; $h++)
                                    <button type="button" wire:click="pilihHari({{ $h }})" class="{{ $h === $hariAktif ? 'aktif' : '' }}">
                                        Hari {{ $h }}<small>{{ count($kegiatanPerHari[$h] ?? []) }}</small>
                                    </button>
                                @endfor
                            </div>

                            @if ($hariAktifKosong)
                                <div class="orcha-kosong">Hari {{ $hariAktif }} belum berisi kegiatan — di PDF tertulis "waktu bebas".</div>
                            @endif

                            @foreach ($kegiatanAktif as $i => $k)
                                <div class="rab-kegiatan" wire:key="kg-{{ $i }}-{{ md5($k['nama'].$k['hari_ke']) }}">
                                    <input type="time" wire:model.blur="itinerary.{{ $i }}.jam" class="form-control rab-jam" title="Jam">
                                    <input type="text" wire:model.blur="itinerary.{{ $i }}.nama" maxlength="150" class="form-control fw-semibold rab-nama"
                                        title="{{ $k['destinasi'] ? 'Destinasi: '.$k['destinasi'] : 'Kegiatan' }}">
                                    <input type="text" wire:model.blur="itinerary.{{ $i }}.keterangan" maxlength="500"
                                        class="form-control rab-ket" placeholder="Keterangan untuk pelanggan (boleh kosong)">
                                    <div class="rab-kegiatan-aksi">
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-lihat" wire:click="geser({{ $i }}, -1)"
                                            title="Naikkan" @disabled($i === $idxPertama)><i class="bi bi-arrow-up"></i></button>
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-lihat" wire:click="geser({{ $i }}, 1)"
                                            title="Turunkan" @disabled($i === $idxTerakhir)><i class="bi bi-arrow-down"></i></button>
                                        <select class="form-select form-select-sm" title="Pindah ke hari lain"
                                            wire:change="pindahHari({{ $i }}, $event.target.value)">
                                            @for ($h = 1; $h <= $jumlahHari; $h++)
                                                <option value="{{ $h }}" @selected($h === $hariAktif)>H{{ $h }}</option>
                                            @endfor
                                        </select>
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus" wire:click="hapusKegiatan({{ $i }})" title="Hapus kegiatan">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            <div class="input-group mt-2 mb-4">
                                <input type="text" wire:model="kegiatanBaru" wire:keydown.enter.prevent="tambahKegiatan" maxlength="150"
                                    class="form-control" placeholder="Kegiatan lain: Check-in hotel, makan malam, perjalanan pulang…">
                                <button type="button" wire:click="tambahKegiatan" class="orcha-btn orcha-btn-lembut">
                                    <i class="bi bi-plus-lg"></i> Tambah ke Hari {{ $hariAktif }}
                                </button>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div class="orcha-label-kecil">
                                    <i class="bi bi-hand-index"></i> Klik untuk menambah ke Hari {{ $hariAktif }} ·
                                    <i class="bi bi-ticket-perforated rab-tiket text-warning"></i> sudah ada harga tiket
                                </div>
                                <div style="min-width: 220px">
                                    <input type="text" wire:model.live.debounce.300ms="cariDestinasi" class="form-control form-control-sm" placeholder="Cari destinasi…">
                                </div>
                            </div>
                            <div class="rab-destinasi">
                                @foreach ($destinasi as $d)
                                    <button type="button" wire:key="ds-{{ md5($d['nama']) }}"
                                        wire:click="tambahDestinasi(@js($d['nama']))"
                                        class="{{ isset($destinasiDipakai[$d['nama']]) ? 'dipakai' : '' }}"
                                        title="{{ $d['daerah'] }}">
                                        @if ($d['punya_tiket'])<i class="bi bi-ticket-perforated tiket"></i>@endif
                                        {{ $d['nama'] }}
                                    </button>
                                @endforeach
                                @if ($tanpaDestinasi)
                                    <div class="orcha-kosong mb-0">Tidak ada destinasi katalog untuk provinsi ini. Tambahkan lewat menu Destinasi Populer, atau ketik sebagai kegiatan lain.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ========================= BIAYA ========================= --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="orcha-bagian-kepala">
                                <div class="orcha-bagian-nomor">2</div>
                                <div>
                                    <div class="orcha-bagian-judul">Rincian biaya</div>
                                    <div class="orcha-bagian-sub">Tarik tiket dari itinerary, lalu pilih armada, hotel, dan lainnya dari master harga.</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                <button type="button" wire:click="tarikBiaya" wire:loading.attr="disabled" wire:target="tarikBiaya" class="orcha-btn orcha-btn-utama text-nowrap">
                                    <i class="bi bi-magic"></i> Tarik biaya dari itinerary
                                </button>
                                <div class="flex-grow-1" style="min-width: 260px">
                                    <div class="input-group">
                                        <select wire:model="pilihMaster" class="form-select">
                                            <option value="">Pilih dari master harga…</option>
                                            @foreach ($masterPerKategori as $label => $isi)
                                                <optgroup label="{{ $label }}">
                                                    @foreach ($isi as $m)
                                                        <option value="{{ $m['id'] }}">{{ $m['nama'] }} — {{ $m['harga_teks'] }} / {{ $m['satuan_label'] }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="tambahDariMaster" class="orcha-btn orcha-btn-lembut">
                                            <i class="bi bi-plus-lg"></i> Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @if ($adaTanpaTiket)
                                <div class="alert alert-warning border-0 rounded-3 small py-2">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Belum ada harga tiket di master untuk: <strong>{{ implode(', ', $tanpaTiket) }}</strong>.
                                    Bila memang berbayar, tambahkan manual di bawah atau isi di
                                    <a href="{{ route('admin.orcha.master-harga') }}" target="_blank">Master Harga</a>.
                                </div>
                            @endif

                            @if ($hargaBasi)
                                <div class="alert alert-danger border-0 rounded-3 small py-2">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Sebagian harga master sudah berubah sejak masuk ke RAB ini. Baris yang bertanda merah masih memakai harga lama.
                                </div>
                            @endif

                            <div class="orcha-gulung">
                                <table class="table align-middle orcha-tabel rab-tabel-biaya mb-0">
                                    <thead>
                                        <tr>
                                            <th>BIAYA</th>
                                            <th>PERHITUNGAN</th>
                                            <th style="width: 120px" class="text-end">HARGA</th>
                                            <th style="width: 70px" class="text-end">QTY</th>
                                            <th class="text-end">SUBTOTAL</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($barisPerKategori as $label => $isi)
                                            <tr class="rab-kat"><td colspan="6">{{ $label }}</td></tr>
                                            @foreach ($isi as $b)
                                                <tr wire:key="by-{{ $b['id'] }}">
                                                    <td>
                                                        <div class="fw-semibold">{{ $b['nama'] }}</div>
                                                        <div class="small text-muted">
                                                            {{ $b['satuan_label'] }}{{ $b['kapasitas'] ? ' · kapasitas '.$b['kapasitas'] : '' }}
                                                        </div>
                                                        @isset($b['harga_master_kini'])
                                                            <div class="rab-basi">
                                                                Master kini Rp {{ number_format($b['harga_master_kini'], 0, ',', '.') }} ·
                                                                <a href="#" wire:click.prevent="pakaiHargaMaster({{ $b['id'] }})">pakai harga baru</a>
                                                            </div>
                                                        @endisset
                                                    </td>
                                                    <td class="small text-muted">{{ $b['penjelasan'] }}</td>
                                                    <td><input type="text" inputmode="numeric" wire:model.blur="hargaBaris.{{ $b['id'] }}" class="form-control"></td>
                                                    <td><input type="number" min="1" max="100" wire:model.blur="jumlahBaris.{{ $b['id'] }}" class="form-control"></td>
                                                    <td class="text-end fw-bold text-nowrap">{{ $b['subtotal_teks'] }}</td>
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                                            data-action="hapusBiaya" data-arg="{{ $b['id'] }}"
                                                            data-title="Hapus {{ $b['nama'] }}?" data-confirm="Ya, hapus" data-icon="warning"
                                                            title="Hapus biaya">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        @if ($tanpaBiaya)
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    Belum ada biaya. Susun itinerary lalu tekan <strong>Tarik biaya dari itinerary</strong>.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                @if ($bukaManual)
                                    <div class="border rounded-4 p-3">
                                        <div class="fw-semibold small mb-2">Biaya manual</div>
                                        <div class="row g-2">
                                            <div class="col-12 col-md-4">
                                                <input type="text" wire:model="manual.nama" maxlength="150" class="form-control @error('manual.nama') is-invalid @enderror" placeholder="Dokumentasi drone">
                                                @error('manual.nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select wire:model="manual.kategori" class="form-select">
                                                    @foreach ($kosakata['kategori'] ?? [] as $kunci => $label)
                                                        <option value="{{ $kunci }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select wire:model.live="manual.satuan" class="form-select">
                                                    @foreach ($kosakata['satuan'] ?? [] as $kunci => $label)
                                                        <option value="{{ $kunci }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <input type="text" inputmode="numeric" wire:model.blur="manual.harga_satuan" class="form-control @error('manual.harga_satuan') is-invalid @enderror" placeholder="Harga">
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <input type="number" min="1" wire:model="manual.kapasitas" class="form-control @error('manual.kapasitas') is-invalid @enderror"
                                                    placeholder="{{ $manualBerkapasitas ? 'Kapasitas' : '—' }}" @disabled(! $manualBerkapasitas)>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 justify-content-end mt-2">
                                            <button type="button" wire:click="$set('bukaManual', false)" class="orcha-btn orcha-btn-lembut">Batal</button>
                                            <button type="button" wire:click="tambahManual" class="orcha-btn orcha-btn-utama"><i class="bi bi-plus-lg"></i> Tambahkan</button>
                                        </div>
                                    </div>
                                @else
                                    <button type="button" wire:click="$set('bukaManual', true)" class="btn btn-link btn-sm p-0 text-decoration-none">
                                        <i class="bi bi-pencil"></i> Tambah biaya manual (tidak ada di master)
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================= RINGKASAN ========================= --}}
                <div class="col-12 col-xl-4">
                    <div class="rab-lengket">
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-3 p-lg-4">
                                <div class="orcha-label-kecil mb-1">Harga per orang</div>
                                <div class="rab-harga-besar">{{ $r['harga_per_orang_teks'] ?? 'Rp 0' }}</div>
                                <div class="text-muted small mb-3">Total {{ $r['harga_total_teks'] ?? 'Rp 0' }} untuk {{ $rab['jumlah_peserta'] }} peserta</div>

                                <div class="rab-baris-angka"><span>Modal total</span><strong>{{ $r['modal_total_teks'] ?? 'Rp 0' }}</strong></div>
                                <div class="rab-baris-angka text-muted"><span>Modal per kepala</span><span>{{ $r['modal_per_kepala_teks'] ?? 'Rp 0' }}</span></div>
                                <div class="rab-baris-angka"><span>Margin diminta</span><span>{{ $r['margin_diminta_teks'] ?? 'Rp 0' }}</span></div>
                                <div class="rab-baris-angka garis text-success">
                                    <span class="fw-bold">Untung</span>
                                    <strong>{{ $r['untung_teks'] ?? 'Rp 0' }}@isset($r['persen_untung']) <small>({{ str_replace('.', ',', $r['persen_untung']) }}%)</small>@endisset</strong>
                                </div>

                                <div class="border-top mt-3 pt-3">
                                    <div class="orcha-label-kecil mb-2">Margin</div>
                                    <div class="rab-pilih-margin mb-2">
                                        @foreach ($kosakata['margin_jenis'] ?? [] as $kunci => $label)
                                            <label class="{{ $marginJenis === $kunci ? 'aktif' : '' }}">
                                                <input type="radio" value="{{ $kunci }}" wire:model.live="marginJenis">{{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @if ($marginPersen)
                                        <div class="input-group mb-2">
                                            <input type="text" inputmode="numeric" wire:model.blur="marginNilai" class="form-control" placeholder="20">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    @else
                                        <div class="orcha-rupiah mb-2">
                                            <input type="text" inputmode="numeric" wire:model.blur="marginNilai" class="orcha-uang form-control" placeholder="150.000">
                                        </div>
                                    @endif
                                    @if ($marginBelumTersimpan)
                                        <div class="small text-warning-emphasis mb-2"><i class="bi bi-info-circle"></i> Isi angkanya — jenis margin baru tersimpan setelah angkanya diisi.</div>
                                    @endif
                                    <div class="d-flex align-items-center gap-2 small">
                                        <span class="text-muted">Bulatkan ke atas per</span>
                                        <select wire:model.live="pembulatan" class="form-select form-select-sm w-auto">
                                            @foreach ([1 => 'Rp 1', 1000 => 'Rp 1.000', 5000 => 'Rp 5.000', 10000 => 'Rp 10.000'] as $nilai => $teks)
                                                <option value="{{ $nilai }}">{{ $teks }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @unless ($tanpaBiaya)
                                    <div class="border-top mt-3 pt-3">
                                        <div class="orcha-label-kecil mb-2">Per kategori</div>
                                        @foreach ($r['per_kategori'] ?? [] as $kat)
                                            <div class="rab-baris-angka"><span>{{ $kat['label'] }}</span><span>{{ $kat['total_teks'] }}</span></div>
                                        @endforeach
                                    </div>
                                @endunless
                            </div>
                        </div>

                        {{-- ===================== JADIKAN PENDAFTARAN ===================== --}}
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-3 p-lg-4">
                                <div class="orcha-label-kecil mb-2"><i class="bi bi-clipboard-check"></i> Pelanggan setuju?</div>
                                @if ($sudahPendaftaran)
                                    <p class="small mb-2">Sudah dijadikan pendaftaran <span class="orcha-kode">{{ $rab['kode_pendaftaran'] }}</span>.</p>
                                    @if ($rab['pendaftaran_id'])
                                        <a href="{{ route('admin.orcha.pendaftaran.detail', $rab['pendaftaran_id']) }}" class="orcha-btn orcha-btn-lembut" wire:navigate>
                                            <i class="bi bi-box-arrow-up-right"></i> Buka pendaftaran
                                        </a>
                                    @endif
                                @elseif ($bolehJadiPendaftaran)
                                    <p class="small text-muted mb-2">
                                        Harga jual, modal per orang, dan biaya tetapnya terisi sendiri dari RAB ini — laporan keuntungan akan sama persis.
                                    </p>
                                    <select wire:model="paketId" class="form-select mb-2 @error('paketId') is-invalid @enderror">
                                        <option value="">Pilih paket induk…</option>
                                        @foreach ($katalog['paket'] ?? [] as $p)
                                            <option value="{{ $p['id'] }}">{{ $p['nama'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('paketId')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                                    <button type="button" class="orcha-btn orcha-btn-utama w-100 justify-content-center pcek-konfirmasi"
                                        data-action="jadikanPendaftaran"
                                        data-title="Jadikan pendaftaran?"
                                        data-text="RAB ditandai disetujui dan pendaftaran baru dibuat dengan harga {{ $r['harga_per_orang_teks'] ?? '' }} per orang."
                                        data-confirm="Ya, buat pendaftaran" data-icon="question">
                                        <i class="bi bi-check2-circle"></i> Jadikan Pendaftaran
                                    </button>
                                @else
                                    <p class="small text-muted mb-0">Tersedia setelah RAB berisi biaya dan tidak berstatus batal.</p>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('admin.orcha.rab') }}" wire:navigate class="orcha-tautan-balik small text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Kembali ke daftar RAB
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
