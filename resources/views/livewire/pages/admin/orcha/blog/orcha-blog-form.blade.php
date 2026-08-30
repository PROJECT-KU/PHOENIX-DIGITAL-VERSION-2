@section('title')
{{ $artikelId ? 'Sunting' : 'Tulis' }} Artikel Blog Orcha || lemon
@stop

@php
    /*
     | Sampul yang sudah tersimpan berada di server ORCHA, bukan di lemon.
     |
     | Jalur yang dikirim API berbentuk "/storage/artikel/x.webp" — relatif,
     | jadi kalau dipakai apa adanya peramban mencarinya di alamat lemon dan
     | mendapat 404. Yang terlihat admin: kotak pratinjau kosong dengan tulisan
     | alt-nya saja. Pola ini disalin dari destinasi-form, yang sudah benar.
     */
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanGambar = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => $artikelId ? 'Sunting Artikel' : 'Tulis Artikel',
            'keterangan' => 'Artikel ini terbit di orchajourney.com/blog, terpisah dari blog Phoenix.',
        ])

        <form wire:submit.prevent="simpan" class="blog-editor">
            <div class="row g-4">

                {{-- ============ KOLOM UTAMA ============ --}}
                <div class="col-lg-8">
                    <div class="bf-panel mb-4">
                        <div class="bf-panel-head"><i class="bi bi-pencil-square"></i> Konten Artikel</div>

                        <label class="form-label fw-bold text-secondary">
                            Judul Artikel <span class="text-danger">*</span>
                        </label>
                        <input type="text" wire:model.live.debounce.500ms="judul"
                            class="form-control form-control-lg @error('judul') is-invalid @enderror"
                            placeholder="Contoh: Bawa Apa Saja ke Bromo saat Trip Dini Hari">
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        {{-- Pratinjau alamat. Slugnya dihitung ORCHA, bukan ditebak
                             di sini — hanya Orcha yang tahu slug mana yang sudah
                             terpakai. Saat menyunting, slug lama dipertahankan
                             supaya alamat yang sudah beredar tidak hangus. --}}
                        <div class="bf-url mt-2">
                            <i class="bi bi-link-45deg"></i>
                            <span class="bf-url-base">orchajourney.com/blog/</span>
                            <span class="bf-url-slug">{{ $slug ?: 'otomatis-dari-judul' }}</span>
                            @if (! $artikelId)
                                <span class="bf-url-auto"><i class="bi bi-magic"></i> otomatis</span>
                            @else
                                <span class="bf-url-auto" style="background:#eef2f7;color:#64748b;">
                                    <i class="bi bi-lock"></i> tetap
                                </span>
                            @endif
                        </div>

                        <div class="mt-4" wire:ignore>
                            <label class="form-label fw-bold text-secondary d-flex align-items-center gap-2">
                                <i class="bi bi-body-text" style="color: var(--ph-orange, #f26522);"></i>
                                Isi Artikel <span class="text-danger">*</span>
                            </label>
                            <div class="quill-container" style="height: 430px; overflow: auto;">
                                <div id="editor-isi"></div>
                            </div>
                            <small class="text-muted mt-2 d-block" style="font-size:.75rem;">
                                <i class="bi bi-lightbulb me-1"></i>
                                Gunakan Judul (H2/H3), tebal, kutipan &amp; poin agar artikel enak dibaca dan lebih
                                SEO-friendly.
                            </small>
                            {{-- value= ditulis eksplisit: skrip penyunting membaca isi awal dari
                                 sini, dan itu jalan sebelum Livewire sempat
                                 mengisinya sendiri. --}}
                            <input type="hidden" wire:model.live.debounce.800ms="isi" id="isi"
                                value="{{ $isi }}">
                        </div>
                        @error('isi') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>

                    {{-- ============ RINGKASAN OTOMATIS ============ --}}
                    <div class="bf-panel">
                        <div
                            class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                            <div>
                                <div class="bf-panel-head mb-1"><i class="bi bi-magic"></i> Ringkasan &amp; SEO — Otomatis</div>
                                <p class="text-muted mb-0" style="font-size:.8rem;">
                                    Diambil otomatis dari judul &amp; isi artikel (kalimat menarik pilihan).
                                    Tak perlu diketik manual.
                                </p>
                            </div>
                            <button type="button" wire:click="buatRingkasan" wire:loading.attr="disabled"
                                class="btn btn-sm btn-outline-primary flex-shrink-0 d-inline-flex align-items-center justify-content-center gap-1 align-self-center align-self-sm-auto">
                                <i class="bi bi-arrow-repeat"></i> <span>Acak lagi</span>
                            </button>
                        </div>

                        <div class="row g-3">
                            {{-- Ringkasan BISA disunting, tidak readonly seperti
                                 meta di bawahnya.

                                 Bedanya: meta hanya dibaca mesin pencari,
                                 sedangkan ringkasan dibaca MANUSIA — ia jadi teks
                                 kartu di daftar blog dan pratinjau tautan saat
                                 artikelnya dibagikan ke WhatsApp. Yang dibaca
                                 manusia harus bisa dibetulkan manusia. --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary small mb-1">
                                    Ringkasan
                                    <span class="text-muted fw-normal">(kartu di daftar blog &amp; pratinjau tautan)</span>
                                </label>
                                <textarea wire:model="ringkasan" rows="2"
                                    class="form-control @error('ringkasan') is-invalid @enderror"
                                    placeholder="Tulis isi artikel dulu, ringkasan akan dibuat otomatis — dan tetap bisa Anda betulkan."></textarea>
                                @error('ringkasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Meta SEO dibiarkan readonly, sama dengan blog
                                 Phoenix: isinya untuk mesin pencari, bukan untuk
                                 dibaca pengunjung, dan panjangnya sudah dijaga
                                 pembuatnya (65 dan 155 huruf). --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary small mb-1">
                                    Meta Title <span class="text-muted fw-normal">(judul di Google)</span>
                                </label>
                                <input type="text" wire:model="metaTitle" readonly
                                    class="form-control bg-white" style="cursor:default;"
                                    placeholder="Otomatis dari judul artikel">
                                @error('metaTitle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary small mb-1">
                                    Meta Description <span class="text-muted fw-normal">(deskripsi di Google)</span>
                                </label>
                                <textarea wire:model="metaDescription" rows="2" readonly
                                    class="form-control bg-white" style="cursor:default;"
                                    placeholder="Otomatis dari isi artikel"></textarea>
                                @error('metaDescription') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ KOLOM SAMPING ============ --}}
                <div class="col-lg-4">

                    {{-- Publikasi --}}
                    <div class="bf-panel mb-4">
                        <div class="bf-panel-head"><i class="bi bi-send-check"></i> Publikasi</div>

                        <label class="form-label fw-semibold text-secondary small">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select wire:model.live="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="draf">📝 Draf (belum tampil)</option>
                            <option value="tayang">🌐 Publikasikan (tampil)</option>
                        </select>
                        @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                        <label class="form-label fw-semibold text-secondary small mt-3">
                            <i class="bi bi-calendar-event me-1"></i> Jadwalkan Terbit
                        </label>
                        <input type="datetime-local" wire:model="terbitPada"
                            class="form-control @error('terbitPada') is-invalid @enderror">
                        @error('terbitPada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted mt-1 d-block" style="font-size:.75rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Isi waktu di masa depan untuk menjadwalkan. Kosongkan = tampil saat dipublikasikan.
                        </small>

                        @if ($status === 'tayang' && $terbitPada === '')
                            <div class="alert alert-info d-flex gap-2 mt-3 mb-0 py-2 px-3" style="font-size:.78rem;">
                                <i class="bi bi-lightbulb-fill flex-shrink-0"></i>
                                <span>Tanggal kosong akan diisi waktu sekarang, jadi artikel langsung terbaca.</span>
                            </div>
                        @endif
                    </div>

                    {{-- Kategori & penulis --}}
                    <div class="bf-panel mb-4">
                        <div class="bf-panel-head"><i class="bi bi-tags"></i> Kategori &amp; Penulis</div>

                        <label class="form-label fw-semibold text-secondary small">Kategori</label>

                        {{-- Popup pemilih, sama dengan blog Phoenix — bukan
                             dropdown. Bedanya bukan selera: popupnya punya kotak
                             cari, dan bisa menambah rubrik baru tanpa
                             meninggalkan artikel yang belum tersimpan. --}}
                        <button type="button"
                            class="form-select text-start of-picker-btn rounded-3 open-kat-picker"
                            data-current="{{ $kategori }}">
                            @if ($namaKategori)
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-tag-fill" style="color:var(--orc-primer);"></i>
                                    <span>{{ $namaKategori }}</span>
                                </span>
                            @else
                                <span class="text-muted">Pilih kategori</span>
                            @endif
                        </button>
                        @error('kategori') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                        <label class="form-label fw-semibold text-secondary small mt-3">Penulis</label>
                        <input type="text" wire:model="penulis"
                            class="form-control @error('penulis') is-invalid @enderror"
                            placeholder="Tim Orcha">
                        @error('penulis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Sampul --}}
                    <div class="bf-panel">
                        <div class="bf-panel-head">
                            <i class="bi bi-card-image"></i> Gambar Sampul
                            <span class="text-muted fw-normal ms-1" style="font-size:.75rem;">(opsional)</span>
                        </div>

                        @php
                            /* temporaryUrl() TIDAK dipakai: alamat bawaan Livewire
                               berakhiran .jpg dan lapisan pengoptimal gambar di
                               hosting membuang query string-nya, padahal izin
                               aksesnya justru ada di situ — pratinjaunya tidak
                               pernah muncul di produksi. Lihat PratinjauUnggahan. */
                            $pratinjauSampul = $sampul && ! $errors->has('sampul')
                                ? \App\Support\PratinjauUnggahan::url($sampul)
                                : (! $hapusSampul ? $tautanGambar($sampulLama) : null);
                        @endphp

                        <div class="bf-cover-preview mb-3" wire:loading.class="opacity-50" wire:target="sampul">
                            @if ($pratinjauSampul)
                                <img src="{{ $pratinjauSampul }}" alt="Pratinjau sampul artikel"
                                    onclick="showGlossyPreview('{{ $pratinjauSampul }}')"
                                    title="Klik untuk memperbesar">
                            @else
                                <div class="bf-cover-empty">
                                    <i class="bi bi-card-image"></i>
                                    <span>Preview sampul (16:9)</span>
                                </div>
                            @endif
                        </div>

                        <div class="upload-container position-relative">
                            <input type="file" wire:model="sampul"
                                class="file-input @error('sampul') is-invalid @enderror"
                                accept="image/png, image/jpeg, image/jpg, image/webp">
                            <div class="upload-overlay">
                                <i class="bi bi-cloud-upload fs-4 text-primary"></i>
                                <span class="text-muted fw-bold">Klik untuk unggah sampul</span>
                            </div>
                        </div>
                        @error('sampul') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                        <small class="text-muted mt-1 d-block" style="font-size:.75rem;">
                            <i class="bi bi-info-circle me-1"></i> JPG, PNG, WEBP (maks 5MB). Rasio ideal 16:9 —
                            Orcha mengubahnya jadi WebP otomatis.
                        </small>

                        @if ($pratinjauSampul)
                            <button type="button" wire:click="batalkanSampul"
                                class="btn btn-sm btn-outline-danger w-100 mt-2 d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-x-circle"></i> <span>Hapus sampul</span>
                            </button>
                        @endif

                        @if ($hapusSampul)
                            <div class="alert alert-warning py-2 px-3 mt-2 mb-0" style="font-size:.75rem;">
                                Sampul akan dihapus saat disimpan. Artikel memakai gambar bawaan blog.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <a href="{{ route('admin.orcha.blog') }}" wire:navigate
                    class="orcha-btn orcha-btn-lembut bf-aksi px-4">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </a>
                {{-- Isi tombol DIGANTI pemintal, bukan pemintal ditaruh di
                     sebelah tulisannya.

                     Menyimpan artikel berarti menunggu Orcha di server lain —
                     jeda yang cukup terasa. Tanpa tanda apa pun admin menekan
                     tombolnya lagi, dan artikel yang sama terkirim dua kali.
                     wire:target="simpan" dipakai supaya tombolnya TIDAK ikut
                     berputar saat yang sedang berjalan cuma unggahan sampul
                     atau pengetikan judul.

                     Pola dan tulisannya sama dengan formulir destinasi. --}}
                <button type="submit" wire:loading.attr="disabled" wire:target="simpan"
                    class="orcha-btn orcha-btn-utama bf-aksi px-5 flex-grow-1">
                    {{-- JANGAN beri kelas d-* (d-flex, d-inline-flex, d-block) pada
                         kedua span ini.

                         wire:loading.remove menyembunyikan dengan menulis
                         style="display: none" sebaris, sedangkan utilitas d-*
                         Bootstrap ditulis `display: ... !important` — jadi
                         !important menang dan tulisan lamanya TIDAK pernah
                         hilang. Yang terlihat admin: "Update Artikel" dan
                         pemintal muncul bersamaan dalam satu tombol.

                         Formulir destinasi tidak kena karena span-nya memang
                         polos; ini menyalin bentuknya. --}}
                    <span wire:loading.remove wire:target="simpan">
                        <i class="bi bi-check2-circle"></i>
                        {{ $artikelId ? 'Update Artikel' : 'Simpan Artikel' }}
                    </span>
                    <span wire:loading wire:target="simpan">
                        <span class="spinner-border spinner-border-sm me-2" role="status"
                            aria-hidden="true"></span>Menyimpan ke Orcha…
                    </span>
                </button>
            </div>
        </form>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>

@push('scripts-head')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
    /*
     | Penyunting dipasang dari tumpukan skrip biasa, BUKAN dari direktif
     | skrip milik Livewire.
     |
     | Direktif itu hanya dijalankan Livewire untuk komponen ANAK. Formulir ini
     | komponen halaman penuh: bloknya ikut terkirim di wire:effects tetapi
     | tidak pernah dieksekusi. Terbukti di peramban — Quill termuat, Livewire
     | termuat, tetapi ".ql-editor" tidak pernah ada dan toolbarnya nol tombol.
     | Itulah sebabnya kotaknya tidak bisa diketik, tanpa satu pun pesan galat.
     |
     | Formulir blog Phoenix tidak kena karena ia komponen anak yang disisipkan
     | lewat tag livewire di dalam halamannya.
     |
     | CATATAN: jangan menulis nama direktif Blade (berawalan @) di komentar
     | mana pun di berkas ini. Blade tetap mengurainya walau berada di dalam
     | komentar JavaScript — satu kata saja menelan sisa view dan komponennya
     | gagal dengan "missing root tag", galat yang sama sekali tidak menyebut
     | komentar sebagai sebabnya. Sudah terjadi di berkas ini.
     */
    (function () {
        if (window.__orchaEditorBound) return;
        window.__orchaEditorBound = true;

        // Toolbar dibuat sama persis dengan blog Phoenix — admin yang berpindah
        // antara dua blog tidak boleh menemukan tombol yang berbeda.
        const orchaToolbar = [
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'header': 2 }, { 'header': 3 }],
            ['blockquote', 'link'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['clean']
        ];

        function pasangPenyunting() {
            const wadah = document.querySelector('#editor-isi');

            // Bukan halaman formulir, atau sudah terpasang. Memasang dua kali
            // membuat Quill kedua menempel pada toolbar yang pertama.
            if (!wadah || wadah.classList.contains('ql-container')) return;

            const quillIsi = new Quill(wadah, {
                theme: 'snow',
                modules: { toolbar: orchaToolbar },
                placeholder: 'Tulis isi artikel di sini...'
            });

            const isiTersembunyi = document.querySelector('#isi');

            // Isi awal dibaca dari nilai yang sudah ada di isian tersembunyi,
            // bukan dari @js($isi): blok ini berada di luar komponen, jadi ia
            // tidak ikut ter-render ulang saat berpindah artikel lewat
            // wire:navigate — nilainya akan tertinggal pada artikel sebelumnya.
            if (isiTersembunyi && isiTersembunyi.value) {
                quillIsi.clipboard.dangerouslyPasteHTML(isiTersembunyi.value);
            }

            quillIsi.on('text-change', function () {
                if (!isiTersembunyi) return;
                const html = quillIsi.root.innerHTML;
                // Penyunting kosong menyisakan "<p><br></p>" — dikirim apa
                // adanya, itu lolos validasi "required" padahal artikelnya kosong.
                isiTersembunyi.value = (quillIsi.getText().trim().length === 0) ? '' : html;
                isiTersembunyi.dispatchEvent(new Event('input'));
            });
        }

        /*
         | Menunggu pustaka Quill benar-benar ada.
         |
         | Halaman daftar tidak memuat Quill. Saat admin menekan "Tulis",
         | wire:navigate menukar halaman lewat JavaScript dan menyisipkan tag
         | <script src> Quill yang baru — yang bisa belum selesai diunduh saat
         | blok ini berjalan. Pada muat halaman biasa Quill sudah ada, jadi
         | jalur ini langsung jalan tanpa menunggu satu putaran pun.
         */
        function mulai() {
            if (!document.querySelector('#editor-isi')) return;

            if (window.Quill) { pasangPenyunting(); return; }

            let sisa = 60;   // ~6 detik
            const tunggu = setInterval(function () {
                if (window.Quill) {
                    clearInterval(tunggu);
                    pasangPenyunting();
                } else if (--sisa <= 0) {
                    clearInterval(tunggu);
                    const wadah = document.querySelector('#editor-isi');
                    // Admin harus diberi tahu, bukan dibiarkan menatap kotak
                    // yang diam tanpa penjelasan.
                    if (wadah) {
                        wadah.innerHTML =
                            '<div style="padding:18px 20px;color:#b91c1c;font-size:.9rem;">' +
                            'Penyunting teks gagal dimuat. Periksa sambungan internet, lalu muat ulang halaman ini.' +
                            '</div>';
                    }
                }
            }, 100);
        }

        // Muat biasa DAN perpindahan wire:navigate — halaman ini bisa dicapai
        // lewat kedua jalan itu.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mulai);
        } else {
            mulai();
        }

        document.addEventListener('livewire:navigated', mulai);
    })();
</script>
@endpush

@push('styles')
<style>
    /* Ditulis inline, bukan lewat Vite: public/build tidak ikut deploy. */
    .blog-editor .bf-panel { background: #fff; border: 1px solid #eef1f6; border-radius: 18px; padding: 22px; box-shadow: 0 2px 14px rgba(16, 40, 73, .05); }
    .blog-editor .bf-panel-head { display: flex; align-items: center; gap: .5rem; font-weight: 800; color: #1f2d3d; margin-bottom: 14px; }
    .blog-editor .bf-panel-head .bi { color: var(--ph-orange, #f26522); }
    .blog-editor .bi { vertical-align: -0.125em; line-height: 1; }

    .bf-url { display: flex; align-items: center; flex-wrap: wrap; gap: .35rem; font-size: .78rem; color: #64748b; background: #f8fafc; border: 1px solid #eef1f6; border-radius: 10px; padding: 7px 11px; }
    .bf-url-base { color: #94a3b8; }
    .bf-url-slug { font-weight: 700; color: #1f2d3d; word-break: break-all; }
    .bf-url-auto { display: inline-flex; align-items: center; gap: .25rem; margin-left: auto; background: #fff3e6; color: #b45309; border-radius: 999px; padding: 2px 9px; font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }

    .bf-cover-preview { aspect-ratio: 16/9; border-radius: 14px; border: 1px dashed #d9dee8; background: linear-gradient(135deg, #eef7fd, #d9edfa); display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .bf-cover-preview img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
    .bf-cover-empty { display: flex; flex-direction: column; align-items: center; gap: .35rem; color: var(--orc-primer); font-size: .78rem; font-weight: 600; }
    .bf-cover-empty .bi { font-size: 1.4rem; }

    .upload-container .file-input { position: absolute; inset: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer; z-index: 2; }
    .upload-container .upload-overlay { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .3rem; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 16px; background: #f8fafc; text-align: center; font-size: .8rem; }

    .of-picker-btn { cursor: pointer; }
    .of-picker-btn::after { content: "\F282"; font-family: "bootstrap-icons"; float: right; color: #94a3b8; font-size: .8rem; }
    .of-pick-list { max-height: 320px; overflow-y: auto; text-align: left; display: flex; flex-direction: column; gap: .4rem; padding: .2rem; }
    .of-pick-item { display: block; width: 100%; text-align: left; border: 1px solid #e6e8f2; background: #fff; border-radius: 12px; padding: .7rem .9rem; font-weight: 600; color: #1e293b; font-size: .92rem; transition: all .15s ease; }
    .of-pick-item:hover { border-color: var(--orc-primer); background: linear-gradient(135deg, rgba(124, 58, 237,.10), rgba(31, 45, 61,.04)); transform: translateY(-1px); }
    .of-pick-empty { text-align: center; color: #94a3b8; padding: 1.5rem; font-size: .9rem; }
    .of-pick-row { display: flex; align-items: stretch; gap: .4rem; }
    .of-pick-row .of-pick-item { flex: 1 1 auto; width: auto; }
    .of-pick-del { flex: 0 0 auto; width: 44px; padding: 0; border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; transition: all .15s ease; }
    .of-pick-del:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: translateY(-1px); }
    .of-pick-used { flex: 0 0 auto; align-self: center; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; background: #f1f5f9; padding: .3rem .6rem; border-radius: 999px; }
    .of-pick-add { display: flex; gap: .5rem; align-items: stretch; }
    .of-pick-add .form-control { flex: 1 1 auto; border-radius: 12px; }
    .of-pick-addbtn { flex: 0 0 auto; border-radius: 12px; font-weight: 600; white-space: nowrap; box-shadow: 0 6px 14px rgba(124, 58, 237,.22); display: inline-flex; align-items: center; justify-content: center; }
    .of-pick-del i.bi, .of-pick-addbtn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
    .of-pick-msg { color: #ef4444; font-size: .82rem; margin-top: .35rem; min-height: 1rem; text-align: left; }
    .of-pick-confirm { display: flex; align-items: center; gap: .5rem; width: 100%; padding: .5rem .8rem; border: 1px dashed #fca5a5; border-radius: 12px; background: #fff5f5; color: #b91c1c; font-weight: 600; font-size: .88rem; }
    .of-pick-confirm span { margin-right: auto; }


    .quill-container { border-radius: 12px; overflow: hidden; border: 1px solid #e6eaf0; }
    .quill-container .ql-toolbar.ql-snow { border: 0; border-bottom: 1px solid #eef1f6; background: #fbfdff; }
    .quill-container .ql-container.ql-snow { border: 0; font-size: .95rem; }
    .quill-container .ql-editor { min-height: 300px; padding: 18px 20px; line-height: 1.8; color: #334155; }
    .quill-container .ql-editor.ql-blank::before { color: #b6bcc6; font-style: normal; }

    /* Warna kontrol toolbar mengikuti biru Orcha, bukan oranye Phoenix — satu
       petunjuk kecil bahwa yang sedang ditulis adalah artikel Orcha. */
    .quill-container .ql-toolbar button:hover .ql-stroke,
    .quill-container .ql-toolbar button.ql-active .ql-stroke,
    .quill-container .ql-toolbar .ql-picker-label:hover .ql-stroke,
    .quill-container .ql-toolbar .ql-picker-label.ql-active .ql-stroke { stroke: var(--orc-primer) !important; }
    .quill-container .ql-toolbar button:hover .ql-fill,
    .quill-container .ql-toolbar button.ql-active .ql-fill { fill: var(--orc-primer) !important; }
    .quill-container .ql-toolbar button:hover,
    .quill-container .ql-toolbar button.ql-active,
    .quill-container .ql-toolbar .ql-picker-label:hover,
    .quill-container .ql-toolbar .ql-picker-label.ql-active { color: var(--orc-primer) !important; }
/* Kedua tombol kaki memakai keluarga .orcha-btn yang sama dengan formulir
   destinasi, bukan .btn Bootstrap yang dirakit sendiri.

   Sebelumnya ikon centang di tombol simpan duduk lebih rendah daripada
   tulisannya. Sebabnya .orcha-btn i — aturan yang meratakan ikon terhadap
   tulisan di seluruh halaman Orcha — memang tidak pernah mengenainya, karena
   tombolnya berkelas .btn btn-primary. Tombol Kembali di sebelahnya kebetulan
   tidak terlihat salah, tapi karena alasan lain: ia flex sendiri, sehingga
   ikonnya tertengahkan tanpa aturan itu.

   Jarak ikon ke tulisan kini datang dari gap milik .orcha-btn, jadi me-1/me-2
   dilepas — kalau dibiarkan, jaraknya jadi dua kali.

   Tingginya sengaja tetap 52px, bukan 38px bawaan .orcha-btn-besar: tombol
   simpan artikel adalah tindakan utama halaman ini dan ukurannya sudah
   dipakai. Diikat ke min-height supaya tidak meleset saat hurufnya berubah. */
.bf-aksi {
    min-height: 52px;
    font-size: 1rem;
}
</style>
@endpush

@push('scripts')
<script>
    (function () {
        // Penjaga pasang-sekali. Formulir ini dibuka lewat wire:navigate, dan
        // tanpa penjaga, pendengarnya bertumpuk tiap kunjungan — satu klik jadi
        // membuka dua popup.
        if (window.__orchaKatPickerBound) return;
        window.__orchaKatPickerBound = true;

        const esc = (t) => String(t).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));

        const gaya = {
            background: 'rgba(255, 255, 255, 0.92)', backdrop: 'rgba(124, 58, 237, 0.15)',
            customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
            buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem'
        };

        function barisHtml(daftar) {
            if (!daftar.length) return '<div class="of-pick-empty">Belum ada kategori. Tambah di bawah.</div>';
            return daftar.map(k => `
                <div class="of-pick-row" data-slug="${esc(k.slug)}">
                    <button type="button" class="of-pick-item" data-slug="${esc(k.slug)}" data-cari="${esc(k.nama.toLowerCase())}">${esc(k.nama)}</button>
                    ${k.dipakai
                        ? '<span class="of-pick-used" title="Sedang dipakai artikel">dipakai</span>'
                        : `<button type="button" class="of-pick-del" data-id="${esc(k.id)}" title="Hapus"><i class="bi bi-trash"></i></button>`}
                </div>`).join('');
        }

        document.addEventListener('click', async function (e) {
            const tombol = e.target.closest('.open-kat-picker');
            if (!tombol || typeof Swal === 'undefined') return;
            e.preventDefault();

            const induk = tombol.closest('[wire\\:id]');
            if (!induk || !window.Livewire) return;
            const lw = window.Livewire.find(induk.getAttribute('wire:id'));

            let daftar = await lw.call('pilihanKategori');

            Swal.fire({
                title: 'Pilih Kategori',
                html: `
                    <input id="katCari" class="form-control mb-2" placeholder="Cari...">
                    <div id="katDaftar" class="of-pick-list">${barisHtml(daftar)}</div>
                    <div class="of-pick-add mt-3">
                        <input id="katBaru" class="form-control" placeholder="Kategori baru, mis. Kuliner Lokal" maxlength="60">
                        <button type="button" id="katTambah" class="btn btn-primary of-pick-addbtn"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
                    </div>
                    <div id="katPesan" class="of-pick-msg"></div>`,
                ...gaya,
                didOpen: () => {
                    const daftarEl = document.getElementById('katDaftar');
                    const cari = document.getElementById('katCari');
                    const pesan = document.getElementById('katPesan');

                    const saring = () => {
                        const q = (cari.value || '').toLowerCase();
                        daftarEl.querySelectorAll('.of-pick-row').forEach(baris => {
                            const item = baris.querySelector('.of-pick-item');
                            baris.style.display = (item && item.dataset.cari.includes(q)) ? '' : 'none';
                        });
                    };

                    // Konfirmasi hapus digambar DI DALAM barisnya, bukan sebagai
                    // popup kedua di atas popup — Swal di atas Swal menutup yang
                    // pertama, jadi daftarnya ikut hilang.
                    const konfirmasiHapus = (baris, id) => {
                        const semula = baris.innerHTML;
                        const kembalikan = () => { baris.innerHTML = semula; pasang(baris); };

                        baris.innerHTML = `<div class="of-pick-confirm">
                            <span>Hapus kategori ini?</span>
                            <button type="button" class="btn btn-sm btn-danger of-pick-ya">Ya</button>
                            <button type="button" class="btn btn-sm btn-light of-pick-tidak">Batal</button></div>`;

                        baris.querySelector('.of-pick-tidak').addEventListener('click', kembalikan);
                        baris.querySelector('.of-pick-ya').addEventListener('click', async () => {
                            const hasil = await lw.call('hapusKategoriKembali', id);
                            if (hasil.error) { kembalikan(); pesan.textContent = hasil.error; return; }
                            daftar = hasil.list; bangunUlang();
                        });
                    };

                    function pasang(baris) {
                        baris.querySelector('.of-pick-item')?.addEventListener('click', (ev) => {
                            lw.set('kategori', ev.currentTarget.dataset.slug);
                            Swal.close();
                        });
                        baris.querySelector('.of-pick-del')?.addEventListener('click', (ev) => {
                            konfirmasiHapus(baris, ev.currentTarget.dataset.id);
                        });
                    }

                    function bangunUlang() {
                        daftarEl.innerHTML = barisHtml(daftar);
                        daftarEl.querySelectorAll('.of-pick-row').forEach(pasang);
                        saring();
                    }

                    cari.addEventListener('input', saring);
                    setTimeout(() => cari.focus(), 100);

                    const tombolTambah = document.getElementById('katTambah');
                    const isianBaru = document.getElementById('katBaru');

                    const tambah = async () => {
                        const nama = (isianBaru.value || '').trim();
                        pesan.textContent = '';

                        if (nama.length < 2) { pesan.textContent = 'Nama kategori minimal 2 karakter.'; return; }
                        if (daftar.some(k => k.nama.toLowerCase() === nama.toLowerCase())) {
                            pesan.textContent = 'Nama tersebut sudah ada.'; return;
                        }

                        tombolTambah.disabled = true;
                        const hasil = await lw.call('tambahKategoriKembali', nama);
                        tombolTambah.disabled = false;

                        if (hasil.error) { pesan.textContent = hasil.error; return; }

                        // Rubrik baru langsung terpilih: admin menambahnya justru
                        // karena artikel yang sedang ditulis membutuhkannya.
                        if (hasil.slug) lw.set('kategori', hasil.slug);
                        Swal.close();
                    };

                    tombolTambah.addEventListener('click', tambah);
                    isianBaru.addEventListener('keydown', (ev) => {
                        if (ev.key === 'Enter') { ev.preventDefault(); tambah(); }
                    });

                    daftarEl.querySelectorAll('.of-pick-row').forEach(pasang);
                }
            });
        });
    })();
</script>
@endpush
