{{-- Rekap task untuk Excel. Satu baris = satu task (per penerima), bukan per
     grup: di lembar sebar yang dipakai orang untuk menyaring dan menjumlah
     sendiri, satu baris per orang jauh lebih berguna daripada satu baris per
     pekerjaan dengan nama-nama yang dijejalkan ke satu sel.

     Tidak ada kolom rupiah — lihat catatan di App\Exports\TaskExport. --}}
<table>
    <thead>
        <tr>
            <th>Nama Task</th>
            <th>Kategori</th>
            <th>Label</th>
            <th>Bobot</th>
            <th>Poin</th>
            <th>Pemberi</th>
            <th>Penerima</th>
            <th>Mulai</th>
            <th>Tenggat</th>
            <th>Status</th>
            <th>Hasil</th>
            <th>Selesai</th>
            <th>Telat (hari)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $labelProgres = ['belum' => 'Belum Dikerjakan', 'dikerjakan' => 'Dikerjakan', 'selesai' => 'Selesai'];
            $labelBonus = [
                'tepat_waktu' => 'Tepat Waktu',
                'terlambat' => 'Melebihi Tenggat',
                'tidak_selesai' => 'Tidak Selesai',
                'tidak_ada_info' => 'Berjalan',
            ];
        @endphp
        @foreach ($tasks as $t)
            @php $bs = $t->bonusStatus(); @endphp
            <tr>
                <td>{{ $t->nama }}</td>
                <td>{{ $t->category->nama ?? '-' }}</td>
                <td>{{ $t->label->nama ?? '-' }}</td>
                <td>{{ ucfirst($t->bobot) }}</td>
                <td>{{ $t->bobotPoin() }}</td>
                <td>{{ $t->pemberi->name ?? $t->pembuat->name ?? 'Admin' }}</td>
                <td>{{ $t->karyawan->name ?? '-' }}</td>
                <td>{{ $t->deadline_mulai?->format('d/m/Y') }}</td>
                <td>{{ $t->deadline_selesai?->format('d/m/Y') }}</td>
                <td>{{ $labelProgres[$t->progress] ?? ucfirst($t->progress) }}</td>
                <td>{{ $labelBonus[$bs] ?? $bs }}</td>
                <td>{{ $t->completed_at?->format('d/m/Y') }}</td>
                <td>{{ $t->hariTerlambat() ?: '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
