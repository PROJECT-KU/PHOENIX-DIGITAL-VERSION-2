@php
    // Halaman bertoken (mis. /cek/{token}) dikirim ke GA memakai jalur samaran
    // supaya token — satu-satunya kunci akses dokumen pelanggan, karena mereka
    // tidak login — tidak ikut tersimpan di laporan Google. Halaman biasa
    // dikirim apa adanya agar laporan tetap normal.
    $jalurSamaran = \App\Support\JalurAnalitik::samaran();
    $akar = rtrim(config('app.url'), '/');
@endphp
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-YTEV4R4VHX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

@if ($jalurSamaran)
  {{-- page_referrer ikut ditimpa: tanpa itu, berpindah dari satu halaman
       bertoken ke halaman lain akan mengirim token lewat kolom referrer. --}}
  gtag('config', 'G-YTEV4R4VHX', {
    page_path: @json($jalurSamaran),
    page_location: @json($akar.$jalurSamaran),
    page_referrer: @json($akar.'/')
  });
@else
  gtag('config', 'G-YTEV4R4VHX');
@endif
</script>
