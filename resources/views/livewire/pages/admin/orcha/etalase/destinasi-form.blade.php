@section('title')
{{ $ubah ? 'Ubah Destinasi' : 'Tambah Destinasi' }} || lemon
@stop

@php
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanGambar = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;

    $ringkas = function ($angka) {
        $angka = (int) $angka;

        return $angka >= 1000
            ? rtrim(rtrim(number_format($angka / 1000, 1, ',', '.'), '0'), ',') . 'k'
            : (string) $angka;
    };

    // Galeri pratinjau: foto utama lebih dulu, lalu gambar tambahan — urutan
    // yang sama dengan jendela detail di website.
    $galeri = array_values(array_filter(array_merge(
        [$gambar ? $gambar->temporaryUrl() : $tautanGambar($gambarLama)],
        array_map($tautanGambar, $subFotoTetap),
        array_map(fn ($berkas) => $berkas->temporaryUrl(), $subFoto),
    )));
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => $ubah ? 'Ubah Destinasi' : 'Tambah Destinasi',
            'keterangan' => 'Tersimpan di server Orcha, langsung tampil di halaman Destinasi Populer.',
        ])

        <form wire:submit="simpan" class="orcha-form">
            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-8">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1 orcha-judul-ikon">
                                <i class="bi bi-signpost-2 text-primary"></i> Identitas Destinasi
                            </h6>
                            <p class="text-muted small mb-3">Nama dan tempatnya — yang dicari pengunjung di daftar.</p>

                            <div class="row g-3">
                                <div class="col-12 col-md-7">
                                    <label class="form-label small fw-semibold">
                                        Nama destinasi <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                        wire:model="nama" placeholder="Contoh: Bromo Tengger Semeru">
                                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-6 col-md-5">
                                    <label class="form-label small fw-semibold">
                                        Wilayah <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('wilayah') is-invalid @enderror"
                                        wire:model.live="wilayah">
                                        @foreach ($daftarWilayah as $kunci => $label)
                                            <option value="{{ $kunci }}" @selected($wilayah === $kunci)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('wilayah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Menyaring pilihan provinsi di sebelah.</div>
                                </div>

                                <div class="col-6 col-md-7">
                                    <label class="form-label small fw-semibold">Provinsi</label>

                                    {{-- Dipilih lewat pemilih berdaftar, bukan diketik.

                                         Bentuknya sama dengan pemilih merek & nama unit di
                                         formulir armada — admin sudah mengenalnya, dan
                                         dua pola berbeda untuk pekerjaan yang sama hanya
                                         menambah yang harus diingat.

                                         Provinsi yang diketik bebas menghasilkan ejaan
                                         berbeda untuk tempat yang sama — "DIY",
                                         "Yogyakarta", "D.I. Yogyakarta" — dan penyaringan
                                         di halaman publik ikut tidak dapat diandalkan. --}}
                                    <button type="button" onclick="orchaPilihProvinsi(this)"
                                        class="form-select text-start orcha-picker @error('provinsi') is-invalid @enderror">
                                        @if (trim($provinsi) !== '')
                                            <span class="text-dark fw-semibold">{{ $provinsi }}</span>
                                        @else
                                            <span class="text-muted">— Pilih provinsi —</span>
                                        @endif
                                    </button>

                                    @error('provinsi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    <div class="form-text">
                                        {{ count($daftarProvinsi) }} provinsi di
                                        {{ $daftarWilayah[$wilayah] ?? 'wilayah ini' }} — yang belum
                                        terdaftar bisa ditambahkan dari pemilihnya.
                                    </div>
                                </div>

                                <div class="col-6 col-md-5">
                                    <label class="form-label small fw-semibold">Perkiraan pengunjung</label>
                                    <input type="number" min="0"
                                        class="form-control @error('totalPengunjung') is-invalid @enderror"
                                        wire:model.live="totalPengunjung" placeholder="0">
                                    @error('totalPengunjung') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Tampil sebagai lencana "sekian pengunjung diantar".</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Keterangan</label>
                                    <textarea rows="3"
                                        class="form-control @error('deskripsi') is-invalid @enderror"
                                        wire:model.live.debounce.400ms="deskripsi"
                                        placeholder="Apa yang membuat tempat ini layak didatangi, dan apa yang perlu disiapkan pengunjung."></textarea>
                                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">
                                        {{ mb_strlen($deskripsi) }}/1000 — dibaca utuh di jendela detail destinasi.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1 orcha-judul-ikon">
                                <i class="bi bi-images text-primary"></i> Foto Destinasi
                            </h6>
                            <p class="text-muted small mb-3">Satu foto utama untuk kartunya, dan sampai
                                {{ $batasSubFoto }} gambar tambahan untuk galeri detailnya.</p>

                            <label class="form-label small fw-semibold">Foto utama</label>

                            {{-- Pratinjau dan pemilih berkas satu kotak: keduanya satu
                                 keputusan, dan gambar yang melayang tanpa pembatas tidak
                                 terbaca sebagai pasangan isiannya. --}}
                            <div class="orcha-foto-kotak @error('gambar') galat @enderror mb-3">
                                <div class="orcha-foto-rupa">
                                    @if ($gambar)
                                        <img src="{{ $gambar->temporaryUrl() }}" alt="">
                                    @elseif ($tautanGambar($gambarLama))
                                        <img src="{{ $tautanGambar($gambarLama) }}" alt="">
                                    @else
                                        <span class="orcha-foto-kosong"><i class="bi bi-image"></i></span>
                                    @endif
                                </div>

                                <div class="orcha-foto-isi">
                                    <input type="file"
                                        class="form-control form-control-sm @error('gambar') is-invalid @enderror"
                                        wire:model="gambar" accept="image/*">
                                    <div class="form-text">Maksimal 4 MB. Kosong berarti gambar lama tetap dipakai.</div>
                                    <div wire:loading wire:target="gambar" class="text-muted small">Mengunggah…</div>
                                    @error('gambar') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="orcha-sub-foto">
                                <div class="orcha-sub-kepala">
                                    <span class="judul">
                                        <i class="bi bi-collection"></i>
                                        Gambar tambahan
                                    </span>
                                    <span class="ket">
                                        Tampil di galeri detail — sisa {{ $this->sisaSubFoto() }} dari {{ $batasSubFoto }}
                                    </span>
                                </div>

                                @if ($subFotoTetap || $subFoto)
                                    <div class="orcha-sub-petak">
                                        @foreach ($subFotoTetap as $jalur)
                                            <div class="petak" wire:key="sub-tetap-{{ md5($jalur) }}">
                                                <img src="{{ $tautanGambar($jalur) }}" alt="">
                                                {{-- Berkasnya baru dibuang di Orcha saat disimpan,
                                                     jadi meninggalkan halaman tanpa menyimpan tidak
                                                     menghilangkan apa pun. --}}
                                                <button type="button" class="buang"
                                                    wire:click="hapusSubFoto('{{ $jalur }}')"
                                                    title="Keluarkan gambar ini">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        @endforeach

                                        @foreach ($subFoto as $urutan => $berkas)
                                            <div class="petak baru" wire:key="sub-baru-{{ $urutan }}">
                                                <img src="{{ $berkas->temporaryUrl() }}" alt="">
                                                <span class="tanda">Baru</span>
                                                <button type="button" class="buang"
                                                    wire:click="batalkanSubFoto({{ $urutan }})"
                                                    title="Batalkan gambar ini">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="orcha-sub-kosong">
                                        <i class="bi bi-image"></i>
                                        Belum ada gambar tambahan.
                                    </p>
                                @endif

                                @if ($this->sisaSubFoto() > 0)
                                    <input type="file" multiple
                                        class="form-control form-control-sm mt-2 @error('subFoto.0') is-invalid @enderror"
                                        wire:model="subFoto" accept="image/*">
                                    <div class="form-text">Bisa pilih beberapa sekaligus — maksimal 2 MB per gambar.</div>
                                @else
                                    <p class="orcha-sub-penuh mt-2">
                                        <i class="bi bi-check-circle"></i>
                                        Sudah {{ $batasSubFoto }} gambar — hapus salah satu untuk menambah.
                                    </p>
                                @endif

                                <div wire:loading wire:target="subFoto" class="text-muted small mt-1">Mengunggah…</div>
                                @error('subFoto') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @error('subFoto.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kartu kanan berada DI DALAM pembungkus lengket ini, dan palang
                     tombolnya jadi anak langsungnya — elemen sticky tidak bisa keluar
                     dari kotak induknya, dan pembungkus yang kosong membuat kolom
                     kanan tampak ditinggalkan begitu kolom kiri memanjang. --}}
                <div class="col-12 col-xl-4">
                    <div class="orcha-lengket orcha-lengket-panjang">

                        {{-- Pratinjau: kartu yang sama dengan yang dilihat pengunjung.
                             Tanpa ini kolom kanan hanya berisi tombol, dan admin baru
                             tahu hasilnya setelah membuka website di tab lain. --}}
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 orcha-judul-ikon">
                                    <i class="bi bi-eye text-primary"></i> Pratinjau di Website
                                </h6>

                                <div class="orcha-pratinjau">
                                    <div class="orcha-dest-foto">
                                        @if ($galeri)
                                            <img src="{{ $galeri[0] }}" alt="">
                                        @else
                                            <span class="orcha-foto-kosong"><i class="bi bi-image"></i></span>
                                        @endif

                                        <span class="orcha-dest-lencana">
                                            <i class="bi bi-people-fill"></i>
                                            {{ $ringkas($totalPengunjung) }} pengunjung
                                        </span>

                                        <div class="orcha-dest-judul">
                                            <strong>{{ $nama ?: 'Nama destinasi' }}</strong>
                                            @if ($provinsi)
                                                <span><i class="bi bi-geo-alt-fill"></i> {{ $provinsi }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="orcha-dest-isi">
                                        <p class="ket">
                                            {{ $deskripsi ?: 'Keterangan destinasi akan tampil di sini.' }}
                                        </p>

                                        @if (count($galeri) > 1)
                                            <div class="orcha-dest-galeri">
                                                @foreach (array_slice($galeri, 1, $batasSubFoto) as $foto)
                                                    <img src="{{ $foto }}" alt="">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 orcha-judul-ikon">
                                    <i class="bi bi-clipboard-data text-primary"></i> Ringkasan
                                </h6>

                                <div class="orcha-ringkas">
                                    <div>
                                        <span class="label">Wilayah</span>
                                        <span class="nilai">{{ $daftarWilayah[$wilayah] ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="label">Gambar</span>
                                        <span class="nilai">
                                            {{ count($galeri) }} foto
                                            @if ($this->sisaSubFoto() > 0)
                                                <small class="text-muted">· sisa {{ $this->sisaSubFoto() }}</small>
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <span class="label">Keterangan</span>
                                        <span class="nilai">
                                            {{ $deskripsi ? mb_strlen($deskripsi).' huruf' : 'Belum ditulis' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol di dasar kolom, sebagaimana lazimnya — tetapi dipaku,
                             supaya tetap terlihat berapa pun panjang isian di kiri. --}}
                        <div class="orcha-aksi-paku">
                            <div class="d-grid gap-2">
                                <button type="submit" class="orcha-btn orcha-btn-utama" wire:loading.attr="disabled"
                                    wire:target="simpan">
                                    <i class="bi bi-save"></i>
                                    <span wire:loading.remove wire:target="simpan">
                                        {{ $ubah ? 'Simpan Perubahan' : 'Tambah Destinasi' }}
                                    </span>
                                    <span wire:loading wire:target="simpan">Menyimpan ke Orcha…</span>
                                </button>
                                <a href="{{ route('admin.orcha.destinasi') }}" wire:navigate
                                    class="orcha-btn orcha-btn-lembut">
                                    Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')

    {{-- ============ PEMILIH PROVINSI ============

         Data disegarkan tiap render DI LUAR penjaga, sedangkan pemasangan
         fungsinya dijaga sekali saja. Kalau datanya ikut di dalam penjaga,
         provinsi yang baru ditambahkan tidak akan pernah terbaca — Livewire
         tidak menjalankan ulang <script> inline saat me-render ulang, jadi
         nilainya membeku pada keadaan pemuatan pertama.

         Pemilihnya ditulis tersendiri, tidak memakai ulang pemilih armada:
         yang di sana mengenal tiga tingkat (merek → unit → tipe) beserta
         aturannya masing-masing, dan menyeretnya ke sini demi satu daftar datar
         akan membuat keduanya saling mengunci saat salah satunya berubah. --}}
    <script>
        window.__orchaPetaProvinsi = @json($petaProvinsi);
        window.__orchaProvinsiKustom = @json($provinsiKustom);
        window.__orchaWilayahDipilih = @json($wilayah);

        if (!window.__orchaProvinsiTerpasang) {
            window.__orchaProvinsiTerpasang = true;

            const provEsc = (t) => String(t).replace(/[&<>"']/g, (m) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            }[m]));

            // Hanya provinsi tambahan yang boleh dihapus dari daftar; yang
            // bawaan ikut versi kode dan dipakai destinasi yang sudah ada.
            const provIdKustom = (nama) => {
                const cocok = (window.__orchaProvinsiKustom || []).find((e) => e.nama === nama);

                return cocok ? cocok.id : null;
            };

            const provDaftar = () => Object.keys(window.__orchaPetaProvinsi || {})
                .filter((n) => window.__orchaPetaProvinsi[n] === window.__orchaWilayahDipilih)
                .sort((a, b) => a.localeCompare(b, 'id'));

            const provBaris = (daftar) => daftar.length
                ? daftar.map((n) => {
                    const id = provIdKustom(n);

                    return '<div class="orcha-pick-row">'
                        + '<button type="button" class="orcha-pick-item" data-nilai="' + provEsc(n)
                        + '" data-cari="' + provEsc(String(n).toLowerCase()) + '">'
                        + '<i class="bi bi-geo-alt me-2" style="color:#1d6fa5;"><\/i>' + provEsc(n)
                        + '<\/button>'
                        + (id ? '<button type="button" class="orcha-pick-del" data-id="' + id
                            + '" title="Hapus dari daftar"><i class="bi bi-trash3"><\/i><\/button>' : '')
                        + '<\/div>';
                }).join('')
                : '<div class="orcha-pick-empty">Belum ada provinsi di wilayah ini. Pakai "Tulis sendiri" di bawah.<\/div>';

            window.orchaPilihProvinsi = function (tombol) {
                if (typeof Swal === 'undefined') return;

                const wadah = tombol.closest('[wire\\:id]');
                if (!wadah) return;

                const cid = wadah.getAttribute('wire:id');
                const komponen = () => window.Livewire && window.Livewire.find(cid);

                const pasangPendengar = () => {
                    const daftarEl = document.getElementById('orchaProvDaftar');
                    if (!daftarEl) return;

                    daftarEl.querySelectorAll('.orcha-pick-item').forEach((b) => {
                        b.addEventListener('click', () => {
                            komponen() && komponen().set('provinsi', b.dataset.nilai);
                            Swal.close();
                        });
                    });

                    daftarEl.querySelectorAll('.orcha-pick-del').forEach((b) => {
                        b.addEventListener('click', (ev) => {
                            // Jangan sampai menghapus berarti sekaligus memilih.
                            ev.stopPropagation();
                            b.disabled = true;
                            komponen() && komponen().call('hapusProvinsi', Number(b.dataset.id));
                        });
                    });
                };

                // Daftarnya digambar ulang di tempat sesudah ada entri ditambah
                // atau dihapus, tanpa menutup popupnya.
                const gambarUlang = () => {
                    const daftarEl = document.getElementById('orchaProvDaftar');
                    if (!daftarEl) return;
                    daftarEl.innerHTML = provBaris(provDaftar());
                    pasangPendengar();
                    const cari = document.getElementById('orchaProvCari');
                    if (cari) cari.dispatchEvent(new Event('input'));
                };
                window.__orchaProvGambarUlang = gambarUlang;

                Swal.fire({
                    title: 'Pilih Provinsi',
                    html: '<input id="orchaProvCari" class="form-control mb-2" placeholder="Ketik untuk mencari provinsi…">'
                        + '<div id="orchaProvDaftar" class="orcha-pick-list">' + provBaris(provDaftar()) + '<\/div>'
                        + '<div id="orchaProvKosong" class="orcha-pick-empty" style="display:none">Tidak ada yang cocok. Pakai "Tulis sendiri" di bawah.<\/div>'
                        + '<button type="button" id="orchaProvManual" class="orcha-pick-item mt-2" style="border-style:dashed;">'
                        + '<i class="bi bi-plus-circle me-2" style="color:#64748b;"><\/i>Tulis sendiri &amp; tambahkan ke daftar…<\/button>',
                    background: 'rgba(255, 255, 255, 0.92)',
                    backdrop: 'rgba(29, 111, 165, 0.15)',
                    customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                    buttonsStyling: false, showConfirmButton: false, showCloseButton: true,
                    width: 480, padding: '1.25rem',
                    willClose: () => { window.__orchaProvGambarUlang = null; },
                    didOpen: () => {
                        const cari = document.getElementById('orchaProvCari');
                        const daftarEl = document.getElementById('orchaProvDaftar');
                        const kosong = document.getElementById('orchaProvKosong');

                        if (cari) {
                            cari.addEventListener('input', () => {
                                const q = cari.value.toLowerCase().trim();
                                let terlihat = 0;
                                daftarEl.querySelectorAll('.orcha-pick-row').forEach((baris) => {
                                    const b = baris.querySelector('.orcha-pick-item');
                                    const cocok = b.dataset.cari.includes(q);
                                    baris.style.display = cocok ? '' : 'none';
                                    if (cocok) terlihat++;
                                });
                                // Daftar kosong tanpa keterangan terbaca seperti
                                // halaman rusak, bukan seperti "tidak ada yang cocok".
                                kosong.style.display = terlihat === 0 && provDaftar().length ? '' : 'none';
                            });
                            setTimeout(() => cari.focus(), 100);
                        }

                        pasangPendengar();

                        const manual = document.getElementById('orchaProvManual');
                        if (manual) manual.addEventListener('click', () => {
                            Swal.fire({
                                title: 'Tambah Provinsi',
                                input: 'text',
                                inputPlaceholder: 'mis. Papua Barat Laut',
                                text: 'Ditambahkan ke wilayah yang sedang dipilih.',
                                background: 'rgba(255, 255, 255, 0.92)',
                                backdrop: 'rgba(29, 111, 165, 0.15)',
                                customClass: {
                                    popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold',
                                    confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel',
                                },
                                buttonsStyling: false, showCancelButton: true,
                                confirmButtonText: 'Tambahkan', cancelButtonText: 'Batal',
                                inputValidator: (v) => (v && v.trim() !== '') ? undefined : 'Masih kosong.',
                            }).then((h) => {
                                if (!h.isConfirmed || !h.value) return;
                                // Sekali ditulis langsung terdaftar — bukan hanya
                                // mengisi isian lalu hilang saat halaman ditutup.
                                komponen() && komponen().call('tambahProvinsi', h.value.trim());
                            });
                        });
                    },
                });
            };

            // Daftar terbaru dari server: dipasang ke global, lalu popup yang
            // sedang terbuka digambar ulang di tempat.
            window.addEventListener('orcha-provinsi-segar', function (e) {
                const d = e.detail || {};
                if (d.peta) window.__orchaPetaProvinsi = d.peta;
                if (d.kustom) window.__orchaProvinsiKustom = d.kustom;
                if (window.__orchaProvGambarUlang) window.__orchaProvGambarUlang();
            });
        }
    </script>

    <style>
        .orcha-sub-foto {
            padding: .85rem;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            background: #fbfdff;
        }

        .orcha-sub-kepala {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: .1rem .6rem;
            margin-bottom: .6rem;
        }

        .orcha-sub-kepala .judul {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .82rem;
            font-weight: 700;
            color: #0f2d4a;
        }

        .orcha-sub-kepala .ket { font-size: .74rem; color: #64748b; }

        /* Petak seukuran sama supaya deretannya rapi berapa pun rasio
           gambarnya — kartu di website pun memotongnya begitu. */
        .orcha-sub-petak {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(6.5rem, 1fr));
            gap: .6rem;
        }

        .orcha-sub-petak .petak {
            position: relative;
            aspect-ratio: 4 / 3;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e3e8ef;
            background: #eef2f6;
        }

        .orcha-sub-petak .petak img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Yang baru dipilih ditandai: sebelum disimpan, keduanya terlihat sama
           padahal yang satu belum tersimpan di mana pun. */
        .orcha-sub-petak .petak.baru { border-color: #9fd0b4; }

        .orcha-sub-petak .tanda {
            position: absolute;
            left: .35rem;
            bottom: .35rem;
            padding: .05rem .35rem;
            border-radius: 5px;
            background: rgba(26, 138, 82, .92);
            color: #fff;
            font-size: .62rem;
            font-weight: 700;
        }

        .orcha-sub-petak .buang {
            position: absolute;
            top: .3rem;
            right: .3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.4rem;
            height: 1.4rem;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: rgba(15, 45, 74, .78);
            color: #fff;
            font-size: .62rem;
            line-height: 1;
        }

        .orcha-sub-petak .buang:hover { background: #c2323c; }

        .orcha-sub-kosong,
        .orcha-sub-penuh {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin: 0;
            font-size: .78rem;
            color: #64748b;
        }

        .orcha-sub-penuh { color: #1a6b43; }

        /* Pratinjau kartu destinasi — bentuknya menirukan kartu di website,
           supaya admin melihat hasilnya sebelum menyimpan, bukan setelah
           membuka website di tab lain. */
        .orcha-dest-foto {
            position: relative;
            aspect-ratio: 4 / 3;
            background: #eef2f6;
        }

        .orcha-dest-foto > img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Gradasi gelap di dasar foto: judul putih di atas foto terang tidak
           terbaca tanpa itu. */
        .orcha-dest-foto::after {
            content: '';
            position: absolute;
            inset: auto 0 0 0;
            height: 60%;
            background: linear-gradient(to top, rgba(15, 45, 74, .88), transparent);
        }

        .orcha-dest-lencana {
            position: absolute;
            top: .55rem;
            left: .55rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .55rem;
            border-radius: 2rem;
            background: #ffc74e;
            color: #0f2d4a;
            font-size: .68rem;
            font-weight: 700;
        }

        .orcha-dest-judul {
            position: absolute;
            inset: auto .8rem .7rem;
            z-index: 2;
            color: #fff;
        }

        .orcha-dest-judul strong {
            display: block;
            font-size: 1rem;
            line-height: 1.25;
        }

        .orcha-dest-judul span {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            margin-top: .15rem;
            font-size: .74rem;
            color: #e2e8f0;
        }

        .orcha-dest-isi { padding: .85rem; }

        .orcha-dest-isi .ket {
            margin: 0;
            font-size: .78rem;
            color: #475569;
            line-height: 1.5;
        }

        .orcha-dest-galeri {
            display: flex;
            gap: .4rem;
            margin-top: .7rem;
        }

        .orcha-dest-galeri img {
            width: 2.6rem;
            height: 2.6rem;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e3ecf3;
        }

        /* Ringkasan: label kiri, nilai kanan — angka dan keterangannya jatuh di
           satu garis tegak, sama seperti daftar tarif di formulir armada. */
        .orcha-ringkas {
            display: grid;
            gap: .4rem;
            padding: .75rem .85rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8fbfd, #eef6fb);
            border: 1px solid #e3ecf3;
            font-size: .8rem;
        }

        .orcha-ringkas > div {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: .6rem;
        }

        .orcha-ringkas .label { color: #64748b; }

        .orcha-ringkas .nilai { font-weight: 600; color: #0f2d4a; }
    </style>
</div>
