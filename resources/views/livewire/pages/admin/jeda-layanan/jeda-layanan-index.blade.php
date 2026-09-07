@section('title')
Jeda Layanan || lemon
@stop

<div>
    <style>
        /* Ditulis inline: public/build tidak ikut terdeploy ke server. */
        .jl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        @media (max-width: 1100px) { .jl-grid { grid-template-columns: 1fr; } }

        .jl-kartu {
            border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;
            background: #fff; display: flex; flex-direction: column; gap: 12px;
        }
        .jl-kartu.is-jeda { border-color: #f0c36d; background: #fffdf6; }

        .jl-atas { display: flex; align-items: center; gap: 12px; }
        .jl-nama { font-weight: 700; font-size: 1.02rem; line-height: 1.2; }
        .jl-status { font-size: .8rem; margin-top: 2px; }
        .jl-kartu .jl-status { color: #16a34a; }
        .jl-kartu.is-jeda .jl-status { color: #b45309; }

        .jl-produk { border-top: 1px dashed #e2e8f0; padding-top: 11px; }
        .jl-produk-judul {
            font-size: .68rem; font-weight: 700; letter-spacing: .06em;
            text-transform: uppercase; color: #94a3b8; margin-bottom: 6px;
        }
        .jl-produk ul { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 5px; }
        .jl-produk li {
            font-size: .74rem; padding: 3px 9px; border-radius: 999px;
            background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
        }
        .jl-kartu.is-jeda .jl-produk li { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
        .jl-kosong { font-size: .78rem; color: #94a3b8; font-style: italic; }

        .jl-pesan-judul {
            font-size: .68rem; font-weight: 700; letter-spacing: .06em;
            text-transform: uppercase; color: #94a3b8; margin-bottom: 5px;
        }
        .jl-aksi { margin-top: auto; }
        .jl-aksi .btn { width: 100%; }
    </style>

    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="gradient-text fw-bold mb-1">Jeda Layanan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Jeda Layanan']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    @php $adaJeda = collect($jeda)->contains(fn ($j) => $j['dijeda']); @endphp
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon-wrapper {{ $adaJeda ? 'bg-gradient-red' : 'bg-gradient-green' }}">
                            <i class="bi bi-{{ $adaJeda ? 'pause-circle' : 'check-circle' }}"></i>
                        </span>
                        <div>
                            <div class="fw-bold">
                                @if ($adaJeda)
                                Dijeda: {{ collect($jeda)->filter(fn ($j) => $j['dijeda'])->keys()->map(fn ($k) => $labelJeda[$k])->implode(', ') }}
                                @else
                                Semua layanan menerima pesanan
                                @endif
                            </div>
                            <small class="text-muted">Status pemesanan layanan jasa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <p class="text-muted mb-4" style="font-size:.88rem; line-height:1.6;">
                    Menjeda layanan menutup <b>pemesanan baru</b> saja. Halaman produknya <b>tetap tampil</b> di toko
                    lengkap dengan harga — hanya tombol belinya yang ditutup, diganti keterangan yang Anda tulis di
                    bawah. Pelanggan yang sudah membayar tetap bisa mengunggah berkas memakai sisa kuotanya.
                    Tiap jenis berdiri sendiri: menjeda Cek Plagiasi tidak menyentuh Cek AI.
                </p>

                @unless ($bolehKelola)
                <div class="alert alert-light border d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-eye"></i>
                    <span>Anda hanya dapat melihat status ini. Mengubahnya membutuhkan izin <b>Kelola Jeda Layanan</b>.</span>
                </div>
                @endunless

                <div class="jl-grid">
                    @foreach ($labelJeda as $jenis => $label)
                    <div class="jl-kartu {{ $jeda[$jenis]['dijeda'] ? 'is-jeda' : '' }}">
                        <div class="jl-atas">
                            <span class="stat-icon-wrapper {{ $jeda[$jenis]['dijeda'] ? 'bg-gradient-red' : 'bg-gradient-green' }}">
                                <i class="bi bi-{{ $jeda[$jenis]['dijeda'] ? 'pause-fill' : 'check-lg' }}"></i>
                            </span>
                            <div>
                                <div class="jl-nama">{{ $label }}</div>
                                <div class="jl-status">
                                    {{ $jeda[$jenis]['dijeda'] ? 'Pesanan baru ditutup' : 'Menerima pesanan' }}
                                </div>
                            </div>
                        </div>

                        <div class="jl-produk">
                            <div class="jl-produk-judul">Produk yang tercakup</div>
                            @if (empty($produkPerJenis[$jenis]))
                            <div class="jl-kosong">Belum ada produk jenis ini.</div>
                            @else
                            <ul>
                                @foreach ($produkPerJenis[$jenis] as $nama)
                                <li>{{ $nama }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>

                        <div>
                            <div class="jl-pesan-judul">Keterangan untuk pembeli</div>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" maxlength="160"
                                    wire:model="jeda.{{ $jenis }}.pesan"
                                    placeholder="{{ \App\Support\JedaLayanan::PESAN_BAWAAN }}"
                                    @disabled(! $bolehKelola)>
                                <button class="btn btn-outline-secondary" type="button"
                                    wire:click="simpanPesan('{{ $jenis }}')"
                                    wire:loading.attr="disabled" wire:target="simpanPesan('{{ $jenis }}')"
                                    @disabled(! $bolehKelola)>
                                    Simpan
                                </button>
                            </div>
                        </div>

                        @if ($bolehKelola)
                        <div class="jl-aksi">
                            <button type="button"
                                class="btn {{ $jeda[$jenis]['dijeda'] ? 'btn-success' : 'btn-outline-danger' }} pcek-konfirmasi"
                                data-action="alihkanJeda" data-arg="{{ $jenis }}"
                                data-title="{{ $jeda[$jenis]['dijeda'] ? 'Buka kembali '.$label.'?' : 'Jeda '.$label.'?' }}"
                                data-text="{{ $jeda[$jenis]['dijeda'] ? 'Pembeli bisa memesan layanan ini lagi.' : 'Pembeli tidak bisa memesan layanan ini sampai dibuka lagi. Produknya tetap tampil di toko.' }}"
                                data-confirm="{{ $jeda[$jenis]['dijeda'] ? 'Ya, buka' : 'Ya, jeda' }}"
                                data-icon="{{ $jeda[$jenis]['dijeda'] ? 'question' : 'warning' }}">
                                <i class="bi bi-{{ $jeda[$jenis]['dijeda'] ? 'play-fill' : 'pause-fill' }}"></i>
                                {{ $jeda[$jenis]['dijeda'] ? 'Buka kembali' : 'Jeda layanan' }}
                            </button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @include('livewire.layout.sweetalert')
</div>
