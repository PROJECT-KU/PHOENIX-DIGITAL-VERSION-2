@section('title')
Detail Pesan Pelanggan || lemon
@stop
<div>
    {{-- Kerangka mengikuti dasbor (bahasa rupa dsb-*). Lihat partials/dasbor-gaya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.message.partials.pesan-gaya')

    @php
        [$stLabel, $stLencana, $stWarna] = $message->tampilanStatus();
        [$prLabel, $prKelas, $prWarna] = $message->tampilanPrioritas();
        $warnaAvatar = ['#7c3aed', '#2563eb', '#16a34a', '#d97706', '#db2777', '#0891b2'][crc32((string) $message->name) % 6];
        $balasanWa = 'Halo '.$message->name.', terima kasih sudah menghubungi Phoenix Digital (tiket '.$message->ticket.'). ';
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_customer_message');
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_customer_message');
        $kategoriDaftar = config('helpdesk.kategori');
        $kanalDaftar = config('helpdesk.kanal');
        // Tautan ke modul lain hanya ditampilkan kalau memang boleh dibuka,
        // supaya petugas helpdesk tidak mendarat di halaman 403.
        $bolehLihatPelanggan = (bool) auth()->user()?->hasPermission('view_customer');
        $bolehLihatPesanan = (bool) auth()->user()?->hasPermission('view_pemesanantoko');
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Detail Pesan</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-ticket-perforated me-1"></i>{{ $message->ticket }}</span>
                    <span class="d-block">Masuk {{ $message->created_at?->locale('id')->translatedFormat('l, d F Y · H:i') }}</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.customer-message.index') }}" wire:navigate class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
                <button type="button" class="dsb-tombol is-lembut" wire:click="unduhPdf" wire:loading.attr="disabled" wire:target="unduhPdf">
                    <span wire:loading.remove wire:target="unduhPdf" class="pp-isi-tombol"><i class="bi bi-file-earmark-pdf"></i><span>Cetak tiket</span></span>
                    <span wire:loading.inline-flex wire:target="unduhPdf" class="pp-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyiapkan…</span></span>
                </button>
                @if ($bolehUbah)
                    <div class="pp-tunda" x-data="{ buka: false }" x-on:click.outside="buka = false">
                        <button type="button" class="dsb-tombol is-lembut" x-on:click="buka = !buka">
                            <span class="pp-isi-tombol">
                                <i class="bi bi-pause-circle"></i>
                                <span>{{ $message->ditunda() ? 'Ditunda sampai '.$message->tunda_sampai->locale('id')->translatedFormat('d M') : 'Tunda' }}</span>
                            </span>
                        </button>
                        <div class="pp-tunda-menu is-bawah" x-show="buka" x-cloak>
                            @if ($message->ditunda())
                                <button type="button" wire:click="lanjutkanTunda" x-on:click="buka = false">Lanjutkan sekarang</button>
                            @endif
                            @foreach ([1 => 'Tunda 1 hari', 3 => 'Tunda 3 hari', 7 => 'Tunda 7 hari'] as $hari => $label)
                                <button type="button" wire:click="tunda({{ $hari }})" x-on:click="buka = false">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    <button type="button" class="dsb-tombol is-lembut pp-konfirmasi" data-action="tandaiBelumDibaca" data-icon="question"
                        data-title="Tandai belum dibaca?" data-text="Tiket kembali muncul di tab Belum dibaca dan Anda dikembalikan ke daftar." data-confirm="Ya, tandai">
                        <span class="pp-isi-tombol"><i class="bi bi-envelope"></i><span>Belum dibaca</span></span>
                    </button>
                @endif
                @if ($bolehHapus)
                    <button type="button" class="dsb-tombol is-lembut pp-konfirmasi" data-action="tandaiSpam" data-icon="warning"
                        data-title="Tandai tiket ini spam?" data-text="Tiket ditutup dan dipindahkan ke arsip." data-confirm="Ya, spam">
                        <span class="pp-isi-tombol"><i class="bi bi-shield-exclamation"></i><span>Spam</span></span>
                    </button>
                    <button type="button" class="dsb-tombol is-lembut pp-konfirmasi" data-action="arsipkan" data-icon="question"
                        data-title="Arsipkan tiket ini?" data-text="Tiket disimpan di arsip dan masih bisa dikembalikan." data-confirm="Ya, arsipkan">
                        <span class="pp-isi-tombol"><i class="bi bi-archive"></i><span>Arsipkan</span></span>
                    </button>
                @endif
            </div>
        </header>

        {{-- Pindah antar tiket tanpa balik ke daftar dulu. --}}
        @if ($sebelum || $berikut)
            <div class="pp-tetangga" style="margin-bottom: 14px;">
                @if ($sebelum)
                    <a href="{{ route('admin.customer-message.detail', $sebelum->id) }}" wire:navigate class="pp-btn" data-arah="baru" title="Pintasan: k">
                        <i class="bi bi-chevron-left"></i><span>Tiket lebih baru</span>
                    </a>
                @endif
                @if ($berikut)
                    <a href="{{ route('admin.customer-message.detail', $berikut->id) }}" wire:navigate class="pp-btn" data-arah="lama" title="Pintasan: j">
                        <span>Tiket lebih lama</span><i class="bi bi-chevron-right"></i>
                    </a>
                    <button type="button" class="pp-pintasan pp-bantuan-pemicu" title="Daftar pintasan papan tik">
                        <kbd>j</kbd>/<kbd>k</kbd> pindah tiket · <kbd>r</kbd> balas · <kbd>c</kbd> catatan · <kbd>?</kbd> bantuan
                    </button>
                @endif
            </div>
        @endif

        <div class="pp-detail">
            {{-- ================== KOLOM ISI ================== --}}
            <div class="pp-detail-utama">
                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <div class="pp-pengirim">
                            <span class="pp-avatar" style="--av: {{ $warnaAvatar }}; cursor: default;">
                                {{ mb_strtoupper(mb_substr(trim((string) $message->name), 0, 1)) ?: '?' }}
                            </span>
                            <div class="pp-pengirim-teks">
                                <b>{{ $message->name }}</b>
                                <span>{{ $message->email ?: 'Tanpa surel' }}{{ $message->no_telp ? ' · '.$message->no_telp : '' }}</span>
                            </div>
                            <span class="dsb-lencana {{ $stLencana }}">{{ $stLabel }}</span>
                        </div>

                        <div class="pp-penanda" style="margin-top: 14px;">
                            <span class="pp-tanda {{ $prKelas }}"><i class="bi bi-flag-fill"></i>Prioritas {{ $prLabel }}</span>
                            @if ($message->labelKategori())
                                <span class="pp-tanda is-topik"><i class="bi bi-tag-fill"></i>{{ $message->labelKategori() }}</span>
                            @endif
                            @if ($message->petugas)
                                <span class="pp-tanda is-petugas"><i class="bi bi-person-check-fill"></i>{{ $message->petugas->name }}</span>
                            @endif
                            @if ($message->read_at)
                                <span class="pp-tanda is-rendah"><i class="bi bi-envelope-open"></i>Dibaca {{ $message->read_at->locale('id')->diffForHumans() }}</span>
                            @else
                                <span class="pp-tanda is-baru"><i class="bi bi-envelope-exclamation"></i>Belum dibaca</span>
                            @endif
                            @if ($message->pernahSpam())
                                <span class="pp-tanda is-spam"><i class="bi bi-shield-exclamation"></i>Pengirim pernah spam</span>
                            @endif
                            @if ($message->ditunda())
                                <span class="pp-tanda is-tunda"><i class="bi bi-pause-circle"></i>Ditunda sampai {{ $message->tunda_sampai->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                            @endif
                            @if ($message->sudahDibalas())
                                <span class="pp-tanda is-dibalas"><i class="bi bi-check2-all"></i>Dibalas {{ $message->replied_at->locale('id')->diffForHumans() }}</span>
                            @elseif ($message->lewatBatas())
                                <span class="pp-tanda is-lewat"><i class="bi bi-alarm"></i>Lewat batas {{ $message->batasJam() }} jam</span>
                            @else
                                <span class="pp-tanda is-rendah"><i class="bi bi-hourglass-split"></i>Batas balas {{ $message->batasJam() }} jam</span>
                            @endif
                        </div>

                        <blockquote class="pp-kutip">{{ $message->message }}</blockquote>

                        @if ($lampiran->isNotEmpty() || $bolehUbah)
                            <div class="pp-lampiran">
                                <span class="pp-detail-label">Lampiran</span>
                                @forelse ($lampiran as $l)
                                    <div class="pp-lampiran-baris" wire:key="lampiran-{{ $l->id }}">
                                        @if (str_starts_with((string) $l->mime, 'image/'))
                                            {{-- Pratinjau kecil: tanpa ini tiap lampiran harus dibuka
                                                 satu per satu di tab baru cuma untuk tahu isinya. --}}
                                            <a href="{{ route('admin.customer-message.lampiran', $l->id) }}" target="_blank" rel="noopener" class="pp-lampiran-gambar">
                                                <img src="{{ route('admin.customer-message.lampiran', $l->id) }}" alt="Pratinjau {{ $l->nama_asli }}" loading="lazy">
                                            </a>
                                        @else
                                            <i class="bi bi-file-earmark-text"></i>
                                        @endif
                                        <span>
                                            <b>{{ $l->nama_asli }}</b>
                                            <small>{{ $l->ukuranTerbaca() }} · {{ $l->dariAdmin() ? 'ditambahkan admin' : 'dari pelanggan' }}</small>
                                        </span>
                                        <a href="{{ route('admin.customer-message.lampiran', $l->id) }}" target="_blank" rel="noopener"
                                            class="pp-btn pp-btn-ikon" title="Buka lampiran" aria-label="Buka lampiran"><i class="bi bi-box-arrow-up-right"></i></a>
                                        @if ($bolehUbah && $l->dariAdmin())
                                            <button type="button" class="pp-btn pp-btn-ikon is-bahaya pp-konfirmasi" data-action="hapusLampiran" data-arg="{{ $l->id }}" data-icon="warning"
                                                data-title="Hapus lampiran ini?" data-text="{{ $l->nama_asli }} dihapus dari tiket." data-confirm="Ya, hapus"
                                                title="Hapus lampiran" aria-label="Hapus lampiran"><i class="bi bi-trash3"></i></button>
                                        @endif
                                    </div>
                                @empty
                                    <p class="pp-catatan-kecil" style="margin-top: 0 !important;">Belum ada lampiran di tiket ini.</p>
                                @endforelse

                                @if ($bolehUbah)
                                    <div class="pp-lampiran-unggah">
                                        <label class="pp-berkas">
                                            <i class="bi bi-paperclip"></i>
                                            <span>{{ $berkasBaru?->getClientOriginalName() ?: 'Pilih berkas (gambar, PDF, DOCX, XLSX — maks. 8 MB)' }}</span>
                                            <input type="file" wire:model="berkasBaru" accept=".jpg,.jpeg,.png,.webp,.pdf,.docx,.xlsx">
                                        </label>
                                        <button type="button" class="pp-btn" wire:click="unggahLampiran" wire:loading.attr="disabled" wire:target="berkasBaru,unggahLampiran">
                                            <i class="bi bi-upload"></i><span>Tambahkan</span>
                                        </button>
                                    </div>
                                    <span class="pp-catatan-kecil" wire:loading wire:target="berkasBaru">Mengunggah berkas…</span>
                                    @error('berkasBaru')<span class="pp-galat">{{ $message }}</span>@enderror
                                    <p class="pp-catatan-kecil">Lampiran admin ikut terkirim saat balasan dikirim lewat surel dari halaman ini.</p>
                                @endif
                            </div>
                        @endif

                        <div class="pp-balas">
                            <span class="pp-detail-label">Balas pelanggan</span>
                            <div class="pp-balas-tombol">
                                @if ($wa = $message->tautanWa($balasanWa))
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="pp-btn is-wa">
                                        <i class="bi bi-whatsapp"></i><span>Balas WhatsApp</span>
                                    </a>
                                @endif
                                @if ($surel = $message->tautanEmail())
                                    <a href="{{ $surel }}" class="pp-btn">
                                        <i class="bi bi-envelope"></i><span>Balas surel</span>
                                    </a>
                                @endif
                                <button type="button" class="pp-btn pp-salin" data-teks="{{ $message->message }}">
                                    <i class="bi bi-clipboard"></i><span>Salin pesan</span>
                                </button>
                            </div>
                            <p class="pp-catatan-kecil">Balasannya dikirim dari aplikasi WhatsApp atau surel Anda sendiri. Tempelkan isinya di kotak bawah supaya tiket ini punya bukti sudah dijawab.</p>
                        </div>
                    </div>
                </section>

                {{-- ================== CATAT BALASAN ================== --}}
                @if ($bolehUbah)
                    <section class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <span class="pp-detail-label">Catat balasan</span>

                            @if ($templates->isNotEmpty())
                                <div class="pp-template">
                                    @foreach ($templates as $t)
                                        <button type="button" class="pp-template-btn" wire:click="pakaiTemplate('{{ $t->id }}')" title="Isikan template ini ke kotak balasan">
                                            <i class="bi bi-lightning-charge-fill"></i>{{ $t->nama }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pp-tulis">
                                <textarea class="dsb-isian" rows="4" wire:model="balasanIsi" placeholder="Tulis atau tempel isi balasan yang dikirim ke pelanggan…"></textarea>
                                @error('balasanIsi')<span class="pp-galat">{{ $message }}</span>@enderror

                                <div class="pp-tulis-baris">
                                    <label class="pp-centang">
                                        <input type="checkbox" wire:model="balasanSelesai" @checked($balasanSelesai)>
                                        <span>Sekalian tandai tiket selesai</span>
                                    </label>
                                    @if (filled($message->email))
                                        <label class="pp-centang" title="Balasan dikirim dari aplikasi ke {{ $message->email }}">
                                            <input type="checkbox" wire:model.live="balasanKirimSurel" @checked($balasanKirimSurel)>
                                            <span>Kirim langsung lewat surel</span>
                                        </label>
                                    @endif
                                    <select class="dsb-isian pp-pilih" wire:model.live="balasanKanal" aria-label="Kanal balasan">
                                        @foreach ($kanalDaftar as $nilai => $label)
                                            <option value="{{ $nilai }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @php $kirimSurel = $balasanKirimSurel && $balasanKanal === 'email' && filled($message->email); @endphp
                                    <button type="button" class="pp-btn is-utama" wire:click="simpanBalasan" wire:loading.attr="disabled" wire:target="simpanBalasan">
                                        <span wire:loading.remove wire:target="simpanBalasan" class="pp-isi-tombol">
                                            <i class="bi {{ $kirimSurel ? 'bi-envelope-arrow-up' : 'bi-send' }}"></i>
                                            <span>{{ $kirimSurel ? 'Kirim & catat' : 'Simpan balasan' }}</span>
                                        </span>
                                        <span wire:loading.inline-flex wire:target="simpanBalasan" class="pp-isi-tombol">
                                            <span class="dsb-putar is-kecil"></span><span>{{ $kirimSurel ? 'Mengirim…' : 'Menyimpan…' }}</span>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            @if ($balasanKanal === 'email' && blank($message->email))
                                <p class="pp-catatan-kecil"><i class="bi bi-info-circle"></i> Tiket ini tidak punya alamat surel — pilih kanal lain.</p>
                            @endif

                            {{-- Kelola template: jarang dipakai, jadi dilipat. --}}
                            <button type="button" class="pp-teknis-pemicu" style="margin-top: 16px;" wire:click="$toggle('kelolaTemplate')" aria-expanded="{{ $kelolaTemplate ? 'true' : 'false' }}">
                                <span class="pp-ringkas-ikon is-rendah" style="background: #faf5ff; color: #7c3aed;"><i class="bi bi-lightning-charge"></i></span>
                                <span>
                                    <b>Kelola template balasan</b>
                                    <small>Dipakai di semua tiket. {nama} dan {tiket} otomatis diganti.</small>
                                </span>
                                <i class="bi {{ $kelolaTemplate ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i>
                            </button>

                            @if ($kelolaTemplate)
                                <div class="pp-template-kelola">
                                    @if ($templates->isNotEmpty())
                                        <div class="pp-template-daftar">
                                            @foreach ($templates as $i => $t)
                                                <div class="pp-template-item {{ (string) $templateId === (string) $t->id ? 'is-diedit' : '' }}" wire:key="tpl-{{ $t->id }}">
                                                    <b>{{ $t->nama }}</b>
                                                    <button type="button" class="pp-btn pp-btn-ikon" wire:click="geserTemplate('{{ $t->id }}', 'naik')" @disabled($i === 0)
                                                        title="Naikkan urutan" aria-label="Naikkan urutan"><i class="bi bi-chevron-up"></i></button>
                                                    <button type="button" class="pp-btn pp-btn-ikon" wire:click="geserTemplate('{{ $t->id }}', 'turun')" @disabled($i === $templates->count() - 1)
                                                        title="Turunkan urutan" aria-label="Turunkan urutan"><i class="bi bi-chevron-down"></i></button>
                                                    <button type="button" class="pp-btn pp-btn-ikon" wire:click="editTemplate('{{ $t->id }}')"
                                                        title="Ubah template" aria-label="Ubah template"><i class="bi bi-pencil"></i></button>
                                                    <button type="button" class="pp-btn pp-btn-ikon is-bahaya pp-konfirmasi" data-action="hapusTemplate" data-arg="{{ $t->id }}" data-icon="warning"
                                                        data-title="Hapus template ini?" data-text="{{ $t->nama }} tidak akan muncul lagi di tiket mana pun." data-confirm="Ya, hapus"
                                                        title="Hapus template" aria-label="Hapus template"><i class="bi bi-trash3"></i></button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <label class="pp-medan">
                                        <span>{{ $templateId ? 'Ubah nama template' : 'Nama template baru' }}</span>
                                        <input type="text" class="dsb-isian" wire:model="templateNama" placeholder="Misalnya: Minta bukti transfer">
                                    </label>
                                    @error('templateNama')<span class="pp-galat">{{ $message }}</span>@enderror

                                    <label class="pp-medan">
                                        <span>Isi template</span>
                                        <textarea class="dsb-isian" rows="3" wire:model="templateIsi" placeholder="Halo {nama}, …"></textarea>
                                    </label>
                                    @error('templateIsi')<span class="pp-galat">{{ $message }}</span>@enderror

                                    <div class="pp-template-aksi">
                                        <button type="button" class="pp-btn" wire:click="simpanTemplate">
                                            <i class="bi {{ $templateId ? 'bi-check2' : 'bi-plus-lg' }}"></i><span>{{ $templateId ? 'Simpan perubahan' : 'Tambah template' }}</span>
                                        </button>
                                        @if ($templateId)
                                            <button type="button" class="pp-btn" wire:click="batalEditTemplate">
                                                <i class="bi bi-x"></i><span>Batal</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- ================== CATATAN INTERNAL ================== --}}
                    <section class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <span class="pp-detail-label">Catatan internal</span>
                            <p class="pp-catatan-kecil" style="margin-top: 0 !important;">Hanya terlihat oleh tim — tidak pernah dikirim ke pelanggan.</p>
                            @if ($templates->isNotEmpty())
                                <div class="pp-template">
                                    @foreach ($templates as $t)
                                        <button type="button" class="pp-template-btn" wire:click="pakaiTemplateCatatan('{{ $t->id }}')" title="Isikan template ini ke catatan">
                                            <i class="bi bi-lightning-charge-fill"></i>{{ $t->nama }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            <div class="pp-tulis">
                                <textarea class="dsb-isian" rows="2" wire:model="catatanIsi" placeholder="Misalnya: sudah ditelepon, minta ditunda sampai besok."></textarea>
                                @error('catatanIsi')<span class="pp-galat">{{ $message }}</span>@enderror
                                <div class="pp-tulis-baris">
                                    <button type="button" class="pp-btn" wire:click="simpanCatatan">
                                        <i class="bi bi-sticky"></i><span>Simpan catatan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- ================== LINIMASA ================== --}}
                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        @php
                            // Tiket ramai bisa punya puluhan baris; yang lama dilipat
                            // supaya kejadian terbaru tidak tenggelam.
                            $batasLinimasa = 6;
                            $lama = max(0, $logs->count() - $batasLinimasa);
                        @endphp
                        <span class="pp-detail-label">Linimasa tiket</span>
                        <div x-data="{ semua: {{ $lama ? 'false' : 'true' }} }">
                            @if ($lama)
                                <button type="button" class="pp-btn pp-lipat" x-on:click="semua = !semua">
                                    <i class="bi" :class="semua ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                    <span x-text="semua ? 'Sembunyikan yang lama' : 'Tampilkan {{ $lama }} kejadian lebih lama'"></span>
                                </button>
                            @endif
                            <ol class="pp-linimasa">
                                <li class="pp-baris" @if ($lama) x-show="semua" x-collapse x-cloak @endif>
                                    <span class="pp-baris-ikon" style="--c: #7c3aed;"><i class="bi bi-inbox-fill"></i></span>
                                    <div class="pp-baris-kepala">
                                        <b>Pesan masuk</b>
                                        <small>{{ $message->created_at?->locale('id')->translatedFormat('d M Y, H:i') }} · dari {{ $message->name }}</small>
                                    </div>
                                </li>
                                @php $tanggalTerakhir = $message->created_at?->toDateString(); @endphp
                                @foreach ($logs as $i => $log)
                                    @php
                                        [$lIkon, $lWarna, $lJudul] = $log->tampilan();
                                        $disembunyikan = $lama && $i < $lama;
                                        $tanggalIni = $log->created_at?->toDateString();
                                        $gantiHari = $tanggalIni !== $tanggalTerakhir;
                                        $tanggalTerakhir = $tanggalIni;
                                    @endphp
                                    @if ($gantiHari)
                                        <li class="pp-baris-tanggal" @if ($disembunyikan) x-show="semua" x-collapse x-cloak @endif>
                                            {{ $log->created_at?->locale('id')->translatedFormat('l, d F Y') }}
                                        </li>
                                    @endif
                                    <li class="pp-baris is-{{ $log->jenis }}" wire:key="log-{{ $log->id }}"
                                        @if ($disembunyikan) x-show="semua" x-collapse x-cloak @endif>
                                        <span class="pp-baris-ikon" style="--c: {{ $lWarna }}"><i class="bi {{ $lIkon }}"></i></span>
                                        <div class="pp-baris-kepala">
                                            <b>{{ $lJudul }}</b>
                                            <small>{{ $log->created_at?->locale('id')->translatedFormat('d M Y, H:i') }} · {{ $log->pelaku() }}</small>
                                        </div>
                                        @if ($log->isi)
                                            <p class="pp-baris-isi">{{ $log->isi }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </section>

                {{-- Keterangan teknis: jarang dipakai, jadi disembunyikan sampai diminta. --}}
                @if ($message->ip_address || $message->user_agent)
                    <section class="dsb-kartu" x-data="{ buka: false }">
                        <div class="dsb-kartu-isi">
                            <button type="button" class="pp-teknis-pemicu" x-on:click="buka = !buka" :aria-expanded="buka.toString()">
                                <span class="pp-ringkas-ikon is-rendah" style="background: #f1f5f9; color: #64748b;"><i class="bi bi-hdd-network"></i></span>
                                <span>
                                    <b>Keterangan teknis</b>
                                    <small>Alamat IP & peramban pengirim — untuk menelusuri kiriman mencurigakan.</small>
                                </span>
                                <i class="bi" :class="buka ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            </button>
                            <div x-show="buka" x-collapse x-cloak>
                                <div class="pp-info" style="margin-top: 14px;">
                                    <div><small>Alamat IP</small><b>{{ $message->ip_address ?: '—' }}</b></div>
                                    <div><small>Peramban</small><b>{{ \Illuminate\Support\Str::limit($message->user_agent, 120) ?: '—' }}</b></div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            {{-- ================== KOLOM SAMPING ================== --}}
            <aside class="pp-detail-samping">
                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <span class="pp-detail-label">Tindak lanjut</span>

                        <label class="pp-medan">
                            <span>Status</span>
                            <select class="dsb-isian" wire:model.live="status" @disabled(! $bolehUbah)>
                                @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::STATUS as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($status === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="pp-medan">
                            <span>Prioritas</span>
                            <select class="dsb-isian" wire:model.live="priority" @disabled(! $bolehUbah)>
                                @foreach (\App\Livewire\Pages\Admin\Message\CustomerMessageList::PRIORITAS as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($priority === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="pp-medan">
                            <span>Topik</span>
                            <select class="dsb-isian" wire:model.live="kategori" @disabled(! $bolehUbah)>
                                <option value="">Belum ditentukan</option>
                                @foreach ($kategoriDaftar as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($kategori === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="pp-medan">
                            <span>Pemegang tiket</span>
                            <select class="dsb-isian" wire:model.live="petugas" @disabled(! $bolehUbah)>
                                <option value="">Belum ditugaskan</option>
                                @foreach ($daftarPetugas as $orang)
                                    <option value="{{ $orang->id }}" @selected((string) $petugas === (string) $orang->id)>{{ $orang->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <p class="pp-catatan-kecil">
                            {{ $bolehUbah ? 'Perubahan langsung tersimpan dan tercatat di linimasa.' : 'Anda hanya bisa melihat tiket ini.' }}
                        </p>
                    </div>
                </section>

                {{-- Kaitan dengan data pelanggan: konteks yang paling dicari saat membalas. --}}
                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <span class="pp-detail-label">Pengirim di data kita</span>
                        <div class="pp-kait">
                            @if ($pelanggan)
                                @if ($bolehLihatPelanggan)
                                    <a href="{{ route('admin.customer.show', $pelanggan->id) }}" wire:navigate class="pp-kait-baris">
                                        <i class="bi bi-person-badge"></i>
                                        <span>
                                            <b>{{ $pelanggan->nama }}</b>
                                            <small>Pelanggan terdaftar · {{ $pelanggan->status_member === 'active' ? 'Member aktif' : 'Non-member' }}</small>
                                        </span>
                                    </a>
                                @else
                                    <div class="pp-kait-baris">
                                        <i class="bi bi-person-badge"></i>
                                        <span>
                                            <b>{{ $pelanggan->nama }}</b>
                                            <small>Pelanggan terdaftar · {{ $pelanggan->status_member === 'active' ? 'Member aktif' : 'Non-member' }}</small>
                                        </span>
                                    </div>
                                @endif
                                @forelse ($pesanan as $order)
                                    @if ($bolehLihatPesanan)
                                        <a href="{{ route('admin.pesanantoko.detail', $order->id) }}" wire:navigate class="pp-kait-baris" wire:key="order-{{ $order->id }}">
                                            <i class="bi bi-bag-check"></i>
                                            <span>
                                                <b>{{ $order->order_number }}</b>
                                                <small>{{ ucfirst($order->status) }} · Rp {{ number_format((float) $order->total, 0, ',', '.') }} · {{ $order->created_at?->locale('id')->translatedFormat('d M Y') }}</small>
                                            </span>
                                        </a>
                                    @else
                                        <div class="pp-kait-baris" wire:key="order-{{ $order->id }}">
                                            <i class="bi bi-bag-check"></i>
                                            <span>
                                                <b>{{ $order->order_number }}</b>
                                                <small>{{ ucfirst($order->status) }} · {{ $order->created_at?->locale('id')->translatedFormat('d M Y') }}</small>
                                            </span>
                                        </div>
                                    @endif
                                @empty
                                    <p class="pp-catatan-kecil">Belum ada pesanan atas nama pelanggan ini.</p>
                                @endforelse
                            @else
                                <p class="pp-catatan-kecil">Surel dan nomornya belum cocok dengan pelanggan mana pun di data kita.</p>
                            @endif
                        </div>
                    </div>
                </section>

                @if ($pesanLain->isNotEmpty())
                    <section class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <span class="pp-detail-label">Pesan lain dari orang yang sama</span>
                            <div class="pp-kait">
                                @foreach ($pesanLain as $lain)
                                    <div class="pp-kait-gabung" wire:key="lain-{{ $lain->id }}">
                                        <a href="{{ route('admin.customer-message.detail', $lain->id) }}" wire:navigate class="pp-kait-baris">
                                            <i class="bi bi-chat-left-text"></i>
                                            <span>
                                                <b>{{ $lain->ticket }}</b>
                                                <small>{{ $lain->tampilanStatus()[0] }} · {{ $lain->created_at?->locale('id')->translatedFormat('d M Y') }}</small>
                                            </span>
                                        </a>
                                        @if ($bolehUbah)
                                            <button type="button" class="pp-btn pp-btn-ikon pp-konfirmasi" data-action="gabungkanTiket" data-arg="{{ $lain->id }}" data-icon="question"
                                                data-title="Gabungkan {{ $lain->ticket }} ke tiket ini?"
                                                data-text="Tiket itu ditutup dan diarsipkan; isinya disalin ke linimasa tiket ini." data-confirm="Ya, gabungkan"
                                                title="Gabungkan ke tiket ini" aria-label="Gabungkan ke tiket ini"><i class="bi bi-union"></i></button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                <section class="dsb-kartu">
                    <div class="dsb-kartu-isi">
                        <span class="pp-detail-label">Rincian tiket</span>
                        <div class="pp-info is-tunggal">
                            <div><small>Nomor tiket</small><b>{{ $message->ticket }}</b></div>
                            <div><small>Masuk</small><b>{{ $message->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</b></div>
                            <div><small>Dibaca</small><b>{{ $message->read_at?->locale('id')->translatedFormat('d M Y, H:i') ?: 'Belum dibaca' }}</b></div>
                            <div><small>Dibalas</small><b>{{ $message->replied_at?->locale('id')->translatedFormat('d M Y, H:i') ?: 'Belum dicatat' }}{{ $message->pembalas ? ' · '.$message->pembalas->name : '' }}</b></div>
                            <div><small>Nomor telepon</small><b>{{ $message->no_telp ?: '—' }}</b></div>
                            @if ($message->induk)
                                <div><small>Digabungkan ke</small><b>{{ $message->induk->ticket }}</b></div>
                            @endif
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    @include('livewire.layout.sweetalert')

    @push('scripts')
        <script>
            // Dipasang SEKALI di dokumen: wire:navigate mengganti <body>.
            if (!window.__pesanDetailTerpasang) {
                window.__pesanDetailTerpasang = true;
                const gaya = {
                    background: 'rgba(255, 255, 255, 0.95)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
                    buttonsStyling: false,
                };
                // Daftar pintasan. Disusun lewat DOM, bukan string HTML: markup di
                // dalam <script> membuat Livewire salah menghitung elemen akar.
                const bukaBantuan = () => {
                    if (typeof Swal === 'undefined') return;
                    const daftar = [
                        ['j', 'Tiket lebih lama'], ['k', 'Tiket lebih baru'],
                        ['r', 'Fokus ke kotak balasan'], ['c', 'Fokus ke catatan internal'],
                        ['Esc', 'Keluar dari kotak isian'], ['?', 'Buka bantuan ini'],
                    ];
                    const kotak = document.createElement('div');
                    kotak.style.display = 'grid';
                    kotak.style.gap = '8px';
                    kotak.style.textAlign = 'left';
                    daftar.forEach(([tombol, arti]) => {
                        const baris = document.createElement('div');
                        baris.style.display = 'flex';
                        baris.style.alignItems = 'center';
                        baris.style.gap = '10px';
                        const kbd = document.createElement('kbd');
                        kbd.textContent = tombol;
                        kbd.style.cssText = 'min-width:34px;text-align:center;padding:3px 8px;border:1px solid #e2e8f0;border-bottom-width:2px;border-radius:7px;background:#f8fafc;font-size:.8rem;color:#475569';
                        const teks = document.createElement('span');
                        teks.textContent = arti;
                        teks.style.fontSize = '.88rem';
                        baris.append(kbd, teks);
                        kotak.append(baris);
                    });
                    Swal.fire({ title: 'Pintasan papan tik', html: kotak, confirmButtonText: 'Tutup', ...gaya });
                };

                document.addEventListener('click', (e) => {
                    if (e.target.closest('.pp-bantuan-pemicu')) {
                        e.preventDefault();
                        bukaBantuan();
                    }
                });

                const panggil = (el, metode, arg) => {
                    const komponen = el.closest('[wire\\:id]');
                    if (!komponen) return;
                    const hidup = Livewire.find(komponen.getAttribute('wire:id'));
                    if (arg === undefined) hidup.call(metode); else hidup.call(metode, arg);
                };
                // execCommand: peramban lama & konteks tanpa HTTPS tidak punya
                // navigator.clipboard, dan tombol Salin harus tetap bekerja.
                const salinCadangan = (teks, selesai) => {
                    const ta = document.createElement('textarea');
                    ta.value = teks;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.left = '-9999px';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); selesai(); } catch (e) { /* diam: tak ada yang bisa dilakukan */ }
                    document.body.removeChild(ta);
                };
                // Pintasan: j/k pindah tiket, r fokus ke kotak balasan, c ke catatan.
                document.addEventListener('keydown', (e) => {
                    if (e.metaKey || e.ctrlKey || e.altKey) return;
                    const el = document.activeElement;
                    if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT' || el.isContentEditable)) {
                        if (e.key === 'Escape') el.blur();
                        return;
                    }

                    const pindah = { j: 1, k: 0 };
                    if (e.key in pindah) {
                        const tautan = document.querySelectorAll('.pp-tetangga a');
                        // Urutan tombolnya: [lebih baru, lebih lama]; k = lebih baru.
                        const sasaran = document.querySelector('.pp-tetangga a[data-arah="' + (e.key === 'j' ? 'lama' : 'baru') + '"]');
                        if (sasaran) { e.preventDefault(); sasaran.click(); }
                        return;
                    }

                    if (e.key === '?') {
                        e.preventDefault();
                        bukaBantuan();
                        return;
                    }

                    const fokus = { r: 'textarea[wire\\:model="balasanIsi"]', c: 'textarea[wire\\:model="catatanIsi"]' };
                    if (e.key in fokus) {
                        const kotak = document.querySelector(fokus[e.key]);
                        if (kotak) { e.preventDefault(); kotak.focus(); kotak.scrollIntoView({ block: 'center', behavior: 'smooth' }); }
                    }
                });

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

                    const salin = e.target.closest('.pp-salin');
                    if (!salin) return;
                    e.preventDefault();
                    const teks = salin.dataset.teks || '';
                    const sudah = () => Swal.fire({ title: 'Tersalin', text: 'Isi pesan sudah disalin.', icon: 'success', timer: 1600, showConfirmButton: false, ...gaya });
                    if (navigator.clipboard?.writeText) {
                        navigator.clipboard.writeText(teks).then(sudah).catch(() => salinCadangan(teks, sudah));
                    } else {
                        salinCadangan(teks, sudah);
                    }
                });
            }
        </script>
    @endpush
</div>
