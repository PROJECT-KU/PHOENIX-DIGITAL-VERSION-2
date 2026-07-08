
@section('title')
Detail Cash Flow || PT. Asthana Cipta Mandiri
@stop
<div>
    @if($isOpen && $cashFlow)
    @php $isIncome = $cashFlow->type === 'income'; @endphp
    @teleport('body')
    <div class="modal fade show d-block" tabindex="-1"
        style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); z-index: 1080;"
        wire:keydown.escape="close">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">

                {{-- ===== Header ===== --}}
                <div class="modal-header border-0 px-4 pt-4 pb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper {{ $isIncome ? 'bg-gradient-green' : 'bg-gradient-red' }} flex-shrink-0">
                            <i class="bi {{ $isIncome ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle' }}"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Detail Transaksi</h5>
                            <small class="text-muted">{{ $detail['jenis'] }} &middot; #{{ strtoupper(substr($cashFlow->id, 0, 8)) }}</small>
                        </div>
                    </div>
                    <button type="button" wire:click="close" class="btn-close"></button>
                </div>

                <div class="modal-body px-4 pb-2">

                    {{-- ===== Ringkasan nominal ===== --}}
                    <div class="rounded-4 p-4 mb-4"
                        style="background: {{ $isIncome ? 'rgba(16,185,129,0.08)' : 'rgba(244,63,94,0.08)' }};">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Nominal Transaksi</p>
                                <h2 class="fw-bold mb-0 {{ $isIncome ? 'text-success' : 'text-danger' }}">
                                    {{ $isIncome ? '+' : '-' }} Rp {{ number_format($cashFlow->amount) }}
                                </h2>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light-{{ $isIncome ? 'success' : 'danger' }} text-{{ $isIncome ? 'success' : 'danger' }} mb-2 d-inline-block">
                                    {{ $isIncome ? 'Pemasukan' : 'Pengeluaran' }}
                                </span>
                                <p class="text-muted mb-0" style="font-size: 0.8rem;">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $cashFlow->transaction_date->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Info dasar ===== --}}
                    <h6 class="fw-bold text-dark text-uppercase mb-3" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        <i class="bi bi-info-circle me-1 text-primary"></i>Informasi Umum
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between border-bottom pb-2">
                                <span class="text-muted">Kategori</span>
                                <span class="fw-semibold text-dark">{{ ucfirst($cashFlow->category ?? '-') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between border-bottom pb-2">
                                <span class="text-muted">Tipe</span>
                                <span class="fw-semibold text-dark">{{ $isIncome ? 'Income' : 'Expense' }}</span>
                            </div>
                        </div>
                        @if($cashFlow->description)
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Deskripsi</span>
                                <span class="fw-semibold text-dark text-end">{{ $cashFlow->description }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- ===== Detail sumber ===== --}}
                    @if(!empty($detail['rows']))
                    <h6 class="fw-bold text-dark text-uppercase mb-3" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        <i class="bi bi-diagram-3 me-1 text-primary"></i>Informasi Sumber
                    </h6>
                    <div class="row g-3 mb-4">
                        @foreach($detail['rows'] as $label => $value)
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="text-muted">{{ $label }}</span>
                                @if ($label === 'Metode Bayar' && $value && $value !== '-')
                                @php
                                $ic = str_contains(strtolower($value), 'qris') ? 'bi-qr-code-scan' : 'bi-bank';
                                @endphp
                                <span class="badge bg-primary-subtle text-primary border border-primary d-inline-flex align-items-center gap-1">
                                    <i class="bi {{ $ic }}"></i> {{ $value }}
                                </span>
                                @elseif ($label === 'Status' && $value && $value !== '-')
                                @php
                                $sc = strtolower($value);
                                $col = 'secondary';
                                if ($sc === 'pending') $col = 'warning';
                                elseif ($sc === 'processing') $col = 'info';
                                elseif ($sc === 'paid') $col = 'success';
                                elseif ($sc === 'cancelled') $col = 'danger';
                                elseif ($sc === 'completed') $col = 'primary';
                                @endphp
                                <span class="badge bg-{{ $col }}">{{ strtoupper($value) }}</span>
                                @else
                                <span class="fw-semibold text-dark text-end">{{ $value ?: '-' }}</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- ===== Rincian item (khusus pesanan) ===== --}}
                    @if(!empty($detail['items']))
                    <h6 class="fw-bold text-dark text-uppercase mb-3" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        <i class="bi bi-box-seam me-1 text-primary"></i>Rincian Item
                    </h6>
                    <div class="table-responsive mb-3">
                        <table class="table align-middle mb-0" style="font-size: 0.85rem;">
                            <thead>
                                <tr class="text-uppercase text-muted" style="font-size: 0.7rem;">
                                    <th class="border-0">Produk</th>
                                    <th class="border-0">Durasi</th>
                                    <th class="border-0 text-center">Qty</th>
                                    <th class="border-0 text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detail['items'] as $it)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $it['nama'] }}</td>
                                    <td class="text-muted">{{ $it['durasi'] }}</td>
                                    <td class="text-center">{{ $it['qty'] }}</td>
                                    <td class="text-end fw-semibold">{{ $it['subtotal'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(!empty($detail['itemsNote']))
                    <p class="text-muted mb-3" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle me-1"></i>{{ $detail['itemsNote'] }}
                    </p>
                    @endif
                    @endif

                    {{-- ===== Total ===== --}}
                    @if(!empty($detail['totals']))
                    @php $totalKeys = array_keys($detail['totals']); $lastKey = end($totalKeys); @endphp
                    <div class="rounded-4 p-3 mb-2" style="background: rgba(124,58,237,0.05);">
                        @foreach($detail['totals'] as $label => $value)
                        <div class="d-flex justify-content-between {{ $label === $lastKey ? 'pt-2 mt-1 border-top' : 'mb-1' }}">
                            <span class="{{ $label === $lastKey ? 'fw-bold text-dark' : 'text-muted' }}">{{ $label }}</span>
                            <span class="fw-bold {{ $label === $lastKey ? 'text-primary fs-6' : 'text-dark' }}">{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                </div>

                {{-- ===== Footer ===== --}}
                <div class="modal-footer border-0 px-4 pb-4 pt-2">
                    <button type="button" wire:click="close"
                        class="btn btn-danger rounded-3 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-x-lg"></i><span>Tutup</span>
                    </button>
                    <button type="button" wire:click="downloadInvoice" wire:loading.attr="disabled"
                        class="btn btn-primary rounded-3 d-inline-flex align-items-center justify-content-center gap-1">
                        <span wire:loading.remove wire:target="downloadInvoice" class="d-inline-flex align-items-center gap-1">
                            <i class="bi bi-file-earmark-pdf"></i><span>Unduh Invoice (PDF)</span>
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endteleport
    @endif
</div>