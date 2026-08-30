@section('title')
Dashboard Orcha Journey || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

{{-- Gaya khusus halaman dasbor.

     Ditulis di sini, bukan di partials/gaya.blade.php, karena
     .orcha-kartu-tindakan dan .orcha-ikon-* juga dipakai empat halaman detail
     (pendaftaran, penyewaan, pembatalan, pesan). Menyunting aturan bersamanya
     akan mengubah keempatnya sekaligus, padahal yang kepenuhan warna cuma
     dasbor — di halaman detail kartunya cuma satu, dan warnanya justru
     membantu. --}}
<style>
    /* ---------- Judul bagian ----------

       Sebelumnya tiga baris kartu berdiri tanpa satu kata pun yang menerangkan
       kenapa mereka dipisah. Yang membaca harus menyimpulkan sendiri bahwa
       lima kartu teratas adalah pekerjaan dan empat di bawahnya uang. */
    .orc-das-judul {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #94a3b8;
        margin: 0 0 .7rem;
    }

    /* ---------- Kartu tindakan ----------

       Ikonnya tidak lagi kotak bergradien pekat berhuruf putih.

       Lima gradien jenuh berjajar membuat mata tidak punya alasan berhenti di
       salah satunya — persis kebalikan dari maksudnya. Sekarang latarnya tipis
       dan warnanya pindah ke glifnya: kelima kartu tetap bisa dibedakan sekilas
       tanpa satu pun yang berteriak. */
    .orc-das-tindakan .orcha-ikon {
        box-shadow: none;
        border-radius: 12px;
    }

    .orc-das-tindakan .orcha-ikon-daftar { background: #eef2ff; color: #4f46e5; }
    .orc-das-tindakan .orcha-ikon-sewa   { background: #f3f0ff; color: #7c3aed; }
    .orc-das-tindakan .orcha-ikon-bayar  { background: #e8f7f0; color: #047857; }
    .orc-das-tindakan .orcha-ikon-batal  { background: #fdeeee; color: #b91c1c; }
    .orc-das-tindakan .orcha-ikon-pesan  { background: #fef4e6; color: #b45309; }

    /* Pita oranye di tepi kiri dibuang.

       Maksudnya menandai kartu yang ada isinya — tetapi pada hari sibuk
       KELIMA kartu ada isinya, sehingga kelimanya berpita dan tandanya tidak
       lagi menandai apa pun. Yang membedakan sudah cukup: angkanya bukan nol
       dan ikonnya berwarna, sedangkan yang kosong meredup sendiri. */
    .orc-das-tindakan .orcha-kartu-tindakan.ada { border-left: 0 !important; }

    /* Latar bergradien milik kartu tindakan di halaman detail tidak dipakai di
       sini: di dasbor ia berdampingan dengan kartu putih lain, dan dua putih
       yang beda tipis terbaca sebagai cacat cetak, bukan sebagai kelompok. */
    .orc-das-tindakan .orcha-kartu-tindakan {
        background: #fff !important;
        border: 1px solid #e9eef5 !important;
    }

    /* ---------- Kartu uang ----------

       Empat pita berlainan warna dibuang. Keempat angka ini dibaca berurutan
       sebagai satu hitungan — omzet dikurangi modal jadi keuntungan — jadi
       memberi tiap kartu warna sendiri memutus bacaan yang justru berurutan.
       Yang tetap berwarna hanya keuntungannya, karena itu jawaban akhirnya. */
    .orc-das-uang .orcha-kartu-uang { border-left: 0 !important; }

    /* ---------- Bilah isi etalase ---------- */
    .orc-das-bilah {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem 2rem;
    }

    .orc-das-bilah-item {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .3rem .1rem;
        color: var(--orc-tinta);
        text-decoration: none;
        border-radius: 8px;
        transition: color .14s ease;
    }

    .orc-das-bilah-item[href]:hover { color: var(--orc-primer); }

    .orc-das-bilah-item > i {
        display: flex;
        align-items: center;
        line-height: 1;
        font-size: .95rem;
        color: #94a3b8;
    }

    .orc-das-bilah-item[href]:hover > i { color: var(--orc-primer); }

    .orc-das-bilah-angka { font-weight: 800; font-size: 1.05rem; line-height: 1; }
    .orc-das-bilah-label { font-size: .82rem; color: #64748b; }

    @media (max-width: 575.98px) {
        /* Di layar sempit bilahnya jadi dua lajur, bukan satu deret panjang
           yang memaksa menggulir ke samping. */
        .orc-das-bilah { gap: .75rem 1rem; }
        .orc-das-bilah-item { flex: 0 0 calc(50% - .5rem); }
    }
</style>

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Dashboard Orcha Journey',
            'keterangan' => 'Data diambil langsung dari server Orcha. Tidak ada salinan yang disimpan di lemon.',
        ])

        {{-- Yang perlu ditindak dulu, baru angka-angka lain. Admin membuka
             halaman ini untuk tahu "apa yang harus saya kerjakan sekarang". --}}
        @php
            // Warna mengikuti makna kartunya, bukan satu warna untuk semua:
            // lima kartu senada membuat mata tidak punya alasan berhenti di
            // salah satunya, padahal itu justru gunanya.
            $perlu = [
                ['pendaftaran_baru', 'Pendaftaran baru', 'bi-clipboard-check', 'admin.orcha.pendaftaran', 'orcha-ikon-daftar'],
                ['penyewaan_baru', 'Sewa kendaraan baru', 'bi-truck', 'admin.orcha.penyewaan', 'orcha-ikon-sewa'],
                ['pembayaran_menunggu', 'Bukti bayar menunggu', 'bi-cash-coin', 'admin.orcha.pembayaran', 'orcha-ikon-bayar'],
                ['pembatalan_diajukan', 'Pembatalan diajukan', 'bi-x-circle', 'admin.orcha.pembatalan', 'orcha-ikon-batal'],
                ['pesan_belum_dibaca', 'Pesan belum dibaca', 'bi-inbox', 'admin.orcha.pesan', 'orcha-ikon-pesan'],
            ];
            $adaPekerjaan = collect($perlu)->sum(fn ($baris) => (int) ($perluDitindak[$baris[0]] ?? 0)) > 0;
        @endphp

        <h2 class="orc-das-judul">Perlu ditindak</h2>

        <div class="row g-3 mb-4 orc-das-tindakan">
            @foreach ($perlu as [$kunci, $label, $ikon, $rute, $rupa])
                @php $nilai = (int) ($perluDitindak[$kunci] ?? 0); @endphp
                <div class="col-6 col-lg-3 col-xl">
                    <a href="{{ route($rute) }}" wire:navigate class="text-decoration-none">
                        <div class="card orcha-kartu orcha-kartu-tindakan h-100 {{ $nilai > 0 ? 'ada' : 'kosong' }}">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <span class="orcha-ikon {{ $rupa }}">
                                    <i class="bi {{ $ikon }}"></i>
                                </span>
                                <div>
                                    <div class="orcha-angka">{{ $nilai }}</div>
                                    <div class="text-muted small">{{ $label }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if (! $galat && ! $adaPekerjaan)
            <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 mb-4">
                <i class="bi bi-check-circle-fill fs-4"></i>
                <span>Tidak ada yang menunggu ditindak di Orcha saat ini.</span>
            </div>
        @endif

        {{-- ============ TREN ENAM BULAN ============

             Angka tunggal menjawab "berapa hari ini"; yang tidak dijawabnya
             adalah "sedang naik atau turun" — dan itu justru pertanyaan yang
             membuat orang membuka dashboard dua kali sehari.

             Dulu digambar SVG sendiri, dengan alasan pustaka grafik tidak bisa
             dipakai karena aset Vite tidak ikut ter-deploy. Alasannya keliru:
             ApexCharts bukan hasil bundel Vite — berkasnya ada di public/mazer,
             terlacak git, dan ikut terkirim ke server. Yang tidak ikut adalah
             public/build. --}}
        @if ($tren !== [])
            <div class="card orcha-kartu mb-4">
                <div class="card-body p-3 p-lg-4">
                    <h6 class="fw-bold mb-1 orcha-judul-ikon" style="font-size:.95rem">
                        <i class="bi bi-bar-chart-line text-primary"></i> Tren enam bulan terakhir
                    </h6>
                    <div class="text-muted mb-2" style="font-size:.8rem">
                        Jumlah pendaftaran open trip dan pemesanan sewa kendaraan tiap bulan.
                    </div>

                    <div id="orcha-grafik-tren" wire:ignore></div>
                </div>
            </div>

            <script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>

            <script>
                (function () {
                    const bulan = @json(collect($tren)->pluck('label')->all());
                    const daftar = @json(collect($tren)->map(fn ($b) => (int) ($b['pendaftaran'] ?? 0))->all());
                    const sewa = @json(collect($tren)->map(fn ($b) => (int) ($b['penyewaan'] ?? 0))->all());

                    function gambarGrafikTrenOrcha() {
                        const kotak = document.querySelector('#orcha-grafik-tren');

                        if (!kotak || typeof ApexCharts === 'undefined') return;

                        // Sisa kanvas sebelumnya dibuang: halaman ini dibuka lewat
                        // wire:navigate, dan tanpa ini tiap kunjungan menumpuk satu
                        // grafik lagi di kotak yang sama.
                        kotak.innerHTML = '';

                        new ApexCharts(kotak, {
                            series: [
                                { name: 'Pendaftaran', data: daftar },
                                { name: 'Sewa kendaraan', data: sewa },
                            ],
                            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                            colors: ['#3b82f6', '#8b5cf6'],
                            plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
                            dataLabels: { enabled: false },
                            legend: { position: 'top', horizontalAlign: 'right' },
                            xaxis: {
                                categories: bulan,
                                labels: { style: { fontWeight: 600, colors: '#64748b' } },
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                            },
                            // Jumlah orang selalu bulat: sumbu bertitik desimal pada
                            // data sekecil ini membuatnya terbaca seperti pecahan.
                            yaxis: { labels: { formatter: (v) => Math.round(v), style: { colors: '#94a3b8' } } },
                            grid: { borderColor: '#eef2f6', strokeDashArray: 4 },
                        }).render();
                    }

                    document.addEventListener('DOMContentLoaded', gambarGrafikTrenOrcha);
                    document.addEventListener('livewire:navigated', gambarGrafikTrenOrcha);
                    gambarGrafikTrenOrcha();
                })();
            </script>
        @endif

        {{-- ============ UANG ============

             Omzet, modal, dan keuntungan — dibaca dari laporan yang SAMA dengan
             halaman Keuntungan Paket, bukan dihitung ulang di sini. Dua tempat
             yang menghitung sendiri-sendiri akan berbeda angkanya suatu saat,
             biasanya tepat ketika ada yang menanyakannya.

             Digambar sebagai batang bersusun tanpa pustaka grafik: aset Vite
             tidak ikut ter-deploy untuk halaman admin ini, jadi pustaka yang
             menuntut bundel hanya menghasilkan kotak kosong di server. --}}
        @if ($uang !== [])
            <h2 class="orc-das-judul">Uang</h2>

            <div class="row g-3 mb-4 orc-das-uang">
                @foreach ([
                    ['omzet_teks', 'Omzet masuk', 'bi-graph-up-arrow', 'omzet'],
                    ['modal_teks', 'Modal keluar', 'bi-box-seam', 'modal'],
                    ['keuntungan_teks', 'Keuntungan', 'bi-piggy-bank', 'untung'],
                    ['potensi_omzet_teks', 'Potensi omzet', 'bi-hourglass-split', 'potensi'],
                ] as [$kunci, $label, $ikon, $rupa])
                    <div class="col-6 col-xl-3">
                        <div class="card orcha-kartu orcha-kartu-uang h-100" data-rupa="{{ $rupa }}">
                            <div class="card-body p-3 p-lg-4">
                                <div class="orcha-label-kecil mb-1">
                                    <i class="bi {{ $ikon }}"></i> {{ $label }}
                                </div>
                                <div class="orcha-angka-uang">{{ $uang[$kunci] ?? 'Rp 0' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Yang belum bisa dihitung disebut apa adanya. Laporan yang diam
                 soal paket tanpa modal membuat keuntungannya terbaca lebih kecil
                 daripada yang sebenarnya, tanpa satu pun tanda. --}}
            @if (($uang['belum_lengkap'] ?? 0) > 0)
                <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex gap-3 align-items-start mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div style="font-size:.86rem">
                        <strong>{{ $uang['belum_lengkap'] }} pendaftaran belum ikut terhitung keuntungannya</strong>
                        — paketnya belum diisi harga modal, jadi angka di atas lebih kecil
                        daripada yang sebenarnya.
                        <a href="{{ route('admin.orcha.keuntungan') }}" wire:navigate>Lengkapi modalnya</a>.
                    </div>
                </div>
            @endif
        @endif

        {{-- ============ GRAFIK OMZET & KEUNTUNGAN ============

             ApexCharts, sama seperti grafik keuangan di dashboard lemon —
             bentuk area bergradien, garis melengkung, keterangan di kanan atas.

             Pustakanya BOLEH dipakai di sini: berkasnya ada di public/mazer,
             bukan hasil bundel Vite. public/build memang tidak ikut ter-deploy,
             tetapi public/mazer terlacak git dan ikut terkirim ke server —
             saya sempat menyamakan keduanya dan menggambar grafiknya sendiri
             dengan batang HTML, padahal tidak perlu. --}}
        @if ($uangPerBulan !== [])
            <div class="card orcha-kartu mb-4">
                <div class="card-body p-3 p-lg-4">
                    <h6 class="fw-bold mb-1 orcha-judul-ikon" style="font-size:.95rem">
                        <i class="bi bi-graph-up-arrow text-primary"></i> Omzet & keuntungan per bulan
                    </h6>
                    <div class="text-muted mb-2" style="font-size:.8rem">
                        Hanya pendaftaran yang sudah lunas dan paketnya sudah punya harga modal.
                    </div>

                    <div id="orcha-grafik-uang" wire:ignore></div>
                </div>
            </div>

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
                         | Sisa grafik sebelumnya dibersihkan lebih dulu.
                         |
                         | Halaman ini dibuka lewat wire:navigate, dan tanpa ini tiap
                         | kunjungan berikutnya menumpuk satu kanvas lagi di kotak yang
                         | sama — tingginya berlipat dan yang terlihat grafik ganda.
                         */
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
                    }

                    document.addEventListener('DOMContentLoaded', gambarGrafikUangOrcha);
                    document.addEventListener('livewire:navigated', gambarGrafikUangOrcha);
                    gambarGrafikUangOrcha();
                })();
            </script>
        @endif

        {{-- ============ ISI ETALASE ============

             Dulu enam kartu sebesar kartu tindakan, masing-masing dengan kotak
             ikon bergradien sendiri. Bentuk itu menyamakan derajat dua hal yang
             berbeda: "22 destinasi populer" adalah KETERANGAN tentang isi
             etalase, bukan sesuatu yang menuntut dikerjakan hari ini — tetapi
             ia berteriak sama kerasnya dengan bukti bayar yang menunggu dicek.
             Ditambah baris tindakan dan baris uang, ada lima belas kartu
             berwarna di satu layar dan tidak ada satu pun yang menonjol.

             Sekarang satu bilah: angka tetap bisa dibaca sekilas dan tetap bisa
             diklik ke halamannya, tetapi tidak lagi menuntut giliran membaca
             lebih dulu daripada pekerjaan yang sebenarnya. --}}
        @php
            $rupaEtalase = [
                // Kunci bukti bayar SENGAJA tidak ada di daftar ini.
                //
                // Angka itu sudah tampil di baris "Perlu ditindak" paling atas,
                // dan menampilkannya dua kali di satu layar membuat admin
                // mengira ada dua tumpukan bukti yang berbeda.
                'paket' => ['bi-map', 'admin.orcha.paket'],
                'kendaraan' => ['bi-bus-front', 'admin.orcha.armada'],
                'destinasi' => ['bi-geo-alt', 'admin.orcha.destinasi'],
                'testimoni' => ['bi-chat-quote', 'admin.orcha.testimoni'],
                'partner' => ['bi-people', 'admin.orcha.partner'],
            ];

            $isiEtalase = collect($kartu)->filter(fn ($item) => isset($rupaEtalase[$item['kunci']]));
        @endphp

        @if ($isiEtalase->isNotEmpty())
            <h2 class="orc-das-judul">Isi etalase</h2>

            <div class="card orcha-kartu mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orc-das-bilah">
                        @foreach ($isiEtalase as $item)
                            @php [$ikon, $rute] = $rupaEtalase[$item['kunci']]; @endphp

                            <a @if (\Illuminate\Support\Facades\Route::has($rute)) href="{{ route($rute) }}" wire:navigate @endif
                                class="orc-das-bilah-item">
                                <i class="bi {{ $ikon }}"></i>
                                <span class="orc-das-bilah-angka">{{ $item['nilai'] }}</span>
                                <span class="orc-das-bilah-label">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            {{-- Rincian paket & armada --}}
            <div class="col-12 col-xl-4">
                <div class="card orcha-kartu h-100">
                    <div class="card-body p-4">
                        {{-- Batang, bukan deret angka.

                             Tiga angka bersusun menuntut pembacanya membandingkan
                             sendiri; panjang batang sudah menjawabnya sebelum
                             angkanya sempat dibaca. --}}
                        <h6 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:.95rem">
                            <i class="bi bi-map text-primary"></i> Paket per kategori
                        </h6>
                        @php $puncakPaket = max(1, collect($paketPerKategori)->max('jumlah') ?? 1); @endphp
                        @forelse ($paketPerKategori as $baris)
                            <div class="orcha-baris-batang">
                                <span class="nama">{{ $baris['label'] }}</span>
                                <span class="jalur">
                                    <span class="isi" style="width:{{ round($baris['jumlah'] / $puncakPaket * 100) }}%;
                                        background:linear-gradient(90deg,#3b82f6,#1d4ed8)"></span>
                                </span>
                                <span class="angka">{{ $baris['jumlah'] }}</span>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada data.</p>
                        @endforelse

                        <h6 class="fw-bold mt-4 mb-3 orcha-judul-ikon" style="font-size:.95rem">
                            <i class="bi bi-bus-front text-primary"></i> Armada per jenis
                        </h6>
                        @forelse ($kendaraanPerJenis as $baris)
                            @php
                                // Yang diukur PORSI SIAP-nya, bukan jumlah unitnya:
                                // pertanyaan admin bukan "punya berapa bus", melainkan
                                // "berapa yang bisa dipakai besok".
                                $porsi = ($baris['jumlah'] ?? 0) > 0
                                    ? round($baris['tersedia'] / $baris['jumlah'] * 100)
                                    : 0;
                                $warna = $porsi >= 60 ? '#10b981' : ($porsi > 0 ? '#f59e0b' : '#cbd5e1');
                            @endphp
                            <div class="orcha-baris-batang">
                                <span class="nama">{{ $baris['label'] }}</span>
                                <span class="jalur">
                                    <span class="isi" style="width:{{ $porsi }}%;background:{{ $warna }}"></span>
                                </span>
                                <span class="angka" style="font-size:.76rem">
                                    {{ $baris['tersedia'] }}/{{ $baris['jumlah'] }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada data.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Pendaftaran terbaru --}}
            <div class="col-12 col-xl-4">
                <div class="card orcha-kartu h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Pendaftaran terbaru</h6>
                            <a href="{{ route('admin.orcha.pendaftaran') }}" wire:navigate
                                class="small text-decoration-none">Lihat semua</a>
                        </div>
                        @forelse ($pendaftaranTerbaru as $baris)
                            <div class="py-2 border-bottom">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="orcha-kode small">{{ $baris['kode'] }}</span>
                                    {{-- Berwarna menurut statusnya, sama seperti di
                                         daftar pendaftaran: lima lencana abu yang sama
                                         tidak memberi tahu apa pun sampai tulisannya
                                         dibaca satu per satu. --}}
                                    <span class="orcha-cip-status-daftar status-{{ $baris['status'] }}">
                                        {{ $baris['status_label'] }}
                                    </span>
                                </div>
                                <div class="small fw-semibold">{{ $baris['nama'] }}</div>
                                <div class="text-muted" style="font-size:.78rem">
                                    {{ $baris['paket']['nama'] }} · {{ $baris['jumlah_peserta'] }} peserta
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada pendaftaran.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sewa terbaru --}}
            <div class="col-12 col-xl-4">
                <div class="card orcha-kartu h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Sewa kendaraan terbaru</h6>
                            <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate
                                class="small text-decoration-none">Lihat semua</a>
                        </div>
                        @forelse ($penyewaanTerbaru as $baris)
                            <div class="py-2 border-bottom">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="orcha-kode small">{{ $baris['kode'] }}</span>
                                    {{-- Warnanya memakai peta status PENYEWAAN, bukan
                                         status pendaftaran: keduanya punya kata yang
                                         sama ("baru", "batal") tetapi daftar keadaannya
                                         berbeda, dan menyamakannya membuat satu status
                                         kehilangan warnanya diam-diam. --}}
                                    <span class="orcha-cip-status-sewa" data-status="{{ $baris['status'] }}">
                                        {{ $baris['status_label'] }}
                                    </span>
                                </div>
                                <div class="small fw-semibold">{{ $baris['nama'] }}</div>
                                <div class="text-muted" style="font-size:.78rem">
                                    {{ $baris['kendaraan']['nama'] }} · {{ $baris['durasi_label'] }}
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada pemesanan sewa.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
