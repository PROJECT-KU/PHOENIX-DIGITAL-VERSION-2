@section('title')
    Keuntungan & Syarat Member | Phoenix Digital
@endsection

<main class="legal-page">
    <div class="legal-hero">
        <div class="container">
            <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Gratis, selamanya</span>
            <h1>Jadi Member Phoenix</h1>
            <p>Tanpa biaya, tanpa ribet. Kumpulkan poin dari setiap belanja dan tukar jadi potongan di
                pembelian berikutnya.</p>
        </div>
    </div>

    <div class="container">
        <div class="legal-card">

            {{-- ===== Cara jadi member ===== --}}
            <div class="legal-block legal-highlight">
                <h2><span><i class="bi bi-key"></i></span> Caranya cuma 2 langkah</h2>
                <p>
                    <b>1. Belanja</b> — pesan produk apa saja, lalu tunggu sampai pesananmu berstatus
                    <b>Selesai</b> (akun sudah kamu terima).<br>
                    <b>2. Tulis testimoni</b> — ceritakan pengalamanmu lewat tombol
                    <b>Tulis Testimoni</b> di halaman depan. Isi nomor WhatsApp yang sama dengan yang
                    kamu pakai saat memesan.
                </p>
                <p>Begitu testimonimu disetujui admin, <b>status Member langsung aktif</b> — otomatis, tanpa
                    perlu menghubungi siapa pun. Kamu juga langsung dapat <b>kode referral</b> untuk dibagikan
                    ke teman.</p>
            </div>

            {{-- ===== Keuntungan ===== --}}
            <div class="legal-block">
                <h2><span><i class="bi bi-gift"></i></span> Apa untungnya?</h2>
                <p>
                    <b>Poin belanja</b> — tiap <b>Rp {{ number_format($perPoin, 0, ',', '.') }}</b> belanja jadi
                    <b>1 poin</b>, dan 1 poin bernilai <b>Rp {{ number_format($nilaiPoin, 0, ',', '.') }}</b>
                    potongan. Poin bisa dipakai kapan saja untuk memotong tagihan.<br>
                    <b>Harga khusus member</b> — banyak promo memberi diskon lebih besar untuk member, dan
                    sebagian promo memang <b>hanya untuk member</b>.<br>
                    <b>Kode referral</b> — bagikan ke teman; kalian berdua sama-sama untung.
                </p>
            </div>

            {{-- ===== Hitungan poin ===== --}}
            <div class="legal-block legal-highlight">
                <h2><span><i class="bi bi-calculator"></i></span> Contoh hitungannya</h2>
                <p>Misal kamu belanja <b>Rp {{ number_format($contohBelanja, 0, ',', '.') }}</b>:</p>
                <p>
                    Rp {{ number_format($contohBelanja, 0, ',', '.') }} ÷ Rp {{ number_format($perPoin, 0, ',', '.') }}
                    = <b>{{ $contohPoin }} poin</b> &nbsp;→&nbsp; senilai
                    <b>Rp {{ number_format($contohNilai, 0, ',', '.') }}</b> potongan.<br>
                    Sisanya <b>Rp {{ number_format($contohSisa, 0, ',', '.') }}</b>
                    <b>tidak hangus</b> — disimpan dan dijumlahkan ke belanja berikutnya.
                </p>
                <p>Jadi belanja kecil pun tidak sia-sia; sisanya menumpuk terus sampai jadi poin.</p>
            </div>

            {{-- ===== Syarat & ketentuan ===== --}}
            <div class="legal-block">
                <h2><span><i class="bi bi-list-check"></i></span> Syarat & ketentuan</h2>
                <p>
                    &bull; Menjadi member <b>gratis</b> — tidak ada biaya pendaftaran maupun iuran.<br>
                    &bull; Testimoni hanya bisa membuat kamu jadi member bila nomor WhatsApp-nya cocok dengan
                    pesanan yang sudah berstatus <b>Selesai</b>. Pesanan yang masih menunggu pembayaran,
                    sedang diproses, atau dibatalkan belum berlaku.<br>
                    &bull; Siapa pun tetap boleh menulis testimoni — hanya saja tanpa pesanan yang selesai,
                    testimoninya tidak memberikan status member.<br>
                    &bull; Semua testimoni <b>ditinjau admin</b> terlebih dahulu. Testimoni yang tidak wajar
                    dapat ditolak.<br>
                    &bull; <b>Poin dihitung dari pesanan yang sudah dibayar</b> pada tahun berjalan, dan
                    <b>direset setiap 1 Januari</b>. Pakai poinmu sebelum akhir tahun.<br>
                    &bull; Nomor WhatsApp-mu <b>tidak ditampilkan</b> pada testimoni dan tidak dibagikan ke
                    pihak lain — hanya dipakai untuk mencocokkan pesanan.
                </p>
            </div>

            {{-- Rute checkout dipakai langsung, BUKAN url()->previous() — kalau halaman
                 ini dibuka dari tempat lain, previous() melempar ke sana padahal
                 tombolnya jelas-jelas bertuliskan "Kembali ke Checkout". --}}
            <div class="legal-block">
                <p class="text-center" style="margin-bottom:0;">
                    <a href="{{ route('checkout') }}" class="co-btn co-btn-primary" style="display:inline-flex; width:auto;">
                        <i class="bi bi-arrow-left"></i> Kembali ke Checkout
                    </a>
                </p>
            </div>

        </div>
    </div>
</main>
