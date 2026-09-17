{{--
    Papan Scrum (Kanban) — CARA PANDANG saja.

    Memakai $tasks yang SAMA dengan tampilan tabel (sudah di-scope visibleTo()
    dan difilter di render()). Tidak ada query baru, tidak ada aturan baru:
    cuma dikelompokkan per kolom progress.

    Tombol aksi memanggil metode yang SUDAH ADA (openTask/mulaiKerjakan/
    tandaiSelesai) — tidak ada logic baru yang dibuat di sini.

    Rupanya memakai bahasa rupa dasbor (dsb-*) seperti tabelnya: tiap kolom
    adalah satu kartu dengan ubin ikon berwarna, dan lencananya sama dengan
    lencana di tabel. Sebelumnya papan ini punya dialeknya sendiri, jadi
    menekan tab "Papan Scrum" terasa seperti pindah aplikasi.
--}}
@php
    $kolom = [
        'belum' => ['judul' => 'Belum Dikerjakan', 'ikon' => 'bi-inbox-fill', 'warna' => '#6366f1', 'lencana' => 'is-nila'],
        'dikerjakan' => ['judul' => 'Sedang Dikerjakan', 'ikon' => 'bi-hourglass-split', 'warna' => '#0284c7', 'lencana' => 'is-biru'],
        'selesai' => ['judul' => 'Selesai', 'ikon' => 'bi-check-circle-fill', 'warna' => '#16a34a', 'lencana' => 'is-hijau'],
    ];
    $perProgress = $tasks->groupBy('progress');
    $lencanaBobotS = ['ringan' => 'is-hijau', 'sedang' => 'is-kuning', 'berat' => 'is-merah'];
@endphp

<div class="dsb-rak">
    @foreach ($kolom as $key => $k)
        @php $isi = $perProgress->get($key, collect()); @endphp

        <div class="dsb-kartu k-4">
            <div class="dsb-kartu-kepala">
                <div class="dsb-kartu-kepala-kiri">
                    <span class="dsb-ikon is-kecil" style="--c: {{ $k['warna'] }}"><i class="bi {{ $k['ikon'] }}"></i></span>
                    <div>
                        <h3 class="dsb-kartu-judul">{{ $k['judul'] }}</h3>
                        <span class="dsb-kartu-sub">{{ $isi->count() }} task</span>
                    </div>
                </div>
                <span class="dsb-lencana {{ $k['lencana'] }}">{{ $isi->count() }}</span>
            </div>

            <div class="dsb-kartu-isi scrum-tumpuk">
                @forelse ($isi as $t)
                    @php
                        $telat = $t->progress !== 'selesai' && $t->deadline_selesai && $t->isLewatDeadline();
                        $hariTelat = $t->progress === 'selesai' ? $t->hariTerlambat() : 0;
                    @endphp
                    <article class="scrum-kartu {{ $telat ? 'is-telat' : '' }}" wire:key="scrum-{{ $t->id }}">
                        <div class="scrum-kartu-atas">
                            @if ($t->category)
                                <span class="dsb-lencana is-ungu">{{ $t->category->nama }}</span>
                            @endif
                            @if ($t->label)
                                <span class="dsb-lencana is-biru">{{ $t->label->nama }}</span>
                            @endif
                            <span class="dsb-lencana {{ $lencanaBobotS[$t->bobot] ?? 'is-abu' }}">{{ ucfirst($t->bobot) }}</span>
                        </div>

                        <button type="button" class="scrum-kartu-judul" wire:click="openTask('{{ $t->id }}')">
                            {{ $t->nama }}
                        </button>

                        <div class="scrum-kartu-meta">
                            @if ($t->deadline_selesai)
                                <span class="{{ $telat ? 'is-telat' : '' }}">
                                    <i class="bi bi-calendar-event"></i>{{ $t->deadline_selesai->locale('id')->translatedFormat('d M') }}@if ($telat) — lewat @endif
                                </span>
                            @endif
                            @if ($hariTelat > 0)
                                <span class="is-tunda"><i class="bi bi-clock-history"></i>telat {{ $hariTelat }} hari</span>
                            @endif
                            @if ($t->karyawan)
                                <span><i class="bi bi-person"></i>{{ \Illuminate\Support\Str::of($t->karyawan->name)->explode(' ')->first() }}</span>
                            @endif
                        </div>

                        {{-- Aksi: memakai metode yang sudah ada, bukan logic baru --}}
                        @if ($t->user_id === auth()->id() && $t->progress !== 'selesai')
                            <div class="scrum-kartu-aksi">
                                @if ($t->progress === 'belum')
                                    <button type="button" class="dsb-tombol is-mungil is-biru" wire:click="mulaiKerjakan('{{ $t->id }}')">
                                        <i class="bi bi-play-fill"></i><span>Mulai</span>
                                    </button>
                                @else
                                    <button type="button" class="dsb-tombol is-mungil is-hijau" wire:click="tandaiSelesai('{{ $t->id }}')">
                                        <i class="bi bi-check-lg"></i><span>Selesai</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="dsb-kosong">
                        <span class="dsb-kosong-ikon"><i class="bi bi-dash-circle"></i></span>
                        <p class="dsb-kosong-judul">Tidak ada</p>
                        <p class="dsb-kosong-ket">Belum ada task di kolom ini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
