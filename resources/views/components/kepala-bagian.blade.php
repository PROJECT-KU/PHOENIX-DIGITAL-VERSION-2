@props([
    'ikon' => null,
    'kicker' => null,
    'judul' => '',
    'sub' => null,
    'tautanUrl' => null,
    'tautanTeks' => 'Lihat Semua',
])

{{-- Kepala bagian yang dipakai SELURUH bagian beranda.

     Sebelumnya tiap bagian punya polanya sendiri: sebagian rata tengah dengan
     chip di atasnya, sebagian rata kiri tanpa chip, ukuran judulnya pun
     berbeda-beda. Mata membaca perbedaan itu sebagai "halaman ini disusun
     beberapa orang", bukan sebagai variasi yang disengaja.

     @once dipakai untuk gayanya: komponen ini muncul lima kali dalam satu
     halaman, dan tanpa itu blok <style> yang sama ikut tercetak lima kali. --}}
@once
<style>
    /* Ditulis inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
    .kb-kepala {
        display: flex; align-items: flex-end; justify-content: space-between;
        gap: 20px; margin-bottom: 30px;
    }
    .kb-teks { min-width: 0; }
    /* Label tanpa pil. Lima chip jingga identik yang berulang di atas tiap
       judul adalah penanda paling kentara bahwa halaman disusun dari cetakan
       yang sama — mata menangkap pengulangannya sebelum membaca isinya.
       Dijadikan label polos: tetap memberi konteks, tanpa berteriak. */
    /* Label pembuka. Jaraknya ke judul DIRAPATKAN (8px jadi 6px) karena ia
       keterangan judul itu sendiri, bukan baris yang berdiri sendiri — dan
       jarak antar huruf dilebarkan .12em jadi .16em supaya pada ukuran sekecil
       ini ia tetap terbaca sebagai label, bukan sebagai kata yang mengecil. */
    .kb-kicker {
        display: inline-flex; align-items: center; gap: 7px; margin-bottom: 6px;
        color: #f26522; font-size: .75rem; font-weight: 700;
        letter-spacing: .16em; text-transform: uppercase;
    }
    .kb-kicker i.bi { font-size: .78rem; line-height: 1; }
    .kb-kicker i.bi::before { display: block; line-height: 1; }
    /* Judul lebih besar, jarak huruf sedikit dirapatkan — beban visualnya
       dipindah dari hiasan ke tipografi. */
    /* Judul bagian dinaikkan 2.1rem jadi 2.4rem. Judul promo kini 44px dan
       judul hero lebih besar lagi; pada 34px, judul bagian terbaca sebagai
       sub-judul dari keduanya, padahal ia setingkat.

       Jarak hurufnya dirapatkan -.02em jadi -.032em: makin besar huruf, makin
       renggang jarak bawaannya terlihat. Leading pun ikut dirapatkan. */
    .kb-judul {
        font-family: 'Poppins', sans-serif; font-weight: 800; color: #1c1f26;
        font-size: 2.4rem; line-height: 1.06; letter-spacing: -.032em; margin: 0 0 12px;
        /* Judul dua baris terbagi rata, bukan satu baris penuh dengan satu kata
           menggantung sendirian di bawahnya. */
        text-wrap: balance;
    }
    /* Lebarnya dipersempit 62 jadi 54 karakter. Di bawah judul yang lebarnya
       terbatas, baris keterangan yang jauh lebih panjang membuat blok teksnya
       terlihat pincang — dan di atas 60 karakter mata mulai kehilangan awal
       baris berikutnya saat berpindah. */
    .kb-sub {
        color: #6b7280; font-size: 1.02rem; line-height: 1.6; margin: 0;
        max-width: 54ch; text-wrap: pretty;
    }
    .kb-tautan {
        display: inline-flex; align-items: center; gap: 8px; flex: 0 0 auto;
        color: #f26522; font-weight: 700; font-size: .95rem; text-decoration: none; white-space: nowrap;
    }
    .kb-tautan:hover { color: #d9531a; }
    .kb-tautan i.bi { line-height: 1; }
    .kb-tautan i.bi::before { display: block; line-height: 1; }
    .kb-kanan { display: flex; align-items: center; gap: 16px; flex: 0 0 auto; }

    @media (max-width: 991.98px) {
        .kb-judul { font-size: 2rem; }
    }
    @media (max-width: 767.98px) {
        .kb-kepala { flex-direction: column; align-items: flex-start; gap: 12px; margin-bottom: 22px; }
        .kb-judul { font-size: 1.7rem; letter-spacing: -.025em; margin-bottom: 10px; }
        .kb-sub { font-size: .95rem; }
        .kb-kicker { font-size: .7rem; letter-spacing: .14em; }
    }
</style>
@endonce

<div class="kb-kepala">
    <div class="kb-teks">
        @if ($kicker)
            <span class="kb-kicker">
                @if ($ikon)<i class="bi {{ $ikon }}"></i>@endif {{ $kicker }}
            </span>
        @endif
        <h2 class="kb-judul">{{ $judul }}</h2>
        @if ($sub)<p class="kb-sub">{{ $sub }}</p>@endif
    </div>

    {{-- Sisi kanan kepala bagian: tautan "Lihat Semua" dan, bila bagiannya
         memang bisa digeser, tombol panahnya. Keduanya dikelompokkan dalam satu
         pembungkus agar tetap sebaris dan tidak saling menjauh saat judulnya
         panjang. --}}
    @if ($tautanUrl || isset($aksi))
        <div class="kb-kanan">
            @if ($tautanUrl)
                <a href="{{ $tautanUrl }}" class="kb-tautan">{{ $tautanTeks }} <i class="bi bi-arrow-right"></i></a>
            @endif
            {{ $aksi ?? '' }}
        </div>
    @endif
</div>
