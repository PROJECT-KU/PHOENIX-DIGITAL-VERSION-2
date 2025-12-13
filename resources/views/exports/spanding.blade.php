<table>
    <thead>
        <tr>
            <th>ID Transaksi</th>
            <th>Tanggal Transaksi</th>
            <th>Nominal</th>
            <th>Deskripsi</th>
            <th>Status</th>
            <th>Penginput</th>
            <th>PIC Pembeli</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($spendings as $spending)
            <tr>
                <td>{{ $spending->id_transaksi }}</td>
                <td>{{ $spending->tanggal_transaksi_formatted }}</td>
                <td>{{ $spending->nominal_formatted }}</td>
                <td>{{ $spending->deskripsi }}</td>
                <td>{{ ucfirst($spending->status) }}</td>
                <td>{{ $spending->namaPenginput }}</td>
                <td>{{ $spending->namaPicPembeli }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
