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
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari nama atau merek kendaraan...">
                        </div>
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
                                <div class="orcha-unit-kosong">
                                    <i class="bi bi-truck"></i>
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

                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-bold" style="font-size:1.02rem">{{ $baris['nama'] }}</div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        {{ $baris['merek'] }} · {{ $baris['jenis_label'] }} ·
                                        {{ $baris['kapasitas'] }} kursi · {{ $baris['transmisi_label'] }}
                                    </div>
                                </div>
                                @if ($baris['nopol'])
                                    <span class="orcha-nopol">{{ $baris['nopol'] }}</span>
                                @endif
                            </div>

                            {{-- Tarif harian ditebalkan, sisanya keterangan: hampir semua
                                 pemesanan memakai satuan hari. --}}
                            <div class="orcha-unit-tarif mt-3">
                                <div>
                                    <div class="orcha-label-kecil">Per hari</div>
                                    <div class="angka">{{ $rupiah($baris['tarif']['hari']) }}</div>
                                </div>
                                <div class="orcha-unit-tarif-lain">
                                    @foreach ([
                                        ['Per jam', $baris['tarif']['jam']],
                                        ['12 jam', $baris['tarif']['12jam']],
                                        ['Sopir', $baris['tarif']['sopir_per_hari']],
                                    ] as [$label, $nilai])
                                        <div>
                                            <span class="orcha-label-kecil">{{ $label }}</span>
                                            <span class="{{ $nilai ? '' : 'text-muted' }}">{{ $rupiah($nilai) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Kondisi dari pemeriksaan serah terima terakhir. Dicatat
                                 tiap unit kembali, dan sebelum ini tidak pernah terbaca
                                 lagi di mana pun. --}}
                            @if ($kondisi)
                                <div class="orcha-unit-kondisi {{ $kondisi['perlu_perhatian'] ? 'awas' : '' }} mt-3">
                                    <div class="orcha-label-kecil orcha-ikon-teks">
                                        <i class="bi {{ $kondisi['perlu_perhatian'] ? 'bi-exclamation-triangle-fill' : 'bi-clipboard-check' }}"></i>
                                        Kondisi terakhir
                                        @if ($kondisi['diperiksa_pada'])
                                            <span class="text-muted fw-normal">
                                                · {{ \Carbon\Carbon::parse($kondisi['diperiksa_pada'])->translatedFormat('j M Y') }}
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
                                <div class="text-muted mt-3 orcha-ikon-teks" style="font-size:.78rem">
                                    <i class="bi bi-clipboard-x"></i> Belum pernah diperiksa serah terima
                                </div>
                            @endif

                            {{-- Jadwal pakai: menjawab "bisa dipakai besok?" tanpa
                                 membuka halaman penyewaan. --}}
                            @if (($jadwal['sedang_disewa'] ?? false) && ($jadwal['kembali_pada'] ?? null))
                                <div class="orcha-unit-jadwal mt-2">
                                    <i class="bi bi-clock-history"></i>
                                    Kembali
                                    {{ \Carbon\Carbon::parse($jadwal['kembali_pada'])->translatedFormat('j M, H:i') }}
                                    @if ($jadwal['kode_berjalan'])
                                        · <span class="orcha-kode">{{ $jadwal['kode_berjalan'] }}</span>
                                    @endif
                                </div>
                            @elseif ($jadwal['mulai_berikutnya'] ?? null)
                                <div class="orcha-unit-jadwal mt-2">
                                    <i class="bi bi-calendar-event"></i>
                                    Terpesan mulai
                                    {{ \Carbon\Carbon::parse($jadwal['mulai_berikutnya'])->translatedFormat('j M, H:i') }}
                                    @if ($jadwal['kode_berikutnya'])
                                        · <span class="orcha-kode">{{ $jadwal['kode_berikutnya'] }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
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

        .orcha-unit-foto {
            position: relative;
            aspect-ratio: 16 / 10;
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
            gap: .35rem;
            color: #94a3b8;
            font-size: .8rem;
        }

        .orcha-unit-kosong > i { font-size: 1.8rem; line-height: 1; }

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
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: .75rem;
            padding: .7rem .85rem;
            border-radius: .8rem;
            background: linear-gradient(135deg, #f8fbfd, #eef6fb);
            border: 1px solid #e3ecf3;
        }

        .orcha-unit-tarif .angka {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f2d4a;
            line-height: 1.2;
        }

        .orcha-unit-tarif-lain {
            display: grid;
            gap: .1rem;
            font-size: .76rem;
            text-align: right;
        }

        .orcha-unit-tarif-lain .orcha-label-kecil {
            display: inline;
            margin-right: .35rem;
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
    </style>
</div>
