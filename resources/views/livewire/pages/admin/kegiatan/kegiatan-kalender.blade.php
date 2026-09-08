@section('title')
Kalender Kegiatan || lemon
@stop

<div>
    <style>
        /* Ditulis inline: public/build tidak ikut terdeploy ke server. */

        /* Glyph Bootstrap Icons punya line-height bawaan yang menariknya turun,
           sehingga melenceng di dalam kotak yang sudah di-flex-center. Keduanya
           (<i> dan ::before) harus disetel — pola yang sama dipakai di Jeda Layanan. */
        .kg-ikon i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; position: relative; z-index: 1; }
        .kg-ikon i.bi::before { display: block; line-height: 1; }
        .kg-btn i.bi { line-height: 1; vertical-align: -.08em; }
        .kg-btn i.bi::before { line-height: 1; }

        .kg-ikon {
            position: relative; overflow: hidden; border-radius: 18px;
            background: linear-gradient(135deg, #818cf8, #4f46e5);
            box-shadow: 0 8px 18px rgba(79,70,229,.26), 0 0 0 5px rgba(99,102,241,.10);
        }
        .kg-ikon::after {
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.24), transparent 62%);
        }
        .kg-ringkas { display: flex; align-items: center; gap: 14px; }
        .kg-ringkas-teks { line-height: 1.35; }
        .kg-ringkas-teks b { display: block; font-size: .98rem; }
        .kg-ringkas-teks small { color: #94a3b8; }

        /* ===== Batang alat ===== */
        .kg-bulan { min-width: 190px; text-align: center; font-weight: 700; font-size: 1.05rem; color: #1e293b; }
        .kg-nav { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; }
        .kg-saring { display: flex; flex-wrap: wrap; gap: 8px; }
        .kg-chip {
            border: 1px solid #e2e8f0; background: #fff; border-radius: 999px;
            padding: 5px 13px; font-size: .82rem; font-weight: 600; color: #64748b;
            display: inline-flex; align-items: center; gap: 7px; cursor: pointer;
            transition: all .15s ease;
        }
        .kg-chip:hover { border-color: #cbd5e1; color: #334155; }
        .kg-chip.aktif { color: #fff; border-color: transparent; box-shadow: 0 4px 10px rgba(15,23,42,.16); }
        .kg-titik { width: 9px; height: 9px; border-radius: 50%; flex: 0 0 auto; }

        /* ===== Kisi kalender ===== */
        .kg-kisi { width: 100%; border-collapse: separate; border-spacing: 6px; table-layout: fixed; }
        .kg-kisi th {
            font-size: .74rem; text-transform: uppercase; letter-spacing: .06em;
            color: #94a3b8; font-weight: 700; padding-bottom: 2px; text-align: center;
        }
        .kg-sel {
            vertical-align: top; height: 112px; padding: 8px;
            border: 1px solid #eef2f7; border-radius: 14px; background: #fff;
            cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .kg-sel:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px rgba(79,70,229,.10); }
        .kg-sel.kg-luar { background: #fafbfc; }
        .kg-sel.kg-luar .kg-angka { color: #cbd5e1; }
        .kg-sel.kg-pekan { background: #fdfdfe; }
        .kg-sel.kg-terpilih { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.16); }
        .kg-angka {
            font-size: .84rem; font-weight: 700; color: #475569;
            width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 50%;
        }
        .kg-sel.kg-hariini .kg-angka { background: linear-gradient(135deg, #818cf8, #4f46e5); color: #fff; }

        .kg-acara {
            display: block; width: 100%; text-align: left; border: 0; background: transparent;
            font-size: .74rem; line-height: 1.3; padding: 3px 6px; margin-top: 4px;
            border-radius: 7px; color: #fff; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .kg-acara.kg-lewat { opacity: .55; }
        .kg-lebih { font-size: .7rem; color: #94a3b8; font-weight: 600; margin-top: 3px; display: block; }

        /* ===== Daftar hari terpilih & agenda ===== */
        .kg-baris {
            display: flex; gap: 12px; padding: 12px 14px; border-radius: 14px;
            border: 1px solid #eef2f7; background: #fff; margin-bottom: 10px;
        }
        .kg-pita { width: 4px; border-radius: 999px; flex: 0 0 auto; }
        .kg-baris-isi { flex: 1 1 auto; min-width: 0; }
        .kg-baris-isi b { display: block; font-size: .93rem; color: #1e293b; line-height: 1.35; }
        .kg-meta { font-size: .78rem; color: #94a3b8; display: flex; flex-wrap: wrap; gap: 4px 12px; margin-top: 3px; }
        .kg-meta span { display: inline-flex; align-items: center; gap: 5px; }
        .kg-meta i.bi { line-height: 1; }
        .kg-meta i.bi::before { line-height: 1; }
        .kg-lencana {
            font-size: .68rem; font-weight: 700; padding: 2px 9px; border-radius: 999px;
            color: #fff; letter-spacing: .02em;
        }
        .kg-catatan { font-size: .82rem; color: #64748b; line-height: 1.6; margin-top: 6px; white-space: pre-line; }
        .kg-peserta { font-size: .74rem; color: #64748b; margin-top: 6px; }

        .kg-kosong { text-align: center; padding: 30px 14px; color: #94a3b8; font-size: .87rem; }

        /* ===== Modal ===== */
        .kg-modal-latar {
            position: fixed; inset: 0; z-index: 1055; background: rgba(15,23,42,.45);
            backdrop-filter: blur(3px); overflow-y: auto; padding: 24px 14px;
        }
        .kg-modal { max-width: 640px; margin: 0 auto; }
        .kg-pilih-jenis { display: flex; flex-wrap: wrap; gap: 8px; }
        .kg-pilih-jenis .kg-chip { padding: 7px 15px; }
        .kg-peserta-kotak { max-height: 190px; overflow-y: auto; border: 1px solid #e9edf3; border-radius: 12px; padding: 10px 12px; }

        @media (max-width: 767.98px) {
            .kg-kisi { border-spacing: 4px; }
            .kg-sel { height: 78px; padding: 5px; }
            .kg-acara { font-size: 0; padding: 0; height: 6px; margin-top: 3px; border-radius: 999px; }
            .kg-lebih { display: none; }
            .kg-bulan { min-width: 0; flex: 1 1 auto; font-size: .95rem; }
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

                    <div class="d-flex align-items-center gap-3">
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
                        <button type="button" class="btn btn-primary kg-btn rounded-3 px-3"
                            wire:click="buatBaru">
                            <i class="bi bi-plus-lg me-1"></i> Tambah
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Batang alat ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">

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
                        <button type="button" class="btn btn-light kg-btn border rounded-3 ms-1 px-3"
                            wire:click="keHariIni">
                            <i class="bi bi-dot"></i> Hari ini
                        </button>
                    </div>

                    <div class="kg-saring">
                        <button type="button"
                            class="kg-chip {{ $saringJenis === '' ? 'aktif' : '' }}"
                            style="{{ $saringJenis === '' ? 'background:#334155' : '' }}"
                            wire:click="$set('saringJenis', '')">
                            Semua
                        </button>
                        @foreach ($jenisPeta as $kunci => $j)
                        <button type="button"
                            class="kg-chip {{ $saringJenis === $kunci ? 'aktif' : '' }}"
                            style="{{ $saringJenis === $kunci ? 'background:'.$j['warna'] : '' }}"
                            wire:click="$set('saringJenis', '{{ $kunci }}')">
                            <span class="kg-titik" style="background:{{ $saringJenis === $kunci ? 'rgba(255,255,255,.85)' : $j['warna'] }}"></span>
                            {{ $j['label'] }}
                        </button>
                        @endforeach

                        <button type="button"
                            class="kg-chip {{ $hanyaSaya ? 'aktif' : '' }}"
                            style="{{ $hanyaSaya ? 'background:#0f766e' : '' }}"
                            wire:click="$toggle('hanyaSaya')">
                            <i class="bi bi-person-check"></i> Saya saja
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
                                    style="background: {{ $k->warna() }}"
                                    title="{{ $k->rentangWaktu() }} — {{ $k->judul }}">
                                    {{ $k->judul }}
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
                            <h5 class="fw-bold mb-0" style="font-size: 1rem; color:#1e293b;">
                                @if ($tanggalTerpilih)
                                {{ \Illuminate\Support\Carbon::parse($tanggalTerpilih)->locale('id')->translatedFormat('l, d F Y') }}
                                @else
                                Kegiatan Hari Terpilih
                                @endif
                            </h5>

                            @if ($tanggalTerpilih && $this->bolehTambah)
                            <button type="button" class="btn btn-sm btn-outline-primary kg-btn rounded-3"
                                wire:click="buatBaru('{{ $tanggalTerpilih }}')">
                                <i class="bi bi-plus-lg me-1"></i> Tambah di tanggal ini
                            </button>
                            @endif
                        </div>

                        @forelse ($daftarKegiatanTerpilih as $k)
                        <div class="kg-baris" wire:key="pilih-{{ $k->id }}">
                            <span class="kg-pita" style="background: {{ $k->warna() }}"></span>
                            <div class="kg-baris-isi">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <b>{{ $k->judul }}</b>
                                    <span class="kg-lencana" style="background: {{ $k->warna() }}">{{ $k->label() }}</span>
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
                                <div class="mt-2 d-flex gap-2">
                                    @if ($this->bolehUbah)
                                    <button type="button" class="btn btn-sm btn-light border kg-btn rounded-3"
                                        wire:click="sunting('{{ $k->id }}')">
                                        <i class="bi bi-pencil me-1"></i> Ubah
                                    </button>
                                    @endif
                                    @if ($this->bolehHapus)
                                    <button type="button" class="btn btn-sm btn-light border kg-btn rounded-3 text-danger hapus-kegiatan-btn"
                                        data-id="{{ $k->id }}" data-judul="{{ $k->judul }}">
                                        <i class="bi bi-trash me-1"></i> Hapus
                                    </button>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="kg-kosong">
                            @if ($tanggalTerpilih)
                            Tidak ada kegiatan pada tanggal ini.
                            @else
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
                        <h5 class="fw-bold mb-3" style="font-size: 1rem; color:#1e293b;">Agenda Terdekat</h5>

                        @forelse ($berikutnya as $k)
                        <div class="kg-baris" wire:key="next-{{ $k->id }}">
                            <span class="kg-pita" style="background: {{ $k->warna() }}"></span>
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
                        <div class="kg-kosong">Belum ada agenda mendatang.</div>
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
                    <button type="button" class="btn btn-light kg-nav kg-btn border" wire:click="tutupForm"
                        aria-label="Tutup">
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
                                style="{{ $jenis === $kunci ? 'background:'.$j['warna'] : '' }}"
                                wire:click="$set('jenis', '{{ $kunci }}')">
                                <span class="kg-titik" style="background:{{ $jenis === $kunci ? 'rgba(255,255,255,.85)' : $j['warna'] }}"></span>
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

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light border kg-btn rounded-3 px-3" wire:click="tutupForm">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary kg-btn rounded-3 px-4" wire:loading.attr="disabled">
                            <i class="bi bi-check2 me-1"></i>
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
