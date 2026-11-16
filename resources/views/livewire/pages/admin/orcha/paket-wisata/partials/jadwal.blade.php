{{--
    Jadwal kegiatan per hari.

    Bisa diurut ulang dan disalin, bukan cuma ditambah & dihapus: hari ke-2
    yang isinya mirip hari ke-1 adalah bentuk paling lumrah sebuah trip, dan
    selama ini satu-satunya cara membuatnya adalah mengetik ulang sepuluh
    baris.
--}}
<section class="dsb-bagian">
    <div class="dsb-rak">
        <div class="dsb-kartu k-12">
            <div class="dsb-kartu-kepala">
                <div class="dsb-kartu-kepala-kiri">
                    <span class="dsb-ikon is-kecil" style="--c: #0f766e"><i class="bi bi-list-check"></i></span>
                    <div>
                        <h3 class="dsb-kartu-judul">Jadwal Kegiatan</h3>
                        <span class="dsb-kartu-sub">{{ count($hari) }} hari · inilah yang dibaca pengunjung</span>
                    </div>
                </div>
            </div>

            <div class="dsb-kartu-isi">
                <p class="pb-ket">
                    Tulis jam dan kegiatannya, satu baris satu kegiatan. Baris yang
                    kegiatannya dikosongkan tidak ikut tersimpan. Jam ditulis
                    <strong>07.00</strong> — yang Anda ketik dirapikan sendiri ke bentuk itu.
                </p>

                @foreach ($hari as $urutanHari => $satuHari)
                    <div class="pb-hari mt-3" wire:key="hari-{{ $urutanHari }}">
                        <div class="pb-hari-kepala">
                            <span class="pb-hari-nomor">{{ $urutanHari + 1 }}</span>

                            <input type="text" class="dsb-isian"
                                wire:model="hari.{{ $urutanHari }}.nama" value="{{ $satuHari['nama'] }}"
                                placeholder="Hari {{ $urutanHari + 1 }}">

                            <span class="pb-hari-aksi">
                                {{-- Naik/turun, bukan seret.

                                     Menyeret menuntut tangan yang mantap dan layar yang
                                     lebar; dua tombol bekerja sama di ponsel, dan keduanya
                                     bisa ditekan berulang tanpa kehilangan posisi. --}}
                                <button type="button" class="dsb-tabel-btn" style="--c: #64748b"
                                    wire:click="pindahHari({{ $urutanHari }}, -1)"
                                    @disabled($urutanHari === 0) title="Naikkan hari ini">
                                    <i class="bi bi-arrow-up"></i>
                                </button>

                                <button type="button" class="dsb-tabel-btn" style="--c: #64748b"
                                    wire:click="pindahHari({{ $urutanHari }}, 1)"
                                    @disabled($urutanHari === count($hari) - 1) title="Turunkan hari ini">
                                    <i class="bi bi-arrow-down"></i>
                                </button>

                                <button type="button" class="dsb-tabel-btn" style="--c: #0284c7"
                                    wire:click="salinHari({{ $urutanHari }})"
                                    title="Gandakan hari ini beserta kegiatannya">
                                    <i class="bi bi-copy"></i>
                                </button>

                                @if (1 < count($hari))
                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi"
                                        style="--c: var(--dsb-bahaya)"
                                        data-action="buangHari" data-arg="{{ $urutanHari }}"
                                        data-title="Hapus hari ke-{{ $urutanHari + 1 }}?"
                                        data-text="Seluruh kegiatan di hari ini ikut terhapus."
                                        data-confirm="Ya, hapus" data-icon="warning"
                                        title="Hapus seluruh hari ini beserta kegiatannya">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </span>
                        </div>

                        @foreach ($satuHari['agenda'] as $urutanAgenda => $agenda)
                            <div class="pb-agenda" wire:key="agenda-{{ $urutanHari }}-{{ $urutanAgenda }}">
                                <input type="text" class="dsb-isian"
                                    wire:model.blur="hari.{{ $urutanHari }}.agenda.{{ $urutanAgenda }}.jam"
                                    value="{{ $agenda['jam'] }}" placeholder="07.00"
                                    inputmode="numeric" aria-label="Jam kegiatan">

                                <input type="text" class="dsb-isian"
                                    wire:model="hari.{{ $urutanHari }}.agenda.{{ $urutanAgenda }}.kegiatan"
                                    value="{{ $agenda['kegiatan'] }}"
                                    placeholder="Penjemputan di titik kumpul"
                                    aria-label="Nama kegiatan">

                                <span class="pb-agenda-aksi">
                                    <button type="button" class="dsb-tabel-btn" style="--c: #64748b"
                                        wire:click="pindahAgenda({{ $urutanHari }}, {{ $urutanAgenda }}, -1)"
                                        @disabled($urutanAgenda === 0) title="Naikkan kegiatan ini">
                                        <i class="bi bi-arrow-up"></i>
                                    </button>

                                    <button type="button" class="dsb-tabel-btn" style="--c: #64748b"
                                        wire:click="pindahAgenda({{ $urutanHari }}, {{ $urutanAgenda }}, 1)"
                                        @disabled($urutanAgenda === count($satuHari['agenda']) - 1)
                                        title="Turunkan kegiatan ini">
                                        <i class="bi bi-arrow-down"></i>
                                    </button>

                                    <button type="button" class="dsb-tabel-btn" style="--c: var(--dsb-bahaya)"
                                        wire:click="buangAgenda({{ $urutanHari }}, {{ $urutanAgenda }})"
                                        title="Hapus baris ini">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </span>
                            </div>
                        @endforeach

                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #0f766e"
                            wire:click="tambahAgenda({{ $urutanHari }})">
                            <i class="bi bi-plus-lg"></i><span>Tambah kegiatan</span>
                        </button>
                    </div>
                @endforeach

                <button type="button" class="dsb-tombol is-lembut mt-2" style="--ikon: #7c3aed"
                    wire:click="tambahHari">
                    <i class="bi bi-calendar-plus"></i><span>Tambah Hari</span>
                </button>
            </div>
        </div>
    </div>
</section>
