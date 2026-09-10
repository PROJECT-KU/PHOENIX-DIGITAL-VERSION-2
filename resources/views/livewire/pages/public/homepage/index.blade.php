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

        /* Latar dibiarkan satu warna. Pita krem yang berselang-seling memang
           memisahkan bagian, tapi ia juga menambah satu lapis pola lagi di atas
           halaman yang sudah penuh pola. Pemisahnya kini dipindah ke STRUKTUR:
           ada bagian yang memakai kartu, ada yang tidak — dan pergantian itu
           terasa lebih tenang daripada pergantian warna. */

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

    {{-- banner ==========================================================
         Pembuka halaman. Banner adalah satu-satunya bagian beranda yang isinya
         diatur admin lewat panel, dan itulah tempat kabar terpenting hari itu
         dipasang — jadi ia yang pertama terlihat, bukan yang harus dicari. --}}
    @include('livewire.pages.public.homepage.partials.banner')
    {{-- end banner --}}

    {{-- jaminan atas ====================================================
         Empat jaminan sebelum apa pun yang lain. Pertanyaan pertama pengunjung
         baru adalah "toko ini bisa dipercaya tidak", bukan "apa yang dijual";
         menjawabnya setelah ia melewati seluruh hero berarti terlambat. --}}
    @include('livewire.pages.public.homepage.partials.jaminan-atas')
    {{-- end jaminan atas --}}

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


    {{-- dipercaya =======================================================
         Pita merek langsung di bawah hero. Pertanyaan pertama pengunjung baru
         bukan "apa yang dijual", melainkan "toko ini benar atau tidak" — dan
         deretan merek yang ia kenali menjawabnya lebih cepat daripada kalimat
         apa pun. Isinya dibaca dari katalog, jadi tidak bisa basi. --}}
    @include('livewire.pages.public.homepage.partials.dipercaya')
    {{-- end dipercaya --}}

    {{-- kategori populer =================================================
         Ditaruh sebelum daftar produk: pengunjung yang sudah tahu kebutuhannya
         bisa langsung menyaring, tanpa menggulir seluruh etalase. Kategori yang
         kehabisan produk hilang sendiri — lihat App\Support\KategoriBeranda. --}}
    @include('livewire.pages.public.homepage.partials.kategori-populer')
    {{-- end kategori populer --}}

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

    {{-- jaminan bawah ===================================================
         Menjawab pertanyaan yang baru muncul SETELAH orang melihat harga: soal
         keaslian lisensi dan keamanan transaksi. Kalimatnya sengaja berbeda
         dari pita jaminan di puncak halaman. --}}
    @include('livewire.pages.public.homepage.partials.jaminan-bawah')
    {{-- end jaminan bawah --}}

    {{-- testimoni --}}
    <livewire:components.testimonials />
    {{-- end testimoni --}}
</div>
