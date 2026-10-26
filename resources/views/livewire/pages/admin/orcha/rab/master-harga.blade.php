@section('title')
Master Harga || lemon
@stop

@php
    $formTerbuka = $tambah || $sunting;
    $perluKapasitas = in_array($isian['satuan'] ?? '', $berkapasitas, true);
    $jumlahData = (int) ($meta['total'] ?? count($daftar));
    $bisaBersih = $this->adaSaringan();
@endphp

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    <style>
        /* Kartu di luar rak 12 kolom: jaraknya diatur sendiri, karena yang
           biasanya mengaturnya adalah gap raknya. */
        /* Baris yang nama destinasinya tidak ada di katalog: tiketnya tidak
           akan pernah ikut tertarik ke RAB, dan sebelumnya tidak ada apa pun
           di layar yang mengabarkannya. */
        .mh-lepas {
            display: inline-flex; align-items: center; gap: 5px; margin-top: 3px;
            font-size: .76rem; font-weight: 600; color: #b45309;
        }
        .mh-lepas-ket {
            padding: 1px 7px 2px; border-radius: 7px;
            background: #fffbeb; border: 1px solid #fde68a; font-size: .68rem; font-weight: 700;
        }
        .mh-asing {
            display: flex; align-items: flex-start; gap: 6px; margin-top: 5px;
            font-size: .74rem; line-height: 1.45; color: #92400e;
        }

        .mh-kartu { margin-bottom: clamp(12px, 1.2vw, 16px); }
        .mh-kartu:last-of-type { margin-bottom: 0; }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu
           bertumpuk, aturan yang sama dengan daftar lemon lain. Tanpa ini
           tabelnya menggulir mendatar di dalam pembungkusnya, dan kolom
           terakhir — tombol ubah & hapus — tidak pernah terlihat tanpa
           digeser lebih dulu. */
        @media (max-width: 767.98px) {
            .mh-tabel { min-width: 0; }
            .mh-tabel thead { display: none; }
            .mh-tabel tbody tr { display: block; padding: 13px 4px; border-bottom: 1px solid #f2f5f9; }
            .mh-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0; text-align: left !important;
            }
            .mh-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            .mh-tabel tbody td > * { min-width: 0; }
            .mh-tabel tbody td > div { text-align: right; }
        }
    </style>

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Master Harga</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Harga tiket, armada, hotel, dan pemandu — ditulis sekali, dipakai seluruh RAB.</span>
                    <span class="d-block">Yang diisi di sini tidak perlu diketik ulang pada tiap penawaran.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.rab') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                    <i class="bi bi-calculator"></i><span>RAB &amp; Itinerary</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        <div class="dsb-kartu mh-kartu">
            <div class="dsb-kartu-isi">
                <div class="orcha-bagian-kepala mb-0">
                    <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <div class="orcha-bagian-judul">Cara harganya dipakai</div>
                        <div class="orcha-bagian-sub">
                            Harga <strong>dibekukan</strong> saat masuk ke sebuah RAB. Mengubah harga di sini
                            hanya berlaku untuk RAB berikutnya; RAB lama diberi tanda supaya bisa diperbarui bila perlu.
                            <br>
                            Isi <strong>destinasi</strong> pada tiket supaya ikut tertarik saat destinasinya dipilih di itinerary.
                            Tandai <strong>wajib</strong> untuk biaya yang selalu ada (makan, parkir, asuransi) — otomatis masuk ke setiap RAB baru di provinsinya.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Saringan --}}
        <div class="dsb-kartu mh-kartu">
            <div class="dsb-kartu-isi">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-3">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari nama, destinasi, atau daerah...'])
                    </div>
                    <div class="col-6 col-lg-3">
                        <select wire:model.live="filterKategori" class="dsb-isian">
                            <option value="">Semua kategori</option>
                            @foreach ($kategori as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <select wire:model.live="filterProvinsi" class="dsb-isian">
                            <option value="">Semua provinsi</option>
                            <option value="-">Berlaku umum (semua provinsi)</option>
                            @foreach ($provinsi as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-3 d-grid">
                        <button type="button" wire:click="bukaTambah" class="dsb-tombol is-lembut justify-content-center" style="--ikon: #4f46e5">
                            <i class="bi bi-plus-lg"></i>
                            <span>Tambah Harga</span>
                        </button>
                    </div>
                </div>
                @if ($bisaBersih)
                    <div class="mt-2">
                        <button type="button" wire:click="bersihkanSaringan" class="btn btn-link btn-sm p-0 text-decoration-none">
                            <i class="bi bi-x-circle"></i> Bersihkan saringan
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if ($formTerbuka)
            <div class="dsb-kartu mh-kartu">
                <div class="dsb-kartu-isi">
                    <h6 class="fw-bold mb-3 orcha-judul-ikon">
                        <i class="bi bi-tags text-primary"></i>
                        {{ $sunting ? 'Ubah Harga' : 'Harga Baru' }}
                    </h6>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="dsb-label">Kategori <span class="text-danger">*</span></label>
                            <select wire:model="isian.kategori" class="dsb-isian">
                                @foreach ($kategori as $kunci => $label)
                                    <option value="{{ $kunci }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="dsb-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" wire:model="isian.nama" maxlength="150"
                                class="dsb-isian @error('isian.nama') is-galat @enderror"
                                placeholder="Tiket Candi Prambanan (domestik) / Sewa Hiace + driver + BBM">
                            @error('isian.nama')<div class="dsb-galat">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="dsb-label">Provinsi</label>
                            <select wire:model.live="isian.provinsi" class="dsb-isian">
                                <option value="">Berlaku umum (semua provinsi)</option>
                                @foreach ($provinsi as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="dsb-label">Destinasi <span class="text-muted fw-normal">(untuk tiket)</span></label>
                            {{-- DIPILIH, bukan diketik.

                                 Harga tiket dijodohkan ke itinerary lewat nama ini
                                 PERSIS. Sebagai ketikan bebas (dulu: input + datalist,
                                 yang cuma menyarankan), satu huruf berbeda —
                                 "prambanan" alih-alih "Candi Prambanan" — membuat
                                 tiketnya tidak pernah ikut tertarik, dan RAB-nya
                                 kekurangan modal tanpa satu pun tanda. --}}
                            @php
                                $destinasiKini = trim((string) ($isian['destinasi'] ?? ''));
                                $asing = $destinasiKini !== '' && ! in_array($destinasiKini, $namaKatalog, true);
                            @endphp
                            <select wire:model.blur="isian.destinasi" class="dsb-isian @error('isian.destinasi') is-galat @enderror">
                                <option value="">Tidak terikat destinasi</option>
                                @if ($asing)
                                    {{-- Nilai lama yang tidak ada di katalog TETAP
                                         ditawarkan, supaya menyunting baris lain tidak
                                         diam-diam menghapusnya. --}}
                                    <option value="{{ $destinasiKini }}">{{ $destinasiKini }} — tidak ada di katalog</option>
                                @endif
                                @foreach ($destinasi as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                            @if ($asing)
                                <div class="mh-asing">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    Nama ini tidak ada di katalog destinasi, jadi tiketnya tidak akan ikut tertarik saat destinasinya dipilih di itinerary.
                                </div>
                            @else
                                <div class="dsb-kartu-sub">Kosongkan untuk biaya yang bukan tiket destinasi.</div>
                            @endif
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="dsb-label">Daerah</label>
                            <input type="text" wire:model="isian.daerah" maxlength="80" class="dsb-isian" placeholder="Sleman">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="dsb-label">Dihitung per <span class="text-danger">*</span></label>
                            <select wire:model.live="isian.satuan" class="dsb-isian">
                                @foreach ($satuan as $kunci => $label)
                                    <option value="{{ $kunci }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="dsb-label">Harga <span class="text-danger">*</span></label>
                            <div class="orcha-rupiah">
                                <input type="text" inputmode="numeric" wire:model.blur="isian.harga"
                                    class="orcha-uang dsb-isian @error('isian.harga') is-galat @enderror" placeholder="50.000">
                            </div>
                            @error('isian.harga')<div class="dsb-galat">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="dsb-label">
                                Kapasitas
                                @if ($perluKapasitas)<span class="text-danger">*</span>@endif
                            </label>
                            <input type="number" min="1" wire:model="isian.kapasitas"
                                class="dsb-isian @error('isian.kapasitas') is-galat @enderror"
                                placeholder="{{ $perluKapasitas ? '14 kursi / 2 orang per kamar' : 'Tidak perlu' }}">
                            @if ($perluKapasitas)
                                <div class="dsb-kartu-sub">Jumlah unit dihitung sendiri: 17 peserta ÷ 14 kursi = 2 unit.</div>
                            @endif
                            @error('isian.kapasitas')<div class="dsb-galat">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="dsb-label">Catatan internal</label>
                            <input type="text" wire:model="isian.catatan" maxlength="500" class="dsb-isian"
                                placeholder="Vendor, nomor kontak, harga berlaku sampai…">
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="orcha-sakelar-kartu {{ $otomatis ? 'nyala' : '' }}">
                                <span class="rupa"><i class="bi {{ $otomatis ? 'bi-magic' : 'bi-hand-index' }}"></i></span>
                                <span class="isi">
                                    <span class="judul">{{ $otomatis ? 'Wajib — masuk sendiri' : 'Dipilih manual' }}</span>
                                    <span class="ket">
                                        {{ $otomatis
                                            ? 'Otomatis masuk ke setiap RAB baru di provinsinya.'
                                            : 'Ditambahkan ke RAB bila admin memilihnya.' }}
                                    </span>
                                </span>
                                <span class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" wire:model.live="otomatis">
                                </span>
                            </label>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }}">
                                <span class="rupa"><i class="bi {{ $aktif ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i></span>
                                <span class="isi">
                                    <span class="judul">{{ $aktif ? 'Aktif' : 'Dimatikan' }}</span>
                                    <span class="ket">
                                        {{ $aktif
                                            ? 'Muncul sebagai pilihan saat menyusun RAB.'
                                            : 'Tersimpan, tetapi tidak ditawarkan di RAB baru.' }}
                                    </span>
                                </span>
                                <span class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end pt-3 mt-4 border-top">
                        <button type="button" wire:click="tutup" class="dsb-tombol is-lembut" style="--ikon: #64748b">Batal</button>
                        <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                            class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                            <span wire:loading.remove wire:target="simpan"><i class="bi bi-check2-circle"></i> Simpan</span>
                            <span wire:loading wire:target="simpan">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="dsb-kartu mh-kartu">
            <div class="dsb-kartu-isi">
                <div class="text-muted small mb-3">{{ $jumlahData }} harga tersimpan.</div>
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel mh-tabel mb-0">
                        <thead>
                            <tr>
                                <th>NAMA</th>
                                <th>KATEGORI</th>
                                <th>WILAYAH</th>
                                <th>DIHITUNG PER</th>
                                <th class="text-end">HARGA</th>
                                <th>STATUS</th>
                                <th class="text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="mh-{{ $baris['id'] }}">
                                    <td data-judul="Nama">
                                        <div class="fw-bold">{{ $baris['nama'] }}</div>
                                        @if ($baris['destinasi'])
                                            @php $lepas = ! in_array($baris['destinasi'], $namaKatalog, true); @endphp
                                            <div class="{{ $lepas ? 'mh-lepas' : 'text-muted small' }}"
                                                @if ($lepas) title="Nama ini tidak ada di katalog destinasi, jadi tiketnya tidak akan ikut tertarik ke RAB" @endif>
                                                <i class="bi {{ $lepas ? 'bi-exclamation-triangle-fill' : 'bi-geo-alt' }}"></i>
                                                {{ $baris['destinasi'] }}
                                                @if ($lepas)<span class="mh-lepas-ket">tidak cocok katalog</span>@endif
                                            </div>
                                        @endif
                                        @if ($baris['catatan'])
                                            <div class="text-muted small fst-italic">{{ $baris['catatan'] }}</div>
                                        @endif
                                    </td>
                                    <td data-judul="Kategori"><span class="badge bg-primary-subtle text-primary-emphasis">{{ $baris['kategori_label'] }}</span></td>
                                    <td data-judul="Wilayah" class="small">
                                        {{ $baris['provinsi'] ?: 'Semua provinsi' }}
                                        @if ($baris['daerah'])<div class="text-muted">{{ $baris['daerah'] }}</div>@endif
                                    </td>
                                    <td data-judul="Dihitung per" class="small">
                                        {{ $baris['satuan_label'] }}
                                        @if ($baris['kapasitas'])<div class="text-muted">kapasitas {{ $baris['kapasitas'] }}</div>@endif
                                    </td>
                                    <td data-judul="Harga" class="text-end fw-bold text-nowrap">{{ $baris['harga_teks'] }}</td>
                                    <td data-judul="Status">
                                        @if ($baris['aktif'])
                                            <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">Mati</span>
                                        @endif
                                        @if ($baris['otomatis'])
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Wajib</span>
                                        @endif
                                    </td>
                                    <td data-judul="Aksi" class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                                wire:click="bukaSunting({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                                title="Ubah harga ini">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                                data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                data-title="Hapus {{ $baris['nama'] }}?"
                                                data-text="RAB yang sudah memakainya tidak berubah."
                                                data-confirm="Ya, hapus" data-icon="warning"
                                                title="Hapus harga ini">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-3"><i class="bi bi-tags"></i></div>
                                        <p class="text-muted mb-0">
                                            {{ $bisaBersih ? 'Tidak ada harga yang cocok dengan saringan.' : 'Belum ada harga. Mulai dari tiket destinasi yang paling sering diminta.' }}
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
