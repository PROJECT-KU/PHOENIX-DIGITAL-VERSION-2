@section('title')
    Kebijakan Privasi | Phoenix Digital
@endsection

<main class="legal-page lg-lebar">
    @include('partials.gaya-legal-daftar-isi')
    <div class="legal-hero">
        <div class="container">
            <span class="ph-sec-eyebrow"><i class="bi bi-shield-lock"></i> Legal</span>
            <h1>Kebijakan Privasi</h1>
            <p>Kami menghargai privasi Anda. Berikut cara Phoenix Digital mengelola data Anda.</p>
        </div>
    </div>

    <div class="container">
        <div class="legal-card">
            @php
                // Urutannya HARUS sama dengan urutan blok di bawah; id-nya pv-1..pv-6.
                $pasal = [
                    'Data yang Kami Kumpulkan', 'Penggunaan Data', 'Keamanan Data',
                    'Berbagi Data', 'Cookies', 'Hak Anda',
                ];
            @endphp

            <div class="lg-tata">
                <nav class="lg-nav" aria-label="Daftar isi">
                    <b>Daftar Isi</b>
                    <div class="lg-nav-tautan">
                        @foreach ($pasal as $i => $judul)
                            <a href="#pv-{{ $i + 1 }}">{{ $i + 1 }}. {{ $judul }}</a>
                        @endforeach
                    </div>
                </nav>

                <div>
            <div class="legal-block" id="pv-1">
                <h2><span>1</span> Data yang Kami Kumpulkan</h2>
                <p>Kami mengumpulkan data yang Anda berikan saat bertransaksi, seperti nama, nomor WhatsApp/email, serta detail pesanan. Data ini diperlukan untuk memproses pesanan Anda.</p>
            </div>

            <div class="legal-block" id="pv-2">
                <h2><span>2</span> Penggunaan Data</h2>
                <p>Data digunakan untuk memproses pesanan, mengirim akun/lisensi, memberi dukungan, dan menginformasikan status transaksi atau promo yang relevan.</p>
            </div>

            <div class="legal-block" id="pv-3">
                <h2><span>3</span> Keamanan Data</h2>
                <p>Kami menjaga kerahasiaan data Anda dan menerapkan langkah yang wajar untuk melindunginya dari akses yang tidak sah.</p>
            </div>

            <div class="legal-block" id="pv-4">
                <h2><span>4</span> Berbagi Data</h2>
                <p>Kami <b>tidak menjual</b> data pribadi Anda. Data hanya digunakan untuk keperluan transaksi dan operasional layanan Phoenix Digital.</p>
            </div>

            <div class="legal-block" id="pv-5">
                <h2><span>5</span> Cookies</h2>
                <p>Situs kami menggunakan cookies untuk meningkatkan pengalaman penggunaan (mis. keranjang belanja). Anda dapat menonaktifkannya melalui pengaturan browser.</p>
            </div>

            <div class="legal-block" id="pv-6">
                <h2><span>6</span> Hak Anda</h2>
                <p>Anda berhak meminta perubahan atau penghapusan data pribadi Anda. Hubungi kami untuk mengajukan permintaan tersebut.</p>
            </div>

                </div>
            </div>

            <div class="legal-contact">
                <i class="bi bi-whatsapp"></i>
                <div>
                    <strong>Ada pertanyaan?</strong>
                    <span>Hubungi kami di <a href="https://wa.me/6289505967995" target="_blank" rel="noopener">0895-0596-7995</a></span>
                </div>
            </div>
        </div>
    </div>
</main>
