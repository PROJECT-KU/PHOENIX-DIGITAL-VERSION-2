<div>
    @once
    <style>
        .lt-bungkus { max-width: 720px; margin: 0 auto; padding: clamp(24px, 5vw, 56px) 16px 64px; }
        .lt-kartu { background: #fff; border: 1px solid #e8ecf2; border-radius: 20px; padding: clamp(20px, 3.5vw, 30px); box-shadow: 0 18px 40px -34px rgba(15, 23, 42, .5); }
        .lt-judul { margin: 0 0 6px; font-size: clamp(1.4rem, 3.4vw, 1.9rem); font-weight: 800; color: #0f172a; }
        .lt-ket { margin: 0 0 22px; font-size: .92rem; line-height: 1.6; color: #64748b; }
        .lt-medan { display: block; margin-bottom: 14px; }
        .lt-medan > span { display: block; margin-bottom: 7px; font-size: .84rem; font-weight: 700; color: #334155; }
        .lt-medan input { width: 100%; padding: 13px 15px; border: 1.5px solid #e8ecf2; border-radius: 12px; font-size: .95rem; color: #0f172a; }
        .lt-medan input:focus { outline: none; border-color: #f26522; box-shadow: 0 0 0 4px rgba(242, 101, 34, .14); }
        .lt-galat { display: block; margin-top: 6px; font-size: .8rem; font-weight: 600; color: #dc2626; }
        .lt-tombol { display: inline-flex; align-items: center; gap: 8px; padding: 13px 24px; border: 0; border-radius: 12px; background: #f26522; color: #fff; font-size: .95rem; font-weight: 700; cursor: pointer; }
        .lt-tombol:hover { background: #d9541a; }
        .lt-status { display: inline-flex; align-items: center; gap: 7px; padding: 6px 13px; border-radius: 999px; font-size: .84rem; font-weight: 700; }
        .lt-rinci { display: grid; gap: 10px; margin: 18px 0; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
        .lt-rinci > div { padding: 12px 14px; border-radius: 13px; background: #f8fafc; border: 1px solid #eef2f7; }
        .lt-rinci small { display: block; font-size: .72rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #94a3b8; }
        .lt-rinci b { display: block; margin-top: 3px; font-size: .92rem; color: #0f172a; overflow-wrap: anywhere; }
        .lt-kutip { margin: 0 0 18px; padding: 14px 16px; border-left: 3px solid #f26522; border-radius: 0 12px 12px 0; background: #fff7f2; font-size: .94rem; line-height: 1.65; color: #334155; white-space: pre-wrap; }
        .lt-balasan { display: grid; gap: 12px; }
        .lt-balasan-baris { padding: 14px 16px; border-radius: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; }
        .lt-balasan-baris small { display: block; margin-bottom: 6px; font-size: .76rem; font-weight: 700; color: #15803d; }
        .lt-balasan-baris p { margin: 0; font-size: .94rem; line-height: 1.65; color: #334155; white-space: pre-wrap; }
        .lt-kosong { padding: 18px; border-radius: 14px; background: #f8fafc; border: 1px dashed #cbd5e1; font-size: .9rem; color: #64748b; text-align: center; }
        .lt-bantu { margin-top: 20px; font-size: .86rem; line-height: 1.6; color: #64748b; }
        .lt-bantu a { color: #f26522; font-weight: 700; }
    </style>
    @endonce

    <div class="lt-bungkus">
        <div class="lt-kartu">
            <h1 class="lt-judul">Status Pesan Anda</h1>

            @if ($pesan)
                @php [$stLabel, , $stWarna] = $pesan->tampilanStatus(); @endphp

                <p class="lt-ket">Berikut perkembangan terakhir untuk tiket <b>{{ $pesan->ticket }}</b>.</p>

                <span class="lt-status" style="background: {{ $stWarna }}1a; color: {{ $stWarna }};">
                    <i class="bi bi-circle-fill" style="font-size: .5rem;"></i>{{ $stLabel }}
                </span>

                <div class="lt-rinci">
                    <div><small>Nomor tiket</small><b>{{ $pesan->ticket }}</b></div>
                    <div><small>Masuk</small><b>{{ $pesan->created_at?->locale('id')->translatedFormat('d F Y, H:i') }}</b></div>
                    <div>
                        <small>Balasan pertama</small>
                        <b>{{ $pesan->replied_at?->locale('id')->translatedFormat('d F Y, H:i') ?: 'Belum dibalas' }}</b>
                    </div>
                </div>

                <p class="lt-ket" style="margin-bottom: 8px;"><b>Pesan yang Anda kirim</b></p>
                <blockquote class="lt-kutip">{{ $pesan->message }}</blockquote>

                <p class="lt-ket" style="margin-bottom: 8px;"><b>Balasan dari kami</b></p>
                @if ($this->balasan->isNotEmpty())
                    <div class="lt-balasan">
                        @foreach ($this->balasan as $b)
                            <div class="lt-balasan-baris">
                                <small>Dibalas {{ $b->created_at?->locale('id')->translatedFormat('d F Y, H:i') }} WIB</small>
                                <p>{{ $b->isi }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="lt-kosong">
                        Belum ada balasan yang tercatat. Kami membalas pada jam operasional 08.00–21.00 WIB.
                    </div>
                @endif

                <p class="lt-bantu">
                    Ada yang ingin ditambahkan? Balas surel tanda terima Anda, atau
                    <a href="{{ route('contact') }}" wire:navigate>kirim pesan baru</a> dengan menyebut nomor tiket di atas.
                </p>
            @else
                @if ($dicari)
                    <div class="lt-kosong" style="margin-bottom: 20px;">
                        Tiket dengan nomor dan alamat surel itu tidak kami temukan. Periksa lagi nomornya ya.
                    </div>
                @endif

                <p class="lt-ket">
                    Masukkan nomor tiket yang ada di surel tanda terima beserta alamat surel yang Anda pakai saat mengirim pesan.
                </p>

                <form wire:submit="cari">
                    <label class="lt-medan">
                        <span>Nomor tiket</span>
                        <input type="text" wire:model="ticket" placeholder="Misalnya: TKT-AB12-CD34" autocomplete="off">
                        @error('ticket') <span class="lt-galat">{{ $message }}</span> @enderror
                    </label>

                    <label class="lt-medan">
                        <span>Alamat surel Anda</span>
                        <input type="email" wire:model="email" placeholder="nama@email.com" autocomplete="email">
                        @error('email') <span class="lt-galat">{{ $message }}</span> @enderror
                    </label>

                    <button type="submit" class="lt-tombol" wire:loading.attr="disabled">
                        <span wire:loading.remove><i class="bi bi-search"></i> Lihat status</span>
                        <span wire:loading>Mencari…</span>
                    </button>
                </form>

                <p class="lt-bantu">
                    Surel tanda terimanya tidak ketemu? <a href="{{ route('contact') }}" wire:navigate>Hubungi kami</a> lewat WhatsApp saja.
                </p>
            @endif
        </div>
    </div>
</div>
