@section('title')
{{ $tulisanId ? 'Ubah' : 'Tulis' }} {{ $judulJenis }} · Situs Asthana || lemon
@stop

@php
    $isPorto = $jenis === 'portofolio';
    $isBerita = ! $isPorto;
    $baru = ! $tulisanId;
    $adaSampulBaru = (bool) $sampul;
    $urlSampul = $adaSampulBaru ? \App\Support\PratinjauUnggahan::url($sampul) : $urlSampulLama;
    $adaSampul = (bool) $urlSampul;
    $adaGaleri = count($urlGaleri) + count($galeriBaru) !== 0;
    $jalurSitus = $isPorto ? '/portofolio/detail.html?slug=' : '/berita/baca.html?slug=';
    $awalanAlamat = ($situs ?: 'situs-asthana').$jalurSitus;
    $isJadwal = $keadaanBerita === 'jadwal';
    $urlKembali = route('admin.asthana.'.$jenis);
@endphp

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.asthana.partials.gaya')

    <form class="dsb" wire:submit="simpan">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">{{ $baru ? 'Tulis' : 'Ubah' }} {{ strtolower($judulJenis) }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><a wire:navigate href="{{ $urlKembali }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke daftar {{ strtolower($judulJenis) }}</a></span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ $urlKembali }}" class="dsb-tombol is-lembut"><span>Batal</span></a>
                <button type="submit" class="dsb-tombol is-utama" wire:loading.attr="disabled" wire:target="simpan,sampul,galeriBaru">
                    <i class="bi bi-check2"></i>
                    <span wire:loading.remove wire:target="simpan,sampul,galeriBaru">Simpan</span>
                    <span wire:loading wire:target="sampul,galeriBaru">Mengunggah…</span>
                    <span wire:loading wire:target="simpan">Menyimpan…</span>
                </button>
            </div>
        </header>

        <div class="dsb-rak">
            {{-- Kolom kiri: isi tulisan --}}
            <div class="k-8">
                <div class="dsb-kartu">
                    <div class="dsb-kartu-isi ast-tumpuk">
                        <div class="dsb-medan">
                            <label class="dsb-label" for="ast-judul">Judul <span class="dsb-wajib">*</span></label>
                            <input id="ast-judul" type="text" class="dsb-isian @error('judul') is-galat @enderror" wire:model.live.debounce.500ms="judul"
                                placeholder="{{ $isPorto ? 'Contoh: Sistem Kasir untuk 12 Cabang Toko Roti' : 'Contoh: Asthana Resmikan Kantor Baru di Pontianak' }}">
                            @error('judul') <span class="dsb-galat">{{ $message }}</span> @enderror
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="ast-slug">Alamat halaman
                                <span class="dsb-label-ket">{{ $baru ? 'otomatis dari judul' : 'sengaja tidak berubah saat judul disunting' }}</span>
                            </label>
                            <div class="dsb-cari">
                                <i class="bi bi-link-45deg"></i>
                                <input id="ast-slug" type="text" class="dsb-isian @error('slug') is-galat @enderror" wire:model="slug">
                            </div>
                            <span class="ast-catatan">{{ $awalanAlamat }}<b>{{ $slug ?: '…' }}</b></span>
                            @error('slug') <span class="dsb-galat">{{ $message }}</span> @enderror
                        </div>

                        <div class="dsb-medan">
                            <span class="dsb-label">Isi</span>
                            <div class="ast-penyunting" wire:ignore>
                                <div id="ast-editor"></div>
                                {{-- value ditulis eksplisit: skrip penyunting membaca isi awal
                                     dari sini sebelum Livewire sempat mengisinya. --}}
                                <input type="hidden" id="ast-isi" wire:model="isi" value="{{ $isi }}">
                            </div>
                            @error('isi') <span class="dsb-galat">{{ $message }}</span> @enderror
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="ast-ringkasan">Ringkasan
                                <span class="dsb-label-ket">teks kartu & pratinjau tautan — kosong = diambil dari awal isi</span>
                            </label>
                            <textarea id="ast-ringkasan" rows="3" maxlength="300" class="dsb-isian @error('ringkasan') is-galat @enderror" wire:model="ringkasan"></textarea>
                            @error('ringkasan') <span class="dsb-galat">{{ $message }}</span> @enderror
                        </div>

                        @if ($isPorto)
                            <div class="dsb-medan">
                                <span class="dsb-label">Galeri <span class="dsb-label-ket">opsional, maksimal 12 gambar</span></span>
                                @if ($adaGaleri)
                                    <div class="ast-galeri">
                                        @foreach ($urlGaleri as $iGaleri => $urlButir)
                                            <div class="ast-galeri-butir" wire:key="galeri-{{ $iGaleri }}-{{ md5($urlButir) }}">
                                                <img src="{{ $urlButir }}" alt="">
                                                <button type="button" wire:click="buangGaleri({{ $iGaleri }})" title="Buang"><i class="bi bi-x-lg"></i></button>
                                            </div>
                                        @endforeach
                                        @foreach ($galeriBaru as $iBaru => $berkasBaru)
                                            <div class="ast-galeri-butir is-baru" wire:key="galeri-baru-{{ $iBaru }}">
                                                <img src="{{ \App\Support\PratinjauUnggahan::url($berkasBaru) }}" alt="">
                                                <button type="button" wire:click="buangGaleriBaru({{ $iBaru }})" title="Batalkan"><i class="bi bi-x-lg"></i></button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="ast-unggah">
                                    <div class="ast-unggah-isi">
                                        <input type="file" accept="image/*" multiple wire:model="galeriBaru">
                                        <span class="ast-catatan">Bingkai hijau = belum tersimpan. Memilih berkas lagi menggantikan pilihan yang belum tersimpan.</span>
                                        <span wire:loading wire:target="galeriBaru" class="ast-catatan">Mengunggah…</span>
                                        @error('galeriBaru') <span class="dsb-galat">{{ $message }}</span> @enderror
                                        @error('galeriBaru.*') <span class="dsb-galat">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: keterangan & penerbitan --}}
            <div class="k-4">
                <div class="dsb-kartu">
                    <div class="dsb-kartu-isi ast-tumpuk">
                        @if ($isPorto)
                            <label class="ast-saklar">
                                <input type="checkbox" wire:model="terbit" @checked($this->terbit)>
                                Terbitkan di situs
                            </label>
                            <label class="ast-saklar">
                                <input type="checkbox" wire:model="unggulan" @checked($this->unggulan)>
                                Tampilkan di beranda
                            </label>
                            <span class="ast-catatan">Beranda menampilkan tiga portofolio unggulan yang terbit.</span>
                        @else
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-keadaan">Keadaan</label>
                                <select id="ast-keadaan" class="dsb-isian" wire:model.live="keadaanBerita">
                                    <option value="draf" @selected($this->keadaanBerita === 'draf')>Draf — belum tampil</option>
                                    <option value="terbit" @selected($this->keadaanBerita === 'terbit')>Terbit sekarang</option>
                                    <option value="jadwal" @selected($this->keadaanBerita === 'jadwal')>Jadwalkan</option>
                                </select>
                            </div>
                            @if ($isJadwal)
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="ast-jadwal">Tayang pada</label>
                                    <input id="ast-jadwal" type="datetime-local" class="dsb-isian @error('jadwal') is-galat @enderror" wire:model="jadwal">
                                    @error('jadwal') <span class="dsb-galat">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="dsb-kartu mt-3">
                    <div class="dsb-kartu-isi ast-tumpuk">
                        <div class="dsb-medan">
                            <span class="dsb-label">Gambar sampul</span>
                            <div class="ast-unggah" style="flex-direction: column; align-items: stretch;">
                                <div class="ast-unggah-rupa" style="width: 100%; height: 160px;">
                                    @if ($adaSampul)
                                        <img src="{{ $urlSampul }}" alt="">
                                    @else
                                        <i class="bi bi-image"></i>
                                    @endif
                                    <span wire:loading wire:target="sampul" class="ast-naik"><span class="ast-naik-isi"><span class="spinner-border spinner-border-sm"></span></span></span>
                                </div>
                                <div class="ast-unggah-isi">
                                    <input type="file" accept="image/*" wire:model="sampul">
                                    <span class="ast-catatan">Mendatar (16:9) paling rapi. Maksimal 6 MB.</span>
                                    @error('sampul') <span class="dsb-galat">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if ($isPorto)
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-kategori">Kategori <span class="dsb-wajib">*</span></label>
                                <select id="ast-kategori" class="dsb-isian" wire:model="kategori">
                                    @foreach (\App\Models\Asthana\Portofolio::KATEGORI as $kunciKategori => $labelKategori)
                                        <option value="{{ $kunciKategori }}" @selected($this->kategori === $kunciKategori)>{{ $labelKategori }}</option>
                                    @endforeach
                                </select>
                                @error('kategori') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-klien">Klien</label>
                                <input id="ast-klien" type="text" class="dsb-isian" wire:model="klien" placeholder="Nama klien">
                            </div>
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-tahun">Tahun</label>
                                <input id="ast-tahun" type="number" class="dsb-isian @error('tahun') is-galat @enderror" wire:model="tahun" placeholder="{{ now()->year }}">
                                @error('tahun') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-tautan">Tautan proyek <span class="dsb-label-ket">opsional</span></label>
                                <input id="ast-tautan" type="url" class="dsb-isian @error('tautan') is-galat @enderror" wire:model="tautan" placeholder="https://">
                                @error('tautan') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-kategori">Kategori <span class="dsb-label-ket">opsional, mis. Kegiatan</span></label>
                                <input id="ast-kategori" type="text" class="dsb-isian" wire:model="kategori">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Quill dimuat LANGSUNG di sini. Layout templateindex membuang semua yang
         didorong ke tumpukan skrip, jadi pustaka yang didorong ke sana tidak
         pernah termuat — tanpa galat apa pun. --}}
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        (function () {
            function pasang() {
                const wadah = document.getElementById('ast-editor');
                // Bukan halaman ini, atau sudah terpasang: memasang dua kali
                // membuat Quill kedua menempel pada toolbar yang pertama.
                if (!wadah || wadah.classList.contains('ql-container')) return;

                const tersembunyi = document.getElementById('ast-isi');
                const q = new Quill(wadah, {
                    theme: 'snow',
                    placeholder: 'Ceritakan proyek / beritanya di sini…',
                    modules: {
                        toolbar: [
                            [{ header: 2 }, { header: 3 }],
                            ['bold', 'italic', 'underline'],
                            ['blockquote', 'link'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['clean'],
                        ],
                    },
                });

                if (tersembunyi && tersembunyi.value) {
                    q.clipboard.dangerouslyPasteHTML(tersembunyi.value);
                }

                q.on('text-change', function () {
                    if (!tersembunyi) return;
                    // Penyunting kosong menyisakan paragraf berisi br; dikirim
                    // apa adanya ia terbaca sebagai isi.
                    tersembunyi.value = q.getText().trim().length === 0 ? '' : q.root.innerHTML;
                    tersembunyi.dispatchEvent(new Event('input'));
                });
            }

            function mulai() {
                if (!document.getElementById('ast-editor')) return;
                if (window.Quill) { pasang(); return; }

                // Lewat wire:navigate, tag skrip Quill bisa belum selesai diunduh.
                let sisa = 60;
                const tunggu = setInterval(function () {
                    if (window.Quill) {
                        clearInterval(tunggu);
                        pasang();
                    } else if (--sisa <= 0) {
                        clearInterval(tunggu);
                        const w = document.getElementById('ast-editor');
                        if (w) w.innerHTML = '<div style="padding:16px;color:#b91c1c;">Penyunting gagal dimuat. Periksa internet, lalu muat ulang halaman.</div>';
                    }
                }, 100);
            }

            mulai();
            if (!window.__astEditorNavigasi) {
                window.__astEditorNavigasi = true;
                document.addEventListener('livewire:navigated', mulai);
            }
        })();
    </script>
</div>
