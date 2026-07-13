@php
    $rn = \Illuminate\Support\Facades\Route::currentRouteName();
    // Akses via array (bukan config() dot-notation) karena nama route mengandung titik, mis. "shop.index".
    $cfg = config('seo.routes', [])[$rn] ?? [];
    $def = config('seo.default');
    $siteName = config('seo.site_name', 'Phoenix Digital');

    // Prioritas: yang di-share komponen → config route → default.
    $seoT = $seoTitle ?? ($cfg['title'] ?? $def['title']);
    $seoD = $seoDescription ?? ($cfg['description'] ?? $def['description']);
    $seoK = $seoKeywords ?? ($cfg['keywords'] ?? $def['keywords'] ?? null);

    // Gambar OG: 1) yang di-share komponen (mis. gambar produk), 2) banner aktif terbaru
    //    (otomatis mengikuti banner yang di-upload admin), 3) logo default.
    if (! empty($seoImage)) {
        $seoImg = asset($seoImage);
    } else {
        $banner = \App\Models\Banners::where('status', 'active')->latest()->first();
        $seoImg = $banner && $banner->gambar
            ? asset('storage/img/banners/' . $banner->gambar)
            : asset(config('seo.image'));
    }

    $seoU = $seoUrl ?? url()->current();
    $noindex = ($seoNoindex ?? false) || in_array($rn, config('seo.noindex_routes', []), true);
    $biz = config('seo.business', []);

    // Breadcrumb: Beranda → (label halaman). Leaf memakai URL saat ini.
    $crumbMap = [
        'shop.index' => 'Shop',
        'shop.detail-product' => $seoCrumbName ?? 'Produk',
        'bundling.index' => 'Paket Bundling',
        'bundling.product-bundlings' => 'Paket Bundling',
        'services' => 'Layanan',
        'about' => 'Tentang Kami',
        'contact' => 'Kontak',
        'faq' => 'FAQ',
        'terms' => 'Syarat & Ketentuan',
        'privacy' => 'Kebijakan Privasi',
        'order.history' => 'Riwayat Pesanan',
    ];
    $breadcrumb = null;
    if (isset($crumbMap[$rn])) {
        $breadcrumb = [
            ['name' => 'Beranda', 'url' => url('/')],
            ['name' => $crumbMap[$rn], 'url' => $seoU],
        ];
    }
@endphp
@if (config('seo.google_verification'))<meta name="google-site-verification" content="{{ config('seo.google_verification') }}">@endif
<title>{{ $seoT }}</title>
<meta name="description" content="{{ $seoD }}">
@if ($seoK)<meta name="keywords" content="{{ $seoK }}">@endif
<link rel="canonical" href="{{ $seoU }}">
<meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">
<meta name="theme-color" content="#f26522">

{{-- Open Graph --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $seoT }}">
<meta property="og:description" content="{{ $seoD }}">
<meta property="og:url" content="{{ $seoU }}">
<meta property="og:image" content="{{ $seoImg }}">
<meta property="og:locale" content="id_ID">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoT }}">
<meta name="twitter:description" content="{{ $seoD }}">
<meta name="twitter:image" content="{{ $seoImg }}">

{{-- Structured data: LocalBusiness / Organization --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Store',
    'name' => $siteName,
    'url' => url('/'),
    'logo' => asset(config('seo.image')),
    'image' => $seoImg,
    'description' => $def['description'],
    'telephone' => $biz['telephone'] ?? null,
    'email' => $biz['email'] ?? null,
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $biz['address'] ?? null,
        'addressLocality' => $biz['locality'] ?? null,
        'addressRegion' => $biz['region'] ?? null,
        'addressCountry' => $biz['country'] ?? 'ID',
    ],
    'sameAs' => $biz['same_as'] ?? [],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

{{-- Structured data: BreadcrumbList --}}
@if ($breadcrumb)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($breadcrumb)->map(fn ($c, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $c['name'],
            'item' => $c['url'],
        ])->all(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endif

{{-- Structured data tambahan per halaman (mis. Product, FAQ) --}}
@isset($seoJsonLd)
    <script type="application/ld+json">{!! $seoJsonLd !!}</script>
@endisset
