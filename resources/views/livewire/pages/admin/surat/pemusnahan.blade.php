@section('title')
Penyusutan Arsip || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.surat.partials.surat-gaya')

    @php
        $adaCalon = $jumlahCalon !== 0;
        $jumlahPilih = count($terpilih);
        $adaPilihan = $jumlahPilih !== 0;
    @endphp

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-fire"></i> Mode Arsip Surat</span>
                <h1 class="dsb-judul">Penyusutan Arsip</h1>
                <p class="dsb-hero-ket">
                    Surat yang masa simpannya sudah habis dikumpulkan di sini, lalu dimusnahkan
                    <b>berkas kertasnya</b> dengan satu berita acara. Catatannya di buku agenda
                    tetap ada &mdash; justru itu yang menjawab "ke mana surat nomor sekian?"
                    bertahun-tahun kemudian.
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.surat.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                    <i class="bi bi-grid-fill"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('admin.surat.index', ['tab' => 'retensi']) }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #7c3aed">
                    <i class="bi bi-archive"></i><span>Buku Agenda</span>
                </a>
            </div>
        </header>

        <section class="dsb-bagian">
            <div class="dsb-rak sr-rak-puncak">
                {{-- ---------- CALON ---------- --}}
                <div class="dsb-kartu k-7">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-archive"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Masa simpan sudah habis</h3>
                                <span class="dsb-kartu-sub">{{ $jumlahCalon }} surat &middot; belum masuk berita acara mana pun</span>
                            </div>
                        </div>
                        @if ($adaCalon && $boleh)
                            <span class="sr-kepala-aksi">
                                <button type="button" wire:click="pilihSemua" class="dsb-tombol is-lembut is-mungil" style="--ikon: #0284c7">
                                    <i class="bi bi-check2-square"></i><span>Pilih semua</span>
                                </button>
                                @if ($adaPilihan)
                                    <button type="button" wire:click="kosongkanPilihan" class="dsb-tombol is-lembut is-mungil" style="--ikon: #64748b">
                                        <i class="bi bi-x-square"></i><span>Kosongkan</span>
                                    </button>
                                @endif
                            </span>
                        @endif
                    </div>

                    @if (! $adaCalon)
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-check2-circle"></i></span>
                            <p class="dsb-kosong-judul">Tidak ada arsip yang jatuh tempo</p>
                            <p class="dsb-kosong-ket">
                                Semua surat masih di dalam masa simpannya, atau sudah masuk berita acara.
                            </p>
                        </div>
                    @else
                        @foreach ($calon as $s)
                            {{-- Atribut centang ditulis SATU baris: wire:model tidak pernah
                                 menggambar `checked` sendiri, @checked yang menuliskannya. --}}
                            <label class="sr-musnah-baris">
                                <input type="checkbox" value="{{ $s->id }}" wire:model.live="terpilih" @checked(in_array((int) $s->id, array_map('intval', $terpilih), true)) @disabled(! $boleh)>
                                <span class="sr-musnah-teks">
                                    <b>{{ $s->nomor_agenda }} &middot; {{ $s->perihal }}</b>
                                    <span>
                                        {{ $s->klasifikasi?->label ?: 'Belum diklasifikasi' }}
                                        &middot; surat {{ $s->tanggal_surat_teks }}
                                        &middot; {{ $s->lokasi_fisik ?: 'tempat berkas belum dicatat' }}
                                    </span>
                                </span>
                                <span class="sr-musnah-tgl">
                                    boleh musnah<br><b>{{ $s->musnah_pada_teks }}</b>
                                </span>
                            </label>
                        @endforeach

                        @if ($calon->hasPages())
                            <div class="sr-halaman">{{ $calon->links('vendor.pagination') }}</div>
                        @endif
                    @endif
                </div>

                {{-- ---------- BERITA ACARA ---------- ---}}
                <div class="dsb-kartu k-5">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #b91c1c"><i class="bi bi-file-earmark-text"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Buat berita acara</h3>
                                <span class="dsb-kartu-sub">{{ $jumlahPilih }} surat dipilih</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        @if (! $boleh)
                            <p class="dsb-kartu-sub" style="margin: 0;">
                                Anda bisa melihat daftarnya, tetapi tidak membuat berita acara.
                                Mintalah izin <b>Kelola Pemusnahan Arsip</b> kepada admin.
                            </p>
                        @else
                            <div class="sr-borang">
                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="pm-tanggal">Tanggal pemusnahan <span class="dsb-wajib">*</span></label>
                                    <input id="pm-tanggal" type="date" class="dsb-isian @error('tanggal') is-galat @enderror" wire:model="tanggal">
                                    @error('tanggal')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Tanggal kertasnya benar-benar dimusnahkan.</span>
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="pm-pj">Penanggung jawab</label>
                                    <input id="pm-pj" type="text" maxlength="180" class="dsb-isian @error('penanggungJawab') is-galat @enderror"
                                        wire:model="penanggungJawab" placeholder="Nama yang menyaksikan & bertanggung jawab">
                                    @error('penanggungJawab')<div class="dsb-galat">{{ $message }}</div>@enderror
                                </div>

                                <div class="dsb-medan sr-lebar">
                                    <label class="dsb-label" for="pm-catatan">Catatan</label>
                                    <textarea id="pm-catatan" rows="3" maxlength="2000" class="dsb-isian @error('catatan') is-galat @enderror"
                                        wire:model="catatan" placeholder="Dimusnahkan dengan mesin penghancur di kantor, disaksikan Bagian Umum."></textarea>
                                    @error('catatan')<div class="dsb-galat">{{ $message }}</div>@enderror
                                    <span class="dsb-kartu-sub">Cara dan saksinya. Inilah yang ditanyakan saat pemeriksaan.</span>
                                </div>
                            </div>

                            @error('terpilih')<div class="dsb-galat" style="margin-top:.4rem">{{ $message }}</div>@enderror

                            <button type="button" class="dsb-tombol is-utama pcek-konfirmasi" style="margin-top: .8rem; width: 100%;"
                                data-action="buatBeritaAcara"
                                data-title="Buat berita acara untuk {{ $jumlahPilih }} surat?"
                                data-text="Statusnya berubah jadi Dimusnahkan dan tidak bisa dikembalikan lewat layar. Catatan suratnya tetap ada di buku agenda."
                                data-confirm="Ya, buat berita acara" data-icon="warning"
                                @disabled(! $adaPilihan)>
                                <i class="bi bi-file-earmark-check"></i><span>Buat berita acara</span>
                            </button>
                        @endif
                    </div>

                    @if ($beritaAcara->isNotEmpty())
                        <div class="dsb-kartu-kepala" style="border-top: 1px solid #f1f5f9;">
                            <div class="dsb-kartu-kepala-kiri">
                                <div>
                                    <h3 class="dsb-kartu-judul" style="font-size:.92rem">Berita acara terakhir</h3>
                                    <span class="dsb-kartu-sub">Klik untuk mencetaknya</span>
                                </div>
                            </div>
                        </div>
                        @foreach ($beritaAcara as $ba)
                            <a href="{{ route('admin.surat.berita-acara', $ba) }}" target="_blank" rel="noopener" class="sr-ba-baris">
                                <i class="bi bi-file-earmark-pdf"></i>
                                <span class="sr-musnah-teks">
                                    <b>{{ $ba->nomor }}</b>
                                    <span>{{ $ba->tanggal_teks }} &middot; {{ $ba->surat_count }} surat &middot; {{ $ba->pembuat?->name ?: 'tidak diketahui' }}</span>
                                </span>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>
    </div>

    @include('livewire.layout.sweetalert')
</div>
