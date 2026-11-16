{{--
    Empat daftar berisian-cepat: tempat, fasilitas, yang tidak termasuk, dan
    titik jemput.

    Keempatnya bentuknya sama persis, jadi ditulis sekali lewat perulangan.
    Bedanya cuma judul, warna, dan contoh isiannya — dan menyalin markahnya
    empat kali berarti satu perbaikan harus diterapkan empat kali, yang
    selalu berakhir dengan satu yang terlewat.
--}}
@php
    $kartuDaftar = [
        ['destinasi', 'Tempat yang Dikunjungi', '#0284c7', 'bi-geo-alt',
            $saranDestinasi, $destinasi, 'destinasiBaru',
            'Tempat lain, misalnya Pantai Pulau Merah',
            'Yang ditulis di sini tampil sebagai daftar destinasi di halaman paket.'],

        ['fasilitas', 'Fasilitas yang Didapat', '#16a34a', 'bi-check2-circle',
            $saranFasilitas, $fasilitas, 'fasilitasBaru',
            'Fasilitas lain, misalnya Tiket kapal',
            'Yang sudah termasuk harga: transport, makan, tiket, pemandu.'],

        ['tidak_termasuk', 'Yang TIDAK Termasuk', '#dc2626', 'bi-x-circle',
            $saranTidakTermasuk, $tidakTermasuk, 'tidakTermasukBaru',
            'Misalnya Tiket masuk wisata',
            'Yang disiapkan sendiri oleh peserta. Tidak disebut di sini berarti tidak
             pernah disebut sama sekali — dan itu selalu berakhir jadi keluhan di lokasi.'],

        ['titik_jemput', 'Titik Jemput', '#7c3aed', 'bi-signpost-split',
            $saranTitikJemput, $titikJemputDaftar, 'titikJemputBaru',
            'Misalnya Klaten',
            'Dipilih dari daftar, bukan diketik ulang tiap kali: "Jogja" dan
             "Yogyakarta" yang bermaksud sama akan jadi dua titik berbeda di
             daftar peserta.'],
    ];
@endphp

<section class="dsb-bagian">
    <div class="dsb-rak">
        @foreach ($kartuDaftar as [$jenis, $judul, $warna, $ikon, $saran, $terpilih, $medanBaru, $contoh, $ketKartu])
            <div class="dsb-kartu k-6" wire:key="kartu-{{ $jenis }}">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: {{ $warna }}"><i class="bi {{ $ikon }}"></i></span>
                        <div>
                            <h3 class="dsb-kartu-judul">{{ $judul }}</h3>
                            <span class="dsb-kartu-sub">{{ count($terpilih) }} dipilih</span>
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    <p class="pb-ket">{{ $ketKartu }}</p>

                    @if ($terpilih)
                        <div class="pb-cip-deret mt-3">
                            @foreach ($terpilih as $urutan => $item)
                                <span class="pb-cip is-terpilih" wire:key="pilih-{{ $jenis }}-{{ $urutan }}">
                                    <span>{{ $item }}</span>
                                    <button type="button" class="pb-cip-buang"
                                        wire:click="buang('{{ $jenis }}', {{ $urutan }})"
                                        title="Keluarkan dari paket ini">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="pb-ket mt-3"><em>Belum ada yang dipilih.</em></p>
                    @endif

                    <hr class="my-3">

                    <p class="pb-ket">
                        Klik untuk memasukkannya ke paket ini. Tanda
                        <i class="bi bi-x-lg"></i> menghapusnya dari pilihan
                        <strong>semua</strong> paket, bukan cuma paket ini.
                    </p>

                    <div class="pb-cip-deret mt-2">
                        @forelse ($saran as $item)
                            @php $sudah = in_array($item['nama'], $terpilih, true); @endphp
                            <span class="pb-cip @if ($sudah) is-terpilih @endif"
                                wire:key="saran-{{ $jenis }}-{{ $item['id'] }}">
                                <button type="button" class="pb-cip-tombol"
                                    wire:click="jungkit('{{ $jenis }}', @js($item['nama']))"
                                    title="{{ $sudah ? 'Keluarkan dari paket ini' : 'Masukkan ke paket ini' }}">
                                    <i class="bi {{ $sudah ? 'bi-check2' : 'bi-plus' }}"></i>
                                    {{ $item['nama'] }}
                                </button>
                                <button type="button" class="pb-cip-buang pcek-konfirmasi"
                                    data-action="hapusSaran" data-arg="{{ $item['id'] }}"
                                    data-title="Hapus dari daftar pilihan?"
                                    data-text="{{ $item['nama'] }} tidak lagi muncul sebagai pilihan cepat. Paket yang sudah tersimpan tidak berubah."
                                    data-confirm="Ya, hapus" data-icon="warning"
                                    title="Hapus dari daftar pilihan">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </span>
                        @empty
                            <span class="pb-ket">Daftar masih kosong. Tambahkan lewat kotak di bawah.</span>
                        @endforelse
                    </div>

                    <div class="pb-tambah">
                        <input type="text" class="dsb-isian" wire:model="{{ $medanBaru }}"
                            wire:keydown.enter.prevent="tambah('{{ $jenis }}')"
                            placeholder="{{ $contoh }}">
                        <button type="button" class="dsb-tombol is-utama"
                            wire:click="tambah('{{ $jenis }}')" wire:loading.attr="disabled">
                            <i class="bi bi-plus-lg"></i><span>Tambah</span>
                        </button>
                    </div>
                    <span class="pb-ket">
                        Yang Anda tambahkan langsung masuk daftar pilihan, jadi paket
                        berikutnya tinggal diklik.
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</section>
