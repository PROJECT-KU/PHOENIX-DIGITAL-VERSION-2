@section('title')
Detail Pelamar Kerja || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*): kepala hero,
         kisi 12 kolom, kartu putih datar berkepala ikon. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.pelamar-kerja.partials.berkas-gaya')

    {{-- Gaya khusus layar ini, ditulis INLINE: public/build di-gitignore. --}}
    <style>
    .pld-lencana-kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-top: 7px; }
    .pld-nomor { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }

    /* Keterangan pelamar: dua kolom baris-berbaris, bukan tabel.
       Baris yang isinya kosong tetap tergambar supaya tiap keterangan selalu
       di tempat yang sama — mata mencari "telepon" di posisi, bukan dengan
       membaca ulang seluruh daftarnya. */
    .pld-medan-rak { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 0 clamp(16px, 2.4vw, 32px); }
    .pld-medan {
        display: flex; align-items: baseline; justify-content: space-between; gap: 12px;
        padding: 9px 0; border-bottom: 1px solid #f5f7fa; min-width: 0;
    }
    .pld-medan-label { color: var(--dsb-redup); font-size: .8rem; white-space: nowrap; }
    .pld-medan-label i.bi { margin-right: 6px; }
    .pld-medan-nilai { font-weight: 700; font-size: .85rem; color: var(--dsb-tinta); text-align: right; overflow-wrap: anywhere; }
    .pld-medan-nilai.is-kosong { font-weight: 500; color: #cbd5e1; }

    /* Tahap seleksi: deret tombol pilih, bukan kotak pilih.
       Semua tahapnya terlihat sekaligus — yang mengubah tahap sedang
       memutuskan di antara lima, dan kotak pilih menyembunyikan empat. */
    .pld-tahap-rak { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .pld-tahap {
        --c: #64748b;
        display: inline-flex; align-items: center; gap: 7px; cursor: pointer;
        padding: 8px 14px; border-radius: 999px; font-size: .82rem; font-weight: 700;
        background: #fff; color: var(--c); border: 1px solid color-mix(in srgb, var(--c) 30%, #fff);
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    .pld-tahap:hover { background: color-mix(in srgb, var(--c) 8%, #fff); }
    .pld-tahap.is-aktif { background: var(--c); color: #fff; border-color: transparent; }

    .pld-bantu { color: var(--dsb-redup); font-size: .8rem; line-height: 1.6; margin: 0 0 14px; }
    .pld-bantu:last-child { margin-bottom: 0; }

    .pld-cek { display: flex; align-items: flex-start; gap: 9px; margin: 12px 0 0; font-size: .83rem; cursor: pointer; }
    .pld-cek input { margin-top: 3px; width: 16px; height: 16px; flex-shrink: 0; accent-color: #7c3aed; }

    .pld-kaki { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    /* Isi tombol yang berganti-ganti (teks biasa ↔ "Menyimpan…").

       Kelasnya LOKAL, bukan .orcha-ikon-teks: kelas itu hidup di berkas gaya
       Orcha, dan layar ini tidak memuatnya — ikon dan tulisannya lalu
       menempel rapat tanpa ada satu pun tanda bahwa gayanya hilang. */
    .pld-tombol-isi { display: inline-flex; align-items: center; gap: .4rem; }
    .pld-tombol-isi > i.bi { line-height: 1; flex: 0 0 auto; }

    </style>

    @php
        // Dirakit di sini: perbandingan di dalam direktif blok membuat penanda
        // morph Livewire timpang (PenandaMorphAdminTest).
        $daftarStatus = \App\Models\JobApplication::STATUS;
        $statusSama = $statusBaru === $pelamar->status;
        $adaTemplatSurel = \App\Mail\StatusLamaranMail::adaTemplat($statusBaru);
        $diubahLabel = $pelamar->status_diubah_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm');

        $warnaBs = [
            'secondary' => '#64748b', 'primary' => '#4f46e5', 'info' => '#0284c7',
            'success' => '#16a34a', 'danger' => '#dc2626', 'warning' => '#d97706',
        ];
        $lencanaBs = [
            'secondary' => 'is-abu', 'primary' => 'is-ungu', 'info' => 'is-biru',
            'success' => 'is-hijau', 'danger' => 'is-merah', 'warning' => 'is-kuning',
        ];

    @endphp

    <div class="dsb pld-detail">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam text-capitalize">{{ $pelamar->name }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-calendar3 me-1"></i>Melamar
                        {{ $pelamar->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY · HH:mm') }} WIB
                    </span>
                    <span class="d-block pld-lencana-kepala">
                        <span class="dsb-lencana is-abu pld-nomor">{{ $pelamar->nomor_lamaran ?? '—' }}</span>
                        <span class="dsb-lencana is-biru text-capitalize">
                            <i class="bi bi-briefcase"></i>{{ $pelamar->posisi_label }}
                        </span>
                        <span class="dsb-lencana {{ $lencanaBs[$pelamar->status_warna] ?? 'is-abu' }}">
                            {{ $pelamar->status_label }}
                        </span>
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.pelamar.index') }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
                @if ($pelamar->phone)
                    {{-- Yang paling sering dilakukan sesudah membaca CV adalah
                         menghubungi orangnya. Nomornya sudah ada di layar;
                         merakit tautannya sendiri hanya menambah langkah. --}}
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $pelamar->phone)) }}"
                        target="_blank" rel="noopener" class="dsb-tombol is-aman">
                        <i class="bi bi-whatsapp"></i><span>Hubungi</span>
                    </a>
                @endif
            </div>
        </header>

        {{-- ================== KETERANGAN + TAHAP ================== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-5">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-person-vcard"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Keterangan Pelamar</h2>
                            <span class="dsb-kartu-sub">Yang dia isi sendiri di formulir</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    <div class="pld-medan-rak">
                        @php
                            $keterangan = [
                                ['bi-envelope', 'Email', $pelamar->email],
                                ['bi-telephone', 'Telepon', $pelamar->phone],
                                ['bi-link-45deg', 'Portofolio', $pelamar->portofolio_url],
                                ['bi-megaphone', 'Tahu dari', $pelamar->sumber_info_label],
                                ['bi-pc-display', 'Alamat IP', $pelamar->ip_address],
                            ];
                        @endphp
                        @foreach ($keterangan as [$ikon, $label, $nilai])
                            <div class="pld-medan">
                                <span class="pld-medan-label"><i class="bi {{ $ikon }}"></i>{{ $label }}</span>
                                @if ($label === 'Portofolio' && $nilai)
                                    <a href="{{ $nilai }}" target="_blank" rel="noopener noreferrer"
                                        class="pld-medan-nilai">{{ $nilai }}</a>
                                @else
                                    <span class="pld-medan-nilai {{ $nilai ? '' : 'is-kosong' }}">
                                        {{ $nilai ?: 'tidak diisi' }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="dsb-kartu k-7">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-signpost-split"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Sudah Sampai Mana</h2>
                            <span class="dsb-kartu-sub">Pelamar melihat tahap ini di halaman Cek Status situs</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @if ($diubahLabel)
                        <p class="pld-bantu">Terakhir diubah {{ $diubahLabel }} WIB.</p>
                    @endif

                    <div class="pld-tahap-rak" role="radiogroup" aria-label="Tahap seleksi">
                        @foreach ($daftarStatus as $kunciStatus => $infoStatus)
                            <button type="button" wire:click="$set('statusBaru', '{{ $kunciStatus }}')"
                                wire:key="tahap-{{ $kunciStatus }}"
                                style="--c: {{ $warnaBs[$infoStatus[1]] ?? '#64748b' }}"
                                class="pld-tahap {{ $statusBaru === $kunciStatus ? 'is-aktif' : '' }}"
                                role="radio" aria-checked="{{ $statusBaru === $kunciStatus ? 'true' : 'false' }}">
                                {{ $infoStatus[0] }}
                            </button>
                        @endforeach
                    </div>

                    @if ($adaTemplatSurel)
                        <div class="dsb-medan">
                            <label class="dsb-label" for="pesanPelamar">
                                Pesan untuk pelamar
                                <span class="dsb-label-ket">(boleh dikosongkan — ikut di surel)</span>
                            </label>
                            <textarea id="pesanPelamar" rows="3" wire:model="pesanPelamar" maxlength="2000"
                                class="dsb-isian @error('pesanPelamar') is-galat @enderror"
                                placeholder="mis. Wawancara Senin, 17 Nov 2026 pukul 10.00 WIB lewat Google Meet. Tautan menyusul.">{{ $pesanPelamar }}</textarea>
                            @error('pesanPelamar') <span class="dsb-galat">{{ $message }}</span> @enderror
                        </div>

                        <label class="pld-cek" for="kirimEmail">
                            <input type="checkbox" id="kirimEmail" wire:model="kirimEmail" @checked($kirimEmail)>
                            <span>Kirim surel pemberitahuan ke <strong>{{ $pelamar->email }}</strong></span>
                        </label>
                    @endif

                    <div class="pld-kaki">
                        <button type="button" class="dsb-tombol is-utama" wire:click="simpanStatus"
                            wire:loading.attr="disabled" wire:target="simpanStatus" @disabled($statusSama)>
                            <span wire:loading.remove wire:target="simpanStatus" class="pld-tombol-isi">
                                <i class="bi bi-check2-circle"></i> Simpan tahap
                            </span>
                            <span wire:loading wire:target="simpanStatus" class="pld-tombol-isi">
                                <span class="dsb-putar is-kecil"></span> Menyimpan…
                            </span>
                        </button>
                        @if ($statusSama)
                            <span class="pld-bantu" style="align-self: center; margin: 0">
                                Pilih tahap yang berbeda dulu.
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== CATATAN INTERNAL ================== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-journal-text"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Catatan Tim</h2>
                            <span class="dsb-kartu-sub">Hanya terlihat di lemon — tidak pernah dikirim ke pelamar</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    <div class="dsb-medan">
                        <textarea rows="4" wire:model="catatanInternal" maxlength="5000" class="dsb-isian"
                            placeholder="Kesan wawancara, nilai tes, rekomendasi…">{{ $catatanInternal }}</textarea>
                    </div>

                    <div class="pld-kaki">
                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #d97706"
                            wire:click="simpanCatatan" wire:loading.attr="disabled" wire:target="simpanCatatan">
                            <span wire:loading.remove wire:target="simpanCatatan" class="pld-tombol-isi">
                                <i class="bi bi-save"></i> Simpan catatan
                            </span>
                            <span wire:loading wire:target="simpanCatatan" class="pld-tombol-isi">
                                <span class="dsb-putar is-kecil"></span> Menyimpan…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== BERKAS ==================

             SATU kartu bertab, bukan tiga kartu bertumpuk. Dulu ketiganya
             masing-masing setinggi A4 — membaca CV lalu surat lamarannya
             berarti menggulung tiga layar penuh, dan berkas pendukung yang
             kosong tetap memakan satu kartu. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-file-earmark-text"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Berkas Lamaran</h2>
                            <span class="dsb-kartu-sub">Dibaca langsung di sini — tidak perlu diunduh dulu</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @include('livewire.pages.admin.pelamar-kerja.partials.berkas-pratinjau', [
                        'pelamar' => $pelamar,
                        'aktif' => $berkasAktif,
                        'aksi' => 'bukaBerkas',
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
