@section('title')
Atur Akses Akun || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*): satu kisi 12
         kolom, kartu putih datar berkepala ikon, isian .dsb-isian. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')

    {{-- Gaya khusus layar ini, ditulis INLINE: public/build di-gitignore, jadi
         berkas hasil build tidak pernah sampai ke server. --}}
    <style>
    .aa-lencana-kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-top: 7px; }

    /* ===== Kotak centang izin =====
       Berubin selebar kartu, bukan satu kolom. Dengan satu kolom, kartu
       selebar layar menyisakan dua pertiga lebar kosong di kanan tiap baris,
       dan 121 izin jadi gulungan sepanjang enam layar. */
    .aa-izin-grup { margin-bottom: 18px; }
    .aa-izin-grup:last-child { margin-bottom: 0; }
    .aa-izin-judul {
        display: flex; align-items: center; gap: 7px; margin-bottom: 9px;
        font-size: .72rem; font-weight: 800; letter-spacing: .05em;
        text-transform: uppercase; color: #94a3b8;
    }
    .aa-izin-judul::after {
        content: ""; flex: 1 1 auto; height: 1px; background: #f1f5f9;
    }
    .aa-izin-rak { display: grid; grid-template-columns: repeat(auto-fill, minmax(238px, 1fr)); gap: 8px; }

    .aa-izin {
        display: flex; align-items: flex-start; gap: 10px; min-width: 0;
        padding: 11px 13px; border: 1px solid var(--dsb-tepi); border-radius: 12px;
        background: #fff; cursor: pointer;
        transition: border-color .15s ease, background .15s ease;
    }
    .aa-izin:hover { border-color: #c4b5fd; background: #fdfcff; }
    .aa-izin input { margin-top: 2px; width: 16px; height: 16px; flex-shrink: 0; accent-color: #7c3aed; }
    .aa-izin-teks { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .aa-izin-nama { font-size: .84rem; font-weight: 700; color: var(--dsb-tinta); line-height: 1.35; }
    .aa-izin-ket { font-size: .75rem; color: var(--dsb-redup); line-height: 1.45; }

    /* Izin yang datang dari peran: terkunci dan diredupkan, tetapi TETAP
       terlihat. Daftar centang yang kosong terbaca sebagai "dia belum punya
       apa pun" padahal perannya sudah memberi sebelas izin. */
    .aa-izin.is-peran { background: #f8fafc; border-style: dashed; cursor: default; }
    .aa-izin.is-peran:hover { border-color: var(--dsb-tepi); background: #f8fafc; }
    .aa-izin.is-peran .aa-izin-nama { color: #64748b; }
    .aa-cap {
        align-self: flex-start; margin-left: auto; flex-shrink: 0;
        padding: 2px 7px; border-radius: 999px; font-size: .66rem; font-weight: 800;
        letter-spacing: .03em; text-transform: uppercase;
        background: #e2e8f0; color: #475569;
    }

    /* ===== Keping izin dari peran (kartu kanan) =====
       Tingginya dibatasi: peran Admin membawa 121 izin, dan kartu sepanjang
       itu mendorong seluruh isi halaman turun sebelum ada yang terbaca. */
    .aa-keping-rak { display: flex; flex-wrap: wrap; gap: 6px; max-height: 232px; overflow-y: auto; }
    .aa-keping {
        padding: 5px 10px; border-radius: 999px; font-size: .75rem; font-weight: 600;
        background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
    }

    .aa-ket { color: var(--dsb-redup); font-size: .82rem; line-height: 1.6; margin: 0 0 14px; }
    .aa-ket:last-child { margin-bottom: 0; }

    /* ===== Kaki lengket =====
       Pada daftar 121 izin, tombol simpan yang cuma ada di ujung bawah berarti
       menggulung enam layar untuk menyimpan satu centang. */
    .aa-kaki {
        position: sticky; bottom: 0; z-index: 3;
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 10px; margin: 16px -4px -4px; padding: 13px 15px;
        border-radius: 14px; background: #f8fafc; border: 1px solid var(--dsb-tepi);
    }
    .aa-kaki-hitung { color: var(--dsb-redup); font-size: .82rem; }
    .aa-kaki-hitung strong { color: var(--dsb-tinta); }
    .aa-kaki-aksi { display: flex; flex-wrap: wrap; gap: 8px; }
    @media (max-width: 575.98px) {
        .aa-kaki { flex-direction: column; align-items: stretch; }
        .aa-kaki-aksi > * { flex: 1 1 0; justify-content: center; }
    }

    .aa-kosong { text-align: center; padding: 28px 18px; color: var(--dsb-redup); font-size: .85rem; }
    </style>

    @php
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_roles');
        $awalan = collect(explode(' ', trim($nama)))->filter()->take(2)
            ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))->implode('');
    @endphp

    <div class="dsb aa-akses">

        {{-- ============ KEPALA ============ --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">{{ $nama }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-envelope me-1"></i>{{ $email }}</span>
                    <span class="d-block aa-lencana-kepala">
                        <span class="dsb-lencana is-ungu">
                            <i class="bi bi-person-badge"></i>
                            {{ $peranTersedia->firstWhere('id', $peranId)?->name ?? 'Belum punya peran' }}
                        </span>
                        <span class="dsb-lencana is-abu">
                            <i class="bi bi-unlock"></i>
                            {{ count($izinDariPeran) }} izin dari peran
                        </span>
                        @if ($jumlahPribadi > 0)
                            <span class="dsb-lencana is-kuning">
                                <i class="bi bi-key-fill"></i>
                                {{ $jumlahPribadi }} izin khusus dia sendiri
                            </span>
                        @endif
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.account.role') }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
                @if ($bolehUbah)
                    <button type="button" class="dsb-tombol is-utama" wire:click="simpan"
                        wire:loading.attr="disabled" wire:target="simpan">
                        <i class="bi bi-check-lg"></i><span>Simpan</span>
                    </button>
                @endif
            </div>
        </header>

        {{-- ============ PERAN & APA YANG SUDAH DIDAPAT ============

             Berdampingan pada satu kisi: yang kiri keputusan yang diambil
             admin, yang kanan akibatnya. Dulu keduanya bertumpuk di jendela
             sembul, dan akibat pilihannya cuma berupa angka. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-5">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-person-badge"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Perannya di Kantor</h2>
                            <span class="dsb-kartu-sub">Menentukan sebagian besar yang bisa dia buka</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi is-tegak">
                    <div class="dsb-medan">
                        <label class="dsb-label" for="aa-peran">Peran</label>
                        <select id="aa-peran" class="dsb-isian @error('peranId') is-galat @enderror"
                            wire:model.live="peranId" @disabled(! $bolehUbah)>
                            <option value="">— belum dipilih —</option>
                            @foreach ($peranTersedia as $peran)
                                <option value="{{ $peran->id }}" @selected((int) $peranId === (int) $peran->id)>
                                    {{ $peran->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('peranId')
                            <span class="dsb-galat">{{ $message }}</span>
                        @enderror
                    </div>

                    <p class="aa-ket" style="margin-top: 14px">
                        Mengganti peran mengubah akses <strong>semua</strong> yang berperan sama.
                        Kalau yang Anda maksud cuma satu orang, jangan ganti peran — beri saja
                        izin khusus di bawah.
                    </p>
                </div>
            </div>

            <div class="dsb-kartu k-7">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-unlock"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Sudah Bisa Dibuka dari Perannya</h2>
                            <span class="dsb-kartu-sub">Didapat otomatis — tidak perlu diberikan lagi</span>
                        </span>
                    </div>
                    <span class="dsb-lencana is-hijau">{{ count($izinDariPeran) }} izin</span>
                </div>

                {{-- TIDAK is-tegak: keterangannya mengalir tepat di bawah kepingnya.

                     Sempat ditambat ke dasar kartu supaya kaki kedua kartu rata;
                     yang terjadi justru lubang pindah ke tengah — antara keping dan
                     keterangannya — dan lubang di tengah terbaca seperti ada yang
                     gagal dimuat, sedangkan sisa ruang di dasar kartu terbaca
                     sebagai jarak kartu biasa. --}}
                <div class="dsb-kartu-isi">
                    @if ($izinPeran->isEmpty())
                        <p class="aa-kosong">
                            Peran ini belum diberi izin apa pun, jadi semua yang bisa dia buka
                            harus diberikan satu per satu di bawah.
                        </p>
                    @else
                        <div class="aa-keping-rak">
                            @foreach ($izinPeran as $izin)
                                <span class="aa-keping">{{ $izin->display_name ?: $izin->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    <p class="aa-ket" style="margin-top: 14px">
                        Mau menambah atau mengurangi daftar ini? Itu diubah di
                        <strong>Pengaturan Role</strong>, dan berlaku untuk
                        <strong>semua</strong> yang berperan sama — bukan cuma {{ $nama }}.
                    </p>
                </div>
            </div>
        </div>

        {{-- ============ IZIN KHUSUS ORANG INI ============ --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-key-fill"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Izin Khusus untuk {{ $nama }}</h2>
                            <span class="dsb-kartu-sub">Hanya dia yang dapat — rekan seperannya tidak ikut</span>
                        </span>
                    </div>
                    @if ($jumlahPribadi > 0)
                        <span class="dsb-lencana is-kuning">{{ $jumlahPribadi }} dicentang</span>
                    @endif
                </div>

                <div class="dsb-kartu-isi">
                    <p class="aa-ket">
                        Centang yang boleh dia buka di luar perannya. Yang kotaknya
                        <strong>abu-abu bergaris putus</strong> sudah didapat dari perannya —
                        tidak perlu, dan memang tidak bisa, dicentang di sini.
                    </p>

                    {{-- Saringan: 121 izin terlalu panjang untuk digulir setiap
                         kali memberi satu izin. --}}
                    <div class="dsb-medan" style="max-width: 420px; margin-bottom: 16px">
                        <label class="dsb-label" for="aa-cari">Cari izin</label>
                        <div class="dsb-cari">
                            <i class="bi bi-search"></i>
                            <input id="aa-cari" type="search" class="dsb-isian"
                                wire:model.live.debounce.300ms="cariIzin"
                                placeholder="Ketik misalnya: orcha, kesehatan, gaji">
                        </div>
                    </div>

                    @forelse ($semuaIzin as $grup => $daftar)
                        <div class="aa-izin-grup">
                            <span class="aa-izin-judul">{{ ucfirst($grup ?: 'lainnya') }}</span>
                            <div class="aa-izin-rak">
                                @foreach ($daftar as $izin)
                                    @php
                                        $dariPeran = in_array((int) $izin->id, $izinDariPeran, true);
                                        /*
                                         | Centangnya DIGAMBAR dari data, bukan diserahkan ke Livewire.
                                         |
                                         | `wire:model` pada kotak centang hanya menyalin DOM →
                                         | properti saat orangnya menekan; tidak pernah sebaliknya.
                                         | Tanpa baris ini, izin pribadi yang sudah tersimpan tampil
                                         | KOSONG tiap kali layarnya dibuka ulang — dan admin
                                         | membacanya sebagai "dia belum diberi akses apa-apa".
                                         |
                                         | Dibandingkan sebagai int: nilai kotak centang berupa teks,
                                         | dan in_array ketat atas "110" vs 110 memulangkan false.
                                         */
                                        $sudah = $dariPeran || in_array(
                                            (int) $izin->id,
                                            array_map('intval', $izinTambahan),
                                            true,
                                        );
                                    @endphp
                                    <label class="aa-izin {{ $dariPeran ? 'is-peran' : '' }}">
                                        {{-- Atributnya sengaja SATU baris: penjaganya memeriksa
                                             potongan HTML apa adanya, dan baris baru di antara
                                             atribut membuat pemeriksaan itu merah tanpa ada
                                             yang rusak. --}}
                                        <input type="checkbox" value="{{ $izin->id }}" wire:model="izinTambahan" @checked($sudah) @disabled($dariPeran || ! $bolehUbah)>
                                        <span class="aa-izin-teks">
                                            <span class="aa-izin-nama">{{ $izin->display_name ?: $izin->name }}</span>
                                            @if ($izin->description)
                                                <span class="aa-izin-ket">{{ $izin->description }}</span>
                                            @endif
                                        </span>
                                        @if ($dariPeran)
                                            <span class="aa-cap">dari peran</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="aa-kosong mb-0">
                            Tidak ada izin yang cocok dengan "{{ $cariIzin }}".
                            Coba kata lain, misalnya nama menunya.
                        </p>
                    @endforelse

                    <div class="aa-kaki">
                        <span class="aa-kaki-hitung">
                            <strong>{{ $jumlahPribadi }}</strong> izin khusus dicentang untuk
                            {{ $nama }}, di luar <strong>{{ count($izinDariPeran) }}</strong> dari perannya
                        </span>

                        <div class="aa-kaki-aksi">
                            <a href="{{ route('admin.account.role') }}" wire:navigate
                                class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                <i class="bi bi-x-lg"></i><span>Batal</span>
                            </a>
                            @if ($bolehUbah)
                                <button type="button" class="dsb-tombol is-utama" wire:click="simpan"
                                    wire:loading.attr="disabled" wire:target="simpan">
                                    <span wire:loading.remove wire:target="simpan" class="orcha-ikon-teks">
                                        <i class="bi bi-check-lg"></i> Simpan akses
                                    </span>
                                    <span wire:loading wire:target="simpan" class="orcha-ikon-teks">
                                        <span class="dsb-putar is-kecil"></span> Menyimpan…
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
