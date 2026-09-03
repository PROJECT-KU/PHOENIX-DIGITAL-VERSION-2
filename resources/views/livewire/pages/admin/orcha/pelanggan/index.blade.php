@section('title')
Pelanggan || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    {{-- Jendelanya digambar sendiri, bukan memakai modal Bootstrap.

         Modal Bootstrap baru muncul setelah JavaScript-nya hidup dan dipanggil
         lewat skrip. Di layar Orcha, isi jendelanya datang dari Livewire —
         menyatukan dua penguasa tampilan pada satu benda membuatnya kadang
         terbuka tanpa isi, kadang berisi tanpa terbuka. Yang di bawah ini cuma
         CSS: kalau $buka terisi, ia tergambar. --}}
    <style>
        .orcha-tirai {
            position: fixed;
            inset: 0;
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(3px);
        }

        .orcha-jendela {
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
        }

        /* Kode rujukannya dibuat besar dan berjarak huruf: ia DIBACAKAN lewat
           telepon dan disalin dengan mata, bukan diklik. */
        .orcha-kode-besar {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: .12em;
            color: #0f172a;
        }

        .orcha-pesan-siap {
            white-space: pre-wrap;
            font-size: .82rem;
            line-height: 1.6;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            padding: .85rem 1rem;
            color: #334155;
        }
    </style>

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Pelanggan',
            'keterangan' => 'Orang yang pernah memesan — trip maupun sewa kendaraan.',
        ])

        {{-- Keterangan pembuka, bukan hiasan.

             Yang membuka layar ini akan mencari tombol "tambah pelanggan" dan
             tidak menemukannya. Dijawab di sini supaya tidak ada yang mengira
             daftarnya rusak. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-bagian-kepala mb-0">
                    <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <div class="orcha-bagian-judul">Daftar ini terisi sendiri</div>
                        <div class="orcha-bagian-sub">
                            Tidak ada tombol tambah — daftarnya disusun dari pendaftaran dan
                            penyewaan yang sudah masuk, dikelompokkan menurut nomor WhatsApp.
                            Satu orang yang menulis nomornya berbeda-beda tetap satu baris.
                            <br>
                            Tombol WhatsApp membuka jendela berisi kode rujukan orang itu dan
                            pesan yang tinggal dikirim — <strong>terutama berguna untuk yang tidak
                            mencantumkan email</strong>, karena mereka tidak pernah terjangkau
                            surat otomatis.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-9">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari nama, nomor WhatsApp, email, atau kode pesanan...'])
                    </div>

                    <div class="col-12 col-lg-3 text-lg-end">
                        <span class="text-muted small">{{ $meta['total'] ?? 0 }} pelanggan</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">PELANGGAN</th>
                                <th>PESANAN</th>
                                <th>TERAKHIR</th>
                                <th>KODE RUJUKAN</th>
                                <th class="text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="pelanggan-{{ $baris['whatsapp_angka'] }}">
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted small">{{ $baris['whatsapp'] }}</div>

                                        {{-- Ketiadaan email ditandai, bukan sekadar dikosongkan.

                                             Orang inilah yang tidak pernah terjangkau surat
                                             otomatis — bukan pengingat, bukan ajakan testimoni,
                                             bukan kode rujukan. Satu-satunya jalan ke mereka
                                             tombol di sebelah kanan baris ini. --}}
                                        @if (blank($baris['email']))
                                            <span class="badge bg-warning-subtle text-warning-emphasis mt-1">
                                                Tanpa email — hanya lewat WhatsApp
                                            </span>
                                        @else
                                            <div class="text-muted" style="font-size:.72rem">{{ $baris['email'] }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($baris['jumlah_trip'] > 0)
                                            <span class="orcha-cip-peserta">
                                                <i class="bi bi-map"></i> {{ $baris['jumlah_trip'] }} trip
                                            </span>
                                        @endif

                                        @if ($baris['jumlah_sewa'] > 0)
                                            <span class="orcha-cip-peserta">
                                                <i class="bi bi-bus-front"></i> {{ $baris['jumlah_sewa'] }} sewa
                                            </span>
                                        @endif

                                        @if ($baris['jumlah_batal'] > 0)
                                            {{-- Menawarkan trip baru kepada orang yang pesanannya
                                                 batal menuntut kalimat pembuka yang berbeda, dan
                                                 itu hanya bisa dipilih kalau keadaannya terlihat. --}}
                                            <div class="text-muted" style="font-size:.72rem">
                                                {{ $baris['jumlah_batal'] }} batal
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-muted small">
                                        @if ($baris['terakhir_pada'])
                                            {{ \Carbon\Carbon::parse($baris['terakhir_pada'])->locale('id')->diffForHumans() }}
                                            <div style="font-size:.72rem">{{ $baris['terakhir_kode'] }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>
                                        @if ($baris['kode_rujukan'])
                                            <span class="orcha-kode">{{ $baris['kode_rujukan'] }}</span>
                                            <div class="text-muted" style="font-size:.72rem">
                                                dipakai {{ $baris['rujukan_dipakai'] }}&times;
                                                @if ($baris['komisi_belum_dibayar'] > 0)
                                                    · <span class="text-danger-emphasis fw-semibold">
                                                        utang Rp {{ number_format($baris['komisi_belum_dibayar'], 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">belum punya</span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-wa"
                                                wire:click="bukaPesan({{ json_encode($baris) }})"
                                                title="Siapkan pesan WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-3">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Belum ada pelanggan. Daftar ini terisi sendiri begitu ada
                                            pendaftaran atau penyewaan yang masuk.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('livewire.pages.admin.orcha.partials.paginasi')
            </div>
        </div>
    </div>

    @if ($buka !== '')
        @php
            $nama = $terpilih['nama'] ?? '';
            $panggil = trim(explode(' ', trim($nama))[0] ?? '');
            $kode = $kodeBaru ?: ($terpilih['kode_rujukan'] ?? '');
            $potongan = 'Rp ' . number_format($meta['rujukan_potongan'] ?? 0, 0, ',', '.');
            $imbalan = 'Rp ' . number_format($meta['rujukan_imbalan'] ?? 0, 0, ',', '.');

            /* Pesannya disusun di sini, bukan diketik admin tiap kali.

               Yang diketik ulang berbeda-beda tiap admin dan tiap hari, dan
               angka yang diingat dari percakapan bulan lalu adalah angka yang
               salah. Yang paling mudah salah justru kodenya sendiri — dan kode
               yang salah satu huruf adalah komisi yang tidak pernah sampai ke
               pemiliknya. */
            $pesan = $kode
                ? "Halo Kak {$panggil}, terima kasih sudah jalan bareng Orcha Journey.\n\n"
                    . "Ini kode rujukan Kakak: {$kode}\n\n"
                    . "Kalau ada teman yang mau ikut trip kami, minta mereka masukkan kode ini "
                    . "saat mendaftar. Mereka dapat potongan {$potongan}, dan Kakak dapat {$imbalan} "
                    . "untuk tiap pendaftaran yang memakainya.\n\n"
                    . 'Kodenya berlaku terus, jadi boleh dibagikan ke siapa saja.'
                : "Halo Kak {$panggil}, terima kasih sudah jalan bareng Orcha Journey.";

            $tautanWa = 'https://api.whatsapp.com/send?phone='
                . preg_replace('/^0/', '62', $terpilih['whatsapp_angka'] ?? '')
                . '&text=' . rawurlencode($pesan);
        @endphp

        <div class="orcha-tirai" wire:click.self="tutupPesan">
            <div class="orcha-jendela">
                <div class="p-3 p-lg-4 border-bottom d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-bold fs-5">{{ $nama }}</div>
                        <div class="text-muted small">{{ $terpilih['whatsapp'] ?? '' }}</div>
                    </div>

                    <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-mati"
                        wire:click="tutupPesan" title="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="p-3 p-lg-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="text-muted small">Pesanan</div>
                            <div class="fw-semibold">
                                {{ ($terpilih['jumlah_trip'] ?? 0) }} trip ·
                                {{ ($terpilih['jumlah_sewa'] ?? 0) }} sewa
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="text-muted small">Terakhir memesan</div>
                            <div class="fw-semibold">
                                {{ ($terpilih['terakhir_pada'] ?? null)
                                    ? \Carbon\Carbon::parse($terpilih['terakhir_pada'])->locale('id')->translatedFormat('j F Y')
                                    : '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="orcha-bagian-kepala mt-4 pt-3 border-top">
                        <div class="orcha-bagian-nomor"><i class="bi bi-share"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Kode rujukan</div>
                            <div class="orcha-bagian-sub">
                                Dipakai teman yang ia ajak saat mendaftar. Potongan {{ $potongan }}
                                untuk temannya, imbalan {{ $imbalan }} untuk dia.
                            </div>
                        </div>
                    </div>

                    @if ($kode)
                        <div class="p-3 rounded-4 mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
                            <div class="orcha-kode-besar">{{ $kode }}</div>
                            @if (($terpilih['komisi_belum_dibayar'] ?? 0) > 0)
                                <div class="text-danger-emphasis small fw-semibold mt-1">
                                    Komisi belum dibayar: Rp
                                    {{ number_format($terpilih['komisi_belum_dibayar'], 0, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Tombol tersendiri, bukan efek samping membuka jendela.

                             Kalau kodenya dibuat sendiri saat jendelanya terbuka,
                             setiap orang yang pernah dilihat admin mendapat kode —
                             termasuk yang seluruh pesanannya batal. --}}
                        <div class="p-3 rounded-4 mb-3 text-center" style="background:#f8fafc;border:1px dashed #cbd5e1">
                            <p class="text-muted small mb-3">
                                Belum punya kode rujukan.
                            </p>

                            <button type="button" wire:click="buatkanKode" wire:loading.attr="disabled"
                                wire:target="buatkanKode" class="orcha-btn orcha-btn-utama">
                                <span wire:loading.remove wire:target="buatkanKode">
                                    <i class="bi bi-plus-lg"></i>
                                    Buatkan kode rujukan
                                </span>
                                <span wire:loading wire:target="buatkanKode">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>Membuat…
                                </span>
                            </button>
                        </div>
                    @endif

                    <div class="text-muted small fw-semibold mb-2">Pesan yang akan dikirim</div>
                    <div class="orcha-pesan-siap mb-3">{{ $pesan }}</div>
                </div>

                <div class="p-3 p-lg-4 border-top d-flex gap-2 justify-content-end">
                    <button type="button" wire:click="tutupPesan" class="orcha-btn orcha-btn-lembut">
                        Tutup
                    </button>

                    {{-- Membuka WhatsApp dengan pesannya sudah terisi. Admin masih
                         bisa menyuntingnya sebelum menekan kirim — kalimat yang tidak
                         bisa diubah sama sekali akhirnya dihapus dan diketik ulang. --}}
                    <a href="{{ $tautanWa }}" target="_blank" rel="noopener"
                        class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-whatsapp"></i>
                        Buka WhatsApp
                    </a>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
