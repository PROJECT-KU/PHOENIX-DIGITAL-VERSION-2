@section('title')
Jejak Audit Orcha || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         dasbor Orcha. Kelas orcha-* hanya dipakai untuk hal yang memang khas
         Orcha (cip status, paginasi API). --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $adaSaringan = $cari !== '' || $filterAdmin !== '' || $dari !== '' || $sampai !== '';
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Jejak Audit</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Siapa mengubah apa, kapan.</span>
                    <span class="d-block">Hanya bisa dibaca — tidak ada yang bisa menghapusnya dari sini.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #2b7fb8">
                    <i class="bi bi-speedometer2"></i><span>Dashboard Orcha</span>
                </a>
            </div>
        </header>

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="ja-saring">
                            {{-- Kotak carinya partial BERSAMA seluruh Orcha, bukan buatan
                                 halaman ini: bentuk dan perilakunya (termasuk tombol
                                 pengosong) harus satu di semua layar. Yang disesuaikan di
                                 sini hanya rupanya, lewat .ja-saring di bawah. --}}
                            <div class="dsb-medan ja-medan-cari">
                                <label class="dsb-label">Cari</label>
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Kode pesanan, nama admin, atau isi perubahan…',
                                ])
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ja-admin">Admin</label>
                                <select id="ja-admin" class="dsb-isian" wire:model.live="filterAdmin">
                                    <option value="">Semua admin</option>
                                    @foreach ($pilihanAdmin as $nama)
                                        <option value="{{ $nama }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Rentang tanggal, bukan satu tanggal.

                                 Pertanyaan audit hampir selalu berbentuk rentang — "pekan
                                 lalu", "sejak pengembaliannya diajukan" — dan satu tanggal
                                 memaksa menelusuri hari demi hari. --}}
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ja-dari">Dari</label>
                                <input id="ja-dari" type="date" class="dsb-isian" wire:model.live="dari">
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="ja-sampai">Sampai</label>
                                <input id="ja-sampai" type="date" class="dsb-isian" wire:model.live="sampai">
                            </div>
                        </div>

                        @if ($adaSaringan)
                            <div class="ja-saring-kaki">
                                <span class="dsb-kartu-sub"><i class="bi bi-funnel"></i> Daftar di bawah mengikuti saringan</span>
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut">
                                    <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat"
                    wire:target="cari,filterAdmin,dari,sampai,bersihkanSaringan,bersihkanCari,keHalaman">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0ea5e9"><i class="bi bi-clock-history"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Perubahan tercatat</h3>
                                <span class="dsb-kartu-sub">Terbaru di atas · satu baris = satu perubahan</span>
                            </div>
                        </div>
                        @if (($meta['total'] ?? null) !== null)
                            <span class="dsb-chip dsb-chip-hitung"><i class="bi bi-list-check"></i>{{ number_format((int) $meta['total'], 0, ',', '.') }} catatan</span>
                        @endif
                    </div>

                    @if ($daftar === [])
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-clock-history"></i></span>
                                <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada perubahan tercatat' }}</p>
                                <p class="dsb-kosong-ket">
                                    {{ $adaSaringan
                                        ? 'Coba ubah kata kunci, nama admin, atau rentang tanggalnya.'
                                        : 'Setiap perubahan yang dilakukan admin Orcha akan muncul di sini.' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel ja-tabel">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Admin</th>
                                        <th>Perubahan</th>
                                        <th>Pesanan</th>
                                        <th>IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        <tr wire:key="jejak-{{ $baris['id'] }}">
                                            <td data-judul="Waktu">
                                                <span class="dsb-tabel-angka">{{ $baris['waktu_teks'] }}</span>
                                            </td>
                                            <td data-judul="Admin">
                                                <span class="ja-admin">
                                                    <span class="dsb-avatar" style="--c: #4f46e5">{{ \Illuminate\Support\Str::substr($baris['admin'], 0, 1) }}</span>
                                                    <span>{{ $baris['admin'] }}</span>
                                                </span>
                                            </td>
                                            <td data-judul="Perubahan">
                                                @php
                                                    /*
                                                     | Ringkasan dari Orcha hampir selalu diawali kalimat
                                                     | yang SAMA dengan aksinya, lalu mengulang perpindahan
                                                     | status di dalam kurung — sehingga satu baris terbaca
                                                     | dua kali ("ubah status pembayaran" lalu "ubah status
                                                     | pembayaran (menunggu → diterima)"). Yang disisakan
                                                     | hanya keterangan yang benar-benar baru, mis. nominal.
                                                     */
                                                    $ringkas = trim((string) ($baris['ringkasan'] ?? ''));
                                                    $aksi = trim((string) $baris['aksi']);

                                                    if ($aksi !== '' && \Illuminate\Support\Str::startsWith(mb_strtolower($ringkas), mb_strtolower($aksi))) {
                                                        $ringkas = trim(mb_substr($ringkas, mb_strlen($aksi)));
                                                        $ringkas = trim(preg_replace('/^\([^)]*\)/u', '', $ringkas));
                                                        $ringkas = trim($ringkas, "—- \t");
                                                    }
                                                @endphp

                                                <span class="dsb-tabel-judul">{{ $baris['aksi'] }}</span>

                                                {{-- Perpindahan status ditampilkan sebagai "dari → ke",
                                                     bukan hanya nilai akhirnya. Nilai akhir saja tidak
                                                     menjawab pertanyaan yang sebenarnya diajukan:
                                                     berubah DARI apa. --}}
                                                @if ($baris['sebelum'] !== null || $baris['sesudah'] !== null)
                                                    <span class="ja-pindah">
                                                        <span class="ja-pindah-lama">{{ $baris['sebelum'] ?: '—' }}</span>
                                                        <i class="bi bi-arrow-right"></i>
                                                        <span class="ja-pindah-baru">{{ $baris['sesudah'] ?: '—' }}</span>
                                                    </span>
                                                @endif

                                                @if ($ringkas !== '')
                                                    <span class="dsb-tabel-meta">{{ $ringkas }}</span>
                                                @endif
                                            </td>
                                            <td data-judul="Pesanan">
                                                @if ($baris['kode'])
                                                    <span class="ja-kode">{{ $baris['kode'] }}</span>
                                                @else
                                                    <span class="dsb-tabel-samar">—</span>
                                                @endif
                                            </td>
                                            <td data-judul="IP">
                                                <span class="dsb-tabel-samar">{{ $baris['ip'] ?: '—' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="ja-halaman">
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        /* Saringan: satu baris di layar lebar, melipat sendiri saat sempit.
           Kotak cari dua kali lebih lebar — isinya kalimat, bukan pilihan. */
        .ja-saring { display: flex; flex-wrap: wrap; gap: 12px; }
        .ja-saring > .dsb-medan { flex: 1 1 180px; min-width: 0; }
        .ja-saring > .ja-medan-cari { flex: 2 1 320px; }

        /* Kotak cari bersama memakai .form-control Bootstrap. Di sebelah medan
           lemon ia terbaca sebagai cacat: tingginya beda, sudutnya beda. Yang
           disamakan rupanya saja — markup & perilakunya tetap milik partial. */
        .ja-saring .form-control {
            width: 100%; min-height: 42px; padding: 9px 14px 9px 40px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
            background: #fff; color: var(--dsb-tinta, #1c1f26); font-size: .88rem;
            box-shadow: none;
        }
        .ja-saring .form-control:focus {
            border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0;
        }
        .ja-saring .form-control-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; z-index: 2;
        }

        .ja-saring-kaki {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
            gap: 12px; margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9;
        }
        .ja-saring-kaki .dsb-kartu-sub { display: inline-flex; align-items: center; gap: 7px; }

        /* Nama admin: inisial berwarna + nama, sejajar di tengah. */
        .ja-admin { display: inline-flex; align-items: center; gap: 9px; font-weight: 600; color: var(--dsb-tinta, #1c1f26); }
        .ja-admin .dsb-avatar { width: 30px; height: 30px; font-size: .78rem; }

        /* "dari → ke" dibaca sebagai satu kalimat; yang lama diredupkan dan
           dicoret supaya arah perubahannya terbaca tanpa mengeja. */
        .ja-pindah { display: inline-flex; align-items: center; gap: 6px; margin-top: 3px; font-size: .78rem; flex-wrap: wrap; }
        .ja-pindah-lama { color: #94a3b8; text-decoration: line-through; }
        .ja-pindah i { color: #cbd5e1; font-size: .7rem; }
        .ja-pindah-baru { font-weight: 700; color: var(--dsb-tinta, #1c1f26); }

        .ja-kode {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .78rem;
            background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
            border-radius: 8px; padding: 2px 8px; white-space: nowrap;
        }

        .ja-halaman { padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
        .ja-halaman > * { margin-bottom: 0 !important; }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu bertumpuk,
           aturan yang sama dengan daftar lemon lain. */
        @media (max-width: 767.98px) {
            .ja-tabel { min-width: 0; }
            .ja-tabel thead { display: none; }
            .ja-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
            .ja-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0;
            }
            .ja-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            .ja-tabel tbody td > :not(:first-child) { margin-left: 0; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
