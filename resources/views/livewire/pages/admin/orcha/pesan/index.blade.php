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
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-5">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari nama, WhatsApp, email, atau isi pesan...'])
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua keperluan</option>
                            @foreach ($pilihanKeperluan as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dua pilihan berdampingan, bukan kotak centang.

                         Kotak centang menyembunyikan dua hal yang justru ingin
                         diketahui admin sebelum menekannya: bahwa yang sedang
                         tampil adalah SEMUA pesan, dan bahwa masih ada sekian
                         yang belum dibaca. Angkanya dibawa dari Orcha dan tidak
                         ikut berubah oleh saringan — yang dihitung keadaan kotak
                         masuk, bukan isi halaman ini. --}}
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="orcha-saring-baca" role="group" aria-label="Saring pesan">
                            <button type="button" class="{{ $hanyaBelumDibaca ? '' : 'aktif' }}"
                                wire:click="$set('hanyaBelumDibaca', false)">
                                <i class="bi bi-inbox"></i> Semua
                            </button>
                            <button type="button" class="{{ $hanyaBelumDibaca ? 'aktif' : '' }}"
                                wire:click="$set('hanyaBelumDibaca', true)">
                                <i class="bi bi-envelope-exclamation"></i> Belum dibaca
                                @if (($meta['belum_dibaca'] ?? 0) > 0)
                                    <span class="jumlah">{{ $meta['belum_dibaca'] }}</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            {{-- Padding kartu dan pembungkus gulungnya menyalin daftar pembatalan,
                 supaya tabelnya tidak menempel di tepi kartu seperti sebelumnya. --}}
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel orcha-tabel-pesan mb-0">
                        <thead>
                            <tr>
                                <th>Keadaan</th>
                                <th>Pengirim</th>
                                <th>Keperluan</th>
                                <th>Isi Pesan</th>
                                <th>Masuk</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                /* Ikonnya menyalin daftar pembatalan, yang sudah memakai
                                   lambang yang sama untuk keempat jenis pesanan. Ikon +
                                   warna: dua penanda, supaya jenis keperluan tetap terbaca
                                   oleh yang sulit membedakan warna. */
                                $ikonKeperluan = [
                                    'open_trip' => 'bi-signpost-split',
                                    'private_trip' => 'bi-people',
                                    'study_tour' => 'bi-mortarboard',
                                    'sewa_kendaraan' => 'bi-truck',
                                    'kerja_sama' => 'bi-briefcase',
                                    'lainnya' => 'bi-chat-dots',
                                ];
                            @endphp

                            @forelse ($daftar as $baris)
                                {{-- Yang belum dibaca dibedakan dengan tiga penanda sekaligus:
                                     garis tebal di tepi kiri baris, latar kebiruan tipis, dan
                                     nama pengirim yang ditebalkan. Satu penanda saja tidak
                                     cukup — warna latar yang setipis ini hilang di layar yang
                                     terlalu terang, dan yang sulit membedakan warna hanya
                                     melihat deretan baris yang sama semua. --}}
                                <tr wire:key="pesan-{{ $baris['id'] }}"
                                    class="orcha-baris-pesan {{ $baris['sudah_dibaca'] ? 'sudah' : 'belum' }}">
                                    <td>
                                        @if ($baris['sudah_dibaca'])
                                            <span class="orcha-status-pesan" data-baca="sudah">
                                                <i class="bi bi-check2-all"></i> Dibaca
                                            </span>
                                        @else
                                            <span class="orcha-status-pesan" data-baca="belum">
                                                <i class="bi bi-envelope-fill"></i> Belum dibaca
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="orcha-nama-pengirim">{{ $baris['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $baris['whatsapp'] }}
                                            {{ $baris['email'] ? '· ' . $baris['email'] : '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="orcha-lencana-keperluan"
                                            data-keperluan="{{ $baris['keperluan'] ?? 'lainnya' }}">
                                            <i class="bi {{ $ikonKeperluan[$baris['keperluan'] ?? 'lainnya'] ?? 'bi-chat-dots' }}"></i>
                                            {{ $baris['keperluan_label'] }}
                                        </span>
                                    </td>
                                    {{-- Dipenggal dua baris. Isi pesan panjangnya berbeda-beda
                                         jauh; kalau dibiarkan utuh, satu pesan bisa setinggi
                                         lima baris lain dan tabelnya berhenti terbaca sebagai
                                         daftar. Yang panjang dibuka utuh di halaman detail. --}}
                                    <td>
                                        <p class="mb-0 orcha-pesan-cuplik">{{ $baris['pesan'] }}</p>
                                    </td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($baris['dibuat_pada'])->locale('id')->translatedFormat('d M Y') }}</div>
                                        <div class="text-muted" style="font-size:.75rem">
                                            {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->format('H:i') }} WIB
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('admin.orcha.pesan.detail', $baris['id']) }}" wire:navigate
                                                class="btn btn-sm orcha-aksi orcha-aksi-lihat" title="Buka pesan selengkapnya">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- Balasannya sudah menyebut keperluannya dan menanyakan
                                                 hal-hal yang toh selalu ditanyakan admin di putaran
                                                 berikutnya — sama persis dengan tombol WA di halaman
                                                 detail, karena keduanya memakai penyusun yang sama. --}}
                                            <a href="{{ \App\Support\BalasanPesanKontak::tautan($baris) }}"
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada pesan yang cocok.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Penomoran halaman ikut di dalam kartu, seperti daftar
                     pembatalan dan bukti pembayaran. Di luar kartu ia
                     mengambang sendirian di latar halaman dan tidak terbaca
                     sebagai bagian dari tabel yang baru saja dibaca. --}}
                @include('livewire.pages.admin.orcha.partials.paginasi')
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
    <style>
        /* Judul kolom ditengahkan, mengikuti daftar sewa, bukti bayar, dan
           pembatalan. Isinya tetap rata kiri supaya tepi kiri tiap kolom lurus
           dan mata punya garis untuk menyusuri baris. */
        .orcha-tabel-pesan thead th,
        .orcha-tabel-pesan thead th.text-end { text-align: center !important; }

        .orcha-tabel-pesan td {
            padding-top: .6rem !important;
            padding-bottom: .6rem !important;
        }

        /* ===== Pembeda sudah dibaca / belum =====
           Tiga penanda untuk satu hal yang sama. Kotak masuk dibuka untuk
           mencari yang belum dikerjakan, bukan untuk membaca ulang yang sudah
           selesai — jadi yang belum dibaca harus terlihat tanpa dicari. */
        .orcha-baris-pesan > td:first-child {
            border-left: 4px solid transparent;
        }

        .orcha-baris-pesan.belum > td {
            background: #f5faff;
        }

        .orcha-baris-pesan.belum > td:first-child {
            border-left-color: #1d6fa5;
        }

        .orcha-nama-pengirim {
            color: #0f2d4a;
            font-weight: 600;
        }

        .orcha-baris-pesan.belum .orcha-nama-pengirim {
            font-weight: 800;
        }

        /* Nama pengirim yang sudah dibaca diredupkan sedikit — tetap terbaca,
           tapi tidak ikut menarik mata saat yang dicari yang baru. */
        .orcha-baris-pesan.sudah .orcha-nama-pengirim {
            color: #46617c;
        }

        /* Isi pesan yang belum dibaca dibuat sepekat teks biasa; yang sudah
           dibaca dibiarkan redup. */
        .orcha-baris-pesan.belum .orcha-pesan-cuplik { color: #0f2d4a; }

        /* Dua baris, sisanya di halaman detail. */
        .orcha-pesan-cuplik {
            white-space: pre-line;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-width: 18rem;
            max-width: 32rem;
            font-size: .84rem;
            color: #46617c;
        }

        /* Penyaring baca/belum: dua tombol berdempet dalam satu bingkai,
           supaya terbaca sebagai satu pilihan — bukan dua tombol terpisah
           yang bisa ditekan bersamaan. */
        .orcha-saring-baca {
            display: inline-flex;
            width: 100%;

            /* Setinggi dan sebulat kotak cari serta pemilih keperluan di
               sebelahnya — ketiganya satu deret kendali, dan sebelumnya yang
               ini 46px berbanding 38px dengan sudut jauh lebih bulat, jadi
               terbaca seperti benda lain yang kebetulan sebaris.
               38 = 2px garis tepi + 2×3px jarak dalam + 30px tinggi tombol. */
            height: 38px;
            padding: 3px;
            gap: 3px;
            border: 1px solid #dbe7f0;
            background: #f4f8fb;
            border-radius: 4px;
        }

        .orcha-saring-baca > button {
            flex: 1 1 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            padding: 0 .5rem;
            border: 0;
            border-radius: 2px;
            background: transparent;
            color: #5b7186;

            /* Sehuruf dengan pemilih keperluan (1rem), bukan .84rem — beda
               ukuran huruf membuat kotaknya tetap terlihat lebih kecil
               walaupun tingginya sudah sama. */
            font-size: 1rem;
            font-weight: 600;
            white-space: nowrap;
            transition: background .15s ease, color .15s ease, box-shadow .15s ease;
        }

        .orcha-saring-baca > button > i { line-height: 1; }

        .orcha-saring-baca > button:hover { color: #1d6fa5; }

        /* Yang aktif diangkat dengan latar putih dan bayangan tipis; warnanya
           saja tidak cukup untuk yang sulit membedakan warna. */
        .orcha-saring-baca > button.aktif {
            background: #fff;
            color: #0f2d4a;
            box-shadow: 0 1px 3px rgba(15, 45, 74, .12);
        }

        .orcha-saring-baca .jumlah {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.35rem;
            height: 1.35rem;
            padding: 0 .35rem;
            border-radius: 1rem;
            background: #1d6fa5;
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            line-height: 1;
        }
    </style>
</div>
