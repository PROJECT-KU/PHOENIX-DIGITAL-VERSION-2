@section('title')
{{ $isEdit ? 'Ubah Surat' : 'Catat Surat' }} || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.surat.partials.surat-gaya')

    @php
        // Perbandingan dirakit di sini, bukan di kondisi direktif blok.
        $masuk = $this->f['arah'] === 'masuk';
        $labelPihak = $this->labelPihak();
        $warnaArah = $masuk ? '#0ea5c6' : '#17a974';
        $ikonArah = $masuk ? 'bi-envelope-arrow-down' : 'bi-envelope-arrow-up';
        $adaNomorNanti = filled($nomorNanti);
        $adaBerkasBaru = count($berkas) !== 0;
        $keluar = ! $masuk;
        $nomorDikunci = $keluar && $nomorOtomatis;
        $adaArtiNomor = $keluar && count($potonganNomor) !== 0;
        $tembusan = $this->daftarTembusan();
        $adaTembusan = count($tembusan) !== 0;
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi {{ $ikonArah }}"></i> {{ $masuk ? 'Surat Masuk' : 'Surat Keluar' }}</span>
                <h1 class="dsb-judul">{{ $isEdit ? 'Ubah Surat' : 'Catat Surat' }}</h1>
                <p class="dsb-hero-ket">
                    @if ($adaNomorNanti)
                        <span class="d-block">
                            Nomor agendanya dibuat otomatis:
                            <b class="sr-agenda">{{ $nomorNanti }}</b>
                        </span>
                    @endif
                    <span class="d-block">
                        Yang wajib hanya tiga: jenis surat, perihal, dan tanggal surat.
                        Sisanya boleh dilengkapi nanti.
                    </span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.surat.index') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        <form wire:submit="save">
            {{-- sr-rak-puncak: dua kartu borang berhenti di akhir isinya,
                 bukan diregangkan sama tinggi. --}}
            <section class="dsb-bagian">
                <div class="dsb-rak sr-rak-borang">
                    {{-- ---------- IDENTITAS SURAT ---------- --}}
                    <div class="dsb-kartu k-7 sr-kartu-borang" style="--aksen: {{ $warnaArah }}">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: {{ $warnaArah }}"><i class="bi {{ $ikonArah }}"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Isi surat</h3>
                                    <span class="dsb-kartu-sub">Apa suratnya, dari atau untuk siapa, dan kapan</span>
                                </div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="sr-borang sr-borang-duo">
                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-arah">Jenis surat <span class="dsb-wajib">*</span></label>
                                    <select id="sr-arah" class="dsb-isian @error('f.arah') is-galat @enderror" wire:model.live="f.arah">
                                        @foreach (\App\Models\Surat\Surat::ARAH as $kunci => $label)
                                            <option value="{{ $kunci }}" @selected($this->f['arah'] === $kunci)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('f.arah')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">
                                        Surat masuk = yang kita terima. Surat keluar = yang kita kirim.
                                        Nomor agendanya berurut sendiri per jenis dan per tahun.
                                    </span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-induk">Menanggapi surat</label>
                                    <select id="sr-induk" class="dsb-isian @error('f.induk_id') is-galat @enderror" wire:model="f.induk_id">
                                        <option value="">Tidak menanggapi surat lain</option>
                                        @foreach ($daftarInduk as $induk)
                                            <option value="{{ $induk->id }}" @selected((string) $this->f['induk_id'] === (string) $induk->id)>
                                                {{ $induk->nomor_agenda }} &middot; {{ \Illuminate\Support\Str::limit($induk->perihal, 60) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('f.induk_id')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">
                                        Isi kalau surat ini balasan atau tindak lanjut dari surat lain. Satu urusan
                                        yang berjalan beberapa surat jadi punya benang merah, bukan tersebar.
                                        Daftarnya 60 surat terakhir ber-{{ $masuk ? 'arah keluar' : 'arah masuk' }}.
                                    </span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-perihal">Perihal <span class="dsb-wajib">*</span></label>
                                    <input id="sr-perihal" type="text" maxlength="255"
                                        class="dsb-isian @error('f.perihal') is-galat @enderror"
                                        wire:model="f.perihal" placeholder="Permohonan kerja sama pelatihan karyawan">
                                    @error('f.perihal')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Tulis seperti di suratnya. Inilah yang dicari orang lain nanti.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-pihak">{{ $labelPihak }}</label>
                                    @if ($masuk)
                                        <input id="sr-pihak" type="text" maxlength="180"
                                            class="dsb-isian @error('f.dari') is-galat @enderror"
                                            wire:model="f.dari" placeholder="Dinas Pendidikan Kab. Banyuwangi">
                                        @error('f.dari')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    @else
                                        <input id="sr-pihak" type="text" maxlength="180"
                                            class="dsb-isian @error('f.kepada') is-galat @enderror"
                                            wire:model="f.kepada" placeholder="PT Mitra Sejahtera">
                                        @error('f.kepada')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    @endif
                                    <span class="dsb-kartu-sub">Nama orang atau instansinya.</span>
                                </div>

                                <div class="dsb-medan" x-data="{}">
                                    <label class="dsb-label" for="sr-tembusan">Tembusan</label>

                                    {{-- Tiap pihak jadi satu keping di dalam kotaknya.
                                         Kepingnya DIRENDER SERVER dari teks yang tersimpan, bukan
                                         disusun di peramban: yang tersimpan tetap satu kolom teks
                                         dipisah titik koma, jadi apa yang terlihat selalu sama
                                         dengan apa yang nanti tercetak di kaki surat. --}}
                                    <div class="sr-tembusan dsb-isian @error('f.tembusan') is-galat @enderror"
                                        x-on:click="$refs.srTembusan.focus()">
                                        @foreach ($tembusan as $urutan => $pihak)
                                            <span class="sr-keping" wire:key="tembusan-{{ $urutan }}-{{ md5($pihak) }}">
                                                <span>{{ $pihak }}</span>
                                                <button type="button" wire:click="hapusTembusan({{ $urutan }})"
                                                    title="Hapus {{ $pihak }}" aria-label="Hapus {{ $pihak }}">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </span>
                                        @endforeach

                                        <input id="sr-tembusan" type="text" maxlength="120" class="sr-tembusan-ketik"
                                            x-ref="srTembusan"
                                            placeholder="{{ $adaTembusan ? 'Tambah lagi…' : 'Direktur; Bagian Keuangan' }}"
                                            x-on:keydown="if ($event.key === ';' || $event.key === 'Enter') { $event.preventDefault(); $wire.tambahTembusan($el.value); $el.value = ''; }"
                                            x-on:blur="if ($el.value.trim() !== '') { $wire.tambahTembusan($el.value); $el.value = ''; }">
                                    </div>

                                    @error('f.tembusan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">
                                        Ketik namanya lalu tekan <b>titik koma</b> atau <b>Enter</b> &mdash; tiap pihak
                                        jadi kepingnya sendiri dan bisa dibuang satu-satu.
                                    </span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-tgl-surat">Tanggal surat <span class="dsb-wajib">*</span></label>
                                    <input id="sr-tgl-surat" type="date"
                                        class="dsb-isian @error('f.tanggal_surat') is-galat @enderror"
                                        wire:model.live="f.tanggal_surat">
                                    @error('f.tanggal_surat')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Tanggal yang tertulis di suratnya.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-tgl-catat">
                                        Tanggal {{ $masuk ? 'diterima' : 'dikirim' }}
                                    </label>
                                    <input id="sr-tgl-catat" type="date"
                                        class="dsb-isian @error('f.tanggal_catat') is-galat @enderror"
                                        wire:model="f.tanggal_catat">
                                    @error('f.tanggal_catat')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Kapan surat ini melewati meja kita.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- ADMINISTRASI ---------- --}}
                    <div class="dsb-kartu k-5 sr-kartu-borang" style="--aksen: #7c3aed">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-tags-fill"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Administrasi</h3>
                                    <span class="dsb-kartu-sub">Klasifikasi, nomor surat, sifat, dan statusnya</span>
                                </div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="sr-borang sr-borang-duo">
                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-klasifikasi">Klasifikasi surat</label>
                                    <select id="sr-klasifikasi" class="dsb-isian @error('f.klasifikasi_id') is-galat @enderror" wire:model.live="f.klasifikasi_id">
                                        <option value="">Belum diklasifikasi</option>
                                        @foreach ($daftarKlasifikasi as $k)
                                            <option value="{{ $k->id }}" @selected((string) $this->f['klasifikasi_id'] === (string) $k->id)>
                                                {{ $k->label }} ({{ $k->retensi_teks }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('f.klasifikasi_id')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">
                                        Menentukan surat ini urusan apa, DAN berapa lama wajib disimpan.
                                        Tanggal boleh musnahnya dihitung dari sini saat disimpan.
                                    </span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-nomor">Nomor surat</label>

                                    @if ($keluar)
                                        {{-- Atribut centang ditulis dalam SATU baris: wire:model tidak
                                             pernah menggambar `checked` sendiri, jadi @checked yang menulisnya. --}}
                                        <label class="sr-nomor-saklar">
                                            <input type="checkbox" wire:model.live="nomorOtomatis" @checked($nomorOtomatis)>
                                            <span>Buatkan nomornya otomatis</span>
                                        </label>
                                    @endif

                                    <div class="sr-nomor-baris">
                                        <input id="sr-nomor" type="text" maxlength="120"
                                            class="dsb-isian @error('f.nomor_surat') is-galat @enderror"
                                            wire:model="f.nomor_surat"
                                            placeholder="{{ $masuk ? '005/DPK/XI/2026' : '016/03.D/ACM/VI/2026' }}"
                                            @readonly($nomorDikunci)>

                                        @if ($keluar && ! $nomorOtomatis)
                                            <button type="button" class="dsb-tombol is-lembut" style="--ikon: #a16207"
                                                wire:click="pakaiNomorUsulan" wire:target="pakaiNomorUsulan">
                                                <i class="bi bi-arrow-repeat"></i><span>Hitung ulang</span>
                                            </button>
                                        @endif
                                    </div>

                                    @error('f.nomor_surat')<div class="dsb-galat">{{ $message }}</div>@enderror

                                    @if ($adaArtiNomor)
                                        <div class="sr-nomor-arti">
                                            @foreach ($potonganNomor as $potong)
                                                <span><b>{{ $potong['isi'] }}</b><i>{{ $potong['arti'] }}</i></span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($masuk)
                                        <span class="dsb-kartu-sub">Nomor yang tertulis di suratnya. Boleh kosong.</span>
                                    @elseif ($nomorOtomatis)
                                        <span class="dsb-kartu-sub">
                                            Bulan dan tahun ikut <b>tanggal surat</b>. Hilangkan centangnya
                                            kalau mau diketik sendiri.
                                        </span>
                                    @else
                                        <span class="dsb-kartu-sub">
                                            Yang tersimpan adalah yang tertulis di sini, dan surat keluar
                                            berikutnya menyambung dari nomor ini.
                                        </span>
                                    @endif
                                </div>


                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-sifat">Sifat <span class="dsb-wajib">*</span></label>
                                    <select id="sr-sifat" class="dsb-isian @error('f.sifat') is-galat @enderror" wire:model="f.sifat">
                                        @foreach (\App\Models\Surat\Surat::SIFAT as $kunci => $label)
                                            <option value="{{ $kunci }}" @selected($this->f['sifat'] === $kunci)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('f.sifat')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Segera & rahasia ditandai garis warna di daftar.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-status">Status <span class="dsb-wajib">*</span></label>
                                    <select id="sr-status" class="dsb-isian @error('f.status') is-galat @enderror" wire:model="f.status">
                                        @foreach (\App\Models\Surat\Surat::STATUS as $kunci => $label)
                                            <option value="{{ $kunci }}" @selected($this->f['status'] === $kunci)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('f.status')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Biarkan "Belum ditindak" kalau baru dicatat.</span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label">Karyawan yang menangani</label>
                                    <div class="sr-pilih-kotak @error('f.penanggung_jawab_id') is-galat @enderror">
                                        @if ($karyawan)
                                            <span class="sr-pilih-terpilih">
                                                <span class="sr-pilih-ikon"><i class="bi bi-person-badge"></i></span>
                                                <span class="sr-pilih-teks">
                                                    <b>{{ $karyawan['nama'] }}</b>
                                                    <span>{{ $karyawan['jabatan'] ?: 'Jabatan belum dicatat' }}</span>
                                                </span>
                                            </span>
                                        @else
                                            <span class="sr-pilih-belum">Belum ditunjuk</span>
                                        @endif

                                        <span class="sr-pilih-aksi">
                                            <button type="button" class="dsb-tombol is-lembut is-mungil" style="--ikon: #7c3aed"
                                                data-sr-pilih-karyawan data-sr-komponen="{{ $this->getId() }}">
                                                <i class="bi bi-search"></i><span>{{ $karyawan ? 'Ganti' : 'Pilih karyawan' }}</span>
                                            </button>
                                            @if ($karyawan)
                                                <button type="button" class="dsb-tombol is-lembut is-mungil" style="--ikon: #64748b"
                                                    wire:click="kosongkanPenanggungJawab">
                                                    <i class="bi bi-x-lg"></i><span>Kosongkan</span>
                                                </button>
                                            @endif
                                        </span>
                                    </div>
                                    @error('f.penanggung_jawab_id')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">
                                        Satu nama yang memegang urusan surat ini &mdash; yang ditanya lebih dulu
                                        ketika orang bertanya "surat ini sekarang di siapa?". Berbeda dari
                                        disposisi, yang bisa beberapa dan punya tenggat sendiri.
                                    </span>
                                </div>

                                
                                                            </div>
                        </div>
                    </div>

                    {{-- ---------- RINGKASAN & PINDAIAN ----------
                         Dua medan paling "longgar" dipindah ke kartunya sendiri
                         selebar penuh. Di dalam dua kartu atas, keduanya memaksa
                         kartunya tumbuh jauh melebihi pasangannya dan menyisakan
                         lajur kosong panjang di sebelahnya. --}}
                    <div class="dsb-kartu k-12 sr-kartu-borang" style="--aksen: #0891b2">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #0891b2"><i class="bi bi-paperclip"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Ringkasan &amp; berkasnya</h3>
                                    <span class="dsb-kartu-sub">Isi suratnya dengan kalimat sendiri, dan di mana kertasnya disimpan</span>
                                </div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="sr-borang sr-borang-duo">
<div class="dsb-medan sr-tumbuh">
                                    <label class="dsb-label" for="sr-ringkasan">Ringkasan isi</label>
                                    <textarea id="sr-ringkasan" rows="6" maxlength="5000"
                                        class="dsb-isian @error('f.ringkasan') is-galat @enderror"
                                        wire:model="f.ringkasan"
                                        placeholder="Mereka meminta pelatihan untuk 20 karyawan pada Januari. Minta balasan sebelum 10 Desember."></textarea>
                                    @error('f.ringkasan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">
                                        Beberapa kalimat dengan bahasa sendiri, supaya orang lain tidak perlu
                                        membuka pindaiannya untuk tahu isinya.
                                    </span>
                                </div>

{{-- Dua medan ini ditumpuk dalam SATU sel.
                                     Tanpa pembungkus, kisinya menaruh "Pindaian surat" di
                                     baris berikutnya lajur kiri — dan lajur kanan di bawah
                                     "Tempat berkas" menganga kosong. --}}
                                <div class="sr-kolom">
<div class="dsb-medan">
                                    <label class="dsb-label" for="sr-lokasi">Tempat berkas fisiknya</label>
                                    <input id="sr-lokasi" type="text" maxlength="180"
                                        class="dsb-isian @error('f.lokasi_fisik') is-galat @enderror"
                                        wire:model="f.lokasi_fisik" placeholder="Lemari 2, Boks B, Berkas 14">
                                    @error('f.lokasi_fisik')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">
                                        Tulis sesuai cara lemari di kantor ini disusun. Kolom inilah yang
                                        menghemat waktu saat kertasnya dicari bertahun-tahun kemudian.
                                    </span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-berkas">Pindaian surat</label>
                                    {{-- Kotak pilih berkas bawaan peramban diganti kotak sendiri.
                                         Yang bawaan menulis "Choose files / No file chosen" dalam bahasa
                                         Inggris, lebarnya berubah-ubah menurut jumlah berkas, dan tidak
                                         bisa diberi keterangan. Input aslinya tetap ada — hanya
                                         ditindih, jadi cara memilihnya tidak berubah sama sekali. --}}
                                    <label class="sr-unggah @error('unggahan.*') is-galat @enderror">
                                        <input id="sr-berkas" type="file" multiple class="sr-unggah-asli"
                                            wire:model="unggahan" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                        <span class="sr-unggah-ikon"><i class="bi bi-cloud-arrow-up"></i></span>
                                        <span class="sr-unggah-teks">
                                            <b>Pilih berkas pindaian</b>
                                            <span>PDF, foto, atau Word &middot; maksimal 20 MB per lembar</span>
                                        </span>
                                    </label>
                                    @error('unggahan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    @error('unggahan.*')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    @error('berkas.*')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <div wire:loading wire:target="unggahan" class="dsb-kartu-sub">
                                        <span class="dsb-putar is-kecil"></span> Mengunggah berkas…
                                    </div>

                                    @if ($adaBerkasBaru)
                                        <div class="sr-antrean">
                                            @foreach ($berkas as $urutan => $b)
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
                                        <span class="dsb-kartu-sub">
                                            {{ count($berkas) }} berkas siap disimpan bersama suratnya.
                                        </span>
                                    @endif

                                    <span class="dsb-kartu-sub">
                                        Boleh pilih beberapa lembar sekaligus, dan boleh memilih lagi &mdash;
                                        yang sudah dipilih tidak hilang. Berkasnya disimpan tertutup: hanya
                                        bisa dibuka dari halaman suratnya, bukan dari alamat umum.
                                    </span>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- SIMPAN ---------- --}}
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-isi sr-baris-aksi">
                            <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-save"></i></span>
                            <div class="sr-baris-teks">
                                <b>{{ $isEdit ? 'Simpan perubahan' : 'Simpan ke buku agenda' }}</b>
                                <span class="dsb-kartu-sub">
                                    Sesudah disimpan, surat ini bisa diberi disposisi — siapa yang
                                    mengerjakan, apa perintahnya, dan kapan tenggatnya.
                                </span>
                            </div>
                            <a href="{{ route('admin.surat.index') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                <i class="bi bi-x-lg"></i><span>Batal</span>
                            </a>
                            <button type="submit" class="dsb-tombol is-utama" wire:loading.attr="disabled" wire:target="save,berkas">
                                <span wire:loading.remove wire:target="save">
                                    <i class="bi bi-check2-circle"></i><span>{{ $isEdit ? 'Simpan' : 'Catat Surat' }}</span>
                                </span>
                                <span wire:loading wire:target="save">
                                    <span class="dsb-putar is-kecil"></span><span>Menyimpan…</span>
                                </span>
                            </button>
                        </div>
                    </div>

                </div>
            </section>
        </form>
    {{-- Daftar karyawan untuk jendela pilih. Ditaruh sebagai data, bukan
         dirangkai ke dalam skrip: nama orang bisa memuat tanda kutip, dan
         merangkainya ke dalam kode membuat satu nama bisa mematahkan
         seluruh halaman. Tanda < > & ' " dikodekan supaya aman di <script>. --}}
    <script type="application/json" id="sr-daftar-karyawan">{!! json_encode($daftarKaryawan, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <script>
        (() => {
            // Satu penangan untuk seluruh halaman, dipasang SEKALI.
            // Tombolnya lahir dan mati tiap Livewire menggambar ulang, jadi
            // penangannya menempel di document, bukan di tombolnya.
            if (window.__srKaryawanTerpasang) return;
            window.__srKaryawanTerpasang = true;

            const ambilDaftar = () => {
                const simpul = document.getElementById('sr-daftar-karyawan');
                if (! simpul) return [];
                try { return JSON.parse(simpul.textContent || '[]'); } catch (e) { return []; }
            };

            document.addEventListener('click', (peristiwa) => {
                const tombol = peristiwa.target.closest('[data-sr-pilih-karyawan]');
                if (! tombol || typeof Swal === 'undefined') return;

                peristiwa.preventDefault();

                const daftar = ambilDaftar();
                const komponen = tombol.dataset.srKomponen;

                Swal.fire({
                    title: 'Pilih karyawan',
                    html: '<input id="srCariKaryawan" class="sr-pilih-cari" placeholder="Ketik nama atau jabatan..." autocomplete="off"><div id="srDaftarKaryawan" class="sr-pilih-daftar"></div>',
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    width: 460,
                    customClass: { cancelButton: 'btn-glossy-cancel' },
                    didOpen: () => {
                        const kotak = document.getElementById('srDaftarKaryawan');
                        const cari = document.getElementById('srCariKaryawan');

                        const gambar = (kata) => {
                            const k = (kata || '').trim().toLowerCase();
                            const cocok = daftar.filter((o) => (o.nama + ' ' + (o.jabatan || '')).toLowerCase().includes(k));

                            kotak.textContent = '';

                            if (cocok.length === 0) {
                                const p = document.createElement('p');
                                p.className = 'sr-pilih-nihil';
                                p.textContent = 'Tidak ada karyawan yang cocok.';
                                kotak.appendChild(p);
                                return;
                            }

                            cocok.forEach((o) => {
                                // Dirakit lewat DOM, bukan innerHTML: nama orang
                                // tidak boleh bisa menyisipkan markup.
                                const baris = document.createElement('button');
                                baris.type = 'button';
                                baris.className = 'sr-pilih-baris';
                                baris.dataset.id = o.id;

                                const nama = document.createElement('b');
                                nama.textContent = o.nama;

                                const jabatan = document.createElement('span');
                                jabatan.textContent = o.jabatan || 'Jabatan belum dicatat';

                                baris.appendChild(nama);
                                baris.appendChild(jabatan);
                                kotak.appendChild(baris);
                            });
                        };

                        gambar('');
                        cari.addEventListener('input', () => gambar(cari.value));

                        kotak.addEventListener('click', (ev) => {
                            const baris = ev.target.closest('.sr-pilih-baris');
                            if (! baris) return;

                            const inti = window.Livewire && Livewire.find(komponen);
                            if (inti) inti.call('pilihPenanggungJawab', baris.dataset.id);

                            Swal.close();
                        });

                        cari.focus();
                    },
                });
            });
        })();
    </script>

    </div>
</div>
