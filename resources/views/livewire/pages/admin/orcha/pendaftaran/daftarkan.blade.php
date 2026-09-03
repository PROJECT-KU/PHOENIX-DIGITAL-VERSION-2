@section('title')
Daftarkan Rombongan || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <style>
        .orcha-serah {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 1rem;
        }

        /* Kode pemesanannya dibuat besar dan berjarak huruf: ia dibacakan lewat
           telepon dan disalin dengan mata, bukan diklik. */
        .orcha-kode-serah {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: .12em;
            color: #0f172a;
        }

        .orcha-pesan-siap {
            white-space: pre-wrap;
            font-size: .82rem;
            line-height: 1.6;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            padding: .85rem 1rem;
            color: #334155;
        }

        .orcha-baris-peserta {
            display: grid;
            grid-template-columns: 2.2rem 1fr 1fr 2.5rem;
            gap: .5rem;
            align-items: center;
        }

        @media (max-width: 767.98px) {
            .orcha-baris-peserta {
                grid-template-columns: 2.2rem 1fr 2.5rem;
            }

            .orcha-baris-peserta .kolom-jemput {
                grid-column: 2 / 4;
            }
        }
    </style>

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Daftarkan Rombongan',
            'keterangan' => 'Private trip dan study tour yang disepakati lewat WhatsApp.',
        ])

        @if ($hasil)
            {{-- Layar berpindah ke SERAH TERIMA begitu tersimpan.

                 Formulir yang tetap terbuka dengan pesan hijau kecil di atasnya
                 membuat admin ragu apakah tersimpan, lalu menekan Simpan lagi —
                 dan rombongan yang sama masuk dua kali dengan dua kode berbeda.

                 Yang tergambar di sini cuma satu hal, karena cuma satu hal yang
                 perlu dikerjakan berikutnya: mengirimkan kodenya. --}}
            @php
                $kode = $hasil['kode'] ?? '';
                $tautan = $hasil['tautan_kesehatan'] ?? '';
                $panggil = trim(explode(' ', trim($hasil['nama'] ?? ''))[0] ?? '');

                $pesan = "Halo Kak {$panggil}, pendaftaran rombongan untuk "
                    . ($hasil['paket']['nama'] ?? 'trip') . " sudah kami catat.\n\n"
                    . "Kode pemesanan: {$kode}\n\n"
                    . "Tiap peserta mohon mengisi riwayat kesehatan lewat tautan ini:\n"
                    . $tautan . "\n\n"
                    . 'Datanya kami butuhkan sebelum berangkat — golongan darah, alergi, '
                    . 'dan kontak darurat. Kalau ada yang kesulitan, kabari kami.';

                $tautanWa = 'https://api.whatsapp.com/send?phone='
                    . preg_replace('/^0/', '62', preg_replace('/\D/', '', $hasil['whatsapp'] ?? ''))
                    . '&text=' . rawurlencode($pesan);
            @endphp

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-check2-circle"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Rombongan terdaftar</div>
                            <div class="orcha-bagian-sub">
                                {{ $hasil['nama'] ?? '' }} · {{ $hasil['jumlah_peserta'] ?? 0 }} peserta ·
                                {{ $hasil['paket']['nama'] ?? '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="orcha-serah p-3 p-lg-4 mb-3">
                        <div class="text-muted small mb-1">Kode pemesanan</div>
                        <div class="orcha-kode-serah mb-3">{{ $kode }}</div>

                        <div class="text-muted small mb-1">Tautan riwayat kesehatan</div>
                        <div class="small text-break">
                            <a href="{{ $tautan }}" target="_blank" rel="noopener">{{ $tautan }}</a>
                        </div>
                    </div>

                    {{-- Tautan yang menunjuk mesin lokal ditahan sebelum terkirim.

                         route() merakit alamatnya dari APP_URL di server Orcha.
                         Bila kunci itu masih berisi localhost — hal yang lolos
                         diam-diam karena seluruh sisi lain aplikasi tetap jalan —
                         tautan ini terkirim ke empat puluh siswa dan tidak bisa
                         dibuka siapa pun kecuali orang yang duduk di server itu.

                         Kegagalannya baru ketahuan lewat keluhan, dan yang
                         menanggung malunya panitia yang menyebarkannya. --}}
                    @if (\Illuminate\Support\Str::contains($tautan, ['localhost', '127.0.0.1', '://0.0.0.0']))
                        <div class="alert alert-danger border-0 rounded-4 mb-3">
                            <i class="bi bi-exclamation-octagon"></i>
                            <strong>Jangan kirim tautan ini.</strong>
                            Alamatnya menunjuk mesin lokal, bukan alamat publik Orcha —
                            tidak akan bisa dibuka siapa pun. Betulkan <code>APP_URL</code>
                            di server Orcha lebih dulu, lalu buka ulang halaman ini.
                        </div>
                    @endif

                    {{-- Peringatan yang menentukan, dan yang paling mudah terlewat.

                         Formulir riwayat kesehatan menolak kode yang pesanannya
                         belum membayar — penjagaan yang memang diminta, supaya
                         kode yang bocor tidak jadi jalan menumpang. Akibatnya
                         tautan ini belum berguna sampai statusnya dimajukan, dan
                         yang menemukan penolakannya bukan admin melainkan
                         panitia yang sudah menyebarkannya ke empat puluh siswa. --}}
                    <div class="alert alert-warning border-0 rounded-4 mb-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Tautannya belum bisa dipakai sebelum pembayaran tercatat.</strong>
                        Formulir riwayat kesehatan menolak kode yang statusnya masih
                        <em>Baru</em>. Setelah uang mukanya masuk, ubah statusnya jadi
                        <em>DP Masuk</em> di halaman Pendaftaran — sesudah itu tautan ini jalan.
                    </div>

                    <div class="text-muted small fw-semibold mb-2">Pesan yang akan dikirim</div>
                    <div class="orcha-pesan-siap mb-3">{{ $pesan }}</div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" wire:click="lagi" class="orcha-btn orcha-btn-lembut">
                            <i class="bi bi-plus-lg"></i>
                            Daftarkan rombongan lain
                        </button>

                        <a href="{{ route('admin.orcha.pendaftaran') }}" wire:navigate
                            class="orcha-btn orcha-btn-lembut">
                            <i class="bi bi-list-ul"></i>
                            Lihat di daftar
                        </a>

                        <a href="{{ $tautanWa }}" target="_blank" rel="noopener"
                            class="orcha-btn orcha-btn-utama">
                            <i class="bi bi-whatsapp"></i>
                            Kirim lewat WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala mb-0">
                        <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Untuk rombongan yang sudah disepakati</div>
                            <div class="orcha-bagian-sub">
                                Private trip dan study tour tidak lewat formulir publik — harganya
                                dirundingkan dan pesertanya berubah sampai menit terakhir. Layar ini
                                yang memasukkannya ke sistem, supaya rombongannya punya kode
                                pemesanan, bisa mengisi riwayat kesehatan, masuk manifes tour
                                leader, dan terhitung di laporan keuntungan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor">1</div>
                        <div>
                            <div class="orcha-bagian-judul">Paket dan pemesan</div>
                            <div class="orcha-bagian-sub">Siapa yang menghubungi, dan untuk trip yang mana.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Paket <span class="text-danger">*</span></label>
                            <select wire:model.live="paketId"
                                class="form-select @error('paketId') is-invalid @enderror">
                                <option value="">Pilih paket...</option>

                                {{-- Dikelompokkan menurut kategori, dan private trip
                                     serta study tour didahulukan: merekalah yang
                                     mengisi layar ini. Open trip tetap ada — sebagian
                                     pemesanannya pun datang lewat telepon. --}}
                                @php
                                    $labelKategori = [
                                        'private_trip' => 'Private Trip',
                                        'study_tour' => 'Study Tour',
                                        'open_trip' => 'Open Trip',
                                    ];
                                    $terkelompok = collect($pilihanPaket)->groupBy('kategori');
                                @endphp

                                @foreach ($labelKategori as $kunci => $label)
                                    @if ($terkelompok->has($kunci))
                                        <optgroup label="{{ $label }}">
                                            @foreach ($terkelompok[$kunci] as $paket)
                                                <option value="{{ $paket['id'] }}"
                                                    @selected((string) $paketId === (string) $paket['id'])>
                                                    {{ $paket['nama'] }}@if ($paket['tanggal_berangkat'])
                                                        · {{ \Carbon\Carbon::parse($paket['tanggal_berangkat'])->locale('id')->translatedFormat('d M Y') }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            @error('paketId')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Nama pemesan <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nama" maxlength="120" value="{{ $nama }}"
                                class="form-control @error('nama') is-invalid @enderror"
                                placeholder="Panitia / guru pendamping / pemesan">
                            @error('nama')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Nomor WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" wire:model="whatsapp" maxlength="32" value="{{ $whatsapp }}"
                                class="form-control @error('whatsapp') is-invalid @enderror"
                                placeholder="0812-3456-7890">
                            <div class="form-text">Kode pemesanan dan tautannya dikirim ke nomor ini.</div>
                            @error('whatsapp')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Email <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="email" wire:model="email" maxlength="150" value="{{ $email }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="panitia@sekolah.sch.id">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Titik jemput utama <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="text" wire:model="titikJemput" maxlength="191" value="{{ $titikJemput }}"
                                class="form-control" placeholder="Halaman sekolah">
                            <div class="form-text">Dipakai sebagai titik bawaan tiap peserta.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor">2</div>
                        <div>
                            <div class="orcha-bagian-judul">Harga rundingan</div>
                            <div class="orcha-bagian-sub">
                                Per orang, bukan per rombongan — seluruh sistem menghitung tagihan
                                sebagai satuan dikali peserta.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Harga per orang</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" min="0" wire:model="hargaJual" value="{{ $hargaJual }}"
                                    class="form-control @error('hargaJual') is-invalid @enderror"
                                    placeholder="750000">
                            </div>
                            {{-- Paket private trip dan study tour sering belum berharga
                                 di sistem karena memang dihitung per rombongan. Tanpa
                                 jalan memasukkannya, pendaftarannya masuk dengan
                                 tagihan nol dan laporan keuntungan ikut salah. --}}
                            <div class="form-text">Kosongkan bila memakai harga paket.</div>
                            @error('hargaJual')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label small fw-semibold">Modal per orang <span class="text-muted fw-normal">(opsional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" min="0" wire:model="hargaModal" value="{{ $hargaModal }}"
                                    class="form-control @error('hargaModal') is-invalid @enderror"
                                    placeholder="500000">
                            </div>
                            <div class="form-text">Dipakai laporan keuntungan.</div>
                            @error('hargaModal')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor">3</div>
                        <div>
                            <div class="orcha-bagian-judul">Peserta</div>
                            <div class="orcha-bagian-sub">
                                Namanya yang masuk manifes panggil-nama dan yang dicocokkan saat
                                riwayat kesehatan diisi. Boleh menyusul, tetapi jumlahnya harus benar
                                sekarang — angka itulah yang mengalikan harga jadi tagihan.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-4">
                            <label class="form-label small fw-semibold">Jumlah peserta <span class="text-danger">*</span></label>
                            {{-- value= ditulis meski nilainya sudah diikat wire:model.

                                 Markup dari server TIDAK memuat nilai isian yang
                                 diikat wire:model; yang mengisinya skrip Livewire
                                 setelah halaman hidup. Akibatnya angka yang baru
                                 disetel dari sisi server — di sini oleh tempel() —
                                 tergambar kosong, dan admin mengetiknya ulang.

                                 Pola yang sama sudah dipakai @selected pada pemilih
                                 paket di layar Pendaftaran, dengan alasan yang sama. --}}
                            <input type="number" min="1" max="200" wire:model.live="jumlahPeserta"
                                value="{{ $jumlahPeserta }}"
                                class="form-control @error('jumlahPeserta') is-invalid @enderror">
                            @error('jumlahPeserta')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-4">
                            <label class="form-label small fw-semibold">
                                Pendamping gratis <span class="text-muted fw-normal">(opsional)</span>
                            </label>
                            <input type="number" min="0" wire:model.live="pendampingGratis"
                                value="{{ $pendampingGratis }}"
                                class="form-control @error('pendampingGratis') is-invalid @enderror">

                            {{-- Terjemahannya ditampilkan hidup, bukan cuma diterangkan sekali.

                                 Dua angka yang berbeda cuma satu kata — "berangkat" dan
                                 "ditagih" — dan tanpa hitungannya terlihat, admin harus
                                 menyimpulkan sendiri bahwa gurunya tetap dapat kursi. --}}
                            @if ((int) $pendampingGratis > 0 && (int) $jumlahPeserta > (int) $pendampingGratis)
                                <div class="form-text text-success-emphasis fw-semibold">
                                    <i class="bi bi-arrow-return-right"></i>
                                    {{ $jumlahPeserta }} berangkat, {{ (int) $jumlahPeserta - (int) $pendampingGratis }} ditagih
                                </div>
                            @else
                                <div class="form-text">Guru pendamping tetap dapat kursi dan masuk manifes.</div>
                            @endif

                            @error('pendampingGratis')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            {{-- Tempel daftar dari WhatsApp.

                                 Daftar peserta study tour datang sebagai satu blok
                                 teks — satu nama per baris, kadang bernomor.
                                 Mengetiknya ulang untuk empat puluh siswa adalah
                                 pekerjaan yang membuat layar ini tidak dipakai sama
                                 sekali, dan rombongannya kembali dicatat di kertas. --}}
                            <label class="form-label small fw-semibold">
                                Tempel daftar nama <span class="text-muted fw-normal">(satu nama per baris)</span>
                            </label>
                            <textarea rows="3" class="form-control" x-data
                                x-on:change="$wire.tempel($event.target.value); $event.target.value = ''"
                                placeholder="1. Budi Santoso&#10;2. Sari Dewi&#10;3. Rian Pratama"></textarea>
                            <div class="form-text">
                                Nomor urut di depan dibuang sendiri. Jumlah pesertanya ikut menyesuaikan.
                            </div>
                        </div>
                    </div>

                    <div class="orcha-baris-peserta mb-2 text-muted small fw-semibold d-none d-md-grid">
                        <div>#</div>
                        <div>Nama peserta</div>
                        <div>Titik jemput</div>
                        <div></div>
                    </div>

                    @foreach ($peserta as $nomor => $baris)
                        <div class="orcha-baris-peserta mb-2" wire:key="peserta-{{ $nomor }}">
                            <div class="text-muted small">{{ $nomor + 1 }}</div>

                            {{-- value= wajib di sini, bukan hiasan.

                                 Tanpa itu, empat puluh nama yang baru ditempel
                                 tergambar sebagai empat puluh kotak kosong — dan yang
                                 melihatnya menyimpulkan tempelannya gagal, lalu
                                 mengetiknya satu per satu. --}}
                            <input type="text" maxlength="120"
                                wire:model="peserta.{{ $nomor }}.nama"
                                value="{{ $baris['nama'] ?? '' }}"
                                class="form-control" placeholder="Nama lengkap">

                            <input type="text" maxlength="191"
                                wire:model="peserta.{{ $nomor }}.titik_jemput"
                                value="{{ $baris['titik_jemput'] ?? '' }}"
                                class="form-control kolom-jemput" placeholder="Titik jemput">

                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus"
                                wire:click="hapusBaris({{ $nomor }})" title="Hapus baris ini">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    @endforeach

                    <button type="button" wire:click="tambahBaris"
                        class="orcha-btn orcha-btn-lembut orcha-btn-kecil mt-2">
                        <i class="bi bi-plus-lg"></i> Tambah baris
                    </button>

                    <div class="mt-4">
                        <label class="form-label small fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                        {{-- Isi textarea ditulis sebagai isi elemen, bukan atribut
                             value — tetapi alasannya sama dengan isian lain:
                             markup dari server tidak memuat nilai yang diikat
                             wire:model. --}}
                        <textarea rows="2" wire:model="catatan" maxlength="2000"
                            class="form-control" placeholder="Kesepakatan harga, permintaan khusus, nomor kontak cadangan...">{{ $catatan }}</textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end pt-3 mt-4 border-top">
                        <a href="{{ route('admin.orcha.pendaftaran') }}" wire:navigate
                            class="orcha-btn orcha-btn-lembut">
                            Batal
                        </a>

                        <button type="button" wire:click="simpan" wire:loading.attr="disabled"
                            wire:target="simpan" class="orcha-btn orcha-btn-utama">
                            <span wire:loading.remove wire:target="simpan">
                                <i class="bi bi-check2-circle"></i>
                                Daftarkan rombongan
                            </span>
                            <span wire:loading wire:target="simpan">
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>Menyimpan…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
