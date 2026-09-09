@section('title')
    Beranda | Phoenix Digital
@endsection
<div class="beranda">
    {{-- ===== Irama halaman =====
         Sebelumnya tiap bagian membawa latar & jaraknya sendiri: berpindah-pindah
         antara putih, krem, dan pita berwarna tanpa pola. Mata membaca perpindahan
         yang tak beraturan itu sebagai "ramai", meski tiap bagiannya rapi sendiri.

         Kini hanya DUA warna yang berselang teratur, dan satu skala jarak untuk
         semua bagian. Gaya ditulis inline karena public/build masuk .gitignore dan
         tidak ikut terdeploy — salinan CSS di server masih tertanggal 19 Agustus. --}}
    <style>
        .beranda .section { padding-top: 52px; padding-bottom: 52px; }

        /* Bagian berlatar krem lembut, berselang-seling dengan yang putih.
           Ditulis per-id, bukan :nth-child, supaya menambah atau memindahkan satu
           bagian tidak diam-diam menukar warna seluruh halaman di bawahnya. */
        .beranda #layanan-beranda,
        .beranda #cara-pesan,
        .beranda #testimoni { background: linear-gradient(180deg, #fffaf4, #fff); }

        /* Hero & etalase flash sale punya jaraknya sendiri: keduanya bukan
           bagian bertajuk melainkan pita pembuka. */
        .beranda .ph-hero.section { padding-top: 18px; padding-bottom: 8px; }
        .beranda #call-to-action.section { padding-top: 26px; padding-bottom: 30px; }

        @media (max-width: 991.98px) {
            .beranda .section { padding-top: 40px; padding-bottom: 40px; }
        }
        @media (max-width: 575.98px) {
            .beranda .section { padding-top: 32px; padding-bottom: 32px; }
        }
    </style>

    {{-- banner --}}
    @include('livewire.pages.public.homepage.partials.banner')
    {{-- end banner --}}

    {{-- flash sale =====================================================
         Didahulukan atas Produk Terlaris: flash sale punya batas waktu,
         sedangkan produk terlaris ada setiap hari. Menaruh yang berbatas waktu
         di bawah berarti sebagian pengunjung menutup halaman sebelum sempat
         melihatnya, dan penawarannya keburu habis.

         Aman ditaruh paling atas: komponennya kosong tanpa latar apa pun saat
         tidak ada flash sale yang sedang berjalan, jadi tidak meninggalkan
         pita kosong di puncak beranda. --}}
    @include('livewire.pages.public.homepage.partials.flash-sale')
    {{-- end flash sale --}}

    {{-- produk terlaris --}}
    @include('livewire.pages.public.homepage.partials.produk-terlaris')
    {{-- end produk terlaris --}}

    {{-- layanan =========================================================
         Ditaruh setelah produk, sebelum paket: pengunjung yang datang mencari
         akun sudah terlayani di atas, dan yang mencari jasa tidak perlu
         menggulir sampai dasar halaman untuk menemukannya. --}}
    @include('livewire.pages.public.homepage.partials.layanan')
    {{-- end layanan --}}

    {{-- produk-bundling --}}
    {{-- Beranda hanya menampilkan 4 paket terbaru; selebihnya di halaman paket
         tersendiri. Komponennya sama dengan /bundling supaya kartu dan alur
         "tambah ke keranjang" tidak bercabang. --}}
    <livewire:pages.public.bundling.index :di-beranda="true" />
    {{-- end produk-bundling --}}

    {{-- cara pesan ======================================================
         Ditaruh SETELAH orang melihat barangnya. Menjelaskan cara memesan
         sebelum ada yang ingin dipesan hanya menunda pertemuan dengan
         produknya; setelah tertarik, barulah pertanyaan "caranya bagaimana"
         muncul di kepala pengunjung. --}}
    @include('livewire.pages.public.homepage.partials.cara-pesan')
    {{-- end cara pesan --}}

    {{-- testimoni --}}
    <livewire:components.testimonials />
    {{-- end testimoni --}}
</div>
