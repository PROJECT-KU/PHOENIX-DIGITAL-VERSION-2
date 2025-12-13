<a href="{{ route('cart') }}" class="header-action-btn cart-btn">
    <div class="cart-icon-wrapper">
        <i class="bi bi-cart3"></i>
        @if($cartCount > 0)
        <span class="badge cart-count" style="background-color: red;">{{ $cartCount }}</span>
        @endif
    </div>
</a>