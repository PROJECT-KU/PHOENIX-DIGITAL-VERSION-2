@extends('emails.asthana.bingkai')

@section('judul', $jenis === 'lamaran' ? 'Lamaran diterima' : 'Pesan diterima')
@section('pratinjau', $jenis === 'lamaran' ? 'Nomor lamaran Anda: '.$nomor : 'Pesan Anda sudah kami terima.')

@section('isi')
    @php
        $urlStatus = ($situs && $nomor) ? $situs.'/karir/status.html?nomor='.urlencode($nomor) : null;
        $langkah = [
            ['Ditinjau', 'Tim kami meninjau CV, surat lamaran, dan berkas Anda.'],
            ['Dihubungi', 'Bila profil Anda sesuai, kami menghubungi lewat email atau telepon.'],
            ['Wawancara', 'Kami atur jadwal wawancara untuk saling mengenal lebih jauh.'],
        ];
    @endphp

    <p style="margin:0 0 6px; font-size:12px; font-weight:800; letter-spacing:1.5px; color:#17a974; text-transform:uppercase;">
        {{ $jenis === 'lamaran' ? '✓ Lamaran diterima' : '✓ Pesan diterima' }}
    </p>
    <p style="margin:0 0 14px; font-size:20px; font-weight:800; color:#0f1b3d;">Halo {{ $nama }},</p>

    @if ($jenis === 'lamaran')
        <p style="margin:0 0 20px; font-size:15px; line-height:1.75; color:#4a5578;">
            @if ($posisi)
                Terima kasih telah melamar posisi <strong style="color:#0f1b3d;">{{ $posisi }}</strong>.
                Lamaran Anda sudah kami terima dan akan ditinjau oleh tim kami.
            @else
                Terima kasih telah mengirim <strong style="color:#0f1b3d;">lamaran umum</strong>.
                CV Anda kami simpan dan kami tinjau setiap ada posisi yang cocok.
            @endif
        </p>

        @if ($nomor)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px; background:#f3f6ff; border:1px solid #dbe4ff; border-radius:16px;">
                <tr>
                    <td align="center" style="padding:18px 16px;">
                        <div style="font-size:11px; font-weight:800; letter-spacing:2px; color:#6b7aa6;">NOMOR LAMARAN</div>
                        <div style="font-family:'SFMono-Regular',Menlo,Consolas,monospace; font-size:24px; font-weight:800; letter-spacing:2px; color:#2455e6; margin:6px 0 4px;">{{ $nomor }}</div>
                        <div style="font-size:12px; color:#6b7aa6;">Simpan nomor ini untuk mengecek status lamaran Anda.</div>
                    </td>
                </tr>
            </table>
        @endif

        <p style="margin:0 0 12px; font-size:14px; font-weight:800; color:#0f1b3d;">Apa selanjutnya?</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px;">
            @foreach ($langkah as $i => [$judulLangkah, $isiLangkah])
                <tr>
                    <td width="40" valign="top" style="padding:0 12px 14px 0;">
                        <div style="width:28px; height:28px; line-height:28px; text-align:center; border-radius:9px; background:#eaf0ff; color:#2455e6; font-size:13px; font-weight:800;">{{ $i + 1 }}</div>
                    </td>
                    <td valign="top" style="padding:0 0 14px; font-size:14px; line-height:1.6; color:#4a5578;">
                        <strong style="color:#0f1b3d;">{{ $judulLangkah }}</strong><br>{{ $isiLangkah }}
                    </td>
                </tr>
            @endforeach
        </table>

        @if ($urlStatus)
            @include('emails.asthana.tombol', ['url' => $urlStatus, 'label' => 'Cek status lamaran'])
        @endif
    @else
        <p style="margin:0 0 20px; font-size:15px; line-height:1.75; color:#4a5578;">
            Terima kasih telah menghubungi kami. Pesan Anda sudah kami terima, dan tim kami
            akan segera membalasnya melalui email atau WhatsApp.
        </p>
        @if ($situs)
            @include('emails.asthana.tombol', ['url' => $situs, 'label' => 'Kunjungi situs kami'])
        @endif
    @endif
@endsection
