@section('title')
Detail Pendaftaran || lemon
@stop

@php
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanBukti = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;

    $wa = fn ($nomor) => 'https://wa.me/' . preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $nomor));

    $tagihan = $pendaftaran['tagihan'] ?? [];
    $pembayaran = $pendaftaran['pembayaran'] ?? [];
    $pembatalan = $pendaftaran['pembatalan'] ?? null;
    $peserta = $pendaftaran['peserta'] ?? [];
    $belumIsi = collect($pendaftaran['peserta_belum_isi'] ?? [])
        ->map(fn ($nama) => mb_strtolower(trim($nama)))
        ->all();

    $persenBayar = ($tagihan['total'] ?? 0) > 0
        ? min(100, round(($tagihan['sudah'] ?? 0) / $tagihan['total'] * 100))
        : 0;

    // Jarak ke keberangkatan dan tenggat pelunasannya. Inilah yang menentukan
    // tindakan admin hari ini — bukan tanggalnya sendiri — dan menghitungnya
    // dari kalender adalah pekerjaan yang berulang setiap kali halaman dibuka.
    $berangkat = ! empty($pendaftaran['tanggal_berangkat'])
        ? \Carbon\Carbon::parse($pendaftaran['tanggal_berangkat'])->startOfDay()
        : null;
    $sisaHari = $berangkat ? now()->startOfDay()->diffInDays($berangkat, false) : null;

    $hariPelunasan = (int) ($aturanBayar['pelunasan_hari_sebelum'] ?? 0);
    $tenggat = $berangkat && $hariPelunasan > 0 ? $berangkat->copy()->subDays($hariPelunasan) : null;
    $lunas = $tagihan['lunas'] ?? false;
    $tenggatLewat = $tenggat && ! $lunas && $tenggat->isPast();
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @if (empty($pendaftaran))
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-person-x"></i></div>
                    <p class="text-muted mb-3">Data pendaftaran tidak bisa ditampilkan.</p>
                    <a href="{{ route('admin.orcha.pendaftaran') }}" class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-arrow-left"></i> Kembali ke daftar
                    </a>
                </div>
            </div>
        @else

            {{-- ============ IDENTITAS ============
                 Kode dan nama berdiri sendiri di kartu paling atas: dua hal itu
                 yang disebut pelanggan saat menelepon, dan tidak ada apa pun di
                 sekitarnya yang perlu bersaing dengan keduanya. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body p-3 p-lg-4">
                    <a href="{{ route('admin.orcha.pendaftaran') }}" class="orcha-tautan-balik mb-2">
                        <i class="bi bi-arrow-left"></i> Semua pendaftaran
                    </a>
                    <h1 class="gradient-text fw-bold mb-1" style="font-size:1.6rem">
                        {{ $pendaftaran['nama'] }}
                    </h1>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="orcha-kode">{{ $pendaftaran['kode'] }}</span>
                        @if ($berangkat)
                            <span class="orcha-cip-hari {{ $sisaHari !== null && $sisaHari <= $hariPelunasan ? 'dekat' : '' }} {{ $sisaHari !== null && $sisaHari < 0 ? 'lewat' : '' }}">
                                <i class="bi bi-calendar-event"></i>
                                @if ($sisaHari > 0)
                                    Berangkat {{ $berangkat->translatedFormat('d M Y') }} · H-{{ $sisaHari }}
                                @elseif ($sisaHari === 0)
                                    Berangkat hari ini
                                @else
                                    Sudah berangkat {{ $berangkat->translatedFormat('d M Y') }}
                                @endif
                            </span>
                        @endif
                        <span class="text-muted" style="font-size:.82rem">
                            Mendaftar
                            {{ \Carbon\Carbon::parse($pendaftaran['dibuat_pada'])->locale('id')->translatedFormat('d F Y, H:i') }}
                            WIB
                        </span>
                    </div>
                </div>
            </div>

            {{-- ============ TINDAKAN ============
                 Kartunya sendiri, berisi hal-hal yang DIKERJAKAN admin: menghubungi,
                 mengunduh berkas, dan mengubah status. Sebelumnya tombol-tombol ini
                 berbagi baris dengan judul, sehingga letaknya ikut berubah mengikuti
                 panjang nama pemesan — dan tombol yang sama tidak pernah berada di
                 tempat yang sama dua kali. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-3 orcha-kartu-tindakan">
                {{-- Satu baris, tanpa label di atasnya.

                     Label "Tindakan" menyisakan pita kosong selebar kartu di atas
                     tombol-tombolnya, dan kartu setinggi itu untuk empat tombol
                     terbaca sebagai ruang yang belum selesai diisi. Tombolnya sendiri
                     sudah menyebutkan namanya masing-masing. --}}
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- Membuka pilihan pesan, bukan percakapan kosong.

                 Sebelumnya tombol ini langsung membuka WhatsApp tanpa isi, dan
                 admin mengetik ulang kalimat yang sama berpuluh kali sehari —
                 dengan nominal yang harus disalin sendiri dari layar sebelah.
                 Satu angka salah ketik berarti pelanggan mentransfer jumlah
                 yang keliru, dan itu baru ketahuan saat buktinya masuk. --}}
                            {{-- Keempatnya seukuran pemilih status di sebelahnya — 34px lewat
                                 .orcha-aksi-sewa, ukuran yang sama dipakai kartu Tindakan di
                                 detail sewa dan detail pembatalan. Satu bilah perkakas yang
                                 tombolnya berbeda-beda tinggi terbaca seperti kumpulan
                                 tombol yang kebetulan bersebelahan. --}}
                            <button type="button" class="orcha-btn orcha-btn-wa orcha-aksi-sewa"
                                onclick="orchaBukaLembar('pilihanWa')">
                                <i class="bi bi-whatsapp"></i> Hubungi Pemesan
                            </button>

                            {{-- Jaring pengaman saat surat tidak sampai: admin bisa
                                 mengunduh kwitansi yang sama persis dengan yang dikirim
                                 ke pelanggan, lalu meneruskannya lewat WhatsApp.
                                 Tidak menuntut izin data kesehatan — isinya biaya. --}}
                            <a href="{{ route('admin.orcha.pendaftaran.kwitansi', $pendaftaranId) }}"
                                class="orcha-btn orcha-btn-lembut orcha-aksi-sewa" title="Kwitansi yang sama dengan yang dikirim ke pelanggan">
                                <i class="bi bi-receipt"></i> Kwitansi
                            </a>

                            {{-- Dua berkas untuk dua pembaca: Excel untuk kantor,
                                 PDF untuk tour leader di lapangan. Keduanya memuat
                                 data kesehatan, jadi ikut dijaga izin yang sama. --}}
                            @if (auth()->user()->hasPermission('view_orcha_kesehatan'))
                                <a href="{{ route('admin.orcha.pendaftaran.pdf', $pendaftaranId) }}"
                                    class="orcha-btn orcha-btn-lembut orcha-aksi-sewa" title="Manifes untuk tour leader di lapangan">
                                    <i class="bi bi-filetype-pdf"></i> Manifes PDF
                                </a>
                                <a href="{{ route('admin.orcha.pendaftaran.excel', $pendaftaranId) }}"
                                    class="orcha-btn orcha-btn-lembut orcha-aksi-sewa" title="Data lengkap untuk kantor">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Excel
                                </a>
                            @endif

                            {{-- Mencatat pembayaran adalah TINDAKAN, sama seperti
                                 menghubungi pemesan — bukan keterangan yang menempel
                                 pada ringkasan biaya.

                                 Sebelumnya tombolnya terselip di bawah batang kemajuan
                                 pembayaran, tempat mata membaca angka dan berhenti. Di
                                 bilah ini ia sejajar dengan perkakas lain yang memang
                                 dicari orang saat membuka halaman.

                                 Disembunyikan saat sudah lunas atau batal: mencatat
                                 pembayaran pada pesanan yang sudah selesai hampir selalu
                                 salah orang. --}}
                            @if (! $lunas && ($pendaftaran['status'] ?? '') !== 'batal')
                                <button type="button" wire:click="bukaFormulirBayar"
                                    class="orcha-btn orcha-btn-lembut orcha-aksi-sewa"
                                    title="Untuk transfer yang dikabari lewat WhatsApp dan sudah Anda cocokkan dengan mutasi rekening">
                                    <i class="bi bi-cash-coin"></i> Catat Pembayaran
                                </button>
                            @endif
                        </div>

                        {{-- Labelnya di samping, bukan di atas: sebaris supaya kartunya
                             tetap setipis bilah perkakas. Warnanya mengikuti keadaan,
                             sama seperti di daftar — satu kotak putih bertuliskan
                             "DP Masuk" tidak memberi tahu apakah itu kabar baik atau
                             pekerjaan yang menunggu. --}}
                        <div class="d-flex align-items-center gap-2">
                            <span class="orcha-label-kecil">Status</span>
                            @if ($pilihanStatus === [])
                                <span class="orcha-status-diam status-{{ $pendaftaran['status'] }}"
                                    title="Daftar status belum bisa diambil dari Orcha, jadi statusnya belum bisa diubah dari sini.">
                                    <i class="bi bi-wifi-off"></i>
                                    {{ $pendaftaran['status_label'] ?? $pendaftaran['status'] }}
                                </span>
                            @else
                                <select class="form-select form-select-sm orcha-pilih-status status-{{ $pendaftaran['status'] }}"
                                    wire:change="ubahStatus($event.target.value)">
                                    @foreach ($pilihanStatus as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected($pendaftaran['status'] === $kunci)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ RINGKASAN BIAYA ============ --}}
            @if ($tagihan)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3 p-lg-4">
                        <div class="row g-3">
                            @foreach ([
                                ['Total tagihan', $tagihan['total_teks'], '', 'bi-receipt'],
                                ['Sudah dibayar', $tagihan['sudah_teks'], 'lunas', 'bi-cash-coin'],
                                ['Sisa', $tagihan['sisa_teks'], ($tagihan['lunas'] ?? false) ? 'lunas' : 'sisa', 'bi-hourglass-split'],
                                /* Dua angka yang memang berbeda, dan keduanya disebut.

                                   "42 orang" pada rombongan yang dua di antaranya guru
                                   pendamping gratis membuat siapa pun mengalikannya dengan
                                   harga satuan dan mendapat angka yang tidak cocok dengan
                                   total tagihan di sebelahnya — lalu menyimpulkan salah
                                   satunya salah. */
                                ['Peserta', ($pendaftaran['pendamping_gratis'] ?? 0) > 0
                                    ? $pendaftaran['jumlah_peserta'].' orang · '.$pendaftaran['peserta_dibayar'].' ditagih'
                                    : $pendaftaran['jumlah_peserta'].' orang', '', 'bi-people'],
                            ] as [$label, $nilai, $kelas, $ikon])
                                <div class="col-6 col-lg-3">
                                    <div class="orcha-ringkas {{ $kelas }}">
                                        <div class="orcha-label-kecil">
                                            <i class="bi {{ $ikon }}"></i> {{ $label }}
                                        </div>
                                        <div class="angka">{{ $nilai }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between" style="font-size:.78rem">
                                <span class="text-muted">Kemajuan pembayaran</span>
                                <span class="fw-bold text-dark">{{ $persenBayar }}%</span>
                            </div>
                            <div class="orcha-palang mt-1 {{ $lunas ? 'lunas' : '' }}">
                                <span style="width: {{ $persenBayar }}%"></span>
                            </div>

                            {{-- Persen saja belum menjawab pertanyaan berikutnya: kapan
                                 sisanya harus masuk. Tenggatnya dihitung dari aturan Orcha
                                 (H-{{ $hariPelunasan }}), bukan angka yang ditulis di sini. --}}
                            <div class="mt-2" style="font-size:.8rem">
                                @if ($lunas)
                                    <span class="orcha-tenggat lunas">
                                        <i class="bi bi-check-circle-fill"></i> Sudah lunas — tidak ada sisa yang perlu ditagih.
                                    </span>
                                @elseif ($tenggat)
                                    <span class="orcha-tenggat {{ $tenggatLewat ? 'lewat' : '' }}">
                                        <i class="bi bi-{{ $tenggatLewat ? 'exclamation-triangle-fill' : 'clock-history' }}"></i>
                                        Sisa <strong>{{ $tagihan['sisa_teks'] }}</strong>
                                        {{ $tenggatLewat ? 'sudah melewati batas pelunasan' : 'dilunasi paling lambat' }}
                                        <strong>{{ $tenggat->translatedFormat('d M Y') }}</strong>
                                        (H-{{ $hariPelunasan }} sebelum berangkat)
                                    </span>
                                @elseif (! $berangkat)
                                    <span class="text-muted">
                                        Sisa <strong>{{ $tagihan['sisa_teks'] }}</strong> — batas pelunasan
                                        mengikuti tanggal berangkat, yang belum dijadwalkan.
                                    </span>
                                @else
                                    {{-- Tanggalnya ada, tapi aturan pelunasan tidak terbaca
                                         (rujukan dari Orcha gagal diambil). Kalimatnya berhenti
                                         di yang memang diketahui: menuduh jadwalnya belum ada
                                         padahal tanggalnya tertulis di layar yang sama membuat
                                         admin meragukan seluruh halaman. --}}
                                    <span class="text-muted">
                                        Sisa yang perlu ditagih <strong>{{ $tagihan['sisa_teks'] }}</strong>.
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Mencatat pembayaran yang diterima admin sendiri.

                             Private trip dan study tour tidak lewat formulir
                             konfirmasi publik: panitia mentransfer lalu mengabari
                             lewat WhatsApp, kadang tanpa tangkapan layar. Yang
                             memastikan uangnya masuk adalah admin yang membuka
                             mutasi rekening — dan sebelum ini pemeriksaan itu
                             tidak punya tempat pulang.

                             Disembunyikan saat sudah lunas: tombol yang selalu ada
                             tetapi tidak selalu berguna hanya menambah benda yang
                             harus diabaikan mata, dan mencatat pembayaran pada
                             pesanan yang sudah lunas hampir selalu salah orang. --}}
                        @if (! $lunas && ($pendaftaran['status'] ?? '') !== 'batal')
                            {{-- Tombol pembukanya kini di bilah perkakas atas; yang
                                 tinggal di sini formulirnya, tepat di bawah angka yang
                                 sedang diubahnya. --}}
                            <div @class(['pt-3 mt-3 border-top' => $bukaBayar])>
                                @if ($bukaBayar)
                                    <div class="orcha-bagian-kepala">
                                        <div class="orcha-bagian-nomor"><i class="bi bi-cash-coin"></i></div>
                                        <div>
                                            <div class="orcha-bagian-judul">Catat pembayaran diterima</div>
                                            <div class="orcha-bagian-sub">
                                                Langsung terhitung sebagai diterima — yang dicatat di sini
                                                adalah hasil pemeriksaan Anda sendiri, bukan klaim pelanggan
                                                yang masih perlu dicek. Tercatat di jejak audit atas nama Anda.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label small fw-semibold">Nominal <span class="text-danger">*</span></label>
                                            {{-- .orcha-rupiah menaruh "Rp" DI DALAM kotaknya —
                                                 pola yang sudah dipakai seluruh isian uang di
                                                 lemon. Kotak "Rp" terpisah membuat layar ini
                                                 satu-satunya yang berbeda bentuknya. --}}
                                            <div class="orcha-rupiah">
                                                <input type="text" inputmode="numeric"
                                                    wire:model.blur="bayar.nominal"
                                                    value="{{ $bayar['nominal'] }}" placeholder="5.000.000"
                                                    class="form-control @error('bayar.nominal') is-invalid @enderror">
                                            </div>
                                            @error('bayar.nominal')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-lg-4">
                                            <label class="form-label small fw-semibold">Tanggal transfer <span class="text-danger">*</span></label>
                                            <input type="date" wire:model="bayar.tanggal_transfer"
                                                value="{{ $bayar['tanggal_transfer'] }}"
                                                max="{{ now()->toDateString() }}"
                                                class="form-control @error('bayar.tanggal_transfer') is-invalid @enderror">
                                            @error('bayar.tanggal_transfer')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-lg-4">
                                            <label class="form-label small fw-semibold">Jenis <span class="text-danger">*</span></label>
                                            <select wire:model="bayar.jenis" class="form-select">
                                                @foreach ($pilihanJenisBayar as $kunci => $label)
                                                    <option value="{{ $kunci }}" @selected($bayar['jenis'] === $kunci)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-12 col-lg-4">
                                            <label class="form-label small fw-semibold">Bank pengirim <span class="text-danger">*</span></label>
                                            <input type="text" maxlength="60" wire:model="bayar.bank_pengirim"
                                                value="{{ $bayar['bank_pengirim'] }}" placeholder="BCA"
                                                class="form-control @error('bayar.bank_pengirim') is-invalid @enderror">
                                            @error('bayar.bank_pengirim')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-lg-8">
                                            <label class="form-label small fw-semibold">Atas nama pengirim <span class="text-danger">*</span></label>
                                            <input type="text" maxlength="120" wire:model="bayar.atas_nama_pengirim"
                                                value="{{ $bayar['atas_nama_pengirim'] }}"
                                                class="form-control @error('bayar.atas_nama_pengirim') is-invalid @enderror">
                                            {{-- Keduanya yang dipakai mencocokkan dengan mutasi
                                                 rekening saat ada yang mempersoalkan. Catatan
                                                 uang tanpa asal-usulnya cuma angka yang harus
                                                 dipercaya. --}}
                                            <div class="form-text">Sesuai yang tertera di mutasi rekening.</div>
                                            @error('bayar.atas_nama_pengirim')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-lg-8">
                                            <label class="form-label small fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                                            <input type="text" maxlength="200" wire:model="bayar.catatan"
                                                value="{{ $bayar['catatan'] }}" class="form-control"
                                                placeholder="Cicilan ke-2, dana komite tahap 1, ...">
                                        </div>

                                        {{-- Bukti transfer, dan sengaja TIDAK wajib.

                                             Di formulir publik bukti wajib karena tanpa gambar
                                             tidak ada yang bisa dicek. Di sini yang mencatat
                                             justru orang yang sudah mengecek — dan sebagian
                                             panitia memang cuma menulis "sudah ditransfer ya"
                                             tanpa tangkapan layar apa pun.

                                             Mewajibkannya berarti pembayaran yang nyata tidak
                                             bisa dicatat karena kurang sebuah gambar, dan yang
                                             terjadi berikutnya bukan admin mengejar gambarnya
                                             melainkan pembayarannya tidak dicatat sama sekali. --}}
                                        <div class="col-12 col-lg-4">
                                            <label class="form-label small fw-semibold">
                                                Bukti transfer <span class="text-muted fw-normal">(opsional)</span>
                                            </label>

                                            <input type="file" accept="image/*" wire:model="buktiBayar"
                                                class="form-control @error('buktiBayar') is-invalid @enderror">

                                            <div wire:loading wire:target="buktiBayar" class="form-text">
                                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                                    aria-hidden="true"></span>Mengunggah…
                                            </div>

                                            @error('buktiBayar')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @else
                                                <div wire:loading.remove wire:target="buktiBayar" class="form-text">
                                                    Tangkapan layar mutasi rekening, bila ada.
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-end pt-3 mt-3 border-top">
                                        <button type="button" wire:click="tutupFormulirBayar"
                                            class="orcha-btn orcha-btn-lembut">Batal</button>

                                        <button type="button" wire:click="catatBayar"
                                            wire:loading.attr="disabled" wire:target="catatBayar"
                                            class="orcha-btn orcha-btn-utama">
                                            <span wire:loading.remove wire:target="catatBayar">
                                                <i class="bi bi-check2-circle"></i>
                                                Catat pembayaran
                                            </span>
                                            <span wire:loading wire:target="catatBayar">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>Menyimpan…
                                            </span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($pembatalan)
                @php
                    /*
                     | Rekeningnya HANYA ditunjukkan bila memang ada yang dikirim.
                     |
                     | Dulu ia selalu tampil sebagai "Rekening pengembalian: ...".
                     | Pada pengajuan yang potongannya sebesar seluruh pembayaran —
                     | kembali Rp 0 — kalimat itu terbaca sebagai perintah
                     | mentransfer ke sana, padahal di fitur Pembatalan memang tidak
                     | ada yang dikembalikan. Admin yang awam mengerjakannya.
                     |
                     | Yang menentukan ANGKANYA, bukan ada tidaknya rekening.
                     */
                    $perkiraan = $pembatalan['perkiraan'] ?? null;
                    $adaKembali = ($perkiraan['kembali'] ?? 0) > 0;
                    $sudahDikirim = ($pembatalan['status'] ?? '') === 'dana_dikirim';
                @endphp

                <div class="card border-0 shadow-sm rounded-4 mb-4 orcha-kartu-batal">
                    <div class="card-body p-3 p-lg-4">
                        <div class="orcha-bagian-kepala mb-3">
                            <div class="orcha-bagian-nomor batal"><i class="bi bi-x-octagon"></i></div>
                            <div class="flex-grow-1">
                                <div class="orcha-bagian-judul d-flex flex-wrap align-items-center gap-2">
                                    Ada pengajuan pembatalan
                                    <span class="orcha-status-batal" data-status="{{ $pembatalan['status'] }}">
                                        {{ $pembatalan['status_label'] ?? $pembatalan['status'] }}
                                    </span>
                                </div>
                                <div class="orcha-bagian-sub">
                                    {{ $pembatalan['jumlah_dibatalkan'] }} peserta ·
                                    {{ $pembatalan['alasan_label'] }} · diajukan
                                    {{ \Carbon\Carbon::parse($pembatalan['dibuat_pada'])->locale('id')->translatedFormat('j M Y') }}
                                    oleh {{ $pembatalan['nama_pemohon'] }}
                                </div>
                            </div>
                        </div>

                        @if ($pembatalan['penjelasan'])
                            <div class="orcha-alasan orcha-alasan-tenang mb-3">
                                <span class="orcha-label-kecil mb-0">
                                    <i class="bi bi-chat-quote"></i> Penjelasan pemohon
                                </span>
                                <div class="mt-1">"{{ $pembatalan['penjelasan'] }}"</div>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => 'Sudah dibayar',
                                    'nilai' => $perkiraan['dibayar_teks'] ?? null,
                                ])
                            </div>
                            <div class="col-12 col-md-4">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => 'Potongan' . (isset($perkiraan['persen']) ? ' (' . $perkiraan['persen'] . '%)' : ''),
                                    'nilai' => $perkiraan['potongan_teks'] ?? null,
                                ])
                            </div>
                            <div class="col-12 col-md-4">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => 'Dikembalikan',
                                    'nilai' => $perkiraan['kembali_teks'] ?? null,
                                ])
                            </div>
                        </div>

                        {{-- Kalimatnya menyebut PERBUATANNYA, bukan sekadar angka:
                             yang membaca kartu ini sedang memutuskan mau berbuat apa. --}}
                        @if (! $adaKembali)
                            <div class="orcha-alasan orcha-alasan-tenang mt-3">
                                <span class="orcha-label-kecil mb-0">
                                    <i class="bi bi-slash-circle"></i> Tidak ada dana yang dikembalikan
                                </span>
                                <div class="mt-1">
                                    Potongannya sebesar seluruh pembayaran yang sudah masuk, jadi
                                    <strong>tidak ada yang perlu ditransfer</strong>. Rekening pemohon
                                    sengaja tidak ditampilkan supaya tidak terlanjur dikirimi.
                                </div>
                            </div>
                        @elseif ($sudahDikirim)
                            <div class="orcha-alasan orcha-alasan-tenang mt-3">
                                <span class="orcha-label-kecil mb-0">
                                    <i class="bi bi-check2-circle"></i> Dana sudah ditandai terkirim
                                </span>
                                <div class="mt-1">
                                    Dikirim ke <strong>{{ $pembatalan['rekening'] }}</strong>.
                                    Tidak perlu ditransfer lagi.
                                </div>
                            </div>
                        @else
                            <div class="orcha-alasan orcha-alasan-sedang mt-3">
                                <span class="orcha-label-kecil mb-0">
                                    <i class="bi bi-send-exclamation"></i> Perlu ditransfer ke pemohon
                                </span>
                                <div class="mt-1">
                                    <strong>{{ $perkiraan['kembali_teks'] }}</strong> ke
                                    <strong>{{ $pembatalan['rekening'] }}</strong>.
                                </div>
                            </div>
                        @endif

                        <a href="{{ route('admin.orcha.pembatalan.detail', $pembatalan['id']) }}" wire:navigate
                            class="orcha-btn orcha-btn-lembut orcha-aksi-sewa mt-3">
                            <i class="bi bi-box-arrow-up-right"></i> Buka pengajuannya
                        </a>
                    </div>
                </div>
            @endif

            <div class="row g-4">

                {{-- ============ KOLOM KIRI ============ --}}
                <div class="col-12 col-lg-7">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-person-vcard text-primary"></i> Data Pemesan
                            </h2>

                            <div class="row g-3">
                                @foreach ([
                                    ['Nama lengkap', $pendaftaran['nama'], null],
                                    ['WhatsApp', $pendaftaran['whatsapp'], $wa($pendaftaran['whatsapp'])],
                                    ['Email', $pendaftaran['email'] ?: '—', $pendaftaran['email'] ? 'mailto:' . $pendaftaran['email'] : null],
                                    ['Status pendaftaran', $pendaftaran['status_label'], null],
                                ] as [$label, $nilai, $tautan])
                                    <div class="col-12 col-md-6">
                                        <div class="orcha-label-kecil">{{ $label }}</div>
                                        @if ($tautan)
                                            <a href="{{ $tautan }}" target="_blank" rel="noopener"
                                                class="fw-bold text-decoration-none">{{ $nilai }}</a>
                                        @else
                                            <div class="fw-bold">{{ $nilai }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Catatan pemesan dan catatan sistem berbagi SATU kolom
                                 di basis data, dan itu tidak bisa diubah dari sini.

                                 Yang bisa diubah labelnya. Sebelum ini seluruh isinya
                                 diberi judul "Catatan dari pemesan" — termasuk baris
                                 "[Sistem] Kursi dilepas otomatis … tidak ada pembayaran
                                 dalam 72 jam" yang ditulis LepaskanKursiTertahan. Admin
                                 yang membuka pemesanan batal lalu membaca alasan
                                 pembatalan seolah pelanggan sendiri yang mengetiknya.

                                 Dipisah di sini, bukan di Orcha: memecah kolomnya berarti
                                 memindahkan data lama, dan yang rusak sekarang cuma
                                 keterangannya. --}}
                            @php
                                $barisCatatan = preg_split('/\r?\n/', (string) $pendaftaran['catatan']);
                                $catatanSistem = array_values(array_filter(
                                    $barisCatatan,
                                    fn ($b) => str_starts_with(trim($b), '[Sistem]')
                                ));
                                $catatanPemesan = trim(implode("\n", array_filter(
                                    $barisCatatan,
                                    fn ($b) => ! str_starts_with(trim($b), '[Sistem]')
                                )));
                            @endphp

                            @if ($catatanPemesan !== '')
                                <div class="mt-3 p-3 rounded-3 bg-light">
                                    <div class="orcha-label-kecil">Catatan dari pemesan</div>
                                    <div style="font-size:.9rem">{{ $catatanPemesan }}</div>
                                </div>
                            @endif

                            @if ($catatanSistem !== [])
                                {{-- Warna berbeda, dan itu perlu: yang membacanya sedang
                                     mencari sebab, dan sebab yang ditulis mesin tidak boleh
                                     tertukar dengan kalimat orang. --}}
                                <div class="orcha-catatan-sistem mt-3">
                                    <div class="orcha-label-kecil">
                                        <i class="bi bi-robot"></i> Dicatat sistem
                                    </div>
                                    @foreach ($catatanSistem as $baris)
                                        <div style="font-size:.86rem">
                                            {{ trim(str_replace('[Sistem]', '', $baris)) }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ============ RIWAYAT PERUBAHAN NAMA ============
                         Nama lama tidak hilang saat peserta diganti. Ia yang membayar,
                         atau yang riwayat kesehatannya sudah masuk — dan pertanyaan
                         "dulu siapa yang didaftarkan" hampir selalu muncul belakangan,
                         saat tidak ada lagi yang mengingatnya. --}}
                    @if (! empty($pendaftaran['riwayat_penggantian']))
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-3 p-lg-4">
                                @php
                                    $riwayat = $pendaftaran['riwayat_penggantian'];
                                    $jumlahGanti = count($riwayat);
                                @endphp

                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-1">
                                    <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem">
                                        <i class="bi bi-arrow-left-right text-primary"></i>
                                        Riwayat Perubahan Nama Peserta
                                    </h2>
                                    {{-- Jumlahnya disebut di judul: kartu ini kadang berisi satu
                                         baris, kadang tujuh, dan yang membacanya biasanya ingin tahu
                                         "sudah berapa kali" sebelum membaca satu per satu. --}}
                                    <span class="orcha-ganti-baru">
                                        {{ $jumlahGanti }} penggantian
                                    </span>
                                </div>

                                <p class="text-muted small mb-3">
                                    Penggantian peserta tidak dikenakan biaya sepanjang jumlahnya tetap.
                                    Surat pernyataannya berbentuk PDF resmi bermaterai — tinggal
                                    dicetak, ditandatangani para pihak, lalu diarsipkan.
                                </p>

                                {{-- Terbaru di atas, dan nomornya dihitung dari yang terlama:
                                     baris teratas menyandang nomor terbesar. Urutan bacanya
                                     mengikuti yang dicari admin, penomorannya mengikuti urutan
                                     kejadian — dua hal berbeda yang keduanya perlu benar. --}}
                                <div class="orcha-ganti-runtun">
                                @foreach (array_reverse($riwayat) as $urutanBalik => $ganti)
                                    <div class="orcha-ganti-baris">
                                        <span class="orcha-ganti-nomor">{{ $jumlahGanti - $urutanBalik }}</span>

                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <span class="orcha-ganti-lama">{{ $ganti['dari'] ?: '—' }}</span>
                                            <i class="bi bi-arrow-right" style="color:#14a06a"></i>
                                            <span class="orcha-ganti-baru">{{ $ganti['ke'] ?: 'tanpa pengganti' }}</span>

                                        </div>

                                        {{-- Titik jemputnya disebut di barisnya sendiri, bukan
                                             ditumpuk di baris nama: yang membacanya sedang mencari
                                             satu hal — di mana orang ini naik. --}}
                                        @if (! empty($ganti['dari_titik']) || ! empty($ganti['ke_titik']))
                                            @php
                                                $titikTetap = ! empty($ganti['dari_titik'])
                                                    && mb_strtolower(trim($ganti['dari_titik']))
                                                        === mb_strtolower(trim($ganti['ke_titik'] ?? ''));
                                            @endphp

                                            <div class="orcha-ganti-titik">
                                                <span><i class="bi bi-geo-alt"></i> Titik jemput</span>

                                                {{-- Titik yang tidak berpindah tidak dicoret. Coretan
                                                     berarti "sudah tidak berlaku", dan titik yang justru
                                                     masih dipakai pengganti tidak boleh terbaca begitu —
                                                     sopir membaca kartu ini untuk tahu di mana berhenti. --}}
                                                @if ($titikTetap)
                                                    <span class="orcha-ganti-baru">{{ $ganti['ke_titik'] }}</span>
                                                    <span>tetap, tidak berpindah</span>
                                                @else
                                                    <span class="orcha-ganti-lama">{{ $ganti['dari_titik'] ?: '—' }}</span>
                                                    <i class="bi bi-arrow-right" style="color:#14a06a"></i>
                                                    <span class="orcha-ganti-baru">{{ $ganti['ke_titik'] ?: 'belum dipilih' }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="orcha-ganti-jejak">
                                            <i class="bi bi-clock-history"></i>
                                            {{ ! empty($ganti['pada'])
                                                ? \Carbon\Carbon::parse($ganti['pada'])->locale('id')->translatedFormat('d F Y, H:i').' WIB'
                                                : 'waktu tidak tercatat' }}
                                            @if (! empty($ganti['oleh']))
                                                &middot; dicatat oleh {{ $ganti['oleh'] }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                </div>

                                {{-- Satu surat untuk seluruh pendaftaran, bukan satu per baris.

                                     Pihak yang menyatakan sama, pendaftaran yang dirujuk sama,
                                     kebijakan yang mendasarinya sama — yang berbeda cuma barisnya.
                                     Tombol di tiap baris membuat pemesan menandatangani dua berkas
                                     bermaterai untuk satu pemesanan yang sama. --}}
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3"
                                    style="border-top:1px dashed #eef2f7">
                                    <span class="text-muted" style="font-size:.78rem">
                                        <i class="bi bi-info-circle"></i>
                                        Satu surat memuat seluruh
                                        {{ $jumlahGanti }} penggantian di atas — cukup ditandatangani sekali.
                                    </span>

                                    <a class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                        href="{{ route('admin.orcha.pendaftaran.surat-penggantian', $pendaftaranId) }}">
                                        <i class="bi bi-file-earmark-pdf"></i> Unduh surat pernyataan
                                    </a>
                                </div>

                                {{-- ===== SURAT YANG SUDAH DITANDATANGANI =====

                                     Surat yang sudah dicetak dan ditandatangani perlu jalan pulang
                                     ke sistem. Tanpa ini ia cuma ada di percakapan WhatsApp satu
                                     admin, dan hilang begitu ponselnya berganti — padahal justru
                                     berkas inilah buktinya, bukan PDF kosong yang diunduh tadi. --}}
                                @if (! empty($pendaftaran['surat_penggantian']))
                                    <div class="orcha-surat-ttd orcha-surat-ttd-ada mt-3">
                                        <i class="bi bi-patch-check-fill"></i>

                                        <div class="flex-grow-1">
                                            <div class="fw-bold" style="font-size:.84rem;color:#0b7a4b">
                                                Surat bertanda tangan sudah diarsipkan
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem">
                                                @if (! empty($pendaftaran['surat_penggantian_pada']))
                                                    Diunggah
                                                    {{ \Carbon\Carbon::parse($pendaftaran['surat_penggantian_pada'])
                                                        ->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                                                @else
                                                    Waktu unggahnya tidak tercatat
                                                @endif
                                            </div>
                                        </div>

                                        <a class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                            href="{{ $pendaftaran['surat_penggantian'] }}" target="_blank">
                                            <i class="bi bi-box-arrow-up-right"></i> Lihat
                                        </a>

                                        {{-- Mengganti berkas memakai isian yang sama dengan mengunggah
                                             pertama kali: yang lama otomatis tergantikan, jadi admin
                                             tidak perlu menghapus dulu baru mengunggah. --}}
                                        <label class="orcha-btn orcha-btn-lembut orcha-btn-kecil mb-0"
                                            style="cursor:pointer">
                                            <i class="bi bi-arrow-repeat"></i> Ganti
                                            <input type="file" wire:model="suratTtd" class="d-none"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp">
                                        </label>

                                        {{-- Sama seperti galeri: SweetAlert, bukan dialog bawaan
                                             peramban yang menampilkan nama host di atas
                                             kalimatnya. --}}
                                        <button type="button" class="orcha-btn orcha-btn-kecil orcha-btn-bahaya pcek-konfirmasi"
                                            data-action="hapusSuratTtd"
                                            data-title="Hapus surat bertanda tangan?"
                                            data-text="Berkasnya ikut terhapus dari server. Surat kosongnya tetap bisa diunduh ulang untuk ditandatangani lagi."
                                            data-confirm="Ya, hapus" data-icon="warning">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="orcha-surat-ttd mt-3">
                                        <i class="bi bi-cloud-arrow-up"></i>

                                        <div class="flex-grow-1">
                                            <div class="fw-bold" style="font-size:.84rem;color: var(--orc-tinta)">
                                                Sudah ditandatangani? Unggah ke sini
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem">
                                                Hasil pindaian atau foto dari WhatsApp sama-sama diterima
                                                — PDF, JPG, atau PNG, maksimal 8 MB.
                                            </div>
                                        </div>

                                        <label class="orcha-btn orcha-btn-lembut orcha-btn-kecil mb-0"
                                            style="cursor:pointer">
                                            <i class="bi bi-upload"></i> Pilih berkas
                                            <input type="file" wire:model="suratTtd" class="d-none"
                                                accept=".pdf,.jpg,.jpeg,.png,.webp">
                                        </label>
                                    </div>
                                @endif

                                <div wire:loading wire:target="suratTtd" class="text-muted mt-2"
                                    style="font-size:.78rem">
                                    <i class="bi bi-arrow-repeat"></i> Mengunggah berkas…
                                </div>

                                @error('suratTtd')
                                    <div class="text-danger mt-2" style="font-size:.78rem">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif

                    {{-- ============ PESERTA ============
                         Rombongan sering berangkat dari kota berbeda, dan tiap
                         peserta mengisi riwayat kesehatannya sendiri. Kedua hal
                         itu ditampilkan berdampingan supaya admin tahu siapa
                         yang perlu ditagih tanpa membuka menu lain. --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem">
                                    <i class="bi bi-people-fill text-primary"></i> Peserta &amp; Titik Jemput
                                </h2>
                                <span class="badge {{ ($pendaftaran['kesehatan_lengkap'] ?? false) ? 'orcha-lencana-bayar-diterima' : 'orcha-lencana-bayar-menunggu' }}">
                                    <i class="bi bi-heart-pulse"></i>
                                    {{ $pendaftaran['kesehatan_terisi'] ?? 0 }}/{{ $pendaftaran['jumlah_peserta'] }}
                                    riwayat kesehatan
                                </span>
                            </div>

                            {{-- Tautan pengisian, siap dikirim ulang.

                                 Yang paling sering diminta bukan pengiriman pertama
                                 melainkan pengiriman ULANG, berhari-hari kemudian,
                                 kepada peserta yang belum juga mengisi. Sebelum ini
                                 admin merangkainya sendiri dari ingatan — dan kode
                                 enam huruf acak yang salah satu hurufnya membawa
                                 orang ke halaman yang menolaknya.

                                 Muncul hanya selama masih ada yang belum mengisi:
                                 tautan yang tetap terpampang setelah semuanya lengkap
                                 cuma menambah benda yang harus diabaikan mata. --}}
                            @if (! ($pendaftaran['kesehatan_lengkap'] ?? false) && ($pendaftaran['tautan_kesehatan'] ?? null))
                                @php
                                    $panggil = trim(explode(' ', trim($pendaftaran['nama'] ?? ''))[0] ?? '');

                                    $pesanKesehatan = "Halo Kak {$panggil}, mohon tiap peserta mengisi riwayat "
                                        . "kesehatan sebelum berangkat ya.\n\n"
                                        . 'Kode pemesanan: ' . ($pendaftaran['kode'] ?? '') . "\n"
                                        . $pendaftaran['tautan_kesehatan'] . "\n\n"
                                        . 'Yang kami butuhkan golongan darah, alergi, dan kontak darurat.';

                                    $waKesehatan = 'https://api.whatsapp.com/send?phone='
                                        . preg_replace('/^0/', '62', preg_replace('/\D/', '', $pendaftaran['whatsapp'] ?? ''))
                                        . '&text=' . rawurlencode($pesanKesehatan);
                                @endphp

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 mb-3 rounded-4"
                                    style="background:#f8fafc;border:1px solid #e2e8f0">
                                    <div class="small text-break" style="min-width:0">
                                        <div class="text-muted" style="font-size:.72rem">Tautan pengisian riwayat kesehatan</div>
                                        <a href="{{ $pendaftaran['tautan_kesehatan'] }}" target="_blank" rel="noopener">
                                            {{ $pendaftaran['tautan_kesehatan'] }}
                                        </a>
                                    </div>

                                    <a href="{{ $waKesehatan }}" target="_blank" rel="noopener"
                                        class="orcha-btn orcha-btn-lembut orcha-btn-kecil">
                                        <i class="bi bi-whatsapp"></i>
                                        Kirim ulang
                                    </a>
                                </div>
                            @endif

                            @forelse ($peserta as $satu)
                                @php
                                    $sudahIsi = ! in_array(mb_strtolower(trim($satu['nama'] ?? '')), $belumIsi, true);
                                    $inisial = collect(explode(' ', trim($satu['nama'] ?? '?')))->filter()->take(2)
                                        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))->implode('');
                                @endphp
                                <div class="orcha-peserta">
                                    <span class="orcha-inisial">{{ $inisial ?: '?' }}</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $satu['nama'] ?: '—' }}</div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            <i class="bi bi-geo-alt"></i>
                                            {{ $satu['titik_jemput'] ?: 'Titik jemput belum dipilih' }}
                                        </div>
                                    </div>
                                    @if ($sudahIsi)
                                        <span class="orcha-lencana-aman"><i class="bi bi-check-circle-fill"></i> Kesehatan terisi</span>
                                    @else
                                        <span class="orcha-lencana-awas"><i class="bi bi-clock-history"></i> Belum mengisi</span>
                                    @endif
                                </div>
                            @empty
                                {{-- Keadaan ini yang dulu membuat rombongannya hilang dari
                                     manifes panggil-nama. Sekarang disebutkan apa adanya,
                                     lengkap dengan jalan keluarnya di tombol bawah. --}}
                                <div class="orcha-kosong-peserta">
                                    <i class="bi bi-person-dash"></i>
                                    <div>
                                        <strong>Nama peserta belum didata.</strong>
                                        Pendaftaran ini tercatat {{ $pendaftaran['jumlah_peserta'] }} orang,
                                        tetapi namanya belum ada satu pun — sehingga rombongan ini tidak
                                        bisa masuk manifes panggil-nama.
                                    </div>
                                </div>
                            @endforelse

                            {{-- Dua tombol, dua kolom sama lebar. Sebelumnya keduanya
                                 mengapung di kiri dengan sisa ruang menganggur di kanan;
                                 pada kartu selebar ini, dua tombol sekecil itu terlihat
                                 seperti baris yang belum selesai diisi. --}}
                            {{-- Papan penunjuk untuk yang mencari "ganti peserta": tindakannya
                                 ada di halaman daftar peserta, bukan di tombol tersendiri. --}}
                            <div class="text-muted mt-3" style="font-size:.82rem">
                                <i class="bi bi-arrow-left-right"></i>
                                Peserta berhalangan dan digantikan orang lain? Ubah namanya lewat
                                <strong>{{ $peserta === [] ? 'Lengkapi daftar peserta' : 'Ubah daftar peserta' }}</strong> —
                                nama lamanya tetap tercatat, dan surat pernyataannya muncul di halaman ini.
                            </div>

                            <div class="row g-2 mt-3">
                                {{-- Nama peserta bukan data kesehatan, jadi tidak dijaga izin
                                     khusus itu: siapa pun yang boleh mengurus pendaftaran boleh
                                     melengkapinya. --}}
                                <div class="col-6">
                                    <a href="{{ route('admin.orcha.pendaftaran.peserta', $pendaftaranId) }}"
                                        wire:navigate class="orcha-btn orcha-btn-lembut w-100">
                                        <i class="bi bi-pencil-square"></i>
                                        {{ $peserta === [] ? 'Lengkapi daftar peserta' : 'Ubah daftar peserta' }}
                                    </a>
                                </div>

                                <div class="col-6">
                                    @if (! auth()->user()->hasPermission('view_orcha_kesehatan'))
                                        <span class="orcha-btn orcha-btn-lembut w-100 disabled text-muted"
                                            title="Butuh izin data kesehatan">
                                            <i class="bi bi-lock"></i> Riwayat kesehatan terkunci
                                        </span>
                                    @elseif (($pendaftaran['jumlah_riwayat_kesehatan'] ?? 0) > 0)
                                        {{-- Halaman tersendiri, bukan popup: isinya panjang, dan
                                             rombongan dua belas orang tidak muat di jendela yang
                                             separuhnya sudah terpakai bingkai. --}}
                                        <a href="{{ route('admin.orcha.pendaftaran.kesehatan', $pendaftaranId) }}"
                                            wire:navigate class="orcha-btn orcha-btn-kesehatan w-100">
                                            <i class="bi bi-heart-pulse"></i>
                                            Lihat Riwayat Kesehatan ({{ $pendaftaran['jumlah_riwayat_kesehatan'] }})
                                        </a>
                                    @else
                                        <span class="orcha-btn orcha-btn-lembut w-100 disabled text-muted"
                                            title="Peserta mengisinya sendiri lewat website Orcha">
                                            <i class="bi bi-info-circle"></i> Belum ada riwayat kesehatan
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ KOLOM KANAN ============ --}}
                <div class="col-12 col-lg-5">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-suitcase-lg text-primary"></i> Perjalanan
                            </h2>

                            <div class="d-flex flex-column gap-3">
                                @foreach ([
                                    ['Paket', $pendaftaran['paket']['nama'] ?: '—', 'bi-map'],
                                    ['Tanggal berangkat', $berangkat
                                        ? $berangkat->locale('id')->translatedFormat('l, d F Y')
                                            . ($sisaHari > 0 ? ' · ' . $sisaHari . ' hari lagi' : ($sisaHari === 0 ? ' · hari ini' : ''))
                                        : 'Menyusul', 'bi-calendar-event'],
                                    ['Titik jemput rombongan', $pendaftaran['titik_jemput'] ?: 'Dikonfirmasi tim', 'bi-geo-alt'],
                                ] as [$label, $nilai, $ikon])
                                    <div class="d-flex gap-3">
                                        <div class="orcha-ikon-kotak bg-gradient-blue">
                                            <i class="bi {{ $ikon }}"></i>
                                        </div>
                                        <div>
                                            <div class="orcha-label-kecil">{{ $label }}</div>
                                            <div class="fw-bold">{{ $nilai }}</div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (! empty($pendaftaran['jemput_per_titik']))
                                    <div>
                                        <div class="orcha-label-kecil mb-2">Pengelompokan jemputan</div>
                                        {{-- Inilah yang dibaca sopir: satu blok per titik, nama
                                             penumpangnya di bawahnya. Sebelumnya semuanya
                                             ditulis dalam satu baris mengalir, dan pada rombongan
                                             dua belas orang batas antar titiknya hilang. --}}
                                        @foreach ($pendaftaran['jemput_per_titik'] as $titik => $orang)
                                            <div class="orcha-jemput-blok">
                                                <div class="orcha-jemput-judul">
                                                    <i class="bi bi-geo-alt-fill"></i>
                                                    {{ $titik }}
                                                    <span class="orcha-jemput-jumlah">{{ count($orang) }} orang</span>
                                                </div>
                                                <div class="orcha-jemput-nama">{{ implode(' · ', $orang) }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ============ PEMBAYARAN ============ --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                {{-- "Bukti Pembayaran" hanya benar bila semuanya berupa
                                     bukti. Sejak pembayaran publik lewat gerbang, sebagian
                                     baris di sini tidak punya bukti apa pun dan memang
                                     tidak seharusnya punya. --}}
                                <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem">
                                    <i class="bi bi-cash-stack text-primary"></i> Pembayaran Masuk
                                </h2>
                                {{-- Membawa kode pesanannya, bukan mendarat di seluruh
                                     daftar.

                                     Admin yang menekannya sedang melihat SATU pesanan;
                                     menjatuhkannya ke daftar penuh berarti menyuruhnya
                                     mengetik ulang kode yang barusan ada di layarnya —
                                     lalu mencarinya lagi di antara pesanan orang lain. --}}
                                <a href="{{ route('admin.orcha.pembayaran', ['cari' => $pendaftaran['kode']]) }}"
                                    class="text-decoration-none" style="font-size:.8rem">
                                    Kelola pembayaran ini
                                </a>
                            </div>

                            @php
                                /*
                                 | Total kode unik seluruh pemesanan ini.
                                 |
                                 | Kode unik TIDAK mengurangi tagihan — ia penanda, bukan
                                 | cicilan. Tetapi uangnya nyata masuk ke rekening, dan
                                 | selisih antara "uang diterima" dan "masuk ke tagihan"
                                 | itulah yang bikin admin ragu saat mencocokkan dengan
                                 | dashboard DOKU. Dijumlahkan sekali di sini supaya
                                 | selisihnya punya angka, bukan jadi teka-teki.
                                 */
                                $totalKodeUnik = collect($pembayaran)
                                    ->sum(fn ($b) => (int) ($b['rincian']['kode_unik'] ?? 0));
                                $totalDiterima = collect($pembayaran)
                                    ->where('status', 'diterima')
                                    ->sum(fn ($b) => (int) ($b['rincian']['total'] ?? $b['nominal']));
                            @endphp

                            @forelse ($pembayaran as $bayar)
                                @php $lewatGerbang = ($bayar['kanal'] ?? 'transfer') === 'doku'; @endphp

                                <div class="d-flex gap-3 pb-3 mb-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                    {{-- Pembayaran gerbang tidak menempati kolom gambar sama
                                         sekali — tidak dengan foto, dan tidak dengan kotak
                                         pengganti.

                                         Kotak "Tanpa bukti" jujur untuk transfer manual yang
                                         buktinya belum dilampirkan: di sana memang ada yang
                                         hilang. Untuk pembayaran gerbang tidak ada yang
                                         hilang, dan kotak apa pun di tempat itu tetap
                                         terbaca sebagai tempat gambar — admin lalu mencari
                                         berkas yang tidak akan pernah ada. Metodenya
                                         disebutkan di barisnya, tempat keterangan memang
                                         seharusnya berada. --}}
                                    @if ($lewatGerbang)
                                    @elseif ($bayar['bukti'])
                                        {{-- Dibuka menumpang di halaman ini, bukan di tab baru:
                                             admin yang sedang mencocokkan pembayaran tidak perlu
                                             kehilangan posisi gulungnya. --}}
                                        <img src="{{ $tautanBukti($bayar['bukti']) }}" alt="Bukti transfer"
                                            class="orcha-bukti"
                                            data-bukti="{{ $tautanBukti($bayar['bukti']) }}"
                                            data-bukti-keterangan="{{ $bayar['nominal_formatted'] }} · {{ $bayar['jenis_label'] }} · {{ $bayar['bank_pengirim'] }} a.n. {{ $bayar['atas_nama_pengirim'] }}"
                                            title="Klik untuk memperbesar">
                                    @else
                                        {{-- Kotak abu kosong menimbulkan pertanyaan sendiri
                                             ("gambarnya gagal dimuat?"), jadi keadaannya
                                             disebutkan apa adanya. --}}
                                        <div class="orcha-bukti orcha-bukti-kosong">
                                            <i class="bi bi-image"></i>
                                            <span>Tanpa bukti</span>
                                        </div>
                                    @endif

                                    <div class="flex-grow-1">
                                        {{-- Nominal yang MASUK TAGIHAN, dan itu yang paling besar
                                             di baris ini. Untuk pembayaran gerbang ia berbeda
                                             dari uang yang diterima — pecahannya menyusul di
                                             bawah, tetapi yang pertama ditangkap mata harus
                                             angka yang menggerakkan sisa tagihan. --}}
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <span class="orcha-nominal-utama">{{ $bayar['nominal_formatted'] }}</span>
                                            <span class="badge orcha-lencana-bayar-{{ $bayar['status'] }}">
                                                {{ $bayar['status_label'] }}
                                            </span>
                                        </div>

                                        {{-- Jenis dipisahkan jadi keping, tanggal dibiarkan
                                             redup.

                                             Keduanya dulu satu baris abu dipisah titik, dan
                                             "Pelunasan · 05 Sep 2026" terbaca sebagai satu
                                             frasa. Padahal jenisnya yang menentukan arti baris
                                             ini — uang muka, angsuran, atau pelunasan
                                             menggerakkan status pesanan dengan cara yang
                                             berbeda — sedangkan tanggalnya cuma keterangan. --}}
                                        <div class="orcha-jenis-tanggal">
                                            <span class="jenis jenis-{{ $bayar['jenis'] }}">{{ $bayar['jenis_label'] }}</span>
                                            <span class="tgl">
                                                {{ $bayar['tanggal_transfer'] ? \Carbon\Carbon::parse($bayar['tanggal_transfer'])->locale('id')->translatedFormat('d M Y') : '—' }}
                                            </span>
                                        </div>
                                        {{-- Untuk pembayaran gerbang, yang berguna bukan
                                             "a.n. siapa" — nama itu nama pemesannya sendiri —
                                             melainkan pecahan angkanya. --}}
                                        @if ($lewatGerbang)
                                            {{-- Metodenya naik ke sini, menggantikan kolom
                                                 gambar yang sudah tidak ada. --}}
                                            {{-- Metodenya berwarna merek, bukan abu.

                                                 Ia satu-satunya keterangan yang menggantikan
                                                 kolom gambar yang sudah dihapus — kalau ikut
                                                 redup seperti sisanya, kolom itu terbaca
                                                 kosong begitu saja. --}}
                                            <div class="orcha-metode-bayar">
                                                <i class="bi bi-lightning-charge-fill"></i>
                                                {{ $bayar['bank_pengirim'] }}
                                            </div>

                                            {{-- Hitungannya diberi warna per peran, bukan
                                                 satu blok abu seragam.

                                                 Tiga angka berdampingan dalam satu warna
                                                 memaksa mata membacanya sebagai kalimat —
                                                 padahal yang perlu ditangkap justru
                                                 hubungannya: mana yang diterima, mana yang
                                                 masuk tagihan, mana yang cuma penanda.
                                                 Warna mengerjakan pemisahan itu tanpa
                                                 menambah satu kata pun. --}}
                                            @if ($rincian = $bayar['rincian'] ?? null)
                                                {{-- Labelnya "Dibayar", bukan "Diterima".

                                                     Lencana status di kanan atas juga berbunyi
                                                     DITERIMA, dan artinya lain sama sekali —
                                                     yang satu keadaan catatannya, yang satu
                                                     uang yang masuk. Dua kata sama dalam satu
                                                     baris memaksa pembacanya menebak mana yang
                                                     dimaksud. --}}
                                                <div class="orcha-pecah-bayar">
                                                    <span class="lbl">Dibayar</span>
                                                    <span class="tot">Rp {{ number_format($rincian['total'], 0, ',', '.') }}</span>
                                                    <span class="op">=</span>
                                                    <span class="pokok">Rp {{ number_format($rincian['pokok'], 0, ',', '.') }}</span>
                                                    <span class="op">+</span>
                                                    <span class="unik">kode unik {{ number_format($rincian['kode_unik'], 0, ',', '.') }}</span>
                                                </div>
                                                <div class="orcha-nomor-tagihan">
                                                    <i class="bi bi-receipt-cutoff"></i> {{ $rincian['invoice'] }}
                                                </div>
                                            @endif
                                        @else
                                            {{-- Jalur manual: banknya adalah METODE-nya, jadi
                                                 diberi bobot yang sama dengan metode gerbang.
                                                 Nama pengirim tetap redup — ia yang dicocokkan
                                                 saat ragu, bukan yang dipindai sekilas. --}}
                                            <div class="orcha-metode-bayar bank">
                                                <i class="bi bi-bank"></i>
                                                {{ $bayar['bank_pengirim'] }}
                                                <span class="atas-nama">a.n. {{ $bayar['atas_nama_pengirim'] }}</span>
                                            </div>
                                        @endif

                                        {{-- Catatan admin diredam menjadi baris terakhir yang
                                             paling ringan. Ia keterangan, bukan angka — dan
                                             baris yang seberat angkanya membuat mata berhenti
                                             di tempat yang salah. --}}
                                        @if ($bayar['catatan_admin'])
                                            <div class="orcha-catatan-baris">
                                                <span class="lbl">Catatan admin</span>
                                                {{ $bayar['catatan_admin'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if ($loop->last && $totalKodeUnik > 0)
                                    {{-- Ringkasan kode unik seluruh pemesanan.

                                         Inilah selisih antara uang yang masuk rekening dan
                                         angka yang mengurangi tagihan. Tanpa baris ini,
                                         admin yang mencocokkan dengan dashboard DOKU
                                         menemukan selisih beberapa ribu dan tidak punya
                                         cara tahu itu wajar. --}}
                                    <div class="orcha-ringkas-unik">
                                        <div class="baris">
                                            <span>Total diterima</span>
                                            <span>Rp {{ number_format($totalDiterima, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="baris unik">
                                            <span>Di antaranya kode unik <em>(penanda, bukan cicilan)</em></span>
                                            <span>Rp {{ number_format($totalKodeUnik, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-center py-4">
                                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-cash-coin"></i></div>
                                    <p class="text-muted mb-0" style="font-size:.88rem">
                                        Belum ada pembayaran yang masuk untuk pemesanan ini.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                    {{-- ============ ANGSURAN ============

                         BERAPA KALI DITENTUKAN SISTEM. Layar ini menampilkan yang
                         diizinkan berikut nominalnya, dan admin memilih dari situ —
                         tidak pernah mengetik angkanya sendiri.

                         Nominalnya ikut ditampilkan untuk tiap pilihan, bukan hanya
                         jumlah terminnya. Admin yang menjelaskan lewat WhatsApp butuh
                         angka itu di layarnya; "boleh 3x" tanpa nominal adalah janji
                         yang tidak bisa dinilai orang yang sedang menghitung
                         kemampuannya. --}}
                    {{-- Kartu ini TIDAK ditampilkan untuk pesanan yang sudah lunas.

                         Tidak ada sisa tagihan yang bisa diangsur, jadi tidak ada
                         keputusan yang menunggu admin — dan kartu yang isinya hanya
                         penolakan menyuruh orang membaca sesuatu yang tidak
                         berkonsekuensi apa pun.

                         Pengecualiannya: pesanan yang lunas LEWAT angsuran. Di sana
                         jadwalnya adalah riwayat, dan riwayat yang hilang begitu lunas
                         justru yang dicari saat ada yang dipersoalkan. --}}
                    @if (empty($angsuran['lunas']) || ! empty($angsuran['rencana']))
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem">
                                    <i class="bi bi-calendar2-week text-primary"></i> Angsuran
                                </h2>

                                @php
                                    // Dihitung dari terminnya sendiri, bukan dari status
                                    // pesanan: yang ditanyakan kartu ini "jadwalnya sudah
                                    // tuntas belum", dan itu dijawab baris-barisnya.
                                    $terminRencana = $angsuran['rencana']['termin'] ?? [];
                                    $rencanaSelesai = $terminRencana !== []
                                        && collect($terminRencana)->every(fn ($t) => $t['status'] === 'lunas');
                                    $adaTelat = collect($terminRencana)->contains(fn ($t) => $t['status'] === 'telat');

                                    // Dirakit di sini, bukan disusun dari potongan di dalam
                                    // markup: "{{ n }}×" pada baris terpisah dari katanya
                                    // menghasilkan baris baru di tengah label, dan yang
                                    // membaca sumbernya tidak melihat kalimat utuhnya.
                                    $labelRencana = ($angsuran['rencana']['jumlah_termin'] ?? 0).'× '
                                        .($rencanaSelesai ? 'selesai' : ($adaTelat ? 'ada yang telat' : 'berjalan'));
                                @endphp

                                @if (! empty($angsuran['rencana']))
                                    {{-- "Berjalan" hanya benar selama masih ada yang belum
                                         lunas. Rencana yang sudah tuntas tetapi dilabeli
                                         berjalan membuat admin mengira masih ada yang perlu
                                         ditagih — dan ia menelepon orang yang sudah selesai
                                         membayar. --}}
                                    <span class="badge {{ $adaTelat ? 'orcha-lencana-bayar-ditolak' : 'orcha-lencana-bayar-diterima' }}">{{ $labelRencana }}</span>
                                @endif
                            </div>

                            @if (! empty($angsuran['rencana']))
                                @php $rencana = $angsuran['rencana']; @endphp

                                <ul class="orcha-termin">
                                    @foreach ($rencana['termin'] as $termin)
                                        <li class="baris {{ $termin['status'] }}">
                                            <span class="urut">{{ $termin['urutan'] }}</span>

                                            <div class="isi">
                                                <div class="fw-semibold">
                                                    {{ $termin['urutan'] === 1 ? 'Uang muka' : 'Angsuran ke-' . ($termin['urutan'] - 1) }}
                                                </div>
                                                <div class="text-muted" style="font-size:.76rem">
                                                    Jatuh tempo
                                                    {{ \Carbon\Carbon::parse($termin['jatuh_tempo'])->locale('id')->translatedFormat('j F Y') }}
                                                    @if ($termin['kurang'] > 0 && $termin['kurang'] < $termin['nominal'])
                                                        · kurang Rp {{ number_format($termin['kurang'], 0, ',', '.') }}
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="text-end">
                                                <div class="fw-bold">{{ $termin['nominal_teks'] }}</div>
                                                <span class="tanda">
                                                    {{ ['lunas' => 'Lunas', 'telat' => 'Telat', 'menunggu' => 'Menunggu'][$termin['status']] }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($rencana['catatan'])
                                    <div class="orcha-cek-catatan mt-3">{{ $rencana['catatan'] }}</div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                                    <span class="text-muted" style="font-size:.76rem">
                                        Dibuat {{ $rencana['dibuat_oleh'] ?: 'admin' }}
                                        @if ($rencana['dibuat_pada'])
                                            · {{ \Carbon\Carbon::parse($rencana['dibuat_pada'])->locale('id')->translatedFormat('j M Y') }}
                                        @endif
                                    </span>

                                    {{-- Tidak ada yang bisa dibatalkan dari jadwal yang sudah
                                         tuntas, dan menawarkannya bukan sekadar tidak berguna
                                         — merusak. Yang tersisa dari rencana selesai cuma
                                         catatannya: bukti bahwa pelanggan diberi keringanan,
                                         jadwalnya apa, dan ia menyelesaikannya. Itu persis
                                         yang dicari saat belakangan ada yang dipersoalkan.

                                         Endpoint-nya juga menolak, bukan cuma tombolnya yang
                                         disembunyikan — layar boleh dilewati. --}}
                                    @if ($rencanaSelesai)
                                        <span class="orcha-rencana-tuntas">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Seluruh termin lunas — jadwalnya disimpan sebagai riwayat
                                        </span>
                                    @else
                                        {{-- Membatalkan rencana TIDAK mengubah tagihannya, dan itu
                                             disebut di dialognya — admin yang mengira ia sedang
                                             menghapus utang pelanggan akan ragu menekannya. --}}
                                        <button type="button" class="orcha-btn orcha-btn-bahaya pcek-konfirmasi"
                                            data-action="batalkanAngsuran"
                                            data-title="Batalkan rencana angsuran?"
                                            data-text="Jadwalnya dihapus, tetapi tagihannya tidak berubah — uang yang sudah masuk tetap masuk, dan sisanya kembali jatuh tempo H-{{ $aturanBayar['pelunasan_hari_sebelum'] ?? 5 }} sebelum berangkat."
                                            data-confirm="Ya, batalkan">
                                            <i class="bi bi-x-circle"></i> Batalkan rencana
                                        </button>
                                    @endif
                                </div>

                            @elseif (! ($angsuran['boleh_diangsur'] ?? false))
                                {{-- Alasannya disebut, bukan sekadar "tidak boleh": admin yang
                                     harus menjelaskan ke pelanggan butuh kalimatnya. --}}
                                <div class="orcha-alasan">
                                    <span class="orcha-label-kecil orcha-ikon-teks">
                                        <i class="bi bi-info-circle"></i> Belum bisa diangsur
                                    </span>
                                    <div style="font-size:.84rem" class="mt-1">
                                        {{ $angsuran['alasan'] ?? 'Pesanan ini belum memenuhi syarat angsuran.' }}
                                    </div>
                                </div>

                            @elseif (! $formulirAngsuran)
                                <p class="text-muted mb-3" style="font-size:.88rem">
                                    Sistem membolehkan maksimal
                                    <strong>{{ $angsuran['maks_termin'] }}× angsuran</strong> untuk pesanan ini.
                                    Berikan hanya bila pelanggan memang memintanya.
                                </p>

                                {{-- Memakai .orcha-btn, bukan .btn Bootstrap mentah.

                                     .btn lemon dipatok padding 10px 20px !important dan tidak
                                     mengatur perataan isinya, jadi ikon dan teks jatuh sebagai
                                     dua benda terpisah — plusnya menggantung sendiri di atas
                                     tulisannya. .orcha-btn sudah inline-flex, tengah, bergap,
                                     dan nowrap; itulah bentuk tombol yang dipakai 88 tempat
                                     lain di panel ini. --}}
                                <button type="button" class="orcha-btn orcha-btn-utama"
                                    wire:click="bukaFormulirAngsuran">
                                    <i class="bi bi-plus-lg"></i> Buat rencana angsuran
                                </button>

                            @else
                                {{-- Angka yang sedang DIBAGI, disebut lebih dulu.

                                     Admin yang memilih "3×" sedang membagi sebuah angka, dan
                                     angka itu harus ada di layar yang sama — bukan diingat
                                     dari kartu lain yang sudah tergulung ke atas. --}}
                                <div class="orcha-angsuran-konteks">
                                    <span class="lbl">Yang dibagi</span>
                                    <span class="nil">{{ $angsuran['total_teks'] }}</span>
                                    <span class="ket">seluruh tagihan pesanan ini</span>
                                </div>

                                {{-- Arti "jatuh tempo" dijelaskan sekali di sini, bukan
                                     diulang di tiap baris.

                                     Yang ditanyakan admin bukan tanggalnya melainkan sifatnya:
                                     apakah itu hari pelanggan harus membayar, atau batas
                                     akhirnya. Perbedaannya nyata — yang mengira itu tanggal
                                     pasti akan menelepon pelanggan yang sebenarnya belum
                                     terlambat. --}}
                                <p class="orcha-angsuran-arahan">
                                    Pilih berapa kali pelanggan akan membayar. Termin pertama selalu
                                    sebesar uang muka — kursinya baru ditahan setelah itu masuk.
                                    Tanggal di bawah adalah <strong>batas akhir tiap termin</strong>;
                                    pelanggan boleh membayar lebih awal, dan termin terakhir selalu
                                    jatuh tepat di batas pelunasan.
                                </p>

                                <div class="orcha-pilih-termin">
                                    @foreach ($angsuran['pilihan'] as $opsi)
                                        @php $terpilih = $terminDipilih === $opsi['jumlah_termin']; @endphp

                                        <label class="opsi {{ $terpilih ? 'aktif' : '' }}">
                                            <input type="radio" wire:model.live="terminDipilih"
                                                value="{{ $opsi['jumlah_termin'] }}" class="d-none">

                                            <div class="kepala">
                                                <div>
                                                    <span class="judul">{{ $opsi['jumlah_termin'] }}× pembayaran</span>
                                                    {{-- Diterjemahkan jadi kalimat, karena "3×" saja
                                                         tidak memberi tahu apa isinya. --}}
                                                    <span class="sub">
                                                        uang muka + {{ $opsi['jumlah_termin'] - 1 }} angsuran
                                                    </span>
                                                </div>

                                                <span class="tandanya">
                                                    <i class="bi {{ $terpilih ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                                </span>
                                            </div>

                                            <ul class="rinci">
                                                @foreach ($opsi['termin'] as $t)
                                                    <li>
                                                        <span class="urut">{{ $t['urutan'] }}</span>
                                                        <span class="apa">{{ $t['label'] }}</span>
                                                        {{-- Tanggalnya DIBERI LABEL, tidak berdiri
                                                             telanjang.

                                                             "26 Sep 2026" sendirian tidak menjawab
                                                             pertanyaan pertama yang muncul: itu
                                                             tanggal apa — hari bayarnya, atau batas
                                                             akhirnya? Admin yang menebak salah akan
                                                             menjanjikan hal yang salah pula ke
                                                             pelanggan.

                                                             Kata yang dipakai "jatuh tempo", sama
                                                             dengan jadwal berjalan, halaman
                                                             pembayaran pelanggan, dan surat
                                                             pengingatnya. Dua istilah untuk satu hal
                                                             lebih membingungkan daripada satu
                                                             istilah yang perlu dipelajari sekali. --}}
                                                        <span class="kapan">
                                                            <span class="lbl">Jatuh tempo</span>
                                                            {{ \Carbon\Carbon::parse($t['jatuh_tempo'])->locale('id')->translatedFormat('j M Y') }}
                                                        </span>
                                                        <span class="berapa">{{ $t['nominal_teks'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </label>
                                    @endforeach
                                </div>

                                {{-- Akibat yang akan terjadi, disebut SEBELUM tombolnya ditekan.

                                     Admin awam tidak tahu apa yang berubah bagi pelanggan setelah
                                     ia menekan Terbitkan — dan yang tidak tahu cenderung tidak
                                     menekan sama sekali, lalu mengurus angsurannya lewat
                                     percakapan seperti sebelum fitur ini ada. --}}
                                <div class="orcha-angsuran-akibat">
                                    <i class="bi bi-info-circle"></i>
                                    <div>
                                        Setelah diterbitkan, pelanggan melihat jadwal ini di halaman
                                        pembayaran dan membayar tiap termin sendiri. Ia diingatkan lewat
                                        email <strong>{{ $angsuran['ingatkan_hari_sebelum'] }} hari</strong>
                                        sebelum tiap jatuh tempo, dan <strong>kotak surat kantor</strong>
                                        dikabari bila ada termin yang terlewat.
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label small fw-semibold mb-1">
                                        Catatan <span class="text-muted fw-normal">(internal — hanya dibaca admin)</span>
                                    </label>
                                    <input type="text" class="form-control" wire:model="catatanAngsuran"
                                        placeholder="Mis. diminta wali murid, dibayar per gajian.">
                                    <div class="form-text">
                                        Alasan keringanan ini diberikan — berguna saat admin lain
                                        membukanya berbulan-bulan kemudian.
                                    </div>
                                </div>

                                {{-- Terbitkan di kanan, Batal di kiri — urutan yang sama
                                     dengan kaki lembar cek pembayaran, supaya tangan admin
                                     tidak perlu belajar dua kebiasaan di satu panel. --}}
                                {{-- Dua tombol setengah lebar, bukan sepasang tombol kecil di
                                     pojok kanan. Kaki kartu yang separuhnya kosong terbaca
                                     seperti ada yang gagal dimuat — dan tombol lebar juga lebih
                                     mudah ditekan di layar sentuh, tempat sebagian admin
                                     mengurus pesanan. --}}
                                <div class="row g-2 mt-3">
                                    <div class="col-6">
                                        <button type="button"
                                            class="orcha-btn orcha-btn-lembut orcha-tombol-lembar"
                                            wire:click="tutupFormulirAngsuran">
                                            <i class="bi bi-x-lg"></i> Batal
                                        </button>
                                    </div>

                                    <div class="col-6">
                                        <button type="button"
                                            class="orcha-btn orcha-btn-utama orcha-tombol-lembar"
                                            wire:click="simpanAngsuran" wire:target="simpanAngsuran"
                                            wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="simpanAngsuran"
                                                class="orcha-ikon-teks">
                                                <i class="bi bi-check-lg"></i> Terbitkan jadwal
                                            </span>
                                            <span wire:loading wire:target="simpanAngsuran">Menyimpan…</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.pratinjau-bukti')
    {{-- ============ PILIHAN PESAN WHATSAPP ============

         Pesannya disusun di komponen, bukan di sini: nominal dan tenggatnya
         berasal dari aturan Orcha yang sama dengan yang dipakai kwitansi, jadi
         angka di percakapan tidak pernah berbeda dari angka di berkas. --}}
    {{-- ============ PILIHAN PESAN WHATSAPP ============

         Popup berdiri sendiri, bukan modal Bootstrap.

         Kelasnya memang terpasang, tetapi JS-nya tidak pernah dimuat —
         resources/js/bootstrap.js di repo ini berisi axios, bukan Bootstrap —
         dan aset Vite pun tidak ikut ter-deploy. Tombol ber-data-bs-toggle
         karenanya diam saja di server, dan itu baru ketahuan setelah dipakai.

         Pesannya disusun di komponen, bukan di sini: nominal dan tenggatnya
         berasal dari aturan Orcha yang sama dengan yang dipakai kwitansi, jadi
         angka di percakapan tidak pernah berbeda dari angka di berkas. --}}
    <div class="orcha-lembar" id="pilihanWa" hidden>
        <div class="orcha-lembar-tirai" onclick="orchaTutupLembar('pilihanWa')"></div>

        <div class="orcha-lembar-isi" role="dialog" aria-modal="true" aria-label="Kirim pesan WhatsApp">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <div class="fw-bold orcha-judul-ikon" style="font-size:1.05rem">
                        <i class="bi bi-whatsapp" style="color:#25d366"></i>
                        Kirim pesan ke {{ $pendaftaran['nama'] ?? 'pemesan' }}
                    </div>
                    <div class="text-muted" style="font-size:.8rem">
                        Pesannya sudah terisi lengkap dengan angka dan tautannya — tinggal periksa lalu kirim.
                    </div>
                </div>

                <button type="button" class="orcha-hapus-baris" aria-label="Tutup"
                    onclick="orchaTutupLembar('pilihanWa')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            @forelse ($pilihanPesan as $pilihan)
                {{-- href memuat versi POLOS; data-wa-pesan memuat yang berpenanda.

                     Skrip di partial salin-wa merakit emojinya di peramban lalu
                     menyusun ulang tautannya saat diklik — emoji tidak pernah ikut
                     melewati respons server, dan justru di perjalanan itulah ia
                     berubah jadi tanda tanya. Bila skripnya tidak sempat jalan,
                     yang terkirim tetap kalimat utuh tanpa emoji. --}}
                <a class="orcha-pilihan-wa"
                    href="{{ $this->tautanWa($pilihan['polos']) }}"
                    data-wa-pesan="{{ $pilihan['pesan'] }}"
                    target="_blank" rel="noopener">
                    <span class="orcha-ikon {{ $pilihan['rupa'] }}">
                        <i class="bi {{ $pilihan['ikon'] }}"></i>
                    </span>

                    <span class="flex-grow-1">
                        <span class="d-block fw-bold" style="font-size:.92rem;color: var(--orc-tinta)">
                            {{ $pilihan['judul'] }}
                        </span>
                        <span class="d-block text-muted" style="font-size:.78rem">
                            {{ $pilihan['ringkas'] }}
                        </span>
                    </span>

                    <i class="bi bi-box-arrow-up-right text-muted"></i>
                </a>
            @empty
                <p class="text-muted text-center mb-0 py-2" style="font-size:.88rem">
                    Tidak ada yang perlu ditagih atau dikirimkan untuk pendaftaran ini —
                    pembayarannya lunas dan riwayat kesehatannya sudah lengkap.
                </p>
            @endforelse

            {{-- Selalu ada, di bawah pilihan yang sudah terisi: kadang yang perlu
                 disampaikan memang tidak ada di daftar mana pun. --}}
            <a class="orcha-pilihan-wa orcha-pilihan-wa-polos"
                href="{{ $this->tautanWa('') ?: $wa($pendaftaran['whatsapp']) }}"
                target="_blank" rel="noopener">
                <span class="orcha-ikon orcha-ikon-netral">
                    <i class="bi bi-chat-dots"></i>
                </span>

                <span class="flex-grow-1">
                    <span class="d-block fw-bold" style="font-size:.92rem;color: var(--orc-tinta)">
                        Buka percakapan kosong
                    </span>
                    <span class="d-block text-muted" style="font-size:.78rem">
                        Menulis sendiri, tanpa pesan siap pakai
                    </span>
                </span>

                <i class="bi bi-box-arrow-up-right text-muted"></i>
            </a>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.salin-wa')
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
