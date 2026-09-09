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
        gap: 20px; margin-bottom: 24px;
    }
    .kb-teks { min-width: 0; }
    .kb-kicker {
        display: inline-flex; align-items: center; gap: 7px; margin-bottom: 10px;
        background: #fff3e6; color: #d9531a; border-radius: 999px; padding: 5px 12px;
        font-size: .68rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase;
    }
    .kb-kicker i.bi { font-size: .76rem; line-height: 1; }
    .kb-kicker i.bi::before { display: block; line-height: 1; }
    .kb-judul {
        font-family: 'Poppins', sans-serif; font-weight: 800; color: #23272f;
        font-size: 2rem; line-height: 1.15; margin: 0 0 6px;
    }
    .kb-sub { color: #6b7280; font-size: .95rem; line-height: 1.6; margin: 0; max-width: 62ch; }
    .kb-tautan {
        display: inline-flex; align-items: center; gap: 8px; flex: 0 0 auto;
        color: #f26522; font-weight: 700; font-size: .95rem; text-decoration: none; white-space: nowrap;
    }
    .kb-tautan:hover { color: #d9531a; }
    .kb-tautan i.bi { line-height: 1; }
    .kb-tautan i.bi::before { display: block; line-height: 1; }

    @media (max-width: 767.98px) {
        .kb-kepala { flex-direction: column; align-items: flex-start; gap: 10px; margin-bottom: 18px; }
        .kb-judul { font-size: 1.5rem; }
        .kb-sub { font-size: .9rem; }
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

    @if ($tautanUrl)
        <a href="{{ $tautanUrl }}" class="kb-tautan">{{ $tautanTeks }} <i class="bi bi-arrow-right"></i></a>
    @endif
</div>
