@section('title')
Pesan Pelanggan || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.message.partials.pesan-gaya')

    @php
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_customer_message');
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_customer_message');
        $arsip = $tab === 'arsip';
        $kategoriDaftar = config('helpdesk.kategori');
        $kartuTab = [
            'baru' => ['Belum dibaca', 'bi-envelope-exclamation-fill', '#d97706'],
            'berjalan' => ['Masih berjalan', 'bi-hourglass-split', '#2563eb'],
            'selesai' => ['Selesai', 'bi-check-circle-fill', '#16a34a'],
            'semua' => ['Semua', 'bi-chat-left-text-fill', '#7c3aed'],
            'arsip' => ['Arsip', 'bi-archive-fill', '#64748b'],
        ];
        $kosong = [
            'baru' => ['bi-inbox', 'Tidak ada pesan baru', 'Semua pesan pelanggan sudah dibaca.'],
            'berjalan' => ['bi-hourglass', 'Tidak ada yang berjalan', 'Semua tiket sudah selesai atau ditutup.'],
            'selesai' => ['bi-check-circle', 'Belum ada yang selesai', 'Tiket yang selesai atau ditutup muncul di sini.'],
            'semua' => ['bi-chat-left-text', 'Belum ada pesan', 'Pesan muncul di sini setelah pelanggan mengisi formulir kontak.'],
            'arsip' => ['bi-archive', 'Arsip masih kosong', 'Tiket yang diarsipkan atau ditandai spam disimpan di sini.'],
        ];
        $idHalaman = $messages->pluck('id')->map(fn ($i) => (string) $i)->all();
        $semuaTercentang = $idHalaman && ! array_diff($idHalaman, $pilih);
        $sasaranMuat = 'search,setTab,gotoPage,nextPage,previousPage,fStatus,fPrioritas,fKategori,fPetugas,fBatas,fDari,fSampai,urut,perPage,resetFilters';
        $warnaAvatar = fn ($nama) => ['#7c3aed', '#2563eb', '#16a34a', '#d97706', '#db2777', '#0891b2'][crc32((string) $nama) % 6];
        [$spamSurel, $spamNomor] = $kontakSpam;
        $pernahSpam = fn ($item) => (filled($item->email) && in_array($item->email, $spamSurel, true))
            || (filled($item->no_telp) && in_array($item->no_telp, $spamNomor, true));
    @endphp

    <div class="dsb" x-data="{ pilihan: @js($tampilan) }">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Pesan Pelanggan</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Pertanyaan yang masuk lewat formulir kontak — dijawab lewat WhatsApp atau surel.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <button type="button" class="dsb-tombol is-lembut" wire:click="unduhExcel" wire:loading.attr="disabled" wire:target="unduhExcel">
                    <span wire:loading.remove wire:target="unduhExcel" class="pp-isi-tombol"><i class="bi bi-file-earmark-excel"></i><span>Excel</span></span>
                    <span wire:loading.inline-flex wire:target="unduhExcel" class="pp-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                <button type="button" class="dsb-tombol is-lembut" wire:click="unduhPdf" wire:loading.attr="disabled" wire:target="unduhPdf">
                    <span wire:loading.remove wire:target="unduhPdf" class="pp-isi-tombol"><i class="bi bi-file-earmark-pdf"></i><span>PDF</span></span>
                    <span wire:loading.inline-flex wire:target="unduhPdf" class="pp-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
            </div>
        </header>

        {{-- ================== TAB ANTREAN ================== --}}
        <nav class="pp-status" aria-label="Saring menurut keadaan tiket">
            @foreach ($kartuTab as $nilai => [$label, $ikon, $warna])
                <button type="button" class="pp-status-btn {{ $tab === $nilai ? 'is-aktif' : '' }} {{ $nilai === 'baru' && 0 < $tabCounts['baru'] ? 'is-perlu' : '' }}"
                    style="--c: {{ $warna }}" wire:click="setTab('{{ $nilai }}')" aria-pressed="{{ $tab === $nilai ? 'true' : 'false' }}">
                    <span class="pp-status-ikon"><i class="bi {{ $ikon }}"></i></span>
                    <span class="pp-status-teks"><b>{{ $tabCounts[$nilai] }}</b><span>{{ $label }}</span></span>
                </button>
            @endforeach
        </nav>

        {{-- ================== RINGKASAN ANTREAN ================== --}}
        <section class="dsb-kartu pp-ringkas">
            <div class="dsb-kartu-isi pp-ringkas-isi">
                {{-- Tiap blok sekaligus pintasan saringan: angkanya memang untuk
                     ditindaklanjuti, jadi tidak perlu cari-cari lagi di menu Saring. --}}
                <button type="button" class="pp-ringkas-blok" wire:click="sorotBelumDibaca" title="Lihat pesan yang belum dibaca, terlama dulu">
                    <span class="pp-ringkas-ikon {{ $tertua ? 'is-tunggu' : 'is-aman' }}">
                        <i class="bi {{ $tertua ? 'bi-clock-history' : 'bi-emoji-smile' }}"></i>
                    </span>
                    <div>
                        @if ($tertua)
                            <p><b>{{ $tertua->menungguTeks() }}</b> pesan terlama menunggu dibaca.</p>
                            <p class="pp-catatan-kecil">{{ $tertua->ticket }} · {{ $tertua->name }}</p>
                        @else
                            <p><b>Bersih</b> — tidak ada pesan yang menunggu dibaca.</p>
                            <p class="pp-catatan-kecil">Pesan baru akan muncul di tab "Belum dibaca".</p>
                        @endif
                    </div>
                </button>
                <button type="button" class="pp-ringkas-blok" wire:click="sorotLewatBatas" title="Lihat tiket yang lewat batas waktu">
                    <span class="pp-ringkas-ikon {{ $lewatBatas ? 'is-mendesak' : 'is-aman' }}">
                        <i class="bi {{ $lewatBatas ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' }}"></i>
                    </span>
                    <div>
                        <p><b>{{ $lewatBatas }}</b> tiket lewat batas waktu membalas.</p>
                        <p class="pp-catatan-kecil">{{ $mendesak }} tiket prioritas tinggi/mendesak masih berjalan.</p>
                    </div>
                </button>
                <button type="button" class="pp-ringkas-blok" wire:click="sorotPekanIni" title="Lihat pesan 7 hari terakhir">
                    <span class="pp-ringkas-ikon is-aman" style="background: #eef2ff; color: #4338ca;"><i class="bi bi-graph-up"></i></span>
                    <div>
                        <p><b>{{ $masukPekanIni }}</b> pesan masuk dalam 7 hari terakhir.</p>
                        <p class="pp-catatan-kecil">
                            @if ($rataResponJam !== null)
                                Rata-rata dibalas {{ $rataResponJam }} jam (30 hari terakhir).
                            @else
                                Belum ada balasan tercatat 30 hari terakhir.
                            @endif
                        </p>
                    </div>
                </button>
            </div>
        </section>

        {{-- ================== SEBARAN TOPIK ================== --}}
        @if ($sebaranTopik)
            <section class="dsb-kartu pp-topik">
                <div class="dsb-kartu-isi">
                    <div class="pp-topik-kepala">
                        <span class="pp-detail-label" style="margin: 0;">Topik 30 hari terakhir</span>
                        @if ($tanpaTopik)
                            <span class="pp-catatan-kecil" style="margin: 0 !important;">{{ $tanpaTopik }} tiket belum diberi topik</span>
                        @endif
                    </div>
                    <div class="pp-topik-baris">
                        @foreach ($sebaranTopik as $t)
                            <button type="button" class="pp-topik-item {{ $fKategori === $t['kunci'] ? 'is-aktif' : '' }}"
                                wire:click="sorotTopik('{{ $t['kunci'] }}')" title="Saring tiket bertopik {{ $t['label'] }}">
                                <span class="pp-topik-label">{{ $t['label'] }}<b>{{ $t['jumlah'] }}</b></span>
                                <span class="pp-topik-bar"><span style="width: {{ max(4, $t['persen']) }}%"></span></span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== CARI & SARING ================== --}}
        <section class="dsb-kartu pp-saring" x-data="{ buka: @js($this->adaSaring) }">
            <div class="dsb-kartu-isi pp-saring-isi">
                <div class="dsb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="search" placeholder="Cari tiket, nama, email, nomor, atau isi pesan…" aria-label="Cari pesan">
                    @if ($search)
                        <button type="button" class="dsb-cari-hapus" wire:click="$set('search', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>

                <div class="pp-saklar {{ $tampilan === 'tabel' ? 'is-tabel' : '' }}" role="group" aria-label="Bentuk tampilan daftar"
                    x-bind:class="pilihan === 'tabel' ? 'is-tabel' : ''">
                    <span class="pp-saklar-pil" aria-hidden="true"></span>
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

                <select class="dsb-isian pp-pilih" wire:model.live="urut" aria-label="Urutkan">
                    <option value="baru" @selected($urut === 'baru')>Terbaru</option>
                    <option value="lama" @selected($urut === 'lama')>Paling lama menunggu</option>
                    <option value="prioritas" @selected($urut === 'prioritas')>Prioritas tertinggi</option>
                    <option value="nama" @selected($urut === 'nama')>Nama A-Z</option>
                    <option value="nama-turun" @selected($urut === 'nama-turun')>Nama Z-A</option>
                    <option value="status" @selected($urut === 'status')>Status</option>
                    <option value="topik" @selected($urut === 'topik')>Topik</option>
                    <option value="petugas" @selected($urut === 'petugas')>Petugas</option>
                </select>

                <select class="dsb-isian pp-pilih is-sempit" wire:model.live="perPage" aria-label="Jumlah per halaman">
                    @foreach ([12, 24, 48] as $n)
                        <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}/halaman</option>
                    @endforeach
                </select>

                <button type="button" class="pp-btn {{ $this->adaSaring ? 'is-aktif' : '' }}" x-on:click="buka = !buka" aria-label="Saringan lanjutan">
                    <i class="bi bi-funnel"></i><span>Saring</span>
                    @if ($this->adaSaring)<span class="pp-saring-titik"></span>@endif
                </button>

                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="{{ $sasaranMuat }}">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
                <span class="pp-pintasan" aria-hidden="true"><kbd>/</kbd> cari · <kbd>t</kbd> ganti tampilan</span>
            </div>

            @if ($pilih)
                <p class="pp-ekspor-ket"><i class="bi bi-info-circle"></i> Unduhan akan berisi {{ count($pilih) }} pesan yang dicentang saja.</p>
            @endif

            @if ($this->chipSaring)
                <div class="pp-chip-saring">
                    @foreach ($this->chipSaring as $chip)
                        <button type="button" class="pp-chip-lepas" wire:click="lepasSaring('{{ $chip['nama'] }}')" title="Lepas saringan ini">
                            {{ $chip['label'] }}<i class="bi bi-x-lg"></i>
                        </button>
                    @endforeach
                    <button type="button" class="pp-chip-lepas is-semua" wire:click="resetFilters">Bersihkan semua</button>
                </div>
            @endif

            <div class="pp-saring-lanjut" x-show="buka" x-collapse x-cloak>
                <div class="pp-saring-baris">
                    <label class="pp-saring-medan">
                        <span>Status</span>
                        <select class="dsb-isian" wire:model.live="fStatus">
                            <option value="">Semua status</option>
                            @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::STATUS as $nilai => $label)
                                <option value="{{ $nilai }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="pp-saring-medan">
                        <span>Prioritas</span>
                        <select class="dsb-isian" wire:model.live="fPrioritas">
                            <option value="">Semua prioritas</option>
                            @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::PRIORITAS as $nilai => $label)
                                <option value="{{ $nilai }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="pp-saring-medan">
                        <span>Topik</span>
                        <select class="dsb-isian" wire:model.live="fKategori">
                            <option value="">Semua topik</option>
                            @foreach ($kategoriDaftar as $nilai => $label)
                                <option value="{{ $nilai }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="pp-saring-medan">
                        <span>Petugas</span>
                        <select class="dsb-isian" wire:model.live="fPetugas">
                            <option value="">Semua petugas</option>
                            <option value="saya">Tiket saya</option>
                            @foreach ($daftarPetugas as $orang)
                                <option value="{{ $orang->id }}">{{ $orang->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="pp-saring-medan">
                        <span>Tindak lanjut</span>
                        <select class="dsb-isian" wire:model.live="fBatas">
                            <option value="">Semua tiket</option>
                            <option value="lewat">Lewat batas waktu</option>
                            <option value="belum">Belum dibalas</option>
                        </select>
                    </label>
                    @if ($arsip)
                        <label class="pp-saring-medan">
                            <span>Isi arsip</span>
                            <select class="dsb-isian" wire:model.live="fSpam">
                                <option value="">Semua isi arsip</option>
                                <option value="spam">Hanya spam</option>
                                <option value="biasa">Tanpa spam</option>
                            </select>
                        </label>
                    @else
                        <label class="pp-saring-medan">
                            <span>Pengirim</span>
                            <select class="dsb-isian" wire:model.live="fCuriga">
                                <option value="">Semua pengirim</option>
                                <option value="1">Pernah kirim spam</option>
                            </select>
                        </label>
                    @endif
                    <label class="pp-saring-medan">
                        <span>Masuk dari</span>
                        <input type="date" class="dsb-isian" wire:model.live="fDari" max="{{ now()->toDateString() }}">
                    </label>
                    <label class="pp-saring-medan">
                        <span>Sampai</span>
                        <input type="date" class="dsb-isian" wire:model.live="fSampai" max="{{ now()->toDateString() }}">
                    </label>
                </div>
            </div>
        </section>

        {{-- ================== BILAH AKSI MASSAL ================== --}}
        @if ($pilih)
            <div class="pp-massal" role="region" aria-label="Aksi massal">
                <span class="pp-massal-jumlah"><b>{{ count($pilih) }}</b> dipilih</span>
                <div class="pp-massal-tombol">
                    @if ($arsip)
                        @if ($bolehHapus)
                            <button type="button" class="pp-btn pp-konfirmasi" data-action="pulihkanTerpilih" data-icon="question"
                                data-title="Kembalikan {{ count($pilih) }} tiket?" data-text="Tiket kembali muncul di daftar aktif." data-confirm="Ya, kembalikan">
                                <i class="bi bi-arrow-counterclockwise"></i><span>Kembalikan</span>
                            </button>
                            <button type="button" class="pp-btn is-bahaya pp-konfirmasi" data-action="hapusPermanenTerpilih" data-icon="warning"
                                data-title="Hapus permanen {{ count($pilih) }} tiket?" data-text="Isi pesan dan linimasanya hilang selamanya dan tidak bisa dikembalikan." data-confirm="Ya, hapus permanen">
                                <i class="bi bi-trash3"></i><span>Hapus permanen</span>
                            </button>
                        @endif
                    @else
                        @if ($bolehUbah)
                            <button type="button" class="pp-btn pp-konfirmasi" data-action="tandaiDibacaTerpilih" data-icon="question"
                                data-title="Tandai {{ count($pilih) }} pesan sudah dibaca?" data-text="Badge helpdesk ikut berkurang." data-confirm="Ya, tandai">
                                <i class="bi bi-envelope-open"></i><span>Tandai dibaca</span>
                            </button>
                            <select class="dsb-isian pp-pilih is-sempit" wire:change="statusTerpilih($event.target.value); $event.target.value = ''" aria-label="Ubah status terpilih">
                                <option value="">Ubah status…</option>
                                @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::STATUS as $nilai => $label)
                                    <option value="{{ $nilai }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <select class="dsb-isian pp-pilih is-sempit" wire:change="prioritasTerpilih($event.target.value); $event.target.value = ''" aria-label="Ubah prioritas terpilih">
                                <option value="">Ubah prioritas…</option>
                                @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::PRIORITAS as $nilai => $label)
                                    <option value="{{ $nilai }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <select class="dsb-isian pp-pilih is-sempit" wire:change="kategoriTerpilih($event.target.value); $event.target.value = ''" aria-label="Beri topik tiket terpilih">
                                <option value="">Beri topik…</option>
                                @foreach ($kategoriDaftar as $nilai => $label)
                                    <option value="{{ $nilai }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <select class="dsb-isian pp-pilih is-sempit" wire:change="tugaskanTerpilih($event.target.value); $event.target.value = ''" aria-label="Serahkan tiket terpilih">
                                <option value="">Serahkan ke…</option>
                                @foreach ($daftarPetugas as $orang)
                                    <option value="{{ $orang->id }}">{{ $orang->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        @if ($bolehHapus)
                            <button type="button" class="pp-btn pp-konfirmasi" data-action="spamTerpilih" data-icon="warning"
                                data-title="Tandai {{ count($pilih) }} pesan sebagai spam?" data-text="Pesan ditutup dan dipindahkan ke arsip." data-confirm="Ya, spam">
                                <i class="bi bi-shield-exclamation"></i><span>Spam</span>
                            </button>
                            <button type="button" class="pp-btn is-bahaya pp-konfirmasi" data-action="hapusTerpilih" data-icon="warning"
                                data-title="Arsipkan {{ count($pilih) }} pesan?" data-text="Pesan yang belum dibaca dilewati. Tiket yang diarsipkan masih bisa dikembalikan." data-confirm="Ya, arsipkan">
                                <i class="bi bi-archive"></i><span>Arsipkan</span>
                            </button>
                        @endif
                    @endif
                    <button type="button" class="pp-btn" wire:click="lepasPilih"><i class="bi bi-x"></i><span>Lepas</span></button>
                </div>
            </div>
        @endif

        {{-- Kerangka pemuatan: tanpa ini kartu lama cuma meredup dan sekilas
             tampak seolah tidak ada yang berubah. --}}
        <div class="pp-kerangka" wire:loading.grid wire:target="{{ $sasaranMuat }}">
            @for ($i = 0; $i < 6; $i++)
                <div class="pp-kerangka-kartu">
                    <div class="pp-kerangka-kepala"><span class="pp-tulang is-bulat"></span><span class="pp-tulang" style="width: 55%"></span></div>
                    <span class="pp-tulang" style="width: 100%; height: 54px; margin-top: 12px;"></span>
                    <span class="pp-tulang" style="width: 42%; margin-top: 10px;"></span>
                </div>
            @endfor
        </div>

        {{-- ================== DAFTAR PESAN ================== --}}
        <section wire:loading.class="pp-sembunyi" wire:target="{{ $sasaranMuat }}">
          <div class="pp-wadah">
            @if ($messages->isEmpty())
                @php
                    [$kIkon, $kJudul, $kKet] = match (true) {
                        $arsip && $fSpam === 'spam' => ['bi-shield-check', 'Tidak ada spam di arsip', 'Pesan yang Anda tandai spam akan muncul di sini.'],
                        $arsip && $fSpam === 'biasa' => ['bi-archive', 'Arsip tanpa spam masih kosong', 'Tiket yang diarsipkan (bukan spam) muncul di sini.'],
                        (bool) ($search || $this->adaSaring) => ['bi-funnel', 'Tidak ada pesan yang cocok', 'Coba ubah kata kunci atau bersihkan saringan.'],
                        default => $kosong[$tab],
                    };
                @endphp
                <div class="dsb-kartu">
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi {{ $kIkon }}"></i></span>
                        <p class="dsb-kosong-judul">{{ $kJudul }}</p>
                        <p class="dsb-kosong-ket">{{ $kKet }}</p>
                    </div>
                </div>
            @else
                <div class="pp-pilih-semua">
                    <label class="pp-centang">
                        <input type="checkbox" @checked($semuaTercentang) wire:click="pilihHalaman({{ \Illuminate\Support\Js::from($idHalaman) }})">
                        <span>Pilih semua di halaman ini</span>
                    </label>
                </div>

                {{-- Bentuk KARTU dan TABEL memakai markup yang SAMA; yang berubah
                     hanya kelasnya, jadi kartunya bergeser posisi alih-alih
                     dibongkar lalu dibangun ulang. Itu yang bikin pergantiannya
                     mulus (pola yang sama dipakai Moderasi Ulasan Produk). --}}
                <div class="pp-daftar {{ $tampilan === 'tabel' ? 'is-tabel' : '' }}"
                    x-bind:class="pilihan === 'tabel' ? 'is-tabel' : ''">
                    {{-- Tiga label yang benar-benar sejajar dengan isi barisnya.
                         Urutan lain (status, topik, petugas) ada di menu Urutkan
                         supaya kepala ini tidak menjanjikan kolom yang tak ada. --}}
                    <div class="pp-tabel-kepala">
                        <button type="button" class="{{ $this->arahUrut('nama') ? 'is-aktif' : '' }}" wire:click="urutkanKolom('nama')">
                            Pengirim &amp; status · {{ $this->arahUrut('nama') === 'turun' ? 'Z-A' : 'A-Z' }}
                            <i class="bi {{ match ($this->arahUrut('nama')) { 'naik' => 'bi-sort-alpha-down', 'turun' => 'bi-sort-alpha-up', default => 'bi-chevron-expand' } }}"></i>
                        </button>
                        <button type="button" class="{{ in_array($urut, ['baru', 'lama'], true) ? 'is-aktif' : '' }}" wire:click="$set('urut', '{{ $urut === 'baru' ? 'lama' : 'baru' }}')">
                            Pesan &amp; penanda · {{ $urut === 'lama' ? 'terlama' : 'terbaru' }}
                            <i class="bi {{ $urut === 'lama' ? 'bi-sort-down-alt' : ($urut === 'baru' ? 'bi-sort-up-alt' : 'bi-chevron-expand') }}"></i>
                        </button>
                        <span>Tindakan</span>
                    </div>

                    <div class="pp-rak">
                        @foreach ($messages as $item)
                            @php
                                [$stLabel, $stLencana, $stWarna] = $item->tampilanStatus();
                                [$prLabel, $prKelas, $prWarna] = $item->tampilanPrioritas();
                            @endphp
                            <article class="pp-kartu {{ in_array((string) $item->id, $pilih, true) ? 'is-dipilih' : '' }} {{ $item->belumDibaca() ? 'is-baru' : '' }}"
                                style="--c: {{ $item->belumDibaca() ? '#d97706' : $stWarna }}" wire:key="pesan-{{ $item->id }}">
                                <div class="pp-kepala">
                                    <label class="pp-centang is-kartu" title="Pilih untuk aksi massal">
                                        <input type="checkbox" value="{{ $item->id }}" wire:model.live="pilih">
                                    </label>
                                    <a href="{{ route('admin.customer-message.detail', $item->id) }}" wire:navigate class="pp-avatar" style="--av: {{ $warnaAvatar($item->name) }}" title="Buka detail pesan">
                                        {{ mb_strtoupper(mb_substr(trim((string) $item->name), 0, 1)) ?: '?' }}
                                    </a>
                                    <div class="pp-kepala-teks">
                                        <p class="pp-nama">{!! \App\Support\SorotKata::pada($item->name, $search) !!}</p>
                                        <span class="pp-tiket"><i class="bi bi-ticket-perforated"></i>{!! \App\Support\SorotKata::pada($item->ticket, $search) !!}</span>
                                        <span class="pp-kontak">{!! \App\Support\SorotKata::pada($item->email ?: $item->no_telp ?: 'Tanpa kontak', $search) !!}</span>
                                    </div>
                                    <span class="dsb-lencana {{ $stLencana }}">{{ $stLabel }}</span>
                                </div>

                                <div class="pp-isi">
                                    <div class="pp-penanda">
                                        @if ($item->is_spam)
                                            <span class="pp-tanda is-spam"><i class="bi bi-shield-exclamation"></i>Spam</span>
                                        @endif
                                        @if ($item->belumDibaca())
                                            <span class="pp-tanda is-baru"><i class="bi bi-envelope-exclamation"></i>Belum dibaca</span>
                                        @endif
                                        <span class="pp-tanda {{ $prKelas }}"><i class="bi bi-flag-fill"></i>{{ $prLabel }}</span>
                                        @if ($item->lewatBatas())
                                            <span class="pp-tanda is-lewat"><i class="bi bi-alarm"></i>Lewat batas {{ $item->batasJam() }} jam</span>
                                        @elseif ($item->sudahDibalas())
                                            <span class="pp-tanda is-dibalas"><i class="bi bi-check2-all"></i>Sudah dibalas</span>
                                        @endif
                                        @if ($item->labelKategori())
                                            <span class="pp-tanda is-topik"><i class="bi bi-tag-fill"></i>{{ $item->labelKategori() }}</span>
                                        @endif
                                        @if ($item->petugas)
                                            <span class="pp-tanda is-petugas"><i class="bi bi-person-check-fill"></i>{{ $item->petugas->name }}</span>
                                        @endif
                                        @if ($item->belumDibaca() && $item->menungguJam() >= 3)
                                            <span class="pp-tanda is-lama"><i class="bi bi-clock-history"></i>Menunggu {{ $item->menungguTeks() }}</span>
                                        @endif
                                        @if ($pernahSpam($item) && ! $item->is_spam)
                                            <span class="pp-tanda is-spam"><i class="bi bi-shield-exclamation"></i>Pengirim pernah spam</span>
                                        @endif
                                        @if ($item->lampiran_count)
                                            <span class="pp-tanda is-rendah"><i class="bi bi-paperclip"></i>{{ $item->lampiran_count }} lampiran</span>
                                        @endif
                                    </div>

                                    <p class="pp-pesan">{!! \App\Support\SorotKata::pada(\Illuminate\Support\Str::limit($item->message, 200), $search) !!}</p>
                                    <div class="pp-waktu">Masuk {{ $item->created_at?->locale('id')->diffForHumans() }}</div>
                                </div>

                                <div class="pp-aksi">
                                    @if ($arsip)
                                        <div class="pp-aksi-utama">
                                            @if ($bolehHapus)
                                                <button type="button" class="pp-btn is-utama pp-konfirmasi" data-action="pulihkan" data-arg="{{ $item->id }}" data-icon="question"
                                                    data-title="Kembalikan tiket ini?" data-text="{{ $item->ticket }} kembali ke daftar aktif." data-confirm="Ya, kembalikan">
                                                    <i class="bi bi-arrow-counterclockwise"></i><span>Kembalikan</span>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="pp-aksi-lain">
                                            @if ($bolehHapus)
                                                <button type="button" class="pp-btn pp-btn-ikon is-bahaya pp-konfirmasi" data-action="hapusPermanen" data-arg="{{ $item->id }}" data-icon="warning"
                                                    data-title="Hapus permanen?" data-text="{{ $item->ticket }} beserta linimasanya hilang selamanya." data-confirm="Ya, hapus permanen"
                                                    title="Hapus permanen" aria-label="Hapus permanen"><i class="bi bi-trash3"></i></button>
                                            @endif
                                        </div>
                                    @else
                                        <div class="pp-aksi-utama">
                                            <a href="{{ route('admin.customer-message.detail', $item->id) }}" wire:navigate class="pp-btn is-utama">
                                                <i class="bi bi-envelope-open"></i><span>Buka & balas</span>
                                            </a>
                                            @if ($item->belumDibaca())
                                                <button type="button" class="pp-btn" wire:click="tandaiDibaca('{{ $item->id }}')" title="Tandai sudah dibaca tanpa membukanya">
                                                    <i class="bi bi-check2"></i><span>Tandai dibaca</span>
                                                </button>
                                            @elseif ($bolehUbah && $item->assigned_to !== auth()->id())
                                                <button type="button" class="pp-btn" wire:click="ambilTiket('{{ $item->id }}')" title="Jadikan tiket ini tanggung jawab Anda">
                                                    <i class="bi bi-person-check"></i><span>Ambil tiket</span>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="pp-aksi-lain">
                                            @if ($wa = $item->tautanWa('Halo '.$item->name.', terima kasih sudah menghubungi Phoenix Digital (tiket '.$item->ticket.'). '))
                                                <a href="{{ $wa }}" target="_blank" rel="noopener" class="pp-btn pp-btn-ikon is-wa" title="Balas lewat WhatsApp" aria-label="Balas lewat WhatsApp"><i class="bi bi-whatsapp"></i></a>
                                            @endif
                                            @if ($surel = $item->tautanEmail())
                                                <a href="{{ $surel }}" class="pp-btn pp-btn-ikon" title="Balas lewat surel" aria-label="Balas lewat surel"><i class="bi bi-envelope"></i></a>
                                            @endif
                                            @if ($bolehHapus)
                                                <button type="button" class="pp-btn pp-btn-ikon pp-konfirmasi" data-action="tandaiSpam" data-arg="{{ $item->id }}" data-icon="warning"
                                                    data-title="Tandai spam?" data-text="{{ $item->ticket }} ditutup dan dipindahkan ke arsip." data-confirm="Ya, spam"
                                                    title="Tandai spam" aria-label="Tandai spam"><i class="bi bi-shield-exclamation"></i></button>
                                                <button type="button" class="pp-btn pp-btn-ikon is-bahaya pp-hapus" data-id="{{ $item->id }}" data-nama="{{ $item->name }}"
                                                    title="{{ $item->belumDibaca() ? 'Baca dulu sebelum bisa diarsipkan' : 'Arsipkan' }}" aria-label="Arsipkan pesan"><i class="bi bi-archive"></i></button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                @if ($messages->hasPages())
                    <div class="pp-halaman">{{ $messages->links('vendor.pagination') }}</div>
                @endif
            @endif
          </div>
        </section>
    </div>

    @include('livewire.layout.sweetalert')

    @push('scripts')
        <script>
            // Dipasang SEKALI di dokumen (bukan body): wire:navigate mengganti <body>.
            if (!window.__pesanPelangganTerpasang) {
                window.__pesanPelangganTerpasang = true;
                const gaya = {
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                    buttonsStyling: false,
                };
                const panggil = (el, metode, arg) => {
                    const komponen = el.closest('[wire\\:id]');
                    if (!komponen) return;
                    const hidup = Livewire.find(komponen.getAttribute('wire:id'));
                    if (arg === undefined) hidup.call(metode); else hidup.call(metode, arg);
                };
                document.addEventListener('click', (e) => {
                    if (typeof Swal === 'undefined') return;

                    const tombol = e.target.closest('.pp-konfirmasi');
                    if (tombol) {
                        e.preventDefault();
                        Swal.fire({
                            title: tombol.dataset.title || 'Lanjutkan?', text: tombol.dataset.text || '',
                            icon: tombol.dataset.icon || 'question', showCancelButton: true,
                            confirmButtonText: tombol.dataset.confirm || 'Ya', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => { if (r.isConfirmed) panggil(tombol, tombol.dataset.action, tombol.dataset.arg); });
                        return;
                    }

                    const hapus = e.target.closest('.pp-hapus');
                    if (hapus) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Arsipkan pesan ini?',
                            text: 'Pesan dari ' + (hapus.dataset.nama || '') + ' dipindahkan ke arsip dan masih bisa dikembalikan.',
                            icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, arsipkan', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => { if (r.isConfirmed) panggil(hapus, 'delete', hapus.dataset.id); });
                    }
                });
                // Pintasan papan tik. Diabaikan saat sedang mengetik di isian mana pun
                // supaya "/" dan "t" tetap bisa ditulis di kotak pencarian.
                document.addEventListener('keydown', (e) => {
                    if (e.metaKey || e.ctrlKey || e.altKey) return;
                    const el = document.activeElement;
                    const mengetik = el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT' || el.isContentEditable);

                    if (e.key === '/' && !mengetik) {
                        const cari = document.querySelector('.pp-saring input[type="search"]');
                        if (cari) { e.preventDefault(); cari.focus(); cari.select(); }
                        return;
                    }

                    if (e.key === 'Escape' && el && el.matches('.pp-saring input[type="search"]')) {
                        el.blur();
                        return;
                    }

                    if ((e.key === 't' || e.key === 'T') && !mengetik) {
                        const saklar = document.querySelector('.pp-saklar button:not(.is-aktif)');
                        if (saklar) { e.preventDefault(); saklar.click(); }
                    }
                });

                window.addEventListener('CustomerMessage-deleted', () => {
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Beres', text: 'Tiket sudah dipindahkan.', icon: 'success', timer: 2000, showConfirmButton: false, ...gaya });
                });
                window.addEventListener('CustomerMessage-deleteError', (e) => {
                    const d = Array.isArray(e.detail) ? (e.detail[0] || {}) : (e.detail || {});
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Gagal', text: d.message || 'Pesan gagal diarsipkan.', icon: 'error', timer: 2800, showConfirmButton: false, ...gaya });
                });
            }
        </script>
    @endpush
</div>
