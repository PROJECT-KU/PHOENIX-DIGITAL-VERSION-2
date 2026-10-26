@section('title')
Vendor || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         master harga dan daftar RAB. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $jumlahData = (int) ($meta['total'] ?? count($daftar));
        $adaSaringan = $this->adaSaringan();
        $formTerbuka = $tambah || $sunting;
        $jenisKini = trim((string) ($isian['jenis'] ?? ''));

        /*
         | Warna & ikon per jenis vendor. Sama alasannya dengan kategori di
         | master harga: lencana abu yang semuanya sama menuntut tulisannya
         | dibaca satu per satu, padahal yang dicari mata cuma "mana yang
         | armada".
         */
        $rupaJenis = [
            'Armada' => ['#0369a1', 'bi-bus-front'],
            'Akomodasi' => ['#6d28d9', 'bi-building'],
            'Konsumsi' => ['#e0912f', 'bi-cup-hot'],
            'Tiket' => ['#1f7a44', 'bi-ticket-perforated'],
            'Pemandu' => ['#0e7490', 'bi-person-badge'],
        ];
        $rupaDari = fn ($j) => $rupaJenis[$j] ?? ['#64748b', 'bi-shop'];
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Vendor</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Dari siapa harga dibeli, dan ke nomor mana meneleponnya.</span>
                    <span class="d-block">Nomor yang berganti cukup diubah di sini — seluruh harga yang memakainya ikut benar.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.master-harga') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #b45309">
                    <i class="bi bi-tags"></i><span>Master Harga</span>
                </a>
                <a href="{{ route('admin.orcha.rab') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                    <i class="bi bi-calculator"></i><span>RAB &amp; Itinerary</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="vd-saring">
                            <div class="vd-medan-cari">
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Cari nama vendor atau nomornya…',
                                ])
                            </div>

                            <button type="button" wire:click="bukaTambah" class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                                <i class="bi bi-plus-lg"></i><span>Vendor Baru</span>
                            </button>

                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut"
                                    style="--ikon: #b45309" title="Kembali menampilkan seluruh vendor">
                                    <i class="bi bi-x-circle"></i><span>Tampilkan semua</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== FORMULIR ================== --}}
        @if ($formTerbuka)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #0369a1"><i class="bi bi-shop"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">{{ $sunting ? 'Ubah Vendor' : 'Vendor Baru' }}</h3>
                                    <span class="dsb-kartu-sub">
                                        Nomornya dibaca <strong>hidup</strong> — diubah di sini, RAB lama pun ikut menunjuk nomor yang baru.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            <div class="vd-medan-rak">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="vd-nama">Nama vendor <span class="dsb-wajib">*</span></label>
                                    <input id="vd-nama" type="text" wire:model="isian.nama" maxlength="120"
                                        class="dsb-isian @error('isian.nama') is-galat @enderror"
                                        placeholder="PO Sumber Jaya">
                                    @error('isian.nama')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="vd-kontak">
                                        Kontak
                                        <span class="dsb-label-ket">boleh sekalian nama orangnya</span>
                                    </label>
                                    <input id="vd-kontak" type="text" wire:model="isian.kontak" maxlength="120"
                                        class="dsb-isian @error('isian.kontak') is-galat @enderror"
                                        placeholder="081234000111 (Pak Budi)">
                                    @error('isian.kontak')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan is-lebar">
                                    <label class="dsb-label">
                                        Jenis
                                        <span class="dsb-label-ket">memasok apa</span>
                                    </label>
                                    {{-- Pilihan cepat, bukan kotak terkunci: vendor nyata
                                         tidak selalu muat di lima kotak, dan menolak yang
                                         keenam hanya membuat admin mengetiknya di kolom
                                         lain yang bukan tempatnya. --}}
                                    <div class="vd-jenis">
                                        @foreach ($pilihanJenis as $j)
                                            @php([$warnaJ, $ikonJ] = $rupaDari($j))
                                            <button type="button" wire:click="pakaiJenis('{{ $j }}')"
                                                class="vd-jenis-chip {{ $jenisKini === $j ? 'is-aktif' : '' }}"
                                                style="--c: {{ $warnaJ }}">
                                                <i class="bi {{ $ikonJ }}"></i>{{ $j }}
                                            </button>
                                        @endforeach
                                        <input type="text" wire:model.live.debounce.400ms="isian.jenis" maxlength="30"
                                            class="dsb-isian vd-jenis-isi" placeholder="atau ketik sendiri">
                                    </div>
                                </div>

                                <div class="dsb-medan is-lebar">
                                    <label class="dsb-label" for="vd-catatan">
                                        Catatan
                                        <span class="dsb-label-ket">tidak pernah ikut ke PDF pelanggan</span>
                                    </label>
                                    <input id="vd-catatan" type="text" wire:model="isian.catatan" maxlength="300"
                                        class="dsb-isian" placeholder="Alamat pool, syarat DP, jam operasional…">
                                </div>
                            </div>

                            <div class="vd-sakelar">
                                <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }}">
                                    <span class="rupa"><i class="bi {{ $aktif ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i></span>
                                    <span class="isi">
                                        <span class="judul">{{ $aktif ? 'Aktif' : 'Dimatikan' }}</span>
                                        <span class="ket">
                                            {{ $aktif
                                                ? 'Muncul sebagai pilihan saat mengisi master harga.'
                                                : 'Tersimpan, tetapi tidak ditawarkan lagi. Harga yang sudah memakainya tetap menunjuk kontaknya.' }}
                                        </span>
                                    </span>
                                    <span class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                                    </span>
                                </label>
                            </div>

                            <div class="vd-kaki">
                                <button type="button" wire:click="tutup" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>
                                <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                                    class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                                    <i class="bi bi-check2-circle" wire:loading.remove wire:target="simpan"></i>
                                    <span class="dsb-putar" wire:loading wire:target="simpan"></span>
                                    <span>Simpan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat"
                    wire:target="cari,bersihkanSaringan,bersihkanCari,keHalaman">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0369a1"><i class="bi bi-shop"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Daftar vendor</h3>
                                <span class="dsb-kartu-sub">
                                    Nomornya ikut muncul di rincian biaya RAB dan di PDF internal — tidak perlu dicari lagi saat menyiapkan keberangkatan.
                                </span>
                            </div>
                        </div>
                        <span class="dsb-chip"><i class="bi bi-list-check"></i>{{ number_format($jumlahData, 0, ',', '.') }} vendor</span>
                    </div>

                    @if ($daftar === [])
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-shop"></i></span>
                                <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada vendor' }}</p>
                                <p class="dsb-kosong-ket">
                                    {{ $adaSaringan
                                        ? 'Coba kata kunci lain — nama vendornya atau sebagian nomornya.'
                                        : 'Mulai dari pemilik armada yang paling sering dipakai.' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel vd-tabel">
                                <thead>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Jenis</th>
                                        <th>Kontak</th>
                                        <th class="text-end">Dipakai</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php([$warnaJ, $ikonJ] = $rupaDari($baris['jenis']))
                                        <tr wire:key="vd-{{ $baris['id'] }}">
                                            <td data-judul="Vendor">
                                                <span class="vd-utama">
                                                    <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>
                                                    @if ($baris['catatan'])
                                                        <span class="dsb-tabel-meta vd-catatan">{{ $baris['catatan'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Jenis">
                                                @if ($baris['jenis'])
                                                    <span class="dsb-lencana" style="--c: {{ $warnaJ }}">
                                                        <i class="bi {{ $ikonJ }}"></i>{{ $baris['jenis'] }}
                                                    </span>
                                                @else
                                                    <span class="dsb-tabel-meta">—</span>
                                                @endif
                                            </td>
                                            <td data-judul="Kontak">
                                                @if ($baris['kontak'])
                                                    <span class="vd-kontak">
                                                        <i class="bi bi-telephone"></i>{{ $baris['kontak'] }}
                                                    </span>
                                                @else
                                                    {{-- Vendor tanpa nomor adalah vendor yang
                                                         tetap harus dicari saat dibutuhkan —
                                                         jadi ketiadaannya ditandai, bukan
                                                         dibiarkan kosong. --}}
                                                    <span class="vd-kosong">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>belum ada nomor
                                                    </span>
                                                @endif
                                            </td>
                                            <td data-judul="Dipakai" class="text-end">
                                                <span class="dsb-tabel-angka">{{ $baris['jumlah_harga'] }} harga</span>
                                            </td>
                                            <td data-judul="Status">
                                                @if ($baris['aktif'])
                                                    <span class="dsb-lencana" style="--c: #1f7a44"><i class="bi bi-check-circle"></i>Aktif</span>
                                                @else
                                                    <span class="dsb-lencana" style="--c: #64748b"><i class="bi bi-pause-circle"></i>Dimatikan</span>
                                                @endif
                                            </td>
                                            <td data-judul="Aksi" class="text-end">
                                                <span class="dsb-tabel-aksi">
                                                    <button type="button" class="dsb-tabel-btn" style="--c: #4f46e5"
                                                        wire:click="bukaSunting({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                                        title="Ubah vendor ini">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi" style="--c: #be123c"
                                                        data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                        data-title="Hapus {{ $baris['nama'] }}?"
                                                        data-text="{{ $baris['jumlah_harga'] }} harga akan kehilangan kontaknya, tetapi harganya sendiri tetap ada."
                                                        data-confirm="Ya, hapus" data-icon="warning"
                                                        title="Hapus vendor ini">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="vd-halaman">
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .vd-saring { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .vd-saring > .vd-medan-cari { flex: 1 1 260px; min-width: 0; }
        .vd-saring .dsb-tombol { flex: 0 0 auto; }

        /* Kotak cari bersama memakai .form-control Bootstrap; yang disamakan
           rupanya saja — markup & perilakunya tetap milik partial. */
        .vd-saring .form-control {
            width: 100%; min-height: 42px; padding: 9px 14px 9px 40px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
            background: #fff; color: var(--dsb-tinta, #1c1f26); font-size: .88rem; box-shadow: none;
        }
        .vd-saring .form-control:focus { border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0; }
        .vd-saring .form-control-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; z-index: 2;
        }

        .vd-medan-rak { display: flex; flex-wrap: wrap; gap: 14px 16px; align-items: flex-start; }
        .vd-medan-rak > .dsb-medan { flex: 1 1 240px; min-width: 0; }
        .vd-medan-rak > .dsb-medan.is-lebar { flex-basis: 100%; }
        .vd-medan-rak .dsb-kartu-sub,
        .vd-medan-rak .dsb-galat { display: block; margin-top: 5px; }
        .vd-medan-rak .dsb-label-ket { margin-left: 4px; font-weight: 500; }

        /* Pilihan jenis: chip melar berbagi baris dengan kotak ketiknya, jadi
           barisnya habis terpakai berapa pun jumlah chipnya. */
        .vd-jenis { display: flex; flex-wrap: wrap; gap: 6px; }
        .vd-jenis-chip {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            flex: 1 1 120px; padding: 8px 11px; border-radius: 10px; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
            color: var(--dsb-tinta, #1c1f26); font-size: .79rem; font-weight: 600; white-space: nowrap;
        }
        .vd-jenis-chip i { color: var(--c, #64748b); }
        .vd-jenis-chip:hover { border-color: #c7d2fe; }
        .vd-jenis-chip.is-aktif { border-color: var(--c, #4f46e5); background: #f8fafc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .08); }
        .vd-jenis-isi { flex: 1 1 150px; min-width: 0; }

        .vd-sakelar { margin-top: 14px; }
        .vd-kaki { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }

        .vd-utama { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .vd-catatan { font-style: italic; }
        .vd-kontak {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .84rem; font-weight: 600; color: var(--dsb-tinta, #1c1f26);
        }
        .vd-kontak i { color: #0369a1; }
        .vd-kosong {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .76rem; font-weight: 600; color: #b45309;
        }

        .vd-halaman { padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
        .vd-halaman > * { margin-bottom: 0 !important; margin-top: 0 !important; }

        @media (max-width: 575.98px) {
            .vd-medan-rak .dsb-label-ket { display: block; margin-left: 0; margin-top: 2px; }
        }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu
           bertumpuk, aturan yang sama dengan daftar lemon lain. */
        @media (max-width: 767.98px) {
            .vd-tabel { min-width: 0; }
            .vd-tabel thead { display: none; }
            .vd-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
            .vd-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0; text-align: left !important;
            }
            .vd-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            .vd-tabel tbody td > * { min-width: 0; }
            .vd-utama { align-items: flex-end; text-align: right; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
