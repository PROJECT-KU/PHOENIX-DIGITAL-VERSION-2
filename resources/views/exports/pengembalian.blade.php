<table>
    <thead>
        <tr>
            <th>ID Transaksi</th>
            <th>Nama Pengembalian</th>
            <th>Tanggal Pengembalian</th>
            <th>Nominal</th>
            <th>Deskripsi</th>
            <th>Status</th>
            <th>Penginput</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pengembalian as $item)
            <tr>
                <td>{{ $item->id_transaksi }}</td>
                <td>{{ $item->nama_pengembalian }}</td>
                <td>{{ $item->tanggal_pengembalian }}</td>
                <td>{{ $item->nominal_formatted ?? number_format($item->nominal, 0, ',', '.') }}</td>
                <td>{{ $item->deskripsi }}</td>
                <td>{{ ucfirst($item->status) }}</td>
                <td>{{ $item->penginput->name ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
