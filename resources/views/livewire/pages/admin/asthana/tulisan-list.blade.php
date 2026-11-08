@section('title')
{{ $info[0] }} · Situs Asthana || lemon
@stop

@php
    [$judul, $ikonJudul, $warna, $keterangan] = $info;
    $isBerita = $jenis === 'berita';
    $kosong = $daftar->isEmpty();
    $adaSaringan = $cari !== '' || $keadaan !== 'semua';
    $urlTulis = route('admin.asthana.'.$jenis.'.tulis');
    $lencanaKeadaan = ['terbit' => ['is-hijau', 'bi-globe2', 'Terbit'], 'terjadwal' => ['is-biru', 'bi-clock', 'Terjadwal'], 'draf' => ['is-abu', 'bi-pencil', 'Draf']];
    $warnaTab = ['semua' => '#475569', 'terbit' => '#16a34a', 'terjadwal' => '#0284c7', 'draf' => '#94a3b8'];
@endphp

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.asthana.partials.gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Isi Situs Asthana</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Dikelola dari sini, tampil di situs profil PT. Asthana Cipta Mandiri.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ $urlTulis }}" class="dsb-tombol is-utama">
                    <i class="bi bi-plus-lg"></i><span>Tulis {{ strtolower($judul) }}</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.asthana.partials.navigasi', ['aktif' => $jenis])

        <section class="dsb-bagian">
            <div class="dsb-kartu">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: {{ $warna }}"><i class="bi {{ $ikonJudul }}"></i></span>
                    <span class="dsb-jendela-teks">
                        <h2 class="dsb-jendela-judul">{{ $judul }}</h2>
                        <span class="dsb-kartu-sub">{{ $keterangan }}</span>
                    </span>
                </div>

                <div class="dsb-kartu-isi ast-tumpuk">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="dsb-cari" style="flex: 1 1 260px;">
                            <i class="bi bi-search"></i>
                            <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="cari"
                                placeholder="{{ $isBerita ? 'Judul, kategori, ringkasan…' : 'Judul, klien, ringkasan…' }}">
                            @if ($cari !== '')
                                <button type="button" class="dsb-cari-hapus" wire:click="$set('cari', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                            @endif
                        </div>
                        <div class="ast-nav mb-0 p-0">
                            @foreach ($daftarKeadaan as $kunciKeadaan => $labelKeadaan)
                                <button type="button" style="--c: {{ $warnaTab[$kunciKeadaan] }}" wire:click="setKeadaan('{{ $kunciKeadaan }}')"
                                    class="ast-nav-item {{ $keadaan === $kunciKeadaan ? 'is-aktif' : '' }}">
                                    <span>{{ $labelKeadaan }}</span><b>{{ $jumlah[$kunciKeadaan] }}</b>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @if ($kosong)
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi {{ $adaSaringan ? 'bi-funnel' : $ikonJudul }}"></i></span>
                            <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada '.strtolower($judul) }}</p>
                            <p class="dsb-kosong-ket">{{ $adaSaringan ? 'Coba kata kunci atau keadaan lain.' : 'Halaman ini di situs menampilkan pesan "segera hadir" sampai ada yang terbit.' }}</p>
                            <a wire:navigate href="{{ $urlTulis }}" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                <i class="bi bi-plus-lg"></i><span>Tulis {{ strtolower($judul) }}</span>
                            </a>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th class="k-sedang">{{ $isBerita ? 'Kategori' : 'Kategori & klien' }}</th>
                                        <th>Keadaan</th>
                                        <th class="k-sedang">{{ $isBerita ? 'Tanggal terbit' : 'Tahun' }}</th>
                                        <th style="text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php
                                            $kunciKeadaanBaris = $isBerita ? $baris->keadaan() : ($baris->terbit ? 'terbit' : 'draf');
                                            [$kelasLencana, $ikonLencana, $teksLencana] = $lencanaKeadaan[$kunciKeadaanBaris];
                                            $urlSampul = $baris::urlGambar($baris->sampul);
                                            $punyaSampul = (bool) $urlSampul;
                                            $urlUbah = route('admin.asthana.'.$jenis.'.ubah', $baris->id);
                                            $kolomDua = $isBerita
                                                ? ($baris->kategori ?: '—')
                                                : collect([\App\Models\Asthana\Portofolio::KATEGORI[$baris->kategori] ?? $baris->kategori, $baris->klien])->filter()->implode(' · ');
                                            $kolomTiga = $isBerita
                                                ? ($baris->terbit_at ? $baris->terbit_at->locale('id')->translatedFormat('d M Y, H:i') : '—')
                                                : ($baris->tahun ?: '—');
                                            $unggulan = ! $isBerita && $baris->unggulan;
                                            $bolehAlih = ! $isBerita;
                                            $sudahTerbit = ! $isBerita && $baris->terbit;
                                        @endphp
                                        <tr wire:key="ast-tulisan-{{ $baris->id }}">
                                            <td>
                                                <a wire:navigate href="{{ $urlUbah }}" class="dsb-tabel-utama">
                                                    @if ($punyaSampul)
                                                        <img class="ast-sampul-mini" src="{{ $urlSampul }}" alt="" loading="lazy">
                                                    @else
                                                        <span class="ast-sampul-mini"><i class="bi bi-image"></i></span>
                                                    @endif
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-judul">{{ $baris->judul }}</span>
                                                        <span class="dsb-tabel-meta">
                                                            <span class="dsb-tabel-samar">/{{ $baris->slug }}</span>
                                                            @if ($unggulan)
                                                                <span class="dsb-lencana is-kuning"><i class="bi bi-star-fill"></i>Beranda</span>
                                                            @endif
                                                        </span>
                                                    </span>
                                                </a>
                                            </td>
                                            <td class="k-sedang">{{ $kolomDua }}</td>
                                            <td><span class="dsb-lencana {{ $kelasLencana }}"><i class="bi {{ $ikonLencana }}"></i>{{ $teksLencana }}</span></td>
                                            <td class="k-sedang">{{ $kolomTiga }}</td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-end">
                                                    @if ($bolehAlih)
                                                        <button type="button" class="dsb-tabel-btn" wire:click="alihTerbit({{ $baris->id }})"
                                                            title="{{ $sudahTerbit ? 'Jadikan draf' : 'Terbitkan' }}">
                                                            <i class="bi {{ $sudahTerbit ? 'bi-eye-slash' : 'bi-globe2' }}"></i>
                                                        </button>
                                                    @endif
                                                    <a wire:navigate href="{{ $urlUbah }}" class="dsb-tabel-btn" title="Ubah"><i class="bi bi-pencil"></i></a>
                                                    <button type="button" class="dsb-tabel-btn is-bahaya pcek-konfirmasi"
                                                        data-action="hapus" data-arg="{{ $baris->id }}"
                                                        data-title="Hapus {{ strtolower($judul) }}?"
                                                        data-text="{{ $baris->judul }} akan dihapus dari situs beserta gambarnya."
                                                        data-confirm="Ya, hapus" data-icon="warning" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div>{{ $daftar->links('vendor.pagination') }}</div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
