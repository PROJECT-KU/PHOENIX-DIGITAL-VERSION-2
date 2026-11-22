@section('title')
{{ $surat->nomor_agenda }} || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.surat.partials.surat-gaya')

    @php
        // Semua perbandingan dirakit di sini, bukan di kondisi direktif blok.
        $masuk = $surat->arah === 'masuk';
        $warnaArah = $masuk ? '#0ea5c6' : '#17a974';
        $ikonArah = $masuk ? 'bi-envelope-arrow-down' : 'bi-envelope-arrow-up';
        $warnaSifat = ['biasa' => '#64748b', 'segera' => '#d97706', 'sangat_segera' => '#e11d48', 'rahasia' => '#7c3aed'];
        $warnaStatus = ['baru' => '#d97706', 'diproses' => '#0284c7', 'selesai' => '#16a34a', 'diarsipkan' => '#64748b'];
        $warnaDis = ['menunggu' => '#d97706', 'dikerjakan' => '#0284c7', 'selesai' => '#16a34a'];

        $lampiran = $surat->lampiran;
        $disposisi = $surat->disposisi->sortByDesc('id');
        $adaLampiran = $lampiran->isNotEmpty();
        $adaDisposisi = $disposisi->isNotEmpty();
        $adaRingkasan = filled($surat->ringkasan);
        $bolehMusnah = $surat->sudah_boleh_musnah;
        $adaBerkasBaru = count($berkasBaru) !== 0;
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi {{ $ikonArah }}"></i> {{ $surat->arah_label }}</span>
                <h1 class="dsb-judul">{{ $surat->perihal }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <b class="sr-agenda">{{ $surat->nomor_agenda }}</b>
                        <span class="dsb-pisah">•</span>{{ $surat->pihak_label }}: {{ $surat->pihak }}
                        <span class="dsb-pisah">•</span>{{ $surat->tanggal_surat_teks }}
                    </span>
                    <span class="d-block">
                        <span class="dsb-lencana" style="--c: {{ $warnaStatus[$surat->status] ?? '#64748b' }}">{{ $surat->status_label }}</span>
                        <span class="dsb-lencana" style="--c: {{ $warnaSifat[$surat->sifat] ?? '#64748b' }}">{{ $surat->sifat_label }}</span>
                        @if ($bolehMusnah)
                            <span class="dsb-lencana" style="--c: #7c3aed">Masa simpan habis</span>
                        @endif
                    </span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.surat.index') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
                <a href="{{ route('admin.surat.lembar-disposisi', $surat) }}" target="_blank" rel="noopener"
                    class="dsb-tombol is-lembut" style="--ikon: #b91c1c" title="Cetak untuk ditempel di surat fisiknya">
                    <i class="bi bi-printer"></i><span>Lembar Disposisi</span>
                </a>
                @if ($bolehCatat)
                    {{-- Jalan terpendek dari "surat ini perlu dibalas" ke suratnya:
                         arah, tujuan, dan klasifikasinya sudah terisi. --}}
                    <a href="{{ route('admin.surat.create', ['balas' => $surat->id]) }}" wire:navigate
                        class="dsb-tombol is-lembut" style="--ikon: #17a974">
                        <i class="bi bi-reply"></i><span>Catat Balasan</span>
                    </a>
                @endif
                @if ($bolehUbah)
                    <a href="{{ route('admin.surat.edit', $surat) }}" wire:navigate class="dsb-tombol is-utama">
                        <i class="bi bi-pencil"></i><span>Ubah</span>
                    </a>
                @endif
                @if ($bolehHapus)
                    <button type="button" class="dsb-tombol is-lembut pcek-konfirmasi" style="--ikon: #e11d48"
                        data-action="hapusSurat"
                        data-title="Hapus surat {{ $surat->nomor_agenda }}?"
                        data-text="Surat dikeluarkan dari daftar. Nomor agendanya tetap dipegang supaya tidak dipakai dua kali, dan pindaiannya tidak dibuang."
                        data-confirm="Ya, hapus" data-icon="warning">
                        <i class="bi bi-trash"></i><span>Hapus</span>
                    </button>
                @endif
            </div>
        </header>

        {{-- ================== FAKTA CEPAT ==================
             Empat hal yang paling sering ditanya tentang sebuah surat,
             dijawab sebelum orang perlu membaca kartu mana pun. Bentuknya
             sama dengan deret angka di Pesanan Toko: yang penting dulu,
             rinciannya menyusul. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                @foreach ([
                    ['Status', $surat->status_label, 'bi-flag-fill', $warnaStatus[$surat->status] ?? '#64748b', 'Tahap pengerjaannya'],
                    ['Sifat', $surat->sifat_label, 'bi-bookmark-star-fill', $warnaSifat[$surat->sifat] ?? '#64748b', 'Seberapa perlu didahulukan'],
                    ['Tanggal surat', $surat->tanggal_surat_teks, 'bi-calendar3', '#0284c7', 'Yang tertulis di suratnya'],
                    ['Ditangani oleh', $surat->penanggungJawab?->name ?: 'Belum ditunjuk', 'bi-person-badge', '#7c3aed', 'Yang memegang urusannya'],
                ] as [$labelFakta, $isiFakta, $ikonFakta, $warnaFakta, $ketFakta])
                    <div class="dsb-kartu k-3">
                        <div class="dsb-kartu-isi sr-fakta">
                            <span class="dsb-ikon is-kecil" style="--c: {{ $warnaFakta }}"><i class="bi {{ $ikonFakta }}"></i></span>
                            <span class="sr-fakta-teks">
                                <span class="dsb-kartu-sub">{{ $labelFakta }}</span>
                                <b>{{ $isiFakta }}</b>
                                <span class="dsb-kartu-sub">{{ $ketFakta }}</span>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- sr-rak-puncak: tiap kartu berhenti di akhir isinya. Diregangkan
             sama tinggi, kartu rincian menyisakan lubang putih sebesar selisih
             dengan kartu di sebelahnya — dan selisih itu berubah-ubah menurut
             banyaknya pindaian, jadi tidak ada tinggi yang "pas" untuk keduanya. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak sr-rak-puncak">
                {{-- ---------- RINCIAN ---------- --}}
                <div class="dsb-kartu k-7">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: {{ $warnaArah }}"><i class="bi bi-file-text"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Rincian surat</h3>
                                <span class="dsb-kartu-sub">Seperti tercatat di buku agenda</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        {{-- Bentuk barisnya mengikuti kartu rincian Pesanan Toko:
                             nama medannya di kiri, isinya di kanan, dipisah garis
                             putus yang halus. Medan yang BELUM terisi sengaja
                             ditulis abu-abu supaya yang sudah terisi menonjol. --}}
                        @php
                            // Tembusan disimpan sebagai satu teks dipisah titik koma
                            // (itu yang diketik di borang). Di sini dipecah lagi agar
                            // tiap nama terbaca sebagai satu keping, bukan satu kalimat
                            // panjang yang patah di tengah nama orang.
                            $daftarTembusan = array_values(array_filter(array_map(
                                'trim',
                                preg_split('/[;\n]+/', (string) $surat->tembusan)
                            ), fn ($satu) => $satu !== ''));
                        @endphp
                        <dl class="sr-rincian">
                            <div>
                                <dt>Nomor agenda</dt>
                                <dd class="sr-kode">{{ $surat->nomor_agenda }}</dd>
                            </div>
                            <div>
                                <dt>Nomor surat</dt>
                                <dd class="{{ $surat->nomor_surat ? 'sr-kode' : 'is-kosong' }}">{{ $surat->nomor_surat ?: 'Tidak dicatat' }}</dd>
                            </div>
                            <div>
                                <dt>{{ $surat->pihak_label }}</dt>
                                <dd>{{ $surat->pihak }}</dd>
                            </div>
                            <div>
                                <dt>Tanggal surat</dt>
                                <dd>{{ $surat->tanggal_surat_teks }}</dd>
                            </div>
                            <div>
                                <dt>Tanggal {{ $masuk ? 'diterima' : 'dikirim' }}</dt>
                                <dd class="{{ $surat->tanggal_catat ? '' : 'is-kosong' }}">{{ $surat->tanggal_catat ? $surat->tanggal_catat_teks : 'Belum dicatat' }}</dd>
                            </div>
                            <div>
                                <dt>Klasifikasi</dt>
                                <dd class="{{ $surat->klasifikasi ? '' : 'is-kosong' }}">{{ $surat->klasifikasi?->label ?: 'Belum diklasifikasi' }}</dd>
                            </div>
                            <div>
                                <dt>Masa simpan</dt>
                                <dd class="{{ $surat->klasifikasi?->retensi_teks ? '' : 'is-kosong' }}">{{ $surat->klasifikasi?->retensi_teks ?: 'Belum ditentukan' }}</dd>
                            </div>
                            <div>
                                <dt>Boleh dimusnahkan</dt>
                                <dd class="{{ $surat->musnah_pada ? '' : 'is-kosong' }}">{{ $surat->musnah_pada_teks }}</dd>
                            </div>
                            <div>
                                <dt>Tempat berkas</dt>
                                <dd class="{{ $surat->lokasi_fisik ? '' : 'is-kosong' }}">{{ $surat->lokasi_fisik ?: 'Belum dicatat' }}</dd>
                            </div>
                            <div>
                                <dt>Tembusan</dt>
                                @if ($daftarTembusan)
                                    <dd>
                                        <span class="sr-rincian-keping">
                                            @foreach ($daftarTembusan as $satuTembusan)
                                                <span class="sr-keping"><span>{{ $satuTembusan }}</span></span>
                                            @endforeach
                                        </span>
                                    </dd>
                                @else
                                    <dd class="is-kosong">Tidak ada</dd>
                                @endif
                            </div>
                        </dl>

                        @if ($surat->induk)
                            <div class="sr-kait">
                                <span class="dsb-label">Menanggapi surat</span>
                                <a href="{{ route('admin.surat.detail', $surat->induk) }}" wire:navigate class="sr-kait-baris">
                                    <i class="bi bi-arrow-return-left"></i>
                                    <span><b>{{ $surat->induk->nomor_agenda }}</b> &middot; {{ $surat->induk->perihal }}</span>
                                </a>
                            </div>
                        @endif

                        @if ($tanggapan->isNotEmpty())
                            <div class="sr-kait">
                                <span class="dsb-label">Ditanggapi oleh</span>
                                @foreach ($tanggapan as $anak)
                                    <a href="{{ route('admin.surat.detail', $anak) }}" wire:navigate class="sr-kait-baris">
                                        <i class="bi bi-arrow-return-right"></i>
                                        <span><b>{{ $anak->nomor_agenda }}</b> &middot; {{ $anak->perihal }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if ($adaRingkasan)
                            {{-- Ringkasan isi dipisahkan ke kotaknya sendiri: ia kalimat
                                 bebas, bukan medan berpasangan seperti daftar di atas,
                                 jadi kalau ikut barisan garis putus ia terbaca seperti
                                 nilai yang meluber. --}}
                            <div class="sr-ringkasan">
                                <span class="dsb-label">Ringkasan isi</span>
                                <p>{{ $surat->ringkasan }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ---------- STATUS & LAMPIRAN ---------- --}}
                <div class="dsb-kartu k-5">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-flag-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Status &amp; penanganan</h3>
                                <span class="dsb-kartu-sub">Sudah sampai mana, dan siapa yang memegangnya</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        @if ($bolehUbah)
                            <span class="dsb-label">Ubah status</span>
                            <div class="dsb-chip-deret" style="margin-bottom: .8rem;">
                                {{-- Hanya status manual. 'Dimusnahkan' hanya bisa dicapai
                                     lewat berita acara: ia menyatakan berkas kertasnya sudah
                                     tidak ada, dan itu bukan keadaan yang diberikan atau
                                     dicabut sambil lalu lewat satu tombol. --}}
                                @foreach (\App\Models\Surat\Surat::STATUS_MANUAL as $kunci)
                                    {{-- Satu blok @php saja: blok @php yang menyusul @php(...)
                                         sebaris akan ikut tertelan sampai @endphp, dan galatnya
                                         menyebut variabel yang tidak bersalah. --}}
                                    @php
                                        $label = \App\Models\Surat\Surat::STATUS[$kunci];
                                        $iniStatus = $surat->status === $kunci;
                                    @endphp
                                    <button type="button" wire:click="ubahStatus('{{ $kunci }}')"
                                        class="dsb-tombol is-mungil {{ $iniStatus ? 'is-utama' : 'is-lembut' }}"
                                        style="--ikon: {{ $warnaStatus[$kunci] ?? '#64748b' }}"
                                        @disabled($iniStatus)>
                                        <i class="bi bi-circle-fill" style="font-size:.5rem"></i><span>{{ $label }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Dua nama yang berbeda artinya, dan sering tertukar:
                             yang MENCATAT suratnya ke buku agenda, dan yang
                             MEMEGANG urusannya. --}}
                        <dl class="sr-rincian" style="margin-top: .2rem;">
                            <div>
                                <dt>Ditangani oleh</dt>
                                <dd>{{ $surat->penanggungJawab?->name ?: 'Belum ditunjuk' }}</dd>
                            </div>
                            <div>
                                <dt>Dicatat oleh</dt>
                                <dd>{{ $surat->pencatat?->name ?: 'Tidak diketahui' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- ---------- PINDAIAN ----------
                     Kartunya SENDIRI, selebar penuh.

                     Sebelumnya daftar pindaian menumpang di kartu kanan. Satu
                     surat berisi lima lembar membuat kartu itu memanjang jauh
                     melewati kartu rincian di sebelahnya, dan separuh kiri
                     layar menganga kosong sepanjang daftar itu — makin banyak
                     berkasnya, makin lebar lubangnya. --}}
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-paperclip"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pindaian surat</h3>
                                <span class="dsb-kartu-sub">Hasil pindai berkas fisiknya &mdash; tersimpan tertutup</span>
                            </div>
                        </div>
                        @if ($adaLampiran)
                            <span class="dsb-chip"><i class="bi bi-paperclip"></i>{{ $lampiran->count() }} berkas</span>
                        @endif
                    </div>
                    <div class="dsb-kartu-isi">
                        <div class="sr-pindaian-unggah">
                        @if ($bolehUbah)
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-berkas-baru">Tambah pindaian</label>
                                    <label class="sr-unggah @error('unggahan.*') is-galat @enderror">
                                        <input id="sr-berkas-baru" type="file" multiple class="sr-unggah-asli"
                                            wire:model="unggahan" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                        <span class="sr-unggah-ikon"><i class="bi bi-cloud-arrow-up"></i></span>
                                        <span class="sr-unggah-teks">
                                            <b>Pilih berkas pindaian</b>
                                            <span>PDF, foto, atau Word &middot; maksimal 20 MB per lembar</span>
                                        </span>
                                    </label>
                                    @error('unggahan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    @error('unggahan.*')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    @error('berkasBaru.*')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <div wire:loading wire:target="unggahan" class="dsb-kartu-sub">
                                        <span class="dsb-putar is-kecil"></span> Mengunggah…
                                    </div>

                                    @if ($adaBerkasBaru)
                                        <div class="sr-antrean">
                                            @foreach ($berkasBaru as $urutan => $b)
                                                <div class="sr-antrean-baris" wire:key="antrean-{{ $urutan }}-{{ $b->getFilename() }}">
                                                    <i class="bi bi-paperclip"></i>
                                                    <span class="sr-antrean-teks">
                                                        <b>{{ $b->getClientOriginalName() }}</b>
                                                        <span>{{ \App\Models\Surat\SuratLampiran::ukuranTeks($b->getSize()) }}</span>
                                                    </span>
                                                    <button type="button" class="dsb-tabel-btn" title="Keluarkan berkas ini"
                                                        wire:click="hapusAntrean({{ $urutan }})">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" wire:click="unggahBerkas" wire:target="unggahBerkas"
                                            class="dsb-tombol is-utama is-mungil" style="margin-top:.4rem">
                                            <i class="bi bi-upload"></i><span>Simpan {{ count($berkasBaru) }} berkas</span>
                                        </button>
                                    @endif

                                    <span class="dsb-kartu-sub">
                                        Boleh pilih beberapa lembar sekaligus, dan boleh memilih lagi —
                                        yang sudah dipilih tidak hilang.
                                    </span>
                                </div>
                            @endif

                        </div>

                        <div class="sr-pindaian-daftar">
                    @if ($adaLampiran)
                                    @foreach ($lampiran as $l)
                                        <div class="sr-lampiran-baris">
                                            <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi {{ $l->ikon }}"></i></span>
                                            <span class="sr-lampiran-teks">
                                                <b>{{ $l->nama }}</b>
                                                <span>{{ $l->ukuran_teks }} · {{ $l->pengunggah?->name ?: 'tidak diketahui' }}</span>
                                            </span>
                                            <a href="{{ route('admin.surat.lampiran', [$surat, $l]) }}" target="_blank" rel="noopener"
                                                class="dsb-tabel-btn" title="Buka pindaian">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                            @if ($bolehHapus)
                                                <button type="button" class="dsb-tabel-btn pcek-konfirmasi" title="Hapus pindaian"
                                                    data-action="hapusLampiran" data-arg="{{ $l->id }}"
                                                    data-title="Hapus pindaian ini?"
                                                    data-text="{{ $l->nama }} dihapus dari server dan tidak bisa dikembalikan."
                                                    data-confirm="Ya, hapus" data-icon="warning">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="dsb-kosong">
                                        <span class="dsb-kosong-ikon"><i class="bi bi-file-earmark"></i></span>
                                        <p class="dsb-kosong-judul">Belum ada pindaian</p>
                                        <p class="dsb-kosong-ket">
                                            Arsip tetap sah tanpa pindaian — tempat berkas fisiknya yang penting.
                                            Pindaian menolong kalau kertasnya sedang dipinjam orang.
                                        </p>
                                    </div>
                                @endif

                        </div>
                    </div>
                </div>

                {{-- ---------- DISPOSISI ---------- --}}
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #b91c1c"><i class="bi bi-person-check"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Disposisi</h3>
                                <span class="dsb-kartu-sub">Siapa yang mengerjakan, apa perintahnya, kapan tenggatnya</span>
                            </div>
                        </div>
                    </div>

                    @if ($bolehCatat)
                        <div class="dsb-kartu-isi">
                            <div class="sr-borang">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-dis-user">Kepada (akun lemon)</label>
                                    <select id="sr-dis-user" class="dsb-isian" wire:model="disKepadaUser">
                                        <option value="">— pilih akun —</option>
                                        @foreach ($daftarUser as $us)
                                            <option value="{{ $us->id }}" @selected((string) $this->disKepadaUser === (string) $us->id)>{{ $us->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="dsb-kartu-sub">Hanya yang boleh membuka arsip surat.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-dis-nama">Atau tulis namanya</label>
                                    <input id="sr-dis-nama" type="text" maxlength="180"
                                        class="dsb-isian @error('disKepadaNama') is-galat @enderror"
                                        wire:model="disKepadaNama" placeholder="Pak Budi — Bagian Umum">
                                    @error('disKepadaNama')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Untuk orang yang tidak punya akun lemon.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-dis-batas">Tenggat</label>
                                    <input id="sr-dis-batas" type="date" class="dsb-isian @error('disBatas') is-galat @enderror" wire:model="disBatas">
                                    @error('disBatas')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Boleh kosong kalau tidak ada tenggat.</span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-dis-instruksi">Instruksi <span class="dsb-wajib">*</span></label>
                                    <textarea id="sr-dis-instruksi" rows="2" maxlength="2000"
                                        class="dsb-isian @error('disInstruksi') is-galat @enderror"
                                        wire:model="disInstruksi"
                                        placeholder="Siapkan balasan dan koordinasikan jadwalnya dengan bagian pelatihan."></textarea>
                                    @error('disInstruksi')<div class="dsb-galat">{{ $message }}</div>@enderror
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <button type="button" wire:click="tambahDisposisi" class="dsb-tombol is-utama"
                                        wire:loading.attr="disabled" wire:target="tambahDisposisi">
                                        <i class="bi bi-plus-lg"></i><span>Tambah disposisi</span>
                                    </button>
                                    <span class="dsb-kartu-sub">
                                        Surat yang masih "Belum ditindak" otomatis jadi "Sedang diproses"
                                        begitu disposisi pertamanya dibuat.
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($adaDisposisi)
                        @foreach ($disposisi as $d)
                            @php $telat = $d->telat; @endphp
                            <div class="sr-dis-baris" style="--c: {{ $telat ? '#b91c1c' : ($warnaDis[$d->status] ?? '#64748b') }}">
                                <span class="dsb-ikon is-kecil"><i class="bi bi-person-check"></i></span>
                                <span class="sr-dis-isi">
                                    <span class="sr-dis-instruksi">{{ $d->instruksi }}</span>
                                    <span class="sr-dis-meta">
                                        <span><b>{{ $d->kepada_teks }}</b></span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $d->batas_teks }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>dibuat {{ $d->pembuat?->name ?: 'sistem' }}, {{ $d->created_at->locale('id')->diffForHumans() }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    @if ($telat)
                                        <span class="dsb-lencana" style="--c: #b91c1c">Lewat tenggat</span>
                                    @else
                                        <span class="dsb-lencana" style="--c: {{ $warnaDis[$d->status] ?? '#64748b' }}">{{ $d->status_label }}</span>
                                    @endif
                                </span>
                                @if ($bolehUbah)
                                    @foreach (\App\Models\Surat\SuratDisposisi::STATUS as $kunci => $label)
                                        @php $iniDis = $d->status === $kunci; @endphp
                                        @if (! $iniDis)
                                            <button type="button" wire:click="ubahStatusDisposisi({{ $d->id }}, '{{ $kunci }}')"
                                                class="dsb-tombol is-lembut is-mungil" style="--ikon: {{ $warnaDis[$kunci] ?? '#64748b' }}"
                                                title="Tandai {{ $label }}">
                                                <span>{{ $label }}</span>
                                            </button>
                                        @endif
                                    @endforeach
                                @endif
                                @if ($bolehHapus)
                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi" title="Hapus disposisi"
                                        data-action="hapusDisposisi" data-arg="{{ $d->id }}"
                                        data-title="Hapus disposisi ini?"
                                        data-text="Catatan tugasnya hilang dan tidak bisa dikembalikan."
                                        data-confirm="Ya, hapus" data-icon="warning">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @elseif (! $bolehUbah)
                        {{-- Keadaan kosong HANYA untuk yang tidak bisa menambah.
                             Di atas borang yang sudah mengajak mengisi, kotak
                             "belum ada" cuma mengulang ajakan yang sama dengan
                             huruf lebih besar — dan meninggalkan pita kosong
                             selebar kartu di antara keduanya. Bagi yang memang
                             tidak boleh menambah, kalimat ini yang menjelaskan
                             kenapa daftarnya kosong. --}}
                        <div class="dsb-kosong sr-kosong-ringkas">
                            <span class="dsb-kosong-ikon"><i class="bi bi-person-check"></i></span>
                            <p class="dsb-kosong-judul">Belum ada disposisi</p>
                            <p class="dsb-kosong-ket">
                                Surat yang hanya dicatat tidak pernah selesai sendiri. Tentukan siapa
                                yang mengerjakan dan kapan tenggatnya.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="dsb-rak" style="margin-top: 1rem;">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #64748b"><i class="bi bi-clock-history"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Riwayat surat</h3>
                                <span class="dsb-kartu-sub">Siapa mengubah apa, dan kapan &mdash; terbaru di atas</span>
                            </div>
                        </div>
                    </div>
                    @if ($jejak->isEmpty())
                        <div class="dsb-kartu-isi">
                            <p class="dsb-kartu-sub" style="margin: 0;">
                                Belum ada riwayat. Surat yang dicatat sebelum fitur ini dipasang tidak punya
                                catatan surut &mdash; riwayat hanya bisa mulai dari sekarang.
                            </p>
                        </div>
                    @else
                        @foreach ($jejak as $baris)
                            <div class="sr-jejak-baris">
                                <span class="dsb-ikon is-kecil" style="--c: {{ $baris->warna }}"><i class="bi {{ $baris->ikon }}"></i></span>
                                <span class="sr-jejak-teks">
                                    <b>{{ $baris->aksi_label }}</b>
                                    @if ($baris->keterangan)
                                        <span>{{ $baris->keterangan }}</span>
                                    @endif
                                </span>
                                <span class="sr-jejak-waktu">
                                    {{ $baris->waktu_teks }}<br>
                                    <span>oleh {{ $baris->pelaku_teks }}</span>
                                </span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>
    </div>

    {{--
        Toast glossy milik lemon (swal-success / swal-error), partial yang sama
        dipakai Pesanan Toko. BUKAN orcha/partials/skrip: itu juga memasang
        penangan khas Orcha (orcha-sukses-pindah dan kawan-kawannya) yang tidak
        punya arti di layar ini.

        Penangan tombol .pcek-konfirmasi tidak perlu dimuat di sini — sudah
        terpasang global di layout templateindex.
    --}}
    @include('livewire.layout.sweetalert')
</div>
