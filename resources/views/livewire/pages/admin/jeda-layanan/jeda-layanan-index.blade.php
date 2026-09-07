@section('title')
Jeda Layanan || lemon
@stop

<div>
    <style>
        /* Ditulis inline: public/build tidak ikut terdeploy ke server. */

        /* Glyph Bootstrap Icons punya line-height bawaan yang menariknya turun,
           sehingga terlihat melenceng di dalam kotak yang sudah di-flex-center.
           Pola perbaikan yang sama dipakai di daftar produk. */
        .jl-ikon i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; }
        .jl-ikon i.bi::before { display: block; line-height: 1; }

        .jl-ringkas { display: flex; align-items: center; gap: 14px; }
        .jl-ringkas-teks { line-height: 1.35; }
        .jl-ringkas-teks b { display: block; font-size: .98rem; }
        .jl-ringkas-teks small { color: #94a3b8; }

        .jl-catatan {
            font-size: .86rem; line-height: 1.65; color: #64748b;
            background: #f8fafc; border: 1px solid #eef2f7; border-radius: 14px;
            padding: 16px 18px; margin-bottom: 22px;
        }
        .jl-catatan b { color: #475569; }

        .jl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: stretch; }
        @media (max-width: 1150px) { .jl-grid { grid-template-columns: 1fr; } }

        .jl-kartu {
            position: relative; overflow: hidden;
            border: 1px solid #e8ebf1; border-radius: 18px; padding: 22px 22px 22px 26px;
            background: #fff; display: flex; flex-direction: column; gap: 18px;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        /* Pita tepi: keadaan kartu terbaca sekilas tanpa membaca tulisannya. */
        .jl-kartu::before {
            content: ""; position: absolute; top: 0; bottom: 0; left: 0; width: 4px;
            background: #22c55e;
        }
        .jl-kartu:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(15, 23, 42, .08); }
        .jl-kartu.is-jeda { border-color: #f6dcae; background: #fffdf7; }
        .jl-kartu.is-jeda::before { background: #f59e0b; }

        .jl-atas { display: flex; align-items: center; gap: 14px; }
        .jl-nama { font-weight: 700; font-size: 1.05rem; line-height: 1.25; margin-bottom: 5px; }

        .jl-pil {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 11px; border-radius: 999px;
            font-size: .72rem; font-weight: 700; letter-spacing: .01em;
            background: #dcfce7; color: #15803d;
        }
        .jl-pil::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .jl-kartu.is-jeda .jl-pil { background: #fef3c7; color: #b45309; }

        .jl-blok { border-top: 1px dashed #e8ebf1; padding-top: 15px; }
        .jl-kartu.is-jeda .jl-blok { border-top-color: #f2e3c4; }
        .jl-label {
            font-size: .67rem; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #a0aab8; margin-bottom: 9px;
        }

        .jl-chips { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 6px; }
        .jl-chips li {
            font-size: .76rem; padding: 5px 11px; border-radius: 9px;
            background: #f1f5f9; color: #475569; border: 1px solid #e6ebf1;
        }
        .jl-kartu.is-jeda .jl-chips li { background: #fef6e4; color: #92400e; border-color: #f6dcae; }
        .jl-kosong { font-size: .78rem; color: #a0aab8; font-style: italic; }

        .jl-pesan { display: flex; gap: 8px; }
        .jl-pesan .form-control {
            border-radius: 11px; font-size: .82rem; padding: 9px 12px;
            border-color: #e2e8f0; background: #fbfcfe;
        }
        .jl-pesan .form-control:focus { background: #fff; }
        .jl-pesan .btn {
            border-radius: 11px; white-space: nowrap; font-size: .8rem; font-weight: 600;
            padding-inline: 16px; border-color: #e2e8f0; color: #475569; background: #fff;
        }
        .jl-pesan .btn:hover { background: #f1f5f9; color: #1e293b; }

        .jl-pratinjau { margin-top: 10px; }
        .jl-pratinjau span {
            display: block; font-size: .66rem; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #b6c0cd; margin-bottom: 3px;
        }
        .jl-pratinjau p {
            margin: 0; font-size: .79rem; line-height: 1.55; color: #64748b; font-style: italic;
        }
        .jl-kartu.is-jeda .jl-pratinjau p { color: #92400e; }

        .jl-aksi { margin-top: auto; padding-top: 4px; }
        .jl-aksi .btn {
            width: 100%; border-radius: 12px; font-weight: 600; padding: 11px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .jl-aksi .btn i.bi { display: flex; align-items: center; line-height: 1; font-size: 1rem; }
        .jl-aksi .btn i.bi::before { display: block; line-height: 1; }
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
                    <div class="jl-ringkas">
                        <span class="stat-icon-wrapper jl-ikon {{ $adaJeda ? 'bg-gradient-red' : 'bg-gradient-green' }}">
                            <i class="bi bi-{{ $adaJeda ? 'pause-fill' : 'check-lg' }}"></i>
                        </span>
                        <div class="jl-ringkas-teks">
                            <b>
                                @if ($adaJeda)
                                Dijeda: {{ collect($jeda)->filter(fn ($j) => $j['dijeda'])->keys()->map(fn ($k) => $labelJeda[$k])->implode(', ') }}
                                @else
                                Semua layanan menerima pesanan
                                @endif
                            </b>
                            <small>Status pemesanan layanan jasa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <p class="jl-catatan">
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
                            <span class="stat-icon-wrapper jl-ikon {{ $jeda[$jenis]['dijeda'] ? 'bg-gradient-red' : 'bg-gradient-green' }}">
                                <i class="bi bi-{{ $jeda[$jenis]['dijeda'] ? 'pause-fill' : 'check-lg' }}"></i>
                            </span>
                            <div>
                                <div class="jl-nama">{{ $label }}</div>
                                <span class="jl-pil">
                                    {{ $jeda[$jenis]['dijeda'] ? 'Pesanan ditutup' : 'Menerima pesanan' }}
                                </span>
                            </div>
                        </div>

                        <div class="jl-blok">
                            <div class="jl-label">Produk yang tercakup</div>
                            @if (empty($produkPerJenis[$jenis]))
                            <div class="jl-kosong">Belum ada produk jenis ini.</div>
                            @else
                            <ul class="jl-chips">
                                @foreach ($produkPerJenis[$jenis] as $nama)
                                <li>{{ $nama }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>

                        <div class="jl-blok">
                            <div class="jl-label">Keterangan untuk pembeli</div>
                            <div class="jl-pesan">
                                <input type="text" class="form-control" maxlength="200"
                                    wire:model="jeda.{{ $jenis }}.pesan"
                                    placeholder="Opsional — ada kalimat bawaan"
                                    @disabled(! $bolehKelola)>
                                <button class="btn" type="button"
                                    wire:click="simpanPesan('{{ $jenis }}')"
                                    wire:loading.attr="disabled" wire:target="simpanPesan('{{ $jenis }}')"
                                    @disabled(! $bolehKelola)>
                                    Simpan
                                </button>
                            </div>

                            {{-- Apa yang BENAR-BENAR dibaca pembeli, bukan tebakan admin. --}}
                            <div class="jl-pratinjau">
                                <span>Dibaca pembeli</span>
                                <p>&ldquo;{{ \App\Support\JedaLayanan::pesan($jenis) }}&rdquo;</p>
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
