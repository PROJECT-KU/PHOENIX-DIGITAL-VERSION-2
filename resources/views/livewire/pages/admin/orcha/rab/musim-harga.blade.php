@section('title')
Musim Harga || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $formTerbuka = $tambah || $sunting !== null;

        /* Dipisah supaya musim yang sudah lewat tidak memenuhi bagian atas
           layar. Daftar yang dibuka bulan November hampir seluruhnya musim
           tahun lalu, dan yang dicari orang saat membukanya selalu yang
           belum datang. */
        $akanDatang = array_values(array_filter($daftar, fn ($b) => ! $b['lewat']));
        $sudahLewat = array_values(array_filter($daftar, fn ($b) => $b['lewat']));
        $belumDiisi = array_values(array_filter($akanDatang, fn ($b) => $b['jumlah_harga'] === 0));
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Musim Harga</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Rentang tanggal yang punya tarifnya sendiri — Nataru, Lebaran, long weekend.</span>
                    <span class="d-block">
                        <strong>Bukan sama dengan Hari Libur:</strong> hari libur memakai tarif akhir pekan,
                        musim punya angkanya sendiri per harga.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.hari-libur') }}" wire:navigate class="dsb-tombol is-awas">
                    <i class="bi bi-calendar3"></i><span>Hari Libur</span>
                </a>
                <a href="{{ route('admin.orcha.master-harga') }}" wire:navigate class="dsb-tombol is-awas">
                    <i class="bi bi-tags"></i><span>Master Harga</span>
                </a>
                <button type="button" wire:click="bukaTambah" class="dsb-tombol is-utama">
                    <i class="bi bi-plus-lg"></i><span>Tambah Musim</span>
                </button>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ========= BEDANYA DENGAN HARI LIBUR =========

             Dua layar yang terlihat mirip dan kalau tertukar menghasilkan
             selisih yang tidak berbunyi: Nataru yang diisi sebagai tanggal
             merah akan dibayar tarif hari Sabtu. Jadi bedanya ditulis di
             muka, bukan dianggap sudah dipahami. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="mu-guna">
                            <span class="dsb-ikon is-kecil" style="--c: {{ $hargaTerpasang > 0 ? 'var(--dsb-utama)' : 'var(--dsb-netral)' }}">
                                <i class="bi bi-graph-up-arrow"></i>
                            </span>
                            <div class="mu-guna-teks">
                                @if ($hargaTerpasang > 0)
                                    <strong>{{ $hargaTerpasang }} harga</strong> punya tarif musim sendiri.
                                    Pada tanggal yang masuk rentang di bawah, tarif itulah yang dipakai saat
                                    <em>Tarik biaya</em> — mengalahkan tarif akhir pekan.
                                @else
                                    Belum ada satu pun harga yang punya tarif musim, jadi daftar ini belum
                                    mengubah apa pun. Tambahkan musimnya dulu, lalu isi tarifnya lewat
                                    tombol <em>Isi Tarif</em> di barisnya.
                                @endif

                                <span class="dsb-kartu-sub d-block mu-beda">
                                    <strong>Urutan menangnya:</strong> musim &rarr; akhir pekan &rarr; hari kerja.
                                    Jadi 25 Desember yang ada di dalam Nataru memakai tarif Nataru, bukan
                                    tarif tanggal merah.
                                </span>

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

        {{-- ========= MUSIM YANG BELUM DIISI =========

             Musim yang terdaftar tanpa satu pun tarif adalah pekerjaan yang
             setengah jalan, dan setengahnya yang hilang tidak terlihat:
             rombongannya tetap dibayari tarif hari biasa. --}}
        @if (count($belumDiisi) > 0)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12 mu-kartu-awas">
                        <div class="dsb-kartu-isi">
                            <div class="mu-guna">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-awas)">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </span>
                                <div class="mu-guna-teks">
                                    <strong>{{ count($belumDiisi) }} musim belum punya satu pun tarif.</strong>
                                    Rentangnya sudah terdaftar, tetapi selama tarifnya kosong seluruh
                                    rombongan di tanggal itu masih dibayari harga hari biasa.
                                    <span class="mu-daftar-nama d-block">
                                        @foreach ($belumDiisi as $b)
                                            <a href="{{ route('admin.orcha.musim-harga.isi', $b['id']) }}" wire:navigate
                                                class="mu-tautan-awas">{{ $b['nama'] }}</a>{{ $loop->last ? '' : ', ' }}
                                        @endforeach
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== FORMULIR ================== --}}
        @if ($formTerbuka)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-calendar-range"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">{{ $sunting ? 'Ubah Musim' : 'Musim Baru' }}</h3>
                                    <span class="dsb-kartu-sub">
                                        Rentangnya tertutup — kedua tanggal ujungnya ikut terhitung.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            <div class="dsb-medan-rak">
                                <div class="dsb-medan is-lebar">
                                    <label class="dsb-label" for="mu-nama">
                                        Nama musim <span class="dsb-wajib">*</span>
                                        <span class="dsb-label-ket">ikut tercetak di rincian biaya RAB</span>
                                    </label>
                                    <input id="mu-nama" type="text" wire:model="isian.nama" maxlength="120"
                                        class="dsb-isian @error('isian.nama') is-galat @enderror"
                                        placeholder="Nataru 2026/2027">
                                    @error('isian.nama')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mu-mulai">Mulai tanggal <span class="dsb-wajib">*</span></label>
                                    <input id="mu-mulai" type="date" wire:model="isian.tanggal_mulai"
                                        class="dsb-isian @error('isian.tanggal_mulai') is-galat @enderror">
                                    @error('isian.tanggal_mulai')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mu-sampai">
                                        Sampai tanggal <span class="dsb-wajib">*</span>
                                        <span class="dsb-label-ket">hari terakhir yang masih bertarif musim</span>
                                    </label>
                                    <input id="mu-sampai" type="date" wire:model="isian.tanggal_sampai"
                                        class="dsb-isian @error('isian.tanggal_sampai') is-galat @enderror">
                                    @error('isian.tanggal_sampai')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                {{-- Hanya NAMANYA yang ditawarkan, bukan tanggalnya:
                                     Lebaran bergeser sebelas hari tiap tahun, dan
                                     rentang yang ditebakkan akan tersimpan dengan
                                     yakin di pekan yang salah. --}}
                                <div class="dsb-medan is-lebar">
                                    <span class="orcha-label-kecil">Nama yang sering dipakai</span>
                                    <div class="mu-chip-rak">
                                        @foreach ($namaSering as $nama)
                                            <button type="button" wire:click="pakaiNama(@js($nama))"
                                                class="mu-chip {{ ($isian['nama'] ?? '') === $nama ? 'is-aktif' : '' }}">
                                                {{ $nama }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mu-prioritas">
                                        Prioritas
                                        <span class="dsb-label-ket">hanya perlu diisi kalau rentangnya bertumpuk</span>
                                    </label>
                                    <input id="mu-prioritas" type="number" min="0" max="1000" wire:model="isian.prioritas"
                                        class="dsb-isian @error('isian.prioritas') is-galat @enderror">
                                    @error('isian.prioritas')<span class="dsb-galat">{{ $message }}</span>@enderror
                                    <span class="dsb-label-ket d-block mu-ket">
                                        Angka lebih besar menang. Kalau seri, rentang yang lebih pendek yang
                                        menang — misalnya "Puncak Tahun Baru" di dalam "Nataru".
                                    </span>
                                </div>

                                <div class="dsb-medan">
                                    <span class="dsb-label">Dipakai RAB baru</span>
                                    <label class="mu-sakelar">
                                        <input type="checkbox" wire:model="isian.aktif">
                                        <span>{{ ($isian['aktif'] ?? true) ? 'Ya, tarifnya dipakai' : 'Tidak — tarifnya tersimpan tapi tidak dipakai' }}</span>
                                    </label>
                                    <span class="dsb-label-ket d-block mu-ket">
                                        Musim yang sudah lewat sebaiknya <strong>dimatikan</strong>, bukan dihapus:
                                        menghapusnya ikut membawa seluruh tarifnya.
                                    </span>
                                </div>
                            </div>

                            <div class="mu-kaki">
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

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat" wire:target="filterTahun">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-utama)"><i class="bi bi-calendar-range"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Daftar musim</h3>
                                <span class="dsb-kartu-sub">
                                    {{ count($daftar) }} musim terdaftar{{ count($sudahLewat) ? ', '.count($sudahLewat).' sudah lewat' : '' }}.
                                </span>
                            </div>
                        </div>

                        @if (count($tahunAda) > 1)
                            <div class="dsb-kartu-kepala-kanan">
                                <select wire:model.live="filterTahun" class="dsb-isian mu-tahun">
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
                                    {{ $this->adaSaringan() ? 'Tidak ada di tahun itu' : 'Belum ada musim' }}
                                </p>
                                <p class="dsb-kosong-ket">
                                    {{-- Kosong BUKAN kesalahan, dan dikatakan apa adanya:
                                         admin yang membaca "belum ada" tanpa penjelasan akan
                                         mengira ada yang rusak. --}}
                                    Daftar yang kosong berarti <strong>hanya hari kerja &amp; akhir pekan</strong>
                                    yang dikenal — dan itu memang sudah berjalan sejak awal.
                                    Tambahkan musim di sini hanya kalau ada rentang tanggal yang tarifnya
                                    berbeda dari keduanya: Nataru, Lebaran, long weekend.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel mu-tabel">
                                <thead>
                                    <tr>
                                        <th>Musim</th>
                                        <th>Rentang</th>
                                        <th>Tarif terpasang</th>
                                        <th>Prioritas</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (array_merge($akanDatang, $sudahLewat) as $baris)
                                        <tr wire:key="mu-{{ $baris['id'] }}"
                                            class="{{ $baris['lewat'] || ! $baris['aktif'] ? 'is-mati' : '' }}">
                                            <td data-judul="Musim">
                                                <span class="mu-utama">
                                                    <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>
                                                    <span class="mu-lencana-rak">
                                                        @if ($baris['berjalan'])
                                                            <span class="dsb-lencana" style="--c: var(--dsb-utama)">
                                                                <i class="bi bi-broadcast"></i>sedang berjalan
                                                            </span>
                                                        @endif
                                                        @if (! $baris['aktif'])
                                                            <span class="dsb-lencana" style="--c: var(--dsb-netral)">
                                                                <i class="bi bi-pause-circle"></i>dimatikan
                                                            </span>
                                                        @endif
                                                        @if ($baris['lewat'])
                                                            <span class="dsb-tabel-meta">sudah lewat</span>
                                                        @endif
                                                    </span>
                                                </span>
                                            </td>
                                            <td data-judul="Rentang">
                                                <span class="mu-utama">
                                                    <span>{{ $baris['rentang_teks'] }}</span>
                                                    <span class="dsb-tabel-meta">{{ $baris['jumlah_hari'] }} hari</span>
                                                    @if (count($baris['tumpang']) > 0)
                                                        {{-- Bertumpuk bukan kesalahan — cuti bersama di dalam
                                                             Nataru itu nyata — tetapi pemenangnya diputuskan
                                                             aturan yang tidak akan diduga siapa pun yang
                                                             belum membacanya. Jadi disebut. --}}
                                                        @foreach ($baris['tumpang'] as $t)
                                                            <span class="dsb-tabel-meta mu-tumpang">
                                                                <i class="bi bi-layers-half"></i>
                                                                <span>bertumpuk dengan {{ $t['nama'] }} —
                                                                    {{ $t['menang'] ? 'yang itu menang' : 'yang ini menang' }}</span>
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Tarif terpasang">
                                                @if ($baris['jumlah_harga'] > 0)
                                                    <span class="dsb-lencana" style="--c: var(--dsb-aman)">
                                                        <i class="bi bi-check2"></i>{{ $baris['jumlah_harga'] }} harga
                                                    </span>
                                                @else
                                                    <span class="dsb-lencana" style="--c: var(--dsb-awas)">
                                                        <i class="bi bi-exclamation-triangle"></i>belum ada
                                                    </span>
                                                @endif
                                            </td>
                                            <td data-judul="Prioritas">
                                                <span class="dsb-tabel-meta">{{ $baris['prioritas'] }}</span>
                                            </td>
                                            <td data-judul="Aksi" class="text-end">
                                                <div class="mu-aksi">
                                                    <a href="{{ route('admin.orcha.musim-harga.isi', $baris['id']) }}"
                                                        wire:navigate class="dsb-tombol is-utama is-kecil" title="Isi tarif musim">
                                                        <i class="bi bi-cash-coin"></i><span>Isi Tarif</span>
                                                    </a>
                                                    <button type="button"
                                                        wire:click="bukaSunting({{ $baris['id'] }}, @js($baris))"
                                                        class="dsb-tombol is-lembut is-kecil" title="Ubah">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                        wire:click="ubahAktif({{ $baris['id'] }}, {{ $baris['aktif'] ? 'false' : 'true' }})"
                                                        class="dsb-tombol is-lembut is-kecil"
                                                        title="{{ $baris['aktif'] ? 'Matikan musim ini' : 'Nyalakan musim ini' }}">
                                                        <i class="bi {{ $baris['aktif'] ? 'bi-pause' : 'bi-play' }}"></i>
                                                    </button>
                                                    <button type="button" class="dsb-tombol is-bahaya is-kecil pcek-konfirmasi"
                                                        data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                        data-title="Hapus musim ini?"
                                                        data-text="{{ $baris['nama'] }} ({{ $baris['rentang_teks'] }}). Kalau masih punya tarif terpasang, hapusnya akan ditolak — matikan saja musimnya."
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

    {{-- <style> telanjang, bukan didorong ke sebuah stack.

         Layout templateindex tidak punya satu pun stack, jadi gaya yang
         didorong ke sana tidak pernah ikut tergambar — halamannya tetap
         200, cuma tampil tanpa gayanya sendiri, dan tidak ada galat yang
         menyebutkannya. --}}
    <style>
        .mu-guna { display: flex; align-items: flex-start; gap: 11px; flex-wrap: wrap; }
        .mu-guna-teks { flex: 1 1 280px; min-width: 0; font-size: .85rem; line-height: 1.55; }
        .mu-beda { margin-top: 6px; }
        .mu-kartu-awas { border-color: #fcd9a4; background: #fffdf7; }
        .mu-daftar-nama { margin-top: 6px; font-weight: 600; }
        .mu-tautan-awas { color: var(--dsb-awas, #b45309); text-decoration: underline; }

        .mu-chip-rak { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; }
        .mu-chip {
            padding: 6px 11px; border-radius: 999px; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
            color: var(--dsb-tinta, #1c1f26); font-size: .78rem; font-weight: 600;
            white-space: nowrap;
        }
        .mu-chip:hover { border-color: #c7d2fe; }
        .mu-chip.is-aktif { border-color: #4f46e5; background: #f6f5ff; }

        .mu-sakelar { display: inline-flex; align-items: center; gap: 8px; font-size: .84rem; cursor: pointer; }
        .mu-ket { margin-top: 5px; line-height: 1.5; }
        .mu-kaki { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; flex-wrap: wrap; }
        .mu-utama { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .mu-lencana-rak { display: flex; flex-wrap: wrap; gap: 5px; }
        /* Pemilihnya DIAWALI .dsb-tabel, dan itu bukan kelebihan huruf.

           Sistem dasbor menulis `.dsb-tabel .dsb-tabel-meta { flex-wrap: wrap }`
           — bobot (0,2,0). Kelas satu biji di sini bobotnya (0,1,0) dan kalah
           tanpa satu pun tanda: ikonnya tetap terdampar sendirian di baris
           atas, teksnya turun ke bawahnya, dan CSS-nya terlihat benar saat
           dibaca. */
        .dsb-tabel .mu-tumpang {
            display: flex; align-items: flex-start; gap: 4px;
            align-self: stretch; flex-wrap: nowrap;
        }
        /* min-width: 0 supaya teks yang panjang MENYUSUT dan membungkus di
           dalam kolomnya, bukan mendorong ikonnya keluar baris. */
        .dsb-tabel .mu-tumpang i { flex: 0 0 auto; margin-top: 2px; }
        .dsb-tabel .mu-tumpang span { min-width: 0; }
        /* TIDAK membungkus: dibungkus, tombol hapus turun sendirian ke baris
           kedua dan deretannya terbaca seperti layar yang rusak. Kolomnya
           cukup sempit untuk itu di layar 1440px. Yang tidak muat digulir
           oleh .dsb-tabel-bungkus, seperti tabel Orcha yang lain. */
        .mu-aksi { display: inline-flex; gap: 6px; justify-content: flex-end; flex-wrap: nowrap; }
        .mu-aksi .dsb-tombol { white-space: nowrap; }
        .mu-tahun { max-width: 160px; }
        /* Yang sudah lewat diredupkan, bukan disembunyikan: tahun lalu tetap
           perlu bisa dibaca saat memeriksa penawaran lama. */
        .mu-tabel tr.is-mati { opacity: .62; }
    </style>
    {{-- Layar ini mengirim `toast-error` saat penyimpanan ditolak, dan yang
         mendengarkannya hidup di partial ini. Tanpanya, gagal simpan tidak
         memunculkan apa pun — musim yang tidak tersimpan terlihat persis
         seperti musim yang tersimpan. --}}
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
