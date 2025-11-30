@if($activePromos->isNotEmpty() && $bestDiscount > 0)
    <div class="promo-badge-container position-absolute" style="top: 10px; right: 10px; z-index: 10;">
        @foreach($activePromos as $promo)
            <div class="promo-badge mb-2 animate__animated animate__pulse animate__infinite" 
                 style="background-color: {{ $promo->badge_color ?? '#FF6B6B' }};"
                 title="{{ $promo->nama_promo }}">
                <i class="bi bi-lightning-fill"></i>
                @if($discountType === 'persen')
                    <strong>{{ $bestDiscount }}%</strong>
                @else
                    <strong>Rp {{ number_format($bestDiscount / 1000, 0) }}K</strong>
                @endif
            </div>
        @endforeach
    </div>

    <style>
        .promo-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 25px;
            color: white;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            white-space: nowrap;
        }
        
        .promo-badge i {
            margin-right: 5px;
        }

        /* Tambahkan animasi pulse jika belum ada */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .animate__pulse {
            animation: pulse 2s ease-in-out infinite;
        }
    </style>
@endif
