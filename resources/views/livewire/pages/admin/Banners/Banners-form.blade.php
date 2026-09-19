<div>
    @php
        $edit = $mode !== 'create';
        $gambarBaru = $gambar && is_object($gambar) && ! $errors->has('gambar');
        $urlBaru = $gambarBaru ? \App\Support\PratinjauUnggahan::url($gambar) : null;
        $urlLama = $existingImage ? asset('storage/img/banners/'.$existingImage) : null;
        $urlTampil = $urlBaru ?: $urlLama;
        $panjangJudul = mb_strlen(trim((string) $judul));
        $opsiTautan = [
            '' => ['Halaman Belanja', 'bi-shop'],
            'produk' => ['Produk tertentu', 'bi-box-seam'],
            'member' => ['Halaman Member', 'bi-person-badge'],
            'bundling' => ['Paket Bundling', 'bi-boxes'],
            'lain' => ['Tautan lain', 'bi-link-45deg'],
        ];
        $statusOpsi = [
            'active' => ['Aktif', 'bi-broadcast', '#16a34a', 'Tampil di beranda sesuai jadwal di bawah'],
            'non-active' => ['Non-aktif', 'bi-eye-slash', '#64748b', 'Disembunyikan, apa pun jadwalnya'],
        ];
    @endphp

    <form wire:submit.prevent="save" class="bn-form-tata">
        {{-- ================== KOLOM UTAMA ================== --}}
        <div class="bn-form-utama">
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bn-form-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-card-heading"></i></span>
                        <div>
                            <b>Informasi Banner</b>
                            <span>Judul & deskripsi tampil sebagai teks besar di beranda, di samping gambar.</span>
                        </div>
                    </div>

                    <div class="bn-medan dsb-medan">
                        <label class="dsb-label" for="bn-judul">Judul Banner <span class="text-danger">*</span></label>
                        <input id="bn-judul" type="text" wire:model.live.debounce.400ms="judul" class="dsb-isian" placeholder="Contoh: Promo Diskon 50%">
                        <small class="bn-bantu bn-hitung-judul {{ 60 < $panjangJudul ? 'is-lebih' : '' }}">
                            <span>Tampil sebagai judul besar di beranda — singkat & menjual. Kata setelah koma terakhir diwarnai jingga.</span>
                            <b>{{ $panjangJudul }}/60</b>
                        </small>
                        @error('judul') <small class="bn-galat">{{ $message }}</small> @enderror
                    </div>

                    <div class="bn-medan dsb-medan">
                        <label class="dsb-label" for="bn-desk">Deskripsi</label>
                        <textarea id="bn-desk" wire:model.live.debounce.400ms="deskripsi" rows="4" class="dsb-isian bn-desk-isian" placeholder="Kalimat ajakan di bawah judul (opsional)"></textarea>
                        <small class="bn-bantu">Tampil sebagai paragraf di bawah judul di beranda. Satu–dua kalimat cukup.</small>
                        @error('deskripsi') <small class="bn-galat">{{ $message }}</small> @enderror
                    </div>

                    <div class="bn-medan dsb-medan">
                        <span class="dsb-label">Status <span class="text-danger">*</span></span>
                        <div class="bn-status-pilih" role="radiogroup" aria-label="Status banner">
                            @foreach ($statusOpsi as $nilai => [$label, $ikon, $warna, $ket])
                                <label class="bn-status-opsi {{ $status === $nilai ? 'is-pilih' : '' }}" style="--c: {{ $warna }}">
                                    <input type="radio" wire:model.live="status" value="{{ $nilai }}">
                                    <span><i class="bi {{ $ikon }}"></i></span>
                                    <span><b>{{ $label }}</b><small>{{ $ket }}</small></span>
                                </label>
                            @endforeach
                        </div>
                        @error('status') <small class="bn-galat">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>

            {{-- Tujuan klik: gambar banner & tombol "Belanja Sekarang" di beranda. --}}
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bn-form-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-cursor-fill"></i></span>
                        <div>
                            <b>Tujuan Klik</b>
                            <span>Ke mana pembeli dibawa saat mengeklik banner atau tombol "Belanja Sekarang".</span>
                        </div>
                    </div>
                    <div class="bn-tautan-pilih" role="radiogroup" aria-label="Tujuan klik">
                        @foreach ($opsiTautan as $nilai => [$label, $ikon])
                            <label class="bn-tautan-opsi {{ $tautanJenis === $nilai ? 'is-pilih' : '' }}">
                                <input type="radio" wire:model.live="tautanJenis" value="{{ $nilai }}">
                                <i class="bi {{ $ikon }}"></i><span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if ($tautanJenis === 'produk')
                        <div class="dsb-medan" style="margin-top: 12px;">
                            <label class="dsb-label" for="bn-produk">Produk</label>
                            <select id="bn-produk" class="dsb-isian" wire:model.live="tautanProduk">
                                <option value="">— Pilih produk —</option>
                                @foreach ($daftarProduk as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_akun }}</option>
                                @endforeach
                            </select>
                            @error('tautanProduk') <small class="bn-galat">{{ $message }}</small> @enderror
                        </div>
                    @elseif ($tautanJenis === 'lain')
                        <div class="dsb-medan" style="margin-top: 12px;">
                            <label class="dsb-label" for="bn-tautan">Tautan</label>
                            <input id="bn-tautan" type="text" class="dsb-isian" wire:model.live.debounce.400ms="tautanLain" placeholder="/shop?kategori=ai atau https://…">
                            <small class="bn-bantu">Awali dengan "/" untuk halaman di situs ini, atau "https://".</small>
                            @error('tautanLain') <small class="bn-galat">{{ $message }}</small> @enderror
                        </div>
                    @endif
                </div>
            </div>

            {{-- Jadwal tayang (opsional). Kosong = tanpa batas waktu. --}}
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bn-form-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-calendar-range"></i></span>
                        <div>
                            <b>Jadwal Tayang <span class="text-muted fw-normal">(opsional)</span></b>
                            <span>Banner muncul & hilang sendiri sesuai jadwal. Kosongkan untuk tayang terus.</span>
                        </div>
                    </div>

                    <div class="bn-jadwal-isian">
                        <div class="dsb-medan">
                            <label class="dsb-label" for="bn-mulai">Mulai tayang</label>
                            <input id="bn-mulai" type="datetime-local" wire:model.live="mulai_tayang" class="dsb-isian">
                            <small class="bn-bantu">Kosong = langsung tayang begitu Aktif.</small>
                            @error('mulai_tayang') <small class="bn-galat">{{ $message }}</small> @enderror
                        </div>
                        <div class="dsb-medan">
                            <label class="dsb-label" for="bn-selesai">Selesai tayang</label>
                            <input id="bn-selesai" type="datetime-local" wire:model.live="selesai_tayang" class="dsb-isian">
                            <small class="bn-bantu">Kosong = tayang seterusnya.</small>
                            @error('selesai_tayang') <small class="bn-galat">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="bn-cepat">
                        <span>Isi cepat:</span>
                        <button type="button" wire:click="aturJadwal('sekarang')">Mulai sekarang</button>
                        <button type="button" wire:click="aturJadwal('7hari')">7 hari</button>
                        <button type="button" wire:click="aturJadwal('30hari')">30 hari</button>
                        <button type="button" wire:click="aturJadwal('akhirbulan')">Sampai akhir bulan</button>
                        <button type="button" wire:click="aturJadwal('kosong')">Tanpa batas</button>
                    </div>

                    <div class="bn-catatan">
                        <i class="bi bi-info-circle"></i>
                        <span>Jadwal hanya berlaku bila status <b>Aktif</b>. Status <b>Non-aktif</b> tetap menyembunyikan banner, apa pun jadwalnya.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== KOLOM SAMPING ================== --}}
        <aside class="bn-form-samping">
            {{-- Pratinjau mini hero beranda: judul (dengan sorotan jingga yang
                 sama — Banners::judulBeraksen), deskripsi, tombol, dan gambar. --}}
            <div class="bn-pratinjau" aria-label="Pratinjau di beranda">
                <span class="bn-pratinjau-label"><i class="bi bi-display"></i> Pratinjau di beranda</span>
                <div class="bn-pratinjau-isi">
                    <div class="bn-pratinjau-teks">
                        <p class="bn-pratinjau-judul">{!! \App\Models\Banners::judulBeraksen($judul ?: 'Judul banner Anda, tampil di sini', 'bn-aksen') !!}</p>
                        @if ($deskripsi)
                            <p class="bn-pratinjau-desk">{{ $deskripsi }}</p>
                        @endif
                        <span class="bn-pratinjau-tombol">Belanja Sekarang <i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="bn-pratinjau-gambar">
                        @if ($urlTampil)
                            <img src="{{ $urlTampil }}" alt="">
                        @else
                            <i class="bi bi-image"></i>
                        @endif
                    </div>
                </div>
            </div>

            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bn-form-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: #ea580c"><i class="bi bi-image"></i></span>
                        <div>
                            <b>Gambar Banner @if (! $edit)<span class="text-danger">*</span>@endif</b>
                            <span>Persegi (1:1), mis. 1254×1254 · JPG/PNG maks. 5 MB</span>
                        </div>
                    </div>

                    {{-- Persegi: sama dengan potongan banner di beranda. --}}
                    <div id="bnZona" class="bn-unggah {{ $urlTampil ? 'is-isi' : '' }} {{ $errors->has('gambar') ? 'is-galat' : '' }}">
                        <input type="file" id="gambarInput" wire:model="gambar" accept="image/png, image/jpeg, image/jpg" aria-label="Pilih gambar banner">
                        @if ($urlTampil)
                            <img src="{{ $urlTampil }}" alt="Pratinjau banner">
                            <span class="bn-unggah-ganti"><i class="bi bi-arrow-repeat"></i>{{ $gambarBaru ? 'Gambar baru · klik untuk ganti' : 'Klik untuk ganti gambar' }}</span>
                        @else
                            <span class="bn-unggah-ikon"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                            <b>Klik atau seret gambar ke sini</b>
                            <small>Otomatis diperkecil ke WebP saat disimpan</small>
                        @endif
                    </div>
                    <div class="bn-unggah-muat" wire:loading.flex wire:target="gambar">
                        <span class="dsb-putar is-kecil"></span> Mengunggah gambar…
                    </div>
                    @error('gambar') <small class="bn-galat">{{ $message }}</small> @enderror
                    @if ($edit)
                        <small class="bn-bantu">Kosongkan bila tidak ingin mengganti gambar.</small>
                    @endif
                </div>
            </div>

            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <span class="bn-kicker">{{ $edit ? 'Simpan perubahan' : 'Simpan banner' }}</span>
                    <button type="submit" class="dsb-tombol is-utama bn-simpan" wire:loading.attr="disabled" wire:target="save,gambar">
                        <span wire:loading.remove wire:target="save" class="bn-isi-tombol"><i class="bi bi-check2-circle"></i><span>{{ $edit ? 'Simpan Perubahan' : 'Simpan Banner' }}</span></span>
                        <span wire:loading.inline-flex wire:target="save" class="bn-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyimpan…</span></span>
                    </button>
                    <ul class="bn-tips">
                        <li>Taruh teks penting di tengah — tepi gambar bisa terpotong di layar kecil.</li>
                        <li>Status di daftar menunjukkan keadaan <b>nyata</b> di beranda (Tayang / Terjadwal / Berakhir).</li>
                    </ul>
                </div>
            </div>
        </aside>
    </form>

    <script>
        // Validasi gambar sebelum diunggah. Dipasang SEKALI di dokumen (capture):
        // versi lama memakai DOMContentLoaded, yang tidak berjalan saat halaman
        // dibuka lewat wire:navigate — pengecekannya diam-diam tidak aktif.
        if (!window.__bannerUnggahTerpasang) {
            window.__bannerUnggahTerpasang = true;
            const peringatan = (judul, teks) => {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true,
                    icon: 'error', title: judul, text: teks, background: 'rgba(255, 255, 255, 0.95)',
                    customClass: { popup: 'swal-glossy-toast', title: 'swal-toast-title' },
                });
            };
            document.addEventListener('change', function(e) {
                if (!e.target || e.target.id !== 'gambarInput') return;
                const file = e.target.files[0];
                if (!file) return;
                const tolak = (judul, teks) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.target.value = '';
                    peringatan(judul, teks);
                };
                if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                    tolak('Format tidak didukung', 'Gunakan gambar JPG atau PNG.');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    tolak('Ukuran terlalu besar', 'Maksimal ukuran gambar adalah 5 MB.');
                }
            }, true);
        }
    </script>
</div>
