@section('title')
Klasifikasi Surat || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.surat.partials.surat-gaya')

    @php
        // Perbandingan dirakit di sini, bukan di kondisi direktif blok.
        $sedangSunting = $sunting !== null;
        $kosong = $daftar->isEmpty();
        $jumlahAktif = $daftar->where('aktif', true)->count();
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-tags-fill"></i> Mode Arsip Surat</span>
                <h1 class="dsb-judul">Klasifikasi Surat</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        Kode klasifikasi menjawab dua hal sekaligus: surat ini termasuk urusan
                        apa, dan berapa lama wajib disimpan.
                    </span>
                    <span class="d-block">
                        {{ $jumlahAktif }} dari {{ $daftar->count() }} kode sedang aktif dan
                        bisa dipilih saat mencatat surat.
                    </span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.surat.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                    <i class="bi bi-grid-fill"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('admin.surat.index') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #0ea5c6">
                    <i class="bi bi-archive"></i><span>Arsip</span>
                </a>
            </div>
        </header>

        {{-- sr-rak-puncak: borang pendek di kiri tidak ikut diregangkan
             setinggi daftar klasifikasi yang panjang. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak sr-rak-puncak">
                {{-- ---------- BORANG ---------- --}}
                @if ($bolehKelola)
                    <div class="dsb-kartu k-4">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #7c3aed">
                                    <i class="bi {{ $sedangSunting ? 'bi-pencil' : 'bi-plus-lg' }}"></i>
                                </span>
                                <div>
                                    <h3 class="dsb-kartu-judul">{{ $sedangSunting ? 'Ubah klasifikasi' : 'Tambah klasifikasi' }}</h3>
                                    <span class="dsb-kartu-sub">Kode, namanya, dan masa simpannya</span>
                                </div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="sr-borang">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="kl-kode">Kode <span class="dsb-wajib">*</span></label>
                                    <input id="kl-kode" type="text" maxlength="40"
                                        class="dsb-isian @error('kode') is-galat @enderror"
                                        wire:model="kode" placeholder="400">
                                    @error('kode')<div class="dsb-galat">{{ $message }}</div>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="kl-retensi">Masa simpan (tahun)</label>
                                    <input id="kl-retensi" type="number" min="0" max="100"
                                        class="dsb-isian @error('retensi') is-galat @enderror"
                                        wire:model="retensi" placeholder="10">
                                    @error('retensi')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Kosongkan kalau belum diputuskan.</span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="kl-nama">Nama <span class="dsb-wajib">*</span></label>
                                    <input id="kl-nama" type="text" maxlength="150"
                                        class="dsb-isian @error('nama') is-galat @enderror"
                                        wire:model="nama" placeholder="Keuangan & Pembayaran">
                                    @error('nama')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Tulis dalam kalimat biasa — inilah yang dibaca saat memilih.</span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="kl-ket">Contoh suratnya</label>
                                    <input id="kl-ket" type="text" maxlength="255"
                                        class="dsb-isian @error('keterangan') is-galat @enderror"
                                        wire:model="keterangan" placeholder="Tagihan, kuitansi, pajak, penagihan.">
                                    @error('keterangan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="kl-urutan">Urutan tampil</label>
                                    <input id="kl-urutan" type="number" min="0" max="9999"
                                        class="dsb-isian @error('urutan') is-galat @enderror"
                                        wire:model="urutan" placeholder="50">
                                    @error('urutan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Angka kecil tampil lebih dulu.</span>
                                </div>

                                <div class="dsb-medan">
                                    <span class="dsb-label">Aktif</span>
                                    <label class="d-flex align-items-center gap-2" style="font-size:.84rem">
                                        {{-- @checked DITULIS SENDIRI, dan atributnya satu baris.

                                             wire:model pada kotak centang hanya menyalin DOM →
                                             properti saat orangnya menekan; ia TIDAK PERNAH
                                             menggambar `checked` dari properti ke HTML. Tanpa
                                             @checked, membuka ulang klasifikasi yang aktif
                                             memperlihatkan kotak KOSONG — lalu menyimpannya
                                             menonaktifkan kode itu tanpa admin bermaksud begitu.

                                             Satu baris karena penjaganya memeriksa HTML apa
                                             adanya; baris baru di antara atribut membuat uji itu
                                             merah tanpa ada yang rusak.

                                             form-check-input: tanpa kelas ini centangnya tidak
                                             tergambar sama sekali. --}}
                                        <input type="checkbox" class="form-check-input" wire:model="aktif" @checked($this->aktif)>
                                        <span>Bisa dipilih saat mencatat surat</span>
                                    </label>
                                </div>

                                <div class="dsb-medan sr-lebar d-flex gap-2">
                                    <button type="button" wire:click="simpan" class="dsb-tombol is-utama"
                                        wire:loading.attr="disabled" wire:target="simpan">
                                        <i class="bi bi-check2-circle"></i><span>{{ $sedangSunting ? 'Simpan perubahan' : 'Tambah' }}</span>
                                    </button>
                                    @if ($sedangSunting)
                                        <button type="button" wire:click="batal" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                            <i class="bi bi-x-lg"></i><span>Batal</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ---------- DAFTAR ---------- --}}
                <div class="dsb-kartu {{ $bolehKelola ? 'k-8' : 'k-12' }}">
                    <div class="sr-daftar-kepala" style="--c: #7c3aed">
                        <span class="dsb-ikon is-kecil"><i class="bi bi-tags-fill"></i></span>
                        <div class="sr-daftar-judul">
                            <b>Daftar klasifikasi</b>
                            <span>Urutan tampil lalu kode · masa simpan menentukan tanggal boleh musnah</span>
                        </div>
                    </div>

                    @if ($kosong)
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-tags"></i></span>
                            <p class="dsb-kosong-judul">Belum ada klasifikasi</p>
                            <p class="dsb-kosong-ket">
                                Surat tetap bisa dicatat tanpa klasifikasi, tetapi masa simpannya
                                tidak akan terhitung. Tambahkan minimal satu kode.
                            </p>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            {{-- sr-tabel-sempit: dsb-tabel memaksa min-width 720px, dan
                                 kartu ini hanya 7 dari 12 lajur. Tanpa ini tabelnya meluber
                                 keluar kartu dan kolom Aksi — tombol ubah & hapusnya —
                                 terpotong di luar layar. Enam kolom di sini semuanya
                                 pendek, jadi lebarnya aman diserahkan ke isinya. --}}
                            <table class="dsb-tabel sr-tabel sr-tabel-sempit">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th class="k-lebar">Nama</th>
                                        <th>Masa Simpan</th>
                                        <th>Aktif</th>
                                        <th style="text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $k)
                                        @php
                                            $iniDisunting = $sunting === $k->id;
                                            $adaKet = filled($k->keterangan);
                                            $terpakai = $k->surat_count !== 0;
                                        @endphp
                                        <tr>
                                            <td><span class="dsb-tabel-judul sr-agenda">{{ $k->kode }}</span></td>
                                            <td>
                                                <span class="dsb-tabel-teks">
                                                    <span class="dsb-tabel-judul">{{ $k->nama }}</span>
                                                    <span class="dsb-tabel-meta">{{ $adaKet ? $k->keterangan : 'Tanpa contoh' }}</span>
                                                    {{-- "Dipakai" jadi keterangan, bukan kolom sendiri: di kartu
                                                         selebar ini satu kolom tambahan menyempitkan Nama sampai
                                                         kata terpotong di tengah ("Ketatausaha/an"). --}}
                                                    <span class="dsb-tabel-meta">
                                                        {{ $terpakai ? $k->surat_count.' surat memakai kode ini' : 'Belum dipakai surat mana pun' }}
                                                    </span>
                                                </span>
                                            </td>
                                            <td><span class="dsb-tabel-angka">{{ $k->retensi_teks }}</span></td>
                                            <td>
                                                @if ($k->aktif)
                                                    <span class="dsb-lencana" style="--c: #16a34a">Aktif</span>
                                                @else
                                                    <span class="dsb-lencana" style="--c: #64748b">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td class="dsb-tabel-aksi">
                                                @if ($bolehKelola)
                                                    <button type="button" wire:click="mulaiSunting({{ $k->id }})"
                                                        class="dsb-tabel-btn" title="Ubah" @disabled($iniDisunting)>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi" title="Hapus"
                                                        data-action="hapus" data-arg="{{ $k->id }}"
                                                        data-title="Hapus klasifikasi {{ $k->kode }}?"
                                                        data-text="{{ $terpakai
                                                            ? 'Kode ini masih dipakai '.$k->surat_count.' surat, jadi ia hanya akan DINONAKTIFKAN — surat lama tetap menunjuk kodenya.'
                                                            : 'Kode ini belum dipakai surat mana pun dan akan dihapus permanen.' }}"
                                                        data-confirm="{{ $terpakai ? 'Ya, nonaktifkan' : 'Ya, hapus' }}" data-icon="warning">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
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
                </div>
            </div>
        </section>
    </div>

    @include('livewire.layout.sweetalert')
</div>
