{{-- Tabel task. Butuh: $ordered (grup task), $reads, $manageGiverIds, peta lencana.

     SATU BARIS = SATU TASK, bukan satu penerima. Satu task yang diberikan ke
     lima orang tetap satu pekerjaan; memecahnya jadi lima baris membuat daftar
     terbaca seperti ada lima pekerjaan berbeda, dan nama task yang sama
     berulang lima kali. Penerimanya dibuka dengan menekan barisnya.

     Urutan: YANG TERBARU DI ATAS (lihat TaskSayaList::render). --}}
@php
    $warnaProgres = ['belum' => '#6366f1', 'dikerjakan' => '#0284c7', 'selesai' => '#16a34a'];
    $lencanaProgres = ['belum' => 'is-nila', 'dikerjakan' => 'is-biru', 'selesai' => 'is-hijau'];
    $lencanaBobot = ['ringan' => 'is-hijau', 'sedang' => 'is-kuning', 'berat' => 'is-merah'];
@endphp

<div class="dsb-kartu">
    <div class="dsb-kartu-kepala">
        <div class="dsb-kartu-kepala-kiri">
            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-list-task"></i></span>
            <div>
                <h3 class="dsb-kartu-judul">Daftar Task</h3>
                <span class="dsb-kartu-sub">Yang terbaru di atas • klik barisnya untuk membuka</span>
            </div>
        </div>
    </div>

    <div class="dsb-tabel-bungkus">
        <table class="dsb-tabel">
            <thead>
                <tr>
                    <th>Task</th>
                    <th class="k-sedang">Penerima</th>
                    <th class="k-lebar">Pemberi</th>
                    <th class="k-sedang">Tenggat</th>
                    <th>Status</th>
                    <th class="k-lebar" style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ordered as $gid => $gtasks)
                    @php
                        $first = $gtasks->first();
                        $jumlah = $gtasks->count();
                        $grup = $jumlah > 1;

                        // Task milik SAYA di dalam grup — itulah yang dibuka saat
                        // barisnya ditekan. Kalau saya bukan penerimanya (mis.
                        // atasan yang memberi task), yang dibuka sub-task pertama.
                        $punyaSaya = $gtasks->firstWhere('user_id', auth()->id()) ?? $first;

                        $selesaiCount = $gtasks->where('progress', 'selesai')->count();
                        $progres = $grup
                            ? ($selesaiCount === $jumlah ? 'selesai' : ($selesaiCount > 0 ? 'dikerjakan' : $first->progress))
                            : $first->progress;

                        $bs = $first->bonusStatus();
                        $selesai = $progres === 'selesai';
                        $lewat = ! $selesai && $bs === 'tidak_selesai';
                        $sisa = $first->deadline_selesai
                            ? (int) now()->startOfDay()->diffInDays($first->deadline_selesai->copy()->startOfDay(), false)
                            : null;
                        $hariIni = ! $selesai && ! $lewat && $sisa === 0;

                        $terkunci = $gtasks->contains(fn ($t) => $t->isLocked());
                        $bolehKelola = $gtasks->contains(fn ($t) => $t->assigned_by && in_array($t->assigned_by, $manageGiverIds));

                        $terakhirBaca = ($reads[$first->group_id] ?? null)
                            ? \Illuminate\Support\Carbon::parse($reads[$first->group_id]) : null;
                        $komentarBaru = $first->groupComments->where('user_id', '!=', auth()->id())
                            ->filter(fn ($c) => ! $terakhirBaca || $c->created_at->gt($terakhirBaca))->count();

                        // Apakah SAYA disebut (@nama-depan) di komentar yang belum dibaca.
                        $namaDepanSaya = mb_strtolower((string) \Illuminate\Support\Str::of(auth()->user()->name)->trim()->explode(' ')->first());
                        $disebutSaya = $namaDepanSaya !== '' && $first->groupComments->contains(
                            fn ($c) => $c->user_id !== auth()->id()
                                && (! $terakhirBaca || $c->created_at->gt($terakhirBaca))
                                && $c->body
                                && preg_match('/@'.preg_quote($namaDepanSaya, '/').'(?![\p{L}\p{N}_])/ui', $c->body)
                        );

                        // Warna pita tepi kiri: merah untuk yang lewat tenggat,
                        // jingga untuk yang jatuh tempo hari ini. Selain itu tanpa
                        // pita — kalau semua baris bertanda, tidak ada yang bertanda.
                        $tanda = $lewat ? '#e11d48' : ($hariIni ? '#d97706' : null);
                    @endphp

                    <tr class="is-klik {{ $tanda ? 'is-tanda' : '' }}" @if ($tanda) style="--c: {{ $tanda }}" @endif
                        wire:key="baris-{{ $gid }}" wire:click="openTask('{{ $punyaSaya->id }}')">

                        <td>
                            <div class="dsb-tabel-utama">
                                <span class="dsb-ikon is-kecil" style="--c: {{ $warnaProgres[$progres] ?? '#64748b' }}">
                                    <i class="bi {{ $grup ? 'bi-folder-fill' : ($selesai ? 'bi-check-lg' : 'bi-card-checklist') }}"></i>
                                </span>
                                <span class="dsb-tabel-teks">
                                    <span class="dsb-tabel-judul">{{ $first->nama }}</span>
                                    <span class="dsb-tabel-meta">
                                        @if ($first->category)
                                            <span class="dsb-lencana is-ungu">{{ $first->category->nama }}</span>
                                        @endif
                                        @if ($first->label)
                                            <span class="dsb-lencana is-biru">{{ $first->label->nama }}</span>
                                        @endif
                                        <span class="dsb-lencana {{ $lencanaBobot[$first->bobot] ?? 'is-abu' }}">{{ ucfirst($first->bobot) }}</span>
                                        @if ($terkunci)
                                            <span class="dsb-lencana is-abu"><i class="bi bi-lock-fill"></i>Terkunci</span>
                                        @endif
                                        @if ($disebutSaya)
                                            <span class="dsb-lencana is-kuning"><i class="bi bi-at"></i>Anda disebut</span>
                                        @endif
                                        @if ($komentarBaru)
                                            <span class="dsb-lencana is-merah"><i class="bi bi-chat-dots-fill"></i>{{ $komentarBaru > 9 ? '9+' : $komentarBaru }} baru</span>
                                        @endif
                                        {{-- Salinan kolom yang hilang di layar sempit. Kolomnya
                                             boleh menghilang, isinya tidak. --}}
                                        <span class="dsb-tabel-samar">
                                            <i class="bi bi-person-badge"></i>{{ $first->pemberi?->name ?? $first->pembuat?->name ?? 'Admin' }}
                                        </span>
                                    </span>
                                </span>
                            </div>
                        </td>

                        <td class="k-sedang" data-judul="Penerima">
                            @if ($grup)
                                <span class="dsb-lencana is-ungu"><i class="bi bi-people-fill"></i>{{ $jumlah }} penerima</span>
                            @else
                                <span class="dsb-tabel-angka">{{ $first->user_id === auth()->id() ? 'Anda' : ($first->karyawan?->name ?? '-') }}</span>
                            @endif
                        </td>

                        <td class="k-lebar" data-judul="Pemberi">
                            <span class="dsb-tabel-angka">{{ $first->pemberi?->name ?? $first->pembuat?->name ?? 'Admin' }}</span>
                        </td>

                        <td class="k-sedang" data-judul="Tenggat">
                            <span class="dsb-tabel-teks">
                                <span class="dsb-tabel-angka">{{ $first->deadline_selesai?->locale('id')->translatedFormat('d M Y') ?? '—' }}</span>
                                <span class="dsb-tabel-meta">
                                    @if ($selesai)
                                        {{-- Kata "Selesai" sudah ada di kolom Status; yang belum
                                             terjawab adalah KAPAN, jadi itulah yang ditulis. --}}
                                        {{ $first->completed_at ? 'Rampung '.$first->completed_at->locale('id')->translatedFormat('d M') : 'Rampung' }}
                                    @elseif ($lewat)
                                        <span style="color: #e11d48; font-weight: 700;">Lewat tenggat</span>
                                    @elseif ($sisa === 0)
                                        <span style="color: #d97706; font-weight: 700;">Hari ini</span>
                                    @elseif ($sisa !== null)
                                        {{ $sisa }} hari lagi
                                    @endif
                                </span>
                            </span>
                        </td>

                        <td data-judul="Status">
                            <span class="dsb-tabel-teks">
                                <span class="dsb-lencana {{ $lewat ? 'is-merah' : ($lencanaProgres[$progres] ?? 'is-abu') }}">
                                    {{ $lewat ? 'Lewat Tenggat' : ($labelProg[$progres] ?? ucfirst($progres)) }}
                                </span>
                                @if ($grup)
                                    {{-- Grup memakai garis kemajuan, bukan satu lencana:
                                         "3 dari 5 selesai" adalah keadaan yang tidak bisa
                                         diwakili satu kata. --}}
                                    <span class="dsb-tabel-meta">{{ $selesaiCount }} dari {{ $jumlah }} selesai</span>
                                    <span class="dsb-kemajuan" style="--c: {{ $warnaProgres[$progres] ?? '#64748b' }}; margin-top: 6px;">
                                        <span style="width: {{ $jumlah > 0 ? round($selesaiCount / $jumlah * 100) : 0 }}%"></span>
                                    </span>
                                @endif
                            </span>
                        </td>

                        {{-- Saat tidak ada satu pun tombol (karyawan biasa pada task
                             solo), selnya ditandai kosong. Di ponsel sel bertumpuk
                             mencetak judulnya sendiri, jadi tanpa penanda ini muncul
                             baris "AKSI" tanpa isi apa pun. --}}
                        <td class="k-lebar {{ ($grup || $bolehKelola) ? '' : 'is-kosong' }}" data-judul="Aksi" style="text-align: right;">
                            <span class="dsb-tabel-aksi">
                                @if ($grup)
                                    <button type="button" class="dsb-tabel-btn" title="Diskusi grup"
                                        wire:click.stop="openGroupChat('{{ $first->group_id }}')">
                                        <i class="bi bi-chat-dots"></i>
                                    </button>
                                @endif
                                @if ($bolehKelola)
                                    @if ($terkunci)
                                        <button type="button" class="dsb-tabel-btn" title="Buka kembali untuk revisi"
                                            wire:click.stop="openReopen('{{ $punyaSaya->id }}')">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="dsb-tabel-btn" title="Edit task"
                                        wire:click.stop="openEditTask('{{ $first->id }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    {{-- Konfirmasi lewat SweetAlert bersama, bukan wire:confirm:
                                         dialog bawaan peramban menampilkan alamat situs di atas
                                         kalimatnya dan terbaca seperti peringatan sistem. --}}
                                    <button type="button" class="dsb-tabel-btn is-bahaya pcek-konfirmasi"
                                        title="Hapus task"
                                        data-action="{{ $grup ? 'deleteGroup' : 'deleteTask' }}"
                                        data-arg="{{ $grup ? $first->group_id : $first->id }}"
                                        data-title="{{ $grup ? 'Hapus task grup ini?' : 'Hapus task ini?' }}"
                                        data-text="{{ $grup ? 'Seluruh '.$jumlah.' penerima ikut terhapus. Tindakan ini tidak bisa dibatalkan.' : 'Tindakan ini tidak bisa dibatalkan.' }}"
                                        data-confirm="Ya, hapus" data-icon="warning">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
