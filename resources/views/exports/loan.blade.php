<table>
    <thead>
        <tr>
            <th>ID Transaksi</th>
            <th>Nama Peminjam</th>
            <th>Tanggal Pinjam</th>
            <th>Nominal</th>
            <th>Deskripsi</th>
            <th>Status</th>
            <th>Penginput</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans as $loan)
        <tr>
            <td>{{ $loan->id_transaksi }}</td>
            <td>{{ $loan->nama_peminjam }}</td>
            <td>{{ $loan->tanggal_peminjam_formatted ?? $loan->tanggal_pinjam }}</td>
            <td>{{ $loan->nominal_formatted ?? 'Rp ' . number_format($loan->nominal, 0, ',', '.') }}</td>
            <td>{{ $loan->deskripsi }}</td>
            <td>{{ ucfirst($loan->status) }}</td>
            <td>{{ $loan->penginput->name ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
