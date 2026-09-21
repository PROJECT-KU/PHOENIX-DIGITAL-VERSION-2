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

        $kartuTab = [
            'all' => ['Semua artikel', 'bi-journals', '#7c3aed'],
            'published' => ['Terbit', 'bi-globe2', '#16a34a'],
            'terjadwal' => ['Terjadwal', 'bi-clock-history', '#2563eb'],
            'draft' => ['Draf', 'bi-pencil-square', '#d97706'],
        ];
        $kosong = [
            'all' => ['bi-journal-text', 'Belum ada artikel', 'Tekan "Tulis Artikel" untuk membuat tulisan pertama.'],
            'published' => ['bi-globe2', 'Belum ada yang terbit', 'Artikel yang sudah dipublikasikan muncul di sini.'],
            'terjadwal' => ['bi-clock-history', 'Tidak ada yang terjadwal', 'Artikel dengan tanggal tayang di masa depan muncul di sini.'],
            'draft' => ['bi-pencil-square', 'Tidak ada draf', 'Tulisan yang belum dipublikasikan disimpan di sini.'],
        ];
        $chipSaring = $this->chipSaring();
        $sasaranMuat = 'search,setFilter,gotoPage,nextPage,previousPage,category,urut,perPage,resetFilters,lepasSaring,saringKategori';
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
                <a wire:navigate href="{{ route('admin.blog.categories') }}" class="dsb-tombol is-lembut" style="--ikon: #2563eb">
                    <i class="bi bi-tags"></i><span>Kategori</span>
                </a>
                @if ($bolehTulis)
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
                <div class="bl-ringkas-blok" style="cursor: default;">
                    <span class="bl-ringkas-ikon is-baca"><i class="bi bi-eye-fill"></i></span>
                    <div>
                        <p><b>{{ number_format($totalDibaca, 0, ',', '.') }}</b> kali dibaca</p>
                        <p class="bl-catatan-kecil">Dihitung dari seluruh artikel, termasuk yang sudah lama tayang.</p>
                    </div>
                </div>

                <button type="button" class="bl-ringkas-blok" wire:click="$set('urut', 'populer')" title="Urutkan dari yang paling banyak dibaca">
                    <span class="bl-ringkas-ikon is-populer"><i class="bi bi-fire"></i></span>
                    <div>
                        @if ($terpopuler)
                            <p>Paling dibaca: <b>{{ \Illuminate\Support\Str::limit($terpopuler->title, 38) }}</b></p>
                            <p class="bl-catatan-kecil">{{ number_format($terpopuler->views, 0, ',', '.') }} pembaca</p>
                        @else
                            <p><b>Belum ada pembaca</b></p>
                            <p class="bl-catatan-kecil">Angka baca muncul setelah artikel terbit dan dikunjungi.</p>
                        @endif
                    </div>
                </button>

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
            </div>
        </section>

        {{-- ================== CARI & SARING ================== --}}
        <section class="dsb-kartu bl-saring">
            <div class="dsb-kartu-isi bl-saring-isi">
                <div class="dsb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="search" placeholder="Cari judul, kategori, atau ringkasan…" aria-label="Cari artikel">
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

                <select class="dsb-isian bl-pilih" wire:model.live="urut" aria-label="Urutkan">
                    <option value="baru">Terbaru</option>
                    <option value="lama">Terlama</option>
                    <option value="populer">Paling banyak dibaca</option>
                    <option value="judul">Judul A-Z</option>
                </select>

                <select class="dsb-isian bl-pilih is-sempit" wire:model.live="perPage" aria-label="Jumlah per halaman">
                    @foreach ([12, 24, 48] as $n)
                        <option value="{{ $n }}">{{ $n }}/halaman</option>
                    @endforeach
                </select>

                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="{{ $sasaranMuat }}">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
            </div>

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

                {{-- Bentuk KARTU dan TABEL memakai markup yang SAMA; yang berubah
                     hanya kelasnya, jadi kartunya bergeser posisi alih-alih
                     dibongkar lalu dibangun ulang. Itu yang bikin pergantiannya
                     mulus (pola yang sama dipakai Moderasi Ulasan Produk). --}}
                <div class="bl-daftar {{ $tampilan === 'tabel' ? 'is-tabel' : '' }}"
                    x-bind:class="pilihan === 'tabel' ? 'is-tabel' : ''">
                    <div class="bl-tabel-kepala">
                        <button type="button" class="{{ $urut === 'judul' ? 'is-aktif' : '' }}" wire:click="$set('urut', '{{ $urut === 'judul' ? 'baru' : 'judul' }}')">
                            Artikel · {{ $urut === 'judul' ? 'judul A-Z' : 'terbaru' }}
                            <i class="bi {{ $urut === 'judul' ? 'bi-sort-alpha-down' : 'bi-chevron-expand' }}"></i>
                        </button>
                        <button type="button" class="{{ $urut === 'populer' ? 'is-aktif' : '' }}" wire:click="$set('urut', '{{ $urut === 'populer' ? 'baru' : 'populer' }}')">
                            Kategori &amp; pembaca · {{ $urut === 'populer' ? 'paling banyak dibaca' : 'terbaru' }}
                            <i class="bi {{ $urut === 'populer' ? 'bi-sort-down' : 'bi-chevron-expand' }}"></i>
                        </button>
                        <span>Tindakan</span>
                    </div>

                    <div class="bl-rak" wire:key="rak-{{ $filter }}-{{ $posts->currentPage() }}">
                        @foreach ($posts as $item)
                            @php
                                [$keadaan, $lencana, $warna, $ikonKeadaan] = $item->keadaan();
                                $sampul = $item->sampulUrl();
                                $tanggal = $item->published_at ?? $item->created_at;
                            @endphp
                            <article class="bl-kartu" style="--c: {{ $warna }}" wire:key="artikel-{{ $item->id }}">
                                @if ($sampul)
                                    <button type="button" class="bl-sampul bl-gambar-besar" data-gambar="{{ $sampul }}" title="Perbesar sampul">
                                        <img src="{{ $sampul }}" alt="Sampul {{ $item->title }}" loading="lazy">
                                        <span class="bl-sampul-lencana"><i class="bi {{ $ikonKeadaan }}"></i>{{ $keadaan }}</span>
                                    </button>
                                @else
                                    <span class="bl-sampul">
                                        <span class="bl-sampul-kosong"><i class="bi bi-card-image"></i><span>Belum ada sampul</span></span>
                                        <span class="bl-sampul-lencana"><i class="bi {{ $ikonKeadaan }}"></i>{{ $keadaan }}</span>
                                    </span>
                                @endif

                                <div class="bl-isi">
                                    <h2 class="bl-judul">
                                        @if ($bolehUbah)
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
                                    <span class="bl-tanda is-baca"><i class="bi bi-eye"></i>{{ number_format($item->views, 0, ',', '.') }}</span>
                                    <span class="bl-tanda is-waktu"><i class="bi bi-hourglass"></i>{{ $item->lamaBaca() }} mnt</span>
                                    <span class="bl-tanda is-waktu">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ optional($tanggal)->locale('id')->translatedFormat($keadaan === 'Terjadwal' ? 'd M Y, H:i' : 'd M Y') }}
                                    </span>
                                </div>

                                <div class="bl-aksi">
                                    <div class="bl-aksi-utama">
                                        @if ($bolehUbah)
                                            <a wire:navigate href="{{ route('admin.blog.edit', $item) }}" class="bl-btn is-utama" title="Sunting artikel">
                                                <i class="bi bi-pencil-square"></i><span>Sunting</span>
                                            </a>
                                        @endif
                                    </div>
                                    <div class="bl-aksi-lain">
                                        @if ($bolehUbah)
                                            <button type="button" class="bl-btn bl-btn-ikon {{ $item->status === 'published' ? 'is-draf' : 'is-terbit' }}"
                                                wire:click="togglePublish('{{ $item->id }}')"
                                                title="{{ $item->status === 'published' ? 'Kembalikan ke draf' : 'Publikasikan sekarang' }}"
                                                aria-label="{{ $item->status === 'published' ? 'Kembalikan ke draf' : 'Publikasikan sekarang' }}">
                                                <i class="bi {{ $item->status === 'published' ? 'bi-eye-slash' : 'bi-globe2' }}"></i>
                                            </button>
                                        @endif
                                        @if ($keadaan === 'Terbit')
                                            <a href="{{ route('blog.show', $item->slug) }}" target="_blank" rel="noopener"
                                                class="bl-btn bl-btn-ikon" title="Buka halaman publik" aria-label="Buka halaman publik">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @endif
                                        @if ($bolehHapus)
                                            <button type="button" class="bl-btn bl-btn-ikon is-bahaya pcek-konfirmasi"
                                                data-action="delete" data-arg="{{ $item->id }}"
                                                data-title="Hapus artikel ini?"
                                                data-text="Artikel dan gambar sampulnya dihapus permanen dan tidak bisa dikembalikan."
                                                data-confirm="Ya, hapus" data-icon="warning"
                                                title="Hapus artikel" aria-label="Hapus artikel">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                @if ($posts->hasPages())
                    <div class="bl-halaman">{{ $posts->links('vendor.pagination') }}</div>
                @endif
            @endif
        </section>
    </div>

    @include('livewire.layout.sweetalert')
    @include('livewire.pages.admin.blog.partials.artikel-gambar')
</div>
