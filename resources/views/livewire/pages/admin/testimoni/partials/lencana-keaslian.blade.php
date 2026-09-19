{{-- Bekal admin menilai keaslian: sudah belanja berapa kali & sudah member
     atau belum. Syaratnya ADA TAUTAN PELANGGAN atau kiriman pelanggan — bukan
     source-nya (testimoni yang diinput admin dari WhatsApp tetap bisa punya
     tautan pelanggan). Variabel: $item (Testimoni, customer dimuat dengan
     belanja_selesai_count). --}}
<div class="tm-chip-deret">
    @if ($item->anonim)
        <span class="dsb-lencana is-abu" title="Pengirim memilih anonim — di publik tampil sebagai {{ $item->nama_publik }}"><i class="bi bi-incognito"></i>Anonim</span>
    @endif
    @if ($item->source === 'customer')
        <span class="dsb-lencana is-biru" title="Dikirim langsung oleh pelanggan lewat form testimoni"><i class="bi bi-person-heart"></i>Dari Pelanggan</span>
    @endif
    @if ($item->customer)
        <span class="dsb-lencana is-hijau" title="Nomor WhatsApp cocok dengan pelanggan terdaftar"><i class="bi bi-bag-check-fill"></i>Belanja {{ $item->customer->belanja_selesai_count ?? 0 }}×</span>
        @if ($item->customer->status_member === 'active')
            <span class="dsb-lencana is-ungu" title="Sudah menjadi Member"><i class="bi bi-star-fill"></i>Member</span>
        @else
            <span class="dsb-lencana is-kuning" title="Akan otomatis jadi member begitu testimoni ini disetujui"><i class="bi bi-hourglass-split"></i>Belum member</span>
        @endif
    @elseif ($item->source === 'customer' && $item->no_hp)
        <span class="dsb-lencana is-merah" title="Nomor tidak cocok dengan pelanggan mana pun, atau pesanannya belum ada yang Selesai"><i class="bi bi-x-circle-fill"></i>Belum pernah belanja</span>
    @elseif ($item->source === 'customer')
        <span class="dsb-lencana is-abu" title="Testimoni lama — dikirim sebelum nomor WhatsApp diwajibkan"><i class="bi bi-question-circle"></i>Tanpa nomor</span>
    @endif
</div>
{{-- "Pemilik nomor" hanya bila namanya BERBEDA dari yang diketik. --}}
@if ($item->customer && mb_strtolower(trim($item->nama)) !== mb_strtolower(trim($item->customer->nama)))
    <div class="tm-pemilik" title="Orang lazim mengetik nama panggilan — ini info, bukan tanda kecurangan">
        <i class="bi bi-person-vcard"></i> Pemilik nomor: <b>{{ $item->customer->nama }}</b>
    </div>
@endif
