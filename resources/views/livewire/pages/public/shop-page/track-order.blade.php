<div class="lcp-page">
    <style>
        /* ===== Lacak Pesanan =====
           Bahasa visual sama dengan Keranjang, Checkout, Pembayaran, dan
           halaman sukses: kartu putih bersudut 18px, ubin ikon berwarna di
           kepala, warna per bagian lewat --c.

           Kelas lcp-*: aturan .co-*, .pay-*, .ph-empty*, dan .cart-summary-note
           yang lama ada di public-custom-styles.css — berkas yang lewat Vite ke
           public/build, dan folder itu masuk .gitignore, jadi beku di server
           sampai ada rsync. Ditulis inline supaya tampilan ini ikut `git pull`. */
        .lcp-page { --lcp-ink: #1c1f26; --lcp-muted: #6b7280; --lcp-line: #eceff3; --lcp-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .lcp-sec { padding: 22px 0 64px; }

        /* ===== Kartu =====
           Selebar kartu kepala — tidak dipatok angka, sebab kartu kepala
           digambar ::before yang menjorok 12px dari tiap sisi .container dan
           isi .container sendiri sudah berjarak 12px karena padding bawaan
           Bootstrap. Isinya dibatasi .lcp-dalam supaya baris kalimat dan lebar
           isian tetap nyaman. */
        .lcp-kartu {
            position: relative; overflow: hidden;
            background: #fff; border: 1px solid var(--lcp-line); border-radius: 18px;
        }
        .lcp-kartu + .lcp-kartu { margin-top: 16px; }
        .lcp-kepala {
            position: relative; z-index: 1;
            display: flex; align-items: center; gap: 10px; padding: 15px 18px; border-bottom: 1px solid var(--lcp-line);
            font-family: var(--lcp-font); font-weight: 800; font-size: 1rem; color: var(--lcp-ink); letter-spacing: -.015em;
        }
        .lcp-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 34px; height: 34px; border-radius: 11px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 82%, #0f172a);
        }
        .lcp-ubin i.bi, .lcp-ubin i.bi::before { display: block; line-height: 1; }
        .lcp-isi { position: relative; z-index: 1; padding: 22px 18px 24px; }
        .lcp-dalam { max-width: 560px; margin: 0 auto; }

        /* Aksen sayap — sapuan cahaya pojok (perlakuan kartu "Cara Pesan" di
           beranda) + titik memudar (kartu kepala). Tanpa ini kartu selebar
           kepala dengan isi 560px terbaca hampa di kiri-kanannya. */
        .lcp-kartu::before {
            content: ""; position: absolute; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(42% 130% at 100% 0%, color-mix(in srgb, var(--c) 16%, transparent) 0%, transparent 72%),
                radial-gradient(42% 130% at 0% 100%, color-mix(in srgb, var(--c) 13%, transparent) 0%, transparent 72%),
                radial-gradient(36% 110% at 0% 0%, rgba(251, 169, 25, .09) 0%, transparent 66%);
        }
        /* Topeng bening di 26%-74%, tepat di luar kolom isi, jadi titik tidak
           pernah jatuh di belakang teks maupun kotak isian. */
        .lcp-kartu::after {
            content: ""; position: absolute; inset: 0; z-index: 0; pointer-events: none;
            background-image: radial-gradient(color-mix(in srgb, var(--c) 24%, transparent) 1px, transparent 1px);
            background-size: 18px 18px;
            -webkit-mask-image: linear-gradient(90deg, #000 0%, transparent 26%, transparent 74%, #000 100%);
            mask-image: linear-gradient(90deg, #000 0%, transparent 26%, transparent 74%, #000 100%);
        }
        @media (max-width: 991.98px) {
            /* Isi memenuhi kartunya; tidak ada sayap yang perlu diisi, dan
               titiknya justru akan menindih kalimat. */
            .lcp-kartu::after { display: none; }
        }

        /* ===== Jalur langkah =====
           Bentuk yang sama dengan /checkout dan /payment. Di halaman lacak,
           inilah jawaban atas pertanyaan yang membuat orang datang ke sini:
           "pesanan saya sudah sampai mana?" */
        .lcp-jalur { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 20px; }
        .lcp-jalur::before { content: ""; position: absolute; z-index: 0; top: 17px; left: 12.5%; right: 12.5%; border-top: 2px dashed #f8d8bf; }
        .lcp-henti { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 7px; text-align: center; }
        .lcp-henti-bulat {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%; font-size: .92rem;
            background: #fff; border: 2px solid var(--lcp-line); color: #c3cad3;
        }
        .lcp-henti-bulat i.bi, .lcp-henti-bulat i.bi::before { display: block; line-height: 1; }
        .lcp-henti-teks { font-size: .76rem; font-weight: 700; color: #b4bcc6; }
        .lcp-henti.is-lewat .lcp-henti-bulat { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
        .lcp-henti.is-lewat .lcp-henti-teks { color: #16a34a; }
        /* border:0, bukan border-color:transparent — cincin transparan di atas
           latar gradasi meninggalkan jahitan siku di dalam lingkaran. */
        .lcp-henti.is-kini .lcp-henti-bulat {
            background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff;
            box-shadow: 0 8px 18px -8px rgba(242, 101, 34, .85);
        }
        .lcp-henti.is-kini .lcp-henti-teks { color: var(--lcp-ink); }
        .lcp-henti.is-tuntas .lcp-henti-bulat {
            background: linear-gradient(135deg, #4ade80, #16a34a); border: 0; color: #fff;
            box-shadow: 0 8px 18px -8px rgba(22, 163, 74, .85);
        }
        .lcp-henti.is-tuntas .lcp-henti-teks { color: #15803d; }
        .lcp-henti.is-gagal .lcp-henti-bulat {
            background: linear-gradient(135deg, #f87171, #dc2626); border: 0; color: #fff;
            box-shadow: 0 8px 18px -8px rgba(220, 38, 38, .8);
        }
        .lcp-henti.is-gagal .lcp-henti-teks { color: #b91c1c; }

        /* ===== Isian ===== */
        .lcp-medan { display: flex; flex-direction: column; gap: 7px; }
        .lcp-medan + .lcp-medan { margin-top: 14px; }
        .lcp-medan label { font-size: .84rem; font-weight: 700; color: var(--lcp-ink); margin: 0; }
        .lcp-page .form-control {
            width: 100%; border: 1.5px solid #e7ebf0; border-radius: 12px; padding: 11px 14px;
            font-size: .92rem; color: var(--lcp-ink); background: #fff;
            transition: border-color .16s ease, box-shadow .16s ease;
        }
        .lcp-page .form-control::placeholder { color: #aeb6c0; }
        .lcp-page .form-control:focus { outline: 0; border-color: #f26522; box-shadow: 0 0 0 3px rgba(242, 101, 34, .13); }
        .lcp-err { display: flex; align-items: center; gap: 5px; font-size: .78rem; font-weight: 600; color: #dc2626; }
        .lcp-err::before { content: "\F33A"; font-family: "bootstrap-icons"; line-height: 1; }

        /* ===== Tombol ===== */
        .lcp-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 48px; padding: 0 20px; border: 1.5px solid transparent; border-radius: 13px;
            text-decoration: none; cursor: pointer;
            font-family: var(--lcp-font); font-weight: 700; font-size: .9rem; white-space: nowrap;
            transition: filter .16s ease, transform .16s ease, border-color .16s ease, color .16s ease;
        }
        .lcp-btn > span { display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .lcp-btn i.bi, .lcp-btn i.bi::before { display: block; line-height: 1; font-size: .95rem; }
        .lcp-btn:disabled { opacity: .65; cursor: wait; }
        /* border:0 — gradasi harus mengisi sampai tepi, bukan berhenti di cincin
           border transparan dan menyisakan garis pucat. */
        .lcp-btn-utama { width: 100%; margin-top: 18px; background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff; box-shadow: 0 12px 24px -14px rgba(242, 101, 34, .85); }
        .lcp-btn-utama:not(:disabled):hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .lcp-btn-garis { background: #fff; border-color: #e5e0d8; color: var(--lcp-ink); }
        .lcp-btn-garis:hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }

        /* ===== Hasil ===== */
        .lcp-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 7px 0; font-size: .86rem; color: var(--lcp-muted); }
        .lcp-row b { font-family: var(--lcp-font); font-weight: 700; color: var(--lcp-ink); text-align: right; }
        .lcp-garis { height: 1px; margin: 12px 0; background: repeating-linear-gradient(to right, var(--lcp-line) 0 6px, transparent 6px 12px); }

        .lcp-item { display: grid; grid-template-columns: 40px minmax(0, 1fr) auto; align-items: center; gap: 11px; padding: 11px 0; }
        .lcp-item + .lcp-item { border-top: 1px dashed var(--lcp-line); }
        /* Logo produk bila berkasnya ada, ikon kategori bila tidak — sama
           dengan Keranjang, Checkout, Pembayaran, dan halaman sukses. */
        .lcp-item-ubin {
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            width: 40px; height: 40px; border-radius: 12px; font-size: .92rem;
            background: color-mix(in srgb, var(--k) 12%, #fff); color: color-mix(in srgb, var(--k) 82%, #0f172a);
        }
        .lcp-item-ubin i.bi, .lcp-item-ubin i.bi::before { display: block; line-height: 1; }
        .lcp-item-ubin img { max-width: 76%; max-height: 76%; object-fit: contain; mix-blend-mode: multiply; }
        .lcp-item-nama { font-family: var(--lcp-font); font-weight: 700; font-size: .86rem; color: var(--lcp-ink); line-height: 1.35; }
        .lcp-item-ket { font-size: .76rem; color: var(--lcp-muted); margin-top: 1px; }
        .lcp-item-harga { font-family: var(--lcp-font); font-weight: 700; font-size: .86rem; color: var(--lcp-ink); white-space: nowrap; }

        .lcp-total { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 12px 0 0; }
        .lcp-total span { font-family: var(--lcp-font); font-weight: 700; font-size: .95rem; color: var(--lcp-ink); }
        /* Warna total mengikuti nasib uangnya, sama seperti di halaman sukses
           dan halaman kedaluwarsa:
             - lunas/selesai -> HIJAU, uangnya benar-benar masuk;
             - dibatalkan    -> KELABU dicoret, uangnya tidak jadi berpindah,
                                jadi jangan dirayakan dengan gradasi jingga;
             - belum dibayar -> jingga, masih menunggu tindakan. */
        .lcp-total strong {
            font-family: var(--lcp-font); font-weight: 800; font-size: 1.4rem; letter-spacing: -.02em;
            background: linear-gradient(135deg, #fba919, #f26522); -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .lcp-total.is-lunas strong { background: linear-gradient(135deg, #4ade80, #16a34a); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .lcp-total.is-batal strong { background: none; -webkit-background-clip: border-box; background-clip: border-box; color: #9aa3af; text-decoration: line-through; }
        .lcp-aksi { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .lcp-aksi .lcp-btn { flex: 1 1 180px; }

        /* ===== Tidak ditemukan ===== */
        .lcp-kosong { text-align: center; }
        .lcp-kosong-ic {
            width: 76px; height: 76px; border-radius: 50%; margin: 0 auto 16px;
            display: grid; place-items: center; font-size: 1.9rem;
            color: #b45309; background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fde68a;
        }
        .lcp-kosong-ic i.bi, .lcp-kosong-ic i.bi::before { display: block; line-height: 1; }
        .lcp-kosong h3 { font-family: var(--lcp-font); font-weight: 800; font-size: 1.15rem; color: var(--lcp-ink); margin: 0 0 8px; }
        .lcp-kosong p { font-size: .88rem; color: var(--lcp-muted); line-height: 1.7; margin: 0 auto 16px; max-width: 420px; }

        .lcp-nota { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 18px 0 0; font-size: .78rem; color: var(--lcp-muted); }
        .lcp-nota i.bi { font-size: .85rem; color: #64748b; }
        .lcp-nota i.bi::before { display: block; line-height: 1; }

        @media (max-width: 575.98px) {
            .lcp-isi { padding: 18px 15px 20px; }
            .lcp-jalur { gap: 4px; }
            .lcp-henti-teks { font-size: .67rem; }
            .lcp-aksi .lcp-btn { flex: 1 1 100%; }
            .lcp-total strong { font-size: 1.25rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .lcp-btn, .lcp-btn:hover, .lcp-page .form-control { transition: none; transform: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title" style="--c: #2563eb">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-search"></i> Lacak Pesanan</span>
                <h1>Lacak Pesanan Anda</h1>
                <p>Cukup masukkan <b>Nomor Order</b> &amp; <b>Nomor HP</b> yang dipakai saat memesan — tanpa perlu login.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">Lacak Pesanan</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="lcp-sec">
        <div class="container">
            {{-- Form --}}
            <div class="lcp-kartu" style="--c: #2563eb">
                <div class="lcp-kepala">
                    <span class="lcp-ubin"><i class="bi bi-box-seam"></i></span> Cari Pesanan
                </div>
                <div class="lcp-isi">
                    <div class="lcp-dalam">
                        <form wire:submit="track">
                            <div class="lcp-medan">
                                <label>Nomor Order <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="orderNumber"
                                    placeholder="Contoh: INV-20260712-0009">
                                @error('orderNumber') <span class="lcp-err">{{ $message }}</span> @enderror
                            </div>
                            <div class="lcp-medan">
                                <label>Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="phone"
                                    placeholder="Contoh: 0895xxxxxxx">
                                @error('phone') <span class="lcp-err">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit" class="lcp-btn lcp-btn-utama"
                                wire:loading.attr="disabled" wire:target="track">
                                <span wire:loading.remove wire:target="track"><i class="bi bi-search"></i> Lacak Pesanan</span>
                                <span wire:loading wire:target="track"><span class="spinner-border spinner-border-sm"></span> Mencari...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Hasil --}}
            @if ($searched)
                @if ($order)
                    @php
                        /*
                         | Di halaman inilah jalur perhentian paling berguna: orang
                         | datang ke sini justru untuk bertanya "pesanan saya sudah
                         | sampai mana?". Statusnya diterjemahkan jadi posisi pada
                         | jalur yang sama dengan /checkout dan /payment.
                         */
                        $st = $order->status;
                        $batal = $st === 'cancelled';
                        $tuntas = $st === 'completed';
                        $sudahBayar = in_array($st, ['paid', 'processing', 'completed'], true);
                    @endphp

                    <div class="lcp-kartu" style="--c: {{ $batal ? '#dc2626' : ($tuntas ? '#16a34a' : '#f26522') }}">
                        <div class="lcp-kepala">
                            <span class="lcp-ubin"><i class="bi bi-receipt"></i></span> Pesanan {{ $order->order_number }}
                        </div>
                        <div class="lcp-isi">
                            <div class="lcp-dalam">
                                <div class="lcp-jalur">
                                    <div class="lcp-henti is-lewat">
                                        <span class="lcp-henti-bulat"><i class="bi bi-check-lg"></i></span>
                                        <span class="lcp-henti-teks">Keranjang</span>
                                    </div>
                                    <div class="lcp-henti is-lewat">
                                        <span class="lcp-henti-bulat"><i class="bi bi-check-lg"></i></span>
                                        <span class="lcp-henti-teks">Data &amp; Promo</span>
                                    </div>
                                    <div class="lcp-henti {{ $batal ? 'is-gagal' : ($sudahBayar ? 'is-lewat' : 'is-kini') }}">
                                        <span class="lcp-henti-bulat"><i class="bi {{ $batal ? 'bi-x-lg' : ($sudahBayar ? 'bi-check-lg' : 'bi-credit-card-2-front-fill') }}"></i></span>
                                        <span class="lcp-henti-teks">Bayar</span>
                                    </div>
                                    <div class="lcp-henti {{ $tuntas ? 'is-tuntas' : ($sudahBayar ? 'is-kini' : '') }}">
                                        <span class="lcp-henti-bulat"><i class="bi {{ $tuntas ? 'bi-check-lg' : 'bi-inbox-fill' }}"></i></span>
                                        <span class="lcp-henti-teks">Terima</span>
                                    </div>
                                </div>

                                <div class="lcp-row"><span>Status</span><span>{!! $order->getStatusBadge() !!}</span></div>
                                <div class="lcp-row"><span>Tanggal</span><b>{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</b></div>

                                <div class="lcp-garis"></div>

                                @foreach ($order->items as $item)
                                    @php
                                        // Warna & ikon dari KATEGORI produk — taksonomi yang sama
                                        // dengan Keranjang, Checkout, dan Shop.
                                        $katLcp = \App\Support\KategoriBeranda::untukProduk($item->product_name ?? '');
                                        $warnaLcp = $katLcp['warna'] ?? '#f26522';
                                        $ikonLcp = $katLcp['ikon'] ?? 'bi-box-seam';

                                        // Logo dipakai HANYA bila berkasnya ada; memasangnya tanpa
                                        // syarat membuat teks alt tampil sebagai gambar rusak.
                                        $berkasLcp = $item->product_image ? basename($item->product_image) : null;
                                        $logoLcp = null;
                                        if ($berkasLcp) {
                                            foreach (['Product', 'ProductBundlings'] as $folderLcp) {
                                                if (is_file(public_path('storage/img/'.$folderLcp.'/'.$berkasLcp))) {
                                                    $logoLcp = asset('storage/img/'.$folderLcp.'/'.$berkasLcp);
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <div class="lcp-item" style="--k: {{ $warnaLcp }}">
                                        <span class="lcp-item-ubin">
                                            @if ($logoLcp)
                                                <img src="{{ $logoLcp }}" alt="{{ $item->product_name }}" loading="lazy"
                                                    onerror="this.remove();">
                                            @else
                                                <i class="bi {{ $ikonLcp }}"></i>
                                            @endif
                                        </span>
                                        <div>
                                            <div class="lcp-item-nama">{{ $item->product_name }} @if ($item->delivery_status === 'cancelled') <span style="color:#dc2626;font-weight:700;">· Dibatalkan</span> @endif</div>
                                            <div class="lcp-item-ket">{{ $item->getDurationLabel() }} &times;{{ $item->quantity }}</div>
                                        </div>
                                        <span class="lcp-item-harga">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach

                                <div class="lcp-garis"></div>

                                <div class="lcp-total {{ $batal ? 'is-batal' : ($sudahBayar ? 'is-lunas' : '') }}">
                                    <span>{{ $batal ? 'Total yang batal' : 'Total' }}</span>
                                    <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                                </div>

                                @if (($order->share_token && $order->status === 'completed') || $order->status === 'pending')
                                    <div class="lcp-aksi">
                                        {{-- Struk hanya untuk pesanan SELESAI — dulu tampil di
                                             semua status, termasuk pesanan yang dibatalkan. --}}
                                        @if ($order->share_token && $order->status === 'completed')
                                            <a href="{{ route('order.receipt', $order->share_token) }}" class="lcp-btn lcp-btn-utama" style="margin-top:0;">
                                                <span><i class="bi bi-file-earmark-text"></i> Lihat Struk</span>
                                            </a>
                                        @endif
                                        @if ($order->status === 'pending')
                                            <a href="{{ route('payment', $order) }}" class="lcp-btn lcp-btn-garis">
                                                <span><i class="bi bi-qr-code"></i> Lanjutkan Pembayaran</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="lcp-kartu" style="--c: #d97706">
                        <div class="lcp-kepala">
                            <span class="lcp-ubin"><i class="bi bi-question-circle"></i></span> Tidak Ditemukan
                        </div>
                        <div class="lcp-isi">
                            <div class="lcp-dalam lcp-kosong">
                                <div class="lcp-kosong-ic"><i class="bi bi-search"></i></div>
                                <h3>Pesanan tidak ditemukan</h3>
                                <p>Periksa kembali <b>Nomor Order</b> dan <b>Nomor HP</b> Anda — pastikan keduanya sama persis dengan saat memesan.</p>
                                <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20menanyakan%20pesanan%20saya."
                                    target="_blank" rel="noopener" class="lcp-btn lcp-btn-garis">
                                    <span><i class="bi bi-whatsapp"></i> Tanya Admin</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <p class="lcp-nota"><i class="bi bi-shield-lock"></i> Data pesanan hanya bisa dibuka dengan nomor order + nomor HP yang cocok.</p>
        </div>
    </section>
</div>
