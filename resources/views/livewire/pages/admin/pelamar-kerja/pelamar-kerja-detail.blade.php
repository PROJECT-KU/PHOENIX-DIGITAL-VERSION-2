@section('title')
Detail Pelamar Kerja || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*): kepala hero,
         kisi 12 kolom, kartu putih datar berkepala ikon. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.pelamar-kerja.partials.berkas-gaya')

    {{-- Gaya khusus layar ini, ditulis INLINE: public/build di-gitignore. --}}
    <style>
    .pld-lencana-kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-top: 7px; }
    .pld-nomor { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }

    /* ===== Pembaca berkas mengisi tinggi kartunya =====

       .dsb-rak meregangkan semua kartu dalam satu baris setinggi yang
       tertinggi. Tinggi bingkai pembaca dipatok dengan vh, jadi ia MENGECIL
       di jendela pendek sementara kolom kanan — yang tingginya ditentukan
       tulisan — tidak ikut mengecil. Selisihnya jatuh sebagai putih di dalam
       kartu, di bawah tombol Unduh: diukur 1px di jendela 1500px, 83px di
       1100px, dan 228px di 900px. Makin pendek layarnya, makin besar
       lubangnya — dan di monitor tinggi tempat orang mengembangkan, ia
       praktis tidak terlihat.

       Rantai flex dari kartu sampai bingkainya disambung supaya ruang itu
       dipakai menggambar halaman lebih besar, bukan dibiarkan kosong.
       min-height menjaga bingkainya tetap layak saat kolom kanan justru
       lebih pendek — mis. di layar sempit, tempat kedua kolom bertumpuk.

       Tingginya tidak lepas tanpa batas: ia berhenti di tinggi kolom kanan,
       yang ditentukan tulisan dan tidak ikut tinggi layar.

       Lingkupnya SATU kartu lewat .pld-kartu-berkas, bukan .dsb-kartu umum:
       menjadikan semua kartu flex column akan mengubah tata letak kartu lain
       di layar ini tanpa ada yang memintanya.

       Dua cara lain sudah dicoba dan ditolak, jadi jangan diulang:
       meregangkan kartunya saja (isinya tidak ikut melar, lubangnya tetap),
       dan align-self: start (putih di dalam kartu memang hilang, tapi
       pindah jadi lubang abu-abu di antara kartu). */
    .pld-kartu-berkas { display: flex; flex-direction: column; }
    .pld-kartu-berkas > .dsb-kartu-isi {
        flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column;
    }
    .pld-kartu-berkas .pbk { flex: 1 1 auto; min-height: 0; }
    .pld-kartu-berkas .pbk-bingkai {
        height: auto; flex: 1 1 auto; min-height: min(58vh, 620px);
    }

    /* ===== Keterangan pelamar =====
       Baris yang isinya kosong TETAP tergambar supaya tiap keterangan selalu
       di tempat yang sama — mata mencari "telepon" di posisi, bukan dengan
       membaca ulang seluruh daftarnya. */
    .pld-info { display: flex; flex-direction: column; }
    .pld-info-baris {
        display: flex; align-items: center; gap: 11px; min-width: 0;
        padding: 10px 0; border-bottom: 1px solid #f5f7fa;
    }
    .pld-info-baris:last-child { border-bottom: 0; padding-bottom: 2px; }
    .pld-info-ikon {
        flex: 0 0 auto; width: 32px; height: 32px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .86rem; color: var(--c, #64748b);
        background: color-mix(in srgb, var(--c, #64748b) 11%, #fff);
    }
    .pld-info-teks { min-width: 0; display: flex; flex-direction: column; gap: 1px; }
    .pld-info-label {
        font-size: .7rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
        color: #94a3b8;
    }
    .pld-info-nilai {
        font-weight: 700; font-size: .85rem; color: var(--dsb-tinta, #1c1f26);
        overflow-wrap: anywhere; text-decoration: none;
    }
    a.pld-info-nilai { color: #1d4ed8; }
    a.pld-info-nilai:hover { text-decoration: underline; }
    .pld-info-nilai.is-kosong { font-weight: 500; color: #cbd5e1; }
    .pld-info-nilai.is-tunggu { font-weight: 600; color: #b45309; }

    /* ===== Tahap seleksi: perjalanan tegak =====

       Lima pil yang membungkus jadi dua baris cuma memberi tahu "ada lima
       pilihan". Yang sebenarnya dilakukan orang di sini adalah memindahkan
       seseorang di sepanjang sebuah alur, jadi urutannya yang harus terbaca —
       dan tegak menyediakan ruang untuk kalimat yang akan dibaca PELAMAR,
       supaya yang menekan tahu apa yang akan dilihat orang yang dinilainya. */
    .pld-alur { list-style: none; margin: 0 0 16px; padding: 0; }
    .pld-alur > li { position: relative; }
    /* Garis penghubung antar-tahap: ditarik dari bulatan ke bulatan
       berikutnya, bukan sepanjang barisnya — kalau tidak, ia menjulur
       melewati tahap terakhir dan terbaca seperti alur yang belum selesai. */
    .pld-alur > li:not(:last-child)::after {
        content: ''; position: absolute; left: 25px; top: 38px; bottom: 2px;
        width: 2px; background: #eef2f7; border-radius: 2px;
    }
    .pld-alur-baris {
        position: relative; z-index: 1;
        display: flex; align-items: flex-start; gap: 11px; width: 100%;
        padding: 8px 10px; margin-bottom: 2px; text-align: left; cursor: pointer;
        background: transparent; border: 1px solid transparent; border-radius: 13px;
        transition: background .15s ease, border-color .15s ease;
    }
    .pld-alur-baris:hover:not(:disabled) { background: #f8fafc; }
    .pld-alur-baris:disabled { cursor: default; }
    .pld-alur-bulat {
        flex: 0 0 auto; width: 32px; height: 32px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .86rem; background: #f1f5f9; color: #94a3b8;
        border: 2px solid #fff; box-shadow: 0 0 0 1px #eef2f7;
        transition: background .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .pld-alur-teks { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
    .pld-alur-judul {
        display: flex; align-items: center; gap: 7px; flex-wrap: wrap;
        font-size: .86rem; font-weight: 700; color: #475569;
    }
    .pld-alur-ket { font-size: .76rem; line-height: 1.5; color: var(--dsb-redup, #6b7280); }
    /* Tahap yang TERSIMPAN (keadaan sebenarnya) diberi warnanya. */
    .pld-alur-baris.is-tersimpan .pld-alur-bulat { background: var(--c); color: #fff; box-shadow: 0 0 0 1px var(--c); }
    .pld-alur-baris.is-tersimpan .pld-alur-judul { color: var(--dsb-tinta, #1c1f26); }
    .pld-alur-kini {
        font-size: .64rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        padding: 2px 7px; border-radius: 999px;
        background: color-mix(in srgb, var(--c) 14%, #fff); color: var(--c);
    }
    /* Tahap yang sedang DIPILIH tapi belum disimpan: dibedakan dari yang
       tersimpan, karena menekan tahap belum berarti tahapnya berpindah. */
    .pld-alur-baris.is-dipilih {
        background: color-mix(in srgb, var(--c) 7%, #fff);
        border-color: color-mix(in srgb, var(--c) 32%, #fff);
    }
    .pld-alur-baris.is-dipilih:not(.is-tersimpan) .pld-alur-bulat {
        background: #fff; color: var(--c); box-shadow: 0 0 0 2px var(--c);
    }
    .pld-alur-baris.is-dipilih .pld-alur-judul { color: var(--dsb-tinta, #1c1f26); }

    .pld-bantu { color: var(--dsb-redup); font-size: .8rem; line-height: 1.6; margin: 0 0 14px; }
    .pld-bantu:last-child { margin-bottom: 0; }

    .pld-cek { display: flex; align-items: flex-start; gap: 9px; margin: 12px 0 0; font-size: .83rem; cursor: pointer; }
    .pld-cek input { margin-top: 3px; width: 16px; height: 16px; flex-shrink: 0; accent-color: #7c3aed; }

    .pld-kaki { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    /* Isi tombol yang berganti-ganti (teks biasa ↔ "Menyimpan…").

       Kelasnya LOKAL, bukan .orcha-ikon-teks: kelas itu hidup di berkas gaya
       Orcha, dan layar ini tidak memuatnya — ikon dan tulisannya lalu
       menempel rapat tanpa ada satu pun tanda bahwa gayanya hilang. */
    .pld-tombol-isi { display: inline-flex; align-items: center; gap: .4rem; }
    .pld-tombol-isi > i.bi { line-height: 1; flex: 0 0 auto; }

    /* Kolom kanan: dua kartu bertumpuk dalam satu petak kisi.

       Tanpa ini kartu "Sudah Sampai Mana" dipaksa setinggi pembaca berkas di
       sebelahnya (align-items: stretch milik .dsb-rak) dan menyisakan ruang
       kosong sepanjang layar — terbaca sebagai halaman yang gagal memuat
       sesuatu. Jaraknya disamakan dengan gap raknya. */
    .pld-kolom { display: flex; flex-direction: column; gap: clamp(12px, 1.2vw, 16px); min-width: 0; }

    /* ===== Pemberitahuan sebaris ===== */
    .pld-kabar {
        display: flex; align-items: flex-start; gap: 9px; flex-wrap: wrap;
        padding: 11px 13px; border-radius: 12px; font-size: .82rem; line-height: 1.55;
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;
    }
    .pld-kabar > i.bi { flex-shrink: 0; line-height: 1.5; }
    .pld-kabar.is-ingat { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .pld-kabar.is-kunci { background: #f8fafc; border-color: var(--dsb-tepi, #e9edf3); color: var(--dsb-redup, #6b7280); }
    .pld-kabar + .pld-kabar { margin-top: 9px; }
    .pld-kabar-isi { min-width: 0; flex: 1 1 240px; }
    .pld-kabar a { color: inherit; font-weight: 700; }

    /* ===== Riwayat =====
       Garis waktu sederhana: titik + garis penghubung. Tiap baris menyebut
       siapa dan kapan, karena pertanyaan yang dibawa orang ke layar ini
       adalah "siapa yang memutuskan ini". */
    .pld-jejak { list-style: none; margin: 0; padding: 0; }
    .pld-jejak-baris { position: relative; padding: 0 0 16px 26px; }
    .pld-jejak-baris::before {
        content: ''; position: absolute; left: 4px; top: 5px;
        width: 9px; height: 9px; border-radius: 50%;
        background: var(--c, #94a3b8); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c, #94a3b8) 18%, #fff);
    }
    .pld-jejak-baris::after {
        content: ''; position: absolute; left: 8px; top: 18px; bottom: 0;
        width: 1px; background: #e9edf3;
    }
    .pld-jejak-baris:last-child { padding-bottom: 0; }
    .pld-jejak-baris:last-child::after { display: none; }
    .pld-jejak-judul { font-size: .85rem; font-weight: 700; color: var(--dsb-tinta); }
    .pld-jejak-meta { font-size: .76rem; color: var(--dsb-redup); margin-top: 1px; }
    .pld-jejak-isi {
        margin-top: 6px; padding: 9px 11px; border-radius: 10px;
        background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
        font-size: .83rem; line-height: 1.6; color: #334155; white-space: pre-line; overflow-wrap: anywhere;
    }

    /* ===== Lamaran lain dari orang yang sama ===== */
    .pld-lain { display: flex; flex-direction: column; gap: 7px; margin-top: 9px; }
    .pld-lain a {
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
        padding: 8px 11px; border-radius: 10px; text-decoration: none;
        background: #fff; border: 1px solid #bfdbfe; color: #1e3a8a; font-size: .82rem;
    }
    .pld-lain a:hover { background: #eff6ff; }
    </style>

    @php
        // Dirakit di sini: perbandingan di dalam direktif blok membuat penanda
        // morph Livewire timpang (PenandaMorphAdminTest).
        $daftarStatus = \App\Models\JobApplication::STATUS;
        $statusSama = $statusBaru === $pelamar->status;
        $adaTemplatSurel = \App\Mail\StatusLamaranMail::adaTemplat($statusBaru);
        $diubahLabel = $pelamar->status_diubah_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm');
        $bisaUbah = $this->bisaUbah;

        $warnaBs = [
            'secondary' => '#64748b', 'primary' => '#4f46e5', 'info' => '#0284c7',
            'success' => '#16a34a', 'danger' => '#dc2626', 'warning' => '#d97706',
        ];
        $lencanaBs = [
            'secondary' => 'is-abu', 'primary' => 'is-ungu', 'info' => 'is-biru',
            'success' => 'is-hijau', 'danger' => 'is-merah', 'warning' => 'is-kuning',
        ];

        // Ikon per tahap, sama dengan yang dipakai tab di layar daftar supaya
        // bentuk yang sama berarti hal yang sama di dua layar.
        $ikonTahap = [
            'baru' => 'bi-inbox-fill', 'diseleksi' => 'bi-file-earmark-text',
            'wawancara' => 'bi-chat-dots-fill', 'diterima' => 'bi-check-circle-fill',
            'ditolak' => 'bi-slash-circle',
        ];

        $jenisJejak = [
            'tahap' => ['bi-signpost-split', 'Tahap diubah'],
            'catatan' => ['bi-journal-text', 'Catatan tim'],
            'surel' => ['bi-envelope', 'Surel pemberitahuan'],
        ];
    @endphp

    <div class="dsb pld-detail">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam text-capitalize">{{ $pelamar->name }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-calendar3 me-1"></i>Melamar
                        {{ $pelamar->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY · HH:mm') }} WIB
                    </span>
                    <span class="d-block pld-lencana-kepala">
                        <span class="dsb-lencana is-abu pld-nomor">{{ $pelamar->nomor_lamaran ?? '—' }}</span>
                        <span class="dsb-lencana is-biru text-capitalize">
                            <i class="bi bi-briefcase"></i>{{ $pelamar->posisi_label }}
                        </span>
                        <span class="dsb-lencana {{ $lencanaBs[$pelamar->status_warna] ?? 'is-abu' }}">
                            {{ $pelamar->status_label }}
                        </span>
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.pelamar.index') }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
                @if ($pelamar->job)
                    {{-- Tautan ke lowongannya. Pertanyaan "sebenarnya posisi ini
                         mintanya apa?" muncul justru saat sedang membaca CV, dan
                         tanpa tautan ini jawabannya harus dicari lewat menu. --}}
                    <a href="{{ route('admin.lowongan.edit', $pelamar->job->id) }}" wire:navigate
                        class="dsb-tombol is-lembut" style="--ikon: #2563eb" title="Lihat lowongan yang dilamar">
                        <i class="bi bi-briefcase"></i><span>Lowongannya</span>
                    </a>
                @endif
                @if ($pelamar->phone)
                    {{-- Yang paling sering dilakukan sesudah membaca CV adalah
                         menghubungi orangnya. Nomornya sudah ada di layar;
                         merakit tautannya sendiri hanya menambah langkah. --}}
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $pelamar->phone)) }}"
                        target="_blank" rel="noopener" class="dsb-tombol is-aman">
                        <i class="bi bi-whatsapp"></i><span>Hubungi</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== BERKAS + TAHAP ==================

             Berkasnya SEJAJAR dengan tombol tahap, bukan di dasar halaman.
             Dulu Berkas Lamaran kartu paling bawah: menilai lalu memutuskan
             berarti menggulung ke bawah membaca CV, lalu menggulung ke atas
             lagi untuk menekan tahapnya — bolak-balik untuk tiap pelamar. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-7 pld-kartu-berkas">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-file-earmark-text"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Berkas Lamaran</h2>
                            <span class="dsb-kartu-sub">
                                Dibaca langsung di sini — tersimpan sampai lamaran ini dihapus
                            </span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @include('livewire.pages.admin.pelamar-kerja.partials.berkas-pratinjau', [
                        'pelamar' => $pelamar,
                        'aktif' => $berkasAktif,
                        'aksi' => 'bukaBerkas',
                    ])
                </div>
            </div>

            <div class="k-5 pld-kolom">
            <div class="dsb-kartu">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-signpost-split"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Sudah Sampai Mana</h2>
                            <span class="dsb-kartu-sub">Pelamar melihat tahap ini di halaman Cek Status situs</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @if ($diubahLabel)
                        <p class="pld-bantu">Terakhir diubah {{ $diubahLabel }} WIB.</p>
                    @endif

                    @unless ($bisaUbah)
                        <div class="pld-kabar is-kunci" style="margin-bottom: 14px">
                            <i class="bi bi-lock"></i>
                            <span class="pld-kabar-isi">
                                Anda bisa membaca lamaran ini, tapi tidak mengubah tahapnya.
                                Mengubah tahap ikut mengirim surel ke pelamar, jadi haknya
                                dipisah — mintalah izin <strong>Edit Pelamar</strong> bila perlu.
                            </span>
                        </div>
                    @endunless

                    {{-- Tahap seleksi: perjalanan tegak, bukan deret pil.

                         Lima pil yang membungkus jadi dua baris tidak
                         memperlihatkan apa pun selain "ada lima pilihan" —
                         padahal yang sedang dilakukan orang di sini adalah
                         memindahkan seseorang di sepanjang sebuah ALUR, dan
                         urutannya itulah yang perlu terbaca. Tegak juga memberi
                         ruang untuk kalimat yang akan DIBACA PELAMAR, sehingga
                         yang menekan tahu persis apa yang akan dilihat orang
                         yang dinilainya. --}}
                    <ol class="pld-alur" role="radiogroup" aria-label="Tahap seleksi">
                        @foreach ($daftarStatus as $kunciStatus => $infoStatus)
                            @php
                                $iniDipilih = $statusBaru === $kunciStatus;
                                $iniTersimpan = $pelamar->status === $kunciStatus;
                            @endphp
                            <li wire:key="tahap-{{ $kunciStatus }}">
                                <button type="button" wire:click="$set('statusBaru', '{{ $kunciStatus }}')"
                                    style="--c: {{ $warnaBs[$infoStatus[1]] ?? '#64748b' }}"
                                    class="pld-alur-baris {{ $iniDipilih ? 'is-dipilih' : '' }} {{ $iniTersimpan ? 'is-tersimpan' : '' }}"
                                    role="radio" aria-checked="{{ $iniDipilih ? 'true' : 'false' }}"
                                    @disabled(! $bisaUbah)>
                                    <span class="pld-alur-bulat">
                                        <i class="bi {{ $ikonTahap[$kunciStatus] ?? 'bi-circle' }}"></i>
                                    </span>
                                    <span class="pld-alur-teks">
                                        <span class="pld-alur-judul">
                                            {{ $infoStatus[0] }}
                                            @if ($iniTersimpan)
                                                <span class="pld-alur-kini">sekarang</span>
                                            @endif
                                        </span>
                                        <span class="pld-alur-ket">{{ $infoStatus[2] }}</span>
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ol>

                    @if ($bisaUbah && $adaTemplatSurel)
                        <div class="dsb-medan">
                            <label class="dsb-label" for="pesanPelamar">
                                Pesan untuk pelamar
                                <span class="dsb-label-ket">(boleh dikosongkan — ikut di surel)</span>
                            </label>
                            <textarea id="pesanPelamar" rows="3" wire:model="pesanPelamar" maxlength="2000"
                                class="dsb-isian @error('pesanPelamar') is-galat @enderror"
                                placeholder="mis. Wawancara Senin, 17 Nov 2026 pukul 10.00 WIB lewat Google Meet. Tautan menyusul.">{{ $pesanPelamar }}</textarea>
                            @error('pesanPelamar') <span class="dsb-galat">{{ $message }}</span> @enderror
                        </div>

                        <label class="pld-cek" for="kirimEmail">
                            <input type="checkbox" id="kirimEmail" wire:model="kirimEmail" @checked($kirimEmail)>
                            <span>Kirim surel pemberitahuan ke <strong>{{ $pelamar->email }}</strong></span>
                        </label>
                    @endif

                    @if ($bisaUbah)
                        <div class="pld-kaki">
                            <button type="button" class="dsb-tombol is-utama" wire:click="simpanStatus"
                                wire:loading.attr="disabled" wire:target="simpanStatus" @disabled($statusSama)>
                                <span wire:loading.remove wire:target="simpanStatus" class="pld-tombol-isi">
                                    <i class="bi bi-check2-circle"></i> Simpan tahap
                                </span>
                                <span wire:loading wire:target="simpanStatus" class="pld-tombol-isi">
                                    <span class="dsb-putar is-kecil"></span> Menyimpan…
                                </span>
                            </button>
                            @if ($statusSama)
                                <span class="pld-bantu" style="align-self: center; margin: 0">
                                    Pilih tahap yang berbeda dulu.
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="dsb-kartu">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-person-vcard"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Keterangan Pelamar</h2>
                            <span class="dsb-kartu-sub">Yang dia isi sendiri di formulir</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    {{-- Label di ATAS nilainya, bukan berseberangan.

                         Berseberangan kiri-kanan, alamat email panjang di kolom
                         sempit menabrak labelnya sendiri lalu patah di tengah
                         kata. Label di atas memberi nilai itu lebar penuh, dan
                         ikon berwarna membuat tiap baris bisa dikenali tanpa
                         membaca labelnya lebih dulu.

                         Nilai yang bisa DILAKUKAN sesuatu dibuat tautan: yang
                         dilakukan sesudah membaca CV hampir selalu menghubungi
                         orangnya, dan menyalin alamat dengan tangan adalah
                         langkah yang tidak perlu ada. --}}
                    @php
                        $wa = $pelamar->phone
                            ? 'https://wa.me/'.preg_replace('/^0/', '62', preg_replace('/\D/', '', $pelamar->phone))
                            : null;

                        $dibukaLabel = $pelamar->berkas_dibuka_at
                            ? ($pelamar->berkas_dibuka_oleh ?: 'seseorang').' · '
                                .$pelamar->berkas_dibuka_at->locale('id')->isoFormat('D MMM YYYY, HH:mm')
                            : null;

                        $keterangan = [
                            ['bi-envelope', '#2563eb', 'Email', $pelamar->email, $pelamar->email ? 'mailto:'.$pelamar->email : null, false],
                            ['bi-telephone', '#16a34a', 'Telepon', $pelamar->phone, $wa, true],
                            ['bi-link-45deg', '#7c3aed', 'Portofolio', $pelamar->portofolio_url, $pelamar->portofolio_url, true],
                            ['bi-megaphone', '#d97706', 'Tahu lowongan ini dari', $pelamar->sumber_info_label, null, false],
                            ['bi-eye', '#0284c7', 'Berkas dibuka', $dibukaLabel, null, false],
                            ['bi-pc-display', '#64748b', 'Alamat IP saat melamar', $pelamar->ip_address, null, false],
                        ];
                    @endphp

                    <div class="pld-info">
                        @foreach ($keterangan as [$ikon, $warna, $label, $nilai, $tautan, $keLuar])
                            <div class="pld-info-baris" wire:key="info-{{ $loop->index }}">
                                <span class="pld-info-ikon" style="--c: {{ $warna }}">
                                    <i class="bi {{ $ikon }}"></i>
                                </span>
                                <span class="pld-info-teks">
                                    <span class="pld-info-label">{{ $label }}</span>
                                    @if ($nilai && $tautan)
                                        <a class="pld-info-nilai" href="{{ $tautan }}"
                                            @if ($keLuar) target="_blank" rel="noopener noreferrer" @endif>
                                            {{ $nilai }}
                                        </a>
                                    @elseif ($nilai)
                                        <span class="pld-info-nilai">{{ $nilai }}</span>
                                    @elseif ($label === 'Berkas dibuka')
                                        {{-- Dibedakan dari "tidak diisi": ini bukan
                                             isian yang dikosongkan pelamar, melainkan
                                             sesuatu yang belum terjadi di pihak kita. --}}
                                        <span class="pld-info-nilai is-tunggu">belum ada yang membukanya</span>
                                    @else
                                        <span class="pld-info-nilai is-kosong">tidak diisi</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @if ($lamaranLain->isNotEmpty())
                        {{-- Orang yang sama pernah melamar sebelumnya. Dicocokkan
                             lewat email. Tanpa ini, riwayat seleksinya yang lalu
                             — termasuk alasan ditolak — tidak pernah ikut terbaca
                             saat memutuskan yang sekarang. --}}
                        <div class="pld-kabar" style="margin-top: 14px">
                            <i class="bi bi-people"></i>
                            <span class="pld-kabar-isi">
                                <strong>Pernah melamar sebelumnya.</strong>
                                Email yang sama tercatat di {{ $lamaranLain->count() }} lamaran lain.
                                <span class="pld-lain">
                                    @foreach ($lamaranLain as $lain)
                                        <a wire:navigate href="{{ route('admin.pelamar.detail', $lain->id) }}"
                                            wire:key="lain-{{ $lain->id }}">
                                            <span class="dsb-lencana is-biru text-capitalize">{{ $lain->posisi_label }}</span>
                                            <span class="dsb-lencana {{ $lencanaBs[$lain->status_warna] ?? 'is-abu' }}">{{ $lain->status_label }}</span>
                                            <span>{{ $lain->created_at->locale('id')->isoFormat('D MMM YYYY') }}</span>
                                        </a>
                                    @endforeach
                                </span>
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </div>

        {{-- ================== RIWAYAT ================== --}}
        <div class="dsb-rak dsb-bagian">
            {{-- ================== RIWAYAT & CATATAN ==================

                 Satu kartu, bukan dua: catatan tim DAN perubahan tahap adalah
                 jawaban untuk pertanyaan yang sama — "apa yang sudah terjadi
                 pada lamaran ini, oleh siapa". Dipisah, keduanya harus dibaca
                 bergantian sambil mencocokkan tanggal sendiri. --}}
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-journal-text"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Riwayat &amp; Catatan Tim</h2>
                            <span class="dsb-kartu-sub">Hanya terlihat di lemon — tidak pernah dikirim ke pelamar</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @if ($bisaUbah)
                        <div class="dsb-medan">
                            <label class="dsb-label" for="catatanBaru">Tambah catatan</label>
                            <textarea id="catatanBaru" rows="3" wire:model="catatanBaru" maxlength="5000"
                                class="dsb-isian @error('catatanBaru') is-galat @enderror"
                                placeholder="Kesan wawancara, nilai tes, rekomendasi…">{{ $catatanBaru }}</textarea>
                            @error('catatanBaru') <span class="dsb-galat">{{ $message }}</span> @enderror
                        </div>

                        <div class="pld-kaki" style="margin-top: 10px">
                            <button type="button" class="dsb-tombol is-lembut" style="--ikon: #d97706"
                                wire:click="simpanCatatan" wire:loading.attr="disabled" wire:target="simpanCatatan">
                                <span wire:loading.remove wire:target="simpanCatatan" class="pld-tombol-isi">
                                    <i class="bi bi-plus-lg"></i> Simpan catatan
                                </span>
                                <span wire:loading wire:target="simpanCatatan" class="pld-tombol-isi">
                                    <span class="dsb-putar is-kecil"></span> Menyimpan…
                                </span>
                            </button>
                            <span class="pld-bantu" style="align-self: center; margin: 0">
                                Catatan lama tidak tertimpa — tiap catatan berdiri sendiri.
                            </span>
                        </div>
                    @endif

                    @if ($jejak->isEmpty())
                        <div class="dsb-kosong" style="padding: 22px 0 6px">
                            <span class="dsb-kosong-ikon"><i class="bi bi-clock-history"></i></span>
                            <p class="dsb-kosong-judul">Belum ada riwayat</p>
                            <p class="dsb-kosong-ket">
                                Perubahan tahap, catatan tim, dan hasil pengiriman surel akan tercatat di sini.
                            </p>
                        </div>
                    @else
                        <ul class="pld-jejak" style="margin-top: {{ $bisaUbah ? '20px' : '4px' }}">
                            @foreach ($jejak as $baris)
                                @php
                                    [$ikonJejak, $judulJejak] = $jenisJejak[$baris->jenis] ?? ['bi-dot', 'Catatan'];
                                    $warnaJejak = $baris->ke_status
                                        ? ($warnaBs[$daftarStatus[$baris->ke_status][1] ?? 'secondary'] ?? '#94a3b8')
                                        : '#94a3b8';
                                @endphp
                                <li class="pld-jejak-baris" wire:key="jejak-{{ $baris->id }}" style="--c: {{ $warnaJejak }}">
                                    <div class="pld-jejak-judul">
                                        <i class="bi {{ $ikonJejak }}"></i>
                                        @if ($baris->jenis === 'tahap')
                                            {{ $daftarStatus[$baris->dari_status][0] ?? $baris->dari_status ?? '—' }}
                                            →
                                            {{ $daftarStatus[$baris->ke_status][0] ?? $baris->ke_status }}
                                        @else
                                            {{ $judulJejak }}
                                        @endif
                                    </div>
                                    <div class="pld-jejak-meta">
                                        {{ $baris->penulis_label }} · {{ $baris->waktu_label }} WIB
                                    </div>
                                    @if ($baris->isi)
                                        <div class="pld-jejak-isi">{{ $baris->isi }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
