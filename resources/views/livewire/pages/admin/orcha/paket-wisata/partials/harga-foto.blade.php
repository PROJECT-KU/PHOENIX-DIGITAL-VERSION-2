{{-- Harga di kiri, foto di kanan: dua hal yang paling sering diubah bersama
     saat paket disiapkan untuk dijual. --}}
<section class="dsb-bagian">
    <div class="dsb-rak">
        <div class="dsb-kartu k-6">
            <div class="dsb-kartu-kepala">
                <div class="dsb-kartu-kepala-kiri">
                    <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-cash-coin"></i></span>
                    <div>
                        <h3 class="dsb-kartu-judul">Harga</h3>
                        <span class="dsb-kartu-sub">Untungnya terlihat saat masih bisa diperbaiki</span>
                    </div>
                </div>
            </div>

            <div class="dsb-kartu-isi">
                <div class="pb-kisi">
                    <div class="dsb-medan">
                        <label class="dsb-label" for="pb-harga">
                            Harga jual <span class="dsb-wajib">*</span>
                        </label>
                        <div class="orcha-rupiah">
                            <input id="pb-harga" type="text" inputmode="numeric" class="orcha-uang dsb-isian"
                                wire:model.blur="hargaTeks" value="{{ $hargaTeks }}" placeholder="1.430.000">
                        </div>
                        @error('harga') <span class="pb-galat">{{ $message }}</span> @enderror
                    </div>

                    <div class="dsb-medan">
                        <label class="dsb-label" for="pb-modal">Modal per orang</label>
                        <div class="orcha-rupiah">
                            <input id="pb-modal" type="text" inputmode="numeric" class="orcha-uang dsb-isian"
                                wire:model.blur="hargaModalTeks" value="{{ $hargaModalTeks }}"
                                placeholder="1.400.000">
                        </div>
                        @error('hargaModal') <span class="pb-galat">{{ $message }}</span> @enderror
                    </div>
                </div>

                <p class="pb-ket mt-2">
                    Modal = biaya kita sendiri untuk satu peserta: transport, penginapan,
                    tiket, dan tim. Kosongkan kalau belum dihitung — jangan diisi nol.
                </p>

                {{-- Untung per peserta langsung terlihat di sini, bukan setelah tutup
                     buku. Paket yang ternyata dijual di bawah modal ketahuan saat masih
                     bisa diperbaiki. --}}
                @php $marginKartu = $this->marginPerOrang(); @endphp
                <div class="orcha-margin {{ $marginKartu === null ? 'kosong' : ((int) $marginKartu < 0 ? 'rugi' : '') }} mt-3">
                    @if ($marginKartu === null)
                        <i class="bi bi-question-circle"></i>
                        <span>Untungnya belum bisa dihitung, karena modalnya belum diisi.</span>
                    @elseif ($marginKartu < 0)
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>
                            <strong>Rugi Rp {{ number_format(abs($marginKartu), 0, ',', '.') }}</strong>
                            per peserta. Harga jualnya di bawah modal.
                        </span>
                    @else
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>
                            Untung <strong>Rp {{ number_format($marginKartu, 0, ',', '.') }}</strong>
                            per peserta
                            @if ($this->marginPersen() !== null)
                                <span class="text-muted">({{ $this->marginPersen() }}% dari harga jual)</span>
                            @endif
                        </span>
                    @endif
                </div>

                <div class="pb-kisi mt-3">
                    <div class="dsb-medan">
                        <label class="dsb-label" for="pb-harga-asli">Harga sebelum diskon</label>
                        <div class="orcha-rupiah">
                            <input id="pb-harga-asli" type="text" inputmode="numeric" class="orcha-uang dsb-isian"
                                wire:model.blur="hargaAsliTeks" value="{{ $hargaAsliTeks }}"
                                placeholder="1.700.000">
                        </div>
                        <span class="pb-ket">Kosongkan kalau tidak ada harga coret.</span>
                    </div>

                    <div class="dsb-medan">
                        <label class="dsb-label" for="pb-diskon">
                            Diskon (%)
                            @if ($diskonOtomatis)
                                <span class="dsb-lencana is-abu">otomatis</span>
                            @else
                                <button type="button" class="pb-cip-tombol" wire:click="hitungDiskon"
                                    title="Kembalikan ke hasil hitungan">
                                    <i class="bi bi-arrow-repeat"></i> hitung ulang
                                </button>
                            @endif
                        </label>
                        <input id="pb-diskon" type="number" min="0" max="100" class="dsb-isian"
                            wire:model.blur="diskonPersen" value="{{ $diskonPersen }}">
                        <span class="pb-ket">
                            {{ $diskonOtomatis
                                ? 'Dihitung sendiri dari selisih kedua harga, dibulatkan ke bawah.'
                                : 'Anda tulis sendiri, tidak lagi ikut selisih harga.' }}
                        </span>
                    </div>
                </div>

                <div class="form-check mt-3">
                    {{-- @checked() WAJIB: wire:model tidak menulis atribut checked
                         saat halamannya pertama digambar. --}}
                    <input class="form-check-input" type="checkbox" id="pb-terbaik"
                        wire:model="pilihanTerbaik" @checked($pilihanTerbaik)>
                    <label class="form-check-label small" for="pb-terbaik">
                        Tandai sebagai pilihan terbaik di website
                    </label>
                </div>
            </div>
        </div>

        {{-- ================== FOTO ================== --}}
        <div class="dsb-kartu k-6">
            <div class="dsb-kartu-kepala">
                <div class="dsb-kartu-kepala-kiri">
                    <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-images"></i></span>
                    <div>
                        <h3 class="dsb-kartu-judul">Foto</h3>
                        <span class="dsb-kartu-sub">Satu sampul untuk kartu, sisanya untuk galeri</span>
                    </div>
                </div>
            </div>

            <div class="dsb-kartu-isi">
                @php
                    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
                    $alamatFoto = fn ($jalur) => str_starts_with((string) $jalur, 'http')
                        ? $jalur
                        : $asalOrcha.$jalur;
                    $pratinjauSampul = $gambar
                        ? \App\Support\PratinjauUnggahan::url($gambar)
                        : ($gambarLama ? $alamatFoto($gambarLama) : null);
                @endphp

                <label class="dsb-label" for="pb-sampul">Foto sampul</label>
                <p class="pb-ket">
                    Yang tampil di kartu paket, di halaman daftar website. Ukuran paling pas
                    <strong>1200 × 750</strong> piksel (paling kecil 800 × 500), maksimal 4 MB.
                </p>

                @if ($pratinjauSampul)
                    <div class="pb-sampul my-2">
                        <img src="{{ $pratinjauSampul }}" alt="Pratinjau kartu">
                        <span class="pb-sampul-label">Kira-kira begini di kartu</span>
                    </div>
                @endif

                <input id="pb-sampul" type="file" class="dsb-isian" wire:model="gambar" accept="image/*">
                @error('gambar') <span class="pb-galat">{{ $message }}</span> @enderror
                <div wire:loading wire:target="gambar" class="pb-ket mt-1">Sedang mengunggah foto…</div>

                {{-- ===== Galeri =====

                     Satu foto sampul tidak cukup untuk menjual trip tiga hari, dan
                     Galeri Perjalanan yang sudah ada adalah galeri PERUSAHAAN —
                     bukan galeri paket ini. --}}
                <hr class="my-3">

                <label class="dsb-label" for="pb-galeri">
                    Galeri paket
                    <span class="dsb-label-ket">{{ count($galeriLama) + count($galeriBaru) }} foto</span>
                </label>
                <p class="pb-ket">
                    Tampil sebagai galeri di halaman paket. Paling banyak 12 foto, maksimal 8 MB per foto.
                </p>

                @if ($galeriLama || $galeriBaru)
                    <div class="pb-galeri my-2">
                        @foreach ($galeriLama as $urutan => $foto)
                            <div class="pb-galeri-kotak" wire:key="gl-{{ $urutan }}-{{ md5($foto) }}">
                                <img src="{{ $alamatFoto($foto) }}" alt="Foto galeri {{ $urutan + 1 }}">
                                {{-- Dicoret dari daftar saja; berkasnya baru dihapus di Orcha
                                     saat paketnya disimpan. Menghapusnya seketika berarti
                                     membatalkan borang meninggalkan foto yang sudah hilang. --}}
                                <button type="button" class="pb-galeri-buang"
                                    wire:click="buangGaleriLama({{ $urutan }})"
                                    title="Keluarkan foto ini dari galeri">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        @endforeach

                        @foreach ($galeriBaru as $urutan => $berkas)
                            <div class="pb-galeri-kotak" wire:key="gb-{{ $urutan }}">
                                <img src="{{ \App\Support\PratinjauUnggahan::url($berkas) }}" alt="Foto baru {{ $urutan + 1 }}">
                                <button type="button" class="pb-galeri-buang"
                                    wire:click="buangGaleriBaru({{ $urutan }})"
                                    title="Batalkan foto ini">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <input id="pb-galeri" type="file" class="dsb-isian" wire:model="galeriBaru"
                    accept="image/*" multiple>
                @error('galeriBaru') <span class="pb-galat">{{ $message }}</span> @enderror
                @error('galeriBaru.*') <span class="pb-galat">{{ $message }}</span> @enderror
                <div wire:loading wire:target="galeriBaru" class="pb-ket mt-1">Sedang mengunggah foto…</div>
            </div>
        </div>
    </div>
</section>
