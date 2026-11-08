@section('title')
Pendaftaran Trip || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*), supaya dua
         daftar yang dikerjakan orang yang sama tidak perlu dipelajari dua
         kali. Kartu tab dipakai bersama lewat partial — satu rupa, satu
         definisi. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.partials.kartu-tab-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $bolehKesehatan = auth()->user()->hasPermission('view_orcha_kesehatan');
        $adaSaringan = $this->adaSaringan();
        $warnaTab = $tabs[$tabAktif][2] ?? '#7c3aed';
        $targetMuat = 'cari,filterPaket,setTab,bersihkanSaringan,keHalaman';
    @endphp

    <div class="dsb pd-daftar">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Pendaftaran Trip</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="d-block">
                        Semua orang yang mendaftar trip — dari website maupun yang Anda masukkan sendiri.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.pendaftaran.daftarkan') }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #16a34a"
                    title="Masukkan rombongan yang sudah sepakat lewat WhatsApp">
                    <i class="bi bi-person-plus"></i><span>Daftarkan Rombongan</span>
                </a>

                @if ($bolehKesehatan)
                    <a href="{{ route('admin.orcha.pendaftaran.manifes', $this->saringanTampil()) }}"
                        class="dsb-tombol is-utama"
                        title="Unduh daftar peserta untuk tour leader — isinya mengikuti yang sedang tampil">
                        <i class="bi bi-filetype-pdf"></i><span>Unduh Daftar Peserta</span>
                    </a>
                @endif
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== TAB ==================

             Dua kelompok, dan pemisahannya disengaja: yang atas cara MELIHAT
             daftar, yang bawah DAFTAR PEKERJAAN. Digabung, "Perlu ditelepon"
             tenggelam jadi salah satu pilihan menonton — padahal ia
             satu-satunya yang uangnya bisa hangus kalau didiamkan. --}}
        <div class="dsb-rak">
            <div class="k-12">
                <div class="pt-grup-deret">
                    @foreach ($grupTab as [$judulGrup, $ketGrup, $kunciGrup])
                        <nav class="pt-grup" aria-label="{{ $judulGrup }}">
                            <div class="pt-grup-kepala">
                                <span class="pt-grup-judul">{{ $judulGrup }}</span>
                                <span class="pt-grup-ket">{{ $ketGrup }}</span>
                            </div>
                            <div class="pt-kartu-deret" style="--n: {{ count($kunciGrup) }}">
                                @foreach ($kunciGrup as $kunci)
                                    @php
                                        [$label, $ikon, $warna] = $tabs[$kunci];
                                        $angka = $jumlahTab[$kunci] ?? null;
                                        $iniAktif = $tabAktif === $kunci;
                                    @endphp
                                    <button type="button" wire:click="setTab('{{ $kunci }}')" style="--c: {{ $warna }}"
                                        class="pt-kartu-tab {{ $iniAktif ? 'is-aktif' : '' }} {{ $angka === 0 ? 'is-nol' : '' }}"
                                        aria-pressed="{{ $iniAktif ? 'true' : 'false' }}">
                                        <span class="pt-kartu-ikon"><i class="bi {{ $ikon }}"></i></span>
                                        <span class="pt-kartu-teks">
                                            {{-- Angkanya hilang, bukan jadi "0", bila Orcha belum
                                                 mengirimnya: nol yang keliru lebih buruk daripada
                                                 tidak ada angka sama sekali. --}}
                                            <span class="pt-kartu-angka">
                                                {{ $angka === null ? '–' : (999 < $angka ? '999+' : number_format($angka, 0, ',', '.')) }}
                                            </span>
                                            <span class="pt-kartu-label">{{ $label }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </nav>
                    @endforeach
                </div>
            </div>

            {{-- ================== SARINGAN ================== --}}
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-isi">
                    <div class="pd-saring">
                        <div class="dsb-medan">
                            <label class="dsb-label">Cari orangnya</label>
                            @include('livewire.pages.admin.orcha.partials.cari', [
                                'petunjuk' => 'Nama, nomor WhatsApp, atau kode pendaftaran',
                                'rupa' => 'dsb',
                            ])
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="pd-paket">Keberangkatan</label>
                            <select id="pd-paket" wire:model.live="filterPaket" class="dsb-isian">
                                <option value="" @selected((string) $this->filterPaket === '')>Semua keberangkatan</option>
                                @foreach ($pilihanPaket as $paket)
                                    <option value="{{ $paket['id'] }}" @selected((string) $this->filterPaket === (string) $paket['id'])>
                                        {{ $paket['nama'] }}@if ($paket['tanggal_berangkat'])
                                            · {{ \Carbon\Carbon::parse($paket['tanggal_berangkat'])->locale('id')->translatedFormat('d M Y') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="dsb-medan pd-medan-aksi">
                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut">
                                    <i class="bi bi-arrow-counterclockwise"></i><span>Tampilkan semua lagi</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($adaSaringan && $bolehKesehatan)
                        {{-- Akibat yang tidak terlihat dari layar: berkas yang
                             diunduh mengikuti saringan yang sedang hidup. Tour
                             leader yang membawa daftar hasil saringan lupa akan
                             berdiri di parkiran dengan daftar yang kurang satu
                             rombongan. --}}
                        <div class="pd-catatan">
                            <i class="bi bi-funnel-fill"></i>
                            <span>
                                Daftar sedang disaring. <strong>Unduh Daftar Peserta</strong> ikut tersaring juga —
                                kosongkan dulu kalau tour leader butuh daftar lengkapnya.
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================== DAFTARNYA ================== --}}
        <div class="dsb-rak">
            <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat" wire:target="{{ $targetMuat }}">
                <div class="pt-daftar-kepala" style="--c: {{ $warnaTab }}">
                    <div class="pt-daftar-judul">
                        <b>{{ $tabs[$tabAktif][0] ?? 'Semua' }}</b>
                        <span>
                            @if (($meta['total'] ?? 0) > 0)
                                {{ number_format($meta['total'], 0, ',', '.') }} pendaftaran
                                @if ($tabAktif === 'perlu_ditagih')
                                    — sudah bayar DP, belum lunas, dan tanggalnya sudah dekat. Yang paling mepet di atas.
                                @endif
                            @else
                                Belum ada isinya
                            @endif
                        </span>
                    </div>
                </div>

                @if (count($daftar) === 0)
                    <div class="dsb-kartu-isi">
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon">
                                <i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-inbox' }}"></i>
                            </span>
                            <p class="dsb-kosong-judul">
                                {{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada pendaftaran di sini' }}
                            </p>
                            <p class="dsb-kosong-ket">
                                {{ $adaSaringan
                                    ? 'Coba ganti kata pencarian atau pilih keberangkatan lain.'
                                    : 'Pendaftaran yang masuk lewat website akan muncul di sini sendiri.' }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="dsb-tabel-bungkus">
                        <table class="dsb-tabel pd-tabel">
                            <thead>
                                <tr>
                                    <th>Yang mendaftar</th>
                                    <th>Ikut trip</th>
                                    <th>Berangkat</th>
                                    <th>Data kesehatan</th>
                                    <th>Keadaan</th>
                                    <th class="text-end">Buka</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($daftar as $baris)
                                    @php
                                        $berangkat = $baris['tanggal_berangkat']
                                            ? \Carbon\Carbon::parse($baris['tanggal_berangkat'])->startOfDay()
                                            : null;
                                        $selisihHari = $berangkat ? now()->startOfDay()->diffInDays($berangkat, false) : null;
                                        $terisi = (int) ($baris['kesehatan_terisi'] ?? 0);
                                        $totalPeserta = max(1, (int) $baris['jumlah_peserta']);
                                        $lengkap = $baris['kesehatan_lengkap'] ?? false;
                                        $titikJemput = $baris['jemput_per_titik'] ?? [];
                                        $angsuran = $baris['angsuran'] ?? null;
                                        $temponBerikutnya = ! empty($angsuran['berikutnya'])
                                            ? \Carbon\Carbon::parse($angsuran['berikutnya'])->locale('id')->translatedFormat('d M')
                                            : null;
                                        $urlDetail = route('admin.orcha.pendaftaran.detail', $baris['id']);
                                    @endphp
                                    <tr wire:key="pendaftaran-{{ $baris['id'] }}">
                                        {{-- ===== Yang mendaftar ===== --}}
                                        <td data-judul="Yang mendaftar">
                                            <a wire:navigate href="{{ $urlDetail }}" class="dsb-tabel-utama pd-tautan">
                                                <span class="dsb-tabel-teks">
                                                    <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>
                                                    <span class="dsb-tabel-meta">
                                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                                        <span><i class="bi bi-whatsapp"></i>{{ $baris['whatsapp'] }}</span>
                                                    </span>
                                                    <span class="dsb-tabel-samar">
                                                        <i class="bi bi-clock"></i>daftar
                                                        {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->locale('id')->translatedFormat('d M Y') }}
                                                    </span>
                                                </span>
                                            </a>

                                            <span class="pd-cip-deret">
                                                <span class="dsb-lencana is-abu">
                                                    <i class="bi bi-people-fill"></i>{{ $baris['jumlah_peserta'] }} orang
                                                </span>

                                                {{-- Rombongan tanpa nama peserta tidak masuk daftar
                                                     panggil-nama. Ditandai di sini supaya ketemu
                                                     sebelum hari berangkat, bukan di lapangan.
                                                     Cipnya sekaligus jalan keluarnya. --}}
                                                @if (empty($baris['peserta']))
                                                    <a href="{{ route('admin.orcha.pendaftaran.peserta', $baris['id']) }}"
                                                        wire:navigate class="dsb-lencana is-kuning text-decoration-none"
                                                        title="Nama pesertanya belum ditulis — klik untuk mengisinya">
                                                        <i class="bi bi-person-dash"></i>nama belum ditulis
                                                    </a>
                                                @endif

                                                {{-- Hanya di daftar telepon: yang mengangkat gagang
                                                     perlu tahu mana yang sudah dikirimi pengingat dan
                                                     tetap diam, dan mana yang memang belum pernah
                                                     dihubungi. Dua keadaan itu menuntut kalimat
                                                     pembuka yang berbeda. --}}
                                                @if ($perluDitagih)
                                                    @if (! empty($baris['pengingat_pelunasan_pada']))
                                                        <span class="dsb-lencana is-biru">
                                                            <i class="bi bi-envelope-check"></i>sudah diingatkan
                                                            {{ \Carbon\Carbon::parse($baris['pengingat_pelunasan_pada'])->locale('id')->diffForHumans() }}
                                                        </span>
                                                    @else
                                                        <span class="dsb-lencana is-abu">
                                                            <i class="bi bi-envelope"></i>belum pernah diingatkan
                                                        </span>
                                                    @endif
                                                @endif
                                            </span>
                                        </td>

                                        {{-- ===== Ikut trip ===== --}}
                                        <td data-judul="Ikut trip">
                                            <span class="dsb-tabel-teks">
                                                <span class="dsb-tabel-judul">{{ $baris['paket']['nama'] }}</span>
                                                @if (! empty($titikJemput))
                                                    <span class="dsb-tabel-meta"
                                                        title="@foreach ($titikJemput as $titik => $orang){{ $titik }}: {{ implode(', ', $orang) }}&#10;@endforeach">
                                                        <i class="bi bi-geo-alt-fill"></i>dijemput di {{ count($titikJemput) }} tempat
                                                    </span>
                                                @elseif ($baris['titik_jemput'])
                                                    <span class="dsb-tabel-meta">
                                                        <i class="bi bi-geo-alt-fill"></i>{{ $baris['titik_jemput'] }}
                                                    </span>
                                                @else
                                                    <span class="dsb-tabel-samar">tempat jemput belum diisi</span>
                                                @endif
                                            </span>
                                        </td>

                                        {{-- ===== Berangkat ===== --}}
                                        <td data-judul="Berangkat">
                                            @if ($berangkat)
                                                <span class="dsb-tabel-teks">
                                                    <span class="dsb-tabel-angka">{{ $berangkat->locale('id')->translatedFormat('d M Y') }}</span>
                                                    {{-- Hitungan mundur, bukan tanggal saja: yang
                                                         menentukan tindakan hari ini adalah jarak ke
                                                         keberangkatan, dan menghitungnya sendiri dari
                                                         kalender itu pekerjaan yang berulang belasan
                                                         kali sehari. --}}
                                                    @if ($selisihHari > 0)
                                                        <span class="dsb-lencana {{ $selisihHari <= 5 ? 'is-merah' : 'is-abu' }}">
                                                            {{ $selisihHari <= 5 ? 'tinggal' : '' }} {{ $selisihHari }} hari lagi
                                                        </span>
                                                    @elseif ($selisihHari === 0)
                                                        <span class="dsb-lencana is-merah"><i class="bi bi-airplane"></i>berangkat hari ini</span>
                                                    @else
                                                        <span class="dsb-lencana is-abu">sudah berangkat</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="dsb-tabel-samar">tanggalnya belum ditentukan</span>
                                            @endif
                                        </td>

                                        {{-- ===== Data kesehatan ===== --}}
                                        <td data-judul="Data kesehatan">
                                            {{-- Harus terlihat jauh sebelum hari berangkat, bukan
                                                 saat rombongan sudah berkumpul. --}}
                                            <span class="dsb-tabel-teks">
                                                <span class="dsb-lencana {{ $lengkap ? 'is-hijau' : ($terisi === 0 ? 'is-merah' : 'is-kuning') }}"
                                                    @if (! empty($baris['peserta_belum_isi']))
                                                        title="Belum mengisi: {{ implode(', ', $baris['peserta_belum_isi']) }}"
                                                    @endif>
                                                    <i class="bi {{ $lengkap ? 'bi-heart-pulse-fill' : 'bi-heart-pulse' }}"></i>
                                                    {{ $terisi }} dari {{ $baris['jumlah_peserta'] }} orang
                                                </span>
                                                <span class="pd-batang {{ $lengkap ? 'is-lengkap' : '' }}">
                                                    <span style="width: {{ min(100, $terisi / $totalPeserta * 100) }}%"></span>
                                                </span>
                                            </span>
                                        </td>

                                        {{-- ===== Keadaan ===== --}}
                                        <td data-judul="Keadaan">
                                            <span class="dsb-tabel-teks">
                                                {{-- Bila daftar statusnya TIDAK sampai, yang digambar
                                                     tulisannya — bukan kotak pilih kosong. Daftar itu
                                                     datang dari Orcha, dan saat Orcha sedang tidak
                                                     bisa dihubungi kotak pilih tanpa satu pun pilihan
                                                     tergambar benar-benar kosong: statusnya lenyap
                                                     tanpa satu pun kalimat yang menjelaskan sebabnya.
                                                     Statusnya sendiri selalu ada — ia datang bersama
                                                     barisnya. --}}
                                                @if ($pilihanStatus === [])
                                                    <span class="dsb-lencana is-abu"
                                                        title="Daftar pilihan belum bisa diambil dari Orcha, jadi keadaannya belum bisa diubah dari sini.">
                                                        <i class="bi bi-wifi-off"></i>{{ $baris['status_label'] ?? $baris['status'] }}
                                                    </span>
                                                @else
                                                    <select class="dsb-isian pd-pilih-status status-{{ $baris['status'] }}"
                                                        wire:change="ubahStatus({{ $baris['id'] }}, $event.target.value)"
                                                        title="Ubah keadaan pendaftaran ini">
                                                        @foreach ($pilihanStatus as $kunci => $label)
                                                            <option value="{{ $kunci }}" @selected($baris['status'] === $kunci)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif

                                                {{-- Pesanan yang belum lunas menjelang berangkat berarti
                                                     dua hal yang sangat berbeda: ia menunggak, atau ia
                                                     sedang menjalani jadwal yang kita sendiri berikan.
                                                     Menelepon orang yang membayar tepat waktu dengan
                                                     nada penagihan merusak kesepakatan yang baru
                                                     dibuat. Tiga keadaan, tiga kalimat. --}}
                                                @if ($angsuran)
                                                    @if ($angsuran['telat'] > 0)
                                                        <span class="dsb-lencana is-merah"
                                                            title="Dicicil {{ $angsuran['jumlah_termin'] }} kali. {{ $angsuran['telat'] }} cicilan sudah lewat tanggalnya dan belum dibayar.">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                            telat bayar cicilan {{ $angsuran['telat'] }}×
                                                        </span>
                                                    @elseif ($angsuran['selesai'])
                                                        <span class="dsb-lencana is-hijau" title="Semua cicilan sudah dibayar.">
                                                            <i class="bi bi-calendar2-check-fill"></i>cicilan lunas
                                                        </span>
                                                    @else
                                                        <span class="dsb-lencana is-biru"
                                                            title="Dicicil {{ $angsuran['jumlah_termin'] }} kali. Cicilan berikutnya jatuh tempo {{ $temponBerikutnya }}.">
                                                            <i class="bi bi-calendar2-range-fill"></i>
                                                            cicilan {{ $angsuran['lunas'] }} dari {{ $angsuran['jumlah_termin'] }}
                                                            @if ($temponBerikutnya) · berikutnya {{ $temponBerikutnya }} @endif
                                                        </span>
                                                    @endif
                                                @endif
                                            </span>
                                        </td>

                                        {{-- ===== Buka ===== --}}
                                        <td data-judul="Buka" class="text-end">
                                            <span class="dsb-tabel-aksi">
                                                <a wire:navigate href="{{ $urlDetail }}" class="dsb-tabel-btn"
                                                    title="Lihat semuanya: pembayaran, peserta, pembatalan">
                                                    <i class="bi bi-person-lines-fill"></i>
                                                </a>

                                                @if ($bolehKesehatan && ($baris['jumlah_riwayat_kesehatan'] ?? 0) > 0)
                                                    <a wire:navigate href="{{ route('admin.orcha.pendaftaran.kesehatan', $baris['id']) }}"
                                                        class="dsb-tabel-btn pd-btn-sehat"
                                                        title="Lihat data kesehatan {{ $baris['jumlah_riwayat_kesehatan'] }} peserta">
                                                        <i class="bi bi-heart-pulse"></i>
                                                    </a>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="dsb-kartu-isi pd-kaki">
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- <style> telanjang: layout templateindex tidak punya satu pun stack,
         jadi gaya yang didorong ke sana tidak pernah ikut tergambar. --}}
    <style>
        /* Saringan: pencarian melebar, keberangkatan tetap, tombol di ujung.
           Grid, bukan flex bebas — dengan flex tombolnya sempat terlempar ke
           baris kedua persis saat saringan dinyalakan, yaitu saat tangan
           sedang di sana. */
        .pd-saring {
            display: grid; gap: 12px; align-items: end;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) auto;
        }
        @media (max-width: 767.98px) {
            .pd-saring { grid-template-columns: 1fr; }
        }
        .pd-medan-aksi { display: flex; align-items: flex-end; }
        .pd-medan-aksi:empty { display: none; }

        .pd-catatan {
            display: flex; align-items: flex-start; gap: 8px; margin-top: 12px;
            padding: 9px 11px; border-radius: 10px; font-size: .8rem; line-height: 1.5;
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
        }

        /* Baris tabel: nama di atas, keterangan menumpuk di bawahnya. */
        .pd-tautan { text-decoration: none; color: inherit; display: block; }
        .pd-tautan:hover .dsb-tabel-judul { color: #4f46e5; }
        .pd-cip-deret { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; }

        /* Batang kemajuan data kesehatan. Angka saja ("7 dari 11") menuntut
           pembagian di kepala untuk tahu seberapa jauh; batangnya menjawab
           itu sebelum angkanya dibaca. */
        .pd-batang {
            display: block; height: 4px; border-radius: 999px;
            background: #eef2f7; overflow: hidden; margin-top: 5px; max-width: 150px;
        }
        .pd-batang > span { display: block; height: 100%; background: #f59e0b; border-radius: 999px; }
        .pd-batang.is-lengkap > span { background: #16a34a; }

        /* Kotak pilih keadaan: selebar isinya, bukan selebar sel — kotak
           sepanjang kolom membuat lencana di bawahnya terlihat menggantung. */
        .pd-pilih-status { max-width: 170px; font-size: .8rem; padding-top: 5px; padding-bottom: 5px; }
        .pd-btn-sehat { color: #be123c; }

        /* Kaki kartu: penomoran halaman menempel ke tepi bawah tanpa ini,
           dan kartunya terbaca seperti terpotong. */
        .pd-kaki { padding-top: 4px; }
    </style>
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
