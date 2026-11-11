@section('title')
{{ $ubah ? 'Ubah Harga' : 'Harga Baru' }} || lemon
@stop

@php
    /*
     | Bahan yang dibaca partial isiannya.
     |
     | Dirakit di sini, bukan di komponennya: ini keputusan TAMPILAN — kapan
     | kolom destinasi ditawarkan, nama apa yang dirakit otomatis, muatan mana
     | yang sedang terpilih. Menaruhnya di PHP berarti layar berikutnya
     | merakitnya sendiri lalu berselisih dengan yang ini.
     */
    $perluKapasitas = $this->perluKapasitas();
    $sebutan = $this->sebutanUnit();
    $pilihanMuat = $this->pilihanKapasitas();
    $muatKini = (int) ($isian['kapasitas'] ?? 0);
    $namaRakitan = $this->namaOtomatis();
    $namaKini = trim((string) ($isian['nama'] ?? ''));
    $destinasiKini = trim((string) ($isian['destinasi'] ?? ''));

    $destinasiAsing = $destinasiKini !== '' && ! in_array($destinasiKini, $namaKatalog, true);

    // Destinasi hanya ditawarkan untuk tiket — tetapi tetap ditampilkan bila
    // nilainya SUDAH ada, supaya mengganti kategori tidak diam-diam
    // menyembunyikan data yang masih tersimpan.
    $medanDestinasi = ($isian['kategori'] ?? '') === 'tiket' || $destinasiKini !== '';

    $vendor = $vendorAda;
    $vendorKini = collect($vendor)->firstWhere('id', (int) ($isian['vendor_id'] ?? 0));

    $kategori = $kosakata['kategori'] ?? [];
    $satuan = $kosakata['satuan'] ?? [];
    $provinsi = $provinsiAda;
@endphp

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')
    @include('livewire.pages.admin.orcha.rab.partials.master-harga-gaya')

    {{-- Pemilih berbentuk jendela, untuk Destinasi, Tempat lain, Vendor,
         dan Provinsi.

         Pemuatnya WAJIB di halaman yang memakainya. Markah isiannya dulu
         memanggil pasangSelectCari() sementara yang menyertakan pustakanya
         cuma halaman DAFTAR, jadi di halaman ini fungsinya tidak pernah ada:
         dibuka langsung, keempat kotak itu tetap kotak pilih bawaan peramban;
         dicapai lewat wire:navigate dari daftar, skripnya terlanjur hidup
         tetapi lembar gayanya tidak ikut, dan yang terlihat kotak pilih asli
         DITAMBAH salinan telanjang nilainya di bawahnya. Keduanya tanpa satu
         pun galat. Dijaga PemuatPilihPopupTest. --}}
    @include('livewire.pages.admin.orcha.partials.pilih-popup')

    <div class="dsb">
        @include('livewire.pages.admin.orcha.rab.partials.master-harga-borang')
    </div>

    @include('livewire.pages.admin.orcha.rab.partials.master-harga-cermin')

    {{-- Tanpa ini, Simpan terlihat seperti tidak ditekan: pesan dan
         perpindahannya dikirim lewat event, dan yang mendengarkannya hidup di
         partial ini. Lihat PenangganEventOrchaTest. --}}
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
