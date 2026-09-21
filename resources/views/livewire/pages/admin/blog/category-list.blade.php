@section('title')
Kategori Artikel || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.blog.partials.artikel-gaya')

    @php
        $bolehTambah = (bool) auth()->user()?->hasPermission('create_blog');
        $bolehUbah = (bool) auth()->user()?->hasPermission('edit_blog');
        $bolehHapus = (bool) auth()->user()?->hasPermission('delete_blog');
    @endphp

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Kategori Artikel</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Pengelompokan tulisan di halaman blog. Mengubah nama kategori ikut memperbarui semua artikel yang memakainya.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.blog.index') }}" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Daftar Artikel</span>
                </a>
                @if ($bolehTambah)
                    <button type="button" class="dsb-tombol is-utama add-category-btn">
                        <i class="bi bi-plus-lg"></i><span>Tambah Kategori</span>
                    </button>
                @endif
            </div>
        </header>

        <section class="dsb-kartu bl-saring">
            <div class="dsb-kartu-isi bl-saring-isi">
                <div class="dsb-cari">
                    <i class="bi bi-search"></i>
                    <input type="search" class="dsb-isian" wire:model.live.debounce.300ms="search" placeholder="Cari kategori…" aria-label="Cari kategori">
                    @if ($search)
                        <button type="button" class="dsb-cari-hapus" wire:click="$set('search', '')" title="Hapus pencarian"><i class="bi bi-x-lg"></i></button>
                    @endif
                </div>
                <select class="dsb-isian bl-pilih" wire:model.live="urut" aria-label="Urutkan kategori">
                    <option value="nama">Nama A-Z</option>
                    <option value="jumlah">Paling banyak artikel</option>
                </select>

                <span class="dsb-chip is-memuat" wire:loading.inline-flex wire:target="search,urut,gotoPage,nextPage,previousPage">
                    <span class="dsb-putar is-kecil"></span>Memuat…
                </span>
            </div>
        </section>

        <div class="bl-kat-kerangka" wire:loading.grid wire:target="search,urut,gotoPage,nextPage,previousPage">
            @for ($i = 0; $i < 6; $i++)
                <div>
                    <span class="bl-tulang is-ubin"></span>
                    <span class="bl-kat-isi">
                        <span class="bl-tulang" style="width: 60%"></span>
                        <span class="bl-tulang" style="width: 35%"></span>
                    </span>
                </div>
            @endfor
        </div>

        <section class="bl-wadah" wire:loading.class="bl-sembunyi" wire:target="search,urut,gotoPage,nextPage,previousPage">
            @if ($categories->isEmpty())
                <div class="dsb-kartu">
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi bi-tags"></i></span>
                        <p class="dsb-kosong-judul">{{ $search ? 'Kategori tidak ditemukan' : 'Belum ada kategori' }}</p>
                        <p class="dsb-kosong-ket">{{ $search ? 'Coba kata kunci lain.' : 'Tekan "Tambah Kategori" untuk membuat yang pertama.' }}</p>
                    </div>
                </div>
            @else
                <div class="bl-kat-rak">
                    @foreach ($categories as $item)
                        @php $dipakai = (int) ($counts[$item->name] ?? 0); @endphp
                        <div class="bl-kat" wire:key="kategori-{{ $item->id }}">
                            @if ($editingId === $item->id)
                                <span class="bl-kat-ikon"><i class="bi bi-pencil"></i></span>
                                <input type="text" class="dsb-isian @error('editingName') is-galat @enderror"
                                    wire:model="editingName" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit" autofocus
                                    aria-label="Nama kategori">
                                <div class="bl-kat-aksi">
                                    <button type="button" class="bl-btn bl-btn-ikon is-terbit" wire:click="saveEdit" title="Simpan perubahan"><i class="bi bi-check-lg"></i></button>
                                    <button type="button" class="bl-btn bl-btn-ikon" wire:click="cancelEdit" title="Batal"><i class="bi bi-x-lg"></i></button>
                                </div>
                                @error('editingName') <span class="bl-galat" style="flex: 1 1 100%;">{{ $message }}</span> @enderror

                                <div class="bl-kat-baris">
                                    <input type="text" class="dsb-isian @error('editingDescription') is-galat @enderror"
                                        wire:model="editingDescription" maxlength="255"
                                        placeholder="Deskripsi singkat kategori (dipakai halaman kategori di blog)"
                                        aria-label="Deskripsi kategori">
                                    @error('editingDescription') <span class="bl-galat">{{ $message }}</span> @enderror

                                    @php $lain = $this->kategoriLain($item->id); @endphp
                                    @if ($lain)
                                        <div class="bl-tag-isi">
                                            <select class="dsb-isian" wire:model.live="gabungKe" aria-label="Gabungkan ke kategori lain">
                                                <option value="">Gabungkan ke kategori lain…</option>
                                                @foreach ($lain as $nama)
                                                    <option value="{{ $nama }}">{{ $nama }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="bl-btn pcek-konfirmasi" @disabled($gabungKe === '')
                                                data-action="gabungkanTerpilih"
                                                data-title="Gabungkan kategori?"
                                                data-text="Semua artikel dipindah ke kategori tujuan, lalu kategori ini dihapus. Artikelnya sendiri tidak terhapus."
                                                data-confirm="Ya, gabungkan" data-icon="warning"
                                                title="Gabungkan kategori ini ke kategori tujuan"><i class="bi bi-sign-merge-left"></i><span>Gabungkan</span></button>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="bl-kat-ikon"><i class="bi bi-tag-fill"></i></span>
                                <div class="bl-kat-teks">
                                    <b>{{ $item->name }}</b>
                                    <small>{{ $dipakai }} artikel</small>
                                    @if ($item->description)
                                        <p class="bl-kat-ket">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <div class="bl-kat-aksi">
                                    <a wire:navigate href="{{ route('admin.blog.index', ['category' => $item->name]) }}"
                                        class="bl-btn bl-btn-ikon" title="Kelola artikel kategori ini"><i class="bi bi-journals"></i></a>
                                    <a href="{{ route('blog.index', ['kategori' => $item->name]) }}" target="_blank" rel="noopener"
                                        class="bl-btn bl-btn-ikon" title="Lihat kategori ini di blog publik"><i class="bi bi-box-arrow-up-right"></i></a>
                                    @if ($bolehUbah)
                                        <button type="button" class="bl-btn bl-btn-ikon" wire:click="startEdit({{ $item->id }})" title="Ubah nama"><i class="bi bi-pencil-square"></i></button>
                                    @endif
                                    @if ($bolehHapus)
                                        @if ($dipakai === 0)
                                            <button type="button" class="bl-btn bl-btn-ikon is-bahaya pcek-konfirmasi"
                                                data-action="delete" data-arg="{{ $item->id }}"
                                                data-title="Hapus kategori ini?"
                                                data-text="Kategori dihapus permanen. Artikel tidak ikut terhapus."
                                                data-confirm="Ya, hapus" data-icon="warning" title="Hapus kategori"><i class="bi bi-trash"></i></button>
                                        @else
                                            {{-- Masih dipakai artikel: tombolnya sengaja dimatikan, bukan
                                                 dibiarkan lalu ditolak server. Alasannya ditulis di judul
                                                 supaya tidak terbaca sebagai tombol rusak. --}}
                                            <span class="bl-btn bl-btn-ikon is-mati" title="Masih dipakai {{ $dipakai }} artikel — ubah kategori artikel tersebut dulu"><i class="bi bi-trash"></i></span>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($categories->hasPages())
                    <div class="bl-halaman">{{ $categories->links('vendor.pagination') }}</div>
                @endif
            @endif
        </section>
    </div>

    @include('livewire.layout.sweetalert')

    <style>
        .bl-kat .dsb-isian { height: 38px; }
        .bl-btn.is-mati { color: #cbd5e1; border-color: #f1f5f9; cursor: not-allowed; }
        .swal-cat-icon { border: none !important; width: 74px !important; height: 74px !important; margin: .3rem auto 0 !important; background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; display: flex !important; align-items: center !important; justify-content: center !important; line-height: 1; border-radius: 50%; box-shadow: 0 12px 26px rgba(109, 40, 217, .34); }
        .swal-cat-icon .swal2-icon-content { display: flex !important; align-items: center; justify-content: center; width: 100%; height: 100%; line-height: 1; }
        .swal-cat-icon i, .swal-cat-icon i::before { display: inline-flex; align-items: center; justify-content: center; line-height: 1; font-size: 32px !important; }
        .swal-cat-title { font-weight: 800 !important; color: #1c1f26 !important; font-size: 1.3rem !important; margin-top: .8rem !important; }
        .swal-cat-sub { color: #64748b; font-size: .88rem; margin: -2px 0 8px; }
        .swal-cat-input { border-radius: 12px !important; border: 1.5px solid #e9edf3 !important; padding: 11px 15px !important; font-size: 1rem !important; box-shadow: none !important; }
        .swal-cat-input:focus { border-color: #7c3aed !important; box-shadow: 0 0 0 .2rem rgba(124, 58, 237, .16) !important; }
    </style>

    {{-- Popup tambah kategori. Dipasang SEKALI lewat penjaga global: pola lama
         mendaftarkan pendengar baru di tiap livewire:navigated, sehingga
         setelah berpindah halaman beberapa kali dialognya muncul berulang. --}}
    @once
    <script data-navigate-once>
        if (!window.__blKategoriTambah) {
            window.__blKategoriTambah = true;

            document.addEventListener('click', function (e) {
                const tombol = e.target.closest && e.target.closest('.add-category-btn');
                if (!tombol || typeof Swal === 'undefined') return;
                e.preventDefault();

                const induk = tombol.closest('[wire\\:id]');
                if (!induk) return;

                const ket = document.createElement('div');
                ket.className = 'swal-cat-sub';
                ket.textContent = 'Beri nama kategori untuk mengelompokkan artikel blog.';

                const ikon = document.createElement('i');
                ikon.className = 'bi bi-tag-fill';

                Swal.fire({
                    iconHtml: ikon,
                    title: 'Tambah Kategori',
                    html: ket,
                    input: 'text',
                    inputPlaceholder: 'Contoh: Tips & Panduan',
                    inputAttributes: { autocapitalize: 'off', autocorrect: 'off', maxlength: 60 },
                    showCancelButton: true,
                    reverseButtons: true,
                    focusConfirm: false,
                    confirmButtonText: 'Simpan Kategori',
                    cancelButtonText: 'Batal',
                    inputValidator: (v) => (!v || v.trim().length < 2) ? 'Nama kategori minimal 2 karakter.' : undefined,
                    background: 'rgba(255,255,255,.96)',
                    backdrop: 'rgba(124, 58, 237, .18)',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'shadow rounded-4',
                        icon: 'swal-cat-icon',
                        title: 'swal-cat-title',
                        input: 'swal-cat-input',
                        confirmButton: 'btn-glossy-confirm',
                        cancelButton: 'btn-glossy-cancel',
                    },
                }).then((hasil) => {
                    if (hasil.isConfirmed) {
                        Livewire.find(induk.getAttribute('wire:id')).call('createCategory', hasil.value.trim());
                    }
                });
            });
        }
    </script>
    @endonce
</div>
