@section('title')
{{ $surat->nomor_agenda }} || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.surat.partials.surat-gaya')

    @php
        // Semua perbandingan dirakit di sini, bukan di kondisi direktif blok.
        $masuk = $surat->arah === 'masuk';
        $warnaArah = $masuk ? '#0ea5c6' : '#17a974';
        $ikonArah = $masuk ? 'bi-envelope-arrow-down' : 'bi-envelope-arrow-up';
        $warnaSifat = ['biasa' => '#64748b', 'segera' => '#d97706', 'sangat_segera' => '#e11d48', 'rahasia' => '#7c3aed'];
        $warnaStatus = ['baru' => '#d97706', 'diproses' => '#0284c7', 'selesai' => '#16a34a', 'diarsipkan' => '#64748b'];
        $warnaDis = ['menunggu' => '#d97706', 'dikerjakan' => '#0284c7', 'selesai' => '#16a34a'];

        $lampiran = $surat->lampiran;
        $disposisi = $surat->disposisi->sortByDesc('id');
        $adaLampiran = $lampiran->isNotEmpty();
        $adaDisposisi = $disposisi->isNotEmpty();
        $adaRingkasan = filled($surat->ringkasan);
        $bolehMusnah = $surat->sudah_boleh_musnah;
        $adaBerkasBaru = count($berkasBaru) !== 0;
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi {{ $ikonArah }}"></i> {{ $surat->arah_label }}</span>
                <h1 class="dsb-judul">{{ $surat->perihal }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <b class="sr-agenda">{{ $surat->nomor_agenda }}</b>
                        <span class="dsb-pisah">•</span>{{ $surat->pihak_label }}: {{ $surat->pihak }}
                        <span class="dsb-pisah">•</span>{{ $surat->tanggal_surat_teks }}
                    </span>
                    <span class="d-block">
                        <span class="dsb-lencana" style="--c: {{ $warnaStatus[$surat->status] ?? '#64748b' }}">{{ $surat->status_label }}</span>
                        <span class="dsb-lencana" style="--c: {{ $warnaSifat[$surat->sifat] ?? '#64748b' }}">{{ $surat->sifat_label }}</span>
                        @if ($bolehMusnah)
                            <span class="dsb-lencana" style="--c: #7c3aed">Masa simpan habis</span>
                        @endif
                    </span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.surat.index') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
                @if ($bolehUbah)
                    <a href="{{ route('admin.surat.edit', $surat) }}" wire:navigate class="dsb-tombol is-utama">
                        <i class="bi bi-pencil"></i><span>Ubah</span>
                    </a>
                @endif
                @if ($bolehHapus)
                    <button type="button" class="dsb-tombol is-lembut pcek-konfirmasi" style="--ikon: #e11d48"
                        data-action="hapusSurat"
                        data-title="Hapus surat {{ $surat->nomor_agenda }}?"
                        data-text="Surat dikeluarkan dari daftar. Nomor agendanya tetap dipegang supaya tidak dipakai dua kali, dan pindaiannya tidak dibuang."
                        data-confirm="Ya, hapus" data-icon="warning">
                        <i class="bi bi-trash"></i><span>Hapus</span>
                    </button>
                @endif
            </div>
        </header>

        {{-- sr-rak-puncak: tiap kartu berhenti di akhir isinya. Diregangkan
             sama tinggi, kartu rincian menyisakan lubang putih sebesar selisih
             dengan kartu di sebelahnya — dan selisih itu berubah-ubah menurut
             banyaknya pindaian, jadi tidak ada tinggi yang "pas" untuk keduanya. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak sr-rak-puncak">
                {{-- ---------- RINCIAN ---------- --}}
                <div class="dsb-kartu k-7">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: {{ $warnaArah }}"><i class="bi bi-file-text"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Rincian surat</h3>
                                <span class="dsb-kartu-sub">Seperti tercatat di buku agenda</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        <dl class="sr-rincian">
                            <div>
                                <dt>Nomor agenda</dt>
                                <dd class="sr-agenda">{{ $surat->nomor_agenda }}</dd>
                            </div>
                            <div>
                                <dt>Nomor surat</dt>
                                <dd>{{ $surat->nomor_surat ?: 'Tidak dicatat' }}</dd>
                            </div>
                            <div>
                                <dt>{{ $surat->pihak_label }}</dt>
                                <dd>{{ $surat->pihak }}</dd>
                            </div>
                            <div>
                                <dt>Tanggal surat</dt>
                                <dd>{{ $surat->tanggal_surat_teks }}</dd>
                            </div>
                            <div>
                                <dt>Tanggal {{ $masuk ? 'diterima' : 'dikirim' }}</dt>
                                <dd>{{ $surat->tanggal_catat_teks }}</dd>
                            </div>
                            <div>
                                <dt>Klasifikasi</dt>
                                <dd>{{ $surat->klasifikasi?->label ?: 'Belum diklasifikasi' }}</dd>
                            </div>
                            <div>
                                <dt>Masa simpan</dt>
                                <dd>{{ $surat->klasifikasi?->retensi_teks ?: 'Belum ditentukan' }}</dd>
                            </div>
                            <div>
                                <dt>Boleh dimusnahkan</dt>
                                <dd>{{ $surat->musnah_pada_teks }}</dd>
                            </div>
                            <div>
                                <dt>Tempat berkas</dt>
                                <dd>{{ $surat->lokasi_fisik ?: 'Belum dicatat' }}</dd>
                            </div>
                            <div>
                                <dt>Dicatat oleh</dt>
                                <dd>{{ $surat->pencatat?->name ?: 'Tidak diketahui' }}</dd>
                            </div>
                        </dl>

                        @if ($adaRingkasan)
                            <div class="dsb-medan" style="margin-top: .85rem;">
                                <span class="dsb-label">Ringkasan isi</span>
                                <p class="sr-dis-instruksi" style="margin: 0;">{{ $surat->ringkasan }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ---------- STATUS & LAMPIRAN ---------- --}}
                <div class="dsb-kartu k-5">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-flag-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Status & pindaian</h3>
                                <span class="dsb-kartu-sub">Tahap pengerjaan dan berkasnya</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        @if ($bolehUbah)
                            <span class="dsb-label">Ubah status</span>
                            <div class="dsb-chip-deret" style="margin-bottom: .8rem;">
                                @foreach (\App\Models\Surat\Surat::STATUS as $kunci => $label)
                                    @php $iniStatus = $surat->status === $kunci; @endphp
                                    <button type="button" wire:click="ubahStatus('{{ $kunci }}')"
                                        class="dsb-tombol is-mungil {{ $iniStatus ? 'is-utama' : 'is-lembut' }}"
                                        style="--ikon: {{ $warnaStatus[$kunci] ?? '#64748b' }}"
                                        @disabled($iniStatus)>
                                        <i class="bi bi-circle-fill" style="font-size:.5rem"></i><span>{{ $label }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if ($bolehUbah)
                            <div class="dsb-medan">
                                <label class="dsb-label" for="sr-berkas-baru">Tambah pindaian</label>
                                <input id="sr-berkas-baru" type="file" multiple
                                    class="dsb-isian @error('unggahan.*') is-galat @enderror"
                                    wire:model="unggahan" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                @error('unggahan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                @error('unggahan.*')<div class="dsb-galat">{{ $message }}</div>@enderror
                                @error('berkasBaru.*')<div class="dsb-galat">{{ $message }}</div>@enderror
                                <div wire:loading wire:target="unggahan" class="dsb-kartu-sub">
                                    <span class="dsb-putar is-kecil"></span> Mengunggah…
                                </div>

                                @if ($adaBerkasBaru)
                                    <div class="sr-antrean">
                                        @foreach ($berkasBaru as $urutan => $b)
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
                                    <button type="button" wire:click="unggahBerkas" wire:target="unggahBerkas"
                                        class="dsb-tombol is-utama is-mungil" style="margin-top:.4rem">
                                        <i class="bi bi-upload"></i><span>Simpan {{ count($berkasBaru) }} berkas</span>
                                    </button>
                                @endif

                                <span class="dsb-kartu-sub">
                                    Boleh pilih beberapa lembar sekaligus, dan boleh memilih lagi —
                                    yang sudah dipilih tidak hilang.
                                </span>
                            </div>
                        @endif
                    </div>

                    @if ($adaLampiran)
                        @foreach ($lampiran as $l)
                            <div class="sr-lampiran-baris">
                                <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi {{ $l->ikon }}"></i></span>
                                <span class="sr-lampiran-teks">
                                    <b>{{ $l->nama }}</b>
                                    <span>{{ $l->ukuran_teks }} · {{ $l->pengunggah?->name ?: 'tidak diketahui' }}</span>
                                </span>
                                <a href="{{ route('admin.surat.lampiran', [$surat, $l]) }}" target="_blank" rel="noopener"
                                    class="dsb-tabel-btn" title="Buka pindaian">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                @if ($bolehHapus)
                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi" title="Hapus pindaian"
                                        data-action="hapusLampiran" data-arg="{{ $l->id }}"
                                        data-title="Hapus pindaian ini?"
                                        data-text="{{ $l->nama }} dihapus dari server dan tidak bisa dikembalikan."
                                        data-confirm="Ya, hapus" data-icon="warning">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-file-earmark"></i></span>
                            <p class="dsb-kosong-judul">Belum ada pindaian</p>
                            <p class="dsb-kosong-ket">
                                Arsip tetap sah tanpa pindaian — tempat berkas fisiknya yang penting.
                                Pindaian menolong kalau kertasnya sedang dipinjam orang.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- ---------- DISPOSISI ---------- --}}
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #b91c1c"><i class="bi bi-person-check"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Disposisi</h3>
                                <span class="dsb-kartu-sub">Siapa yang mengerjakan, apa perintahnya, kapan tenggatnya</span>
                            </div>
                        </div>
                    </div>

                    @if ($bolehCatat)
                        <div class="dsb-kartu-isi">
                            <div class="sr-borang">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-dis-user">Kepada (akun lemon)</label>
                                    <select id="sr-dis-user" class="dsb-isian" wire:model="disKepadaUser">
                                        <option value="">— pilih akun —</option>
                                        @foreach ($daftarUser as $us)
                                            <option value="{{ $us->id }}" @selected((string) $this->disKepadaUser === (string) $us->id)>{{ $us->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="dsb-kartu-sub">Hanya yang boleh membuka arsip surat.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-dis-nama">Atau tulis namanya</label>
                                    <input id="sr-dis-nama" type="text" maxlength="180"
                                        class="dsb-isian @error('disKepadaNama') is-galat @enderror"
                                        wire:model="disKepadaNama" placeholder="Pak Budi — Bagian Umum">
                                    @error('disKepadaNama')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Untuk orang yang tidak punya akun lemon.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="sr-dis-batas">Tenggat</label>
                                    <input id="sr-dis-batas" type="date" class="dsb-isian @error('disBatas') is-galat @enderror" wire:model="disBatas">
                                    @error('disBatas')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Boleh kosong kalau tidak ada tenggat.</span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="sr-dis-instruksi">Instruksi <span class="dsb-wajib">*</span></label>
                                    <textarea id="sr-dis-instruksi" rows="2" maxlength="2000"
                                        class="dsb-isian @error('disInstruksi') is-galat @enderror"
                                        wire:model="disInstruksi"
                                        placeholder="Siapkan balasan dan koordinasikan jadwalnya dengan bagian pelatihan."></textarea>
                                    @error('disInstruksi')<div class="dsb-galat">{{ $message }}</div>@enderror
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <button type="button" wire:click="tambahDisposisi" class="dsb-tombol is-utama"
                                        wire:loading.attr="disabled" wire:target="tambahDisposisi">
                                        <i class="bi bi-plus-lg"></i><span>Tambah disposisi</span>
                                    </button>
                                    <span class="dsb-kartu-sub">
                                        Surat yang masih "Belum ditindak" otomatis jadi "Sedang diproses"
                                        begitu disposisi pertamanya dibuat.
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($adaDisposisi)
                        @foreach ($disposisi as $d)
                            @php $telat = $d->telat; @endphp
                            <div class="sr-dis-baris" style="--c: {{ $telat ? '#b91c1c' : ($warnaDis[$d->status] ?? '#64748b') }}">
                                <span class="dsb-ikon is-kecil"><i class="bi bi-person-check"></i></span>
                                <span class="sr-dis-isi">
                                    <span class="sr-dis-instruksi">{{ $d->instruksi }}</span>
                                    <span class="sr-dis-meta">
                                        <span><b>{{ $d->kepada_teks }}</b></span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $d->batas_teks }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>dibuat {{ $d->pembuat?->name ?: 'sistem' }}, {{ $d->created_at->locale('id')->diffForHumans() }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    @if ($telat)
                                        <span class="dsb-lencana" style="--c: #b91c1c">Lewat tenggat</span>
                                    @else
                                        <span class="dsb-lencana" style="--c: {{ $warnaDis[$d->status] ?? '#64748b' }}">{{ $d->status_label }}</span>
                                    @endif
                                </span>
                                @if ($bolehUbah)
                                    @foreach (\App\Models\Surat\SuratDisposisi::STATUS as $kunci => $label)
                                        @php $iniDis = $d->status === $kunci; @endphp
                                        @if (! $iniDis)
                                            <button type="button" wire:click="ubahStatusDisposisi({{ $d->id }}, '{{ $kunci }}')"
                                                class="dsb-tombol is-lembut is-mungil" style="--ikon: {{ $warnaDis[$kunci] ?? '#64748b' }}"
                                                title="Tandai {{ $label }}">
                                                <span>{{ $label }}</span>
                                            </button>
                                        @endif
                                    @endforeach
                                @endif
                                @if ($bolehHapus)
                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi" title="Hapus disposisi"
                                        data-action="hapusDisposisi" data-arg="{{ $d->id }}"
                                        data-title="Hapus disposisi ini?"
                                        data-text="Catatan tugasnya hilang dan tidak bisa dikembalikan."
                                        data-confirm="Ya, hapus" data-icon="warning">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-person-check"></i></span>
                            <p class="dsb-kosong-judul">Belum ada disposisi</p>
                            <p class="dsb-kosong-ket">
                                Surat yang hanya dicatat tidak pernah selesai sendiri. Tentukan siapa
                                yang mengerjakan dan kapan tenggatnya.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    {{--
        Toast glossy milik lemon (swal-success / swal-error), partial yang sama
        dipakai Pesanan Toko. BUKAN orcha/partials/skrip: itu juga memasang
        penangan khas Orcha (orcha-sukses-pindah dan kawan-kawannya) yang tidak
        punya arti di layar ini.

        Penangan tombol .pcek-konfirmasi tidak perlu dimuat di sini — sudah
        terpasang global di layout templateindex.
    --}}
    @include('livewire.layout.sweetalert')
</div>
