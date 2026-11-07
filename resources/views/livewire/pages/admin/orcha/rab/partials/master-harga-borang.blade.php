{{-- Isian satu baris master harga.

     Dipakai halaman tersendiri (tambah & ubah). Dulu ia kartu yang
     menyelip di dalam daftar, mendorong tabelnya keluar pandangan — dan
     alamatnya tidak pernah menunjuk baris yang sedang disunting. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-awas)"><i class="bi bi-tags"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">{{ $sunting ? 'Ubah Harga' : 'Harga Baru' }}</h3>
                                <span class="dsb-kartu-sub">
                                    Begitu harga ini dipakai di sebuah RAB, angkanya <strong>tersimpan di RAB itu</strong>. Mengubahnya di sini tidak mengubah RAB yang sudah jadi — hanya RAB yang dibuat sesudahnya.
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu-isi">
                        {{-- Kotaknya melar mengisi barisnya (flex, bukan kolom
                             tetap), karena jumlah kotak yang terlihat BERUBAH:
                             destinasi hanya untuk tiket, kapasitas hanya untuk
                             satuan yang menghitung unit. Dengan kolom tetap,
                             setiap kotak yang disembunyikan meninggalkan
                             lubang di tengah formulirnya. --}}
                        {{-- Tiga baris bermakna, bukan satu rak panjang:
                             apa &amp; berapa, lalu di mana, lalu namanya.
                             Urutannya bukan selera — destinasi harus sudah
                             terpilih sebelum kotak nama, karena namanya
                             dirakit dari destinasi itu. --}}
                        <section class="mh-bagian">
                            <div class="mh-bagian-kepala">
                                <span class="mh-bagian-ikon" style="--c: var(--dsb-utama)"><i class="bi bi-tags-fill"></i></span>
                                <div>
                                    <h4 class="mh-bagian-judul">Apa yang dibeli, dan berapa</h4>
                                    <span class="mh-bagian-ket">Jenis biayanya, cara menghitungnya, lalu harganya.</span>
                                </div>
                            </div>
                        <div class="mh-medan-rak">
                            <div class="dsb-medan">
                                <label class="dsb-label" for="mh-kategori">Kategori <span class="dsb-wajib">*</span></label>
                                {{-- @selected() wajib, terutama di sini: membuka "Ubah"
                                     pada baris transportasi tanpa ini menggambarkan
                                     kategorinya sebagai "Tiket & Retribusi" — pilihan
                                     pertama daftar — padahal yang tersimpan bukan itu.
                                     Menekan Simpan lalu mengubah kategorinya diam-diam. --}}
                                <select id="mh-kategori" wire:model.live="isian.kategori" class="dsb-isian">
                                    @foreach ($kategori as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected(($isian['kategori'] ?? '') === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="mh-satuan">
                                    Dihitung per <span class="dsb-wajib">*</span>
                                    <span class="dsb-label-ket">terisi dari kategori</span>
                                </label>
                                <select id="mh-satuan" wire:model.live="isian.satuan" class="dsb-isian">
                                    @foreach ($satuan as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected(($isian['satuan'] ?? '') === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="mh-harga">Harga <span class="dsb-wajib">*</span></label>
                                <div class="orcha-rupiah">
                                    <input id="mh-harga" type="text" inputmode="numeric" wire:model.blur="isian.harga"
                                        class="orcha-uang dsb-isian @error('isian.harga') is-galat @enderror" placeholder="50.000">
                                </div>
                                @error('isian.harga')<span class="dsb-galat">{{ $message }}</span>@enderror
                            </div>

                            {{-- Harga akhir pekan: saklar, bukan kotak yang
                                 selalu hadir. Sebagian besar harga sama
                                 setiap hari, dan kotak kedua yang ada untuk
                                 tidak diisi tetap menuntut dibaca lebih
                                 dulu di setiap formulir. --}}
                            <div class="dsb-medan is-lebar">
                                {{-- Kartu sakelar, bentuk yang SAMA dengan "Aktif" dan
                                     "Dipilih manual" di bagian terakhir.

                                     Sebelumnya ceklis polos, jadi satu formulir memakai
                                     dua idiom untuk hal yang sama persis: menyalakan atau
                                     mematikan sesuatu. Yang membacanya harus belajar dua
                                     kali, dan yang bentuknya lebih kecil terbaca seperti
                                     setelan yang kurang penting — padahal ia yang
                                     menentukan harga tiket di hari Sabtu.

                                     Judul & keterangannya ikut berubah menurut
                                     keadaannya, sama dengan kedua kartu itu: yang
                                     terbaca keadaan SEKARANG, bukan nama setelannya. --}}
                                <label class="orcha-sakelar-kartu {{ $duaHarga ? 'nyala' : '' }}">
                                    <span class="rupa">
                                        <i class="bi {{ $duaHarga ? 'bi-calendar-event-fill' : 'bi-calendar3' }}"></i>
                                    </span>
                                    <span class="isi">
                                        <span class="judul">
                                            {{ $duaHarga ? 'Akhir pekan & hari libur lebih mahal' : 'Harga sama setiap hari' }}
                                        </span>
                                        <span class="ket">
                                            {{ $duaHarga
                                                ? 'Mis. Baluran: hari kerja Rp 21.000, akhir pekan Rp 31.000.'
                                                : 'Satu harga dipakai Senin sampai Minggu, termasuk tanggal merah.' }}
                                        </span>
                                    </span>
                                    <span class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" wire:model.live="duaHarga">
                                    </span>
                                </label>

                                @if ($duaHarga)
                                    <div class="mh-dua-isi">
                                        <div class="mh-dua-kotak">
                                            <span class="orcha-label-kecil">Hari kerja (Senin–Jumat)</span>
                                            {{-- Ikut berubah SAMBIL diketik, bukan menunggu
                                                 kotak Harga ditinggalkan.

                                                 Kotak Harga terikat wire:model.blur — dan itu
                                                 disengaja: .live di isian rupiah membuat kursor
                                                 melompat ke ujung tiap ketukan. Tapi akibatnya
                                                 kotak ini diam di "—" sementara angkanya sudah
                                                 diketik, dan yang membacanya menyangka harga
                                                 hari kerjanya belum terbaca. Jadi cerminnya
                                                 dikerjakan peramban; angkanya tetap milik
                                                 server. --}}
                                            <div class="mh-dua-angka" data-cermin="mh-harga">{{ $isian['harga'] !== '' ? 'Rp '.$isian['harga'] : '—' }}</div>
                                            <span class="dsb-kartu-sub">dari kotak Harga di atas</span>
                                        </div>

                                        <div class="mh-dua-kotak">
                                            <label class="orcha-label-kecil" for="mh-harga-akhir-pekan">
                                                Akhir pekan &amp; hari libur <span class="dsb-wajib">*</span>
                                            </label>
                                            <div class="orcha-rupiah">
                                                <input id="mh-harga-akhir-pekan" type="text" inputmode="numeric"
                                                    wire:model.blur="isian.harga_akhir_pekan"
                                                    class="orcha-uang dsb-isian @error('isian.harga_akhir_pekan') is-galat @enderror"
                                                    placeholder="31.000">
                                            </div>
                                            @error('isian.harga_akhir_pekan')
                                                <span class="dsb-galat">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Yang dihitung akhir pekan disebut, supaya
                                         tidak ada yang mengira tanggal merah ikut
                                         terbaca sendiri. --}}
                                    <span class="dsb-kartu-sub mh-dua-ket">
                                        Sabtu &amp; Minggu terbaca sendiri dari tanggalnya.
                                        Tanggal merah lain — 17 Agustus, Idul Fitri, cuti bersama —
                                        harus didaftarkan di
                                        <a href="{{ route('admin.orcha.hari-libur') }}">Hari Libur</a>,
                                        kalau tidak tanggal itu dihargai tarif hari kerja.
                                    </span>
                                @endif
                            </div>

                            {{-- Kapasitas hanya untuk satuan yang menghitung unit.
                                 Sebelumnya kotaknya selalu ada dengan petunjuk
                                 "Tidak perlu" — kotak yang hadir untuk tidak
                                 diisi tetap menuntut dibaca lebih dulu. --}}
                            @if ($perluKapasitas)
                                <div class="dsb-medan is-lebar">
                                    <label class="dsb-label" for="mh-kapasitas">
                                        Muat berapa orang per {{ $sebutan }}? <span class="dsb-wajib">*</span>
                                        <span class="dsb-label-ket">bukan jumlah {{ $sebutan }}-nya</span>
                                    </label>

                                    <div class="mh-muat">
                                        <input id="mh-kapasitas" type="number" min="1" max="1000"
                                            wire:model.live.debounce.400ms="isian.kapasitas"
                                            class="dsb-isian mh-muat-angka @error('isian.kapasitas') is-galat @enderror"
                                            placeholder="{{ $sebutan === 'kamar' ? '2' : '14' }}">

                                        {{-- Pilihan bernama, bukan angka yang ditebakkan.
                                             Transportasi tidak selalu Hiace — bisa mobil
                                             pribadi, Elf, atau bus besar — dan angka yang
                                             salah di sini tidak pernah terlihat salah:
                                             yang meleset cuma jumlah unitnya. --}}
                                        <div class="mh-muat-pilih">
                                            @foreach ($pilihanMuat as [$namaKendaraan, $kursi])
                                                <button type="button" wire:click="pakaiKapasitas({{ $kursi }})"
                                                    class="mh-muat-chip {{ $muatKini === $kursi ? 'is-aktif' : '' }}"
                                                    title="Isi dengan {{ $kursi }}">
                                                    {{ $namaKendaraan }}
                                                    <span class="mh-muat-kursi">{{ $kursi }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    @error('isian.kapasitas')
                                        <span class="dsb-galat">{{ $message }}</span>
                                    @else
                                        {{-- Dua angka yang selama ini sama-sama disebut
                                             "kapasitas", dan karena itu tertukar: yang
                                             diisi di sini muatan SATU buah; jumlah
                                             buahnya dihitung sendiri dari peserta. --}}
                                        <span class="dsb-kartu-sub">
                                            @if ($muatKini)
                                                Satu {{ $sebutan }} muat <strong>{{ $muatKini }} orang</strong>.
                                                Jumlah {{ $sebutan }}-nya dihitung sendiri saat menyusun RAB —
                                                peserta ÷ {{ $muatKini }}, dibulatkan ke atas, jadi
                                                <strong>{{ $muatKini + 1 }} orang sudah butuh 2 {{ $sebutan }}</strong>.
                                            @else
                                                Isi muatan <strong>satu</strong> {{ $sebutan }} saja. Jumlah
                                                {{ $sebutan }}-nya dihitung sendiri saat menyusun RAB:
                                                peserta ÷ muatan, dibulatkan ke atas.
                                            @endif
                                        </span>
                                    @enderror
                                </div>
                            @endif
                        </div>
                        </section>

                        <section class="mh-bagian">
                            <div class="mh-bagian-kepala">
                                <span class="mh-bagian-ikon" style="--c: var(--dsb-aman)"><i class="bi bi-geo-alt-fill"></i></span>
                                <div>
                                    <h4 class="mh-bagian-judul">Berlaku di mana, dibeli dari siapa</h4>
                                    <span class="mh-bagian-ket">Wilayah tempat harga ini dipakai, dan vendor yang menjualnya.</span>
                                </div>
                            </div>
                        <div class="mh-medan-rak">

                            {{-- Destinasi hanya ditawarkan untuk tiket — di sembilan
                                 kategori lain ia kotak yang tidak pernah diisi.
                                 Tetap ditampilkan bila nilainya SUDAH ada, supaya
                                 mengganti kategori tidak diam-diam menyembunyikan
                                 data yang masih tersimpan. --}}
                            @if ($medanDestinasi)
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-destinasi">
                                        Destinasi
                                        <span class="dsb-label-ket" title="Harga tiket ini ikut terbawa saat tempat yang sama dimasukkan ke jadwal perjalanan">dicocokkan ke jadwal</span>
                                    </label>
                                    {{-- DIPILIH, bukan diketik: harga tiket dijodohkan
                                         ke itinerary lewat nama ini PERSIS. --}}
                                    {{-- Daftarnya puluhan dan IKUT BERUBAH mengikuti
                                         provinsi, jadi kuncinya memuat daftar beserta
                                         nilainya — node yang kuncinya berubah diganti
                                         utuh, dan Select2 dipasang ulang dengan
                                         pilihan yang baru. --}}
                                    <div wire:key="dst-{{ md5($destinasiKini.'|'.implode('|', $destinasi)) }}" wire:ignore
                                        x-data x-init="pasangSelectCari($el, 'isian.destinasi', { placeholder: 'Tidak terikat destinasi' })">
                                        <select id="mh-destinasi"
                                            class="dsb-isian @error('isian.destinasi') is-galat @enderror">
                                            <option value="">Tidak terikat destinasi</option>
                                            @if ($destinasiAsing)
                                                {{-- Nilai lama yang tidak ada di katalog TETAP
                                                     ditawarkan, supaya menyunting baris lain
                                                     tidak diam-diam menghapusnya. --}}
                                                <option value="{{ $destinasiKini }}" selected>{{ $destinasiKini }} — tidak ada di katalog</option>
                                            @endif
                                            @foreach ($destinasi as $d)
                                                <option value="{{ $d }}" @selected($destinasiKini === $d)>{{ $d }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @unless ($destinasiBaru)
                                        <button type="button" wire:click="$set('destinasiBaru', true)" class="mh-tambah">
                                            <i class="bi bi-plus-circle"></i> Destinasinya belum ada di daftar
                                        </button>
                                    @endunless

                                    {{-- SATU tiket untuk beberapa tempat.
                                         Tiket terusan Dieng berlaku di Candi Arjuna dan
                                         Kawah Sikidang, dibayar sekali. Tanpa daftar ini
                                         admin terpaksa membuat dua baris — dan keduanya
                                         ikut tertarik ke RAB, lalu tiketnya ditagih dua
                                         kali. Muncul hanya setelah destinasi utamanya
                                         dipilih: tanpa itu ia tidak berarti apa-apa. --}}
                                    {{-- Fiturnya menyebut dirinya sendiri walau belum bisa
                                         dipakai. Dulu blok di bawah muncul HANYA setelah
                                         destinasinya dipilih, jadi admin yang mencari cara
                                         menulis tiket terusan tidak menemukan apa pun untuk
                                         diklik — dan menyerah mengira fiturnya tidak ada. --}}
                                    @if (trim((string) ($isian['destinasi'] ?? '')) === '')
                                        <span class="mh-terusan-petunjuk">
                                            Satu tiket berlaku untuk beberapa tempat?
                                            Pilih destinasi utamanya dulu, nanti muncul tempat kedua di sini.
                                        </span>
                                    @endif

                                </div>
                            @endif

                            {{-- Dari siapa harga ini dibeli. Nomornya tidak
                                 diketik di sini — ia hidup di daftar vendor,
                                 supaya nomor yang berganti cukup diubah sekali
                                 dan seluruh harga yang memakainya ikut benar. --}}
                            <div class="dsb-medan">
                                <label class="dsb-label" for="mh-vendor">
                                    Vendor
                                    <span class="dsb-label-ket">nomornya ikut terbawa ke RAB</span>
                                </label>
                                <div wire:key="ven-{{ $isian['vendor_id'] ?? '' }}-{{ count($vendor) }}" wire:ignore
                                    x-data x-init="pasangSelectCari($el, 'isian.vendor_id', { placeholder: 'Belum ditentukan' })">
                                    <select id="mh-vendor" class="dsb-isian">
                                        <option value="">Belum ditentukan</option>
                                        @foreach ($vendor as $v)
                                            <option value="{{ $v['id'] }}" @selected((string) ($this->isian['vendor_id'] ?? '') === (string) $v['id'])>{{ $v['nama'] }}@if (! empty($v['jenis'])) — {{ $v['jenis'] }}@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                                @php($vendorKini = collect($vendor)->firstWhere('id', (int) ($isian['vendor_id'] ?? 0)))
                                @if ($vendorKini && ! empty($vendorKini['kontak']))
                                    <span class="mh-vendor-kontak">
                                        <i class="bi bi-telephone"></i>{{ $vendorKini['kontak'] }}
                                        {{-- Buntu sebelumnya: nomornya terlihat, tetapi
                                             tidak ada jalan ke rinciannya dari sini. --}}
                                        <a href="{{ route('admin.orcha.vendor.detail', $vendorKini['id']) }}" wire:navigate
                                            class="mh-vendor-tautan" title="Buka rincian {{ $vendorKini['nama'] }}">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </span>
                                @elseif ($vendorKini)
                                    {{-- Vendor tanpa nomor tetap harus dicari saat
                                         dibutuhkan — jadi ketiadaannya ditandai. --}}
                                    <span class="mh-vendor-kosong">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        Vendor ini belum punya nomor.
                                        <a href="{{ route('admin.orcha.vendor') }}" wire:navigate>Isi di daftar vendor</a>
                                    </span>
                                @elseif (! $vendorBaru)
                                    {{-- Dulu di sini ada tautan ke daftar vendor —
                                         yang MEMINDAHKAN halaman, dan seluruh isian
                                         harga yang sudah diketik hilang bersamanya. --}}
                                    <button type="button" wire:click="$set('vendorBaru', true)" class="mh-tambah">
                                        <i class="bi bi-plus-circle"></i> Vendornya belum ada di daftar
                                    </button>
                                @endif
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="mh-provinsi">Provinsi</label>
                                <div wire:key="prov-{{ $isian['provinsi'] ?? '' }}" wire:ignore
                                    x-data x-init="pasangSelectCari($el, 'isian.provinsi', { placeholder: 'Berlaku umum (semua provinsi)' })">
                                    <select id="mh-provinsi" class="dsb-isian">
                                        <option value="">Berlaku umum (semua provinsi)</option>
                                        @foreach ($provinsi as $p)
                                            <option value="{{ $p }}" @selected(($isian['provinsi'] ?? '') === $p)>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="mh-daerah">Daerah</label>
                                <input id="mh-daerah" type="text" wire:model.live.debounce.400ms="isian.daerah"
                                    maxlength="80" class="dsb-isian" placeholder="Sleman">
                            </div>

                            @if ($vendorBaru)
                                <div class="mh-baru is-vendor">
                                    <div class="mh-baru-judul">
                                        <i class="bi bi-shop"></i> Vendor baru
                                    </div>

                                    <input type="text" wire:model="namaVendorBaru" maxlength="120"
                                        wire:keydown.enter.prevent="tambahVendorKatalog"
                                        class="dsb-isian @error('namaVendorBaru') is-galat @enderror"
                                        placeholder="Nama vendor, mis. PO Sumber Jaya">

                                    <input type="text" wire:model="kontakVendorBaru" maxlength="120"
                                        wire:keydown.enter.prevent="tambahVendorKatalog"
                                        class="dsb-isian" placeholder="081234000111 (Pak Budi)">

                                    <button type="button" wire:click="tambahVendorKatalog"
                                        wire:loading.attr="disabled" wire:target="tambahVendorKatalog"
                                        class="dsb-tombol is-telaah">
                                        <i class="bi bi-plus-lg" wire:loading.remove wire:target="tambahVendorKatalog"></i>
                                        <span class="dsb-putar" wire:loading wire:target="tambahVendorKatalog"></span>
                                        <span>Tambah vendor</span>
                                    </button>

                                    <button type="button" wire:click="batalVendorBaru"
                                        class="dsb-tombol is-lembut">
                                        <i class="bi bi-x-lg"></i><span>Batal</span>
                                    </button>

                                    @error('namaVendorBaru')
                                        <span class="dsb-galat mh-baru-kaki">{{ $message }}</span>
                                    @else
                                        <span class="dsb-kartu-sub mh-baru-kaki">
                                            Langsung terpilih untuk harga ini, dan masuk ke
                                            <a href="{{ route('admin.orcha.vendor') }}" wire:navigate>daftar vendor</a>.
                                        </span>
                                    @enderror
                                </div>
                            @endif

                            @if ($medanDestinasi && trim((string) ($isian['destinasi'] ?? '')) !== '')
                                <div class="mh-terusan">
                                    <div class="mh-terusan-judul">
                                        Tempat lain yang pakai tiket yang sama
                                        <span class="dsb-label-ket">dibayar sekali untuk semuanya</span>
                                    </div>

                                    @if ($destinasiLain !== [])
                                        <div class="mh-cip-rak">
                                            @foreach ($destinasiLain as $i => $d)
                                                <span class="mh-cip" wire:key="dl-{{ $i }}-{{ md5($d) }}">
                                                    {{ $d }}
                                                    <button type="button" wire:click="hapusDestinasiLain({{ $i }})" title="Keluarkan dari tiket ini">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mh-terusan-tambah">
                                        <select wire:model="destinasiLainPilih" class="dsb-isian">
                                            {{-- Penandanya ikut, seperti pilihan lain di bawahnya:
                                                 Blade tidak menuliskan `selected` sendiri, dan kotak
                                                 yang nilainya baru dikosongkan akan tetap menggambar
                                                 pilihan terakhir.

                                                 Kata kuncinya sengaja TIDAK ditulis utuh di komentar
                                                 ini — pemindai penjaga menghitung kemunculannya, dan
                                                 komentar pun ikut terhitung. --}}
                                            <option value="" @selected($destinasiLainPilih === '')>Pilih tempat lain…</option>
                                            @foreach ($destinasi as $d)
                                                @if ($d !== ($isian['destinasi'] ?? '') && ! in_array($d, $destinasiLain, true))
                                                    <option value="{{ $d }}" @selected($destinasiLainPilih === $d)>{{ $d }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="tambahDestinasiLain" class="mh-tambah">
                                            <i class="bi bi-plus-lg"></i> Tambah
                                        </button>
                                    </div>
                                </div>
                            @endif

                            {{-- Selebar rak, bukan terjepit di dalam medan Destinasi.

                                 Di sepertiga lebar kartu, isian dan tombolnya tidak
                                 muat sebaris: "Batal" jatuh sendirian ke baris kedua
                                 dan menyisakan tiga perempat baris menganggur —
                                 sementara dua pertiga kartu di sebelahnya kosong.

                                 Kotak pilih hanya memuat yang sudah ada di katalog,
                                 sementara destinasi baru memang bermunculan. Tanpa
                                 jalan ini admin harus pindah menu dan kehilangan
                                 seluruh isian yang sudah diketiknya. --}}
                            @if ($destinasiBaru && $medanDestinasi)
                                <div class="mh-baru">
                                    <div class="mh-baru-judul">
                                        <i class="bi bi-plus-circle"></i> Destinasi baru
                                    </div>

                                    <input type="text" wire:model="namaDestinasiBaru" maxlength="100"
                                        wire:keydown.enter.prevent="tambahDestinasiKatalog"
                                        class="dsb-isian @error('namaDestinasiBaru') is-galat @enderror"
                                        placeholder="Nama destinasi baru, mis. Tebing Breksi">

                                    <button type="button" wire:click="tambahDestinasiKatalog"
                                        wire:loading.attr="disabled" wire:target="tambahDestinasiKatalog"
                                        class="dsb-tombol is-aman">
                                        <i class="bi bi-plus-lg" wire:loading.remove wire:target="tambahDestinasiKatalog"></i>
                                        <span class="dsb-putar" wire:loading wire:target="tambahDestinasiKatalog"></span>
                                        <span>Tambah ke katalog</span>
                                    </button>

                                    <button type="button" wire:click="batalDestinasiBaru"
                                        class="dsb-tombol is-lembut">
                                        <i class="bi bi-x-lg"></i><span>Batal</span>
                                    </button>

                                    @error('namaDestinasiBaru')
                                        <span class="dsb-galat mh-baru-kaki">{{ $message }}</span>
                                    @else
                                        <span class="dsb-kartu-sub mh-baru-kaki">
                                            Namanya langsung masuk ke daftar destinasi Orcha, jadi bisa dipakai saat menyusun jadwal perjalanan juga.
                                            @if (trim((string) ($isian['provinsi'] ?? '')) !== '')
                                                Provinsi <strong>{{ $isian['provinsi'] }}</strong> ikut terkirim.
                                            @endif
                                        </span>
                                    @enderror
                                </div>
                            @endif


                        </div>

                        @if ($destinasiAsing)
                            <div class="mh-peringatan">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>
                                    <strong>{{ $destinasiKini }}</strong> tidak ada di daftar destinasi.
                                    Karena namanya tidak cocok, harga tiket ini tidak akan ikut terbawa saat
                                    tempat itu dimasukkan ke jadwal perjalanan. Pilih namanya dari daftar,
                                    atau tambahkan destinasinya lebih dulu.
                                </span>
                            </div>
                        @elseif ($this->tiketTanpaDestinasi())
                            <div class="mh-peringatan">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>
                                    Tiket yang tidak diisi destinasinya tidak akan pernah ikut terbawa ke RAB,
                                    karena sistem mencarinya lewat nama tempat di jadwal perjalanan. Pilih
                                    destinasinya, atau tandai <strong>wajib</strong> supaya tetap masuk sendiri.
                                </span>
                            </div>
                        @endif
                        </section>

                        <section class="mh-bagian">
                            <div class="mh-bagian-kepala">
                                <span class="mh-bagian-ikon" style="--c: var(--dsb-telaah)"><i class="bi bi-card-text"></i></span>
                                <div>
                                    <h4 class="mh-bagian-judul">Namanya di daftar</h4>
                                    <span class="mh-bagian-ket">Nama inilah yang muncul di Master Harga dan di rincian biaya RAB.</span>
                                </div>
                            </div>
                        <div class="mh-medan-rak">
                            <div class="dsb-medan is-lebar">
                                <label class="dsb-label" for="mh-nama">
                                    Nama <span class="dsb-wajib">*</span>
                                    @if ($namaKini === '' && $namaRakitan !== '')
                                        <span class="dsb-label-ket">boleh dibiarkan kosong</span>
                                    @endif
                                </label>
                                <input id="mh-nama" type="text" wire:model.blur="isian.nama" maxlength="150"
                                    class="dsb-isian @error('isian.nama') is-galat @enderror"
                                    placeholder="{{ $namaRakitan !== '' ? $namaRakitan : 'Sewa kendaraan + sopir + BBM' }}">
                                @error('isian.nama')<span class="dsb-galat">{{ $message }}</span>@enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="mh-berlaku">
                                    Harga berlaku sampai
                                    <span class="dsb-label-ket">boleh dikosongkan</span>
                                </label>
                                <input id="mh-berlaku" type="date" wire:model="isian.berlaku_sampai"
                                    class="dsb-isian @error('isian.berlaku_sampai') is-galat @enderror">
                                @error('isian.berlaku_sampai')
                                    <span class="dsb-galat">{{ $message }}</span>
                                @else
                                    {{-- Dulu ini diketik sebagai kalimat di kolom catatan,
                                         jadi tidak ada apa pun yang bisa memperingatkan
                                         saat tanggalnya lewat. --}}
                                    @if ($this->berlakuSudahLewat())
                                        {{-- Tidak ditolak: harga lama yang memang sudah
                                             kedaluwarsa sah dicatat apa adanya. Tetapi
                                             diterima DIAM-DIAM adalah yang membuat salah
                                             ketik "2020" tidak pernah ketahuan. --}}
                                        {{-- Kalimatnya dibungkus satu <span>: pembungkus
                                             luarnya flex, dan tanpa ini tiap potongan teks
                                             jadi item flex sendiri — kata bercetak tebal
                                             di tengah kalimat terlempar ke kolomnya
                                             sendiri berikut spasi yang janggal. --}}
                                        <span class="mh-ingat">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            <span>Tanggal ini sudah lewat — harganya akan langsung ditandai <strong>LEWAT</strong>. Biarkan begitu bila memang disengaja.</span>
                                        </span>
                                    @else
                                        <span class="dsb-kartu-sub">
                                            Lewat tanggal ini harganya tetap dipakai — barisnya saja yang ditandai supaya
                                            ditanyakan ulang ke vendornya.
                                        </span>
                                    @endif
                                @enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="mh-catatan">
                                    Catatan untuk tim sendiri
                                    <span class="dsb-label-ket">tidak pernah terlihat pelanggan</span>
                                </label>
                                <input id="mh-catatan" type="text" wire:model="isian.catatan" maxlength="500"
                                    class="dsb-isian" placeholder="Vendor, nomor kontak, harga berlaku sampai…">
                            </div>
                        </div>

                        {{-- Yang terisi sendiri diperlihatkan, bukan dikerjakan
                             diam-diam: nama yang baru muncul sesudah menyimpan
                             tidak bisa dikoreksi oleh orang yang tidak pernah
                             melihatnya. --}}
                        @if ($namaKini === '' && $namaRakitan !== '')
                            <div class="mh-rakitan">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-aman)"><i class="bi bi-magic"></i></span>
                                <div class="mh-rakitan-teks">
                                    <span class="dsb-kartu-sub">Nama ini akan terisi sendiri</span>
                                    <strong class="mh-rakitan-judul">{{ $namaRakitan }}</strong>
                                </div>
                            </div>
                        @endif
                        </section>

                        <section class="mh-bagian">
                            <div class="mh-bagian-kepala">
                                <span class="mh-bagian-ikon" style="--c: var(--dsb-awas)"><i class="bi bi-toggles"></i></span>
                                <div>
                                    <h4 class="mh-bagian-judul">Kapan harga ini ikut ke RAB</h4>
                                    <span class="mh-bagian-ket">Muncul sebagai pilihan saja, atau ikut masuk sendiri tanpa dipilih.</span>
                                </div>
                            </div>
                        <div class="mh-sakelar">
                            <label class="orcha-sakelar-kartu {{ $otomatis ? 'nyala' : '' }}">
                                <span class="rupa"><i class="bi {{ $otomatis ? 'bi-magic' : 'bi-hand-index' }}"></i></span>
                                <span class="isi">
                                    <span class="judul">{{ $otomatis ? 'Wajib — masuk sendiri' : 'Dipilih manual' }}</span>
                                    <span class="ket">
                                        {{ $otomatis
                                            ? 'Ikut masuk sendiri ke setiap RAB baru di provinsinya.'
                                            : 'Hanya ikut kalau dipilih sendiri saat menyusun RAB.' }}
                                    </span>
                                </span>
                                <span class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" wire:model.live="otomatis">
                                </span>
                            </label>

                            <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }}">
                                <span class="rupa"><i class="bi {{ $aktif ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i></span>
                                <span class="isi">
                                    <span class="judul">{{ $aktif ? 'Aktif' : 'Dimatikan' }}</span>
                                    <span class="ket">
                                        {{ $aktif
                                            ? 'Muncul sebagai pilihan saat menyusun RAB.'
                                            : 'Tetap tersimpan, tetapi tidak muncul lagi saat menyusun RAB baru.' }}
                                    </span>
                                </span>
                                <span class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                                </span>
                            </label>
                        </div>
                        </section>

                        {{-- Penolakan yang menuntut KEPUTUSAN tinggal di
                             formulirnya, bukan lewat sebagai toast. Keputusan
                             tidak bisa diambil dari kalimat yang sudah lenyap. --}}
                        @if ($kembaranPesan !== '')
                            <div class="mh-kembar">
                                <i class="bi bi-exclamation-octagon-fill"></i>
                                <div class="mh-kembar-teks">
                                    <strong>{{ $kembaranPesan }}</strong>
                                    @if (! empty($kembaranBaris['nama']))
                                        <span class="dsb-kartu-sub">
                                            Yang sudah ada: <strong>{{ $kembaranBaris['nama'] }}</strong>
                                            @if (! empty($kembaranBaris['harga_teks'])) — {{ $kembaranBaris['harga_teks'] }} @endif
                                        </span>
                                    @endif
                                </div>
                                <button type="button" wire:click="simpanPaksa"
                                    wire:loading.attr="disabled" wire:target="simpanPaksa"
                                    class="dsb-tombol is-awas">
                                    <i class="bi bi-check2" wire:loading.remove wire:target="simpanPaksa"></i>
                                    <span class="dsb-putar" wire:loading wire:target="simpanPaksa"></span>
                                    <span>Tetap simpan</span>
                                </button>
                            </div>
                        @endif

                        <div class="mh-kaki">
                            {{-- Hanya saat menambah: "lalu tambah lagi" pada
                                 formulir sunting tidak berarti apa-apa. --}}
                            @unless ($sunting)
                                <div class="mh-kaki-kiri">
                                    <label class="mh-lanjut" title="Formulirnya tetap terbuka, kategori & provinsinya dipertahankan">
                                        <input type="checkbox" class="form-check-input mt-0" wire:model="lanjutTambah">
                                        <span>Tambah lagi setelah simpan</span>
                                    </label>

                                    {{-- Sebaris dengan tombol lain. Berdiri sendiri di
                                         barisnya, ia menyisakan hampir selebar kartu
                                         menganggur di kanannya — dan tombol tunggal yang
                                         menggantung terbaca seperti sisa, bukan seperti
                                         jalan pintas yang setara. --}}
                                    @if (! $tempelBuka)
                                        <button type="button" wire:click="bukaTempel" class="dsb-tombol is-telaah">
                                            <i class="bi bi-clipboard-plus"></i><span>Tempel banyak baris dari Excel</span>
                                        </button>
                                    @endif
                                </div>
                            @endunless
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

                        {{-- ===== Tempel dari spreadsheet =====

                             Memasukkan lima puluh tiket berarti lima puluh kali
                             buka-isi-simpan, dan di tengah pengulangan sebanyak
                             itu salah ketik berhenti terasa seperti kemungkinan.
                             Kotak di atas yang menentukan kategori, satuan,
                             provinsi, dan vendornya — yang ditempel hanya yang
                             berbeda tiap baris. --}}
                        @unless ($sunting)
                            @if ($tempelBuka)
                                <div class="mh-tempel-bungkus">
                                    @php($terbaca = $this->barisTempel())
                                    <div class="mh-tempel">
                                        <div class="mh-tempel-kepala">
                                            <strong>Tempel dari Excel</strong>
                                            <span class="dsb-kartu-sub">
                                                {{-- Cara MENGETIK disebut lebih dulu, bukan TAB.
                                                     TAB tidak bisa diketik di dalam kotak ini —
                                                     tombolnya memindahkan fokus — jadi menyebutnya
                                                     sebagai cara utama membuat admin menuruti
                                                     petunjuk yang tidak mungkin dijalankan. --}}
                                                Satu baris satu harga: <strong>nama</strong>, koma,
                                                <strong>harga</strong>. Contoh:
                                                <code class="mh-tempel-contoh">Tiket Borobudur, 100.000</code>
                                            </span>
                                            <span class="dsb-kartu-sub">
                                                Menempel dari Excel juga langsung terbaca. Mau sekalian
                                                destinasinya? Tambahkan satu koma lagi di belakangnya.
                                                Kategori, satuan, provinsi, dan vendornya diambil dari kotak di atas.
                                            </span>
                                        </div>

                                        {{-- Nilainya DICETAK di dalam tagnya, bukan
                                             diandalkan dari wire:model: isi textarea
                                             adalah isi elemennya, dan yang dikosongkan
                                             dari server harus benar-benar hilang di
                                             layar setelah barisnya masuk. --}}
                                        <textarea wire:model.live="tempelIsi" rows="5"
                                            class="dsb-isian @error('tempelIsi') is-galat @enderror"
                                            placeholder="Tiket Borobudur, 50.000&#10;Tiket Dieng, 30.000">{{ $tempelIsi }}</textarea>

                                        @error('tempelIsi')<span class="dsb-galat">{{ $message }}</span>@enderror

                                        @if ($tempelGagal !== [])
                                            {{-- Yang dilewati tidak ikut hilang bersama
                                                 panelnya: ia pekerjaan yang masih tersisa. --}}
                                            <div class="mh-tempel-gagal">
                                                <strong>{{ count($tempelGagal) }} baris dilewati:</strong>
                                                @foreach ($tempelGagal as $g)
                                                    <span>{{ $g['nama'] }} — {{ $g['sebab'] }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="mh-tempel-kaki">
                                            <span class="dsb-chip">
                                                <i class="bi bi-list-ol"></i>{{ count($terbaca) }} baris terbaca
                                            </span>
                                            <button type="button" wire:click="tutupTempel" class="dsb-tombol is-lembut">
                                                <i class="bi bi-x-lg"></i><span>Tutup</span>
                                            </button>
                                            <button type="button" wire:click="simpanTempel"
                                                wire:loading.attr="disabled" wire:target="simpanTempel"
                                                class="dsb-tombol is-aman">
                                                <i class="bi bi-download" wire:loading.remove wire:target="simpanTempel"></i>
                                                <span class="dsb-putar" wire:loading wire:target="simpanTempel"></span>
                                                <span>Masukkan semua</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endunless
                    </div>
                </div>
            </div>
        </section>
