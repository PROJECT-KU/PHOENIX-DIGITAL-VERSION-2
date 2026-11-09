{{-- Halaman perantara tautan bagikan (lowongan, portofolio, berita): meta OG
     untuk perayap WhatsApp/LinkedIn/Google, lalu manusia dialihkan ke situs. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }}</title>
    <meta name="description" content="{{ $deskripsi }}">
    <link rel="canonical" href="{{ $tujuan }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PT. Asthana Cipta Mandiri">
    <meta property="og:title" content="{{ $judul }}">
    <meta property="og:description" content="{{ $deskripsi }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $gambar }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="robots" content="noindex, follow">
    @if ($jsonLd)
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif
    <meta http-equiv="refresh" content="0; url={{ $tujuan }}">
    <script>location.replace(@json($tujuan));</script>
</head>
<body style="font-family: system-ui, sans-serif; padding: 40px; text-align: center; color: #0f1b3d;">
    <p>Membuka <strong>{{ $nama }}</strong>…</p>
    <p><a href="{{ $tujuan }}">Lanjut ke halamannya</a></p>
</body>
</html>
