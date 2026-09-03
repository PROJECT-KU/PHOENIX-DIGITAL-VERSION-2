@section('title')
Daftar Peserta || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @php
            $tercatat = (int) ($pendaftaran['jumlah_peserta'] ?? 0);
            $terisi = collect($barisPeserta)->filter(fn ($b) => trim($b['nama'] ?? '') !== '')->count();
        @endphp

        {{-- ============ KEPALA ============ --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <a href="{{ route('admin.orcha.pendaftaran.detail', $pendaftaranId) }}" wire:navigate
                    class="orcha-tautan-balik mb-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke detail pendaftaran
                </a>
                <h1 class="gradient-text fw-bold mb-1" style="font-size:1.6rem">Daftar Peserta</h1>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="orcha-kode">{{ $pendaftaran['kode'] ?? '—' }}</span>
                    <span class="text-muted" style="font-size:.85rem">
                        {{ $pendaftaran['nama'] ?? '' }} ·
                        {{ $pendaftaran['paket']['nama'] ?? 'Paket menyusul' }}
                    </span>
                </div>

            </div>
        </div>

        {{-- ============ ISI CEPAT ============
             Dua cara pengisian berbagi satu kartu melebar, bukan dua kartu
             menumpuk di kolom sempit: keduanya dipakai sekali di awal lalu
             ditinggalkan, sedangkan daftar namanya yang dikerjakan lama —
             dan daftar itulah yang pantas mendapat seluruh lebar halaman. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-4">
                    <div class="col-12 col-lg-7">
                        <h2 class="fw-bold mb-1 orcha-judul-ikon" style="font-size:1rem">
                            <i class="bi bi-clipboard-check text-primary"></i> Tempel dari Excel atau WhatsApp
                        </h2>
                        <p class="text-muted small mb-2">
                            Satu nama per baris. Kolom kedua dianggap titik jemput.
                            Penomoran daftar WhatsApp dan baris judul tidak ikut terbaca.
                            <br>
                            Untuk <strong>mengganti peserta</strong>, tulis
                            <code>Haha &gt; Wiam</code> — nama lama, panah, nama pengganti.
                            Titik jemput penggantinya ditulis di kolom kedua seperti biasa:
                            <code>Haha &gt; Wiam, Klaten</code>. Dikosongkan berarti penggantinya
                            naik di titik yang sama.
                        </p>

                        <textarea class="form-control orcha-tempel" rows="4" wire:model="tempelan"
                            placeholder="Budi Santoso&#10;Siti Rahmawati, Stasiun Tugu&#10;Haha &gt; Wiam, Klaten"></textarea>

                        <button type="button" class="orcha-btn orcha-btn-lembut mt-2" wire:click="tempel">
                            <i class="bi bi-plus-circle"></i> Ambil nama
                        </button>
                    </div>

                    <div class="col-12 col-lg-5">
                        <h2 class="fw-bold mb-1 orcha-judul-ikon" style="font-size:1rem">
                            <i class="bi bi-file-earmark-arrow-up text-primary"></i> Unggah berkas panitia
                        </h2>
                        <p class="text-muted small mb-2">
                            Excel atau CSV, maksimal 2 MB. Baris judul diabaikan.
                            Kolom ketiga <strong>Menggantikan</strong> boleh diisi nama lama
                            bila baris itu menggantikan peserta — titik jemput penggantinya
                            tetap di kolom kedua, dan yang dikosongkan mewarisi titik lamanya.
                        </p>

                        <input type="file" class="form-control" wire:model="berkasPeserta"
                            accept=".xlsx,.xls,.csv,.txt">

                        <div wire:loading wire:target="berkasPeserta" class="text-muted mt-2"
                            style="font-size:.8rem">Membaca berkas…</div>
                        @error('berkasPeserta')
                            <div class="text-danger mt-2" style="font-size:.8rem">{{ $message }}</div>
                        @enderror

                        {{-- Templat dibuat untuk pendaftaran ini: barisnya sebanyak
                             peserta yang tercatat, dan kolom titik jemputnya sudah
                             berisi pilihan milik paketnya. Panitia memilih alih-alih
                             mengetik, sehingga ejaan yang kembali ke sistem sama dengan
                             yang sudah ditentukan. --}}
                        <a href="{{ route('admin.orcha.pendaftaran.peserta.templat', $pendaftaranId) }}"
                            class="orcha-btn orcha-btn-lembut orcha-btn-kecil mt-2">
                            <i class="bi bi-download"></i> Unduh templat
                        </a>

                        <div class="text-muted mt-3" style="font-size:.78rem">
                            <i class="bi bi-info-circle"></i>
                            Nama biasa <strong>menambah</strong> daftar di bawah; nama yang
                            menyatakan menggantikan seseorang <strong>menimpa</strong> barisnya.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ DAFTAR NAMA ============ --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                    <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem">
                        <i class="bi bi-people-fill text-primary"></i> Nama Peserta
                    </h2>
                    <div class="d-flex flex-wrap gap-2">
                        {{-- Sakelar pembagian bus & kamar.

                             Disembunyikan sampai diminta: untuk open trip berlima,
                             dua kolom yang selalu kosong hanya menyempitkan kolom
                             nama. Menyala sendiri bila pembagiannya sudah ada —
                             yang membuka rombongan yang sudah dibagi tidak boleh
                             melihat pembagiannya lenyap. --}}
                        <button type="button"
                            class="orcha-btn orcha-btn-kecil {{ $bagiKelompok ? 'orcha-btn-utama' : 'orcha-btn-lembut' }}"
                            wire:click="$toggle('bagiKelompok')"
                            title="Tampilkan kolom pembagian bus dan kamar">
                            <i class="bi bi-bus-front"></i>
                            {{ $bagiKelompok ? 'Sedang membagi bus & kamar' : 'Bagi bus & kamar' }}
                        </button>

                        <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                            wire:click="tambahBaris">
                            <i class="bi bi-plus-lg"></i> Tambah baris
                        </button>
                    </div>
                </div>

                {{-- Peringatan yang harus dibaca SEBELUM mengetik: kaitan riwayat
                     kesehatan berdasarkan nama, jadi mengubah ejaan nama memutus
                     kaitannya. --}}
                {{-- Penggantian peserta dikerjakan DI SINI, dan itu tidak jelas dengan
                     sendirinya: admin hanya mengetik nama lain lalu menyimpan. Tanpa
                     kalimat ini, yang mencari tombol "ganti peserta" tidak akan
                     menemukan apa pun. --}}
                <div class="orcha-tenggat mb-3" style="font-size:.84rem">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>
                        <strong>Mengganti peserta?</strong> Tekan tombol
                        <i class="bi bi-arrow-left-right"></i> di baris orang yang berhalangan —
                        nama lamanya ditahan, dan nama pengganti diisi di kotak barunya sendiri.
                        Perubahannya dicatat beserta nama lama, dan surat pernyataannya bisa
                        diunduh dari halaman detail. Tidak ada biaya sepanjang jumlah pesertanya
                        tetap.
                    </span>
                </div>

                <p class="text-muted small mb-3">
                    Peserta yang riwayat kesehatannya sudah masuk ditandai
                    <i class="bi bi-heart-pulse-fill orcha-tanda-sehat"></i>.
                    Mengubah ejaan namanya memutus kaitan dengan riwayat itu — riwayatnya tidak hilang,
                    tetapi tidak lagi dikenali sebagai miliknya.
                </p>

                {{-- Judul kolom ditulis sekali di atas, bukan mengandalkan teks
                     bayangan di dalam isian: teks bayangan hilang begitu isiannya
                     terisi, dan pada daftar dua puluh baris tidak ada lagi yang
                     memberi tahu kolom mana yang mana. --}}
                <div class="row g-2 d-none d-md-flex px-2 mb-1">
                    <div class="col-auto" style="width:2.9rem"></div>
                    <div class="col-auto" style="width:1.7rem"></div>
                    <div class="col"><span class="orcha-label-kecil">Nama peserta</span></div>
                    <div class="col-4"><span class="orcha-label-kecil">Titik jemput</span></div>
                    <div class="col-auto" style="width:5.4rem"></div>
                </div>

                @foreach ($barisPeserta as $urutan => $baris)
                    @php
                        $namaBaris = mb_strtolower(trim($baris['nama'] ?? ''));
                        $sudah = $namaBaris !== '' && in_array($namaBaris, $sudahIsi, true);
                    @endphp
                    <div class="row g-2 align-items-center orcha-baris-peserta"
                        wire:key="baris-peserta-{{ $urutan }}">
                        <div class="col-auto" style="width:2.9rem">
                            <span class="orcha-nomor-peserta">{{ $urutan + 1 }}</span>
                        </div>

                        {{-- value ditulis eksplisit: wire:model sendiri tidak menggambar
                             nilainya di HTML dari server, dan baris hasil tempelan harus
                             sudah terlihat sebelum admin menekan simpan — kalau tidak, ia
                             menyimpan sesuatu yang belum sempat diperiksanya. --}}
                        {{-- Penanda riwayat kesehatan: satu ikon hati di kolom sempit,
                             bukan lencana bertulisan.

                             Lencana bertulisan menuntut kolom selebar 6,6rem yang pada
                             baris tanpa riwayat berubah jadi lubang di tengah daftar —
                             rapi barisnya, tapi bolong tampilannya. Kolomnya tetap ada
                             meski kosong supaya tiap baris setinggi yang lain. --}}
                        <div class="col-auto text-center" style="width:1.7rem">
                            @if ($sudah)
                                <i class="bi bi-heart-pulse-fill orcha-tanda-sehat"
                                    title="Riwayat kesehatannya sudah masuk. Mengubah ejaan namanya memutus kaitan itu."></i>
                            @endif
                        </div>

                        <div class="col">
                            @if (filled($baris['gantikan'] ?? null))
                                {{-- Nama lama ikut MASUK ke dalam kotaknya, mendahului
                                     panah: satu baris yang terbaca utuh sebagai
                                     "suparjiman → hafid".

                                     Ditaruh di baris terpisah di atas kotak, matanya harus
                                     melompat dua kali untuk merangkai satu kalimat — dan
                                     pada daftar dua puluh orang, lompatan itu berulang dua
                                     puluh kali. --}}
                                <div class="orcha-ganti-gabung">
                                    <span class="orcha-ganti-lama">{{ $baris['gantikan'] }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="barisPeserta.{{ $urutan }}.nama"
                                        value="{{ $baris['nama'] ?? '' }}"
                                        placeholder="nama pengganti" autofocus>
                                </div>
                            @else
                                <input type="text" class="form-control form-control-sm"
                                    wire:model="barisPeserta.{{ $urutan }}.nama"
                                    value="{{ $baris['nama'] ?? '' }}" placeholder="Nama peserta">
                            @endif
                        </div>

                        <div class="col-4">
                            {{-- Dipilih, bukan diketik.

                                 Mengetik bebas menghasilkan ejaan berbeda-beda untuk tempat
                                 yang sama — "Klaten" dan "klaten" — dan manifes lalu mencetak
                                 dua kelompok bernama sama, lalu sopir berhenti dua kali di
                                 tempat yang satu. --}}
                            @php
                                /* Kotaknya tampil selama baris ini sedang diganti — tidak
                                   menunggu titiknya benar-benar berpindah.

                                   Sempat dibuat muncul hanya saat titiknya beda, dan itu
                                   justru yang membingungkan: kolom nama berubah bentuk,
                                   kolom di sebelahnya tidak, dan admin tidak punya cara
                                   tahu apakah titik jemputnya ikut terbawa ke pengganti
                                   atau terlewat sama sekali. Sekarang kedua kolom selalu
                                   berubah bersama-sama. */
                                $titikPindah = filled($baris['gantikan'] ?? null)
                                    && filled($baris['gantikan_titik'] ?? null);
                            @endphp

                            {{-- Titik lamanya ikut dicoret, sebentuk dengan nama penggantinya
                                 di kolom sebelah: satu baris, dua perubahan, dibaca dengan cara
                                 yang sama. --}}
                            @if ($titikPindah)
                                <div class="orcha-ganti-gabung">
                                    <span class="orcha-ganti-lama">{{ $baris['gantikan_titik'] }}</span>
                                    <i class="bi bi-arrow-right"></i>
                            @endif

                            @if ($pilihanTitik !== [])
                                <select class="form-select form-select-sm"
                                    wire:model="barisPeserta.{{ $urutan }}.titik_jemput">
                                    <option value="">— belum dipilih —</option>
                                    @foreach ($pilihanTitik as $titik)
                                        {{-- Dicocokkan tanpa memedulikan besar-kecil huruf:
                                             data lama menyimpan "klaten" sedangkan daftar
                                             pilihannya "Klaten", dan perbandingan persis huruf
                                             membuat titik yang sudah terisi tampil sebagai
                                             "belum dipilih" — lalu ikut hilang saat disimpan. --}}
                                        <option value="{{ $titik }}"
                                            @selected(mb_strtolower(trim($baris['titik_jemput'] ?? '')) === mb_strtolower($titik))>
                                            {{ $titik }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                {{-- Paket ini belum menentukan titik jemput mana pun.
                                     Isian bebas dibiarkan supaya admin tidak terkunci
                                     tanpa satu pun pilihan. --}}
                                <input type="text" class="form-control form-control-sm"
                                    wire:model="barisPeserta.{{ $urutan }}.titik_jemput"
                                    value="{{ $baris['titik_jemput'] ?? '' }}"
                                    placeholder="Titik jemput belum ditentukan paket">
                            @endif

                            @if ($titikPindah)
                                </div>
                            @endif
                        </div>

                        {{-- Bus dan kamar, hanya saat pembagian dinyalakan.

                             Untuk open trip berlima, dua kolom yang selalu kosong
                             hanya menyempitkan kolom nama dan menambah benda yang
                             harus diabaikan mata. Untuk study tour 120 orang,
                             inilah yang dikerjakan seminggu sebelum berangkat. --}}
                        @if ($bagiKelompok)
                            <div class="col-12 col-md-2">
                                <input type="text" class="form-control form-control-sm"
                                    wire:model="barisPeserta.{{ $urutan }}.bus"
                                    value="{{ $baris['bus'] ?? '' }}"
                                    maxlength="60" placeholder="Bus">
                            </div>

                            <div class="col-12 col-md-2">
                                <input type="text" class="form-control form-control-sm"
                                    wire:model="barisPeserta.{{ $urutan }}.kamar"
                                    value="{{ $baris['kamar'] ?? '' }}"
                                    maxlength="60" placeholder="Kamar">
                            </div>
                        @endif

                        <div class="col-auto text-end" style="width:5.4rem">
                            @if (filled($baris['gantikan'] ?? null))
                                <button type="button" class="orcha-hapus-baris orcha-tombol-batal"
                                    wire:click="batalGanti({{ $urutan }})"
                                    title="Batal mengganti, kembalikan nama lamanya">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            @elseif (filled($baris['nama'] ?? null))
                                <button type="button" class="orcha-hapus-baris orcha-tombol-ganti"
                                    wire:click="mulaiGanti({{ $urutan }})"
                                    title="Peserta ini berhalangan, digantikan orang lain">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            @endif

                            <button type="button" class="orcha-hapus-baris"
                                wire:click="hapusBaris({{ $urutan }})" title="Hapus baris ini">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                @endforeach

                {{-- Menempel di dasar kartu saat daftarnya panjang: pada rombongan
                     empat puluh orang, tombol simpan yang hanya ada di ujung bawah
                     berarti menggulung seluruh daftar untuk menyimpan satu koreksi. --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 orcha-aksi-lengket">
                    <span class="text-muted" style="font-size:.8rem">
                        <strong>{{ $terisi }}</strong> nama terisi dari
                        <strong>{{ $tercatat }}</strong> peserta yang tercatat
                    </span>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.orcha.pendaftaran.detail', $pendaftaranId) }}"
                            wire:navigate class="orcha-btn orcha-btn-lembut">Batal</a>
                        <button type="button" class="orcha-btn orcha-btn-utama" wire:click="simpan"
                            wire:loading.attr="disabled">
                            <i class="bi bi-check-lg"></i> Simpan daftar peserta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
