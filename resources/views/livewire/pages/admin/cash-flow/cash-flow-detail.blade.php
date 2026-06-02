<div>
    @if($isOpen && $cashFlow)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" wire:click="$set('isOpen', false)" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Info Dasar</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td>Tanggal</td>
                                    <td>: {{ $cashFlow->transaction_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td>Tipe</td>
                                    <td>: {{ ucfirst($cashFlow->type) }}</td>
                                </tr>
                                <tr>
                                    <td>Nominal</td>
                                    <td>: Rp {{ number_format($cashFlow->amount) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Sumber Data ({{ class_basename($cashFlow->sourceable_type) }})</h6>

                            @if($cashFlow->sourceable instanceof \App\Models\Order)
                            <p>Customer: {{ $cashFlow->sourceable->customer->name ?? '-' }}</p>
                            <p>Metode Bayar: {{ $cashFlow->sourceable->payment_method }}</p>
                            <a href="#" class="btn btn-primary btn-sm">Lihat Invoice</a>

                            @elseif($cashFlow->sourceable instanceof \App\Models\GajiKaryawans)
                            <p>Karyawan: {{ $cashFlow->sourceable->karyawan->name ?? '-' }}</p>
                            <p>Bank: {{ $cashFlow->sourceable->bank }}</p>

                            @elseif($cashFlow->sourceable instanceof \App\Models\Loan)
                            <p>Peminjam: {{ $cashFlow->sourceable->nama_peminjam }}</p>
                            <p>Status Pinjaman: {{ $cashFlow->sourceable->status }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>