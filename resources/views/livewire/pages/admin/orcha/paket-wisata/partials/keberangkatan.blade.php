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

                                {{-- Tombol BERLABEL, bukan ikon telanjang.

                                     Gembok tanpa tulisan tidak memberi tahu siapa pun bahwa
                                     ia menutup pendaftaran; yang tahu cuma yang pernah
                                     diberi tahu, dan itu bukan definisi tombol yang bisa
                                     dipakai. --}}
                                <span class="pb-kb-aksi">
                                    <button type="button" class="dsb-tabel-btn" style="--c: #7c3aed"
                                        wire:click="ubahKeberangkatan({{ $kb['id'] }})"
                                        title="Ubah tanggal, harga, atau kursinya">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <button type="button" class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                                        wire:click="salinKeberangkatan({{ $kb['id'] }})"
                                        title="Buat tanggal baru dengan isi yang sama persis">
                                        <i class="bi bi-copy"></i><span>Salin</span>
                                    </button>

                                    <button type="button" class="dsb-tombol is-lembut"
                                        style="--ikon: {{ $kb['aktif'] ? '#b45309' : '#16a34a' }}"
                                        wire:click="alihkanKeberangkatan({{ $kb['id'] }}, {{ $kb['aktif'] ? 'true' : 'false' }})">
                                        <i class="bi {{ $kb['aktif'] ? 'bi-lock' : 'bi-unlock' }}"></i>
                                        <span>{{ $kb['aktif'] ? 'Tutup' : 'Buka lagi' }}</span>
                                    </button>

                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi"
                                        style="--c: var(--dsb-bahaya)"
                                        data-action="hapusKeberangkatan" data-arg="{{ $kb['id'] }}"
                                        data-title="Hapus tanggal {{ $kb['rentang_tanggal'] }}?"
                                        data-text="Kalau tanggal ini sudah ada yang mendaftar, hapusnya akan ditolak. Tutup saja, supaya tidak menerima pendaftar baru."
                                        data-confirm="Ya, hapus" data-icon="warning"
                                        title="Hapus tanggal ini">
                                        <i class="bi bi-trash"></i>
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

                        {{-- Baris isian: tambah baru, salin, atau sunting yang sudah ada. --}}
                        <div class="pb-kb-borang">
                            <div class="pb-kisi is-tiga">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-kb-tanggal">
                                        @if ($kbUbahId)
                                            Tanggal berangkat
                                        @elseif ($kbSalinDari)
                                            Tanggal berangkat untuk salinannya
                                        @else
                                            Tanggal berangkat baru
                                        @endif
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
                                        Jumlah kursi <span class="dsb-label-ket">(ikut paket bila kosong)</span>
                                    </label>
                                    <input id="pb-kb-kuota" type="number" min="1" class="dsb-isian"
                                        wire:model="kbKuota" placeholder="ikut paket">
                                </div>
                            </div>

                            <div class="pb-kisi is-tiga mt-3">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-kb-harga">
                                        Harga tanggal ini <span class="dsb-label-ket">(ikut paket bila kosong)</span>
                                    </label>
                                    {{-- orcha-uang & pembungkus orcha-rupiah: isian uang di
                                         Orcha diformat sambil diketik lewat kelas itu. Tanpa
                                         keduanya, angkanya baru bertitik setelah pindah kolom —
                                         dan isian ini berdiri tepat di sebelah dua isian uang
                                         lain yang berperilaku lain. --}}
                                    <div class="orcha-rupiah">
                                        <input id="pb-kb-harga" type="text" inputmode="numeric"
                                            class="orcha-uang dsb-isian"
                                            wire:model.blur="kbHargaTeks" value="{{ $kbHargaTeks }}"
                                            placeholder="ikut paket">
                                    </div>
                                    <span class="pb-ket">
                                        Diisi hanya bila tanggal ini memang lain harganya — akhir
                                        pekan, atau armada yang berbeda.
                                    </span>
                                </div>

                                <div class="dsb-medan" style="justify-content: flex-end">
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
                                </div>

                                <div class="dsb-medan" style="justify-content: flex-end">
                                    @if ($kbUbahId || $kbSalinDari)
                                        <button type="button" class="dsb-tombol is-lembut"
                                            wire:click="batalKeberangkatan">
                                            <i class="bi bi-x-lg"></i><span>Batal</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if ($kbSalinDari)
                                <p class="pb-ket mt-2">
                                    Jumlah kursi, harga, dan catatannya ikut tersalin. Tanggal pulang
                                    yang dikosongkan dihitung sendiri, selama trip yang Anda salin.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
