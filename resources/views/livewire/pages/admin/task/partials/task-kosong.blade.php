{{-- Keadaan kosong bersama untuk papan scrum & grafik aktivitas.

     Dipisah dari keadaan kosong tabel karena pertanyaannya sama tetapi
     wadahnya berbeda — dan tanpa ini, saringan yang tidak menghasilkan apa pun
     memunculkan papan bertiga kolom kosong tanpa satu pun jalan keluar. --}}
<div class="dsb-kartu">
    <div class="dsb-kosong">
        <span class="dsb-kosong-ikon">
            <i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-clipboard-check' }}"></i>
        </span>

        @if ($adaSaringan)
            <p class="dsb-kosong-judul">Tidak ada task yang cocok</p>
            <p class="dsb-kosong-ket">Tidak ada task yang cocok dengan saringan yang sedang aktif.</p>
            <button type="button" wire:click="kosongkanSaringan" class="dsb-tombol is-utama" style="margin-top: 14px;">
                <i class="bi bi-x-circle"></i><span>Kosongkan saringan</span>
            </button>
        @else
            <p class="dsb-kosong-judul">
                {{ $tampilan === 'aktivitas' ? 'Belum ada yang diselesaikan' : 'Belum ada task' }}
            </p>
            <p class="dsb-kosong-ket">
                {{ $tampilan === 'aktivitas'
                    ? 'Grafik ini terisi setelah ada task yang diselesaikan pada tahun yang dipilih.'
                    : 'Task yang ditugaskan kepada Anda akan muncul di sini.' }}
            </p>
        @endif
    </div>
</div>
