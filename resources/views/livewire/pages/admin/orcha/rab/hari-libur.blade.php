@section('title')
Hari Libur || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $formTerbuka = $tambah || $sunting !== null;

        /* Dipisah supaya yang sudah lewat tidak memenuhi bagian atas layar.
           Daftar yang dibuka bulan Desember hampir seluruhnya tanggal yang
           sudah lewat, dan yang dicari orang saat membukanya selalu yang
           belum datang. */
        $akanDatang = array_values(array_filter($daftar, fn ($b) => ! $b['lewat']));
        $sudahLewat = array_values(array_filter($daftar, fn ($b) => $b['lewat']));
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Hari Libur</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Tanggal merah yang dihargai seperti akhir pekan.</span>
                    <span class="d-block">
                        <strong>Sabtu &amp; Minggu tidak perlu didaftarkan</strong> — keduanya sudah terbaca
                        sendiri dari tanggalnya.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.master-harga') }}" wire:navigate class="dsb-tombol is-awas">
                    <i class="bi bi-tags"></i><span>Master Harga</span>
                </a>
                <button type="button" wire:click="bukaTambah" class="dsb-tombol is-utama">
                    <i class="bi bi-plus-lg"></i><span>Tambah Tanggal</span>
                </button>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ========= APA GUNANYA DAFTAR INI =========

             Layar yang paling mudah dilupakan: diisi sekali setahun, dan
             kalau tidak diisi tidak ada apa pun yang terlihat rusak —
             tanggal merahnya hanya dihargai tarif hari kerja. Jadi
             akibatnya yang ditulis, bukan cara memakainya. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="hl-guna">
                            <span class="dsb-ikon is-kecil" style="--c: {{ $hargaTerpengaruh > 0 ? 'var(--dsb-awas)' : 'var(--dsb-netral)' }}">
                                <i class="bi bi-calendar2-week"></i>
                            </span>
                            <div class="hl-guna-teks">
                                @if ($hargaTerpengaruh > 0)
                                    <strong>{{ $hargaTerpengaruh }} harga</strong> punya tarif akhir pekan sendiri.
                                    Pada tanggal yang terdaftar di sini, tarif itulah yang dipakai saat
                                    <em>Tarik biaya</em> di penyusun RAB.
                                    @if (count($daftar) === 0)
                                        <span class="hl-tegas">
                                            Daftarnya masih kosong, jadi 17 Agustus dan Idul Fitri masih dihargai
                                            tarif hari kerja.
                                        </span>
                                    @endif
                                @else
                                    Belum ada harga yang punya tarif akhir pekan, jadi daftar ini belum
                                    mengubah apa pun. Isi dulu <em>harga akhir pekan</em> di
                                    <a href="{{ route('admin.orcha.master-harga') }}" wire:navigate>Master Harga</a>.
                                @endif

                                <span class="dsb-kartu-sub d-block">
                                    RAB yang sudah menarik biaya <strong>tidak berubah</strong> kalau daftar ini
                                    disunting — harganya sudah dibekukan per baris penawaran.
                                </span>
                            </div>
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
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-calendar-plus"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">{{ $sunting ? 'Ubah Tanggal' : 'Tanggal Libur Baru' }}</h3>
                                    <span class="dsb-kartu-sub">
                                        Yang perlu didaftarkan hanya libur yang tidak bisa dihitung dari tanggalnya.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            <div class="dsb-medan-rak">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="hl-tanggal">Tanggal <span class="dsb-wajib">*</span></label>
                                    <input id="hl-tanggal" type="date" wire:model="isian.tanggal"
                                        class="dsb-isian @error('isian.tanggal') is-galat @enderror">
                                    @error('isian.tanggal')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="hl-nama">
                                        Nama liburnya <span class="dsb-wajib">*</span>
                                        <span class="dsb-label-ket">dipakai di rincian biaya RAB</span>
                                    </label>
                                    <input id="hl-nama" type="text" wire:model="isian.nama" maxlength="120"
                                        class="dsb-isian @error('isian.nama') is-galat @enderror"
                                        placeholder="Cuti bersama">
                                    @error('isian.nama')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                {{-- Hanya NAMANYA yang ditawarkan, bukan tanggalnya:
                                     Idul Fitri dan Nyepi bergeser tiap tahun, dan
                                     tanggal yang ditebakkan akan tersimpan dengan
                                     yakin di hari yang salah. --}}
                                <div class="dsb-medan is-lebar">
                                    <span class="orcha-label-kecil">Nama yang sering dipakai</span>
                                    <div class="hl-chip-rak">
                                        @foreach ($namaSering as $nama)
                                            <button type="button" wire:click="pakaiNama(@js($nama))"
                                                class="hl-chip {{ ($isian['nama'] ?? '') === $nama ? 'is-aktif' : '' }}">
                                                {{ $nama }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="hl-kaki">
                                <button type="button" wire:click="tutup" class="dsb-tombol is-lembut">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>
                                <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                                    class="dsb-tombol is-utama">
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

        {{-- ================== TEMPEL BANYAK ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    @if (! $bukaTempel)
                        <div class="dsb-kartu-isi hl-tempel-ajak">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-clipboard-plus"></i></span>
                            <div class="hl-guna-teks">
                                <strong>Punya daftar libur setahun?</strong>
                                <span class="dsb-kartu-sub d-block">
                                    Tempel sekaligus, satu baris satu tanggal — tidak perlu 16 kali buka-isi-simpan.
                                </span>
                            </div>
                            <button type="button" wire:click="$set('bukaTempel', true)" class="dsb-tombol is-lembut">
                                <i class="bi bi-clipboard"></i><span>Tempel Daftar</span>
                            </button>
                        </div>
                    @else
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-clipboard-plus"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Tempel daftar libur</h3>
                                    <span class="dsb-kartu-sub">
                                        Tanggal dulu, lalu namanya. Tanggal yang sudah ada dilewati, tidak ditolak.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            <label class="orcha-label-kecil" for="hl-tempel">Satu baris satu tanggal</label>
                            <textarea id="hl-tempel" wire:model="tempel" rows="6"
                                class="dsb-isian hl-tempel-kotak @error('tempel') is-galat @enderror"
                                placeholder="2027-01-01	Tahun Baru&#10;2027-03-19	Nyepi&#10;2027-08-17	HUT RI"></textarea>
                            @error('tempel')<span class="dsb-galat">{{ $message }}</span>@enderror

                            <span class="dsb-kartu-sub d-block hl-tempel-ket">
                                Pemisahnya boleh tab, koma, atau dua spasi — tempelan dari spreadsheet
                                langsung terbaca. Baris yang tidak terbaca disebut nomornya, bukan ditelan.
                            </span>

                            <div class="hl-kaki">
                                <button type="button" wire:click="tutup" class="dsb-tombol is-lembut">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>
                                <button type="button" wire:click="kirimTempel" wire:loading.attr="disabled" wire:target="kirimTempel"
                                    class="dsb-tombol is-utama">
                                    <i class="bi bi-upload" wire:loading.remove wire:target="kirimTempel"></i>
                                    <span class="dsb-putar" wire:loading wire:target="kirimTempel"></span>
                                    <span>Tambahkan Semua</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat" wire:target="filterTahun">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-utama)"><i class="bi bi-calendar3"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Daftar hari libur</h3>
                                <span class="dsb-kartu-sub">
                                    {{ count($daftar) }} tanggal terdaftar{{ count($sudahLewat) ? ', '.count($sudahLewat).' sudah lewat' : '' }}.
                                </span>
                            </div>
                        </div>

                        @if (count($tahunAda) > 1)
                            <div class="dsb-kartu-kepala-kanan">
                                <select wire:model.live="filterTahun" class="dsb-isian hl-tahun">
                                    <option value="" @selected($this->filterTahun === '')>Semua tahun</option>
                                    @foreach ($tahunAda as $th)
                                        <option value="{{ $th }}" @selected((string) $this->filterTahun === (string) $th)>{{ $th }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    @if (count($daftar) === 0)
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-calendar-x"></i></span>
                                <p class="dsb-kosong-judul">
                                    {{ $this->adaSaringan() ? 'Tidak ada di tahun itu' : 'Belum ada tanggal libur' }}
                                </p>
                                <p class="dsb-kosong-ket">
                                    {{-- Kosong BUKAN kesalahan, dan dikatakan apa adanya:
                                         admin yang membaca "belum ada" tanpa penjelasan akan
                                         mengira ada yang rusak, atau mengira Sabtu-Minggu
                                         juga ikut tidak terbaca. --}}
                                    Daftar yang kosong berarti <strong>hanya Sabtu &amp; Minggu</strong> yang
                                    dihitung akhir pekan — dan itu memang sudah berjalan.
                                    Yang perlu ditambahkan di sini cuma tanggal merah yang tidak bisa
                                    dihitung dari tanggalnya: 17 Agustus, Idul Fitri, Nyepi, cuti bersama.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel hl-tabel">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th>Dicatat</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (array_merge($akanDatang, $sudahLewat) as $baris)
                                        <tr wire:key="hl-{{ $baris['id'] }}" class="{{ $baris['lewat'] ? 'is-mati' : '' }}">
                                            <td data-judul="Tanggal">
                                                <span class="hl-utama">
                                                    <span class="dsb-tabel-judul">{{ $baris['tanggal_teks'] }}</span>
                                                    @if ($baris['lewat'])
                                                        <span class="dsb-tabel-meta">sudah lewat</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Nama">
                                                <span class="hl-utama">
                                                    <span>{{ $baris['nama'] }}</span>
                                                    @if ($baris['sudah_akhir_pekan'])
                                                        {{-- Tidak salah dan tidak merusak apa pun, tetapi
                                                             juga tidak mengubah harga mana pun — dan admin
                                                             yang mengira baru saja mengamankan satu tanggal
                                                             berhak tahu tanggal itu memang sudah aman. --}}
                                                        <span class="dsb-lencana" style="--c: var(--dsb-netral)">
                                                            <i class="bi bi-info-circle"></i>memang akhir pekan
                                                        </span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Dicatat">
                                                <span class="dsb-tabel-meta">{{ $baris['oleh'] ?: '—' }}</span>
                                            </td>
                                            <td data-judul="Aksi" class="text-end">
                                                <div class="hl-aksi">
                                                    <button type="button"
                                                        wire:click="bukaSunting({{ $baris['id'] }}, @js($baris))"
                                                        class="dsb-tombol is-lembut is-kecil" title="Ubah">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="dsb-tombol is-bahaya is-kecil pcek-konfirmasi"
                                                        data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                        data-title="Hapus tanggal ini?"
                                                        data-text="{{ $baris['tanggal_teks'] }} — {{ $baris['nama'] }}. RAB yang sudah dibuat tidak berubah; harganya sudah dibekukan."
                                                        data-confirm="Hapus" data-icon="warning" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @push('gaya')
        <style>
            /* Satu blok penjelas, bukan paragraf lepas: ikon di kiri,
               kalimatnya melar mengisi sisa baris. */
            .hl-guna, .hl-tempel-ajak {
                display: flex; align-items: flex-start; gap: 11px; flex-wrap: wrap;
            }
            .hl-guna-teks { flex: 1 1 280px; min-width: 0; font-size: .85rem; line-height: 1.55; }
            .hl-tegas {
                display: block; margin-top: 5px; font-weight: 600;
                color: var(--dsb-awas, #b45309);
            }
            .hl-tempel-ajak { align-items: center; }
            .hl-tempel-kotak {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: .82rem; line-height: 1.6; resize: vertical;
            }
            .hl-tempel-ket { margin-top: 7px; }

            .hl-chip-rak { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; }
            .hl-chip {
                padding: 6px 11px; border-radius: 999px; cursor: pointer;
                background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
                color: var(--dsb-tinta, #1c1f26); font-size: .78rem; font-weight: 600;
                white-space: nowrap;
            }
            .hl-chip:hover { border-color: #c7d2fe; }
            .hl-chip.is-aktif { border-color: #4f46e5; background: #f6f5ff; }

            .hl-kaki { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; flex-wrap: wrap; }
            .hl-utama { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
            .hl-aksi { display: inline-flex; gap: 6px; justify-content: flex-end; }
            .hl-tahun { max-width: 160px; }
            /* Yang sudah lewat diredupkan, bukan disembunyikan: tahun lalu
               tetap perlu bisa dibaca saat memeriksa penawaran lama. */
            .hl-tabel tr.is-mati { opacity: .62; }
        </style>
    @endpush
</div>
