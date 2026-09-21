{{-- Tata letak formulir memakai bahasa rupa dasbor (dsb-*) + gaya artikel
     (bl-*). Kabel yang sudah jalan SENGAJA tidak disentuh: Quill tetap di
     dalam wire:ignore dengan #editor-body dan input tersembunyi #body,
     pemilih kategori tetap .open-cat-picker, unggahan sampul tetap
     #coverInput. Yang berubah hanya kelas & susunannya. --}}
@include('livewire.pages.admin.partials.dasbor-gaya')
@include('livewire.pages.admin.blog.partials.artikel-gaya')

<form wire:submit.prevent="save" class="blog-editor dsb">
    <div class="bl-form">
        {{-- ============ KOLOM UTAMA: ISI ARTIKEL ============ --}}
        <div class="bl-form-utama">
            <section class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bl-panel-judul">
                        <span class="bl-panel-ikon" style="background: #f5f3ff; color: #7c3aed;"><i class="bi bi-pencil-square"></i></span>
                        <span>
                            <b>Konten Artikel</b>
                            <small>Judul dan isi tulisan yang dibaca pengunjung.</small>
                        </span>
                    </div>

                    <label class="bl-medan">
                        <span>Judul Artikel <em class="bl-wajib">*</em></span>
                        <input type="text" wire:model.live.debounce.500ms="title"
                            class="dsb-isian @error('title') is-galat @enderror"
                            placeholder="Contoh: 5 Tips Memilih Akun Premium yang Aman &amp; Bergaransi">
                        @error('title') <span class="bl-galat">{{ $message }}</span> @enderror
                    </label>

                    <div class="bl-url">
                        <i class="bi bi-link-45deg"></i>
                        <span>phoenixdigitalwarehouse.com/blog/</span>
                        <b>{{ $slug ?: 'otomatis-dari-judul' }}</b>
                        <span class="bl-url-auto"><i class="bi bi-magic"></i>otomatis</span>
                    </div>

                    <div class="bl-medan" style="margin-top: 16px;" wire:ignore>
                        <span><i class="bi bi-body-text me-1" style="color: #7c3aed;"></i>Isi Artikel <em class="bl-wajib">*</em></span>
                        <div class="quill-container" style="height: 430px; overflow: auto;">
                            <div id="editor-body"></div>
                        </div>
                        <span class="bl-bantu"><i class="bi bi-lightbulb me-1"></i>Gunakan Judul (H2/H3), tebal, kutipan &amp; poin agar artikel enak dibaca dan lebih SEO-friendly.</span>
                        <input type="hidden" wire:model.live.debounce.800ms="body" id="body">
                    </div>
                    @error('body') <span class="bl-galat">{{ $message }}</span> @enderror
                </div>
            </section>

            {{-- Ringkasan & SEO otomatis (hanya baca) --}}
            <section class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bl-panel-judul">
                        <span class="bl-panel-ikon" style="background: #eef2ff; color: #4338ca;"><i class="bi bi-magic"></i></span>
                        <span>
                            <b>Ringkasan &amp; SEO</b>
                            <small>Diambil otomatis dari judul &amp; isi artikel — tak perlu diketik.</small>
                        </span>
                        <button type="button" class="bl-btn bl-seo-acak" wire:click="generateSeo" wire:loading.attr="disabled" wire:target="generateSeo">
                            <span wire:loading.remove wire:target="generateSeo" class="bl-isi-tombol"><i class="bi bi-arrow-repeat"></i><span>Acak lagi</span></span>
                            <span wire:loading.inline-flex wire:target="generateSeo" class="bl-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyusun…</span></span>
                        </button>
                    </div>

                    <label class="bl-medan">
                        <span>Ringkasan (excerpt)</span>
                        <textarea wire:model="excerpt" rows="2" readonly class="dsb-isian is-kunci"
                            placeholder="Tulis isi artikel dulu, ringkasan akan dibuat otomatis…"></textarea>
                    </label>

                    <label class="bl-medan">
                        <span>Meta Title — judul di Google</span>
                        <input type="text" wire:model="meta_title" readonly class="dsb-isian is-kunci" placeholder="Otomatis dari judul artikel">
                    </label>

                    <label class="bl-medan" style="margin-bottom: 0;">
                        <span>Meta Description — deskripsi di Google</span>
                        <textarea wire:model="meta_description" rows="2" readonly class="dsb-isian is-kunci"
                            placeholder="Otomatis dari isi artikel"></textarea>
                    </label>
                </div>
            </section>
        </div>

        {{-- ============ SAMPING: PENGATURAN ============ --}}
        <aside class="bl-form-samping">
            <section class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bl-panel-judul">
                        <span class="bl-panel-ikon" style="background: #ecfdf5; color: #15803d;"><i class="bi bi-send-check"></i></span>
                        <span>
                            <b>Publikasi</b>
                            <small>Kapan artikel ini terlihat pengunjung.</small>
                        </span>
                    </div>

                    <label class="bl-medan">
                        <span>Status <em class="bl-wajib">*</em></span>
                        <select wire:model.defer="status" class="dsb-isian @error('status') is-galat @enderror">
                            <option value="draft">Draf — belum tampil</option>
                            <option value="published">Publikasikan — tampil</option>
                        </select>
                        @error('status') <span class="bl-galat">{{ $message }}</span> @enderror
                    </label>

                    <label class="bl-medan" style="margin-bottom: 0;">
                        <span><i class="bi bi-calendar-event me-1" style="color: #2563eb;"></i>Jadwalkan terbit</span>
                        <input type="datetime-local" wire:model.defer="published_at" class="dsb-isian @error('published_at') is-galat @enderror">
                        @error('published_at') <span class="bl-galat">{{ $message }}</span> @enderror
                        <span class="bl-bantu"><i class="bi bi-info-circle me-1"></i>Isi waktu di masa depan untuk menjadwalkan. Dikosongkan berarti tampil begitu dipublikasikan.</span>
                    </label>
                </div>
            </section>

            <section class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bl-panel-judul">
                        <span class="bl-panel-ikon" style="background: #fff7ed; color: #c2410c;"><i class="bi bi-tags"></i></span>
                        <span>
                            <b>Kategori</b>
                            <small>Mengelompokkan artikel di halaman blog.</small>
                        </span>
                    </div>

                    <button type="button" class="bl-pilih-kategori open-cat-picker"
                        data-current="{{ $category }}"
                        data-can-create="{{ auth()->user()->hasPermission('create_blog') ? '1' : '0' }}"
                        data-can-delete="{{ auth()->user()->hasPermission('delete_blog') ? '1' : '0' }}">
                        @if ($category)
                            <span class="bl-isi-tombol"><i class="bi bi-tag-fill" style="color: #c2410c;"></i><span>{{ $category }}</span></span>
                        @else
                            <span class="bl-pilih-kosong">Pilih kategori</span>
                        @endif
                        <i class="bi bi-chevron-down bl-pilih-panah"></i>
                    </button>
                    @error('category') <span class="bl-galat">{{ $message }}</span> @enderror
                </div>
            </section>

            <section class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="bl-panel-judul">
                        <span class="bl-panel-ikon" style="background: #eff6ff; color: #1d4ed8;"><i class="bi bi-card-image"></i></span>
                        <span>
                            <b>Gambar Sampul</b>
                            <small>Opsional — rasio ideal 16:9, maksimal 5 MB.</small>
                        </span>
                    </div>

                    <div class="bl-sampul-pratinjau">
                        @if ($cover && is_object($cover) && ! $errors->has('cover'))
                            <img src="{{ \App\Support\PratinjauUnggahan::url($cover) }}" alt="Pratinjau sampul"
                                onclick="showGlossyPreview('{{ \App\Support\PratinjauUnggahan::url($cover) }}')" title="Klik untuk memperbesar">
                        @elseif ($existingCover)
                            <img src="{{ asset('storage/img/blog/'.$existingCover) }}" alt="Sampul saat ini"
                                onclick="showGlossyPreview('{{ asset('storage/img/blog/'.$existingCover) }}')" title="Klik untuk memperbesar">
                        @else
                            <div class="bl-sampul-kosong is-form">
                                <i class="bi bi-card-image"></i>
                                <span>Pratinjau sampul (16:9)</span>
                            </div>
                        @endif
                    </div>

                    <label class="bl-unggah">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Klik untuk unggah sampul</span>
                        <input type="file" id="coverInput" wire:model="cover" accept="image/png, image/jpeg, image/jpg, image/webp">
                    </label>
                    <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="cover" style="margin-top: 10px;">
                        <span class="dsb-putar is-kecil"></span>Mengunggah gambar…
                    </span>
                    @error('cover') <span class="bl-galat">{{ $message }}</span> @enderror
                    <span class="bl-bantu"><i class="bi bi-info-circle me-1"></i>JPG, PNG, atau WEBP. Gambar besar dikecilkan otomatis saat disimpan.</span>
                </div>
            </section>

            <div class="bl-simpan-baris">
                <button type="submit" class="dsb-tombol is-utama bl-simpan" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save" class="bl-isi-tombol">
                        <i class="bi bi-check2-circle"></i><span>{{ $mode === 'create' ? 'Simpan Artikel' : 'Perbarui Artikel' }}</span>
                    </span>
                    <span wire:loading.inline-flex wire:target="save" class="bl-isi-tombol">
                        <span class="dsb-putar is-kecil"></span><span>Menyimpan…</span>
                    </span>
                </button>
                <a wire:navigate href="{{ route('admin.blog.index') }}" class="bl-btn bl-batal">Batal</a>
            </div>
        </aside>
    </div>
</form>

@push('scripts-head')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@script
<script>
    const blogToolbar = [
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'header': 2 }, { 'header': 3 }],
        ['blockquote', 'link'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['clean']
    ];

    const quillBody = new Quill('#editor-body', {
        theme: 'snow',
        modules: { toolbar: blogToolbar },
        placeholder: 'Tulis isi artikel di sini...'
    });

    const hiddenBody = document.querySelector('#body');

    // Muat konten awal (mode edit) dari nilai yang sudah ada di komponen.
    const initialBody = @js($body);
    if (initialBody) {
        quillBody.clipboard.dangerouslyPasteHTML(initialBody);
    }

    quillBody.on('text-change', function() {
        const html = quillBody.root.innerHTML;
        hiddenBody.value = (quillBody.getText().trim().length === 0) ? '' : html;
        hiddenBody.dispatchEvent(new Event('input'));
    });
</script>
@endscript

@push('styles')
<style>
    .quill-container { position: relative; border: 1px solid #e6e9f2; border-radius: 14px; overflow: auto; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
    .quill-container .ql-toolbar.ql-snow {
        position: sticky; top: 0; z-index: 5; background: #f8fafc;
        border: none; border-bottom: 1px solid #eef0f7; border-radius: 14px 14px 0 0; padding: 10px 12px;
    }
    .quill-container .ql-container.ql-snow { border: none; font-size: 1.02rem; }
    .quill-container .ql-editor { min-height: 300px; padding: 18px 20px; line-height: 1.8; color: #334155; }
    .quill-container .ql-editor.ql-blank::before { color: #b6bcc6; font-style: normal; }
    /* Kontrol toolbar pakai warna brand saat hover/aktif */
    .quill-container .ql-toolbar button:hover .ql-stroke,
    .quill-container .ql-toolbar button.ql-active .ql-stroke,
    .quill-container .ql-toolbar .ql-picker-label:hover .ql-stroke,
    .quill-container .ql-toolbar .ql-picker-label.ql-active .ql-stroke { stroke: var(--ph-orange, #f26522) !important; }
    .quill-container .ql-toolbar button:hover .ql-fill,
    .quill-container .ql-toolbar button.ql-active .ql-fill { fill: var(--ph-orange, #f26522) !important; }
    .quill-container .ql-toolbar button:hover,
    .quill-container .ql-toolbar button.ql-active,
    .quill-container .ql-toolbar .ql-picker-label:hover,
    .quill-container .ql-toolbar .ql-picker-label.ql-active { color: var(--ph-orange, #f26522) !important; }
    .quill-container .ql-snow .ql-picker-options { border-radius: 10px; border: 1px solid #eef0f7; box-shadow: 0 8px 24px rgba(15, 23, 42, .1); }

    /* Ikon bootstrap di dalam form disejajarkan dengan teksnya. */
    .blog-editor .bi { vertical-align: -0.125em; line-height: 1; }

    /* Select2 disamakan dengan tema form admin */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        min-height: 44px; border: 1px solid #dee2e6; border-radius: .5rem; display: flex; align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left: 6px; color: #334155; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px; }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--ph-orange, #f26522); box-shadow: 0 0 0 .18rem rgba(242, 101, 34, .12);
    }
    .select2-dropdown { border: 1px solid #dee2e6; border-radius: .5rem; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--ph-orange, #f26522); }

    /* ===== Popup pilih/kelola kategori — pola sama persis dengan Penyelesaian Task ===== */
    .of-pick-list { max-height: 320px; overflow-y: auto; text-align: left; display: flex; flex-direction: column; gap: .4rem; padding: .2rem; }
    .of-pick-item { display: block; width: 100%; text-align: left; border: 1px solid #e6e8f2; background: #fff; border-radius: 12px; padding: .7rem .9rem; font-weight: 600; color: #1e293b; font-size: .92rem; transition: all .15s ease; }
    .of-pick-item:hover { border-color: #6c63ff; background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.04)); transform: translateY(-1px); }
    .of-pick-empty { text-align: center; color: #94a3b8; padding: 1.5rem; font-size: .9rem; }
    .of-pick-row { display: flex; align-items: stretch; gap: .4rem; }
    .of-pick-row .of-pick-item { flex: 1 1 auto; width: auto; }
    .of-pick-del { flex: 0 0 auto; width: 44px; padding: 0; border: 1px solid #fee2e2; background: #fff5f5; color: #ef4444; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; transition: all .15s ease; }
    .of-pick-del:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: translateY(-1px); }
    .of-pick-used { flex: 0 0 auto; align-self: center; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; background: #f1f5f9; padding: .3rem .6rem; border-radius: 999px; }
    .of-pick-add { display: flex; gap: .5rem; align-items: stretch; }
    .of-pick-add .form-control { flex: 1 1 auto; border-radius: 12px; }
    .of-pick-addbtn { flex: 0 0 auto; border-radius: 12px; font-weight: 600; white-space: nowrap; box-shadow: 0 6px 14px rgba(124, 58, 237, .22); display: inline-flex; align-items: center; justify-content: center; }
    .of-pick-del i.bi, .of-pick-addbtn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
    .of-pick-msg { color: #ef4444; font-size: .82rem; margin-top: .35rem; min-height: 1rem; text-align: left; }
    .of-pick-confirm { display: flex; align-items: center; gap: .5rem; width: 100%; padding: .5rem .8rem; border: 1px dashed #fca5a5; border-radius: 12px; background: #fff5f5; color: #b91c1c; font-weight: 600; font-size: .88rem; }
    .of-pick-confirm span { margin-right: auto; }
</style>
@endpush

<!--================== SWEET ALERT IMAGE UPLOAD ==================-->
<script>
    if (typeof window.showGlossyPreview !== 'function') {
        window.showGlossyPreview = function(imageUrl) {
            Swal.fire({
                imageUrl: imageUrl, imageAlt: 'Preview Gambar', showConfirmButton: false,
                showCloseButton: true, width: 'auto', padding: '1em',
                background: 'rgba(255, 255, 255, 0.65)', backdrop: 'rgba(0, 0, 0, 0.4)',
                didOpen: () => {
                    const popup = Swal.getPopup();
                    popup.style.backdropFilter = 'blur(15px)';
                    popup.style.WebkitBackdropFilter = 'blur(15px)';
                    popup.style.borderRadius = '20px';
                    const img = Swal.getImage();
                    img.style.borderRadius = '12px'; img.style.maxHeight = '80vh'; img.style.objectFit = 'contain';
                }
            });
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        const ToastGlossy = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 4000,
            timerProgressBar: true, background: 'rgba(255, 255, 255, 0.85)',
            customClass: { popup: 'swal-glossy-toast', title: 'swal-toast-title', timerProgressBar: 'swal-toast-progress' }
        });

        const coverInput = document.getElementById('coverInput');
        if (coverInput) {
            coverInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const valid = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    if (!valid.includes(file.type)) {
                        e.preventDefault(); e.stopImmediatePropagation(); e.target.value = '';
                        ToastGlossy.fire({ icon: 'error', title: 'Format tidak didukung!', text: 'Gunakan JPG, PNG, atau WEBP.' });
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        e.preventDefault(); e.stopImmediatePropagation(); e.target.value = '';
                        ToastGlossy.fire({ icon: 'error', title: 'Ukuran Terlalu Besar!', text: 'Maksimal ukuran gambar adalah 5 MB.' });
                        return;
                    }
                }
            }, true);
        }
    });
</script>
<!--================== END SWEET ALERT IMAGE UPLOAD ==================-->

<!--================== POPUP PILIH / KELOLA KATEGORI (pola Penyelesaian Task) ==================-->
<script>
    (function () {
        if (window.__blogCatPickerBound) return;
        window.__blogCatPickerBound = true;

        const esc = (s) => String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));

        const pickGlossy = {
            background: 'rgba(255, 255, 255, 0.92)', backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
            buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem'
        };

        function rowsHtml(list, canDelete) {
            if (!list.length) return '<div class="of-pick-empty">Belum ada kategori. Tambah di bawah.</div>';
            return list.map(it => `
                <div class="of-pick-row" data-row="${esc(it.name)}">
                    <button type="button" class="of-pick-item" data-name="${esc(it.name)}" data-search="${esc(it.name.toLowerCase())}">${esc(it.name)}</button>
                    ${it.used
                        ? '<span class="of-pick-used" title="Sedang dipakai artikel">dipakai</span>'
                        : (canDelete ? `<button type="button" class="of-pick-del" data-del="${esc(it.name)}" title="Hapus"><i class="bi bi-trash"></i></button>` : '')}
                </div>`).join('');
        }

        document.addEventListener('click', async function (e) {
            const btn = e.target.closest('.open-cat-picker');
            if (!btn || typeof Swal === 'undefined') return;
            e.preventDefault();
            const host = btn.closest('[wire\\:id]');
            if (!host || !window.Livewire) return;
            const lw = window.Livewire.find(host.getAttribute('wire:id'));
            const canCreate = btn.getAttribute('data-can-create') === '1';
            const canDelete = btn.getAttribute('data-can-delete') === '1';

            let list = await lw.call('categoryOptions');

            Swal.fire({
                title: 'Pilih Kategori',
                html: `
                    <input id="bcSearch" class="form-control mb-2" placeholder="Cari...">
                    <div id="bcList" class="of-pick-list">${rowsHtml(list, canDelete)}</div>
                    ${canCreate ? `
                    <div class="of-pick-add mt-3">
                        <input id="bcNew" class="form-control" placeholder="Kategori baru, mis. Tips & Panduan" maxlength="60">
                        <button type="button" id="bcAdd" class="btn btn-primary of-pick-addbtn"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
                    </div>` : ''}
                    <div id="bcMsg" class="of-pick-msg"></div>`,
                ...pickGlossy,
                didOpen: () => {
                    const listEl = document.getElementById('bcList');
                    const search = document.getElementById('bcSearch');
                    const msg = document.getElementById('bcMsg');

                    const applyFilter = () => {
                        const q = (search.value || '').toLowerCase();
                        listEl.querySelectorAll('.of-pick-row').forEach(row => {
                            const item = row.querySelector('.of-pick-item');
                            row.style.display = (item && item.dataset.search.includes(q)) ? '' : 'none';
                        });
                    };

                    const confirmDelete = (row, name) => {
                        const original = row.innerHTML;
                        const restore = () => { row.innerHTML = original; wireRow(row, name); };
                        row.innerHTML = `<div class="of-pick-confirm">
                            <span>Hapus kategori ini?</span>
                            <button type="button" class="btn btn-sm btn-danger of-pick-yes">Ya</button>
                            <button type="button" class="btn btn-sm btn-light of-pick-no">Batal</button></div>`;
                        row.querySelector('.of-pick-no').addEventListener('click', restore);
                        row.querySelector('.of-pick-yes').addEventListener('click', async () => {
                            const res = await lw.call('deleteCategoryReturn', name);
                            if (res.error) { restore(); msg.textContent = res.error; return; }
                            list = res.list; rebuild();
                        });
                    };

                    function wireRow(row, name) {
                        row.querySelector('.of-pick-item')?.addEventListener('click', () => { lw.set('category', name); Swal.close(); });
                        row.querySelector('.of-pick-del')?.addEventListener('click', () => confirmDelete(row, name));
                    }

                    function rebuild() {
                        listEl.innerHTML = rowsHtml(list, canDelete);
                        listEl.querySelectorAll('.of-pick-row').forEach(row => wireRow(row, row.dataset.row));
                        applyFilter();
                    }

                    search.addEventListener('input', applyFilter);
                    setTimeout(() => search.focus(), 100);

                    const addBtn = document.getElementById('bcAdd');
                    if (addBtn) {
                        const newInp = document.getElementById('bcNew');
                        const doAdd = async () => {
                            const name = (newInp.value || '').trim();
                            msg.textContent = '';
                            if (name.length < 2) { msg.textContent = 'Nama kategori minimal 2 karakter.'; return; }
                            if (list.some(it => it.name.toLowerCase() === name.toLowerCase())) { msg.textContent = 'Nama tersebut sudah ada.'; return; }
                            addBtn.disabled = true;
                            const res = await lw.call('addCategoryReturn', name);
                            addBtn.disabled = false;
                            if (res.error) { msg.textContent = res.error; return; }
                            lw.set('category', name); // item baru langsung terpilih
                            Swal.close();
                        };
                        addBtn.addEventListener('click', doAdd);
                        newInp.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') { ev.preventDefault(); doAdd(); } });
                    }

                    listEl.querySelectorAll('.of-pick-row').forEach(row => wireRow(row, row.dataset.row));
                }
            });
        });
    })();
</script>
<!--================== END POPUP KATEGORI ==================-->
