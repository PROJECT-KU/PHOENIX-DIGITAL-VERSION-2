<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center; font-weight: bold; font-size: 14px;">
                LAMPIRAN SEWA AKUN OLEH RUMAH SCOPUS
            </th>
        </tr>
        <tr>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center;">No.</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center;">Nama Camp</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center;">Periode Camp</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center;">Nama Peserta</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center;">No WA</th>
        </tr>
    </thead>
    <tbody>
        @php $globalNo = 1; @endphp
        @foreach($groupedData as $key => $items)
        @php
        [$namaCamp, $batchCamp] = explode('|', $key);
        $rowCount = $items->count();
        @endphp

        @foreach($items as $index => $item)
        <tr>
            {{-- Kolom No (Merge) --}}
            @if($index === 0)
            <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; vertical-align: top; text-align: center;">
                {{ $globalNo++ }}
            </td>

            {{-- Kolom Nama Camp (Merge) --}}
            <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; vertical-align: top; font-weight: bold;">
                {{ $namaCamp }} #{{ $batchCamp }}
            </td>

            {{-- Kolom Periode (Merge) --}}
            <td rowspan="{{ $rowCount }}" style="border: 1px solid #000000; vertical-align: top; text-align: center;">
                {{-- Mengambil tanggal dari record pertama di grup ini --}}
                {{ \Carbon\Carbon::parse($items->first()->tanggal_mulai_camp)->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($items->first()->tanggal_akhir_camp)->format('d M Y') }}
            </td>
            @endif

            {{-- Kolom Data Peserta (Per Row) --}}
            <td style="border: 1px solid #000000;">{{ $item->nama_pembeli }}</td>
            <td style="border: 1px solid #000000;">{{ $item->telp_pembeli }}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>