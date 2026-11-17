{{-- Manifes rombongan untuk tour leader, versi Excel.

     Barisnya SAMA dengan manifes PDF: dikelompokkan per titik jemput, urut
     sama. Yang berbeda cuma bentuknya — PDF dibawa berdiri, Excel disaring dan
     dihitung. Dua berkas yang berbeda isinya adalah asal perselisihan di
     parkiran. --}}
@php
    /* Dirakit dengan aturan yang sama persis dengan manifes PDF: satu baris
       per PESERTA, dikelompokkan menurut titik jemputnya, dan di dalam tiap
       titik urut sesuai daftar pendaftarannya. */
    $baris = [];

    foreach ($daftar as $satu) {
        $riwayat = collect($kesehatan[$satu['id']] ?? [])
            ->keyBy(fn ($r) => mb_strtolower(trim($r['nama_peserta'] ?? '')));

        foreach ($satu['peserta'] ?? [] as $orang) {
            $nama = trim((string) ($orang['nama'] ?? ''));

            if ($nama === '') {
                continue;
            }

            $k = $riwayat[mb_strtolower($nama)] ?? null;

            $baris[] = [
                'titik' => trim((string) ($orang['titik_jemput'] ?? '')) ?: 'Titik jemput belum dipilih',
                'nama' => $nama,
                // Jenis kelamin dari baris pesertanya DULU, baru dari riwayat
                // kesehatan: yang pertama diisi pemesan dan selalu ada.
                'lp' => $orang['jenis_kelamin'] ?? ($k['jenis_kelamin'] ?? ''),
                'bus' => trim(($orang['bus'] ?? '').(filled($orang['kursi'] ?? null) ? ' · '.$orang['kursi'] : '')),
                'kamar' => $orang['kamar'] ?? '',
                'usia' => $k['usia'] ?? '',
                'darah' => $k['golongan_darah'] ?? '',
                'perhatian' => ($k['tingkat_perhatian'] ?? '') === 'tinggi' ? 'PERLU PERHATIAN' : '',
                'kesehatan' => $k
                    ? collect([$k['riwayat_penyakit'] ?? null, $k['alergi'] ?? null, $k['pantangan_makanan'] ?? null])
                        ->filter()->implode('; ')
                    : 'belum diisi',
                'darurat' => $k
                    ? trim(($k['kontak_darurat']['nama'] ?? '').' '.($k['kontak_darurat']['hp'] ?? ''))
                    : '',
                'pemesan' => $satu['nama'] ?? '',
                'kode' => $satu['kode'] ?? '',
                'wa' => $satu['whatsapp'] ?? '',
            ];
        }
    }

    $perTitik = collect($baris)->groupBy('titik')->sortKeys();

    $judulUtama = 'background-color:#0F2D4A;color:#FFFFFF;font-size:15px;font-weight:bold;padding:8px;';
    $judulTitik = 'background-color:#1D6FA5;color:#FFFFFF;font-weight:bold;padding:6px;';
    $kepalaKolom = 'background-color:#EEF6FB;color:#0F2D4A;font-weight:bold;border:1px solid #CFE4F2;padding:5px;';
    $sel = 'border:1px solid #E3EEF6;padding:4px;';
    $selAwas = 'border:1px solid #E3EEF6;padding:4px;background-color:#FFF4E5;';
    $catatan = 'color:#6B7785;font-size:11px;padding:4px;';
@endphp

<table>
    <tr><td colspan="10" style="{{ $judulUtama }}">MANIFES ROMBONGAN — UNTUK TOUR LEADER</td></tr>
    <tr>
        <td colspan="10" style="{{ $catatan }}">
            Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB &middot;
            {{ count($baris) }} peserta &middot; {{ $perTitik->count() }} titik jemput.
            Isinya sama dengan manifes PDF.
        </td>
    </tr>

    <tr>
        <td style="{{ $kepalaKolom }}">Titik jemput</td>
        <td style="{{ $kepalaKolom }}">No</td>
        <td style="{{ $kepalaKolom }}">Nama peserta</td>
        <td style="{{ $kepalaKolom }}">L/P</td>
        <td style="{{ $kepalaKolom }}">Bus &amp; kursi</td>
        <td style="{{ $kepalaKolom }}">Kamar</td>
        <td style="{{ $kepalaKolom }}">Usia / gol. darah</td>
        <td style="{{ $kepalaKolom }}">Perhatian khusus</td>
        <td style="{{ $kepalaKolom }}">Kontak darurat</td>
        <td style="{{ $kepalaKolom }}">Pemesan &amp; WhatsApp</td>
    </tr>

    @foreach ($perTitik as $titik => $orang)
        {{-- Kepala titik jemput: inilah urutan kerja tour leader di
             lapangan — berhenti, panggil nama, lanjut. --}}
        <tr>
            <td colspan="10" style="{{ $judulTitik }}">
                {{ $titik }} — {{ $orang->count() }} orang
            </td>
        </tr>

        @foreach ($orang as $satu)
            @php $gaya = $satu['perhatian'] !== '' ? $selAwas : $sel; @endphp
            <tr>
                <td style="{{ $gaya }}">{{ $titik }}</td>
                <td style="{{ $gaya }}">{{ $loop->iteration }}</td>
                <td style="{{ $gaya }}">{{ $satu['nama'] }}</td>
                <td style="{{ $gaya }}">{{ mb_substr((string) $satu['lp'], 0, 1) }}</td>
                <td style="{{ $gaya }}">{{ $satu['bus'] ?: '—' }}</td>
                <td style="{{ $gaya }}">{{ $satu['kamar'] ?: '—' }}</td>
                <td style="{{ $gaya }}">
                    {{ collect([$satu['usia'] ? $satu['usia'].' th' : null, $satu['darah'] ? 'gol. '.$satu['darah'] : null])
                        ->filter()->implode(' · ') }}
                </td>
                <td style="{{ $gaya }}">
                    {{ trim($satu['perhatian'].' '.$satu['kesehatan']) }}
                </td>
                <td style="{{ $gaya }}">{{ $satu['darurat'] ?: '—' }}</td>
                {{-- Nomor diawali apostrof supaya Excel tidak membuang nol di
                     depannya: "081234567890" jadi "81234567890", dan nomor itu
                     tidak bisa dihubungi. --}}
                <td style="{{ $gaya }}">
                    {{ $satu['pemesan'] }} {{ $satu['wa'] !== '' ? "'".$satu['wa'] : '' }}
                </td>
            </tr>
        @endforeach
    @endforeach
</table>
