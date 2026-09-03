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
                                ['Peserta', $pendaftaran['jumlah_peserta'] . ' orang', '', 'bi-people'],
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
                        </div>                    </div>
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

                            @if ($pendaftaran['catatan'])
                                <div class="mt-3 p-3 rounded-3 bg-light">
                                    <div class="orcha-label-kecil">Catatan dari pemesan</div>
                                    <div style="font-size:.9rem">{{ $pendaftaran['catatan'] }}</div>
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
                                <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem">
                                    <i class="bi bi-cash-stack text-primary"></i> Bukti Pembayaran
                                </h2>
                                <a href="{{ route('admin.orcha.pembayaran') }}" class="text-decoration-none"
                                    style="font-size:.8rem">Kelola semua</a>
                            </div>

                            @forelse ($pembayaran as $bayar)
                                <div class="d-flex gap-3 pb-3 mb-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                    @if ($bayar['bukti'])
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
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <span class="fw-bold">{{ $bayar['nominal_formatted'] }}</span>
                                            <span class="badge orcha-lencana-bayar-{{ $bayar['status'] }}">
                                                {{ $bayar['status_label'] }}
                                            </span>
                                        </div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $bayar['jenis_label'] }} ·
                                            {{ $bayar['tanggal_transfer'] ? \Carbon\Carbon::parse($bayar['tanggal_transfer'])->locale('id')->translatedFormat('d M Y') : '—' }}
                                        </div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $bayar['bank_pengirim'] }} a.n. {{ $bayar['atas_nama_pengirim'] }}
                                        </div>
                                        @if ($bayar['catatan_admin'])
                                            <div class="mt-1" style="font-size:.78rem">
                                                <span class="orcha-label-kecil d-inline">Catatan admin:</span>
                                                {{ $bayar['catatan_admin'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-cash-coin"></i></div>
                                    <p class="text-muted mb-0" style="font-size:.88rem">
                                        Belum ada bukti transfer yang dikirim pelanggan.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
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
