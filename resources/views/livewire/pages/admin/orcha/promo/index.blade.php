@section('title')
Promo Rombongan || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Promo Rombongan',
            'keterangan' => 'Potongan yang berlaku menurut jumlah peserta dalam satu pendaftaran.',
        ])

        {{-- Keterangan pembuka, bukan hiasan.

             Dua hal ditanyakan siapa pun yang membuka halaman ini pertama kali:
             apakah promonya bertumpuk, dan apakah ia menumpang di atas harga
             early bird. Dijawab di sini supaya tidak ditemukan sebagai kejutan
             saat angkanya sudah telanjur dijanjikan ke pelanggan. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-bagian-kepala mb-0">
                    <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <div class="orcha-bagian-judul">Cara tingkatnya dipilih</div>
                        <div class="orcha-bagian-sub">
                            Yang berlaku <strong>satu tingkat saja</strong> — yang paling tinggi
                            syaratnya. Rombongan 12 orang memakai tingkat 10, bukan tingkat 5
                            ditambah tingkat 10.
                            <br>
                            Potongannya menumpang di atas <strong>harga yang sedang berlaku</strong>,
                            termasuk harga early bird. Paket yang sudah turun jadi Rp 1.430.000
                            dihitung promonya dari angka itu, bukan dari harga normalnya.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="text-muted small mb-0">
                    {{ count($daftar) }} tingkat tersimpan.
                </div>

                <button type="button" wire:click="bukaTambah" class="orcha-btn orcha-btn-utama">
                    <i class="bi bi-plus-lg"></i>
                    Tambah Tingkat
                </button>
            </div>
        </div>

        @if ($tambah || $sunting)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <h6 class="fw-bold mb-3 orcha-judul-ikon">
                        <i class="bi bi-gift text-primary"></i>
                        {{ $sunting ? 'Ubah Tingkat' : 'Tingkat Baru' }}
                    </h6>

                    {{-- Jenis keuntungannya dipilih LEBIH DULU.

                         Dulu kedua kotak angka tampil berdampingan dan admin harus
                         menyimpulkan sendiri bahwa hanya salah satu yang perlu diisi —
                         sebagian mengisi keduanya, sebagian tidak mengisi satu pun lalu
                         ditolak setelah menekan simpan. Dengan memilih dulu, isian yang
                         tidak relevan tidak pernah muncul. --}}
                    <label class="form-label small fw-semibold">Bentuk keuntungannya <span class="text-danger">*</span></label>

                    <div class="orcha-pilih-kartu mb-4" wire:key="jenis-promo">
                        <label class="orcha-kartu-pilihan {{ $jenis === 'persen' ? 'aktif' : '' }}">
                            <input type="radio" value="persen" wire:model.live="jenis">
                            <span class="tanda"><i class="bi bi-check-lg"></i></span>
                            <i class="bi bi-percent rupa"></i>
                            <span>
                                <span class="judul">Potongan persen</span>
                                <span class="ket">Harga per orang turun sekian persen</span>
                            </span>
                        </label>

                        <label class="orcha-kartu-pilihan {{ $jenis === 'gratis' ? 'aktif' : '' }}">
                            <input type="radio" value="gratis" wire:model.live="jenis">
                            <span class="tanda"><i class="bi bi-check-lg"></i></span>
                            <i class="bi bi-person-check rupa"></i>
                            <span>
                                <span class="judul">Gratis orang</span>
                                <span class="ket">Sejumlah orang tidak dibayar</span>
                            </span>
                        </label>
                    </div>

                    @php
                        $min = (int) ($isian['min_peserta'] ?: 0);
                        $nilai = (int) (($jenis === 'gratis' ? $isian['gratis_orang'] : $isian['potongan_persen']) ?: 0);
                        $siap = $min > 0 && $nilai > 0;
                    @endphp

                    {{-- Isian di kiri, pratinjau di kanan.

                         Sebelumnya isiannya berderet tiga ke samping dengan sakelar
                         mengambang di ujungnya, dan pratinjau baru muncul setelah kedua
                         angkanya terisi — sehingga separuh kartu menganga kosong tepat
                         saat admin membukanya pertama kali. --}}
                    <div class="row g-4">
                        <div class="col-12 col-lg-7">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Minimal peserta <span class="text-danger">*</span></label>
                                    <input type="number" min="2" max="100" wire:model.live.debounce.400ms="isian.min_peserta"
                                        class="form-control @error('isian.min_peserta') is-invalid @enderror"
                                        placeholder="10">
                                    <div class="form-text">Rombongan sebanyak ini atau lebih.</div>
                                    @error('isian.min_peserta')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-6">
                                    @if ($jenis === 'gratis')
                                        <label class="form-label small fw-semibold">Berapa orang gratis <span class="text-danger">*</span></label>
                                        <input type="number" min="1" max="20" wire:model.live.debounce.400ms="isian.gratis_orang"
                                            class="form-control @error('isian.gratis_orang') is-invalid @enderror"
                                            placeholder="1">
                                        <div class="form-text">Sisanya tetap dibayar penuh.</div>
                                        @error('isian.gratis_orang')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    @else
                                        <label class="form-label small fw-semibold">Potongan (%) <span class="text-danger">*</span></label>
                                        <input type="number" min="1" max="100" wire:model.live.debounce.400ms="isian.potongan_persen"
                                            class="form-control @error('isian.potongan_persen') is-invalid @enderror"
                                            placeholder="5">
                                        {{-- Cakupannya disebut di sini, bukan cuma di pratinjau.

                                             Admin yang mengetik "10" perlu tahu itu 10% dari SATU
                                             kursi, bukan dari seluruh tagihan rombongan — kalau
                                             tidak, angka yang dikiranya kecil ternyata besar, atau
                                             sebaliknya. --}}
                                        <div class="form-text">Dari harga satu kursi, untuk pemesan saja.</div>
                                        @error('isian.potongan_persen')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            </div>

                            {{-- Sakelar memakai kartu, bukan togel telanjang.

                                 Togel tanpa keterangan memaksa admin menebak apa yang
                                 dimatikannya. Pola orcha-sakelar-kartu ini sudah dipakai
                                 formulir armada, dan kalimatnya berubah mengikuti
                                 keadaannya — jadi yang terbaca akibatnya, bukan namanya. --}}
                            <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }} mt-3">
                                <span class="rupa">
                                    <i class="bi {{ $aktif ? 'bi-broadcast' : 'bi-pause-circle' }}"></i>
                                </span>
                                <span class="isi">
                                    <span class="judul">{{ $aktif ? 'Sedang berjalan' : 'Sedang dimatikan' }}</span>
                                    <span class="ket">
                                        {{ $aktif
                                            ? 'Berlaku pada paket yang ditandai ikut promo rombongan.'
                                            : 'Angkanya tersimpan, tetapi tidak dipakai menghitung harga.' }}
                                    </span>
                                </span>
                                <span class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                                </span>
                            </label>
                        </div>

                        {{-- Pratinjau SELALU tergambar, termasuk saat angkanya belum diisi.

                             Tulisannya tidak lagi diketik admin — dulu ia bisa berbeda dari
                             potongan yang benar-benar berlaku: mengubah 5% jadi 7% lalu lupa
                             menyunting kalimat "hemat 5%" di sebelahnya. Ditampilkan supaya
                             admin tetap melihat apa yang akan dibaca pelanggannya. --}}
                        <div class="col-12 col-lg-5">
                            <div class="orcha-pratinjau-promo h-100 {{ $siap ? '' : 'kosong' }}">
                                <div class="orcha-label-kecil mb-2">
                                    <i class="bi bi-eye"></i> Yang dibaca pelanggan
                                </div>

                                @if ($siap)
                                    <div class="orcha-pratinjau-judul">
                                        @if ($jenis === 'gratis')
                                            Ajak {{ $min }} orang — gratis {{ $nilai }} orang
                                        @else
                                            Ajak {{ $min }} orang — potongan {{ $nilai }}% untuk pemesan
                                        @endif
                                    </div>

                                    <div class="orcha-pratinjau-ket">
                                        Yang belum mencapainya membaca:
                                        <em>
                                            @if ($jenis === 'gratis')
                                                "Ajak {{ $min }} orang, {{ $nilai }} orang gratis."
                                            @else
                                                "Ajak {{ $min }} orang, Anda dapat potongan {{ $nilai }}%."
                                            @endif
                                        </em>
                                    </div>
                                @else
                                    <div class="orcha-pratinjau-ket">
                                        Isi kedua angkanya — kalimat promonya dirakit sendiri,
                                        jadi tidak perlu diketik dan tidak bisa berbeda dari
                                        potongan yang berlaku.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end pt-3 mt-4 border-top">
                        <button type="button" wire:click="tutup" class="orcha-btn orcha-btn-lembut">
                            Batal
                        </button>

                        <button type="button" wire:click="simpan" wire:loading.attr="disabled"
                            wire:target="simpan" class="orcha-btn orcha-btn-utama">
                            <span wire:loading.remove wire:target="simpan">
                                <i class="bi bi-check2-circle"></i>
                                Simpan
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

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">MINIMAL</th>
                                <th>KEUNTUNGAN</th>
                                <th>TULISAN</th>
                                <th>STATUS</th>
                                <th class="text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="promo-{{ $baris['id'] }}">
                                    <td class="ps-4 fw-bold">{{ $baris['min_peserta'] }} orang</td>

                                    <td>
                                        @if (($baris['gratis_orang'] ?? 0) > 0)
                                            <span class="badge bg-success-subtle text-success-emphasis">
                                                Gratis {{ $baris['gratis_orang'] }} orang
                                            </span>
                                        @endif
                                        @if (($baris['potongan_persen'] ?? 0) > 0)
                                            <span class="badge bg-primary-subtle text-primary-emphasis">
                                                Potongan {{ $baris['potongan_persen'] }}%
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-muted small">{{ $baris['label'] }}</td>

                                    <td>
                                        @if ($baris['aktif'])
                                            <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">Mati</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">
                                        <button type="button" class="orcha-aksi"
                                            wire:click="bukaSunting({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                            title="Ubah tingkat ini">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button type="button" class="orcha-aksi orcha-bahaya"
                                            wire:click="hapus({{ $baris['id'] }})"
                                            wire:confirm="Hapus tingkat {{ $baris['min_peserta'] }} orang? Rombongan sebesar itu akan turun ke tingkat di bawahnya."
                                            title="Hapus tingkat ini">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-3">
                                            <i class="bi bi-gift"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Belum ada tingkat promo. Selama kosong, Orcha memakai
                                            tingkat bawaannya.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
