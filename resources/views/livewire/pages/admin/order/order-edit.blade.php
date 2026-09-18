@section('title')
Ubah Pesanan || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.order.partials.toko-gaya')

    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $totalLama = (int) $order->total;
        $selisih = $totalBaru - $totalLama;
        $bolehUbah = ! $alasan;
    @endphp

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Ubah Pesanan</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-receipt me-1"></i>{{ $order->order_number }} · {{ $order->customer->nama ?? 'Tanpa nama' }}</span>
                    <span class="d-block">Hanya untuk pesanan yang belum dibayar. Harga dihitung ulang dari katalog.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.pesanantoko.detail', $order) }}" class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        @if ($alasan)
            <div class="pe-tolak">
                <span class="dsb-ikon" style="--c: #dc2626"><i class="bi bi-lock-fill"></i></span>
                <div>
                    <b>Pesanan ini tidak bisa diubah</b>
                    <span>{{ $alasan }}</span>
                </div>
            </div>
        @else
            <div class="pe-tata">
                <section class="pe-utama">
                    <div class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <div class="pe-kepala">
                                <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-bag-fill"></i></span>
                                <div>
                                    <b>Isi pesanan</b>
                                    <span>Ganti produk, satuan, durasi, atau jumlah.</span>
                                </div>
                            </div>

                            @if ($hargaBerubah)
                                <div class="pe-info">
                                    <i class="bi bi-info-circle"></i>
                                    <span>Sebagian harga lama berasal dari paket atau promo. Setelah disimpan, <b>semua baris memakai harga katalog normal</b> — periksa totalnya di ringkasan.</span>
                                </div>
                            @endif

                            @foreach ($baris as $i => $b)
                                @php $r = $rinci[$i] ?? ['harga' => 0, 'subtotal' => 0]; @endphp
                                <div class="pe-baris" wire:key="pe-baris-{{ $i }}">
                                    <div class="pe-baris-kepala">
                                        <span class="pe-no">{{ $i + 1 }}</span>
                                        <b>{{ $r['produk']->nama_akun ?? 'Produk belum dipilih' }}</b>
                                        @if (1 < count($baris))
                                            <button type="button" class="pe-hapus" wire:click="hapusBaris({{ $i }})" title="Hapus baris">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="pe-medan">
                                        <div class="dsb-medan pe-produk">
                                            <label class="dsb-label" for="pe-p-{{ $i }}">Produk <span class="text-danger">*</span></label>
                                            <select id="pe-p-{{ $i }}" class="dsb-isian" wire:model.live="baris.{{ $i }}.product_id">
                                                <option value="">— Pilih produk —</option>
                                                @foreach ($produkPilihan as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nama_akun }}</option>
                                                @endforeach
                                            </select>
                                            @error('baris.'.$i.'.product_id') <small class="pe-galat">{{ $message }}</small> @enderror
                                        </div>
                                        <div class="dsb-medan">
                                            <label class="dsb-label" for="pe-s-{{ $i }}">Satuan</label>
                                            <select id="pe-s-{{ $i }}" class="dsb-isian" wire:model.live="baris.{{ $i }}.duration_type">
                                                <option value="bulan">Bulan</option>
                                                <option value="tahun">Tahun</option>
                                            </select>
                                        </div>
                                        <div class="dsb-medan">
                                            <label class="dsb-label" for="pe-d-{{ $i }}">Durasi</label>
                                            <input id="pe-d-{{ $i }}" type="number" min="1" class="dsb-isian" wire:model.live.debounce.400ms="baris.{{ $i }}.duration_value">
                                        </div>
                                        <div class="dsb-medan">
                                            <label class="dsb-label" for="pe-q-{{ $i }}">Jumlah</label>
                                            <input id="pe-q-{{ $i }}" type="number" min="1" class="dsb-isian" wire:model.live.debounce.400ms="baris.{{ $i }}.quantity">
                                        </div>
                                    </div>
                                    <div class="pe-hitung">
                                        <span>{{ $rupiah($r['harga']) }} × {{ max(1, (int) $b['quantity']) }}</span>
                                        <b>{{ $rupiah($r['subtotal']) }}</b>
                                    </div>
                                </div>
                            @endforeach
                            @error('baris') <p class="pe-galat">{{ $message }}</p> @enderror

                            <button type="button" class="dsb-tombol is-lembut" wire:click="tambahBaris">
                                <i class="bi bi-plus-lg"></i><span>Tambah produk</span>
                            </button>
                        </div>
                    </div>

                    <div class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <div class="pe-kepala">
                                <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-chat-left-text-fill"></i></span>
                                <div>
                                    <b>Catatan pelanggan</b>
                                    <span>Boleh dikosongkan.</span>
                                </div>
                            </div>
                            <textarea class="dsb-isian pe-catatan" rows="3" wire:model="catatan" placeholder="Mis. akun tujuan, permintaan khusus"></textarea>
                        </div>
                    </div>
                </section>

                <aside class="pe-samping">
                    <div class="dsb-kartu">
                        <div class="dsb-kartu-isi">
                            <span class="pe-kicker">Ringkasan</span>
                            <div class="pe-ringkas"><span>Total lama</span><b>{{ $rupiah($totalLama) }}</b></div>
                            <div class="pe-ringkas"><span>Kode unik</span><b>{{ $rupiah($order->unique_code) }}</b></div>
                            <div class="pe-total">
                                <span>Total baru</span>
                                <b>{{ $rupiah($totalBaru) }}</b>
                                @if ($selisih)
                                    <small class="{{ 0 < $selisih ? 'is-naik' : 'is-turun' }}">{{ 0 < $selisih ? '+' : '−' }}{{ $rupiah(abs($selisih)) }} dari total lama</small>
                                @else
                                    <small>Sama dengan total lama</small>
                                @endif
                            </div>
                            <button type="button" class="dsb-tombol is-utama pe-simpan" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan" @disabled(! $bolehUbah)>
                                <span wire:loading.remove wire:target="simpan" class="pt-isi-tombol"><i class="bi bi-check2-circle"></i><span>Simpan Perubahan</span></span>
                                <span wire:loading.inline-flex wire:target="simpan" class="pt-isi-tombol"><span class="dsb-putar is-kecil"></span><span>Menyimpan…</span></span>
                            </button>
                            <p class="pe-ket">Beri tahu pelanggan bila totalnya berubah. Perubahan tercatat di Riwayat pesanan.</p>
                        </div>
                    </div>
                </aside>
            </div>
        @endif
    </div>

    <style>
        .pe-tata { display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 320px; align-items: start; }
        .pe-utama { display: grid; gap: 20px; min-width: 0; }
        .pe-samping { position: sticky; top: 16px; }
        @media (max-width: 991.98px) { .pe-tata { grid-template-columns: minmax(0, 1fr); } .pe-samping { position: static; } }
        .pe-kepala { display: flex; gap: 12px; align-items: center; padding-bottom: 14px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        .pe-kepala > div > b { display: block; font-size: .98rem; color: #1c1f26; }
        .pe-kepala > div > span { display: block; font-size: .8rem; color: #64748b; }
        .pe-info { display: flex; gap: 8px; padding: 10px 12px; margin-bottom: 14px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; font-size: .82rem; line-height: 1.5; }
        .pe-baris { border: 1px solid #eef2f7; border-radius: 14px; padding: 14px; margin-bottom: 12px; background: #fcfcfd; }
        .pe-baris-kepala { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .pe-baris-kepala b { flex: 1 1 auto; min-width: 0; font-size: .9rem; color: #1c1f26; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pe-no { width: 24px; height: 24px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: #7c3aed; color: #fff; font-size: .74rem; font-weight: 800; flex-shrink: 0; }
        .pe-hapus { width: 32px; height: 32px; border-radius: 9px; border: 1px solid #fecaca; background: #fff; color: #dc2626; display: inline-flex; align-items: center; justify-content: center; }
        .pe-hapus:hover { background: #dc2626; color: #fff; }
        .pe-medan { display: grid; gap: 10px; grid-template-columns: minmax(0, 2.4fr) repeat(3, minmax(0, 1fr)); }
        @media (max-width: 767.98px) { .pe-medan { grid-template-columns: repeat(3, minmax(0, 1fr)); } .pe-produk { grid-column: 1 / -1; } }
        .pe-hitung { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; padding: 9px 12px; border-radius: 11px; background: #f5f3ff; font-size: .82rem; color: #64748b; }
        .pe-hitung b { color: #4c1d95; font-size: .95rem; }
        .pe-galat { display: block; color: #dc2626; font-size: .78rem; margin: 4px 0 0; }
        .pe-catatan { min-height: 84px; resize: vertical; }
        .pe-kicker { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #f26522; margin-bottom: 10px; }
        .pe-ringkas { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #eef2f7; font-size: .86rem; color: #64748b; }
        .pe-ringkas b { color: #1c1f26; }
        .pe-total { margin: 14px 0; padding: 14px; border-radius: 14px; background: #ecfdf5; border: 1px solid #bbf7d0; }
        .pe-total span { display: block; font-size: .74rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #047857; }
        .pe-total b { display: block; font-size: 1.5rem; color: #065f46; }
        .pe-total small { display: block; font-size: .78rem; color: #64748b; }
        .pe-total small.is-naik { color: #b45309; font-weight: 700; }
        .pe-total small.is-turun { color: #047857; font-weight: 700; }
        .pe-simpan { width: 100%; justify-content: center; }
        .pe-ket { margin: 10px 0 0; font-size: .76rem; color: #94a3b8; text-align: center; }
        .pe-tolak { display: flex; gap: 14px; align-items: center; padding: 18px 20px; border-radius: 18px; background: #fef2f2; border: 1px solid #fecaca; }
        .pe-tolak b { display: block; color: #991b1b; }
        .pe-tolak > div > span { display: block; color: #7f1d1d; font-size: .88rem; }
    </style>

    @include('livewire.layout.sweetalert')
</div>
