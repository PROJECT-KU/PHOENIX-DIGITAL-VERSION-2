{{-- Modal detail paket bundling.
     Dipakai halaman /bundling dan halaman paket tersendiri lewat trait
     App\Livewire\Concerns\DetailPaket, supaya isinya tidak pernah berbeda.
     Harga memakai nilai dari HargaPaket yang sudah dihitung di openDetail. --}}
@php
    // Nama metode keranjang berbeda antar komponen; default addToCart.
    $aksiKeranjang = $aksiKeranjang ?? 'addToCart';
@endphp
@if ($showBundleDetail && $detailBundle)
    <div class="fs-modal-overlay" wire:key="bd-detail-modal" wire:click.self="closeDetail">
        <div class="fs-modal bd-detail">
            <button type="button" class="fs-modal-close" wire:click="closeDetail" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>

            <div class="bdl-card bdl-card--modal">
                @include('partials.bundling-header', [
                    'produk' => collect($detailBundle['produk'] ?? [])->pluck('nama')->all(),
                    'nama' => $detailBundle['nama'],
                ])

                @include('partials.bundling-deskripsi', ['teks' => $detailBundle['deskripsi'] ?? ''])

                <div class="text-center mb-3">
                    <span class="bdl-promo">PROMO HARI INI!</span>
                </div>

                <div class="text-center mb-3">
                    @if ($detailBundle['coret'] > $detailBundle['bayar'])
                        <div class="bdl-price-old">Rp {{ number_format($detailBundle['coret'], 0, ',', '.') }}</div>
                    @endif
                    <div>
                        <span class="bdl-price-now">Rp {{ number_format($detailBundle['bayar'], 0, ',', '.') }}</span>
                        <span class="bdl-price-unit">/ paket</span>
                    </div>
                    @if ($detailBundle['butuh_kode'])
                        <div class="bdl-price-unit mt-1">Harga promo aktif setelah kode promo dipakai di keranjang.</div>
                    @endif
                </div>

                @if (!empty($detailBundle['produk']))
                    <div class="bdl-incl mb-3">
                        <div class="bdl-incl-title"><i class="bi bi-box-seam"></i> Termasuk dalam paket</div>
                        @foreach ($detailBundle['produk'] as $pr)
                            <div class="bdl-incl-row">
                                <span class="bdl-incl-name">
                                    <i class="bi bi-check-circle-fill"></i>{{ $pr['nama'] }}
                                </span>
                                <span class="bdl-dur-badge">{{ $pr['dur_value'] }} {{ $pr['dur_type'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <button type="button" class="bdl-order-btn mt-auto" wire:click="{{ $aksiKeranjang }}('{{ $detailBundle['id'] }}')"
                    wire:loading.attr="disabled" wire:target="{{ $aksiKeranjang }}('{{ $detailBundle['id'] }}')">
                    <span wire:loading.remove wire:target="{{ $aksiKeranjang }}('{{ $detailBundle['id'] }}')">Pesan Sekarang!</span>
                    <span wire:loading wire:target="{{ $aksiKeranjang }}('{{ $detailBundle['id'] }}')">Memproses...</span>
                </button>

                <p class="bdl-foot mt-3 mb-0">🎉 <b>Jangan lewatkan kesempatan terbatas ini!</b> Promo bisa berakhir kapan saja.</p>
            </div>
        </div>
    </div>
@endif
