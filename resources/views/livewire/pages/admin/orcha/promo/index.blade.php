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

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Minimal peserta <span class="text-danger">*</span></label>
                            <input type="number" min="2" max="100" wire:model="isian.min_peserta"
                                class="form-control @error('isian.min_peserta') is-invalid @enderror"
                                placeholder="10">
                            <div class="form-text">Rombongan sebanyak ini atau lebih.</div>
                            @error('isian.min_peserta')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            @if ($jenis === 'gratis')
                                <label class="form-label small fw-semibold">Berapa orang gratis <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="20" wire:model="isian.gratis_orang"
                                    class="form-control @error('isian.gratis_orang') is-invalid @enderror"
                                    placeholder="1">
                                <div class="form-text">Sisanya tetap dibayar penuh.</div>
                                @error('isian.gratis_orang')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            @else
                                <label class="form-label small fw-semibold">Potongan (%) <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="100" wire:model="isian.potongan_persen"
                                    class="form-control @error('isian.potongan_persen') is-invalid @enderror"
                                    placeholder="5">
                                <div class="form-text">Dari harga yang sedang berlaku.</div>
                                @error('isian.potongan_persen')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="col-12 col-md-4 d-flex align-items-center">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="promo-aktif"
                                    wire:model="aktif">
                                <label class="form-check-label small" for="promo-aktif">Aktif</label>
                            </div>
                        </div>
                    </div>

                    {{-- Pratinjau kalimatnya, dirakit dari angka yang sedang diketik.

                         Tulisannya tidak lagi diketik admin — dulu ia bisa berbeda dari
                         potongan yang benar-benar berlaku: mengubah 5% jadi 7% lalu lupa
                         menyunting kalimat "hemat 5%" di sebelahnya. Yang dibaca
                         pelanggan angka yang salah, yang ditagih angka yang benar.

                         Ditampilkan sebagai pratinjau, bukan disembunyikan, supaya admin
                         tetap melihat apa yang akan dibaca pelanggannya. --}}
                    @php
                        $min = (int) ($isian['min_peserta'] ?: 0);
                        $nilai = (int) (($jenis === 'gratis' ? $isian['gratis_orang'] : $isian['potongan_persen']) ?: 0);
                    @endphp

                    @if ($min > 0 && $nilai > 0)
                        <div class="alert alert-light border mt-4 mb-0">
                            <div class="orcha-label-kecil mb-2">
                                <i class="bi bi-eye"></i> Yang dibaca pelanggan
                            </div>

                            <div class="fw-bold text-dark">
                                @if ($jenis === 'gratis')
                                    Ajak {{ $min }} orang — gratis {{ $nilai }} orang
                                @else
                                    Ajak {{ $min }} orang — hemat {{ $nilai }}%
                                @endif
                            </div>

                            <div class="text-muted small mt-1">
                                Yang belum mencapainya membaca:
                                @if ($jenis === 'gratis')
                                    "Ajak {{ $min }} orang, {{ $nilai }} orang gratis."
                                @else
                                    "Ajak {{ $min }} orang, hemat {{ $nilai }}% untuk seluruh rombongan."
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2 mt-4">
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

                        <button type="button" wire:click="tutup" class="orcha-btn orcha-btn-lembut">
                            Batal
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
