{{--
    Medan formulir vendor, dipakai dua layar: daftar dan rincian.

    Ditarik ke partial — bukan disalin — karena dua formulir yang menyimpan
    ke tempat yang sama tetapi bisa berbeda isinya adalah cacat yang baru
    ketahuan saat salah satunya sudah menyimpan nilai yang tidak diterima
    yang lain.

    Variabel: $isian, $aktif, $jenisKini, $jenisAsing, $pilihanJenis,
    $provinsiAda, $rupaDari — disiapkan blok @php layar pemanggilnya.
--}}
                    <div class="vd-medan-rak">
                        <div class="dsb-medan">
                            <label class="dsb-label" for="vd-nama">Nama vendor <span class="dsb-wajib">*</span></label>
                            <input id="vd-nama" type="text" wire:model="isian.nama" maxlength="120"
                                class="dsb-isian @error('isian.nama') is-galat @enderror"
                                placeholder="PO Sumber Jaya">
                            @error('isian.nama')<span class="dsb-galat">{{ $message }}</span>@enderror
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="vd-kontak">
                                Kontak
                                <span class="dsb-label-ket">boleh sekalian nama orangnya</span>
                            </label>
                            <input id="vd-kontak" type="text" wire:model="isian.kontak" maxlength="120"
                                class="dsb-isian @error('isian.kontak') is-galat @enderror"
                                placeholder="081234000111 (Pak Budi)">
                            @error('isian.kontak')<span class="dsb-galat">{{ $message }}</span>@enderror
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="vd-provinsi">
                                Melayani wilayah
                                <span class="dsb-label-ket">kosong = di mana saja</span>
                            </label>
                            <input id="vd-provinsi" type="text" wire:model="isian.provinsi" maxlength="80"
                                class="dsb-isian" placeholder="DI Yogyakarta"
                                list="vd-daftar-provinsi">
                            <datalist id="vd-daftar-provinsi">
                                @foreach ($provinsiAda as $p)
                                    <option value="{{ $p }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="dsb-medan is-lebar">
                            <label class="dsb-label">
                                Jenis
                                <span class="dsb-label-ket">memasok apa</span>
                            </label>
                            {{-- Pilihan cepat, bukan kotak terkunci: vendor nyata
                                 tidak selalu muat di lima kotak, dan menolak yang
                                 keenam hanya membuat admin mengetiknya di kolom
                                 lain yang bukan tempatnya. --}}
                            <div class="vd-jenis">
                                @foreach ($pilihanJenis as $j)
                                    @php([$warnaJ, $ikonJ] = $rupaDari($j))
                                    <button type="button" wire:click="pakaiJenis('{{ $j }}')"
                                        class="vd-jenis-chip {{ $jenisKini === $j ? 'is-aktif' : '' }}"
                                        style="--c: {{ $warnaJ }}">
                                        <i class="bi {{ $ikonJ }}"></i>{{ $j }}
                                    </button>
                                @endforeach
                            </div>
                            {{-- Di luar deret chip, bukan sejajar dengannya: ia
                                 berbeda fungsi, dan berbaris bersama membuatnya
                                 terbaca seperti chip keenam. --}}
                            <div class="vd-jenis-lain">
                                <span class="dsb-kartu-sub">atau</span>
                                <input type="text" wire:model.live.debounce.400ms="isian.jenis" maxlength="30"
                                    class="dsb-isian vd-jenis-isi" placeholder="ketik jenis lain">
                            </div>
                            @if ($jenisAsing)
                                <span class="dsb-kartu-sub vd-jenis-asing">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>{{ $jenisKini }}</strong> di luar daftar baku — tetap tersimpan, dan ikut jadi pilihan saringan.
                                </span>
                            @endif
                        </div>

                        <div class="dsb-medan is-lebar">
                            <label class="dsb-label" for="vd-catatan">
                                Catatan
                                <span class="dsb-label-ket">tidak pernah ikut ke PDF pelanggan</span>
                            </label>
                            <input id="vd-catatan" type="text" wire:model="isian.catatan" maxlength="300"
                                class="dsb-isian" placeholder="Alamat pool, syarat DP, jam operasional…">
                        </div>
                    </div>

                    <div class="vd-sakelar">
                        <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }}">
                            <span class="rupa"><i class="bi {{ $aktif ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i></span>
                            <span class="isi">
                                <span class="judul">{{ $aktif ? 'Aktif' : 'Dimatikan' }}</span>
                                <span class="ket">
                                    {{ $aktif
                                        ? 'Muncul sebagai pilihan saat mengisi master harga.'
                                        : 'Tersimpan, tetapi tidak ditawarkan lagi. Harga yang sudah memakainya tetap menunjuk kontaknya.' }}
                                </span>
                            </span>
                            <span class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                            </span>
                        </label>
                    </div>
