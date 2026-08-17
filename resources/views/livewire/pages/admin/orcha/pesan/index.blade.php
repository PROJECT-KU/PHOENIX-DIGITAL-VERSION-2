@section('title')
Pesan Kontak Orcha || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Pesan Kontak',
            'keterangan' => 'Pertanyaan yang masuk lewat formulir kontak website Orcha.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari nama, WhatsApp, email, atau isi pesan...">
                        </div>
                    </div>
                    <div class="col-8 col-lg-4">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua keperluan</option>
                            @foreach ($pilihanKeperluan as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-lg-2">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="orcha-belum-dibaca"
                                wire:model.live="hanyaBelumDibaca">
                            <label class="form-check-label small" for="orcha-belum-dibaca">Belum dibaca</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($daftar as $baris)
                <div class="col-12 col-xl-6" wire:key="pesan-{{ $baris['id'] }}">
                    {{-- Yang belum dibaca diberi garis tebal di kiri. Kotak masuk dibuka
                         untuk mencari apa yang belum dikerjakan, bukan untuk membaca
                         ulang yang sudah selesai. --}}
                    <div class="card orcha-kartu h-100 orcha-pesan-kartu {{ $baris['sudah_dibaca'] ? '' : 'belum' }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-bold">
                                        {{ $baris['nama'] }}
                                        @unless ($baris['sudah_dibaca'])
                                            <span class="orcha-titik-baru" title="Belum dibaca"></span>
                                        @endunless
                                    </div>
                                    <div class="text-muted small">
                                        {{ $baris['whatsapp'] }}
                                        {{ $baris['email'] ? '· ' . $baris['email'] : '' }}
                                    </div>
                                </div>
                                <span class="badge orcha-lencana-bayar-diterima">{{ $baris['keperluan_label'] }}</span>
                            </div>

                            {{-- Dipenggal tiga baris: kartu yang tingginya mengikuti panjang
                                 pesan membuat daftar melompat-lompat, dan yang panjang justru
                                 perlu dibuka utuh di halaman detailnya. --}}
                            <p class="small mb-3 orcha-pesan-cuplik">{{ $baris['pesan'] }}</p>

                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <span class="text-muted" style="font-size:.78rem">
                                    {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->translatedFormat('d M Y, H:i') }}
                                </span>

                                <div class="d-flex gap-2 align-items-center">
                                    @if ($baris['sudah_dibaca'])
                                        <span class="text-muted orcha-ikon-teks" style="font-size:.76rem">
                                            <i class="bi bi-check2-all"></i> dibaca
                                        </span>
                                    @endif

                                    <a href="{{ route('admin.orcha.pesan.detail', $baris['id']) }}" wire:navigate
                                        class="btn btn-sm orcha-aksi orcha-aksi-lihat" title="Buka pesan selengkapnya">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ \App\Support\TautanWa::kirim($baris['whatsapp'], 'Halo ' . $baris['nama'] . ', terima kasih sudah menghubungi Orcha Journey.') }}"
                                        target="_blank" rel="noopener"
                                        class="btn btn-sm orcha-aksi orcha-aksi-wa" title="Balas lewat WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>

                                    @unless ($baris['sudah_dibaca'])
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                            title="Tandai sudah dibaca"
                                            wire:click="tandaiDibaca({{ $baris['id'] }})" wire:loading.attr="disabled">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body text-center py-5">
                            <div class="empty-state-icon-wrapper mx-auto mb-2">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <p class="text-muted mb-0">Belum ada pesan yang cocok.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @include('livewire.pages.admin.orcha.partials.paginasi')
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
    <style>
        /* Garis kiri tebal + titik: dua penanda untuk hal yang sama, karena
           yang belum dibaca harus terlihat tanpa dicari. */
        .orcha-pesan-kartu.belum {
            border-left: 4px solid #1d6fa5;
        }

        .orcha-titik-baru {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-left: .35rem;
            border-radius: 50%;
            background: #1d6fa5;
            vertical-align: middle;
        }

        /* Tiga baris, sisanya di halaman detail. */
        .orcha-pesan-cuplik {
            white-space: pre-line;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</div>
