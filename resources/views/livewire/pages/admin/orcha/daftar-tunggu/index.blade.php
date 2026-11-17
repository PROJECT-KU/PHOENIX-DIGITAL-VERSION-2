@section('title')
Daftar Tunggu || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    {{-- <style> telanjang: layout templateindex tidak punya satu pun stack,
         jadi gaya yang didorong ke sana tidak pernah ikut tergambar. --}}
    <style>
        /* Laci pilihan rombongan pada tombol tahan kursi.

           <details>, bukan menu ber-JS: layar ini tidak memuat Alpine untuk
           apa pun yang lain, dan satu menu yang menuntut skrip hidup berarti
           satu tombol yang diam saat skripnya tidak sempat termuat. */
        .dt-laci { position: relative; display: inline-block; }
        .dt-laci > summary { list-style: none; cursor: pointer; }
        .dt-laci > summary::-webkit-details-marker { display: none; }
        .dt-laci-isi {
            position: absolute; z-index: 40; right: 0; top: calc(100% + 6px);
            min-width: 240px; padding: 8px; border-radius: 13px; text-align: left;
            background: #fff; border: 1px solid #e3e8ef;
            box-shadow: 0 18px 36px -12px rgba(15, 23, 42, .28);
        }
        .dt-laci-judul {
            display: block; padding: 2px 8px 6px;
            font-size: .72rem; font-weight: 700; letter-spacing: .04em;
            text-transform: uppercase; color: #94a3b8;
        }
        .dt-laci-isi > button {
            display: flex; align-items: center; gap: 8px; width: 100%;
            padding: 8px 10px; border: 0; border-radius: 9px; cursor: pointer;
            background: none; text-align: left; color: #334155;
            font-size: .83rem; font-weight: 600;
        }
        .dt-laci-isi > button small { margin-left: auto; font-weight: 500; color: #94a3b8; }
        .dt-laci-isi > button:hover { background: #f1f5f9; }
    </style>

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Daftar Tunggu',
            'keterangan' => 'Peminat yang menunggu kursi terbuka di trip yang sudah penuh.',
        ])

        {{-- Keterangan pembuka, bukan hiasan.

             Yang membuka layar ini akan bertanya apakah ia harus menghubungi
             mereka satu per satu. Jawabannya tidak — sistem sudah melakukannya.
             Dijawab di sini supaya tidak ada pekerjaan ganda, dan supaya yang
             MEMANG perlu dikerjakan manusia terlihat jelas. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-bagian-kepala mb-0">
                    <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <div class="orcha-bagian-judul">Mereka dikabari otomatis</div>
                        <div class="orcha-bagian-sub">
                            Begitu ada kursi yang dilepas — biasanya karena pendaftar lain tidak
                            membayar — yang paling lama menunggu langsung dikabari lewat email,
                            sebanyak kursi yang terbuka saja.
                            <br>
                            Yang perlu Anda kerjakan sendiri dua hal: menghubungi mereka yang
                            <strong>kursinya sudah terbuka tetapi tanpa email</strong> — merekalah
                            yang tidak bisa dijangkau sistem, dan angkanya yang tampil di menu —
                            lalu memutuskan apakah antrean yang panjang layak dibukakan
                            keberangkatan tambahan.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                {{-- Kotak carinya memakai partial bersama, bukan markup sendiri.

                     Layar ini sempat memasang ikon kaca pembesarnya sendiri —
                     position-absolute tanpa memberi padding kiri pada isiannya —
                     sehingga ikonnya menindih tulisan petunjuknya. Partial
                     bersama sudah mengurus jaraknya lewat .form-control-icon dan
                     ps-5, dan sekalian membawa tombol pengosong yang muncul saat
                     ada isinya.

                     Markup sendiri untuk hal yang sudah punya partial adalah cara
                     paling pasti untuk berbeda dari layar lain — dan bedanya baru
                     terlihat setelah ada yang membuka keduanya berurutan. --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div style="flex:2 1 240px">
                        @include('livewire.pages.admin.orcha.partials.cari', [
                            'petunjuk' => 'Cari nama atau nomor WhatsApp...',
                        ])
                    </div>

                    <div style="flex:1 1 200px">
                        <select class="form-select" wire:model.live="filterPaket">
                            <option value="" @selected((string) $this->filterPaket === '')>Semua trip</option>
                            @foreach ($paketPilihan as $id => $nama)
                                {{-- @selected ditulis meski nilainya diikat wire:model:
                                     tanpa itu markup dari server tidak pernah menandai
                                     pilihan yang sedang aktif, dan kotaknya memajang
                                     trip lama sementara daftarnya sudah tidak disaring. --}}
                                <option value="{{ $id }}" @selected((string) $this->filterPaket === (string) $id)>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DUA angka, berdampingan, dan itu yang menghilangkan
                         kebingungannya.

                         Sebelumnya layar cuma menyebut "1 menunggu kursi"
                         sementara penanda di menu kosong — dan yang melihatnya
                         menyimpulkan keduanya tidak sinkron. Padahal keduanya
                         menjawab pertanyaan yang berbeda: yang satu seberapa besar
                         antreannya, yang satu berapa yang menunggu ditelepon.

                         Angka merah di sini SAMA PERSIS dengan yang di menu.
                         Menaruhnya berdampingan membuat hubungan keduanya terlihat
                         sendiri, tanpa perlu satu kalimat penjelasan pun.

                         Yang biru bukan keadaan baik maupun buruk, cuma ukuran —
                         karena itu warnanya berbeda dari lencana status di dalam
                         tabel yang memakai hijau dan merah. --}}
                    <div class="d-flex align-items-center gap-2 ms-lg-auto">
                        <span class="badge bg-primary-subtle text-primary-emphasis"
                            style="font-size:.82rem;padding:.5rem .85rem"
                            title="Seluruh peminat yang masih menunggu kursi terbuka">
                            <i class="bi bi-hourglass-split"></i>
                            {{ $meta['total'] ?? 0 }} menunggu kursi
                        </span>

                        @if (($meta['perlu_dihubungi'] ?? 0) > 0)
                            <span class="badge bg-danger-subtle text-danger-emphasis"
                                style="font-size:.82rem;padding:.5rem .85rem"
                                title="Kursinya sudah terbuka tetapi mereka tanpa email — angka inilah yang tampil di menu">
                                <i class="bi bi-telephone-outbound"></i>
                                {{ $meta['perlu_dihubungi'] }} perlu dihubungi
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            {{-- Bentuk tabelnya mengikuti layar Orcha lain, bukan kelas
                 Bootstrap polos.

                 Layar ini sempat memakai card-body p-0 dan table-responsive —
                 akibatnya keterangan "Menampilkan 1–1 dari 1" menempel di tepi
                 kartu tanpa jarak, sedangkan di layar lain ia berjarak sama
                 dengan isi kartunya. Tabelnya pun kehilangan sorotan baris dan
                 gaya .orcha-tabel.

                 Bedanya kecil satu per satu, tetapi admin berpindah antar layar
                 ini sepanjang hari — dan satu layar yang bentuknya lain
                 terbaca seperti bagian yang belum selesai. --}}
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel mb-0">
                        <thead>
                            <tr>
                                <th>Peminat</th>
                                <th>Trip</th>
                                <th>Menunggu Sejak</th>
                                <th>Kabar</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                @php
                                    /* Pesannya dirakit di komponen, bukan di sini.

                                       Saat admin datang dari layar Pendaftaran
                                       karena satu kursi baru kosong, kalimatnya
                                       harus sudah menyebut TANGGALNYA — pesan
                                       tanpa tanggal menghasilkan percakapan
                                       bolak-balik tentang satu keterangan yang
                                       sudah ada di tangan admin sejak awal. */
                                    $wa = 'https://api.whatsapp.com/send?phone='
                                        . preg_replace('/^0/', '62', preg_replace('/\D/', '', $baris['whatsapp']))
                                        . '&text=' . rawurlencode($this->pesanTawaran($baris));
                                @endphp

                                <tr wire:key="tunggu-{{ $baris['id'] }}">
                                    <td>
                                        <div class="fw-bold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted small">
                                            {{ $baris['whatsapp'] }} · {{ $baris['jumlah_peserta'] }} orang
                                        </div>
                                    </td>

                                    <td class="text-muted small">{{ $baris['paket'] ?? '—' }}</td>

                                    <td class="text-muted small">
                                        {{ $baris['menunggu_sejak']
                                            ? \Carbon\Carbon::parse($baris['menunggu_sejak'])->locale('id')->diffForHumans()
                                            : '—' }}
                                    </td>

                                    <td>
                                        {{-- Urutan pemeriksaannya MENENTUKAN, dan sempat salah.

                                             dikabari_pada tidak berarti "sudah dikabari". Artinya
                                             "kursi terbuka dan orang ini yang dipilih sistem".
                                             Untuk yang punya email, surat memang terkirim; untuk
                                             yang TANPA email, penandanya dipasang lalu tidak ada
                                             apa pun yang dikirim — sengaja, supaya tim menelepon.

                                             Sebelumnya dikabari_pada diperiksa lebih dulu,
                                             sehingga orang yang tidak bisa dijangkau siapa pun
                                             tergambar hijau "Dikabari 2 jam lalu". Layarnya
                                             mengatakan kebalikan dari kenyataan, tepat untuk
                                             orang yang paling membutuhkan admin. --}}
                                        {{-- Keadaan TAWARAN didahulukan.

                                             Kursi yang sedang ditahan untuk orang ini adalah hal
                                             paling penting di barisnya — ia menahan admin lain
                                             menawarkan kursi yang sama. Penanda "sudah dihubungi"
                                             di bawah menjawab pertanyaan yang lain dan boleh
                                             menyusul. --}}
                                        @if (($baris['keadaan_tawaran'] ?? 'belum') === 'ditahan')
                                            <span class="badge bg-primary-subtle text-primary-emphasis d-block mb-1">
                                                <i class="bi bi-bookmark-check"></i>
                                                Kursi {{ $baris['rombongan'] }} ditahan
                                                @if ($baris['ditahan_sampai'] ?? null)
                                                    sampai {{ \Carbon\Carbon::parse($baris['ditahan_sampai'])->locale('id')->translatedFormat('d M, H:i') }}
                                                @endif
                                            </span>
                                        @elseif (($baris['keadaan_tawaran'] ?? 'belum') === 'lewat')
                                            {{-- Tahanannya habis dan orangnya tidak membalas.
                                                 Kursinya boleh ditawarkan ke yang berikutnya —
                                                 dan tanpa keadaan ketiga ini, ia menggantung
                                                 selamanya atas nama orang yang diam. --}}
                                            <span class="badge bg-warning-subtle text-warning-emphasis d-block mb-1">
                                                <i class="bi bi-hourglass-bottom"></i>
                                                Tahanan kursi {{ $baris['rombongan'] }} sudah lewat
                                            </span>
                                        @endif

                                        @if ($baris['dihubungi_pada'] ?? null)
                                            <span class="badge bg-success-subtle text-success-emphasis">
                                                Sudah dihubungi
                                                {{ \Carbon\Carbon::parse($baris['dihubungi_pada'])->locale('id')->diffForHumans() }}
                                                @if ($baris['dihubungi_oleh'] ?? null)
                                                    · {{ $baris['dihubungi_oleh'] }}
                                                @endif
                                            </span>
                                        @elseif ($baris['dikabari_pada'] && blank($baris['email']))
                                            {{-- Inilah yang menuntut perbuatan, dan yang dihitung
                                                 penanda di bilah samping. --}}
                                            <span class="badge bg-danger-subtle text-danger-emphasis">
                                                Kursi terbuka — belum bisa dikabari
                                            </span>
                                        @elseif ($baris['dikabari_pada'])
                                            <span class="badge bg-success-subtle text-success-emphasis">
                                                Dikabari
                                                {{ \Carbon\Carbon::parse($baris['dikabari_pada'])->locale('id')->diffForHumans() }}
                                            </span>
                                        @elseif (blank($baris['email']))
                                            {{-- Tanpa email, tetapi kursinya belum terbuka. Belum
                                                 ada yang bisa dikabarkan, jadi belum ada yang
                                                 perlu dikerjakan — ditandai netral, bukan awas. --}}
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                Menunggu kursi · tanpa email
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                Menunggu kursi
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        {{-- Varian tombolnya dipakai apa adanya dari partial gaya.

                                             Sebelumnya keduanya cuma berkelas .orcha-aksi — yang
                                             latarnya bening dan batasnya bening — sehingga tombol
                                             WhatsApp tergambar sebagai ikon telanjang tanpa kotak,
                                             sementara tombol hapus di sebelahnya berkotak. Dua
                                             tombol berdampingan yang bentuknya berbeda terbaca
                                             seperti salah satunya rusak.

                                             orcha-aksi-wa dan orcha-aksi-hapus sudah ada dan sudah
                                             dipakai halaman Pesan Kontak; bentuk pembungkusnya pun
                                             disalin dari sana supaya jaraknya sama. --}}
                                        <div class="d-flex gap-2 justify-content-end">
                                            {{-- Menahan kursi satu rombongan untuk orang ini.

                                                 Dicatat SEBELUM WhatsApp dibuka, bukan sesudah
                                                 balasannya datang: yang menahan admin kedua
                                                 menawarkan kursi yang sama adalah catatan
                                                 niatnya, dan menunggu balasan justru membuat
                                                 jendela bentroknya selebar waktu menunggu. --}}
                                            @php
                                                $rombonganBisa = $this->rombonganPaket((int) $baris['paket_id']);
                                            @endphp

                                            @if (($baris['keadaan_tawaran'] ?? 'belum') !== 'belum')
                                                <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus"
                                                    wire:click="lepasTahanan({{ $baris['id'] }})"
                                                    title="Lepas tahanannya supaya kursinya boleh ditawarkan ke orang lain">
                                                    <i class="bi bi-bookmark-x"></i>
                                                </button>
                                            @elseif (count($rombonganBisa) === 1)
                                                {{-- Satu tanggal: langsung, tanpa memilih. --}}
                                                <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-paku"
                                                    wire:click="tahanKursi({{ $baris['id'] }}, {{ $rombonganBisa[0]['id'] }})"
                                                    title="Tahan kursi {{ $rombonganBisa[0]['rentang_tanggal'] }} untuk {{ $baris['nama'] }} selama 6 jam">
                                                    <i class="bi bi-bookmark-plus"></i>
                                                </button>
                                            @elseif (count($rombonganBisa) > 1)
                                                <details class="dt-laci">
                                                    <summary class="btn btn-sm orcha-aksi orcha-aksi-paku"
                                                        title="Tahan kursi salah satu rombongan untuk orang ini">
                                                        <i class="bi bi-bookmark-plus"></i>
                                                    </summary>
                                                    <div class="dt-laci-isi">
                                                        <span class="dt-laci-judul">Tahan kursi rombongan</span>
                                                        @foreach ($rombonganBisa as $kb)
                                                            <button type="button"
                                                                wire:click="tahanKursi({{ $baris['id'] }}, {{ $kb['id'] }})">
                                                                <i class="bi bi-calendar-event"></i>
                                                                {{ $kb['rentang_tanggal'] }}
                                                                @if (($kb['sisa_kursi'] ?? null) !== null)
                                                                    <small>sisa {{ $kb['sisa_kursi'] }}</small>
                                                                @endif
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @endif

                                            {{-- Menekan tombol ini SEKALIGUS menandai bahwa
                                                 orangnya sudah dihubungi.

                                                 Tanpa itu, satu-satunya cara menurunkan penanda di
                                                 bilah samping adalah mengeluarkannya dari antrean
                                                 — padahal orang yang menjawab "nanti saya kabari
                                                 lagi" memang belum boleh dikeluarkan. Tombol
                                                 tersendiri untuk menandainya juga tidak dipakai:
                                                 langkah tambahan yang harus diingat adalah
                                                 langkah yang akhirnya terlewat.

                                                 x-on:click, BUKAN wire:click. Livewire menahan
                                                 perilaku bawaan tautan, sehingga WhatsApp-nya
                                                 tidak jadi terbuka; Alpine tidak. Jadi tautannya
                                                 terbuka seperti biasa oleh peramban, dan
                                                 penandanya dipasang di belakangnya. --}}
                                            <a href="{{ $wa }}" target="_blank" rel="noopener"
                                                class="btn btn-sm orcha-aksi orcha-aksi-wa"
                                                x-on:click="$wire.tandaiDihubungi({{ $baris['id'] }})"
                                                title="Hubungi lewat WhatsApp — sekaligus menandainya sudah dihubungi">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>

                                            {{-- Konfirmasinya lewat SweetAlert, bukan wire:confirm.

                                                 Dialog bawaan peramban menampilkan
                                                 "127.0.0.1:8001 says" di atas kalimatnya —
                                                 terbaca seperti peringatan sistem yang bocor,
                                                 bukan bagian dari aplikasi. --}}
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                                data-action="keluarkan" data-arg="{{ $baris['id'] }}"
                                                data-title="Keluarkan {{ addslashes($baris['nama']) }} dari daftar tunggu?"
                                                data-text="Ia tidak akan dikabari lagi saat ada kursi terbuka."
                                                data-confirm="Ya, keluarkan" data-icon="warning"
                                                title="Keluarkan dari antrean">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-3">
                                            <i class="bi bi-hourglass-split"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Belum ada yang menunggu. Daftar ini terisi sendiri saat
                                            ada trip yang kursinya habis.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('livewire.pages.admin.orcha.partials.paginasi')
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
