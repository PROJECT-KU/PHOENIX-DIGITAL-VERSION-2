@section('title')
{{ $data['vendor']['nama'] ?? 'Vendor' }} || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $v = $data['vendor'] ?? [];
        $harga = $data['harga'] ?? [];
        $rab = $data['rab'] ?? [];
        $nomorTel = $this->nomorDari($v['kontak'] ?? null);
        $nomorWa = $this->nomorWa($v['kontak'] ?? null);

        $pilihanJenis = $this->pilihanJenis();
        $jenisKini = trim((string) ($isian['jenis'] ?? ''));
        $jenisAsing = $jenisKini !== '' && ! in_array($jenisKini, $pilihanJenis, true);
        $provinsiAda = array_values(array_filter([$v['provinsi'] ?? null]));

        $rupaJenis = [
            'Armada' => ['#0369a1', 'bi-bus-front'],
            'Akomodasi' => ['#6d28d9', 'bi-building'],
            'Konsumsi' => ['#e0912f', 'bi-cup-hot'],
            'Tiket' => ['#1f7a44', 'bi-ticket-perforated'],
            'Pemandu' => ['#0e7490', 'bi-person-badge'],
        ];
        $rupaDari = fn ($j) => $rupaJenis[$j] ?? ['#64748b', 'bi-shop'];

        $rabMeta = $data['rab_meta'] ?? [];

        $rupaStatus = [
            'draf' => '#64748b', 'dikirim' => '#0369a1',
            'disetujui' => '#1f7a44', 'batal' => '#be123c',
        ];
    @endphp

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-shop"></i> Vendor</span>
                <h1 class="dsb-judul">{{ $v['nama'] ?? 'Vendor' }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        {{ $v['jenis'] ?? 'Belum berjenis' }} ·
                        {{ ($v['provinsi'] ?? null) ?: 'Berlaku di semua provinsi' }}
                    </span>
                    @if (! empty($v['catatan']))
                        <span class="d-block">{{ $v['catatan'] }}</span>
                    @endif
                </p>
            </div>

            <div class="dsb-hero-aksi">
                @if ($nomorWa)
                    <a href="https://wa.me/{{ $nomorWa }}" target="_blank" rel="noopener"
                        class="dsb-tombol is-lembut" style="--ikon: #1f7a44">
                        <i class="bi bi-whatsapp"></i><span>WhatsApp</span>
                    </a>
                    <a href="tel:{{ $nomorTel }}" class="dsb-tombol is-lembut" style="--ikon: #0369a1">
                        <i class="bi bi-telephone"></i><span>Telepon</span>
                    </a>
                @endif
                <button type="button" wire:click="bukaUbah" class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                    <i class="bi bi-pencil-square"></i><span>Ubah</span>
                </button>
                @if ($v['aktif'] ?? false)
                    {{-- Keputusan mematikan vendor justru lahir di sini, saat
                         angka belanjanya terlihat. Melempar admin kembali ke
                         daftar berarti menyuruhnya mencari baris yang baru
                         saja ia buka. --}}
                    <button type="button" class="dsb-tombol is-lembut pcek-konfirmasi" style="--ikon: #b45309"
                        data-action="matikanIni"
                        data-title="Matikan {{ $v['nama'] ?? 'vendor ini' }}?"
                        data-text="Tidak ditawarkan lagi untuk harga baru. Kontaknya tetap terbaca di RAB yang sudah memakainya."
                        data-confirm="Ya, matikan" data-icon="question">
                        <i class="bi bi-pause-circle"></i><span>Matikan</span>
                    </button>
                @endif
                <a href="{{ route('admin.orcha.vendor') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Daftar Vendor</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        @if ($sunting)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #4f46e5"><i class="bi bi-pencil-square"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Ubah vendor</h3>
                                    <span class="dsb-kartu-sub">Nomornya dibaca <strong>hidup</strong> — RAB lama pun ikut menunjuk nomor yang baru.</span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            @include('livewire.pages.admin.orcha.vendor.partials.medan')

                            <div class="vd-kaki">
                                <button type="button" wire:click="tutup" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>
                                <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                                    class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                                    <i class="bi bi-check2-circle" wire:loading.remove wire:target="simpan"></i>
                                    <span class="dsb-putar" wire:loading wire:target="simpan"></span>
                                    <span>Simpan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ===== Angka yang menjawab "sudah sejauh apa dengan vendor ini" ===== --}}
        {{-- Satu angka, bukan tiga.

             "Harga terdaftar: 3" dan "Dipakai di RAB: 2" mengulang apa yang
             bisa dihitung sendiri dari dua tabel tepat di bawahnya; keduanya
             pindah ke kepala tabelnya masing-masing. Yang benar-benar baru
             cuma nilai belanjanya — dan itu yang pantas sebesar ini. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi vd-belanja">
                        <span class="dsb-ikon" style="--c: #4f46e5"><i class="bi bi-cash-stack"></i></span>
                        <div class="vd-belanja-teks">
                            <span class="dsb-kartu-sub">Sudah dibelanjakan ke vendor ini</span>
                            <strong class="vd-belanja-nilai">{{ $data['belanja_teks'] ?? 'Rp 0' }}</strong>
                            <span class="dsb-kartu-sub">
                                Dihitung dari seluruh RAB yang memakainya — bukan dari halaman yang sedang terlihat.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== Harga yang dibeli dari vendor ini ===== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #b45309"><i class="bi bi-tags"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Harga dari vendor ini</h3>
                                <span class="dsb-kartu-sub">Harga master, bukan yang sudah beku di RAB.</span>
                            </div>
                        </div>
                        <span class="dsb-chip"><i class="bi bi-tags"></i>{{ count($harga) }} harga</span>
                        <a href="{{ route('admin.orcha.master-harga', ['vendor' => $vendorId]) }}" wire:navigate
                            class="dsb-tombol is-lembut" style="--ikon: #b45309">
                            <i class="bi bi-pencil-square"></i><span>Kelola</span>
                        </a>
                    </div>

                    @if ($harga === [])
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-tags"></i></span>
                                <p class="dsb-kosong-judul">Belum ada harga</p>
                                <p class="dsb-kosong-ket">Vendor ini belum dipasang di satu pun baris master harga.</p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel vdd-tabel">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th class="text-end">Harga</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($harga as $h)
                                        <tr wire:key="vh-{{ $h['id'] }}">
                                            <td data-judul="Nama"><span class="dsb-tabel-judul">{{ $h['nama'] }}</span></td>
                                            <td data-judul="Kategori"><span class="dsb-tabel-meta">{{ $h['kategori_label'] }}</span></td>
                                            <td data-judul="Harga" class="text-end">
                                                <span class="vdd-utama is-kanan">
                                                    <span class="dsb-tabel-judul dsb-tabel-angka">{{ $h['harga_teks'] }}</span>
                                                    <span class="dsb-tabel-meta">{{ $h['satuan_label'] }}</span>
                                                </span>
                                            </td>
                                            <td data-judul="Status">
                                                @if ($h['aktif'])
                                                    <span class="dsb-lencana" style="--c: #1f7a44"><i class="bi bi-check-circle"></i>Aktif</span>
                                                @else
                                                    <span class="dsb-lencana" style="--c: #64748b"><i class="bi bi-pause-circle"></i>Mati</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ===== RAB yang memakainya ===== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0369a1"><i class="bi bi-calculator"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">RAB yang memakainya</h3>
                                <span class="dsb-kartu-sub">Terbaru di atas · nilainya memakai rumus yang sama dengan rincian biaya.</span>
                            </div>
                        </div>
                        <span class="dsb-chip"><i class="bi bi-calculator"></i>{{ $rabMeta['total'] ?? count($rab) }} RAB</span>
                    </div>

                    @if ($rab === [])
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-calculator"></i></span>
                                <p class="dsb-kosong-judul">Belum dipakai di RAB mana pun</p>
                                <p class="dsb-kosong-ket">Harga dari vendor ini belum pernah masuk ke sebuah penawaran.</p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel vdd-tabel">
                                <thead>
                                    <tr>
                                        <th>Perjalanan</th>
                                        <th>Status</th>
                                        <th class="text-end">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rab as $r)
                                        @php($warna = $rupaStatus[$r['status']] ?? '#64748b')
                                        <tr wire:key="vr-{{ $r['id'] }}">
                                            <td data-judul="Perjalanan">
                                                <span class="vdd-utama">
                                                    <a href="{{ route('admin.orcha.rab.susun', $r['id']) }}" wire:navigate
                                                        class="dsb-tabel-judul vdd-tautan">{{ $r['judul'] }}</a>
                                                    <span class="dsb-tabel-meta">
                                                        <span class="orcha-kode">{{ $r['kode'] }}</span>
                                                        {{ $r['jumlah_baris'] }} baris biaya
                                                    </span>
                                                </span>
                                            </td>
                                            <td data-judul="Status">
                                                <span class="dsb-lencana" style="--c: {{ $warna }}">{{ $r['status_label'] }}</span>
                                            </td>
                                            <td data-judul="Nilai" class="text-end">
                                                <span class="dsb-tabel-judul dsb-tabel-angka">{{ $r['nilai_teks'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if (($rabMeta['halaman_terakhir'] ?? 1) > 1)
                            {{-- Daftarnya berhalaman; nilai belanja di atas TIDAK —
                                 "sudah dibelanjakan Rp 6 juta" yang ternyata cuma
                                 halaman pertama adalah angka yang salah tanpa
                                 kelihatan salah. --}}
                            <div class="vdd-halaman">
                                <span class="dsb-kartu-sub">
                                    Halaman {{ $rabMeta['halaman'] }} dari {{ $rabMeta['halaman_terakhir'] }} ·
                                    {{ $rabMeta['total'] }} RAB
                                </span>
                                <span class="vdd-halaman-tombol">
                                    <button type="button" wire:click="keHalamanRab({{ $halamanRab - 1 }})"
                                        class="dsb-tombol is-lembut" style="--ikon: #64748b"
                                        @disabled($halamanRab <= 1)>
                                        <i class="bi bi-chevron-left"></i><span>Sebelumnya</span>
                                    </button>
                                    <button type="button" wire:click="keHalamanRab({{ $halamanRab + 1 }})"
                                        class="dsb-tombol is-lembut" style="--ikon: #64748b"
                                        @disabled($halamanRab >= ($rabMeta['halaman_terakhir'] ?? 1))>
                                        <span>Berikutnya</span><i class="bi bi-chevron-right"></i>
                                    </button>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </section>
    </div>

    <style>
        .vd-belanja { display: flex; align-items: center; gap: 14px; }
        .vd-belanja-teks { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .vd-belanja-nilai { font-size: 1.6rem; font-weight: 800; line-height: 1.15; color: var(--dsb-tinta, #1c1f26); }

        .vd-kaki { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }
        .vdd-halaman {
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9;
        }
        .vdd-halaman .dsb-kartu-sub { flex: 1 1 200px; }
        .vdd-halaman-tombol { display: flex; gap: 8px; }

        .vdd-utama { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .vdd-utama.is-kanan { align-items: flex-end; }
        .vdd-tautan { text-decoration: none; }
        .vdd-tautan:hover { color: #4338ca; }

        @media (max-width: 767.98px) {
            .vdd-tabel { min-width: 0; }
            .vdd-tabel thead { display: none; }
            .vdd-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
            .vdd-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0; text-align: left !important;
            }
            .vdd-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            .vdd-tabel tbody td > * { min-width: 0; }
            .vdd-utama { align-items: flex-end; text-align: right; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
