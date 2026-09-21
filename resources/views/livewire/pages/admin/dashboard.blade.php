<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'));
    }
}; ?>

@section('title')
Dashboard || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')

    @php
        // Salam dirakit di server: satu-satunya waktu yang benar bagi toko ini
        // adalah waktu servernya. Versi lama mengandalkan jam KOMPUTER ADMIN
        // lewat JavaScript, jadi laptop yang zonanya meleset menyapa "Selamat
        // Malam" pada pukul sembilan pagi.
        $jam = (int) now()->format('H');
        $salam = match (true) {
            $jam >= 5 && $jam < 11 => 'Selamat pagi',
            $jam >= 11 && $jam < 15 => 'Selamat siang',
            $jam >= 15 && $jam < 18 => 'Selamat sore',
            $jam >= 1 && $jam < 5 => 'Selamat dini hari',
            default => 'Selamat malam',
        };
        $namaDepan = \Illuminate\Support\Str::of(Auth::user()->name)->trim()->explode(' ')->first();

        $fotoAku = Auth::user()->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists(Auth::user()->profile_photo)
            ? Storage::url(Auth::user()->profile_photo)
            : null;

        // Lencana status pesanan: warnanya sama dengan yang dipakai halaman
        // pesanan, supaya satu status tidak pernah berganti warna antar layar.
        $warnaStatus = [
            'pending' => 'is-kuning',
            'draft' => 'is-abu',
            'paid' => 'is-hijau',
            'processing' => 'is-biru',
            'completed' => 'is-ungu',
            'cancelled' => 'is-merah',
        ];
    @endphp

    <div class="dsb">
        @include('livewire.pages.admin.partials.birthday-card')

        {{-- ================== SAPAAN & AKSI CEPAT ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                {{-- Nama dan lambaian dilekatkan: dengan spasi biasa peramban boleh
                     memotong baris tepat sebelum emoji, sehingga di ponsel 👋
                     jatuh sendirian di baris bawah. Sekarang keduanya selalu
                     berpindah baris bersama. --}}
                <h1 class="dsb-salam">{{ $salam }}, <span class="dsb-salam-nama">{{ $namaDepan }}&nbsp;👋</span></h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">
                        Ringkasan toko &amp; keuangan periode {{ $periodeLabel }}
                        {{-- Penanda kesegaran: angka di halaman ini dihitung saat
                             halaman dimuat, bukan mengalir sendiri. Tanpa jam ini,
                             tab yang dibiarkan terbuka semalaman terbaca seolah
                             masih menunjukkan keadaan sekarang. --}}
                        {{-- Penanda jam dan tombol muat ulang SATU PASANG, dibungkus
                             supaya tidak pernah terpisah baris. Tanpa pembungkus,
                             di ponsel tombolnya jatuh sendirian ke baris bawah
                             dengan sisi kanan kosong. --}}
                        <span class="dsb-segar-grup">
                            <span class="dsb-segar" title="Angka di halaman ini dihitung saat halaman dimuat">
                                <i class="bi bi-clock-history"></i>Data per {{ now()->locale('id')->translatedFormat('H:i') }}
                            </span>
                            {{-- Tombolnya ADA karena jamnya ada: memberi tahu angka
                                 sudah basi tanpa memberi cara menyegarkannya hanya
                                 memindahkan pekerjaan ke admin (cari tombol reload
                                 peramban, dan kehilangan posisi gulir). --}}
                            <button type="button" class="dsb-segar is-tombol" wire:click="muatUlang"
                                wire:loading.attr="disabled" wire:target="muatUlang"
                                title="Hitung ulang semua angka di halaman ini">
                                <span class="dsb-segar-isi" wire:loading.remove.inline-flex wire:target="muatUlang"><i class="bi bi-arrow-clockwise"></i>Muat ulang</span>
                                <span class="dsb-segar-isi" wire:loading.inline-flex wire:target="muatUlang"><span class="dsb-putar is-kecil"></span>Memuat…</span>
                            </button>
                        </span>
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <div class="dsb-aku">
                    <span class="dsb-aku-foto">
                        {{-- Foto hanya dipasang bila BERKASNYA ada. Memasangnya
                             begitu saja meninggalkan gambar rusak (atau, saat
                             disembunyikan lewat onerror, lubang kosong) di
                             kartu identitas — dan sebagian besar admin memang
                             belum pernah mengunggah foto. --}}
                        @if ($fotoAku)
                            <img src="{{ $fotoAku }}" alt="Foto {{ Auth::user()->name }}">
                        @else
                            <span class="dsb-aku-inisial">{{ \Illuminate\Support\Str::substr(Auth::user()->name, 0, 1) }}</span>
                        @endif
                        <span class="dsb-titik {{ Auth::user()->isOnline() ? 'is-daring' : 'is-luring' }}"
                            title="{{ Auth::user()->isOnline() ? 'Online' : 'Offline' }}"></span>
                    </span>
                    <span>
                        <span class="dsb-aku-nama">{{ Auth::user()->name }}</span>
                        <span class="dsb-aku-peran">{{ Auth::user()->role->name ?? 'Pengguna' }}</span>
                    </span>
                </div>

                <a href="{{ route('admin.account.profile') }}" wire:navigate class="dsb-tombol is-utama">
                    <i class="bi bi-person-fill"></i><span>Profil</span>
                </a>

                <button type="button" class="dsb-tombol is-bahaya btn-logout">
                    <i class="bi bi-box-arrow-right"></i><span>Logout</span>
                </button>
            </div>
        </header>

        {{-- ================== AKSI CEPAT ==================
             Dikeluarkan dari kartu sapaan: di sana ia berdesakan dengan kartu
             identitas dan tombol Logout — lima hal berjajar, dan yang paling
             sering diklik justru paling sulit dikenali. Sebagai kartu sendiri,
             tiap aksi punya ubin, nama, dan satu baris keterangan. --}}
        @php
            $aksiCepat = [];

            if (\Illuminate\Support\Facades\Route::has('admin.pesanantoko.create') && auth()->user()->hasPermission('create_pemesanantoko')) {
                $aksiCepat[] = ['#7c3aed', 'bi-bag-plus-fill', 'Pesanan Baru', 'Buat pesanan untuk pembeli', route('admin.pesanantoko.create')];
            }

            if (\Illuminate\Support\Facades\Route::has('admin.spending.create') && auth()->user()->hasPermission('create_spending')) {
                $aksiCepat[] = ['#e11d48', 'bi-receipt', 'Catat Pengeluaran', 'Masuk ke arus kas periode ini', route('admin.spending.create')];
            }
        @endphp

        @if (! empty($aksiCepat))
            <section class="dsb-bagian">
                <div class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #f26522"><i class="bi bi-lightning-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Aksi Cepat</h3>
                                <span class="dsb-kartu-sub">Yang paling sering dikerjakan dari dasbor</span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu-isi">
                        <div class="dsb-aksi">
                            @foreach ($aksiCepat as [$warna, $ikon, $nama, $ket, $tautan])
                                <a class="dsb-aksi-item" href="{{ $tautan }}" wire:navigate style="--c: {{ $warna }}">
                                    <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                                    <span class="dsb-aksi-teks">
                                        <span class="dsb-aksi-nama">{{ $nama }}</span>
                                        <span class="dsb-aksi-ket">{{ $ket }}</span>
                                    </span>
                                    <i class="bi bi-arrow-right dsb-aksi-panah"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Bot Turnitin: kabar bot + kartu yang butuh tangan admin (kuota habis, gagal) --}}
        <livewire:pages.admin.bot-turnitin.panel-bot-turnitin />

        {{-- ================== BUTUH PERHATIAN ==================
             Pekerjaan dan uang yang MENUNGGU tindakan — bukan rekap yang sudah
             terjadi. Semua angkanya sudah lama ada di database dan tidak pernah
             ditampilkan; yang tidak ditampilkan tidak dikerjakan.

             Sengaja diletakkan di ATAS ringkasan keuangan: rekap bulan ini bisa
             dibaca kapan saja, sedangkan pelanggan yang menunggu tidak. --}}
        @php
            $ops = $operasional;
            $rupiahOps = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');

            // Kartu hanya dibuat untuk yang ADA isinya. Deretan kartu bernilai
            // nol yang tidak pernah berubah melatih mata untuk melewatinya,
            // dan saat salah satunya akhirnya berisi, ia ikut terlewat.
            $perhatian = [];

            // Angka besar tiap kartu selalu yang MENUNTUT tindakan; kalimat
            // pendukungnya dijaga ringkas satu baris supaya baris bawah semua
            // kartu berhenti di ketinggian yang sama.
            if ($ops['langganan']['segera'] > 0 || $ops['langganan']['belum_dikabari'] > 0) {
                $perhatian[] = [
                    'warna' => '#d97706',
                    'ikon' => 'bi-hourglass-split',
                    'label' => 'Langganan Habis',
                    'nilai' => $ops['langganan']['segera'],
                    'satuan' => 'akan habis',
                    'pil' => $ops['langganan']['belum_dikabari'] > 0
                        ? $ops['langganan']['belum_dikabari'].' belum dikabari'
                        : null,
                    'ket' => $ops['langganan']['nilai_segera'] > 0
                        ? $rupiahOps($ops['langganan']['nilai_segera']).' bila diperpanjang'
                        : 'Dalam '.\App\Support\RingkasanOperasional::AMBANG_HABIS_HARI.' hari ke depan',
                    'ikon_ket' => 'bi-cash-coin',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.pesanantoko.index')
                        ? route('admin.pesanantoko.index', ['activeTab' => 'habis']) : null,
                ];
            }

            if ($ops['jasa']['manual'] > 0 || $ops['jasa']['dikerjakan'] > 0) {
                // Bot hanya menangani plagiasi Turnitin; cek AI & parafrase
                // selalu tangan admin. Saat antrean manual kosong, angka
                // besarnya diganti yang sedang dikerjakan — "0 menunggu"
                // sebagai judul kartu tidak memberi tahu apa pun.
                $adaAntrean = $ops['jasa']['manual'] > 0;
                $perhatian[] = [
                    'warna' => '#7c3aed',
                    'ikon' => 'bi-file-earmark-text-fill',
                    'label' => $adaAntrean ? 'Jasa Antre Manual' : 'Jasa Dikerjakan',
                    'nilai' => $adaAntrean ? $ops['jasa']['manual'] : $ops['jasa']['dikerjakan'],
                    'satuan' => $adaAntrean ? 'antre manual' : 'berjalan',
                    'pil' => $adaAntrean && $ops['jasa']['manual_terlama']
                        // Di bawah semenit, "terlama 0 detik" tidak memberi tahu
                        // apa pun — yang ingin diketahui adalah "baru masuk".
                        ? ($ops['jasa']['manual_terlama']->diffInMinutes(now()) < 1
                            ? 'Baru masuk'
                            : 'Terlama '.$ops['jasa']['manual_terlama']->locale('id')->diffForHumans(null, true))
                        : null,
                    'ket' => $adaAntrean
                        ? $ops['jasa']['dikerjakan'].' sedang dikerjakan'
                        : $ops['jasa']['bot'].' menunggu giliran bot',
                    'ikon_ket' => 'bi-arrow-repeat',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.pesanantoko.index')
                        ? route('admin.pesanantoko.index') : null,
                ];
            }

            if ($ops['pesanan']['jumlah'] > 0) {
                $perhatian[] = [
                    'warna' => '#0284c7',
                    'ikon' => 'bi-hourglass',
                    'label' => 'Belum Dibayar',
                    'nilai' => $ops['pesanan']['jumlah'],
                    'satuan' => 'pesanan',
                    'pil' => $ops['pesanan']['segera_kedaluwarsa'] > 0
                        ? $ops['pesanan']['segera_kedaluwarsa'].' hangus < 1 jam'
                        : null,
                    'ket' => $rupiahOps($ops['pesanan']['nilai']).' belum masuk',
                    'ikon_ket' => 'bi-wallet2',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.pesanantoko.index')
                        ? route('admin.pesanantoko.index', ['activeTab' => 'neworder']) : null,
                ];
            }

            if ($ops['task']['jumlah'] > 0) {
                $perhatian[] = [
                    'warna' => '#e11d48',
                    'ikon' => 'bi-clipboard-x-fill',
                    'label' => 'Task Telat',
                    'nilai' => $ops['task']['jumlah'],
                    'satuan' => 'task',
                    'pil' => $ops['task']['terlama']
                        ? 'Terlama '.$ops['task']['terlama']->locale('id')->translatedFormat('d M Y')
                        : null,
                    // Mengikuti periode 21–20 yang dipilih, bukan sepanjang masa.
                    'ket' => 'Tenggat periode '.$periodeLabel.', belum selesai',
                    'ikon_ket' => 'bi-calendar-x',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.task-saya.index')
                        ? route('admin.task-saya.index') : null,
                ];
            }

            if (! empty($ops['stok'])) {
                // Dua nama saja lalu "+N lagi": daftar yang dipotong di tengah
                // kata ("Gemi…") tidak bisa dibaca maupun dicari.
                $namaStok = collect($ops['stok'])->take(2)
                    ->map(fn ($s) => $s['produk'].' ('.$s['sisa'].')')->implode(', ');
                $sisaNama = count($ops['stok']) - 2;

                $perhatian[] = [
                    'warna' => '#16a34a',
                    'ikon' => 'bi-box-seam',
                    'label' => 'Stok Menipis',
                    'nilai' => count($ops['stok']),
                    'satuan' => 'produk',
                    'pil' => 'Sisa ≤ '.\App\Support\RingkasanOperasional::AMBANG_STOK.' akun',
                    'ket' => $namaStok.($sisaNama > 0 ? ' +'.$sisaNama.' lagi' : ''),
                    'ikon_ket' => 'bi-box',
                    'url' => \Illuminate\Support\Facades\Route::has('admin.DataAkun.index')
                        ? route('admin.DataAkun.index') : null,
                ];
            }

            /*
             | Lebar kartu dipilih supaya BARIS TERAKHIR selalu penuh.
             |
             | Pada lima kartu seperempat lebar, yang kelima berdiri sendirian
             | dengan tiga perempat baris kosong di sebelahnya — terbaca seperti
             | ada yang gagal dimuat. Lima kartu karena itu dibagi 3 + 2.
             */
            $jumlahPerhatian = count($perhatian);
            $lebarPerhatian = match (true) {
                $jumlahPerhatian >= 6 => 'k-4',
                $jumlahPerhatian === 5 => null,      // ditentukan per kartu di bawah
                $jumlahPerhatian === 4 => 'k-3',
                $jumlahPerhatian === 3 => 'k-4',
                default => 'k-6',
            };
        @endphp

        @if (! empty($perhatian))
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kepala" style="--c: #d97706">
                        <span class="dsb-kepala-ikon"><i class="bi bi-exclamation-diamond-fill"></i></span>
                        {{-- is-hitung-atas: di ponsel pil jumlah naik sebaris dengan
                             kicker supaya tidak memakan satu baris sendiri. --}}
                        <div class="dsb-kepala-teks is-hitung-atas">
                            <span class="dsb-kicker">Butuh Perhatian</span>
                            <h2 class="dsb-judul">Yang Menunggu Dikerjakan</h2>
                            <div class="dsb-chip-deret">
                                <span class="dsb-chip dsb-chip-hitung"><i class="bi bi-list-check"></i>{{ count($perhatian) }} hal</span>
                                <span class="dsb-chip is-samar">Kartu hanya muncul saat memang ada isinya</span>
                            </div>
                        </div>
                    </div>

                    @foreach ($perhatian as $p)
                        {{-- Tautannya MENUTUPI kartu, bukan kartunya yang jadi
                             <a>: dengan begitu markupnya satu jalur saja. Versi
                             bercabang (<a> bila ada tautan, <article> bila
                             tidak) menaruh @if di dalam tag, dan tanda
                             lebih-besar sesudah @endif membuat Livewire
                             melewati penanda morph-nya. --}}
                        {{-- Teksnya dibungkus satu kolom flex sendiri: dengan
                             begitu keterangan di bawah selalu jatuh ke DASAR
                             kartu, entah kartunya punya pil atau tidak — dan
                             baris bawah semua kartu berhenti di satu garis. --}}
                        @php
                            // Lima kartu: tiga di baris pertama, dua di baris kedua.
                            $lebarKartu = $lebarPerhatian ?? ($loop->index < 3 ? 'k-4' : 'k-6');

                            // Di tablet semua kartu berpasangan dua-dua. Bila
                            // jumlahnya ganjil, yang terakhir melebar penuh —
                            // kalau tidak, ia berdiri setengah lebar dengan
                            // separuh baris kosong di sebelahnya.
                            $penuhTablet = $loop->last && $jumlahPerhatian % 2 === 1;
                        @endphp
                        <article class="dsb-tugas {{ $lebarKartu }} {{ $penuhTablet ? 'is-penuh-sedang' : '' }}"
                            style="--c: {{ $p['warna'] }}">
                            <span class="dsb-ikon"><i class="bi {{ $p['ikon'] }}"></i></span>

                            <span class="dsb-tugas-isi">
                                <span class="dsb-stat-label">{{ $p['label'] }}</span>
                                <span class="dsb-tugas-angka">
                                    {{ $p['nilai'] }}<span class="dsb-tugas-satuan">{{ $p['satuan'] }}</span>
                                </span>

                                @if ($p['pil'])
                                    <span class="dsb-pil">{{ $p['pil'] }}</span>
                                @endif

                                <span class="dsb-stat-ket">
                                    <i class="bi {{ $p['ikon_ket'] }}"></i><span>{{ $p['ket'] }}</span>
                                </span>
                            </span>

                            @if ($p['url'])
                                <a class="dsb-tutup-kartu" href="{{ $p['url'] }}" wire:navigate>
                                    <span class="visually-hidden">Buka {{ $p['label'] }}</span>
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================== LAYANAN & PERPANJANGAN ==================
             Dua sisi mutu layanan: siapa yang harus dihubungi supaya tidak
             hilang, dan seberapa cepat pekerjaan yang masuk diselesaikan.

             Daftarnya sengaja memuat NAMA dan tombol hubungi. Angka "38 akan
             habis" tidak bisa ditindaklanjuti — yang menentukan perpanjangan
             adalah siapa orangnya dan nomor mana yang dihubungi. --}}
        @if (! empty($langgananSegera) || $kecepatanJasa['selesai'] > 0)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kepala" style="--c: #0ea5e9">
                        <span class="dsb-kepala-ikon"><i class="bi bi-arrow-repeat"></i></span>
                        <div class="dsb-kepala-teks">
                            <span class="dsb-kicker">Layanan</span>
                            <h2 class="dsb-judul">Perpanjangan &amp; Kecepatan</h2>
                            <div class="dsb-chip-deret">
                                <span class="dsb-chip is-samar">Daftar perpanjangan selalu keadaan sekarang, bukan periode yang dipilih</span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu k-7">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-hourglass-split"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Perlu Dihubungi</h3>
                                    <span class="dsb-kartu-sub">Yang sudah habis lebih dulu, lalu yang paling dekat</span>
                                </div>
                            </div>
                            @if (\Illuminate\Support\Facades\Route::has('admin.pesanantoko.index'))
                                <a href="{{ route('admin.pesanantoko.index', ['activeTab' => 'habis']) }}" wire:navigate class="dsb-tautan">
                                    <span>Semua</span><i class="bi bi-arrow-right"></i>
                                </a>
                            @endif
                        </div>

                        <div class="dsb-daftar">
                            @forelse ($langgananSegera as $lg)
                                @php
                                    $sisa = (int) $lg['sisa'];
                                    $habis = $sisa < 0;
                                    $pesanWa = 'Halo '.$lg['nama'].', langganan '.$lg['produk'].' Anda '
                                        .($habis ? 'sudah berakhir pada ' : 'akan berakhir pada ')
                                        .($lg['tanggal']?->locale('id')->translatedFormat('d F Y') ?? 'waktu dekat')
                                        .'. Apakah ingin diperpanjang? Terima kasih 🙏';
                                    $tautanWa = \App\Support\TautanWa::kirim($lg['no_hp'], $pesanWa);
                                @endphp
                                <div class="dsb-baris">
                                    <span class="dsb-avatar" style="--c: {{ $habis ? '#e11d48' : '#d97706' }}">{{ \Illuminate\Support\Str::substr($lg['nama'], 0, 1) }}</span>
                                    <span class="dsb-baris-isi">
                                        <span class="dsb-baris-judul">{{ $lg['nama'] }}</span>
                                        <span class="dsb-baris-meta">
                                            <span>{{ $lg['produk'] }}</span>
                                            <span class="dsb-pisah">•</span>
                                            <span>{{ $lg['tanggal']?->locale('id')->translatedFormat('d M Y') ?? 'tanpa tanggal' }}</span>
                                        </span>
                                    </span>
                                    <span class="dsb-baris-kanan is-mendatar">
                                        <span class="dsb-lencana {{ $habis ? 'is-luring' : 'is-kuning' }}">
                                            {{ $habis ? abs($sisa).' HARI LEWAT' : ($sisa === 0 ? 'HABIS HARI INI' : $sisa.' HARI LAGI') }}
                                        </span>
                                        {{-- Tombolnya tetap tampil walau nomornya kosong,
                                             tetapi mati: menyembunyikannya membuat baris
                                             tanpa nomor terlihat sudah beres. --}}
                                        @if ($tautanWa)
                                            <a class="dsb-baris-aksi" href="{{ $tautanWa }}" target="_blank" rel="noopener">
                                                <i class="bi bi-whatsapp"></i>Hubungi
                                            </a>
                                        @else
                                            <span class="dsb-baris-aksi is-mati" title="Pelanggan ini tidak punya nomor WhatsApp">
                                                <i class="bi bi-whatsapp"></i>Tanpa nomor
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            @empty
                                <div class="dsb-kosong">
                                    <span class="dsb-kosong-ikon"><i class="bi bi-check2-circle"></i></span>
                                    <p class="dsb-kosong-judul">Tidak ada yang perlu dihubungi</p>
                                    <p class="dsb-kosong-ket">Tidak ada langganan yang habis dalam {{ \App\Support\RingkasanOperasional::AMBANG_HABIS_HARI }} hari ke depan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="dsb-kartu k-5">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #0ea5e9"><i class="bi bi-stopwatch-fill"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Kecepatan Jasa</h3>
                                    <span class="dsb-kartu-sub">Dihitung sejak naskah masuk • {{ $periodeLabel }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            @if ($kecepatanJasa['selesai'] > 0)
                                @php
                                    $tepat = (float) $kecepatanJasa['tepat_persen'];
                                    $warnaTepat = $tepat >= 90 ? '#16a34a' : ($tepat >= 70 ? '#d97706' : '#e11d48');
                                @endphp
                                <div class="dsb-data">
                                    <div class="dsb-data-baris">
                                        <span class="dsb-data-label"><i class="bi bi-speedometer2"></i>Rata-rata selesai</span>
                                        <span class="dsb-data-nilai">{{ \App\Support\KecepatanJasa::labelJam($kecepatanJasa['rata_jam']) }}</span>
                                    </div>
                                    <div class="dsb-data-baris">
                                        <span class="dsb-data-label"><i class="bi bi-check2-circle"></i>Selesai dalam sehari</span>
                                        <span class="dsb-data-nilai" style="color: {{ $warnaTepat }}">{{ number_format($tepat, 1, ',', '.') }}%</span>
                                    </div>
                                    <div class="dsb-data-baris">
                                        <span class="dsb-data-label"><i class="bi bi-exclamation-circle"></i>Lewat sehari</span>
                                        <span class="dsb-data-nilai">{{ $kecepatanJasa['lewat_sehari'] }} dari {{ $kecepatanJasa['selesai'] }}</span>
                                    </div>
                                    <div class="dsb-data-baris">
                                        <span class="dsb-data-label"><i class="bi bi-hourglass-bottom"></i>Paling lama</span>
                                        <span class="dsb-data-nilai">{{ \App\Support\KecepatanJasa::labelJam($kecepatanJasa['terlama_jam']) }}</span>
                                    </div>
                                </div>

                                <span class="dsb-kemajuan" style="--c: {{ $warnaTepat }}"
                                    title="{{ number_format($tepat, 1, ',', '.') }}% selesai dalam sehari">
                                    <span style="width: {{ min(max($tepat, 0), 100) }}%"></span>
                                </span>
                                <p class="dsb-kartu-sub" style="margin-top: 10px;">
                                    Batas wajar {{ \App\Support\KecepatanJasa::AMBANG_JAM }} jam sejak naskah diunggah pelanggan.
                                </p>
                            @else
                                <div class="dsb-kosong">
                                    <span class="dsb-kosong-ikon"><i class="bi bi-stopwatch"></i></span>
                                    <p class="dsb-kosong-judul">Belum ada pengecekan selesai</p>
                                    <p class="dsb-kosong-ket">Angkanya muncul setelah ada pengecekan yang diselesaikan pada periode ini.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== RINGKASAN KEUANGAN ==================
             Satu rak 12 kolom memuat kepala bagian DAN kelima kartunya, jadi
             tepi kiri judul, kartu besar, dan kartu kecil benar-benar segaris. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak" wire:loading.class="dsb-sedang-muat" wire:target="pilihPeriode">
                <div class="dsb-kepala" style="--c: #16a34a">
                    <span class="dsb-kepala-ikon"><i class="bi bi-graph-up-arrow"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Ringkasan</span>
                        <h2 class="dsb-judul">Uang Masuk &amp; Keluar</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-calendar-range"></i>{{ $periodeLabel }}</span>
                            @if ($periodeBerjalan)
                                <span class="dsb-chip is-samar">Periode dihitung tanggal 21 sampai 20</span>
                            @else
                                <span class="dsb-chip is-samar">Periode lampau — bukan angka berjalan</span>
                            @endif
                            {{-- Satu-satunya tanda bahwa permintaannya sedang
                                 berjalan. Tanpa ini, panah periode terasa mati
                                 pada sambungan lambat dan ditekan dua kali. --}}
                            <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="pilihPeriode">
                                <span class="dsb-putar is-kecil"></span>Menghitung ulang…
                            </span>
                        </div>
                    </div>

                    {{-- Pemilih periode. Tombol mundur/maju, bukan kotak pilih
                         berisi daftar bulan: yang hampir selalu dicari adalah
                         "periode sebelum ini", dan itu harus satu klik. --}}
                    <div class="dsb-geser">
                        <button type="button" class="dsb-geser-btn" wire:click="pilihPeriode({{ $mundur + 1 }})"
                            wire:loading.attr="disabled" wire:target="pilihPeriode"
                            @disabled($mundur >= \App\Livewire\Pages\Admin\Dashboard::MUNDUR_MAKS)
                            title="Periode sebelumnya"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="dsb-geser-btn" wire:click="pilihPeriode({{ $mundur - 1 }})"
                            wire:loading.attr="disabled" wire:target="pilihPeriode"
                            @disabled($periodeBerjalan)
                            title="Periode berikutnya"><i class="bi bi-chevron-right"></i></button>
                        @unless ($periodeBerjalan)
                            <button type="button" class="dsb-geser-btn is-kini" wire:click="pilihPeriode(0)"
                                wire:loading.attr="disabled" wire:target="pilihPeriode">Kembali ke sekarang</button>
                        @endunless
                    </div>

                    <a href="{{ route('admin.cashflow.index') }}" wire:navigate class="dsb-tautan">
                        <span>Buka Cash Flow</span><i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <article class="dsb-stat is-utama k-6" style="--c: #16a34a">
                    <span class="dsb-ikon"><i class="bi bi-calendar-day-fill"></i></span>
                    <p class="dsb-stat-label">
                        Pendapatan Hari Ini
                        {{-- Kartu ini TIDAK ikut pemilih periode. Saat periode
                             digeser ke belakang, empat kartu di sekitarnya
                             berubah dan kartu ini tetap hari ini; tanpa
                             penanda, angkanya terbaca sebagai angka periode
                             lampau yang keliru. --}}
                        @unless ($periodeBerjalan)
                            <span class="dsb-tanda-kini"><i class="bi bi-pin-angle-fill"></i>Selalu hari ini</span>
                        @endunless
                    </p>
                    <p class="dsb-stat-nilai is-hijau">Rp {{ $pendapatanHariIni }}</p>
                    {{-- Kode unik HARI INI pindah ke kartu ini, bukan lagi
                         menumpang kartu periode: di sana satu kartu memuat
                         tiga dasar waktu sekaligus (total periode, angka hari
                         ini, dan pembanding kemarin). --}}
                    <span class="dsb-pil"><i class="bi bi-upc-scan"></i>Kode unik: <b>Rp {{ $kodeUnikHariIni }}</b></span>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-wallet2"></i>
                        <span>Pesanan dibayar {{ now()->translatedFormat('d M Y') }} (paid/proses/selesai)</span>
                    </p>
                    <x-banding-harian :data="$bandingPendapatan" />
                </article>

                <article class="dsb-stat is-utama k-6" style="--c: {{ $saldoIsNegatif ? '#dc2626' : '#7c3aed' }}">
                    <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                    <p class="dsb-stat-label">Saldo Bersih</p>
                    <p class="dsb-stat-nilai {{ $saldoIsNegatif ? 'is-merah' : '' }}">Rp {{ $saldoBersih }}</p>
                    <x-banding-periode :data="$bandingPeriode['saldo']" :rentang="$bandingPeriode['label_sebelumnya']" />
                    <p class="dsb-stat-ket">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Pemasukan − Pengeluaran • {{ $periodeLabel }}</span>
                    </p>
                </article>

                <article class="dsb-stat k-4" style="--c: #059669">
                    <span class="dsb-ikon"><i class="bi bi-graph-up-arrow"></i></span>
                    <p class="dsb-stat-label">Total Pemasukan</p>
                    <p class="dsb-stat-nilai">Rp {{ $totalPemasukan }}</p>
                    <x-banding-periode :data="$bandingPeriode['pemasukan']" :rentang="$bandingPeriode['label_sebelumnya']" />
                    <p class="dsb-stat-ket"><i class="bi bi-wallet2"></i><span>Cashflow • {{ $periodeLabel }}</span></p>
                </article>

                <article class="dsb-stat k-4" style="--c: #e11d48">
                    <span class="dsb-ikon"><i class="bi bi-graph-down-arrow"></i></span>
                    <p class="dsb-stat-label">Total Pengeluaran</p>
                    <p class="dsb-stat-nilai">Rp {{ $totalPengeluaran }}</p>
                    {{-- biaya: naiknya pengeluaran BUKAN kabar baik, jadi warnanya dibalik. --}}
                    <x-banding-periode :data="$bandingPeriode['pengeluaran']" :rentang="$bandingPeriode['label_sebelumnya']" biaya />
                    <p class="dsb-stat-ket"><i class="bi bi-wallet2"></i><span>Cashflow • {{ $periodeLabel }}</span></p>
                </article>

                <article class="dsb-stat k-4" style="--c: #0284c7">
                    <span class="dsb-ikon"><i class="bi bi-upc-scan"></i></span>
                    <p class="dsb-stat-label">Total Kode Unik</p>
                    <p class="dsb-stat-nilai">Rp {{ $totalKodeUnik }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-calendar-check"></i><span>Periode {{ $periodeLabel }}</span></p>
                </article>
            </div>
        </section>


        {{-- ================== LABA & PENJUALAN ==================
             Bagian ini menjawab dua hal yang selama ini tidak dijawab di mana
             pun: berapa yang benar-benar tersisa dari omset, dan berapa KALI
             penjualan itu terjadi.

             Modal sudah lama tercatat (satu baris expense per order item),
             tetapi di kartu "Total Pengeluaran" ia melebur dengan gaji, iklan,
             dan listrik — jadi omset besar dan omset untung terlihat sama.

             Omset dan modal di sini dihitung dari HIMPUNAN PESANAN yang sama,
             bukan dari tanggal baris buku kasnya, supaya kedua kartu mustahil
             saling bertentangan. --}}
        @php
            $labaKini = $laba['kini'];
            $jualKini = $penjualan['kini'];
            $rupiahLaba = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
            $labaNegatif = $labaKini['laba_kotor'] < 0;
        @endphp

        <section class="dsb-bagian">
            <div class="dsb-rak" wire:loading.class="dsb-sedang-muat" wire:target="pilihPeriode">
                <div class="dsb-kepala" style="--c: #0f766e">
                    <span class="dsb-kepala-ikon"><i class="bi bi-piggy-bank-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Untung Rugi</span>
                        <h2 class="dsb-judul">Laba &amp; Penjualan</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-calendar-range"></i>{{ $periodeLabel }}</span>
                            <span class="dsb-chip is-samar">Omset dan modal dihitung dari pesanan yang sama</span>
                        </div>
                    </div>
                </div>

                <article class="dsb-stat is-utama k-6" style="--c: {{ $labaNegatif ? '#dc2626' : '#0f766e' }}">
                    <span class="dsb-ikon"><i class="bi bi-cash-coin"></i></span>
                    <p class="dsb-stat-label">Laba Kotor</p>
                    <p class="dsb-stat-nilai {{ $labaNegatif ? 'is-merah' : '' }}">{{ $rupiahLaba($labaKini['laba_kotor']) }}</p>
                    {{-- Margin sebagai pil, bukan kartu sendiri: ia hanya punya
                         arti berdampingan dengan laba yang dipersenkannya. --}}
                    <span class="dsb-pil">
                        <i class="bi bi-percent"></i>Margin:
                        <b>{{ $labaKini['margin'] === null ? 'belum ada omset' : number_format($labaKini['margin'], 1, ',', '.').'%' }}</b>
                    </span>
                    <x-banding-periode :data="$laba['laba_kotor']" :rentang="$laba['label_sebelumnya']" />
                    <p class="dsb-stat-ket">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Omset {{ $rupiahLaba($labaKini['omset']) }} − modal {{ $rupiahLaba($labaKini['modal']) }}</span>
                    </p>
                </article>

                <article class="dsb-stat is-utama k-6" style="--c: #7c3aed">
                    <span class="dsb-ikon"><i class="bi bi-people-fill"></i></span>
                    <p class="dsb-stat-label">Pelanggan Periode Ini</p>
                    <p class="dsb-stat-nilai">{{ $jualKini['pelanggan'] }}<span class="dsb-stat-satuan">orang</span></p>
                    {{-- Pelanggan yang KEMBALI dipisah karena itulah nadi
                         bisnis langganan: ia tidak memerlukan biaya iklan, dan
                         turunnya angka itu adalah peringatan paling awal yang
                         bisa didapat. --}}
                    <span class="dsb-pil"><i class="bi bi-arrow-repeat"></i>Kembali: <b>{{ $jualKini['kembali'] }}</b></span>
                    <x-banding-periode :data="$penjualan['kembali']" :rentang="$penjualan['label_sebelumnya']"
                        prefiks="" sufiks=" pelanggan kembali" label="di periode lalu" />
                    <p class="dsb-stat-ket">
                        <i class="bi bi-person-plus"></i>
                        <span>{{ $jualKini['baru'] }} pelanggan baru @if ($jualKini['tanpa_akun'] > 0) • {{ $jualKini['tanpa_akun'] }} pesanan tanpa akun @endif</span>
                    </p>
                </article>

                <article class="dsb-stat k-4" style="--c: #0284c7">
                    <span class="dsb-ikon"><i class="bi bi-bag-check-fill"></i></span>
                    <p class="dsb-stat-label">Pesanan Dibayar</p>
                    <p class="dsb-stat-nilai">{{ $jualKini['pesanan'] }}<span class="dsb-stat-satuan">pesanan</span></p>
                    {{-- Rata-rata nilai pesanan menempel di kartu jumlahnya:
                         keduanya bersama menjawab kenapa omset berubah —
                         pembelinya bertambah, atau belanjanya membesar. --}}
                    <span class="dsb-pil"><i class="bi bi-calculator"></i>Rata-rata: <b>{{ $rupiahLaba($jualKini['rata']) }}</b></span>
                    <x-banding-periode :data="$penjualan['pesanan']" :rentang="$penjualan['label_sebelumnya']"
                        prefiks="" sufiks=" pesanan" />
                    <p class="dsb-stat-ket"><i class="bi bi-wallet2"></i><span>Omset {{ $rupiahLaba($jualKini['nilai']) }}</span></p>
                </article>

                <article class="dsb-stat k-4" style="--c: #d97706">
                    <span class="dsb-ikon"><i class="bi bi-box-seam-fill"></i></span>
                    <p class="dsb-stat-label">Modal Terpakai</p>
                    <p class="dsb-stat-nilai">{{ $rupiahLaba($labaKini['modal']) }}</p>
                    {{-- biaya: modal yang naik bukan kabar baik dengan
                         sendirinya — ia baik hanya bila labanya ikut naik. --}}
                    <x-banding-periode :data="$laba['modal']" :rentang="$laba['label_sebelumnya']" biaya />
                    <p class="dsb-stat-ket"><i class="bi bi-upc"></i><span>Harga beli akun &amp; biaya pengecekan</span></p>
                </article>

                <article class="dsb-stat k-4" style="--c: #e11d48">
                    <span class="dsb-ikon"><i class="bi bi-receipt"></i></span>
                    <p class="dsb-stat-label">Biaya Operasional</p>
                    <p class="dsb-stat-nilai">{{ $rupiahLaba($labaKini['biaya_operasional']) }}</p>
                    <x-banding-periode :data="$laba['biaya_operasional']" :rentang="$laba['label_sebelumnya']" biaya />
                    <p class="dsb-stat-ket"><i class="bi bi-list-ul"></i><span>Pengeluaran di luar modal (gaji, iklan, lain-lain)</span></p>
                </article>
            </div>
        </section>

        {{-- ================== PEMAKAIAN PROMO ==================
             Menjawab pertanyaan yang selama ini hanya bisa dijawab dengan
             membuka satu per satu halaman promo: pada periode ini, promo mana
             yang benar-benar dipakai pembeli, berapa kali, dan berapa rupiah
             yang dilepas karenanya. --}}
        @php
            $daftarPromo = [
                ['flash_sale', 'Flash Sale', 'bi-lightning-charge-fill', '#f26522'],
                ['kode_promo', 'Kode Promo', 'bi-ticket-perforated-fill', '#7c3aed'],
                ['referral', 'Kode Rujukan', 'bi-people-fill', '#0284c7'],
            ];

            // Promo otomatis hanya ditampilkan bila memang pernah terpakai —
            // kartu bernilai nol yang tidak pernah berubah hanya menyita ruang.
            if (($promoDipakai['auto_promo']['jumlah'] ?? 0) > 0) {
                $daftarPromo[] = ['auto_promo', 'Promo Otomatis', 'bi-magic', '#16a34a'];
            }

            $lebarPromo = count($daftarPromo) === 4 ? 'k-3' : 'k-4';
            $totalPakai = collect($daftarPromo)->sum(fn ($p) => $promoDipakai[$p[0]]['jumlah'] ?? 0);
            $rupiahPromo = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        @endphp

        <section class="dsb-bagian">
            <div class="dsb-rak" wire:loading.class="dsb-sedang-muat" wire:target="pilihPeriode">
                <div class="dsb-kepala" style="--c: #f26522">
                    <span class="dsb-kepala-ikon"><i class="bi bi-tags-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Promo</span>
                        <h2 class="dsb-judul">Promo yang Terpakai</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-check2-circle"></i>{{ $totalPakai }}× dipakai</span>
                            <span class="dsb-chip"><i class="bi bi-cash-coin"></i>{{ $rupiahPromo($promoDipakai['total_nilai'] ?? 0) }} dilepas</span>
                            <span class="dsb-chip is-samar">Hanya pesanan yang dibayar, periode {{ $periodeLabel }}</span>
                        </div>
                    </div>
                    @if (auth()->user()->hasPermission('view_promo'))
                        <a href="{{ route('admin.promo.index') }}" wire:navigate class="dsb-tautan">
                            <span>Kelola Promo</span><i class="bi bi-arrow-right"></i>
                        </a>
                    @endif
                </div>

                @foreach ($daftarPromo as [$kunci, $nama, $ikon, $warna])
                    @php $pakai = $promoDipakai[$kunci] ?? ['jumlah' => 0, 'nilai' => 0]; @endphp
                    <article class="dsb-stat {{ $lebarPromo }}" style="--c: {{ $warna }}">
                        <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                        <p class="dsb-stat-label">{{ $nama }}</p>
                        <p class="dsb-stat-nilai">{{ $pakai['jumlah'] }}<span style="font-size:.9rem; font-weight:700; color:#6b7280; margin-left:5px;">kali</span></p>
                        <p class="dsb-stat-ket">
                            <i class="bi bi-tag"></i>
                            <span>{{ $pakai['jumlah'] > 0 ? $rupiahPromo($pakai['nilai']) . ' diskon diberikan' : 'Belum dipakai periode ini' }}</span>
                        </p>
                    </article>
                @endforeach

                {{-- Rincian per NAMA promo. "Flash sale 12 kali" tidak bisa
                     ditindaklanjuti; yang menentukan promo mana yang layak
                     diulang adalah nama promonya.

                     Hanya tampil bila ADA yang terpakai: saat kosong, ketiga
                     kartu nol di atasnya sudah mengatakan hal yang sama, dan
                     kartu kosong kedua hanya memanjangkan halaman. --}}
                @if (! empty($promoRincian))
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #f26522"><i class="bi bi-list-ol"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Promo Mana yang Dipakai</h3>
                                <span class="dsb-kartu-sub">Terbanyak lebih dulu • periode {{ $periodeLabel }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-daftar">
                        @php
                            $rupaPromo = [
                                'flash_sale' => ['Flash Sale', 'bi-lightning-charge-fill', '#f26522'],
                                'kode_promo' => ['Kode Promo', 'bi-ticket-perforated-fill', '#7c3aed'],
                                'auto_promo' => ['Promo Otomatis', 'bi-magic', '#16a34a'],
                                'referral' => ['Kode Rujukan', 'bi-people-fill', '#0284c7'],
                            ];
                        @endphp

                        @foreach ($promoRincian as $baris)
                            @php [$jenisNama, $jenisIkon, $jenisWarna] = $rupaPromo[$baris['tipe']] ?? ['Promo', 'bi-tag-fill', '#64748b']; @endphp
                            <div class="dsb-baris">
                                <span class="dsb-avatar" style="--c: {{ $jenisWarna }}"><i class="bi {{ $jenisIkon }}"></i></span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $baris['nama'] }}</span>
                                    <span class="dsb-baris-meta">
                                        <span class="dsb-lencana" style="background: color-mix(in srgb, {{ $jenisWarna }} 12%, #fff); color: {{ $jenisWarna }};">{{ $jenisNama }}</span>
                                        @if ($baris['kode'] && $baris['tipe'] !== 'referral')
                                            <span class="dsb-pisah">•</span>
                                            <span>{{ $baris['kode'] }}</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-baris-nilai">{{ $baris['jumlah'] }}× dipakai</span>
                                    <span class="dsb-baris-meta">{{ $rupiahPromo($baris['nilai']) }} diskon</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </section>

        {{-- ================== GRAFIK & TREN ==================
             Keempat grafik dalam SATU bagian. Sebelumnya terbelah jadi dua
             bagian berurutan dengan dua kepala — dan dua kepala berturut-turut
             terbaca seperti dua topik berbeda, padahal semuanya menjawab
             pertanyaan yang sama: uangnya dari mana, ke mana, dan bergerak
             seperti apa.

             Susunannya 8+4 lalu 7+5 — dua baris, masing-masing satu grafik
             lebar berpasangan dengan satu ringkasan sempit. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak" wire:loading.class="dsb-sedang-muat" wire:target="pilihPeriode">
                <div class="dsb-kepala" style="--c: #0284c7">
                    <span class="dsb-kepala-ikon"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Analisis</span>
                        <h2 class="dsb-judul">Grafik &amp; Tren</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-calendar3"></i>{{ now()->year }}</span>
                            <span class="dsb-chip"><i class="bi bi-calendar-range"></i>{{ $periodeLabel }}</span>
                            <span class="dsb-chip is-samar">Arus setahun, cara bayar, tren harian, dan produk terlaris</span>
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu k-8">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-bar-chart-line-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Grafik Keuangan</h3>
                                <span class="dsb-kartu-sub">Pemasukan vs pengeluaran sepanjang {{ now()->year }}</span>
                            </div>
                        </div>
                        <span class="dsb-lencana is-hijau">{{ now()->year }}</span>
                    </div>
                    <div class="dsb-kartu-isi">
                        {{-- Penanda "sedang memuat" dihapus sendiri oleh Apex
                             saat grafiknya digambar. Tanpa ini, kartunya kosong
                             beberapa ratus milidetik dan terbaca seperti tidak
                             ada datanya. --}}
                        <div class="dsb-grafik">
                            <div class="dsb-memuat"><span class="dsb-putar"></span> Menggambar grafik…</div>
                            <div id="finance-chart"></div>
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu k-4">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-credit-card-2-front-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Metode Pembayaran</h3>
                                <span class="dsb-kartu-sub">Sebaran pesanan per metode</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        @if (empty($counts))
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-credit-card"></i></span>
                                <p class="dsb-kosong-judul">Belum ada data</p>
                                <p class="dsb-kosong-ket">Belum ada pesanan dengan metode pembayaran.</p>
                            </div>
                        @else
                            <div class="dsb-grafik is-donat">
                                <div class="dsb-memuat"><span class="dsb-putar"></span> Menggambar grafik…</div>
                                <div id="chart-visitors-profile"></div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Grafik tahunan menjawab "bulan mana yang ramai"; yang
                     harian menjawab "minggu ini bagaimana" — pertanyaan yang
                     justru ditanyakan tiap hari. --}}
                <div class="dsb-kartu k-7">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-activity"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pemasukan Harian</h3>
                                <span class="dsb-kartu-sub">Pesanan dibayar per hari • hari tanpa pesanan digambar nol</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        <div class="dsb-grafik">
                            <div class="dsb-memuat"><span class="dsb-putar"></span> Menggambar grafik…</div>
                            <div id="grafik-harian"></div>
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu k-5">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-trophy-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Paling Laku</h3>
                                <span class="dsb-kartu-sub">Lima teratas menurut nilai</span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-daftar">
                        @forelse ($produkTerlaris as $i => $laris)
                            <div class="dsb-baris">
                                <span class="dsb-avatar" style="--c: {{ ['#d97706','#7c3aed','#0284c7','#16a34a','#e11d48'][$i] ?? '#64748b' }}">{{ $i + 1 }}</span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $laris['produk'] }}</span>
                                    <span class="dsb-baris-meta">{{ $laris['jumlah'] }}× terjual</span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-baris-nilai">Rp {{ number_format($laris['nilai'], 0, ',', '.') }}</span>
                                </span>
                            </div>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-trophy"></i></span>
                                <p class="dsb-kosong-judul">Belum ada penjualan</p>
                                <p class="dsb-kosong-ket">Belum ada pesanan dibayar pada periode ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== PESANAN & PELANGGAN TERBARU ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #7c3aed">
                    <span class="dsb-kepala-ikon"><i class="bi bi-lightning-charge-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Terbaru</span>
                        <h2 class="dsb-judul">Aktivitas Terakhir</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-bag-check"></i>{{ $recentOrders->count() }} pesanan terakhir</span>
                            <span class="dsb-chip is-samar">Klik barisnya untuk membuka</span>
                        </div>
                    </div>
                </div>

                {{-- Pesanan terbaru --}}
                <div class="dsb-kartu k-4">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-bag-check-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pesanan Terbaru</h3>
                                <span class="dsb-kartu-sub">Toko</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.pesanantoko.index') }}" wire:navigate class="dsb-tautan">
                            <span>Semua</span><i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    {{-- Daftar baris, bukan tabel: di HP, tabel lima kolom selalu
                         berakhir tergeser keluar layar — dan kolom yang tidak
                         terlihat sama saja dengan kolom yang tidak ada. --}}
                    <div class="dsb-daftar">
                        @forelse ($recentOrders as $order)
                            <a href="{{ route('admin.pesanantoko.detail', $order->id) }}" wire:navigate class="dsb-baris">
                                <span class="dsb-avatar" style="--c: #7c3aed">{{ \Illuminate\Support\Str::substr($order->customer->nama ?? 'U', 0, 1) }}</span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $order->order_number }}</span>
                                    <span class="dsb-baris-meta">
                                        <span>{{ $order->customer->nama ?? 'Umum' }}</span>
                                        <span class="dsb-pisah">•</span>
                                        {{-- locale('id'): APP_LOCALE=en, tanpa ini tertulis "2 hours ago". --}}
                                        <span>{{ $order->created_at?->locale('id')->diffForHumans() }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-baris-nilai">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                    <span class="dsb-lencana {{ $warnaStatus[$order->status] ?? 'is-abu' }}">{{ strtoupper($order->status) }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-bag"></i></span>
                                <p class="dsb-kosong-judul">Belum ada pesanan</p>
                                <p class="dsb-kosong-ket">Pesanan baru akan muncul di sini begitu masuk.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pelanggan terbaru --}}
                <div class="dsb-kartu k-4">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-people-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pelanggan Terbaru</h3>
                                <span class="dsb-kartu-sub">Pendaftar terakhir</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.customer.index') }}" wire:navigate class="dsb-tautan">
                            <span>Semua</span><i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="dsb-daftar">
                        @forelse ($recentCustomers as $customer)
                            <a href="{{ route('admin.customer.edit', $customer->id) }}" wire:navigate class="dsb-baris">
                                <span class="dsb-avatar" style="--c: #16a34a">{{ \Illuminate\Support\Str::substr($customer->nama ?? 'U', 0, 1) }}</span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $customer->nama ?? 'Umum' }}</span>
                                    <span class="dsb-baris-meta">
                                        <span><i class="bi bi-telephone"></i> {{ $customer->no_hp }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ (int) $customer->point }} poin</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-lencana {{ $customer->status_member === 'active' ? 'is-hijau' : 'is-tamu' }}">
                                        {{ $customer->status_member === 'active' ? 'MEMBER' : 'NON-MEMBER' }}
                                    </span>
                                    <span class="dsb-baris-meta">{{ $customer->created_at?->locale('id')->diffForHumans() }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-person-plus"></i></span>
                                <p class="dsb-kosong-judul">Belum ada pelanggan</p>
                                <p class="dsb-kosong-ket">Pelanggan baru muncul di sini setelah pesanan pertamanya.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pengeluaran terbaru.

                     "Catat Pengeluaran" sudah jadi aksi cepat di kepala
                     halaman, tetapi hasil catatannya tidak pernah terlihat
                     lagi dari dasbor — sehingga pengeluaran yang tercatat dua
                     kali baru ketahuan saat membuka layar Cash Flow. --}}
                <div class="dsb-kartu k-4">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #e11d48"><i class="bi bi-receipt"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pengeluaran Terbaru</h3>
                                <span class="dsb-kartu-sub">Catatan terakhir</span>
                            </div>
                        </div>
                        @if (auth()->user()->hasPermission('view_spending'))
                            <a href="{{ route('admin.spending.index') }}" wire:navigate class="dsb-tautan">
                                <span>Semua</span><i class="bi bi-arrow-right"></i>
                            </a>
                        @endif
                    </div>

                    <div class="dsb-daftar">
                        @forelse ($pengeluaranTerbaru as $keluar)
                            <div class="dsb-baris">
                                <span class="dsb-avatar" style="--c: #e11d48"><i class="bi bi-receipt"></i></span>
                                <span class="dsb-baris-isi">
                                    {{-- Dipotong oleh CSS saja, bukan juga oleh Str::limit:
                                         dua pemotongan berturut-turut menghasilkan
                                         "Operasional Konsumsi ke Surakarta……", dan yang
                                         CSS lakukan sudah menyesuaikan lebar kartunya.
                                         Teks penuhnya tetap bisa dibaca lewat title. --}}
                                    <span class="dsb-baris-judul" title="{{ $keluar->deskripsi ?: 'Tanpa keterangan' }}">{{ $keluar->deskripsi ?: 'Tanpa keterangan' }}</span>
                                    <span class="dsb-baris-meta">
                                        <span>{{ $keluar->penginput->name ?? 'Tanpa penginput' }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $keluar->tanggal_transaksi?->locale('id')->translatedFormat('d M Y') }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-baris-nilai">Rp {{ number_format((float) $keluar->nominal, 0, ',', '.') }}</span>
                                    {{-- Pengeluaran 'pending' BELUM masuk buku kas
                                         (lihat SyncCashFlowAction::shouldRecord), jadi
                                         statusnya ikut ditampilkan — tanpa itu, angka
                                         di sini tidak cocok dengan Total Pengeluaran
                                         di atas dan tidak ada yang menjelaskan kenapa. --}}
                                    <span class="dsb-lencana {{ $keluar->status === 'pending' ? 'is-kuning' : 'is-hijau' }}">
                                        {{ strtoupper($keluar->status ?? '-') }}
                                    </span>
                                </span>
                            </div>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-receipt"></i></span>
                                <p class="dsb-kosong-judul">Belum ada pengeluaran</p>
                                <p class="dsb-kosong-ket">Catatan pengeluaran terakhir akan muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== AGENDA & TIM ==================
             Dua kartu yang sama-sama tentang ORANG, bukan uang: kegiatan yang
             menunggu dan siapa yang sedang daring. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #d97706">
                    <span class="dsb-kepala-ikon"><i class="bi bi-people-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Harian</span>
                        <h2 class="dsb-judul">Agenda &amp; Tim</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip is-samar">Kegiatan yang menunggu Anda, dan siapa yang sedang daring</span>
                        </div>
                    </div>
                </div>

                <div class="k-7">@include('livewire.pages.admin.partials.agenda-saya')</div>
                <div class="k-5">@livewire('pages.admin.online-users')</div>
            </div>
        </section>

    </div>
</div>

<!--================== PUSHER REAL TIME ONLINE/OFFLINE ==================-->
@push('scripts')
<script>
    /* Memperbarui SATU baris di daftar "Karyawan Online" saat status berubah,
       tanpa menunggu polling 10 detik berikutnya.

       Penanda yang dicari adalah kelasnya (.dsb-lencana), bukan "span pertama
       di dalam baris" seperti versi lama — baris sekarang punya beberapa span
       (foto, nama, keterangan), dan yang pertama adalah fotonya. */
    Echo.channel('online-users')
        .listen('.UserOnlineStatusChanged', (e) => {
            const pengguna = e.user;
            const baris = document.getElementById(`user-${pengguna.id}`);
            const wadah = document.getElementById('online-users-container');
            if (!wadah) return;

            const lencana = (daring) =>
                `<span class="dsb-lencana ${daring ? 'is-hijau' : 'is-luring'}">` +
                `<span class="dsb-bulat"></span>${daring ? 'ONLINE' : 'OFFLINE'}</span>`;

            const keterangan = (u) => u.online
                ? 'Sedang daring'
                : `Terakhir terlihat ${u.last_seen_at || 'tidak diketahui'}`;

            if (baris) {
                const tanda = baris.querySelector('.dsb-lencana');
                if (tanda) tanda.outerHTML = lencana(pengguna.online);

                const ket = baris.querySelector('.dsb-baris-isi .dsb-baris-meta');
                if (ket) ket.textContent = keterangan(pengguna);
                return;
            }

            const barisBaru = document.createElement('div');
            barisBaru.className = 'dsb-baris';
            barisBaru.id = `user-${pengguna.id}`;
            barisBaru.innerHTML =
                `<span class="dsb-avatar" style="--c: #7c3aed">${(pengguna.name || 'U').charAt(0)}</span>` +
                `<span class="dsb-baris-isi">` +
                `<span class="dsb-baris-judul"></span>` +
                `<span class="dsb-baris-meta"></span>` +
                `</span>` +
                `<span class="dsb-baris-kanan">${lencana(pengguna.online)}</span>`;

            // Nama & keterangan dipasang sebagai TEKS, bukan disisipkan sebagai
            // HTML: nama pengguna diisi orang, dan tidak boleh bisa membawa tag.
            barisBaru.querySelector('.dsb-baris-judul').textContent = pengguna.name || '-';
            barisBaru.querySelector('.dsb-baris-meta').textContent = keterangan(pengguna);

            const kosong = wadah.querySelector('.dsb-kosong');
            if (kosong) kosong.remove();
            wadah.appendChild(barisBaru);
        });
</script>
@endpush
<!--================== END PUSHER REAL TIME ONLINE/OFFLINE ==================-->

<!--================== GRAFIK PEMASUKAN & PENGELUARAN ==================-->
@push('scripts')
<script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>
@endpush

<script>
    // 1. Kita bungkus logika grafik ke dalam sebuah fungsi khusus
    function renderFinanceChart() {
        const chartElement = document.querySelector("#finance-chart");

        // Jika elemen grafik tidak ada di halaman ini, hentikan proses
        if (!chartElement) return;

        // AMAN DARI AUTO-FORMATTER — data dari cashflow (income vs expense)
        const dataPemasukan = @json($dataGrafikPemasukan);
        const dataPengeluaran = @json($dataGrafikPengeluaran);

        const chartOptions = {
            series: [{
                    name: 'Pemasukan',
                    data: dataPemasukan
                },
                {
                    name: 'Pengeluaran',
                    data: dataPengeluaran
                }
            ],
            chart: {
                type: 'area',
                height: 380,
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit'
            },
            colors: ['#10b981', '#f43f5e'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                labels: {
                    style: {
                        fontWeight: 600,
                        colors: '#64748b'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748b'
                    },
                    formatter: function(value) {
                        if (value === 0) return 0;
                        return "Rp " + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function(value) {
                        return "Rp " + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            }
        };

        // Bersihkan sisa grafik sebelumnya agar tidak menumpuk saat kembali ke halaman ini
        chartElement.innerHTML = '';
        const chart = new ApexCharts(chartElement, chartOptions);
        chart.render();
    }

    // 2. PANGGIL FUNGSI BERDASARKAN EVENT

    // Eksekusi saat halaman direfresh normal (F5)
    document.addEventListener('DOMContentLoaded', renderFinanceChart);

    // Eksekusi saat berpindah halaman via SPA Livewire (wire:navigate)
    document.addEventListener('livewire:navigated', renderFinanceChart);
</script>

<script>
    document.addEventListener('DOMContentLoaded', renderFinanceChart);
    document.addEventListener('livewire:navigated', () => {
        setTimeout(renderFinanceChart, 100);
    });

    document.addEventListener('livewire:updated', () => {
        if (document.querySelector("#finance-chart")) {
            renderFinanceChart();
        }
    });
</script>
<!--================== END GRAFIK PEMASUKAN & PENGELUARAN ==================-->

<!--================== GRAFIK PEMASUKAN HARIAN ==================-->
<script>
    function gambarGrafikHarian() {
        const wadah = document.querySelector('#grafik-harian');
        if (!wadah || typeof ApexCharts === 'undefined') return;

        const tanggal = @json($pemasukanHarian['tanggal']);
        const nilai = @json($pemasukanHarian['nilai']);

        const rupiah = (v) => 'Rp ' + Number(v || 0).toLocaleString('id-ID');

        // Berapa tanggal yang boleh dicetak dihitung dari LEBAR wadahnya, bukan
        // dari jumlah harinya. tickAmount milik Apex hanya perkiraan: pada
        // periode 31 hari di kolom yang sempit, labelnya tetap dicetak rapat
        // sampai terbaca menyambung ("21 Agt23 Agt25 Agt").
        const LEBAR_LABEL = 72; // "21 Agt" berikut jarak napas kiri-kanannya
        const lebar = wadah.clientWidth || 640;
        window.lebarGrafikHarian = lebar;
        const muat = Math.max(2, Math.floor(lebar / LEBAR_LABEL));
        const langkah = Math.max(1, Math.ceil(tanggal.length / muat));

        const pilihan = {
            series: [{ name: 'Pemasukan', data: nilai }],
            chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
            colors: ['#16a34a'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .35, opacityTo: .04, stops: [0, 90, 100] } },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: tanggal,
                labels: {
                    style: { fontWeight: 600, colors: '#94a3b8' }, rotate: 0,
                    hideOverlappingLabels: false,
                    // Tanggal di antaranya DIKOSONGKAN, bukan dibuang dari
                    // kategorinya: titik datanya tetap utuh dan judul tooltip
                    // tetap menyebut tanggal yang ditunjuk.
                    formatter: (v, _t, opsi) => {
                        const i = (opsi && typeof opsi.i === 'number') ? opsi.i : tanggal.indexOf(v);
                        return i % langkah === 0 ? v : '';
                    },
                },
                axisBorder: { show: false }, axisTicks: { show: false },
                tooltip: { enabled: false },
            },
            yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: (v) => v >= 1000000 ? (v / 1000000).toFixed(1) + ' jt' : (v / 1000).toFixed(0) + ' rb' } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                theme: 'light',
                // Judulnya dibaca langsung dari daftar tanggal, bukan dari
                // label sumbunya — label yang dikosongkan tidak boleh membuat
                // tooltipnya kehilangan tanggal.
                x: { formatter: (v, opsi) => (opsi && tanggal[opsi.dataPointIndex]) || v },
                y: { formatter: rupiah },
            },
            noData: { text: 'Belum ada pemasukan pada periode ini.' },
        };

        wadah.innerHTML = '';
        new ApexCharts(wadah, pilihan).render();
    }

    document.addEventListener('DOMContentLoaded', () => setTimeout(gambarGrafikHarian, 60));
    document.addEventListener('livewire:navigated', () => setTimeout(gambarGrafikHarian, 120));
    // Pergantian periode mengganti datanya lewat Livewire, jadi grafiknya
    // digambar ulang setelah komponen diperbarui.
    document.addEventListener('livewire:updated', () => setTimeout(gambarGrafikHarian, 60));

    // Jarak antar tanggal bergantung pada lebar wadahnya, jadi grafiknya
    // digambar ulang saat lebarnya berubah jauh (sidebar dilipat, layar
    // diputar). Ambang 60px supaya bilah alamat peramban ponsel yang
    // muncul-hilang tidak memicu gambar ulang terus-menerus.
    window.addEventListener('resize', () => {
        const wadah = document.querySelector('#grafik-harian');
        if (!wadah || Math.abs(wadah.clientWidth - (window.lebarGrafikHarian || 0)) < 60) return;
        window.lebarGrafikHarian = wadah.clientWidth;
        clearTimeout(window.tungguGrafikHarian);
        window.tungguGrafikHarian = setTimeout(gambarGrafikHarian, 200);
    });
</script>
<!--================== END GRAFIK PEMASUKAN HARIAN ==================-->

{{-- Salam TIDAK lagi dirakit di peramban.

     Skrip lamanya membaca jam dari komputer admin (new Date().getHours()),
     jadi laptop yang zona waktunya meleset menyapa "Selamat Malam" pada pukul
     sembilan pagi. Sekarang dirakit di server, lihat $salam di atas. --}}

<!--================== SWEET ALERT LOGOUT ==================-->
<script>
    if (!window.logoutListenerAdded) {
        window.logoutListenerAdded = true;

        const glossyConfig = {
            background: 'rgba(255, 255, 255, 0.8)',
            backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: {
                popup: 'swal-glossy-popup',
                confirmButton: 'btn-glossy-confirm',
                cancelButton: 'btn-glossy-cancel',
                title: 'swal-glossy-title'
            },
            buttonsStyling: false
        };

        document.addEventListener('click', function(event) {
            const logoutBtn = event.target.closest('.btn-logout');

            if (logoutBtn) {
                event.preventDefault();

                Swal.fire({
                    title: 'Yakin ingin keluar?',
                    text: "Anda harus login kembali untuk masuk ke sistem.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Logout!',
                    cancelButtonText: 'Batal',
                    ...glossyConfig
                }).then((result) => {
                    if (result.isConfirmed) {
                        const livewireComponentId = logoutBtn.closest('[wire\\:id]').getAttribute('wire:id');
                        Livewire.find(livewireComponentId).call('logout');
                    }
                });
            }
        });
    }
</script>
<!--================== END SWEEAT ALERT LOGOUT ==================-->

<!--================== GRAFIK VISITORS PROFILE ==================-->
<script>
    function renderVisitorsChart() {
        const chartElement = document.querySelector("#chart-visitors-profile");

        // Jangan render jika elemen tidak ada
        if (!chartElement) return;

        // Validasi Data: Pastikan data dari PHP sudah terisi
        const visitorCounts = @json($counts ?? []);
        const visitorCountries = @json($countries ?? []);

        if (visitorCounts.length === 0) return;

        const optionsVisitors = {
            series: visitorCounts,
            chart: {
                type: 'donut',
                height: 350,
                animations: {
                    enabled: true
                }
            },
            labels: visitorCountries,
            colors: ['#7c3aed', '#3b82f6', '#10b981', '#f43f5e'],
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: true
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%'
                    }
                }
            }
        };

        chartElement.innerHTML = '';
        const chart = new ApexCharts(chartElement, optionsVisitors);
        chart.render();
    }

    // Gunakan MutationObserver agar lebih akurat
    const observer = new MutationObserver((mutations, obs) => {
        const chartElement = document.querySelector("#chart-visitors-profile");
        if (chartElement) {
            renderVisitorsChart();
            obs.disconnect(); // Hentikan pemantauan setelah grafik tampil
        }
    });

    // Jalankan inisialisasi
    document.addEventListener('livewire:navigated', () => {
        // Beri sedikit waktu untuk rendering Livewire selesai (50ms)
        setTimeout(renderVisitorsChart, 50);

        // Mulai memantau jika grafik belum muncul (fallback)
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    // Backup: Tetap jalankan saat DOM content siap
    document.addEventListener('DOMContentLoaded', () => setTimeout(renderVisitorsChart, 50));
</script>
