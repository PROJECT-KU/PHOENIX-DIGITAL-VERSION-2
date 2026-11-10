<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*). Kabel Quill yang
         sudah jalan SENGAJA tidak disentuh: id editor, wire:ignore, dan blok
         @script di bawah tetap persis seperti sebelumnya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')

    {{-- Gaya khusus layar ini, ditulis INLINE: public/build di-gitignore,
         jadi berkas hasil build tidak pernah sampai ke server. --}}
    <style>
    /* TIGA kolom, bukan auto-fit.

       Dengan auto-fit, layar lebar memuat empat kolom — dan enam isian
       "boleh dikosongkan" jatuh jadi 4 + 2, menyisakan dua sel kosong
       selebar setengah kartu di baris kedua. Dipatok tiga, enam isian itu
       menjadi dua baris penuh. */
    .lwg-rak { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    /* Bagian "wajib diisi" hanya punya DUA isian sesudah nama posisi yang
       melebar penuh. Pada kisi tiga kolom, sel ketiganya kosong — petak
       putih selebar sepertiga kartu tepat di bagian yang paling dibaca. */
    .lwg-rak.is-dua { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (max-width: 991.98px) { .lwg-rak { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .lwg-rak, .lwg-rak.is-dua { grid-template-columns: minmax(0, 1fr); } }
    .lwg-rak .is-lebar { grid-column: 1 / -1; }

    .lwg-bagian { margin-bottom: 22px; }
    .lwg-bagian:last-child { margin-bottom: 0; }
    .lwg-bagian-judul {
        display: flex; align-items: center; gap: 7px; margin-bottom: 12px;
        font-size: .72rem; font-weight: 800; letter-spacing: .05em;
        text-transform: uppercase; color: #94a3b8;
    }
    .lwg-bagian-judul::after { content: ""; flex: 1 1 auto; height: 1px; background: #f1f5f9; }
    .lwg-bagian-judul i.bi { color: #7c3aed; font-size: .88rem; line-height: 1; }
    .lwg-bagian-judul i.bi::before { display: block; line-height: 1; }

    .lwg-bantu { color: var(--dsb-redup); font-size: .76rem; margin-top: 5px; }

    /* Kotak Quill: bingkainya disamakan dengan .dsb-isian supaya dua jenis
       isian di satu borang tidak tampak berasal dari dua aplikasi. */
    .lwg-tulis {
        border: 1px solid var(--dsb-tepi); border-radius: 11px; overflow: hidden; background: #fff;
    }
    .lwg-tulis .ql-toolbar.ql-snow {
        border: 0; border-bottom: 1px solid var(--dsb-tepi); background: #f8fafc;
        position: sticky; top: 0; z-index: 10;
    }
    .lwg-tulis .ql-container.ql-snow { border: 0; height: 220px; overflow-y: auto; }
    .lwg-tulis .ql-editor { font-size: .88rem; line-height: 1.65; }

    /* Kaki lengket: borang ini panjang (dua editor teks), dan tombol simpan
       yang hanya ada di ujung bawah berarti menggulung dua layar untuk
       menyimpan satu koreksi di kotak paling atas. */
    .lwg-kaki {
        position: sticky; bottom: 0; z-index: 3;
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 10px; margin: 20px -4px -4px; padding: 13px 15px;
        border-radius: 14px; background: #f8fafc; border: 1px solid var(--dsb-tepi);
    }
    .lwg-kaki-ket { color: var(--dsb-redup); font-size: .8rem; }
    .lwg-kaki-aksi { display: flex; flex-wrap: wrap; gap: 8px; }
    /* Isi tombol yang berganti-ganti (teks biasa ↔ "Menyimpan…").

       Kelasnya LOKAL, bukan .orcha-ikon-teks: kelas itu hidup di berkas gaya
       Orcha, dan layar ini tidak memuatnya — ikon dan tulisannya lalu
       menempel rapat tanpa ada satu pun tanda bahwa gayanya hilang. */
    .lwg-tombol-isi { display: inline-flex; align-items: center; gap: .4rem; }
    .lwg-tombol-isi > i.bi { line-height: 1; flex: 0 0 auto; }

    @media (max-width: 575.98px) {
        .lwg-kaki { flex-direction: column; align-items: stretch; }
        .lwg-kaki-aksi > * { flex: 1 1 0; justify-content: center; }
    }
    </style>

    {{-- TANPA kelas .dsb di sini.

         Borang ini selalu dipasang di dalam halaman yang pembungkusnya sudah
         ber-.dsb (create/edit), dan .dsb membawa padding samping. Dipasang
         dua kali, paddingnya ikut dua kali — kartu borang menjorok ~18px
         lebih sempit daripada kartu di layar daftar, dan dua layar yang
         seharusnya sebaris jadi tidak pernah segaris. --}}
    <form wire:submit.prevent="save" class="lwg-borang">
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-briefcase"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Keterangan Posisi</h2>
                            <span class="dsb-kartu-sub">Inilah yang dibaca pelamar di kartu lowongan situs</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    {{-- value= DITULIS EKSPLISIT di tiap isian sebaris.

                         wire:model sendiri tidak menggambar nilainya di HTML dari
                         peladen. Di layar Ubah, isian yang kosong terbaca sebagai
                         "lowongan ini memang belum punya judul" — lalu disimpan
                         begitu saja. Pola yang sama sudah dipakai layar peserta
                         Orcha dan borang tulisan Asthana.

                         <textarea> tidak perlu: isinya memang ditulis sebagai isi
                         elemen, bukan atribut. --}}

                    {{-- ===== Yang wajib ===== --}}
                    <div class="lwg-bagian">
                        <div class="lwg-bagian-judul"><i class="bi bi-asterisk"></i> Wajib diisi</div>
                        <div class="lwg-rak is-dua">
                            <div class="dsb-medan is-lebar">
                                <label class="dsb-label" for="lwg-title">
                                    Nama posisi <span class="dsb-wajib">*</span>
                                </label>
                                <input id="lwg-title" type="text" wire:model.live="title" value="{{ $title }}"
                                    class="dsb-isian @error('title') is-galat @enderror"
                                    placeholder="mis. Programmer Laravel">
                                @error('title') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="lwg-unit">
                                    Perusahaan <span class="dsb-wajib">*</span>
                                </label>
                                <select id="lwg-unit" class="dsb-isian @error('unit') is-galat @enderror" wire:model="unit">
                                    @foreach (\App\Models\Lowongan::UNIT as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected((string) $this->unit === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('unit') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="lwg-status">
                                    Tayang di situs? <span class="dsb-wajib">*</span>
                                </label>
                                <select id="lwg-status" class="dsb-isian @error('isActive') is-galat @enderror"
                                    wire:model.defer="isActive">
                                    <option value="" @selected((string) $this->isActive === '')>— pilih dulu —</option>
                                    <option value="active" @selected((string) $this->isActive === 'active')>Ya, tayangkan</option>
                                    <option value="non-active" @selected((string) $this->isActive === 'non-active')>Belum, simpan saja</option>
                                </select>
                                <span class="lwg-bantu">
                                    "Belum" berarti tersimpan di sini tetapi tidak muncul di situs.
                                </span>
                                @error('isActive') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ===== Keterangan tambahan ===== --}}
                    <div class="lwg-bagian">
                        <div class="lwg-bagian-judul"><i class="bi bi-card-checklist"></i> Boleh dikosongkan</div>
                        <p class="lwg-bantu" style="margin: -6px 0 12px">
                            Yang dikosongkan tidak ditampilkan di situs — bukan ditampilkan kosong.
                        </p>

                        <div class="lwg-rak">
                            <div class="dsb-medan">
                                <label class="dsb-label" for="lwg-jenis">Jenis pekerjaan</label>
                                <select id="lwg-jenis" class="dsb-isian @error('jenis') is-galat @enderror" wire:model="jenis">
                                    <option value="" @selected((string) $this->jenis === '')>— tidak disebut —</option>
                                    @foreach (\App\Models\Lowongan::JENIS as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected((string) $this->jenis === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('jenis') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="lwg-pola">Bekerja di mana</label>
                                <select id="lwg-pola" class="dsb-isian @error('polaKerja') is-galat @enderror" wire:model="polaKerja">
                                    <option value="" @selected((string) $this->polaKerja === '')>— tidak disebut —</option>
                                    @foreach (\App\Models\Lowongan::POLA_KERJA as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected((string) $this->polaKerja === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('polaKerja') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="lwg-lokasi">Kota</label>
                                <input id="lwg-lokasi" type="text" wire:model="lokasi" maxlength="100" value="{{ $lokasi }}"
                                    class="dsb-isian @error('lokasi') is-galat @enderror" placeholder="mis. Yogyakarta">
                                @error('lokasi') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="lwg-batas">Terakhir melamar</label>
                                <input id="lwg-batas" type="date" wire:model="batasLamaran" value="{{ $batasLamaran }}"
                                    class="dsb-isian @error('batasLamaran') is-galat @enderror">
                                <span class="lwg-bantu">
                                    Lewat tanggal ini, lowongan hilang sendiri dari situs.
                                </span>
                                @error('batasLamaran') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="lwg-posisi">Butuh berapa orang</label>
                                <input id="lwg-posisi" type="number" min="1" max="999" wire:model="jumlahPosisi" value="{{ $jumlahPosisi }}"
                                    class="dsb-isian @error('jumlahPosisi') is-galat @enderror" placeholder="mis. 2">
                                @error('jumlahPosisi') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="lwg-gaji">Kisaran gaji</label>
                                <input id="lwg-gaji" type="text" wire:model="gaji" maxlength="100" value="{{ $gaji }}"
                                    class="dsb-isian @error('gaji') is-galat @enderror"
                                    placeholder="mis. Rp 4–6 juta / bulan">
                                <span class="lwg-bantu">Kosongkan kalau belum mau disebut.</span>
                                @error('gaji') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan is-lebar">
                                <label class="dsb-label" for="lwg-ringkas">Kalimat pembuka</label>
                                <textarea id="lwg-ringkas" rows="2" wire:model="ringkasan" maxlength="255"
                                    class="dsb-isian @error('ringkasan') is-galat @enderror"
                                    placeholder="Satu–dua kalimat yang tampil di kartu lowongan">{{ $ringkasan }}</textarea>
                                @error('ringkasan') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Isi lowongan (Quill) ===== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-file-text"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Isi Lowongannya</h2>
                            <span class="dsb-kartu-sub">Pekerjaannya apa saja, dan siapa yang dicari</span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    {{-- wire:ignore WAJIB: Quill mengubah DOM di dalamnya, dan
                         Livewire yang menggambar ulang kotak ini akan membuang
                         editornya berikut tulisan yang belum tersimpan. --}}
                    <div class="dsb-medan lwg-bagian" wire:ignore>
                        <label class="dsb-label">Pekerjaannya apa saja</label>
                        <div class="lwg-tulis quill-container">
                            <div id="editor-descriptions"></div>
                        </div>
                        <input type="hidden" wire:model="descriptions" id="descriptions">
                        @error('descriptions') <span class="dsb-galat">{{ $message }}</span> @enderror
                    </div>

                    <div class="dsb-medan lwg-bagian" wire:ignore>
                        <label class="dsb-label">Syarat pelamarnya</label>
                        <div class="lwg-tulis quill-container">
                            <div id="editor-requirements"></div>
                        </div>
                        <input type="hidden" wire:model="requirements" id="requirements">
                        @error('requirements') <span class="dsb-galat">{{ $message }}</span> @enderror
                    </div>

                    <div class="lwg-kaki">
                        <span class="lwg-kaki-ket">
                            {{ $this->mode === 'create'
                                ? 'Belum tayang sebelum Anda menekan Simpan.'
                                : 'Perubahan baru berlaku setelah disimpan.' }}
                        </span>

                        <div class="lwg-kaki-aksi">
                            <a href="{{ route('admin.lowongan.index') }}" wire:navigate
                                class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                <i class="bi bi-x-lg"></i><span>Batal</span>
                            </a>
                            {{-- Kelas display di dalam, bukan pada elemen wire:loading:
                                 d-* !important mengalahkan cara Livewire
                                 menyembunyikannya. --}}
                            <button type="submit" class="dsb-tombol is-utama"
                                wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save" class="lwg-tombol-isi">
                                    <i class="bi bi-check2-circle"></i>
                                    {{ $this->mode === 'create' ? 'Simpan Lowongan' : 'Simpan Perubahan' }}
                                </span>
                                <span wire:loading wire:target="save" class="lwg-tombol-isi">
                                    <span class="dsb-putar is-kecil"></span> Menyimpan…
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts-head')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush
@script
<script>
    const toolbarOptions = [
        ['bold', 'italic', 'underline'],
        [{
            'header': 1
        }, {
            'header': 2
        }],
        [{
            'list': 'ordered'
        }, {
            'list': 'bullet'
        }],
        ['align', {
            'align': 'center'
        }]
    ];

    function initQuillEditor(editorId, inputId) {
        const quill = new Quill(`#${editorId}`, {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            }
        });

        const hiddenInput = document.querySelector(`#${inputId}`);

        quill.on('text-change', function() {
            const html = quill.root.innerHTML;
            hiddenInput.value = html;
            hiddenInput.dispatchEvent(new Event('input'));
        });

        return quill;
    }

    const quilldescriptions = initQuillEditor('editor-descriptions', 'descriptions');
    const quillrequirements = initQuillEditor('editor-requirements', 'requirements');

    Livewire.on('load-quill-content', (data) => {
        if (data[0].descriptions) {
            quilldescriptions.root.innerHTML = data[0].descriptions;
        }
        if (data[0].requirements) {
            quillrequirements.root.innerHTML = data[0].requirements;
        }
    });
</script>
@endscript
