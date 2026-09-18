@php
    $status = \App\Support\RiwayatPesanan::STATUS;
    $tgl = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d/m/Y H:i') : '';
@endphp
<table>
    <thead>
        <tr>
            <th>No. Pesanan</th>
            <th>Tanggal Dibuat</th>
            <th>Pelanggan</th>
            <th>No. HP</th>
            <th>Produk</th>
            <th>Subtotal</th>
            <th>Diskon</th>
            <th>Total</th>
            <th>Metode Bayar</th>
            <th>Status</th>
            <th>Tanggal Dibayar</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $tgl($order->created_at) }}</td>
                <td>{{ $order->customer->nama ?? '' }}</td>
                <td>{{ $order->customer->no_hp ?? '' }}</td>
                <td>{{ $order->items->map(fn ($i) => $i->product_name.' ('.$i->duration_value.' '.$i->duration_type.')')->implode(', ') }}</td>
                <td>{{ (int) $order->subtotal }}</td>
                <td>{{ (int) $order->total_discount }}</td>
                <td>{{ (int) $order->total }}</td>
                <td>{{ $order->labelPembayaran()[0] ?? $order->payment_method }}</td>
                <td>{{ $status[$order->status] ?? $order->status }}</td>
                <td>{{ $tgl($order->paid_at) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
