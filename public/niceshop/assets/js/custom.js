// document.querySelectorAll('input[name="price_option"]').forEach(radio => {
//     radio.addEventListener('change', function () {
//         let selectedPrice = this.dataset.price;
//         let salePrice = document.querySelector('.sale-price');

//         if (salePrice && selectedPrice) {
//             salePrice.textContent = selectedPrice;
//         }

//         // Update UI active class
//         document.querySelectorAll('.price-option').forEach(opt => {
//             opt.classList.remove('active');
//         });
//         this.closest('.price-option').classList.add('active');
//     });
// });

function formatRupiah(number) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
    }

    const salePrice = document.getElementById('salePrice');
    const regularPrice = document.getElementById('regularPrice');

    // Default state: regular price hidden
    regularPrice.style.display = "none";

    document.querySelectorAll('input[name="price_option"]').forEach(radio => {
        radio.addEventListener('change', function () {

            const harga = parseInt(this.dataset.value);
            const multiplier = parseInt(this.dataset.multiplier);

            // Sale price = harga paket
            salePrice.textContent = formatRupiah(harga);

            // Harga coret = harga * multiplier
            let hargaCoret = harga * multiplier;

            // Khusus paket per bulan → harga_awal menjadi harga coret
            if (this.value === 'perbulan') {
                hargaCoret = parseInt(this.dataset.regular);
            }

            regularPrice.textContent = formatRupiah(hargaCoret);
            regularPrice.style.display = "inline-block"; // show only after choosing
        });
    });