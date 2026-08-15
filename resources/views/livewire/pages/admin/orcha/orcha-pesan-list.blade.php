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
                    <div class="card orcha-kartu h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-bold">{{ $baris['nama'] }}</div>
                                    <div class="text-muted small">
                                        {{ $baris['whatsapp'] }}
                                        {{ $baris['email'] ? '· ' . $baris['email'] : '' }}
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark">{{ $baris['keperluan_label'] }}</span>
                            </div>

                            <p class="small mb-3" style="white-space: pre-line">{{ $baris['pesan'] }}</p>

                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <span class="text-muted" style="font-size:.78rem">
                                    {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->translatedFormat('d M Y, H:i') }}
                                </span>

                                <div class="d-flex gap-2">
                                    <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $baris['whatsapp'])) }}"
                                        target="_blank" rel="noopener"
                                        class="btn btn-sm btn-success rounded-3 orcha-tombol">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>Balas</span>
                                    </a>

                                    @if ($baris['sudah_dibaca'])
                                        <span class="badge bg-success-subtle text-success align-self-center">
                                            Sudah dibaca
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-light border rounded-3 orcha-tombol"
                                            wire:click="tandaiDibaca({{ $baris['id'] }})" wire:loading.attr="disabled">
                                            <i class="bi bi-check2"></i>
                                            <span>Tandai dibaca</span>
                                        </button>
                                    @endif
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
</div>
