@section('title')
Bagian Pemeriksaan Kendaraan || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Bagian Pemeriksaan',
            'keterangan' => 'Yang dicek saat unit diserahkan dan saat kembali, berikut perkiraan biaya perbaikannya.',
        ])

        {{-- Keterangan pembuka, bukan hiasan.

             Yang membuka halaman ini akan bertanya dua hal: kenapa tingkat
             kondisinya tidak ikut bisa diubah, dan kenapa sebagian bagian tidak
             bisa dihapus. Dijawab di sini supaya tidak ditemukan sebagai
             penolakan saat tombolnya sudah ditekan. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-bagian-kepala mb-0">
                    <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <div class="orcha-bagian-judul">Yang bisa dan tidak bisa diubah di sini</div>
                        <div class="orcha-bagian-sub">
                            Daftar bagiannya bebas ditambah dan dipilah per jenis unit. Tingkat
                            kondisinya —
                            <strong>{{ collect($tingkatKondisi)->implode(' → ') }}</strong>
                            — tetap dikunci, karena urutannya dipakai membandingkan keadaan
                            sebelum dan sesudah pada seluruh serah terima yang sudah tersimpan.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-6">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari nama bagian...'])
                    </div>
                    <div class="col-12 col-sm-8 col-lg-4">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua jenis unit</option>
                            @foreach ($jenisKendaraan as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-lg-2">
                        <button type="button" class="orcha-btn orcha-btn-utama w-100" style="height:38px"
                            wire:click="bukaTambah">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($tambah || $sunting)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-{{ $sunting ? 'pencil' : 'plus-lg' }}"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">
                                {{ $sunting ? 'Ubah Bagian' : 'Bagian Baru' }}
                            </div>
                            <div class="orcha-bagian-sub">
                                Tarifnya perkiraan untuk mengusulkan denda — nota bengkel yang
                                sebenarnya selalu menang, dan admin bebas mengubahnya saat serah terima.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <label class="form-label small fw-semibold">
                                Nama bagian <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('isian.label') is-invalid @enderror"
                                wire:model="isian.label" maxlength="120"
                                placeholder="Mis. Pintu bagasi samping">
                            @error('isian.label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-lg-7">
                            <label class="form-label small fw-semibold">
                                Berlaku untuk jenis unit <span class="text-danger">*</span>
                            </label>
                            {{-- Kartu yang bisa ditekan, bukan kotak centang kecil —
                                 bentuk yang sama dengan pemilih transmisi di formulir
                                 armada. Sasaran kliknya seluruh kartu, dan yang terpilih
                                 ditandai warna SEKALIGUS tanda centang, karena warna saja
                                 tidak cukup bagi yang sulit membedakannya. --}}
                            @php
                                $rupaJenis = [
                                    'mobil' => ['bi-car-front-fill', 'Sedan, MPV, SUV'],
                                    'hiace' => ['bi-truck-front-fill', 'Minibus 15 kursi'],
                                    'bus' => ['bi-bus-front-fill', 'Medium & big bus'],
                                ];
                            @endphp

                            <div class="orcha-pilih-kartu" wire:key="jenis-bagian">
                                @foreach ($jenisKendaraan as $kunci => $label)
                                    <label class="orcha-kartu-pilihan {{ in_array($kunci, $jenisPilihan) ? 'aktif' : '' }}">
                                        <input type="checkbox" value="{{ $kunci }}" wire:model.live="jenisPilihan">
                                        <span class="tanda"><i class="bi bi-check-lg"></i></span>
                                        <i class="bi {{ $rupaJenis[$kunci][0] ?? 'bi-truck' }} rupa"></i>
                                        <span>
                                            <span class="judul">{{ $label }}</span>
                                            <span class="ket">{{ $rupaJenis[$kunci][1] ?? 'Unit sewa' }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('jenisPilihan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            {{-- Satu baris. Dua baris membuat kolom kanan lebih tinggi
                                 daripada kolom kirinya, dan di bawah "Nama bagian" menganga
                                 lubang kosong setinggi satu baris teks. --}}
                            <div class="form-text">
                                Yang tak berlaku hanya akan diisi "Baik" tanpa pernah diperiksa.
                            </div>
                        </div>

                        @foreach ([
                            ['biaya_lecet', 'Biaya lecet / minor'],
                            ['biaya_rusak', 'Biaya rusak'],
                            ['biaya_hilang', 'Biaya hilang'],
                        ] as [$medan, $label])
                            {{-- "Rp" di DALAM kotak isian lewat .orcha-rupiah, bukan kotak
                                 tempelan di sebelahnya. Kotak tempelan memecah satu isian
                                 jadi dua kotak bersebelahan dengan garis di tengahnya, dan
                                 pada baris berisi tiga isian uang hasilnya terlihat seperti
                                 tabel yang patah-patah. Aturannya sudah ada di lembar gaya
                                 bersama — dipakai seluruh isian uang di layar Orcha. --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">{{ $label }} <span class="text-danger">*</span></label>
                                <div class="orcha-rupiah">
                                    <input type="text" inputmode="numeric"
                                        class="orcha-uang form-control @error('uang.'.$medan) is-invalid @enderror"
                                        wire:model.blur="uang.{{ $medan }}"
                                        value="{{ $uang[$medan] ?? '' }}" placeholder="0">
                                </div>
                                @error('uang.'.$medan) <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        @endforeach

                        <div class="col-12">
                            {{-- Nol yang ditulis sadar berbeda dari nol karena lupa: bagian
                                 tanpa tarif membuat usulan denda diam-diam melewatinya —
                                 perhitungannya tetap jalan, angkanya kurang. --}}
                            <div class="form-text mt-0">
                                Boleh diisi 0 bila memang tidak ditagihkan, tetapi tidak boleh dikosongkan.
                            </div>
                        </div>

                        {{-- Selalu tampil, bukan hanya saat menyunting.

                             Sebelumnya hanya muncul di mode ubah, dan admin yang membuka
                             formulir tambah tidak pernah tahu bahwa keadaan ini ada —
                             apalagi bahwa MENONAKTIFKAN adalah jalan keluar untuk bagian
                             yang sudah tidak dipakai tetapi tidak bisa dihapus. --}}
                        <div class="col-12">
                            <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }}">
                                <span class="rupa">
                                    <i class="bi {{ $aktif ? 'bi-clipboard-check' : 'bi-slash-circle' }}"></i>
                                </span>
                                <span class="isi">
                                    <span class="judul">
                                        {{ $aktif ? 'Ikut diperiksa' : 'Tidak diperiksa lagi' }}
                                    </span>
                                    <span class="ket">
                                        {{ $aktif
                                            ? 'Muncul di ceklis serah terima untuk jenis unit yang dipilih di atas.'
                                            : 'Berhenti muncul di pemeriksaan baru — namanya tetap terbaca di lembar serah terima lama.' }}
                                    </span>
                                </span>
                                <span class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        wire:model.live="aktif">
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-besar w-100"
                                wire:click="tutup">
                                <i class="bi bi-x-lg"></i> Batal
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="orcha-btn orcha-btn-utama orcha-btn-besar w-100"
                                wire:click="simpan" wire:target="simpan" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="simpan">
                                    <i class="bi bi-save"></i> Simpan
                                </span>
                                <span wire:loading wire:target="simpan">
                                    <span class="spinner-border spinner-border-sm me-2"
                                        role="status" aria-hidden="true"></span>Menyimpan...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel orcha-tabel-bagian mb-0">
                        <thead>
                            <tr>
                                <th>Bagian</th>
                                <th>Jenis Unit</th>
                                <th>Lecet</th>
                                <th>Rusak</th>
                                <th>Hilang</th>
                                <th>Keadaan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $rupaJenisBaris = [
                                    'mobil' => ['bi-car-front-fill'],
                                    'hiace' => ['bi-truck-front-fill'],
                                    'bus' => ['bi-bus-front-fill'],
                                ];
                            @endphp

                            @forelse ($daftar as $baris)
                                <tr wire:key="bagian-{{ $baris['id'] }}"
                                    class="{{ $baris['aktif'] ? '' : 'orcha-baris-mati' }}">
                                    <td>
                                        <div class="fw-semibold">{{ $baris['label'] }}</div>
                                        <div class="orcha-kode" style="font-size:.74rem">{{ $baris['kunci'] }}</div>
                                    </td>
                                    <td>
                                        <div class="orcha-deret-jenis">
                                            @foreach ($baris['jenis'] as $j)
                                                <span class="orcha-cip-jenis" data-jenis="{{ $j }}">
                                                    <i class="bi {{ $rupaJenisBaris[$j][0] ?? 'bi-truck' }}"></i>
                                                    {{ $jenisKendaraan[$j] ?? $j }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    @foreach (['biaya_lecet', 'biaya_rusak', 'biaya_hilang'] as $medan)
                                        <td class="orcha-sel-uang">
                                            Rp {{ number_format($baris[$medan], 0, ',', '.') }}
                                        </td>
                                    @endforeach
                                    {{-- Hijau untuk yang sedang berlaku, abu netral untuk yang
                                         sengaja dimatikan. Lencananya tidak lagi meminjam dari
                                         layar lain: yang aktif dulu memakai lencana "sudah
                                         dibaca" milik pesan kontak — abu pudar, terbaca seperti
                                         sesuatu yang dimatikan — dan yang nonaktif memakai
                                         lencana "ditolak" milik pembatalan, merah, seolah ada
                                         yang salah. Menonaktifkan bagian justru tindakan yang
                                         benar dan disengaja. --}}
                                    <td>
                                        @if ($baris['aktif'])
                                            <span class="orcha-cip-keadaan" data-keadaan="aktif">
                                                <i class="bi bi-clipboard-check"></i> Diperiksa
                                            </span>
                                        @else
                                            <span class="orcha-cip-keadaan" data-keadaan="nonaktif">
                                                <i class="bi bi-slash-circle"></i> Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                                title="Ubah bagian ini"
                                                wire:click='bukaSunting({{ $baris['id'] }}, @json($baris))'>
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            {{-- Yang sudah menempel di lembar serah terima tidak
                                                 ditawari tombol hapus sama sekali. Menawarkannya lalu
                                                 menolak saat ditekan hanya memindahkan kekecewaan;
                                                 yang perlu diketahui admin adalah bahwa jalannya
                                                 memang menonaktifkan, bukan menghapus. --}}
                                            @if ($baris['pernah_dipakai'])
                                                <span class="btn btn-sm orcha-aksi orcha-aksi-mati"
                                                    title="Sudah tercatat di lembar serah terima — nonaktifkan saja lewat tombol ubah">
                                                    <i class="bi bi-lock"></i>
                                                </span>
                                            @else
                                                {{-- Sebentuk dengan tombol ubah di sebelahnya, dan dengan
                                                     tombol hapus di daftar armada: .orcha-aksi-hapus, bukan
                                                     .tombol-bahaya yang bentuknya lain sendiri.

                                                     Konfirmasinya lewat pcek-konfirmasi — SweetAlert seragam
                                                     yang dipakai seluruh tindakan merusak di lemon — bukan
                                                     wire:confirm bawaan, yang memunculkan kotak peramban
                                                     polos di tengah halaman yang sudah bergaya. --}}
                                                <button type="button"
                                                    class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                                    data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                    data-title="Hapus bagian ini?"
                                                    data-text="{{ addslashes($baris['label']) }} tidak akan muncul lagi di ceklis serah terima."
                                                    data-confirm="Ya, hapus" data-icon="warning"
                                                    title="Hapus bagian ini">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-list-check"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada bagian pemeriksaan.</p>
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

    <style>
        .orcha-tabel-bagian thead th,
        .orcha-tabel-bagian thead th.text-end { text-align: center !important; }

        .orcha-tabel-bagian td {
            padding-top: .6rem !important;
            padding-bottom: .6rem !important;
        }

        /* ===== Perataan kolom =====
           Judulnya ditengahkan, jadi isinya ikut ditengahkan — kecuali kolom
           pertama, yang panjang namanya berbeda-beda sehingga tepi kiri lurus
           lebih berguna daripada titik tengah yang bergerak.

           Sebelumnya angka rupiahnya rata kanan di bawah judul yang tengah:
           angkanya merapat ke kolom sebelahnya dan terbaca seperti milik
           kolom itu — "Rp 3.000.000" berdempet dengan lencana "Diperiksa".

           Lebar minimumnya dipatok supaya tiap kolom punya ruang sendiri dan
           tidak saling menekan begitu satu nama bagian memanjang. */
        .orcha-tabel-bagian th:first-child,
        .orcha-tabel-bagian td:first-child { text-align: left !important; min-width: 15rem; }

        .orcha-tabel-bagian td:nth-child(2),
        .orcha-tabel-bagian td:nth-child(6) { text-align: center; }

        .orcha-tabel-bagian th:nth-child(n+3):nth-child(-n+5),
        .orcha-tabel-bagian td:nth-child(n+3):nth-child(-n+5) {
            text-align: center;
            min-width: 8.5rem;
            padding-left: .9rem !important;
            padding-right: .9rem !important;
        }

        .orcha-tabel-bagian th:nth-child(6),
        .orcha-tabel-bagian td:nth-child(6) { min-width: 8rem; }

        /* BUKAN .orcha-rupiah: nama itu sudah dipakai lembar gaya bersama untuk
           kotak ISIAN uang, dan menempelkan "Rp" sendiri lewat ::before. Dipakai
           di sel tabel yang teksnya sudah memuat "Rp", hasilnya "Rp Rp 250.000". */
        .orcha-sel-uang { white-space: nowrap; font-variant-numeric: tabular-nums; }

        /* Lencana jenisnya dibungkus flex supaya jaraknya tetap sama dan
           barisnya membungkus rapi saat unit berlaku untuk ketiga jenis. */
        .orcha-deret-jenis {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
            justify-content: center;
        }

        /* Yang nonaktif diredupkan seluruh barisnya — bukan hanya lencananya —
           supaya daftar yang panjang tetap terbaca sekilas: yang pekat masih
           dipakai, yang pudar tidak. */
        .orcha-baris-mati > td { opacity: .55; }

        {{-- Gaya .orcha-aksi-mati pindah ke lembar gaya bersama: daftar
             pendaftaran open trip memakainya juga. --}}

    </style>
</div>
