@section('title')
Armada Orcha || lemon
@stop

@php
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanGambar = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;
    $rupiah = fn ($angka) => $angka ? 'Rp ' . number_format((float) $angka, 0, ',', '.') : '—';
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Armada Orcha',
            'keterangan' => 'Unit beserta tarif, kondisi terakhir, dan jadwal pakainya.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2">
                    <div class="col-12 col-lg-8">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari nama atau merek kendaraan...'])
                    </div>

                    <div class="col-8 col-lg-2">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua jenis</option>
                            @foreach ($pilihan as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-4 col-lg-2 text-end">
                        {{-- Kelasnya mengikuti tombol tambah di Paket Wisata dan Etalase.
                             Sebelumnya memakai keluarga .orcha-btn yang memasang ukuran
                             sendiri (font .86rem), sehingga tombolnya 35px sementara
                             tombol tambah di fitur lain 44px — terukur di peramban. --}}
                        <a href="{{ route('admin.orcha.armada.tambah') }}" wire:navigate
                            class="btn btn-primary rounded-3 orcha-tombol orcha-tombol-tambah w-100 justify-content-center">
                            <i class="bi bi-plus-lg"></i>
                            <span class="d-none d-sm-inline">Tambah Unit</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu, bukan tabel tarif.

             Armada dikenali dari rupa unitnya — fotonya, nopolnya — bukan dari
             deretan angka. Tabel lama memuat empat kolom tarif berdampingan
             sehingga foto tidak muat, padahal itu yang pertama dicari mata saat
             mencocokkan "yang mana Avanza putih tadi". --}}
        <div class="row g-3">
            @forelse ($daftar as $baris)
                @php
                    $kondisi = $baris['kondisi'] ?? null;
                    $jadwal = $baris['jadwal'] ?? [];
                @endphp

                <div class="col-12 col-md-6 col-xl-4" wire:key="kendaraan-{{ $baris['id'] }}">
                    <div class="card border-0 shadow-sm rounded-4 h-100 orcha-unit
                        {{ ($kondisi['perlu_perhatian'] ?? false) ? 'perlu-perhatian' : '' }}">

                        <div class="orcha-unit-foto">
                            @if ($tautanGambar($baris['gambar']))
                                <img src="{{ $tautanGambar($baris['gambar']) }}" alt="{{ $baris['nama'] }}">
                            @else
                                {{-- Ikonnya dibungkus kotak berukuran tetap yang memusatkan
                                     sendiri isinya. Sebagai <i> lepas, tingginya ditentukan
                                     kotak barisnya: font-size 1,8rem terpasang tetapi
                                     kotaknya terukur 16px di peramban, jadi ikonnya
                                     menempel ke tulisan di bawahnya. --}}
                                <div class="orcha-unit-kosong">
                                    <span class="orcha-unit-kosong-rupa"><i class="bi bi-image"></i></span>
                                    <span>Belum ada foto</span>
                                </div>
                            @endif

                            {{-- Ketersediaan ditempel di fotonya: itu keputusan pertama
                                 yang diambil admin saat calon penyewa menelepon. --}}
                            @if ($jadwal['sedang_disewa'] ?? false)
                                <span class="orcha-unit-status keluar">
                                    <i class="bi bi-arrow-up-right-circle"></i> Sedang disewa
                                </span>
                            @elseif (! $baris['tersedia'])
                                <span class="orcha-unit-status nonaktif">
                                    <i class="bi bi-pause-circle"></i> Tidak aktif
                                </span>
                            @else
                                <span class="orcha-unit-status siap">
                                    <i class="bi bi-check-circle"></i> Siap disewakan
                                </span>
                            @endif
                        </div>

                        {{-- Kolom lentur supaya kaki kartu bisa dipaku ke dasar.
                             Sebelumnya isi kartu berhenti di mana saja: unit tanpa
                             catatan kondisi menyisakan 169px kosong di bawah tombolnya
                             sementara unit yang ada catatannya hanya 60px — terukur di
                             peramban — sehingga tombol ubah/hapus tiap kartu berbeda
                             tingginya dan deretannya terlihat berantakan. --}}
                        <div class="card-body p-3 p-lg-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-bold" style="font-size:1.02rem">{{ $baris['nama'] }}</div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        {{ $baris['merek'] }}{{ ($baris['varian'] ?? '') ? ' '.$baris['varian'] : '' }}
                                        @if ($baris['tahun'] ?? null) · {{ $baris['tahun'] }} @endif
                                        · {{ $baris['jenis_label'] }} ·
                                        {{-- kapasitas berisi kursi PENUMPANG. Kursi totalnya disebut
                                             dalam tanda kurung HANYA bila berbeda, yaitu saat unit
                                             selalu dengan sopir — menuliskan "7 penumpang (7 kursi)"
                                             di semua baris menambah kata tanpa menambah keterangan. --}}
                                        @if (($baris['kursi_total'] ?? null) && $baris['kursi_total'] !== $baris['kapasitas'])
                                            {{ $baris['kapasitas'] }} penumpang ({{ $baris['kursi_total'] }} kursi)
                                        @else
                                            {{ $baris['kapasitas'] }} kursi
                                        @endif
                                        · {{ $baris['transmisi_label'] }}
                                        @if (($baris['cc'] ?? null)) · {{ number_format($baris['cc'], 0, ',', '.') }} cc @endif
                                    </div>

                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @unless ($baris['lepas_kunci'] ?? true)
                                            <span class="orcha-lencana-sopir">
                                                <i class="bi bi-person-badge"></i>
                                                <span>Selalu dengan sopir</span>
                                            </span>
                                        @endunless

                                        {{-- Hanya unit all-in yang dilencanai: yang ditanggung
                                             penyewa adalah keadaan biasa, dan melencanai keadaan
                                             biasa membuat lencananya berhenti berarti. --}}
                                        @php
                                            $posTermasuk = collect($baris['operasional'] ?? [])
                                                ->filter(fn ($pos) => $pos['termasuk'] ?? false);
                                        @endphp

                                        {{-- Aturan luar kota disebut HANYA bila berbeda.
                                             Unit yang aturannya sama di kedua wilayah tidak
                                             perlu dua lencana yang isinya sama; yang berbeda
                                             wajib terlihat di daftar, karena lencana di
                                             sebelahnya hanya berlaku untuk dalam kota. --}}
                                        @php
                                            $luar = $baris['luar_kota'] ?? null;
                                            $luarTermasuk = collect($luar['operasional'] ?? [])
                                                ->filter(fn ($pos) => $pos['termasuk'] ?? false)
                                                ->pluck('label');

                                            if ($luar['termasuk_sopir'] ?? false) {
                                                $luarTermasuk->push('sopir');
                                            }
                                        @endphp

                                        @if ($posTermasuk->isNotEmpty())
                                            <span class="orcha-lencana-allin">
                                                <i class="bi bi-fuel-pump"></i>
                                                <span>
                                                    {{-- Pos yang termasuk disebut namanya, bukan
                                                         "All-in" untuk semua keadaan: unit yang hanya
                                                         BBM-nya termasuk bukan all-in, dan menyebutnya
                                                         begitu menjanjikan lebih dari yang benar. --}}
                                                    {{ $posTermasuk->count() === count($baris['operasional'] ?? [])
                                                        ? 'All-in'
                                                        : $posTermasuk->pluck('label')->join(' + ').' termasuk' }}
                                                    @if (($baris['biaya_operasional_total'] ?? 0) > 0)
                                                        +{{ number_format($baris['biaya_operasional_total'], 0, ',', '.') }}/hari
                                                    @endif
                                                </span>
                                            </span>
                                        @endif

                                        @if ($baris['beda_aturan_luar_kota'] ?? false)
                                            <span class="orcha-lencana-luar">
                                                <i class="bi bi-signpost-split"></i>
                                                <span>
                                                    Luar kota:
                                                    {{ $luarTermasuk->isEmpty()
                                                        ? 'semua ditanggung penyewa'
                                                        : $luarTermasuk->join(' + ').' termasuk' }}
                                                </span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($baris['nopol'])
                                    <span class="orcha-nopol">{{ $baris['nopol'] }}</span>
                                @endif
                            </div>

                            {{-- Tarif harian ditebalkan, sisanya keterangan: hampir semua
                                 pemesanan memakai satuan hari.

                                 Keterangan rata kiri, nominal rata KANAN. Sebelumnya
                                 label dan angka berdempetan dalam satu kolom kanan yang
                                 sempit, sehingga admin harus membaca tiap barisnya untuk
                                 membandingkan; sekarang angkanya jatuh di satu garis
                                 tegak dan bisa disusuri sekali lihat. --}}
                            <div class="orcha-unit-tarif mt-3">
                                <div class="orcha-tarif-utama">
                                    <span class="orcha-label-kecil">Per hari</span>
                                    <span class="angka">{{ $rupiah($baris['tarif']['hari']) }}</span>
                                </div>

                                @php
                                    // Sopir disebut keadaannya, bukan hanya angkanya: unit yang
                                    // tarifnya sudah termasuk sopir dulu menampilkan "—" — tanda
                                    // yang di baris lain berarti "belum diisi".
                                    $barisTarif = [
                                        ['Per jam', $rupiah($baris['tarif']['jam'] ?? null), (bool) ($baris['tarif']['jam'] ?? null)],
                                        ['12 jam', $rupiah($baris['tarif']['12jam'] ?? null), (bool) ($baris['tarif']['12jam'] ?? null)],
                                    ];

                                    if ($baris['tarif']['luar_kota'] ?? null) {
                                        $barisTarif[] = ['Luar kota / hari', $rupiah($baris['tarif']['luar_kota']), true];
                                    }

                                    $barisTarif[] = ($baris['termasuk_sopir'] ?? false)
                                        ? ['Sopir / hari', 'Sudah termasuk', true]
                                        : ['Sopir / hari', $rupiah($baris['tarif']['sopir_per_hari'] ?? null), (bool) ($baris['tarif']['sopir_per_hari'] ?? null)];
                                @endphp

                                <div class="orcha-unit-tarif-lain">
                                    @foreach ($barisTarif as [$label, $nilai, $terisi])
                                        <div>
                                            <span class="orcha-label-kecil">{{ $label }}</span>
                                            <span class="{{ $terisi ? 'nilai' : 'nilai text-muted' }}">{{ $nilai }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Kondisi dari pemeriksaan serah terima terakhir. Dicatat
                                 tiap unit kembali, dan sebelum ini tidak pernah terbaca
                                 lagi di mana pun. --}}
                            {{-- Blok tengah menyerap sisa ruang kartu lewat my-auto, jadi
                                 selisih tinggi antar-unit terbagi ke atas dan ke bawah
                                 sebagai dua jarak napas — bukan menumpuk jadi satu lubang
                                 di atas tombol seperti sebelumnya. --}}
                            <div class="my-auto py-3">
                            @if ($kondisi)
                                <div class="orcha-unit-kondisi {{ $kondisi['perlu_perhatian'] ? 'awas' : '' }}">
                                    <div class="orcha-label-kecil orcha-ikon-teks">
                                        <i class="bi {{ $kondisi['perlu_perhatian'] ? 'bi-exclamation-triangle-fill' : 'bi-clipboard-check' }}"></i>
                                        Kondisi terakhir
                                        @if ($kondisi['diperiksa_pada'])
                                            <span class="text-muted fw-normal">
                                                · {{ \Carbon\Carbon::parse($kondisi['diperiksa_pada'])->locale('id')->translatedFormat('j M Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($kondisi['rincian'])
                                        <div class="mt-1" style="font-size:.8rem">
                                            @foreach (array_slice($kondisi['rincian'], 0, 3) as $satu)
                                                <span class="orcha-cacat {{ $satu['nilai'] }}">
                                                    {{ $satu['bagian'] }}: {{ strtolower($satu['kondisi']) }}
                                                </span>
                                            @endforeach
                                            @if (count($kondisi['rincian']) > 3)
                                                <span class="text-muted">
                                                    +{{ count($kondisi['rincian']) - 3 }} lagi
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="mt-1" style="font-size:.8rem">Semua bagian baik.</div>
                                    @endif
                                </div>
                            @else
                                {{-- Diberi bentuk kotak seperti blok kondisi, bukan satu
                                     baris tipis. Sebagai baris telanjang ia melayang di
                                     tengah kartu — unit yang belum pernah diperiksa jadi
                                     terlihat seperti kartu yang gagal dimuat, padahal
                                     "belum diperiksa" itu sendiri keterangan yang perlu
                                     dilihat admin. --}}
                                <div class="orcha-unit-kondisi kosong">
                                    <div class="orcha-label-kecil orcha-ikon-teks">
                                        <i class="bi bi-clipboard-x"></i>
                                        Belum ada pemeriksaan
                                    </div>
                                    <div class="mt-1" style="font-size:.78rem">
                                        Belum pernah diperiksa serah terima.
                                    </div>
                                </div>
                            @endif

                            {{-- Jadwal pakai: menjawab "bisa dipakai besok?" tanpa
                                 membuka halaman penyewaan. --}}
                            @if (($jadwal['sedang_disewa'] ?? false) && ($jadwal['kembali_pada'] ?? null))
                                <div class="orcha-unit-jadwal mt-2">
                                    <i class="bi bi-clock-history"></i>
                                    Kembali
                                    {{ \Carbon\Carbon::parse($jadwal['kembali_pada'])->locale('id')->translatedFormat('j M, H:i') }}
                                    @if ($jadwal['kode_berjalan'])
                                        · <span class="orcha-kode">{{ $jadwal['kode_berjalan'] }}</span>
                                    @endif
                                </div>
                            @elseif ($jadwal['mulai_berikutnya'] ?? null)
                                <div class="orcha-unit-jadwal mt-2">
                                    <i class="bi bi-calendar-event"></i>
                                    Terpesan mulai
                                    {{ \Carbon\Carbon::parse($jadwal['mulai_berikutnya'])->locale('id')->translatedFormat('j M, H:i') }}
                                    @if ($jadwal['kode_berikutnya'])
                                        · <span class="orcha-kode">{{ $jadwal['kode_berikutnya'] }}</span>
                                    @endif
                                </div>
                            @endif
                            </div>

                            <div class="orcha-unit-kaki d-flex justify-content-between align-items-center gap-2">
                                <span class="text-muted" style="font-size:.76rem">
                                    {{ $baris['jumlah_penyewaan'] ?? 0 }}× disewa
                                </span>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.orcha.armada.ubah', $baris['id']) }}" wire:navigate
                                        class="btn btn-sm orcha-aksi orcha-aksi-ubah" title="Ubah unit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                        data-action="hapus" data-arg="{{ $baris['id'] }}"
                                        data-title="Hapus unit ini?"
                                        data-text="{{ addslashes($baris['nama']) }} akan hilang dari daftar sewa."
                                        data-confirm="Ya, hapus" data-icon="warning" title="Hapus unit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body text-center py-5">
                            <div class="empty-state-icon-wrapper mx-auto mb-2">
                                <i class="bi bi-truck"></i>
                            </div>
                            <p class="text-muted mb-0">Belum ada kendaraan yang cocok.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            @include('livewire.pages.admin.orcha.partials.paginasi')
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')

    <style>
        .orcha-unit {
            overflow: hidden;
            transition: box-shadow .15s ease, transform .15s ease;
        }

        .orcha-unit:hover {
            transform: translateY(-2px);
            box-shadow: 0 .6rem 1.4rem rgba(15, 45, 74, .12) !important;
        }

        /* Unit yang ada bagian rusak atau hilang diberi garis merah di kiri —
           terlihat sebelum kartunya dibaca. */
        .orcha-unit.perlu-perhatian {
            border-left: 4px solid #c2323c !important;
        }

        /* Lebih rendah dari sebelumnya (16/10, terukur 295px). Di daftar admin
           fotonya penanda pengenal, bukan pajangan: hampir semua unit belum
           berfoto, sehingga tiga per baris berarti tiga kotak abu-abu besar yang
           mendorong keterangan pentingnya turun. Dibatasi tingginya supaya di
           layar lebar pun tidak melar. */
        .orcha-unit-foto {
            position: relative;
            aspect-ratio: 16 / 9;
            max-height: 200px;
            background: #eef2f6;
        }

        .orcha-unit-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .orcha-unit-kosong {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            color: #94a3b8;
            font-size: .76rem;
        }

        /* Kotak berukuran tetap yang memusatkan sendiri ikonnya. Ikon telanjang
           tingginya ditentukan kotak baris — terukur 16px walau font-size-nya
           1,8rem — sehingga ia menempel ke tulisan di bawahnya. */
        .orcha-unit-kosong-rupa {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: .8rem;
            background: #e2e8f0;
            color: #94a3b8;
        }

        .orcha-unit-kosong-rupa > i { font-size: 1.15rem; line-height: 1; }

        .orcha-unit-status {
            position: absolute;
            left: .7rem;
            bottom: .7rem;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .65rem;
            border-radius: 2rem;
            font-size: .74rem;
            font-weight: 700;
            color: #fff;
            backdrop-filter: blur(2px);
        }

        .orcha-unit-status > i { line-height: 1; }

        .orcha-unit-status.siap { background: rgba(26, 138, 82, .92); }

        .orcha-unit-status.keluar { background: rgba(29, 111, 165, .92); }

        .orcha-unit-status.nonaktif { background: rgba(100, 116, 139, .92); }

        .orcha-nopol {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .04em;
            padding: .2rem .5rem;
            border: 1px solid #cbd5e1;
            border-radius: .4rem;
            color: #0f2d4a;
            background: #f8fafc;
            white-space: nowrap;
        }

        .orcha-unit-tarif {
            padding: .7rem .85rem;
            border-radius: .8rem;
            background: linear-gradient(135deg, #f8fbfd, #eef6fb);
            border: 1px solid #e3ecf3;
            /* Angka berjajar rapi hanya bila lebar tiap digitnya sama; tanpa ini
               "Rp 1.000.000" dan "Rp 250.000" tidak pernah benar-benar lurus. */
            font-variant-numeric: tabular-nums;
        }

        /* Keterangan di kiri, nominal di kanan — di baris utama maupun rinciannya,
           sehingga semua angka jatuh di satu garis tegak. */
        .orcha-tarif-utama {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .5rem;
        }

        .orcha-unit-tarif .angka {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f2d4a;
            line-height: 1.2;
        }

        .orcha-unit-tarif-lain {
            display: grid;
            gap: .15rem;
            margin-top: .45rem;
            padding-top: .45rem;
            border-top: 1px solid #e3ecf3;
            font-size: .76rem;
        }

        .orcha-unit-tarif-lain > div {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .5rem;
        }

        /* Label rincian dikembalikan ke huruf biasa. Sebagai kapital berspasi
           semua — PER JAM, 12 JAM, SOPIR / HARI — empat baris berurutan terbaca
           seperti teriakan dan justru lebih lambat dibaca; kapital disisakan
           untuk satu label utama saja supaya penekanannya masih berarti. */
        .orcha-unit-tarif-lain .orcha-label-kecil {
            text-transform: none;
            letter-spacing: normal;
            font-size: .76rem;
            font-weight: 500;
        }

        .orcha-unit-tarif-lain .nilai {
            font-weight: 600;
            color: #1d4d75;
            white-space: nowrap;
        }

        .orcha-unit-tarif-lain .nilai.text-muted { font-weight: 500; }

        /* Kaki kartu: dipisahkan garis supaya terbaca sebagai tempat tombol,
           bukan sebagai baris keterangan berikutnya. */
        .orcha-unit-kaki {
            padding-top: .7rem;
            border-top: 1px solid #eef2f6;
        }

        .orcha-unit-kondisi {
            padding: .6rem .75rem;
            border-radius: .7rem;
            background: #f7f9fb;
            border: 1px solid #e3ecf3;
        }

        .orcha-unit-kondisi.awas {
            background: #fdecee;
            border-color: #f6c9cd;
        }

        /* Garis putus-putus: bentuknya sama dengan blok kondisi supaya kartunya
           tidak berlubang, tapi tetap terbaca sebagai "belum ada isinya" — bukan
           sebagai catatan yang sudah pernah dibuat. */
        .orcha-unit-kondisi.kosong {
            background: transparent;
            border-style: dashed;
            border-color: #dbe4ec;
            color: #94a3b8;
        }

        /* Cacat ringan dan berat dibedakan warnanya: lecet lama wajar pada
           armada sewa, rusak dan hilang tidak. */
        .orcha-cacat {
            display: inline-block;
            padding: .1rem .45rem;
            margin: .1rem .2rem .1rem 0;
            border-radius: .35rem;
            background: #eef2f6;
            color: #475569;
        }

        .orcha-cacat.rusak,
        .orcha-cacat.hilang {
            background: #fde2e4;
            color: #9b2530;
            font-weight: 600;
        }

        .orcha-unit-jadwal {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .78rem;
            color: #1d6fa5;
        }

        .orcha-unit-jadwal > i { line-height: 1; }
    
        /* Lencana unit yang tidak boleh lepas kunci. Kuning kehijauan, bukan
           merah: ini keterangan cara pakai, bukan kesalahan. */
        .orcha-lencana-sopir {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .12rem .45rem;
            border-radius: 7px;
            background: #f6edd0;
            color: #8a6d1f;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1.3;
        }

        /* Lencana all-in: hijau, karena ini keuntungan bagi penyewa — bukan
           peringatan seperti lencana sopir yang kuning. */
        .orcha-lencana-allin {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .12rem .45rem;
            border-radius: 7px;
            background: #d7f0e2;
            color: #1a6b43;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1.3;
        }

        /* Lencana aturan luar kota: kuning kehijauan seperti lencana sopir,
           karena keduanya sama-sama keterangan cara pakai — bukan keuntungan
           harga seperti lencana all-in yang hijau. */
        .orcha-lencana-luar {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .12rem .45rem;
            border-radius: 7px;
            background: #e6eefb;
            color: #1d4d75;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .orcha-lencana-luar i,
        .orcha-lencana-allin i,
        .orcha-lencana-sopir i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            vertical-align: middle;
        }
</style>
</div>
