<table>
    <thead>
        {{-- Baris 1–3 disediakan untuk logo (drawing) + identitas Phoenix Digital --}}
        <tr>
            <th colspan="5" style="height: 26px; text-align: right; font-size: 18px; font-weight: bold; color: #0f172a;">
                PHOENIX DIGITAL
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: right; font-size: 10px; color: #6b7280;">
                Digital Solution Partner &nbsp;•&nbsp; Contact: +62 895-0596-7995
            </th>
        </tr>
        <tr>
            <th colspan="5" style="height: 6px; background-color: #ea8a00;"></th>
        </tr>
        <tr>
            <th colspan="5" style="height: 10px;"></th>
        </tr>

        {{-- Judul dokumen --}}
        <tr>
            <th colspan="5" style="text-align: center; font-weight: bold; font-size: 14px; color: #92660b; background-color: #faf3e6; height: 22px;">
                LAMPIRAN SEWA AKUN OLEH RUMAH SCOPUS
            </th>
        </tr>
        <tr>
            <th colspan="5" style="height: 8px;"></th>
        </tr>

        {{-- Header kolom --}}
        <tr>
            <th style="border: 1px solid #0f172a; font-weight: bold; text-align: center; background-color: #ea8a00; color: #ffffff;">No.</th>
            <th style="border: 1px solid #0f172a; font-weight: bold; text-align: center; background-color: #ea8a00; color: #ffffff;">Nama Camp</th>
            <th style="border: 1px solid #0f172a; font-weight: bold; text-align: center; background-color: #ea8a00; color: #ffffff;">Periode Camp</th>
            <th style="border: 1px solid #0f172a; font-weight: bold; text-align: center; background-color: #ea8a00; color: #ffffff;">Nama Peserta</th>
            <th style="border: 1px solid #0f172a; font-weight: bold; text-align: center; background-color: #ea8a00; color: #ffffff;">No WA</th>
        </tr>
    </thead>
    <tbody>
        @php $globalNo = 1; @endphp
        @foreach($groupedData as $key => $items)
        @php
        [$namaCamp, $batchCamp] = explode('|', $key);
        $rowCount = $items->count();
        $stripe = $globalNo % 2 === 0 ? '#fbf7f0' : '#ffffff';
        @endphp

        @foreach($items as $index => $item)
        <tr>
            {{-- Kolom No (Merge) --}}
            @if($index === 0)
            <td rowspan="{{ $rowCount }}" style="border: 1px solid #d1d5db; vertical-align: top; text-align: center; background-color: {{ $stripe }};">
                {{ $globalNo++ }}
            </td>

            {{-- Kolom Nama Camp (Merge) --}}
            <td rowspan="{{ $rowCount }}" style="border: 1px solid #d1d5db; vertical-align: top; font-weight: bold; background-color: {{ $stripe }};">
                {{ $namaCamp }} #{{ $batchCamp }}
            </td>

            {{-- Kolom Periode (Merge) --}}
            <td rowspan="{{ $rowCount }}" style="border: 1px solid #d1d5db; vertical-align: top; text-align: center; background-color: {{ $stripe }};">
                {{ \Carbon\Carbon::parse($items->first()->tanggal_mulai_camp)->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($items->first()->tanggal_akhir_camp)->format('d M Y') }}
            </td>
            @endif

            {{-- Kolom Data Peserta (Per Row) --}}
            <td style="border: 1px solid #d1d5db;">{{ $item->nama_pembeli }}</td>
            <td style="border: 1px solid #d1d5db;">{{ $item->telp_pembeli }}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>
