<table>
    <thead>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Tayang</th>
            <th>Dibaca</th>
            <th>Slug</th>
            <th>Penulis</th>
            <th>Dibuat</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($artikel as $a)
            <tr>
                <td>{{ $a->title }}</td>
                <td>{{ $a->category ?: '-' }}</td>
                <td>{{ $a->keadaan()[0] }}</td>
                <td>{{ optional($a->published_at)->format('d/m/Y H:i') ?: '-' }}</td>
                <td>{{ (int) $a->views }}</td>
                <td>/blog/{{ $a->slug }}</td>
                <td>{{ $a->author ?: '-' }}</td>
                <td>{{ optional($a->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
