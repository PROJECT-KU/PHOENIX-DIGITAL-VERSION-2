<table>
    <thead>
        <tr>
            <th>ID Transaksi</th>
            <th>Nama Karyawan</th>
            <th>Tanggal Transaksi</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->id_transaksi }}</td>
                <td>{{ $item->karyawan->name ?? '-' }}</td>
                <td>{{ $item->tanggal_transaksi }}</td>
                <td>{{ number_format($item->total, 0, ',', '.') }}</td>
                <td>{{ ucfirst($item->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
