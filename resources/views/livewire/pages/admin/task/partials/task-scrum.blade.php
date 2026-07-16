{{--
    Papan Scrum (Kanban) — CARA PANDANG saja.

    Memakai $tasks yang SAMA dengan tampilan daftar (sudah di-scope visibleTo()
    dan difilter periode di render()). Tidak ada query baru, tidak ada aturan
    baru: cuma dikelompokkan per kolom progress.

    Tombol aksi memanggil metode yang SUDAH ADA (openTask/mulaiKerjakan/
    tandaiSelesai) — tidak ada logic baru yang dibuat di sini.
--}}
@php
    $kolom = [
        'belum' => ['judul' => 'Belum Dikerjakan', 'ikon' => 'bi-inbox', 'warna' => '#94a3b8'],
        'dikerjakan' => ['judul' => 'Sedang Dikerjakan', 'ikon' => 'bi-hourglass-split', 'warna' => '#d97706'],
        'selesai' => ['judul' => 'Selesai', 'ikon' => 'bi-check2-circle', 'warna' => '#059669'],
    ];
    $perProgress = $tasks->groupBy('progress');
@endphp

<div class="scrum-board">
    @foreach ($kolom as $key => $k)
        @php $isi = $perProgress->get($key, collect()); @endphp
        <div class="scrum-col">
            <div class="scrum-col-head" style="--k:{{ $k['warna'] }};">
                <span class="scrum-col-ico"><i class="bi {{ $k['ikon'] }}"></i></span>
                <span class="scrum-col-judul">{{ $k['judul'] }}</span>
                <span class="scrum-col-badge">{{ $isi->count() }}</span>
            </div>

            <div class="scrum-col-body">
                @forelse ($isi as $t)
                    @php
                        $telat = $t->progress !== 'selesai' && $t->deadline_selesai && $t->isLewatDeadline();
                        $hariTelat = $t->progress === 'selesai' ? $t->hariTerlambat() : 0;
                    @endphp
                    <div class="scrum-card {{ $telat ? 'is-telat' : '' }}" wire:key="scrum-{{ $t->id }}">
                        {{-- Kategori + label ditampilkan BERDUA (seperti kartu Daftar),
                             supaya label tidak mengambang tanpa induk. Mis. kategori
                             "Programming" dgn label "Feature". --}}
                        <div class="scrum-card-top">
                            <div class="scrum-tags">
                                @if ($t->category)
                                    <span class="scrum-kategori"><i class="bi bi-tag"></i>{{ $t->category->nama }}</span>
                                @endif
                                @if ($t->label)
                                    <span class="scrum-label">{{ $t->label->nama }}</span>
                                @endif
                            </div>
                            <span class="scrum-bobot bobot-{{ $t->bobot }}">{{ ucfirst($t->bobot) }}</span>
                        </div>

                        <button type="button" class="scrum-card-judul" wire:click="openTask('{{ $t->id }}')">
                            {{ $t->nama }}
                        </button>

                        <div class="scrum-card-meta">
                            @if ($t->deadline_selesai)
                                <span class="scrum-meta-item {{ $telat ? 'text-danger fw-bold' : '' }}">
                                    <i class="bi bi-calendar-event"></i>
                                    {{ $t->deadline_selesai->translatedFormat('d M') }}
                                    @if ($telat) — lewat @endif
                                </span>
                            @endif
                            @if ($hariTelat > 0)
                                <span class="scrum-meta-item text-warning fw-bold">
                                    <i class="bi bi-clock-history"></i>telat {{ $hariTelat }} hari
                                </span>
                            @endif
                            @if ($t->karyawan)
                                <span class="scrum-meta-item">
                                    <i class="bi bi-person"></i>{{ \Illuminate\Support\Str::of($t->karyawan->name)->explode(' ')->first() }}
                                </span>
                            @endif
                        </div>

                        {{-- Aksi: memakai metode yang sudah ada, bukan logic baru --}}
                        @if ($t->user_id === auth()->id() && $t->progress !== 'selesai')
                            <div class="scrum-card-aksi">
                                @if ($t->progress === 'belum')
                                    <button type="button" class="scrum-btn scrum-btn-mulai" wire:click="mulaiKerjakan('{{ $t->id }}')">
                                        <i class="bi bi-play-fill"></i>Mulai
                                    </button>
                                @else
                                    <button type="button" class="scrum-btn scrum-btn-selesai" wire:click="tandaiSelesai('{{ $t->id }}')">
                                        <i class="bi bi-check-lg"></i>Selesai
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="scrum-kosong">
                        <i class="bi bi-dash-circle"></i>
                        <span>Tidak ada</span>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
