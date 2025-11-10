document.querySelectorAll('input[name="price_option"]').forEach(radio => {
    radio.addEventListener('change', function () {
        let selectedPrice = this.dataset.price;
        let salePrice = document.querySelector('.sale-price');

        if (salePrice && selectedPrice) {
            salePrice.textContent = selectedPrice;
        }

        // Update UI active class
        document.querySelectorAll('.price-option').forEach(opt => {
            opt.classList.remove('active');
        });
        this.closest('.price-option').classList.add('active');
    });
});