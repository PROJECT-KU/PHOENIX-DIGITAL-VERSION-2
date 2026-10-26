<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Jenis</th>
            <th>Wilayah</th>
            <th>Kontak</th>
            <th>Jumlah harga</th>
            <th>Status</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($baris as $v)
            <tr>
                <td>{{ $v['nama'] }}</td>
                <td>{{ $v['jenis'] ?: '-' }}</td>
                {{-- Kosong berarti berlaku di mana saja, bukan belum diisi —
                     ditulis apa adanya supaya tidak terbaca sebagai lubang data. --}}
                <td>{{ $v['provinsi'] ?: 'Semua wilayah' }}</td>
                {{-- Didahului petik supaya "0812…" tidak dibaca Excel sebagai
                     angka dan nol depannya hilang. --}}
                <td>{{ $v['kontak'] ? "'".$v['kontak'] : '-' }}</td>
                <td>{{ $v['jumlah_harga'] }}</td>
                <td>{{ $v['aktif'] ? 'Aktif' : 'Dimatikan' }}</td>
                <td>{{ $v['catatan'] ?: '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
