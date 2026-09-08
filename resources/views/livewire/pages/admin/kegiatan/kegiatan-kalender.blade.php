@section('title')
Kalender Kegiatan || lemon
@stop

<div>
    <style>
        /* Ditulis inline: public/build tidak ikut terdeploy ke server. */

        /* ===== Tombol =====
           Layout admin menyetel `.btn .bi { display: flex }` untuk SEMUA tombol.
           Kotak flex itu elemen blok, jadi setiap ikon di dalam tombol turun ke
           barisnya sendiri — itulah sebab "Tambah" dan "Hari ini" tampil dua
           baris dengan tinggi yang tidak seragam.

           Perbaikannya bukan melawan aturan itu, melainkan menjadikan TOMBOLNYA
           wadah flex: ikon dan teks lalu berdiri sebagai dua item sebaris,
           rata tengah, dengan jarak dari `gap` (bukan margin) sehingga sisa
           ruang kiri-kanan tetap seimbang. */
        .kg-btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 7px;
            line-height: 1;
            white-space: nowrap;
        }
        .kg-btn i.bi { font-size: 1rem !important; line-height: 1; flex: 0 0 auto; }
        .kg-btn i.bi::before { display: block; line-height: 1; }
        /* Jarak sudah dipegang gap; margin bawaan hanya menggeser teks dari tengah. */
        .kg-btn i.me-1 { margin-right: 0 !important; }

        /* Semua tombol batang alat setinggi sama persis, apa pun isinya. */
        .kg-alat .kg-btn { height: 42px; padding: 0 14px !important; }
        .kg-alat .kg-nav { width: 42px; padding: 0 !important; }

        /* Tombol kecil di dalam kartu. `.btn { padding: 10px 20px !important }`
           dari layout berlaku juga untuk .btn-sm, jadi tingginya harus disetel
           tegas di sini — kalau tidak, tombol "kecil" sama besar dengan yang biasa. */
        .kg-btn-kecil { height: 34px; padding: 0 13px !important; font-size: .81rem; }
        .kg-btn-kecil i.bi { font-size: .88rem !important; }

        /* ===== Lambang kepala halaman ===== */
        .kg-ikon i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; position: relative; z-index: 1; }
        .kg-ikon i.bi::before { display: block; line-height: 1; }
        .kg-ikon {
            position: relative; overflow: hidden; border-radius: 18px;
            background: linear-gradient(135deg, #a78bfa, #6d28d9);
            box-shadow: 0 8px 18px rgba(109,40,217,.24), 0 0 0 5px rgba(139,92,246,.10);
        }
        .kg-ikon::after {
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.24), transparent 62%);
        }
        .kg-ringkas { display: flex; align-items: center; gap: 14px; }
        .kg-ringkas-teks { line-height: 1.35; }
        .kg-ringkas-teks b { display: block; font-size: .98rem; color: #1e293b; }
        .kg-ringkas-teks small { color: #94a3b8; }

        /* ===== Batang alat ===== */
        .kg-bulan {
            min-width: 148px; text-align: center; font-weight: 700;
            font-size: 1rem; color: #1e293b; letter-spacing: -.01em;
        }

        /* Legenda jenis. Saat tidak terpilih chip memakai warna JENISNYA sendiri
           dalam nada lembut — bukan putih dengan titik kecil. Dengan begitu
           batang saring sekaligus menjadi legenda: warna di chip sama persis
           dengan warna kegiatannya di kisi. */
        /* Tetap satu baris bersama navigasi bulan. Ukurannya dirampingkan
           secukupnya agar tujuh chip muat di sisa ruang sebelah sidebar; masih
           boleh membungkus di layar sempit, tapi tidak lagi pada lebar biasa. */
        .kg-saring { display: flex; flex-wrap: wrap; gap: 6px; }

        /* Saat legenda duduk di kanan, baris yang terpaksa membungkus ikut rata
           kanan — kalau rata kiri, sisanya terlihat menggantung di tengah kartu. */
        @media (min-width: 992px) { .kg-saring { justify-content: flex-end; } }
        .kg-chip {
            border: 1px solid transparent; border-radius: 999px;
            padding: 0 13px; height: 42px;
            font-size: .81rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: 6px;
            cursor: pointer; transition: transform .12s ease, box-shadow .15s ease, filter .15s ease;
            background: var(--kg-lembut); color: var(--kg-warna);
        }
        .kg-chip i.bi { font-size: .9rem; line-height: 1; }
        .kg-chip i.bi::before { display: block; line-height: 1; }
        .kg-chip:hover { filter: brightness(.97); }
        .kg-chip.aktif {
            background: var(--kg-warna); color: #fff;
            box-shadow: 0 6px 14px -4px var(--kg-warna);
        }
        .kg-chip:active { transform: scale(.97); }
        .kg-titik { width: 9px; height: 9px; border-radius: 50%; flex: 0 0 auto; background: var(--kg-warna); }
        .kg-chip.aktif .kg-titik { background: rgba(255,255,255,.9); }

        /* ===== Kisi kalender ===== */
        .kg-kisi { width: 100%; border-collapse: separate; border-spacing: 7px; table-layout: fixed; }
        .kg-kisi th {
            font-size: .72rem; text-transform: uppercase; letter-spacing: .07em;
            color: #a8b3c4; font-weight: 700; padding-bottom: 4px; text-align: center;
        }
        .kg-sel {
            vertical-align: top; height: 108px; padding: 8px;
            border: 1px solid #eef1f6; border-radius: 16px; background: #fff;
            cursor: pointer;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .kg-sel:hover { border-color: #ddd8fb; box-shadow: 0 6px 16px -6px rgba(109,40,217,.28); }
        .kg-sel.kg-luar { background: #fbfcfd; border-color: #f2f5f8; }
        .kg-sel.kg-luar .kg-angka { color: #cbd5e1; }
        .kg-sel.kg-pekan { background: #fcfcfe; }
        .kg-sel.kg-terpilih { border-color: #a78bfa; box-shadow: 0 0 0 3px rgba(167,139,250,.22); }
        .kg-angka {
            font-size: .82rem; font-weight: 700; color: #64748b;
            width: 27px; height: 27px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 50%; line-height: 1;
        }
        .kg-sel.kg-hariini .kg-angka {
            background: linear-gradient(135deg, #a78bfa, #6d28d9); color: #fff;
            box-shadow: 0 4px 10px -2px rgba(109,40,217,.45);
        }

        /* Kegiatan di dalam sel: pil berwarna lembut dengan pita warna pekat di
           tepi kiri. Blok penuh warna pekat lima baris berturut-turut membuat
           kisinya berteriak; nada lembut menjaga angka tanggal tetap terbaca. */
        .kg-acara {
            display: flex; align-items: center; gap: 5px; width: 100%;
            font-size: .73rem; line-height: 1.35; padding: 3px 7px; margin-top: 4px;
            border-radius: 6px; font-weight: 600; text-align: left;
            background: var(--kg-lembut); color: var(--kg-warna);
            border: 0; border-left: 3px solid var(--kg-warna);
        }
        .kg-acara span {
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;
        }
        .kg-acara.kg-lewat { opacity: .5; }
        .kg-lebih { font-size: .7rem; color: #a8b3c4; font-weight: 600; margin-top: 4px; display: block; }

        /* ===== Daftar hari terpilih & agenda ===== */
        .kg-judul-kartu { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }
        .kg-baris {
            display: flex; gap: 12px; padding: 12px 14px; border-radius: 14px;
            border: 1px solid #eef1f6; background: #fff; margin-bottom: 10px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .kg-baris:hover { border-color: #e3e8ef; box-shadow: 0 4px 12px -6px rgba(15,23,42,.18); }
        .kg-pita { width: 4px; border-radius: 999px; flex: 0 0 auto; background: var(--kg-warna); }
        .kg-baris-isi { flex: 1 1 auto; min-width: 0; }
        .kg-baris-isi b { display: block; font-size: .93rem; color: #1e293b; line-height: 1.35; }
        .kg-meta { font-size: .78rem; color: #94a3b8; display: flex; flex-wrap: wrap; gap: 3px 14px; margin-top: 4px; }
        .kg-meta span { display: inline-flex; align-items: center; gap: 5px; }
        .kg-meta i.bi { line-height: 1; font-size: .85rem; }
        .kg-meta i.bi::before { display: block; line-height: 1; }
        .kg-lencana {
            font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 999px;
            display: inline-flex; align-items: center; gap: 5px; flex: 0 0 auto;
            background: var(--kg-lembut); color: var(--kg-warna);
        }
        .kg-lencana i.bi { font-size: .78rem; line-height: 1; }
        .kg-lencana i.bi::before { display: block; line-height: 1; }
        .kg-catatan { font-size: .82rem; color: #64748b; line-height: 1.6; margin-top: 7px; white-space: pre-line; }
        .kg-peserta { font-size: .75rem; color: #64748b; margin-top: 7px; display: flex; align-items: center; gap: 6px; }
        .kg-peserta i.bi { line-height: 1; flex: 0 0 auto; }
        .kg-peserta i.bi::before { display: block; line-height: 1; }

        .kg-kosong { text-align: center; padding: 34px 14px; color: #a8b3c4; font-size: .87rem; }
        .kg-kosong i.bi { display: block; font-size: 1.7rem; margin-bottom: 10px; color: #d7dee8; line-height: 1; }

        /* ===== Modal ===== */
        .kg-modal-latar {
            position: fixed; inset: 0; z-index: 1055; background: rgba(15,23,42,.45);
            backdrop-filter: blur(3px); overflow-y: auto; padding: 24px 14px;
        }
        .kg-modal { max-width: 640px; margin: 0 auto; }
        .kg-pilih-jenis { display: flex; flex-wrap: wrap; gap: 8px; }
        .kg-pilih-jenis .kg-chip { padding: 0 15px; font-size: .84rem; }
        .kg-peserta-kotak { max-height: 190px; overflow-y: auto; border: 1px solid #e9edf3; border-radius: 12px; padding: 10px 12px; }

        @media (max-width: 767.98px) {
            .kg-kisi { border-spacing: 4px; }
            .kg-sel { height: 76px; padding: 5px; border-radius: 12px; }
            /* Di layar sempit judulnya mustahil terbaca; disederhanakan jadi
               pita warna saja, yang tetap memberi tahu ADA kegiatan dan jenisnya. */
            .kg-acara { font-size: 0; padding: 0; height: 5px; margin-top: 3px; border-radius: 999px; border-left: 0; background: var(--kg-warna); gap: 0; }
            .kg-acara i.bi { display: none; }
            .kg-lebih { display: none; }
            .kg-bulan { min-width: 0; flex: 1 1 auto; font-size: .95rem; }
            .kg-alat .kg-btn { height: 40px; padding: 0 13px !important; }
            .kg-alat .kg-nav { width: 40px; }
            .kg-chip { height: 38px; font-size: .8rem; padding: 0 13px; }
        }
    </style>

    @php
        $jenisPeta = \App\Models\Kegiatan::JENIS;
    @endphp

    <div class="page-heading">
        {{-- ===== Kepala halaman ===== --}}
        <div class="page-title mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="gradient-text fw-bold mb-1">Kalender Kegiatan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Kalender Kegiatan']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 kg-alat">
                        <div class="kg-ringkas">
                            <span class="stat-icon-wrapper kg-ikon">
                                <i class="bi bi-calendar3"></i>
                            </span>
                            <div class="kg-ringkas-teks">
                                <b>{{ $jumlahBulanIni }} kegiatan</b>
                                <small>{{ $namaBulan }}</small>
                            </div>
                        </div>

                        @if ($this->bolehTambah)
                        <button type="button" class="btn btn-primary kg-btn" wire:click="buatBaru">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Batang alat ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 kg-alat">

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-light kg-nav kg-btn border" wire:click="bulanSebelumnya"
                            aria-label="Bulan sebelumnya">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="kg-bulan">{{ $namaBulan }}</span>
                        <button type="button" class="btn btn-light kg-nav kg-btn border" wire:click="bulanBerikutnya"
                            aria-label="Bulan berikutnya">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button type="button" class="btn btn-light kg-btn border ms-1" wire:click="keHariIni">
                            <i class="bi bi-calendar-check"></i> Hari ini
                        </button>
                    </div>

                    <div class="kg-saring">
                        <button type="button"
                            class="kg-chip {{ $saringJenis === '' ? 'aktif' : '' }}"
                            style="--kg-warna:#6d28d9; --kg-lembut:#f2ecfd;"
                            wire:click="$set('saringJenis', '')">
                            Semua
                        </button>
                        @foreach ($jenisPeta as $kunci => $j)
                        <button type="button"
                            class="kg-chip {{ $saringJenis === $kunci ? 'aktif' : '' }}"
                            style="--kg-warna:{{ $j['warna'] }}; --kg-lembut:{{ $j['lembut'] }};"
                            wire:click="$set('saringJenis', '{{ $kunci }}')">
                            <span class="kg-titik"></span>
                            {{ $j['label'] }}
                        </button>
                        @endforeach

                        <button type="button"
                            class="kg-chip {{ $hanyaSaya ? 'aktif' : '' }}"
                            style="--kg-warna:#0f766e; --kg-lembut:#e6f4f2;"
                            wire:click="$toggle('hanyaSaya')">
                            <i class="bi bi-person-check-fill"></i> Saya saja
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Kisi kalender ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-2 p-md-3">
                <table class="kg-kisi">
                    <thead>
                        <tr>
                            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $h)
                            <th>{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($minggu as $baris)
                        <tr>
                            @foreach ($baris as $sel)
                            <td class="kg-sel
                                       {{ $sel['bulanIni'] ? '' : 'kg-luar' }}
                                       {{ $sel['akhirPekan'] ? 'kg-pekan' : '' }}
                                       {{ $sel['hariIni'] ? 'kg-hariini' : '' }}
                                       {{ $tanggalTerpilih === $sel['tanggal'] ? 'kg-terpilih' : '' }}"
                                wire:key="sel-{{ $sel['tanggal'] }}"
                                wire:click="pilihTanggal('{{ $sel['tanggal'] }}')">

                                <span class="kg-angka">{{ $sel['angka'] }}</span>

                                @foreach ($sel['kegiatan']->take(3) as $k)
                                <span class="kg-acara {{ $k->sudahLewat() ? 'kg-lewat' : '' }}"
                                    style="--kg-warna:{{ $k->warna() }}; --kg-lembut:{{ $k->lembut() }};"
                                    title="{{ $k->rentangWaktu() }} — {{ $k->judul }}">
                                    <span>{{ $k->judul }}</span>
                                </span>
                                @endforeach

                                @if ($sel['kegiatan']->count() > 3)
                                <span class="kg-lebih">+{{ $sel['kegiatan']->count() - 3 }} lagi</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-4">
            {{-- ===== Hari terpilih ===== --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="kg-judul-kartu">
                                @if ($tanggalTerpilih)
                                {{ \Illuminate\Support\Carbon::parse($tanggalTerpilih)->locale('id')->translatedFormat('l, d F Y') }}
                                @else
                                Kegiatan Hari Terpilih
                                @endif
                            </h5>

                            @if ($tanggalTerpilih && $this->bolehTambah)
                            <button type="button" class="btn btn-sm btn-outline-primary kg-btn kg-btn-kecil"
                                wire:click="buatBaru('{{ $tanggalTerpilih }}')">
                                <i class="bi bi-plus-lg"></i> Tambah di tanggal ini
                            </button>
                            @endif
                        </div>

                        @forelse ($daftarKegiatanTerpilih as $k)
                        <div class="kg-baris" wire:key="pilih-{{ $k->id }}"
                            style="--kg-warna:{{ $k->warna() }}; --kg-lembut:{{ $k->lembut() }};">
                            <span class="kg-pita"></span>
                            <div class="kg-baris-isi">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <b>{{ $k->judul }}</b>
                                    <span class="kg-lencana">
                                        <i class="bi bi-{{ $k->ikon() }}"></i>{{ $k->label() }}
                                    </span>
                                </div>

                                <div class="kg-meta">
                                    <span><i class="bi bi-clock"></i> {{ $k->rentangWaktu() }}</span>
                                    @if ($k->lokasi)
                                    <span><i class="bi bi-geo-alt"></i> {{ $k->lokasi }}</span>
                                    @endif
                                </div>

                                @if ($k->deskripsi)
                                <div class="kg-catatan">{{ $k->deskripsi }}</div>
                                @endif

                                @if ($k->peserta->isNotEmpty())
                                <div class="kg-peserta">
                                    <i class="bi bi-people"></i>
                                    {{ $k->peserta->pluck('name')->implode(', ') }}
                                </div>
                                @endif

                                @if ($this->bolehUbah || $this->bolehHapus)
                                <div class="mt-3 d-flex gap-2">
                                    @if ($this->bolehUbah)
                                    <button type="button" class="btn btn-sm btn-light border kg-btn kg-btn-kecil"
                                        wire:click="sunting('{{ $k->id }}')">
                                        <i class="bi bi-pencil"></i> Ubah
                                    </button>
                                    @endif
                                    @if ($this->bolehHapus)
                                    <button type="button" class="btn btn-sm btn-light border kg-btn kg-btn-kecil text-danger hapus-kegiatan-btn"
                                        data-id="{{ $k->id }}" data-judul="{{ $k->judul }}">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="kg-kosong">
                            @if ($tanggalTerpilih)
                            <i class="bi bi-calendar2-x"></i>
                            Tidak ada kegiatan pada tanggal ini.
                            @else
                            <i class="bi bi-hand-index-thumb"></i>
                            Klik salah satu tanggal untuk melihat kegiatannya.
                            @endif
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===== Agenda terdekat ===== --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h5 class="kg-judul-kartu mb-3">Agenda Terdekat</h5>

                        @forelse ($berikutnya as $k)
                        <div class="kg-baris" wire:key="next-{{ $k->id }}"
                            style="--kg-warna:{{ $k->warna() }}; --kg-lembut:{{ $k->lembut() }};">
                            <span class="kg-pita"></span>
                            <div class="kg-baris-isi">
                                <b>{{ $k->judul }}</b>
                                <div class="kg-meta">
                                    <span><i class="bi bi-calendar-event"></i>
                                        {{ $k->mulai->locale('id')->translatedFormat('d M') }}</span>
                                    <span><i class="bi bi-clock"></i> {{ $k->rentangWaktu() }}</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="kg-kosong">
                            <i class="bi bi-calendar2-check"></i>
                            Belum ada agenda mendatang.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modal form ===== --}}
    @if ($formTampil)
    <div class="kg-modal-latar" wire:key="form-kegiatan">
        <div class="kg-modal card border-0 shadow-lg rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="gradient-text fw-bold mb-0">
                        {{ $formId ? 'Ubah Kegiatan' : 'Tambah Kegiatan' }}
                    </h5>
                    <button type="button" class="btn btn-light kg-nav kg-btn border kg-alat" wire:click="tutupForm"
                        aria-label="Tutup" style="width:42px; height:42px; padding:0;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form wire:submit="simpan">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('judul') is-invalid @enderror"
                            wire:model="judul" placeholder="Mis. Rapat mingguan tim produk" autofocus>
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis</label>
                        <div class="kg-pilih-jenis">
                            @foreach ($jenisPeta as $kunci => $j)
                            <button type="button"
                                class="kg-chip {{ $jenis === $kunci ? 'aktif' : '' }}"
                                style="--kg-warna:{{ $j['warna'] }}; --kg-lembut:{{ $j['lembut'] }};"
                                wire:click="$set('jenis', '{{ $kunci }}')">
                                <i class="bi bi-{{ $j['ikon'] }}"></i>
                                {{ $j['label'] }}
                            </button>
                            @endforeach
                        </div>
                        @error('jenis') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="kg-seharian" wire:model.live="seharian">
                        <label class="form-check-label" for="kg-seharian">Berlangsung seharian</label>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Tanggal mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggalMulai') is-invalid @enderror"
                                wire:model="tanggalMulai">
                            @error('tanggalMulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if (! $seharian)
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Jam mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('jamMulai') is-invalid @enderror"
                                wire:model="jamMulai">
                            @error('jamMulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Tanggal selesai</label>
                            <input type="date" class="form-control @error('tanggalSelesai') is-invalid @enderror"
                                wire:model="tanggalSelesai">
                            <small class="text-muted">Kosongkan bila selesai di hari yang sama.</small>
                            @error('tanggalSelesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if (! $seharian)
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Jam selesai</label>
                            <input type="time" class="form-control @error('jamSelesai') is-invalid @enderror"
                                wire:model="jamSelesai">
                            <small class="text-muted">Boleh kosong bila belum pasti.</small>
                            @error('jamSelesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lokasi</label>
                        <input type="text" class="form-control @error('lokasi') is-invalid @enderror"
                            wire:model="lokasi" placeholder="Mis. Ruang rapat lantai 2 / Google Meet">
                        @error('lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" rows="3"
                            wire:model="deskripsi" placeholder="Agenda, hal yang perlu disiapkan, dsb."></textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Peserta</label>
                        <div class="kg-peserta-kotak">
                            @foreach ($semuaKaryawan as $u)
                            <div class="form-check" wire:key="peserta-{{ $u->id }}">
                                <input class="form-check-input" type="checkbox" id="kg-u-{{ $u->id }}"
                                    value="{{ $u->id }}" wire:model="peserta">
                                <label class="form-check-label" for="kg-u-{{ $u->id }}">{{ $u->name }}</label>
                            </div>
                            @endforeach
                        </div>
                        @error('peserta') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 kg-alat">
                        <button type="button" class="btn btn-light border kg-btn" wire:click="tutupForm">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary kg-btn" wire:loading.attr="disabled">
                            <i class="bi bi-check2"></i>
                            <span wire:loading.remove wire:target="simpan">Simpan</span>
                            <span wire:loading wire:target="simpan">Menyimpan…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!--================== SWEET ALERT HAPUS ==================-->
    <script data-navigate-once>
        document.addEventListener('click', function (event) {
            const tombol = event.target.closest('.hapus-kegiatan-btn');
            if (!tombol) return;

            event.preventDefault();

            Swal.fire({
                title: 'Hapus kegiatan?',
                text: '"' + (tombol.getAttribute('data-judul') || '') + '" akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                background: 'rgba(255, 255, 255, 0.95)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                buttonsStyling: false,
                customClass: {
                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0',
                    confirmButton: 'btn-glossy-confirm',
                    cancelButton: 'btn-glossy-cancel',
                    title: 'fw-bold'
                }
            }).then((hasil) => {
                if (!hasil.isConfirmed) return;

                const komponen = tombol.closest('[wire\\:id]');
                if (komponen) {
                    Livewire.find(komponen.getAttribute('wire:id')).call('hapus', tombol.getAttribute('data-id'));
                }
            });
        });
    </script>
    <!--================== END SWEET ALERT HAPUS ==================-->
</div>
