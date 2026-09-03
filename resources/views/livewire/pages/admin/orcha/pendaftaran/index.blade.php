@section('title')
Pendaftaran Open Trip || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Pendaftaran Open Trip',
            'keterangan' => 'Peserta yang mendaftar lewat website Orcha Journey.',
        ])

        {{-- Pencarian & saringan --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                {{-- Bilah saringan memakai FLEX, bukan grid dua belas kolom.

                     Grid-nya sudah dua kali menjatuhkan tombol terakhir ke baris
                     kedua, dan dua kali sebabnya berbeda: sekali karena jumlah
                     kolomnya lewat, sekali karena ms-auto menyerap sisa ruang jadi
                     margin. Keduanya lolos dari penjaga yang menghitung kolom, dan
                     keduanya baru ketahuan lewat potret layar.

                     Dengan flex, tiap benda menyebut lebar dasarnya sendiri dan
                     boleh menyusut — tidak ada jatah dua belas yang harus dijaga
                     tiap kali satu tombol ditambahkan. Pola ini sudah dipakai kartu
                     sakelar di bawahnya. --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div style="flex:1 1 170px">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari kode, nama...'])
                    </div>

                    {{-- Saringan paket berdiri sendiri, tidak menumpang kotak cari.
                         Manifes tour leader dibentuk dari daftar yang sedang tampil,
                         dan mengetik "Banyuwangi" bisa ikut menyeret paket lain yang
                         namanya mirip — kelebihan satu rombongan di manifes baru
                         ketahuan saat rombongannya sudah berkumpul. --}}
                    <div style="flex:2 1 220px">
                        <select wire:model.live="filterPaket" class="form-select">
                            <option value="">Semua paket</option>
                            {{-- @selected ditulis meski nilainya diikat wire:model.

                                 Tanpa itu, markup dari server tidak pernah menandai pilihan yang
                                 sedang aktif, dan setelah "Bersihkan saringan" kotak ini tetap
                                 memajang paket lama sementara daftarnya sudah tidak disaring —
                                 layar yang berbohong tentang keadaannya sendiri. --}}
                            @foreach ($pilihanPaket as $paket)
                                <option value="{{ $paket['id'] }}" @selected((string) $filterPaket === (string) $paket['id'])>
                                    {{ $paket['nama'] }}@if ($paket['tanggal_berangkat'])
                                        · {{ \Carbon\Carbon::parse($paket['tanggal_berangkat'])->locale('id')->translatedFormat('d M Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="flex:1 1 160px">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua status</option>
                            @foreach ($pilihanStatus as $kunci => $label)
                                <option value="{{ $kunci }}" @selected($filterStatus === $kunci)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pengosong saringan DUDUK DI BARIS YANG SAMA dengan yang
                         dibersihkannya.

                         Sebelumnya ia berbaris sendiri di bawah, terpisah dari tiga
                         kotak yang justru jadi urusannya — dan tombol yang jauh dari
                         benda yang diubahnya menuntut mata berpindah dua kali untuk
                         mengerti hubungannya.

                         Lebarnya mengikuti isi: ia cuma muncul kadang-kadang, dan
                         jatah tetap yang kosong separuh waktu menggeser tombol lain
                         bolak-balik setiap kali saringannya dinyalakan. --}}
                    @if ($this->adaSaringan())
                        <button type="button" wire:click="bersihkanSaringan"
                            class="orcha-btn orcha-btn-lembut orcha-btn-seragam"
                            title="Kosongkan pencarian, paket, status, dan saringan tagihan sekaligus">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Bersihkan</span>
                        </button>
                    @endif

                </div>

                {{-- Saringan tagihan berdiri sendiri di barisnya, bukan jadi
                     pilihan keempat di dalam kotak status.

                     Yang ini bukan cara MELIHAT daftar, melainkan daftar
                     pekerjaan: siapa yang harus ditelepon hari ini. Menaruhnya
                     sejajar dengan "Semua status" membuatnya tenggelam jadi
                     salah satu dari sekian pilihan, dan pekerjaan yang tidak
                     terlihat tidak pernah dikerjakan.

                     Pengingat pelunasan sudah dikirim sistem tiap pagi. Yang
                     tersisa di daftar ini justru yang sudah dikirimi dan tetap
                     diam — dan itu cuma bisa diselesaikan lewat telepon. --}}
                {{-- Sakelar dan tindakan SEBARIS.

                     Baris pertama sudah penuh oleh empat saringan, dan memaksa
                     Daftarkan serta Manifes ikut di sana membuat salah satunya
                     terlempar sendirian ke baris kedua — bergesernya terjadi persis
                     saat saringan dinyalakan, yaitu saat admin sedang menekan
                     tombol.

                     Turun ke sini, keduanya sejajar dengan sakelar dan barisnya
                     tetap satu. Sakelarnya menyusut mengikuti ruang yang tersisa;
                     lebar dasarnya 260px, cukup untuk judul dan keterangannya
                     sebelum ia mulai membungkus. --}}
                <div class="d-flex flex-wrap align-items-stretch gap-2 mt-3">
                    <label class="orcha-sakelar-kartu {{ $perluDitagih ? 'nyala' : '' }} mb-0" style="flex:1 1 260px">
                        <span class="rupa">
                            <i class="bi {{ $perluDitagih ? 'bi-telephone-outbound' : 'bi-cash-coin' }}"></i>
                        </span>
                        <span class="isi">
                            <span class="judul">
                                {{ $perluDitagih ? 'Sedang melihat yang perlu ditagih' : 'Perlu ditagih' }}
                            </span>
                            <span class="ket">
                                {{ $perluDitagih
                                    ? ($meta['total'] ?? 0) . ' pendaftaran sudah bayar DP tetapi belum lunas, dan tanggalnya sudah dekat. Yang paling mepet di atas.'
                                    : 'Sudah bayar DP, belum lunas, dan tanggalnya sudah dekat.' }}
                            </span>
                        </span>
                        <span class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" wire:model.live="perluDitagih">
                        </span>
                    </label>

                    {{-- Daftarkan dan Manifes bukan cara MELIHAT daftar melainkan hal
                         yang dikerjakan terhadapnya — karena itu terpisah dari
                         saringan di atas, dan didorong ke ujung kanan.

                         Pada layar sempit ms-auto tidak berlaku dan keduanya turun
                         mengikuti sakelar; itu memang yang diinginkan. --}}
                    <div class="d-flex gap-2 ms-lg-auto align-items-center" style="flex:0 1 auto">
                        <a href="{{ route('admin.orcha.pendaftaran.daftarkan') }}" wire:navigate
                            class="orcha-btn orcha-btn-lembut orcha-btn-seragam"
                            title="Masukkan rombongan yang disepakati lewat WhatsApp">
                            <i class="bi bi-person-plus"></i>
                            <span>Daftarkan</span>
                        </a>

                        @if (auth()->user()->hasPermission('view_orcha_kesehatan'))
                            <a href="{{ route('admin.orcha.pendaftaran.manifes', $this->saringanTampil()) }}"
                                class="orcha-btn orcha-btn-utama orcha-btn-seragam"
                                title="Manifes tour leader untuk daftar yang sedang tampil">
                                <i class="bi bi-filetype-pdf"></i>
                                <span>Manifes</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Keterangannya tinggal di bawah; tombolnya sudah naik ke baris
                     saringan.

                     Yang disebut di sini akibat yang tidak terlihat dari layar:
                     manifes yang diunduh mengikuti saringan yang sedang hidup.
                     Tour leader yang membawa manifes hasil saringan lupa akan
                     berdiri di parkiran dengan daftar yang kurang satu rombongan. --}}
                @if ($this->adaSaringan())
                    <div class="text-muted mt-3" style="font-size:.78rem">
                        <i class="bi bi-funnel"></i>
                        Daftar sedang disaring — manifes yang diunduh mengikuti saringan ini.
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel orcha-tabel-judul-tengah mb-0">
                        <thead>
                            <tr>
                                {{-- Kode dan nama disatukan: keduanya menjawab pertanyaan yang
                                     sama — pesanan siapa ini — dan tujuh kolom membuat tabelnya
                                     lebih lebar daripada kartunya, sehingga kolom aksi terpotong
                                     di tepi kanan. --}}
                                <th>Pemesan</th>
                                <th>Paket</th>
                                <th>Berangkat</th>
                                <th>Riwayat kesehatan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                @php
                                    $berangkat = $baris['tanggal_berangkat']
                                        ? \Carbon\Carbon::parse($baris['tanggal_berangkat'])->startOfDay()
                                        : null;
                                    $selisihHari = $berangkat ? now()->startOfDay()->diffInDays($berangkat, false) : null;

                                    $terisi = (int) ($baris['kesehatan_terisi'] ?? 0);
                                    $totalPeserta = max(1, (int) $baris['jumlah_peserta']);
                                    $lengkap = $baris['kesehatan_lengkap'] ?? false;
                                    $titikJemput = $baris['jemput_per_titik'] ?? [];
                                @endphp
                                <tr wire:key="pendaftaran-{{ $baris['id'] }}">
                                    <td>
                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                        <div class="fw-semibold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.76rem">
                                            {{ $baris['whatsapp'] }} · masuk
                                            {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->locale('id')->translatedFormat('d M Y') }}
                                        </div>
                                        <span class="orcha-cip-peserta mt-1">
                                            <i class="bi bi-people-fill"></i>
                                            {{ $baris['jumlah_peserta'] }} peserta
                                        </span>
                                        {{-- Rombongan tanpa nama peserta tidak masuk manifes
                                             panggil-nama. Ditandai di sini supaya bisa dicari
                                             sebelum hari berangkat, bukan ditemukan di lapangan. --}}
                                        {{-- Hanya saat daftar tagihan menyala.

                                             Yang mengangkat telepon perlu tahu mana yang sudah
                                             menerima surat pengingat dan tetap diam, dan mana
                                             yang memang belum pernah dihubungi sama sekali. Dua
                                             keadaan itu menuntut kalimat pembuka yang berbeda —
                                             menanyakan "sudah terima email kami?" kepada orang
                                             yang belum dikirimi membuat kita terdengar seperti
                                             sedang mengarang. --}}
                                        @if ($perluDitagih)
                                            @if (! empty($baris['pengingat_pelunasan_pada']))
                                                <div class="text-muted mt-1" style="font-size:.72rem">
                                                    <i class="bi bi-envelope-check"></i>
                                                    diingatkan
                                                    {{ \Carbon\Carbon::parse($baris['pengingat_pelunasan_pada'])->locale('id')->diffForHumans() }}
                                                </div>
                                            @else
                                                <div class="text-muted mt-1" style="font-size:.72rem">
                                                    <i class="bi bi-envelope"></i> belum diingatkan
                                                </div>
                                            @endif
                                        @endif

                                        @if (empty($baris['peserta']))
                                            {{-- Cipnya sekaligus jalan keluarnya: sekali klik
                                                 langsung ke halaman pengisian nama, tanpa mampir
                                                 ke detail dulu. --}}
                                            <a href="{{ route('admin.orcha.pendaftaran.peserta', $baris['id']) }}"
                                                wire:navigate class="orcha-cip-awas mt-1 text-decoration-none"
                                                title="Nama peserta belum didata — klik untuk melengkapinya">
                                                <i class="bi bi-person-dash"></i> nama belum didata
                                            </a>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="small fw-semibold">{{ $baris['paket']['nama'] }}</div>
                                        {{-- Nama peserta per titik jemput dulu dicetak seluruhnya di sel
                                             ini, dan satu baris pesanan bisa memenuhi setengah layar.
                                             Yang perlu terlihat di daftar cuma berapa titiknya; nama
                                             per titik dibaca sopir dari halaman detail dan manifes. --}}
                                        @if (! empty($titikJemput))
                                            <span class="orcha-cip-jemput"
                                                title="@foreach ($titikJemput as $titik => $orang){{ $titik }}: {{ implode(', ', $orang) }}&#10;@endforeach">
                                                <i class="bi bi-geo-alt-fill"></i>
                                                {{ count($titikJemput) }} titik jemput
                                            </span>
                                        @elseif ($baris['titik_jemput'])
                                            <span class="orcha-cip-jemput">
                                                <i class="bi bi-geo-alt-fill"></i>
                                                {{ $baris['titik_jemput'] }}
                                            </span>
                                        @else
                                            <span class="text-muted" style="font-size:.76rem">Titik jemput belum diisi</span>
                                        @endif
                                    </td>

                                    <td class="text-center text-nowrap">
                                        @if ($berangkat)
                                            <div class="small fw-semibold">{{ $berangkat->locale('id')->translatedFormat('d M Y') }}</div>
                                            {{-- Hitungan mundur, bukan tanggal saja: yang menentukan
                                                 tindakan admin hari ini adalah jarak ke keberangkatan,
                                                 dan menghitungnya sendiri dari kalender itu pekerjaan
                                                 yang berulang belasan kali sehari. --}}
                                            @if ($selisihHari > 0)
                                                <span class="orcha-cip-hari {{ $selisihHari <= 5 ? 'dekat' : '' }}">
                                                    H-{{ $selisihHari }}
                                                </span>
                                            @elseif ($selisihHari === 0)
                                                <span class="orcha-cip-hari dekat">Berangkat hari ini</span>
                                            @else
                                                <span class="orcha-cip-hari lewat">Sudah lewat</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Belum dijadwalkan</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        {{-- Kelengkapan riwayat kesehatan harus terlihat jauh sebelum
                                             hari berangkat, bukan saat rombongan sudah berkumpul. --}}
                                        <span class="orcha-cip-sehat {{ $lengkap ? 'lengkap' : ($terisi === 0 ? 'kosong' : '') }}"
                                            @if (! empty($baris['peserta_belum_isi']))
                                                title="Belum mengisi: {{ implode(', ', $baris['peserta_belum_isi']) }}"
                                            @endif>
                                            <i class="bi {{ $lengkap ? 'bi-heart-pulse-fill' : 'bi-heart-pulse' }}"></i>
                                            {{ $terisi }}/{{ $baris['jumlah_peserta'] }}
                                        </span>
                                        <div class="orcha-sehat-batang mt-1 {{ $lengkap ? 'lengkap' : '' }}">
                                            <span style="width: {{ min(100, $terisi / $totalPeserta * 100) }}%"></span>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        {{-- Tetap bisa diubah langsung dari daftar, tapi warnanya
                                             mengikuti keadaan: lima kotak putih berjajar tidak
                                             memberi tahu apa pun sampai tulisannya dibaca satu per
                                             satu. --}}
                                        {{-- Bila daftar statusnya TIDAK sampai, yang digambar
                                             tulisannya — bukan kotak pilih kosong.

                                             Daftar itu datang dari /rujukan milik Orcha, dan
                                             saat Orcha sedang tidak bisa dihubungi
                                             rujukan() mengembalikan senarai kosong. Kotak
                                             pilih tanpa satu pun pilihan tergambar sebagai
                                             pil berwarna yang benar-benar kosong: statusnya
                                             lenyap dari layar tanpa satu pun kalimat yang
                                             menjelaskan sebabnya, dan admin membacanya
                                             sebagai data yang hilang.

                                             Statusnya sendiri selalu ada — ia datang bersama
                                             barisnya, bukan dari rujukan. Jadi yang benar:
                                             tetap tunjukkan statusnya, dan katakan bahwa yang
                                             belum bisa dilakukan hanya mengubahnya. --}}
                                        @if ($pilihanStatus === [])
                                            <span class="orcha-status-diam status-{{ $baris['status'] }}"
                                                title="Daftar status belum bisa diambil dari Orcha, jadi statusnya belum bisa diubah dari sini.">
                                                <i class="bi bi-wifi-off"></i>
                                                {{ $baris['status_label'] ?? $baris['status'] }}
                                            </span>
                                        @else
                                            <select class="form-select form-select-sm orcha-pilih-status status-{{ $baris['status'] }}"
                                                wire:change="ubahStatus({{ $baris['id'] }}, $event.target.value)">
                                                @foreach ($pilihanStatus as $kunci => $label)
                                                    <option value="{{ $kunci }}" @selected($baris['status'] === $kunci)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>

                                    <td class="text-center text-nowrap">
                                        {{-- Data pelanggan selengkapnya — pembayaran, peserta, dan
                                             pengajuan pembatalan — ada di halamannya sendiri. --}}
                                        <a href="{{ route('admin.orcha.pendaftaran.detail', $baris['id']) }}"
                                            class="btn btn-sm orcha-aksi orcha-aksi-lihat orcha-aksi-berlabel"
                                            title="Lihat detail pelanggan">
                                            <i class="bi bi-person-lines-fill"></i>
                                            <span>Detail</span>
                                        </a>

                                        @if (! auth()->user()->hasPermission('view_orcha_kesehatan'))
                                            <span class="text-muted small">—</span>
                                        @elseif (($baris['jumlah_riwayat_kesehatan'] ?? 0) > 0)
                                            {{-- Menuju halamannya sendiri, sama seperti tombol di
                                                 halaman detail. Warnanya dilembutkan dari merah
                                                 menyala: merah di aplikasi ini berarti hapus atau
                                                 rugi, dan membuka riwayat kesehatan bukan keduanya. --}}
                                            <a href="{{ route('admin.orcha.pendaftaran.kesehatan', $baris['id']) }}"
                                                wire:navigate
                                                class="btn btn-sm orcha-aksi orcha-aksi-sehat orcha-aksi-berlabel orcha-aksi-riwayat"
                                                title="Lihat riwayat kesehatan peserta">
                                                <i class="bi bi-heart-pulse"></i>
                                                <span>Riwayat ({{ $baris['jumlah_riwayat_kesehatan'] }})</span>
                                            </a>
                                        @else
                                            {{-- Berbentuk tombol yang sengaja mati, bukan tulisan
                                                 telanjang: sebaris tombol yang salah satu selnya
                                                 berisi teks polos membuat kolom Aksi terlihat
                                                 bolong dan barisnya tidak lagi sejajar.

                                                 Sengaja tidak dibuat bisa ditekan — halamannya
                                                 memang tidak punya apa-apa untuk ditampilkan, dan
                                                 tombol yang membuka halaman kosong lebih
                                                 mengecewakan daripada tombol yang jelas mati. --}}
                                            <span class="btn btn-sm orcha-aksi orcha-aksi-mati orcha-aksi-berlabel orcha-aksi-riwayat"
                                                title="Belum ada peserta yang mengisi riwayat kesehatannya">
                                                <i class="bi bi-heart-pulse"></i>
                                                <span>Belum ada riwayat</span>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-clipboard-x"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada pendaftaran yang cocok.</p>
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
