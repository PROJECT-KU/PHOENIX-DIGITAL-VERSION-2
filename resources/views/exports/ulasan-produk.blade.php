<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jenis</th>
            <th>Produk / Paket</th>
            <th>Nama Pengulas</th>
            <th>Rating</th>
            <th>Ulasan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($ulasan as $u)
            <tr>
                <td>{{ $u->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $u->jenis === 'paket' ? 'Paket bundling' : 'Produk satuan' }}</td>
                <td>{{ $u->namaTarget() }}</td>
                <td>{{ $u->nama }}</td>
                <td>{{ $u->rating }}</td>
                <td>{{ $u->ulasan }}</td>
                <td>{{ $u->tampilanStatus()[0] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
