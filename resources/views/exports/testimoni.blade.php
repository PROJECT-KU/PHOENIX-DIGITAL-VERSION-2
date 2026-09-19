<table>
    <thead>
        <tr>
            <th>Tanggal Kirim</th>
            <th>Nama (Admin)</th>
            <th>Tampil Sebagai</th>
            <th>Peran</th>
            <th>Rating</th>
            <th>Testimoni</th>
            <th>Status</th>
            <th>Tampil di Beranda</th>
            <th>Disorot</th>
            <th>Sumber</th>
            <th>Pembeli Terverifikasi</th>
            <th>Ditinjau</th>
            <th>Peninjau</th>
            <th>Alasan Tolak</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($testimoni as $t)
            <tr>
                <td>{{ $t->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $t->nama }}</td>
                {{-- nama_publik: nama yang dilihat pengunjung (tersamar bila anonim). --}}
                <td>{{ $t->nama_publik }}</td>
                <td>{{ $t->peran }}</td>
                <td>{{ $t->rating }}</td>
                <td>{{ $t->pesan }}</td>
                <td>{{ ['pending' => 'Menunggu', 'active' => 'Disetujui', 'non-active' => 'Ditolak'][$t->status] ?? $t->status }}</td>
                <td>{{ $t->status === 'active' && ! $t->tersembunyiKarenaRating() ? 'Ya' : 'Tidak' }}</td>
                <td>{{ $t->sorot ? 'Ya' : 'Tidak' }}</td>
                <td>{{ $t->source === 'customer' ? 'Kiriman pelanggan' : 'Input admin' }}</td>
                <td>{{ $t->customer_id ? 'Ya' : 'Tidak' }}</td>
                <td>{{ $t->ditinjau_at?->format('d/m/Y H:i') }}</td>
                <td>{{ $t->peninjau?->name }}</td>
                <td>{{ $t->alasan_tolak }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
