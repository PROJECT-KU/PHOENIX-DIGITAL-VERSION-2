
@section('title')
Detail Pesanan RSC || PT. Asthana Cipta Mandiri
@stop
<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Detail Pemesanan RSC</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Detail Pemesanan RSC']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a wire:navigate href="{{route('admin.pesananrsc.index')}}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    <span>Kembali</span>
                </a>
            </div>

            @if($batchData)
            {{-- Info Batch --}}
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-4">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th colspan="2" class="bg-light text-dark text-center">Detail Batch</th>
                                </tr>
                                <tr>
                                    <td class="fw-bold" width="180">Nama Camp</td>
                                    <td>: {{ $batchData->nama_camp }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Batch</td>
                                    <td>: {{ $batchData->batch_camp }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tanggal Mulai</td>
                                    <td>: {{ \Carbon\Carbon::parse($batchData->tanggal_mulai_camp)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tanggal Akhir</td>
                                    <td>: {{ \Carbon\Carbon::parse($batchData->tanggal_akhir_camp)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Durasi</td>
                                    <td>: {{ \Carbon\Carbon::parse($batchData->tanggal_mulai_camp)->diffInDays(\Carbon\Carbon::parse($batchData->tanggal_akhir_camp)) + 1 }} hari</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th colspan="2" class="bg-light text-dark text-center">Detail Akun</th>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Akun</td>
                                    <td>: {{ $batchData->dataakun->nama_akun ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Username</td>
                                    <td>: {{ $batchData->username ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold" width="180">Password</td>
                                    <td>:
                                        <span id="password-text">••••••••</span>
                                        <button type="button" class="btn btn-sm btn-link p-0" onclick="togglePassword()">
                                            <i class="bi bi-eye" id="eye-icon"></i>
                                        </button>
                                        <span class="d-none" id="password-real">{{ $batchData->password ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Link Akses</td>
                                    <td>:
                                        @if($batchData->link_akses)
                                        <a href="{{ $batchData->link_akses }}" target="_blank">{{ Str::limit($batchData->link_akses, 30) }}</a>
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th colspan="2" class="bg-light text-center text-dark">Detail Lainnya</th>
                                </tr>
                                <tr>
                                    <td class="fw-bold">PIC</td>
                                    <td>: {{ $batchData->users->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status</td>
                                    <td>:
                                        <span class="badge 
                                                @if($batchData->status == 'baru') bg-success
                                                @elseif($batchData->status == 'perpanjang') bg-info
                                                @elseif($batchData->status == 'pengganti') bg-warning
                                                @else bg-secondary
                                                @endif">
                                            {{ strtoupper($batchData->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tanggal Pemesanan</td>
                                    <td>: {{ \Carbon\Carbon::parse($batchData->tanggal_pemesanan)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tanggal Berakhir</td>
                                    <td>: {{ \Carbon\Carbon::parse($batchData->tanggal_berakhir)->format('d M Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($batchData->deskripsi)
                    <div class="mt-3 pt-3 border-top">
                        <strong class="d-block mb-2">Deskripsi:</strong>
                        <p class="mb-0">{{ $batchData->deskripsi }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Daftar Peserta --}}
            <table class="table table-borderless table-sm">
                <tr>
                    <th colspan="2" class="bg-light text-center text-dark">Daftar Peserta</th>
                </tr>
            </table>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>ID Transaksi</th>
                            <th>Nama Pembeli</th>
                            <th>No Telp</th>
                            <th>Jumlah Pemesanan</th>
                            <th>Harga Satuan</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pesertaList as $index => $peserta)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><code>{{ $peserta->id_transaksi }}</code></td>
                            <td>{{ $peserta->nama_pembeli ?? '-' }}</td>
                            <td>{{ $peserta->telp_pembeli ?? '-' }}</td>
                            <td>{{ $peserta->jumlah_pemesanan ?? '-' }}</td>
                            <td>Rp {{ number_format($peserta->harga_satuan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($peserta->total ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Tidak ada data peserta</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($pesertaList->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">TOTAL:</th>
                            <th class="text-end">Rp {{ number_format($batchData->total_harga, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- JavaScript untuk toggle password --}}
    <script>
        function togglePassword() {
            const passwordText = document.getElementById('password-text');
            const passwordReal = document.getElementById('password-real');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordText.textContent === '••••••••') {
                passwordText.textContent = passwordReal.textContent;
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordText.textContent = '••••••••';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }
    </script>
</div>