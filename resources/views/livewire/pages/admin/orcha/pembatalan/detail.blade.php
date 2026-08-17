@section('title')
Detail Pembatalan || lemon
@stop

@php
    $rp = fn ($angka) => 'Rp ' . number_format((int) $angka, 0, ',', '.');
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanBukti = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;

    $p = $pembatalan['perkiraan'] ?? null;
    $pesanan = $pembatalan['pesanan'] ?? null;
    $rekening = $pembatalan['rekening'] ?? [];
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @if (empty($pembatalan))
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-x-circle"></i></div>
                    <p class="text-muted mb-3">Data pengajuan pembatalan tidak bisa ditampilkan.</p>
                    <a href="{{ route('admin.orcha.pembatalan') }}" class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-arrow-left"></i> Kembali ke daftar
                    </a>
                </div>
            </div>
        @else

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <a href="{{ route('admin.orcha.pembatalan') }}" class="orcha-tautan-balik mb-2">
                                <i class="bi bi-arrow-left"></i> Semua pengajuan
                            </a>
                            <h1 class="gradient-text fw-bold mb-1" style="font-size:1.6rem">
                                {{ $pembatalan['nama_pemohon'] }}
                            </h1>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="orcha-kode">{{ $pembatalan['kode_pendaftaran'] }}</span>
                                <span class="text-muted" style="font-size:.82rem">
                                    {{ $pembatalan['jenis_label'] }} ·
                                    diajukan
                                    {{ \Carbon\Carbon::parse($pembatalan['dibuat_pada'])->translatedFormat('j F Y, H:i') }}
                                </span>
                                <span class="badge orcha-lencana-bayar-{{ $pembatalan['status'] === 'ditolak' ? 'ditolak' : ($pembatalan['status'] === 'diajukan' ? 'menunggu' : 'diterima') }}">
                                    {{ $pembatalan['status_label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            {{-- Kalimat perhitungan pengembalian yang selama ini diketik
                                 ulang tiap kali. Angkanya diambil dari perkiraan yang sama
                                 dengan yang tampil di layar. --}}
                            @if ($this->tautanWa())
                                <button type="button" class="orcha-btn orcha-btn-lembut"
                                    data-wa-pesan="{{ $this->pesanWa() }}"
                                    title="Salin teks perhitungannya untuk ditempel di WhatsApp">
                                    <i class="bi bi-clipboard"></i> Salin Perhitungan
                                </button>

                                <a href="{{ $this->tautanWa() }}" target="_blank" rel="noopener"
                                    class="orcha-btn orcha-btn-wa" data-wa-pesan="{{ $this->pesanWa() }}">
                                    <i class="bi bi-whatsapp"></i> Kirim Perhitungan
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Tiga angka yang menentukan keputusan, sebelum keterangan apa pun.
                         Admin membuka halaman ini untuk mengetahui berapa yang harus
                         dikirim balik. --}}
                    @if ($p)
                        <div class="row g-3 mt-1">
                            @foreach ([
                                ['Sudah dibayar', $p['dibayar_teks'], '', 'bi-cash-stack'],
                                [$p['ditetapkan'] ? 'Potongan ditetapkan' : 'Potongan ' . $p['persen'] . '%',
                                    '− ' . $p['potongan_teks'], 'sisa', 'bi-scissors'],
                                ['Dikembalikan', $p['kembali_teks'], $p['kembali'] > 0 ? 'lunas' : 'sisa', 'bi-arrow-return-left'],
                                ['Total biaya', $p['total_teks'], '', 'bi-receipt'],
                            ] as [$label, $nilai, $kelas, $ikon])
                                <div class="col-6 col-lg-3">
                                    <div class="orcha-ringkas {{ $kelas }}">
                                        <div class="orcha-label-kecil orcha-ikon-teks">
                                            <i class="bi {{ $ikon }}"></i> {{ $label }}
                                        </div>
                                        <div class="angka">{{ $nilai }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="orcha-alasan mt-3">
                            <span class="orcha-label-kecil orcha-ikon-teks">
                                <i class="bi bi-calculator"></i> Dasar perhitungan
                            </span>
                            <div class="mt-1" style="font-size:.86rem">
                                <strong>{{ $p['batas'] }}</strong> — potongan {{ $p['persen'] }}% dari total biaya
                                {{ $p['total_teks'] }}, dibatasi sebesar pembayaran yang sudah masuk.
                                @if ($p['lewat'])
                                    Waktu mulainya sudah lewat, jadi dihitung sebagai tidak datang tanpa kabar.
                                @endif
                            </div>
                            <div class="mt-1 text-muted" style="font-size:.8rem">
                                Angka ini perkiraan menurut kebijakan. Periksa dulu biaya yang sudah
                                terlanjur dikeluarkan sebelum menetapkannya.
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning border-0 rounded-3 mt-3 mb-0 d-flex gap-2 align-items-start"
                            style="font-size:.86rem">
                            <i class="bi bi-exclamation-circle" style="line-height:1.5"></i>
                            <span>
                                Perkiraan belum bisa dihitung — pesanannya tidak ditemukan atau belum punya
                                tanggal mulai. Cocokkan kodenya dengan pemohon lebih dulu.
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-7">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-person-lines-fill text-primary"></i> Pemohon
                            </h2>

                            <div class="row g-3">
                                @foreach ([
                                    ['Nama pemohon', $pembatalan['nama_pemohon']],
                                    ['WhatsApp', $pembatalan['whatsapp']],
                                    ['Email', $pembatalan['email'] ?: '—'],
                                    ['Alasan', $pembatalan['alasan_label']],
                                ] as [$label, $nilai])
                                    <div class="col-6">
                                        <div class="orcha-label-kecil">{{ $label }}</div>
                                        <div class="fw-bold" style="font-size:.9rem">{{ $nilai }}</div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($pembatalan['penjelasan'])
                                <div class="mt-3">
                                    <div class="orcha-label-kecil mb-1">Penjelasan pemohon</div>
                                    <div class="orcha-alasan" style="font-size:.86rem">{{ $pembatalan['penjelasan'] }}</div>
                                </div>
                            @endif

                            {{-- Nama pemohon dan nama pemesan tidak selalu sama, dan
                                 perbedaannya perlu diperiksa sebelum dana dikirim. --}}
                            @if ($pesanan && $pesanan['nama'] !== $pembatalan['nama_pemohon'])
                                <div class="orcha-alasan orcha-alasan-tinggi mt-3">
                                    <span class="orcha-label-kecil orcha-ikon-teks" style="color:#b91c1c">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Nama pemohon berbeda dari pemesan
                                    </span>
                                    <div class="mt-1" style="font-size:.84rem">
                                        Pemesannya <strong>{{ $pesanan['nama'] }}</strong>, sedangkan yang mengajukan
                                        <strong>{{ $pembatalan['nama_pemohon'] }}</strong>. Pastikan pengajuan ini
                                        memang dari pihak yang berhak sebelum dana dikirim.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Riwayat pembayaran = dasar angka yang dikirim balik. Bukti yang
                         masih menunggu ditandai, karena memutuskannya akan mengubah
                         perhitungan di atas. --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-cash-coin text-primary"></i> Bukti Pembayaran
                            </h2>

                            @forelse ($pembatalan['pembayaran'] ?? [] as $bayar)
                                <div class="orcha-alasan {{ $bayar['status'] === 'menunggu' ? 'orcha-alasan-tinggi' : '' }} mb-2">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <div class="fw-bold">{{ $bayar['nominal_formatted'] }}
                                                <span class="text-muted fw-normal" style="font-size:.8rem">
                                                    · {{ $bayar['jenis_label'] }}
                                                </span>
                                            </div>
                                            <div class="text-muted" style="font-size:.78rem">
                                                {{ $bayar['bank_pengirim'] }} a.n. {{ $bayar['atas_nama_pengirim'] }} ·
                                                {{ $bayar['tanggal_transfer']
                                                    ? \Carbon\Carbon::parse($bayar['tanggal_transfer'])->translatedFormat('j M Y')
                                                    : '—' }}
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge orcha-lencana-bayar-{{ $bayar['status'] }}">
                                                {{ $bayar['status_label'] }}
                                            </span>
                                            @if ($bayar['bukti'])
                                                <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-lihat"
                                                    title="Lihat bukti transfer"
                                                    data-bukti="{{ $tautanBukti($bayar['bukti']) }}"
                                                    data-bukti-keterangan="{{ $pembatalan['kode_pendaftaran'] }} · {{ $bayar['nominal_formatted'] }} · {{ $bayar['bank_pengirim'] }}">
                                                    <i class="bi bi-receipt"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($bayar['catatan_admin'])
                                        <div class="mt-2" style="font-size:.8rem">{{ $bayar['catatan_admin'] }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0" style="font-size:.86rem">
                                    Belum ada bukti pembayaran untuk kode ini. Tanpa uang yang masuk,
                                    tidak ada yang perlu dikembalikan.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">

                    @if ($pesanan)
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-3 p-lg-4">
                                <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                    <i class="bi {{ $pesanan['jenis'] === 'sewa_kendaraan' ? 'bi-truck' : 'bi-signpost-split' }} text-primary"></i>
                                    Pesanan yang Dibatalkan
                                </h2>

                                <div class="row g-3">
                                    @foreach (array_filter([
                                        ['Pemesan', $pesanan['nama']],
                                        [$pesanan['jenis'] === 'sewa_kendaraan' ? 'Kendaraan' : 'Paket', $pesanan['keterangan'] ?: '—'],
                                        [$pesanan['jenis'] === 'sewa_kendaraan' ? 'Mulai sewa' : 'Tanggal berangkat',
                                            $pesanan['mulai']
                                                ? \Carbon\Carbon::parse($pesanan['mulai'])->translatedFormat($pesanan['jenis'] === 'sewa_kendaraan' ? 'j M Y, H:i' : 'j M Y')
                                                : 'Menyusul'],
                                        $pesanan['jenis'] === 'sewa_kendaraan'
                                            ? ['Lama sewa', $pesanan['durasi_label'] ?: '—']
                                            : ['Jumlah peserta', $pesanan['jumlah_peserta'] . ' orang'],
                                    ]) as [$label, $nilai])
                                        <div class="col-6">
                                            <div class="orcha-label-kecil">{{ $label }}</div>
                                            <div class="fw-bold" style="font-size:.9rem">{{ $nilai }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3 d-flex align-items-center gap-2">
                                    <span class="orcha-label-kecil mb-0">Status pesanan</span>
                                    <span class="badge orcha-lencana-bayar-{{ $pesanan['status'] === 'batal' ? 'ditolak' : 'diterima' }}">
                                        {{ $pesanan['status_label'] }}
                                    </span>
                                </div>

                                {{-- Status pesanan berubah sendiri hanya ketika pembatalannya
                                     disetujui. Selama masih diajukan, pesanannya sengaja
                                     dibiarkan berjalan — tim masih boleh menolak. --}}
                                @if ($pesanan['status'] !== 'batal' && in_array($pembatalan['status'], ['disetujui', 'dana_dikirim']))
                                    <div class="alert alert-warning border-0 rounded-3 mt-3 mb-0" style="font-size:.82rem">
                                        Pembatalan sudah disetujui tetapi pesanannya belum tercatat batal.
                                        Simpan ulang statusnya untuk menyelaraskan.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-bank text-primary"></i> Rekening Pengembalian
                            </h2>

                            <div class="row g-3">
                                @foreach ([
                                    ['Bank', $rekening['bank'] ?? '—'],
                                    ['Nomor rekening', $rekening['nomor'] ?? '—'],
                                    ['Atas nama', $rekening['atas_nama'] ?? '—'],
                                ] as [$label, $nilai])
                                    <div class="col-6 col-md-4">
                                        <div class="orcha-label-kecil">{{ $label }}</div>
                                        <div class="fw-bold" style="font-size:.9rem">{{ $nilai }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3 text-muted d-flex gap-2 align-items-start" style="font-size:.8rem">
                                <i class="bi bi-shield-check" style="line-height:1.5"></i>
                                Dana hanya dikirim ke rekening atas nama pemesan yang melakukan pembayaran.
                            </div>
                        </div>
                    </div>

                </div>
            </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-3 p-lg-4">
                        <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                            <i class="bi bi-check2-square text-primary"></i> Tindak Lanjut
                        </h2>

                        {{-- Dua kolom: kiri menghitung, kanan memutuskan.
                             Sebelumnya seluruh panel ini menumpuk di kolom kanan yang
                             sempit — pilihan statusnya patah jadi dua baris, keterangannya
                             berdesakan, dan kolom kiri berakhir jauh lebih pendek sehingga
                             separuh halaman kosong. --}}
                        <div class="row g-4">
                            <div class="col-12 col-lg-5">

                        {{-- Hitung manual.

                             Usulan kebijakan sudah terisi supaya admin melanjutkan,
                             bukan menaksir dari nol. Tapi sistem tidak tahu segalanya:
                             tiket masuk yang sudah dibayarkan ke pihak ketiga tidak bisa
                             ditarik, dan ada kelonggaran yang memang layak diberikan
                             ketika alasannya musibah. Yang memutuskan tetap manusia. --}}
                        @if ($p)
                            <div class="orcha-label-kecil mb-1">Potongan yang ditetapkan</div>
                            <div class="orcha-rupiah">
                                {{-- data-dibayar dan data-tersimpan dipakai skrip di bawah untuk
                                     menghitung ulang angka pengembalian sambil admin mengetik.
                                     wire:model tetap .blur: mengirim tiap ketikan ke server akan
                                     menulis ulang isian di tengah pengetikan dan memindahkan
                                     kursor. --}}
                                <input type="text" inputmode="numeric" class="form-control orcha-uang"
                                    wire:model.blur="potongan"
                                    data-potongan
                                    data-dibayar="{{ (int) $p['dibayar'] }}"
                                    data-tersimpan="{{ (int) ($pembatalan['potongan_ditetapkan'] ?? $p['usulan']) }}">
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
                                <span class="text-muted" style="font-size:.78rem">
                                    Usulan kebijakan: <strong>{{ $p['usulan_teks'] }}</strong>
                                    ({{ $p['persen'] }}% dari total)
                                </span>
                                <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                    wire:click="$set('potongan', '{{ number_format($p['usulan'], 0, ',', '.') }}')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Pakai usulan
                                </button>
                            </div>

                            {{-- height:auto melawan .orcha-ringkas yang memakai height:100%.
                                 Kelas itu memang untuk kartu berjajar yang tingginya harus
                                 sama; di sini ia justru memuaikan kotaknya sampai ke dasar
                                 kolom dan meninggalkan bidang kosong. --}}
                            <div class="orcha-ringkas {{ $this->kembaliSekarang() > 0 ? 'lunas' : 'sisa' }} mt-2"
                                style="height:auto" data-kotak-kembali>
                                <div class="orcha-label-kecil orcha-ikon-teks">
                                    <i class="bi bi-arrow-return-left"></i> Dikembalikan ke pelanggan
                                    {{-- Selalu ada di HTML, disembunyikan bila tidak perlu:
                                         skrip di halaman menyalakannya sambil admin mengetik,
                                         tanpa menunggu jawaban server. --}}
                                    <span class="orcha-lencana-catat ms-1" data-lencana-belum {{ $this->belumTersimpan() ? '' : 'hidden' }}>belum tersimpan</span>
                                </div>
                                <div class="angka" data-angka-kembali>
                                    Rp {{ number_format($this->kembaliSekarang(), 0, ',', '.') }}
                                </div>
                            </div>

                            @if ($this->belumTersimpan())
                                <div class="alert alert-warning border-0 rounded-3 mt-2 mb-0" style="font-size:.8rem">
                                    <i class="bi bi-exclamation-circle"></i>
                                    Angka ini belum tersimpan. Yang tercatat di Orcha masih
                                    <strong>Rp {{ number_format((int) ($pembatalan['potongan_ditetapkan'] ?? $p['usulan']), 0, ',', '.') }}</strong>,
                                    dan itulah yang dipakai pesan ke pelanggan.
                                </div>
                            @endif

                        @endif
                            </div>

                            <div class="col-12 col-lg-7">
                        <div class="orcha-label-kecil mb-2">Status pengajuan</div>

                        {{-- Berdampingan, bukan daftar turun: menyetujui dan menolak
                             adalah dua tindakan yang berbeda akibatnya, dan yang
                             disetujui ikut membatalkan pesanannya. --}}
                        <div class="orcha-pilihan-status">
                            @foreach ($pilihanStatus as $kunci => $label)
                                <label class="orcha-status-pil orcha-status-{{ $kunci }}">
                                    <input type="radio" wire:model="statusBaru" value="{{ $kunci }}">
                                    <span>
                                        <i class="bi {{ [
                                            'diajukan' => 'bi-hourglass-split',
                                            'diproses' => 'bi-arrow-repeat',
                                            'disetujui' => 'bi-check-circle-fill',
                                            'dana_dikirim' => 'bi-send-check-fill',
                                            'ditolak' => 'bi-x-circle-fill',
                                        ][$kunci] ?? 'bi-circle' }}"></i>
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <label class="form-label small fw-semibold mb-1">Catatan admin</label>
                            <textarea class="form-control" rows="3" wire:model="catatanAdmin"
                                placeholder="Mis. potongan 25% sesuai kebijakan."></textarea>
                            <div class="form-text">
                                Catatan ini ikut terbaca admin lain, dan menjadi penjelasan bila
                                perhitungannya dipersoalkan kemudian.
                            </div>
                        </div>

                        {{-- Ikonnya jadi item flex tersendiri, teksnya dibungkus satu span.
                             Sempat memakai .orcha-ikon-teks di sini, dan itu keliru:
                             inline-flex membuat SETIAP anak jadi item flex, sehingga tiap
                             <strong> terlempar ke kolomnya sendiri dan kalimatnya berantakan. --}}
                        <div class="alert alert-info border-0 rounded-3 mt-3 d-flex gap-2 align-items-start"
                            style="font-size:.8rem">
                            <i class="bi bi-info-circle" style="line-height:1.5"></i>
                            <span>
                                Status <strong>Disetujui</strong> atau <strong>Dana dikirim</strong> ikut
                                menandai pesanannya batal. Bukti bayar yang masih menunggu tidak diputuskan
                                sendiri — jumlah yang benar-benar masuk menentukan besar pengembalian.
                            </span>
                        </div>

                            </div>
                        </div>

                        <button type="button" class="orcha-btn orcha-btn-utama w-100 mt-3"
                            wire:click="simpan" wire:loading.attr="disabled">
                            <i class="bi bi-save"></i> Simpan Tindak Lanjut
                        </button>
                    </div>
                </div>
        @endif
    </div>

    {{-- Angka pengembalian mengikuti ketikan, bukan menunggu isian ditinggalkan.

         Hitungannya sederhana — dibayar dikurangi potongan — jadi dikerjakan di
         peramban saja. Mengirim tiap ketikan ke server berarti Livewire menulis
         ulang isiannya di tengah pengetikan; kursor melompat ke ujung dan angka
         yang sedang diketik berantakan.

         Server tetap yang berwenang: saat isian ditinggalkan, wire:model.blur
         mengirimkan nilainya dan seluruh kotak ini digambar ulang dari sana.
         Yang di sini hanya supaya admin melihat akibat ketikannya seketika.

         Ditulis inline sesuai aturan repo: public/build tidak ikut ter-deploy. --}}
    <script>
        if (! window.orchaHitungKembaliSiap) {
            window.orchaHitungKembaliSiap = true;

            const angka = (teks) => parseInt(String(teks || '').replace(/\D/g, ''), 10) || 0;

            const titik = (n) => n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            document.addEventListener('input', function (e) {
                const isian = e.target.closest('[data-potongan]');
                if (! isian) return;

                const dibayar = parseInt(isian.dataset.dibayar, 10) || 0;
                const tersimpan = parseInt(isian.dataset.tersimpan, 10) || 0;
                const potongan = Math.min(angka(isian.value), dibayar);
                const kembali = Math.max(0, dibayar - potongan);

                const kotak = document.querySelector('[data-kotak-kembali]');
                const teks = document.querySelector('[data-angka-kembali]');
                const lencana = document.querySelector('[data-lencana-belum]');

                if (teks) teks.textContent = 'Rp ' + titik(kembali);

                // Warnanya ikut berubah: hijau bila masih ada yang dikembalikan,
                // merah bila habis — sama seperti yang digambar server.
                if (kotak) {
                    kotak.classList.toggle('lunas', kembali > 0);
                    kotak.classList.toggle('sisa', kembali <= 0);
                }

                if (lencana) lencana.hidden = angka(isian.value) === tersimpan;
            });
        }
    </script>

    @include('livewire.pages.admin.orcha.partials.pratinjau-bukti')
    @include('livewire.pages.admin.orcha.partials.salin-wa')
    @include('livewire.pages.admin.orcha.partials.skrip')

    <style>
        .orcha-pilihan-status {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .orcha-status-pil { margin: 0; }

        .orcha-status-pil input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .orcha-status-pil span {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem .9rem;
            border-radius: .7rem;
            border: 1.5px solid #dbe7f0;
            background: #fff;
            color: #5b7186;
            font-size: .84rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s ease;
        }

        .orcha-status-pil span > i { line-height: 1; }

        .orcha-status-pil span:hover { border-color: #b9d0e2; }

        /* Warna baru muncul saat dipilih; sebelum admin memutuskan, tidak ada
           pilihan yang pantas terlihat seperti sudah dipilih. */
        .orcha-status-diajukan input:checked + span,
        .orcha-status-diproses input:checked + span {
            border-color: #d99a19;
            background: #fdf6e7;
            color: #8a6110;
        }

        .orcha-status-disetujui input:checked + span,
        .orcha-status-dana_dikirim input:checked + span {
            border-color: #1a8a52;
            background: #e9f7f0;
            color: #126b40;
        }

        .orcha-status-ditolak input:checked + span {
            border-color: #c2323c;
            background: #fdecee;
            color: #9b2530;
        }

        @media (max-width: 575.98px) {
            .orcha-pilihan-status { flex-direction: column; }

            .orcha-status-pil span { justify-content: center; }
        }
    </style>
</div>
