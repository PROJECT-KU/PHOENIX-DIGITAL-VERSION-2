@section('title')
Rekap Pengeluaran || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Rekap Pengeluaran</h1>
                <p class="dsb-hero-ket">
                    @if ($rab)
                        <span class="d-block">
                            <strong>{{ $rab['kode'] }}</strong> &middot; {{ $rab['judul'] }}
                            @if ($rab['jumlah_peserta']) &middot; {{ $rab['jumlah_peserta'] }} peserta @endif
                        </span>
                    @endif
                    <span class="d-block">
                        Isi <strong>tagihan yang benar-benar diminta vendor</strong>, lalu catat tiap
                        pembayarannya. Selisihnya dihitung sendiri.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.rab.susun', $rabId) }}" wire:navigate class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Kembali ke RAB</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ========= BELUM SELESAI =========

             Rekap pada trip yang belum jalan adalah angka yang belum ada.
             Tombolnya di sini, bukan cuma di layar RAB: yang membuka rekap
             dan yang menemukan statusnya belum selesai adalah orang yang
             sama, pada detik yang sama. --}}
        @if ($rab && $rab['status'] !== 'selesai')
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12 rk-kartu-awas">
                        <div class="dsb-kartu-isi">
                            <div class="rk-guna">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-awas)">
                                    <i class="bi bi-hourglass-split"></i>
                                </span>
                                <div class="rk-guna-teks">
                                    <strong>Trip ini belum ditandai selesai.</strong>
                                    Statusnya masih <em>{{ $rab['status_label'] }}</em>. Rekapnya boleh diisi
                                    sekarang, tetapi trip ini belum masuk daftar
                                    <em>yang perlu direkap</em> sampai ditandai selesai — dan yang tidak
                                    masuk daftar itulah yang paling lama uangnya menggantung.
                                </div>
                                <button type="button" wire:click="tandaiSelesai" class="dsb-tombol is-utama">
                                    <i class="bi bi-check2-circle"></i><span>Tandai Selesai</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== ANGKA BESARNYA ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-isi">
                        <span class="orcha-label-kecil">Untung</span>
                        <div class="rk-untung">
                            <div class="rk-untung-kotak">
                                <span class="dsb-kartu-sub">Rencana (dari RAB)</span>
                                <strong class="rk-angka">{{ 'Rp '.number_format($rekap['untung_rencana'] ?? 0, 0, ',', '.') }}</strong>
                            </div>
                            <div class="rk-untung-kotak is-nyata">
                                <span class="dsb-kartu-sub">Nyata (dari tagihan)</span>
                                <strong class="rk-angka">{{ 'Rp '.number_format($rekap['untung_nyata'] ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        {{-- Angka yang dicari orang saat membuka layar ini. --}}
                        @php($hemat = (int) ($rekap['hemat'] ?? 0))
                        <div class="rk-selisih {{ $hemat > 0 ? 'is-hemat' : ($hemat < 0 ? 'is-rugi' : '') }}">
                            @if ($hemat > 0)
                                <i class="bi bi-arrow-down-circle"></i>
                                <span>Hemat <strong>{{ 'Rp '.number_format($hemat, 0, ',', '.') }}</strong>
                                    — vendor menagih lebih murah daripada RAB.</span>
                            @elseif ($hemat < 0)
                                <i class="bi bi-arrow-up-circle"></i>
                                <span>Tagihan <strong>{{ 'Rp '.number_format(abs($hemat), 0, ',', '.') }}</strong>
                                    lebih mahal daripada RAB.</span>
                            @else
                                <i class="bi bi-dash-circle"></i>
                                <span>Tagihannya pas dengan RAB.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-isi">
                        <span class="orcha-label-kecil">Uang keluar</span>
                        <div class="rk-uang">
                            <div><span class="dsb-kartu-sub">RAB</span>
                                <strong>{{ 'Rp '.number_format($rekap['rab_total'] ?? 0, 0, ',', '.') }}</strong></div>
                            <div><span class="dsb-kartu-sub">Tagihan</span>
                                <strong>{{ 'Rp '.number_format($rekap['tagihan_total'] ?? 0, 0, ',', '.') }}</strong></div>
                            <div><span class="dsb-kartu-sub">Dibayar</span>
                                <strong>{{ 'Rp '.number_format($rekap['dibayar_total'] ?? 0, 0, ',', '.') }}</strong></div>
                        </div>

                        @php($kurang = (int) ($rekap['kurang_bayar'] ?? 0))
                        @if ($kurang > 0)
                            {{-- Ini BUKAN hemat, dan itu seluruh gunanya layar ini.
                                 Uang yang masih harus keluar terbaca sebagai untung
                                 kalau tagihannya tidak dicatat terpisah. --}}
                            <div class="rk-selisih is-rugi">
                                <i class="bi bi-exclamation-triangle"></i>
                                <span>Masih kurang bayar
                                    <strong>{{ 'Rp '.number_format($kurang, 0, ',', '.') }}</strong>
                                    ke vendor. Ini utang, bukan untung.</span>
                            </div>
                        @elseif ($kurang < 0)
                            <div class="rk-selisih is-hemat">
                                <i class="bi bi-info-circle"></i>
                                <span>Kelebihan bayar
                                    <strong>{{ 'Rp '.number_format(abs($kurang), 0, ',', '.') }}</strong>
                                    — catat refundnya kalau sudah kembali.</span>
                            </div>
                        @else
                            <div class="rk-selisih">
                                <i class="bi bi-check2-circle"></i><span>Semua tagihan sudah lunas.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ========= YANG BELUM DIREKAP =========

             Satu hotel yang terlewat membuat seluruh laporannya salah, dan
             angkanya tetap terlihat masuk akal. Jadi namanya disebut. --}}
        @if (! empty($rekap['belum_direkap']))
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12 rk-kartu-awas">
                        <div class="dsb-kartu-isi">
                            <div class="rk-guna">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-awas)">
                                    <i class="bi bi-list-check"></i>
                                </span>
                                <div class="rk-guna-teks">
                                    <strong>{{ count($rekap['belum_direkap']) }} biaya belum diisi tagihannya:</strong>
                                    {{ implode(', ', $rekap['belum_direkap']) }}.
                                    <span class="dsb-kartu-sub d-block">
                                        Sementara ini dihitung sebesar RAB-nya, jadi untung nyata di atas
                                        masih angka sementara.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if (($rekap['bayar_tanpa_bukti'] ?? 0) > 0)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-isi">
                            <div class="rk-guna">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-netral)">
                                    <i class="bi bi-paperclip"></i>
                                </span>
                                <div class="rk-guna-teks">
                                    <strong>{{ $rekap['bayar_tanpa_bukti'] }} pembayaran belum ada buktinya.</strong>
                                    Angkanya tetap terhitung — yang belum ada cuma lampirannya.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== BARIS BIAYA ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-utama)"><i class="bi bi-receipt"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Rincian per biaya</h3>
                                <span class="dsb-kartu-sub">
                                    {{ $rekap['jumlah_baris'] ?? 0 }} baris dari RAB.
                                </span>
                            </div>
                        </div>
                    </div>

                    @if (count($baris) === 0)
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-inbox"></i></span>
                                <p class="dsb-kosong-judul">RAB ini belum punya baris biaya</p>
                                <p class="dsb-kosong-ket">
                                    Tidak ada yang bisa direkap sampai biayanya disusun di layar RAB.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-kartu-isi rk-daftar">
                            @foreach ($baris as $b)
                                <div class="rk-baris {{ $b['sudah_direkap'] ? 'is-direkap' : '' }}"
                                    wire:key="rk-{{ $b['biaya_id'] }}">

                                    <div class="rk-baris-kepala">
                                        <div class="rk-baris-nama">
                                            <span class="dsb-tabel-judul">{{ $b['nama'] }}</span>
                                            <span class="dsb-tabel-meta">
                                                {{ $b['kategori_label'] }}
                                                @if ($b['vendor']) &middot; {{ $b['vendor'] }} @endif
                                                &middot; {{ $b['penjelasan'] }}
                                            </span>
                                        </div>
                                        <div class="rk-baris-lencana">
                                            @if (! $b['sudah_direkap'])
                                                <span class="dsb-lencana" style="--c: var(--dsb-awas)">
                                                    <i class="bi bi-dash-circle"></i>belum direkap
                                                </span>
                                            @elseif ($b['lunas'])
                                                <span class="dsb-lencana" style="--c: var(--dsb-aman)">
                                                    <i class="bi bi-check2"></i>lunas
                                                </span>
                                            @elseif ($b['kurang_bayar'] > 0)
                                                <span class="dsb-lencana" style="--c: var(--dsb-bahaya)">
                                                    <i class="bi bi-exclamation-triangle"></i>kurang
                                                    {{ 'Rp '.number_format($b['kurang_bayar'], 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="dsb-lencana" style="--c: var(--dsb-netral)">
                                                    <i class="bi bi-arrow-counterclockwise"></i>lebih bayar
                                                </span>
                                            @endif

                                            @if ($b['hemat'] !== null && $b['hemat'] > 0)
                                                <span class="dsb-lencana" style="--c: var(--dsb-aman)">
                                                    <i class="bi bi-piggy-bank"></i>hemat
                                                    {{ 'Rp '.number_format($b['hemat'], 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rk-baris-isian">
                                        <label class="rk-medan">
                                            <span>RAB</span>
                                            <div class="rk-tetap">{{ $b['rab_teks'] }}</div>
                                        </label>

                                        <label class="rk-medan">
                                            <span>Tagihan vendor</span>
                                            <div class="orcha-rupiah">
                                                <input type="text" inputmode="numeric"
                                                    wire:model.blur="tagihan.{{ $b['biaya_id'] }}"
                                                    value="{{ $tagihan[$b['biaya_id']] ?? '' }}"
                                                    class="orcha-uang dsb-isian" placeholder="belum diisi">
                                            </div>
                                        </label>

                                        <label class="rk-medan">
                                            <span>PIC</span>
                                            <input type="text" maxlength="120"
                                                wire:model.blur="pic.{{ $b['biaya_id'] }}"
                                                value="{{ $pic[$b['biaya_id']] ?? '' }}"
                                                class="dsb-isian" placeholder="siapa yang urus">
                                        </label>

                                        <label class="rk-medan">
                                            <span>Dibayar</span>
                                            <div class="rk-tetap">{{ $b['dibayar_teks'] }}</div>
                                        </label>

                                        <div class="rk-medan-aksi">
                                            <button type="button" wire:click="simpanTagihan({{ $b['biaya_id'] }})"
                                                wire:loading.attr="disabled" wire:target="simpanTagihan({{ $b['biaya_id'] }})"
                                                class="dsb-tombol is-lembut is-kecil">
                                                <i class="bi bi-check2"></i><span>Simpan</span>
                                            </button>
                                            <button type="button" wire:click="bukaBayar({{ $b['biaya_id'] }})"
                                                class="dsb-tombol is-utama is-kecil">
                                                <i class="bi bi-plus-lg"></i><span>Bayar</span>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Pembayaran yang sudah tercatat --}}
                                    @if (count($b['bayar']) > 0)
                                        <div class="rk-bayar-daftar">
                                            @foreach ($b['bayar'] as $p)
                                                <div class="rk-bayar {{ $p['arah'] < 0 ? 'is-kembali' : '' }}"
                                                    wire:key="rkb-{{ $p['id'] }}">
                                                    <span class="rk-bayar-jenis">{{ $p['jenis_label'] }}</span>
                                                    <span class="rk-bayar-nominal">
                                                        {{ $p['arah'] < 0 ? '− ' : '' }}{{ $p['nominal_teks'] }}
                                                    </span>
                                                    <span class="dsb-tabel-meta">{{ $p['tanggal_teks'] ?? '—' }}</span>
                                                    <span class="dsb-tabel-meta">{{ $p['oleh'] ?? '' }}</span>

                                                    @if ($p['bukti'])
                                                        <a href="{{ $p['bukti'] }}" target="_blank" rel="noopener"
                                                            class="rk-bukti is-ada">
                                                            <i class="bi bi-paperclip"></i>bukti
                                                            @if ($p['bukti_diganti'] > 0)
                                                                <span class="rk-diganti"
                                                                    title="Bukti pernah diganti {{ $p['bukti_diganti'] }} kali; yang lama tersimpan">
                                                                    ({{ $p['bukti_diganti'] }}×)
                                                                </span>
                                                            @endif
                                                        </a>
                                                    @else
                                                        <span class="rk-bukti is-kosong">
                                                            <i class="bi bi-paperclip"></i>belum ada bukti
                                                        </span>
                                                    @endif

                                                    <div class="rk-bayar-aksi">
                                                        <button type="button" wire:click="bukaGanti({{ $p['id'] }})"
                                                            class="dsb-tombol is-lembut is-kecil"
                                                            title="{{ $p['bukti'] ? 'Ganti bukti' : 'Unggah bukti' }}">
                                                            <i class="bi bi-upload"></i>
                                                        </button>
                                                        <button type="button" class="dsb-tombol is-bahaya is-kecil pcek-konfirmasi"
                                                            data-action="hapusBayar" data-arg="{{ $p['id'] }}"
                                                            data-title="Hapus pembayaran ini?"
                                                            data-text="{{ $p['jenis_label'] }} {{ $p['nominal_teks'] }}. Berkas buktinya tetap tersimpan di arsip."
                                                            data-confirm="Hapus" data-icon="warning" title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>

                                                    @if ($gantiUntuk === $p['id'])
                                                        <div class="rk-unggah">
                                                            <input type="file" accept="image/*" wire:model="buktiBaru"
                                                                class="dsb-isian">
                                                            @error('buktiBaru')<span class="dsb-galat">{{ $message }}</span>@enderror
                                                            <div class="rk-unggah-aksi">
                                                                <button type="button" wire:click="tutupBayar"
                                                                    class="dsb-tombol is-lembut is-kecil">Batal</button>
                                                                <button type="button" wire:click="simpanGantiBukti"
                                                                    wire:loading.attr="disabled" wire:target="simpanGantiBukti,buktiBaru"
                                                                    class="dsb-tombol is-utama is-kecil">
                                                                    <span class="dsb-putar" wire:loading wire:target="simpanGantiBukti,buktiBaru"></span>
                                                                    <span>Simpan Bukti</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Panel tambah pembayaran --}}
                                    @if ($bayarUntuk === $b['biaya_id'])
                                        <div class="rk-tambah">
                                            <div class="rk-tambah-rak">
                                                <label class="rk-medan">
                                                    <span>Jenis</span>
                                                    <select wire:model="bayar.jenis" class="dsb-isian">
                                                        @foreach ($jenisBayar as $kunci => $label)
                                                            <option value="{{ $kunci }}" @selected($this->bayar['jenis'] === (string) $kunci)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </label>
                                                <label class="rk-medan">
                                                    <span>Nominal</span>
                                                    <div class="orcha-rupiah">
                                                        <input type="text" inputmode="numeric" wire:model.blur="bayar.nominal"
                                                            class="orcha-uang dsb-isian @error('bayar.nominal') is-galat @enderror"
                                                            placeholder="500.000">
                                                    </div>
                                                    @error('bayar.nominal')<span class="dsb-galat">{{ $message }}</span>@enderror
                                                </label>
                                                <label class="rk-medan">
                                                    <span>Tanggal</span>
                                                    <input type="date" wire:model="bayar.tanggal" class="dsb-isian">
                                                </label>
                                                <label class="rk-medan is-lebar">
                                                    <span>Bukti transfer <span class="dsb-label-ket">boleh menyusul</span></span>
                                                    <input type="file" accept="image/*" wire:model="bukti" class="dsb-isian">
                                                    @error('bukti')<span class="dsb-galat">{{ $message }}</span>@enderror
                                                </label>
                                                <label class="rk-medan is-lebar">
                                                    <span>Catatan</span>
                                                    <input type="text" maxlength="300" wire:model="bayar.catatan"
                                                        class="dsb-isian" placeholder="mis. transfer BCA a.n. Fave">
                                                </label>
                                            </div>
                                            <div class="rk-unggah-aksi">
                                                <button type="button" wire:click="tutupBayar" class="dsb-tombol is-lembut">
                                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                                </button>
                                                <button type="button" wire:click="simpanBayar"
                                                    wire:loading.attr="disabled" wire:target="simpanBayar,bukti"
                                                    class="dsb-tombol is-utama">
                                                    <i class="bi bi-check2-circle" wire:loading.remove wire:target="simpanBayar,bukti"></i>
                                                    <span class="dsb-putar" wire:loading wire:target="simpanBayar,bukti"></span>
                                                    <span>Catat Pembayaran</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ================== PER KATEGORI ================== --}}
        @if (! empty($rekap['per_kategori']))
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-diagram-3"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Per kategori</h3>
                                    <span class="dsb-kartu-sub">Dari mana hemat atau selisihnya datang.</span>
                                </div>
                            </div>
                        </div>
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>RAB</th>
                                        <th>Tagihan</th>
                                        <th>Dibayar</th>
                                        <th>Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rekap['per_kategori'] as $k)
                                        <tr wire:key="rkk-{{ $k['kategori'] }}">
                                            <td data-judul="Kategori">
                                                <span class="dsb-tabel-judul">{{ $k['label'] }}</span>
                                                @if ($k['belum_direkap'] > 0)
                                                    <span class="dsb-tabel-meta">{{ $k['belum_direkap'] }} belum direkap</span>
                                                @endif
                                            </td>
                                            <td data-judul="RAB"><span class="dsb-tabel-meta">{{ $k['rab_teks'] }}</span></td>
                                            <td data-judul="Tagihan"><span class="dsb-tabel-meta">{{ $k['tagihan_teks'] }}</span></td>
                                            <td data-judul="Dibayar"><span class="dsb-tabel-meta">{{ $k['dibayar_teks'] }}</span></td>
                                            <td data-judul="Selisih">
                                                @if ($k['hemat'] > 0)
                                                    <span class="dsb-lencana" style="--c: var(--dsb-aman)">
                                                        <i class="bi bi-arrow-down"></i>{{ 'Rp '.number_format($k['hemat'], 0, ',', '.') }}
                                                    </span>
                                                @elseif ($k['hemat'] < 0)
                                                    <span class="dsb-lencana" style="--c: var(--dsb-bahaya)">
                                                        <i class="bi bi-arrow-up"></i>{{ 'Rp '.number_format(abs($k['hemat']), 0, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span class="dsb-tabel-meta">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    {{-- <style> telanjang: layout templateindex tidak punya stack, jadi gaya
         yang didorong ke sana tidak pernah tergambar. --}}
    <style>
        .rk-guna { display: flex; align-items: flex-start; gap: 11px; flex-wrap: wrap; }
        .rk-guna-teks { flex: 1 1 280px; min-width: 0; font-size: .85rem; line-height: 1.55; }
        .rk-kartu-awas { border-color: #fcd9a4; background: #fffdf7; }

        .rk-untung { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px; }
        .rk-untung-kotak {
            flex: 1 1 150px; min-width: 0; padding: 10px 12px; border-radius: 12px;
            background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
            display: flex; flex-direction: column; gap: 2px;
        }
        /* Yang NYATA dibedakan: dua angka bertumpuk tanpa penanda terbaca
           sebagai "sebelum & sesudah pajak", dan yang dipakai orang selalu
           yang atas. */
        .rk-untung-kotak.is-nyata { background: #f0fdf4; border-color: #bbf7d0; }
        .rk-angka { font-size: 1.05rem; font-variant-numeric: tabular-nums; }

        .rk-uang { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 6px; }
        .rk-uang > div { display: flex; flex-direction: column; gap: 2px; }
        .rk-uang strong { font-variant-numeric: tabular-nums; }

        .rk-selisih {
            display: flex; align-items: flex-start; gap: 7px; margin-top: 11px;
            padding: 9px 11px; border-radius: 10px; font-size: .82rem; line-height: 1.5;
            background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
        }
        .rk-selisih.is-hemat { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .rk-selisih.is-rugi { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

        .rk-daftar { display: flex; flex-direction: column; gap: 12px; }
        .rk-baris {
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 14px;
            padding: 13px 14px; background: #fff;
        }
        /* Baris yang sudah diperiksa orang dibedakan tipis: dari dua puluh
           baris, yang belum harus bisa ditemukan tanpa membaca tiap kotak. */
        .rk-baris.is-direkap { background: #fbfdfb; }
        .rk-baris-kepala { display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
        .rk-baris-nama { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1 1 240px; }
        .rk-baris-lencana { display: flex; gap: 5px; flex-wrap: wrap; align-items: flex-start; }

        .rk-baris-isian { display: flex; flex-wrap: wrap; gap: .5rem; align-items: flex-end; margin-top: 11px; }
        .rk-medan { display: flex; flex-direction: column; gap: 3px; flex: 0 1 170px; min-width: 0; }
        .rk-medan.is-lebar { flex: 1 1 280px; }
        .rk-medan > span:first-child {
            font-size: .66rem; font-weight: 700; letter-spacing: .04em;
            text-transform: uppercase; color: var(--dsb-redup, #94a3b8);
        }
        /* Yang bisa DIKETIK dibedakan dari yang cuma ditampilkan.

           RAB dan Dibayar berdiri tepat di sebelah kotak Tagihan yang
           memang isian. Diberi rupa yang sama — kotak putih bergaris —
           keduanya terbaca sebagai isian yang macet, dan yang mencobanya
           akan menyimpulkan layarnya rusak. Jadi keduanya dibuat rata,
           tanpa garis, dengan latar yang jelas bukan putih. */
        .rk-tetap {
            padding: 7px 2px; background: transparent; border: 0;
            border-bottom: 1px dashed var(--dsb-tepi, #e9edf3);
            color: var(--dsb-tinta, #1c1f26);
            font-weight: 700; font-variant-numeric: tabular-nums; font-size: .92rem;
        }
        .rk-medan-aksi { display: flex; gap: 6px; align-items: flex-end; flex-wrap: wrap; }

        .rk-bayar-daftar { display: flex; flex-direction: column; gap: 6px; margin-top: 11px; }
        .rk-bayar {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            padding: 7px 10px; border-radius: 10px; background: #f8fafc;
            border: 1px solid var(--dsb-tepi, #e9edf3); font-size: .82rem;
        }
        /* Uang yang KEMBALI dibedakan warnanya: deretan angka yang semuanya
           terlihat sama membuat refund ikut terbaca sebagai uang keluar. */
        .rk-bayar.is-kembali { background: #f0fdf4; border-color: #bbf7d0; }
        .rk-bayar-jenis { font-weight: 700; }
        .rk-bayar-nominal { font-variant-numeric: tabular-nums; font-weight: 600; }
        .rk-bayar-aksi { display: inline-flex; gap: 5px; margin-left: auto; }

        .rk-bukti { display: inline-flex; align-items: center; gap: 4px; font-size: .78rem; }
        .rk-bukti.is-ada { color: #4f46e5; text-decoration: underline; }
        /* Yang BELUM ada bukti disebut, bukan dibiarkan kosong: baris tanpa
           penanda terbaca sebagai baris yang lengkap. */
        .rk-bukti.is-kosong { color: var(--dsb-awas, #b45309); }
        .rk-diganti { font-weight: 700; }

        .rk-unggah { flex: 1 1 100%; display: flex; flex-direction: column; gap: 6px; margin-top: 6px; }
        .rk-unggah-aksi { display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap; margin-top: 10px; }

        .rk-tambah {
            margin-top: 11px; padding-top: 11px;
            border-top: 1px dashed var(--dsb-tepi, #e9edf3);
        }
        .rk-tambah-rak { display: flex; flex-wrap: wrap; gap: .5rem; align-items: flex-end; }
    </style>
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
