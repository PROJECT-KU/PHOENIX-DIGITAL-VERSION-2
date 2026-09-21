<table>
    <thead>
        <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Tag</th>
            <th>Status</th>
            <th>Tayang</th>
            <th>Dibaca (total)</th>
            <th>Dibaca (30 hari)</th>
            <th>Lama baca</th>
            <th>Disematkan</th>
            <th>Slug</th>
            <th>Penulis</th>
            <th>Dibuat</th>
            <th>Terakhir diubah</th>
            @if ($ikutIsi)
                <th>Ringkasan</th>
                <th>Isi artikel</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($artikel as $a)
            <tr>
                <td>{{ $a->title }}</td>
                <td>{{ $a->category ?: '-' }}</td>
                <td>{{ implode(', ', $a->tagDaftar()) ?: '-' }}</td>
                <td>{{ $a->trashed() ? 'Di tong sampah' : $a->keadaan()[0] }}</td>
                <td>{{ optional($a->published_at)->format('d/m/Y H:i') ?: '-' }}</td>
                <td>{{ (int) $a->views }}</td>
                <td>{{ $a->baca30() }}</td>
                <td>{{ $a->lamaBaca() }} menit</td>
                <td>{{ $a->is_featured ? 'Ya' : 'Tidak' }}</td>
                <td>/blog/{{ $a->slug }}</td>
                <td>{{ $a->author ?: '-' }}</td>
                <td>{{ optional($a->created_at)->format('d/m/Y H:i') }}</td>
                <td>{{ optional($a->updated_at)->format('d/m/Y H:i') }}</td>
                @if ($ikutIsi)
                    <td>{{ $a->excerpt ?: '-' }}</td>
                    {{-- Isi disimpan sebagai TEKS POLOS: berkas Excel yang memuat
                         HTML mentah tidak bisa dibaca siapa pun. --}}
                    <td>{{ trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($a->body)))) }}</td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
