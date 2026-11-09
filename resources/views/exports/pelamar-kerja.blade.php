<table>
    <thead>
        <tr>
            <th>No. Lamaran</th>
            <th>Tanggal</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Telepon</th>
            <th>Posisi</th>
            <th>Perusahaan</th>
            <th>Tahap</th>
            <th>Tahap Diubah</th>
            <th>Portofolio</th>
            <th>Tahu dari</th>
            <th>Surat Lamaran</th>
            <th>Berkas Pendukung</th>
            <th>Catatan Internal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pelamar as $p)
            <tr>
                <td>{{ $p->nomor_lamaran }}</td>
                <td>{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->email }}</td>
                <td>{{ $p->phone }}</td>
                <td>{{ $p->posisi_label }}</td>
                <td>{{ $p->job?->unit_label ?? 'Grup Asthana' }}</td>
                <td>{{ $p->status_label }}</td>
                <td>{{ $p->status_diubah_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $p->portofolio_url }}</td>
                <td>{{ $p->sumber_info_label }}</td>
                <td>{{ $p->cover_letter_path ? 'Ada' : 'Tidak' }}</td>
                <td>{{ $p->lampiran_path ? 'Ada' : 'Tidak' }}</td>
                <td>{{ $p->catatan_internal }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
