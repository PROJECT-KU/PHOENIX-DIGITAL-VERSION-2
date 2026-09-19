<div>
    @php
        $edit = $mode !== 'create';
        $fileBaru = $file && is_object($file) && ! $errors->has('file');
        $namaBaru = $fileBaru ? $file->getClientOriginalName() : null;
        $ukuranBaru = $fileBaru ? number_format($file->getSize() / 1048576, 2, ',', '.').' MB' : null;
        $ukuranLama = $edit && $ebook ? $ebook->ukuranFileLabel() : null;
        $statusOpsi = [
            'active' => ['Aktif', 'bi-check-circle-fill', '#16a34a', 'Bisa dipilih saat memproses pesanan'],
            'non-active' => ['Nonaktif', 'bi-pause-circle-fill', '#64748b', 'Disembunyikan dari pilihan bonus'],
        ];
    @endphp

    <form wire:submit.prevent="save" class="eb-form-tata">
        {{-- ================== KOLOM UTAMA ================== --}}
        <div class="dsb-kartu">
            <div class="dsb-kartu-isi">
                <div class="eb-form-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-journal-text"></i></span>
                    <div>
                        <b>Informasi Ebook</b>
                        <span>Judul & deskripsi tampil untuk admin saat memilih bonus pesanan.</span>
                    </div>
                </div>

                <div class="eb-medan dsb-medan">
                    <label class="dsb-label" for="eb-judul">Judul Ebook <span class="text-danger">*</span></label>
                    <input id="eb-judul" type="text" wire:model.defer="judul" class="dsb-isian" placeholder="Contoh: Panduan Grammarly, Panduan Scopus">
                    @error('judul') <small class="eb-galat">{{ $message }}</small> @enderror
                </div>

                <div class="eb-medan dsb-medan">
                    <label class="dsb-label" for="eb-desk">Deskripsi</label>
                    <textarea id="eb-desk" wire:model.defer="deskripsi" rows="4" class="dsb-isian eb-desk-isian" placeholder="Keterangan singkat tentang isi ebook (opsional)"></textarea>
                    @error('deskripsi') <small class="eb-galat">{{ $message }}</small> @enderror
                </div>

                <div class="eb-medan dsb-medan">
                    <span class="dsb-label">Status <span class="text-danger">*</span></span>
                    <div class="eb-status-pilih" role="radiogroup" aria-label="Status ebook">
                        @foreach ($statusOpsi as $nilai => [$label, $ikon, $warna, $ket])
                            <label class="eb-status-opsi {{ $status === $nilai ? 'is-pilih' : '' }}" style="--c: {{ $warna }}">
                                <input type="radio" wire:model.live="status" value="{{ $nilai }}">
                                <span><i class="bi {{ $ikon }}"></i></span>
                                <span><b>{{ $label }}</b><small>{{ $ket }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    @error('status') <small class="eb-galat">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>

        {{-- ================== KOLOM SAMPING ================== --}}
        <aside class="eb-form-samping">
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <div class="eb-form-kepala">
                        <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                        <div>
                            <b>Berkas PDF @if (! $edit)<span class="text-danger">*</span>@endif</b>
                            <span>Maks. 2 MB · dibuka view-only oleh pelanggan</span>
                        </div>
                    </div>

                    @if ($edit && $existingFile)
                        <div class="eb-berkas" style="margin-bottom: 12px;">
                            <span class="eb-berkas-ikon"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                            <div>
                                <b>Berkas saat ini</b>
                                <small>{{ $ukuranLama ?: 'Berkas tidak ditemukan di server — unggah ulang' }}</small>
                            </div>
                            @if ($ukuranLama)
                                <a href="{{ route('admin.ebook.download', $ebook) }}" class="dsb-tabel-btn" title="Unduh berkas saat ini"><i class="bi bi-download"></i></a>
                            @endif
                        </div>
                    @endif

                    <div id="ebookZona" class="eb-unggah {{ $fileBaru ? 'is-siap' : '' }} {{ $errors->has('file') ? 'is-galat' : '' }}">
                        <input type="file" id="ebookFileInput" wire:model.live="file" accept="application/pdf,.pdf" aria-label="Pilih berkas PDF">
                        <span class="eb-unggah-ikon"><i id="ebookOverlayIcon" class="bi {{ $fileBaru ? 'bi-file-earmark-check-fill' : 'bi-cloud-arrow-up-fill' }}"></i></span>
                        <b id="ebookOverlayText">{{ $namaBaru ?: ($edit ? 'Klik untuk mengganti PDF' : 'Klik atau seret PDF ke sini') }}</b>
                        <small>{{ $fileBaru ? $ukuranBaru.' · siap disimpan' : 'Format PDF, maksimal 2 MB' }}</small>
                    </div>
                    <div class="eb-unggah-muat" wire:loading.flex wire:target="file">
                        <span class="dsb-putar is-kecil"></span> Mengunggah berkas…
                    </div>
                    @error('file') <small class="eb-galat">{{ $message }}</small> @enderror
                    @if ($edit)
                        <small class="eb-bantu">Kosongkan bila tidak ingin mengganti berkas. Tautan yang sudah dikirim ke pelanggan tetap sama.</small>
                    @endif
                </div>
            </div>

            <div class="dsb-kartu">
                <div class="dsb-kartu-isi">
                    <span class="eb-kicker">{{ $edit ? 'Simpan perubahan' : 'Simpan ebook' }}</span>
                    <button type="submit" class="dsb-tombol is-utama eb-simpan" wire:loading.attr="disabled" wire:target="save,file">
                        <span wire:loading.remove wire:target="save" class="eb-isi-tombol"><i class="bi bi-check2-circle"></i><span>{{ $edit ? 'Simpan Perubahan' : 'Simpan Ebook' }}</span></span>
                        <span wire:loading.inline-flex wire:target="save" class="eb-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyimpan…</span></span>
                    </button>
                    <ul class="eb-tips" style="margin-top: 12px;">
                        <li>Ebook <b>Aktif</b> muncul di pilihan bonus saat memproses pesanan.</li>
                        <li>Pelanggan hanya bisa membaca, tidak bisa mengunduh.</li>
                    </ul>
                </div>
            </div>
        </aside>
    </form>

    <!--================== SWEET ALERT EBOOK UPLOAD ==================-->
    <script>
        // Pasang sekali saja di level document agar tetap jalan walau halaman dibuka via wire:navigate (SPA).
        if (!window.__ebookUploadBound) {
            window.__ebookUploadBound = true;

            // Popup glossy tengah, seragam dengan SweetAlert lain di aplikasi
            const ebookGlossyError = (title, text) => {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    title: title,
                    html: text,
                    icon: 'error',
                    background: 'rgba(255, 255, 255, 0.9)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    confirmButtonText: 'Mengerti',
                    customClass: {
                        popup: 'swal-glossy-popup rounded-4 shadow-lg border-0',
                        confirmButton: 'btn-glossy-confirm',
                        title: 'fw-bold'
                    },
                    buttonsStyling: false
                });
            };

            const allowedExt = ['pdf'];
            const maxSize = 2 * 1024 * 1024; // 2MB (mengikuti upload_max_filesize PHP)

            // Tampilkan nama file langsung di area upload agar admin tahu file sudah dipilih
            // textContent, bukan innerHTML: nama berkas tidak boleh menyisipkan markup.
            const showSelectedName = (name) => {
                const txt = document.getElementById('ebookOverlayText');
                const ico = document.getElementById('ebookOverlayIcon');
                const zona = document.getElementById('ebookZona');
                if (txt) txt.textContent = name || 'Klik atau seret PDF ke sini';
                if (ico) ico.className = name ? 'bi bi-file-earmark-check-fill' : 'bi bi-cloud-arrow-up-fill';
                if (zona) zona.classList.toggle('is-siap', !!name);
            };

            // Validasi sebelum upload — delegasi di document (capture) khusus input ebook
            document.addEventListener('change', function(e) {
                if (!e.target || e.target.id !== 'ebookFileInput') return;

                const file = e.target.files[0];
                if (!file) {
                    showSelectedName(null);
                    return;
                }

                const ext = file.name.split('.').pop().toLowerCase();

                if (!allowedExt.includes(ext)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.target.value = '';
                    showSelectedName(null);
                    ebookGlossyError('Harus PDF',
                        'File <b>.' + ext + '</b> tidak bisa diunggah.<br>Ebook <b>hanya boleh format PDF</b> agar bisa dibuka view-only.');
                    return;
                }

                if (file.size > maxSize) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.target.value = '';
                    showSelectedName(null);
                    const mb = (file.size / 1024 / 1024).toFixed(2);
                    ebookGlossyError('Ukuran File Terlalu Besar',
                        'File Anda berukuran <b>' + mb + ' MB</b>, melebihi batas maksimal <b>2 MB</b>.<br>' +
                        'Silakan kompres dulu file-nya, lalu unggah ulang.');
                    return;
                }

                // Valid → langsung tampilkan nama file (sebelum proses upload selesai)
                showSelectedName(file.name);
            }, true);

            // Kegagalan upload dari server (mis. melebihi batas PHP / koneksi putus)
            document.addEventListener('livewire-upload-error', function(e) {
                if (!e.target || e.target.id !== 'ebookFileInput') return;
                ebookGlossyError('Gagal Mengunggah File',
                    'File gagal diunggah karena <b>ukuran melebihi batas server (maks 2 MB)</b> atau koneksi terputus.<br>' +
                    'Kompres file lalu coba lagi.');
            }, true);
        }
    </script>
    <!--================== END SWEET ALERT EBOOK UPLOAD ==================-->
</div>