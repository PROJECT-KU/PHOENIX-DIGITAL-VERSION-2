{{--
    Tanggal keberangkatan: satu paket, beberapa rombongan.

    Baru muncul SESUDAH paketnya tersimpan — tanggal menempel pada paket, dan
    paket yang belum punya id belum punya tempat untuk ditempeli.
--}}
@if ($ubah)
    <section class="dsb-bagian">
        <div class="dsb-rak">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-calendar2-week"></i></span>
                        <div>
                            <h3 class="dsb-kartu-judul">Tanggal Keberangkatan</h3>
                            <span class="dsb-kartu-sub">Inilah yang bisa dipilih pendaftar di website</span>
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    <p class="pb-ket">
                        Satu paket boleh dijual untuk beberapa tanggal. Jadwal kegiatan, foto,
                        fasilitas, dan harganya sama — yang berbeda hanya tanggalnya.
                        <strong>Harga &amp; jumlah kursi yang dikosongkan berarti ikut paket ini.</strong>
                    </p>

                    <div class="pb-kb mt-3">
                        @forelse ($keberangkatan as $kb)
                            @php
                                $hargaSendiri = ($kb['price'] ?? null) !== null;
                                $kuotaSendiri = ($kb['kuota'] ?? null) !== null;
                                $sedangDisunting = $kbUbahId === $kb['id'];
                            @endphp

                            <div class="pb-kb-baris @if ($sedangDisunting) is-sunting @endif"
                                wire:key="kb-{{ $kb['id'] }}">
                                <span class="pb-kb-isi">
                                    <span class="pb-kb-tanggal">{{ $kb['rentang_tanggal'] }}</span>

                                {{-- Kuncinya 'price', BUKAN 'harga'.

                                     Orcha mengirim angka mentahnya sebagai price dan nilai
                                     terpakainya sebagai harga_dipakai; 'harga' tidak pernah
                                     ada. Lencana ini sempat tidak tergambar sama sekali
                                     karenanya — tanggal yang harganya memang berbeda
                                     terlihat persis sama dengan yang ikut harga paket. --}}
                                @if ($hargaSendiri)
                                    <span class="dsb-lencana is-ungu">
                                        Rp {{ number_format($kb['harga_dipakai'], 0, ',', '.') }}
                                        · harga sendiri
                                    </span>
                                @endif

                                @if ($kuotaSendiri)
                                    <span class="dsb-lencana is-biru">
                                        {{ $kb['kuota'] }} kursi · kuota sendiri
                                    </span>
                                @endif

                                @if (! $kb['aktif'])
                                    <span class="dsb-lencana is-abu"><i class="bi bi-lock-fill"></i>Ditutup</span>
                                @elseif ($kb['penuh'])
                                    {{-- Hijau, bukan merah: terjual habis itu kabar baik, dan
                                         daftar paketnya sudah memakai warna itu. Dua layar yang
                                         mewarnai keadaan sama dengan warna berlawanan membuat
                                         salah satunya pasti salah dibaca. --}}
                                    <span class="dsb-lencana is-hijau">
                                        <i class="bi bi-check-circle-fill"></i>Terisi penuh
                                    </span>
                                @elseif (($kb['sisa_kursi'] ?? null) !== null)
                                    <span class="dsb-lencana is-hijau">sisa {{ $kb['sisa_kursi'] }} kursi</span>
                                @endif
                                </span>

                                {{-- Tombol BERLABEL, bukan ikon telanjang.

                                     Gembok tanpa tulisan tidak memberi tahu siapa pun bahwa
                                     ia menutup pendaftaran; yang tahu cuma yang pernah
                                     diberi tahu, dan itu bukan definisi tombol yang bisa
                                     dipakai. --}}
                                <span class="pb-kb-aksi">
                                    <button type="button" class="pb-kb-btn" style="--ikon: #7c3aed"
                                        wire:click="ubahKeberangkatan({{ $kb['id'] }})"
                                        title="Ubah tanggal, harga, atau kursinya">
                                        <i class="bi bi-pencil"></i><span>Ubah</span>
                                    </button>

                                    {{-- Jalan ke pembagian kursi & kamar rombongan ini.

                                         Dari sinilah tanggal keberangkatan
                                         dikelola, jadi di sinilah pertanyaan
                                         "bus dan kamarnya sudah dibagi belum?"
                                         muncul. Sebelumnya layar itu hanya bisa
                                         dicapai lewat salah satu pendaftaran —
                                         yang harus dicari dulu. --}}
                                    <a class="pb-kb-btn" style="--ikon: #16a34a"
                                        href="{{ route('admin.orcha.pembagian', $kb['id']) }}" wire:navigate
                                        title="Bagi tempat duduk bus dan kamar hotel untuk rombongan ini">
                                        <i class="bi bi-grid-3x3-gap"></i>
                                        <span>Bagi kursi</span>
                                    </a>

                                    <button type="button" class="pb-kb-btn" style="--ikon: #0284c7"
                                        wire:click="salinKeberangkatan({{ $kb['id'] }})"
                                        title="Buat tanggal baru dengan isi yang sama persis">
                                        <i class="bi bi-copy"></i><span>Salin</span>
                                    </button>

                                    <button type="button" class="pb-kb-btn"
                                        style="--ikon: {{ $kb['aktif'] ? '#b45309' : '#16a34a' }}"
                                        wire:click="alihkanKeberangkatan({{ $kb['id'] }}, {{ $kb['aktif'] ? 'true' : 'false' }})"
                                        title="{{ $kb['aktif'] ? 'Tutup tanggal ini, tidak menerima pendaftar baru' : 'Buka lagi tanggal ini' }}">
                                        <i class="bi {{ $kb['aktif'] ? 'bi-lock' : 'bi-unlock' }}"></i>
                                        <span>{{ $kb['aktif'] ? 'Tutup' : 'Buka lagi' }}</span>
                                    </button>

                                    <button type="button" class="pb-kb-btn is-bahaya pcek-konfirmasi"
                                        data-action="hapusKeberangkatan" data-arg="{{ $kb['id'] }}"
                                        data-title="Hapus tanggal {{ $kb['rentang_tanggal'] }}?"
                                        data-text="Kalau tanggal ini sudah ada yang mendaftar, hapusnya akan ditolak. Tutup saja, supaya tidak menerima pendaftar baru."
                                        data-confirm="Ya, hapus" data-icon="warning"
                                        title="Hapus tanggal ini">
                                        <i class="bi bi-trash"></i><span>Hapus</span>
                                    </button>
                                </span>
                            </div>
                        @empty
                            <div class="pb-kb-baris">
                                <span class="pb-ket">
                                    Belum ada tanggal. Tambahkan satu, supaya pengunjung bisa mendaftar.
                                </span>
                            </div>
                        @endforelse

                        {{-- Kotak isian: tambah baru, salin, atau sunting yang sudah ada.

                             Judulnya BERGANTI mengikuti apa yang sedang dikerjakan.
                             Tanpa itu, menekan "Ubah" pada salah satu baris tidak
                             mengubah apa pun yang terlihat di kotak ini — isinya saja
                             yang terisi — dan yang menekannya tidak tahu bahwa ia
                             sedang menyunting, bukan menambah. --}}
                        <div class="pb-kb-borang">
                            <span class="pb-kb-judul">
                                @if ($kbUbahId)
                                    <i class="bi bi-pencil-square"></i> Ubah tanggal yang dipilih
                                @elseif ($kbSalinDari)
                                    <i class="bi bi-copy"></i> Salin jadi tanggal baru
                                @else
                                    <i class="bi bi-plus-circle"></i> Tambah tanggal baru
                                @endif
                            </span>

                            <div class="pb-kb-isian">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-kb-tanggal">
                                        Tanggal berangkat <span class="dsb-wajib">*</span>
                                    </label>
                                    <input id="pb-kb-tanggal" type="date" class="dsb-isian"
                                        wire:model="kbTanggal" value="{{ $kbTanggal }}">
                                    @error('kbTanggal') <span class="pb-galat">{{ $message }}</span> @enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-kb-pulang">
                                        Tanggal pulang <span class="dsb-label-ket">(boleh kosong)</span>
                                    </label>
                                    <input id="pb-kb-pulang" type="date" class="dsb-isian"
                                        wire:model="kbTanggalPulang" value="{{ $kbTanggalPulang }}">
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-kb-kuota">
                                        Jumlah kursi <span class="dsb-label-ket">(ikut paket)</span>
                                    </label>
                                    <input id="pb-kb-kuota" type="number" min="1" class="dsb-isian"
                                        wire:model="kbKuota" placeholder="ikut paket">
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-kb-harga">
                                        Harga tanggal ini <span class="dsb-label-ket">(ikut paket)</span>
                                    </label>
                                    {{-- orcha-uang & pembungkus orcha-rupiah: isian uang di Orcha
                                         diformat sambil diketik lewat kelas itu. Tanpa keduanya
                                         angkanya baru bertitik setelah pindah kolom, dan isian ini
                                         berdiri tepat di sebelah dua isian uang lain yang
                                         berperilaku lain. --}}
                                    <div class="orcha-rupiah">
                                        <input id="pb-kb-harga" type="text" inputmode="numeric"
                                            class="orcha-uang dsb-isian"
                                            wire:model.blur="kbHargaTeks" value="{{ $kbHargaTeks }}"
                                            placeholder="ikut paket">
                                    </div>
                                </div>
                            </div>

                            <div class="pb-kb-kaki">
                                <span class="pb-kb-kaki-ket">
                                    @if ($kbSalinDari)
                                        Jumlah kursi, harga, dan catatannya ikut tersalin. Tanggal
                                        pulang yang dikosongkan dihitung sendiri, selama trip yang
                                        Anda salin.
                                    @else
                                        Kursi &amp; harga diisi hanya bila tanggal ini memang lain —
                                        akhir pekan, atau armada yang berbeda.
                                    @endif
                                </span>

                                <span class="pb-kb-kaki-tombol">
                                    @if ($kbUbahId || $kbSalinDari)
                                        <button type="button" class="dsb-tombol is-lembut"
                                            wire:click="batalKeberangkatan">
                                            <i class="bi bi-x-lg"></i><span>Batal</span>
                                        </button>
                                    @endif

                                    <button type="button" class="dsb-tombol is-utama"
                                        wire:click="simpanKeberangkatan"
                                        wire:loading.attr="disabled" wire:target="simpanKeberangkatan">
                                        <i class="bi {{ $kbUbahId ? 'bi-check-lg' : ($kbSalinDari ? 'bi-copy' : 'bi-plus-lg') }}"></i>
                                        <span>
                                            @if ($kbUbahId)
                                                Simpan tanggal
                                            @elseif ($kbSalinDari)
                                                Simpan salinan
                                            @else
                                                Tambah tanggal
                                            @endif
                                        </span>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
