@section('title')
Dashboard Orcha Journey || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon.

         Dasbor Orcha dulu memakai dialeknya sendiri (Bootstrap card + kelas
         orcha-*), sehingga berpindah ke sini terasa seperti berpindah aplikasi
         padahal admin-nya sama. Sekarang kerangkanya sama persis dengan dasbor
         lemon: rak 12 kolom, kepala bagian, kartu angka, daftar, keadaan
         kosong. Warna Orcha tetap dipakai lewat token --orc-*. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $perlu = [
            ['pendaftaran_baru', 'Pendaftaran baru', 'bi-clipboard-check', 'admin.orcha.pendaftaran', '#4f46e5', 'pendaftaran menunggu diproses'],
            ['penyewaan_baru', 'Sewa kendaraan baru', 'bi-truck', 'admin.orcha.penyewaan', '#7c3aed', 'pemesanan sewa masuk'],
            ['pembayaran_menunggu', 'Bukti bayar menunggu', 'bi-cash-coin', 'admin.orcha.pembayaran', '#047857', 'bukti transfer perlu dicek'],
            ['pembatalan_diajukan', 'Pembatalan diajukan', 'bi-x-circle', 'admin.orcha.pembatalan', '#b91c1c', 'menunggu keputusan admin'],
            ['pesan_belum_dibaca', 'Pesan belum dibaca', 'bi-inbox', 'admin.orcha.pesan', '#b45309', 'dari formulir kontak'],
        ];
        $jumlahPekerjaan = collect($perlu)->sum(fn ($baris) => (int) ($perluDitindak[$baris[0]] ?? 0));

    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Dashboard Orcha Journey</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Data diambil langsung dari server Orcha — tidak ada salinan yang disimpan di lemon.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                {{-- wire:target menyebut NAMA METODE, bukan $refresh: dengan
                     $refresh penanda memuatnya ikut menyala untuk permintaan
                     lain yang kebetulan lewat. --}}
                <button type="button" wire:click="segarkan" wire:loading.attr="disabled" wire:target="segarkan"
                    class="dsb-tombol is-lembut" style="--ikon: #2b7fb8">
                    <i class="bi bi-arrow-repeat" wire:loading.remove wire:target="segarkan"></i>
                    <span class="dsb-putar" wire:loading wire:target="segarkan"></span>
                    <span>Muat ulang</span>
                </button>
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #d97706">
                    <i class="bi bi-box-arrow-left"></i><span>Kembali ke lemon</span>
                </a>
            </div>
        </header>

        @if ($galat)
            {{-- Orcha tak terjangkau: disebut apa adanya, bukan angka nol yang
                 terbaca seolah memang tidak ada pekerjaan. --}}
            <section class="dsb-bagian">
                <div class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon" style="color: #b45309; background: #fffbeb; border-color: #fde68a">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </span>
                            <p class="dsb-kosong-judul">Data Orcha belum bisa ditampilkan</p>
                            <p class="dsb-kosong-ket">{{ $galat }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== PERLU DITINDAK ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #d97706">
                    <span class="dsb-kepala-ikon"><i class="bi bi-exclamation-diamond-fill"></i></span>
                    <div class="dsb-kepala-teks is-hitung-atas">
                        <span class="dsb-kicker">Butuh Perhatian</span>
                        <h2 class="dsb-judul">Yang Menunggu Dikerjakan</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip dsb-chip-hitung"><i class="bi bi-list-check"></i>{{ $jumlahPekerjaan }} hal</span>
                            @if (! $galat && $jumlahPekerjaan === 0)
                                <span class="dsb-chip is-samar">Tidak ada yang menunggu ditindak saat ini</span>
                            @endif
                        </div>
                    </div>
                </div>

                @foreach ($perlu as $i => [$kunci, $label, $ikon, $rute, $warna, $ket])
                    @php
                        $nilai = (int) ($perluDitindak[$kunci] ?? 0);
                        // Lima kartu: tiga di baris pertama, dua di baris kedua —
                        // susunan yang sama dengan "Butuh Perhatian" di lemon.
                        $lebar = $i < 3 ? 'k-4' : 'k-6';
                    @endphp
                    <article class="dsb-tugas {{ $lebar }} {{ $loop->last ? 'is-penuh-sedang' : '' }}" style="--c: {{ $warna }}">
                        <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>

                        <span class="dsb-tugas-isi">
                            <span class="dsb-stat-label">{{ $label }}</span>
                            <span class="dsb-tugas-angka">{{ $nilai }}</span>
                            <span class="dsb-stat-ket"><i class="bi {{ $ikon }}"></i><span>{{ $ket }}</span></span>
                        </span>

                        {{-- Tautannya MENUTUPI kartu, bukan kartunya yang jadi <a>:
                             markupnya satu jalur, jadi penanda morph Livewire aman. --}}
                        <a class="dsb-tutup-kartu" href="{{ route($rute) }}" wire:navigate>
                            <span class="visually-hidden">Buka {{ $label }}</span>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ================== UANG ================== --}}
        @if ($uang !== [])
            @php
                // Yang masih potensi ikut dihitung: modalnya sama-sama kosong,
                // dan pekerjaan yang menunggu admin sama persis.
                $belumUtuh = (int) ($uang['belum_lengkap'] ?? 0) + (int) ($uang['potensi_belum_lengkap'] ?? 0);
            @endphp

            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kepala" style="--c: #0ea5e9">
                        <span class="dsb-kepala-ikon"><i class="bi bi-cash-stack"></i></span>
                        <div class="dsb-kepala-teks">
                            <span class="dsb-kicker">Uang</span>
                            <h2 class="dsb-judul">Omzet &amp; Keuntungan</h2>
                            <div class="dsb-chip-deret">
                                <span class="dsb-chip is-samar">Dibaca dari laporan Keuntungan Paket, bukan dihitung ulang</span>
                            </div>
                        </div>
                    </div>

                    @foreach ([
                        ['omzet_teks', 'Omzet masuk', 'bi-graph-up-arrow', '#2b7fb8', 'sudah diterima'],
                        ['modal_teks', 'Modal keluar', 'bi-box-seam', '#64748b', 'biaya paket'],
                        ['keuntungan_teks', 'Keuntungan', 'bi-piggy-bank', '#1a8a52', 'omzet dikurangi modal'],
                        ['potensi_omzet_teks', 'Potensi omzet', 'bi-hourglass-split', '#d97706', 'belum lunas'],
                    ] as [$kunci, $label, $ikon, $warna, $ket])
                        <article class="dsb-stat k-3" style="--c: {{ $warna }}">
                            <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                            <span class="dsb-stat-label">{{ $label }}</span>
                            <span class="dsb-stat-nilai">{{ $uang[$kunci] ?? 'Rp 0' }}</span>
                            <span class="dsb-stat-ket"><i class="bi {{ $ikon }}"></i><span>{{ $ket }}</span></span>
                        </article>
                    @endforeach

                    @if ($belumUtuh > 0)
                        {{-- Yang belum bisa dihitung disebut apa adanya: laporan yang
                             diam soal paket tanpa modal membuat keuntungannya terbaca
                             lebih kecil daripada yang sebenarnya, tanpa satu pun tanda. --}}
                        <div class="dsb-kartu k-12" style="--c: #d97706">
                            <div class="dsb-kartu-isi orc-das-catatan">
                                <span class="dsb-ikon is-kecil"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                <span>
                                    <strong>{{ $belumUtuh }} pendaftaran belum ikut terhitung keuntungannya</strong>
                                    — paketnya belum diisi harga modal, jadi angka di atas lebih kecil daripada
                                    yang sebenarnya.
                                    <a href="{{ route('admin.orcha.keuntungan') }}" wire:navigate class="orc-das-tautan">Lengkapi modalnya</a>.
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ================== GRAFIK ================== --}}
        @if ($uangPerBulan !== [])
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #2b7fb8"><i class="bi bi-graph-up-arrow"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Omzet &amp; keuntungan per bulan</h3>
                                    <span class="dsb-kartu-sub">Hanya pendaftaran yang sudah lunas dan paketnya punya harga modal</span>
                                </div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="dsb-grafik">
                                <div id="orcha-grafik-uang" wire:ignore></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Dimuat LANGSUNG di sini, bukan lewat @push('scripts').

                 Layout templateindex — yang dipakai seluruh halaman Orcha —
                 tidak punya @stack('scripts'), jadi apa pun yang di-push ke
                 sana dibuang diam-diam. Pustakanya tidak pernah termuat dan
                 grafiknya tidak pernah tergambar, tanpa satu pun galat di
                 layar maupun di konsol. --}}
            <script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>

            <script>
                (function () {
                    const bulan = @json(collect($uangPerBulan)->pluck('bulan_label')->all());
                    const omzet = @json(collect($uangPerBulan)->map(fn ($b) => (int) ($b['omzet'] ?? 0))->all());
                    const untung = @json(collect($uangPerBulan)->map(fn ($b) => (int) ($b['keuntungan'] ?? 0))->all());

                    const rupiah = (n) => 'Rp ' + Math.round(n).toString()
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                    function gambarGrafikUangOrcha() {
                        const kotak = document.querySelector('#orcha-grafik-uang');

                        if (!kotak || typeof ApexCharts === 'undefined') return;

                        /*
                         | Satu kotak digambar SEKALI saja.
                         |
                         | Fungsi ini sengaja dipasang di tiga tempat — dipanggil
                         | langsung, lalu DOMContentLoaded, lalu livewire:navigated —
                         | karena tidak satu pun dari ketiganya bisa diandalkan
                         | sendirian: skripnya inline sehingga DOMContentLoaded belum
                         | tentu sudah lewat, sedangkan halaman yang dibuka lewat
                         | wire:navigate tidak menembakkan DOMContentLoaded sama sekali.
                         |
                         | Tanpa penjaga ini ketiganya benar-benar berjalan pada muat
                         | pertama: kotaknya dikosongkan lalu digambar ulang dua kali,
                         | dan yang terlihat garisnya naik, jatuh ke nol, naik lagi,
                         | jatuh lagi. Animasinya yang berulang, bukan datanya yang
                         | berubah — jadi tidak ada satu pun galat yang menandainya.
                         |
                         | Penandanya menempel pada ELEMEN, bukan variabel di closure:
                         | kunjungan berikutnya lewat wire:navigate membawa elemen baru
                         | yang belum bertanda, jadi grafiknya tetap tergambar lagi.
                         */
                        if (kotak.dataset.orchaTergambar === '1') return;

                        // Sisa kanvas dari kunjungan sebelumnya, kalau ada.
                        kotak.innerHTML = '';

                        new ApexCharts(kotak, {
                            series: [
                                { name: 'Omzet', data: omzet },
                                { name: 'Keuntungan', data: untung },
                            ],
                            chart: { type: 'area', height: 340, toolbar: { show: false }, fontFamily: 'inherit' },
                            colors: ['#2b7fb8', '#1a8a52'],
                            fill: {
                                type: 'gradient',
                                gradient: { shadeIntensity: 1, opacityFrom: .4, opacityTo: .05, stops: [0, 90, 100] },
                            },
                            stroke: { curve: 'smooth', width: 3 },
                            dataLabels: { enabled: false },
                            legend: { position: 'top', horizontalAlign: 'right' },
                            xaxis: {
                                categories: bulan,
                                labels: { style: { fontWeight: 600, colors: '#64748b' } },
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                            },
                            yaxis: { labels: { formatter: rupiah, style: { colors: '#94a3b8' } } },
                            tooltip: { y: { formatter: rupiah } },
                            grid: { borderColor: '#eef2f6', strokeDashArray: 4 },
                        }).render();

                        kotak.dataset.orchaTergambar = '1';
                    }

                    document.addEventListener('DOMContentLoaded', gambarGrafikUangOrcha);
                    document.addEventListener('livewire:navigated', gambarGrafikUangOrcha);
                    gambarGrafikUangOrcha();
                })();
            </script>
        @endif

        {{-- ================== DAFTAR TERBARU ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #4f46e5"><i class="bi bi-clipboard-check"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pendaftaran terbaru</h3>
                                <span class="dsb-kartu-sub">Lima yang paling akhir masuk</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.orcha.pendaftaran') }}" wire:navigate class="dsb-tautan">Lihat semua</a>
                    </div>

                    <div class="dsb-daftar">
                        @forelse ($pendaftaranTerbaru as $baris)
                            <div class="dsb-baris">
                                <span class="dsb-avatar" style="--c: #4f46e5">{{ \Illuminate\Support\Str::substr($baris['nama'], 0, 1) }}</span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $baris['nama'] }}</span>
                                    <span class="dsb-baris-meta">
                                        <span>{{ $baris['kode'] }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $baris['paket']['nama'] }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $baris['jumlah_peserta'] }} peserta</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    {{-- Cip status milik Orcha dipertahankan: peta warnanya
                                         SENGAJA dua (pendaftaran & penyewaan). Kata yang sama
                                         berbeda artinya — "baru" kuning di pendaftaran, biru di
                                         sewa — jadi menyatukannya membuat satu status kehilangan
                                         warnanya diam-diam. --}}
                                    <span class="orcha-cip-status-daftar status-{{ $baris['status'] }}">{{ $baris['status_label'] }}</span>
                                </span>
                            </div>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-clipboard"></i></span>
                                <p class="dsb-kosong-judul">Belum ada pendaftaran</p>
                                <p class="dsb-kosong-ket">Pendaftaran yang masuk akan muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-truck"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Sewa kendaraan terbaru</h3>
                                <span class="dsb-kartu-sub">Lima pemesanan terakhir</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate class="dsb-tautan">Lihat semua</a>
                    </div>

                    <div class="dsb-daftar">
                        @forelse ($penyewaanTerbaru as $baris)
                            <div class="dsb-baris">
                                <span class="dsb-avatar" style="--c: #7c3aed">{{ \Illuminate\Support\Str::substr($baris['nama'], 0, 1) }}</span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $baris['nama'] }}</span>
                                    <span class="dsb-baris-meta">
                                        <span>{{ $baris['kode'] }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $baris['kendaraan']['nama'] }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $baris['durasi_label'] }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="orcha-cip-status-sewa" data-status="{{ $baris['status'] }}">{{ $baris['status_label'] }}</span>
                                </span>
                            </div>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-truck"></i></span>
                                <p class="dsb-kosong-judul">Belum ada pemesanan sewa</p>
                                <p class="dsb-kosong-ket">Pemesanan kendaraan akan muncul di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        /* Catatan "belum terhitung": satu baris ikon + kalimat. */
        .orc-das-catatan { display: flex; align-items: flex-start; gap: 12px; font-size: .88rem; color: #475569; }
        .orc-das-catatan strong { color: var(--dsb-tinta, #1c1f26); }
        /* Tautan DI DALAM kalimat: .dsb-tautan bergaya tombol, dan tombol di
           tengah kalimat memutus bacaannya. */
        .orc-das-tautan { color: #b45309; font-weight: 700; text-decoration: underline; }
        .orc-das-tautan:hover { color: #92400e; }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
