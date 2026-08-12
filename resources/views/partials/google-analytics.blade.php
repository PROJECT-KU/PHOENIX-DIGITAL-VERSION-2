@php
    // Halaman bertoken (mis. /cek/{token}) dikirim ke GA memakai jalur samaran
    // supaya token — satu-satunya kunci akses dokumen pelanggan, karena mereka
    // tidak login — tidak ikut tersimpan di laporan Google. Halaman biasa
    // dikirim apa adanya agar laporan tetap normal.
    $jalurSamaran = \App\Support\JalurAnalitik::samaran();
    $akar = rtrim(config('app.url'), '/');

    // Lewat config, bukan ditulis langsung di tiga tempat seperti sebelumnya.
    // Kosong = gtag tidak dimuat sama sekali, sehingga mengganti properti GA4
    // cukup mengubah satu baris .env dan tidak ada data yang diam-diam mengalir
    // ke properti yang tidak bisa dibuka pemiliknya.
    $gaId = config('services.google_analytics.id');
@endphp
@if ($gaId)
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

@if ($jalurSamaran)
  {{-- page_referrer ikut ditimpa: tanpa itu, berpindah dari satu halaman
       bertoken ke halaman lain akan mengirim token lewat kolom referrer. --}}
  gtag('config', @json($gaId), {
    page_path: @json($jalurSamaran),
    page_location: @json($akar.$jalurSamaran),
    page_referrer: @json($akar.'/')
  });
@else
  gtag('config', @json($gaId));
@endif
</script>
@endif
