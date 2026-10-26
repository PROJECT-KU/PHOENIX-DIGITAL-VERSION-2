@section('title')
Jejak Audit Orcha || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         dasbor Orcha. Kelas orcha-* hanya dipakai untuk hal yang memang khas
         Orcha (cip status, paginasi API). --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $jumlahSaringan = $this->jumlahSaringan();
        $adaSaringan = $jumlahSaringan > 0;
        $jumlahLanjut = $this->jumlahSaringanLanjut();

        /*
         | Rupa per JENIS aksi: ikon + warna.
         |
         | Tanpa ini "membuka riwayat kesehatan" (akses data pribadi) dan
         | "tambah master harga" (rutin) tampil persis sama, sehingga yang
         | berbobot hanya ketahuan setelah tiap baris dibaca satu per satu.
         | Dicocokkan dari KATA KUNCI, bukan daftar tertutup: aksi baru muncul
         | seiring fitur Orcha bertambah, dan daftar tertutup akan menjatuhkan
         | semuanya ke abu-abu tanpa ada yang menyadarinya.
         */
        $rupaAksi = function (string $aksi): array {
            $a = mb_strtolower($aksi);

            return match (true) {
                str_contains($a, 'bayar') || str_contains($a, 'angsuran') || str_contains($a, 'dana')
                    => ['bi-cash-coin', '#047857'],
                str_contains($a, 'batal') || str_contains($a, 'hapus') || str_contains($a, 'tolak')
                    => ['bi-x-octagon', '#b91c1c'],
                str_contains($a, 'riwayat kesehatan') || str_contains($a, 'peserta') || str_contains($a, 'pelanggan')
                    => ['bi-person-vcard', '#b45309'],
                str_contains($a, 'promo') || str_contains($a, 'rujukan') || str_contains($a, 'harga') || str_contains($a, 'rab')
                    => ['bi-tags', '#7c3aed'],
                str_contains($a, 'status') => ['bi-arrow-left-right', '#0ea5e9'],
                str_contains($a, 'daftar') || str_contains($a, 'buat') || str_contains($a, 'tambah')
                    => ['bi-plus-circle', '#4f46e5'],
                default => ['bi-pencil-square', '#64748b'],
            };
        };

        /*
         | Kode pesanan jadi tautan ke daftar yang bersangkutan.
         |
         | Yang dikirim kata kuncinya, bukan id: jejak hanya menyimpan kode,
         | dan menebak id dari kode berarti menebak. Daftar tujuan yang
         | mencarikannya — itu juga yang dilakukan admin secara manual selama
         | ini, hanya dengan menyalin-tempel.
         */
        $tautanKode = function (?string $kode): ?string {
            if (! $kode) {
                return null;
            }

            return match (true) {
                str_starts_with($kode, 'SK-') => route('admin.orcha.penyewaan', ['cari' => $kode]),
                str_starts_with($kode, 'OT-') => route('admin.orcha.pendaftaran', ['cari' => $kode]),
                default => null,
            };
        };

        // Pengelompokan per hari: daftar yang mengalir tanpa pemisah memaksa
        // mata membandingkan tanggal baris demi baris.
        $hariTerakhir = null;

        $namaHari = function (?string $iso): string {
            if (! $iso) {
                return 'Tanpa tanggal';
            }

            $t = \Illuminate\Support\Carbon::parse($iso);

            return match (true) {
                $t->isToday() => 'Hari ini',
                $t->isYesterday() => 'Kemarin',
                default => $t->locale('id')->translatedFormat('l, d F Y'),
            };
        };

        /*
         | Nilai status dibaca sebagai nama, bukan isi kolom.
         |
         | Yang dicatat Orcha nilai mesinnya — "dp_masuk", "menunggu" — dan
         | itulah yang dulu tercetak apa adanya. Admin membaca "dp_masuk" di
         | sini sementara hal yang sama bernama "DP Masuk" di layar sebelah;
         | garis bawah dan huruf kecil itu bocoran isi basis data.
         |
         | Yang tidak dikenal rujukan (mis. nominal) tetap tampil, cuma
         | garis bawahnya diganti spasi — lebih baik apa adanya daripada hilang.
         */
        $namaNilai = function (?string $nilai) use ($petaNilai): string {
            $n = trim((string) $nilai);

            if ($n === '') {
                return '';
            }

            return $petaNilai[$n] ?? \Illuminate\Support\Str::ucfirst(str_replace('_', ' ', $n));
        };
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Jejak Audit</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Siapa mengubah apa, kapan.</span>
                    <span class="d-block">Hanya bisa dibaca — tidak ada yang bisa menghapusnya dari sini.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #2b7fb8">
                    <i class="bi bi-speedometer2"></i><span>Dashboard Orcha</span>
                </a>
            </div>
        </header>

        {{-- Tanpa pita ini, layar yang datanya GAGAL diambil tampil persis
             seperti layar yang datanya memang kosong — "Belum ada perubahan
             tercatat" di atas jejak yang sebenarnya penuh. Yang membacanya
             menyimpulkan catatannya hilang. --}}
        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        {{-- Satu baris, tiga kotak.

                             Sebelumnya bilah ini enam medan berlabel plus sederet
                             tombol rentang plus satu baris kaki — tiga susun yang
                             memenuhi layar sebelum satu pun catatan terbaca, dan
                             terbaca seperti formulir yang harus diisi dulu.

                             Yang tersisa di depan cuma yang ditanyakan tiap hari:
                             apa, siapa, kapan. Labelnya pun dilepas — pilihan
                             pertama tiap kotak ("Semua admin", "Kapan saja") sudah
                             menyebut dirinya sendiri, jadi label di atasnya hanya
                             mengulang dengan huruf kecil. --}}
                        {{-- Satu baris, tiga kotak.

                             Sebelumnya bilah ini enam medan berlabel plus sederet
                             tombol rentang plus satu baris kaki — tiga susun yang
                             memenuhi layar sebelum satu pun catatan terbaca, dan
                             terbaca seperti formulir yang harus diisi dulu.

                             Yang tersisa di depan cuma yang ditanyakan tiap hari:
                             apa, siapa, kapan. Labelnya pun dilepas — pilihan
                             pertama tiap kotak ("Semua admin", "Kapan saja") sudah
                             menyebut dirinya sendiri, jadi label di atasnya hanya
                             mengulang dengan huruf kecil. --}}
                        <div class="ja-saring">
                            {{-- Kotak carinya partial BERSAMA seluruh Orcha, bukan buatan
                                 halaman ini: bentuk dan perilakunya (termasuk tombol
                                 pengosong) harus satu di semua layar. Yang disesuaikan di
                                 sini hanya rupanya, lewat .ja-saring di bawah. --}}
                            <div class="ja-medan-cari">
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Cari kode pesanan atau nama admin…',
                                ])
                            </div>

                            <select class="dsb-isian ja-pilih" wire:model.live="filterAdmin"
                                aria-label="Saring menurut admin">
                                <option value="">Semua admin</option>
                                @foreach ($pilihanAdmin as $nama)
                                    <option value="{{ $nama }}">{{ $nama }}</option>
                                @endforeach
                            </select>

                            {{-- SATU kotak menggantikan "Dari", "Sampai", dan tiga
                                 tombol pintasan. Pertanyaannya memang "pekan lalu",
                                 bukan "tanggal berapa tujuh hari lalu" — dan yang
                                 terakhir itu yang dulu harus dihitung sendiri. --}}
                            <select class="dsb-isian ja-pilih" wire:model.live="rentang"
                                aria-label="Saring menurut waktu">
                                <option value="">Kapan saja</option>
                                @foreach ($this::RENTANG_CEPAT as $kunci => [$label, $mundur])
                                    <option value="{{ $kunci }}">{{ $label }}</option>
                                @endforeach
                                <option value="{{ $this::PILIH_SENDIRI }}">Pilih tanggal sendiri…</option>
                            </select>

                            {{-- SATU panel untuk seluruh saringan selebihnya — tanggal
                                 persis, jenis perubahan, alamat IP.

                                 Dulu dua panel terpisah yang muncul sendiri-sendiri, dan
                                 masing-masing cuma mengisi separuh kiri kartu: dua baris
                                 setengah kosong bertumpuk. Sekarang keempatnya satu baris
                                 empat kolom yang habis selebar kartunya.

                                 Terbuka sendiri bila ada yang aktif di dalamnya: daftar
                                 pendek tanpa sebab yang terlihat adalah data yang hilang,
                                 bagi yang membukanya. --}}
                            <button type="button" wire:click="$toggle('lainnya')"
                                class="dsb-tombol is-lembut ja-tombol-lain" style="--ikon: #64748b"
                                aria-expanded="{{ $lainnya ? 'true' : 'false' }}">
                                <i class="bi bi-sliders"></i><span>Saringan lain</span>
                                @if ($jumlahLanjut)
                                    <span class="ja-lencana">{{ $jumlahLanjut }}</span>
                                @endif
                                <i class="bi bi-chevron-down ja-panah {{ $lainnya ? 'is-buka' : '' }}"></i>
                            </button>

                            {{-- Yang diunduh SELURUH hasil saringan, bukan halaman yang
                                 terlihat: audit yang dilampirkan ke laporan tidak boleh
                                 terpotong di baris terakhir tanpa ada yang menyadarinya. --}}
                            <button type="button" wire:click="unduhCsv" wire:loading.attr="disabled"
                                wire:target="unduhCsv" class="dsb-tombol is-lembut" style="--ikon: #16a34a"
                                title="Unduh semua catatan yang sedang tersaring, bukan halaman ini saja">
                                <i class="bi bi-download" wire:loading.remove wire:target="unduhCsv"></i>
                                <span class="dsb-putar" wire:loading wire:target="unduhCsv"></span>
                                <span>Unduh</span>
                            </button>

                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan"
                                    class="dsb-tombol is-lembut" style="--ikon: #b45309"
                                    title="Kembali menampilkan seluruh catatan">
                                    <i class="bi bi-x-circle"></i><span>Tampilkan semua</span>
                                </button>
                            @endif
                        </div>

                        @if ($lainnya)
                            <div class="ja-saring-lipat">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="ja-dari">Dari tanggal</label>
                                    <input id="ja-dari" type="date" class="dsb-isian" wire:model.live="dari">
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="ja-sampai">Sampai tanggal</label>
                                    <input id="ja-sampai" type="date" class="dsb-isian" wire:model.live="sampai">
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="ja-aksi">Jenis perubahan</label>
                                    <select id="ja-aksi" class="dsb-isian" wire:model.live="filterAksi">
                                        <option value="">Semua jenis perubahan</option>
                                        @foreach ($pilihanAksi as $aksi)
                                            <option value="{{ $aksi }}">{{ $aksi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="ja-ip">Alamat IP</label>
                                    <input id="ja-ip" type="text" class="dsb-isian" placeholder="mis. 103.10."
                                        wire:model.live.debounce.500ms="filterIp">
                                    <span class="dsb-kartu-sub">Untuk memeriksa akses dari luar kantor</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat"
                    wire:target="cari,filterAdmin,filterAksi,filterIp,rentang,dari,sampai,baris,bersihkanSaringan,bersihkanCari,keHalaman">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0ea5e9"><i class="bi bi-clock-history"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Perubahan tercatat</h3>
                                <span class="dsb-kartu-sub">Terbaru di atas · satu baris = satu perubahan</span>
                            </div>
                        </div>
                        @if (($meta['total'] ?? null) !== null)
                            <span class="dsb-chip dsb-chip-hitung"><i class="bi bi-list-check"></i>{{ number_format((int) $meta['total'], 0, ',', '.') }} catatan</span>
                        @endif
                    </div>

                    @if ($daftar === [])
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-clock-history"></i></span>
                                <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada perubahan tercatat' }}</p>
                                <p class="dsb-kosong-ket">
                                    {{ $adaSaringan
                                        ? 'Coba ubah kata kunci, nama admin, atau rentang tanggalnya.'
                                        : 'Setiap perubahan yang dilakukan admin Orcha akan muncul di sini.' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel ja-tabel">
                                <thead>
                                    {{-- IP tidak lagi kolom sendiri: isinya hampir selalu
                                         sama, tetapi lebarnya mendorong kolom yang justru
                                         dibaca. Ia turun jadi keterangan di bawah nama. --}}
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Admin</th>
                                        <th>Perubahan</th>
                                        <th>Pesanan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php
                                            $hariIni = $namaHari($baris['waktu'] ?? null);
                                            $gantiHari = $hariIni !== $hariTerakhir;
                                            $hariTerakhir = $hariIni;

                                            [$ikonAksi, $warnaAksi] = $rupaAksi((string) $baris['aksi']);

                                            /*
                                             | Warna avatar mengikuti NAMA: dengan beberapa admin
                                             | aktif, warna tetap per orang membuat "mana pekerjaan
                                             | si A" terbaca tanpa mengeja tiap baris.
                                             |
                                             | Penyebarnya md5, bukan crc32: ketiga admin yang benar-
                                             | benar ada di data (Asthana, Everette, Rosalinda)
                                             | kebetulan bersisa SAMA di crc32 % 6, jadi ketiganya
                                             | berwarna hijau dan penandanya tidak menandai apa pun.
                                             */
                                            $warnaAdmin = ['#4f46e5', '#0ea5e9', '#b45309', '#047857', '#be185d', '#7c3aed'][intval(substr(md5((string) $baris['admin']), 0, 8), 16) % 6];

                                            $waktu = ($baris['waktu'] ?? null)
                                                ? \Illuminate\Support\Carbon::parse($baris['waktu'])
                                                : null;
                                        @endphp

                                        @if ($gantiHari)
                                            <tr class="ja-hari" wire:key="hari-{{ $baris['id'] }}">
                                                <td colspan="4">{{ $hariIni }}</td>
                                            </tr>
                                        @endif

                                        <tr wire:key="jejak-{{ $baris['id'] }}">
                                            <td data-judul="Waktu">
                                                {{-- Yang baru dibaca sebagai "2 jam lalu"; tanggal
                                                     penuhnya tetap ada di tooltip dan di CSV. --}}
                                                <span class="dsb-tabel-angka" title="{{ $baris['waktu_teks'] }}">
                                                    {{ $waktu && $waktu->gt(now()->subDays(2)) ? $waktu->locale('id')->diffForHumans() : ($waktu?->format('H:i') ?? '—') }}
                                                </span>
                                                <span class="dsb-tabel-meta">{{ $waktu?->locale('id')->translatedFormat('d M Y') }}</span>
                                            </td>
                                            <td data-judul="Admin">
                                                <span class="ja-admin">
                                                    <span class="dsb-avatar" style="--c: {{ $warnaAdmin }}">{{ \Illuminate\Support\Str::substr($baris['admin'], 0, 1) }}</span>
                                                    <span class="ja-admin-teks">
                                                        <span>{{ $baris['admin'] }}</span>
                                                        <span class="dsb-tabel-meta ja-ip">{{ $baris['ip'] ?: 'tanpa IP' }}</span>
                                                    </span>
                                                </span>
                                            </td>
                                            <td data-judul="Perubahan">
                                                {{-- Satu wadah, bukan dua kotak bersaudara.

                                                     Di ponsel sel tabel jadi baris flex "label | isi".
                                                     Dengan dua kotak, lebarnya dibagi tiga — dan nama
                                                     aksinya terjepit sampai satu huruf per baris, yang
                                                     terbaca seperti halaman rusak, bukan halaman sempit. --}}
                                                <span class="ja-ubah">
                                                <span class="ja-aksi">
                                                    <span class="dsb-ikon is-kecil" style="--c: {{ $warnaAksi }}"><i class="bi {{ $ikonAksi }}"></i></span>

                                                    {{-- Nama aksi dan perpindahannya satu kalimat di satu
                                                         kotak teks, supaya keduanya duduk di garis huruf
                                                         yang sama. Sebagai dua kotak bersebelahan,
                                                         ukurannya yang berbeda membuat kata penghubungnya
                                                         melayang — terbaca seperti salah tempel. --}}
                                                    <span class="ja-aksi-teks">
                                                        <span class="dsb-tabel-judul">{{ $baris['aksi'] }}</span>

                                                        {{-- Perpindahan status ditampilkan sebagai "dari → ke",
                                                             bukan hanya nilai akhirnya. Nilai akhir saja tidak
                                                             menjawab pertanyaan yang sebenarnya diajukan:
                                                             berubah DARI apa. --}}
                                                        @if ($baris['sebelum'] !== null || $baris['sesudah'] !== null)
                                                            {{-- Tanpa nilai awal, "— → DP Masuk" terbaca seperti
                                                                 ada yang hilang. Yang benar: memang belum punya
                                                                 keadaan sebelumnya, jadi cukup "jadi X". --}}
                                                            @if ($baris['sebelum'])
                                                                <span class="ja-nilai is-lama">{{ $namaNilai($baris['sebelum']) }}</span>
                                                                <span class="ja-kata" aria-label="berubah menjadi">&rarr;</span>
                                                            @else
                                                                <span class="ja-kata">jadi</span>
                                                            @endif
                                                            <span class="ja-nilai is-baru">{{ $baris['sesudah'] ? $namaNilai($baris['sesudah']) : '—' }}</span>
                                                        @endif
                                                    </span>
                                                </span>

                                                {{-- Keterangan dibaca sebagai rincian berlabel, bukan satu
                                                     baris "kunci: nilai, kunci: nilai" yang terbaca seperti
                                                     potongan berkas log yang tak sengaja ikut tercetak. --}}
                                                @if (($baris['rincian'] ?? []) !== [])
                                                    <span class="ja-rinci">
                                                        @foreach ($baris['rincian'] as $butir)
                                                            <span class="ja-rinci-butir" style="--c: {{ $butir['warna'] }}">
                                                                @if ($butir['label'] !== '')
                                                                    <span class="ja-rinci-label">{{ $butir['label'] }}</span>
                                                                @endif
                                                                <span class="ja-rinci-nilai">{{ $butir['nilai'] }}</span>
                                                            </span>
                                                        @endforeach
                                                    </span>
                                                @endif
                                                </span>
                                            </td>
                                            <td data-judul="Pesanan">
                                                @php $tautan = $tautanKode($baris['kode'] ?? null); @endphp

                                                @if ($baris['kode'] && $tautan)
                                                    <a href="{{ $tautan }}" wire:navigate class="ja-kode is-tautan"
                                                        title="Buka {{ $baris['kode'] }} di daftarnya">
                                                        {{ $baris['kode'] }}<i class="bi bi-arrow-up-right"></i>
                                                    </a>
                                                @elseif ($baris['kode'])
                                                    {{-- Kode yang tidak punya daftar tujuan (mis. RAB-)
                                                         tetap ditampilkan apa adanya, bukan tautan mati. --}}
                                                    <span class="ja-kode">{{ $baris['kode'] }}</span>
                                                @else
                                                    <span class="dsb-tabel-samar">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="ja-halaman">
                        {{-- "Berapa baris" adalah pertanyaan yang baru muncul SETELAH
                             daftarnya terbaca, jadi tempatnya di kaki tabel bersama
                             nomor halaman — bukan di bilah saringan di atas, tempat
                             ia jadi kotak keenam yang harus dilewati mata setiap kali
                             halaman dibuka. --}}
                        <label class="ja-baris">
                            <span>Tampilkan</span>
                            <select class="dsb-isian" wire:model.live="baris" aria-label="Jumlah baris per halaman">
                                @foreach ($this::PILIHAN_BARIS as $n)
                                    <option value="{{ $n }}">{{ $n }}</option>
                                @endforeach
                            </select>
                            <span>baris</span>
                        </label>

                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        /* Satu baris di layar lebar, melipat sendiri saat sempit. Kotak cari
           melar; dua kotak pilihan selebar isinya; tombol tetap seukurannya. */
        .ja-saring { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .ja-saring > .ja-medan-cari { flex: 1 1 260px; min-width: 0; }
        .ja-saring > .ja-pilih { flex: 0 1 auto; width: auto; min-width: 150px; max-width: 220px; }
        .ja-saring .dsb-tombol { flex: 0 0 auto; }

        /* Panel saringan selebihnya: GRID empat kolom sama lebar, bukan flex.

           Dengan flex, medan yang lebih sempit dari jatahnya menyisakan ruang
           kosong di kanan — dan dua panel setengah kosong bertumpuk terbaca
           seperti layar yang belum selesai dirapikan. Grid membagi habis
           selebar kartunya, berapa pun lebar isinya. */
        .ja-saring-lipat {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px 14px;
            /* Label tiap medan sejajar di atas: satu medan yang punya
               keterangan di bawahnya tidak boleh mendorong tetangganya. */
            align-items: start; margin-top: 12px; padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }
        .ja-saring-lipat > .dsb-medan { min-width: 0; }
        .ja-saring-lipat .dsb-kartu-sub { display: block; margin-top: 5px; }

        /* Di bawah ~992px bilah atas tidak lagi muat satu baris. Kotak cari
           mengambil barisnya sendiri dan sisanya membagi habis baris kedua —
           kalau tidak, dua tombol tertinggal sendirian di baris ketiga dengan
           separuh baris kosong di kanannya. */
        @media (max-width: 991.98px) {
            .ja-saring > .ja-medan-cari { flex: 1 1 100%; }
            .ja-saring > .ja-pilih { flex: 1 1 150px; max-width: none; }
            .ja-saring-lipat { grid-template-columns: repeat(2, 1fr); }
        }

        /* Lencana angka di tombolnya: berapa saringan yang sedang aktif di
           balik lipatan. Tanpa ini, menutup panel berarti menyembunyikan sebab
           daftarnya pendek. */
        .ja-tombol-lain { cursor: pointer; }
        .ja-lencana {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 19px; height: 19px; padding: 0 6px; border-radius: 999px;
            background: #4f46e5; color: #fff; font-size: .68rem; font-weight: 800;
        }
        .ja-panah { font-size: .7rem; color: #94a3b8; transition: transform .18s ease; }
        .ja-panah.is-buka { transform: rotate(180deg); }

        /* Di ponsel kotak cari mengambil barisnya sendiri dan dua kotak pilihan
           berbagi baris berikutnya. Tanpa ini keduanya melipat sendiri-sendiri
           — empat baris untuk tiga pertanyaan, di layar yang paling sempit. */
        @media (max-width: 575.98px) {
            .ja-saring > .ja-medan-cari { flex: 1 1 100%; }
            .ja-saring > .ja-pilih { flex: 1 1 140px; min-width: 0; max-width: none; }
            .ja-saring-lipat { grid-template-columns: 1fr; }
        }

        /* Kotak cari bersama memakai .form-control Bootstrap. Di sebelah medan
           lemon ia terbaca sebagai cacat: tingginya beda, sudutnya beda. Yang
           disamakan rupanya saja — markup & perilakunya tetap milik partial. */
        .ja-saring .form-control {
            width: 100%; min-height: 42px; padding: 9px 14px 9px 40px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
            background: #fff; color: var(--dsb-tinta, #1c1f26); font-size: .88rem;
            box-shadow: none;
        }
        .ja-saring .form-control:focus {
            border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0;
        }
        .ja-saring .form-control-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; z-index: 2;
        }

        /* Nama admin: inisial berwarna + nama, sejajar di tengah. */
        .ja-admin { display: inline-flex; align-items: center; gap: 9px; font-weight: 600; color: var(--dsb-tinta, #1c1f26); }
        .ja-admin .dsb-avatar { width: 30px; height: 30px; font-size: .78rem; }

        /* "dari → ke" dibaca sebagai satu kalimat dengan nama aksinya.

           Semuanya span sebaris di dalam satu kotak teks — BUKAN flexbox di
           sebelah judul. Dua kotak bersebelahan dengan ukuran huruf berbeda
           duduk di tengah masing-masing, bukan di garis huruf yang sama, dan
           kata penghubungnya tampak melayang separuh baris di bawah. */
        .ja-aksi-teks { min-width: 0; }
        .ja-aksi-teks .dsb-tabel-judul { display: inline; }

        /* Kata penghubung ("jadi", panah) berwarna lain dari nilainya: ia
           perekat kalimat, bukan isi yang dibaca. Dulu abu-abu yang sama
           dengan nilai lama, sehingga "menunggu" dan panahnya terbaca
           sebagai satu benda. */
        .ja-kata {
            color: #7c3aed; font-weight: 600; font-size: .78rem;
            margin: 0 2px; white-space: nowrap;
        }

        /* Rincian keterangan: label redup, isinya tegas, dipisah titik tengah.

           Dulu satu baris mentah "paket: X, kategori: private_trip, …" — dan
           yang perlu dibaca admin (isinya) tenggelam di antara nama kolom yang
           tidak pernah ia tanyakan. */
        .ja-rinci {
            display: flex; flex-wrap: wrap; align-items: baseline; gap: 3px 5px;
            margin-top: 4px; font-size: .78rem; color: var(--dsb-redup, #6b7280);
            /* min-width: 0 — WAJIB.
               Sebagai medan flex, lebar terkecilnya secara bawaan selebar
               isinya, jadi ia menolak menyempit; di ponsel satu rincian
               panjang melebarkan selnya, lalu tabelnya, lalu seluruh
               halamannya bergeser mendatar. */
            min-width: 0;
        }
        .ja-rinci-butir {
            display: inline-flex; flex-wrap: wrap; align-items: baseline; gap: 0 5px;
            min-width: 0; padding: 1px 7px 2px; border-radius: 7px; max-width: 100%;
            /* Latar setipis mungkin dari warna labelnya sendiri: cukup untuk
               memagari satu medan dari tetangganya, tidak cukup untuk
               menjadikan barisnya deretan tombol. */
            background: color-mix(in srgb, var(--c) 7%, transparent);
        }

        /* Tiap jenis medan warnanya sendiri — dan yang diwarnai LABELnya,
           bukan isinya.

           Seluruh rincian tadinya satu warna redup, jadi "paket", "Private
           Trip Premium", "kategori" dan seterusnya mengalir sebagai satu
           untaian; mata harus mengeja titik dua untuk tahu mana nama medan
           dan mana jawabannya. Isinya tetap tinta tegas supaya ia yang
           terbaca lebih dulu — warnanya penanda jenis, bukan isi. */
        .ja-rinci-label { color: var(--c, #0e7490); font-weight: 600; }
        .ja-rinci-nilai {
            color: var(--dsb-tinta, #1c1f26); font-weight: 700;
            /* Angka selebar sama: "Rp 1.716.000" dan "Rp 2.500.000" di dua
               baris bersebelahan jadi bisa dibandingkan tanpa dieja. */
            font-variant-numeric: tabular-nums;
        }

        .ja-nilai { font-size: .82rem; white-space: nowrap; }
        .ja-nilai.is-lama { color: #94a3b8; text-decoration: line-through; }
        .ja-nilai.is-baru { font-weight: 700; color: var(--dsb-tinta, #1c1f26); }

        .ja-kode {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .78rem;
            background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
            border-radius: 8px; padding: 2px 8px; white-space: nowrap;
        }

        /* Pemisah hari: satu baris tipis, bukan kartu — ia penanda, bukan isi. */
        .ja-hari td {
            background: #f8fafc; color: var(--dsb-redup, #6b7280);
            font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            padding: 7px 14px !important; border-bottom: 1px solid var(--dsb-tepi, #e9edf3);
        }

        .ja-ubah { display: block; min-width: 0; }

        /* Aksi: ikon berwarna + namanya, sejajar di tengah. */
        .ja-aksi { display: inline-flex; align-items: center; gap: 9px; }
        .ja-aksi .dsb-ikon { flex-shrink: 0; }

        .ja-admin-teks { display: flex; flex-direction: column; line-height: 1.3; }
        .ja-ip { font-size: .72rem; }

        .ja-kode.is-tautan { display: inline-flex; align-items: center; gap: 5px; text-decoration: none; color: #4338ca; border-color: #c7d2fe; }
        .ja-kode.is-tautan:hover { background: #eef2ff; }
        .ja-kode.is-tautan i { font-size: .7rem; }

        .ja-halaman {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
            gap: 10px; padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9;
        }
        .ja-halaman > * { margin-bottom: 0 !important; }

        .ja-baris {
            display: inline-flex; align-items: center; gap: 7px; margin: 0;
            color: var(--dsb-redup, #6b7280); font-size: .8rem; white-space: nowrap;
        }
        .ja-baris .dsb-isian { width: auto; min-width: 72px; padding-top: 6px; padding-bottom: 6px; }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu bertumpuk,
           aturan yang sama dengan daftar lemon lain. */
        @media (max-width: 767.98px) {
            .ja-tabel { min-width: 0; }
            .ja-tabel thead { display: none; }
            .ja-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
            .ja-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0;
            }
            .ja-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            .ja-tabel tbody td > :not(:first-child) { margin-left: 0; }

            /* Isi tiap sel harus boleh menyempit. Tanpa ini medan flex di
               dalamnya (nama aksi, rincian) mempertahankan lebar isinya dan
               mendorong kartunya keluar layar. */
            .ja-tabel tbody td > * { min-width: 0; }

            /* Kolom perubahan: isinya menumpuk dan rata kanan, menyamai sel
               lain yang isinya juga berdiri di kanan labelnya. */
            .ja-ubah { flex: 1 1 auto; text-align: right; }
            .ja-ubah .ja-aksi, .ja-ubah .ja-rinci { justify-content: flex-end; }

            /* Pemisah hari tidak ikut jadi kartu berlabel — ia tetap satu
               baris penanda di atas kelompoknya. */
            .ja-tabel tbody tr.ja-hari { display: block; padding: 0; }
            .ja-tabel tbody tr.ja-hari td { display: block; }
            .ja-tabel tbody tr.ja-hari td::before { content: none; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
