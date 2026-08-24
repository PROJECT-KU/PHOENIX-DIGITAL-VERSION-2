{{--
    Kotak cari beserta tombol pengosongnya.

    Dipakai bersama seluruh daftar Orcha supaya bentuk dan perilakunya satu.
    Tombol silangnya hanya muncul saat ada isinya — tombol yang selalu ada
    tetapi tidak selalu berguna hanya menambah benda yang harus diabaikan mata.

    Variabel: $cari (dari komponen), $petunjuk (teks placeholder).
--}}
<div class="form-group position-relative mb-0">
    <div class="form-control-icon"><i class="bi bi-search"></i></div>

    <input wire:model.live.debounce.400ms="cari" type="text"
        class="form-control ps-5 {{ $cari !== '' ? 'pe-5' : '' }}"
        value="{{ $cari }}"
        placeholder="{{ $petunjuk ?? 'Cari...' }}">

    @if ($cari !== '')
        <button type="button" class="orcha-cari-bersih" wire:click="bersihkanCari"
            title="Kosongkan pencarian" aria-label="Kosongkan pencarian">
            <i class="bi bi-x-lg"></i>
        </button>
    @endif
</div>
