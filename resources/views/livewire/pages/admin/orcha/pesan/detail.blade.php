@section('title')
Detail Pesan Kontak || lemon
@stop

@php
    $terkait = $pesan['pesanan_terkait'] ?? [];
    $lain = $pesan['pesan_lain'] ?? [];
    $sudahDibaca = $pesan['sudah_dibaca'] ?? false;

    // Ikon per keperluan, sama dengan yang dipakai di kotak masuk.
    $ikonKeperluan = [
        'open_trip' => 'bi-signpost-split',
        'private_trip' => 'bi-people',
        'study_tour' => 'bi-mortarboard',
        'sewa_kendaraan' => 'bi-truck',
        'kerja_sama' => 'bi-briefcase',
        'lainnya' => 'bi-chat-dots',
    ];
    $keperluan = $pesan['keperluan'] ?? 'lainnya';
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @if (empty($pesan))
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-inbox"></i></div>
                    <p class="text-muted mb-3">Pesan tidak bisa ditampilkan.</p>
                    <a href="{{ route('admin.orcha.pesan') }}" class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-arrow-left"></i> Kembali ke kotak masuk
                    </a>
                </div>
            </div>
        @else

            {{-- ============ KEPALA ============
                 Bentuknya menyalin lembar serah terima: judul, satu baris
                 keterangan, dan tombol pindah halaman di kanan. Yang berupa
                 PERBUATAN — menandai dibaca, menyalin, membalas — turun ke
                 kartu Tindakan di bawahnya, karena berdesakan di pojok kanan
                 nama pengirim membuat ketiganya terbaca sebagai hiasan judul. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="gradient-text fw-bold mb-1">{{ $pesan['nama'] }}</h4>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="orcha-lencana-keperluan" data-keperluan="{{ $keperluan }}">
                                    <i class="bi {{ $ikonKeperluan[$keperluan] ?? 'bi-chat-dots' }}"></i>
                                    {{ $pesan['keperluan_label'] }}
                                </span>

                                @if ($sudahDibaca)
                                    <span class="orcha-status-pesan" data-baca="sudah">
                                        <i class="bi bi-check2-all"></i>
                                        Dibaca {{ \Carbon\Carbon::parse($pesan['dibaca_pada'])->locale('id')->translatedFormat('j M, H:i') }}
                                    </span>
                                @else
                                    <span class="orcha-status-pesan" data-baca="belum">
                                        <i class="bi bi-envelope-fill"></i> Belum dibaca
                                    </span>
                                @endif

                                <span class="text-muted small">
                                    masuk
                                    {{ \Carbon\Carbon::parse($pesan['dibuat_pada'])->locale('id')->translatedFormat('j F Y, H:i') }}
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('admin.orcha.pesan') }}" wire:navigate
                            class="orcha-btn orcha-btn-lembut">
                            <i class="bi bi-arrow-left"></i> Kotak masuk
                        </a>
                    </div>
                </div>
            </div>

            {{-- ============ TINDAKAN ============
                 Berdiri sendiri, sebentuk dengan kartu Tindakan di detail sewa
                 dan detail pembatalan. Pita emas di tepi kiri muncul selama
                 pesannya belum dibaca — penanda bahwa masih ada yang harus
                 dikerjakan, bukan bahwa ada yang salah. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 orcha-kartu-tindakan {{ $sudahDibaca ? '' : 'ada' }}">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="orcha-label-kecil">
                                <i class="bi bi-lightning-charge"></i> Tindakan
                            </div>
                            <div class="text-muted" style="font-size:.8rem">
                                Balasannya sudah disiapkan lengkap dengan pertanyaan lanjutan
                                yang biasa ditanyakan untuk keperluan ini.
                            </div>
                        </div>

                        {{-- Tingginya 34px lewat .orcha-aksi-sewa, sama dengan kartu
                             Tindakan di detail sewa dan pembatalan. --}}
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @unless ($sudahDibaca)
                                <button type="button" class="orcha-btn orcha-btn-lembut orcha-aksi-sewa"
                                    wire:click="tandaiDibaca" wire:target="tandaiDibaca"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="tandaiDibaca">
                                        <i class="bi bi-check2"></i> Tandai Dibaca
                                    </span>
                                    <span wire:loading wire:target="tandaiDibaca">
                                        <span class="spinner-border spinner-border-sm me-2"
                                            role="status" aria-hidden="true"></span>Menyimpan...
                                    </span>
                                </button>
                            @endunless

                            @if ($this->tautanWa())
                                <button type="button" class="orcha-btn orcha-btn-lembut orcha-aksi-sewa"
                                    data-wa-pesan="{{ $this->pesanWa() }}"
                                    title="Salin teks balasannya untuk ditempel di WhatsApp">
                                    <i class="bi bi-clipboard"></i> Salin Balasan
                                </button>

                                <a href="{{ $this->tautanWa() }}" target="_blank" rel="noopener"
                                    class="orcha-btn orcha-btn-wa orcha-aksi-sewa"
                                    data-wa-pesan="{{ $this->pesanWa() }}">
                                    <i class="bi bi-whatsapp"></i> Balas via WA
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ ISI PESAN ============ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-chat-left-quote"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Isi Pesan</div>
                            <div class="orcha-bagian-sub">
                                Ditulis sendiri oleh pengirim lewat formulir kontak, apa adanya.
                            </div>
                        </div>
                    </div>

                    <div class="orcha-pesan-isi">{{ $pesan['pesan'] }}</div>
                </div>
            </div>

            {{-- ============ PENGIRIM ============
                 Medan berkotak, sama dengan lembar serah terima: bacaan, bukan
                 isian, tetapi tetap berbingkai supaya batas tiap keterangan
                 jelas dan tidak menyatu jadi satu paragraf. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-person-lines-fill"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Pengirim</div>
                            <div class="orcha-bagian-sub">Ke mana balasannya dikirim.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            @include('livewire.pages.admin.orcha.partials.medan', [
                                'label' => 'Nama',
                                'nilai' => $pesan['nama'],
                            ])
                        </div>
                        <div class="col-12 col-md-4">
                            @include('livewire.pages.admin.orcha.partials.medan', [
                                'label' => 'WhatsApp',
                                'nilai' => $pesan['whatsapp'] ?: null,
                                'tautan' => \App\Support\TautanWa::kirim($pesan['whatsapp'] ?? null, '') ?: null,
                            ])
                        </div>
                        <div class="col-12 col-md-4">
                            @include('livewire.pages.admin.orcha.partials.medan', [
                                'label' => 'Email',
                                'nilai' => $pesan['email'] ?: null,
                                'tautan' => $pesan['email'] ? 'mailto:' . $pesan['email'] : null,
                            ])
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ PESANAN PENGIRIM ============
                 Inilah yang menentukan nada balasan: calon pelanggan ditawari,
                 pemesan ditenangkan.

                 Dijajar mendatar, bukan ditumpuk di kolom sempit di kanan.
                 Pengirim yang sudah delapan kali memesan membuat kolom itu
                 memanjang jauh melewati isi pesannya, dan halamannya jadi
                 berat sebelah tanpa satu pun keterangan tambahan. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-bag-check"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">
                                Pesanan Pengirim
                                @if ($terkait)
                                    <span class="badge orcha-lencana-catat ms-1">{{ count($terkait) }}</span>
                                @endif
                            </div>
                            {{-- Pendek satu baris: keterangan yang mengular sampai tiga
                                 baris di layar kecil membuat ikon bagiannya melayang di
                                 tengah-tengahnya, dan kepala bagiannya berhenti terbaca
                                 sebagai satu judul. Peringatannya turun ke bawah daftar. --}}
                            <div class="orcha-bagian-sub">Dicocokkan dari nomor WhatsApp dan emailnya.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        @forelse ($terkait as $satu)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="orcha-pesanan-kartu">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <span class="orcha-kode">{{ $satu['kode'] }}</span>
                                        <span class="badge orcha-lencana-bayar-{{ $satu['status'] === 'batal' ? 'ditolak' : 'diterima' }}">
                                            {{ $satu['status_label'] }}
                                        </span>
                                    </div>
                                    <div class="fw-bold mt-1" style="font-size:.9rem">
                                        {{ $satu['keterangan'] ?: '—' }}
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        {{ $satu['jenis_label'] }}
                                        @if ($satu['mulai'])
                                            · mulai {{ \Carbon\Carbon::parse($satu['mulai'])->locale('id')->translatedFormat('j M Y') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            {{-- Bukan kekurangan data, melainkan keterangan yang berguna:
                                 orang ini belum pernah memesan, jadi pesannya pertanyaan
                                 calon pelanggan. --}}
                            <div class="col-12">
                                <div class="orcha-alasan orcha-alasan-tenang">
                                    <span class="orcha-label-kecil orcha-ikon-teks mb-0">
                                        <i class="bi bi-person-plus"></i> Belum pernah memesan
                                    </span>
                                    <div class="mt-1">
                                        Nomor dan email ini belum tercatat pada pesanan mana pun.
                                        Perlakukan sebagai pertanyaan calon pelanggan.
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($terkait)
                        <div class="text-muted mt-3" style="font-size:.78rem">
                            Kode yang dipesan dengan nomor atau email berbeda tidak ikut muncul di sini.
                        </div>
                    @endif
                </div>
            </div>

            {{-- ============ PESAN LAIN ============
                 Yang sudah tiga kali bertanya dan belum dibalas perlu
                 diperlakukan berbeda dari yang baru pertama menulis. --}}
            @if ($lain)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3 p-lg-4">
                        <div class="orcha-bagian-kepala">
                            <div class="orcha-bagian-nomor"><i class="bi bi-clock-history"></i></div>
                            <div>
                                <div class="orcha-bagian-judul">
                                    Pesan Lain dari Pengirim Ini
                                    <span class="badge orcha-lencana-catat ms-1">{{ count($lain) }}</span>
                                </div>
                                <div class="orcha-bagian-sub">
                                    Riwayat pertanyaan dari orang yang sama, terbaru lebih dulu.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach ($lain as $satu)
                                <div class="col-12 col-lg-6">
                                    <div class="orcha-pesanan-kartu {{ $satu['sudah_dibaca'] ? '' : 'belum' }} h-100">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <span class="orcha-label-kecil mb-0">{{ $satu['keperluan_label'] }}</span>
                                            <span class="text-muted" style="font-size:.76rem">
                                                {{ \Carbon\Carbon::parse($satu['dibuat_pada'])->locale('id')->translatedFormat('j M Y, H:i') }}
                                            </span>
                                        </div>

                                        @unless ($satu['sudah_dibaca'])
                                            <span class="orcha-status-pesan mt-2" data-baca="belum">
                                                <i class="bi bi-envelope-fill"></i> Belum dibaca
                                            </span>
                                        @endunless

                                        <div class="mt-1" style="font-size:.86rem">
                                            {{ \Illuminate\Support\Str::limit($satu['pesan'], 180) }}
                                        </div>

                                        <a href="{{ route('admin.orcha.pesan.detail', $satu['id']) }}" wire:navigate
                                            class="orcha-tautan-balik mt-2" style="font-size:.8rem">
                                            Buka pesan ini <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.salin-wa')
    @include('livewire.pages.admin.orcha.partials.skrip')

    <style>
        /* Isi pesan dibaca utuh, jadi diberi ruang dan jarak baris yang lega —
           bukan diperlakukan seperti keterangan kecil di sekitarnya.

           Selebar kartunya. Sempat dibatasi 78ch demi kenyamanan membaca baris
           panjang, tetapi pesan kontak kebanyakan pendek — satu-dua kalimat —
           sehingga yang terlihat justru separuh kartu menganga kosong di
           sebelah kanannya. Yang panjang pun lebih baik muat utuh. */
        .orcha-pesan-isi {
            white-space: pre-line;
            font-size: .95rem;
            line-height: 1.7;
            color: #24384a;
            background: #f7f9fb;
            border: 1px solid #e3ecf3;
            border-left: 4px solid var(--orc-primer);
            border-radius: .8rem;
            padding: 1rem 1.15rem;
        }

        /* Kartu pesanan dan kartu pesan lain: satu bentuk untuk keduanya,
           sekeluarga dengan .orcha-medan di lembar serah terima — bingkai tipis,
           sudut membulat, latar sedikit lebih terang daripada kartunya. */
        .orcha-pesanan-kartu {
            height: 100%;
            padding: .7rem .85rem .75rem;
            border: 1px solid #eaf1f7;
            border-radius: 12px;
            background: #fafdff;
        }

        /* Yang belum dibaca diberi tepi kiri, penanda yang sama dengan barisnya
           di kotak masuk. */
        .orcha-pesanan-kartu.belum {
            border-left: 4px solid var(--orc-primer);
        }

    </style>
</div>
