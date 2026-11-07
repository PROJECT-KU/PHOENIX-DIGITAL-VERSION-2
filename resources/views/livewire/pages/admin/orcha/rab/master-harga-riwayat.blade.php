@section('title')
{{ $hargaId ? 'Riwayat Harga' : 'Perubahan Harga Terakhir' }} || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')
    @include('livewire.pages.admin.orcha.rab.partials.master-harga-gaya')

    <div class="dsb">
        {{-- Satu halaman, dua pertanyaan: tanpa {harga} ia menjawab "apa saja
             yang berubah belakangan", dengan {harga} ia menjawab "harga ini
             pernah berapa". --}}
        @if ($galat !== '')
            <div class="mh-peringatan"><i class="bi bi-exclamation-triangle"></i><span>{{ $galat }}</span></div>
        @endif

        @if ($hargaId)
                <section class="dsb-bagian">
                    <div class="dsb-rak">
                        <div class="dsb-kartu k-12">
                            <div class="dsb-kartu-kepala">
                                <div class="dsb-kartu-kepala-kiri">
                                    <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-clock-history"></i></span>
                                    <div>
                                        {{-- "Riwayat harga" sudah tidak benar: yang dicatat
                                             kini seluruh perubahan barisnya, bukan angkanya
                                             saja. Judul yang menjanjikan lebih sempit
                                             daripada isinya membuat orang berhenti mencari
                                             di sini. --}}
                                        <h3 class="dsb-kartu-judul">Riwayat perubahan — {{ $meta['nama'] ?? '' }}</h3>
                                        <span class="dsb-kartu-sub">
                                            Setiap kali baris ini disunting, beserta siapa yang menyuntingnya.
                                            Harganya sekarang <strong>{{ $meta['harga_kini'] ?? '-' }}</strong> —
                                            RAB yang sudah jadi tetap memakai angka saat ia dibuat.
                                        </span>
                                    </div>
                                </div>
                                <button type="button"  class="dsb-tombol is-lembut">
                                    <i class="bi bi-x-lg"></i><span>Tutup</span>
                                </button>
                            </div>

                            @if ($baris === [])
                                {{-- Satu baris, bukan blok kosong setinggi layar. Panel ini
                                     dibuka dari dalam daftar, jadi tidak ada apa pun untuk
                                     dilihat harus berarti "sedikit tempat", bukan sebaliknya. --}}
                                <div class="dsb-kartu-isi">
                                    <p class="mh-jejak-nihil">
                                        <i class="bi bi-clock-history"></i>
                                        Harganya belum pernah berubah — yang tercatat hanya perubahan angkanya.
                                    </p>
                                </div>
                            @else
                                <div class="dsb-tabel-bungkus">
                                    <table class="dsb-tabel mh-riwayat">
                                        <thead>
                                            {{-- TIGA kolom, bukan lima.

                                                 Dari / Jadi / Selisih semuanya berporos harga,
                                                 dan sejak perubahan lain ikut dicatat ketiganya
                                                 berdiri kosong di sebagian besar baris —
                                                 tepatnya pada baris yang paling perlu dibaca,
                                                 karena destinasi yang salah satu huruf tidak
                                                 menyentuh angka mana pun. --}}
                                            <tr>
                                                <th>Kapan</th>
                                                <th>Yang berubah</th>
                                                <th>Oleh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($baris as $r)
                                                <tr wire:key="riw-{{ $r['id'] }}">
                                                    <td data-judul="Kapan">
                                                        <span class="mh-utama">
                                                            <span class="dsb-tabel-judul">{{ $r['pada'] }}</span>
                                                            <span class="dsb-tabel-meta mh-diubah">{{ $r['pada_teks'] }}</span>
                                                        </span>
                                                    </td>
                                                    <td data-judul="Yang berubah">
                                                        <div class="mh-ubah">
                                                            {{-- Harga tetap di atas: ia yang paling
                                                                 sering dicari, dan satu-satunya yang
                                                                 punya selisih & persen. --}}
                                                            @if ($r['harga_berubah'] ?? true)
                                                                <div class="mh-ubah-baris">
                                                                    <span class="mh-ubah-medan">Harga</span>
                                                                    <span class="mh-ubah-nilai">
                                                                        <span class="mh-riwayat-lama">{{ $r['harga_lama'] }}</span>
                                                                        <i class="bi bi-arrow-right"></i>
                                                                        <strong>{{ $r['harga_baru'] }}</strong>
                                                                    </span>
                                                                    <span class="mh-selisih {{ $r['naik'] ? 'is-naik' : 'is-turun' }}">
                                                                        <i class="bi {{ $r['naik'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>{{ $r['selisih'] }}@if ($r['persen'] !== null) · {{ $r['naik'] ? '+' : '' }}{{ $r['persen'] }}%@endif
                                                                    </span>
                                                                </div>
                                                            @endif

                                                            @if (! empty($r['akhir_pekan']))
                                                                <div class="mh-ubah-baris">
                                                                    <span class="mh-ubah-medan">Harga akhir pekan</span>
                                                                    <span class="mh-ubah-nilai">
                                                                        <span class="mh-riwayat-lama">{{ $r['akhir_pekan']['lama'] ?: 'belum ada' }}</span>
                                                                        <i class="bi bi-arrow-right"></i>
                                                                        <strong>{{ $r['akhir_pekan']['baru'] ?: 'dihapus' }}</strong>
                                                                    </span>
                                                                </div>
                                                            @endif

                                                            {{-- Perubahan selain harga: nama, vendor,
                                                                 destinasi, dan seterusnya. --}}
                                                            @foreach ($r['perubahan'] ?? [] as $u)
                                                                <div class="mh-ubah-baris">
                                                                    <span class="mh-ubah-medan">{{ $u['medan'] }}</span>
                                                                    <span class="mh-ubah-nilai">
                                                                        <span class="mh-riwayat-lama">{{ $u['dari'] ?: 'kosong' }}</span>
                                                                        <i class="bi bi-arrow-right"></i>
                                                                        <strong>{{ $u['ke'] ?: 'dikosongkan' }}</strong>
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td data-judul="Oleh">
                                                        <span class="mh-utama">
                                                            <span>{{ $r['oleh'] ?: 'tidak tercatat' }}</span>
                                                            @if (! empty($r['catatan']))
                                                                <span class="dsb-tabel-meta">{{ $r['catatan'] }}</span>
                                                            @endif
                                                        </span>
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
        @else
                <section class="dsb-bagian">
                    <div class="dsb-rak">
                        <div class="dsb-kartu k-12">
                            <div class="dsb-kartu-kepala">
                                <div class="dsb-kartu-kepala-kiri">
                                    <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-clock-history"></i></span>
                                    <div>
                                        <h3 class="dsb-kartu-judul">Perubahan terakhir</h3>
                                        <span class="dsb-kartu-sub">
                                            Yang tercatat hanya perubahan angkanya — mengganti nama, vendor, atau catatan
                                            tidak meninggalkan jejak. Tekan namanya untuk membuka riwayat harga itu sendiri.
                                        </span>
                                    </div>
                                </div>
                                <span class="mh-kepala-aksi">
                                    <select class="dsb-isian mh-periode" wire:model.live="hari" aria-label="Periode">
                                        @foreach ([0 => 'Sejak awal', 7 => '7 hari terakhir', 30 => '30 hari terakhir', 90 => '90 hari terakhir'] as $hari => $label)
                                            <option value="{{ $hari }}" @selected((int) $hari === $hari)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button"  class="dsb-tombol is-lembut">
                                        <i class="bi bi-x-lg"></i><span>Tutup</span>
                                    </button>
                                </span>
                            </div>

                            @php($jumlahJejak = (int) ($meta['jumlah'] ?? count($baris)))
                            @if (count($baris) < $jumlahJejak)
                                {{-- Daftar yang dipotong diam-diam membuat orang mengira
                                     itulah seluruhnya. --}}
                                <div class="dsb-kartu-isi mh-jejak-potong">
                                    Menampilkan {{ count($baris) }} perubahan terbaru dari
                                    <strong>{{ number_format($jumlahJejak, 0, ',', '.') }}</strong>. Persempit periodenya untuk melihat yang lain.
                                </div>
                            @endif

                            <div class="dsb-kartu-isi mh-riwayat-semua">
                                @if ($baris === [])
                                    {{-- Satu baris, bukan blok kosong setinggi layar: tidak
                                         ada apa-apa untuk dilihat, dan ruang yang disediakan
                                         untuk ketiadaan itu tetap harus digulir. --}}
                                    <p class="mh-jejak-nihil">
                                        <i class="bi bi-clock-history"></i>
                                        Belum ada harga yang pernah berubah angkanya.
                                    </p>
                                @else
                                    <ol class="mh-jejak">
                                        @foreach ($baris as $r)
                                            <li wire:key="jejak-{{ $r['id'] }}">
                                                <span class="mh-jejak-tanggal">{{ $r['pada_teks'] }}</span>
                                                <button type="button" wire:click="bukaRiwayat({{ $r['harga_id'] }})"
                                                    class="mh-jejak-nama">{{ $r['harga_nama'] ?: 'harga yang sudah dihapus' }}</button>
                                                {{-- Perubahan yang HANYA menyentuh harga akhir pekan
                                                     menyisakan selisih nol di kolom hari kerja. Ditulis
                                                     apa adanya, ia terbaca "−Rp 0, turun" — perubahan
                                                     yang seolah tidak terjadi, padahal itulah yang
                                                     dicari orang saat membuka riwayat. --}}
                                                @if ($r['harga_berubah'] ?? true)
                                                    <span class="mh-jejak-angka">
                                                        {{ $r['harga_lama'] }} &rarr; <strong>{{ $r['harga_baru'] }}</strong>
                                                    </span>
                                                    <span class="mh-selisih {{ $r['naik'] ? 'is-naik' : 'is-turun' }}">
                                                        <i class="bi {{ $r['naik'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>{{ $r['selisih'] }}
                                                    </span>
                                                @endif

                                                @if (! empty($r['akhir_pekan']))
                                                    <span class="mh-jejak-angka mh-jejak-pekan">
                                                        akhir pekan
                                                        {{ $r['akhir_pekan']['lama'] ?: '—' }} &rarr;
                                                        <strong>{{ $r['akhir_pekan']['baru'] ?: 'dihapus' }}</strong>
                                                    </span>
                                                @endif
                                                @if ($r['oleh'])<span class="mh-jejak-oleh">oleh {{ $r['oleh'] }}</span>@endif
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
        @endif

        <div class="mh-riwayat-kaki">
            <a href="{{ $this->tujuanKembali() }}" class="dsb-tombol is-lembut" style="--ikon: #64748b" wire:navigate>
                <i class="bi bi-arrow-left"></i><span>Kembali ke Master Harga</span>
            </a>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
