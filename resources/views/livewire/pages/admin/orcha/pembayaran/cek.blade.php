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

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <a href="{{ route('admin.orcha.pembayaran') }}" wire:navigate class="orcha-tautan-balik mb-2">
                                <i class="bi bi-arrow-left"></i> Semua bukti pembayaran
                            </a>
                            <h1 class="gradient-text fw-bold mb-1" style="font-size:1.5rem">Cek Bukti Pembayaran</h1>
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
        <div class="col-12 col-lg-7">
            {{-- Nominal dibesarkan sendiri. Inilah angka yang dicocokkan
                 dengan mutasi rekening, dan salah baca satu digit di sini
                 berarti salah menyatakan pesanan sudah lunas. --}}
            <div class="orcha-cek-nominal">
                <div class="orcha-label-kecil">Nominal yang dikirim</div>
                <div class="angka">{{ $bukti['nominal_formatted'] }}</div>
                <div class="orcha-cek-tanggal">
                    <i class="bi bi-calendar-event"></i>
                    {{ $bukti['tanggal_transfer']
                        ? \Carbon\Carbon::parse($bukti['tanggal_transfer'])->locale('id')->translatedFormat('j F Y')
                        : 'tanggal transfer tidak diisi' }}
                </div>
            </div>

            <div class="row g-3 mt-1">
                @foreach ([
                    ['bi-person-badge', 'Pengirim', $bukti['atas_nama_pengirim'], null],
                    ['bi-bank', 'Bank pengirim', $bukti['bank_pengirim'], null],
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

            @if ($bukti['catatan'])
                <div class="mt-3">
                    <div class="orcha-label-kecil mb-1">Catatan pelanggan</div>
                    <div class="orcha-cek-catatan">{{ $bukti['catatan'] }}</div>
                </div>
            @endif
        </div>

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
                        Pelanggan tidak melampirkan berkas bukti.
                    </p>
                </div>
            @endif
        </div>
    </div>

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
            <label class="form-label small fw-semibold mb-1">
                Catatan admin
                <span class="text-muted fw-normal">(ikut terbaca oleh admin lain)</span>
            </label>
            <input type="text" class="form-control" wire:model="catatanAdmin"
                placeholder="Mis. cocok dengan mutasi rekening 15 Agu.">
            <div class="form-text">
                Bila ditolak, tuliskan alasannya — kalimat inilah yang dikirim ke
                pelanggan supaya ia tahu apa yang perlu diperbaiki.
            </div>
        </div>
    </div>
                </div>

                {{-- Kaki lembar: keputusannya disimpan di sini, dan kabar ke
                     pelanggan berangkat dari sini juga. --}}
                <div class="card-footer bg-transparent border-0 p-3 p-lg-4 pt-0">
                    {{-- Disebutkan sebelum tombolnya ditekan, bukan sesudah: yang
                         menerima email adalah pelanggan, dan email tidak bisa ditarik. --}}
                    <div class="orcha-alasan orcha-alasan-tenang mb-3">
                        <i class="bi bi-envelope"></i>
                        Pelanggan otomatis dikabari lewat email setelah status disimpan.
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
                                <i class="bi bi-x-lg"></i> Batal
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
