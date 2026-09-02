@section('title')
Daftar Tunggu || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Daftar Tunggu',
            'keterangan' => 'Peminat yang menunggu kursi terbuka di trip yang sudah penuh.',
        ])

        {{-- Keterangan pembuka, bukan hiasan.

             Yang membuka layar ini akan bertanya apakah ia harus menghubungi
             mereka satu per satu. Jawabannya tidak — sistem sudah melakukannya.
             Dijawab di sini supaya tidak ada pekerjaan ganda, dan supaya yang
             MEMANG perlu dikerjakan manusia terlihat jelas. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-bagian-kepala mb-0">
                    <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <div class="orcha-bagian-judul">Mereka dikabari otomatis</div>
                        <div class="orcha-bagian-sub">
                            Begitu ada kursi yang dilepas — biasanya karena pendaftar lain tidak
                            membayar — yang paling lama menunggu langsung dikabari lewat email,
                            sebanyak kursi yang terbuka saja.
                            <br>
                            Yang perlu Anda kerjakan sendiri dua hal: menghubungi yang
                            <strong>tidak mencantumkan email</strong>, dan memutuskan apakah
                            antrean yang panjang layak dibukakan keberangkatan tambahan.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control" wire:model.live.debounce.400ms="cari"
                                placeholder="Cari nama atau nomor WhatsApp...">
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <select class="form-select" wire:model.live="filterPaket">
                            <option value="">Semua trip</option>
                            @foreach ($paketPilihan as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-2 text-lg-end">
                        <span class="text-muted small">{{ $meta['total'] ?? 0 }} menunggu</span>
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
                                <th class="ps-4">PEMINAT</th>
                                <th>TRIP</th>
                                <th>MENUNGGU SEJAK</th>
                                <th>KABAR</th>
                                <th class="text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                @php
                                    $wa = 'https://api.whatsapp.com/send?phone='
                                        . preg_replace('/^0/', '62', preg_replace('/\D/', '', $baris['whatsapp']))
                                        . '&text=' . rawurlencode(
                                            'Halo ' . $baris['nama'] . ', ada kabar soal '
                                            . ($baris['paket'] ?? 'trip yang Anda tunggu') . '.');
                                @endphp

                                <tr wire:key="tunggu-{{ $baris['id'] }}">
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted small">
                                            {{ $baris['whatsapp'] }} · {{ $baris['jumlah_peserta'] }} orang
                                        </div>
                                    </td>

                                    <td class="text-muted small">{{ $baris['paket'] ?? '—' }}</td>

                                    <td class="text-muted small">
                                        {{ $baris['menunggu_sejak']
                                            ? \Carbon\Carbon::parse($baris['menunggu_sejak'])->locale('id')->diffForHumans()
                                            : '—' }}
                                    </td>

                                    <td>
                                        @if ($baris['dikabari_pada'])
                                            <span class="badge bg-success-subtle text-success-emphasis">
                                                Dikabari
                                                {{ \Carbon\Carbon::parse($baris['dikabari_pada'])->locale('id')->diffForHumans() }}
                                            </span>
                                        @elseif (blank($baris['email']))
                                            {{-- Ini yang menuntut perbuatan: tanpa email, sistem
                                                 tidak bisa mengabarinya sama sekali. Ditandai
                                                 supaya tidak tenggelam di antara yang lain. --}}
                                            <span class="badge bg-warning-subtle text-warning-emphasis">
                                                Tanpa email — hubungi sendiri
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                Menunggu kursi
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-end pe-4">
                                        {{-- Varian tombolnya dipakai apa adanya dari partial gaya.

                                             Sebelumnya keduanya cuma berkelas .orcha-aksi — yang
                                             latarnya bening dan batasnya bening — sehingga tombol
                                             WhatsApp tergambar sebagai ikon telanjang tanpa kotak,
                                             sementara tombol hapus di sebelahnya berkotak. Dua
                                             tombol berdampingan yang bentuknya berbeda terbaca
                                             seperti salah satunya rusak.

                                             orcha-aksi-wa dan orcha-aksi-hapus sudah ada dan sudah
                                             dipakai halaman Pesan Kontak; bentuk pembungkusnya pun
                                             disalin dari sana supaya jaraknya sama. --}}
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ $wa }}" target="_blank" rel="noopener"
                                                class="btn btn-sm orcha-aksi orcha-aksi-wa"
                                                title="Hubungi lewat WhatsApp">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus"
                                                wire:click="keluarkan({{ $baris['id'] }})"
                                                wire:confirm="Keluarkan {{ $baris['nama'] }} dari daftar tunggu? Ia tidak akan dikabari lagi saat ada kursi terbuka."
                                                title="Keluarkan dari antrean">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-3">
                                            <i class="bi bi-hourglass-split"></i>
                                        </div>
                                        <p class="text-muted mb-0">
                                            Belum ada yang menunggu. Daftar ini terisi sendiri saat
                                            ada trip yang kursinya habis.
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

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
