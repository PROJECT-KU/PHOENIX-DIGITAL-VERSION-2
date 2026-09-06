@section('title')
Cek Bukti Pembayaran || lemon
@stop

@php
    // Berkas bukti tersimpan di Orcha, jadi jalurnya dilengkapi asal servernya.
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanBukti = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @if (empty($bukti))
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-cash-coin"></i></div>
                    <p class="text-muted mb-3">Bukti pembayaran ini tidak bisa dibuka.</p>
                    <a href="{{ route('admin.orcha.pembayaran') }}" class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-arrow-left"></i> Kembali ke daftar
                    </a>
                </div>
            </div>
        @else

            @php
                // Dihitung sekali, dipakai di seluruh lembar: dua jalur ini butuh
                // halaman yang berbeda, bukan halaman sama dengan sedikit tempelan.
                $lewatGerbang = ($bukti['kanal'] ?? 'transfer') === 'doku';
                $tagihanPesanan = $bukti['pesanan']['tagihan'] ?? [];
                $rincianAtas = $bukti['rincian'] ?? null;
            @endphp

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <a href="{{ route('admin.orcha.pembayaran') }}" wire:navigate class="orcha-tautan-balik mb-2">
                                <i class="bi bi-arrow-left"></i> Semua bukti pembayaran
                            </a>
                            {{-- Judulnya mengikuti pekerjaan yang benar-benar ada.

                                 "Cek Bukti Pembayaran" menyuruh admin memeriksa sesuatu.
                                 Untuk pembayaran gerbang tidak ada yang perlu diperiksa —
                                 uangnya sudah dipastikan sebelum barisnya lahir — dan judul
                                 yang menyuruh memeriksa membuat orang mencari-cari bukti
                                 yang memang tidak akan pernah ada. --}}
                            <h1 class="gradient-text fw-bold mb-1" style="font-size:1.5rem">
                                {{ $lewatGerbang ? 'Pembayaran Online' : 'Cek Bukti Pembayaran' }}
                            </h1>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="orcha-kode">{{ $bukti['kode'] }}</span>
                                <span class="text-muted" style="font-size:.82rem">
                                    {{ $bukti['jenis_label'] }} · dikirim
                                    {{ \Carbon\Carbon::parse($bukti['dibuat_pada'])->locale('id')->translatedFormat('j M Y, H:i') }}
                                </span>
                            </div>
                        </div>

                        <span class="badge orcha-lencana-bayar-{{ $bukti['status'] }}">
                            {{ $bukti['status_label'] }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3 p-lg-4">
    <div class="row g-4">
        {{-- Kolomnya melebar penuh saat panel bukti tidak ditampilkan.

             Membiarkannya tetap 7 dari 12 menyisakan separuh halaman kosong di
             kanan — dan ruang kosong sebesar itu terbaca sebagai sesuatu yang
             gagal dimuat, bukan sebagai kelegaan. --}}
        <div class="{{ $lewatGerbang ? 'col-12' : 'col-12 col-lg-7' }}">
            {{-- Nominal dibesarkan sendiri. Inilah angka yang dicocokkan
                 dengan mutasi rekening, dan salah baca satu digit di sini
                 berarti salah menyatakan pesanan sudah lunas. --}}
            <div class="orcha-cek-nominal">
                {{-- Angka besarnya adalah uang yang BENAR-BENAR MASUK, termasuk
                     kode uniknya — itu yang akan dicocokkan admin dengan mutasi
                     atau dashboard DOKU. Yang masuk ke tagihan lebih kecil, dan
                     dipecah tepat di bawahnya supaya selisihnya tidak pernah
                     jadi teka-teki. --}}
                <div class="orcha-label-kecil">
                    {{ $lewatGerbang ? 'Uang yang diterima' : 'Nominal yang dikirim' }}
                </div>
                <div class="angka">
                    Rp {{ number_format($rincianAtas['total'] ?? $bukti['nominal'], 0, ',', '.') }}
                </div>
                <div class="orcha-cek-tanggal">
                    <i class="bi bi-calendar-event"></i>
                    {{ $bukti['tanggal_transfer']
                        ? \Carbon\Carbon::parse($bukti['tanggal_transfer'])->locale('id')->translatedFormat('j F Y')
                        : 'tanggal transfer tidak diisi' }}
                </div>

                {{-- Pecahan angkanya, untuk pembayaran yang masuk lewat gerbang.

                     Satu angka gelondongan memaksa admin menerka berapa uang
                     mukanya dan berapa yang cuma penanda — dan pertanyaan itu
                     muncul persis saat ia menghitung sisa tagihan pelanggan.
                     Menerkanya menghasilkan sisa yang meleset beberapa ratus
                     rupiah, cukup untuk membuat dua orang berdebat tentang
                     siapa yang salah hitung.

                     Baris transfer manual tidak punya pecahan, dan memang tidak
                     seharusnya dikarang: nominalnya apa adanya seperti di
                     mutasi. --}}
                @if ($rincian = $bukti['rincian'] ?? null)
                    <div class="orcha-cek-pecahan">
                        <div class="baris">
                            <span>{{ $bukti['jenis_label'] }} — masuk ke tagihan</span>
                            <span>Rp {{ number_format($rincian['pokok'], 0, ',', '.') }}</span>
                        </div>
                        <div class="baris unik">
                            <span>Kode unik <em>(penanda, tidak mengurangi tagihan)</em></span>
                            <span>+ {{ number_format($rincian['kode_unik'], 0, ',', '.') }}</span>
                        </div>
                        <div class="tagihan">
                            <i class="bi bi-receipt-cutoff"></i> {{ $rincian['invoice'] }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="orcha-cek-kartu">
                <div class="kepala">Siapa &amp; pesanan apa</div>
            <div class="row g-3">
                @foreach ([
                    ['bi-person-badge', 'Pengirim', $bukti['atas_nama_pengirim'], null],
                    [
                        ($bukti['kanal'] ?? 'transfer') === 'doku' ? 'bi-credit-card-2-front' : 'bi-bank',
                        ($bukti['kanal'] ?? 'transfer') === 'doku' ? 'Metode pembayaran' : 'Bank pengirim',
                        $bukti['bank_pengirim'],
                        null,
                    ],
                    ['bi-person-circle', 'Pemesan', $bukti['pesanan']['nama'] ?? '—', $bukti['pesanan']['whatsapp'] ?? null],
                    ['bi-signpost-split', 'Pesanan', $bukti['pesanan']['keterangan'] ?? '—', null],
                ] as [$ikon, $label, $nilai, $tambahan])
                    <div class="col-6">
                        <div class="orcha-cek-fakta">
                            <span class="orcha-cek-ikon"><i class="bi {{ $ikon }}"></i></span>
                            <div>
                                <div class="orcha-label-kecil">{{ $label }}</div>
                                <div class="fw-bold">{{ $nilai }}</div>
                                @if ($tambahan)
                                    <div class="text-muted" style="font-size:.76rem">
                                        {{ $tambahan }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            </div>

            @if ($lewatGerbang)
                {{-- Kalimat yang MENUTUP pekerjaan, bukan yang menerangkannya.

                     Admin yang membuka lembar ini sedang bertanya "saya harus
                     apa?". Untuk pembayaran gerbang jawabannya "tidak ada", dan
                     jawaban itu harus datang lebih dulu daripada rinciannya —
                     kalau tidak, ia terlanjur membaca semuanya untuk mencari
                     pekerjaan yang tidak ada. --}}
                <div class="orcha-cek-beres">
                    {{-- Centangnya SVG, bukan glif bootstrap-icons.

                         Glif bi-check-lg digambar di bagian atas kotak em-nya,
                         jadi line-height:1 menengahkan KOTAKNYA sementara
                         tintanya tetap duduk tinggi. Terlihat jelas saat
                         diperbesar 8x: ruang hijau di bawah centang lebih lebar
                         daripada di atasnya.

                         Menambalnya dengan translateY sekian piksel berarti
                         menanam angka sihir yang akan meleset lagi begitu ukuran
                         kotak atau versi ikonnya berubah. Jalur yang digambar
                         sendiri di viewBox simetris tidak punya masalah itu:
                         tengahnya benar menurut bentuknya, bukan menurut
                         metrik fontnya. --}}
                    <span class="ikon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 12.5 L10.5 17 L18 8" stroke="currentColor"
                                stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <div>
                        <p class="judul">Sudah masuk — tidak ada yang perlu dikerjakan</p>
                        <p class="isi">
                            Uangnya dipastikan gerbang pembayaran sebelum catatan ini dibuat.
                            Tidak ada yang perlu dicocokkan dengan mutasi, dan memang tidak ada
                            berkas bukti untuk dibuka.
                        </p>
                    </div>
                </div>
            @endif

            @unless ($bukti['pesanan'])
                {{-- Kode salah ketik tetap masuk. Yang tidak boleh terjadi
                     adalah admin menerimanya seolah pesanannya jelas. --}}
                <div class="orcha-alasan orcha-alasan-tinggi mt-3">
                    <span class="orcha-label-kecil orcha-ikon-teks" style="color:#b91c1c">
                        <i class="bi bi-exclamation-triangle-fill"></i> Kode tidak dikenal
                    </span>
                    <div style="font-size:.84rem" class="mt-1">
                        Kode <strong>{{ $bukti['kode'] }}</strong> tidak cocok dengan
                        pesanan mana pun. Cocokkan dulu dengan pemesannya sebelum
                        menerima — uang yang diakui ke pesanan yang salah lebih sulit
                        diurai daripada bukti yang ditunda.
                    </div>
                </div>
            @endunless

            {{-- Posisi tagihan pesanannya.

                 Inilah yang sebenarnya dicari admin sesudah tahu pembayarannya
                 masuk: "berarti kurang berapa lagi?". Selama angka itu tidak ada
                 di sini, ia membuka halaman pendaftaran hanya untuk satu baris —
                 dan sebagian menghitungnya di kepala, lalu meleset. --}}
            @if ($tagihanPesanan)
                <div class="orcha-cek-kartu orcha-cek-posisi">
                    <div class="kepala">Posisi tagihan pesanan ini</div>
                    <div class="kotak">
                        @foreach ([
                            ['Total tagihan', $tagihanPesanan['total_teks'], ''],
                            ['Sudah dibayar', $tagihanPesanan['sudah_teks'], 'masuk'],
                            [$tagihanPesanan['lunas'] ? 'Lunas' : 'Sisa', $tagihanPesanan['sisa_teks'], $tagihanPesanan['lunas'] ? 'lunas' : 'sisa'],
                        ] as [$label, $nilai, $rasa])
                            <div class="sel {{ $rasa }}">
                                <span class="lbl">{{ $label }}</span>
                                <span class="nil">{{ $nilai }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($bukti['catatan'])
                <div class="mt-3">
                    <div class="orcha-label-kecil mb-1">Catatan pelanggan</div>
                    <div class="orcha-cek-catatan">{{ $bukti['catatan'] }}</div>
                </div>
            @endif
        </div>

        {{-- Seluruh kolom bukti TIDAK ditampilkan untuk pembayaran gerbang.

             Kotak gambar kosong bertuliskan "belum ada berkas bukti" adalah
             pertanyaan yang tidak punya jawaban: admin membacanya sebagai
             sesuatu yang hilang dan mulai mencarinya. Isian "lampirkan bukti
             susulan" di bawahnya lebih buruk lagi — ia menawarkan pekerjaan
             yang tidak seharusnya ada. --}}
        @unless ($lewatGerbang)
        <div class="col-12 col-lg-5">
            <div class="orcha-label-kecil mb-1">Bukti transfer</div>
            @if ($bukti['bukti'])
                <div class="orcha-cek-bukti"
                    data-bukti="{{ $tautanBukti($bukti['bukti']) }}"
                    data-bukti-keterangan="{{ $bukti['kode'] }} · {{ $bukti['nominal_formatted'] }} · {{ $bukti['bank_pengirim'] }} a.n. {{ $bukti['atas_nama_pengirim'] }}">
                    <img src="{{ $tautanBukti($bukti['bukti']) }}" alt="Bukti transfer">
                    <span class="orcha-cek-perbesar">
                        <i class="bi bi-arrows-fullscreen"></i> Klik untuk memperbesar
                    </span>
                </div>
            @else
                <div class="orcha-cek-kosong">
                    <div class="empty-state-icon-wrapper mx-auto mb-2">
                        <i class="bi bi-image"></i>
                    </div>
                    <p class="text-muted mb-0" style="font-size:.84rem">
                        Belum ada berkas bukti — pelanggan tidak melampirkannya,
                        atau catatan ini dibuat admin tanpa gambar.
                    </p>
                </div>
            @endif

            {{-- Bukti susulan, atau pengganti bukti yang sudah ada.

                 Sebelum ini satu-satunya jalur yang menerima berkas adalah
                 pencatatan pembayaran BARU. Admin yang lupa melampirkan
                 buktinya tinggal punya dua pilihan, dan dua-duanya buruk:
                 mencatat ulang — yang menghitung uangnya dua kali sehingga
                 tagihannya salah — atau membiarkannya tanpa gambar. --}}
            <div class="mt-3">
                <label class="form-label small fw-semibold">
                    {{ $bukti['bukti'] ? 'Ganti bukti' : 'Lampirkan bukti susulan' }}
                </label>

                <input type="file" accept="image/*" wire:model="buktiBaru"
                    class="form-control form-control-sm @error('buktiBaru') is-invalid @enderror">

                <div wire:loading wire:target="buktiBaru,unggahBukti" class="form-text">
                    <span class="spinner-border spinner-border-sm me-1" role="status"
                        aria-hidden="true"></span>Mengunggah…
                </div>

                @error('buktiBaru')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @else
                    <div wire:loading.remove wire:target="buktiBaru,unggahBukti" class="form-text">
                        {{ $bukti['bukti']
                            ? 'Yang lama tetap tersimpan, tidak dihapus.'
                            : 'Tangkapan layar mutasi rekening.' }}
                    </div>
                @enderror

                @if ($buktiBaru)
                    <button type="button" wire:click="unggahBukti"
                        wire:loading.attr="disabled" wire:target="unggahBukti"
                        class="orcha-btn orcha-btn-utama orcha-btn-kecil mt-2">
                        <i class="bi bi-cloud-arrow-up"></i>
                        Simpan bukti
                    </button>
                @endif
            </div>

            {{-- Riwayat penggantian.

                 Catatan uang yang buktinya berganti diam-diam adalah hal yang
                 paling sulit dijelaskan saat dipersoalkan — dan yang
                 mempersoalkannya biasanya bukan kita. --}}
            @if (! empty($bukti['bukti_riwayat']))
                <div class="mt-3">
                    <div class="orcha-label-kecil mb-1">
                        <i class="bi bi-clock-history"></i>
                        Bukti sebelumnya ({{ count($bukti['bukti_riwayat']) }})
                    </div>

                    @foreach ($bukti['bukti_riwayat'] as $lama)
                        <div class="d-flex align-items-center justify-content-between gap-2 py-1"
                            style="font-size:.78rem">
                            <span class="text-muted">
                                Diganti
                                {{ $lama['diganti_pada']
                                    ? \Carbon\Carbon::parse($lama['diganti_pada'])->locale('id')->diffForHumans()
                                    : '—' }}
                                @if ($lama['oleh'])
                                    · oleh {{ $lama['oleh'] }}
                                @endif
                            </span>

                            @if ($lama['bukti'])
                                <a href="{{ $tautanBukti($lama['bukti']) }}" target="_blank" rel="noopener">
                                    Lihat
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endunless
    </div>

    {{-- Formulir keputusan: pusat perhatian di jalur manual, disembunyikan di
         jalur gerbang.

         Menawarkan "Diterima / Ditolak" untuk uang yang sudah ada di rekening
         bukan cuma mubazir — ia mengundang admin menolak pembayaran yang nyata,
         dan akibatnya menimpa pelanggan yang tidak melakukan kesalahan apa pun.
         Kemampuannya tetap ada untuk keadaan langka (sengketa, dana
         dikembalikan), tetapi harus dibuka dengan sengaja. --}}
    @if ($lewatGerbang)
        <details class="orcha-cek-lanjut">
            <summary><i class="bi bi-sliders"></i> Perlu mengubah status pembayaran ini?</summary>
            <p class="ket">
                Hanya untuk keadaan langka seperti sengketa atau dana yang dikembalikan.
                Dalam keadaan biasa, biarkan apa adanya.
            </p>
    @endif

    <div class="orcha-cek-putus">
        <div class="orcha-label-kecil mb-2">Keputusan</div>

        {{-- Ditampilkan sebagai pilihan berdampingan, bukan daftar
             turun. Menerima dan menolak adalah dua tindakan yang
             berbeda akibatnya, jadi keduanya pantas terlihat
             sekaligus — bukan bersembunyi di balik satu klik. --}}
        <div class="orcha-cek-pilihan">
            @foreach ($pilihanStatus as $kunci => $label)
                <label class="orcha-cek-status orcha-cek-status-{{ $kunci }}">
                    {{-- @checked ditulis sendiri: tanpa itu tidak ada satu
                         pun pilihan yang tersorot saat lembar ini dibuka,
                         dan admin tidak bisa melihat status yang berlaku
                         sekarang — padahal itu titik tolak keputusannya. --}}
                    <input type="radio" wire:model="statusBaru"
                        value="{{ $kunci }}" @checked($statusBaru === $kunci)>
                    <span>
                        <i
                            class="bi {{ ['menunggu' => 'bi-hourglass-split', 'diterima' => 'bi-check-circle-fill', 'ditolak' => 'bi-x-circle-fill'][$kunci] ?? 'bi-circle' }}"></i>
                        {{ $label }}
                    </span>
                </label>
            @endforeach
        </div>

        {{-- Pratayang pesan WhatsApp.

             Ditampilkan langsung di halaman, bukan lewat tautan. Isinya
             teks yang sama persis dengan yang akan dikirim, jadi apa yang
             terlihat di sini adalah yang benar-benar dihasilkan server.

             Ini sekaligus alat pembanding: bila emojinya utuh di sini
             tetapi berantakan setelah masuk WhatsApp, yang keliru bukan
             penyusunan pesannya melainkan cara aplikasi WhatsApp membaca
             tautan — dan tombol Salin di bawah adalah jalan keluarnya. --}}
        <details class="orcha-cek-pratayang mt-3">
            <summary>
                <i class="bi bi-eye"></i> Lihat pesan yang akan dikirim
            </summary>
            {{-- Isinya diisi skrip perakit emoji. Cadangannya teks
                 tanpa emoji, supaya kotak ini tidak pernah kosong
                 maupun menampilkan penanda mentah. --}}
            <pre data-wa-pratayang="{{ $this->pesanWa($bukti) }}">{{ $this->pesanWaPolos($bukti) }}</pre>
            <p class="mb-0">
                Emoji di atas tampil benar? Berarti pesannya memang benar.
                Bila di WhatsApp berubah jadi tanda tanya, pakai
                <strong>Salin Pesan</strong> lalu tempel (⌘V) di sana.
            </p>
        </details>

        <div class="mt-3">
            {{-- Labelnya HARUS menyebut siapa yang membacanya, dan jawabannya
                 berubah menurut keputusan yang dipilih.

                 Sebelumnya tertulis "ikut terbaca oleh admin lain" — kalimat
                 yang mengundang orang menuliskan hal internal: dugaan,
                 pengingat, nama rekan yang harus mengecek. Padahal isinya ikut
                 tercetak di kwitansi pelanggan. Janji yang dilanggar diam-diam
                 seperti itu baru ketahuan setelah ada pelanggan membaca sesuatu
                 yang tidak pernah ditujukan padanya.

                 Sekarang catatannya memang internal — kecuali saat Ditolak,
                 dan di situ peringatannya berdiri terang di depan mata. --}}
            <label class="form-label small fw-semibold mb-1">
                Catatan admin
                @if ($statusBaru === 'ditolak')
                    <span class="orcha-catatan-terbaca">
                        <i class="bi bi-exclamation-triangle-fill"></i> dibaca pelanggan
                    </span>
                @else
                    <span class="text-muted fw-normal">(internal — hanya dibaca admin)</span>
                @endif
            </label>
            <input type="text" class="form-control" wire:model="catatanAdmin"
                placeholder="Mis. cocok dengan mutasi rekening 15 Agu.">
            <div class="form-text">
                @if ($statusBaru === 'ditolak')
                    <strong class="text-danger">Kalimat ini dikirim ke pelanggan.</strong>
                    Tuliskan apa yang perlu ia perbaiki, bukan catatan untuk rekan Anda.
                @else
                    Tidak ikut ke kwitansi maupun email pelanggan. Satu-satunya
                    pengecualian: bila statusnya <strong>Ditolak</strong>, catatan ini
                    menjadi alasan yang dikirim kepadanya.
                @endif
            </div>
        </div>
    </div>

    @if ($lewatGerbang)
        </details>
    @endif
                </div>

                {{-- Kaki lembar: keputusannya disimpan di sini, dan kabar ke
                     pelanggan berangkat dari sini juga. --}}
                <div class="card-footer bg-transparent border-0 p-3 p-lg-4 pt-0">
                    {{-- Disebutkan sebelum tombolnya ditekan, bukan sesudah: yang
                         menerima email adalah pelanggan, dan email tidak bisa ditarik. --}}
                    <div class="orcha-alasan orcha-alasan-tenang mb-3">
                        <i class="bi bi-envelope"></i>
                        @if ($lewatGerbang)
                            Pelanggan sudah menerima tanda terimanya lewat email begitu
                            pembayaran ini masuk.
                        @else
                            Pelanggan otomatis dikabari lewat email setelah status disimpan.
                        @endif
                    </div>

                    {{-- Kabar WhatsApp bersebelahan dengan Simpan: begitu statusnya
                         diputuskan, mengabari pelanggan adalah langkah berikutnya yang
                         wajar — bukan sesuatu yang harus dicari lagi di daftar.

                         Isinya mengikuti status yang TERSIMPAN, bukan yang baru dipilih
                         di layar. Mengabari "sudah diterima" untuk sesuatu yang belum
                         disimpan berarti menjanjikan yang belum tercatat. --}}
                    @php $waPopup = $this->tautanWa($bukti); @endphp

                    <div class="row g-2">
                        <div class="{{ $waPopup ? 'col-6 col-lg-3' : 'col-6' }}">
                            <a href="{{ route('admin.orcha.pembayaran') }}" wire:navigate
                                class="orcha-btn orcha-btn-lembut orcha-tombol-lembar">
                                {{-- "Batal" hanya benar bila ada yang sedang dikerjakan.
                                     Di lembar yang cuma dibaca, kata itu membuat orang
                                     mengira ia sedang membatalkan pembayarannya. --}}
                                <i class="bi bi-arrow-left"></i>
                                {{ $lewatGerbang ? 'Kembali' : 'Batal' }}
                            </a>
                        </div>

                        @if ($waPopup)
                            {{-- Tombol salin berdiri sendiri, tidak hanya menumpang di
                                 tombol WA. Bila emojinya berantakan di aplikasi WhatsApp,
                                 inilah jalan yang pasti: menempel memindahkan karakter
                                 yang sama persis, tanpa sandi yang perlu dibaca ulang. --}}
                            <div class="col-6 col-lg-3">
                                <button type="button"
                                    class="orcha-btn orcha-btn-lembut orcha-tombol-lembar"
                                    data-wa-pesan="{{ $this->pesanWa($bukti) }}"
                                    title="Salin teks pesannya untuk ditempel di WhatsApp">
                                    <i class="bi bi-clipboard"></i> Salin Pesan
                                </button>
                            </div>

                            <div class="col-6 col-lg-3">
                                <a href="{{ $waPopup }}" target="_blank" rel="noopener"
                                    class="orcha-btn orcha-btn-wa orcha-tombol-lembar"
                                    data-wa-pesan="{{ $this->pesanWa($bukti) }}">
                                    <i class="bi bi-whatsapp"></i> Kabari via WA
                                </a>
                            </div>
                        @endif

                        <div class="{{ $waPopup ? 'col-6 col-lg-3' : 'col-6' }}">
                            {{-- Ikonnya berganti pemintal selama disimpan.

                                 Menyimpan status berarti menembak Orcha, dan Orcha
                                 sekaligus mengirim email ke pelanggan — jadi jedanya
                                 terasa. Tombol yang tidak berubah apa-apa selama itu
                                 membuat admin mengira tekanannya tidak masuk lalu
                                 menekannya lagi, dan email keduanya sudah berangkat
                                 sebelum ia sempat menyesal.

                                 Menyasar simpan() secara khusus: tanpa itu, tombol ini
                                 ikut memintal setiap kali ada permintaan lain di
                                 halaman yang sama. --}}
                            <button type="button" class="orcha-btn orcha-btn-utama orcha-tombol-lembar"
                                wire:click="simpan" wire:target="simpan" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="simpan">
                                    <i class="bi bi-save"></i> Simpan Status
                                </span>
                                {{-- Tanpa kelas display apa pun.

                                     .d-inline-flex milik Bootstrap memakai !important,
                                     dan itu mengalahkan aturan penyembunyi di partial
                                     gaya — pemintalnya akan tergambar dari awal justru
                                     karena kelas tata letaknya. Jaraknya diatur margin,
                                     yang tidak menyentuh display sama sekali. --}}
                                <span wire:loading wire:target="simpan">
                                    <span class="spinner-border spinner-border-sm me-2"
                                        role="status" aria-hidden="true"></span>Menyimpan...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.salin-wa')
    @include('livewire.pages.admin.orcha.partials.pratinjau-bukti')
    @include('livewire.pages.admin.orcha.pembayaran.partials.gaya-cek')
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
