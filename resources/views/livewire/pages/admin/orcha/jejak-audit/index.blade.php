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

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="ja-saring">
                            {{-- Kotak carinya partial BERSAMA seluruh Orcha, bukan buatan
                                 halaman ini: bentuk dan perilakunya (termasuk tombol
                                 pengosong) harus satu di semua layar. Yang disesuaikan di
                                 sini hanya rupanya, lewat .ja-saring di bawah. --}}
                            <div class="dsb-medan ja-medan-cari">
                                <label class="dsb-label">Cari</label>
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Kode pesanan, nama admin, atau isi perubahan…',
                                ])
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ja-admin">Admin</label>
                                <select id="ja-admin" class="dsb-isian" wire:model.live="filterAdmin">
                                    <option value="">Semua admin</option>
                                    @foreach ($pilihanAdmin as $nama)
                                        <option value="{{ $nama }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- "Siapa saja yang pernah menyentuh pembayaran?" dijawab di
                                 sini, bukan dengan menebak kata di kotak cari. --}}
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ja-aksi">Jenis perubahan</label>
                                <select id="ja-aksi" class="dsb-isian" wire:model.live="filterAksi">
                                    <option value="">Semua jenis</option>
                                    @foreach ($pilihanAksi as $aksi)
                                        <option value="{{ $aksi }}">{{ $aksi }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Rentang tanggal, bukan satu tanggal.

                                 Pertanyaan audit hampir selalu berbentuk rentang — "pekan
                                 lalu", "sejak pengembaliannya diajukan" — dan satu tanggal
                                 memaksa menelusuri hari demi hari. --}}
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ja-dari">Dari</label>
                                <input id="ja-dari" type="date" class="dsb-isian" wire:model.live="dari">
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ja-sampai">Sampai</label>
                                <input id="ja-sampai" type="date" class="dsb-isian" wire:model.live="sampai">
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ja-ip">Alamat IP</label>
                                <input id="ja-ip" type="text" class="dsb-isian" placeholder="mis. 103.10."
                                    wire:model.live.debounce.500ms="filterIp">
                            </div>
                        </div>

                        {{-- Pintasan rentang: pertanyaan audit hampir selalu "pekan lalu",
                             bukan dua tanggal yang diketik satu per satu. Yang terisi tetap
                             kedua medan tanggalnya, jadi pilihannya kelihatan & bisa diubah. --}}
                        <div class="ja-cepat">
                            <span class="dsb-label">Rentang cepat</span>
                            <div class="ja-cepat-deret">
                                @foreach ($this::RENTANG_CEPAT as $kunci => [$label, $mundur])
                                    @php
                                        $aktif = $dari === now()->subDays($mundur)->toDateString()
                                            && $sampai === now()->toDateString();
                                    @endphp
                                    <button type="button" wire:click="rentangCepat('{{ $kunci }}')"
                                        wire:loading.attr="disabled" wire:target="rentangCepat"
                                        class="dsb-chip ja-chip-tombol {{ $aktif ? 'is-aktif' : '' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="ja-saring-kaki">
                            <span class="dsb-kartu-sub">
                                <i class="bi bi-funnel"></i>
                                @if ($adaSaringan)
                                    {{ $jumlahSaringan }} saringan aktif — daftar & unduhan mengikutinya
                                @else
                                    Belum ada saringan; unduhan memuat seluruh catatan
                                @endif
                            </span>

                            <span class="ja-saring-aksi">
                                <span class="dsb-medan ja-medan-baris">
                                    <label class="dsb-label" for="ja-baris">Baris</label>
                                    <select id="ja-baris" class="dsb-isian" wire:model.live="baris">
                                        @foreach ($this::PILIHAN_BARIS as $n)
                                            <option value="{{ $n }}">{{ $n }}</option>
                                        @endforeach
                                    </select>
                                </span>

                                {{-- Yang diunduh SELURUH hasil saringan, bukan halaman yang
                                     terlihat: audit yang dilampirkan ke laporan tidak boleh
                                     terpotong di baris terakhir tanpa ada yang menyadarinya. --}}
                                <button type="button" wire:click="unduhCsv" wire:loading.attr="disabled"
                                    wire:target="unduhCsv" class="dsb-tombol is-lembut" style="--ikon: #16a34a">
                                    <i class="bi bi-filetype-csv" wire:loading.remove wire:target="unduhCsv"></i>
                                    <span class="dsb-putar" wire:loading wire:target="unduhCsv"></span>
                                    <span>Unduh CSV</span>
                                </button>

                                @if ($adaSaringan)
                                    <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut">
                                        <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
                                    </button>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat"
                    wire:target="cari,filterAdmin,dari,sampai,bersihkanSaringan,bersihkanCari,keHalaman">
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
                                                @php
                                                    /*
                                                     | Ringkasan dari Orcha hampir selalu diawali kalimat
                                                     | yang SAMA dengan aksinya, lalu mengulang perpindahan
                                                     | status di dalam kurung — sehingga satu baris terbaca
                                                     | dua kali ("ubah status pembayaran" lalu "ubah status
                                                     | pembayaran (menunggu → diterima)"). Yang disisakan
                                                     | hanya keterangan yang benar-benar baru, mis. nominal.
                                                     */
                                                    $ringkas = trim((string) ($baris['ringkasan'] ?? ''));
                                                    $aksi = trim((string) $baris['aksi']);

                                                    if ($aksi !== '' && \Illuminate\Support\Str::startsWith(mb_strtolower($ringkas), mb_strtolower($aksi))) {
                                                        $ringkas = trim(mb_substr($ringkas, mb_strlen($aksi)));
                                                        $ringkas = trim(preg_replace('/^\([^)]*\)/u', '', $ringkas));
                                                        $ringkas = trim($ringkas, "—- \t");
                                                    }
                                                @endphp

                                                <span class="ja-aksi">
                                                    <span class="dsb-ikon is-kecil" style="--c: {{ $warnaAksi }}"><i class="bi {{ $ikonAksi }}"></i></span>
                                                    <span class="dsb-tabel-judul">{{ $baris['aksi'] }}</span>
                                                </span>

                                                {{-- Perpindahan status ditampilkan sebagai "dari → ke",
                                                     bukan hanya nilai akhirnya. Nilai akhir saja tidak
                                                     menjawab pertanyaan yang sebenarnya diajukan:
                                                     berubah DARI apa. --}}
                                                @if ($baris['sebelum'] !== null || $baris['sesudah'] !== null)
                                                    {{-- Tanpa nilai awal, "— → dp_masuk" terbaca seperti
                                                         ada yang hilang. Yang benar: memang belum punya
                                                         keadaan sebelumnya, jadi cukup "jadi X". --}}
                                                    <span class="ja-pindah">
                                                        @if ($baris['sebelum'])
                                                            <span class="ja-pindah-lama">{{ $baris['sebelum'] }}</span>
                                                            <i class="bi bi-arrow-right"></i>
                                                        @else
                                                            <span class="ja-pindah-awal">jadi</span>
                                                        @endif
                                                        <span class="ja-pindah-baru">{{ $baris['sesudah'] ?: '—' }}</span>
                                                    </span>
                                                @endif

                                                @if ($ringkas !== '')
                                                    <span class="dsb-tabel-meta">{{ $ringkas }}</span>
                                                @endif
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
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        /* Saringan: satu baris di layar lebar, melipat sendiri saat sempit.
           Kotak cari dua kali lebih lebar — isinya kalimat, bukan pilihan. */
        .ja-saring { display: flex; flex-wrap: wrap; gap: 12px; }
        .ja-saring > .dsb-medan { flex: 1 1 180px; min-width: 0; }
        .ja-saring > .ja-medan-cari { flex: 2 1 320px; }

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

        .ja-saring-kaki {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
            gap: 12px; margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9;
        }
        .ja-saring-kaki .dsb-kartu-sub { display: inline-flex; align-items: center; gap: 7px; }

        /* Nama admin: inisial berwarna + nama, sejajar di tengah. */
        .ja-admin { display: inline-flex; align-items: center; gap: 9px; font-weight: 600; color: var(--dsb-tinta, #1c1f26); }
        .ja-admin .dsb-avatar { width: 30px; height: 30px; font-size: .78rem; }

        /* "dari → ke" dibaca sebagai satu kalimat; yang lama diredupkan dan
           dicoret supaya arah perubahannya terbaca tanpa mengeja. */
        .ja-pindah { display: inline-flex; align-items: center; gap: 6px; margin-top: 3px; font-size: .78rem; flex-wrap: wrap; }
        .ja-pindah-lama { color: #94a3b8; text-decoration: line-through; }
        .ja-pindah i { color: #cbd5e1; font-size: .7rem; }
        .ja-pindah-baru { font-weight: 700; color: var(--dsb-tinta, #1c1f26); }
        .ja-pindah-awal { color: #94a3b8; }

        .ja-kode {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .78rem;
            background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
            border-radius: 8px; padding: 2px 8px; white-space: nowrap;
        }

        /* Pintasan rentang. Yang terpilih berwarna penuh supaya terbaca
           sebagai keadaan, bukan sekadar tombol yang barusan ditekan. */
        .ja-cepat { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
        .ja-cepat .dsb-label { margin: 0; }
        .ja-cepat-deret { display: flex; gap: 7px; flex-wrap: wrap; }
        .ja-chip-tombol { cursor: pointer; border: 1px solid var(--dsb-tepi, #e9edf3); background: #fff; }
        .ja-chip-tombol:hover { border-color: #c7d2fe; color: #4338ca; }
        .ja-chip-tombol.is-aktif { background: #eef2ff; border-color: #c7d2fe; color: #3730a3; font-weight: 700; }

        .ja-saring-aksi { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
        .ja-medan-baris { min-width: 0; }
        .ja-medan-baris .dsb-isian { width: auto; min-width: 84px; }

        /* Pemisah hari: satu baris tipis, bukan kartu — ia penanda, bukan isi. */
        .ja-hari td {
            background: #f8fafc; color: var(--dsb-redup, #6b7280);
            font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            padding: 7px 14px !important; border-bottom: 1px solid var(--dsb-tepi, #e9edf3);
        }

        /* Aksi: ikon berwarna + namanya, sejajar di tengah. */
        .ja-aksi { display: inline-flex; align-items: center; gap: 9px; }
        .ja-aksi .dsb-ikon { flex-shrink: 0; }

        .ja-admin-teks { display: flex; flex-direction: column; line-height: 1.3; }
        .ja-ip { font-size: .72rem; }

        .ja-kode.is-tautan { display: inline-flex; align-items: center; gap: 5px; text-decoration: none; color: #4338ca; border-color: #c7d2fe; }
        .ja-kode.is-tautan:hover { background: #eef2ff; }
        .ja-kode.is-tautan i { font-size: .7rem; }

        .ja-halaman { padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
        .ja-halaman > * { margin-bottom: 0 !important; }

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

            /* Pemisah hari tidak ikut jadi kartu berlabel — ia tetap satu
               baris penanda di atas kelompoknya. */
            .ja-tabel tbody tr.ja-hari { display: block; padding: 0; }
            .ja-tabel tbody tr.ja-hari td { display: block; }
            .ja-tabel tbody tr.ja-hari td::before { content: none; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
