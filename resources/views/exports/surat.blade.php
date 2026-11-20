{{--
    Lembar Excel buku agenda.

    Satu baris = satu surat, dan kolomnya ditulis selengkap buku agenda
    kertas — termasuk tembusan, tempat berkas, dan tanggal boleh musnah.
    Inilah berkas yang dipakai di kantor untuk disaring dan dihitung sendiri;
    yang ringkas ada di versi PDF-nya.
--}}
<table>
    <thead>
        <tr><th colspan="17" style="font-weight:bold;font-size:14px;">BUKU AGENDA SURAT</th></tr>
        <tr><th colspan="17">{{ $ringkasan }}</th></tr>
        <tr><th colspan="17">Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H.i') }} &middot; {{ $surat->count() }} surat</th></tr>
        <tr><th colspan="17"></th></tr>
        <tr>
            <th style="font-weight:bold;">No</th>
            <th style="font-weight:bold;">Nomor Agenda</th>
            <th style="font-weight:bold;">Jenis</th>
            <th style="font-weight:bold;">Nomor Surat</th>
            <th style="font-weight:bold;">Tanggal Surat</th>
            <th style="font-weight:bold;">Tanggal Diterima/Dikirim</th>
            <th style="font-weight:bold;">Perihal</th>
            <th style="font-weight:bold;">Dari</th>
            <th style="font-weight:bold;">Kepada</th>
            <th style="font-weight:bold;">Tembusan</th>
            <th style="font-weight:bold;">Klasifikasi</th>
            <th style="font-weight:bold;">Sifat</th>
            <th style="font-weight:bold;">Status</th>
            <th style="font-weight:bold;">Tempat Berkas</th>
            <th style="font-weight:bold;">Boleh Dimusnahkan</th>
            <th style="font-weight:bold;">Menanggapi</th>
            <th style="font-weight:bold;">Dicatat Oleh</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($surat as $i => $s)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $s->nomor_agenda }}</td>
                <td>{{ $s->arah_label }}</td>
                <td>{{ $s->nomor_surat ?: '-' }}</td>
                <td>{{ $s->tanggal_surat_teks }}</td>
                <td>{{ $s->tanggal_catat_teks }}</td>
                <td>{{ $s->perihal }}</td>
                <td>{{ $s->dari ?: '-' }}</td>
                <td>{{ $s->kepada ?: '-' }}</td>
                <td>{{ $s->tembusan ?: '-' }}</td>
                <td>{{ $s->klasifikasi?->label ?: 'Belum diklasifikasi' }}</td>
                <td>{{ $s->sifat_label }}</td>
                <td>{{ $s->status_label }}</td>
                <td>{{ $s->lokasi_fisik ?: '-' }}</td>
                <td>{{ $s->musnah_pada_teks }}</td>
                <td>{{ $s->induk?->nomor_agenda ?: '-' }}</td>
                <td>{{ $s->pencatat?->name ?: '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
