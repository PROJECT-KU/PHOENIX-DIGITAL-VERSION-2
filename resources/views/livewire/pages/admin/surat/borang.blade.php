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
                <div class="dsb-rak sr-rak-puncak">
                    {{-- ---------- IDENTITAS SURAT ---------- --}}
                    <div class="dsb-kartu k-6">
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
                            <div class="sr-borang">
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

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-ringkasan">Ringkasan isi</label>
                                    <textarea id="sr-ringkasan" rows="3" maxlength="5000"
                                        class="dsb-isian @error('f.ringkasan') is-galat @enderror"
                                        wire:model="f.ringkasan"
                                        placeholder="Mereka meminta pelatihan untuk 20 karyawan pada Januari. Minta balasan sebelum 10 Desember."></textarea>
                                    @error('f.ringkasan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">
                                        Beberapa kalimat dengan bahasa sendiri, supaya orang lain tidak perlu
                                        membuka pindaiannya untuk tahu isinya.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- ADMINISTRASI ---------- --}}
                    <div class="dsb-kartu k-6">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-tags-fill"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Administrasi</h3>
                                    <span class="dsb-kartu-sub">Klasifikasi, sifat, status, dan tempat berkasnya</span>
                                </div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div class="sr-borang">
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
                                            Angka tengah hanya ikut klasifikasi yang dipilih; bulan dan tahun ikut
                                            <b>tanggal surat</b>. Hilangkan centangnya kalau mau diketik sendiri.
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

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-berkas">Pindaian surat</label>
                                    <input id="sr-berkas" type="file" multiple
                                        class="dsb-isian @error('berkas.*') is-galat @enderror"
                                        wire:model="berkas" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                    @error('berkas.*')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <div wire:loading wire:target="berkas" class="dsb-kartu-sub">
                                        <span class="dsb-putar is-kecil"></span> Mengunggah berkas…
                                    </div>
                                    @if ($adaBerkasBaru)
                                        <span class="dsb-kartu-sub">{{ count($berkas) }} berkas siap disimpan.</span>
                                    @endif
                                    <span class="dsb-kartu-sub">
                                        PDF, foto, atau Word. Maksimal 20 MB per lembar. Berkasnya disimpan
                                        tertutup — hanya bisa dibuka dari halaman ini, bukan dari alamat umum.
                                    </span>
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
    </div>
</div>
