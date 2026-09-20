<table>
    <thead>
        <tr>
            <th>Tiket</th>
            <th>Tanggal</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No. Telp</th>
            <th>Pesan</th>
            <th>Status</th>
            <th>Prioritas</th>
            <th>Topik</th>
            <th>Petugas</th>
            <th>Dibaca</th>
            <th>Dibalas</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pesan as $p)
            <tr>
                <td>{{ $p->ticket }}</td>
                <td>{{ $p->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->email }}</td>
                <td>{{ $p->no_telp }}</td>
                <td>{{ $p->message }}</td>
                <td>{{ $p->tampilanStatus()[0] }}</td>
                <td>{{ $p->tampilanPrioritas()[0] }}</td>
                <td>{{ $p->labelKategori() ?: '-' }}</td>
                <td>{{ $p->petugas?->name ?: '-' }}</td>
                <td>{{ $p->read_at?->format('d/m/Y H:i') ?: 'Belum dibaca' }}</td>
                <td>{{ $p->replied_at?->format('d/m/Y H:i') ?: 'Belum dibalas' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
