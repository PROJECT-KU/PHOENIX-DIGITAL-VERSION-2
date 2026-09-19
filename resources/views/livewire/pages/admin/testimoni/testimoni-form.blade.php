@php
    $edit = $mode !== 'create';
    $fotoBaru = $foto && is_object($foto) && ! $errors->has('foto');
    $urlFoto = $fotoBaru ? \App\Support\PratinjauUnggahan::url($foto) : ($existingImage ? asset('storage/img/testimoni/'.$existingImage) : null);
    $anonim = $edit && $testimoni?->anonim;
    $namaTampil = $anonim ? \App\Models\Testimoni::samarkanNama((string) $nama) : (trim((string) $nama) ?: 'Nama pengirim');
    $avPalet = ['#7c3aed', '#0284c7', '#16a34a', '#ea580c', '#db2777', '#4f46e5', '#0d9488', '#d97706'];
    $avWarna = $avPalet[abs(crc32((string) $nama)) % count($avPalet)];
    $panjangPesan = mb_strlen(trim((string) $pesan));
    $statusOpsi = [
        'active' => ['Disetujui', 'bi-check-circle-fill', '#16a34a', 'Tampil di halaman publik'],
        'non-active' => ['Ditolak', 'bi-eye-slash-fill', '#64748b', 'Disembunyikan dari publik'],
    ];
@endphp

<form wire:submit.prevent="save" class="tm-form-tata">
    {{-- ================== KOLOM UTAMA ================== --}}
    <div class="tm-form-utama">
        <div class="dsb-kartu">
            <div class="dsb-kartu-isi">
                <div class="tm-form-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-person-fill"></i></span>
                    <div>
                        <b>Pengirim</b>
                        <span>Tautkan ke pelanggan agar tampil dengan label "Pembeli Asli".</span>
                    </div>
                </div>

                {{-- Nomor diambil dari data pelanggan, TIDAK diketik admin — salah satu
                     digit saja, tautannya meleset & labelnya diam-diam tidak muncul. --}}
                <div class="tm-medan dsb-medan">
                    <span class="dsb-label">Tautkan ke pelanggan <span class="text-muted fw-normal">(opsional)</span></span>
                    <button type="button" onclick="tfPelangganPicker(this)" class="dsb-isian tm-pilih-pelanggan">
                        <span>{{ $pelangganTerpilih?->nama ?? 'Pilih pelanggan — atau biarkan kosong' }}</span>
                        <i class="bi bi-search"></i>
                    </button>
                    @error('customer_id') <small class="tm-galat">{{ $message }}</small> @enderror
                    @if ($pelangganTerpilih)
                        <div class="tm-tautan-info">
                            <span class="dsb-lencana is-hijau"><i class="bi bi-bag-check-fill"></i>Belanja {{ $pelangganTerpilih->belanja_selesai_count }}×</span>
                            @if ($pelangganTerpilih->status_member === 'active')
                                <span class="dsb-lencana is-ungu"><i class="bi bi-star-fill"></i>Member</span>
                            @else
                                <span class="dsb-lencana is-kuning" title="Otomatis jadi member begitu testimoni ini Disetujui"><i class="bi bi-hourglass-split"></i>Belum member</span>
                            @endif
                            <span><i class="bi bi-whatsapp"></i> {{ $no_hp }}</span>
                            <button type="button" class="tm-btn tm-lepas" wire:click="lepasPelanggan"><i class="bi bi-x-lg"></i><span>Lepas</span></button>
                        </div>
                    @else
                        <small class="tm-bantu">Hanya pelanggan dengan pesanan <b>Selesai</b> yang bisa dipilih.</small>
                    @endif
                </div>

                <div class="tm-medan tm-dua">
                    <div class="dsb-medan">
                        <label class="dsb-label" for="tm-nama">Nama <span class="text-danger">*</span></label>
                        <input id="tm-nama" type="text" wire:model.live.debounce.400ms="nama" class="dsb-isian" placeholder="Contoh: Budi Santoso">
                        @error('nama') <small class="tm-galat">{{ $message }}</small> @enderror
                        @if ($anonim)
                            <small class="tm-bantu">Pengirim memilih anonim — di publik tampil sebagai <b>{{ $namaTampil }}</b>.</small>
                        @endif
                    </div>
                    <div class="dsb-medan">
                        <label class="dsb-label" for="tm-peran">Peran / Jabatan</label>
                        <input id="tm-peran" type="text" wire:model.live.debounce.400ms="peran" class="dsb-isian" placeholder="Contoh: Mahasiswa UNAIR">
                        @error('peran') <small class="tm-galat">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="dsb-kartu">
            <div class="dsb-kartu-isi">
                <div class="tm-form-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: #f59e0b"><i class="bi bi-chat-quote-fill"></i></span>
                    <div>
                        <b>Testimoni</b>
                        <span>Rating, isi pesan, dan status tampil di publik.</span>
                    </div>
                </div>

                <div class="tm-medan dsb-medan">
                    <span class="dsb-label">Rating <span class="text-danger">*</span></span>
                    <div class="tm-rating-pilih" role="radiogroup" aria-label="Rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" class="{{ $i <= (int) $rating ? 'is-isi' : '' }}" wire:click="setRating({{ $i }})"
                                role="radio" aria-checked="{{ (int) $rating === $i ? 'true' : 'false' }}" aria-label="{{ $i }} bintang"><i class="bi bi-star-fill"></i></button>
                        @endfor
                    </div>
                    @error('rating') <small class="tm-galat">{{ $message }}</small> @enderror
                </div>

                <div class="tm-medan dsb-medan">
                    <label class="dsb-label" for="tm-pesan">Pesan testimoni <span class="text-danger">*</span></label>
                    <textarea id="tm-pesan" wire:model.live.debounce.400ms="pesan" rows="5" class="dsb-isian tm-pesan-isian" placeholder="Tuliskan testimoni pelanggan di sini…"></textarea>
                    <small class="tm-bantu tm-hitung"><span>Minimal 5 karakter.</span><b>{{ $panjangPesan }} karakter</b></small>
                    @error('pesan') <small class="tm-galat">{{ $message }}</small> @enderror
                </div>

                <div class="tm-medan dsb-medan">
                    <span class="dsb-label">Status <span class="text-danger">*</span></span>
                    @if ($status === 'pending')
                        <small class="tm-bantu" style="margin: 0 0 8px;">Kiriman ini masih <b>menunggu</b> — pilih Disetujui atau Ditolak.</small>
                    @endif
                    <div class="tm-status-pilih" role="radiogroup" aria-label="Status testimoni">
                        @foreach ($statusOpsi as $nilai => [$label, $ikon, $warna, $ket])
                            <label class="tm-status-opsi {{ $status === $nilai ? 'is-pilih' : '' }}" style="--c: {{ $warna }}">
                                <input type="radio" wire:model.live="status" value="{{ $nilai }}">
                                <span><i class="bi {{ $ikon }}"></i></span>
                                <span><b>{{ $label }}</b><small>{{ $ket }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    @if ($status === 'active' && $pelangganTerpilih && $pelangganTerpilih->status_member !== 'active')
                        <small class="tm-bantu" style="color: #7c3aed;"><i class="bi bi-star"></i> Pelanggan ini otomatis menjadi <b>Member</b> saat disimpan.</small>
                    @endif
                    @error('status') <small class="tm-galat">{{ $message }}</small> @enderror

                    @if ($status === 'active')
                        @php $ambang = \App\Models\Testimoni::RATING_MIN_TAMPIL; @endphp
                        <label class="tm-sorot-saklar {{ $sorot ? 'is-nyala' : '' }}">
                            <input type="checkbox" wire:model.live="sorot">
                            <span class="tm-sorot-ikon"><i class="bi {{ $sorot ? 'bi-star-fill' : 'bi-star' }}"></i></span>
                            <span class="tm-sorot-teks">
                                <b>Sorot di beranda</b>
                                <small>Naik ke barisan depan slider testimoni.</small>
                            </span>
                        </label>
                        @if ((int) $rating < $ambang && ! $sorot)
                            {{-- Jujur sejak awal: disetujui saja tidak cukup untuk tampil. --}}
                            <small class="tm-bantu" style="color: #b45309;">
                                <i class="bi bi-eye-slash"></i> Bintang {{ (int) $rating }} <b>tidak tampil di beranda</b> (minimal {{ $ambang }}). Nyalakan Sorot bila tetap ingin ditampilkan.
                            </small>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================== KOLOM SAMPING ================== --}}
    <aside class="tm-form-samping">
        {{-- Pratinjau kartu seperti di halaman publik. --}}
        <div class="tm-pratinjau" aria-label="Pratinjau di halaman publik">
            <span class="tm-pratinjau-label"><i class="bi bi-display"></i> Pratinjau di publik</span>
            <div class="tm-detail-profil">
                <span class="tm-avatar" style="--av: {{ $avWarna }}; flex-basis: 46px; width: 46px; height: 46px; font-size: 1rem;">
                    @if ($urlFoto)
                        <img src="{{ $urlFoto }}" alt="">
                    @else
                        {{ mb_strtoupper(mb_substr(trim((string) $nama), 0, 1)) ?: '?' }}
                    @endif
                </span>
                <div>
                    <b>{{ $namaTampil }}</b>
                    <span>{{ $peran ?: 'Peran pengirim' }}</span>
                </div>
            </div>
            <span class="tm-bintang" style="display: block; margin-top: 8px;">
                @for ($i = 1; $i <= 5; $i++)<i class="bi bi-star-fill {{ $i <= (int) $rating ? '' : 'is-kosong' }}"></i>@endfor
            </span>
            <p>{{ $pesan ?: 'Isi testimoni akan tampil di sini.' }}</p>
            @if ($pelangganTerpilih)
                <span class="dsb-lencana is-hijau" style="margin-top: 10px;"><i class="bi bi-patch-check-fill"></i>Pembeli Asli</span>
            @endif
        </div>

        <div class="dsb-kartu tm-kartu-foto">
            <div class="dsb-kartu-isi">
                <span class="tm-kicker">Foto pengirim <span class="text-muted fw-normal" style="text-transform: none; letter-spacing: 0;">(opsional)</span></span>
                <div class="tm-unggah is-ringkas">
                    <input type="file" id="fotoInput" wire:model="foto" accept="image/png, image/jpeg, image/jpg" aria-label="Pilih foto">
                    <span class="tm-avatar" style="--av: {{ $avWarna }}">
                        @if ($urlFoto)
                            <img src="{{ $urlFoto }}" alt="">
                        @else
                            <i class="bi bi-camera-fill"></i>
                        @endif
                    </span>
                    <div>
                        <b>{{ $urlFoto ? 'Ganti foto' : 'Unggah foto' }}</b>
                        <small>JPG/PNG, maks. 5 MB. Tanpa foto, inisial nama yang dipakai.</small>
                    </div>
                </div>
                <div class="tm-unggah-muat" wire:loading.flex wire:target="foto"><span class="dsb-putar is-kecil"></span> Mengunggah foto…</div>
                <div class="tm-foto-aksi">
                    @include('partials.kamera-foto', ['target' => 'foto', 'kelas' => 'tm-btn'])
                    @if ($urlFoto)
                        <button type="button" class="tm-btn is-bahaya" wire:click="hapusFoto">
                            <i class="bi bi-trash3"></i><span>Hapus foto</span>
                        </button>
                    @endif
                </div>
                @error('foto') <small class="tm-galat">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="dsb-kartu">
            <div class="dsb-kartu-isi">
                <span class="tm-kicker">{{ $edit ? 'Selesai mengubah?' : 'Sudah lengkap?' }}</span>
                <p class="tm-simpan-catatan">
                    @if ($status !== 'active')
                        Testimoni ini disimpan tanpa tampil di halaman depan.
                    @elseif ($sorot)
                        Disorot — tampil di barisan depan halaman depan.
                    @elseif ((int) $rating < \App\Models\Testimoni::RATING_MIN_TAMPIL)
                        Disetujui, tapi bintang {{ (int) $rating }} tidak ditampilkan di halaman depan.
                    @else
                        Testimoni ini akan tampil di halaman depan.
                    @endif
                </p>
                <button type="submit" class="dsb-tombol is-utama tm-simpan" wire:loading.attr="disabled" wire:target="save,foto">
                    <span wire:loading.remove wire:target="save" class="tm-isi-tombol"><i class="bi bi-check2-circle"></i><span>{{ $edit ? 'Simpan Perubahan' : 'Simpan Testimoni' }}</span></span>
                    <span wire:loading.inline-flex wire:target="save" class="tm-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyimpan…</span></span>
                </button>
            </div>
        </div>
    </aside>
</form>

@php
    // Disiapkan di sini, bukan di dalam @json(...): direktif Blade memotong
    // argumennya dengan mencocokkan kurung, jadi array multi-baris bikin parse error.
    $tfPelangganJs = $daftarPelanggan->map(fn ($c) => [
        'id' => $c->id,
        'nama' => $c->nama,
        'no_hp' => $c->no_hp,
        'belanja' => $c->belanja_selesai_count,
        'member' => $c->status_member === 'active',
    ])->values();
@endphp
<script>
    // Daftar pelanggan yang berhak label "Pembeli Asli" (pesanan Selesai).
    window.__tfPelanggan = @json($tfPelangganJs);

    // Nama & nomor pelanggan diketik sendiri oleh pembeli saat checkout —
    // WAJIB di-escape sebelum disisipkan ke HTML (dulu disisipkan mentah).
    window.__tfEsc = (t) => String(t ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    window.tfPelangganPicker = function (btn) {
        if (typeof Swal === 'undefined') return;
        const el = btn.closest('[wire\\:id]');
        if (!el) return;
        const cid = el.getAttribute('wire:id');
        const esc = window.__tfEsc;
        const items = window.__tfPelanggan || [];
        const rows = items.length
            ? items.map((it) =>
                '<button type="button" class="tf-pick-item" data-id="' + esc(it.id) + '" data-search="' + esc(((it.nama || '') + ' ' + (it.no_hp || '')).toLowerCase()) + '">' +
                '<span class="tf-pick-name">' + esc(it.nama) + (it.member ? ' <span class="dsb-lencana is-ungu">member</span>' : '') + '</span>' +
                '<span class="tf-pick-sub">' + esc(it.no_hp) + ' · sudah belanja ' + esc(it.belanja) + ' kali</span>' +
                '</button>').join('')
            : '<div class="tf-pick-empty">Belum ada pelanggan dengan pesanan Selesai.</div>';

        Swal.fire({
            title: 'Pilih Pelanggan',
            html: '<input id="tfPickSearch" class="form-control mb-2" placeholder="Ketik nama atau nomor…">' +
                '<div id="tfPickList" class="tf-pick-list">' + rows + '</div>',
            background: 'rgba(255, 255, 255, 0.95)',
            backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
            buttonsStyling: false, showConfirmButton: false, showCloseButton: true, width: 480, padding: '1.25rem',
            didOpen: function () {
                const search = document.getElementById('tfPickSearch');
                const listEl = document.getElementById('tfPickList');
                search?.addEventListener('input', () => {
                    const q = search.value.toLowerCase();
                    listEl.querySelectorAll('.tf-pick-item').forEach((b) => { b.style.display = b.dataset.search.includes(q) ? '' : 'none'; });
                });
                setTimeout(() => search?.focus(), 100);
                listEl.querySelectorAll('.tf-pick-item').forEach((b) => b.addEventListener('click', () => {
                    // set() memicu updatedCustomerId() -> nama & no_hp terisi sendiri
                    window.Livewire?.find(cid).set('customer_id', b.dataset.id);
                    Swal.close();
                }));
            }
        });
    };

    // Validasi foto sebelum diunggah. Dipasang SEKALI di dokumen (capture) —
    // versi lama memakai DOMContentLoaded yang tidak berjalan lewat wire:navigate.
    if (!window.__testimoniFotoTerpasang) {
        window.__testimoniFotoTerpasang = true;
        document.addEventListener('change', function (e) {
            if (!e.target || e.target.id !== 'fotoInput') return;
            const file = e.target.files[0];
            if (!file) return;
            const tolak = (judul, teks) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.target.value = '';
                if (typeof Swal !== 'undefined') Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, icon: 'error', title: judul, text: teks });
            };
            if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                tolak('Format tidak didukung', 'Gunakan foto JPG atau PNG.');
                return;
            }
            if (file.size > 5 * 1024 * 1024) tolak('Ukuran terlalu besar', 'Maksimal ukuran foto adalah 5 MB.');
        }, true);
    }
</script>
