@section('title')
{{ $ubah ? 'Ubah Destinasi' : 'Tambah Destinasi' }} || lemon
@stop

@php
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanGambar = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;

    $ringkas = function ($angka) {
        $angka = (int) $angka;

        return $angka >= 1000
            ? rtrim(rtrim(number_format($angka / 1000, 1, ',', '.'), '0'), ',') . 'k'
            : (string) $angka;
    };

    // Galeri pratinjau: foto utama lebih dulu, lalu gambar tambahan — urutan
    // yang sama dengan jendela detail di website.
    $galeri = array_values(array_filter(array_merge(
        [$gambar ? \App\Support\PratinjauUnggahan::url($gambar) : $tautanGambar($gambarLama)],
        array_map($tautanGambar, $subFotoTetap),
        array_map(fn ($berkas) => \App\Support\PratinjauUnggahan::url($berkas), $subFoto),
    )));
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => $ubah ? 'Ubah Destinasi' : 'Tambah Destinasi',
            'keterangan' => 'Tersimpan di server Orcha, langsung tampil di halaman Destinasi Populer.',
        ])

        <form wire:submit="simpan" class="orcha-form">
            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-8">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1 orcha-judul-ikon">
                                <i class="bi bi-signpost-2 text-primary"></i> Identitas Destinasi
                            </h6>
                            <p class="text-muted small mb-3">Nama dan tempatnya — yang dicari pengunjung di daftar.</p>

                            <div class="row g-3">
                                <div class="col-12 col-lg-8">
                                    <label class="form-label small fw-semibold">
                                        Nama destinasi <span class="text-danger">*</span>
                                    </label>
                                    {{-- Isian DAN pemilih berdampingan.

                                         Nama destinasi tidak bisa didaftar habis, jadi mengetik
                                         harus tetap mungkin — tetapi tempat yang paling sering
                                         diminta layak tinggal dipilih, lengkap dengan
                                         provinsinya. Yang diketik pun tetap diusulkan
                                         provinsinya lewat peta.

                                         .live.debounce, bukan .blur: usulannya baru berguna bila
                                         muncul saat admin masih mengetik, bukan setelah ia
                                         pindah ke isian berikutnya dan mengisinya sendiri. --}}
                                    <div class="input-group orcha-gabung">
                                        <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                            wire:model.live.debounce.900ms="nama"
                                            placeholder="Ketik, atau pilih dari daftar">
                                        {{-- Tombolnya bukan pelengkap yang disamarkan.

                                             Sebagai tombol abu-abu bersebelahan dengan isian,
                                             ia terbaca seperti hiasan — padahal justru jalan
                                             tercepatnya: satu ketukan mengisi empat isian
                                             sekaligus. Warna dan tulisannya dibuat mengundang
                                             supaya jalan itu terlihat, bukan ditemukan sendiri. --}}
                                        <button type="button" onclick="orchaPilihDestinasi(this)"
                                            class="orcha-tombol-daftar" title="Pilih dari daftar destinasi">
                                            <i class="bi bi-compass"></i>
                                            <span class="d-none d-sm-inline">Pilih dari daftar</span>
                                        </button>
                                    </div>
                                    @error('nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                    <div wire:loading wire:target="nama" class="form-text">Mencari lokasinya…</div>

                                    @if ($usulanLokasi)
                                        {{-- Asal usulannya disebut: yang mengisi dua isian tanpa
                                             mengatakan apa-apa terasa seperti sistem yang mengubah
                                             pekerjaan admin diam-diam. --}}
                                        <div class="orcha-usulan">
                                            <i class="bi bi-magic"></i>
                                            <span>{{ $usulanLokasi }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12 col-lg-4">
                                    <label class="form-label small fw-semibold">Perkiraan pengunjung</label>
                                    <input type="number" min="0"
                                        class="form-control @error('totalPengunjung') is-invalid @enderror"
                                        wire:model.live="totalPengunjung" placeholder="0">
                                    @error('totalPengunjung') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Tampil sebagai lencana di kartunya.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Keterangan</label>
                                    <textarea rows="3"
                                        class="form-control @error('deskripsi') is-invalid @enderror"
                                        wire:model.live.debounce.400ms="deskripsi"
                                        placeholder="Apa yang membuat tempat ini layak didatangi, dan apa yang perlu disiapkan pengunjung."></textarea>
                                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">
                                        {{ mb_strlen($deskripsi) }}/1000 — dibaca utuh di jendela detail destinasi.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- Lokasi dijadikan kartunya sendiri.

                         Ketiganya satu keputusan berurutan — wilayah menyaring provinsi,
                         provinsi menyaring daerah — tetapi sebelumnya tercecer di antara
                         perkiraan pengunjung dan keterangan, sehingga urutannya tidak
                         terbaca sama sekali. Ditata sebaris dengan tanda panah, rantainya
                         terlihat sebelum satu pun isian disentuh. --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1 orcha-judul-ikon">
                                <i class="bi bi-geo-alt text-primary"></i> Lokasi
                            </h6>
                            <p class="text-muted small mb-3">Dipilih berurutan — tiap pilihan menyaring pilihan berikutnya.</p>

                            <div class="orcha-rantai">
                                <div class="orcha-rantai-isi">
                                    <label class="form-label small fw-semibold">
                                        Wilayah <span class="text-danger">*</span>
                                    </label>

                                    {{-- Wilayah yang sedang dipilih ditempel di DOM, BUKAN hanya
                                         di dalam <script> di bawah.

                                         Livewire tidak menjalankan ulang <script> inline saat
                                         me-render ulang, jadi nilai yang ditulis di sana membeku
                                         pada keadaan pemuatan pertama: mengganti wilayah tidak
                                         mengubah daftar provinsi sama sekali. Penanda ini ikut
                                         ter-render tiap kali, dan pemilihnya membacanya saat
                                         diklik. --}}
                                    <span data-orcha-wilayah="{{ $wilayah }}" class="d-none"></span>

                                    <button type="button" onclick="orchaPilihWilayah(this)"
                                        class="form-select text-start orcha-picker @error('wilayah') is-invalid @enderror">
                                        <span class="text-dark fw-semibold">
                                            {{ $daftarWilayah[$wilayah] ?? '— Pilih wilayah —' }}
                                        </span>
                                    </button>

                                    @error('wilayah') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                </div>
                                <div class="orcha-rantai-panah d-none d-lg-flex">
                                    {{-- Pengganjal setinggi label di sebelahnya.

                                         Tanpa ini panahnya harus dinaikkan dengan angka
                                         tebakan, dan tebakannya meleset — terukur di peramban
                                         7,6px di bawah pusat pemilihnya.

                                         Labelnya dibungkus <div> dan BUKAN dijadikan anak
                                         langsung kolom ini. Anak langsung sebuah flex ikut
                                         di-blok-kan, sehingga label kehilangan kotak barisnya —
                                         dan kotak baris itulah yang di kolom sebelah menyisakan
                                         2px di atas label. Dengan pembungkus ini labelnya
                                         kembali berada di aliran biasa, persis seperti
                                         tetangganya, jadi tinggi pengganjalnya sama sampai ke
                                         pecahan piksel tanpa satu angka pun dikarang. --}}
                                    <div><label class="form-label small fw-semibold" aria-hidden="true">&nbsp;</label></div>
                                    <span class="ikon"><i class="bi bi-chevron-right"></i></span>
                                </div>

                                <div class="orcha-rantai-isi">
                                    <label class="form-label small fw-semibold">Provinsi</label>

                                    {{-- Dipilih lewat pemilih berdaftar, bukan diketik.

                                         Bentuknya sama dengan pemilih merek & nama unit di
                                         formulir armada — admin sudah mengenalnya, dan
                                         dua pola berbeda untuk pekerjaan yang sama hanya
                                         menambah yang harus diingat.

                                         Provinsi yang diketik bebas menghasilkan ejaan
                                         berbeda untuk tempat yang sama — "DIY",
                                         "Yogyakarta", "D.I. Yogyakarta" — dan penyaringan
                                         di halaman publik ikut tidak dapat diandalkan. --}}
                                    <button type="button" onclick="orchaPilihProvinsi(this)"
                                        class="form-select text-start orcha-picker @error('provinsi') is-invalid @enderror">
                                        @if (trim($provinsi) !== '')
                                            <span class="text-dark fw-semibold">{{ $provinsi }}</span>
                                        @else
                                            <span class="text-muted">— Pilih provinsi —</span>
                                        @endif
                                    </button>

                                    @error('provinsi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                </div>
                                <div class="orcha-rantai-panah d-none d-lg-flex">
                                    {{-- Pengganjal setinggi label di sebelahnya.

                                         Tanpa ini panahnya harus dinaikkan dengan angka
                                         tebakan, dan tebakannya meleset — terukur di peramban
                                         7,6px di bawah pusat pemilihnya.

                                         Labelnya dibungkus <div> dan BUKAN dijadikan anak
                                         langsung kolom ini. Anak langsung sebuah flex ikut
                                         di-blok-kan, sehingga label kehilangan kotak barisnya —
                                         dan kotak baris itulah yang di kolom sebelah menyisakan
                                         2px di atas label. Dengan pembungkus ini labelnya
                                         kembali berada di aliran biasa, persis seperti
                                         tetangganya, jadi tinggi pengganjalnya sama sampai ke
                                         pecahan piksel tanpa satu angka pun dikarang. --}}
                                    <div><label class="form-label small fw-semibold" aria-hidden="true">&nbsp;</label></div>
                                    <span class="ikon"><i class="bi bi-chevron-right"></i></span>
                                </div>

                                <div class="orcha-rantai-isi">
                                    <label class="form-label small fw-semibold">Daerah</label>

                                    {{-- Provinsi yang sedang dipilih ditempel di DOM: pemilih
                                         daerah menyaring memakainya, dan nilai yang ditulis di
                                         dalam <script> membeku pada pemuatan pertama. --}}
                                    <span data-orcha-provinsi="{{ $provinsi }}" class="d-none"></span>

                                    <button type="button" onclick="orchaPilihDaerah(this)"
                                        class="form-select text-start orcha-picker @error('daerah') is-invalid @enderror">
                                        @if (trim($daerah) !== '')
                                            <span class="text-dark fw-semibold">{{ $daerah }}</span>
                                        @else
                                            <span class="text-muted">— Pilih daerah —</span>
                                        @endif
                                    </button>

                                    @error('daerah') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                </div>
                            </div>

                                @php
                                    $alamatSekarang = collect([$daerah, $provinsi, $daftarWilayah[$wilayah] ?? null])
                                        ->filter(fn ($bagian) => trim((string) $bagian) !== '')
                                        ->implode(' · ');
                                @endphp
                            {{-- Hasil akhirnya disebut apa adanya: tiga pilihan terpisah
                                 tidak menunjukkan bunyi alamat yang akan dibaca pengunjung. --}}
                            <div class="orcha-alamat-jadi mt-3">
                                <i class="bi bi-signpost-split"></i>
                                <span>
                                    @if ($alamatSekarang)
                                        Tampil sebagai <strong>{{ $alamatSekarang }}</strong>
                                    @else
                                        Belum ada lokasi — pilih wilayahnya dulu.
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1 orcha-judul-ikon">
                                <i class="bi bi-images text-primary"></i> Foto Destinasi
                            </h6>
                            <p class="text-muted small mb-3">Satu foto utama untuk kartunya, dan sampai
                                {{ $batasSubFoto }} gambar tambahan untuk galeri detailnya.</p>

                            <label class="form-label small fw-semibold">Foto utama</label>

                            {{-- Pratinjau dan pemilih berkas satu kotak: keduanya satu
                                 keputusan, dan gambar yang melayang tanpa pembatas tidak
                                 terbaca sebagai pasangan isiannya. --}}
                            <div class="orcha-foto-kotak @error('gambar') galat @enderror mb-3">
                                <div class="orcha-foto-rupa">
                                    @if ($gambar)
                                        <img src="{{ \App\Support\PratinjauUnggahan::url($gambar) }}" alt="">
                                    @elseif ($tautanGambar($gambarLama))
                                        <img src="{{ $tautanGambar($gambarLama) }}" alt="">
                                    @else
                                        <span class="orcha-foto-kosong"><i class="bi bi-image"></i></span>
                                    @endif
                                </div>

                                <div class="orcha-foto-isi">
                                    <input type="file"
                                        class="form-control form-control-sm @error('gambar') is-invalid @enderror"
                                        wire:model="gambar" accept="image/*">
                                    <div class="form-text">Maksimal 4 MB. Kosong berarti gambar lama tetap dipakai.</div>
                                    <div wire:loading wire:target="gambar" class="text-muted small">Mengunggah…</div>
                                    @error('gambar') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="orcha-sub-foto">
                                <div class="orcha-sub-kepala">
                                    <span class="judul">
                                        <i class="bi bi-collection"></i>
                                        Gambar tambahan
                                    </span>
                                    <span class="ket">
                                        Tampil di galeri detail — sisa {{ $this->sisaSubFoto() }} dari {{ $batasSubFoto }}
                                    </span>
                                </div>

                                @if ($subFotoTetap || $subFoto)
                                    <div class="orcha-sub-petak">
                                        @foreach ($subFotoTetap as $jalur)
                                            <div class="petak" wire:key="sub-tetap-{{ md5($jalur) }}">
                                                <img src="{{ $tautanGambar($jalur) }}" alt="">
                                                {{-- Berkasnya baru dibuang di Orcha saat disimpan,
                                                     jadi meninggalkan halaman tanpa menyimpan tidak
                                                     menghilangkan apa pun. --}}
                                                <button type="button" class="buang"
                                                    wire:click="hapusSubFoto('{{ $jalur }}')"
                                                    title="Keluarkan gambar ini">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        @endforeach

                                        @foreach ($subFoto as $urutan => $berkas)
                                            <div class="petak baru" wire:key="sub-baru-{{ $urutan }}">
                                                <img src="{{ \App\Support\PratinjauUnggahan::url($berkas) }}" alt="">
                                                <span class="tanda">Baru</span>
                                                <button type="button" class="buang"
                                                    wire:click="batalkanSubFoto({{ $urutan }})"
                                                    title="Batalkan gambar ini">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="orcha-sub-kosong">
                                        <i class="bi bi-image"></i>
                                        Belum ada gambar tambahan.
                                    </p>
                                @endif

                                @if ($this->sisaSubFoto() > 0)
                                    <input type="file" multiple
                                        class="form-control form-control-sm mt-2 @error('subFoto.0') is-invalid @enderror"
                                        wire:model="subFoto" accept="image/*">
                                    <div class="form-text">Bisa pilih beberapa sekaligus — maksimal 2 MB per gambar.</div>
                                @else
                                    <p class="orcha-sub-penuh mt-2">
                                        <i class="bi bi-check-circle"></i>
                                        Sudah {{ $batasSubFoto }} gambar — hapus salah satu untuk menambah.
                                    </p>
                                @endif

                                <div wire:loading wire:target="subFoto" class="text-muted small mt-1">Mengunggah…</div>
                                @error('subFoto') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('subFoto.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu kanan berada DI DALAM pembungkus lengket ini, dan palang
                     tombolnya jadi anak langsungnya — elemen sticky tidak bisa keluar
                     dari kotak induknya, dan pembungkus yang kosong membuat kolom
                     kanan tampak ditinggalkan begitu kolom kiri memanjang. --}}
                <div class="col-12 col-xl-4">
                    <div class="orcha-lengket orcha-lengket-panjang">

                        {{-- Pratinjau: kartu yang sama dengan yang dilihat pengunjung.
                             Tanpa ini kolom kanan hanya berisi tombol, dan admin baru
                             tahu hasilnya setelah membuka website di tab lain. --}}
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 orcha-judul-ikon">
                                    <i class="bi bi-eye text-primary"></i> Pratinjau di Website
                                </h6>

                                <div class="orcha-pratinjau">
                                    <div class="orcha-dest-foto">
                                        @if ($galeri)
                                            <img src="{{ $galeri[0] }}" alt="">
                                        @else
                                            <span class="orcha-foto-kosong"><i class="bi bi-image"></i></span>
                                        @endif

                                        <span class="orcha-dest-lencana">
                                            <i class="bi bi-people-fill"></i>
                                            {{ $ringkas($totalPengunjung) }} pengunjung
                                        </span>

                                        <div class="orcha-dest-judul">
                                            <strong>{{ $nama ?: 'Nama destinasi' }}</strong>
                                            @php
                                                $alamat = collect([$daerah, $provinsi])
                                                    ->filter(fn ($b) => trim((string) $b) !== '')
                                                    ->implode(', ');
                                            @endphp
                                            @if ($alamat)
                                                <span><i class="bi bi-geo-alt-fill"></i> {{ $alamat }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="orcha-dest-isi">
                                        <p class="ket">
                                            {{ $deskripsi ?: 'Keterangan destinasi akan tampil di sini.' }}
                                        </p>

                                        @if (count($galeri) > 1)
                                            <div class="orcha-dest-galeri">
                                                @foreach (array_slice($galeri, 1, $batasSubFoto) as $foto)
                                                    <img src="{{ $foto }}" alt="">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 orcha-judul-ikon">
                                    <i class="bi bi-clipboard-check text-primary"></i> Kesiapan
                                </h6>

                                @php
                                    // Yang wajib DIAMBIL DARI rules() di komponennya, bukan
                                    // ditulis ulang di sini: daftar yang dikarang di tampilan
                                    // akan berbohong begitu aturannya berubah, dan bohongnya
                                    // baru ketahuan saat admin menekan simpan.
                                    $wajib = fn ($medan) => str_contains($this->getRules()[$medan] ?? '', 'required');

                                    $adaSubFoto = count($subFotoTetap) + count($subFoto);
                                    $adaUtama = (bool) ($gambar || $gambarLama);
                                    $tempat = collect([$daerah, $provinsi])->filter()->implode(', ');

                                    $butir = [
                                        [
                                            'label' => 'Nama destinasi',
                                            'wajib' => $wajib('nama'),
                                            'ada' => trim((string) $nama) !== '',
                                            'nilai' => trim((string) $nama) !== '' ? $nama : 'Belum diisi',
                                        ],
                                        [
                                            'label' => 'Wilayah',
                                            'wajib' => $wajib('wilayah'),
                                            'ada' => trim((string) $wilayah) !== '',
                                            'nilai' => $daftarWilayah[$wilayah] ?? 'Belum dipilih',
                                        ],
                                        [
                                            'label' => 'Provinsi & daerah',
                                            'wajib' => false,
                                            'ada' => $tempat !== '',
                                            'nilai' => $tempat ?: 'Belum diisi',
                                        ],
                                        [
                                            'label' => 'Foto utama',
                                            'wajib' => false,
                                            'ada' => $adaUtama,
                                            'nilai' => $adaUtama ? 'Sudah ada' : 'Belum ada',
                                        ],
                                        [
                                            'label' => 'Gambar tambahan',
                                            'wajib' => false,
                                            'ada' => $adaSubFoto > 0,
                                            'nilai' => $adaSubFoto.' dari '.$batasSubFoto,
                                        ],
                                        [
                                            'label' => 'Keterangan',
                                            'wajib' => false,
                                            'ada' => trim((string) $deskripsi) !== '',
                                            'nilai' => trim((string) $deskripsi) !== ''
                                                ? mb_strlen($deskripsi).'/1000 huruf'
                                                : 'Belum ditulis',
                                        ],
                                    ];

                                    $terisi = count(array_filter($butir, fn ($b) => $b['ada']));
                                    $kurang = array_values(array_filter($butir, fn ($b) => $b['wajib'] && ! $b['ada']));

                                    // Kalimatnya dirakit UTUH di sini, bukan disusun dari
                                    // beberapa baris di dalam <span>. Blade menyisipkan baris
                                    // baru di antara potongannya, dan kalimat yang di layar
                                    // terbaca menyatu jadi terpisah-pisah di dalam HTML —
                                    // ikut menyulitkan pembaca layar, bukan hanya pengujian.
                                    $kalimatKurang = 'Belum bisa disimpan — '
                                        .collect($kurang)->pluck('label')->map(fn ($l) => mb_strtolower($l))->implode(' dan ')
                                        .' masih kosong.';
                                @endphp

                                <div class="orcha-siap-kepala">
                                    <span>Terisi</span>
                                    <strong>{{ $terisi }} dari {{ count($butir) }}</strong>
                                </div>

                                <div class="orcha-siap-bar" role="presentation">
                                    <span style="width: {{ round($terisi / count($butir) * 100) }}%"></span>
                                </div>

                                <div class="orcha-siap-daftar">
                                    @foreach ($butir as $b)
                                        <div class="orcha-siap-baris">
                                            @if ($b['ada'])
                                                <i class="bi bi-check-circle-fill tanda ada"></i>
                                            @elseif ($b['wajib'])
                                                <i class="bi bi-exclamation-circle-fill tanda kurang"></i>
                                            @else
                                                <i class="bi bi-circle tanda kosong"></i>
                                            @endif

                                            <span class="label">
                                                {{ $b['label'] }}
                                                @if ($b['wajib'])
                                                    <span class="wajib" title="Wajib diisi">*</span>
                                                @endif
                                            </span>

                                            <span class="nilai @unless ($b['ada']) belum @endunless">{{ $b['nilai'] }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Kalimat penutupnya menjawab satu pertanyaan yang selalu
                                     ditanyakan admin sebelum menekan simpan: "ini sudah bisa
                                     disimpan atau belum". Sebelumnya jawabannya hanya bisa
                                     didapat dengan menekan simpan lalu membaca pesan merah. --}}
                                @if ($kurang)
                                    <p class="orcha-siap-nota kurang">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <span>{{ $kalimatKurang }}</span>
                                    </p>
                                @else
                                    <p class="orcha-siap-nota siap">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Sudah bisa disimpan. Sisanya boleh dilengkapi kapan saja.</span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Tombol di dasar kolom, sebagaimana lazimnya — tetapi dipaku,
                             supaya tetap terlihat berapa pun panjang isian di kiri. --}}
                        <div class="orcha-aksi-paku">
                            {{-- Seukuran tombol simpan di formulir paket wisata dan armada
                                 — 38px, huruf 1rem — lewat .orcha-btn-besar. Ketiganya
                                 formulir tambah data yang dikerjakan berurutan, dan tombol
                                 yang mengecil sendiri di salah satunya membuat ketiganya
                                 terasa dari aplikasi yang berbeda-beda.

                                 Pemintal, bukan sekadar tulisan yang berganti: menyimpan
                                 destinasi menembak Orcha berikut gambarnya, jadi jedanya
                                 terasa. Tombol yang hanya berganti kalimat masih terlihat
                                 diam, dan yang menekannya cenderung menekan lagi.

                                 Ikon simpannya ikut masuk ke bungkus yang disembunyikan
                                 supaya ia berganti MENJADI pemintal, bukan berdiri di
                                 sebelahnya. --}}
                            <div class="d-grid gap-2">
                                <button type="submit" class="orcha-btn orcha-btn-utama orcha-btn-besar"
                                    wire:loading.attr="disabled" wire:target="simpan">
                                    <span wire:loading.remove wire:target="simpan">
                                        <i class="bi bi-save"></i>
                                        {{ $ubah ? 'Simpan Perubahan' : 'Tambah Destinasi' }}
                                    </span>
                                    <span wire:loading wire:target="simpan">
                                        <span class="spinner-border spinner-border-sm me-2"
                                            role="status" aria-hidden="true"></span>Menyimpan ke Orcha…
                                    </span>
                                </button>
                                <a href="{{ route('admin.orcha.destinasi') }}" wire:navigate
                                    class="orcha-btn orcha-btn-lembut orcha-btn-besar">
                                    <i class="bi bi-x-lg"></i> Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')

    {{-- ============ PEMILIH PROVINSI ============

         Data disegarkan tiap render DI LUAR penjaga, sedangkan pemasangan
         fungsinya dijaga sekali saja. Kalau datanya ikut di dalam penjaga,
         provinsi yang baru ditambahkan tidak akan pernah terbaca — Livewire
         tidak menjalankan ulang <script> inline saat me-render ulang, jadi
         nilainya membeku pada keadaan pemuatan pertama.

         Pemilihnya ditulis tersendiri, tidak memakai ulang pemilih armada:
         yang di sana mengenal tiga tingkat (merek → unit → tipe) beserta
         aturannya masing-masing, dan menyeretnya ke sini demi satu daftar datar
         akan membuat keduanya saling mengunci saat salah satunya berubah. --}}
    <script>
        window.__orchaPetaProvinsi = @json($petaProvinsi);
        window.__orchaProvinsiKustom = @json($provinsiKustom);
        window.__orchaDaftarWilayah = @json($daftarWilayah);
        window.__orchaWilayahKustom = @json($wilayahKustom);
        window.__orchaKatalogDaerah = @json($katalogDaerah);
        window.__orchaKatalogDaerahKustom = @json($katalogDaerahKustom);
        window.__orchaKatalogDestinasi = @json($katalogDestinasi);
        window.__orchaKatalogDestinasiKustom = @json($katalogDestinasiKustom);

        if (!window.__orchaProvinsiTerpasang) {
            window.__orchaProvinsiTerpasang = true;

            const provEsc = (t) => String(t).replace(/[&<>"']/g, (m) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            }[m]));

            // Hanya provinsi tambahan yang boleh dihapus dari daftar; yang
            // bawaan ikut versi kode dan dipakai destinasi yang sudah ada.
            const provIdKustom = (nama) => {
                const cocok = (window.__orchaProvinsiKustom || []).find((e) => e.nama === nama);

                return cocok ? cocok.id : null;
            };

            // Dibaca dari DOM, bukan dari nilai yang dibekukan <script> saat
            // halaman pertama dimuat — itulah sebabnya mengganti wilayah dulu
            // tidak mengubah daftar provinsinya sama sekali.
            const provWilayah = () => {
                const penanda = document.querySelector('[data-orcha-wilayah]');

                return penanda ? penanda.getAttribute('data-orcha-wilayah') : '';
            };

            const provDaftar = () => Object.keys(window.__orchaPetaProvinsi || {})
                .filter((n) => window.__orchaPetaProvinsi[n] === provWilayah())
                .sort((a, b) => a.localeCompare(b, 'id'));

            const provBaris = (daftar) => daftar.length
                ? daftar.map((n) => {
                    const id = provIdKustom(n);

                    return '<div class="orcha-pick-row">'
                        + '<button type="button" class="orcha-pick-item" data-nilai="' + provEsc(n)
                        + '" data-cari="' + provEsc(String(n).toLowerCase()) + '">'
                        + '<i class="bi bi-geo-alt me-2" style="color:var(--orc-primer);"><\/i>' + provEsc(n)
                        + '<\/button>'
                        + (id ? '<button type="button" class="orcha-pick-del" data-id="' + id
                            + '" title="Hapus dari daftar"><i class="bi bi-trash3"><\/i><\/button>' : '')
                        + '<\/div>';
                }).join('')
                : '<div class="orcha-pick-empty">Belum ada provinsi di wilayah ini. Pakai "Tulis sendiri" di bawah.<\/div>';

            window.orchaPilihProvinsi = function (tombol) {
                if (typeof Swal === 'undefined') return;

                const wadah = tombol.closest('[wire\\:id]');
                if (!wadah) return;

                const cid = wadah.getAttribute('wire:id');
                const komponen = () => window.Livewire && window.Livewire.find(cid);

                const pasangPendengar = () => {
                    const daftarEl = document.getElementById('orchaProvDaftar');
                    if (!daftarEl) return;

                    daftarEl.querySelectorAll('.orcha-pick-item').forEach((b) => {
                        b.addEventListener('click', () => {
                            komponen() && komponen().set('provinsi', b.dataset.nilai);
                            Swal.close();
                        });
                    });

                    daftarEl.querySelectorAll('.orcha-pick-del').forEach((b) => {
                        b.addEventListener('click', (ev) => {
                            // Jangan sampai menghapus berarti sekaligus memilih.
                            ev.stopPropagation();
                            b.disabled = true;
                            komponen() && komponen().call('hapusProvinsi', Number(b.dataset.id));
                        });
                    });
                };

                // Daftarnya digambar ulang di tempat sesudah ada entri ditambah
                // atau dihapus, tanpa menutup popupnya.
                const gambarUlang = () => {
                    const daftarEl = document.getElementById('orchaProvDaftar');
                    if (!daftarEl) return;
                    daftarEl.innerHTML = provBaris(provDaftar());
                    pasangPendengar();
                    const cari = document.getElementById('orchaProvCari');
                    if (cari) cari.dispatchEvent(new Event('input'));
                };
                window.__orchaProvGambarUlang = gambarUlang;

                Swal.fire({
                    title: 'Pilih Provinsi',
                    html: '<input id="orchaProvCari" class="form-control mb-2" placeholder="Ketik untuk mencari provinsi…">'
                        + '<div id="orchaProvDaftar" class="orcha-pick-list">' + provBaris(provDaftar()) + '<\/div>'
                        + '<div id="orchaProvKosong" class="orcha-pick-empty" style="display:none">Tidak ada yang cocok. Pakai "Tulis sendiri" di bawah.<\/div>'
                        + '<button type="button" id="orchaProvManual" class="orcha-pick-item mt-2" style="border-style:dashed;">'
                        + '<i class="bi bi-plus-circle me-2" style="color:#64748b;"><\/i>Tulis sendiri &amp; tambahkan ke daftar…<\/button>',
                    background: 'rgba(255, 255, 255, 0.92)',
                    backdrop: 'rgba(124, 58, 237, 0.15)',
                    customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                    buttonsStyling: false, showConfirmButton: false, showCloseButton: true,
                    width: 480, padding: '1.25rem',
                    willClose: () => { window.__orchaProvGambarUlang = null; },
                    didOpen: () => {
                        const cari = document.getElementById('orchaProvCari');
                        const daftarEl = document.getElementById('orchaProvDaftar');
                        const kosong = document.getElementById('orchaProvKosong');

                        if (cari) {
                            cari.addEventListener('input', () => {
                                const q = cari.value.toLowerCase().trim();
                                let terlihat = 0;
                                daftarEl.querySelectorAll('.orcha-pick-row').forEach((baris) => {
                                    const b = baris.querySelector('.orcha-pick-item');
                                    const cocok = b.dataset.cari.includes(q);
                                    baris.style.display = cocok ? '' : 'none';
                                    if (cocok) terlihat++;
                                });
                                // Daftar kosong tanpa keterangan terbaca seperti
                                // halaman rusak, bukan seperti "tidak ada yang cocok".
                                kosong.style.display = terlihat === 0 && provDaftar().length ? '' : 'none';
                            });
                            setTimeout(() => cari.focus(), 100);
                        }

                        pasangPendengar();

                        const manual = document.getElementById('orchaProvManual');
                        if (manual) manual.addEventListener('click', () => {
                            Swal.fire({
                                title: 'Tambah Provinsi',
                                input: 'text',
                                inputPlaceholder: 'mis. Papua Barat Laut',
                                text: 'Ditambahkan ke wilayah yang sedang dipilih.',
                                background: 'rgba(255, 255, 255, 0.92)',
                                backdrop: 'rgba(124, 58, 237, 0.15)',
                                customClass: {
                                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold',
                                    confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel',
                                },
                                buttonsStyling: false, showCancelButton: true,
                                confirmButtonText: 'Tambahkan', cancelButtonText: 'Batal',
                                inputValidator: (v) => (v && v.trim() !== '') ? undefined : 'Masih kosong.',
                            }).then((h) => {
                                if (!h.isConfirmed || !h.value) return;
                                // Sekali ditulis langsung terdaftar — bukan hanya
                                // mengisi isian lalu hilang saat halaman ditutup.
                                komponen() && komponen().call('tambahProvinsi', h.value.trim());
                            });
                        });
                    },
                });
            };

            /**
             * Pemilih wilayah — bentuknya sama, tetapi TANPA "tulis sendiri".
             *
             * Wilayah bukan sekadar isian: keenamnya jadi tab penyaring di
             * halaman publik dan dipakai kartu destinasi maupun armada.
             * Menambah wilayah ketujuh dari sini akan menghasilkan wilayah yang
             * tidak punya tab, tidak punya urutan, dan destinasinya tidak
             * ketemu oleh siapa pun.
             */
            window.orchaPilihWilayah = function (tombol) {
                if (typeof Swal === 'undefined') return;

                const wadah = tombol.closest('[wire\\:id]');
                if (!wadah) return;

                const cid = wadah.getAttribute('wire:id');
                const komponen = () => window.Livewire && window.Livewire.find(cid);
                const sekarang = provWilayah();

                // Hanya wilayah tambahan yang punya tombol hapus; yang bawaan
                // ikut versi kode dan dipakai destinasi yang sudah ada.
                const wilIdKustom = (kunci) => {
                    const cocok = (window.__orchaWilayahKustom || []).find((e) => e.kunci === kunci);

                    return cocok ? cocok.id : null;
                };

                const wilBaris = () => Object.entries(window.__orchaDaftarWilayah || {})
                    .map(([kunci, label]) => {
                        const id = wilIdKustom(kunci);

                        return '<div class="orcha-pick-row">'
                            + '<button type="button" class="orcha-pick-item' + (kunci === sekarang ? ' terpilih' : '')
                            + '" data-nilai="' + provEsc(kunci)
                            + '" data-cari="' + provEsc(String(label).toLowerCase()) + '">'
                            + '<i class="bi bi-compass me-2" style="color:var(--orc-primer);"><\/i>' + provEsc(label)
                            + '<\/button>'
                            + (id ? '<button type="button" class="orcha-pick-del" data-id="' + id
                                + '" title="Hapus dari daftar"><i class="bi bi-trash3"><\/i><\/button>' : '')
                            + '<\/div>';
                    }).join('');

                Swal.fire({
                    title: 'Pilih Wilayah',
                    html: '<input id="orchaWilCari" class="form-control mb-2" placeholder="Ketik untuk mencari wilayah…">'
                        + '<div id="orchaWilDaftar" class="orcha-pick-list">' + wilBaris() + '<\/div>'
                        + '<button type="button" id="orchaWilManual" class="orcha-pick-item mt-2" style="border-style:dashed;">'
                        + '<i class="bi bi-plus-circle me-2" style="color:#64748b;"><\/i>Tulis sendiri &amp; tambahkan ke daftar…<\/button>',
                    background: 'rgba(255, 255, 255, 0.92)',
                    backdrop: 'rgba(124, 58, 237, 0.15)',
                    customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                    buttonsStyling: false, showConfirmButton: false, showCloseButton: true,
                    width: 460, padding: '1.25rem',
                    didOpen: () => {
                        const cari = document.getElementById('orchaWilCari');
                        const daftarEl = document.getElementById('orchaWilDaftar');

                        if (cari) {
                            cari.addEventListener('input', () => {
                                const q = cari.value.toLowerCase().trim();
                                daftarEl.querySelectorAll('.orcha-pick-row').forEach((r) => {
                                    r.style.display = r.querySelector('.orcha-pick-item')
                                        .dataset.cari.includes(q) ? '' : 'none';
                                });
                            });
                            setTimeout(() => cari.focus(), 100);
                        }

                        const pasangWil = () => {
                            daftarEl.querySelectorAll('.orcha-pick-item').forEach((b) => {
                                b.addEventListener('click', () => {
                                    komponen() && komponen().set('wilayah', b.dataset.nilai);
                                    Swal.close();
                                });
                            });

                            daftarEl.querySelectorAll('.orcha-pick-del').forEach((b) => {
                                b.addEventListener('click', (ev) => {
                                    // Jangan sampai menghapus berarti sekaligus memilih.
                                    ev.stopPropagation();
                                    b.disabled = true;
                                    komponen() && komponen().call('hapusWilayah', Number(b.dataset.id));
                                });
                            });
                        };

                        pasangWil();
                        window.__orchaWilGambarUlang = () => { daftarEl.innerHTML = wilBaris(); pasangWil(); };

                        const manual = document.getElementById('orchaWilManual');
                        if (manual) manual.addEventListener('click', () => {
                            Swal.fire({
                                title: 'Tambah Wilayah',
                                input: 'text',
                                inputPlaceholder: 'mis. Jalur Rempah',
                                text: 'Langsung jadi tab penyaring di halaman Destinasi.',
                                background: 'rgba(255, 255, 255, 0.92)',
                                backdrop: 'rgba(124, 58, 237, 0.15)',
                                customClass: {
                                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold',
                                    confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel',
                                },
                                buttonsStyling: false, showCancelButton: true,
                                confirmButtonText: 'Tambahkan', cancelButtonText: 'Batal',
                                inputValidator: (v) => (v && v.trim() !== '') ? undefined : 'Masih kosong.',
                            }).then((h) => {
                                if (!h.isConfirmed || !h.value) return;
                                komponen() && komponen().call('tambahWilayah', h.value.trim());
                            });
                        });
                    },
                    willClose: () => { window.__orchaWilGambarUlang = null; },
                });
            };

            /**
             * Pemilih daerah — menyaring mengikuti provinsi yang sedang dipilih,
             * sama seperti provinsi menyaring mengikuti wilayah.
             */
            window.orchaPilihDaerah = function (tombol) {
                if (typeof Swal === 'undefined') return;

                const wadah = tombol.closest('[wire\\:id]');
                if (!wadah) return;

                const cid = wadah.getAttribute('wire:id');
                const komponen = () => window.Livewire && window.Livewire.find(cid);

                // Dibaca dari DOM, bukan dari nilai yang dibekukan <script>.
                const provSekarang = () => {
                    const penanda = document.querySelector('[data-orcha-provinsi]');

                    return penanda ? penanda.getAttribute('data-orcha-provinsi') : '';
                };

                const daerahIdKustom = (nama) => {
                    const cocok = (window.__orchaKatalogDaerahKustom || [])
                        .find((e) => e.nama === nama && e.provinsi === provSekarang());

                    return cocok ? cocok.id : null;
                };

                const daerahDaftar = () => Object.keys(window.__orchaKatalogDaerah || {})
                    .filter((n) => window.__orchaKatalogDaerah[n] === provSekarang())
                    .sort((a, b) => a.localeCompare(b, 'id'));

                const daerahBaris = () => {
                    const daftar = daerahDaftar();

                    if (! provSekarang()) {
                        return '<div class="orcha-pick-empty">Pilih provinsinya dulu — daftar daerah mengikuti provinsi.<\/div>';
                    }

                    if (! daftar.length) {
                        return '<div class="orcha-pick-empty">Belum ada daerah di provinsi ini. Pakai "Tulis sendiri" di bawah.<\/div>';
                    }

                    return daftar.map((n) => {
                        const id = daerahIdKustom(n);

                        return '<div class="orcha-pick-row">'
                            + '<button type="button" class="orcha-pick-item" data-nilai="' + provEsc(n)
                            + '" data-cari="' + provEsc(String(n).toLowerCase()) + '">'
                            + '<i class="bi bi-pin-map me-2" style="color:var(--orc-primer);"><\/i>' + provEsc(n)
                            + '<\/button>'
                            + (id ? '<button type="button" class="orcha-pick-del" data-id="' + id
                                + '" title="Hapus dari daftar"><i class="bi bi-trash3"><\/i><\/button>' : '')
                            + '<\/div>';
                    }).join('');
                };

                const pasangDaerah = () => {
                    const daftarEl = document.getElementById('orchaDaerahDaftar');
                    if (!daftarEl) return;

                    daftarEl.querySelectorAll('.orcha-pick-item').forEach((b) => {
                        b.addEventListener('click', () => {
                            komponen() && komponen().set('daerah', b.dataset.nilai);
                            Swal.close();
                        });
                    });

                    daftarEl.querySelectorAll('.orcha-pick-del').forEach((b) => {
                        b.addEventListener('click', (ev) => {
                            ev.stopPropagation();
                            b.disabled = true;
                            komponen() && komponen().call('hapusDaerah', Number(b.dataset.id));
                        });
                    });
                };

                Swal.fire({
                    title: 'Pilih Daerah',
                    html: '<input id="orchaDaerahCari" class="form-control mb-2" placeholder="Ketik untuk mencari daerah…">'
                        + '<div id="orchaDaerahDaftar" class="orcha-pick-list">' + daerahBaris() + '<\/div>'
                        + '<button type="button" id="orchaDaerahManual" class="orcha-pick-item mt-2" style="border-style:dashed;">'
                        + '<i class="bi bi-plus-circle me-2" style="color:#64748b;"><\/i>Tulis sendiri &amp; tambahkan ke daftar…<\/button>',
                    background: 'rgba(255, 255, 255, 0.92)',
                    backdrop: 'rgba(124, 58, 237, 0.15)',
                    customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                    buttonsStyling: false, showConfirmButton: false, showCloseButton: true,
                    width: 480, padding: '1.25rem',
                    willClose: () => { window.__orchaDaerahGambarUlang = null; },
                    didOpen: () => {
                        const cari = document.getElementById('orchaDaerahCari');
                        const daftarEl = document.getElementById('orchaDaerahDaftar');

                        if (cari) {
                            cari.addEventListener('input', () => {
                                const q = cari.value.toLowerCase().trim();
                                daftarEl.querySelectorAll('.orcha-pick-row').forEach((r) => {
                                    r.style.display = r.querySelector('.orcha-pick-item')
                                        .dataset.cari.includes(q) ? '' : 'none';
                                });
                            });
                            setTimeout(() => cari.focus(), 100);
                        }

                        pasangDaerah();
                        window.__orchaDaerahGambarUlang = () => {
                            daftarEl.innerHTML = daerahBaris();
                            pasangDaerah();
                        };

                        const manual = document.getElementById('orchaDaerahManual');
                        if (manual) manual.addEventListener('click', () => {
                            if (! provSekarang()) {
                                Swal.fire({
                                    title: 'Pilih provinsinya dulu',
                                    text: 'Daerah disimpan bersama provinsinya, supaya daftarnya bisa disaring.',
                                    icon: 'info',
                                    background: 'rgba(255, 255, 255, 0.92)',
                                    customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' },
                                    buttonsStyling: false,
                                    confirmButtonText: 'Mengerti',
                                });

                                return;
                            }

                            Swal.fire({
                                title: 'Tambah Daerah',
                                input: 'text',
                                inputPlaceholder: 'mis. Situbondo',
                                text: 'Ditambahkan ke provinsi ' + provSekarang() + '.',
                                background: 'rgba(255, 255, 255, 0.92)',
                                backdrop: 'rgba(124, 58, 237, 0.15)',
                                customClass: {
                                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold',
                                    confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel',
                                },
                                buttonsStyling: false, showCancelButton: true,
                                confirmButtonText: 'Tambahkan', cancelButtonText: 'Batal',
                                inputValidator: (v) => (v && v.trim() !== '') ? undefined : 'Masih kosong.',
                            }).then((h) => {
                                if (!h.isConfirmed || !h.value) return;
                                komponen() && komponen().call('tambahDaerah', h.value.trim());
                            });
                        });
                    },
                });
            };

            window.addEventListener('orcha-daerah-segar', function (e) {
                const d = e.detail || {};
                if (d.katalog) window.__orchaKatalogDaerah = d.katalog;
                if (d.kustom) window.__orchaKatalogDaerahKustom = d.kustom;
                if (window.__orchaDaerahGambarUlang) window.__orchaDaerahGambarUlang();
            });

            /**
             * Pemilih nama destinasi.
             *
             * Barisnya menyebut provinsinya sekalian: yang membuat daftar ini
             * berguna bukan namanya — admin sudah tahu namanya — melainkan
             * provinsi yang ikut terisi begitu dipilih.
             */
            window.orchaPilihDestinasi = function (tombol) {
                if (typeof Swal === 'undefined') return;

                const wadah = tombol.closest('[wire\\:id]');
                if (!wadah) return;

                const cid = wadah.getAttribute('wire:id');
                const komponen = () => window.Livewire && window.Livewire.find(cid);

                const destIdKustom = (nama) => {
                    const cocok = (window.__orchaKatalogDestinasiKustom || []).find((e) => e.nama === nama);

                    return cocok ? cocok.id : null;
                };

                const destBaris = () => {
                    const katalog = window.__orchaKatalogDestinasi || {};
                    const nama = Object.keys(katalog).sort((a, b) => a.localeCompare(b, 'id'));

                    if (!nama.length) {
                        return '<div class="orcha-pick-empty">Daftar belum termuat. Pakai "Tulis sendiri" di bawah.<\/div>';
                    }

                    return nama.map((n) => {
                        const id = destIdKustom(n);
                        const baris = katalog[n] || {};
                        // Daerahnya ikut disebut dan ikut dicari: admin yang ingat
                        // "yang di Banyuwangi itu" menemukannya tanpa ingat namanya.
                        const alamat = [baris.daerah, baris.provinsi].filter(Boolean).join(', ');

                        return '<div class="orcha-pick-row">'
                            + '<button type="button" class="orcha-pick-item" data-nilai="' + provEsc(n)
                            + '" data-cari="' + provEsc((n + ' ' + alamat).toLowerCase()) + '">'
                            + '<i class="bi bi-geo me-2" style="color:var(--orc-primer);"><\/i>' + provEsc(n)
                            + (alamat ? '<small class="text-muted ms-2">' + provEsc(alamat) + '<\/small>' : '')
                            + '<\/button>'
                            + (id ? '<button type="button" class="orcha-pick-del" data-id="' + id
                                + '" title="Hapus dari daftar"><i class="bi bi-trash3"><\/i><\/button>' : '')
                            + '<\/div>';
                    }).join('');
                };

                const pasangDest = () => {
                    const daftarEl = document.getElementById('orchaDestDaftar');
                    if (!daftarEl) return;

                    daftarEl.querySelectorAll('.orcha-pick-item').forEach((b) => {
                        b.addEventListener('click', () => {
                            komponen() && komponen().call('pilihDestinasi', b.dataset.nilai);
                            Swal.close();
                        });
                    });

                    daftarEl.querySelectorAll('.orcha-pick-del').forEach((b) => {
                        b.addEventListener('click', (ev) => {
                            ev.stopPropagation();
                            b.disabled = true;
                            komponen() && komponen().call('hapusKatalogDestinasi', Number(b.dataset.id));
                        });
                    });
                };

                Swal.fire({
                    title: 'Pilih Destinasi',
                    html: '<input id="orchaDestCari" class="form-control mb-2" placeholder="Ketik nama atau provinsinya…">'
                        + '<div id="orchaDestDaftar" class="orcha-pick-list">' + destBaris() + '<\/div>'
                        + '<div id="orchaDestKosong" class="orcha-pick-empty" style="display:none">Tidak ada yang cocok. Pakai "Tulis sendiri" di bawah.<\/div>'
                        + '<button type="button" id="orchaDestManual" class="orcha-pick-item mt-2" style="border-style:dashed;">'
                        + '<i class="bi bi-plus-circle me-2" style="color:#64748b;"><\/i>Tulis sendiri &amp; tambahkan ke daftar…<\/button>',
                    background: 'rgba(255, 255, 255, 0.92)',
                    backdrop: 'rgba(124, 58, 237, 0.15)',
                    customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                    buttonsStyling: false, showConfirmButton: false, showCloseButton: true,
                    width: 520, padding: '1.25rem',
                    willClose: () => { window.__orchaDestGambarUlang = null; },
                    didOpen: () => {
                        const cari = document.getElementById('orchaDestCari');
                        const daftarEl = document.getElementById('orchaDestDaftar');
                        const kosong = document.getElementById('orchaDestKosong');

                        if (cari) {
                            // Pencariannya ikut membaca provinsi: admin yang tahu
                            // tujuannya di Jawa Timur tetapi lupa namanya tetap
                            // bisa menemukannya.
                            cari.addEventListener('input', () => {
                                const q = cari.value.toLowerCase().trim();
                                let terlihat = 0;
                                daftarEl.querySelectorAll('.orcha-pick-row').forEach((r) => {
                                    const cocok = r.querySelector('.orcha-pick-item').dataset.cari.includes(q);
                                    r.style.display = cocok ? '' : 'none';
                                    if (cocok) terlihat++;
                                });
                                kosong.style.display = terlihat === 0 ? '' : 'none';
                            });
                            setTimeout(() => cari.focus(), 100);
                        }

                        pasangDest();
                        window.__orchaDestGambarUlang = () => { daftarEl.innerHTML = destBaris(); pasangDest(); };

                        const manual = document.getElementById('orchaDestManual');
                        if (manual) manual.addEventListener('click', () => {
                            Swal.fire({
                                title: 'Tambah Destinasi ke Daftar',
                                input: 'text',
                                inputPlaceholder: 'mis. Pantai Pulau Merah',
                                text: 'Provinsinya dicari otomatis bila dikenali.',
                                background: 'rgba(255, 255, 255, 0.92)',
                                backdrop: 'rgba(124, 58, 237, 0.15)',
                                customClass: {
                                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold',
                                    confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel',
                                },
                                buttonsStyling: false, showCancelButton: true,
                                confirmButtonText: 'Tambahkan', cancelButtonText: 'Batal',
                                inputValidator: (v) => (v && v.trim() !== '') ? undefined : 'Masih kosong.',
                            }).then((h) => {
                                if (!h.isConfirmed || !h.value) return;
                                komponen() && komponen().call('tambahDestinasi', h.value.trim());
                            });
                        });
                    },
                });
            };

            window.addEventListener('orcha-katalog-destinasi-segar', function (e) {
                const d = e.detail || {};
                if (d.katalog) window.__orchaKatalogDestinasi = d.katalog;
                if (d.kustom) window.__orchaKatalogDestinasiKustom = d.kustom;
                if (window.__orchaDestGambarUlang) window.__orchaDestGambarUlang();
            });

            // Daftar wilayah terbaru dari server.
            window.addEventListener('orcha-wilayah-segar', function (e) {
                const d = e.detail || {};
                if (d.daftar) window.__orchaDaftarWilayah = d.daftar;
                if (d.kustom) window.__orchaWilayahKustom = d.kustom;
                if (window.__orchaWilGambarUlang) window.__orchaWilGambarUlang();
            });

            // Daftar terbaru dari server: dipasang ke global, lalu popup yang
            // sedang terbuka digambar ulang di tempat.
            window.addEventListener('orcha-provinsi-segar', function (e) {
                const d = e.detail || {};
                if (d.peta) window.__orchaPetaProvinsi = d.peta;
                if (d.kustom) window.__orchaProvinsiKustom = d.kustom;
                if (window.__orchaProvGambarUlang) window.__orchaProvGambarUlang();
            });
        }
    </script>

    <style>
        .orcha-sub-foto {
            padding: .85rem;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            background: #fbfdff;
        }

        .orcha-sub-kepala {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: .1rem .6rem;
            margin-bottom: .6rem;
        }

        .orcha-sub-kepala .judul {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .82rem;
            font-weight: 700;
            color: var(--orc-tinta);
        }

        .orcha-sub-kepala .ket { font-size: .74rem; color: #64748b; }

        /* Petak seukuran sama supaya deretannya rapi berapa pun rasio
           gambarnya — kartu di website pun memotongnya begitu. */
        .orcha-sub-petak {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(6.5rem, 1fr));
            gap: .6rem;
        }

        .orcha-sub-petak .petak {
            position: relative;
            aspect-ratio: 4 / 3;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e3e8ef;
            background: #eef2f6;
        }

        .orcha-sub-petak .petak img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Yang baru dipilih ditandai: sebelum disimpan, keduanya terlihat sama
           padahal yang satu belum tersimpan di mana pun. */
        .orcha-sub-petak .petak.baru { border-color: #9fd0b4; }

        .orcha-sub-petak .tanda {
            position: absolute;
            left: .35rem;
            bottom: .35rem;
            padding: .05rem .35rem;
            border-radius: 5px;
            background: rgba(26, 138, 82, .92);
            color: #fff;
            font-size: .62rem;
            font-weight: 700;
        }

        .orcha-sub-petak .buang {
            position: absolute;
            top: .3rem;
            right: .3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.4rem;
            height: 1.4rem;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: rgba(31, 45, 61, .78);
            color: #fff;
            font-size: .62rem;
            line-height: 1;
        }

        .orcha-sub-petak .buang:hover { background: #c2323c; }

        .orcha-sub-kosong,
        .orcha-sub-penuh {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin: 0;
            font-size: .78rem;
            color: #64748b;
        }

        .orcha-sub-penuh { color: #1a6b43; }

        /* Pratinjau kartu destinasi — bentuknya menirukan kartu di website,
           supaya admin melihat hasilnya sebelum menyimpan, bukan setelah
           membuka website di tab lain. */
        .orcha-dest-foto {
            position: relative;
            aspect-ratio: 4 / 3;
            background: #eef2f6;
        }

        .orcha-dest-foto > img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Gradasi gelap di dasar foto: judul putih di atas foto terang tidak
           terbaca tanpa itu. */
        .orcha-dest-foto::after {
            content: '';
            position: absolute;
            inset: auto 0 0 0;
            height: 60%;
            background: linear-gradient(to top, rgba(31, 45, 61, .88), transparent);
        }

        .orcha-dest-lencana {
            position: absolute;
            top: .55rem;
            left: .55rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .55rem;
            border-radius: 2rem;
            background: #ffc74e;
            color: var(--orc-tinta);
            font-size: .68rem;
            font-weight: 700;
        }

        .orcha-dest-judul {
            position: absolute;
            inset: auto .8rem .7rem;
            z-index: 2;
            color: #fff;
        }

        .orcha-dest-judul strong {
            display: block;
            font-size: 1rem;
            line-height: 1.25;
        }

        .orcha-dest-judul span {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            margin-top: .15rem;
            font-size: .74rem;
            color: #e2e8f0;
        }

        .orcha-dest-isi { padding: .85rem; }

        .orcha-dest-isi .ket {
            margin: 0;
            font-size: .78rem;
            color: #475569;
            line-height: 1.5;
        }

        .orcha-dest-galeri {
            display: flex;
            gap: .4rem;
            margin-top: .7rem;
        }

        .orcha-dest-galeri img {
            width: 2.6rem;
            height: 2.6rem;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e3ecf3;
        }

        /* Tombol "pilih dari daftar" di samping nama destinasi.

           Sewarna dengan tombol utama halaman ini, karena memang tindakan yang
           paling menghemat pekerjaan: satu ketukan mengisi nama, daerah,
           provinsi, dan wilayah sekaligus.

           Radiusnya 12px, ANGKA YANG SAMA dengan .form-control di layout
           lemon. Sebelumnya 10px, dan isian di sebelahnya tetap membulat penuh
           di kanan — jadi tombolnya terbaca sebagai benda lain yang kebetulan
           berdempetan, bukan bagian dari isian yang sama. */
        .orcha-tombol-daftar {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem .9rem;
            border: 0;
            border-radius: 0 12px 12px 0;
            background: linear-gradient(135deg, var(--orc-primer), var(--orc-primer-2));
            color: #fff;
            font-size: .82rem;
            font-weight: 600;
            white-space: nowrap;
            transition: box-shadow .15s ease, transform .15s ease;
        }

        .orcha-tombol-daftar:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(124, 58, 237, .34);
        }

        .orcha-tombol-daftar > i { font-size: .95rem; line-height: 1; }

        /* Isian nama dan tombolnya dibaca sebagai SATU kendali.

           Tiga hal harus dilawan sekaligus, ketiganya terukur di peramban:

           1. layout lemon memasang border-radius: 12px !important ke SETIAP
              .form-control, sehingga aturan input-group bawaan Bootstrap kalah
              dan sisi kanan isian tetap membulat;
           2. border kanan isian dihapus, bukan ditimpa. Menimpanya dengan
              margin negatif tidak berhasil: Bootstrap memberi .form-control
              position: relative, jadi isiannya tergambar DI ATAS tombol dan
              bordernya muncul kembali di atas tumpangan itu;
           3. geseran -1px bawaan input-group ikut dinolkan — tanpa border,
              geseran itu justru menyelipkan latar putih isian di atas tepi
              kiri tombol.

           Hasilnya kedua tepinya bertemu persis, tanpa garis dan tanpa celah. */
        .orcha-gabung .form-control {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border-right: 0 !important;
        }

        .orcha-gabung .orcha-tombol-daftar {
            margin-left: 0 !important;
        }

        /* Rantai lokasi: tiga pemilih berurutan dengan panah di antaranya.
           Panahnya hanya di layar lebar — bertumpuk, arah "berikutnya" sudah
           terbaca dari urutan atas-bawah. */
        .orcha-rantai {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: .5rem;
        }

        /* Tiap isian berbagi sisa ruang rata; min-width menjaga nama provinsi
           yang panjang tetap terbaca sebelum barisnya membungkus. */
        .orcha-rantai-isi {
            flex: 1 1 12rem;
            min-width: 0;
        }

        .orcha-rantai-panah {
            flex: 0 0 auto;
            flex-direction: column;
            /* Setinggi baris, supaya sisa ruang di bawah pengganjal label persis
               setinggi pemilih di sebelahnya. */
            align-self: stretch;
            color: #cbd5e1;
        }

        /* Ukuran panahnya diatur di ikonnya, bukan di kolomnya: mengecilkan
           huruf kolom ikut mengecilkan pengganjal label di dalamnya — .small
           dihitung dari induknya — dan pengganjal yang lebih pendek menaikkan
           panahnya dari pusat pemilih. */
        .orcha-rantai-panah .ikon {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
        }

        /* Hasil akhirnya: bunyi alamat yang akan dibaca pengunjung. */
        .orcha-alamat-jadi {
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem .8rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #f8fbfd, #eef6fb);
            border: 1px solid #e3ecf3;
            font-size: .8rem;
            color: #475569;
        }

        .orcha-alamat-jadi strong { color: var(--orc-tinta); }

        /* Keterangan usulan lokasi: hijau lembut, bukan kuning peringatan —
           ini kabar baik yang menghemat pekerjaan, bukan sesuatu yang salah. */
        .orcha-usulan {
            display: flex;
            align-items: flex-start;
            gap: .4rem;
            margin-top: .35rem;
            padding: .4rem .6rem;
            border-radius: 9px;
            background: #eef8f2;
            color: #1a6b43;
            font-size: .76rem;
            line-height: 1.4;
        }

        .orcha-usulan > i { line-height: 1.4; }

        /* Kesiapan: bukan .orcha-ringkas.

           Nama itu sudah dipakai kartu angka bersama di gaya.blade.php —
           penyewaan, pendaftaran, pembatalan, dan armada memakainya — dan
           berkas gaya itu ikut termuat di halaman ini. Mendefinisikan ulang
           namanya di sini berarti aturan lokal ini menimpa kartu bersama
           tersebut begitu ada satu saja dipasang di halaman ini. */
        .orcha-siap-kepala {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            font-size: .78rem;
            color: #64748b;
        }

        .orcha-siap-kepala strong { color: var(--orc-tinta); }

        .orcha-siap-bar {
            height: 6px;
            margin: .4rem 0 .75rem;
            border-radius: 99px;
            background: #e6eef5;
            overflow: hidden;
        }

        .orcha-siap-bar > span {
            display: block;
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--orc-primer), var(--orc-primer-2));
            transition: width .25s ease;
        }

        /* SATU grid untuk seluruh daftar, bukan satu grid per baris.

           Dua sebabnya, keduanya terlihat begitu isinya panjang:

           - kolom yang dibentuk per baris tidak pernah lurus antar baris,
             karena tiap baris menghitung lebarnya sendiri;
           - kolom keterangan yang lentur akan diremas habis oleh nilai yang
             panjang. Dengan nama "Taman Nasional Bromo Tengger Semeru",
             keterangannya sempat tersisa selebar SATU huruf dan menurun ke
             bawah sehuruf demi sehuruf.

           Kolom keterangan karena itu max-content: selebar keterangan
           terpanjang, tidak pernah lebih sempit. Yang melar dan boleh
           membungkus kolom nilainya. */
        .orcha-siap-daftar {
            display: grid;
            grid-template-columns: auto max-content minmax(0, 1fr);
            /* stretch, bukan baseline: garis putus di bawah tiap sel harus
               jatuh di ketinggian yang sama. Dengan baseline, sel yang isinya
               satu baris berhenti lebih tinggi daripada sel di sebelahnya yang
               membungkus dua baris, dan garisnya patah bertingkat. */
            align-items: stretch;
            font-size: .78rem;
            /* Tinggi baris sebagai PANJANG, bukan kelipatan. Kelipatan dikalikan
               ke ukuran huruf masing-masing sel, sehingga kotak baris ikonnya
               (yang hurufnya sedikit lebih besar) berbeda dari kotak baris
               teksnya dan garis dasarnya bergeser. Panjang diwarisi apa adanya
               oleh ketiganya. */
            line-height: 1.2rem;
        }

        .orcha-siap-baris { display: contents; }

        /* Jarak antar kolom lewat padding sel, bukan column-gap: garis putus
           di bawahnya milik tiap sel, dan gap akan menyisakan lubang di
           antaranya. */
        .orcha-siap-baris > * {
            padding: .42rem 0;
            border-bottom: 1px dashed #e9f0f6;
        }

        .orcha-siap-baris > .tanda,
        .orcha-siap-baris > .label { padding-right: .5rem; }

        .orcha-siap-baris:last-child > * { border-bottom: 0; padding-bottom: 0; }

        /* Tema lemon memasang .bi { width: 1rem; height: 1rem } untuk SEMUA
           ikon. Tinggi yang dipatok membatalkan align-items: stretch —
           merentang hanya berlaku pada tinggi auto — sehingga sel ikonnya
           berhenti 16px sementara sel di sebelahnya 33,6px, dan garis putus di
           bawahnya jatuh lebih tinggi daripada garis di sebelahnya. Terukur,
           bukan dikira: selisih tepi bawahnya 17,6px. */
        .orcha-siap-baris > .tanda {
            width: auto;
            height: auto;
            font-size: .82rem;
        }
        .orcha-siap-baris .tanda.ada { color: #2f9e6e; }
        .orcha-siap-baris .tanda.kurang { color: #d97706; }
        .orcha-siap-baris .tanda.kosong { color: #cbd5e1; }

        .orcha-siap-baris .label { color: #475569; }
        .orcha-siap-baris .label .wajib { color: #dc2626; }

        .orcha-siap-baris .nilai {
            text-align: right;
            font-weight: 600;
            color: var(--orc-tinta);
        }

        .orcha-siap-baris .nilai.belum { font-weight: 500; color: #94a3b8; }

        .orcha-siap-nota {
            display: flex;
            align-items: flex-start;
            gap: .4rem;
            margin: .8rem 0 0;
            padding: .5rem .65rem;
            border-radius: 10px;
            font-size: .76rem;
            line-height: 1.45;
        }

        .orcha-siap-nota > i { line-height: 1.45; }

        .orcha-siap-nota.siap { background: #eef8f2; color: #1a6b43; }
        .orcha-siap-nota.kurang { background: #fff7ed; color: #9a3412; }
    </style>
</div>
