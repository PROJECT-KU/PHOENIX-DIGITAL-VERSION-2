@section('title')
Artikel || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.blog.partials.artikel-gaya')

    @php
        $bolehTulis = (bool) auth()->user()?->hasPermission('create_blog');
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_blog');
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_blog');
        $sampah = $filter === 'sampah';

        $kartuTab = [
            'all' => ['Semua artikel', 'bi-journals', '#7c3aed'],
            'published' => ['Terbit', 'bi-globe2', '#16a34a'],
            'terjadwal' => ['Terjadwal', 'bi-clock-history', '#2563eb'],
            'draft' => ['Draf', 'bi-pencil-square', '#d97706'],
            'sampah' => ['Tong sampah', 'bi-trash3', '#64748b'],
        ];
        $kosong = [
            'all' => ['bi-journal-text', 'Belum ada artikel', 'Tekan "Tulis Artikel" untuk membuat tulisan pertama.'],
            'published' => ['bi-globe2', 'Belum ada yang terbit', 'Artikel yang sudah dipublikasikan muncul di sini.'],
            'terjadwal' => ['bi-clock-history', 'Tidak ada yang terjadwal', 'Artikel dengan tanggal tayang di masa depan muncul di sini.'],
            'draft' => ['bi-pencil-square', 'Tidak ada draf', 'Tulisan yang belum dipublikasikan disimpan di sini.'],
            'sampah' => ['bi-trash3', 'Tong sampah kosong', 'Artikel yang dibuang bisa dikembalikan dari sini.'],
        ];

        $chipSaring = $this->chipSaring();
        $idHalaman = $posts->pluck('id')->map(fn ($i) => (string) $i)->all();
        $semuaTercentang = $idHalaman && ! array_diff($idHalaman, $pilih);
        $sasaranMuat = 'search,setFilter,gotoPage,nextPage,previousPage,category,tag,urut,perPage,resetFilters,lepasSaring,urutkanKolom,fUnggulan,fMandek';

        $tren = $dibaca30Sebelumnya === 0
            ? null
            : (int) round((($dibaca30 - $dibaca30Sebelumnya) / $dibaca30Sebelumnya) * 100);
        $trenNaik = $tren !== null && $tren >= 0;
        $adaMandek = $jumlahMandek !== 0;
        $adaSisaHalaman = $posts->total() !== count($idHalaman);
    @endphp

    <div class="dsb" x-data="{ pilihan: @js($tampilan) }">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Artikel</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Tulisan blog Phoenix Digital — draf, jadwal tayang, dan artikel yang sudah dibaca pengunjung.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <button type="button" class="dsb-tombol is-lembut" wire:click="unduhExcel" wire:loading.attr="disabled" wire:target="unduhExcel" style="--ikon: #16a34a">
                    <span wire:loading.remove wire:target="unduhExcel" class="bl-isi-tombol"><i class="bi bi-file-earmark-excel"></i><span>Excel</span></span>
                    <span wire:loading.inline-flex wire:target="unduhExcel" class="bl-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                <button type="button" class="dsb-tombol is-lembut" wire:click="unduhPdf" wire:loading.attr="disabled" wire:target="unduhPdf" style="--ikon: #dc2626">
                    <span wire:loading.remove wire:target="unduhPdf" class="bl-isi-tombol"><i class="bi bi-file-earmark-pdf"></i><span>PDF</span></span>
                    <span wire:loading.inline-flex wire:target="unduhPdf" class="bl-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                <button type="button" class="dsb-tombol is-lembut" wire:click="unduhMarkdown" wire:loading.attr="disabled" wire:target="unduhMarkdown" style="--ikon: #0f172a" title="Unduh naskah sebagai Markdown (untuk pindah platform)">
                    <span wire:loading.remove wire:target="unduhMarkdown" class="bl-isi-tombol"><i class="bi bi-markdown"></i><span>Markdown</span></span>
                    <span wire:loading.inline-flex wire:target="unduhMarkdown" class="bl-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                <a wire:navigate href="{{ route('admin.blog.categories') }}" class="dsb-tombol is-lembut" style="--ikon: #2563eb">
                    <i class="bi bi-tags"></i><span>Kategori</span>
                </a>
                @if ($bolehTulis)
                    {{-- Impor berkas tulisan (.md/.html/.txt) jadi draf baru. --}}
                    <label class="dsb-tombol is-lembut bl-impor" style="--ikon: #0e7490" title="Impor berkas .md, .html, atau .txt sebagai draf">
                        <span wire:loading.remove wire:target="berkasImpor" class="bl-isi-tombol"><i class="bi bi-box-arrow-in-down"></i><span>Impor</span></span>
                        <span wire:loading.inline-flex wire:target="berkasImpor" class="bl-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Mengimpor…</span></span>
                        <input type="file" multiple wire:model="berkasImpor" accept=".md,.markdown,.html,.htm,.txt">
                    </label>
                    <a wire:navigate href="{{ route('admin.blog.create') }}" class="dsb-tombol is-utama">
                        <i class="bi bi-pencil-square"></i><span>Tulis Artikel</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== TAB KEADAAN ================== --}}
        <nav class="bl-status" aria-label="Saring menurut keadaan artikel">
            @foreach ($kartuTab as $nilai => [$label, $ikon, $warna])
                <button type="button" class="bl-status-btn {{ $filter === $nilai ? 'is-aktif' : '' }}"
                    style="--c: {{ $warna }}" wire:click="setFilter('{{ $nilai }}')" aria-pressed="{{ $filter === $nilai ? 'true' : 'false' }}">
                    <span class="bl-status-ikon"><i class="bi {{ $ikon }}"></i></span>
                    <span class="bl-status-teks"><b>{{ $tabCounts[$nilai] }}</b><span>{{ $label }}</span></span>
                </button>
            @endforeach
        </nav>

        {{-- ================== RINGKASAN ================== --}}
        <section class="dsb-kartu bl-ringkas">
            <div class="dsb-kartu-isi bl-ringkas-isi">
                <button type="button" class="bl-ringkas-blok" wire:click="urutkanKolom('baca')" title="Urutkan menurut jumlah pembaca">
                    <span class="bl-ringkas-ikon is-baca"><i class="bi bi-eye-fill"></i></span>
                    <div>
                        <p><b>{{ number_format($dibaca30, 0, ',', '.') }}</b> dibaca 30 hari terakhir</p>
                        <p class="bl-catatan-kecil">
                            @if ($tren === null)
                                Total sepanjang masa {{ number_format($totalDibaca, 0, ',', '.') }} kali.
                            @elseif ($trenNaik)
                                Naik {{ $tren }}% dari 30 hari sebelumnya.
                            @else
                                Turun {{ abs($tren) }}% dari 30 hari sebelumnya.
                            @endif
                        </p>
                    </div>
                </button>

                <button type="button" class="bl-ringkas-blok" wire:click="$set('urut', 'populer')" title="Urutkan dari yang paling banyak dibaca">
                    <span class="bl-ringkas-ikon is-populer"><i class="bi bi-fire"></i></span>
                    <div>
                        @if ($terpopuler)
                            <p>Paling dibaca: <b>{{ \Illuminate\Support\Str::limit($terpopuler->title, 38) }}</b></p>
                            <p class="bl-catatan-kecil">{{ number_format($terpopuler->views, 0, ',', '.') }} pembaca sepanjang masa</p>
                        @else
                            <p><b>Belum ada pembaca</b></p>
                            <p class="bl-catatan-kecil">Angka baca muncul setelah artikel terbit dan dikunjungi.</p>
                        @endif
                    </div>
                </button>

                @if ($adaMandek)
                    <button type="button" class="bl-ringkas-blok" wire:click="$set('fMandek', true)" title="Lihat artikel yang mandek">
                        <span class="bl-ringkas-ikon is-populer"><i class="bi bi-hourglass-bottom"></i></span>
                        <div>
                            <p><b>{{ $jumlahMandek }}</b> artikel mandek</p>
                            <p class="bl-catatan-kecil">Sudah lama terbit tapi tidak dibaca sama sekali sebulan terakhir.</p>
                        </div>
                    </button>
                @else
                    <button type="button" class="bl-ringkas-blok" wire:click="setFilter('published')" title="Lihat artikel yang sudah terbit">
                        <span class="bl-ringkas-ikon is-terbit"><i class="bi bi-send-check-fill"></i></span>
                        <div>
                            @if ($terbaru)
                                <p>Terbit terakhir: <b>{{ \Illuminate\Support\Str::limit($terbaru->title, 38) }}</b></p>
                                <p class="bl-catatan-kecil">{{ optional($terbaru->published_at ?? $terbaru->created_at)->locale('id')->translatedFormat('d F Y') }}</p>
                            @else
                                <p><b>Belum ada yang terbit</b></p>
                                <p class="bl-catatan-kecil">Draf tidak terlihat pengunjung sampai dipublikasikan.</p>
                            @endif
                        </div>
                    </button>
                @endif
            </div>
        </section>

        {{-- ================== CARI & SARING ================== --}}
        <section class="dsb-kartu bl-saring">
            <div class="dsb-kartu-isi bl-saring-isi">
                <div class="dsb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" id="bl-cari" class="dsb-isian" wire:model.live.debounce.300ms="search" placeholder="Cari judul, kategori, tag, atau ringkasan…" aria-label="Cari artikel">
                    @if ($search)
                        <button type="button" class="dsb-cari-hapus" wire:click="$set('search', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>

                {{-- Pil putihnya bergeser; tampilannya sendiri diganti seketika
                     oleh Alpine supaya tidak menunggu jawaban server. --}}
                <div class="bl-saklar {{ $tampilan === 'tabel' ? 'is-tabel' : '' }}" role="group" aria-label="Bentuk tampilan daftar"
                    x-bind:class="pilihan === 'tabel' ? 'is-tabel' : ''">
                    <span class="bl-saklar-pil" aria-hidden="true"></span>
                    <button type="button" x-bind:class="pilihan === 'kartu' ? 'is-aktif' : ''"
                        x-on:click="pilihan = 'kartu'" wire:click="setTampilan('kartu')"
                        x-bind:aria-pressed="(pilihan === 'kartu').toString()">
                        <i class="bi bi-grid"></i><span>Kartu</span>
                    </button>
                    <button type="button" x-bind:class="pilihan === 'tabel' ? 'is-aktif' : ''"
                        x-on:click="pilihan = 'tabel'" wire:click="setTampilan('tabel')"
                        x-bind:aria-pressed="(pilihan === 'tabel').toString()">
                        <i class="bi bi-list-ul"></i><span>Tabel</span>
                    </button>
                </div>

                <select class="dsb-isian bl-pilih" wire:model.live="category" aria-label="Saring kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoriDaftar as $nama)
                        <option value="{{ $nama }}">{{ $nama }}</option>
                    @endforeach
                </select>

                @if ($tagDaftar)
                    <select class="dsb-isian bl-pilih is-sempit" wire:model.live="tag" aria-label="Saring tag">
                        <option value="">Semua tag</option>
                        @foreach ($tagDaftar as $nama)
                            <option value="{{ $nama }}">#{{ $nama }}</option>
                        @endforeach
                    </select>
                @endif

                @if ($penyuntingDaftar->isNotEmpty())
                    <select class="dsb-isian bl-pilih" wire:model.live="fPenyunting" aria-label="Saring menurut penyunting">
                        <option value="">Semua penyunting</option>
                        @foreach ($penyuntingDaftar as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                @endif

                <select class="dsb-isian bl-pilih" wire:model.live="urut" aria-label="Urutkan">
                    <option value="baru">Terbaru</option>
                    <option value="lama">Terlama</option>
                    <option value="populer">Paling banyak dibaca</option>
                    <option value="populer30">Paling ramai 30 hari</option>
                    <option value="diperbarui">Terakhir diubah</option>
                    <option value="judul">Judul A-Z</option>
                </select>

                <select class="dsb-isian bl-pilih is-sempit" wire:model.live="perPage" aria-label="Jumlah per halaman">
                    @foreach ([12, 24, 48] as $n)
                        <option value="{{ $n }}">{{ $n }}/halaman</option>
                    @endforeach
                </select>

                <label class="bl-tukar {{ $fUnggulan ? 'is-nyala' : '' }}" title="Hanya artikel yang disematkan di halaman blog">
                    <input type="checkbox" wire:model.live="fUnggulan">
                    <span>Disematkan</span>
                </label>

                <label class="bl-tukar {{ $ikutIsi ? 'is-nyala' : '' }}" title="Sertakan isi artikel di berkas Excel/PDF yang diunduh">
                    <input type="checkbox" wire:model.live="ikutIsi">
                    <span>Unduh + isi</span>
                </label>

                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="{{ $sasaranMuat }}">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
                <span class="bl-pintasan" aria-hidden="true"><kbd>/</kbd> cari · <kbd>t</kbd> ganti tampilan</span>
            </div>

            @error('berkasImpor')
                <div class="bl-chip-saring"><span class="bl-galat">{{ $message }}</span></div>
            @enderror
            @error('berkasImpor.*')
                <div class="bl-chip-saring"><span class="bl-galat">{{ $message }}</span></div>
            @enderror

            @unless ($tagDaftar)
                <p class="bl-petunjuk" style="margin: 0 clamp(14px, 2vw, 20px) clamp(12px, 2vw, 16px);">
                    <i class="bi bi-lightbulb"></i>
                    <span>Belum ada artikel yang diberi tag. Tag mengelompokkan tulisan lebih tajam daripada kategori — dipakai bilah topik di halaman blog dan menentukan "artikel terkait" yang muncul di bawah tulisan.</span>
                </p>
            @endunless

            @if ($chipSaring)
                <div class="bl-chip-saring">
                    @foreach ($chipSaring as $chip)
                        <button type="button" class="bl-chip-lepas" wire:click="lepasSaring('{{ $chip['nama'] }}')" title="Lepas saringan ini">
                            {{ $chip['label'] }}<i class="bi bi-x-lg"></i>
                        </button>
                    @endforeach
                    <button type="button" class="bl-chip-lepas is-semua" wire:click="resetFilters">Bersihkan semua</button>
                </div>
            @endif
        </section>

        {{-- Kerangka pemuatan: tanpa ini kartu lama hanya meredup, dan sekilas
             tampak seolah tidak ada yang berubah. --}}
        <div class="bl-kerangka" wire:loading.grid wire:target="{{ $sasaranMuat }}">
            @for ($i = 0; $i < 6; $i++)
                <div class="bl-kerangka-kartu">
                    <div class="bl-kerangka-sampul"><span class="bl-tulang" style="width: 100%; height: 100%; border-radius: 0;"></span></div>
                    <div class="bl-kerangka-isi">
                        <span class="bl-tulang" style="width: 80%"></span>
                        <span class="bl-tulang" style="width: 45%"></span>
                        <span class="bl-tulang" style="width: 100%; height: 30px;"></span>
                    </div>
                </div>
            @endfor
        </div>

        {{-- ================== DAFTAR ARTIKEL ================== --}}
        <section class="bl-wadah" wire:loading.class="bl-sembunyi" wire:target="{{ $sasaranMuat }}">
            @if ($undo)
                <div class="bl-urung">
                    <i class="bi bi-trash3"></i>
                    <span>{{ count($undo) }} artikel dipindahkan ke tong sampah.</span>
                    <button type="button" class="bl-btn" wire:click="batalkanHapus"><i class="bi bi-arrow-counterclockwise"></i><span>Urungkan</span></button>
                </div>
            @endif

            @if ($posts->isEmpty())
                @php
                    [$kIkon, $kJudul, $kKet] = $this->adaSaring
                        ? ['bi-funnel', 'Tidak ada artikel yang cocok', 'Coba ubah kata kunci atau bersihkan saringan.']
                        : $kosong[$filter];
                @endphp
                <div class="dsb-kartu">
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi {{ $kIkon }}"></i></span>
                        <p class="dsb-kosong-judul">{{ $kJudul }}</p>
                        <p class="dsb-kosong-ket">{{ $kKet }}</p>
                    </div>
                </div>
            @else
                <p class="visually-hidden" role="status" aria-live="polite">
                    {{ $posts->total() }} artikel ditemukan.
                </p>

                @if ($bolehUbah || $bolehHapus)
                    <div class="bl-pilih-semua">
                        <label class="bl-centang">
                            <input type="checkbox" @checked($semuaTercentang) wire:click="pilihHalaman({{ \Illuminate\Support\Js::from($idHalaman) }})">
                            <span>Pilih semua di halaman ini</span>
                        </label>
                        @if ($semuaTercentang && $adaSisaHalaman)
                            <button type="button" class="bl-tautan" wire:click="pilihSemuaHasil">
                                Pilih semua {{ min(\App\Livewire\Pages\Admin\Blog\BlogList::BATAS_PILIH, $posts->total()) }} artikel hasil saringan
                            </button>
                        @endif
                        @if ($sampah && $bolehHapus)
                            <button type="button" class="bl-tautan pcek-konfirmasi" data-action="kosongkanSampah"
                                data-title="Kosongkan tong sampah?"
                                data-text="Semua artikel di tong sampah beserta gambarnya dihapus permanen."
                                data-confirm="Ya, kosongkan" data-icon="warning">Kosongkan tong sampah</button>
                        @endif
                    </div>
                @endif

                {{-- Bentuk KARTU dan TABEL memakai markup yang SAMA; yang berubah
                     hanya kelasnya, jadi kartunya bergeser posisi alih-alih
                     dibongkar lalu dibangun ulang. Itu yang bikin pergantiannya
                     mulus (pola yang sama dipakai Moderasi Ulasan Produk). --}}
                <div class="bl-daftar {{ $tampilan === 'tabel' ? 'is-tabel' : '' }}"
                    x-bind:class="pilihan === 'tabel' ? 'is-tabel' : ''">
                    <div class="bl-tabel-kepala">
                        <button type="button" class="{{ $this->arahUrut('judul') ? 'is-aktif' : '' }}" wire:click="urutkanKolom('judul')">
                            Artikel · {{ $urut === 'judul' ? 'judul A-Z' : 'terbaru' }}
                            <i class="bi {{ $urut === 'judul' ? 'bi-sort-alpha-down' : 'bi-chevron-expand' }}"></i>
                        </button>
                        <button type="button" class="{{ $this->arahUrut('baca') ? 'is-aktif' : '' }}" wire:click="urutkanKolom('baca')">
                            Kategori &amp; pembaca · {{ $urut === 'populer30' ? '30 hari' : ($urut === 'populer' ? 'sepanjang masa' : 'terbaru') }}
                            <i class="bi {{ $this->arahUrut('baca') ? 'bi-sort-down' : 'bi-chevron-expand' }}"></i>
                        </button>
                        <span>Tindakan</span>
                    </div>

                    <div class="bl-rak" wire:key="rak-{{ $filter }}-{{ $posts->currentPage() }}">
                        @foreach ($posts as $item)
                            @php
                                [$keadaan, $lencana, $warna, $ikonKeadaan] = $item->keadaan();
                                // Di tong sampah keadaan aslinya tidak relevan lagi.
                                $labelKeadaan = $sampah ? 'Di tong sampah' : $keadaan;
                                $ikonKeadaan = $sampah ? 'bi-trash3' : $ikonKeadaan;
                                $sampul = $item->sampulUrl();
                                $tanggal = $item->published_at ?? $item->created_at;
                                $tercentang = in_array((string) $item->id, $pilih, true);
                                $mundur = $item->hitungMundur();
                            @endphp
                            <article class="bl-kartu {{ $tercentang ? 'is-tercentang' : '' }}" style="--c: {{ $warna }}" wire:key="artikel-{{ $item->id }}">
                                @if ($bolehUbah || $bolehHapus)
                                    <input type="checkbox" class="bl-kartu-centang" @checked($tercentang)
                                        wire:click="pilihHalaman({{ \Illuminate\Support\Js::from([(string) $item->id]) }})"
                                        aria-label="Pilih artikel {{ $item->title }}">
                                @endif

                                @if ($sampul)
                                    <button type="button" class="bl-sampul bl-gambar-besar" data-gambar="{{ $sampul }}" title="Perbesar sampul">
                                        <img src="{{ $sampul }}" alt="{{ $item->cover_alt ?: 'Sampul '.$item->title }}" loading="lazy">
                                        <span class="bl-sampul-lencana"><i class="bi {{ $ikonKeadaan }}"></i>{{ $labelKeadaan }}</span>
                                        @if ($item->is_featured)
                                            <span class="bl-sampul-semat" title="Disematkan di halaman blog"><i class="bi bi-pin-angle-fill"></i></span>
                                        @endif
                                    </button>
                                @else
                                    <span class="bl-sampul">
                                        <span class="bl-sampul-kosong"><i class="bi bi-card-image"></i><span>Belum ada sampul</span></span>
                                        <span class="bl-sampul-lencana"><i class="bi {{ $ikonKeadaan }}"></i>{{ $labelKeadaan }}</span>
                                        @if ($item->is_featured)
                                            <span class="bl-sampul-semat" title="Disematkan di halaman blog"><i class="bi bi-pin-angle-fill"></i></span>
                                        @endif
                                    </span>
                                @endif

                                <div class="bl-isi">
                                    <h2 class="bl-judul">
                                        @if ($bolehUbah && ! $sampah)
                                            <a wire:navigate href="{{ route('admin.blog.edit', $item) }}">{!! \App\Support\SorotKata::pada($item->title, $search) !!}</a>
                                        @else
                                            {!! \App\Support\SorotKata::pada($item->title, $search) !!}
                                        @endif
                                    </h2>
                                    <span class="bl-slug">/blog/{{ $item->slug }}</span>
                                    @if ($item->excerpt)
                                        <p class="bl-cuplikan">{!! \App\Support\SorotKata::pada(\Illuminate\Support\Str::limit($item->excerpt, 160), $search) !!}</p>
                                    @endif
                                </div>

                                <div class="bl-penanda">
                                    @if ($item->category)
                                        <button type="button" class="bl-tanda is-kategori" wire:click="$set('category', @js($item->category))" title="Saring kategori ini">
                                            <i class="bi bi-tag-fill"></i>{{ $item->category }}
                                        </button>
                                    @endif
                                    @foreach (array_slice($item->tagDaftar(), 0, 2) as $i => $t)
                                        <button type="button" class="bl-tanda is-tag {{ $i === 0 ? '' : 'is-sekunder' }}" wire:click="$set('tag', @js($t))" title="Saring tag ini">#{{ $t }}</button>
                                    @endforeach
                                    <span class="bl-tanda is-baca" title="Dibaca sepanjang masa · 30 hari terakhir">
                                        <i class="bi bi-eye"></i>{{ number_format($item->views, 0, ',', '.') }}
                                        <small style="opacity:.7">· {{ number_format($item->baca30(), 0, ',', '.') }}</small>
                                    </span>
                                    <span class="bl-tanda is-waktu is-sekunder"><i class="bi bi-hourglass"></i>{{ $item->lamaBaca() }} mnt</span>
                                    @if ($mundur)
                                        <span class="bl-tanda is-hitung"><i class="bi bi-clock"></i>tayang {{ $mundur }}</span>
                                    @else
                                        <span class="bl-tanda is-waktu">
                                            <i class="bi bi-calendar-event"></i>
                                            {{ optional($tanggal)->locale('id')->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                    @if ($item->is_featured)
                                        <span class="bl-tanda is-semat"><i class="bi bi-pin-angle-fill"></i>Disematkan</span>
                                    @endif
                                    @if ($sampah && $item->deleted_at)
                                        <span class="bl-tanda is-waktu" title="Waktu artikel ini dibuang">
                                            <i class="bi bi-trash3"></i>dibuang {{ $item->deleted_at->locale('id')->diffForHumans() }}
                                        </span>
                                    @endif
                                    @if ($item->penyunting)
                                        <span class="bl-tanda is-waktu is-sekunder" title="Terakhir diubah {{ optional($item->updated_at)->locale('id')->translatedFormat('d M Y H:i') }}">
                                            <i class="bi bi-person"></i>{{ \Illuminate\Support\Str::limit($item->penyunting->name, 14) }}
                                        </span>
                                    @endif
                                    @if (! $sampah && $item->mandek())
                                        <span class="bl-tanda is-mandek" title="Sudah lama terbit tanpa pembaca sebulan terakhir"><i class="bi bi-hourglass-bottom"></i>Mandek</span>
                                    @endif
                                </div>

                                <div class="bl-aksi">
                                    @if ($sampah)
                                        <div class="bl-aksi-utama">
                                            @if ($bolehHapus)
                                                <button type="button" class="bl-btn is-utama" wire:click="pulihkan('{{ $item->id }}')">
                                                    <i class="bi bi-arrow-counterclockwise"></i><span>Kembalikan</span>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="bl-aksi-lain">
                                            @if ($bolehHapus)
                                                <button type="button" class="bl-btn bl-btn-ikon is-bahaya pcek-konfirmasi"
                                                    data-action="hapusPermanen" data-arg="{{ $item->id }}"
                                                    data-title="Hapus permanen?"
                                                    data-text="Artikel dan gambar sampulnya dihapus selamanya dan tidak bisa dikembalikan."
                                                    data-confirm="Ya, hapus permanen" data-icon="warning"
                                                    title="Hapus permanen" aria-label="Hapus permanen"><i class="bi bi-trash"></i></button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="bl-aksi-utama">
                                            @if ($bolehUbah)
                                                <a wire:navigate href="{{ route('admin.blog.edit', $item) }}" class="bl-btn is-utama" title="Sunting artikel">
                                                    <i class="bi bi-pencil-square"></i><span>Sunting</span>
                                                </a>
                                            @endif
                                        </div>
                                        <div class="bl-aksi-lain">
                                            {{-- Satu tombol untuk semua keadaan: artikel terbit membuka
                                                 halaman publiknya, draf & terjadwal membuka pratinjau
                                                 yang hanya bisa dilihat admin. --}}
                                            <a href="{{ route('blog.show', $item->slug) }}" target="_blank" rel="noopener"
                                                class="bl-btn bl-btn-ikon" title="{{ $keadaan === 'Terbit' ? 'Buka halaman publik' : 'Pratinjau (belum tayang)' }}"
                                                aria-label="{{ $keadaan === 'Terbit' ? 'Buka halaman publik' : 'Pratinjau' }}">
                                                <i class="bi {{ $keadaan === 'Terbit' ? 'bi-box-arrow-up-right' : 'bi-eye' }}"></i>
                                            </a>
                                            @if ($bolehUbah)
                                                <button type="button" class="bl-btn bl-btn-ikon {{ $item->is_featured ? 'is-aktif' : '' }}"
                                                    wire:click="toggleSemat('{{ $item->id }}')"
                                                    title="{{ $item->is_featured ? 'Lepas sematan' : 'Sematkan di atas halaman blog' }}"
                                                    aria-label="{{ $item->is_featured ? 'Lepas sematan' : 'Sematkan' }}">
                                                    <i class="bi {{ $item->is_featured ? 'bi-pin-angle-fill' : 'bi-pin-angle' }}"></i>
                                                </button>
                                                <button type="button" class="bl-btn bl-btn-ikon {{ $item->status === 'published' ? 'is-draf' : 'is-terbit' }}"
                                                    wire:click="togglePublish('{{ $item->id }}')"
                                                    title="{{ $item->status === 'published' ? 'Kembalikan ke draf' : 'Publikasikan sekarang' }}"
                                                    aria-label="{{ $item->status === 'published' ? 'Kembalikan ke draf' : 'Publikasikan sekarang' }}">
                                                    <i class="bi {{ $item->status === 'published' ? 'bi-eye-slash' : 'bi-globe2' }}"></i>
                                                </button>
                                            @endif
                                            @if ($bolehTulis)
                                                <button type="button" class="bl-btn bl-btn-ikon pcek-konfirmasi"
                                                    data-action="duplikat" data-arg="{{ $item->id }}"
                                                    data-title="Salin artikel ini?"
                                                    data-text="Salinannya dibuat sebagai draf baru, lalu langsung dibuka untuk disunting."
                                                    data-confirm="Ya, salin" data-icon="question"
                                                    title="Salin jadi draf baru" aria-label="Salin artikel"><i class="bi bi-files"></i></button>
                                            @endif
                                            @if ($bolehHapus)
                                                <button type="button" class="bl-btn bl-btn-ikon is-bahaya pcek-konfirmasi"
                                                    data-action="delete" data-arg="{{ $item->id }}"
                                                    data-title="Buang artikel ini?"
                                                    data-text="Artikel dipindahkan ke tong sampah dan masih bisa dikembalikan."
                                                    data-confirm="Ya, buang" data-icon="warning"
                                                    title="Buang ke tong sampah" aria-label="Buang ke tong sampah"><i class="bi bi-trash"></i></button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                @if ($posts->hasPages())
                    <div class="bl-halaman">{{ $posts->links('vendor.pagination') }}</div>
                @endif
            @endif

            {{-- Bilah aksi massal — menempel di bawah layar selama ada yang dicentang. --}}
            @if ($pilih)
                <div class="bl-massal">
                    <b>{{ count($pilih) }} dipilih</b>
                    <span class="bl-massal-pisah"></span>
                    @if ($sampah)
                        @if ($bolehHapus)
                            <button type="button" class="bl-btn" wire:click="massalPulihkan"><i class="bi bi-arrow-counterclockwise"></i><span>Kembalikan</span></button>
                        @endif
                    @else
                        @if ($bolehUbah)
                            <button type="button" class="bl-btn" wire:click="massalStatus('published')"><i class="bi bi-globe2"></i><span>Terbitkan</span></button>
                            <button type="button" class="bl-btn" wire:click="massalStatus('draft')"><i class="bi bi-eye-slash"></i><span>Jadikan draf</span></button>
                            <select class="dsb-isian bl-pilih is-sempit" wire:change="massalKategori($event.target.value)" aria-label="Pindahkan ke kategori">
                                <option value="">Pindah kategori…</option>
                                @foreach ($kategoriDaftar as $nama)
                                    <option value="{{ $nama }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="dsb-isian bl-pilih is-sempit" wire:model="tagMassal"
                                placeholder="Tag…" maxlength="40" aria-label="Tag untuk aksi massal">
                            <button type="button" class="bl-btn" wire:click="massalTagTambah" title="Tambahkan tag ini ke semua yang dicentang">
                                <i class="bi bi-plus-lg"></i><span>Tag</span>
                            </button>
                            <button type="button" class="bl-btn" wire:click="massalTagLepas" title="Lepas tag ini dari semua yang dicentang">
                                <i class="bi bi-dash-lg"></i><span>Tag</span>
                            </button>
                        @endif
                        @if ($bolehHapus)
                            <button type="button" class="bl-btn is-bahaya pcek-konfirmasi" data-action="massalHapus"
                                data-title="Buang {{ count($pilih) }} artikel?"
                                data-text="Semuanya dipindahkan ke tong sampah dan masih bisa dikembalikan."
                                data-confirm="Ya, buang" data-icon="warning"><i class="bi bi-trash"></i><span>Buang</span></button>
                        @endif
                    @endif
                    <button type="button" class="bl-btn" wire:click="lepasPilih"><i class="bi bi-x"></i><span>Lepas</span></button>
                </div>
            @endif
        </section>
    </div>

    @include('livewire.layout.sweetalert')
    @include('livewire.pages.admin.blog.partials.artikel-gambar')
    @include('livewire.pages.admin.blog.partials.artikel-pintasan')
</div>
