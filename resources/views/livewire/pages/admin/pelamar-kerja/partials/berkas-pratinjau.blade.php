{{-- Pembaca berkas pelamar: SATU jendela, tiga berkas, bertab.

     Dulu ketiganya kartu bertumpuk masing-masing setinggi A4 — membaca CV
     lalu surat lamarannya berarti menggulung tiga layar penuh, dan berkas
     pendukung yang kosong tetap memakan satu kartu.

     Berkasnya SELALU PDF (formulir situs menolak yang lain lewat
     mimetypes:application/pdf), jadi peramban pasti bisa menggambarnya —
     tidak ada cabang "kalau bukan PDF".

     Butuh:
       $pelamar  — App\Models\JobApplication
       $aktif    — kunci berkas yang sedang dibuka ('cv'|'surat'|'lampiran')
       $aksi     — nama metode Livewire untuk berpindah tab
--}}
@php
    /*
     | Dirakit di PHP, bukan lewat direktif bersarang: direktif di dalam
     | direktif membuat penanda morph Livewire timpang (PenandaMorphAdminTest).
     */
    $daftarBerkas = [
        ['cv', 'CV', 'bi-file-earmark-person', $pelamar->cv_path,
            $pelamar->cv_path ? route('admin.pelamar.cv', $pelamar) : null],
        ['surat', 'Surat Lamaran', 'bi-envelope-paper', $pelamar->cover_letter_path,
            $pelamar->cover_letter_path ? route('admin.pelamar.surat', $pelamar) : null],
        ['lampiran', 'Berkas Pendukung', 'bi-paperclip', $pelamar->lampiran_path,
            $pelamar->lampiran_path ? route('admin.pelamar.lampiran', $pelamar) : null],
    ];

    $adaBerkas = collect($daftarBerkas)->contains(fn ($b) => (bool) $b[3]);
    $terpilih = collect($daftarBerkas)->firstWhere(0, $aktif) ?: $daftarBerkas[0];
    $urlAktif = $terpilih[4];

    /*
     | Berkas yang SUDAH DIHAPUS sesuai masa simpan dibedakan dari yang memang
     | tidak pernah dilampirkan. Keduanya sama-sama kosong di layar, tetapi
     | artinya berbeda — dan hanya satu yang bisa diminta ulang ke orangnya.
     | CV wajib di formulir situs, jadi CV yang hilang berarti sudah dipangkas.
     */
    $sudahDipangkas = $pelamar->cv_path === null && $pelamar->created_at?->lt(now()->subDays(90));
@endphp

<div class="pbk">
    @if ($sudahDipangkas)
        <div class="pbk-kabar">
            <i class="bi bi-archive"></i>
            <span>
                <strong>Berkasnya sudah dihapus.</strong>
                CV dan surat lamaran dihapus otomatis setelah masa simpannya lewat —
                catatan lamarannya tetap disimpan. Kalau masih dibutuhkan, minta
                {{ $pelamar->name }} mengirimkannya lagi.
            </span>
        </div>
    @elseif (! $adaBerkas)
        <div class="dsb-kosong">
            <span class="dsb-kosong-ikon"><i class="bi bi-file-earmark-x"></i></span>
            <p class="dsb-kosong-judul">Tidak ada berkas</p>
            <p class="dsb-kosong-ket">Pelamar ini tidak melampirkan berkas apa pun.</p>
        </div>
    @else
        <div class="pbk-tab" role="tablist">
            @foreach ($daftarBerkas as [$kunci, $label, $ikon, $jalur, $url])
                @php $iniAktif = $aktif === $kunci; @endphp
                <button type="button" wire:click="{{ $aksi }}('{{ $kunci }}')"
                    class="pbk-tombol {{ $iniAktif ? 'is-aktif' : '' }} {{ $jalur ? '' : 'is-kosong' }}"
                    role="tab" aria-selected="{{ $iniAktif ? 'true' : 'false' }}"
                    @disabled(! $jalur)
                    title="{{ $jalur ? 'Baca '.$label : $label.' tidak dilampirkan' }}">
                    <i class="bi {{ $ikon }}"></i>
                    <span>{{ $label }}</span>
                    @if (! $jalur)
                        <span class="pbk-tanda">—</span>
                    @endif
                </button>
            @endforeach
        </div>

        @if ($urlAktif)
            {{-- Digambar SENDIRI lewat pdf.js (lihat berkas-gaya), bukan
                 diserahkan ke penampil PDF peramban.

                 <iframe src="berkas.pdf"> bergantung pada setelan peramban:
                 Chrome yang disetel "Download PDFs instead of automatically
                 opening them" menggantinya dengan kotak abu bertombol
                 "Open", dan isinya tidak pernah terbaca tanpa satu klik
                 lagi. Setelan itu ada di mesin tiap admin, bukan di tangan
                 kita.

                 wire:key memuat kunci berkasnya: tanpa itu Livewire memakai
                 ulang wadah yang sama saat tab berganti, dan halaman CV
                 tetap tergambar di tab Surat Lamaran. --}}
            <div class="pbk-bingkai" data-pdf="{{ $urlAktif }}"
                wire:key="pbk-{{ $pelamar->id }}-{{ $aktif }}"
                aria-label="{{ $terpilih[1] }} {{ $pelamar->name }}">
                <p class="pbk-muat">Membuka berkas…</p>
            </div>

            <div class="pbk-kaki">
                <span class="pbk-kaki-ket">
                    Perlu memperbesar atau mencetak? Pakai <em>Buka di tab baru</em>.
                </span>
                <span class="pbk-kaki-aksi">
                    <a href="{{ $urlAktif }}" target="_blank" rel="noopener"
                        class="dsb-tombol is-lembut" style="--ikon: #0284c7">
                        <i class="bi bi-box-arrow-up-right"></i><span>Buka di tab baru</span>
                    </a>
                    <a href="{{ $urlAktif }}" download class="dsb-tombol is-lembut" style="--ikon: #16a34a">
                        <i class="bi bi-download"></i><span>Unduh</span>
                    </a>
                </span>
            </div>
        @else
            <div class="dsb-kosong">
                <span class="dsb-kosong-ikon"><i class="bi {{ $terpilih[2] }}"></i></span>
                <p class="dsb-kosong-judul">{{ $terpilih[1] }} tidak dilampirkan</p>
                <p class="dsb-kosong-ket">
                    {{ $terpilih[0] === 'lampiran'
                        ? 'Berkas pendukung memang tidak wajib.'
                        : 'Pelamar tidak mengirimkannya.' }}
                </p>
            </div>
        @endif
    @endif
</div>
