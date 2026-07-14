@section('title')
    Layanan Teknologi | Phoenix Digital
@endsection

@php
    $wa = 'https://wa.me/6289505967995?text=';
    $services = [
        [
            'icon' => 'bi-code-slash',
            'title' => 'Pengembangan Website',
            'desc' => 'Landing page, company profile, hingga toko online yang cepat, responsif, dan mudah dikelola.',
            'bonus' => 'Gratis domain & hosting 1 tahun',
            'features' => ['Desain modern & mobile-friendly', 'SEO dasar — terindeks Google', 'Gratis SSL (HTTPS) & panel admin'],
            'price' => 'Rp 500.000',
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan layanan Pengembangan Website.',
        ],
        [
            'icon' => 'bi-phone',
            'title' => 'Aplikasi Mobile',
            'desc' => 'Aplikasi Android & iOS untuk bisnis, layanan, maupun internal perusahaan Anda.',
            'bonus' => 'Gratis domain & hosting 1 tahun',
            'features' => ['Android & iOS', 'UI/UX rapi & ringan', 'Bantu publish ke Play Store'],
            'price' => 'Rp 2.000.000',
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan layanan Aplikasi Mobile.',
        ],
        [
            'icon' => 'bi-camera-reels',
            'title' => 'Konten Sosial Media',
            'desc' => 'Desain feed, video pendek, dan copywriting untuk menaikkan brand di media sosial.',
            'bonus' => 'Gratis 3× revisi desain',
            'features' => ['Desain feed & story', 'Video pendek (reels/tiktok)', 'Copywriting & caption'],
            'price' => 'Rp 500.000',
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan layanan Konten Sosial Media.',
        ],
        [
            'icon' => 'bi-diagram-3',
            'title' => 'Sistem Informasi / Aplikasi Web',
            'desc' => 'Dashboard, manajemen data, kasir (POS), hingga sistem internal sesuai alur kerja Anda.',
            'bonus' => 'Gratis training penggunaan',
            'features' => ['Custom sesuai kebutuhan', 'Multi-user & hak akses', 'Laporan & ekspor data'],
            'price' => 'Rp 1.500.000',
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan layanan Sistem Informasi / Aplikasi Web.',
        ],
        [
            'icon' => 'bi-palette',
            'title' => 'UI/UX & Desain',
            'desc' => 'Wireframe, prototype, dan desain antarmuka yang menarik sekaligus mudah digunakan.',
            'bonus' => 'Gratis file sumber (editable)',
            'features' => ['Wireframe & prototype', 'Desain UI konsisten', 'Logo & identitas brand'],
            'price' => 'Rp 750.000',
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan layanan UI/UX & Desain.',
        ],
        [
            'icon' => 'bi-robot',
            'title' => 'Bot WhatsApp & Otomasi',
            'desc' => 'Auto-reply, notifikasi otomatis, dan integrasi untuk menghemat waktu operasional.',
            'bonus' => 'Gratis setup & konfigurasi awal',
            'features' => ['Auto-reply & broadcast', 'Notifikasi otomatis', 'Integrasi sistem/API'],
            'price' => 'Rp 500.000',
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan layanan Bot WhatsApp & Otomasi.',
        ],
    ];

    $perks = [
        ['bi-gift', 'Domain & hosting 1 tahun', 'Gratis untuk website & aplikasi'],
        ['bi-shield-check', 'Garansi & support', 'Perbaikan bug & bantuan purnajual'],
        ['bi-arrow-repeat', 'Revisi terjamin', 'Revisi sampai sesuai kesepakatan'],
        ['bi-file-earmark-code', 'Source code milik Anda', 'Tanpa terkunci ke vendor'],
        ['bi-chat-dots', 'Konsultasi gratis', 'Bantu rancang solusi terbaik'],
        ['bi-lightning-charge', 'Cepat & amanah', 'Progres jelas, tepat waktu'],
        ['bi-phone', 'Responsif semua perangkat', 'Rapi di HP, tablet & desktop'],
        ['bi-search', 'Siap terindeks Google', 'SEO dasar sudah termasuk'],
    ];

    $webPackages = [
        [
            'name' => 'Paket Starter Company',
            'old' => 'Rp 1.500.000',
            'price' => 'Rp 500.000',
            'popular' => false,
            'for' => 'UMKM & startup baru',
            'features' => ['1–5 Halaman', 'Gratis Domain & hosting 1 tahun', 'WhatsApp Button', 'SEO dasar (meta title & description)', 'Form kontak', 'Keamanan SSL (HTTPS)', 'Desain profesional & responsif', 'Page Speed Optimization (basic)', 'Training singkat admin', 'Garansi bug 3 bulan', 'Revisi 2×'],
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan Paket Starter Company (website).',
        ],
        [
            'name' => 'Paket Ecommerce',
            'old' => 'Rp 7.500.000',
            'price' => 'Rp 2.000.000',
            'popular' => true,
            'for' => 'Brand online & UMKM aktif jualan',
            'features' => ['Unlimited produk', 'Payment gateway otomatis', 'Voucher & diskon', 'Desain responsif', 'Laporan penjualan', 'SEO produk', 'Gratis Domain & hosting 1 tahun', 'Keamanan SSL (HTTPS)', 'Page Speed Optimization', 'Revisi 5×'],
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan Paket Ecommerce (website).',
        ],
        [
            'name' => 'Paket Custom',
            'old' => 'Rp 15.000.000',
            'price' => 'Rp 8.000.000',
            'popular' => false,
            'for' => 'Perusahaan & sistem kompleks',
            'features' => ['Sistem web kompleks', 'Multi role user', 'Integrasi API pihak ketiga', 'Keamanan lanjutan', 'Dokumentasi sistem', 'Unlimited email bisnis', 'Gratis Domain & hosting 1 tahun', 'SEO lanjutan', 'Keamanan SSL (HTTPS)', 'Page Speed Optimization', 'Unlimited revisi', 'Support prioritas', 'SLA & maintenance khusus'],
            'msg' => 'Halo Phoenix Digital, saya tertarik dengan Paket Custom (website).',
        ],
    ];

    // Pembeda dari vendor lain — semua sudah termasuk harga.
    $diffs = [
        ['Domain & hosting 1 tahun', 'Gratis, sudah termasuk', 'Biaya tambahan'],
        ['Source code proyek', 'Sepenuhnya milik Anda', 'Sering ditahan / terkunci'],
        ['SSL / HTTPS (keamanan)', 'Gratis, sudah termasuk', 'Sering dikenai biaya'],
        ['Garansi & perbaikan bug', 'Termasuk (mulai 3 bulan)', 'Bayar terpisah'],
        ['Revisi', 'Sesuai paket, jelas di awal', 'Dibatasi / bayar per revisi'],
        ['Training penggunaan', 'Gratis', 'Tidak ada / berbayar'],
        ['Transparansi harga', 'All-in, tanpa biaya tersembunyi', 'Banyak biaya tak terduga'],
        ['Dukungan (support)', 'Respons cepat via WhatsApp', 'Lambat / sistem tiket'],
        ['Ketepatan waktu', 'Estimasi jelas & tepat waktu', 'Sering molor tanpa kabar'],
        ['Desain', 'Custom sesuai brand Anda', 'Template seragam / kaku'],
        ['Kepemilikan akun & akses', 'Semua diserahkan ke Anda', 'Sering dipegang vendor'],
        ['Komunikasi', 'Langsung dengan tim developer', 'Lewat perantara / lambat'],
    ];
@endphp

<main class="svc-page">
    <style>
        /* Sembunyikan garis animasi latar khusus di halaman layanan */
        #ph-page-lines { display: none !important; }
    </style>
    <div class="svc-hero">
        <div class="container">
            <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Layanan Teknologi</span>
            <h1>Solusi Digital untuk Bisnis &amp; Instansi Anda</h1>
            <p>Selain menjual akun premium &amp; tools AI, Phoenix Digital juga mengerjakan pengembangan
                teknologi — dari website hingga aplikasi. Harga <b>mulai Rp 500.000-an</b>, menyesuaikan kebutuhan.</p>
            <a class="svc-hero-cta" href="{{ $wa }}Halo%20Phoenix%20Digital%2C%20saya%20ingin%20konsultasi%20layanan%20teknologi." target="_blank" rel="noopener">
                <i class="bi bi-whatsapp"></i> Konsultasi Gratis
            </a>
        </div>
    </div>

    <div class="container">
        <div class="svc-perks">
            <div class="svc-perks-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-patch-check-fill"></i> Sudah Termasuk</span>
                <h2>Setiap proyek sudah termasuk</h2>
                <p>Nilai lebih yang Anda dapatkan tanpa biaya tambahan.</p>
            </div>
            <div class="svc-perks-grid">
                @foreach ($perks as $p)
                    <div class="svc-perk">
                        <span class="svc-perk-ic"><i class="bi {{ $p[0] }}"></i></span>
                        <div>
                            <b>{{ $p[1] }}</b>
                            <span>{{ $p[2] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="svc-grid">
            @foreach ($services as $s)
                <div class="svc-card">
                    <div class="svc-icon"><i class="bi {{ $s['icon'] }}"></i></div>
                    <h3>{{ $s['title'] }}</h3>
                    <p class="svc-desc">{{ $s['desc'] }}</p>
                    @if (!empty($s['bonus']))
                        <div class="svc-bonus"><i class="bi bi-gift-fill"></i> {{ $s['bonus'] }}</div>
                    @endif
                    <ul class="svc-features">
                        @foreach ($s['features'] as $f)
                            <li><i class="bi bi-check-circle-fill"></i> {{ $f }}</li>
                        @endforeach
                    </ul>
                    <div class="svc-foot">
                        <div class="svc-price">
                            <small>Mulai</small>
                            <strong>{{ $s['price'] }}</strong>
                        </div>
                        <a class="svc-btn" href="{{ $wa }}{{ rawurlencode($s['msg']) }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i> Pesan
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paket berjenjang khusus Pengembangan Website --}}
        <div class="svcpkg">
            <div class="svc-perks-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-window-stack"></i> Paket Website</span>
                <h2>Paket Pengembangan Website</h2>
                <p>Pilih paket yang paling sesuai — semua sudah termasuk <b>domain &amp; hosting gratis 1 tahun</b>.</p>
            </div>

            <div class="svcpkg-grid">
                @foreach ($webPackages as $pkg)
                    <div class="svcpkg-card @if ($pkg['popular']) is-popular @endif">
                        @if ($pkg['popular'])
                            <span class="svcpkg-badge"><i class="bi bi-star-fill"></i> Terpopuler</span>
                        @endif
                        <h3>{{ $pkg['name'] }}</h3>
                        <div class="svcpkg-price">
                            <span class="svcpkg-old">{{ $pkg['old'] }}</span>
                            <div class="svcpkg-now"><small>Mulai</small> <strong>{{ $pkg['price'] }}</strong></div>
                        </div>
                        <ul class="svcpkg-features">
                            @foreach ($pkg['features'] as $f)
                                <li><i class="bi bi-check-circle-fill"></i> {{ $f }}</li>
                            @endforeach
                        </ul>
                        <div class="svcpkg-for"><i class="bi bi-bullseye"></i> Cocok untuk: <b>{{ $pkg['for'] }}</b></div>
                        <a class="svc-btn w-100 justify-content-center" href="{{ $wa }}{{ rawurlencode($pkg['msg']) }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i> Pilih Paket
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Pembeda dari vendor/startup lain --}}
        <div class="svcdiff">
            <div class="svc-perks-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-award-fill"></i> Keunggulan Kami</span>
                <h2>Kenapa Phoenix Digital Berbeda?</h2>
                <p>Semua keunggulan ini <b>sudah termasuk dalam harga</b> — tanpa biaya tersembunyi, tanpa tambahan mendadak.</p>
            </div>

            <div class="svcdiff-wrap">
                <div class="svcdiff-table">
                    <div class="svcdiff-row svcdiff-head">
                        <div class="svcdiff-feat">Yang Anda dapatkan</div>
                        <div class="svcdiff-us">Phoenix Digital</div>
                        <div class="svcdiff-them">Vendor umumnya</div>
                    </div>
                    @foreach ($diffs as $d)
                        <div class="svcdiff-row">
                            <div class="svcdiff-feat">{{ $d[0] }}</div>
                            <div class="svcdiff-us"><i class="bi bi-check-circle-fill"></i> <span>{{ $d[1] }}</span></div>
                            <div class="svcdiff-them"><i class="bi bi-x-circle"></i> <span>{{ $d[2] }}</span></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="svc-cta">
            <div>
                <h3>Punya kebutuhan khusus?</h3>
                <p>Ceritakan proyek Anda — kami bantu carikan solusi & estimasi harga terbaik.</p>
            </div>
            <a class="svc-btn lg" href="{{ $wa }}Halo%20Phoenix%20Digital%2C%20saya%20ingin%20mendiskusikan%20kebutuhan%20proyek%20teknologi." target="_blank" rel="noopener">
                <i class="bi bi-whatsapp"></i> Hubungi Admin
            </a>
        </div>

        <p class="svc-note"><i class="bi bi-info-circle"></i> Harga di atas adalah harga <b>mulai</b>; biaya akhir menyesuaikan cakupan, fitur, dan tingkat kerumitan proyek.</p>
    </div>
</main>
