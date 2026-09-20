@section('title')
Pesan Pelanggan || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.message.partials.pesan-gaya')

    @php
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_customer_message');
        $kartuTab = [
            'baru' => ['Belum dibaca', 'bi-envelope-exclamation-fill', '#d97706'],
            'berjalan' => ['Masih berjalan', 'bi-hourglass-split', '#2563eb'],
            'selesai' => ['Selesai', 'bi-check-circle-fill', '#16a34a'],
            'semua' => ['Semua', 'bi-chat-left-text-fill', '#7c3aed'],
        ];
        $kosong = [
            'baru' => ['bi-inbox', 'Tidak ada pesan baru', 'Semua pesan pelanggan sudah dibaca.'],
            'berjalan' => ['bi-hourglass', 'Tidak ada yang berjalan', 'Semua tiket sudah selesai atau ditutup.'],
            'selesai' => ['bi-check-circle', 'Belum ada yang selesai', 'Tiket yang selesai atau ditutup muncul di sini.'],
            'semua' => ['bi-chat-left-text', 'Belum ada pesan', 'Pesan muncul di sini setelah pelanggan mengisi formulir kontak.'],
        ];
        $idHalaman = $messages->pluck('id')->map(fn ($i) => (string) $i)->all();
        $semuaTercentang = $idHalaman && ! array_diff($idHalaman, $pilih);
        $sasaranMuat = 'search,setTab,gotoPage,nextPage,previousPage,fStatus,fPrioritas,fDari,fSampai,urut,perPage,resetFilters';
        $warnaAvatar = fn ($nama) => ['#7c3aed', '#2563eb', '#16a34a', '#d97706', '#db2777', '#0891b2'][crc32((string) $nama) % 6];
    @endphp

    <div class="dsb">
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
                <button type="button" class="dsb-tombol" wire:click="unduhExcel" wire:loading.attr="disabled" wire:target="unduhExcel">
                    <span wire:loading.remove wire:target="unduhExcel" class="pp-isi-tombol"><i class="bi bi-file-earmark-excel"></i><span>Excel</span></span>
                    <span wire:loading.inline-flex wire:target="unduhExcel" class="pp-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                <button type="button" class="dsb-tombol" wire:click="unduhPdf" wire:loading.attr="disabled" wire:target="unduhPdf">
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
                <div class="pp-ringkas-blok">
                    <span class="pp-ringkas-ikon {{ $tertua ? 'is-tunggu' : 'is-aman' }}">
                        <i class="bi {{ $tertua ? 'bi-clock-history' : 'bi-emoji-smile' }}"></i>
                    </span>
                    <div>
                        @if ($tertua)
                            <p><b>{{ $tertua->menungguJam() }} jam</b> pesan terlama menunggu dibaca.</p>
                            <p class="pp-catatan-kecil">{{ $tertua->ticket }} · {{ $tertua->name }}</p>
                        @else
                            <p><b>Bersih</b> — tidak ada pesan yang menunggu dibaca.</p>
                            <p class="pp-catatan-kecil">Pesan baru akan muncul di tab "Belum dibaca".</p>
                        @endif
                    </div>
                </div>
                <div class="pp-ringkas-blok">
                    <span class="pp-ringkas-ikon {{ $mendesak ? 'is-mendesak' : 'is-aman' }}">
                        <i class="bi {{ $mendesak ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' }}"></i>
                    </span>
                    <div>
                        <p><b>{{ $mendesak }}</b> tiket prioritas tinggi/mendesak masih berjalan.</p>
                        <p class="pp-catatan-kecil">Urutkan menurut prioritas untuk menaikkannya ke atas.</p>
                    </div>
                </div>
            </div>
        </section>

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

                <select class="dsb-isian pp-pilih" wire:model.live="urut" aria-label="Urutkan">
                    <option value="baru" @selected($urut === 'baru')>Terbaru</option>
                    <option value="lama" @selected($urut === 'lama')>Paling lama menunggu</option>
                    <option value="prioritas" @selected($urut === 'prioritas')>Prioritas tertinggi</option>
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
                    @if ($bolehHapus)
                        <button type="button" class="pp-btn is-bahaya pp-konfirmasi" data-action="hapusTerpilih" data-icon="warning"
                            data-title="Hapus {{ count($pilih) }} pesan?" data-text="Pesan yang belum dibaca akan dilewati, sisanya dihapus permanen." data-confirm="Ya, hapus">
                            <i class="bi bi-trash3"></i><span>Hapus</span>
                        </button>
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

        {{-- ================== RAK PESAN ================== --}}
        <section wire:loading.class="pp-sembunyi" wire:target="{{ $sasaranMuat }}">
            @if ($messages->isEmpty())
                @php
                    [$kIkon, $kJudul, $kKet] = ($search || $this->adaSaring)
                        ? ['bi-funnel', 'Tidak ada pesan yang cocok', 'Coba ubah kata kunci atau bersihkan saringan.']
                        : $kosong[$tab];
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

                <div class="pp-rak">
                    @foreach ($messages as $item)
                        @php
                            [$stLabel, $stLencana, $stWarna] = $item->tampilanStatus();
                            [$prLabel, $prKelas, $prWarna] = $item->tampilanPrioritas();
                            $menunggu = $item->menungguJam();
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
                                    @if ($item->belumDibaca())
                                        <span class="pp-tanda is-baru"><i class="bi bi-envelope-exclamation"></i>Belum dibaca</span>
                                    @endif
                                    <span class="pp-tanda {{ $prKelas }}"><i class="bi bi-flag-fill"></i>{{ $prLabel }}</span>
                                    @if ($item->belumDibaca() && $menunggu >= 24)
                                        <span class="pp-tanda is-lama"><i class="bi bi-clock-history"></i>Menunggu {{ intdiv($menunggu, 24) }} hari</span>
                                    @elseif ($item->belumDibaca() && $menunggu >= 3)
                                        <span class="pp-tanda is-lama"><i class="bi bi-clock-history"></i>Menunggu {{ $menunggu }} jam</span>
                                    @endif
                                </div>

                                <p class="pp-pesan">{!! \App\Support\SorotKata::pada(\Illuminate\Support\Str::limit($item->message, 200), $search) !!}</p>
                                <div class="pp-waktu">Masuk {{ $item->created_at?->locale('id')->diffForHumans() }}</div>
                            </div>

                            <div class="pp-aksi">
                                <div class="pp-aksi-utama">
                                    <a href="{{ route('admin.customer-message.detail', $item->id) }}" wire:navigate class="pp-btn is-utama">
                                        <i class="bi bi-envelope-open"></i><span>Buka & balas</span>
                                    </a>
                                    @if ($item->belumDibaca())
                                        <button type="button" class="pp-btn" wire:click="tandaiDibaca('{{ $item->id }}')" title="Tandai sudah dibaca tanpa membukanya">
                                            <i class="bi bi-check2"></i><span>Tandai dibaca</span>
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
                                        <button type="button" class="pp-btn pp-btn-ikon is-bahaya pp-hapus" data-id="{{ $item->id }}" data-nama="{{ $item->name }}"
                                            title="{{ $item->belumDibaca() ? 'Baca dulu sebelum bisa dihapus' : 'Hapus' }}" aria-label="Hapus pesan"><i class="bi bi-trash3"></i></button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($messages->hasPages())
                    <div class="pp-halaman">{{ $messages->links('vendor.pagination') }}</div>
                @endif
            @endif
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
                            title: 'Hapus pesan ini?',
                            text: 'Pesan dari ' + (hapus.dataset.nama || '') + ' dihapus permanen.',
                            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', ...gaya,
                        }).then((r) => { if (r.isConfirmed) panggil(hapus, 'delete', hapus.dataset.id); });
                    }
                });
                window.addEventListener('CustomerMessage-deleted', () => {
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Terhapus', text: 'Pesan berhasil dihapus.', icon: 'success', timer: 2000, showConfirmButton: false, ...gaya });
                });
                window.addEventListener('CustomerMessage-deleteError', (e) => {
                    const d = Array.isArray(e.detail) ? (e.detail[0] || {}) : (e.detail || {});
                    if (typeof Swal !== 'undefined') Swal.fire({ title: 'Gagal', text: d.message || 'Pesan gagal dihapus.', icon: 'error', timer: 2800, showConfirmButton: false, ...gaya });
                });
            }
        </script>
    @endpush
</div>
