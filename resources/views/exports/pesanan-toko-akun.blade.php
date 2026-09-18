@php
    $tgl = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d/m/Y') : '';
@endphp
<table>
    <thead>
        <tr>
            <th>Produk</th>
            <th>No. Pesanan</th>
            <th>Pelanggan</th>
            <th>No. HP</th>
            <th>Username Akun</th>
            <th>Mulai</th>
            <th>Berakhir</th>
            <th>Langganan</th>
            <th>Diingatkan (segera habis)</th>
            <th>Diberi tahu (habis)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->order->order_number ?? '' }}</td>
                <td>{{ $item->order->customer->nama ?? '' }}</td>
                <td>{{ $item->order->customer->no_hp ?? '' }}</td>
                <td>{{ $item->account_username }}</td>
                <td>{{ $tgl($item->start_date) }}</td>
                <td>{{ $tgl($item->end_date) }}</td>
                <td>{{ $item->subscription_status }}</td>
                <td>{{ $item->ingat_perpanjang_at ? $item->ingat_perpanjang_at->format('d/m/Y H:i') : '' }}</td>
                <td>{{ $item->habis_notified_at ? $item->habis_notified_at->format('d/m/Y H:i') : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
